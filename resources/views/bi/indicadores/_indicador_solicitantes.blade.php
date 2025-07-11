<table class="table table-sm table-bordered table-hover table-striped align-middle small" id="tabela-solicitantes" style="width: 100%">
    <thead class="table-light">
        <tr>
            <th>Solicitante</th>
            <th>Quantidade</th>
            <th>Valor Total (R$)</th>
            <th>Ticket Médio (R$)</th>
            <th>% do Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($solicitantes as $s)
            <tr>
                <td>{{ $s['solicitante'] }}</td>
                <td>{{ $s['quantidade'] }}</td>
                <td>{{ number_format($s['total'], 2, ',', '.') }}</td>
                <td>{{ number_format($s['ticket_medio'], 2, ',', '.') }}</td>
                <td>{{ number_format($s['percentual'], 1, ',', '.') }}%</td>
            </tr>
        @endforeach
    </tbody>
</table>
