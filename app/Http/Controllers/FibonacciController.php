<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Providers\CurrentMonthRangeProvider;
use App\Providers\CurrentYearRangeProvider;
use App\Providers\CustomRangeProvider;
use App\Service\FibonacciRangeResolver;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use InvalidArgumentException;

final class FibonacciController extends Controller
{
    public function index(Request $request, FibonacciRangeResolver $resolver)
    {
        $data = $request->isMethod('post') ? $request->post() : $request->query();
        $hasInput = array_key_exists('start_date', $data) || array_key_exists('end_date', $data);
        $start = $data['start_date'] ?? '';
        $end = $data['end_date'] ?? '';
        $errors = [];
        $sections = [];

        if ($hasInput) {
            foreach (['start_date' => $start, 'end_date' => $end] as $field => $value) {
                if (!is_string($value) || strlen($value) > 260 || trim($value) === '') {
                    $errors[$field] = 'La entrada recibida no es válida.';
                }
            }

            if (!$errors) {
                $start = trim($start);
                $end = trim($end);
                try {
                    $providers = [
                        ['label' => 'Mes actual', 'provider' => new CurrentMonthRangeProvider()],
                        ['label' => 'Año actual', 'provider' => new CurrentYearRangeProvider()],
                        ['label' => 'Rango personalizado', 'provider' => new CustomRangeProvider($start, $end)],
                    ];

                    foreach ($providers as $item) {
                        $range = $item['provider']->range();
                        $sections[] = [
                            'label' => $item['label'],
                            'range' => $range->toArray(),
                            'values' => $resolver->resolve($range),
                        ];
                    }
                } catch (InvalidArgumentException $exception) {
                    $sections = [];
                    $errors['start_date'] = 'Usa "Y-m-d H:i:s" o "ts:<bigint>" con fechas válidas.';
                }
            }
        }

        return view('fibonacci', [
            'start' => is_string($start) ? $start : '',
            'end' => is_string($end) ? $end : '',
            'errors' => $errors,
            'sections' => $sections,
        ]);
    }
}
