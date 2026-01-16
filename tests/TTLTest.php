<?php

declare(strict_types=1);

/**
 * Copyright (c) 2022 Kai Sassnowski
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/laravel-arcanist/arcanist
 */

namespace Arcanist\Tests;

use Arcanist\TTL;
use Carbon\Carbon;
use InvalidArgumentException;

class TTLTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider validValueProvider
     */
    public function testItCanBeTurnedIntoADate(int $value, callable $expectedDate): void
    {
        Carbon::setTestNow(now());

        $ttl = TTL::fromSeconds($value);

        self::assertTrue($ttl->expiresAfter()->eq($expectedDate()));
    }

    public static function validValueProvider(): iterable
    {
        yield from [
            [0, fn () => now()],
            [24 * 60 * 60, fn () => now()->subDay()],
            [60, fn () => now()->subMinute()],
        ];
    }

    public function testItCannotBeNegative(): void
    {
        $this->expectException(InvalidArgumentException::class);

        TTL::fromSeconds(-1);
    }

    /**
     * @dataProvider secondsProvider
     */
    public function testItCanBeTurnedBackToSeconds(int $value): void
    {
        Carbon::setTestNow(now());

        $ttl = TTL::fromSeconds($value);

        self::assertEquals($value, $ttl->toSeconds());
    }

    public static function secondsProvider(): iterable
    {
        yield from [
            [0],
            [60],
            [24 * 60 * 60],
        ];
    }
}
