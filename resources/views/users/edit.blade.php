@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Usuário: {{ $user->name }}</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="name" class="form-label">Nome</label>
            <input type="text" class="form-control" name="name" value="{{ old('name', $user->name) }}" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">E-mail</label>
            <input type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}" required>
        </div>

        <div class="mb-3">
            <label for="company_group" class="form-label">Grupo</label>
            <select name="company_group" class="form-select" required>
                <option value="">Selecione um grupo</option>
                @foreach($companyGroups as $group)
                    <option value="{{ $group->id }}" {{ (old('company_group', $user->company_group) == $group->id) ? 'selected' : '' }}>
                        {{ $group->name }} ({{ $group->pagantes_count }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="role" class="form-label">Perfil de Acesso</label>
            <select name="role" id="role" class="form-select" required>
                @foreach($roles as $key => $label)
                    <option value="{{ $key }}" {{ (old('role', $user->role) == $key) ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Nova Senha (deixe em branco para manter a atual)</label>
            <input type="password" class="form-control" name="password">
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirmar Nova Senha</label>
            <input type="password" class="form-control" name="password_confirmation">
        </div>

        <button class="btn btn-primary">Atualizar</button>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
