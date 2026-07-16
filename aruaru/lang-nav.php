<?php
/** 11言語共通の言語切替ナビ。$current に現在の言語コードを入れてincludeする。 */
declare(strict_types=1);
$languages = [
    'ja'    => ['label' => '🇯🇵 日本語',    'href' => '/aruaru/'],
    'en'    => ['label' => '🇺🇸 English',   'href' => '/aruaru/index-en.php'],
    'ko'    => ['label' => '🇰🇷 한국어',     'href' => '/aruaru/index-ko.php'],
    'zh-cn' => ['label' => '🇨🇳 简体中文',  'href' => '/aruaru/index-zh-cn.php'],
    'zh-tw' => ['label' => '🇹🇼 繁體中文',  'href' => '/aruaru/index-zh-tw.php'],
    'ru'    => ['label' => '🇷🇺 Русский',   'href' => '/aruaru/index-ru.php'],
    'uk'    => ['label' => '🇺🇦 Українська','href' => '/aruaru/index-uk.php'],
    'de'    => ['label' => '🇩🇪 Deutsch',   'href' => '/aruaru/index-de.php'],
    'it'    => ['label' => '🇮🇹 Italiano',  'href' => '/aruaru/index-it.php'],
    'fr'    => ['label' => '🇫🇷 Français',  'href' => '/aruaru/index-fr.php'],
    'ar'    => ['label' => '🇸🇦 العربية',   'href' => '/aruaru/index-ar.php'],
    'fa'    => ['label' => '🇮🇷 فارسی',    'href' => '/aruaru/index-fa.php'],
    // ↑ 12言語すべて実装済み(2026-07-15)。
];
$current = $current ?? 'ja';
?>
<div class="lang-switch">
<?php foreach ($languages as $code => $l): if ($code === $current) continue; ?>
  <a href="<?= htmlspecialchars($l['href'], ENT_QUOTES, 'UTF-8') ?>"><?= $l['label'] ?></a>
<?php endforeach; ?>
  <a href="https://aruaru.tokyo/">🎲 aruaru.tokyo</a>
</div>
