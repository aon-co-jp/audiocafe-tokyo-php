<?php
/**
 * audiocafe.tokyo/aruaru/cron.php
 * 技術ランキング + 学習価格の毎朝更新 (--cron-tech)。
 *
 * 2026-10-08 変更: 旧版は --cron-all を渡していたが、index.php が include する
 * rakuten-mobile/index.php の --cron-all が先に走って exit するため、
 * 技術ランキング/学習価格が 2026-06-01 から更新されていなかった。
 * 楽天モバイル/doda/英会話は Rust版 (audiocafe-cron-rust.service) が担当する。
 * (旧: LOLIPOPのCronから呼び出す専用ファイル。現在は ConoHA VPS の
 *  systemd タイマー audiocafe-cron-php.timer から実行される)
 */
ob_start();   // index.php がページHTMLを先に出力するため、--cron-tech 側で破棄する
$argv = ['cron.php', '--cron-tech'];
require __DIR__ . '/index.php';
