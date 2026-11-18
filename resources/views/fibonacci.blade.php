@extends('layouts.base')
@section('title', 'Fibonacci en rangos UTC')
@section('body_class', 'fibonacci-page')
@section('content')
<main class="shell layout-stack">
    <section class="hero-text">
        <p class="eyebrow">Prueba técnica</p>
        <h1>Fibonacci entre timestamps UTC</h1>
        <p class="lead">Introduce dos fechas <code>Y-m-d H:i:s</code> y obtén los resultados para el mes actual, año actual y rango personalizado.</p>
        <p class="lead">También se admiten timestamps sintéticos de hasta 256 dígitos: <code>ts:&lt;bigint&gt;</code>.</p>
    </section>
    <section class="panel-card">
        <form method="get" action="{{ route('fibonacci') }}" novalidate autocomplete="off">
            <div class="field-group">
                <label for="start_date">Fecha de inicio (UTC)</label>
                <input id="start_date" name="start_date" type="text" maxlength="260" required
                    spellcheck="false" autocapitalize="off" autocomplete="off"
                    value="{{ $start }}" placeholder="2026-06-01 00:00:00 o ts:12345678901234567890">
                @if(isset($errors['start_date']))<p class="error-message" role="alert">{{ $errors['start_date'] }}</p>@endif
            </div>
            <div class="field-group">
                <label for="end_date">Fecha de fin (UTC)</label>
                <input id="end_date" name="end_date" type="text" maxlength="260" required
                    spellcheck="false" autocapitalize="off" autocomplete="off"
                    value="{{ $end }}" placeholder="2026-06-30 23:59:59 o ts:12345678901234567999">
                @if(isset($errors['end_date']))<p class="error-message" role="alert">{{ $errors['end_date'] }}</p>@endif
            </div>
            <button type="submit" class="button-primary">Calcular</button>
        </form>
    </section>
    @if(count($sections) > 0)
    <section class="results-grid">
        @foreach($sections as $section)
            <article class="result-card">
                <h2>{{ $section['label'] }}</h2>
                <p><strong>Inicio:</strong> {{ $section['range']['start'] }}</p>
                <p><strong>Fin:</strong> {{ $section['range']['end'] }}</p>
                <p><strong>Timezone / modo:</strong> {{ $section['range']['timezone'] }}</p>
                @if(count($section['values']) > 0)
                    <ul class="result-list">
                        @foreach($section['values'] as $value)<li>{{ $value }}</li>@endforeach
                    </ul>
                @else
                    <p class="empty-state">No hay timestamps Fibonacci dentro de este rango.</p>
                @endif
            </article>
        @endforeach
    </section>
    @endif
</main>
@endsection
