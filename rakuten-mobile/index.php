<?php
/**
 * audiocafe.tokyo/rakuten-mobile/index.php — 楽天モバイル情報専用ページ
 * /aruaru と /aruaru-lady の楽天コーナーを統合・独立化
 * Cron: aruaru/index.php --cron-all に相乗り（毎日05:00AMに自動クロール更新）
 *       cron内で RAKUTEN_MOBILE_CACHE_DIR を /rakuten-mobile/ に向けて実行
 * 生成: 2026-06-02
 */

/* =========================================================
   ★ Cron 相乗りモード
   aruaru/index.php --cron-all の末尾に以下を追加:
   // ⑧ rakuten-mobile ページ用キャッシュ更新
   $rmDir = __DIR__ . '/../rakuten-mobile/';
   if (!defined('RAKUTEN_MOBILE_CACHE_DIR')) define('RAKUTEN_MOBILE_CACHE_DIR', $rmDir);
   require_once $rmDir . 'index.php';  // --cron-rakuten-mobile で実行
   ※ 本ファイル単体での手動実行も可:
      php /path/to/rakuten-mobile/index.php --cron-all
========================================================= */

/* =========================================================
   キャッシュファイルパス（/rakuten-mobile/ ディレクトリ内に独立）
========================================================= */
$_rm_dir = defined('RAKUTEN_MOBILE_CACHE_DIR') ? rtrim((string)constant('RAKUTEN_MOBILE_CACHE_DIR'), '/') . '/' : __DIR__ . '/';

if (!defined('RM_CACHE_FILE'))    define('RM_CACHE_FILE',    $_rm_dir . 'rakuten-mobile-cache.json');
if (!defined('RM_INTL_CACHE'))    define('RM_INTL_CACHE',    $_rm_dir . 'rakuten-intl-call-cache.json');
if (!defined('RM_PLAT_CACHE'))    define('RM_PLAT_CACHE',    $_rm_dir . 'rakuten-platinum-cache.json');
if (!defined('RM_CACHE_TTL'))     define('RM_CACHE_TTL',     86400); // 24時間
if (!defined('RM_INTL_TTL'))      define('RM_INTL_TTL',      86400);
if (!defined('RM_PLAT_TTL'))      define('RM_PLAT_TTL',      86400);

