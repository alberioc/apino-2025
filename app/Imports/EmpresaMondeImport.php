<?php

namespace App\Imports;

use App\Models\EmpresaMonde;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class EmpresaMondeImport implements ToModel, WithHeadingRow, WithChunkReading, WithBatchInserts
{
    /**
     * Processa uma linha da planilha por vez
     */
    public function model(array $row)
    {
        try {
            $nome = $this->cleanString($row['nome'] ?? null);

            // Ignora se não houver nome
            if (!$nome || $nome === 'NI') {
                Log::warning("⚠ Linha ignorada: nome vazio ou inválido.");
                return null;
            }

            $empresa = new EmpresaMonde([
                'systemAccountId' => 35862,
                'nome'            => $nome
            ]);

            Log::info("✔ Inserindo: {$empresa->nome}");

            return $empresa;

        } catch (\Exception $e) {
            Log::error("❌ Erro ao importar linha: " . $e->getMessage());
            return null;
        }
    }

    public function batchSize(): int
    {
        return 100;
    }

    /**
     * Define o tamanho do chunk (bloco de leitura)
     */
    public function chunkSize(): int
    {
        return 100; // ajustável conforme memória do servidor
    }

    /**
     * Limpa o texto (remove espaços, símbolos estranhos etc.)
     */
    private function cleanString($string): string
    {
        $string = trim((string) $string);
        $string = preg_replace('/\s+/', ' ', $string); // múltiplos espaços => 1
        $string = preg_replace('/[^a-zA-Z0-9 \,\.\-\_@]/u', '', $string); // remove símbolos não permitidos
        return $string ?: 'NI';
    }
}
