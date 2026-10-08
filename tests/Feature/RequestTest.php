<?php

declare(strict_types=1);

namespace Ruvelo\Device\Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Ruvelo\Device\Detector;
use Ruvelo\Device\Device;
use Ruvelo\Device\DeviceServiceProvider;
use Ruvelo\Device\Testing\UserAgents;
use Ruvelo\Device\Tests\TestCase;

final class RequestTest extends TestCase
{
    protected function defineRoutes($router): void
    {
        Route::middleware('web')->get('/device', fn (Request $request) => response()->json($request->device()));
        Route::middleware('web')->get('/static', fn () => response()->json(Device::fromRequest()));
    }

    public function test_the_request_macro_returns_the_device(): void
    {
        $this->withHeaders(['User-Agent' => UserAgents::IPHONE, 'Accept-Language' => 'de-CH,de;q=0.9'])
            ->getJson('/device')
            ->assertOk()
            ->assertJsonPath('type', 'phone')
            ->assertJsonPath('browser.name', 'Safari')
            ->assertJsonPath('os.name', 'iOS')
            ->assertJsonPath('languages', ['de-ch', 'de']);
    }

    public function test_from_request_reads_the_current_request(): void
    {
        $this->withHeader('User-Agent', UserAgents::GPTBOT)
            ->getJson('/static')
            ->assertJsonPath('type', 'bot')
            ->assertJsonPath('bot.name', 'GPTBot')
            ->assertJsonPath('bot.category', 'ai-crawler');
    }

    public function test_client_hints_on_the_request_are_used(): void
    {
        $this->withHeaders([
            'User-Agent' => UserAgents::CHROME_WINDOWS,
            'Sec-CH-UA' => '"Brave";v="141", "Not?A_Brand";v="8", "Chromium";v="141"',
            'Sec-CH-UA-Platform' => '"Windows"',
            'Sec-CH-UA-Platform-Version' => '"19.0.0"',
        ])->getJson('/device')
            ->assertJsonPath('browser.name', 'Brave')
            ->assertJsonPath('os.version', '11')
            ->assertJsonPath('used_client_hints', true);
    }

    public function test_the_ipad_cookie_turns_a_mac_into_an_ipad(): void
    {
        $this->withHeader('User-Agent', UserAgents::SAFARI_MAC)
            ->getJson('/device')
            ->assertJsonPath('type', 'desktop')
            ->assertJsonPath('might_be_ipad', true);

        // Set by JavaScript, so it arrives unencrypted through the web middleware.
        $this->withHeader('User-Agent', UserAgents::SAFARI_MAC)
            ->withUnencryptedCookie('device_ipad', '1')
            ->get('/device')
            ->assertJsonPath('type', 'tablet')
            ->assertJsonPath('os.name', 'iPadOS')
            ->assertJsonPath('os.version', '26.0')
            ->assertJsonPath('model', 'iPad')
            ->assertJsonPath('might_be_ipad', false);
    }

    public function test_the_ipad_cookie_does_not_affect_real_macs_using_chrome(): void
    {
        $this->withHeaders([
            'User-Agent' => UserAgents::CHROME_MAC,
            'Sec-CH-UA-Platform' => '"macOS"',
        ])->withUnencryptedCookie('device_ipad', '1')
            ->get('/device')
            ->assertJsonPath('type', 'desktop');
    }

    public function test_answers_are_kept_in_memory(): void
    {
        $detector = $this->app->make(Detector::class);

        $first = $detector->fromUserAgent(UserAgents::IPHONE);
        $second = $detector->fromUserAgent(UserAgents::IPHONE, ['Accept-Language' => 'fr']);

        $this->assertEquals($first->withLanguages(['fr']), $second);
        $this->assertSame(['fr'], $second->languages);
        $this->assertSame([], $first->languages);

        $detector->flush();
        $this->assertEquals($first, $detector->fromUserAgent(UserAgents::IPHONE));
    }

    public function test_answers_can_be_shared_through_a_cache_store(): void
    {
        config()->set('device.cache.store', 'array');
        $this->app->forgetInstance(Detector::class);

        $device = Device::fromUserAgent(UserAgents::IPHONE);

        $keys = array_keys((fn () => $this->storage)->call(Cache::store('array')->getStore()));
        $this->assertCount(1, $keys);
        $this->assertStringStartsWith('device:'.Detector::RULES_VERSION.':', $keys[0]);

        // A fresh detector (another worker) answers from the cache.
        $this->app->forgetInstance(Detector::class);
        $this->assertEquals($device, Device::fromUserAgent(UserAgents::IPHONE));
    }

    public function test_the_macro_can_be_turned_off(): void
    {
        Request::flushMacros();
        config()->set('device.request_macro', false);

        (new DeviceServiceProvider($this->app))->boot();

        $this->assertFalse(Request::hasMacro('device'));
    }
}
