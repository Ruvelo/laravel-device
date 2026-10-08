<?php

declare(strict_types=1);

namespace Ruvelo\Device\Tests\Feature;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Route;
use Ruvelo\Device\Agent;
use Ruvelo\Device\Device;
use Ruvelo\Device\Exceptions\DeviceException;
use Ruvelo\Device\Exceptions\UnknownMethod;
use Ruvelo\Device\Facades\Agent as AgentFacade;
use Ruvelo\Device\Testing\UserAgents;
use Ruvelo\Device\Tests\TestCase;

/**
 * What the Agent does beyond jenssegers/agent's own tests: the known issues it
 * fixes, and how it sits in a Laravel app.
 */
final class AgentTest extends TestCase
{
    protected function defineRoutes($router): void
    {
        Route::get('/facade', fn () => [
            'mobile' => AgentFacade::isMobile(),
            'browser' => AgentFacade::browser(),
            'platform' => AgentFacade::platform(),
            'alias' => \Agent::isPhone(),
            'container' => app('agent')->platform(),
        ]);
    }

    private function agent(string $userAgent): Agent
    {
        return new Agent([], $userAgent);
    }

    public function test_the_facade_reads_the_current_request(): void
    {
        $this->withHeader('User-Agent', UserAgents::IPHONE)
            ->getJson('/facade')
            ->assertExactJson(['mobile' => true, 'browser' => 'Safari', 'platform' => 'iOS', 'alias' => true, 'container' => 'iOS']);
    }

    public function test_one_agent_per_request(): void
    {
        $first = $this->app->make(Agent::class);
        $this->assertSame($first, $this->app->make('agent'));

        $this->app->forgetScopedInstances();
        $this->assertNotSame($first, $this->app->make(Agent::class));
    }

    public function test_new_agent_reads_the_current_request(): void
    {
        $this->app['request']->headers->set('User-Agent', UserAgents::SAMSUNG_GALAXY);
        $this->app['request']->headers->set('Accept-Language', 'pt-BR,pt;q=0.9');

        $agent = new Agent;

        $this->assertSame('Samsung Internet', $agent->browser());
        $this->assertSame(['pt-br', 'pt'], $agent->languages());
        $this->assertSame(UserAgents::SAMSUNG_GALAXY, $agent->getUserAgent());
        $this->assertSame('pt-BR,pt;q=0.9', $agent->getHttpHeader('Accept-Language'));
    }

    public function test_is_chrome_is_false_on_other_chromium_browsers(): void
    {
        $this->assertTrue($this->agent(UserAgents::CHROME_WINDOWS)->isChrome());
        $this->assertFalse($this->agent(UserAgents::EDGE_WINDOWS)->isChrome());
        $this->assertTrue($this->agent(UserAgents::EDGE_WINDOWS)->isEdge());
        $this->assertFalse($this->agent(UserAgents::SAMSUNG_GALAXY)->isChrome());
        $this->assertTrue($this->agent(UserAgents::SAMSUNG_GALAXY)->isSamsungInternet());
        $this->assertFalse($this->agent(UserAgents::CHROME_MAC)->isSafari());
        $this->assertTrue($this->agent(UserAgents::SAFARI_MAC)->isSafari());
    }

    public function test_ipad_in_desktop_mode_is_a_tablet_when_it_can_be_told(): void
    {
        $agent = $this->agent('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/141.0.7390.41 Safari/605.1.15');

        $this->assertTrue($agent->isTablet());
        $this->assertFalse($agent->isDesktop());
        $this->assertSame('iOS', $agent->platform());
        $this->assertTrue($agent->is('iPadOS'));
    }

    public function test_ai_crawlers_are_robots(): void
    {
        foreach (['GPTBot' => UserAgents::GPTBOT, 'ClaudeBot' => UserAgents::CLAUDEBOT, 'ChatGPT-User' => UserAgents::CHATGPT_USER] as $name => $ua) {
            $agent = $this->agent($ua);

            $this->assertTrue($agent->isRobot(), $name);
            $this->assertSame($name, $agent->robot());
            $this->assertFalse($agent->isDesktop(), $name);
            $this->assertSame('robot', $agent->deviceType());
        }
    }

