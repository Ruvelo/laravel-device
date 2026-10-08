<?php

declare(strict_types=1);

namespace Ruvelo\Device\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use Ruvelo\Device\Device;
use Ruvelo\Device\Tests\TestCase;

final class UserAgentFixturesTest extends TestCase
{
    /** @return array<string, array{0: string, 1: array<string, string|null>}> */
    public static function userAgents(): array
    {
        return require __DIR__.'/../Fixtures/user-agents.php';
    }

    /** @param  array<string, string|null>  $expected */
    #[DataProvider('userAgents')]
    public function test_it_detects_real_user_agents(string $userAgent, array $expected): void
    {
        $device = Device::fromUserAgent($userAgent);

        $actual = [
            'type' => $device->type->value,
            'browser' => $device->browser->name,
            'browser_version' => $device->browser->version,
            'os' => $device->os->name,
            'os_version' => $device->os->version,
            'engine' => $device->engine->name,
            'engine_version' => $device->engine->version,
            'brand' => $device->brand,
            'model' => $device->model,
            'bot' => $device->botName,
            'category' => $device->botCategory?->value,
        ];

        $actual = array_intersect_key($actual, $expected);
        ksort($actual);
        ksort($expected);

        $this->assertSame($expected, $actual, $userAgent);
        $this->assertSame($device->type->value === 'bot', $device->isBot);
    }
}
