# Changelog

All notable changes to this project are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and the project uses
[Semantic Versioning](https://semver.org/).

## [Unreleased]

## [1.0.0] - 2026-10-08

First release.

### Added

- `$request->device()` and `Device::fromRequest()` / `Device::fromUserAgent()`, returning an immutable `Device`: `type` (`DeviceType` enum: desktop, phone, tablet, tv, console, wearable, bot, unknown), `browser`, `os` and `engine` with versions, `brand` and `model`, `isBot`, `botName`, `botCategory`, `languages`, `usedClientHints` and `mightBeIpad`. JSON-serialisable, with `toArray()` and `fromArray()`.
- Own, ordered rule tables for browsers (including Samsung Internet, Brave, Yandex, DuckDuckGo, in-app browsers and Android web views), operating systems (Windows 7 to 11, macOS, iOS, iPadOS, Android, Fire OS, HarmonyOS, Chrome OS, Linux distributions, KaiOS...), engines, TVs, consoles, wearables and Android makers.
- Bot detection with categories: search, AI crawler, AI search, AI assistant, link preview, SEO, monitoring, feed, archiver and HTTP tools. GPTBot, ChatGPT-User, OAI-SearchBot, ClaudeBot, Claude-User, Claude-SearchBot, PerplexityBot, Google-Extended, Bytespider, CCBot, Meta-ExternalAgent, Amazonbot and others.
- User-Agent Client Hints: `Sec-CH-UA`, `-Mobile`, `-Platform`, `-Platform-Version`, `-Model`, `-Full-Version-List` and `-Form-Factors` override the frozen User-Agent where they're more reliable (Windows 11, Brave, real Android models and versions, full browser versions).
- `RequestClientHints` middleware (`device.hints`) sending `Accept-CH` and, optionally, `Critical-CH`.
- iPadOS desktop-mode handling: iOS browsers' tokens in a Mac User-Agent, the `mightBeIpad` flag, and the `@detectIpad` directive with its cookie (excluded from cookie encryption, CSP nonce aware). Safari's frozen iOS 18.6 version is corrected from its `Version/` token.
- Blade directives `@mobile`, `@phone`, `@tablet`, `@desktop`, `@bot` and `@device(...)`, with `@else` and `@unless` forms.
- `ShareDevice` middleware (`device.share`) sharing `$device` with every view.
- `Ruvelo\Device\Agent`, the `Agent` facade and alias: a drop-in replacement for jenssegers/agent's API, passing its test suite unchanged, with exact browser checks (`isChrome()` is false on Edge) and `toDevice()` to reach the typed API. Scoped per request, so it's safe under Octane.
- Bounded in-memory cache of parsed answers, and an optional Laravel cache store.
- Amazon CloudFront `CloudFront-Is-*-Viewer` headers.
- `Ruvelo\Device\Testing\UserAgents`: real User-Agents for host-app tests.
- `InvalidDeviceType` and `UnknownMethod` exceptions, both implementing `DeviceException`.
- Laravel Boost guidelines (`resources/boost/guidelines/core.blade.php`).

[Unreleased]: https://github.com/Ruvelo/laravel-device/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/Ruvelo/laravel-device/releases/tag/v1.0.0
