# Contributing to Laravel Device

Thanks for helping! Wrongly detected User-Agents, docs fixes and features are all welcome.

## Before you start

- **A User-Agent detected wrongly:** [open an issue](https://github.com/Ruvelo/laravel-device/issues/new/choose) with the full string, any `Sec-CH-UA-*` headers, and what you expected. Better still, add it to `tests/Fixtures/user-agents.php` and send a pull request with the fix.
- **Features:** open an issue first if it's more than a small change, so we can agree on the shape before you spend time on it.
- **Security issues:** don't open an issue. See [SECURITY.md](https://github.com/Ruvelo/.github/blob/main/SECURITY.md).

## Setup

You need PHP 8.3+ and Composer. Nothing else: there's no database.

```bash
git clone https://github.com/<you>/laravel-device && cd laravel-device
composer install
composer check
```

`composer check` runs exactly what CI runs:

| Command | What it does |
|---|---|
| `composer test` | PHPUnit, through Orchestra Testbench |
| `composer lint` | Code style check (Laravel Pint) |
| `composer format` | Fix code style |
| `composer analyse` | Static analysis (PHPStan level 8 with Larastan) |
| `composer demo` | Build the static demo into `build/` |

## Changing the rules

1. Add the User-Agent to `tests/Fixtures/user-agents.php` with what it should give, and watch the test fail. Use real strings, not made-up ones, and say where they came from in the pull request.
2. Change the table in `src/Detection/`. Rules are tried in order, most specific first: a browser built on Chromium goes above Chrome, a bot that mentions another bot goes above it.
3. Keep patterns cheap: no nested quantifiers, nothing that backtracks over the whole string.
4. Bump `Detector::RULES_VERSION` so cached answers are recomputed.
5. Keep `tests/Unit/JenssegersCompatibilityTest.php` green: it's jenssegers/agent's own suite, and it must never be edited to pass.

## Making a change

1. Branch from `main`.
2. Write a test that fails without your change. Feature tests live in `tests/Feature`, unit tests in `tests/Unit`.
3. Keep the public API stable: `Device` and its value objects, the enums, `Agent` and its facade, the Blade directives, middleware aliases, config keys and the JSON shape. If you must change one, say so in the PR.
4. Run `composer format` and `composer check`.
5. Add a line under **Unreleased** in [CHANGELOG.md](CHANGELOG.md).
6. Open the pull request.

## Where things live

```
src/Device.php               The typed answer, with fromRequest() and fromUserAgent()
src/Detector.php             Caching, headers and languages around the parser
src/Detection/               The parser and its rule tables: bots, browsers, systems, hardware, Client Hints
src/Agent.php                The jenssegers/agent-compatible API
src/Http/Middleware/         ShareDevice and RequestClientHints
src/Testing/UserAgents.php   Real User-Agents for host-app tests
resources/boost/guidelines/  Laravel Boost guidelines for coding agents; keep them in step with the API
demo/                        The demo page (deployed to GitHub Pages), its build script and screenshots
```

## Style

- `declare(strict_types=1)` everywhere, typed properties and return types.
- No `@phpstan-ignore` or baseline entries: fix the cause.
- Comments explain *why*, not *what*.
- The demo follows the [Ruvelo house style](https://github.com/Ruvelo/.github/blob/main/BRAND.md): CSS variables, no build step, works without JavaScript, light and dark.

By contributing you agree that your work is released under the [MIT license](LICENSE) and that you'll follow the [code of conduct](https://github.com/Ruvelo/.github/blob/main/CODE_OF_CONDUCT.md).
