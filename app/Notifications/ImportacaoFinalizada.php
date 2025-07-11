<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ImportacaoFinalizada extends Notification
{
    use Queueable;

    public $importStatus;

    public function __construct($importStatus)
    {
        $this->importStatus = $importStatus;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $arquivo = $this->importStatus->nome_arquivo;
        $status = ucfirst($this->importStatus->status);
        $linhas = $this->importStatus->linhas_processadas ?? 'N/D';
        $erro = $this->importStatus->mensagem_erro;

        $mensagem = (new MailMessage)
            ->subject("Importação de vendas: $status")
            ->greeting("Olá!")
            ->line("O arquivo **$arquivo** foi processado.")
            ->line("Status: **$status**")
            ->line("Linhas processadas: $linhas");

        if ($erro) {
            $mensagem->line("Erro: $erro");
        }

        $mensagem
            ->line("Obrigado por usar o sistema!")
            ->salutation("Atenciosamente,\nAPINO Turismo");

        return $mensagem;
    }
}
