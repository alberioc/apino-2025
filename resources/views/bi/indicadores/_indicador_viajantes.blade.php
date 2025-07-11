<table class="table table-sm table-bordered table-hover table-striped align-middle small" id="tabela-viajantes" style="width: 100%">
    <thead class="table-light">
        <tr>
            <th>Passageiro</th>
            <th>Quantidade</th>
            <th>Valor Total (R$)</th>
            <th>Ticket Médio (R$)</th>
            <th>% do Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($viajantes as $v)
            <tr>
                <td>{{ $v['nome'] }}</td>
                <td>{{ $v['quantidade'] }}</td>
                <td>{{ number_format($v['total'], 2, ',', '.') }}</td>
                <td>{{ number_format($v['ticket_medio'], 2, ',', '.') }}</td>
                <td>{{ number_format($v['percentual'], 1, ',', '.') }}%</td>
            </tr>
        @endforeach
    </tbody>
</table>
