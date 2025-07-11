<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon as CarbonDate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BIController extends Controller
{
    protected $biService;
    
    public function index(Request $request)
    {
        // Grupo da empresa logada
        $companyGroupId = auth()->user()->company_group;

        // Pega o nome do grupo empresarial
        $nomeGrupo = DB::table('company_groups')
        ->where('id', $companyGroupId)
        ->value('name');
        
        // Buscar pagantes associados ao company group
        $pagantesDoGrupo = DB::table('company_group_venda')
            ->where('company_group_id', $companyGroupId)
            ->pluck('pagante');

        // Filtros principais (data e pagantes)
        $query = DB::table('vendas')
            ->whereIn('pagante', $pagantesDoGrupo);
        
        if ($request->filled('inicio')) {
            $query->whereDate('data_venda', '>=', $request->inicio);
        } else {
            $query->whereDate('data_venda', '>=', CarbonDate::now()->subMonths(3)->startOfMonth());
        }

        if ($request->filled('fim')) {
            $query->whereDate('data_venda', '<=', $request->fim);
        } else {
            $query->whereDate('data_venda', '<=', CarbonDate::now()->endOfMonth());
        }

        if ($request->filled('pagantes')) {
            $query->whereIn('pagante', $request->pagantes);
        }

        // Consulta principal
        $vendas = $query->orderBy('data_venda', 'ASC')->get();

        // Indicadores do período atual
        $totalVendas = $vendas->count();
        $quantidadeTotal = $vendas->sum('quantidade');
        $valorTotal = $vendas->sum('valor_total');
        $ticketMedio = $quantidadeTotal > 0 ? $valorTotal / $quantidadeTotal : 0;

        // --- Agregar dados dos últimos 4 anos para o comparativo ---
        $anoAtual = CarbonDate::now()->year;
        $anosComparativo = [];

        for ($ano = $anoAtual - 3; $ano <= $anoAtual; $ano++) {
            $vendasAno = DB::table('vendas')
                ->whereIn('pagante', $pagantesDoGrupo)
                ->whereYear('data_venda', $ano)
                ->get();

            $totalAno = $vendasAno->count();
            $valorTotalAno = $vendasAno->sum('valor_total');
            $ticketMedioAno = $totalAno > 0 ? $valorTotalAno / $totalAno : 0;

            $anosComparativo[] = [
                'ano' => $ano,
                'valor_total' => $valorTotalAno,
                'quantidade' => $totalAno,
                'ticket_medio' => $ticketMedioAno,
            ];
        }

        // Gerar insight com base no comparativo dos anos
        $prompt = "Você é um consultor da Apino Turismo, uma agência de viagens 
        corporativas especializada em gestão de viagens corporativas que já oferece um sistema de OBT 
        para seus clientes, com política de viagens, controle orçamentário por centro de custo, fluxo de aprovação e gestão completa. 
        Abaixo estão os dados históricos de compras do grupo empresarial \"{$nomeGrupo}\". 
        Fale sobre a evolução das compras e valor do ticket médio e gere um insight consultivo 
        de até 3 frases, voltado para o grupo cliente, com **dicas de como otimizar o uso das 
        soluções já oferecidas pela Apino**, como melhorar a adesão à política, aumentar a 
        eficiência, comprar com mais antecedência e fortalecer a governança. 
        Seja objetivo, estratégico e mostre oportunidades reais de melhoria. 
        Evite sugestões que impliquem contratar outras ferramentas ou negociar com a agência, 
        pois ela já é a parceira estratégica do grupo. Sempre enfatize a parceria com a Apino Turismo\n\n";
        $prompt .= json_encode($anosComparativo, JSON_PRETTY_PRINT);

        $insightAnoComparativo = gerarInsightOpenAI($prompt);        

        // Agrupar por produto
        $produtosData = $vendas->groupBy('produto')->map(function ($produtosGroup) {
            $quantidade = $produtosGroup->sum('quantidade');
            $valorTotalProduto = $produtosGroup->sum('valor_total');
            $ticketMedioProduto = $quantidade > 0 ? $valorTotalProduto / $quantidade : 0;

            return [
                'produto' => $produtosGroup->first()->produto,
                'quantidade' => $quantidade,
                'valor_total' => $valorTotalProduto,
                'ticket_medio' => $ticketMedioProduto,
            ];
        });

        // Dados dos últimos 12 meses (para gráfico)
        $vendasUltimos12Meses = DB::table('vendas')
            ->selectRaw("
                DATE_FORMAT(data_venda, '%Y-%m') as mes,
                SUM(valor_total) as valor_total,
                SUM(COALESCE(quantidade, 0)) as quantidade_total,
                IF(SUM(COALESCE(quantidade, 0)) > 0, SUM(valor_total) / SUM(COALESCE(quantidade, 0)), 0) as ticket_medio
            ")
            ->whereIn('pagante', $pagantesDoGrupo)
            ->whereDate('data_venda', '>=', CarbonDate::now()->subMonths(12)->startOfMonth())
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();
        //dd($vendasUltimos12Meses);

        // Exemplo para agregar os solicitantes
        $solicitantesData = DB::table('vendas')
            ->select(
                'solicitante',
                DB::raw('SUM(valor_total) as valor_total'),
                DB::raw('COUNT(*) as quantidade'),
                DB::raw('AVG(valor_total) as valor_medio')
            )
            ->whereIn('pagante', $pagantesDoGrupo)
            ->when($request->inicio, fn($q) => $q->whereDate('data_venda', '>=', $request->inicio))
            ->when($request->fim, fn($q) => $q->whereDate('data_venda', '<=', $request->fim))
            ->groupBy('solicitante')
            ->get();

        // Calcular o total geral para o share
        $totalValor = $solicitantesData->sum('valor_total');

        // Adiciona share % em cada item
        $solicitantesData = $solicitantesData->map(function($item) use ($totalValor) {
            $item->share = $totalValor > 0 ? round(($item->valor_total / $totalValor) * 100, 2) : 0;
            return $item;
        });
        
        return view('bi.index', [            
            'vendas' => $vendas,
            'pagantesDoGrupo' => $pagantesDoGrupo,
            'inicio' => $request->inicio,
            'fim' => $request->fim,
            'pagantesSelecionados' => $request->pagantes ?? [],
            'totalVendas' => $totalVendas,
            'quantidadeTotal' => $quantidadeTotal,
            'valorTotal' => $valorTotal,
            'ticketMedio' => $ticketMedio,
            'vendasUltimos12Meses' => $vendasUltimos12Meses,
            'anosComparativo' => $anosComparativo,
            'insightAnoComparativo' => $insightAnoComparativo,
            'produtosData' => $produtosData,
            'solicitantesData' => $solicitantesData,
        ]);

    }

    public function produtoDetalhes(Request $request)
    {
        $produto = $request->query('produto');
        $companyGroupId = auth()->user()->company_group;

        // Definir datas de filtro, usando valores do request ou padrão (últimos 3 meses)
        $inicio = $request->filled('inicio') 
            ? $request->inicio 
            : now()->subMonths(3)->startOfMonth()->toDateString();

        $fim = $request->filled('fim') 
            ? $request->fim 
            : now()->endOfMonth()->toDateString();

        // Buscar pagantes do grupo para filtrar vendas
        $pagantesDoGrupo = DB::table('company_group_venda')
            ->where('company_group_id', $companyGroupId)
            ->pluck('pagante');

        // Buscar valor total do produto no período e pagantes do grupo
        $valorTotalProduto = DB::table('vendas')
            ->where('produto', $produto)
            ->whereIn('pagante', $pagantesDoGrupo)
            ->whereDate('data_venda', '>=', $inicio)
            ->whereDate('data_venda', '<=', $fim)
            ->sum('valor_total');

        $html = "<h6>Produto: <strong>{$produto}</strong></h6>";
        $html .= "<p>Total Geral: <strong>R$ " . number_format($valorTotalProduto, 2, ',', '.') . "</strong></p>";

        if ($produto === 'Diárias de Hospedagem') {
            // Agrupar por cidade, somando diárias e valor
            $dados = DB::table('vendas')
                ->select('cidade_fornecedor',
                    DB::raw('SUM(diarias) as quantidade_total'),
                    DB::raw('SUM(valor_total) as valor_total')
                )
                ->where('produto', $produto)
                ->whereIn('pagante', $pagantesDoGrupo)
                ->whereDate('data_venda', '>=', $inicio)
                ->whereDate('data_venda', '<=', $fim)
                ->groupBy('cidade_fornecedor')
                ->orderBy(DB::raw('SUM(diarias)'), 'desc')
                ->get();

            $html .= "<div class='table-responsive'>";
            $html .= "<table id='tabelaDetalhesProduto' class='table table-sm table-bordered table-hover table-striped align-middle small'>";
            $html .= "<thead><tr>
                        <th>Cidade</th>
                        <th>Quantidade de Diárias</th>
                        <th>Valor Total (R$)</th>
                        <th>Diária Média (R$)</th>
                        <th>% do Total</th>
                    </tr></thead><tbody>";

            foreach ($dados as $item) {
                $ticketMedio = $item->quantidade_total > 0 ? $item->valor_total / $item->quantidade_total : 0;
                $percentual = $valorTotalProduto > 0 ? ($item->valor_total / $valorTotalProduto) * 100 : 0;

                $html .= "<tr>";
                $html .= "<td>{$item->cidade_fornecedor}</td>";
                $html .= "<td>{$item->quantidade_total}</td>";
                $html .= "<td>" . number_format($item->valor_total, 2, ',', '.') . "</td>";
                $html .= "<td>" . number_format($ticketMedio, 2, ',', '.') . "</td>";
                $html .= "<td>" . number_format($percentual, 1, ',', '.') . "%</td>";
                $html .= "</tr>";
            }
        } elseif ($produto === 'Aluguel de Carro') {
            // Agrupar por fornecedor, somando diárias e valor
            $dados = DB::table('vendas')
                ->select('fornecedor',
                    DB::raw('SUM(diarias) as quantidade_total'),
                    DB::raw('SUM(valor_total) as valor_total')
                )
                ->where('produto', $produto)
                ->whereIn('pagante', $pagantesDoGrupo)
                ->whereDate('data_venda', '>=', $inicio)
                ->whereDate('data_venda', '<=', $fim)
                ->groupBy('fornecedor')
                ->orderBy(DB::raw('SUM(diarias)'), 'desc')
                ->get();

            $html .= "<div class='table-responsive'>";
            $html .= "<table id='tabelaDetalhesProduto' class='table table-sm table-bordered table-hover table-striped align-middle small'>";
            $html .= "<thead><tr>
                        <th>Fornecedor</th>
                        <th>Quantidade de Diárias</th>
                        <th>Valor Total (R$)</th>
                        <th>Diária Média (R$)</th>
                        <th>% do Total</th>
                    </tr></thead><tbody>";

            foreach ($dados as $item) {
                $ticketMedio = $item->quantidade_total > 0 ? $item->valor_total / $item->quantidade_total : 0;
                $percentual = $valorTotalProduto > 0 ? ($item->valor_total / $valorTotalProduto) * 100 : 0;

                $html .= "<tr>";
                $html .= "<td>{$item->fornecedor}</td>";
                $html .= "<td>{$item->quantidade_total}</td>";
                $html .= "<td>" . number_format($item->valor_total, 2, ',', '.') . "</td>";
                $html .= "<td>" . number_format($ticketMedio, 2, ',', '.') . "</td>";
                $html .= "<td>" . number_format($percentual, 1, ',', '.') . "%</td>";
                $html .= "</tr>";
            }
        } else {
            // Para outros produtos: contar processos únicos (venda_numero) por fornecedor
            $dados = DB::table('vendas')
                ->select('fornecedor', 
                    DB::raw('COUNT(*) as quantidade_total'), 
                    DB::raw('SUM(valor_total) as valor_total'))
                ->where('produto', $produto)
                ->whereIn('pagante', $pagantesDoGrupo)
                ->when($inicio, fn($q) => $q->whereDate('data_venda', '>=', $inicio))
                ->when($fim, fn($q) => $q->whereDate('data_venda', '<=', $fim))
                ->groupBy('fornecedor')
                ->orderBy('valor_total', 'desc')
                ->get();

            $html .= "<div class='table-responsive'>";
            $html .= "<table id='tabelaDetalhesProduto' class='table table-sm table-bordered table-hover table-striped align-middle small'>";
            $html .= "<thead><tr>
                        <th>Fornecedor</th>
                        <th>Quantidade de Processos</th>
                        <th>Valor Total (R$)</th>
                        <th>Ticket Médio (R$)</th>
                        <th>% do Total</th>
                    </tr></thead><tbody>";

            foreach ($dados as $item) {
                $ticketMedio = $item->quantidade_total > 0 ? $item->valor_total / $item->quantidade_total : 0;
                $percentual = $valorTotalProduto > 0 ? ($item->valor_total / $valorTotalProduto) * 100 : 0;

                $html .= "<tr>";
                $html .= "<td>{$item->fornecedor}</td>";
                $html .= "<td>{$item->quantidade_total}</td>";
                $html .= "<td>" . number_format($item->valor_total, 2, ',', '.') . "</td>";
                $html .= "<td>" . number_format($ticketMedio, 2, ',', '.') . "</td>";
                $html .= "<td>" . number_format($percentual, 1, ',', '.') . "%</td>";
                $html .= "</tr>";
            }
        }

        $html .= "</tbody></table></div>";

        return response()->json(['html' => $html]);
    }

}
