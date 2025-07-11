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
                <h5 class="card-title mb-3 text-center">Resetar Senha</h5>

                @if ($errors->any())
                    <div class="alert alert-danger py-2 px-3 small">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf

                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <div class="mb-2">
                        <input type="email" name="email" class="form-control" placeholder="E-mail" value="{{ old('email', $request->email) }}" required autofocus>
                    </div>

                    <div class="mb-2">
                        <input type="password" name="password" class="form-control" placeholder="Nova senha" required>
                    </div>

                    <div class="mb-2">
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Confirme a nova senha" required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-sm">Resetar senha</button>
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
