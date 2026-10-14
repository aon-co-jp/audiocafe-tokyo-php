# PORTING.md — Reusable patterns

A list of implementation patterns that can be carried over to other projects as-is (or with
minor changes).

## `lang-nav.php` (`aruaru/`, `aruaru-lady/`)

A lightweight pattern for a 12-language (JA/EN/KO/ZH-CN/ZH-TW/RU/UK/DE/IT/FR/AR/FA, later
extended to 18) language-switch navigation, shared via `include` by swapping only the
`$current` variable.

```php
<?php
$languages = [
    'ja' => ['label' => '🇯🇵 日本語', 'href' => '/aruaru/'],
    'en' => ['label' => '🇺🇸 English', 'href' => '/aruaru/index-en.php'],
    // ...
];
$current = $current ?? 'ja';
?>
<div class="lang-switch">
<?php foreach ($languages as $code => $l): if ($code === $current) continue; ?>
  <a href="<?= htmlspecialchars($l['href'], ENT_QUOTES, 'UTF-8') ?>"><?= $l['label'] ?></a>
<?php endforeach; ?>
</div>
```

Just add `$current = '<lang>'; include __DIR__ . '/lang-nav.php';` at the top of each
language page to get working multi-language navigation. For Arabic/Persian, also add
`dir="rtl"`, e.g. `<html lang="ar" dir="rtl">`.

## Don't scrape search-result-based content — navigate directly instead (lesson learned)

Avoid designs that fetch bot-protected pages (like YouTube search results) through a
scraping proxy such as `r.jina.ai` and then guess/auto-play/auto-display "the most likely"
one item. On 2026-07-16 we found jina.ai's blocking behavior toward YouTube kept shifting
(403 → temporarily worked around with an `X-Respond-With: markdown` header → 401), meaning
we'd have to chase an external service's changing behavior indefinitely — so the design
itself was scrapped.

Instead, wherever search-query-driven content is needed, **navigate directly to the real
search-results page** (e.g. `https://www.youtube.com/results?search_query=...`) without
scraping. The user sees genuine search-engine results, and there is no possibility of
unrelated content being shown.

```javascript
// Good: navigate directly to the real search-results page
window.location.assign('https://www.youtube.com/results?search_query=' + encodeURIComponent(query));

// Avoid: scrape and auto-play a guessed single result
// fetch('https://r.jina.ai/' + searchUrl, {...}).then(...) // fragile to the external service's behavior changes
```

## String matching robust to separator variations (`stripSeparators`)

For cases where hyphens/underscores/spaces (including full-width) differ between a search
keyword and the actual content title.

```javascript
function stripSeparators(s) {
  return String(s || '').replace(/[\s　_-]+/g, '');
}
// Apply to both sides before comparing
if (stripSeparators(title.toLowerCase()).indexOf(stripSeparators(token.toLowerCase())) !== -1) { /* match */ }
```

Especially useful for searches involving model numbers/product names (e.g. "DD67000" vs.
"DD-67000").

## nginx: HTTP→HTTPS redirect + Let's Encrypt auto-provisioning boilerplate

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name example.tokyo www.example.tokyo;
    location /.well-known/acme-challenge/ { root /var/www/acme-webroot; }
    location / { return 301 https://$host$request_uri; }
}

server {
    listen 443 ssl;
    listen [::]:443 ssl;
    server_name example.tokyo www.example.tokyo;
    ssl_certificate     /etc/letsencrypt/live/example.tokyo/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/example.tokyo/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    # ... location /
}
```

After obtaining a certificate with
`certbot certonly --webroot -w /var/www/acme-webroot -d example.tokyo -d www.example.tokyo --non-interactive --agree-tos -m <email>`,
add the 443 block as shown above.

## VPS migration guide (added 2026-10-14)

Production is a single ConoHa VPS (AlmaLinux, 4 cores, 3.6 GB RAM, no GPU). This section explains how to move to a new VPS and what
**can / cannot be restored from Git**. **This runbook has not been run end-to-end on a fresh machine** (neither has
`deploy/vps/install-units.sh`). Only the automatic knowledge restore was actually measured on an empty store (19 entries restored in 18 s).

### Layout to reproduce

```
nginx (443, certbot)
 ├─ /  /aruaru/  /aruaru-lady/  /rakuten-mobile/  /summary ─▶ Rust app audiocafe-tokyo-rust (127.0.0.1:4400)
 ├─ other PHP pages (index-*.php, ...)                      ─▶ PHP-FPM (/run/php-fpm/www.sock, root /var/www/audiocafe.tokyo)
 └─ (legacy PHP home page)                                  ─▶ php -S (127.0.0.1:4401, audiocafe-php-legacy.service)
aruaru-llm (127.0.0.1:4600) + aruaru-search (127.0.0.1:4610)   ← generative-AI broker (Gemini/Groq/Mistral free tiers) + knowledge store
systemd timers (daily 05:00): audiocafe-cron-rust (Rakuten Mobile / doda / English-lesson ranking) and audiocafe-cron-php (tech ranking / learning prices / AI learning comments + hostess-job rankings)
systemd timer (every 6 h + right after the cron): knowledge-sync-push (automatic knowledge backup to GitHub)
```

The real config files live in `deploy/vps/` (`systemd/`, `nginx/`). They contain no secrets (only `EnvironmentFile` references).

### Required repositories (all under `github.com/aon-co-jp/`)

| Repository | Production location | Purpose |
|---|---|---|
| `audiocafe-tokyo` (this repo) | `/var/www/audiocafe.tokyo` (owner `nginx`) | PHP app and the daily crawlers (`aruaru/cron.php`, `aruaru-lady/cron.php`) |
| `audiocafe-tokyo-rust` | `/root/audiocafe-tokyo-rust` | Rust app that serves `/aruaru` etc. (`cargo build --release`) |
| `aruaru-llm` | `/root/aruaru-llm` | Generative-AI broker + knowledge store (`cargo build --release`) |
| `aruaru-search` | `/root/aruaru-search` | In-house meta search (required by `aruaru-llm`) |
| `aruaru-tokyo` | `/root/url/aruaru.tokyo` | Top page of `aruaru.tokyo` (Rust) |
| `open-english` | (read-only) | Knowledge backup target `data/knowledge/knowledge.json` |

### Steps

1. **Prepare the OS** (AlmaLinux): `nginx php-fpm php-cli php-curl php-mbstring php-xml git jq certbot python3-certbot-nginx` and Rust (`rustup`). Verified with PHP 8.3.
2. **Clone the repositories** to the locations above. For PHP: `git clone ... /var/www/audiocafe.tokyo` then `chown -R nginx:nginx`.
3. **Build the Rust apps**: `cargo build --release` in each of `audiocafe-tokyo-rust`, `aruaru-llm`, `aruaru-search`, `aruaru-tokyo` (do not build them in parallel on 3.6 GB RAM).
4. **Place the secrets** (not in Git; `scp` from the old VPS or from the local `F:\` txt files):
   `/root/aruaru-llm/.env.providers` (Gemini/Groq/Mistral/Cloudflare/OpenRouter keys), `.env.google-search`, `.env.ocr`, and the GitHub write token
   (`git credential store`; `knowledge-sync-push` pushes to `open-english`).
5. **Install systemd units**: `sudo bash deploy/vps/install-units.sh` (copies units/drop-ins to `/etc/systemd/system`, runs `daemon-reload`, enables the timers).
6. **nginx**: copy `deploy/vps/nginx/*.conf` to `/etc/nginx/conf.d/`. **Switch DNS to the new IP first**, then `certbot --nginx -d audiocafe.tokyo -d www.audiocafe.tokyo` (certificates are not in Git; reissue them).
7. **Knowledge restores itself**: on first start `aruaru-llm` runs `sync_from_remote` and merges GitHub's `knowledge.json`. Check: `curl -s 127.0.0.1:4600/v1/knowledge/search | jq '.items|length'`.
8. **First crawl**: `systemctl start audiocafe-cron-rust.service audiocafe-cron-php.service` (the PHP side takes ~19 min). The cache JSON files are regenerated daily; no need to copy them.
9. **Verify**: `curl -I https://audiocafe.tokyo/aruaru/`, `systemctl list-timers | grep audiocafe`, `journalctl -u audiocafe-cron-php.service -n 30`.

### What Git can / cannot restore

| Item | Restorable |
|---|---|
| PHP code (`--cron-tech` in `aruaru/index.php`, price correction, `ca-extra.pem`) | ✅ this repo (committed 2026-10-14) |
| systemd and nginx configuration | ✅ `deploy/vps/` |
| Knowledge the AI learned (price corrections, rules) | ✅ restored automatically from GitHub |
| API keys (`.env.*`) and the GitHub token | ❌ re-place manually |
| Let's Encrypt certificates | ❌ reissue with certbot |
| Crawl result caches (JSON) | △ not needed (regenerated daily; AI learning links take a few days to cover ~170 technologies) |

### Caveats (honest disclosure)
- **The PHP in this repo is newer than production as of 2026-10-14 in places** (e.g. the language switcher in `rakuten-mobile/index.php`). Dropping the repo onto a new VPS will look slightly different from today's production. It has not been deployed to the current VPS (this change was about saving the code).
- The intermediate certificate for `db-engines.com` (`aruaru/ca-extra.pem`) expires in September 2028; if the issuer changes, fetching fails.
- Price scraping and the URL liveness checks depend on third-party sites; when a page changes they fall back to "no_match" / "check the official site" (the previous value is kept).
