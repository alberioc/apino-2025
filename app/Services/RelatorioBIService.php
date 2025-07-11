<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RelatorioBIService
{
    public function buscarPagantesDoGrupo(int $companyGroupId)
    {
        return DB::table('company_group_venda')
            ->where('company_group_id', $companyGroupId)
            ->pluck('pagante');
    }

    public function filtrarVendas(Request $request, $pagantesDoGrupo)
    {
        $query = DB::table('vendas')->whereIn('pagante', $pagantesDoGrupo);

        $inicio = $request->filled('inicio') ? $request->inicio : Carbon::now()->subMonths(3)->startOfMonth();
        $fim = $request->filled('fim') ? $request->fim : Carbon::now()->endOfMonth();

        $query->whereDate('data_venda', '>=', $inicio)
              ->whereDate('data_venda', '<=', $fim);

        if ($request->filled('pagantes')) {
            $query->whereIn('pagante', $request->pagantes);
        }

        return $query->orderBy('data_venda', 'ASC')->get();
    }

    public function calcularIndicadores($vendas)
    {
        $quantidade = $vendas->sum('quantidade');
        $valorTotal = $vendas->sum('valor_total');
        return [
            'quantidadeTotal' => $quantidade,
            'valorTotal' => $valorTotal,
            'ticketMedio' => $quantidade > 0 ? $valorTotal / $quantidade : 0,
        ];
    }

    public function obterComparativoAnos($pagantesDoGrupo)
    {
        $anoAtual = Carbon::now()->year;
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

        return $anosComparativo;
    }

    public function agruparPorProduto($vendas)
    {
        return $vendas->groupBy('produto')->map(function ($produtosGroup) {
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
    }

    public function obterVendasUltimos12Meses($pagantesDoGrupo)
    {
        return DB::table('vendas')
            ->selectRaw("
                DATE_FORMAT(data_venda, '%Y-%m') as mes,
                SUM(valor_total) as valor_total,
                SUM(COALESCE(quantidade, 0)) as quantidade_total,
                IF(SUM(COALESCE(quantidade, 0)) > 0, SUM(valor_total) / SUM(COALESCE(quantidade, 0)), 0) as ticket_medio
            ")
            ->whereIn('pagante', $pagantesDoGrupo)
            ->whereDate('data_venda', '>=', Carbon::now()->subMonths(12)->startOfMonth())
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();
    }

    public function agruparPorSolicitante(Request $request, $pagantesDoGrupo)
    {
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

        $totalValor = $solicitantesData->sum('valor_total');

        return $solicitantesData->map(function ($item) use ($totalValor) {
            $item->share = $totalValor > 0 ? round(($item->valor_total / $totalValor) * 100, 2) : 0;
            return $item;
        });
    }
}
