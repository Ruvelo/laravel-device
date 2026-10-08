<?php

declare(strict_types=1);

namespace Ruvelo\Device\Tests\Unit;

use Ruvelo\Device\BotCategory;
use Ruvelo\Device\Browser;
use Ruvelo\Device\Device;
use Ruvelo\Device\DeviceType;
use Ruvelo\Device\Exceptions\DeviceException;
use Ruvelo\Device\Exceptions\InvalidDeviceType;
use Ruvelo\Device\Testing\UserAgents;
use Ruvelo\Device\Tests\TestCase;

final class DeviceTest extends TestCase
{
    public function test_type_helpers(): void
    {
        $phone = Device::fromUserAgent(UserAgents::IPHONE);
        $tablet = Device::fromUserAgent(UserAgents::IPAD);
        $desktop = Device::fromUserAgent(UserAgents::FIREFOX_WINDOWS);
        $tv = Device::fromUserAgent(UserAgents::SMART_TV);
        $console = Device::fromUserAgent(UserAgents::PLAYSTATION);

        $this->assertTrue($phone->isPhone() && $phone->isMobile() && ! $phone->isTablet() && ! $phone->isDesktop());
        $this->assertTrue($tablet->isTablet() && $tablet->isMobile() && ! $tablet->isPhone());
        $this->assertTrue($desktop->isDesktop() && ! $desktop->isMobile());
        $this->assertTrue($tv->isTv() && ! $tv->isDesktop() && ! $tv->isMobile());
        $this->assertTrue($console->isConsole());
        $this->assertFalse($console->isWearable());
    }

    public function test_is_accepts_types_values_and_mobile(): void
    {
        $device = Device::fromUserAgent(UserAgents::IPAD);

        $this->assertTrue($device->is('mobile'));
        $this->assertTrue($device->is(DeviceType::Tablet));
        $this->assertTrue($device->is('phone', 'tablet'));
        $this->assertFalse($device->is('desktop', DeviceType::Tv));
        $this->assertTrue(Device::fromUserAgent(UserAgents::GOOGLEBOT)->is('robot'));
    }

    public function test_unknown_type_names_throw(): void
    {
        $this->expectException(InvalidDeviceType::class);
        $this->expectExceptionMessage('"fridge" is not a device type');

        Device::fromUserAgent(UserAgents::IPHONE)->is('fridge');
    }

    public function test_package_exceptions_share_an_interface(): void
    {
        try {
            DeviceType::resolve('toaster');
            $this->fail('No exception thrown');
        } catch (DeviceException $e) {
            $this->assertInstanceOf(InvalidDeviceType::class, $e);
        }
    }

    public function test_bots_and_ai_bots(): void
    {
        $gpt = Device::fromUserAgent(UserAgents::GPTBOT);
        $google = Device::fromUserAgent(UserAgents::GOOGLEBOT);
        $person = Device::fromUserAgent(UserAgents::SAFARI_MAC);

        $this->assertTrue($gpt->isBot);
        $this->assertTrue($gpt->isAiBot());
        $this->assertSame(BotCategory::AiCrawler, $gpt->botCategory);
        $this->assertFalse($google->isAiBot());
        $this->assertFalse($person->isBot);
        $this->assertNull($person->botName);
        $this->assertFalse($person->isAiBot());
        $this->assertTrue(BotCategory::AiAssistant->isAi());
        $this->assertSame('AI crawler', BotCategory::AiCrawler->label());
    }

    public function test_languages_come_from_accept_language(): void
    {
        $device = Device::fromUserAgent(UserAgents::SAFARI_MAC, ['Accept-Language' => 'fr-BE, fr;q=0.9, en;q=0.7, *;q=0.5, de;q=0']);

        $this->assertSame(['fr-be', 'fr', 'en', '*'], $device->languages);
        $this->assertSame('fr-be', $device->language());
        $this->assertNull(Device::fromUserAgent(UserAgents::SAFARI_MAC)->language());
    }

