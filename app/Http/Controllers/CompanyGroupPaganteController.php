<?php

namespace App\Http\Controllers;

use App\Models\CompanyGroup;
use App\Models\CompanyGroupVenda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyGroupPaganteController extends Controller
{
    public function create($groupId)
    {
        $group = CompanyGroup::findOrFail($groupId);

        // Todos os pagantes únicos da tabela vendas
        $pagantes = DB::table('vendas')
            ->select('pagante')
            ->whereNotNull('pagante')
            ->where('pagante', '!=', '')
            ->where('tipo_pessoa', 'J')
            ->groupBy('pagante')
            ->orderBy('pagante')
            ->get();

        // Pagantes já associados ao grupo
        $associados = CompanyGroupVenda::where('company_group_id', $groupId)
            ->orderBy('pagante')
            ->get();

        // Filtrar pagantes para excluir os já associados
        $pagantesDisponiveis = $pagantes->filter(function ($pagante) use ($associados) {
            return !$associados->contains('pagante', $pagante->pagante);
        });

        return view('company_groups.pagantes', [
            'group' => $group,
            'pagantes' => $pagantesDisponiveis,
            'associados' => $associados,
        ]);
    }

    public function store(Request $request, $groupId)
    {
        $request->validate([
            'pagantes' => 'required|array',
        ]);

        foreach ($request->pagantes as $pagante) {
            CompanyGroupVenda::firstOrCreate([
                'company_group_id' => $groupId,
                'pagante' => $pagante,
            ]);
        }

        return redirect()->route('company-groups.index')
            ->with('success', 'Pagantes adicionados ao grupo com sucesso.');
    }

    public function destroy($groupId, $pagante)
    {
        CompanyGroupVenda::where('company_group_id', $groupId)
            ->where('pagante', $pagante)
            ->delete();

        return redirect()->route('company-groups.pagantes', $groupId)
            ->with('success', "Pagante \"$pagante\" removido do grupo.");
    }

}
