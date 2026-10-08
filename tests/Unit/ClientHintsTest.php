<?php

declare(strict_types=1);

namespace Ruvelo\Device\Tests\Unit;

use Ruvelo\Device\Detection\ClientHints;
use Ruvelo\Device\Detector;
use Ruvelo\Device\Device;
use Ruvelo\Device\Testing\UserAgents;
use Ruvelo\Device\Tests\TestCase;

final class ClientHintsTest extends TestCase
{
    private const CHROME_141 = '"Google Chrome";v="141", "Not?A_Brand";v="8", "Chromium";v="141"';

    public function test_windows_11_is_told_apart_from_windows_10(): void
    {
        $eleven = Device::fromUserAgent(UserAgents::CHROME_WINDOWS, [
            'Sec-CH-UA' => self::CHROME_141,
            'Sec-CH-UA-Platform' => '"Windows"',
            'Sec-CH-UA-Platform-Version' => '"19.0.0"',
        ]);
        $ten = Device::fromUserAgent(UserAgents::CHROME_WINDOWS, [
            'Sec-CH-UA' => self::CHROME_141,
            'Sec-CH-UA-Platform' => '"Windows"',
            'Sec-CH-UA-Platform-Version' => '"10.0.0"',
        ]);
        $seven = Device::fromUserAgent(UserAgents::CHROME_WINDOWS, [
            'Sec-CH-UA-Platform' => '"Windows"',
            'Sec-CH-UA-Platform-Version' => '"0.1.0"',
        ]);

        $this->assertSame('Windows 11', (string) $eleven->os);
        $this->assertSame('Windows 10', (string) $ten->os);
        $this->assertSame('7', $seven->os->version);
        $this->assertTrue($eleven->usedClientHints);
        $this->assertSame('desktop', $eleven->type->value);
    }

    public function test_the_full_version_list_gives_the_real_browser_version(): void
    {
        $device = Device::fromUserAgent(UserAgents::CHROME_WINDOWS, [
            'Sec-CH-UA' => self::CHROME_141,
            'Sec-CH-UA-Full-Version-List' => '"Google Chrome";v="141.0.7390.66", "Not?A_Brand";v="8.0.0.0", "Chromium";v="141.0.7390.66"',
        ]);

        $this->assertSame('Chrome', $device->browser->name);
        $this->assertSame('141.0.7390.66', $device->browser->version);
        $this->assertSame('Blink', $device->engine->name);
        $this->assertSame('141.0.7390.66', $device->engine->version);
    }

    public function test_the_major_version_alone_keeps_the_user_agent_version(): void
    {
        $device = Device::fromUserAgent(UserAgents::CHROME_WINDOWS, ['Sec-CH-UA' => self::CHROME_141]);

        $this->assertSame('141.0.0.0', $device->browser->version);
    }

    public function test_brave_is_recognised_although_its_user_agent_is_chrome(): void
    {
        $withoutHints = Device::fromUserAgent(UserAgents::CHROME_WINDOWS);
        $withHints = Device::fromUserAgent(UserAgents::CHROME_WINDOWS, [
            'Sec-CH-UA' => '"Brave";v="141", "Not?A_Brand";v="8", "Chromium";v="141"',
        ]);

        $this->assertSame('Chrome', $withoutHints->browser->name);
        $this->assertSame('Brave', $withHints->browser->name);
        $this->assertSame('141', $withHints->browser->version);
    }

    public function test_edge_and_opera_brands(): void
    {
        $edge = Device::fromUserAgent(UserAgents::EDGE_WINDOWS, ['Sec-CH-UA' => '"Microsoft Edge";v="141", "Not?A_Brand";v="8", "Chromium";v="141"']);
        $opera = Device::fromUserAgent(UserAgents::CHROME_WINDOWS, ['Sec-CH-UA' => '"Chromium";v="140", "Not=A?Brand";v="24", "Opera";v="124"']);

        $this->assertSame('Edge', $edge->browser->name);
        $this->assertSame('Opera', $opera->browser->name);
        $this->assertSame('124', $opera->browser->version);
    }

    public function test_a_more_specific_user_agent_beats_a_generic_chromium_brand(): void
    {
        $vivaldi = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Vivaldi/7.6.3797.52';

        $device = Device::fromUserAgent($vivaldi, ['Sec-CH-UA' => '"Chromium";v="140", "Not=A?Brand";v="24"']);

        $this->assertSame('Vivaldi', $device->browser->name);
        $this->assertSame('7.6.3797.52', $device->browser->version);
    }

