# audiocafe.tokyo

A PHP multi-content site covering IT/construction job listings (`aruaru`), jobs/nightlife
information aimed at women (`aruaru-lady`), Rakuten Mobile information (`rakuten-mobile`),
and a company profile (`top`).

The sister site [aruaru.tokyo](https://github.com/aon-co-jp/aruaru-tokyo-server) (Rust +
Poem) is intentionally kept on a separate domain and stack — `audiocafe.tokyo` is PHP,
`aruaru.tokyo` is Rust+Poem, by deliberate per-domain design (no plan to unify).

**This PHP repository itself is in the middle of a gradual migration to
[audiocafe-tokyo-rust](https://github.com/aon-co-jp/audiocafe-tokyo-rust)**: in production,
the top page and the `/aruaru/`, `/aruaru-lady/`, and `/rakuten-mobile/` paths have already
been cut over to the Rust version (per-`location` nginx proxying to `127.0.0.1:4400`). All
other paths (`/top/`, `/cancer/`, `/Python/`, `/video/`, etc.) and the cron-driven cache
refresh are still handled by this PHP repository.

## Structure

- `index.php` — top page (multi-language card picker UI, Google Translate integration)
- `aruaru/` — IT/construction job listings. Japanese + 11 language translations
  (EN/KO/ZH-CN/ZH-TW/RU/UK/DE/IT/FR/AR/FA), later extended to 18 languages
  (see CLAUDE.md HANDOFF)
- `aruaru-lady/` — jobs/nightlife information for women, same 18-language coverage
- `rakuten-mobile/` — automated crawl + cached display of Rakuten Mobile information, using
  the same `lang-nav.php` pattern as `aruaru`/`aruaru-lady`, 18 languages
  (Japanese + English (US/UK)/Italian/German/Austrian/Swiss/French/Russian/Ukrainian/
  Arabic/Persian/Korean/Chinese (Simplified/Traditional)/Spanish/Filipino/Hebrew)
- `top/` — company profile
- `cancer/`, `world/`, `video/` — other content pages

## Multi-language support

Each `index-<lang>.php` under `aruaru/`/`aruaru-lady/` includes the shared `lang-nav.php` to
show a language-switch navigation. Arabic (`ar`) and Persian (`fa`) use `dir="rtl"` for
right-to-left layout.

## YouTube playback bug fix (2026-07-16)

The previous design — scraping YouTube search-result pages via `r.jina.ai` and guessing a
"plausible" video to auto-play — was fundamentally unstable: jina.ai's blocking behavior
toward YouTube kept shifting (403 → temporarily worked around with a header → 401), affecting
every series button that relied on a search query (not just DD67000). Scraping was removed
entirely; search URLs now navigate directly to the real YouTube search-results page (applies
to both the series-button/intro path and the NEXT/random-pool path). This eliminates any
possibility of an unrelated video playing.

## Deployment

Files are copied directly via `scp` to `/var/www/audiocafe.tokyo` on the VPS and served by
nginx + PHP-FPM (no build step for this repository itself). Both HTTPS on 443 (a real Let's
Encrypt certificate) and an 80→443 redirect are configured. **As noted above, the `/`,
`/aruaru/`, `/aruaru-lady/`, and `/rakuten-mobile/` paths are proxied within the same vhost to
`audiocafe-tokyo-rust` (a Rust binary on `127.0.0.1:4400`), so PHP-FPM only answers requests
for the remaining paths.**

## Excluded from this repository

- Personal-information files (résumés/career histories/insurance memos under `top/`) —
  excluded via `.gitignore` since this is a public repository
- An unrelated separate tool (`Python/`)
- Cache/log/backup files (regenerated at runtime)

## Related projects

- [audiocafe-tokyo-rust](https://github.com/aon-co-jp/audiocafe-tokyo-rust) — the parallel
  Rust+RPoem port of this same site. The top page, `/aruaru/`, `/aruaru-lady/`, and
  `/rakuten-mobile/` have already been cut over to it in production
- [aruaru-tokyo-server](https://github.com/aon-co-jp/aruaru-tokyo-server) — the sister site
  `aruaru.tokyo` (Rust+Poem, separate domain/project), which mirror-proxies
  `/aruaru/`/`/aruaru-lady/`/`/rakuten-mobile/` back to this site's content
- [aruaru-easyweb](https://github.com/aon-co-jp/aruaru-easyweb) — domain/HTTPS automation and
  the OTP auth server
