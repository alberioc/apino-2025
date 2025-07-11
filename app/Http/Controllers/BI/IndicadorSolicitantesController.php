<?php

namespace App\Http\Controllers\BI;

use App\Http\Controllers\Controller;
use App\Services\FiltroVendasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon as CarbonDate;

class IndicadorSolicitantesController extends Controller
{
    public function show(Request $request)
    {
        $query = FiltroVendasService::aplicarFiltros($request, auth()->user()->company_group, 'vendas');

        // Seleciona os dados
        $dados = $query->select('solicitante', 'valor_total')->get();

        // Agrupa e calcula os indicadores
        $agrupado = $dados->groupBy('solicitante')->map(function ($items, $nome) {
            $quantidade = $items->count();
            $total = $items->sum('valor_total');

            return [
                'solicitante' => $nome,
                'quantidade' => $quantidade,
                'total' => $total,
                'ticket_medio' => $quantidade > 0 ? $total / $quantidade : 0,
            ];
        })->values();

        $totalGeral = $agrupado->sum('total');

        $resultado = $agrupado->map(function ($item) use ($totalGeral) {
            $item['percentual'] = $totalGeral > 0 ? ($item['total'] / $totalGeral) * 100 : 0;
            return $item;
        });

        return view('bi.indicadores._indicador_solicitantes', [
            'solicitantes' => $resultado,
        ]);
    }
}
