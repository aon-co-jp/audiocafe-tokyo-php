<?php
/** 18言語共通の言語切替ナビ。$current に現在の言語コードを入れてincludeする。
 *  'at'(オーストリア)と'ch'(スイス)はいずれもドイツ語が公用語の一つ
 *  (スイスは独/仏/伊/ロマンシュ語が公用語で、最も広く使われるドイツ語を
 *  代表バリアントとして採用)のため、'de'と同一の翻訳内容を国旗・ラベル
 *  のみ変えて別ファイルで提供する(架空の別言語は作らない)。 */
declare(strict_types=1);
$languages = [
    'ja'    => ['label' => '🇯🇵 日本語',              'href' => '/rakuten-mobile/'],
    'en'    => ['label' => '🇺🇸 English',             'href' => '/rakuten-mobile/index-en.php'],
    'en-gb' => ['label' => '🇬🇧 English (UK)',        'href' => '/rakuten-mobile/index-en-gb.php'],
    'it'    => ['label' => '🇮🇹 Italiano',            'href' => '/rakuten-mobile/index-it.php'],
    'de'    => ['label' => '🇩🇪 Deutsch',             'href' => '/rakuten-mobile/index-de.php'],
    'at'    => ['label' => '🇦🇹 Deutsch (Österreich)', 'href' => '/rakuten-mobile/index-at.php'],
    'ch'    => ['label' => '🇨🇭 Deutsch (Schweiz)',    'href' => '/rakuten-mobile/index-ch.php'],
    'fr'    => ['label' => '🇫🇷 Français',            'href' => '/rakuten-mobile/index-fr.php'],
    'ru'    => ['label' => '🇷🇺 Русский',             'href' => '/rakuten-mobile/index-ru.php'],
    'uk'    => ['label' => '🇺🇦 Українська',          'href' => '/rakuten-mobile/index-uk.php'],
    'ar'    => ['label' => '🇸🇦 العربية',             'href' => '/rakuten-mobile/index-ar.php'],
    'fa'    => ['label' => '🇮🇷 فارسی',              'href' => '/rakuten-mobile/index-fa.php'],
    'ko'    => ['label' => '🇰🇷 한국어',               'href' => '/rakuten-mobile/index-ko.php'],
    'zh-cn' => ['label' => '🇨🇳 简体中文',            'href' => '/rakuten-mobile/index-zh-cn.php'],
    'zh-tw' => ['label' => '🇹🇼 繁體中文',            'href' => '/rakuten-mobile/index-zh-tw.php'],
    'es'    => ['label' => '🇪🇸 Español',            'href' => '/rakuten-mobile/index-es.php'],
    'tl'    => ['label' => '🇵🇭 Filipino',            'href' => '/rakuten-mobile/index-tl.php'],
    'he'    => ['label' => '🇮🇱 עברית',              'href' => '/rakuten-mobile/index-he.php'],
    // ↑ 18言語すべて実装済み(2026-08-04、ヘブライ語を追加)。
];
$current = $current ?? 'ja';
?>
<div class="lang-switch">
<?php foreach ($languages as $code => $l): if ($code === $current) continue; ?>
  <a href="<?= htmlspecialchars($l['href'], ENT_QUOTES, 'UTF-8') ?>"><?= $l['label'] ?></a>
<?php endforeach; ?>
  <a href="https://audiocafe.tokyo/aruaru/">📊 aruaru（IT技術情報）</a>
</div>
