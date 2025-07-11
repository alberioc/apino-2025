@extends('layouts.guest')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">

        {{-- Logo --}}
        <div class="text-center mb-4">
            <img src="{{ asset('images/logo-site-empresas.png') }}" alt="Apino Turismo" style="max-width: 180px;">
        </div>

        {{-- Box do formulário --}}
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-3 text-center">Recuperar Senha</h5>

                {{-- Mensagem de sucesso --}}
                @if (session('status'))
                    <div class="alert alert-success py-2 px-3 small">
                        {{ session('status') }}
                    </div>
                @endif

                {{-- Erros --}}
                @if ($errors->any())
                    <div class="alert alert-danger py-2 px-3 small">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                {{-- Formulário --}}
                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <div class="mb-2">
                        <input type="email" name="email" class="form-control" placeholder="Digite seu e-mail" required autofocus>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-sm">Enviar link de recuperação</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="text-center mt-3 small">
            <a href="{{ route('login') }}">Voltar para login</a>
        </div>

    </div>
</div>
@endsection
