<?php

declare(strict_types=1);

namespace Ruvelo\Device\Detection;

use Ruvelo\Device\BotCategory;

/**
 * Known crawlers, fetchers and HTTP clients.
 *
 * Each rule is [pattern, name, category]. Patterns are matched
 * case-insensitively, in order, so put specific names before families
 * (ChatGPT-User before a generic "bot"). The text a pattern matches is what
 * Agent::robot() returns, as jenssegers/agent did, so keep the match short:
 * "Googlebot", not the whole User-Agent.
 *
 * Sources: each operator's published User-Agent documentation, checked
 * October 2026. Some "bots" are robots.txt tokens only and never appear in a
 * User-Agent (Google-Extended, Applebot-Extended): they are listed so that a
 * request spoofing them is still recognised, but expect no real traffic.
 */
final class Bots
{
    /** @var list<array{0: string, 1: string, 2: BotCategory}>|null */
    private static ?array $rules = null;

    /**
     * @return array{name: string, category: BotCategory, token: string}|null
     */
    public static function match(string $userAgent): ?array
    {
        if ($userAgent === '') {
            return null;
        }

        foreach (self::rules() as [$pattern, $name, $category]) {
            if (preg_match('~'.$pattern.'~i', $userAgent, $match)) {
                return ['name' => $name, 'category' => $category, 'token' => $match[0]];
            }
        }

        // Anything that calls itself a bot, crawler or spider, or links to its own docs.
        // CUBOT is a phone maker, not a bot.
        if (preg_match('~[a-z0-9._-]*(?:bot|crawler|spider|scraper)(?![a-z])~i', $userAgent, $match)
            && stripos($match[0], 'cubot') === false) {
            $token = trim($match[0], '._-');

            return ['name' => $token, 'category' => BotCategory::Other, 'token' => $token];
        }

        if (preg_match('~\(compatible;[^)]*\+?https?://~i', $userAgent, $match)) {
            return ['name' => 'Unknown bot', 'category' => BotCategory::Other, 'token' => 'compatible'];
        }

        return null;
    }

