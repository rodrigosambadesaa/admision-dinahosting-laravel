# Ejercicio 1 — Fibonacci CLI (versión Laravel)

La estructura `PHP/` es obligatoria en el PDF original. Esta carpeta
contiene una entrada ejecutable **Laravel**, sin conservar un segundo
algoritmo PHP heredado fuera del framework. Los servicios están en
`app/Math/`, `app/Providers/`, `app/Service/` y el comando nativo en
`app/Console/Commands/FibonacciCommand.php`.

```bash
composer install
php PHP/fibonacci.php "ts:0" "ts:21"
# Forma nativa:
php artisan fibonacci "2026-01-01 00:00:00" "2026-12-31 23:59:59"
```

El contrato JSON, la precisión arbitraria de BigInteger y los límites UTC
son idénticos al ejercicio original. `JAVASCRIPT/` es el único ejercicio
con lógica JavaScript independiente, y `CSS/` es HTML5 sin JavaScript.
