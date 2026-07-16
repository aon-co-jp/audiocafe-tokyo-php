<?php
/** 11言語共通の言語切替ナビ(aruaru-lady用)。$current に現在の言語コードを入れてincludeする。 */
declare(strict_types=1);
$languages = [
    'ja'    => ['label' => '🇯🇵 日本語',    'href' => '/aruaru-lady/'],
    'en'    => ['label' => '🇺🇸 English',   'href' => '/aruaru-lady/index-en.php'],
    'ko'    => ['label' => '🇰🇷 한국어',     'href' => '/aruaru-lady/index-ko.php'],
    'zh-cn' => ['label' => '🇨🇳 简体中文',  'href' => '/aruaru-lady/index-zh-cn.php'],
    'zh-tw' => ['label' => '🇹🇼 繁體中文',  'href' => '/aruaru-lady/index-zh-tw.php'],
    'ru'    => ['label' => '🇷🇺 Русский',   'href' => '/aruaru-lady/index-ru.php'],
    'uk'    => ['label' => '🇺🇦 Українська','href' => '/aruaru-lady/index-uk.php'],
    'de'    => ['label' => '🇩🇪 Deutsch',   'href' => '/aruaru-lady/index-de.php'],
    'it'    => ['label' => '🇮🇹 Italiano',  'href' => '/aruaru-lady/index-it.php'],
    'fr'    => ['label' => '🇫🇷 Français',  'href' => '/aruaru-lady/index-fr.php'],
    'ar'    => ['label' => '🇸🇦 العربية',   'href' => '/aruaru-lady/index-ar.php'],
    'fa'    => ['label' => '🇮🇷 فارسی',     'href' => '/aruaru-lady/index-fa.php'],
];
$current = $current ?? 'ja';
?>
<div class="lang-switch">
<?php foreach ($languages as $code => $l): if ($code === $current) continue; ?>
  <a href="<?= htmlspecialchars($l['href'], ENT_QUOTES, 'UTF-8') ?>"><?= $l['label'] ?></a>
<?php endforeach; ?>
  <a href="https://aruaru.tokyo/">🎲 aruaru.tokyo</a>
</div>
