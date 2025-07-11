<?php

namespace App\Http\Controllers;

use App\Models\CompanyGroup;
use Illuminate\Http\Request;

class CompanyGroupController extends Controller
{
    // Listar todos os grupos
    public function index()
    {
        $groups = CompanyGroup::withCount('pagantes')
    ->orderBy('created_at', 'desc')
    ->get();
        return view('company_groups.index', compact('groups'));
    }

    // Mostrar formulário de criação
    public function create()
    {
        return view('company_groups.create');
    }

    // Salvar novo grupo
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:company_groups,name',
        ]);

        CompanyGroup::create($request->only('name'));

        return redirect()->route('company-groups.index')
            ->with('success', 'Grupo "' . $request->name . '" criado com sucesso!');
    }

    // Mostrar um grupo (opcional, pode redirecionar para index)
    public function show(CompanyGroup $companyGroup)
    {
        return view('company_groups.show', compact('companyGroup'));
    }

    // Mostrar formulário para editar
    public function edit(CompanyGroup $companyGroup)
    {
        return view('company_groups.edit', compact('companyGroup'));
    }

    // Atualizar grupo
    public function update(Request $request, CompanyGroup $companyGroup)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:company_groups,name,' . $companyGroup->id,
        ]);

        $companyGroup->update($request->only('name'));

        return redirect()->route('company-groups.index')
            ->with('success', 'Grupo "' . $companyGroup->name . '" atualizado com sucesso!');
    }

    // Deletar grupo
    public function destroy(CompanyGroup $companyGroup)
    {
        $name = $companyGroup->name;  // guarda o nome antes de deletar
        $companyGroup->delete();

        return redirect()->route('company-groups.index')
            ->with('success', 'Grupo "' . $name . '" excluído com sucesso!');
    }
}
