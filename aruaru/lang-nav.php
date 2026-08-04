<?php
/** 18言語共通の言語切替ナビ。$current に現在の言語コードを入れてincludeする。
 *  'at'(オーストリア)と'ch'(スイス)はいずれもドイツ語が公用語の一つ
 *  のため、'de'と同一の翻訳内容を国旗・ラベルのみ変えて別ファイルで
 *  提供する(架空の別言語は作らない、rakuten-mobileと同じ方針)。 */
declare(strict_types=1);
$languages = [
    'ja'    => ['label' => '🇯🇵 日本語',              'href' => '/aruaru/'],
    'en'    => ['label' => '🇺🇸 English',             'href' => '/aruaru/index-en.php'],
    'en-gb' => ['label' => '🇬🇧 English (UK)',        'href' => '/aruaru/index-en-gb.php'],
    'ko'    => ['label' => '🇰🇷 한국어',               'href' => '/aruaru/index-ko.php'],
    'zh-cn' => ['label' => '🇨🇳 简体中文',            'href' => '/aruaru/index-zh-cn.php'],
    'zh-tw' => ['label' => '🇹🇼 繁體中文',            'href' => '/aruaru/index-zh-tw.php'],
    'ru'    => ['label' => '🇷🇺 Русский',             'href' => '/aruaru/index-ru.php'],
    'uk'    => ['label' => '🇺🇦 Українська',          'href' => '/aruaru/index-uk.php'],
    'de'    => ['label' => '🇩🇪 Deutsch',             'href' => '/aruaru/index-de.php'],
    'at'    => ['label' => '🇦🇹 Deutsch (Österreich)', 'href' => '/aruaru/index-at.php'],
    'ch'    => ['label' => '🇨🇭 Deutsch (Schweiz)',    'href' => '/aruaru/index-ch.php'],
    'it'    => ['label' => '🇮🇹 Italiano',            'href' => '/aruaru/index-it.php'],
    'fr'    => ['label' => '🇫🇷 Français',            'href' => '/aruaru/index-fr.php'],
    'es'    => ['label' => '🇪🇸 Español',            'href' => '/aruaru/index-es.php'],
    'tl'    => ['label' => '🇵🇭 Filipino',            'href' => '/aruaru/index-tl.php'],
    'ar'    => ['label' => '🇸🇦 العربية',             'href' => '/aruaru/index-ar.php'],
    'fa'    => ['label' => '🇮🇷 فارسی',              'href' => '/aruaru/index-fa.php'],
    'he'    => ['label' => '🇮🇱 עברית',              'href' => '/aruaru/index-he.php'],
    // ↑ 18言語すべて実装済み(2026-08-04、rakuten-mobileの17言語構成に
    //   ヘブライ語を追加。at/ch(独語共用)含む)。
];
$current = $current ?? 'ja';
?>
<div class="lang-switch">
<?php foreach ($languages as $code => $l): if ($code === $current) continue; ?>
  <a href="<?= htmlspecialchars($l['href'], ENT_QUOTES, 'UTF-8') ?>"><?= $l['label'] ?></a>
<?php endforeach; ?>
  <a href="https://aruaru.tokyo/">🎲 aruaru.tokyo</a>
</div>