    public function test_name_and_version_helpers(): void
    {
        $device = Device::fromUserAgent(UserAgents::CHROME_WINDOWS);

        $this->assertSame('Chrome 141', (string) $device->browser);
        $this->assertSame(141, $device->browser->major());
        $this->assertTrue($device->browser->atLeast(120));
        $this->assertFalse($device->browser->atLeast(200));
        $this->assertTrue($device->browser->is('chrome'));
        $this->assertTrue($device->browser->known());
        $this->assertSame('', (string) new Browser);
        $this->assertNull((new Browser('Chrome'))->major());
        $this->assertFalse((new Browser)->atLeast(1));
        $this->assertSame('Safari 17.4', (string) new Browser('Safari', '17.4'));
    }

    public function test_to_array_and_json(): void
    {
        $device = Device::fromUserAgent(UserAgents::SAMSUNG_GALAXY, ['Accept-Language' => 'en-GB']);

        $this->assertSame([
            'type' => 'phone',
            'browser' => ['name' => 'Samsung Internet', 'version' => '28.0'],
            'os' => ['name' => 'Android', 'version' => '15'],
            'engine' => ['name' => 'Blink', 'version' => '130.0.0.0'],
            'brand' => 'Samsung',
            'model' => 'SM-S938B',
            'is_bot' => false,
            'bot' => null,
            'languages' => ['en-gb'],
            'used_client_hints' => false,
            'might_be_ipad' => false,
            'user_agent' => UserAgents::SAMSUNG_GALAXY,
        ], $device->toArray());

        $json = json_decode((string) json_encode(Device::fromUserAgent(UserAgents::CLAUDEBOT)), true);
        $this->assertSame(['name' => 'ClaudeBot', 'category' => 'ai-crawler'], $json['bot']);
    }

    public function test_from_array_round_trips(): void
    {
        foreach ([UserAgents::IPHONE, UserAgents::GPTBOT, UserAgents::SAFARI_MAC, ''] as $ua) {
            $device = Device::fromUserAgent($ua, ['Accept-Language' => 'nl']);

            $this->assertEquals($device, Device::fromArray($device->toArray()));
        }

        $this->assertNull(Device::fromArray(['type' => 'fridge']));
        $this->assertNull(Device::fromArray([]));
    }

    public function test_device_type_labels_and_groups(): void
    {
        $this->assertSame('Games console', DeviceType::Console->label());
        $this->assertSame([DeviceType::Phone, DeviceType::Tablet], DeviceType::resolve('Mobile'));
        $this->assertTrue(DeviceType::Tablet->isMobile());
        $this->assertFalse(DeviceType::Wearable->isMobile());
    }

    public function test_safari_on_a_mac_might_be_an_ipad(): void
    {
        $this->assertTrue(Device::fromUserAgent(UserAgents::SAFARI_MAC)->mightBeIpad);
        $this->assertFalse(Device::fromUserAgent(UserAgents::CHROME_MAC)->mightBeIpad);
        $this->assertFalse(Device::fromUserAgent(UserAgents::IPAD)->mightBeIpad);
    }

    public function test_cloudfront_device_headers(): void
    {
        $tablet = Device::fromUserAgent('Amazon CloudFront', ['CloudFront-Is-Tablet-Viewer' => 'true', 'CloudFront-Is-Mobile-Viewer' => 'true']);
        $tv = Device::fromUserAgent('Amazon CloudFront', ['CloudFront-Is-SmartTV-Viewer' => 'true']);
        $desktop = Device::fromUserAgent('Amazon CloudFront', ['CloudFront-Is-Desktop-Viewer' => 'true', 'CloudFront-Is-Mobile-Viewer' => 'false']);

        $this->assertSame('tablet', $tablet->type->value);
        $this->assertSame('tv', $tv->type->value);
        $this->assertSame('desktop', $desktop->type->value);
        $this->assertSame('unknown', Device::fromUserAgent('Amazon CloudFront')->type->value);
    }

    public function test_long_user_agents_are_cut(): void
    {
        $device = Device::fromUserAgent(UserAgents::IPHONE.str_repeat(' padding', 500));

        $this->assertSame(1000, mb_strlen($device->userAgent));
        $this->assertTrue($device->isPhone());
    }

    public function test_long_tail_phones_fall_back_to_mobile_detect(): void
    {
        $device = Device::fromUserAgent('Nokia6300/2.0 (05.00) Profile/MIDP-2.0 Configuration/CLDC-1.1');

        $this->assertSame('phone', $device->type->value);
    }
}
