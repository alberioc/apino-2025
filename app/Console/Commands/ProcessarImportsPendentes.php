<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\VendasImport;
use App\Models\Venda;
use App\Models\ImportStatus;
use App\Notifications\ImportacaoFinalizada;
use ZipArchive;

class ProcessarImportsPendentes extends Command
{
    protected $signature = 'imports:processar';
    protected $description = 'Processa arquivos XLSX pendentes na pasta imports-pendentes';

    public function handle()
    {
        $files = Storage::files('imports-pendentes');

        if (empty($files)) {
            $this->info('✅ Nenhum arquivo pendente para processar.');
            return;
        }

        foreach ($files as $file) {
            $this->info("📄 Processando arquivo: $file");

            $nome = basename($file); // pega apenas o nome do arquivo
            $importStatus = ImportStatus::where('nome_arquivo', $nome)->first();

            if (!$importStatus) {
                $this->warn("⚠️ Nenhum registro encontrado com nome_arquivo = $nome");
                continue; // pula esse arquivo
            }

            // Marca início do processamento
            $importStatus->update([
                'status' => 'processando',
                'iniciado_em' => now(),
            ]);

            try {
                // Limpa a tabela antes de importar
                Venda::truncate();

                // Processa a importação
                Excel::import(new VendasImport($importStatus->id), storage_path('app/' . $file));

                // Cmpacta o arquivo e arquiva
                $zipPath = storage_path('app/imports-processados/' . basename($file) . '.zip');

                $zip = new ZipArchive;
                if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
                    $zip->addFile(storage_path('app/' . $file), basename($file));
                    $zip->close();

                    // Agora podemos remover o arquivo original
                    Storage::delete($file);
                } else {
                    $this->error("❌ Falha ao compactar o arquivo $file");
                }

                // Marca sucesso
                $importStatus->update([
                    'status' => 'sucesso',
                    'linhas_processadas' => Venda::count(),
                    'processado_em' => now(),
                ]);

                // Notifica por e-mail
                if ($importStatus->email) {
                    Notification::route('mail', $importStatus->email)
                        ->notify(new ImportacaoFinalizada($importStatus));
                }

                $this->info("✅ Arquivo $nome importado com sucesso!");
            } catch (\Exception $e) {
                // Marca erro
                $importStatus->update([
                    'status' => 'erro',
                    'mensagem_erro' => $e->getMessage(),
                    'processado_em' => now(),
                ]);

                $this->error("❌ Erro ao importar $nome: " . $e->getMessage());
            }
        }
    }
}
