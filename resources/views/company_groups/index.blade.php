@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Grupos de Empresas</h1>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="d-flex gap-2 mb-3">
        <a href="{{ route('company-groups.create') }}" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-plus"></i> Novo Grupo
        </a>

        <a href="{{ route('gerenciamento.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>    

    @if($groups->count())
    <table id="company-groups-table" class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Empresas Associadas</th>
                <th>Criado em</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($groups as $group)
            <tr>
                <td>{{ $group->id }}</td>
                <td>{{ $group->name }}</td>
                <td>{{ $group->pagantes_count }}</td>
                <td>{{ $group->created_at->format('d/m/Y H:i') }}</td>
                <td>
                    <a href="{{ route('company-groups.edit', $group) }}" class="btn btn-sm btn-warning">Editar</a>
                    <a href="{{ route('company-groups.pagantes', $group->id) }}" class="btn btn-sm btn-secondary">
                        Adicionar Pagantes
                    </a>
                    <form action="{{ route('company-groups.destroy', $group) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Deseja realmente excluir?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">Excluir</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @else
    <p>Nenhum grupo cadastrado.</p>
    @endif
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#company-groups-table').DataTable({
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json"
            }
        });
    });
</script>
@endpush