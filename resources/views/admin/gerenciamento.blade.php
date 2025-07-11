@extends('layouts.app')

@section('title', 'APINO | Administração')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Painel de Administração
    </h2>
@endsection

@section('content')
<div class="py-1">
    <div class="container">

        <div class="mb-4">
            <h5>Bem-vindo, {{ Auth::user()->name }}!</h5>
        </div>

        <div class="row g-4">

            {{-- Gerenciar Usuários --}}
            <div class="col-md-4 d-flex mb-4">
                <div class="card text-center flex-fill">
                    <div class="card-header">
                        <i class="fas fa-users fa-4x text-primary"></i>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Gerenciar Usuários</h5>
                        <p class="card-text">
                            Crie, edite e exclua usuários para controlar o acesso ao sistema.
                        </p>
                        <a href="{{ route('users.index') }}" class="btn btn-primary mt-auto">
                            <i class="fas fa-arrow-right"></i> Ir para Usuários
                        </a>
                    </div>
                </div>
            </div>

            {{-- Gerenciar Grupos de Empresas --}}
            <div class="col-md-4 d-flex mb-4">
                <div class="card text-center flex-fill">
                    <div class="card-header">
                        <i class="fas fa-building fa-4x text-success"></i>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Gerenciar Grupos de Empresas</h5>
                        <p class="card-text">
                            Controle os grupos de empresas associados e seus pagantes.
                        </p>
                        <a href="{{ route('company-groups.index') }}" class="btn btn-success mt-auto">
                            <i class="fas fa-arrow-right"></i> Ir para Grupos
                        </a>
                    </div>
                </div>
            </div>

            {{-- Importar Planilha de Vendas --}}
            <div class="col-md-4 d-flex mb-4">
                <div class="card text-center flex-fill">
                    <div class="card-header">
                        <i class="fas fa-file-excel fa-4x text-primary"></i>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Importar Planilha de Vendas</h5>
                        <p class="card-text">
                            Faça upload da planilha para registrar as vendas no banco de dados.
                        </p>
                        <a href="{{ route('vendas.importar.form') }}" class="btn btn-primary mt-auto">
                            <i class="fas fa-upload"></i> Importar
                        </a>
                    </div>
                </div>
            </div>

            {{-- Receita Recorrente Corporativa --}}
            <div class="col-md-4 d-flex mb-4">
                <div class="card text-center flex-fill">
                    <div class="card-header">
                        <i class="fas fa-chart-line fa-4x text-success"></i>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Receita Recorrente</h5>
                        <p class="card-text">
                            Acompanhe as metas de crescimento e veja se estamos no ritmo para bater os R$150 mil até dezembro.
                        </p>
                        <a href="{{ route('dashboard.receitaRecorrente') }}" class="btn btn-success mt-auto">
                            <i class="fas fa-chart-bar"></i> Ver Indicadores
                        </a>
                    </div>
                </div>
            </div>

            {{-- Outros recursos administrativos --}}
            <div class="col-md-4 d-flex mb-4">
                <div class="card text-center flex-fill">
                    <div class="card-header">
                        <i class="fas fa-cogs fa-4x text-info"></i>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Configurações</h5>
                        <p class="card-text">
                            Ajuste configurações do sistema e preferências administrativas.
                        </p>
                        <a href="#" class="btn btn-info mt-auto disabled" title="Em breve">
                            <i class="fas fa-arrow-right"></i> Em breve
                        </a>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection
