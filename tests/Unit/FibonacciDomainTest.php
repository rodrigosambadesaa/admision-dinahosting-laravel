<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Math\BigInteger;
use App\Model\DateRange;
use App\Providers\CustomRangeProvider;
use App\Providers\CurrentMonthRangeProvider;
use App\Providers\CurrentYearRangeProvider;
use App\Service\FibonacciRangeResolver;
use App\Service\FibonacciSequenceGenerator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class FibonacciDomainTest extends TestCase
{
    public function test_signed_big_integer_normalization_comparison_and_carries(): void
    {
        self::assertSame('0', (string) BigInteger::fromString('-0000'));
        self::assertSame('42', (string) BigInteger::fromString('+00042'));
        self::assertSame('-2', (string) BigInteger::fromString('-0002'));
        self::assertSame('100000000000000000000',
            (string) BigInteger::fromString('99999999999999999999')->add(BigInteger::fromInt(1)));
        self::assertSame('-1', (string) BigInteger::fromString('-3')->add(BigInteger::fromInt(2)));
        self::assertSame(0, BigInteger::fromString('-0')->compare(BigInteger::fromInt(0)));
        self::assertGreaterThan(0, BigInteger::fromString(str_repeat('9', 80) . '9')
            ->compare(BigInteger::fromString(str_repeat('9', 80) . '8')));
        self::assertSame(str_repeat('9', 256), (string) BigInteger::fromString('+' . str_repeat('9', 256)));
    }

    public function test_invalid_big_integer_inputs_are_rejected(): void
    {
        foreach (['1e3', '1.5', '', '١', str_repeat('9', 257), ' 2 ', '--3'] as $value) {
            try {
                BigInteger::fromString($value);
                self::fail("Accepted invalid bigint: " . $value);
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    public function test_fibonacci_duplicate_one_and_inclusive_inverted_ranges(): void
    {
        $resolver = new FibonacciRangeResolver(new FibonacciSequenceGenerator());
        $gen = new FibonacciSequenceGenerator();
        self::assertSame(['0', '1', '1', '2', '3', '5', '8', '13'],
            array_map('strval', $gen->generateUpTo(BigInteger::fromInt(13))));
        self::assertSame([], $gen->generateUpTo(BigInteger::fromInt(-1)));
        self::assertSame(['0'], array_map('strval', $gen->generateUpTo(BigInteger::fromInt(0))));

        foreach ([['5', '13'], ['13', '5']] as [$start, $end]) {
            self::assertSame(['5', '8', '13'],
                $resolver->resolve(DateRange::fromTimestampStrings($start, $end)));
        }
        self::assertSame(['1', '1'], $resolver->resolve(DateRange::fromTimestampStrings('1', '1')));
        self::assertSame([], $resolver->resolve(DateRange::fromTimestampStrings('-50', '-1')));
        self::assertSame(['354224848179261915075'],
            $resolver->resolve(DateRange::fromTimestampStrings('354224848179261915075', '354224848179261915075')));
    }

    public function test_strict_dates_mixed_boundaries_and_year_zero(): void
    {
        foreach ([
            ['1970-01-01 00:00:00', '0'],
            ['0000-01-01 00:00:00', '-62167219200'],
            ['0000-02-29 00:00:00', '-62162121600'],
            ['2024-02-29 00:00:00', '1709164800'],
        ] as [$date, $timestamp]) {
            self::assertSame($timestamp,
                (new CustomRangeProvider($date, $date))->range()->toArray()['start_timestamp']);
        }
        foreach (['2023-02-29 00:00:00', '1900-02-29 00:00:00',
                  '2024-02-30 00:00:00', '2024-13-01 00:00:00',
                  '2024-01-01 24:00:00', '2024-1-01 00:00:00',
                  '2024-01-01T00:00:00', '2024-01-01 00:00:00 junk',
                  'ts:' . str_repeat('9', 257)] as $invalid) {
            try {
                (new CustomRangeProvider($invalid, 'ts:13'))->range();
                self::fail("Accepted invalid range: " . $invalid);
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
        $range = (new CustomRangeProvider('ts:13', '1970-01-01 00:00:05'))->range()->toArray();
        self::assertSame('5', $range['start_timestamp']);
        self::assertSame('13', $range['end_timestamp']);
        self::assertSame('UTC -> synthetic-bigint', $range['timezone']);
        foreach ([new CurrentMonthRangeProvider(), new CurrentYearRangeProvider()] as $provider) {
            self::assertSame('UTC', $provider->range()->toArray()['timezone']);
        }
    }
}
