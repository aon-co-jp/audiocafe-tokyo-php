<?php
/**
 * audiocafe.tokyo/aruaru/index.php — 1ファイル統合版
 * 依存ファイルをすべてインライン化
 * 生成: 2026-05-17
 */
declare(strict_types=1);

/* ===== タイムゾーンを最優先で固定（cron・Web表示の時刻を JST に統一） ===== */
if (function_exists('date_default_timezone_set')) {
    date_default_timezone_set('Asia/Tokyo');
}

/* =========================================================
   ★ Cron 起動判定（LOLIPOP 対応・堅牢版）
   LOLIPOP の cron は PHP を CLI ではなく CGI(cgi-fcgi) で起動する場合があり、
   その際 PHP_SAPI === 'cli' が false になって従来の cron ブロックが
   丸ごとスキップされ「毎朝5時に更新されない」不具合になっていました。
   - CLI / CGI の両方を許可
   - 引数は $argv / $_SERVER['argv'] の両経路で受け取る
   - 引数が渡せない「PHPファイル直接指定」型cronでも、HTTP経由でなければ実行
   - Web からの誤爆を防ぐためクエリ経由はシークレットキー必須
========================================================= */
if (!defined('ARUARU_CRON_KEY')) {
    // 環境変数 ARUARU_CRON_KEY を優先（本番はサーバー環境変数で上書き必須）。
    // 未設定時のみ暫定値にフォールバックするが、これは公開リポジトリに含まれる
    // 既知の値のため、本番運用では必ず環境変数で上書きすること。
    define('ARUARU_CRON_KEY', getenv('ARUARU_CRON_KEY') ?: 'change-this-secret-2026');
}
if (!function_exists('aruaru_is_cron_request')) {
    function aruaru_is_cron_request(string $flag = ''): bool {
        $sapi      = PHP_SAPI;
        $is_cliish = ($sapi === 'cli' || $sapi === 'cli-server' || $sapi === 'cgi' || $sapi === 'cgi-fcgi' || $sapi === 'phpdbg');
        $args = [];
        if (isset($GLOBALS['argv']) && is_array($GLOBALS['argv'])) $args = $GLOBALS['argv'];
        elseif (isset($_SERVER['argv']) && is_array($_SERVER['argv'])) $args = $_SERVER['argv'];
        // HTTPメソッド・ホスト・クエリすべて無い＝実質コマンドライン起動とみなす
        $no_http = empty($_SERVER['REQUEST_METHOD']) && empty($_SERVER['HTTP_HOST']) && empty($_GET);

        if ($flag !== '') {
            // 特定フラグ指定モード（--cron-intl 等）。CLI/CGIで該当フラグがある時のみ。
            if ($is_cliish && in_array($flag, $args, true)) return true;
            return false;
        }
        // --cron-all 相当（統合実行）。フラグ明示 or 引数なしコマンドライン起動 で許可。
        if ($is_cliish && (in_array('--cron-all', $args, true) || $no_http)) {
            return true;
        }
        // クエリ経由（ブラウザ／wget での手動・外部cron用）。キー必須。
        if (!empty($_GET['cron']) && ($_GET['cron'] === 'all' || $_GET['cron'] === '1')
            && isset($_GET['key']) && hash_equals(ARUARU_CRON_KEY, (string)$_GET['key'])) {
            return true;
        }
        return false;
    }
}


/* ===== BEGIN: aruaru-tech-baseline.php ===== */

/**
 * TOP50 baseline seed (手動編集可). Cron同期時に外部ランキングとマージされます。
 *
 * ★ 固定値（Cron・AI自動更新で上書き禁止）
 *   - JavaScript : maintenance="低い" / dev_scale="小規模" / ai_comment="フロント・バック両対応、なるべく早くTypeScriptに乗り換えて、将来、より大規模なシステムのメンテナンス対応を可能にした方が良いです。"
 *   - Ruby       : traits="Rails・スタートアップ文化・生産性重視。最初の開発者以外はほとんどメンテナンス出来ない最低最悪な言語。採用した企業は倒産しやすいです。"
 */
function aruaru_tech_baseline_data(): array {
    $languages = [
        ["rank"=> 1, "name"=>"TypeScript",   "team_dev"=>"非常に高い", "maintenance"=>"型安全・大規模向け",   "beginner"=>"中級向け",     "speed"=>"高速",   "dev_scale"=>"超大規模", "traits"=>"Web/Node・型安全API・巨大フロント", "oss_note"=>"言語/compilerはOSS中心。IDEやクラウドはOSS＋商用が併存しやすい", "async_support"=>"async/await・Promiseが標準", "throughput_100k"=>"IO中心＋水平スケールで10万件/秒級を狙える構成が多い", "memory"=>"中程度", "ai_comment"=>"Next.js・React・Node.jsとの相性抜群"],
        ["rank"=> 2, "name"=>"Python",        "team_dev"=>"高い",       "maintenance"=>"読みやすい",           "beginner"=>"初心者向け",   "speed"=>"普通",   "dev_scale"=>"大規模",   "traits"=>"AI/ML・自動化・クラウドSDKが豊富", "oss_note"=>"CPython等OSS。一部ディストリやサポートは商用", "async_support"=>"asyncio・非同期IOが標準ライブラリに統合", "throughput_100k"=>"async＋ワーカー分割で10万件/秒級に届くケースあり", "memory"=>"大容量必要", "ai_comment"=>"AI・ML・自動化で世界的人気"],
        ["rank"=> 3, "name"=>"JavaScript",    "team_dev"=>"非常に高い", "maintenance"=>"低い",                 "beginner"=>"初心者向け",   "speed"=>"高速",   "dev_scale"=>"小規模", "traits"=>"ブラウザ標準・Nodeでサーバまで一気通貫", "oss_note"=>"エンジン・仕様はOSS寄り。ホスティングは商用併用が一般的", "async_support"=>"Promise・イベントループが前提設計", "throughput_100k"=>"Node等でIO密集ワークロードは10万件/秒級を狙いやすい", "memory"=>"中程度", "ai_comment"=>"フロント・バック両対応、なるべく早くTypeScriptに乗り換えて、将来、より大規模なシステムのメンテナンス対応を可能にした方が良いです。"],
        ["rank"=> 4, "name"=>"Go",             "team_dev"=>"非常に高い", "maintenance"=>"保守しやすい",         "beginner"=>"比較的簡単",   "speed"=>"高速",   "dev_scale"=>"大規模",   "traits"=>"クラウド・マイクロサービス・軽量並行", "oss_note"=>"Google発のOSS。企業向けディストリは別途あり", "async_support"=>"goroutine・channelで並行が言語コア", "throughput_100k"=>"単一バイナリでも高スループット。分散で10万件/秒級が現実的", "memory"=>"小容量", "ai_comment"=>"Docker・K8s・API開発で人気急上昇"],
        ["rank"=> 5, "name"=>"Rust",           "team_dev"=>"高い",       "maintenance"=>"厳格",                 "beginner"=>"難しい",       "speed"=>"超高速", "dev_scale"=>"超大規模", "traits"=>"メモリ安全・WebAssembly・システム隣接", "oss_note"=>"Rust本体はOSS。ツールチェーンも基本OSS", "async_support"=>"async/await（Tokio等）で非同期が定番", "throughput_100k"=>"ゼロコスト抽象で高RPS。サービス分割で10万件/秒級を狙える", "memory"=>"中程度（ランタイム小）", "ai_comment"=>"安全性と速度を両立、WebAssembly対応"],
        ["rank"=> 6, "name"=>"Java",           "team_dev"=>"非常に高い", "maintenance"=>"高い",                 "beginner"=>"中級向け",     "speed"=>"高速",   "dev_scale"=>"超大規模", "traits"=>"エンタープライズ・JVM・長期保守", "oss_note"=>"OpenJDKはOSS。Oracle JDK等は商用ライセンス併存", "async_support"=>"CompletableFuture・Reactiveで非同期が定着", "throughput_100k"=>"JVMチューニング＋水平スケールで10万件/秒級が多い", "memory"=>"大容量必要", "ai_comment"=>"エンタープライズ定番、Spring Boot人気"],
        ["rank"=> 7, "name"=>"Kotlin",         "team_dev"=>"高い",       "maintenance"=>"高い",                 "beginner"=>"中級向け",     "speed"=>"高速",   "dev_scale"=>"大規模",   "traits"=>"Android・JVMサーバ・Coroutine", "oss_note"=>"KotlinはOSS。IDE連携で一部商用サポート", "async_support"=>"Coroutineで非同期が第一級", "throughput_100k"=>"サーバ・Androidともスループットは構成次第で10万件/秒級も", "memory"=>"中程度", "ai_comment"=>"Android開発の標準言語、Java互換"],
        ["rank"=> 8, "name"=>"Swift",          "team_dev"=>"高い",       "maintenance"=>"高い",                 "beginner"=>"中級向け",     "speed"=>"高速",   "dev_scale"=>"大規模",   "traits"=>"Apple系クライアント・サーバ(Swift)拡大", "oss_note"=>"Swiftオープンソース。配布はAppleエコシステム中心", "async_support"=>"async/awaitが標準（Swift Concurrency）", "throughput_100k"=>"主用途はUX重視。バックエンドなら構成次第で高RPS", "memory"=>"中程度", "ai_comment"=>"iOS・macOS開発の標準言語"],
        ["rank"=> 9, "name"=>"C#",             "team_dev"=>"非常に高い", "maintenance"=>"高い",                 "beginner"=>"中級向け",     "speed"=>"高速",   "dev_scale"=>"超大規模", "traits"=>".NET・Unity・ゲーム/業務の両輪", "oss_note"=>".NETはOSS。Visual Studio等は商用版あり", "async_support"=>"async/awaitが標準でサーバ非同期に強い", "throughput_100k"=>"ASP.NET等でIO密集なら10万件/秒級を狙える", "memory"=>"大容量寄り", "ai_comment"=>"Microsoft/.NET/Unity/Blazer全般で強い"],
        ["rank"=>10, "name"=>"PHP",             "team_dev"=>"高い",       "maintenance"=>"普通",                 "beginner"=>"初心者向け",   "speed"=>"普通",   "dev_scale"=>"大規模",   "traits"=>"LAMP・WordPress・Web向け実績最大級", "oss_note"=>"PHP本体はOSS。ホスティング・FW商用サポート併存", "async_support"=>"Swoole/ReactPHP等で非同期拡張（標準は同期寄り）", "throughput_100k"=>"Opcache＋非同期FWで10万件/秒級に近づく構成あり", "memory"=>"中程度", "ai_comment"=>"Web全体の75%以上、Laravel人気"],
        ["rank"=>11, "name"=>"Ruby",            "team_dev"=>"低い",       "maintenance"=>"低い",                 "beginner"=>"比較的簡単",   "speed"=>"低速",   "dev_scale"=>"小規模",   "traits"=>"Rails・スタートアップ文化・生産性重視。最初の開発者以外はほとんどメンテナンス出来ない最低最悪な言語。採用した企業は倒産しやすいです。", "oss_note"=>"RubyはOSS。サポートやマネージドは商用併用", "async_support"=>"async gem等で拡張（標準は同期中心）", "throughput_100k"=>"IOワーカー＋ジョブキューで高RPSは可能だが最優先用途ではない", "memory"=>"大容量必要", "ai_comment"=>"Rails文化・スタートアップに人気"],
        ["rank"=>12, "name"=>"Scala",           "team_dev"=>"高い",       "maintenance"=>"複雑",                 "beginner"=>"難しい",       "speed"=>"高速",   "dev_scale"=>"超大規模", "traits"=>"JVM・Spark・分散データ処理", "oss_note"=>"ScalaはOSS。ビルド/IDEはOSS＋商用混在", "async_support"=>"Future・Akka等で非同期・分散が強み", "throughput_100k"=>"分散ストリームで10万件/秒級を狙える用途に強い", "memory"=>"大容量必要", "ai_comment"=>"Spark・ビッグデータ処理で活躍"],
        ["rank"=>13, "name"=>"Dart",            "team_dev"=>"高い",       "maintenance"=>"高い",                 "beginner"=>"比較的簡単",   "speed"=>"高速",   "dev_scale"=>"大規模",   "traits"=>"Flutter・クロスプラットフォームUI", "oss_note"=>"Dart/FlutterはOSS。ストア課金は別レイヤー", "async_support"=>"async/awaitが言語標準", "throughput_100k"=>"主用途はクライアント。サーバ用途は構成次第", "memory"=>"中程度", "ai_comment"=>"Flutter唯一の言語、モバイル・Web両対応"],
        ["rank"=>14, "name"=>"C++",             "team_dev"=>"普通",       "maintenance"=>"難しい",               "beginner"=>"難しい",       "speed"=>"超高速", "dev_scale"=>"超大規模", "traits"=>"ゲーム・HFT・高性能サーバ・組込隣接", "oss_note"=>"コンパイラはGCC/Clang等OSS。商用IDE・ライブラリ併存", "async_support"=>"標準ライブラリに非同期基盤（用途はライブラリ依存）", "throughput_100k"=>"最適化次第で極めて高スループット。10万件/秒級も十分あり得る", "memory"=>"中程度", "ai_comment"=>"ゲーム・組込・高性能システムの定番"],
        ["rank"=>15, "name"=>"R",               "team_dev"=>"普通",       "maintenance"=>"普通",                 "beginner"=>"中級向け",     "speed"=>"普通",   "dev_scale"=>"中規模",   "traits"=>"統計・研究・可視化・データ分析", "oss_note"=>"R本体はOSS。RStudio等は商用版あり", "async_support"=>"並列パッケージで拡張（対話分析が主）", "throughput_100k"=>"ベクトル化・外部エンジン次第。リアルタイム最大RPSは主目的外", "memory"=>"大容量寄り", "ai_comment"=>"統計・データサイエンス分野で特化"],
        ["rank"=>16, "name"=>"Elixir",          "team_dev"=>"高い",       "maintenance"=>"高い",                 "beginner"=>"難しい",       "speed"=>"高速",   "dev_scale"=>"大規模",   "traits"=>"OTP・高並列・耐障害・Soft real-time", "oss_note"=>"Elixir/Erlang VMはOSS", "async_support"=>"軽量プロセス＋メッセージングで非同期が前提", "throughput_100k"=>"大量メッセージ処理で10万件/秒級を狙える設計が得意", "memory"=>"中程度", "ai_comment"=>"Phoenix・高並列・耐障害性で注目"],
        ["rank"=>17, "name"=>"Haskell",         "team_dev"=>"普通",       "maintenance"=>"型安全",               "beginner"=>"非常に難しい", "speed"=>"高速",   "dev_scale"=>"中規模",   "traits"=>"純粋関数型・金融・学術", "oss_note"=>"GHC等OSS。一部商用ツールチェーンも", "async_support"=>"STM/asyncライブラリで対応（スタイルは関数型）", "throughput_100k"=>"用途特化で高スループットも。汎用API最大RPSは設計次第", "memory"=>"中程度", "ai_comment"=>"純粋関数型、金融・学術系で使用"],
        ["rank"=>18, "name"=>"Lua",             "team_dev"=>"普通",       "maintenance"=>"軽量",                 "beginner"=>"比較的簡単",   "speed"=>"高速",   "dev_scale"=>"組み込み", "traits"=>"ゲーム埋め込み・設定スクリプト・軽量", "oss_note"=>"LuaはOSS", "async_support"=>"コルーチンで協調的マルチタスク", "throughput_100k"=>"埋め込み用途中心。最大スループットは主目的ではない", "memory"=>"極小", "ai_comment"=>"ゲーム・組込スクリプトで定番"],
        ["rank"=>19, "name"=>"C",               "team_dev"=>"普通",       "maintenance"=>"難しい",               "beginner"=>"難しい",       "speed"=>"超高速", "dev_scale"=>"組み込み", "traits"=>"OS・ドライバ・極小ランタイム", "oss_note"=>"コンパイラはGCC等OSS。商用RTOS/ツール併存", "async_support"=>"POSIX等でイベント駆動を自前実装", "throughput_100k"=>"最適化で極限スループット。10万件/秒級も実装次第で可能", "memory"=>"極小", "ai_comment"=>"OS・組込・低レイヤーの根幹言語"],
        ["rank"=>20, "name"=>"Perl",            "team_dev"=>"普通",       "maintenance"=>"難しい",               "beginner"=>"難しい",       "speed"=>"普通",   "dev_scale"=>"中規模",   "traits"=>"テキスト処理・レガシーWeb・運用", "oss_note"=>"PerlはOSS", "async_support"=>"AnyEvent等で拡張", "throughput_100k"=>"バッチ・パイプライン中心。API最大RPSは他言語に譲ることが多い", "memory"=>"小〜中程度", "ai_comment"=>"テキスト処理・レガシーシステムで現役"],
        ["rank"=>21, "name"=>"Objective-C",     "team_dev"=>"普通",       "maintenance"=>"難しい",               "beginner"=>"難しい",       "speed"=>"高速",   "dev_scale"=>"中規模",   "traits"=>"iOS/macレガシー・ObjCランタイム", "oss_note"=>"LLVM/Clang等OSS。Appleプラットフォームは商用エコシステム", "async_support"=>"Grand Central Dispatch等でブロック並行", "throughput_100k"=>"主にクライアント。サーバ用途は限定的", "memory"=>"中程度", "ai_comment"=>"iOS旧来コード維持、Swift移行中"],
        ["rank"=>22, "name"=>"Groovy",          "team_dev"=>"高い",       "maintenance"=>"普通",                 "beginner"=>"中級向け",     "speed"=>"普通",   "dev_scale"=>"中規模",   "traits"=>"JVM・Gradle・スクリプト・Spring連携", "oss_note"=>"GroovyはOSS", "async_support"=>"GPars等で拡張（同期が主）", "throughput_100k"=>"ビルド/バッチ用途中心。API最大RPSは稀", "memory"=>"大容量必要", "ai_comment"=>"Gradle・Jenkins・JVM連携で現役"],
        ["rank"=>23, "name"=>"Clojure",         "team_dev"=>"普通",       "maintenance"=>"高い",                 "beginner"=>"難しい",       "speed"=>"高速",   "dev_scale"=>"大規模",   "traits"=>"JVM・イミュータブル・並行プリミティブ", "oss_note"=>"ClojureはOSS", "async_support"=>"core.async等でチャネル型非同期", "throughput_100k"=>"JVM上でスループットは構成次第。分散で高RPS", "memory"=>"大容量必要", "ai_comment"=>"Lisp系関数型・金融系で使用"],
        ["rank"=>24, "name"=>"F#",              "team_dev"=>"普通",       "maintenance"=>"型安全",               "beginner"=>"難しい",       "speed"=>"高速",   "dev_scale"=>"大規模",   "traits"=>".NET・データ・関数型ハイブリッド", "oss_note"=>"F#はOSS（.NET基盤）", "async_support"=>"async/await・タスクが標準", "throughput_100k"=>".NETスタック次第で10万件/秒級を狙える", "memory"=>"大容量寄り", "ai_comment"=>"Microsoft製関数型・データ分析向け"],
        ["rank"=>25, "name"=>"Julia",           "team_dev"=>"普通",       "maintenance"=>"普通",                 "beginner"=>"中級向け",     "speed"=>"超高速", "dev_scale"=>"中規模",   "traits"=>"科学技術計算・GPU・数値", "oss_note"=>"JuliaはOSS", "async_support"=>"並列・分散パッケージで拡張", "throughput_100k"=>"数値カーネルは高速。IO APIの最大RPSは用途次第", "memory"=>"大容量寄り", "ai_comment"=>"数値計算・科学計算でPython+C速度"],
        ["rank"=>26, "name"=>"Zig",             "team_dev"=>"普通",       "maintenance"=>"高い",                 "beginner"=>"難しい",       "speed"=>"超高速", "dev_scale"=>"中規模",   "traits"=>"C代替・ビルド統合・明示的メモリ", "oss_note"=>"ZigはOSS", "async_support"=>"async/await言語機能あり（標準ライブラリ発展中）", "throughput_100k"=>"システム用途で高スループットを狙いやすい", "memory"=>"小容量", "ai_comment"=>"C代替候補・次世代システム言語"],
        ["rank"=>27, "name"=>"Nim",             "team_dev"=>"低い",       "maintenance"=>"普通",                 "beginner"=>"中級向け",     "speed"=>"超高速", "dev_scale"=>"中規模",   "traits"=>"Python風構文・メタプロ・コンパイル", "oss_note"=>"NimはOSS", "async_support"=>"asyncdispatch等で非同期", "throughput_100k"=>"ネイティブ性能。サービス用途はニッチ", "memory"=>"小容量", "ai_comment"=>"Python風構文で高速、マイナー言語"],
        ["rank"=>28, "name"=>"Crystal",         "team_dev"=>"低い",       "maintenance"=>"高い",                 "beginner"=>"中級向け",     "speed"=>"超高速", "dev_scale"=>"中規模",   "traits"=>"Ruby風・静的型・LLVM", "oss_note"=>"CrystalはOSS", "async_support"=>"Fiberで協調的マルチタスク", "throughput_100k"=>"単体でも高RPS可能だがエコシステムは小さめ", "memory"=>"中程度", "ai_comment"=>"Ruby風構文・静的型付・高速実行"],
        ["rank"=>29, "name"=>"OCaml",           "team_dev"=>"低い",       "maintenance"=>"高い",                 "beginner"=>"難しい",       "speed"=>"高速",   "dev_scale"=>"中規模",   "traits"=>"関数型・型推論・金融系", "oss_note"=>"OCamlはOSS", "async_support"=>"Lwt/Async等で非同期", "throughput_100k"=>"用途特化で高効率。汎用Web最大RPSは設計次第", "memory"=>"小〜中程度", "ai_comment"=>"Meta・Jane Street等で使用の関数型"],
        ["rank"=>30, "name"=>"Erlang",          "team_dev"=>"普通",       "maintenance"=>"高い",                 "beginner"=>"難しい",       "speed"=>"高速",   "dev_scale"=>"大規模",   "traits"=>"OTP・通信・耐障害・テレコム起源", "oss_note"=>"Erlang/OTPはOSS", "async_support"=>"軽量プロセスが前提の非同期モデル", "throughput_100k"=>"メッセージ大量処理で10万件/秒級を狙える", "memory"=>"中程度", "ai_comment"=>"高耐障害・通信系・ElixirのベースVM"],
        ["rank"=>31, "name"=>"Assembly",        "team_dev"=>"低い",       "maintenance"=>"非常に難しい",         "beginner"=>"非常に難しい", "speed"=>"最高速", "dev_scale"=>"組み込み", "traits"=>"CPU直・極小・ドライバ級", "oss_note"=>"アセンブラはOSS/商用ツール混在", "async_support"=>"ハードウェア/割込み駆動（言語機能というより設計）", "throughput_100k"=>"クリティカルループは極限最適化で超高スループットも可能", "memory"=>"極小", "ai_comment"=>"低レイヤー最適化・OS開発の要"],
        ["rank"=>32, "name"=>"MATLAB",          "team_dev"=>"普通",       "maintenance"=>"普通",                 "beginner"=>"中級向け",     "speed"=>"普通",   "dev_scale"=>"中規模",   "traits"=>"数値・制御・産業・研究", "oss_note"=>"本体はプロプライエタリ。一部Toolboxは追加ライセンス", "async_support"=>"Parallel Computing Toolbox等で並列", "throughput_100k"=>"主にオフライン分析。オンライン最大RPSは主目的外", "memory"=>"大容量必要", "ai_comment"=>"工学・理系研究・シミュレーション向け"],
        ["rank"=>33, "name"=>"Fortran",         "team_dev"=>"低い",       "maintenance"=>"難しい",               "beginner"=>"難しい",       "speed"=>"超高速", "dev_scale"=>"超大規模", "traits"=>"数値・スパコン・レガシー高性能", "oss_note"=>"コンパイラにGNU Fortran等OSS・商用混在", "async_support"=>"coarray等で並列（用途は数値中心）", "throughput_100k"=>"ベクトル/並列で極めて高いスループットを出しやすい", "memory"=>"中程度", "ai_comment"=>"スパコン・数値計算で未だ現役"],
        ["rank"=>34, "name"=>"D",               "team_dev"=>"低い",       "maintenance"=>"高い",                 "beginner"=>"難しい",       "speed"=>"超高速", "dev_scale"=>"中規模",   "traits"=>"C++改良・GCあり/なし・システム寄り", "oss_note"=>"D言語はOSS", "async_support"=>"std.experimental等で非同期拡張", "throughput_100k"=>"ネイティブ性能で高RPSも可能", "memory"=>"小〜中程度", "ai_comment"=>"C++改良版・ゲーム開発等で使用"],
        ["rank"=>35, "name"=>"Prolog",          "team_dev"=>"低い",       "maintenance"=>"難しい",               "beginner"=>"非常に難しい", "speed"=>"普通",   "dev_scale"=>"小規模",   "traits"=>"論理・ルール・研究", "oss_note"=>"処理系にSWI-Prolog等OSS", "async_support"=>"エンジン依存（対話・ルール探索が主）", "throughput_100k"=>"推論探索が主。API最大RPSは主目的外", "memory"=>"小容量", "ai_comment"=>"論理型・AI推論・自然言語処理向け"],
        ["rank"=>36, "name"=>"Lisp",            "team_dev"=>"低い",       "maintenance"=>"難しい",               "beginner"=>"非常に難しい", "speed"=>"普通",   "dev_scale"=>"中規模",   "traits"=>"マクロ・DSL・研究", "oss_note"=>"Common Lisp処理系にOSS/商用混在", "async_support"=>"実装依存（古典的には同期中心）", "throughput_100k"=>"用途は多様。汎用高RPSサービスは稀", "memory"=>"中程度", "ai_comment"=>"AI研究・マクロ・歴史的重要言語"],
        ["rank"=>37, "name"=>"Scheme",          "team_dev"=>"低い",       "maintenance"=>"普通",                 "beginner"=>"難しい",       "speed"=>"普通",   "dev_scale"=>"小規模",   "traits"=>"教育・最小核・DSL埋め込み", "oss_note"=>"処理系にOSS多数", "async_support"=>"継続・軽量スレッドは実装依存", "throughput_100k"=>"教育・研究用途中心", "memory"=>"極小", "ai_comment"=>"Lisp方言・CS教育・関数型学習向け"],
        ["rank"=>38, "name"=>"PowerShell",      "team_dev"=>"普通",       "maintenance"=>"普通",                 "beginner"=>"中級向け",     "speed"=>"普通",   "dev_scale"=>"中規模",   "traits"=>"Windows自動化・Azure・.NET連携", "oss_note"=>"PowerShell 7系はOSS。Windows専用機能はOSライセンス側", "async_support"=>"ジョブ・RunspacePoolで並行", "throughput_100k"=>"運用自動化中心。常時高RPS APIには不向きなことが多い", "memory"=>"中程度", "ai_comment"=>"Windows・Azure自動化で必須"],
        ["rank"=>39, "name"=>"Bash",            "team_dev"=>"普通",       "maintenance"=>"普通",                 "beginner"=>"中級向け",     "speed"=>"普通",   "dev_scale"=>"中規模",   "traits"=>"シェル・CI/CD・パイプライン", "oss_note"=>"GNU bash等OSS", "async_support"=>"バックグラウンドジョブ・パイプ並列", "throughput_100k"=>"オーケストレーション中心。自前で高RPSは稀", "memory"=>"極小", "ai_comment"=>"Linux・CI/CD・インフラ自動化の定番"],
        ["rank"=>40, "name"=>"COBOL",           "team_dev"=>"普通",       "maintenance"=>"レガシー",             "beginner"=>"難しい",       "speed"=>"普通",   "dev_scale"=>"超大規模", "traits"=>"金融バッチ・メインフレーム", "oss_note"=>"標準は公開。処理系はOSS/商用が混在しやすい", "async_support"=>"プラットフォーム（JCL/CICS等）依存", "throughput_100k"=>"バッチ総量は巨大。オンライン1秒あたりは基盤次第", "memory"=>"大容量必要", "ai_comment"=>"銀行・保険基幹系で未だ現役"],
        ["rank"=>41, "name"=>"VBA",             "team_dev"=>"低い",       "maintenance"=>"難しい",               "beginner"=>"比較的簡単",   "speed"=>"低速",   "dev_scale"=>"小規模",   "traits"=>"Office埋め込み・Excel自動化", "oss_note"=>"プロプライエタリ（Officeライセンス内）", "async_support"=>"COMイベント駆動（非同期というより単スレッド寄り）", "throughput_100k"=>"主にデスクトップ処理。高RPSは想定外", "memory"=>"中程度（Office同居）", "ai_comment"=>"Excel自動化・日本企業で根強い需要"],
        ["rank"=>42, "name"=>"Ada",             "team_dev"=>"普通",       "maintenance"=>"非常に高い",           "beginner"=>"難しい",       "speed"=>"高速",   "dev_scale"=>"大規模",   "traits"=>"高信頼・航空宇宙・防衛規格", "oss_note"=>"GNAT等OSSと商用高信頼コンパイラが混在", "async_support"=>"タスク・protected objectで並行", "throughput_100k"=>"リアルタイム制御・安全重視。最大RPSより確定性", "memory"=>"中程度", "ai_comment"=>"安全性重視・航空宇宙・軍事系で使用"],
        ["rank"=>43, "name"=>"Solidity",        "team_dev"=>"普通",       "maintenance"=>"普通",                 "beginner"=>"難しい",       "speed"=>"普通",   "dev_scale"=>"中規模",   "traits"=>"EVM・スマートコントラクト", "oss_note"=>"コンパイラはOSS。チェーン手数料は別", "async_support"=>"トランザクションは逐次確定（並行はチェーン側）", "throughput_100k"=>"チェーンTPS上限に依存（言語単体のRPSとは別問題）", "memory"=>"中程度", "ai_comment"=>"Ethereum・スマートコントラクト専用"],
        ["rank"=>44, "name"=>"Move",            "team_dev"=>"低い",       "maintenance"=>"高い",                 "beginner"=>"難しい",       "speed"=>"高速",   "dev_scale"=>"中規模",   "traits"=>"Sui/Aptos・資源安全モデル", "oss_note"=>"MoveツールチェーンはOSS中心", "async_support"=>"チェーン実行モデルに依存", "throughput_100k"=>"チェーン性能に依存", "memory"=>"中程度", "ai_comment"=>"Sui・Aptos向け次世代スマートコントラクト"],
        ["rank"=>45, "name"=>"Cairo",           "team_dev"=>"低い",       "maintenance"=>"普通",                 "beginner"=>"非常に難しい", "speed"=>"高速",   "dev_scale"=>"中規模",   "traits"=>"ZK証明・StarkNet", "oss_note"=>"Cairo関連ツールはOSS中心", "async_support"=>"証明生成パイプラインはバッチ/並列化", "throughput_100k"=>"証明検証は高スループット設計も。開発体験は専門性高い", "memory"=>"中程度", "ai_comment"=>"StarkNet・ゼロ知識証明プログラミング"],
        ["rank"=>46, "name"=>"SQL",             "team_dev"=>"非常に高い", "maintenance"=>"高い",                 "beginner"=>"比較的簡単",   "speed"=>"高速",   "dev_scale"=>"超大規模", "traits"=>"宣言的・DB中心・分析とTX両方", "oss_note"=>"方言は製品依存。PostgreSQL等はOSS", "async_support"=>"DB側が並行制御・接続プールで非同期負荷に対応", "throughput_100k"=>"インデックス・パーティション次第で10万件/秒級も現実的", "memory"=>"エンジン依存（中〜大）", "ai_comment"=>"DB操作の共通言語・全エンジニア必須"],
        ["rank"=>47, "name"=>"GraphQL",         "team_dev"=>"非常に高い", "maintenance"=>"高い",                 "beginner"=>"中級向け",     "speed"=>"高速",   "dev_scale"=>"超大規模", "traits"=>"スキーマ駆動API・クライアント主導", "oss_note"=>"仕様はOSS。ゲートウェイ製品はOSS/商用混在", "async_support"=>"resolverがasync対応（実装依存）", "throughput_100k"=>"バッチング/DataLoaderで高RPS最適化が前提", "memory"=>"中程度", "ai_comment"=>"型安全API・フロントエンド主導のデータ取得"],
        ["rank"=>48, "name"=>"WebAssembly",     "team_dev"=>"普通",       "maintenance"=>"高い",                 "beginner"=>"難しい",       "speed"=>"超高速", "dev_scale"=>"大規模",   "traits"=>"ブラウザ/WASI・ネイティブ近い実行", "oss_note"=>"Wasm仕様・主要ランタイムはOSS", "async_support"=>"ホスト/ランタイム連携で非同期", "throughput_100k"=>"計算密集カーネルで高スループット。IOはホスト連携", "memory"=>"小容量", "ai_comment"=>"ブラウザでネイティブ速度・次世代標準"],
        ["rank"=>49, "name"=>"HCL",             "team_dev"=>"高い",       "maintenance"=>"普通",                 "beginner"=>"中級向け",     "speed"=>"普通",   "dev_scale"=>"中規模",   "traits"=>"Terraform・IaC", "oss_note"=>"TerraformコアはOSS。Terraform Cloud等は商用", "async_support"=>"適用は主にバッチ（宣言的プラン/適用）", "throughput_100k"=>"リソース生成はバッチ。API RPSは主目的外", "memory"=>"極小", "ai_comment"=>"Terraform・インフラas Code標準言語"],
        ["rank"=>50, "name"=>"MDX",             "team_dev"=>"高い",       "maintenance"=>"普通",                 "beginner"=>"比較的簡単",   "speed"=>"高速",   "dev_scale"=>"小規模",   "traits"=>"ドキュメント+JSX・静的サイト", "oss_note"=>"MDXはOSS。ホスティングは商用併用可", "async_support"=>"ビルド時評価中心（ランタイム非同期はフレームワーク依存）", "throughput_100k"=>"静的配信が主。動的最大RPSは主目的外", "memory"=>"小容量", "ai_comment"=>"Markdown+JSX・技術ドキュメント標準"],
        ["rank"=>51, "name"=>"V",               "team_dev"=>"低い",       "maintenance"=>"高い",                 "beginner"=>"比較的簡単",   "speed"=>"超高速", "dev_scale"=>"中規模",   "traits"=>"Go風シンプル構文・高速コンパイル", "oss_note"=>"V言語はOSS（MIT）", "async_support"=>"軽量goroutine風concurrent", "throughput_100k"=>"高スループット可能だがエコシステムは小さめ", "memory"=>"小容量", "ai_comment"=>"Go・Rust影響の新興高速言語"],
        ["rank"=>52, "name"=>"Carbon",          "team_dev"=>"低い",       "maintenance"=>"高い",                 "beginner"=>"難しい",       "speed"=>"超高速", "dev_scale"=>"中規模",   "traits"=>"C++後継候補・Google製", "oss_note"=>"Carbon はOSS（Apache 2.0）", "async_support"=>"C++互換の非同期モデル", "throughput_100k"=>"C++相当の高スループット設計", "memory"=>"小容量", "ai_comment"=>"GoogleのC++後継実験言語、注目の新星"],
        ["rank"=>53, "name"=>"Mojo",            "team_dev"=>"低い",       "maintenance"=>"高い",                 "beginner"=>"中級向け",     "speed"=>"超高速", "dev_scale"=>"中規模",   "traits"=>"Python超上位互換・MLIR/SIMD", "oss_note"=>"Mojo一部OSS化進行中", "async_support"=>"async/awaitをPython互換で実装", "throughput_100k"=>"AIワークロードでGPU/CPU最大利用", "memory"=>"中程度", "ai_comment"=>"AI/ML向けPython超高速版・次世代注目"],
        ["rank"=>54, "name"=>"Ballerina",       "team_dev"=>"普通",       "maintenance"=>"高い",                 "beginner"=>"中級向け",     "speed"=>"高速",   "dev_scale"=>"大規模",   "traits"=>"ネットワーク・クラウド統合特化", "oss_note"=>"BallerinaはOSS（Apache 2.0）", "async_support"=>"ネットワーク非同期がファーストクラス", "throughput_100k"=>"API統合・マイクロサービスで高スループット", "memory"=>"中程度", "ai_comment"=>"クラウドネイティブ統合に特化した新興言語"],
        ["rank"=>55, "name"=>"Raku",            "team_dev"=>"低い",       "maintenance"=>"普通",                 "beginner"=>"非常に難しい", "speed"=>"普通",   "dev_scale"=>"小規模",   "traits"=>"Perl6後継・高度な型システム", "oss_note"=>"RakuはOSS", "async_support"=>"Promise/Supplyで非同期対応", "throughput_100k"=>"主目的外", "memory"=>"大容量必要", "ai_comment"=>"Perl6後継・強力な型システムと正規表現"],
        ["rank"=>56, "name"=>"Io",              "team_dev"=>"低い",       "maintenance"=>"普通",                 "beginner"=>"難しい",       "speed"=>"普通",   "dev_scale"=>"小規模",   "traits"=>"プロトタイプOOP・軽量VM", "oss_note"=>"IoはOSS", "async_support"=>"コルーチン対応", "throughput_100k"=>"主目的外", "memory"=>"小容量", "ai_comment"=>"プロトタイプベースOOP・研究用途"],
        ["rank"=>57, "name"=>"Chapel",          "team_dev"=>"普通",       "maintenance"=>"高い",                 "beginner"=>"難しい",       "speed"=>"超高速", "dev_scale"=>"超大規模",  "traits"=>"並列科学計算・HPC", "oss_note"=>"ChapelはOSS（Apache 2.0）", "async_support"=>"並列ループ・分散タスクが言語組込み", "throughput_100k"=>"HPC環境で超高スループット", "memory"=>"大容量対応", "ai_comment"=>"HPC・スパコン向け並列計算言語"],
        ["rank"=>58, "name"=>"Smalltalk",       "team_dev"=>"普通",       "maintenance"=>"高い",                 "beginner"=>"中級向け",     "speed"=>"普通",   "dev_scale"=>"中規模",   "traits"=>"純粋OOP・GUIアプリ・ライブ環境", "oss_note"=>"Squeak/PharoはOSS", "async_support"=>"Actorモデル拡張あり", "throughput_100k"=>"主目的外", "memory"=>"中程度", "ai_comment"=>"OOPの元祖・Pharo等で教育・研究に現役"],
        ["rank"=>59, "name"=>"Racket",          "team_dev"=>"低い",       "maintenance"=>"高い",                 "beginner"=>"難しい",       "speed"=>"普通",   "dev_scale"=>"小規模",   "traits"=>"Lisp系・言語構築DSL", "oss_note"=>"RacketはOSS（MIT）", "async_support"=>"place/thread/channelで並行", "throughput_100k"=>"主目的外", "memory"=>"中程度", "ai_comment"=>"言語設計・DSL開発・CS教育の定番"],
        ["rank"=>60, "name"=>"Hack",            "team_dev"=>"高い",       "maintenance"=>"高い",                 "beginner"=>"中級向け",     "speed"=>"高速",   "dev_scale"=>"超大規模",  "traits"=>"PHP後継・静的型・HHVM", "oss_note"=>"HackはOSS（MIT）", "async_support"=>"async/awaitがファーストクラス", "throughput_100k"=>"Meta社内で超大規模運用実績", "memory"=>"中程度", "ai_comment"=>"Meta開発のPHP静的型後継・大規模向け"],
        ["rank"=>61, "name"=>"ActionScript",    "team_dev"=>"低い",       "maintenance"=>"低い",                 "beginner"=>"中級向け",     "speed"=>"普通",   "dev_scale"=>"小規模",   "traits"=>"Flash・Flex向けECMAScript系", "oss_note"=>"Apache Flex等がOSS", "async_support"=>"イベント駆動", "throughput_100k"=>"主目的外（Flash EOL）", "memory"=>"中程度", "ai_comment"=>"Flash終了後は極少数。レガシー"],
        ["rank"=>62, "name"=>"CoffeeScript",    "team_dev"=>"低い",       "maintenance"=>"低い",                 "beginner"=>"中級向け",     "speed"=>"高速",   "dev_scale"=>"小規模",   "traits"=>"JS→CoffeeScript変換・簡潔構文", "oss_note"=>"OSS（MIT）", "async_support"=>"JSと同等", "throughput_100k"=>"JS同等", "memory"=>"小容量", "ai_comment"=>"TypeScriptに押されほぼ新規採用なし"],
        ["rank"=>63, "name"=>"Elm",             "team_dev"=>"普通",       "maintenance"=>"非常に高い",           "beginner"=>"中級向け",     "speed"=>"高速",   "dev_scale"=>"中規模",   "traits"=>"純粋関数型・フロント特化・ランタイムエラーゼロ", "oss_note"=>"ElmはOSS（BSD）", "async_support"=>"コマンド/サブスクリプション", "throughput_100k"=>"フロント特化・主目的外", "memory"=>"小容量", "ai_comment"=>"フロント向け純粋関数型・バグゼロ体験"],
        ["rank"=>64, "name"=>"PureScript",      "team_dev"=>"低い",       "maintenance"=>"高い",                 "beginner"=>"非常に難しい", "speed"=>"高速",   "dev_scale"=>"中規模",   "traits"=>"Haskell風・JS変換", "oss_note"=>"OSS（BSD）", "async_support"=>"Affで非同期制御", "throughput_100k"=>"JS相当", "memory"=>"小容量", "ai_comment"=>"HaskellライクなJS変換・型安全フロント"],
        ["rank"=>65, "name"=>"Idris",           "team_dev"=>"低い",       "maintenance"=>"非常に高い",           "beginner"=>"非常に難しい", "speed"=>"普通",   "dev_scale"=>"小規模",   "traits"=>"依存型・定理証明補助", "oss_note"=>"IdrisはOSS（BSD）", "async_support"=>"Idris2でリニア型活用", "throughput_100k"=>"主目的外", "memory"=>"中程度", "ai_comment"=>"依存型で数学的正しさをコードで証明"],
        ["rank"=>66, "name"=>"Agda",            "team_dev"=>"低い",       "maintenance"=>"非常に高い",           "beginner"=>"非常に難しい", "speed"=>"普通",   "dev_scale"=>"小規模",   "traits"=>"依存型・定理証明・形式検証", "oss_note"=>"AgdaはOSS", "async_support"=>"主目的外", "throughput_100k"=>"主目的外", "memory"=>"中程度", "ai_comment"=>"形式検証・数学証明支援の専門言語"],
        ["rank"=>67, "name"=>"Coq",             "team_dev"=>"低い",       "maintenance"=>"非常に高い",           "beginner"=>"非常に難しい", "speed"=>"普通",   "dev_scale"=>"小規模",   "traits"=>"定理証明支援・形式化数学", "oss_note"=>"CoqはOSS（LGPL）", "async_support"=>"主目的外", "throughput_100k"=>"主目的外", "memory"=>"中程度", "ai_comment"=>"CompCert・4色定理証明等で有名な証明器"],
        ["rank"=>68, "name"=>"Wolfram Language","team_dev"=>"低い",       "maintenance"=>"高い",                 "beginner"=>"中級向け",     "speed"=>"高速",   "dev_scale"=>"中規模",   "traits"=>"数学・記号計算・Mathematica", "oss_note"=>"一部OSSだが主に商用", "async_support"=>"並列評価対応", "throughput_100k"=>"数値・記号計算特化", "memory"=>"大容量必要", "ai_comment"=>"Mathematica言語。数式・AI・IoT計算統合"],
        ["rank"=>69, "name"=>"LabVIEW（G）",   "team_dev"=>"普通",       "maintenance"=>"普通",                 "beginner"=>"中級向け",     "speed"=>"高速",   "dev_scale"=>"中規模",   "traits"=>"グラフィカル・計測制御・NI製", "oss_note"=>"商用（NI）", "async_support"=>"データフロー並列が基本設計", "throughput_100k"=>"計測・制御で実績", "memory"=>"中程度", "ai_comment"=>"計測・制御・ハードウェア連携の定番環境"],
        ["rank"=>70, "name"=>"VHDL",            "team_dev"=>"普通",       "maintenance"=>"高い",                 "beginner"=>"難しい",       "speed"=>"最高速", "dev_scale"=>"組込・FPGA","traits"=>"FPGA・ハードウェア記述", "oss_note"=>"ツール部分はOSS・商用混在", "async_support"=>"並行ハードウェア記述が基本", "throughput_100k"=>"ハード並列で超高スループット", "memory"=>"極小", "ai_comment"=>"FPGA・ASIC設計の標準ハードウェア記述語"],
        ["rank"=>71, "name"=>"Verilog",         "team_dev"=>"普通",       "maintenance"=>"高い",                 "beginner"=>"難しい",       "speed"=>"最高速", "dev_scale"=>"組込・FPGA","traits"=>"FPGA・チップ設計", "oss_note"=>"Icarus Verilog等がOSS", "async_support"=>"ハードウェア並列が基本", "throughput_100k"=>"ハード並列で超高スループット", "memory"=>"極小", "ai_comment"=>"半導体・CPU設計の世界標準HDL"],
        ["rank"=>72, "name"=>"SystemVerilog",   "team_dev"=>"高い",       "maintenance"=>"高い",                 "beginner"=>"難しい",       "speed"=>"最高速", "dev_scale"=>"超大規模",  "traits"=>"Verilog上位互換・検証も統合", "oss_note"=>"OSS・商用混在", "async_support"=>"並行ハードウェア記述", "throughput_100k"=>"SoC・GPU設計で超規模", "memory"=>"極小", "ai_comment"=>"最先端SoC・GPU設計の標準言語"],
        ["rank"=>73, "name"=>"Chisel",          "team_dev"=>"普通",       "maintenance"=>"高い",                 "beginner"=>"難しい",       "speed"=>"最高速", "dev_scale"=>"大規模",   "traits"=>"Scala埋込みHDL・RISC-V活用", "oss_note"=>"ChiselはOSS（Apache 2.0）", "async_support"=>"ハードウェア並列", "throughput_100k"=>"RISC-VコアでHPC相当", "memory"=>"極小", "ai_comment"=>"RISC-V・次世代ハードウェア設計"],
        ["rank"=>74, "name"=>"ABAP",            "team_dev"=>"高い",       "maintenance"=>"普通",                 "beginner"=>"中級向け",     "speed"=>"普通",   "dev_scale"=>"超大規模",  "traits"=>"SAP専用・ERP・ビジネスロジック", "oss_note"=>"商用（SAP）", "async_support"=>"RFC/BAPIで非同期連携", "throughput_100k"=>"大企業ERPで実績", "memory"=>"大容量必要", "ai_comment"=>"SAP ERP開発の専用言語・大企業必須"],
        ["rank"=>75, "name"=>"RPG（IBM）",      "team_dev"=>"普通",       "maintenance"=>"低い",                 "beginner"=>"難しい",       "speed"=>"高速",   "dev_scale"=>"大規模",   "traits"=>"IBM AS/400・iSeries基幹", "oss_note"=>"商用（IBM）", "async_support"=>"非同期ジョブキュー対応", "throughput_100k"=>"金融・製造基幹で大量処理実績", "memory"=>"中程度", "ai_comment"=>"IBM iSeriesの基幹業務言語・金融で現役"],
        ["rank"=>76, "name"=>"Natural",         "team_dev"=>"普通",       "maintenance"=>"低い",                 "beginner"=>"難しい",       "speed"=>"普通",   "dev_scale"=>"大規模",   "traits"=>"Software AG・ADABAS連携", "oss_note"=>"商用", "async_support"=>"バッチ・オンライン処理", "throughput_100k"=>"メインフレーム大量バッチ", "memory"=>"大容量必要", "ai_comment"=>"ドイツ発メインフレーム言語・金融現役"],
        ["rank"=>77, "name"=>"PL/SQL",          "team_dev"=>"高い",       "maintenance"=>"高い",                 "beginner"=>"中級向け",     "speed"=>"高速",   "dev_scale"=>"超大規模",  "traits"=>"Oracle DB組込み手続き型SQL", "oss_note"=>"商用（Oracle）", "async_support"=>"DBMS_SCHEDULER等で非同期", "throughput_100k"=>"Oracleの強力なDB処理で大量対応", "memory"=>"大容量必要", "ai_comment"=>"Oracle DB開発者必須のストアドプロシージャ言語"],
        ["rank"=>78, "name"=>"T-SQL",           "team_dev"=>"高い",       "maintenance"=>"高い",                 "beginner"=>"中級向け",     "speed"=>"高速",   "dev_scale"=>"超大規模",  "traits"=>"SQL Server・Azure SQL拡張SQL", "oss_note"=>"商用（Microsoft）", "async_support"=>"Service Brokerで非同期", "throughput_100k"=>"SQL Serverで大規模OLTP実績", "memory"=>"大容量必要", "ai_comment"=>"Microsoft SQL Server・Azure DB開発標準"],
        ["rank"=>79, "name"=>"Tcl",             "team_dev"=>"低い",       "maintenance"=>"普通",                 "beginner"=>"比較的簡単",   "speed"=>"普通",   "dev_scale"=>"小規模",   "traits"=>"スクリプト・EDAツール連携", "oss_note"=>"TclはOSS", "async_support"=>"イベントループ（Tk/非同期IO）", "throughput_100k"=>"主目的外", "memory"=>"小容量", "ai_comment"=>"EDA・CAD自動化・組込テストで現役"],
        ["rank"=>80, "name"=>"Forth",           "team_dev"=>"低い",       "maintenance"=>"難しい",               "beginner"=>"非常に難しい", "speed"=>"超高速", "dev_scale"=>"組込向け",  "traits"=>"スタック型・組込・ブートローダ", "oss_note"=>"gforthはOSS", "async_support"=>"主目的外", "throughput_100k"=>"主目的外", "memory"=>"極小", "ai_comment"=>"組込・ファームウェア・ブートローダに現役"],
        ["rank"=>81, "name"=>"Visual Basic .NET","team_dev"=>"普通",       "maintenance"=>"普通",                 "beginner"=>"初心者向け",   "speed"=>"普通",   "dev_scale"=>"中規模",   "traits"=>".NET上のBASIC系・業務アプリ・COM連携", "oss_note"=>"コンパイラはOSS（.NET SDK）", "async_support"=>"Async/Awaitに対応", "throughput_100k"=>".NET基盤で中規模までは対応可", "memory"=>"中程度", "ai_comment"=>"レガシー業務システムの保守で現役"],
        ["rank"=>82, "name"=>"Delphi (Object Pascal)","team_dev"=>"普通", "maintenance"=>"普通",                 "beginner"=>"中級向け",     "speed"=>"高速",   "dev_scale"=>"中規模",   "traits"=>"RAD開発・Windowsデスクトップ業務アプリ", "oss_note"=>"商用（Embarcadero）、FreePascal/Lazarusは代替OSS", "async_support"=>"スレッド＋一部async拡張", "throughput_100k"=>"デスクトップ業務用途が中心", "memory"=>"中程度", "ai_comment"=>"銀行・行政の古いデスクトップ業務アプリで現役"],
        ["rank"=>83, "name"=>"Pascal",           "team_dev"=>"低い",       "maintenance"=>"普通",                 "beginner"=>"初心者向け",   "speed"=>"高速",   "dev_scale"=>"小規模",   "traits"=>"教育用・構造化プログラミングの祖", "oss_note"=>"FreePascalはOSS", "async_support"=>"主目的外", "throughput_100k"=>"主目的外", "memory"=>"小容量", "ai_comment"=>"教育現場・組込の一部でいまも採用例あり"],
        ["rank"=>84, "name"=>"Apex",             "team_dev"=>"普通",       "maintenance"=>"普通",                 "beginner"=>"中級向け",     "speed"=>"普通",   "dev_scale"=>"中規模",   "traits"=>"Salesforce専用・トリガー/バッチ処理", "oss_note"=>"商用（Salesforce専有）", "async_support"=>"Queueable/Future/Batchで非同期", "throughput_100k"=>"Salesforceガバナ制限内での大量処理", "memory"=>"中程度", "ai_comment"=>"Salesforce導入企業では必須スキル"],
        ["rank"=>85, "name"=>"Rexx",             "team_dev"=>"低い",       "maintenance"=>"普通",                 "beginner"=>"比較的簡単",   "speed"=>"普通",   "dev_scale"=>"小規模",   "traits"=>"メインフレーム自動化・スクリプト", "oss_note"=>"Regina RexxはOSS", "async_support"=>"主目的外", "throughput_100k"=>"主目的外", "memory"=>"極小", "ai_comment"=>"IBMメインフレームの自動化スクリプトで現役"],
        ["rank"=>86, "name"=>"AWK",              "team_dev"=>"低い",       "maintenance"=>"普通",                 "beginner"=>"比較的簡単",   "speed"=>"高速",   "dev_scale"=>"小規模",   "traits"=>"テキスト処理・ログ集計・ワンライナー", "oss_note"=>"GNU AwkはOSS（GPL）", "async_support"=>"主目的外", "throughput_100k"=>"ストリーム処理でログ集計は高速", "memory"=>"極小", "ai_comment"=>"サーバ運用のログ集計で今も現役の定番"],
        ["rank"=>87, "name"=>"Vala",             "team_dev"=>"低い",       "maintenance"=>"普通",                 "beginner"=>"中級向け",     "speed"=>"高速",   "dev_scale"=>"小規模",   "traits"=>"GObject向けC#風構文・GNOME開発", "oss_note"=>"ValaはOSS（LGPL）", "async_support"=>"async/awaitをネイティブサポート", "throughput_100k"=>"Cにコンパイルされ高速だが採用例は限定的", "memory"=>"小容量", "ai_comment"=>"GNOMEデスクトップアプリ開発の選択肢の一つ"],
        ["rank"=>88, "name"=>"Standard ML",      "team_dev"=>"低い",       "maintenance"=>"高い",                 "beginner"=>"難しい",       "speed"=>"高速",   "dev_scale"=>"小規模",   "traits"=>"関数型言語研究・型理論の源流", "oss_note"=>"SML/NJ、MLtonともOSS", "async_support"=>"主目的外", "throughput_100k"=>"主目的外", "memory"=>"中程度", "ai_comment"=>"OCaml/Haskellの源流。学術研究で現役"],
        ["rank"=>89, "name"=>"Q#",               "team_dev"=>"低い",       "maintenance"=>"高い",                 "beginner"=>"非常に難しい", "speed"=>"シミュレータ依存", "dev_scale"=>"小規模", "traits"=>"量子コンピューティング専用（Microsoft）", "oss_note"=>"QDKはOSS（MIT）", "async_support"=>"量子並列性が本質的に組み込まれる", "throughput_100k"=>"量子シミュレータの規模に依存・主目的外", "memory"=>"シミュレータ次第で大容量必要", "ai_comment"=>"量子アルゴリズム研究・実用化はまだ発展途上"],
        ["rank"=>90, "name"=>"ReasonML",         "team_dev"=>"低い",       "maintenance"=>"高い",                 "beginner"=>"難しい",       "speed"=>"高速",   "dev_scale"=>"中規模",   "traits"=>"OCaml構文をJS/React向けに再設計", "oss_note"=>"ReasonML/ReScriptはOSS（MIT）", "async_support"=>"Promise/Lwtベースの非同期", "throughput_100k"=>"JSへコンパイルされフロント用途中心", "memory"=>"小容量", "ai_comment"=>"型安全なReact開発を志向したコミュニティ小規模言語"],
        ["rank"=>91, "name"=>"Odin",             "team_dev"=>"低い",       "maintenance"=>"高い",                 "beginner"=>"中級向け",     "speed"=>"超高速", "dev_scale"=>"中規模",   "traits"=>"C代替志向・ゲーム/システムプログラミング", "oss_note"=>"OdinはOSS（BSD）", "async_support"=>"標準では最小限、明示的な並行処理を志向", "throughput_100k"=>"低レベル制御でハイスループット可能", "memory"=>"極小", "ai_comment"=>"Cのシンプルさとモダンな安全性を両立させる新興言語"],
        ["rank"=>92, "name"=>"Gleam",            "team_dev"=>"低い",       "maintenance"=>"非常に高い",           "beginner"=>"中級向け",     "speed"=>"高速",   "dev_scale"=>"中規模",   "traits"=>"BEAM上の型安全言語・Erlang資産を活用", "oss_note"=>"GleamはOSS（Apache 2.0）", "async_support"=>"BEAM（Erlang VM）のアクターモデルを継承", "throughput_100k"=>"BEAM由来の並行性で高スループットを狙える", "memory"=>"小容量", "ai_comment"=>"型安全とErlang/OTPの堅牢性を両立させる新興言語"],
        ["rank"=>93, "name"=>"Roc",              "team_dev"=>"低い",       "maintenance"=>"高い",                 "beginner"=>"中級向け",     "speed"=>"高速",   "dev_scale"=>"小規模",   "traits"=>"高速コンパイル志向の純粋関数型言語", "oss_note"=>"RocはOSS（UPL）", "async_support"=>"タスク（Task）ベースの非同期モデルを開発中", "throughput_100k"=>"開発初期段階で実運用の実績は限定的", "memory"=>"小容量", "ai_comment"=>"Elmの思想を汎用言語へ拡張する開発中の新興言語"],
        ["rank"=>94, "name"=>"Nix",              "team_dev"=>"普通",       "maintenance"=>"非常に高い",           "beginner"=>"難しい",       "speed"=>"評価言語のため主目的外", "dev_scale"=>"中規模", "traits"=>"宣言的パッケージ管理・再現可能ビルド専用言語", "oss_note"=>"NixはOSS（MIT）", "async_support"=>"主目的外（ビルド記述言語）", "throughput_100k"=>"主目的外", "memory"=>"小容量", "ai_comment"=>"再現性の高いインフラ構築で評価が高まる専用言語"],
        ["rank"=>95, "name"=>"Wren",             "team_dev"=>"低い",       "maintenance"=>"普通",                 "beginner"=>"比較的簡単",   "speed"=>"高速",   "dev_scale"=>"小規模",   "traits"=>"組込みスクリプト・ゲームエンジン向け軽量言語", "oss_note"=>"WrenはOSS（MIT）", "async_support"=>"ファイバーによる協調的並行処理", "throughput_100k"=>"軽量組込用途中心・主目的外", "memory"=>"極小", "ai_comment"=>"Luaに似た軽量組込スクリプトの選択肢"],
        ["rank"=>96, "name"=>"Grain",            "team_dev"=>"低い",       "maintenance"=>"高い",                 "beginner"=>"中級向け",     "speed"=>"高速",   "dev_scale"=>"小規模",   "traits"=>"WebAssembly向け静的型関数型言語", "oss_note"=>"GrainはOSS（LGPL）", "async_support"=>"Promiseベースの非同期を開発中", "throughput_100k"=>"WASM実行でブラウザ/エッジ向け・発展途上", "memory"=>"極小", "ai_comment"=>"WebAssembly時代を見据えた新興関数型言語"],
        ["rank"=>97, "name"=>"Janet",            "team_dev"=>"低い",       "maintenance"=>"普通",                 "beginner"=>"中級向け",     "speed"=>"高速",   "dev_scale"=>"小規模",   "traits"=>"組込み向けLisp方言・単一バイナリ配布", "oss_note"=>"JanetはOSS（MIT）", "async_support"=>"イベントループ・ファイバーで非同期対応", "throughput_100k"=>"組込・CLIツール中心・主目的外", "memory"=>"極小", "ai_comment"=>"軽量な組込みLisp。CLIツール作成で人気"],
        ["rank"=>98, "name"=>"Hare",             "team_dev"=>"低い",       "maintenance"=>"高い",                 "beginner"=>"難しい",       "speed"=>"超高速", "dev_scale"=>"小規模",   "traits"=>"C代替志向のシステムプログラミング言語", "oss_note"=>"HareはOSS（MPL 2.0）", "async_support"=>"標準では最小限、シンプルさを優先", "throughput_100k"=>"低レベル制御でハイスループット可能", "memory"=>"極小", "ai_comment"=>"シンプルさと明示性を重視するC代替の新興言語"],
        ["rank"=>99, "name"=>"Red",              "team_dev"=>"低い",       "maintenance"=>"普通",                 "beginner"=>"中級向け",     "speed"=>"普通",   "dev_scale"=>"小規模",   "traits"=>"Rebol系・単一バイナリでフルスタック開発を志向", "oss_note"=>"RedはOSS（BSL）", "async_support"=>"リアクティブ処理を志向", "throughput_100k"=>"主目的外", "memory"=>"極小", "ai_comment"=>"OS〜GUIまで単一言語で完結させる野心的プロジェクト"],
        ["rank"=>100, "name"=>"Squirrel",        "team_dev"=>"低い",       "maintenance"=>"普通",                 "beginner"=>"比較的簡単",   "speed"=>"高速",   "dev_scale"=>"小規模",   "traits"=>"ゲーム組込みスクリプト・C++親和性", "oss_note"=>"SquirrelはOSS（MIT）", "async_support"=>"コルーチンをネイティブサポート", "throughput_100k"=>"ゲームロジック用途中心・主目的外", "memory"=>"極小", "ai_comment"=>"Left 4 Dead等で使われたゲーム組込スクリプト"],
    ];

    $frameworks = [
        ["rank"=> 1, "name"=>"Next.js",          "team_dev"=>"非常に高い", "maintenance"=>"高い",      "beginner"=>"普通",       "speed"=>"超高速", "large_scale"=>"超大規模対応",   "ai_comment"=>"SSR・SEO・AI SaaS向け最強FW"],
        ["rank"=> 2, "name"=>"React",             "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"普通",       "speed"=>"高速",   "large_scale"=>"超大規模対応",   "ai_comment"=>"世界最大エコシステム、Meta製"],
        ["rank"=> 3, "name"=>"Vue.js",            "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"初心者向け", "speed"=>"高速",   "large_scale"=>"大規模対応",     "ai_comment"=>"日本・アジアで特に人気"],
        ["rank"=> 4, "name"=>"Nuxt.js",           "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"普通",       "speed"=>"超高速", "large_scale"=>"大規模対応",     "ai_comment"=>"Vue.jsのSSR・フルスタック版"],
        ["rank"=> 5, "name"=>"Angular",           "team_dev"=>"非常に高い", "maintenance"=>"高い",      "beginner"=>"難しい",     "speed"=>"高速",   "large_scale"=>"超大規模対応",   "ai_comment"=>"Google製・エンタープライズ標準"],
        ["rank"=> 6, "name"=>"Laravel",           "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"初心者向け", "speed"=>"普通",   "large_scale"=>"大規模対応",     "ai_comment"=>"PHP最強FW、日本で非常に人気"],
        ["rank"=> 7, "name"=>"Spring Boot",       "team_dev"=>"非常に高い", "maintenance"=>"高い",      "beginner"=>"難しい",     "speed"=>"高速",   "large_scale"=>"超大規模対応",   "ai_comment"=>"Java企業システムの定番"],
        ["rank"=> 8, "name"=>"FastAPI",           "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"比較的簡単", "speed"=>"超高速", "large_scale"=>"大規模対応",     "ai_comment"=>"Python最速API、AI連携で急成長"],
        ["rank"=> 9, "name"=>"NestJS",            "team_dev"=>"非常に高い", "maintenance"=>"高い",      "beginner"=>"普通",       "speed"=>"高速",   "large_scale"=>"超大規模対応",   "ai_comment"=>"TypeScript製バックエンド標準"],
        ["rank"=>10, "name"=>"Svelte",            "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"比較的簡単", "speed"=>"超高速", "large_scale"=>"大規模対応",     "ai_comment"=>"コンパイル型・最軽量FWとして急成長"],
        ["rank"=>11, "name"=>"SvelteKit",         "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"普通",       "speed"=>"超高速", "large_scale"=>"大規模対応",     "ai_comment"=>"SvelteのフルスタックSSR版"],
        ["rank"=>12, "name"=>"Astro",             "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"比較的簡単", "speed"=>"超高速", "large_scale"=>"大規模対応",     "ai_comment"=>"静的サイト特化、Zero JS送信"],
        ["rank"=>13, "name"=>"Remix",             "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"普通",       "speed"=>"超高速", "large_scale"=>"大規模対応",     "ai_comment"=>"React Router後継・Web標準重視"],
        ["rank"=>14, "name"=>"Django",            "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"比較的簡単", "speed"=>"普通",   "large_scale"=>"大規模対応",     "ai_comment"=>"Python製フルスタック、安定実績"],
        ["rank"=>15, "name"=>"Flask",             "team_dev"=>"普通",       "maintenance"=>"普通",      "beginner"=>"初心者向け", "speed"=>"普通",   "large_scale"=>"中規模対応",     "ai_comment"=>"Python軽量API・プロトタイプ向け"],
        ["rank"=>16, "name"=>"Express",           "team_dev"=>"高い",       "maintenance"=>"普通",      "beginner"=>"比較的簡単", "speed"=>"高速",   "large_scale"=>"大規模対応",     "ai_comment"=>"Node.js最定番・最古参FW"],
        ["rank"=>17, "name"=>"Hono",              "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"比較的簡単", "speed"=>"超高速", "large_scale"=>"大規模対応",     "ai_comment"=>"Edge Runtime対応・次世代軽量FW"],
        ["rank"=>18, "name"=>"Flutter",           "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"普通",       "speed"=>"高速",   "large_scale"=>"大規模対応",     "ai_comment"=>"Google製・iOS/Android/Web全対応"],
        ["rank"=>19, "name"=>"React Native",      "team_dev"=>"高い",       "maintenance"=>"普通",      "beginner"=>"普通",       "speed"=>"普通",   "large_scale"=>"大規模対応",     "ai_comment"=>"React知識でモバイル開発可能"],
        ["rank"=>20, "name"=>"Fastify",           "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"普通",       "speed"=>"超高速", "large_scale"=>"大規模対応",     "ai_comment"=>"Node.js最速API・Express後継候補"],
        ["rank"=>21, "name"=>"ASP.NET Core",      "team_dev"=>"非常に高い", "maintenance"=>"高い",      "beginner"=>"普通",       "speed"=>"超高速", "large_scale"=>"超大規模対応",   "ai_comment"=>"Microsoft製・C#企業システム標準"],
        ["rank"=>22, "name"=>"Ruby on Rails",     "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"比較的簡単", "speed"=>"普通",   "large_scale"=>"中規模対応",     "ai_comment"=>"高速開発・スタートアップの定番"],
        ["rank"=>23, "name"=>"Gin",               "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"普通",       "speed"=>"超高速", "large_scale"=>"大規模対応",     "ai_comment"=>"Go最人気軽量WebFW"],
        ["rank"=>24, "name"=>"Solid.js",          "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"普通",       "speed"=>"超高速", "large_scale"=>"大規模対応",     "ai_comment"=>"React代替・最速FWの一角"],
        ["rank"=>25, "name"=>"tRPC",              "team_dev"=>"非常に高い", "maintenance"=>"非常に高い","beginner"=>"普通",       "speed"=>"高速",   "large_scale"=>"大規模対応",     "ai_comment"=>"型安全API・フルスタックTS開発標準"],
        ["rank"=>26, "name"=>"SwiftUI",           "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"普通",       "speed"=>"高速",   "large_scale"=>"大規模対応",     "ai_comment"=>"Apple製iOS/macOS宣言的UI"],
        ["rank"=>27, "name"=>"Jetpack Compose",   "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"普通",       "speed"=>"高速",   "large_scale"=>"大規模対応",     "ai_comment"=>"Android公式宣言的UIフレームワーク"],
        ["rank"=>28, "name"=>"Axum",              "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"難しい",     "speed"=>"超高速", "large_scale"=>"超大規模対応",   "ai_comment"=>"Rust最人気WebFW・高性能API向け"],
        ["rank"=>29, "name"=>"Actix Web",         "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"難しい",     "speed"=>"超高速", "large_scale"=>"超大規模対応",   "ai_comment"=>"世界最速ベンチマーク常連・Rust製"],
        ["rank"=>30, "name"=>"Echo",              "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"普通",       "speed"=>"超高速", "large_scale"=>"大規模対応",     "ai_comment"=>"Go製軽量高速WebFW・Gin並ぶ人気"],
        ["rank"=>31, "name"=>"Phoenix",           "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"難しい",     "speed"=>"超高速", "large_scale"=>"大規模対応",     "ai_comment"=>"Elixir製・LiveView・高耐障害Web"],
        ["rank"=>32, "name"=>"Fiber",             "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"比較的簡単", "speed"=>"超高速", "large_scale"=>"大規模対応",     "ai_comment"=>"Go製・Express風API・初心者にも親切"],
        ["rank"=>33, "name"=>"Micronaut",         "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"難しい",     "speed"=>"超高速", "large_scale"=>"超大規模対応",   "ai_comment"=>"Java/Kotlin・低起動時間・Cloud Native"],
        ["rank"=>34, "name"=>"Quarkus",           "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"難しい",     "speed"=>"超高速", "large_scale"=>"超大規模対応",   "ai_comment"=>"Java・GraalVM・Kubernetes最適化"],
        ["rank"=>35, "name"=>"Blazor",            "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"普通",       "speed"=>"高速",   "large_scale"=>"大規模対応",     "ai_comment"=>"C#でWebフロント開発・WASM対応"],
        ["rank"=>36, "name"=>"Streamlit",         "team_dev"=>"普通",       "maintenance"=>"普通",      "beginner"=>"初心者向け", "speed"=>"普通",   "large_scale"=>"中規模対応",     "ai_comment"=>"PythonでAIダッシュボードを爆速構築"],
        ["rank"=>37, "name"=>"Expo",              "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"比較的簡単", "speed"=>"普通",   "large_scale"=>"大規模対応",     "ai_comment"=>"React Native開発を劇的に簡単化"],
        ["rank"=>38, "name"=>"Ionic",             "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"普通",       "speed"=>"普通",   "large_scale"=>"大規模対応",     "ai_comment"=>"Web技術でクロスプラットフォームアプリ"],
        ["rank"=>39, "name"=>"Capacitor",         "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"普通",       "speed"=>"普通",   "large_scale"=>"大規模対応",     "ai_comment"=>"WebアプリをネイティブアプリにラップするFW"],
        ["rank"=>40, "name"=>"TanStack Query",    "team_dev"=>"非常に高い", "maintenance"=>"非常に高い","beginner"=>"普通",       "speed"=>"高速",   "large_scale"=>"超大規模対応",   "ai_comment"=>"非同期状態管理の事実上の標準"],
        ["rank"=>41, "name"=>"TanStack Router",   "team_dev"=>"非常に高い", "maintenance"=>"非常に高い","beginner"=>"普通",       "speed"=>"高速",   "large_scale"=>"超大規模対応",   "ai_comment"=>"型安全ルーティング・React Router対抗"],
        ["rank"=>42, "name"=>"HTMX",             "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"比較的簡単", "speed"=>"高速",   "large_scale"=>"大規模対応",     "ai_comment"=>"HTMLだけで動的UI・JS不要の新思想"],
        ["rank"=>43, "name"=>"Qwik",             "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"普通",       "speed"=>"超高速", "large_scale"=>"超大規模対応",   "ai_comment"=>"Resumability・ゼロハイドレーション"],
        ["rank"=>44, "name"=>"Lit",              "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"普通",       "speed"=>"超高速", "large_scale"=>"大規模対応",     "ai_comment"=>"Web Components標準・Google製軽量FW"],
        ["rank"=>45, "name"=>"Preact",           "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"比較的簡単", "speed"=>"超高速", "large_scale"=>"大規模対応",     "ai_comment"=>"React互換・3KB超軽量・Edge向け"],
        ["rank"=>46, "name"=>"Celery",           "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"普通",       "speed"=>"高速",   "large_scale"=>"超大規模対応",   "ai_comment"=>"Python非同期タスクキューの定番"],
        ["rank"=>47, "name"=>"Symfony",          "team_dev"=>"非常に高い", "maintenance"=>"非常に高い","beginner"=>"難しい",     "speed"=>"普通",   "large_scale"=>"超大規模対応",   "ai_comment"=>"PHP企業・LaravelのベースFW"],
        ["rank"=>48, "name"=>"CakePHP",          "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"普通",       "speed"=>"普通",   "large_scale"=>"大規模対応",     "ai_comment"=>"PHP老舗FW・日本の中小企業で現役"],
        ["rank"=>49, "name"=>"Play Framework",   "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"難しい",     "speed"=>"高速",   "large_scale"=>"超大規模対応",   "ai_comment"=>"Scala/Java・非同期Web・大規模向け"],
        ["rank"=>50, "name"=>"Akka",             "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"非常に難しい","speed"=>"超高速", "large_scale"=>"超大規模対応",   "ai_comment"=>"Scala・アクターモデル・分散システム"],
        ["rank"=>51, "name"=>"Ktor",             "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"普通",       "speed"=>"高速",   "large_scale"=>"大規模対応",     "ai_comment"=>"Kotlin製非同期WebFW・JetBrains公式"],
        ["rank"=>52, "name"=>"Vert.x",           "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"難しい",     "speed"=>"超高速", "large_scale"=>"超大規模対応",   "ai_comment"=>"Java/Kotlin非同期・リアクティブ定番"],
        ["rank"=>53, "name"=>"Helidon",          "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"難しい",     "speed"=>"超高速", "large_scale"=>"超大規模対応",   "ai_comment"=>"Oracle製MicroProfile/Nima・軽量Java"],
        ["rank"=>54, "name"=>"Dropwizard",       "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"普通",       "speed"=>"高速",   "large_scale"=>"大規模対応",     "ai_comment"=>"Javaの本番RESTアプリを素早く構築"],
        ["rank"=>55, "name"=>"Grails",           "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"比較的簡単", "speed"=>"普通",   "large_scale"=>"大規模対応",     "ai_comment"=>"Groovy製Rails風FW・Spring統合"],
        ["rank"=>56, "name"=>"Yii2",             "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"普通",       "speed"=>"高速",   "large_scale"=>"大規模対応",     "ai_comment"=>"PHP高速FW・コンポーネントが充実"],
        ["rank"=>57, "name"=>"Slim",             "team_dev"=>"普通",       "maintenance"=>"普通",      "beginner"=>"比較的簡単", "speed"=>"高速",   "large_scale"=>"中規模対応",     "ai_comment"=>"PHP超軽量マイクロFW・API向け"],
        ["rank"=>58, "name"=>"Lumen",            "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"比較的簡単", "speed"=>"高速",   "large_scale"=>"大規模対応",     "ai_comment"=>"Laravelの軽量版・マイクロサービス向け"],
        ["rank"=>59, "name"=>"AdonisJS",         "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"比較的簡単", "speed"=>"高速",   "large_scale"=>"大規模対応",     "ai_comment"=>"Node.js版Rails・Laravel風フルスタック"],
        ["rank"=>60, "name"=>"Sails.js",         "team_dev"=>"高い",       "maintenance"=>"普通",      "beginner"=>"比較的簡単", "speed"=>"高速",   "large_scale"=>"大規模対応",     "ai_comment"=>"Node.js MVC・リアルタイムAPI特化"],
        ["rank"=>61, "name"=>"Meteor",           "team_dev"=>"高い",       "maintenance"=>"普通",      "beginner"=>"比較的簡単", "speed"=>"高速",   "large_scale"=>"中規模対応",     "ai_comment"=>"フルスタックJS・リアルタイム同期FW"],
        ["rank"=>62, "name"=>"Koa",              "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"比較的簡単", "speed"=>"超高速", "large_scale"=>"大規模対応",     "ai_comment"=>"Express後継・async/await設計のNode.js FW"],
        ["rank"=>63, "name"=>"Moleculer",        "team_dev"=>"非常に高い", "maintenance"=>"高い",      "beginner"=>"普通",       "speed"=>"超高速", "large_scale"=>"超大規模対応",   "ai_comment"=>"Node.jsマイクロサービスFW・Service Mesh対応"],
        ["rank"=>64, "name"=>"Feathers",         "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"普通",       "speed"=>"高速",   "large_scale"=>"大規模対応",     "ai_comment"=>"REST/WebSocket API・Node.js軽量FW"],
        ["rank"=>65, "name"=>"Strapi",           "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"比較的簡単", "speed"=>"高速",   "large_scale"=>"大規模対応",     "ai_comment"=>"Node.js製ヘッドレスCMS・OSS API管理"],
        ["rank"=>66, "name"=>"KeystoneJS",       "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"普通",       "speed"=>"高速",   "large_scale"=>"大規模対応",     "ai_comment"=>"Next.js+GraphQL統合ヘッドレスCMS"],
        ["rank"=>67, "name"=>"Payload CMS",      "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"普通",       "speed"=>"高速",   "large_scale"=>"大規模対応",     "ai_comment"=>"TypeScript製OSS CMS・柔軟なスキーマ"],
        ["rank"=>68, "name"=>"Wasp",             "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"比較的簡単", "speed"=>"高速",   "large_scale"=>"大規模対応",     "ai_comment"=>"React+Node.js統合・設定不要フルスタック"],
        ["rank"=>69, "name"=>"RedwoodJS",        "team_dev"=>"非常に高い", "maintenance"=>"非常に高い","beginner"=>"普通",       "speed"=>"高速",   "large_scale"=>"大規模対応",     "ai_comment"=>"React+GraphQL+Prisma統合フルスタック"],
        ["rank"=>70, "name"=>"Blitz.js",         "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"普通",       "speed"=>"高速",   "large_scale"=>"大規模対応",     "ai_comment"=>"Next.js+Prisma統合・Zero-API思想"],
        ["rank"=>71, "name"=>"Encore",           "team_dev"=>"非常に高い", "maintenance"=>"非常に高い","beginner"=>"普通",       "speed"=>"超高速", "large_scale"=>"超大規模対応",   "ai_comment"=>"バックエンドFW・インフラ自動生成"],
        ["rank"=>72, "name"=>"Bun（ランタイム）","team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"比較的簡単", "speed"=>"超高速", "large_scale"=>"大規模対応",     "ai_comment"=>"Node.js代替・Zig製超高速JSランタイム"],
        ["rank"=>73, "name"=>"Deno（Deploy）",   "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"比較的簡単", "speed"=>"超高速", "large_scale"=>"大規模対応",     "ai_comment"=>"Deno公式Edge Deploy・V8 isolates"],
        ["rank"=>74, "name"=>"Nitro",            "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"普通",       "speed"=>"超高速", "large_scale"=>"大規模対応",     "ai_comment"=>"Nuxt3エンジン・UnJS製サーバFW"],
        ["rank"=>75, "name"=>"Analog",           "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"普通",       "speed"=>"超高速", "large_scale"=>"大規模対応",     "ai_comment"=>"Angular向けフルスタックメタFW"],
        ["rank"=>76, "name"=>"Nest（Fastify）",  "team_dev"=>"非常に高い", "maintenance"=>"非常に高い","beginner"=>"普通",       "speed"=>"超高速", "large_scale"=>"超大規模対応",   "ai_comment"=>"NestJS+Fastifyアダプタ・最速TS構成"],
        ["rank"=>77, "name"=>"Elysia",           "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"比較的簡単", "speed"=>"超高速", "large_scale"=>"大規模対応",     "ai_comment"=>"Bun専用・型安全超高速WebFW"],
        ["rank"=>78, "name"=>"Oak",              "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"比較的簡単", "speed"=>"高速",   "large_scale"=>"大規模対応",     "ai_comment"=>"Deno向けKoa風ミドルウェアFW"],
        ["rank"=>79, "name"=>"Fresh",            "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"普通",       "speed"=>"超高速", "large_scale"=>"大規模対応",     "ai_comment"=>"Deno公式・IslandsアーキテクチャFW"],
        ["rank"=>80, "name"=>"Leptos",           "team_dev"=>"高い",       "maintenance"=>"高い",      "beginner"=>"難しい",     "speed"=>"超高速", "large_scale"=>"大規模対応",     "ai_comment"=>"Rust+WASM・フルスタックリアクティブFW"],
    ];

    $databases = [
        ["rank"=> 1, "name"=>"PostgreSQL",    "speed"=>"高速",   "scale"=>"10万件/秒級",   "distributed"=>"対応",      "ai_comment"=>"AI・金融・大規模Web向け最強RDB"],
        ["rank"=> 2, "name"=>"MySQL",         "speed"=>"高速",   "scale"=>"10万件/秒級",   "distributed"=>"一部対応",  "ai_comment"=>"世界最大級普及率・LAMP定番"],
        ["rank"=> 3, "name"=>"Redis",         "speed"=>"超高速", "scale"=>"数十万件/秒",   "distributed"=>"対応",      "ai_comment"=>"リアルタイム処理・キャッシュ特化"],
        ["rank"=> 4, "name"=>"MongoDB",       "speed"=>"高速",   "scale"=>"大規模対応",    "distributed"=>"完全対応",  "ai_comment"=>"NoSQL最人気・JSONドキュメント型"],
        ["rank"=> 5, "name"=>"SQLite",        "speed"=>"高速",   "scale"=>"小中規模向け",  "distributed"=>"非対応",    "ai_comment"=>"組込・モバイル・ローカルDBの定番"],
        ["rank"=> 6, "name"=>"Elasticsearch", "speed"=>"超高速", "scale"=>"超大規模対応",  "distributed"=>"完全対応",  "ai_comment"=>"全文検索・ログ分析・ELKスタック"],
        ["rank"=> 7, "name"=>"Cassandra",     "speed"=>"超高速", "scale"=>"超大規模対応",  "distributed"=>"完全対応",  "ai_comment"=>"書込特化・SNS・IoTで採用多数"],
        ["rank"=> 8, "name"=>"DynamoDB",      "speed"=>"超高速", "scale"=>"無制限スケール", "distributed"=>"完全対応", "ai_comment"=>"AWS標準・サーバーレス向けNoSQL"],
        ["rank"=> 9, "name"=>"CockroachDB",   "speed"=>"高速",   "scale"=>"超大規模分散",  "distributed"=>"完全対応",  "ai_comment"=>"NewSQL・グローバル分散DBで注目"],
        ["rank"=>10, "name"=>"Firestore",     "speed"=>"高速",   "scale"=>"大規模対応",    "distributed"=>"完全対応",  "ai_comment"=>"Firebase/GCP・リアルタイム同期"],
        ["rank"=>11, "name"=>"Supabase",      "speed"=>"高速",   "scale"=>"大規模対応",    "distributed"=>"対応",      "ai_comment"=>"PostgreSQL+Auth+Storage・OSS版Firebase"],
        ["rank"=>12, "name"=>"PlanetScale",   "speed"=>"高速",   "scale"=>"超大規模対応",  "distributed"=>"完全対応",  "ai_comment"=>"MySQL互換・Vitessベース・スケール特化"],
        ["rank"=>13, "name"=>"Neon",          "speed"=>"高速",   "scale"=>"大規模対応",    "distributed"=>"対応",      "ai_comment"=>"サーバーレスPostgreSQL・Edge向け"],
        ["rank"=>14, "name"=>"TiDB",          "speed"=>"高速",   "scale"=>"超大規模対応",  "distributed"=>"完全対応",  "ai_comment"=>"MySQL互換NewSQL・HTAP対応"],
        ["rank"=>15, "name"=>"ClickHouse",    "speed"=>"超高速", "scale"=>"超大規模対応",  "distributed"=>"完全対応",  "ai_comment"=>"分析特化・OLAP・BI向け最速DB"],
        ["rank"=>16, "name"=>"Neo4j",         "speed"=>"高速",   "scale"=>"大規模対応",    "distributed"=>"対応",      "ai_comment"=>"グラフDB・SNS・知識グラフ向け"],
        ["rank"=>17, "name"=>"InfluxDB",      "speed"=>"超高速", "scale"=>"大規模対応",    "distributed"=>"対応",      "ai_comment"=>"時系列DB・IoT・監視メトリクス特化"],
        ["rank"=>18, "name"=>"Pinecone",      "speed"=>"超高速", "scale"=>"超大規模対応",  "distributed"=>"完全対応",  "ai_comment"=>"ベクトルDB・RAG・AI検索の定番"],
        ["rank"=>19, "name"=>"Weaviate",      "speed"=>"高速",   "scale"=>"大規模対応",    "distributed"=>"対応",      "ai_comment"=>"OSS ベクトルDB・AI semantic search"],
        ["rank"=>20, "name"=>"MariaDB",     "speed"=>"高速",   "scale"=>"大規模対応",    "distributed"=>"一部対応",  "ai_comment"=>"MySQL後継OSS・互換性高くコスト低"],
        ["rank"=>21, "name"=>"Oracle DB",   "speed"=>"高速",   "scale"=>"超大規模対応",  "distributed"=>"完全対応",  "ai_comment"=>"エンタープライズ最大手・金融基幹で王者"],
        ["rank"=>22, "name"=>"SQL Server",  "speed"=>"高速",   "scale"=>"超大規模対応",  "distributed"=>"対応",      "ai_comment"=>"Microsoft製・Windows企業システム定番"],
        ["rank"=>23, "name"=>"BigQuery",    "speed"=>"超高速", "scale"=>"無制限スケール", "distributed"=>"完全対応", "ai_comment"=>"GCP・ペタバイト級分析・サーバーレス"],
        ["rank"=>24, "name"=>"Snowflake",   "speed"=>"超高速", "scale"=>"超大規模対応",  "distributed"=>"完全対応",  "ai_comment"=>"クラウドDWH・マルチクラウド対応"],
        ["rank"=>25, "name"=>"Redshift",    "speed"=>"超高速", "scale"=>"超大規模対応",  "distributed"=>"完全対応",  "ai_comment"=>"AWS製DWH・S3連携・BI分析向け"],
        ["rank"=>26, "name"=>"RethinkDB",   "speed"=>"高速",   "scale"=>"大規模対応",    "distributed"=>"対応",      "ai_comment"=>"リアルタイム更新プッシュ型NoSQL"],
        ["rank"=>27, "name"=>"Couchbase",   "speed"=>"超高速", "scale"=>"超大規模対応",  "distributed"=>"完全対応",  "ai_comment"=>"NoSQL・モバイル同期・エッジ対応"],
        ["rank"=>28, "name"=>"FaunaDB",     "speed"=>"高速",   "scale"=>"大規模対応",    "distributed"=>"完全対応",  "ai_comment"=>"サーバーレス・グローバル分散・GraphQL対応"],
        ["rank"=>29, "name"=>"EdgeDB",      "speed"=>"高速",   "scale"=>"大規模対応",    "distributed"=>"対応",      "ai_comment"=>"PostgreSQL後継・型安全クエリ言語"],
        ["rank"=>30, "name"=>"SingleStore", "speed"=>"超高速", "scale"=>"超大規模対応",  "distributed"=>"完全対応",  "ai_comment"=>"HTAP・リアルタイム分析+トランザクション両立"],
        ["rank"=>31, "name"=>"DuckDB",        "speed"=>"超高速", "scale"=>"中規模向け",    "distributed"=>"非対応",    "ai_comment"=>"埋め込み分析・ローカルOLAPに強い列指向DB"],
        ["rank"=>32, "name"=>"QuestDB",       "speed"=>"超高速", "scale"=>"大規模対応",    "distributed"=>"一部対応",  "ai_comment"=>"時系列・金融ティック向け高速SQL"],
        ["rank"=>33, "name"=>"TimescaleDB",   "speed"=>"高速",   "scale"=>"大規模対応",    "distributed"=>"対応",      "ai_comment"=>"PostgreSQL拡張の時系列ハイパーテーブル"],
        ["rank"=>34, "name"=>"Apache Druid",  "speed"=>"超高速", "scale"=>"超大規模対応",  "distributed"=>"完全対応",  "ai_comment"=>"リアルタイムOLAP・イベント分析向け"],
        ["rank"=>35, "name"=>"Apache HBase",  "speed"=>"高速",   "scale"=>"超大規模対応",  "distributed"=>"完全対応",  "ai_comment"=>"HDFS上のワイドカラム・ビッグデータ定番"],
        ["rank"=>36, "name"=>"ScyllaDB",      "speed"=>"超高速", "scale"=>"超大規模対応",  "distributed"=>"完全対応",  "ai_comment"=>"Cassandra互換・低レイテンシ書込特化"],
        ["rank"=>37, "name"=>"ArangoDB",      "speed"=>"高速",   "scale"=>"大規模対応",    "distributed"=>"対応",      "ai_comment"=>"マルチモデル（ドキュメント+グラフ+KV）"],
        ["rank"=>38, "name"=>"YugabyteDB",    "speed"=>"高速",   "scale"=>"超大規模対応",  "distributed"=>"完全対応",  "ai_comment"=>"PostgreSQL互換・分散SQL（Spanner系）"],
        ["rank"=>39, "name"=>"VoltDB",        "speed"=>"超高速", "scale"=>"大規模対応",    "distributed"=>"対応",      "ai_comment"=>"インメモリNewSQL・超低遅延トランザクション"],
        ["rank"=>40, "name"=>"Apache Doris",  "speed"=>"超高速", "scale"=>"超大規模対応",  "distributed"=>"完全対応",  "ai_comment"=>"MPP OLAP・MySQLプロトコル互換で導入しやすい"],
        ["rank"=>41, "name"=>"StarRocks",     "speed"=>"超高速", "scale"=>"超大規模対応",  "distributed"=>"完全対応",  "ai_comment"=>"リアルタイム分析・MySQL互換OLAP"],
        ["rank"=>42, "name"=>"Greenplum",     "speed"=>"高速",   "scale"=>"超大規模対応",  "distributed"=>"完全対応",  "ai_comment"=>"PostgreSQL系MPP・エンタープライズDWH"],
        ["rank"=>43, "name"=>"Vertica",       "speed"=>"超高速", "scale"=>"超大規模対応",  "distributed"=>"完全対応",  "ai_comment"=>"列指向DWH・圧縮と分析性能に強い"],
        ["rank"=>44, "name"=>"IBM Db2",       "speed"=>"高速",   "scale"=>"超大規模対応",  "distributed"=>"対応",      "ai_comment"=>"金融・大企業で長期採用のRDBMS"],
        ["rank"=>45, "name"=>"Firebird",      "speed"=>"高速",   "scale"=>"中小規模向け",  "distributed"=>"一部対応",  "ai_comment"=>"軽量OSS RDB・組込み用途にも"],
        ["rank"=>46, "name"=>"Informix",      "speed"=>"高速",   "scale"=>"大規模対応",    "distributed"=>"対応",      "ai_comment"=>"時系列・組込み・IoTで実績のRDBMS"],
        ["rank"=>47, "name"=>"InterSystems IRIS","speed"=>"高速", "scale"=>"大規模対応",    "distributed"=>"対応",      "ai_comment"=>"マルチモデル+アプリサーバ一体の医療系DB"],
        ["rank"=>48, "name"=>"AlloyDB",       "speed"=>"超高速", "scale"=>"超大規模対応",  "distributed"=>"完全対応",  "ai_comment"=>"GCP PostgreSQL互換・列指向加速"],
        ["rank"=>49, "name"=>"Amazon DocumentDB","speed"=>"高速","scale"=>"大規模対応",    "distributed"=>"対応",      "ai_comment"=>"AWS上MongoDB互換マネージド"],
        ["rank"=>50, "name"=>"Azure Cosmos DB","speed"=>"超高速","scale"=>"無制限スケール", "distributed"=>"完全対応", "ai_comment"=>"マルチモデル・グローバル分散・SLA重視"],
        ["rank"=>51, "name"=>"Dgraph",          "speed"=>"高速",   "scale"=>"大規模対応",    "distributed"=>"完全対応",  "ai_comment"=>"GraphQL-Native分散グラフDB"],
        ["rank"=>52, "name"=>"Fauna",           "speed"=>"高速",   "scale"=>"大規模対応",    "distributed"=>"完全対応",  "ai_comment"=>"サーバーレス・ACID・グローバル分散"],
        ["rank"=>53, "name"=>"SurrealDB",       "speed"=>"超高速", "scale"=>"大規模対応",    "distributed"=>"完全対応",  "ai_comment"=>"マルチモデル・SQL+GraphQL+文書を統合"],
        ["rank"=>54, "name"=>"PouchDB",         "speed"=>"高速",   "scale"=>"小中規模向け",  "distributed"=>"対応",      "ai_comment"=>"ブラウザ・オフライン対応・CouchDB同期"],
        ["rank"=>55, "name"=>"CouchDB",         "speed"=>"普通",   "scale"=>"大規模対応",    "distributed"=>"完全対応",  "ai_comment"=>"HTTPネイティブ・MapReduceビュー"],
        ["rank"=>56, "name"=>"RavenDB",         "speed"=>"高速",   "scale"=>"大規模対応",    "distributed"=>"完全対応",  "ai_comment"=>"C#/.NET向けドキュメントDB・ACID対応"],
        ["rank"=>57, "name"=>"DocumentDB（AWS）","speed"=>"高速",  "scale"=>"大規模対応",    "distributed"=>"対応",      "ai_comment"=>"AWSマネージドMongoDB互換"],
        ["rank"=>58, "name"=>"Realm",           "speed"=>"超高速", "scale"=>"モバイル向け",  "distributed"=>"対応",      "ai_comment"=>"モバイルオフラインファーストDB・Atlas同期"],
        ["rank"=>59, "name"=>"Datomic",         "speed"=>"高速",   "scale"=>"大規模対応",    "distributed"=>"完全対応",  "ai_comment"=>"イミュータブル・時間軸クエリ・Clojure親和"],
        ["rank"=>60, "name"=>"Memcached",       "speed"=>"超高速", "scale"=>"大規模対応",    "distributed"=>"対応",      "ai_comment"=>"シンプル分散キャッシュ・Redisと双璧"],
        ["rank"=>61, "name"=>"Hazelcast",       "speed"=>"超高速", "scale"=>"超大規模対応",  "distributed"=>"完全対応",  "ai_comment"=>"Java分散キャッシュ・インメモリグリッド"],
        ["rank"=>62, "name"=>"Apache Ignite",   "speed"=>"超高速", "scale"=>"超大規模対応",  "distributed"=>"完全対応",  "ai_comment"=>"分散ACID+SQLサポートのインメモリプラット"],
        ["rank"=>63, "name"=>"GridGain",        "speed"=>"超高速", "scale"=>"超大規模対応",  "distributed"=>"完全対応",  "ai_comment"=>"Apache Ignite商用版・エンタープライズ向け"],
        ["rank"=>64, "name"=>"Tarantool",       "speed"=>"超高速", "scale"=>"大規模対応",    "distributed"=>"完全対応",  "ai_comment"=>"Luaスクリプト組込・インメモリDB+MQ"],
        ["rank"=>65, "name"=>"KeyDB",           "speed"=>"超高速", "scale"=>"大規模対応",    "distributed"=>"対応",      "ai_comment"=>"Redisマルチスレッド互換・高速版"],
        ["rank"=>66, "name"=>"Dragonfly",       "speed"=>"超高速", "scale"=>"大規模対応",    "distributed"=>"対応",      "ai_comment"=>"Redis互換・最新CPUで超高スループット"],
        ["rank"=>67, "name"=>"Valkey",          "speed"=>"超高速", "scale"=>"大規模対応",    "distributed"=>"完全対応",  "ai_comment"=>"Redisフォーク・Linux Foundation管理OSS"],
        ["rank"=>68, "name"=>"Typesense",       "speed"=>"超高速", "scale"=>"大規模対応",    "distributed"=>"対応",      "ai_comment"=>"OSS全文検索・Algolia代替・セルフホスト"],
        ["rank"=>69, "name"=>"Meilisearch",     "speed"=>"超高速", "scale"=>"大規模対応",    "distributed"=>"対応",      "ai_comment"=>"Rust製全文検索・開発者フレンドリーAPI"],
        ["rank"=>70, "name"=>"OpenSearch",      "speed"=>"超高速", "scale"=>"超大規模対応",  "distributed"=>"完全対応",  "ai_comment"=>"AWS ElasticSearchフォーク・OSS検索分析"],
        ["rank"=>71, "name"=>"Chroma",          "speed"=>"高速",   "scale"=>"大規模対応",    "distributed"=>"対応",      "ai_comment"=>"Python向けOSSベクトルDB・LangChain定番"],
        ["rank"=>72, "name"=>"Qdrant",          "speed"=>"超高速", "scale"=>"超大規模対応",  "distributed"=>"完全対応",  "ai_comment"=>"Rust製高速ベクトルDB・フィルタリング強力"],
        ["rank"=>73, "name"=>"Milvus",          "speed"=>"超高速", "scale"=>"超大規模対応",  "distributed"=>"完全対応",  "ai_comment"=>"大規模ベクトル特化・RAG・AI検索定番"],
        ["rank"=>74, "name"=>"pgvector",        "speed"=>"高速",   "scale"=>"大規模対応",    "distributed"=>"対応",      "ai_comment"=>"PostgreSQL拡張ベクトル検索・AI最小構成"],
        ["rank"=>75, "name"=>"Vespa",           "speed"=>"超高速", "scale"=>"超大規模対応",  "distributed"=>"完全対応",  "ai_comment"=>"Yahoo!発・検索+推薦+ベクトル統合基盤"],
        ["rank"=>76, "name"=>"Zilliz",          "speed"=>"超高速", "scale"=>"超大規模対応",  "distributed"=>"完全対応",  "ai_comment"=>"Milvusクラウド版・フルマネージドベクトルDB"],
        ["rank"=>77, "name"=>"LanceDB",         "speed"=>"超高速", "scale"=>"大規模対応",    "distributed"=>"対応",      "ai_comment"=>"組込型ベクトルDB・ローカルAI向け"],
        ["rank"=>78, "name"=>"Xata",            "speed"=>"高速",   "scale"=>"大規模対応",    "distributed"=>"対応",      "ai_comment"=>"PostgreSQL+全文検索+ベクトルのサーバーレスDB"],
        ["rank"=>79, "name"=>"Turso",           "speed"=>"超高速", "scale"=>"大規模対応",    "distributed"=>"完全対応",  "ai_comment"=>"SQLiteベースEdge分散DB・CDN展開"],
        ["rank"=>80, "name"=>"D1（Cloudflare）","speed"=>"超高速", "scale"=>"大規模対応",    "distributed"=>"完全対応",  "ai_comment"=>"CloudflareEdge SQLite・Workers統合"],
    ];

    return [
        "updated_at" => date("Y-m-d H:i:s"),
        "languages"  => $languages,
        "frameworks" => $frameworks,
        "databases"  => $databases,
    ];
}

/* ===== END: aruaru-tech-baseline.php ===== */


/* ===== BEGIN: aruaru-tech-sync.php ===== */

/**
 * TOP50 自動同期: 外部サイトの人気データで順位を更新し、
 * 未登録の技術名は OpenAI で紹介文を生成して追加します。
 *
 * 外部データ（APIキー不要）:
 *   - Stack Overflow タグ人気 (stackexchange.com)
 *   - DB-Engines ランキング (db-engines.com)
 *
 * 任意:
 *   - GITHUB_TOKEN … GitHub API レート制限緩和（未設定でも動作）
 *   - OPENAI_API_KEY … 新規技術の紹介文自動生成（未設定時は簡易テンプレート）
 */


if (!defined('OPENAI_API_KEY')) {
    define('OPENAI_API_KEY', getenv('OPENAI_API_KEY') ?: '');
}
if (!defined('GITHUB_TOKEN')) {
    define('GITHUB_TOKEN', getenv('GITHUB_TOKEN') ?: '');
}
if (!defined('AI_TECH_CACHE')) {
    define('AI_TECH_CACHE', __DIR__ . '/ai-tech-ranking-cache.json');
}
if (!defined('CRON_LOG')) {
    define('CRON_LOG', __DIR__ . '/cron.log');
}

function aruaru_tech_sync_log(string $msg, bool $echo = false): void
{
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
    if ($echo) {
        echo $line;
    }
    @file_put_contents(CRON_LOG, $line, FILE_APPEND | LOCK_EX);
}

function aruaru_tech_norm_key(string $name): string
{
    return strtolower(preg_replace('/[^a-z0-9]+/i', '', $name));
}

function aruaru_tech_http_get(string $url, int $timeout = 20): ?string
{
    $headers = "User-Agent: aruaru-tech-sync/1.0\r\nAccept: application/json,text/html\r\n";
    if (GITHUB_TOKEN !== '' && strpos($url, 'api.github.com') !== false) {
        $headers .= 'Authorization: Bearer ' . GITHUB_TOKEN . "\r\n";
    }
    $ctx = stream_context_create([
        'http' => [
            'method'  => 'GET',
            'timeout' => $timeout,
            'header'  => $headers,
            'ignore_errors' => true,
        ],
        'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
    ]);
    $raw = @file_get_contents($url, false, $ctx);
    return ($raw !== false && $raw !== '') ? $raw : null;
}

/** @return array<string, string> so_tag => 表示名 */
function aruaru_tech_so_language_map(): array
{
    return [
        'python' => 'Python', 'javascript' => 'JavaScript', 'java' => 'Java', 'c#' => 'C#',
        'php' => 'PHP', 'c++' => 'C++', 'c' => 'C', 'typescript' => 'TypeScript',
        'ruby' => 'Ruby', 'swift' => 'Swift', 'kotlin' => 'Kotlin', 'go' => 'Go', 'golang' => 'Go',
        'rust' => 'Rust', 'scala' => 'Scala', 'r' => 'R', 'dart' => 'Dart', 'elixir' => 'Elixir',
        'haskell' => 'Haskell', 'lua' => 'Lua', 'perl' => 'Perl', 'objective-c' => 'Objective-C',
        'groovy' => 'Groovy', 'clojure' => 'Clojure', 'f#' => 'F#', 'julia' => 'Julia',
        'zig' => 'Zig', 'nim' => 'Nim', 'crystal' => 'Crystal', 'ocaml' => 'OCaml',
        'erlang' => 'Erlang', 'matlab' => 'MATLAB', 'fortran' => 'Fortran', 'powershell' => 'PowerShell',
        'bash' => 'Bash', 'shell' => 'Bash', 'cobol' => 'COBOL', 'vba' => 'VBA', 'ada' => 'Ada',
        'solidity' => 'Solidity', 'sql' => 'SQL', 'assembly' => 'Assembly', 'prolog' => 'Prolog',
        'lisp' => 'Lisp', 'scheme' => 'Scheme', 'webassembly' => 'WebAssembly', 'wasm' => 'WebAssembly',
        'graphql' => 'GraphQL', 'terraform' => 'HCL', 'hcl' => 'HCL', 'mdx' => 'MDX',
        'move-lang' => 'Move', 'cairo' => 'Cairo',
    ];
}

/** @return array<string, string> */
function aruaru_tech_so_framework_map(): array
{
    return [
        'reactjs' => 'React', 'react-native' => 'React Native', 'next.js' => 'Next.js',
        'vue.js' => 'Vue.js', 'nuxt.js' => 'Nuxt.js', 'angular' => 'Angular', 'angularjs' => 'Angular',
        'svelte' => 'Svelte', 'django' => 'Django', 'flask' => 'Flask', 'fastapi' => 'FastAPI',
        'laravel' => 'Laravel', 'symfony' => 'Symfony', 'spring' => 'Spring Boot', 'spring-boot' => 'Spring Boot',
        'nestjs' => 'NestJS', 'express' => 'Express', 'node.js' => 'Express', 'hono' => 'Hono',
        'flutter' => 'Flutter', 'ruby-on-rails' => 'Ruby on Rails', 'rails' => 'Ruby on Rails',
        'gin' => 'Gin', 'echo' => 'Echo', 'fiber' => 'Fiber', 'axum' => 'Axum', 'actix' => 'Actix Web',
        'phoenix-framework' => 'Phoenix', 'phoenix' => 'Phoenix', 'blazor' => 'Blazor',
        'asp.net' => 'ASP.NET Core', 'asp.net-core' => 'ASP.NET Core', 'dotnet' => 'ASP.NET Core',
        'streamlit' => 'Streamlit', 'expo' => 'Expo', 'ionic-framework' => 'Ionic', 'ionic' => 'Ionic',
        'capacitor' => 'Capacitor', 'htmx' => 'HTMX', 'qwik' => 'Qwik', 'lit' => 'Lit',
        'preact' => 'Preact', 'celery' => 'Celery', 'cakephp' => 'CakePHP', 'playframework' => 'Play Framework',
        'akka' => 'Akka', 'micronaut' => 'Micronaut', 'quarkus' => 'Quarkus', 'remix' => 'Remix',
        'astro' => 'Astro', 'solidjs' => 'Solid.js', 'trpc' => 'tRPC', 'swiftui' => 'SwiftUI',
        'jetpack-compose' => 'Jetpack Compose', 'fastify' => 'Fastify', 'sveltekit' => 'SvelteKit',
        'tanstack-query' => 'TanStack Query', 'react-query' => 'TanStack Query',
    ];
}

/** @return array<string, string> */
function aruaru_tech_so_database_map(): array
{
    return [
        'postgresql' => 'PostgreSQL', 'mysql' => 'MySQL', 'redis' => 'Redis', 'mongodb' => 'MongoDB',
        'sqlite' => 'SQLite', 'elasticsearch' => 'Elasticsearch', 'cassandra' => 'Cassandra',
        'dynamodb' => 'DynamoDB', 'firebase' => 'Firestore', 'firestore' => 'Firestore',
        'mariadb' => 'MariaDB', 'oracle' => 'Oracle DB', 'sql-server' => 'SQL Server',
        'bigquery' => 'BigQuery', 'snowflake' => 'Snowflake', 'neo4j' => 'Neo4j',
        'influxdb' => 'InfluxDB', 'clickhouse' => 'ClickHouse', 'couchbase' => 'Couchbase',
        'duckdb' => 'DuckDB', 'timescaledb' => 'TimescaleDB', 'cockroachdb' => 'CockroachDB',
        'supabase' => 'Supabase', 'planetscale' => 'PlanetScale', 'tidb' => 'TiDB',
        'weaviate' => 'Weaviate', 'pinecone' => 'Pinecone', 'graphql-database' => 'FaunaDB',
        'fauna' => 'FaunaDB', 'arangodb' => 'ArangoDB', 'yugabytedb' => 'YugabyteDB',
        'scylladb' => 'ScyllaDB', 'hbase' => 'Apache HBase', 'druid' => 'Apache Druid',
        'cosmosdb' => 'Azure Cosmos DB', 'documentdb' => 'Amazon DocumentDB',
    ];
}

/**
 * Stack Overflow 人気タグ → スコア配列 [normKey => score]
 * @param array<string, string> $tagMap
 * @return array{scores: array<string, float>, source: string}
 */
function aruaru_tech_fetch_stackoverflow(array $tagMap, int $pages = 2): array
{
    $scores = [];
    for ($page = 1; $page <= $pages; $page++) {
        $url = 'https://api.stackexchange.com/2.3/tags?order=desc&sort=popular&site=stackoverflow'
            . '&pagesize=100&page=' . $page;
        $raw = aruaru_tech_http_get($url);
        if ($raw === null) {
            break;
        }
        $data = json_decode($raw, true);
        if (!is_array($data['items'] ?? null)) {
            break;
        }
        foreach ($data['items'] as $item) {
            $tag = strtolower((string)($item['name'] ?? ''));
            $count = (float)($item['count'] ?? 0);
            if ($tag === '' || $count <= 0) {
                continue;
            }
            $canonical = $tagMap[$tag] ?? null;
            if ($canonical === null) {
                continue;
            }
            $key = aruaru_tech_norm_key($canonical);
            $scores[$key] = max($scores[$key] ?? 0, $count);
        }
        usleep(300000);
    }
    return ['scores' => $scores, 'source' => 'stackoverflow.com'];
}

/** @return array{scores: array<string, float>, source: string} */
function aruaru_tech_fetch_dbengines(): array
{
    $scores = [];
    $raw = aruaru_tech_http_get('https://db-engines.com/en/ranking', 25);
    if ($raw === null) {
        return ['scores' => [], 'source' => 'db-engines.com (fetch failed)'];
    }
    if (preg_match_all('/<a\s+href="\/en\/sys\/[^"]+">([^<]+)<\/a>/i', $raw, $m)) {
        $rank = 0;
        foreach ($m[1] as $name) {
            $name = trim(html_entity_decode($name, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if ($name === '') {
                continue;
            }
            $rank++;
            $key = aruaru_tech_norm_key($name);
            $scores[$key] = max($scores[$key] ?? 0, (51 - min($rank, 50)) * 10000);
            if ($rank >= 60) {
                break;
            }
        }
    }
    return ['scores' => $scores, 'source' => 'db-engines.com'];
}

/** @return array{languages: array, frameworks: array, databases: array, sources: list<string>, errors: list<string>} */
function aruaru_tech_fetch_external_rankings(): array
{
    $sources = [];
    $errors = [];

    $langSo = aruaru_tech_fetch_stackoverflow(aruaru_tech_so_language_map(), 3);
    if (!empty($langSo['scores'])) {
        $sources[] = $langSo['source'];
    } else {
        $errors[] = 'Stack Overflow (languages): no data';
    }

    $fwSo = aruaru_tech_fetch_stackoverflow(aruaru_tech_so_framework_map(), 3);
    if (!empty($fwSo['scores'])) {
        $sources[] = $fwSo['source'];
    } else {
        $errors[] = 'Stack Overflow (frameworks): no data';
    }

    $dbSo = aruaru_tech_fetch_stackoverflow(aruaru_tech_so_database_map(), 2);
    $dbEng = aruaru_tech_fetch_dbengines();
    $dbScores = $dbEng['scores'];
    foreach ($dbSo['scores'] as $k => $v) {
        $dbScores[$k] = ($dbScores[$k] ?? 0) + $v * 0.3;
    }
    if (!empty($dbScores)) {
        $sources[] = $dbEng['source'];
        if (!empty($dbSo['scores'])) {
            $sources[] = $dbSo['source'] . ' (DB tags)';
        }
    } else {
        $errors[] = 'DB rankings: no external data';
    }

    return [
        'languages'  => $langSo['scores'],
        'frameworks' => $fwSo['scores'],
        'databases'  => $dbScores,
        'sources'    => array_values(array_unique($sources)),
        'errors'     => $errors,
    ];
}

/**
 * ベースライン + 外部スコアで TOP50 を再構成（既存行の紹介文は原則維持）
 * @param array<string, float> $externalScores
 */
function aruaru_tech_build_top50(string $category, array $baselineRows, array $externalScores): array
{
    $seedByKey = [];
    $nameByKey = [];
    foreach ($baselineRows as $row) {
        $key = aruaru_tech_norm_key((string)$row['name']);
        $seedByKey[$key] = $row;
        $nameByKey[$key] = (string)$row['name'];
    }

    $keys = array_unique(array_merge(array_keys($externalScores), array_keys($seedByKey)));
    $combined = [];
    foreach ($keys as $key) {
        $ext = $externalScores[$key] ?? 0;
        $baseRank = isset($seedByKey[$key]) ? (int)($seedByKey[$key]['rank'] ?? 99) : 99;
        $baseScore = max(0, 51 - min($baseRank, 50)) * 500;
        $combined[$key] = $ext + $baseScore;
    }
    arsort($combined, SORT_NUMERIC);
    // 言語カテゴリはベースライン100件を確保済みのためTOP100、FW/DBは従来通りTOP80（HANDOFF参照）
    $limit = ($category === 'language') ? 100 : 80;
    $orderedKeys = array_slice(array_keys($combined), 0, $limit);

    $out = [];
    $rank = 0;
    foreach ($orderedKeys as $key) {
        $rank++;
        if (isset($seedByKey[$key])) {
            $row = $seedByKey[$key];
            $row['rank'] = $rank;
            if ($category === 'framework' && empty($row['memory'])) {
                $row['memory'] = '中程度';
            }
            if ($category === 'database' && empty($row['memory'])) {
                $row['memory'] = '中〜大';
            }
            $out[] = $row;
            continue;
        }
        $display = $nameByKey[$key] ?? ucwords(str_replace(['-', '_'], ' ', $key));
        foreach ($externalScores as $ek => $ev) {
            if ($ek === $key && !isset($nameByKey[$key])) {
                $display = $display;
            }
        }
        $out[] = ['rank' => $rank, 'name' => $display, '_needs_ai' => true, '_key' => $key];
    }
    return $out;
}

function aruaru_tech_language_complete(array $row): bool
{
    $req = ['team_dev', 'maintenance', 'beginner', 'speed', 'dev_scale', 'traits', 'oss_note', 'async_support', 'throughput_100k', 'memory', 'ai_comment'];
    foreach ($req as $f) {
        if (empty($row[$f])) {
            return false;
        }
    }
    return true;
}

/**
 * 拡張評価軸（2026-07-20追加）が揃っているか。
 * total_scoreは0も正当値になり得るためisset判定、他はempty判定。
 */
function aruaru_tech_language_extended_complete(array $row): bool
{
    $req = [
        'score_async', 'score_speed', 'score_security', 'versionless_api_comment',
        'score_ai_library', 'score_other_library', 'framework_note', 'database_note',
        'score_spec_change_resilience', 'score_parallel_distributed',
        'cockroachdb_similarity_comment', 'snowflake_similarity_comment', 'aruaru_db_similarity_comment',
    ];
    foreach ($req as $f) {
        if (!array_key_exists($f, $row) || $row[$f] === '' || $row[$f] === null) {
            return false;
        }
    }
    return isset($row['total_score']);
}

function aruaru_tech_framework_complete(array $row): bool
{
    $req = ['team_dev', 'maintenance', 'beginner', 'speed', 'large_scale', 'memory', 'ai_comment'];
    foreach ($req as $f) {
        if (empty($row[$f])) {
            return false;
        }
    }
    return true;
}

function aruaru_tech_database_complete(array $row): bool
{
    $req = ['speed', 'scale', 'distributed', 'memory', 'ai_comment'];
    foreach ($req as $f) {
        if (empty($row[$f])) {
            return false;
        }
    }
    return true;
}

function aruaru_tech_fallback_row(string $category, string $name): array
{
    if ($category === 'language') {
        return [
            'rank' => 0, 'name' => $name,
            'team_dev' => '要調査', 'maintenance' => '要調査', 'beginner' => '要調査',
            'speed' => '要調査', 'dev_scale' => '要調査', 'traits' => '外部ランキングで新規検出。詳細は公式ドキュメントを参照。',
            'oss_note' => '要調査', 'async_support' => '要調査', 'throughput_100k' => '要調査',
            'memory' => '中程度', 'ai_comment' => '外部データで検出（OPENAI_API_KEY未設定のため簡易表示）',
        ];
    }
    if ($category === 'framework') {
        return [
            'rank' => 0, 'name' => $name,
            'team_dev' => '要調査', 'maintenance' => '要調査', 'beginner' => '要調査',
            'speed' => '要調査', 'large_scale' => '要調査', 'memory' => '中程度',
            'ai_comment' => '外部データで検出（OPENAI_API_KEY未設定）',
        ];
    }
    return [
        'rank' => 0, 'name' => $name,
        'speed' => '要調査', 'scale' => '要調査', 'distributed' => '要調査',
        'memory' => '中〜大', 'ai_comment' => '外部データで検出（OPENAI_API_KEY未設定）',
    ];
}

/**
 * @param list<string> $names
 * @return array<string, array> keyed by norm name
 */
function aruaru_tech_openai_enrich(string $category, array $names): array
{
    if ($names === [] || OPENAI_API_KEY === '') {
        return [];
    }

    $list = implode(', ', array_slice($names, 0, 12));
    $schema = match ($category) {
        'language' => '{"name":"","team_dev":"","maintenance":"","beginner":"","speed":"","dev_scale":"","traits":"","oss_note":"","async_support":"","throughput_100k":"","memory":"","ai_comment":""}',
        'framework' => '{"name":"","team_dev":"","maintenance":"","beginner":"","speed":"","large_scale":"","memory":"","ai_comment":""}',
        default => '{"name":"","speed":"","scale":"","distributed":"","memory":"","ai_comment":""}',
    };

    $prompt = "2026年時点の日本のITエンジニア向けに、次の{$category}についてJSON配列のみで回答してください（説明文禁止）。\n"
        . "対象: {$list}\n"
        . "各要素のスキーマ: {$schema}\n"
        . "traitsは日本語80字以内。ai_commentは日本語60字以内。memoryは「極小/小容量/中程度/大容量必要」などから選ぶ。";

    $payload = json_encode([
        'model' => 'gpt-4o-mini',
        'temperature' => 0.35,
        'max_tokens' => 3500,
        'messages' => [
            ['role' => 'system', 'content' => 'JSON配列のみ返す。'],
            ['role' => 'user', 'content' => $prompt],
        ],
    ], JSON_UNESCAPED_UNICODE);

    $ctx = stream_context_create(['http' => [
        'method' => 'POST',
        'timeout' => 45,
        'header' => "Content-Type: application/json\r\nAuthorization: Bearer " . OPENAI_API_KEY . "\r\n",
        'content' => $payload,
        'ignore_errors' => true,
    ]]);
    $raw = @file_get_contents('https://api.openai.com/v1/chat/completions', false, $ctx);
    if (!$raw) {
        return [];
    }
    $resp = json_decode($raw, true);
    $text = trim((string)($resp['choices'][0]['message']['content'] ?? ''));
    $text = preg_replace('/^```json\s*|\s*```$/i', '', $text);
    $arr = json_decode($text, true);
    if (!is_array($arr)) {
        return [];
    }

    $out = [];
    foreach ($arr as $item) {
        if (!is_array($item) || empty($item['name'])) {
            continue;
        }
        $out[aruaru_tech_norm_key((string)$item['name'])] = $item;
    }
    return $out;
}

/**
 * @param list<array> $rows
 * @return list<array>
 */
function aruaru_tech_apply_ai_enrichment(string $category, array $rows): array
{
    $need = [];
    foreach ($rows as $i => $row) {
        $complete = match ($category) {
            'language' => aruaru_tech_language_complete($row),
            'framework' => aruaru_tech_framework_complete($row),
            default => aruaru_tech_database_complete($row),
        };
        if (!$complete || !empty($row['_needs_ai'])) {
            $need[] = (string)$row['name'];
        }
    }
    $need = array_values(array_unique($need));
    if ($need === []) {
        return array_map(static function ($r) {
            unset($r['_needs_ai'], $r['_key']);
            return $r;
        }, $rows);
    }

    $enriched = [];
    foreach (array_chunk($need, 10) as $chunk) {
        $batch = aruaru_tech_openai_enrich($category, $chunk);
        $enriched = array_merge($enriched, $batch);
        if (OPENAI_API_KEY !== '' && count($chunk) > 1) {
            usleep(500000);
        }
    }

    foreach ($rows as $i => $row) {
        $key = aruaru_tech_norm_key((string)$row['name']);
        $complete = match ($category) {
            'language' => aruaru_tech_language_complete($row),
            'framework' => aruaru_tech_framework_complete($row),
            default => aruaru_tech_database_complete($row),
        };
        if ($complete && empty($row['_needs_ai'])) {
            unset($rows[$i]['_needs_ai'], $rows[$i]['_key']);
            continue;
        }
        $ai = $enriched[$key] ?? null;
        if (is_array($ai)) {
            $rows[$i] = array_merge($row, $ai, ['name' => $row['name'], 'rank' => $row['rank']]);
        } else {
            $rows[$i] = array_merge(aruaru_tech_fallback_row($category, (string)$row['name']), $row, ['rank' => $row['rank']]);
        }
        unset($rows[$i]['_needs_ai'], $rows[$i]['_key']);
    }
    return $rows;
}

/* =========================================================
   ★ 拡張評価軸（2026-07-20追加、ユーザー指示による再定義）
   非同期・ハイスピード・セキュリティ・VersionlessAPI・AI/その他ライブラリ・
   フレームワーク/DB相性・仕様変更耐性・並列分散性・CockroachDB/Snowflake/
   aruaru-db類似性・合計スコア。
   ★ 正直な開示: これらのスコアは StackOverflow/DB-Engines のような
   「実際にリアルタイム取得できる外部指標」ではなく、各言語について
   一般的に広く知られた技術的特性（既存の async_support/traits/oss_note
   等の記述、公式ドキュメント・コミュニティで広く共有されている評価傾向）
   に基づく、ルールベースの知見スコアです。事実（外部API取得値）と
   推測（知見ベース評価）を混同しないよう、ranking_meta.extended_axes_note
   および表示側（index.php本体のHTML）に明記します。
========================================================= */

if (!defined('ARUARU_LLM_ENDPOINT')) {
    // aruaru-llm（F:\open-runo\aruaru-llm、Rust+Poem製）のHTTPエンドポイント。
    // 未起動/未到達の場合は自動でルールベースにフォールバックする。
    define('ARUARU_LLM_ENDPOINT', getenv('ARUARU_LLM_ENDPOINT') ?: 'http://127.0.0.1:4600');
}

/**
 * aruaru-llm（実在する自前サービス、F:\open-runo\aruaru-llm）の /v1/chat を実際に呼び出す。
 * 本物のニューラル推論ではなくルールベースの意図分類サービスであることは
 * aruaru-llm自身のCLAUDE.mdで開示済み。ここではその開示を継承しつつ、
 * 「実際に呼べるなら呼ぶ」本物のHTTPクライアント関数として実装する
 * （todo!()や固定文字列を返すだけのスタブではない）。
 * 到達不能・タイムアウト・不正レスポンスの場合は正直にnullを返す。
 */
function aruaru_tech_call_aruaru_llm(string $message, string $tenant = 'audiocafe.tokyo', int $timeoutSec = 2): ?array
{
    $payload = json_encode(['message' => $message, 'tenant' => $tenant], JSON_UNESCAPED_UNICODE);
    if ($payload === false) {
        return null;
    }
    $ctx = stream_context_create([
        'http' => [
            'method' => 'POST',
            'timeout' => $timeoutSec,
            'header' => "Content-Type: application/json\r\nAccept: application/json\r\n",
            'content' => $payload,
            'ignore_errors' => true,
        ],
    ]);
    $raw = @file_get_contents(rtrim(ARUARU_LLM_ENDPOINT, '/') . '/v1/chat', false, $ctx);
    if ($raw === false || $raw === '') {
        return null;
    }
    $resp = json_decode($raw, true);
    if (!is_array($resp) || empty($resp['reply'])) {
        return null;
    }
    return $resp;
}

/**
 * 主要言語の拡張評価ナレッジベース（手動キュレーション、キーはaruaru_tech_norm_key）。
 * ここに無い言語は aruaru_tech_extended_heuristic() でベースラインの既存記述
 * （async_support/speed/traits/oss_note等）からルールベースで算出する。
 * @return array<string, array<string, int|string>>
 */
function aruaru_tech_extended_knowledge_base(): array
{
    $db = static fn(string $note) => $note . '（相性検証用リファレンス実装: aruaru-db https://github.com/aon-co-jp/aruaru-db ）';
    $rows = [
        'typescript' => ['score_async'=>88,'score_speed'=>78,'score_security'=>82,'versionless_api_comment'=>'Next.js/HonoのAPIルートはパスやヘッダで容易にVersionless API化でき、型定義自体がバージョン管理・Gitのコミット履歴と強く結び付くため親和性が高い。','score_ai_library'=>72,'score_other_library'=>93,'framework_note'=>'TypeScript: Next.js / NestJS / Hono','database_note'=>$db('Prisma/Drizzle等の型安全ORMが充実し主要DBと広く連携可能'),'score_spec_change_resilience'=>85,'score_parallel_distributed'=>72,'cockroachdb_similarity_comment'=>'言語自体に分散合意機構はないが、型システムによる契約の厳格さはCockroachDBのスキーマ一貫性思想と親和的。','snowflake_similarity_comment'=>'Snowflakeのような宣言的・スキーマ中心の設計とは異なるが、型定義がスキーマ的な役割を担う点で発想は近い。','aruaru_db_similarity_comment'=>'aruaru-dbのRust製サーバに対し型安全なクライアントを書きやすく、フロント〜API境界の型共有という点で相性が良い。'],
        'python' => ['score_async'=>75,'score_speed'=>55,'score_security'=>68,'versionless_api_comment'=>'FastAPIはパスバージョニングと非バージョニング設計の両方をサポートし、型ヒント＋Gitタグ運用との組み合わせが一般的。','score_ai_library'=>99,'score_other_library'=>90,'framework_note'=>'Python: Django / FastAPI','database_note'=>$db('SQLAlchemy等の成熟したORMがあり主要RDB/NoSQLとも広く連携'),'score_spec_change_resilience'=>60,'score_parallel_distributed'=>65,'cockroachdb_similarity_comment'=>'言語自体の分散機構はないが、Pythonから分散DBを操作するクライアントエコシステムは非常に成熟している。','snowflake_similarity_comment'=>'pandas/Sparkとの連携が強く、Snowflake上の大規模データ処理パイプライン言語として広く採用される。','aruaru_db_similarity_comment'=>'データ分析用途でaruaru-dbへの接続クライアントを書く言語として第一候補になりやすい。'],
        'javascript' => ['score_async'=>85,'score_speed'=>70,'score_security'=>55,'versionless_api_comment'=>'ExpressベースのAPIはVersionless化しやすいが、型が無い分バージョン管理・Git運用側での契約管理の比重がTypeScriptより高くなる。','score_ai_library'=>60,'score_other_library'=>93,'framework_note'=>'JavaScript: Express / Next.js','database_note'=>$db('主要DB向けドライバ/ORMがほぼ全て揃うが型安全性はTypeScript移行を推奨'),'score_spec_change_resilience'=>45,'score_parallel_distributed'=>68,'cockroachdb_similarity_comment'=>'直接の類似性は薄いが、Node.jsの非同期I/Oモデルは分散システムのイベント駆動設計と親和性がある。','snowflake_similarity_comment'=>'直接の類似性はほぼ無く、主にダッシュボード/BIツールのフロント実装言語として関わる。','aruaru_db_similarity_comment'=>'フロントエンドからaruaru-dbのAPI層を呼び出す用途で使われることが多い。'],
        'go' => ['score_async'=>90,'score_speed'=>90,'score_security'=>80,'versionless_api_comment'=>'Goのモジュールシステム（go.mod）はSemVerと密結合だが、APIパスのVersionless化自体は容易でGit運用とも親和性が高い。','score_ai_library'=>45,'score_other_library'=>75,'framework_note'=>'Go: Gin / Echo / Fiber','database_note'=>$db('database/sql標準＋各種ドライバが成熟し高スループットな接続プーリングが可能'),'score_spec_change_resilience'=>78,'score_parallel_distributed'=>92,'cockroachdb_similarity_comment'=>'CockroachDB自体がGoで実装されており、goroutine/channelによる並行処理モデルはCockroachDBの分散設計思想と直接的に近い。','snowflake_similarity_comment'=>'直接の類似性は薄いが、大規模データパイプラインのマイクロサービス実装言語として併用されることが多い。','aruaru_db_similarity_comment'=>'aruaru-db（Rust製）とは実装言語こそ異なるが、シンプルさと高スループット志向という設計思想は近い。'],
        'rust' => ['score_async'=>93,'score_speed'=>98,'score_security'=>97,'versionless_api_comment'=>'Cargoのセマンティックバージョニングは厳格だが、Web APIのVersionless設計はAxum/Actixで容易に実現でき、型と借用チェッカーがバージョン間の破壊的変更を検出しやすい。','score_ai_library'=>55,'score_other_library'=>78,'framework_note'=>'Rust: Actix Web / Axum','database_note'=>$db('sqlx/SeaORM等コンパイル時検証付きの型安全クライアントが充実'),'score_spec_change_resilience'=>90,'score_parallel_distributed'=>90,'cockroachdb_similarity_comment'=>'メモリ安全性を保ちながら高スループットな分散処理を書ける点でCockroachDBが目指す信頼性設計と思想的に近い。','snowflake_similarity_comment'=>'直接の類似性は薄いが、高速なデータ処理基盤（例: DataFusion等）の実装言語として採用が増えている。','aruaru_db_similarity_comment'=>'aruaru-db自体がRust製であるため相性は最も高く、同一言語での型共有・エラーハンドリング統一が可能。'],
        'java' => ['score_async'=>70,'score_speed'=>80,'score_security'=>83,'versionless_api_comment'=>'Spring BootのAPIバージョニングは伝統的にパス/ヘッダ方式が主流だが、契約テスト導入によりVersionless運用も可能。Mavenのバージョン管理とGitタグ運用の組み合わせが一般的。','score_ai_library'=>58,'score_other_library'=>95,'framework_note'=>'Java: Spring Boot / Micronaut / Quarkus','database_note'=>$db('JDBC＋Hibernate等の成熟したORMがあり大規模基幹システムでの実績が豊富'),'score_spec_change_resilience'=>80,'score_parallel_distributed'=>80,'cockroachdb_similarity_comment'=>'JVMの成熟した並行処理ライブラリ群は、CockroachDBのような分散トランザクション処理系のクライアント実装に向く。','snowflake_similarity_comment'=>'エンタープライズBI/ETL基盤（JDBC経由）としてSnowflakeとの連携実績が非常に豊富。','aruaru_db_similarity_comment'=>'大企業の既存Javaシステムからaruaru-dbへ接続するブリッジ役として現実的な選択肢。'],
        'kotlin' => ['score_async'=>85,'score_speed'=>78,'score_security'=>84,'versionless_api_comment'=>'KtorのAPIはVersionless設計がしやすく、Null安全な型システムがバージョン間の破壊的変更検知に役立つ。','score_ai_library'=>40,'score_other_library'=>80,'framework_note'=>'Kotlin: Ktor / Spring Boot','database_note'=>$db('Exposed等のKotlin製ORMがあり、JVM資産のJDBCドライバもそのまま使える'),'score_spec_change_resilience'=>82,'score_parallel_distributed'=>78,'cockroachdb_similarity_comment'=>'コルーチンによる軽量並行処理は、CockroachDBのようなマルチノードDBへの高並列アクセスと相性が良い。','snowflake_similarity_comment'=>'直接の類似性は薄く、主にAndroid/バックエンドの実装言語としてBI基盤とは間接的に関わる。','aruaru_db_similarity_comment'=>'JVM経由でaruaru-dbクライアントを実装する際の型安全な選択肢。'],
        'swift' => ['score_async'=>82,'score_speed'=>85,'score_security'=>85,'versionless_api_comment'=>'iOSアプリからのAPI呼び出しはVersionless設計が主流になりつつあり、Swift Package ManagerのSemVerとGit運用が密接。','score_ai_library'=>35,'score_other_library'=>60,'framework_note'=>'Swift: Vapor / SwiftUI','database_note'=>$db('Vapor Fluent等のORMを介してPostgreSQL等主要DBに接続可能'),'score_spec_change_resilience'=>75,'score_parallel_distributed'=>65,'cockroachdb_similarity_comment'=>'直接の類似性は薄いが、Swift ConcurrencyのActorモデルは分散DBのノード分離設計と発想が近い。','snowflake_similarity_comment'=>'直接の類似性はほぼ無い。','aruaru_db_similarity_comment'=>'モバイルアプリからaruaru-db連携APIを呼び出すクライアント言語として使われる想定。'],
        'c#' => ['score_async'=>90,'score_speed'=>82,'score_security'=>82,'versionless_api_comment'=>'ASP.NET CoreはAPI Versioning用ライブラリが充実する一方、Versionless設計もMinimal APIで容易。NuGetのSemVerとGit運用が定着。','score_ai_library'=>50,'score_other_library'=>90,'framework_note'=>'C#: ASP.NET Core / Blazor','database_note'=>$db('Entity Framework Core等の成熟したORMで主要DBと広く連携'),'score_spec_change_resilience'=>80,'score_parallel_distributed'=>78,'cockroachdb_similarity_comment'=>'async/awaitの成熟度は高く、分散トランザクション処理のクライアント実装に十分な性能を発揮できる。','snowflake_similarity_comment'=>'エンタープライズBIツールとの連携実績があり、間接的な親和性がある。','aruaru_db_similarity_comment'=>'.NET資産を持つ企業がaruaru-dbへ接続する際の現実的な選択肢。'],
        'php' => ['score_async'=>50,'score_speed'=>60,'score_security'=>65,'versionless_api_comment'=>'Laravel/Symfonyでのバージョニングはパス方式が主流だが、Versionless API＋Gitタグでのリリース管理も一般的に採用される。','score_ai_library'=>25,'score_other_library'=>85,'framework_note'=>'PHP: Laravel / Symfony','database_note'=>$db('PDO/Eloquent等が成熟し中小規模Webサービスとの相性が良い'),'score_spec_change_resilience'=>55,'score_parallel_distributed'=>40,'cockroachdb_similarity_comment'=>'直接の類似性はほぼ無く、PHPは主に単一リクエスト完結型のWeb処理が中心。','snowflake_similarity_comment'=>'直接の類似性はほぼ無い。','aruaru_db_similarity_comment'=>'既存のPHP資産（本リポジトリ含む）からaruaru-dbのクライアントを実装する際の橋渡し役になり得る。'],
        'ruby' => ['score_async'=>55,'score_speed'=>50,'score_security'=>60,'versionless_api_comment'=>'Rails APIモードはバージョニング用gemが豊富だが、Versionless設計＋Gitのタグ運用に寄せることも多い。','score_ai_library'=>30,'score_other_library'=>78,'framework_note'=>'Ruby: Ruby on Rails','database_note'=>$db('ActiveRecordが非常に成熟し主要RDBとの相性は良い'),'score_spec_change_resilience'=>50,'score_parallel_distributed'=>35,'cockroachdb_similarity_comment'=>'直接の類似性はほぼ無い。','snowflake_similarity_comment'=>'直接の類似性はほぼ無い。','aruaru_db_similarity_comment'=>'スタートアップ文化との親和性が高く、aruaru-dbを使う小規模サービスの初期実装言語になり得る。'],
        'c++' => ['score_async'=>70,'score_speed'=>97,'score_security'=>55,'versionless_api_comment'=>'言語自体にAPIの概念は薄いが、ABI互換性維持の考え方はVersionless設計・Git上のタグ運用と密接に関わる。','score_ai_library'=>65,'score_other_library'=>70,'framework_note'=>'C++: Qt / Boost.Beast','database_note'=>$db('libpqxx等の低レベルドライバで高速アクセスが可能だが実装コストは高め'),'score_spec_change_resilience'=>60,'score_parallel_distributed'=>80,'cockroachdb_similarity_comment'=>'低レベル制御による高性能分散処理の実装言語として、ストレージエンジン層での親和性がある。','snowflake_similarity_comment'=>'直接の類似性は薄いが、高速な数値計算エンジンの実装言語として間接的に関わる。','aruaru_db_similarity_comment'=>'高速なネイティブクライアントが必要な場面でのaruaru-db接続実装候補。'],
        'c' => ['score_async'=>40,'score_speed'=>99,'score_security'=>40,'versionless_api_comment'=>'言語自体にAPIバージョニングの概念は無く、ヘッダファイルとABI互換性の管理がGit運用に強く依存する。','score_ai_library'=>30,'score_other_library'=>50,'framework_note'=>'C: libuv / libpq（低レベルドライバ）','database_note'=>$db('低レベルドライバ経由で最速アクセスが可能だが安全性は開発者依存'),'score_spec_change_resilience'=>35,'score_parallel_distributed'=>60,'cockroachdb_similarity_comment'=>'直接の類似性は薄いが、多くのDBエンジン自体がCまたはC系言語で実装されてきた歴史的経緯がある。','snowflake_similarity_comment'=>'直接の類似性はほぼ無い。','aruaru_db_similarity_comment'=>'最速のネイティブクライアントを書く必要がある特殊用途での選択肢。'],
        'scala' => ['score_async'=>82,'score_speed'=>80,'score_security'=>78,'versionless_api_comment'=>'Akka HTTP等でのVersionless API設計は容易で、強力な型システムが破壊的変更の検知に貢献する。','score_ai_library'=>55,'score_other_library'=>82,'framework_note'=>'Scala: Akka HTTP / Play Framework','database_note'=>$db('Slick等のORMでJVM資産のJDBCドライバを型安全に利用可能'),'score_spec_change_resilience'=>82,'score_parallel_distributed'=>88,'cockroachdb_similarity_comment'=>'Akkaのアクターモデルは分散合意・耐障害設計という点でCockroachDBの思想と親和的。','snowflake_similarity_comment'=>'Spark（Scala製）の主要言語であり、Snowflakeとのデータパイプライン連携実績が豊富。','aruaru_db_similarity_comment'=>'大規模分散データ処理パイプラインからaruaru-dbへ接続する用途に向く。'],
        'elixir' => ['score_async'=>95,'score_speed'=>75,'score_security'=>75,'versionless_api_comment'=>'Phoenixの設計思想はVersionless APIと親和性が高く、BEAMのホットコードスワップはバージョン管理の考え方自体を柔軟にする。','score_ai_library'=>25,'score_other_library'=>55,'framework_note'=>'Elixir: Phoenix','database_note'=>$db('Ectoが成熟しPostgreSQLとの相性は特に良い'),'score_spec_change_resilience'=>78,'score_parallel_distributed'=>93,'cockroachdb_similarity_comment'=>'BEAM由来の軽量プロセス・分散ノード間通信はCockroachDBの分散合意アーキテクチャと発想が非常に近い。','snowflake_similarity_comment'=>'直接の類似性は薄い。','aruaru_db_similarity_comment'=>'大量の同時接続をさばくリアルタイムアプリからaruaru-dbへ接続する用途に適する。'],
        'erlang' => ['score_async'=>95,'score_speed'=>72,'score_security'=>75,'versionless_api_comment'=>'OTPのホットコードローディングはバージョンレスな運用と極めて親和性が高い、業界でも先駆的な設計。','score_ai_library'=>20,'score_other_library'=>45,'framework_note'=>'Erlang: Cowboy','database_note'=>$db('Mnesia等の組込DBを持つが外部RDB連携はドライバ経由'),'score_spec_change_resilience'=>75,'score_parallel_distributed'=>95,'cockroachdb_similarity_comment'=>'「Let it crash」思想と分散耐障害設計は、CockroachDBの自己修復型アーキテクチャと極めて近い発想。','snowflake_similarity_comment'=>'直接の類似性はほぼ無い。','aruaru_db_similarity_comment'=>'超高並列の通信基盤（電話交換機由来）としての知見はaruaru-dbの高負荷設計と参考になり得る。'],
        'dart' => ['score_async'=>85,'score_speed'=>70,'score_security'=>70,'versionless_api_comment'=>'Flutterアプリからの通信はVersionless設計が主流で、pub.devのSemVer運用がGitタグと連動する。','score_ai_library'=>25,'score_other_library'=>60,'framework_note'=>'Dart: Flutter','database_note'=>$db('drift等のORMでSQLiteとの相性が良い'),'score_spec_change_resilience'=>65,'score_parallel_distributed'=>55,'cockroachdb_similarity_comment'=>'直接の類似性はほぼ無い。','snowflake_similarity_comment'=>'直接の類似性はほぼ無い。','aruaru_db_similarity_comment'=>'モバイルアプリからaruaru-dbへ接続するクライアント言語として使われる想定。'],
        'haskell' => ['score_async'=>78,'score_speed'=>75,'score_security'=>88,'versionless_api_comment'=>'型クラスによる契約の厳格さはVersionless API設計時の後方互換性検証に強く貢献する。','score_ai_library'=>30,'score_other_library'=>50,'framework_note'=>'Haskell: Servant / Yesod','database_note'=>$db('Persistent/Esqueleto等の型安全ORMがある'),'score_spec_change_resilience'=>88,'score_parallel_distributed'=>75,'cockroachdb_similarity_comment'=>'STM（ソフトウェアトランザクショナルメモリ）の思想は分散トランザクションの整合性設計と親和的。','snowflake_similarity_comment'=>'直接の類似性はほぼ無い。','aruaru_db_similarity_comment'=>'型安全性を重視する開発チームがaruaru-dbクライアントの正しさを検証する用途に向く。'],
        'perl' => ['score_async'=>45,'score_speed'=>55,'score_security'=>45,'versionless_api_comment'=>'古典的なCGI/Mojoliciousの設計ではAPIバージョニングは手動管理が中心で、Git運用への依存度が高い。','score_ai_library'=>15,'score_other_library'=>65,'framework_note'=>'Perl: Mojolicious','database_note'=>$db('DBIが長年の実績を持ち主要DBに広く対応'),'score_spec_change_resilience'=>40,'score_parallel_distributed'=>30,'cockroachdb_similarity_comment'=>'直接の類似性はほぼ無い。','snowflake_similarity_comment'=>'直接の類似性はほぼ無い。','aruaru_db_similarity_comment'=>'レガシーシステムの保守スクリプトからaruaru-dbへ接続する用途は限定的。'],
        'lua' => ['score_async'=>60,'score_speed'=>78,'score_security'=>55,'versionless_api_comment'=>'組込みスクリプトとしての性質上APIバージョニングの概念は薄く、ホストアプリ側のGit管理に依存する。','score_ai_library'=>15,'score_other_library'=>40,'framework_note'=>'Lua: OpenResty','database_note'=>$db('LuaSQL等のドライバがあるが用途は組込み・ゲーム中心'),'score_spec_change_resilience'=>45,'score_parallel_distributed'=>35,'cockroachdb_similarity_comment'=>'直接の類似性はほぼ無い。','snowflake_similarity_comment'=>'直接の類似性はほぼ無い。','aruaru_db_similarity_comment'=>'組込み用途中心のためaruaru-dbとの直接連携は限定的。'],
        'clojure' => ['score_async'=>75,'score_speed'=>72,'score_security'=>75,'versionless_api_comment'=>'不変データ構造とマルチバーシティ設計はVersionless APIの後方互換性維持と相性が良い。','score_ai_library'=>35,'score_other_library'=>60,'framework_note'=>'Clojure: Ring / Compojure','database_note'=>$db('JVM資産のJDBCドライバをそのまま利用可能'),'score_spec_change_resilience'=>78,'score_parallel_distributed'=>75,'cockroachdb_similarity_comment'=>'STM等の並行処理プリミティブは分散システムの整合性設計と発想が近い。','snowflake_similarity_comment'=>'直接の類似性は薄い。','aruaru_db_similarity_comment'=>'JVM経由でaruaru-dbクライアントを実装する選択肢の一つ。'],
        'f#' => ['score_async'=>82,'score_speed'=>78,'score_security'=>80,'versionless_api_comment'=>'代数的データ型による厳格な型付けはVersionless API設計時の破壊的変更検知に貢献する。','score_ai_library'=>40,'score_other_library'=>75,'framework_note'=>'F#: Giraffe / Saturn','database_note'=>$db('.NET資産のEntity Framework Core等をそのまま利用可能'),'score_spec_change_resilience'=>82,'score_parallel_distributed'=>72,'cockroachdb_similarity_comment'=>'直接の類似性は薄い。','snowflake_similarity_comment'=>'直接の類似性は薄い。','aruaru_db_similarity_comment'=>'.NET資産を持つチームの型安全なaruaru-dbクライアント実装候補。'],
        'zig' => ['score_async'=>75,'score_speed'=>96,'score_security'=>78,'versionless_api_comment'=>'言語自体は若くAPIバージョニングの慣習はまだ確立途上で、Gitのタグ運用にほぼ全面的に依存する。','score_ai_library'=>20,'score_other_library'=>35,'framework_note'=>'Zig: 標準ライブラリ中心（Web FWは発展途上）','database_note'=>$db('CのDBクライアントをFFI経由でそのまま呼び出せる'),'score_spec_change_resilience'=>55,'score_parallel_distributed'=>60,'cockroachdb_similarity_comment'=>'直接の類似性はほぼ無い。','snowflake_similarity_comment'=>'直接の類似性はほぼ無い。','aruaru_db_similarity_comment'=>'低レベル制御で最速のクライアントを書ける可能性はあるがエコシステムはまだ小さい。'],
        'solidity' => ['score_async'=>30,'score_speed'=>40,'score_security'=>60,'versionless_api_comment'=>'デプロイ済みスマートコントラクトは原理的にVersionlessではなく、アップグレード可能パターン（Proxy）とGitでのソース管理が併用される。','score_ai_library'=>10,'score_other_library'=>45,'framework_note'=>'Solidity: Hardhat / Foundry','database_note'=>$db('オンチェーンデータのため従来型DBとは異質だがインデクサ経由で連携可能'),'score_spec_change_resilience'=>30,'score_parallel_distributed'=>25,'cockroachdb_similarity_comment'=>'直接の類似性はほぼ無い。','snowflake_similarity_comment'=>'直接の類似性はほぼ無い。','aruaru_db_similarity_comment'=>'オンチェーンイベントをインデックスしaruaru-dbへ格納する用途で間接的に関わる。'],
        'sql' => ['score_async'=>40,'score_speed'=>65,'score_security'=>70,'versionless_api_comment'=>'SQL自体はAPIではないためVersionlessの概念は薄いが、マイグレーションファイルのGit管理は必須の実務慣行。','score_ai_library'=>20,'score_other_library'=>60,'framework_note'=>'SQL: dbt（データ変換パイプライン）','database_note'=>$db('すべてのRDBの共通言語であり相性の概念そのものが前提'),'score_spec_change_resilience'=>50,'score_parallel_distributed'=>50,'cockroachdb_similarity_comment'=>'CockroachDBはPostgreSQL互換のSQL方言を採用しており直接的に高い親和性がある。','snowflake_similarity_comment'=>'SnowflakeもSQLを主要インターフェースとしており直接的に高い親和性がある。','aruaru_db_similarity_comment'=>'aruaru-dbがpgwireプロトコル（PostgreSQL互換）である場合、標準SQLがそのまま通用する可能性が高い。'],
    ];
    return $rows;
}

/**
 * ナレッジベースに無い言語向けのルールベース算出（既存フィールドの記述から
 * キーワードマッチングでスコアを導出する。実測値ではなく推測ベースである
 * ことを明示するため、表示側の注記と合わせて運用する）。
 */
function aruaru_tech_extended_heuristic(array $row): array
{
    $name = (string)($row['name'] ?? '');
    $text = implode(' ', [
        (string)($row['async_support'] ?? ''), (string)($row['traits'] ?? ''),
        (string)($row['oss_note'] ?? ''), (string)($row['speed'] ?? ''),
        (string)($row['throughput_100k'] ?? ''), (string)($row['maintenance'] ?? ''),
        (string)($row['dev_scale'] ?? ''),
    ]);

    $score = static function (string $text, array $pos, array $neg, int $base) {
        $s = $base;
        foreach ($pos as $kw) {
            if (stripos($text, $kw) !== false) $s += 12;
        }
        foreach ($neg as $kw) {
            if (stripos($text, $kw) !== false) $s -= 15;
        }
        return max(5, min(100, $s));
    };

    $scoreAsync = $score($text, ['async', 'await', '非同期', 'イベントループ', 'goroutine', 'アクター', 'コルーチン', 'Promise', '並行'], ['主目的外', '同期'], 50);
    $scoreSpeed = $score($text, ['高速', '超高速', '最高速', 'ネイティブ', 'コンパイル'], ['遅い', '普通'], 55);
    $scoreSecurity = $score($text, ['型安全', '静的型', 'メモリ安全', '証明', '検証'], ['手動', '脆弱', 'GIL'], 50);
    $scoreAiLib = $score($text, ['AI', 'ML', '機械学習', 'データ', '記号計算', '数式'], [], 30);
    $scoreOtherLib = $score($text, ['豊富', 'エコシステム', 'パッケージ', 'ライブラリ', '標準'], ['限定的', '発展途上'], 45);
    $scoreSpecResil = $score($text, ['型安全', '静的型', 'テスト', '証明', '検証'], ['スクリプト', '動的'], 45);
    $scoreParallel = $score($text, ['並列', '分散', 'goroutine', 'アクター', 'マルチスレッド', '並行'], ['単一', '主目的外'], 45);

    return [
        'score_async' => $scoreAsync,
        'score_speed' => $scoreSpeed,
        'score_security' => $scoreSecurity,
        'versionless_api_comment' => "{$name}向けのVersionlessAPI設計は言語自体の制約より採用するWebフレームワーク次第。バージョン管理・Git運用との親和性は一般的なテキスト形式ソース管理の恩恵をそのまま受けられる（詳細は個別調査を推奨）。",
        'score_ai_library' => $scoreAiLib,
        'score_other_library' => $scoreOtherLib,
        'framework_note' => "{$name}: 主要フレームワークは要調査（公式ドキュメント・パッケージレジストリを参照）",
        'database_note' => "{$name}からのDB接続は標準/サードパーティドライバ経由が一般的（詳細な相性は要調査）。参考実装: aruaru-db https://github.com/aon-co-jp/aruaru-db 。",
        'score_spec_change_resilience' => $scoreSpecResil,
        'score_parallel_distributed' => $scoreParallel,
        'cockroachdb_similarity_comment' => "{$name}自体は分散データベースではないため直接の類似性は薄い（要調査）。",
        'snowflake_similarity_comment' => "{$name}自体はデータウェアハウスではないため直接の類似性は薄い（要調査）。",
        'aruaru_db_similarity_comment' => "{$name}とaruaru-db（https://github.com/aon-co-jp/aruaru-db ）の具体的な類似性・接続実績は要調査。",
    ];
}

/**
 * languages配列の全行に拡張評価軸を付与する（cron.php経由の日次リフレッシュで毎回再評価）。
 * ナレッジベース優先、無ければヒューリスティック。aruaru-llmが到達可能なら
 * aruaru_db_similarity_commentの一部を実際のHTTP応答で上書きする（実在確認済み・
 * 呼び出し可能な本物の関数を使用。既定は不到達time-outで即フォールバック）。
 */
function aruaru_tech_apply_extended_scoring(array $rows, bool $verbose = false): array
{
    $kb = aruaru_tech_extended_knowledge_base();
    static $llmReachable = null;

    foreach ($rows as $i => $row) {
        $key = aruaru_tech_norm_key((string)($row['name'] ?? ''));
        $ext = $kb[$key] ?? aruaru_tech_extended_heuristic($row);

        if ($llmReachable !== false) {
            $name = (string)($row['name'] ?? '');
            $reply = aruaru_tech_call_aruaru_llm("{$name}とaruaru-dbの類似性を一言で");
            if ($reply !== null) {
                $llmReachable = true;
                $ext['aruaru_db_similarity_comment'] = '[aruaru-llm:' . (string)($reply['engine'] ?? 'rule-based-v0') . '] ' . (string)$reply['reply'];
            } elseif ($llmReachable === null) {
                $llmReachable = false;
                aruaru_tech_sync_log('aruaru-llm 未到達（' . ARUARU_LLM_ENDPOINT . '）— ルールベースにフォールバック', $verbose);
            }
        }

        $merged = array_merge($row, $ext);
        $total = (int)$merged['score_async'] + (int)$merged['score_speed'] + (int)$merged['score_security']
            + (int)$merged['score_ai_library'] + (int)$merged['score_other_library']
            + (int)$merged['score_spec_change_resilience'] + (int)$merged['score_parallel_distributed'];
        $merged['total_score'] = $total;
        $rows[$i] = $merged;
    }
    return $rows;
}

/** メイン: 外部ランキング + AI補完 → キャッシュ用データ */
function aruaru_tech_refresh_rankings(bool $verbose = false): array
{
    $baseline = aruaru_tech_baseline_data();
    $external = aruaru_tech_fetch_external_rankings();

    if (empty($external['languages']) && empty($external['frameworks']) && empty($external['databases'])) {
        aruaru_tech_sync_log('外部データ取得失敗 — ベースラインのみ保存', $verbose);
        $baseline['languages'] = aruaru_tech_apply_extended_scoring($baseline['languages'], $verbose);
        usort($baseline['languages'], static fn($a, $b) => ($b['total_score'] ?? 0) <=> ($a['total_score'] ?? 0));
        foreach ($baseline['languages'] as $i => $row) {
            $baseline['languages'][$i]['rank'] = $i + 1;
        }
        $baseline['ranking_meta'] = [
            'sources' => ['baseline-only'],
            'fetched_at' => date('c'),
            'openai_used' => false,
            'errors' => $external['errors'],
            'extended_axes_note' => '非同期/セキュリティ/AIライブラリ等の拡張スコアはStackOverflow等のリアルタイム外部指標ではなく、一般的な技術知見に基づくルールベース評価です。',
        ];
        return $baseline;
    }

    $languages = aruaru_tech_build_top50('language', $baseline['languages'], $external['languages']);
    $frameworks = aruaru_tech_build_top50('framework', $baseline['frameworks'], $external['frameworks']);
    $databases = aruaru_tech_build_top50('database', $baseline['databases'], $external['databases']);

    $aiUsed = OPENAI_API_KEY !== '';
    $languages = aruaru_tech_apply_ai_enrichment('language', $languages);
    $frameworks = aruaru_tech_apply_ai_enrichment('framework', $frameworks);
    $databases = aruaru_tech_apply_ai_enrichment('database', $databases);

    $newLang = count(array_filter($languages, static function ($r) {
        return strpos((string)($r['ai_comment'] ?? ''), '外部データで検出') !== false;
    }));
    // 拡張評価軸（非同期/セキュリティ/AIライブラリ等）を毎回再評価して付与（[9/8]相当の項目、cron-all統合）
    $languages = aruaru_tech_apply_extended_scoring($languages, $verbose);
    // total_score（拡張評価軸の合計）をランキングのソートキーとして再採番
    usort($languages, static fn($a, $b) => ($b['total_score'] ?? 0) <=> ($a['total_score'] ?? 0));
    foreach ($languages as $i => $row) {
        $languages[$i]['rank'] = $i + 1;
    }

    aruaru_tech_sync_log(
        '同期完了: 言語' . count($languages) . ' / FW' . count($frameworks) . ' / DB' . count($databases)
        . ' / ソース: ' . implode(', ', $external['sources']),
        $verbose
    );

    return [
        'updated_at' => date('Y-m-d H:i:s'),
        'languages' => $languages,
        'frameworks' => $frameworks,
        'databases' => $databases,
        'ranking_meta' => [
            'sources' => $external['sources'],
            'fetched_at' => date('c'),
            'openai_used' => $aiUsed,
            'github_token' => GITHUB_TOKEN !== '',
            'errors' => $external['errors'],
            'note' => '順位は Stack Overflow / DB-Engines 等の外部人気データとベースラインを合成。紹介文の新規生成は OpenAI（キー設定時）。',
            'extended_axes_note' => '非同期/ハイスピード/セキュリティ/AIライブラリ/その他ライブラリ/仕様変更耐性/並列分散性の各スコアと各種類似性コメントは、StackOverflow/DB-Enginesのようなリアルタイム外部指標ではなく、一般的に広く知られた技術的特性・コミュニティでの評価傾向に基づくルールベース評価です（一部、aruaru-llm https://github.com/aon-co-jp/aruaru-llm への実HTTP問い合わせが成功した場合はその応答を反映、aruaru-llm自体も現状はルールベースの意図分類であり本物のニューラル推論ではないことをaruaru-llm側のCLAUDE.mdで開示済み）。',
        ],
    ];
}

/** index.php 互換 */
function aruaruTechAnalyzerData(): array
{
    return aruaru_tech_baseline_data();
}

/* ===== END: aruaru-tech-sync.php ===== */


/* ===== BEGIN: aruaru-learning-data.php ===== */

/**
 * aruaru — 学習塾・家庭教師・教室・オンライン学習 TOP50（日英）＋技術別リンクマップ
 */



function aruaru_policy_messages(): array
{
    return [
        'ja' => '発電とAIデーターセンターなどへのSETの投資よりも、ソフトウェアやアプリやWEBサイト関連の初心者や未経験者を採用して育てる企業やお店や、学生が生成AIなどのAI先生やオンライン学習サイト利用料を無償化する国家予算や、助成金や補助金や投資されたお金などがまわると良いですね。',
        'en' => 'Rather than pouring most subsidies, grants, and investment into power generation, AI data centers, and similar large-scale SET-style infrastructure, it would be better if more of that funding—including national budgets that make generative AI tutors and online learning fees free for students—went to companies and shops that hire and train beginners and people with no experience in software, apps, and website-related work.',
    ];
}

/** @return array<int, array{rank:int,name:string,url:string,features:string,price:string}> */
function aruaru_learning_build_list(array $items): array
{
    $out = [];
    foreach ($items as $i => $row) {
        $out[] = [
            'rank'     => $i + 1,
            'name'     => $row[0],
            'url'      => $row[1],
            'features' => $row[2],
            'price'    => $row[3],
        ];
    }
    return $out;
}

/**
 * TOP50 表の行と AI トレンドチップを対応付けるスクロール用 ID（衝突しにくい短いハッシュ）
 */
function aruaru_anchor_id_for_tech(string $section, string $name): string
{
    $sec = preg_replace('/[^a-z]/', '', strtolower($section));
    if ($sec === '') {
        $sec = 'x';
    }
    $h = substr(hash('sha256', $sec . '|' . mb_strtolower(trim($name), 'UTF-8')), 0, 14);

    return 'aruaru-' . $sec . '-' . $h;
}

function aruaru_learning_categories(): array
{
    aruaru_learning_prices_refresh_if_stale();
    $pr = aruaru_learning_service_prices();

    $juku_ja = [
        ['河合塾（Kawaijuku）', 'https://www.kawai-juku.ac.jp/', '大学受験・理系強化・全国展開', '年間数十万円〜（コースによる）'],
        ['駿台予備学校', 'https://www.sundai-yobi.co.jp/', '難関大・医学部・理系特化', '年間数十万円〜'],
        ['早稲田アカデミー', 'https://www.waseda-ac.co.jp/', '小中高・大学受験', '月額1〜3万円前後〜'],
        ['栄光ゼミナール', 'https://www.eikoh-seminar.com/', '個別指導・少人数', '月額2〜4万円前後〜'],
        ['スタディサプリ進学', 'https://studysapuri.jp/', '動画＋進路・オンライン塾', '月額数千円〜'],
        ['トライ', 'https://www.trygroup.co.jp/', '家庭教師・個別', '月額3〜6万円前後〜'],
        ['明光義塾', 'https://www.meikogijuku.jp/', '個別指導', '月額2〜4万円前後〜'],
        ['個別指導学院ヒーローズ', 'https://www.heros-j.com/', '個別・定着重視', '月額2〜3万円前後〜'],
        ['湘南ゼミナール', 'https://www.shozemi.com/', '小中高・定期テスト', '月額1〜3万円前後〜'],
        ['SAPIX', 'https://www.sapix.co.jp/', '中学受験', '年間数十万円〜'],
        ['四谷大塚', 'https://www.yotsuyaotsuka.com/', '中学受験', '年間数十万円〜'],
        ['日能研', 'https://www.nichinoken.co.jp/', '中学受験', '年間数十万円〜'],
        ['WAM', 'https://www.wam.ne.jp/', 'プログラミング・IT資格', 'コースにより月1〜3万円〜'],
        ['ヒューマンアカデミー', 'https://haa.athuman.com/', 'IT・Web・デザイン', '数十万円〜（講座による）'],
        ['インターネット・アカデミー', 'https://www.internetacademy.jp/', 'Web・プログラミング', '講座ごとに異なる'],
        ['ニホン・コンピューター・カレッジ', 'https://www.ncc.ac.jp/', '専門学校・実践', '入学金＋学費（要確認）'],
        ['HAL東京', 'https://www.hal.ac.jp/tokyo', 'ゲーム・IT・デザイン', '学費（要確認）'],
        ['東京デザインテクノロジー専門学校', 'https://www.tdc.ac.jp/', 'IT・デザイン', '学費（要確認）'],
        ['アカデミー・デュ・ヴィナン', 'https://www.adv.gr.jp/', '語学・ビジネス', 'コースによる'],
        ['ECC', 'https://www.ecc.jp/', '語学・PC', 'コースによる'],
    ];

    /** 講師紹介・マッチング主体のサービスを中心（塾教室型は学習塾カテゴリを参照）。 */
    $tutor_intro_ja = [
        ['家庭教師のトライ', 'https://www.trygroup.co.jp/', '全国ネットワーク・オンライン可・講師紹介', '時間数・単価は要確認'],
        ['あすなろ家庭教師', 'https://www.asunaro-g.co.jp/', 'マンツーマン・定期・紹介', '地域・単価要確認'],
        ['マナリンク（オンライン家庭教師）', 'https://manalink.jp/', 'マッチング型・オンライン中心', '講師単価は要確認'],
        ['スタディサプリ（個別オンライン指導）', 'https://studysapuri.jp/', '大手・オンラインマンツーマン事例あり', 'プランにより要確認'],
        ['家庭教師紹介サイトの比較（検索入口）', 'https://www.google.com/search?q=' . rawurlencode('家庭教師 紹介サービス マッチング'), 'サービス検索のみ（表示は検索サイトに依存）', '—'],
    ];
    $tutor_intro_en = [
        ['Wyzant', 'https://www.wyzant.com/', 'USA marketplace for tutors', 'Hourly pricing'],
        ['Preply', 'https://preply.com/', 'Language & tutors online', 'Per-lesson packs'],
        ['Varsity Tutors', 'https://www.varsitytutors.com/', 'USA live tutoring', 'Subscription / bundles'],
        ['Tutor.com', 'https://www.tutor.com/', 'Education support', 'Institution-based access often'],
        ['Superprof', 'https://www.superprof.com/', 'EU/global tutor marketplace', 'Per-profile rates'],
        ['TutorMe', 'https://www.tutor.me/', 'On-demand tutoring', 'Subscription'],
        ['Skooli', 'https://www.skooli.com/', 'K-12 & college tutors', 'Pay as you go'],
        ['Lessonface', 'https://lessonface.com/', 'Music & academic live online', 'Per teacher'],
        ['Italki', 'https://www.italki.com/', 'Languages 1-on-1', 'Community / paid lessons'],
        ['Cambly', 'https://www.cambly.com/', 'English conversation tutors', 'Subscription'],
    ];

    $juku_en = [
        ['Khan Academy', 'https://www.khanacademy.org/', 'Free K-12 and CS basics', 'Free'],
        ['Coursera', 'https://www.coursera.org/', 'University & industry courses', 'Free audit / subscription'],
        ['edX', 'https://www.edx.org/', 'MIT/Harvard-style MOOCs', 'Free audit / paid certificates'],
        ['Udemy', 'https://www.udemy.com/', 'Huge course marketplace', 'Often $10–20 on sale'],
        ['Pluralsight', 'https://www.pluralsight.com/', 'Tech skill paths', 'Subscription ~$29/mo'],
        ['LinkedIn Learning', 'https://www.linkedin.com/learning/', 'Business & tech video', 'Subscription'],
        ['Codecademy', 'https://www.codecademy.com/', 'Interactive coding', 'Free tier / Pro ~$20/mo'],
        ['freeCodeCamp', 'https://www.freecodecamp.org/', 'Full-stack curriculum', 'Free'],
        ['The Odin Project', 'https://www.theodinproject.com/', 'Web dev path', 'Free'],
        ['MIT OpenCourseWare', 'https://ocw.mit.edu/', 'University lectures', 'Free'],
        ['Stanford Online', 'https://online.stanford.edu/', 'CS & engineering', 'Varies'],
        ['Harvard CS50', 'https://cs50.harvard.edu/', 'Intro CS', 'Free / certificate fee'],
        ['Google Career Certificates', 'https://grow.google/certificates/', 'Job-ready certs', 'Subscription via Coursera'],
        ['AWS Skill Builder', 'https://skillbuilder.aws/', 'Cloud training', 'Free & paid plans'],
        ['Microsoft Learn', 'https://learn.microsoft.com/', 'Azure & .NET', 'Free'],
        ['Google Developers', 'https://developers.google.com/', 'Android & web', 'Free'],
        ['Apple Developer', 'https://developer.apple.com/learn/', 'Swift & iOS', 'Free'],
        ['Meta Blueprint', 'https://www.facebookblueprint.com/', 'Dev & ads', 'Free courses'],
        ['IBM SkillsBuild', 'https://skillsbuild.org/', 'Tech & AI skills', 'Free tiers'],
        ['DataCamp', 'https://www.datacamp.com/', 'Data science', 'Subscription'],
    ];

    $pad_ja = function (array $base, string $prefix, string $feat, string $price): array {
        $n = count($base);
        for ($i = $n; $i < 80; $i++) {
            $r = $i + 1;
            $base[] = ["{$prefix} おすすめ #{$r}", 'https://www.google.com/search?q=' . rawurlencode($prefix), $feat, $price];
        }
        return $base;
    };
    $pad_en = function (array $base, string $prefix, string $search_tail = ' programming learn'): array {
        $n = count($base);
        for ($i = $n; $i < 80; $i++) {
            $r = $i + 1;
            $middle = ($search_tail === '') ? '' : $search_tail;
            $q = trim($prefix . $middle . " #{$r}");
            $base[] = ["{$prefix} pick #{$r}", 'https://www.google.com/search?q=' . rawurlencode($q), 'Curated learning option', 'Varies / often free tiers'];
        }
        return $base;
    };

    $merge_by_url = static function (array $lists): array {
        $seen = [];
        $out = [];
        foreach ($lists as $rows) {
            foreach ($rows as $row) {
                $url = (string)($row[1] ?? '');
                if ($url === '' || isset($seen[$url])) {
                    continue;
                }
                $seen[$url] = true;
                $out[] = $row;
            }
        }

        return $out;
    };

    $pc_ja = $pad_ja([
        ['パソコン教室ワード', 'https://www.pc-word.jp/', 'Office・基礎操作', '月額5千〜1.5万円〜'],
        ['キッズパソコン教室', 'https://www.kids-pc.jp/', '子ども向け', '月額5千〜1万円〜'],
        ['アビバ', 'https://www.aviva.co.jp/', '資格・Office', 'コースによる'],
        ['ライズ', 'https://www.rise.co.jp/', 'PC・資格', 'コースによる'],
        ['ハローPC', 'https://www.hello-pc.jp/', '全国チェーン', '月額制'],
    ], 'PC教室', 'Word/Excel/typing', '月額5千〜2万円〜');

    $prog_ja_inner = [
        ['テックアカデミー', 'https://techacademy.jp/', 'Web・Ruby/Python', '月額〜数十万（プランによる）'],
        ['ドットインストール', 'https://dotinstall.com/', '動画で手軽に', $pr['dotinstall']],
        ['Progate', 'https://prog-8.com/', '初心者向け', $pr['progate']],
        ['Schoo', 'https://schoo.jp/', 'ライブ授業', '月額2,178円〜'],
        ['SAMURAI ENGINEER', 'https://www.sejuku.net/', '転職特化', '高額（要相談）'],
        ['RUNTEQ', 'https://nntechno.co.jp/service/runteq/', 'Rails', '要相談'],
        ['ウェブキャンプ', 'https://web-camp.io/', '短期集中', '要相談'],
        ['デイトラ', 'https://datacamp.jp/', 'データ分析', '月額制'],
        ['キッズプログラミング', 'https://kidsplaza.jp/', '子ども向け', '月額〜'],
        ['LITALICOワンダー', 'https://wonder.litalico.jp/', '子どもSTEAM', '月額〜'],
    ];
    $site_ja_extra = [
        ['Udemy（日本）', 'https://www.udemy.com/', 'セール多い', '1講座数千円〜'],
        ['paizaラーニング', 'https://paiza.jp/works', '問題演習', $pr['paiza']],
        ['AtCoder', 'https://atcoder.jp/', '競プロ', '無料'],
        ['Qiita', 'https://qiita.com/', '技術記事', '無料'],
        ['Zenn', 'https://zenn.dev/', '技術記事', '無料'],
        ['MDN Web Docs（日本語）', 'https://developer.mozilla.org/ja/', 'Web標準', '無料'],
        ['Python.jp', 'https://www.python.jp/', '公式日本語', '無料'],
        ['Coursera', 'https://www.coursera.org/', '大学講座', '無料聴講〜'],
        ['AWS Skill Builder', 'https://skillbuilder.aws/', 'AWS', '無料〜'],
        ['Google Cloud Skills Boost', 'https://www.cloudskillsboost.google/', 'GCP', '無料〜'],
        ['Microsoft Learn', 'https://learn.microsoft.com/ja-jp/', 'Azure/.NET', '無料'],
        ['freeCodeCamp（日本語記事連携）', 'https://www.freecodecamp.org/', '英語中心', '無料'],
        ['Codecademy', 'https://www.codecademy.com/', '対話型', 'Pro有料'],
    ];
    $prog_ja = $pad_ja(
        $merge_by_url([$prog_ja_inner, $site_ja_extra]),
        'プログラミング教室',
        '対面/オンライン・動画・演習',
        '無料〜月額数十万'
    );

    $tablet_ja = $pad_ja([
        ['タブレット学習（GIGA）', 'https://www.mext.go.jp/', '学校教育ICT', '自治体・学校導入'],
        ['Smile Zemi', 'https://smile-zemi.jp/', '小中学生', '月額〜'],
        ['Z会のタブレット', 'https://www.zkai.co.jp/', '中学受験', '月額〜'],
        ['進研ゼミ デジタル', 'https://www.benesse.co.jp/', '小中高', '月額〜'],
        ['スタディサプリ', 'https://studysapuri.jp/', '動画学習', '月額〜'],
        ['チャレンジタッチ', 'https://www.benesse.co.jp/', '幼児', '月額〜'],
        ['スマイルゼミ 幼児', 'https://smile-zemi.jp/', '幼児', '月額〜'],
        ['iPad + Swift Playgrounds', 'https://www.apple.com/jp/swift/playgrounds/', 'Swift入門', '機器代＋無料アプリ'],
    ], '学習タブレット', '動画・問題集', '月額3千〜1万円〜＋端末代');

    $prog_en_bootcamps = [
        ['App Academy', 'https://www.appacademy.io/', 'Bootcamp', 'ISA / tuition'],
        ['Flatiron School', 'https://flatironschool.com/', 'Bootcamp', 'Tuition'],
        ['General Assembly', 'https://generalassemb.ly/', 'Bootcamp', 'Varies'],
    ];
    $online_en_base = [
        ['freeCodeCamp', 'https://www.freecodecamp.org/', 'Full curriculum', 'Free'],
        ['Codecademy', 'https://www.codecademy.com/', 'Interactive', 'Free / Pro'],
        ['Udemy', 'https://www.udemy.com/', 'Marketplace', 'Sale prices'],
        ['Coursera', 'https://www.coursera.org/', 'MOOCs', 'Audit free'],
        ['Pluralsight', 'https://www.pluralsight.com/', 'Tech skill paths', 'Subscription'],
        ['LinkedIn Learning', 'https://www.linkedin.com/learning/', 'Video', 'Subscription'],
        ['Egghead.io', 'https://egghead.io/', 'Short lessons', 'Subscription'],
        ['Frontend Masters', 'https://frontendmasters.com/', 'Deep front-end', 'Subscription'],
        ['Scrimba', 'https://scrimba.com/', 'Interactive', 'Free / Pro'],
        ['Exercism', 'https://exercism.org/', 'Mentored practice', 'Free'],
    ];
    $online_en_extra = [
        ['edX', 'https://www.edx.org/', 'MIT/Harvard-style MOOCs', 'Free audit / paid'],
        ['The Odin Project', 'https://www.theodinproject.com/', 'Web dev path', 'Free'],
        ['DataCamp', 'https://www.datacamp.com/', 'Data science & code', 'Subscription'],
        ['AWS Skill Builder', 'https://skillbuilder.aws/', 'Cloud training', 'Free & paid'],
        ['Microsoft Learn', 'https://learn.microsoft.com/', 'Azure & .NET', 'Free'],
        ['MIT OpenCourseWare', 'https://ocw.mit.edu/', 'CS lectures', 'Free'],
    ];
    $prog_en_merged = $pad_en(
        $merge_by_url([$prog_en_bootcamps, $online_en_base, $online_en_extra]),
        'programming school or online course'
    );

    return [
        'juku' => [
            'title_ja' => 'おすすめ学習塾 TOP50',
            'title_en' => 'Recommended Cram Schools / Study Centers TOP50',
            'ja' => aruaru_learning_build_list($pad_ja($juku_ja, '学習塾', '受験・資格・IT', '要確認')),
            'en' => aruaru_learning_build_list($pad_en($juku_en, 'cram school study')),
        ],
        'home_tutor' => [
            'title_ja' => 'おすすめ家庭教師紹介サービス TOP50',
            'title_en' => 'Private Tutor / Matching Services TOP50',
            'ja' => aruaru_learning_build_list($pad_ja($tutor_intro_ja, '家庭教師紹介サービス', '講師マッチング・対面オンライン', '紹介手数料・時給は各社サイトで確認')),
            'en' => aruaru_learning_build_list($pad_en($tutor_intro_en, 'private tutor referral', '')),
        ],
        'pc_school' => [
            'title_ja' => 'おすすめPC教室 TOP50',
            'title_en' => 'Recommended PC Schools TOP50',
            'ja' => aruaru_learning_build_list($pc_ja),
            'en' => aruaru_learning_build_list($pad_en([
                ['New Horizons', 'https://www.newhorizons.com/', 'IT training', 'Paid courses'],
                ['ONLC', 'https://www.onlc.com/', 'Cert prep', 'Paid'],
            ], 'PC training')),
        ],
        'prog_school' => [
            'title_ja' => 'おすすめプログラミング教室 TOP50',
            'title_en' => 'Recommended Programming Schools TOP50',
            'ja' => aruaru_learning_build_list($prog_ja),
            'en' => aruaru_learning_build_list($prog_en_merged),
        ],
        'tablet' => [
            'title_ja' => 'おすすめ学習タブレット TOP50',
            'title_en' => 'Recommended Learning Tablets TOP50',
            'ja' => aruaru_learning_build_list($tablet_ja),
            'en' => aruaru_learning_build_list($pad_en([
                ['Khan Academy Kids', 'https://learn.khanacademy.org/khan-academy-kids/', 'Kids', 'Free'],
                ['ABCmouse', 'https://www.abcmouse.com/', 'Kids', 'Subscription'],
            ], 'kids tablet learning')),
        ],
    ];
}

/**
 * 技術名 → 学習リンク（日英）
 * @return array<string, array{ja: list<array{name,url,features,price}>, en: list<...>}>
 */
function aruaru_tech_learning_links(): array
{
    aruaru_learning_prices_refresh_if_stale();
    $doc = static fn(string $jaName, string $jaUrl, string $enName, string $enUrl, string $feat, string $price): array => [
        'ja' => [['name' => $jaName, 'url' => $jaUrl, 'features' => $feat, 'price' => $price]],
        'en' => [['name' => $enName, 'url' => $enUrl, 'features' => $feat, 'price' => $price]],
    ];

    $merge = static function (array ...$parts): array {
        $ja = [];
        $en = [];
        foreach ($parts as $p) {
            $ja = array_merge($ja, $p['ja'] ?? []);
            $en = array_merge($en, $p['en'] ?? []);
        }
        return ['ja' => $ja, 'en' => $en];
    };

    $lp = aruaru_learning_service_prices();
    $common_ja = [
        ['name' => 'ドットインストール', 'url' => 'https://dotinstall.com/', 'features' => '動画で入門', 'price' => $lp['dotinstall']],
        ['name' => 'Progate', 'url' => 'https://prog-8.com/', 'features' => '初心者向け', 'price' => $lp['progate']],
        ['name' => 'paizaラーニング', 'url' => 'https://paiza.jp/works', 'features' => '演習', 'price' => $lp['paiza']],
        ['name' => 'Schoo', 'url' => 'https://schoo.jp/', 'features' => 'ライブ授業', 'price' => '月額2,178円〜'],
    ];
    $common_en = [
        ['name' => 'freeCodeCamp', 'url' => 'https://www.freecodecamp.org/', 'features' => 'Full curriculum', 'price' => 'Free'],
        ['name' => 'Codecademy', 'url' => 'https://www.codecademy.com/', 'features' => 'Interactive', 'price' => 'Free / Pro'],
        ['name' => 'Udemy', 'url' => 'https://www.udemy.com/', 'features' => 'Courses', 'price' => 'Sale prices'],
        ['name' => 'Coursera', 'url' => 'https://www.coursera.org/', 'features' => 'MOOCs', 'price' => 'Free audit'],
    ];

    $map = [
        'TypeScript' => $merge(
            $doc('TypeScript公式ハンドブック（日本語）', 'https://www.typescriptlang.org/ja/docs/', 'TypeScript Handbook', 'https://www.typescriptlang.org/docs/', '公式・型', '無料'),
            $doc('MDN Web Docs', 'https://developer.mozilla.org/ja/docs/Web/JavaScript', 'MDN JavaScript', 'https://developer.mozilla.org/en-US/docs/Web/JavaScript', 'Web基礎', '無料'),
            ['ja' => $common_ja, 'en' => $common_en]
        ),
        'JavaScript' => $merge(
            $doc('MDN JavaScript', 'https://developer.mozilla.org/ja/docs/Web/JavaScript', 'MDN JS Guide', 'https://developer.mozilla.org/en-US/docs/Web/JavaScript/Guide', '標準リファレンス', '無料'),
            $doc('JavaScript.info（日本語）', 'https://ja.javascript.info/', 'javascript.info', 'https://javascript.info/', '体系的チュートリアル', '無料'),
            ['ja' => $common_ja, 'en' => $common_en]
        ),
        'Python' => $merge(
            $doc('Python公式（日本語）', 'https://docs.python.org/ja/3/', 'Python Docs', 'https://docs.python.org/3/', '公式', '無料'),
            $doc('PyQ', 'https://pyq.jp/', 'Real Python', 'https://realpython.com/', '演習・記事', '無料〜有料'),
            ['ja' => $common_ja, 'en' => $common_en]
        ),
        'Go' => $merge(
            $doc('Go公式（日本語）', 'https://go.dev/doc/', 'Go Tour', 'https://go.dev/tour/', '公式', '無料'),
            $doc('Qiita Goタグ', 'https://qiita.com/tags/go', 'Go by Example', 'https://gobyexample.com/', '事例', '無料'),
            ['ja' => $common_ja, 'en' => $common_en]
        ),
        'Rust' => $merge(
            $doc('Rust公式（日本語）', 'https://doc.rust-jp.rs/book/', 'The Rust Book', 'https://doc.rust-lang.org/book/', '公式', '無料'),
            ['ja' => $common_ja, 'en' => $common_en]
        ),
        'Java' => $merge(
            $doc('Oracle Java Tutorials', 'https://docs.oracle.com/javase/tutorial/', 'Java Tutorials', 'https://docs.oracle.com/javase/tutorial/', '公式', '無料'),
            ['ja' => $common_ja, 'en' => $common_en]
        ),
        'PHP' => $merge(
            $doc('PHP公式（日本語）', 'https://www.php.net/manual/ja/', 'PHP Manual', 'https://www.php.net/manual/en/', '公式', '無料'),
            $doc('Laravel公式（日本語）', 'https://readouble.com/laravel/', 'Laravel Docs', 'https://laravel.com/docs', 'FW', '無料'),
            ['ja' => $common_ja, 'en' => $common_en]
        ),
        'Ruby' => $merge(
            $doc('Ruby公式（日本語）', 'https://www.ruby-lang.org/ja/documentation/', 'Ruby Docs', 'https://www.ruby-lang.org/en/documentation/', '公式', '無料'),
            $doc('Railsガイド', 'https://railsguides.jp/', 'Rails Guides', 'https://guides.rubyonrails.org/', 'Rails', '無料'),
            ['ja' => $common_ja, 'en' => $common_en]
        ),
        'SQL' => $merge(
            $doc('SQLZoo（日本語）', 'https://sqlzoo.net/', 'SQLBolt', 'https://sqlbolt.com/', '対話', '無料'),
            $doc('PostgreSQL公式', 'https://www.postgresql.jp/document/', 'PostgreSQL Docs', 'https://www.postgresql.org/docs/', 'RDB', '無料'),
            ['ja' => $common_ja, 'en' => $common_en]
        ),
        'AWS' => $merge(
            $doc('AWS Skill Builder', 'https://skillbuilder.aws/', 'AWS Skill Builder', 'https://skillbuilder.aws/', '公式トレーニング', '無料〜'),
            $doc('AWS公式ドキュメント', 'https://docs.aws.amazon.com/ja_jp/', 'AWS Documentation', 'https://docs.aws.amazon.com/', 'リファレンス', '無料'),
            ['ja' => $common_ja, 'en' => $common_en]
        ),
        'PostgreSQL' => $merge(
            $doc('PostgreSQL 日本語', 'https://www.postgresql.jp/document/', 'PostgreSQL Docs', 'https://www.postgresql.org/docs/', '公式', '無料'),
            ['ja' => $common_ja, 'en' => $common_en]
        ),
        'MySQL' => $merge(
            $doc('MySQL公式（日本語）', 'https://dev.mysql.com/doc/', 'MySQL Docs', 'https://dev.mysql.com/doc/', '公式', '無料'),
            ['ja' => $common_ja, 'en' => $common_en]
        ),
        'MongoDB' => $merge(
            $doc('MongoDB University', 'https://learn.mongodb.com/', 'MongoDB University', 'https://learn.mongodb.com/', '公式', '無料〜'),
            ['ja' => $common_ja, 'en' => $common_en]
        ),
        'Redis' => $merge(
            $doc('Redis公式', 'https://redis.io/docs/', 'Redis Docs', 'https://redis.io/docs/', '公式', '無料'),
            ['ja' => $common_ja, 'en' => $common_en]
        ),
        'React' => $merge(
            $doc('React公式（日本語）', 'https://ja.react.dev/', 'React Docs', 'https://react.dev/', '公式', '無料'),
            ['ja' => $common_ja, 'en' => $common_en]
        ),
        'Next.js' => $merge(
            $doc('Next.js公式', 'https://nextjs.org/docs', 'Next.js Learn', 'https://nextjs.org/learn', '公式', '無料'),
            ['ja' => $common_ja, 'en' => $common_en]
        ),
        'Vue.js' => $merge(
            $doc('Vue.js公式（日本語）', 'https://ja.vuejs.org/', 'Vue.js Docs', 'https://vuejs.org/', '公式', '無料'),
            ['ja' => $common_ja, 'en' => $common_en]
        ),
        'Laravel' => $merge(
            $doc('Laravel公式（日本語）', 'https://readouble.com/laravel/', 'Laravel Docs', 'https://laravel.com/docs', 'FW', '無料'),
            ['ja' => $common_ja, 'en' => $common_en]
        ),
        'Docker' => $merge(
            $doc('Docker Docs', 'https://docs.docker.com/', 'Docker Get Started', 'https://docs.docker.com/get-started/', 'コンテナ', '無料'),
            ['ja' => $common_ja, 'en' => $common_en]
        ),
        'Kubernetes' => $merge(
            $doc('Kubernetes公式（日本語）', 'https://kubernetes.io/ja/docs/home/', 'Kubernetes Docs', 'https://kubernetes.io/docs/home/', 'K8s', '無料'),
            ['ja' => $common_ja, 'en' => $common_en]
        ),
        'Terraform' => $merge(
            $doc('Terraform Docs', 'https://developer.hashicorp.com/terraform/docs', 'Terraform Learn', 'https://developer.hashicorp.com/terraform/tutorials', 'IaC', '無料'),
            ['ja' => $common_ja, 'en' => $common_en]
        ),
        'Mojo' => $merge(
            $doc('Mojo公式', 'https://docs.modular.com/mojo/', 'Modular Mojo', 'https://docs.modular.com/mojo/', 'AI/システム', '要確認'),
            ['ja' => $common_ja, 'en' => $common_en]
        ),
        'WunderGraph' => $merge(
            $doc('WunderGraph Docs', 'https://wundergraph.com/docs', 'WunderGraph', 'https://wundergraph.com/docs', 'API/BFF', 'OSS＋クラウド'),
            ['ja' => $common_ja, 'en' => $common_en]
        ),
    ];

    $default = ['ja' => $common_ja, 'en' => $common_en];
    $fw_db_extra = [
        'Spring Boot' => $map['Java'],
        'FastAPI' => $map['Python'],
        'NestJS' => $map['TypeScript'],
        'Django' => $map['Python'],
        'Flask' => $map['Python'],
        'Express' => $map['JavaScript'],
        'Nuxt.js' => $map['Vue.js'],
        'Angular' => $merge(
            $doc('Angular公式', 'https://angular.jp/', 'Angular', 'https://angular.dev/', 'FW', '無料'),
            ['ja' => $common_ja, 'en' => $common_en]
        ),
        'GraphQL' => $merge(
            $doc('GraphQL公式', 'https://graphql.org/learn/', 'GraphQL Learn', 'https://graphql.org/learn/', 'API', '無料'),
            ['ja' => $common_ja, 'en' => $common_en]
        ),
        'DynamoDB' => $map['AWS'],
        'BigQuery' => $merge(
            $doc('Google Cloud Skills Boost', 'https://www.cloudskillsboost.google/', 'BigQuery Docs', 'https://cloud.google.com/bigquery/docs', 'GCP', '無料〜'),
            ['ja' => $common_ja, 'en' => $common_en]
        ),
        'Snowflake' => $merge(
            $doc('Snowflake University', 'https://www.snowflake.com/en/data-cloud-academy/', 'Snowflake Learning', 'https://learn.snowflake.com/', 'DWH', '無料〜'),
            ['ja' => $common_ja, 'en' => $common_en]
        ),
    ];

    foreach ($fw_db_extra as $k => $v) {
        $map[$k] = $v;
    }

    return ['map' => $map, 'default' => $default];
}

function aruaru_resolve_tech_links(string $name, string $kind): array
{
    static $cache = null;
    if ($cache === null) {
        $cache = aruaru_tech_learning_links();
    }
    $key = trim($name);
    if ($key === 'AWS' || stripos($key, 'amazon') !== false) {
        $key = 'AWS';
    }
    $links = $cache['map'][$key] ?? $cache['default'];
    // 配列をコピーして変更（static cache を汚さない）
    $links = ['ja' => $links['ja'] ?? [], 'en' => $links['en'] ?? []];

    // PyQ — Python・GraphQL等を学べる学習サイト。
    // Python本体は既定リンクに収録済みのため、Python系フレームワーク／GraphQL に追加する
    $pyq_targets = [
        'Django', 'FastAPI', 'Flask', 'Streamlit', 'Celery', 'GraphQL',
    ];
    if (in_array($key, $pyq_targets, true)) {
        array_unshift($links['ja'], [
            'name'     => 'PyQ（パイキュー）',
            'url'      => 'https://pyq.jp/',
            'features' => 'ブラウザだけでPython・GraphQL等を実践学習できるオンライン教材',
            'price'    => '基本無料体験あり、月額制（公式サイトで要確認）',
        ]);
    }

    $searchJa = 'https://www.google.com/search?q=' . rawurlencode($key . ' 学習 公式');
    $searchEn = 'https://www.google.com/search?q=' . rawurlencode($key . ' learn official tutorial');
    $links['ja'][] = ['name' => "{$key} を検索（日本語）", 'url' => $searchJa, 'features' => '追加リソース探索', 'price' => '—'];
    $links['en'][] = ['name' => "Search {$key} (English)", 'url' => $searchEn, 'features' => 'More resources', 'price' => '—'];
    return ['kind' => $kind, 'name' => $key, 'ja' => $links['ja'], 'en' => $links['en']];
}

/* ===== END: aruaru-learning-data.php ===== */


/* ===== BEGIN: aruaru-learning-links-ai.php ===== */

/**
 * TOP50 技術名ごとに、OpenAI でオンライン学習サイトの URL 集を取得しキャッシュする。
 * Cron で毎日数件ずつ更新（API 負荷・コスト抑制）。
 */


if (!defined('ARUARU_LEARNING_AI_CACHE')) {
    define('ARUARU_LEARNING_AI_CACHE', __DIR__ . '/aruaru-tech-learning-ai-cache.json');
}

function aruaru_learning_ai_load_cache(): array
{
    if (!is_readable(ARUARU_LEARNING_AI_CACHE)) {
        return ['entries' => [], 'meta' => []];
    }
    $raw = @file_get_contents(ARUARU_LEARNING_AI_CACHE);
    if ($raw === false || $raw === '') {
        return ['entries' => [], 'meta' => []];
    }
    $j = json_decode($raw, true);

    return is_array($j) ? $j + ['entries' => [], 'meta' => []] : ['entries' => [], 'meta' => []];
}

function aruaru_learning_ai_save_cache(array $data): void
{
    $data['meta']['saved_at'] = date('c');
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($json !== false) {
        @file_put_contents(ARUARU_LEARNING_AI_CACHE, $json, LOCK_EX);
    }
}

/** @param list<array{name?:string,url?:string,features?:string,price?:string}> $items */
function aruaru_learning_ai_dedupe_urls(array $items): array
{
    $seen = [];
    $out = [];
    foreach ($items as $it) {
        if (!is_array($it)) {
            continue;
        }
        $url = trim((string)($it['url'] ?? ''));
        if ($url === '' || !preg_match('#^https://#i', $url)) {
            continue;
        }
        $key = strtolower((string)(parse_url($url, PHP_URL_HOST) ?: '')) . "\0" . $url;
        if (isset($seen[$key])) {
            continue;
        }
        $seen[$key] = true;
        $out[] = [
            'name'     => (string)($it['name'] ?? 'リンク'),
            'url'      => $url,
            'features' => (string)($it['features'] ?? 'オンライン学習'),
            'price'    => (string)($it['price'] ?? '要確認'),
        ];
    }

    return $out;
}

/**
 * @param array<string, array{name:string,kind:string,ja:list,en:list}> $panel
 * @return array<string, array{name:string,kind:string,ja:list,en:list}>
 */
function aruaru_learning_ai_merge_panel(array $panel): array
{
    $cache = aruaru_learning_ai_load_cache();
    $entries = $cache['entries'] ?? [];
    foreach ($panel as $key => $block) {
        if (!is_array($block) || !isset($entries[$key]) || !is_array($entries[$key])) {
            continue;
        }
        $jaExtra = $entries[$key]['ja'] ?? [];
        $enExtra = $entries[$key]['en'] ?? [];
        if (!is_array($jaExtra)) {
            $jaExtra = [];
        }
        if (!is_array($enExtra)) {
            $enExtra = [];
        }
        $jaBase = $block['ja'] ?? [];
        $enBase = $block['en'] ?? [];
        if (!is_array($jaBase)) {
            $jaBase = [];
        }
        if (!is_array($enBase)) {
            $enBase = [];
        }
        $panel[$key]['ja'] = aruaru_learning_ai_dedupe_urls(array_merge(
            aruaru_learning_ai_dedupe_urls($jaExtra),
            $jaBase
        ));
        $panel[$key]['en'] = aruaru_learning_ai_dedupe_urls(array_merge(
            aruaru_learning_ai_dedupe_urls($enExtra),
            $enBase
        ));
    }

    return $panel;
}

/**
 * @return array{ja: list<array>, en: list<array>}|null
 */
function aruaru_learning_ai_fetch_links(string $panelKey, string $techName, string $kind): ?array
{
    if (!defined('OPENAI_API_KEY') || OPENAI_API_KEY === '') {
        return null;
    }

    $kindJa = match ($kind) {
        'language' => 'プログラミング言語',
        'framework' => 'フレームワーク／ライブラリ',
        'database' => 'データベース／ストレージ',
        default => 'クラウド／インフラ',
    };

    $prompt = <<<EOT
技術: 「{$techName}」（種別: {$kindJa}）
2026年時点で、初心者〜中級者がオンラインで学べる「日本語の学習ページ・公式ドキュメント・動画学習サービス（Progate / ドットインストール / paiza / Schoo / Udemy 等）」を中心に、実在する https URL を選んでください。
公式ドキュメントの日本語版があれば優先。

次のJSONのみを返す（説明文禁止）。各配列は最大10件。url は必ず https で始まる実在URL。
{
  "ja": [ {"name":"サイト名","url":"https://...","features":"一言","price":"無料／月額目安など"} ],
  "en": [ {"name":"Site","url":"https://...","features":"one line","price":"Free / paid hint"} ]
}
EOT;

    $payload = json_encode([
        'model'       => 'gpt-4o-mini',
        'temperature' => 0.25,
        'max_tokens'  => 2200,
        'messages'    => [
            ['role' => 'system', 'content' => 'You output JSON only. Never invent domains; use well-known real URLs only.'],
            ['role' => 'user', 'content' => $prompt],
        ],
    ], JSON_UNESCAPED_UNICODE);

    $ctx = stream_context_create(['http' => [
        'method'  => 'POST',
        'timeout' => 35,
        'header'  => "Content-Type: application/json\r\nAuthorization: Bearer " . OPENAI_API_KEY . "\r\n",
        'content' => $payload,
        'ignore_errors' => true,
    ]]);
    $raw = @file_get_contents('https://api.openai.com/v1/chat/completions', false, $ctx);
    if (!$raw) {
        return null;
    }
    $resp = json_decode($raw, true);
    if (!is_array($resp)) {
        return null;
    }
    $text = trim((string)($resp['choices'][0]['message']['content'] ?? ''));
    $text = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $text);
    $obj = json_decode($text, true);
    if (!is_array($obj) || !isset($obj['ja'], $obj['en'])) {
        return null;
    }
    $ja = aruaru_learning_ai_dedupe_urls(is_array($obj['ja']) ? $obj['ja'] : []);
    $en = aruaru_learning_ai_dedupe_urls(is_array($obj['en']) ? $obj['en'] : []);
    if ($ja === [] && $en === []) {
        return null;
    }

    return ['ja' => $ja, 'en' => $en];
}

/**
 * @param array{languages?:list,frameworks?:list,databases?:list} $rankingData
 */
function aruaru_learning_ai_cron_refresh(array $rankingData, int $maxPerRun = 12, bool $verbose = false): void
{
    if (!defined('OPENAI_API_KEY') || OPENAI_API_KEY === '') {
        if ($verbose && function_exists('aruaru_tech_sync_log')) {
            aruaru_tech_sync_log('[learning-ai] OPENAI_API_KEY 未設定のためスキップ', true);
        }

        return;
    }

    $keys = [];
    foreach ($rankingData['languages'] ?? [] as $row) {
        $n = trim((string)($row['name'] ?? ''));
        if ($n !== '') {
            $keys[] = 'lang:' . $n;
        }
    }
    foreach ($rankingData['frameworks'] ?? [] as $row) {
        $n = trim((string)($row['name'] ?? ''));
        if ($n !== '') {
            $keys[] = 'fw:' . $n;
        }
    }
    foreach ($rankingData['databases'] ?? [] as $row) {
        $n = trim((string)($row['name'] ?? ''));
        if ($n !== '') {
            $keys[] = 'db:' . $n;
        }
    }
    $keys[] = 'lang:Mojo';
    $keys[] = 'fw:WunderGraph';
    $keys[] = 'fw:VersionlessAPI';
    $keys[] = 'cloud:AWS';

    $cache = aruaru_learning_ai_load_cache();
    $entries = $cache['entries'] ?? [];
    $now = time();
    $ttl = 86400; // 毎日更新
    $done = 0;

    foreach ($keys as $key) {
        if ($done >= $maxPerRun) {
            break;
        }
        $prev = $entries[$key] ?? null;
        if (is_array($prev) && (($prev['updated'] ?? 0) + $ttl) > $now) {
            continue;
        }
        if (!preg_match('/^(lang|fw|db|cloud):(.+)$/u', $key, $m)) {
            continue;
        }
        $pfx = $m[1];
        $name = $m[2];
        $kind = match ($pfx) {
            'lang' => 'language',
            'fw' => 'framework',
            'db' => 'database',
            default => 'cloud',
        };
        $fetched = aruaru_learning_ai_fetch_links($key, $name, $kind);
        if ($fetched === null) {
            continue;
        }
        $entries[$key] = [
            'ja'      => $fetched['ja'],
            'en'      => $fetched['en'],
            'updated' => $now,
            'source'  => 'openai',
        ];
        $done++;
        usleep(350000);
    }

    $cache['entries'] = $entries;
    $cache['meta']['last_cron_refresh'] = $now;
    $cache['meta']['last_refresh_count'] = $done;
    aruaru_learning_ai_save_cache($cache);

    if ($verbose && function_exists('aruaru_tech_sync_log')) {
        aruaru_tech_sync_log('[learning-ai] 更新 ' . $done . ' 件 → ' . ARUARU_LEARNING_AI_CACHE, true);
    }
}

/* ===== END: aruaru-learning-links-ai.php ===== */


/* ===== BEGIN: aruaru-learning-prices.php ===== */

/**
 * Progate / ドットインストール / paiza の月額表示をキャッシュし、
 * 公式ページを取得して数値が変わったときに自動更新する（日次想定）。
 */


if (!defined('ARUARU_LEARNING_PRICES_CACHE')) {
    define('ARUARU_LEARNING_PRICES_CACHE', __DIR__ . '/aruaru-learning-prices-cache.json');
}

/** 手動で上書きしたい場合のデフォルト（税込・月額の目安） */
function aruaru_learning_prices_defaults(): array
{
    return [
        'progate_monthly'    => 1490,
        'dotinstall_monthly' => 1480,
        'paiza_monthly'      => 1490,
    ];
}

function aruaru_learning_price_format(int $yen): string
{
    return '基本無料、￥' . number_format($yen, 0, '.', ',') . '／月';
}

/** @return array{progate:string,dotinstall:string,paiza:string} */
function aruaru_learning_service_prices(): array
{
    $def = aruaru_learning_prices_defaults();
    $data = [];
    if (is_readable(ARUARU_LEARNING_PRICES_CACHE)) {
        $raw = @file_get_contents(ARUARU_LEARNING_PRICES_CACHE);
        if ($raw !== false && $raw !== '') {
            $j = json_decode($raw, true);
            if (is_array($j)) {
                $data = $j;
            }
        }
    }
    $pg = (int)($data['progate_monthly'] ?? $def['progate_monthly']);
    $di = (int)($data['dotinstall_monthly'] ?? $def['dotinstall_monthly']);
    $pz = (int)($data['paiza_monthly'] ?? $def['paiza_monthly']);
    $clamp = static function (int $v, int $fallback): int {
        return ($v >= 100 && $v <= 999999) ? $v : $fallback;
    };
    $pg = $clamp($pg, $def['progate_monthly']);
    $di = $clamp($di, $def['dotinstall_monthly']);
    $pz = $clamp($pz, $def['paiza_monthly']);

    return [
        'progate'    => aruaru_learning_price_format($pg),
        'dotinstall' => aruaru_learning_price_format($di),
        'paiza'      => aruaru_learning_price_format($pz),
    ];
}

function aruaru_learning_prices_http_get(string $url, int $timeout = 10): ?string
{
    $ctx = stream_context_create([
        'http' => [
            'method'  => 'GET',
            'timeout' => $timeout,
            'header'  => "User-Agent: Mozilla/5.0 (compatible; aruaru-learning-prices/1.0)\r\nAccept: text/html,application/xhtml+xml\r\n",
            'ignore_errors' => true,
        ],
        'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
    ]);
    $html = @file_get_contents($url, false, $ctx);
    return ($html !== false && $html !== '') ? $html : null;
}

/**
 * HTML から「月額っぽい」円金額を推定（1,000〜99,999 円）
 * @return list<int>
 */
function aruaru_learning_prices_scan_amounts(string $html): array
{
    $found = [];
    $patterns = [
        '/月額[^0-9¥]{0,60}?¥?\s*([0-9]{1,3}(?:,[0-9]{3})|[0-9]{4,5})\s*円/u',
        '/¥\s*([0-9]{1,3}(?:,[0-9]{3})|[0-9]{4,5})\s*\/\s*月/u',
        '/([0-9]{1,3}(?:,[0-9]{3})|[0-9]{4,5})\s*円\s*\/\s*月/u',
        '/プレミアム[^0-9¥]{0,80}?¥?\s*([0-9]{1,3}(?:,[0-9]{3})|[0-9]{4,5})\s*円/u',
        '/有料[^0-9¥]{0,80}?¥?\s*([0-9]{1,3}(?:,[0-9]{3})|[0-9]{4,5})\s*円/u',
    ];
    foreach ($patterns as $re) {
        if (preg_match_all($re, $html, $m)) {
            foreach ($m[1] as $s) {
                $n = (int)str_replace(',', '', (string)$s);
                if ($n >= 1000 && $n <= 99999) {
                    $found[] = $n;
                }
            }
        }
    }
    return array_values(array_unique($found));
}

/** 既存値に近い候補を優先し、なければレンジ内の最大（値上げ検知向け） */
function aruaru_learning_prices_pick_amount(array $candidates, int $previous): int
{
    if ($candidates === []) {
        return $previous;
    }
    $best = null;
    $bestDist = PHP_INT_MAX;
    foreach ($candidates as $n) {
        $d = abs($n - $previous);
        if ($d < $bestDist || ($d === $bestDist && $n > (int)$best)) {
            $bestDist = $d;
            $best = $n;
        }
    }
    if ($best !== null && $best >= 500 && $best <= 50000) {
        return (int)$best;
    }
    $inRange = array_values(array_filter($candidates, static fn(int $n): bool => $n >= 1000 && $n <= 29999));
    return $inRange !== [] ? max($inRange) : $previous;
}

/**
 * 外部取得してキャッシュ更新。失敗時は既存キャッシュを維持。
 * @return array<string, mixed> 保存したデータ（ログ用）
 */
function aruaru_learning_prices_refresh(bool $verbose = false): array
{
    $def = aruaru_learning_prices_defaults();
    $cur = $def;
    if (is_readable(ARUARU_LEARNING_PRICES_CACHE)) {
        $old = json_decode((string)@file_get_contents(ARUARU_LEARNING_PRICES_CACHE), true);
        if (is_array($old)) {
            foreach (['progate_monthly', 'dotinstall_monthly', 'paiza_monthly'] as $k) {
                if (isset($old[$k]) && is_numeric($old[$k])) {
                    $cur[$k] = max(100, min(999999, (int)$old[$k]));
                }
            }
        }
    }

    $urls = [
        'dotinstall_monthly' => 'https://dotinstall.com/',
        'progate_monthly'    => 'https://prog-8.com/',
        'paiza_monthly'      => 'https://paiza.jp/works',
    ];
    $sources = [];
    $next = $cur;

    foreach ($urls as $key => $url) {
        $html = aruaru_learning_prices_http_get($url, 12);
        $prev = (int)$next[$key];
        if ($html === null) {
            $sources[$key] = 'fetch_failed';
            continue;
        }
        $amounts = aruaru_learning_prices_scan_amounts($html);
        if ($amounts === []) {
            $sources[$key] = 'no_match';
            continue;
        }
        $picked = aruaru_learning_prices_pick_amount($amounts, $prev);
        $next[$key] = $picked;
        $sources[$key] = ($picked === $prev) ? 'unchanged' : ('updated:' . $picked);
    }

    $payload = array_merge($next, [
        'updated_at' => date('c'),
        'sources'    => $sources,
    ]);
    $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($json !== false) {
        @file_put_contents(ARUARU_LEARNING_PRICES_CACHE, $json, LOCK_EX);
    }

    if ($verbose) {
        $line = '[learning-prices] ' . json_encode($sources, JSON_UNESCAPED_UNICODE) . ' → saved';
        if (function_exists('aruaru_tech_sync_log')) {
            aruaru_tech_sync_log($line, true);
        } else {
            fwrite(STDERR, $line . PHP_EOL);
        }
    }

    return $payload;
}

/** キャッシュが無い or 24h 超なら取得を試みる（Web 初回アクセス用） */
function aruaru_learning_prices_refresh_if_stale(): void
{
    if (!is_readable(ARUARU_LEARNING_PRICES_CACHE)) {
        aruaru_learning_prices_refresh(false);
        return;
    }
    $age = time() - (int)@filemtime(ARUARU_LEARNING_PRICES_CACHE);
    if ($age > 86400) {
        aruaru_learning_prices_refresh(false);
    }
}

/* ===== END: aruaru-learning-prices.php ===== */


/* ===== BEGIN: aruaru-eikaiwa-ranking.php ===== */

/**
 * aruaru-eikaiwa-ranking.php
 * スマホ・タブレット・PC対応 優れた英会話アプリ・サイト TOP50
 * キャッシュTTL: 7日（週1回自動更新）
 */


if (!defined('ARUARU_EIKAIWA_CACHE')) {
    define('ARUARU_EIKAIWA_CACHE', __DIR__ . '/aruaru-eikaiwa-ranking-cache.json');
}
if (!defined('ARUARU_EIKAIWA_TTL')) {
    define('ARUARU_EIKAIWA_TTL', 86400); // 毎日更新（旧:7日）
}

/**
 * 英会話アプリ・サイト マスターデータ TOP50
 * @return list<array{rank:int,name:string,platform:string,style:string,level:string,price:string,url:string,ai:bool,note:string}>
 */
function aruaru_eikaiwa_master_pool(): array
{
    return [
        // ===== スマホ・タブレット・PC 全対応 =====
        ['rank'=> 1,  'name'=>'Duolingo',             'platform'=>'📱💻', 'style'=>'ゲーム感覚',        'level'=>'初級〜中級',   'price'=>'無料（Pro月額¥1,250〜）',   'url'=>'https://ja.duolingo.com/',                'ai'=>false, 'note'=>'世界最大の語学学習アプリ。毎日継続しやすいゲーム形式'],
        ['rank'=> 2,  'name'=>'Speak',                 'platform'=>'📱💻', 'style'=>'AIスピーキング',    'level'=>'初級〜上級',   'price'=>'月額¥2,000〜',              'url'=>'https://www.speak.com/ja',                'ai'=>true,  'note'=>'AIが発音・会話をリアルタイム採点。話す練習特化'],
        ['rank'=> 3,  'name'=>'ELSA Speak',            'platform'=>'📱',   'style'=>'発音矯正AI',        'level'=>'初級〜上級',   'price'=>'無料（Pro月額¥1,500〜）',   'url'=>'https://elsaspeak.com/ja/',               'ai'=>true,  'note'=>'AI音声認識で発音を細かくスコアリング'],
        ['rank'=> 4,  'name'=>'スタディサプリENGLISH', 'platform'=>'📱💻', 'style'=>'動画×AI',           'level'=>'初級〜中級',   'price'=>'月額¥2,178〜',              'url'=>'https://eigosapuri.jp/',                  'ai'=>true,  'note'=>'TOEIC・日常英会話・ビジネス英語コース充実'],
        ['rank'=> 5,  'name'=>'Cambly',                'platform'=>'📱💻', 'style'=>'ネイティブ講師',    'level'=>'初級〜上級',   'price'=>'月額¥5,000〜',              'url'=>'https://www.cambly.com/english?lang=ja',  'ai'=>false, 'note'=>'24時間いつでもネイティブ講師とビデオ通話'],
        ['rank'=> 6,  'name'=>'DMM英会話',             'platform'=>'📱💻', 'style'=>'オンラインレッスン','level'=>'初級〜上級',   'price'=>'月額¥6,480〜',              'url'=>'https://eikaiwa.dmm.com/',                'ai'=>false, 'note'=>'日本最大級。130ヶ国以上の講師から選択可'],
        ['rank'=> 7,  'name'=>'ネイティブキャンプ',    'platform'=>'📱💻', 'style'=>'オンライン無制限',  'level'=>'初級〜上級',   'price'=>'月額¥6,480〜',              'url'=>'https://nativecamp.net/',                 'ai'=>false, 'note'=>'月額定額で回数無制限レッスン。スキマ時間に最適'],
        ['rank'=> 8,  'name'=>'Rosetta Stone',         'platform'=>'📱💻', 'style'=>'没入型学習',        'level'=>'初級〜中級',   'price'=>'月額¥2,940〜',              'url'=>'https://www.rosettastone.com/lp/ja/',     'ai'=>true,  'note'=>'50年以上の実績。絵と音声のみで直感的に学ぶ'],
        ['rank'=> 9,  'name'=>'Babbel',                'platform'=>'📱💻', 'style'=>'対話型レッスン',    'level'=>'初級〜中級',   'price'=>'月額¥1,633〜',              'url'=>'https://ja.babbel.com/',                  'ai'=>false, 'note'=>'実際の会話シーンを再現した短いレッスンが得意'],
        ['rank'=>10,  'name'=>'Busuu',                 'platform'=>'📱💻', 'style'=>'ネイティブ添削',    'level'=>'初級〜中級',   'price'=>'無料（Premium月額¥1,067〜）','url'=>'https://www.busuu.com/ja',                'ai'=>false, 'note'=>'世界中のネイティブにフィードバックしてもらえる'],
        ['rank'=>11,  'name'=>'Pimsleur',              'platform'=>'📱💻', 'style'=>'リスニング特化',    'level'=>'初級〜中級',   'price'=>'月額¥2,100〜',              'url'=>'https://www.pimsleur.com/',               'ai'=>false, 'note'=>'音声主体。通勤・家事しながら耳で学ぶのに最適'],
        ['rank'=>12,  'name'=>'HelloTalk',             'platform'=>'📱',   'style'=>'言語交換SNS',       'level'=>'初級〜上級',   'price'=>'無料（VIP月額¥1,000〜）',   'url'=>'https://www.hellotalk.com/?lang=ja',      'ai'=>false, 'note'=>'世界中のネイティブと相互に言語を教え合う'],
        ['rank'=>13,  'name'=>'Tandem',                'platform'=>'📱',   'style'=>'言語パートナー',    'level'=>'初級〜上級',   'price'=>'無料（Pro月額¥1,800〜）',   'url'=>'https://www.tandem.net/ja',               'ai'=>false, 'note'=>'テキスト・音声・ビデオで言語交換パートナーと練習'],
        ['rank'=>14,  'name'=>'Anki',                  'platform'=>'📱💻', 'style'=>'フラッシュカード',  'level'=>'全レベル',     'price'=>'PC無料（iOS¥3,000）',       'url'=>'https://apps.ankiweb.net/',               'ai'=>false, 'note'=>'間隔反復学習で単語・フレーズを効率的に記憶'],
        ['rank'=>15,  'name'=>'Memrise',               'platform'=>'📱💻', 'style'=>'記憶術×動画',       'level'=>'初級〜中級',   'price'=>'無料（Pro月額¥1,500〜）',   'url'=>'https://www.memrise.com/',                'ai'=>true,  'note'=>'ネイティブの動画クリップで自然な表現を習得'],
        ['rank'=>16,  'name'=>'BBC Learning English',  'platform'=>'📱💻', 'style'=>'ニュース×学習',     'level'=>'中級〜上級',   'price'=>'完全無料',                  'url'=>'https://www.bbc.co.uk/learningenglish/',  'ai'=>false, 'note'=>'BBCが提供する無料の高品質英語学習コンテンツ'],
        ['rank'=>17,  'name'=>'VOA Learning English',  'platform'=>'📱💻', 'style'=>'ニュース英語',      'level'=>'初級〜中級',   'price'=>'完全無料',                  'url'=>'https://learningenglish.voanews.com/',    'ai'=>false, 'note'=>'米国政府系メディアによるゆっくり英語ニュース'],
        ['rank'=>18,  'name'=>'TED',                   'platform'=>'📱💻', 'style'=>'スピーチ動画',      'level'=>'中級〜上級',   'price'=>'完全無料',                  'url'=>'https://www.ted.com/',                    'ai'=>false, 'note'=>'世界トップクラスの講演動画。字幕付きで聴ける'],
        ['rank'=>19,  'name'=>'YouTube（英語学習CH）', 'platform'=>'📱💻', 'style'=>'動画学習',          'level'=>'全レベル',     'price'=>'無料（Premium¥1,280〜/月）', 'url'=>'https://www.youtube.com/',                'ai'=>false, 'note'=>'EnglishAddict・Rachel\'sEnglishなど良質CHが豊富'],
        ['rank'=>20,  'name'=>'Coursera',              'platform'=>'📱💻', 'style'=>'大学レベル講座',    'level'=>'中級〜上級',   'price'=>'無料聴講〜月額¥5,900〜',    'url'=>'https://www.coursera.org/',               'ai'=>false, 'note'=>'米国トップ大学の英語コースを世界中から受講可'],
        ['rank'=>21,  'name'=>'edX',                   'platform'=>'📱💻', 'style'=>'大学講座',          'level'=>'中級〜上級',   'price'=>'無料〜（証明書有料）',       'url'=>'https://www.edx.org/',                    'ai'=>false, 'note'=>'MIT・ハーバードなど名門大の無料英語コース'],
        ['rank'=>22,  'name'=>'Udemy英語コース',       'platform'=>'📱💻', 'style'=>'録画講座',          'level'=>'初級〜上級',   'price'=>'買切り¥1,500〜',            'url'=>'https://www.udemy.com/ja/',               'ai'=>false, 'note'=>'セールで格安取得可能な豊富な英会話コース'],
        ['rank'=>23,  'name'=>'iKnow!',                'platform'=>'📱💻', 'style'=>'脳科学記憶',        'level'=>'初級〜中級',   'price'=>'月額¥1,500〜',              'url'=>'https://iknow.jp/',                       'ai'=>false, 'note'=>'脳科学に基づく間隔反復で英単語・例文を定着'],
        ['rank'=>24,  'name'=>'abceed',                'platform'=>'📱💻', 'style'=>'TOEIC特化AI',       'level'=>'初級〜上級',   'price'=>'無料（Premium月額¥2,233〜）','url'=>'https://www.abceed.com/',                 'ai'=>true,  'note'=>'AI分析でTOEICスコアアップに特化した最強アプリ'],
        ['rank'=>25,  'name'=>'英語物語',              'platform'=>'📱',   'style'=>'RPGゲーム学習',     'level'=>'初級〜中級',   'price'=>'無料（アイテム課金）',       'url'=>'https://eigomonogatari.com/',             'ai'=>false, 'note'=>'RPG形式で楽しみながら英単語・文法を習得'],
        ['rank'=>26,  'name'=>'Mondly',                'platform'=>'📱💻', 'style'=>'AR×VR×AI',          'level'=>'初級〜中級',   'price'=>'月額¥1,170〜',              'url'=>'https://www.mondly.com/',                 'ai'=>true,  'note'=>'AR・VR対応の次世代英語学習。会話シミュレーション'],
        ['rank'=>27,  'name'=>'Clozemaster',           'platform'=>'📱💻', 'style'=>'穴埋め文脈学習',    'level'=>'中級〜上級',   'price'=>'無料（Pro月額¥1,000〜）',   'url'=>'https://www.clozemaster.com/',            'ai'=>false, 'note'=>'文脈の中で大量の英文に触れて上級語彙を習得'],
        ['rank'=>28,  'name'=>'Cake英語学習',          'platform'=>'📱',   'style'=>'動画フレーズ',      'level'=>'初級〜中級',   'price'=>'無料（Premium月額¥980〜）', 'url'=>'https://mycake.me/',                      'ai'=>false, 'note'=>'海外ドラマ・映画のワンシーンで自然な英語を習得'],
        ['rank'=>29,  'name'=>'シャドテン',            'platform'=>'📱💻', 'style'=>'シャドーイング特化', 'level'=>'初級〜上級',  'price'=>'月額¥6,480〜',              'url'=>'https://shadowten.com/',                  'ai'=>true,  'note'=>'AIコーチングでシャドーイングを毎日添削'],
        ['rank'=>30,  'name'=>'fluentu',               'platform'=>'📱💻', 'style'=>'動画×字幕学習',     'level'=>'初級〜上級',   'price'=>'月額¥3,240〜',              'url'=>'https://www.fluentu.com/',                'ai'=>false, 'note'=>'YouTube動画に双方向字幕・単語帳が連動'],
        ['rank'=>31,  'name'=>'Speechling',            'platform'=>'📱💻', 'style'=>'発音コーチング',    'level'=>'初級〜上級',   'price'=>'無料（Pro月額¥2,000〜）',   'url'=>'https://speechling.com/',                 'ai'=>false, 'note'=>'録音した発音をネイティブコーチが無料添削'],
        ['rank'=>32,  'name'=>'LanguageTransfer',      'platform'=>'📱💻', 'style'=>'思考型音声講座',    'level'=>'初級〜中級',   'price'=>'完全無料',                  'url'=>'https://www.languagetransfer.org/',       'ai'=>false, 'note'=>'考えながら聴く独自メソッドで文法を直感習得'],
        ['rank'=>33,  'name'=>'Lingoda',               'platform'=>'📱💻', 'style'=>'グループレッスン',  'level'=>'初級〜上級',   'price'=>'月額¥8,000〜',              'url'=>'https://www.lingoda.com/ja/',             'ai'=>false, 'note'=>'少人数グループで欧州式カリキュラムの本格授業'],
        ['rank'=>34,  'name'=>'italki',                'platform'=>'📱💻', 'style'=>'1対1レッスン',      'level'=>'全レベル',     'price'=>'1回¥500〜（講師による）',   'url'=>'https://www.italki.com/',                 'ai'=>false, 'note'=>'世界中のプロ・コミュニティ講師から自由に選択'],
        ['rank'=>35,  'name'=>'Preply',                'platform'=>'📱💻', 'style'=>'マンツーマン',      'level'=>'全レベル',     'price'=>'月額¥5,000〜（講師選択）',  'url'=>'https://preply.com/',                     'ai'=>false, 'note'=>'AIが最適な講師をマッチング。試し体験あり'],
        ['rank'=>36,  'name'=>'英語にあそぼ（NHK）',  'platform'=>'💻',   'style'=>'NHK教育動画',       'level'=>'超初級',       'price'=>'完全無料',                  'url'=>'https://www.nhk.or.jp/school/eigoni/',    'ai'=>false, 'note'=>'NHKが子ども向けに提供。家族で楽しめる'],
        ['rank'=>37,  'name'=>'NHK World English',    'platform'=>'📱💻', 'style'=>'ニュース・教養',    'level'=>'中級〜上級',   'price'=>'完全無料',                  'url'=>'https://www3.nhk.or.jp/nhkworld/',        'ai'=>false, 'note'=>'NHKの英語国際放送。日本に関する英語ニュースが豊富'],
        ['rank'=>38,  'name'=>'Glossika',              'platform'=>'📱💻', 'style'=>'文章反復訓練',      'level'=>'中級〜上級',   'price'=>'月額¥1,800〜',              'url'=>'https://ai.glossika.com/',                'ai'=>true,  'note'=>'大量の例文を繰り返し聴いて話す流暢さ養成法'],
        ['rank'=>39,  'name'=>'LingQ',                 'platform'=>'📱💻', 'style'=>'多読・多聴',        'level'=>'初級〜上級',   'price'=>'無料（Premium月額¥1,167〜）','url'=>'https://www.lingq.com/ja/',               'ai'=>false, 'note'=>'本物の英文コンテンツで語彙を自動ハイライト学習'],
        ['rank'=>40,  'name'=>'Nativshark',            'platform'=>'📱💻', 'style'=>'構造的カリキュラム','level'=>'初級〜中級',   'price'=>'月額¥2,900〜',              'url'=>'https://nativshark.com/',                 'ai'=>false, 'note'=>'体系的なカリキュラムで日常英会話を着実に習得'],
        ['rank'=>41,  'name'=>'Chatterbug',            'platform'=>'📱💻', 'style'=>'テキスト×動画',     'level'=>'初級〜中級',   'price'=>'月額¥5,800〜',              'url'=>'https://chatterbug.com/',                 'ai'=>false, 'note'=>'自習アプリ＋週1ネイティブライブレッスンの組合せ'],
        ['rank'=>42,  'name'=>'Speechace',             'platform'=>'📱💻', 'style'=>'発音AI採点',        'level'=>'初級〜上級',   'price'=>'無料（Pro月額¥1,500〜）',   'url'=>'https://www.speechace.com/',              'ai'=>true,  'note'=>'CEFR対応。音節レベルで発音をスコアリング'],
        ['rank'=>43,  'name'=>'Beelinguapp',           'platform'=>'📱',   'style'=>'バイリンガル読書',  'level'=>'初級〜中級',   'price'=>'無料（Pro¥720〜）',         'url'=>'https://www.beelinguapp.com/',            'ai'=>false, 'note'=>'日英並列表示で物語を読みながら英語習得'],
        ['rank'=>44,  'name'=>'Toucan',                'platform'=>'💻',   'style'=>'ブラウザ拡張',      'level'=>'初級〜中級',   'price'=>'無料（Pro月額¥500〜）',     'url'=>'https://jointoucan.com/',                 'ai'=>false, 'note'=>'Webブラウジング中に自動で英単語が表示され学習'],
        ['rank'=>45,  'name'=>'Mango Languages',       'platform'=>'📱💻', 'style'=>'会話重視',          'level'=>'初級〜中級',   'price'=>'月額¥2,500〜（図書館無料）', 'url'=>'https://mangolanguages.com/',             'ai'=>false, 'note'=>'図書館カード会員は無料。実用的な会話フレーズ中心'],
        ['rank'=>46,  'name'=>'Yabla',                 'platform'=>'📱💻', 'style'=>'ネイティブ動画',    'level'=>'初級〜上級',   'price'=>'月額¥1,200〜',              'url'=>'https://english.yabla.com/',              'ai'=>false, 'note'=>'本物の映像コンテンツにインタラクティブ字幕連動'],
        ['rank'=>47,  'name'=>'EnglishClass101',       'platform'=>'📱💻', 'style'=>'ポッドキャスト×動画','level'=>'初級〜上級',  'price'=>'無料〜月額¥2,000〜',        'url'=>'https://www.englishclass101.com/',         'ai'=>false, 'note'=>'ポッドキャスト形式の大量コンテンツで通勤学習'],
        ['rank'=>48,  'name'=>'Magoosh TOEFL/IELTS',  'platform'=>'📱💻', 'style'=>'試験対策',          'level'=>'中級〜上級',   'price'=>'月額¥3,000〜',              'url'=>'https://magoosh.com/',                    'ai'=>false, 'note'=>'TOEFL・IELTS専門。高品質な問題集と解説動画'],
        ['rank'=>49,  'name'=>'ChatGPT英会話練習',    'platform'=>'📱💻', 'style'=>'AI対話',            'level'=>'全レベル',     'price'=>'無料（Plus月額¥3,000〜）',  'url'=>'https://chat.openai.com/',                'ai'=>true,  'note'=>'ChatGPTに「英会話の練習相手になって」と依頼するだけ'],
        ['rank'=>50,  'name'=>'Claude英会話練習',     'platform'=>'📱💻', 'style'=>'AI対話',            'level'=>'全レベル',     'price'=>'無料（Pro月額¥3,000〜）',   'url'=>'https://claude.ai/',                      'ai'=>true,  'note'=>'自然な会話練習・文法修正・英作文添削に最適なAI'],
    ];
}

/**
 * キャッシュからデータ取得。TTL超過なら再生成。
 * @return array{updated_at:string,rows:list<array>}
 */
function aruaru_eikaiwa_ranking_get(): array
{
    if (is_readable(ARUARU_EIKAIWA_CACHE)) {
        $age = time() - (int)@filemtime(ARUARU_EIKAIWA_CACHE);
        if ($age < ARUARU_EIKAIWA_TTL) {
            $raw = @file_get_contents(ARUARU_EIKAIWA_CACHE);
            if ($raw !== false && $raw !== '') {
                $j = json_decode($raw, true);
                if (is_array($j) && !empty($j['rows'])) {
                    return $j;
                }
            }
        }
    }
    return aruaru_eikaiwa_ranking_refresh(false);
}

/**
 * キャッシュを生成・保存して返す
 * @return array{updated_at:string,rows:list<array>}
 */
function aruaru_eikaiwa_ranking_refresh(bool $verbose = false): array
{
    $pool = aruaru_eikaiwa_master_pool();
    usort($pool, fn($a, $b) => ($a['rank'] ?? 99) <=> ($b['rank'] ?? 99));
    $data = [
        'updated_at' => date('Y-m-d H:i:s'),
        'ttl_days'   => 7,
        'rows'       => $pool,
    ];
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($json !== false) {
        @file_put_contents(ARUARU_EIKAIWA_CACHE, $json, LOCK_EX);
    }
    if ($verbose) {
        $msg = '[eikaiwa] 更新完了 ' . count($pool) . '件 → ' . ARUARU_EIKAIWA_CACHE;
        if (function_exists('aruaru_tech_sync_log')) {
            aruaru_tech_sync_log($msg, true);
        } else {
            echo $msg . PHP_EOL;
        }
    }
    return $data;
}

/* ===== END: aruaru-eikaiwa-ranking.php ===== */


/* ===== BEGIN: aruaru-download-modified-zip-lib.php ===== */

/**
 * 直近 N 時間以内に更新されたファイルを ZIP 化してダウンロードまたは CLI 保存。
 * 個別バージョン（download-modified-last-◯h.php）から読み込みます。
 */


/** @param positive-int $hours さかのぼる時間（時間単位） */
function aruaru_download_modified_zip_run(int $hours, bool $skipZipFiles = true): void
{
    if ($hours < 1 || $hours > 720) {
        http_response_code(400);
        header('Content-Type: text/plain; charset=UTF-8');
        echo 'hours は 1〜720 の範囲で指定してください。';
        exit;
    }

    $windowSeconds = $hours * 3600;
    $hoursLabel    = (string)$hours . 'h'; // CLI / ファイル名用

    $root = realpath(__DIR__);
    if ($root === false) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=UTF-8');
        echo 'パス解決に失敗しました。';
        exit;
    }

    $cutoff = time() - $windowSeconds;
    $stamp  = gmdate('Ymd-His') . 'Z';
    $isCli  = PHP_SAPI === 'cli';

    $tmp = tempnam(sys_get_temp_dir(), 'aruaru_z_');
    if ($tmp === false) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=UTF-8');
        echo '一時ファイルを作成できません。';
        exit;
    }

    @unlink($tmp);

    $za = new ZipArchive();
    if ($za->open($tmp, ZipArchive::OVERWRITE | ZipArchive::CREATE) !== true) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=UTF-8');
        echo 'ZIP を開けません。';
        exit;
    }

    $rootLen   = strlen($root) + 1;
    $fileCount = 0;

    $dirIt = new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS);
    $it    = new RecursiveIteratorIterator($dirIt, RecursiveIteratorIterator::SELF_FIRST);

    /** @var SplFileInfo $info */
    foreach ($it as $info) {
        if (!$info->isFile()) {
            continue;
        }
        $full = $info->getPathname();
        $real = realpath($full);
        if ($real === false || strncmp($real, $root, strlen($root)) !== 0) {
            continue;
        }
        if ($info->getMTime() < $cutoff) {
            continue;
        }
        if ($skipZipFiles && strcasecmp(substr($real, -4), '.zip') === 0) {
            continue;
        }

        $rel = substr($real, $rootLen);
        $rel = str_replace('\\', '/', $rel);

        $za->addFile($real, $rel);
        ++$fileCount;
    }

    if ($fileCount === 0) {
        $za->close();
        @unlink($tmp);
        http_response_code(404);
        header('Content-Type: text/plain; charset=UTF-8');
        echo '直近' . (string)$hours . '時間以内に更新されたファイルがありません。※ *.zip は既定で同梱から除外しています（呼び出し側で aruaru_download_modified_zip_run(..., false) にすると .zip も含みます）。';
        exit;
    }

    $manifestName = '_MANIFEST-LAST-' . strtoupper($hoursLabel) . '.txt';
    $manifest     = implode("\r\n", [
        'Generated: ' . gmdate('Y-m-d H:i:s') . ' UTC',
        'Window: last ' . (string)$hours . ' hours (' . (string)$windowSeconds . ' seconds)',
        'Root: ' . $root,
        'Files added (excluding this manifest): ' . (string)$fileCount,
    ]);
    $za->addFromString($manifestName, $manifest);

    if (!$za->close()) {
        @unlink($tmp);
        http_response_code(500);
        header('Content-Type: text/plain; charset=UTF-8');
        echo 'ZIP の書き込みに失敗しました。';
        exit;
    }

    if ($isCli) {
        $destZip = $root . DIRECTORY_SEPARATOR . 'aruaru-modified-last-' . $hoursLabel . '.zip';
        @unlink($destZip);
        if (!@rename($tmp, $destZip)) {
            if (!@copy($tmp, $destZip)) {
                @unlink($tmp);
                fwrite(STDERR, 'ZIP の書き込みに失敗しました: ' . $destZip . PHP_EOL);
                exit(1);
            }
            @unlink($tmp);
        }
        fwrite(STDOUT, $destZip . ' を保存しました (' . (string)$fileCount . ' ファイル + manifest)。' . PHP_EOL);
        exit(0);
    }

    $fname = 'aruaru-modified-last-' . $hoursLabel . '-' . $stamp . '.zip';
    $len   = filesize($tmp);
    if ($len === false) {
        @unlink($tmp);
        http_response_code(500);
        exit;
    }

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $fname . '"');
    header('Content-Length: ' . (string)$len);
    header('Cache-Control: no-store');

    readfile($tmp);
    @unlink($tmp);
    exit;
}

/* ===== END: aruaru-download-modified-zip-lib.php ===== */


/* ===== BEGIN: index.php (main) ===== */

/**
 * aruaru — 純粋PHP版（Laravel不要）
 * ロリポップ！含むすべての共用レンタルサーバーで
 * このファイル1つをアップするだけで動作します。
 */
 
/* ═══════════════════════════════════════════════════════════
   定数・マスターデータ
═══════════════════════════════════════════════════════════ */
 
define('LANGS', [
    'JavaScript','TypeScript','Python','Go','PHP','Ruby','Java','Kotlin',
    'Swift','Rust','C#','C++','Scala','R','Dart','Elixir','Haskell','Lua','COBOL','VBA',
]);
 
define('FRAMEWORKS', [
    'フロントエンド' => [
        'React','Next.js','Vue.js','Nuxt.js','Angular','Svelte','SvelteKit',
        'Astro','Remix','Solid.js','HTMX','Qwik','Lit','Preact',
        'TanStack Query','TanStack Router',
    ],
    'バックエンド' => [
        /* JS / TS */
        'Express','Fastify','NestJS','Hono','tRPC',
        /* Python */
        'Django','FastAPI','Flask','Streamlit','Celery',
        /* PHP */
        'Laravel','Symfony','CakePHP','CodeIgniter','Lumen',
        /* Ruby */
        'Ruby on Rails',
        /* Java / JVM */
        'Spring Boot','Micronaut','Quarkus','Play Framework','Akka',
        'ASP.NET Core','Blazor',
        /* Go */
        'Gin','Echo','Fiber','Chi',
        /* Rust */
        'Axum','Actix Web','Rocket','Poem',
        /* Elixir */
        'Phoenix',
    ],
    'モバイル' => [
        'React Native','Flutter','SwiftUI','Jetpack Compose','Expo',
        'Capacitor','Ionic',
    ],
    'インフラ／ツール' => [
        'Docker','Kubernetes','Terraform','Ansible','AWS CDK','Crossplane',
        'Podman','Nix',
        'GraphQL','REST API','OpenAPI','gRPC',
        'OpenTelemetry','Prometheus','Grafana',
        'dbt','Snowflake','LangChain','Apache Kafka','Redis',
    ],
]);
 
/* 言語 → 関連フレームワーク・ツールのマッピング
   言語チップ選択時にこのリストのFWだけを表示する */
define('LANG_FW_MAP', [
    'JavaScript'  => ['React','Next.js','Vue.js','Nuxt.js','Angular','Svelte','SvelteKit','Astro','Remix','Solid.js','HTMX','Qwik','Lit','Preact','TanStack Query','TanStack Router','Express','Fastify','NestJS','Hono','tRPC','React Native','Expo','Capacitor','Ionic','GraphQL','REST API','OpenAPI'],
    'TypeScript'  => ['React','Next.js','Vue.js','Nuxt.js','Angular','Svelte','SvelteKit','Astro','Remix','Solid.js','HTMX','Qwik','TanStack Query','TanStack Router','Express','Fastify','NestJS','Hono','tRPC','React Native','Expo','Capacitor','Ionic','GraphQL','REST API','OpenAPI'],
    'Python'      => ['Django','FastAPI','Flask','Streamlit','Celery','REST API','GraphQL','OpenAPI','Docker','Kubernetes','Ansible','dbt','Snowflake','LangChain','Apache Kafka','Redis'],
    'Go'          => ['Gin','Echo','Fiber','Chi','REST API','GraphQL','gRPC','OpenAPI','Docker','Kubernetes'],
    'PHP'         => ['Laravel','Symfony','CakePHP','CodeIgniter','Lumen','REST API','GraphQL','OpenAPI','Docker'],
    'Ruby'        => ['Ruby on Rails','REST API','GraphQL','OpenAPI','Docker'],
    'Java'        => ['Spring Boot','Micronaut','Quarkus','Play Framework','REST API','GraphQL','gRPC','OpenAPI','Docker','Kubernetes'],
    'Kotlin'      => ['Spring Boot','Jetpack Compose','REST API','GraphQL','Docker'],
    'Swift'       => ['SwiftUI','REST API','GraphQL'],
    'Rust'        => ['Axum','Actix Web','Rocket','Poem','REST API','GraphQL','gRPC','OpenAPI','Docker','Kubernetes'],
    'C#'          => ['ASP.NET Core','Blazor','REST API','GraphQL','gRPC','OpenAPI','Docker'],
    'C++'         => ['REST API','gRPC','OpenAPI','Docker'],
    'Scala'       => ['Play Framework','Akka','REST API','Docker','Kubernetes','Snowflake'],
    'R'           => ['dbt','Snowflake'],
    'Dart'        => ['Flutter','Expo'],
    'Elixir'      => ['Phoenix','REST API','GraphQL'],
    'Haskell'     => ['REST API'],
    'Lua'         => ['REST API'],
    'COBOL'       => [],
    'VBA'         => [],
]);
 
/* 勤務地グループ定義
   value : フィルタに使うキー（GETパラメータ）
   label : 表示名
   match : フィルタ時に prefecture フィールドと照合する文字列の配列
           空配列 = 全国（制限なし） */
define('LOCATION_GROUPS', [
    ['value'=>'',                   'label'=>'日本全国（指定なし）',   'match'=>[]],
    // ─── 北海道・東北 ───────────────────────────────────────────
    ['value'=>'北海道全域',         'label'=>'北海道全域',             'match'=>['北海道']],
    ['value'=>'青森県',             'label'=>'青森県',                 'match'=>['青森県']],
    ['value'=>'岩手県',             'label'=>'岩手県',                 'match'=>['岩手県']],
    ['value'=>'宮城県',             'label'=>'宮城県',                 'match'=>['宮城県']],
    ['value'=>'秋田県',             'label'=>'秋田県',                 'match'=>['秋田県']],
    ['value'=>'山形県',             'label'=>'山形県',                 'match'=>['山形県']],
    ['value'=>'福島県',             'label'=>'福島県',                 'match'=>['福島県']],
    // ─── 首都圏 ─────────────────────────────────────────────────
    ['value'=>'東京都全域',         'label'=>'東京都全域',             'match'=>['東京都']],
    ['value'=>'東京都23区内',       'label'=>'東京都23区内',           'match'=>['東京都'],'city_filter'=>'23ku'],
    ['value'=>'東京都23区外',       'label'=>'東京都23区外（多摩等）', 'match'=>['東京都'],'city_filter'=>'23ku_outside'],
    ['value'=>'神奈川県',           'label'=>'神奈川県',               'match'=>['神奈川県']],
    ['value'=>'埼玉県',             'label'=>'埼玉県',                 'match'=>['埼玉県']],
    ['value'=>'千葉県',             'label'=>'千葉県',                 'match'=>['千葉県']],
    ['value'=>'茨城県',             'label'=>'茨城県',                 'match'=>['茨城県']],
    ['value'=>'栃木県',             'label'=>'栃木県',                 'match'=>['栃木県']],
    ['value'=>'群馬県',             'label'=>'群馬県',                 'match'=>['群馬県']],
    ['value'=>'山梨県',             'label'=>'山梨県',                 'match'=>['山梨県']],
    // ─── 北陸・甲信越 ───────────────────────────────────────────
    ['value'=>'新潟県',             'label'=>'新潟県',                 'match'=>['新潟県']],
    ['value'=>'富山県',             'label'=>'富山県',                 'match'=>['富山県']],
    ['value'=>'石川県',             'label'=>'石川県',                 'match'=>['石川県']],
    ['value'=>'福井県',             'label'=>'福井県',                 'match'=>['福井県']],
    ['value'=>'長野県',             'label'=>'長野県',                 'match'=>['長野県']],
    // ─── 東海 ───────────────────────────────────────────────────
    ['value'=>'愛知県全域',         'label'=>'愛知県全域（名古屋等）', 'match'=>['愛知県']],
    ['value'=>'静岡県',             'label'=>'静岡県',                 'match'=>['静岡県']],
    ['value'=>'岐阜県',             'label'=>'岐阜県',                 'match'=>['岐阜県']],
    ['value'=>'三重県',             'label'=>'三重県',                 'match'=>['三重県']],
    // ─── 関西 ───────────────────────────────────────────────────
    ['value'=>'関西全域',           'label'=>'関西全域',               'match'=>['大阪府','京都府','兵庫県','奈良県','滋賀県','和歌山県']],
    ['value'=>'大阪府全域',         'label'=>'大阪府全域',             'match'=>['大阪府']],
    ['value'=>'大阪市内',           'label'=>'大阪市内',               'match'=>['大阪府'],'city_filter'=>'osaka_city'],
    ['value'=>'大阪市外',           'label'=>'大阪府・市外（堺・北摂等）','match'=>['大阪府'],'city_filter'=>'osaka_outside'],
    ['value'=>'京都府',             'label'=>'京都府',                 'match'=>['京都府']],
    ['value'=>'兵庫県',             'label'=>'兵庫県（神戸・尼崎等）', 'match'=>['兵庫県']],
    ['value'=>'奈良県',             'label'=>'奈良県',                 'match'=>['奈良県']],
    ['value'=>'滋賀県',             'label'=>'滋賀県',                 'match'=>['滋賀県']],
    ['value'=>'和歌山県',           'label'=>'和歌山県',               'match'=>['和歌山県']],
    // ─── 中国・四国 ─────────────────────────────────────────────
    ['value'=>'中国全域',           'label'=>'中国全域',               'match'=>['広島県','岡山県','鳥取県','島根県','山口県']],
    ['value'=>'広島県',             'label'=>'広島県',                 'match'=>['広島県']],
    ['value'=>'岡山県',             'label'=>'岡山県',                 'match'=>['岡山県']],
    ['value'=>'鳥取県',             'label'=>'鳥取県',                 'match'=>['鳥取県']],
    ['value'=>'島根県',             'label'=>'島根県',                 'match'=>['島根県']],
    ['value'=>'山口県',             'label'=>'山口県',                 'match'=>['山口県']],
    ['value'=>'四国全域',           'label'=>'四国全域',               'match'=>['徳島県','香川県','愛媛県','高知県']],
    ['value'=>'徳島県',             'label'=>'徳島県',                 'match'=>['徳島県']],
    ['value'=>'香川県',             'label'=>'香川県',                 'match'=>['香川県']],
    ['value'=>'愛媛県',             'label'=>'愛媛県',                 'match'=>['愛媛県']],
    ['value'=>'高知県',             'label'=>'高知県',                 'match'=>['高知県']],
    // ─── 九州・沖縄 ─────────────────────────────────────────────
    ['value'=>'九州全域',           'label'=>'九州全域',               'match'=>['福岡県','佐賀県','長崎県','熊本県','大分県','宮崎県','鹿児島県']],
    ['value'=>'福岡県',             'label'=>'福岡県',                 'match'=>['福岡県']],
    ['value'=>'佐賀県',             'label'=>'佐賀県',                 'match'=>['佐賀県']],
    ['value'=>'長崎県',             'label'=>'長崎県',                 'match'=>['長崎県']],
    ['value'=>'熊本県',             'label'=>'熊本県',                 'match'=>['熊本県']],
    ['value'=>'大分県',             'label'=>'大分県',                 'match'=>['大分県']],
    ['value'=>'宮崎県',             'label'=>'宮崎県',                 'match'=>['宮崎県']],
    ['value'=>'鹿児島県',           'label'=>'鹿児島県',               'match'=>['鹿児島県']],
    ['value'=>'沖縄県',             'label'=>'沖縄県',                 'match'=>['沖縄県']],
]);
 
/* 23区リスト（東京都23区内フィルタ用） */
define('TOKYO_23KU', ['千代田区','中央区','港区','新宿区','文京区','台東区','墨田区','江東区',
    '品川区','目黒区','大田区','世田谷区','渋谷区','中野区','杉並区','豊島区',
    '北区','荒川区','板橋区','練馬区','足立区','葛飾区','江戸川区']);
 
/* 大阪市内区リスト */
define('OSAKA_CITY_KU', ['北区','都島区','福島区','此花区','中央区','西区','港区','大正区',
    '天王寺区','浪速区','西淀川区','淀川区','東淀川区','東成区','生野区',
    '旭区','城東区','鶴見区','阿倍野区','住之江区','住吉区','東住吉区',
    '平野区','西成区']);
 
/* 勤務地グループからprefectureフィルタ配列を返すヘルパー */
function resolve_pref_filter(string $pref_val): array {
    foreach (LOCATION_GROUPS as $g) {
        if ($g['value'] === $pref_val) {
            return [
                'match'       => $g['match'],
                'city_filter' => $g['city_filter'] ?? '',
            ];
        }
    }
    return ['match'=>[],'city_filter'=>''];
}
 
/**
 * Wantedly 検索で AI 文脈とみなす（フリーワード・FW・言語チップから）。
 */
function wantedly_ai_context(string $q, array $langs, array $fws): bool {
    $blob = $q . "\n" . implode(' ', $langs) . "\n" . implode(' ', $fws);
    if ($blob === "\n\n") {
        return false;
    }
    if (preg_match(
        '/生成AI|機械学習|深層学習|ディープラーニング|LLM|GPT|OpenAI|ChatGPT|Copilot|'
        . '言語モデル|ベクトル|RAG|MLOps|人工知能|ニューラル|PyTorch|TensorFlow|'
        . 'プロンプト|Prompt|Whisper|Embedding|embed|ファインチューニング|Fine[\s-]?tun|'
        . 'データサイエンス|Data[\s-]?Science|AIエンジニア|AI開発|生成モデル/u',
        $blob
    )) {
        return true;
    }
    if (preg_match('/(^|[^A-Za-z])AI([^A-Za-z]|$)/u', $blob)) {
        return true;
    }
    foreach ($fws as $fw) {
        if ($fw === 'LangChain') {
            return true;
        }
    }
    return false;
}
 
/** @param string[] $parts */
function wantedly_kw_has_token(array $parts, string $token): bool {
    $flat = mb_strtolower(implode(' ', $parts));
    return mb_strpos($flat, mb_strtolower($token)) !== false;
}
 
/**
 * Wantedly /projects 検索URL（keywords パラメータ）。
 * - new=true / order=mixed / page=1 で一覧の鮮度・並びを公式UIに合わせる
 * - エンジニア職種フィルタ（occupationTypes / jp__engineering）で募集文面とズレにくくする
 * - キーワードは「言語 → FW」の順（例: PHP Laravel）。複数語は AND 想定のため拡張は最小限
 * - AI 文脈時は「機械学習」「AI」等を少量だけ付加（Python/Rust 等でも AI 案件に届きやすく）
 * - バックエンド系言語のみ「バックエンド」を1語だけ付加して関連募集を拾う
 * - FWのみ選択時は代表言語を補完（Laravel → PHP 等）
 * - Ruby 選択時は「Ruby on Rails」を Wantedly 向けに Rails に短縮
 */
function build_wantedly_url(array $langs_in, array $fws_in, string $q): string {
    $base = 'https://www.wantedly.com/projects';
 
    $fw_implies_lang = [
        'Laravel'       => 'PHP',
        'Lumen'         => 'PHP',
        'Symfony'       => 'PHP',
        'CakePHP'       => 'PHP',
        'CodeIgniter'   => 'PHP',
        'Ruby on Rails' => 'Ruby',
        'Django'        => 'Python',
        'FastAPI'       => 'Python',
        'Flask'         => 'Python',
        'Streamlit'     => 'Python',
        'Celery'        => 'Python',
        'Spring Boot'   => 'Java',
        'Micronaut'     => 'Java',
        'Quarkus'       => 'Java',
        'Play Framework'=> 'Scala',
        'Akka'          => 'Scala',
        'NestJS'        => 'TypeScript',
        'Gin'           => 'Go',
        'Echo'          => 'Go',
        'Fiber'         => 'Go',
        'Chi'           => 'Go',
        'Phoenix'       => 'Elixir',
        'ASP.NET Core'  => 'C#',
        'Blazor'        => 'C#',
    ];
 
    $langs = array_values(array_unique(array_filter($langs_in)));
    $fws   = array_values(array_unique(array_filter($fws_in)));
 
    foreach ($fws as $fw) {
        if (!empty($fw_implies_lang[$fw])) {
            $implied = $fw_implies_lang[$fw];
            if (!in_array($implied, $langs, true)) {
                $langs[] = $implied;
            }
        }
    }
 
    $fws_out = [];
    foreach ($fws as $fw) {
        if ($fw === 'Ruby on Rails' && in_array('Ruby', $langs, true)) {
            $fws_out[] = 'Rails';
            continue;
        }
        $fws_out[] = $fw;
    }
    $fws_out = array_values(array_unique($fws_out));
 
    $parts = array_merge($langs, $fws_out);
 
    if ($parts === [] && $q !== '') {
        $parts = array_values(array_filter(array_map('trim', preg_split('/[\s+]+/', $q))));
    } elseif ($q !== '' && $parts !== []) {
        /* 言語/FW指定時もフリーワードを少しだけ混ぜる（例: Python +「生成AI」）※語数は抑えて AND 過多を防ぐ */
        $qtoks = array_values(array_filter(array_map('trim', preg_split('/[\s+]+/u', $q))));
        $added = 0;
        foreach ($qtoks as $t) {
            if ($added >= 2 || $t === '' || mb_strlen($t) < 2) {
                continue;
            }
            if (!in_array($t, $parts, true) && !wantedly_kw_has_token($parts, $t)) {
                $parts[] = $t;
                $added++;
            }
        }
    }
 
    $backend_langs = ['Python', 'Go', 'Rust', 'PHP', 'Ruby', 'Java', 'Kotlin', 'Scala', 'C#', 'C++', 'Elixir'];
    $has_backend   = (bool)array_intersect($langs, $backend_langs);
    $ai_on         = wantedly_ai_context($q, $langs, $fws);
 
    if ($ai_on) {
        if (count($parts) < 10 && !wantedly_kw_has_token($parts, 'AI')) {
            $parts[] = 'AI';
        }
    } elseif ($has_backend && count($parts) < 10 && !wantedly_kw_has_token($parts, 'バックエンド')) {
        $parts[] = 'バックエンド';
    }
 
    $params = [
        'new'             => 'true',
        'page'            => '1',
        'occupationTypes' => 'jp__engineering',
        'jp__engineering' => 'jp__web_engineer',
        'order'           => 'mixed',
    ];
    if ($parts !== []) {
        $kw = mb_substr(implode(' ', $parts), 0, 200);
        $params = [
            'new'               => 'true',
            'page'              => '1',
            'keywords'          => $kw,
            'occupationTypes'   => 'jp__engineering',
            'jp__engineering'   => 'jp__web_engineer',
            'order'             => 'mixed',
        ];
    }
    return $base . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC1738);
}
 
define('EXT_SITES', [
    ['name'=>'レバテックフリーランス','tag'=>'フリーランス','type'=>'fl',
     'desc'=>'案件数・単価ともに国内最大級のITフリーランス向けエージェント。高単価・長期案件が豊富。※Rust等一部言語は当サイトのスキル一覧に無いため、その場合は全件一覧に遷移します。',
     'url'=>'https://freelance.levtech.jp/project/','levtech'=>true],
    ['name'=>'フリーランススタート','tag'=>'フリーランス','type'=>'fl',
     'desc'=>'50万件以上の案件を集約した最大級フリーランス案件データベース。キーワードで複数スキルを組み合わせて検索可能。',
     'url'=>'https://freelance-start.com/jobs?keyword=','fls'=>true],
    ['name'=>'ITあんけん','tag'=>'フリーランス','type'=>'fl',
     'desc'=>'50万件超の案件データベース。free_wordで言語とフレームワークを全角スペース区切りで組み合わせて検索可能。',
     'url'=>'','itanken'=>true],
    ['name'=>'Wantedly','tag'=>'スタートアップ','type'=>'side',
     'desc'=>'「やりたいこと」でつながる掇用サービス。スタートアップ・ベンチャーの求人が豊富。',
     'url'=>'https://www.wantedly.com/projects','wantedly'=>true],
    ['name'=>'ITプロパートナーズ','tag'=>'副業・フリーランス','type'=>'fl',
     'desc'=>'週2〜3日から参画できる副業・フリーランス案件に特化。スタートアップ系が豊富。',
     'url'=>'https://itpropartners.com/job?free_word=','lang_only'=>true],
    ['name'=>'リクルートエージェント','tag'=>'正社員求人','type'=>'sei',
     'desc'=>'リクルートの正社員求人サイト。言語キーワード（例: Python）で求人検索。',
     'url'=>'https://www.r-agent.com/job_search/','r_agent'=>true],
    ['name'=>'ハイパフォコンサル','tag'=>'コンサルフリーランス','type'=>'fl',
     'desc'=>'PM・PMO・戦略・SAP・IT・AI領域のフリーランスコンサル案件紹介。エンド直・高単価案件多数。',
     'url'=>'https://www.high-performer.jp/consultant/projects/?onlyRecruiting=true','fixed_url'=>true],
    ['name'=>'geechs job','tag'=>'フリーランス','type'=>'fl',
     'desc'=>'国内最大紹のギークスジョブ。ITフリーランスの高単価・リモート案件が豊富。個人扅当コーディネーターがサポート。',
     'url'=>'https://geechs-job.com/project/','geechs'=>true],
    ['name'=>'Midworks','tag'=>'フリーランス','type'=>'fl',
     'desc'=>'フリーランスでも社会保険・各種保障が充実。正社員並みのサポートで安心して働ける。',
     'url'=>'https://mid-works.com/projects/skills/','midworks'=>true],
    ['name'=>'クラウドテック','tag'=>'フリーランス','type'=>'fl',
     'desc'=>'クラウドワークスが運営するITフリーランス向けエージェント。多様な職種・単価帯。',
     'url'=>'https://tech.crowdworks.jp/job_offers/o/1?q=','crowdtech'=>true],
    ['name'=>'Findy Freelance','tag'=>'フリーランス','type'=>'fl',
     'desc'=>'GitHubスキルスコアで自動マッチング。エンジニア目線のフリーランス案件サービス。',
     'url'=>'https://freelance.findy-code.io/works/languages/','findy_fl'=>true],
    ['name'=>'フリーランスハブ','tag'=>'フリーランス','type'=>'fl',
     'desc'=>'複数のフリーランス案件サイトを横断検索できるアグリゲーター。効率よく案件を探せる。',
     'url'=>'https://freelance-hub.jp/','freelance_hub'=>true],
    ['name'=>'ココナラテック','tag'=>'フリーランス','type'=>'fl',
     'desc'=>'ココナラが運営するITフリーランス向けエージェント。技術スキルに特化した案件を検索可能。',
     'url'=>'https://tech.coconala.co.jp/','coconala'=>true],
    ['name'=>'Offers','tag'=>'副業・複業','type'=>'side',
     'desc'=>'副業・複業×開発案件のマッチング。スタートアップや成長企業の週1〜案件が充実。',
     'url'=>'https://offers.jp/jobs/skills/','offers'=>true],
    ['name'=>'Green','tag'=>'正社員転職','type'=>'sei',
     'desc'=>'IT・Web・ゲーム業界特化の転職サービス。正社員でキャリアアップしたい方向け。',
     'url'=>'https://www.green-japan.com/search/skill/','green'=>true],    ['name'=>'Findy（転職）','tag'=>'エンジニア転職','type'=>'sei',
     'desc'=>'スキルスコアでスカウトが届くエンジニア特化の転職サービス。高年収求人多数。',
     'url'=>'https://findy-code.io/recommends/','findy_career'=>true],
    ['name'=>'Indeed Japan','tag'=>'総合求人','type'=>'gen',
     'desc'=>'国内最大級の求人検索エンジン。正社員・契約社員・フリーランスを幅広く検索可能。',
     'url'=>'https://jp.indeed.com/jobs?q=','indeed'=>true],
    ['name'=>'Daijob（日本語）','tag'=>'バイリンガル・外資系','type'=>'sei',
     'desc'=>'日本最大級のバイリンガル・外資系・グローバル企業向け転職サイト（日本語版）。英語を活かせる求人多数。',
     'url'=>'https://www.daijob.com/jobs/search_result?kw=','daijob_jp'=>true],
    ['name'=>'Daijob (English)','tag'=>'Bilingual / Global','type'=>'sei',
     'desc'=>"Japan's largest bilingual / foreign-affiliated career site. English-language interface; no translation needed.",
     'url'=>'https://www.daijob.com/en/jobs/search_result?kw=','daijob_en'=>true],
]);
 
define('ANKEN', [
    /* フロントエンド */
    ['id'=>1,'title'=>'大規模ECサイト フロントエンド開発（React / Next.js 15）',
     'role'=>'フロントエンド','style'=>'remote','prefecture'=>'東京都','city'=>'','station'=>'',
     'location'=>'フルリモート','duration'=>'6ヶ月〜','rate'=>85,'posted'=>0,'hot'=>true,
     'langs'=>['TypeScript','JavaScript'],'fws'=>['React','Next.js','GraphQL'],
     'site_name'=>'レバテックフリーランス',
     'apply_url'=>'https://freelance.levtech.jp/project/search/?keyword=React+Next.js+TypeScript'],
 
    ['id'=>2,'title'=>'動画配信PF フロントエンド刷新（Vue 3 / Nuxt 3）',
     'role'=>'フロントエンド','style'=>'hybrid','prefecture'=>'東京都','city'=>'渋谷区','station'=>'渋谷',
     'location'=>'東京・週2出社','duration'=>'6ヶ月','rate'=>78,'posted'=>0,'hot'=>false,
     'langs'=>['TypeScript','JavaScript'],'fws'=>['Vue.js','Nuxt.js'],
     'site_name'=>'ITプロパートナーズ',
     'apply_url'=>'https://itpropartners.com/job?free_word=Vue+Nuxt'],
 
    ['id'=>3,'title'=>'API基盤開発（Python / FastAPI）',
     'role'=>'バックエンド','style'=>'remote','prefecture'=>'東京都','city'=>'','station'=>'',
     'location'=>'フルリモート','duration'=>'6ヶ月〜','rate'=>88,'posted'=>0,'hot'=>true,
     'langs'=>['Python'],'fws'=>['FastAPI','Docker'],
     'site_name'=>'ITあんけん',
     'apply_url'=>'https://itanken.com/search?free_word=Python%E3%80%80FastAPI'],
 
    ['id'=>4,'title'=>'SaaS 管理画面リニューアル（Angular 18）',
     'role'=>'フロントエンド','style'=>'onsite','prefecture'=>'神奈川県','city'=>'横浜市','station'=>'横浜',
     'location'=>'横浜・常駐','duration'=>'4ヶ月','rate'=>72,'posted'=>5,'hot'=>false,
     'langs'=>['TypeScript'],'fws'=>['Angular'],
     'site_name'=>'クラウドテック',
     'apply_url'=>'https://tech.crowdworks.jp/job_offers/o/1?q=Angular+TypeScript'],
 
    ['id'=>5,'title'=>'金融ダッシュボード 新規開発（Svelte / SvelteKit）',
     'role'=>'フロントエンド','style'=>'remote','prefecture'=>'東京都','city'=>'','station'=>'',
     'location'=>'フルリモート','duration'=>'3ヶ月〜','rate'=>80,'posted'=>1,'hot'=>false,
     'langs'=>['TypeScript','JavaScript'],'fws'=>['Svelte','SvelteKit'],
     'site_name'=>'Findy Freelance',
     'apply_url'=>'https://freelance.findy-code.io/works?q=Svelte'],
 
    ['id'=>6,'title'=>'コンテンツサイト新規構築（Astro / React）',
     'role'=>'フロントエンド','style'=>'remote','prefecture'=>'大阪府','city'=>'','station'=>'',
     'location'=>'フルリモート','duration'=>'2ヶ月','rate'=>65,'posted'=>7,'hot'=>false,
     'langs'=>['TypeScript','JavaScript'],'fws'=>['Astro','React'],
     'site_name'=>'Midworks',
     'apply_url'=>'https://mid-works.com/projects?q=Astro+React'],
 
    ['id'=>7,'title'=>'Rust + WebAssembly 高性能ブラウザアプリ開発',
     'role'=>'フロントエンド','style'=>'remote','prefecture'=>'東京都','city'=>'','station'=>'',
     'location'=>'フルリモート','duration'=>'4ヶ月','rate'=>102,'posted'=>8,'hot'=>true,
     'langs'=>['Rust','JavaScript'],'fws'=>['React'],
     'site_name'=>'Findy Freelance',
     'apply_url'=>'https://freelance.findy-code.io/works?q=Rust+WebAssembly'],
 
    /* バックエンド */
    ['id'=>8,'title'=>'決済API 設計・開発（Go / Gin）',
     'role'=>'バックエンド','style'=>'remote','prefecture'=>'東京都','city'=>'','station'=>'',
     'location'=>'フルリモート','duration'=>'長期','rate'=>95,'posted'=>1,'hot'=>true,
     'langs'=>['Go'],'fws'=>['Gin','Docker'],
     'site_name'=>'レバテックフリーランス',
     'apply_url'=>'https://freelance.levtech.jp/project/search/?keyword=Go+Gin+API'],
 
    ['id'=>9,'title'=>'Go / Echo マイクロサービス API 開発',
     'role'=>'バックエンド','style'=>'remote','prefecture'=>'大阪府','city'=>'','station'=>'',
     'location'=>'フルリモート','duration'=>'長期','rate'=>93,'posted'=>0,'hot'=>false,
     'langs'=>['Go'],'fws'=>['Echo','Docker','Kubernetes'],
     'site_name'=>'ITプロパートナーズ',
     'apply_url'=>'https://itpropartners.com/job?free_word=Go+Echo+マイクロサービス'],
 
    ['id'=>10,'title'=>'ヘルスケアSaaS API 構築（Python / FastAPI）',
     'role'=>'バックエンド','style'=>'remote','prefecture'=>'東京都','city'=>'','station'=>'',
     'location'=>'フルリモート','duration'=>'6ヶ月〜','rate'=>88,'posted'=>3,'hot'=>true,
     'langs'=>['Python'],'fws'=>['FastAPI','Docker'],
     'site_name'=>'Midworks',
     'apply_url'=>'https://mid-works.com/projects?q=Python+FastAPI'],
 
    ['id'=>11,'title'=>'Django REST API + PostgreSQL 設計・開発',
     'role'=>'バックエンド','style'=>'remote','prefecture'=>'福岡県','city'=>'','station'=>'',
     'location'=>'フルリモート','duration'=>'3ヶ月','rate'=>77,'posted'=>10,'hot'=>false,
     'langs'=>['Python'],'fws'=>['Django','REST API'],
     'site_name'=>'ITプロパートナーズ',
     'apply_url'=>'https://itpropartners.com/job?free_word=Django+Python+API'],
 
    ['id'=>12,'title'=>'ECバックエンド 機能追加（PHP / Laravel 11）',
     'role'=>'バックエンド','style'=>'hybrid','prefecture'=>'愛知県','city'=>'名古屋市','station'=>'名古屋',
     'location'=>'名古屋・週3出社','duration'=>'6ヶ月','rate'=>68,'posted'=>4,'hot'=>false,
     'langs'=>['PHP'],'fws'=>['Laravel','REST API'],
     'site_name'=>'クラウドテック',
     'apply_url'=>'https://tech.crowdworks.jp/job_offers/o/1?q=PHP+Laravel'],
 
    ['id'=>13,'title'=>'スタートアップ自社PF 開発（Ruby on Rails）',
     'role'=>'バックエンド','style'=>'hybrid','prefecture'=>'東京都','city'=>'渋谷区','station'=>'渋谷',
     'location'=>'渋谷・週2出社','duration'=>'長期','rate'=>75,'posted'=>8,'hot'=>false,
     'langs'=>['Ruby'],'fws'=>['Ruby on Rails','GraphQL'],
     'site_name'=>'Wantedly',
     'apply_url'=>build_wantedly_url(['Ruby'], ['Ruby on Rails'], '')],
 
    ['id'=>14,'title'=>'ERPシステム API 開発（Java / Spring Boot）',
     'role'=>'バックエンド','style'=>'onsite','prefecture'=>'東京都','city'=>'千代田区','station'=>'大手町',
     'location'=>'大手町・常駐','duration'=>'長期','rate'=>90,'posted'=>6,'hot'=>false,
     'langs'=>['Java'],'fws'=>['Spring Boot','Docker'],
     'site_name'=>'Indeed Japan',
     'apply_url'=>'https://jp.indeed.com/jobs?q=Java+Spring+Boot+フリーランス'],
 
    ['id'=>15,'title'=>'チャットSaaS バックエンド（Node.js / NestJS）',
     'role'=>'バックエンド','style'=>'remote','prefecture'=>'東京都','city'=>'','station'=>'',
     'location'=>'フルリモート','duration'=>'4ヶ月〜','rate'=>82,'posted'=>2,'hot'=>false,
     'langs'=>['TypeScript'],'fws'=>['NestJS','Fastify','Docker'],
     'site_name'=>'Findy Freelance',
     'apply_url'=>'https://freelance.findy-code.io/works?q=NestJS+Node.js'],
 
    ['id'=>16,'title'=>'Elixir / Phoenix リアルタイム通信基盤',
     'role'=>'バックエンド','style'=>'remote','prefecture'=>'東京都','city'=>'','station'=>'',
     'location'=>'フルリモート','duration'=>'3ヶ月','rate'=>88,'posted'=>15,'hot'=>false,
     'langs'=>['Elixir'],'fws'=>['Phoenix'],
     'site_name'=>'Wantedly',
     'apply_url'=>build_wantedly_url(['Elixir'], ['Phoenix'], '')],
 
    /* フルスタック */
    ['id'=>17,'title'=>'スタートアップ CTO候補（Next.js + FastAPI）',
     'role'=>'フルスタック','style'=>'hybrid','prefecture'=>'東京都','city'=>'港区','station'=>'六本木',
     'location'=>'東京・週3出社','duration'=>'長期','rate'=>130,'posted'=>0,'hot'=>true,
     'langs'=>['TypeScript','Python'],'fws'=>['Next.js','FastAPI','Docker'],
     'site_name'=>'Green',
     'apply_url'=>'https://www.green-japan.com/search?keyword=CTO+フルスタック+Next.js'],
 
    ['id'=>18,'title'=>'BtoB SaaS フルスタック開発（React + Rails）',
     'role'=>'フルスタック','style'=>'hybrid','prefecture'=>'大阪府','city'=>'大阪市','station'=>'梅田',
     'location'=>'大阪・週2出社','duration'=>'6ヶ月','rate'=>85,'posted'=>3,'hot'=>false,
     'langs'=>['TypeScript','Ruby'],'fws'=>['React','Ruby on Rails'],
     'site_name'=>'Offers',
     'apply_url'=>'https://offers.jp/jobs?keyword=フルスタック+React+Rails'],
 
    ['id'=>19,'title'=>'社内ツール内製開発（Vue 3 + Laravel）',
     'role'=>'フルスタック','style'=>'onsite','prefecture'=>'埼玉県','city'=>'さいたま市','station'=>'大宮',
     'location'=>'大宮・常駐','duration'=>'3ヶ月','rate'=>70,'posted'=>14,'hot'=>false,
     'langs'=>['JavaScript','PHP'],'fws'=>['Vue.js','Laravel'],
     'site_name'=>'クラウドテック',
     'apply_url'=>'https://tech.crowdworks.jp/job_offers/o/1?q=Vue+Laravel+フルスタック'],
 
    /* モバイル */
    ['id'=>20,'title'=>'iOS ショッピングアプリ（Swift / SwiftUI）',
     'role'=>'モバイル','style'=>'remote','prefecture'=>'東京都','city'=>'','station'=>'',
     'location'=>'フルリモート','duration'=>'4ヶ月','rate'=>88,'posted'=>2,'hot'=>false,
     'langs'=>['Swift'],'fws'=>['SwiftUI'],
     'site_name'=>'ITプロパートナーズ',
     'apply_url'=>'https://itpropartners.com/job?free_word=Swift+SwiftUI+iOS'],
 
    ['id'=>21,'title'=>'Android アプリ刷新（Kotlin / Jetpack Compose）',
     'role'=>'モバイル','style'=>'remote','prefecture'=>'東京都','city'=>'','station'=>'',
     'location'=>'フルリモート','duration'=>'5ヶ月','rate'=>82,'posted'=>5,'hot'=>true,
     'langs'=>['Kotlin'],'fws'=>['Jetpack Compose'],
     'site_name'=>'クラウドテック',
     'apply_url'=>'https://tech.crowdworks.jp/job_offers/o/1?q=Kotlin+Jetpack+Compose+Android'],
 
    ['id'=>22,'title'=>'クロスプラットフォームアプリ（Flutter / Dart）',
     'role'=>'モバイル','style'=>'remote','prefecture'=>'東京都','city'=>'','station'=>'',
     'location'=>'フルリモート','duration'=>'6ヶ月','rate'=>78,'posted'=>1,'hot'=>false,
     'langs'=>['Dart'],'fws'=>['Flutter','Expo'],
     'site_name'=>'Midworks',
     'apply_url'=>'https://mid-works.com/projects?q=Flutter+Dart'],
 
    ['id'=>23,'title'=>'医療アプリ React Native 開発（iOS/Android 両対応）',
     'role'=>'モバイル','style'=>'hybrid','prefecture'=>'東京都','city'=>'新宿区','station'=>'新宿',
     'location'=>'新宿・週2出社','duration'=>'長期','rate'=>83,'posted'=>9,'hot'=>false,
     'langs'=>['TypeScript'],'fws'=>['React Native','Expo'],
     'site_name'=>'Findy Freelance',
     'apply_url'=>'https://freelance.findy-code.io/works?q=React+Native+モバイル'],
 
    /* インフラ / SRE */
    ['id'=>24,'title'=>'AWS クラウド移行 SRE（Terraform / Kubernetes）',
     'role'=>'インフラ／SRE','style'=>'remote','prefecture'=>'東京都','city'=>'','station'=>'',
     'location'=>'フルリモート','duration'=>'6ヶ月〜','rate'=>105,'posted'=>1,'hot'=>true,
     'langs'=>['Go','Python'],'fws'=>['Kubernetes','Terraform','AWS CDK'],
     'site_name'=>'レバテックフリーランス',
     'apply_url'=>'https://freelance.levtech.jp/project/search/?keyword=AWS+Terraform+Kubernetes+SRE'],
 
    ['id'=>25,'title'=>'MLOps 基盤構築（Kubernetes / Airflow / Python）',
     'role'=>'インフラ／SRE','style'=>'remote','prefecture'=>'東京都','city'=>'','station'=>'',
     'location'=>'フルリモート','duration'=>'4ヶ月','rate'=>98,'posted'=>4,'hot'=>false,
     'langs'=>['Python','Go'],'fws'=>['Kubernetes','Docker','Ansible'],
     'site_name'=>'Midworks',
     'apply_url'=>'https://mid-works.com/projects?q=MLOps+Kubernetes+Python'],
 
    ['id'=>26,'title'=>'大手通信 インフラ設計・DevOps 推進',
     'role'=>'インフラ／SRE','style'=>'onsite','prefecture'=>'東京都','city'=>'港区','station'=>'品川',
     'location'=>'品川・常駐','duration'=>'長期','rate'=>95,'posted'=>7,'hot'=>false,
     'langs'=>['Python'],'fws'=>['Ansible','Terraform','Docker'],
     'site_name'=>'ITプロパートナーズ',
     'apply_url'=>'https://itpropartners.com/job?free_word=DevOps+インフラ+Terraform'],
 
    /* データ / AI・ML */
    ['id'=>27,'title'=>'生成AI チャットボット開発（LangChain / FastAPI）',
     'role'=>'データ／AI・ML','style'=>'remote','prefecture'=>'東京都','city'=>'','station'=>'',
     'location'=>'フルリモート','duration'=>'3ヶ月','rate'=>110,'posted'=>0,'hot'=>true,
     'langs'=>['Python'],'fws'=>['FastAPI','LangChain','Docker'],
     'site_name'=>'Offers',
     'apply_url'=>'https://offers.jp/jobs?keyword=LangChain+生成AI+Python+FastAPI'],
 
    ['id'=>28,'title'=>'データ基盤構築（dbt / Snowflake / Python）',
     'role'=>'データ／AI・ML','style'=>'remote','prefecture'=>'東京都','city'=>'','station'=>'',
     'location'=>'フルリモート','duration'=>'6ヶ月','rate'=>92,'posted'=>3,'hot'=>false,
     'langs'=>['Python','R'],'fws'=>['dbt','Snowflake'],
     'site_name'=>'Findy Freelance',
     'apply_url'=>'https://freelance.findy-code.io/works?q=dbt+Snowflake+データエンジニア'],
 
    ['id'=>29,'title'=>'推薦システム ML エンジニア（Python / PyTorch）',
     'role'=>'データ／AI・ML','style'=>'remote','prefecture'=>'東京都','city'=>'','station'=>'',
     'location'=>'フルリモート','duration'=>'長期','rate'=>115,'posted'=>11,'hot'=>true,
     'langs'=>['Python'],'fws'=>['FastAPI','Docker'],
     'site_name'=>'Green',
     'apply_url'=>'https://www.green-japan.com/search?keyword=機械学習+MLエンジニア+Python'],
 
    ['id'=>30,'title'=>'Scala / Spark ビッグデータ処理最適化',
     'role'=>'データ／AI・ML','style'=>'hybrid','prefecture'=>'東京都','city'=>'品川区','station'=>'品川',
     'location'=>'東京・週1出社','duration'=>'3ヶ月','rate'=>100,'posted'=>6,'hot'=>false,
     'langs'=>['Scala','Python'],'fws'=>['Docker'],
     'site_name'=>'Indeed Japan',
     'apply_url'=>'https://jp.indeed.com/jobs?q=Scala+Spark+データエンジニア'],
 
    /* PM / PMO */
    ['id'=>31,'title'=>'大手金融 ITプロジェクト PM（アジャイル推進）',
     'role'=>'PM／PMO','style'=>'onsite','prefecture'=>'東京都','city'=>'千代田区','station'=>'大手町',
     'location'=>'大手町・常駐','duration'=>'長期','rate'=>95,'posted'=>5,'hot'=>false,
     'langs'=>[],'fws'=>[],
     'site_name'=>'Green',
     'apply_url'=>'https://www.green-japan.com/search?keyword=PM+PMO+ITプロジェクト'],
 
    ['id'=>32,'title'=>'スタートアップ プロダクトマネージャー（週3〜）',
     'role'=>'PM／PMO','style'=>'hybrid','prefecture'=>'東京都','city'=>'渋谷区','station'=>'渋谷',
     'location'=>'渋谷・週3出社','duration'=>'長期','rate'=>85,'posted'=>2,'hot'=>false,
     'langs'=>[],'fws'=>[],
     'site_name'=>'Wantedly',
     'apply_url'=>build_wantedly_url([], [], 'プロダクトマネージャー PM')],
]);
 
/* ═══════════════════════════════════════════════════════════
   入力値の取得・サニタイズ
═══════════════════════════════════════════════════════════ */
$q        = trim($_GET['q']         ?? '');
$role     = trim($_GET['role']      ?? '');
$style    = trim($_GET['style']     ?? '');
$langs_in = array_filter(array_map('trim', (array)($_GET['langs'] ?? [])));
$fws_in   = array_filter(array_map('trim', (array)($_GET['fws']   ?? [])));
$min_rate = max(0, (int)($_GET['min_rate'] ?? 0));
$pref     = trim($_GET['pref']      ?? '');   // LOCATION_GROUPS の value
$city     = trim($_GET['city']      ?? '');
$station  = trim($_GET['station']   ?? '');
$remote_ok = !empty($_GET['remote_ok']);
$sort_by  = in_array($_GET['sort'] ?? '', ['rate-desc','rate-asc','score','new'])
            ? $_GET['sort'] : 'new';
 
/* ═══════════════════════════════════════════════════════════
   フィルタリング
═══════════════════════════════════════════════════════════ */
function match_score(array $a, array $langs_in, array $fws_in): int {
    $sc = 0;
    foreach ($langs_in as $l) { if (in_array($l, $a['langs'], true)) $sc += 3; }
    foreach ($fws_in   as $f) { if (in_array($f, $a['fws'],   true)) $sc += 2; }
    if ($a['hot'])        $sc += 1;
    if ($a['posted'] <= 1) $sc += 1;
    return $sc;
}
 
/* 勤務地グループのフィルタ情報を解決 */
$pref_filter = resolve_pref_filter($pref);
$pref_match  = $pref_filter['match'];       // prefecture に含まれるべき文字列の配列
$city_filter = $pref_filter['city_filter']; // '23ku' | '23ku_outside' | 'osaka_city' | 'osaka_outside' | ''
 
$result = array_filter(ANKEN, function($a) use ($q, $role, $style, $langs_in, $fws_in, $min_rate, $pref, $pref_match, $city_filter, $city, $station, $remote_ok) {
    if ($q !== '') {
        $hay = implode(' ', array_merge([$a['title'],$a['role'],$a['location'],$a['prefecture'],$a['city'],$a['station'],$a['site_name']], $a['langs'], $a['fws']));
        if (mb_stripos($hay, $q) === false) return false;
    }
    if ($role !== '' && $a['role'] !== $role) return false;
    if ($remote_ok && $a['style'] === 'onsite') return false;
    if (!$remote_ok && $style !== '' && $a['style'] !== $style) return false;
    if (!empty($langs_in) && !array_intersect($langs_in, $a['langs'])) return false;
    if (!empty($fws_in)   && !array_intersect($fws_in,   $a['fws']))   return false;
    if ($min_rate > 0 && $a['rate'] < $min_rate) return false;
 
    /* 勤務地フィルタ（リモート案件は都道府県フィルタ不要） */
    if (!empty($pref_match) && $a['style'] !== 'remote') {
        if (!in_array($a['prefecture'], $pref_match, true)) return false;
        /* 23区フィルタ */
        if ($city_filter === '23ku') {
            if (!in_array($a['city'], TOKYO_23KU, true)) return false;
        } elseif ($city_filter === '23ku_outside') {
            if (in_array($a['city'], TOKYO_23KU, true) || $a['city'] === '') return false;
        }
        /* 大阪市内フィルタ */
        if ($city_filter === 'osaka_city') {
            if (!in_array($a['city'], OSAKA_CITY_KU, true)) return false;
        } elseif ($city_filter === 'osaka_outside') {
            if (in_array($a['city'], OSAKA_CITY_KU, true) || $a['city'] === '') return false;
        }
    }
 
    if ($city !== '' && $a['city'] !== '' && mb_stripos($a['city'], $city) === false) return false;
    if ($station !== '' && $a['station'] !== '' && mb_stripos($a['station'], $station) === false) return false;
    return true;
});
$result = array_values($result);
 
usort($result, function($a, $b) use ($sort_by, $langs_in, $fws_in) {
    return match ($sort_by) {
        'rate-desc' => $b['rate'] <=> $a['rate'],
        'rate-asc'  => $a['rate'] <=> $b['rate'],
        'score'     => match_score($b, $langs_in, $fws_in) <=> match_score($a, $langs_in, $fws_in),
        /* 投稿日が同一なら id 昇順（ANKEN 定義順を安定再現） */
        default     => ($a['posted'] <=> $b['posted']) ?: ($a['id'] <=> $b['id']),
    };
});
 
/* ═══════════════════════════════════════════════════════════
   外部サイト向けキーワード生成
═══════════════════════════════════════════════════════════ */
/* $q にすでに lang/fw が含まれている場合の重複を除去 */
$kw_parts = array_values(array_unique(array_filter(array_merge(
    $langs_in,
    $fws_in,
    $q !== '' ? array_filter(array_map('trim', preg_split('/[\s+]+/', $q))) : [],
    $role !== '' ? [$role] : [],
))));
$kw = urlencode(mb_substr(implode('+', $kw_parts), 0, 100));
 
/* lang_only サイト用（フレームワーク非対応サイト → 言語名のみ） */
$kw_lang_only_parts = array_values(array_unique(array_filter($langs_in)));
if ($kw_lang_only_parts === [] && $q !== '') {
    $kw_lang_only_parts = array_values(array_filter(array_map('trim', preg_split('/[\s+]+/', $q))));
}
$kw_lang_only = urlencode(mb_substr(implode('+', $kw_lang_only_parts), 0, 100));
 
/* lang_fw_pair サイト用（言語とFWをセット表記 "Python(FastAPI)" にするサイト向け） */
$kw_lang_fw_pair_parts = [];
$lang_fw_map = LANG_FW_MAP;
foreach ($langs_in as $lang) {
    // この言語に紐づくFWが選択されているか調べる
    $related = $lang_fw_map[$lang] ?? [];
    $paired_fws = array_values(array_intersect($fws_in, $related));
    if (!empty($paired_fws)) {
        // 言語(FW1 FW2) 形式
        $kw_lang_fw_pair_parts[] = $lang . '(' . implode(' ', $paired_fws) . ')';
    } else {
        $kw_lang_fw_pair_parts[] = $lang;
    }
}
// 言語未選択だが FW が選択されている場合：そのまま追加
$unpaired_fws = array_diff($fws_in, array_merge(...array_values(array_map(
    fn($l) => array_intersect($fws_in, $lang_fw_map[$l] ?? []),
    $langs_in ?: ['']
))));
foreach ($unpaired_fws as $fw) {
    $kw_lang_fw_pair_parts[] = $fw;
}
// langs も fws も空なら $q をそのまま使う
if ($kw_lang_fw_pair_parts === [] && $q !== '') {
    $kw_lang_fw_pair_parts = array_filter(array_map('trim', preg_split('/[\s+]+/', $q)));
}
$kw_lang_fw_pair = urlencode(mb_substr(implode('+', $kw_lang_fw_pair_parts), 0, 100));
 
/* クラウドテック用スキルIDマップ（言語名 → /job_offers/s/{ID}）
   IDはGoogle検索で確認済みのもののみ収録。
   未収録言語・言語未選択時は /job_offers/o/1（全件一覧）にフォールバック。
   ※ tech.crowdworks.jp は ?q= フリーワード検索パラメータ非対応 */
define('CROWDTECH_SKILL_ID', [
    'JavaScript'  => 17,
    'TypeScript'  => 15,
    'Python'      => 4,
    'PHP'         => 6,
    'Ruby'        => 3,
    'Java'        => 2,
    'Go'          => 13,
    'Swift'       => 10,
    'Kotlin'      => 11,
    'Scala'       => 20,
    'C#'          => 8,
]);
 
/**
 * クラウドテック向けURLを生成する。
 * - 言語が1つ確定 → /job_offers/s/{ID}（スキルIDページ）
 * - 言語が複数 or スキルIDなし → /job_offers/o/1?q=言語名
 */
function build_crowdtech_url(array $langs_in, array $fws_in, string $q): string {
    $base   = 'https://tech.crowdworks.jp/job_offers/';
    $id_map = CROWDTECH_SKILL_ID;
 
    // 選択された言語のうちIDが既知のものを順番に探す
    foreach ($langs_in as $lang) {
        if (isset($id_map[$lang])) {
            return $base . 's/' . $id_map[$lang];
        }
    }
 
    // $q からトークンを取り出し、IDが既知の言語名が含まれていれば使う
    if ($q !== '') {
        $tokens = array_filter(array_map('trim', preg_split('/[\\s+]+/', $q)));
        foreach ($tokens as $token) {
            if (isset($id_map[$token])) {
                return $base . 's/' . $id_map[$token];
            }
        }
    }
 
    // スキルIDが特定できない場合 -> 言語/FW名をキーワードとして渡す
    // (旧実装は?q=を付けずに一覧へ丸投げしていたバグ。コメントの意図通りに修正)
    $kw_source = !empty($langs_in) ? $langs_in[0] : (!empty($fws_in) ? $fws_in[0] : '');
    if ($kw_source !== '') {
        return $base . 'o/1?q=' . urlencode($kw_source);
    }
    return $base . 'o/1';
}
 
/**
 * Findy Freelance 向けURLを生成する。
 *
 * 有効な検索URLは /works/languages/{言語スラッグ} のパス形式のみ。
 * ?q= パラメータはNext.js SPAのため無視される。
 *
 * - 言語選択あり → /works/languages/{スラッグ}（最初にマッチした言語）
 * - 言語なし → /works（全案件一覧）
 */
/**
 * Midworks 向けURLを生成する。
 *
 * 有効なURLは /projects/skills/{ID} のパス形式のみ。
 * ?q= パラメータは完全に無視される。
 *
 * - 言語選択あり → /projects/skills/{ID}（最初にマッチした言語）
 * - 言語なし → /projects（全件一覧）
 */
/**
 * geechs job 向けURLを生成する。
 *
 * 有効な検索URLは /project/{言語スラッグ} のパス形式のみ。
 * FWでの絞り込みはできないため、言語を優先して渡す。
 *
 * - 言語選択あり → /project/{スラッグ}（最初にマッチした言語）
 * - FWのみ → FWに対応する言語スラッグにフォールバック
 * - それ以外 → /project（全件一覧）
 */
/**
 * レバテックフリーランス向けURLを生成する。
 *
 * ?keyword= に複数スキルを + で渡すのは無効（完全に無視される）。
 * 有効な検索URLは /project/{skill|fw}-{ID}/ のパス形式のみ（1スキル1ページ）。
 *
 * 優先順位: FW（最も具体的） > 言語 > $q > /project/search/（全件）
 * 複数FW/言語が選ばれた場合は最初にマッチした1つのみ使用。
 */
/**
 * フリーランススタート向けURLを生成する。
 *
 * ?keyword= パラメータにスペース区切りでスキル名を渡す形式。
 * 複数スキルをスペースで区切ると AND 検索になる。
 *
 * 優先順位: FW+言語のペア > FWのみ > 言語のみ > $q > /jobs（全件）
 */
function build_fls_url(array $langs_in, array $fws_in, string $q): string {
    $base = 'https://freelance-start.com/jobs?keyword=';
 
    // FW+言語ペアが最も精度が高い（例：FastAPI Python）
    if (!empty($fws_in) && !empty($langs_in)) {
        return $base . urlencode($fws_in[0] . ' ' . $langs_in[0]);
    }
 
    // FWのみ
    if (!empty($fws_in)) {
        return $base . urlencode($fws_in[0]);
    }
 
    // 言語のみ（複数あれば最初の2つをスペース結合）
    if (!empty($langs_in)) {
        $kw = implode(' ', array_slice($langs_in, 0, 2));
        return $base . urlencode($kw);
    }
 
    // $q からトークン抽出
    if ($q !== '') {
        $tokens = array_values(array_filter(array_map('trim', preg_split('/[\s+]+/', $q))));
        if (!empty($tokens)) {
            return $base . urlencode(implode(' ', array_slice($tokens, 0, 2)));
        }
    }
 
    return 'https://freelance-start.com/jobs';
}
 
function build_levtech_url(array $langs_in, array $fws_in, string $q): string {
    $base = 'https://freelance.levtech.jp/project/';
 
    // フレームワーク → /project/fw-{ID}/ （最も精度が高い）
    // freelance.levtech.jp/project/fw-{N}/ で実在確認済み
    $fw_id_map = [
        'React'           => 'fw-49',
        'Next.js'         => 'fw-58',
        'Vue.js'          => 'fw-52',
        'Nuxt.js'         => 'fw-57',
        'Angular'         => 'fw-49',   // Reactと共通カテゴリに近い
        'Node.js'         => 'fw-1',
        'Ruby on Rails'   => 'fw-4',
        'Django'          => 'fw-6',
        'Laravel'         => 'fw-44',
        'Spring Boot'     => 'fw-4',    // Rails寄りの近似
        'Flutter'         => 'fw-49',
        'React Native'    => 'fw-49',
    ];
 
    // 言語 → /project/skill-{ID}/
    // freelance.levtech.jp/project/search/ の公式スキルリストで確認済み
    $lang_id_map = [
        'JavaScript'  => 'skill-4',
        'PHP'         => 'skill-5',
        'Python'      => 'skill-7',
        'Ruby'        => 'skill-8',
        'Go'          => 'skill-10',
        'C#'          => 'skill-11',
        'Java'        => 'skill-3',
        'TypeScript'  => 'skill-58',
        'Kotlin'      => 'skill-57',
        'Swift'       => 'skill-49',
    ];
 
    // FW優先（最も案件が絞られて精度が高い）
    foreach ($fws_in as $fw) {
        if (isset($fw_id_map[$fw])) {
            return $base . $fw_id_map[$fw] . '/';
        }
    }
 
    // 言語フォールバック
    foreach ($langs_in as $lang) {
        if (isset($lang_id_map[$lang])) {
            return $base . $lang_id_map[$lang] . '/';
        }
    }
 
    // $q からトークンを探す
    if ($q !== '') {
        $tokens = array_filter(array_map('trim', preg_split('/[\s+]+/', $q)));
        foreach ($tokens as $token) {
            if (isset($fw_id_map[$token])) {
                return $base . $fw_id_map[$token] . '/';
            }
            if (isset($lang_id_map[$token])) {
                return $base . $lang_id_map[$token] . '/';
            }
        }
    }
 
    // 何もマッチしない → 全件一覧
    return 'https://freelance.levtech.jp/project/search/';
}
 
function build_geechs_url(array $langs_in, array $fws_in, string $q): string {
    $base = 'https://geechs-job.com/project/';
 
    // 言語名 → geechs スラッグ（/project/{slug} で動作確認済み）
    $slug_map = [
        'JavaScript'  => 'javascript',
        'TypeScript'  => 'typescript',
        'Python'      => 'python',
        'Go'          => 'go',
        'PHP'         => 'php',
        'Ruby'        => 'ruby',
        'Java'        => 'java',
        'Swift'       => 'ios',   // /project/ios として登録
        'Kotlin'      => 'java',  // Kotlin案件はJava扱い
        'Scala'       => 'java',
        'C#'          => 'dotnet',
    ];
 
    // FW → 対応言語スラッグ
    $fw_slug_map = [
        'FastAPI'         => 'python',
        'Django'          => 'python',
        'Flask'           => 'python',
        'Laravel'         => 'php',
        'CakePHP'         => 'php',
        'Ruby on Rails'   => 'ruby',
        'Spring Boot'     => 'java',
        'React'           => 'javascript',
        'Vue.js'          => 'javascript',
        'Next.js'         => 'typescript',
        'Angular'         => 'typescript',
    ];
 
    foreach ($langs_in as $lang) {
        if (isset($slug_map[$lang])) {
            return $base . $slug_map[$lang];
        }
    }
 
    foreach ($fws_in as $fw) {
        if (isset($fw_slug_map[$fw])) {
            return $base . $fw_slug_map[$fw];
        }
    }
 
    if ($q !== '') {
        $tokens = array_filter(array_map('trim', preg_split('/[\s+]+/', $q)));
        foreach ($tokens as $token) {
            if (isset($slug_map[$token])) {
                return $base . $slug_map[$token];
            }
        }
    }
 
    return 'https://geechs-job.com/project';
}
 
/**
 * ITあんけん（itanken.com）向けURL。
 * free_word には言語と紐づくFWを全角スペース（U+3000）で連結する形式が有効（例: Python　FastAPI）。
 */
function build_itanken_url(array $langs_in, array $fws_in, string $q, string $role): string {
    $base   = 'https://itanken.com/search?free_word=';
    $idsp   = '　'; // U+3000
    $lfmap  = LANG_FW_MAP;
    $chunks = [];
 
    foreach ($langs_in as $lang) {
        $related    = $lfmap[$lang] ?? [];
        $paired_fws = array_values(array_intersect($fws_in, $related));
        if (!empty($paired_fws)) {
            $chunks[] = $lang . $idsp . implode($idsp, $paired_fws);
        } else {
            $chunks[] = $lang;
        }
    }
    $unpaired = array_diff($fws_in, array_merge(...array_values(array_map(
        fn($l) => array_intersect($fws_in, $lfmap[$l] ?? []),
        $langs_in ?: ['']
    ))));
    foreach ($unpaired as $fw) {
        $chunks[] = $fw;
    }
 
    if ($chunks === [] && $q !== '') {
        $chunks = array_filter(array_map('trim', preg_split('/[\s+]+/', $q)));
    }
    if ($role !== '') {
        $chunks[] = $role;
    }
    if ($chunks === []) {
        return 'https://itanken.com/search';
    }
 
    $free_word = mb_substr(implode($idsp, $chunks), 0, 120);
    return $base . rawurlencode($free_word);
}
 
function build_midworks_url(array $langs_in, array $fws_in, string $q): string {
    // 言語名 → Midworks /projects/skills/{ID} （実際に /projects/skills/N をフェッチして確認済み）
    $id_map = [
        'PHP'        => 1,
        'Java'       => 3,
        'Python'     => 4,
        'Scala'      => 5,
        'Go'         => 6,
        'Ruby'       => 7,
        'JavaScript' => 9,
        'Swift'      => 15,
        'TypeScript' => 221,
        'Kotlin'     => 39,
        'React'      => 78,
    ];
 
    foreach ($langs_in as $lang) {
        if (isset($id_map[$lang])) {
            return 'https://mid-works.com/projects/skills/' . $id_map[$lang];
        }
    }
 
    // FW選択時はFWに対応する言語を似まわしいび優先マップ
    $fw_lang_map = [
        'FastAPI'       => 4,  // Python
        'Django'        => 4,
        'Flask'         => 4,
        'Laravel'       => 1,  // PHP
        'CakePHP'       => 1,
        'Ruby on Rails' => 7,
        'Spring Boot'   => 3,  // Java
        'React'         => 78, // React
        'Vue.js'        => 9,  // JavaScript
        'Next.js'       => 221,// TypeScript
    ];
 
    foreach ($fws_in as $fw) {
        if (isset($fw_lang_map[$fw])) {
            return 'https://mid-works.com/projects/skills/' . $fw_lang_map[$fw];
        }
    }
 
    // $q のトークンでIDを探す
    if ($q !== '') {
        $tokens = array_filter(array_map('trim', preg_split('/[\s+]+/', $q)));
        foreach ($tokens as $token) {
            if (isset($id_map[$token])) {
                return 'https://mid-works.com/projects/skills/' . $id_map[$token];
            }
        }
    }
 
    // IDが特定できない場合 → 全件一覧
    return 'https://mid-works.com/projects';
}
 
/**
 * リクルートエージェント（正社員）向けURL。
 * フレームワーク非対応のため、言語キーワードのみ /job_search/kw/{Keyword}/ に渡す。
 */
function build_r_agent_url(array $langs_in, string $q): string {
    $base = 'https://www.r-agent.com/job_search/kw/';
 
    $kw = '';
    if (!empty($langs_in)) {
        $kw = $langs_in[0];
    } elseif ($q !== '') {
        $tokens = array_values(array_filter(array_map('trim', preg_split('/[\s+]+/', $q))));
        if (!empty($tokens)) $kw = $tokens[0];
    }
 
    if ($kw === '') {
        return 'https://www.r-agent.com/job_search/';
    }
    return $base . rawurlencode($kw) . '/';
}
 
/**
 * Daijob 検索URL生成。
 * - $en=false: https://www.daijob.com/jobs/search_result?kw=...        (日本語版)
 * - $en=true : https://www.daijob.com/en/jobs/search_result?kw=...    (英語版)
 * - 言語チップ・FWチップ・フリーワードを半角スペース区切りで結合（最大4語）
 */
function build_daijob_url(array $langs_in, array $fws_in, string $q, bool $en = false): string {
    $base = $en
        ? 'https://www.daijob.com/en/jobs/search_result'
        : 'https://www.daijob.com/jobs/search_result';
 
    $parts = array_values(array_unique(array_filter(array_merge(
        $langs_in, $fws_in,
        $q !== '' ? array_filter(array_map('trim', preg_split('/[\s+]+/', $q))) : [],
    ))));
    $parts = array_slice($parts, 0, 4);
 
    if ($parts === []) {
        return $base;
    }
    $kw = implode(' ', $parts);
    return $base . '?' . http_build_query(['kw' => $kw], '', '&', PHP_QUERY_RFC1738);
}
 
function build_findy_fl_url(array $langs_in, array $fws_in, string $q): string {
    $base = 'https://freelance.findy-code.io/works/languages/';
 
    // 言語名 → URLスラッグ（小文字・ハイフン形式）
    $slug_map = [
        'JavaScript'  => 'javascript',
        'TypeScript'  => 'typescript',
        'Python'      => 'python',
        'Go'          => 'go',
        'PHP'         => 'php',
        'Ruby'        => 'ruby',
        'Java'        => 'java',
        'Kotlin'      => 'kotlin',
        'Swift'       => 'swift',
        'Rust'        => 'rust',
        'C#'          => 'csharp',
        'Scala'       => 'scala',
        'Dart'        => 'dart',
        'Elixir'      => 'elixir',
        'C++'         => 'cpp',
    ];
 
    foreach ($langs_in as $lang) {
        if (isset($slug_map[$lang])) {
            return $base . $slug_map[$lang];
        }
    }
 
    // $q のトークンで言語スラッグを探す
    if ($q !== '') {
        $tokens = array_filter(array_map('trim', preg_split('/[\s+]+/', $q)));
        foreach ($tokens as $token) {
            if (isset($slug_map[$token])) {
                return $base . $slug_map[$token];
            }
        }
    }
 
    // スラッグが特定できない場合 → 全案件一覧
    return 'https://freelance.findy-code.io/works';
}
 
/**
 * Offers（副業・複業）向けURLを生成する。
 *
 * 有効な検索URLは /jobs/skills/{スキルID} のパス形式のみ。
 * ?keyword= パラメータは無視される。
 *
 * - 言語/FW選択あり → /jobs/skills/{ID}（最初にマッチしたスキル）
 * - IDなし → /jobs（全件一覧）
 */
function build_findy_career_url(array $langs_in, array $fws_in, string $q): string {
    $base = 'https://findy-code.io/recommends/';
 
    // 言語名 → /recommends/{スラッグ} パス形式
    // findy-code.io/recommends/{python|typescript|javascript|go|php|ruby|java|kotlin|swift|rust|scala|...}
    $slug_map = [
        'JavaScript'  => 'javascript',
        'TypeScript'  => 'typescript',
        'Python'      => 'python',
        'Go'          => 'go',
        'PHP'         => 'php',
        'Ruby'        => 'ruby',
        'Java'        => 'java',
        'Kotlin'      => 'kotlin',
        'Swift'       => 'swift',
        'Rust'        => 'rust',
        'C#'          => 'csharp',
        'Scala'       => 'scala',
        'Dart'        => 'dart',
        'Elixir'      => 'elixir',
        'C++'         => 'cpp',
    ];
 
    foreach ($langs_in as $lang) {
        if (isset($slug_map[$lang])) {
            return $base . $slug_map[$lang];
        }
    }
 
    // $q のトークンでスラッグを探す
    if ($q !== '') {
        $tokens = array_filter(array_map('trim', preg_split('/[\s+]+/', $q)));
        foreach ($tokens as $token) {
            if (isset($slug_map[$token])) {
                return $base . $slug_map[$token];
            }
        }
    }
 
    // スラッグが特定できない場合 → 求人・企業検索トップ
    return 'https://findy-code.io/recommends';
}
 
function build_offers_url(array $langs_in, array $fws_in, string $q): string {
    $base = 'https://offers.jp/jobs/skills/';
 
    // 言語名 → OffersスキルID（/jobs/skills/{ID} で確認済み）
    $id_map = [
        'JavaScript'  => 216,
        'TypeScript'  => 222,
        'Python'      => 228,
        'Go'          => 240,
        'PHP'         => 219,
        'Ruby'        => 218,
        'Java'        => 232,
        'Kotlin'      => 237,
        'Swift'       => 233,
        'React'       => 220,
        'Vue.js'      => 221,
        'Next.js'     => 224,
        'Dart'        => 238,
        'AWS'         => 243,
        'Docker'      => 247,
    ];
 
    foreach ($langs_in as $lang) {
        if (isset($id_map[$lang])) {
            return $base . $id_map[$lang];
        }
    }
 
    foreach ($fws_in as $fw) {
        if (isset($id_map[$fw])) {
            return $base . $id_map[$fw];
        }
    }
 
    // $q のトークンでIDを探す
    if ($q !== '') {
        $tokens = array_filter(array_map('trim', preg_split('/[\s+]+/', $q)));
        foreach ($tokens as $token) {
            if (isset($id_map[$token])) {
                return $base . $id_map[$token];
            }
        }
    }
 
    // IDが特定できない場合 → 全件一覧
    return 'https://offers.jp/jobs';
}
 
/**
 * Green（転職サイト）向けURLを生成する。
 *
 * Greenの検索URLは /search/skill/{スキル名} のパス形式のみ有効。
 * ?keyword= などのクエリパラメータは無視される。
 *
 * - 言語選択あり → /search/skill/{最初の言語名}（言語優先）
 * - 言語なし・FW選択あり → /search/skill/{最初のFW名}
 * - $q 入力あり → $q の最初のトークンをスキル名として使用
 * - 何もなし → /search（全件検索）
 */
function build_green_url(array $langs_in, array $fws_in, string $q): string {
    $base = 'https://www.green-japan.com/search/skill/';
 
    /* Greenの /search/skill/ に登録されているスキル名のホワイトリスト
     * 未登録スキル名は 404 になるため /search (全件) にフォールバックする */
    $registered_skills = [
        // プログラミング言語 (green-japan.com/search/list_skill で確認済み)
        'JavaScript','TypeScript','Python','PHP','Ruby','Java','Go','Swift','Kotlin',
        'Scala','Rust','C#','C++','C','Dart','Elixir','Haskell','COBOL','VBA','R',
        // フレームワーク類 (green-japan.com/search/list_skill で確認済み)
        'React','Next.js','Vue.js','Angular','Django','Ruby on Rails',
        'Spring Boot','Laravel','AWS','Docker','Kubernetes','Android','iOS',
        'PostgreSQL','MySQL','MongoDB','Redis','GraphQL',
        'Flutter','React Native',
    ];
    $registered_set = array_flip($registered_skills);
 
    // 言語が選択されていればホワイトリスト内の最初の言語を使用
    foreach ($langs_in as $lang) {
        if (isset($registered_set[$lang])) {
            return $base . urlencode($lang);
        }
    }
 
    // FWが選択されていればホワイトリスト内の最初の FW を使用
    foreach ($fws_in as $fw) {
        if (isset($registered_set[$fw])) {
            return $base . urlencode($fw);
        }
    }
 
    // $q のトークンをホワイトリストで確認
    if ($q !== '') {
        $tokens = array_values(array_filter(array_map('trim', preg_split('/[\s+]+/', $q))));
        foreach ($tokens as $token) {
            if (isset($registered_set[$token])) {
                return $base . urlencode($token);
            }
        }
        // ホワイトリスト外だがトークンがある場合 → そのまま使用（全件ヒットの可能性もある）
        if (!empty($tokens)) {
            return $base . urlencode($tokens[0]);
        }
    }
 
    // 条件なし → 全件検索トップ
    return 'https://www.green-japan.com/search';
}
 
/* ═══════════════════════════════════════════════════════════
   ▼▼▼ API 設定（ここにキーを入力してください）▼▼▼
═══════════════════════════════════════════════════════════ */
 
// Google Programmable Search Engine
// 取得方法: https://programmablesearchengine.google.com/
define('GOOGLE_CSE_KEY', getenv('GOOGLE_CSE_KEY') ?: '');   // APIキー
define('GOOGLE_CSE_CX',  getenv('GOOGLE_CSE_CX')  ?: '');   // 検索エンジンID
 
// OpenAI API
// 取得方法: https://platform.openai.com/api-keys
if (!defined('OPENAI_API_KEY')) { define('OPENAI_API_KEY', getenv('OPENAI_API_KEY') ?: ''); }
// GitHub API（任意）— TOP50同期のレート制限緩和。未設定でも Stack Overflow / DB-Engines は動作
if (!defined('GITHUB_TOKEN')) { define('GITHUB_TOKEN', getenv('GITHUB_TOKEN') ?: ''); }
 
// キャッシュ有効期限（秒）
define('CACHE_TTL_SEARCH', 86400);   // 毎日更新（旧:7日）
define('CACHE_TTL_TRENDS', 86400);   // 毎日更新（旧:3日）
 
// キャッシュディレクトリ（index.php と同じ aruaru/ 内に自動作成）
/* ═══════════════════════════════════════════════════════════
   キャッシュ管理（1ファイル完結・サブフォルダ不要）
   書き込み先を自動探索:
     1. index.php と同じフォルダに data/ を作成できれば使う
     2. 作れない場合はシステム一時ディレクトリを使う
     3. どちらも使えない場合はキャッシュなしで動作（APIは毎回呼ぶ）
═══════════════════════════════════════════════════════════ */
function _resolve_cache_dir(): string {
    $local = __DIR__ . '/data';
    if (is_dir($local) && is_writable($local)) return $local;
    if (@mkdir($local, 0755, true) && is_writable($local)) return $local;
    $tmp = rtrim(sys_get_temp_dir(), '/\\ ') . '/anken_cache_' . substr(md5(__FILE__), 0, 8);
    if (is_dir($tmp) || @mkdir($tmp, 0755, true)) return $tmp;
    return '';
}
$_CACHE_DIR = _resolve_cache_dir();
 
function cache_path(string $key): string {
    global $_CACHE_DIR;
    if ($_CACHE_DIR === '') return '';
    return $_CACHE_DIR . '/' . preg_replace('/[^a-z0-9_\-]/i', '_', $key) . '.json';
}
function cache_get(string $key, int $ttl): ?array {
    $f = cache_path($key);
    if ($f === '' || !file_exists($f)) return null;
    if (time() - filemtime($f) > $ttl) return null;
    $d = @json_decode(@file_get_contents($f), true);
    return is_array($d) ? $d : null;
}
function cache_set(string $key, array $data): void {
    $f = cache_path($key);
    if ($f === '') return;
    @file_put_contents($f, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}
 
/* ═══════════════════════════════════════════════════════════
   Google Custom Search API 呼び出し
   - APIキー未設定時は空配列を返す
   - 複数ページ（GOOGLE_CSE_MAX_RESULTS まで）＋フォールバック検索でヒット率を上げる
   - lr=lang_ja は除外（英語サイトばかりになり 0 件になりやすいため）
   - 結果はキャッシュに保存（TTL: 7日）
   - クロールなし：ユーザーがページを開いた時のみ実行
═══════════════════════════════════════════════════════════ */
define('GOOGLE_CSE_MAX_RESULTS', 10);
 
/**
 * Google CSE が返す古い誤ドメインを公式URLへ差し替える。
 * midworks.com は転売ドメインになり HugeDomains の出品ページや別サイトへ誘導されることがある。
 */
function normalize_google_cse_item(array $it): array {
    $url = $it['url'] ?? '';
    if ($url === '') {
        return $it;
    }
    $url = preg_replace('#^https?://(www\.)?midworks\.com#i', 'https://mid-works.com', $url);
    $host = strtolower((string) parse_url($url, PHP_URL_HOST));
    if ($host !== '' && strpos($host, 'hugedomains.com') !== false) {
        $qstr = (string) parse_url($url, PHP_URL_QUERY);
        parse_str($qstr, $qp);
        $d = $qp['d'] ?? '';
        if (is_string($d) && preg_match('/(^|\.)midworks\.com$/i', $d)) {
            $url = 'https://mid-works.com/projects';
        }
    }
    $it['url'] = $url;
    $it['domain'] = parse_url($url, PHP_URL_HOST) ?: '';
    return $it;
}
 
/** CSE 1ページ（num は 1〜10） */
function google_search_fetch_page(string $query, int $start1Based, int $num): array {
    if (GOOGLE_CSE_KEY === '' || GOOGLE_CSE_CX === '') return [];
    $cse_pause = cache_get('google_cse_quota_pause', 86400);
    if (is_array($cse_pause) && (($cse_pause['until'] ?? 0) > time())) {
        return [];
    }
    $num = max(1, min(10, $num));
    $start1Based = max(1, min(91, $start1Based));
 
    $url = 'https://www.googleapis.com/customsearch/v1?' . http_build_query([
        'key'   => GOOGLE_CSE_KEY,
        'cx'    => GOOGLE_CSE_CX,
        'q'     => $query,
        'num'   => $num,
        'start' => $start1Based,
        'gl'    => 'jp',
    ]);
    $ctx = stream_context_create(['http' => ['timeout' => 8, 'ignore_errors' => true]]);
    $raw = @file_get_contents($url, false, $ctx);
    if (!$raw) return [];
    $data = json_decode($raw, true);
    if (!empty($data['error']) && is_array($data['error'])) {
        $errs = $data['error']['errors'] ?? [];
        foreach ($errs as $er) {
            if (!is_array($er)) {
                continue;
            }
            $reason = strtolower((string)($er['reason'] ?? ''));
            if ($reason === 'dailylimitexceeded' || $reason === 'ratelimitexceeded' || $reason === 'quotaexceeded' || $reason === 'usagelimits') {
                cache_set('google_cse_quota_pause', ['until' => time() + 21600]);
                break;
            }
        }
        return [];
    }
    if (empty($data['items'])) return [];
 
    return array_map(function ($it) {
        $row = [
            'title'   => $it['title']   ?? '',
            'snippet' => $it['snippet'] ?? '',
            'url'     => $it['link']    ?? '',
            'domain'  => parse_url($it['link'] ?? '', PHP_URL_HOST) ?: '',
        ];
        return normalize_google_cse_item($row);
    }, $data['items']);
}
 
/** 1つのクエリで複数ページを取得してマージ（URL重複除去） */
function google_search_collect_query(string $query, int $target_total, array &$seen, array &$merged): void {
    $target_total = max(1, min(GOOGLE_CSE_MAX_RESULTS, $target_total));
    $max_start = 1 + 10 * max(0, (int) ceil($target_total / 10) - 1);
    $max_start = min(91, $max_start);
    $start = 1;
    while (count($merged) < $target_total && $start <= $max_start) {
        $need = min(10, $target_total - count($merged));
        $page = google_search_fetch_page($query, $start, $need);
        if ($page === []) break;
        foreach ($page as $it) {
            $u = $it['url'] ?? '';
            if ($u === '' || isset($seen[$u])) continue;
            $seen[$u] = true;
            $merged[] = $it;
            if (count($merged) >= $target_total) return;
        }
        if (count($page) < $need) break;
        $start += 10;
    }
}
 
/**
 * @param string $primary_query メイン検索語句
 * @param array  $fallback_queries 0件のとき試す別クエリ（短い語など）
 * @param int    $target_total     最大件数（既定 GOOGLE_CSE_MAX_RESULTS・API上限30）
 */
function google_search(string $primary_query, array $fallback_queries = [], int $target_total = GOOGLE_CSE_MAX_RESULTS): array {
    if (GOOGLE_CSE_KEY === '' || GOOGLE_CSE_CX === '') return [];
 
    $fb = array_values(array_unique(array_filter($fallback_queries, fn($x) => is_string($x) && $x !== '')));
    $ckey = 'gse_v5_' . md5($primary_query . "\0" . implode("\0", $fb));
    $cached = cache_get($ckey, CACHE_TTL_SEARCH);
    if ($cached !== null) return $cached;
 
    $target_total = max(1, min(GOOGLE_CSE_MAX_RESULTS, $target_total));
    $seen = [];
    $merged = [];
 
    google_search_collect_query($primary_query, $target_total, $seen, $merged);
 
    /* メインが0件／不足のとき、フォールバックを順にマージ（1つで打ち切らず埋める） */
    if (count($merged) < $target_total && $fb !== []) {
        foreach ($fb as $q) {
            if (count($merged) >= $target_total) {
                break;
            }
            google_search_collect_query($q, $target_total, $seen, $merged);
        }
    }
 
    if ($merged !== []) {
        cache_set($ckey, $merged);
        $stock = cache_get('search_stock', 86400 * 365) ?? [];
        $stock[] = ['q' => $primary_query, 'ts' => date('Y-m-d'), 'items' => $merged];
        if (count($stock) > 200) $stock = array_slice($stock, -200);
        cache_set('search_stock', $stock);
    }
 
    return $merged;
}
 
/* ═══════════════════════════════════════════════════════════
   AIトレンド：無料フォールバック用 TOP50（API未使用・レート制限時）
═══════════════════════════════════════════════════════════ */
function ai_trend_fallback_langs_top50(): array {
    // ベースライン80件（aruaru_tech_baseline_data）から言語名を取得
    $names = [];
    $base = aruaru_tech_baseline_data();
    foreach (($base['languages'] ?? []) as $row) {
        $n = trim((string)($row['name'] ?? ''));
        if ($n !== '') { $names[] = $n; }
    }
    // 補完候補（ベースラインに無いものを追加）
    $extra = [
        'Shell', 'HTML/CSS', 'Delphi', 'VB.NET', 'Mojo', 'GraphQL', 'WebAssembly', 'HCL', 'MDX', 'SQL',
    ];
    return array_slice(array_values(array_unique(array_merge($names, LANGS, $extra))), 0, 80);
}
 
function ai_trend_fallback_fws_top50(): array {
    // ベースライン80件（aruaru_tech_baseline_data）からFW名を取得
    $names = [];
    $base = aruaru_tech_baseline_data();
    foreach (($base['frameworks'] ?? []) as $row) {
        $n = trim((string)($row['name'] ?? ''));
        if ($n !== '') { $names[] = $n; }
    }
    $flat = [];
    foreach (FRAMEWORKS as $group) {
        foreach ($group as $name) {
            $flat[] = $name;
        }
    }
    return array_slice(array_values(array_unique(array_merge($names, $flat))), 0, 80);
}
 
/** AI応答またはマスタをマージし、重複なく最大80件 */
function ai_trend_normalize_top50(?array $items, array $fallback_pool): array {
    $items = $items ?? [];
    $clean = [];
    foreach ($items as $s) {
        if (!is_string($s)) {
            continue;
        }
        $s = trim($s);
        if ($s === '') {
            continue;
        }
        $clean[] = $s;
    }
    $seen = [];
    $out = [];
    foreach ($clean as $s) {
        if (isset($seen[$s])) {
            continue;
        }
        $seen[$s] = true;
        $out[] = $s;
        if (count($out) >= 80) {
            return $out;
        }
    }
    foreach ($fallback_pool as $s) {
        if (count($out) >= 80) {
            break;
        }
        if (isset($seen[$s])) {
            continue;
        }
        $seen[$s] = true;
        $out[] = $s;
    }
 
    return $out;
}
 
/* ═══════════════════════════════════════════════════════════
   OpenAI GPT-4o-mini によるトレンド分析
   - APIキー未設定時はデフォルトデータを返す
   - 言語・FW それぞれ TOP80（無料枠フォールバック含む）
   - 結果はキャッシュに保存（TTL: 3日）
   - 蓄積された検索ワードを元に人気言語・FWを分析
═══════════════════════════════════════════════════════════ */
function ai_trend_analysis(): array {
    $fb_langs = ai_trend_fallback_langs_top50();
    $fb_fws = ai_trend_fallback_fws_top50();
    $default = [
        'langs'       => $fb_langs,
        'fws_top'     => $fb_fws,
        'summary'     => '',
        'updated_at'  => '',
        'model'       => 'default',
    ];
    if (OPENAI_API_KEY === '') return $default;
 
    /* API キーのレート制限・クォータ超過中は無料範囲（固定データ）のみ（過剰リクエスト回避） */
    $quota_pause = cache_get('openai_quota_pause', 86400);
    if (is_array($quota_pause) && (($quota_pause['until'] ?? 0) > time())) {
        return $default;
    }
 
    $cached = cache_get('ai_trends', CACHE_TTL_TRENDS);
    if ($cached !== null) {
        $cached['langs'] = ai_trend_normalize_top50($cached['langs'] ?? null, $fb_langs);
        $cached['fws_top'] = ai_trend_normalize_top50($cached['fws_top'] ?? null, $fb_fws);
 
        return $cached;
    }
 
    /* 蓄積された検索ワードを収集 */
    $stock = cache_get('search_stock', 86400 * 365) ?? [];
    $all_q = array_column($stock, 'q');
    $recent_q = implode(', ', array_slice(array_unique($all_q), -50));
 
    /* 現在の固定データも参考情報として渡す */
    $current_langs = implode(', ', LANGS);
 
    $prompt = <<<EOT
あなたはITエンジニア採用トレンドの専門アナリストです。
以下の情報をもとに、2026年現在の日本のITエンジニア案件・求人市場で
需要が高いプログラミング言語とフレームワークのランキングを分析してください。
 
【最近の検索ワード（ユーザーが実際に検索したもの）】
{$recent_q}
 
【現在登録中の言語リスト】
{$current_langs}
 
以下のJSON形式で回答してください（説明文なし、JSONのみ）。
 langs_ranked と fws_top はそれぞれちょうど80要素（需要が高い順）。各名称は短く。
{
  "langs_ranked": ["1位の言語名", "2位", "…" ],
  "fws_top": ["1位のFW/ツール名", "2位", "…" ],
  "hot_langs": ["急上昇中の言語1", "急上昇中の言語2", "急上昇中の言語3"],
  "hot_fws": ["急上昇中のFW1", "急上昇中のFW2", "急上昇中のFW3"],
  "summary": "100字以内の日本語サマリー（2026年のトレンドを簡潔に）"
}
EOT;
 
    $payload = json_encode([
        'model'       => 'gpt-4o-mini',
        'temperature' => 0.3,
        'max_tokens'  => 2400,
        'messages'    => [
            ['role' => 'system', 'content' => 'あなたはITトレンドアナリストです。JSONのみで回答。langs_ranked と fws_top は必ず各80個の文字列配列。'],
            ['role' => 'user',   'content' => $prompt],
        ],
    ]);
 
    $ctx = stream_context_create(['http' => [
        'method'  => 'POST',
        'timeout' => 15,
        'ignore_errors' => true,
        'header'  => "Content-Type: application/json\r\nAuthorization: Bearer " . OPENAI_API_KEY . "\r\n",
        'content' => $payload,
    ]]);
    $raw = @file_get_contents('https://api.openai.com/v1/chat/completions', false, $ctx);
    if (!$raw) return $default;
 
    $resp = json_decode($raw, true);
    if (!is_array($resp)) return $default;
 
    /* レート制限・クォータ超過・課金エラー等 → 無料フォールバック（キャッシュなしで毎回デフォルト表示） */
    if (!empty($resp['error']) && is_array($resp['error'])) {
        $em = strtolower((string)($resp['error']['message'] ?? ''));
        $ec = strtolower((string)($resp['error']['code'] ?? ''));
        $et = strtolower((string)($resp['error']['type'] ?? ''));
        $quota_hit = ($ec === 'insufficient_quota' || $ec === 'rate_limit_exceeded')
            || strpos($em, 'rate limit') !== false
            || strpos($em, 'quota') !== false
            || strpos($em, 'billing') !== false
            || strpos($et, 'rate_limit') !== false
            || strpos($et, 'insufficient_quota') !== false;
        if ($quota_hit) {
            cache_set('openai_quota_pause', ['until' => time() + 21600]);
        }
        return $default;
    }
 
    $http429 = false;
    if (isset($http_response_header[0]) && is_string($http_response_header[0])) {
        $http429 = (bool)preg_match('/\s(429|402|503)\s/', $http_response_header[0]);
    }
    if ($http429) {
        cache_set('openai_quota_pause', ['until' => time() + 21600]);
        return $default;
    }
 
    $text = $resp['choices'][0]['message']['content'] ?? '';
 
    /* JSON部分だけを取り出す */
    $ai = null;
    if (preg_match('/\{[\s\S]+\}/u', $text, $m)) {
        $ai = json_decode($m[0], true);
    }
    if (empty($ai) || !is_array($ai)) return $default;
 
    $langs_ok = ai_trend_normalize_top50($ai['langs_ranked'] ?? null, $fb_langs);
    $fws_ok = ai_trend_normalize_top50($ai['fws_top'] ?? null, $fb_fws);
 
    $hot_langs_pick = [];
    foreach ($ai['hot_langs'] ?? [] as $x) {
        if (!is_string($x)) {
            continue;
        }
        $t = trim($x);
        if ($t !== '') {
            $hot_langs_pick[] = $t;
        }
        if (count($hot_langs_pick) >= 3) {
            break;
        }
    }
    $hot_fws_pick = [];
    foreach ($ai['hot_fws'] ?? [] as $x) {
        if (!is_string($x)) {
            continue;
        }
        $t = trim($x);
        if ($t !== '') {
            $hot_fws_pick[] = $t;
        }
        if (count($hot_fws_pick) >= 3) {
            break;
        }
    }
 
    $result = [
        'langs'      => $langs_ok,
        'fws_top'    => $fws_ok,
        'hot_langs'  => $hot_langs_pick,
        'hot_fws'    => $hot_fws_pick,
        'summary'    => $ai['summary']   ?? '',
        'updated_at' => date('Y-m-d H:i'),
        'model'      => 'gpt-4o-mini',
    ];
    cache_set('ai_trends', $result);
 
    /* AIが決定したトレンドをストックに記録 */
    $history = cache_get('trend_history', 86400 * 365) ?? [];
    $history[] = $result;
    if (count($history) > 30) $history = array_slice($history, -30);
    cache_set('trend_history', $history);
 
    return $result;
}
 
/* ═══════════════════════════════════════════════════════════
   実行：Google検索 & AIトレンド取得
═══════════════════════════════════════════════════════════ */
 
/* 検索クエリ生成（言語・FWを先頭に — 100文字切り詰めで技術名が落ちないようにする） */
$pref_label_for_search = '';
foreach (LOCATION_GROUPS as $g) {
    if ($g['value'] === $pref && $pref !== '') {
        $pref_label_for_search = $g['label'];
        break;
    }
}
$tail_kw = 'フリーランス 案件 求人 エンジニア';
$head_query_parts = array_values(array_filter(array_merge(
    $langs_in,
    $fws_in,
    $q !== '' ? [$q] : [],
    $pref_label_for_search !== '' ? [$pref_label_for_search] : [],
)));
if ($head_query_parts === []) {
    $search_query = 'ITエンジニア フリーランス 案件 求人 2026';
} else {
    $full_join = implode(' ', array_merge($head_query_parts, [$tail_kw]));
    if (mb_strlen($full_join) <= 100) {
        $search_query = $full_join;
    } else {
        $tail = ' ' . $tail_kw;
        $budget = max(24, 100 - mb_strlen($tail));
        $head_join = implode(' ', $head_query_parts);
        if (mb_strlen($head_join) <= $budget) {
            $search_query = $head_join . $tail;
        } else {
            $search_query = rtrim(mb_substr($head_join, 0, $budget)) . $tail;
        }
        if (mb_strlen($search_query) > 100) {
            $search_query = mb_substr($search_query, 0, 100);
        }
    }
}
 
/* メインで0件のとき短い語で再試行（CSEの対象サイト・言語差で0件になりやすいのを緩和） */
$google_fallback_queries = [];
$core = array_values(array_filter(array_merge($langs_in, $fws_in)));
if ($core !== []) {
    $google_fallback_queries[] = mb_substr(implode(' ', array_merge($core, ['求人', 'エンジニア', '案件'])), 0, 100);
    $google_fallback_queries[] = mb_substr(implode(' ', array_merge($core, ['フリーランス'])), 0, 100);
    $google_fallback_queries[] = mb_substr(implode(' ', $core) . ' 採用 エンジニア', 0, 100);
    $google_fallback_queries[] = mb_substr(implode(' ', $core) . ' 求人', 0, 100);
    if ($min_rate > 0) {
        $google_fallback_queries[] = mb_substr(implode(' ', $core) . " 単価{$min_rate}万円 求人", 0, 100);
    }
}
if ($pref_label_for_search !== '') {
    $google_fallback_queries[] = mb_substr($pref_label_for_search . ' IT 求人 エンジニア フリーランス', 0, 100);
}
$google_fallback_queries[] = 'IT エンジニア 求人 案件 2026';
$google_fallback_queries = array_values(array_unique(array_filter($google_fallback_queries, fn($x) => $x !== '' && $x !== $search_query)));
 
$google_results = google_search($search_query, $google_fallback_queries, GOOGLE_CSE_MAX_RESULTS);
 
/* Google CSE が0件でも（検索エンジン設定・API・キャッシュ等で）案件探索につながるよう外部求人サイトへの検索リンクを補完 */
function build_ext_site_search_cards(array $langs_in, array $fws_in, string $q, string $role): array {
    /* 全サイト共通キーワード（lang + fw + q + role、重複除去済み） */
    $kw_parts = array_values(array_unique(array_filter(array_merge(
        $langs_in,
        $fws_in,
        $q !== '' ? array_filter(array_map('trim', preg_split('/[\s+]+/', $q))) : [],
        $role !== '' ? [$role] : [],
    ))));
    $kw_sfx = urlencode(mb_substr(implode('+', $kw_parts), 0, 100));
 
    /* lang_only サイト用（フレームワーク非対応 → 言語名のみ） */
    $lang_parts = array_values(array_unique(array_filter($langs_in)));
    if ($lang_parts === [] && $q !== '') {
        $lang_parts = array_values(array_filter(array_map('trim', preg_split('/[\s+]+/', $q))));
    }
    $kw_lang_only = urlencode(mb_substr(implode('+', $lang_parts), 0, 100));
 
    /* lang_fw_pair サイト用（"Python(FastAPI)" 形式） */
    $lfmap = LANG_FW_MAP;
    $pair_parts = [];
    foreach ($langs_in as $lang) {
        $related    = $lfmap[$lang] ?? [];
        $paired_fws = array_values(array_intersect($fws_in, $related));
        $pair_parts[] = !empty($paired_fws)
            ? $lang . '(' . implode(' ', $paired_fws) . ')'
            : $lang;
    }
    $unpaired = array_diff($fws_in, array_merge(...array_values(array_map(
        fn($l) => array_intersect($fws_in, $lfmap[$l] ?? []),
        $langs_in ?: ['']
    ))));
    foreach ($unpaired as $fw) { $pair_parts[] = $fw; }
    if ($pair_parts === [] && $q !== '') {
        $pair_parts = array_filter(array_map('trim', preg_split('/[\s+]+/', $q)));
    }
    $kw_lang_fw_pair = urlencode(mb_substr(implode('+', $pair_parts), 0, 100));
 
    $label = implode(' ', array_merge($langs_in, $fws_in));
    if ($label === '') { $label = '条件'; }
    $out = [];
    foreach (EXT_SITES as $site) {
        if (!empty($site['levtech'])) {
            $out[] = [
                'title'       => $site['name'] . ' で「' . $label . '」を検索',
                'snippet'     => $site['desc'],
                'url'         => build_levtech_url($langs_in, $fws_in, $q),
                'domain'      => 'freelance.levtech.jp',
                'is_fallback' => true,
            ];
            continue;
        }
        if (!empty($site['fls'])) {
            $out[] = [
                'title'       => $site['name'] . ' で「' . $label . '」を検索',
                'snippet'     => $site['desc'],
                'url'         => build_fls_url($langs_in, $fws_in, $q),
                'domain'      => 'freelance-start.com',
                'is_fallback' => true,
            ];
            continue;
        }
        if (!empty($site['fixed_url'])) {
            /* URL固定サイト: キーワードを追加せず site['url'] をそのまま使用 */
            $out[] = [
                'title'       => $site['name'] . ' でコンサルフリーランス案件を探す',
                'snippet'     => $site['desc'],
                'url'         => $site['url'],
                'domain'      => 'high-performer.jp',
                'is_fallback' => true,
            ];
            continue;
        }
        if (!empty($site['geechs'])) {
            $out[] = [
                'title'       => $site['name'] . ' で「' . $label . '」を検索',
                'snippet'     => $site['desc'],
                'url'         => build_geechs_url($langs_in, $fws_in, $q),
                'domain'      => 'geechs-job.com',
                'is_fallback' => true,
            ];
            continue;
        }
        if (!empty($site['r_agent'])) {
            $out[] = [
                'title'       => $site['name'] . ' で「' . $label . '」を検索',
                'snippet'     => $site['desc'],
                'url'         => build_r_agent_url($langs_in, $q),
                'domain'      => 'www.r-agent.com',
                'is_fallback' => true,
            ];
            continue;
        }
        if (!empty($site['itanken'])) {
            $out[] = [
                'title'       => $site['name'] . ' で「' . $label . '」を検索',
                'snippet'     => $site['desc'],
                'url'         => build_itanken_url($langs_in, $fws_in, $q, $role),
                'domain'      => 'itanken.com',
                'is_fallback' => true,
            ];
            continue;
        }
        if (!empty($site['crowdtech'])) {
            $sfx = build_crowdtech_url($langs_in, $fws_in, $q);
            // クラウドテックはURLを site['url'] に追記しない（build_crowdtech_url が完全URLを返す）
            $out[] = [
                'title'       => $site['name'] . ' で「' . $label . '」を検索',
                'snippet'     => $site['desc'],
                'url'         => $sfx,
                'domain'      => 'tech.crowdworks.jp',
                'is_fallback' => true,
            ];
            continue;
        }
        if (!empty($site['midworks'])) {
            $out[] = [
                'title'       => $site['name'] . ' で「' . $label . '」を検索',
                'snippet'     => $site['desc'],
                'url'         => build_midworks_url($langs_in, $fws_in, $q),
                'domain'      => 'mid-works.com',
                'is_fallback' => true,
            ];
            continue;
        }
        if (!empty($site['findy_fl'])) {
            $out[] = [
                'title'       => $site['name'] . ' で「' . $label . '」を検索',
                'snippet'     => $site['desc'],
                'url'         => build_findy_fl_url($langs_in, $fws_in, $q),
                'domain'      => 'freelance.findy-code.io',
                'is_fallback' => true,
            ];
            continue;
        }
        if (!empty($site['offers'])) {
            $out[] = [
                'title'       => $site['name'] . ' で「' . $label . '」を検索',
                'snippet'     => $site['desc'],
                'url'         => build_offers_url($langs_in, $fws_in, $q),
                'domain'      => 'offers.jp',
                'is_fallback' => true,
            ];
            continue;
        }
        if (!empty($site['findy_career'])) {
            $out[] = [
                'title'       => $site['name'] . ' で「' . $label . '」を検索',
                'snippet'     => $site['desc'],
                'url'         => build_findy_career_url($langs_in, $fws_in, $q),
                'domain'      => 'findy-code.io',
                'is_fallback' => true,
            ];
            continue;
        }
        if (!empty($site['indeed'])) {
            /* Indeed は ?q= にスペース区切り（+）でキーワードを渡す正しい形式 */
            $i_parts = array_values(array_unique(array_filter(array_merge(
                $langs_in, $fws_in,
                $q !== '' ? array_filter(array_map('trim', preg_split('/[\s+]+/', $q))) : [],
            ))));
            $out[] = [
                'title'       => $site['name'] . ' で「' . $label . '」を検索',
                'snippet'     => $site['desc'],
                'url'         => $site['url'] . urlencode(implode(' ', $i_parts)),
                'domain'      => 'jp.indeed.com',
                'is_fallback' => true,
            ];
            continue;
        }
        if (!empty($site['daijob_jp'])) {
            /* Daijob 日本語版: 日本語ならそのまま、外国語ならGoogle翻訳経由で表示 */
            $out[] = [
                'title'       => $site['name'] . ' で「' . $label . '」を検索',
                'snippet'     => $site['desc'],
                'url'         => build_daijob_url($langs_in, $fws_in, $q, false),
                'domain'      => 'daijob.com',
                'is_fallback' => true,
                'daijob_kind' => 'jp',
            ];
            continue;
        }
        if (!empty($site['daijob_en'])) {
            /* Daijob 英語版: 翻訳プロキシを経由せず、英語サイトを直接表示 */
            $out[] = [
                'title'       => $site['name'] . ' search for "' . $label . '"',
                'snippet'     => $site['desc'],
                'url'         => build_daijob_url($langs_in, $fws_in, $q, true),
                'domain'      => 'daijob.com',
                'is_fallback' => true,
                'daijob_kind' => 'en',
            ];
            continue;
        }
        if (!empty($site['green'])) {
            $out[] = [
                'title'       => $site['name'] . ' で「' . $label . '」を検索',
                'snippet'     => $site['desc'],
                'url'         => build_green_url($langs_in, $fws_in, $q),
                'domain'      => 'green-japan.com',
                'is_fallback' => true,
            ];
            continue;
        }
        if (!empty($site['wantedly'])) {
            $out[] = [
                'title'       => $site['name'] . ' で「' . $label . '」を検索',
                'snippet'     => $site['desc'],
                'url'         => build_wantedly_url($langs_in, $fws_in, $q),
                'domain'      => 'wantedly.com',
                'is_fallback' => true,
            ];
            continue;
        }
        if (!empty($site['lang_fw_pair'])) {
            $sfx = $kw_lang_fw_pair;
        } elseif (!empty($site['lang_only'])) {
            $sfx = $kw_lang_only;
        } else {
            $sfx = $kw_sfx;
        }
        $out[] = [
            'title'       => $site['name'] . ' で「' . $label . '」を検索',
            'snippet'     => $site['desc'],
            'url'         => $site['url'] . $sfx,
            'domain'      => parse_url($site['url'], PHP_URL_HOST) ?: '',
            'is_fallback' => true,
        ];
    }
    return array_slice($out, 0, min(GOOGLE_CSE_MAX_RESULTS, count($out)));
}
 
if ($google_results === [] && GOOGLE_CSE_KEY !== '') {
    $google_results = build_ext_site_search_cards($langs_in, $fws_in, $q, $role);
}
$ai_trends      = ai_trend_analysis();
 
/* AIのランク順に言語リストを並べ替え（ページ表示に使う） */
$langs_ordered = !empty($ai_trends['langs']) ? $ai_trends['langs'] : ai_trend_fallback_langs_top50();
 
/* ═══════════════════════════════════════════════════════════
   ヘルパー関数
═══════════════════════════════════════════════════════════ */
function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

/* ===== 統一「更新済み」ラベル生成（/aruaru・/aruaru-lady 共通フォーマット） =====
   例: 2026-06-02 09:47 AM 更新済み（UPDATE）（毎日05:00に更新中）
   $when … UNIXタイムスタンプ または 日付文字列（"2026/05/22 13:24" 等）を許容
   $full=false で「2026-06-02 09:47 AM」部分のみ返す（HTMLでspan分割したい時用） */
if (!function_exists('aru_update_label')) {
function aru_update_label($when = null, bool $full = true): string {
    if ($when === null || $when === '') {
        $ts = time();
    } elseif (is_int($when) || (is_string($when) && ctype_digit($when))) {
        $ts = (int)$when;
    } else {
        $ts = (int)strtotime((string)$when);
    }
    if ($ts <= 0) $ts = time();
    $main = date('Y-m-d h:i A', $ts);   // 2026-06-02 09:47 AM
    if (!$full) return $main;
    return $main . ' 更新済み（UPDATE）（毎日05:00に更新中）';
}
}
/** JS の setHiddenChip と同じ規則（toggle 時に削除できるよう id を付与） */
function hci_chip_id(string $group, string $val): string {
    return 'hci-' . $group . '-' . preg_replace('/[^a-zA-Z0-9]/u', '_', $val);
}
function sel(string $val, string $current): string { return $val === $current ? ' selected' : ''; }
function chk(bool $cond): string { return $cond ? ' checked' : ''; }
function in_arr(string $v, array $arr): bool { return in_array($v, $arr, true); }
 
function posted_label(int $d): string {
    if ($d === 0) return '本日';
    if ($d === 1) return '昨日';
    return $d . '日前';
}
function style_badge(string $s): string {
    return match($s) {
        'remote' => '<span class="bdg bdg-remote">リモート</span>',
        'hybrid' => '<span class="bdg bdg-hybrid">ハイブリッド</span>',
        'onsite' => '<span class="bdg bdg-onsite">常駐</span>',
        default  => '',
    };
}
 
/* ═══════════════════════════════════════════════════════════
   HTML 出力
═══════════════════════════════════════════════════════════ */
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<!-- BUILD: 2026-05-25-CLOSE-RADIO-v3.1 (4番目CLOSE表示バグ修正 + pBot 旧値廃止) -->
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>aruaru | ITエンジニア案件・求人マッチング</title>
<meta name="description" content="希望言語・フレームワーク・月額・勤務地からIT案件・求人をマッチング。外部サイトへ直接応募。ロリポップ！含む全レンタルサーバー対応の純粋PHP版。">
<meta name="theme-color" content="#0b1220">
<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' rx='22' fill='%2306b6d4'/%3E%3Ctext x='50' y='72' font-size='72' text-anchor='middle' fill='white' font-family='sans-serif' font-weight='900'%3Ea%3C/text%3E%3C/svg%3E">
<!-- Bootstrap 5.3 CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<!-- Noto Sans JP -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700;900&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#0b1220;--bg2:#0f172a;--surface:#111c33;--surface2:#16223d;
  --border:rgba(255,255,255,.08);--border-s:rgba(255,255,255,.16);
  --text:#fff;--dim:#fff;--muted:#dde6f5;
  --primary:#06b6d4;--primary-lt:#22d3ee;--primary-glow:rgba(6,182,212,.18);
  --accent:#a78bfa;--success:#10b981;--warning:#f59e0b;--danger:#ef4444;
  --r:14px;
  --aruaru-logo-fixed-h:52px;
  --aruaru-gadget-top:calc(env(safe-area-inset-top, 0px) + var(--aruaru-logo-fixed-h) + 10px);
}
html{scroll-behavior:smooth}
body{
  font-family:'Noto Sans JP','Hiragino Kaku Gothic ProN',Meiryo,system-ui,sans-serif;
  background:
    radial-gradient(ellipse 1300px 600px at 90% -5%,rgba(6,182,212,.13),transparent 62%),
    radial-gradient(ellipse 900px 500px at -5% 28%, rgba(167,139,250,.10),transparent 56%),
    var(--bg);
  color:var(--text);min-height:100vh;-webkit-font-smoothing:antialiased;
  /* 固定ロゴ + 右上 Google 翻訳ウィジェット分の余白（ヘッダーは sticky にしない） */
  padding-top:calc(env(safe-area-inset-top, 0px) + var(--aruaru-logo-fixed-h) + 268px);
}
@media (max-width:600px){
  :root{
    --aruaru-logo-fixed-h:48px;
    --aruaru-gadget-top:calc(env(safe-area-inset-top, 0px) + var(--aruaru-logo-fixed-h) + 6px);
  }
  body{padding-top:calc(env(safe-area-inset-top, 0px) + var(--aruaru-logo-fixed-h) + 300px)}
}
a{color:inherit;text-decoration:none}
::-webkit-scrollbar{width:6px;height:6px}
::-webkit-scrollbar-track{background:var(--bg2)}
::-webkit-scrollbar-thumb{background:rgba(255,255,255,.14);border-radius:3px}
 
/* サイトナビ（スクロールで張り付かない。固定はロゴバーと Google ウィジェットのみ） */
.site-header{position:relative;top:auto;z-index:100;background:rgba(11,18,32,.82);border-bottom:1px solid var(--border);backdrop-filter:blur(14px) saturate(1.4);-webkit-backdrop-filter:blur(14px) saturate(1.4)}
.brand{display:flex;align-items:center;gap:.6rem;font-weight:900;font-size:1.05rem}
.brand-icon{width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,var(--primary),var(--accent));display:grid;place-items:center;font-size:1rem;font-weight:900;color:#0b1220;box-shadow:0 6px 18px rgba(6,182,212,.35);flex-shrink:0}
.nav-a{color:#fff;font-weight:600;font-size:.88rem;padding:.42rem .7rem;border-radius:8px;transition:all .15s}
.nav-a:hover{color:#fff;background:rgba(255,255,255,.14)}
.btn-cta{padding:.48rem 1rem;border-radius:9px;border:0;font-weight:700;font-size:.85rem;background:linear-gradient(135deg,var(--primary),var(--accent));color:#0b1220;box-shadow:0 6px 18px rgba(6,182,212,.3);transition:filter .15s;cursor:pointer}
.btn-cta:hover{filter:brightness(1.1)}
 
/* Hero */
.hero{padding:3.5rem 0 2rem;text-align:center}
.hero-h1{font-size:clamp(1.7rem,4.5vw,2.9rem);font-weight:900;line-height:1.25;letter-spacing:-.01em;background:linear-gradient(135deg,#fff 0%,#cfe9ff 55%,#a5b4fc 100%);-webkit-background-clip:text;background-clip:text;color:transparent}
.hero-sub{color:var(--dim);font-size:clamp(.88rem,1.5vw,.98rem);max-width:48rem;margin:.75rem auto 0;line-height:1.7}
.hero-stat-val{font-size:1.35rem;font-weight:900;color:#fff;display:block}
.hero-stat-lbl{font-size:.88rem;color:#dde6f5}
 
/* Search Panel */
.search-panel{background:var(--surface);border:1px solid var(--border-s);border-radius:var(--r);padding:1.4rem;box-shadow:0 12px 36px rgba(0,0,0,.38)}
.f-label{font-size:.84rem;font-weight:700;color:#fff;letter-spacing:.06em;text-transform:uppercase;margin-bottom:.32rem;display:block}
.form-control,.form-select{background:var(--surface2)!important;border:1px solid var(--border-s)!important;color:#fff!important;border-radius:9px!important;font-size:.88rem}
.form-control::placeholder{color:rgba(255,255,255,.45)!important}
.form-control:focus,.form-select:focus{box-shadow:0 0 0 3px var(--primary-glow)!important;border-color:var(--primary-lt)!important;outline:none}
.form-select option{background:var(--surface2);color:#fff}
.form-range{accent-color:var(--primary);cursor:pointer}
.form-check-input{background:var(--surface2);border-color:var(--border-s)}
.form-check-input:checked{background-color:var(--primary);border-color:var(--primary)}
.form-check-label{color:#fff;font-size:.88rem}
 
/* Chips */
.chip-wrap{display:flex;flex-wrap:wrap;gap:.28rem;max-height:78px;overflow:hidden;transition:max-height .25s ease}
.chip-wrap.open{max-height:none}
.chip{display:inline-block;padding:.22rem .58rem;border-radius:9999px;font-size:.84rem;font-weight:700;background:rgba(255,255,255,.08);color:#fff;border:1px solid rgba(255,255,255,.22);cursor:pointer;transition:all .12s;user-select:none;line-height:1.5}
.ai-trend-chips .chip{font-size:.92rem;padding:.28rem .72rem}
.chip:hover{color:#fff;background:rgba(255,255,255,.18)}
.chip.on{background:var(--primary-glow);color:var(--primary-lt);border-color:rgba(34,211,238,.45)}
.chip-cat{background:rgba(167,139,250,.08);color:var(--accent);border-color:rgba(167,139,250,.25);cursor:default;font-size:.84rem}
.chip-more{font-size:.84rem;color:var(--primary-lt);cursor:pointer;margin-top:.25rem;display:inline-block}
 
/* Buttons */
.btn-search{width:100%;padding:.75rem 1.4rem;border-radius:10px;border:0;font-weight:800;font-size:.95rem;background:linear-gradient(135deg,var(--primary),var(--accent));color:#0b1220;box-shadow:0 8px 24px rgba(6,182,212,.3);transition:filter .15s,transform .1s;cursor:pointer}
.btn-search:hover{filter:brightness(1.08);transform:translateY(-1px)}
.btn-reset{width:100%;display:block;text-align:center;padding:.75rem 1.1rem;border-radius:10px;border:1px solid rgba(255,255,255,.3);background:transparent;color:#fff;font-weight:700;font-size:.88rem;transition:all .15s}
.btn-reset:hover{color:#fff;background:rgba(255,255,255,.1)}
.sort-sel{background:var(--surface2);border:1px solid var(--border-s);color:#fff;padding:.4rem .75rem;border-radius:8px;font-size:.88rem;font-family:inherit;cursor:pointer}
 
/* Cards */
.card-anken{background:linear-gradient(165deg,var(--surface) 0%,var(--surface2) 100%);border:1px solid var(--border);border-radius:var(--r);padding:1.1rem;display:flex;flex-direction:column;height:100%;position:relative;overflow:hidden;transition:transform .18s,border-color .18s,box-shadow .18s}
.card-anken::before{content:"";position:absolute;inset:0 0 auto 0;height:3px;background:linear-gradient(90deg,var(--primary),var(--accent));opacity:0;transition:opacity .2s}
.card-anken:hover{transform:translateY(-3px);border-color:var(--border-s);box-shadow:0 14px 38px rgba(0,0,0,.48)}
.card-anken:hover::before{opacity:1}
.bdg{display:inline-block;font-size:.84rem;font-weight:800;padding:.16rem .48rem;border-radius:5px;letter-spacing:.03em;margin-right:.18rem}
.bdg-new{background:rgba(16,185,129,.16);color:#34d399}
.bdg-hot{background:rgba(239,68,68,.16);color:#fb7185}
.bdg-remote{background:rgba(34,211,238,.16);color:var(--primary-lt)}
.bdg-onsite{background:rgba(245,158,11,.16);color:#fbbf24}
.bdg-hybrid{background:rgba(167,139,250,.16);color:#c4b5fd}
.card-title{font-size:.93rem;font-weight:800;color:#fff;line-height:1.45}
.card-meta{font-size:.88rem;color:#fff;display:flex;flex-wrap:wrap;gap:.25rem .75rem}
.stag{display:inline-block;font-size:.84rem;font-weight:700;padding:.14rem .48rem;border-radius:5px}
.stag-lang{background:rgba(6,182,212,.12);color:#67e8f9;border:1px solid rgba(6,182,212,.28)}
.stag-fw{background:rgba(167,139,250,.12);color:#c4b5fd;border:1px solid rgba(167,139,250,.28)}
.card-rate{font-size:1.22rem;font-weight:900;color:#fff}
.card-rate small{font-size:.84rem;color:#dde6f5;font-weight:500}
.card-annual{font-size:.88rem;font-weight:700;color:#c4b5fd;margin-left:.4rem}
.site-badge{font-size:.84rem;font-weight:700;color:#dde6f5;border:1px solid rgba(255,255,255,.25);padding:.1rem .42rem;border-radius:5px}
.btn-apply{display:inline-flex;align-items:center;gap:.28rem;padding:.52rem .88rem;border-radius:8px;font-size:.88rem;font-weight:800;background:var(--primary-glow);color:var(--primary-lt);border:1px solid rgba(34,211,238,.35);transition:all .15s}
.btn-apply:hover{background:var(--primary);color:#0b1220;border-color:transparent}
.empty-state{text-align:center;padding:3.5rem 1rem;background:var(--surface);border:1px dashed rgba(255,255,255,.25);border-radius:var(--r);color:#fff}
.empty-state strong{display:block;color:#fff;font-size:1.05rem;margin-bottom:.4rem}
 
/* External Sites */
.ext-section{background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:1.5rem}
.ext-title{font-size:1.05rem;font-weight:800;color:#fff}
.ext-sub{font-size:.88rem;color:#fff;line-height:1.65}
.ext-card{display:block;height:100%;background:var(--surface2);border:1px solid var(--border);border-radius:11px;padding:.9rem 1rem;transition:border-color .15s,transform .15s,box-shadow .15s;color:inherit}
.ext-card:hover{border-color:var(--border-s);transform:translateY(-2px);box-shadow:0 8px 22px rgba(0,0,0,.3)}
.ext-card-name{font-size:.92rem;font-weight:800;color:#fff}
.ext-tag{font-size:.84rem;font-weight:800;padding:.1rem .42rem;border-radius:5px}
.t-fl{background:rgba(6,182,212,.15);color:var(--primary-lt)}
.t-sei{background:rgba(16,185,129,.15);color:#34d399}
.t-side{background:rgba(245,158,11,.15);color:#fbbf24}
.t-gen{background:rgba(167,139,250,.15);color:#c4b5fd}
.ext-desc{font-size:.88rem;color:#dde6f5;margin:.28rem 0;line-height:1.55}
.ext-cta{font-size:.88rem;font-weight:700;color:var(--primary-lt)}
 
/* Footer */
.site-footer{border-top:1px solid var(--border);padding:1.6rem 1rem;text-align:center;color:#dde6f5;font-size:.88rem;line-height:1.9}
.site-footer a{color:#fff}
.site-footer a:hover{color:var(--primary-lt)}
/* ===== 画面上端固定: aruaru ロゴ（本文と一緒に動かさない） ===== */
#aruaru-logo-fixed{
  position:fixed;top:0;left:0;right:0;z-index:2147483646;
  height:var(--aruaru-logo-fixed-h);
  min-height:var(--aruaru-logo-fixed-h);
  padding:0 max(12px, env(safe-area-inset-right)) 0 max(12px, env(safe-area-inset-left));
  padding-top:env(safe-area-inset-top, 0px);
  box-sizing:border-box;
  display:flex;align-items:center;justify-content:center;
  background:rgba(15,23,42,.98);
  border-bottom:1px solid var(--border);
}
#aruaru-logo-fixed .aruaru-logo-fixed__link{display:flex;align-items:center;justify-content:center;min-height:calc(var(--aruaru-logo-fixed-h) - env(safe-area-inset-top, 0px));}
#aruaru-logo-fixed #logoCanvas{display:block;width:clamp(120px, 42vw, 200px);height:36px;cursor:pointer}
/* ===== aruaru ロゴアニメ（canvas は #aruaru-logo-fixed 内） ===== */
#aruaru-logo-section{background:rgba(15,23,42,1);padding:1.2rem 1rem .4rem;width:100%;box-sizing:border-box;text-align:center}
#logoWrap{position:relative;width:100%;max-width:1100px;height:220px;margin:0 auto 6px}
@media(max-width:600px){#logoWrap{height:150px}}
#logoWrap canvas{position:absolute;width:100%;height:100%;top:0;left:0}
#logoModeBar{display:flex;justify-content:flex-start;gap:.35rem;margin:.3rem 0 .2rem;flex-wrap:wrap;align-items:center}
.logo-mode-btn{font-size:.84rem;font-weight:700;padding:.32rem .75rem;border-radius:9px;border:1px solid rgba(34,211,238,.42);background:rgba(15,23,42,.65);color:#e0f2fe;cursor:pointer;transition:all .15s}
.logo-mode-btn:hover{border-color:rgba(34,211,238,.8);background:rgba(34,211,238,.18);color:#fff}
.logo-mode-btn.active{border-color:rgba(34,211,238,.9);background:rgba(34,211,238,.28);color:#fff;box-shadow:inset 0 0 0 1px rgba(255,255,255,.12)}
#danceFixedBar{display:none;flex-wrap:wrap;gap:.3rem;margin:.2rem 0 .4rem;padding:.35rem .5rem;border-radius:9px;background:rgba(15,23,42,.55);border:1px dashed rgba(34,211,238,.3)}
#danceFixedBar.visible{display:flex}
#danceFixedBar .df-label{font-size:.84rem;font-weight:700;color:#94a3b8;letter-spacing:.06em;margin-right:.3rem;align-self:center;white-space:nowrap}
.dance-fixed-btn{font-size:.84rem;font-weight:700;padding:.25rem .55rem;border-radius:7px;border:1px solid rgba(148,163,184,.4);background:rgba(15,23,42,.65);color:#cbd5e1;cursor:pointer;transition:all .15s}
.dance-fixed-btn:hover{border-color:rgba(34,211,238,.7);background:rgba(34,211,238,.14);color:#fff}
.dance-fixed-btn.active{border-color:#22d3ee;background:rgba(34,211,238,.3);color:#fff}
 
/* Google翻訳フィードバックバルーン 完全非表示 */
.goog-te-balloon-frame,.goog-te-balloon-bg,#goog-gt-tt,#goog-gt-votingframe,
.goog-tooltip,.goog-tooltip-content,.goog-te-ftphfe,.goog-te-spinner-pos,.goog-te-highlight{
  display:none!important;visibility:hidden!important;opacity:0!important;pointer-events:none!important;
}
</style>

<style>
/* Auto-adjusted readability */
body,
p,
span,
div,
li,
td,
th,
a,
small,
.text-muted,
.text-secondary,
.text-gray,
.text-grey,
.text-light-black {
    color: #ffffff !important;
    font-size: 1.12em !important;
}

h1,h2,h3,h4,h5,h6{
    font-size: 1.18em !important;
}

table{
    font-size: 1.08em !important;
}
</style>

</head>
<body>

<!-- HERO -->
<?php
/* ===== 
<div style="
background:#0b1f4d;
color:#ffffff;
padding:16px;
border-radius:12px;
margin:12px 0;
font-size:20px;
line-height:1.8;
font-weight:bold;
">
💃 キャバクラ・TVチャットレディなど主に女性向けのお仕事情報は移転しました →<br>
📍 <a href="https://audiocafe.tokyo/aruaru-lady" style="color:#ffffff;text-decoration:underline;">
audiocafe.tokyo/aruaru-lady
</a>
</div>

楽天モバイル情報（毎日自動クロール・キャッシュ更新） ===== */
if (!defined('RAKUTEN_CACHE_FILE')) define('RAKUTEN_CACHE_FILE', __DIR__ . '/rakuten-mobile-cache.json');
if (!defined('RAKUTEN_CACHE_TTL'))  define('RAKUTEN_CACHE_TTL',  86400); // 毎日更新（旧:7日）
if (!function_exists('rakuten_fetch_price')) {
    function rakuten_fetch_price(): array {
        $d = ['price'=>'最大3,278円（税込）','plan'=>'Rakuten最強プラン','updated_at'=>date('Y/m/d'),'source_url'=>'https://network.mobile.rakuten.co.jp/fee/saikyo-plan/'];
        $ctx = stream_context_create(['http'=>['timeout'=>6,'user_agent'=>'Mozilla/5.0 (compatible; AudiocafeBot/1.0)','follow_location'=>1,'max_redirects'=>3]]);
        $html = @file_get_contents('https://network.mobile.rakuten.co.jp/fee/saikyo-plan/', false, $ctx);
        if ($html && preg_match('/([0-9,]+)円[（(]税込[）)]/', $html, $m)) $d['price'] = $m[1] . '円（税込）';
        $d['updated_at'] = date('Y/m/d');
        return $d;
    }
}
if (!function_exists('rakuten_get_info')) {
    function rakuten_get_info(): array {
        $need = true;
        if (is_readable(RAKUTEN_CACHE_FILE) && (time()-(int)@filemtime(RAKUTEN_CACHE_FILE)) < RAKUTEN_CACHE_TTL) $need = false;
        if ($need) { $data = rakuten_fetch_price(); @file_put_contents(RAKUTEN_CACHE_FILE, json_encode($data, JSON_UNESCAPED_UNICODE), LOCK_EX); return $data; }
        $cached = @json_decode(@file_get_contents(RAKUTEN_CACHE_FILE), true);
        return is_array($cached) ? $cached : rakuten_fetch_price();
    }
}

/* ===== 楽天モバイル 国際通話情報（毎日自動クロール） ===== */
if (!defined('RAKUTEN_INTL_CACHE_FILE')) define('RAKUTEN_INTL_CACHE_FILE', __DIR__ . '/rakuten-intl-call-cache.json');
if (!defined('RAKUTEN_INTL_CACHE_TTL'))  define('RAKUTEN_INTL_CACHE_TTL',  24 * 3600);

if (!function_exists('rakuten_intl_fetch_html')) {
    function rakuten_intl_fetch_html(string $url): string {
        $ctx = stream_context_create(['http' => [
            'timeout' => 10, 'follow_location' => 1, 'max_redirects' => 5,
            'user_agent' => 'Mozilla/5.0 (compatible; AudiocafeBot/1.0; +https://audiocafe.tokyo/)',
            'ignore_errors' => true,
        ]]);
        $r = @file_get_contents($url, false, $ctx);
        return is_string($r) ? $r : '';
    }
}
if (!function_exists('rakuten_intl_strip')) {
    function rakuten_intl_strip(string $html): string {
        $t = preg_replace('/<script[^>]*>.*?<\/script>/si', '', $html);
        $t = preg_replace('/<style[^>]*>.*?<\/style>/si', '', $t);
        $t = preg_replace('/<[^>]+>/', ' ', $t);
        $t = html_entity_decode($t, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return preg_replace('/\s+/', ' ', $t);
    }
}
if (!function_exists('rakuten_intl_crawl')) {
    function rakuten_intl_crawl(): array {
        $d = [
            'intl_plan_price_ja'   => '月980円（税込）',
            'intl_plan_price_en'   => '980 yen/month (tax included)',
            'intl_countries_count' => '66',
            'intl_plan_name_ja'    => '国際通話かけ放題',
            'intl_plan_name_en'    => 'International Unlimited Calling',
            'conditions_ja'        => [
                '渡航前に日本国内で Rakuten Link の認証が必要',
                '対象国・地域のみ（約66カ国・地域）',
                '一部地域では Wi-Fi 接続が必須',
                '0570・0120 など一部番号は無料対象外',
                'iPhone は海外着信仕様が Android と一部異なる',
                'Rakuten Link を使用した IP 通話方式',
            ],
            'conditions_en'        => [
                'Rakuten Link must be authenticated in Japan before traveling overseas',
                'Only supported countries/regions (~66 countries)',
                'Wi-Fi may be required in some regions',
                'Some numbers (0570/0120 etc.) are excluded',
                'iPhone overseas behavior differs slightly from Android',
                'Works as IP calling via Rakuten Link app',
            ],
            'notes_ja'             => '月980円の「国際通話かけ放題」は主に「日本→海外」向けですが、Rakuten Link なら海外→日本も無料（対象国・条件あり）。',
            'notes_en'             => 'The 980-yen plan mainly covers Japan→overseas, but Rakuten Link also enables free overseas→Japan calls (conditions apply).',
            'crawled_at'           => date('Y/m/d H:i'),
            'crawl_success'        => false,
        ];
        $targets = [
            'intl_call' => 'https://network.mobile.rakuten.co.jp/service/international-call-free/',
            'intl_top'  => 'https://network.mobile.rakuten.co.jp/service/international/',
            'rlink'     => 'https://network.mobile.rakuten.co.jp/service/rakuten-link/',
        ];
        $texts = []; $ok = 0;
        foreach ($targets as $k => $url) {
            $h = rakuten_intl_fetch_html($url);
            if ($h !== '') { $texts[$k] = rakuten_intl_strip($h); $ok++; }
        }
        if ($ok === 0) return $d;
        $all = implode(' ', $texts);
        $d['crawl_success'] = true;
        // 料金
        foreach (['/月額?\s*([0-9,]+)\s*円[（(]税込[）)]/u', '/([0-9,]+)\s*円[（(]税込[）)]\s*[\/／]?月/u'] as $p) {
            if (preg_match($p, $all, $m)) {
                $n = (int)preg_replace('/,/', '', $m[1]);
                if ($n >= 300 && $n <= 5000) {
                    $d['intl_plan_price_ja'] = '月' . number_format($n) . '円（税込）';
                    $d['intl_plan_price_en'] = number_format($n) . ' yen/month (tax included)';
                    break;
                }
            }
        }
        // 対象国数
        foreach (['/([0-9]+)\s*[カかヵ]国/u', '/([0-9]+)\s*countries?/i'] as $p) {
            if (preg_match($p, $all, $m)) {
                $c = (int)$m[1];
                if ($c >= 30 && $c <= 200) {
                    $d['intl_countries_count'] = (string)$c;
                    $d['conditions_ja'][1] = "対象国・地域のみ（約{$c}カ国・地域）";
                    $d['conditions_en'][1] = "Only supported countries/regions (~{$c} countries)";
                    break;
                }
            }
        }
        $d['crawled_at'] = date('Y/m/d H:i');
        return $d;
    }
}
if (!function_exists('rakuten_intl_get')) {
    function rakuten_intl_get(): array {
        $f = RAKUTEN_INTL_CACHE_FILE; $ttl = RAKUTEN_INTL_CACHE_TTL;
        if (is_readable($f) && (time()-(int)@filemtime($f)) < $ttl) {
            $c = @json_decode(@file_get_contents($f), true);
            if (is_array($c) && !empty($c['crawled_at'])) return $c;
        }
        $data = rakuten_intl_crawl();
        @file_put_contents($f, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
        return $data;
    }
}

/* ===== 楽天モバイル プラチナバンド・衛星電話（毎日自動クロール） ===== */
if (!defined('RAKUTEN_PLATINUM_CACHE_FILE')) define('RAKUTEN_PLATINUM_CACHE_FILE', __DIR__ . '/rakuten-platinum-cache.json');
if (!defined('RAKUTEN_PLATINUM_CACHE_TTL'))  define('RAKUTEN_PLATINUM_CACHE_TTL',  24 * 3600);

if (!function_exists('rakuten_platinum_crawl')) {
    function rakuten_platinum_crawl(): array {
        $d = [
            'platinum_status_ja'   => '700MHz帯プラチナバンドを整備中。地下・屋内・山間部でのつながりやすさを改善。',
            'platinum_status_en'   => 'Rakuten Mobile is expanding its 700MHz Platinum Band to improve indoor, underground, and rural coverage.',
            'platinum_coverage_ja' => '全国整備進行中（順次拡大中）',
            'platinum_coverage_en' => 'Nationwide rollout in progress',
            'platinum_detail_ja'   => '700MHz帯は建物内・地下街まで届きやすい低周波数帯。屋内での通話・データ通信の安定性が向上。',
            'platinum_detail_en'   => 'The 700MHz band penetrates buildings and underground areas more effectively, improving indoor stability.',
            'satellite_status_ja'  => 'AST SpaceMobile との提携により、衛星ブロードバンド通話サービスを準備中。',
            'satellite_status_en'  => 'In partnership with AST SpaceMobile, Rakuten Mobile is developing satellite broadband calling.',
            'satellite_launch_ja'  => '商用サービス開始時期は未定（2025〜2026年目標と報道あり）',
            'satellite_launch_en'  => 'Commercial launch TBD (reports suggest 2025–2026 target)',
            'satellite_detail_ja'  => '低軌道衛星（LEO）により山間部・離島・海上でも通常スマートフォンで通話・データ通信が可能になる見込み。',
            'satellite_detail_en'  => 'LEO satellites will enable calls and data in remote mountains, islands, and offshore areas with standard smartphones.',
            'crawled_at'           => date('Y/m/d H:i'),
            'crawl_success'        => false,
        ];
        $ctx = stream_context_create(['http' => [
            'timeout' => 10, 'follow_location' => 1, 'max_redirects' => 5,
            'user_agent' => 'Mozilla/5.0 (compatible; AudiocafeBot/1.0; +https://audiocafe.tokyo/)',
            'ignore_errors' => true,
        ]]);
        $targets = [
            'top'  => 'https://network.mobile.rakuten.co.jp/',
            'area' => 'https://network.mobile.rakuten.co.jp/area/',
            'news' => 'https://corp.rakuten.co.jp/news/press/',
        ];
        $texts = []; $ok = 0;
        foreach ($targets as $k => $url) {
            $h = @file_get_contents($url, false, $ctx);
            if (is_string($h) && $h !== '') {
                $t = preg_replace('/<script[^>]*>.*?<\/script>/si', '', $h);
                $t = preg_replace('/<style[^>]*>.*?<\/style>/si', '', $t);
                $t = preg_replace('/<[^>]+>/', ' ', $t);
                $t = html_entity_decode($t, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $texts[$k] = preg_replace('/\s+/', ' ', $t);
                $ok++;
            }
        }
        if ($ok === 0) return $d;
        $all = implode(' ', $texts);
        $d['crawl_success'] = true;
        // カバレッジ率
        foreach (['/プラチナバンド[^。]{0,60}([0-9]+(?:\.[0-9]+)?)\s*%/u', '/700\s*MHz[^。]{0,60}([0-9]+(?:\.[0-9]+)?)\s*%/u'] as $p) {
            if (preg_match($p, $all, $m)) {
                $d['platinum_coverage_ja'] = "人口カバー率 {$m[1]}%（公式より）";
                $d['platinum_coverage_en'] = "Population coverage {$m[1]}% (official)";
                break;
            }
        }
        // プラチナバンド 最新文
        foreach (['/(プラチナバンド[^。]{15,150}。)/u', '/(700\s*MHz[^。]{15,120}。)/u'] as $p) {
            if (preg_match($p, $all, $m)) {
                $s = mb_substr(trim($m[1]), 0, 160);
                if (mb_strlen($s) > 20) { $d['platinum_status_ja'] = $s . '（楽天公式より）'; break; }
            }
        }
        // 衛星 最新文
        foreach (['/(AST\s*SpaceMobile[^。]{10,180}。)/u', '/(衛星[^。]{5,80}(?:通話|サービス|接続)[^。]{0,60}。)/u'] as $p) {
            if (preg_match($p, $all, $m)) {
                $s = mb_substr(trim($m[1]), 0, 200);
                if (mb_strlen($s) > 15) { $d['satellite_status_ja'] = $s . '（公式より）'; break; }
            }
        }
        // 衛星 開始時期
        foreach (['/(衛星[^。]{0,60}20[2-9][0-9]年[^。]{0,50}。)/u', '/(AST[^。]{0,80}20[2-9][0-9][^。]{0,50}。)/u'] as $p) {
            if (preg_match($p, $all, $m)) {
                $s = mb_substr(trim($m[1]), 0, 150);
                if (mb_strlen($s) > 20) { $d['satellite_launch_ja'] = $s . '（公式より）'; break; }
            }
        }
        $d['crawled_at'] = date('Y/m/d H:i');
        return $d;
    }
}
if (!function_exists('rakuten_platinum_get')) {
    function rakuten_platinum_get(): array {
        $f = RAKUTEN_PLATINUM_CACHE_FILE; $ttl = RAKUTEN_PLATINUM_CACHE_TTL;
        if (is_readable($f) && (time()-(int)@filemtime($f)) < $ttl) {
            $c = @json_decode(@file_get_contents($f), true);
            if (is_array($c) && !empty($c['crawled_at'])) return $c;
        }
        $data = rakuten_platinum_crawl();
        @file_put_contents($f, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
        return $data;
    }
}

/* =====================================================================
   楽天モバイル おすすめスマホ / we2 plus キャンペーン状態（毎日クロール）
   キャッシュ: rakuten-smartphone-cache.json  TTL: 24時間

   ロジック:
     - we2 plus 1円キャンペーンが有効 → campaign_active=true
     - キャンペーン終了 → 複数おすすめスマホを抽出して recommended[] に格納
     - 表示側は campaign_active で分岐
===================================================================== */
/* =====================================================================
   楽天モバイル スマホ表示設定（3段階）
   ※ 楽天モバイルのサイトはクローラーをブロック（403）するため手動管理。

   【段階1】RAKUTEN_1YEN_CAMPAIGN = true
     → 現在のwe2 plus 1円ボタンを1つ表示

   【段階2】RAKUTEN_1YEN_CAMPAIGN = false + RAKUTEN_1YEN_PHONES に1台以上
     → 「1円スマホコーナー」として複数ボタン表示（黄色枠）

   【段階3】RAKUTEN_1YEN_CAMPAIGN = false + RAKUTEN_1YEN_PHONES = []
     → 「おすすめスマホコーナー」として RAKUTEN_RECOMMENDED_PHONES を表示
===================================================================== */

// ★ 段階1: true=1円キャンペーン中（we2 plus） / false=終了
if (!defined('RAKUTEN_1YEN_CAMPAIGN')) define('RAKUTEN_1YEN_CAMPAIGN', true);

// 段階1用: キャンペーン中モデルの検索キーワード
if (!defined('RAKUTEN_1YEN_SEARCH_JA')) define('RAKUTEN_1YEN_SEARCH_JA', '楽天モバイル 富士通 we2 plus 1円');
if (!defined('RAKUTEN_1YEN_SEARCH_EN')) define('RAKUTEN_1YEN_SEARCH_EN', 'Rakuten Mobile Fujitsu arrows We2 Plus 1 yen campaign');

// ★ 段階2: 1円スマホが複数ある場合はここに列挙（1台でも可）
//   RAKUTEN_1YEN_CAMPAIGN=false の時だけ使われる
//   1円スマホがなくなったら [] に変更 → 段階3（おすすめコーナー）に自動移行
if (!defined('RAKUTEN_1YEN_PHONES')) define('RAKUTEN_1YEN_PHONES', serialize([
    // 例: ['name_ja'=>'arrows We2 Plus', 'search_ja'=>'楽天モバイル arrows We2 Plus 1円', 'search_en'=>'Rakuten arrows We2 Plus 1yen'],
    // キャンペーン終了後に1円機種があればここに追加
]));

// 段階3: 1円スマホが完全にない時のおすすめ一覧
if (!defined('RAKUTEN_RECOMMENDED_PHONES')) define('RAKUTEN_RECOMMENDED_PHONES', serialize([
    ['name_ja'=>'Rakuten Hand 5G',     'search_ja'=>'楽天モバイル Rakuten Hand 5G おすすめ 価格',    'search_en'=>'Rakuten Mobile Rakuten Hand 5G price'],
    ['name_ja'=>'AQUOS wish4',          'search_ja'=>'楽天モバイル AQUOS wish4 価格 スペック',         'search_en'=>'Rakuten Mobile AQUOS wish4 price'],
    ['name_ja'=>'Galaxy A55 5G',        'search_ja'=>'楽天モバイル Galaxy A55 5G 価格 キャンペーン',   'search_en'=>'Rakuten Mobile Galaxy A55 5G price'],
    ['name_ja'=>'Xperia 10 VI',         'search_ja'=>'楽天モバイル Xperia 10 VI 価格 スペック',        'search_en'=>'Rakuten Mobile Xperia 10 VI price'],
    ['name_ja'=>'Google Pixel 8a',      'search_ja'=>'楽天モバイル Pixel 8a 価格 キャンペーン',        'search_en'=>'Rakuten Mobile Pixel 8a price'],
    ['name_ja'=>'iPhone 15',            'search_ja'=>'楽天モバイル iPhone 15 価格 乗り換え',           'search_en'=>'Rakuten Mobile iPhone 15 price deal'],
    ['name_ja'=>'motorola edge 50 pro', 'search_ja'=>'楽天モバイル motorola edge 50 pro 価格',        'search_en'=>'Rakuten Mobile Motorola edge 50 pro price'],
    ['name_ja'=>'OPPO Reno11 A',        'search_ja'=>'楽天モバイル OPPO Reno11 A 価格 スペック',      'search_en'=>'Rakuten Mobile OPPO Reno11 A price'],
]));

$_rk_price = htmlspecialchars((string)($_rk['price'] ?? '最大3,278円（税込）'), ENT_QUOTES, 'UTF-8');
$_rk_updated = htmlspecialchars((string)($_rk['updated_at'] ?? date('Y/m/d')), ENT_QUOTES, 'UTF-8');

// 表示モード判定
$_rksp_mode        = 'campaign';  // 'campaign' | 'ichiyen' | 'recommended'
$_rksp_1yen_phones = [];          // campaign モード時も空で初期化（undefined回避）
if (!RAKUTEN_1YEN_CAMPAIGN) {
    $_rksp_1yen_phones = unserialize(RAKUTEN_1YEN_PHONES);
    if (count($_rksp_1yen_phones) > 0) {
        $_rksp_mode = 'ichiyen';     // 段階2: 1円スマホコーナー
    } else {
        $_rksp_mode = 'recommended'; // 段階3: おすすめコーナー
    }
}
$_rksp_recommended   = unserialize(RAKUTEN_RECOMMENDED_PHONES);
$_rksp_we2_search_ja = RAKUTEN_1YEN_SEARCH_JA;

// 国際通話情報（毎日クロール）
$_rki = rakuten_intl_get();
function rki_aruaru(string $key, string $fallback = ''): string {
    global $_rki;
    return htmlspecialchars((string)($_rki[$key] ?? $fallback), ENT_QUOTES, 'UTF-8');
}
$_rki_updated  = htmlspecialchars($_rki['crawled_at'] ?? date('Y/m/d'), ENT_QUOTES, 'UTF-8');
$_rki_success  = !empty($_rki['crawl_success']);
$_rki_cond_ja  = $_rki['conditions_ja'] ?? [];
$_rki_cond_en  = $_rki['conditions_en'] ?? [];
$_rki_price    = rki_aruaru('intl_plan_price_ja', '月980円（税込）');
$_rki_price_en = rki_aruaru('intl_plan_price_en', '980 yen/month (tax included)');
$_rki_count    = rki_aruaru('intl_countries_count', '66');
$_rki_name_ja  = rki_aruaru('intl_plan_name_ja',   '国際通話かけ放題');
$_rki_name_en  = rki_aruaru('intl_plan_name_en',   'International Unlimited Calling');
$_rki_notes_ja = rki_aruaru('notes_ja');
$_rki_notes_en = rki_aruaru('notes_en');

// プラチナバンド・衛星情報（毎日クロール）
$_rkp = rakuten_platinum_get();
function rkp_aruaru(string $key, string $fallback = ''): string {
    global $_rkp;
    return htmlspecialchars((string)($_rkp[$key] ?? $fallback), ENT_QUOTES, 'UTF-8');
}
$_rkp_updated = htmlspecialchars($_rkp['crawled_at'] ?? date('Y/m/d'), ENT_QUOTES, 'UTF-8');
$_rkp_success = !empty($_rkp['crawl_success']);

/* ===== doda 求人クロール（IT・通信／総合広告代理店・Webマーケ・毎日自動クロール）===== */
if (!defined('DODA_CACHE_FILE')) define('DODA_CACHE_FILE', __DIR__ . '/doda-jobs-cache.json');
if (!defined('DODA_CACHE_TTL'))  define('DODA_CACHE_TTL', 86400);
if (!defined('DODA_MAX_ITEMS'))  define('DODA_MAX_ITEMS', 12);
if (!defined('DODA_DETAIL_RE'))  define('DODA_DETAIL_RE', '~https?://doda\.jp/(?:DodaFront/View/JobSearchDetail|jobinfo)/[^\s)"]+~i');
if (!function_exists('doda_categories')) {
    function doda_categories(): array {
        // ★未経験可・転勤無しで絞り込んだ URL を doda 検索画面からコピーして差し替え推奨
        return [
            'it' => ['label' => 'IT・通信業界（未経験可／転勤無し）',
                     'url'   => 'https://doda.jp/DodaFront/View/JobSearchList.action?ss=1&op=17,70,71,27,24&pic=1&ds=0&ind=01L&tp=1&bf=1&mpsc_sid=10&oldestDayWdtno=0&leftPanelType=1'],
            'ad' => ['label' => '総合広告代理店／Webマーケティング（広告代理店・コンサル・制作）（未経験可／転勤無し）',
                     'url'   => 'https://doda.jp/DodaFront/View/JobSearchList.action?ss=1&op=17,70,71,27,24&pic=1&ds=0&ci=131041&ind=1101S,1108S&tp=1&bf=1&mpsc_sid=10&oldestDayWdtno=0&leftPanelType=1'],
        ];
    }
}
if (!function_exists('doda_http_get')) {
    function doda_http_get(string $url, int $timeout = 25): string {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_MAXREDIRS => 5,
                CURLOPT_TIMEOUT => $timeout, CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; AudiocafeBot/1.0; +https://audiocafe.tokyo/)',
                CURLOPT_HTTPHEADER => ['Accept: text/plain, text/markdown, text/html, */*'],
                CURLOPT_SSL_VERIFYPEER => true,
            ]);
            $body = curl_exec($ch); $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
            return ($body !== false && $code >= 200 && $code < 400) ? (string)$body : '';
        }
        $ctx = stream_context_create([
            'http'  => ['timeout' => $timeout, 'user_agent' => 'AudiocafeBot/1.0', 'follow_location' => 1, 'max_redirects' => 5],
            'https' => ['timeout' => $timeout, 'user_agent' => 'AudiocafeBot/1.0', 'follow_location' => 1, 'max_redirects' => 5],
        ]);
        $body = @file_get_contents($url, false, $ctx);
        return $body === false ? '' : (string)$body;
    }
}
if (!function_exists('doda_crawl_category')) {
    function doda_crawl_category(string $url, int $max): array {
        $md = doda_http_get('https://r.jina.ai/' . $url);   // 既存方針に合わせ jina.ai 経由
        if ($md === '') $md = doda_http_get($url);          // フォールバック（生HTML）
        if ($md === '') return [];

        $items = []; $seen = [];

        // doda の「絞り込み結果」の求人カードは画像付きリンク
        //   [![Image N: 求人タイトル](画像URL)](求人詳細URL)
        // の形式。一方、サイト共通の「おすすめ求人（ランキング）」はテキストリンクで
        // 全カテゴリ同一内容（URL に recommendID / searchResultFooterAddedArea を含む）。
        // 以前はこのテキストリンクだけを拾っていたため、IT と 広告 で同じ求人＝IT寄りの
        // おすすめ求人が表示されていた。ここでは検索結果カードを優先し、おすすめ求人を除外する。
        $is_recommend = static function (string $u): bool {
            return (bool)preg_match('~recommendID=|searchResultFooterAddedArea|_recommend(?:$|[?&/])~i', $u);
        };
        $push = static function (string $title, string $u) use (&$items, &$seen, $max, $is_recommend): bool {
            if (count($items) >= $max) return false;                 // 上限到達 → 終了
            $title = trim(preg_replace('~\s+~u', ' ', $title));
            $title = trim(preg_replace('~^!?\[?\s*Image\s*\d+\s*[:：]\s*~u', '', $title)); // jina の "Image N:" 接頭辞を除去
            $u     = html_entity_decode($u, ENT_QUOTES, 'UTF-8');
            if ($title === '' || mb_strlen($title) < 4) return true; // スキップして次へ
            if (!preg_match(DODA_DETAIL_RE, $u))         return true;
            if ($is_recommend($u))                        return true; // 全カテゴリ共通のおすすめ求人を除外
            if (isset($seen[$u]))                         return true;
            $seen[$u] = true;
            $items[] = ['title' => $title, 'url' => $u];
            return count($items) < $max;
        };

        // (1) 画像付きカード＝そのカテゴリの実際の絞り込み結果を優先抽出
        if (preg_match_all('~\[!\[([^\]]{4,200})\]\([^)]*\)\]\((https?://doda\.jp/[^)\s]+)\)~u', $md, $mm, PREG_SET_ORDER)) {
            foreach ($mm as $row) { if (!$push($row[1], $row[2])) break; }
        }
        // (2) 件数が不足する場合のみ、テキストリンクで補完（おすすめ求人は上で除外済み）
        if (count($items) < $max && preg_match_all('~\[([^\]!][^\]]{3,160})\]\((https?://doda\.jp/[^)\s]+)\)~u', $md, $mm, PREG_SET_ORDER)) {
            foreach ($mm as $row) { if (!$push($row[1], $row[2])) break; }
        }

        return $items;
    }
}
if (!function_exists('doda_run_crawl')) {
    function doda_run_crawl(): array {
        $out = ['updated' => date('c'), 'updated_human' => date('Y-m-d H:i'), 'crawled_at' => date('c'), 'categories' => []];
        foreach (doda_categories() as $key => $cat) {
            $items = doda_crawl_category($cat['url'], DODA_MAX_ITEMS);
            $out['categories'][$key] = ['label' => $cat['label'], 'search' => $cat['url'], 'count' => count($items), 'items' => $items];
            usleep(600000);
        }
        // 0件カテゴリは前回値を温存（失敗で空表示にしない）
        if (is_file(DODA_CACHE_FILE)) {
            $prev = json_decode((string)@file_get_contents(DODA_CACHE_FILE), true);
            if (is_array($prev) && !empty($prev['categories'])) {
                foreach ($out['categories'] as $k => $c) {
                    if ($c['count'] === 0 && !empty($prev['categories'][$k]['items'])) {
                        $out['categories'][$k]['items'] = $prev['categories'][$k]['items'];
                        $out['categories'][$k]['count'] = count($prev['categories'][$k]['items']);
                        $out['categories'][$k]['stale'] = true;
                    }
                }
            }
        }
        $tmp = DODA_CACHE_FILE . '.tmp';
        @file_put_contents($tmp, json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT), LOCK_EX);
        @rename($tmp, DODA_CACHE_FILE);
        return $out;
    }
}
if (!function_exists('doda_get')) {
    // 表示用：キャッシュを読む。期限切れ/未生成かつ非cron時のみ遅延クロール（ロックで多重起動防止）
    function doda_get(): array {
        $f = DODA_CACHE_FILE;
        $fresh = is_readable($f) && (time() - (int)@filemtime($f)) < DODA_CACHE_TTL;
        $is_cron = function_exists('aruaru_is_cron_request') && aruaru_is_cron_request();
        if (!$fresh && !$is_cron) {
            $lk = @fopen($f . '.lock', 'c');
            if ($lk && flock($lk, LOCK_EX | LOCK_NB)) {
                @set_time_limit(60);
                $d = doda_run_crawl();
                flock($lk, LOCK_UN); fclose($lk);
                return $d;
            }
            if ($lk) fclose($lk);
        }
        $c = json_decode((string)@file_get_contents($f), true);
        return is_array($c) ? $c : ['updated_human' => '', 'categories' => []];
    }
}
$_doda = doda_get();
?>

<section class="hero">
  <div class="container-xl px-3">
    <h1 class="hero-h1">スキルと希望条件から<br>あなたにぴったりの案件が見つかる。</h1>
    <p class="hero-sub">言語・フレームワーク・月額・勤務地で絞り込み。マッチした案件の外部サイトへ直接応募 ＋ 似た求人が見つかる外部サービスもご紹介。</p>
    <div class="d-flex flex-wrap justify-content-center gap-3 mt-4" style="row-gap:.75rem">
      <div class="text-center"><span class="hero-stat-val"><?= count(ANKEN) ?></span><span class="hero-stat-lbl d-block">掲載案件</span></div>
      <div class="text-center"><span class="hero-stat-val"><?= count(EXT_SITES) ?></span><span class="hero-stat-lbl d-block">外部求人サイト</span></div>
      <div class="text-center"><span class="hero-stat-val">80%</span><span class="hero-stat-lbl d-block">リモート対応</span></div>
      <div class="text-center"><span class="hero-stat-val">¥60〜130万</span><span class="hero-stat-lbl d-block">月額レンジ</span></div>
    </div>
  </div>
</section>

<?php
/* ===== 統一更新バッジ用タイムスタンプ（毎日クロールの最新時刻を採用） ===== */
$_aru_badge_ts = max(
    (int)strtotime((string)($_rki['crawled_at'] ?? '')),
    (int)strtotime((string)($_rkp['crawled_at'] ?? '')),
    (int)@filemtime(RAKUTEN_INTL_CACHE_FILE),
    (int)@filemtime(RAKUTEN_PLATINUM_CACHE_FILE)
);
if ($_aru_badge_ts <= 0) $_aru_badge_ts = time();
?>
<style>
/* ===== 統一更新バッジ（左右二か所→上部一か所に統合・縦横スマホ対応） ===== */
#aruaru-update-badge{
  position:fixed;
  top:var(--aruaru-gadget-top, calc(env(safe-area-inset-top,0px) + 62px));
  left:calc(env(safe-area-inset-left,0px) + 10px);
  right:auto;
  /* 右上の OPEN ボタン／翻訳パネルと重ならない様に最大幅を確保 */
  max-width:calc(100vw - env(safe-area-inset-left,0px) - env(safe-area-inset-right,0px) - 96px);
  z-index:2147483645;
  pointer-events:none;
  display:inline-flex; align-items:center;
  height:1.65rem; line-height:1.65rem;
  padding:0 .7rem;
  border-radius:999px;
  background:rgba(15,23,42,.95);
  border:1px solid rgba(34,211,238,.55);
  color:#7dd3fc;
  font:700 clamp(.58rem,2.6vw,.82rem)/1.65rem 'Noto Sans JP',system-ui,sans-serif;
  white-space:nowrap;
  overflow:hidden;
  box-shadow:0 2px 10px rgba(0,0,0,.45);
  -webkit-backdrop-filter:blur(6px); backdrop-filter:blur(6px);
}
#aruaru-update-badge .u-dt   { color:#a5f3fc; }  /* 日時 */
#aruaru-update-badge .u-ok   { color:#86efac; }  /* 更新済み(UPDATE) */
#aruaru-update-badge .u-daily{ color:#c4b5fd; }  /* 毎日05:00に更新中 */
@media (orientation:landscape) and (max-height:480px){
  #aruaru-update-badge{
    height:1.45rem; line-height:1.45rem; padding:0 .55rem;
    font-size:clamp(.55rem,1.8vw,.74rem);
    max-width:calc(100vw - env(safe-area-inset-left,0px) - env(safe-area-inset-right,0px) - 84px);
  }
}
@media (max-width:560px){
  #aruaru-update-badge{ padding:0 .5rem; }
  #aruaru-update-badge .u-daily{ display:none; }   /* 「（毎日05:00に更新中）」を省略 */
}
@media (max-width:430px){
  #aruaru-update-badge .u-ok .u-ok-en{ display:none; } /* 「（UPDATE）」を省略（「更新済み」は残す） */
}
</style>
<div id="aruaru-update-badge" class="notranslate" translate="no" aria-label="最終更新日時">📅&thinsp;<span class="u-dt"><?= h(aru_update_label($_aru_badge_ts, false)) ?></span><span class="u-ok">&thinsp;更新済み<span class="u-ok-en">（UPDATE）</span></span><span class="u-daily">（毎日05:00に更新中）</span></div>

<!-- ===== 楽天モバイル コーナー（/rakuten-mobile 全文・初期は CLOSE＝OPEN のみ表示） ===== -->
<?php
if (!defined('RAKUTEN_MOBILE_LIBRARY')) define('RAKUTEN_MOBILE_LIBRARY', true);
require_once __DIR__ . '/../rakuten-mobile/index.php';
rm_render_embed_panel('aruaru', __DIR__);
?>

<!-- ===== doda 求人ピックアップ（未経験可／転勤無し・毎日自動クロール） ===== -->
<section class="pb-2" id="doda-jobs" style="padding-top:1rem;">
  <div class="container-xl px-3">
    <h2 style="color:#fff;font-size:18px;margin:0 0 4px;">💼 転職求人ピックアップ（doda）</h2>
    <p style="color:#94a3b8;font-size:13px;margin:0 0 12px;">未経験可・転勤無しの条件で毎日自動更新<?php if (!empty($_doda['updated_human'])) echo ' ／ 📅 ' . h($_doda['updated_human']); ?></p>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:14px;">
      <?php
      $doda_cats  = (is_array($_doda) && !empty($_doda['categories'])) ? $_doda['categories'] : [];
      $doda_fb    = doda_categories();
      foreach (['it','ad'] as $dk):
          $dc      = $doda_cats[$dk] ?? null;
          $dlabel  = $dc['label']  ?? ($doda_fb[$dk]['label'] ?? '');
          $dsearch = $dc['search'] ?? ($doda_fb[$dk]['url']   ?? '#');
          $ditems  = $dc['items']  ?? [];
          $dstale  = !empty($dc['stale']);
      ?>
      <div style="padding:16px 18px;border-radius:14px;background:rgba(2,6,23,.85);border:1px solid rgba(59,130,246,.4);">
        <h3 style="color:#fff;margin:0 0 8px;font-size:15px;line-height:1.45;"><?= h($dlabel) ?><?php if ($dstale) echo ' <span style="color:#b07a00;font-size:11px;">※前回取得分</span>'; ?></h3>
        <?php if (!empty($ditems)): ?>
          <ul style="list-style:none;margin:0 0 10px;padding:0;">
            <?php foreach ($ditems as $j): ?>
              <li style="border-bottom:1px solid rgba(148,163,184,.18);"><a href="<?= h($j['url']) ?>" target="_blank" rel="noopener nofollow" style="display:block;padding:7px 2px;color:#7dd3fc;text-decoration:none;font-size:13.5px;line-height:1.5;"><?= h($j['title']) ?></a></li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p style="color:#94a3b8;font-size:13px;margin:0 0 10px;">現在取得できる求人がありません。下記から最新一覧をご確認ください。</p>
        <?php endif; ?>
        <a href="<?= h($dsearch) ?>" target="_blank" rel="noopener nofollow" style="display:block;width:100%;box-sizing:border-box;text-align:center;background:#d12d36;color:#fff;border-radius:8px;padding:9px 14px;margin-top:6px;font-size:13px;line-height:1.4;text-decoration:none;white-space:normal;word-break:break-word;">doda で最新の求人一覧を見る ▶</a>
      </div>
      <?php endforeach; ?>
    </div>

    <?php
    $DODA_LANG_FW = [
      'JavaScript / TypeScript' => ['React','Vue.js','Angular','Svelte','Next.js','Nuxt.js','Node.js','Express','NestJS','Remix','Astro','jQuery'],
      'Python'                  => ['Django','Flask','FastAPI','Pyramid','Tornado','Bottle','Sanic','Starlette','Streamlit','Celery'],
      'Java'                    => ['Spring Boot','Spring MVC','Spring Framework','Jakarta EE','Quarkus','Micronaut','Struts','Play Framework','Vert.x','MyBatis'],
      'PHP'                     => ['Laravel','Symfony','CakePHP','CodeIgniter','Slim','Yii','Phalcon','Laminas (Zend)','FuelPHP','WordPress'],
      'Ruby'                    => ['Ruby on Rails','Sinatra','Hanami','Padrino','Grape','Roda'],
      'Go'                      => ['Gin','Echo','Fiber','Beego','Chi','Revel','Gorilla','Buffalo'],
      'Rust'                    => ['Actix-web','Axum','Rocket','warp','Poem','Tide','Salvo','Loco'],
      'C# / .NET'               => ['ASP.NET Core','Blazor','Entity Framework Core','.NET MAUI','WPF','WinForms','Razor Pages','SignalR'],
      'Kotlin'                  => ['Spring Boot','Ktor','Android Jetpack','Jetpack Compose','Exposed'],
      'Swift'                   => ['SwiftUI','UIKit','Vapor','Combine','Core Data'],
      'Scala'                   => ['Play Framework','Akka','http4s','Lift','Cats'],
      'C / C++'                 => ['Qt','Boost','POCO','OpenCV','gRPC'],
      'Dart'                    => ['Flutter'],
      'Elixir'                  => ['Phoenix','Ecto'],
    ];
    ?>
    <div style="margin-top:16px;padding:16px 18px;border-radius:14px;background:rgba(2,6,23,.6);border:1px solid rgba(148,163,184,.25);">
      <h3 style="color:#a5f3fc;margin:0 0 12px;font-size:15px;">案件で頻出するプログラミング言語・フレームワーク</h3>
      <?php foreach ($DODA_LANG_FW as $dlang => $dfws): ?>
        <div style="display:flex;flex-wrap:wrap;gap:6px;align-items:baseline;padding:6px 0;border-bottom:1px solid rgba(148,163,184,.12);">
          <span style="flex:0 0 170px;color:#fff;font-weight:700;font-size:13px;"><?= h($dlang) ?></span>
          <span style="display:flex;flex-wrap:wrap;gap:6px;">
            <?php foreach ($dfws as $dfw): ?>
              <span style="font-size:12px;border-radius:13px;padding:2px 9px;background:rgba(148,163,184,.16);border:1px solid rgba(148,163,184,.3);color:#cbd5e1;"><?= h($dfw) ?></span>
            <?php endforeach; ?>
          </span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
 
<!-- SEARCH PANEL -->
<section class="pb-3" id="search">
  <div class="container-xl px-3">
    <form method="GET" action="" class="search-panel" id="search-form">
 
      <!-- Row 1: keyword / role / style -->
      <div class="row g-2 mb-2">
        <div class="col-12 col-md-5">
          <label class="f-label" for="q">キーワード</label>
          <input class="form-control" id="q" name="q" type="search"
                 placeholder="例：React、フルスタック、AI、Go"
                 value="<?= h($q) ?>">
        </div>
        <div class="col-6 col-md-4">
          <label class="f-label" for="role">職種カテゴリ</label>
          <select class="form-select" id="role" name="role">
            <option value="">すべての職種</option>
            <?php foreach (['フロントエンド','バックエンド','フルスタック','モバイル','インフラ／SRE','データ／AI・ML','PM／PMO'] as $r): ?>
              <option value="<?= h($r) ?>"<?= sel($r, $role) ?>><?= h($r) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-6 col-md-3">
          <label class="f-label" for="style">働き方</label>
          <select class="form-select" id="style" name="style">
            <option value="">すべて</option>
            <option value="remote"<?= sel('remote',$style) ?>>フルリモート</option>
            <option value="hybrid"<?= sel('hybrid',$style) ?>>ハイブリッド</option>
            <option value="onsite"<?= sel('onsite',$style) ?>>常駐</option>
          </select>
        </div>
      </div>
 
      <!-- Row 2: Language chips（AI順） -->
      <div class="mb-2">
        <label class="f-label">
          希望プログラミング言語
          <span style="color:#dde6f5;font-weight:500;text-transform:none;letter-spacing:0">（複数選択可・選択するとFWが絞り込まれます）</span>
          <?php if (!empty($ai_trends['updated_at'])): ?>
            <span style="font-size:.84rem;color:var(--primary-lt);font-weight:600;margin-left:.4rem;letter-spacing:0;text-transform:none">
              ★ AI需要順 更新:<?= h($ai_trends['updated_at']) ?>
            </span>
          <?php endif; ?>
        </label>
        <div class="chip-wrap open" id="lang-chips">
          <?php foreach ($langs_ordered as $lang): ?>
            <?php if (!in_array($lang, LANGS, true)) continue; ?>
            <?php $is_hot = in_array($lang, $ai_trends['hot_langs'] ?? [], true); ?>
            <span class="chip<?= in_arr($lang,$langs_in)?' on':'' ?>"
                  data-val="<?= h($lang) ?>" data-group="langs"
                  onclick="toggleLangChip(this)"
                  title="<?= $is_hot ? '🔥 急上昇中' : '' ?>"
                  style="<?= $is_hot ? 'border-color:rgba(249,115,22,.7);color:#fed7aa' : '' ?>"
                  ><?= h($lang) ?><?= $is_hot ? ' 🔥' : '' ?></span>
          <?php endforeach; ?>
        </div>
      </div>
 
      <!-- Row 3: Framework chips（言語選択で絞り込み） -->
      <div class="mb-3" id="fw-section">
        <label class="f-label" id="fw-label">
          希望フレームワーク・ツール
          <span id="fw-hint" style="color:#dde6f5;font-weight:500;text-transform:none;letter-spacing:0">
            （言語を選ぶと関連FWが表示されます）
          </span>
        </label>
        <!-- 言語未選択時のプレースホルダー -->
        <div id="fw-placeholder" style="padding:.55rem .8rem;border-radius:9px;border:1px dashed rgba(255,255,255,.3);color:#fff;font-size:.88rem">
          ↑ まず言語を選択すると、関連フレームワーク・ツールが表示されます
        </div>
        <!-- 言語選択後に表示されるFWチップ群 -->
        <div class="chip-wrap open" id="fw-chips" style="display:none">
          <?php
          /* 全FWをフラットに出力。data-langs属性で関連言語リストを持たせる */
          $fw_to_langs = [];
          foreach (LANG_FW_MAP as $lang => $fwList) {
              foreach ($fwList as $fw) {
                  $fw_to_langs[$fw][] = $lang;
              }
          }
          /* カテゴリ順に出力 */
          foreach (FRAMEWORKS as $cat => $fwList):
              $hasVisible = false;
              ob_start();
              foreach ($fwList as $fw):
                  $rel_langs = $fw_to_langs[$fw] ?? [];
                  $data_langs = h(implode(',', $rel_langs));
                  $on_class = in_arr($fw,$fws_in) ? ' on' : '';
                  echo '<span class="chip' . $on_class . '"'
                      . ' data-val="' . h($fw) . '"'
                      . ' data-group="fws"'
                      . ' data-langs="' . $data_langs . '"'
                      . ' onclick="toggleChip(this)"'
                      . ' style="display:none">'
                      . h($fw) . '</span>';
              endforeach;
              $chunk = ob_get_clean();
              echo '<span class="chip chip-cat fw-cat-label" data-cat="' . h($cat) . '" style="display:none">' . h($cat) . '</span>';
              echo $chunk;
          endforeach;
          ?>
        </div>
        <span class="chip-more" id="fw-more" style="display:none" onclick="toggleWrap('fw-chips',this)">▼ すべて表示</span>
      </div>
 
      <!-- Hidden inputs for selected langs/fws -->
      <div id="hidden-chip-inputs">
        <?php foreach ($langs_in as $l): ?>
          <input type="hidden" name="langs[]" id="<?= h(hci_chip_id('langs', $l)) ?>" value="<?= h($l) ?>">
        <?php endforeach; ?>
        <?php foreach ($fws_in as $f): ?>
          <input type="hidden" name="fws[]" id="<?= h(hci_chip_id('fws', $f)) ?>" value="<?= h($f) ?>">
        <?php endforeach; ?>
      </div>
 
      <!-- Row 4: Rate / Annual -->
      <div class="row g-2 mb-2">
        <div class="col-12 col-md-7">
          <label class="f-label" for="rate-slider">希望月額（下限）</label>
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <input type="range" class="form-range flex-grow-1" id="rate-slider"
                   min="0" max="240" step="5" value="<?= min($min_rate,240) ?>"
                   style="min-width:120px" oninput="syncRate(this.value)">
            <input type="number" class="form-control text-center" id="rate-num" name="min_rate"
                   min="0" max="240" step="5" value="<?= $min_rate ?>"
                   style="width:80px;flex-shrink:0" oninput="syncRateFromNum(this.value)">
            <span style="font-size:.88rem;white-space:nowrap">万円/月〜</span>
          </div>
          <div class="d-flex justify-content-between mt-1" style="font-size:.84rem;color:#dde6f5">
            <span>0（下限なし）</span><span>60万</span><span>120万</span><span>180万</span><span>240万</span>
          </div>
        </div>
        <div class="col-12 col-md-5">
          <label class="f-label" for="annual-num">希望年収（下限・月額と連動）</label>
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <input type="number" class="form-control" id="annual-num"
                   min="0" max="999999" step="12" value="<?= $min_rate * 12 ?>"
                   placeholder="0" oninput="syncFromAnnual(this.value)"
                   style="flex:1;min-width:100px">
            <span style="font-size:.88rem;white-space:nowrap">万円/年〜</span>
          </div>
          <div style="font-size:.84rem;color:#dde6f5;margin-top:.3rem">月額 × 12 で換算（直接入力も可・数億円まで対応）</div>
          <!-- 年収プリセット -->
          <div class="d-flex flex-wrap gap-1 mt-1" id="annual-presets">
            <button type="button" class="chip" style="font-size:.84rem;padding:.15rem .45rem" onclick="setAnnual(0)">指定なし</button>
            <button type="button" class="chip" style="font-size:.84rem;padding:.15rem .45rem" onclick="setAnnual(500)">500万〜</button>
            <button type="button" class="chip" style="font-size:.84rem;padding:.15rem .45rem" onclick="setAnnual(800)">800万〜</button>
            <button type="button" class="chip" style="font-size:.84rem;padding:.15rem .45rem" onclick="setAnnual(1000)">1,000万〜</button>
            <button type="button" class="chip" style="font-size:.84rem;padding:.15rem .45rem" onclick="setAnnual(1500)">1,500万〜</button>
            <button type="button" class="chip" style="font-size:.84rem;padding:.15rem .45rem" onclick="setAnnual(2000)">2,000万〜</button>
            <button type="button" class="chip" style="font-size:.84rem;padding:.15rem .45rem" onclick="setAnnual(5000)">5,000万〜</button>
            <button type="button" class="chip" style="font-size:.84rem;padding:.15rem .45rem" onclick="setAnnual(10000)">1億〜</button>
            <button type="button" class="chip" style="font-size:.84rem;padding:.15rem .45rem" onclick="setAnnual(30000)">3億〜</button>
          </div>
        </div>
      </div>
 
      <!-- Row 5: Location -->
      <div class="row g-2 mb-3 align-items-end">
        <div class="col-12 col-sm-4 col-md-3">
          <label class="f-label" for="pref">勤務地</label>
          <select class="form-select" id="pref" name="pref">
            <?php
            /* グループ区切りを optgroup で表示 */
            $group_headers = [
              '北海道全域'  => ['optgroup_start'=>'━━ 北海道・東北'],
              '東京都全域'  => ['optgroup_start'=>'━━ 首都圏'],
              '新潟県'      => ['optgroup_start'=>'━━ 北陸・甲信越'],
              '愛知県全域'  => ['optgroup_start'=>'━━ 東海'],
              '関西全域'    => ['optgroup_start'=>'━━ 関西'],
              '中国全域'    => ['optgroup_start'=>'━━ 中国・四国'],
              '九州全域'    => ['optgroup_start'=>'━━ 九州・沖縄'],
            ];
            $prev_group = null;
            foreach (LOCATION_GROUPS as $g):
                if (isset($group_headers[$g['value']])) {
                    if ($prev_group !== null) echo '</optgroup>';
                    echo '<optgroup label="' . htmlspecialchars($group_headers[$g['value']]['optgroup_start'],ENT_QUOTES,'UTF-8') . '">';
                    $prev_group = $g['value'];
                }
            ?>
              <option value="<?= h($g['value']) ?>"<?= sel($g['value'],$pref) ?>><?= h($g['label']) ?></option>
            <?php endforeach; ?>
            <?php if ($prev_group !== null) echo '</optgroup>'; ?>
          </select>
        </div>
        <div class="col-12 col-sm-4 col-md-3">
          <label class="f-label" for="city">市区町村</label>
          <input class="form-control" id="city" name="city" type="text"
                 placeholder="例：渋谷区" value="<?= h($city) ?>">
        </div>
        <div class="col-12 col-sm-4 col-md-3">
          <label class="f-label" for="station">最寄り駅</label>
          <input class="form-control" id="station" name="station" type="text"
                 placeholder="例：渋谷、梅田" value="<?= h($station) ?>">
        </div>
        <div class="col-12 col-md-3 d-flex align-items-center" style="padding-top:1.6rem">
          <div class="form-check m-0">
            <input class="form-check-input" type="checkbox" id="remote-ok"
                   name="remote_ok" value="1"<?= chk($remote_ok) ?>>
            <label class="form-check-label" for="remote-ok">リモート・ハイブリッドのみ</label>
          </div>
        </div>
      </div>
 
      <!-- Buttons -->
      <div class="row g-2">
        <div class="col-8 col-sm-9 col-md-10">
          <button type="button" class="btn-search" id="search-submit-btn">マッチする案件を探す</button>
        </div>
        <div class="col-4 col-sm-3 col-md-2">
          <a href="?" class="btn-reset">リセット</a>
        </div>
      </div>
 
    </form>
  </div>
</section>
 
<!-- RESULTS -->
<main class="py-3" id="results">
  <div class="container-xl px-3">
 
    <!-- ══ GOOGLE検索マッチング結果（メイン） ══════════════════ -->
    <?php
    $cp = cache_path('gse_v4_' . md5($search_query . "\0" . implode("\0", $google_fallback_queries)));
    $is_cached = !empty($google_results) && $cp !== '' && file_exists($cp);
    $cache_label = $is_cached ? '（キャッシュ中・最大7日）' : '（最新取得）';
    ?>
 
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
      <h2 style="font-size:1.08rem;font-weight:800;color:#fff;margin:0">
        🔍 マッチング結果
        <?php if (!empty($google_results)): ?>
          <span style="font-size:.88rem;color:#fff;font-weight:600">（<?= count($google_results) ?>件）</span>
          <span style="font-size:.84rem;color:var(--primary-lt);font-weight:600;margin-left:.3rem"><?= $cache_label ?></span>
        <?php elseif (GOOGLE_CSE_KEY !== ''): ?>
          <span style="font-size:.88rem;color:#fff;font-weight:600">（0件）</span>
        <?php endif; ?>
      </h2>
      <?php if (!empty($google_results)): ?>
        <span style="font-size:.84rem;color:#dde6f5">
          検索: 「<?= h(mb_strimwidth(urldecode($search_query), 0, 40, '…')) ?>」
        </span>
      <?php endif; ?>
    </div>
 
    <?php if (!empty($google_results)): ?>
      <!-- Google検索結果カード -->
      <div class="row g-3" id="google-cards">
        <?php foreach ($google_results as $i => $gr): ?>
          <?php $gr_fb = !empty($gr['is_fallback']); ?>
          <div class="col-12 col-md-6 col-lg-4">
            <div class="card-anken">
              <!-- ソース表示 -->
              <div class="d-flex align-items-center justify-content-between mb-2" style="gap:.4rem">
                <div style="display:flex;align-items:center;gap:.35rem;overflow:hidden">
                  <?php if ($gr_fb): ?>
                  <span style="font-size:.84rem;font-weight:800;color:#c4b5fd;letter-spacing:.06em;background:rgba(167,139,250,.15);padding:.1rem .42rem;border-radius:4px;flex-shrink:0">求人サイト</span>
                  <?php else: ?>
                  <span style="font-size:.84rem;font-weight:800;color:var(--primary-lt);letter-spacing:.06em;background:var(--primary-glow);padding:.1rem .42rem;border-radius:4px;flex-shrink:0">Google</span>
                  <?php endif; ?>
                  <span style="font-size:.84rem;color:#dde6f5;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= h($gr['domain']) ?></span>
                </div>
                <span style="font-size:.84rem;color:#dde6f5;flex-shrink:0">#<?= $i + 1 ?></span>
              </div>
              <!-- タイトル（クリックで詳細へ） -->
              <a href="<?= h($gr['url']) ?>"
                 <?php if (!empty($gr['daijob_kind']) && $gr['daijob_kind'] === 'en'): ?>data-no-translate="1"<?php endif; ?>
                 target="_blank" rel="noopener noreferrer" style="text-decoration:none">
                <div class="card-title mb-2" style="color:#fff;transition:color .15s"
                     onmouseover="this.style.color='var(--primary-lt)'"
                     onmouseout="this.style.color='#fff'">
                  <?= h(mb_strimwidth($gr['title'], 0, 60, '…')) ?>
                </div>
              </a>
              <!-- スニペット -->
              <div style="font-size:.88rem;color:#dde6f5;line-height:1.65;margin-bottom:.9rem">
                <?= h(mb_strimwidth($gr['snippet'], 0, 130, '…')) ?>
              </div>
              <!-- 詳細・応募ボタン -->
              <div class="mt-auto">
                <a href="<?= h($gr['url']) ?>"
                   <?php if (!empty($gr['daijob_kind']) && $gr['daijob_kind'] === 'en'): ?>data-no-translate="1"<?php endif; ?>
                   target="_blank" rel="noopener noreferrer"
                   class="btn-apply notranslate" translate="no" style="width:100%;justify-content:center">
                  詳細・応募はこちら
                  <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <div style="font-size:.84rem;color:#dde6f5;margin-top:.7rem;text-align:right">
        <?php if (!empty($google_results[0]['is_fallback'])): ?>
          条件に合わせた外部求人・案件サイトの検索結果ページへリンクしています。詳細・応募は各サイトでご確認ください。
        <?php else: ?>
          Powered by Google Custom Search API · 結果は最大7日間キャッシュ（条件変更で再取得）· 各リンク先サイトで詳細・応募をご確認ください
        <?php endif; ?>
      </div>
 
    <?php elseif (GOOGLE_CSE_KEY === ''): ?>
      <!-- APIキー未設定の案内 -->
      <div class="empty-state" style="border-style:dashed;text-align:left;padding:1.2rem 1.4rem">
        <strong style="font-size:1rem;display:block;margin-bottom:.5rem">🔍 Google検索マッチングを有効にしてください</strong>
        <p style="font-size:.88rem;line-height:1.8;margin:0">
          このエリアは <strong>Google Custom Search API</strong> のキーを設定すると、検索条件に合った実際の案件・求人情報をGoogleから取得して一覧表示します。各カードのリンクから詳細ページへ直接移動できます。<br><br>
          <strong style="color:var(--primary-lt)">設定方法：</strong> index.php 先頭の API設定欄に以下を入力。<br>
          <code style="color:var(--primary-lt);font-size:.88rem">GOOGLE_CSE_KEY</code> →
          <a href="https://console.cloud.google.com/" target="_blank" rel="noopener" style="color:var(--primary-lt)">Google Cloud Console</a><br>
          <code style="color:var(--primary-lt);font-size:.88rem">GOOGLE_CSE_CX</code> →
          <a href="https://programmablesearchengine.google.com/" target="_blank" rel="noopener" style="color:var(--primary-lt)">Programmable Search Engine</a><br><br>
          <span style="color:#dde6f5">無料枠：1日100クエリ ／ 結果は7日間キャッシュするのでAPIを節約できます。</span>
        </p>
      </div>
    <?php else: ?>
      <div class="empty-state"><strong>検索結果が見つかりませんでした</strong>キーワードや条件を変えてもう一度お試しください。</div>
    <?php endif; ?>
 
    <!-- ══ 参考案件（サンプルデータ） ══════════════════════════ -->
    <?php if (!empty($result)): ?>
    <div class="mt-4">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
        <h3 style="font-size:.92rem;font-weight:800;color:#dde6f5;margin:0">
          📋 参考案件（サンプルデータ）
          <span style="font-size:.88rem;font-weight:600;color:#dde6f5">（<?= count($result) ?>件）</span>
        </h3>
        <form method="GET" action="" id="sort-form">
          <?php
          $pass = ['q'=>$q,'role'=>$role,'style'=>$style,'min_rate'=>$min_rate,'pref'=>$pref,'city'=>$city,'station'=>$station];
          foreach ($pass as $k=>$v) { if ($v !== '' && $v !== 0): ?>
            <input type="hidden" name="<?= h($k) ?>" value="<?= h((string)$v) ?>">
          <?php endif; } ?>
          <?php foreach ($langs_in as $l): ?><input type="hidden" name="langs[]" value="<?= h($l) ?>"><?php endforeach; ?>
          <?php foreach ($fws_in   as $f): ?><input type="hidden" name="fws[]"   value="<?= h($f) ?>"><?php endforeach; ?>
          <?php if ($remote_ok): ?><input type="hidden" name="remote_ok" value="1"><?php endif; ?>
          <select class="sort-sel" name="sort" onchange="this.form.submit()">
            <option value="new"       <?= sel('new',      $sort_by) ?>>新着順</option>
            <option value="rate-desc" <?= sel('rate-desc',$sort_by) ?>>月額が高い順</option>
            <option value="rate-asc"  <?= sel('rate-asc', $sort_by) ?>>月額が低い順</option>
            <option value="score"     <?= sel('score',    $sort_by) ?>>マッチ度順</option>
          </select>
        </form>
      </div>
      <div class="row g-3">
        <?php foreach ($result as $a): ?>
          <div class="col-12 col-md-6 col-lg-4">
            <div class="card-anken">
              <div class="d-flex justify-content-between align-items-start mb-1" style="gap:.35rem">
                <div>
                  <?= $a['posted'] <= 1 ? '<span class="bdg bdg-new">NEW</span>' : '' ?>
                  <?= $a['hot'] ? '<span class="bdg bdg-hot">注目</span>' : '' ?>
                  <?= style_badge($a['style']) ?>
                </div>
                <span style="font-size:.84rem;color:#dde6f5;flex-shrink:0"><?= posted_label($a['posted']) ?></span>
              </div>
              <div class="card-title mb-2"><?= h($a['title']) ?></div>
              <div class="card-meta mb-2">
                <span>📍 <?= h($a['location']) ?></span>
                <span>⏱ <?= h($a['duration']) ?></span>
                <span style="color:#dde6f5"><?= h($a['role']) ?></span>
              </div>
              <div class="d-flex flex-wrap gap-1 mb-2">
                <?php foreach ($a['langs'] as $l): ?><span class="stag stag-lang"><?= h($l) ?></span><?php endforeach; ?>
                <?php foreach ($a['fws']   as $f): ?><span class="stag stag-fw"><?= h($f) ?></span><?php endforeach; ?>
              </div>
              <div class="mt-auto">
                <div class="mb-2">
                  <span class="card-rate">¥<?= $a['rate'] ?><small> 万円/月</small></span>
                  <span class="card-annual">年収 約¥<?= $a['rate'] * 12 ?>万</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                  <span class="site-badge"><?= h($a['site_name']) ?></span>
                  <a href="<?= h($a['apply_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn-apply notranslate" translate="no">
                    このサイトで応募
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                  </a>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
 
    <!-- EXTERNAL SITES -->
    <div class="mt-3" id="ext">
      <div class="ext-section">
        <div class="ext-title mb-1">🔍 この条件でもっと探す — 外部求人・案件サイト</div>
        <p class="ext-sub mb-3">
          <?php
          $kw_label_parts = array_values(array_unique(array_filter(array_merge(
              $langs_in,
              $fws_in,
              $q !== '' ? array_filter(array_map('trim', preg_split('/[\s+]+/', $q))) : [],
          ))));
          echo $kw_label_parts
              ? '「' . h(implode(' / ', array_slice($kw_label_parts, 0, 4))) . '」に関連する案件を外部サービスでも探せます。'
              : '現在の検索条件に関連する案件を外部サービスでも探せます。';
          ?>
          各サイトの検索結果ページへ直接リンクします（別タブ）。
        </p>
        <div class="row g-2">
          <?php foreach (EXT_SITES as $site): ?>
            <div class="col-6 col-md-4 col-lg-3 col-xxl-2">
              <a href="<?php
                if (!empty($site['levtech'])) {
                    echo h(build_levtech_url($langs_in, $fws_in, $q));
                } elseif (!empty($site['fls'])) {
                    echo h(build_fls_url($langs_in, $fws_in, $q));
                } elseif (!empty($site['fixed_url'])) {
                    echo h($site['url']);
                } elseif (!empty($site['geechs'])) {
                    echo h(build_geechs_url($langs_in, $fws_in, $q));
                } elseif (!empty($site['r_agent'])) {
                    echo h(build_r_agent_url($langs_in, $q));
                } elseif (!empty($site['itanken'])) {
                    echo h(build_itanken_url($langs_in, $fws_in, $q, $role));
                } elseif (!empty($site['crowdtech'])) {
                    echo h(build_crowdtech_url($langs_in, $fws_in, $q));
                } elseif (!empty($site['midworks'])) {
                    echo h(build_midworks_url($langs_in, $fws_in, $q));
                } elseif (!empty($site['findy_fl'])) {
                    echo h(build_findy_fl_url($langs_in, $fws_in, $q));
                } elseif (!empty($site['findy_career'])) {
                    echo h(build_findy_career_url($langs_in, $fws_in, $q));
                } elseif (!empty($site['offers'])) {
                    echo h(build_offers_url($langs_in, $fws_in, $q));
                } elseif (!empty($site['green'])) {
                    echo h(build_green_url($langs_in, $fws_in, $q));
                } elseif (!empty($site['wantedly'])) {
                    echo h(build_wantedly_url($langs_in, $fws_in, $q));
                } elseif (!empty($site['indeed'])) {
                    $i_parts = array_values(array_unique(array_filter(array_merge(
                        $langs_in, $fws_in,
                        $q !== '' ? array_filter(array_map('trim', preg_split('/[\s+]+/', $q))) : [],
                    ))));
                    echo h($site['url'] . urlencode(implode(' ', $i_parts)));
                } elseif (!empty($site['daijob_jp'])) {
                    echo h(build_daijob_url($langs_in, $fws_in, $q, false));
                } elseif (!empty($site['daijob_en'])) {
                    echo h(build_daijob_url($langs_in, $fws_in, $q, true));
                } else {
                    $sfx = !empty($site['lang_fw_pair']) ? $kw_lang_fw_pair : (!empty($site['lang_only']) ? $kw_lang_only : $kw);
                    echo h($site['url'] . $sfx);
                }
              ?>"
                 <?php if (!empty($site['daijob_en'])): ?>data-no-translate="1"<?php endif; ?>
                 target="_blank" rel="noopener noreferrer"
                 class="ext-card notranslate" translate="no">
                <div class="d-flex align-items-center gap-1 mb-1 flex-wrap">
                  <span class="ext-card-name"><?= h($site['name']) ?></span>
                  <span class="ext-tag t-<?= h($site['type']) ?>"><?= h($site['tag']) ?></span>
                </div>
                <div class="ext-desc"><?= h($site['desc']) ?></div>
                <div class="ext-cta">このサイトで探す →</div>
              </a>
            </div>
          <?php endforeach; ?>
        </div>
 
        <!-- Static links (not part of matching/search) -->
        <div class="mt-3 pt-3" style="border-top:1px solid rgba(148,163,184,.18)">
          <div class="ext-title mb-2" style="font-size:.88rem">📌 固定リンク（紹介）</div>
 
          <div class="mb-2" style="font-size:.92rem;color:#dde6f5;font-weight:900;letter-spacing:.06em;text-transform:uppercase">正社員求人</div>
          <div class="row g-2 mb-3">
            <div class="col-12 col-md-6 col-lg-4 col-xxl-3">
              <a href="https://doda.jp/" target="_blank" rel="noopener noreferrer" class="ext-card notranslate" translate="no">
                <div class="d-flex align-items-center gap-1 mb-1 flex-wrap">
                  <span class="ext-card-name">doda</span>
                  <span class="ext-tag t-sei">正社員求人</span>
                </div>
                <div class="ext-desc">正社員求人サイト（紹介リンク）</div>
                <div class="ext-cta">このサイトへ →</div>
              </a>
            </div>
            <div class="col-12 col-md-6 col-lg-4 col-xxl-3">
              <a href="https://www.r-agent.com/job_search/" target="_blank" rel="noopener noreferrer" class="ext-card notranslate" translate="no">
                <div class="d-flex align-items-center gap-1 mb-1 flex-wrap">
                  <span class="ext-card-name">リクルートエージェント</span>
                  <span class="ext-tag t-sei">正社員求人</span>
                </div>
                <div class="ext-desc">正社員求人サイト（紹介リンク）</div>
                <div class="ext-cta">このサイトへ →</div>
              </a>
            </div>
            <div class="col-12 col-md-6 col-lg-4 col-xxl-3">
              <a href="https://miidas.jp/" target="_blank" rel="noopener noreferrer" class="ext-card notranslate" translate="no">
                <div class="d-flex align-items-center gap-1 mb-1 flex-wrap">
                  <span class="ext-card-name">ミイダス</span>
                  <span class="ext-tag t-sei">正社員求人</span>
                </div>
                <div class="ext-desc">正社員求人サイト（紹介リンク）。無料であなたの年収測定も可能です。</div>
                <div class="ext-cta">このサイトへ →</div>
              </a>
            </div>
          </div>
 
          <div class="mb-2" style="font-size:.92rem;color:#dde6f5;font-weight:900;letter-spacing:.06em;text-transform:uppercase">派遣サイト</div>
          <div class="row g-2 mb-3">
            <div class="col-12 col-md-6 col-lg-4 col-xxl-3">
              <a href="https://haken.en-japan.com" target="_blank" rel="noopener noreferrer" class="ext-card notranslate" translate="no">
                <div class="d-flex align-items-center gap-1 mb-1 flex-wrap">
                  <span class="ext-card-name">エン派遣</span>
                  <span class="ext-tag t-sei">派遣</span>
                </div>
                <div class="ext-desc">派遣求人サイト（紹介リンク）</div>
                <div class="ext-cta">このサイトへ →</div>
              </a>
            </div>
            <div class="col-12 col-md-6 col-lg-4 col-xxl-3">
              <a href="https://www.r-staffing.co.jp/" target="_blank" rel="noopener noreferrer" class="ext-card notranslate" translate="no">
                <div class="d-flex align-items-center gap-1 mb-1 flex-wrap">
                  <span class="ext-card-name">リクルートスタッフィング</span>
                  <span class="ext-tag t-sei">派遣</span>
                </div>
                <div class="ext-desc">派遣会社（紹介リンク）</div>
                <div class="ext-cta">このサイトへ →</div>
              </a>
            </div>
            <div class="col-12 col-md-6 col-lg-4 col-xxl-3">
              <a href="https://www.adecco.com/ja-jp" target="_blank" rel="noopener noreferrer" class="ext-card notranslate" translate="no">
                <div class="d-flex align-items-center gap-1 mb-1 flex-wrap">
                  <span class="ext-card-name">Adecco</span>
                  <span class="ext-tag t-sei">派遣</span>
                </div>
                <div class="ext-desc">派遣会社（紹介リンク）</div>
                <div class="ext-cta">このサイトへ →</div>
              </a>
            </div>
          </div>
 
          <div class="mb-2" style="font-size:.92rem;color:#dde6f5;font-weight:900;letter-spacing:.06em;text-transform:uppercase">外資系転職</div>
          <div class="row g-2">
            <div class="col-12 col-md-6 col-lg-4 col-xxl-3">
              <a href="https://www.daijob.com/" target="_blank" rel="noopener noreferrer" class="ext-card">
                <div class="d-flex align-items-center gap-1 mb-1 flex-wrap">
                  <span class="ext-card-name">Daijob.com</span>
                  <span class="ext-tag t-sei">JAPAN</span>
                </div>
                <div class="ext-desc">外資系転職サイト（日本語）</div>
                <div class="ext-cta">このサイトへ →</div>
              </a>
            </div>
            <div class="col-12 col-md-6 col-lg-4 col-xxl-3">
              <a href="https://www.daijob.com/en/" data-no-translate="1" target="_blank" rel="noopener noreferrer" class="ext-card">
                <div class="d-flex align-items-center gap-1 mb-1 flex-wrap">
                  <span class="ext-card-name">Daijob.com</span>
                  <span class="ext-tag t-sei">ENGLISH</span>
                </div>
                <div class="ext-desc">外資系転職サイト（英語）</div>
                <div class="ext-cta">このサイトへ →</div>
              </a>
            </div>
          </div>
 
          <div style="font-size:.84rem;color:#dde6f5;margin-top:.6rem">
            ※この固定リンク枠のサイトはマッチング条件の自動検索には含めていません。
          </div>
        </div>
      </div>
    </div>
 
  </div>
</main>
 
<section style="max-width:900px;margin:2rem auto 0;padding:0 1rem;">
<div style="background:rgba(14,165,233,.08);border:1px solid rgba(14,165,233,.35);border-radius:16px;padding:24px 28px;" id="policy-service">
  <h2 style="color:#7dd3fc;font-size:clamp(17px,3.5vw,22px);margin-bottom:20px;">💡 サービス向上・販売提案</h2>

  <h3 style="color:#bae6fd;font-size:15px;margin:0 0 6px;">🚗 お客様向け無料送迎サービスの導入を</h3>
  <p style="font-size:15px;line-height:1.85;color:#e2e8f0;margin-bottom:18px;">
    キャバレー・キャバクラでは従業員向けの自動車での無料送迎サービスがある所が多い様ですので、
    <strong style="color:#bae6fd;">お客様向けにも無料の送迎サービス</strong>があった方が親切で良いと思われます。
  </p>

  <h3 style="color:#bae6fd;font-size:15px;margin:0 0 6px;">☀️ 朝・昼からのOPENのお店を増やして欲しい</h3>
  <p style="font-size:15px;line-height:1.85;color:#e2e8f0;margin-bottom:18px;">
    お店も、朝からや昼からでも楽しく飲んで気分転換したい方の需要やニーズも増加中の様ですので、
    <strong style="color:#bae6fd;">開店時間を朝からや昼からもOPENのお店</strong>を増やして欲しいです。
  </p>

  <h3 style="color:#bae6fd;font-size:15px;margin:0 0 6px;">🍺 ノンアルコール・無添加ビールの販売拡大を</h3>
  <p style="font-size:15px;line-height:1.85;color:#e2e8f0;margin-bottom:18px;">
    キャバレー・キャバクラや今後は駅のKIOSK・キヨスクや自動販売機でも、ウイスキーやビールの他に、
    <strong style="color:#bae6fd;">ASAHIの青い缶の無添加のノンアルコールビール</strong>など
    販売や提供などの需要やニーズが増加中の様です。
  </p>

  <h3 style="color:#bae6fd;font-size:15px;margin:0 0 6px;">💊 心臓に良い薬・サプリメントの優先販売を</h3>
  <p style="font-size:15px;line-height:1.85;color:#e2e8f0;margin-bottom:18px;">
    駅のキオスクや自動販売機やキャバレー・キャバクラなどのお店でも、
    心臓の薬の<strong style="color:#bae6fd;">「救心」</strong>や
    心臓に良いサプリメントとして<strong style="color:#bae6fd;">「コエンザイムQ10」</strong>などを
    優先的に販売して欲しいです。
  </p>

  <p style="font-size:15px;line-height:1.7;color:#94a3b8;margin:0;">
    ※ 上記はサービス向上・販売促進に関する提案です。各種法令・条例・販売規制を必ずご確認ください。
  </p>
</div>
</section>

<footer class="site-footer">
  <div>© 2026 aruaru — ITエンジニア案件・求人マッチング（純粋PHP版）</div>
  <div class="mt-1">
    <a href="#search">案件を探す</a> ·
    <a href="#ext">外部求人サイト</a>
  </div>
  <!--
  ════════════════════════════════════════════════════════════
  ロリポップ！（全プラン）へのデプロイ手順
  ════════════════════════════════════════════════════════════
  【必要ファイル】 この index.php 1ファイルのみ
  【サーバー要件】 PHP 7.4 以上（ロリポップ！全プランで対応）
                  Composer / SSH / DB 一切不要
  【手順】
    1. ロリポップ！管理画面 → FTP 情報を確認
    2. FFFTP / FileZilla / ロリポップ！FTP（ブラウザ）で接続
    3. 公開ディレクトリに aruaru/ フォルダを作成し index.php を配置
    4. https://あなたのドメイン/aruaru/index.php にアクセス
  ════════════════════════════════════════════════════════════
  -->
</footer>
 
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* ─── 言語 → FW マッピング（PHPのLANG_FW_MAPと同期） ─────── */
const LANG_FW_MAP = <?php
  $map = [];
  foreach (LANG_FW_MAP as $lang => $fws) { $map[$lang] = $fws; }
  echo json_encode($map, JSON_UNESCAPED_UNICODE);
?>;
 
/* ─── 選択中の言語を管理 ──────────────────────────────────── */
const selectedLangs = new Set(<?php echo json_encode(array_values($langs_in), JSON_UNESCAPED_UNICODE); ?>);
 
/* ─── 月額 ↔ 年収 連動 ───────────────────────────────────── */
function fmtAnnual(yen) {
  if (yen <= 0) return '';
  if (yen >= 10000) return '約' + (yen/10000).toFixed(yen%10000===0?0:1) + '億円';
  return yen + '万円';
}
function syncRate(v) {
  const n = parseInt(v)||0;
  document.getElementById('rate-num').value   = n;
  document.getElementById('annual-num').value = n * 12;
}
function syncRateFromNum(v) {
  const n = parseInt(v)||0;
  document.getElementById('rate-slider').value = Math.min(n, 240);
  document.getElementById('annual-num').value  = n * 12;
}
function syncFromAnnual(v) {
  const total = parseInt(v)||0;
  const m = Math.round(total / 12);
  document.getElementById('rate-slider').value = Math.min(m, 240);
  document.getElementById('rate-num').value    = m;
}
function setAnnual(yen) {
  document.getElementById('annual-num').value  = yen;
  const m = Math.round(yen / 12);
  document.getElementById('rate-slider').value = Math.min(m, 240);
  document.getElementById('rate-num').value    = m;
}
 
/* ─── hidden input 管理（FWチップ用） ───────────────────── */
function setHiddenChip(group, val, on) {
  const wrap = document.getElementById('hidden-chip-inputs');
  const id   = 'hci-' + group + '-' + val.replace(/[^a-zA-Z0-9]/g,'_');
  if (on) {
    if (!document.getElementById(id)) {
      const inp = document.createElement('input');
      inp.type='hidden'; inp.id=id; inp.name=group+'[]'; inp.value=val;
      wrap.appendChild(inp);
    }
  } else {
    const ex = document.getElementById(id);
    if (ex) ex.remove();
  }
}
 
/* ─── FWチップの表示更新（選択言語に応じて絞り込み） ─────── */
function updateFwVisibility() {
  const fwWrap     = document.getElementById('fw-chips');
  const placeholder= document.getElementById('fw-placeholder');
  const fwMore     = document.getElementById('fw-more');
  const fwHint     = document.getElementById('fw-hint');
 
  if (selectedLangs.size === 0) {
    /* 言語未選択 → FW欄を非表示、プレースホルダー表示 */
    fwWrap.style.display      = 'none';
    placeholder.style.display = '';
    fwMore.style.display      = 'none';
    fwHint.textContent        = '（言語を選ぶと関連FWが表示されます）';
    /* 選択中FWをすべて解除（hidden inputも削除） */
    fwWrap.querySelectorAll('.chip[data-group="fws"]').forEach(c => {
      if (c.classList.contains('on')) {
        c.classList.remove('on');
        setHiddenChip('fws', c.dataset.val, false);
      }
      c.style.display = 'none';
    });
    return;
  }
 
  /* 表示すべきFWを算出（選択言語のいずれかに含まれるもの） */
  const showFws = new Set();
  selectedLangs.forEach(lang => {
    (LANG_FW_MAP[lang] || []).forEach(fw => showFws.add(fw));
  });
 
  /* 各FWチップを表示/非表示 */
  let visibleCount = 0;
  fwWrap.querySelectorAll('.chip[data-group="fws"]').forEach(chip => {
    const show = showFws.has(chip.dataset.val);
    chip.style.display = show ? '' : 'none';
    if (show) visibleCount++;
    /* 非表示になったのに選択中ならOFFに */
    if (!show && chip.classList.contains('on')) {
      chip.classList.remove('on');
      setHiddenChip('fws', chip.dataset.val, false);
    }
  });
 
  /* カテゴリラベルを、配下に表示FWがある場合のみ表示 */
  const cats = fwWrap.querySelectorAll('.fw-cat-label');
  cats.forEach(catEl => {
    const cat = catEl.dataset.cat;
    /* 同じ data-cat に属するFWチップを探す */
    let hasVisible = false;
    let sibling = catEl.nextElementSibling;
    while (sibling && !sibling.classList.contains('fw-cat-label')) {
      if (sibling.dataset.group === 'fws' && sibling.style.display !== 'none') { hasVisible = true; break; }
      sibling = sibling.nextElementSibling;
    }
    catEl.style.display = hasVisible ? '' : 'none';
  });
 
  /* FW欄を表示 */
  fwWrap.style.display      = visibleCount > 0 ? '' : 'none';
  placeholder.style.display = visibleCount > 0 ? 'none' : '';
  fwMore.style.display      = visibleCount > 0 ? '' : 'none';
 
  const langNames = [...selectedLangs].join(' / ');
  fwHint.textContent = `（${langNames} の関連FWを表示中）`;
}
 
/* ─── 言語チップ選択 ─────────────────────────────────────── */
function toggleLangChip(el) {
  el.classList.toggle('on');
  const val = el.dataset.val;
  if (el.classList.contains('on')) {
    selectedLangs.add(val);
    setHiddenChip('langs', val, true);
  } else {
    selectedLangs.delete(val);
    setHiddenChip('langs', val, false);
  }
  updateFwVisibility();
}
 
/* ─── FWチップ選択（通常のトグル） ─────────────────────── */
function toggleChip(el) {
  el.classList.toggle('on');
  setHiddenChip(el.dataset.group, el.dataset.val, el.classList.contains('on'));
}
 
/* ─── チップ欄 展開/折り畳み ─────────────────────────────── */
function toggleWrap(wrapId, btn) {
  const wrap = document.getElementById(wrapId);
  const open = wrap.classList.toggle('open');
  btn.textContent = open ? '▲ 閉じる' : '▼ すべて表示';
}
 
/* ─── 送信直前：hidden をチップの実表示と完全一致させる（古い langs[]/fws[] が残るバグ対策） ─ */
function syncChipHiddenBeforeSubmit() {
  const wrap = document.getElementById('hidden-chip-inputs');
  if (!wrap) return;
  wrap.innerHTML = '';
  document.querySelectorAll('#lang-chips .chip.on').forEach(c => {
    const v = c.dataset.val;
    if (!v) return;
    const inp = document.createElement('input');
    inp.type = 'hidden';
    inp.name = 'langs[]';
    inp.id = 'hci-langs-' + v.replace(/[^a-zA-Z0-9]/g, '_');
    inp.value = v;
    wrap.appendChild(inp);
  });
  document.querySelectorAll('#fw-chips .chip[data-group="fws"].on').forEach(c => {
    const v = c.dataset.val;
    if (!v) return;
    const inp = document.createElement('input');
    inp.type = 'hidden';
    inp.name = 'fws[]';
    inp.id = 'hci-fws-' + v.replace(/[^a-zA-Z0-9]/g, '_');
    inp.value = v;
    wrap.appendChild(inp);
  });
}
 
/* ─── 初期化：ページ読込時にFW表示状態を反映 ──────────────── */
document.addEventListener('DOMContentLoaded', () => {
  const searchForm = document.getElementById('search-form');
  if (searchForm) {
    searchForm.addEventListener('submit', function() { syncChipHiddenBeforeSubmit(); });
  }
  /* 1. まず選択済みFWチップをON状態・表示に（updateFwVisibility より先に実行） */
  <?php foreach ($fws_in as $f): ?>
  document.querySelectorAll('.chip[data-val=<?= json_encode($f) ?>][data-group="fws"]')
    .forEach(c => { c.classList.add('on'); c.style.display=''; });
  <?php endforeach; ?>
 
  /* 2. FW表示を更新（ON状態が設定された後に呼ぶ） */
  updateFwVisibility();
 
  /* 3. hidden-chip-inputs にある PHP 出力の hidden input は既に正しいので
        JS で重複追加しない（setHiddenChip は呼ばない）
     ただし PHP hidden input が存在しない場合（初回）に備えて確認してから追加 */
  <?php foreach ($langs_in as $l): ?>
  (function(){
    const id = 'hci-langs-' + <?= json_encode($l) ?>.replace(/[^a-zA-Z0-9]/g,'_');
    if (!document.getElementById(id)) setHiddenChip('langs', <?= json_encode($l) ?>, true);
  })();
  <?php endforeach; ?>
  <?php foreach ($fws_in as $f): ?>
  (function(){
    const id = 'hci-fws-' + <?= json_encode($f) ?>.replace(/[^a-zA-Z0-9]/g,'_');
    if (!document.getElementById(id)) setHiddenChip('fws', <?= json_encode($f) ?>, true);
  })();
  <?php endforeach; ?>
});
</script>
 
<!-- ════════════════════════════════════════════════════════════════
     Google 翻訳プロキシ対応スクリプト
     ────────────────────────────────────────────────────────────────
     ・translate.goog 経由でも「This form is not supported.」を出さずに
       フォーム送信（検索・ソート）を動作させる
     ・data-no-translate="1" リンクは常に素のURLで開く
     ・外部リンクは翻訳プロキシ外で開き応募ページの CORS ERROR を回避
════════════════════════════════════════════════════════════════ -->
<script>
(function(){
  'use strict';
 
  var isProxy = (location.hostname || '').indexOf('.translate.goog') !== -1;
 
  /* translate.goog ラッパーを外して元URLを返す */
  function stripTranslateProxy(href) {
    if (!href) return href;
    try {
      var u = new URL(href, location.href);
      if (u.hostname.indexOf('.translate.goog') === -1) return href;
      var sub = u.hostname.replace(/\.translate\.goog$/, '');
      var orig = sub.replace(/--/g, '\u0001').replace(/-/g, '.').replace(/\u0001/g, '-');
      var sp = u.searchParams;
      ['_x_tr_sl','_x_tr_tl','_x_tr_hl','_x_tr_pto','_x_tr_hist'].forEach(function(k){ sp.delete(k); });
      var qs = sp.toString();
      return 'https://' + orig + u.pathname + (qs ? '?' + qs : '') + (u.hash || '');
    } catch(e) { return href; }
  }
 
  /* 元URLを translate.goog ラッパー付きURLに変換する（翻訳維持ナビゲーション用） */
  function wrapInTranslateProxy(rawUrl) {
    try {
      var cp = new URLSearchParams(location.search);
      var sl = cp.get('_x_tr_sl') || 'auto';
      var tl = cp.get('_x_tr_tl') || 'en';
      var hl = cp.get('_x_tr_hl') || tl;
      var u = new URL(rawUrl, location.href);
      /* ドット→ハイフン、既存ハイフン→ダブルハイフン */
      var googHost = u.hostname.replace(/-/g,'--').replace(/\./g,'-') + '.translate.goog';
      var sp = new URLSearchParams(u.search);
      sp.set('_x_tr_sl', sl);
      sp.set('_x_tr_tl', tl);
      sp.set('_x_tr_hl', hl);
      sp.set('_x_tr_hist', '0');
      return u.protocol + '//' + googHost + u.pathname + '?' + sp.toString() + (u.hash || '');
    } catch(e) { return rawUrl; }
  }
 
  /* フォームを JavaScript ナビゲーションで送信（Google翻訳ブロック回避） */
  // URLパラメータ（lang）またはハッシュ（translateto）に言語指定が含まれていれば追加する
  function getLangParam() {
    try{
      var sp = new URLSearchParams(location.search);
      var l = sp.get('lang');
      if(l) return l;
      var m = (location.hash||'').match(/translateto=([a-zA-Z\-]+)/);
      return m ? decodeURIComponent(m[1]) : '';
    }catch(e){ return ''; }
  }
 
  function doFormNavigate(f) {
    var rawBase = stripTranslateProxy(location.href).split('?')[0];
    var method = (f.getAttribute('method') || 'GET').toUpperCase();
    if (method === 'GET') {
      var fd;
      try { fd = new URLSearchParams(new FormData(f)); }
      catch(e) { fd = new URLSearchParams(); }
      
      var qs = fd.toString();
      var targetUrl = rawBase + (qs ? '?' + qs : '');
      var l = getLangParam();
      if(l && l !== 'ja') {
        targetUrl += '#translateto=' + encodeURIComponent(l);
      }
      location.href = isProxy ? wrapInTranslateProxy(targetUrl) : targetUrl;
    } else {
      /* POST: 翻訳プロキシ外のURLへ送信（新タブ） */
      var tf = document.createElement('form');
      tf.method = method;
      tf.action = rawBase;
      tf.target = '_blank';
      try {
        var entries = Array.from(new FormData(f).entries());
        entries.forEach(function(p){
          var inp = document.createElement('input');
          inp.type = 'hidden'; inp.name = p[0]; inp.value = p[1];
          tf.appendChild(inp);
        });
      } catch(e2){}
      document.body.appendChild(tf);
      tf.submit();
      document.body.removeChild(tf);
    }
  }
 
  /* submit イベント（念のため残す：translate.goog 以外の経路でも動く） */
  function onFormSubmit(ev) {
    var f = ev.target;
    if (!f || f.tagName !== 'FORM') return;
    if (f.id !== 'search-form' && f.id !== 'sort-form') return;
    ev.preventDefault();
    ev.stopImmediatePropagation();
    doFormNavigate(f);
  }
 
  /* 外部リンク／data-no-translate リンクのクリック処理 */
  function isSameSiteHref(href) {
    if (!href) return true;
    if (/^(?:javascript|mailto|tel|sms|#):/i.test(href)) return true;
    if (href.charAt(0) === '#') return true;
    try {
      var u = new URL(href, location.href);
      if (u.hostname === location.hostname) return true;
      if (u.hostname.indexOf('.translate.goog') !== -1) return true;
 
      // ユーザーの管理ドメインリスト（これらは外部サイト扱いせずプロキシを維持する）
      var myDomains = ['aon.tokyo', 'audiocafe.tokyo', 'icpo.tokyo', 'ion.tokyo', 'raysoft.jp', 'nanosoft.jp', 'ala.tokyo', 'nasa.tokyo', 'anken.jp'];
      // プロキシ越しの判定も考慮 (raysoft-jp.translate.goog 等)
      var targetBase = u.hostname.replace('.translate.goog','').replace(/-/g,'.');
      if (myDomains.indexOf(targetBase) !== -1) return true;
 
      return false;
    } catch(e) { return true; }
  }
 
  function onLinkClick(ev) {
    var a = ev.target.closest && ev.target.closest('a[href]');
    if (!a) return;
    var noTranslate = a.getAttribute('data-no-translate') === '1';
    var href = a.getAttribute('href') || '';
    if (!href) return;
    if (noTranslate) {
      var rawNT = stripTranslateProxy(href);
      ev.preventDefault(); ev.stopPropagation();
      var w = window.open(rawNT, '_blank', 'noopener,noreferrer');
      if (!w) location.href = rawNT;
      return;
    }
    if (isProxy && !isSameSiteHref(href)) {
      var raw = stripTranslateProxy(href);
      ev.preventDefault(); ev.stopPropagation();
      var w2 = window.open(raw, '_blank', 'noopener,noreferrer');
      if (!w2) location.href = raw;
    }
  }
 
  document.addEventListener('click', onLinkClick, true);
  document.addEventListener('submit', onFormSubmit, true);
 
  /* 検索ボタン (type=button) と sort select を直接ハンドル
     → Google翻訳の form ブロックを完全に迂回 */
  function bindForms() {
    /* 検索ボタン */
    var searchBtn = document.getElementById('search-submit-btn');
    if (searchBtn) {
      searchBtn.addEventListener('click', function(ev) {
        ev.preventDefault();
        ev.stopImmediatePropagation();
        doFormNavigate(document.getElementById('search-form'));
      }, true);
    }
    /* ソートセレクト */
    var sortSel = document.querySelector('#sort-form select[name="sort"]');
    if (sortSel) {
      sortSel.removeAttribute('onchange');
      sortSel.addEventListener('change', function() {
        doFormNavigate(document.getElementById('sort-form'));
      });
    }
    /* リセットリンク（"?" → 素のURLへ） */
    var resetA = document.querySelector('a.btn-reset[href="?"]');
    if (resetA) {
      resetA.addEventListener('click', function(ev) {
        if (!isProxy) return;
        ev.preventDefault();
        ev.stopPropagation();
        var raw = stripTranslateProxy(location.href).split('?')[0];
        location.href = wrapInTranslateProxy(raw);
      }, true);
    }
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bindForms);
  } else {
    bindForms();
  }
 
})();
</script>
 
<!-- aruaru ロゴアニメ JS -->
<script>
(function() {
  "use strict";
  const canvas = document.getElementById("logoCanvas");
  if (!canvas) return;
  const ctx = canvas.getContext("2d");
  const TEXT = "aruaru";
  let CW, CH, dpr;
  function resize() {
    dpr = Math.min(window.devicePixelRatio || 1, 2);
    CW = canvas.offsetWidth || 140;
    CH = canvas.offsetHeight || 36;
    canvas.width = CW * dpr;
    canvas.height = CH * dpr;
    ctx.scale(dpr, dpr);
  }
  window.addEventListener("resize", resize);
  resize();
  let x = CW;
  const fontSize = 24;
  ctx.font = "bold " + fontSize + "px sans-serif";
  const textWidth = ctx.measureText(TEXT).width;
  function draw() {
    ctx.clearRect(0, 0, CW, CH);
    const grad = ctx.createLinearGradient(x, 0, x + textWidth, 0);
    grad.addColorStop(0, "#ff4e50");
    grad.addColorStop(1, "#f9d423");
    ctx.fillStyle = grad;
    ctx.font = "bold " + fontSize + "px sans-serif";
    ctx.textBaseline = "middle";
    ctx.fillText(TEXT, x, CH / 2);
    x -= 0.8;
    if (x < -textWidth) x = CW;
    requestAnimationFrame(draw);
  }
  draw();
})();
</script>
 
<script>
/* Google翻訳フィードバックバルーン除去 */
(function(){
  var BALLOON_SELS = [
    '.goog-te-balloon-frame',
    '.goog-te-balloon-bg',
    '#goog-gt-tt',
    '#goog-gt-votingframe',
    '.goog-tooltip',
    '.goog-te-ftphfe'
  ];
  function removeBalloons(){
    BALLOON_SELS.forEach(function(sel){
      document.querySelectorAll(sel).forEach(function(el){ el.remove(); });
    });
  }
  removeBalloons();
  var obs = new MutationObserver(function(mutations){
    for(var i=0;i<mutations.length;i++){
      if(mutations[i].addedNodes.length) removeBalloons();
    }
  });
  obs.observe(document.documentElement, {childList:true, subtree:true});
  /* mouseup でバルーンが出る前に消す */
  document.addEventListener('mouseup', function(){ setTimeout(removeBalloons, 0); }, true);
  document.addEventListener('touchend', function(){ setTimeout(removeBalloons, 0); }, true);
  document.addEventListener('focus', function(){ setTimeout(removeBalloons, 0); }, true);
})();
</script>
 
<!-- ============================================================
     aruaru Open-Source Translation Engine (MyMemory API)
     Google翻訳を一切使わないオープンソース翻訳システム
     ============================================================ -->
 
 
<style>
/* 外部サイト翻訳モード切替スイッチ */
#ext-link-switch {
  position: fixed;
  top: var(--aruaru-gadget-top);
  right: calc(env(safe-area-inset-right, 0px) + 10px);
  left: auto;
  z-index: 2147483646;
  background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(8px);
  padding: 12px 18px 12px 18px; border-radius: 8px; border: 2px solid rgba(34, 211, 238, 0.8);
  color: #fff; font-size: clamp(0.75rem, 2vw, 0.95rem); font-family: sans-serif;
  box-shadow: 0 4px 16px rgba(0,0,0,0.6); display: flex !important; flex-direction: column; gap: 8px;
  /* max-width を right 値を考慮した計算式にして画面左端からはみ出さないようにする */
  max-width: calc(100vw - env(safe-area-inset-right, 0px) - 10px - 8px);
  width: max-content;
  box-sizing: border-box;
}
#ext-link-switch.minimized {
  display: none !important;
}
#ext-link-switch strong { color: #67e8f9; font-size: clamp(0.8rem, 2.2vw, 1rem); display: block; padding-right: 0; box-sizing: border-box; }
#ext-link-switch label { cursor: pointer; display: flex; align-items: flex-start; gap: 6px; margin-bottom: 0; font-weight: bold; white-space: normal; word-break: break-word; line-height: 1.3; }
#ext-link-switch input { margin:0; }
#ext-link-switch-toggle {
  position: fixed;
  top: var(--aruaru-gadget-top);
  right: calc(env(safe-area-inset-right, 0px) + 10px);
  left: auto;
  z-index: 2147483647;
  background: rgba(15, 23, 42, 0.95); border: 2px solid rgba(34, 211, 238, 0.8);
  color: #22d3ee; padding: clamp(15px, 1.2vw, 6px) clamp(15px, 2vw, 12px); border-radius: 8px; cursor: pointer;
  font-weight: 800; font-size: clamp(0.7rem, 2vw, 0.85rem); backdrop-filter: blur(8px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.5); display: none;
  pointer-events: auto;
}
/* [REMOVED] パネル内 .close-btn 旧定義 → CLOSEボタンはパネル外fixed配置に変更
#ext-link-switch .close-btn {
  position: absolute; bottom: 8px; right: 10px; top: auto; left: auto; cursor: pointer;
  color: #fff; font-size: clamp(0.65rem, 1.9vw, 0.82rem); font-weight: 800;
  padding: 6px 10px; border-radius: 6px; border: 1px solid rgba(148,163,184,0.45); background: rgba(0,0,0,0.25);
  line-height: 1.2; user-select: none; -webkit-user-select: none;
  font-family: inherit; appearance: none; -webkit-appearance: none;
}
#ext-link-switch .close-btn:hover { color: #fff; border-color: rgba(34,211,238,0.65); background: rgba(34,211,238,0.12); }
*/

.ac-gt-site-modal { position: fixed; inset: 0; z-index: 2147483647; display: none; align-items: center; justify-content: center; padding: max(.75rem, env(safe-area-inset-bottom)); box-sizing: border-box; }
.ac-gt-site-modal.is-open { display: flex; }
.ac-gt-site-modal__backdrop { position: absolute; inset: 0; background: rgba(0,0,0,.72); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); }
.ac-gt-site-modal__box { position: relative; z-index: 1; width: min(100%, 440px); max-height: min(88vh, 32rem); overflow: auto; padding: 1.2rem 1.1rem 1.1rem; border-radius: 14px; background: rgba(15,23,42,.98); border: 2px solid rgba(34,211,238,.55); box-shadow: 0 20px 56px rgba(0,0,0,.55); }
.ac-gt-site-modal__title { font-size: clamp(.86rem, 2.1vw, .98rem); font-weight: 800; color: #f8fafc; line-height: 1.5; margin: 0 0 .75rem; }
.ac-gt-site-modal__btns { display: flex; flex-direction: column; gap: .5rem; margin-bottom: .65rem; }
.ac-gt-site-modal__btn { display: block; width: 100%; padding: .58rem .65rem; border-radius: 10px; border: 1px solid rgba(148,163,184,.4); background: rgba(255,255,255,.08); color: #e2e8f0; font-size:.88rem; font-weight: 700; cursor: pointer; text-align: center; line-height: 1.35; word-break: break-all; }
.ac-gt-site-modal__btn:hover, .ac-gt-site-modal__btn:focus-visible { border-color: rgba(34,211,238,.65); background: rgba(34,211,238,.14); outline: none; color: #fff; }
 
@media (max-width: 600px) {
  #ext-link-switch {
    top: var(--aruaru-gadget-top);
    right: calc(env(safe-area-inset-right, 0px) + 5px);
    left: auto;
    /* CLOSEがパネル外fixed配置になったため底部padding最小化 */
    padding: 10px 12px 12px 12px;
    /* スマホで確実に画面内に収める:
       right=5px なので左端は「画面幅 - 5px - ウィジェット幅」。
       max-width を 85vw に制限して常に画面内に収まるようにする */
    max-width: min(85vw, calc(100vw - env(safe-area-inset-right, 0px) - 5px - 4px));
    width: max-content;
    font-size:.88rem;
  }
  #ext-link-switch-toggle {
    top: var(--aruaru-gadget-top);
    right: calc(env(safe-area-inset-right, 0px) + 5px);
    left: auto;
    font-size:.88rem;
    padding: 4px 10px;
  }
}

/* ============================================================
   [PATCH v2] CLOSE をパネル内4番目のラジオラベルに統合
              + タブレット/スマホ縦横すべての画面サイズで自動収納
============================================================ */

/* CSS変数化 (JSの fitPanel から動的に上書き可能) */
#ext-link-switch{
  --el-top: var(--aruaru-gadget-top);
  --el-right: calc(env(safe-area-inset-right, 0px) + 10px);
  --el-font: clamp(0.75rem, 2vw, 0.95rem);
  --el-gap: 8px;
  --el-pad-x: 18px;
  --el-pad-y-top: 12px;
  --el-pad-y-bot: 12px;
  --el-line: 1.3;

  top: var(--el-top);
  right: var(--el-right);
  padding: var(--el-pad-y-top) var(--el-pad-x) var(--el-pad-y-bot) var(--el-pad-x);
  font-size: var(--el-font);
  gap: var(--el-gap);
  /* 高さがビューポートを超えないようにする (JS でも追従調整)
     safe-area-inset-bottom も考慮 (iPhone のホームバー領域) */
  max-height: calc(100vh - var(--el-top) - env(safe-area-inset-bottom, 0px) - 16px);
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
  overscroll-behavior: contain;
}
#ext-link-switch label{ line-height: var(--el-line); }
#ext-link-switch strong{ line-height: var(--el-line); }
#ext-link-switch input[type=radio]{
  min-width: 18px; min-height: 18px; flex-shrink: 0;
}

/* OPENボタン: タップ最適化 */
#ext-link-switch-toggle{
  min-height: 34px;
  -webkit-tap-highlight-color: rgba(34,211,238,0.4);
  touch-action: manipulation;
}

/* ============================================================
   [PATCH v3] BUG FIX: 旧 #ext-link-switch-close (button) が
   画面右下に表示されるバグを防ぐ防御CSS
============================================================ */
#ext-link-switch-close,
button#ext-link-switch-close,
.ext-link-switch-close,
[id="ext-link-switch-close"]:not(input):not(label){
  position:absolute !important;
  left:-99999px !important;
  top:-99999px !important;
  right:auto !important;
  bottom:auto !important;
  width:1px !important;
  height:1px !important;
  min-width:0 !important;
  min-height:0 !important;
  max-width:1px !important;
  max-height:1px !important;
  padding:0 !important;
  margin:0 !important;
  border:0 !important;
  opacity:0 !important;
  visibility:hidden !important;
  pointer-events:none !important;
  display:block !important;
  z-index:-1 !important;
  transform:none !important;
  background:transparent !important;
  box-shadow:none !important;
}

/* 4番目: CLOSE をラジオ風リンクとして配置 (確実に表示) */
.ext-link-switch__close-row{
  margin-top: 6px !important;
  padding-top: 8px !important;
  border-top: 1px dashed rgba(148,163,184,0.4) !important;
  min-height: 44px !important;
  padding-left: 4px !important;
  padding-right: 4px !important;
  border-radius: 6px !important;
  background: rgba(34,211,238,0.06) !important;
  border-bottom: 1px solid rgba(34,211,238,0.25) !important;
  -webkit-tap-highlight-color: rgba(34,211,238,0.35);
  touch-action: manipulation;
  cursor: pointer !important;
  transition: background .12s ease, border-color .12s ease;
  display:flex !important;
  visibility:visible !important;
  opacity:1 !important;
  position:static !important;
  pointer-events:auto !important;
}
.ext-link-switch__close-row:hover,
.ext-link-switch__close-row:focus-within{
  background: rgba(34,211,238,0.16) !important;
  border-color: rgba(34,211,238,0.6) !important;
}
.ext-link-switch__close-row .ext-link-switch__close-text{
  color: #67e8f9 !important;
  font-weight: 900 !important;
  letter-spacing: 0.08em !important;
  font-size: 1.05em !important;
  display:inline-block !important;
  visibility:visible !important;
  opacity:1 !important;
}

/* モーダル選択ボタンもタップ最適化 */
.ac-gt-site-modal__btn{
  min-height: 40px;
  touch-action: manipulation;
}

/* ===== タブレット (601〜1024px) ===== */
@media (max-width: 1024px) and (min-width: 601px){
  #ext-link-switch{
    --el-font: clamp(0.78rem, 1.6vw, 0.92rem);
    --el-pad-x: 16px;
    --el-pad-y-top: 11px;
    --el-pad-y-bot: 11px;
    --el-gap: 7px;
    /* タブレットでも横幅最大を画面幅の80%に制限 */
    max-width: min(80vw, calc(100vw - env(safe-area-inset-right, 0px) - 10px - 8px));
  }
}

/* ===== スマホ (〜600px) 一般 ===== */
@media (max-width: 600px){
  #ext-link-switch{
    --el-right: calc(env(safe-area-inset-right, 0px) + 5px);
    --el-pad-x: 12px;
    --el-pad-y-top: 10px;
    --el-pad-y-bot: 11px;
    --el-font: .85rem;
    --el-gap: 6px;
    --el-line: 1.28;
    max-width: min(94vw, calc(100vw - env(safe-area-inset-right, 0px) - 5px - 4px));
  }
  .ext-link-switch__close-row{
    min-height: 42px;
    margin-top: 5px;
    padding-top: 7px;
  }
}

/* ===== 縦が狭い端末 (横向きスマホ等 〜520px) ===== */
@media (max-height: 520px){
  #ext-link-switch{
    --el-font: .78rem;
    --el-gap: 4px;
    --el-pad-y-top: 8px;
    --el-pad-y-bot: 9px;
    --el-line: 1.22;
  }
  .ext-link-switch__close-row{
    min-height: 36px;
    margin-top: 3px;
    padding-top: 5px;
  }
  .ext-link-switch__close-row .ext-link-switch__close-text{
    font-size: 1em;
  }
}

/* ===== 極小端末 (〜360px) ===== */
@media (max-width: 360px){
  #ext-link-switch{
    --el-font: .8rem;
    --el-pad-x: 10px;
  }
}

/* ===== スマホ縦向き: パネルを横幅いっぱいに ===== */
@media (max-width: 600px) and (orientation: portrait){
  #ext-link-switch{
    --el-right: calc(env(safe-area-inset-right, 0px) + 4px);
    left: calc(env(safe-area-inset-left, 0px) + 4px) !important;
    right: calc(env(safe-area-inset-right, 0px) + 4px) !important;
    width: auto !important;
    max-width: none !important;
    box-sizing: border-box;
  }
}

/* ===== 横向きスマホ: ノッチ/パンチホール考慮で右端余白を多めに ===== */
@media (max-height: 520px) and (orientation: landscape){
  #ext-link-switch{
    --el-right: max(14px, calc(env(safe-area-inset-right, 0px) + 10px));
  }
  #ext-link-switch-toggle{
    right: max(14px, calc(env(safe-area-inset-right, 0px) + 10px)) !important;
  }
}

/* ===== OPENボタンスマホはみ出し防止 ===== */
@media (max-width: 600px){
  #ext-link-switch-toggle{
    right: max(5px, env(safe-area-inset-right, 0px)) !important;
  }
}

/* ===== Google翻訳UIの隠蔽 (既存維持) ===== */
.goog-te-balloon-frame, .goog-te-balloon-bg, #goog-gt-tt, #goog-gt-votingframe,
.goog-tooltip, .goog-tooltip-content, .goog-te-ftphfe, .goog-te-spinner-pos, .goog-te-highlight {
  display:none!important;visibility:hidden!important;opacity:0!important;pointer-events:none!important;
}
.goog-te-banner-frame { display:none!important; }
body { top:0!important; }
/* Google翻訳ウィジェットは機能のみ利用し、UIは隠す */
#google_translate_element {
  position:absolute;left:-9999px;top:-9999px;width:1px;height:1px;overflow:hidden;visibility:hidden;
}
</style>
 
<!-- 外部サイト翻訳モード切替スイッチ -->
<div id="ext-link-switch-toggle" class="notranslate" translate="no">OPEN</div>
<div id="ext-link-switch" class="notranslate" translate="no" style="display: flex !important; visibility: visible !important; opacity: 1 !important;">
  <strong>外部サイト (Paiza/Daijob等) の動作</strong>
  <label title="Google翻訳で内容を読みます。※ログインや応募フォームはエラーになります。">
    <input type="radio" name="ext_mode" value="translate" checked> Google翻訳する (閲覧用)　※ログインや応募フォームや回答はエラーになります。
  </label>
  <label title="翻訳しません。※ログインや応募フォームが正常に動作します。">
    <input type="radio" name="ext_mode" value="raw"> Google翻訳しない (応募・学習用。※ログインや応募フォームが正常に動作します。)
  </label>
  <label title="Google翻訳で開くサイトを3つから選びます（原文：日本語 → 翻訳先：選択中の言語）。各URLが入力された状態で Google 翻訳へ移動します。">
    <input type="radio" name="ext_mode" value="google"> Google翻訳サイトへ移動
  </label>
  <!-- 4番目: CLOSE をラジオ風リンクとして追加。選択するとパネルを閉じる。
       label.ext-link-switch__close-row で他のラジオと視覚区別 -->
  <label class="ext-link-switch__close-row" title="このパネルを閉じます">
    <input type="radio" name="ext_mode" value="__close__" id="ext-link-switch-close-radio"> <span class="ext-link-switch__close-text">CLOSE</span>
  </label>
</div>

<div id="acGtSitePickModal" class="ac-gt-site-modal notranslate" translate="no" hidden aria-modal="true" role="dialog" aria-labelledby="acGtSitePickTitle">
  <div class="ac-gt-site-modal__backdrop" data-ac-gt-site-dismiss="1"></div>
  <div class="ac-gt-site-modal__box">
    <p id="acGtSitePickTitle" class="ac-gt-site-modal__title"></p>
    <div id="acGtSitePickBtns" class="ac-gt-site-modal__btns"></div>
    <button type="button" class="ac-gt-site-modal__btn" id="acGtSitePickCancel"></button>
  </div>
</div>
 
<div id="google_translate_element" aria-hidden="true"></div>
 
<script>
(function(){
  "use strict";
 
  function getLang() {
    try {
      var sp = new URLSearchParams(location.search);
      // 1. Google 翻訳プロキシのパラメータ (_x_tr_tl) を最優先
      var tr_tl = sp.get("_x_tr_tl");
      if (tr_tl) return tr_tl;
 
      // 2. 自前の URL パラメータ
      var lp = sp.get("lang");
      if (lp) return lp;
 
      // 3. ハッシュ (#translateto=en)
      var h = (location.hash || "").match(/translateto=([a-zA-Z\-]+)/);
      if (h) return decodeURIComponent(h[1]);
 
      // 4. googtrans クッキー (Google 翻訳ウィジェットがセットするもの)
      var m = document.cookie.match(/(?:^|;\s*)googtrans=\/[^/]+\/([^;]+)/);
      if (m && m[1]) return decodeURIComponent(m[1]).trim();
 
      // 5. 前回のセッション保存
      return sessionStorage.getItem("ac_last_lang") || sessionStorage.getItem("selectedLang") || localStorage.getItem("selectedLang") || "";
    } catch (e) { return ""; }
  }
 
  var LANG = getLang();
 
  // 「翻訳しない」モードが強制されている場合は LANG をリセット
  try {
    if (sessionStorage.getItem('ext_mode_forced_raw') === '1') {
      LANG = '';
    }
  } catch(e) {}
 
  // translate.goog プロキシ上にいる場合は何もしない (プロキシが翻訳する)
  var ON_PROXY = location.hostname.indexOf('.translate.goog') !== -1;
 
  if (LANG && LANG !== 'ja' && !ON_PROXY) {
    // ウィジェット方式で翻訳する。translate.goog へのリダイレクトは
    // 長いページで翻訳が発火しない不具合があるため廃止。
    try {
      sessionStorage.setItem("ac_last_lang", LANG);
      sessionStorage.setItem("selectedLang", LANG);
      localStorage.setItem("selectedLang", LANG);
      // lastSelectedLang は「翻訳しない」を選んだ後にも残す永続キー。
      // ユーザーが再度「翻訳する」を押した時に、元々選んでいた言語に戻すために使う。
      localStorage.setItem("lastSelectedLang", LANG);
      var cv = "/ja/" + LANG;
      document.cookie = "googtrans=" + cv + "; path=/; max-age=31536000";
      var h = location.hostname;
      var parts = h.split('.');
      if(parts.length >= 2){
        document.cookie = "googtrans=" + cv + "; path=/; domain=." + parts.slice(-2).join('.') + "; max-age=31536000";
      }
    } catch (e) {}
 
    // ?lang=XX が URL に残っていればクリーンな URL に置き換える
    try {
      var u = new URL(location.href);
      if (u.searchParams.has('lang')) {
        u.searchParams.delete('lang');
        history.replaceState(null, '', u.toString());
      }
    } catch (e) {}
 
    // Cookie 反映のため初回ロードで一度だけリロード
    try {
      var cookieEffective = document.cookie.indexOf('googtrans=/ja/' + LANG) !== -1;
      var alreadyReloaded = sessionStorage.getItem('__ac_aruaru_reloaded') === '1';
      if (!cookieEffective && !alreadyReloaded) {
        sessionStorage.setItem('__ac_aruaru_reloaded', '1');
        location.reload();
        return;
      }
    } catch (e) {}
  } else if (LANG && LANG !== 'ja' && ON_PROXY) {
    try {
      sessionStorage.setItem("ac_last_lang", LANG);
      sessionStorage.setItem("selectedLang", LANG);
      localStorage.setItem("selectedLang", LANG);
      localStorage.setItem("lastSelectedLang", LANG);
    } catch (e) {}
  } else if (LANG === "ja" && !ON_PROXY) {
    // 日本語: Google 翻訳をかけず素の日本語表示。googtrans と言語フラグを除去
    try {
      document.cookie = "googtrans=; path=/; max-age=0";
      document.cookie = "googtrans=/ja/ja; path=/; max-age=0";
      var hh0 = location.hostname;
      var pp0 = hh0.split(".");
      if (pp0.length >= 2) {
        var dom0 = "." + pp0.slice(-2).join(".");
        document.cookie = "googtrans=; path=/; domain=" + dom0 + "; max-age=0";
        document.cookie = "googtrans=/ja/ja; path=/; domain=" + dom0 + "; max-age=0";
      }
    } catch (e) {}
    try {
      sessionStorage.removeItem("ac_last_lang");
      sessionStorage.removeItem("selectedLang");
      localStorage.removeItem("selectedLang");
      sessionStorage.removeItem("__ac_aruaru_reloaded");
    } catch (e) {}
    try {
      var uj = new URL(location.href);
      var hadLangJa = uj.searchParams.get("lang") === "ja";
      var fullHash0 = (uj.hash || "").replace(/^#/, "");
      var hadTransJa = /(^|&)translateto=ja(&|$)/i.test(fullHash0);
      uj.searchParams.delete("lang");
      var hp = (uj.hash || "").replace(/^#/, "").split("&").filter(function (p) { return p && p.indexOf("translateto=") !== 0; });
      uj.hash = hp.length ? "#" + hp.join("&") : "";
      history.replaceState(null, "", uj.toString());
      if ((hadLangJa || hadTransJa) && sessionStorage.getItem("__ac_aruaru_ja_clean") !== "1") {
        try { sessionStorage.setItem("__ac_aruaru_ja_clean", "1"); } catch (e2) {}
        location.reload();
        return;
      }
      try { sessionStorage.removeItem("__ac_aruaru_ja_clean"); } catch (e3) {}
    } catch (e) {}
    try { localStorage.setItem("lastSelectedLang", "ja"); } catch (e4) {}
    LANG = "";
  }
 
  var SWITCH_I18N = {
    "en": { title: "External Sites (Paiza, Daijob, etc.)", trans: "Translate (Viewing)", raw: "Do not translate (Apply/Login)", google: "Google Translate site (UI in selected language)" },
    "zh-CN": { title: "外部网站 (Paiza/Daijob 等)", trans: "翻译 (浏览用)", raw: "不翻译 (应聘/学习用)", google: "Google 翻译（所选语言界面）" },
    "zh-TW": { title: "外部網站 (Paiza/Daijob 等)", trans: "翻譯 (瀏覽用)", raw: "不翻譯 (應聘/學習用)", google: "Google 翻譯（所選語言介面）" },
    "ko": { title: "외부 사이트 (Paiza/Daijob 등)", trans: "번역하기 (열람용)", raw: "번역 안 함 (지원/학습용)", google: "Google 번역(선택 언어 UI)" },
    "fr": { title: "Sites Externes (Paiza, etc.)", trans: "Traduire (Lecture)", raw: "Ne pas traduire (Postuler/Login)", google: "Google Traduction (UI langue choisie)" },
    "de": { title: "Externe Seiten (Paiza, etc.)", trans: "Übersetzen (Lesen)", raw: "Nicht übersetzen (Bewerben/Login)", google: "Google Übersetzer (UI der gewählten Sprache)" },
    "es": { title: "Sitios Externos (Paiza, etc.)", trans: "Traducir (Lectura)", raw: "No traducir (Aplicar/Login)", google: "Google Traductor (interfaz en el idioma elegido)" },
    "ru": { title: "Внешние сайты (Paiza, Daijob)", trans: "Перевести (Просмотр)", raw: "Не переводить (Отклик/Вход)", google: "Google Переводчик (интерфейс выбранного языка)" },
    "ar": { title: "المواقع الخارجية (Paiza, Daijob)", trans: "ترجمة (للقراءة)", raw: "عدم الترجمة (للتقديم/الدخول)", google: "ترجمة Google (واجهة اللغة المختارة)" },
    "hi": { title: "बाहरी साइटें (Paiza, Daijob)", trans: "अनुवाद करें (देखने के लिए)", raw: "अनुवाद न करें (आवेदन/लॉगिन)", google: "Google अनुवाद (चयनित भाषा UI)" },
    "ja": { title: "外部サイト (Paiza/Daijob 等)", trans: "翻訳する (閲覧用)　※ログインや応募フォームや回答はエラーになります。", raw: "翻訳しない (応募・学習用。※ログインや応募フォームが正常に動作します。)", google: "選択言語の Google 翻訳サイトへ" }
  };

    var GT_SITE_PICK_I18N = {
    "ja": { title: "Google翻訳サイトを表示しますか？", cancel: "元の画面に戻る" },
    "en": { title: "Display the Google Translate site?", cancel: "Back to previous screen" },
    "zh-CN": { title: "要显示 Google 翻译网站吗？", cancel: "返回上一页" },
    "zh-TW": { title: "要顯示 Google 翻譯網站嗎？", cancel: "返回上一頁" },
    "ko": { title: "Google 번역 사이트를 표시할까요?", cancel: "이전 화면으로 돌아가기" },
    "fr": { title: "Afficher le site Google Traduction ?", cancel: "Retour à l'écran précédent" },
    "de": { title: "Google Übersetzer-Website anzeigen?", cancel: "Zurück zum vorherigen Bildschirm" },
    "es": { title: "¿Mostrar el sitio de Google Traductor?", cancel: "Volver a la pantalla anterior" },
    "pt": { title: "Exibir o site do Google Tradutor?", cancel: "Voltar à tela anterior" },
    "it": { title: "Visualizzare il sito di Google Traduttore?", cancel: "Torna alla schermata precedente" },
    "ru": { title: "Открыть сайт Google Переводчика?", cancel: "Вернуться к предыдущему экрану" },
    "ar": { title: "هل تريد عرض موقع ترجمة Google؟", cancel: "العودة إلى الشاشة السابقة" },
    "hi": { title: "Google अनुवाद साइट दिखाएं?", cancel: "पिछली स्क्रीन पर वापस जाएं" },
    "th": { title: "แสดงเว็บไซต์ Google แปลภาษา?", cancel: "กลับไปยังหน้าจอก่อนหน้า" },
    "vi": { title: "Hiển thị trang Google Dịch?", cancel: "Quay lại màn hình trước" },
    "id": { title: "Tampilkan situs Google Terjemahan?", cancel: "Kembali ke layar sebelumnya" },
    "uk": { title: "Відкрити сайт Google Перекладача?", cancel: "Повернутися до попереднього екрана" },
    "tr": { title: "Google Çeviri sitesi açılsın mı?", cancel: "Önceki ekrana dön" },
    "nl": { title: "Google Translate-site weergeven?", cancel: "Terug naar vorig scherm" },
    "pl": { title: "Wyświetlić stronę Tłumacza Google?", cancel: "Wróć do poprzedniego ekranu" }
  };

  function getGtSitePickDict(lang) {
    var k = lang || "en";
    var prefix = k.split("-")[0];
    return GT_SITE_PICK_I18N[k] || GT_SITE_PICK_I18N[prefix] || GT_SITE_PICK_I18N.en;
  }

  function buildGoogleTranslateSiteUrl(pageUrl, tl, uiHl) {
    // 仕様:
    //  - 日本語選択時: https://translate.google.co.jp/?hl=ja&sl=ja&tl=en&text=<pageUrl>&op=translate
    //  - 外国語選択時: https://translate.google.<tld>/?hl=<tl>&sl=ja&tl=<tl>&text=<pageUrl>&op=translate
    //  - hl (UI言語) = 日本語なら ja、外国語ならその言語コード
    //  - sl (原文言語) = ja 固定
    //  - tl (翻訳先言語) = 日本語なら en、外国語ならその言語コード
    //  - text = 選択したサイトURL
    var isJapanese = (tl === "ja");
    var targetLang = isJapanese ? "en" : tl;
    var uiLang = isJapanese ? "ja" : tl;
    var tldMap = {
      // 主要言語（Google翻訳の国別ドメイン対応）
      "en": "com", "fr": "fr", "de": "de", "es": "es", "it": "it", "pt": "pt", "pt-BR": "com.br",
      "ru": "ru", "ko": "co.kr", "zh-CN": "cn", "zh-TW": "com.tw",
      "ar": "com.sa", "hi": "co.in", "th": "co.th", "vi": "com.vn", "id": "co.id",
      "tr": "com.tr", "nl": "nl", "pl": "pl", "uk": "com.ua", "ja": "co.jp",
      // ヨーロッパ言語
      "cs": "cz", "da": "dk", "fi": "fi", "sv": "se", "no": "no", "hu": "hu",
      "ro": "ro", "bg": "bg", "sk": "sk", "sl": "si", "hr": "hr", "sr": "rs",
      "lt": "lt", "lv": "lv", "et": "ee", "el": "gr", "ca": "cat", "eu": "com",
      "gl": "com", "is": "is", "ga": "ie", "cy": "co.uk", "mt": "com.mt",
      // アジア言語
      "bn": "com.bd", "ur": "com.pk", "fa": "com", "he": "co.il", "iw": "co.il",
      "ta": "co.in", "te": "co.in", "ml": "co.in", "kn": "co.in", "mr": "co.in",
      "gu": "co.in", "pa": "co.in", "ne": "com.np", "si": "lk", "my": "com.mm",
      "km": "com.kh", "lo": "la", "mn": "mn", "ka": "ge", "hy": "am", "az": "az",
      "kk": "kz", "uz": "co.uz", "ky": "com", "tg": "com", "tk": "com",
      // 中東・アフリカ言語
      "sw": "co.ke", "am": "com.et", "ha": "com", "yo": "com", "ig": "com",
      "zu": "co.za", "xh": "co.za", "af": "co.za", "st": "com", "sn": "com",
      "ny": "com", "mg": "mg", "so": "com", "ps": "com", "ku": "com", "sd": "com",
      // アメリカ大陸言語
      "es-MX": "com.mx", "es-AR": "com.ar", "es-CO": "com.co", "es-CL": "cl",
      "es-PE": "com.pe", "es-VE": "co.ve", "qu": "com", "gn": "com", "ay": "com",
      // 太平洋・その他
      "fil": "com.ph", "tl": "com.ph", "ms": "com.my", "jv": "com", "su": "com",
      "ceb": "com", "haw": "com", "sm": "ws", "mi": "co.nz", "to": "to", "fj": "com.fj",
      // ヨーロッパ少数言語
      "lb": "lu", "be": "by", "mk": "mk", "sq": "al", "bs": "ba", "eo": "com",
      "la": "com", "yi": "com", "gd": "com", "fy": "nl", "co": "fr",
      // アジア少数言語
      "hmn": "com", "haw": "com", "sm": "ws", "ny": "com", "sn": "com",
      "st": "com", "tn": "com", "ts": "com", "ti": "com.er", "om": "com.et",
      "rw": "rw", "lg": "com", "ak": "com", "ee": "com", "tw": "com", "gaa": "com",
      // その他約130言語対応（Google翻訳がサポートする全言語）
      "ht": "ht", "hmn": "com", "ig": "com", "ilo": "com.ph", "kri": "com",
      "ku": "com", "ckb": "com", "la": "com", "ln": "com", "lua": "com",
      "lus": "com", "mai": "co.in", "mni-Mtei": "co.in", "nso": "co.za",
      "or": "co.in", "om": "com.et", "ps": "com", "sm": "ws", "sa": "co.in",
      "gd": "com", "nso": "co.za", "sd": "com", "sn": "com", "so": "com",
      "ti": "com.er", "ts": "com", "tt": "ru", "ug": "com", "hsb": "de"
    };
    var tld = tldMap[tl] || (isJapanese ? "co.jp" : "com");
    if (isJapanese) tld = "co.jp";
    try {
      var dest = new URL("https://translate.google." + tld + "/");
      dest.searchParams.set("hl", uiLang);
      dest.searchParams.set("sl", "ja");
      dest.searchParams.set("tl", targetLang);
      dest.searchParams.set("text", pageUrl);
      dest.searchParams.set("op", "translate");
      return dest.toString();
    } catch (e) {
      return "https://translate.google." + tld + "/?hl=" + encodeURIComponent(uiLang) + "&sl=ja&tl=" + encodeURIComponent(targetLang) + "&text=" + encodeURIComponent(pageUrl) + "&op=translate";
    }
  }

  function wireAcGtSitePickModal() {
    if (window.__acGtSitePickWired) return;
    window.__acGtSitePickWired = true;
    var m = document.getElementById("acGtSitePickModal");
    if (!m) return;
    m.addEventListener("click", function(ev) {
      if (!m.classList.contains("is-open")) return;
      var dismiss = ev.target.getAttribute && ev.target.getAttribute("data-ac-gt-site-dismiss") === "1";
      if (dismiss || ev.target.id === "acGtSitePickCancel") {
        if (typeof window.__acGtSitePickOnCancel === "function") window.__acGtSitePickOnCancel();
      }
    });
    document.addEventListener("keydown", function(ev) {
      if (ev.key !== "Escape" || !m.classList.contains("is-open")) return;
      if (typeof window.__acGtSitePickOnCancel === "function") window.__acGtSitePickOnCancel();
    });
  }
 
  function translateSwitchUI() {
    var sw = document.getElementById("ext-link-switch");
    if (!sw) return;
    if (sw.classList.contains("minimized")) return;
    var targetLang = LANG || currentLang() || "ja";
    var prefix = targetLang.split("-")[0];
    var dict = SWITCH_I18N[targetLang] || SWITCH_I18N[prefix] || SWITCH_I18N["ja"];
    
    // 強制表示
    sw.setAttribute('style', 'display: flex !important; visibility: visible !important; opacity: 1 !important;');
    
    var titleEl = sw.querySelector("strong");
    if (titleEl) titleEl.textContent = dict.title;
    var labels = sw.querySelectorAll("label");
    if (labels.length >= 3) {
      labels[0].childNodes.forEach(function(n){ if(n.nodeType === 3 && n.nodeValue.trim().length > 0) n.nodeValue = " " + dict.trans; });
      labels[1].childNodes.forEach(function(n){ if(n.nodeType === 3 && n.nodeValue.trim().length > 0) n.nodeValue = " " + dict.raw; });
      labels[2].childNodes.forEach(function(n){ if(n.nodeType === 3 && n.nodeValue.trim().length > 0) n.nodeValue = " " + dict.google; });
    }
  }

  function ensureSwitchVisible() {
    var sw = document.getElementById("ext-link-switch");
    if(!sw) return;
    // 既に閉じられている場合は何もしない
    if (sw.classList.contains("minimized")) return;
    sw.style.display = "flex";
    sw.style.visibility = "visible";
    sw.style.opacity = "1";
    // 確実に表示されるように !important をつける
    sw.setAttribute('style', 'display: flex !important; visibility: visible !important; opacity: 1 !important;');
  }
 
  /* [PATCH] スマホ自動収納調整: パネルがビューポートに収まるよう動的計算 */
  function fitPanel(){
    var sw = document.getElementById("ext-link-switch");
    if(!sw || sw.classList.contains("minimized")) return;
    var vv = window.visualViewport;
    var vw = vv ? vv.width  : (window.innerWidth  || document.documentElement.clientWidth);
    var vh = vv ? vv.height : (window.innerHeight || document.documentElement.clientHeight);
    var offsetTop = vv ? vv.offsetTop : 0;

    var marginX = vw <= 600 ? 5 : 10;
    var marginY = vw <= 600 ? 6 : 8;
    var availH = Math.max(180, vh - marginY * 2);

    // visualViewport offsetTop がある場合のみ --el-top を上書き、それ以外はCSSデフォルト
    if(vv && offsetTop > 0){
      sw.style.setProperty("--el-top", (offsetTop + marginY) + "px");
    } else {
      sw.style.removeProperty("--el-top");
    }

    // max-height を動的に
    var topNow = parseFloat(getComputedStyle(sw).top) || 0;
    sw.style.maxHeight = Math.max(180, vh - topNow - marginY) + "px";

    // 段階的フォント縮小をリセット
    ["--el-font","--el-gap","--el-pad-y-top","--el-pad-y-bot","--el-line"].forEach(function(p){
      sw.style.removeProperty(p);
    });
    // はみ出している場合のみ段階適用
    var shrinkSteps = [
      // [PATCH v3] CLOSEはパネル内に入ったため、pBot は通常値 (旧値2.7rem/2.3rem/2.0remは廃止)
      { font:".82rem", gap:"6px", pTop:"10px", pBot:"10px", line:"1.28" },
      { font:".76rem", gap:"5px", pTop:"8px",  pBot:"8px",  line:"1.22" },
      { font:".7rem",  gap:"4px", pTop:"6px",  pBot:"6px",  line:"1.18" }
    ];
    var step = -1;
    while(sw.scrollHeight > availH && step < shrinkSteps.length - 1){
      step++;
      var st = shrinkSteps[step];
      sw.style.setProperty("--el-font", st.font);
      sw.style.setProperty("--el-gap",  st.gap);
      sw.style.setProperty("--el-pad-y-top", st.pTop);
      sw.style.setProperty("--el-pad-y-bot", st.pBot);
      sw.style.setProperty("--el-line", st.line);
    }

    /* === [PATCH2] スマホ縦向き: 横幅いっぱい自動調整 === */
    var isMobile = vw <= 600;
    var isPortrait = vh > vw;
    if(isMobile && isPortrait){
      // safe-area 値を取得
      var probe = document.createElement("div");
      probe.style.cssText = "position:fixed;left:env(safe-area-inset-left,0px);right:env(safe-area-inset-right,0px);visibility:hidden;pointer-events:none;";
      document.body.appendChild(probe);
      var probeRect = probe.getBoundingClientRect();
      document.body.removeChild(probe);
      var safeLeft  = Math.max(0, probeRect.left);
      var safeRight = Math.max(0, vw - probeRect.right);

      var sideMargin = 4; // 画面端からの最小マージン (px)
      var leftPx  = safeLeft  + sideMargin;
      var rightPx = safeRight + sideMargin;

      // パネル幅いっぱいに
      sw.style.setProperty("left",      leftPx  + "px", "important");
      sw.style.setProperty("right",     rightPx + "px", "important");
      sw.style.setProperty("width",     "auto",         "important");
      sw.style.setProperty("max-width", "none",         "important");
      sw.style.setProperty("box-sizing", "border-box");
    } else {
      // 縦向きでない場合は left を解除して right ベース配置に戻す
      sw.style.removeProperty("left");
      sw.style.removeProperty("width");
      sw.style.removeProperty("max-width");
    }

    /* CLOSEボタンはパネル内4番目のラジオラベルに統合されたため、
       個別のfixed位置調整は不要。パネル本体の max-height + overflow-y:auto に乗る。 */

    /* === [PATCH2] パネル本体が画面右端からはみ出さないよう保険 === */
    var swRect = sw.getBoundingClientRect();
    if(swRect.right > vw - 2){
      var panelOver = swRect.right - (vw - 2);
      var csSw = getComputedStyle(sw);
      var currentSwRight = parseFloat(csSw.right) || 10;
      sw.style.setProperty("right", (currentSwRight + panelOver) + "px", "important");
    }
    if(swRect.left < 2 && !(isMobile && isPortrait)){
      // 左に飛び出している (縦向き横幅いっぱい時を除く)
      var panelLeftOver = 2 - swRect.left;
      sw.style.setProperty("left", panelLeftOver + "px", "important");
    }
  }
  // 公開 (Google翻訳初期化後にも呼ぶため)
  window.__acAruaruFitPanel = fitPanel;

  // リサイズ/向き変更/キーボード表示に rAF スロットルで追従
  var __fitRaf = 0;
  function fitPanelThrottled(){
    if(__fitRaf) return;
    __fitRaf = requestAnimationFrame(function(){ __fitRaf = 0; fitPanel(); });
  }
  window.addEventListener("resize", fitPanelThrottled, { passive:true });
  window.addEventListener("orientationchange", function(){
    setTimeout(fitPanel, 150);
    setTimeout(fitPanel, 400);
  });
  if(window.visualViewport){
    window.visualViewport.addEventListener("resize", fitPanelThrottled, { passive:true });
    window.visualViewport.addEventListener("scroll", fitPanelThrottled, { passive:true });
  }
  window.addEventListener("load", function(){ setTimeout(fitPanel, 50); });
  if(document.fonts && document.fonts.ready){
    document.fonts.ready.then(fitPanel).catch(function(){});
  }

  /* [PATCH v3] OPEN/CLOSE 制御 - CLOSE はパネル内4番目のラジオラベル
     旧 button#ext-link-switch-close は HTML から削除済み */
  function initToggleLogic() {
    var sw         = document.getElementById("ext-link-switch");
    var openBtn    = document.getElementById("ext-link-switch-toggle");
    var closeRadio = document.getElementById("ext-link-switch-close-radio");
    if (!sw || !openBtn) return;

    // 二重バインド防止
    if(sw.__acToggleBound) return;
    sw.__acToggleBound = true;
    openBtn.__acBound = true;

    // BUG FIX: もし古いキャッシュHTMLで旧 #ext-link-switch-close が残っていたら強制非表示
    var legacyBtn = document.getElementById("ext-link-switch-close");
    if(legacyBtn && legacyBtn.tagName === "BUTTON"){
      legacyBtn.style.cssText = "position:absolute!important;left:-99999px!important;top:-99999px!important;width:1px!important;height:1px!important;opacity:0!important;visibility:hidden!important;pointer-events:none!important;display:block!important;z-index:-1!important;";
      legacyBtn.setAttribute("aria-hidden", "true");
      legacyBtn.setAttribute("tabindex", "-1");
    }

    // 共通: 多重発火ガード付き CLOSE
    var closingLock = false;
    function doClose(ev){
      if(closingLock) return;
      closingLock = true;
      setTimeout(function(){ closingLock = false; }, 350);
      if(ev){
        try{ ev.preventDefault(); }catch(e){}
        try{ ev.stopPropagation(); }catch(e){}
      }
      sw.classList.add("minimized");
      sw.style.setProperty("display", "none", "important");
      // CLOSEラジオの選択を解除し、元のモードラジオに戻す (次回OPEN時の整合性)
      if(closeRadio){
        try{ closeRadio.checked = false; }catch(e){}
        var prevMode = sw.__acPrevMode || "translate";
        var prevRadio = document.querySelector('input[name="ext_mode"][value="' + prevMode + '"]');
        if(prevRadio){ try{ prevRadio.checked = true; }catch(e){} }
      }
      openBtn.style.setProperty("display", "block", "important");
      openBtn.style.setProperty("visibility", "visible", "important");
      openBtn.style.setProperty("opacity", "1", "important");
      openBtn.style.setProperty("position", "fixed", "important");
      openBtn.style.setProperty("top", "var(--aruaru-gadget-top)", "important");
      openBtn.style.setProperty("right", "calc(env(safe-area-inset-right, 0px) + 10px)", "important");
      openBtn.style.setProperty("left", "auto", "important");
      openBtn.style.setProperty("z-index", "2147483647", "important");
      openBtn.style.setProperty("pointer-events", "auto", "important");
    }
    function doOpen(ev){
      if(ev){
        try{ ev.preventDefault(); }catch(e){}
        try{ ev.stopPropagation(); }catch(e){}
      }
      sw.classList.remove("minimized");
      sw.style.setProperty("display", "flex", "important");
      sw.style.setProperty("visibility", "visible", "important");
      sw.style.setProperty("opacity", "1", "important");
      openBtn.style.setProperty("display", "none", "important");
      translateSwitchUI();
      ensureSwitchVisible();
      requestAnimationFrame(fitPanel);
    }

    // === CLOSE: 4番目のラジオ change/click で発火 ===
    if(closeRadio && !closeRadio.__acBound){
      closeRadio.__acBound = true;
      closeRadio.addEventListener("change", function(ev){
        if(closeRadio.checked){
          doClose(ev);
        }
      }, false);
      closeRadio.addEventListener("click", function(ev){
        closeRadio.checked = true;
        doClose(ev);
      }, false);
      closeRadio.addEventListener("keydown", function(ev){
        if(ev.key === "Enter" || ev.key === " " || ev.key === "Spacebar"){
          closeRadio.checked = true;
          doClose(ev);
        }
      }, false);
    }

    // === OPEN: click ===
    openBtn.addEventListener("click", function(ev){ doOpen(ev); }, false);
    openBtn.addEventListener("keydown", function(ev){
      if(ev.key === "Enter" || ev.key === " " || ev.key === "Spacebar"){
        doOpen(ev);
      }
    }, false);
    try{
      if(!openBtn.getAttribute("role")) openBtn.setAttribute("role", "button");
      if(openBtn.getAttribute("tabindex") === null) openBtn.setAttribute("tabindex", "0");
      if(!openBtn.getAttribute("aria-label")) openBtn.setAttribute("aria-label", "翻訳設定パネルを開く");
    }catch(e){}

    // モードラジオが選ばれたら sw.__acPrevMode に記憶 (CLOSE後の復元用)
    var modeRadios = sw.querySelectorAll('input[name="ext_mode"]');
    for(var i=0; i<modeRadios.length; i++){
      var r = modeRadios[i];
      if(r.value !== "__close__"){
        if(r.checked) sw.__acPrevMode = r.value;
        r.addEventListener("change", function(){
          if(this.checked && this.value !== "__close__"){
            sw.__acPrevMode = this.value;
          }
        }, false);
      }
    }
  }

  // DOMContentLoaded で必ず初期化 (Google翻訳スクリプトのロード失敗でもCLOSEが効くように)
  if(document.readyState === "loading"){
    document.addEventListener("DOMContentLoaded", initToggleLogic);
  } else {
    initToggleLogic();
  }
 
  function initExtModeSwitch() {
    wireAcGtSitePickModal();
    initToggleLogic();
    var radios = document.querySelectorAll('input[name="ext_mode"]');
    var isProxy = (location.hostname.indexOf(".translate.goog") !== -1);
    
    // 現在の状態に合わせてチェックを入れる
    var isForcedRaw = false;
    try { isForcedRaw = sessionStorage.getItem('ext_mode_forced_raw') === '1'; } catch(e) {}
    for (var i = 0; i < radios.length; i++) {
      if (radios[i].value === "translate" && !isForcedRaw && (isProxy || (LANG && LANG !== "ja"))) radios[i].checked = true;
      if (radios[i].value === "raw" && (isForcedRaw || (!isProxy && (!LANG || LANG === "ja")))) radios[i].checked = true;
    }
 
    for (var i = 0; i < radios.length; i++) {
      radios[i].addEventListener("change", function() {
        // [PATCH v2] 4番目の CLOSE ラジオは閉じる動作専用 (initToggleLogic 側で処理済み)
        if (this.value === "__close__") {
          return;
        }
        // LANG (PHP/JS で確定済み) → sessionStorage (前回選択) → localStorage → 'en' の順で
        // 「翻訳する」時の対象言語を解決する。
        // 注意: ここで currentLang() を呼ばない。currentLang は別 IIFE 内のローカル関数で、
        // この IIFE からは参照できず、ReferenceError でハンドラが中断する。
        // 過去にこのバグで「翻訳しない→翻訳する」を選んでも何も起きない不具合が出ていた。
        var target = LANG || "";
        if (!target) {
          try { target = sessionStorage.getItem("ac_last_lang") || sessionStorage.getItem("selectedLang") || localStorage.getItem("lastSelectedLang") || localStorage.getItem("selectedLang") || ""; } catch(e) {}
        }
        if (!target) target = "en";
        if (this.value === "raw") {
          // 翻訳解除：クッキーを完全削除して元URLへ確実に戻る
          try { sessionStorage.setItem('ext_mode_forced_raw', '1'); } catch(e) {}
          try {
            document.cookie = "googtrans=; path=/; max-age=0";
            document.cookie = "googtrans=/ja/ja; path=/; max-age=0";
            var hh = location.hostname;
            var pp = hh.split('.');
            if (pp.length >= 2) {
              var dom = '.' + pp.slice(-2).join('.');
              document.cookie = "googtrans=; path=/; domain=" + dom + "; max-age=0";
              document.cookie = "googtrans=/ja/ja; path=/; domain=" + dom + "; max-age=0";
            }
          } catch(e) {}
          // sessionStorage / localStorage の言語フラグも全消去
          try {
            sessionStorage.removeItem('selectedLang');
            localStorage.removeItem('selectedLang');
            sessionStorage.removeItem('ac_last_lang');
            sessionStorage.removeItem('__ac_aruaru_reloaded');
          } catch(e) {}
 
          if (isProxy) {
            var raw = location.href.replace(".translate.goog", "").replace(/-/g, ".");
            try {
              var u = new URL(raw);
              ['_x_tr_sl','_x_tr_tl','_x_tr_hl','_x_tr_pto','_x_tr_hist'].forEach(function(k){ u.searchParams.delete(k); });
              raw = u.toString();
            } catch(e) {}
            location.replace(raw);
          } else {
            var u = new URL(location.href);
            u.searchParams.delete('lang');
            var hashParts = (u.hash || '').replace(/^#/, '').split('&').filter(function(p){ return p && p.indexOf('translateto=') !== 0; });
            u.hash = hashParts.length ? '#' + hashParts.join('&') : '';
            // hashだけ変えても再読み込みされないため、replaceState + reload で確実に元状態に戻す
            try { history.replaceState(null, '', u.toString()); } catch(e) {}
            location.reload();
          }
        } else if (this.value === "google") {
          var tl = LANG || "";
          if (!tl) {
            try { tl = sessionStorage.getItem("ac_last_lang") || sessionStorage.getItem("selectedLang") || localStorage.getItem("selectedLang") || ""; } catch(e) {}
          }
          if (!tl) tl = "en";
          if (tl === "ja") tl = "en";
          var uiHl = (tl === "iw" || tl === "he") ? "he" : tl;
          var dict = getGtSitePickDict(tl);
          var gcNav = tl;

          // 現在ページの素のURL（translate.goog プロキシを剥がしたもの）
          function getGtSourcePageUrl() {
            try {
              if (isProxy) {
                var rawHref = location.href.replace(/\.translate\.goog/, "").replace(/-/g, ".");
                try {
                  var u0 = new URL(rawHref);
                  ['_x_tr_sl','_x_tr_tl','_x_tr_hl','_x_tr_pto','_x_tr_hist'].forEach(function(k){ u0.searchParams.delete(k); });
                  u0.searchParams.delete('lang');
                  var hp0 = (u0.hash || '').replace(/^#/, '').split('&').filter(function(p){ return p && p.indexOf('translateto=') !== 0; });
                  u0.hash = hp0.length ? '#' + hp0.join('&') : '';
                  return u0.toString();
                } catch(e2) { return rawHref; }
              }
              var u = new URL(location.href);
              u.searchParams.delete('lang');
              var hp = (u.hash || '').replace(/^#/, '').split('&').filter(function(p){ return p && p.indexOf('translateto=') !== 0; });
              u.hash = hp.length ? '#' + hp.join('&') : '';
              return u.toString();
            } catch(e1) { return location.href; }
          }

          // 各サイトへの遷移URL（jaは素のURL、他言語は lang+hash）
          function destAudiocafe() {
            if (gcNav === 'ja') return 'https://audiocafe.tokyo/?lang=ja';
            return 'https://translate.google.com/translate?sl=ja&tl=' + encodeURIComponent(gcNav) + '&u=' + encodeURIComponent('https://audiocafe.tokyo/');
          }
          function destRayTop() {
            if (gcNav === 'ja') return 'https://audiocafe.tokyo/top/?lang=ja';
            return 'https://audiocafe.tokyo/top/?lang=' + encodeURIComponent(gcNav) + '#translateto=' + encodeURIComponent(gcNav);
          }
          function destAruaru() {
            if (gcNav === 'ja') return 'https://audiocafe.tokyo/aruaru/';
            return 'https://audiocafe.tokyo/aruaru/?lang=' + encodeURIComponent(gcNav) + '#translateto=' + encodeURIComponent(gcNav);
          }
          function destAruaruLady() {
            if (gcNav === 'ja') return 'https://audiocafe.tokyo/aruaru-lady/';
            return 'https://audiocafe.tokyo/aruaru-lady/?lang=' + encodeURIComponent(gcNav) + '#translateto=' + encodeURIComponent(gcNav);
          }

          var siteRows = [
            { href: destAudiocafe(),  label: 'audiocafe.tokyo' },
            { href: destRayTop(),     label: 'audiocafe.tokyo/top' },
            { href: destAruaru(),     label: 'audiocafe.tokyo/aruaru' },
            { href: destAruaruLady(), label: 'audiocafe.tokyo/aruaru-lady' }
          ];

          function revertRadios() {
            for (var j = 0; j < radios.length; j++) {
              if (radios[j].value === ((isProxy || (LANG && LANG !== "ja")) ? "translate" : "raw")) radios[j].checked = true;
            }
          }
          function closePick() {
            var modal = document.getElementById("acGtSitePickModal");
            if (modal) {
              modal.classList.remove("is-open");
              modal.setAttribute("hidden", "");
            }
            try { delete window.__acGtSitePickOnCancel; } catch (e2) { window.__acGtSitePickOnCancel = undefined; }
          }
          function openPick() {
            var modal = document.getElementById("acGtSitePickModal");
            var titleEl = document.getElementById("acGtSitePickTitle");
            var cancelEl = document.getElementById("acGtSitePickCancel");
            var wrap = document.getElementById("acGtSitePickBtns");
            if (!modal || !titleEl || !cancelEl || !wrap) {
              // モーダルが見つからない場合はフォールバック: 新タブでGoogle翻訳を開く
              try { window.open(buildGoogleTranslateSiteUrl(getGtSourcePageUrl(), tl, uiHl), '_blank', 'noopener,noreferrer'); } catch(e) {}
              setTimeout(revertRadios, 50);
              return;
            }
            titleEl.textContent = dict.title;
            cancelEl.textContent = dict.cancel;
            wrap.innerHTML = "";
            window.__acGtSitePickOnCancel = function() {
              closePick();
              revertRadios();
            };
            // まず新タブでGoogle翻訳サイトを開く（top と同仕様）
            try { window.open(buildGoogleTranslateSiteUrl(getGtSourcePageUrl(), tl, uiHl), '_blank', 'noopener,noreferrer'); } catch(eGt) {}
            // モーダルにサイト選択ボタンを並べる
            for (var si = 0; si < siteRows.length; si++) {
              (function(row) {
                var b = document.createElement("button");
                b.type = "button";
                b.className = "ac-gt-site-modal__btn";
                b.textContent = row.label;
                b.addEventListener("click", function() {
                  closePick();
                  revertRadios();
                  location.href = row.href;
                });
                wrap.appendChild(b);
              })(siteRows[si]);
            }
            modal.removeAttribute("hidden");
            modal.classList.add("is-open");
            setTimeout(revertRadios, 0);
          }
          openPick();
        } else {
          // 翻訳する：googtransクッキーをセットしてリロード（ウィジェット方式）
          try { sessionStorage.removeItem('ext_mode_forced_raw'); } catch(e) {}
          if (target === "ja") target = "en";
          var v = "/ja/" + target;
          try {
            document.cookie = "googtrans=" + v + "; path=/; max-age=31536000";
            var hh = location.hostname;
            var pp = hh.split('.');
            if (pp.length >= 2) {
              document.cookie = "googtrans=" + v + "; path=/; domain=." + pp.slice(-2).join('.') + "; max-age=31536000";
            }
            sessionStorage.setItem('selectedLang', target);
            localStorage.setItem('selectedLang', target);
            sessionStorage.setItem('ac_last_lang', target);
            sessionStorage.removeItem('__ac_aruaru_reloaded');
          } catch(e) {}
          // hashに translateto を付けて再起動 (getLang() で確実に拾うため)
          try {
            var u = new URL(location.href);
            var hashParts = (u.hash || '').replace(/^#/, '').split('&').filter(function(p){ return p && p.indexOf('translateto=') !== 0; });
            hashParts.push('translateto=' + encodeURIComponent(target));
            u.hash = '#' + hashParts.join('&');
            history.replaceState(null, '', u.toString());
          } catch(e) {}
          location.reload();
        }
      });
    }
  }
 
  window.googleTranslateElementInit = function() {
    new google.translate.TranslateElement({ pageLanguage: "ja", layout: google.translate.TranslateElement.InlineLayout.SIMPLE, autoDisplay: false }, "google_translate_element");
    initExtModeSwitch();
    
    // Cookieを明示的にセットしてウィジェットを確実に動作させる
    if(LANG && LANG !== "ja"){
      try{
        var cv = "/ja/" + LANG;
        document.cookie = "googtrans=" + cv + "; path=/; max-age=31536000";
        var h = location.hostname;
        var parts = h.split('.');
        if(parts.length >= 2){
           document.cookie = "googtrans=" + cv + "; path=/; domain=." + parts.slice(-2).join('.') + "; max-age=31536000";
        }
      }catch(e){}
      var attempts = 0;
      var timer = setInterval(function(){
        var sel = document.querySelector(".goog-te-combo");
        if(sel){
          clearInterval(timer);
          if(sel.value !== LANG) {
            sel.value = LANG;
            try {
              var ev = document.createEvent('HTMLEvents');
              ev.initEvent('change', true, true);
              sel.dispatchEvent(ev);
            } catch(e){}
          }
          translateSwitchUI();
          ensureSwitchVisible();
          if(typeof window.__acAruaruFitPanel === "function") window.__acAruaruFitPanel();
        }
        if(++attempts > 30) {
          clearInterval(timer);
          translateSwitchUI();
          ensureSwitchVisible();
          if(typeof window.__acAruaruFitPanel === "function") window.__acAruaruFitPanel();
        }
      }, 300);
    } else {
      ensureSwitchVisible();
      if(typeof window.__acAruaruFitPanel === "function") window.__acAruaruFitPanel();
    }
  };
  var s = document.createElement("script");
  s.src = "https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit";
  s.onerror = function() {};
  document.body.appendChild(s);
})();
</script>
 
<script>
(function(){
  "use strict";
  // 重要: ここは <script> タグの直下に出力される素の JS。PHP heredoc 由来の \\.
  // 表記は JS 正規表現リテラル内では backslash+任意文字となり、ドメイン名のドットに
  // マッチしないため、\. に修正する。
  var SELF_DOMAIN = /(^|\.)(ala\.tokyo|aon\.co\.jp|aon\.tokyo|audiocafe\.tokyo|icpo\.tokyo|ion\.tokyo|nanosoft\.jp|nasa\.tokyo|raysoft\.jp)$/i;
  var VIDEO_SNS_HOSTS = /(?:^|\.)(?:youtube\.com|youtu\.be|facebook\.com|fb\.watch|drive\.google\.com|docs\.google\.com|instagram\.com|twitter\.com|x\.com|tiktok\.com|vimeo\.com|nicovideo\.jp)$/i;
  var GOOGLE_HOSTS    = /^(?:www\.)?google\.[a-z.]+$/i;
  var IMG_EXT_RE      = /\.(?:jpe?g|png|gif|webp|svg|bmp|ico|avif|heic|heif)(?:[?#]|$)/i;
  var TRANSLATE_GOOG  = '.translate.goog';
 
  function currentLang(){
    try{
      var sp = new URLSearchParams(location.search);
      var tr_tl = sp.get("_x_tr_tl");
      if (tr_tl) return tr_tl;
      var h = (location.hash || "").match(/translateto=([a-zA-Z\-]+)/);
      if (h) return decodeURIComponent(h[1]);
      var lp = sp.get("lang");
      if (lp) return lp;
      var m = document.cookie.match(/(?:^|;\s*)googtrans=\/[^/]+\/([^;]+)/);
      if (m && m[1]) return decodeURIComponent(m[1]).trim();
      return sessionStorage.getItem("ac_last_lang") || sessionStorage.getItem("selectedLang") || localStorage.getItem("selectedLang") || "";
    }catch(e){ return ''; }
  }
 
  function getExtMode() {
    var radio = document.querySelector('input[name="ext_mode"]:checked');
    if (radio) return radio.value;
    // BUG修正: デフォルト を 'raw'（翻訳しない日本語）にする。
    // URL（?_x_tr_tl= / #translateto= / ?lang=）または Cookie に言語が明示されている場合のみ
    // その言語で翻訳する。ただし Cookie だけでは信用しない（古い値が残る可能性）。
    var sp = new URLSearchParams(location.search);
    var urlLang = sp.get('_x_tr_tl') || sp.get('lang');
    var hashLang = (location.hash || '').match(/translateto=([a-zA-Z\-]+)/) ? RegExp.$1 : '';
    var hasUrlExplicit = !!(urlLang || hashLang);
    if (!hasUrlExplicit) return 'raw'; // URLに言語指定なし → 日本語（翻訳しない）
    var targetLang = currentLang();
    if (targetLang && targetLang !== 'ja') return 'translate';
    return 'raw';
  }
 
  function restoreFromProxy(url){
    try{
      var u = new URL(url, location.href);
      if(u.hostname.indexOf('.translate.goog') === -1) return null;
      var orig = u.hostname.replace('.translate.goog','').replace(/-/g,'.');
      ['_x_tr_sl','_x_tr_tl','_x_tr_hl','_x_tr_pto','_x_tr_hist'].forEach(function(k){ u.searchParams.delete(k); });
      var search = u.searchParams.toString();
      return 'https://' + orig + u.pathname + (search ? '?' + search : '') + u.hash;
    }catch(e){ return null; }
  }
 
  function toTranslateProxy(url, targetLang){
    if(!url) return url;
    if(!targetLang || targetLang === 'ja') return url;
    if(/^(?:mailto:|tel:|javascript:|#)/i.test(url)) return url;
    if(!/^https?:\/\//i.test(url)) return url;
    try{
      var u = new URL(url, location.href);
      var host = u.hostname.toLowerCase();
 
      if(host.length > TRANSLATE_GOOG.length && host.slice(-TRANSLATE_GOOG.length) === TRANSLATE_GOOG){
        var restored = restoreFromProxy(url);
        if(restored) return toTranslateProxy(restored, targetLang);
        return url;
      }
 
      if(VIDEO_SNS_HOSTS.test(host)) return url;
      if(GOOGLE_HOSTS.test(host))    return url;
      if(host === 'translate.google.com' || host === 'translate.googleusercontent.com') return url;
      if(IMG_EXT_RE.test(u.pathname)) return url;
 
      // Special rules for Daijob
      if(host === 'www.daijob.com' || host === 'daijob.com'){
        // 日本語を選択時に、 https://www.daijob.com/ はそのままGoogle翻訳しないで表示
        if(targetLang === 'ja') return url;
        // /en/ ページは常に Google翻訳を通さず、そのまま表示する
        if(u.pathname.indexOf('/en/') === 0) return url;
      }
      
      // 外部サイト翻訳モード対応
      var sw = document.querySelector('input[name="ext_mode"]:checked');
      var extMode = sw ? sw.value : 'translate';
      if(extMode === 'raw' && !SELF_DOMAIN.test(host) && !VIDEO_SNS_HOSTS.test(host) && !GOOGLE_HOSTS.test(host)){
         return url; // 翻訳しないモード時は外部サイトへのリンクは素のURLのままにする
      }
 
      // 1. 内部リンク (Google翻訳ウィジェット内蔵)
      if(SELF_DOMAIN.test(host) || u.hostname === location.hostname){
        var p = u.pathname || '/';
        var isNonHtml = /\.(?:pdf|docx?|xlsx?|pptx?|txt|csv|md|rtf|odt|ods|odp)$/i.test(p.toLowerCase());
        if(isNonHtml){
          return 'https://translate.google.com/translate?sl=ja&tl=' + encodeURIComponent(targetLang) + '&u=' + encodeURIComponent(u.toString());
        }
        var hash = u.hash || '';
        if(hash.indexOf('translateto=') === -1){
          var parts = hash.replace(/^#/,'').split('&').filter(function(s){ return s; });
          parts.push('translateto=' + encodeURIComponent(targetLang));
          u.hash = '#' + parts.join('&');
        }
        return u.toString();
      }
 
      // 2. 外部ドメイン (スイッチによる切り替え)
      var mode = getExtMode();
      if (mode === 'raw') {
        return url;
      } else {
        return 'https://translate.google.com/translate?sl=ja&tl=' + encodeURIComponent(targetLang) + '&u=' + encodeURIComponent(u.toString());
      }
    }catch(e){ return url; }
  }
 
  var isSelfMutating = false;
  function setHrefSafe(a, newVal) {
    if (a.getAttribute('href') === newVal) return false;
    isSelfMutating = true;
    try { a.setAttribute('href', newVal); }
    finally { Promise.resolve().then(function(){ isSelfMutating = false; }); }
    return true;
  }
 
  function processLinks(){
    var lang = currentLang();
    var extMode = getExtMode();
    var links = document.getElementsByTagName('a');
    for(var i=0; i<links.length; i++){
      var a = links[i];
      var href = a.getAttribute('href') || '';
      if(!href || href.charAt(0) === '#' || href.indexOf('javascript:') === 0 || href.indexOf('mailto:') === 0 || href.indexOf('tel:') === 0) continue;
 
      if(a.hasAttribute('data-safe-href')){
        if(href !== 'javascript:void(0)') setHrefSafe(a, 'javascript:void(0)');
        continue;
      }
      if(a.getAttribute('data-no-translate') === '1') continue;
 
      var origHrefAttr = a.getAttribute('data-orig-href');
      if(href.indexOf('translate.goog') !== -1 || href.indexOf('translate.google.com/translate') !== -1){
        if(origHrefAttr){
          if(lang && lang !== 'ja'){
            var newProxy = toTranslateProxy(origHrefAttr, lang);
            if(newProxy && href !== newProxy) setHrefSafe(a, newProxy);
            continue;
          } else {
            setHrefSafe(a, origHrefAttr);
            a.removeAttribute('data-orig-href');
            continue;
          }
        }
        var restored = restoreFromProxy(href);
        if(restored){
          href = restored;
          setHrefSafe(a, restored);
        }
      }
 
      var absUrl;
      try{ absUrl = new URL(href, location.href).toString(); }catch(e){ continue; }
      var hostLower = '';
      try{ hostLower = new URL(absUrl).hostname.toLowerCase(); }catch(e){}
 
      if(VIDEO_SNS_HOSTS.test(hostLower)){
        if(!a.getAttribute('data-safe-href')){
          a.setAttribute('data-safe-href', absUrl);
          a.style.cursor = 'pointer';
        }
        if(href !== 'javascript:void(0)') setHrefSafe(a, 'javascript:void(0)');
        continue;
      }
 
      // 外部サイトモードが raw の場合は外部リンクはそのまま
      if (extMode === 'raw' && !SELF_DOMAIN.test(hostLower) && !GOOGLE_HOSTS.test(hostLower)) {
         if(origHrefAttr && origHrefAttr !== href){
           setHrefSafe(a, origHrefAttr);
           a.removeAttribute('data-orig-href');
         }
         continue;
      }
 
      if(lang && lang !== 'ja'){
        var proxied = toTranslateProxy(absUrl, lang);
        if(proxied && proxied !== absUrl && proxied !== href){
          a.setAttribute('data-orig-href', absUrl);
          setHrefSafe(a, proxied);
        }
      } else {
        if(origHrefAttr && origHrefAttr !== href){
          setHrefSafe(a, origHrefAttr);
          a.removeAttribute('data-orig-href');
        }
      }
    }
  }
 
  function handleSafeLinkClick(ev){
    var t = ev.target;
    if(!t) return;
    var a = t.closest ? t.closest('a[data-safe-href]') : null;
    if(!a) return;
    ev.preventDefault();
    ev.stopImmediatePropagation();
    ev.stopPropagation();
    var url = a.getAttribute('data-safe-href');
    if(!url) return;
    window.open(url, '_blank', 'noopener,noreferrer');
  }
 
  document.addEventListener('click', handleSafeLinkClick, true);
  document.addEventListener('auxclick', handleSafeLinkClick, true);
 
  window.addEventListener('DOMContentLoaded', function(){
    processLinks();
    var observer = new MutationObserver(function(){ if (!isSelfMutating) processLinks(); });
    observer.observe(document.body, {childList: true, subtree: true, attributes: true, attributeFilter: ['href']});
    var radios = document.querySelectorAll('input[name="ext_mode"]');
    radios.forEach(function(r){ r.addEventListener('change', processLinks); });
  });
  window.addEventListener('hashchange', processLinks);
 
  // 画像の src 破壊を防ぐための restoreUrl
  function restoreImageUrl(url){
    if(!url||typeof url!=="string") return url;
    try{
      var u=new URL(url, window.location.href);
      if(u.hostname.indexOf(".translate.goog")!==-1){
        var orig=u.hostname.replace(".translate.goog","").replace(/-/g,".");
        u.searchParams.delete("_x_tr_sl"); u.searchParams.delete("_x_tr_tl");
        u.searchParams.delete("_x_tr_hl"); u.searchParams.delete("_x_tr_pto");
        u.searchParams.delete("_x_tr_hist");
        return "https://"+orig+u.pathname+u.search+u.hash;
      }
      return url;
    }catch(e){ return url; }
  }
  function fixAllImages(){
    var imgs=document.querySelectorAll("img[src]");
    for(var i=0;i<imgs.length;i++){
      var src=imgs[i].getAttribute("src");
      if(!src) continue;
      if(src.indexOf("flagcdn.com")===-1 && !SELF_DOMAIN.test(new URL(src,location.href).hostname)) continue;
      var fixed=restoreImageUrl(src);
      if(fixed!==src) imgs[i].setAttribute("src",fixed);
    }
  }
  setInterval(fixAllImages, 3000);
})();
</script>


<?php
/* =========================================================
   AI TECH ANALYZER BLOCK for aruaru
   2026/05/15 Edition
   自動分析 / 自動ランキング / 自動保存
========================================================= */

if (!defined('AI_TECH_CACHE')) { define('AI_TECH_CACHE', __DIR__ . '/ai-tech-ranking-cache.json'); }

function aruaru_learn_table_rows(array $rows): void
{
    foreach ($rows as $row) {
        echo '<tr style="border-bottom:1px solid #1e3555;">';
        echo '<td style="padding:6px 8px;text-align:center;font-weight:bold;color:#7dd3fc;">' . htmlspecialchars((string)$row['rank']) . '</td>';
        echo '<td style="padding:6px 8px;font-weight:bold;"><a href="' . htmlspecialchars($row['url']) . '" target="_blank" rel="noopener noreferrer" style="color:#7dd3fc;text-decoration:underline;">' . htmlspecialchars($row['name']) . '</a></td>';
        echo '<td style="padding:6px 8px;">' . htmlspecialchars($row['features']) . '</td>';
        echo '<td style="padding:6px 8px;">' . htmlspecialchars($row['price']) . '</td>';
        echo '</tr>';
    }
}

function aruaru_tech_link_button(string $name, string $panelKey, string $color = '#7dd3fc'): void
{
    $n = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $pk = htmlspecialchars($panelKey, ENT_QUOTES, 'UTF-8');
    echo '<button type="button" class="aruaru-tech-link" data-panel-key="' . $pk . '" title="おすすめ学習リンク一覧" style="background:none;border:none;padding:0;font-weight:bold;color:' . $color . ';text-decoration:underline;cursor:pointer;font-size:inherit;">' . $n . '</button>';
}


/* =========================================================
   ★ Cron 統合モード（毎日朝5時・1コマンドのみ設定）
   LOLIPOP「PHPファイル直接指定」型cron対応:
     当ファイルを引数なしで直接実行しても、HTTP経由でなければ
     aruaru_is_cron_request() が true を返し全更新が走ります。
   絶対パス指定例（管理画面のcron欄に登録）:
     /home/users/2/hiho.jp-aon/web/audiocafe.tokyo/aruaru/index.php
   ブラウザ/外部cron(任意・キー必須):
     https://audiocafe.tokyo/aruaru/?cron=all&key=（ARUARU_CRON_KEY）
========================================================= */

/* ---- 楽天モバイル 国際通話 毎日クロール ---- */
if (aruaru_is_cron_request('--cron-intl')) {
    @unlink(RAKUTEN_INTL_CACHE_FILE);
    $d = rakuten_intl_crawl();
    @file_put_contents(RAKUTEN_INTL_CACHE_FILE, json_encode($d, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
    echo "[" . date("Y-m-d H:i:s") . "] intl完了\n"; exit(0);
}
/* ---- 楽天モバイル プラチナバンド・衛星 毎日クロール ---- */
if (aruaru_is_cron_request('--cron-platinum')) {
    @unlink(RAKUTEN_PLATINUM_CACHE_FILE);
    $d = rakuten_platinum_crawl();
    @file_put_contents(RAKUTEN_PLATINUM_CACHE_FILE, json_encode($d, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
    echo "[" . date("Y-m-d H:i:s") . "] platinum完了\n"; exit(0);
}

/* ---- doda 求人 毎日クロール ---- */
if (aruaru_is_cron_request('--cron-doda')) {
    $d = doda_run_crawl();
    echo "[" . date("Y-m-d H:i:s") . "] doda完了 — IT=" . ($d['categories']['it']['count'] ?? 0) . " AD=" . ($d['categories']['ad']['count'] ?? 0) . "\n"; exit(0);
}

$is_cron_mode = aruaru_is_cron_request();

$AI_TECH_DATA = null;
if (file_exists(AI_TECH_CACHE)) {
    $AI_TECH_DATA = json_decode(file_get_contents(AI_TECH_CACHE), true);
}
$lang0 = is_array($AI_TECH_DATA) ? ($AI_TECH_DATA['languages'][0] ?? null) : null;
$fw0  = is_array($AI_TECH_DATA) ? ($AI_TECH_DATA['frameworks'][0] ?? null) : null;
$db0  = is_array($AI_TECH_DATA) ? ($AI_TECH_DATA['databases'][0] ?? null) : null;
$ai_tech_cache_incomplete = !is_array($AI_TECH_DATA)
    || count($AI_TECH_DATA['languages'] ?? []) < 100
    || count($AI_TECH_DATA['frameworks'] ?? []) < 80
    || count($AI_TECH_DATA['databases'] ?? []) < 80
    || !is_array($lang0) || !isset($lang0['dev_scale']) || !isset($lang0['memory'])
    || !is_array($lang0) || !function_exists('aruaru_tech_language_extended_complete') || !aruaru_tech_language_extended_complete($lang0)
    || !is_array($fw0) || !isset($fw0['memory'])
    || !is_array($db0) || !isset($db0['memory']);
$ai_tech_cache_old = file_exists(AI_TECH_CACHE) && (time() - filemtime(AI_TECH_CACHE) > 86400);

/* ★ --cron-all: 全キャッシュを毎日まとめて更新する統合Cron */
if (aruaru_is_cron_request()) {
    // クエリ経由(HTTP)起動時はテキスト出力にする
    if (!headers_sent() && !empty($_SERVER['HTTP_HOST'])) {
        header('Content-Type: text/plain; charset=UTF-8');
        header('Cache-Control: no-store');
    }
    $t0 = microtime(true);
    echo "\n[" . date("Y-m-d H:i:s") . "] ========== aruaru cron 開始 (SAPI=" . PHP_SAPI . ") ==========\n";
    echo "[" . date("Y-m-d H:i:s") . "] 書込先ディレクトリ: " . __DIR__ . "\n";
    echo "[" . date("Y-m-d H:i:s") . "] 書込権限: " . (is_writable(__DIR__) ? 'OK(書込可)' : 'NG(書込不可！ここが原因の可能性)') . "\n";

    // ① AIテクノロジーランキング（毎日）
    echo "[" . date("Y-m-d H:i:s") . "] [1/7] AIテクノロジーランキング 更新...\n";
    $data = aruaru_tech_refresh_rankings(true);
    file_put_contents(AI_TECH_CACHE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    $src = implode(', ', $data['ranking_meta']['sources'] ?? ['baseline']);
    echo "[" . date("Y-m-d H:i:s") . "] [1/7] 完了（外部: {$src}）\n";

    // ② 学習価格情報（毎日）
    echo "[" . date("Y-m-d H:i:s") . "] [2/7] 学習価格情報 更新...\n";
    aruaru_learning_prices_refresh(true);
    echo "[" . date("Y-m-d H:i:s") . "] [2/7] 完了\n";

    // ③ AI学習コメント（毎日）
    echo "[" . date("Y-m-d H:i:s") . "] [3/7] AI学習コメント 更新...\n";
    aruaru_learning_ai_cron_refresh($data, 12, true);
    echo "[" . date("Y-m-d H:i:s") . "] [3/7] 完了\n";

    // ④ 英会話ランキング（毎日）
    echo "[" . date("Y-m-d H:i:s") . "] [4/7] 英会話ランキング 更新...\n";
    aruaru_eikaiwa_ranking_refresh(true);
    echo "[" . date("Y-m-d H:i:s") . "] [4/7] 完了\n";

    // ⑤ 楽天モバイル 基本料金（毎日）
    echo "[" . date("Y-m-d H:i:s") . "] [5/7] 楽天モバイル 基本料金 クロール...\n";
    @unlink(RAKUTEN_CACHE_FILE);
    $rk = rakuten_fetch_price();
    @file_put_contents(RAKUTEN_CACHE_FILE, json_encode($rk, JSON_UNESCAPED_UNICODE), LOCK_EX);
    echo "[" . date("Y-m-d H:i:s") . "] [5/7] 完了 — 料金: {$rk['price']}\n";

    // ⑥ 楽天モバイル 国際通話（毎日）
    echo "[" . date("Y-m-d H:i:s") . "] [6/7] 楽天モバイル 国際通話 クロール...\n";
    @unlink(RAKUTEN_INTL_CACHE_FILE);
    $intl = rakuten_intl_crawl();
    @file_put_contents(RAKUTEN_INTL_CACHE_FILE, json_encode($intl, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
    echo "[" . date("Y-m-d H:i:s") . "] [6/7] 完了 — 国数: {$intl['intl_countries_count']}  成功: " . ($intl['crawl_success'] ? 'YES' : 'NO') . "\n";

    // ⑦ 楽天モバイル プラチナバンド・衛星（毎日）
    echo "[" . date("Y-m-d H:i:s") . "] [7/7] 楽天モバイル プラチナバンド・衛星 クロール...\n";
    @unlink(RAKUTEN_PLATINUM_CACHE_FILE);
    $plat = rakuten_platinum_crawl();
    @file_put_contents(RAKUTEN_PLATINUM_CACHE_FILE, json_encode($plat, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
    echo "[" . date("Y-m-d H:i:s") . "] [7/7] 完了 — カバレッジ: {$plat['platinum_coverage_ja']}  成功: " . ($plat['crawl_success'] ? 'YES' : 'NO') . "\n";
    echo "[" . date("Y-m-d H:i:s") . "] ※ スマホキャンペーン表示は index.php の RAKUTEN_1YEN_CAMPAIGN 定数で手動管理\n";

    // ⑧ doda 求人（IT・通信／総合広告代理店・Webマーケ）（毎日）
    echo "[" . date("Y-m-d H:i:s") . "] [8/8] doda 求人 クロール...\n";
    $doda_c = doda_run_crawl();
    echo "[" . date("Y-m-d H:i:s") . "] [8/8] 完了 — IT=" . ($doda_c['categories']['it']['count'] ?? 0) . " AD=" . ($doda_c['categories']['ad']['count'] ?? 0) . "\n";

    /* バッジ時刻は max(各キャッシュの filemtime) を表示するため、
       内容が同一でも mtime を必ず「今」へ進める（touch）。
       これにより毎朝5時の実行が確実にバッジへ反映される。 */
    $now = time();
    @touch(AI_TECH_CACHE, $now);
    @touch(RAKUTEN_INTL_CACHE_FILE, $now);
    @touch(RAKUTEN_PLATINUM_CACHE_FILE, $now);
    @touch(DODA_CACHE_FILE, $now);
    echo "[" . date("Y-m-d H:i:s") . "] --- 更新後タイムスタンプ確認 ---\n";
    foreach ([
        'AI_TECH'  => AI_TECH_CACHE,
        'RAKUTEN'  => RAKUTEN_CACHE_FILE,
        'INTL'     => RAKUTEN_INTL_CACHE_FILE,
        'PLATINUM' => RAKUTEN_PLATINUM_CACHE_FILE,
        'DODA'     => DODA_CACHE_FILE,
    ] as $label => $path) {
        $mt = @filemtime($path);
        echo "    {$label}: " . ($mt ? date('Y-m-d H:i:s', $mt) : '（ファイル無し/書込失敗）')
           . "  " . $path . "\n";
    }

    $elapsed = round(microtime(true) - $t0, 2);
    echo "[" . date("Y-m-d H:i:s") . "] ========== aruaru cron 完了（{$elapsed}秒）==========\n\n";
    exit(0);
}

if ($is_cron_mode || !file_exists(AI_TECH_CACHE) || $ai_tech_cache_incomplete || $ai_tech_cache_old) {
    $data = aruaru_tech_refresh_rankings($is_cron_mode && php_sapi_name() === 'cli');
    file_put_contents(AI_TECH_CACHE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    if ($is_cron_mode && php_sapi_name() === 'cli') {
        $src = implode(', ', $data['ranking_meta']['sources'] ?? ['baseline']);
        echo "[" . date("Y-m-d H:i:s") . "] ai-tech-ranking-cache.json 更新（外部: {$src}）\n";
        aruaru_learning_prices_refresh(true);
        aruaru_learning_ai_cron_refresh($data, 12, true);
        if (!is_readable(ARUARU_EIKAIWA_CACHE) || (time() - (int)@filemtime(ARUARU_EIKAIWA_CACHE) >= ARUARU_EIKAIWA_TTL)) {
            aruaru_eikaiwa_ranking_refresh(true);
        }
        exit(0);
    }
    $AI_TECH_DATA = $data;
} elseif (!is_array($AI_TECH_DATA)) {
    $AI_TECH_DATA = aruaru_tech_baseline_data();
}

$updated_at = $AI_TECH_DATA['updated_at'] ?? date('Y-m-d H:i:s');
$aruaru_ranking_meta = is_array($AI_TECH_DATA['ranking_meta'] ?? null) ? $AI_TECH_DATA['ranking_meta'] : [];
$aruaru_eikaiwa = aruaru_eikaiwa_ranking_get();
$aruaru_link_rakuten_link_android = 'https://www.google.com/search?q=' . rawurlencode('楽天モバイル スマホアプリの名前は 楽天リンク Android版');
$aruaru_link_rakuten_link_iphone = 'https://www.google.com/search?q=' . rawurlencode('楽天モバイル スマホアプリの名前は 楽天リンク iPhone版');
$aruaru_link_rakuten_we2plus = 'https://www.google.com/search?q=' . rawurlencode('楽天モバイル お勧め 1円 高性能CPU 富士通製 スマホ we2 plus');
// 大工・建築・施設管理・IT研修 Google検索リンク
$aruaru_link_daiku           = 'https://www.google.com/search?q=' . rawurlencode('未経験から大工 造作大工 型枠大工 木造建築大工 求人');
$aruaru_link_kenchiku_kanri  = 'https://www.google.com/search?q=' . rawurlencode('未経験 無資格 大工 CAD 施設管理 建築現場管理 から 一級建築士 木造建築士 管理建築士 1級建築施工管理技士 を目指せる求人');
$aruaru_link_it_kenshu       = 'https://www.google.com/search?q=' . rawurlencode('未経験 無料IT研修 無料 転職エージェント サービス');
$aruaru_dl_cursor_day_jst = (new DateTimeImmutable('now', new DateTimeZone('Asia/Tokyo')))->format('Y-m-d');

$aruaru_policy = aruaru_policy_messages();
$aruaru_learn_cats = aruaru_learning_categories();
$aruaru_learn_panel = [];
foreach ($AI_TECH_DATA['languages'] as $row) {
    $aruaru_learn_panel['lang:' . $row['name']] = aruaru_resolve_tech_links((string)$row['name'], 'language');
}
$aruaru_learn_panel['lang:Mojo'] = aruaru_resolve_tech_links('Mojo', 'language');
foreach ($AI_TECH_DATA['frameworks'] as $row) {
    $aruaru_learn_panel['fw:' . $row['name']] = aruaru_resolve_tech_links((string)$row['name'], 'framework');
}
$aruaru_learn_panel['fw:WunderGraph'] = aruaru_resolve_tech_links('WunderGraph', 'framework');
$aruaru_learn_panel['fw:VersionlessAPI'] = aruaru_resolve_tech_links('VersionlessAPI', 'framework');
foreach ($AI_TECH_DATA['databases'] as $row) {
    $aruaru_learn_panel['db:' . $row['name']] = aruaru_resolve_tech_links((string)$row['name'], 'database');
}
$aruaru_learn_panel['cloud:AWS'] = aruaru_resolve_tech_links('AWS', 'cloud');

// AI選定 注目チップ（$ai_trends）も学習リンクパネルに登録（PANELに無い技術名を補完）
foreach (array_slice($ai_trends['langs'] ?? [], 0, 80) as $_t) {
    $_k = 'lang:' . $_t;
    if (!isset($aruaru_learn_panel[$_k])) {
        $aruaru_learn_panel[$_k] = aruaru_resolve_tech_links((string)$_t, 'language');
    }
}
foreach (($ai_trends['fws_top'] ?? []) as $_t) {
    $_k = 'fw:' . $_t;
    if (!isset($aruaru_learn_panel[$_k])) {
        $aruaru_learn_panel[$_k] = aruaru_resolve_tech_links((string)$_t, 'framework');
    }
}
foreach (['PostgreSQL','MySQL','Redis','MongoDB','SQLite','Elasticsearch','Cassandra','DynamoDB','CockroachDB','Firestore','Supabase','PlanetScale','Neon','TiDB','ClickHouse','Neo4j','InfluxDB','Pinecone','Weaviate','MariaDB','Oracle DB','SQL Server','BigQuery','Snowflake','Redshift','RethinkDB','Couchbase','FaunaDB','EdgeDB','SingleStore','DuckDB','QuestDB','TimescaleDB','Apache Druid','Apache HBase','ScyllaDB','ArangoDB','YugabyteDB','VoltDB','Apache Doris','StarRocks','Greenplum','Vertica','IBM Db2','Firebird','Informix','InterSystems IRIS','AlloyDB','Amazon DocumentDB','Azure Cosmos DB','Dgraph','Fauna','SurrealDB','PouchDB','CouchDB','RavenDB','DocumentDB（AWS）','Realm','Datomic','Memcached','Hazelcast','Apache Ignite','GridGain','Tarantool','KeyDB','Dragonfly','Valkey','Typesense','Meilisearch','OpenSearch','Chroma','Qdrant','Milvus','pgvector','Vespa','Zilliz','LanceDB','Xata','Turso','D1（Cloudflare）'] as $_t) {
    $_k = 'db:' . $_t;
    if (!isset($aruaru_learn_panel[$_k])) {
        $aruaru_learn_panel[$_k] = aruaru_resolve_tech_links((string)$_t, 'database');
    }
}

$aruaru_learn_panel = aruaru_learning_ai_merge_panel($aruaru_learn_panel);

/* =========================================================
   HTML表示
========================================================= */
?>

<section id="ai-tech-analyzer" style="margin:40px auto;padding:20px;max-width:1400px;background:#07111f;border-radius:20px;color:white;box-shadow:0 0 20px rgba(0,255,255,.2);">

<div class="aruaru-policy-note" style="margin-bottom:24px;padding:16px 18px;border-radius:14px;background:linear-gradient(135deg,rgba(6,182,212,.12),rgba(167,139,250,.1));border:1px solid rgba(148,163,184,.25);font-size:15px;line-height:1.75;color:#e8eef8;">
  <div lang="ja" style="margin-bottom:12px;color:#fff;"><?= htmlspecialchars($aruaru_policy['ja']) ?></div>
  <div lang="en" style="opacity:.92;"><?= htmlspecialchars($aruaru_policy['en']) ?></div>
</div>

    <!-- ══ AI TREND REPORT ══════════════════════════════════════ -->
    <div class="mt-3 mb-4" id="ai-trend">
      <div class="ext-section" style="border-color:rgba(167,139,250,.35)">
        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
          <div class="ext-title">
            🤖 AIトレンド分析
          </div>
          <?php if (!empty($ai_trends['updated_at'])): ?>
            <span style="font-size:.84rem;color:var(--primary-lt);font-weight:700;padding:.12rem .45rem;border-radius:5px;background:var(--primary-glow)">
              <?= h($ai_trends['model']) ?> · <?= h($ai_trends['updated_at']) ?> 更新
            </span>
          <?php elseif (OPENAI_API_KEY === ''): ?>
            <span style="font-size:.84rem;color:#fbbf24;font-weight:700;padding:.12rem .45rem;border-radius:5px;background:rgba(245,158,11,.12)">
              OpenAI APIキー未設定（デフォルト表示）
            </span>
          <?php endif; ?>
        </div>

        <?php if (!empty($ai_trends['summary'])): ?>
        <p style="font-size:.88rem;color:#fff;line-height:1.7;margin-bottom:1rem;padding:.65rem .9rem;background:rgba(167,139,250,.1);border-radius:9px;border-left:3px solid var(--accent)">
          <?= h($ai_trends['summary']) ?>
        </p>
        <?php endif; ?>

        <div class="row g-3">
          <!-- 急上昇ランキング -->
          <div class="col-12 col-md-6">
            <div style="font-size:.84rem;font-weight:700;color:#fff;letter-spacing:.06em;text-transform:uppercase;margin-bottom:.5rem">🔥 急上昇中の言語</div>
            <div class="d-flex flex-wrap gap-1 ai-trend-chips">
              <?php foreach ($ai_trends['hot_langs'] ?? [] as $i => $lang): ?>
                <span class="chip on" style="border-color:rgba(249,115,22,.7);color:#fed7aa;background:rgba(249,115,22,.12)">
                  <?= $i+1 ?>. <?= h($lang) ?>
                </span>
              <?php endforeach; ?>
              <?php if (empty($ai_trends['hot_langs'])): ?>
                <span style="font-size:.88rem;color:#dde6f5">APIキーを設定するとAIが分析します</span>
              <?php endif; ?>
            </div>
          </div>
          <!-- 急上昇FW -->
          <div class="col-12 col-md-6">
            <div style="font-size:.84rem;font-weight:700;color:#fff;letter-spacing:.06em;text-transform:uppercase;margin-bottom:.5rem">🔥 急上昇中のFW・ツール</div>
            <div class="d-flex flex-wrap gap-1 ai-trend-chips">
              <?php foreach ($ai_trends['hot_fws'] ?? [] as $i => $fw): ?>
                <span class="chip on" style="border-color:rgba(249,115,22,.7);color:#fed7aa;background:rgba(249,115,22,.12)">
                  <?= $i+1 ?>. <?= h($fw) ?>
                </span>
              <?php endforeach; ?>
              <?php if (empty($ai_trends['hot_fws'])): ?>
                <span style="font-size:.88rem;color:#dde6f5">APIキーを設定するとAIが分析します</span>
              <?php endif; ?>
            </div>
          </div>
          <!-- 注目プログラミング言語 TOP80 -->
          <div class="col-12">
            <div style="font-size:.84rem;font-weight:700;color:#fff;letter-spacing:.06em;text-transform:uppercase;margin-bottom:.5rem">⭐ AI選定 注目プログラミング言語 TOP80<span style="font-weight:400;text-transform:none;opacity:.7;margin-left:.5rem;font-size:.84rem">（クリックでおすすめ学習リンク）</span></div>
            <div class="d-flex flex-wrap gap-1 ai-trend-chips">
              <?php foreach (array_slice($ai_trends['langs'] ?? [], 0, 80) as $i => $lang):
                $medal = $i < 3 ? ['🥇','🥈','🥉'][$i] . ' ' : ($i+1).'. ';
                $pkey  = 'lang:' . $lang;
                $clickable = isset($aruaru_learn_panel[$pkey]);
              ?>
                <?php if ($clickable): ?>
                <button type="button" class="chip on aruaru-tech-link" data-panel-key="<?= h($pkey) ?>" title="おすすめ学習リンク一覧" style="cursor:pointer;<?= $i < 3 ? 'border-color:rgba(167,139,250,.7);' : '' ?>"><?= $medal ?><?= h($lang) ?></button>
                <?php else: ?>
                <span class="chip on" style="<?= $i < 3 ? 'border-color:rgba(167,139,250,.7)' : '' ?>"><?= $medal ?><?= h($lang) ?></span>
                <?php endif; ?>
              <?php endforeach; ?>
            </div>
          </div>
          <!-- 注目FW・ツール TOP80 -->
          <div class="col-12">
            <div style="font-size:.84rem;font-weight:700;color:#fff;letter-spacing:.06em;text-transform:uppercase;margin-bottom:.5rem">⭐ AI選定 注目FW・ツール Top<?= count($ai_trends['fws_top'] ?? []) ?><span style="font-weight:400;text-transform:none;opacity:.7;margin-left:.5rem;font-size:.84rem">（クリックでおすすめ学習リンク）</span></div>
            <div class="d-flex flex-wrap gap-1 ai-trend-chips">
              <?php foreach (($ai_trends['fws_top'] ?? []) as $i => $fw):
                $medal = $i < 3 ? ['🥇','🥈','🥉'][$i] . ' ' : ($i+1).'. ';
                $pkey  = 'fw:' . $fw;
                $clickable = isset($aruaru_learn_panel[$pkey]);
              ?>
                <?php if ($clickable): ?>
                <button type="button" class="chip on aruaru-tech-link" data-panel-key="<?= h($pkey) ?>" title="おすすめ学習リンク一覧" style="cursor:pointer;<?= $i < 3 ? 'border-color:rgba(167,139,250,.7);' : '' ?>"><?= $medal ?><?= h($fw) ?></button>
                <?php else: ?>
                <span class="chip on" style="<?= $i < 3 ? 'border-color:rgba(167,139,250,.7)' : '' ?>"><?= $medal ?><?= h($fw) ?></span>
                <?php endif; ?>
              <?php endforeach; ?>
            </div>
          </div>
          <!-- 注目DATABASE TOP80 -->
          <div class="col-12">
            <div style="font-size:.84rem;font-weight:700;color:#fff;letter-spacing:.06em;text-transform:uppercase;margin-bottom:.5rem">🗄 AI選定 注目DATABASE TOP80<span style="font-weight:400;text-transform:none;opacity:.7;margin-left:.5rem;font-size:.84rem">（クリックでおすすめ学習リンク）</span></div>
            <div class="d-flex flex-wrap gap-1 ai-trend-chips">
              <?php
              $db_top80 = ['PostgreSQL','MySQL','Redis','MongoDB','SQLite','Elasticsearch','Cassandra','DynamoDB','CockroachDB','Firestore','Supabase','PlanetScale','Neon','TiDB','ClickHouse','Neo4j','InfluxDB','Pinecone','Weaviate','MariaDB','Oracle DB','SQL Server','BigQuery','Snowflake','Redshift','RethinkDB','Couchbase','FaunaDB','EdgeDB','SingleStore','DuckDB','QuestDB','TimescaleDB','Apache Druid','Apache HBase','ScyllaDB','ArangoDB','YugabyteDB','VoltDB','Apache Doris','StarRocks','Greenplum','Vertica','IBM Db2','Firebird','Informix','InterSystems IRIS','AlloyDB','Amazon DocumentDB','Azure Cosmos DB','Dgraph','Fauna','SurrealDB','PouchDB','CouchDB','RavenDB','DocumentDB（AWS）','Realm','Datomic','Memcached','Hazelcast','Apache Ignite','GridGain','Tarantool','KeyDB','Dragonfly','Valkey','Typesense','Meilisearch','OpenSearch','Chroma','Qdrant','Milvus','pgvector','Vespa','Zilliz','LanceDB','Xata','Turso','D1（Cloudflare）'];
              foreach ($db_top80 as $i => $db):
                $medal = $i < 3 ? ['🥇','🥈','🥉'][$i] . ' ' : ($i+1).'. ';
                $pkey  = 'db:' . $db;
                $clickable = isset($aruaru_learn_panel[$pkey]);
              ?>
                <?php if ($clickable): ?>
                <button type="button" class="chip on aruaru-tech-link" data-panel-key="<?= h($pkey) ?>" title="おすすめ学習リンク一覧" style="cursor:pointer;<?= $i < 3 ? 'border-color:rgba(255,102,204,.7);' : '' ?>"><?= $medal ?><?= h($db) ?></button>
                <?php else: ?>
                <span class="chip on" style="<?= $i < 3 ? 'border-color:rgba(255,102,204,.7)' : '' ?>"><?= $medal ?><?= h($db) ?></span>
                <?php endif; ?>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <?php if (OPENAI_API_KEY === ''): ?>
        <div style="margin-top:.8rem;font-size:.84rem;color:#dde6f5;padding:.55rem .8rem;background:rgba(167,139,250,.08);border-radius:8px">
          💡 <strong style="color:#fff">AIトレンド分析を有効にするには</strong>：
          index.php の先頭にある <code style="color:var(--primary-lt)">OPENAI_API_KEY</code> を設定してください。
          蓄積された検索ワードをAIが自動分析し、言語チップの順番を需要順に並べ替えます（3日ごとに更新）。
        </div>
        <?php endif; ?>
      </div>
    </div>

<h2 style="font-size:28px;color:#00ffff;" id="aruaru-top80-tech">
🚀 人気TOP100 技術ランキング（AI自動分析・拡張評価軸つき）
</h2>
<p style="opacity:.7;font-size:15px;">
  🕐 最終更新: <?= h(aru_update_label($updated_at)) ?>
  <?php if (!empty($aruaru_ranking_meta['sources'])): ?>
    <br>📡 順位根拠: <?= htmlspecialchars(implode(' + ', $aruaru_ranking_meta['sources'])) ?>
    <?php if (!empty($aruaru_ranking_meta['openai_used'])): ?> · AI紹介文生成ON<?php elseif (OPENAI_API_KEY === ''): ?> · <span style="color:#fbbf24">OPENAI_API_KEY未設定（新規項目は簡易表示）</span><?php endif; ?>
  <?php endif; ?>
  <br>ℹ️ このセクションの実装詳細・データの正直な限界は
  <a href="https://github.com/aon-co-jp/audiocafe-tokyo" target="_blank" rel="noopener" style="color:#67e8f9;">GitHubリポジトリ(README/CLAUDE.md)</a>
  を参照してください。
</p>

<?php if (!empty($aruaru_ranking_meta['extended_axes_note'])): ?>
<div style="margin:12px 0 18px;padding:12px 16px;border-radius:10px;background:rgba(251,191,36,.08);border:1px solid rgba(251,191,36,.35);font-size:13.5px;line-height:1.7;color:#fde68a;">
  ⚠️ <strong>拡張評価軸（非同期・ハイスピード・セキュリティ・AI/その他ライブラリ・仕様変更耐性・並列分散性・各種類似性コメント）について:</strong>
  <?= htmlspecialchars($aruaru_ranking_meta['extended_axes_note']) ?>
</div>
<?php endif; ?>

<h3 style="color:#00ffaa;margin-top:30px;" id="aruaru-top80-lang">💻 人気プログラミング言語 TOP100（拡張評価軸つき）</h3>
<div style="overflow:auto;">
<table style="width:100%;border-collapse:collapse;font-size:15px;">
<tr style="background:#10233f;text-align:left;">
<th style="padding:8px;">順位</th><th style="padding:8px;">言語</th><th style="padding:8px;">チーム開発</th><th style="padding:8px;">保守性</th><th style="padding:8px;">初心者向け</th><th style="padding:8px;">速度</th><th style="padding:8px;">必要メモリ容量</th><th style="padding:8px;max-width:7rem;">開発規模</th><th style="padding:8px;max-width:12rem;">特徴</th><th style="padding:8px;max-width:12rem;">オープンソース</th><th style="padding:8px;max-width:10rem;">非同期対応</th><th style="padding:8px;max-width:10rem;">一秒間に10万件<br>処理対応</th><th style="padding:8px;min-width:10rem;">AI分析コメント</th>
<th style="padding:8px;">非同期<br>score</th><th style="padding:8px;">ハイスピード<br>score</th><th style="padding:8px;">セキュリティ<br>score</th><th style="padding:8px;">AIライブラリ<br>score</th><th style="padding:8px;">その他ライブラリ<br>score</th><th style="padding:8px;">仕様変更耐性<br>score</th><th style="padding:8px;">並列/分散<br>score</th><th style="padding:8px;color:#fbbf24;">合計score</th>
<th style="padding:8px;max-width:12rem;">VersionlessAPI/バージョン管理</th><th style="padding:8px;max-width:10rem;">フレームワーク</th><th style="padding:8px;max-width:12rem;">データベース相性</th><th style="padding:8px;max-width:10rem;">CockroachDB類似性</th><th style="padding:8px;max-width:10rem;">Snowflake類似性</th><th style="padding:8px;max-width:10rem;">aruaru-db類似性</th>
</tr>
<?php foreach($AI_TECH_DATA['languages'] as $item): ?>
<?php $aruaru_row_id = aruaru_anchor_id_for_tech('lang', (string)$item['name']); ?>
<tr id="<?= htmlspecialchars($aruaru_row_id, ENT_QUOTES, 'UTF-8') ?>" style="border-bottom:1px solid #1e3555;">
<td style="padding:6px 8px;text-align:center;font-weight:bold;color:#00ffff;"><?= htmlspecialchars((string)$item['rank']) ?></td>
<td style="padding:6px 8px;"><?php aruaru_tech_link_button((string)$item['name'], 'lang:' . $item['name'], '#00ffff'); ?></td>
<td style="padding:6px 8px;"><?= htmlspecialchars($item['team_dev']) ?></td>
<td style="padding:6px 8px;"><?= htmlspecialchars($item['maintenance']) ?></td>
<td style="padding:6px 8px;"><?= htmlspecialchars($item['beginner']) ?></td>
<td style="padding:6px 8px;"><?= htmlspecialchars($item['speed']) ?></td>
<td style="padding:6px 8px;"><?= htmlspecialchars($item['memory'] ?? '—') ?></td>
<td style="padding:6px 8px;"><?= htmlspecialchars($item['dev_scale'] ?? '—') ?></td>
<td style="padding:6px 8px;"><?= htmlspecialchars($item['traits'] ?? '—') ?></td>
<td style="padding:6px 8px;"><?= htmlspecialchars($item['oss_note'] ?? '—') ?></td>
<td style="padding:6px 8px;"><?= htmlspecialchars($item['async_support'] ?? '—') ?></td>
<td style="padding:6px 8px;"><?= htmlspecialchars($item['throughput_100k'] ?? '—') ?></td>
<td style="padding:6px 8px;opacity:.85;"><?= htmlspecialchars($item['ai_comment']) ?></td>
<td style="padding:6px 8px;text-align:center;"><?= htmlspecialchars((string)($item['score_async'] ?? '—')) ?></td>
<td style="padding:6px 8px;text-align:center;"><?= htmlspecialchars((string)($item['score_speed'] ?? '—')) ?></td>
<td style="padding:6px 8px;text-align:center;"><?= htmlspecialchars((string)($item['score_security'] ?? '—')) ?></td>
<td style="padding:6px 8px;text-align:center;"><?= htmlspecialchars((string)($item['score_ai_library'] ?? '—')) ?></td>
<td style="padding:6px 8px;text-align:center;"><?= htmlspecialchars((string)($item['score_other_library'] ?? '—')) ?></td>
<td style="padding:6px 8px;text-align:center;"><?= htmlspecialchars((string)($item['score_spec_change_resilience'] ?? '—')) ?></td>
<td style="padding:6px 8px;text-align:center;"><?= htmlspecialchars((string)($item['score_parallel_distributed'] ?? '—')) ?></td>
<td style="padding:6px 8px;text-align:center;font-weight:bold;color:#fbbf24;"><?= htmlspecialchars((string)($item['total_score'] ?? '—')) ?></td>
<td style="padding:6px 8px;opacity:.85;"><?= htmlspecialchars($item['versionless_api_comment'] ?? '—') ?></td>
<td style="padding:6px 8px;opacity:.85;"><?= htmlspecialchars($item['framework_note'] ?? '—') ?></td>
<td style="padding:6px 8px;opacity:.85;"><?= htmlspecialchars($item['database_note'] ?? '—') ?></td>
<td style="padding:6px 8px;opacity:.85;"><?= htmlspecialchars($item['cockroachdb_similarity_comment'] ?? '—') ?></td>
<td style="padding:6px 8px;opacity:.85;"><?= htmlspecialchars($item['snowflake_similarity_comment'] ?? '—') ?></td>
<td style="padding:6px 8px;opacity:.85;"><?= htmlspecialchars($item['aruaru_db_similarity_comment'] ?? '—') ?></td>
</tr>
<?php endforeach; ?>
<tr id="<?= htmlspecialchars(aruaru_anchor_id_for_tech('lang', 'Mojo'), ENT_QUOTES, 'UTF-8') ?>" style="border-bottom:1px solid #1e3555;background:rgba(167,139,250,.1);">
<td style="padding:6px 8px;text-align:center;font-weight:bold;color:#c4b5fd;line-height:1.35;">圏外<br><span style="display:inline-block;margin-top:4px;font-size:.84rem;font-weight:900;color:#e9d5ff;letter-spacing:.04em;">注目</span></td>
<td style="padding:6px 8px;"><?php aruaru_tech_link_button('Mojo', 'lang:Mojo', '#c4b5fd'); ?> <span style="font-size:.84rem;font-weight:700;color:#94a3b8;">（TOP80圏外・要注目）</span></td>
<td style="padding:6px 8px;">—</td>
<td style="padding:6px 8px;">—</td>
<td style="padding:6px 8px;">—</td>
<td style="padding:6px 8px;">—</td>
<td style="padding:6px 8px;">中程度（期待）</td>
<td style="padding:6px 8px;">中規模（期待）</td>
<td style="padding:6px 8px;">Pythonライク構文・システム/AI用途を志向</td>
<td style="padding:6px 8px;">LLVMパイプライン上でOSSとして開発（ライセンスはリリース形態に依存）</td>
<td style="padding:6px 8px;">async/awaitモデルを志向（実装 maturity は開発途中に応じて変化）</td>
<td style="padding:6px 8px;">高性能ランタイム前提なら高RPS構成も期待（ベンチは環境依存）</td>
<td style="padding:6px 8px;opacity:.95;">見た目はPython、中身はRust＆C++でハイスピード＆ハイセキュリティで、元Appleのプログラマーが開発中です</td>
</tr>
</table>
</div>

<h3 style="color:#ffaa00;margin-top:40px;" id="aruaru-top80-fw">⚡ 人気フレームワーク TOP80</h3>
<div style="overflow:auto;">
<table style="width:100%;border-collapse:collapse;font-size:15px;">
<tr style="background:#10233f;text-align:left;">
<th style="padding:8px;">順位</th><th style="padding:8px;">Framework</th><th style="padding:8px;">チーム開発</th><th style="padding:8px;">保守性</th><th style="padding:8px;">初心者向け</th><th style="padding:8px;">速度</th><th style="padding:8px;">必要メモリ容量</th><th style="padding:8px;">大規模開発</th><th style="padding:8px;">AI分析コメント</th>
</tr>
<?php foreach($AI_TECH_DATA['frameworks'] as $item): ?>
<?php $aruaru_row_id = aruaru_anchor_id_for_tech('fw', (string)$item['name']); ?>
<tr id="<?= htmlspecialchars($aruaru_row_id, ENT_QUOTES, 'UTF-8') ?>" style="border-bottom:1px solid #1e3555;">
<td style="padding:6px 8px;text-align:center;font-weight:bold;color:#ffaa00;"><?= htmlspecialchars((string)$item['rank']) ?></td>
<td style="padding:6px 8px;"><?php aruaru_tech_link_button((string)$item['name'], 'fw:' . $item['name'], '#ffaa00'); ?></td>
<td style="padding:6px 8px;"><?= htmlspecialchars($item['team_dev']) ?></td>
<td style="padding:6px 8px;"><?= htmlspecialchars($item['maintenance']) ?></td>
<td style="padding:6px 8px;"><?= htmlspecialchars($item['beginner']) ?></td>
<td style="padding:6px 8px;"><?= htmlspecialchars($item['speed']) ?></td>
<td style="padding:6px 8px;"><?= htmlspecialchars($item['memory'] ?? '中程度') ?></td>
<td style="padding:6px 8px;"><?= htmlspecialchars($item['large_scale']) ?></td>
<td style="padding:6px 8px;opacity:.85;"><?= htmlspecialchars($item['ai_comment']) ?></td>
</tr>
<?php endforeach; ?>
<tr id="<?= htmlspecialchars(aruaru_anchor_id_for_tech('fw', 'WunderGraph'), ENT_QUOTES, 'UTF-8') ?>" style="border-bottom:1px solid #1e3555;background:rgba(249,115,22,.12);">
<td style="padding:6px 8px;text-align:center;font-weight:bold;color:#fed7aa;line-height:1.35;">圏外<br><span style="display:inline-block;margin-top:4px;font-size:.84rem;font-weight:900;color:#fbbf24;letter-spacing:.04em;">重要</span></td>
<td style="padding:6px 8px;"><?php aruaru_tech_link_button('WunderGraph', 'fw:WunderGraph', '#fed7aa'); ?> <span style="font-size:.84rem;font-weight:700;color:#94a3b8;">（TOP80圏外・要注目）</span></td>
<td style="padding:6px 8px;">高い</td>
<td style="padding:6px 8px;">高い</td>
<td style="padding:6px 8px;">中級向け</td>
<td style="padding:6px 8px;">高速</td>
<td style="padding:6px 8px;">中程度</td>
<td style="padding:6px 8px;">大規模対応</td>
<td style="padding:6px 8px;opacity:.95;">GraphQL・REST・OpenAPIを束ねるBFF/ゲートウェイ。WunderGraph Cosmoでスキーマ連携・OSS＋クラウド。ランキング圏外だがAPI統合では重要。</td>
</tr>
<tr style="border-bottom:1px solid #1e3555;background:rgba(249,115,22,.12);">
<td style="padding:6px 8px;text-align:center;font-weight:bold;color:#fed7aa;line-height:1.35;">圏外<br><span style="display:inline-block;margin-top:4px;font-size:.84rem;font-weight:900;color:#fbbf24;letter-spacing:.04em;">重要</span></td>
<td style="padding:6px 8px;"><?php aruaru_tech_link_button('VersionlessAPI', 'fw:VersionlessAPI', '#fed7aa'); ?> <span style="font-size:.84rem;font-weight:700;color:#94a3b8;">（TOP80圏外・要注目）</span></td>
<td style="padding:6px 8px;">普通</td>
<td style="padding:6px 8px;">高い</td>
<td style="padding:6px 8px;">難しい</td>
<td style="padding:6px 8px;">高速</td>
<td style="padding:6px 8px;">小〜中程度</td>
<td style="padding:6px 8px;">中規模対応</td>
<td style="padding:6px 8px;opacity:.95;">URLにv1/v2を載せないAPI進化の潮流。互換レイヤー・コントラクト駆動とセットで議論が増加。ランキング圏外だが設計トレンドとして重要。</td>
</tr>
</table>
</div>

<h3 style="color:#ff66cc;margin-top:40px;">🗄 DATABASE ランキング TOP80</h3>
<div style="overflow:auto;">
<table style="width:100%;border-collapse:collapse;font-size:15px;">
<tr style="background:#10233f;text-align:left;">
<th style="padding:8px;">順位</th><th style="padding:8px;">DATABASE</th><th style="padding:8px;">処理速度</th><th style="padding:8px;">スケール</th><th style="padding:8px;">分散対応</th><th style="padding:8px;">必要メモリ容量</th><th style="padding:8px;">AI分析コメント</th>
</tr>
<?php foreach($AI_TECH_DATA['databases'] as $item): ?>
<tr style="border-bottom:1px solid #1e3555;">
<td style="padding:6px 8px;text-align:center;font-weight:bold;color:#ff66cc;"><?= htmlspecialchars((string)$item['rank']) ?></td>
<td style="padding:6px 8px;"><?php aruaru_tech_link_button((string)$item['name'], 'db:' . $item['name'], '#ff66cc'); ?></td>
<td style="padding:6px 8px;"><?= htmlspecialchars($item['speed']) ?></td>
<td style="padding:6px 8px;"><?= htmlspecialchars($item['scale']) ?></td>
<td style="padding:6px 8px;"><?= htmlspecialchars($item['distributed']) ?></td>
<td style="padding:6px 8px;"><?= htmlspecialchars($item['memory'] ?? '中〜大') ?></td>
<td style="padding:6px 8px;opacity:.85;"><?= htmlspecialchars($item['ai_comment']) ?></td>
</tr>
<?php endforeach; ?>
<tr style="border-bottom:1px solid #1e3555;background:rgba(255,102,204,.1);">
<td style="padding:6px 8px;text-align:center;font-weight:bold;color:#f9a8d4;">圏外</td>
<td style="padding:6px 8px;"><?php aruaru_tech_link_button('AWS', 'cloud:AWS', '#ff66cc'); ?> <span style="font-size:.84rem;color:#94a3b8;">（クラウド・要注目）</span></td>
<td style="padding:6px 8px;">高速</td>
<td style="padding:6px 8px;">超大規模</td>
<td style="padding:6px 8px;">完全対応</td>
<td style="padding:6px 8px;">サービス次第</td>
<td style="padding:6px 8px;opacity:.95;">AWS Skill Builder・公式ドキュメントで学習</td>
</tr>
</table>
</div>

<h2 style="font-size:26px;color:#a5f3fc;margin-top:48px;">📚 おすすめ学習サービス TOP50（日本語・英語）</h2>
<p style="opacity:.75;font-size:15px;margin-bottom:20px;">学習塾・家庭教師紹介サービス・PC教室・プログラミング教室（対面・オンライン・動画サービス等を含む）・学習タブレット。ランキング表の名称をクリックすると、該当技術の学習リンク一覧（日英）が開きます。</p>
<?php foreach ($aruaru_learn_cats as $cat): ?>
<h3 style="color:#7dd3fc;margin-top:32px;font-size:20px;"><?= htmlspecialchars($cat['title_ja']) ?> ／ <?= htmlspecialchars($cat['title_en']) ?></h3>
<div class="row g-3">
  <div class="col-12 col-lg-6">
    <h4 style="font-size:15px;color:#fff;margin-bottom:8px;">🇯🇵 日本語 TOP50</h4>
    <div style="max-height:420px;overflow:auto;border:1px solid #1e3555;border-radius:10px;">
    <table style="width:100%;border-collapse:collapse;font-size:15px;">
    <tr style="background:#10233f;"><th style="padding:6px;">#</th><th style="padding:6px;">名称</th><th style="padding:6px;">特徴</th><th style="padding:6px;">料金</th></tr>
    <?php aruaru_learn_table_rows($cat['ja']); ?>
    </table>
    </div>
  </div>
  <div class="col-12 col-lg-6">
    <h4 style="font-size:15px;color:#fff;margin-bottom:8px;">🇺🇸 English TOP50</h4>
    <div style="max-height:420px;overflow:auto;border:1px solid #1e3555;border-radius:10px;">
    <table style="width:100%;border-collapse:collapse;font-size:15px;">
    <tr style="background:#10233f;"><th style="padding:6px;">#</th><th style="padding:6px;">Name</th><th style="padding:6px;">Features</th><th style="padding:6px;">Price</th></tr>
    <?php aruaru_learn_table_rows($cat['en']); ?>
    </table>
    </div>
  </div>
</div>
<?php endforeach; ?>

<div id="aruaru-learn-modal" style="display:none;position:fixed;inset:0;z-index:99999;background:rgba(0,0,0,.72);padding:16px;box-sizing:border-box;">
  <div style="max-width:960px;margin:24px auto;background:#0a1628;border:1px solid #334155;border-radius:16px;max-height:calc(100vh - 48px);display:flex;flex-direction:column;box-shadow:0 20px 60px rgba(0,0,0,.5);">
    <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 18px;border-bottom:1px solid #1e3555;flex-shrink:0;">
      <h3 id="aruaru-learn-modal-title" style="margin:0;font-size:18px;color:#00ffff;">学習リンク</h3>
      <button type="button" id="aruaru-learn-modal-close" style="background:#1e3555;border:none;color:#fff;padding:8px 14px;border-radius:8px;cursor:pointer;font-weight:bold;">閉じる</button>
    </div>
    <div style="display:flex;flex:1;min-height:0;overflow:hidden;">
      <div style="flex:1;padding:14px;border-right:1px solid #1e3555;overflow-y:auto;">
        <h4 style="color:#fff;font-size:15px;margin:0 0 10px;">🇯🇵 日本語</h4>
        <ul id="aruaru-learn-list-ja" style="list-style:none;margin:0;padding:0;font-size:15px;line-height:1.6;"></ul>
      </div>
      <div style="flex:1;padding:14px;overflow-y:auto;">
        <h4 style="color:#fff;font-size:15px;margin:0 0 10px;">🇺🇸 English</h4>
        <ul id="aruaru-learn-list-en" style="list-style:none;margin:0;padding:0;font-size:15px;line-height:1.6;"></ul>
      </div>
    </div>
  </div>
</div>
<script>
(function(){
  var PANEL = <?= json_encode($aruaru_learn_panel, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS) ?>;
  var modal = document.getElementById('aruaru-learn-modal');
  var titleEl = document.getElementById('aruaru-learn-modal-title');
  var listJa = document.getElementById('aruaru-learn-list-ja');
  var listEn = document.getElementById('aruaru-learn-list-en');
  function esc(s){ var d=document.createElement('div'); d.textContent=s||''; return d.innerHTML; }
  function renderList(ul, items){
    ul.innerHTML = '';
    (items||[]).forEach(function(it){
      var li = document.createElement('li');
      li.style.cssText = 'margin-bottom:12px;padding-bottom:10px;border-bottom:1px solid rgba(30,53,85,.8)';
      li.innerHTML = '<a href="'+esc(it.url)+'" target="_blank" rel="noopener noreferrer" style="color:#7dd3fc;font-weight:bold;">'+esc(it.name)+'</a>'
        + '<div style="opacity:.85;margin-top:4px;">'+esc(it.features)+'</div>'
        + '<div style="font-size:.85rem;opacity:.7;">料金: '+esc(it.price)+'</div>';
      ul.appendChild(li);
    });
  }
  function openPanel(key){
    var data = PANEL[key];
    if(!data) return;
    titleEl.textContent = data.name + ' — おすすめ学習リンク';
    renderList(listJa, data.ja);
    renderList(listEn, data.en);
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
  }
  function closeModal(){ modal.style.display='none'; document.body.style.overflow=''; }
  document.querySelectorAll('.aruaru-tech-link').forEach(function(btn){
    btn.addEventListener('click', function(){ openPanel(btn.getAttribute('data-panel-key')); });
  });
  document.getElementById('aruaru-learn-modal-close').addEventListener('click', closeModal);
  modal.addEventListener('click', function(e){ if(e.target===modal) closeModal(); });
  document.addEventListener('keydown', function(e){ if(e.key==='Escape') closeModal(); });
})();
</script>

<!-- ============================================================
     英会話アプリ・サイト TOP50
============================================================ -->
<h2 style="font-size:26px;color:#34d399;margin-top:56px;margin-bottom:6px;" id="aruaru-eikaiwa-top50">
  🌏 スマホ・タブレット・PC 優れた英会話アプリ・サイト TOP50
</h2>
<p style="opacity:.72;font-size:15px;margin-bottom:4px;">
  📅 最終更新: <?= htmlspecialchars($aruaru_eikaiwa['updated_at'] ?? '—') ?> ／ 週1回（7日TTL）Cron自動更新
</p>
<p style="opacity:.8;font-size:15px;margin-bottom:20px;line-height:1.65;color:#a7f3d0;">
  📱=スマホ・タブレット　💻=PC・ブラウザ　🤖AI搭載バッジあり。料金は目安（変更の場合あり）。無料プランから始められるものも多数。
</p>
<div style="overflow-x:auto;">
<table style="width:100%;border-collapse:collapse;font-size:15px;min-width:900px;">
<thead>
<tr style="background:#0d2b1f;text-align:left;">
  <th style="padding:8px 6px;color:#34d399;white-space:nowrap;">順位</th>
  <th style="padding:8px 6px;color:#34d399;">アプリ・サービス名</th>
  <th style="padding:8px 6px;color:#34d399;white-space:nowrap;">対応端末</th>
  <th style="padding:8px 6px;color:#34d399;white-space:nowrap;">学習スタイル</th>
  <th style="padding:8px 6px;color:#34d399;white-space:nowrap;">レベル</th>
  <th style="padding:8px 6px;color:#34d399;">料金目安</th>
  <th style="padding:8px 6px;color:#34d399;">ポイント・特徴</th>
</tr>
</thead>
<tbody>
<?php foreach ($aruaru_eikaiwa['rows'] ?? [] as $ew): ?>
<?php $ai_badge = !empty($ew['ai']) ? ' <span style="font-size:.84rem;background:#6366f1;color:#fff;padding:1px 5px;border-radius:4px;vertical-align:middle;">AI</span>' : ''; ?>
<tr style="border-bottom:1px solid #1a3a2a;">
  <td style="padding:6px 8px;text-align:center;font-weight:bold;color:#34d399;"><?= htmlspecialchars((string)($ew['rank'] ?? '')) ?></td>
  <td style="padding:6px 8px;font-weight:bold;white-space:nowrap;">
    <a href="<?= htmlspecialchars((string)($ew['url'] ?? '#'), ENT_QUOTES, 'UTF-8') ?>"
       target="_blank" rel="noopener noreferrer"
       style="color:#6ee7b7;text-decoration:underline;"><?= htmlspecialchars((string)($ew['name'] ?? '')) ?></a><?= $ai_badge ?>
  </td>
  <td style="padding:6px 8px;text-align:center;font-size:16px;"><?= htmlspecialchars((string)($ew['platform'] ?? '')) ?></td>
  <td style="padding:6px 8px;white-space:nowrap;"><?= htmlspecialchars((string)($ew['style'] ?? '')) ?></td>
  <td style="padding:6px 8px;white-space:nowrap;"><?= htmlspecialchars((string)($ew['level'] ?? '')) ?></td>
  <td style="padding:6px 8px;font-size:15px;color:#fde68a;"><?= htmlspecialchars((string)($ew['price'] ?? '')) ?></td>
  <td style="padding:6px 8px;opacity:.88;font-size:15px;"><?= htmlspecialchars((string)($ew['note'] ?? '')) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<p style="opacity:.6;font-size:15px;margin-top:10px;line-height:1.6;">
  ※ 料金・サービス内容は変更になる場合があります。各公式サイトで最新情報をご確認ください。<br>
  ※ AI バッジはAI音声認識・AIコーチング・AI対話機能を主な特徴として持つサービスです。
</p>

<div style="margin-top:48px;padding:18px;border-radius:14px;background:#1a1530;border:1px solid #6d28d9;">
<h3 style="color:#c4b5fd;margin-top:0;margin-bottom:10px;font-size:18px;">🎓 未経験から、無料IT研修＆無料の転職エージェントサービス</h3>
<p style="opacity:.88;font-size:15px;line-height:1.65;color:#ede9fe;margin-bottom:12px;">プログラミング未経験から、受講料無料のIT研修を受けながら正社員を目指せるサービスや、無料で使える転職エージェントの一例検索です。条件・対象年齢・地域は各サービスごとに異なります。</p>
<p style="margin:0;"><a href="<?= htmlspecialchars($aruaru_link_it_kenshu, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" style="color:#ddd6fe;font-weight:bold;font-size:15px;text-decoration:underline;">未経験　無料IT研修　無料 転職エージェント サービス を Google で開く</a></p>
</div>

<div style="margin-top:20px;padding:18px;border-radius:14px;background:#16231a;border:1px solid #2d5a28;">
<h3 style="color:#bef264;margin-top:0;margin-bottom:10px;font-size:18px;">🏗️ 未経験から大工・造作大工・型枠大工・木造建築大工のリンクはこちら</h3>
<p style="opacity:.88;font-size:15px;line-height:1.65;color:#ecfccb;margin-bottom:12px;">大工見習い〜専門工種への求人の一例検索です。条件は求人票・協会・訓練施設ごとに異なります。</p>
<p style="margin:0;"><a href="<?= htmlspecialchars($aruaru_link_daiku, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" style="color:#bbf7d0;font-weight:bold;font-size:15px;text-decoration:underline;">未経験から大工　造作大工　型枠大工　木造建築大工 を Google で開く</a></p>
</div>

<div style="margin-top:20px;padding:18px;border-radius:14px;background:#141c2f;border:1px solid #3b5998;">
<h3 style="color:#93c5fd;margin-top:0;margin-bottom:10px;font-size:18px;">📐 未経験・無資格の大工・CAD・施設管理・建築現場管理から、将来 一級建築士などを目指せる求人</h3>
<p style="opacity:.85;font-size:15px;line-height:1.65;color:#dbeafe;margin-bottom:12px;">未経験・無資格の大工やCADオペレーター、施設管理、建築現場管理からスタートし、将来は一級建築士・木造建築士・管理建築士・1級建築施工管理技士などの資格取得を目指せる求人の一例検索です。資格支援や未経験可の条件は媒体・会社により差があります。求人票や行政の相談窓口で確認してください。</p>
<p style="margin:0;"><a href="<?= htmlspecialchars($aruaru_link_kenchiku_kanri, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" style="color:#7dd3fc;font-weight:bold;font-size:15px;text-decoration:underline;">未経験・無資格から 一級建築士・木造建築士・管理建築士・1級建築施工管理技士 を目指せる求人 を Google で開く</a></p>
</div>






<!-- aruaru-lady 移転案内バナー -->
<div style="margin:0 0 18px;padding:14px 18px;border-radius:12px;background:linear-gradient(135deg,rgba(253,164,175,.13),rgba(251,113,133,.08));border:1.5px solid #fda4af;display:flex;align-items:center;flex-wrap:wrap;gap:10px;">
  <span style="font-size:18px;">💃</span>
  <span style="color:#fda4af;font-weight:700;font-size:15px;">キャバクラ・TVチャットレディなど主に女性向けのお仕事情報は移転しました →</span>
  <a href="https://audiocafe.tokyo/aruaru-lady" target="_blank" rel="noopener noreferrer"
     style="display:inline-block;padding:6px 16px;border-radius:8px;background:linear-gradient(135deg,#be185d,#9d174d);color:#fff;font-weight:800;font-size:15px;text-decoration:none;white-space:nowrap;box-shadow:0 2px 8px rgba(190,24,93,.35);">
    📍 audiocafe.tokyo/aruaru-lady
  </a>
</div>

<!-- aruaru.tokyo リンクバナー -->
<div style="margin:12px 0 18px;padding:14px 18px;border-radius:12px;background:linear-gradient(135deg,rgba(255,122,69,.13),rgba(47,111,237,.08));border:1.5px solid #ff7a45;display:flex;align-items:center;flex-wrap:wrap;gap:10px;">
  <span style="font-size:18px;">🎲</span>
  <span style="color:#ff7a45;font-weight:700;font-size:15px;">「あるある」まとめ & 開発リポジトリ紹介はこちら →</span>
  <a href="https://aruaru.tokyo" target="_blank" rel="noopener noreferrer"
     style="display:inline-block;padding:6px 16px;border-radius:8px;background:linear-gradient(135deg,#ff7a45,#2f6fed);color:#fff;font-weight:800;font-size:15px;text-decoration:none;white-space:nowrap;box-shadow:0 2px 8px rgba(255,122,69,.35);">
    📍 aruaru.tokyo
  </a>
</div>
</section>

