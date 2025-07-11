@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4><i class="fas fa-check-circle text-success"></i> Planilha processada com sucesso!</h4>

    <div class="alert alert-info mt-3">
        As colunas <strong>Trecho</strong> e <strong>Destino</strong> foram preenchidas conforme as regras.
    </div>

    <a href="{{ route('planilhas.download', ['arquivo' => $arquivo]) }}" class="btn btn-primary">
        <i class="fas fa-download"></i> Baixar planilha processada
    </a>
</div>
@endsection
