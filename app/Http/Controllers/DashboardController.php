<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function receitaRecorrente()
    {
        $baseJulho = 69448.06; // Receita recorrente projetada para julho (ajuste se necessário)
        $metaFinal = 150000.00;
        $mesesRestantes = 5;

        $metas = [];
        $realizadas = [];

        // Cálculo da taxa composta
        $taxa = pow(($metaFinal / $baseJulho), 1 / $mesesRestantes) - 1;

        // Primeiro adiciona julho (meta é a base, pois é já conhecido)
        $metas[date('Y-m', strtotime('first day of july'))] = $baseJulho;
        $realizadas[date('Y-m', strtotime('first day of july'))] = $this->buscarReceitaCorporativaDoMes(date('Y-m', strtotime('first day of july')));
        $faturamento[date('Y-m', strtotime('first day of july'))] = $this->buscarFaturamentoCorporativoDoMes(date('Y-m', strtotime('first day of july')));

        // Agora adiciona agosto a dezembro
        $dataInicio = Carbon::create(date('Y'), 8, 1); // agosto
        $metaAtual = $baseJulho;

        for ($i = 0; $i < $mesesRestantes; $i++) {
            $metaAtual = $metaAtual * (1 + $taxa);

            $mes = $dataInicio->copy()->addMonths($i)->format('Y-m');
            $metas[$mes] = round($metaAtual, 2);
            $realizadas[$mes] = $this->buscarReceitaCorporativaDoMes($mes);
        }

        return view('painel.receita', compact('metas', 'realizadas', 'faturamento'));
    }
    
    private function buscarReceitaCorporativaDoMes(string $mes)
    {
        // Exemplo: $mes = '2025-08'
        return DB::table('vendas')
            ->where('tipo_pessoa', 'j') // ajuste esse filtro conforme sua base
            ->whereBetween('data_venda', [
                $mes . '-01',
                $mes . '-31'
            ])
            ->sum('receitas'); // ou 'receita' se tiver campo separado
    }

    private function buscarFaturamentoCorporativoDoMes(string $mes)
    {
        // Exemplo: $mes = '2025-08'
        return DB::table('vendas')
            ->where('tipo_pessoa', 'j') // ajuste esse filtro conforme sua base
            ->whereBetween('data_venda', [
                $mes . '-01',
                $mes . '-31'
            ])
            ->sum('faturamento'); // ou 'receita' se tiver campo separado
    }

    public function rankingMensal(string $mes)
    {
        $dados = DB::table('vendas')
            ->select('pagante')
            ->selectRaw('SUM(faturamento) as faturamento')
            ->selectRaw('SUM(receitas) as receita')
            ->where('tipo_pessoa', 'j')
            ->whereBetween('data_venda', [
                $mes . '-01',
                $mes . '-31'
            ])
            ->groupBy('pagante')
            ->orderByDesc('faturamento')
            ->get();

        // 🔢 Total da receita do mês para calcular percentual
        $totalReceita = $dados->sum('receita');

        // 📅 Data de corte: mês anterior ao mês do ranking
        $inicioUltimoMes = \Carbon\Carbon::parse($mes . '-01')->subMonth()->startOfMonth();
        $fimUltimoMes = $inicioUltimoMes->copy()->endOfMonth();

        \Log::info('Verificando compras entre: ' . $inicioUltimoMes->toDateString() . ' e ' . $fimUltimoMes->toDateString());

        // 🏷️ Adiciona posição, % e ativo
        $dadosRankeados = $dados->map(function ($item, $index) use ($totalReceita, $inicioUltimoMes, $fimUltimoMes) {
            $ativo = DB::table('vendas')
                ->where('tipo_pessoa', 'j')
                ->where('pagante', $item->pagante)
                ->whereBetween('data_venda', [$inicioUltimoMes, $fimUltimoMes])
                ->exists();

            return [
                'posicao' => $index + 1,
                'pagante' => $item->pagante,
                'faturamento' => $item->faturamento,
                'receita' => $item->receita,
                'percentual' => $totalReceita > 0 ? ($item->receita / $totalReceita) * 100 : 0,
                'ativo' => $ativo,
            ];
        });

        $mesFormatado = \Carbon\Carbon::parse($mes . '-01')->translatedFormat('F Y');

        return view('painel.ranking', [
            'mes' => $mesFormatado,
            'dados' => $dadosRankeados,
        ]);
    }

}
