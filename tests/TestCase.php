<?php

declare(strict_types=1);

namespace Ruvelo\Device\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Ruvelo\Device\DeviceServiceProvider;
use Ruvelo\Device\Facades\Agent;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [DeviceServiceProvider::class];
    }

    protected function getPackageAliases($app): array
    {
        return ['Agent' => Agent::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('d', 32)));
    }
}
