<?php
/**
 * check.php — aruaru/index.php の500エラー原因を画面に表示する診断ツール
 * 使い方: このファイルを aruaru/ フォルダに置き、
 *         https://audiocafe.tokyo/aruaru/check.php にアクセス
 * 確認後は必ず削除してください（エラー内容が外部に見えるため）。
 */

// すべてのエラーを画面に表示
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

header('Content-Type: text/plain; charset=UTF-8');

echo "=== PHP 環境診断 ===\n";
echo "PHP バージョン: " . PHP_VERSION . "\n";
echo "PHP_VERSION_ID: " . PHP_VERSION_ID . "  (80000以上が必要)\n";
echo "OS: " . PHP_OS . "\n";
echo "\n";

echo "=== 拡張モジュール ===\n";
foreach (['json', 'mbstring', 'curl', 'openssl', 'zip', 'fileinfo'] as $ext) {
    echo sprintf("  %-10s : %s\n", $ext, extension_loaded($ext) ? 'OK' : '★なし★');
}
echo "\n";

echo "=== ファイル・ディレクトリ ===\n";
$dir = __DIR__;
echo "現在のディレクトリ: {$dir}\n";
echo "書き込み可能: " . (is_writable($dir) ? 'OK' : '★不可★') . "\n";
$idx = $dir . '/index.php';
echo "index.php 存在: " . (file_exists($idx) ? 'OK (' . filesize($idx) . ' bytes)' : '★なし★') . "\n";
echo "\n";

echo "=== index.php 構文チェック ===\n";
$out = [];
$ret = 0;
$phpbin = PHP_BINARY ?: 'php';
@exec(escapeshellarg($phpbin) . ' -l ' . escapeshellarg($idx) . ' 2>&1', $out, $ret);
if ($out) {
    echo implode("\n", $out) . "\n";
} else {
    echo "(php -l を実行できませんでした)\n";
}
echo "\n";

echo "=== index.php を実際に読み込んでエラーを捕捉 ===\n";
echo "↓ ここから下にエラーが出れば、それが500の原因です\n";
echo str_repeat('-', 50) . "\n";

// 出力バッファでHTMLを捨てて、エラーだけ見る
ob_start();
try {
    // 致命的エラーも捕捉
    register_shutdown_function(function () {
        $e = error_get_last();
        if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            // バッファを捨ててエラーだけ表示
            while (ob_get_level() > 0) { ob_end_clean(); }
            echo "\n★★★ FATAL ERROR 検出 ★★★\n";
            echo "種類  : " . $e['type'] . "\n";
            echo "内容  : " . $e['message'] . "\n";
            echo "ファイル: " . $e['file'] . "\n";
            echo "行番号: " . $e['line'] . "\n";
        }
    });
    include $idx;
    while (ob_get_level() > 0) { ob_end_clean(); }
    echo "\n（致命的エラーなし — index.php は最後まで実行されました）\n";
} catch (Throwable $ex) {
    while (ob_get_level() > 0) { ob_end_clean(); }
    echo "\n★★★ 例外 検出 ★★★\n";
    echo "種類  : " . get_class($ex) . "\n";
    echo "内容  : " . $ex->getMessage() . "\n";
    echo "ファイル: " . $ex->getFile() . "\n";
    echo "行番号: " . $ex->getLine() . "\n";
    echo "\nスタックトレース:\n" . $ex->getTraceAsString() . "\n";
}
