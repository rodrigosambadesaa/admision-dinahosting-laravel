<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class RouteTest extends TestCase
{
    public function test_all_exercises_and_original_entrypoints_are_available(): void
    {
        foreach ([
            '/' => 'Prueba técnica Dinahosting',
            '/index.html' => 'Prueba técnica Dinahosting',
            '/php/' => 'Fibonacci en PHP y Laravel',
            '/PHP/index.html' => 'Fibonacci en PHP y Laravel',
            '/javascript/' => 'fibonacci-form',
            '/JAVASCRIPT/index.html' => 'fibonacci-form',
            '/login/' => 'Inicia sesión',
            '/CSS/index.html' => 'Inicia sesión',
            '/fibonacci/' => 'Fibonacci entre timestamps UTC',
        ] as $url => $expected) {
            $this->get($url)->assertOk()->assertSee($expected);
        }
    }

    public function test_server_calculator_and_validation(): void
    {
        $this->get('/fibonacci/?start_date=ts%3A0&end_date=ts%3A21')
            ->assertOk()->assertSee('Rango personalizado')
            ->assertSee('<li>21</li>', false);
        $this->get('/fibonacci/?start_date=ts%3A21&end_date=ts%3A5')
            ->assertOk()->assertSee('<li>5</li>', false);
        $this->get('/fibonacci/?start_date=2023-02-29%2000%3A00%3A00&end_date=ts%3A21')
            ->assertOk()->assertSee('fechas válidas')->assertDontSee('class="results-grid"', false);
        $this->get('/fibonacci/?start_date%5B%5D=bad&end_date=ts%3A21')
            ->assertOk()->assertSee('La entrada recibida no es válida.');
        $this->get('/fibonacci/?start_date=%3Cscript%3Ealert(1)%3C%2Fscript%3E&end_date=ts%3A1')
            ->assertOk()->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_security_headers_are_scoped_to_browser_script_page(): void
    {
        foreach (['/', '/login/', '/CSS/index.html', '/php/', '/fibonacci/'] as $path) {
            $this->get($path)->assertOk()
                ->assertHeader('X-Frame-Options', 'DENY')
                ->assertHeader('X-Content-Type-Options', 'nosniff');
            $this->assertStringContainsString("script-src 'none'",
                $this->get($path)->headers->get('Content-Security-Policy'));
        }
        foreach (['/javascript/', '/JAVASCRIPT/index.html'] as $path) {
            $response = $this->get($path)->assertOk();
            $this->assertStringContainsString("script-src 'self'", $response->headers->get('Content-Security-Policy'));
            $this->assertStringContainsString("frame-ancestors 'none'", $response->headers->get('Content-Security-Policy'));
        }
    }

    public function test_demo_login_never_authenticates(): void
    {
        $this->get('/login/')->assertOk()->assertSee('Demostración sin autenticación')
            ->assertSee('action="https://dinahosting.com/login-usuarios"', false);
        $this->assertStringContainsString('form-action https://dinahosting.com',
            $this->get('/login/')->headers->get('Content-Security-Policy'));
        $this->assertStringContainsString('form-action https://dinahosting.com',
            $this->get('/CSS/index.html')->headers->get('Content-Security-Policy'));
        $this->post('/login/', ['usuario' => 'fake@example.com', 'contrasena' => 'Fake1234'])
            ->assertStatus(405);
        $this->post('/CSS/index.html', ['usuario' => 'fake@example.com'])->assertStatus(405);
    }
}
