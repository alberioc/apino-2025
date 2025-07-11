import pandas as pd
from sqlalchemy import create_engine
from datetime import datetime
import os
from dotenv import load_dotenv
from openai import OpenAI

# Carrega variáveis de ambiente
load_dotenv(dotenv_path='scripts/.env')
client = OpenAI()

# Config DB
db_user = os.getenv('DB_USERNAME')
db_pass = os.getenv('DB_PASSWORD')
db_host = os.getenv('DB_HOST')
db_name = os.getenv('DB_DATABASE')

engine = create_engine(f'mysql+pymysql://{db_user}:{db_pass}@{db_host}/{db_name}')

# Variáveis de filtro
pagante = 'DATAMED'
produto = 'Passagem Aérea'

# Tradução dos trechos
airports_path = os.path.join('storage', 'app', 'data', 'airports.dat')
cols = ['ID','Nome','Cidade','Pais','IATA','ICAO','Lat','Lon','Alt','Timezone','DST','TZ','Tipo','Fonte']
airports_df = pd.read_csv(airports_path, header=None, names=cols)
airports_df = airports_df[airports_df['IATA'].notnull() & (airports_df['IATA'] != '\\N')]
iata_to_city = airports_df.set_index('IATA')['Cidade'].to_dict()

def traduz_trecho(trecho):
    if not isinstance(trecho, str):
        return trecho
    partes = trecho.split('-')
    nomes = [iata_to_city.get(p.strip(), p.strip()) for p in partes]
    return ' → '.join(nomes)

# Consulta SQL
query = """
SELECT venda_numero, pagante, produto, data_inicio, data_fim, data_venda, trechos, valor_total
FROM vendas
WHERE valor_total IS NOT NULL
"""

df = pd.read_sql(query, engine)

# Converte datas
df['data_inicio'] = pd.to_datetime(df['data_inicio'], errors='coerce')
df['data_venda'] = pd.to_datetime(df['data_venda'], errors='coerce')
df['data_fim'] = pd.to_datetime(df['data_fim'], errors='coerce')

# Define intervalo dos últimos 3 meses
hoje = datetime.today()
primeiro_dia_mes_atual = hoje.replace(day=1)
inicio_periodo = (primeiro_dia_mes_atual - pd.DateOffset(months=3)).to_pydatetime()
fim_periodo = (primeiro_dia_mes_atual - pd.DateOffset(days=1)).replace(hour=23, minute=59, second=59)

# Filtros
df = df[
    (df['data_venda'] >= inicio_periodo) &
    (df['data_venda'] <= fim_periodo) &
    (df['pagante'] == pagante) &
    (df['produto'] == produto)
]

# Limpa dados inválidos
df = df.dropna(subset=['data_inicio', 'data_fim', 'data_venda'])

# Calcula antecedência
df['antecedencia_dias'] = (df['data_inicio'] - df['data_venda']).dt.days

# Faixas em dias
bins = [-1, 7, 14, 30, 60, 90, 9999]
labels = ['0-7 dias', '8-14 dias', '15-30 dias', '31-60 dias', '61-90 dias', '91+ dias']
df['faixa_antecedencia'] = pd.cut(df['antecedencia_dias'], bins=bins, labels=labels)

# Remove valores baixos
df = df[df['valor_total'] > 50]

# Traduz trechos
df['trecho_legivel'] = df['trechos'].apply(traduz_trecho)
df['trecho_legivel'] = df['trecho_legivel'].str.replace('→', '->')

# Agrupamento
agrupado = df.groupby(['faixa_antecedencia', 'trecho_legivel'])['valor_total'].mean().reset_index()
contagem = df.groupby(['faixa_antecedencia', 'trecho_legivel'])['valor_total'].count().reset_index(name='quantidade')
combinado = pd.merge(agrupado, contagem, on=['faixa_antecedencia', 'trecho_legivel'])

# Pivôs de média e quantidade
resumo_valores = combinado.pivot(index='trecho_legivel', columns='faixa_antecedencia', values='valor_total').fillna(0)
resumo_qtd = combinado.pivot(index='trecho_legivel', columns='faixa_antecedencia', values='quantidade').fillna(0).astype(int)

# Formatação: R$ valor (x vezes)
def formatar_valor_qtd(v, q):
    return f'R$ {v:,.2f} ({q}x)' if q > 0 else '—'

# Tabela final formatada
tabela_completa = pd.DataFrame(index=resumo_valores.index)
for col in resumo_valores.columns:
    tabela_completa[col] = [
        formatar_valor_qtd(resumo_valores.loc[ix, col], resumo_qtd.loc[ix, col])
        for ix in resumo_valores.index
    ]

# Limita a 20 trechos
resumo_cortado = tabela_completa.head(20)
resumo_cortado.index.name = 'Trecho'
resumo_str = resumo_cortado.to_string()

# Salva CSV formatado
output_dir = os.path.join('storage', 'app', 'data')
os.makedirs(output_dir, exist_ok=True)
dados_csv_path = os.path.join(output_dir, 'dados_antecedencia.csv')
resumo_cortado.to_csv(dados_csv_path, encoding='utf-8')

# Prompt para GPT
prompt = f"""
Você é um analista financeiro especialista em viagens corporativas.

Analisando os dados abaixo, que mostram o custo médio por trecho agrupado pela antecedência da compra da viagem, gere um relatório **curto e direto (até 3 parágrafos)**. Seja empático e estratégico: 
- Evite frases como "falta de planejamento".
- Considere que a empresa pode lidar com viagens emergenciais.
- Foque em sugerir melhorias, otimizações e oportunidades de economia de forma construtiva.
- Considere melhorar a política de viagens evitando descumprimento.

{resumo_str}

Gere um resumo **curto e direto**, com até 1 parágrafo. Destaque padrões, excessos e sugestões de economia de forma clara e objetiva.
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

# Salva o insight
output_dir = os.path.join('storage', 'app', 'insights')
os.makedirs(output_dir, exist_ok=True)
output_file = os.path.join(output_dir, 'insight_antecedencia.txt')

with open(output_file, 'w', encoding='utf-8') as f:
    f.write(texto_ia)

print("✅ Insight de antecedência com faixas em dias gerado e salvo com sucesso!")
