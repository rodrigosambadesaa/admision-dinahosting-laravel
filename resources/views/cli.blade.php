@extends('layouts.base')
@section('title', 'Ejercicio 1 - Fibonacci CLI en Laravel')
@section('body_class', 'home-page')
@section('content')
<main class="shell"><section class="hero-card">
    <p class="eyebrow">Ejercicio 1 · PHP y Laravel</p>
    <h1>Fibonacci en PHP y Laravel</h1>
    <p class="lead">Comando Artisan con clases, interfaces, proveedores de rangos y aritmética BigInteger decimal propia, sin GMP ni BCMath. Preserva el contrato JSON del CLI original.</p>
    <pre><code>php artisan fibonacci "2026-06-01 00:00:00" "2026-06-30 23:59:59"</code></pre>
    <pre><code>php artisan fibonacci "ts:123456789012345678901234567890" "ts:123456789012345678901234568500"</code></pre>
    <p>Salida JSON: <code>current_month</code>, <code>current_year</code>, <code>custom_range</code> y todos los timestamps como cadenas.</p>
    <p><a href="{{ route('fibonacci') }}">Calculadora procesada en Laravel</a> · <a href="{{ route('javascript') }}">Calculadora JavaScript original</a> · <a href="{{ route('home') }}">Portada</a></p>
</section></main>
@endsection
