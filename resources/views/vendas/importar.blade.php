@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Importar Planilha de Vendas</h2>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-4">
        <a href="{{ route('vendas.importar.status') }}" class="btn btn-outline-secondary">
            <i class="fas fa-list me-1"></i> Ver status das importações

            @if($pendentes > 0)
                <span class="badge bg-warning text-dark ms-2">{{ $pendentes }} pendente{{ $pendentes > 1 ? 's' : '' }}</span>
            @endif

            @if($processando > 0)
                <span class="badge bg-primary ms-2">{{ $processando }} processando{{ $processando > 1 ? 's' : '' }}</span>
            @endif

            @if($sucesso > 0)
                <span class="badge bg-success ms-2">{{ $sucesso }} sucesso{{ $sucesso > 1 ? 's' : '' }}</span>
            @endif

            @if($erros > 0)
                <span class="badge bg-danger ms-1">{{ $erros }} erro{{ $erros > 1 ? 's' : '' }}</span>
            @endif
        </a>
    </div>

    <form method="POST" action="{{ route('vendas.importar.enviar') }}" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label for="arquivo" class="form-label">Escolha a planilha (.xlsx ou .csv)</label>
            <input type="file" name="arquivo" id="arquivo" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-upload me-1"></i> Enviar e Importar
        </button>

        <a href="{{ route('gerenciamento.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </form>
</div>
@endsection
