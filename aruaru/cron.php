<?php
/**
 * audiocafe.tokyo/aruaru/cron.php
 * LOLIPOPのCronから呼び出す専用ファイル
 *
 * LOLIPOP Cron設定（これ1行だけ）:
 *   /usr/bin/php /home/users/サーバーID/audiocafe.tokyo/aruaru/cron.php
 *
 * 実行内容 (--cron-all)：全8項目・毎日自動更新
 *   [1/8] AIテクノロジーランキング
 *   [2/8] 学習価格情報
 *   [3/8] AI学習コメント
 *   [4/8] 英会話ランキング
 *   [5/8] 楽天モバイル基本料金
 *   [6/8] 楽天モバイル国際通話
 *   [7/8] プラチナバンド・衛星
 *   [8/8] おすすめスマホ・キャンペーン判定
 *         ├ ichiyen-startページ存在確認
 *         ├ 1円スマホ系テキストマッチ
 *         └ 失敗時は前回キャッシュ or campaign_active=true（安全側）
 */
$argv = ['cron.php', '--cron-all'];
require __DIR__ . '/index.php';
