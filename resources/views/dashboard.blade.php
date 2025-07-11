@extends('layouts.app')

@section('title', 'APINO | Área do Cliente')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Área do Cliente
    </h2>
@endsection

@section('content')
<div class="py-1">
    <div class="container">

        <div class="mb-4">
            <h5>Bem-vindo, {{ Auth::user()->name }} - {{ auth()->user()->company_group }}!</h5>
        </div>

        <div class="row g-4">

            {{-- BI - Business Intelligence --}}
            <div class="col-md-4 d-flex mb-4">
                <div class="card text-center flex-fill">
                    <div class="card-header">
                        <i class="fas fa-chart-line fa-4x text-primary"></i>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Business Intelligence</h5>
                        <p class="card-text">
                            Tenha uma visão completa e em tempo real das suas métricas e indicadores essenciais para decisões inteligentes.
                        </p>
                        <a href="{{ url('bi') }}" class="btn btn-primary mt-auto">
                            <i class="fas fa-arrow-right"></i> Entrar no Painel
                        </a>
                    </div>
                </div>
            </div>

            {{-- Relatórios de Carbono --}}
            <div class="col-md-4 d-flex mb-4">
                <div class="card text-center flex-fill">
                    <div class="card-header">
                        <i class="fas fa-leaf fa-4x text-success"></i>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Relatório de Emissões de Carbono</h5>
                        <p class="card-text">
                            Explore dados detalhados sobre suas emissões de CO₂ e descubra oportunidades para reduzir seu impacto ambiental.
                        </p>
                        <a href="{{ url('/relatorios/carbono') }}" class="btn btn-success mt-auto">
                            <i class="fas fa-arrow-right"></i> Acessar Relatório
                        </a>
                    </div>
                </div>
            </div>

            {{-- Faturas --}}
            <div class="col-md-4 d-flex mb-4">
                <div class="card text-center flex-fill">
                    <div class="card-header">
                        <i class="fas fa-receipt fa-4x text-info"></i>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Faturas</h5>
                        <p class="card-text">
                            Consulte e baixe suas faturas recentes para manter seu controle financeiro em dia.
                        </p>
                        <a href="{{ url('faturas/') }}" class="btn btn-info mt-auto">
                            <i class="fas fa-arrow-right"></i> Acessar Faturas
                        </a>
                    </div>
                </div>
            </div>
            
        </div>

    </div>
</div>
@endsection
