<?php

declare(strict_types=1);

namespace Ruvelo\Device;

/**
 * What a bot is for. The three AI categories let you treat training crawlers,
 * AI search indexers and fetches made on behalf of a person differently.
 */
enum BotCategory: string
{
    /** Search engine crawlers: Googlebot, Bingbot, DuckDuckBot... */
    case Search = 'search';

    /** Crawlers collecting data to train AI models: GPTBot, ClaudeBot, CCBot... */
    case AiCrawler = 'ai-crawler';

    /** Crawlers indexing pages for AI search answers: OAI-SearchBot, PerplexityBot... */
    case AiSearch = 'ai-search';

    /** One-off fetches an AI assistant makes because a person asked: ChatGPT-User, Claude-User... */
    case AiAssistant = 'ai-assistant';

    /** Link previews in social networks and chat apps: Slackbot, Twitterbot, facebookexternalhit... */
    case Social = 'social';

    /** SEO and marketing crawlers: AhrefsBot, SemrushBot... */
    case Seo = 'seo';

    /** Uptime and performance monitors: UptimeRobot, Pingdom, Lighthouse... */
    case Monitoring = 'monitoring';

    /** Feed readers fetching RSS or Atom. */
    case Feed = 'feed';

    /** Web archives: the Internet Archive, Common Crawl's archiver... */
    case Archiver = 'archiver';

    /** HTTP libraries, command-line tools and headless browsers: curl, python-requests, HeadlessChrome... */
    case Tool = 'tool';

    /** Anything that says it's a bot but isn't in the list. */
    case Other = 'other';

    public function isAi(): bool
    {
        return $this === self::AiCrawler || $this === self::AiSearch || $this === self::AiAssistant;
    }

    public function label(): string
    {
        return match ($this) {
            self::Search => 'Search engine',
            self::AiCrawler => 'AI crawler',
            self::AiSearch => 'AI search',
            self::AiAssistant => 'AI assistant',
            self::Social => 'Link preview',
            self::Seo => 'SEO tool',
            self::Monitoring => 'Monitoring',
            self::Feed => 'Feed reader',
            self::Archiver => 'Archiver',
            self::Tool => 'HTTP client',
            self::Other => 'Other bot',
        };
    }
}
