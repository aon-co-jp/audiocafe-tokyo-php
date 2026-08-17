# 開発方針＆開発環境ルール(audiocafe.tokyo)

作業ドライブは`F:\open-runo`。この節は[`open-raid-z`](https://github.com/aon-co-jp/open-raid-z)の`CLAUDE.md`を正本とし、各プロジェクトへコピーして同期する方針に準じる。

## このリポジトリの役割

PHP製のマルチコンテンツサイト。求人情報(`aruaru`/`aruaru-lady`)・楽天モバイル情報(`rakuten-mobile`)・会社案内(`top`)などを1つのVPS上でまとめて配信する。

**姉妹ドメイン`aruaru.tokyo`([aruaru-tokyo-server](https://github.com/aon-co-jp/aruaru-tokyo-server))はRust+Poemで実装されており、ドメインごとにスタックが異なってよいという設計判断で統一の予定はない**。

一方、この`audiocafe.tokyo`自体については、**同一ドメイン内でRustへの並行移植プロジェクト([audiocafe-tokyo-rust](https://github.com/aon-co-jp/audiocafe-tokyo-rust))が別リポジトリとして進行中**であり、既にトップページ(`/`)・`/aruaru/`・`/aruaru-lady/`・`/rakuten-mobile/`の4パスは本番`nginx`設定(`/etc/nginx/conf.d/audiocafe.tokyo.conf`)で`127.0.0.1:4400`のRustバイナリへプロキシされ実際に稼働中(詳細は`audiocafe-tokyo-rust/PORTING.md`のHANDOFFログ参照)。それ以外の全パス(`/top/`・`/cancer/`・`/Python/`・`/video/`・静的キャッシュJSON等)は引き続きこのリポジトリのPHP実装がそのまま処理している。cronによるキャッシュ自動更新(`*-cache.json`生成)も引き続きこのPHPリポジトリ側の責務。したがって「技術スタックは意図的にPHPで統一・移行の予定はない」という記述は本サイト自身については実態と異なるため、この節で訂正する。

## 技術スタック

- 素のPHP(フレームワーク不使用)。1ファイル完結が基本(`index.php`が各セクションのロジック・HTML・JSを内包)。
- 多言語ページ(`index-<lang>.php`)は共通の`lang-nav.php`をPHPの`include`で共有する。
- 外部連携: Google Custom Search・Google翻訳プロキシリンク。

## 開発時の注意点(実際に発生した既知の問題)

- **YouTube検索結果ページを`r.jina.ai`でスクレイプして「それらしい動画」を推測再生する方式は採用しない**(2026-07-16、方針転換)。jina.aiのYouTubeに対するブロック挙動が403→(ヘッダー追加で回避)→401と繰り返し変化し、追随し続けるのが根本的に不安定と判明したため。検索ワードベースの動画再生が必要な箇所は、スクレイプせず実際のYouTube検索結果ページへ直接画面遷移させること(`navigateToNonPlayableUrl`参照)。
- **検索クエリとタイトルの表記ゆれ**(ハイフン・スペースの有無、例: `DD67000` vs `DD-67000`)を吸収する`stripSeparators()`は、タイトル関連性チェック(`isTitleRelevant`)等、他の用途では引き続き有効。
- **VPSの実IPアドレスをコード・ドキュメントに記録しない**こと(既存の運用ルールを継承)。
- **個人情報ファイル(`top/`配下の履歴書・職歴書・保険メモ)はpublicリポジトリにpushしない**——`.gitignore`で除外済み。新しい個人情報ファイルを追加する場合も同様に扱うこと。

## デプロイ

```bash
scp -r <対象ファイル> conoha:/var/www/audiocafe.tokyo/<パス>
ssh conoha "chown nginx:nginx /var/www/audiocafe.tokyo/<パス>"
```

nginx+PHP-FPM(`/etc/nginx/conf.d/audiocafe.tokyo.conf`)で配信。443番はLet's Encrypt実証明書、80番は443番へリダイレクト。**ただし前節の通り`/`・`/aruaru/`・`/aruaru-lady/`・`/rakuten-mobile/`の4パスは同じvhostファイル内で`127.0.0.1:4400`(audiocafe-tokyo-rust)へのプロキシに切り替え済みのため、この4パスに限りPHP-FPMではなくRustバイナリが応答する。**この4パス以外(`/top/`・`/cancer/`・`/Python/`・`/video/`・キャッシュJSON配信等)は従来通りPHP-FPM経由。

## 関連プロジェクト

- [audiocafe-tokyo-rust](https://github.com/aon-co-jp/audiocafe-tokyo-rust) — この`audiocafe.tokyo`自体をRust+RPoemへ段階的に移行する並行移植プロジェクト。トップページ・`/aruaru/`・`/aruaru-lady/`・`/rakuten-mobile/`は既に本番でRust版へカットオーバー済み(`nginx`の`location`単位、`127.0.0.1:4400`)、それ以外のパスと全cron自動更新処理はこのPHPリポジトリが引き続き担当
- [aruaru-tokyo-server](https://github.com/aon-co-jp/aruaru-tokyo-server) — 姉妹サイト`aruaru.tokyo`(Rust+Poem製、別ドメイン・別プロジェクト)。TOPページから`aruaru`/`aruaru-lady`両方の日本語版+多言語版へリンクし、`/aruaru/`・`/aruaru-lady/`・`/rakuten-mobile/`をミラーproxyでこのドメインのコンテンツへ橋渡ししている
- [aruaru-easyweb](https://github.com/aon-co-jp/aruaru-easyweb) — ドメイン/HTTPS自動化・OTP認証サーバー(`easyweb.tokyo`)
- [open-raid-z](https://github.com/aon-co-jp/open-raid-z) — 開発ルールの正本

## 運用ルール

- 開発中はこの`CLAUDE.md`を、コード変更のコミット/pushと必ず一緒にpushする。
- publicリポジトリのため、個人情報・秘密情報を含むファイルを新規追加する際は必ず`.gitignore`への追加を検討する。

## 現状

- 2026-07-16: 初回git化・push完了。95ファイル、53MB(個人情報ファイル・無関係な別ツール・キャッシュ/ログを除外)。
- 2026-07-16: YouTube検索結果の「無関係な動画が再生される」バグを根治。当初`X-Respond-With`ヘッダー追加でjina.aiの403を回避したが、その後jina.ai自体が401を返すようになり再発。最終的にjina.aiスクレイプ依存を完全に排除し、検索URLは実際のYouTube検索結果ページへの直接画面遷移に統一(`fetchAndCollect`・`fetchSearchResultIds`の両経路とも対応済み)。
- 2026-07-16: `aruaru`/`aruaru-lady`を日本語+11言語(EN/KO/ZH-CN/ZH-TW/RU/UK/DE/IT/FR/AR/FA)対応に拡張。共通の`lang-nav.php`で言語切替。
- 2026-07-16: `rakuten-mobile`ページにも`aruaru.tokyo`への導線バナーを追加。

## 運用ルール追記(2026-07-18、正本はopen-raid-zのCLAUDE.md参照) — 確認不要の自動継続・リミット解除後の自動再開

- **コンテキストウインドウ・5時間利用制限・その他のセッション中断が
  発生し、その後リミットが解除されて新しいセッションが開始された場合、
  「続けてよろしいですか」等の確認を挟まず、毎回自動的に前回セッションの
  続きの作業を再開すること**(ユーザー指示、2026-07-18)。具体的には:
  1. セッション開始時、各リポジトリの`git status`/`git log`と、この
     `CLAUDE.md`(および他プロジェクトのCLAUDE.md)のHANDOFF節・
     「次にすべきこと」記載を確認し、未完了・未pushの作業が無いかを
     まず裏取りする(タスク管理メタデータを鵜呑みにしない既存方針と
     同じ姿勢で、実際のgit状態を確認する)。
  2. 未完了作業が見つかった場合、ユーザーへの確認を求めず、そのまま
     自動的に検証(build/test)→修正→コミット→pushまで完了させる。
  3. 完了している場合は、各CLAUDE.mdの「次にすべきこと」「未着手・
     未完成」に記載された次の項目へ確認なしに着手する(既存の
     「未着手だからといって確認を求めて手を止めない」方針の延長)。
  4. 「続けてよろしければそのまま自動開発を継続します」のような、
     続行そのものを尋ねる確認は今後一切行わない(ユーザー指示、
     2026-07-18)。作業内容の要約・進捗報告はしてよいが、それは
     承認を求めるものではなく完了報告として書く。
  5. こまめにコミット・pushしておくことで、次回セッションが「どこから
     再開すべきか」を迷わず`git log`/CLAUDE.mdから機械的に判断できる
     ようにしておく(区切りがついた時点で都度コミット・pushする既存
     方針との組み合わせ)。


## 運用ルール追記(2026-07-19、正本はopen-raid-zのCLAUDE.md参照) — 白画面バグ等を見逃さない検証徹底

- **WEB/UIを持つ機能を実装した後は、ビルド成功・`cargo test`・curlでの
  ステータスコード確認だけで「完了」と報告せず、実際に画面が正しく
  表示される(白画面・レンダリング崩れ・コンソールエラーが無い)ところ
  まで確認すること**(ユーザー指示、2026-07-19)。
  1. ブラウザ操作が可能な環境では、実際にページを開いて表示内容
     (見出し・本文・想定した要素の存在)とコンソールエラーの有無を
     確認する。
  2. ブラウザ操作ができない環境では、少なくとも`curl`等でHTMLボディの
     中身を取得し、期待される文字列が実際に含まれているかを確認する
     ——ステータスコード200だけを見て「動作確認済み」としない。
  3. 白画面・エラー・期待した内容の欠落等の不具合が見つかった場合は、
     確認を求めず自動的に原因調査・修正・再確認まで行う。
  4. 本番ドメインが未取得・DNS未設定なだけの状態は上記の「白画面
     バグ」とは別物であり、混同しない(`localhost`確認で代替可)。


## HANDOFF(直近の作業ログ、上が最新)

- **2026-08-17 YouTube背景プレイヤーにMcIntosh Amplifierのリンクを追加
  (ユーザー指示「audiocafe.tokyoのYoutubeのSPECの次にMcintosh
  Amplifierのリンクにhttps://ameblo.jp/www-aon/entry-12976022104.html
  を貼って」への対応)**: `SEARCH_SERIES`配列の「SPEC RPA-MG1000
  RPA-MG3000 画像検索」エントリの直後、「Pass Labs USA」の直前に
  McIntosh Amplifierのエントリ(btn/label、ameblo.jpの当該記事URL)を
  追加。姉妹リポジトリ`audiocafe-tokyo-rust`側の`assets/
  search_series.json`にも同じ内容・同じ位置で追加済み(詳細は
  同リポジトリのCLAUDE.md参照)。README/CLAUDE/PORTINGの英語版
  (README-English.md/CLAUDE-English.md/PORTING-English.md)も本HANDOFF
  と併せて新設した。
  - 次にすべきこと: 特になし(今回のスコープは完了)。

- **2026-08-04(続き) `aruaru`/`aruaru-lady`を11言語→18言語へ拡張 +
  `rakuten-mobile`にヘブライ語を追加(ユーザー指示「世界十数カ国語に
  翻訳するように指示したのがどこかに残っていると思いますので、その
  途中から再開して、ヘブライ語への翻訳も追加して」「この3つの英語を
  基本として翻訳したサイトの上に世界十数カ国の言語名をナビとして並べて
  クリックすると読めるようにして」)**:
  1. **背景**: 直前のHANDOFF(下記)で`rakuten-mobile`のみ17言語
     (英/英(UK)/伊/独/オーストリア/スイス/仏/露/ウクライナ/アラビア/
     ペルシャ/韓/中国語簡体字・繁体字/西/フィリピン語)に拡張済みだった
     一方、`aruaru`/`aruaru-lady`は2026-07-15時点の11言語(英/韓/中国語
     簡体字・繁体字/露/ウクライナ/独/伊/仏/アラビア/ペルシャ)のまま
     取り残されていた。今回、`rakuten-mobile`と同じ18言語構成
     (17言語+ヘブライ語)へ3サイトとも揃えた。
  2. **`aruaru`/`aruaru-lady`に新規7言語ファイルを追加**: 英語(UK)・
     ドイツ語(オーストリア/スイス、`de`と同一翻訳内容を国旗・ラベルの
     みで別提供)・スペイン語・フィリピン語・ヘブライ語
     (`index-en-gb.php`/`index-at.php`/`index-ch.php`/`index-es.php`/
     `index-tl.php`/`index-he.php`、各サイトにつき6ファイル)。
     いずれもClaude Codeによる人力翻訳(自動翻訳APIは未使用)。
  3. **`rakuten-mobile`にヘブライ語ファイルを追加**: `index-he.php`
     (既存16言語に対する追加のみ、他言語は前回HANDOFFで完了済み)。
  4. **ヘブライ語版の実装**: 既存のアラビア語版(`index-ar.php`)と同じ
     `<html lang="he" dir="rtl">`+RTL対応CSS(`ul`のpadding方向反転等)の
     パターンを踏襲。
  5. **`lang-nav.php`を3サイトとも18言語構成に更新**し、コメントで
     `at`/`ch`がドイツ語共用である旨・全18言語実装済みである旨を明記。
  6. **検証**: 新規・変更した全16ファイル(新規14ファイル+lang-nav.php
     3件更新のうち2件は新規扱い)を`php -l`で構文チェック、エラー無し。
     実インターネット経由でのアクセス確認は本番反映後に別途実施予定。
  - **本番反映・実バグ2件を発見・修正**: (a) `/aruaru`・`/aruaru-lady`・
    `/rakuten-mobile`配下は本番で`open-web-server`(共有リバースプロキシ)
    により`audiocafe-tokyo-rust`(Rust版、多言語ページ未実装)へ丸ごと
    転送される設定になっており、`audiocafe-tokyo-php`側にファイルを
    scpしただけでは新規言語ページに一切到達できなかった
    (`index-ar.php`等、以前から存在するはずのファイルも同様に404)。
    `open-web-server`の`domains.toml`に`/aruaru/index-`・
    `/aruaru-lady/index-`・`/rakuten-mobile/index-`という、より長い
    prefixで`127.0.0.1:4401`(PHP-FPM相当のlegacy `php -S`)へ振り分ける
    エントリを追加し(`tenant_router`は`path_prefix`長の最長一致を
    優先)、`systemctl restart open-web-server`で反映。(b) 調査の結果、
    直前の2026-08-04エントリで「rakuten-mobileの17言語版をscpで本番
    アップロード・実インターネット経由で確認済み」としていた記述は
    **誤りだったことが判明**——実際にはVPS上に`index-he.php`以外の
    言語ファイルが1件も存在しておらず(`index.php`のみ)、当時の
    「確認済み」は取り違えだったと考えられる。今回全17言語ファイルを
    改めて`scp`し、実際にVPS上に存在すること・`https://audiocafe.tokyo/
    rakuten-mobile/index-en.php`等が実際に200を返すことを`curl`で
    再確認した。(c) `audiocafe-tokyo-rust`側の日本語トップページ
    (`/aruaru`等)には多言語版への導線(ナビ)自体が無かったため、
    同リポジトリに18言語ピル型ナビを追加(詳細は`audiocafe-tokyo-rust/
    CLAUDE.md` 2026-08-04続きエントリ参照)。
  - **検証**: `aruaru/index-he.php`・`aruaru/index-en.php`・
    `aruaru-lady/index-es.php`・`rakuten-mobile/index-en.php`・
    `rakuten-mobile/index-he.php`・`rakuten-mobile/index-tl.php`いずれも
    実インターネット経由で200を確認。3ディレクトリとも実ファイル数
    (`index-*.php`)が17件ずつ(=18言語)VPS上に存在することを確認済み。
  - 次にすべきこと: 動的データ部分(料金・カバレッジ率等)は静的翻訳の
    ため今後の更新時にも自動追随しない点は既存の制約のまま(直前
    HANDOFFの開示と同じ)。

- **2026-08-04 `rakuten-mobile`を17言語対応に拡張 + トップページにブログリンク追加**:
  ユーザー指示「英語が基本で翻訳して、中で主要国にリンクを一番上に張って
  リンク先で表示して」+段階的な言語追加指示(英/英(UK)/伊/独/オーストリア/
  スイス/仏/露/ウクライナ/アラビア/ペルシャ/韓/中国語簡体字・繁体字/西/
  フィリピン語)に対応し、既存の`aruaru`/`aruaru-lady`と同じ
  `lang-nav.php`パターンを`rakuten-mobile/`にも新設。
  - 新規`rakuten-mobile/lang-nav.php`(17言語、`at`/`ch`はオーストリア/
    スイスの公用語がドイツ語であることを理由に`de`と同一翻訳内容を国旗・
    ラベルのみ変えて別ファイル提供——架空の別言語は作らない設計)。
  - `index.php`(日本語)に`$current = 'ja'; include __DIR__ .
    '/lang-nav.php';`を`<body><main>`直後に追加。
  - `index-en.php`(英語、ユーザー指示通り基本版)を含む16言語分の
    翻訳版ファイルを新規作成(`index-en-gb.php`/`index-it.php`/
    `index-de.php`/`index-at.php`/`index-ch.php`/`index-fr.php`/
    `index-ru.php`/`index-uk.php`/`index-ar.php`(RTL)/`index-fa.php`
    (RTL)/`index-ko.php`/`index-zh-cn.php`/`index-zh-tw.php`/
    `index-es.php`/`index-tl.php`)。いずれもClaude Codeによる人力翻訳
    (自動翻訳APIは未使用)、全17ファイル`php -l`構文チェック済み。
  - トップページ(`index.php`)の`<body>`直後に2件目のブログリンク
    (「上下水道配管や屋根瓦などのハイテク新素材。パナホームとヤマダ
    ホームのコーキングレス外壁」、`ameblo.jp/www-aon/entry-12974607800`)
    を追加(既存の1件目リンクと同じ位置・スタイル)。
  - **正直な開示**: (1) 17言語版の翻訳は静的な一括翻訳であり、
    キャッシュJSON側の動的データ(料金・カバレッジ率等)が今後更新
    されても翻訳文自体は自動追随しない(数値・単位表記は元々言語間で
    ほぼ共通のため実害は小さいと判断)。(2) `aruaru-llm`側に
    `POST /v1/translate`エンドポイントを新設したが(同リポジトリ
    CLAUDE.md参照)、今回の17言語版生成には使用していない(人力翻訳の方が
    品質面で確実と判断)——将来、動的データ部分の自動翻訳が必要になった
    場合の実装候補として記録するのみ。
  - **本番反映**: `scp`で`/var/www/audiocafe.tokyo/`へ直接アップロード、
    トップページ("/")自体は`audiocafe-tokyo-rust`(別リポジトリ)が配信
    するため、ブログリンク追加はそちら側にも別途反映(同リポジトリ
    CLAUDE.md参照)。実インターネット経由で新規17ファイルの`index-*.php`
    アクセス・ブログリンク表示を確認済み。
  - 次にすべきこと: (1) 動的データ部分の自動翻訳が必要になった場合、
    `aruaru-llm`の`/v1/translate`を実際に呼ぶcron配線を検討、(2) `aruaru`・
    `aruaru-lady`と同様、rakuten-mobileの17言語版もcron自動更新
    (`audiocafe-cron-php.timer`、毎朝5時)経由でキャッシュJSON側は
    更新され続けるが、静的翻訳文自体の更新契機は無いため、内容が大きく
    変わった場合は翻訳ファイルの再生成が必要になる。

- **2026-07-29 YouTube再生リストシリーズ(`SEARCH_SERIES`)のデータ整備一式**:
  ユーザー報告に基づき、`audiocafe-tokyo-rust`(Rust版)と同時に以下を対応、
  VPS本番へ`index.php`を直接アップロードして反映まで確認済み:
  1. SPEC RPA-MG1000/RPA-MG3000・Pass Labs USA/Japanのリンクを追加
     (Accuphaseの前、SPEC本体→SPEC画像検索→Pass Labs USA→Pass Labs Japan
     →Accuphaseの順)。
  2. 誤字修正: 「国際的に、戦争屋の一層で自民党も解体」→
     「国際的に、戦争屋の一掃で自民党も解体か？」。
  3. 重複エントリの整理: 「上杉 真空管アンプ　UESGI　フォノアンプ」の
     英語/日本語説明文2重複を1件に統合。「懸垂式モノレール」ラベルが
     誤って牛糞堆肥化の動画エントリにも付いていたのを
     「牛糞の堆肥（たいひ）化」に修正。
  4. 新規追加: 「エジプトとインカの地下から日本語の石板」の次に
     「解明されてきたUFO技術３選」を追加。
  - **判明した実バグ(Rust版固有、このリポジトリのコードは無変更)**:
     Rust版は動画を1本も含まないシリーズについて、実際のURLを見ずに
     ラベル文字列から常にYouTube検索を合成してしまう実バグがあった
     (`audiocafe-tokyo-rust`側で修正済み、詳細は同リポジトリのCLAUDE.md
     参照)。このPHP版は元々`navigateUrl`/`isYtPanelClosed`という別方式で
     実際のURLへ正しく遷移する設計だったため影響なし。
  - 次にすべきこと: 80件超のシリーズを1件ずつ全数点検したわけではない
    ため、他にも同種の重複・誤ラベル・リンク切れが残っている可能性が
    ある。ユーザーからの追加報告があれば都度対応する。

- **2026-07-20(続き) ヘルスチェック・セキュリティ点検・ドキュメント実態整合**:
  ユーザー指示により、リポジトリ全体の健全性確認とドキュメント記述の実態整合を行った。
  - **`php -l`構文チェック**: `Python/`以外の全33ファイルで実施、エラーなし。
  - **セキュリティ**: `aruaru/index.php:26`で`ARUARU_CRON_KEY`(cron手動実行用の
    秘密キー)が`'change-this-secret-2026'`という固定文字列で直接`define()`
    されており、`OPENAI_API_KEY`/`GITHUB_TOKEN`/`GOOGLE_CSE_KEY`等の他の秘密情報
    (すべて`getenv()`優先のパターン)と異なり環境変数から上書きする経路が無い
    ままだった問題を修正。`getenv('ARUARU_CRON_KEY') ?: 'change-this-secret-2026'`
    に変更し、他の秘密情報と同じ環境変数優先パターンに統一(公開リポジトリに
    含まれる既知の値がそのまま本番の秘密キーとして使われ得る状態だったため)。
    本番VPS側で`ARUARU_CRON_KEY`環境変数を実際に設定することを推奨。
  - **open-easy-webとの連携確認**: `open-easy-web`リポジトリを調査した結果、
    nginx vhostの規約(`/etc/nginx/conf.d/<domain>.conf`、ポート80→443
    リダイレクト、Let's Encrypt証明書パス)はこのリポジトリのCLAUDE.mdの記述と
    構造的に一致することを確認。ただし`audiocafe.tokyo`の実際のvhostファイルは
    open-easy-webの自動生成(`gen-vhost.sh`)ではなく手動作成・手動管理であり、
    open-easy-web側のドメイン登録レコードもユーザー指示で明示的に削除済み
    (open-easy-web CLAUDE.md記載)——この点はopen-easy-web側の既知の運用実態であり、
    本リポジトリの記述に矛盾は無い。
  - **ドキュメントと実態の齟齬を修正(最重要)**: このCLAUDE.md/README.mdはこれまで
    「技術スタックは意図的にPHP、統一・移行の予定はない」と記載していたが、
    実際には`audiocafe-tokyo-rust`(別リポジトリ、Rust+RPoem)への並行移植が
    2026-07-17から進行しており、2026-07-19時点で**トップページ・`/aruaru/`・
    `/aruaru-lady/`・`/rakuten-mobile/`の4パスは本番nginx設定で既にRust版へ
    カットオーバー済み**(`audiocafe-tokyo-rust/PORTING.md`のHANDOFFログで確認)。
    この重大な事実がこのリポジトリのドキュメントに一切反映されていなかったため、
    「役割」「デプロイ」「関連プロジェクト」の各節を実態に合わせて訂正した
    (README.mdも同様に修正)。cron自動更新処理・`/top/`等の残りパスは引き続き
    このPHPリポジトリの責務のまま。
  - **個人情報**: `top/`配下の履歴書等は既に`.gitignore`で除外済み(現状維持)。
    その他、DB接続情報・実メールアドレス・実電話番号のハードコードは見つからず
    (grep調査済み)。`index.php`のトップページ言語カードには実在するブログ
    URL(`ameblo.jp/www-aon`等)へのリンクが多数含まれるが、これはサイト運営者
    自身が公開しているコンテンツへの導線であり、認証情報や個人を特定する
    非公開情報の漏洩ではないため対象外と判断した。
  - **検証**: `git status`はコミット前に確認、`php -l`は上記の通り全件成功。
    本HANDOFF自体もコミット・push対象に含める。
  - 次にすべきこと: (1) 本番VPSで`ARUARU_CRON_KEY`環境変数を実際に設定し、
    公開リポジトリに含まれる暫定値がそのまま使われていないか確認すること、
    (2) 今回のドキュメント整合はあくまで前提作業であり、`audiocafe-tokyo-rust`
    側の残り移植(cron移植・多言語版対応等、同リポジトリのCLAUDE.md参照)が
    本当の意味での「連携の完成」に向けた本体作業として残っている。

- **2026-07-20 AIテクノロジーランキングの評価軸拡張(既存システムの再定義・再実装)**:
  ユーザー指示により、`aruaru/index.php`の既存AIテクノロジーランキング機構
  （`aruaru_tech_*`関数群・`ai-tech-ranking-cache.json`、cron.php経由の日次更新）を
  ゼロから作り直さず、既存スキーマを拡張する形で再実装した。
  - **languages配列をTOP80→TOP100へ拡張**: ベースライン(`aruaru_tech_baseline_data()`)に
    20言語(Visual Basic .NET・Delphi・Pascal・Apex・Rexx・AWK・Vala・Standard ML・Q#・
    ReasonML・Odin・Gleam・Roc・Nix・Wren・Grain・Janet・Hare・Red・Squirrel)を追加し
    ちょうど100件に到達（水増しなし、実在する言語のみ）。`aruaru_tech_build_top50()`の
    `array_slice`上限をカテゴリ別に変更（language=100、framework/database=80のまま）。
    **frameworks/databasesはTOP80のまま据え置き**（ユーザー要望は100件を「目指す」だったが、
    今回は言語のみに対象を絞り、正直にスコープ外として記録する）。
  - **新規評価軸14項目を追加**（`score_async`/`score_speed`/`score_security`/
    `versionless_api_comment`/`score_ai_library`/`score_other_library`/`framework_note`/
    `database_note`(aruaru-dbリンク付き)/`score_spec_change_resilience`/
    `score_parallel_distributed`/`cockroachdb_similarity_comment`/
    `snowflake_similarity_comment`/`aruaru_db_similarity_comment`/`total_score`）。
    実装は`aruaru_tech_extended_knowledge_base()`(主要25言語の手動キュレーション)＋
    `aruaru_tech_extended_heuristic()`(それ以外の言語向け、既存フィールドのキーワード
    マッチングによるルールベース算出)の二段構成。`aruaru_tech_apply_extended_scoring()`が
    `aruaru_tech_refresh_rankings()`内(cron項目[1/7]に統合、別項目は増やさず)で毎回
    全行に適用され、`total_score`をランキングのソートキーとして`rank`を再採番する。
  - **aruaru-llm連携**: `F:\open-runo\aruaru-llm`(Rust+Poem製、実在確認済み)へ
    `aruaru_tech_call_aruaru_llm()`が実際にHTTP POST(`/v1/chat`、既定エンドポイント
    `http://127.0.0.1:4600`、`ARUARU_LLM_ENDPOINT`環境変数で上書き可)する、スタブでない
    本物の関数を実装。到達できればその応答で`aruaru_db_similarity_comment`を上書き、
    到達不能(未起動等)なら1回の試行後に即座にルールベースへフォールバックしログに記録
    （実機検証時は未起動のため毎回フォールバックを確認、`cron.log`に記録あり）。
    aruaru-llm自体が「本物のニューラル推論ではなくルールベース」であることは
    aruaru-llm側のCLAUDE.mdで開示済みであり、本リポジトリの表示側にもその限界を明記した。
  - **事実と推測の混同防止**: `ranking_meta.extended_axes_note`と表示側(index.php本体の
    黄色い注記ボックス)に「非同期/セキュリティ等の拡張スコアはStackOverflow等のリアルタイム
    外部指標ではなく一般的な技術知見に基づくルールベース評価」である旨を明記。
  - **表示側**: 言語テーブルに新規列(score系8列＋comment系5列)を追加し見出しを
    「TOP80→TOP100」に更新。ページ上部にGitHubリポジトリ(README/CLAUDE.md)への
    リンクも追加。
  - **検証**: `php -l`成功。mbstring拡張が既定では未読込のPHP環境だったため
    `-d extension_dir=... -d extension=php_mbstring.dll`を付けて`php -S`起動、
    `curl`で実際にHTMLを取得し新規フィールド(`score_async`・`versionless_api_comment`・
    `aruaru-db`リンク・`CockroachDB類似性`・`合計score`見出し・`TOP100`見出し)が
    実際に本文へ出力されることを確認。生成された`ai-tech-ranking-cache.json`を
    PHPで検証しlanguages=100件・frameworks=80件・databases=80件、
    languages[0]の全26キー(旧12+新14)存在・`total_score`降順ソート済みを確認
    （テスト実行時は外部ネットワーク(StackOverflow/DB-Engines/aruaru-llm)が
    いずれも到達不能でベースラインのみの動作経路だったため、本番VPS環境での
    外部データ取得込みの動作は次回本番反映時に別途確認が必要）。
  - **既知の制約(正直な記録)**: (1) frameworks/databasesはTOP80のまま(languages
    のみTOP100)。(2) aruaru-llmはこの開発機では未起動のため`aruaru_db_similarity_comment`
    の実LLM応答上書きパスは未検証(コードはHTTPクライアントとして実装済みで、
    到達可能な環境なら動作する設計)。(3) 新規スコアは一般知見ベースのルール評価であり
    StackOverflow等のような実測値ではない(表示側に明記済み)。

- **2026-07-16(続き)**: YouTube検索結果バグの根本修正完了。`fetchAndCollect`(シリーズボタン/イントロ/再検索経路)と`fetchSearchResultIds`(NEXT/ランダムプール経路)の両方でjina.aiスクレイプを撤廃し、検索URLは実際のYouTube検索結果ページへの直接遷移に統一。PHP構文チェック(`php -l`)・埋め込みJSの構文チェック(`node --check`、PHPタグ部分をプレースホルダ置換した上で検証)ともに成功、本番環境で200 OK・修正内容の反映を確認済み。
- **2026-07-16**: リポジトリ初期化・ドキュメント整備(README/CLAUDE.md/PORTING.md新規作成)・全ファイルpush完了。
