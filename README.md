# audiocafe.tokyo

PHP製のマルチコンテンツサイト。IT/建築系求人情報(`aruaru`)・女性向け求人/夜間エンターテインメント情報(`aruaru-lady`)・楽天モバイル関連情報(`rakuten-mobile`)・会社案内(`top`)などを扱う。

姉妹サイト[aruaru.tokyo](https://github.com/aon-co-jp/aruaru-tokyo-server)(Rust + Poem製)とはドメイン・スタックともに意図的に分離している——`audiocafe.tokyo`はPHP、`aruaru.tokyo`はRust+Poemという、ドメインごとにスタックが異なる設計。

**このリポジトリ自体(PHP版)も、[audiocafe-tokyo-rust](https://github.com/aon-co-jp/audiocafe-tokyo-rust)への段階的な移行が進行中**であり、本番では既にトップページ・`/aruaru/`・`/aruaru-lady/`・`/rakuten-mobile/`の4パスがRust版へカットオーバー済み(nginxの`location`単位でのプロキシ切替、`127.0.0.1:4400`)。それ以外のパス(`/top/`・`/cancer/`・`/Python/`・`/video/`等)とcronによるキャッシュ自動更新は引き続きこのPHPリポジトリが担当している。

## 構成

- `index.php` — TOPページ(多言語カード選択UI、Google翻訳連携)
- `aruaru/` — IT・建築系求人情報。日本語版 + 11言語(EN/KO/ZH-CN/ZH-TW/RU/UK/DE/IT/FR/AR/FA)の翻訳版
- `aruaru-lady/` — 女性向け求人・夜間エンターテインメント情報。同じく12言語対応
- `rakuten-mobile/` — 楽天モバイル関連情報の自動クロール・キャッシュ表示。`aruaru`/`aruaru-lady`と同じ`lang-nav.php`パターンで17言語対応(日本語+英語(米/英)/伊/独/オーストリア/スイス/仏/露/ウクライナ/アラビア/ペルシャ/韓/中国語簡体字・繁体字/西/フィリピン語、2026-08-04)
- `top/` — 会社案内
- `cancer/`, `world/`, `video/` — その他コンテンツページ

## 多言語対応

`aruaru/`・`aruaru-lady/`配下の各`index-<lang>.php`は、共通の`lang-nav.php`をincludeして言語切替ナビを表示する。アラビア語(`ar`)・ペルシャ語(`fa`)は`dir="rtl"`でレイアウトを右から左に対応。

## YouTube検索結果の再生バグ修正(2026-07-16)

`r.jina.ai`経由でYouTube検索結果ページをスクレイプし「それらしい動画」を推測して自動再生する方式は、jina.aiのYouTubeに対するブロック挙動が403→(ヘッダー追加で一時回避)→401と繰り返し変化し、根本的に不安定だった(DD67000に限らず、検索クエリを使う全てのシリーズボタンに影響)。最終的にスクレイプ依存を完全に撤廃し、検索URLは実際のYouTube検索結果ページへの直接画面遷移に統一(シリーズボタン/イントロ経路・NEXT/ランダムプール経路の両方に適用)。無関係な動画が再生される可能性自体が無くなった。

## デプロイ

VPS上の`/var/www/audiocafe.tokyo`へ`scp`で直接配置し、nginx+PHP-FPMで配信する(このリポジトリ自体はビルド不要)。443番のHTTPS(Let's Encrypt実証明書)と80番→443番リダイレクトの両方に対応。**ただし前述の通り`/`・`/aruaru/`・`/aruaru-lady/`・`/rakuten-mobile/`の4パスは同じvhost内で`audiocafe-tokyo-rust`(Rustバイナリ、`127.0.0.1:4400`)へのプロキシに切り替え済みのため、PHP-FPMが直接応答するのはそれ以外のパスのみ。**

## 除外しているもの

- 個人情報ファイル(`top/`配下の履歴書・職歴書・保険メモ)——publicリポジトリのため`.gitignore`で除外
- サイトと無関係な別ツール(`Python/`)
- キャッシュ・ログ・バックアップファイル群(実行時に再生成される)

## 関連プロジェクト

- [audiocafe-tokyo-rust](https://github.com/aon-co-jp/audiocafe-tokyo-rust) — このサイト自身のRust+RPoemへの並行移植版。トップページ・`/aruaru/`・`/aruaru-lady/`・`/rakuten-mobile/`は本番で既にこちらへカットオーバー済み
- [aruaru-tokyo-server](https://github.com/aon-co-jp/aruaru-tokyo-server) — 姉妹サイト`aruaru.tokyo`(Rust+Poem製、別ドメイン・別プロジェクト)。`/aruaru/`・`/aruaru-lady/`・`/rakuten-mobile/`をミラーproxyでこのサイトのコンテンツへ橋渡ししている
- [aruaru-easyweb](https://github.com/aon-co-jp/aruaru-easyweb) — ドメイン/HTTPS自動化・OTP認証サーバー

## VPSお引越し(2026-10-14追記)
本番のVPSを新しいVPSへ移せるよう、構成・手順・設定ファイルをまとめた。設定の実物は [`deploy/vps/`](deploy/vps/)(systemd・nginx、秘密なし)、
手順と「Gitで復元できるもの/できないもの」は [`PORTING.md`](PORTING.md) の「VPS お引越し手順」を参照。
毎朝05:00の自動クロールは、`aruaru/cron.php`(技術ランキング・学習価格・AI学習コメント。生成AIは自前の`aruaru-llm`経由でOpenAIは使わない)と
`aruaru-lady/cron.php`(キャバ系ランキング)。**新品のVPSでの通し試験は未実施。**

## API 方針: REST 全廃・gRPC / Connect へ (2026-11-04)

### 日本語
- **REST は全廃する。新しい API は REST で作らない。** ブラウザや `curl` から呼ぶ API は **Connect の単純呼び出し**(`POST /<パッケージ>.<Service>/<Method>`、`content-type: application/json`、本文は JSON)、サービス間は **gRPC / gRPC-web**(RPoem の管理API `open_runo.admin.v1.Admin` と同じ流儀)。ストリーミングは、必要になってから。
- **既存の REST は、触るときに順に移す。** 移したら旧 REST の経路は消す。移行が終わるまで残るが、**新規には追加しない**。
- **JSON は RS-JSON に統一**する(読み込みは `from_str_secure` / `parse_secure` でサイズ・深さ・重複キーの上限つき、書き出しは `rust_json::fast`)。ブラウザの WASM に `prost` は入れない(サイズが増えるため)。
- **例外(自分たちの API ではなく、相手の規格が決めているもの)**: ACME、OAuth/OIDC のコールバック、第三者(GitHub など)からの Webhook、ヘルスチェック、静的ファイル配信、OTLP など。これらは規格どおりの HTTP を受ける。
- 参考実装: `RPoem`(`open_runo.admin.v1.Admin`)、`open-easy-web`(`server/src/connect.rs`、`proto/open_easy_web/admin/v1/admin.proto`)。
- ※ このリポジトリのコードが既に方針どおりかどうかは、各リポジトリの現状による。この節は方針の記録であり、コードの変更を意味しない。

### English
- **REST is being abolished. New APIs are not built as REST.** APIs called from browsers or `curl` use **Connect unary calls** (`POST /<package>.<Service>/<Method>`, `content-type: application/json`, JSON body); service-to-service calls use **gRPC / gRPC-web** (the same style as RPoem's admin API `open_runo.admin.v1.Admin`). Streaming only when it is actually needed.
- **Existing REST endpoints are migrated one by one, when they are touched.** The old REST route is removed once migrated. Until migration is finished they remain, but **no new ones are added**.
- **JSON is unified on RS-JSON**: reading with `from_str_secure` / `parse_secure` (size, depth and duplicate-key limits), writing with `rust_json::fast`. `prost` is not put into browser WASM (it would grow the binary).
- **Exceptions (shapes dictated by someone else's standard, not our own API)**: ACME, OAuth/OIDC callbacks, webhooks from third parties (e.g. GitHub), health checks, static file serving, OTLP, and so on. These accept plain HTTP as the standard requires.
- Reference implementations: `RPoem` (`open_runo.admin.v1.Admin`) and `open-easy-web` (`server/src/connect.rs`, `proto/open_easy_web/admin/v1/admin.proto`).
- Note: whether the code in a given repository already follows this policy depends on that repository. This section records the policy; it does not mean the code has been changed.

### Deutsch (KI-Übersetzung, redaktionell überarbeitet; noch nicht von Muttersprachlern geprüft)
- **REST wird vollständig abgeschafft; neue APIs entstehen nicht mehr als REST.** APIs, die aus dem Browser oder per `curl` aufgerufen werden, verwenden **unäre Connect-Aufrufe** (`POST /<Paket>.<Service>/<Methode>`, `content-type: application/json`, JSON-Body). Zwischen Diensten kommen **gRPC bzw. gRPC-web** zum Einsatz, im selben Stil wie die Admin-API von RPoem (`open_runo.admin.v1.Admin`). Streaming wird erst ergänzt, wenn es tatsächlich gebraucht wird.
- **Bestehende REST-Endpunkte werden schrittweise umgestellt, und zwar immer dann, wenn man ohnehin an ihnen arbeitet.** Sobald ein Endpunkt umgestellt ist, wird die alte REST-Route entfernt. Bis zum Abschluss der Migration bleiben die übrigen bestehen, es werden aber **keine neuen mehr hinzugefügt**.
- **JSON wird einheitlich über RS-JSON verarbeitet**: Gelesen wird mit `from_str_secure` bzw. `parse_secure` (mit Obergrenzen für Größe, Verschachtelungstiefe und doppelte Schlüssel), geschrieben mit `rust_json::fast`. `prost` wird nicht in das Browser-WASM aufgenommen, da es die Datei vergrößern würde.
- **Ausnahmen (Schnittstellen, deren Form ein fremder Standard vorgibt und die nicht zu unserer eigenen API gehören)**: ACME, OAuth/OIDC-Callbacks, Webhooks von Drittanbietern (z. B. GitHub), Health-Checks, die Auslieferung statischer Dateien, OTLP und Ähnliches. Diese nehmen, wie vom jeweiligen Standard verlangt, gewöhnliches HTTP entgegen.
- Referenzimplementierungen: `RPoem` (`open_runo.admin.v1.Admin`) und `open-easy-web` (`server/src/connect.rs`, `proto/open_easy_web/admin/v1/admin.proto`).
- Hinweis: Ob der Code eines einzelnen Repositorys diese Richtlinie bereits erfüllt, hängt vom jeweiligen Repository ab. Dieser Abschnitt dokumentiert lediglich die Richtlinie; er besagt nicht, dass der Code schon angepasst wurde.

### Русский (перевод выполнен с помощью ИИ и отредактирован; носителями языка пока не проверен)
- **От REST мы полностью отказываемся; новые API больше не создаются в стиле REST.** API, к которым обращаются из браузера или через `curl`, используют **унарные вызовы Connect** (`POST /<пакет>.<Service>/<Method>`, `content-type: application/json`, тело запроса — JSON). Для взаимодействия между сервисами применяются **gRPC и gRPC-web** — в том же стиле, что и административный API RPoem (`open_runo.admin.v1.Admin`). Потоковая передача данных добавляется лишь тогда, когда в ней действительно возникает необходимость.
- **Существующие REST-эндпоинты переводятся поэтапно — по мере того, как с ними приходится работать.** После перевода старый REST-маршрут удаляется. Пока миграция не завершена, оставшиеся эндпоинты сохраняются, но **новые не добавляются**.
- **Работа с JSON унифицирована на основе RS-JSON**: чтение — через `from_str_secure` или `parse_secure` (с ограничениями на размер, глубину вложенности и повторяющиеся ключи), запись — через `rust_json::fast`. `prost` в браузерный WASM не включается, поскольку это увеличило бы размер файла.
- **Исключения (интерфейсы, форму которых определяет чужой стандарт, а не наш собственный API)**: ACME, обратные вызовы OAuth/OIDC, вебхуки сторонних сервисов (например, GitHub), проверки работоспособности, раздача статических файлов, OTLP и тому подобное. Такие интерфейсы принимают обычный HTTP, как того требует соответствующий стандарт.
- Эталонные реализации: `RPoem` (`open_runo.admin.v1.Admin`) и `open-easy-web` (`server/src/connect.rs`, `proto/open_easy_web/admin/v1/admin.proto`).
- Примечание: соответствует ли код конкретного репозитория этой политике, зависит от самого репозитория. Данный раздел лишь фиксирует политику и не означает, что код уже изменён.
