@extends('layouts.base')
@section('title', 'Prueba técnica Dinahosting')
@section('body_class', 'home-page')
@section('content')
<main class="shell">
    <section class="hero-card">
        <p class="eyebrow">Port a Laravel</p>
        <h1>Prueba técnica Dinahosting</h1>
        <p class="lead">Reimplementación de los tres ejercicios originales en Laravel: Fibonacci en PHP mediante Artisan, Fibonacci en Vanilla JavaScript y un formulario de login HTML5/CSS de demostración.</p>
        <div class="card-grid">
            <a class="nav-card" href="{{ route('php') }}">
                <span class="nav-label">Ejercicio 1 · PHP/Laravel</span>
                <strong>Fibonacci CLI y servidor</strong>
                <span>Comando Artisan y calculadora procesada por Laravel.</span>
            </a>
            <a class="nav-card" href="{{ route('javascript') }}">
                <span class="nav-label">Ejercicio 2 · JavaScript</span>
                <strong>Fibonacci en el navegador</strong>
                <span>Algoritmo original sin AJAX, con BigInteger propio.</span>
            </a>
            <a class="nav-card" href="{{ route('login') }}">
                <span class="nav-label">Ejercicio 3 · HTML5/CSS</span>
                <strong>Login HTML5</strong>
                <span>Formulario demostrativo sin autenticación real.</span>
            </a>
        </div>
        <p class="subtle">También está disponible la <a href="{{ route('fibonacci') }}">calculadora en el servidor</a> y los accesos históricos <code>/PHP/index.html</code>, <code>/JAVASCRIPT/index.html</code> y <code>/CSS/index.html</code>.</p>
        <p class="subtle">CLI: <code>php artisan fibonacci "ts:0" "ts:21"</code>.</p>
    </section>
</main>
@endsection
