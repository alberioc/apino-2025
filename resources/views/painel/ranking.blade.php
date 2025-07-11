@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>
        🏆 Ranking de Clientes Corporativos – {{ $mes }} 
        <small class="text-muted">(Total de Receitas: R$ {{ number_format($dados->sum('receita'), 2, ',', '.') }})</small>
    </h2>

    <a href="{{ route('dashboard.receitaRecorrente') }}" class="btn btn-sm btn-outline-secondary my-3">
        <i class="fas fa-arrow-left"></i> Voltar
    </a>

    {{-- Tabela --}}
    <div class="table-responsive">
        <table class="table table-sm table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th style="width: 80px;">#</th>
                    <th>Pagante</th>
                    <th>Faturamento</th>
                    <th>Receita</th>
                    <th>% Receita</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalFaturamento = 0;
                    $totalReceita = 0;
                @endphp

                @forelse ($dados as $cliente)
                    @php
                        $totalFaturamento += $cliente['faturamento'];
                        $totalReceita += $cliente['receita'];
                    @endphp
                    <tr>
                        <td>
                            {{ $cliente['posicao'] }}
                            @if($cliente['ativo'])
                                <i class="fas fa-star text-warning" title="Cliente ativo no último mês"></i>
                            @else
                                <i class="far fa-star text-muted" title="Sem compras recentes"></i>
                            @endif
                        </td>
                        <td>                            
                            {{ $cliente['pagante'] }}
                        </td>
                        <td>R$ {{ number_format($cliente['faturamento'], 2, ',', '.') }}</td>
                        <td>R$ {{ number_format($cliente['receita'], 2, ',', '.') }}</td>
                        <td>{{ number_format($cliente['percentual'], 0, ',', '.') }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">Nenhum dado disponível para este mês.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="2" class="text-end">TOTAL</td>
                    <td>R$ {{ number_format($totalFaturamento, 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($totalReceita, 2, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Gráfico de barras horizontais --}}
    <div class="row mb-4">
        <div class="col-md-10 offset-md-1">
            <canvas id="graficoBarras"></canvas>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    const ctx = document.getElementById('graficoBarras');
    if (!ctx) {
        console.log("Canvas não encontrado");
        return;
    }

    const pagantes = {!! json_encode($dados->pluck('pagante')->filter()->values()) !!};
    const receitas = {!! json_encode($dados->pluck('receita')->map(function($v) { return round($v, 2); })->values()) !!};

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: pagantes,
            datasets: [{
                label: 'Receita por Pagante',
                data: receitas,
                backgroundColor: '#4caf50',
                borderRadius: 5,
                barPercentage: 0.6,
                categoryPercentage: 0.6,
            }]
        },
        options: {
            indexAxis: 'y', // barras horizontais
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let val = context.parsed.x || context.parsed.y || 0;
                            return 'R$ ' + val.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(val) {
                            return 'R$ ' + val.toLocaleString('pt-BR');
                        }
                    }
                },
                y: {
                    ticks: {
                        autoSkip: false,
                        maxRotation: 0,
                        minRotation: 0,
                    }
                }
            }
        }
    });
});
</script>
@endpush