    public function test_modern_platforms_and_browsers(): void
    {
        $this->assertSame('OS X', $this->agent(UserAgents::SAFARI_MAC)->platform());
        $this->assertSame('AndroidOS', $this->agent(UserAgents::CHROME_ANDROID)->platform());
        $this->assertSame('Edge', $this->agent(UserAgents::EDGE_WINDOWS)->browser());
        $this->assertSame('141.0.0.0', $this->agent(UserAgents::EDGE_WINDOWS)->version('Edge'));
        $this->assertSame('28.0', $this->agent(UserAgents::SAMSUNG_GALAXY)->version('Samsung Internet'));
        $this->assertSame(141.0, $this->agent(UserAgents::CHROME_WINDOWS)->version('Chrome', Agent::VERSION_TYPE_FLOAT));
        $this->assertFalse($this->agent(UserAgents::CHROME_WINDOWS)->version(''));
        $this->assertFalse($this->agent(UserAgents::CHROME_WINDOWS)->version('Nonsense'));
    }

    public function test_device_type_strings(): void
    {
        $this->assertSame('desktop', $this->agent(UserAgents::FIREFOX_LINUX)->deviceType());
        $this->assertSame('phone', $this->agent(UserAgents::IPHONE)->deviceType());
        $this->assertSame('tablet', $this->agent(UserAgents::IPAD)->deviceType());
        $this->assertSame('other', $this->agent(UserAgents::SMART_TV)->deviceType());
        $this->assertSame('desktop', $this->agent('')->deviceType());
    }

    public function test_tvs_consoles_and_watches_are_neither_desktop_nor_mobile(): void
    {
        $tv = $this->agent(UserAgents::SMART_TV);

        $this->assertFalse($tv->isDesktop());
        $this->assertFalse($tv->isMobile());
        $this->assertTrue($tv->isTV());
        $this->assertTrue($this->agent(UserAgents::PLAYSTATION)->isConsole());
    }

    public function test_methods_take_another_user_agent(): void
    {
        $agent = $this->agent(UserAgents::FIREFOX_WINDOWS);

        $this->assertTrue($agent->isMobile(UserAgents::IPHONE));
        $this->assertSame('Safari', $agent->browser(UserAgents::IPHONE));
        $this->assertSame('Googlebot', $agent->robot(UserAgents::GOOGLEBOT));
        $this->assertSame('Firefox', $agent->browser());
    }

    public function test_client_hints_from_the_headers(): void
    {
        $agent = new Agent([
            'HTTP_USER_AGENT' => UserAgents::CHROME_WINDOWS,
            'HTTP_SEC_CH_UA' => '"Brave";v="141", "Not?A_Brand";v="8", "Chromium";v="141"',
            'HTTP_SEC_CH_UA_FULL_VERSION_LIST' => '"Brave";v="141.1.83.112", "Not?A_Brand";v="8.0.0.0", "Chromium";v="141.0.7390.66"',
        ]);

        $this->assertSame('Brave', $agent->browser());
        $this->assertSame('141.1.83.112', $agent->version('Brave'));
        $this->assertTrue($agent->isBrave());

        // ...but not for some other User-Agent.
        $agent->setUserAgent(UserAgents::EDGE_WINDOWS);
        $this->assertSame('Edge', $agent->browser());
    }

    public function test_magic_methods_and_rules(): void
    {
        $agent = $this->agent(UserAgents::IPHONE);

        $this->assertTrue($agent->isiPhone());
        $this->assertTrue($agent->isIphone());
        $this->assertTrue($agent->isiOS());
        $this->assertTrue($agent->is('Apple'));
        $this->assertFalse($agent->isAndroidOS());
        $this->assertTrue($agent->isWebKit());
        $this->assertTrue($agent->is('mobile'));
        $this->assertFalse($agent->is('Nonsense'));
        $this->assertSame('iPhone', $agent->device());
        $this->assertFalse($this->agent(UserAgents::CHROME_WINDOWS)->device());
    }

