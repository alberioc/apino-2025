@extends('layouts.guest')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">

        {{-- Logo da empresa --}}
        <div class="text-center mb-4">
            <img src="{{ asset('images/logo-site-empresas.png') }}" alt="Apino Turismo" style="max-width: 180px;">
        </div>

        {{-- Box do formulário --}}
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-3 text-center">Confirme sua senha</h5>

                <p class="mb-3 text-center">Por favor, confirme sua senha antes de continuar.</p>

                @if ($errors->any())
                    <div class="alert alert-danger py-2 px-3 small">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('password.confirm') }}">
                    @csrf

                    <div class="mb-2">
                        <input type="password" name="password" class="form-control" placeholder="Senha" required autofocus autocomplete="current-password">
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-sm">Confirmar senha</button>
                    </div>
                </form>

                <div class="text-center mt-3 small">
                    <a href="{{ route('password.request') }}">Esqueceu a senha?</a>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection
