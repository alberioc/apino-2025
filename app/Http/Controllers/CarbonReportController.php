<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Carbon;
use App\Services\AirportService;
use Carbon\Carbon as CarbonDate; // a lib Carbon com alias

class CarbonReportController extends Controller
{
    protected $airportService;

    public function __construct(AirportService $airportService)
    {
        $this->airportService = $airportService;
    }

    private function gerarParesTrechos(string $trechos): array
    {
        $aeroportos = explode('-', $trechos);
        $pares = [];

        for ($i = 0; $i < count($aeroportos) - 1; $i++) {
            $pares[] = [$aeroportos[$i], $aeroportos[$i + 1]];
        }

        return $pares;
    }

    public function index(Request $request)
    {
        // Obtem o Company Group do Usuário
        $companyGroupId = auth()->user()->company_group;

        // Obtem os pagantes do Company Group do Usuário
        $pagantesDoGrupo = DB::table('company_group_venda')
            ->where('company_group_id', $companyGroupId)
            ->pluck('pagante'); // retorna array com nomes de empresas
        
        // Filtro por data_venda (se informado)
        $query = Carbon::where('produto', 'Passagem Aérea')
            ->whereIn('pagante', $pagantesDoGrupo);        

        if ($request->filled('inicio') && $request->filled('fim')) {
            $query->whereDate('data_venda', '>=', $request->inicio);
            $query->whereDate('data_venda', '<=', $request->fim);
            $inicio = $request->inicio;
            $fim = $request->fim;
        } else {
            $inicio = CarbonDate::now()->subMonths(3)->startOfMonth()->toDateString();
            $fim = CarbonDate::now()->endOfMonth()->toDateString();

            $query->whereDate('data_venda', '>=', $inicio);
            $query->whereDate('data_venda', '<=', $fim);
        }

        if ($request->filled('pagantes')) {
            $query->whereIn('pagante', $request->input('pagantes'));
        }

        // Garantir ordenação por data_venda DESC
        $vendas = $query->orderBy('data_venda', 'desc')->get();

        $totalEmission = 0;

        foreach ($vendas as $venda) {
            $pares = $this->gerarParesTrechos($venda->trechos);

            $distanciaTotal = 0;

            foreach ($pares as [$origem, $destino]) {
                $coordOrigem = $this->airportService->getCoordinates($origem);
                $coordDestino = $this->airportService->getCoordinates($destino);

                if ($coordOrigem && $coordDestino) {
                    $distancia = $this->airportService->haversineDistance($coordOrigem, $coordDestino);
                    $distanciaTotal += $distancia;
                }
            }

            // Fórmula: (Distância / 11.17) * 0.83 * passageiros
            $passageiros = max(1, intval($venda->passageiros)); // fallback para 1 se vazio ou inválido
            $tempoMin = $distanciaTotal / 11.17;
            $co2PorPassageiro = $tempoMin * 0.83;
            $emissaoTotal = $co2PorPassageiro * $passageiros;

            $venda->distancia_km = round($distanciaTotal, 1);
            $venda->emissao_calculada = round($emissaoTotal, 2);

            $totalEmission += $emissaoTotal;
        }

        return view('carbono.report', [
            'vendas' => $vendas,
            'totalEmission' => $totalEmission,
            'inicio' => $request->input('inicio'),
            'fim' => $request->input('fim'),
            'pagantesDoGrupo' => $pagantesDoGrupo, // ← aqui
        ]);
    }
}
