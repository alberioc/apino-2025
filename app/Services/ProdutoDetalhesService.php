<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ProdutoDetalhesService
{
    public function gerarHtmlDetalhes(string $produto, array $pagantesDoGrupo): string
    {
        $valorTotalProduto = DB::table('vendas')
            ->where('produto', $produto)
            ->whereIn('pagante', $pagantesDoGrupo)
            ->sum('valor_total');

        $html = "<h6>Produto: <strong>{$produto}</strong></h6>";
        $html .= "<p>Total Geral: <strong>R$ " . number_format($valorTotalProduto, 2, ',', '.') . "</strong></p>";

        if ($produto === 'Diárias de Hospedagem') {
            $dados = DB::table('vendas')
                ->select('cidade_fornecedor',
                    DB::raw('SUM(diarias) as quantidade_total'),
                    DB::raw('SUM(valor_total) as valor_total')
                )
                ->where('produto', $produto)
                ->whereIn('pagante', $pagantesDoGrupo)
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

            $html .= "</tbody></table></div>"; // fechando tbody e div
        } else {
            $dados = DB::table('vendas')
                ->select('fornecedor',
                    DB::raw('SUM(quantidade) as quantidade_total'),
                    DB::raw('SUM(valor_total) as valor_total')
                )
                ->where('produto', $produto)
                ->whereIn('pagante', $pagantesDoGrupo)
                ->groupBy('fornecedor')
                ->orderBy(DB::raw('SUM(valor_total)'), 'desc')
                ->get();

            $html .= "<div class='table-responsive'>";
            $html .= "<table id='tabelaDetalhesProduto' class='table table-sm table-bordered table-hover table-striped align-middle small'>";
            $html .= "<thead><tr>
                        <th>Fornecedor</th>
                        <th>Quantidade</th>
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

            $html .= "</tbody></table></div>"; // fechando tbody e div
        }

        return $html;
    }
}
