<?php
/**
 * audiocafe.tokyo/aruaru-lady/index.php — 1ファイル統合版
 * 依存ファイルをすべてインライン化
 * 生成: 2026-05-17
 */
declare(strict_types=1);

/* ===== 言語パラメータ受け取り（Google翻訳ウィジェット連携） ===== */
$_lady_lang = isset($_GET['lang']) ? preg_replace('/[^a-zA-Z\-]/', '', (string)$_GET['lang']) : '';
if ($_lady_lang !== '') {
    // 翻訳モード時はキャッシュを無効化
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
}

/* ===== BEGIN: aruaru-caba-ranking.php ===== */

/**
 * キャバレー／キャバクラ系「高額時給目安」TOP50（東京23区内・23区外多摩／全国）
 */


if (!defined('ARUARU_CABA_CACHE')) {
    define('ARUARU_CABA_CACHE', __DIR__ . '/aruaru-caba-ranking-cache.json');
}

if (!defined('ARUARU_CABA_TTL')) {
    define('ARUARU_CABA_TTL', 86400); // 毎日更新
}

/** @return list<array{region:string,w:int,area:string,title:string,band:string,pickup:string,url:string}> */
function aruaru_caba_master_pool(): array
{
    $q = static fn(string $s): string => 'https://www.google.com/search?q=' . rawurlencode($s);
    $p = static fn(string $s): string => '検索結果・各店で確認。' . $s;

    $tokyo23 = [
        ['w' => 15200, 'area' => '東京都・港区（23区内）', 'title' => '六本木・赤坂・麻布エリア', 'band' => '約12,000〜18,000円/h級の例あり※要確認', 'url' => $q('東京23区 港区 キャバクラ 求人 高収入'), 'pickup' => $p('無料送迎を掲げる例あり')],
        ['w' => 15100, 'area' => '東京都・新宿区（23区内）', 'title' => '歌舞伎町・新宿三丁目', 'band' => '約11,000〜17,000円/h級の例あり※要確認', 'url' => $q('新宿区 歌舞伎町 キャバクラ 高時給'), 'pickup' => $p('送迎は店舗ごとに要確認')],
        ['w' => 15050, 'area' => '東京都・渋谷区（23区内）', 'title' => '渋谷・恵比寿・道玄坂', 'band' => '約10,000〜16,000円/h級の例あり※要確認', 'url' => $q('渋谷区 キャバクラ 求人 高収入'), 'pickup' => $p('')],
        ['w' => 15000, 'area' => '東京都・中央区（23区内）', 'title' => '銀座・八重洲・築地', 'band' => '約11,000〜17,000円/h級の例あり※要確認', 'url' => $q('中央区 銀座 キャバレー 求人'), 'pickup' => $p('高級店は送迎付き例あり')],
        ['w' => 14900, 'area' => '東京都・豊島区（23区内）', 'title' => '池袋・東池袋', 'band' => '約8,500〜14,000円/h級の例あり※要確認', 'url' => $q('豊島区 池袋 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 14850, 'area' => '東京都・品川区（23区内）', 'title' => '五反田・大崎・目黒', 'band' => '約8,000〜13,500円/h級の例あり※要確認', 'url' => $q('品川区 五反田 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 14800, 'area' => '東京都・台東区（23区内）', 'title' => '上野・浅草・御徒町', 'band' => '約7,500〜12,500円/h級の例あり※要確認', 'url' => $q('台東区 上野 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 14750, 'area' => '東京都・墨田区（23区内）', 'title' => '錦糸町・押上', 'band' => '約7,000〜12,000円/h級の例あり※要確認', 'url' => $q('墨田区 錦糸町 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 14700, 'area' => '東京都・江東区（23区内）', 'title' => '豊洲・門前仲町・木場', 'band' => '約7,500〜12,500円/h級の例あり※要確認', 'url' => $q('江東区 キャバクラ 求人 高時給'), 'pickup' => $p('')],
        ['w' => 14650, 'area' => '東京都・大田区（23区内）', 'title' => '蒲田・大森・品川寄り', 'band' => '約7,500〜12,000円/h級の例あり※要確認', 'url' => $q('大田区 蒲田 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 14600, 'area' => '東京都・世田谷区（23区内）', 'title' => '三軒茶屋・下北沢・二子玉川', 'band' => '約8,000〜13,000円/h級の例あり※要確認', 'url' => $q('世田谷区 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 14550, 'area' => '東京都・目黒区（23区内）', 'title' => '中目黒・自由が丘', 'band' => '約8,000〜13,000円/h級の例あり※要確認', 'url' => $q('目黒区 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 14500, 'area' => '東京都・中野区（23区内）', 'title' => '中野・高円寺', 'band' => '約7,000〜11,500円/h級の例あり※要確認', 'url' => $q('中野区 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 14450, 'area' => '東京都・杉並区（23区内）', 'title' => '荻窪・阿佐ヶ谷', 'band' => '約7,000〜11,500円/h級の例あり※要確認', 'url' => $q('杉並区 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 14400, 'area' => '東京都・北区（23区内）', 'title' => '赤羽・王子', 'band' => '約7,000〜12,000円/h級の例あり※要確認', 'url' => $q('北区 赤羽 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 14350, 'area' => '東京都・板橋区（23区内）', 'title' => '板橋・成増', 'band' => '約6,500〜11,000円/h級の例あり※要確認', 'url' => $q('板橋区 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 14300, 'area' => '東京都・練馬区（23区内）', 'title' => '練馬・石神井', 'band' => '約6,500〜11,000円/h級の例あり※要確認', 'url' => $q('練馬区 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 14250, 'area' => '東京都・足立区（23区内）', 'title' => '北千住・綾瀬', 'band' => '約6,500〜11,000円/h級の例あり※要確認', 'url' => $q('足立区 北千住 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 14200, 'area' => '東京都・葛飾区（23区内）', 'title' => '新小岩・亀有', 'band' => '約6,500〜11,000円/h級の例あり※要確認', 'url' => $q('葛飾区 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 14150, 'area' => '東京都・江戸川区（23区内）', 'title' => '小岩・西葛西', 'band' => '約6,500〜11,000円/h級の例あり※要確認', 'url' => $q('江戸川区 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 14100, 'area' => '東京都・荒川区（23区内）', 'title' => '日暮里・西日暮里', 'band' => '約6,500〜11,000円/h級の例あり※要確認', 'url' => $q('荒川区 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 14050, 'area' => '東京都・文京区（23区内）', 'title' => '本郷・後楽園', 'band' => '約7,000〜11,500円/h級の例あり※要確認', 'url' => $q('文京区 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 14000, 'area' => '東京都・千代田区（23区内）', 'title' => '有楽町・神田', 'band' => '約8,000〜13,500円/h級の例あり※要確認', 'url' => $q('千代田区 有楽町 キャバクラ 求人'), 'pickup' => $p('')],
    ];

    $tokyoTama = [
        ['w' => 13800, 'area' => '東京都・八王子市（23区外・多摩）', 'title' => '八王子駅前・南口', 'band' => '約7,000〜12,000円/h級の例あり※要確認', 'url' => $q('八王子 キャバクラ 求人 高時給'), 'pickup' => $p('多摩西部')],
        ['w' => 13750, 'area' => '東京都・立川市（23区外・多摩）', 'title' => '立川駅北口・南口', 'band' => '約7,000〜12,000円/h級の例あり※要確認', 'url' => $q('立川 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 13700, 'area' => '東京都・町田市（23区外・多摩）', 'title' => '町田駅・小田急線沿線', 'band' => '約7,000〜11,500円/h級の例あり※要確認', 'url' => $q('町田 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 13650, 'area' => '東京都・府中市（23区外・多摩）', 'title' => '府中駅・けやき並木', 'band' => '約6,500〜11,000円/h級の例あり※要確認', 'url' => $q('府中 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 13600, 'area' => '東京都・調布市（23区外・多摩）', 'title' => '調布・仙川', 'band' => '約6,500〜11,000円/h級の例あり※要確認', 'url' => $q('調布 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 13550, 'area' => '東京都・武蔵野市（23区外・多摩）', 'title' => '吉祥寺・三鷹', 'band' => '約7,000〜11,500円/h級の例あり※要確認', 'url' => $q('吉祥寺 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 13500, 'area' => '東京都・三鷹市（23区外・多摩）', 'title' => '三鷹・武蔵境', 'band' => '約6,500〜11,000円/h級の例あり※要確認', 'url' => $q('三鷹 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 13450, 'area' => '東京都・国分寺市（23区外・多摩）', 'title' => '国分寺・西国分寺', 'band' => '約6,500〜10,500円/h級の例あり※要確認', 'url' => $q('国分寺 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 13400, 'area' => '東京都・小金井市（23区外・多摩）', 'title' => '小金井・武蔵小金井', 'band' => '約6,500〜10,500円/h級の例あり※要確認', 'url' => $q('小金井 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 13350, 'area' => '東京都・日野市（23区外・多摩）', 'title' => '日野・高幡不動', 'band' => '約6,000〜10,000円/h級の例あり※要確認', 'url' => $q('日野 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 13300, 'area' => '東京都・昭島市（23区外・多摩）', 'title' => '昭島・拝島', 'band' => '約6,000〜10,000円/h級の例あり※要確認', 'url' => $q('昭島 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 13250, 'area' => '東京都・西東京市（23区外・多摩）', 'title' => '田無・ひばりが丘', 'band' => '約6,000〜10,000円/h級の例あり※要確認', 'url' => $q('西東京 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 13200, 'area' => '東京都・東村山市（23区外・多摩）', 'title' => '秋川・久米川', 'band' => '約6,000〜10,000円/h級の例あり※要確認', 'url' => $q('東村山 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 13150, 'area' => '東京都・青梅市（23区外・多摩）', 'title' => '青梅・河辺', 'band' => '約5,500〜9,500円/h級の例あり※要確認', 'url' => $q('青梅 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 13100, 'area' => '東京都・福生市（23区外・多摩）', 'title' => '福生・牛浜', 'band' => '約5,500〜9,500円/h級の例あり※要確認', 'url' => $q('福生 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 13050, 'area' => '東京都・あきる野市（23区外・多摩）', 'title' => '秋川・五日市', 'band' => '約5,500〜9,000円/h級の例あり※要確認', 'url' => $q('あきる野 ナイトワーク 求人'), 'pickup' => $p('')],
        ['w' => 13000, 'area' => '東京都・多摩市（23区外・多摩）', 'title' => '聖蹟桜ヶ丘・永山', 'band' => '約6,000〜10,000円/h級の例あり※要確認', 'url' => $q('多摩市 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 12950, 'area' => '東京都・稲城市（23区外・多摩）', 'title' => '京王よみうりランド・稲城', 'band' => '約6,000〜10,000円/h級の例あり※要確認', 'url' => $q('稲城 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 12900, 'area' => '東京都・清瀬市（23区外・多摩）', 'title' => '清瀬・竹丘', 'band' => '約5,500〜9,500円/h級の例あり※要確認', 'url' => $q('清瀬 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 12850, 'area' => '東京都・東大和市（23区外・多摩）', 'title' => '東大和・中央林間寄り', 'band' => '約5,500〜9,500円/h級の例あり※要確認', 'url' => $q('東大和 キャバクラ 求人'), 'pickup' => $p('')],
    ];

    $national = [
        ['w' => 14690, 'area' => '大阪府・大阪市', 'title' => 'ミナミ／心斎橋', 'band' => '約9,000〜15,000円/h級の例あり※要確認', 'url' => $q('大阪 ミナミ キャバクラ 求人 高時給'), 'pickup' => $p('')],
        ['w' => 14620, 'area' => '大阪府・北区', 'title' => 'キタ／梅田', 'band' => '約9,000〜14,500円/h級の例あり※要確認', 'url' => $q('大阪 梅田 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 14540, 'area' => '愛知県・名古屋市', 'title' => '栄／錦', 'band' => '約8,500〜14,000円/h級の例あり※要確認', 'url' => $q('名古屋 栄 キャバクラ 求人 高収入'), 'pickup' => $p('')],
        ['w' => 14480, 'area' => '福岡県・福岡市', 'title' => '中洲', 'band' => '約8,500〜14,000円/h級の例あり※要確認', 'url' => $q('福岡 中洲 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 14410, 'area' => '北海道・札幌市', 'title' => 'すすきの', 'band' => '約8,000〜13,500円/h級の例あり※要確認', 'url' => $q('札幌 すすきの キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 14350, 'area' => '宮城県・仙台市', 'title' => '国分町', 'band' => '約7,500〜13,000円/h級の例あり※要確認', 'url' => $q('仙台 国分町 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 14290, 'area' => '神奈川県・横浜市', 'title' => '関内・桜木町', 'band' => '約8,000〜13,500円/h級の例あり※要確認', 'url' => $q('横浜 関内 キャバクラ 求人 高時給'), 'pickup' => $p('')],
        ['w' => 14220, 'area' => '千葉県・千葉市', 'title' => '栄町', 'band' => '約7,000〜12,000円/h級の例あり※要確認', 'url' => $q('千葉 栄町 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 14160, 'area' => '埼玉県・川口市', 'title' => '西川口', 'band' => '約7,000〜12,000円/h級の例あり※要確認', 'url' => $q('西川口 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 13800, 'area' => '京都府・京都市', 'title' => '祇園・木屋町', 'band' => '約7,500〜12,500円/h級の例あり※要確認', 'url' => $q('京都 祇園 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 13740, 'area' => '兵庫県・神戸市', 'title' => '三宮・花隈', 'band' => '約7,500〜12,500円/h級の例あり※要確認', 'url' => $q('神戸 三宮 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 13680, 'area' => '広島県・広島市', 'title' => '流川', 'band' => '約7,000〜12,000円/h級の例あり※要確認', 'url' => $q('広島 流川 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 13620, 'area' => '静岡県・浜松市', 'title' => '肴町', 'band' => '約6,500〜11,000円/h級の例あり※要確認', 'url' => $q('浜松 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 13560, 'area' => '静岡県・静岡市', 'title' => '青葉通り', 'band' => '約6,500〜11,000円/h級の例あり※要確認', 'url' => $q('静岡 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 13500, 'area' => '新潟県・新潟市', 'title' => '古町', 'band' => '約6,500〜11,000円/h級の例あり※要確認', 'url' => $q('新潟 古町 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 13440, 'area' => '長野県・長野市', 'title' => '権堂', 'band' => '約6,000〜10,500円/h級の例あり※要確認', 'url' => $q('長野 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 13380, 'area' => '石川県・金沢市', 'title' => '片町', 'band' => '約6,500〜11,000円/h級の例あり※要確認', 'url' => $q('金沢 片町 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 13320, 'area' => '岐阜県・岐阜市', 'title' => '柳ヶ瀬', 'band' => '約6,000〜10,000円/h級の例あり※要確認', 'url' => $q('岐阜 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 13260, 'area' => '三重県・四日市市', 'title' => '駅前', 'band' => '約6,000〜10,000円/h級の例あり※要確認', 'url' => $q('四日市 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 13200, 'area' => '滋賀県・大津市', 'title' => '駅周辺', 'band' => '約6,000〜10,000円/h級の例あり※要確認', 'url' => $q('滋賀 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 13140, 'area' => '奈良県・奈良市', 'title' => '県内', 'band' => '約6,000〜9,500円/h級の例あり※要確認', 'url' => $q('奈良 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 13080, 'area' => '和歌山県・和歌山市', 'title' => '市内', 'band' => '約6,000〜9,500円/h級の例あり※要確認', 'url' => $q('和歌山 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 13020, 'area' => '岡山県・岡山市', 'title' => '本町・駅前', 'band' => '約6,500〜11,000円/h級の例あり※要確認', 'url' => $q('岡山 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 12960, 'area' => '山口県・下関市', 'title' => '関門', 'band' => '約6,000〜9,500円/h級の例あり※要確認', 'url' => $q('下関 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 12900, 'area' => '香川県・高松市', 'title' => 'ライオン街', 'band' => '約6,000〜10,000円/h級の例あり※要確認', 'url' => $q('高松 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 12840, 'area' => '愛媛県・松山市', 'title' => '大街道', 'band' => '約6,000〜10,000円/h級の例あり※要確認', 'url' => $q('松山 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 12780, 'area' => '高知県・高知市', 'title' => 'はりまや町', 'band' => '約6,000〜9,500円/h級の例あり※要確認', 'url' => $q('高知 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 12720, 'area' => '徳島県・徳島市', 'title' => '駅前', 'band' => '約6,000〜9,500円/h級の例あり※要確認', 'url' => $q('徳島 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 12660, 'area' => '鳥取県・鳥取市', 'title' => '県内', 'band' => '約5,500〜9,000円/h級の例あり※要確認', 'url' => $q('鳥取 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 12600, 'area' => '島根県・松江市', 'title' => '県内', 'band' => '約5,500〜9,000円/h級の例あり※要確認', 'url' => $q('島根 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 12540, 'area' => '山形県・山形市', 'title' => '県内', 'band' => '約5,500〜9,000円/h級の例あり※要確認', 'url' => $q('山形 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 12480, 'area' => '福島県・福島市', 'title' => '県内', 'band' => '約5,500〜9,000円/h級の例あり※要確認', 'url' => $q('福島 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 12420, 'area' => '茨城県・水戸市', 'title' => '駅前', 'band' => '約6,000〜9,500円/h級の例あり※要確認', 'url' => $q('水戸 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 12360, 'area' => '栃木県・宇都宮市', 'title' => 'オリオン街', 'band' => '約6,000〜10,000円/h級の例あり※要確認', 'url' => $q('宇都宮 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 12300, 'area' => '群馬県・前橋市', 'title' => '市内', 'band' => '約6,000〜9,500円/h級の例あり※要確認', 'url' => $q('前橋 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 12240, 'area' => '山梨県・甲府市', 'title' => '歓楽街', 'band' => '約6,000〜9,500円/h級の例あり※要確認', 'url' => $q('甲府 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 12180, 'area' => '富山県・富山市', 'title' => '駅前', 'band' => '約6,000〜9,500円/h級の例あり※要確認', 'url' => $q('富山 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 12120, 'area' => '福井県・福井市', 'title' => '県内', 'band' => '約5,500〜9,000円/h級の例あり※要確認', 'url' => $q('福井 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 12060, 'area' => '熊本県・熊本市', 'title' => '新市街', 'band' => '約6,500〜11,000円/h級の例あり※要確認', 'url' => $q('熊本 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 12000, 'area' => '鹿児島県・鹿児島市', 'title' => '天文館', 'band' => '約6,500〜11,000円/h級の例あり※要確認', 'url' => $q('鹿児島 天文館 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 11940, 'area' => '長崎県・長崎市', 'title' => '銅座町', 'band' => '約6,000〜10,000円/h級の例あり※要確認', 'url' => $q('長崎 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 11880, 'area' => '大分県・大分市', 'title' => '都町', 'band' => '約6,000〜10,000円/h級の例あり※要確認', 'url' => $q('大分 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 11820, 'area' => '宮崎県・宮崎市', 'title' => 'ニシタチ', 'band' => '約6,000〜9,500円/h級の例あり※要確認', 'url' => $q('宮崎 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 11760, 'area' => '沖縄県・那覇市', 'title' => '松山・国際通り', 'band' => '約7,000〜12,000円/h級の例あり※要確認', 'url' => $q('那覇 キャバクラ 求人 高収入'), 'pickup' => $p('')],
        ['w' => 11700, 'area' => '青森県・青森市', 'title' => '県内', 'band' => '約5,500〜9,000円/h級の例あり※要確認', 'url' => $q('青森 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 11640, 'area' => '岩手県・盛岡市', 'title' => '県内', 'band' => '約5,500〜9,000円/h級の例あり※要確認', 'url' => $q('盛岡 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 11580, 'area' => '秋田県・秋田市', 'title' => '県内', 'band' => '約5,500〜9,000円/h級の例あり※要確認', 'url' => $q('秋田 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 11520, 'area' => '北海道・旭川市', 'title' => '市内', 'band' => '約6,000〜10,000円/h級の例あり※要確認', 'url' => $q('旭川 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 11460, 'area' => '北海道・函館市', 'title' => '五稜郭・宝来町', 'band' => '約6,000〜10,000円/h級の例あり※要確認', 'url' => $q('函館 キャバクラ 求人'), 'pickup' => $p('')],
        ['w' => 4800, 'area' => '全国（まとめ）', 'title' => '求人ポータルで比較', 'band' => 'エリアにより幅広い※要確認', 'url' => $q('キャバクラ 求人 高時給 全国'), 'pickup' => $p('')],
    ];

    $out = [];
    foreach ($tokyo23 as $r) {
        $out[] = array_merge($r, ['region' => 'tokyo_23']);
    }
    foreach ($tokyoTama as $r) {
        $out[] = array_merge($r, ['region' => 'tokyo_tama']);
    }
    foreach ($national as $r) {
        $out[] = array_merge($r, ['region' => 'national']);
    }

    return $out;
}

/**
 * @param list<array{region:string,w:int,...}> $pool
 * @return list<array{rank:int,area:string,title:string,band:string,pickup:string,url:string}>
 */
function aruaru_caba_select_top50(array $pool, int $seed): array
{
    usort($pool, static fn(array $a, array $b): int => ($b['w'] <=> $a['w']));
    $n = count($pool);
    $off = ($seed % max(1, $n));
    $rot = [];
    for ($i = 0; $i < $n; $i++) {
        $rot[] = $pool[($off + $i) % $n];
    }
    usort($rot, static fn(array $a, array $b): int => ($b['w'] <=> $a['w']));
    $top = array_slice($rot, 0, 50);
    foreach ($top as $i => $row) {
        unset($top[$i]['w'], $top[$i]['region']);
        $top[$i]['rank'] = $i + 1;
    }

    return $top;
}

function aruaru_caba_disclaimer(): string
{
    return '日本全国　キャバレー　キャバクラは、無料の自動車送迎がある場合も御座います。時給・勤務条件・年齢等は法令・自治体規制および各店・求人媒体の表記により異なります。下表は高額時給帯の募集例が出やすいエリアの検索窓口の目安ランキングです（週次で自動更新。特定店の推奨・保証ではありません）。';
}

/**
 * @return array{updated_at:string,disclaimer:string,tokyo_23:array{rows:list},tokyo_tama:array{rows:list},national:array{rows:list},rows:list}
 */
function aruaru_caba_build_payload(): array
{
    $week = (int)gmdate('oW');
    $pool = aruaru_caba_master_pool();
    $byRegion = ['tokyo_23' => [], 'tokyo_tama' => [], 'national' => []];
    foreach ($pool as $row) {
        $byRegion[$row['region']][] = $row;
    }

    return [
        'updated_at' => gmdate('Y-m-d H:i:s') . ' UTC',
        'disclaimer' => aruaru_caba_disclaimer(),
        'tokyo_23'   => ['rows' => aruaru_caba_select_top50($byRegion['tokyo_23'], $week ^ 7919)],
        'tokyo_tama' => ['rows' => aruaru_caba_select_top50($byRegion['tokyo_tama'], $week ^ 8923)],
        'national'   => ['rows' => aruaru_caba_select_top50($byRegion['national'], $week ^ 9937)],
    ];
}

function aruaru_caba_ranking_load_cache(): ?array
{
    if (!is_readable(ARUARU_CABA_CACHE)) {
        return null;
    }
    $raw = @file_get_contents(ARUARU_CABA_CACHE);
    if ($raw === false || $raw === '') {
        return null;
    }
    $j = json_decode($raw, true);

    return is_array($j) ? $j : null;
}

/** 旧キャッシュ（単一 rows）を新形式へ */
function aruaru_caba_normalize_payload(array $cached): array
{
    if (isset($cached['tokyo_23']['rows'], $cached['tokyo_tama']['rows'], $cached['national']['rows'])) {
        return $cached;
    }

    return aruaru_caba_build_payload();
}

function aruaru_caba_ranking_refresh(bool $verbose = false): bool
{
    $payload = aruaru_caba_build_payload();
    $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        return false;
    }
    $ok = @file_put_contents(ARUARU_CABA_CACHE, $json, LOCK_EX) !== false;
    if ($verbose && function_exists('aruaru_tech_sync_log')) {
        aruaru_tech_sync_log('[caba] TOP50×3 キャッシュ更新 ' . ($ok ? 'OK' : 'NG') . ' → ' . ARUARU_CABA_CACHE, true);
    }

    return $ok;
}

/**
 * @return array{updated_at:string,disclaimer:string,tokyo_23:array{rows:list},tokyo_tama:array{rows:list},national:array{rows:list}}
 */
function aruaru_caba_ranking_get(): array
{
    $need = true;
    if (is_readable(ARUARU_CABA_CACHE)) {
        $age = time() - (int)@filemtime(ARUARU_CABA_CACHE);
        if ($age < ARUARU_CABA_TTL) {
            $need = false;
        }
    }
    if ($need) {
        aruaru_caba_ranking_refresh(false);
    }
    $cached = aruaru_caba_ranking_load_cache();
    if (is_array($cached)) {
        return aruaru_caba_normalize_payload($cached);
    }

    return aruaru_caba_build_payload();
}

/* ===== END: aruaru-caba-ranking.php ===== */


/* ===== BEGIN: aruaru-jukujo-caba-ranking.php ===== */

/**
 * 熟女キャバレー／熟女キャバクラ系「エリア別・高級帯」目安 TOP50（掲載負荷抑止のキャッシュのみ）
 */


if (!defined('ARUARU_JUKUJO_CABA_CACHE')) {
    define('ARUARU_JUKUJO_CABA_CACHE', __DIR__ . '/aruaru-jukujo-caba-ranking-cache.json');
}

if (!defined('ARUARU_JUKUJO_CABA_TTL')) {
    define('ARUARU_JUKUJO_CABA_TTL', 86400); // 毎日更新
}

/**
 * 「高級順」並び替えのためのインデックスのみ。実店舗単価とは一致しません。※各店検索・法令遵守を確認。
 *
 * @return list<array{lux:int,area:string,title:string,lux_band:string,pickup:string,url:string}>
 */
function aruaru_jukujo_caba_master_pool(): array
{
    $q = static fn(string $s): string => 'https://www.google.com/search?q=' . rawurlencode($s);
    $p = static fn(string $s): string => '検索・店舗確認。' . $s;

    return [
        ['lux' => 98, 'area' => '東京都・中央区', 'title' => '銀座・八重洲（熟女・高級帯検索）', 'lux_band' => 'ハイブランド級の店が集まるエリアの例※要確認', 'url' => $q('銀座 熟女キャバレー 高級 求人'), 'pickup' => $p('熟年可・車送迎掲載例あり／各店確認')],
        ['lux' => 97, 'area' => '東京都・港区', 'title' => '六本木・赤坂エリア（ミセス求人）', 'lux_band' => '高級客層／ラウンジ・クラブ例あり※要確認', 'url' => $q('六本木 ミセスキャバクラ 求人 熟女'), 'pickup' => $p('送迎条件は求人票によります')],
        ['lux' => 96, 'area' => '東京都・新宿区', 'title' => '歌舞伎町（ハイランク求人例）', 'lux_band' => '幅広〜高級帯まで混在の例※要確認', 'url' => $q('歌舞伎町 熟女 キャバクラ 高級 求人'), 'pickup' => $p('時間帯・店舗で差大／年齢表記確認')],
        ['lux' => 95, 'area' => '東京都・渋谷区', 'title' => '恵比寿・渋谷南エリア', 'lux_band' => 'シティ型ハイランク例あり※要確認', 'url' => $q('恵比寿 成熟女性 キャバクラ 求人'), 'pickup' => $p('')],
        ['lux' => 93, 'area' => '大阪府・大阪市', 'title' => '北区・ミナミ（ハイ級例）', 'lux_band' => '関西でも高級帯求人の例※要確認', 'url' => $q('大阪 ミナミ 熟女キャバクラ 高級'), 'pickup' => $p('')],
        ['lux' => 92, 'area' => '京都府・京都市', 'title' => '祇園木屋町エリア（格式）', 'lux_band' => '伝統街区・単価帯高め例※要確認', 'url' => $q('京都 祇園 熟女キャバレー 求人'), 'pickup' => $p('')],
        ['lux' => 91, 'area' => '福岡県・福岡市', 'title' => '中洲（ミセス求人例）', 'lux_band' => '九州でも高級帯求人例※要確認', 'url' => $q('中洲 熟女キャバクラ 高級'), 'pickup' => $p('')],
        ['lux' => 90, 'area' => '北海道・札幌市', 'title' => 'すすきのエリア（シニア層求人）', 'lux_band' => '北国エリア〜高級帯事例※要確認', 'url' => $q('札幌すすきの ミセスキャバクラ 求人'), 'pickup' => $p('')],
        ['lux' => 89, 'area' => '神奈川県・横浜市', 'title' => '中区・西区エリア（都心近）', 'lux_band' => '首都圏サテライト級の事例※要確認', 'url' => $q('横浜 熟女キャバクラ 高級'), 'pickup' => $p('')],
        ['lux' => 89, 'area' => '愛知県・名古屋市', 'title' => '栄・錦エリア', 'lux_band' => '名古屋高級事例※要確認', 'url' => $q('名古屋錦 熟女キャバレー 求人'), 'pickup' => $p('')],
        ['lux' => 88, 'area' => '兵庫県・神戸市', 'title' => '三宮・異人館近郊', 'lux_band' => '海港都市・高級帯事例※要確認', 'url' => $q('神戸三宮 熟女キャバクラ'), 'pickup' => $p('')],
        ['lux' => 87, 'area' => '宮城県・仙台市', 'title' => '国分町エリア（東北屈指）', 'lux_band' => '単価帯に幅※要確認', 'url' => $q('仙台 国分町 熟女キャバクラ 求人'), 'pickup' => $p('')],
        ['lux' => 86, 'area' => '広島県・広島市', 'title' => '流川・薬研堀エリア', 'lux_band' => '地方都市上位例※要確認', 'url' => $q('広島 流川 熟女キャバレー'), 'pickup' => $p('')],
        ['lux' => 85, 'area' => '千葉県・千葉市', 'title' => '栄町〜都心アクセスエリア', 'lux_band' => '東京隣接型・幅広求人例※要確認', 'url' => $q('千葉 栄町 ミセスキャバクラ 求人 熟女'), 'pickup' => $p('')],
        ['lux' => 84, 'area' => '埼玉県・川口市', 'title' => '首都圏北西部エリア', 'lux_band' => '東京近郊・幅広求人※', 'url' => $q('西川口 熟女キャバクラ 求人'), 'pickup' => $p('')],
        ['lux' => 83, 'area' => '東京都・豊島区', 'title' => '池袋北口〜南口', 'lux_band' => '都内大型歓楽街級※', 'url' => $q('池袋 ミセスキャバクラ 求人'), 'pickup' => $p('')],
        ['lux' => 83, 'area' => '東京都・品川区', 'title' => '五反田・大崎駅周辺', 'lux_band' => '東京副都心系※', 'url' => $q('五反田 熟女 キャバクラ'), 'pickup' => $p('')],
        ['lux' => 82, 'area' => '東京都・台東区', 'title' => '上野・浅草口エリア', 'lux_band' => '伝統〜幅広求人※', 'url' => $q('上野 ミセスキャバレー'), 'pickup' => $p('')],
        ['lux' => 82, 'area' => '東京都・墨田区', 'title' => '錦糸町エリア（南千住方面含）', 'lux_band' => '都内求人・幅広※', 'url' => $q('錦糸町 熟女キャバクラ'), 'pickup' => $p('')],
        ['lux' => 81, 'area' => '東京都・武蔵野市／三鷹', 'title' => '吉祥寺エリア（都心衛星）', 'lux_band' => '中高級求人例※', 'url' => $q('吉祥寺 熟女 キャバクラ 求人'), 'pickup' => $p('')],
        ['lux' => 80, 'area' => '静岡県・浜松市', 'title' => '肴町・駅前ナイトワーク', 'lux_band' => '東海地方の主要街※', 'url' => $q('浜松 ミセスキャバクラ'), 'pickup' => $p('')],
        ['lux' => 79, 'area' => '静岡県・静岡市', 'title' => '青葉通り〜御幸町エリア', 'lux_band' => '地方核都市※', 'url' => $q('静岡 熟女キャバレー 求人'), 'pickup' => $p('')],
        ['lux' => 79, 'area' => '新潟県・新潟市', 'title' => '古町エリア（北陸屈指）', 'lux_band' => '北陸高級求人例※', 'url' => $q('新潟古町 熟女キャバクラ'), 'pickup' => $p('')],
        ['lux' => 78, 'area' => '石川県・金沢市', 'title' => '片町・香林坊エリア', 'lux_band' => '伝統街区・来客単価高め事例※', 'url' => $q('金沢 ミセスキャバクラ 高級'), 'pickup' => $p('')],
        ['lux' => 78, 'area' => '岐阜県・岐阜市', 'title' => '柳ケ瀬・長良川周辺', 'lux_band' => '県都エリア求人※', 'url' => $q('岐阜 柳ヶ瀬 熟女 キャバクラ'), 'pickup' => $p('')],
        ['lux' => 77, 'area' => '三重県・四日市市', 'title' => '三重ナイトワーク', 'lux_band' => '工業都市求人※', 'url' => $q('四日市 熟女キャバクラ'), 'pickup' => $p('')],
        ['lux' => 77, 'area' => '滋賀県・大津市／草津', 'title' => '琵琶湖西岸エリア', 'lux_band' => '大阪・京都サテライト※', 'url' => $q('滋賀 熟女 キャバレー'), 'pickup' => $p('')],
        ['lux' => 76, 'area' => '長野県・長野市', 'title' => '権堂・駅前ナイトワーク', 'lux_band' => '観光地近接・冬期需要など※', 'url' => $q('長野市 ミセスキャバクラ'), 'pickup' => $p('')],
        ['lux' => 76, 'area' => '奈良県・奈良市', 'title' => '京都寄りエリア求人', 'lux_band' => '広域求人※', 'url' => $q('奈良 熟女キャバクラ 求人'), 'pickup' => $p('')],
        ['lux' => 75, 'area' => '和歌山県・和歌山市', 'title' => '紀州エリア求人', 'lux_band' => '地方求人※', 'url' => $q('和歌山 熟女キャバレー'), 'pickup' => $p('')],
        ['lux' => 75, 'area' => '岡山県・岡山市', 'title' => '本町駅前〜田町ナイトワーク', 'lux_band' => '中国エリア求人※', 'url' => $q('岡山 ミセスキャバクラ'), 'pickup' => $p('')],
        ['lux' => 74, 'area' => '山口県・下関市', 'title' => '関門〜小倉衛星求人', 'lux_band' => '北九州圏求人※', 'url' => $q('下関 熟女キャバクラ'), 'pickup' => $p('')],
        ['lux' => 74, 'area' => '香川県・高松市', 'title' => 'ライオン街エリア（四国門戸）', 'lux_band' => '四国主要街※', 'url' => $q('高松 ライオン街 ミセス求人'), 'pickup' => $p('')],
        ['lux' => 73, 'area' => '愛媛県・松山市', 'title' => '道後・大街道周辺', 'lux_band' => '四国中核※', 'url' => $q('松山 熟女キャバクラ 求人'), 'pickup' => $p('')],
        ['lux' => 73, 'area' => '徳島県・徳島市', 'title' => '阿波おどり〜駅前', 'lux_band' => '四国東部※', 'url' => $q('徳島 ミセスキャバクラ'), 'pickup' => $p('')],
        ['lux' => 72, 'area' => '高知県・高知市', 'title' => 'はりまや町〜帯筋', 'lux_band' => '地方求人※', 'url' => $q('高知 はりまや町 熟女キャバクラ'), 'pickup' => $p('')],
        ['lux' => 72, 'area' => '熊本県・熊本市', 'title' => '新市街・下通エリア', 'lux_band' => '九州中核求人※', 'url' => $q('熊本 ミセスキャバレー'), 'pickup' => $p('')],
        ['lux' => 71, 'area' => '鹿児島県・鹿児島市', 'title' => '天文館エリア（南九州）', 'lux_band' => '地方核都市求人※', 'url' => $q('天文館 熟女キャバクラ'), 'pickup' => $p('')],
        ['lux' => 71, 'area' => '宮崎県・宮崎市', 'title' => 'ニシタチエリア', 'lux_band' => '宮崎求人※', 'url' => $q('ニシタチ ミセス求人'), 'pickup' => $p('')],
        ['lux' => 70, 'area' => '大分県・大分市', 'title' => '都町エリア求人', 'lux_band' => '温泉街近接求人※', 'url' => $q('大分 熟女キャバクラ'), 'pickup' => $p('')],
        ['lux' => 70, 'area' => '長崎県・長崎市', 'title' => '銅座〜浜町ナイトワーク', 'lux_band' => '港町求人※', 'url' => $q('長崎 銅座町 ミセスキャバクラ'), 'pickup' => $p('')],
        ['lux' => 69, 'area' => '佐賀県・佐賀市', 'title' => '県都エリア', 'lux_band' => '地方求人※', 'url' => $q('佐賀県 熟女キャバクラ 求人'), 'pickup' => $p('')],
        ['lux' => 69, 'area' => '島根県・松江市', 'title' => '山陰・松江城下エリア', 'lux_band' => '広域求人※', 'url' => $q('松江 ミセスキャバクラ 熟女'), 'pickup' => $p('')],
        ['lux' => 68, 'area' => '鳥取県・鳥取市', 'title' => '山陰・鳥取沙丘周辺', 'lux_band' => '求人希少地域※広域検索を', 'url' => $q('鳥取県 ミセス求人 キャバクラ'), 'pickup' => $p('')],
        ['lux' => 67, 'area' => '福島県・福島市', 'title' => '東北地方ナイトワーク', 'lux_band' => '求人差大※確認', 'url' => $q('福島市 ミセス求人'), 'pickup' => $p('')],
        ['lux' => 67, 'area' => '山形県・山形市', 'title' => '県都エリア', 'lux_band' => '求人差大※確認', 'url' => $q('山形市 熟女キャバクラ'), 'pickup' => $p('')],
        ['lux' => 66, 'area' => '秋田県・秋田市', 'title' => '川反エリア求人', 'lux_band' => '地方求人※', 'url' => $q('秋田 川反 キャバクラ 熟女'), 'pickup' => $p('')],
        ['lux' => 66, 'area' => '青森県・青森市', 'title' => '県都エリア', 'lux_band' => '求人希少事例※広域検索', 'url' => $q('青森県 ミセスキャバクラ'), 'pickup' => $p('')],
        ['lux' => 65, 'area' => '岩手県・盛岡市', 'title' => '県都エリア', 'lux_band' => '求人希少事例※広域検索', 'url' => $q('盛岡市 ミセス求人'), 'pickup' => $p('')],
        ['lux' => 64, 'area' => '北海道・旭川市', 'title' => '道北エリア', 'lux_band' => '冬季需要など地域差※', 'url' => $q('旭川 熟女キャバクラ'), 'pickup' => $p('')],
        ['lux' => 64, 'area' => '北海道・函館市', 'title' => '港町〜五稜郭周辺', 'lux_band' => '北海道地方求人※', 'url' => $q('函館 ミセスキャバクラ'), 'pickup' => $p('')],
        ['lux' => 63, 'area' => '茨城県・水戸市', 'title' => '県都エリア', 'lux_band' => '広域求人※', 'url' => $q('水戸 熟女キャバレー'), 'pickup' => $p('')],
        ['lux' => 63, 'area' => '栃木県・宇都宮市', 'title' => 'オリオン街〜駅前ナイトワーク', 'lux_band' => '北関東求人※', 'url' => $q('宇都宮 熟女キャバクラ'), 'pickup' => $p('')],
        ['lux' => 62, 'area' => '群馬県・前橋市', 'title' => '県都エリア', 'lux_band' => '求人差大※広域検索', 'url' => $q('前橋 ミセス求人'), 'pickup' => $p('')],
        ['lux' => 62, 'area' => '山梨県・甲府市', 'title' => '歓楽街エリア', 'lux_band' => '東京近郊サテライト※', 'url' => $q('甲府 ミセスキャバクラ'), 'pickup' => $p('')],
        ['lux' => 61, 'area' => '富山県・富山市', 'title' => '北陸新幹線沿線求人', 'lux_band' => '北陸求人※', 'url' => $q('富山 熟女キャバクラ'), 'pickup' => $p('')],
        ['lux' => 61, 'area' => '福井県・福井市', 'title' => '県都エリア', 'lux_band' => '求人希少事例※広域検索', 'url' => $q('福井県 ミセスキャバクラ'), 'pickup' => $p('')],
        ['lux' => 60, 'area' => '沖縄県・那覇市', 'title' => '国際通り〜松山エリア（観光地）', 'lux_band' => '離島特例・求人差大※要確認', 'url' => $q('那覇 熟女キャバクラ'), 'pickup' => $p('風俗営業等の法令遵守を確認')],
        ['lux' => 55, 'area' => '全国（広域検索まとめ）', 'title' => '求人媒体での全国比較', 'lux_band' => '単価帯エリア依存※広域確認', 'url' => $q('熟女キャバクラ 高級店 求人 全国'), 'pickup' => $p('年齢表記・送迎など条件を比較')],
        ['lux' => 52, 'area' => 'TV／ライブチャット（オンライン）', 'title' => 'テレビ型・チャットワーク検索入口', 'lux_band' => '単価体系はプラットフォーム依存※確認', 'url' => $q('ライブチャット テレビ 求人 在宅'), 'pickup' => $p('契約書・身分証の取り扱い確認')],
        ['lux' => 50, 'area' => 'TV／ライブチャット（オンライン）', 'title' => 'テレビ電話〜オーディション求人例', 'lux_band' => '※媒体・プラット別に差大', 'url' => $q('TVチャット ライブチャット求人 経験不問'), 'pickup' => $p('熟年応募可否は各求人を確認')],
    ];
}

/**
 * 「高級（lux）」降順ベースで週ごとシードをXorし上位50へ（同一lux内は並び順はシードにより変動）
 *
 * @return list<array{rank:int,lux:int,area:string,title:string,lux_band:string,pickup:string,url:string}>
 */
function aruaru_jukujo_caba_select_top50(int $seed): array
{
    $pool = aruaru_jukujo_caba_master_pool();

    foreach ($pool as $i => $row) {
        $pool[$i]['k'] = $row['lux'] * 100000 + (($seed >> ($i % 12)) & 4095);
    }

    usort($pool, static fn(array $a, array $b): int => ($b['lux'] <=> $a['lux']) ?: (($b['k'] ?? 0) <=> ($a['k'] ?? 0)));

    $top = array_slice($pool, 0, 50);
    foreach ($top as $i => $_) {
        unset($top[$i]['k']);
        $top[$i]['rank'] = $i + 1;
    }

    return $top;
}

/**
 * @return array{updated_at:string,rows:list,disclaimer:string}
 */
function aruaru_jukujo_caba_build_payload(): array
{
    $week = (int)gmdate('oW');
    $seed = ($week ^ 17837);

    return [
        'updated_at'  => gmdate('Y-m-d H:i:s') . ' UTC',
        'rows'        => aruaru_jukujo_caba_select_top50($seed),
        'disclaimer'  => '熟女キャバレー／熟女キャバクラにおいて、「高級」の目安・熟年応募可否・自動車無料送迎の有無は店舗および求人ごとに異なります。本表は検索への窓口目安のみで、順位や店を推奨・保証するものではありません。風俗営業適正化法等に従ってご利用ください。',
    ];
}

function aruaru_jukujo_caba_ranking_load_cache(): ?array
{
    if (!is_readable(ARUARU_JUKUJO_CABA_CACHE)) {
        return null;
    }
    $raw = @file_get_contents(ARUARU_JUKUJO_CABA_CACHE);
    if ($raw === false || $raw === '') {
        return null;
    }
    $j = json_decode($raw, true);

    return is_array($j) ? $j : null;
}

function aruaru_jukujo_caba_ranking_refresh(bool $verbose = false): bool
{
    $payload = aruaru_jukujo_caba_build_payload();
    $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        return false;
    }
    $ok = @file_put_contents(ARUARU_JUKUJO_CABA_CACHE, $json, LOCK_EX) !== false;
    if ($verbose && function_exists('aruaru_tech_sync_log')) {
        aruaru_tech_sync_log('[jukujo-caba] TOP50 cache ' . ($ok ? 'OK' : 'NG') . ' -> ' . ARUARU_JUKUJO_CABA_CACHE, true);
    }

    return $ok;
}

/**
 * @return array{updated_at:string,rows:list,disclaimer:string}
 */
function aruaru_jukujo_caba_ranking_get(): array
{
    $need = true;
    if (is_readable(ARUARU_JUKUJO_CABA_CACHE)) {
        $age = time() - (int)@filemtime(ARUARU_JUKUJO_CABA_CACHE);
        if ($age < ARUARU_JUKUJO_CABA_TTL) {
            $need = false;
        }
    }
    if ($need) {
        aruaru_jukujo_caba_ranking_refresh(false);
    }
    $cached = aruaru_jukujo_caba_ranking_load_cache();
    if (is_array($cached) && isset($cached['rows'], $cached['disclaimer'])) {
        return $cached;
    }

    return aruaru_jukujo_caba_build_payload();
}

/* ===== END: aruaru-jukujo-caba-ranking.php ===== */

/* ===== BEGIN: aruaru-tvchat-group-ranking.php ===== */

/**
 * TVチャットレディ【グループチャット（パーティーチャット）版】（ノンアダルト・在宅／駅前体験）— 高額時給（実質時給目安）ランキング
 * 複数客同時接続のグループ/パーティチャットを中心に高効率な稼ぎ方ができる例を集約。
 * 毎日自動クロール（実体は日次シード再生成）＋キャッシュ。各行 Google検索リンク付き。
 * w = 実質時給の目安インデックス（プラットフォーム・報酬率・同時接続効率の相対値。保証額ではありません）
 */

if (!defined('ARUARU_TVCHAT_GROUP_CACHE')) {
    define('ARUARU_TVCHAT_GROUP_CACHE', __DIR__ . '/aruaru-tvchat-group-ranking-cache.json');
}

if (!defined('ARUARU_TVCHAT_GROUP_TTL')) {
    define('ARUARU_TVCHAT_GROUP_TTL', 86400); // 毎日更新
}

/** @return list<array{w:int,plat:string,title:string,band:string,pickup:string,url:string}> */
function aruaru_tvchat_group_master_pool(): array
{
    $q = static fn(string $s): string => 'https://www.google.com/search?q=' . rawurlencode($s);
    $p = static fn(string $s): string => '報酬率・最低保証・身分証/契約の取り扱いを各サイトで確認。' . $s;

    return [
        ['w' => 8200, 'plat' => '大手ライブ配信系', 'title' => 'FANZAライブチャット系 パーティ/グループ枠（ノンアダルト）', 'band' => '同時接続が多く高効率になりやすい大手例※要確認', 'url' => $q('FANZA ライブチャット パーティ グループ ノンアダルト 報酬'), 'pickup' => $p('パーティ/グループ枠の有無・規約を確認')],
        ['w' => 8000, 'plat' => '大手ライブ配信系', 'title' => 'ジュエルライブ系 グループチャット（ノンアダルト）', 'band' => '人気プラットフォームのグループ高報酬例※', 'url' => $q('ジュエルライブ グループチャット ノンアダルト 報酬'), 'pickup' => $p('')],
        ['w' => 7900, 'plat' => '大手ライブ配信系', 'title' => 'マダムライブ系 グループ（年齢層幅広）', 'band' => '熟年層も応募可・グループ高効率例※', 'url' => $q('マダムライブ グループチャット ノンアダルト 報酬'), 'pickup' => $p('年齢層・ジャンルを確認')],
        ['w' => 7800, 'plat' => '大手ライブ配信系', 'title' => 'DXLIVE系 パーティチャット（ノンアダルト）', 'band' => '海外客も含む同時接続の例※', 'url' => $q('DXLIVE パーティチャット ノンアダルト 報酬'), 'pickup' => $p('')],
        ['w' => 7700, 'plat' => '大手ライブ配信系', 'title' => 'エンジェルライブ系 グループ枠', 'band' => 'グループ単価が明瞭な例※', 'url' => $q('エンジェルライブ グループチャット ノンアダルト 報酬'), 'pickup' => $p('')],
        ['w' => 7600, 'plat' => '大手ライブ配信系', 'title' => 'ライブでゴーゴー系 パーティ枠', 'band' => '複数客同時の高効率例※', 'url' => $q('ライブでゴーゴー パーティチャット ノンアダルト 報酬'), 'pickup' => $p('')],
        ['w' => 7400, 'plat' => '駅前チャットルーム', 'title' => '東京都・新宿区（歌舞伎町・新宿駅前）グループ対応店', 'band' => '都心ルームのグループ対応例※', 'url' => $q('新宿 チャットレディ グループ パーティ ノンアダルト 求人'), 'pickup' => $p('駅前ルーム・送迎・グループ対応の有無を確認')],
        ['w' => 7300, 'plat' => '駅前チャットルーム', 'title' => '東京都・渋谷区（渋谷駅前）グループ対応店', 'band' => '都心ルームのグループ例※', 'url' => $q('渋谷 チャットレディ グループ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 7200, 'plat' => '駅前チャットルーム', 'title' => '東京都・豊島区（池袋駅前）グループ対応店', 'band' => '都内大型エリアのグループ例※', 'url' => $q('池袋 チャットレディ グループ パーティ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 7100, 'plat' => '駅前チャットルーム', 'title' => '大阪府・大阪市（梅田・難波）グループ対応店', 'band' => '関西主要駅前のグループ例※', 'url' => $q('大阪 梅田 チャットレディ グループ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 7000, 'plat' => '駅前チャットルーム', 'title' => '愛知県・名古屋市（栄・名駅）グループ対応店', 'band' => '中部主要エリアのグループ例※', 'url' => $q('名古屋 チャットレディ グループ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 6900, 'plat' => '駅前チャットルーム', 'title' => '福岡県・福岡市（天神・博多）グループ対応店', 'band' => '九州主要エリアのグループ例※', 'url' => $q('福岡 天神 チャットレディ グループ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 6850, 'plat' => '駅前チャットルーム', 'title' => '北海道・札幌市（すすきの・札幌駅）グループ対応店', 'band' => '北国主要エリアのグループ例※', 'url' => $q('札幌 チャットレディ グループ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 6800, 'plat' => '駅前チャットルーム', 'title' => '神奈川県・横浜市（横浜駅前）グループ対応店', 'band' => '首都圏サテライトのグループ例※', 'url' => $q('横浜 チャットレディ グループ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 6750, 'plat' => '駅前チャットルーム', 'title' => '宮城県・仙台市（国分町・仙台駅）グループ対応店', 'band' => '東北中核エリアのグループ例※', 'url' => $q('仙台 チャットレディ グループ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 6700, 'plat' => '駅前チャットルーム', 'title' => '広島県・広島市（流川・広島駅）グループ対応店', 'band' => '中国地方中核のグループ例※', 'url' => $q('広島 チャットレディ グループ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 6650, 'plat' => '駅前チャットルーム', 'title' => '京都府・京都市（河原町・京都駅）グループ対応店', 'band' => '関西観光中核のグループ例※', 'url' => $q('京都 チャットレディ グループ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 6600, 'plat' => '駅前チャットルーム', 'title' => '兵庫県・神戸市（三宮）グループ対応店', 'band' => '阪神圏のグループ例※', 'url' => $q('神戸 三宮 チャットレディ グループ ノンアダルト'), 'pickup' => $p('')],
        ['w' => 6550, 'plat' => '駅前チャットルーム', 'title' => '埼玉県・さいたま市（大宮駅前）グループ対応店', 'band' => '首都圏北部のグループ例※', 'url' => $q('大宮 チャットレディ グループ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 6500, 'plat' => '駅前チャットルーム', 'title' => '千葉県・千葉市（千葉駅前）グループ対応店', 'band' => '東京隣接エリアのグループ例※', 'url' => $q('千葉 チャットレディ グループ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 6450, 'plat' => '駅前チャットルーム', 'title' => '東京都・八王子市（八王子駅前・多摩西部）グループ対応店', 'band' => '多摩エリアのグループ例※', 'url' => $q('八王子 チャットレディ グループ ノンアダルト 求人'), 'pickup' => $p('多摩西部・送迎の有無を確認')],
        ['w' => 6400, 'plat' => '駅前チャットルーム', 'title' => '東京都・立川市（立川駅前・多摩）グループ対応店', 'band' => '多摩中核エリアのグループ例※', 'url' => $q('立川 チャットレディ グループ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 6350, 'plat' => '通話アプリ系', 'title' => 'ガールズチャット系 グループ通話枠', 'band' => 'グループ通話単価が明瞭な例※', 'url' => $q('ガールズチャット グループ通話 ノンアダルト 報酬'), 'pickup' => $p('グループ通話単価を確認')],
        ['w' => 6300, 'plat' => '通話アプリ系', 'title' => 'モコム系 グループ枠（女性向け）', 'band' => 'スマホ完結・グループの例※', 'url' => $q('モコム グループ ノンアダルト チャットレディ 報酬'), 'pickup' => $p('スマホ完結の可否を確認')],
        ['w' => 6250, 'plat' => '通話アプリ系', 'title' => 'クレア系 グループ枠（音声・ビデオ）', 'band' => '在宅・通勤両対応のグループ例※', 'url' => $q('クレア グループチャット ノンアダルト 報酬'), 'pickup' => $p('')],
        ['w' => 6200, 'plat' => '駅前チャットルーム', 'title' => '静岡県・浜松市／静岡市 グループ対応店', 'band' => '東海中核エリアのグループ例※', 'url' => $q('静岡 チャットレディ グループ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 6150, 'plat' => '駅前チャットルーム', 'title' => '新潟県・新潟市（古町）グループ対応店', 'band' => '北陸中核エリアのグループ例※', 'url' => $q('新潟 チャットレディ グループ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 6100, 'plat' => '駅前チャットルーム', 'title' => '石川県・金沢市（香林坊・片町）グループ対応店', 'band' => '北陸エリアのグループ例※', 'url' => $q('金沢 チャットレディ グループ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 6050, 'plat' => '駅前チャットルーム', 'title' => '岡山県・岡山市 グループ対応店', 'band' => '中国地方のグループ例※', 'url' => $q('岡山 チャットレディ グループ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 6000, 'plat' => '駅前チャットルーム', 'title' => '熊本県・熊本市（下通・新市街）グループ対応店', 'band' => '九州中核エリアのグループ例※', 'url' => $q('熊本 チャットレディ グループ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 5950, 'plat' => '駅前チャットルーム', 'title' => '鹿児島県・鹿児島市（天文館）グループ対応店', 'band' => '南九州エリアのグループ例※', 'url' => $q('鹿児島 天文館 チャットレディ グループ ノンアダルト'), 'pickup' => $p('')],
        ['w' => 5900, 'plat' => '駅前チャットルーム', 'title' => '香川県・高松市（四国門戸）グループ対応店', 'band' => '四国主要エリアのグループ例※', 'url' => $q('高松 チャットレディ グループ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 5850, 'plat' => '駅前チャットルーム', 'title' => '愛媛県・松山市（大街道）グループ対応店', 'band' => '四国中核エリアのグループ例※', 'url' => $q('松山 チャットレディ グループ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 5800, 'plat' => '駅前チャットルーム', 'title' => '長野県・長野市／山梨県・甲府市 グループ対応店', 'band' => '甲信エリアのグループ例※', 'url' => $q('長野 甲府 チャットレディ グループ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 5750, 'plat' => '駅前チャットルーム', 'title' => '栃木県・宇都宮市／群馬県・前橋市 グループ対応店', 'band' => '北関東エリアのグループ例※', 'url' => $q('宇都宮 前橋 チャットレディ グループ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 5700, 'plat' => '駅前チャットルーム', 'title' => '茨城県・水戸市 グループ対応店', 'band' => '北関東のグループ例※', 'url' => $q('水戸 チャットレディ グループ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 5650, 'plat' => '駅前チャットルーム', 'title' => '岐阜県・岐阜市／三重県・四日市市 グループ対応店', 'band' => '東海エリアのグループ例※', 'url' => $q('岐阜 四日市 チャットレディ グループ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 5600, 'plat' => '駅前チャットルーム', 'title' => '滋賀県・大津市／奈良県・奈良市 グループ対応店', 'band' => '関西サテライトのグループ例※', 'url' => $q('滋賀 奈良 チャットレディ グループ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 5550, 'plat' => '駅前チャットルーム', 'title' => '富山県・富山市／福井県・福井市 グループ対応店', 'band' => '北陸エリアのグループ例※', 'url' => $q('富山 福井 チャットレディ グループ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 5500, 'plat' => '駅前チャットルーム', 'title' => '山口県・下関市／島根県・松江市／鳥取県・鳥取市 グループ対応店', 'band' => '山陰・関門エリアのグループ例※', 'url' => $q('山口 島根 鳥取 チャットレディ グループ ノンアダルト 求人'), 'pickup' => $p('求人希少地域※広域検索を')],
        ['w' => 5450, 'plat' => '駅前チャットルーム', 'title' => '徳島県・徳島市／高知県・高知市 グループ対応店', 'band' => '四国エリアのグループ例※', 'url' => $q('徳島 高知 チャットレディ グループ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 5400, 'plat' => '駅前チャットルーム', 'title' => '大分県・大分市／宮崎県・宮崎市 グループ対応店', 'band' => '九州エリアのグループ例※', 'url' => $q('大分 宮崎 チャットレディ グループ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 5350, 'plat' => '駅前チャットルーム', 'title' => '長崎県・長崎市／佐賀県・佐賀市 グループ対応店', 'band' => '九州西部エリアのグループ例※', 'url' => $q('長崎 佐賀 チャットレディ グループ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 5300, 'plat' => '駅前チャットルーム', 'title' => '福島県・福島市／山形県・山形市 グループ対応店', 'band' => '東北エリアのグループ例※', 'url' => $q('福島 山形 チャットレディ グループ ノンアダルト 求人'), 'pickup' => $p('求人差大※確認')],
        ['w' => 5250, 'plat' => '駅前チャットルーム', 'title' => '秋田県・秋田市／青森県・青森市 グループ対応店', 'band' => '東北北部エリアのグループ例※', 'url' => $q('秋田 青森 チャットレディ グループ ノンアダルト 求人'), 'pickup' => $p('求人希少※広域検索')],
        ['w' => 5200, 'plat' => '駅前チャットルーム', 'title' => '岩手県・盛岡市 グループ対応店', 'band' => '東北エリアのグループ例※', 'url' => $q('盛岡 チャットレディ グループ ノンアダルト 求人'), 'pickup' => $p('求人希少※広域検索')],
        ['w' => 5150, 'plat' => '駅前チャットルーム', 'title' => '北海道・旭川市／函館市 グループ対応店', 'band' => '道北・道南エリアのグループ例※', 'url' => $q('旭川 函館 チャットレディ グループ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 5100, 'plat' => '駅前チャットルーム', 'title' => '沖縄県・那覇市（国際通り）グループ対応店', 'band' => '離島特例・求人差大※要確認', 'url' => $q('那覇 チャットレディ グループ ノンアダルト 求人'), 'pickup' => $p('離島特例・条件を確認')],
        ['w' => 5000, 'plat' => '在宅特化系', 'title' => '在宅グループ配信（主婦・副業向け）', 'band' => 'スキマ時間・グループ配信の例※', 'url' => $q('チャットレディ 在宅 グループ 主婦 副業 ノンアダルト'), 'pickup' => $p('扶養・確定申告の扱いを確認')],
        ['w' => 4800, 'plat' => '全国（広域検索まとめ）', 'title' => 'グループ/パーティ枠の全国比較', 'band' => '報酬体系はサイト依存※広域確認', 'url' => $q('TVチャットレディ グループ パーティ ノンアダルト 高収入 全国'), 'pickup' => $p('複数サイトの報酬率・条件を比較')],
    ];
}

function aruaru_tvchat_group_select_top50(int $seed): array
{
    $pool = aruaru_tvchat_group_master_pool();
    foreach ($pool as $i => $row) {
        $pool[$i]['k'] = $row['w'] * 1000 + (($seed >> ($i % 12)) & 4095);
    }
    usort($pool, static fn(array $a, array $b): int => ($b['w'] <=> $a['w']) ?: (($b['k'] ?? 0) <=> ($a['k'] ?? 0)));
    $top = array_slice($pool, 0, 50);
    foreach ($top as $i => $_) {
        unset($top[$i]['k']);
        $top[$i]['rank'] = $i + 1;
    }
    return $top;
}

function aruaru_tvchat_group_build_payload(): array
{
    $day  = (int)gmdate('Yz');
    $seed = ($day ^ 39067);
    return [
        'updated_at'  => gmdate('Y-m-d H:i:s') . ' UTC',
        'rows'        => aruaru_tvchat_group_select_top50($seed),
        'disclaimer'  => 'TVチャットレディ【グループチャット（パーティーチャット）版】（ノンアダルト・在宅／駅前体験）における「実質時給目安」は、運営サイトの報酬率・最低保証・同時接続効率・指定機材により大きく異なります。本表は検索への窓口目安のみで、順位・サイト・店舗を推奨または保証するものではありません。年齢制限・身分証/契約の取り扱い・各種法令を必ずご確認ください。',
    ];
}

function aruaru_tvchat_group_ranking_load_cache(): ?array
{
    if (!is_readable(ARUARU_TVCHAT_GROUP_CACHE)) {
        return null;
    }
    $raw = @file_get_contents(ARUARU_TVCHAT_GROUP_CACHE);
    if ($raw === false || $raw === '') {
        return null;
    }
    $j = json_decode($raw, true);
    return is_array($j) ? $j : null;
}

function aruaru_tvchat_group_ranking_refresh(bool $verbose = false): bool
{
    $payload = aruaru_tvchat_group_build_payload();
    $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        return false;
    }
    $ok = @file_put_contents(ARUARU_TVCHAT_GROUP_CACHE, $json, LOCK_EX) !== false;
    if ($verbose && function_exists('aruaru_tech_sync_log')) {
        aruaru_tech_sync_log('[tvchat-group] TOP50 cache ' . ($ok ? 'OK' : 'NG') . ' -> ' . ARUARU_TVCHAT_GROUP_CACHE, true);
    }
    return $ok;
}

function aruaru_tvchat_group_ranking_get(): array
{
    $need = true;
    if (is_readable(ARUARU_TVCHAT_GROUP_CACHE)) {
        $age = time() - (int)@filemtime(ARUARU_TVCHAT_GROUP_CACHE);
        if ($age < ARUARU_TVCHAT_GROUP_TTL) {
            $need = false;
        }
    }
    if ($need) {
        aruaru_tvchat_group_ranking_refresh(false);
    }
    $cached = aruaru_tvchat_group_ranking_load_cache();
    if (is_array($cached) && isset($cached['rows'], $cached['disclaimer'])) {
        return $cached;
    }
    return aruaru_tvchat_group_build_payload();
}

/* ===== END: aruaru-tvchat-group-ranking.php ===== */


/* ===== BEGIN: aruaru-tvchat-normal-ranking.php ===== */

/**
 * TVチャットレディ【通常版／1対1】（ノンアダルト・在宅／駅前体験）— 高額時給（実質時給目安）ランキング
 * 1対1のビデオ/音声/メールを中心とした稼ぎ方の例を集約。
 * 毎日自動クロール（実体は日次シード再生成）＋キャッシュ。各行 Google検索リンク付き。
 * w = 実質時給の目安インデックス（プラットフォーム・報酬率・稼働効率の相対値。保証額ではありません）
 */

if (!defined('ARUARU_TVCHAT_NORMAL_CACHE')) {
    define('ARUARU_TVCHAT_NORMAL_CACHE', __DIR__ . '/aruaru-tvchat-normal-ranking-cache.json');
}

if (!defined('ARUARU_TVCHAT_NORMAL_TTL')) {
    define('ARUARU_TVCHAT_NORMAL_TTL', 86400); // 毎日更新
}

/** @return list<array{w:int,plat:string,title:string,band:string,pickup:string,url:string}> */
function aruaru_tvchat_normal_master_pool(): array
{
    $q = static fn(string $s): string => 'https://www.google.com/search?q=' . rawurlencode($s);
    $p = static fn(string $s): string => '報酬率・最低保証・身分証/契約の取り扱いを各サイトで確認。' . $s;

    return [
        ['w' => 7800, 'plat' => '大手ライブ配信系', 'title' => 'FANZAライブチャット系 1対1（ノンアダルト枠）', 'band' => '報酬率が高い大手系の1対1例※要確認', 'url' => $q('FANZA ライブチャット ノンアダルト 1対1 報酬 高い'), 'pickup' => $p('ノンアダルト枠の有無・規約を確認')],
        ['w' => 7600, 'plat' => '大手ライブ配信系', 'title' => 'ジュエルライブ系 1対1（ノンアダルト）', 'band' => '人気プラットフォームの1対1高報酬例※', 'url' => $q('ジュエルライブ ノンアダルト 1対1 報酬'), 'pickup' => $p('')],
        ['w' => 7400, 'plat' => '大手ライブ配信系', 'title' => 'マダムライブ系 1対1（年齢層幅広）', 'band' => '熟年層も応募可の1対1高報酬例※', 'url' => $q('マダムライブ ノンアダルト 1対1 報酬'), 'pickup' => $p('年齢層・ジャンルを確認')],
        ['w' => 7200, 'plat' => '通話アプリ系', 'title' => 'ガールズチャット系（メール・通話 1対1）', 'band' => 'メール・通話単価が明瞭な例※', 'url' => $q('ガールズチャット ノンアダルト 報酬 時給'), 'pickup' => $p('メール単価・通話単価を確認')],
        ['w' => 7000, 'plat' => '通話アプリ系', 'title' => 'モコム系（女性向けトークアプリ 1対1）', 'band' => 'スマホ完結・通話報酬の例※', 'url' => $q('モコム ノンアダルト チャットレディ 報酬'), 'pickup' => $p('スマホ完結の可否を確認')],
        ['w' => 6800, 'plat' => '通話アプリ系', 'title' => 'クレア系（メール・音声・ビデオ 1対1）', 'band' => '在宅・通勤両対応の例※', 'url' => $q('クレア チャットレディ ノンアダルト 報酬 高い'), 'pickup' => $p('')],
        ['w' => 6600, 'plat' => '在宅特化系', 'title' => 'チャットレディ在宅専門サイト（1対1）', 'band' => '在宅・スマホ完結の報酬例※', 'url' => $q('チャットレディ 在宅 ノンアダルト 報酬 高い'), 'pickup' => $p('機材貸与・サポート体制を確認')],
        ['w' => 6400, 'plat' => '在宅特化系', 'title' => 'WEBカメラ配信（PC高画質 1対1）', 'band' => 'PC・高画質で単価が上がる例※', 'url' => $q('WEBカメラ チャットレディ ノンアダルト 報酬'), 'pickup' => $p('WEBカメラ付きPC推奨／指定機材を確認')],
        ['w' => 6200, 'plat' => '駅前チャットルーム', 'title' => '東京都・新宿区（歌舞伎町・新宿駅前）', 'band' => '都心チャットルーム体験の例※', 'url' => $q('新宿 チャットレディ ノンアダルト 体験 求人'), 'pickup' => $p('駅前ルーム・送迎の有無を確認')],
        ['w' => 6100, 'plat' => '駅前チャットルーム', 'title' => '東京都・渋谷区（渋谷駅前）', 'band' => '都心ルームの例※', 'url' => $q('渋谷 チャットレディ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 6000, 'plat' => '駅前チャットルーム', 'title' => '東京都・豊島区（池袋駅前）', 'band' => '都内大型エリアの例※', 'url' => $q('池袋 チャットレディ ノンアダルト 体験 求人'), 'pickup' => $p('')],
        ['w' => 5900, 'plat' => '駅前チャットルーム', 'title' => '大阪府・大阪市（梅田・難波）', 'band' => '関西主要駅前の例※', 'url' => $q('大阪 梅田 チャットレディ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 5800, 'plat' => '駅前チャットルーム', 'title' => '愛知県・名古屋市（栄・名駅）', 'band' => '中部主要エリアの例※', 'url' => $q('名古屋 チャットレディ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 5700, 'plat' => '駅前チャットルーム', 'title' => '福岡県・福岡市（天神・博多）', 'band' => '九州主要エリアの例※', 'url' => $q('福岡 天神 チャットレディ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 5600, 'plat' => '駅前チャットルーム', 'title' => '北海道・札幌市（すすきの・札幌駅）', 'band' => '北国主要エリアの例※', 'url' => $q('札幌 チャットレディ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 5500, 'plat' => '駅前チャットルーム', 'title' => '神奈川県・横浜市（横浜駅前）', 'band' => '首都圏サテライトの例※', 'url' => $q('横浜 チャットレディ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 5450, 'plat' => '駅前チャットルーム', 'title' => '宮城県・仙台市（国分町・仙台駅）', 'band' => '東北中核エリアの例※', 'url' => $q('仙台 チャットレディ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 5400, 'plat' => '駅前チャットルーム', 'title' => '広島県・広島市（流川・広島駅）', 'band' => '中国地方中核の例※', 'url' => $q('広島 チャットレディ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 5350, 'plat' => '駅前チャットルーム', 'title' => '京都府・京都市（河原町・京都駅）', 'band' => '関西観光中核の例※', 'url' => $q('京都 チャットレディ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 5300, 'plat' => '駅前チャットルーム', 'title' => '兵庫県・神戸市（三宮）', 'band' => '阪神圏の例※', 'url' => $q('神戸 三宮 チャットレディ ノンアダルト'), 'pickup' => $p('')],
        ['w' => 5250, 'plat' => '駅前チャットルーム', 'title' => '埼玉県・さいたま市（大宮駅前）', 'band' => '首都圏北部の例※', 'url' => $q('大宮 チャットレディ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 5200, 'plat' => '駅前チャットルーム', 'title' => '千葉県・千葉市（千葉駅前）', 'band' => '東京隣接エリアの例※', 'url' => $q('千葉 チャットレディ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 5150, 'plat' => '駅前チャットルーム', 'title' => '東京都・八王子市（八王子駅前・多摩西部）', 'band' => '多摩エリアの例※', 'url' => $q('八王子 チャットレディ ノンアダルト 求人'), 'pickup' => $p('多摩西部・送迎の有無を確認')],
        ['w' => 5100, 'plat' => '駅前チャットルーム', 'title' => '東京都・立川市（立川駅前・多摩）', 'band' => '多摩中核エリアの例※', 'url' => $q('立川 チャットレディ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 5050, 'plat' => '駅前チャットルーム', 'title' => '静岡県・浜松市／静岡市', 'band' => '東海中核エリアの例※', 'url' => $q('静岡 チャットレディ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 5000, 'plat' => '駅前チャットルーム', 'title' => '新潟県・新潟市（古町）', 'band' => '北陸中核エリアの例※', 'url' => $q('新潟 チャットレディ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 4950, 'plat' => '駅前チャットルーム', 'title' => '石川県・金沢市（香林坊・片町）', 'band' => '北陸エリアの例※', 'url' => $q('金沢 チャットレディ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 4900, 'plat' => '駅前チャットルーム', 'title' => '岡山県・岡山市', 'band' => '中国地方の例※', 'url' => $q('岡山 チャットレディ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 4850, 'plat' => '駅前チャットルーム', 'title' => '熊本県・熊本市（下通・新市街）', 'band' => '九州中核エリアの例※', 'url' => $q('熊本 チャットレディ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 4800, 'plat' => '駅前チャットルーム', 'title' => '鹿児島県・鹿児島市（天文館）', 'band' => '南九州エリアの例※', 'url' => $q('鹿児島 天文館 チャットレディ ノンアダルト'), 'pickup' => $p('')],
        ['w' => 4750, 'plat' => '駅前チャットルーム', 'title' => '香川県・高松市（四国門戸）', 'band' => '四国主要エリアの例※', 'url' => $q('高松 チャットレディ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 4700, 'plat' => '駅前チャットルーム', 'title' => '愛媛県・松山市（大街道）', 'band' => '四国中核エリアの例※', 'url' => $q('松山 チャットレディ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 4650, 'plat' => '駅前チャットルーム', 'title' => '長野県・長野市／山梨県・甲府市', 'band' => '甲信エリアの例※', 'url' => $q('長野 甲府 チャットレディ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 4600, 'plat' => '駅前チャットルーム', 'title' => '栃木県・宇都宮市／群馬県・前橋市', 'band' => '北関東エリアの例※', 'url' => $q('宇都宮 前橋 チャットレディ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 4550, 'plat' => '駅前チャットルーム', 'title' => '茨城県・水戸市', 'band' => '北関東の例※', 'url' => $q('水戸 チャットレディ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 4500, 'plat' => '駅前チャットルーム', 'title' => '岐阜県・岐阜市／三重県・四日市市', 'band' => '東海エリアの例※', 'url' => $q('岐阜 四日市 チャットレディ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 4450, 'plat' => '駅前チャットルーム', 'title' => '滋賀県・大津市／奈良県・奈良市', 'band' => '関西サテライトの例※', 'url' => $q('滋賀 奈良 チャットレディ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 4400, 'plat' => '駅前チャットルーム', 'title' => '富山県・富山市／福井県・福井市', 'band' => '北陸エリアの例※', 'url' => $q('富山 福井 チャットレディ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 4350, 'plat' => '駅前チャットルーム', 'title' => '山口県・下関市／島根県・松江市／鳥取県・鳥取市', 'band' => '山陰・関門エリアの例※', 'url' => $q('山口 島根 鳥取 チャットレディ ノンアダルト 求人'), 'pickup' => $p('求人希少地域※広域検索を')],
        ['w' => 4300, 'plat' => '駅前チャットルーム', 'title' => '徳島県・徳島市／高知県・高知市', 'band' => '四国エリアの例※', 'url' => $q('徳島 高知 チャットレディ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 4250, 'plat' => '駅前チャットルーム', 'title' => '大分県・大分市／宮崎県・宮崎市', 'band' => '九州エリアの例※', 'url' => $q('大分 宮崎 チャットレディ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 4200, 'plat' => '駅前チャットルーム', 'title' => '長崎県・長崎市／佐賀県・佐賀市', 'band' => '九州西部エリアの例※', 'url' => $q('長崎 佐賀 チャットレディ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 4150, 'plat' => '駅前チャットルーム', 'title' => '福島県・福島市／山形県・山形市', 'band' => '東北エリアの例※', 'url' => $q('福島 山形 チャットレディ ノンアダルト 求人'), 'pickup' => $p('求人差大※確認')],
        ['w' => 4100, 'plat' => '駅前チャットルーム', 'title' => '秋田県・秋田市／青森県・青森市', 'band' => '東北北部エリアの例※', 'url' => $q('秋田 青森 チャットレディ ノンアダルト 求人'), 'pickup' => $p('求人希少※広域検索')],
        ['w' => 4050, 'plat' => '駅前チャットルーム', 'title' => '岩手県・盛岡市', 'band' => '東北エリアの例※', 'url' => $q('盛岡 チャットレディ ノンアダルト 求人'), 'pickup' => $p('求人希少※広域検索')],
        ['w' => 4000, 'plat' => '駅前チャットルーム', 'title' => '北海道・旭川市／函館市', 'band' => '道北・道南エリアの例※', 'url' => $q('旭川 函館 チャットレディ ノンアダルト 求人'), 'pickup' => $p('')],
        ['w' => 3950, 'plat' => '駅前チャットルーム', 'title' => '沖縄県・那覇市（国際通り）', 'band' => '離島特例・求人差大※要確認', 'url' => $q('那覇 チャットレディ ノンアダルト 求人'), 'pickup' => $p('離島特例・条件を確認')],
        ['w' => 3900, 'plat' => '在宅特化系', 'title' => '主婦・副業向け在宅チャット（1対1）', 'band' => 'スキマ時間・短時間の例※', 'url' => $q('チャットレディ 在宅 主婦 副業 ノンアダルト'), 'pickup' => $p('扶養・確定申告の扱いを確認')],
        ['w' => 3850, 'plat' => '在宅特化系', 'title' => '学生・未経験歓迎の在宅チャット（1対1）', 'band' => '未経験OK・サポート充実の例※', 'url' => $q('チャットレディ 未経験 在宅 ノンアダルト 求人'), 'pickup' => $p('年齢確認・身分証の取り扱いを確認')],
        ['w' => 3500, 'plat' => '全国（広域検索まとめ）', 'title' => '通常（1対1）枠の全国比較', 'band' => '報酬体系はサイト依存※広域確認', 'url' => $q('TVチャットレディ ノンアダルト 1対1 求人 高収入 全国'), 'pickup' => $p('複数サイトの報酬率・条件を比較')],
    ];
}

function aruaru_tvchat_normal_select_top50(int $seed): array
{
    $pool = aruaru_tvchat_normal_master_pool();
    foreach ($pool as $i => $row) {
        $pool[$i]['k'] = $row['w'] * 1000 + (($seed >> ($i % 12)) & 4095);
    }
    usort($pool, static fn(array $a, array $b): int => ($b['w'] <=> $a['w']) ?: (($b['k'] ?? 0) <=> ($a['k'] ?? 0)));
    $top = array_slice($pool, 0, 50);
    foreach ($top as $i => $_) {
        unset($top[$i]['k']);
        $top[$i]['rank'] = $i + 1;
    }
    return $top;
}

function aruaru_tvchat_normal_build_payload(): array
{
    $day  = (int)gmdate('Yz');
    $seed = ($day ^ 28541);
    return [
        'updated_at'  => gmdate('Y-m-d H:i:s') . ' UTC',
        'rows'        => aruaru_tvchat_normal_select_top50($seed),
        'disclaimer'  => 'TVチャットレディ【通常版／1対1】（ノンアダルト・在宅／駅前体験）における「実質時給目安」は、運営サイトの報酬率・最低保証・稼働効率・指定機材により大きく異なります。本表は検索への窓口目安のみで、順位・サイト・店舗を推奨または保証するものではありません。年齢制限・身分証/契約の取り扱い・各種法令を必ずご確認ください。',
    ];
}

function aruaru_tvchat_normal_ranking_load_cache(): ?array
{
    if (!is_readable(ARUARU_TVCHAT_NORMAL_CACHE)) {
        return null;
    }
    $raw = @file_get_contents(ARUARU_TVCHAT_NORMAL_CACHE);
    if ($raw === false || $raw === '') {
        return null;
    }
    $j = json_decode($raw, true);
    return is_array($j) ? $j : null;
}

function aruaru_tvchat_normal_ranking_refresh(bool $verbose = false): bool
{
    $payload = aruaru_tvchat_normal_build_payload();
    $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        return false;
    }
    $ok = @file_put_contents(ARUARU_TVCHAT_NORMAL_CACHE, $json, LOCK_EX) !== false;
    if ($verbose && function_exists('aruaru_tech_sync_log')) {
        aruaru_tech_sync_log('[tvchat-normal] TOP50 cache ' . ($ok ? 'OK' : 'NG') . ' -> ' . ARUARU_TVCHAT_NORMAL_CACHE, true);
    }
    return $ok;
}

function aruaru_tvchat_normal_ranking_get(): array
{
    $need = true;
    if (is_readable(ARUARU_TVCHAT_NORMAL_CACHE)) {
        $age = time() - (int)@filemtime(ARUARU_TVCHAT_NORMAL_CACHE);
        if ($age < ARUARU_TVCHAT_NORMAL_TTL) {
            $need = false;
        }
    }
    if ($need) {
        aruaru_tvchat_normal_ranking_refresh(false);
    }
    $cached = aruaru_tvchat_normal_ranking_load_cache();
    if (is_array($cached) && isset($cached['rows'], $cached['disclaimer'])) {
        return $cached;
    }
    return aruaru_tvchat_normal_build_payload();
}

/* ===== END: aruaru-tvchat-normal-ranking.php ===== */



/* ===== BEGIN: aruaru-taiken-corners.php ===== */

/**
 * 体験入店・体験可能店 — エリア別コーナー（キャバ／熟女キャバ／TVチャットレディ）
 * 日本全国：主要都市＋23区外・多摩など区外サテライトを名称付きで分割
 */


/** @return list<array{id:string,title:string,intro:string,areas:list<array{label:string,place:string,note:string}>>} */
function aruaru_taiken_zone_definitions(): array
{
    return [
        [
            'id'    => 'tokyo23',
            'title' => '東京都・23区内',
            'intro' => '港区・新宿・渋谷・銀座ほか、区ごとの体験入店・体験勤務の募集例を検索する窓口です。',
            'areas' => [
                ['label' => '港区', 'place' => '六本木・赤坂・麻布', 'note' => '23区内'],
                ['label' => '新宿区', 'place' => '歌舞伎町・新宿三丁目', 'note' => '23区内'],
                ['label' => '渋谷区', 'place' => '渋谷・恵比寿・道玄坂', 'note' => '23区内'],
                ['label' => '中央区', 'place' => '銀座・八重洲', 'note' => '23区内'],
                ['label' => '豊島区', 'place' => '池袋・東池袋', 'note' => '23区内'],
                ['label' => '品川区', 'place' => '五反田・大崎', 'note' => '23区内'],
                ['label' => '台東区', 'place' => '上野・浅草', 'note' => '23区内'],
                ['label' => '墨田区', 'place' => '錦糸町', 'note' => '23区内'],
                ['label' => '江東区', 'place' => '豊洲・門前仲町', 'note' => '23区内'],
                ['label' => '大田区', 'place' => '蒲田・大森', 'note' => '23区内'],
                ['label' => '世田谷区', 'place' => '三軒茶屋・下北沢', 'note' => '23区内'],
                ['label' => '目黒区', 'place' => '中目黒・自由が丘', 'note' => '23区内'],
                ['label' => '中野区', 'place' => '中野・高円寺', 'note' => '23区内'],
                ['label' => '杉並区', 'place' => '荻窪・阿佐ヶ谷', 'note' => '23区内'],
                ['label' => '北区', 'place' => '赤羽・王子', 'note' => '23区内'],
                ['label' => '板橋区', 'place' => '板橋・成増', 'note' => '23区内'],
                ['label' => '練馬区', 'place' => '練馬・石神井', 'note' => '23区内'],
                ['label' => '足立区', 'place' => '北千住・綾瀬', 'note' => '23区内'],
                ['label' => '葛飾区', 'place' => '新小岩・亀有', 'note' => '23区内'],
                ['label' => '江戸川区', 'place' => '小岩・西葛西', 'note' => '23区内'],
                ['label' => '荒川区', 'place' => '日暮里', 'note' => '23区内'],
                ['label' => '文京区', 'place' => '本郷・後楽園', 'note' => '23区内'],
                ['label' => '千代田区', 'place' => '有楽町・神田', 'note' => '23区内'],
            ],
        ],
        [
            'id'    => 'tokyo_tama',
            'title' => '東京都・23区外（多摩地域ほか）',
            'intro' => '立川・八王子・町田・吉祥寺など、都心外の多摩・西東京エリアです。',
            'areas' => [
                ['label' => '立川市', 'place' => '立川駅北口・南口', 'note' => '多摩'],
                ['label' => '八王子市', 'place' => '八王子駅前', 'note' => '多摩西部'],
                ['label' => '町田市', 'place' => '町田駅・小田急沿線', 'note' => '多摩南'],
                ['label' => '府中市', 'place' => '府中駅・けやき並木', 'note' => '多摩'],
                ['label' => '調布市', 'place' => '調布・仙川', 'note' => '多摩'],
                ['label' => '武蔵野市', 'place' => '吉祥寺', 'note' => '多摩'],
                ['label' => '三鷹市', 'place' => '三鷹・武蔵境', 'note' => '多摩'],
                ['label' => '国分寺市', 'place' => '国分寺・西国分寺', 'note' => '多摩'],
                ['label' => '小金井市', 'place' => '武蔵小金井', 'note' => '多摩'],
                ['label' => '日野市', 'place' => '高幡不動', 'note' => '多摩'],
                ['label' => '昭島市', 'place' => '昭島・拝島', 'note' => '多摩'],
                ['label' => '西東京市', 'place' => '田無・ひばりが丘', 'note' => '多摩'],
                ['label' => '東村山市', 'place' => '秋川・久米川', 'note' => '多摩'],
                ['label' => '青梅市', 'place' => '青梅・河辺', 'note' => '多摩西'],
                ['label' => '福生市', 'place' => '福生・牛浜', 'note' => '多摩'],
                ['label' => '多摩市', 'place' => '聖蹟桜ヶ丘', 'note' => '多摩'],
                ['label' => '稲城市', 'place' => '京王稲城', 'note' => '多摩南'],
                ['label' => '清瀬市', 'place' => '清瀬・竹丘', 'note' => '多摩北'],
            ],
        ],
        [
            'id'    => 'kanto_capital',
            'title' => '首都圏（神奈川・千葉・埼玉）主要都市',
            'intro' => '横浜・川崎・千葉・さいたま・川口など、東京23区外の首都圏中核都市です。',
            'areas' => [
                ['label' => '神奈川県・横浜市', 'place' => '関内・桜木町', 'note' => '県都'],
                ['label' => '神奈川県・川崎市', 'place' => '川崎駅・武蔵小杉', 'note' => '首都圏'],
                ['label' => '神奈川県・相模原市', 'place' => '橋本・相模大野', 'note' => '県内区外'],
                ['label' => '千葉県・千葉市', 'place' => '栄町', 'note' => '県都'],
                ['label' => '千葉県・船橋市', 'place' => '船橋・西船橋', 'note' => '県内'],
                ['label' => '千葉県・柏市', 'place' => '柏・我孫子', 'note' => '県内北東'],
                ['label' => '埼玉県・さいたま市', 'place' => '大宮・浦和', 'note' => '県都'],
                ['label' => '埼玉県・川口市', 'place' => '西川口', 'note' => '首都圏北'],
                ['label' => '埼玉県・川越市', 'place' => '川越・小江戸', 'note' => '県内区外'],
            ],
        ],
        [
            'id'    => 'hokkaido',
            'title' => '北海道',
            'intro' => '札幌すすきのを中心に、旭川・函館など道内主要・区外エリアです。',
            'areas' => [
                ['label' => '札幌市', 'place' => 'すすきの', 'note' => '道央'],
                ['label' => '旭川市', 'place' => '旭川駅前', 'note' => '道北'],
                ['label' => '函館市', 'place' => '五稜郭・宝来町', 'note' => '道南'],
                ['label' => '帯広市', 'place' => '帯広・十勝', 'note' => '道東'],
                ['label' => '釧路市', 'place' => '釧路・幣舞橋', 'note' => '道東'],
            ],
        ],
        [
            'id'    => 'tohoku',
            'title' => '東北',
            'intro' => '仙台・盛岡・青森など東北各県の主要歓楽街・駅前エリアです。',
            'areas' => [
                ['label' => '宮城県・仙台市', 'place' => '国分町', 'note' => '東北最大'],
                ['label' => '福島県・郡山市', 'place' => '郡山駅前', 'note' => '県内中核'],
                ['label' => '福島県・福島市', 'place' => '福島市内', 'note' => '県都'],
                ['label' => '山形県・山形市', 'place' => '山形駅前', 'note' => '県都'],
                ['label' => '秋田県・秋田市', 'place' => '川反', 'note' => '県都'],
                ['label' => '岩手県・盛岡市', 'place' => '盛岡市内', 'note' => '県都'],
                ['label' => '青森県・青森市', 'place' => '青森市内', 'note' => '県都'],
            ],
        ],
        [
            'id'    => 'kanto_pref',
            'title' => '関東（茨城・栃木・群馬・山梨）',
            'intro' => '北関東・山梨の県都・歓楽街エリアです。',
            'areas' => [
                ['label' => '茨城県・水戸市', 'place' => '水戸駅前', 'note' => '県都'],
                ['label' => '栃木県・宇都宮市', 'place' => 'オリオン街', 'note' => '県都'],
                ['label' => '群馬県・前橋市', 'place' => '前橋市内', 'note' => '県都'],
                ['label' => '群馬県・高崎市', 'place' => '高崎駅前', 'note' => '県内'],
                ['label' => '山梨県・甲府市', 'place' => '甲府歓楽街', 'note' => '県都'],
            ],
        ],
        [
            'id'    => 'chubu_hokuriku',
            'title' => '甲信越・北陸（長野・新潟ほか）',
            'intro' => '長野・新潟・金沢・富山など、中部山地と日本海側の主要エリアです。',
            'areas' => [
                ['label' => '長野県・長野市', 'place' => '権堂', 'note' => '県都'],
                ['label' => '長野県・松本市', 'place' => '松本・深志', 'note' => '県内区外'],
                ['label' => '長野県・上田市', 'place' => '上田・軽井沢寄り', 'note' => '県内'],
                ['label' => '新潟県・新潟市', 'place' => '古町', 'note' => '県都'],
                ['label' => '石川県・金沢市', 'place' => '片町', 'note' => '北陸'],
                ['label' => '富山県・富山市', 'place' => '富山駅前', 'note' => '北陸'],
                ['label' => '福井県・福井市', 'place' => '福井市内', 'note' => '北陸'],
            ],
        ],
        [
            'id'    => 'tokai',
            'title' => '東海（愛知・静岡・岐阜・三重）',
            'intro' => '名古屋・浜松・静岡・岐阜・四日市など東海地方の主要・区外都市です。',
            'areas' => [
                ['label' => '愛知県・名古屋市', 'place' => '栄・錦', 'note' => '県都'],
                ['label' => '愛知県・豊田市', 'place' => '豊田・岡崎寄り', 'note' => '名古屋市外'],
                ['label' => '静岡県・静岡市', 'place' => '青葉通り', 'note' => '県都'],
                ['label' => '静岡県・浜松市', 'place' => '肴町', 'note' => '県内'],
                ['label' => '岐阜県・岐阜市', 'place' => '柳ヶ瀬', 'note' => '県都'],
                ['label' => '三重県・四日市市', 'place' => '四日市駅前', 'note' => '県内最大'],
                ['label' => '三重県・津市', 'place' => '津市内', 'note' => '県都'],
            ],
        ],
        [
            'id'    => 'kansai',
            'title' => '関西（大阪・京都・神戸・奈良ほか）',
            'intro' => 'ミナミ・梅田・祇園・三宮など関西の中核と、堺・枚方など府県内区外です。',
            'areas' => [
                ['label' => '大阪府・大阪市', 'place' => 'ミナミ・心斎橋', 'note' => '府都'],
                ['label' => '大阪府・大阪市', 'place' => 'キタ・梅田', 'note' => '府都'],
                ['label' => '大阪府・堺市', 'place' => '堺・堺東', 'note' => '大阪市外'],
                ['label' => '大阪府・東大阪市', 'place' => '東大阪・八尾寄り', 'note' => '府内区外'],
                ['label' => '京都府・京都市', 'place' => '祇園・木屋町', 'note' => '府都'],
                ['label' => '京都府・宇治市', 'place' => '宇治・南部', 'note' => '府内区外'],
                ['label' => '兵庫県・神戸市', 'place' => '三宮・花隈', 'note' => '県都'],
                ['label' => '兵庫県・姫路市', 'place' => '姫路駅前', 'note' => '県内区外'],
                ['label' => '奈良県・奈良市', 'place' => '奈良市内', 'note' => '県都'],
                ['label' => '滋賀県・大津市', 'place' => '大津・草津', 'note' => '県都'],
                ['label' => '和歌山県・和歌山市', 'place' => '和歌山市内', 'note' => '県都'],
            ],
        ],
        [
            'id'    => 'chugoku',
            'title' => '中国地方',
            'intro' => '広島・岡山・山口など中国地方の主要都市です。',
            'areas' => [
                ['label' => '広島県・広島市', 'place' => '流川・薬研堀', 'note' => '県都'],
                ['label' => '岡山県・岡山市', 'place' => '本町・駅前', 'note' => '県都'],
                ['label' => '山口県・下関市', 'place' => '関門・小倉寄り', 'note' => '県内'],
                ['label' => '鳥取県・鳥取市', 'place' => '鳥取市内', 'note' => '県都'],
                ['label' => '島根県・松江市', 'place' => '松江城下', 'note' => '県都'],
            ],
        ],
        [
            'id'    => 'shikoku',
            'title' => '四国',
            'intro' => '高松・松山・高知・徳島など四国4県の主要エリアです。',
            'areas' => [
                ['label' => '香川県・高松市', 'place' => 'ライオン街', 'note' => '四国入口'],
                ['label' => '愛媛県・松山市', 'place' => '大街道・道後', 'note' => '四国中核'],
                ['label' => '高知県・高知市', 'place' => 'はりまや町', 'note' => '県都'],
                ['label' => '徳島県・徳島市', 'place' => '徳島駅前', 'note' => '県都'],
            ],
        ],
        [
            'id'    => 'kyushu',
            'title' => '九州（福岡・熊本・鹿児島ほか）',
            'intro' => '中洲・熊本・天文館など九州各県の主要歓楽街・駅前です。',
            'areas' => [
                ['label' => '福岡県・福岡市', 'place' => '中洲', 'note' => '九州最大'],
                ['label' => '福岡県・北九州市', 'place' => '小倉・門司', 'note' => '県内区外'],
                ['label' => '佐賀県・佐賀市', 'place' => '佐賀市内', 'note' => '県都'],
                ['label' => '長崎県・長崎市', 'place' => '銅座町', 'note' => '県都'],
                ['label' => '熊本県・熊本市', 'place' => '新市街', 'note' => '県都'],
                ['label' => '大分県・大分市', 'place' => '都町', 'note' => '県都'],
                ['label' => '宮崎県・宮崎市', 'place' => 'ニシタチ', 'note' => '県都'],
                ['label' => '鹿児島県・鹿児島市', 'place' => '天文館', 'note' => '南九州'],
                ['label' => '鹿児島県・霧島市', 'place' => '霧島・姶良寄り', 'note' => '県内区外'],
            ],
        ],
        [
            'id'    => 'okinawa',
            'title' => '沖縄',
            'intro' => '那覇を中心に、沖縄本島の主要エリアです（離島・条例は各店で要確認）。',
            'areas' => [
                ['label' => '那覇市', 'place' => '国際通り・松山', 'note' => '県都'],
                ['label' => '沖縄市', 'place' => 'コザ・泡瀬', 'note' => '本島中部'],
                ['label' => 'うるま市', 'place' => 'うるま・中部', 'note' => '本島東'],
            ],
        ],
    ];
}

/**
 * @return array{caba:string,jukujo:string,tv:string}
 */
function aruaru_taiken_search_queries(string $label, string $place): array
{
    $loc = trim($label . ' ' . $place);

    return [
        'caba'   => $loc . ' キャバクラ キャバレー 体験入店 体験勤務',
        'jukujo' => $loc . ' 熟女キャバクラ 熟女キャバレー ミセス 体験入店',
        'tv'     => $loc . ' TVチャットレディ ノンアダルト 体験 駅前 体験入店',
    ];
}

/**
 * @param 'caba'|'jukujo'|'tv' $kind
 * @return list<array{id:string,title:string,intro:string,items:list<array{label:string,place:string,note:string,url:string}>>}
 */
function aruaru_taiken_build_category(string $kind): array
{
    $q = static fn(string $s): string => 'https://www.google.com/search?q=' . rawurlencode($s);
    $zones = [];

    foreach (aruaru_taiken_zone_definitions() as $zone) {
        $items = [];
        foreach ($zone['areas'] as $a) {
            $queries = aruaru_taiken_search_queries($a['label'], $a['place']);
            $items[] = [
                'label' => $a['label'],
                'place' => $a['place'],
                'note'  => $a['note'],
                'url'   => $q($queries[$kind]),
            ];
        }
        $zones[] = [
            'id'    => $zone['id'],
            'title' => $zone['title'],
            'intro' => $zone['intro'],
            'items' => $items,
        ];
    }

    return $zones;
}

/**
 * @return array{
 *   disclaimer:string,
 *   caba:array{title:string,slug:string,zones:list},
 *   jukujo:array{title:string,slug:string,zones:list},
 *   tv:array{title:string,slug:string,zones:list}
 * }
 */
function aruaru_taiken_corners_data(): array
{
    return [
        'disclaimer' => '体験入店・体験勤務の可否・年齢・契約・風俗営業適正化法等は店舗・自治体・求人媒体ごとに異なります。下記は各エリア名での Google 検索窓口のみで、特定店の推奨・保証ではありません。応募前に公式HP・求人票で必ずご確認ください。',
        'caba' => [
            'title' => 'キャバレー・キャバクラ　体験可能店（エリア別コーナー）',
            'slug'  => 'aruaru-taiken-caba',
            'zones' => aruaru_taiken_build_category('caba'),
        ],
        'jukujo' => [
            'title' => '熟女キャバレー・熟女キャバクラ　体験可能店（エリア別コーナー）',
            'slug'  => 'aruaru-taiken-jukujo',
            'zones' => aruaru_taiken_build_category('jukujo'),
        ],
        'tv' => [
            'title' => 'TVチャットレディ　体験可能店（エリア別コーナー）',
            'slug'  => 'aruaru-taiken-tv',
            'zones' => aruaru_taiken_build_category('tv'),
        ],
    ];
}

/**
 * @param array{title:string,slug:string,zones:list} $cat
 * @param array{border:string,bg:string,accent:string,heading:string} $theme
 */
function aruaru_taiken_render_category(array $cat, array $theme): void
{
    $slug = (string)($cat['slug'] ?? '');
    $title = (string)($cat['title'] ?? '');
    echo '<section style="margin-top:36px;padding:20px;border-radius:14px;background:' . $theme['bg'] . ';border:1px solid ' . $theme['border'] . ';" id="' . htmlspecialchars($slug, ENT_QUOTES, 'UTF-8') . '">';
    echo '<h3 style="color:' . $theme['heading'] . ';margin:0 0 12px;font-size:20px;line-height:1.4;">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h3>';
    echo '<p style="opacity:.88;font-size:15px;line-height:1.7;color:#e2e8f0;margin:0 0 20px;">主要都市と区外（例：多摩・堺・豊田・松本など）ごとにコーナーを分けています。リンク先は検索結果です。</p>';

    foreach ($cat['zones'] as $zone) {
        $zid = (string)($zone['id'] ?? '');
        $ztitle = (string)($zone['title'] ?? '');
        $zintro = (string)($zone['intro'] ?? '');
        $anchor = $slug . '-' . $zid;
        echo '<article style="margin-top:22px;padding:16px;border-radius:12px;background:rgba(0,0,0,.18);border:1px solid ' . $theme['border'] . ';" id="' . htmlspecialchars($anchor, ENT_QUOTES, 'UTF-8') . '">';
        echo '<h4 style="color:' . $theme['accent'] . ';margin:0 0 8px;font-size:17px;">📍 ' . htmlspecialchars($ztitle, ENT_QUOTES, 'UTF-8') . '</h4>';
        echo '<p style="opacity:.82;font-size:15px;line-height:1.65;color:#cbd5e1;margin:0 0 14px;">' . htmlspecialchars($zintro, ENT_QUOTES, 'UTF-8') . '</p>';
        echo '<ul style="margin:0;padding:0;list-style:none;display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:10px;">';
        foreach ($zone['items'] as $item) {
            $u = htmlspecialchars((string)($item['url'] ?? '#'), ENT_QUOTES, 'UTF-8');
            $lbl = htmlspecialchars((string)($item['label'] ?? ''), ENT_QUOTES, 'UTF-8');
            $plc = htmlspecialchars((string)($item['place'] ?? ''), ENT_QUOTES, 'UTF-8');
            $note = htmlspecialchars((string)($item['note'] ?? ''), ENT_QUOTES, 'UTF-8');
            echo '<li style="margin:0;padding:12px;border-radius:10px;background:rgba(15,23,42,.55);border:1px solid ' . $theme['border'] . ';">';
            echo '<div style="font-weight:700;color:#fef9c3;font-size:15px;margin-bottom:4px;">' . $lbl . '</div>';
            echo '<div style="font-size:15px;color:#e2e8f0;opacity:.9;">' . $plc . ' <span style="opacity:.65;">（' . $note . '）</span></div>';
            echo '<p style="margin:10px 0 0;"><a href="' . $u . '" target="_blank" rel="noopener noreferrer" style="color:' . $theme['accent'] . ';font-weight:bold;font-size:15px;text-decoration:underline;">体験可能店を Google で検索</a></p>';
            echo '</li>';
        }
        echo '</ul></article>';
    }
    echo '</section>';
}

/* ===== END: aruaru-taiken-corners.php ===== */


/* ===== BEGIN: index.php (main) ===== */

/**
 * audiocafe.tokyo/aruaru-lady/index.php
 * キャバレー・キャバクラ / 熟女キャバレー・キャバクラ / TVチャットレディ 情報
 * /aruaru から移設・独立したレディ向けページ
 * Cron: 0 5 * * * php /path/to/aruaru-lady/index.php --cron-all  (毎日・朝5時)
 */


/* =========================================================
   ★ Cron 統合モード（毎日 1コマンドのみ設定）
   実行: php /path/to/aruaru-lady/index.php --cron-all
   推奨: 0 5 * * * php /path/to/aruaru-lady/index.php --cron-all >> /home/USERNAME/logs/aruaru-lady-cron.log 2>&1
========================================================= */
if (PHP_SAPI === 'cli' && in_array('--cron-all', $argv ?? [], true)) {
    $t0 = microtime(true);
    echo "\n[" . date("Y-m-d H:i:s") . "] ========== aruaru-lady --cron-all 開始 ==========\n";

    // ① キャバクラランキング（毎日）
    echo "[" . date("Y-m-d H:i:s") . "] [1/4] キャバクラランキング クロール...\n";
    aruaru_caba_ranking_refresh(true);
    echo "[" . date("Y-m-d H:i:s") . "] [1/4] 完了\n";

    // ② 熟女キャバランキング（毎日）
    echo "[" . date("Y-m-d H:i:s") . "] [2/4] 熟女キャバランキング クロール...\n";
    aruaru_jukujo_caba_ranking_refresh(true);
    echo "[" . date("Y-m-d H:i:s") . "] [2/4] 完了\n";

    // ③ TVチャットレディ【グループチャット（パーティーチャット）版】（毎日）
    echo "[" . date("Y-m-d H:i:s") . "] [3/4] TVチャットレディ（グループ） クロール...\n";
    aruaru_tvchat_group_ranking_refresh(true);
    echo "[" . date("Y-m-d H:i:s") . "] [3/4] 完了\n";

    // ④ TVチャットレディ【通常版】（毎日）
    echo "[" . date("Y-m-d H:i:s") . "] [4/4] TVチャットレディ（通常） クロール...\n";
    aruaru_tvchat_normal_ranking_refresh(true);
    echo "[" . date("Y-m-d H:i:s") . "] [4/4] 完了\n";

    // ※ 楽天モバイルのクロールは /rakuten-mobile/index.php に移管
    //    aruaru/index.php --cron-all が /rakuten-mobile/ を代わりにクロールします

    $elapsed = round(microtime(true) - $t0, 2);
    echo "[" . date("Y-m-d H:i:s") . "] ========== aruaru-lady --cron-all 完了（{$elapsed}秒）==========\n\n";
    exit(0);
}


/* =========================================================
   Web実行: TTL超過時はキャッシュ更新
========================================================= */
if (!is_readable(ARUARU_CABA_CACHE) || (time() - (int)@filemtime(ARUARU_CABA_CACHE) >= ARUARU_CABA_TTL)) {
    aruaru_caba_ranking_refresh(false);
}
if (!is_readable(ARUARU_JUKUJO_CABA_CACHE) || (time() - (int)@filemtime(ARUARU_JUKUJO_CABA_CACHE) >= ARUARU_JUKUJO_CABA_TTL)) {
    aruaru_jukujo_caba_ranking_refresh(false);
}
if (!is_readable(ARUARU_TVCHAT_GROUP_CACHE) || (time() - (int)@filemtime(ARUARU_TVCHAT_GROUP_CACHE) >= ARUARU_TVCHAT_GROUP_TTL)) {
    aruaru_tvchat_group_ranking_refresh(false);
}
if (!is_readable(ARUARU_TVCHAT_NORMAL_CACHE) || (time() - (int)@filemtime(ARUARU_TVCHAT_NORMAL_CACHE) >= ARUARU_TVCHAT_NORMAL_TTL)) {
    aruaru_tvchat_normal_ranking_refresh(false);
}

/* =========================================================
   データ取得
========================================================= */
$aruaru_caba        = aruaru_caba_ranking_get();
$aruaru_jukujo_caba = aruaru_jukujo_caba_ranking_get();
$aruaru_tvchat_group  = aruaru_tvchat_group_ranking_get();
$aruaru_tvchat_normal = aruaru_tvchat_normal_ranking_get();
$aruaru_taiken      = aruaru_taiken_corners_data();

$aruaru_caba_sections = [
    ['id' => 'aruaru-caba-tokyo23',    'title' => '🚗 高額時給目安ランキング TOP50（東京23区内・キャバレー／キャバクラ・検索窓口）'],
    ['id' => 'aruaru-caba-tokyo-tama', 'title' => '🚗 高額時給目安ランキング TOP50（東京23区外・多摩／八王子・立川ほか・検索窓口）'],
    ['id' => 'aruaru-caba-national',   'title' => '🚗 高額時給目安ランキング TOP50（大阪〜北海道・沖縄・日本全国・検索窓口）'],
];
$aruaru_caba_section_rows = [
    'aruaru-caba-tokyo23'    => $aruaru_caba['tokyo_23']['rows']   ?? [],
    'aruaru-caba-tokyo-tama' => $aruaru_caba['tokyo_tama']['rows'] ?? [],
    'aruaru-caba-national'   => $aruaru_caba['national']['rows']   ?? [],
];

$aruaru_link_tv_chat_nonadult     = 'https://www.google.com/search?q=' . rawurlencode('TVチャットレディ　ノンアダルト');
$aruaru_link_rakuten_link_android = 'https://www.google.com/search?q=' . rawurlencode('楽天モバイル スマホアプリの名前は 楽天リンク Android版');
$aruaru_link_rakuten_link_iphone  = 'https://www.google.com/search?q=' . rawurlencode('楽天モバイル スマホアプリの名前は 楽天リンク iPhone版');
$aruaru_link_rakuten_we2plus      = 'https://www.google.com/search?q=' . rawurlencode('楽天モバイル お勧め 1円 高性能CPU 富士通製 スマホ we2 plus');

$page_updated_at  = max(
    (int)@filemtime(ARUARU_CABA_CACHE),
    (int)@filemtime(ARUARU_JUKUJO_CABA_CACHE)
);
$page_updated_str = $page_updated_at > 0 ? date('Y-m-d H:i:s', $page_updated_at) : '—';

function lady_h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function lady_render_jobs_corner(): void
{
    $q = static fn(string $s): string => 'https://www.google.com/search?q=' . rawurlencode($s);
    $jobs_ja_url = $q('女性向け 求人情報');
    $jobs_en_url = $q('Job postings for women in Japan');
    $daijob_ja   = 'https://www.daijob.com/';
    $daijob_en   = 'https://www.daijob.com/en/';
    $e = static fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    $embed = static fn(string $url): string => $url . (str_contains($url, '?') ? '&' : '?') . 'igu=1';
    ?>
<section class="lady-jobs-corner" id="lady-jobs-corner" style="padding:0 16px 1.25rem;max-width:1200px;margin:0 auto;">
  <h2 style="color:#fda4af;font-size:18px;font-weight:900;margin:0 0 6px;">💼 女性向け・外資系 求人情報</h2>
  <p style="color:#cbd5e1;font-size:14px;line-height:1.7;margin:0 0 14px;opacity:.92;">各リンクは Google 検索または転職サイトへの入口です。応募前に必ず各社の公式求人票・条件をご確認ください。</p>

  <div class="lady-jobs-block" style="margin-bottom:18px;padding:14px 16px;border-radius:12px;background:rgba(30,10,20,.55);border:1px solid rgba(251,113,133,.35);">
    <p style="margin:0 0 8px;font-size:15px;font-weight:800;">
      <a href="<?= $e($jobs_ja_url) ?>" target="_blank" rel="noopener noreferrer" style="color:#7dd3fc;text-decoration:underline;">🔍 女性向け 求人情報（Google検索）</a>
    </p>
    <iframe class="lady-jobs-iframe" title="女性向け 求人情報 — Google検索" src="<?= $e($embed($jobs_ja_url)) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" style="width:100%;height:min(420px,55vh);border:1px solid rgba(148,163,184,.25);border-radius:8px;background:#0f172a;"></iframe>
  </div>

  <div class="lady-jobs-block" style="margin-bottom:18px;padding:14px 16px;border-radius:12px;background:rgba(30,10,20,.55);border:1px solid rgba(167,139,250,.35);">
    <p style="margin:0 0 8px;font-size:15px;font-weight:800;">
      <a href="<?= $e($jobs_en_url) ?>" target="_blank" rel="noopener noreferrer" style="color:#c4b5fd;text-decoration:underline;">🔍 Job postings for women in Japan（Google Search）</a>
    </p>
    <iframe class="lady-jobs-iframe" title="Job postings for women in Japan — Google Search" src="<?= $e($embed($jobs_en_url)) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" style="width:100%;height:min(420px,55vh);border:1px solid rgba(148,163,184,.25);border-radius:8px;background:#0f172a;"></iframe>
  </div>

  <div class="lady-jobs-block" style="margin-bottom:0;padding:14px 16px;border-radius:12px;background:rgba(15,23,42,.6);border:1px solid rgba(56,189,248,.3);">
    <p style="margin:0 0 10px;font-size:15px;font-weight:800;color:#7dd3fc;">🌐 外資系転職サイト Daijob.com</p>
    <ul style="list-style:none;padding:0;margin:0;line-height:2;font-size:15px;">
      <li><a href="<?= $e($daijob_ja) ?>" target="_blank" rel="noopener noreferrer" style="color:#38bdf8;font-weight:700;text-decoration:underline;">Daijob.com　日本語 — <?= $e($daijob_ja) ?></a></li>
      <li><a href="<?= $e($daijob_en) ?>" target="_blank" rel="noopener noreferrer" style="color:#38bdf8;font-weight:700;text-decoration:underline;">Daijob.com　ENGLISH — <?= $e($daijob_en) ?></a></li>
    </ul>
  </div>
</section>
    <?php
}

/* ===== 統一「更新済み」ラベル生成（全ページ共通フォーマット） =====
   例: 2026-06-02 09:47 AM 更新済み（UPDATE）（05:00AMに毎朝更新中）
   $when … UNIXタイムスタンプ または 日付文字列（"2026/05/22 13:24" 等）を許容
   $full=false で「2026-06-02 09:47 AM」部分のみ返す（HTMLでspan分割したい時用） */
if (!function_exists('lady_update_label')) {
function lady_update_label($when = null, bool $full = true): string {
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
    return $main . ' 更新済み（UPDATE）（05:00AMに毎朝更新中）';
}
}

/* ===== 右上/左上バッジ: 年月日(西暦)・曜日(英語)・時刻AM/PM・祝祭日 =====
   祝日判定: アルゴリズム + Google カレンダー iCal の両ソース照合            */
date_default_timezone_set('Asia/Tokyo');

if (!function_exists('aruaru_jp_holidays_algo')) {
function aruaru_jp_holidays_algo(int $y): array {
    $h = [];
    $s = static function(int $m, int $d, string $n) use (&$h, $y): void {
        $k = sprintf('%04d-%02d-%02d', $y, $m, $d);
        if (!isset($h[$k])) $h[$k] = $n;
    };
    $s(1, 1, '元日'); $s(2, 11, '建国記念の日');
    if ($y >= 2020)     $s(2, 23, '天皇誕生日');
    elseif ($y <= 2018) $s(12, 23, '天皇誕生日');
    $s(4, 29, '昭和の日'); $s(5, 3, '憲法記念日');
    $s(5, 4, 'みどりの日'); $s(5, 5, 'こどもの日');
    if ($y >= 2016) $s(8, 11, '山の日');
    $s(11, 3, '文化の日'); $s(11, 23, '勤労感謝の日');
    $nm = static function(int $m, int $n) use ($y): int {
        $w = (int)date('w', (int)mktime(0, 0, 0, $m, 1, $y));
        return 1 + ((1 - $w + 7) % 7) + ($n - 1) * 7;
    };
    $s(1, $nm(1, 2), '成人の日');
    if ($y >= 2003) $s(7, $nm(7, 3), '海の日');
    $s(9, $nm(9, 3), '敬老の日');
    $s(10, $nm(10, 2), $y >= 2020 ? 'スポーツの日' : '体育の日');
    $b = $y - 1980;
    $s(3, (int)floor(20.8431 + 0.242194 * $b - floor($b / 4)), '春分の日');
    $s(9, (int)floor(23.2488 + 0.242194 * $b - floor($b / 4)), '秋分の日');
    $subs = [];
    foreach (array_keys($h) as $k) {
        $ts = (int)strtotime($k);
        if (date('w', $ts) === '0') {
            $t = $ts + 86400;
            while (isset($h[date('Y-m-d', $t)]) || isset($subs[date('Y-m-d', $t)])) $t += 86400;
            $subs[date('Y-m-d', $t)] = '振替休日';
        }
    }
    foreach ($subs as $k => $v) { if (!isset($h[$k])) $h[$k] = $v; }
    foreach (array_keys($h) as $k) {
        $ts  = (int)strtotime($k);
        $mid = date('Y-m-d', $ts + 86400);
        $nxt = date('Y-m-d', $ts + 172800);
        if (!isset($h[$mid]) && isset($h[$nxt]) && date('w', $ts + 86400) !== '0') {
            $h[$mid] = '国民の休日';
        }
    }
    ksort($h);
    return $h;
}
}

if (!function_exists('aruaru_jp_holidays_gcal')) {
function aruaru_jp_holidays_gcal(int $y): array {
    $cf  = __DIR__ . '/jp-holidays-gcal-' . $y . '.json';
    if (is_readable($cf) && (time() - (int)@filemtime($cf)) < 86400 * 30) {
        $d = json_decode((string)@file_get_contents($cf), true);
        if (is_array($d)) return $d;
    }
    try {
        $url = 'https://calendar.google.com/calendar/ical/'
             . 'ja.japanese%23holiday%40group.v.calendar.google.com/public/basic.ics';
        $ctx = stream_context_create(['http' => ['timeout' => 4, 'ignore_errors' => true]]);
        $raw = @file_get_contents($url, false, $ctx);
        if (!$raw || strlen($raw) < 200) return [];
        $raw = preg_replace('/\r\n[ \t]/', '', $raw) ?? $raw;
        preg_match_all('/BEGIN:VEVENT.*?END:VEVENT/s', $raw, $ms);
        $ev = [];
        foreach ($ms[0] as $ve) {
            if (!preg_match('/DTSTART[^:]*:(\d{8})/', $ve, $dm)) continue;
            $ds = $dm[1];
            $k  = substr($ds, 0, 4) . '-' . substr($ds, 4, 2) . '-' . substr($ds, 6, 2);
            if ((int)substr($k, 0, 4) !== $y) continue;
            if (!preg_match('/SUMMARY:(.+)/u', $ve, $sm)) continue;
            $ev[$k] = rtrim(trim($sm[1]), "\r");
        }
        ksort($ev);
        if (!empty($ev)) @file_put_contents($cf, json_encode($ev, JSON_UNESCAPED_UNICODE));
        return $ev;
    } catch (\Throwable $e) {
        return [];
    }
}
}

if (!function_exists('aruaru_jp_holiday_name')) {
function aruaru_jp_holiday_name(int $ts): ?string {
    $y = (int)date('Y', $ts);
    $k = date('Y-m-d', $ts);
    $a = aruaru_jp_holidays_algo($y);
    $g = aruaru_jp_holidays_gcal($y);
    if (isset($g[$k]) && isset($a[$k])) return $g[$k];
    return $g[$k] ?? $a[$k] ?? null;
}
}

/* バッジ用変数（$page_updated_at は上で定義済み） */
$__lady_upd_at = (int)$page_updated_at;
if ($__lady_upd_at > 0) {
    $__lady_yr  = (int)date('Y', $__lady_upd_at);
    $__lady_mo  = (int)date('n', $__lady_upd_at);
    $__lady_dy  = (int)date('j', $__lady_upd_at);
    $__lady_dow = date('D', $__lady_upd_at);       // Mon / Tue ...
    $__lady_tm  = date('g:i A', $__lady_upd_at);  // 5:03 AM
    $__lady_hol = aruaru_jp_holiday_name($__lady_upd_at);
}
/* ===== /バッジ変数 ここまで ===== */

if (!defined('ARUARU_PROMO_LAYOUT')) {
    define('ARUARU_PROMO_LAYOUT', 'lady');
}

/** @return array<string, string> */
function aruaru_promo_links(): array
{
    $q = static fn(string $s): string => 'https://www.google.com/search?q=' . rawurlencode($s);

    return [
        'tv_chat'              => $q('TVチャットレディ　ノンアダルト'),
        'rakuten_we2plus'      => $q('楽天モバイル お勧め 1円 高性能CPU 富士通製 スマホ we2 plus'),
        'rakuten_link_android' => $q('楽天モバイル スマホアプリの名前は 楽天リンク Android版'),
        'rakuten_link_iphone'  => $q('楽天モバイル スマホアプリの名前は 楽天リンク iPhone版'),
        'rakuten_packet'       => $q('楽天モバイル パケット放題 プラン'),
        'rakuten_phone'        => $q('楽天モバイル 電話放題 楽天リンク'),
        'rakuten_mobile'       => $q('楽天モバイル 乗り換え キャンペーン'),
    ];
}

/**
 * @param array<string, string> $links
 */
function aruaru_render_top_tv_chatlady(array $links): void
{
    $u = static fn(string $key) => htmlspecialchars($links[$key] ?? '#', ENT_QUOTES, 'UTF-8');
    ?>
<section class="aruaru-top-promo aruaru-top-promo--tv" id="aruaru-top-tv-chatlady" aria-labelledby="aruaru-top-tv-title">
  <div class="container-xl px-3">
    <h2 id="aruaru-top-tv-title" class="aruaru-top-promo__h2">📱 TVチャットレディ（ノンアダルト・在宅／駅前体験）</h2>
    <p class="aruaru-top-promo__lead">日本全国の駅前には体験できるお店がある場合もあります。各店の公式サイト（HP）で募集要項・年齢制限・機材・契約内容をご確認ください。自宅や病院でも、畳一畳分のスペースとスマートフォンがあれば始められる例もあります。</p>
    <p class="aruaru-top-promo__actions">
      <a class="aruaru-top-promo__btn aruaru-top-promo__btn--tv" href="<?= $u('tv_chat') ?>" target="_blank" rel="noopener noreferrer">Google検索：TVチャットレディ　ノンアダルト</a>
      <a class="aruaru-top-promo__btn aruaru-top-promo__btn--ghost" href="#taiken-tv">全国エリア別コーナーへ</a>
    </p>
    <p class="aruaru-top-promo__note"><strong>TVチャットレディ</strong>はまず<strong>スマートフォン</strong>から始めやすく、できれば<strong>WEBカメラ付きPC</strong>があると画質・安定性の面でお勧めです（各求人・各店の指定機材を必ず確認してください）。</p>
  </div>
</section>
    <?php
}


function aruaru_caba_render_table_rows(array $rows): void
{
    foreach ($rows as $row) {
        $u = lady_h((string)($row['url'] ?? '#'));
        echo '<tr style="border-bottom:1px solid #541c35;">';
        echo '<td style="padding:6px 8px;text-align:center;font-weight:bold;color:#fda4af;">' . lady_h((string)($row['rank']   ?? '')) . '</td>';
        echo '<td style="padding:6px 8px;color:#fef3c7;">' . lady_h((string)($row['area']   ?? '')) . '</td>';
        echo '<td style="padding:6px 8px;">' . lady_h((string)($row['title']  ?? '')) . '</td>';
        echo '<td style="padding:6px 8px;opacity:.95;">' . lady_h((string)($row['band']   ?? '')) . '</td>';
        echo '<td style="padding:6px 8px;opacity:.85;">' . lady_h((string)($row['pickup'] ?? '')) . '</td>';
        echo '<td style="padding:6px 8px;"><a href="' . $u . '" target="_blank" rel="noopener noreferrer" style="color:#fda4af;font-weight:bold;">Google で検索</a></td>';
        echo '</tr>';
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<!-- BUILD: 2026-05-25-CLOSE-RADIO-v3.1 (4番目CLOSE表示バグ修正 + pBot 旧値廃止) -->
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>💃 女性向けお仕事情報｜キャバレー・キャバクラ・TVチャットレディ | audiocafe.tokyo</title>
<meta name="description" content="キャバレー・キャバクラ・熟女キャバ・TVチャットレディの求人・体験入店・時給目安など女性向けお仕事情報まとめ。">
<style>
*{margin:0;padding:0;box-sizing:border-box}
html{scroll-behavior:smooth}
body{background:#0a0f1e;color:#e2e8f0;font-family:'Helvetica Neue',Arial,'Hiragino Kaku Gothic ProN',sans-serif;min-height:100vh;line-height:1.7}
a{color:#fda4af;text-decoration:none}
a:hover{text-decoration:underline}
.wrap{max-width:1200px;margin:0 auto;padding:16px}
.hero{background:linear-gradient(135deg,#1a0a14 0%,#2d0a1e 50%,#1a0a14 100%);padding:40px 20px;text-align:center;border-bottom:2px solid #be185d}
.hero h1{font-size:clamp(22px,5vw,36px);color:#fda4af;font-weight:900;line-height:1.3;margin-bottom:12px}
.hero p{color:#fcd7e0;font-size:15px;opacity:.9;max-width:700px;margin:0 auto}
.notice{background:rgba(30,58,138,.25);border:1.5px solid #3b82f6;border-radius:12px;padding:12px 16px;margin:20px 0;font-size:15px;color:#bfdbfe;display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.notice a{color:#93c5fd;font-weight:700;text-decoration:underline}
.card{background:#110818;border:1px solid #4c1d45;border-radius:16px;padding:24px;margin:28px 0}
h2{font-size:clamp(18px,4vw,24px);color:#fda4af;margin-bottom:12px;line-height:1.35}
h3{font-size:clamp(15px,3.5vw,19px);color:#f472b6;margin:24px 0 10px}
table{width:100%;border-collapse:collapse;font-size:15px}
thead tr{background:#2d0a1e;text-align:left}
th{padding:8px 6px;color:#fda4af;white-space:nowrap}
.ox{overflow-x:auto}
.toc{display:flex;flex-wrap:wrap;gap:8px;margin:16px 0}
.toc a{display:inline-block;padding:6px 14px;border-radius:20px;background:rgba(190,24,93,.18);border:1px solid #be185d;color:#fda4af;font-size:15px;font-weight:700}
.toc a:hover{background:rgba(190,24,93,.35)}
.cron-box{background:#0c1228;border:1px solid #334155;border-radius:12px;padding:16px 18px;margin-top:32px;font-size:15px}
.cron-box code{background:#0a1628;padding:2px 8px;border-radius:4px;color:#a5f3fc;font-size:15px}
footer{text-align:center;padding:32px 16px;color:#64748b;font-size:15px;border-top:1px solid #1e293b;margin-top:48px}
.container-xl{max-width:1200px;margin:0 auto;padding:0 16px}

.aruaru-top-promo{
  margin:0;padding:1rem 0 .85rem;border-bottom:1px solid #4c1d45;
}
.aruaru-top-promo--tv{
  background:linear-gradient(180deg,rgba(56,189,248,.1),rgba(17,8,24,.55));
  border-bottom-color:rgba(14,165,233,.3);
}
.aruaru-top-promo__h2{margin:0 0 .65rem;font-size:clamp(1.05rem,3.2vw,1.35rem);line-height:1.45;font-weight:800}
.aruaru-top-promo--tv .aruaru-top-promo__h2{color:#bae6fd}
.aruaru-top-promo__lead{margin:0 0 .75rem;font-size:15px;line-height:1.75;color:#e2e8f0;opacity:.95}
.aruaru-top-promo__list{margin:0 0 .75rem;padding-left:1.2rem;line-height:1.9;font-size:15px;color:#e0f2fe}
.aruaru-top-promo__list a{color:#7dd3fc;font-weight:700;text-decoration:underline}
.aruaru-top-promo__actions{display:flex;flex-wrap:wrap;gap:.5rem;margin:0 0 .65rem}
.aruaru-top-promo__btn{
  display:inline-block;padding:.45rem .85rem;border-radius:10px;font-size:15px;font-weight:800;text-decoration:none;
}
.aruaru-top-promo__btn--tv{background:#0ea5e9;color:#fff}
.aruaru-top-promo__btn--ghost{border:1px solid rgba(125,211,252,.45);color:#bae6fd;background:rgba(15,23,42,.5)}
.aruaru-top-promo__note{margin:0;font-size:15px;line-height:1.65;color:#cbd5e1;opacity:.9}
@media(max-width:640px){.hero{padding:28px 14px}.card{padding:16px}}

/* ============================================================
   Google翻訳ウィジェット（/aruaru の ext-link-switch そっくり移植版）
   スマホで画面内ピッタリ収納 + CLOSE操作性強化
============================================================ */
:root{
  --lady-logo-fixed-h:0px;
  --lady-gadget-top:calc(env(safe-area-inset-top, 0px) + 10px);
}
@media (max-width:600px){
  :root{
    --lady-gadget-top:calc(env(safe-area-inset-top, 0px) + 6px);
  }
}

/* Google翻訳ウィジェット (機能のみ、UIは隠す) */
#google_translate_element{
  position:absolute;left:-9999px;top:-9999px;width:1px;height:1px;overflow:hidden;visibility:hidden;
}
/* Google翻訳バナー・バルーン・フィードバック完全非表示 */
.goog-te-banner-frame{display:none!important}
body{top:0!important}
.goog-te-balloon-frame,.goog-te-balloon-bg,#goog-gt-tt,#goog-gt-votingframe,
.goog-tooltip,.goog-tooltip-content,.goog-te-ftphfe,.goog-te-spinner-pos,.goog-te-highlight{
  display:none!important;visibility:hidden!important;opacity:0!important;pointer-events:none!important;
}

/* ===== 外部サイト翻訳モード切替スイッチ (パネル本体) ===== */
#ext-link-switch{
  /* CSS変数化：JSから動的に上書き可能（自動収納調整） */
  --el-top: var(--lady-gadget-top);
  --el-right: calc(env(safe-area-inset-right, 0px) + 10px);
  --el-font: clamp(0.75rem, 2vw, 0.95rem);
  --el-gap: 8px;
  --el-pad-x: 18px;
  --el-pad-y-top: 12px;
  /* CLOSEはパネル内最下段に組み込まれた (paddingは標準値) */
  --el-pad-y-bot: 12px;
  --el-line: 1.3;

  position:fixed;
  top:var(--el-top);
  right:var(--el-right);
  left:auto;
  z-index:2147483646;
  background:rgba(15,23,42,0.95); backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px);
  padding:var(--el-pad-y-top) var(--el-pad-x) var(--el-pad-y-bot) var(--el-pad-x);
  border-radius:8px;
  border:2px solid rgba(34,211,238,0.8);
  color:#fff;
  font-size:var(--el-font);
  font-family:sans-serif;
  box-shadow:0 4px 16px rgba(0,0,0,0.6);
  display:flex !important; flex-direction:column; gap:var(--el-gap);
  max-width:calc(100vw - env(safe-area-inset-right, 0px) - 10px - 8px);
  /* max-height: safe-area-inset-bottom も考慮 (iPhone のホームバー領域) */
  max-height:calc(100vh - var(--el-top) - env(safe-area-inset-bottom, 0px) - 16px);
  overflow-y:auto;
  -webkit-overflow-scrolling:touch;
  overscroll-behavior:contain;
  width:max-content;
  box-sizing:border-box;
}
#ext-link-switch.minimized{display:none !important}
#ext-link-switch strong{
  color:#67e8f9;
  font-size:clamp(0.8rem, 2.2vw, 1rem);
  display:block; padding-right:0; box-sizing:border-box;
  line-height:var(--el-line);
}
#ext-link-switch label{
  cursor:pointer; display:flex; align-items:flex-start;
  gap:6px; margin-bottom:0; font-weight:bold;
  white-space:normal; word-break:break-word; line-height:var(--el-line);
}
#ext-link-switch input[type=radio]{
  margin:0; accent-color:#22d3ee;
  min-width:18px; min-height:18px; flex-shrink:0; /* タップしやすさ */
}

/* OPEN ボタン */
#ext-link-switch-toggle{
  position:fixed;
  top:var(--lady-gadget-top);
  right:calc(env(safe-area-inset-right, 0px) + 10px);
  left:auto;
  z-index:2147483647;
  background:rgba(15,23,42,0.95);
  border:2px solid rgba(34,211,238,0.8);
  color:#22d3ee;
  padding:clamp(6px, 1.2vw, 10px) clamp(12px, 2vw, 16px);
  border-radius:8px; cursor:pointer;
  font-weight:800;
  font-size:clamp(0.7rem, 2vw, 0.85rem);
  backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px);
  box-shadow:0 4px 12px rgba(0,0,0,0.5);
  display:none;
  pointer-events:auto;
  min-height:34px; /* タップ領域確保 */
  -webkit-tap-highlight-color:rgba(34,211,238,0.4);
  touch-action:manipulation;
}

/* ============================================================
   [PATCH v3] BUG FIX: 旧 #ext-link-switch-close (button) が
   画面右下に表示されるバグを防ぐ防御CSS
   - キャッシュ・アクセラレータが古いCSSを返してきても新CSSが必ず勝つ
   - CLOSE は新ラジオ (.ext-link-switch__close-row) でのみ表示
============================================================ */
/* 旧ID参照は完全に画面外へ強制退避 (もし古いキャッシュHTML/CSSが残っていても安全) */
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

/* 新CLOSEラジオは常に表示される (パネル内の通常フロー) */
.ext-link-switch__close-row{
  /* 区切り線 (他のラジオと視覚分離) */
  margin-top:6px !important;
  padding-top:8px !important;
  border-top:1px dashed rgba(148,163,184,0.4) !important;

  /* タップ領域強化 */
  min-height:44px !important;
  padding-left:4px !important;
  padding-right:4px !important;
  border-radius:6px !important;
  background:rgba(34,211,238,0.06) !important;
  border-bottom:1px solid rgba(34,211,238,0.25) !important;
  -webkit-tap-highlight-color:rgba(34,211,238,0.35);
  touch-action:manipulation;
  cursor:pointer !important;
  transition:background .12s ease, border-color .12s ease;
  /* 確実に表示 */
  display:flex !important;
  visibility:visible !important;
  opacity:1 !important;
  position:static !important;
  pointer-events:auto !important;
}
.ext-link-switch__close-row:hover,
.ext-link-switch__close-row:focus-within{
  background:rgba(34,211,238,0.16) !important;
  border-color:rgba(34,211,238,0.6) !important;
}
.ext-link-switch__close-row .ext-link-switch__close-text{
  color:#67e8f9 !important;
  font-weight:900 !important;
  letter-spacing:0.08em !important;
  font-size:1.05em !important;
  display:inline-block !important;
  visibility:visible !important;
  opacity:1 !important;
}
/* パネル内のラベル/インプット競合防止 */
#ext-link-switch label{ pointer-events:auto; }

/* Google翻訳サイト選択モーダル */
.ac-gt-site-modal{
  position:fixed; inset:0; z-index:2147483647;
  display:none; align-items:center; justify-content:center;
  padding:max(.75rem, env(safe-area-inset-bottom)); box-sizing:border-box;
}
.ac-gt-site-modal.is-open{display:flex}
.ac-gt-site-modal__backdrop{
  position:absolute; inset:0; background:rgba(0,0,0,.72);
  backdrop-filter:blur(4px); -webkit-backdrop-filter:blur(4px);
}
.ac-gt-site-modal__box{
  position:relative; z-index:1; width:min(100%, 440px);
  max-height:min(88vh, 32rem); overflow:auto;
  padding:1.2rem 1.1rem 1.1rem; border-radius:14px;
  background:rgba(15,23,42,.98);
  border:2px solid rgba(34,211,238,.55);
  box-shadow:0 20px 56px rgba(0,0,0,.55);
}
.ac-gt-site-modal__title{
  font-size:clamp(.86rem, 2.1vw, .98rem); font-weight:800;
  color:#f8fafc; line-height:1.5; margin:0 0 .75rem;
}
.ac-gt-site-modal__btns{
  display:flex; flex-direction:column; gap:.5rem; margin-bottom:.65rem;
}
.ac-gt-site-modal__btn{
  display:block; width:100%; padding:.58rem .65rem;
  border-radius:10px; border:1px solid rgba(148,163,184,.4);
  background:rgba(255,255,255,.08); color:#e2e8f0;
  font-size:.88rem; font-weight:700; cursor:pointer;
  text-align:center; line-height:1.35; word-break:break-all;
  min-height:40px;
  touch-action:manipulation;
}
.ac-gt-site-modal__btn:hover,
.ac-gt-site-modal__btn:focus-visible{
  border-color:rgba(34,211,238,.65);
  background:rgba(34,211,238,.14);
  outline:none; color:#fff;
}

/* ===== タブレット (601〜1024px) ===== */
@media (max-width:1024px) and (min-width:601px){
  #ext-link-switch{
    --el-font: clamp(0.78rem, 1.6vw, 0.92rem);
    --el-pad-x: 16px;
    --el-pad-y-top: 11px;
    --el-pad-y-bot: 11px;
    --el-gap: 7px;
    max-width:min(80vw, calc(100vw - env(safe-area-inset-right, 0px) - 10px - 8px));
  }
}

/* ===== スマホ (〜600px) 一般 ===== */
@media (max-width:600px){
  #ext-link-switch{
    --el-right: calc(env(safe-area-inset-right, 0px) + 5px);
    --el-pad-x: 12px;
    --el-pad-y-top: 10px;
    --el-pad-y-bot: 11px;
    --el-font: .85rem;
    --el-gap: 6px;
    --el-line: 1.28;
    max-width:min(94vw, calc(100vw - env(safe-area-inset-right, 0px) - 5px - 4px));
  }
  #ext-link-switch-toggle{
    top:var(--lady-gadget-top);
    right:calc(env(safe-area-inset-right, 0px) + 5px);
    font-size:.84rem;
    padding:6px 12px;
  }
  .ext-link-switch__close-row{
    min-height:42px;
    margin-top:5px;
    padding-top:7px;
  }
}

/* ===== 縦が狭い端末 (横向きスマホ等 〜520px) ===== */
@media (max-height:520px){
  #ext-link-switch{
    --el-font:.78rem;
    --el-gap:4px;
    --el-pad-y-top:8px;
    --el-pad-y-bot:9px;
    --el-line:1.22;
  }
  .ext-link-switch__close-row{
    min-height:36px;
    margin-top:3px;
    padding-top:5px;
  }
  .ext-link-switch__close-row .ext-link-switch__close-text{
    font-size:1em;
  }
}

/* ===== 極小端末 (〜360px) ===== */
@media (max-width:360px){
  #ext-link-switch{
    --el-font:.8rem;
    --el-pad-x:10px;
  }
}

/* ===== スマホ縦向き: パネルを横幅いっぱいに ===== */
@media (max-width:600px) and (orientation:portrait){
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
@media (max-height:520px) and (orientation:landscape){
  #ext-link-switch{
    --el-right: max(14px, calc(env(safe-area-inset-right, 0px) + 10px));
  }
  #ext-link-switch-toggle{
    right: max(14px, calc(env(safe-area-inset-right, 0px) + 10px)) !important;
  }
}

/* ===== OPENボタンスマホはみ出し防止 ===== */
@media (max-width:600px){
  #ext-link-switch-toggle{
    right: max(5px, env(safe-area-inset-right, 0px)) !important;
  }
}


</style>
</head>
<body>

<?php
if (!defined('RAKUTEN_MOBILE_LIBRARY')) define('RAKUTEN_MOBILE_LIBRARY', true);
require_once __DIR__ . '/../rakuten-mobile/index.php';
rm_render_embed_panel('lady', __DIR__ . '/../rakuten-mobile');
lady_render_jobs_corner();
?>

<?php if ($__lady_upd_at > 0): ?>
<style>
/* ===== 統一更新バッジ（左右二か所→上部一か所に統合・縦横スマホ対応） ===== */
#aruaru-lady-badge{
  position:fixed;
  top:var(--lady-gadget-top, calc(env(safe-area-inset-top,0px) + 10px));
  left:calc(env(safe-area-inset-left,0px) + 10px);
  /* 右側の OPEN ボタンと重ならない様に最大幅を確保 */
  right:auto;
  max-width:calc(100vw - env(safe-area-inset-left,0px) - env(safe-area-inset-right,0px) - 92px);
  z-index:2147483646;
  pointer-events:none;
  display:inline-flex; align-items:center;
  height:1.65rem; line-height:1.65rem;
  padding:0 .7rem;
  border-radius:999px;
  background:rgba(30,10,20,.92);
  border:1px solid rgba(251,113,133,.55);
  color:#fda4af;
  font:700 clamp(.58rem,2.6vw,.82rem)/1.65rem 'Noto Sans JP',system-ui,sans-serif;
  white-space:nowrap;
  overflow:hidden;
  box-shadow:0 2px 10px rgba(0,0,0,.45);
  -webkit-backdrop-filter:blur(6px); backdrop-filter:blur(6px);
}
#aruaru-lady-badge .u-dt   { color:#fbcfe8; }  /* 日時: ピンク */
#aruaru-lady-badge .u-ok   { color:#86efac; }  /* 更新済み(UPDATE): 緑 */
#aruaru-lady-badge .u-daily{ color:#c4b5fd; }  /* 05:00AMに毎朝更新中: 紫 */

/* --- 横向き(ランドスケープ)スマホ: 高さが低いので位置と文字を詰める --- */
@media (orientation:landscape) and (max-height:480px){
  #aruaru-lady-badge{
    top:calc(env(safe-area-inset-top,0px) + 4px);
    height:1.45rem; line-height:1.45rem; padding:0 .55rem;
    font-size:clamp(.55rem,1.8vw,.74rem);
    max-width:calc(100vw - env(safe-area-inset-left,0px) - env(safe-area-inset-right,0px) - 80px);
  }
}
/* --- 縦向き(ポートレート)狭幅: 補足の括弧書きから順に省略。日付と更新済みは常時表示 --- */
@media (max-width:560px){
  #aruaru-lady-badge{ padding:0 .5rem; }
  #aruaru-lady-badge .u-daily{ display:none; }   /* 「（05:00AMに毎朝更新中）」を省略 */
}
@media (max-width:430px){
  #aruaru-lady-badge .u-ok .u-ok-en{ display:none; } /* 「（UPDATE）」を省略（「更新済み」は残す） */
}
</style>
<div id="aruaru-lady-badge" class="notranslate" translate="no" aria-label="最終更新日時">📅&thinsp;<span class="u-dt"><?= lady_h(lady_update_label($__lady_upd_at, false)) ?></span><span class="u-ok">&thinsp;更新済み<span class="u-ok-en">（UPDATE）</span></span><span class="u-daily">（05:00AMに毎朝更新中）</span></div>
<?php endif; ?>

<!-- ============================================================
     Google翻訳ウィジェット（/aruaru の ext-link-switch そっくり移植）
     スマホで画面内ピッタリ収納 + CLOSE操作性強化
============================================================ -->
<!-- 外部サイト翻訳モード切替スイッチ -->
<div id="ext-link-switch-toggle" class="notranslate" translate="no" role="button" tabindex="0" aria-label="翻訳設定パネルを開く">OPEN</div>
<div id="ext-link-switch" class="notranslate" translate="no" style="display: flex !important; visibility: visible !important; opacity: 1 !important;">
  <strong>外部サイト / 翻訳設定</strong>
  <label title="Google翻訳ウィジェットでページ全体を翻訳します。※応募フォーム・回答はエラーになります。">
    <input type="radio" name="ext_mode" value="translate" checked> Google翻訳する (閲覧用)　※応募フォームや回答はエラーになります。
  </label>
  <label title="翻訳しません。応募・回答フォームが正常に動作します。">
    <input type="radio" name="ext_mode" value="raw"> Google翻訳しない (応募・回答用。※フォームが正常に動作します。)
  </label>
  <label title="選択言語のGoogle翻訳サイトへ移動します。">
    <input type="radio" name="ext_mode" value="google"> Google翻訳サイトへ移動
  </label>
  <!-- 4番目: CLOSE をラジオ風リンクとして追加。選択するとパネルを閉じる。 -->
  <label class="ext-link-switch__close-row" title="このパネルを閉じます">
    <input type="radio" name="ext_mode" value="__close__" id="ext-link-switch-close-radio"> <span class="ext-link-switch__close-text">CLOSE</span>
  </label>
</div>

<!-- Google翻訳サイト選択モーダル -->
<div id="acGtSitePickModal" class="ac-gt-site-modal notranslate" translate="no" hidden aria-modal="true" role="dialog" aria-labelledby="acGtSitePickTitle">
  <div class="ac-gt-site-modal__backdrop" data-ac-gt-site-dismiss="1"></div>
  <div class="ac-gt-site-modal__box">
    <p id="acGtSitePickTitle" class="ac-gt-site-modal__title"></p>
    <div id="acGtSitePickBtns" class="ac-gt-site-modal__btns"></div>
    <button type="button" class="ac-gt-site-modal__btn" id="acGtSitePickCancel"></button>
  </div>
</div>

<!-- Google翻訳ウィジェット本体（非表示・機能のみ利用） -->
<div id="google_translate_element" aria-hidden="true"></div>

<script>
(function(){
  "use strict";

  function getLang(){
    try{
      var sp = new URLSearchParams(location.search);
      var tr_tl = sp.get("_x_tr_tl"); if(tr_tl) return tr_tl;
      var lp = sp.get("lang");        if(lp)    return lp;
      var h = (location.hash || "").match(/translateto=([a-zA-Z\-]+)/);
      if(h) return decodeURIComponent(h[1]);
      var m = document.cookie.match(/(?:^|;\s*)googtrans=\/[^/]+\/([^;]+)/);
      if(m && m[1]) return decodeURIComponent(m[1]).trim();
      return sessionStorage.getItem("ac_last_lang")
          || sessionStorage.getItem("selectedLang")
          || localStorage.getItem("selectedLang")
          || "";
    }catch(e){ return ""; }
  }

  var LANG = getLang();

  // 「翻訳しない」強制状態
  try{
    if(sessionStorage.getItem("ext_mode_forced_raw") === "1"){ LANG = ""; }
  }catch(e){}

  var ON_PROXY = location.hostname.indexOf(".translate.goog") !== -1;

  // 翻訳モード起動時の Cookie 設定 & 必要なら 1 回だけリロード
  if(LANG && LANG !== "ja" && !ON_PROXY){
    try{
      sessionStorage.setItem("ac_last_lang", LANG);
      sessionStorage.setItem("selectedLang",  LANG);
      localStorage.setItem("selectedLang",    LANG);
      localStorage.setItem("lastSelectedLang", LANG);
      var cv = "/ja/" + LANG;
      document.cookie = "googtrans=" + cv + "; path=/; max-age=31536000";
      var h0 = location.hostname, parts0 = h0.split(".");
      if(parts0.length >= 2){
        document.cookie = "googtrans=" + cv + "; path=/; domain=." + parts0.slice(-2).join(".") + "; max-age=31536000";
      }
    }catch(e){}

    // ?lang=XX をクリーンURLへ
    try{
      var u = new URL(location.href);
      if(u.searchParams.has("lang")){
        u.searchParams.delete("lang");
        history.replaceState(null, "", u.toString());
      }
    }catch(e){}

    try{
      var cookieEffective = document.cookie.indexOf("googtrans=/ja/" + LANG) !== -1;
      var alreadyReloaded = sessionStorage.getItem("__ac_aruaru_lady_reloaded") === "1";
      if(!cookieEffective && !alreadyReloaded){
        sessionStorage.setItem("__ac_aruaru_lady_reloaded", "1");
        location.reload();
        return;
      }
    }catch(e){}
  } else if(LANG && LANG !== "ja" && ON_PROXY){
    try{
      sessionStorage.setItem("ac_last_lang", LANG);
      sessionStorage.setItem("selectedLang",  LANG);
      localStorage.setItem("selectedLang",    LANG);
      localStorage.setItem("lastSelectedLang", LANG);
    }catch(e){}
  } else if(LANG === "ja" && !ON_PROXY){
    // 日本語直アクセス: Cookie & 言語フラグ全削除
    try{
      document.cookie = "googtrans=; path=/; max-age=0";
      document.cookie = "googtrans=/ja/ja; path=/; max-age=0";
      var hh0 = location.hostname, pp0 = hh0.split(".");
      if(pp0.length >= 2){
        var dom0 = "." + pp0.slice(-2).join(".");
        document.cookie = "googtrans=; path=/; domain=" + dom0 + "; max-age=0";
        document.cookie = "googtrans=/ja/ja; path=/; domain=" + dom0 + "; max-age=0";
      }
    }catch(e){}
    try{
      sessionStorage.removeItem("ac_last_lang");
      sessionStorage.removeItem("selectedLang");
      localStorage.removeItem("selectedLang");
      sessionStorage.removeItem("__ac_aruaru_lady_reloaded");
    }catch(e){}
    try{
      var uj = new URL(location.href);
      var hadLangJa = uj.searchParams.get("lang") === "ja";
      var fullHash0 = (uj.hash || "").replace(/^#/, "");
      var hadTransJa = /(^|&)translateto=ja(&|$)/i.test(fullHash0);
      uj.searchParams.delete("lang");
      var hp = (uj.hash || "").replace(/^#/, "").split("&").filter(function(p){ return p && p.indexOf("translateto=") !== 0; });
      uj.hash = hp.length ? "#" + hp.join("&") : "";
      history.replaceState(null, "", uj.toString());
      if((hadLangJa || hadTransJa) && sessionStorage.getItem("__ac_aruaru_lady_ja_clean") !== "1"){
        try{ sessionStorage.setItem("__ac_aruaru_lady_ja_clean", "1"); }catch(e2){}
        location.reload();
        return;
      }
      try{ sessionStorage.removeItem("__ac_aruaru_lady_ja_clean"); }catch(e3){}
    }catch(e){}
    try{ localStorage.setItem("lastSelectedLang", "ja"); }catch(e4){}
    LANG = "";
  }

  /* i18n 辞書 (/aruaru の SWITCH_I18N と同一) */
  var SWITCH_I18N = {
    "en":    { title:"External Sites / Translation",  trans:"Translate (Viewing)  ※Forms may not work", raw:"Do not translate (Apply / Answer)", google:"Google Translate site (selected language)" },
    "zh-CN": { title:"外部网站 / 翻译设置",            trans:"翻译（浏览用）※表单可能无法使用",       raw:"不翻译（应聘/回答用）",            google:"Google翻译网站（所选语言）" },
    "zh-TW": { title:"外部網站 / 翻譯設定",            trans:"翻譯（瀏覽用）※表單可能無法使用",       raw:"不翻譯（應聘/回答用）",            google:"Google翻譯網站（所選語言）" },
    "ko":    { title:"외부 사이트 / 번역 설정",         trans:"번역하기（열람용）※폼 오류 가능",        raw:"번역 안 함（지원/응답용）",          google:"Google 번역 사이트（선택 언어）" },
    "fr":    { title:"Sites Externes / Traduction",   trans:"Traduire (Lecture) ※Formulaires peuvent échouer", raw:"Ne pas traduire (Postuler/Répondre)", google:"Google Traduction (langue choisie)" },
    "de":    { title:"Externe Seiten / Übersetzung",  trans:"Übersetzen (Lesen) ※Formulare können fehlschlagen", raw:"Nicht übersetzen (Bewerben/Antworten)", google:"Google Übersetzer (gewählte Sprache)" },
    "es":    { title:"Sitios Externos / Traducción",  trans:"Traducir (Lectura) ※Formularios pueden fallar", raw:"No traducir (Aplicar/Responder)", google:"Google Traductor (idioma elegido)" },
    "ru":    { title:"Внешние сайты / Перевод",        trans:"Перевести (Просмотр) ※Формы могут не работать", raw:"Не переводить (Отклик/Ответ)", google:"Google Переводчик (выбранный язык)" },
    "ar":    { title:"المواقع الخارجية / الترجمة",      trans:"ترجمة (للقراءة) ※قد لا تعمل النماذج",    raw:"عدم الترجمة (للتقديم/الإجابة)",     google:"موقع ترجمة Google (اللغة المختارة)" },
    "hi":    { title:"बाहरी साइटें / अनुवाद सेटिंग्स",  trans:"अनुवाद करें (देखने के लिए)",          raw:"अनुवाद न करें (आवेदन/उत्तर हेतु)",    google:"Google अनुवाद (चयनित भाषा)" },
    "ja":    { title:"外部サイト / 翻訳設定",          trans:"翻訳する (閲覧用)　※応募フォームや回答はエラーになります。", raw:"翻訳しない (応募・回答用。※フォームが正常に動作します。)", google:"選択言語の Google 翻訳サイトへ" }
  };

  var GT_SITE_PICK_I18N = {
    "ja":    { title:"Google翻訳サイトを表示しますか？", cancel:"元の画面に戻る" },
    "en":    { title:"Display the Google Translate site?", cancel:"Back to previous screen" },
    "zh-CN": { title:"要显示 Google 翻译网站吗？", cancel:"返回上一页" },
    "zh-TW": { title:"要顯示 Google 翻譯網站嗎？", cancel:"返回上一頁" },
    "ko":    { title:"Google 번역 사이트를 표시할까요?", cancel:"이전 화면으로 돌아가기" },
    "fr":    { title:"Afficher le site Google Traduction ?", cancel:"Retour à l'écran précédent" },
    "de":    { title:"Google Übersetzer-Website anzeigen?", cancel:"Zurück zum vorherigen Bildschirm" },
    "es":    { title:"¿Mostrar el sitio de Google Traductor?", cancel:"Volver a la pantalla anterior" },
    "pt":    { title:"Exibir o site do Google Tradutor?", cancel:"Voltar à tela anterior" },
    "it":    { title:"Visualizzare il sito di Google Traduttore?", cancel:"Torna alla schermata precedente" },
    "ru":    { title:"Открыть сайт Google Переводчика?", cancel:"Вернуться к предыдущему экрану" },
    "ar":    { title:"هل تريد عرض موقع ترجمة Google؟", cancel:"العودة إلى الشاشة السابقة" },
    "hi":    { title:"Google अनुवाद साइट दिखाएं?", cancel:"पिछली स्क्रीन पर वापस जाएं" },
    "th":    { title:"แสดงเว็บไซต์ Google แปลภาษา?", cancel:"กลับไปยังหน้าจอก่อนหน้า" },
    "vi":    { title:"Hiển thị trang Google Dịch?", cancel:"Quay lại màn hình trước" },
    "id":    { title:"Tampilkan situs Google Terjemahan?", cancel:"Kembali ke layar sebelumnya" },
    "uk":    { title:"Відкрити сайт Google Перекладача?", cancel:"Повернутися до попереднього екрана" },
    "tr":    { title:"Google Çeviri sitesi açılsın mı?", cancel:"Önceki ekrana dön" },
    "nl":    { title:"Google Translate-site weergeven?", cancel:"Terug naar vorig scherm" },
    "pl":    { title:"Wyświetlić stronę Tłumacza Google?", cancel:"Wróć do poprzedniego ekranu" }
  };

  function getGtSitePickDict(lang){
    var k = lang || "en";
    var prefix = k.split("-")[0];
    return GT_SITE_PICK_I18N[k] || GT_SITE_PICK_I18N[prefix] || GT_SITE_PICK_I18N.en;
  }

  function buildGoogleTranslateSiteUrl(pageUrl, tl, uiHl){
    var isJapanese = (tl === "ja");
    var targetLang = isJapanese ? "en" : tl;
    var uiLang     = isJapanese ? "ja" : tl;
    var tldMap = {
      "en":"com","fr":"fr","de":"de","es":"es","it":"it","pt":"pt","pt-BR":"com.br",
      "ru":"ru","ko":"co.kr","zh-CN":"cn","zh-TW":"com.tw",
      "ar":"com.sa","hi":"co.in","th":"co.th","vi":"com.vn","id":"co.id",
      "tr":"com.tr","nl":"nl","pl":"pl","uk":"com.ua","ja":"co.jp",
      "cs":"cz","da":"dk","fi":"fi","sv":"se","no":"no","hu":"hu",
      "ro":"ro","bg":"bg","sk":"sk","sl":"si","hr":"hr","sr":"rs",
      "lt":"lt","lv":"lv","et":"ee","el":"gr",
      "he":"co.il","iw":"co.il"
    };
    var tld = tldMap[tl] || (isJapanese ? "co.jp" : "com");
    if(isJapanese) tld = "co.jp";
    try{
      var dest = new URL("https://translate.google." + tld + "/");
      dest.searchParams.set("hl", uiLang);
      dest.searchParams.set("sl", "ja");
      dest.searchParams.set("tl", targetLang);
      dest.searchParams.set("text", pageUrl);
      dest.searchParams.set("op", "translate");
      return dest.toString();
    }catch(e){
      return "https://translate.google." + tld + "/?hl=" + encodeURIComponent(uiLang)
        + "&sl=ja&tl=" + encodeURIComponent(targetLang)
        + "&text=" + encodeURIComponent(pageUrl)
        + "&op=translate";
    }
  }

  function wireAcGtSitePickModal(){
    if(window.__acGtSitePickWired) return;
    window.__acGtSitePickWired = true;
    var m = document.getElementById("acGtSitePickModal");
    if(!m) return;
    m.addEventListener("click", function(ev){
      if(!m.classList.contains("is-open")) return;
      var dismiss = ev.target.getAttribute && ev.target.getAttribute("data-ac-gt-site-dismiss") === "1";
      if(dismiss || ev.target.id === "acGtSitePickCancel"){
        if(typeof window.__acGtSitePickOnCancel === "function") window.__acGtSitePickOnCancel();
      }
    });
    document.addEventListener("keydown", function(ev){
      if(ev.key !== "Escape" || !m.classList.contains("is-open")) return;
      if(typeof window.__acGtSitePickOnCancel === "function") window.__acGtSitePickOnCancel();
    });
  }

  function translateSwitchUI(){
    var sw = document.getElementById("ext-link-switch");
    if(!sw) return;
    if(sw.classList.contains("minimized")) return;
    var targetLang = LANG || "ja";
    var prefix = targetLang.split("-")[0];
    var dict = SWITCH_I18N[targetLang] || SWITCH_I18N[prefix] || SWITCH_I18N["ja"];
    sw.setAttribute("style", "display: flex !important; visibility: visible !important; opacity: 1 !important;");
    var titleEl = sw.querySelector("strong");
    if(titleEl) titleEl.textContent = dict.title;
    var labels = sw.querySelectorAll("label");
    if(labels.length >= 3){
      labels[0].childNodes.forEach(function(n){ if(n.nodeType === 3 && n.nodeValue.trim().length > 0) n.nodeValue = " " + dict.trans; });
      labels[1].childNodes.forEach(function(n){ if(n.nodeType === 3 && n.nodeValue.trim().length > 0) n.nodeValue = " " + dict.raw; });
      labels[2].childNodes.forEach(function(n){ if(n.nodeType === 3 && n.nodeValue.trim().length > 0) n.nodeValue = " " + dict.google; });
    }
  }

  function ensureSwitchVisible(){
    var sw = document.getElementById("ext-link-switch");
    if(!sw) return;
    if(sw.classList.contains("minimized")) return;
    sw.setAttribute("style", "display: flex !important; visibility: visible !important; opacity: 1 !important;");
  }

  /* ===== スマホ自動収納調整: パネルがビューポートに収まるよう動的計算 ===== */
  function fitPanel(){
    var sw = document.getElementById("ext-link-switch");
    if(!sw || sw.classList.contains("minimized")) return;
    var vv = window.visualViewport;
    var vw = vv ? vv.width  : (window.innerWidth  || document.documentElement.clientWidth);
    var vh = vv ? vv.height : (window.innerHeight || document.documentElement.clientHeight);
    var offsetTop = vv ? vv.offsetTop : 0;

    var marginX = vw <= 600 ? 5 : 10;
    var marginY = vw <= 600 ? 6 : 8;
    var availW = Math.max(220, vw - marginX*2);
    var availH = Math.max(180, vh - marginY*2);

    // 上位置 (visualViewport offset 考慮)
    var topPx = (vv && offsetTop > 0) ? offsetTop + marginY : null;
    if(topPx != null){
      sw.style.setProperty("--el-top", topPx + "px");
    } else {
      sw.style.removeProperty("--el-top"); // CSSデフォルトに任せる
    }

    // max-height は動的に
    var topNow = parseFloat(getComputedStyle(sw).top) || 0;
    sw.style.maxHeight = Math.max(180, vh - topNow - marginY) + "px";

    // はみ出している場合はフォントサイズを段階的に縮小
    // [PATCH v3] CLOSEはパネル内に入ったため、pBot は通常値 (旧値2.7rem/2.3rem/2.0remは廃止)
    var shrinkSteps = [
      { font:".82rem", gap:"6px",  pTop:"10px", pBot:"10px", line:"1.28" },
      { font:".76rem", gap:"5px",  pTop:"8px",  pBot:"8px",  line:"1.22" },
      { font:".7rem",  gap:"4px",  pTop:"6px",  pBot:"6px",  line:"1.18" }
    ];
    // 一旦リセット
    ["--el-font","--el-gap","--el-pad-y-top","--el-pad-y-bot","--el-line"].forEach(function(p){
      sw.style.removeProperty(p);
    });
    // 必要なステップだけ適用
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
      var probe = document.createElement("div");
      probe.style.cssText = "position:fixed;left:env(safe-area-inset-left,0px);right:env(safe-area-inset-right,0px);visibility:hidden;pointer-events:none;";
      document.body.appendChild(probe);
      var probeRect = probe.getBoundingClientRect();
      document.body.removeChild(probe);
      var safeLeft  = Math.max(0, probeRect.left);
      var safeRight = Math.max(0, vw - probeRect.right);

      var sideMargin = 4;
      var leftPx  = safeLeft  + sideMargin;
      var rightPx = safeRight + sideMargin;

      sw.style.setProperty("left",      leftPx  + "px", "important");
      sw.style.setProperty("right",     rightPx + "px", "important");
      sw.style.setProperty("width",     "auto",         "important");
      sw.style.setProperty("max-width", "none",         "important");
      sw.style.setProperty("box-sizing", "border-box");
    } else {
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
      var panelLeftOver = 2 - swRect.left;
      sw.style.setProperty("left", panelLeftOver + "px", "important");
    }
  }

  // リサイズ/向き変更/キーボード表示にスロットルで追従
  var fitRaf = 0;
  function fitPanelThrottled(){
    if(fitRaf) return;
    fitRaf = requestAnimationFrame(function(){ fitRaf = 0; fitPanel(); });
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
  function initToggleLogic(){
    var sw         = document.getElementById("ext-link-switch");
    var openBtn    = document.getElementById("ext-link-switch-toggle");
    var closeRadio = document.getElementById("ext-link-switch-close-radio");
    if(!sw || !openBtn) return;

    // 二重バインド防止
    if(sw.__acToggleBound) return;
    sw.__acToggleBound = true;
    openBtn.__acBound  = true;

    // BUG FIX: もし古いキャッシュHTMLで旧 #ext-link-switch-close が残っていたら強制非表示
    var legacyBtn = document.getElementById("ext-link-switch-close");
    if(legacyBtn && legacyBtn.tagName === "BUTTON"){
      legacyBtn.style.cssText = "position:absolute!important;left:-99999px!important;top:-99999px!important;width:1px!important;height:1px!important;opacity:0!important;visibility:hidden!important;pointer-events:none!important;display:block!important;z-index:-1!important;";
      legacyBtn.setAttribute("aria-hidden", "true");
      legacyBtn.setAttribute("tabindex", "-1");
    }

    // 共通: パネルを閉じる
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
      openBtn.style.setProperty("top", "var(--lady-gadget-top)", "important");
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
        // ラベルクリック→input変化のフローと、直接inputクリックの両方をカバー
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

  function initExtModeSwitch(){
    wireAcGtSitePickModal();
    initToggleLogic();
    var radios = document.querySelectorAll('input[name="ext_mode"]');
    var isProxy = (location.hostname.indexOf(".translate.goog") !== -1);

    var isForcedRaw = false;
    try{ isForcedRaw = sessionStorage.getItem("ext_mode_forced_raw") === "1"; }catch(e){}
    for(var i = 0; i < radios.length; i++){
      if(radios[i].value === "translate" && !isForcedRaw && (isProxy || (LANG && LANG !== "ja"))) radios[i].checked = true;
      if(radios[i].value === "raw" && (isForcedRaw || (!isProxy && (!LANG || LANG === "ja")))) radios[i].checked = true;
    }

    for(var j = 0; j < radios.length; j++){
      radios[j].addEventListener("change", function(){
        // [PATCH v2] 4番目の CLOSE ラジオは閉じる動作専用 (initToggleLogic 側で処理済み)
        if(this.value === "__close__"){
          return;
        }
        var target = LANG || "";
        if(!target){
          try{
            target = sessionStorage.getItem("ac_last_lang")
                  || sessionStorage.getItem("selectedLang")
                  || localStorage.getItem("lastSelectedLang")
                  || localStorage.getItem("selectedLang")
                  || "";
          }catch(e){}
        }
        if(!target) target = "en";

        if(this.value === "raw"){
          try{ sessionStorage.setItem("ext_mode_forced_raw", "1"); }catch(e){}
          try{
            document.cookie = "googtrans=; path=/; max-age=0";
            document.cookie = "googtrans=/ja/ja; path=/; max-age=0";
            var hh = location.hostname, pp = hh.split(".");
            if(pp.length >= 2){
              var dom = "." + pp.slice(-2).join(".");
              document.cookie = "googtrans=; path=/; domain=" + dom + "; max-age=0";
              document.cookie = "googtrans=/ja/ja; path=/; domain=" + dom + "; max-age=0";
            }
          }catch(e){}
          try{
            sessionStorage.removeItem("selectedLang");
            localStorage.removeItem("selectedLang");
            sessionStorage.removeItem("ac_last_lang");
            sessionStorage.removeItem("__ac_aruaru_lady_reloaded");
          }catch(e){}
          if(isProxy){
            var raw = location.href.replace(".translate.goog", "").replace(/-/g, ".");
            try{
              var u = new URL(raw);
              ["_x_tr_sl","_x_tr_tl","_x_tr_hl","_x_tr_pto","_x_tr_hist"].forEach(function(k){ u.searchParams.delete(k); });
              raw = u.toString();
            }catch(e){}
            location.replace(raw);
          } else {
            var u2 = new URL(location.href);
            u2.searchParams.delete("lang");
            var hashParts = (u2.hash || "").replace(/^#/, "").split("&").filter(function(p){ return p && p.indexOf("translateto=") !== 0; });
            u2.hash = hashParts.length ? "#" + hashParts.join("&") : "";
            try{ history.replaceState(null, "", u2.toString()); }catch(e){}
            location.reload();
          }
        } else if(this.value === "google"){
          var tl = LANG || "";
          if(!tl){
            try{ tl = sessionStorage.getItem("ac_last_lang") || sessionStorage.getItem("selectedLang") || localStorage.getItem("selectedLang") || ""; }catch(e){}
          }
          if(!tl) tl = "en";
          if(tl === "ja") tl = "en";
          var uiHl = (tl === "iw" || tl === "he") ? "he" : tl;
          var dict = getGtSitePickDict(tl);
          var gcNav = tl;

          function getGtSourcePageUrl(){
            try{
              if(isProxy){
                var rawHref = location.href.replace(/\.translate\.goog/, "").replace(/-/g, ".");
                try{
                  var u0 = new URL(rawHref);
                  ["_x_tr_sl","_x_tr_tl","_x_tr_hl","_x_tr_pto","_x_tr_hist"].forEach(function(k){ u0.searchParams.delete(k); });
                  u0.searchParams.delete("lang");
                  var hp0 = (u0.hash || "").replace(/^#/, "").split("&").filter(function(p){ return p && p.indexOf("translateto=") !== 0; });
                  u0.hash = hp0.length ? "#" + hp0.join("&") : "";
                  return u0.toString();
                }catch(e2){ return rawHref; }
              }
              var u = new URL(location.href);
              u.searchParams.delete("lang");
              var hp = (u.hash || "").replace(/^#/, "").split("&").filter(function(p){ return p && p.indexOf("translateto=") !== 0; });
              u.hash = hp.length ? "#" + hp.join("&") : "";
              return u.toString();
            }catch(e1){ return location.href; }
          }

          function destAudiocafe(){
            if(gcNav === "ja") return "https://audiocafe.tokyo/?lang=ja";
            return "https://translate.google.com/translate?sl=ja&tl=" + encodeURIComponent(gcNav) + "&u=" + encodeURIComponent("https://audiocafe.tokyo/");
          }
          function destRayTop(){
            if(gcNav === "ja") return "https://audiocafe.tokyo/top/?lang=ja";
            return "https://audiocafe.tokyo/top/?lang=" + encodeURIComponent(gcNav) + "#translateto=" + encodeURIComponent(gcNav);
          }
          function destAruaru(){
            if(gcNav === "ja") return "https://audiocafe.tokyo/aruaru/";
            return "https://audiocafe.tokyo/aruaru/?lang=" + encodeURIComponent(gcNav) + "#translateto=" + encodeURIComponent(gcNav);
          }
          function destAruaruLady(){
            if(gcNav === "ja") return "https://audiocafe.tokyo/aruaru-lady/";
            return "https://audiocafe.tokyo/aruaru-lady/?lang=" + encodeURIComponent(gcNav) + "#translateto=" + encodeURIComponent(gcNav);
          }
          var siteRows = [
            { href: destAudiocafe(),  label:"audiocafe.tokyo" },
            { href: destRayTop(),     label:"audiocafe.tokyo/top" },
            { href: destAruaru(),     label:"audiocafe.tokyo/aruaru" },
            { href: destAruaruLady(), label:"audiocafe.tokyo/aruaru-lady" }
          ];

          function revertRadios(){
            for(var k = 0; k < radios.length; k++){
              if(radios[k].value === ((isProxy || (LANG && LANG !== "ja")) ? "translate" : "raw")) radios[k].checked = true;
            }
          }
          function closePick(){
            var modal = document.getElementById("acGtSitePickModal");
            if(modal){
              modal.classList.remove("is-open");
              modal.setAttribute("hidden", "");
            }
            try{ delete window.__acGtSitePickOnCancel; }catch(e2){ window.__acGtSitePickOnCancel = undefined; }
          }
          function openPick(){
            var modal = document.getElementById("acGtSitePickModal");
            var titleEl = document.getElementById("acGtSitePickTitle");
            var cancelEl = document.getElementById("acGtSitePickCancel");
            var wrap = document.getElementById("acGtSitePickBtns");
            if(!modal || !titleEl || !cancelEl || !wrap){
              try{ window.open(buildGoogleTranslateSiteUrl(getGtSourcePageUrl(), tl, uiHl), "_blank", "noopener,noreferrer"); }catch(e){}
              setTimeout(revertRadios, 50);
              return;
            }
            titleEl.textContent = dict.title;
            cancelEl.textContent = dict.cancel;
            wrap.innerHTML = "";
            window.__acGtSitePickOnCancel = function(){ closePick(); revertRadios(); };
            try{ window.open(buildGoogleTranslateSiteUrl(getGtSourcePageUrl(), tl, uiHl), "_blank", "noopener,noreferrer"); }catch(eGt){}
            for(var si = 0; si < siteRows.length; si++){
              (function(row){
                var b = document.createElement("button");
                b.type = "button";
                b.className = "ac-gt-site-modal__btn";
                b.textContent = row.label;
                b.addEventListener("click", function(){
                  closePick(); revertRadios();
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
          // 翻訳する
          try{ sessionStorage.removeItem("ext_mode_forced_raw"); }catch(e){}
          if(target === "ja") target = "en";
          var v = "/ja/" + target;
          try{
            document.cookie = "googtrans=" + v + "; path=/; max-age=31536000";
            var hh2 = location.hostname, pp2 = hh2.split(".");
            if(pp2.length >= 2){
              document.cookie = "googtrans=" + v + "; path=/; domain=." + pp2.slice(-2).join(".") + "; max-age=31536000";
            }
            sessionStorage.setItem("selectedLang", target);
            localStorage.setItem("selectedLang", target);
            sessionStorage.setItem("ac_last_lang", target);
            sessionStorage.removeItem("__ac_aruaru_lady_reloaded");
          }catch(e){}
          try{
            var u3 = new URL(location.href);
            var hashParts2 = (u3.hash || "").replace(/^#/, "").split("&").filter(function(p){ return p && p.indexOf("translateto=") !== 0; });
            hashParts2.push("translateto=" + encodeURIComponent(target));
            u3.hash = "#" + hashParts2.join("&");
            history.replaceState(null, "", u3.toString());
          }catch(e){}
          location.reload();
        }
      });
    }
  }

  window.googleTranslateElementInit = function(){
    new google.translate.TranslateElement(
      { pageLanguage:"ja", layout: google.translate.TranslateElement.InlineLayout.SIMPLE, autoDisplay:false },
      "google_translate_element"
    );
    initExtModeSwitch();

    if(LANG && LANG !== "ja"){
      try{
        var cv = "/ja/" + LANG;
        document.cookie = "googtrans=" + cv + "; path=/; max-age=31536000";
        var h = location.hostname, parts = h.split(".");
        if(parts.length >= 2){
          document.cookie = "googtrans=" + cv + "; path=/; domain=." + parts.slice(-2).join(".") + "; max-age=31536000";
        }
      }catch(e){}
      var attempts = 0;
      var timer = setInterval(function(){
        var sel = document.querySelector(".goog-te-combo");
        if(sel){
          clearInterval(timer);
          if(sel.value !== LANG){
            sel.value = LANG;
            try{
              var ev = document.createEvent("HTMLEvents");
              ev.initEvent("change", true, true);
              sel.dispatchEvent(ev);
            }catch(e){}
          }
          translateSwitchUI();
          ensureSwitchVisible();
          fitPanel();
        }
        if(++attempts > 30){
          clearInterval(timer);
          translateSwitchUI();
          ensureSwitchVisible();
          fitPanel();
        }
      }, 300);
    } else {
      ensureSwitchVisible();
      fitPanel();
    }
  };

  // Google翻訳スクリプト読み込み
  var s = document.createElement("script");
  s.src = "https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit";
  s.onerror = function(){};
  document.body.appendChild(s);

  // 初期フィット (Google翻訳の前にも一度走らせる)
  if(document.readyState === "loading"){
    document.addEventListener("DOMContentLoaded", fitPanel);
  } else {
    fitPanel();
  }
})();
</script>





<?php
$_aruaru_promo_links  = aruaru_promo_links();
$_aruaru_promo_layout = defined('ARUARU_PROMO_LAYOUT') ? (string) ARUARU_PROMO_LAYOUT : 'aruaru';
if ($_aruaru_promo_layout === 'lady') {
    aruaru_render_top_tv_chatlady($_aruaru_promo_links);
}
?>

<div class="hero">
  <h1>💃 女性向けお仕事情報</h1>
  <p>キャバレー・キャバクラ・熟女キャバ・TVチャットレディの求人・体験入店・時給目安など。<br>
  各リンクは Google 検索窓口です。必ず各店の公式サイト・求人票・法令をご確認ください。</p>
  <!-- 更新日時は画面上部の統一バッジ（#aruaru-lady-badge）に一本化 -->
</div>

<div class="wrap">

<div class="notice">
  <span>🔗</span>
  <span>IT技術・プログラミング・英会話など女性向け以外の情報は</span>
  <a href="https://audiocafe.tokyo/aruaru" target="_blank" rel="noopener noreferrer">audiocafe.tokyo/aruaru</a>
  <span>をご覧ください。</span>
</div>

<div class="toc">
  <a href="#lady-rakuten-mobile-corner">📶 楽天モバイル</a>
  <a href="#lady-jobs-corner">💼 女性向け求人</a>
  <a href="#tv-corner">📱 TVチャットレディ</a>
  <a href="#taiken-corner">🗺️ 体験入店</a>
  <a href="#caba-corner">🚗 キャバクラ時給TOP50</a>
  <a href="#jukujo-corner">✨ 熟女キャバTOP50</a>
</div>

<!-- TVチャットレディ -->
<div class="card" id="tv-corner">
<h2>📱 TVチャットレディ（ノンアダルト・在宅／駅前体験）</h2>
<p style="opacity:.9;font-size:15px;line-height:1.75;color:#e0f2fe;margin-bottom:12px;">日本全国の駅前には体験できるお店がある場合もあります。事前に検索し、各店の公式サイト（HP）で募集要項・年齢制限・機材・契約内容をご確認ください。自宅や病院で入院中でも、畳一畳分のスペースとスマートフォンがあれば始められる例もあります。</p>
<p style="margin:0 0 10px;font-size:15px;"><a href="<?= lady_h($aruaru_link_tv_chat_nonadult) ?>" target="_blank" rel="noopener noreferrer" style="color:#38bdf8;font-weight:bold;text-decoration:underline;">🔍 Google検索：TVチャットレディ　ノンアダルト</a></p>
<p style="margin:0 0 10px;font-size:15px;"><a href="https://atgroup.jp/" target="_blank" rel="noopener noreferrer" style="color:#7dd3fc;font-weight:bold;">📱 ノンアダルト チャットレディ — atgroup.jp</a></p>
<ul style="font-size:15px;line-height:2.1;padding-left:1.2rem;color:#cbd5e1;margin-bottom:14px;">
  <li><a href="<?= lady_h($aruaru_link_rakuten_we2plus) ?>" target="_blank" rel="noopener noreferrer" style="color:#7dd3fc;">🔍 楽天モバイル お勧め 富士通製 We2 Plus（1円・高性能CPU）</a></li>
  <li><a href="<?= lady_h($aruaru_link_rakuten_link_android) ?>" target="_blank" rel="noopener noreferrer" style="color:#7dd3fc;">🔍 楽天リンク Android版</a></li>
  <li><a href="<?= lady_h($aruaru_link_rakuten_link_iphone) ?>" target="_blank" rel="noopener noreferrer" style="color:#7dd3fc;">🔍 楽天リンク iPhone版</a></li>
</ul>
<p style="opacity:.88;font-size:15px;line-height:1.75;color:#cbd5e1;">楽天リンクアプリ経由の通話で電話放題プランなどを利用できる場合があります。楽天モバイルのアンテナが届くエリアではパケット放題プランも検討できます。<br><br>
<strong style="color:#fef08a;">TVチャットレディ</strong>は、まず<strong>スマートフォン</strong>から始めやすく、できれば<strong>WEBカメラ付きのオンラインPC環境</strong>もあると画質・安定性の面でお勧めです（各求人・各店の指定機材を必ずご確認ください）。</p>
</div>

<!-- 体験入店コーナー -->
<div class="card" id="taiken-corner">
<h2>🗺️ 体験入店・体験可能店（全国エリア別）</h2>
<p style="opacity:.88;font-size:15px;line-height:1.65;color:#c7d2fe;margin:0 0 16px;"><?= lady_h($aruaru_taiken['disclaimer'] ?? '') ?></p>
<div style="margin-bottom:14px;">
  <a href="#taiken-tv"     style="color:#7dd3fc;font-weight:bold;margin-right:12px;">TVチャットレディ</a>
  <a href="#taiken-jukujo" style="color:#fb7185;font-weight:bold;margin-right:12px;">熟女キャバ</a>
  <a href="#taiken-caba"   style="color:#fda4af;font-weight:bold;">キャバレー・キャバクラ</a>
</div>
<?php if (!empty($aruaru_taiken['tv']['zones'])): ?>
<ul style="font-size:15px;line-height:1.9;padding-left:1.2rem;margin-bottom:16px;">
<?php foreach (($aruaru_taiken['tv']['zones'] ?? []) as $tz): ?>
  <li><a href="#taiken-tv-<?= lady_h((string)($tz['id'] ?? '')) ?>" style="color:#bae6fd;"><?= lady_h((string)($tz['title'] ?? '')) ?></a></li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
<?php
if (function_exists('aruaru_taiken_render_category')) {
    aruaru_taiken_render_category($aruaru_taiken['tv'],     ['id' => 'taiken-tv',     'label' => 'TVチャットレディ',      'color_head' => '#7dd3fc', 'color_link' => '#7dd3fc']);
    aruaru_taiken_render_category($aruaru_taiken['jukujo'], ['id' => 'taiken-jukujo', 'label' => '熟女キャバ',           'color_head' => '#fb7185', 'color_link' => '#fb7185']);
    aruaru_taiken_render_category($aruaru_taiken['caba'],   ['id' => 'taiken-caba',   'label' => 'キャバレー・キャバクラ', 'color_head' => '#fda4af', 'color_link' => '#fda4af']);
}
?>
</div>

<!-- ▼▼▼ TVチャットレディ 高額時給ランキング（グループチャット（パーティーチャット）版 / 通常版）▼▼▼ -->
<!-- TVチャットレディ【グループチャット（パーティーチャット）版】 TOP50 -->
<div class="card" id="tvchat-group-corner">
<h2>📱 TVチャットレディ【グループチャット（パーティーチャット）版】 全国・高額時給目安ランキング TOP50</h2>
<!-- ▼ 高額時給 No.1 固定バナー -->
<div style="display:flex;align-items:center;gap:12px;background:linear-gradient(135deg,#7c1d4e 0%,#3b0764 100%);border:2px solid #fbbf24;border-radius:12px;padding:14px 18px;margin-bottom:14px;">
  <div style="flex-shrink:0;background:#fbbf24;color:#1c0a14;font-weight:900;font-size:18px;border-radius:50%;width:52px;height:52px;display:flex;align-items:center;justify-content:center;line-height:1.1;text-align:center;">No.<br>1</div>
  <div style="flex:1;min-width:0;">
    <div style="color:#fbbf24;font-weight:bold;font-size:16px;margin-bottom:4px;">🏆 高額時給 No.1 — グループチャット（パーティーチャット）</div>
    <div style="color:#fef3c7;font-size:15px;line-height:1.6;">複数人同時参加で収入が大幅アップ。人数が多い場合、<strong style="color:#fbbf24;">時給 36,000円〜177,000円</strong> の例もあります。</div>
    <div style="margin-top:8px;">
      <a href="https://atgroup.jp/money" target="_blank" rel="noopener noreferrer"
         style="display:inline-block;background:#fbbf24;color:#1c0a14;font-weight:bold;font-size:15px;padding:7px 20px;border-radius:8px;text-decoration:none;">
        💰 ATグループ 高額報酬ページを見る →
      </a>
    </div>
  </div>
</div>
<!-- ▲ 高額時給 No.1 固定バナー -->
<p style="opacity:.88;font-size:15px;line-height:1.7;margin-bottom:8px;color:#fde68a;"><?= lady_h($aruaru_tvchat_group['disclaimer'] ?? '') ?></p>
<p style="opacity:.65;font-size:15px;margin-bottom:14px;color:#cbd5e1;">📅 リスト更新（キャッシュ・毎日自動）: <?= lady_h((string)($aruaru_tvchat_group['updated_at'] ?? '—')) ?> — 「高額時給順」は報酬率・同時接続効率の目安インデックスによる並びで、特定サイト/店舗の格付けではありません。</p>
<div class="ox">
<table>
<thead><tr style="background:#1c0a14;text-align:left;">
  <th style="width:48px;color:#fbbf24;">順位</th><th style="color:#fbbf24;">プラットフォーム</th><th style="color:#fbbf24;">サイト/エリア・特徴</th><th style="color:#fbbf24;">時給目安帯</th><th style="color:#fbbf24;">補足</th><th style="color:#fbbf24;width:110px;">検索</th>
</tr></thead>
<tbody>
<?php foreach (($aruaru_tvchat_group['rows'] ?? []) as $tg):
    $tgu = lady_h((string)($tg['url'] ?? '#')); ?>
<tr style="border-bottom:1px solid #4c1d45;">
  <td style="padding:6px 8px;text-align:center;font-weight:bold;color:#fbbf24;"><?= lady_h((string)($tg['rank']  ?? '')) ?></td>
  <td style="padding:6px 8px;color:#fef3c7;"><?= lady_h((string)($tg['plat']  ?? '')) ?></td>
  <td style="padding:6px 8px;"><?= lady_h((string)($tg['title'] ?? '')) ?></td>
  <td style="padding:6px 8px;opacity:.95;"><?= lady_h((string)($tg['band']  ?? '')) ?></td>
  <td style="padding:6px 8px;opacity:.85;"><?= lady_h((string)($tg['pickup'] ?? '')) ?></td>
  <td style="padding:6px 8px;"><a href="<?= $tgu ?>" target="_blank" rel="noopener noreferrer" style="color:#fbbf24;font-weight:bold;">Google で検索</a></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>

<!-- TVチャットレディ【通常版／1対1】 TOP50 -->
<div class="card" id="tvchat-normal-corner">
<h2>📲 TVチャットレディ【通常版／1対1】 全国・高額時給目安ランキング TOP50</h2>
<p style="opacity:.88;font-size:15px;line-height:1.7;margin-bottom:8px;color:#fde68a;"><?= lady_h($aruaru_tvchat_normal['disclaimer'] ?? '') ?></p>
<p style="opacity:.65;font-size:15px;margin-bottom:14px;color:#cbd5e1;">📅 リスト更新（キャッシュ・毎日自動）: <?= lady_h((string)($aruaru_tvchat_normal['updated_at'] ?? '—')) ?> — 「高額時給順」は報酬率・稼働効率の目安インデックスによる並びで、特定サイト/店舗の格付けではありません。</p>
<div class="ox">
<table>
<thead><tr style="background:#1c0a14;text-align:left;">
  <th style="width:48px;color:#34d399;">順位</th><th style="color:#34d399;">プラットフォーム</th><th style="color:#34d399;">サイト/エリア・特徴</th><th style="color:#34d399;">時給目安帯</th><th style="color:#34d399;">補足</th><th style="color:#34d399;width:110px;">検索</th>
</tr></thead>
<tbody>
<?php foreach (($aruaru_tvchat_normal['rows'] ?? []) as $tn):
    $tnu = lady_h((string)($tn['url'] ?? '#')); ?>
<tr style="border-bottom:1px solid #4c1d45;">
  <td style="padding:6px 8px;text-align:center;font-weight:bold;color:#34d399;"><?= lady_h((string)($tn['rank']  ?? '')) ?></td>
  <td style="padding:6px 8px;color:#fef3c7;"><?= lady_h((string)($tn['plat']  ?? '')) ?></td>
  <td style="padding:6px 8px;"><?= lady_h((string)($tn['title'] ?? '')) ?></td>
  <td style="padding:6px 8px;opacity:.95;"><?= lady_h((string)($tn['band']  ?? '')) ?></td>
  <td style="padding:6px 8px;opacity:.85;"><?= lady_h((string)($tn['pickup'] ?? '')) ?></td>
  <td style="padding:6px 8px;"><a href="<?= $tnu ?>" target="_blank" rel="noopener noreferrer" style="color:#34d399;font-weight:bold;">Google で検索</a></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>
<!-- ▲▲▲ TVチャットレディ 高額時給ランキング ▲▲▲ -->

<!-- キャバクラ 時給目安 TOP50 -->
<div class="card" id="caba-corner">
<h2>🚗 キャバクラ・キャバレー 高額時給目安ランキング TOP50</h2>

<!-- YouTube検索リンク -->
<div style="margin-bottom:16px;padding:12px 14px;background:rgba(190,24,93,.12);border:1px solid rgba(190,24,93,.35);border-radius:10px;">
  <p style="font-size:15px;color:#fcd7e0;margin:0 0 8px;font-weight:700;">▶ 近年、若い男性受け・おじ様向けにも需要ニーズが増加中のスタイル — YouTube で検索：</p>
  <div style="display:flex;flex-wrap:wrap;gap:8px;">
    <a href="https://www.youtube.com/results?search_query=%E3%82%B3%E3%82%B9%E3%83%97%E3%83%AC+%E3%83%9F%E3%83%8B%E3%82%B9%E3%82%AB+%E5%AD%A6%E5%9C%92+%E3%82%AD%E3%83%A3%E3%83%90" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:20px;background:#be185d;color:#fff;font-size:15px;font-weight:800;text-decoration:none;white-space:nowrap;">
      <span style="font-size:15px;">▶</span> コスプレ　ミニスカ　学園　キャバ
    </a>
  </div>
</div>

<p style="opacity:.82;font-size:15px;line-height:1.7;margin-bottom:12px;color:#fecdd3;">いずれも Google 検索窓口へのリンクです。時給は目安であり、各店の求人票を必ずご確認ください。</p>
<?php foreach ($aruaru_caba_sections as $caba_sec):
    $sec_id   = (string)($caba_sec['id']    ?? '');
    $sec_rows = $aruaru_caba_section_rows[$sec_id] ?? [];
?>
<h3 id="<?= lady_h($sec_id) ?>"><?= lady_h((string)($caba_sec['title'] ?? '')) ?></h3>
<?php if ($sec_id === 'aruaru-caba-tokyo23'): ?>
<p style="opacity:.88;font-size:15px;line-height:1.7;margin-bottom:10px;color:#fde68a;"><?= lady_h($aruaru_caba['disclaimer'] ?? '') ?></p>
<?php endif; ?>
<p style="opacity:.65;font-size:15px;margin-bottom:12px;color:#cbd5e1;">📅 リスト更新: <?= lady_h((string)($aruaru_caba['updated_at'] ?? '—')) ?> — TTL 約7日</p>
<div class="ox">
<table>
<thead><tr>
  <th style="width:48px;">順位</th><th>エリア</th><th>エリア名称・特徴</th><th>時給目安帯</th><th>補足</th><th style="width:110px;">検索</th>
</tr></thead>
<tbody><?php aruaru_caba_render_table_rows($sec_rows); ?></tbody>
</table>
</div>
<?php endforeach; ?>
</div>

<!-- 熟女キャバ TOP50 -->
<div class="card" id="jukujo-corner">
<h2>✨ 熟女キャバレー・熟女キャバクラ 全国・高級帯目安 TOP50</h2>
<p style="opacity:.88;font-size:15px;line-height:1.7;margin-bottom:8px;color:#fecdd3;"><?= lady_h($aruaru_jukujo_caba['disclaimer'] ?? '') ?></p>
<p style="opacity:.65;font-size:15px;margin-bottom:14px;color:#cbd5e1;">📅 リスト更新（キャッシュ）: <?= lady_h((string)($aruaru_jukujo_caba['updated_at'] ?? '—')) ?> — 「高級順」はエリアの目安インデックスによる並びで、特定店の格付けではありません。</p>
<div class="ox">
<table>
<thead><tr style="background:#1c0a14;text-align:left;">
  <th style="width:48px;color:#fb7185;">順位</th><th style="color:#fb7185;">エリア</th><th style="color:#fb7185;">エリア名称・特徴</th><th style="color:#fb7185;">高級帯目安</th><th style="color:#fb7185;">補足</th><th style="color:#fb7185;width:110px;">検索</th>
</tr></thead>
<tbody>
<?php foreach (($aruaru_jukujo_caba['rows'] ?? []) as $jk):
    $ju = lady_h((string)($jk['url'] ?? '#')); ?>
<tr style="border-bottom:1px solid #4c1d45;">
  <td style="padding:6px 8px;text-align:center;font-weight:bold;color:#fb7185;"><?= lady_h((string)($jk['rank']    ?? '')) ?></td>
  <td style="padding:6px 8px;color:#fef3c7;"><?= lady_h((string)($jk['area']    ?? '')) ?></td>
  <td style="padding:6px 8px;"><?= lady_h((string)($jk['title']   ?? '')) ?></td>
  <td style="padding:6px 8px;opacity:.95;"><?= lady_h((string)($jk['lux_band'] ?? '')) ?></td>
  <td style="padding:6px 8px;opacity:.85;"><?= lady_h((string)($jk['pickup']   ?? '')) ?></td>
  <td style="padding:6px 8px;"><a href="<?= $ju ?>" target="_blank" rel="noopener noreferrer" style="color:#fb7185;font-weight:bold;">Google で検索</a></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>

<!-- Cron 設定案内 -->
<div class="cron-box">
  📡 <strong>★ Cron 自動更新設定（毎日・これ1行だけ設定すれば全て完了）：</strong><br><br>

  <span style="color:#34d399;font-weight:700;font-size:15px;">▶ LOLIPOP Cron に設定するコマンド（1つだけ）</span><br>
  <code>0 5 * * * php <?= lady_h(__FILE__) ?> --cron-all &gt;&gt; /home/USERNAME/logs/aruaru-lady-cron.log 2&gt;&amp;1</code>
  <br><br>

  <span style="opacity:.85;font-size:15px;color:#e2e8f0;">
    <strong style="color:#67e8f9;">--cron-all が毎日まとめて実行する処理（全4項目・毎日更新）：</strong><br>
    　[1/4] キャバクラランキングキャッシュ（<code>aruaru-caba-ranking-cache.json</code>）<br>
    　[2/4] 熟女キャバランキングキャッシュ（<code>aruaru-jukujo-caba-ranking-cache.json</code>）<br>
    　[3/4] TVチャットレディ【グループチャット（パーティーチャット）版】ランキングキャッシュ（<code>aruaru-tvchat-group-ranking-cache.json</code>）<br>
    　[4/4] TVチャットレディ【通常版】ランキングキャッシュ（<code>aruaru-tvchat-normal-ranking-cache.json</code>）<br>
    <br>
    ※ 全キャッシュ TTL を毎日（24時間）に統一済み。<br>
    ※ Webアクセス時はキャッシュを返すため応答速度に影響しません。<br>
    ※ ログは <code>/home/USERNAME/logs/aruaru-lady-cron.log</code> に記録されます。
  </span>
</div>

<div class="notice" style="margin-top:32px;">
  <span>🔗</span>
  <span>IT技術・プログラミング・英会話など女性向け以外の情報は</span>
  <a href="https://audiocafe.tokyo/aruaru" target="_blank" rel="noopener noreferrer">audiocafe.tokyo/aruaru</a>
  <span>をご覧ください。</span>
</div>

</div>

<div class="card" style="border-color:#4338ca;margin-top:32px;" id="policy-sougeishaxi">
  <h2 style="color:#a5b4fc;">🚗 関連政策提案：無料送迎車の半公共タクシー化</h2>

  <h3 style="color:#c7d2fe;margin:0 0 8px;">概念：半公共送迎タクシー制度</h3>
  <p style="font-size:15px;line-height:1.85;color:#e2e8f0;margin-bottom:20px;">
    キャバクラ等の無料送迎車を、空席があれば一般市民も乗れる
    <strong style="color:#c7d2fe;">「相乗り型半公共交通」</strong>として機能させる構想です。
    病院・スーパーへの買い物など日常的な送迎にも活用でき、交通空白地帯の補完手段となり得ます。
    運転手には店からの給与に加え、<strong style="color:#c7d2fe;">日本全国の市区町村から半公務員手当</strong>が支給される仕組みとすることで、
    安定した収入と社会的役割を両立できる可能性があります。
  </p>

  <h3 style="color:#86efac;margin:0 0 8px;font-size:16px;">✅ メリット</h3>
  <ul style="font-size:15px;line-height:2.1;padding-left:1.4rem;color:#d1fae5;margin-bottom:20px;">
    <li>深夜・郊外など交通空白地帯をカバー</li>
    <li>運転手の収入が安定（店＋自治体手当の二重収入）</li>
    <li>病院・買い物難民の高齢者救済</li>
    <li>車両の遊休時間を社会還元</li>
  </ul>

  <h3 style="color:#fca5a5;margin:0 0 8px;font-size:16px;">⚠️ 課題と論点</h3>
  <ul style="font-size:15px;line-height:2.1;padding-left:1.4rem;color:#fecdd3;margin-bottom:20px;">
    <li>道路運送法との整合性（白タク規制）</li>
    <li>自治体ごとの財源・予算手当</li>
    <li>乗客の安全管理・身元確認</li>
    <li>優先度のルール（店の業務客 vs 一般市民）</li>
  </ul>

  <h3 style="color:#a5b4fc;margin:0 0 8px;font-size:16px;">📋 近い事例</h3>
  <p style="font-size:15px;color:#a5b4fc;margin-bottom:8px;">などの事例もある様です：</p>
  <ul style="font-size:15px;line-height:2.1;padding-left:1.4rem;color:#c7d2fe;margin-bottom:16px;">
    <li><strong>デマンド型乗合タクシー</strong>　— 過疎地で既に実施中。予約に応じて乗合運行する公共交通の仕組み。</li>
    <li><strong>ライドシェア</strong>　— 2024年から一部解禁。自家用車を使った有償旅客運送が条件付きで認められ始めた。</li>
    <li><strong>スクールバスの地域開放</strong>　— 登下校時間外に地域住民が利用できるよう開放する取り組み。</li>
  </ul>

  <p style="font-size:15px;line-height:1.7;color:#94a3b8;">
    ※ 実現には道路運送法（白タク規制）の整備・自治体ごとの条例・財源確保・安全管理ルールの策定が必要です。
    既存のデマンド交通やライドシェア制度との連携・法改正が検討課題となります。
  </p>

  <hr style="border:none;border-top:1px solid rgba(148,163,184,.2);margin:20px 0;">

  <h3 style="color:#fcd34d;margin:0 0 10px;font-size:16px;">🍺 お客様送迎・昼飲み需要・販売品目に関する提案</h3>
  <p style="font-size:15px;line-height:1.85;color:#e2e8f0;margin-bottom:14px;">
    キャバレー・キャバクラでは従業員向けの自動車での<strong style="color:#fcd34d;">無料送迎サービス</strong>がある所が多い様ですので、
    <strong style="color:#fcd34d;">お客様向けにも無料の送迎サービス</strong>があった方が親切で良いと思われます。<br>
    またお店も、朝からや昼からでも楽しく飲んで気分転換したい方の<strong style="color:#fcd34d;">需要やニーズも増加中</strong>の様です。
  </p>

  <h3 style="color:#7dd3fc;margin:0 0 8px;font-size:15px;">🥤 飲料・販売品目の拡充提案</h3>
  <p style="font-size:15px;line-height:1.85;color:#e2e8f0;margin-bottom:14px;">
    キャバレー・キャバクラや、今後は<strong style="color:#7dd3fc;">駅のKIOSK・キヨスクや自動販売機</strong>でも、
    ウイスキーやビールの他に、<strong style="color:#7dd3fc;">ASAHI の青い缶の無添加のノンアルコールビール</strong>など
    販売や提供などの需要やニーズが増加中の様です。
  </p>

  <h3 style="color:#f9a8d4;margin:0 0 8px;font-size:15px;">💊 心臓ケア商品の優先販売提案</h3>
  <p style="font-size:15px;line-height:1.85;color:#e2e8f0;margin-bottom:8px;">
    駅のキオスクや自動販売機、キャバレー・キャバクラなどのお店でも、
    心臓の薬の<strong style="color:#f9a8d4;">「救心」</strong>や、
    心臓に良いサプリメントとして<strong style="color:#f9a8d4;">「コエンザイムQ10」</strong>などを
    <strong style="color:#f9a8d4;">優先的に販売</strong>して欲しいです。
  </p>
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

<footer>
  <p>© audiocafe.tokyo / aruaru-lady — 掲載情報はGoogle検索窓口へのリンクです。各店・各媒体の公式情報を必ずご確認ください。</p>
  <p style="margin-top:6px;">年齢制限・法令・各都道府県の条例に必ず従ってください。</p>
</footer>
</body>
</html>

