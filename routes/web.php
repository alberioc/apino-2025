<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CarbonReportController;
use App\Http\Controllers\FaturaController;
use App\Http\Controllers\BIController;
use App\Http\Controllers\EmpresaMondeImportController;
use App\Http\Controllers\CompanyGroupController;
use App\Http\Controllers\CompanyGroupPaganteController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PlanilhaController;
use App\Http\Controllers\Admin\GerenciamentoController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\VendaImportController;
use App\Http\Controllers\DashboardController;

use App\Http\Controllers\BI\IndicadorViajantesController;
use App\Http\Controllers\BI\IndicadorSolicitantesController;

use Illuminate\Support\Facades\DB;
use App\Models\EmpresaMonde;

// Rotas públicas
Route::get('/', function () {
    return view('auth.login');
});

Route::prefix('planilhas')->group(function () {
    Route::get('/', [PlanilhaController::class, 'index'])->name('planilhas.index'); // Formulário
    Route::post('/enviar', [PlanilhaController::class, 'processar'])->name('planilhas.processar'); // Upload
    Route::get('/resultado', [PlanilhaController::class, 'resultado'])->name('planilhas.resultado'); // Visualizar resultado
    Route::get('/download/{arquivo}', [PlanilhaController::class, 'download'])->name('planilhas.download');
    Route::get('/planilhas/editar', [PlanilhaController::class, 'editar'])->name('planilhas.editar');
    Route::post('/planilhas/salvar', [PlanilhaController::class, 'salvar'])->name('planilhas.salvar');

    // Rotas para envio da planilha de vendas
    Route::get('/gerenciar/importar-vendas', [VendaImportController::class, 'formulario'])
    ->name('vendas.importar.form');

    Route::post('/gerenciar/importar-vendas', [VendaImportController::class, 'importar'])
        ->name('vendas.importar.enviar');

    Route::get('/vendas/importacoes/status', [VendaImportController::class, 'status'])->name('vendas.importar.status');
});

Route::get('/teste-transacao', function () {
    DB::beginTransaction();

    EmpresaMonde::create([
        'systemAccountId' => 35862,
        'nome' => 'Teste Reversível',
        'email' => 'teste@teste.com',
        'telefone' => '1111',
        'celular' => '2222',
        'cidade' => 'Teste',
        'uf' => 'TS',
    ]);

    DB::rollBack();

    return 'Registro não deve aparecer no banco, mas o ID aumentará.';
});

Route::get('/import-empresa-monde', function () {
    return view('empresa_monde.import');
})->name('empresa_monde.form');

Route::post('/import-empresa-monde', [EmpresaMondeImportController::class, 'import'])
    ->name('empresa_monde.import');

// Rota para administradores
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/gerenciamento', [GerenciamentoController::class, 'index'])->name('gerenciamento.index');
    // Rota para mostrar a página com o botão
    Route::get('/admin/rodar-scripts', [AdminController::class, 'mostrarForm'])->name('admin.mostrar.form');

    // Rota para processar o POST e rodar os scripts
    Route::post('/admin/rodar-scripts', [AdminController::class, 'gerarInsights'])->name('admin.rodar.scripts');

    // Rota para Painel de vendas e Receita
    Route::get('/receita-recorrente', [DashboardController::class, 'receitaRecorrente'])->name('dashboard.receitaRecorrente');

    // Rota para Ranking de Vendas
    Route::get('/receita-recorrente/ranking/{mes}', [DashboardController::class, 'rankingMensal'])
    ->name('dashboard.receita.ranking');
    
});

// Rotas protegidas por autenticação
Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');

    // Rotas de Indicadores
    Route::get('/bi/indicador-viajantes', [IndicadorViajantesController::class, 'show']);
    Route::get('/bi/indicador-solicitantes', [IndicadorSolicitantesController::class, 'show']);

    // Detalhes do produto
    Route::get('/bi/produto-detalhes', [BIController::class, 'produtoDetalhes']);

    // CRUD Usuários
    Route::resource('users', UserController::class);

    // CRUD Company Groups
    Route::resource('company-groups', CompanyGroupController::class);

    // Associação pagantes -> company groups
    Route::prefix('company-groups/{company_group}')->group(function () {
        Route::get('pagantes', [CompanyGroupPaganteController::class, 'create'])->name('company-groups.pagantes');
        Route::post('pagantes', [CompanyGroupPaganteController::class, 'store'])->name('company-groups.pagantes.store');
        Route::delete('pagantes/{pagante}', [CompanyGroupPaganteController::class, 'destroy'])->name('company-groups.pagantes.destroy');
    });

    Route::get('/relatorios/carbono', [CarbonReportController::class, 'index'])->name('relatorios.carbono');
    Route::get('/faturas', [FaturaController::class, 'index'])->name('faturas.index');
    Route::get('/bi', [App\Http\Controllers\BIController::class, 'index'])->name('bi.index');

    // Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