    /**
     * @return list<array{0: string, 1: string, 2: BotCategory}>
     */
    public static function rules(): array
    {
        return self::$rules ??= [
            // AI assistants fetching a page because a person asked.
            ['ChatGPT-User', 'ChatGPT-User', BotCategory::AiAssistant],
            ['Claude-User', 'Claude-User', BotCategory::AiAssistant],
            ['Perplexity-User', 'Perplexity-User', BotCategory::AiAssistant],
            ['MistralAI-User', 'MistralAI-User', BotCategory::AiAssistant],
            ['DuckAssistBot', 'DuckAssistBot', BotCategory::AiAssistant],
            ['meta-externalfetcher', 'Meta-ExternalFetcher', BotCategory::AiAssistant],
            ['Gemini-Deep-Research', 'Gemini-Deep-Research', BotCategory::AiAssistant],
            ['Google-NotebookLM', 'Google-NotebookLM', BotCategory::AiAssistant],

            // AI search indexers.
            ['OAI-SearchBot', 'OAI-SearchBot', BotCategory::AiSearch],
            ['Claude-SearchBot', 'Claude-SearchBot', BotCategory::AiSearch],
            ['PerplexityBot', 'PerplexityBot', BotCategory::AiSearch],
            ['YouBot', 'YouBot', BotCategory::AiSearch],
            ['PhindBot', 'PhindBot', BotCategory::AiSearch],
            ['Andibot', 'Andibot', BotCategory::AiSearch],

            // AI training crawlers.
            ['GPTBot', 'GPTBot', BotCategory::AiCrawler],
            ['ClaudeBot', 'ClaudeBot', BotCategory::AiCrawler],
            ['Claude-Web', 'Claude-Web', BotCategory::AiCrawler],
            ['anthropic-ai', 'anthropic-ai', BotCategory::AiCrawler],
            ['Google-Extended', 'Google-Extended', BotCategory::AiCrawler],
            ['Google-CloudVertexBot', 'Google-CloudVertexBot', BotCategory::AiCrawler],
            ['Applebot-Extended', 'Applebot-Extended', BotCategory::AiCrawler],
            ['Bytespider', 'Bytespider', BotCategory::AiCrawler],
            ['TikTokSpider', 'TikTokSpider', BotCategory::AiCrawler],
            ['CCBot', 'CCBot', BotCategory::AiCrawler],
            ['meta-externalagent', 'Meta-ExternalAgent', BotCategory::AiCrawler],
            ['FacebookBot', 'FacebookBot', BotCategory::AiCrawler],
            ['Amazonbot', 'Amazonbot', BotCategory::AiCrawler],
            ['cohere-training-data-crawler', 'cohere-training-data-crawler', BotCategory::AiCrawler],
            ['cohere-ai', 'cohere-ai', BotCategory::AiCrawler],
            ['Diffbot', 'Diffbot', BotCategory::AiCrawler],
            ['AI2Bot(?:-Dolma)?', 'AI2Bot', BotCategory::AiCrawler],
            ['Ai2Bot-Dolma', 'Ai2Bot-Dolma', BotCategory::AiCrawler],
            ['Timpibot', 'Timpibot', BotCategory::AiCrawler],
            ['ImagesiftBot', 'ImagesiftBot', BotCategory::AiCrawler],
            ['omgili(?:bot)?', 'omgili', BotCategory::AiCrawler],
            ['PanguBot', 'PanguBot', BotCategory::AiCrawler],
            ['PetalBot', 'PetalBot', BotCategory::Search],
            ['Kangaroo Bot', 'Kangaroo Bot', BotCategory::AiCrawler],
            ['img2dataset', 'img2dataset', BotCategory::AiCrawler],
            ['Webzio-Extended', 'Webzio-Extended', BotCategory::AiCrawler],
            ['VelenPublicWebCrawler', 'VelenPublicWebCrawler', BotCategory::AiCrawler],
            ['QualifiedBot', 'QualifiedBot', BotCategory::AiCrawler],

            // Search engines.
            ['Googlebot(?:-Image|-Video|-News)?', 'Googlebot', BotCategory::Search],
            ['Google-InspectionTool', 'Google-InspectionTool', BotCategory::Search],
            ['GoogleOther', 'GoogleOther', BotCategory::Search],
            ['Storebot-Google', 'Storebot-Google', BotCategory::Search],
            ['AdsBot-Google(?:-Mobile)?', 'AdsBot-Google', BotCategory::Search],
            ['Mediapartners-Google', 'Mediapartners-Google', BotCategory::Search],
            ['APIs-Google', 'APIs-Google', BotCategory::Search],
            ['Google-Read-Aloud', 'Google-Read-Aloud', BotCategory::Tool],
            ['Google-PageRenderer', 'Google-PageRenderer', BotCategory::Social],
            ['Google-Site-Verification', 'Google-Site-Verification', BotCategory::Tool],
            ['bingbot', 'Bingbot', BotCategory::Search],
            ['BingPreview', 'BingPreview', BotCategory::Social],
            ['adidxbot', 'AdIdxBot', BotCategory::Search],
            ['msnbot(?:-media)?', 'msnbot', BotCategory::Search],
            ['DuckDuckBot(?:-Https)?', 'DuckDuckBot', BotCategory::Search],
            ['DuckDuckGo-Favicons-Bot', 'DuckDuckGo-Favicons-Bot', BotCategory::Search],
            ['Applebot', 'Applebot', BotCategory::Search],
            ['Yandex(?!Search|Browser)', 'YandexBot', BotCategory::Search],
            ['Baiduspider', 'Baiduspider', BotCategory::Search],
            ['Sogou web spider', 'Sogou', BotCategory::Search],
            ['360Spider', '360Spider', BotCategory::Search],
            ['Yeti/', 'Yeti', BotCategory::Search],
            ['SeznamBot', 'SeznamBot', BotCategory::Search],
            ['Qwantbot|Qwantify', 'Qwantbot', BotCategory::Search],
            ['MojeekBot', 'MojeekBot', BotCategory::Search],
            ['coccocbot', 'coccocbot', BotCategory::Search],
            ['Exabot', 'Exabot', BotCategory::Search],
            ['Daum(?:oa)?', 'Daum', BotCategory::Search],
            ['ia_archiver', 'Alexa', BotCategory::Archiver],
            ['archive\.org_bot', 'Internet Archive', BotCategory::Archiver],
            ['heritrix', 'Heritrix', BotCategory::Archiver],

            // Link previews.
            ['facebookexternalhit', 'Facebook', BotCategory::Social],
            ['facebookcatalog', 'Facebook catalog', BotCategory::Social],
            ['Twitterbot', 'Twitterbot', BotCategory::Social],
            ['LinkedInBot', 'LinkedInBot', BotCategory::Social],
            ['Slackbot(?:-LinkExpanding)?|Slack-ImgProxy', 'Slackbot', BotCategory::Social],
            ['Discordbot', 'Discordbot', BotCategory::Social],
            ['TelegramBot', 'TelegramBot', BotCategory::Social],
            ['WhatsApp/', 'WhatsApp', BotCategory::Social],
            ['Pinterest(?:bot)?/', 'Pinterestbot', BotCategory::Social],
            ['redditbot', 'Redditbot', BotCategory::Social],
            ['Embedly', 'Embedly', BotCategory::Social],
            ['Iframely', 'Iframely', BotCategory::Social],
            ['SkypeUriPreview', 'Skype', BotCategory::Social],
            ['Mastodon/', 'Mastodon', BotCategory::Social],
            ['Bluesky Cardyb', 'Bluesky', BotCategory::Social],
            ['vkShare', 'VK', BotCategory::Social],
            ['Snap URL Preview', 'Snapchat', BotCategory::Social],
            ['MicrosoftPreview', 'Microsoft Teams', BotCategory::Social],

            // SEO tools.
            ['AhrefsBot|AhrefsSiteAudit', 'AhrefsBot', BotCategory::Seo],
            ['SemrushBot|SiteAuditBot', 'SemrushBot', BotCategory::Seo],
            ['MJ12bot', 'MJ12bot', BotCategory::Seo],
            ['DotBot', 'DotBot', BotCategory::Seo],
            ['rogerbot', 'Moz', BotCategory::Seo],
            ['Screaming Frog SEO Spider', 'Screaming Frog', BotCategory::Seo],
            ['BLEXBot', 'BLEXBot', BotCategory::Seo],
            ['serpstatbot', 'Serpstat', BotCategory::Seo],
            ['DataForSeoBot', 'DataForSeoBot', BotCategory::Seo],
            ['Barkrowler', 'Barkrowler', BotCategory::Seo],
            ['SeekportBot', 'SeekportBot', BotCategory::Seo],
            ['MegaIndex', 'MegaIndex', BotCategory::Seo],

            // Monitoring.
            ['UptimeRobot', 'UptimeRobot', BotCategory::Monitoring],
            ['Pingdom', 'Pingdom', BotCategory::Monitoring],
            ['StatusCake', 'StatusCake', BotCategory::Monitoring],
            ['Site24x7', 'Site24x7', BotCategory::Monitoring],
            ['Better ?Uptime|BetterStack', 'Better Stack', BotCategory::Monitoring],
            ['Datadog(?:/Synthetics|Synthetics)', 'Datadog', BotCategory::Monitoring],
            ['NewRelicPinger', 'New Relic', BotCategory::Monitoring],
            ['Uptime-Kuma', 'Uptime Kuma', BotCategory::Monitoring],
            ['Checkly', 'Checkly', BotCategory::Monitoring],
            ['OhDear', 'Oh Dear', BotCategory::Monitoring],
            ['Chrome-Lighthouse', 'Lighthouse', BotCategory::Monitoring],
            ['GTmetrix', 'GTmetrix', BotCategory::Monitoring],

            // Feed readers.
            ['Feedly', 'Feedly', BotCategory::Feed],
            ['Feedbin', 'Feedbin', BotCategory::Feed],
            ['NewsBlur', 'NewsBlur', BotCategory::Feed],
            ['Inoreader', 'Inoreader', BotCategory::Feed],
            ['Tiny Tiny RSS', 'Tiny Tiny RSS', BotCategory::Feed],
            ['FreshRSS', 'FreshRSS', BotCategory::Feed],
            ['Miniflux', 'Miniflux', BotCategory::Feed],
            // Last: other readers say they're "like FeedFetcher-Google".
            ['FeedFetcher-Google', 'FeedFetcher-Google', BotCategory::Feed],

            // Headless browsers, HTTP libraries and command-line tools.
            ['HeadlessChrome', 'HeadlessChrome', BotCategory::Tool],
            ['PhantomJS', 'PhantomJS', BotCategory::Tool],
            ['^curl/', 'curl', BotCategory::Tool],
            ['^Wget/', 'Wget', BotCategory::Tool],
            ['python-requests', 'python-requests', BotCategory::Tool],
            ['python-urllib|Python-urllib', 'Python urllib', BotCategory::Tool],
            ['python-httpx', 'HTTPX', BotCategory::Tool],
            ['aiohttp', 'aiohttp', BotCategory::Tool],
            ['Scrapy', 'Scrapy', BotCategory::Tool],
            ['Go-http-client', 'Go-http-client', BotCategory::Tool],
            ['^axios/', 'axios', BotCategory::Tool],
            ['node-fetch', 'node-fetch', BotCategory::Tool],
            ['^undici', 'undici', BotCategory::Tool],
            ['^okhttp/', 'OkHttp', BotCategory::Tool],
            ['Apache-HttpClient', 'Apache HttpClient', BotCategory::Tool],
            ['^Java/', 'Java', BotCategory::Tool],
            ['libwww-perl', 'libwww-perl', BotCategory::Tool],
            ['GuzzleHttp', 'Guzzle', BotCategory::Tool],
            ['Symfony HttpClient', 'Symfony HttpClient', BotCategory::Tool],
            ['PostmanRuntime', 'Postman', BotCategory::Tool],
            ['insomnia/', 'Insomnia', BotCategory::Tool],
            ['^HTTPie/', 'HTTPie', BotCategory::Tool],
        ];
    }
}
