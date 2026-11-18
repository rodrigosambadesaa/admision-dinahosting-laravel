# Prueba técnica Dinahosting — Laravel

Reimplementación en Laravel de los tres ejercicios de
[Dinahosting original](https://github.com/rodrigosambadesaa/admision-dinahosting)
y la variante [Django](https://github.com/rodrigosambadesaa/admision-dinahosting-django).

## Funcionalidades y equivalencia

| Ejercicio | Rutas Laravel | Comportamiento |
| --- | --- | --- |
| PHP CLI | **php artisan fibonacci** y **/php/**, **/PHP/index.html** | JSON idéntico al CLI original, con tres grupos (mes, año, personalizado) |
| JavaScript | **/javascript/**, **/JAVASCRIPT/index.html** | El código JS y CSS original se sirve sin cambiar el algoritmo y calcula sin enviar datos al servidor |
| Login HTML5 | **/login/**, **/CSS/index.html** | Formulario de demostración, sin credenciales reales, con Bootstrap y CSS/SCSS original |
| Portada | **/**, **/index.html** | Navegación entre los tres ejercicios |
| Web adicional | **/fibonacci/** | Calculadora de rangos en Laravel, equivalente a la de Django |

Las clases PHP de dominio se trasladan desde el original a la estructura PSR-4 de Laravel:
BigInteger, DateRange, RangeBoundary, proveedores de rangos, generador y resolver Fibonacci.
El **BigInteger es una implementación decimal propia**, sin GMP ni BCMath. Las fechas están
estrictamente en UTC y aceptan el calendario gregoriano proléptico, incluidos los años 0000-0099.
Los intervalos son inclusivos, se normalizan si llegan invertidos y preservan los dos unos
de la sucesión 0, 1, 1, 2, ... Los timestamps sintéticos admiten **hasta 256 dígitos**,
con límites de entrada para impedir consumos arbitrarios de memoria y CPU.

## Desarrollo local

Necesita PHP 8.3+, Composer y las extensiones PHP habituales de Laravel, como mbstring.
Para ejecutar los tests JS, necesita Node 22+.

~~~bash
composer install
cp .env.example .env
php artisan key:generate
php artisan serve
~~~

Abrir http://127.0.0.1:8000/.

CLI:

~~~bash
php artisan fibonacci "ts:0" "ts:21"
php artisan fibonacci "2024-02-29 00:00:00" "2024-02-29 23:59:59"
php artisan fibonacci "ts:123456789012345678901234567890" "ts:123456789012345678901234568500"
~~~

El contrato JSON contiene, **en este orden**: current_month, current_year, custom_range.
Cada sección contiene range (start, end, timezone, start_timestamp, end_timestamp)
y fibonacci_timestamps; todos los timestamps de salida son cadenas decimales.

## Docker (solo desarrollo)

~~~bash
docker compose build --pull
docker compose up web
~~~

Abrir http://localhost:8087. Cambie WEB_PORT si lo necesita. La clave de cifrado
de Compose es **únicamente de desarrollo, pública y deliberadamente no secreta**.
No usar esta configuración ni el servidor php artisan serve en producción.
Para producción debe configurar una clave única, HTTPS, APP_DEBUG=false, un
servidor HTTP/PHP-FPM adecuado y un origen de confianza para assets.

La imagen base sigue los parches de PHP 8.4 y la CI rechaza versiones anteriores a
8.4.27, además de comprobar ajustes de PHP y el aislamiento del directorio public/.
Se rechazan los POST de login; **no existe autenticación, ni almacenamiento de contraseñas**.
La CSP permite scripts locales solamente en las páginas del ejercicio JS y los
prohíbe en el resto.

## Pruebas y CI

~~~bash
php artisan test
node --test tests/javascript.test.cjs
python tests/differential.py
~~~

El test diferencial necesita checkout de los repositorios original y Django bajo
reference/ y django/ respectivamente, con PHP, Python/Django y Node instalados.

El workflow ci.yml prueba PHP 8.3/8.4/8.5, unidades, rutas, cabeceras de seguridad,
integridad exacta de los assets JS/CSS contra el original, comparación diferencial
de los cuatro lenguajes y smoke real con Docker.

## Benchmark reproducible

El workflow fibonacci-benchmark.yml usa en el **mismo runner Ubuntu** los repositorios
original y Django fijados a commits verificables junto a esta rama Laravel:

- Núcleo caliente PHP, JavaScript/Node, Python/Django y Laravel: CPU por resolución.
- CLI completo PHP, Django y Laravel: CPU de proceso, memoria pico RSS y tiempo de pared.
- Cuatro rangos: 0-21, fechas reales, 80 dígitos y 250 dígitos.
- Cinco muestras por caso y runtime, un proceso previo descartado y SHA-256 de cada resultado.

~~~bash
python benchmarks/compare.py --original-dir reference --django-dir django \
    --laravel-dir . --output-dir benchmark-results
~~~

Los artefactos contienen results.json (muestras y medianas) y summary.md (tablas);
las cifras entre runners compartidos son orientativas, no una medida absoluta
de superioridad entre lenguajes. El núcleo Laravel utiliza las clases portadas
sin ejecutar el bootstrap del framework, mientras que el CLI sí incluye Artisan.
