@extends('layouts.app')

@section('head')
    <meta http-equiv="refresh" content="30">
@endsection

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">📊 Acompanhamento da Receita Recorrente Corporativa</h2>

    <div class="row">
        @foreach($metas as $mes => $meta)
            @php
                $realizada = $realizadas[$mes] ?? 0;
                $faturamento = $faturamento[$mes] ?? 0;
                $diferenca = $realizada - $meta;
                $porcentagem = min(100, round(($realizada / $meta) * 100));
                $mesFormatado = \Carbon\Carbon::parse($mes . '-01')->format('F Y');
                $temReceita = isset($realizada) && collect($realizada)->sum() > 0;

                if ($porcentagem >= 100) {
                    $status = ['class' => 'bg-success', 'texto' => '✅ Acima da Meta'];
                } elseif ($porcentagem >= 90) {
                    $status = ['class' => 'bg-warning', 'texto' => '⚠️ Quase lá'];
                } else {
                    $status = ['class' => 'bg-danger', 'texto' => '❌ Abaixo da Meta'];
                }
            @endphp
            @if ($temReceita)
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header {{ $status['class'] }} text-white">
                        {{ $mesFormatado }} – {{ $status['texto'] }}
                    </div>
                    <div class="card-body">
                        <p><strong>Meta de Receita:</strong> R$ {{ number_format($meta, 2, ',', '.') }}</p>
                        <p><strong>Receita Realizada:</strong> R$ {{ number_format($realizada, 2, ',', '.') }}</p>
                        <p><strong>Faturamento:</strong> R$ {{ number_format($faturamento, 2, ',', '.') }}</p>
                        <p><strong>Falta:</strong> 
                            R$ {{ number_format(max(0, $meta - $realizada), 2, ',', '.') }} de receita
                        </p>
                        <div class="progress mt-3" style="height: 20px;">
                            <div class="progress-bar {{ $status['class'] }}" role="progressbar" style="width: {{ $porcentagem }}%;" aria-valuenow="{{ $porcentagem }}" aria-valuemin="0" aria-valuemax="100">
                                {{ $porcentagem }}%
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex gap-2">
                        <a href="{{ route('dashboard.receita.ranking', ['mes' => $mes]) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-list-ol"></i> Ver Ranking
                        </a>
                        <a href="{{ route('gerenciamento.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Voltar
                        </a>
                    </div>
                </div>
            </div>
            @endif
        @endforeach
    </div>
</div>
@endsection
