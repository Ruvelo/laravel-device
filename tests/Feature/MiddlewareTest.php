<?php

declare(strict_types=1);

namespace Ruvelo\Device\Tests\Feature;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Ruvelo\Device\Testing\UserAgents;
use Ruvelo\Device\Tests\TestCase;

final class MiddlewareTest extends TestCase
{
    protected function defineRoutes($router): void
    {
        Route::middleware('device.share')->get('/shared', fn () => View::shared('device')->type->value);
        Route::middleware('device.share:visitor')->get('/named', fn () => View::shared('visitor')->browser->name);
        Route::middleware('device.hints')->get('/hints', fn () => 'ok');
        Route::middleware('device.hints:Sec-CH-UA-Model')->get('/model', fn () => 'ok');
    }

    public function test_share_device_puts_the_device_on_every_view(): void
    {
        $this->withHeader('User-Agent', UserAgents::IPAD)->get('/shared')->assertSeeText('tablet');
    }

    public function test_the_shared_variable_can_be_renamed(): void
    {
        $this->withHeader('User-Agent', UserAgents::EDGE_WINDOWS)->get('/named')->assertSeeText('Edge');
    }

    public function test_request_client_hints_sends_accept_ch(): void
    {
        $this->get('/hints')
            ->assertHeader('Accept-CH', 'Sec-CH-UA-Platform-Version, Sec-CH-UA-Model, Sec-CH-UA-Full-Version-List, Sec-CH-UA-Form-Factors')
            ->assertHeaderMissing('Critical-CH');
    }

    public function test_critical_hints_are_also_sent_as_critical_ch(): void
    {
        config()->set('device.client_hints.critical', ['Sec-CH-UA-Platform-Version']);

        $this->get('/hints')
            ->assertHeader('Critical-CH', 'Sec-CH-UA-Platform-Version')
            ->assertHeader('Accept-CH', 'Sec-CH-UA-Platform-Version, Sec-CH-UA-Model, Sec-CH-UA-Full-Version-List, Sec-CH-UA-Form-Factors');
    }

    public function test_route_parameters_choose_the_hints(): void
    {
        $this->get('/model')->assertHeader('Accept-CH', 'Sec-CH-UA-Model');
    }

    public function test_nothing_is_sent_when_no_hints_are_configured(): void
    {
        config()->set('device.client_hints.request', []);

        $this->get('/hints')->assertHeaderMissing('Accept-CH')->assertHeaderMissing('Critical-CH');
    }
}
