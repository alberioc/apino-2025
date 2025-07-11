import pandas as pd
from sqlalchemy import create_engine
from datetime import datetime
import os
from dotenv import load_dotenv
from openai import OpenAI

load_dotenv(dotenv_path='scripts/.env')
client = OpenAI()

# Configurações do banco
db_user = os.getenv('DB_USERNAME')
db_pass = os.getenv('DB_PASSWORD')
db_host = os.getenv('DB_HOST')
db_name = os.getenv('DB_DATABASE')
engine = create_engine(f'mysql+pymysql://{db_user}:{db_pass}@{db_host}/{db_name}')

# Variáveis de filtro
pagante = 'DATAMED'
setor = 'Saúde'  # você pode parametrizar se quiser

# Consulta base
query = """
SELECT venda_numero, produto, fornecedor, pagante, centro_custo_passageiro AS centro_custo, 
       valor_total, cidade_fornecedor, data_venda
FROM lista_passageiros
WHERE valor_total IS NOT NULL
"""

df = pd.read_sql(query, engine)
df['data_venda'] = pd.to_datetime(df['data_venda'], errors='coerce')
df = df.dropna(subset=['data_venda'])

# Pega últimos 3 meses completos
hoje = datetime.today()
inicio_periodo = (hoje.replace(day=1) - pd.DateOffset(months=3)).to_pydatetime()
fim_periodo = (hoje.replace(day=1) - pd.DateOffset(days=1)).replace(hour=23, minute=59, second=59)

# Filtra pagante + período
df_mes = df[
    (df['data_venda'] >= inicio_periodo) &
    (df['data_venda'] <= fim_periodo) &
    (df['pagante'] == pagante)
]

print(f"Registros após filtro: {len(df_mes)}")
if df_mes.empty:
    print(f"Nenhum dado encontrado para pagante {pagante} nos últimos 3 meses.")
    exit()

# Agrupamento e cálculo da média
agrupado = df_mes.groupby('centro_custo')['valor_total'].agg(['sum', 'count']).sort_values(by='sum', ascending=False).head(10)
agrupado['media'] = agrupado['sum'] / agrupado['count']

# Formatação: R$ média (x vezes)
def formatar_valor_qtd(media, qtd):
    return f'R$ {media:,.2f} ({qtd:.0f}x)' if qtd > 0 else '—'

agrupado['Resumo'] = agrupado.apply(lambda x: formatar_valor_qtd(x['media'], x['count']), axis=1)

# Prepara CSV com colunas renomeadas
resumo_csv = agrupado.reset_index()
resumo_csv.rename(columns={
    'centro_custo': 'Centro de Custo',
    'sum': 'Total Gasto (R$)',
}, inplace=True)

# Mantém apenas as colunas necessárias
resumo_csv = resumo_csv[['Centro de Custo', 'Total Gasto (R$)', 'Resumo']]

# Formata o Total Gasto para padrão brasileiro
resumo_csv['Total Gasto (R$)'] = resumo_csv['Total Gasto (R$)'].apply(
    lambda x: f'R$ {x:,.2f}'.replace(',', 'v').replace('.', ',').replace('v', '.')
)

# Salva CSV
output_data_dir = os.path.join('storage', 'app', 'data')
os.makedirs(output_data_dir, exist_ok=True)
resumo_csv.to_csv(os.path.join(output_data_dir, 'dados_centro_custo.csv'), encoding='utf-8', index=False)

# Prepara prompt para IA com o resumo formatado para texto
resumo_str = resumo_csv.to_string(index=False)
periodo_str = f"{inicio_periodo.strftime('%d/%m/%Y')} a {fim_periodo.strftime('%d/%m/%Y')}"

prompt = f"""
Você é um analista financeiro especialista em viagens corporativas.

A empresa pagante {pagante} atua no setor {setor}, onde viagens emergenciais são comuns e impactam os custos.

Abaixo estão os gastos por centro de custo no período de {periodo_str}:
{resumo_str}

Com base nesses dados, gere um **resumo curto e direto** com 1 parágrafo.
A Apino disponibiliza para uso da {pagante} um sistema de aprovação, controle e aplicação da política de viagens.
Seja estratégico e empático: destaque oportunidades de economia relacionadas à antecipação e melhores práticas de compra de viagens corporativas, evitando sugerir redução no volume de viagens, pois o setor demanda deslocamentos urgentes e contínuos.
"""

# Chamada OpenAI
response = client.chat.completions.create(
    model="gpt-4o",
    messages=[
        {"role": "system", "content": "Você é um analista financeiro especialista em viagens corporativas."},
        {"role": "user", "content": prompt}
    ],
    temperature=0.7,
    max_tokens=350
)

# Salva insight
texto_ia = response.choices[0].message.content.strip()
output_insight_path = os.path.join('storage', 'app', 'insights', 'insight_centro_custo.txt')
os.makedirs(os.path.dirname(output_insight_path), exist_ok=True)

with open(output_insight_path, 'w', encoding='utf-8') as f:
    f.write(texto_ia)

print("✅ Insight por centro de custo gerado e salvo com sucesso!")
