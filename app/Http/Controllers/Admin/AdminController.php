<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function mostrarForm()
    {
        return view('admin.rodar_scripts'); // Caminho correto da blade
    }
    
    public function gerarInsights(Request $request)
{
    $pastaScripts = base_path('scripts');
    $arquivosPython = File::files($pastaScripts);
    $erros = [];
    $sucessos = [];

    // Caminho para o Python do seu virtualenv
    $pythonPath = base_path('venv/Scripts/python.exe');

    foreach ($arquivosPython as $arquivo) {
        if ($arquivo->getExtension() !== 'py') {
            continue;
        }

        $caminhoCompleto = $arquivo->getRealPath();
        $process = new Process([$pythonPath, $caminhoCompleto]);

        // Se quiser definir variáveis de ambiente para evitar erro de hash randomization
        $process->setEnv([
            'PYTHONHASHSEED' => 'random',
        ]);

        $process->run();

        if (!$process->isSuccessful()) {
            $erros[] = $arquivo->getFilename() . ': ' . $process->getErrorOutput();
        } else {
            $sucessos[] = $arquivo->getFilename();
        }
    }

    if (!empty($erros)) {
        return back()->with('error', implode('<br>', $erros));
    }

    return back()->with('success', 'Scripts rodados com sucesso: ' . implode(', ', $sucessos));
}
}
