# scripts/gerar_insight_viajantes.py

import pandas as pd
from sqlalchemy import create_engine
from datetime import datetime
import os
from dotenv import load_dotenv
from openai import OpenAI

# Carrega variáveis do .env
load_dotenv(dotenv_path='scripts/.env')
client = OpenAI()

# Conexão com banco
db_user = os.getenv('DB_USERNAME')
db_pass = os.getenv('DB_PASSWORD')
db_host = os.getenv('DB_HOST')
db_name = os.getenv('DB_DATABASE')
engine = create_engine(f'mysql+pymysql://{db_user}:{db_pass}@{db_host}/{db_name}')

# Filtros
pagante = 'DATAMED'
setor = 'Saúde'

# Consulta
query = """
SELECT passageiro, pagante, valor_total, data_venda
FROM lista_passageiros
WHERE valor_total IS NOT NULL
"""

df = pd.read_sql(query, engine)
df['data_venda'] = pd.to_datetime(df['data_venda'], errors='coerce')
df = df.dropna(subset=['data_venda'])

# Últimos 3 meses completos
hoje = datetime.today()
inicio_periodo = (hoje.replace(day=1) - pd.DateOffset(months=3)).to_pydatetime()
fim_periodo = (hoje.replace(day=1) - pd.DateOffset(days=1)).replace(hour=23, minute=59, second=59)

# Filtra período e pagante
df = df[
    (df['data_venda'] >= inicio_periodo) &
    (df['data_venda'] <= fim_periodo) &
    (df['pagante'] == pagante)
]

if df.empty:
    print("Nenhum dado encontrado para viajantes.")
    exit()

# Agrupamento por passageiro
agrupado = df.groupby('passageiro')['valor_total'].agg(['sum', 'count']).sort_values(by='sum', ascending=False).head(10)
agrupado['media'] = agrupado['sum'] / agrupado['count']

# Formatação: R$ média (x vezes)
def formatar_valor_qtd(media, qtd):
    return f'R$ {media:,.2f} ({qtd:.0f}x)' if qtd > 0 else '—'

agrupado['Resumo'] = agrupado.apply(lambda x: formatar_valor_qtd(x['media'], x['count']), axis=1)

# Prepara CSV
resumo_csv = agrupado.reset_index()
resumo_csv.rename(columns={
    'passageiro': 'Viajante',
    'sum': 'Total Gasto (R$)',
    'count': 'Nº de Viagens',
}, inplace=True)

resumo_csv = resumo_csv[['Viajante', 'Total Gasto (R$)', 'Resumo']]
resumo_csv['Total Gasto (R$)'] = resumo_csv['Total Gasto (R$)'].apply(lambda x: f'R$ {x:,.2f}'.replace(',', 'v').replace('.', ',').replace('v', '.'))

# Salva CSV
output_data_dir = os.path.join('storage', 'app', 'data')
os.makedirs(output_data_dir, exist_ok=True)
resumo_csv.to_csv(os.path.join(output_data_dir, 'dados_viajantes.csv'), encoding='utf-8', index=False)

# Gera insight com IA
resumo_str = resumo_csv.to_string(index=False)
periodo_str = f"{inicio_periodo.strftime('%d/%m/%Y')} a {fim_periodo.strftime('%d/%m/%Y')}"
prompt = f"""
Você é um analista financeiro especialista em viagens corporativas.

Abaixo estão os maiores viajantes da empresa {pagante} no setor {setor}, durante o período de {periodo_str}:

{resumo_str}

Com base nesses dados, escreva um resumo curto e estratégico (1 parágrafo) destacando padrões de comportamento de viagem, oportunidades de economia, e como o sistema Apino pode apoiar na gestão dos viajantes mais frequentes.
Evite sugerir redução de viagens, e foque em boas práticas de compra e uso inteligente da política.
"""

response = client.chat.completions.create(
    model="gpt-4o",
    messages=[
        {"role": "system", "content": "Você é um analista financeiro especialista em viagens corporativas."},
        {"role": "user", "content": prompt}
    ],
    temperature=0.7,
    max_tokens=350
)

texto_ia = response.choices[0].message.content.strip()

# Salva insight
output_insight_path = os.path.join('storage', 'app', 'insights', 'insight_viajantes.txt')
with open(output_insight_path, 'w', encoding='utf-8') as f:
    f.write(texto_ia)

print("✅ Insight de viajantes gerado e salvo com sucesso!")
