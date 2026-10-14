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

## 検索結果ベースのコンテンツはスクレイプせず直接遷移させる(教訓)

YouTube検索結果ページ等、bot対策のあるページを`r.jina.ai`のようなスクレイププロキシ経由で取得し、「それらしい1件」を推測して自動再生・自動表示する設計は避けること。2026-07-16、jina.aiのYouTubeに対するブロック挙動が403→(`X-Respond-With: markdown`ヘッダーで一時回避)→401と繰り返し変化し、外部サービスの仕様変更に永続的に追随し続ける必要があると判明したため、この設計自体を廃止した。

代わりに、検索ワードに基づくコンテンツが必要な箇所は、スクレイプせず**実際の検索結果ページ(例: `https://www.youtube.com/results?search_query=...`)へ直接画面遷移させる**こと。ユーザーは本物の検索エンジンの結果を見ることになり、無関係なコンテンツが表示される可能性がそもそも存在しない。

```javascript
// 良い例: 実際の検索結果ページへ直接遷移
window.location.assign('https://www.youtube.com/results?search_query=' + encodeURIComponent(query));

// 避けるべき例: スクレイプして推測した1件を自動再生
// fetch('https://r.jina.ai/' + searchUrl, {...}).then(...) // ← 外部サービスの挙動変化に弱い
```

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

## VPS お引越し手順(2026-10-14追記)

現在の本番は ConoHa VPS(AlmaLinux、4コア・RAM 3.6GB・GPUなし)1台。新しいVPSへ移すときの手順と、
**Gitで復元できるもの / できないもの**を整理する。**この手順書は新品のVPSでの通し試験は未実施**
(`deploy/vps/install-units.sh`も同様)。知識の自動復元だけは、空の知識ストアで実測済み(19件を18秒で復元)。

### 構成(これを再現する)

```
nginx(443, certbot)
 ├─ /  /aruaru/  /aruaru-lady/  /rakuten-mobile/  /summary ─▶ Rust版 audiocafe-tokyo-rust (127.0.0.1:4400)
 ├─ 上記以外のPHP(index-*.php など) ─▶ PHP-FPM (/run/php-fpm/www.sock, ルート /var/www/audiocafe.tokyo)
 └─ (旧PHPホームページ) ─▶ php -S (127.0.0.1:4401, audiocafe-php-legacy.service)
aruaru-llm (127.0.0.1:4600) + aruaru-search (127.0.0.1:4610)   ← 生成AI(Gemini/Groq/Mistral等の無料枠)と知識ストア
systemd タイマー(毎朝05:00): audiocafe-cron-rust(楽天モバイル/doda/英会話) と audiocafe-cron-php(技術ランキング/学習価格/AI学習コメント + キャバ系)
systemd タイマー(6時間ごと+cron直後): knowledge-sync-push(知識をGitHubへ自動バックアップ)
```

設定ファイルの実物は `deploy/vps/`(`systemd/`、`nginx/`)にある。秘密は含めていない(`EnvironmentFile`の参照だけ)。

### 必要なリポジトリ(すべて `github.com/aon-co-jp/`)

| リポジトリ | 置き場所(本番) | 用途 |
|---|---|---|
| `audiocafe-tokyo`(このリポジトリ) | `/var/www/audiocafe.tokyo`(所有者`nginx`) | PHP本体と毎朝のクロール(`aruaru/cron.php`、`aruaru-lady/cron.php`) |
| `audiocafe-tokyo-rust` | `/root/audiocafe-tokyo-rust` | `/aruaru`等を配信するRust版(`cargo build --release`) |
| `aruaru-llm` | `/root/aruaru-llm` | 生成AIの束ね役+知識ストア(`cargo build --release`) |
| `aruaru-search` | `/root/aruaru-search` | 自前メタ検索(`aruaru-llm`が依存) |
| `aruaru-tokyo` | `/root/url/aruaru.tokyo` | `aruaru.tokyo`のトップ(Rust) |
| `open-english` | (読み取りのみ) | 知識のバックアップ先 `data/knowledge/knowledge.json` |

### 手順

1. **OS準備**(AlmaLinux): `nginx php-fpm php-cli php-curl php-mbstring php-xml git jq certbot python3-certbot-nginx` と Rust(`rustup`)。PHPは8.3で動作確認。
2. **リポジトリをclone**して上の場所へ置く。PHPは `git clone ... /var/www/audiocafe.tokyo` のあと `chown -R nginx:nginx`。
3. **Rustをビルド**: `audiocafe-tokyo-rust`・`aruaru-llm`・`aruaru-search`・`aruaru-tokyo` それぞれ `cargo build --release`(RAM 3.6GBでは同時ビルドを避ける)。
4. **秘密を配置**(Gitには無い。旧VPSから`scp`、または手元の`F:\`のtxtから):
   `/root/aruaru-llm/.env.providers`(Gemini/Groq/Mistral/Cloudflare/OpenRouterのキー)、`.env.google-search`、`.env.ocr`。
   GitHub書き込み用のトークン(`git credential store`、`knowledge-sync-push`が`open-english`へpushするため)。
5. **systemdを導入**: `sudo bash deploy/vps/install-units.sh`(unit・drop-inを`/etc/systemd/system`へ配置し`daemon-reload`、タイマーを有効化)。
6. **nginx**: `deploy/vps/nginx/*.conf` を `/etc/nginx/conf.d/` へ。**DNSを新IPへ切り替えてから** `certbot --nginx -d audiocafe.tokyo -d www.audiocafe.tokyo`(証明書はGitに無いので再発行)。
7. **知識の復元は自動**: `aruaru-llm`の初回起動で`sync_from_remote`が GitHub の `knowledge.json` を取り込む。確認: `curl -s 127.0.0.1:4600/v1/knowledge/search | jq '.items|length'`。
8. **初回クロール**: `systemctl start audiocafe-cron-rust.service audiocafe-cron-php.service`(PHP側は約19分)。キャッシュJSONは毎朝再生成されるので移す必要はない。
9. **確認**: `curl -I https://audiocafe.tokyo/aruaru/`、`systemctl list-timers | grep audiocafe`、`journalctl -u audiocafe-cron-php.service -n 30`。

### Gitで復元できるもの / できないもの

| 項目 | 復元 |
|---|---|
| PHPコード(`aruaru/index.php`の`--cron-tech`、価格訂正、`ca-extra.pem`) | ✅ このリポジトリ(2026-10-14に反映) |
| systemd・nginxの設定 | ✅ `deploy/vps/` |
| AIが学んだ知識(価格の訂正・ルール) | ✅ GitHubから自動復元 |
| APIキー(`.env.*`)・GitHubトークン | ❌ 手動で再配置 |
| Let's Encrypt証明書 | ❌ certbotで再発行 |
| クロール結果のキャッシュJSON | △ 不要(毎朝再生成、AI学習リンクは約170技術が揃うまで数日) |

### 注意(正直な開示)
- **リポジトリのPHPは、2026-10-14時点の本番より新しい部分がある**(例: `rakuten-mobile/index.php`の言語切替)。新VPSへリポジトリをそのまま置くと、現行の本番とは見た目が少し違う。現行VPSへは未反映(今回の作業はコードの保存が目的)。
- `db-engines.com`用の中間証明書(`aruaru/ca-extra.pem`)は2028年9月に期限。発行元が変わると取得に失敗する。
- 学習価格・AI学習コメントのURL実在確認は外部サイトの作りに依存する。ページが変わると「no_match」「要確認」になる(前回値は維持)。
