<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FiltroVendasService
{
    public static function aplicarFiltros(Request $request, $companyGroupId, $tabela = 'vendas')
    {
        $pagantesDoGrupo = DB::table('company_group_venda')
            ->where('company_group_id', $companyGroupId)
            ->pluck('pagante');

        $query = DB::table($tabela)->whereIn('pagante', $pagantesDoGrupo);

        if ($request->filled('inicio')) {
            $query->whereDate('data_venda', '>=', $request->inicio);
        } else {
            $query->whereDate('data_venda', '>=', Carbon::now()->subMonths(3)->startOfMonth());
        }

        if ($request->filled('fim')) {
            $query->whereDate('data_venda', '<=', $request->fim);
        } else {
            $query->whereDate('data_venda', '<=', Carbon::now()->endOfMonth());
        }

        if ($request->filled('pagantes')) {
            $query->whereIn('pagante', $request->pagantes);
        }

        return $query;
    }

}
