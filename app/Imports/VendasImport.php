<?php

namespace App\Imports;

use App\Models\Venda;
use App\Models\ImportStatus;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterChunk;

class VendasImport implements ToModel, WithHeadingRow, WithChunkReading, WithBatchInserts, WithEvents
{
    protected $importStatusId;
    protected $linhasProcessadas = 0;

    public function __construct($importStatusId)
    {
        $this->importStatusId = $importStatusId;
    }

    public function model(array $row)
    {
        if (!isset($row['venda_no'])) {
            return null;
        }

        $this->linhasProcessadas++;

        return new Venda([
            'venda_numero' => $row['venda_no'] ?? null,
            'vendedor' => $row['vendedor'] ?? null,
            'data_venda' => $this->formatarData($row['data_venda'] ?? null),
            'pagante' => $row['pagante'] ?? null,
            'data_inicio' => $this->formatarData($row['data_inicio'] ?? null),
            'produto' => $row['produto'] ?? null,
            'fornecedor' => $row['fornecedor'] ?? null,
            'representante' => $row['representante'] ?? null,
            'valor_total' => $row['valor_total'] ?? null,
            'segmento' => $row['segmento'] ?? null,
            'tipo_de_viajante_eventos' => $row['tipo_de_viajante_eventos'] ?? null,
            'data_fim' => $this->formatarData($row['data_fim'] ?? null),
            'hora_inicio' => $this->formatarHora($row['hora_inicio'] ?? null),
            'hora_fim' => $this->formatarHora($row['hora_fim'] ?? null),
            'diarias' => $row['diarias'] ?? null,
            'quantidade' => $row['quantidade'] ?? null,
            'documento' => $row['documento'] ?? null,
            'tipo_acomodacao' => $row['tipo_acomodacao'] ?? null,
            'regime' => $row['regime'] ?? null,
            'categoria_quarto' => $row['categoria_quarto'] ?? null,
            'categoria_veiculo' => $row['categoria_veiculo'] ?? null,
            'local_retirada' => $row['local_retirada'] ?? null,
            'local_devolucao' => $row['local_devolucao'] ?? null,
            'destino' => $row['destino'] ?? null,
            'tipo_pessoa' => $row['tipo_pessoa'] ?? null,
            'situacao' => $row['situacao'] ?? null,
            'solicitante' => $row['solicitante'] ?? null,
            'receitas' => $row['receitas'] ?? null,
            'faturamento' => $row['faturamento'] ?? null,
            'cpf' => $row['cpf'] ?? null,
            'cnpj' => $row['cnpj'] ?? null,
            'aprovador' => $row['aprovador'] ?? null,
            'email' => Str::limit($row['e_mail'] ?? '', 255),
            'celular' => $row['celular'] ?? null,
            'telefone' => $row['telefone'] ?? null,
            'numero_notas_fiscais' => $row['numero_notas_fiscais'] ?? null,
            'passageiros' => Str::limit($row['passageiros'] ?? null),
            'cidade_fornecedor' => $row['cidade_fornecedor'] ?? null,
            'trechos' => $row['trechos'] ?? null,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterChunk::class => function () {
                ImportStatus::where('id', $this->importStatusId)
                    ->update([
                        'linhas_processadas' => $this->linhasProcessadas,
                    ]);
            },
        ];
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function batchSize(): int
    {
        return 500;
    }

    private function formatarData($valor)
    {
        if (is_null($valor)) return null;
        if (is_numeric($valor)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($valor)->format('Y-m-d');
        }
        return date('Y-m-d', strtotime($valor));
    }

    private function formatarHora($valor)
    {
        if (is_null($valor) || $valor === '') return null;
        if (is_numeric($valor)) return gmdate('H:i:s', round($valor * 86400));
        return date('H:i:s', strtotime($valor));
    }
}
