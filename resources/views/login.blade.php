@extends('layouts.base')
@section('title', 'Login HTML5')
@section('body_class', 'login-page')
@push('head')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<link rel="stylesheet" href="{{ asset('assets/login/style.css') }}">
@endpush
@section('content')
<main class="container py-5">
    <div class="row justify-content-center"><div class="col-12 col-md-8 col-lg-5">
        <section class="login-card">
            <p class="login-kicker">Acceso clientes</p>
            <h1 class="login-title">Inicia sesión</h1>
            <p class="login-copy">Formulario HTML5 sin JavaScript, con validación nativa y estilos apoyados en Bootstrap.</p>
            <p class="form-text" role="note">Demostración sin autenticación: utiliza exclusivamente datos ficticios. No se validan cuentas reales.</p>
            <form action="{{ route('login') }}" method="post" class="needs-validation" accept-charset="UTF-8" autocomplete="off">
                @csrf
                <div class="mb-3">
                    <label for="usuario" class="form-label">Usuario</label>
                    <input id="usuario" name="usuario" type="email" class="form-control form-control-lg" placeholder="nombre@dominio.com" autocomplete="username" required>
                </div>
                <div class="mb-3">
                    <label for="contrasena" class="form-label">Contraseña</label>
                    <input id="contrasena" name="contrasena" type="password" class="form-control form-control-lg" minlength="8" maxlength="64" pattern="(?=.*[a-z])(?=.*[A-Z])[A-Za-z0-9]{8,64}" autocomplete="off" required>
                    <div class="form-text">Mínimo 8 caracteres, al menos una mayúscula, una minúscula y sin caracteres especiales.</div>
                </div>
                <div class="form-check mb-4">
                    <input id="recordar_contrasena" name="recordar_contrasena" type="checkbox" class="form-check-input">
                    <label class="form-check-label" for="recordar_contrasena">Recordar contraseña</label>
                </div>
                <button type="submit" class="btn btn-brand btn-lg w-100">Entrar</button>
            </form>
        </section>
    </div></div>
</main>
@endsection
