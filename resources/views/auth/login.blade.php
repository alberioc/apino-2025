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
                <h5 class="card-title mb-3 text-center">Área do Cliente</h5>

                @if (session('status'))
                    <div class="alert alert-success py-2 px-3 small">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger py-2 px-3 small">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-2">
                        <input type="email" name="email" class="form-control" placeholder="E-mail"
                               value="{{ old('email') }}" required autofocus>
                    </div>

                    <div class="mb-2">
                        <input type="password" name="password" class="form-control" placeholder="Senha"
                               required autocomplete="current-password">
                    </div>

                    <div class="d-flex justify-content-between mb-2 small">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember"
                                   {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">Lembrar</label>
                        </div>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}">Esqueceu a senha?</a>
                        @endif
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-sm">Entrar</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
