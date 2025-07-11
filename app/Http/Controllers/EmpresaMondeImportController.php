<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Imports\EmpresaMondeImport;
use Maatwebsite\Excel\Facades\Excel;

class EmpresaMondeImportController extends Controller
{
    public function import(Request $request)
    {
        set_time_limit(1800);
        
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        Log::info('Importação iniciada');

        // Executa a importação
        Excel::import(new EmpresaMondeImport, $request->file('file'));

        Log::info('Importação finalizada');

        return redirect()
            ->back()
            ->with('success', 'Importação realizada com sucesso!');
    }
}
