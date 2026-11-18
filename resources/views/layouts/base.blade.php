<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer">
    <title>@yield('title', 'Prueba técnica Dinahosting')</title>
    <link rel="stylesheet" href="{{ asset('assets/app.css') }}">
    @stack('head')
</head>
<body class="@yield('body_class')">
    @yield('content')
</body>
</html>
