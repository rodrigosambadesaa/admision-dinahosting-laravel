<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

final class CommandTest extends TestCase
{
    public function test_cli_json_contract_and_original_group_order(): void
    {
        self::assertSame(0, Artisan::call('fibonacci', ['start' => 'ts:0', 'end' => 'ts:21']));
        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(['current_month', 'current_year', 'custom_range'], array_keys($payload));
        self::assertSame(['0', '1', '1', '2', '3', '5', '8', '13', '21'],
            $payload['custom_range']['fibonacci_timestamps']);
        self::assertSame([
            'start' => 'ts:0', 'end' => 'ts:21', 'timezone' => 'synthetic-bigint',
            'start_timestamp' => '0', 'end_timestamp' => '21',
        ], $payload['custom_range']['range']);
    }

    public function test_invalid_cli_returns_failure_and_reversed_dates_normalize(): void
    {
        self::assertSame(0, Artisan::call('fibonacci', [
            'start' => '2024-02-29 23:59:59', 'end' => '2024-02-29 00:00:00',
        ]));
        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('2024-02-29 00:00:00', $payload['custom_range']['range']['start']);
        self::assertSame(1, Artisan::call('fibonacci', [
            'start' => 'ts:' . str_repeat('9', 257), 'end' => 'ts:0',
        ]));
    }
}
