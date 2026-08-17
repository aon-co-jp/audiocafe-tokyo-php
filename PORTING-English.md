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
