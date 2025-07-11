<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\VendasImport;
use App\Models\ImportStatus;

class VendaImportController extends Controller
{
    public function formulario()
    {
        $pendentes = ImportStatus::where('status', 'pendente')->count();
        $processando = ImportStatus::where('status', 'processando')->count();
        $sucesso = ImportStatus::where('status', 'sucesso')->count();
        $erros = ImportStatus::where('status', 'erro')->count();

        return view('vendas.importar', compact('pendentes', 'processando', 'sucesso', 'erros'));
    }

    public function importar(Request $request)
    {
        $request->validate([
            'arquivo' => 'required|file|mimes:xlsx,csv',
        ]);

        $arquivo = $request->file('arquivo');
        $nome = $arquivo->hashName(); // ou getClientOriginalName() se preferir nome real
        $arquivo->storeAs('imports-pendentes', $nome);

        ImportStatus::create([
            'nome_arquivo' => $nome,
            'status' => 'pendente',
            'email' => auth()->user()->email ?? null,
        ]);

        return redirect()->route('vendas.importar.form')
            ->with('success', 'Arquivo recebido! Processamento será feito em background.');
    }

    public function status()
    {
        $importacoes = ImportStatus::orderBy('created_at', 'desc')->get();

        return view('vendas.status_importacoes', compact('importacoes'));
    }

}
