# Respuestas del PDF y adecuación técnica — Laravel

El archivo **`Prueba Tca_PHP.pdf`** se conserva sin alterar las
respuestas históricas originales (Git blob SHA
`3fba2fcb5d8dd1fac76a89d6d150f20d44671d04`).
No se cambian las respuestas personales, como haber comenzado con C
o indicar Java como lenguaje principal en el momento de redactarlo.

## Aclaraciones técnicas necesarias

- **Kendo UI no es un framework MVC completo**: es sobre todo un conjunto de
  componentes de interfaz. **Laravel sí es un framework MVC**, con
  controladores, modelos Eloquent y vistas Blade.
- **`docker-compose up`** levanta servicios y construye imágenes cuando
  es necesario; para forzar la reconstrucción se añade `--build`.
- **ORM** significa mapeo objeto-relacional y Laravel ofrece Eloquent;
  no se trata solo de una práctica genérica de programación.
- **Regex de HTML**: la expresión mencionada reconoce etiquetas,
  comentarios y algunos bloques de estilo/script, pero las regex no son
  un sustituto de un parser HTML o sanitizer resistente a XSS.
- **`git stash`** guarda cambios temporales del índice y el árbol
  de trabajo; para incluir ficheros no rastreados se necesita `-u`.

La valoración del código PHP de la página 2 sigue siendo adecuada:
`$form` puede no haberse inicializado, existe un `catch` redundante,
y el controlador acopla acceso a configuración, servicios y persistencia.

## Estructura exigida

| Carpeta | Implementación |
| --- | --- |
| `PHP/` | Adaptador CLI a **Laravel Artisan** y clases `App\*` |
| `JAVASCRIPT/` | Formulario HTML + **Vanilla JavaScript ES6+**, sin backend |
| `CSS/` | HTML5 + Bootstrap sin JS + CSS/SCSS |

El PDF exige `POST https://dinahosting.com/login-usuarios`. Ese
destino se indica en los formularios de login y se permite por CSP solo
para ese ejercicio. No se implementa autenticación propia; el envío
real se dirige al tercero, por lo que se deben emplear **datos ficticios**.