    public function test_android_model_and_version_come_from_hints_when_the_user_agent_is_reduced(): void
    {
        $device = Device::fromUserAgent(UserAgents::CHROME_ANDROID, [
            'Sec-CH-UA' => self::CHROME_141,
            'Sec-CH-UA-Mobile' => '?1',
            'Sec-CH-UA-Platform' => '"Android"',
            'Sec-CH-UA-Platform-Version' => '"16.0.0"',
            'Sec-CH-UA-Model' => '"Pixel 9 Pro"',
        ]);

        $this->assertSame('phone', $device->type->value);
        $this->assertSame('Android 16', (string) $device->os);
        $this->assertSame('Pixel 9 Pro', $device->model);
        $this->assertSame('Google', $device->brand);
    }

    public function test_android_tablets_send_mobile_false(): void
    {
        $device = Device::fromUserAgent(UserAgents::CHROME_ANDROID, [
            'Sec-CH-UA-Mobile' => '?0',
            'Sec-CH-UA-Platform' => '"Android"',
        ]);

        $this->assertSame('tablet', $device->type->value);
    }

    public function test_form_factors_win_over_the_mobile_flag(): void
    {
        $tablet = Device::fromUserAgent(UserAgents::CHROME_ANDROID, [
            'Sec-CH-UA-Mobile' => '?1',
            'Sec-CH-UA-Platform' => '"Android"',
            'Sec-CH-UA-Form-Factors' => '"Tablet"',
        ]);
        $watch = Device::fromUserAgent(UserAgents::CHROME_ANDROID, [
            'Sec-CH-UA-Platform' => '"Android"',
            'Sec-CH-UA-Form-Factors' => '"Watch"',
        ]);

        $this->assertSame('tablet', $tablet->type->value);
        $this->assertSame('wearable', $watch->type->value);
    }

    public function test_android_in_desktop_mode_is_still_android(): void
    {
        // "Request desktop site" swaps the User-Agent for a Linux one; the hints stay honest.
        $device = Device::fromUserAgent('Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', [
            'Sec-CH-UA-Mobile' => '?0',
            'Sec-CH-UA-Platform' => '"Android"',
            'Sec-CH-UA-Form-Factors' => '"Mobile"',
        ]);

        $this->assertSame('Android', $device->os->name);
        $this->assertSame('phone', $device->type->value);
    }

    public function test_macos_version_and_no_ipad_doubt_with_hints(): void
    {
        $device = Device::fromUserAgent(UserAgents::CHROME_MAC, [
            'Sec-CH-UA' => self::CHROME_141,
            'Sec-CH-UA-Platform' => '"macOS"',
            'Sec-CH-UA-Platform-Version' => '"26.0.0"',
        ]);

        $this->assertSame('macOS 26', (string) $device->os);
        $this->assertFalse($device->mightBeIpad);
    }

    public function test_grease_brands_are_ignored(): void
    {
        $hints = ClientHints::fromHeaders(['sec-ch-ua' => '" Not A;Brand";v="99", "Not)A;Brand";v="8", "Not/A)Brand";v="24"']);

        $this->assertSame([], $hints->brands);
        $this->assertNull($hints->browser());
        $this->assertFalse($hints->present());
    }

    public function test_only_chromium_falls_back_to_chromium(): void
    {
        $hints = ClientHints::fromHeaders(['sec-ch-ua' => '"Chromium";v="141", "Not?A_Brand";v="8"']);

        $this->assertSame(['name' => 'Chromium', 'version' => '141'], $hints->browser());
    }

    public function test_unknown_platform_and_empty_model_are_ignored(): void
    {
        $hints = ClientHints::fromHeaders([
            'sec-ch-ua-platform' => '"Unknown"',
            'sec-ch-ua-model' => '""',
            'sec-ch-ua-mobile' => 'nonsense',
        ]);

        $this->assertNull($hints->platform);
        $this->assertNull($hints->model);
        $this->assertNull($hints->mobile);
    }

    public function test_hints_can_be_switched_off(): void
    {
        config()->set('device.client_hints.read', false);
        $this->app->forgetInstance(Detector::class);

        $device = Device::fromUserAgent(UserAgents::CHROME_WINDOWS, ['Sec-CH-UA' => '"Brave";v="141", "Chromium";v="141"']);

        $this->assertSame('Chrome', $device->browser->name);
        $this->assertFalse($device->usedClientHints);
    }

    public function test_server_style_header_names_work_too(): void
    {
        $device = Device::fromUserAgent(UserAgents::CHROME_WINDOWS, ['HTTP_SEC_CH_UA' => '"Brave";v="141", "Chromium";v="141"']);

        $this->assertSame('Brave', $device->browser->name);
    }
}
