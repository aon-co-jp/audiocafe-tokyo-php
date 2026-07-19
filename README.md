# audiocafe.tokyo

PHP製のマルチコンテンツサイト。IT/建築系求人情報(`aruaru`)・女性向け求人/夜間エンターテインメント情報(`aruaru-lady`)・楽天モバイル関連情報(`rakuten-mobile`)・会社案内(`top`)などを扱う。

姉妹サイト[aruaru.tokyo](https://github.com/aon-co-jp/aruaru-tokyo-server)(Rust + Poem製)とはドメイン・スタックともに意図的に分離している——`audiocafe.tokyo`はPHP、`aruaru.tokyo`はRust+Poemという、ドメインごとにスタックが異なる設計。

## 構成

- `index.php` — TOPページ(多言語カード選択UI、Google翻訳連携)
- `aruaru/` — IT・建築系求人情報。日本語版 + 11言語(EN/KO/ZH-CN/ZH-TW/RU/UK/DE/IT/FR/AR/FA)の翻訳版
- `aruaru-lady/` — 女性向け求人・夜間エンターテインメント情報。同じく12言語対応
- `rakuten-mobile/` — 楽天モバイル関連情報の自動クロール・キャッシュ表示
- `top/` — 会社案内
- `cancer/`, `world/`, `video/` — その他コンテンツページ

## 多言語対応

`aruaru/`・`aruaru-lady/`配下の各`index-<lang>.php`は、共通の`lang-nav.php`をincludeして言語切替ナビを表示する。アラビア語(`ar`)・ペルシャ語(`fa`)は`dir="rtl"`でレイアウトを右から左に対応。

## YouTube検索結果の再生バグ修正(2026-07-16)

`r.jina.ai`経由でYouTube検索結果ページをスクレイプし「それらしい動画」を推測して自動再生する方式は、jina.aiのYouTubeに対するブロック挙動が403→(ヘッダー追加で一時回避)→401と繰り返し変化し、根本的に不安定だった(DD67000に限らず、検索クエリを使う全てのシリーズボタンに影響)。最終的にスクレイプ依存を完全に撤廃し、検索URLは実際のYouTube検索結果ページへの直接画面遷移に統一(シリーズボタン/イントロ経路・NEXT/ランダムプール経路の両方に適用)。無関係な動画が再生される可能性自体が無くなった。

## デプロイ

VPS上の`/var/www/audiocafe.tokyo`へ`scp`で直接配置し、nginx+PHP-FPMで配信する(このリポジトリ自体はビルド不要)。443番のHTTPS(Let's Encrypt実証明書)と80番→443番リダイレクトの両方に対応。

## 除外しているもの

- 個人情報ファイル(`top/`配下の履歴書・職歴書・保険メモ)——publicリポジトリのため`.gitignore`で除外
- サイトと無関係な別ツール(`Python/`)
- キャッシュ・ログ・バックアップファイル群(実行時に再生成される)

## 関連プロジェクト

- [aruaru-tokyo-server](https://github.com/aon-co-jp/aruaru-tokyo-server) — 姉妹サイト`aruaru.tokyo`(Rust+Poem製)。`/aruaru/`・`/aruaru-lady/`・`/rakuten-mobile/`をミラーproxyでこのサイトのコンテンツへ橋渡ししている
- [aruaru-easyweb](https://github.com/aon-co-jp/aruaru-easyweb) — ドメイン/HTTPS自動化・OTP認証サーバー
