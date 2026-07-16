# PORTING.md — お引越し可能ファイル

他プロジェクトへそのまま(または軽微な変更で)持っていける実装パターン一覧。

## `lang-nav.php`(`aruaru/`・`aruaru-lady/`)

12言語(JA/EN/KO/ZH-CN/ZH-TW/RU/UK/DE/IT/FR/AR/FA)の言語切替ナビを、`$current`変数だけ差し替えてinclude共有する軽量パターン。

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

各言語ページの先頭で`$current = '<lang>'; include __DIR__ . '/lang-nav.php';`するだけで、多言語ナビが機能する。アラビア語・ペルシャ語は`<html lang="ar" dir="rtl">`のように`dir="rtl"`を追加すること。

## jina.aiスクレイプの403回避ヘッダー

YouTube検索結果ページ等、bot対策のあるページを`r.jina.ai`経由でスクレイプする場合、ヘッダー無しのfetchだと403で空ページしか返らないことがある。

```javascript
fetch('https://r.jina.ai/' + url, {
  credentials: 'omit',
  mode: 'cors',
  headers: { 'x-respond-with': 'markdown' }
})
```

このヘッダーはjina.ai公式のCORSプリフライトで許可されている(`Access-Control-Allow-Headers: x-respond-with`を確認済み)。同様の403問題が起きた場合、まずこのヘッダーの有無を疑うこと。

## 表記ゆれに強い文字列マッチング(`stripSeparators`)

検索キーワードと実際のコンテンツタイトルとの間でハイフン・アンダースコア・空白(全角含む)の有無が食い違う場合の対策。

```javascript
function stripSeparators(s) {
  return String(s || '').replace(/[\s　_-]+/g, '');
}
// 比較前に両辺へ適用する
if (stripSeparators(title.toLowerCase()).indexOf(stripSeparators(token.toLowerCase())) !== -1) { /* match */ }
```

型番・製品名などを含む検索(例: "DD67000" vs "DD-67000")で特に有効。

## nginx: HTTP→HTTPSリダイレクト + Let's Encrypt自動取得の定型パターン

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

`certbot certonly --webroot -w /var/www/acme-webroot -d example.tokyo -d www.example.tokyo --non-interactive --agree-tos -m <email>` で証明書取得後、上記のように443番ブロックを追加する。