    public function test_match(): void
    {
        $agent = $this->agent(UserAgents::IPHONE);

        $this->assertTrue($agent->match('iPhone OS (\d+)'));
        $this->assertSame('iPhone OS (\d+)', $agent->getMatchingRegex());
        $this->assertSame('18', $agent->getMatchesArray()[1] ?? null);
        $this->assertFalse($agent->match('Android'));
        $this->assertTrue($agent->match('Android', UserAgents::CHROME_ANDROID));
    }

    public function test_unknown_methods_throw(): void
    {
        $this->expectException(UnknownMethod::class);

        $this->agent(UserAgents::IPHONE)->nonsense();
    }

    public function test_unknown_method_is_a_package_exception(): void
    {
        $this->assertInstanceOf(DeviceException::class, UnknownMethod::named('x'));
    }

    public function test_bridge_to_the_typed_device(): void
    {
        $device = $this->agent(UserAgents::IPAD)->toDevice();

        $this->assertInstanceOf(Device::class, $device);
        $this->assertSame('iPadOS', $device->os->name);
    }

    public function test_rule_lists_are_exposed_like_jenssegers(): void
    {
        $this->assertArrayHasKey('Macintosh', Agent::getDesktopDevices());
        $this->assertArrayHasKey('iPhone', Agent::getPhoneDevices());
        $this->assertArrayHasKey('iPad', Agent::getTabletDevices());
        $this->assertArrayHasKey('OS X', Agent::getPlatforms());
        $this->assertArrayHasKey('Edge', Agent::getBrowsers());
        $this->assertArrayHasKey('Windows', Agent::getProperties());
        $this->assertSame(Agent::getPlatforms(), Agent::getOperatingSystems());
    }

    public function test_headers_by_either_name(): void
    {
        $agent = new Agent(['HTTP_USER_AGENT' => UserAgents::IPHONE, 'HTTP_X_FORWARDED_FOR' => '203.0.113.9']);

        $this->assertSame('203.0.113.9', $agent->getHttpHeader('X-Forwarded-For'));
        $this->assertSame('203.0.113.9', $agent->getHttpHeader('HTTP_X_FORWARDED_FOR'));
        $this->assertNull($agent->getHttpHeader('Missing'));
        $this->assertSame(UserAgents::IPHONE, $agent->getUserAgent());
        $this->assertCount(2, $agent->getHttpHeaders());
    }

    public function test_opera_mini_phone_headers_are_appended_like_mobile_detect(): void
    {
        $agent = new Agent([
            'HTTP_USER_AGENT' => 'Opera/9.80 (Android; Opera Mini/7.6.40234/191.248; U; en) Presto/2.12.423 Version/12.16',
            'HTTP_X_OPERAMINI_PHONE_UA' => 'Mozilla/5.0 (Linux; U; Android 4.4.2; en-us; GT-I9195 Build/KOT49H)',
        ]);

        $this->assertStringContainsString('GT-I9195', (string) $agent->getUserAgent());
        $this->assertTrue($agent->isPhone());
    }

    public function test_takes_headers_directly(): void
    {
        $agent = new Agent(['HTTP_USER_AGENT' => UserAgents::FIREFOX_LINUX]);

        $this->assertSame('Linux', $agent->platform());
    }

    public function test_works_outside_a_laravel_app(): void
    {
        $app = Container::getInstance();
        Container::setInstance(new Container);

        try {
            $agent = new Agent(['HTTP_USER_AGENT' => UserAgents::IPHONE]);

            $this->assertTrue($agent->isPhone());
            $this->assertSame('iOS', $agent->platform());
        } finally {
            Container::setInstance($app);
        }
    }
}
