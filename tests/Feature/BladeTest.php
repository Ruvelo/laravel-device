<?php

declare(strict_types=1);

namespace Ruvelo\Device\Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Vite;
use Ruvelo\Device\Testing\UserAgents;
use Ruvelo\Device\Tests\TestCase;
use Ruvelo\Device\View\IpadScript;

final class BladeTest extends TestCase
{
    private function visit(string $userAgent): void
    {
        $this->app->instance('request', Request::create('/', 'GET', server: ['HTTP_USER_AGENT' => $userAgent]));
    }

    private function render(string $template): string
    {
        return trim(Blade::render($template));
    }

    public function test_mobile_includes_phones_and_tablets(): void
    {
        $template = '@mobile mobile @else not mobile @endmobile';

        $this->visit(UserAgents::IPHONE);
        $this->assertSame('mobile', $this->render($template));

        $this->visit(UserAgents::IPAD);
        $this->assertSame('mobile', $this->render($template));

        $this->visit(UserAgents::FIREFOX_WINDOWS);
        $this->assertSame('not mobile', $this->render($template));
    }

    public function test_phone_tablet_and_desktop(): void
    {
        $template = '@phone phone @endphone @tablet tablet @endtablet @desktop desktop @enddesktop';

        $this->visit(UserAgents::SAMSUNG_GALAXY);
        $this->assertSame('phone', $this->render($template));

        $this->visit(UserAgents::ANDROID_TABLET);
        $this->assertSame('tablet', $this->render($template));

        $this->visit(UserAgents::SAFARI_MAC);
        $this->assertSame('desktop', $this->render($template));
    }

    public function test_bot_and_unlessbot(): void
    {
        $template = '@bot bot @endbot @unlessbot person @endbot';

        $this->visit(UserAgents::CLAUDEBOT);
        $this->assertSame('bot', $this->render($template));

        $this->visit(UserAgents::IPHONE);
        $this->assertSame('person', $this->render($template));
    }

    public function test_elseif_variants(): void
    {
        $template = '@phone phone @elsetablet tablet @else other @endphone';

        $this->visit(UserAgents::IPAD);
        $this->assertSame('tablet', $this->render($template));
    }

    public function test_device_takes_any_types(): void
    {
        $template = "@device('tv', 'console') big screen @enddevice";

        $this->visit(UserAgents::PLAYSTATION);
        $this->assertSame('big screen', $this->render($template));

        $this->visit(UserAgents::SMART_TV);
        $this->assertSame('big screen', $this->render($template));

        $this->visit(UserAgents::IPHONE);
        $this->assertSame('', $this->render($template));
    }

    public function test_detect_ipad_prints_a_tiny_script(): void
    {
        $html = $this->render('@detectIpad');

        $this->assertStringStartsWith('<script>', $html);
        $this->assertStringContainsString('navigator.maxTouchPoints>1', $html);
        $this->assertStringContainsString('("device_ipad")', $html);
        $this->assertStringNotContainsString('nonce', $html);
    }

    public function test_detect_ipad_uses_the_csp_nonce(): void
    {
        Vite::useCspNonce('abc123');

        $this->assertStringStartsWith('<script nonce="abc123">', $this->render('@detectIpad'));
    }

    public function test_detect_ipad_prints_nothing_when_turned_off_or_unsafe(): void
    {
        config()->set('device.ipad_cookie', null);
        $this->assertSame('', $this->render('@detectIpad'));

        $this->assertSame('', (string) IpadScript::render('bad"name'));
    }

    public function test_every_directive_is_registered(): void
    {
        foreach (['mobile', 'endmobile', 'phone', 'tablet', 'desktop', 'bot', 'unlessbot', 'device', 'enddevice', 'detectIpad'] as $directive) {
            $this->assertArrayHasKey($directive, Blade::getCustomDirectives(), $directive);
        }
    }
}
