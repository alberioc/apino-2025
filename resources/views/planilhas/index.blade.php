@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4><i class="fas fa-file-excel"></i> Importar Planilha de Vendas</h4>
    <form action="{{ route('planilhas.processar') }}" method="POST" enctype="multipart/form-data" class="mt-3">
        @csrf
        <div class="mb-3">
            <label for="arquivo" class="form-label">Selecione o arquivo Excel (.xlsx)</label>
            <input type="file" name="arquivo" id="arquivo" class="form-control" accept=".xlsx,.xls" required>
        </div>
        <button type="submit" class="btn btn-success">
            <i class="fas fa-upload"></i> Enviar e Processar
        </button>
    </form>
</div>
@endsection
