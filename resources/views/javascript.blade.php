<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fibonacci en rangos UTC</title>
    <link rel="stylesheet" href="{{ asset('assets/javascript/styles.css') }}">
</head>
<body>
<main class="page">
    <section class="hero">
        <p class="eyebrow">Prueba técnica</p>
        <h1>Fibonacci entre timestamps UTC</h1>
        <p class="lead">Introduce dos fechas con formato <code>Y-m-d H:i:s</code> y obtén en la misma vista los resultados para el mes actual, el año actual y tu rango personalizado.</p>
        <p class="lead">Para pruebas extremas también puedes usar <code>ts:&lt;bigint&gt;</code>, por ejemplo <code>ts:123456789012345678901234567890</code>. Esta versión usa la implementación original de <code>BigInteger</code> sin el tipo <code>BigInt</code> nativo.</p>
    </section>
    <section class="panel">
        <form id="fibonacci-form" novalidate autocomplete="off" accept-charset="UTF-8">
            <label for="start_date">Fecha de inicio (UTC)</label>
            <input id="start_date" name="start_date" type="text" inputmode="text" maxlength="260" placeholder="2026-06-01 00:00:00 o ts:12345678901234567890" spellcheck="false" autocapitalize="off" autocomplete="off" required>
            <label for="end_date">Fecha de fin (UTC)</label>
            <input id="end_date" name="end_date" type="text" inputmode="text" maxlength="260" placeholder="2026-06-30 23:59:59 o ts:12345678901234567999" spellcheck="false" autocapitalize="off" autocomplete="off" required>
            <button type="submit">Calcular</button>
            <p id="error-message" class="error-message" aria-live="polite"></p>
        </form>
    </section>
    <section id="results" class="results" aria-live="polite"></section>
</main>
<script src="{{ asset('assets/javascript/fibonacci.js') }}"></script>
</body>
</html>
