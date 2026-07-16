# 開発方針＆開発環境ルール(audiocafe.tokyo)

作業ドライブは`F:\open-runo`。この節は[`open-raid-z`](https://github.com/aon-co-jp/open-raid-z)の`CLAUDE.md`を正本とし、各プロジェクトへコピーして同期する方針に準じる。

## このリポジトリの役割

PHP製のマルチコンテンツサイト。求人情報(`aruaru`/`aruaru-lady`)・楽天モバイル情報(`rakuten-mobile`)・会社案内(`top`)などを1つのVPS上でまとめて配信する。

**技術スタックは意図的にPHP**——姉妹サイト`aruaru.tokyo`([aruaru-tokyo-server](https://github.com/aon-co-jp/aruaru-tokyo-server))はRust+Poemで実装されているが、これは「ドメインごとにスタックが異なってよい」という設計判断であり、統一・移行の予定はない。

## 技術スタック

- 素のPHP(フレームワーク不使用)。1ファイル完結が基本(`index.php`が各セクションのロジック・HTML・JSを内包)。
- 多言語ページ(`index-<lang>.php`)は共通の`lang-nav.php`をPHPの`include`で共有する。
- 外部連携: Google Custom Search・YouTube検索結果の`r.jina.ai`スクレイプ・Google翻訳プロキシリンク。

## 開発時の注意点(実際に発生した既知の問題)

- **`r.jina.ai`はUser-Agent/ヘッダー無しのfetchだとYouTube側から403を返される**(2026-07-16判明)。`fetch('https://r.jina.ai/' + url, {{ headers: {{ 'x-respond-with': 'markdown' }} }})`のように必ずヘッダーを付けること。このヘッダーはjina.ai公式のCORSプリフライトで許可されている(確認済み)。
- **検索クエリとタイトルの表記ゆれ**(ハイフン・スペースの有無、例: `DD67000` vs `DD-67000`)は、`stripSeparators()`で正規化してから比較すること(`collectRelevantSearchIds`/`isTitleRelevant`参照)。
- **VPSの実IPアドレスをコード・ドキュメントに記録しない**こと(既存の運用ルールを継承)。
- **個人情報ファイル(`top/`配下の履歴書・職歴書・保険メモ)はpublicリポジトリにpushしない**——`.gitignore`で除外済み。新しい個人情報ファイルを追加する場合も同様に扱うこと。

## デプロイ

```bash
scp -r <対象ファイル> conoha:/var/www/audiocafe.tokyo/<パス>
ssh conoha "chown nginx:nginx /var/www/audiocafe.tokyo/<パス>"
```

nginx+PHP-FPM(`/etc/nginx/conf.d/audiocafe.tokyo.conf`)で配信。443番はLet's Encrypt実証明書、80番は443番へリダイレクト。

## 関連プロジェクト

- [aruaru-tokyo-server](https://github.com/aon-co-jp/aruaru-tokyo-server) — 姉妹サイト`aruaru.tokyo`(Rust+Poem製)。TOPページから`aruaru`/`aruaru-lady`両方の日本語版+多言語版へリンクし、`/aruaru/`・`/aruaru-lady/`・`/rakuten-mobile/`をミラーproxyでこのリポジトリのコンテンツへ橋渡ししている
- [aruaru-easyweb](https://github.com/aon-co-jp/aruaru-easyweb) — ドメイン/HTTPS自動化・OTP認証サーバー(`easyweb.tokyo`)
- [open-raid-z](https://github.com/aon-co-jp/open-raid-z) — 開発ルールの正本

## 運用ルール

- 開発中はこの`CLAUDE.md`を、コード変更のコミット/pushと必ず一緒にpushする。
- publicリポジトリのため、個人情報・秘密情報を含むファイルを新規追加する際は必ず`.gitignore`への追加を検討する。

## 現状

- 2026-07-16: 初回git化・push完了。95ファイル、53MB(個人情報ファイル・無関係な別ツール・キャッシュ/ログを除外)。
- 2026-07-16: `r.jina.ai`の403ブロック問題を修正(`X-Respond-With: markdown`ヘッダー追加)。YouTube検索結果ベースの全シリーズボタンで無関係な動画が再生されるバグを解消。DD67000のハイフン表記ゆれの正規化も追加。
- 2026-07-16: `aruaru`/`aruaru-lady`を日本語+11言語(EN/KO/ZH-CN/ZH-TW/RU/UK/DE/IT/FR/AR/FA)対応に拡張。共通の`lang-nav.php`で言語切替。
- 2026-07-16: `rakuten-mobile`ページにも`aruaru.tokyo`への導線バナーを追加。

## HANDOFF(直近の作業ログ、上が最新)

- **2026-07-16**: リポジトリ初期化・ドキュメント整備(README/CLAUDE.md/PORTING.md新規作成)・全ファイルpush完了。
