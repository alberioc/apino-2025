@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Gerenciar Pagantes do Grupo: <strong>{{ $group->name }}</strong></h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-4">
        {{-- Coluna da esquerda: Adicionar pagantes --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Adicionar Pagantes</div>
                <div class="card-body">
                    <form action="{{ route('company-groups.pagantes.store', $group->id) }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="pagantes" class="form-label">Selecione as empresas:</label>
                            <select name="pagantes[]" id="pagantes" class="form-select" multiple style="width: 250px;">
                                @foreach($pagantes as $p)
                                    <option value="{{ $p->pagante }}">{{ $p->pagante }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button class="btn btn-primary" type="submit">Adicionar</button>
                        <a href="{{ route('company-groups.index') }}" class="btn btn-secondary">Voltar</a>
                    </form>
                </div>
            </div>
        </div>

        {{-- Coluna da direita: Pagantes já associados --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Pagantes Associados</div>
                <div class="card-body">
                    @if($associados->isEmpty())
                        <p class="text-muted">Nenhum pagante associado ainda.</p>
                    @else
                        <ul class="list-group">
                            @foreach($associados as $assoc)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ $assoc->pagante }}
                                    <form action="{{ route('company-groups.pagantes.destroy', [$group->id, $assoc->pagante]) }}" method="POST" onsubmit="return confirm('Remover este pagante?')" class="ms-2">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger">Remover</button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#pagantes').select2({
            placeholder: "Selecione empresas",
            allowClear: true,
            width: 'resolve'
        });
    });
</script>
@endpush
