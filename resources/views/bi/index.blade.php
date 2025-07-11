@extends('layouts.app')

@section('title', 'APINO | BI - Vendas')

@section('header')
    <h3 class="font-semibold text-xl text-gray-800 leading-tight">
        Business Intelligence | {{ auth()->user()->companyGroup->name ?? 'Sem grupo' }}
    </h3>
@endsection

@section('content')
<div class="container py-3">
        @if($inicio && $fim)
            <p>Período selecionado: <strong>{{ \Carbon\Carbon::parse($inicio)->format('d/m/Y') }}</strong> até <strong>{{ \Carbon\Carbon::parse($fim)->format('d/m/Y') }}</strong></p>
        @elseif($inicio)
            <p>Período a partir de: <strong>{{ \Carbon\Carbon::parse($inicio)->format('d/m/Y') }}</strong></p>
        @elseif($fim)
            <p>Período até: <strong>{{ \Carbon\Carbon::parse($fim)->format('d/m/Y') }}</strong></p>
        @else
            <p>Período: <strong>Últimos 3 meses</strong></p>
        @endif

    {{-- Filtros --}}
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
            <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
        </div>

    </form>

    {{-- Indicadores --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary h-100">
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-shopping-cart fa-2x me-3"></i>
                    <div>
                        <h6 class="card-title mb-0">Total de Vendas</h6>
                        <span class="fs-5">{{ $totalVendas }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-info h-100">
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-dollar-sign fa-2x me-3"></i>
                    <div>
                        <h6 class="card-title mb-0">Valor Total</h6>
                        <span class="fs-5">R$ {{ number_format($valorTotal, 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-warning h-100">
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-receipt fa-2x me-3"></i>
                    <div>
                        <h6 class="card-title mb-0">Ticket Médio</h6>
                        <span class="fs-5">R$ {{ number_format($ticketMedio, 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Gráfico: Vendas dos Últimos 12 Meses --}}
    <div class="card mb-4">
        <div class="card-header bg-info text-white d-flex align-items-center">
            <i class="fas fa-chart-line fa-lg me-2"></i>
            <strong>Desempenho dos últimos 12 meses</strong>
        </div>
        <div class="card-body">
            <canvas id="vendas12MesesChart" height="120"></canvas>
        </div>
    </div>

    {{-- Comparativo Últimos 4 Anos --}}
    <div class="card mb-4">
        <div class="card-header bg-info text-white d-flex align-items-center">
            <i class="fas fa-circle-dollar-to-slot fa-lg me-2"></i>
            <strong>Comparativo Ano a Ano</strong>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach ($anosComparativo as $anoData)
                    <div class="col-md-3 mb-3">
                        <div class="card card-sm shadow-sm">
                            <div class="card-body text-center">
                                <small class="fw-bold">
                                    <i class="fas fa-calendar-days"></i> {{ $anoData['ano'] }}
                                </small>
                                <div class="row border-info border-bottom mt-2 mb-2">
                                    <div class="col-sm">
                                        <small class="fw-bold">Volume</small><br>
                                        <small>R$ {{ number_format($anoData['valor_total'], 2, ',', '.') }}</small>
                                    </div>
                                </div>
                                <div class="row border-info border-bottom mb-2">
                                    <div class="col-sm">
                                        <small>Processos</small><br>
                                        <small class="fw-bold">{{ $anoData['quantidade'] }}</small>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm">
                                        <small>Valor Médio</small><br>
                                        <small class="fw-bold">R$ {{ number_format($anoData['ticket_medio'], 2, ',', '.') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

        {{-- Insight Automático --}}
        <div class="card border-start border-4 border-primary shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="fas fa-lightbulb text-warning"></i> Insight de Evolução
                </h5>
                <p class="card-text">
                    @if (!empty($insightAnoComparativo))
                        {{ $insightAnoComparativo }}
                    @else
                        Os dados dos últimos anos mostram uma evolução consistente. Continue monitorando os principais clientes e incentive os centros de custo com maior potencial.
                    @endif
                </p>
            </div>
        </div>


    {{-- Indicadores por Produto --}}
    <div class="card mb-4">
        <div class="card-header bg-info text-white d-flex align-items-center">
            <i class="fas fa-boxes fa-lg me-2"></i>
            <strong>Indicadores por Produto</strong>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach ($produtosData as $produtoData)
                    <div class="col-md-3 mb-3">
                        <div class="card card-sm mb-3" data-produto="{{ $produtoData['produto'] }}">
                            <div class="card-body text-center">
                                <small class="fw-bold">{{ $produtoData['produto'] }}</small>
                                <div class="row border-info border-bottom mt-2 mb-2">
                                    <div class="col-sm">
                                        <small>Valor Total</small><br>
                                        <small class="fw-bold">R$ {{ number_format($produtoData['valor_total'], 2, ',', '.') }}</small>
                                    </div>
                                </div>
                                <div class="row border-info border-bottom mb-2">
                                    <div class="col-sm">
                                        <small>Quantidade</small><br>
                                        <small class="fw-bold">{{ $produtoData['quantidade'] }}</small>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-sm">
                                        <small>Ticket Médio</small><br>
                                        <small class="fw-bold">R$ {{ number_format($produtoData['ticket_medio'], 2, ',', '.') }}</small>
                                    </div>
                                </div>
                                <button 
                                    type="button" 
                                    class="btn btn-primary btn-sm" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#detalhesProdutoModal"
                                    onclick="mostrarDetalhesProduto('{{ $produtoData['produto'] }}')"
                                >
                                    Ver Detalhes
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach                    
            </div>            
        </div>        
    </div>
    {{-- FModal de produtos --}}
    <div class="modal fade" id="detalhesProdutoModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Detalhes do Produto: <span id="nomeProduto"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="conteudoProdutoDetalhes">
                    <!-- Carregado dinamicamente -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Solicitantes --}}
    <div class="card mb-4">
        <div class="card-header bg-info text-white d-flex align-items-center">
            <i class="fas fa-users fa-lg me-2"></i>
            <strong>Solicitantes - Volume e Share</strong>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabelaSolicitantes" class="table table-sm table-striped table-bordered align-middle small">
                    <thead class="table-light">
                        <tr>
                            <th>Solicitante</th>
                            <th>Volume Total (R$)</th>
                            <th>Quantidade de Processos</th>
                            <th>Share (%)</th>
                            <th>Valor Médio do Processo (R$)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($solicitantesData as $solicitante)
                        <tr>
                            <td>{{ $solicitante->solicitante }}</td>
                            <td>{{ number_format($solicitante->valor_total, 2, ',', '.') }}</td>
                            <td>{{ $solicitante->quantidade }}</td>
                            <td>{{ $solicitante->share }}%</td>
                            <td>{{ number_format($solicitante->valor_medio, 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Tabela de Vendas --}}
    <div class="card mb-4">
        <div class="card-header bg-info text-white d-flex align-items-center">
            <i class="fas fa-table fa-lg me-2"></i>
            <strong>Tabela de Vendas</strong>
        </div>
        <div class="card-body p-2">
            <div class="table-responsive">
                <table id="vendasTable" class="table table-sm table-bordered table-hover table-striped align-middle small">
                    <thead class="table-light text-center">
                        <tr>
                            <th class="text-nowrap">Nº</th>
                            <th class="text-nowrap">Data</th>
                            <th class="text-nowrap">Pagante</th>
                            <th class="text-nowrap">Produto</th>
                            <th class="text-nowrap">Fornecedor</th>
                            <th class="text-nowrap">Saida</th>
                            <th class="text-nowrap">Retorno</th>
                            <th class="text-nowrap">Solicitante</th>
                            <th class="text-nowrap">Valor R$</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($vendas as $venda)
                            <tr class="linha-venda" data-venda='@json($venda)'>
                                <td class="text-nowrap">{{ $venda->venda_numero }}</td>
                                <td class="text-nowrap">{{ \Carbon\Carbon::parse($venda->data_venda)->format('d/m/Y') }}</td>
                                <td class="text-nowrap">{{ $venda->pagante }}</td>
                                <td class="text-nowrap">{{ $venda->produto }}</td>
                                <td class="text-nowrap">{{ $venda->fornecedor }}</td>
                                <td class="text-nowrap">{{ \Carbon\Carbon::parse($venda->data_inicio)->format('d/m/Y') }}</td>
                                <td class="text-nowrap">{{ \Carbon\Carbon::parse($venda->data_fim)->format('d/m/Y') }}</td>
                                <td class="text-end">{{ $venda->solicitante ?? '-' }}</td>
                                <td class="text-end">{{ number_format($venda->valor_total ?? 0, 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Nenhuma venda encontrada.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table> 
            </div>
        </div>
    </div>
    {{-- Modal Invoice --}}
    <div id="invoiceModal" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Detalhes da Venda</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <section id="invoiceContent">
                <!-- Conteúdo será preenchido via JS -->
                </section>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
            </div>
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
    // === FORMATADORES ===
    function formatarData(dataISO) {
        if (!dataISO) return '-';

        const partes = dataISO.split('-');
        const ano = parseInt(partes[0], 10);
        const mes = parseInt(partes[1], 10) - 1; // meses em JS: 0 = jan
        const dia = parseInt(partes[2], 10);

        const data = new Date(ano, mes, dia);
        return data.toLocaleDateString('pt-BR');
    }

    function formatarHora(hora) {
        if (!hora) return '-';

        // Se vier só "19:14:59", cria uma data fictícia com isso
        const partes = hora.split(':');
        if (partes.length >= 2) {
            return `${partes[0].padStart(2, '0')}:${partes[1].padStart(2, '0')}`;
        }

        return '-';
    }

    function formatarValor(valor) {
        if (!valor) return '0,00';
        return parseFloat(valor).toLocaleString('pt-BR', {minimumFractionDigits: 2});
    }

    // === MODAL DE DETALHES POR PRODUTO ===
    function mostrarDetalhesProduto(produto) {
        $('#nomeProduto').text(produto);
        $('#conteudoProdutoDetalhes').html('<p class="text-center text-muted">⏳ Aguarde, carregando dados...</p>');

        const modalEl = document.getElementById('detalhesProdutoModal');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();

        $.get('/bi/produto-detalhes', { produto: produto }, function(response) {
            $('#conteudoProdutoDetalhes').html(response.html);

            $('#tabelaDetalhesProduto').DataTable({
                ordering: true,
                responsive: true,
                paging: true,
                searching: true,
                language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json" },
                order: [[2, 'desc']],
                columnDefs: [
                    { targets: [2], type: 'num' }
                ]
            });
        }).fail(function() {
            $('#conteudoProdutoDetalhes').html('<p class="text-danger text-center">Erro ao carregar os dados.</p>');
        });
    }

    $('#detalhesProdutoModal').on('hidden.bs.modal', function () {
        $('#conteudoProdutoDetalhes').html('');
        $('#nomeProduto').text('');
    });

    // === INICIALIZAÇÃO GERAL ===
    $(document).ready(function () {
        const mensagemTopo = 'Período: {{ $inicioFormatado }} a {{ $fimFormatado }}';

        // Inicializa DataTables em todas as tabelas exceto a de solicitantes (que tem ordenação diferente)
        $('table:not(#tabelaSolicitantes)').DataTable({
            ordering: true,
            responsive: true,
            language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json" },
            dom: 'Bfrtip',
            buttons: [
                { extend: 'copyHtml5', text: 'Copiar', className: 'btn btn-secondary', messageTop: mensagemTopo },
                { extend: 'excelHtml5', text: 'Excel', className: 'btn btn-secondary', messageTop: mensagemTopo },
                { extend: 'csvHtml5', text: 'CSV', className: 'btn btn-secondary', messageTop: mensagemTopo },
                {
                    extend: 'pdfHtml5',
                    text: 'PDF',
                    className: 'btn btn-secondary',
                    messageTop: mensagemTopo,
                    orientation: 'portrait',
                    pageSize: 'A4',
                    exportOptions: { columns: ':visible' }
                },
                { extend: 'print', text: 'Imprimir', className: 'btn btn-secondary', messageTop: mensagemTopo }
            ],
            order: [[0, 'desc']]
        });

        // Tabela de Solicitantes com ordenação na segunda coluna (índice 2)
        $('#tabelaSolicitantes').DataTable({
            order: [[2, 'desc']],
            responsive: true,
            language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json" },
            dom: 'Bfrtip',
            buttons: [
                { extend: 'copyHtml5', text: 'Copiar', className: 'btn btn-secondary', messageTop: mensagemTopo },
                { extend: 'excelHtml5', text: 'Excel', className: 'btn btn-secondary', messageTop: mensagemTopo },
                { extend: 'csvHtml5', text: 'CSV', className: 'btn btn-secondary', messageTop: mensagemTopo },
                {
                    extend: 'pdfHtml5',
                    text: 'PDF',
                    className: 'btn btn-secondary',
                    messageTop: mensagemTopo,
                    orientation: 'portrait',
                    pageSize: 'A4',
                    exportOptions: { columns: ':visible' }
                },
                { extend: 'print', text: 'Imprimir', className: 'btn btn-secondary', messageTop: mensagemTopo }
            ]
        });

        // Select2
        $('#pagantes').select2({
            placeholder: "Selecione empresas",
            allowClear: true,
            width: 'resolve'
        });

        // Limita datas
        $('#inicio').on('change', function () {
            $('#fim').attr('min', $(this).val());
        });
        $('#fim').on('change', function () {
            $('#inicio').attr('max', $(this).val());
        });

        // CLIQUE NAS LINHAS DE VENDA PARA MODAL DE INVOICE
        $(document).on('click', '.linha-venda', function () {
            const venda = $(this).data('venda');

            let html = `
                <div class="row mb-4">
                    <div class="col-6">
                        <p><strong>Pagante:</strong> ${venda.pagante}</p>
                        <p><strong>Solicitante:</strong> ${venda.solicitante ?? '-'}</p>
                        <p><strong>Produto:</strong> ${venda.produto}</p>
                        <p><strong>Localizador:</strong> ${venda.documento ?? '-'}</p>
                    </div>
                    <div class="col-6 text-end">
                        <p><strong>Data da Venda:</strong> ${formatarData(venda.data_venda)}</p>
                        <p><strong>Passageiros:</strong> ${venda.passageiros ?? '-'}</p>
                        <p><strong>Nº Venda:</strong> ${venda.venda_numero ?? '-'}</p>
                        <p><strong>Valor Total:</strong> R$ ${formatarValor(venda.valor_total)}</p>
                    </div>
                </div>
            `;

            // === Condições por tipo de produto ===
            const produto = venda.produto?.toLowerCase() ?? '';

            if (produto.includes('passagem aérea')) {
                html += `
                    <hr>
                    <h6>Detalhes da Passagem Aérea</h6>
                    <p>
                        ${venda.fornecedor ?? '-'} - 
                        ${venda.trechos ?? '-'} - 
                        ${formatarData(venda.data_inicio)} ${formatarHora(venda.hora_inicio)} → 
                        ${formatarData(venda.data_fim)} ${formatarHora(venda.hora_fim)}
                    </p>
                `;
            } else if (produto.includes('diárias de hospedagem')) {
                html += `
                    <hr>
                    <h6>Detalhes da Hospedagem</h6>
                    <p>
                        ${venda.fornecedor ?? '-'} - 
                        ${venda.cidade_fornecedor ?? '-'} - 
                        ${formatarData(venda.data_inicio)} → 
                        ${formatarData(venda.data_fim)}
                    </p>
                `;
            } else if (produto.includes('aluguel de carro')) {
                html += `
                    <hr>
                    <h6>Detalhes da Locação</h6>
                    <p>
                        ${venda.fornecedor ?? '-'} - 
                        Retirada: ${formatarData(venda.data_inicio)}${venda.local_retirada ? ' - ' + venda.local_retirada : ''}
                        Devolução: ${formatarData(venda.data_fim)}${venda.local_devolucao ? ' - ' + venda.local_devolucao : ''}
                    </p>
                `;
            } else {
                // Outros produtos genéricos
                html += `
                    <hr>
                    <h6>Outros</h6>
                    <p>
                        ${venda.fornecedor ?? '-'} - 
                        Período: ${formatarData(venda.data_inicio)} → ${formatarData(venda.data_fim)}<br>
                    </p>
                `;
            }

            $('#invoiceContent').html(html);
            const modal = new bootstrap.Modal(document.getElementById('invoiceModal'));
            modal.show();
        });

        // GRÁFICO - Últimos 12 meses
        const ctx = document.getElementById('vendas12MesesChart')?.getContext('2d');
        const vendasUltimos12Meses = @json($vendasUltimos12Meses);

        if (ctx && vendasUltimos12Meses.length > 0) {
            const labels = vendasUltimos12Meses.map(item => item.mes);
            const valores = vendasUltimos12Meses.map(item => Number(item.valor_total));
            const quantidades = vendasUltimos12Meses.map(item => Number(item.quantidade_total));

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Valor Total (R$)',
                            data: valores,
                            borderColor: 'rgba(54, 162, 235, 1)',
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            yAxisID: 'y',
                            tension: 0.3,
                        },
                        {
                            label: 'Quantidade de Processos',
                            data: quantidades,
                            borderColor: 'rgba(75, 192, 192, 1)',
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            yAxisID: 'y1',
                            tension: 0.3,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    interaction: { mode: 'index', intersect: false },
                    stacked: false,
                    scales: {
                        y: {
                            type: 'linear',
                            position: 'left',
                            title: { display: true, text: 'Valor Total (R$)' }
                        },
                        y1: {
                            type: 'linear',
                            position: 'right',
                            grid: { drawOnChartArea: false },
                            title: { display: true, text: 'Quantidade de Processos' }
                        }
                    },
                    plugins: {
                        legend: { position: 'top' },
                        title: { display: true, text: 'Vendas Últimos 12 Meses' }
                    }
                }
            });
        }
    });
</script>

@endpush