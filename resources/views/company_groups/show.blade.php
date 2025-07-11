@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Detalhes do Grupo</h1>

    <p><strong>ID:</strong> {{ $companyGroup->id }}</p>
    <p><strong>Nome:</strong> {{ $companyGroup->name }}</p>
    <p><strong>Criado em:</strong> {{ $companyGroup->created_at->format('d/m/Y H:i') }}</p>
    <p><strong>Atualizado em:</strong> {{ $companyGroup->updated_at->format('d/m/Y H:i') }}</p>

    <a href="{{ route('company-groups.index') }}" class="btn btn-secondary">Voltar</a>
</div>
@endsection
