@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Detalhes do Usuário: {{ $user->name }}</h1>

    <div class="card mb-3">
        <div class="card-header">Informações Básicas</div>
        <div class="card-body">
            <p><strong>ID:</strong> {{ $user->id }}</p>
            <p><strong>Nome:</strong> {{ $user->name }}</p>
            <p><strong>E-mail:</strong> {{ $user->email }}</p>
            <p><strong>Grupo:</strong> {{ $user->companyGroup ? $user->companyGroup->name : '—' }}</p>
            <p><strong>Perfil:</strong> {{ ucfirst($user->role) }}</p>
            <p><strong>Criado em:</strong> {{ $user->created_at->format('d/m/Y H:i') }}</p>
            <p><strong>Última atualização:</strong> {{ $user->updated_at->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning">Editar</a>
    <a href="{{ route('users.index') }}" class="btn btn-secondary">Voltar</a>
</div>
@endsection
