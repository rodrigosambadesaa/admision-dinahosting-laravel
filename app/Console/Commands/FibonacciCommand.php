<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Providers\CurrentMonthRangeProvider;
use App\Providers\CurrentYearRangeProvider;
use App\Providers\CustomRangeProvider;
use App\Service\FibonacciRangeResolver;
use App\Service\FibonacciSequenceGenerator;
use Illuminate\Console\Command;
use Throwable;

final class FibonacciCommand extends Command
{
    protected $signature = 'fibonacci {start : UTC date or ts:<bigint>} {end : UTC date or ts:<bigint>}';

    protected $description = 'Calculate Fibonacci timestamps for current month, year and a custom inclusive range';

    public function handle(FibonacciRangeResolver $resolver): int
    {
        try {
            $start = $this->argument('start');
            $end = $this->argument('end');
            if (!is_string($start) || !is_string($end) || $start === '' || $end === '') {
                throw new \InvalidArgumentException('Two non-empty custom range values are required.');
            }

            $result = [];
            foreach ([
                new CurrentMonthRangeProvider(),
                new CurrentYearRangeProvider(),
                new CustomRangeProvider($start, $end),
            ] as $provider) {
                $range = $provider->range();
                $result[$provider->name()] = [
                    'range' => $range->toArray(),
                    'fibonacci_timestamps' => $resolver->resolve($range),
                ];
            }

            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->components->error($exception->getMessage());
            return self::FAILURE;
        }
    }
}
