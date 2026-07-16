<?php
/**
 * audiocafe.tokyo/aruaru-lady/cron.php
 * LOLIPOPのCronから呼び出す専用ファイル
 *
 * LOLIPOP Cron設定（これ1行だけ）:
 *   /usr/bin/php /home/users/サーバーID/audiocafe.tokyo/aruaru-lady/cron.php
 *
 * 実行内容 (--cron-all)：全6項目・毎日自動更新
 *   [1/6] キャバクラランキング
 *   [2/6] 熟女キャバランキング
 *   [3/6] 楽天モバイル基本料金
 *   [4/6] 楽天モバイル国際通話
 *   [5/6] プラチナバンド・衛星
 *   [6/6] おすすめスマホ・キャンペーン判定
 *         ├ ichiyen-startページ存在確認
 *         ├ 1円スマホ系テキストマッチ
 *         └ 失敗時は前回キャッシュ or campaign_active=true（安全側）
 */
$argv = ['cron.php', '--cron-all'];
require __DIR__ . '/index.php';
