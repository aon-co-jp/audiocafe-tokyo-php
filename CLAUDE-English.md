# Development policy & environment rules (audiocafe.tokyo) — condensed English version

> This is a condensed English translation covering the current state only. For the full
> historical HANDOFF log (all changes since 2026-07-16), see the Japanese original,
> [CLAUDE.md](CLAUDE.md).

This section follows [`open-raid-z`](https://github.com/aon-co-jp/open-raid-z)'s `CLAUDE.md`
as the canonical source, copied into each project per this ecosystem's sync convention.

## Role of this repository

A PHP multi-content site that serves job listings (`aruaru`/`aruaru-lady`), Rakuten Mobile
information (`rakuten-mobile`), a company profile (`top`), and more, all from a single VPS.

**The sister domain `aruaru.tokyo` ([aruaru-tokyo-server](https://github.com/aon-co-jp/aruaru-tokyo-server))
is implemented in Rust+Poem, and there is no plan to unify stacks across domains — that is a
deliberate design choice.**

For `audiocafe.tokyo` itself, however, **a parallel migration to Rust
([audiocafe-tokyo-rust](https://github.com/aon-co-jp/audiocafe-tokyo-rust)) is under way as a
separate repository**, and in production the top page (`/`), `/aruaru/`, `/aruaru-lady/`, and
`/rakuten-mobile/` are already proxied to the Rust binary at `127.0.0.1:4400` via the
production nginx config (`/etc/nginx/conf.d/audiocafe.tokyo.conf`). All other paths
(`/top/`, `/cancer/`, `/Python/`, `/video/`, static cache JSON, etc.) are still served by this
PHP implementation, and the cron-driven cache refresh (`*-cache.json` generation) also
remains this PHP repository's responsibility.

## Tech stack

- Plain PHP (no framework). Generally one file per section (`index.php` bundles logic,
  HTML, and JS for its section).
- Multi-language pages (`index-<lang>.php`) share a common `lang-nav.php` via PHP `include`.
- External integrations: Google Custom Search, a Google Translate proxy link.

## Known development pitfalls

- **Do not scrape YouTube search-result pages via `r.jina.ai` to guess and auto-play a
  "plausible" video** (policy change, 2026-07-16). jina.ai's blocking behavior toward YouTube
  kept shifting (403 → worked around with a header → 401), proving fundamentally unstable to
  chase. Where a search-query-driven video is needed, navigate directly to the real YouTube
  search-results page instead (see `navigateToNonPlayableUrl`).
- `stripSeparators()` (handles hyphen/space variations, e.g. `DD67000` vs. `DD-67000`) remains
  useful elsewhere, such as in title-relevance checks (`isTitleRelevant`).
- **Never record the VPS's real IP address in code or documentation** (existing operational
  rule, inherited).
- **Never push personal-information files** (résumés/career histories/insurance memos under
  `top/`) to this public repository — already excluded via `.gitignore`; apply the same rule
  to any new personal-information files.

## Deployment

```bash
scp -r <files> conoha:/var/www/audiocafe.tokyo/<path>
ssh conoha "chown nginx:nginx /var/www/audiocafe.tokyo/<path>"
```

Served by nginx + PHP-FPM (`/etc/nginx/conf.d/audiocafe.tokyo.conf`). Port 443 uses a real
Let's Encrypt certificate; port 80 redirects to 443. **As noted above, `/`, `/aruaru/`,
`/aruaru-lady/`, and `/rakuten-mobile/` are proxied within the same vhost file to
`127.0.0.1:4400` (audiocafe-tokyo-rust), so only those four paths are answered by the Rust
binary rather than PHP-FPM.** Everything else (`/top/`, `/cancer/`, `/Python/`, `/video/`,
cache JSON serving, etc.) continues to go through PHP-FPM as before.

## Related projects

- [audiocafe-tokyo-rust](https://github.com/aon-co-jp/audiocafe-tokyo-rust) — the parallel
  migration of this same site to Rust+RPoem. The top page, `/aruaru/`, `/aruaru-lady/`, and
  `/rakuten-mobile/` have already been cut over to it in production; other paths and all cron
  auto-refresh logic remain this PHP repository's responsibility.
- [aruaru-tokyo-server](https://github.com/aon-co-jp/aruaru-tokyo-server) — the sister site
  `aruaru.tokyo` (Rust+Poem, a separate domain/project). Its top page links to both the
  Japanese and multi-language versions of `aruaru`/`aruaru-lady`, and mirror-proxies
  `/aruaru/`/`/aruaru-lady/`/`/rakuten-mobile/` back to this domain's content.
- [aruaru-easyweb](https://github.com/aon-co-jp/aruaru-easyweb) — domain/HTTPS automation and
  the OTP auth server (`easyweb.tokyo`).
- [open-raid-z](https://github.com/aon-co-jp/open-raid-z) — canonical source of the shared
  development rules.

## Operational rules

- Always push this `CLAUDE.md` together with any code commit/push made during development.
- Since this is a public repository, always consider adding new files containing personal or
  secret information to `.gitignore`.

## Latest HANDOFF entry (see CLAUDE.md for the full log)

- **2026-08-17 — Added a McIntosh Amplifier link to the YouTube background player** (per user
  instruction: "after SPEC in audiocafe.tokyo's YouTube [series], paste the McIntosh Amplifier
  link https://ameblo.jp/www-aon/entry-12976022104.html"). Added a new entry to the
  `SEARCH_SERIES` array, placed immediately after the "SPEC RPA-MG1000 RPA-MG3000 image
  search" entry and immediately before "Pass Labs USA". The same entry, in the same position,
  was also added to the sister repository `audiocafe-tokyo-rust`'s `assets/search_series.json`
  (see that repo's CLAUDE.md). The English README/CLAUDE/PORTING documents (this set of
  files) were created alongside this change.
