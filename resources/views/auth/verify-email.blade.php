@extends('layouts.guest')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">

        <div class="text-center mb-4">
            <img src="{{ asset('images/logo-site-empresas.png') }}" alt="Apino Turismo" style="max-width: 180px;">
        </div>

        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h5 class="mb-3">Verifique seu e-mail</h5>

                @if (session('status') == 'verification-link-sent')
                    <div class="alert alert-success py-2 px-3 small">
                        Um novo link de verificação foi enviado para seu e-mail.
                    </div>
                @endif

                <p>Antes de continuar, por favor, verifique seu e-mail clicando no link que enviamos.</p>

                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm mb-3">Reenviar link de verificação</button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm">Sair</button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
