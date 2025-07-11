@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Criar Novo Grupo</h1>

    <form action="{{ route('company-groups.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Nome do Grupo</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button class="btn btn-success" type="submit">Salvar</button>
        <a href="{{ route('company-groups.index') }}" class="btn btn-secondary">Voltar</a>
    </form>
</div>
@endsection
