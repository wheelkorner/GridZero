@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@section('adminlte_css_pre')
<link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
@stop

@php( $login_url = View::getSection('login_url') ?? config('adminlte.login_url', 'login') )

@if (config('adminlte.use_route_url', false))
@php(    $login_url = $login_url ? route($login_url) : '' )
@else
@php(    $login_url = $login_url ? url($login_url) : '' )
@endif

@section('auth_header', 'Painel Restrito - ROOT ACCESS')

@section('auth_body')
<form action="{{ url('admin/login') }}" method="post">
    @csrf

    {{-- Username field --}}
    <div class="input-group mb-3">
        <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
            value="{{ old('username') }}" placeholder="Identificador de Rede" autofocus required>

        <div class="input-group-append">
            <div class="input-group-text">
                <span class="fas fa-user {{ config('adminlte.classes_auth_icon', '') }}"></span>
            </div>
        </div>

        @error('username')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    {{-- Password field --}}
    <div class="input-group mb-3">
        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
            placeholder="Chave de Acesso" required>

        <div class="input-group-append">
            <div class="input-group-text">
                <span class="fas fa-lock {{ config('adminlte.classes_auth_icon', '') }}"></span>
            </div>
        </div>

        @error('password')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    {{-- Login button --}}
    <div class="row">
        <div class="col-12">
            <button type="submit"
                class="btn btn-block {{ config('adminlte.classes_auth_btn', 'btn-flat btn-primary') }}">
                <span class="fas fa-sign-in-alt"></span>
                AUTENTICAR
            </button>
        </div>
    </div>

</form>
@stop

@section('auth_footer')
<p class="my-0">
    <a href="{{ url('/') }}">
        Voltar ao Grid Principal
    </a>
</p>
@stop