/* =========================================================
   クロール関数（二重 include 防止）
========================================================= */
if (!function_exists('rm_h')) {

function rm_fetch_html(string $url): string {
    $ctx = stream_context_create(['http' => [
        'timeout' => 10, 'follow_location' => 1, 'max_redirects' => 5,
        'user_agent' => 'Mozilla/5.0 (compatible; AudiocafeBot/1.0; +https://audiocafe.tokyo/)',
        'ignore_errors' => true,
    ]]);
    $r = @file_get_contents($url, false, $ctx);
    return is_string($r) ? $r : '';
}

function rm_strip(string $html): string {
    $t = preg_replace('/<script[^>]*>.*?<\/script>/si', '', $html);
    $t = preg_replace('/<style[^>]*>.*?<\/style>/si', '', $t);
    $t = preg_replace('/<[^>]+>/', ' ', $t);
    $t = html_entity_decode($t, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return preg_replace('/\s+/', ' ', $t);
}

/* --- 基本料金クロール --- */
function rm_fetch_price(): array {
    $d = [
        'price'      => '最大3,278円（税込）',
        'plan'       => 'Rakuten最強プラン',
        'updated_at' => date('Y/m/d H:i'),
        'source_url' => 'https://network.mobile.rakuten.co.jp/fee/saikyo-plan/',
    ];
    $html = rm_fetch_html('https://network.mobile.rakuten.co.jp/fee/saikyo-plan/');
    if ($html && preg_match('/([0-9,]+)円[（(]税込[）)]/', $html, $m)) {
        $d['price'] = $m[1] . '円（税込）';
    }
    $d['updated_at'] = date('Y/m/d H:i');
    return $d;
}

function rm_get_price(): array {
    if (is_readable(RM_CACHE_FILE) && (time() - (int)@filemtime(RM_CACHE_FILE)) < RM_CACHE_TTL) {
        $c = @json_decode(@file_get_contents(RM_CACHE_FILE), true);
        if (is_array($c) && !empty($c['price'])) return $c;
    }
    $data = rm_fetch_price();
    @file_put_contents(RM_CACHE_FILE, json_encode($data, JSON_UNESCAPED_UNICODE), LOCK_EX);
    return $data;
}

/* --- 国際通話クロール --- */
function rm_intl_crawl(): array {
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
        'notes_ja'       => '月980円の「国際通話かけ放題」は主に「日本→海外」向けですが、Rakuten Link なら海外→日本も無料（対象国・条件あり）。',
        'notes_en'       => 'The 980-yen plan mainly covers Japan→overseas, but Rakuten Link also enables free overseas→Japan calls (conditions apply).',
        'crawled_at'     => date('Y/m/d H:i'),
        'crawl_success'  => false,
    ];
    $targets = [
        'intl_call' => 'https://network.mobile.rakuten.co.jp/service/international-call-free/',
        'intl_top'  => 'https://network.mobile.rakuten.co.jp/service/international/',
        'rlink'     => 'https://network.mobile.rakuten.co.jp/service/rakuten-link/',
    ];
    $texts = []; $ok = 0;
    foreach ($targets as $k => $url) {
        $h = rm_fetch_html($url);
        if ($h !== '') { $texts[$k] = rm_strip($h); $ok++; }
    }
    if ($ok === 0) return $d;
    $all = implode(' ', $texts);
    $d['crawl_success'] = true;
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

function rm_get_intl(): array {
    if (is_readable(RM_INTL_CACHE) && (time() - (int)@filemtime(RM_INTL_CACHE)) < RM_INTL_TTL) {
        $c = @json_decode(@file_get_contents(RM_INTL_CACHE), true);
        if (is_array($c) && !empty($c['crawled_at'])) return $c;
    }
    $data = rm_intl_crawl();
    @file_put_contents(RM_INTL_CACHE, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
    return $data;
}

/* --- プラチナバンド・衛星クロール --- */
function rm_plat_crawl(): array {
    $d = [
        'platinum_status_ja'   => '700MHz帯プラチナバンドを整備中。地下・屋内・山間部でのつながりやすさを改善。',
        'platinum_status_en'   => 'Rakuten Mobile is expanding its 700MHz Platinum Band to improve indoor, underground, and rural coverage.',
        'platinum_coverage_ja' => '全国整備進行中（順次拡大中）',
        'platinum_coverage_en' => 'Nationwide rollout in progress',
        'platinum_detail_ja'   => '700MHz帯（プラチナバンド）は電波が建物内や地下街まで届きやすい低周波数帯。屋内での通話・データ通信の安定性が向上。',
        'platinum_detail_en'   => 'The 700MHz Platinum Band penetrates buildings and underground areas, improving indoor call and data stability.',
        'satellite_status_ja'  => 'AST SpaceMobile との提携により、衛星ブロードバンド通話サービスを準備中。',
        'satellite_status_en'  => 'In partnership with AST SpaceMobile, Rakuten Mobile is developing satellite broadband calling.',
        'satellite_launch_ja'  => '商用サービス開始時期は未定（2025〜2026年を目標と報道あり）',
        'satellite_launch_en'  => 'Commercial launch TBD (reports suggest 2025–2026 target)',
        'satellite_detail_ja'  => '低軌道衛星（LEO）を利用し、山間部・離島・海上でも通常のスマートフォンで通話・データ通信が可能になる見込み。',
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
        $html = @file_get_contents($url, false, $ctx);
        if (is_string($html) && $html !== '') {
            $texts[$k] = rm_strip($html);
            $ok++;
        }
    }
    if ($ok === 0) return $d;
    $all = implode(' ', $texts);
    $d['crawl_success'] = true;
    foreach (['/プラチナバンド[^。]{0,60}([0-9]+(?:\.[0-9]+)?)\s*%/u',
              '/700\s*MHz[^。]{0,60}([0-9]+(?:\.[0-9]+)?)\s*%/u'] as $pat) {
        if (preg_match($pat, $all, $m)) {
            $pct = $m[1];
            $d['platinum_coverage_ja'] = "人口カバー率 {$pct}%（公式より）";
            $d['platinum_coverage_en'] = "Population coverage {$pct}% (official)";
            break;
        }
    }
    foreach (['/(プラチナバンド[^。]{15,150}。)/u', '/(700\s*MHz[^。]{15,120}。)/u'] as $pat) {
        if (preg_match($pat, $all, $m)) {
            $s = mb_substr(trim($m[1]), 0, 160);
            if (mb_strlen($s) > 20) { $d['platinum_status_ja'] = $s . '（楽天公式より）'; break; }
        }
    }
    foreach (['/(AST\s*SpaceMobile[^。]{10,180}。)/u',
              '/(衛星[^。]{5,80}(?:通話|サービス|接続)[^。]{0,60}。)/u'] as $pat) {
        if (preg_match($pat, $all, $m)) {
            $s = mb_substr(trim($m[1]), 0, 200);
            if (mb_strlen($s) > 15) { $d['satellite_status_ja'] = $s . '（公式より）'; break; }
        }
    }
    foreach (['/(衛星[^。]{0,60}20[2-9][0-9]年[^。]{0,50}。)/u',
              '/(AST[^。]{0,80}20[2-9][0-9][^。]{0,50}。)/u'] as $pat) {
        if (preg_match($pat, $all, $m)) {
            $s = mb_substr(trim($m[1]), 0, 150);
            if (mb_strlen($s) > 20) { $d['satellite_launch_ja'] = $s . '（公式より）'; break; }
        }
    }
    $d['crawled_at'] = date('Y/m/d H:i');
    return $d;
}

function rm_get_plat(): array {
    if (is_readable(RM_PLAT_CACHE) && (time() - (int)@filemtime(RM_PLAT_CACHE)) < RM_PLAT_TTL) {
        $c = @json_decode(@file_get_contents(RM_PLAT_CACHE), true);
        if (is_array($c) && !empty($c['crawled_at'])) return $c;
    }
    $data = rm_plat_crawl();
    @file_put_contents(RM_PLAT_CACHE, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
    return $data;
}

/* =========================================================
   ★ --cron-all: 単体実行モード（または aruaru --cron-all から include で呼ばれる場合）
   RAKUTEN_MOBILE_CRON_NO_EXIT が定義されている場合は exit しない（include用）
========================================================= */
if (PHP_SAPI === 'cli' && in_array('--cron-all', $argv ?? [], true)) {
    $t0 = microtime(true);
    echo "\n[" . date("Y-m-d H:i:s") . "] ===== rakuten-mobile --cron-all 開始 =====\n";
    echo "[" . date("Y-m-d H:i:s") . "] キャッシュ先: {$_rm_dir}\n";

    echo "[" . date("Y-m-d H:i:s") . "] [1/3] 基本料金 クロール...\n";
    @unlink(RM_CACHE_FILE);
    $rk = rm_fetch_price();
    @file_put_contents(RM_CACHE_FILE, json_encode($rk, JSON_UNESCAPED_UNICODE), LOCK_EX);
    echo "[" . date("Y-m-d H:i:s") . "] [1/3] 完了 — 料金: {$rk['price']}\n";

    echo "[" . date("Y-m-d H:i:s") . "] [2/3] 国際通話 クロール...\n";
    @unlink(RM_INTL_CACHE);
    $intl = rm_intl_crawl();
    @file_put_contents(RM_INTL_CACHE, json_encode($intl, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
    echo "[" . date("Y-m-d H:i:s") . "] [2/3] 完了 — {$intl['intl_plan_price_ja']} / {$intl['intl_countries_count']}カ国 / " . ($intl['crawl_success'] ? 'SUCCESS' : 'FALLBACK') . "\n";

    echo "[" . date("Y-m-d H:i:s") . "] [3/3] プラチナバンド・衛星 クロール...\n";
    @unlink(RM_PLAT_CACHE);
    $plat = rm_plat_crawl();
    @file_put_contents(RM_PLAT_CACHE, json_encode($plat, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
    echo "[" . date("Y-m-d H:i:s") . "] [3/3] 完了 — {$plat['platinum_coverage_ja']} / " . ($plat['crawl_success'] ? 'SUCCESS' : 'FALLBACK') . "\n";

    $now = time();
    @touch(RM_INTL_CACHE,  $now);
    @touch(RM_PLAT_CACHE,  $now);
    @touch(RM_CACHE_FILE,  $now);

    $elapsed = round(microtime(true) - $t0, 2);
    echo "[" . date("Y-m-d H:i:s") . "] ===== rakuten-mobile --cron-all 完了（{$elapsed}秒）=====\n\n";
    // include から呼ばれた場合は exit しない（aruaru --cron-all 相乗り時）
    if (!defined('RAKUTEN_MOBILE_CRON_NO_EXIT')) {
        exit(0);
    }
    return; // include 元へ戻る
}

/* =========================================================
   ページ表示用データ取得
========================================================= */
function rm_h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function rm_update_label($when = null, bool $full = true): string {
    if ($when === null || $when === '') {
        $ts = time();
    } elseif (is_int($when) || (is_string($when) && ctype_digit($when))) {
        $ts = (int)$when;
    } else {
        $ts = (int)strtotime((string)$when);
    }
    if ($ts <= 0) $ts = time();
    $main = date('Y-m-d h:i A', $ts);
    if (!$full) return $main;
    return $main . ' 更新済み（UPDATE）（05:00AMに毎朝更新中）';
}

function rm_load_display_data(): void
{
    global $_rm_rk, $_rm_intl, $_rm_plat;
    global $_rm_price, $_rm_updated;
    global $_rm_intl_price, $_rm_intl_count, $_rm_intl_name, $_rm_intl_crawled, $_rm_intl_ok;
    global $_rm_plat_status, $_rm_plat_detail, $_rm_plat_coverage;
    global $_rm_sat_status, $_rm_sat_detail, $_rm_sat_launch;
    global $_rm_plat_crawled, $_rm_plat_ok, $_rm_badge_ts, $_rm_links;

    $_rm_rk   = rm_get_price();
    $_rm_intl = rm_get_intl();
    $_rm_plat = rm_get_plat();

    $_rm_price      = rm_h((string)($_rm_rk['price']      ?? '最大3,278円（税込）'));
    $_rm_updated    = rm_h((string)($_rm_rk['updated_at'] ?? date('Y/m/d H:i')));

    $_rm_intl_price   = rm_h((string)($_rm_intl['intl_plan_price_ja']   ?? '月980円（税込）'));
    $_rm_intl_count   = rm_h((string)($_rm_intl['intl_countries_count'] ?? '66'));
    $_rm_intl_name    = rm_h((string)($_rm_intl['intl_plan_name_ja']    ?? '国際通話かけ放題'));
    $_rm_intl_crawled = $_rm_intl['crawled_at'] ?? null;
    $_rm_intl_ok      = !empty($_rm_intl['crawl_success']);

    $_rm_plat_status   = rm_h((string)($_rm_plat['platinum_status_ja']   ?? ''));
    $_rm_plat_detail   = rm_h((string)($_rm_plat['platinum_detail_ja']   ?? ''));
    $_rm_plat_coverage = rm_h((string)($_rm_plat['platinum_coverage_ja'] ?? ''));
    $_rm_sat_status    = rm_h((string)($_rm_plat['satellite_status_ja']  ?? ''));
    $_rm_sat_detail    = rm_h((string)($_rm_plat['satellite_detail_ja']  ?? ''));
    $_rm_sat_launch    = rm_h((string)($_rm_plat['satellite_launch_ja']  ?? ''));
    $_rm_plat_crawled  = $_rm_plat['crawled_at'] ?? null;
    $_rm_plat_ok       = !empty($_rm_plat['crawl_success']);

    $_rm_badge_ts = max(
        (int)strtotime((string)($_rm_intl['crawled_at'] ?? '')),
        (int)strtotime((string)($_rm_plat['crawled_at'] ?? '')),
        (int)@filemtime(RM_INTL_CACHE),
        (int)@filemtime(RM_PLAT_CACHE)
    );
    if ($_rm_badge_ts <= 0) $_rm_badge_ts = time();

    $_q = static fn(string $s): string => 'https://www.google.com/search?q=' . rawurlencode($s);
    $_rm_links = [
        'we2plus'      => $_q('楽天モバイル お勧め 1円 高性能CPU 富士通製 スマホ we2 plus'),
        'packet'       => $_q('楽天モバイル パケット放題 プラン'),
        'phone'        => $_q('楽天モバイル 電話放題 楽天リンク'),
        'link_android' => $_q('楽天モバイル スマホアプリの名前は 楽天リンク Android版'),
        'link_iphone'  => $_q('楽天モバイル スマホアプリの名前は 楽天リンク iPhone版'),
        'campaign'     => $_q('楽天モバイル 乗り換え キャンペーン'),
        'area'         => 'https://network.mobile.rakuten.co.jp/area/',
        'official'     => 'https://network.mobile.rakuten.co.jp/fee/saikyo-plan/',
        'intl_free'    => 'https://network.mobile.rakuten.co.jp/service/international-call-free/',
        'price_search' => $_q('楽天モバイル Rakuten最強プラン 料金'),
    ];
}

/**
 * 楽天モバイル OPEN/CLOSE 埋め込みパネル（aruaru / aruaru-lady 共通）
 */
function rm_render_embed_panel(string $idPrefix = 'acafe', string $cacheDir = ''): void
{
    if ($cacheDir === '') {
        $cacheDir = __DIR__;
    }
    $cornerId = $idPrefix . '-rakuten-mobile-corner';
    $openId   = $idPrefix . '-rm-open';
    $closeId  = $idPrefix . '-rm-close';
    $panelId  = $idPrefix . '-rm-panel';
    $e = static fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    ?>
<section class="pb-2 acafe-rm-embed-corner" id="<?= $e($cornerId) ?>" style="padding-top:1rem;">
  <div class="container-xl px-3" style="max-width:1200px;margin:0 auto;padding:0 16px;">
    <p style="margin:0 0 8px;font-size:15px;font-weight:800;color:#fca5a5;">📶 楽天モバイル関連情報コーナー</p>
    <button type="button" id="<?= $e($openId) ?>" class="acafe-rm-open-btn notranslate" translate="no" aria-expanded="false" aria-controls="<?= $e($panelId) ?>">OPEN</button>
    <div id="<?= $e($panelId) ?>" class="acafe-rm-panel" hidden>
      <button type="button" id="<?= $e($closeId) ?>" class="acafe-rm-close-btn notranslate" translate="no" aria-label="楽天モバイル情報を閉じる">CLOSE</button>
      <?php
      if (!defined('RAKUTEN_MOBILE_CACHE_DIR')) define('RAKUTEN_MOBILE_CACHE_DIR', $cacheDir);
      rm_load_display_data();
      if (!defined('RM_EMBED_MODE')) define('RM_EMBED_MODE', true);
      echo '<div class="aruaru-rm-embed-root">';
      rm_render_fragment();
      echo '</div>';
      ?>
    </div>
  </div>
</section>
<style>
#<?= $e($cornerId) ?> .acafe-rm-open-btn{
  display:inline-flex;align-items:center;justify-content:center;
  padding:10px 22px;border-radius:12px;
  border:2px solid rgba(239,68,68,.75);
  background:rgba(30,10,10,.92);color:#fca5a5;
  font:800 15px/1.2 'Noto Sans JP',system-ui,sans-serif;
  cursor:pointer;letter-spacing:.06em;
  box-shadow:0 2px 12px rgba(0,0,0,.35);
}
#<?= $e($cornerId) ?> .acafe-rm-open-btn:hover{background:rgba(239,68,68,.25);color:#fff}
#<?= $e($cornerId) ?> .acafe-rm-panel[hidden]{display:none!important}
#<?= $e($cornerId) ?> .acafe-rm-panel{margin-top:12px;padding:12px 0 4px;border-top:1px solid rgba(148,163,184,.22)}
#<?= $e($cornerId) ?> .acafe-rm-close-btn{
  display:inline-flex;align-items:center;justify-content:center;
  margin:0 0 12px;padding:8px 18px;border-radius:10px;
  border:2px solid rgba(34,211,238,.65);
  background:rgba(15,23,42,.95);color:#7dd3fc;
  font:800 14px/1.2 'Noto Sans JP',system-ui,sans-serif;
  cursor:pointer;letter-spacing:.05em;
}
#<?= $e($cornerId) ?> .acafe-rm-close-btn:hover{background:rgba(34,211,238,.15);color:#fff}
@media(max-width:560px){
  #<?= $e($cornerId) ?> .acafe-rm-open-btn,
  #<?= $e($cornerId) ?> .acafe-rm-close-btn{width:100%;box-sizing:border-box}
}
</style>
<script>
(function(){
  var openBtn  = document.getElementById(<?= json_encode($openId) ?>);
  var closeBtn = document.getElementById(<?= json_encode($closeId) ?>);
  var panel    = document.getElementById(<?= json_encode($panelId) ?>);
  if (!openBtn || !closeBtn || !panel) return;
  function openPanel(){
    panel.hidden = false;
    openBtn.hidden = true;
    openBtn.setAttribute('aria-expanded', 'true');
    try { panel.scrollIntoView({ behavior: 'smooth', block: 'start' }); } catch(e) {}
  }
  function closePanel(){
    panel.hidden = true;
    openBtn.hidden = false;
    openBtn.setAttribute('aria-expanded', 'false');
  }
  openBtn.addEventListener('click', openPanel);
  closeBtn.addEventListener('click', closePanel);
})();
</script>
    <?php
}

function rm_render_fragment(): void
{
    global $_rm_badge_ts, $_rm_rk, $_rm_price, $_rm_links;
    global $_rm_intl_crawled, $_rm_intl_ok, $_rm_intl_price, $_rm_intl_name, $_rm_intl_count;
    global $_rm_plat_crawled, $_rm_plat_ok, $_rm_sat_status, $_rm_sat_detail, $_rm_sat_launch;
    global $_rm_plat_status, $_rm_plat_detail, $_rm_plat_coverage;
    $_rm_embed = defined('RM_EMBED_MODE') && RM_EMBED_MODE;
    ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700;900&display=swap" rel="stylesheet">
<style>
<?php if ($_rm_embed): ?>
.aruaru-rm-embed-root #rm-update-badge{display:none!important}
.aruaru-rm-embed-root .page-wrap{padding:8px 0 32px;max-width:100%}
.aruaru-rm-embed-root .rm-hero{margin-top:0}
<?php endif; ?>
:root{
  --bg:#0b1220;--surface:rgba(15,23,42,.9);--border:rgba(148,163,184,.2);
  --red:#ef4444;--blue:#3b82f6;--cyan:#22d3ee;--purple:#a78bfa;--orange:#fb923c;
  --text:#e2e8f0;--dim:#94a3b8;--yellow:#fde68a;
}
.aruaru-rm-embed-root,.aruaru-rm-embed-root *{box-sizing:border-box}
<?php if (!$_rm_embed): ?>
*{box-sizing:border-box;margin:0;padding:0}
html{font-size:16px}
body{
  font-family:'Noto Sans JP',system-ui,sans-serif;
  background:var(--bg);color:var(--text);min-height:100vh;
  line-height:1.7;
}
<?php else: ?>
.aruaru-rm-embed-root{
  font-family:'Noto Sans JP',system-ui,sans-serif;
  color:var(--text);line-height:1.7;
}
<?php endif; ?>
<?php if ($_rm_embed): ?>
.aruaru-rm-embed-root a{color:#7dd3fc;text-decoration:underline}
.aruaru-rm-embed-root a:hover{color:#bae6fd}
<?php else: ?>
a{color:#7dd3fc;text-decoration:underline}
a:hover{color:#bae6fd}
<?php endif; ?>

#rm-update-badge{
  position:fixed;top:calc(env(safe-area-inset-top,0px) + 10px);
  left:calc(env(safe-area-inset-left,0px) + 10px);
  right:auto;max-width:calc(100vw - 20px - 92px);
  z-index:9999;pointer-events:none;
  display:inline-flex;align-items:center;
  height:1.65rem;line-height:1.65rem;padding:0 .7rem;
  border-radius:999px;
  background:rgba(30,10,10,.92);border:1px solid rgba(239,68,68,.55);
  color:#fca5a5;
  font:700 clamp(.58rem,2.6vw,.82rem)/1.65rem 'Noto Sans JP',system-ui,sans-serif;
  white-space:nowrap;overflow:hidden;
  box-shadow:0 2px 10px rgba(0,0,0,.45);
  -webkit-backdrop-filter:blur(6px);backdrop-filter:blur(6px);
}
#rm-update-badge .u-dt  {color:#fca5a5}
#rm-update-badge .u-ok  {color:#86efac}
#rm-update-badge .u-daily{color:#c4b5fd}
@media(orientation:landscape) and (max-height:480px){
  #rm-update-badge{height:1.45rem;line-height:1.45rem;padding:0 .55rem;font-size:clamp(.55rem,1.8vw,.74rem)}
}
@media(max-width:560px){
  #rm-update-badge .u-daily{display:none}
}
@media(max-width:430px){
  #rm-update-badge .u-ok .u-ok-en{display:none}
}

.page-wrap{max-width:1100px;margin:0 auto;padding:72px 16px 40px}

.rm-hero{
  background:linear-gradient(135deg,rgba(231,10,38,.18) 0%,rgba(59,130,246,.1) 100%);
  border:2px solid rgba(239,68,68,.4);border-radius:18px;
  padding:28px 24px;margin-bottom:24px;
}
.rm-hero__badge{
  display:inline-flex;align-items:center;gap:6px;
  padding:4px 12px;border-radius:999px;
  background:rgba(239,68,68,.2);border:1px solid rgba(239,68,68,.5);
  color:#fca5a5;font-size:13px;font-weight:700;margin-bottom:12px;
}
.rm-hero h1{
  font-size:clamp(1.3rem,4vw,2rem);font-weight:900;
  color:#fff;margin-bottom:8px;line-height:1.3;
}
.rm-hero__sub{font-size:15px;color:var(--dim);margin-bottom:16px}
.rm-hero__price{
  font-size:clamp(1.6rem,5vw,2.4rem);font-weight:900;
  color:var(--red);letter-spacing:-.02em;
}
.rm-hero__price span{font-size:.6em;color:var(--dim)}
.rm-hero__official{
  display:inline-block;margin-top:12px;padding:8px 18px;
  border-radius:10px;background:rgba(239,68,68,.25);
  border:1px solid rgba(239,68,68,.6);
  color:#fca5a5;font-weight:800;font-size:15px;text-decoration:none;
}
.rm-hero__official:hover{background:rgba(239,68,68,.4);color:#fff}

.rm-cards{
  display:grid;grid-template-columns:repeat(auto-fit,minmax(290px,1fr));
  gap:14px;margin-bottom:24px;
}
.rm-card{
  padding:18px 20px;border-radius:14px;
  background:rgba(2,6,23,.85);
}
.rm-card--intl {border:1px solid rgba(59,130,246,.4)}
.rm-card--sat  {border:1px solid rgba(56,189,248,.4)}
.rm-card--plat {border:1px solid rgba(167,139,250,.4)}
.rm-card__h3{color:#fff;font-size:16px;font-weight:800;margin-bottom:6px}
.rm-card__meta{color:var(--dim);font-size:13px;margin-bottom:8px}
.rm-card__body{font-size:14px;line-height:1.8;color:var(--text)}
.rm-card__body .dim{color:var(--dim);font-size:13px}

.rm-area{
  background:rgba(15,23,42,.5);border:1px solid var(--border);
  border-radius:12px;padding:16px 18px;margin-bottom:24px;
}
.rm-area__h{font-size:15px;font-weight:800;color:var(--dim);margin-bottom:10px}
.rm-area__btns{display:flex;flex-wrap:wrap;gap:8px}
.rm-area__btn{
  display:inline-block;padding:6px 14px;border-radius:8px;
  font-size:15px;font-weight:700;text-decoration:none;
}
.rm-area__btn--red {background:rgba(231,10,38,.2);border:1px solid rgba(231,10,38,.5);color:#fca5a5}
.rm-area__btn--blue{background:rgba(59,130,246,.15);border:1px solid rgba(59,130,246,.4);color:#93c5fd}
.rm-area__btn--red:hover {background:rgba(231,10,38,.35);color:#fff}
.rm-area__btn--blue:hover{background:rgba(59,130,246,.3);color:#fff}

.rm-coverage{
  display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));
  gap:10px;margin-bottom:24px;
}
.rm-coverage__item{
  background:rgba(15,23,42,.55);border-radius:10px;padding:12px 14px;
}
.rm-coverage__item--cyan  {border:1px solid rgba(34,211,238,.3)}
.rm-coverage__item--purple{border:1px solid rgba(139,92,246,.3)}
.rm-coverage__item--orange{border:1px solid rgba(251,146,60,.3)}
.rm-coverage__ttl{font-weight:800;font-size:15px;margin-bottom:6px}
.rm-coverage__ttl--cyan  {color:#22d3ee}
.rm-coverage__ttl--purple{color:#a78bfa}
.rm-coverage__ttl--orange{color:#fb923c}
.rm-coverage__body{font-size:15px;line-height:1.7;color:var(--text)}

.rm-links{
  background:linear-gradient(180deg,rgba(191,219,254,.1),rgba(17,8,24,.6));
  border:1px solid rgba(56,189,248,.25);border-radius:14px;
  padding:20px 20px;margin-bottom:24px;
}
.rm-links__h2{font-size:clamp(1.05rem,3.2vw,1.25rem);font-weight:800;color:#7dd3fc;margin-bottom:8px}
.rm-links__lead{font-size:15px;line-height:1.75;color:var(--text);opacity:.95;margin-bottom:12px}
.rm-links__list{list-style:none;padding:0;margin:0 0 12px;line-height:1.9;font-size:15px;color:#e0f2fe}
.rm-links__list a{color:#7dd3fc;font-weight:700;text-decoration:underline}
.rm-links__note{font-size:15px;line-height:1.65;color:#cbd5e1;opacity:.9}

.rm-search-btns{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:24px}
.rm-search-btn{
  display:inline-block;padding:8px 16px;border-radius:9px;
  font-size:15px;font-weight:800;text-decoration:none;
}
.rm-search-btn--red   {background:rgba(231,10,38,.25);border:1px solid rgba(231,10,38,.6);color:#fca5a5}
.rm-search-btn--blue  {background:rgba(59,130,246,.15);border:1px solid rgba(59,130,246,.4);color:#93c5fd}
.rm-search-btn--purple{background:rgba(139,92,246,.15);border:1px solid rgba(139,92,246,.4);color:#c4b5fd}
.rm-search-btn:hover{opacity:.8;color:#fff}

.rm-footer{
  text-align:center;padding:24px 16px;color:var(--dim);
  font-size:14px;border-top:1px solid var(--border);margin-top:16px;
}
.rm-footer a{color:var(--dim)}
.rm-footer a:hover{color:var(--text)}

.rm-cron-note{
  background:rgba(15,23,42,.5);border:1px solid var(--border);
  border-radius:10px;padding:14px 16px;margin-bottom:24px;
  font-size:13px;color:var(--dim);line-height:1.7;
}
.rm-cron-note code{
  background:#0a1628;padding:2px 7px;border-radius:4px;
  color:#a5f3fc;font-size:12px;
}
</style>

<!-- 更新バッジ -->
<div id="rm-update-badge" class="notranslate" translate="no" aria-label="最終更新日時">
  📅&thinsp;<span class="u-dt"><?= rm_h(rm_update_label($_rm_badge_ts, false)) ?></span><span class="u-ok">&thinsp;更新済み<span class="u-ok-en">（UPDATE）</span></span><span class="u-daily">（05:00AMに毎朝更新中）</span>
</div>

<div class="page-wrap">

  <div class="rm-hero">
    <div class="rm-hero__badge">📶 Rakuten最強プラン</div>
    <h1>楽天モバイル 最新情報</h1>
    <p class="rm-hero__sub">自社の楽天回線エリアと au回線（パートナー回線）エリアを合わせてデータ使い放題（パケット放題）となります。</p>
    <div>月間無制限に使っても <span class="rm-hero__price"><?= $_rm_price ?><span>（税込）</span></span></div>
    <a href="<?= rm_h($_rm_links['official']) ?>" target="_blank" rel="noopener noreferrer" class="rm-hero__official">公式サイトで確認 →</a>
    <p style="font-size:13px;color:var(--dim);margin-top:10px;">📅 <?= rm_h(rm_update_label($_rm_rk['updated_at'] ?? null)) ?></p>
  </div>

  <div class="rm-coverage">
    <div class="rm-coverage__item rm-coverage__item--cyan">
      <div class="rm-coverage__ttl rm-coverage__ttl--cyan">📡 楽天回線エリア</div>
      <p class="rm-coverage__body">人口カバー率<strong style="color:var(--yellow)">99.9%</strong>を達成。自社基地局エリア内ではデータ高速<strong style="color:var(--yellow)">無制限</strong>で利用できます。</p>
    </div>
    <div class="rm-coverage__item rm-coverage__item--purple">
      <div class="rm-coverage__ttl rm-coverage__ttl--purple">🔄 パートナー回線（au）エリア</div>
      <p class="rm-coverage__body">楽天電波が届きにくい屋内や一部エリアでは<strong style="color:#a78bfa">auローミング</strong>を利用。月間<strong style="color:#fca5a5">5GBまで</strong>高速、超過後は最大1Mbps。</p>
    </div>
    <div class="rm-coverage__item rm-coverage__item--orange">
      <div class="rm-coverage__ttl rm-coverage__ttl--orange">⚠️ 注意点</div>
      <p class="rm-coverage__body">地下・高層ビル・奥まった屋内では繋がりにくい場合あり。プラチナバンド（700MHz帯）を拡大中。</p>
    </div>
  </div>

  <div class="rm-area">
    <div class="rm-area__h">🗺️ エリア確認ツール</div>
    <div class="rm-area__btns">
      <a href="<?= rm_h($_rm_links['area']) ?>" target="_blank" rel="noopener noreferrer" class="rm-area__btn rm-area__btn--red">📍 楽天モバイル 通信・エリアマップ</a>
      <a href="https://network.mobile.rakuten.co.jp/faq/detail/00001549/" target="_blank" rel="noopener noreferrer" class="rm-area__btn rm-area__btn--blue">❓ データ高速無制限エリアとは</a>
    </div>
  </div>

  <div class="rm-search-btns">
    <a href="<?= rm_h($_rm_links['price_search']) ?>" target="_blank" rel="noopener noreferrer" class="rm-search-btn rm-search-btn--red">🔍 最新料金を Google で検索</a>
    <a href="<?= rm_h($_rm_links['campaign']) ?>" target="_blank" rel="noopener noreferrer" class="rm-search-btn rm-search-btn--blue">🔍 乗り換えキャンペーン</a>
    <a href="<?= rm_h($_rm_links['we2plus']) ?>" target="_blank" rel="noopener noreferrer" class="rm-search-btn rm-search-btn--purple">📱 1円スマホ（we2 plus）</a>
  </div>

  <div class="rm-cards">
    <div class="rm-card rm-card--intl">
      <div class="rm-card__h3">📞 楽天モバイル 国際通話プラン詳細</div>
      <p class="rm-card__meta">📅 <?= rm_h(rm_update_label($_rm_intl_crawled)) ?><?= $_rm_intl_ok ? ' ✓ クロール成功' : '' ?></p>
      <div class="rm-card__body">
        🇯🇵 日本 → 海外 プラン料金：<?= $_rm_intl_price ?> / <?= $_rm_intl_name ?><br>
        🌍 かけ放題対象国：<?= $_rm_intl_count ?> カ国<br>
        ✈️ 海外 → 日本：Rakuten Link 利用時 無料（対象国・条件あり）<br><br>
        <strong style="color:var(--yellow)">🌏 海外からも日本へ電話放題？</strong><br>
        ✅ はい、かなり本当です。主に Rakuten Link アプリ利用時（条件あり）。<br><br>
        🇯🇵 日本→日本：Rakuten Link で無料<br>
        🇯🇵 日本→海外：「<?= $_rm_intl_name ?>（<?= $_rm_intl_price ?>）」で<?= $_rm_intl_count ?>カ国かけ放題<br>
        ✈️ 海外→日本：Rakuten Link で無料（対象国から）<br><br>
        <a href="<?= rm_h($_rm_links['intl_free']) ?>" target="_blank" rel="noopener noreferrer">📎 国際通話かけ放題 公式ページ</a>
      </div>
    </div>

    <div class="rm-card rm-card--sat">
      <div class="rm-card__h3">🚀 衛星ブロードバンド通話（AST SpaceMobile 提携）</div>
      <p class="rm-card__meta">📅 <?= rm_h(rm_update_label($_rm_plat_crawled)) ?><?= $_rm_plat_ok ? ' ✓ クロール成功' : '' ?></p>
      <div class="rm-card__body">
        <?= $_rm_sat_status ?><br>
        <span style="color:#cbd5e1"><?= $_rm_sat_detail ?></span><br>
        <span class="dim">🛰️ <?= $_rm_sat_launch ?></span>
      </div>
    </div>

    <div class="rm-card rm-card--plat">
      <div class="rm-card__h3">📡 プラチナ回線（700MHz帯 プラチナバンド）</div>
      <p class="rm-card__meta">📅 <?= rm_h(rm_update_label($_rm_plat_crawled)) ?><?= $_rm_plat_ok ? ' ✓ クロール成功' : '' ?></p>
      <div class="rm-card__body">
        <?= $_rm_plat_status ?><br>
        <span style="color:#cbd5e1"><?= $_rm_plat_detail ?></span><br>
        <span class="dim">📶 カバレッジ：<?= $_rm_plat_coverage ?></span>
      </div>
    </div>
  </div>

  <div class="rm-links">
    <h2 class="rm-links__h2">📶 楽天モバイル（1円スマホ・パケット放題・電話放題）</h2>
    <p class="rm-links__lead">スマホなら楽天モバイルへの乗り換えを検討できます。eSIM 対応端末やキャンペーンの一例として、富士通製「we2 plus」など高性能 CPU 端末を<strong>1円</strong>で入手できる案内が出る場合があります（時期・在庫・契約条件は要確認）。日本全国で<strong>楽天リンク</strong>アプリが使えます。</p>
    <ul class="rm-links__list">
      <li><a href="<?= rm_h($_rm_links['we2plus']) ?>" target="_blank" rel="noopener noreferrer">1円スマホの例：富士通製 we2 plus など（要確認）</a></li>
      <li><a href="<?= rm_h($_rm_links['packet']) ?>" target="_blank" rel="noopener noreferrer">パケット放題・データ使い放題プラン</a></li>
      <li><a href="<?= rm_h($_rm_links['phone']) ?>" target="_blank" rel="noopener noreferrer">電話放題・楽天リンク経由の通話</a></li>
      <li><a href="<?= rm_h($_rm_links['link_android']) ?>" target="_blank" rel="noopener noreferrer">楽天リンク Android版</a></li>
      <li><a href="<?= rm_h($_rm_links['link_iphone']) ?>" target="_blank" rel="noopener noreferrer">楽天リンク iPhone版</a></li>
      <li><a href="<?= rm_h($_rm_links['campaign']) ?>" target="_blank" rel="noopener noreferrer">楽天モバイル 乗り換え・キャンペーン全般</a></li>
    </ul>
    <p class="rm-links__note">楽天モバイルのアンテナ・基地局が届くエリアでは、オンライン配信や TV チャットでも<strong>パケットを気にしにくいプラン</strong>を検討できます。病院などの FREE Wi-Fi が使える場合、在宅・入院中の環境づくりにも役立つことがあります（プラン内容・エリアは必ず公式で確認してください）。</p>
  </div>

  <div class="rm-cron-note notranslate" translate="no">
    <strong style="color:#67e8f9">⏱ 自動更新について</strong><br>
    このページは <code>aruaru/index.php --cron-all</code> の cron に相乗りして毎朝 05:00AM に自動クロール・キャッシュ更新されます。<br>
    単体実行: <code>php /path/to/rakuten-mobile/index.php --cron-all</code><br>
    キャッシュ先: <code>/rakuten-mobile/rakuten-mobile-cache.json</code> 他3ファイル
  </div>

  <div style="display:flex;flex-wrap:wrap;gap:10px;justify-content:center;margin-bottom:24px">
    <a href="https://audiocafe.tokyo/" style="display:inline-block;padding:8px 18px;border-radius:10px;background:rgba(15,23,42,.7);border:1px solid var(--border);color:var(--dim);font-weight:700;text-decoration:none;">← audiocafe.tokyo トップ</a>
    <a href="https://audiocafe.tokyo/aruaru/" style="display:inline-block;padding:8px 18px;border-radius:10px;background:rgba(15,23,42,.7);border:1px solid var(--border);color:var(--dim);font-weight:700;text-decoration:none;">📊 aruaru（IT技術情報）</a>
    <a href="https://audiocafe.tokyo/aruaru-lady/" style="display:inline-block;padding:8px 18px;border-radius:10px;background:rgba(15,23,42,.7);border:1px solid var(--border);color:var(--dim);font-weight:700;text-decoration:none;">💃 aruaru-lady（女性向け情報）</a>
  </div>

  <!-- aruaru.tokyo リンクバナー -->
  <div style="margin:12px 0 18px;padding:14px 18px;border-radius:12px;background:linear-gradient(135deg,rgba(255,122,69,.13),rgba(47,111,237,.08));border:1.5px solid #ff7a45;display:flex;align-items:center;flex-wrap:wrap;gap:10px;justify-content:center;">
    <span style="font-size:18px;">🎲</span>
    <span style="color:#ff7a45;font-weight:700;font-size:15px;">「あるある」まとめ & 開発リポジトリ紹介はこちら →</span>
    <a href="https://aruaru.tokyo" target="_blank" rel="noopener noreferrer" style="display:inline-block;padding:8px 18px;border-radius:10px;background:#ff7a45;color:#fff;font-weight:700;text-decoration:none;">
      📍 aruaru.tokyo
    </a>
  </div>

</div>

<footer class="rm-footer">
  <p>© audiocafe.tokyo — <a href="https://audiocafe.tokyo/">トップへ</a></p>
  <p style="margin-top:4px;font-size:12px">楽天モバイル情報は毎朝05:00AMに自動クロール更新。内容は必ず<a href="<?= rm_h($_rm_links['official']) ?>" target="_blank" rel="noopener noreferrer">公式サイト</a>でご確認ください。</p>
</footer>
    <?php
}

} // end if (!function_exists('rm_h'))

/* 他ページから関数だけ読み込む場合（aruaru / aruaru-lady）は HTML を出さない */
if (defined('RAKUTEN_MOBILE_LIBRARY') && RAKUTEN_MOBILE_LIBRARY) {
    return;
}

$_RM_EMBED = defined('RAKUTEN_MOBILE_EMBED') && RAKUTEN_MOBILE_EMBED;

/* 旧: RAKUTEN_MOBILE_EMBED フラグで index.php を直接 include した場合 */
if ($_RM_EMBED) {
    rm_load_display_data();
    if (!defined('RM_EMBED_MODE')) define('RM_EMBED_MODE', true);
    echo '<div class="aruaru-rm-embed-root">';
    rm_render_fragment();
    echo '</div>';
    return;
}

rm_load_display_data();

/* lang パラメータ（Google翻訳対応）— 埋め込み時は aruaru 側に任せる */
if (!$_RM_EMBED) {
    $_rm_lang = isset($_GET['lang']) ? preg_replace('/[^a-zA-Z\-]/', '', (string)$_GET['lang']) : '';
    if ($_rm_lang !== '' && $_rm_lang !== 'ja') {
        $cv = '/ja/' . $_rm_lang;
        $expire = time() + 31536000;
        setcookie('googtrans', $cv, $expire, '/');
        setcookie('googtrans', $cv, $expire, '/', '.' . ($_SERVER['HTTP_HOST'] ?? ''));
        $clean = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
        header('Location: ' . $clean . '#translateto=' . rawurlencode($_rm_lang));
        exit;
    } elseif ($_rm_lang === 'ja') {
        setcookie('googtrans', '', time() - 3600, '/');
        setcookie('googtrans', '', time() - 3600, '/', '.' . ($_SERVER['HTTP_HOST'] ?? ''));
        $clean = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
        header('Location: ' . $clean);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>📶 楽天モバイル情報 — audiocafe.tokyo</title>
<meta name="description" content="楽天モバイル「Rakuten最強プラン」の最新料金・国際通話・プラチナバンド・衛星ブロードバンド情報。毎朝05:00AMに自動クロール更新。">
<meta name="theme-color" content="#0b1220">
<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' rx='22' fill='%23ef4444'/%3E%3Ctext x='50' y='72' font-size='64' text-anchor='middle' fill='white' font-family='sans-serif'%3E📶%3C/text%3E%3C/svg%3E">
</head>
<body>
<?php rm_render_fragment(); ?>


<!-- Google翻訳ウィジェット（非表示・機能のみ） -->
<div id="google_translate_element" aria-hidden="true" style="display:none"></div>

<script>
(function(){
  "use strict";

  function getLang() {
    try {
      var sp = new URLSearchParams(location.search);
      var tr_tl = sp.get("_x_tr_tl"); if (tr_tl) return tr_tl;
      var lp = sp.get("lang");         if (lp)    return lp;
      var h = (location.hash || "").match(/translateto=([a-zA-Z\-]+)/);
      if (h) return decodeURIComponent(h[1]);
      var m = document.cookie.match(/(?:^|;\s*)googtrans=\/[^\/]+\/([^;]+)/);
      if (m && m[1]) return decodeURIComponent(m[1]).trim();
      return sessionStorage.getItem("ac_last_lang")
          || sessionStorage.getItem("selectedLang")
          || localStorage.getItem("selectedLang")
          || "";
    } catch(e) { return ""; }
  }

  var LANG = getLang();
  var ON_PROXY = location.hostname.indexOf('.translate.goog') !== -1;

  if (LANG && LANG !== 'ja' && !ON_PROXY) {
    // Cookie をセットしてウィジェット翻訳を起動
    try {
      sessionStorage.setItem("ac_last_lang", LANG);
      sessionStorage.setItem("selectedLang", LANG);
      localStorage.setItem("selectedLang", LANG);
      localStorage.setItem("lastSelectedLang", LANG);
      var cv = "/ja/" + LANG;
      document.cookie = "googtrans=" + cv + "; path=/; max-age=31536000";
      var hh = location.hostname, hp = hh.split('.');
      if (hp.length >= 2) {
        document.cookie = "googtrans=" + cv + "; path=/; domain=." + hp.slice(-2).join('.') + "; max-age=31536000";
      }
    } catch(e) {}

    // ?lang=XX / #translateto=XX をクリーンURLに除去
    try {
      var u = new URL(location.href);
      u.searchParams.delete('lang');
      var hash = (u.hash || '').replace(/[#&]?translateto=[^&]*/g, '').replace(/^#?&/, '#').replace(/^#$/, '');
      u.hash = hash;
      history.replaceState(null, '', u.toString().replace(/#$/, ''));
    } catch(e) {}

    // Cookie 反映のため初回のみリロード
    try {
      var cookieOk = document.cookie.indexOf('googtrans=/ja/' + LANG) !== -1;
      var reloaded = sessionStorage.getItem('__ac_rm_reloaded') === '1';
      if (!cookieOk && !reloaded) {
        sessionStorage.setItem('__ac_rm_reloaded', '1');
        location.reload();
        return;
      }
      sessionStorage.removeItem('__ac_rm_reloaded');
    } catch(e) {}

    // Google翻訳ウィジェット初期化
    window.googleTranslateElementInit = function() {
      new google.translate.TranslateElement({
        pageLanguage: 'ja',
        autoDisplay: false
      }, 'google_translate_element');
    };
    var s = document.createElement('script');
    s.src = 'https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
    s.async = true;
    document.head.appendChild(s);

  } else if (LANG && LANG !== 'ja' && ON_PROXY) {
    try {
      sessionStorage.setItem("ac_last_lang", LANG);
      sessionStorage.setItem("selectedLang", LANG);
      localStorage.setItem("selectedLang", LANG);
      localStorage.setItem("lastSelectedLang", LANG);
    } catch(e) {}
  } else if (LANG === 'ja' && !ON_PROXY) {
    // 日本語: googtrans を除去
    try {
      document.cookie = "googtrans=; path=/; max-age=0";
      var hh0 = location.hostname, pp0 = hh0.split('.');
      if (pp0.length >= 2) {
        var dom0 = '.' + pp0.slice(-2).join('.');
        document.cookie = "googtrans=; path=/; domain=" + dom0 + "; max-age=0";
      }
      sessionStorage.removeItem("ac_last_lang");
      sessionStorage.removeItem("selectedLang");
      localStorage.removeItem("selectedLang");
      sessionStorage.removeItem("__ac_rm_reloaded");
    } catch(e) {}
    try {
      var uj = new URL(location.href);
      uj.searchParams.delete('lang');
      var hp2 = (uj.hash || '').replace(/^#/, '').split('&').filter(function(p){ return p && p.indexOf('translateto=') !== 0; });
      uj.hash = hp2.length ? '#' + hp2.join('&') : '';
      history.replaceState(null, '', uj.toString().replace(/#$/, ''));
    } catch(e) {}
  }
})();
</script>
</body>
</html>
