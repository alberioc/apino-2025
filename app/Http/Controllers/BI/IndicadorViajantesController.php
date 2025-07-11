<?php

namespace App\Http\Controllers\BI;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Services\FiltroVendasService;
use Illuminate\Http\Request;

class IndicadorViajantesController extends Controller
{
    public function show(Request $request)
    {
        // Aplica os filtros para 'lista_passageiros'
        $query = FiltroVendasService::aplicarFiltros($request, auth()->user()->company_group, 'lista_passageiros');

        // Seleciona passageiro e valor_total, já que é um passageiro por linha
        $dados = $query->select('passageiro', 'valor_total')->whereNotNull('passageiro')->get();
        
        // Agrupa por passageiro, calcula quantidade, soma e ticket médio
        $agrupado = $dados
            ->groupBy('passageiro')
            ->map(function ($items, $nome) {
                $quantidade = $items->count();
                $total = $items->sum('valor_total');

                return [
                    'nome' => $nome,
                    'quantidade' => $quantidade,
                    'total' => $total,
                    'ticket_medio' => $quantidade > 0 ? $total / $quantidade : 0,
                ];
            })
            ->values();

        // Soma geral para calcular percentual
        $totalGeral = $agrupado->sum('total');

        // Adiciona percentual para cada passageiro
        $resultado = $agrupado->map(function ($item) use ($totalGeral) {
            $item['percentual'] = $totalGeral > 0 ? ($item['total'] / $totalGeral) * 100 : 0;
            return $item;
        });

        // Retorna para view
        return view('bi.indicadores._indicador_viajantes', ['viajantes' => $resultado]);
    }
}
