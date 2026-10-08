@php
    use Ruvelo\Device\DeviceType;

    $icons = [
        'desktop' => '<rect x="3" y="4" width="18" height="12" rx="2"/><path d="M8 20h8M12 16v4"/>',
        'phone' => '<rect x="7" y="2.5" width="10" height="19" rx="2.5"/><path d="M11 18.5h2"/>',
        'tablet' => '<rect x="4.5" y="2.5" width="15" height="19" rx="2.5"/><path d="M11 18.5h2"/>',
        'tv' => '<rect x="2.5" y="5" width="19" height="12" rx="2"/><path d="M8 21h8M9 2.5l3 2.5 3-2.5"/>',
        'console' => '<path d="M7 8h10a4 4 0 0 1 4 4v2.5a2.5 2.5 0 0 1-4.5 1.5L15 14H9l-1.5 2a2.5 2.5 0 0 1-4.5-1.5V12a4 4 0 0 1 4-4Z"/><path d="M8 11v2M7 12h2M15.5 11.5h.01M17.5 12.5h.01"/>',
        'wearable' => '<rect x="6.5" y="6.5" width="11" height="11" rx="3"/><path d="M9 6.5 9.5 2.5h5l.5 4M9 17.5l.5 4h5l.5-4M12 10v2.2l1.4 1"/>',
        'bot' => '<rect x="4" y="8" width="16" height="11" rx="3"/><path d="M12 4.5V8M9 13h.01M15 13h.01M2 13v2M22 13v2"/><circle cx="12" cy="3.5" r="1"/>',
        'unknown' => '<circle cx="12" cy="12" r="9"/><path d="M9.6 9.5a2.5 2.5 0 0 1 4.8 1c0 1.7-2.4 2-2.4 3.5M12 17h.01"/>',
    ];
    $icon = fn (string $type) => '<svg class="dv-icon" viewBox="0 0 24 24" aria-hidden="true">'.$icons[$type].'</svg>';
    $groups = [
        'all' => 'Everyone',
        'phone' => 'Phones',
        'tablet' => 'Tablets',
        'desktop' => 'Computers',
        'screen' => 'TVs and consoles',
        'bot' => 'Bots',
        'ai' => 'AI bots',
    ];
    $groupOf = fn ($device) => trim(implode(' ', [
        $device->type->value,
        in_array($device->type, [DeviceType::Tv, DeviceType::Console, DeviceType::Wearable], true) ? 'screen' : '',
        $device->isAiBot() ? 'ai' : '',
    ]));
    $counts = ['all' => count($rows)];
    foreach ($rows as $row) {
        foreach (explode(' ', $groupOf($row['device'])) as $g) {
            $counts[$g] = ($counts[$g] ?? 0) + 1;
        }
    }
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel Device · Device, browser, OS and bot detection for Laravel</title>
    <meta name="description" content="Device, browser, OS and bot detection for Laravel, with User-Agent Client Hints. The maintained successor to jenssegers/agent.">
    <style>
        :root {
            --device-bg: #ffffff;
            --device-subtle: #f8f8fc;
            --device-muted: #f0f0f7;
            --device-line: #e4e4ef;
            --device-ink: #16162a;
            --device-text-2: #4b4b63;
            --device-text-3: #74748b;
            --device-accent: #3d4eff;
            --device-accent-2: #a78bfa;
            --device-accent-ink: #2b38d6;
            --device-accent-soft: #eef0ff;
            --device-on-accent: #ffffff;
            --device-night: #11111c;
            --device-success: #167a4a;
            --device-success-soft: #e8f8ef;
            --device-warning: #b26a00;
            --device-warning-soft: #fff4e0;
            --device-danger: #e5384f;
            --device-danger-soft: #fdecef;
            --device-radius: 8px;
            --device-radius-lg: 14px;
            --device-sans: "Geist", ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            --device-mono: "Geist Mono", ui-monospace, "SF Mono", "Cascadia Code", Menlo, Consolas, monospace;
            color-scheme: light;
        }
        @media (prefers-color-scheme: dark) {
            :root {
                --device-bg: #11111c;
                --device-subtle: #171725;
                --device-muted: #1f1f30;
                --device-line: #2a2a3f;
                --device-ink: #f1f1f8;
                --device-text-2: #b6b6cc;
                --device-text-3: #8787a3;
                --device-accent: #8f9bff;
                --device-accent-2: #c4b5fd;
                --device-accent-ink: #b3bbff;
                --device-accent-soft: #1e2150;
                --device-on-accent: #0b0b1a;
                --device-night: #0b0b14;
                --device-success: #74d6a2;
                --device-success-soft: #0f2b1f;
                --device-warning: #f5b04c;
                --device-warning-soft: #2e2210;
                --device-danger: #ff6b80;
                --device-danger-soft: #331520;
                color-scheme: dark;
            }
        }
        *, *::before, *::after { box-sizing: border-box; }
        body { margin: 0; background: var(--device-bg); color: var(--device-ink); font: 15px/1.6 var(--device-sans); -webkit-font-smoothing: antialiased; }
        body::before { content: ""; display: block; height: 3px; background: linear-gradient(90deg, var(--device-accent), var(--device-accent-2)); }
        a { color: var(--device-accent); text-decoration: none; }
        a:hover { text-decoration: underline; text-underline-offset: 3px; }
        :focus-visible { outline: 2px solid var(--device-accent); outline-offset: 2px; border-radius: 4px; }
        code, pre { font-family: var(--device-mono); }
        h1, h2, h3 { font-weight: 600; letter-spacing: -.02em; line-height: 1.2; }

        .demo-banner { background: var(--device-accent); color: var(--device-on-accent); font: 500 .875rem/1.4 var(--device-sans); padding: .55rem 16px; text-align: center; }
        .demo-banner a { color: inherit; font-weight: 700; text-decoration: underline; text-underline-offset: 2px; }

        .dv-wrap { max-width: 72rem; margin: 0 auto; padding: 0 24px; }
        .dv-bar { display: flex; align-items: center; gap: 1rem; padding: .9rem 0; border-bottom: 1px solid var(--device-line); }
        .dv-brand { display: inline-flex; align-items: center; gap: .6rem; font-weight: 600; letter-spacing: -.01em; color: var(--device-ink); }
        .dv-brand:hover { text-decoration: none; }
        .dv-brand .dv-by { color: var(--device-text-3); font-weight: 500; }
        .dv-mark { display: grid; place-items: center; width: 1.75rem; height: 1.75rem; border-radius: 7px; background: linear-gradient(135deg, var(--device-accent), var(--device-accent-2)); color: #fff; font-size: .85rem; font-weight: 700; box-shadow: 0 4px 12px -4px color-mix(in srgb, var(--device-accent) 60%, transparent); }
        .dv-bar nav { margin-left: auto; display: flex; gap: 1.25rem; font-size: .9rem; }
        .dv-bar nav a { color: var(--device-text-2); }

        .dv-hero { display: grid; grid-template-columns: minmax(0, 1.15fr) minmax(0, .85fr); gap: 3rem; align-items: center; padding: 4rem 0 3rem; position: relative; }
        .dv-hero::before { content: ""; position: absolute; inset: 0 -24px auto; height: 22rem; z-index: -1; background-image: linear-gradient(var(--device-line) 1px, transparent 1px), linear-gradient(90deg, var(--device-line) 1px, transparent 1px); background-size: 44px 44px; -webkit-mask: linear-gradient(to bottom, rgb(0 0 0 / .55), transparent); mask: linear-gradient(to bottom, rgb(0 0 0 / .55), transparent); }
        .dv-eyebrow { display: inline-flex; align-items: center; gap: .5rem; font-size: .8rem; font-weight: 500; color: var(--device-accent-ink); background: var(--device-accent-soft); border-radius: 999px; padding: .25rem .75rem; }
        .dv-hero h1 { font-size: clamp(2.3rem, 5vw, 3.6rem); letter-spacing: -.045em; line-height: 1.04; margin: 1.1rem 0 1rem; }
        .dv-lede { font-size: 1.1rem; color: var(--device-text-2); margin: 0 0 1.75rem; max-width: 34rem; }
        .dv-install { display: flex; align-items: center; gap: .75rem; flex-wrap: wrap; }
        .dv-install code { background: var(--device-muted); border-radius: var(--device-radius); padding: .7rem 1rem; font-size: .9rem; }
        .dv-install code::before { content: "$ "; color: var(--device-text-3); }
        .dv-btn { display: inline-flex; align-items: center; font-weight: 500; font-size: .9rem; padding: .7rem 1.05rem; border-radius: var(--device-radius); background: var(--device-accent); color: var(--device-on-accent); box-shadow: 0 8px 20px -10px color-mix(in srgb, var(--device-accent) 80%, transparent); }
        .dv-btn:hover { background: var(--device-accent-ink); text-decoration: none; }

        .dv-card { border: 1px solid var(--device-line); border-radius: var(--device-radius-lg); background: var(--device-bg); box-shadow: 0 24px 60px -32px color-mix(in srgb, var(--device-accent) 60%, transparent); overflow: hidden; }
        .dv-card-head { display: flex; align-items: center; justify-content: space-between; gap: .5rem; padding: .75rem 1rem; background: var(--device-subtle); border-bottom: 1px solid var(--device-line); font-size: .8rem; color: var(--device-text-3); }
        .dv-card-head strong { color: var(--device-ink); font-weight: 600; }
        .dv-card-body { padding: 1rem; }
        .dv-facts { display: grid; grid-template-columns: auto 1fr; gap: .45rem 1rem; margin: 0; font-size: .9rem; }
        .dv-facts dt { color: var(--device-text-3); }
        .dv-facts dd { margin: 0; font-weight: 500; min-width: 0; overflow-wrap: anywhere; }
        .dv-note { font-size: .78rem; color: var(--device-text-3); margin: .9rem 0 0; line-height: 1.5; }
        .dv-ua { font: .72rem/1.5 var(--device-mono); color: var(--device-text-3); overflow-wrap: anywhere; }

        .dv-section { padding: 3rem 0; }
        .dv-section > h2 { font-size: 1.75rem; margin: 0 0 .5rem; }
        .dv-section > p { color: var(--device-text-2); margin: 0 0 1.5rem; max-width: 46rem; }

        .dv-filters { display: flex; flex-wrap: wrap; gap: .5rem; margin: 0 0 1rem; }
        .dv-filters button { font: 500 .85rem/1 var(--device-sans); color: var(--device-text-2); background: var(--device-bg); border: 1px solid var(--device-line); border-radius: 999px; padding: .5rem .85rem; cursor: pointer; }
        .dv-filters button span { color: var(--device-text-3); margin-left: .3rem; }
        .dv-filters button:hover { border-color: var(--device-accent); color: var(--device-accent-ink); }
        .dv-filters button[aria-pressed=true] { background: var(--device-accent-soft); border-color: transparent; color: var(--device-accent-ink); }
        .dv-filters button[aria-pressed=true] span { color: inherit; }

        .dv-table { width: 100%; border-collapse: separate; border-spacing: 0; border: 1px solid var(--device-line); border-radius: var(--device-radius-lg); overflow: hidden; font-size: .9rem; }
        .dv-table th { text-align: left; font-weight: 500; font-size: .78rem; color: var(--device-text-3); background: var(--device-subtle); padding: .65rem .9rem; border-bottom: 1px solid var(--device-line); }
        .dv-table td { padding: .8rem .9rem; border-bottom: 1px solid var(--device-line); vertical-align: top; }
        .dv-table tr:last-child td { border-bottom: 0; }
        .dv-table tbody tr:hover td { background: var(--device-subtle); }
        .dv-visitor { width: 38%; }
        .dv-visitor strong { display: block; font-weight: 500; margin-bottom: .15rem; }
        .dv-type { display: inline-flex; align-items: center; gap: .5rem; white-space: nowrap; font-weight: 500; }
        .dv-type .dv-icon { width: 1.9rem; height: 1.9rem; padding: .35rem; border-radius: var(--device-radius); background: var(--device-accent-soft); stroke: var(--device-accent-ink); }
        .dv-type.is-bot .dv-icon { background: var(--device-muted); stroke: var(--device-text-2); }
        .dv-icon { fill: none; stroke: currentColor; stroke-width: 1.7; stroke-linecap: round; stroke-linejoin: round; flex: none; }
        .dv-name { font-weight: 500; }
        .dv-version { color: var(--device-text-3); }
        .dv-none { color: var(--device-text-3); }
        .dv-sub { display: block; color: var(--device-text-3); font-size: .8rem; }
        .dv-chip { display: inline-block; font-size: .72rem; font-weight: 500; border-radius: 999px; padding: .1rem .55rem; margin-top: .3rem; background: var(--device-accent-soft); color: var(--device-accent-ink); white-space: nowrap; }
        .dv-chip.is-ai { background: var(--device-warning-soft); color: var(--device-warning); }
        .dv-chip.is-hint { background: var(--device-success-soft); color: var(--device-success); }
        .dv-chip.is-maybe { background: var(--device-muted); color: var(--device-text-2); }
        .dv-hints { margin-top: .4rem; }
        .dv-hints summary { cursor: pointer; font-size: .75rem; color: var(--device-success); list-style: none; display: inline-block; }
        .dv-hints summary::-webkit-details-marker { display: none; }
        .dv-hints summary::before { content: "+ "; }
        .dv-hints[open] summary::before { content: "− "; }
        .dv-hints pre { margin: .4rem 0 0; font-size: .7rem; line-height: 1.5; white-space: pre-wrap; overflow-wrap: anywhere; color: var(--device-text-2); background: var(--device-subtle); border-radius: var(--device-radius); padding: .5rem .65rem; }

        .dv-night { background: var(--device-night); color: #f1f1f8; border-radius: 20px; padding: 3rem; margin: 1rem 0 3rem; }
        .dv-night h2 { font-size: 1.75rem; margin: 0 0 .5rem; color: #fff; }
        .dv-night > p { color: #b6b6cc; margin: 0 0 2rem; max-width: 46rem; }
        .dv-night a { color: #b3bbff; }
        .dv-grid { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 1.5rem; }
        .dv-code { background: #171725; border: 1px solid #2a2a3f; border-radius: var(--device-radius-lg); overflow: hidden; }
        .dv-code h3 { margin: 0; font: 500 .78rem/1 var(--device-sans); letter-spacing: 0; color: #8787a3; padding: .75rem 1rem; border-bottom: 1px solid #2a2a3f; }
        .dv-code pre { margin: 0; padding: 1rem; font-size: .82rem; line-height: 1.7; overflow-x: auto; color: #e4e4ef; }
        .dv-code .del { color: #ff9cab; } .dv-code .add { color: #74d6a2; } .dv-code .c { color: #8787a3; } .dv-code .k { color: #c4b5fd; } .dv-code .s { color: #b3bbff; }
        .dv-map { width: 100%; border-collapse: collapse; font-size: .85rem; }
        .dv-map th { text-align: left; font-weight: 500; color: #8787a3; font-size: .75rem; padding: .6rem 1rem; border-bottom: 1px solid #2a2a3f; }
        .dv-map td { padding: .55rem 1rem; border-bottom: 1px solid #22223a; vertical-align: top; }
        .dv-map tr:last-child td { border-bottom: 0; }
        .dv-map code { font-size: .78rem; color: #e4e4ef; }
        .dv-map td:last-child { color: #b6b6cc; }

        .dv-renders { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .75rem; padding: 1rem; }
        .dv-render { border: 1px solid #2a2a3f; border-radius: var(--device-radius); padding: .75rem .85rem; }
        .dv-render small { display: block; color: #8787a3; font-size: .75rem; margin-bottom: .35rem; }
        .dv-render span { color: #fff; font-weight: 500; font-size: .9rem; }
        .dv-render em { color: #8787a3; font-style: normal; font-size: .9rem; }

        .dv-foot { border-top: 1px solid var(--device-line); color: var(--device-text-3); font-size: .85rem; padding: 1.5rem 0 3rem; display: flex; flex-wrap: wrap; justify-content: space-between; gap: .5rem; }
        .dv-foot a { color: inherit; text-decoration: underline; text-underline-offset: 2px; }

        .dv-only { padding-top: 2rem; }
        .dv-only .dv-night { margin: 0; }

        @media (max-width: 60rem) {
            .dv-hero { grid-template-columns: minmax(0, 1fr); gap: 2rem; padding-top: 2.5rem; }
            .dv-grid { grid-template-columns: minmax(0, 1fr); }
        }
        @media (max-width: 47.99rem) {
            .dv-wrap { padding: 0 16px; }
            .dv-bar nav a:not(:last-child) { display: none; }
            .dv-night { padding: 1.75rem 1.1rem; border-radius: var(--device-radius-lg); margin-inline: -4px; }
            .dv-renders { grid-template-columns: minmax(0, 1fr); }
            .dv-table, .dv-table tbody, .dv-table tr, .dv-table td { display: block; }
            .dv-table thead { display: none; }
            .dv-table { border: 0; border-radius: 0; overflow: visible; }
            .dv-table tr { border: 1px solid var(--device-line); border-radius: var(--device-radius-lg); margin-bottom: .75rem; padding: .35rem 0; }
            .dv-table td { border: 0; padding: .35rem .9rem; display: grid; grid-template-columns: 6.5rem minmax(0, 1fr); gap: .5rem; }
            .dv-table td::before { content: attr(data-label); color: var(--device-text-3); font-size: .8rem; padding-top: .1rem; }
            .dv-table td.dv-visitor { display: block; width: auto; padding-bottom: .5rem; }
            .dv-table td.dv-visitor::before { content: none; }
            .dv-table tbody tr:hover td { background: none; }
        }
    </style>
</head>
<body>
<div class="dv-wrap">
    <header class="dv-bar">
        <a class="dv-brand" href="/laravel-device/"><span class="dv-mark">D</span>Laravel Device <span class="dv-by">by Ruvelo</span></a>
        <nav>
            <a href="{{ $repo }}#moving-from-jenssegersagent">Migration guide</a>
            <a href="{{ $repo }}#for-developers">Developer guide</a>
            <a href="{{ $repo }}">GitHub</a>
        </nav>
    </header>

    @if ($only === null)
    <section class="dv-hero">
        <div>
            <span class="dv-eyebrow">The maintained successor to jenssegers/agent</span>
            <h1>Know who’s on the other end.</h1>
            <p class="dv-lede">Phones, tablets, TVs, consoles, browsers, operating systems and bots, including the AI crawlers. Reads User-Agent Client Hints, so Windows 11, Brave and real Android models show up as they are.</p>
            <div class="dv-install">
                <code>composer require ruvelo/laravel-device</code>
                <a class="dv-btn" href="{{ $repo }}#readme">Read the docs</a>
            </div>
        </div>

        <div class="dv-card" id="your-browser">
            <div class="dv-card-head"><strong>Your browser</strong><span>its own report, not the package</span></div>
            <div class="dv-card-body">
                <dl class="dv-facts" data-browser-facts>
                    <dt>This page</dt><dd>{{ $page->type->label() }} · {{ $page->browser }} · {{ $page->os->name }}</dd>
                </dl>
                <p class="dv-note" data-browser-note>This demo is a static page, so no server sees your visit. Above: what the package detected for the Chrome-on-Windows request that built the page. With JavaScript on, this box shows what your browser says about itself in <code>navigator.userAgentData</code>, the same data it sends as Client Hints.</p>
            </div>
        </div>
    </section>
    @endif

    @if ($only === null || $only === 'table')
    <section class="dv-section" id="visitors" @if ($only) style="padding-top: 2rem" @endif>
        <h2>{{ count($rows) }} real visitors</h2>
        <p>Real User-Agent strings, some with the Client Hints Chromium browsers send, and what <code>$request-&gt;device()</code> made of each one. Rendered by the package when this page was built.</p>

        <div class="dv-filters" role="group" aria-label="Show" data-filters @unless ($shot) hidden @endunless>
            @foreach ($groups as $key => $label)
                @if (($counts[$key] ?? 0) > 0)
                    <button type="button" data-group="{{ $key }}" aria-pressed="{{ $key === 'all' ? 'true' : 'false' }}">{{ $label }}<span>{{ $counts[$key] }}</span></button>
                @endif
            @endforeach
        </div>

        <table class="dv-table">
            <thead>
                <tr>
                    <th scope="col">Visitor</th>
                    <th scope="col">Type</th>
                    <th scope="col">Browser</th>
                    <th scope="col">System</th>
                    <th scope="col">Device or bot</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    @php($d = $row['device'])
                    <tr data-groups="{{ $groupOf($d) }}">
                        <td class="dv-visitor">
                            <strong>{{ $row['label'] }}</strong>
                            <div class="dv-ua">{{ $row['userAgent'] }}</div>
                            @if ($row['hints'])
                                <details class="dv-hints">
                                    <summary>Client Hints</summary>
                                    <pre>@foreach ($row['hints'] as $name => $value){{ $name }}: {{ $value }}
@endforeach</pre>
                                </details>
                            @endif
                        </td>
                        <td data-label="Type"><div><span class="dv-type {{ $d->isBot ? 'is-bot' : '' }}">{!! $icon($d->type->value) !!}{{ $d->type->label() }}</span></div></td>
                        <td data-label="Browser"><div>
                            @if ($d->browser->known())
                                <span class="dv-name">{{ $d->browser->name }}</span>
                                @if ($d->browser->version)<span class="dv-version">{{ preg_replace('/(?<=\d)(\.0)+$/', '', $d->browser->version) }}</span>@endif
                                @if ($d->engine->known())<span class="dv-sub">{{ $d->engine->name }}</span>@endif
                            @else
                                <span class="dv-none">–</span>
                            @endif
                        </div></td>
                        <td data-label="System"><div>
                            @if ($d->os->known())
                                <span class="dv-name">{{ $d->os->name }}</span>
                                @if ($d->os->version)<span class="dv-version">{{ $d->os->version }}</span>@endif
                            @else
                                <span class="dv-none">–</span>
                            @endif
                            @if ($d->usedClientHints)<br><span class="dv-chip is-hint">from Client Hints</span>@endif
                        </div></td>
                        <td data-label="{{ $d->isBot ? 'Bot' : 'Device' }}"><div>
                            @if ($d->isBot)
                                <span class="dv-name">{{ $d->botName }}</span><br>
                                <span class="dv-chip {{ $d->isAiBot() ? 'is-ai' : '' }}">{{ $d->botCategory?->label() }}</span>
                            @elseif ($d->brand || $d->model)
                                <span class="dv-name">{{ trim($d->brand.' '.($d->model === $d->brand ? '' : $d->model)) }}</span>
                                @if ($d->mightBeIpad)<br><span class="dv-chip is-maybe">or an iPad</span>@endif
                            @else
                                <span class="dv-none">–</span>
                            @endif
                        </div></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
    @endif

    @if ($only === null || $only === 'migration')
    <section class="dv-night @if ($only) dv-only @endif" id="migration">
        <h2>Moving from jenssegers/agent</h2>
        <p>Same method names, same answers. Change the import and keep your code; the facade alias <code>Agent</code> keeps working in Blade too. Then, when you like, move to the typed API.</p>

        <div class="dv-grid">
            <div class="dv-code">
                <h3>Before and after</h3>
<pre><span class="c"># composer.json</span>
<span class="del">- "jenssegers/agent": "^2.6"</span>
<span class="add">+ "ruvelo/laravel-device": "^1.0"</span>

<span class="del">- use Jenssegers\Agent\Agent;</span>
<span class="add">+ use Ruvelo\Device\Agent;</span>

<span class="k">$agent</span> = <span class="k">new</span> Agent();
<span class="k">$agent</span>-&gt;isMobile();          <span class="c">// unchanged</span>
<span class="k">$agent</span>-&gt;browser();           <span class="c">// "Edge", not "Chrome"</span>
<span class="k">$agent</span>-&gt;version(<span class="s">'Edge'</span>);     <span class="c">// "141.0.0.0"</span>

<span class="c">// The typed way</span>
<span class="k">$device</span> = <span class="k">$request</span>-&gt;device();
<span class="k">$device</span>-&gt;type;               <span class="c">// DeviceType::Phone</span>
<span class="k">$device</span>-&gt;os-&gt;atLeast(<span class="s">17</span>);    <span class="c">// true</span>
<span class="k">$device</span>-&gt;isAiBot();          <span class="c">// false</span></pre>
            </div>

            <div class="dv-code">
                <h3>Method by method</h3>
                <table class="dv-map">
                    <thead><tr><th>jenssegers/agent</th><th>Typed API</th></tr></thead>
                    <tbody>
                        <tr><td><code>isMobile()</code></td><td><code>$device-&gt;isMobile()</code></td></tr>
                        <tr><td><code>isTablet()</code>, <code>isPhone()</code></td><td><code>isTablet()</code>, <code>isPhone()</code></td></tr>
                        <tr><td><code>isDesktop()</code></td><td><code>isDesktop()</code></td></tr>
                        <tr><td><code>isRobot()</code>, <code>robot()</code></td><td><code>isBot</code>, <code>botName</code>, <code>botCategory</code></td></tr>
                        <tr><td><code>browser()</code>, <code>version($b)</code></td><td><code>browser-&gt;name</code>, <code>browser-&gt;version</code></td></tr>
                        <tr><td><code>platform()</code></td><td><code>os-&gt;name</code>, <code>os-&gt;version</code></td></tr>
                        <tr><td><code>device()</code></td><td><code>brand</code>, <code>model</code></td></tr>
                        <tr><td><code>languages()</code></td><td><code>languages</code></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="dv-code" style="margin-top: 1.5rem">
            <h3>In Blade: one template, rendered by the package for three visitors</h3>
<pre>{{ $bladeExample }}</pre>
            <div class="dv-renders">
                @foreach ($bladeOutputs as $who => $output)
                    <div class="dv-render"><small>{{ $who }} sees</small>@if ($output === null)<em>nothing</em>@else<span>{{ $output }}</span>@endif</div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @if ($only === null)
    <footer class="dv-foot">
        <span>Built by <a href="https://github.com/francoisbultez">François Bultez</a> at <a href="https://github.com/Ruvelo">Ruvelo</a>. MIT licence.</span>
        <span>With thanks to <a href="https://github.com/jenssegers/agent">jenssegers/agent</a> and <a href="https://github.com/serbanghita/Mobile-Detect">Mobile Detect</a>.</span>
    </footer>
    @endif
</div>

@if ($only === null && ! $shot)
<script>
(function () {
    // Filters: every row is visible without JavaScript.
    var filters = document.querySelector('[data-filters]');
    if (filters) {
        filters.hidden = false;
        filters.addEventListener('click', function (event) {
            var button = event.target.closest('button');
            if (!button) return;
            var group = button.getAttribute('data-group');
            filters.querySelectorAll('button').forEach(function (b) { b.setAttribute('aria-pressed', b === button ? 'true' : 'false'); });
            document.querySelectorAll('[data-groups]').forEach(function (row) {
                row.hidden = group !== 'all' && row.getAttribute('data-groups').split(' ').indexOf(group) < 0;
            });
        });
    }

    // Your browser: what it says about itself. Not the package; there's no server here.
    var facts = document.querySelector('[data-browser-facts]');
    var note = document.querySelector('[data-browser-note]');
    if (!facts || !note) return;
    function row(term, value) {
        var dt = document.createElement('dt'), dd = document.createElement('dd');
        dt.textContent = term; dd.textContent = value;
        facts.append(dt, dd);
    }
    facts.textContent = '';
    var data = navigator.userAgentData;
    if (data) {
        var brands = data.brands.filter(function (b) { return !/^not.*a.*brand$/i.test(b.brand.trim()); })
            .map(function (b) { return b.brand + ' ' + b.version; });
        row('Brands', brands.join(', ') || 'none');
        row('Platform', data.platform || 'not given');
        row('Mobile', data.mobile ? 'yes' : 'no');
        note.textContent = 'From navigator.userAgentData, which your browser fills in itself: the same values it sends as Sec-CH-UA headers. The package reads those headers on your server.';
        if (data.getHighEntropyValues) {
            data.getHighEntropyValues(['platformVersion', 'model', 'formFactors']).then(function (high) {
                if (high.platformVersion) row('Platform version', high.platformVersion);
                if (high.model) row('Model', high.model);
                if (high.formFactors && high.formFactors.length) row('Form factor', high.formFactors.join(', '));
            }).catch(function () {});
        }
    } else {
        row('User-Agent', navigator.userAgent);
        if (navigator.maxTouchPoints > 1 && /Macintosh/.test(navigator.userAgent)) row('Touch screen', 'yes: an iPad asking for desktop sites');
        note.textContent = 'Your browser doesn’t offer navigator.userAgentData (Safari and Firefox don’t), so here is its User-Agent string, which is all a server gets from it.';
    }
})();
</script>
@endif
</body>
</html>
