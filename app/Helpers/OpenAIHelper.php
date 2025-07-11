<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

if (!function_exists('gerarInsightOpenAI')) {
    function gerarInsightOpenAI(string $prompt, string $role = 'Você é um analista em Gestão de Viagens Corporativas da Apino Turismo.'): string
    {
        try {
            $resposta = Http::withToken(env('OPENAI_API_KEY'))->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o',
                'messages' => [
                    ['role' => 'system', 'content' => $role],
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            return $resposta->json('choices.0.message.content') ?? 'Insight vazio.';
        } catch (\Exception $e) {
            Log::error('Erro ao gerar insight OpenAI: ' . $e->getMessage());
            return 'Não foi possível gerar o insight no momento.';
        }
    }
}
