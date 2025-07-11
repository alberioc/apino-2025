@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4">
        Relatórios e insights para tomada de decisão sobre gastos, centros de custo e comportamento de compra
    </h3>

    {{-- Tabela de dados usados no insight Centro de Custo --}}
    <div class="card mb-2">
        <div class="card-body">
            @php
                $dadosPath = storage_path('app/data/dados_centro_custo.csv');
            @endphp

            @if (file_exists($dadosPath))
                <div class="table-responsive">
                    @php
                        $csv = array_map('str_getcsv', file($dadosPath));
                    @endphp
                    <div id="indicador-centro-custo" class="mb-4">
                        <table id="tabela-antecedencia" class="table table-sm table-bordered table-hover table-striped align-middle small">
                            <thead class="table-light">
                                <tr>
                                    @foreach ($csv[0] as $header)
                                        <th>{{ $header }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (array_slice($csv, 1) as $row)
                                    <tr>
                                        @foreach ($row as $cell)
                                            <td>{{ $cell }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <p class="text-muted">Nenhum dado disponível.</p>
            @endif        

            {{-- Bloco de Insights Centros de Custo --}}
            <figure class="card border-info" id="insight-antecedencia">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-lightbulb"></i> Insights de Centros de custo
                </div>

                <div class="card-body small">
                    @php
                        $arquivo = storage_path('app/insights/insight_centro_custo.txt');
                        $insights = file_exists($arquivo) ? file($arquivo, FILE_IGNORE_NEW_LINES) : [];
                    @endphp

                    @if (count($insights))
                        @foreach ($insights as $linha)
                            <p>{{ $linha }}</p>
                        @endforeach
                    @else
                        <p class="text-muted">Nenhum insight disponível no momento.</p>
                    @endif
                </div>

                <figcaption class="blockquote-footer ms-3 mb-2">
                    Gerado com <cite title="Fonte">Apino IA</cite>
                </figcaption>
            </figure>
        </div>
    </div>

    {{-- Tabela de dados usados no insight Trechos --}}
    <div class="card mb-2">
        <div class="card-body">
            @php
                $dadosPath = storage_path('app/data/dados_antecedencia.csv');
            @endphp

            @if (file_exists($dadosPath))
                <div class="table-responsive">
                    @php
                        $csv = array_map('str_getcsv', file($dadosPath));
                    @endphp
                    <div id="indicador-antecedencia" class="mb-4">
                        <table id="tabela-antecedencia" class="table table-sm table-bordered table-hover table-striped align-middle small">
                            <thead class="table-light">
                                <tr>
                                    @foreach ($csv[0] as $header)
                                        <th>{{ $header }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (array_slice($csv, 1) as $row)
                                    <tr>
                                        @foreach ($row as $cell)
                                            <td>{{ $cell }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <p class="text-muted">Nenhum dado disponível.</p>
            @endif

            {{-- Bloco de Insights Antecedência --}}
            <figure class="card border-info" id="insight-antecedencia">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-lightbulb"></i> Insights de Antecedência
                </div>

                <div class="card-body small">
                    @php
                        $arquivo = storage_path('app/insights/insight_antecedencia.txt');
                        $insights = file_exists($arquivo) ? file($arquivo, FILE_IGNORE_NEW_LINES) : [];
                    @endphp

                    @if (count($insights))
                        @foreach ($insights as $linha)
                            <p>{{ $linha }}</p>
                        @endforeach
                    @else
                        <p class="text-muted">Nenhum insight disponível no momento.</p>
                    @endif
                </div>

                <figcaption class="blockquote-footer ms-3 mb-2">
                    Gerado com <cite title="Fonte">Apino IA</cite>
                </figcaption>
            </figure>
        </div>
    </div>

    {{-- Tabela de dados usados no insight viajantes --}}
    <div class="card mb-2">
        <div class="card-body">
            @php
                $dadosPath = storage_path('app/data/dados_viajantes.csv');
            @endphp

            @if (file_exists($dadosPath))
                <div class="table-responsive">
                    @php
                        $csv = array_map('str_getcsv', file($dadosPath));
                    @endphp
                    <div id="indicador-viajantes" class="mb-4">
                        <table id="tabela-viajantes" class="table table-sm table-bordered table-hover table-striped align-middle small">
                            <thead class="table-light">
                                <tr>
                                    @foreach ($csv[0] as $header)
                                        <th>{{ $header }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (array_slice($csv, 1) as $row)
                                    <tr>
                                        @foreach ($row as $cell)
                                            <td>{{ $cell }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <p class="text-muted">Nenhum dado disponível.</p>
            @endif

            {{-- Bloco de Insights Viajantes --}}
            <figure class="card border-info" id="insight-antecedencia">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-lightbulb"></i> Insights de Viajantes
                </div>

                <div class="card-body small">
                    @php
                        $arquivo = storage_path('app/insights/insight_viajantes.txt');
                        $insights = file_exists($arquivo) ? file($arquivo, FILE_IGNORE_NEW_LINES) : [];
                    @endphp

                    @if (count($insights))
                        @foreach ($insights as $linha)
                            <p>{{ $linha }}</p>
                        @endforeach
                    @else
                        <p class="text-muted">Nenhum insight disponível no momento.</p>
                    @endif
                </div>

                <figcaption class="blockquote-footer ms-3 mb-2">
                    Gerado com <cite title="Fonte">Apino IA</cite>
                </figcaption>
            </figure>
        </div>
    </div>

    {{-- Tabela de dados usados no insight viajantes --}}
    <div class="card mb-2">
        <div class="card-body">
            @php
                $dadosPath = storage_path('app/data/dados_viajantes.csv');
            @endphp

            @if (file_exists($dadosPath))
                <div class="table-responsive">
                    @php
                        $csv = array_map('str_getcsv', file($dadosPath));
                    @endphp
                    <div id="indicador-viajantes" class="mb-4">
                        <table id="tabela-viajantes" class="table table-sm table-bordered table-hover table-striped align-middle small">
                            <thead class="table-light">
                                <tr>
                                    @foreach ($csv[0] as $header)
                                        <th>{{ $header }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (array_slice($csv, 1) as $row)
                                    <tr>
                                        @foreach ($row as $cell)
                                            <td>{{ $cell }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <p class="text-muted">Nenhum dado disponível.</p>
            @endif

            {{-- Bloco de Insights Viajantes --}}
            <figure class="card border-info" id="insight-antecedencia">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-lightbulb"></i> Insights de Viajantes
                </div>

                <div class="card-body small">
                    @php
                        $arquivo = storage_path('app/insights/insight_viajantes.txt');
                        $insights = file_exists($arquivo) ? file($arquivo, FILE_IGNORE_NEW_LINES) : [];
                    @endphp

                    @if (count($insights))
                        @foreach ($insights as $linha)
                            <p>{{ $linha }}</p>
                        @endforeach
                    @else
                        <p class="text-muted">Nenhum insight disponível no momento.</p>
                    @endif
                </div>

                <figcaption class="blockquote-footer ms-3 mb-2">
                    Gerado com <cite title="Fonte">Apino IA</cite>
                </figcaption>
            </figure>
        </div>
    </div>

    {{-- Tabela de dados usados no insight solicitantes --}}
    <div class="card mb-2">
        <div class="card-body">
            @php
                $dadosPath = storage_path('app/data/dados_solicitantes.csv');
            @endphp

            @if (file_exists($dadosPath))
                <div class="table-responsive">
                    @php
                        $csv = array_map('str_getcsv', file($dadosPath));
                    @endphp
                    <div id="indicador-solicitantes" class="mb-4">
                        <table id="tabela-solicitantes" class="table table-sm table-bordered table-hover table-striped align-middle small">
                            <thead class="table-light">
                                <tr>
                                    @foreach ($csv[0] as $header)
                                        <th>{{ $header }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (array_slice($csv, 1) as $row)
                                    <tr>
                                        @foreach ($row as $cell)
                                            <td>{{ $cell }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <p class="text-muted">Nenhum dado disponível.</p>
            @endif

            {{-- Bloco de Insights Viajantes --}}
            <figure class="card border-info" id="insight-solicitante">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-lightbulb"></i> Insights de Solicitantes
                </div>

                <div class="card-body small">
                    @php
                        $arquivo = storage_path('app/insights/insight_solicitantes.txt');
                        $insights = file_exists($arquivo) ? file($arquivo, FILE_IGNORE_NEW_LINES) : [];
                    @endphp

                    @if (count($insights))
                        @foreach ($insights as $linha)
                            <p>{{ $linha }}</p>
                        @endforeach
                    @else
                        <p class="text-muted">Nenhum insight disponível no momento.</p>
                    @endif
                </div>

                <figcaption class="blockquote-footer ms-3 mb-2">
                    Gerado com <cite title="Fonte">Apino IA</cite>
                </figcaption>
            </figure>
        </div>
    </div>

    {{-- Indicador de Viajantes
    <div class="card mb-2">
        <div class="card-header bg-info text-white d-flex align-items-center">
            <i class="fas fa-users-between-lines fa-lg me-2"></i>
            <strong>Viajantes</strong>
        </div>
        <div class="card-body p-2" id="indicador-viajantes">
            <div class="text-center my-3">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
                <div class="text-center text-muted mt-2">Carregando indicador de viajantes...</div>
            </div>
        </div>
    </div>
    --}}
    {{-- Indicador de Solicitantes
    <div class="card mb-2">
        <div class="card-header bg-info text-white d-flex align-items-center">
            <i class="fas fa-user-tie fa-lg me-2"></i>
            <strong>Solicitantes</strong>
        </div>
        <div class="card-body p-2" id="indicador-solicitantes">
            <div class="text-center my-3">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
                <div class="text-center text-muted mt-2">Carregando indicador de solicitantes...</div>
            </div>
        </div>
    </div>
     --}}
</div>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    function carregarIndicador(id, rota, mensagemErro) {
        fetch(rota)
            .then(res => res.text())
            .then(html => {
                const container = document.getElementById(id);
                container.innerHTML = html;

                // Aguarda DOM atualizar para inicializar DataTable com segurança
                setTimeout(() => {
                    $(container).find('table').DataTable({
                        ordering: true,
                        responsive: true,
                        language: {
                            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json"
                        },
                        dom: 'Bfrtip',
                        buttons: [
                            { extend: 'copyHtml5', text: 'Copiar', className: 'btn btn-secondary' },
                            { extend: 'excelHtml5', text: 'Excel', className: 'btn btn-secondary' },
                            { extend: 'csvHtml5', text: 'CSV', className: 'btn btn-secondary' },
                            {
                                extend: 'pdfHtml5',
                                text: 'PDF',
                                className: 'btn btn-secondary',
                                orientation: 'portrait',
                                pageSize: 'A4',
                                exportOptions: { columns: ':visible' }
                            },
                            { extend: 'print', text: 'Imprimir', className: 'btn btn-secondary' }
                        ],
                        order: [[1, 'desc']]
                    });
                }, 50); // 50ms de delay para garantir renderização
            })
            .catch(() => {
                document.getElementById(id).innerHTML = `<div class="text-danger text-center my-3">Erro ao carregar ${mensagemErro}</div>`;
            });
    }

    carregarIndicador('indicador-viajantes', '/bi/indicador-viajantes', 'indicador de viajantes');
    carregarIndicador('indicador-solicitantes', '/bi/indicador-solicitantes', 'indicador de solicitantes');
});
$(document).ready(function() {
    $('table').DataTable({
        dom: 'Bfrtip',
        buttons: [
            'copyHtml5', 'excelHtml5', 'csvHtml5',
            {
                extend: 'pdfHtml5',
                text: 'PDF',
                orientation: 'landscape',  // <== paisagem
                pageSize: 'A4',
                exportOptions: { columns: ':visible' },
                customize: function (doc) {
                    // Inserir texto do insight antes da tabela no PDF
                    var insightText = `
                        ${document.getElementById('insight-antecedencia').innerText.trim()}
                    `;

                    // Coloca o texto no início do PDF
                    doc.content.unshift({
                        text: insightText,
                        margin: [0, 0, 0, 12], // espaço abaixo do texto
                        fontSize: 10
                    });

                    // Aqui você pode ajustar outras configurações, se quiser
                }
            },
            {
                extend: 'print',
                text: 'Imprimir',
                exportOptions: { columns: ':visible' },
                customize: function (win) {
                    var insightText = document.getElementById('insight-antecedencia').innerText.trim();
                    var body = $(win.document.body);
                    
                    // Adiciona título + insight no topo da impressão
                    body.prepend(
                        '<h3>Insights de Antecedência</h3><p style="font-size:small;">' + insightText.replace(/\n/g, '<br>') + '</p><hr>'
                    );

                    // Estilo extra, se quiser:
                    body.find('table').addClass('table table-bordered table-sm');
                }
            }
        ],
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json"
        },
        order: [[0, 'desc']]
    });

    });
</script>
@endpush
