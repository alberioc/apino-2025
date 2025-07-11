@extends('layouts.app')

@section('content')
@if(session('success'))
    <div class="alert alert-success">{!! session('success') !!}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{!! session('error') !!}</div>
@endif

<div class="container">
    <h1>Executar Scripts Python</h1>

    @if(session('success'))
        <div class="alert alert-success">{!! session('success') !!}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{!! session('error') !!}</div>
    @endif

    <form action="{{ url('/admin/rodar-scripts') }}" method="POST">
    @csrf  {{-- Muito importante para segurança --}}
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-cogs"></i> Rodar Todos os Scripts
        </button>
    </form>
</div>
@endsection
