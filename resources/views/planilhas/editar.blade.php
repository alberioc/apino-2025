@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Editar Dados da Planilha</h2>
    <form action="{{ route('planilhas.salvar') }}" method="POST">
        @csrf
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th>Venda Nº</th>
                    <th>Data Venda</th>
                    <th>Produto</th>
                    <th>Documento</th>
                    <th>Fornecedor</th>
                    <th>Data Início</th>
                    <th>Data Fim</th>
                    <th>Trecho</th>
                    <th>Origem</th>
                    <th>Destino</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dados as $index => $linha)
                <tr>
                    <td>{{ $linha['venda_numero'] }}</td>
                    <td>{{ $linha['data_venda'] }}</td>
                    <td>{{ $linha['produto'] }}</td>
                    <td>{{ $linha['documento'] }}</td>
                    <td>{{ $linha['fornecedor'] }}</td>
                    <td>{{ $linha['data_inicio'] }}</td>
                    <td>{{ $linha['data_fim'] }}</td>
                    <td>{{ $linha['trecho'] }}</td>

                    {{-- Campos editáveis --}}
                    <td><input type="text" name="dados[{{ $index }}][origem]" value="{{ $linha['origem'] }}" class="form-control form-control-sm"></td>
                    <td><input type="text" name="dados[{{ $index }}][destino]" value="{{ $linha['destino'] }}" class="form-control form-control-sm"></td>

                    {{-- Campos fixos para envio --}}
                    <input type="hidden" name="dados[{{ $index }}][venda_numero]" value="{{ $linha['venda_numero'] }}">
                    <input type="hidden" name="dados[{{ $index }}][data_venda]" value="{{ $linha['data_venda'] }}">
                    <input type="hidden" name="dados[{{ $index }}][produto]" value="{{ $linha['produto'] }}">
                    <input type="hidden" name="dados[{{ $index }}][documento]" value="{{ $linha['documento'] }}">
                    <input type="hidden" name="dados[{{ $index }}][fornecedor]" value="{{ $linha['fornecedor'] }}">
                    <input type="hidden" name="dados[{{ $index }}][centro_custo]" value="{{ $linha['centro_custo'] }}">
                    <input type="hidden" name="dados[{{ $index }}][valor]" value="{{ $linha['valor'] }}">
                    <input type="hidden" name="dados[{{ $index }}][data_inicio]" value="{{ $linha['data_inicio'] }}">
                    <input type="hidden" name="dados[{{ $index }}][data_fim]" value="{{ $linha['data_fim'] }}">
                </tr>
                @endforeach
            </tbody>
        </table>

        <button type="submit" class="btn btn-primary">Gerar Planilha para Download</button>
    </form>
</div>
@endsection
