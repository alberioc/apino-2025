@extends('layouts.app')

@section('head')
    <meta http-equiv="refresh" content="60">
@endsection

@section('content')
<div class="container">
    <h1 class="mb-4">Status das Importações</h1>

    <table class="table table-bordered table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>Arquivo</th>
                <th>Status</th>
                <th>Linhas Processadas</th>
                <th>Erro</th>
                <th>Início</th>
                <th>Término</th>
                <th>Duração</th>
                <th>Enviado em</th>
                <th>Atualizado em</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($importacoes as $importacao)
                <tr>
                    <td>{{ $importacao->nome_arquivo }}</td>
                    <td>
                        @php
                            $badge = match($importacao->status) {
                                'pendente' => 'warning',
                                'processando' => 'primary',
                                'sucesso' => 'success',
                                'erro' => 'danger',
                                default => 'secondary',
                            };
                        @endphp
                        <span class="badge bg-{{ $badge }}">{{ ucfirst($importacao->status) }}</span>
                    </td>
                    <td>{{ $importacao->linhas_processadas ?? '-' }}</td>
                    <td>
                        @if ($importacao->mensagem_erro)
                            <span class="text-danger" title="{{ $importacao->mensagem_erro }}">
                                {{ \Illuminate\Support\Str::limit($importacao->mensagem_erro, 40) }}
                            </span>
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $importacao->iniciado_em ? $importacao->iniciado_em->format('d/m/Y H:i:s') : '-' }}</td>
                    <td>{{ optional($importacao->processado_em)->format('d/m/Y H:i:s') ?? '-' }}</td>
                    <td>
                        @if ($importacao->iniciado_em && $importacao->processado_em)
                            {{ $importacao->iniciado_em->diffForHumans($importacao->processado_em, true) }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $importacao->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $importacao->updated_at->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Nenhuma importação registrada ainda.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <a href="{{ route('vendas.importar.form') }}" class="btn btn-secondary">← Voltar</a>
</div>
@endsection
