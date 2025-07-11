@extends('layouts.app')

@section('title', 'Importar Empresas Monde')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Importar Empresas Monde</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form id="import-form" method="POST" action="{{ route('empresa_monde.import') }}" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label for="file" class="form-label">Selecione a planilha Excel (.xlsx):</label>
            <input type="file" name="file" id="file" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Importar</button>
    </form>

    <div id="import-feedback" class="mt-4" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Importando...</span>
        </div>
        <span class="ms-2">Importando dados, por favor aguarde...</span>
    </div>

    <div class="progress" style="height: 25px; display:none;" id="progressBarContainer">
        <div id="progressBar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuemin="0" aria-valuemax="100">
            0%
        </div>
    </div>

</div>

<script>
    document.getElementById('import-form').addEventListener('submit', function () {
        document.getElementById('import-feedback').style.display = 'block';
    });
</script>
@endsection
