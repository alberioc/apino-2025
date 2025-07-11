@extends('layouts.app')

@section('title', 'Relatório de Emissões de Carbono')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" />
@endpush

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Relatório de Emissões de Carbono - {{ auth()->user()->companyGroup->name ?? 'Sem grupo' }}
    </h2>
@endsection

@section('content')
    @if($inicio && $fim)
        <p>Período selecionado: <strong>{{ \Carbon\Carbon::parse($inicio)->format('d/m/Y') }}</strong> até <strong>{{ \Carbon\Carbon::parse($fim)->format('d/m/Y') }}</strong></p>
    @elseif($inicio)
        <p>Período a partir de: <strong>{{ \Carbon\Carbon::parse($inicio)->format('d/m/Y') }}</strong></p>
    @elseif($fim)
        <p>Período até: <strong>{{ \Carbon\Carbon::parse($fim)->format('d/m/Y') }}</strong></p>
    @else
        <p>Período: <strong>Últimos 3 meses</strong></p>
    @endif
    
    <form method="GET" class="row g-3 align-items-end mb-4">

        <div class="col">
            <label for="inicio" class="form-label">Data Início</label>
            <input type="date" name="inicio" id="inicio" class="form-control" value="{{ request('inicio') }}">
        </div>

        <div class="col">
            <label for="fim" class="form-label">Data Fim</label>
            <input type="date" name="fim" id="fim" class="form-control" value="{{ request('fim') }}">
        </div>

        <div class="col-6">
            <label for="pagantes" class="form-label">Empresas do Grupo</label>
            <select name="pagantes[]" id="pagantes" class="form-select" multiple style="width: 250px;">
                @foreach($pagantesDoGrupo as $pagante)
                    <option value="{{ $pagante }}" {{ collect(request('pagantes'))->contains($pagante) ? 'selected' : '' }}>
                        {{ $pagante }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col">
            <button type="submit" class="btn btn-primary">Filtrar</button>
        </div>

    </form>

    <div class="alert alert-light" role="alert">
        <p class="text-muted small">
            Fórmula: (Distância KM / KM por minuto) × CO₂ por minuto<br>
            Distância KM: site <a href="https://www.owlsnest.eu" target="_blank">www.owlsnest.eu</a><br>
            KM por minuto: 670 ÷ 60 = 11,17<br>
            CO₂ por minuto: 50 ÷ 60 = 0,83 kg por passageiro
        </p>
    </div>

    <div class="mb-4">
        <h5>Total de Emissões: {{ number_format($totalEmission, 2, ',', '.') }} kg CO₂</h5>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-success text-white d-flex align-items-center">
            <i class="fas fa-leaf fa-lg me-2"></i>
            <strong>Relatório de Emissão de CO₂</strong>
        </div>
        <div class="card-body">
        <div class="table-responsive">
            <table id="carbonTable" class="table table-sm table-striped table-bordered align-middle small">
                <thead class="table-light">
                    <tr>
                        <th>Empresa</th>
                        <th>Passageiro(s)</th>
                        <th>Localizador</th>
                        <th>Fornecedor</th>
                        <th>Trechos</th>
                        <th>Distância Total (KM)</th>
                        <th>Partida</th>
                        <th>Chegada</th>
                        <th>Emissão de CO₂ (kg)</th>
                        <th>Data da Venda</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vendas as $venda)
                        <tr>
                            <td>{{ $venda->pagante }}</td>
                            <td>{{ $venda->passageiros }}</td>
                            <td>{{ $venda->documento }}</td>
                            <td>{{ $venda->fornecedor }}</td>
                            <td>{{ $venda->trechos }}</td>
                            <td>{{ number_format($venda->distancia_km, 2, ',', '.') }} km</td>
                            <td>{{ \Carbon\Carbon::parse($venda->data_inicio)->format('d/m/Y') }} {{ $venda->hora_inicio }}</td>
                            <td>{{ \Carbon\Carbon::parse($venda->data_fim)->format('d/m/Y') }} {{ $venda->hora_fim }}</td>
                            <td>{{ number_format($venda->emissao_calculada, 2, ',', '.') }}</td>
                            <td>{{ \Carbon\Carbon::parse($venda->data_venda)->format('d/m/Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<!-- Buttons extension -->
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>

<!-- Export libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<!-- Buttons HTML5 export -->
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>

<!-- Buttons print -->
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

@php
    $inicioFormatado = request('inicio') ? \Carbon\Carbon::parse(request('inicio'))->format('d/m/Y') : 'Início';
    $fimFormatado = request('fim') ? \Carbon\Carbon::parse(request('fim'))->format('d/m/Y') : 'Fim';
@endphp

<script>
$(document).ready(function () {
    const mensagemTopo = "Total de Emissões: {{ number_format($totalEmission, 2, ',', '.') }} kg CO2 | Período: {{ $inicioFormatado }} até {{ $fimFormatado }}";
    
    $('#carbonTable').DataTable({
        ordering: true,
        responsive: true,
        language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json" },
        dom: 'Bfrtip',
        buttons: [
            { extend: 'copyHtml5', text: 'Copiar', className: 'btn btn-secondary', messageTop: mensagemTopo },
            { extend: 'excelHtml5', text: 'Excel', className: 'btn btn-success', messageTop: mensagemTopo },
            { extend: 'csvHtml5', text: 'CSV', className: 'btn btn-info', messageTop: mensagemTopo },
            {
                extend: 'pdfHtml5',
                text: 'PDF',
                className: 'btn btn-danger',
                messageTop: mensagemTopo,
                orientation: 'landscape', // <= aqui!
                pageSize: 'A4',
                exportOptions: {
                    columns: ':visible'
                }
            },
            { extend: 'print', text: 'Imprimir', className: 'btn btn-primary', messageTop: mensagemTopo }
        ],        
        order: [[9, 'desc']] // coluna 9 (índice 8), ordem ascendente
    });

    $('#inicio').on('change', function () {
        var min = $(this).val();
        $('#fim').attr('min', min);
    });
    $('#fim').on('change', function () {
        var max = $(this).val();
        $('#inicio').attr('max', max);
    });

    // Select2 init
    $('#pagantes').select2({
        placeholder: "Selecione empresas",
        allowClear: true,
        width: 'resolve'
    });
});
</script>
@endpush