<?php
// ============================================================================
// ★★★ FIX VERSION: 2026-07-16-v41 ★★★
// 修正項目:
//   1. [v41-BUG / 方針転換] DD67000等のYouTube検索結果ボタンで無関係な動画が
//      再生されるBUGが、v39/v40のjina.ai対策(ヘッダー追加)後も再発した。
//      原因調査の結果、(a) v40のヘッダー修正が実は本番に反映されていなかった、
//      (b) 反映後に再検証するとjina.ai自体が403から401 Unauthorizedへ挙動を
//      変えており、ヘッダーで回避しても今度は認証エラーで弾かれることが判明した。
//      jina.aiスクレイプ経由での「それらしい動画を推測して自動再生」という
//      方式自体がjina.ai側の仕様変更に永続的に追随し続けねばならず根本的に
//      不安定と判断し、ユーザー指示によりfetchAndCollect()内の検索URL処理を
//      廃止。検索URLは他の「動画ページでないURL」(ホームページ等)と全く同じ
//      共通分岐で扱うようにし、YouTube自身の検索結果ページへの直接画面遷移に
//      統一した(スクレイプ・推測を挟まないため、無関係な動画が再生される
//      可能性がそもそも存在しない)。
//   2. 既知の残課題として明記: NEXT/ランダムプール経路(fetchSearchResultIds、
//      「次へ」ボタン連打や統合ランダム再生時に検索結果を追加取得する経路)は
//      今回のスコープ外——jina.ai失敗時にDEFAULT_BG_VIDEO_ID(無関係な固定動画)
//      へフォールバックする経路が残っている。次回対応が必要。
// ----------------------------------------------------------------------------
// ★★★ (旧) FIX VERSION: 2026-07-16-v40 ★★★
// 修正項目:
//   1. [v40-BUG / 根本原因] DD67000に限らず「YouTube検索結果の動画が全て無関係な
//      動画に差し替わる」BUGの真因を特定・修正。r.jina.ai がYouTube検索結果ページを
//      スクレイプする際、通常のfetch(ヘッダー無し)だとYouTube側から403 Forbiddenを
//      返され、動画へのリンクを一切含まない空のページ(ナビ画像のみ)しか取得できなく
//      なっていた(v37/v38時点では正常だったが、その後jina.ai側の挙動が変化したと
//      見られる)。これによりcollectRelevantSearchIds/collectSearchResultIdsが
//      常に0件となり、無関係な動画へのフォールバックが「全ての検索結果ボタン」で
//      発生していた(DD67000のハイフン表記ゆれは副次的な問題で、本質はこちら)。
//      修正: 全てのr.jina.ai向けfetch()に `X-Respond-With: markdown` ヘッダーを
//      追加(jina.ai公式のCORSプリフライトで許可されていることを確認済み)。これに
//      より403を回避し、実際の検索結果(動画タイトル・リンクとも正常)を取得できる
//      ようになった。
// ----------------------------------------------------------------------------
// ★★★ (旧) FIX VERSION: 2026-07-16-v39 ★★★
// 修正項目:
//   1. [v39-BUG] 「JBL DD67000」ボタン等、検索ワードに区切り文字が無いのに実際の
//      動画タイトル側が「DD-67000」のようにハイフン/スペース入り型番表記になっている
//      場合、collectRelevantSearchIds()/isTitleRelevant() の部分一致が失敗し、
//      関連動画0件→無関係な動画(おすすめ・Shorts棚等)への全件フォールバックが
//      発生していた(v37/v38の関連性フィルタ自体は機能していたが、区切り文字の
//      表記ゆれを吸収していなかった)。
//      修正: stripSeparators() でハイフン・アンダースコア・空白(全角含む)を除去して
//      から比較するようにし、"DD67000" と "DD-67000" のような表記ゆれを許容する。
// ----------------------------------------------------------------------------
// ★★★ (旧) FIX VERSION: 2026-06-24-v38 ★★★
// 修正項目:
//   1. [v38] YouTube検索結果リンク(youtube.com/results?search_query=...)で
//      「検索ワードと無関係な動画が再生される」BUGを全経路で根治:
//      - 背景再生で検索URLを扱う経路はすべて fetchAndCollect()/fetchSearchResultIds()
//        を通る(シリーズボタン switchSeries→playAtUrlIndex / 起動イントロ
//        playIntroStep / NEXT・統合プール fetchSearchResultIds / 再検索
//        redoSearchOnce)。この2関数の検索結果抽出を関連性フィルタに統一。
//        ※ 言語カードの cardLinks 検索リンクは元々 YouTube検索ページへ直接遷移する
//          だけで背景再生しないため対象外(=もともと無関係動画再生は起きない)。
//      - collectRelevantSearchIds() を jina.ai の出力形式差に強い2段判定へ強化:
//          Pass A: markdown リンク [タイトル](URL) のリンクテキスト(=タイトル)に
//                  検索ワードのトークンが含まれるIDだけを出現順で採用(最も正確)。
//          Pass B: Aが0件のとき、各動画リンク直前の近傍テキスト(120字)に
//                  トークンが出るIDを採用(タイトルをリンクテキストにしない形式向け。
//                  次の結果のタイトル誤検出を避けるため前方のみ参照)。
//          A・Bとも0件 → collectSearchResultIds()→collectYouTubeIds() に段階
//          フォールバックするため従来動作からの後退なし。
//      - これにより「おすすめ動画・Shorts棚・広告・サイドバー候補」のIDが除外され、
//        DD67000 等の検索結果は実際に関連する上位動画のみ再生される。
// ----------------------------------------------------------------------------
// ★★★ (旧) FIX VERSION: 2026-06-24-v37 ★★★
// 修正項目:
//   1. [v37-BUG] シリーズの YouTube検索結果リンク(DD67000 等
//      youtube.com/results?search_query=...)で「検索ワードと無関係な動画が
//      再生される」BUGを修正:
//      - 根本原因: 検索結果ページを jina.ai でスクレイプし collectSearchResultIds()
//        が watch?v=/youtu.be/shorts/embed のIDを「ドキュメント出現順」で全部拾うが、
//        そのページには本来の検索結果に加え「おすすめ動画・Shorts棚・広告・サイドバー
//        候補」のIDも大量に含まれる。その上位2本を再生していたため、DD67000 と無関係な
//        動画が頻繁に再生されていた。再生後タイトルで判定する beginSearchRelevanceWatch
//        は後追い(1回のみ)で取りこぼしていた。
//      - 修正: collectRelevantSearchIds(text, query) を新設。jina markdown のリンク
//        [タイトル](URL) を解析し、そのリンク自身のタイトルに検索ワードのトークンが
//        含まれるIDだけを出現順で残す(無関係動画を抽出段階で排除)。
//        ・fetchAndCollect() の検索URL分岐 → 関連抽出を最優先で適用
//          (シリーズボタン switchSeries→playAtUrlIndex / イントロ playIntroStep /
//           再検索 redoSearchOnce はすべてこの経路を通る)
//        ・fetchSearchResultIds() (NEXT/ランダムプール経路) にも同適用
//        ・関連IDが0本なら collectSearchResultIds()→collectYouTubeIds() に
//          段階フォールバックするため、従来動作からの後退は無い。
// ----------------------------------------------------------------------------
// ★★★ (旧) FIX VERSION: 2026-06-12-v36 ★★★
// 修正項目:
//   1. [v36-BUG1] 「最初に自動再生される動画だけ音量調整が効かない」BUGの再修正:
//      - v35の残存不具合: 「音を出したい意思」と「実際にアンミュート成立」を
//        単一フラグ __audioUnlocked で兼用していた。起動直後はプレイヤーAPI
//        (unMute/setVolume)がまだ使えず applyUnmuteFromSlider() は失敗するのに、
//        スライダーinputハンドラ等が __audioUnlocked=true を無条件に立てていたため、
//        「アンロック済みのつもりで実際はミュートのまま」状態に固まっていた。
//        ページを開いて即スライダーを触る操作で再現し、最初の動画だけ無音/無反応、
//        2本目以降(プレイヤーready後)は正常、という症状になっていた。
//      - 修正: フラグを2つに分離。
//          __wantAudio    = ユーザーが音を出したい意思(ジェスチャ発生で true)
//          __audioUnlocked = 実際に unMute/iframe差し替えが成立(確定で true)
//        (a) __audioUnlocked は applyUnmuteFromSlider()/syncVolumeFromSlider() が
//            実際に unMute できたときだけ立てる(失敗時は立てない)。
//        (b) syncVolumeFromSlider() のミュート判定ゲートを __wantAudio に変更。
//        (c) onPlayerReady は __wantAudio 表明済みなら ready時にアンミュート再適用。
//            起動直後にスライダーを触って当時未readyでも、ready後に音が戻る。
//            (一度でもユーザー操作があれば sticky activation で遅延unMute可)
//        (d) 新規プレイヤー/Shorts iframe の初期muteも __wantAudio で判定し、
//            意思表明後の2本目以降は最初から音付きで生成(再ジェスチャ不要)。
// ----------------------------------------------------------------------------
// ★★★ (旧) FIX VERSION: 2026-06-12-v35 ★★★
// 修正項目:
//   1. [v35-BUG1] 「最初の動画だけ音量調整が効かない」BUGの本修正:
//      - 根本原因: 起動時の最初の再生は autoplay=1 で開始されるが、ユーザー操作が
//        無い状態ではブラウザの自動再生ポリシーにより「ミュート時のみ自動再生可」と
//        なる。YouTubeプレイヤー/iframeはこの制約下で自動ミュートして再生するため、
//        API経由の unMute()/setVolume() を呼んでも音が戻らなかった。
//        さらに最初の動画が Shorts(iframe)の場合、syncVolumeFromSlider() は
//        YT.Player専用で iframe には一切効かず、スライダー操作も無反応だった。
//      - 修正:
//        (a) 最初のユーザー操作(クリック/タップ/キー)を全画面で1度だけ捕捉する
//            bindAudioUnlockOnce() を追加。そのジェスチャ内で unMute()+setVolume()
//            (iframeの場合は mute=1→mute=0 へ src 差し替え)を実行し音声をアンロック。
//        (b) 最初のプレイヤー/Shorts埋め込みは明示的に mute=1 で生成し、確実に自動再生。
//        (c) スライダー操作時に applyUnmuteFromSlider() を呼び iframe にも対応。
//        (d) 音量スライダーの初期値を 0→50 に変更(アンミュート時に無音になる問題を回避)。
//        (e) onPlayerReady / syncVolumeFromSlider はアンロック前はミュート維持し、
//            API状態と実状態の不一致を防止。
// ----------------------------------------------------------------------------
// ★★★ (旧) FIX VERSION: 2026-06-11-v30 ★★★
// 修正項目:
//   1. [v30] 「最初の動画が意図しない一時停止で固まり、画面クリック/PLAYでも
//            再生されない」「シリーズボタン後も一時停止できない」BUGを修正:
//            - 根本原因: v28/v29 で導入した「最初の1本を頭出し一時停止」機能。
//              コールド起動時はユーザー操作(ジェスチャ)がまだ無いため、
//              クロスオリジンのYouTube iframe に対しブラウザの自動再生ポリシーが
//              autoplay も親ページからの playVideo() もブロックし、
//              プレイヤーが再生不能状態のまま固まっていた。その壊れた状態が
//              後続(シリーズボタン)の再生・一時停止操作にも波及していた。
//            - 修正: 頭出し一時停止機能(__ytInitialPause 経路)を完全撤去。
//              最初の1本も通常どおり autoplay で再生(v27以前の挙動に復帰)。
//              startIntroSequence / instantiateYtBgPlayer / onPlayerReady /
//              injectEmbedIframe から該当分岐を削除。
//   2. [v30] YouTube検索結果で無関係な動画が再生されるBUGの追加修正:
//            - v29 の collectSearchResultIds は host(youtube.com)必須の正規表現で、
//              jina.ai が host無し/相対リンク(/watch?v=ID)で返すと空振りし、
//              従来の雑な collectYouTubeIds にフォールバックしていた。
//            - 修正: host非依存で watch?v=/youtu.be/shorts/embed をドキュメント
//              出現順(=検索上位順)に抽出。検索ステップは「上位2本を順番に」再生。
// ============================================================================
// ★★★ (旧) FIX VERSION: 2026-06-11-v29 ★★★
// 修正項目:
//   1. [v29-BUG] 最初に再生される動画が「頭出し一時停止」のまま固まり、
//               画面クリック/PLAYボタンでも再生再開できないBUGを修正:
//               - 原因: v28は新規プレイヤーを autoplay=1 で生成し
//                 onPlayerReady で pauseVideo() していたため、autoplay と
//                 pauseVideo() が競合し再生不能状態で固まっていた。
//               - 修正: __ytInitialPause 時は autoplay=0 でプレイヤーを生成し
//                 CUED(頭出し一時停止)状態にする。onPlayerReady では
//                 pauseVideo() を呼ばずフラグを落とすだけ。
//                 → ユーザー操作の playVideo() で確実に再生再開できる。
//   2. [v29-BUG] YouTube検索結果URLで無関係な動画が再生されるBUGを修正:
//               - 原因: collectYouTubeIds() はパターン優先順で並ぶうえ
//                 おすすめ/Shorts棚/広告の動画IDまで拾い、さらに
//                 そのプールから「ランダム2本」を選んでいたため
//                 検索クエリと無関係な動画が頻繁に再生されていた。
//               - 修正: collectSearchResultIds() を新設し watch?v=/youtu.be/
//                 shorts/embed の実リンクのみをページ出現順(=関連度順)で抽出。
//                 検索結果ステップは「上位2本を順番に」再生(ランダム廃止)。
//                 (空振り時は従来 collectYouTubeIds にフォールバック)
// ============================================================================
// ★★★ (旧) FIX VERSION: 2026-06-11-v28 ★★★
// 修正項目:
//   1. [v28] 起動時の最初の1本を頭出し一時停止で表示する仕様に変更:
//               - 新変数 __ytInitialPause (bool): true = 次に再生される1本だけ
//                 頭出しで一時停止する。startIntroSequence() で true にセット。
//               - onPlayerReady: __ytInitialPause が true なら pauseVideo() し
//                 フラグを false に落とす (2本目以降は通常自動再生)
//               - instantiateYtBgPlayer (loadVideoById分岐): フラグが true なら
//                 cueVideoById を使い再生を開始しない
//               - injectEmbedIframe (Shorts): フラグが true なら
//                 iframe の autoplay=0 で埋め込み、フラグを false に落とす
// ============================================================================
// ★★★ (旧) FIX VERSION: 2026-06-11-v26 ★★★
// 修正項目:
//   1. [v26] YouTube検索結果URLの再生を「ランダム2本→次URL/画面遷移」に変更:
//               - 旧: 検索結果を無制限にランダム再生し続ける
//               - 新: 検索結果からランダムに2本だけ再生し、
//                     2本目終了後は次のURL/ホームページへ自動的に画面遷移する
//               - 対象: イントロシーケンス中(playIntroStep)・
//                       シリーズ順次再生中(playAtUrlIndex)のいずれも同仕様
//               - 新変数: __srqIds(選択済みID), __srqPlayCount(消化本数),
//                         __srqOnDone(2本後コールバック)
//   2. [v26/v25] 起動時再生をSEARCH_SERIES先頭から順番再生に変更:
//               - buildIntroQueue: GT/CT特別処理を削除し、
//                 SEARCH_SERIES全シリーズのurls全本を上から順にキューに積むのみ
// [v24以前の修正項目]
//   1. [v24] シリーズボタン押下時の仕様をv20以前に復帰:
//               - ボタン押下 → 背景で再生 (v21の即画面遷移を廃止)
//               - 同じボタンを複数回押すたびに nextUrlIdx が +1 循環し、
//                 ストックされている全URLを順番に確実に再生できる
//               - タイトル(ytNowTitle)/URLリンク(ytNowUrl)クリック時は
//                 外部サイト/YouTube/その他ページへ同タブ画面遷移
//   2. [v24] setNowPlayingDisplay: URLクリック処理を全URL同タブ遷移に統一
//               - YouTube直接動画URL → buildNowPlayingNavUrl() でt=付与
//               - YouTube検索/Google検索/Facebook/その他 → そのまま遷移
//               - (旧) YouTube以外はtarget="_blank"別タブ → (新) 全URL同タブ遷移
//   3. [v24] ytNowTitle/ytNowUrlのtitle属性を汎用表現に変更:
//               「タップ → 動画/ページを開く」
// ============================================================================
// [過去バージョン履歴]
//   1. クリックハンドラ: __userClickedSeries で同押下判定
//   2. switchSeries: __onErrorRetryCount/ytBgFallbackUsed リセット
//   3. onPlayerError: 101/150エラー時に次URLへスキップ
//   4. playAtUrlIndex: jina.ai失敗時にフォールバック
//   5. skipToNext: tryPlayItem リトライロジック
//   6. [v10] イントロ再生順: 英語GT×1→日本語GT×1→CT1上2本→CT2上2本→操作パネル順次
//   7. [v11-BUG1] STOPボタンがiframe(Shorts等)で止まらないBUG修正
//   8. [v11-BUG2] NowPlayingタイトル・URLがGT内容に化けるBUG修正
//   9. [v11-BUG3] 画面クリックでiframe再生が止まらないBUG修正
//  10. [v11-UI] 操作パネル: BACK/REW/Play/Stop/FF/NEXT の6ボタン分離
//               REW=10秒巻き戻し / FF=10秒早送り / Loop+Status を別行
//  11. [v12-BUG4] スマホでYouTubeが途切れ途切れになるBUG修正
//               - vq:'hd2160' / setPlaybackQuality('hd2160') 削除 → ABR自動適応
//               - instantiateYtBgPlayer: ytBgPlayer存在時はloadVideoByIdで差し替え
//               - prepareHost: iframeモード以外はYTプレイヤーを破壊しない
//  12. [v13-BUG5] シリーズボタンのURLが動画ページでないホームページ
//               (ショップ/Google検索結果ページ等)の場合のBUG修正:
//               - 従来: jina.aiでページをスクレイプし、ページ内の無関係な動画を再生
//               - 修正: isPlayableVideoUrl()で判定し、
//                 (a) ボタン押下時(switchSeries) → 同じ画面のままそのページへ遷移(現ページを置換)
//                 (b) 自動再生/イントロ中(fetchAndCollect) → cb(null)でスキップ
//               - 壊れた 'ngFIhttps://...' プレフィックスURLも修正
//  13. [v14] Google検索URLを最新版で開く機能:
//               - switchSeries でGoogle検索URL(www.google.com/search?q=...)を検出
//               - q= パラメータの検索ワードのみ抽出し、sxsrf/ei/biw/bih/gs_lp等の
//                 古いセッションパラメータを除去したクリーンなURLで最新検索を実行
//               - udm= パラメータ(Google AI Mode等)は保持
//  14. [v15-BUG6] YouTube検索結果URL(youtube.com/results?search_query=...)のBUG修正:
//               - 従来: isPlayableVideoUrl()がyoutube.comを含む全URLを「再生可能」と判定
//                 → switchSeriesのlocation.assign()分岐をスルー
//                 → jina.aiでスクレイプするも検索結果HTMLからIDが取れず再生不能
//               - 修正: isPlayableVideoUrl()で /results を含むYouTube URLを除外
//                 → switchSeriesのwindow.location.assign()でブラウザ直接遷移
//               - 対象: UESGI上杉3件・その他youtube.com/results?search_query=... 全件
//  15. [v16] 背景チェックOFF/タイトルクリック時の画面遷移バグ修正:
//               - チェックを外した瞬間にYouTube等への遷移が起きなかったBUGを修正
//                 (window.open→window.location.assign / ytBgQueue非依存の実装に刷新)
//               - 遷移時に getCurrentTime() で再生位置(秒)を取得し
//                 YouTube URL末尾に ?t=秒 を付与して途中再生位置から開く
//               - タイトル(ytNowTitle)クリック時も同様に再生時間付きURLへ遷移
//                 (BGオン時は従来通り次へスキップ)
//               - NowPlayingのURLリンク(ytNowUrl)クリック時も再生時間を付与
//               - ヘルパー関数追加: getCurrentPlaybackSeconds() / buildYtUrlWithTime()
//                 / getCurrentYtUrlWithTime()
//  16. [v17] チェックを外しても文言が「背景で再生しない」に変わらないBUG修正:
//               - ラベル<span>に id(ytBgPlayModeEn/ytBgPlayModeJa)を付与
//               - onBgModeChange/初期化時に applyBgModeLabel() でEN/JA文言を切替
//                 ON :「Play in Background」「（背景で再生する）」
//                 OFF:「Not playing in background」「（背景で再生しない）」
//  17. [v18] 初回アクセス時のデフォルトを「チェックなし(背景で再生しない)」に変更:
//               - localStorage未保存時の既定値 true→false
//               - HTML初期状態も checked属性/checkedクラス除去・OFF文言で出力
//                 (JS実行前のフラッシュ防止)
//               - フレッシュ起動時は __ytReturnMode 未設定のため自動遷移しない
//  18. [v19] デフォルト=チェックON に戻す＋強制リセット＋アクセラレータ対策強化:
//               - 新規ブラウザセッション(閉じて開き直し)を sessionStorage で検知し
//                 (a) localStorage を強制 '1' に, (b) CacheStorage/ServiceWorker 削除,
//                 (c) チェックを強制ON にして表示
//               - 既定値(localStorage未保存)も true(背景で再生する)に統一
//               - HTTPヘッダーに Surrogate-Control/CDN-Cache-Control/Vary/動的ETag 追加
//                 (LOLIPOPアクセラレータ等の共有キャッシュ対策)
//  19. [v20] NOW PLAYINGクリック時のYouTube遷移仕様を変更:
//               - 旧仕様(v16)「背景の現在の再生位置(?t=現在秒)から続きを見る」を廃止
//               - 新仕様: タイトル(ytNowTitle)/URLリンク(ytNowUrl)クリック時、
//                 (a) ストックURLに時間指定(t=...)があれば → その時間で開く
//                 (b) 時間指定がもともと無ければ          → 動画の最初(頭出し)で開く
//                 いずれも window.location.assign() で同タブ画面遷移(再生途中でも即遷移)
//               - 追加: currentStockStartSeconds / currentStockVideoId で
//                 「ストックURL由来の開始秒数」を追跡(instantiateYtBgPlayerで記録)
//               - ヘルパー追加: buildNowPlayingNavUrl()
//               - getCurrentPlaybackSeconds()等のv16現在位置系はクリック処理から不使用化
//               - シリーズボタンの背景再生動作・遷移処理は従来通り(変更なし)
//  20. [v21] シリーズボタン押下時も背景再生せず即YouTubeへ画面遷移:
//               - switchSeries末尾の playAtUrlIndex() 呼び出しを廃止
//               - ボタン押下時の遷移ルール:
//                 (a) YouTube直接動画URL  → 時間指定(t=...)があればその時間で、無ければ頭出し
//                 (b) YouTube検索結果URL  → youtube.com/results?search_query=... のままで遷移
//                 (c) Google検索URL       → q=のみ抽出したクリーンURL(udm=保持)で遷移
//                 (d) その他URL(Facebook等) → そのURLへそのまま遷移
//               - v13-BUG5/v14のisPlayableVideoUrl判定分岐も統合(全URL共通の遷移処理へ一本化)
//  21. [v22] 言語カードクリック後の /aruaru・/aruaru-lady・/rakuten-mobile 遷移を同ドメイン対応:
//               - 従来: 常に https://audiocafe.tokyo/... のフルURLへ遷移
//               - 修正: __acNavBase() ヘルパーで現在のホストを判定し、
//                 (a) audiocafe.tokyo からのアクセス → 相対パス(/aruaru/等)で遷移
//                 (b) 別ドメイン(ala.tokyo/aon.co.jp等)からのアクセス → フルURLで遷移
//               - 対象: makeAcNavAruaruUrl / makeAcNavAruaruLadyUrl / makeAcNavRakutenMobileUrl
//  22. [v23] 言語カード遷移先: 任意ドメイン配下のパス存在確認をPHP側で実施:
//               - PHP: $_SERVER['DOCUMENT_ROOT'] 基準で file_exists()/is_dir() により
//                 /aruaru・/aruaru-lady・/rakuten-mobile の実在を確認
//               - 結果を __AC_NAV_LOCAL = {aruaru:bool, aruaruLady:bool, rakutenMobile:bool}
//                 としてページ内JSに埋め込む
//               - JS: __acNavUrl() ヘルパーが __AC_NAV_LOCAL を参照し、
//                 (a) true  → 相対パス(/aruaru/等)で遷移（同ドメイン優先）
//                 (b) false → https://audiocafe.tokyo/... のフルURLで遷移
//               - audiocafe.tokyo 以外のドメイン(ala.tokyo等)でも、
//                 そのDocumentRoot配下に各ディレクトリがあれば優先的に相対パス遷移する
//               - v22の __acNavBase()（ホスト名文字列比較）を廃止・置換
// ============================================================================

// ★ キャッシュ無効化ヘッダー (Lolipopアクセラレータ・プロキシ・CDN対策)
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
// 中間キャッシュ(リバースプロキシ/アクセラレータ/CDN)向け
header('Surrogate-Control: no-store');
header('CDN-Cache-Control: no-store');
header('Vary: *');
// レスポンスごとに必ず変わる ETag を付与し、共有キャッシュのヒットを防ぐ
header('ETag: "' . bin2hex(random_bytes(8)) . '"');

// ============================================================================
// index.php - 統合版 (ルート1枚で完結・LOLIPOP対応)
// 動作モード:
//   1. ?lang なし   → 言語カード表示 (130カ国)
//   2. ?lang=ja     → 言語カードを閉じて、日本語ビジネスページを表示(翻訳なし)
//   3. ?lang=XX(他) → 言語カードを閉じて、ビジネスページ + LIST 表示、Google翻訳適用
// ============================================================================

$lang = isset($_GET['lang']) ? preg_replace('/[^a-zA-Z-]/', '', $_GET['lang']) : '';

// ★ [v23] 同ドメイン配下の /aruaru・/aruaru-lady・/rakuten-mobile の存在をPHPで確認。
// file_exists() でサーバー上のファイルを直接チェックするため、
// 他ドメインからのアクセスでも「そのドメインのDocumentRoot配下」に実際にあるかどうかを判定できる。
// 結果はJS変数 __AC_NAV_LOCAL としてページ内に埋め込み、言語カードの遷移先URL生成に使用する。
$_ac_nav_docroot = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/');
$_ac_nav_local = [
    'aruaru'        => (is_dir($_ac_nav_docroot . '/aruaru')        || file_exists($_ac_nav_docroot . '/aruaru/index.php')),
    'aruaruLady'    => (is_dir($_ac_nav_docroot . '/aruaru-lady')   || file_exists($_ac_nav_docroot . '/aruaru-lady/index.php')),
    'rakutenMobile' => (is_dir($_ac_nav_docroot . '/rakuten-mobile') || file_exists($_ac_nav_docroot . '/rakuten-mobile/index.php')),
];

// mixlist=1 で lang 未指定時は ja 扱い
if (isset($_GET['mixlist']) && $lang === '') {
    $lang = 'ja';
}

// モード判定
$is_mixlist = isset($_GET['mixlist']);

// ★ 動画ランチャーモード (?mode=videolauncher)
$is_videolauncher = (isset($_GET['mode']) && $_GET['mode'] === 'videolauncher');

$is_cards_mode =
    (!$is_mixlist && !$is_videolauncher && ($lang === '' || $lang === 'ja'));

$is_ja_mode =
    ($lang === 'ja');

$is_translate_mode =
    (!$is_videolauncher && $lang !== '' && $lang !== 'ja'); // 言語カード＆/top要約LIST表示モード（日本語含む）

if (!$is_cards_mode) {
  header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
  header('Pragma: no-cache');
  header('Expires: 0');
}

// ==== SEED URLS (LIST構築用) =======================================
$SEED_URLS = [
  'https://ameblo.jp/www-aon/entry-12962652333.html',
  'https://ameblo.jp/www-aon/entry-12962682099.html',
  'https://ameblo.jp/www-aon/entry-12962730844.html',
  'https://ameblo.jp/www-aon/entry-12962730913.html',
  'https://ameblo.jp/www-aon/entry-12962730979.html',
  'https://ameblo.jp/www-aon/entry-12962011505.html',
  'https://ameblo.jp/www-aon/entry-12962045006.html',
  'https://ameblo.jp/www-aon/entry-12962004102.html',
  'https://ameblo.jp/www-aon/entry-12962649046.html',
  'https://ameblo.jp/www-aon/',
  'https://ameblo.jp/www-aon/entry-12959607919.html',
  'https://www.youtube.com/shorts/vXl0uoGnLC4',
  'https://www.facebook.com/reel/1531954871786867?locale=ja_JP',
  'https://www.youtube.com/shorts/M8XoS_8aDVA',
  'https://www.facebook.com/reel/1868606027121912?locale=ja_JP',
  'https://www.youtube.com/shorts/UfGxkRl0b9M',
  'https://www.facebook.com/reel/1829377097726495?locale=ja_JP',
  'https://www.youtube.com/shorts/YAEujI3VMrI',
  'https://www.facebook.com/reel/2520685188346999?locale=ja_JP',
  'https://www.youtube.com/shorts/3m6QXmKhYl4',
  'https://www.facebook.com/reel/999057156114660?locale=ja_JP',
  'https://www.youtube.com/shorts/GwycKGbec9s',
  'https://www.facebook.com/reel/2605431916518997?locale=ja_JP',
  'https://youtube.com/results?sp=mAEA&search_query=%E6%97%A5%E6%9C%AC+icbm',
  'https://www.youtube.com/shorts/RlXPYNx_VXM',
  'https://www.youtube.com/watch?v=ZKRBr5Zb6uY',
  'https://www.facebook.com/reel/1326008972738162/?app=fbl',
  'https://ameblo.jp/www-aon/entry-12959906889.html',
  'https://www.facebook.com/reel/1252821293014874?locale=ja_JP',
  'https://youtube.com/shorts/Gjw-1pT_-7Y?si=Ktpi7rcv5ts0-NDp',
  'https://www.youtube.com/watch?v=LhgFCvEP6j4',
  'https://www.facebook.com/reel/1826943584644565?locale=ja_JP',
  'https://www.youtube.com/watch?v=D2kWEZCP0F0',
  'https://www.facebook.com/reel/3593109567493701?locale=ja_JP',
  'https://www.youtube.com/watch?v=_DJ6E4LSLno',
  'https://www.youtube.com/watch?v=8ZyOiVvg9k0',
  'https://www.facebook.com/reel/901170439352064?locale=ja_JP',
  'https://www.youtube.com/watch?v=Ku3W4DWC-dw',
  'https://www.facebook.com/reel/917629584502965?locale=ja_JP',
  'https://www.youtube.com/results?search_query=%E7%84%A1%E4%BA%BA%E9%81%8B%E8%BB%A2%E3%80%80%E7%95%91',
  'https://www.youtube.com/shorts/esHyIvz7Iu0',
  'https://www.facebook.com/reel/1239737814444113?locale=ja_JP',
  'https://www.youtube.com/watch?v=siBWwWz6RSg',
  'https://www.facebook.com/reel/1491040635870813?locale=ja_JP',
  'https://www.youtube.com/watch?v=ufGJUrNDmdA',
  'https://www.facebook.com/reel/1992382882156066?locale=ja_JP',
  'https://drive.google.com/file/d/14YSfs6JKVG9KlWNVUFpWCqVP1IRrzNdZ/view?usp=sharing',
  'https://www.youtube.com/watch?v=jSp-q5i1RrI',
  'https://www.facebook.com/reel/1240151174191698?locale=ja_JP',
  'https://drive.google.com/file/d/1pWs_iRENaqQl3iXPBIOJB-jpKH36rNM1/view?usp=sharing',
  'https://www.youtube.com/results?search_query=ESD+Acoustic+Super+Dragon',
  'https://www.facebook.com/reel/4295909847399642?locale=ja_JP',
  'https://drive.google.com/file/d/1JToOcHhoXufyOJheDqRZojsfKVAHJtji/view?usp=sharing',
  'https://www.youtube.com/watch?v=sTbnmKUqZ3U',
  'https://www.facebook.com/reel/1436690014582378?locale=ja_JP',
  'https://ameblo.jp/www-aon/entry-12957176948.html?frm=theme',
  'https://www.youtube.com/watch?v=9MUH2gowaK8',
  'https://www.facebook.com/reel/944373501589168?locale=ja_JP',
  'https://drive.google.com/file/d/1O4Irl0xmXE82BIzS8sOaZpy5pHOFeemD/view?usp=sharing',
  'https://www.youtube.com/@yoshisuntv7502',
  'https://www.youtube.com/results?search_query=%E3%83%A8%E3%83%BC%E3%83%AD%E3%83%83%E3%83%91%E3%80%80TV%E3%80%80%E7%A5%9E%E9%81%93%E3%80%80',
  'https://drive.google.com/file/d/1kPOW_S8oW81SvqLnlWPAKgS1k0TN2ygd/view?usp=sharing',
  'https://www.facebook.com/reel/796082626860292?locale=ja_JP',
  'https://www.youtube.com/results?search_query=%E3%82%A4%E3%82%B9%E3%83%A9%E3%83%A0%E6%95%99%E3%80%80%E3%82%AD%E3%83%AA%E3%82%B9%E3%83%88%E6%95%99%E3%80%80%E3%82%A2%E3%83%8B%E3%83%A1%E3%80%80%E3%82%A2%E3%83%8B%E3%83%A1%E3%82%BD%E3%83%B3%E3%82%B0',
  'https://www.youtube.com/results?search_query=%E6%97%A5%E6%9C%AC%E3%80%80%E6%97%85%E8%A1%8C%E3%80%80%E8%8C%B6%E9%81%93%E3%80%80%E6%9B%B8%E9%81%93%E3%80%80%E5%90%88%E6%B0%97%E9%81%93%E3%80%80%E7%9D%80%E7%89%A9%E3%80%80%E6%B8%A9%E6%B3%89',
  'https://www.youtube.com/results?search_query=MITSUIBISHI%E3%80%80SpaceJET%E3%80%80%E3%83%AA%E3%83%BC%E3%82%B8%E3%83%A7%E3%83%8A%E3%83%AB%E3%82%B8%E3%82%A7%E3%83%83%E3%83%88%E3%80%80%E3%80%802026%E3%80%80',
  'https://www.youtube.com/results?search_query=hondajet+echelon+2600',
  'https://www.youtube.com/watch?v=pMOrmB1XZF4',
  'https://www.youtube.com/results?search_query=%E6%97%A5%E6%9C%AC%E3%80%80%E6%95%99%E6%8E%88%E3%80%80%E3%82%B4%E3%83%9F%E3%81%A7%E3%80%80%E7%A0%82%E6%BC%A0%E3%80%80%E7%B7%91%E5%8C%96%E3%80%80',
  'https://www.youtube.com/watch?v=gCBWEDutzN8',
  'https://www.facebook.com/reel/2374823542941143?locale=ja_JP',
  'https://www.youtube.com/watch?v=K_U6ln18KTo',
  'https://www.facebook.com/reel/2089460388563331?locale=ja_JP',
  'https://www.youtube.com/results?search_query=%E6%AD%8C%E5%BF%83%E3%82%8A%E3%81%88',
  'https://www.youtube.com/results?search_query=%E4%B8%89%E7%94%B0%E3%82%8A%E3%82%87%E3%81%86',
  'https://www.google.com/search?q=%E5%AF%AE%E8%B2%BB%E7%84%A1%E6%96%99+%E6%97%A5%E6%89%95%E3%81%84&oq=%E5%AF%AE%E8%B2%BB%E7%84%A1%E6%96%99+%E6%97%A5%E6%89%95%E3%81%84&gs_lcrp=EgZjaHJvbWUyBggAEEUYOdIBCTM0NDFqMGoxNagCCLACAQ&sourceid=chrome&ie=UTF-8&jbr=sep:0',
  'https://www.daijob.com/en/',
  'https://www.daijob.com/',
  'https://ameblo.jp/www-aon/entry-12738618944.html',
  'https://www.r-agent.com/kensaku/',
  'https://www.google.com/search?q=%E6%9C%88%E9%A1%8D240%E4%B8%87%E5%86%86%E3%80%80%E6%A1%88%E4%BB%B6&sca_esv=c04783e6906b025b&sxsrf=ANbL-n5S1hSDYTctSiotMAA_1wU-y9YC0A%3A1770077076354&ei=lDuBaeanFYKk2roPqdyCkAE&biw=1707&bih=739&aic=0&ved=0ahUKEwjmwfH6grySAxUCklYBHSmuABIQ4dUDCBE&uact=5&oq=%E6%9C%88%E9%A1%8D240%E4%B8%87%E5%86%86%E3%80%80%E6%A1%88%E4%BB%B6&gs_lp=Egxnd3Mtd2l6LXNlcnAiGOaciOmhjTI0MOS4h-WGhuOAgOahiOS7tjIFEAAY7wUyBRAAGO8FMgUQABjvBTIFEAAY7wVI5xNQlAFY0RBwAXgBkAEAmAFdoAGaBqoBATm4AQPIAQD4AQGYAgqgAsEGwgIKEAAYsAMY1gQYR8ICCBAAGIAEGKIEwgIFECEYoAHCAgkQIRigARgKGCrCAgQQIRgKmAMAiAYBkAYKkgcCMTCgB-4PsgcBObgHuwbCBwUwLjkuMcgHF4AIAA&sclient=gws-wiz-serp',
  'https://www.high-performer.jp/',
  'https://www.google.com/search?q=%E3%82%A2%E3%83%A1%E3%83%AA%E3%82%AB+%E3%83%87%E3%83%BC%E3%82%BF%E3%82%B5%E3%82%A4%E3%82%A8%E3%83%B3%E3%83%86%E3%82%A3%E3%82%B9%E3%83%88+%E8%B3%87%E6%A0%BC%E5%8F%96%E5%BE%97%E8%80%85%E3%80%80%E5%B9%B4%E5%8F%8E+%E3%82%A2%E3%83%A1%E3%83%AA%E3%82%AB+%E6%97%A5%E6%9C%AC&sca_esv=c04783e6906b025b&biw=1707&bih=739&aic=0&sxsrf=ANbL-n7jotaJDpd1kgOQeHkHaZ-D3Lljww%3A1770077469460&ei=HT2BaajpG-C60-kPou6lmQI&ved=0ahUKEwjo66q2hLySAxVg3TQHHSJ3KSMQ4dUDCBE&uact=5&oq=%E3%82%A2%E3%83%A1%E3%83%AA%E3%82%AB+%E3%83%87%E3%83%BC%E3%82%BF%E3%82%B5%E3%82%A4%E3%82%A8%E3%83%B3%E3%83%86%E3%82%A3%E3%82%B9%E3%83%88+%E8%B3%87%E6%A0%BC%E5%8F%96%E5%BE%97%E8%80%85%E3%80%80%E5%B9%B4%E5%8F%8E+%E3%82%A2%E3%83%A1%E3%83%AA%E3%82%AB+%E6%97%A5%E6%9C%AC&gs_lp=Egxnd3Mtd2l6LXNlcnAiW-OCouODoeODquOCqyDjg4fjg7zjgr_jgrXjgqTjgqjjg7Pjg4bjgqPjgrnjg4gg6LOH5qC85Y-W5b6X6ICF44CA5bm05Y-OIOOCouODoeODquOCqyDml6XmnKwyCBAAGKIEGIkFMggQABiiBBiJBUjWIVDcCFj5H3ABeAGQAQCYAcMBoAHVD6oBBDEyLje4AQPIAQD4AQGYAg2gAqQJwgIKEAAYsAMY1gQYR5gDAIgGAZAGCpIHAzkuNKAHiDOyBwM4LjS4B54JwgcGMC4xMi4xyAcUgAgA&sclient=gws-wiz-serp',
  'https://atgroup.jp/',
  'https://www.google.com/search?q=%E3%83%8E%E3%83%B3%E3%82%A2%E3%83%80%E3%83%AB%E3%83%88+%E3%83%81%E3%83%A3%E3%83%83%E3%83%88%E3%83%AC%E3%83%87%E3%82%A3&sca_esv=1cf3b1e2cbd119f3&sxsrf=ANbL-n76FdgCm5M_UjXCQ7ScXz7FtH3z-Q%3A1770022998303&ei=VmiAaaCbEo_c1e8PkLXg8Qc&biw=1707&bih=739&aic=0&ved=0ahUKEwignrvAubqSAxUPbvUHHZAaOH4Q4dUDCBE&uact=5&oq=%E3%83%8E%E3%83%B3%E3%82%A2%E3%83%80%E3%83%AB%E3%83%88+%E3%83%81%E3%83%A3%E3%83%83%E3%83%88%E3%83%AC%E3%83%87%E3%82%A3&gs_lp=Egxnd3Mtd2l6LXNlcnAiKOODjuODs-OCouODgOODq-ODiCDjg4Hjg6Pjg4Pjg4jjg6zjg4fjgqNI6BdQ8RNYoRVwAXgAkAEAmAF2oAHZAaoBAzEuMbgBA8gBAPgBAZgCAKACAJgDAIgGAZIHAKAHxQGyBwC4BwDCBwDIBwCACAA&sclient=gws-wiz-serp',
  'https://www.iijmio.jp/device/fcnt/arrows_we2plus_m06.html',
  'https://onboarding.mobile.rakuten.co.jp/plans',
  'https://ameblo.jp/www-aon/entry-12956648481.html',
  'https://www.google.com/search?q=%E3%82%AD%E3%83%A3%E3%83%90%E3%82%AF%E3%83%A9+%E6%99%82%E7%B5%A65%2C000%E5%86%86+7%2C000%E5%86%86&sca_esv=1cf3b1e2cbd119f3&biw=842&bih=739&aic=0&sxsrf=ANbL-n58Sd1x252ntrelnWvDGW4QbeDx-Q%3A1770023182607&ei=DmmAab3lJP_i1e8PvoDbiAg&ved=0ahUKEwi9pKyYurqSAxV_cfUHHT7AFoEQ4dUDCBE&uact=5&oq=%E3%82%AD%E3%83%A3%E3%83%90%E3%82%AF%E3%83%A9+%E6%99%82%E7%B5%A65%2C000%E5%86%86+7%2C000%E5%86%86&gs_lp=Egxnd3Mtd2l6LXNlcnAiJ-OCreODo-ODkOOCr-ODqSDmmYLntaY1LDAwMOWGhiA3LDAwMOWGhkiDH1DLC1jzGnACeAGQAQCYAXWgAe4FqgEDMC43uAEDyAEA-AEBmAIDoAJ8wgIKEAAYsAMY1gQYR8ICCBAAGIAEGKIEwgIFEAAY7wWYAwCIBgGQBgGSBwMyLjGgB_YGsgcDMC4xuAd4wgcDMC4zyAcEgAgA&sclient=gws-wiz-serp',
  'https://www.google.com/search?q=%E7%86%9F%E5%A5%B3%E3%82%AD%E3%83%A3%E3%83%90%E3%82%AF%E3%83%A9%E3%80%80%E6%B1%82%E4%BA%BA&sca_esv=1cf3b1e2cbd119f3&biw=846&bih=749&aic=0&sxsrf=ANbL-n7JDc-JWw0-X8Vhh6RaLMZKo1J7RQ%3A1770087595471&ei=q2SBae68HLra2roPw7GC8As&ved=0ahUKEwiuvuWSqrySAxU6rVYBHcOYAL4Q4dUDCBE&uact=5&oq=%E7%86%9F%E5%A5%B3%E3%82%AD%E3%83%A3%E3%83%90%E3%82%AF%E3%83%A9%E3%80%80%E6%B1%82%E4%BA%BA&gs_lp=Egxnd3Mtd2l6LXNlcnAiHueGn-Wls-OCreODo-ODkOOCr-ODqeOAgOaxguS6ukjxKFDoAViYJHACeACQAQCYAWygAcMHqgEEMTAuMbgBA8gBAPgBAZgCAKACAJgDAIgGAZIHAKAHoAWyBwC4BwDCBwDIBwCACAA&sclient=gws-wiz-serp&jbr=sep:0',
  'https://www.youtube.com/results?search_query=%E3%82%A4%E3%83%99%E3%83%AB%E3%83%A1%E3%82%AF%E3%83%81%E3%83%B3%E3%80%80%E3%82%AC%E3%83%B3%E3%80%80%E5%8C%97%E9%87%8C%E5%A4%A7%E5%AD%A6',
  'https://youtu.be/RtsalxNnk8Y?si=QAU9dy0AwB2OafKN',
  'https://www.facebook.com/reel/858539457187978?locale=ja_JP',
  'https://www.youtube.com/watch?v=maP5EMW4vxg',
  'https://www.facebook.com/reel/2702077600170951?locale=ja_JP',
  'https://www.youtube.com/watch?v=N-nG2S62H_E',
  'https://youtu.be/cfAo6soo1YA?si=t6RZ7H24UBM6soA6',
  'https://townwork.net/viewjob/jobid_6e068aedb8fedbb0/',
  'https://chatgpt.com/c/6983f182-30a0-8320-91bb-60b38afff82e',
  'https://www.google.com/search?q=HifiMAN+SERENADE&sca_esv=73cfac6e472fb041&sxsrf=ANbL-n4Hm6NYcaVTSc6mvnOO4L68jhqKDw%3A1770168337078&source=hp&ei=EaCCadrfAvn91e8PmqrAMA&iflsig=AFdpzrgAAAAAaYKuITNWp7KL4YPf0s5D-nRHNET7i8ud&ved=0ahUKEwiarLD31r6SAxX5fvUHHRoVEAYQ4dUDCDI&uact=5&oq=HifiMAN+SERENADE&gs_lp=Egdnd3Mtd2l6IhBIaWZpTUFOIFNFUkVOQURFMgUQABiABDIFEAAYgAQyBRAAGIAEMgUQABiABDIFEAAYgAQyBRAAGIAEMgUQABiABDIFEAAYgAQyBRAAGIAEMgUQABiABEiRFlCUCliUCnABeACQAQCYAUugAUuqAQExuAEDyAEA-AEC-AEBmAIBoAJUqAIAmAMC8QWlS5k8NpbETpIHATGgB6EDsgcBMbgHVMIHAzItMcgHB4AIAA&sclient=gws-wiz',
  'https://www.google.com/search?q=DST-Lacerta+DST-DRACO+PC%E3%81%A8LAN%E6%8E%A5%E7%B6%9A&sca_esv=7da175bd9b925cd1&biw=1707&bih=739&aic=0&sxsrf=ANbL-n5DaZAc3XNEVxKRf2gjtzlGOYeRnQ%3A1770162410705&ei=6oiCad_fKtSdvr0PhNjL4Ac&ved=0ahUKEwjf_LztwL6SAxXUjq8BHQTsEnwQ4dUDCBM&uact=5&oq=DST-Lacerta+DST-DRACO+PC%E3%81%A8LAN%E6%8E%A5%E7%B6%9A&gs_lp=Egxnd3Mtd2l6LXNlcnAiJERTVC1MYWNlcnRhIERTVC1EUkFDTyBQQ-OBqExBTuaOpee2mjIIEAAYogQYiQUyBRAAGO8FMggQABiABBiiBDIFEAAY7wVIkUhQX1jCRHAHeAGQAQCYAegBoAH7EaoBBjE3LjUuMbgBA8gBAPgBAZgCGqAC4g_CAgoQABiwAxjWBBhHwgIFECEYoAHCAgYQIRgKGCqYAwCIBgGQBgqSBwYyMC41LjGgB4oosgcGMTMuNS4xuAfND8IHBjIuMjMuMcgHMIAIAA&sclient=gws-wiz-serp',
  'https://www.amazon.co.jp/dp/B0D7ZWLW2G',
  'https://www.youtube.com/results?search_query=ChatGPT%E3%81%A7RUST%E3%81%A7WEB%E9%96%8B%E7%99%BA',
  'https://ideabyishiduka.blogspot.com/2026/01/blog-post_52.html',
  'https://www.youtube.com/results?search_query=%E9%AB%98%E6%A9%8B%E3%81%82%E3%81%9A%E7%BE%8E',
  'https://www.youtube.com/results?search_query=%E9%88%B4%E6%9C%A8%E7%91%9B%E7%BE%8E%E5%AD%90',
  'https://www.youtube.com/results?search_query=%E5%A4%A7%E5%92%8C%E3%83%8F%E3%82%A6%E3%82%B9%E3%81%AE%E3%82%AA%E3%83%BC%E3%83%87%E3%82%A3%E3%82%AA%E3%83%AB%E3%83%BC%E3%83%A0%E3%80%80%E3%83%BC%E7%9F%B3%E4%BA%95%E5%BC%8F%E3%83%AA%E3%82%B9%E3%83%8B%E3%83%B3%E3%82%B0%E3%83%AB%E3%83%BC%E3%83%A0%E3%82%92%E6%8E%A1%E7%94%A8%E3%81%97%E3%81%9F%E7%90%86%E6%83%B3%E3%81%AE%E9%9F%B3%E3%82%92%E8%87%AA%E5%AE%85%E3%81%A7%E6%A5%BD%E3%81%97%E3%82%81%E3%82%8B%E5%BF%AB%E9%81%A9%E9%98%B2%E9%9F%B3%E5%AE%A4%E3%80%82%E5%A4%A7%E4%BA%8B%E3%81%AA%E3%81%AE%E3%81%AF%E6%A9%9F%E5%99%A8%E3%82%88%E3%82%8A%E7%A9%BA%E9%96%93%E3%83%BC',
  'https://ideabyishiduka.blogspot.com/2026/01/blog-post_37.html?zx=34dc68db11a0d1ce',
  'https://ideabyishiduka.blogspot.com/2026/01/20261171.html',
  'https://neovisionconsulting.blogspot.com/2025/04/tad-me1tadtad-me1tx3-3tadtad-me1tx.html',
  'https://neovisionconsulting.blogspot.com/2025/02/opt-iso-boxmdela-s110base-t.html',
  'https://neovisionconsulting.blogspot.com/2025/02/auro-cxauro-3dnew-version.html',
  'https://neovisionconsulting.blogspot.com/2025/03/24.html',
  'https://neovisionconsulting.blogspot.com/2025/02/island-songs-ai-nishida-my-favorite.html',
  'https://neovisionconsulting.blogspot.com/2025/02/amazon-music-unlimitedhiresstreo.html',
  'https://neovisionconsulting.blogspot.com/2025/02/crystal-clear-sound-test-ultimate.html',
  'https://neovisionconsulting.blogspot.com/2025/02/hi-end-sound-test-speaker-hi-res-music.html',
  'https://neovisionconsulting.blogspot.com/2025/02/fanclub-livealdea-bar-at-tokyo-2024.html',
  'https://neovisionconsulting.blogspot.com/2025/02/zard221-2021629-1750u-next.html',
  'https://www.youtube.com/results?search_query=ZARD%E3%80%80%E3%82%AA%E3%83%BC%E3%82%B1%E3%82%B9%E3%83%88%E3%83%A9',
  'https://neovisionconsulting.blogspot.com/2025/02/kokiaelsound-5v4asforzato-dst-lacerta.html',
  'https://neovisionconsulting.blogspot.com/2025/01/2025_30.html',
  'https://neovisionconsulting.blogspot.com/2025/02/dalisub-e12nsbsube12n.html',
  'https://neovisionconsulting.blogspot.com/2025/02/sonosarc-ultrasonos-sub-4124-20251240630.html',
  'https://neovisionconsulting.blogspot.com/2025/02/sonos-sub-mini-subwoofer-wifi-black.html',
  'https://neovisionconsulting.blogspot.com/2025/01/stereo.html',
  'https://neovisionconsulting.blogspot.com/2025/01/blog-post_35.html',
  'https://neovisionconsulting.blogspot.com/2025/01/40khz_24.html',
  'https://neovisionconsulting.blogspot.com/2025/02/california-dreamin-diana-krall-lp.html',
  'https://neovisionconsulting.blogspot.com/2025/01/tiglon-whitetiger-tpl-3000a-wt18mor12m.html',
  'https://neovisionconsulting.blogspot.com/2024/04/oyaideli-50-g5.html',
  'https://neovisionconsulting.blogspot.com/2022/08/thx-4.html',
  'https://neovisionconsulting.blogspot.com/2022/08/klipschs-theatre-room-speakers-have.html',
  'https://neovisionconsulting.blogspot.com/2023/01/gustarx1620230116.html',
  'https://neovisionconsulting.blogspot.com/2021/04/i-love-audio-kinoshita-monitorrey_15.html',
  'https://neovisionconsulting.blogspot.com/2020/12/audiovs-comparison-audition-audio.html',
  'https://neovisionconsulting.blogspot.com/2020/07/imax4000dream-imax-home-theater-for.html',
  'https://neovisionconsulting.blogspot.com/2022/09/imax_19.html',
  'https://neovisionconsulting.blogspot.com/2020/07/special-feature-on-audio-room-with-high_24.html',
  'https://neovisionconsulting.blogspot.com/2020/07/high-ceiling-is-ideal-for-luxury-audio_24.html',
  'https://neovisionconsulting.blogspot.com/2021/09/dan-dagostinothe-dan-dagostino-is-audio_13.html',
  'https://neovisionconsulting.blogspot.com/2024/03/wb-um200nusb-typec.html',
  'https://neovisionconsulting.blogspot.com/2024/03/facebooklm6365.html',
  'https://neovisionconsulting.blogspot.com/2024/02/blog-post_785.html',
  'https://neovisionconsulting.blogspot.com/2024/02/pc-generic-24-90-atx-24-atx-argb-psu-24.html',
  'https://neovisionconsulting.blogspot.com/2024/03/blog-post_38.html',
  'https://neovisionconsulting.blogspot.com/2024/03/hp-5-2017new-master-referencespeaker.html',
  'https://neovisionconsulting.blogspot.com/2024/03/yahoo8n7n1rca.html',
  'https://neovisionconsulting.blogspot.com/2024/03/kharma-elegance-rca-10-20000.html',
  'https://neovisionconsulting.blogspot.com/2024/03/388a2024-audioopera-rca1m328.html',
  'https://neovisionconsulting.blogspot.com/2024/02/20224224mm015748inchi508cm2inchi.html',
  'https://neovisionconsulting.blogspot.com/2024/03/ear-834p.html',
  'https://neovisionconsulting.blogspot.com/2024/03/monitor-audio-silver-rs8-black-120000-1.html',
  'https://neovisionconsulting.blogspot.com/2024/03/monitor-audio-bronze-6-walnut-1-2ch.html',
  'https://neovisionconsulting.blogspot.com/2024/02/smslusb-dacd2rgustardusb-dacr26.html',
  'https://neovisionconsulting.blogspot.com/2024/02/fostex-cw250d.html',
  'https://youtube.com/results?sp=mAEA&search_query=%E5%9C%B0%E9%9C%87%E3%81%AB%E5%BC%B7%E3%81%84%E3%82%B9%E3%83%94%E3%83%BC%E3%82%AB%E3%83%BC%E3%82%B9%E3%82%BF%E3%83%B3%E3%83%89',
  'https://neovisionconsulting.blogspot.com/2024/02/b-cm1-78000.html',
  'https://neovisionconsulting.blogspot.com/2024/02/770000.html',
  'https://neovisionconsulting.blogspot.com/2024/02/kripton-pc-hr1000pcocc-hr-2m-45000.html',
  'https://neovisionconsulting.blogspot.com/2024/02/ietf.html',
  'https://neovisionconsulting.blogspot.com/2024/02/to-music-lovers-around-world-and.html',
  'https://neovisionconsulting.blogspot.com/2024/02/pass-labs-is-top-of-line-audio.html',
  'https://neovisionconsulting.blogspot.com/2024/02/la-4-masterclass-pre-amplifier650000_23.html',
  'https://neovisionconsulting.blogspot.com/2024/02/spa-4-masterclass-stereo-power-amplifier.html',
  'https://neovisionconsulting.blogspot.com/2024/02/audiosirtonepcsacd.html',
  'https://neovisionconsulting.blogspot.com/2023/12/7n-5427553p-zonotone-7nps-shupreme1-18m.html',
  'https://neovisionconsulting.blogspot.com/2024/01/blog-post_52.html',
  'https://neovisionconsulting.blogspot.com/2024/02/ac-3ex.html',
  'https://neovisionconsulting.blogspot.com/2024/02/blog-post_32.html',
  'https://neovisionconsulting.blogspot.com/2024/02/rca10m-45000.html',
  'https://neovisionconsulting.blogspot.com/2024/02/triode-luminous84-128700.html',
  'https://neovisionconsulting.blogspot.com/2024/02/2a3.html',
  'https://neovisionconsulting.blogspot.com/2024/01/about-of-luxman-cl1000_31.html',
  'https://neovisionconsulting.blogspot.com/2024/02/aurorasoundhfsa-01am2tech-larson.html',
  'https://neovisionconsulting-blogspot-com.translate.goog/2023/12/aurorasoundhfsa-01-20231213-aab5-3.html?_x_tr_sl=ja&_x_tr_tl=en&_x_tr_hl=ja&_x_tr_pto=wapp',
  'https://neovisionconsulting.blogspot.com/2023/12/aurorasoundhfsa-01-20231213-aab5-3.html',
  'https://neovisionconsulting.blogspot.com/2023/11/blog-post_23.html',
  'https://neovisionconsulting.blogspot.com/2024/02/ibassodac8octa-dacdapdx260socsnapdragon.html',
  'https://neovisionconsulting.blogspot.com/2023/11/usb-dac-vmv-d2r-vs-d400ex-comparison_30.html',
  'https://neovisionconsulting.blogspot.com/2023/11/usb-dacvmv-d2r-vs-d400ex-youtube-vmv.html',
  'https://neovisionconsulting.blogspot.com/2023/12/cardas-hexlink-powercord-hex6-15m.html',
  'https://www.audiostyle.net/archives/sirtone-pwc11008.html',
  'https://neovisionconsulting.blogspot.com/2023/12/mutecref10-nano-2024-20231207.html',
  'https://neovisionconsulting.blogspot.com/2023/10/cayin-n7-dap.html',
  'https://neovisionconsulting.blogspot.com/2023/11/zero-link.html',
  'https://www.youtube.com/results?search_query=SFORZATO%E3%80%80SOULNOTE',
  'https://neovisionconsulting.blogspot.com/2023/11/soulnotezero-link.html',
  'https://neovisionconsulting.blogspot.com/2023/11/sforzatodst-lynx-dst-lanusbzero-link.html',
  'https://neovisionconsulting.blogspot.com/2023/11/usbupnplan-dac-roon_13.html',
  'https://neovisionconsulting.blogspot.com/2023/11/pc-audiolan-dacdiretta.html',
  'https://www.esoteric.jp/jp/product/d1x/feature',
  'https://www.youtube.com/results?search_query=Chord+Electronics+DAV',
  'https://neovisionconsulting.blogspot.com/2023/11/dacfpga-spartan-6-xc6slx75lsilsi.html',
  'https://av.watch.impress.co.jp/docs/news/1547583.html',
  'https://neovisionconsulting.blogspot.com/2023/11/what-is-new-standard-usb4-what-is.html',
  'https://neovisionconsulting.blogspot.com/2023/11/usb4usb.html',
  'https://neovisionconsulting.blogspot.com/2023/11/accuphaseuesugi.html',
  'https://www.youtube.com/results?search_query=Jackie+Evancho',
  'https://www.youtube.com/results?search_query=%E3%82%B5%E3%83%A9%E3%83%BB%E3%82%AA%E3%83%AC%E3%82%A4%E3%83%B3+%E5%90%9B%E3%82%92%E3%81%AE%E3%81%9B%E3%81%A6',
  'https://neovisionconsulting.blogspot.com/2023/11/sayonara-adieu-galaxy-express-999.html',
  'https://neovisionconsulting.blogspot.com/2023/11/lucy-thomas-timeless.html',
  'https://neovisionconsulting.blogspot.com/2023/11/netflix-react-ssr-50-slackreact.html',
  'https://neovisionconsulting.blogspot.com/2023/11/li-50-g5-furutech-fp314ag-15musb-dac.html',
  'https://neovisionconsulting.blogspot.com/2023/04/ntt-ocnwifiyoutube.html',
  'https://neovisionconsulting.blogspot.com/2023/09/ever-solo-dmp-a6-hifi-a6-m2ssd.html',
  'https://neovisionconsulting.blogspot.com/2023/10/20luxmantriode-trxeq7luxman.html',
  'https://neovisionconsulting.blogspot.com/2023/10/saec-stratosphere-pc-triple-cex-rca-15m.html',
  'https://neovisionconsulting.blogspot.com/2023/03/m2tech-larson-a.html',
  'https://amzn.to/luxman/',
  'https://amzn.to/yamaha/',
  'https://neovisionconsulting.blogspot.com/2023/01/2620211215-2000.html',
  'https://amzn.asia/d/5o2gx8Z',
  'https://neovisionconsulting-blogspot-com.translate.goog/2023/04/acoustic-revive-power-reference-triple-c.html?_x_tr_sl=ja&_x_tr_tl=en&_x_tr_hl=ja&_x_tr_pto=wapp',
  'https://neovisionconsulting.blogspot.com/2023/04/acoustic-revive-power-reference-triple-c.html',
  'https://neovisionconsulting.blogspot.com/2022/10/for-checking-audio-sound-quality-power.html',
  'https://neovisionconsulting.blogspot.com/2022/10/oyaidetunamigpx-rv218mpc825.html',
  'https://neovisionconsulting.blogspot.com/2023/04/acoustic-reviveforged-pc-triplec.html',
  'https://www.kripton.jp/fs/kripton/pc-hr1500m-tc',
  'https://neovisionconsulting.blogspot.com/2022/10/blog-post_25.html',
  'https://neovisionconsulting.blogspot.com/2020/08/stella-stereo-power-amplifier-taurus.html',
  'https://neovisionconsulting.blogspot.com/2023/01/if-its-windows-pc-it-can-be-reproduced.html',
  'https://neovisionconsulting.blogspot.com/2023/01/su-g30su-r1000byamp.html',
  'https://neovisionconsulting.blogspot.com/2022/12/i-heard-that-rebecca-quit-hard-rock.html',
  'https://neovisionconsulting.blogspot.com/2022/12/hit-hitaon-ceo.html',
  'https://neovisionconsulting.blogspot.com/2022/11/the-10-most-expensive-and-exuberant.html',
  'https://neovisionconsulting.blogspot.com/2022/11/large-back-loaded-horn-speakers.html',
  'https://neovisionconsulting.blogspot.com/2022/11/blog-post_59.html',
  'https://neovisionconsulting.blogspot.com/2022/11/usb-3031-1115m-5000usb-to-busb-type-c.html',
  'https://neovisionconsulting.blogspot.com/2022/10/165cmamt2wayamtwebatdiolanyoutube.html',
  'https://neovisionconsulting.blogspot.com/2022/10/magico-m9-speakers-cost-over-120.html',
  'https://neovisionconsulting.blogspot.com/2022/10/m9imax40001-imax-dolby-cinema-atmos_30.html',
  'https://neovisionconsulting.blogspot.com/2022/11/monitor-audio-bronze6wa-2-25.html',
  'https://neovisionconsulting-blogspot-com.translate.goog/2022/11/after-replacing-power-cord-between-pc.html?_x_tr_sl=ja&_x_tr_tl=en&_x_tr_hl=ja&_x_tr_pto=wapp',
  'https://neovisionconsulting.blogspot.com/2022/11/7npc-triplec45rca.html',
  'https://neovisionconsulting.blogspot.com/2022/11/blog-post_86.html',
  'https://neovisionconsulting.blogspot.com/2022/11/zonotonepower-cables7n45.html',
  'https://neovisionconsulting.blogspot.com/2022/11/blog-post_18.html',
  'https://neovisionconsulting.blogspot.com/2022/11/furutech-fi50m-ncf-r.html',
  'https://neovisionconsulting.blogspot.com/2022/11/after-replacing-power-cord-between-pc.html',
  'https://neovisionconsulting.blogspot.com/2022/11/q-tpl-2000atz20a15a-tpl-2000atztpl-2000a.html',
  'https://neovisionconsulting-blogspot-com.translate.goog/2022/10/blog-post_51.html?_x_tr_sl=ja&_x_tr_tl=en&_x_tr_hl=ja&_x_tr_pto=wapp',
  'https://neovisionconsulting.blogspot.com/2022/10/blog-post_51.html',
  'https://neovisionconsulting-blogspot-com.translate.goog/2022/10/blog-post_51.html?_x_tr_sl=ja&_x_tr_tl=zh-CN&_x_tr_hl=ja&_x_tr_pto=wapp',
  'https://neovisionconsulting.blogspot.com/2022/09/logicool-21ch-pc-z523bk-pc-z533-120w_19.html',
  'https://neovisionconsulting.blogspot.com/2022/10/audiodesign-amplifier-and-speaker.html',
  'https://neovisionconsulting.blogspot.com/2022/10/tiglon-tpl-2000atztpl-2000atca-18w-hse.html',
  'https://neovisionconsulting.blogspot.com/2022/10/18mtiglonhse-mgl-dfa10-hse18.html',
  'https://www.amazon.co.jp/dp/B00NDRHMQ2/ref=cm_sw_r_awdo_ZJ6PZFAK3JMXJWS5JZKN_0?psc=1',
  'http://www.oyaide.com/ja/products/power_supply/power_cable/tunami_gpx-r_v2',
  'https://neovisionconsulting.blogspot.com/2022/10/for-high-quality-sound-for-surround.html',
  'https://neovisionconsulting.blogspot.com/2022/10/pci-eusb31type-type-c-asm3142-xp-win7-8.html',
  'https://neovisionconsulting.blogspot.com/2022/03/c-1011-p-23-2mor3mor5m-3phigh-class.html',
  'https://neovisionconsulting.blogspot.com/2022/09/blog-post_42.html',
  'https://neovisionconsulting.blogspot.com/2022/09/dragonspeakersystermcfrp-ototenesd.html',
  'https://neovisionconsulting.blogspot.com/2022/09/large-japanese-speaker.html',
  'https://neovisionconsulting.blogspot.com/2022/10/oyaide-ss-47-2m.html',
  'https://neovisionconsulting.blogspot.com/2022/08/englishwe-propose-that-nvidia-amd.html',
  'https://neovisionconsulting.blogspot.com/2021/06/5top.html',
  'https://neovisionconsulting.blogspot.com/2022/08/nvidia-amdavx512full.html',
  'https://neovisionconsulting-blogspot-com.translate.goog/2020/07/mirai-speaker-home-new.html?_x_tr_sl=ja&_x_tr_tl=en&_x_tr_hl=ja&_x_tr_pto=wapp',
  'https://neovisionconsulting-blogspot-com.translate.goog/2022/02/denon-sc-l30.html?_x_tr_sl=ja&_x_tr_tl=en&_x_tr_hl=ja&_x_tr_pto=wapp',
  'https://neovisionconsulting-blogspot-com.translate.goog/2022/05/way.html?_x_tr_sl=ja&_x_tr_tl=en&_x_tr_hl=ja&_x_tr_pto=wapp',
  'https://neovisionconsulting.blogspot.com/2020/07/mirai-speaker-home-new.html',
  'https://neovisionconsulting.blogspot.com/2022/02/denon-sc-l30.html',
  'https://neovisionconsulting.blogspot.com/2022/05/way.html',
  'https://neovisionconsulting.blogspot.com/2022/06/idea-for-successor-to-monitor-audio.html',
  'https://neovisionconsulting.blogspot.com/2022/03/koji-tamaki-orchestra.html',
  'https://neovisionconsulting.blogspot.com/2021/10/denonavav-amplifier-surround.html',
  'https://neovisionconsulting.blogspot.com/2022/03/wir-stellen-ein-modell-vor-das-einen.html',
  'https://neovisionconsulting.blogspot.com/2022/03/introducing-model-that-uses-volume.html',
  'https://neovisionconsulting.blogspot.com/2022/06/tx-na5008english.html',
  'https://neovisionconsulting.blogspot.com/2022/06/av-92-onkyo-tx-nr5007-gold-or-silvertx.html',
  'https://neovisionconsulting.blogspot.com/2022/03/blog-post_23.html',
  'https://neovisionconsulting.blogspot.com/2022/06/tx-na5008.html',
  'https://neovisionconsulting.blogspot.com/2022/05/su-r100020220515.html',
  'https://neovisionconsulting.blogspot.com/2022/06/about-speaker-cables-with-good-sound.html',
  'https://neovisionconsulting.blogspot.com/2022/06/20220608.html',
  'https://neovisionconsulting.blogspot.com/2022/06/made-in-italia-alare-remiga21200.html',
  'https://neovisionconsulting.blogspot.com/2022/03/enya4kjackie-evanchokoujitamakiorchestr.html',
  'https://neovisionconsulting.blogspot.com/2022/05/7.html',
  'https://neovisionconsulting.blogspot.com/2022/06/o-techs-speaker-cables-that-flow-like.html',
  'https://neovisionconsulting.blogspot.com/2022/06/blog-post_63.html',
  'https://neovisionconsulting.blogspot.com/2022/05/zonotone-6nsp-granster-5500-bi-wiring-2.html',
  'https://neovisionconsulting.blogspot.com/2022/05/zonotone-6nsp-granster-5500-beryllium.html',
  'https://neovisionconsulting.blogspot.com/2022/05/canare-4s6g-ofc-bfa-2-2m.html',
  'https://neovisionconsulting.blogspot.com/2022/03/onkodo-mogami-2534-rca-2-03m.html',
  'https://neovisionconsulting.blogspot.com/2022/02/rcausbusbtype-atype-c.html',
  'https://www.amazon.co.jp/s?k=%E3%83%86%E3%82%A3%E3%82%B0%E3%83%AD%E3%83%B3+%E9%9B%BB%E6%BA%90%E3%82%B1%E3%83%BC%E3%83%96%E3%83%AB&i=digital-music&camp=247&creative=1211&linkCode=ur2&linkId=9abeffb74b5dc36950ff7fe1de4f36e2&tag=sastruts-22',
  'https://procable.jp/lan/belden_lan.html',
  'https://neovisionconsulting.blogspot.com/2022/05/pro-cable-usb-typec-printer-usb-dac-top.html',
  'https://neovisionconsulting.blogspot.com/2022/05/prousb-typecusb-dac20cm30cmusb.html',
  'https://neovisionconsulting.blogspot.com/2022/04/usb-dacugreenusb-typecmidiugreen-usb-c.html',
  'https://neovisionconsulting.blogspot.com/2022/03/blog-post_571.html',
  'https://neovisionconsulting.blogspot.com/2022/05/blog-post_83.html',
  'https://neovisionconsulting.blogspot.com/2022/05/monitor-audio-bronze2wawhite.html',
  'https://neovisionconsulting.blogspot.com/2022/03/open-third-eye-awakening-your-higher.html',
  'https://neovisionconsulting.blogspot.com/2022/03/youtube.html',
  'https://neovisionconsulting.blogspot.com/2022/04/ado.html',
  'https://neovisionconsulting.blogspot.com/2022/04/rampow-usb-type-c-to-usb-30-20cmotg.html',
  'https://neovisionconsulting.blogspot.com/2022/03/i-asked-denon-if-there-is-any-plan-to.html',
  'https://neovisionconsulting.blogspot.com/2022/03/denonusbwindows11u-nextcddvddisk.html',
  'https://neovisionconsulting.blogspot.com/2022/03/i-asked-panasonic-technics-questionwhen.html',
  'https://neovisionconsulting.blogspot.com/2022/03/panasonicsu-g30-su-g700m2-su.html',
  'https://neovisionconsulting.blogspot.com/2022/02/uhq-cdmqaalbinonis-adagio-pachelbels.html',
  'https://neovisionconsulting.blogspot.com/2022/03/jbl-quantum-stream.html',
  'https://neovisionconsulting.blogspot.com/2022/03/how-to-build-14000-speakers-for-1300.html',
  'https://neovisionconsulting.blogspot.com/2022/03/presonus-eris-epresonus-eris-e-series.html?m=0',
  'https://www.amazon.co.jp/dp/B0040C1ED6/ref=as_sl_pc_as_ss_li_til?tag=sastruts-22&linkCode=w00&linkId=d05f9aa79c8a56c04667a80e01a6a431&creativeASIN=B0040C1ED6',
  'https://neovisionconsulting.blogspot.com/2021/12/30.html',
  'https://neovisionconsulting.blogspot.com/2022/03/rey-audiodemoyoutube.html',
  'https://neovisionconsulting.blogspot.com/2022/03/electro-voice-patrician-ii-450000011987.html',
  'https://neovisionconsulting.blogspot.com/2022/03/electro-voice-georgian.html',
  'https://neovisionconsulting.blogspot.com/2022/02/21-lr-by-wyvern-audio-24k-by-wyvern.html',
  'https://neovisionconsulting.blogspot.com/2022/02/honkent-600x2-9999-ofc-lr.html',
  'https://neovisionconsulting.blogspot.com/2022/03/a-speech-robot-can-finally-produce-same.html?m=0',
  'https://neovisionconsulting.blogspot.com/2022/02/ifi-audiomqa.html',
  'https://neovisionconsulting.blogspot.com/2022/02/usb-daca80a100a300prousb-dac-to-a300pro.html',
  'https://neovisionconsulting.blogspot.com/2021/11/10-k10s-nekkst-usb-dac-gustard-dac.html',
  'https://neovisionconsulting.blogspot.com/2022/03/pro-shop-by-leroy.html',
  'https://neovisionconsulting.blogspot.com/2021/10/from-now-on-concept-of-audio-system.html',
  'https://neovisionconsulting.blogspot.com/2021/12/usb-daca300pro-jib-2rca-to-2rca-rl.html',
  'https://neovisionconsulting.blogspot.com/2022/01/focalalpha8020cm500002100000.html',
  'https://neovisionconsulting.blogspot.com/2022/01/presonus-eris-e35-presonus-eris-e45-bt.html',
  'https://neovisionconsulting.blogspot.com/2021/10/as-of-october-2021-more-and-more.html',
  'https://neovisionconsulting.blogspot.com/2021/10/202110tvusb-dacnetflixu-nextapplemusic.html',
  'https://neovisionconsulting.blogspot.com/2021/11/blog-post_99.html',
  'https://neovisionconsulting.blogspot.com/2022/02/usbdacfocalalpha80-usb-daca100.html',
  'https://neovisionconsulting.blogspot.com/2022/02/anber-tech-54127-4k60hz-hdmi-71ch.html',
  'https://neovisionconsulting.blogspot.com/2021/12/dacd.html',
  'https://neovisionconsulting.blogspot.com/2022/02/2401exclusive-2401-style-kenrick.html',
  'https://neovisionconsulting.blogspot.com/2022/02/an-idea-for-jbls-new-vertical-double.html',
  'https://neovisionconsulting.blogspot.com/2021/10/4k3bgm4k-relaxing-hawaii-piano-hawaii.html',
  'https://neovisionconsulting.blogspot.com/2021/11/tv.html',
  'https://neovisionconsulting.blogspot.com/2021/12/klipsch-klipsch-is-well-known-speaker.html',
  'https://neovisionconsulting.blogspot.com/2020/07/avalon4500tesseractavalons-tesseract-45.html',
  'https://neovisionconsulting.blogspot.com/2020/07/4500techdasair-force-zerotechdas-air.html',
  'https://neovisionconsulting.blogspot.com/2020/05/focal2m2700grande-utopia-em-evostella.html',
  'https://neovisionconsulting.blogspot.com/2020/06/laser-record-player.html',
  'https://neovisionconsulting.blogspot.com/2021/05/pagode600-edition-mk-ii-hd02mr-finite.html',
  'https://neovisionconsulting.blogspot.com/2020/07/nssd410-296-100200chrome-based.html',
  'https://neovisionconsulting.blogspot.com/2020/06/about-cellulose-nanofiber-new-material_19.html',
  'https://neovisionconsulting.blogspot.com/2020/07/paint-fine-this-is-world-recognized.html',
  'https://neovisionconsulting.blogspot.com/2020/08/30.html',
  'https://www.google.com/search?sxsrf=ALeKk00SRZMKrqJAAi-YCXFWuyqpAK36Vg%3A1602292918979&ei=tgyBX5eqO8ypoATUwp-ACw&q=2021%E5%B9%B4%E6%B3%95%E6%94%B9%E6%AD%A3%E3%80%80%E5%BB%BA%E7%AF%89%E5%9F%BA%E6%BA%96&oq=2021%E5%B9%B4%E6%B3%95%E6%94%B9%E6%AD%A3%E3%80%80%E5%BB%BA%E7%AF%89%E5%9F%BA%E6%BA%96&gs_lcp=CgZwc3ktYWIQAzIFCCEQoAE6BAgjECc6BQgAEM0CUIoPWOYqYJ8uaAFwAHgAgAFeiAGoBpIBAjEwmAEAoAEBqgEHZ3dzLXdpesABAQ&sclient=psy-ab&ved=0ahUKEwiX3Yui7qjsAhXMFIgKHVThB7AQ4dUDCA0&uact=5',
  'https://www.google.com/search?q=%E7%AD%8B%E4%BA%A4%E3%81%84%E3%82%88%E3%82%8A%E5%A4%A7%E3%81%8D%E3%81%AA%E6%A7%8B%E9%80%A0%E5%90%88%E6%9D%BF%E3%81%AE%E3%83%80%E3%82%A4%E3%82%B1%E3%83%B3%E3%83%80%E3%82%A4%E3%83%A9%E3%82%A4%E3%83%88%E3%82%B7%E3%83%BC%E3%82%B8%E3%83%B3%E3%82%B0%E3%83%9C%E3%83%BC%E3%83%89%E3%81%A7%E3%81%99%E3%80%82&sca_esv=9e2784469eca2aa9&sxsrf=ANbL-n6veeMzyfPYGShnbfu3-NV8obONBA%3A1773740353858&ei=QSG5aa6KNO_c2roPlbXq2AI&ved=0ahUKEwjurtzd0aaTAxVvrlYBHZWaGisQ4dUDCBE&uact=5&oq=%E7%AD%8B%E4%BA%A4%E3%81%84%E3%82%88%E3%82%8A%E5%A4%A7%E3%81%8D%E3%81%AA%E6%A7%8B%E9%80%A0%E5%90%88%E6%9D%BF%E3%81%AE%E3%83%80%E3%82%A4%E3%82%B1%E3%83%B3%E3%83%80%E3%82%A4%E3%83%A9%E3%82%A4%E3%83%88%E3%82%B7%E3%83%BC%E3%82%B8%E3%83%B3%E3%82%B0%E3%83%9C%E3%83%BC%E3%83%89%E3%81%A7%E3%81%99%E3%80%82&gs_lp=Egxnd3Mtd2l6LXNlcnAiY-eti-S6pOOBhOOCiOOCiuWkp-OBjeOBquani-mAoOWQiOadv-OBruODgOOCpOOCseODs-ODgOOCpOODqeOCpOODiOOCt-ODvOOCuOODs-OCsOODnOODvOODieOBp-OBmeOAgjIFEAAY7wUyCBAAGIAEGKIEMgUQABjvBTIIEAAYgAQYogQyBRAAGO8FSLwDUABYAHAAeACQAQCYAZ0BoAGdAaoBAzAuMbgBA8gBAPgBAvgBAZgCAaACogGYAwCSBwMwLjGgB4UDsgcDMC4xuAeiAcIHAzItMcgHA4AIAA&sclient=gws-wiz-serp',
  'https://neovisionconsulting.blogspot.com/2020/07/blog-post_40.html',
  'https://www.google.com/search?q=%E3%83%A4%E3%83%99%E3%83%9B%E3%83%BC%E3%83%A0+%E9%95%B7%E5%B4%8E+%E3%83%9B%E3%82%A6%E9%85%B8%E6%B0%B4+%E9%AB%98%E8%80%90%E9%9C%87%E6%80%A7+%E8%80%90%E9%9C%87%E3%83%9C%E3%83%BC%E3%83%89+%E3%83%80%E3%82%A4%E3%83%A9%E3%82%A4%E3%83%88%E5%B7%A5%E6%B3%95&sca_esv=82ec823d9eac6353&biw=949&bih=900&sxsrf=ANbL-n6Hqg1XVbluoXV8ODGpm7tWVdBSUw%3A1773815024119&ei=8ES6acPxBtzj2roP1sCx-A4&ved=0ahUKEwjDqaPz56iTAxXcsVYBHVZgDO8Q4dUDCBE&uact=5&oq=%E3%83%A4%E3%83%99%E3%83%9B%E3%83%BC%E3%83%A0+%E9%95%B7%E5%B4%8E+%E3%83%9B%E3%82%A6%E9%85%B8%E6%B0%B4+%E9%AB%98%E8%80%90%E9%9C%87%E6%80%A7+%E8%80%90%E9%9C%87%E3%83%9C%E3%83%BC%E3%83%89+%E3%83%80%E3%82%A4%E3%83%A9%E3%82%A4%E3%83%88%E5%B7%A5%E6%B3%95&gs_lp=Egxnd3Mtd2l6LXNlcnAiVuODpOODmeODm-ODvOODoCDplbfltI4g44Ob44Km6YW45rC0IOmrmOiAkOmch-aApyDogJDpnIfjg5zjg7zjg4kg44OA44Kk44Op44Kk44OI5bel5rOVSL0LUABYqgVwAHgBkAEAmAGQAaAB_QGqAQMwLjK4AQPIAQD4AQGYAgCgAgCYAwCSBwCgB8YFsgcAuAcAwgcAyAcAgAgA&sclient=gws-wiz-serp',
  'https://neovisionconsulting.blogspot.com/2020/10/blog-post_8.html',
  'https://neovisionconsulting.blogspot.com/2020/10/blog-post_10.html',
  'https://neovisionconsulting.blogspot.com/2021/09/4jbl-krs-tunes-4-way-multi-jbl-class-d.html',
  'https://neovisionconsulting.blogspot.com/2021/09/jbl-c35-krs-new-repro-jbl-c35-fairfield.html',
  'https://www.youtube.com/playlist?list=PLMKeU44Jcmplf72oDMDJ6ZZY9SJBW6UwX',
  'https://neovisionconsulting.blogspot.com/2020/06/boxjbl-remove-unit-from-speaker-box-and.html',
  'https://neovisionconsulting.blogspot.com/2020/03/b-802-d3-constellation-audio-pictor.html',
  'https://neovisionconsulting.blogspot.com/2020/10/jazzvocalsaec.html',
  'https://neovisionconsulting.blogspot.com/2021/12/1-by.html',
  'https://neovisionconsulting.blogspot.com/2020/06/111204-jackie-evanchotv.html',
  'https://neovisionconsulting.blogspot.com/2022/07/british-audio-wharfedale-monitor-audio.html',
  'https://neovisionconsulting.blogspot.com/2021/09/i-would-like-to-aim-to-be-large-scale.html'
];

// ==== HELPERS ======================================================
function is_video_host($url) {
  return preg_match('#(youtube\.com|youtu\.be|facebook\.com/reel|facebook\.com/watch|fb\.watch|vimeo\.com|nicovideo\.jp|tiktok\.com|drive\.google\.com)#i', $url);
}
function source_name($url) {
  if (preg_match('#youtube\.com|youtu\.be#i', $url)) return 'YouTube';
  if (preg_match('#facebook\.com|fb\.watch#i', $url)) return 'Facebook';
  if (preg_match('#drive\.google\.com#i', $url)) return 'Drive';
  if (preg_match('#vimeo\.com#i', $url)) return 'Vimeo';
  if (preg_match('#nicovideo\.jp#i', $url)) return 'Niconico';
  if (preg_match('#tiktok\.com#i', $url)) return 'TikTok';
  return 'Video';
}
function extract_yt_id($url) {
  $patterns = [
    '#youtu\.be/([a-zA-Z0-9_-]{11})#',
    '#[?&]v=([a-zA-Z0-9_-]{11})#',
    '#/embed/([a-zA-Z0-9_-]{11})#',
    '#/shorts/([a-zA-Z0-9_-]{11})#',
  ];
  foreach ($patterns as $p) {
    if (preg_match($p, $url, $m)) return $m[1];
  }
  return null;
}
function fetch_url($url, $timeout = 4) {
  $ctx = stream_context_create([
    'http' => [
      'timeout' => $timeout,
      'user_agent' => 'Mozilla/5.0 (compatible; AudiocafeBot/1.0)',
      'follow_location' => 1, 'max_redirects' => 3, 'ignore_errors' => true,
    ],
    'https' => [
      'timeout' => $timeout,
      'user_agent' => 'Mozilla/5.0 (compatible; AudiocafeBot/1.0)',
      'follow_location' => 1, 'max_redirects' => 3, 'ignore_errors' => true,
    ],
  ]);
  $data = @file_get_contents($url, false, $ctx);
  return $data ?: '';
}

function extract_from_html($html, $base_url) {
  $result = ['links' => [], 'images' => [], 'title' => null];
  if (!$html) return $result;
  if (preg_match('#<title[^>]*>([^<]{1,200})</title>#i', $html, $m)) {
    $t = trim(html_entity_decode($m[1], ENT_QUOTES, 'UTF-8'));
    $t = preg_replace('#\s*[-|]\s*(?:YouTube|Facebook|Ameba|Ameblo).*$#i', '', $t);
    if ($t) $result['title'] = $t;
  }
  if (preg_match_all('#<a[^>]+href=["\']([^"\']+)["\'][^>]*>(.*?)</a>#is', $html, $matches, PREG_SET_ORDER)) {
    foreach ($matches as $m) {
      $href = trim($m[1]);
      $text = trim(strip_tags($m[2]));
      $text = preg_replace('/\s+/', ' ', $text);
      if (!$href || $href[0] === '#' || stripos($href, 'javascript:') === 0) continue;
      if (!preg_match('#^https?://#i', $href)) {
        if (strpos($href, '//') === 0) $href = 'https:' . $href;
        elseif ($href[0] === '/') {
          if (preg_match('#^(https?://[^/]+)#i', $base_url, $bm)) $href = $bm[1] . $href;
          else continue;
        } else continue;
      }
      $result['links'][] = ['url' => $href, 'text' => $text ?: $href];
    }
  }
  if (preg_match_all('#<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\']#i', $html, $ms, PREG_SET_ORDER)) {
    foreach ($ms as $m) {
      $src = trim($m[1]);
      if ($src) $result['images'][] = ['src' => $src, 'alt' => ''];
    }
  }
  if (preg_match_all('#<img[^>]+src=["\']([^"\']+)["\'][^>]*(?:alt=["\']([^"\']*)["\'])?[^>]*>#i', $html, $ms, PREG_SET_ORDER)) {
    foreach ($ms as $m) {
      $src = trim($m[1]);
      $alt = isset($m[2]) ? trim($m[2]) : '';
      if (!$src || stripos($src, 'data:') === 0) continue;
      if (!preg_match('#^https?://#i', $src)) {
        if (strpos($src, '//') === 0) $src = 'https:' . $src;
        elseif ($src[0] === '/' && preg_match('#^(https?://[^/]+)#i', $base_url, $bm)) $src = $bm[1] . $src;
        else continue;
      }
      if (preg_match('#(icon|logo|sprite|favicon|emoji|avatar|thumb_)#i', $src)) continue;
      $result['images'][] = ['src' => $src, 'alt' => $alt];
    }
  }
  return $result;
}

function build_lists($seed_urls) {
  $cache_file = sys_get_temp_dir() . '/audiocafe_lists_cache.json';
  $cache_ttl = 86400;
  if (is_file($cache_file) && (time() - filemtime($cache_file)) < $cache_ttl) {
    $cached = @json_decode(@file_get_contents($cache_file), true);
    if ($cached) return $cached;
  }
  $text_links = []; $video_links = []; $photos = [];
  $seen_text = []; $seen_video = []; $seen_img = [];
  foreach ($seed_urls as $url) {
    if (!$url) continue;
    if (is_video_host($url)) {
      if (!isset($seen_video[$url])) {
        $seen_video[$url] = true;
        $video_links[] = ['url' => $url, 'title' => $url];
      }
    }
  }
  $fetched_count = 0;
  $max_fetches = 30;
  foreach ($seed_urls as $url) {
    if ($fetched_count >= $max_fetches) break;
    if (is_video_host($url)) continue;
    if (!preg_match('#^https?://#i', $url)) continue;
    $html = fetch_url($url, 4);
    if (!$html) continue;
    $fetched_count++;
    $data = extract_from_html($html, $url);
    $page_title = $data['title'] ?: $url;
    if (!isset($seen_text[$url])) {
      $seen_text[$url] = true;
      $text_links[] = ['url' => $url, 'title' => $page_title];
    }
    foreach ($data['links'] as $link) {
      $u = $link['url'];
      if (is_video_host($u)) {
        if (!isset($seen_video[$u])) {
          $seen_video[$u] = true;
          $video_links[] = ['url' => $u, 'title' => $link['text'] ?: $page_title];
        }
      }
    }
    $img_count = 0;
    foreach ($data['images'] as $img) {
      if ($img_count >= 3) break;
      $src = $img['src'];
      if (isset($seen_img[$src])) continue;
      $seen_img[$src] = true;
      $photos[] = ['src' => $src, 'alt' => $img['alt'] ?: $page_title];
      $img_count++;
    }
  }
  foreach ($seed_urls as $url) {
    if (is_video_host($url)) continue;
    if (!isset($seen_text[$url]) && preg_match('#^https?://#i', $url)) {
      $seen_text[$url] = true;
      $text_links[] = ['url' => $url, 'title' => $url];
    }
  }
  $result = ['text_links' => $text_links, 'video_links' => $video_links, 'photos' => $photos];
  @file_put_contents($cache_file, json_encode($result, JSON_UNESCAPED_UNICODE));
  return $result;
}

// LISTは翻訳モードの時のみ構築
if ($is_translate_mode || $is_mixlist) {
  // BUG修正: $is_mixlist（日本語含む）の場合も $lists / $SUMMARY_LIST を必ず定義する
  $cache_file = sys_get_temp_dir() . '/audiocafe_lists_cache.json';
  $cache_ttl = 86400;
  $cache_valid = (is_file($cache_file) && (time() - filemtime($cache_file)) < $cache_ttl);

  // ローディング処理:
  //   1. キャッシュがない & build=1が未指定 → ローディング画面表示
  //   2. キャッシュがない & build=1 → 実際にビルド実行（ユーザーにはバックグラウンド）
  //   3. キャッシュがある → 即表示

  if (!$cache_valid && !isset($_GET['build']) && !$is_mixlist) {
    // ローディング画面表示モード（mixlist時はローディングなし）
    $show_loading = true;
  } else {
    $show_loading = false;
    $lists = build_lists($SEED_URLS);
    // 言語カード＆/top要約LIST用: 主要7カ国（USA,JAPAN,IRAN,IRAQ,RUSSIA,CHINA,KOREA）
    // ルートの言語カード（英語〜韓国語）の英語・日本語文章を省略なしで全文埋め込み
    // ===== 自動生成: JSの L[] 配列からSUMMARY_LISTを構築 =====
    // L[] を1箇所修正するだけで mixlist にも自動反映される
    $SUMMARY_LIST = (function() {
      $self = file_get_contents(__FILE__);
      // var L=[...]; ブロックを行頭アンカーで抽出（PHPコード内のL[]と混同しない）
      if (!preg_match('/^var L=\[$(.*?)^\];$/ms', $self, $m)) return [];
      $raw = '[' . $m[1] . ']';
      // JSのUnicodeエスケープをデコード
      $raw = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function($u){ return mb_convert_encoding(pack('H*', $u[1]), 'UTF-8', 'UTF-16BE'); }, $raw);
      // JS文字列の \n → 実改行（JSON非互換な生の\nを変換）
      // cardLinks配列をJSON互換に変換するためにJS→JSON変換
      // JSオブジェクトキーにクォートを追加: g: → "g":
      $raw = preg_replace('/\b(g|n|t|a|r|c|d|p|fc|bioScroll|cardLinks|href|label)\s*:/', '"$1":', $raw);
      // bioScroll:1 はJSONのtrue相当だがそのまま数値として扱う
      // 末尾カンマ除去（JSON非互換）
      $raw = preg_replace('/,\s*([\]}])/', '$1', $raw);
      $cards = json_decode($raw, true);
      if (!is_array($cards)) return [];
      $out = [];
      foreach ($cards as $card) {
        $c_val = isset($card['c']) ? $card['c'] : '';
        $d_val = isset($card['d']) ? $card['d'] : '';
        $a_val = isset($card['a']) ? $card['a'] : '';
        $g_val = isset($card['g']) ? $card['g'] : '';
        // c か d が実質的なコンテンツ（短い国名だけでなく長文）を持つカードのみ
        if (mb_strlen($c_val) < 30 && mb_strlen($d_val) < 30) continue;
        $links = [];
        if (!empty($card['cardLinks']) && is_array($card['cardLinks'])) {
          foreach ($card['cardLinks'] as $lk) {
            if (!empty($lk['href'])) {
              $links[] = ['href' => $lk['href'], 'label' => isset($lk['label']) ? $lk['label'] : $lk['href']];
            }
          }
        }
        $fc_val = isset($card['fc']) ? $card['fc'] : 'un';
        $out[] = ['g' => $g_val, 'a' => $a_val, 'fc' => $fc_val, 'title_en' => $c_val, 'title_ja' => $d_val, 'links' => $links];
      }
      return $out;
    })();
    if (!is_array($SUMMARY_LIST)) { $SUMMARY_LIST = []; }
  }
} else {
  $show_loading = false;
}
?>
<?php if ($is_cards_mode): ?>
<!DOCTYPE html>
<html lang="ja">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title>AUDIOCAFE | World — Select Your Language</title>
<meta name="description" content="Translate audiocafe.tokyo into any of the world's languages via Google Translate.">
<link rel="icon" href="/images/audiocafe-logo-wordmark.svg" type="image/svg+xml">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
/* 高解像度・高品質レンダリング */
html{-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;text-rendering:optimizeLegibility;font-feature-settings:"kern" 1}
img{image-rendering:-webkit-optimize-contrast;image-rendering:crisp-edges}
canvas{image-rendering:pixelated}
:root{--bg:#000;--surface:rgba(15,23,42,.52);--border:rgba(255,255,255,.06);--border-hover:rgba(34,211,238,.3);--text:#e2e8f0;--text-dim:#94a3b8;--text-muted:#64748b;--cyan:#22d3ee;--cyan-glow:rgba(34,211,238,.15)}
html{scroll-behavior:smooth}
body{background:var(--bg);color:var(--text);font-family:'Segoe UI',system-ui,-apple-system,sans-serif;min-height:100vh;display:flex;flex-direction:column}
::selection{background:var(--cyan-glow);color:#fff}


.header{position:relative;overflow:hidden;text-align:center;padding:1.5rem 1rem 2rem}
.header::before{content:none;display:none}
@keyframes logoGradientLR{0%{background-position:0% 50%}100%{background-position:100% 50%}}.logo{display:inline-block;font-size:clamp(2.5rem,6vw,3.8rem);font-weight:900;letter-spacing:-.02em;opacity:.62;background:linear-gradient(90deg,rgba(99,102,241,.88) 0%,rgba(37,99,235,.88) 10%,rgba(34,197,94,.88) 20%,rgba(132,204,22,.88) 30%,rgba(250,204,21,.88) 40%,rgba(249,115,22,.88) 50%,rgba(255,20,147,.88) 60%,rgba(239,68,68,.88) 70%,rgba(192,38,211,.88) 80%,rgba(124,58,237,.88) 90%,rgba(99,102,241,.88) 100%);background-size:400% 100%;animation:logoGradientLR 20s ease-in-out infinite;-webkit-background-clip:text;background-clip:text;color:transparent;text-decoration:none;transition:opacity .2s,filter .2s;filter:drop-shadow(0 2px 14px rgba(0,0,0,.55)) drop-shadow(0 0 28px rgba(0,0,0,.35))}
.logo:hover{opacity:.82;filter:drop-shadow(0 3px 18px rgba(0,0,0,.6)) drop-shadow(0 0 32px rgba(34,211,238,.15))}
.subtitle{margin-top:.5rem;font-size:clamp(.95rem,2vw,1.25rem);color:var(--text-dim);font-weight:500}
.note{margin-top:.5rem;font-size:.8rem;color:var(--text-muted);max-width:36rem;margin-left:auto;margin-right:auto;line-height:1.6}
.search-wrap{position:relative;max-width:26rem;margin:1.5rem auto 0}
.search-icon{position:absolute;left:.9rem;top:50%;transform:translateY(-50%);width:1.1rem;height:1.1rem;color:var(--text-muted);pointer-events:none}
.search{width:100%;padding:.7rem 1rem .7rem 2.6rem;border-radius:9999px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.05);color:#fff;font-size:.875rem;outline:none;backdrop-filter:blur(12px);transition:border-color .2s,box-shadow .2s}
.search::placeholder{color:var(--text-muted)}
.search:focus{border-color:rgba(34,211,238,.5);box-shadow:0 0 0 3px rgba(34,211,238,.12)}
.pills{display:flex;flex-wrap:wrap;justify-content:center;gap:.4rem;margin-top:1.2rem;padding:0 .5rem}
.pill{border:none;border-radius:9999px;padding:.45rem 1.05rem;font-size:.95rem;font-weight:700;cursor:pointer;transition:all .2s;background:rgba(255,255,255,.05);color:#fff}
.pill:hover{background:rgba(255,255,255,.1);color:var(--text-dim)}
.pill.active{background:var(--cyan-glow);color:var(--cyan);box-shadow:inset 0 0 0 1px rgba(34,211,238,.4)}
.main{max-width:76rem;margin:0 auto;padding:1rem 1rem 4rem;width:100%;flex:1;min-width:0}
.region-section{margin-bottom:2.5rem}
.region-title{font-size:2rem;font-weight:700;color:#fff;margin-bottom:1.2rem;letter-spacing:.03em}
.region-count{font-size:1rem;font-weight:400;color:var(--text-muted);margin-left:.5rem}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(min(100%,158px),1fr));gap:.65rem}
@media(min-width:640px){.grid{grid-template-columns:repeat(auto-fill,minmax(min(100%,172px),1fr));gap:.7rem}}
@media(min-width:1024px){.grid{grid-template-columns:repeat(auto-fill,minmax(min(100%,182px),1fr));gap:.75rem}}
.card{display:flex;flex-direction:column;align-items:stretch;gap:.35rem;padding:1rem .55rem;border-radius:.75rem;border:1px solid var(--border);background:var(--surface);backdrop-filter:blur(6px);text-decoration:none;text-align:center;transition:transform .18s,border-color .18s,background .18s,box-shadow .18s;cursor:pointer;width:100%;min-width:0;max-width:100%;box-sizing:border-box}
.card:hover{transform:scale(1.03);border-color:var(--border-hover);background:rgba(255,255,255,.07);box-shadow:0 8px 24px rgba(34,211,238,.06)}
.card-flag{width:60px;height:40px;object-fit:cover;border-radius:4px;box-shadow:0 2px 8px rgba(0,0,0,.3);transition:transform .18s;image-rendering:-webkit-optimize-contrast;image-rendering:auto;align-self:center;flex-shrink:0}
.card:hover .card-flag{transform:scale(1.1)}
.card-code{display:block;font-size:.95rem;font-weight:800;letter-spacing:.08em;color:var(--cyan);transition:color .18s;align-self:center;max-width:100%;text-align:center !important;width:100%}
.card:hover .card-code{color:#67e8f9}
.card-native{display:block;font-size:.95rem;font-weight:600;color:rgba(255,255,255,.9);align-self:center;max-width:100%;text-align:center !important;width:100%}
.card:hover .card-native{color:#fff}
.card-en{display:block;font-size:1.0rem;font-weight:600;color:rgba(255,255,255,.9);word-break:break-word;overflow-wrap:anywhere;line-height:1.4;align-self:center;max-width:100%;min-width:0;text-align:center !important;width:100%}
.card:hover .card-en{color:#fff}
.card-country{display:block;font-size:1.0rem;font-weight:500;color:rgba(255,255,255,.92);word-break:break-word;overflow-wrap:anywhere;line-height:1.45;text-align:center !important;max-width:100%;min-width:0;width:100%;padding:.4rem .35rem;margin:0;box-sizing:border-box;border-radius:.4rem;background:rgba(0,0,0,.14);max-height:min(42vh,16rem);overflow-y:auto;-webkit-overflow-scrolling:touch;overscroll-behavior:contain}
.card:hover .card-country{color:#fff}
.card-country+.card-country{margin-top:.2rem}
/* サブラベル（IRAQカード等: ネイティブ語名の直後に表示する行） */
.card-sublabel{display:block;font-size:.85rem;font-weight:600;color:rgba(255,255,255,.78);letter-spacing:.04em;line-height:1.5;max-width:100%;text-align:center !important;width:100%}
.card:hover .card-sublabel{color:rgba(255,255,255,.95)}
/* bioScroll カードの英語長文: 左右スクロール */
.card-en-scroll{
  display:block;
  white-space:normal;
  word-break:break-word;
  overflow-wrap:anywhere;
  overflow-x:hidden;
  overflow-y:auto;
  font-size:inherit;
}
.card-en-scroll::-webkit-scrollbar{height:7px}
.card-en-scroll::-webkit-scrollbar-thumb{background:rgba(34,211,238,.5);border-radius:4px}
.card-en-scroll::-webkit-scrollbar-thumb:hover{background:rgba(34,211,238,.85)}
.card-en-scroll::-webkit-scrollbar-track{background:rgba(255,255,255,.05);border-radius:4px}
.card--bio-scroll .card-en{
  white-space:normal;
  word-break:break-word;
  overflow-wrap:anywhere;
  overflow-x:hidden;
  overflow-y:auto;
  font-size:inherit;
}
.card--bio-scroll .card-jp-bio{
  max-height:min(34vh,15rem);
  overflow-y:auto;
  -webkit-overflow-scrolling:touch;
  overscroll-behavior:contain;
  width:100%;
  display:flex;
  flex-direction:column;
  gap:.2rem;
}
.card--bio-scroll .card-jp-bio .card-country{max-height:none}
.card{align-self:stretch}
.card:focus-visible{outline:2px solid rgba(34,211,238,.75);outline-offset:2px}
.card-exlink{display:block;margin-top:.35rem;padding:.38rem .45rem;font-size:.95rem;font-weight:700;color:#67e8f9;line-height:1.35;word-break:break-all;text-align:center;text-decoration:none;border-radius:.4rem;border:1px solid rgba(34,211,238,.28);background:rgba(0,0,0,.16);cursor:pointer;transition:border-color .15s,color .15s,background .15s}
.card-exlink:hover,.card-exlink:focus-visible{color:#fff;border-color:rgba(34,211,238,.55);background:rgba(34,211,238,.1);outline:none}
.ac-nav-modal__extra{display:flex;flex-direction:column;gap:.5rem;margin-top:.25rem;padding-top:.75rem;border-top:1px solid rgba(148,163,184,.22)}
.ac-nav-modal__extra-caption{font-size:.76rem;color:#94a3b8;line-height:1.45;margin:0 0 .15rem}
.empty{text-align:center;padding:4rem 1rem;color:var(--text-muted);font-size:1rem}
.footer{border-top:1px solid rgba(255,255,255,.05);padding:1.5rem 1rem;text-align:center;font-size:.7rem;color:var(--text-muted);line-height:1.7}
.footer a{color:var(--text-dim);text-decoration:none;transition:color .2s}
.footer a:hover{color:#fff}

.ac-nav-modal{position:fixed;inset:0;z-index:2000;display:none;align-items:center;justify-content:center;padding:max(.75rem,env(safe-area-inset-bottom));box-sizing:border-box}
.ac-nav-modal.is-open{display:flex}
.ac-nav-modal__backdrop{position:absolute;inset:0;background:rgba(0,0,0,.68);backdrop-filter:blur(5px);-webkit-backdrop-filter:blur(5px)}
.ac-nav-modal__box{position:relative;z-index:1;width:min(100%,460px);max-height:min(92vh,38rem);display:flex;flex-direction:column;padding:0;overflow:hidden;border-radius:16px;background:rgba(15,23,42,.98);border:1px solid rgba(34,211,238,.5);box-shadow:0 24px 64px rgba(0,0,0,.55)}
.ac-nav-modal__scroll{flex:1 1 auto;min-height:0;max-height:min(88vh,36rem);overflow-y:auto;overflow-x:hidden;-webkit-overflow-scrolling:touch;overscroll-behavior:contain;padding:1.35rem 1.15rem 1.25rem}
.ac-nav-modal__scroll:focus-within{outline:none}
.ac-nav-modal__title{font-size:clamp(.9rem,2.2vw,1.02rem);font-weight:800;color:#f8fafc;line-height:1.55;margin:0 0 .45rem}
.ac-nav-modal__hint{font-size:.78rem;color:#94a3b8;margin:0 0 1rem;line-height:1.45}
.ac-nav-modal__btns{display:flex;flex-direction:column;gap:.55rem}
.ac-nav-modal__btn{display:block;width:100%;padding:.68rem .9rem;border-radius:12px;border:1px solid rgba(148,163,184,.38);background:rgba(255,255,255,.07);color:#e2e8f0;font-size:.86rem;font-weight:700;cursor:pointer;text-align:center;line-height:1.4}
.ac-nav-modal__btn:hover,.ac-nav-modal__btn:focus-visible{border-color:rgba(34,211,238,.6);background:rgba(34,211,238,.14);outline:none}
.ac-nav-modal__btn--primary{border-color:rgba(34,211,238,.55)}
.site-nav-wrap .sn-title{display:block;font-weight:700;color:var(--text-dim);margin-bottom:.4rem;font-size:.62rem;letter-spacing:.12em;text-transform:uppercase}
.site-nav-rows{display:flex;flex-direction:column;gap:.35rem;align-items:center}
.site-nav-row{display:flex;flex-wrap:wrap;gap:.35rem .6rem;justify-content:center;align-items:center}
.site-nav-row .sn-d{font-weight:600;color:rgba(255,255,255,.85);font-size:.72rem}
.site-nav-row a{color:var(--cyan);text-decoration:none}
.site-nav-row a:hover{text-decoration:underline}
.site-nav-row .sn-sep{color:var(--text-muted);opacity:.45}
body>.site-nav-wrap .sn-title,body>.site-nav-wrap .sn-d,body>.site-nav-wrap a,body>.site-nav-wrap .sn-sep,body>.site-nav-wrap table,body>.site-nav-wrap th,body>.site-nav-wrap td{text-shadow:0 1px 3px rgba(0,0,0,.9),0 0 18px rgba(0,0,0,.65)}


/* background YouTube search playlist */
.yt-bg-outer{position:fixed;inset:0;z-index:0;pointer-events:none;overflow:hidden;background:#000}
.yt-bg-outer iframe{position:absolute;top:50%;left:50%;width:100vw;height:56.25vw;min-height:100vh;min-width:177.77vh;transform:translate(-50%,-50%);border:0;opacity:1}
body:not(.yt-bg-ready) .yt-bg-outer{opacity:1;visibility:visible}
body.yt-bg-ready .yt-bg-outer{opacity:1;visibility:visible;transition:opacity .35s ease}
body>.site-nav-wrap{position:relative;z-index:1;background:transparent!important;backdrop-filter:none!important;-webkit-backdrop-filter:none!important}
body>header,body>main,body>footer{position:relative;z-index:1;background:transparent!important;backdrop-filter:none!important;-webkit-backdrop-filter:none!important}
.header .subtitle,.header .note,.region-title,.region-count{text-shadow:0 1px 3px rgba(0,0,0,.85),0 0 24px rgba(0,0,0,.55)}
.footer,.footer a{text-shadow:0 1px 2px rgba(0,0,0,.75)}
/* Google公式翻訳ウィジェットのバナーを非表示にしてカスタムパネルが常に最前面に */
.goog-te-banner-frame{display:none!important}
.goog-te-gadget{font-size:0!important;color:transparent!important}
.goog-te-gadget a{display:none!important}
body{top:0!important}
.yt-bg-vol-wrap{position:fixed;top:1rem;right:1rem;z-index:2147483647;display:flex;align-items:center;gap:.75rem;padding:.65rem 1rem;box-sizing:border-box;width:min(90vw,420px);max-width:100%;max-height:calc(100vh - 2rem);max-height:calc(100dvh - 2rem);overflow-y:auto;overflow-x:hidden;background:rgba(15,23,42,.92);border:1px solid rgba(34,211,238,.28);border-radius:14px;box-shadow:0 10px 36px rgba(0,0,0,.4);backdrop-filter:blur(10px);-webkit-overflow-scrolling:touch;overscroll-behavior:contain}
/* CLOSEボタン行を sticky 化して常時パネル内最上部に表示 */
.yt-bg-vol-wrap .yt-close-row{position:sticky;top:-.65rem;margin-top:-.65rem;margin-left:-1rem;margin-right:-1rem;padding:.55rem 1rem .35rem;background:linear-gradient(180deg,rgba(15,23,42,.98) 0%,rgba(15,23,42,.95) 75%,rgba(15,23,42,0) 100%);z-index:10;display:flex;align-items:center;justify-content:flex-end;width:auto;flex-basis:100%;gap:.6rem}
/* 横向きスマホ等の極端に低い画面用 */
@media (max-height:480px){
  .yt-bg-vol-wrap{padding:.45rem .75rem;gap:.4rem}
  .yt-bg-vol-wrap .yt-close-row{margin-top:-.45rem;margin-left:-.75rem;margin-right:-.75rem;padding:.4rem .75rem .25rem;top:-.45rem}
}
.yt-bg-vol-wrap label{font-size:clamp(0.7rem, 2vw, 0.875rem);font-weight:700;color:#e2e8f0;letter-spacing:.02em}
.yt-bg-vol-wrap input[type=range]{width:min(100%,min(200px,35vw));height:12px;accent-color:#22d3ee;cursor:pointer}
.yt-bg-vol-wrap{flex-wrap:wrap}
.yt-bg-theme{font-size:.82rem;font-weight:700;color:#94a3b8;letter-spacing:.02em;margin-right:.15rem;display:flex;align-items:center;gap:.35rem;flex-wrap:wrap;max-width:100%}
.yt-bg-name-en{font-weight:600;color:#cbd5e1;letter-spacing:.06em;text-transform:uppercase;font-size:.78rem}
.yt-bg-name-sep{opacity:.45;font-weight:400}
.yt-bg-search-row{width:100%;flex-basis:100%;display:flex;flex-wrap:wrap;align-items:center;gap:.5rem .65rem;margin-top:.35rem;padding-top:.55rem;border-top:1px solid rgba(148,163,184,.25)}
.yt-bg-search-row label{font-size:.78rem;font-weight:700;color:#cbd5e1;white-space:nowrap}
.yt-bg-search-row input[type=search]{flex:1 1 0;min-width:0;max-width:100%;padding:.45rem .65rem;border-radius:10px;border:1px solid rgba(34,211,238,.35);background:rgba(15,23,42,.85);color:#f1f5f9;font-size:.82rem}
.yt-bg-search-row .yt-bg-btn{font-size:.78rem;font-weight:700;padding:.45rem .85rem;border-radius:10px;border:1px solid rgba(34,211,238,.45);background:linear-gradient(180deg,rgba(34,211,238,.22),rgba(15,23,42,.9));color:#e0f2fe;cursor:pointer}
.yt-bg-search-row .yt-bg-btn:hover{border-color:rgba(34,211,238,.75);background:linear-gradient(180deg,rgba(34,211,238,.35),rgba(15,23,42,.95))}
.yt-bg-ctrl-row{display:flex;align-items:center;gap:.4rem;flex-wrap:wrap;margin-left:.15rem}
.yt-bg-mini-btn{font-size:.72rem;font-weight:700;padding:.4rem .72rem;border-radius:9px;border:1px solid rgba(34,211,238,.42);background:rgba(15,23,42,.55);color:#e0f2fe;cursor:pointer;transition:border-color .15s,background .15s,color .15s}
.yt-bg-mini-btn:hover{border-color:rgba(34,211,238,.75);background:rgba(34,211,238,.18);color:#fff}
.yt-bg-mini-btn[aria-pressed="true"]{border-color:rgba(34,211,238,.9);background:rgba(34,211,238,.28);color:#fff;box-shadow:inset 0 0 0 1px rgba(255,255,255,.12)}
.yt-series-btn{display:inline-flex;align-items:center;user-select:none;-webkit-user-select:none;white-space:nowrap;max-width:100%;overflow:visible;box-sizing:border-box}
/* ボタン群は「シリーズ」ラベルの下に行幅いっぱいで配置し、安定した可用幅を確保する */
#ytSeriesBtns{display:flex;flex-wrap:wrap;gap:.35rem .4rem;align-items:flex-start;flex:1 1 100%;min-width:0;width:100%}
/* タイトルが1行に収まりきらないボタンは行幅いっぱい(フル行)に広げてから
   JS でフォントサイズだけを自動縮小する（文字の切り捨ては行わない） */
#ytSeriesBtns .yt-series-btn.yt-fit-full{flex:1 1 100%;max-width:100%;justify-content:flex-start}
/* 最終手段: 下限フォントでも1行に入らない極端なタイトルだけ折り返して全文表示（クリップ回避） */
#ytSeriesBtns .yt-series-btn.yt-fit-wrap{align-items:flex-start}
#ytSeriesBtns .yt-series-btn.yt-fit-wrap .yt-series-label{white-space:normal;word-break:break-word;overflow-wrap:anywhere}
.yt-series-label{user-select:text;-webkit-user-select:text;cursor:text;display:inline;white-space:nowrap;font-size:inherit}
.yt-series-label::selection{background:rgba(34,211,238,.4);color:#fff}
/* Now Playing エリア */
.yt-now-playing{width:100%;flex-basis:100%;margin-top:.35rem;padding-top:.45rem;border-top:1px solid rgba(148,163,184,.18);font-size:.72rem;line-height:1.55;display:flex;flex-direction:column;gap:.2rem}
.yt-now-label{font-weight:700;color:#94a3b8;letter-spacing:.06em;text-transform:uppercase;font-size:.62rem}
.yt-now-title{color:#f1f5f9;font-weight:600;user-select:text;-webkit-user-select:text;word-break:break-all;cursor:pointer;transition:color .15s}
.yt-now-title:hover{color:#22d3ee}
.yt-now-title::selection{background:rgba(34,211,238,.4);color:#fff}
.yt-now-url{color:#22d3ee;word-break:break-all;text-decoration:none;font-size:.68rem}
.yt-now-url:hover{text-decoration:underline}
@media(max-width:640px){
  .yt-now-title{display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;word-break:break-word}
  .yt-now-url{display:block}
}
/* 無料スマホ壁紙コーナー（Now Playing の下に表示） */
.yt-wp-corner{width:100%;flex-basis:100%;margin-top:.4rem;padding-top:.5rem;border-top:1px solid rgba(148,163,184,.18);display:flex;flex-direction:column;gap:.4rem}
.yt-wp-head{font-weight:800;color:#fbbf24;letter-spacing:.04em;font-size:.72rem;display:flex;align-items:center;gap:.3rem}
.yt-wp-hint{font-size:.58rem;color:#94a3b8;line-height:1.45}
.yt-wp-grid{display:flex;gap:.55rem;overflow-x:auto;padding-bottom:.35rem;-webkit-overflow-scrolling:touch;scrollbar-width:thin}
.yt-wp-grid::-webkit-scrollbar{height:6px}
.yt-wp-grid::-webkit-scrollbar-thumb{background:rgba(148,163,184,.4);border-radius:3px}
.yt-wp-item{flex:0 0 auto;width:80px;display:flex;flex-direction:column;gap:.22rem;text-align:center}
.yt-wp-thumb{display:block;width:80px;aspect-ratio:499/1080;object-fit:cover;border-radius:9px;border:1px solid rgba(148,163,184,.35);background:#0f172a;box-shadow:0 2px 10px rgba(0,0,0,.45);transition:border-color .15s,transform .15s}
.yt-wp-item:hover .yt-wp-thumb{border-color:#22d3ee;transform:translateY(-2px)}
.yt-wp-name{font-size:.54rem;color:#cbd5e1;line-height:1.28;word-break:break-word;min-height:2.1em}
.yt-wp-dl{font-size:.56rem;font-weight:800;color:#22d3ee;text-decoration:none;border:1px solid rgba(34,211,238,.5);border-radius:7px;padding:.15rem .2rem;display:block;white-space:nowrap}
.yt-wp-dl:hover{background:rgba(34,211,238,.16)}
.yt-bg-ctrl-sep{opacity:.4;color:#64748b;font-size:.7rem;user-select:none}
body.yt-only-mode>.site-nav-wrap,body.yt-only-mode>header,body.yt-only-mode>main,body.yt-only-mode>footer{display:none!important}
body.yt-only-mode .yt-bg-outer{z-index:100;pointer-events:auto}
body.yt-only-mode .yt-bg-vol-wrap{z-index:2147483647}
.yt-bg-ctx-menu{position:fixed;z-index:200;min-width:11rem;padding:.35rem 0;background:rgba(15,23,42,.96);border:1px solid rgba(34,211,238,.35);border-radius:10px;box-shadow:0 12px 40px rgba(0,0,0,.45);display:none;font-size:.78rem}
.yt-bg-ctx-menu button{display:block;width:100%;text-align:left;padding:.45rem .85rem;border:none;background:transparent;color:#e2e8f0;cursor:pointer;font:inherit}
.yt-bg-ctx-menu button:hover{background:rgba(34,211,238,.15);color:#fff}
.yt-bg-ctx-menu .yt-ctx-h{padding:.25rem .85rem .15rem;font-size:.62rem;letter-spacing:.06em;text-transform:uppercase;color:#64748b;font-weight:700}
/* stack page above background video */
body>:not(.yt-bg-outer):not(#ytBgVolumeWrap):not(#ytBgCtxMenu){position:relative;z-index:1}

#logoWrap{
 position:relative;
 width:100%;
 max-width:1200px;
 height:280px;
 margin:20px 0;
}
#logoGif{
 position:absolute;width:100%;height:100%;
 object-fit:contain;object-position:left center;opacity:.18;
}
#logoWrap canvas{
 position:absolute;width:100%;height:100%;
 top:0;left:0;
}
#logoModeBar{
 display:flex;justify-content:flex-start;gap:.5rem;margin:.4rem 0 .4rem;
 position:relative;z-index:10;flex-wrap:wrap;align-items:center;
}
.logo-mode-btn{
 font-size:.72rem;font-weight:700;padding:.38rem .85rem;border-radius:10px;
 border:1px solid rgba(34,211,238,.42);background:rgba(15,23,42,.65);
 color:#e0f2fe;cursor:pointer;transition:all .15s;
}
.logo-mode-btn:hover{border-color:rgba(34,211,238,.8);background:rgba(34,211,238,.18);color:#fff}
.logo-mode-btn.active{border-color:rgba(34,211,238,.9);background:rgba(34,211,238,.28);color:#fff;box-shadow:inset 0 0 0 1px rgba(255,255,255,.12)}

/* Dance Fixed のジャンル選択バー */
#danceFixedBar{
 display:flex;flex-wrap:wrap;gap:.3rem;margin:.2rem 0 1rem;
 padding:.4rem .55rem;border-radius:10px;
 background:rgba(15,23,42,.55);border:1px dashed rgba(34,211,238,.3);
 position:relative;z-index:10;
}
#danceFixedBar .df-label{
 font-size:.68rem;font-weight:700;color:#94a3b8;
 letter-spacing:.06em;margin-right:.35rem;align-self:center;
 white-space:nowrap;
}
.dance-fixed-btn{
 font-size:.66rem;font-weight:700;padding:.28rem .6rem;border-radius:8px;
 border:1px solid rgba(148,163,184,.4);background:rgba(15,23,42,.65);
 color:#cbd5e1;cursor:pointer;transition:all .15s;
}
.dance-fixed-btn:hover{border-color:rgba(34,211,238,.7);background:rgba(34,211,238,.14);color:#fff}
.dance-fixed-btn.active{border-color:#22d3ee;background:rgba(34,211,238,.3);color:#fff;box-shadow:inset 0 0 0 1px rgba(255,255,255,.15)}


/* =========================================================
   FULL TEXT MODE FOR LANGUAGE CARD & /top SUMMARY
   日本語・英語の長文を要約せず全文表示（横長・左右スクロール）
   注: 長い英語/日本語文章は .card-country (c.c / c.d) に入る
       .card-en は言語名（短い）なので対象外
========================================================= */

/* 長文(.card-country)は省略せず全文。縦の高さ制限は解除 */
.card-country,
.card .summary,
.card .excerpt{
  max-height:none !important;
  height:auto !important;
  -webkit-line-clamp:unset !important;
  text-overflow:unset !important;
}

/* スクロール制限解除（カード自体） */
.card--bio-scroll{
  overflow:visible !important;
}

/* 言語カードを横長に：グリッドを1カラムにして横幅いっぱいに */
.grid{
  grid-template-columns:1fr !important;
}

/* カード本体も横幅いっぱい */
.card{
  width:100% !important;
  max-width:100% !important;
}

/* bioScrollカードの長文（.card-country）のみ：横長・左右スクロール・左揃え */
.card--bio-scroll .card-country{
  display:block !important;
  width:100% !important;
  max-width:100% !important;
  box-sizing:border-box !important;
  font-size:1.0rem !important;
  line-height:1.95 !important;
  padding:1rem 1.6rem !important;
  text-align:left !important;
  background:rgba(0,0,0,0.22) !important;
  border-radius:14px !important;
  white-space:nowrap !important;
  overflow-x:auto !important;
  overflow-y:hidden !important;
  -webkit-overflow-scrolling:touch !important;
  overscroll-behavior-x:contain !important;
}

/* 横スクロールバーの見た目 */
.card-country::-webkit-scrollbar{height:9px}
.card-country::-webkit-scrollbar-thumb{background:rgba(34,211,238,.55);border-radius:5px}
.card-country::-webkit-scrollbar-thumb:hover{background:rgba(34,211,238,.85)}
.card-country::-webkit-scrollbar-track{background:rgba(255,255,255,.06);border-radius:5px}

/* 本文(.card-country: c.c / c.d)はリンクではないので、クリック/タップで
   画面遷移させず、マウス・指でテキスト選択＆コピーできるようにする */
.card-country{
  cursor:text !important;
  -webkit-user-select:text !important;
  -moz-user-select:text !important;
  -ms-user-select:text !important;
  user-select:text !important;
}

/* スマホ */
@media (max-width:768px){
  .card-country{
    font-size:1.0rem !important;
    line-height:1.9 !important;
    padding:.9rem 1rem !important;
  }
}



/* === 2026-06-08 YouTube操作パネル文字サイズ改善 === */
.yt-bg-vol-wrap,
.yt-bg-vol-wrap *{
    word-break: break-word !important;
    overflow-wrap: anywhere !important;
}

.yt-bg-vol-wrap label{font-size:1rem !important;}
.yt-bg-theme{font-size:1rem !important;}
.yt-bg-name-en{font-size:1rem !important;}
.yt-bg-search-row label{font-size:1rem !important;}
.yt-bg-search-row input[type=search]{font-size:1rem !important;}
.yt-bg-search-row .yt-bg-btn{font-size:1rem !important;}
.yt-bg-mini-btn{font-size:1rem !important;}

#ytNowPlaying,
.yt-now-playing,
.yt-title,
.yt-url,
.yt-desc,
.yt-description{
    font-size:1rem !important;
    line-height:1.5 !important;
    white-space:normal !important;
    word-break:break-word !important;
    overflow-wrap:anywhere !important;
}
/* === end === */

</style>

</head>
<body>


<?php
$mlja = ($lang === 'ja');
$return_label = '🌐 Back to Language Cards';

if ($lang === 'ja') {
  $return_label = '🌐 言語カード選択に戻る';
} elseif ($lang === 'en') {
  $return_label = '🌐 Back to Language Cards';
} elseif ($lang === 'ru') {
  $return_label = '🌐 Вернуться к выбору языка';
} elseif ($lang === 'zh-CN' || $lang === 'cn') {
  $return_label = '🌐 返回语言卡选择';
} elseif ($lang === 'ko') {
  $return_label = '🌐 언어 카드 선택으로 돌아가기';
} elseif ($lang === 'fa') {
  $return_label = '🌐 بازگشت به انتخاب زبان';
} elseif ($lang === 'ar') {
  $return_label = '🌐 العودة إلى اختيار اللغة';
}
?>


<?php if ($is_mixlist): ?>
<div id="root-back-link-wrap"
     style="
      position:relative;
      z-index:99999;
      padding-top:120px;
      padding-left:18px;
      margin-bottom:18px;
     ">

  <a href="https://audiocafe.tokyo/?google_translate=0"
     id="root-back-link"
     target="_top"
     rel="noopener"
     onclick="return forceRootHome(event);"
     style="
      display:inline-block;
      font-size:2.4em;
      font-weight:bold;
      line-height:1.6;
      text-decoration:none;
      color:#00ccff;
      background:rgba(0,0,0,0.78);
      padding:16px 24px;
      border-radius:18px;
      box-shadow:0 4px 18px rgba(0,0,0,0.35);
      position:relative;
      z-index:99999;
      <?= ($lang !== 'ja') ? 'margin-top:120px;' : 'margin-top:90px;' ?>
     ">

     ⬅ 言語選択前の https://audiocafe.tokyo ルートに戻る

  </a>

</div>

<script>
/* [v23] PHP file_exists() による同ドメイン配下パス存在チェック結果 */
var __AC_NAV_LOCAL = <?= json_encode($_ac_nav_local, JSON_THROW_ON_ERROR) ?>;
</script>
<script>
console.log('%c[YT-BG v15] Play in Background チェックボックス追加・非背景モード外部遷移ループ実装', 'background:#0f172a;color:#22d3ee;padding:4px 8px;border-radius:4px;font-weight:700');
function forceRootHome(e){

  e.preventDefault();

  // Google Translate iframe対策
  try{
    if(window.location.hostname.indexOf('translate.goog') !== -1){
      window.top.location.href = 'https://audiocafe.tokyo/';
      return false;
    }
  }catch(err){}

  // 翻訳Cookie削除
  document.cookie = 'googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
  document.cookie = 'googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=.audiocafe.tokyo;';

  // 強制的に翻訳無しルートへ
  window.top.location.replace('https://audiocafe.tokyo/');

  return false;
}
</script>

<style>
@media (max-width:768px){

  #root-back-link-wrap{
    padding-top:140px !important;
    padding-left:12px !important;
  }

  #root-back-link{
    font-size:1.65em !important;
    line-height:1.7 !important;
  }

}
</style>
<?php endif; ?>



<script>
/* 日本語カード選択でこのページに来た場合: URLの ?lang=ja と _ac_nav を消してクリーンに保つ
   （言語カードページ自体は翻訳しないため、パラメータは表示専用で不要） */
(function(){
  try{
    var u = new URL(location.href);
    var changed = false;
    if(u.searchParams.get('lang') === 'ja'){ u.searchParams.delete('lang'); changed = true; }
    if(u.searchParams.has('_ac_nav')){ u.searchParams.delete('_ac_nav'); changed = true; }
    if(u.searchParams.has('_ac_fixtop')){ u.searchParams.delete('_ac_fixtop'); changed = true; }
    if(changed){
      history.replaceState(null, '', u.pathname + (u.search ? u.search : '') + u.hash);
    }
  }catch(e){}
})();
</script>

<div class="yt-bg-outer" aria-hidden="true"><div id="yt-bg-player-host"></div>
  <div id="introTimerBox" style="position:absolute;top:10px;right:10px;background:rgba(0,0,0,.75);color:#fff;padding:6px 12px;border-radius:10px;font-weight:700;font-size:.85rem;z-index:50;display:none;backdrop-filter:blur(6px);border:1px solid rgba(255,255,255,.25);font-variant-numeric:tabular-nums;letter-spacing:.04em">
    次の動画まで <span id="introTimerValue">60</span>秒
  </div>
  <div id="introTimerBar" style="position:absolute;left:0;bottom:0;height:3px;width:100%;background:linear-gradient(90deg,#22d3ee,#4ade80);transform-origin:left;z-index:50;display:none"></div>
</div>
<div class="yt-bg-vol-wrap" id="ytBgVolumeWrap" role="group" aria-label="Background YouTube volume">
  <span id="ytSeriesLabel" style="display:none"></span>
  <div class="yt-close-row" style="margin-bottom:.4rem">
    <button type="button" id="ytPanelClose" style="font-size:clamp(0.7rem, 2.2vw, 0.95rem);font-weight:800;padding:clamp(0.4rem, 1.5vw, 0.7rem) clamp(0.8rem, 3vw, 1.4rem);border-radius:14px;border:2px solid rgba(34,211,238,.8);background:rgba(15,23,42,.95);color:#22d3ee;cursor:pointer;white-space:nowrap;backdrop-filter:blur(12px);box-shadow:0 4px 24px rgba(34,211,238,.3),0 8px 32px rgba(0,0,0,.5);letter-spacing:.06em">✕ CLOSE</button>
  </div>
  <div class="yt-now-playing" id="ytNowPlaying">
    <span class="yt-now-label">▶ Now Playing</span>
    <span class="yt-now-title" id="ytNowTitle" title="タップ → 動画/ページを開く">— Title Unknown —</span>
    <a class="yt-now-url" id="ytNowUrl" href="#" data-no-translate="1" target="_blank" rel="noopener noreferrer"></a>
  </div>
  <div style="display:flex;align-items:center;gap:.6rem;width:100%">
    <label for="ytBgVolume" style="white-space:nowrap;flex-shrink:0;font-size:.8rem;font-weight:700;color:#e2e8f0;letter-spacing:.06em">VOLUME</label>
    <input type="range" id="ytBgVolume" min="0" max="100" value="0" step="1" style="flex:1;min-width:0" />
  </div>
  <div class="yt-bg-ctrl-row">
    <button type="button" class="yt-bg-mini-btn" id="ytBgPrev">◀ BACK</button>
    <button type="button" class="yt-bg-mini-btn" id="ytBgRew">REW</button>
    <span class="yt-bg-ctrl-sep" aria-hidden="true">|</span>
    <button type="button" class="yt-bg-mini-btn" id="ytBgPlayBtn">Play</button>
    <button type="button" class="yt-bg-mini-btn" id="ytBgStopBtn">Stop</button>
    <span class="yt-bg-ctrl-sep" aria-hidden="true">|</span>
    <button type="button" class="yt-bg-mini-btn" id="ytBgFf">FF</button>
    <button type="button" class="yt-bg-mini-btn" id="ytBgNext">NEXT ▶</button>
  </div>
  <div class="yt-bg-ctrl-row" style="margin-top:.3rem">
    <button type="button" class="yt-bg-mini-btn" id="ytBgLoopToggle" aria-pressed="false">Loop OFF</button>
    <span class="yt-bg-ctrl-sep" aria-hidden="true">|</span>
    <span id="ytBgStatusLabel" style="font-size:.72rem;font-weight:700;color:#67e8f9;letter-spacing:.04em">Stop Now</span>
  </div>
  <div class="yt-bg-search-row">
    <input type="text" id="ytBgSearch" value="" placeholder="URL または 検索ワード" autocomplete="off" enterkeyhint="go" />
    <button type="button" class="yt-bg-mini-btn" id="ytBgSearchApply">SEARCH</button>
    <button type="button" class="yt-bg-mini-btn" id="ytBgOnlyToggle" aria-pressed="false">YouTube</button>
  </div>
  <div class="yt-bg-search-row" style="border-top:1px solid rgba(148,163,184,.18);margin-top:.3rem;padding-top:.45rem;gap:.35rem .45rem">
    <span style="font-size:.72rem;font-weight:700;color:#cbd5e1;white-space:nowrap">シリーズ</span>
    <span id="ytSeriesBtns"></span>
  </div>
  <div class="yt-wp-corner" id="ytWallpaperCorner">
    <span class="yt-wp-head">🎁 無料 スマホ壁紙コーナー</span>
    <span class="yt-wp-hint">画像をタップで原寸表示 →「⬇ 保存」または画像長押しで端末に保存できます。</span>
    <div class="yt-wp-grid">
      <div class="yt-wp-item">
        <a href="https://stat.ameba.jp/user_images/20260505/16/www-aon/87/8b/p/o0499108015778827097.png" target="_blank" rel="noopener noreferrer">
          <img class="yt-wp-thumb" loading="lazy" src="https://stat.ameba.jp/user_images/20260505/16/www-aon/87/8b/p/o0499108015778827097.png?caw=200" alt="NTT IOWN 井上飛鳥さん 黒服 スマホ壁紙">
        </a>
        <span class="yt-wp-name">NTT IOWN 井上飛鳥さん 黒服</span>
        <a class="yt-wp-dl" href="https://stat.ameba.jp/user_images/20260505/16/www-aon/87/8b/p/o0499108015778827097.png" download="ino-asuka-black.png" target="_blank" rel="noopener noreferrer">⬇ 保存</a>
      </div>
      <div class="yt-wp-item">
        <a href="https://stat.ameba.jp/user_images/20260505/16/www-aon/1e/87/j/o0499108015778824305.jpg" target="_blank" rel="noopener noreferrer">
          <img class="yt-wp-thumb" loading="lazy" src="https://stat.ameba.jp/user_images/20260505/16/www-aon/1e/87/j/o0499108015778824305.jpg?caw=200" alt="NTT IOWN 井上飛鳥さん 白服 スマホ壁紙">
        </a>
        <span class="yt-wp-name">NTT IOWN 井上飛鳥さん 白服</span>
        <a class="yt-wp-dl" href="https://stat.ameba.jp/user_images/20260505/16/www-aon/1e/87/j/o0499108015778824305.jpg" download="ino-asuka-white.jpg" target="_blank" rel="noopener noreferrer">⬇ 保存</a>
      </div>
      <div class="yt-wp-item">
        <a href="https://stat.ameba.jp/user_images/20260505/16/www-aon/c2/90/p/o0499108015778820256.png" target="_blank" rel="noopener noreferrer">
          <img class="yt-wp-thumb" loading="lazy" src="https://stat.ameba.jp/user_images/20260505/16/www-aon/c2/90/p/o0499108015778820256.png?caw=200" alt="大阪の神様ビリケン スマホ壁紙">
        </a>
        <span class="yt-wp-name">大阪の神様ビリケン</span>
        <a class="yt-wp-dl" href="https://stat.ameba.jp/user_images/20260505/16/www-aon/c2/90/p/o0499108015778820256.png" download="osaka-billiken.png" target="_blank" rel="noopener noreferrer">⬇ 保存</a>
      </div>
      <div class="yt-wp-item">
        <a href="https://stat.ameba.jp/user_images/20260505/18/www-aon/16/38/p/o0498108015778869116.png" target="_blank" rel="noopener noreferrer">
          <img class="yt-wp-thumb" loading="lazy" src="https://stat.ameba.jp/user_images/20260505/18/www-aon/16/38/p/o0498108015778869116.png?caw=200" alt="大型スピーカー紹介のTONOさん スマホ壁紙">
        </a>
        <span class="yt-wp-name">大型スピーカー紹介のTONOさん</span>
        <a class="yt-wp-dl" href="https://stat.ameba.jp/user_images/20260505/18/www-aon/16/38/p/o0498108015778869116.png" download="tono-speaker.png" target="_blank" rel="noopener noreferrer">⬇ 保存</a>
      </div>
    </div>
  </div>
</div>
<!-- パネルが閉じている時に表示するOPENボタン -->
<button type="button" id="ytPanelOpen" style="display:none;position:fixed;top:1rem;right:1rem;z-index:2147483647;font-size:clamp(0.7rem, 2.2vw, 0.95rem);font-weight:800;padding:clamp(0.4rem, 1.5vw, 0.7rem) clamp(0.8rem, 3vw, 1.4rem);border-radius:14px;border:2px solid rgba(34,211,238,.8);background:rgba(15,23,42,.95);color:#22d3ee;cursor:pointer;backdrop-filter:blur(12px);box-shadow:0 4px 24px rgba(34,211,238,.3),0 8px 32px rgba(0,0,0,.5);letter-spacing:.06em">▶ OPEN</button>

<nav class="site-nav-wrap" aria-label="All domains"><span class="sn-title">Domains — home / top</span><p class="sn-gh" style="margin:.35rem 0 0;font-size:.62rem;text-align:center"><a href="https://github.com/aon-tokyo/aon/blob/cursor/audiocafe-vite-static-4d1a/aon.co.jp/index.html" style="color:var(--cyan)" >GitHub でこのページのソースを表示</a></p><div class="site-nav-rows"><div class="site-nav-row"><span class="sn-d">audiocafe.tokyo</span><span class="sn-sep">·</span><a href="https://audiocafe.tokyo/">/</a><span class="sn-sep">·</span><a href="https://audiocafe.tokyo/top/">/top</a></div></div><div style="width:100%;max-width:52rem;margin:.55rem auto 0;padding:.55rem .75rem .45rem;border-top:1px solid rgba(255,255,255,.08);text-align:left"><div style="font-weight:700;font-size:.58rem;letter-spacing:.08em;text-transform:uppercase;color:#64748b;margin-bottom:.35rem">GitHub — <code style="font-size:.62rem;background:rgba(255,255,255,.08);padding:.1rem .25rem;border-radius:3px">index.html</code> <span style="font-weight:500;text-transform:none;color:#64748b">(root · branch <code style="font-size:.62rem">cursor/audiocafe-vite-static-4d1a</code>)</span></div><table style="width:100%;border-collapse:collapse;font-size:.62rem;line-height:1.45"><thead><tr style="border-bottom:1px solid rgba(255,255,255,.12)"><th style="text-align:left;padding:.2rem .35rem .35rem 0;color:#cbd5e1;font-weight:600;width:7.5rem">ドメイン</th><th style="text-align:left;padding:.2rem 0 .35rem;color:#cbd5e1;font-weight:600">リンク</th></tr></thead><tbody></tbody></table></div></nav>


<header class="header">
  <div id="logoWrap">
    <img id="logoGif" alt="AUDIOCAFE logo">
    <canvas id="logoCanvas"></canvas>
  </div>
  <div id="logoModeBar">
    <button class="logo-mode-btn active" data-mode="scroll">🌈 スクロール</button>
    <button class="logo-mode-btn" data-mode="dance">💃 Dance</button>
    <button class="logo-mode-btn" data-mode="danceFixed">🎯 Dance Fixed</button>
    <button class="logo-mode-btn" data-mode="danceAI">🤖 Dance AI</button>
    <button class="logo-mode-btn" data-mode="wave">🌊 波</button>
    <button class="logo-mode-btn" data-mode="explode">💥 爆発</button>
    <button class="logo-mode-btn" data-mode="orbit">🌀 オービット</button>
  </div>
  <div id="danceFixedBar">
    <span class="df-label">Genre →</span>
    <button class="dance-fixed-btn active" data-genre="kabuki">🎭 歌舞伎</button>
    <button class="dance-fixed-btn" data-genre="egypt">🏛 エジプト</button>
    <button class="dance-fixed-btn" data-genre="india">🕉 インド</button>
    <button class="dance-fixed-btn" data-genre="hiphop">🎤 HIPHOP</button>
    <button class="dance-fixed-btn" data-genre="kpop">💖 K-POP</button>
    <button class="dance-fixed-btn" data-genre="latin">💃 ラテン</button>
    <button class="dance-fixed-btn" data-genre="orchestra">🎻 オーケストラ</button>
    <button class="dance-fixed-btn" data-genre="jazz">🎷 JAZZ</button>
    <button class="dance-fixed-btn" data-genre="ethnic">🪘 民族</button>
    <button class="dance-fixed-btn" data-genre="freestyle">✨ フリースタイル</button>
  </div>
  <audio id="audio" src="/audio/bg.wav" loop preload="none"></audio>
  <a href="https://audiocafe.tokyo/" class="logo" style="display:none">audiocafe.tokyo</a>
  <p class="subtitle">Please select your native language.</p>
  <p class="note">あなたの母国語を選択してください。2回目の選択時はブラウザを閉じて再度開いてください。<br>動画を視聴するには日本語を選択してください。</p>
  <br>
  <br>
  <li><a href="https://aon.tokyo/cancer" target="_blank" rel="noopener noreferrer">Cancer Therapy. ガン治療法はこちら。</a></li>
  <br>
  <br>

  <div class="search-wrap">
    <svg class="search-icon" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
    <input type="text" class="search" id="search" placeholder="Search languages..." autocomplete="off">
  </div>
  <div class="pills" id="pills"></div>
</header>

<main class="main" id="main"></main>
<div id="acNavChoiceModal" class="ac-nav-modal" hidden aria-modal="true" role="dialog" aria-labelledby="acNavChoiceTitle">
  <div class="ac-nav-modal__backdrop" data-ac-nav-dismiss="1"></div>
  <div class="ac-nav-modal__box">
    <div class="ac-nav-modal__scroll">
      <p id="acNavChoiceTitle" class="ac-nav-modal__title">Google翻訳サイトを表示しますか？</p>
      <p class="ac-nav-modal__hint">選んだ言語で開きます。日本語の場合は翻訳なしで開きます。ロシア語カードでは下に追加リンクが出ます。</p>
      <div class="ac-nav-modal__btns">
        <button type="button" class="ac-nav-modal__btn ac-nav-modal__btn--primary" id="acNavBtnAc" data-ac-nav-dest="ac">audiocafe.tokyo</button>
        <button type="button" class="ac-nav-modal__btn ac-nav-modal__btn--primary" id="acNavBtnAcTop" data-ac-nav-dest="acTop">audiocafe.tokyo/top</button>
        <button type="button" class="ac-nav-modal__btn ac-nav-modal__btn--primary" id="acNavBtnAruaru" data-ac-nav-dest="aruaru">audiocafe.tokyo/aruaru</button>
        <button type="button" class="ac-nav-modal__btn ac-nav-modal__btn--primary" id="acNavBtnAruaruLady" data-ac-nav-dest="aruaruLady">audiocafe.tokyo/aruaru-lady</button>
        <button type="button" class="ac-nav-modal__btn ac-nav-modal__btn--primary" id="acNavBtnRakutenMobile" data-ac-nav-dest="rakutenMobile">📶 audiocafe.tokyo/rakuten-mobile</button>
        <button type="button" class="ac-nav-modal__btn ac-nav-modal__btn--primary" id="acNavBtnMixlist" data-ac-nav-dest="mixlist">📋 言語カード＆/top 要約LIST</button>
        <button type="button" class="ac-nav-modal__btn ac-nav-modal__btn--primary" id="acNavBtnAruaruTokyo" onclick="window.open('https://aruaru.tokyo/', '_blank', 'noopener')">🎲 aruaru.tokyo</button>
        <button type="button" class="ac-nav-modal__btn ac-nav-modal__btn--google" id="acNavBtnGoogleTrans">🌐 Google翻訳サイトへ移動</button>
        <div id="acNavExtraBtns" class="ac-nav-modal__extra" aria-live="polite"></div>
        <button type="button" class="ac-nav-modal__btn" id="acNavBtnCancel">元の画面に戻る</button>
      </div>
    </div>
  </div>
</div>


<footer class="footer">
  <p>Copyright &copy; 2025 <a href="https://audiocafe.tokyo/">audiocafe.tokyo</a>, Akiru Akiruno-City Tokyo Japan. All Rights Reserved.</p>
  <p>Powered by Google Translate</p>
</footer>

<script>
(function(){
"use strict";
var L=[
{g:"fa",n:"Persian",t:"\u0641\u0627\u0631\u0633\u06CC",a:"IRAN (Persian)",r:"Middle East",c:"The hills of Iran (Persia) are the root of the roots of religions, including Judaism, Christianity, Islam, Brahmanism, and Zoroastrianism.",d:"イラン（ペルシャ）の丘は、ユダヤ教、キリスト教、イスラム教、バラモン教、ゾロアスター教など宗教のルーツの中のルーツです。",cardLinks:[{href:"https://ameblo.jp/www-aon/entry-12969548131.html",label:"Iran is the cradle of religions such as Judaism."},{href:"https://ameblo.jp/www-aon/entry-12969547840.html",label:"イラン（ペルシャ）の丘は、ユダヤ教を始め宗教のルーツです。"},{href:"https://www.youtube.com/results?search_query=トランプがネタニヤフに激怒　イスラエルはレバノンへの攻撃を停止すべきだ　米イラン合意95％を阻むイスラエル　ネタニヤフ首相は自身のスキャンダル隠ぺいの為に戦争を起こしていますので問題です。",label:"トランプがネタニヤフに激怒　イスラエルはレバノンへの攻撃を停止すべきだ　米イラン合意95％を阻むイスラエル　ネタニヤフ首相は自身のスキャンダル隠ぺいの為に戦争を起こしていますので問題です。"},{href:"https://www.youtube.com/results?search_query=Trump+furious+at+Netanyahu+Israel+should+stop+attacking+Lebanon+US+Iran+deal+Netanyahu+scandal+coverup+war",label:"Trump is furious at Netanyahu: Israel should halt its attacks on Lebanon. Israel is blocking 95% of the US–Iran agreement. It is a serious problem that Prime Minister Netanyahu is waging war to cover up his own scandals."}],bioScroll:1,fc:"ir"},
{g:"ja",n:"Japanese",t:"\u65E5\u672C\u8A9E",a:"JAPAN",r:"Asia",c:"If the Japanese who speak Japanese—a hybrid of Hebrew and Sumerian—are the true Jews, then it would seem that Japan should govern Israel as well.\nIt is said that the ancestors of the Japanese were a mixture of the true Jews and Sumerians; the Ama people (Amur people), the royal lineage that ruled ancient Rome and ancient Greece; the chieftain called Ashiatouan—the origin of the name “Asia”—who long ago, through the Katakamuna, spread the concept of yin and yang (in and yo) to Korea and China, including a health practice of wearing T-shirts printed with the cosmic yin-yang; the Ainu people who crossed over to China and invented the kanji (Chinese characters); and lineages such as the Tenson-kōrin (the descent of the heavenly grandson).\n\nWhen the consumption tax was introduced, the corporate tax rate was about 40% at the time, and the tax was introduced on the premise that the corporate tax rate would eventually be lowered to about 20%. To abolish the consumption tax, it would be enough to raise the corporate tax back from about 40% to around 45% if that is still insufficient. If more revenue is still needed, you can push ahead with turning government offices and agencies into paperless, online-application websites through e-government (also called digital government), carry out large-scale reductions in the civil service, put government finances on a sounder footing, and then secure budgets for things such as: low birth rate and aging policies like Russia's program of giving away houses; the basic pension plus employee pension plus supplementary pension for all participants; making late-stage elderly medical care free for low- and middle-income groups; replacement budgets that cover all loan repayments when people become unemployed—including housing and car loans—plus free PCs; free internet providers and replacement budgets so smartphones and mobile bills are effectively free; and basic income.　　The Christian Bible states that the persecuted Jews fled to the far eastern edge of the earth, the land where the sun rises, an island nation. Japan's former Prime Minister Toshiki Kaifu's family lineage is descended from the Amur people (the Ama), who are themselves descendants of the royal families that ruled ancient Greece and ancient Rome.",d:"ヘブライ語とシュメール語のハイブリッドな日本語を話す日本人が真のユダヤ人ならイスラエルも日本が統治するべきだと思われます。\n日本人が真のユダヤ人とシュメール人と古代ローマと古代ギリシャを収めた王族のアマ人（アムール人）や、アジアの名の由来のアシアトウアンと言う族長でカタカムナと言う昔、陰と陽の考え方を韓国や中国にも広めて、宇宙の陰陽を印刷したＴシャツを着る健康法や中国に渡って漢字を発明したアイヌ人や、天孫降臨系などの混血が日本人の先祖と言われております。\n\n消費税が導入された時の法人税は当時約40％で法人税を将来約20％に下げる前提で、消費税が導入されました。消費税を撤廃するには、法人税を元の約40％から足りなければ45％くらいに引き上げれば良いのです。更に足りなければ、eガバメント、別名デジタルガバメントのお役所や政府のペーパーレス、オンライン申請などWEBサイト化を進めて、公務員の大幅なリストラを行ない、政府の財源の健全化を行ない、ロシアの様に家を一軒プレゼントする様な少子高齢化予算、全員加入の基礎年金＋厚生年金＋増額年金予算、後期高齢者医療制度の低所得層と中間所得層の無償化、失業時の住宅ローンや自動車ローンなど全てのローン返済の代替え返済予算やPC本体や、インターネットプロバイダー無償化やスマホ本体代金＋携帯電話利用料金の代替えで無償化や、ベーシックインカムなどの予算確保が可能となります。　　キリスト教の聖書には、迫害されたユダヤ人たちが、何処に逃げ延びたかが、「遥か東の果て。太陽昇る土地。島国。」と書いてあります。日本の海部俊樹元首相の家系はアムール人（アマ人）の末裔で、古代ギリシャや古代ローマを収めた王族の末裔です。",cardLinks:[{href:"https://ameblo.jp/www-aon/entry-12970014137.html",label:"235万円/月 デジタル庁次世代サービスグランドデザイン策定案件"},{href:"https://ameblo.jp/www-aon/entry-12970170838.html",label:"トンネルコンポスト方式で、ごみと生ごみを燃やさずに発酵させると、燃料と肥料が出来ます。燃やす前に排気口に日立造船のフィルターを施工しておくと水銀も回収出来ます。"},{href:"https://ameblo.jp/www-aon/entry-12970171082.html",label:"業務用の強力なマイクロ波を発生する電子レンジでゴミのプラスチックを石油にして販売して売り上げを出せます。"},{href:"https://ameblo.jp/www-aon/entry-12970171334.html",label:"燃えるごみとプラスチックごみから燃料ができます。"},{href:"https://ameblo.jp/www-aon/entry-12970171624.html",label:"スマホなど様々な産業廃棄物の灰から取れるレアアースのそれぞれ種類が違う様です。"},{href:"https://ameblo.jp/www-aon/entry-12970171769.html",label:"日本の海底の泥からレアアース"},{href:"https://aonceo.blogspot.com/2026/06/blog-post_18.html",label:"ヤマダホームとパナホームの シーリングレス(コーキングレス)のオプションでメンテナンスフリーに近い外壁を選べるそうなので教えて"},{href:"https://aonceo.blogspot.com/2026/05/7140-vs-vs.html",label:"パナソニックホームズ:震度7の地震を140回耐える実験をクリアし、繰り返しの地震に最強レベルの強さを誇る。 VS 大和ハウス(ジーヴォ):制震装置「グランデバイス」を搭載し、巨大地震や繰り返しの地震に強い。 VS ヤマダホーム では、メリット デメリットとどちらが優れていると思いますか? 5月 02, 2026"},{href:"https://ameblo.jp/www-aon/entry-12969811759.html",label:"ノーベル化学賞：空気から金（宝）を抽出するMOFの開発／京都大学特別教授・北川進"},{href:"https://ameblo.jp/www-aon/entry-12969862549.html",label:"核武装している国には、仕返しが怖いので核攻撃しないのが国際的な流れです。"},{href:"https://ameblo.jp/www-aon/entry-12969546249.html",label:"Japan Should Govern Israel."},{href:"https://ameblo.jp/www-aon/entry-12969546151.html",label:"日本人が真のユダヤ人なら平和の為にイスラエルの真の統治者になった方が良い理由。"},{href:"https://ameblo.jp/www-aon/entry-12970079689.html",label:"この世界が仮想現実である証拠がついに発見か...シミュレーション仮説で判明したこの宇宙の本当の正体とは【都市伝説】"},{href:"https://www.youtube.com/shorts/L9fnLxAzwuw",label:"山の上に貯水ダムを建設して日中の余剰電力で海水を巨大なポンプで山の上まで汲み、夕方は放流して発電します。"},{href:"https://www.youtube.com/watch?v=l1UGJ-iarug",label:"日本がついに海水から水素生成に成功！中性海水電解セル"},{href:"https://www.youtube.com/results?search_query=%E5%B8%B8%E6%B8%A9%E6%A0%B8%E8%9E%8D%E5%90%88%E3%80%80%E3%82%B9%E3%83%88%E3%83%BC%E3%83%96",label:"常温核融合　ストーブ"},{href:"https://www.youtube.com/watch?v=C8Szkx4ILwQ",label:"It is said that Japanese stone tablets and a massive UFO were excavated from underground near the Pyramids and the Sphinx in Egypt, as well as in the Inca region."},{href:"https://youtu.be/C8Szkx4ILwQ",label:"エジプトのピラミッドとスフィンクスとインカの地下から日本語の石板と巨大なUFOが発掘されたそうです。"},{href:"https://www.youtube.com/results?search_query=%E4%B8%96%E7%95%8C%E4%B8%AD%E3%81%A7%E3%82%B3%E3%82%B9%E3%83%97%E3%83%AC%E3%80%80%E3%82%B3%E3%83%9F%E3%82%B1%E3%80%80%E3%82%A2%E3%83%8B%E3%83%A1%E3%82%BD%E3%83%B3%E3%82%B0%E3%83%96%E3%83%BC%E3%83%A0%E3%80%80%E3%82%A2%E3%83%8B%E3%82%BD%E3%83%B3%E3%83%90%E3%83%BC",label:"Global Cosplay & Anime Song Boom (世界的コミケ、アニソンブーム)"},{href:"https://www.youtube.com/results?search_query=%E3%82%A4%E3%82%BF%E3%83%AA%E3%82%A2%E3%80%80%E3%83%95%E3%83%A9%E3%83%B3%E3%82%B9%E3%80%80%E3%83%89%E3%82%A4%E3%83%84%E3%80%80%E4%B8%96%E7%95%8C%E4%B8%AD%E3%81%A7%E7%A5%9E%E9%81%93%E3%83%96%E3%83%BC%E3%83%A0",label:"Italy, France, Germany — Shinto Boom Worldwide (イタリア フランス ドイツ 世界中で神道ブーム)"},{href:"https://www.youtube.com/watch?v=wi4itXp4p-I",label:"Birth of Bleaching-Resistant Coral (白化知らずのサンゴ誕生)"},{href:"https://www.youtube.com/watch?v=K_U6ln18KTo",label:"Research on Coral Resistant to Summer High Water Temperatures (夏場の高水温に強いサンゴの研究 日本財団 海と日本PROJECT in 沖縄県 2021)"},{href:"https://www.google.com/search?q=%E7%94%A3%E6%A5%AD%E5%BB%83%E6%A3%84%E7%89%A9%E3%81%AA%E3%81%A9%E3%81%AE%E7%89%B9%E6%AE%8A%E3%81%AA%E3%81%A9%E3%81%AE%E6%A7%98%E3%81%AA%E7%84%BC%E5%8D%B4%E7%81%B0%E3%81%8B%E3%82%89%E3%81%A9%E3%81%AE%E6%A7%98%E3%81%AA%E3%83%AC%E3%82%A2%E3%82%A2%E3%83%BC%E3%82%B9%E3%81%8C%E5%8F%96%E3%82%8C%E3%81%BE%E3%81%99%E3%81%8B%EF%BC%9F&udm=14",label:"産業廃棄物などの特殊などの様な焼却灰からどの様なレアアースが取れますか？"},{href:"https://youtu.be/2uK5VXtUlmc?si=gjMCWHhG74RxTkxv",label:"石炭から大量のレアアース！"},{href:"https://www.youtube.com/results?search_query=%E6%97%A5%E6%9C%AC%E3%81%AE%E6%B5%B7%E5%BA%95%E6%B3%A5%E6%B2%BC%E3%81%8B%E3%82%89%E3%83%AC%E3%82%A2%E3%82%A2%E3%83%BC%E3%82%B9",label:"日本の海底泥沼からレアアース"},{href:"https://www.youtube.com/results?search_query=%E6%97%A5%E6%9C%AC%E3%81%AE%E7%86%B1%E6%B0%B4%E9%89%B1%E5%BA%8A%E3%81%8B%E3%82%89%E9%87%91",label:"日本の熱水鉱床から金"},{href:"https://www.google.com/search?q=%E5%A1%A9%E5%8C%96%E6%B0%B4+%E3%81%8B%E3%82%89%E3%83%90%E3%82%AF%E3%83%86%E3%83%AA%E3%82%A2%E3%81%8C+%E7%84%A1%E5%AE%B3%E3%81%AA24%E9%87%91&udm=50",label:"Google AI モード：塩化水 からバクテリアが 無害な24金"},{href:"https://www.youtube.com/results?search_query=%E6%9D%B1%E8%8A%9D%E3%80%80%E7%96%91%E4%BC%BC%E9%87%8F%E5%AD%90%E3%82%B3%E3%83%B3%E3%83%94%E3%83%A5%E3%83%BC%E3%82%BF%E3%83%BC",label:"東芝　疑似量子コンピューター"},{href:"https://www.youtube.com/watch?v=xMUA_RvpgmQ",label:"電気代1万分の一　“脳型半導体”ニューロモルフィック"},{href:"https://www.youtube.com/watch?v=6-n_sn0T_nM",label:"次世代EUV光源"},{href:"https://www.youtube.com/watch?v=oZatV8bW4BU&t=0s",label:"小型・省エネEUV露光技術"},{href:"https://www.youtube.com/watch?v=ylAbOPUCZ18",label:"新型　EUV　沖縄"},{href:"https://www.youtube.com/results?search_query=%E5%8D%8A%E5%B0%8E%E4%BD%93%E3%80%80%E3%82%B9%E3%83%94%E3%83%B3%E3%83%88%E3%83%AD%E3%83%8B%E3%82%AF%E3%82%B9",label:"スピントロニクス"},{href:"https://www.youtube.com/results?search_query=MRAM",label:"MRAM"},{href:"https://www.youtube.com/results?search_query=SAIMEMORY",label:"SAIMEMORY"},{href:"https://www.youtube.com/results?search_query=%E3%83%A6%E3%83%8B%E3%83%95%E3%82%A1%E3%82%A4%E3%83%89%E3%83%A1%E3%83%A2%E3%83%AA",label:"Unified Memory"}],bioScroll:1,fc:"jp"},
{g:"ar",n:"Arabic (Egypt)",t:"\u0627\u0644\u0639\u0631\u0628\u064A\u0629 (\u0645\u0635\u0631)",a:"EGYPT",r:"Middle East",c:"It is said that Japanese stone tablets and a massive UFO were excavated from underground near the Pyramids and the Sphinx in Egypt, as well as in the Inca region. The Quran (Koran) of Islam was created by translating the Christian Bible into Arabic. The representative of the translators is the Muhammad brothers. The first thing that the people who translated the Bible into Arabic and created the Quran built in Islamic countries was Christian churches. When I was a student, I probably saw on NHK Channel 3 TV that, while that church was being cleaned, images of Christ, crosses, and similar items emerged from inside; I believe that should be broadcast on TV news around the world.\n\nMuslim believers around the world are now working to defeat the DS (Deep State), the Great Reset, and other genocide schemes that plan to destroy all of humanity other than Christians, Jews, and white people. To stop those who seek to destroy the world through misguided interpretations of the Holy Bible, what is spreading around the world right now — surpassing Christianity, Judaism, and Islam — is Japan’s Shinto faith in the eight million gods (yaoyorozu no kami), a nature-based religion, as well as the hidden-god Yahweh faith found in Japanese shrines. Surpassing even those is the growing global boom in the Japanese language (an ancient tongue that later influenced Sumerian and Hebrew), Japanese cuisine, visits to Shinto shrines, Buddhist temples, and hot springs in Nara and Kyoto, and the worldwide explosion of Japanese manga, anime, and gaming — with people passionately declaring that dressing up in costume as their favorite characters and singing anime songs in Japanese is their very reason for living. These are the heartfelt feelings of people all around the world who love Japanese culture with such fervor.\n\nOn top of all that, as Japan becomes a leader of initiatives for Asia and the world, it is our sincere hope that Iran, Iraq, Saudi Arabia, Dubai, Egypt, Asia, Russia, Ukraine, America, India, and all nations of the world will take each other’s hands, and that through love, courage, and friendship, the whole world will become a leader of global initiatives — through things like a YouTube video by a Japanese university professor who succeeded in greening the desert, and a YouTube video about the successful cultivation of coral that resists bleaching — so that environmental protection of the earth’s natural world may advance around the world, and so that through trade, online business, and other activities for the public good and national benefit, economies around the world may flourish.\n\nFurthermore, by making full use of smartphones, tablets, PCs, AI teachers, and rental servers to their fullest, let us reform and improve school education, and provide training for working adults, corporate training, IT, WEB, DATABASE, AI, semiconductor engineer development, English conversation education, Japanese language education, bookkeeping, accounting, and business management development, training for carpenters and first-class architects, and education for obtaining U.S. data scientist qualifications — so that we may cultivate people who are capable anywhere, leaving no one behind! Let us cultivate people of whom we can be proud anywhere, leaving no one behind!\n\nWhile protecting the earth’s natural environment and advancing economic development around the world, we look forward to the emergence of many internationally exemplary leaders and messiahs from all over the world. May our descendants prosper, and may all of humanity coexist and flourish together.",d:"エジプトのピラミッドとスフィンクスとインカの地下から日本語の石板と巨大なUFOが発掘されたそうです。キリスト教のバイブルをアラビア語に翻訳して出来たのがコーラン（クルアーン）です。翻訳者たちの代表がモハンマド兄弟です。バイブルをアラビア語に翻訳してコーランを作った人たちが、イスラム諸国に最初に建てたのはキリスト教会で、私が、学生の頃に見た、恐らくNHK ３CHのTVで、その教会を掃除中に中からキリストの絵や十字架などが出てきたので、世界中のTV NEWSで放送するべきだと思います。イスラム教の信者のムスリム達も、世界中で今、キリスト教やユダヤ教や白人以外の全人類を滅亡させる計画を立ててDSやグレートリセットなどの計画を立てているジェノサイド計画を打ち破る為にも、間違った解釈による聖書バイブルの通り世界を滅ぼそうと計画を阻止する為にも、キリスト教、ユダヤ教、イスラム教よりも、今世界中でブームの日本の神道の八百万の神と言う自然信仰や、神社の隠れた神のヤハウェ信仰でもあります。それを更に上回るのは、後のシュメール語とヘブライ語に影響を与えた古い言語である日本語の流行、日本食の流行、日本の奈良京都などの神社仏閣と日本食と温泉ツアーブーム。日本の漫画、アニメ、ゲームブームでそのキャラクターにコスチュームプレイしながら日本語でアニメソングを歌うのが生きがいだと連呼、豪語される強豪の日本文化を愛して下さっている世界中の方々の熱い思いで御座います。それらにプラスして。日本がアジアや世界のイニシアチブリーダーとなりイランやイラクやサウジアラビアやドバイやエジプトやアジアやロシアやウクライナやアメリカやインドなど世界中が手に手を取りあい、愛と勇気と友情によって世界中が世界のイニシアチブリーダーとなり、砂漠の緑化に成功した日本の大学の教授のYoutubeであったり。白くなりにくいサンゴの養殖に成功のYoutubeであったりして世界中で地球の自然環境保護と、世界中で貿易やオンラインビジネスなどの公益で国益で世界中の経済が発展します様に。そして、スマホ、タブレット、PCでAI先生やレンタルサーバーフル動員で、学校教育の改革改善、社会人研修、企業研修、IT、WEB、DATABASE、AI、半導体エンジニア育成、英会話教育、日本語教育、簿記会計や経営者育成、大工さんや一級建築士育成。アメリカのデーターサイエンティストの資格取得教育などで、何処に行っても通用する人材を落ちこぼれ無しに育成して参りましょう！何処に出しても恥ずかしくない人材を落ちこぼれ無しに育成して参りましょう！地球の自然環境保護と世界中が経済発展を行ないながら、世界中の中から国際的模範的指導者のメシアが大勢出てくる事に期待しております。子孫繁栄、共存共栄、人類共存で御座います。",cardLinks:[{href:"https://ameblo.jp/www-aon/entry-12968778177.html",label:"解明されてきたUFO技術３選 #雑学 #都市伝説 #宇宙"},{href:"https://youtube.com/shorts/hxhF1OSd-iE?si=ZY40jPCWA-_3xDMO",label:"解明されてきたUFO技術３選 YouTube Shorts"},{href:"https://youtu.be/C8Szkx4ILwQ?si=d89IMnIxO6SsnDYH",label:"YouTube"},{href:"https://www.youtube.com/results?search_query=%E7%A0%82%E6%BC%A0%E3%81%AE%E7%B7%91%E5%8C%96%E3%81%AB%E6%88%90%E5%8A%9F%E3%81%97%E3%81%9F%E6%97%A5%E6%9C%AC%E4%BA%BA%E6%95%99%E6%8E%88%E3%80%80%E5%A4%A7%E5%B1%B1%E4%BF%AE%E4%B8%80",label:"Japanese Professor Who Succeeded in Desert Greening — Shuichi Oyama (砂漠の緑化に成功した日本人教授　大山修一)"}],bioScroll:1,fc:"eg"},
{g:"en",n:"English",t:"English",a:"USA",r:"Americas",c:"666 is written as ‘WWW’ in Hebrew. Therefore, the 666 in biblical prophecy is not a mark on a person’s forehead but the header of a website. Likewise, 666 is not a mark on a person’s hand but a number hidden within barcode scanner readers. The Third Secret of Fatima is remarkably close in content to Japan’s Kadokawa anime ‘Genma Taisen’ (GENMATAISEN); this cannot be mere coincidence. The depiction of a magma-fire dragon erupting from a volcanic flow was a portrayal within an advanced animated world, not a depiction of a real-world nuclear war.",d:"666は、ヘブライ語でWWWになります。よって666は、予言の聖書の人間の額ではなく、ホームページのヘッダーであります。666は、予言の聖書の人間の手ではなく、バーコードスキャナーリーダーに隠れた数字でもあります。",cardLinks:[{href:"https://youtu.be/C8Szkx4ILwQ",label:"エジプトのピラミッドとスフィンクスとインカの地下から日本語の石板と巨大なUFOが発掘されたそうです。"},{href:"https://www.youtube.com/watch?v=C8Szkx4ILwQ",label:"It is said that Japanese stone tablets and a massive UFO were excavated from underground near the Pyramids and the Sphinx in Egypt, as well as in the Inca region."},{href:"https://www.youtube.com/results?search_query=%E3%82%AA%E3%82%A2%E3%82%B9%E3%83%9A",label:"『オアスペ（Oahspe）』は、1882年にアメリカの歯科医ジョン・ニューブローが書いたもので、5つの人種「五色人」の起源や、高度な技術を持ち、日本の古史古伝である『竹内文書』と、宇宙神や天空浮船、神々の日本降臨などが記載されており、それらを裏付ける、日本のご神域、ご聖域の岩に刻まれたペトログラフ、ペトログリフなどが御座います。オアスペは、ニューエイジの新世代のバイブルとされております。"},{href:"https://www.youtube.com/results?search_query=Oahspe",label:"‘Oahspe’ was written in 1882 by John Newbrough, an American dentist. It records the origin of the five races known as the ‘Goshikijin’ (Five-Colored Peoples), a people of advanced technology, and—together with Japan’s ancient text the ‘Takeuchi Documents’—it describes cosmic gods, heavenly floating ships, and the descent of the gods to Japan. These accounts are supported by petrographs and petroglyphs carved into the rocks of Japan’s sacred sites and divine domains. Oahspe is regarded as the Bible of the New Age, a scripture for the new generation."},{href:"https://ameblo.jp/www-aon/entry-12969528377.html",label:"UFO開示の新事実。2000年間人類を騙し続けた嘘とは？【 都市伝説 】"},{href:"https://ameblo.jp/www-aon/entry-12969528377.html",label:"New facts on UFO disclosure: What is the lie that has deceived humanity for 2,000 years? [Urban Legend]"},{href:"https://www.youtube.com/watch?v=Vm0HZ05omJc",label:"【UFOファイル公開】アメリカが隠してたものは？「消えた11人の科学者」…P47,P52の正体がヤバすぎる"},{href:"https://www.youtube.com/watch?v=Vm0HZ05omJc",label:"[UFO Files Released] What was America hiding? ‘The 11 Vanished Scientists’ — the shocking truth behind P47 and P52"},{href:"https://www.youtube.com/results?search_query=%E5%9C%B0%E7%90%83%E3%81%AE%E5%9C%B0%E8%BB%B8%E3%81%8C%E5%8F%8D%E8%BB%A2%E3%80%80%E3%83%9D%E3%83%BC%E3%83%AB%E3%82%B7%E3%83%95%E3%83%88",label:"地球の地軸が反転　ポールシフト"},{href:"https://www.youtube.com/results?search_query=Earth+axis+reversal+pole+shift",label:"Earth’s Axis Reversal — Pole Shift"},{href:"https://www.youtube.com/results?search_query=The+Great+Taking",label:"What is The Great Taking? How can it be prevented?"},{href:"https://www.youtube.com/results?search_query=%E3%82%B0%E3%83%AC%E3%83%BC%E3%83%88%E3%83%BB%E3%83%86%E3%82%A4%E3%82%AD%E3%83%B3%E3%82%B0",label:"グレート・テイキングとは？　それを防ぐには？"},{href:"https://www.google.com/search?q=What+is+The+Great+Taking%3F+How+can+it+be+prevented%3F&udm=50",label:"Google AI Mode (ENGLISH)：What is The Great Taking? How can it be prevented?"},{href:"https://www.google.com/search?q=%E3%82%B0%E3%83%AC%E3%83%BC%E3%83%88%E3%83%BB%E3%83%86%E3%82%A4%E3%82%AD%E3%83%B3%E3%82%B0%E3%81%A8%E3%81%AF%EF%BC%9F%E3%80%80%E3%81%9D%E3%82%8C%E3%82%92%E9%98%B2%E3%81%90%E3%81%AB%E3%81%AF%EF%BC%9F&udm=50",label:"Google AI モード（日本語）：グレート・テイキングとは？　それを防ぐには？"}],bioScroll:1,fc:"us"},
{g:"de",n:"German (Switzerland)",t:"Schweiz",a:"SWITZERLAND",r:"Europe",c:"Switzerland is known for its underground and pumped-storage hydroelectric power plants built deep inside the Alps. Surplus daytime electricity is used to pump water up to the mountaintops, and in the evening the water is released to generate power.",d:"スイスは、アルプスの地下深くに造られた地下発電所や揚水発電所で知られています。日中の余剰電力でポンプにより水を山の上まで汲み上げ、夕方に放流して発電します。",cardLinks:[{href:"https://www.youtube.com/watch?v=rTNp69jLD_A&t=0s",label:"スイス　地下　発電"}],bioScroll:1,fc:"ch"},
{g:"en",n:"English (Canada)",t:"Canada",a:"CANADA",r:"Americas",c:"Canada is home to the world-famous Niagara Falls, which straddles the border between Canada and the United States. The Canadian side offers the most spectacular panoramic views of the great Horseshoe Falls.",d:"カナダには、カナダとアメリカの国境にまたがる世界的に有名なナイアグラの滝があります。カナダ側からは、馬蹄形のホースシュー滝の最も雄大なパノラマを眺めることができます。",cardLinks:[{href:"https://www.youtube.com/results?search_query=%E3%82%AB%E3%83%8A%E3%83%80%E3%80%80%E3%83%8A%E3%82%A4%E3%82%A2%E3%82%B0%E3%83%A9",label:"カナダ　ナイアグラ"}],bioScroll:1,fc:"ca"},
{g:"ar",n:"Arabic (Iraq)",t:"\u0639\u0631\u0627\u0642\u064A\nArabic",p:"Republic of Iraq",a:"IRAQ (Arabic)",r:"Middle East",c:"The Quran (Koran) of Islam was created by translating the Christian Bible into Arabic. The representative of the translators is the Muhammad brothers.\n\nThe first thing that the people who translated the Bible into Arabic and created the Quran built in Islamic countries was Christian churches. When I was a student, I probably saw on NHK Channel 3 TV that, while that church was being cleaned, images of Christ, crosses, and similar items emerged from inside; I believe that should be broadcast on TV news around the world.",d:"キリスト教のバイブルをアラビア語に翻訳して出来たのがコーラン（クルアーン）です。翻訳者たちの代表がモハンマド兄弟です。\n\nバイブルをアラビア語に翻訳してコーランを作った人たちが、イスラム諸国に最初に建てたのはキリスト教会で、私が、学生の頃に見た、恐らくNHK ３CHのTVで、その教会を掃除中に中からキリストの絵や十字架などが出てきたので、世界中のTV NEWSで放送するべきだと思います。",cardLinks:[{href:"https://www.youtube.com/results?search_query=%E4%B8%96%E7%95%8C%E4%B8%AD%E3%81%A7%E3%82%B3%E3%82%B9%E3%83%97%E3%83%AC%E3%80%80%E3%82%B3%E3%83%9F%E3%82%B1%E3%80%80%E3%82%A2%E3%83%8B%E3%83%A1%E3%82%BD%E3%83%B3%E3%82%B0%E3%83%96%E3%83%BC%E3%83%A0%E3%80%80%E3%82%A2%E3%83%8B%E3%82%BD%E3%83%B3%E3%83%90%E3%83%BC",label:"Global Cosplay & Anime Song Boom (世界的コミケ、アニソンブーム)"},{href:"https://www.youtube.com/results?search_query=%E3%82%A4%E3%82%BF%E3%83%AA%E3%82%A2%E3%80%80%E3%83%95%E3%83%A9%E3%83%B3%E3%82%B9%E3%80%80%E3%83%89%E3%82%A4%E3%83%84%E3%80%80%E4%B8%96%E7%95%8C%E4%B8%AD%E3%81%A7%E7%A5%9E%E9%81%93%E3%83%96%E3%83%BC%E3%83%A0",label:"Italy, France, Germany — Shinto Boom Worldwide (イタリア フランス ドイツ 世界中で神道ブーム)"}],bioScroll:1,fc:"iq"},
{g:"ru",n:"Russian",t:"\u0420\u0443\u0441\u0441\u043A\u0438\u0439",a:"RUSSIA",r:"Europe",c:"The Russia–Ukraine war was brought about by then U.S. President Biden and his younger son. President Putin has indicated that, under certain conditions, he is prepared to end the war at any time.",d:"\u30ED\u30B7\u30A2\u3068\u30A6\u30AF\u30E9\u30A4\u30CA\u6226\u4E89\u306F\u3001\u5F53\u6642\u306E\u30A2\u30E1\u30EA\u30AB\u306E\u30D0\u30A4\u30C7\u30F3\u5143\u5927\u7D71\u9818\u3068\u6B21\u7537\u304C\u8D77\u3053\u3057\u305F\u3082\u306E\u3067\u3059\u3002\u30D7\u30FC\u30C1\u30F3\u5927\u7D71\u9818\u306F\u3001\u4E00\u5B9A\u306E\u6761\u4EF6\u306E\u5143\u306A\u3089\u3044\u3064\u3067\u3082\u6226\u4E89\u3092\u7D42\u7D50\u3059\u308B\u6E96\u5099\u304C\u3042\u308B\u3068\u8A00\u3063\u3066\u3044\u308B\u3088\u3046\u3067\u3059\u3002",cardLinks:[{href:"https://aon.tokyo/russia",label:"russia"},{href:"https://chatgpt.com/share/6a04d47a-6b0c-8323-8f1c-a70aeb1bfde6",label:"ChatGPT 共有"}],bioScroll:1,fc:"ru"},
{g:"zh-CN",n:"Chinese (Simplified)",t:"\u7B80\u4F53\u4E2D\u6587",a:"CHINA",r:"Asia",c:"Japan's Imperial Family is also known as the Hata clan (HATASHI), who came to Japan and built shrines to worship the hidden God Yahweh (YHWH), including the Oosake Shrine (大避) dedicated to King David and the Inari Shrine (稲荷, INARI = INRI) dedicated to Christ. The Japanese Imperial Family (Hata clan), as a hybrid of Jewish and Sumerian people, ruled ancient China and reigned as the First Emperor of Qin (秦の始皇帝). Their ancestors were the Pharaohs of Egypt, and their forefathers belonged to the families of Abraham and Noah.",d:"日本の天皇家は、秦氏（HATASHI）でもあり、日本に渡来して隠れた神ヤハウェ（YHWH）を祭る神社を建設し、ダビデを祭る大避（OOSAKE）神社や、キリストを祭る稲荷（INARIをINRI）神社なども御座います。日本の天皇家（秦氏）は、中国では、ユダヤ人とシュメール人のハイブリッド人として古代中国を牛耳り支配して秦の始皇帝として君臨し、その先祖はエジプトのファラオであり、その先祖はアブラハムやノアのファミリーで御座います。",bioScroll:1,fc:"cn"},
{g:"ko",n:"Korean",t:"\uD55C\uAD6D\uC5B4",a:"KOREA",r:"Asia",c:"During the Second World War, Korea, which had been defeated by Japan, lived in constant fear of war from powers such as China and Russia, while ordinary people were destitute and lived in deep poverty. Time and again, Koreans appealed to the Japanese government for things such as: reform of a society that was contemptuous of women and in which men enjoyed excessive advantages; abolition of status systems such as Korea's yangban (the yangban elite); river control and flood works; dam and reservoir projects; construction of subways, bridges, and tunnels; building of schools and hospitals; and creating Hangul textbooks. The Japanese government accepted these requests, and the Japan–Korea annexation was carried out. Thereafter, the yangban and others distributed wealth they had accumulated to ordinary people, and [those elites] took part in the overall improvement of living infrastructure—including road building in Korea—that the Korean government had asked the Japanese government to undertake.　　The Asia clan, after which the name 'Asia' is derived, was led by chieftain Asia-Touan, an ancestor of Ashiya Doman, who once dueled with Abe no Seimei. He is the founder of Onmyodo (the Way of Yin and Yang), which was transmitted from Japan to Korea as the Eight Trigrams (Bagua / Palgwae). There is also a health practice of printing the Katakamuna script of the Katakamuna civilization onto shirts and wearing them.",d:"第二次世界大戦当時、日本に敗戦した韓国は、中国やロシアなどからの戦争におびえながら、一般人が貧乏で生活貧困で、日本政府に、女性蔑視の男性が有利すぎる社会の改善。朝鮮の両班（ヤンバン）などの身分制度廃止や、河川の治水事業や、ダムの貯水事業や、地下鉄や橋の建設やトンネルの建設や、学校や病院の建設や、ハングルの教科書を作る話などを、何度も何度も、日本政府にお願いがあり、それを日本政府が引き受けて日韓併合が行われました。そして。両班などが、蓄えていた資産を一般人に分け与えたり、朝鮮の道路整備など韓国政府から日本政府に、お願いのあった生活インフラ全般の整備にあたりました。アジアの名の由来のアシア族は族長アシアトウアンで、安倍晴明（あべのせいめい）と対決した芦屋道満（あしやどうまん）の先祖で陰陽道の元祖で韓国には日本から八卦として伝わりました。カタカムナ文明のカタカムナ文字を印刷してシャツに貼る健康法も御座います。",bioScroll:1,fc:"kr"},
{g:"zh-TW",n:"Chinese (Traditional)",t:"\u7E41\u9AD4\u4E2D\u6587",a:"TAIWAN",r:"Asia",c:"Taiwan",fc:"tw"},
{g:"et",n:"Estonian",t:"Eesti",a:"ESTONIA",r:"Europe",c:"Estonia e-Government",fc:"ee"},
{g:"az",n:"Azerbaijani",t:"Az\u0259rbaycan",a:"AZERBAIJAN",r:"Asia",c:"Azerbaijan e-Government",fc:"az"},
{g:"hi",n:"Hindi",t:"\u0939\u093F\u0928\u094D\u0926\u0940",a:"INDIA",r:"Asia",c:"India",fc:"in"},
{g:"ar",n:"Arabic",t:"\u0627\u0644\u0639\u0631\u0628\u064A\u0629",a:"ARAB",r:"Middle East",c:"Saudi Arabia",fc:"sa"},
{g:"uk",n:"Ukrainian",t:"\u0423\u043A\u0440\u0430\u0457\u043D\u0441\u044C\u043A\u0430",a:"UKR",r:"Europe",c:"Ukraine",fc:"ua"},
{g:"it",n:"Italian",t:"Italiano",a:"ITALY",r:"Europe",c:"Italy",fc:"it"},
{g:"fr",n:"French",t:"Fran\u00E7ais",a:"FRANCE",r:"Europe",c:"France",fc:"fr"},
{g:"de",n:"German",t:"Deutsch",a:"GER",r:"Europe",c:"Germany",fc:"de"},
{g:"es",n:"Spanish (Mexico)",t:"Espa\u00F1ol (M\u00E9xico)",a:"MEXICO",r:"Americas",c:"Mexico",fc:"mx"},
{g:"pt",n:"Portuguese (Brazil)",t:"Portugu\u00EAs (Brasil)",a:"BRAZIL",r:"Americas",c:"Brazil",fc:"br"},
{g:"en",n:"English (UK)",t:"English (UK)",a:"UK",r:"Europe",c:"United Kingdom",fc:"gb"},
{g:"es",n:"Spanish",t:"Espa\u00F1ol",a:"SPAIN",r:"Europe",c:"Spain",fc:"es"},
{g:"pt",n:"Portuguese",t:"Portugu\u00EAs",a:"PORT",r:"Europe",c:"Portugal",fc:"pt"},
{g:"nl",n:"Dutch",t:"Nederlands",a:"NLD",r:"Europe",c:"Netherlands",fc:"nl"},
{g:"pl",n:"Polish",t:"Polski",a:"POLAND",r:"Europe",c:"Poland",fc:"pl"},
{g:"sv",n:"Swedish",t:"Svenska",a:"SWEDEN",r:"Europe",c:"Sweden",fc:"se"},
{g:"da",n:"Danish",t:"Dansk",a:"DEN",r:"Europe",c:"Denmark",fc:"dk"},
{g:"fi",n:"Finnish",t:"Suomi",a:"FIN",r:"Europe",c:"Finland",fc:"fi"},
{g:"no",n:"Norwegian",t:"Norsk",a:"NOR",r:"Europe",c:"Norway",fc:"no"},
{g:"cs",n:"Czech",t:"\u010Ce\u0161tina",a:"CZECH",r:"Europe",c:"Czech Republic",fc:"cz"},
{g:"sk",n:"Slovak",t:"Sloven\u010Dina",a:"SLOVAK",r:"Europe",c:"Slovakia",fc:"sk"},
{g:"hu",n:"Hungarian",t:"Magyar",a:"HUN",r:"Europe",c:"Hungary",fc:"hu"},
{g:"ro",n:"Romanian",t:"Rom\u00E2n\u0103",a:"ROM",r:"Europe",c:"Romania",fc:"ro"},
{g:"bg",n:"Bulgarian",t:"\u0411\u044A\u043B\u0433\u0430\u0440\u0441\u043A\u0438",a:"BUL",r:"Europe",c:"Bulgaria",fc:"bg"},
{g:"el",n:"Greek",t:"\u0395\u03BB\u03BB\u03B7\u03BD\u03B9\u03BA\u03AC",a:"GREECE",r:"Europe",c:"Greece",fc:"gr"},
{g:"hr",n:"Croatian",t:"Hrvatski",a:"CRO",r:"Europe",c:"Croatia",fc:"hr"},
{g:"sr",n:"Serbian",t:"\u0421\u0440\u043F\u0441\u043A\u0438",a:"SERBIA",r:"Europe",c:"Serbia",fc:"rs"},
{g:"sl",n:"Slovenian",t:"Sloven\u0161\u010Dina",a:"SVN",r:"Europe",c:"Slovenia",fc:"si"},
{g:"lv",n:"Latvian",t:"Latvie\u0161u",a:"LATVIA",r:"Europe",c:"Latvia",fc:"lv"},
{g:"lt",n:"Lithuanian",t:"Lietuvi\u0173",a:"LITH",r:"Europe",c:"Lithuania",fc:"lt"},
{g:"be",n:"Belarusian",t:"\u0411\u0435\u043B\u0430\u0440\u0443\u0441\u043A\u0430\u044F",a:"BLR",r:"Europe",c:"Belarus",fc:"by"},
{g:"is",n:"Icelandic",t:"\u00CDslenska",a:"ICE",r:"Europe",c:"Iceland",fc:"is"},
{g:"ga",n:"Irish",t:"Gaeilge",a:"IRE",r:"Europe",c:"Ireland",fc:"ie"},
{g:"cy",n:"Welsh",t:"Cymraeg",a:"WALES",r:"Europe",c:"Wales",fc:"gb-wls"},
{g:"gd",n:"Scots Gaelic",t:"G\u00E0idhlig",a:"SCOT",r:"Europe",c:"Scotland",fc:"gb-sct"},
{g:"mt",n:"Maltese",t:"Malti",a:"MALTA",r:"Europe",c:"Malta",fc:"mt"},
{g:"sq",n:"Albanian",t:"Shqip",a:"ALB",r:"Europe",c:"Albania",fc:"al"},
{g:"mk",n:"Macedonian",t:"\u041C\u0430\u043A\u0435\u0434\u043E\u043D\u0441\u043A\u0438",a:"MACED",r:"Europe",c:"North Macedonia",fc:"mk"},
{g:"bs",n:"Bosnian",t:"Bosanski",a:"BOSNIA",r:"Europe",c:"Bosnia and Herzegovina",fc:"ba"},
{g:"lb",n:"Luxembourgish",t:"L\u00EBtzebuergesch",a:"LUX",r:"Europe",c:"Luxembourg",fc:"lu"},
{g:"ca",n:"Catalan",t:"Catal\u00E0",a:"CAT",r:"Europe",c:"Spain (Catalonia)",fc:"es"},
{g:"gl",n:"Galician",t:"Galego",a:"GAL",r:"Europe",c:"Spain (Galicia)",fc:"es"},
{g:"eu",n:"Basque",t:"Euskara",a:"BASQUE",r:"Europe",c:"Spain (Basque Country)",fc:"es"},
{g:"fy",n:"Frisian",t:"Frysk",a:"FRIES",r:"Europe",c:"Netherlands (Friesland)",fc:"nl"},
{g:"co",n:"Corsican",t:"Corsu",a:"CORS",r:"Europe",c:"France (Corsica)",fc:"fr"},
{g:"eo",n:"Esperanto",t:"Esperanto",a:"ESPR",r:"Europe",c:"International",fc:"eu"},
{g:"la",n:"Latin",t:"Latina",a:"LATIN",r:"Europe",c:"Vatican City",fc:"va"},
{g:"oc",n:"Occitan",t:"Occitan",a:"OCC",r:"Europe",c:"France (Occitania)",fc:"fr"},
{g:"br",n:"Breton",t:"Brezhoneg",a:"BRETON",r:"Europe",c:"France (Brittany)",fc:"fr"},
{g:"ka",n:"Georgian",t:"\u10E5\u10D0\u10E0\u10D7\u10E3\u10DA\u10D8",a:"GEO",r:"Europe",c:"Georgia",fc:"ge"},
{g:"hy",n:"Armenian",t:"\u0540\u0561\u0575\u0565\u0580\u0565\u0576",a:"ARM",r:"Europe",c:"Armenia",fc:"am"},
{g:"tt",n:"Tatar",t:"\u0422\u0430\u0442\u0430\u0440\u0447\u0430",a:"TATAR",r:"Europe",c:"Russia (Tatarstan)",fc:"ru"},
{g:"ba",n:"Bashkir",t:"\u0411\u0430\u0448\u04A1\u043E\u0440\u0442",a:"BASH",r:"Europe",c:"Russia (Bashkortostan)",fc:"ru"},
{g:"cv",n:"Chuvash",t:"\u0427\u04D1\u0432\u0430\u0448",a:"CHUV",r:"Europe",c:"Russia (Chuvashia)",fc:"ru"},
{g:"yi",n:"Yiddish",t:"\u05D9\u05D9\u05B4\u05D3\u05D9\u05E9",a:"YIDSH",r:"Europe",c:"Israel",fc:"il"},
{g:"tr",n:"Turkish",t:"T\u00FCrk\u00E7e",a:"TURKEY",r:"Middle East",c:"Turkey",fc:"tr"},
{g:"iw",n:"Hebrew",t:"\u05E2\u05D1\u05E8\u05D9\u05EA",a:"ISR",r:"Middle East",c:"Israel",fc:"il"},
{g:"ur",n:"Urdu",t:"\u0627\u0631\u062F\u0648",a:"PAK",r:"Middle East",c:"Pakistan",fc:"pk"},
{g:"ps",n:"Pashto",t:"\u067E\u069A\u062A\u0648",a:"AFGHAN",r:"Middle East",c:"Afghanistan",fc:"af"},
{g:"ku",n:"Kurdish (Kurmanji)",t:"Kurd\u00EE",a:"KURD",r:"Middle East",c:"Iraq / Turkey",fc:"iq"},
{g:"ckb",n:"Kurdish (Sorani)",t:"\u0633\u06C6\u0631\u0627\u0646\u06CC",a:"KURD",r:"Middle East",c:"Iraq",fc:"iq"},
{g:"kk",n:"Kazakh",t:"\u049A\u0430\u0437\u0430\u049B",a:"KAZAK",r:"Middle East",c:"Kazakhstan",fc:"kz"},
{g:"ky",n:"Kyrgyz",t:"\u041A\u044B\u0440\u0433\u044B\u0437\u0447\u0430",a:"KYRGYZ",r:"Middle East",c:"Kyrgyzstan",fc:"kg"},
{g:"uz",n:"Uzbek",t:"O\u02BBzbek",a:"UZBEK",r:"Middle East",c:"Uzbekistan",fc:"uz"},
{g:"tg",n:"Tajik",t:"\u0422\u043E\u04B7\u0438\u043A\u04E3",a:"TAJIK",r:"Middle East",c:"Tajikistan",fc:"tj"},
{g:"tk",n:"Turkmen",t:"T\u00FCrkmen",a:"TURKM",r:"Middle East",c:"Turkmenistan",fc:"tm"},
{g:"bn",n:"Bengali",t:"\u09AC\u09BE\u0982\u09B2\u09BE",a:"BANGLA",r:"Asia",c:"Bangladesh",fc:"bd"},
{g:"ta",n:"Tamil",t:"\u0BA4\u0BAE\u0BBF\u0BB4\u0BCD",a:"TAMIL",r:"Asia",c:"India (Tamil Nadu)",fc:"in"},
{g:"te",n:"Telugu",t:"\u0C24\u0C46\u0C32\u0C41\u0C17\u0C41",a:"TELUGU",r:"Asia",c:"India (Andhra Pradesh)",fc:"in"},
{g:"ml",n:"Malayalam",t:"\u0D2E\u0D32\u0D2F\u0D3E\u0D33\u0D02",a:"KERALA",r:"Asia",c:"India (Kerala)",fc:"in"},
{g:"kn",n:"Kannada",t:"\u0C95\u0CA8\u0CCD\u0CA8\u0CA1",a:"KANNAD",r:"Asia",c:"India (Karnataka)",fc:"in"},
{g:"gu",n:"Gujarati",t:"\u0A97\u0AC1\u0A9C\u0AB0\u0ABE\u0AA4\u0AC0",a:"GUJAR",r:"Asia",c:"India (Gujarat)",fc:"in"},
{g:"mr",n:"Marathi",t:"\u092E\u0930\u093E\u0920\u0940",a:"MARATH",r:"Asia",c:"India (Maharashtra)",fc:"in"},
{g:"pa",n:"Punjabi",t:"\u0A2A\u0A70\u0A1C\u0A3E\u0A2C\u0A40",a:"PUNJAB",r:"Asia",c:"India (Punjab)",fc:"in"},
{g:"or",n:"Odia",t:"\u0B13\u0B21\u0B3C\u0B3F\u0B06",a:"ODISHA",r:"Asia",c:"India (Odisha)",fc:"in"},
{g:"ne",n:"Nepali",t:"\u0928\u0947\u092A\u093E\u0932\u0940",a:"NEPAL",r:"Asia",c:"Nepal",fc:"np"},
{g:"si",n:"Sinhala",t:"\u0DC3\u0DD2\u0D82\u0DC4\u0DBD",a:"LANKA",r:"Asia",c:"Sri Lanka",fc:"lk"},
{g:"as",n:"Assamese",t:"\u0985\u09B8\u09AE\u09C0\u09AF\u09BC\u09BE",a:"ASSAM",r:"Asia",c:"India (Assam)",fc:"in"},
{g:"sd",n:"Sindhi",t:"\u0633\u0646\u068C\u064A",a:"SINDH",r:"Asia",c:"Pakistan (Sindh)",fc:"pk"},
{g:"doi",n:"Dogri",t:"\u0921\u094B\u0917\u0930\u0940",a:"DOGRI",r:"Asia",c:"India (Jammu)",fc:"in"},
{g:"bho",n:"Bhojpuri",t:"\u092D\u094B\u091C\u092A\u0941\u0930\u0940",a:"BHOJP",r:"Asia",c:"India (Bihar)",fc:"in"},
{g:"mai",n:"Maithili",t:"\u092E\u0948\u0925\u093F\u0932\u0940",a:"MAITH",r:"Asia",c:"India (Bihar)",fc:"in"},
{g:"gom",n:"Konkani",t:"\u0915\u094B\u0902\u0915\u0923\u0940",a:"GOA",r:"Asia",c:"India (Goa)",fc:"in"},
{g:"sa",n:"Sanskrit",t:"\u0938\u0902\u0938\u094D\u0915\u0943\u0924\u092E\u094D",a:"SANSKR",r:"Asia",c:"India",fc:"in"},
{g:"dv",n:"Divehi",t:"\u078B\u07A8\u0788\u07AC\u0780\u07A8",a:"MALDIV",r:"Asia",c:"Maldives",fc:"mv"},
{g:"th",n:"Thai",t:"\u0E44\u0E17\u0E22",a:"THAI",r:"Asia",c:"Thailand",fc:"th"},
{g:"vi",n:"Vietnamese",t:"Ti\u1EBFng Vi\u1EC7t",a:"VIET",r:"Asia",c:"Vietnam",fc:"vn"},
{g:"id",n:"Indonesian",t:"Bahasa Indonesia",a:"INDO",r:"Asia",c:"Indonesia",fc:"id"},
{g:"ms",n:"Malay",t:"Bahasa Melayu",a:"MALAY",r:"Asia",c:"Malaysia",fc:"my"},
{g:"tl",n:"Filipino",t:"Filipino",a:"PHIL",r:"Asia",c:"Philippines",fc:"ph"},
{g:"km",n:"Khmer",t:"\u1781\u17D2\u1798\u17C2\u179A",a:"KHMER",r:"Asia",c:"Cambodia",fc:"kh"},
{g:"lo",n:"Lao",t:"\u0EA5\u0EB2\u0EA7",a:"LAO",r:"Asia",c:"Laos",fc:"la"},
{g:"my",n:"Myanmar (Burmese)",t:"\u1017\u1019\u102C",a:"MYANM",r:"Asia",c:"Myanmar",fc:"mm"},
{g:"jw",n:"Javanese",t:"Jawa",a:"JAVA",r:"Asia",c:"Indonesia (Java)",fc:"id"},
{g:"su",n:"Sundanese",t:"Basa Sunda",a:"SUNDA",r:"Asia",c:"Indonesia (Sunda)",fc:"id"},
{g:"ceb",n:"Cebuano",t:"Cebuano",a:"CEBU",r:"Asia",c:"Philippines (Cebu)",fc:"ph"},
{g:"ilo",n:"Iloko",t:"Iloko",a:"ILOKO",r:"Asia",c:"Philippines (Ilocos)",fc:"ph"},
{g:"hil",n:"Hiligaynon",t:"Hiligaynon",a:"HILIG",r:"Asia",c:"Philippines (Visayas)",fc:"ph"},
{g:"hmn",n:"Hmong",t:"Hmoob",a:"HMONG",r:"Asia",c:"Laos / Vietnam",fc:"la"},
{g:"yue",n:"Cantonese",t:"\u7CB5\u8A9E",a:"HK",r:"Asia",c:"Hong Kong",fc:"hk"},
{g:"mn",n:"Mongolian",t:"\u041C\u043E\u043D\u0433\u043E\u043B",a:"MONGOL",r:"Asia",c:"Mongolia",fc:"mn"},
{g:"ug",n:"Uyghur",t:"\u0626\u06C7\u064A\u063A\u06C7\u0631\u0686\u06D5",a:"UYGHUR",r:"Asia",c:"China (Xinjiang)",fc:"cn"},
{g:"sw",n:"Swahili",t:"Kiswahili",a:"SWAHL",r:"Africa",c:"Tanzania / Kenya",fc:"tz"},
{g:"am",n:"Amharic",t:"\u12A0\u121B\u122D\u129B",a:"ETHIO",r:"Africa",c:"Ethiopia",fc:"et"},
{g:"ha",n:"Hausa",t:"Hausa",a:"HAUSA",r:"Africa",c:"Nigeria",fc:"ng"},
{g:"ig",n:"Igbo",t:"Igbo",a:"IGBO",r:"Africa",c:"Nigeria",fc:"ng"},
{g:"yo",n:"Yoruba",t:"Yor\u00F9b\u00E1",a:"YORUBA",r:"Africa",c:"Nigeria",fc:"ng"},
{g:"zu",n:"Zulu",t:"isiZulu",a:"ZULU",r:"Africa",c:"South Africa",fc:"za"},
{g:"xh",n:"Xhosa",t:"isiXhosa",a:"XHOSA",r:"Africa",c:"South Africa",fc:"za"},
{g:"af",n:"Afrikaans",t:"Afrikaans",a:"SAFR",r:"Africa",c:"South Africa",fc:"za"},
{g:"sn",n:"Shona",t:"chiShona",a:"ZIM",r:"Africa",c:"Zimbabwe",fc:"zw"},
{g:"ny",n:"Chichewa",t:"Chichewa",a:"MALAWI",r:"Africa",c:"Malawi",fc:"mw"},
{g:"so",n:"Somali",t:"Soomaaliga",a:"SOMALI",r:"Africa",c:"Somalia",fc:"so"},
{g:"mg",n:"Malagasy",t:"Malagasy",a:"MADAG",r:"Africa",c:"Madagascar",fc:"mg"},
{g:"rw",n:"Kinyarwanda",t:"Kinyarwanda",a:"RWANDA",r:"Africa",c:"Rwanda",fc:"rw"},
{g:"st",n:"Sesotho",t:"Sesotho",a:"LESOTO",r:"Africa",c:"Lesotho",fc:"ls"},
{g:"lg",n:"Luganda",t:"Oluganda",a:"UGANDA",r:"Africa",c:"Uganda",fc:"ug"},
{g:"om",n:"Oromo",t:"Afaan Oromoo",a:"OROMO",r:"Africa",c:"Ethiopia",fc:"et"},
{g:"ti",n:"Tigrinya",t:"\u1275\u130D\u122D\u129B",a:"ERITR",r:"Africa",c:"Eritrea",fc:"er"},
{g:"ln",n:"Lingala",t:"Ling\u00E1la",a:"CONGO",r:"Africa",c:"DR Congo",fc:"cd"},
{g:"bm",n:"Bambara",t:"Bamanankan",a:"MALI",r:"Africa",c:"Mali",fc:"ml"},
{g:"ee",n:"Ewe",t:"E\u028Begbe",a:"GHANA",r:"Africa",c:"Ghana",fc:"gh"},
{g:"ak",n:"Twi (Akan)",t:"Twi",a:"TWI",r:"Africa",c:"Ghana",fc:"gh"},
{g:"nso",n:"Northern Sotho",t:"Sepedi",a:"SEPEDI",r:"Africa",c:"South Africa",fc:"za"},
{g:"ts",n:"Tsonga",t:"Xitsonga",a:"TSONGA",r:"Africa",c:"South Africa",fc:"za"},
{g:"tn",n:"Tswana",t:"Setswana",a:"BOTSWA",r:"Africa",c:"Botswana",fc:"bw"},
{g:"ss",n:"Swati",t:"siSwati",a:"ESWAT",r:"Africa",c:"Eswatini",fc:"sz"},
{g:"nr",n:"Ndebele (South)",t:"isiNdebele",a:"NDEB",r:"Africa",c:"South Africa",fc:"za"},
{g:"ht",n:"Haitian Creole",t:"Krey\u00F2l ayisyen",a:"HAITI",r:"Americas",c:"Haiti",fc:"ht"},
{g:"gn",n:"Guarani",t:"Ava\u00F1e'\u1EBD",a:"PARA",r:"Americas",c:"Paraguay",fc:"py"},
{g:"qu",n:"Quechua",t:"Runasimi",a:"PERU",r:"Americas",c:"Peru",fc:"pe"},
{g:"ay",n:"Aymara",t:"Aymar aru",a:"BOLIV",r:"Americas",c:"Bolivia",fc:"bo"},
{g:"haw",n:"Hawaiian",t:"\u02BB\u014Clelo Hawai\u02BBi",a:"HAWAII",r:"Americas",c:"USA (Hawaii)",fc:"us"},
{g:"mi",n:"Maori",t:"Te Reo M\u0101ori",a:"NZ",r:"Pacific",c:"New Zealand",fc:"nz"},
{g:"sm",n:"Samoan",t:"Gagana Samoa",a:"SAMOA",r:"Pacific",c:"Samoa",fc:"ws"},
{g:"fj",n:"Fijian",t:"Na Vosa Vakaviti",a:"FIJI",r:"Pacific",c:"Fiji",fc:"fj"}
];

// ミックスリスト用にLをグローバルに公開
try { window.__AC_LANG_DATA__ = L; } catch(e){}

var REGIONS=["Asia","Europe","Americas","Middle East","Africa","Pacific"];
var REGION_LABELS={
  "Asia":"Asia",
  "Europe":"Europe",
  "Americas":"Americas",
  "Middle East":"Middle East & Central Asia",
  "Africa":"Africa",
  "Pacific":"Pacific"
};
var ABC_KEY="ABC";
var ABC_LABEL="ABC (A-Z)";

// ★翻訳機能のバージョン (キャッシュ確認用): v2026-04-26-A
function makeUrl(gc){
  if (gc === "ja") return "/?lang=ja";

  return "/?lang=" + encodeURIComponent(gc) + "#translateto=" + encodeURIComponent(gc);
}

/** 言語カードモーダル用: ルート
 * BUG修正: ?lang=XX を渡すと翻訳LISTが表示されるため、
 * Google翻訳プロキシURL経由で audiocafe.tokyo を翻訳表示する。
 * 日本語の場合のみ素のURLを使用。
 */
function makeAcNavAudiocafeRoot(gc){
  if (gc === "ja") return "https://audiocafe.tokyo/?lang=ja";
  return "https://translate.google.com/translate?sl=ja&tl=" + encodeURIComponent(gc) + "&u=" + encodeURIComponent("https://audiocafe.tokyo/");
}
/** 言語カードモーダル用: /top/ 静的ページ (top/index.html) */
function makeAcNavRaysoftTop(gc){
  if (gc === "ja") return "https://audiocafe.tokyo/top/?lang=ja";
  return "https://audiocafe.tokyo/top/?lang=" + encodeURIComponent(gc) + "#translateto=" + encodeURIComponent(gc);
}
function isAcNavTopPathname(pathname){
  var p = (pathname || "").replace(/\/+$/,"") || "/";
  return p === "/top" || p.indexOf("/top/") === 0;
}
function guardAcNavRootHref(href, gc){
  try{
    var u = new URL(href, "https://audiocafe.tokyo/");
    if(isAcNavTopPathname(u.pathname)) return makeAcNavAudiocafeRoot(gc || "ja");
    if(u.pathname === "/index.php" && u.searchParams.get("lang")) return makeAcNavAudiocafeRoot(gc || "ja");
    return href;
  }catch(e){
    return makeAcNavAudiocafeRoot(gc || "ja");
  }
}
// ★ [v23] 言語カード遷移先URL生成:
//   PHP側の __AC_NAV_LOCAL（file_exists結果）を参照し、
//   現在のドメイン配下にパスが存在すれば相対パスで遷移、
//   無ければ https://audiocafe.tokyo/... のフルURLで遷移する。
//   どのドメインからアクセスしても「そのドメイン内に /aruaru/ があれば優先」。
function __acNavUrl(localKey, path, gc, withLang){
  // __AC_NAV_LOCAL がPHPから注入されていない場合でも ReferenceError を出さない。
  // 未定義時は安全側に倒して audiocafe.tokyo のフルURLにフォールバックする。
  var navLocal = (typeof __AC_NAV_LOCAL !== 'undefined') ? __AC_NAV_LOCAL : null;
  var base = (navLocal && navLocal[localKey])
    ? path                          // 同ドメイン配下に存在 → 相対パス
    : 'https://audiocafe.tokyo' + path; // 存在しない → audiocafe.tokyo フルURL
  if (!withLang) return base;
  if (gc === 'ja') return base + '?lang=ja';
  return base + '?lang=' + encodeURIComponent(gc) + '#translateto=' + encodeURIComponent(gc);
}
function makeAcNavAruaruUrl(gc){
  return __acNavUrl('aruaru', '/aruaru/', gc, true);
}
function makeAcNavAruaruLadyUrl(gc){
  return __acNavUrl('aruaruLady', '/aruaru-lady/', gc, true);
}
function makeAcNavRakutenMobileUrl(gc){
  if (gc === 'ja') return __acNavUrl('rakutenMobile', '/rakuten-mobile/', gc, false);
  return __acNavUrl('rakutenMobile', '/rakuten-mobile/', gc, true);
}
function makeAcNavGoogleTransUrl(gc, pageUrl){
  // 日本語選択時: 左=pageUrl, 右=英語, UIは日本語
  var tl = (gc === 'ja') ? 'en' : gc;
  var hl = (gc === 'ja') ? 'ja' : gc;
  var tldMap = {'zh-CN':'com.cn','zh-TW':'com.tw','ru':'ru','de':'de','fr':'fr','it':'it','es':'es','pt':'com.br','ko':'co.kr','ja':'co.jp'};
  var tld = tldMap[hl] || (hl === 'ja' ? 'co.jp' : 'com');
  return 'https://translate.google.' + tld + '/?hl=' + encodeURIComponent(hl) +
         '&sl=ja&tl=' + encodeURIComponent(tl) +
         '&text=' + encodeURIComponent(pageUrl) + '&op=translate';
}

/** Google翻訳サイトピッカー i18n (タイトル + 元の画面に戻る) */
var AC_NAV_MODAL_I18N = {
  "ja": { title: "Google翻訳サイトを表示しますか？", cancel: "元の画面に戻る", hint: "選んだ言語で開きます。日本語の場合は翻訳なしで開きます。", mixlist: "📋 言語カード＆/top 要約LIST", googleTrans: "🌐 Google翻訳サイトへ移動" },
  "en": { title: "Display the Google Translate site?", cancel: "Back to previous screen", hint: "Opens in your selected language. Japanese opens without translation.", mixlist: "📋 Language Card & /top Summary LIST", googleTrans: "🌐 Go to Google Translate Site" },
  "zh-CN": { title: "要显示 Google 翻译网站吗？", cancel: "返回上一页", hint: "将以您选择的语言打开。日语不会被翻译。", mixlist: "📋 语言卡片 & /top 摘要LIST", googleTrans: "🌐 前往 Google 翻译网站" },
  "zh-TW": { title: "要顯示 Google 翻譯網站嗎？", cancel: "返回上一頁", hint: "將以您選擇的語言開啟。日語不會被翻譯。", mixlist: "📋 語言卡片 & /top 摘要LIST", googleTrans: "🌐 前往 Google 翻譯網站" },
  "ko": { title: "Google 번역 사이트를 표시할까요?", cancel: "이전 화면으로 돌아가기", hint: "선택한 언어로 열립니다. 일본어는 번역 없이 열립니다.", mixlist: "📋 언어 카드 & /top 요약 LIST", googleTrans: "🌐 Google 번역 사이트로 이동" },
  "fr": { title: "Afficher le site Google Traduction ?", cancel: "Retour à l'écran précédent", hint: "S'ouvre dans la langue sélectionnée. Le japonais s'ouvre sans traduction.", mixlist: "📋 Carte de langue & Résumé /top LIST", googleTrans: "🌐 Aller sur Google Traduction" },
  "de": { title: "Google Übersetzer-Website anzeigen?", cancel: "Zurück zum vorherigen Bildschirm", hint: "Öffnet in der gewählten Sprache. Japanisch öffnet ohne Übersetzung.", mixlist: "📋 Sprachkarte & /top Zusammenfassung LIST", googleTrans: "🌐 Zur Google Übersetzer-Website" },
  "es": { title: "¿Mostrar el sitio de Google Traductor?", cancel: "Volver a la pantalla anterior", hint: "Se abre en el idioma seleccionado. El japonés se abre sin traducción.", mixlist: "📋 Tarjeta de idioma & Resumen /top LIST", googleTrans: "🌐 Ir al sitio de Google Traductor" },
  "pt": { title: "Exibir o site do Google Tradutor?", cancel: "Voltar à tela anterior", hint: "Abre no idioma selecionado. O japonês abre sem tradução.", mixlist: "📋 Cartão de idioma & Resumo /top LIST", googleTrans: "🌐 Ir para o site do Google Tradutor" },
  "it": { title: "Visualizzare il sito di Google Traduttore?", cancel: "Torna alla schermata precedente", hint: "Si apre nella lingua selezionata. Il giapponese si apre senza traduzione.", mixlist: "📋 Scheda lingua & Riepilogo /top LIST", googleTrans: "🌐 Vai al sito di Google Traduttore" },
  "ru": { title: "Открыть сайт Google Переводчика?", cancel: "Вернуться к предыдущему экрану", hint: "Открывается на выбранном языке. Японский открывается без перевода.", mixlist: "📋 Языковая карточка & Сводка /top LIST", googleTrans: "🌐 Перейти на сайт Google Переводчика" },
  "ar": { title: "هل تريد عرض موقع ترجمة Google؟", cancel: "العودة إلى الشاشة السابقة", hint: "يفتح باللغة المختارة. يفتح اليابانية بدون ترجمة.", mixlist: "📋 بطاقة اللغة & قائمة ملخص /top", googleTrans: "🌐 الانتقال إلى موقع ترجمة Google" },
  "hi": { title: "Google अनुवाद साइट दिखाएं?", cancel: "पिछली स्क्रीन पर वापस जाएं", hint: "चुनी गई भाषा में खुलता है। जापानी बिना अनुवाद के खुलता है।", mixlist: "📋 भाषा कार्ड & /top सारांश LIST", googleTrans: "🌐 Google अनुवाद साइट पर जाएं" },
  "th": { title: "แสดงเว็บไซต์ Google แปลภาษา?", cancel: "กลับไปยังหน้าจอก่อนหน้า", hint: "เปิดในภาษาที่เลือก ภาษาญี่ปุ่นจะเปิดโดยไม่มีการแปล", mixlist: "📋 บัตรภาษา & สรุป /top LIST", googleTrans: "🌐 ไปที่เว็บไซต์ Google แปลภาษา" },
  "vi": { title: "Hiển thị trang Google Dịch?", cancel: "Quay lại màn hình trước", hint: "Mở bằng ngôn ngữ đã chọn. Tiếng Nhật mở mà không dịch.", mixlist: "📋 Thẻ ngôn ngữ & Tóm tắt /top LIST", googleTrans: "🌐 Đến trang Google Dịch" },
  "id": { title: "Tampilkan situs Google Terjemahan?", cancel: "Kembali ke layar sebelumnya", hint: "Dibuka dalam bahasa yang dipilih. Bahasa Jepang dibuka tanpa terjemahan.", mixlist: "📋 Kartu bahasa & Ringkasan /top LIST", googleTrans: "🌐 Ke situs Google Terjemahan" },
  "uk": { title: "Відкрити сайт Google Перекладача?", cancel: "Повернутися до попереднього екрана", hint: "Відкривається обраною мовою. Японська відкривається без перекладу.", mixlist: "📋 Мовна картка & Зведення /top LIST", googleTrans: "🌐 Перейти на сайт Google Перекладача" },
  "tr": { title: "Google Çeviri sitesi açılsın mı?", cancel: "Önceki ekrana dön", hint: "Seçili dilde açılır. Japonca çeviri olmadan açılır.", mixlist: "📋 Dil Kartı & /top Özeti LIST", googleTrans: "🌐 Google Çeviri Sitesine Git" },
  "nl": { title: "Google Translate-site weergeven?", cancel: "Terug naar vorig scherm", hint: "Wordt geopend in de geselecteerde taal. Japans opent zonder vertaling.", mixlist: "📋 Taalkaart & /top Samenvatting LIST", googleTrans: "🌐 Naar Google Translate-site" },
  "pl": { title: "Wyświetlić stronę Tłumacza Google?", cancel: "Wróć do poprzedniego ekranu", hint: "Otwiera się w wybranym języku. Japoński otwiera się bez tłumaczenia.", mixlist: "📋 Karta językowa & Podsumowanie /top LIST", googleTrans: "🌐 Przejdź do Tłumacza Google" },
  "fa": { title: "آیا می‌خواهید سایت Google Translate را نمایش دهید؟", cancel: "بازگشت به صفحه قبلی", hint: "در زبان انتخابی باز می‌شود. ژاپنی بدون ترجمه باز می‌شود.", mixlist: "📋 کارت زبان & خلاصه /top LIST", googleTrans: "🌐 رفتن به سایت ترجمه Google" },
  "he": { title: "להציג את אתר Google Translate?", cancel: "חזרה למסך הקודם", hint: "נפתח בשפה שנבחרה. יפנית נפתחת ללא תרגום.", mixlist: "📋 כרטיס שפה & סיכום /top LIST", googleTrans: "🌐 עבור לאתר Google Translate" },
  "el": { title: "Εμφάνιση ιστότοπου Google Μεταφραστή;", cancel: "Επιστροφή στην προηγούμενη οθόνη", hint: "Ανοίγει στην επιλεγμένη γλώσσα. Τα Ιαπωνικά ανοίγουν χωρίς μετάφραση.", mixlist: "📋 Κάρτα γλώσσας & Σύνοψη /top LIST", googleTrans: "🌐 Μετάβαση στον ιστότοπο Google Μεταφραστή" },
  "sv": { title: "Visa Google Translate-sidan?", cancel: "Tillbaka till föregående skärm", hint: "Öppnas på valt språk. Japanska öppnas utan översättning.", mixlist: "📋 Språkkort & /top Sammanfattning LIST", googleTrans: "🌐 Gå till Google Translate" },
  "da": { title: "Vis Google Oversæt-siden?", cancel: "Tilbage til forrige skærm", hint: "Åbner på det valgte sprog. Japansk åbner uden oversættelse.", mixlist: "📋 Sprogkort & /top Resumé LIST", googleTrans: "🌐 Gå til Google Oversæt" },
  "fi": { title: "Näytetäänkö Google Kääntäjä -sivu?", cancel: "Takaisin edelliseen näyttöön", hint: "Avautuu valitulla kielellä. Japani avautuu ilman käännöstä.", mixlist: "📋 Kielikortti & /top Yhteenveto LIST", googleTrans: "🌐 Siirry Google Kääntäjään" },
  "cs": { title: "Zobrazit stránku Google Translate?", cancel: "Zpět na předchozí obrazovku", hint: "Otevře se ve vybraném jazyce. Japonština se otevře bez překladu.", mixlist: "📋 Jazyková karta & Shrnutí /top LIST", googleTrans: "🌐 Přejít na Google Translate" },
  "ro": { title: "Afișați site-ul Google Translate?", cancel: "Înapoi la ecranul anterior", hint: "Se deschide în limba selectată. Japoneza se deschide fără traducere.", mixlist: "📋 Card limbă & Rezumat /top LIST", googleTrans: "🌐 Mergeți la Google Translate" },
  "hu": { title: "Megjelenítse a Google Fordító webhelyet?", cancel: "Vissza az előző képernyőre", hint: "A kiválasztott nyelven nyílik meg. A japán fordítás nélkül nyílik meg.", mixlist: "📋 Nyelvkártya & /top Összefoglaló LIST", googleTrans: "🌐 Ugrás a Google Fordítóhoz" },
  "sw": { title: "Onyesha tovuti ya Google Translate?", cancel: "Rudi kwenye skrini iliyopita", hint: "Inafunguliwa katika lugha iliyochaguliwa. Kijapani inafunguliwa bila tafsiri.", mixlist: "📋 Kadi ya lugha & Muhtasari /top LIST", googleTrans: "🌐 Nenda kwenye Google Translate" }
};

function getAcNavModalDict(lang) {
  var k = lang || "ja";
  var prefix = k.split("-")[0];
  return AC_NAV_MODAL_I18N[k] || AC_NAV_MODAL_I18N[prefix] || AC_NAV_MODAL_I18N["ja"];
}



function trimCardUrlTail(s){
  if(!s||typeof s!=="string") return "";
  return s.replace(/[\s).,;>'"\]]+$/,"");
}

function extractHttpUrlsFromString(str){
  if(!str||typeof str!=="string") return [];
  var re=/https?:\/\/[^\s<>"'`\]]+/gi;
  var out=[], m;
  while((m=re.exec(str))!==null){
    var u=trimCardUrlTail(m[0]);
    if(u) out.push(u);
  }
  return out;
}

function isTranslateInfrastructureUrl(urlStr){
  try{
    var h=new URL(urlStr,window.location.href).hostname.toLowerCase();
    if(h==="translate.google.com") return true;
    if(h==="translate.goog"||h.slice(-15)===".translate.goog") return true;
    return false;
  }catch(e){ return true; }
}

function isTopDestinationUrl(urlStr){
  try{
    var u=new URL(urlStr,window.location.href);
    var cur=(window.location.hostname||"").toLowerCase();
    var host=(u.hostname||"").toLowerCase();
    var path=(u.pathname||"").replace(/\/+$/,"")||"/";
    if(host===cur||host===""){
      if(path==="/top"||path.indexOf("/top/")===0) return true;
    }
    return false;
  }catch(e){ return false; }
}

function shortLinkLabel(href){
  try{
    var u=new URL(href);
    var t=u.host+u.pathname+(u.search?"?…":"");
    if(t.length>56) t=t.slice(0,53)+"\u2026";
    return t;
  }catch(e){ return href; }
}

function mergeCardOutboundLinks(c){
  var map={};
  function set(href, lab){
    if(!href||typeof href!=="string") return;
    href=trimCardUrlTail(href.trim());
    if(!/^https?:\/\//i.test(href)) return;
    if(isTranslateInfrastructureUrl(href)) return;
    if(isTopDestinationUrl(href)) return;
    var Lb=(lab&&String(lab).trim())?String(lab).trim():"";
    if(!map[href]) map[href]={href:href,label:Lb||shortLinkLabel(href)};
    else if(Lb) map[href].label=Lb;
  }
  var j;
  if(c.cardLinks&&c.cardLinks.length){
    for(j=0;j<c.cardLinks.length;j++){
      var lk=c.cardLinks[j];
      if(lk&&lk.href) set(lk.href, lk.label||"");
    }
  }
  var parts=[];
  if(typeof c.c==="string") parts=parts.concat(extractHttpUrlsFromString(c.c));
  if(typeof c.d==="string") parts=parts.concat(extractHttpUrlsFromString(c.d));
  for(j=0;j<parts.length;j++) set(parts[j], "");
  var out=[], k;
  for(k in map){
    if(Object.prototype.hasOwnProperty.call(map,k)) out.push(map[k]);
  }
  return out;
}

var searchEl=document.getElementById("search");
var pillsEl=document.getElementById("pills");
var mainEl=document.getElementById("main");
var activeRegion="all";

function buildPills(){
  var h='<button class="pill'+(activeRegion==="all"?' active':'')+'" data-r="all">All</button>';
  h+='<button class="pill'+(activeRegion===ABC_KEY?' active':'')+'" data-r="'+ABC_KEY+'">'+ABC_LABEL+'</button>';
  for(var i=0;i<REGIONS.length;i++){
    var isActive=(REGIONS[i]===activeRegion);
    h+='<button class="pill'+(isActive?' active':'')+'" data-r="'+REGIONS[i]+'">'+REGION_LABELS[REGIONS[i]]+'</button>';
  }
  pillsEl.innerHTML=h;
  var btns=pillsEl.querySelectorAll(".pill");
  for(var j=0;j<btns.length;j++){
    (function(b){
      b.addEventListener("click",function(){
        activeRegion=b.getAttribute("data-r");
        var all=pillsEl.querySelectorAll(".pill");
        for(var k=0;k<all.length;k++) all[k].className="pill";
        b.className="pill active";
        render();
      });
    })(btns[j]);
  }
}

function render(){
  var q=(searchEl.value||"").toLowerCase().trim();
  var base=L;
  if(activeRegion!=="all"&&activeRegion!==ABC_KEY){
    base=[];
    for(var i=0;i<L.length;i++){if(L[i].r===activeRegion)base.push(L[i]);}
  }
  var items=[];
  for(var i=0;i<base.length;i++){
    var c=base[i];
    if(!q||c.n.toLowerCase().indexOf(q)!==-1||c.t.toLowerCase().indexOf(q)!==-1||c.g.toLowerCase().indexOf(q)!==-1||c.c.toLowerCase().indexOf(q)!==-1||c.a.toLowerCase().indexOf(q)!==-1){
      items.push(c);
    }
  }
  if(!items.length){mainEl.innerHTML='<p class="empty">No languages found.</p>';return;}

  var html="";

  if(activeRegion===ABC_KEY){
    var sorted=items.slice().sort(function(a,b){
      var x=a.n.toLowerCase(),y=b.n.toLowerCase();
      return x<y?-1:x>y?1:0;
    });
    var groups={},letters=[];
    for(var i=0;i<sorted.length;i++){
      var letter=sorted[i].n.charAt(0).toUpperCase();
      if(!groups[letter]){groups[letter]=[];letters.push(letter);}
      groups[letter].push(sorted[i]);
    }
    letters.sort();
    for(var i=0;i<letters.length;i++){
      var arr=groups[letters[i]];
      html+='<section class="region-section"><h2 class="region-title">'+esc(letters[i])+'<span class="region-count">('+arr.length+')</span></h2><div class="grid">';
      for(var j=0;j<arr.length;j++) html+=cardHtml(arr[j]);
      html+='</div></section>';
    }
  }else if(activeRegion==="all"){
    /* All: L配列のカスタム順序をそのまま維持してフラット表示 */
    html+='<section class="region-section"><div class="grid">';
    for(var i=0;i<items.length;i++) html+=cardHtml(items[i]);
    html+='</div></section>';
  }else{
    var grouped={};
    grouped[activeRegion]=[];
    for(var i=0;i<items.length;i++){
      if(items[i].r===activeRegion) grouped[activeRegion].push(items[i]);
    }
    var arr=grouped[activeRegion];
    if(arr&&arr.length){
      html+='<section class="region-section"><h2 class="region-title">'+REGION_LABELS[activeRegion]+'<span class="region-count">('+arr.length+')</span></h2><div class="grid">';
      for(var j=0;j<arr.length;j++) html+=cardHtml(arr[j]);
      html+='</div></section>';
    }
  }
  mainEl.innerHTML=html;
}

// ===== エジプトカード マスター自動コピー =====
// エジプトカード（a:"EGYPT"）の c・d・cardLinks（エジプトとインカの地下でUFOと
// 日本語の石板が見つかった内容＋UFO原理解明のYouTube紹介）を参照する。
// ★[仕様変更] この内容は「エジプトカード」と「日本語カード(JAPAN)」だけに表示する。
//   エジプトカードは元から保持、日本語カードには末尾へマージ、その他の言語カードには
//   一切追記しない（同じ文章を全削除）。
var _egyptMaster = (function(){
  for(var _i=0;_i<L.length;_i++){
    if(L[_i].a==='EGYPT') return L[_i];
  }
  return null;
})();

function cardHtml(c){
  // DPRに応じて国旗の解像度を自動選択（最大w640 = 4K/8K対応）
  var dpr = window.devicePixelRatio || 1;
  var flagW = dpr >= 3 ? 'w640' : dpr >= 2 ? 'w320' : 'w160';
  var transHref = makeUrl(c.g);

  // ===== エジプトマスター自動マージ =====
  // ★[仕様変更] エジプトカードの内容（UFO・日本語の石板・UFO原理動画）は
  //   日本語カード(JAPAN)にのみ末尾追記する。エジプト自身は元から保持、
  //   その他の言語カードには一切追記しない。
  var cExt = c; // デフォルトはそのまま
  if(_egyptMaster && c.a === 'JAPAN'){
    var extraC = (_egyptMaster.c && c.c !== _egyptMaster.c)
      ? (c.c ? c.c + '\n\n' + _egyptMaster.c : _egyptMaster.c) : c.c;
    var extraD = (_egyptMaster.d && c.d !== _egyptMaster.d)
      ? (c.d ? c.d + '\n\n' + _egyptMaster.d : _egyptMaster.d) : c.d;
    // cardLinks: 既存 + エジプト分をマージ（重複href除去）
    var baseLinks = c.cardLinks || [];
    var egyptLinks = _egyptMaster.cardLinks || [];
    var mergedLinks = baseLinks.slice();
    var existHrefs = {};
    for(var _ei=0;_ei<baseLinks.length;_ei++){
      if(baseLinks[_ei]&&baseLinks[_ei].href) existHrefs[baseLinks[_ei].href]=1;
    }
    for(var _ej=0;_ej<egyptLinks.length;_ej++){
      var _elk = egyptLinks[_ej];
      if(_elk&&_elk.href&&!existHrefs[_elk.href]) mergedLinks.push(_elk);
    }
    cExt = {
      g:c.g, n:c.n, t:c.t, a:c.a, r:c.r,
      c:extraC, d:extraD,
      cardLinks:mergedLinks,
      bioScroll:c.bioScroll, fc:c.fc, p:c.p
    };
  }

  var merged = mergeCardOutboundLinks(cExt);
  var linksAttr = '';
  var linksHtml = '';
  if (merged.length) {
    linksAttr = ' data-ac-card-links="' + esc(JSON.stringify(merged)) + '"';
    for (var li = 0; li < merged.length; li++) {
      var lk = merged[li];
      var h = lk.href || '';
      var lab = lk.label || h;
      if (!h) continue;
      linksHtml += '<a class="card-exlink" href="' + esc(h) + '" target="_blank" rel="noopener noreferrer">' + esc(lab) + '</a>';
    }
  }
  var bioScrollClass = cExt.bioScroll ? ' card--bio-scroll' : '';
  // p フィールド: ネイティブ語名の後に追加表示するサブラベル（改行区切り）
  var pHtml = '';
  if (cExt.p) {
    var pLines = cExt.p.split('\n');
    for (var pi = 0; pi < pLines.length; pi++) {
      pHtml += '<span class="card-sublabel notranslate" translate="no">'+esc(pLines[pi])+'</span>';
    }
  }
  // n フィールド: bioScroll の場合は左右スクロール付き card-en-scroll で表示
  var nHtml = cExt.bioScroll
    ? '<span class="card-en card-en-scroll">'+esc(cExt.n)+'</span>'
    : '<span class="card-en">'+esc(cExt.n)+'</span>';
  return '<div class="card'+bioScrollClass+'" role="button" tabindex="0" data-lang-code="' + esc(cExt.g) + '" data-ac-translate-href="' + esc(transHref) + '"' + linksAttr + '>'
    +'<img class="card-flag" src="https://flagcdn.com/'+flagW+'/'+cExt.fc+'.png" alt="'+esc(cExt.c)+'" loading="lazy" decoding="async">'
    +'<span class="card-code notranslate" translate="no">'+esc(cExt.a)+'</span>'
    +'<span class="card-native notranslate" translate="no">'+esc(cExt.t).replace(/\n/g,'<br>')+'</span>'
    + pHtml
    + nHtml
    +'<span class="card-country">'+esc(cExt.c)+'</span>'
    +(cExt.d ? '<span class="card-country">'+esc(cExt.d)+'</span>' : '')
    + linksHtml
    +'</div>';
}

// 言語カード: div + 内部リンク。ルートでは googtrans を付けない。
(function(){
  var acNavPending = null;

  function clearGoogleTranslateCookie(){
    try{ document.cookie = 'googtrans=; path=/; max-age=0; SameSite=Lax'; }catch(e){}
    try{ document.cookie = 'googtrans=/ja/ja; path=/; max-age=0; SameSite=Lax'; }catch(e){}
    try{
      var parts = location.hostname.split('.');
      if(parts.length >= 2){
        var dom = '.' + parts.slice(-2).join('.');
        document.cookie = 'googtrans=; path=/; domain=' + dom + '; max-age=0; SameSite=Lax';
        document.cookie = 'googtrans=/ja/ja; path=/; domain=' + dom + '; max-age=0; SameSite=Lax';
      }
    }catch(e){}
  }

  function persistLangPreference(gc, setGoogtrans){
    try{ sessionStorage.setItem('selectedLang', gc); }catch(e){}
    try{ localStorage.setItem('selectedLang', gc); }catch(e){}
    if(!setGoogtrans) return;
    var v = '/ja/' + (gc === 'ja' ? 'ja' : gc);
    try{ document.cookie = 'googtrans=' + v + '; path=/; max-age=31536000; SameSite=Lax'; }catch(e){}
    try{
      var host = location.hostname;
      var parts = host.split('.');
      if(parts.length >= 2){
        document.cookie = 'googtrans=' + v + '; path=/; domain=.' + parts.slice(-2).join('.') + '; max-age=31536000; SameSite=Lax';
      }
    }catch(e){}
  }

  function readCardLinksFromEl(el){
    if(!el) return [];
    var raw = el.getAttribute('data-ac-card-links');
    if(!raw) return [];
    try{ return JSON.parse(raw); }catch(e){ return []; }
  }

  function clearAcNavExtraBtns(){
    var wrap = document.getElementById('acNavExtraBtns');
    if(wrap) wrap.innerHTML = '';
  }

  function renderAcNavExtras(links){
    var wrap = document.getElementById('acNavExtraBtns');
    if(!wrap) return;
    wrap.innerHTML = '';
    if(!links || !links.length) return;
    var cap = document.createElement('p');
    cap.className = 'ac-nav-modal__extra-caption';
    cap.textContent = 'この言語カードの追加リンク（googtrans なし・新しいタブ）';
    wrap.appendChild(cap);
    for(var i=0;i<links.length;i++){
      (function(entry){
        var href = entry.href || '';
        if(!href) return;
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'ac-nav-modal__btn';
        btn.textContent = entry.label || href;
        btn.addEventListener('click', function(){
          var pend = acNavPending;
          if(!pend) return;
          var gc = pend.gc;
          clearGoogleTranslateCookie();
          persistLangPreference(gc, false);
          closeAcNavModal();
          window.open(href, '_blank', 'noopener,noreferrer');
        });
        wrap.appendChild(btn);
      })(links[i]);
    }
  }

  function closeAcNavModal(){
    var m = document.getElementById('acNavChoiceModal');
    if(m){ m.classList.remove('is-open'); m.setAttribute('hidden',''); }
    clearAcNavExtraBtns();
    acNavPending = null;
  }

  function openAcNavModal(gc, cardLinks){
    var pageUrl = location.href.split('#')[0].split('?')[0]; // Google翻訳サイト用：現在のクリーンURL
    var dest = {
      ac:          makeAcNavAudiocafeRoot(gc),
      acTop:       makeAcNavRaysoftTop(gc),
      aruaru:      makeAcNavAruaruUrl(gc),
      aruaruLady:  makeAcNavAruaruLadyUrl(gc),
      rakutenMobile: makeAcNavRakutenMobileUrl(gc),
      googleTrans: makeAcNavGoogleTransUrl(gc, pageUrl),
      mixlist:     "https://audiocafe.tokyo/?lang=" + encodeURIComponent((gc && (""+gc).trim()) ? gc : "ja") + "&mixlist=1"
    };
    dest.ac = guardAcNavRootHref(dest.ac, gc);
    acNavPending = { gc: gc, dest: dest, cardLinks: cardLinks || [] };
    var m = document.getElementById('acNavChoiceModal');
    function syncAcNavBtnHrefs(d){
      var pairs = [
        ['acNavBtnAc', 'ac'],
        ['acNavBtnAcTop', 'acTop'],
        ['acNavBtnAruaru', 'aruaru'],
        ['acNavBtnAruaruLady', 'aruaruLady'],
        ['acNavBtnRakutenMobile', 'rakutenMobile'],
        ['acNavBtnMixlist', 'mixlist']
      ];
      for(var pi=0;pi<pairs.length;pi++){
        var el = document.getElementById(pairs[pi][0]);
        var k = pairs[pi][1];
        if(!el || !d[k]) continue;
        el.setAttribute('data-ac-nav-dest', k);
        el.setAttribute('data-ac-nav-href', d[k]);
      }
    }
    syncAcNavBtnHrefs(dest);
    if(!m){
      clearGoogleTranslateCookie();
      persistLangPreference(gc, false);
      location.assign(dest.ac);
      return;
    }
    // 選択された言語に応じてタイトル・キャンセル文言を切り替え
    try {
      var dict = getAcNavModalDict(gc);
      var titleEl = document.getElementById('acNavChoiceTitle');
      var cancelEl = document.getElementById('acNavBtnCancel');
      var hintEl = m.querySelector('.ac-nav-modal__hint');
      var mixlistEl = document.getElementById('acNavBtnMixlist');
      var googleTransEl = document.getElementById('acNavBtnGoogleTrans');
      if (titleEl) titleEl.textContent = dict.title;
      if (cancelEl) cancelEl.textContent = dict.cancel;
      if (hintEl) hintEl.textContent = dict.hint;
      if (mixlistEl && dict.mixlist) mixlistEl.textContent = dict.mixlist;
      if (googleTransEl && dict.googleTrans) googleTransEl.textContent = dict.googleTrans;
    } catch(e) {}
    m.classList.add('is-open');
    m.removeAttribute('hidden');
    renderAcNavExtras(acNavPending.cardLinks);
    var b = document.getElementById('acNavBtnAc');
    if(b) b.focus();
  }

  function openLanguageCard(card, ev){
    if(ev.ctrlKey || ev.metaKey || ev.shiftKey || ev.altKey) return false;
    var gc = card.getAttribute('data-lang-code');
    if(!gc) return false;
    openAcNavModal(gc, readCardLinksFromEl(card));
    return true;
  }

  function onClick(ev){
    if(ev.button !== 0) return;
    var ex = ev.target.closest && ev.target.closest('a.card-exlink');
    if(ex){
      var card0 = ex.closest('.card[data-lang-code]');
      if(card0){
        clearGoogleTranslateCookie();
        persistLangPreference(card0.getAttribute('data-lang-code'), false);
      }
      return;
    }
    // ★本文(.card-country: c.c / c.d)のクリック/タップは画面遷移させない。
    //   テキスト選択・コピーを優先する（遷移するのは a.card-exlink リンクのみ）。
    if(ev.target.closest && ev.target.closest('.card-country')) return;
    var card = ev.target.closest && ev.target.closest('.card[data-lang-code]');
    if(!card) return;
    if(openLanguageCard(card, ev)){
      ev.preventDefault();
      ev.stopPropagation();
    }
  }

  function onAuxClick(ev){
    if(ev.button !== 1) return;
    if(ev.target.closest && ev.target.closest('a.card-exlink')) return;
    if(ev.target.closest && ev.target.closest('.card-country')) return;
    var card = ev.target.closest && ev.target.closest('.card[data-lang-code]');
    if(!card) return;
    var translateHref = card.getAttribute('data-ac-translate-href');
    if(!translateHref) return;
    ev.preventDefault();
    clearGoogleTranslateCookie();
    persistLangPreference(card.getAttribute('data-lang-code'), false);
    window.open(translateHref, '_blank', 'noopener,noreferrer');
  }

  function onCardKeydown(ev){
    if(ev.key !== 'Enter' && ev.key !== ' ') return;
    var card = ev.target.closest && ev.target.closest('.card[data-lang-code]');
    if(!card || !mainEl || !mainEl.contains(card)) return;
    if(ev.target.closest && ev.target.closest('a.card-exlink')) return;
    if(openLanguageCard(card, ev)){
      ev.preventDefault();
      ev.stopPropagation();
    }
  }

  if(mainEl) mainEl.addEventListener('click', onClick, true);
  if(mainEl) mainEl.addEventListener('auxclick', onAuxClick, true);
  if(mainEl) mainEl.addEventListener('keydown', onCardKeydown, true);

  function wireAcNavModal(){
    var m = document.getElementById('acNavChoiceModal');
    if(!m) return;
    m.addEventListener('click', function(ev){
      if(ev.target.getAttribute && ev.target.getAttribute('data-ac-nav-dismiss') === '1') closeAcNavModal();
    });
    var modalBox = m.querySelector('.ac-nav-modal__box');
    if(modalBox){
      modalBox.addEventListener('click', function(ev){ ev.stopPropagation(); });
    }
    var btnAc    = document.getElementById('acNavBtnAc');
    var btnAcTop = document.getElementById('acNavBtnAcTop');
    var btnAru   = document.getElementById('acNavBtnAruaru');
    var btnLady  = document.getElementById('acNavBtnAruaruLady');
    var btnGt    = document.getElementById('acNavBtnGoogleTrans');
    var btnCx    = document.getElementById('acNavBtnCancel');
    function goDestHref(href, gc, destKey){
      if(!href) return;
      clearGoogleTranslateCookie();
      persistLangPreference(gc, false);
      closeAcNavModal();
      if(gc === 'ja'){
        // #translateto= のみ除去。?lang=ja は残して遷移先でキャッシュクリアさせる
        try{
          var u = new URL(href, location.href);
          var h = u.hash || '';
          if(h){ h = h.replace(/[#&]?translateto=[^&]*/g,'').replace(/^#?&/,'#').replace(/^#$/,''); u.hash = h; }
          href = u.toString().replace(/#$/,'');
        }catch(e){}
        // sessionStorageにforce_jaフラグをセット（遷移先で参照）
        try{ sessionStorage.setItem('__ac_force_ja','1'); }catch(e){}
        try{ sessionStorage.setItem('selectedLang','ja'); }catch(e){}
        try{ localStorage.setItem('selectedLang','ja'); }catch(e){}
        // 現在のドメインのgoogtransを全パスで完全削除
        try{
          var _domains = [location.hostname, '.'+location.hostname];
          var _hp = location.hostname.split('.');
          if(_hp.length >= 2) _domains.push('.'+_hp.slice(-2).join('.'));
          var _paths = ['/','/top','/top/','/aruaru','/aruaru/','/aruaru-lady','/aruaru-lady/','/rakuten-mobile','/rakuten-mobile/'];
          var _exp = 'expires=Thu, 01 Jan 1970 00:00:00 GMT';
          _domains.forEach(function(d){
            _paths.forEach(function(p){
              document.cookie='googtrans=; path='+p+'; domain='+d+'; max-age=0; '+_exp+'; SameSite=Lax';
            });
          });
          _paths.forEach(function(p){
            document.cookie='googtrans=; path='+p+'; max-age=0; '+_exp+'; SameSite=Lax';
          });
        }catch(e){}
      }
      try{
        var uNav = new URL(href, location.href);
        var pNav = uNav.pathname.replace(/\/+$/,"") || "/";
        // BUG修正: mixlist遷移時に lang が抜けていたら gc から復元する
        if(uNav.searchParams.get("mixlist") && !uNav.searchParams.get("lang")){
          var fixGc = (gc && (""+gc).trim()) ? (""+gc).trim() : 'ja';
          uNav.searchParams.set('lang', fixGc);
          href = uNav.toString();
        }
        if((pNav === "/" || pNav === "/index.php" || pNav === "/rakuten-mobile" || pNav === "/rakuten-mobile/index.php") && uNav.searchParams.get("lang")){
          uNav.searchParams.set('_ac_nav', String(Date.now()));
          href = uNav.toString();
        }
      }catch(e){}
      location.assign(href);
    }
    function goDest(key){
      if(!acNavPending) return;
      var gc = acNavPending.gc;
      var href;
      // BUG修正: mixlist は dest の古い値ではなく gc から再生成
      if(key === 'mixlist'){
        var sg = (gc && (""+gc).trim()) ? (""+gc).trim() : 'ja';
        href = 'https://audiocafe.tokyo/?lang=' + encodeURIComponent(sg) + '&mixlist=1';
      } else {
        href = acNavPending.dest[key];
      }
      goDestHref(href, gc, key);
    }
    function resolveAcNavHref(el, key, gc, destObj){
      // BUG修正: mixlist は古い属性値を一切信用せず、gc から必ず再生成する。
      // gc が空・null・undefined の場合は 'ja' をデフォルトにし、lang抜けを完全防止。
      var safeGc = (gc && (""+gc).trim()) ? (""+gc).trim() : 'ja';
      if(key === 'mixlist'){
        return 'https://audiocafe.tokyo/?lang=' + encodeURIComponent(safeGc) + '&mixlist=1';
      }
      var href = (el && el.getAttribute('data-ac-nav-href')) || (destObj && destObj[key]) || '';
      if(!href && gc){
        if(key === 'ac') href = makeAcNavAudiocafeRoot(gc);
        else if(key === 'acTop') href = makeAcNavRaysoftTop(gc);
        else if(key === 'aruaru') href = makeAcNavAruaruUrl(gc);
        else if(key === 'aruaruLady') href = makeAcNavAruaruLadyUrl(gc);
        else if(key === 'rakutenMobile') href = makeAcNavRakutenMobileUrl(gc);
      }
      if(key === 'ac') href = guardAcNavRootHref(href, gc);
      return href;
    }
    function navBtnDestKey(el, fallbackKey){
      if(el && el.id === 'acNavBtnAc') return 'ac';
      if(el && el.id === 'acNavBtnAcTop') return 'acTop';
      if(el && el.id === 'acNavBtnAruaru') return 'aruaru';
      if(el && el.id === 'acNavBtnAruaruLady') return 'aruaruLady';
      if(el && el.id === 'acNavBtnRakutenMobile') return 'rakutenMobile';
      if(el && el.id === 'acNavBtnMixlist') return 'mixlist';
      return (el && el.getAttribute('data-ac-nav-dest')) || fallbackKey;
    }
    function bindNavBtn(el, key){
      if(!el) return;
      el.addEventListener('click', function(ev){
        ev.preventDefault();
        ev.stopPropagation();
        var pend = acNavPending;
        if(!pend) return;
        var destKey = navBtnDestKey(el, key);
        var href = resolveAcNavHref(el, destKey, pend.gc, pend.dest);
        goDestHref(href, pend.gc, destKey);
      });
    }
    bindNavBtn(btnAc, 'ac');
    bindNavBtn(btnAcTop, 'acTop');
    bindNavBtn(btnAru, 'aruaru');
    // aruaru-lady: 直接 /aruaru-lady?lang=XX#translateto=XX へ遷移
    // (旧3択サブモーダルは廃止。/aruaru-lady 内の翻訳ウィジェットで切替する)
    bindNavBtn(btnLady, 'aruaruLady');
    bindNavBtn(document.getElementById('acNavBtnRakutenMobile'), 'rakutenMobile');
    bindNavBtn(document.getElementById('acNavBtnMixlist'), 'mixlist');
    if(btnGt)    btnGt.addEventListener('click',    function(){
      if(!acNavPending) return;
      var href = acNavPending.dest['googleTrans'];
      closeAcNavModal();
      window.open(href, '_blank', 'noopener,noreferrer');
    });
    if(btnCx) btnCx.addEventListener('click', closeAcNavModal);
    document.addEventListener('keydown', function(ev){
      if(ev.key !== 'Escape') return;
      if(!m.classList.contains('is-open')) return;
      closeAcNavModal();
    });
  }
  wireAcNavModal();

})();

function esc(s){var d=document.createElement("div");d.textContent=s;return d.innerHTML;}

searchEl.addEventListener("input",function(){render();});
buildPills();
render();

/* Google Translate URL repair (images + links)
   - Fixes translate.goog proxy URLs and translate.google.com/website?u= wrapper URLs
   - Ensures flag images and external media still load even after translation */
(function(){
  // Only repair image URLs (flags) so we don't break translated navigation.
  function restoreUrl(url){
    if(!url||typeof url!=="string") return url;
    try{
      var u=new URL(url, window.location.href);
      var host=u.hostname;

      // translate.google.com wrapper
      if(host==="translate.google.com" && (u.pathname==="/website" || u.pathname==="/translate")){
        var wrapped=u.searchParams.get("u");
        if(!wrapped) return url;
        var prev=wrapped;
        for(var di=0;di<3;di++){
          try{
            var dec=decodeURIComponent(prev);
            if(dec===prev) break;
            prev=dec;
          }catch(e){break;}
        }
        return prev;
      }

      // translate.goog proxy
      if(host.indexOf(".translate.goog")!==-1){
        var originalDomain=host.replace(".translate.goog","").replace(/-/g,".");
        var params=u.searchParams;
        params.delete("_x_tr_sl");params.delete("_x_tr_tl");params.delete("_x_tr_hl");
        params.delete("_x_tr_pto");params.delete("_x_tr_hist");
        var search=params.toString();
        return "https://"+originalDomain+u.pathname+(search?"?"+search:"")+u.hash;
      }
      return url;
    }catch(e){
      return url;
    }
  }

  function fixAll(){
    var imgs=document.querySelectorAll("img[src]");
    for(var i=0;i<imgs.length;i++){
      var src=imgs[i].getAttribute("src");
      // Only touch flagcdn images; leave all other images untouched.
      if(!src) continue;
      if(src.indexOf("flagcdn.com")===-1) continue;
      var fixed=restoreUrl(src);
      if(fixed!==src) imgs[i].setAttribute("src",fixed);
    }
  }

  if(document.readyState==="loading"){
    document.addEventListener("DOMContentLoaded",fixAll);
  }else{
    fixAll();
  }
  window.addEventListener("load",function(){fixAll(); setTimeout(fixAll,1000); setTimeout(fixAll,3000);});
  try{
    var mo=new MutationObserver(function(){fixAll();});
    mo.observe(document.body||document.documentElement,{childList:true,subtree:true,attributes:true,attributeFilter:["href","src"]});
  }catch(e){}
})();
})();
</script>

<script src="https://www.youtube.com/iframe_api"></script>
<script>
(function(){
"use strict";
// =====================================================================
// ★ YouTubeシリーズの設定
//    btn    : シリーズボタンのテキスト
//    label  : 右下パネルに表示されるシリーズ名
//    urls   : 関連URLを上から列挙（ブログ・HP・YouTube・Facebook・Googleドライブ等）
//             → 各URLをフェッチしてページ内を調べ
//               YouTube > Facebook > Googleドライブ の優先順位で自動選択・再生
//             ※ YouTubeやyoutu.beのURLを直接書いた場合はフェッチ不要で即再生
// =====================================================================

var SEARCH_SERIES = [
{
    btn: 'KRS Rey Audio WARP-5',
    label: 'KRS Rey Audio WARP-5',
    urls: [
      'https://youtu.be/6EUEtclPZ-k?si=BHWPzAk9Jh60mIyA'
    ]
  },
  {
    btn: 'QUAD JBL4351 CUSTOM',
    label: 'QUAD 4351',
    urls: [
      'https://www.youtube.com/shorts/0fMzUOTsxJs'
    ]
  },
   {
    btn: 'JBL Summit EVEREST 2026',
    label: 'JBL Summit EVEREST 2026',
    urls: [
      'https://www.youtube.com/watch?v=iz3jKEw_PoY',
      'https://www.youtube.com/watch?v=dgaWyWG0MGA'
    ]
  },
  {
    btn: 'JBL Summit K2',
    label: 'JBL Summit K2',
    urls: [
      'https://www.youtube.com/shorts/5GXnCuzYMMs'
    ]
  },
  {
    btn: 'JBL DD67000',
    label: 'JBL DD67000',
    urls: [
      'https://www.youtube.com/results?search_query=DD67000'
    ]
  },

  {
    btn: 'QUAD JBL',
    label: 'QUAD JBL',
    urls: [
      'https://youtu.be/asjUC5y1bdg?si=noL5h_fNud9dJlv0'
    ]
  },

  {
    btn: 'KRS 4351',
    label: 'World Class JBL Speakers 10th Anniv KENRICK SOUND 4351 & McIntosh MC901　憧れのサウンドがご自宅に！ケンリックサウンド10周年仕様',
    urls: [
      'https://www.youtube.com/watch?v=mSDVnO5gFYk&t=552s',
      'https://youtu.be/bmNTCy6GDtM?si=pFjGEFEeb9WxyFxi',
      'https://www.youtube.com/results?search_query=%E6%9C%80%E9%AB%98%E5%B3%B0%E3%82%B9%E3%83%94%E3%83%BC%E3%82%AB%E3%83%BC%E3%80%80%E5%9C%A7%E5%B7%BB%E3%80%80KRS4351'
    ]
  },
  {
    btn: '岡野昭仁。歌唱力がカンストするとこうなるって知ってた？　　　　　　　　',
    label: 'アカペラ最強',
    urls: [
      'https://www.youtube.com/shorts/LFRgAkNSpdg'
    ]
  },
{
    btn: 'Digitalconcerthall',
    label: 'デジタルコンサートホール',
    urls: [
      'https://www.digitalconcerthall.com/ja'
    ]
  },
{
    btn: 'パガーニ　David Garrett - Paganini Caprice Nº 24',
    label: 'paga-ni',
    urls: [
      'https://www.youtube.com/watch?v=ITzcZia7fsQ&list=RDITzcZia7fsQ&start_radio=1'
    ]
  },{
    btn: 'パガーニ　N.Paganini:Caprice No.24 “Thema”#ヴァイオリニスト',
    label: 'パガーニ　N.Paganini:Caprice No.24 “Thema”#ヴァイオリニスト',
    urls: [
      'https://youtube.com/shorts/SfFoTQF9WPE?si=ziGojL0jEKte52ie'
    ]
  },
  {
    btn: 'パパっ子あおいちゃん',
    label: 'パパっ子あおいちゃん',
    urls: [
      'https://www.youtube.com/shorts/vXl0uoGnLC4',
      'https://www.youtube.com/shorts/M8XoS_8aDVA',
      'https://www.youtube.com/results?search_query=%E3%81%8A%E4%BB%95%E4%BA%8B%E8%A1%8C%E3%81%8B%E3%81%AA%E3%81%84%E3%81%A7'
  ]
  },
  {
    btn: 'ラウンドアバウト　環状交差点',
    label: 'ラウンドアバウト　環状交差点',
    urls: [
      'https://www.youtube.com/results?search_query=%E3%83%A9%E3%82%A6%E3%83%B3%E3%83%89%E3%82%A2%E3%83%90%E3%82%A6%E3%83%88+%E7%92%B0%E7%8A%B6%E4%BA%A4%E5%B7%AE%E7%82%B9'
    ]
  },
{
    btn: '懸垂式モノレールの緊急脱出',
    label: '懸垂式モノレール',
    urls: [
      'https://www.youtube.com/results?search_query=%E6%87%B8%E5%9E%82%E5%BC%8F%E3%83%A2%E3%83%8E%E3%83%AC%E3%83%BC%E3%83%AB+%E7%B7%8A%E6%80%A5%E8%84%B1%E5%87%BA'
    ]
  },

  {
    btn: '牛糞の堆肥（たいひ）化　循環農法　有機土壌　米ぬか　納豆菌　酵母菌　乳酸菌',
    label: '懸垂式モノレール',
    urls: [
      'https://www.youtube.com/results?search_query=%E7%89%9B%E7%B3%9E%E3%81%AE%E5%A0%86%E8%82%A5%EF%BC%88%E3%81%9F%E3%81%84%E3%81%B2%EF%BC%89%E5%8C%96%E3%80%80%E6%B3%A8%E6%84%8F%E3%80%80%E3%83%90%E3%82%AF%E3%83%81%E3%83%A3%E3%83%BC%E3%80%80%E5%BE%AA%E7%92%B0%E8%BE%B2%E6%B3%95%E3%80%80%E6%9C%89%E6%A9%9F%E5%9C%9F%E5%A3%8C%E3%80%80%E7%B1%B3%E3%81%AC%E3%81%8B%E3%80%80%E7%B4%8D%E8%B1%86%E8%8F%8C%E3%80%80%E9%85%B5%E6%AF%8D%E8%8F%8C%E3%80%80%E4%B9%B3%E9%85%B8%E8%8F%8C%E3%80%80'
    ]
  },
  
{
    btn: '神道',
    label: 'NATARIE PORTMAN',
    urls: [
      'https://www.youtube.com/watch?v=Ywhs17jhxec',
      'https://www.youtube.com/shorts/t57FbTpjOBM',
      'https://www.youtube.com/watch?v=fUtm2ddH8kk',
      'https://www.youtube.com/watch?v=yArA3iJZk1k',
      'https://www.youtube.com/shorts/t4qDKTlkeK0',
      'https://www.youtube.com/watch?v=_PxhV6s8Wso',
      'https://www.youtube.com/results?search_query=%E7%A5%9E%E7%A4%BE%E4%BB%8F%E9%96%A3'
    ]
  },
  {
    btn: 'オアスペ日本が救世主',
    label: 'オアスペ　日本人の正体　救世主',
    urls: [
      'https://youtu.be/J79k0z0bFb4?si=RWMvFF6ew_OQMOG1',
      'https://www.youtube.com/embed/J79k0z0bFb4',
      'https://www.youtube.com/results?search_query=%E3%82%AA%E3%82%A2%E3%82%B9%E3%83%9A%E3%80%80%E6%97%A5%E6%9C%AC%E4%BA%BA%E3%81%AE%E6%AD%A3%E4%BD%93%E3%80%80%E6%95%91%E4%B8%96%E4%B8%BB'
      
    ]
  },
   
{
    btn: 'エジプトとインカの地下から日本語の石板',
    label: 'エジプトとインカの地下から日本語の石板',
    urls: [
      'https://youtu.be/C8Szkx4ILwQ?si=d89IMnIxO6SsnDYH'
    ]
  },
{
    btn: 'Japanese stone tablets found underground in Egypt and the Inca civilization.',
    label: 'エジプトとインカの地下から日本語の石板',
    urls: [
      'https://youtu.be/C8Szkx4ILwQ?si=d89IMnIxO6SsnDYH'
    ]
  },
  {
    btn: 'グレート・テイキングとは？　それを防ぐには？',
    label: 'グレート・テイキングとは？',
    urls: [
      'https://www.youtube.com/results?search_query=%E3%82%B0%E3%83%AC%E3%83%BC%E3%83%88%E3%83%BB%E3%83%86%E3%82%A4%E3%82%AD%E3%83%B3%E3%82%B0'
    ]
  },
  {
    btn: 'What is The Great Taking? How can it be prevented?',
    label: 'The Great Taking',
    urls: [
      'https://www.youtube.com/results?search_query=The+Great+Taking'
    ]
  },

  {
    btn: 'Cancer Therapy1',
    label: 'Cancer Therapy1',
    urls: [
      'https://www.youtube.com/watch?v=j_-8ifQauSI',
      'https://www.youtube.com/watch?v=9zA_vZ_FD9w',
      'https://www.youtube.com/watch?v=9KqdkTxtjKM',
      'https://www.youtube.com/shorts/9hV4Cka33QY',
      'https://www.youtube.com/watch?v=MNg_FyFAYMQ',
      'https://www.youtube.com/results?search_query=%E9%87%8D%E6%9B%B9%E6%B0%B4%E3%81%A8%E3%82%AF%E3%82%A8%E3%83%B3%E9%85%B8%E6%B0%B4%E3%82%92%E9%A3%B2%E3%82%93%E3%81%A7%E3%82%AC%E3%83%B3%E6%B2%BB%E7%99%82%E3%80%80Citric+Acid%E3%80%80%EF%BC%86%E3%80%80Baking+Soda%E3%80%80WATER+for+Citric+Acid+drink+for+Cancer+therapy'
    ]
  },
  {
    btn: 'Cancer Therapy2',
    label: 'Cancer Therapy2',
    urls: [
      'https://www.youtube.com/shorts/Q4AVdrv7AIA',
      'https://www.youtube.com/watch?v=r1yAtM9fIh8',
      'https://www.youtube.com/watch?v=ZKRBr5Zb6uY',
      'https://www.youtube.com/watch?v=lp-MB9cnEEI',
      'https://www.youtube.com/shorts/k9SKDS_aE30',
      'https://www.youtube.com/watch?v=pd5coVZ8VPs',
      'https://www.youtube.com/watch?v=RZjF9TCjoWo',
      'https://www.youtube.com/shorts/eRBwweOtpTI',
      'https://www.youtube.com/watch?v=c35-ApV5PmA',
      'https://www.youtube.com/shorts/5GlMITvrr1I',
      'https://www.youtube.com/results?search_query=A+Cancer+Treatment+Using+Bananas+%26+Pineapple+%26+Bufferin+A+%26+Aspirin'
    ]
  },
  {
    btn: '大黒摩季',
    label: '大黒摩季',
    urls: [
      'https://www.youtube.com/results?search_query=%E5%A4%A7%E9%BB%92%E6%91%A9%E5%AD%A3%E3%80%80%E3%83%92%E3%83%83%E3%83%88%E6%9B%B2%E3%80%80%E3%83%99%E3%82%B9%E3%83%88'
    ]
  },
  {
    btn: '山口百恵',
    label: '山口百恵　ヒット曲　ベスト',
    urls: [
      'https://www.youtube.com/results?search_query=%E5%B1%B1%E5%8F%A3%E7%99%BE%E6%81%B5%E3%80%80%E3%83%92%E3%83%83%E3%83%88%E6%9B%B2%E3%80%80%E3%83%99%E3%82%B9%E3%83%88'
    ]
  },
 
  {
    btn: '岡野昭仁',
    label: '玉置浩二&ポルノグラフティ（岡野昭仁）『サウダージ』　＃music ＃ポルノグラフティ　＃玉置浩二',
    urls: [
      'https://www.youtube.com/shorts/65HmcYsqUcI',
      'https://www.youtube.com/results?search_query=%E5%B2%A1%E9%87%8E%E6%98%AD%E4%BB%81'
    ]
  },
{
    btn: 'NO JUMBO JET 9.11',
    label: 'NO JUMBO JET 9.11',
    urls: [
      'https://www.youtube.com/watch?v=jBiP_W29_fM&list=PLCjojjmWc-Ps5TZyVEh6z_UcZqjautila'
    ]
  },



  
 
  
  
  
  
  {
    btn: 'B&W Nautilus',
    label: 'B&W Nautilus speakers',
    urls: [
      'https://www.youtube.com/results?search_query=B%2526W+Nautilus+speakers'
    ]
  },
  {
    btn: 'B&W 800 801',
    label: 'B&W 800 801 speakers',
    urls: [
      'https://www.youtube.com/results?search_query=B%2526W+800+801'
    ]
  },
  {
    btn: 'B&W 805 705',
    label: 'B&W 805 705',
    urls: [
      'https://www.youtube.com/results?search_query=B%2526W+805+705'
    ]
  },
  {
    btn: 'B&W 704',
    label: 'B&W 704',
    urls: [
      'https://www.youtube.com/results?search_query=B%2526W+704'
    ]
  },
  {
    btn: 'ESD Acoustic Dragon1',
    label: 'ESD Acoustic Dragon',
    urls: [
      'https://www.youtube.com/shorts/8Ju6OEg1da4',
      'https://www.youtube.com/shorts/XC3vBVYpMPQ',
      'https://www.youtube.com/watch?v=x6PJXsFeWvo',
      'https://www.youtube.com/watch?v=05lqO38PDO8'
      
    ]
  },
  {
    btn: 'ESD Acoustic Dragon2',
    label: 'ESD Acoustic Dragon',
    urls: [
      'https://www.youtube.com/watch?v=zbj9TfJZrmE',
      'https://www.youtube.com/watch?v=kiAFzNZbKCY',
      'https://www.youtube.com/watch?v=e4BInrcX_Mg'
    ]
  },
  {
    btn: 'World Wide Shipping. ESD Acoustic Super Dragon',
    label: 'Shop. ESD Acoustic Super Dragon',
    urls: [
      'https://esdacoustic.store/collections/speaker/products/super-dragon'
    ]
  },
  
  {
    btn: 'TONOさんPART1【驚愕！】空間そのものがスピーカーだ！',
    label: 'TONO1',
    urls: [
      'https://www.youtube.com/watch?v=ufGJUrNDmdA'
    ]
  },
  {
    btn: 'TONOさんPART2【衝撃！】オーディオ畑',
    label: 'TONO',
    urls: [
      'https://www.youtube.com/watch?v=jSp-q5i1RrI'
    ]
  },
   
 
  

{
    btn: 'JBL4345（KRS）アルニコ',
    label: 'JBL KRS',
    urls: [
      'https://www.youtube.com/results?search_query=JBL%E3%80%804345%E3%80%80%E3%82%A2%E3%83%AB%E3%83%8B%E3%82%B3'
    ]
  },
{
    btn: 'JBL custom',
    label: 'JBL KRS',
    urls: [
      'https://www.youtube.com/results?search_query=Best+HiFi+High-End+JBL+custom+cabinet+By+Lansing'
    ]
  },
  {
    btn: 'KRS2401',
    label: 'KRS2401',
    urls: [
      'https://youtube.com/results?sp=mAEA&search_query=%E5%AE%8C%E6%88%90%EF%BC%81%E3%82%A8%E3%82%AF%E3%82%B9%E3%82%AF%E3%83%AB%E3%83%BC%E3%82%B7%E3%83%962401%E5%9E%8B%E3%82%B1%E3%83%B3%E3%83%AA%E3%83%83%E3%82%AF%E3%82%B5%E3%82%A6%E3%83%B3%E3%83%89%E3%82%B9%E3%83%94%E3%83%BC%E3%82%AB%E3%83%BC%E3%80%80Exclusive+2401+Style+KENRICK+Speakers+JBL+2231A+%2B+375+%2B+075+Completed'
    ]
  },
  {
    btn: 'POLK AUDIO R700BRN',
    label: 'POLK AUDIO R700BRN',
    urls: [
      'https://www.youtube.com/results?search_query=POLK+AUDIO+R700BRN'
    ]
  },
  {
    btn: 'D412EX',
    label: 'D412EX',
    urls: [
      'https://www.youtube.com/results?search_query=D412EX'
    ]
  },
  {
    btn: 'Custom-made speakers',
    label: 'Custom-made speakers',
    urls: [
      'https://www.google.com/search?q=Custom-made+speakers&sca_esv=f76dbb9d142d848d&sxsrf=ANbL-n4WcpSNZL_UBAywFoRcSzND9XMYRA%3A1780924073445&ei=qb4mauLjGpHi1e8Pm7qU4Qg&biw=958&bih=910&ved=0ahUKEwii3JOW2_eUAxURcfUHHRsdJYwQ4dUDCBA&oq=Custom-made+speakers&gs_lp=Egxnd3Mtd2l6LXNlcnAiFEN1c3RvbS1tYWRlIHNwZWFrZXJzMgQQABgeMgQQABgeMgQQABgeMgYQABgeGAoyBhAAGAgYHjIGEAAYCBgeMgYQABgIGB4yBhAAGAgYHjIGEAAYCBgeMgYQABgIGB5IgQlQAFgAcAB4AZABAJgBX6ABX6oBATG4AQzIAQD4AQL4AQGYAgGgAmWYAwCSBwMwLjGgB8kDsgcDMC4xuAdlwgcDMi0xyAcEgAgB&sclient=gws-wiz-serp'
    ]
  },
  {
    btn: 'オーダーメイドスピーカー',
    label: 'オーダーメイドスピーカー',
    urls: [
      'https://www.google.com/search?q=%E3%82%AA%E3%83%BC%E3%83%80%E3%83%BC%E3%83%A1%E3%82%A4%E3%83%89%E3%82%B9%E3%83%94%E3%83%BC%E3%82%AB%E3%83%BC&rlz=1C1HKFL_jaJP1208JP1208&oq=&gs_lcrp=EgZjaHJvbWUqCQgAEEUYOxjCAzIJCAAQRRg7GMIDMgkIARBFGDsYwgMyCQgCEEUYOxjCAzIJCAMQRRg7GMIDMgkIBBBFGDsYwgMyCQgFEEUYOxjCAzIJCAYQRRg7GMIDMgkIBxBFGDsYwgPSAQkxNjY4ajBqMTWoAgiwAgE&sourceid=chrome&ie=UTF-8'
    ]
  },
   {
    btn: '自作DIYスピーカー',
    label: '自作DIYスピーカー',
    urls: [
      'https://www.google.com/search?q=%E8%87%AA%E4%BD%9CDIY%E3%82%B9%E3%83%94%E3%83%BC%E3%82%AB%E3%83%BC&sca_esv=47b14df0f15d899c&biw=958&bih=854&sxsrf=ANbL-n5oGDt8pBNM9ecVTaXvPtVw1aULLQ%3A1780864747243&ei=69Ylat6_DtSKvr0PmbjoqQY&ved=0ahUKEwie4ZuV_vWUAxVUha8BHRkcOmUQ4dUDCBA&uact=5&oq=%E8%87%AA%E4%BD%9CDIY%E3%82%B9%E3%83%94%E3%83%BC%E3%82%AB%E3%83%BC&gs_lp=Egxnd3Mtd2l6LXNlcnAiGOiHquS9nERJWeOCueODlOODvOOCq-ODvDIFEAAY7wUyBRAAGO8FMgUQABjvBUisL1DdJljELHADeACQAQCYAXOgAZsCqgEDMi4xuAEDyAEA-AEBmAIGoAKtAsICCBAAGO8FGLADwgILEAAYgAQYogQYsAPCAgUQIRigAZgDAIgGAZAGBZIHAzUuMaAHggSyBwMyLjG4B6QCwgcDMC42yAcLgAgB&sclient=gws-wiz-serp'
    ]
  },
   
  {
    btn: 'KENRICK SOUND',
    label: 'KENRICK SOUND',
    urls: [
      'https://www.google.com/search?q=KENRICK+SOUND&sca_esv=47b14df0f15d899c&biw=958&bih=854&sxsrf=ANbL-n70NNOAL8bMbTVyP5Ww6AS8CksjrQ%3A1780864834571&ei=QtclavnGIpCNvr0P8di1mQM&ved=0ahUKEwj57-2-_vWUAxWQhq8BHXFsLTMQ4dUDCBA&uact=5&oq=KENRICK+SOUND&gs_lp=Egxnd3Mtd2l6LXNlcnAiDUtFTlJJQ0sgU09VTkQyBRAAGIAEMgUQABiABDIFEAAYgAQyBRAAGIAEMgQQABgeMgQQABgeMgQQABgeMgQQABgeMgQQABgeMgQQABgeSOqTAlDj3QFY1I8CcAN4AZABAJgBYaABlQiqAQIxM7gBA8gBAPgBAZgCD6AC3wioAgDCAgoQABhHGNYEGLADwgINEAAYgAQYBBixAxiDAcICEBAAGIAEGIoFGAQYsQMYgwHCAgcQABiABBgEwgIKEAAYgAQYigUYQ8ICEBAAGIAEGIoFGEMYsQMYgwHCAg0QABiABBiKBRhDGLEDwgIKEAAYgAQYBBixA8ICDRAAGIAEGIoFGAQYsQOYAwHxBXqmoL9x97iAiAYBkAYKkgcEMTQuMaAH4CeyBwQxMi4xuAfUCMIHBjAuMS4xNMgHOYAIAQ&sclient=gws-wiz-serp'
    ]
  },
   {
    btn: 'D412EX',
    label: 'D412EX',
    urls: [
      'https://www.youtube.com/results?search_query=D412EX'
    ]
  },
  {
    btn: 'LINN　LP12',
    label: 'LINN　LP12',
    urls: [
      'https://www.youtube.com/results?search_query=LINN%E3%80%80LP12'
    ]
  },
  {
    btn: 'LINN',
    label: 'LINN',
    urls: [
      'https://linn.jp/'
    ]
  },
  {
    btn: 'UESGI　上杉 真空管アンプ',
    label: '上杉',
    urls: [
      'https://www.youtube.com/results?search_query=UESGI%E3%80%80%E4%B8%8A%E6%9D%89+%E7%9C%9F%E7%A9%BA%E7%AE%A1%E3%82%A2%E3%83%B3%E3%83%97'
    ]
  },
  {
    btn: 'UESGI　上杉 アンプ',
    label: '上杉',
    urls: [
      'https://www.youtube.com/results?search_query=UESGI%E3%80%80%E4%B8%8A%E6%9D%89+%E3%82%A2%E3%83%B3%E3%83%97'
    ]
  },
  {
    btn: 'UESGI　上杉　フォノアンプ',
    label: 'UESGI　上杉　フォノアンプ',
    urls: [
      'https://www.youtube.com/results?search_query=UESGI%E3%80%80%E4%B8%8A%E6%9D%89%E3%80%80%E3%83%95%E3%82%A9%E3%83%8E%E3%82%A2%E3%83%B3%E3%83%97'
    ]
  },
  {
    btn: 'Many people who purchase an Accuphase preamplifier or integrated amplifier seem to feel at first that its highly detailed sound represents the ultimate in audio quality. However, that impression is often strongest only in the beginning.When listening slowly and casually over a long period of time, some listeners may find the Accuphase sound easier to tire of. In contrast, this video discusses how a system consisting of a UESGI preamplifier, a Luxman power amplifier, and a UESGI phono amplifier can remain enjoyable for years, providing a sound that listeners never seem to grow tired of and always want to keep listening to.',
    label: '上杉 真空管アンプ　UESGI　フォノアンプ',
    urls: [
      'https://www.youtube.com/watch?v=lubUnMAsWiA',
      'https://www.youtube.com/watch?v=mXhvklqxcbM',
      'https://www.youtube.com/watch?v=t8WN8EBs3tQ'
    ]
  },
  {
    btn: 'Accuphaseのプリアンプやプリメインアンプを購入して最初に聴いた感想は、解像度の高い音で音質が最高と勘違いする方が多い様です。最初だけです。ゆっくりゆったりと時間を掛けて聴いているとAccuphaseの音は飽きやすく、代わりにUESGIのプリアンプとLUXMANのパワーアンプとUESGIのフォノアンプを長期間保有して聞いていると飽きないでいつまでも聴いていたいと思わせてくれる動画。',
    label: '上杉 真空管アンプ　UESGI　フォノアンプ',
    urls: [
      'https://www.youtube.com/watch?v=lubUnMAsWiA',
      'https://www.youtube.com/watch?v=mXhvklqxcbM',
      'https://www.youtube.com/watch?v=t8WN8EBs3tQ'
    ]
  },
   {
    btn: 'UESGI（上杉研究所）2',
    label: 'UESGI（上杉研究所）2',
    urls: [
      'http://www.uesugilab.co.jp/'
    ]
  },
  {
    btn: 'Accuphase',
    label: 'Accuphase',
    urls: [
      'https://www.accuphase.co.jp/'
    ]
  },
  {
    btn: 'LUXMAN',
    label: 'LUXMAN',
    urls: [
      'https://www.luxman.co.jp/'
    ]
  },
  {
    btn: 'YAMAHA',
    label: 'YAMAHA',
    urls: [
      'https://jp.yamaha.com/index.html'
    ]
  },
  
  {
    btn: 'FOSTEX',
    label: 'FOSTEX',
    urls: [
      'https://www.fostex.jp/'
    ]
  },
  


  
 
   
  {
    btn: '政府の犯罪の告発者を情報統制する法案可決？',
    label: '情報統制法？',
    urls: [
      'https://www.youtube.com/results?search_query=%E5%9B%BD%E5%AE%B6%E6%83%85%E5%A0%B1%E5%B1%80%E8%A8%AD%E7%BD%AE%E6%B3%95%E6%A1%88%E3%80%80%E4%BA%BA%E6%A8%A9%E7%84%A1%E8%A6%96%E3%80%80%E9%81%95%E6%B3%95'
    ]
  },
  
 
{
    btn: '自民',
    label: '国際的に、戦争屋の一層で自民党も解体',
    urls: [
      'https://www.youtube.com/watch?v=0OGm-P-1wpg'
    ]
  },
  
  {
    btn: '農林中金100兆円を朝鮮韓国日本政府が泥棒！',
    label: '農林中金　100兆円　政府の犯罪',
    urls: [
      'https://www.youtube.com/results?search_query=%E8%BE%B2%E6%9E%97%E4%B8%AD%E9%87%91%E3%80%80100%E5%85%86%E5%86%86%E3%80%80%E6%94%BF%E5%BA%9C%E3%81%8C%E6%B3%A5%E6%A3%92'
    ]
  },
  {
    btn: '岸田と石破',
    label: '#平井宏治 #バイデン',
    urls: [
      'https://www.youtube.com/embed/1WGNmeQqYVs'
    ]
  },
  
  {
    btn: 'デジタル政府化の公務員リストラで財政健全化で少子高齢化予算確保',
    label: 'デジタル政府化',
    urls: [
      'https://www.youtube.com/results?search_query=%E6%94%BF%E5%BA%9C%E3%82%84%E3%81%8A%E5%BD%B9%E6%89%80%E3%81%AE%E3%83%87%E3%82%B8%E3%82%BF%E3%83%AB%E3%82%AC%E3%83%90%E3%83%A1%E3%83%B3%E3%83%88%E5%8C%96%E3%81%8C%E9%87%8D%E8%A6%81'
    ]
  },
  
  {
    btn: '歌心りえ',
    label: 'RIE UTAGOKORO',
    urls: [
      'https://www.youtube.com/watch?v=O1fY5NJlwCY&list=RDO1fY5NJlwCY&start_radio=1',
      'https://www.youtube.com/watch?v=vPGm2q79px4&list=RDvPGm2q79px4&start_radio=1',
      'https://www.youtube.com/watch?v=iC1Y-1pwbm0&list=RDiC1Y-1pwbm0&start_radio=1',
      'https://www.youtube.com/watch?v=qVymStzDEjU&list=RDqVymStzDEjU&start_radio=1',
      'https://www.youtube.com/watch?v=3DrVc07KdyQ&list=RD3DrVc07KdyQ&start_radio=1',
      'https://www.youtube.com/watch?v=u2-aAlOPzIY&list=RDu2-aAlOPzIY&start_radio=1',
      'https://www.youtube.com/watch?v=vVzQVcnib1w&list=RDvVzQVcnib1w&start_radio=1',
      'https://www.youtube.com/watch?v=6lrejTJEWWY&list=RD6lrejTJEWWY&start_radio=1',
      'https://www.youtube.com/results?search_query=%E6%AD%8C%E5%BF%83%E3%82%8A%E3%81%88'
    ]
  },

  {
    btn: '高橋洋子',
    label: 'YOUKO TAKAHASHI',
    urls: [
      'https://www.youtube.com/watch?v=MwcmUj09zGw'
    ]
  },
  
  {
    btn: '鈴木瑛美子',
    label: '鈴木瑛美子',
    urls: [
      'https://www.youtube.com/results?search_query=%E9%88%B4%E6%9C%A8%E7%91%9B%E7%BE%8E%E5%AD%90'
    ]
  },
  {
    btn: 'MISIA',
    label: 'MISIA',
    urls: [
      'https://www.youtube.com/watch?v=4gv6Q_xg8_w&list=RD4gv6Q_xg8_w&start_radio=1',
      'https://www.youtube.com/watch?v=Iintu_NTwk8&list=RDIintu_NTwk8&start_radio=1',
      'https://www.youtube.com/watch?v=4pD9K_14TEE&list=RD4pD9K_14TEE&start_radio=1',
      'https://www.youtube.com/watch?v=ZtraHjuuDPI&list=RDZtraHjuuDPI&start_radio=1',
      'https://www.youtube.com/watch?v=5Wb49KmCHBQ&list=PLwUezF_R28ItgRJql3emMv5O9m95a3QT5&index=12',
      'https://www.youtube.com/watch?v=efqBnrR1RjU&list=RDefqBnrR1RjU&start_radio=1',
      'https://www.youtube.com/results?search_query=MISIA'
    ]
  },
  {
    btn: '玉置浩二　デュエット　　　　　　　　',
    label: '玉置浩二',
    urls: [
       'https://www.youtube.com/results?search_query=%E7%8E%89%E7%BD%AE%E6%B5%A9%E4%BA%8C+%E3%83%87%E3%83%A5%E3%82%A8%E3%83%83%E3%83%88'
    ]
  },  
  {
    btn: '玉置浩二',
    label: '玉置浩二',
    urls: [
      'https://www.youtube.com/watch?v=VcRrGSzBWiE&list=RDVcRrGSzBWiE&start_radio=1',
      'https://www.youtube.com/watch?v=zH5PH8ZfUsM&list=RDzH5PH8ZfUsM&start_radio=1',
      'https://www.youtube.com/watch?v=dl9zXiUPulE&list=RDdl9zXiUPulE&start_radio=1',
      'https://www.youtube.com/watch?v=e8nu6LpC8ts&list=RDe8nu6LpC8ts&start_radio=1',
      'https://www.youtube.com/watch?v=BRGv9hN6XD4&list=PLohn0U8RfxxS-GIgWz-pf29bQqLSrwzBn',
      'https://www.youtube.com/watch?v=-JQ2TtHtYLc&list=PLohn0U8RfxxS-GIgWz-pf29bQqLSrwzBn&index=2',
      'https://www.youtube.com/results?search_query=%E7%8E%89%E7%BD%AE%E6%B5%A9%E4%BA%8C'
    ]
  },     

  {
    btn: '三田りょう',
    label: '三田りょう',
    urls: [
      'https://www.youtube.com/results?search_query=%E4%B8%89%E7%94%B0%E3%82%8A%E3%82%87%E3%81%86'
      
    ]
  },
  
  
  
 
  
  {
    
    btn: '日本',
    label: '日本食　観光名所　温泉',
    urls: [
      'https://www.youtube.com/results?search_query=%E6%97%A5%E6%9C%AC%E9%A3%9F%E3%80%80%E8%A6%B3%E5%85%89%E5%90%8D%E6%89%80%E3%80%80%E6%B8%A9%E6%B3%89'
   ]
  }, 
  {
    btn: '風呂　　　　　　　　　　　　　　',
    label: 'お風呂',
    urls: [
      'https://www.youtube.com/watch?v=ai04y5vZGL4',
      'https://www.youtube.com/watch?v=AphKpwtjOZ0',
      'https://www.youtube.com/results?search_query=%E6%B8%A9%E6%B3%89%E6%97%85%E8%A1%8C'
   ]
  }, 

  {
    btn: 'サウナ',
    label: 'サウナ',
    urls: [

      'https://www.youtube.com/watch?v=MJU5DYqACtE',
      'https://www.youtube.com/shorts/W4xT-JO23MY',
      'https://www.youtube.com/watch?v=BibwxWZzgrA',
      'https://www.youtube.com/watch?v=URM9kU1x_q0',
      'https://www.youtube.com/watch?v=_PxhV6s8Wso',
      'https://www.youtube.com/results?search_query=%E3%82%B5%E3%82%A6%E3%83%8A+%E6%AD%A3%E3%81%97%E3%81%84%E5%85%A5%E3%82%8A%E6%96%B9'
      ]
  },
   
  
  
    
  
  {
    btn: 'YHWH',
    label: 'NIHON のIHONをヘブライ語にするとYHWHの古代語に？',
    urls: [
      'https://www.youtube.com/watch?v=5MFwZbfpKEk',
      'https://www.youtube.com/watch?v=9MUH2gowaK8',
      'https://www.youtube.com/watch?v=VDk2TK5EI9A',
      'https://www.youtube.com/shorts/sphnufU1ElA',
      'https://www.youtube.com/shorts/90wBeIBjueg',
      'https://www.youtube.com/watch?v=vgwUuyPf6h0',
      'https://www.youtube.com/watch?v=Fv0rZSYd9Yk',
      'https://www.youtube.com/shorts/oh4wO9WuKe0',
      'https://www.youtube.com/results?search_query=%E7%A5%9E%E7%A4%BE%E4%BB%8F%E9%96%A3%E5%B7%A1%E3%82%8A'
    ]
  },
  {
    btn: '声',
    label: '',
    urls: [
      'https://www.youtube.com/watch?v=nD62Ojw8E24',
      'https://www.youtube.com/watch?v=fbRiD46xkh8',
      'https://www.youtube.com/watch?v=JbB6w-vflI0',
      'https://www.youtube.com/watch?v=NmlQF7qnOJA',
      'https://www.youtube.com/watch?v=FD67FQrlHGc',
      'https://www.youtube.com/results?search_query=%E3%83%9C%E3%82%A4%E3%82%B9%E3%83%88%E3%83%AC%E3%83%BC%E3%83%8B%E3%83%B3%E3%82%B0%E3%80%80%E3%83%93%E3%83%96%E3%83%A9%E3%83%BC%E3%83%88%E3%80%80%E3%83%96%E3%83%AC%E3%82%B9%E3%82%A2%E3%82%A6%E3%83%88%E3%80%80%E3%82%B0%E3%83%AB%E3%83%BC%E3%83%B4%E6%84%9F%E3%80%80%E5%80%8D%E9%9F%B3'
    ]
  },
  {
    btn: '足技　　　　　　　　',
    label: '',
    urls: [
      'https://www.youtube.com/shorts/83VVfYhs2lI',
      'https://www.youtube.com/watch?v=uhWIGPrKX5k',
      'https://www.youtube.com/shorts/vAccGJUiOZc',
      'https://www.youtube.com/watch?v=45UZYG5SPuE&list=RD45UZYG5SPuE&start_radio=1',
      'https://www.youtube.com/results?search_query=%E6%97%A5%E6%9C%AC%E8%AA%9E%E3%80%80%E8%8B%B1%E8%AA%9E%E3%80%80%E5%88%9D%E5%BF%83%E8%80%85%E3%80%80%E5%88%9D%E3%82%81%E3%81%A6%E3%80%80%E3%83%A0%E3%83%BC%E3%83%B3%E3%82%A6%E3%82%A9%E3%83%BC%E3%82%AF%E3%80%80%E4%B8%96%E7%95%8C',
    
    ]
  },
  {
    btn: 'KAZUMA',
    label: '',
    urls: [
      'https://www.youtube.com/watch?v=aNCrV-NiilA',
      'https://www.youtube.com/watch?v=_iLz63jln0I',
      'https://www.youtube.com/shorts/pcXfwcsIAQQ',
      'https://www.youtube.com/shorts/DmPrK_gxfZo',
      'https://www.youtube.com/shorts/kivNEY8ITJ4',
      'https://www.youtube.com/shorts/GRmJdVpi5wE',
      'https://www.youtube.com/results?search_query=%E3%82%AB%E3%82%BA%E3%83%9E+%E5%A4%9A%E8%A8%80%E8%AA%9E'
      
    ]
  },
  
];
// =====================================================================
// ★ デフォルト再生シリーズ番号（0始まり）: 0 = ナタリー・ポートマン
var DEFAULT_SERIES_INDEX = 0;
var currentSeriesIndex   = DEFAULT_SERIES_INDEX;
var currentSeriesUrls    = SEARCH_SERIES[DEFAULT_SERIES_INDEX].urls;
var currentUrlIndex      = 0;
var searchResultIds      = [];
var searchResultPage     = 0;
var currentPlayingUrl    = '';   // 現在再生中のURL（Facebook/GDrive追跡用）
var currentPlayingType   = '';   // 'youtube' | 'facebook' | 'gdrive'
// ★ [v20] 現在再生中の動画の「ストックURL由来の再生開始秒数」とその対象動画ID。
//   ストックURLに時間指定(t=...)があれば該当秒数、無ければ 0。
//   NOW PLAYING のタイトル/URLクリック時、YouTube遷移先の ?t= 付与判定に使う。
//   （v16の「現在の背景再生位置から続きを見る」仕様の代替。再生位置は使わない）
var currentStockStartSeconds = 0;
var currentStockVideoId      = '';
var fbQueueUrls          = [];   // Facebook再生キュー（上から順再生用）
var fbQueuePage          = 0;    // Facebook再生キューの現在位置
// ★ Google検索結果/ホームページ等の非再生URLを「画面遷移せずSKIPして次の動画へ」
//   進める際の無限ループ防止カウンタ。実際に動画が再生開始したら0にリセットする。
var __skipChainCount     = 0;
var __SKIP_CHAIN_MAX     = 40;
var DEFAULT_BG_VIDEO_ID  = 'mSDVnO5gFYk';  // ★ フォールバック専用 (再生候補がすべて失敗した場合のみ使用)
var SAFE_BG_VIDEO_IDS    = ['mSDVnO5gFYk', 'Ywhs17jhxec', '5MFwZbfpKEk', 'O2IMOiSCLi8'];
var safeBgRotate         = 0;
var ytBgPlayer           = null;
var adCheckTimer         = null;
var ytBgFallbackUsed     = false;
var currentSearchQuery   = '';
var ytControlsBound      = false;
var ytSearchFetchToken   = 0;
var YT_SAMPLE_VIDEO_IDS  = { "M7lc1UVf-VE": 1, "jNQXAC9IVRw": 1 };
var ytBgLoop             = false;
var ytCtxMenuBound       = false;
var ytBgLastToggleMs     = 0;
var ytCardNavBound       = false;
var ytHandoffStartPaused = false;
var __ytInitialPause     = false;  // ★ [v28] 初回起動時の最初の1本だけ頭出し一時停止
var ytRandomPool         = [];
var ytPlayedPool         = [];
var ytRandomSearchPool   = [];
var ytPlayedSearchPool   = [];
var __iframeMode         = false;
var __nowPlayingToken    = 0;
// ★ [v26] 検索結果URL「ランダム2本再生→次へ遷移」制御
var __srqIds       = [];   // 検索結果から選んだ2本のIDリスト
var __srqPlayCount = 0;    // 消化済み本数 (0→1本目再生中, 1→2本目再生中, 2→完了)
var __srqOnDone    = null; // 2本消化後に呼ぶコールバック function()
// ★ [v32] 検索結果の動画が「検索ワードと無関係でないか」を再生開始後にチェックし、
//   無関係なら最新の検索結果で一度だけ再検索する機能の状態
var __searchQuery        = '';     // 現在再生中の検索動画の検索ワード(キーワード)
var __searchRecheckUsed  = false;  // この検索につき再検索を既に1回使ったか
var __searchRelevTimer   = null;   // 関連性チェック用タイマー
// 背景で再生するフラグ。「背景で再生」チェックボックスは廃止したため常に背景再生(true)で固定。
var ytBgPlayInBackground = true;
// (旧 __krs4351FirstPlayed フラグは廃止: 起動時は SEARCH_SERIES[0].urls[0] を再生)
// ★ unifiedプールが「現シリーズ消化中」か「全シリーズ横断中」かを示すフェーズ
//    0 = 現シリーズだけのシャッフル消化中
//    1 = 現シリーズ消化済み → 全シリーズ横断シャッフル
var __unifiedPoolPhase   = 0;

// ============================================================================
// ★★★ [v33] 自動切り替えタイマー機能 ★★★
//   再生中の動画(ホームページ紹介含むシリーズ全般)が始まってから60秒後に
//   自動的に skipToNext() を呼び、次の動画/URLへ切り替える。
//   - 新しい動画の再生開始ごとに startAutoSwitchTimer() を呼んでリセット
//   - ユーザーが BACK/NEXT/シリーズボタン等を押した場合も、その操作で
//     新しい再生が始まるため自然にタイマーがリセットされる
//   - STOPボタン押下時は cancelAutoSwitchTimer() でタイマー停止
// ============================================================================
var AUTO_SWITCH_SECONDS = 60;          // 自動切り替えまでの秒数
var __autoSwitchEnabled  = true;       // 自動切り替え機能の有効フラグ
var __autoSwitchTimer    = null;       // setInterval の ID
var __autoSwitchLeft     = AUTO_SWITCH_SECONDS; // 残り秒数

function cancelAutoSwitchTimer() {
  if (__autoSwitchTimer !== null) {
    clearInterval(__autoSwitchTimer);
    __autoSwitchTimer = null;
  }
  var box = document.getElementById('introTimerBox');
  var bar = document.getElementById('introTimerBar');
  if (box) box.style.display = 'none';
  if (bar) bar.style.display = 'none';
}

function updateAutoSwitchDisplay() {
  var box = document.getElementById('introTimerBox');
  var bar = document.getElementById('introTimerBar');
  var val = document.getElementById('introTimerValue');
  if (!box || !bar || !val) return;
  if (!__autoSwitchEnabled) { box.style.display = 'none'; bar.style.display = 'none'; return; }
  box.style.display = 'block';
  bar.style.display = 'block';
  val.textContent = __autoSwitchLeft;
  bar.style.transform = 'scaleX(' + Math.max(0, __autoSwitchLeft / AUTO_SWITCH_SECONDS) + ')';
}

// 新しい動画/ページの再生が始まるたびに呼ぶ: タイマーを60秒からリスタート
function startAutoSwitchTimer() {
  cancelAutoSwitchTimer();
  if (!__autoSwitchEnabled) return;
  __autoSwitchLeft = AUTO_SWITCH_SECONDS;
  updateAutoSwitchDisplay();
  __autoSwitchTimer = setInterval(function() {
    __autoSwitchLeft--;
    if (__autoSwitchLeft <= 0) {
      cancelAutoSwitchTimer();
      // ★ 60秒経過 → 次の動画/URLへ自動切り替え
      try { skipToNext(); } catch (e) {}
      return;
    }
    updateAutoSwitchDisplay();
  }, 1000);
}

// 自動切り替えのON/OFFを切り替える(任意のボタンから呼び出し可能)
function toggleAutoSwitch() {
  __autoSwitchEnabled = !__autoSwitchEnabled;
  if (__autoSwitchEnabled) {
    startAutoSwitchTimer();
  } else {
    cancelAutoSwitchTimer();
  }
  return __autoSwitchEnabled;
}

// ★ [v34-BUG3] fetchAndCollect が navigateUrl を返した場合(ホームページ等の
//   非再生URL)にブラウザを直接遷移させる共通ヘルパー。
//   背景再生中に呼ばれる可能性もあるため、自動切り替えタイマーは止めておく。
function navigateToNonPlayableUrl(navigateUrl) {
  cancelAutoSwitchTimer();
  try { sessionStorage.setItem('__ytReturnMode', '1'); } catch (e) {}
  window.location.assign(navigateUrl);
}

// ★ YouTube操作パネル(ytBgVolumeWrap)がCLOSE中かどうかを判定する。
//   CLOSE時は volWrap に inline display:none が設定される(CLOSEボタン処理)。
//   CSS側で隠れているケースにも備えて computed style もフォールバックで確認する。
function isYtPanelClosed() {
  var volWrap = document.getElementById('ytBgVolumeWrap');
  if (!volWrap) return false;
  if (volWrap.style && volWrap.style.display === 'none') return true;
  try {
    if (window.getComputedStyle && getComputedStyle(volWrap).display === 'none') return true;
  } catch (e) {}
  return false;
}


function shuffleArray(arr) {
  var a = arr.slice();
  for (var i = a.length - 1; i > 0; i--) {
    var j = Math.floor(Math.random() * (i + 1));
    var t = a[i];
    a[i] = a[j];
    a[j] = t;
  }
  return a;
}

function resetRandomPools() {
  var urls = getValidUrls();
  ytRandomPool = shuffleArray(urls);
  ytPlayedPool = [];
}

function getNextRandomUrl() {
  if (!ytRandomPool.length) {
    ytRandomPool = shuffleArray(ytPlayedPool);
    ytPlayedPool = [];
  }
  if (!ytRandomPool.length) return null;
  var next = ytRandomPool.shift();
  ytPlayedPool.push(next);
  return next;
}

function prepareRandomSearchPool(ids) {
  ytRandomSearchPool = shuffleArray((ids || []).slice());
  ytPlayedSearchPool = [];
}

function getNextRandomSearchId() {
  if (!ytRandomSearchPool.length) {
    ytRandomSearchPool = shuffleArray(ytPlayedSearchPool);
    ytPlayedSearchPool = [];
  }
  if (!ytRandomSearchPool.length) return null;
  var next = ytRandomSearchPool.shift();
  ytPlayedSearchPool.push(next);
  return next;
}


function getCurrentYtBgVideoId() {
  try {
    if (ytBgPlayer && ytBgPlayer.getVideoData) {
      var vd = ytBgPlayer.getVideoData();
      if (vd && vd.video_id && /^[a-zA-Z0-9_-]{11}$/.test(vd.video_id)) return vd.video_id;
    }
  } catch (e) {}
  return null;
}

// ★ [v16] 現在の再生位置(秒)を取得 — ytBgPlayer が存在しない/iframeモードは 0 を返す
function getCurrentPlaybackSeconds() {
  try {
    if (ytBgPlayer && ytBgPlayer.getCurrentTime && !__iframeMode) {
      var t = ytBgPlayer.getCurrentTime();
      if (typeof t === 'number' && t > 1) return Math.floor(t);
    }
  } catch (e) {}
  return 0;
}

// ★ [v16] YouTube URL に ?t=秒 を付与して返す
//   既存の watch URL / youtu.be URL に対応。0秒の場合はそのまま返す。
function buildYtUrlWithTime(url, seconds) {
  if (!url || !seconds || seconds < 1) return url;
  try {
    var u = new URL(url);
    var host = u.hostname.replace(/^www\./, '');
    if (host === 'youtube.com' || host === 'youtu.be') {
      u.searchParams.set('t', String(seconds));
      return u.toString();
    }
  } catch (e) {}
  return url;
}

// ★ [v16] 現在再生中のYouTubeのURLを、再生位置付きで取得する
function getCurrentYtUrlWithTime() {
  try {
    var vid = getCurrentYtBgVideoId();
    if (!vid) return null;
    var sec = getCurrentPlaybackSeconds();
    var base = 'https://www.youtube.com/watch?v=' + vid;
    return buildYtUrlWithTime(base, sec);
  } catch (e) {}
  return null;
}

// ★ [v20] NOW PLAYING のタイトル/URLクリック時に開くYouTube遷移先URLを決定する。
//   仕様（v16の「背景の現在再生位置から続きを見る」を廃止し、以下に置換）:
//     ・ストックURL(元URL)に時間指定(t=...)があれば → その時間を保持して開く
//     ・時間指定がもともと無ければ            → 動画の最初(頭出し)から開く
//   ※ 再生途中であっても「現在の再生位置」は一切使わない。
function buildNowPlayingNavUrl() {
  // iframeモード(Shorts/Facebook/GDrive等)は元URLへそのまま遷移
  if (__iframeMode) {
    return (typeof currentPlayingUrl !== 'undefined' && currentPlayingUrl) ? currentPlayingUrl : null;
  }
  // 現在実際に再生中の動画IDを取得
  var vid = null;
  try { vid = getCurrentYtBgVideoId(); } catch (e) {}
  if (!vid && typeof currentPlayingUrl !== 'undefined' && currentPlayingUrl) {
    vid = extractYouTubeId(currentPlayingUrl);
  }
  if (!vid) {
    // YouTube動画IDが取れない場合は元URLへフォールバック
    return (typeof currentPlayingUrl !== 'undefined' && currentPlayingUrl) ? currentPlayingUrl : null;
  }
  var base = 'https://www.youtube.com/watch?v=' + vid;
  // ストックURLの時間指定は「その時ロードした動画」に対してのみ適用。
  // (playlist等で自動的に別動画へ進んだ場合は時間指定を引き継がず頭出し)
  if (vid === currentStockVideoId && currentStockStartSeconds > 0) {
    return base + '&t=' + currentStockStartSeconds + 's';
  }
  return base;
}

function buildYtHandoffHashString(vid, vol, loop, q, paused) {
  if (!vid || !/^[a-zA-Z0-9_-]{11}$/.test(vid)) return "";
  var v = vol | 0;
  if (v < 0) v = 0;
  if (v > 100) v = 100;
  var qs = q != null ? String(q) : "";
  return "ytbg=1&v=" + encodeURIComponent(vid) + "&vol=" + v + "&lp=" + (loop ? "1" : "0") + "&ps=" + (paused ? "1" : "0") + "&q=" + encodeURIComponent(qs);
}

function parseYtHandoffFromLocation() {
  var raw = window.location.hash;
  if (!raw || raw.length < 8) return null;
  var seg = raw.charAt(0) === "#" ? raw.slice(1) : raw;
  if (seg.indexOf("ytbg=1") === -1) return null;
  var params = {};
  seg.split("&").forEach(function(part) {
    var i = part.indexOf("=");
    if (i === -1) return;
    var k = decodeURIComponent(part.slice(0, i));
    var v = decodeURIComponent(part.slice(i + 1).replace(/\+/g, " "));
    params[k] = v;
  });
  if (params.ytbg !== "1" || !params.v) return null;
  if (!/^[a-zA-Z0-9_-]{11}$/.test(params.v)) return null;
  return params;
}

function consumeYtHandoffHash() {
  var params = parseYtHandoffFromLocation();
  if (!params) return null;
  try {
    if (window.history && window.history.replaceState) {
      var clean = window.location.pathname + window.location.search;
      window.history.replaceState(null, "", clean);
    }
  } catch (e) {}
  return params;
}

function applyYtHandoffParams(ho) {
  if (!ho || !ho.v || !/^[a-zA-Z0-9_-]{11}$/.test(ho.v)) return false;
  if (ho.q != null && ho.q !== "") {
    currentSearchQuery = ho.q;
    var si = document.getElementById("ytBgSearch");
    if (si) si.value = ho.q;
  }
  ytBgLoop = ho.lp !== "0";
  ytHandoffStartPaused = ho.ps === "1";
  var vel = document.getElementById("ytBgVolume");
  if (vel && ho.vol != null && ho.vol !== "") {
    var vv = parseInt(ho.vol, 10);
    if (!isNaN(vv) && vv >= 0 && vv <= 100) vel.value = String(vv);
  }
  updateYtTransportUi();
  return true;
}

function bindYtCardHandoffOnce() {
  if (ytCardNavBound) return;
  ytCardNavBound = true;
  document.addEventListener("click", function(ev) {
    var t = ev.target;
    if (!t || !t.closest) return;
    var a = t.closest(".card[data-lang-code]");
    if (!a) return;
    var hrefAttr = a.getAttribute("data-ac-translate-href");
    if (!hrefAttr) return;
    var vid = getCurrentYtBgVideoId();
    if (!vid) return;
    var vel = document.getElementById("ytBgVolume");
    var vol = vel ? (parseInt(vel.value, 10) || 0) : 0;
    syncQueryFromInput();
    var paused = false;
    try {
      if (ytBgPlayer && ytBgPlayer.getPlayerState && window.YT) {
        var st = ytBgPlayer.getPlayerState();
        paused = st === YT.PlayerState.PAUSED || st === YT.PlayerState.CUED;
      }
    } catch (e) {}
    var frag = buildYtHandoffHashString(vid, vol, ytBgLoop, currentSearchQuery, paused);
    if (!frag) return;
    try {
      var u = new URL(hrefAttr, window.location.href);
      // 既存hashに含まれる translateto= は破壊せずに保持してマージする
      var existingHash = (u.hash || "").replace(/^#/, "");
      var keepParts = [];
      if (existingHash) {
        var hashParts = existingHash.split("&");
        for (var hi = 0; hi < hashParts.length; hi++) {
          var hp = hashParts[hi];
          if (!hp) continue;
          // ytbg関連のパラメータは新しいfragに置き換わるため除外、それ以外(translateto等)は保持
          if (/^(ytbg|v|vol|lp|ps|q)=/.test(hp)) continue;
          keepParts.push(hp);
        }
      }
      var merged = keepParts.length ? (keepParts.join("&") + "&" + frag) : frag;
      u.hash = merged;
      a.setAttribute("data-ac-translate-href", u.toString());
    } catch (e) {
      try {
        // フォールバック: hashが取れない場合も既存のtranslateto=部分は残す
        var raw = hrefAttr || "";
        var hashIdx = raw.indexOf("#");
        var base = hashIdx === -1 ? raw : raw.slice(0, hashIdx);
        var oldHash = hashIdx === -1 ? "" : raw.slice(hashIdx + 1);
        var keep2 = [];
        if (oldHash) {
          var p2 = oldHash.split("&");
          for (var pi = 0; pi < p2.length; pi++) {
            if (!p2[pi]) continue;
            if (/^(ytbg|v|vol|lp|ps|q)=/.test(p2[pi])) continue;
            keep2.push(p2[pi]);
          }
        }
        a.setAttribute("data-ac-translate-href", base + "#" + (keep2.length ? keep2.join("&") + "&" + frag : frag));
      } catch (e2) {}
    }
  }, true);
}

function syncQueryFromInput() {
  var inp = document.getElementById("ytBgSearch");
  var q = inp ? String(inp.value || "").trim() : "";
  if (!q && currentSeriesUrls && currentSeriesUrls.length) q = currentSeriesUrls[0];
  currentSearchQuery = q;
}

function syncVolumeFromSlider(retriesLeft) {
  var el = document.getElementById("ytBgVolume");
  var p = ytBgPlayer;
  if (!el) return;
  if (!p || typeof p.setVolume !== 'function') {
    // ★ [v34-BUG1] 起動直後の最初の動画は YT.Player インスタンスが
    //   生成されてもAPIメソッドがまだ使えない瞬間があり、setVolume/unMute が
    //   無反応(無視)になって「音量調整が効かない」状態のまま固まっていた。
    //   → プレイヤーが使えるようになるまで少し待って再試行する。
    if (retriesLeft === undefined) retriesLeft = 10; // 約3秒間リトライ
    if (retriesLeft > 0) setTimeout(function() { syncVolumeFromSlider(retriesLeft - 1); }, 300);
    return;
  }
  var v = parseInt(el.value, 10) || 0;
  try {
    // ★ [v36-BUG1] ゲートを __wantAudio(音を出したい意思)で判定する。
    //   意思が無い間(=最初の動画・まだ誰も触っていない)はミュート維持。
    //   v35は __audioUnlocked(確定)で判定していたが、起動直後にスライダーを触ると
    //   確定フラグだけ先に立って実際はミュートのまま固まる不具合があった。
    if (!__wantAudio) {
      p.setVolume(v > 0 ? v : 50);
      p.mute();
      return;
    }
    if (v > 0) {
      p.unMute();
      p.setVolume(v);
      __audioUnlocked = true; // ★ ここまで来たら確実にアンミュート成立
    } else {
      p.setVolume(0);
      p.mute();
    }
  } catch (e) {
    // ★ プレイヤーがまだ内部初期化中で例外が出た場合もリトライする
    if (retriesLeft === undefined) retriesLeft = 10;
    if (retriesLeft > 0) setTimeout(function() { syncVolumeFromSlider(retriesLeft - 1); }, 300);
  }
}

// ============================================================================
// ★★★ [v35-BUG1] 最初の動画の音量が効かないBUGの本修正 ★★★
//   原因: 起動時の最初の YT.Player は autoplay=1 で生成されるが、
//         ユーザー操作(ジェスチャ)が無い状態ではブラウザの自動再生ポリシーにより
//         「ミュート状態でのみ自動再生」が許可される。YouTubeプレイヤーはこの制約下で
//         自動的にミュートして再生を開始するため、API経由の unMute()/setVolume() を
//         呼んでも音が戻らない(=「最初の動画だけ音量が効かない」)状態になっていた。
//   修正: 最初の1回のユーザー操作(クリック/タップ/キー入力)を全画面で捕捉し、
//         そのジェスチャ内で unMute()+setVolume() を実行して音声をアンロックする。
//         これによりブラウザがその後の音声再生を許可する。
// ============================================================================
// ★ [v36-BUG1] フラグを2つに分離する。
//   __wantAudio    : ユーザーが「音を出したい」意思表示をしたか(ジェスチャ発生)。
//   __audioUnlocked: 実際に unMute()/iframe差し替えが成立したか(確定)。
//   v35は両者を1つの __audioUnlocked で兼用していたため、プレイヤーAPIが
//   まだ使えない起動直後にスライダーを触ると、applyUnmuteFromSlider() が失敗(false)
//   なのに __audioUnlocked だけ true になり「アンロック済みのつもりで実際はミュート」
//   状態に固まっていた(=最初の動画だけ音量が効かない)。
//   → 意思(__wantAudio)が立ったら、プレイヤーが ready 次第アンミュートを再適用する。
//     一度でもユーザー操作があればブラウザの sticky activation により遅延 unMute も
//     許可されるため、これで最初の動画にも確実に音が戻る。
var __audioUnlocked = false;
var __wantAudio = false;

function applyUnmuteFromSlider() {
  __wantAudio = true; // 呼ばれた時点でユーザーは音を出したい(必ずジェスチャ内から呼ぶ)
  var el = document.getElementById("ytBgVolume");
  var v = el ? (parseInt(el.value, 10) || 0) : 0;
  var p = ytBgPlayer;
  // YT.Player(API)経由
  if (p && typeof p.unMute === 'function') {
    try {
      // ★ スライダー値が0(OFF)のときは強制的に音を出さずミュート維持。
      //   v>0 のときだけアンミュートし、その値で再生する。
      if (v > 0) {
        p.unMute();
        p.setVolume(v);
        __audioUnlocked = true; // ★ 実際にアンミュート成立したときだけ確定
      } else {
        p.setVolume(0);
        p.mute();
      }
      return true;
    } catch (e) { /* fallthrough to iframe */ }
  }
  // iframe(Shorts/Facebook/GDrive)経由: mute=1 で読み込んだ場合は
  // mute無しのsrcに差し替えて音声を復帰させる(ユーザージェスチャ内なので許可される)
  //   ★ スライダーが0(OFF)のときはミュートのまま維持する。
  var iframe = document.getElementById('yt-bg-embed-iframe');
  if (v > 0 && iframe && iframe.src && iframe.src.indexOf('mute=1') !== -1) {
    try {
      iframe.src = iframe.src.replace(/([?&])mute=1/i, '$1mute=0');
      __audioUnlocked = true;
      return true;
    } catch (e2) {}
  }
  // ★ ここに来た = まだ成立せず(プレイヤー未ready等)。__audioUnlocked は立てない。
  //   意思(__wantAudio)は立っているので、ready後に syncVolumeFromSlider() が再適用する。
  return false;
}

// 最初のユーザージェスチャで音声をアンロックする(一度だけ)
function bindAudioUnlockOnce() {
  if (window.__audioUnlockBound) return;
  window.__audioUnlockBound = true;
  function unlock() {
    // ★ [v36-BUG1] ジェスチャが来た時点で必ず「音を出したい意思」を記録する。
    //   この時点でプレイヤーAPIが未readyでも、ready後に syncVolumeFromSlider() が
    //   __wantAudio を見てアンミュートを再適用する(sticky activationで遅延unMute可)。
    __wantAudio = true;
    if (__audioUnlocked) { detach(); return; }
    // 今この場(ジェスチャ内)でも一度試す。成立すれば確定&解除。
    applyUnmuteFromSlider();           // 成功時に __audioUnlocked を立てる
    syncVolumeFromSlider();            // 未readyでもリトライで後追い適用
    if (__audioUnlocked) {
      detach();
    }
    // ★ 未成立(まだ再生前/未ready)の場合は detach せず、次のジェスチャ・ready後に再適用。
  }
  function detach() {
    document.removeEventListener('pointerdown', unlock, true);
    document.removeEventListener('click', unlock, true);
    document.removeEventListener('touchstart', unlock, true);
    document.removeEventListener('keydown', unlock, true);
  }
  document.addEventListener('pointerdown', unlock, true);
  document.addEventListener('click', unlock, true);
  document.addEventListener('touchstart', unlock, true);
  document.addEventListener('keydown', unlock, true);
}

function updateYtTransportUi() {
  var pauseBtn = document.getElementById("ytBgPauseToggle");
  var playBtn  = document.getElementById("ytBgPlayBtn");
  var stopBtn  = document.getElementById("ytBgStopBtn");
  var statusLabel = document.getElementById("ytBgStatusLabel");
  var loopBtn  = document.getElementById("ytBgLoopToggle");
  var p = ytBgPlayer;
  var YT = window.YT;
  var playing = false;
  if (YT && p && p.getPlayerState) {
    try {
      var st = p.getPlayerState();
      playing = st === YT.PlayerState.PLAYING || st === YT.PlayerState.BUFFERING;
    } catch(e) {}
  } else {
    // ★ [v11-BUG1] iframe再生中の状態判定
    var iframe = document.getElementById('yt-bg-embed-iframe');
    if (iframe) playing = !!(iframe.src && iframe.src !== 'about:blank');
  }
  if (playBtn) playBtn.setAttribute("aria-pressed", playing ? "true" : "false");
  if (stopBtn) stopBtn.setAttribute("aria-pressed", playing ? "false" : "true");
  if (statusLabel) statusLabel.textContent = playing ? "Play Now" : "Stop Now";
  if (loopBtn) {
    loopBtn.textContent = ytBgLoop ? "Loop ON" : "Loop OFF";
    loopBtn.setAttribute("aria-pressed", ytBgLoop ? "true" : "false");
  }
}

function destroyYtBgPlayer() {
  ytSearchFetchToken++;
  __iframeMode = false;
  __nowPlayingToken++;
  try {
    if (ytBgPlayer && ytBgPlayer.destroy) ytBgPlayer.destroy();
  } catch (e) {}
  ytBgPlayer = null;
}

// ------------------------------------------------------------------
// URL解決エンジン + スキップエンジン
// ------------------------------------------------------------------

function isYouTubeUrl(url) {
  return /(?:(?:www\.|m\.)?youtube\.com|(?:m\.)?youtu\.be)/i.test(url);
}
function isDigitalConcertHallUrl(url) {
  return /(^https?:\/\/)?(?:www\.)?digitalconcerthall\.com\//i.test(String(url || ''));
}
// ★ [v13-BUG5] 「再生可能な動画URL」判定
//   YouTube / Facebook / Google Drive / Vimeo / niconico / TikTok のいずれか。
//   これ以外（ショップ等のホームページ・www.google.com/search 等）は
//   ページ内の無関係な動画を拾って再生しないよう「非再生URL(=リンク)」扱いにする。
// ★ [v15-BUG6] youtube.com/results?search_query=... (検索結果ページ) は
//   動画IDを持たないため jina.ai でスクレイプしても再生不能になるBUGを修正。
//   /results を含む YouTube URL は「非再生URL(=リンク)」扱いにし、
//   switchSeries の window.location.assign() でブラウザ直接遷移させる。
function isPlayableVideoUrl(url) {
  if (!url) return false;
  // youtube.com/results (検索結果ページ) は再生不可として除外
  if (/(?:www\.|m\.)?youtube\.com\/results/i.test(url)) return false;
  // youtube.com/results に sp= パラメータ付きも除外 (例: youtube.com/results?sp=mAEA&...)
  if (/(?:www\.|m\.)?youtube\.com\/[^/]*\?[^#]*search_query=/i.test(url)) return false;
  return /(?:(?:www\.|m\.)?youtube\.com|(?:m\.)?youtu\.be|facebook\.com|fb\.watch|drive\.google\.com|vimeo\.com|nicovideo\.jp|tiktok\.com)/i.test(url);
}

// ★ [v31] 仕様: URL欄には「YouTube URL(固定動画)」と「検索ワード」の両方を入れられる。
//   http(s) で始まらないエントリは検索ワードとみなし YouTube検索URL に変換する。
function looksLikeHttpUrl(s) {
  return /^https?:\/\//i.test(String(s || '').trim());
}
function buildYtSearchUrl(query) {
  return 'https://www.youtube.com/results?search_query=' + encodeURIComponent(String(query || '').trim());
}
function normalizeSeriesEntry(entry) {
  var e = String(entry || '').trim();
  if (!e) return '';
  if (looksLikeHttpUrl(e)) return e;     // URL(固定動画 or 既存の検索結果URL)はそのまま
  return buildYtSearchUrl(e);            // 検索ワード → YouTube検索URL に変換
}
// ★ [v31] 仕様: シリーズの再生対象URL一覧を取得する。
//   ・URL欄のエントリ(URL or 検索ワード)を正規化
//   ・URLが1本も無く label(タイトル)がある場合は label でYouTube検索する
function getSeriesEntries(series) {
  if (!series) return [];
  var raw = (series.urls || []);
  var out = [];
  for (var i = 0; i < raw.length; i++) {
    var n = normalizeSeriesEntry(raw[i]);
    if (n) out.push(n);
  }
  if (!out.length && series.label && String(series.label).trim()) {
    out.push(buildYtSearchUrl(series.label));
  }
  return out;
}

function collectYouTubeIds(text) {
  var ids = [], seen = {};
  var patterns = [
    /(?:m\.)?youtu\.be\/([a-zA-Z0-9_-]{11})/g,
    /[?&]v=([a-zA-Z0-9_-]{11})/g,
    /\/embed\/([a-zA-Z0-9_-]{11})/g,
    /\/v\/([a-zA-Z0-9_-]{11})/g,
    /\/shorts\/([a-zA-Z0-9_-]{11})/g,
    /m\.youtube\.com\/watch[^"'\s<>]*[?&]v=([a-zA-Z0-9_-]{11})/g
  ];
  for (var pi = 0; pi < patterns.length; pi++) {
    var m, re = patterns[pi];
    while ((m = re.exec(text)) !== null) {
      var id = m[1];
      if (!seen[id] && !YT_SAMPLE_VIDEO_IDS[id]) { seen[id] = 1; ids.push(id); }
    }
  }
  return ids;
}

// ★ [v29-BUG] YouTube検索結果ページ専用のID抽出。
//   collectYouTubeIds() はパターン優先順(youtu.be→?v=→embed...)で並ぶため
//   検索結果の関連度順が崩れ、さらに「おすすめ」「Shorts棚」「広告」など
//   検索クエリと無関係な動画IDまで拾ってしまう。
//   この関数は watch?v= / youtu.be / shorts / embed の「実際の動画リンク」だけを
//   ページ内の出現順(=検索結果の上位順)のまま重複排除して返す。
function collectSearchResultIds(text) {
  var ids = [], seen = {};
  // ★ [v30] jina.ai はリンクを host 無し/相対(/watch?v=ID 等)で出力する事があるため
  //   ホスト名に依存せず watch?v= / youtu.be / shorts / embed の実リンクを
  //   ドキュメント出現順(=検索結果の上位順)で抽出する。
  //   bare な「&v=」(プレイリスト/関連動画パラメータ等)は拾わず watch?v= に限定し、
  //   無関係動画の混入を抑える。
  var re = /(?:watch\?(?:[^"'\s<>]*&)?v=|youtu\.be\/|\/shorts\/|\/embed\/)([a-zA-Z0-9_-]{11})/g;
  var m;
  while ((m = re.exec(text)) !== null) {
    var id = m[1];
    if (id && !seen[id] && !YT_SAMPLE_VIDEO_IDS[id]) { seen[id] = 1; ids.push(id); }
  }
  return ids;
}

// ★ [v37-BUG / v38] YouTube検索結果(DD67000等)で「他の動画が再生される」BUGの修正。
//   collectSearchResultIds() は検索結果ページ内の watch?v=/youtu.be/shorts/embed を
//   出現順で全部拾うため、検索クエリと無関係な「おすすめ動画・Shorts棚・広告・
//   サイドバー候補」のIDまで混入し、その上位2本が再生されていた。
//   検索ワードのトークンに「関連する動画ID」だけを出現順(=関連度順)で残す。
//   jina.ai の出力形式差に強くするため2段で判定する:
//     Pass A: markdown リンク [タイトル](URL) のリンクテキスト(=タイトル)で判定(最も正確)
//     Pass B: Aが0件のとき、各動画リンクの近傍テキストにトークンが出るかで判定
//             (jinaがタイトルをリンクテキストにしない形式へのフォールバック)
//   ・query が空 or トークン無し → 従来どおり全件(出現順)を返す
//   ・A・Bとも0件             → 呼び出し側で collectSearchResultIds にフォールバック
// ★ [v39-BUG] 「JBL DD67000」のように検索ワードに区切り文字が無くても、実際の動画
//   タイトル側が「DD-67000」のようにハイフン/スペース入りの型番表記になっている場合、
//   単純な部分一致では一致せず関連動画0件→無関係動画への全件フォールバックが発生して
//   いた。ハイフン・アンダースコア・空白を除去してから比較することで、区切り文字の
//   有無に関わらず型番が一致するようにする(例: "DD67000" と "DD-67000" は一致)。
function stripSeparators(s) {
  return String(s || '').replace(/[\s　_-]+/g, '');
}

function collectRelevantSearchIds(text, query) {
  var toks = searchQueryTokens(query);
  if (!toks.length) return collectSearchResultIds(text);  // 検索ワード不明時は従来動作
  var lowToks = [];
  for (var ti = 0; ti < toks.length; ti++) lowToks.push(stripSeparators(String(toks[ti]).toLowerCase()));
  var t = String(text || '');
  var idRe = /(?:watch\?(?:[^&]*&)?v=|youtu\.be\/|\/shorts\/|\/embed\/)([a-zA-Z0-9_-]{11})/;
  function hasTok(s) {
    var ls = stripSeparators(String(s).toLowerCase());
    for (var k = 0; k < lowToks.length; k++) { if (ls.indexOf(lowToks[k]) !== -1) return true; }
    return false;
  }

  // ---- Pass A: markdown リンクテキスト(タイトル)で関連判定 ----
  //   通常 : [JBL DD67000 試聴レビュー](https://www.youtube.com/watch?v=ID)
  //   サムネ: [![alt(=タイトル)](thumb.jpg)](.../watch?v=ID)
  var idsA = [], seenA = {};
  var linkRe = /\[([^\]]*(?:\[[^\]]*\][^\]]*)*)\]\(([^)\s]+)[^)]*\)/g;
  var m;
  while ((m = linkRe.exec(t)) !== null) {
    var idm = (m[2] || '').match(idRe);
    if (!idm) continue;
    var idA = idm[1];
    if (!idA || seenA[idA] || YT_SAMPLE_VIDEO_IDS[idA]) continue;
    // リンクテキスト内の画像構文 ![alt](src) は alt(=タイトル)だけ残す
    var title = (m[1] || '').replace(/!\[([^\]]*)\]\([^)]*\)/g, '$1');
    if (hasTok(title)) { seenA[idA] = 1; idsA.push(idA); }  // 最初の関連出現のみ採用
  }
  if (idsA.length) return idsA;

  // ---- Pass B: 近傍テキストで関連判定(タイトルがリンクテキストでない形式向け) ----
  //   見出し型レイアウトではタイトルはリンクの直前に出る。後方を見ると「次の結果の
  //   タイトル」を誤検出するため前方のみ参照する(タイトルがリンク後に来るサムネ型は
  //   Pass A のリンクテキスト解析側で既に拾える)。
  var low = t.toLowerCase();
  var idsB = [], seenB = {};
  var WIN_BEFORE = 120;
  var reB = /(?:watch\?(?:[^"'\s<>]*&)?v=|youtu\.be\/|\/shorts\/|\/embed\/)([a-zA-Z0-9_-]{11})/g;
  var mb;
  while ((mb = reB.exec(t)) !== null) {
    var idB = mb[1];
    if (!idB || seenB[idB] || YT_SAMPLE_VIDEO_IDS[idB]) continue;
    var pos = mb.index;
    var ws = pos - WIN_BEFORE; if (ws < 0) ws = 0;
    if (hasTok(low.slice(ws, pos))) { seenB[idB] = 1; idsB.push(idB); }
  }
  return idsB;  // 0件なら呼び出し側が collectSearchResultIds にフォールバック
}

function extractYouTubeId(url) {
  if (!url) return null;
  url = String(url).trim();
  if (/^[a-zA-Z0-9_-]{11}$/.test(url)) return url;
  var m;
  m = url.match(/(?:m\.)?youtu\.be\/([a-zA-Z0-9_-]{11})/);
  if (m) return m[1];
  m = url.match(/(?:v=|\/embed\/|\/v\/|\/shorts\/)([a-zA-Z0-9_-]{11})/);
  if (m) return m[1];
  return null;
}

// ★ URLから再生開始秒数を取得 (t=552s / t=552 / t=9m12s / t=1h2m3s に対応)
function extractYouTubeStartSeconds(url) {
  if (!url) return 0;
  // t=1h2m3s / t=9m12s / t=552s / t=552 の全パターンに対応
  var m = String(url).match(/[?&]t=(?:(\d+)h)?(?:(\d+)m)?(\d+)s?(?:&|$|#)/);
  if (!m) {
    // &t= が末尾の場合 (&t=552 など & の後ろが無いケース)
    m = String(url).match(/[?&]t=(?:(\d+)h)?(?:(\d+)m)?(\d+)s?$/);
  }
  if (!m) return 0;
  var h = parseInt(m[1] || '0', 10);
  var min = parseInt(m[2] || '0', 10);
  var sec = parseInt(m[3] || '0', 10);
  var total = h * 3600 + min * 60 + sec;
  return total > 0 ? total : 0;
}

function isYouTubeSearchUrl(url) {
  return /(?:www\.|m\.)?youtube\.com\/results/i.test(url)
      || /(?:www\.|m\.)?youtube\.com\/[^/]*\?[^#]*search_query=/i.test(url);
}

function fetchAndCollect(url, cb) {
  if (!url || !url.trim()) { cb(null); return; }
  url = url.trim();
  if (isDigitalConcertHallUrl(url)) {
    // ★ [仕様変更] Digital Concert Hall も操作パネルの開閉で扱いを切り替える:
    //     - パネルOPEN中  → 従来どおり DCH ホームページへ画面遷移
    //     - パネルCLOSE中 → 画面遷移せず SKIP して次の動画へ進む
    if (isYtPanelClosed()) { cb({ skip: true }); }
    else { cb({ navigateUrl: url }); }
    return;
  }
  // ★ [v41-BUG / 根本対策] YouTube検索結果URL(youtube.com/results?search_query=...)を
  //   jina.aiでスクレイプして「それらしい1本」を推測再生する方式は、jina.ai側の
  //   ブロック挙動(403→ヘッダー追加で回避→さらに401)に何度も追随する必要があり
  //   根本的に不安定と判明した(2026-07-16)。ユーザー指示により方針転換:
  //   スクレイプ・推測再生はやめ、検索ワード(タイトル)によるYouTube検索結果
  //   ページへの**直接画面遷移**に統一する。これはGoogle検索結果・ホームページ等の
  //   「動画ページでないURL」と全く同じ扱い(下記の共通分岐)であり、
  //   YouTube自身の検索エンジンが返す本物の関連動画を必ず案内できる
  //   (無関係な動画が再生される可能性がそもそも存在しない)。
  //     - パネルCLOSE中 → 画面遷移せず SKIP して次の動画を自動再生
  //     - パネルOPEN中  → 実際のYouTube検索結果ページへ画面遷移
  //   ( DigitalConcertHall だけは上で navigateUrl による画面遷移を常に維持 )
  if (!isPlayableVideoUrl(url)) {
    if (isYtPanelClosed()) { cb({ skip: true }); }
    else { cb({ navigateUrl: url }); }
    return;
  }
  if (isYouTubeUrl(url)) {
    var directId = extractYouTubeId(url);
    if (directId) {
      var startSec = extractYouTubeStartSeconds(url);
      // ★ [BUGFIX] Shorts も通常動画と同じく YT.Player 経路(ytIds)で再生する。
      //   従来は controls:0 制限回避のため bare <iframe> で埋め込んでいたが、
      //   bare iframe には音量API(setVolume)が無く、1曲目がShortsの場合に
      //   「音量調整が効かず一定音量しか出ない」BUGの原因になっていた。
      //   YT.Player は同じ youtube.com/embed/ID を使うため埋め込み可否は同じで、
      //   かつ setVolume で音量を自由に調整できる。
      cb({ ytIds: [directId], fbUrls: [], gdUrls: [], startSeconds: startSec });
      return;
    }
  }
  if (/drive\.google\.com/i.test(url)) {
    var gm = url.match(/\/d\/([a-zA-Z0-9_-]+)/) || url.match(/[?&]id=([a-zA-Z0-9_-]+)/);
    if (gm) { cb({ ytIds: [], fbUrls: [], gdUrls: ['https://drive.google.com/file/d/' + gm[1] + '/preview'] }); return; }
  }
  if (/facebook\.com|fb\.watch/i.test(url)) {
    // jina.ai 経由でページをフェッチし、YouTube ID と Facebook 動画リンクを上から収集
    fetch('https://r.jina.ai/' + url, { credentials: 'omit', mode: 'cors', headers: { 'x-respond-with': 'markdown' } })
      .then(function(r) { if (!r.ok) throw new Error('http'); return r.text(); })
      .then(function(t) {
        var ytIds = collectYouTubeIds(t);
        // Facebook 内のリール・動画・watchリンクを上から順に収集（/reel/含む）
        var fbSeen = {}, fbFound = [];
        var fbAllRe = /https?:\/\/(?:www\.|m\.)?facebook\.com\/(?:reel\/|[^"'\s<>]*\/videos\/|watch[^"'\s<>]*)[^\s"'<>]*/g;
        var fm;
        while ((fm = fbAllRe.exec(t)) !== null) {
          var fu = fm[0].replace(/['">\/\s]+$/, '');
          if (fu && !fbSeen[fu]) { fbSeen[fu] = 1; fbFound.push(fu); }
        }
        // 自分自身のURLも先頭に含める（ページ本体が動画の場合）
        if (!fbSeen[url]) fbFound.unshift(url);
        if (ytIds && ytIds.length) {
          // YouTube IDが見つかれば優先して返す（fbUrls もキュー用に渡す）
          cb({ ytIds: ytIds, fbUrls: fbFound, gdUrls: [], startSeconds: 0 });
        } else if (fbFound.length) {
          cb({ ytIds: [], fbUrls: fbFound, gdUrls: [] });
        } else {
          cb({ ytIds: [], fbUrls: [url], gdUrls: [] });
        }
      })
      .catch(function() {
        // フェッチ失敗時は自分自身を iframe フォールバック
        cb({ ytIds: [], fbUrls: [url], gdUrls: [] });
      });
    return;
  }
  fetch('https://r.jina.ai/' + url, { credentials: 'omit', mode: 'cors', headers: { 'x-respond-with': 'markdown' } })
    .then(function(r) { if (!r.ok) throw new Error('http'); return r.text(); })
    .then(function(t) {
      var fbUrls = [], fbSeen = {};
      var fbRe = /https?:\/\/(?:www\.|m\.)?facebook\.com\/(?:[^"'\s<>]*\/videos\/|watch[^"'\s<>]*)[^\s"'<>]*/g;
      var fm; while ((fm = fbRe.exec(t)) !== null) { var fu = fm[0].replace(/['">\s]+$/,''); if (!fbSeen[fu]){fbSeen[fu]=1;fbUrls.push(fu);} }
      var gdUrls = [], gdSeen = {};
      var gdRe = /https?:\/\/drive\.google\.com\/(?:file\/d\/|open\?id=)([a-zA-Z0-9_-]+)/g;
      var gm2; while ((gm2 = gdRe.exec(t)) !== null) { var ge='https://drive.google.com/file/d/'+gm2[1]+'/preview'; if(!gdSeen[ge]){gdSeen[ge]=1;gdUrls.push(ge);} }
      cb({ ytIds: collectYouTubeIds(t), fbUrls: fbUrls, gdUrls: gdUrls });
    })
    .catch(function() { cb(null); });
}

function injectEmbedIframe(embedUrl, sourceUrl, sourceType) {
  var outer = document.querySelector('.yt-bg-outer');
  if (outer) outer.innerHTML = '<div id="yt-bg-player-host"></div>'
    + '<div id="introTimerBox" style="position:absolute;top:10px;right:10px;background:rgba(0,0,0,.75);color:#fff;padding:6px 12px;border-radius:10px;font-weight:700;font-size:.85rem;z-index:50;display:none;backdrop-filter:blur(6px);border:1px solid rgba(255,255,255,.25);font-variant-numeric:tabular-nums;letter-spacing:.04em">次の動画まで <span id="introTimerValue">60</span>秒</div>'
    + '<div id="introTimerBar" style="position:absolute;left:0;bottom:0;height:3px;width:100%;background:linear-gradient(90deg,#22d3ee,#4ade80);transform-origin:left;z-index:50;display:none"></div>';
  // ★ [BUGFIX] iframe(Shorts/Facebook/GDrive)を埋め込む際、直前のYT.Playerは
  //   上の innerHTML 差し替えで DOM ごと消滅している。しかし ytBgPlayer 変数は
  //   死んだプレイヤーを指したまま残るため、STOPボタンや画面タップの
  //   「if (!p) → iframe を止める」分岐が発火せず p.pauseVideo()(無反応)へ流れ、
  //   2本目以降の Shorts を停止できないBUGの原因になっていた。
  //   → iframe モードに入る時点で必ず破棄して null にし、!p 判定を確実にする。
  try { if (ytBgPlayer && ytBgPlayer.destroy) ytBgPlayer.destroy(); } catch (e) {}
  ytBgPlayer = null;
  var host = document.getElementById('yt-bg-player-host');
  if (!host) return;
  var iframe = document.createElement('iframe');
  iframe.src = embedUrl;
  iframe.id = 'yt-bg-embed-iframe';
  iframe.style.cssText = 'position:absolute;top:50%;left:50%;width:100vw;height:56.25vw;min-height:100vh;min-width:177.77vh;transform:translate(-50%,-50%);border:0;';
  iframe.allow = 'autoplay; fullscreen';
  iframe.allowFullscreen = true;
  host.appendChild(iframe);
  __iframeMode = true;
  // ★ [v20] iframeモード(Shorts/Facebook/GDrive)はストック時間追跡をリセット。
  //   NOW PLAYINGクリック時は currentPlayingUrl(元URL)へそのまま遷移する。
  currentStockVideoId      = '';
  currentStockStartSeconds = 0;
  // ★ [v33] iframeモード(Shorts/Facebook/GDrive等)再生開始 → 自動切り替えタイマーをリスタート
  startAutoSwitchTimer();
  // ★ [v35-BUG1] iframeモードでも最初のユーザー操作で音声をアンロックできるよう待機
  bindAudioUnlockOnce();
  var myToken = ++__nowPlayingToken;
  markBgReady();
  setNowPlayingDisplay('読み込み中...', sourceUrl || embedUrl);
  if (sourceUrl && /youtube\.com|youtu\.be/i.test(sourceUrl)) {
    fetch('https://r.jina.ai/' + sourceUrl, { credentials: 'omit', mode: 'cors', headers: { 'x-respond-with': 'markdown' } })
      .then(function(r) { if (!r.ok) throw new Error('http'); return r.text(); })
      .then(function(t) {
        if (myToken !== __nowPlayingToken) return;
        var title = null, m;
        m = t.match(/<title[^>]*>([^<]{1,200})<\/title>/i);
        if (m) title = m[1].trim().replace(/\s*[-|]\s*YouTube\s*$/i, '').trim();
        if (!title) { m = t.match(/og:title[^>]*content=["']([^"']{1,200})["']/i); if (m) title = m[1].trim(); }
        setNowPlayingDisplay(title || sourceUrl, sourceUrl);
        updateYtTransportUi();
      })
      .catch(function() {
        if (myToken !== __nowPlayingToken) return;
        setNowPlayingDisplay(sourceUrl, sourceUrl);
        updateYtTransportUi();
      });
  } else {
    updateNowPlayingFromPage(sourceUrl || embedUrl, sourceType || 'gdrive');
    updateYtTransportUi();
  }
}

function launchFromCollected(r, sourceUrl) {
  // ★ Shorts 専用: IFrame 直埋め込み
  if (r && r.shortsEmbed) {
    injectEmbedIframe(r.shortsEmbed, sourceUrl || ('https://www.youtube.com/watch?v=' + r.shortsId), 'youtube');
    return;
  }
  if (r && r.ytIds && r.ytIds.length) {
    var ss = (r.ytIds.length === 1) ? (r.startSeconds || 0) : 0;
    instantiateYtBgPlayer(r.ytIds[0], ss);
    updateNowPlaying('youtube', 'https://www.youtube.com/watch?v=' + r.ytIds[0]);
    return;
  }
  if (r && r.fbUrls && r.fbUrls.length) {
    // 複数URLがあれば fbQueueUrls にセットして上から順再生
    fbQueueUrls = r.fbUrls.slice();
    fbQueuePage = 0;
    var u = fbQueueUrls[0];
    injectEmbedIframe(u + (u.indexOf('?') === -1 ? '?' : '&') + 'autoplay=1', sourceUrl || u, 'facebook');
    return;
  }
  if (r && r.gdUrls && r.gdUrls.length) {
    injectEmbedIframe(r.gdUrls[0], sourceUrl || r.gdUrls[0], 'gdrive');
    return;
  }
  instantiateYtBgPlayer(DEFAULT_BG_VIDEO_ID, 0);
  updateNowPlaying('youtube', 'https://www.youtube.com/watch?v=' + DEFAULT_BG_VIDEO_ID);
}

// --- スキップエンジン ---

function getValidUrls() {
  // ★ [v31] URL欄の検索ワード対応 + タイトルのみ→検索 を getSeriesEntries に集約
  return getSeriesEntries(SEARCH_SERIES[currentSeriesIndex]);
}

function prepareHost() {
  // ★ BUG FIX: YouTube プレイヤーが生存している場合は destroy しない
  //   → loadVideoById で差し替えるため DOM 破壊は不要
  //   iframe モード（Facebook/Shorts/GDrive）の場合のみ destroy して再生成する
  if (__iframeMode || !ytBgPlayer) {
    destroyYtBgPlayer();
    var outer = document.querySelector('.yt-bg-outer');
    if (outer) outer.innerHTML = '<div id="yt-bg-player-host"></div>'
      + '<div id="introTimerBox" style="position:absolute;top:10px;right:10px;background:rgba(0,0,0,.75);color:#fff;padding:6px 12px;border-radius:10px;font-weight:700;font-size:.85rem;z-index:50;display:none;backdrop-filter:blur(6px);border:1px solid rgba(255,255,255,.25);font-variant-numeric:tabular-nums;letter-spacing:.04em">次の動画まで <span id="introTimerValue">60</span>秒</div>'
      + '<div id="introTimerBar" style="position:absolute;left:0;bottom:0;height:3px;width:100%;background:linear-gradient(90deg,#22d3ee,#4ade80);transform-origin:left;z-index:50;display:none"></div>';
  } else {
    // YT IFrame API プレイヤー生存中: トークンだけ更新してプレイヤーは保持
    ytSearchFetchToken++;
    __nowPlayingToken++;
  }
}

// ★ [v41] jina.aiスクレイプ(403→401とブロック挙動が変わり続け根本的に不安定と
//   判明)には依存しない。呼び出し側が空配列を受け取った場合、無関係な固定動画
//   (DEFAULT_BG_VIDEO_ID)を再生する代わりに、この関数が返す実際のYouTube検索
//   URL(第2引数 `url`)へ直接画面遷移させること(navigateToNonPlayableUrl参照)。
function fetchSearchResultIds(cb) {
  // ★FIX: 同じシリーズの urls 内に YouTube 検索URLがあればそれを優先利用する
  //        （btn は単なるボタン名で、ユーザの意図した検索クエリと一致しない場合がある）
  var seriesUrls = (SEARCH_SERIES[currentSeriesIndex] && SEARCH_SERIES[currentSeriesIndex].urls) || [];
  var url = '';
  for (var si = seriesUrls.length - 1; si >= 0; si--) {
    var su = seriesUrls[si] && seriesUrls[si].trim();
    if (su && /youtube\.com\/results/i.test(su)) { url = su; break; }
  }
  if (!url) {
    var q = SEARCH_SERIES[currentSeriesIndex].btn;
    url = 'https://www.youtube.com/results?search_query=' + encodeURIComponent(q);
  }
  cb([], url);
}

// ★ [v32] 検索結果の関連性チェック + 一度だけ再検索する機能
//   検索URLから検索ワードを取り出す
function extractSearchQuery(url) {
  var m = String(url || '').match(/[?&]search_query=([^&#]*)/i);
  if (!m) return '';
  try { return decodeURIComponent(m[1].replace(/\+/g, ' ')); }
  catch (e) { return m[1].replace(/\+/g, ' '); }
}
//   検索ワードを意味のあるトークンに分割(全角/半角スペース・+・読点等で区切る)
function searchQueryTokens(q) {
  return String(q || '').split(/[\s\u3000\+,，、]+/).filter(function (t) { return t && t.length >= 2; });
}
//   動画タイトルが検索ワードに関連しているか(トークンを1つでも含めば関連とみなす)
//   タイトル未取得(空)の場合は誤判定を避けるため「関連」とみなす(true)
function isTitleRelevant(title, q) {
  if (!title) return true;
  var toks = searchQueryTokens(q);
  if (!toks.length) return true;
  var lt = stripSeparators(String(title).toLowerCase());
  for (var i = 0; i < toks.length; i++) {
    if (lt.indexOf(stripSeparators(toks[i].toLowerCase())) !== -1) return true;
  }
  return false;
}
//   検索動画の再生開始後にタイトルを取得し、無関係なら一度だけ再検索する監視を開始
function beginSearchRelevanceWatch(query) {
  __searchQuery = query || '';
  if (__searchRelevTimer) { clearTimeout(__searchRelevTimer); __searchRelevTimer = null; }
  if (!__searchQuery) return;
  var watchToken = ytSearchFetchToken;
  var attempts = 0;
  function check() {
    if (watchToken !== ytSearchFetchToken) return; // 別の再生に切り替わった
    attempts++;
    var p = ytBgPlayer;
    var title = '';
    try { if (p && p.getVideoData) title = (p.getVideoData().title || ''); } catch (e) {}
    if (!title && attempts < 4) { __searchRelevTimer = setTimeout(check, 1500); return; }
    var relevant = isTitleRelevant(title, __searchQuery);
    if (!relevant && !__searchRecheckUsed) {
      __searchRecheckUsed = true;
      redoSearchOnce(__searchQuery);
    }
  }
  __searchRelevTimer = setTimeout(check, 2500);
}
//   最新の検索結果で一度だけ再検索し、最上位(最も関連性の高い)動画を再生する
function redoSearchOnce(query) {
  var url = buildYtSearchUrl(query) + '&__fresh=' + Date.now(); // jina.aiキャッシュ回避
  prepareHost();
  var myToken = ++ytSearchFetchToken;
  var prevVid = currentStockVideoId;
  fetchAndCollect(url, function (r) {
    if (myToken !== ytSearchFetchToken) return;
    if (r && r.ytIds && r.ytIds.length) {
      // 最上位=最も関連性が高い。直前の無関係動画と同一なら次の候補にずらす。
      var vid = r.ytIds[0];
      for (var i = 0; i < r.ytIds.length; i++) {
        if (r.ytIds[i] !== prevVid) { vid = r.ytIds[i]; break; }
      }
      // 再検索後は __srq を解除し、これ以上の再チェックは行わない(once)
      __srqIds = []; __srqPlayCount = 0; __srqOnDone = null;
      instantiateYtBgPlayer(vid, 0);
      updateNowPlaying('youtube', 'https://www.youtube.com/watch?v=' + vid);
      setTimeout(function () { syncVolumeFromSlider(); }, 300);
    }
  });
}

// ★ [v15] 背景再生 or 外部遷移を切り替えるヘルパー
// ytBgPlayInBackground=true → 通常の背景再生 (instantiateYtBgPlayer)
// ytBgPlayInBackground=false → YouTube等に画面遷移して再生、終了後に戻る
function launchVideoOrNavigate(vid, startSeconds, platform, directUrl) {
  if (ytBgPlayInBackground) {
    // 通常背景再生
    instantiateYtBgPlayer(vid, startSeconds || 0);
    if (platform === 'youtube') {
      updateNowPlaying('youtube', 'https://www.youtube.com/watch?v=' + vid);
    }
    return;
  }
  // ★ 非背景モード: 外部ページへ遷移して再生
  // ブラウザの「戻る」で戻ってきた時に pageshow で検知 → 次の動画へ自動遷移
  var targetUrl = directUrl || ('https://www.youtube.com/watch?v=' + encodeURIComponent(vid));
  try { sessionStorage.setItem('__ytReturnMode', '1'); } catch(e){}
  window.location.assign(targetUrl);
}

function playAtUrlIndex(idx, dir) {
  prepareHost();
  ytBgFallbackUsed = false;
  var myToken = ++ytSearchFetchToken;
  var validUrls = getValidUrls();
  currentUrlIndex = idx;

  if (idx >= 0 && idx < validUrls.length) {
    var url = validUrls[idx];
    searchResultIds = []; searchResultPage = 0;
    fetchAndCollect(url, function(r) {
      if (myToken !== ytSearchFetchToken) return;
      // ★ DigitalConcertHall 等: 画面遷移を維持
      if (r && r.navigateUrl) {
        navigateToNonPlayableUrl(r.navigateUrl);
        return;
      }
      // ★ [仕様変更] Google検索結果・ホームページ等: 画面遷移せずSKIPして次URLへ
      if (r && r.skip) {
        if (++__skipChainCount > __SKIP_CHAIN_MAX) {
          __skipChainCount = 0;
          launchVideoOrNavigate(DEFAULT_BG_VIDEO_ID, 0, 'youtube', null);
          return;
        }
        var skNextIdx = idx + 1;
        if (skNextIdx < validUrls.length) {
          playAtUrlIndex(skNextIdx, 'next');
        } else if (validUrls.length > 1) {
          playAtUrlIndex(0, 'next');
        } else {
          launchVideoOrNavigate(DEFAULT_BG_VIDEO_ID, 0, 'youtube', null);
        }
        return;
      }
      // ★ Shorts 専用: IFrame 直埋め込み
      if (r && r.shortsEmbed) {
        injectEmbedIframe(r.shortsEmbed, 'https://www.youtube.com/watch?v=' + r.shortsId, 'youtube');
        return;
      }
      if (r && r.ytIds && r.ytIds.length) {
        if (/youtube\.com\/results/i.test(url)) {
          // ★ [v29-BUG] 検索結果は「関連度順の上位2本」を順番に再生し、完了後は次URLへ
          //   (旧 v26 のランダム2本選択は無関係動画を引くため廃止)
          var picked2 = r.ytIds.slice(0, 2);
          var firstId2 = picked2[0];
          // 2本目と完了後コールバックをセット
          var capturedIdx = idx;
          var capturedValidUrls = validUrls.slice();
          __srqIds       = picked2.slice(1);
          __srqPlayCount = 0;
          __srqOnDone    = function() {
            // 次のURLへ進む(なければ循環)
            var nextIdx2 = capturedIdx + 1;
            if (nextIdx2 < capturedValidUrls.length) {
              playAtUrlIndex(nextIdx2, 'next');
            } else {
              // 全URL消化 → 先頭に循環
              playAtUrlIndex(0, 'next');
            }
          };
          launchVideoOrNavigate(firstId2, 0, 'youtube', null);
          // ★ [v32] 新しい検索を開始 → 再検索フラグをリセットし関連性チェック開始
          __searchRecheckUsed = false;
          beginSearchRelevanceWatch(extractSearchQuery(url));
        } else {
          // 直接URLなら startSeconds を引き継ぐ
          var ss = (r.ytIds.length === 1) ? (r.startSeconds || 0) : 0;
          launchVideoOrNavigate(r.ytIds[0], ss, 'youtube', url);
        }
      } else if (r && (r.fbUrls && r.fbUrls.length || r.gdUrls && r.gdUrls.length)) {
        launchFromCollected(r, url);
      } else {
        // ★ BUG FIX: jina.ai フェッチ失敗(403等)
        // Facebook/fb.watch URL なら直接 iframe で再生（フォールバック）
        if (/facebook\.com|fb\.watch/i.test(url)) {
          fbQueueUrls = [url];
          fbQueuePage = 0;
          injectEmbedIframe(url + (url.indexOf('?') === -1 ? '?' : '&') + 'autoplay=1', url, 'facebook');
        } else {
          // それ以外は同シリーズの次URLへスキップ
          var nextIdx = idx + 1;
          if (nextIdx < validUrls.length) {
            playAtUrlIndex(nextIdx, 'next');
          } else {
            // 全URL試し終わり → urls[0] に循環
            if (validUrls.length > 1) {
              playAtUrlIndex(0, 'next');
            } else {
              launchVideoOrNavigate(DEFAULT_BG_VIDEO_ID, 0, 'youtube', null);
            }
          }
        }
      }
    });
  } else {
    if (searchResultIds.length === 0) {
      fetchSearchResultIds(function(ids, searchUrl) {
        if (myToken !== ytSearchFetchToken) return;
        // ★ [v41] jina.aiスクレイプは廃止したため ids は常に空。無関係な固定動画
        //   (DEFAULT_BG_VIDEO_ID)を再生する代わりに、実際のYouTube検索結果
        //   ページへ画面遷移する(パネルCLOSE中はSKIP扱いで何もしない)。
        if (!ids || !ids.length) {
          if (!isYtPanelClosed()) { navigateToNonPlayableUrl(searchUrl); }
          return;
        }
        searchResultIds = ids.slice();
        prepareRandomSearchPool(searchResultIds);
        var vid = getNextRandomSearchId();
        if (vid) { launchVideoOrNavigate(vid, 0, 'youtube', null); }
        else if (!isYtPanelClosed()) { navigateToNonPlayableUrl(searchUrl); }
      });
    } else {
      searchResultPage = dir === 'prev'
        ? Math.max(0, searchResultPage - 1)
        : Math.min(searchResultIds.length - 1, searchResultPage + 1);
      if (dir === 'next' && searchResultPage >= searchResultIds.length - 1) {
        fetchSearchResultIds(function(ids, searchUrl) {
          if (myToken !== ytSearchFetchToken) return;
          // ★FIX: 既存IDに含まれない「本当に新しい」IDだけ追加し、その先頭から再生
          //        新IDが無ければ wrap-around して別の動画を確実に再生する
          var seen = {};
          for (var si2 = 0; si2 < searchResultIds.length; si2++) seen[searchResultIds[si2]] = 1;
          var newOnes = [];
          if (ids && ids.length) {
            for (var ni = 0; ni < ids.length; ni++) {
              if (!seen[ids[ni]]) { newOnes.push(ids[ni]); seen[ids[ni]] = 1; }
            }
          }
          if (newOnes.length > 0) {
            searchResultPage = searchResultIds.length;
            searchResultIds = searchResultIds.concat(newOnes);
          } else if (searchResultIds.length > 1) {
            // 新しい動画が見つからなかった → 既存リストの先頭に巻き戻して別動画を再生
            searchResultPage = 0;
          }
          var vid = searchResultIds[searchResultPage];
          if (vid) { launchVideoOrNavigate(vid, 0, 'youtube', null); }
          else if (!isYtPanelClosed()) { navigateToNonPlayableUrl(searchUrl); }
        });
      } else {
        var vid = searchResultIds[searchResultPage];
        if (vid) { launchVideoOrNavigate(vid, 0, 'youtube', null); }
        else if (!isYtPanelClosed()) { navigateToNonPlayableUrl('https://www.youtube.com/results?search_query=' + encodeURIComponent((SEARCH_SERIES[currentSeriesIndex] && SEARCH_SERIES[currentSeriesIndex].btn) || '')); }
      }
    }
  }
  setTimeout(function() { syncVolumeFromSlider(); }, 300);
  setTimeout(function() { syncVolumeFromSlider(); }, 900);
}

var unifiedRandomPool = [];
var unifiedPlayedPool = [];

function prepareUnifiedPool() {
  var all = [];

  // 検索結果ID群があれば最優先
  if (searchResultIds && searchResultIds.length) {
    for (var i = 0; i < searchResultIds.length; i++) {
      all.push({
        type: 'youtube',
        id: searchResultIds[i]
      });
    }
  }

  if (__unifiedPoolPhase === 0) {
    // ★ フェーズ0: 現シリーズ内のURLだけ
    var urls = getValidUrls();
    for (var j = 0; j < urls.length; j++) {
      all.push({
        type: 'url',
        url: urls[j],
        startSeconds: extractYouTubeStartSeconds(urls[j])
      });
    }
  } else {
    // ★ フェーズ1: 現シリーズ消化済み → 全シリーズ横断
    for (var s = 0; s < SEARCH_SERIES.length; s++) {
      // 現シリーズはフェーズ0で既に再生済みなのでスキップ
      if (s === currentSeriesIndex) continue;
      var sUrls = (SEARCH_SERIES[s] && SEARCH_SERIES[s].urls) || [];
      for (var k = 0; k < sUrls.length; k++) {
        var u = sUrls[k] && sUrls[k].trim();
        if (u) {
          all.push({
            type: 'url',
            url: u,
            seriesIndex: s,
            startSeconds: extractYouTubeStartSeconds(u)
          });
        }
      }
    }
    // 現シリーズも含めて再シャッフル (全シリーズ横断ループ用)
    var curUrls = getValidUrls();
    for (var m = 0; m < curUrls.length; m++) {
      all.push({
        type: 'url',
        url: curUrls[m],
        seriesIndex: currentSeriesIndex,
        startSeconds: extractYouTubeStartSeconds(curUrls[m])
      });
    }
  }

  unifiedRandomPool = shuffleArray(all);
  unifiedPlayedPool = [];
}

function getNextUnifiedItem() {
  if (!unifiedRandomPool.length) {
    // ★ プール枯渇時の挙動
    if (__unifiedPoolPhase === 0) {
      // フェーズ0 → フェーズ1 に移行 (現シリーズ全消化 → 全シリーズ横断)
      __unifiedPoolPhase = 1;
      unifiedPlayedPool = [];
      prepareUnifiedPool();
    } else {
      // フェーズ1継続: 全シリーズ横断シャッフルをループ
      unifiedRandomPool = shuffleArray(unifiedPlayedPool);
      unifiedPlayedPool = [];
    }
  }

  if (!unifiedRandomPool.length) return null;

  var item = unifiedRandomPool.shift();
  unifiedPlayedPool.push(item);

  return item;
}

// =====================================================================
// ★ 起動時イントロ再生シーケンス (v2026-06-08)
//   1. The Great Taking (英語)      検索結果からランダム1本 → 1回再生
//   2. グレート・テイキング (日本語) 検索結果からランダム1本 → 1回再生
//   3. Cancer Therapy1 上から2本のみ順番に再生
//   4. Cancer Therapy2 上から2本のみ順番に再生
//   5. 以後: 操作パネルの一番上のシリーズから順番に全シリーズを順次再生
//      (シャッフルなし・SEARCH_SERIES[0]から上→下の順・各シリーズのurls全部)
//   ※ 検索URLは検索結果からランダム1本 / 直接URLはそのまま1本を再生する。
//   ※ Next/Prevボタンやシリーズボタンを押すとイントロは中断され通常動作へ移行。
// =====================================================================
// ★ 起動時再生シーケンス (v2026-06-11-v26)
//   操作パネルの一番上のシリーズから順番に全シリーズを順次再生
//   (シャッフルなし・SEARCH_SERIES[0]から上→下の順・各シリーズのurls全本)
//   ※ 検索URLは検索結果からランダム2本 / 直接URLはそのまま1本を再生する。
//   ※ Next/Prevボタンやシリーズボタンを押すとシーケンスは中断され通常動作へ移行。
// =====================================================================

var introQueue   = [];     // 再生待ちステップ: {type:'search'|'direct', url:'...'}
var introActive  = false;  // イントロ消化中フラグ
var __introStarted = false;

function introStepType(u) {
  return /youtube\.com\/results/i.test(u) ? 'search' : 'direct';
}

function buildIntroQueue() {
  var q = [];
  // 操作パネルの上から順にSEARCH_SERIES全シリーズを順次再生
  // (シャッフルなし・上から順番どおり・各シリーズのurls全本)
  // ★ [v31] getSeriesEntries で URL欄の検索ワード・タイトルのみ検索に対応
  for (var s = 0; s < SEARCH_SERIES.length; s++) {
    var sUrls = getSeriesEntries(SEARCH_SERIES[s]);
    for (var k = 0; k < sUrls.length; k++) {
      var u = sUrls[k] && sUrls[k].trim();
      if (u) q.push({ type: introStepType(u), url: u });
    }
  }
  introQueue = q;
}

function startIntroSequence() {
  if (__introStarted) return;
  __introStarted = true;
  // ★ [v30] v28/v29 の「最初の1本を頭出し一時停止」機能を撤去。
  //   コールド起動時はユーザー操作が無く、クロスオリジンのYouTube iframe に対する
  //   ブラウザの自動再生ポリシーにより autoplay も親ページからの playVideo() も
  //   ブロックされ、動画が「意図しない一時停止」のまま固まって
  //   画面クリック/PLAYボタンでも復帰できなくなっていた。
  //   → 最初の1本も通常どおり autoplay で再生する(v27以前の挙動に復帰)。
  buildIntroQueue();
  if (!introQueue.length) {
    // フォールバック: 従来通り SEARCH_SERIES[0] を再生
    currentSeriesIndex = 0;
    currentSeriesUrls  = SEARCH_SERIES[0].urls;
    currentUrlIndex    = 0;
    playAtUrlIndex(0, 'next');
    return;
  }
  introActive = true;
  advanceIntro();
}

// イントロ全消化後: SEARCH_SERIES[0]から上→下の順に順次再生(シャッフルなし)
function finishIntroToUnified() {
  introActive = false;
  searchResultIds = [];
  searchResultPage = 0;
  __srqIds = []; __srqPlayCount = 0; __srqOnDone = null;  // ★ [v26]
  // ★ シャッフルなし: 操作パネル一番上のシリーズから順次再生
  currentSeriesIndex = 0;
  currentSeriesUrls  = SEARCH_SERIES[0].urls;
  currentUrlIndex    = 0;
  __unifiedPoolPhase = 0;
  unifiedRandomPool  = [];
  unifiedPlayedPool  = [];
  playAtUrlIndex(0, 'next');
}

function advanceIntro() {
  if (!introActive) { skipToNext(); return; }
  if (!introQueue.length) { finishIntroToUnified(); return; }
  __srqIds = []; __srqPlayCount = 0; __srqOnDone = null;  // ★ [v26] 前ステップの残りをクリア
  var step = introQueue.shift();
  playIntroStep(step);
}

function playIntroStep(step) {
  prepareHost();
  var myToken = ++ytSearchFetchToken;
  fetchAndCollect(step.url, function(r) {
    if (myToken !== ytSearchFetchToken) return;
    // ★ DigitalConcertHall 等: 画面遷移を維持
    if (r && r.navigateUrl) {
      navigateToNonPlayableUrl(r.navigateUrl);
      return;
    }
    // ★ [仕様変更] Google検索結果・ホームページ等: 画面遷移せずSKIPして次イントロステップへ
    if (r && r.skip) {
      if (++__skipChainCount > __SKIP_CHAIN_MAX) {
        __skipChainCount = 0;
        instantiateYtBgPlayer(DEFAULT_BG_VIDEO_ID, 0);
        updateNowPlaying('youtube', 'https://www.youtube.com/watch?v=' + DEFAULT_BG_VIDEO_ID);
        return;
      }
      advanceIntro();
      return;
    }
    // ★ Shorts 専用: IFrame 直埋め込み
    if (r && r.shortsEmbed) {
      injectEmbedIframe(r.shortsEmbed, 'https://www.youtube.com/watch?v=' + r.shortsId, 'youtube');
      setTimeout(function() { syncVolumeFromSlider(); }, 300);
      return;
    }
    if (r && r.ytIds && r.ytIds.length) {
      var vid, ss = 0;
      if (step.type === 'search') {
        // ★ [v29-BUG] 検索結果は「関連度順の上位2本」を順番に再生する。
        //   旧 v26 はプール全体からランダム2本を選んでいたため、抽出に紛れた
        //   おすすめ/Shorts棚など無関係な動画を引いてしまうBUGがあった。
        //   r.ytIds は collectSearchResultIds() により検索結果の出現順なので、
        //   先頭から2本(1本しか無ければ1本)を取れば最も関連性の高い動画になる。
        //   1本目を即再生し、__srqIds/__srqPlayCount/__srqOnDoneをセット。
        //   2本目以降はskipToNextが消化し、完了後advanceIntro()で次ステップへ。
        var picked = r.ytIds.slice(0, 2);
        vid = picked[0];
        // __srqに残り(2本目)と完了後コールバックをセット
        __srqIds       = picked.slice(1);  // 残り0〜1本
        __srqPlayCount = 0;
        __srqOnDone    = function() { advanceIntro(); };
      } else {
        // 直接URL: 1本だけ。startSeconds(t=...)があれば引き継ぐ
        vid = r.ytIds[0];
        ss = (r.ytIds.length === 1) ? (r.startSeconds || 0) : 0;
      }
      instantiateYtBgPlayer(vid, ss);
      updateNowPlaying('youtube', 'https://www.youtube.com/watch?v=' + vid);
      // ★ [v32] 検索ステップなら関連性チェックを開始(無関係なら一度だけ再検索)
      if (step.type === 'search') {
        __searchRecheckUsed = false;
        beginSearchRelevanceWatch(extractSearchQuery(step.url));
      } else {
        __searchQuery = '';
      }
    } else if (r && (r.fbUrls && r.fbUrls.length || r.gdUrls && r.gdUrls.length)) {
      launchFromCollected(r, step.url);
    } else {
      // 取得失敗 → このステップは飛ばして次のイントロステップへ
      advanceIntro();
      return;
    }
    setTimeout(function() { syncVolumeFromSlider(); }, 300);
  });
}

function skipToNext() {
  // ★ [v26] 検索結果「2本再生」消化中チェック
  //   __srqOnDone がセットされている = 検索URLの2本消化シーケンス進行中
  //   __srqPlayCount < 2 → まだ次の1本を再生する
  //   __srqPlayCount >= 2 → 2本消化完了 → onDoneコールバックで次URLへ
  if (__srqOnDone) {
    if (__srqPlayCount < __srqIds.length) {
      // 次の1本を再生
      var nextId = __srqIds[__srqPlayCount];
      __srqPlayCount++;
      prepareHost();
      instantiateYtBgPlayer(nextId, 0);
      updateNowPlaying('youtube', 'https://www.youtube.com/watch?v=' + nextId);
      // ★ [v32] 2本目の検索動画も関連性チェック(再検索フラグは引き継ぐ=合計1回まで)
      beginSearchRelevanceWatch(__searchQuery);
      setTimeout(function() { syncVolumeFromSlider(); }, 300);
      return;
    } else {
      // 2本消化完了 → 次URLへ進む
      var done = __srqOnDone;
      __srqIds = []; __srqPlayCount = 0; __srqOnDone = null;
      done();
      return;
    }
  }

  // ★ イントロ消化中は Next も advanceIntro 経由 (次のイントロステップへ)
  if (introActive) { advanceIntro(); return; }

  // ★ Facebook キュー再生中は次の Facebook 動画へ
  if (fbQueueUrls.length > 1) {
    fbQueuePage = (fbQueuePage + 1) % fbQueueUrls.length;
    var fbNext = fbQueueUrls[fbQueuePage];
    prepareHost();
    injectEmbedIframe(fbNext + (fbNext.indexOf('?') === -1 ? '?' : '&') + 'autoplay=1', fbNext, 'facebook');
    setTimeout(function() { syncVolumeFromSlider(); }, 300);
    return;
  }

  if (!unifiedRandomPool.length) {
    prepareUnifiedPool();
  }

  var item = getNextUnifiedItem();

  if (!item) {
    prepareHost();
    instantiateYtBgPlayer(DEFAULT_BG_VIDEO_ID, 0);
    return;
  }

  prepareHost();  // ★ YTプレイヤー生存中はトークン更新のみ(loadVideoByIdで差し替え)

  // ★ fetch中フラグ: このトークンが変わったら古いコールバックを無視する
  var mySkipToken = ++ytSearchFetchToken;

  if (item.type === 'youtube') {
    instantiateYtBgPlayer(item.id, item.startSeconds || 0);
    updateNowPlaying(
      'youtube',
      'https://www.youtube.com/watch?v=' + item.id
    );
    setTimeout(function() { syncVolumeFromSlider(); }, 300);
    return;
  }

  // ★ BUG FIX: fetchAndCollect が null を返した場合（jina.ai が youtube/results を 403 ブロック等）
  //   → DEFAULT にフォールバックせず、プール内の次のアイテムを再帰的に試みる
  function tryPlayItem(tryItem, token, retryLeft) {
    if (token !== ytSearchFetchToken) return;
    if (!tryItem) {
      instantiateYtBgPlayer(DEFAULT_BG_VIDEO_ID, 0);
      updateNowPlaying('youtube', 'https://www.youtube.com/watch?v=' + DEFAULT_BG_VIDEO_ID);
      setTimeout(function() { syncVolumeFromSlider(); }, 300);
      return;
    }
    if (tryItem.type === 'youtube') {
      instantiateYtBgPlayer(tryItem.id, tryItem.startSeconds || 0);
      updateNowPlaying('youtube', 'https://www.youtube.com/watch?v=' + tryItem.id);
      setTimeout(function() { syncVolumeFromSlider(); }, 300);
      return;
    }
    fetchAndCollect(tryItem.url, function(r) {
      if (token !== ytSearchFetchToken) return;
      // ★ DigitalConcertHall 等: 画面遷移を維持
      if (r && r.navigateUrl) {
        navigateToNonPlayableUrl(r.navigateUrl);
        return;
      }
      // ★ [仕様変更] Google検索結果・ホームページ等: 画面遷移せずSKIPして次アイテムへ
      if (r && r.skip) {
        if (++__skipChainCount > __SKIP_CHAIN_MAX) {
          __skipChainCount = 0;
          instantiateYtBgPlayer(DEFAULT_BG_VIDEO_ID, 0);
          updateNowPlaying('youtube', 'https://www.youtube.com/watch?v=' + DEFAULT_BG_VIDEO_ID);
          setTimeout(function() { syncVolumeFromSlider(); }, 300);
          return;
        }
        if (retryLeft > 0) {
          tryPlayItem(getNextUnifiedItem(), token, retryLeft - 1);
        } else {
          instantiateYtBgPlayer(DEFAULT_BG_VIDEO_ID, 0);
          updateNowPlaying('youtube', 'https://www.youtube.com/watch?v=' + DEFAULT_BG_VIDEO_ID);
          setTimeout(function() { syncVolumeFromSlider(); }, 300);
        }
        return;
      }
      // ★ Shorts 専用: IFrame 直埋め込み
      if (r && r.shortsEmbed) {
        injectEmbedIframe(r.shortsEmbed, 'https://www.youtube.com/watch?v=' + r.shortsId, 'youtube');
        setTimeout(function() { syncVolumeFromSlider(); }, 300);
        return;
      }
      if (r && r.ytIds && r.ytIds.length) {
        var vid = r.ytIds[Math.floor(Math.random() * r.ytIds.length)];
        var ss = (r.ytIds.length === 1) ? (r.startSeconds || 0) : 0;
        instantiateYtBgPlayer(vid, ss);
        updateNowPlaying('youtube', 'https://www.youtube.com/watch?v=' + vid);
        setTimeout(function() { syncVolumeFromSlider(); }, 300);
        return;
      }
      if (r && (r.fbUrls && r.fbUrls.length || r.gdUrls && r.gdUrls.length)) {
        launchFromCollected(r, tryItem.url);
        setTimeout(function() { syncVolumeFromSlider(); }, 300);
        return;
      }
      // r=null (jina.ai 失敗) でも Facebook URL なら直接 iframe で再生
      if (/facebook\.com|fb\.watch/i.test(tryItem.url)) {
        fbQueueUrls = [tryItem.url];
        fbQueuePage = 0;
        var fbU = tryItem.url;
        injectEmbedIframe(fbU + (fbU.indexOf('?') === -1 ? '?' : '&') + 'autoplay=1', fbU, 'facebook');
        setTimeout(function() { syncVolumeFromSlider(); }, 300);
        return;
      }
      if (retryLeft > 0) {
        var nextItem = getNextUnifiedItem();
        tryPlayItem(nextItem, token, retryLeft - 1);
      } else {
        instantiateYtBgPlayer(DEFAULT_BG_VIDEO_ID, 0);
        updateNowPlaying('youtube', 'https://www.youtube.com/watch?v=' + DEFAULT_BG_VIDEO_ID);
        setTimeout(function() { syncVolumeFromSlider(); }, 300);
      }
    });
  }

  tryPlayItem(item, mySkipToken, 5);
}

function skipToPrev() {
  // ★ イントロ消化中は Prev も次のイントロステップへ (スクリプト再生は前進のみ)
  if (introActive) { advanceIntro(); return; }

  // ★ Facebook キュー再生中は前の Facebook 動画へ
  if (fbQueueUrls.length > 1) {
    fbQueuePage = (fbQueuePage - 1 + fbQueueUrls.length) % fbQueueUrls.length;
    var fbPrev = fbQueueUrls[fbQueuePage];
    prepareHost();
    injectEmbedIframe(fbPrev + (fbPrev.indexOf('?') === -1 ? '?' : '&') + 'autoplay=1', fbPrev, 'facebook');
    setTimeout(function() { syncVolumeFromSlider(); }, 300);
    return;
  }

  if (searchResultIds.length > 0 && searchResultPage > 0) {
    searchResultPage--;
    prepareHost();
    var vid = searchResultIds[searchResultPage];
    instantiateYtBgPlayer(vid);
    updateNowPlaying('youtube', 'https://www.youtube.com/watch?v=' + vid);
    setTimeout(function() { syncVolumeFromSlider(); }, 300);
  } else {
    searchResultIds = []; searchResultPage = 0;
    playAtUrlIndex(Math.max(0, currentUrlIndex - 1), 'prev');
  }
}

function switchSeries(idx, isSameSeries) {
  // ★ BUG FIX 仕様:
  //   - 別シリーズのボタンを押した     → そのシリーズの urls[0] を再生
  //   - 現在のシリーズのボタンを再押下 → 同シリーズの次の urls[n+1] を再生 (末尾なら循環)
  // ユーザの明示的な選択 → onPlayerError のリトライカウンタもリセット
  // ★ シリーズボタン押下でイントロは中断
  introActive = false;
  introQueue = [];
  window.__onErrorRetryCount = 0;
  ytBgFallbackUsed = false;
  var nextUrlIdx = 0;
  if (isSameSeries) {
    var len = (SEARCH_SERIES[idx].urls || []).length;
    nextUrlIdx = (len > 0) ? ((currentUrlIndex + 1) % len) : 0;
  }
  currentSeriesIndex = idx;
  currentSeriesUrls  = SEARCH_SERIES[idx].urls;
  currentUrlIndex    = nextUrlIdx;
  searchResultIds    = [];
  searchResultPage   = 0;
  fbQueueUrls        = [];
  fbQueuePage        = 0;
  __srqIds = []; __srqPlayCount = 0; __srqOnDone = null;  // ★ [v26] 検索2本消化リセット
  resetRandomPools();
  unifiedRandomPool  = [];
  unifiedPlayedPool  = [];
  __unifiedPoolPhase = 0;
  var labelEl = document.getElementById('ytSeriesLabel');
  if (labelEl) labelEl.textContent = 'Youtube ' + SEARCH_SERIES[idx].label;
  var allBtns = document.querySelectorAll('.yt-series-btn');
  for (var k = 0; k < allBtns.length; k++) allBtns[k].setAttribute('aria-pressed', 'false');
  var thisBtns = document.querySelectorAll('.yt-series-btn[data-series="' + idx + '"]');
  for (var k = 0; k < thisBtns.length; k++) thisBtns[k].setAttribute('aria-pressed', 'true');
  // ★ [v24] シリーズボタン押下時は背景で再生する (v21の即遷移を廃止・v20以前の仕様に復帰)
  //   同じシリーズを複数回押すたびに nextUrlIdx が +1 循環するため、
  //   ストックされている全URLを順番に確実に再生できる。
  //   タイトル/URLクリック時に外部遷移するのは setNowPlayingDisplay 側が担当。
  playAtUrlIndex(nextUrlIdx, 'next');
}

function reloadYtBackground() {
  introActive = false; introQueue = [];
  searchResultIds = []; searchResultPage = 0; currentUrlIndex = 0;
  __srqIds = []; __srqPlayCount = 0; __srqOnDone = null;  // ★ [v26]
  playAtUrlIndex(0, 'next');
}

function buildSeriesBtnsEarly() {
  var wrap = document.getElementById('ytSeriesBtns');
  if (!wrap || wrap.dataset.built) return;
  var html = '';
  for (var i = 0; i < SEARCH_SERIES.length; i++) {
    html += '<button type="button" class="yt-bg-mini-btn yt-series-btn" data-series="' + i + '" aria-pressed="' + (i === DEFAULT_SERIES_INDEX ? 'true' : 'false') + '">'
          + '<span class="yt-series-label">' + __trimSeriesLabel(SEARCH_SERIES[i].btn) + '</span>'
          + '</button>';
  }
  wrap.innerHTML = html;
  wrap.dataset.built = '1';
  wrap.addEventListener('click', function(ev) {
    var btn = ev.target.closest ? ev.target.closest('.yt-series-btn') : null;
    if (!btn) return;
    var sel = window.getSelection ? window.getSelection() : null;
    if (sel && sel.toString().length > 0) return;
    var idx = parseInt(btn.getAttribute('data-series'), 10);
    if (isNaN(idx)) return;
    // ★ BUG FIX: 「同シリーズ再押下」=「直前にユーザがクリックしたのと同じシリーズ」
    //   起動時の currentSeriesIndex=0 との偶然一致を防ぐ
    var isSame = (window.__userClickedSeries === idx);
    console.log('[v9-CLICK] idx=' + idx + ' prev=' + window.__userClickedSeries + ' isSame=' + isSame + ' curUrlIdx=' + currentUrlIndex);
    window.__userClickedSeries = idx;
    switchSeries(idx, isSame);
  }, false);
  wrap.addEventListener('touchstart', function(ev) {
    var btn = ev.target.closest ? ev.target.closest('.yt-series-btn') : null;
    if (!btn) return;
    var label = btn.querySelector('.yt-series-label');
    if (!label) return;
    var timer = setTimeout(function() {
      try {
        var range = document.createRange();
        range.selectNodeContents(label);
        var s = window.getSelection();
        if (s) { s.removeAllRanges(); s.addRange(range); }
      } catch(e) {}
    }, 400);
    btn._lpt = timer;
  }, { passive: true });
  wrap.addEventListener('touchend', function(ev) {
    var btn = ev.target.closest ? ev.target.closest('.yt-series-btn') : null;
    if (btn && btn._lpt) { clearTimeout(btn._lpt); btn._lpt = null; }
  }, { passive: true });
  wrap.addEventListener('touchmove', function(ev) {
    var btn = ev.target.closest ? ev.target.closest('.yt-series-btn') : null;
    if (btn && btn._lpt) { clearTimeout(btn._lpt); btn._lpt = null; }
  }, { passive: true });
}

// =====================================================================
// シリーズ（Youtube再生候補）ボタン タイトル自動フィット機能
// 目的: 縦スマホ・横スマホ・タブレット・PC のいずれの幅でも
//   ・タイトル全文が隠れない（パネル端でクリップされない）
//   ・タイトルが途中で改行しない（1ボタン = 常に1行）
// 方式: 文字の切り捨て（短縮）は一切行わず、全文を表示したまま、
//   行幅に収まらないボタンだけ「フル行化＋フォントサイズ自動縮小」で合わせる。
//   必要なボタンだけ縮め、短いボタンは基準サイズのまま詰めて並べる。
// =====================================================================
var __ytSeriesFitPending = false;
var __ytSeriesFullCache  = null;   // [全文(前後空白トリム済み), ...]

// 前後の半角/全角スペースを除去（手動パディング廃止 → 自動調整へ一本化）
function __trimSeriesLabel(s){
  return String(s == null ? '' : s).replace(/^[\s\u3000]+|[\s\u3000]+$/g, '');
}

function __buildOriginalsCache(){
  if (__ytSeriesFullCache) return __ytSeriesFullCache;
  var arr = [];
  for (var i = 0; i < SEARCH_SERIES.length; i++) {
    arr.push(__trimSeriesLabel(SEARCH_SERIES[i] && SEARCH_SERIES[i].btn));
  }
  __ytSeriesFullCache = arr;
  return arr;
}

// 全ラベルを常に「全文（トリム済み）」へ復帰させる（短縮はしない）
function __applySeriesShrinkLevel(){
  var wrap = document.getElementById('ytSeriesBtns');
  if (!wrap) return;
  // 旧短縮クラスが残っていれば除去（後方互換）
  wrap.classList.remove('yt-shrink-1','yt-shrink-2','yt-shrink-3');
  var cache = __buildOriginalsCache();
  var labels = wrap.querySelectorAll('.yt-series-label');
  for (var i = 0; i < labels.length; i++){
    var full = cache[i] || '';
    if (labels[i].textContent !== full) labels[i].textContent = full;
    var btn = labels[i].closest ? labels[i].closest('.yt-series-btn') : null;
    if (btn) btn.title = full;   // ホバーで全文確認（縮小時用）
  }
}

// 実フィット処理: 各ボタンの全文ラベルを現在の利用可能幅に収める
function __fitSeriesButtonsNow(){
  var wrap = document.getElementById('ytSeriesBtns');
  if (!wrap) return;
  // まず全文へ復帰し、前回の縮小・フル行指定をリセット
  __applySeriesShrinkLevel();
  var btns = wrap.querySelectorAll('.yt-series-btn');
  if (!btns.length) return;

  var SAFE   = 2;   // 端の余白(px)

  // 一旦すべての上書きを解除して素の状態を作る
  for (var r = 0; r < btns.length; r++){
    btns[r].classList.remove('yt-fit-full','yt-fit-wrap');
    btns[r].style.removeProperty('font-size');
    var lb = btns[r].querySelector('.yt-series-label');
    if (lb) lb.style.removeProperty('font-size');
  }

  // ボタン群が使える行幅（#ytSeriesBtns は flex:1 1 100% で行幅いっぱい）
  var avail = wrap.clientWidth;
  if (!avail || avail < 8){
    var p = wrap.parentNode;
    avail = (p && p.clientWidth) ? p.clientWidth : (window.innerWidth || 360);
  }

  for (var i = 0; i < btns.length; i++){
    var btn = btns[i];
    var label = btn.querySelector('.yt-series-label');
    if (!label) continue;

    // ボタンの水平 padding + border
    var cs = window.getComputedStyle ? window.getComputedStyle(btn) : null;
    var padX = 0;
    if (cs){
      padX = (parseFloat(cs.paddingLeft)  || 0) + (parseFloat(cs.paddingRight)  || 0)
           + (parseFloat(cs.borderLeftWidth) || 0) + (parseFloat(cs.borderRightWidth) || 0);
    }
    // フル行にしたときラベルが使える幅
    var roomFull = avail - padX - SAFE;
    if (roomFull < 1) roomFull = 1;

    // 基準フォントでのラベル本来の幅（nowrap なので1行分の実幅）
    var natural = Math.ceil(label.getBoundingClientRect().width);
    if (!natural) natural = label.scrollWidth || 0;
    if (natural <= 0) continue;

    // 行幅いっぱいでも収まらない長さ → フォントは縮小せず、フル行化して折り返す
    //   （フォントサイズは他のボタンと同じ基準サイズのまま維持する）
    if (natural > roomFull){
      btn.classList.add('yt-fit-full');
      btn.classList.add('yt-fit-wrap');
    }
    // natural <= roomFull のボタンは基準サイズのまま（短いものは詰めて横並び）
  }
}

function autoFitSeriesButtons(){
  if (__ytSeriesFitPending) return;
  __ytSeriesFitPending = true;
  // requestAnimationFrame を 2 回挟んでレイアウト確定を待つ
  var raf = window.requestAnimationFrame || function(cb){ return setTimeout(cb, 16); };
  raf(function(){ raf(function(){
    try { __fitSeriesButtonsNow(); } catch(e){}
    __ytSeriesFitPending = false;
  }); });
}

// リサイズ・回転時に再フィット
(function(){
  var t = null;
  function schedule(){
    if (t) clearTimeout(t);
    t = setTimeout(function(){ t = null; autoFitSeriesButtons(); }, 120);
  }
  window.addEventListener('resize', schedule, { passive: true });
  window.addEventListener('orientationchange', schedule, { passive: true });
  // 初期実行: DOM ready 後と、念のため少し遅延でもう一度
  if (document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', function(){ schedule(); setTimeout(schedule, 400); });
  } else {
    schedule();
    setTimeout(schedule, 400);
  }
  // パネルOPEN/CLOSEの状態変化にも追従
  setTimeout(function(){
    var openBtn = document.getElementById('ytPanelOpen');
    if (openBtn){ openBtn.addEventListener('click', function(){ setTimeout(autoFitSeriesButtons, 60); }); }
  }, 600);
})();

function applyYtSearch() {
  var inp = document.getElementById("ytBgSearch");
  var raw = inp ? inp.value.trim() : '';
  if (!raw) return;
  // URLでない場合は検索ワードとして YouTube 検索URLに変換
  var url = /^https?:\/\//i.test(raw)
    ? raw
    : 'https://www.youtube.com/results?search_query=' + encodeURIComponent(raw);
  searchResultIds = []; searchResultPage = 0; currentUrlIndex = -1;
  prepareHost();
  ytBgFallbackUsed = false; safeBgRotate = 0;
  var myToken = ++ytSearchFetchToken;
  fetchAndCollect(url, function(r) {
    if (myToken !== ytSearchFetchToken) return;
    // ★ Shorts 専用: IFrame 直埋め込み
    if (r && r.shortsEmbed) {
      injectEmbedIframe(r.shortsEmbed, 'https://www.youtube.com/watch?v=' + r.shortsId, 'youtube');
      setTimeout(function() { syncVolumeFromSlider(); }, 300);
      return;
    }
    // 複数IDが取れた場合（検索結果など）は searchResultIds にセットして上から順に再生
    if (r && r.ytIds && r.ytIds.length > 1) {
      searchResultIds = r.ytIds;
      searchResultPage = 0;
      var vid = searchResultIds[0];
      instantiateYtBgPlayer(vid);
      updateNowPlaying('youtube', 'https://www.youtube.com/watch?v=' + vid);
    } else {
      launchFromCollected(r);
    }
    setTimeout(function() { syncVolumeFromSlider(); }, 300);
  });
}

// ★ [v31] 起動直後の「頭出し一時停止/自動再生ブロック」状態を確実に再生開始させる。
//   コールド起動時、YouTube の自動再生ポリシーによりプレイヤーが UNSTARTED(-1)/CUED(5)
//   のまま固まり、親ページからの playVideo() では復帰できないことがある
//   (YouTube は iframe 内の自前の再生ボタンのクリックを要求するが、背景iframeは
//    pointer-events:none のためクリックできない)。
//   シリーズボタンが効くのは loadVideoById() がユーザー操作(ジェスチャ)内で
//   動画を読み込み直し、自動再生が許可されるため。これと同じ経路で復帰させる。
function forcePlayBgPlayer(p) {
  if (!p) return;
  var st = -99;
  try { if (p.getPlayerState) st = p.getPlayerState(); } catch (e) {}
  try { if (p.playVideo) p.playVideo(); } catch (e2) {}
  // UNSTARTED(-1) / CUED(5) は playVideo() では起動できないため、
  // シリーズボタンと同じ loadVideoById() でこのジェスチャ内に強制ロードして再生する。
  if (st === -1 || st === 5) {
    try {
      var vid = currentStockVideoId || DEFAULT_BG_VIDEO_ID;
      var ss  = currentStockStartSeconds || 0;
      if (ss > 0) p.loadVideoById({ videoId: vid, startSeconds: ss });
      else p.loadVideoById(vid);
    } catch (e3) {}
  }
}

function bindYtControlsOnce() {
  if (ytControlsBound) return;
  ytControlsBound = true;
  var searchIn = document.getElementById("ytBgSearch");
  var applyBtn = document.getElementById("ytBgSearchApply");
  var onlyBtn = document.getElementById("ytBgOnlyToggle");
  var volWrap = document.getElementById("ytBgVolumeWrap");

  // 「背景で再生」チェックボックスは廃止（常に背景再生）。初期化処理は不要。

  // ▼▼▼ ここから追加（CLOSE / OPEN ボタンの動作） ▼▼▼
  var panelCloseBtn = document.getElementById("ytPanelClose");
  var panelOpenBtn = document.getElementById("ytPanelOpen");
  if (panelCloseBtn && panelOpenBtn && volWrap) {
    // CLOSEボタンを押した時の処理（パネルを隠してOPENボタンを表示）
    panelCloseBtn.addEventListener("click", function() {
      volWrap.style.display = "none";
      panelOpenBtn.style.display = "block";
    });
    // OPENボタンを押した時の処理（パネルを表示してOPENボタンを隠す）
    panelOpenBtn.addEventListener("click", function() {
      volWrap.style.display = ""; // 元のCSS(display:flex)に戻す
      panelOpenBtn.style.display = "none";
    });
  }
  if (searchIn) searchIn.value = '';

  // Series buttons — buildSeriesBtnsEarly() で既に生成済みの場合はスキップ
  buildSeriesBtnsEarly();
  // 生成直後にラベル自動短縮（CLOSEボタン強制表示）を実行
  if (typeof autoFitSeriesButtons === 'function') {
    setTimeout(autoFitSeriesButtons, 30);
    setTimeout(autoFitSeriesButtons, 300);
  }

  if (applyBtn) applyBtn.addEventListener("click", applyYtSearch, false);
  if (searchIn) searchIn.addEventListener("keydown", function(ev) {
    if (ev.key === "Enter") { ev.preventDefault(); applyYtSearch(); }
  }, false);
  var prevBtn = document.getElementById("ytBgPrev");
  var nextBtn = document.getElementById("ytBgNext");
  var rewBtn  = document.getElementById("ytBgRew");
  var ffBtn   = document.getElementById("ytBgFf");
  if (prevBtn) prevBtn.addEventListener("click", function() { skipToPrev(); }, false);
  if (nextBtn) nextBtn.addEventListener("click", function() { skipToNext(); }, false);
  if (rewBtn) rewBtn.addEventListener("click", function() {
    // ★ REW: 10秒巻き戻し（iframeモードは非対応 — skipToPrevにフォールバック）
    var p = ytBgPlayer;
    if (p && p.getCurrentTime && p.seekTo) {
      try {
        var t = p.getCurrentTime();
        p.seekTo(Math.max(0, t - 10), true);
        updateYtTransportUi();
      } catch(e) { skipToPrev(); }
    } else {
      skipToPrev();
    }
  }, false);
  if (ffBtn) ffBtn.addEventListener("click", function() {
    // ★ FF: 10秒早送り（iframeモードは非対応 — skipToNextにフォールバック）
    var p = ytBgPlayer;
    if (p && p.getCurrentTime && p.seekTo && p.getDuration) {
      try {
        var t = p.getCurrentTime();
        var dur = p.getDuration();
        p.seekTo(Math.min(dur, t + 10), true);
        updateYtTransportUi();
      } catch(e) { skipToNext(); }
    } else {
      skipToNext();
    }
  }, false);
  if (onlyBtn) onlyBtn.addEventListener("click", function() {
    var on = document.body.classList.toggle("yt-only-mode");
    onlyBtn.setAttribute("aria-pressed", on ? "true" : "false");
    onlyBtn.textContent = on ? "\u30b5\u30a4\u30c8\u306b\u623b\u308b" : "Youtube\u306b\u79fb\u52d5";
    setTimeout(function() { syncVolumeFromSlider(); }, 0);
  }, false);
  if (volWrap) volWrap.addEventListener("input", function(ev) {
    if (!ev.target || ev.target.id !== "ytBgVolume") return;
    // ★ [v36-BUG1] スライダー操作はユーザージェスチャ → 「音を出したい意思」を立てる。
    //   v35はここで __audioUnlocked=true を無条件に立てていたが、起動直後で
    //   プレイヤー未ready の場合 applyUnmuteFromSlider() が失敗しても確定フラグだけ
    //   立ってしまい、最初の動画がミュートのまま固まる原因だった。
    //   → 意思だけ立て、実際のアンミュート成立は applyUnmuteFromSlider()/
    //     syncVolumeFromSlider() の中でのみ __audioUnlocked を立てる。
    __wantAudio = true;
    applyUnmuteFromSlider();   // 成立すれば __audioUnlocked を立てる(iframeはmute差し替え)
    syncVolumeFromSlider();    // 未readyならリトライで後追い適用(__wantAudioを参照)
  }, false);
  // ★ [v20] タイトルをタップ/クリック → YouTubeへ画面遷移
  //   ストックURLに時間指定があればその時間で、無ければ頭出しで開く。
  //   (v16の「現在の再生位置から続き」仕様は廃止)
  var nowTitleEl = document.getElementById("ytNowTitle");
  if (nowTitleEl) {
    nowTitleEl.title = 'タップ → 動画/ページを開く';
    nowTitleEl.style.cursor = 'pointer';
    nowTitleEl.addEventListener("click", function(ev) {
      var sel = window.getSelection ? window.getSelection() : null;
      if (sel && sel.toString().length > 0) return; // テキスト選択中は無視
      var destUrl = buildNowPlayingNavUrl();
      if (!destUrl && typeof currentPlayingUrl !== 'undefined' && currentPlayingUrl) {
        destUrl = currentPlayingUrl;
      }
      if (destUrl) { window.location.assign(destUrl); }
    }, false);
  }

  var loopToggle = document.getElementById("ytBgLoopToggle");
  if (loopToggle) loopToggle.addEventListener("click", function() {
    ytBgLoop = !ytBgLoop;
    updateYtTransportUi();
  }, false);

  var playBtn = document.getElementById("ytBgPlayBtn");
  var stopBtn = document.getElementById("ytBgStopBtn");
  if (playBtn) playBtn.addEventListener("click", function() {
    var p = ytBgPlayer;
    if (!p) {
      var iframe = document.getElementById('yt-bg-embed-iframe');
      if (iframe) {
        var resumeSrc = iframe.dataset.pausedSrc || iframe.src;
        if (resumeSrc && resumeSrc !== 'about:blank') {
          if (resumeSrc.indexOf('autoplay=0') !== -1) resumeSrc = resumeSrc.replace('autoplay=0','autoplay=1');
          else if (resumeSrc.indexOf('autoplay=') === -1) resumeSrc += (resumeSrc.indexOf('?')===-1?'?':'&')+'autoplay=1';
          iframe.src = resumeSrc;
          iframe.dataset.pausedSrc = '';
        }
      }
      updateYtTransportUi(); startAutoSwitchTimer(); return;
    }
    forcePlayBgPlayer(p);
    syncVolumeFromSlider();
    updateYtTransportUi();
    startAutoSwitchTimer(); // ★ [v33] 再生再開時に自動切り替えタイマーを再開
  }, false);
  if (stopBtn) stopBtn.addEventListener("click", function() {
    cancelAutoSwitchTimer(); // ★ [v33] 一時停止中は自動切り替えカウントも止める
    var p = ytBgPlayer;
    if (!p) {
      var iframe = document.getElementById('yt-bg-embed-iframe');
      if (iframe) { iframe.dataset.pausedSrc = iframe.src; iframe.src = 'about:blank'; }
      updateYtTransportUi(); return;
    }
    try { if (p.pauseVideo) p.pauseVideo(); updateYtTransportUi(); } catch(e) {}
  }, false);
  bindYtBgInteractionOnce();
  bindYtCardHandoffOnce();
}

function hideYtCtxMenu() {
  var menu = document.getElementById("ytBgCtxMenu");
  if (!menu) return;
  menu.style.display = "none";
  menu.hidden = true;
}

function showYtCtxMenu(clientX, clientY) {
  var menu = document.getElementById("ytBgCtxMenu");
  if (!menu) return;
  menu.hidden = false;
  menu.style.display = "block";
  var w = menu.offsetWidth || 160;
  var h = menu.offsetHeight || 200;
  var x = Math.min(clientX, window.innerWidth - w - 6);
  var y = Math.min(clientY, window.innerHeight - h - 6);
  menu.style.left = Math.max(4, x) + "px";
  menu.style.top = Math.max(4, y) + "px";
}

function bindYtBgInteractionOnce() {
  if (ytCtxMenuBound) return;
  ytCtxMenuBound = true;
  var menu = document.getElementById("ytBgCtxMenu");
  function toggleBgPlayPause() {
    var now = Date.now();
    if (now - ytBgLastToggleMs < 320) return;
    ytBgLastToggleMs = now;
    var p = ytBgPlayer;
    // ★ [v11-BUG3] iframeモード中はiframeを直接操作
    if (!p || !p.getPlayerState) {
      var iframe = document.getElementById('yt-bg-embed-iframe');
      if (!iframe) return;
      if (iframe.src && iframe.src !== 'about:blank') {
        iframe.dataset.pausedSrc = iframe.src;
        iframe.src = 'about:blank';
      } else {
        var resumeSrc = iframe.dataset.pausedSrc || '';
        if (resumeSrc) {
          if (resumeSrc.indexOf('autoplay=0') !== -1) resumeSrc = resumeSrc.replace('autoplay=0','autoplay=1');
          else if (resumeSrc.indexOf('autoplay=') === -1) resumeSrc += (resumeSrc.indexOf('?')===-1?'?':'&')+'autoplay=1';
          iframe.src = resumeSrc;
          iframe.dataset.pausedSrc = '';
        }
      }
      updateYtTransportUi();
      return;
    }
    try {
      var YT = window.YT;
      if (!YT) return;
      var st = p.getPlayerState();
      if (st === YT.PlayerState.PLAYING) {
        if (p.pauseVideo) p.pauseVideo();
      } else {
        forcePlayBgPlayer(p);
        syncVolumeFromSlider();
      }
      updateYtTransportUi();
    } catch (e) {}
  }
  document.addEventListener("click", function(ev) {
    if (menu && !menu.hidden && menu.contains(ev.target)) return;
    if (menu && !menu.hidden) hideYtCtxMenu();
    if (ev.button !== 0) return;
    var el = ev.target;
    if (el && el.closest && el.closest("a,button,input,textarea,select,label,.yt-bg-vol-wrap,.yt-bg-ctx-menu,.card[data-lang-code]")) return;
    toggleBgPlayPause();
  }, true);
  document.addEventListener("contextmenu", function(ev) {
    var el = ev.target;
    if (el && el.closest && el.closest("#ytBgVolumeWrap,.yt-bg-ctx-menu,a,button,input,textarea,select,label")) return;
    ev.preventDefault();
    showYtCtxMenu(ev.clientX, ev.clientY);
  }, true);
  if (menu) {
    var btns = menu.querySelectorAll("[data-yt-ctx]");
    for (var i = 0; i < btns.length; i++) {
      btns[i].addEventListener("click", function(ev) {
        ev.preventDefault();
        ev.stopPropagation();
        var act = ev.currentTarget.getAttribute("data-yt-ctx");
        var p = ytBgPlayer;
        hideYtCtxMenu();
        if (!p) return;
        try {
          if (act === "play") {
            if (p.playVideo) p.playVideo();
            syncVolumeFromSlider();
            return;
          }
          if (act === "pause") {
            if (p.pauseVideo) p.pauseVideo();
            return;
          }
          if (act === "loop") {
            ytBgLoop = !ytBgLoop;
            updateYtTransportUi();
            return;
          }
          if (act === "mute") {
            var mu = p.isMuted && p.isMuted();
            if (mu) {
              if (p.unMute) p.unMute();
              syncVolumeFromSlider();
            } else {
              if (p.mute) p.mute();
            }
            return;
          }
          if (act === "seekBack" && p.getCurrentTime && p.seekTo) {
            p.seekTo(Math.max(0, p.getCurrentTime() - 10), true);
            return;
          }
          if (act === "seekFwd" && p.getCurrentTime && p.seekTo) {
            p.seekTo(p.getCurrentTime() + 10, true);
            return;
          }
          if (act === "volDown" || act === "volUp") {
            var vel = document.getElementById("ytBgVolume");
            var vv = vel ? (parseInt(vel.value, 10) || 0) : 0;
            vv += act === "volUp" ? 10 : -10;
            if (vv < 0) vv = 0;
            if (vv > 100) vv = 100;
            if (vel) vel.value = String(vv);
            syncVolumeFromSlider();
            return;
          }
          if (act === "openYt") {
            var vid = null;
            try {
              if (p.getVideoData) vid = p.getVideoData().video_id;
            } catch (e1) {}
            if (vid) window.open("https://www.youtube.com/watch?v=" + encodeURIComponent(vid), "_blank", "noopener,noreferrer");
          }
        } catch (e) {}
      });
    }
  }
}

// ------------------------------------------------------------------
// Now Playing 情報表示
// ------------------------------------------------------------------
function setNowPlayingDisplay(title, url) {
  var titleEl = document.getElementById('ytNowTitle');
  var urlEl   = document.getElementById('ytNowUrl');
  if (titleEl) {
    titleEl.textContent = title || '— Title Unknown —';
    // ★ [v24] タイトルクリックは動画/ページへ同タブ遷移
    titleEl.title = 'タップ → 動画/ページを開く';
  }
  if (urlEl) {
    if (url) {
      urlEl.textContent = url;
      urlEl.href = url;
      urlEl.setAttribute('data-no-translate', '1');
      if (urlEl.dataset) urlEl.dataset.translateProxied = '1';
      urlEl.setAttribute('rel', 'noopener noreferrer');
      urlEl.style.display = '';
      // ★ [v24] URLクリック → 外部サイト/YouTubeへ同タブ画面遷移
      //   YouTube直接動画URL → buildNowPlayingNavUrl() でストックURL由来の時間指定を付与
      //   YouTube検索/Google検索/その他URL → そのまま window.location.assign() で遷移
      urlEl.removeAttribute('target');
      (function(capturedUrl) {
        urlEl.onclick = function(ev) {
          ev.preventDefault();
          var sel = window.getSelection ? window.getSelection() : null;
          if (sel && sel.toString().length > 0) return; // テキスト選択中は無視
          var isYtDirect = /youtube\.com\/watch|youtu\.be\//.test(capturedUrl);
          var finalUrl = isYtDirect ? (buildNowPlayingNavUrl() || capturedUrl) : capturedUrl;
          window.location.assign(finalUrl);
        };
      })(url);
    } else {
      urlEl.textContent = '';
      urlEl.href = '#';
      urlEl.onclick = null;
      urlEl.style.display = 'none';
    }
  }
}

/** YouTubeプレイヤーからタイトル・URLを取得して表示 */
function updateNowPlayingFromYt() {
  try {
    if (!ytBgPlayer || !ytBgPlayer.getVideoData) return;
    var vd = ytBgPlayer.getVideoData();
    var title = (vd && vd.title) ? vd.title : null;
    var vid   = (vd && vd.video_id) ? vd.video_id : null;
    var url   = vid ? 'https://www.youtube.com/watch?v=' + vid : null;
    setNowPlayingDisplay(title, url);
  } catch(e) {
    setNowPlayingDisplay(null, null);
  }
}

/** Facebook / Googleドライブの場合、jina.ai経由でページをフェッチしてタイトルを探す */
function updateNowPlayingFromPage(pageUrl, type) {
  if (!pageUrl) { setNowPlayingDisplay(null, pageUrl || null); return; }
  // まず仮表示
  setNowPlayingDisplay('Searching...', pageUrl);
  fetch('https://r.jina.ai/' + pageUrl, { credentials: 'omit', mode: 'cors', headers: { 'x-respond-with': 'markdown' } })
    .then(function(r) { if (!r.ok) throw new Error('http'); return r.text(); })
    .then(function(text) {
      // <title>タグ or og:title を探す
      var title = null;
      var m;
      m = text.match(/<title[^>]*>([^<]{1,200})<\/title>/i);
      if (m) title = m[1].trim().replace(/\s*[-|]\s*(?:YouTube|Facebook|Google Drive).*$/i, '').trim();
      if (!title) {
        m = text.match(/og:title[^>]*content=["']([^"']{1,200})["']/i);
        if (m) title = m[1].trim();
      }
      if (!title) {
        // h1タグ
        m = text.match(/<h1[^>]*>([^<]{1,200})<\/h1>/i);
        if (m) title = m[1].replace(/<[^>]+>/g,'').trim();
      }
      setNowPlayingDisplay(title || null, pageUrl);
    })
    .catch(function() {
      setNowPlayingDisplay(null, pageUrl);
    });
}

/** 再生開始時に呼ぶ — type と url を渡す */
function updateNowPlaying(type, url) {
  currentPlayingType = type || '';
  currentPlayingUrl  = url  || '';
  __iframeMode = false;
  var myToken = ++__nowPlayingToken;
  if (type === 'youtube') {
    setTimeout(function() { if (myToken !== __nowPlayingToken) return; updateNowPlayingFromYt(); }, 800);
    setTimeout(function() { if (myToken !== __nowPlayingToken) return; updateNowPlayingFromYt(); }, 2500);
  } else if (type === 'facebook' || type === 'gdrive') {
    updateNowPlayingFromPage(url, type);
  } else {
    setNowPlayingDisplay(null, url || null);
  }
}

function markBgReady() {
  try { document.body.classList.add("yt-bg-ready"); } catch (e) {}
}

function isLikelyAdTitle(t) {
  if (!t || typeof t !== "string") return false;
  if (/[広告]|スポンサー|Advertisement|Sponsored|^\s*Ad\s| 広告/i.test(t)) return true;
  return false;
}

function skipIfAd(player) {
  try {
    if (!player || !player.getVideoData) return;
    var vd = player.getVideoData();
    var title = (vd && vd.title) ? vd.title : "";
    if (isLikelyAdTitle(title)) {
      if (player.nextVideo) player.nextVideo();
    }
  } catch (e) {}
}

function clearAdTimer() {
  if (adCheckTimer) {
    clearInterval(adCheckTimer);
    adCheckTimer = null;
  }
}

function loadSafeBgVideo(player, idx) {
  var id = SAFE_BG_VIDEO_IDS[idx % SAFE_BG_VIDEO_IDS.length];
  try {
    if (player && player.loadVideoById) player.loadVideoById(id);
  } catch (e) {}
}

function onPlayerError(ev) {
  var p = ev.target;
  // ★ destroyYtBgPlayer 後に遅延発火した古いエラーイベントは無視
  if (p !== ytBgPlayer) return;
  try {
    // ★ BUG FIX: 埋め込み再生不可動画(エラーコード 101/150)の場合、
    //   reloadYtBackground() で urls[0] に戻すのではなく、
    //   同シリーズの次のURLへスキップする
    var errCode = (ev && typeof ev.data !== 'undefined') ? ev.data : 0;
    var isEmbedDenied = (errCode === 101 || errCode === 150);
    if (isEmbedDenied) {
      var validUrls2 = getValidUrls();
      if (validUrls2.length > 1) {
        var nextIdx2 = (currentUrlIndex + 1) % validUrls2.length;
        if (!window.__onErrorRetryCount) window.__onErrorRetryCount = 0;
        window.__onErrorRetryCount++;
        if (window.__onErrorRetryCount <= validUrls2.length) {
          playAtUrlIndex(nextIdx2, 'next');
          return;
        }
        window.__onErrorRetryCount = 0;
      }
    } else {
      window.__onErrorRetryCount = 0;
    }
    if (!ytBgFallbackUsed) {
      ytBgFallbackUsed = true;
      reloadYtBackground();
      return;
    }
    if (safeBgRotate < SAFE_BG_VIDEO_IDS.length) {
      loadSafeBgVideo(p, safeBgRotate);
      safeBgRotate++;
      try { if (p.playVideo) p.playVideo(); } catch (e1) {}
      return;
    }
    try {
      if (p && p.loadVideoById) p.loadVideoById(DEFAULT_BG_VIDEO_ID);
      if (p && p.playVideo) p.playVideo();
    } catch (e2) {}
  } catch (e) {
    try {
      if (p && p.loadVideoById) p.loadVideoById(DEFAULT_BG_VIDEO_ID);
      if (p && p.playVideo) p.playVideo();
    } catch (e3) {}
  }
}

function onPlayerStateChange(ev) {
  var p = ev.target;
  var YT = window.YT;
  if (!YT) return;
  clearAdTimer();
  if (ev.data === YT.PlayerState.PLAYING || ev.data === YT.PlayerState.BUFFERING) {
    markBgReady();
  }
  updateYtTransportUi();
  if (ev.data === YT.PlayerState.PLAYING) {
    setTimeout(function() { skipIfAd(p); }, 500);
    setTimeout(function() { skipIfAd(p); }, 2000);
    adCheckTimer = setInterval(function() { skipIfAd(p); }, 3500);
    // ★ BUG FIX: setPlaybackQuality('hd2160') 削除 → ABR自動適応に委ねる
    // タイトル更新（API経由）
    setTimeout(function() { updateNowPlayingFromYt(); }, 600);
    // ★ [v34-BUG1] 再生開始(PLAYING)時に音量を再適用。
    //   起動直後の最初の動画は onPlayerReady 時点でまだ unMute/setVolume が
    //   反映されないケースがあるため、ここでも一度同期する。
    syncVolumeFromSlider();
    return;
  }
  if (ev.data === YT.PlayerState.ENDED) {
    // ★ destroyYtBgPlayer 後に遅延発火した古いイベントを無視する
    //    (switchSeries 直後など、既に別の再生が開始している場合はスキップしない)
    if (p !== ytBgPlayer) return;
    // ★ [v34-BUG2] 自然終了時に60秒自動切替タイマーが重複して走らないようキャンセルしておく
    //   (次の動画開始時に instantiateYtBgPlayer/injectEmbedIframe が再度開始する)
    cancelAutoSwitchTimer();
    try { skipToNext(); } catch(e) {}
  }
}

function onPlayerReady(ev) {
  var p = ev.target;
  bindYtControlsOnce();
  bindAudioUnlockOnce();
  safeBgRotate = 0;
  try {
    // ★ [v36-BUG1] ユーザーが音を出したい意思(__wantAudio)を表明済みなら、
    //   プレイヤーが ready になった今こそアンミュートを適用する。これにより
    //   「起動直後にスライダーを触ったが当時は未readyで効かなかった」ケースでも
    //   最初の動画にちゃんと音が戻る。意思が無ければミュートのまま自動再生。
    if (__wantAudio) {
      syncVolumeFromSlider();
    } else {
      try { if (p.mute) p.mute(); } catch (e0) {}
    }
    if (ytHandoffStartPaused) {
      ytHandoffStartPaused = false;
      if (p.pauseVideo) p.pauseVideo();
    } else {
      if (p.playVideo) p.playVideo();
    }
    updateYtTransportUi();
  } catch (e) {}
}

function instantiateYtBgPlayer(videoId, startSeconds) {
  if (!window.YT || !YT.Player) return;
  __skipChainCount = 0; // 実際に動画が再生開始したのでSKIP連鎖カウンタをリセット
  var vid = videoId || DEFAULT_BG_VIDEO_ID;
  var ss  = parseInt(startSeconds, 10) || 0;
  // ★ [v20] この動画のストックURL由来の開始秒数を記録（時間指定が無ければ0=頭出し）。
  //   NOW PLAYINGクリック時のYouTube遷移先 ?t= 判定に使用する。
  currentStockVideoId      = vid;
  currentStockStartSeconds = ss;

  // ★ [v33] 新しい動画の再生開始 → 自動切り替えタイマーをリスタート
  startAutoSwitchTimer();



  // ★ BUG FIX: プレイヤーが既に存在する場合は destroy せず loadVideoById で差し替え
  //   → DOM破壊＆再生成によるモバイルでのバッファリング再起動を防止
  if (ytBgPlayer) {
    try {
      if (ss > 0) {
        ytBgPlayer.loadVideoById({ videoId: vid, startSeconds: ss });
      } else {
        ytBgPlayer.loadVideoById(vid);
      }
    } catch(e) {}
    return;
  }

  var host = document.getElementById("yt-bg-player-host");
  if (!host) return;
  // ★ [v36-BUG1] 未表明(最初)は mute=1 で確実に自動再生。ユーザーが音を出したい
  //   意思(__wantAudio)を表明済みなら、2本目以降は最初から mute無しで生成する。
  bindAudioUnlockOnce();
  var pVars = {
    autoplay: 1,
    mute: __wantAudio ? 0 : 1,
    controls: 0,
    rel: 0,
    modestbranding: 1,
    playsinline: 1,
    enablejsapi: 1,
    iv_load_policy: 3,
    // ★ BUG FIX: vq:'hd2160' を削除 → YouTube の ABR(自動ビットレート適応)に委ねる
    //   スマホ環境で4K強制指定するとWifi良好でもバッファリング途切れが発生するため
    cc_load_policy: 0
  };
  if (ss > 0) pVars.start = ss;
  ytBgPlayer = new YT.Player("yt-bg-player-host", {
    videoId: vid,
    width: "100%",
    height: "100%",
    playerVars: pVars,
    events: {
      onReady: onPlayerReady,
      onStateChange: onPlayerStateChange,
      onError: onPlayerError
    }
  });
}

function createYtBgPlayer() {
  if (!window.YT || !YT.Player) return;
  if (ytBgPlayer) return;
  var host = document.getElementById("yt-bg-player-host");
  if (!host) return;
  // ★ [BUGFIX] 操作系(STOP/PLAY/LOOP ボタン・画面タップ一時停止)のバインドは
  //   従来 onPlayerReady() 内でのみ実行していた。しかし起動時の最初の1本が
  //   YouTube Shorts の場合は YT.Player ではなく素の <iframe> を直埋め込みするため
  //   onPlayerReady() が発火せず、STOPボタンも画面タップも無反応だった。
  //   → プレイヤー生成に依存せず、API ready 時点で一度だけ確実にバインドする。
  //   (bindYtControlsOnce は ytControlsBound ガードで多重実行を防止済み)
  bindYtControlsOnce();
  var ho = consumeYtHandoffHash();
  if (ho && applyYtHandoffParams(ho)) {
    // 他ページから引き継いだ場合
    instantiateYtBgPlayer(ho.v);
    return;
  }
  // ★ 初回起動: イントロ再生シーケンスを開始
  //   (英語GT→日本語GT×2→Cancer Therapy1全→Cancer Therapy2全→以後 全LIST連続)
  startIntroSequence();
}

function initYtBgPlayer() {
  if (!window.YT || !window.YT.Player) return;
  createYtBgPlayer();
}

window.onYouTubeIframeAPIReady = initYtBgPlayer;

(function scheduleYtBgInit() {
  function tryInit() {
    if (window.YT && window.YT.Player) {
      initYtBgPlayer();
      return true;
    }
    return false;
  }
  if (tryInit()) return;
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", function() { tryInit(); }, false);
  }
  var n = 0;
  var tick = setInterval(function() {
    n++;
    if (tryInit() || n > 300) clearInterval(tick);
  }, 50);
})();
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", function() { bindYtCardHandoffOnce(); buildSeriesBtnsEarly(); }, false);
} else {
  bindYtCardHandoffOnce(); buildSeriesBtnsEarly();
}

// ★ [v15] 外部動画ページ(YouTube等)からブラウザバックで戻ってきた場合の自動処理
// sessionStorage.__ytReturnMode=1 がセットされていたら:
//   → パネルを開く → 非背景モードなら次の動画URLへ自動遷移を繰り返す
window.addEventListener('pageshow', function(ev) {
  var flag;
  try { flag = sessionStorage.getItem('__ytReturnMode'); } catch(e) { flag = null; }
  if (!flag) return;
  try { sessionStorage.removeItem('__ytReturnMode'); } catch(e){}
  // パネルを開く
  var volWrap = document.getElementById('ytBgVolumeWrap');
  var openBtn = document.getElementById('ytPanelOpen');
  if (volWrap) volWrap.style.display = '';
  if (openBtn) openBtn.style.display = 'none';
  // 非背景モードなら次の動画へ自動遷移
  if (!ytBgPlayInBackground) {
    setTimeout(function() {
      try { skipToNext(); } catch(e) {}
    }, 600);
  }
}, false);
})();
</script>



<!-- ========== ロゴ描画 & YouTube背景 JS (index.html_20260421から復元) ========== -->
<script>
// ========================
// /top制御（統合版: index.phpは常にルート扱い）
// ========================
const isTop = false;

if(isTop){
 document.getElementById("logoWrap")?.remove();
 document.getElementById("logoModeBar")?.remove();
}

// ========================
// GIF
// ========================
const domain = location.hostname.replace("www","");
const logoImg = document.getElementById("logoGif");
if(logoImg){
 logoImg.src = `https://${domain}/images/logo.gif`;
}

// ========================
// 3D パーティクルロゴ + YouTube音楽連動
// ========================
if(!isTop){

// --- モードボタン ---
let logoMode = 'scroll';
let _triggerExplodeFn = null; // canvas初期化後にセット
let _resetCycleFn    = null; // canvas初期化後にセット

document.querySelectorAll('.logo-mode-btn').forEach(btn=>{
  btn.addEventListener('click',()=>{
    document.querySelectorAll('.logo-mode-btn').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
    logoMode = btn.dataset.mode;
    if(_resetCycleFn) _resetCycleFn();           // サイクルリセット
    if(logoMode==='explode' && _triggerExplodeFn) _triggerExplodeFn();
  });
});

// --- Dance Fixed ジャンル選択ボタン ---
window.danceFixedGenre = 'kabuki'; // デフォルト
document.querySelectorAll('.dance-fixed-btn').forEach(btn=>{
  btn.addEventListener('click',()=>{
    document.querySelectorAll('.dance-fixed-btn').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
    window.danceFixedGenre = btn.dataset.genre;
    // Dance Fixed モードでなければ自動で切替
    if(logoMode !== 'danceFixed'){
      const fixedBtn = document.querySelector('.logo-mode-btn[data-mode="danceFixed"]');
      if(fixedBtn) fixedBtn.click();
    }
  });
});

// --- YouTube音量を周期的にポーリングして擬似音圧を取得 ---
let ytEnergy = 0; // 0~1
let ytPlaying = false;
(function ytPoll(){
  try {
    if(window.ytBgPlayer && ytBgPlayer.getPlayerState){
      const st = ytBgPlayer.getPlayerState();
      ytPlaying = (st === 1);
      if(ytPlaying){
        const vol = ytBgPlayer.getVolume ? ytBgPlayer.getVolume() : 50;
        // 音量を基本エネルギーとして使い、周期的な変動を加える
        const base = vol / 100;
        const t = performance.now() / 1000;
        // 複数のサイン波を重ねてリアルな音楽っぽい波形を擬似生成
        const wave =
          0.4 * Math.abs(Math.sin(t * 2.3)) +
          0.25 * Math.abs(Math.sin(t * 5.7)) +
          0.2 * Math.abs(Math.sin(t * 11.1)) +
          0.15 * Math.abs(Math.sin(t * 0.8));
        ytEnergy = base > 0.01 ? Math.min(1, wave * base * 1.6) : 0;
      } else {
        ytEnergy *= 0.92;
      }
    }
  } catch(e) {}
  setTimeout(ytPoll, 80);
})();

// --- Canvas ロゴ ---
const canvas = document.getElementById('logoCanvas');
if(canvas){
  const ctx = canvas.getContext('2d');
  const TEXT = 'audiocafe.tokyo';
  const GRAD_SPEED = 18;

  // =============================================
  // 解像度対応リサイズ（最大4K / 30FPS 相当）
  // =============================================
  let CW = 1, CH = 1;
  function resize(){
    const dpr = Math.min(window.devicePixelRatio || 1, 4); // 最大4x = 4K/8K対応
    CW = canvas.offsetWidth;
    CH = canvas.offsetHeight;
    canvas.width  = Math.round(CW * dpr);
    canvas.height = Math.round(CH * dpr);
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    initParticles();
  }

  // =============================================
  // フォントサイズ計算（共通）
  // =============================================
  function calcFontSize(w, h){
    return Math.min(w * 0.93 / TEXT.length * 1.62, h * 0.70);
  }

  // =============================================
  // 帆船描画（波モード専用）
  // =============================================
  // 船のヨー角（度）: 0=正面, 90=左, -90=右, 180=後ろ
  // ターゲットは ±75° に固定して正面・後ろを避ける
  let shipYawDeg = 75; // 初期は左向き

  function drawSailingShip(ctx, now, W, H, amp, yawDeg){
    // 船のX座標: ロゴ左端 から "audiocafe" の末尾（ドットの直前）までの中央
    const leftPad = 8;
    const fs = calcFontSize(W, H);
    ctx.save();
    ctx.font = `900 ${fs}px 'Segoe UI',system-ui,sans-serif`;
    const widthBeforeDot = ctx.measureText('audiocafe').width;
    ctx.restore();
    const shipCX = leftPad + widthBeforeDot / 2;

    const shipScale = Math.min(W, H) * 0.18;
    const cy = H * 0.28;

    // 揺れ
    const bobY = Math.sin(now * 1.1) * 6 * amp + Math.sin(now * 2.3) * 2 * amp;
    const bobX = Math.cos(now * 0.9) * 8 * amp + Math.cos(now * 1.8) * 3 * amp;
    const roll = (Math.sin(now * 1.1) * 0.12 + Math.sin(now * 2.3) * 0.04) * amp;

    // --- 3D パース計算 ---
    const yaw = yawDeg * Math.PI / 180; // ラジアン
    const cosY = Math.cos(yaw);  // -1〜1: 横幅スケール
    const sinY = Math.sin(yaw);  // -1〜1: 奥行き方向 (正=左向き)

    // 船体の横幅係数（cosYで透視変形）
    const hullW = 55;           // 最大半幅
    const hullWScaled = hullW * Math.abs(cosY); // 正面向きで細くなる

    // 帆の奥行き方向（sinYで手前/奥の帆の見え方を変える）
    const sailDepth = sinY;     // 1=左向き時に帆が手前, -1=右向き時

    ctx.save();
    ctx.translate(shipCX + bobX, cy + bobY);
    ctx.rotate(roll);
    ctx.scale(shipScale / 100, shipScale / 100);

    const sv = ctx.shadowBlur;

    // --- 船体（hull）3D ---
    // 船底のカーブを cosY で横伸縮
    ctx.beginPath();
    ctx.moveTo(-hullWScaled, 0);
    ctx.bezierCurveTo(-hullWScaled - 5 * Math.abs(cosY), 8,
                      -hullWScaled * 0.7, 22, 0, 24);
    ctx.bezierCurveTo( hullWScaled * 0.7, 22,
                       hullWScaled + 5 * Math.abs(cosY), 8,
                       hullWScaled, 0);
    ctx.closePath();
    // 左向き/右向きで光の当たり方を変える
    const hullLight = cosY > 0 ? '#9a5c22' : '#5c3010';
    ctx.fillStyle = hullLight;
    ctx.shadowColor = 'rgba(0,0,0,0.55)';
    ctx.shadowBlur = 10;
    ctx.fill();
    ctx.strokeStyle = '#3a1a05';
    ctx.lineWidth = 1.8;
    ctx.stroke();

    // 船体側面の陰影ライン（3D感）
    if(Math.abs(cosY) > 0.15){
      ctx.beginPath();
      ctx.moveTo(-hullWScaled, 0);
      ctx.lineTo( hullWScaled, 0);
      ctx.strokeStyle = cosY > 0 ? '#c8803a' : '#7a4020';
      ctx.lineWidth = 2.5;
      ctx.shadowBlur = 0;
      ctx.stroke();
    }

    // 船首の飾り（バウスプリット）- 向きに応じて左右に
    const bowDir = cosY >= 0 ? -1 : 1;
    ctx.beginPath();
    ctx.moveTo(bowDir * hullWScaled, -2);
    ctx.lineTo(bowDir * (hullWScaled + 18), -12);
    ctx.strokeStyle = '#8b5e2a';
    ctx.lineWidth = 2.5;
    ctx.shadowBlur = 0;
    ctx.stroke();

    // --- マスト（3D: 位置をsinYで微妙にずらす）---
    const mastX = sinY * 6; // 奥行きで少しずれて見える
    ctx.beginPath();
    ctx.moveTo(mastX, 2);
    ctx.lineTo(mastX, -80);
    ctx.strokeStyle = '#8b5e2a';
    ctx.lineWidth = 3.5;
    ctx.shadowBlur = 0;
    ctx.stroke();

    // --- ヤード（横桁）---
    const yardW = 36 * Math.abs(cosY) + 8; // 正面向きでも少し見える
    ctx.beginPath();
    ctx.moveTo(mastX - yardW, -40);
    ctx.lineTo(mastX + yardW, -40);
    ctx.strokeStyle = '#7a5020';
    ctx.lineWidth = 2.5;
    ctx.stroke();

    // --- メインセイル（3D: cosYで幅、sailDepthで奥行きカーブ）---
    const sailBow = Math.sin(now * 1.4) * 7 * amp * Math.abs(cosY);
    const sailW = yardW * 0.95;
    const sailCurve = sailDepth * 18 + sailBow; // 手前/奥への膨らみ

    ctx.beginPath();
    ctx.moveTo(mastX - sailW, -38);
    ctx.bezierCurveTo(mastX - sailW * 0.5 + sailCurve, -22,
                      mastX - sailW * 0.3 + sailCurve, -10,
                      mastX - sailW * 0.2, 2);
    ctx.lineTo(mastX + sailW * 0.2, 2);
    ctx.bezierCurveTo(mastX + sailW * 0.3 + sailCurve, -10,
                      mastX + sailW * 0.5 + sailCurve, -22,
                      mastX + sailW, -38);
    ctx.closePath();

    // 光の当たり方で帆の色を変える
    const sailBrightness = 0.6 + 0.4 * Math.abs(cosY);
    const sg = ctx.createLinearGradient(mastX - sailW, -38, mastX + sailW, 2);
    sg.addColorStop(0, `rgba(${Math.round(255*sailBrightness)},${Math.round(250*sailBrightness)},${Math.round(230*sailBrightness)},0.95)`);
    sg.addColorStop(1, `rgba(${Math.round(220*sailBrightness)},${Math.round(205*sailBrightness)},${Math.round(175*sailBrightness)},0.90)`);
    ctx.fillStyle = sg;
    ctx.shadowColor = 'rgba(100,150,255,0.25)';
    ctx.shadowBlur = 5;
    ctx.fill();
    ctx.strokeStyle = 'rgba(160,140,100,0.7)';
    ctx.lineWidth = 1.2;
    ctx.stroke();

    // --- トップセイル ---
    const tsW = 18 * Math.abs(cosY) + 4;
    const tsBow = Math.sin(now * 1.8 + 0.5) * 5 * amp * Math.abs(cosY);
    const tsCurve = sailDepth * 10 + tsBow;
    ctx.beginPath();
    ctx.moveTo(mastX - tsW, -42);
    ctx.bezierCurveTo(mastX - tsW * 0.5 + tsCurve, -58,
                      mastX + tsCurve * 0.5, -68,
                      mastX, -78);
    ctx.bezierCurveTo(mastX + tsCurve * 0.5, -68,
                      mastX + tsW * 0.5 + tsCurve, -58,
                      mastX + tsW, -42);
    ctx.closePath();
    ctx.fillStyle = `rgba(${Math.round(245*sailBrightness)},${Math.round(240*sailBrightness)},${Math.round(220*sailBrightness)},0.88)`;
    ctx.shadowBlur = 3;
    ctx.fill();
    ctx.strokeStyle = 'rgba(160,140,100,0.6)';
    ctx.lineWidth = 1.0;
    ctx.stroke();

    // --- 旗（マスト頂上）---
    const flagDir = cosY >= 0 ? 1 : -1;
    const flagWave = Math.sin(now * 3.5) * 5 * amp;
    ctx.beginPath();
    ctx.moveTo(mastX, -80);
    ctx.lineTo(mastX + flagDir * (16 + flagWave), -72);
    ctx.lineTo(mastX + flagDir * (14 + flagWave * 0.5), -66);
    ctx.lineTo(mastX, -72);
    ctx.closePath();
    ctx.fillStyle = '#ef4444';
    ctx.shadowColor = 'rgba(239,68,68,0.7)';
    ctx.shadowBlur = 6;
    ctx.fill();

    // --- ロープ ---
    ctx.beginPath();
    ctx.moveTo(mastX - sailW, -38);
    ctx.lineTo(-hullWScaled, 2);
    ctx.moveTo(mastX + sailW, -38);
    ctx.lineTo( hullWScaled, 2);
    ctx.strokeStyle = 'rgba(140,100,50,0.55)';
    ctx.lineWidth = 1.0;
    ctx.shadowBlur = 0;
    ctx.stroke();

    ctx.shadowBlur = sv;
    ctx.restore();
  }

  // =============================================
  // スクロール・波モード: Canvas2D テキスト直接描画
  // =============================================
  function drawTextGradient(now, waveOffset){
    ctx.clearRect(0, 0, CW, CH);
    const fs = calcFontSize(CW, CH);
    ctx.font = `900 ${fs}px 'Segoe UI',system-ui,sans-serif`;
    ctx.textAlign = 'left';
    ctx.textBaseline = 'middle';

    const textW = ctx.measureText(TEXT).width;
    const leftPad = 8;
    const baseY = CH / 2;

    // 音量連動: グローと微pulseで反応
    const e = (typeof ytEnergy === 'number') ? ytEnergy : 0;
    const pulse = 1 + e * 0.05; // 最大 +5% scale

    // 音量でグラデーションの流速を少し速める
    const speedMult = 1 + e * 0.8;
    const gradShift = (now * GRAD_SPEED * speedMult / 360 * textW * 1.5) % textW;
    const gx0 = leftPad - gradShift;
    const gx1 = gx0 + textW * 1.5;
    const grad = ctx.createLinearGradient(gx0, 0, gx1, 0);
    const stops = [
      [0,    '#a855f7'],
      [0.08, '#3b82f6'],
      [0.20, '#22d3ee'],
      [0.32, '#22c55e'],
      [0.45, '#facc15'],
      [0.57, '#f97316'],
      [0.68, '#ef4444'],
      [0.80, '#ec4899'],
      [0.90, '#a855f7'],
      [1.00, '#3b82f6'],
    ];
    stops.forEach(([s, c]) => grad.addColorStop(s, c));

    if(waveOffset){
      const chars = TEXT.split('');
      let curX = leftPad;
      chars.forEach((ch) => {
        const chW = ctx.measureText(ch).width;
        const ratio = (curX + chW/2 - leftPad) / textW;
        const dy = waveOffset(ratio, now);
        ctx.save();
        ctx.shadowColor = 'rgba(168,85,247,0.6)';
        ctx.shadowBlur = 18 + e * 18;
        ctx.fillStyle = grad;
        ctx.fillText(ch, curX, baseY + dy);
        ctx.restore();
        curX += chW;
      });
    } else {
      ctx.save();
      ctx.shadowColor = 'rgba(168,85,247,0.5)';
      ctx.shadowBlur = 16 + e * 20;
      ctx.fillStyle = grad;
      ctx.translate(leftPad + textW/2, baseY);
      ctx.scale(pulse, pulse);
      ctx.fillText(TEXT, -textW/2, 0);
      ctx.restore();
    }
  }

  // =============================================
  // 爆発・オービットモード: パーティクル
  // =============================================
  let particles = [];
  let exploding = false;
  let explodeTimer = 0;
  const EXPLODE_HOLD = 90;
  const REFORM_SPEED = 0.025; // ゆっくり戻る

  function getTextPixels(w, h){
    const scale = Math.min(window.devicePixelRatio || 1, 4);
    const ow = Math.round(w * scale);
    const oh = Math.round(h * scale);
    const off = document.createElement('canvas');
    off.width = ow; off.height = oh;
    const ox = off.getContext('2d');
    const fs = calcFontSize(w, h) * scale;
    ox.font = `900 ${fs}px 'Segoe UI',system-ui,sans-serif`;
    ox.fillStyle = '#fff';
    ox.textAlign = 'left';
    ox.textBaseline = 'middle';
    ox.fillText(TEXT, 8 * scale, oh / 2);
    const d = ox.getImageData(0, 0, ow, oh).data;
    const pts = [];
    const step = Math.max(2, Math.round(scale * 2.5));
    for(let y = 0; y < oh; y += step){
      for(let x = 0; x < ow; x += step){
        if(d[(y * ow + x) * 4 + 3] > 128){
          pts.push({ x: x / scale, y: y / scale });
        }
      }
    }
    return pts;
  }

  function initParticles(){
    const W = CW, H = CH;
    if(!W || !H) return;
    const pts = getTextPixels(W, H);
    if(!pts.length) return;
    particles = pts.map(p => ({
      tx: p.x, ty: p.y,
      x: p.x, y: p.y,   // 最初から元位置に配置（ちらつき防止）
      vx: 0, vy: 0,
      ex: (Math.random()-0.5)*18,
      ey: (Math.random()-0.5)*14 - 6,
      size: Math.random()*1.6+1.0,
      phase:  Math.random()*Math.PI*2,
      phaseY: Math.random()*Math.PI*2,
    }));
    exploding = false;
    explodeTimer = 0;
  }

  function particleColor(p, now, W){
    const hue = (p.tx / W * 360 + now * GRAD_SPEED) % 360;
    return `hsl(${hue},100%,62%)`;
  }

  function triggerExplode(){
    exploding = true;
    explodeTimer = 0;
    particles.forEach(p => {
      const angle = Math.atan2(p.ty - CH/2, p.tx - CW/2);
      const speed = 8 + Math.random()*16;
      p.ex = Math.cos(angle)*speed + (Math.random()-0.5)*10;
      p.ey = Math.sin(angle)*speed + (Math.random()-0.5)*10;
    });
  }

  // =============================================
  // サイクル管理
  // EFFECT_SEC: アクション時間
  // REST_SEC:   静止（元ロゴ表示）時間 ← 2倍にして10秒
  // =============================================
  const EFFECT_SEC = 5.0;
  const REST_SEC   = 10.0; // 5秒→10秒（2倍）
  let cycleStart = performance.now() / 1000;
  let cycleSubPhase = 'rest'; // 最初は静止から始める

  function updateCycle(now, mode){
    const elapsed = now - cycleStart;
    if(cycleSubPhase === 'effect' && elapsed >= EFFECT_SEC){
      cycleSubPhase = 'rest'; cycleStart = now;
    } else if(cycleSubPhase === 'rest' && elapsed >= REST_SEC){
      cycleSubPhase = 'effect'; cycleStart = now;
      if(mode === 'explode') triggerExplode();
    }
  }

  const resetCycle = () => {
    cycleStart = performance.now() / 1000;
    // explodeは即爆発させるためeffectから、wave/orbitもeffectから開始
    cycleSubPhase = 'effect';
  };

  // 船のヨー角（度）: 75=左向き, -75=右向き（正面0°/後ろ180°を避ける）

  // =============================================
  // Dance ヘルパー: ジャンル定義 + 振り付け + AI判定
  // 全ジャンル: 歌舞伎、エジプト、インド、HIPHOP、K-POP、ラテン、オーケストラ、JAZZ、民族、フリースタイル
  // =============================================

  // ジャンル表示名
  const DANCE_GENRE_NAMES = {
    kabuki:    '🎭 歌舞伎',
    egypt:     '🏛 エジプト',
    india:     '🕉 インド',
    hiphop:    '🎤 HIPHOP',
    kpop:      '💖 K-POP',
    latin:     '💃 ラテン',
    orchestra: '🎻 オーケストラ',
    jazz:      '🎷 JAZZ',
    ethnic:    '🪘 民族',
    freestyle: '✨ フリースタイル',
  };

  // ジャンル色パレット
  const DANCE_GENRE_COLORS = {
    kabuki:    ['#d71b3b', '#1a1a1a', '#ffd700', '#ffffff'],
    egypt:     ['#ffd700', '#d4a017', '#3fb6bc', '#8b4513'],
    india:     ['#ff6f00', '#ff9933', '#ec407a', '#138808'],
    hiphop:    ['#ff0080', '#8000ff', '#00ffff', '#ffff00'],
    kpop:      ['#ff6b9d', '#c471ed', '#12c2e9', '#f7797d'],
    latin:     ['#ff0040', '#ff8c00', '#ffd700', '#ff1493'],
    orchestra: ['#4b0082', '#9370db', '#daa520', '#f5f5dc'],
    jazz:      ['#2e1a47', '#6a0dad', '#d4af37', '#4169e1'],
    ethnic:    ['#8b4513', '#cd853f', '#d2691e', '#ff4500'],
    freestyle: ['#00ffff', '#ff00ff', '#ffff00', '#00ff00'],
  };

  // 1文字分のダンス振り付けを返す (dx, dy, rot, scale)
  function danceMotionForChar(genre, now, i, ratio, beat){
    let dx = 0, dy = 0, rot = 0, scale = 1;

    if(genre === 'kabuki'){
      // 歌舞伎: 見得を切るポーズ。ゆったり大きな動き→止まる
      const pose = Math.floor(now * 0.5) % 4;
      const poseT = (now * 0.5) % 1;
      const freeze = poseT < 0.3 ? 1 : poseT > 0.7 ? 1 : Math.sin((poseT - 0.3) * Math.PI / 0.4);
      const tilt = [0, 0.15, -0.12, 0.2][pose];
      dy = Math.sin(ratio * Math.PI) * 10 * (1 - freeze) + beat * 6;
      rot = tilt * (1 - freeze * 0.7);
      scale = 1 + beat * 0.08;
    }
    else if(genre === 'egypt'){
      // エジプト: 角張った横スライド、ヒエログリフ風
      const step = Math.floor(now * 3);
      const stepT = (now * 3) % 1;
      dx = (step % 2 === 0 ? 1 : -1) * (8 + beat * 8) * Math.pow(stepT, 2);
      dy = -Math.abs(Math.sin(now * Math.PI * 2 + i * 0.5)) * (6 + beat * 6);
      rot = 0;
      scale = 1 + beat * 0.07;
    }
    else if(genre === 'india'){
      // インド: 首ワッブル + 螺旋
      const wobble = Math.sin(now * 3 + i * 0.3) * (12 + beat * 10);
      dx = wobble;
      dy = Math.cos(now * 2.5 + i * 0.2) * (8 + beat * 8);
      rot = Math.sin(now * 3) * 0.1;
      scale = 1 + Math.sin(now * 4 + i) * 0.08 + beat * 0.08;
    }
    else if(genre === 'hiphop'){
      // HIPHOP: 激しいバウンス、ポッピング
      const bounce = Math.pow(Math.abs(Math.sin(now * Math.PI * 3 + i * 0.4)), 3);
      dy = -bounce * (18 + beat * 20);
      dx = Math.sin(now * 8 + i) * (3 + beat * 6);
      rot = (Math.random() < 0.05 ? (Math.random() - 0.5) * 0.3 : 0) * (0.5 + beat);
      scale = 1 + bounce * 0.15 + beat * 0.12;
    }
    else if(genre === 'kpop'){
      // K-POP: シンクロしたウェーブ
      const wave = Math.sin(ratio * Math.PI * 2 - now * 3.5);
      dy = wave * (14 + beat * 14);
      dx = Math.cos(ratio * Math.PI * 2 - now * 3.5) * (4 + beat * 4);
      rot = wave * 0.08;
      scale = 1 + Math.abs(wave) * 0.06 + beat * 0.08;
    }
    else if(genre === 'latin'){
      // ラテン: サルサの腰振り + 情熱的な回転
      const hip = Math.sin(now * Math.PI * 2.5) * (12 + beat * 10);
      dx = hip + Math.sin(now * 4 + i * 0.3) * 3;
      dy = Math.abs(Math.sin(now * 5)) * -(6 + beat * 6) + beat * 6;
      rot = Math.sin(now * 2.5) * 0.15;
      scale = 1 + beat * 0.12;
    }
    else if(genre === 'orchestra'){
      // オーケストラ: 指揮者のタクトのような、ゆったり優雅な上下動
      const swing = Math.sin(now * 1.2 + ratio * Math.PI);
      dy = swing * (10 + beat * 16);
      dx = Math.cos(now * 0.8 + i * 0.15) * (2 + beat * 4);
      rot = Math.sin(now * 0.6) * 0.04;
      scale = 1 + Math.abs(swing) * 0.04 + beat * 0.1;
    }
    else if(genre === 'jazz'){
      // JAZZ: スイング、シンコペーション、斜めの動き
      const swing = Math.sin(now * Math.PI * 1.8 + i * 0.3);
      const shuffle = ((now * 2) % 1) < 0.67 ? 1 : 0.5; // スイング比 2:1
      dx = swing * (6 + beat * 6) * shuffle;
      dy = Math.cos(now * 2.4 + i * 0.4) * (8 + beat * 6) * shuffle;
      rot = swing * 0.06;
      scale = 1 + beat * 0.09;
    }
    else if(genre === 'ethnic'){
      // 民族: 伝統的な円環運動、大地を踏むような力強い下動
      const phase = now * 1.8 + i * 0.35;
      dx = Math.cos(phase) * (5 + beat * 6);
      dy = Math.sin(phase * 2) * (4 + beat * 4) + Math.abs(Math.sin(now * 3)) * (4 + beat * 8);
      rot = Math.sin(phase) * 0.05;
      scale = 1 + beat * 0.1;
    }
    else if(genre === 'freestyle'){
      // フリースタイル: 全部混ぜた即興。文字ごとに違う動き
      const r1 = Math.sin(now * (2 + i * 0.2) + i);
      const r2 = Math.cos(now * (3 + i * 0.15));
      dx = r1 * (10 + beat * 10);
      dy = r2 * (12 + beat * 12);
      rot = r1 * r2 * 0.15;
      scale = 1 + Math.abs(r1 * r2) * 0.1 + beat * 0.1;
    }

    return { dx, dy, rot, scale };
  }

  // 1フレーム描画: 任意のジャンル × 任意のfade(0-1) で描く
  function renderDanceFrame(genre, now, energy, fade){
    ctx.clearRect(0, 0, CW, CH);

    // ビート: 音量変化を強めに反映 + 背景BPM(120)もミックス
    const simulatedBeat = 0.5 + 0.5 * Math.sin(now * 2 * Math.PI * 2);
    // 音量が高い時は音量支配、無音時はBPM模擬
    const beat = energy > 0.05
      ? (energy * 0.85 + simulatedBeat * 0.15)
      : simulatedBeat * 0.35;

    const fs = calcFontSize(CW, CH);
    ctx.font = `900 ${fs}px 'Segoe UI',system-ui,sans-serif`;
    ctx.textAlign = 'left';
    ctx.textBaseline = 'middle';
    const textW = ctx.measureText(TEXT).width;
    const leftPad = 8;
    const baseY = CH / 2;

    const colors = DANCE_GENRE_COLORS[genre] || DANCE_GENRE_COLORS.freestyle;
    const gradShift = (now * (30 + energy * 40)) % textW;
    const grad = ctx.createLinearGradient(leftPad - gradShift, 0, leftPad - gradShift + textW, 0);
    colors.forEach((c, i) => grad.addColorStop(i / (colors.length - 1), c));

    ctx.save();
    ctx.globalAlpha = fade;
    ctx.shadowColor = colors[0];
    ctx.shadowBlur = 18 + beat * 22;

    const chars = TEXT.split('');
    let curX = leftPad;
    chars.forEach((ch, i) => {
      const chW = ctx.measureText(ch).width;
      const ratio = (curX + chW/2 - leftPad) / textW;
      const m = danceMotionForChar(genre, now, i, ratio, beat);
      ctx.save();
      ctx.translate(curX + chW/2 + m.dx, baseY + m.dy);
      ctx.rotate(m.rot);
      ctx.scale(m.scale, m.scale);
      ctx.fillStyle = grad;
      ctx.textAlign = 'center';
      ctx.fillText(ch, 0, 0);
      ctx.restore();
      curX += chW;
    });

    ctx.restore();

    // ジャンル名を左上に小さく表示
    ctx.save();
    ctx.globalAlpha = fade * 0.75;
    ctx.font = `700 ${Math.max(14, fs * 0.18)}px 'Segoe UI',system-ui,sans-serif`;
    ctx.textAlign = 'left';
    ctx.textBaseline = 'top';
    ctx.fillStyle = colors[0];
    ctx.shadowColor = 'rgba(0,0,0,0.5)';
    ctx.shadowBlur = 8;
    ctx.fillText(DANCE_GENRE_NAMES[genre] || genre, 12, 10);
    ctx.restore();
  }

  // AI風ジャンル判定: 音量のアップダウンパターンだけで推定
  // 履歴を記録して変化パターンから推測
  const _energyHistory = [];
  const _ENERGY_HIST_MAX = 60; // 約2秒分 (30fps想定)

  function detectGenreFromEnergy(now, energy){
    // 音量履歴を蓄積
    _energyHistory.push({ t: now, e: energy });
    while(_energyHistory.length > _ENERGY_HIST_MAX){ _energyHistory.shift(); }

    // 最低1秒分は溜めないと判定困難
    if(_energyHistory.length < 20){
      return detectGenreFromEnergy._current || 'freestyle';
    }

    // 特徴量を計算
    const values = _energyHistory.map(h => h.e);
    const avg = values.reduce((a, b) => a + b, 0) / values.length;
    const max = Math.max(...values);
    const min = Math.min(...values);
    const range = max - min;
    // 変化率（隣接差分の平均絶対値）
    let diffSum = 0;
    for(let i = 1; i < values.length; i++){ diffSum += Math.abs(values[i] - values[i-1]); }
    const volatility = diffSum / (values.length - 1);
    // ピーク数（極大値）
    let peaks = 0;
    for(let i = 2; i < values.length - 2; i++){
      if(values[i] > values[i-1] && values[i] > values[i+1] && values[i] > avg * 1.15) peaks++;
    }

    // === ジャンル推定ルール ===
    // volatility: 微妙 <0.02, 中 0.02-0.05, 大 >0.05
    // avg: 静か <0.15, 中 0.15-0.35, 大 >0.35
    // peaks: 少 <3, 中 3-6, 多 >6
    let genre;

    if(avg < 0.05){
      // ほぼ無音 → フリースタイル or JAZZ
      genre = peaks > 0 ? 'jazz' : 'freestyle';
    }
    else if(volatility > 0.06 && peaks > 5){
      // 激しく上下動 → HIPHOP
      genre = 'hiphop';
    }
    else if(volatility > 0.04 && avg > 0.25){
      // 中程度の上下動 + 大音量 → ラテン or K-POP
      genre = peaks > 4 ? 'latin' : 'kpop';
    }
    else if(volatility < 0.02 && avg > 0.3){
      // 一定して高い音量 → オーケストラ
      genre = 'orchestra';
    }
    else if(volatility < 0.015){
      // 非常に滑らか → JAZZ
      genre = 'jazz';
    }
    else if(range > 0.35){
      // ダイナミックレンジ広い → 歌舞伎(静→動) or 民族
      genre = peaks > 3 ? 'ethnic' : 'kabuki';
    }
    else if(peaks > 4){
      // リズミカル → インド
      genre = 'india';
    }
    else if(avg < 0.12){
      // 控えめ → エジプト
      genre = 'egypt';
    }
    else {
      // デフォルト → K-POP
      genre = 'kpop';
    }

    // ヒステリシス: 前回と違うジャンルに変わる場合、直近の判定と違っていたら更新
    // 頻繁な切替を避けるため最低2秒は維持
    const prev = detectGenreFromEnergy._current;
    const lastSwitch = detectGenreFromEnergy._lastSwitchTime || 0;
    if(prev !== genre && (now - lastSwitch) >= 2){
      detectGenreFromEnergy._current = genre;
      detectGenreFromEnergy._lastSwitchTime = now;
    }
    return detectGenreFromEnergy._current || genre;
  }

  // =============================================
  // メインアニメーションループ
  // =============================================
  function animate(){
    requestAnimationFrame(animate);
    if(!CW || !CH) return;

    const energy = ytEnergy;
    const now = performance.now() / 1000;
    const mode = logoMode;

    // ---- スクロールモード ----
    if(mode === 'scroll'){
      drawTextGradient(now, null);
      return;
    }

    // ---- Dance モード (6ジャンル固定ローテーション) ----
    // YouTube音量変化(ytEnergy)をリズム模擬に利用
    // ジャンル: 歌舞伎 → エジプト → インド → HIPHOP → K-POP → ラテン
    if(mode === 'dance'){
      const GENRES = ['kabuki', 'egypt', 'india', 'hiphop', 'kpop', 'latin'];
      const GENRE_DURATION = 8;
      const danceStart = animate._danceStart || (animate._danceStart = now);
      const elapsed = now - danceStart;
      const genreIdx = Math.floor(elapsed / GENRE_DURATION) % GENRES.length;
      const genre = GENRES[genreIdx];
      const phaseInGenre = (elapsed % GENRE_DURATION) / GENRE_DURATION;
      const fade = phaseInGenre < 0.15 ? phaseInGenre / 0.15
                 : phaseInGenre > 0.85 ? (1 - phaseInGenre) / 0.15 : 1;
      renderDanceFrame(genre, now, energy, fade);
      return;
    }

    // ---- Dance Fixed モード (ユーザー選択ジャンルを固定再生) ----
    if(mode === 'danceFixed'){
      const genre = window.danceFixedGenre || 'kabuki';
      renderDanceFrame(genre, now, energy, 1);
      return;
    }

    // ---- Dance AI モード (音量変化パターンから自動判定) ----
    if(mode === 'danceAI'){
      const genre = detectGenreFromEnergy(now, energy);
      // ジャンル切替時のフェード効果
      const switchTime = detectGenreFromEnergy._lastSwitchTime || 0;
      const timeSinceSwitch = now - switchTime;
      const fade = timeSinceSwitch < 0.6 ? Math.min(1, timeSinceSwitch / 0.6) : 1;
      renderDanceFrame(genre, now, energy, fade);
      return;
    }

    // ---- 波モード ----
    if(mode === 'wave'){
      // サイクルなし — 他のボタンを押すまで常に波アニメーション継続
      ctx.clearRect(0, 0, CW, CH);

      const waveAmpX = 22 + energy * 28;

      // === ロゴテキスト：各文字をY方向に波打たせ、波が右→左・左→右と流れる ===
      const fs = calcFontSize(CW, CH);
      ctx.font = `900 ${fs}px 'Segoe UI',system-ui,sans-serif`;
      ctx.textAlign = 'left';
      ctx.textBaseline = 'middle';
      const textW = ctx.measureText(TEXT).width;
      const leftPad = 8;
      const baseY = CH * 0.68;

      // スクロールグラデーション
      const gradShift = (now * GRAD_SPEED / 360 * textW * 1.5) % textW;
      const gx0 = leftPad - gradShift;
      const gx1 = gx0 + textW * 1.5;
      const grad = ctx.createLinearGradient(gx0, 0, gx1, 0);
      const stops = [
        [0,    '#a855f7'],[0.08,'#3b82f6'],[0.20,'#22d3ee'],
        [0.32, '#22c55e'],[0.45,'#facc15'],[0.57,'#f97316'],
        [0.68, '#ef4444'],[0.80,'#ec4899'],[0.90,'#a855f7'],[1.00,'#3b82f6'],
      ];
      stops.forEach(([s,c]) => grad.addColorStop(s,c));

      // 主波: sin(ratio*2.5π − now*2.8) → 右から左へ進む
      // 副波: sin(ratio*4.5π + now*1.9) → 左から右へ進む
      // 主波が支配的（0.7 vs 0.3）なので「右から左」が基調
      const chars = TEXT.split('');
      let curX = leftPad;
      chars.forEach((ch) => {
        const chW = ctx.measureText(ch).width;
        const ratio = (curX + chW/2 - leftPad) / textW;
        const dy = Math.sin(ratio * Math.PI * 2.5 - now * 2.8) * waveAmpX * 0.7
                 + Math.sin(ratio * Math.PI * 4.5 + now * 1.9) * waveAmpX * 0.3;
        ctx.save();
        ctx.shadowColor = 'rgba(168,85,247,0.55)';
        ctx.shadowBlur = 16;
        ctx.fillStyle = grad;
        ctx.fillText(ch, curX, baseY + dy);
        ctx.restore();
        curX += chW;
      });

      // === 波の進行方向で船のヨー角をゆっくり切り替える ===
      const ratio05 = 0.5;
      const v1 = -2.8 * Math.cos(ratio05 * Math.PI * 2.5 - now * 2.8) * waveAmpX * 0.7;
      const v2 =  1.9 * Math.cos(ratio05 * Math.PI * 4.5 + now * 1.9) * waveAmpX * 0.3;
      const netV = v1 + v2;
      // 目標ヨー角: 左向き=75°、右向き=-75° （正面0°/後ろ180° を避ける）
      const targetYaw = netV < 0 ? 75 : -75;
      // lerp: 0.001 ≒ 約1000フレーム(約16秒)かけて切り替わる（前回の4倍以上遅い）
      shipYawDeg += (targetYaw - shipYawDeg) * 0.001;

      drawSailingShip(ctx, now, CW, CH, 1, shipYawDeg);

      return;
    }

    // ---- パーティクルモード（爆発・オービット）----
    if(!particles.length) return;
    updateCycle(now, mode);
    const isResting = cycleSubPhase === 'rest';
    const restT = isResting ? Math.min(1,(now-cycleStart)/REST_SEC) : 0;
    // 収束スプリング: REST_SEC の前半4割でゆっくり戻り、残りは静止維持
    const returnProgress = Math.min(1, restT / 0.4);
    const spring = isResting
      ? REFORM_SPEED + returnProgress * REFORM_SPEED * 2
      : REFORM_SPEED;

    ctx.clearRect(0, 0, CW, CH);

    if(mode === 'explode'){
      if(isResting){
        particles.forEach(p => {
          p.vx = p.vx*0.80 + (p.tx-p.x)*spring;
          p.vy = p.vy*0.80 + (p.ty-p.y)*spring;
          p.x += p.vx; p.y += p.vy;
        });
      } else if(exploding){
        explodeTimer++;
        particles.forEach(p => {
          p.x += p.ex*(1+energy*2.5); p.y += p.ey*(1+energy*2.5);
          p.ex *= 0.91; p.ey *= 0.91; p.ey += 0.28;
        });
        if(explodeTimer > EXPLODE_HOLD) exploding = false;
      } else {
        // 爆発後→元位置へ収束
        particles.forEach(p => {
          p.vx = p.vx*0.80 + (p.tx-p.x)*REFORM_SPEED;
          p.vy = p.vy*0.80 + (p.ty-p.y)*REFORM_SPEED;
          p.x += p.vx; p.y += p.vy;
        });
      }

    } else if(mode === 'orbit'){
      // 音量連動: 音量が上がると軌道半径と回転速度が増加
      const orbitAmp = 1 + energy * 1.5;
      const orbitSpeed = 1 + energy * 0.8;
      particles.forEach(p => {
        let tx = p.tx, ty = p.ty;
        if(!isResting){
          // effectフェーズ: 元位置を中心に大きく軌道を描く
          tx += 20 * orbitAmp * Math.cos(now * 1.6 * orbitSpeed + p.phase);
          ty += 14 * orbitAmp * Math.sin(now * 1.6 * orbitSpeed + p.phaseY);
        }
        const sp = isResting ? spring : 0.08;
        const damp = 0.78;
        p.vx = p.vx*damp + (tx-p.x)*sp;
        p.vy = p.vy*damp + (ty-p.y)*sp;
        p.x += p.vx; p.y += p.vy;
      });
    }

    // パーティクル描画
    particles.forEach(p => {
      const col = particleColor(p, now, CW);
      ctx.shadowColor = col;
      ctx.shadowBlur = isResting ? 3 : 7;
      ctx.fillStyle = col;
      ctx.globalAlpha = 0.93;
      ctx.beginPath();
      ctx.arc(p.x, p.y, p.size, 0, Math.PI*2);
      ctx.fill();
      ctx.globalAlpha = 1;
      ctx.shadowBlur = 0;
    });
  }

  window.addEventListener('resize', resize);
  setTimeout(()=>{ resize(); animate(); }, 80);

  _triggerExplodeFn = ()=>{
    exploding = false; explodeTimer = 0; triggerExplode();
    cycleSubPhase = 'effect'; cycleStart = performance.now()/1000;
  };
  _resetCycleFn = resetCycle;
}

} // end if(!isTop)

// ========================
// YouTube操作パネル CLOSE / OPEN
// ========================
(function(){
  var panel    = document.getElementById('ytBgVolumeWrap');
  var closeBtn = document.getElementById('ytPanelClose');
  var openBtn  = document.getElementById('ytPanelOpen');
  if(!panel || !closeBtn || !openBtn) return;
  closeBtn.addEventListener('click', function(){
    panel.style.display = 'none';
    openBtn.style.display = 'block';
  });
  openBtn.addEventListener('click', function(){
    panel.style.display = '';
    openBtn.style.display = 'none';
    // パネル再表示時にもCLOSEボタンが確実に見えるよう自動短縮を再実行
    if (typeof autoFitSeriesButtons === 'function') {
      setTimeout(autoFitSeriesButtons, 30);
    }
  });
})();
</script>



<style>

/* =========================================================
   MIXLIST FULL COPY VIEW
   言語カード＆/top 要約ページのみ全文コピー表示
========================================================= */

.mixlist-fulltext-copy{
  margin:28px 0;
  padding:22px;
  border-radius:18px;
  background:rgba(0,0,0,0.18);
  line-height:2;
  overflow-wrap:break-word;
}

.mixlist-fulltext-copy h2{
  font-size:2rem;
  margin-bottom:18px;
}

.mixlist-fulltext-copy .full-en,
.mixlist-fulltext-copy .full-ja{
  margin-bottom:30px;
  padding:18px;
  border-radius:14px;
  background:rgba(255,255,255,0.08);
  white-space:pre-wrap;
}

@media (max-width:768px){

  .mixlist-fulltext-copy h2{
    font-size:1.45rem;
  }

}

</style>

<script>
document.addEventListener('DOMContentLoaded', function(){

  const url = new URL(location.href);

  // mixlistページのみ
  if(!url.searchParams.get('mixlist')){
    return;
  }

  // ルートページは対象外
  if(!url.searchParams.get('lang')){
    return;
  }

  // 元のカード群から英語と日本語を取得
  const enBlocks = document.querySelectorAll(
    '.card-en, .lang-en, [data-lang="en"]'
  );

  const jaBlocks = document.querySelectorAll(
    '.card-ja, .lang-ja, [data-lang="ja"]'
  );

  let enText = '';
  let jaText = '';

  enBlocks.forEach(el=>{
    const t = el.innerText.trim();
    if(t.length > 40){
      enText += "\n\n" + t;
    }
  });

  jaBlocks.forEach(el=>{
    const t = el.innerText.trim();
    if(t.length > 20){
      jaText += "\n\n" + t;
    }
  });

  // 引用だけではなく全文コピー表示
  const wrap = document.createElement('div');
  wrap.className = 'mixlist-fulltext-copy';

  wrap.innerHTML = `
    <h2>FULL TEXT COPY VIEW</h2>

    <div class="full-en">
      <h3>English Full Text</h3>
      <div>${escapeHtml(enText)}</div>
    </div>

    <div class="full-ja">
      <h3>日本語全文</h3>
      <div>${escapeHtml(jaText)}</div>
    </div>
  `;

  // main-contentの下へ追加
  const target =
    document.querySelector('.main-content') ||
    document.querySelector('main') ||
    document.body;

  target.appendChild(wrap);

});

function escapeHtml(text){

  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;

}
</script>


<style>

/* =========================================================
   JAPANESE FULLTEXT LEFT PADDING BUG FIX
========================================================= */

.mixlist-fulltext-copy{
  padding-left:28px !important;
  padding-right:28px !important;
  box-sizing:border-box !important;
}

.mixlist-fulltext-copy .full-ja{
  margin-left:18px !important;
  margin-right:18px !important;
  padding-left:26px !important;
  padding-right:26px !important;
  box-sizing:border-box !important;
  width:calc(100% - 36px) !important;
}

.mixlist-fulltext-copy .full-ja div{
  padding-left:10px !important;
  padding-right:10px !important;
}

@media (max-width:768px){

  .mixlist-fulltext-copy{
    padding-left:14px !important;
    padding-right:14px !important;
  }

  .mixlist-fulltext-copy .full-ja{
    margin-left:8px !important;
    margin-right:8px !important;
    padding-left:16px !important;
    padding-right:16px !important;
    width:calc(100% - 16px) !important;
  }

}

</style>

</body>
</html>
<?php exit; endif; ?>
<?php if ($is_translate_mode && $show_loading): ?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Loading... | 読み込み中 | 로딩 중 | <?=htmlspecialchars($lang)?></title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
html,body{height:100%}
body{
  background:linear-gradient(135deg,#001a3d 0%,#003366 50%,#0056b3 100%);
  color:#fff;
  font-family:'Helvetica Neue',Arial,'Hiragino Kaku Gothic ProN',sans-serif;
  display:flex;align-items:center;justify-content:center;
  min-height:100vh;
  padding:2rem 1rem;
}
.loader-wrap{
  text-align:center;
  max-width:500px;
  width:100%;
}
.spinner{
  width:80px;height:80px;
  margin:0 auto 2rem;
  border:6px solid rgba(255,255,255,.2);
  border-top-color:#22d3ee;
  border-radius:50%;
  animation:spin 1s linear infinite;
}
@keyframes spin{to{transform:rotate(360deg)}}
.progress-wrap{
  width:100%;
  max-width:400px;
  margin:1.5rem auto 1rem;
  height:14px;
  background:rgba(255,255,255,.15);
  border-radius:7px;
  overflow:hidden;
  box-shadow:inset 0 2px 4px rgba(0,0,0,.2);
}
.progress-bar{
  height:100%;
  background:linear-gradient(90deg,#22d3ee,#06b6d4);
  border-radius:7px;
  transition:width .3s ease;
  box-shadow:0 0 10px rgba(34,211,238,.5);
}
.msg-block{
  margin:1rem 0;
  line-height:1.7;
}
.msg-ja{font-size:1.1rem;font-weight:600;color:#fff}
.msg-en{font-size:1rem;color:#cbd5e1;margin-top:.3rem}
.msg-other{font-size:1rem;color:#94a3b8;margin-top:.3rem}
.lang-tag{
  display:inline-block;
  font-size:.7rem;
  padding:.15rem .5rem;
  background:rgba(34,211,238,.2);
  color:#22d3ee;
  border-radius:4px;
  margin-right:.4rem;
  font-weight:700;
  letter-spacing:.05em;
}
.progress-text{
  font-size:.85rem;
  color:#94a3b8;
  margin-top:.5rem;
  font-family:ui-monospace,monospace;
}
.title-main{
  font-size:1.4rem;
  font-weight:800;
  margin-bottom:1rem;
  color:#22d3ee;
  letter-spacing:.04em;
}
.note{
  font-size:.75rem;
  color:#64748b;
  margin-top:1.5rem;
  line-height:1.6;
}
</style>
</head>
<body>

<div class="loader-wrap">
  <div class="spinner"></div>

  <div class="title-main">AUDIOCAFE</div>

  <div class="msg-block">
    <div class="msg-ja"><span class="lang-tag">JP</span>初回と24時間以後は、キャッシュが効きませんので、読み込み解析に約30秒間を必要と致します。少々お待ちください。</div>
  </div>

  <div class="msg-block">
    <div class="msg-en"><span class="lang-tag">EN</span>On the first visit and after 24 hours, the cache expires, so it takes about 30 seconds to load and analyze. Please wait a moment.</div>
  </div>

  <div class="msg-block" id="msg-other">
    <!-- 選択言語のメッセージをJSで挿入 -->
  </div>

  <div class="progress-wrap">
    <div class="progress-bar" id="progress-bar" style="width:0%"></div>
  </div>
  <div class="progress-text" id="progress-text">0%</div>

  <div class="note">
    Preparing translated content...<br>
    翻訳コンテンツを準備しています
  </div>
</div>

<script>
// ============================================================
// 選択言語でのメッセージ表示
// MyMemory無料翻訳API (認証不要、1日あたり5000文字まで) を利用
// キャッシュを使って2回目以降は即表示
// ============================================================
(function(){
  var lang = <?=json_encode($lang)?>;
  var sourceText = '初回と24時間以後は、キャッシュが効きませんので、読み込み解析に約30秒間を必要と致します。少々お待ちください。';
  var cacheKey = 'ac_loading_msg_' + lang;
  var el = document.getElementById('msg-other');

  if(!el || !lang || lang === 'ja' || lang === 'en') return;

  function render(translated){
    el.innerHTML = '<div class="msg-other"><span class="lang-tag">' +
      lang.toUpperCase() + '</span>' + translated + '</div>';
  }

  // キャッシュチェック
  var cached = null;
  try { cached = localStorage.getItem(cacheKey); } catch(e){}
  if(cached){
    render(cached);
    return;
  }

  // 仮表示 (翻訳取得中)
  el.innerHTML = '<div class="msg-other"><span class="lang-tag">' +
    lang.toUpperCase() + '</span>...</div>';

  // MyMemory API で翻訳取得
  var apiUrl = 'https://api.mymemory.translated.net/get?q=' +
    encodeURIComponent(sourceText) + '&langpair=ja|' + encodeURIComponent(lang);

  fetch(apiUrl)
    .then(function(r){ return r.json(); })
    .then(function(data){
      if(data && data.responseData && data.responseData.translatedText){
        var t = data.responseData.translatedText;
        render(t);
        try { localStorage.setItem(cacheKey, t); } catch(e){}
      }
    })
    .catch(function(){
      // 失敗時は英語メッセージを流用
      el.innerHTML = '<div class="msg-other"><span class="lang-tag">' +
        lang.toUpperCase() + '</span>Please wait about 30 seconds...</div>';
    });
})();

// ============================================================
// プログレスバー (擬似: 30秒で100%に近づく)
// ============================================================
(function(){
  var bar = document.getElementById('progress-bar');
  var txt = document.getElementById('progress-text');
  var start = Date.now();
  var duration = 30000; // 30秒
  var tick = setInterval(function(){
    var elapsed = Date.now() - start;
    // 漸近的に100%に近づく (30秒で95%)
    var pct = Math.min(95, (elapsed / duration) * 95);
    bar.style.width = pct.toFixed(1) + '%';
    txt.textContent = Math.floor(pct) + '%';
    if (elapsed > duration * 2) clearInterval(tick);
  }, 200);
})();

// ============================================================
// 実際のビルド処理: 裏で同じURLに ?build=1 を付けてfetch
// 完了したら ?lang=XX にリダイレクト (キャッシュが作られているので即表示)
// ============================================================
(function(){
  var buildUrl = location.pathname + '?lang=<?=rawurlencode($lang)?>&build=1';
  var finalUrl = location.pathname + '?lang=<?=rawurlencode($lang)?>';

  fetch(buildUrl, { credentials: 'same-origin' })
    .then(function(r){ return r.text(); })
    .catch(function(){})
    .finally(function(){
      // プログレスバーを100%にしてから遷移
      var bar = document.getElementById('progress-bar');
      var txt = document.getElementById('progress-text');
      if(bar) bar.style.width = '100%';
      if(txt) txt.textContent = '100%';
      setTimeout(function(){
        location.replace(finalUrl);
      }, 400);
    });
})();
</script>

</body>
</html>
<?php exit; endif; ?>

<?php // 以下は ?lang=ja か ?lang=XX(他言語) のとき表示 ?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>audiocafe.tokyo | 海水が原料の核融合発電。山間ダム発電。AIデーターセンター＆AWS＆NTT WEB ARENAの融合。輸出入・貿易商社</title>
<link rel="icon" href="/images/audiocafe-logo-wordmark.svg" type="image/svg+xml">
<style>
html,body{margin:0;padding:0;height:100%}
body{font-family:'Helvetica Neue',Arial,'Hiragino Kaku Gothic ProN','Hiragino Sans',Meiryo,sans-serif;color:#333;line-height:1.6}
a{text-decoration:none;color:inherit}
ul{list-style:none;padding:0}
:root{--navy:#003366;--blue:#0056b3;--white:#ffffff;--light-gray:#f4f4f4}
header{background:var(--white);padding:20px 5%;display:flex;justify-content:space-between;align-items:center;border-bottom:3px solid var(--navy)}
.logo{font-size:clamp(2rem,6vw,4rem);font-weight:bold;color:var(--navy)}
.logo-mark{text-decoration:none;display:inline-block}
.logo-text--audiocafe{display:inline-block;font-size:clamp(1.6rem,5vw,3rem);font-weight:900;letter-spacing:-.02em;background:linear-gradient(90deg,#6366f1 0%,#2563eb 10%,#22c55e 20%,#84cc16 30%,#facc15 40%,#f97316 50%,#ff1493 60%,#ef4444 70%,#c026d3 80%,#7c3aed 90%,#6366f1 100%);background-size:400% 100%;animation:acLogoGrad 20s ease-in-out infinite;-webkit-background-clip:text;background-clip:text;color:transparent}
@keyframes acLogoGrad{0%{background-position:0% 50%}100%{background-position:100% 50%}}
.ac-site-logo{display:block;height:clamp(2rem,6vw,3.5rem);width:auto;max-width:min(100%,28rem)}
nav ul{display:flex;gap:20px}
nav a{font-weight:bold;color:var(--navy)}
nav a:hover{color:var(--blue)}
.hero{background:var(--navy);color:var(--white);padding:100px 5%;text-align:center}
.hero h1{font-size:40px;margin-bottom:10px}
.hero p{font-size:18px}
section{padding:60px 5%}
h2{text-align:center;color:var(--navy);font-size:32px;margin-bottom:40px;position:relative}
h2::after{content:'';width:60px;height:3px;background:var(--blue);position:absolute;bottom:-10px;left:50%;transform:translateX(-50%)}
.features{display:flex;gap:30px;justify-content:space-between;flex-wrap:wrap}
.feature-item{background:var(--white);padding:30px;flex:1;min-width:240px;text-align:center;border:1px solid #ddd;border-radius:5px}
.feature-item h3{color:var(--blue)}
.products{background:var(--light-gray)}
.product-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px}
.product-item{background:var(--white);padding:20px;text-align:center;border-radius:5px;box-shadow:0 2px 5px rgba(0,0,0,0.1)}
footer{background:var(--navy);color:var(--white);text-align:center;padding:30px 5%;margin-top:40px}
.copyright{font-size:14px;margin-top:20px}
.fullscreen-image{width:100vw;height:min(56.25vw,100vh);display:block;object-fit:contain;object-position:center;background:#000}
img.fullscreen-image:first-of-type{width:80vw;max-width:100%;margin-left:auto;margin-right:auto}
.fullscreen-image.portrait{height:100vh;width:100vw;object-fit:contain}

/* 言語切替バー */
.ac-lang-bar{position:sticky;top:0;z-index:100;background:#003366;padding:.55rem 1rem;display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;box-shadow:0 2px 8px rgba(0,0,0,.3)}
.ac-lang-bar a{color:#22d3ee;text-decoration:none;font-weight:700;font-size:.85rem}
.ac-lang-bar a:hover{text-decoration:underline}
.ac-lang-bar .ac-cur{color:#fff;font-weight:800;margin-left:auto;font-size:.78rem}

<?php if ($is_translate_mode): ?>
/* Google翻訳UI非表示 */
.goog-te-gadget{font-size:0!important;color:transparent!important}
.goog-te-gadget a{display:none!important}
.goog-te-banner-frame{display:none!important}
body{top:0!important}
#google_translate_element{position:fixed;left:-10000px;top:-10000px;opacity:0;pointer-events:none}

/* LIST */
#ac-lists-section{background:#f8fafc;padding:2rem 1rem;border-top:4px solid #0056b3}
#ac-lists-root{max-width:1200px;margin:0 auto}
.ac-list-header{font-size:1.05rem;font-weight:800;color:#003366;padding:.8rem 1rem;margin:1.5rem 0 .75rem;border-left:5px solid #0056b3;background:#fff;border-radius:6px;box-shadow:0 1px 3px rgba(0,0,0,.06)}
.ac-list-header:first-child{margin-top:0}
.ac-list-header .ac-count{font-size:.72rem;font-weight:600;color:#64748b;margin-left:.5rem}
#ac-list-translate{background:#fff;border-radius:8px;padding:.5rem 0;box-shadow:0 1px 3px rgba(0,0,0,.05)}
.ac-item{padding:.65rem 1.1rem;border-bottom:1px solid #eef2f7;font-size:.9rem;line-height:1.55}
.ac-item:last-child{border-bottom:none}
.ac-item a{color:#0056b3;text-decoration:none;word-break:break-word}
.ac-item a:hover{text-decoration:underline;color:#003366}
#ac-list-videos{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:.9rem}
.ac-video-card{background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08);cursor:pointer;display:flex;flex-direction:column;transition:transform .18s,box-shadow .18s}
.ac-video-card:hover{transform:translateY(-2px);box-shadow:0 6px 18px rgba(0,0,0,.12)}
.ac-video-thumb{width:100%;aspect-ratio:16/9;background:#000 center/cover no-repeat;position:relative;display:flex;align-items:center;justify-content:center}
.ac-video-thumb::before{content:'';position:absolute;inset:0;background:linear-gradient(180deg,rgba(0,0,0,0) 60%,rgba(0,0,0,.45));pointer-events:none}
.ac-play-icon{position:relative;z-index:1;width:52px;height:52px;border-radius:50%;background:rgba(255,0,0,.9);display:flex;align-items:center;justify-content:center;box-shadow:0 4px 14px rgba(0,0,0,.4)}
.ac-play-icon::after{content:'';border-style:solid;border-width:8px 0 8px 14px;border-color:transparent transparent transparent #fff;margin-left:3px}
.ac-source-badge{position:absolute;top:.5rem;left:.5rem;z-index:2;font-size:.62rem;font-weight:700;padding:.15rem .45rem;border-radius:4px;background:rgba(0,0,0,.7);color:#fff;letter-spacing:.05em}
.ac-video-meta{padding:.7rem .85rem .85rem;flex:1;display:flex;flex-direction:column;gap:.35rem}
.ac-video-title{font-size:.87rem;font-weight:700;line-height:1.4;color:#0f172a;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden}
.ac-video-url{font-size:.66rem;color:#64748b;word-break:break-all;font-family:ui-monospace,'SF Mono',Menlo,monospace;display:-webkit-box;-webkit-line-clamp:1;-webkit-box-orient:vertical;overflow:hidden}
.ac-video-actions{display:flex;gap:.4rem;margin-top:.4rem}
.ac-video-actions button{flex:1;font-size:.7rem;font-weight:700;padding:.38rem .5rem;border:1px solid #cbd5e1;background:#f1f5f9;color:#0f172a;border-radius:6px;cursor:pointer;transition:all .15s}
.ac-video-actions button:hover{background:#0056b3;color:#fff;border-color:#0056b3}
#ac-list-photos{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:.6rem}
.ac-photo-card{background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.08);cursor:pointer;transition:transform .15s}
.ac-photo-card:hover{transform:scale(1.02)}
.ac-photo-card img{width:100%;height:auto;display:block}
.ac-photo-caption{padding:.45rem .6rem;font-size:.72rem;color:#475569;word-break:break-all;border-top:1px solid #eef2f7}
.ac-empty{padding:1.5rem 1rem;text-align:center;color:#94a3b8;font-size:.9rem;background:#fff;border-radius:8px;border:1px dashed #cbd5e1}
/* ミックスリスト */
#ac-mixlist-section{background:#f0f4ff;padding:2rem 1rem;border-top:4px solid #7c3aed}
#ac-mixlist-root{max-width:1200px;margin:0 auto}
.ac-mix-group{margin-bottom:2.5rem;background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.07);overflow:hidden}
.ac-mix-group-title{font-size:1rem;font-weight:800;color:#fff;background:#003366;padding:.75rem 1.2rem;margin:0;letter-spacing:.03em;border-left:5px solid #7c3aed}
.ac-mix-group-title span{font-size:.75rem;font-weight:500;opacity:.8;margin-left:.6rem}
.ac-mix-item{padding:.8rem 1.2rem;border-bottom:1px solid #eef2f7;font-size:.9rem;line-height:1.65;color:#1e293b}
.ac-mix-item:last-child{border-bottom:none}
.ac-mix-item-en{color:#1e40af;margin-bottom:.3rem}
.ac-mix-item-ja{color:#475569;font-size:.85rem}
/* 言語カード全文表示（左右に余白付き） */
.ac-mix-item--full{padding:1.4rem 2.2rem}
.ac-mix-item-fulltext{white-space:pre-wrap;word-break:break-word;overflow-wrap:anywhere;line-height:1.85;padding-left:1.2rem;padding-right:1.2rem}
.ac-mix-item-fulltext-en{color:#1e40af;font-size:.92rem;margin-bottom:.6rem}
.ac-mix-item-fulltext-ja{color:#374151;font-size:.9rem}
/* 長文の縦スクロール（ルートの言語カードと同じ挙動を復活） */
.ac-mix-item-scroll{
  max-height:16rem;
  overflow-y:auto;
  -webkit-overflow-scrolling:touch;
  overscroll-behavior:contain;
  padding-top:.55rem;
  padding-bottom:.55rem;
  margin-top:.35rem;
  border-radius:.45rem;
  background:#f8fafc;
  border:1px solid #e2e8f0;
}
.ac-mix-item-scroll::-webkit-scrollbar{width:8px}
.ac-mix-item-scroll::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:4px}
.ac-mix-item-scroll::-webkit-scrollbar-thumb:hover{background:#94a3b8}
.ac-mix-item-scroll::-webkit-scrollbar-track{background:transparent}
.ac-mix-item-flag-row{display:flex;align-items:center;gap:.55rem;margin-bottom:.7rem;padding-left:1.2rem;padding-right:1.2rem}
.ac-mix-item-flag-row img{width:38px;height:25px;object-fit:cover;border-radius:3px;box-shadow:0 1px 4px rgba(0,0,0,.18);flex-shrink:0}
.ac-mix-item-flag-label{font-size:.8rem;font-weight:800;color:#7c3aed;letter-spacing:.04em}
.ac-mix-item-label{font-size:.78rem;font-weight:800;color:#1e40af;letter-spacing:.04em;padding-left:1.2rem;padding-right:1.2rem;display:block;margin-bottom:.2rem}
.ac-mix-lang-sep{margin:.85rem 1.2rem .6rem;border:none;border-top:1px dashed #d1d5db}
.ac-mix-item-links{display:flex;flex-wrap:wrap;gap:.55rem;padding:.9rem 1.2rem .4rem}
.ac-mix-item-link-btn{display:inline-flex;align-items:center;gap:.35em;padding:.45rem 1rem;border-radius:8px;background:linear-gradient(135deg,#1e40af,#7c3aed);color:#fff;font-size:.82rem;font-weight:700;text-decoration:none;line-height:1.3;transition:opacity .18s,transform .12s}
.ac-mix-item-link-btn:hover{opacity:.85;transform:translateY(-1px)}
@media (max-width:768px){
  .ac-mix-item--full{padding:1.1rem 1rem}
  .ac-mix-item-fulltext{padding-left:.4rem;padding-right:.4rem}
  .ac-mix-item-flag-row{padding-left:.4rem;padding-right:.4rem}
  .ac-mix-item-label{padding-left:.4rem;padding-right:.4rem}
  .ac-mix-lang-sep{margin-left:.4rem;margin-right:.4rem}
  .ac-mix-item-scroll{max-height:13rem}
  .ac-mix-item-links{padding-left:.4rem;padding-right:.4rem}
  .ac-mix-item-link-btn{font-size:.78rem;padding:.4rem .8rem}
}
<?php endif; ?>
</style>
</head>
<body>
<?php $mlja = ($lang === 'ja'); ?>

<?php if ($is_translate_mode): ?>
<!-- Google翻訳マウントポイント -->
<div id="google_translate_element" aria-hidden="true"></div>
<?php endif; ?>

<!-- 言語切替バー -->
<div class="ac-lang-bar">
  <a href="./">🌐 言語選択に戻る</a>
  <span class="ac-cur">Lang: <?=htmlspecialchars($lang)?></span>
</div>

<script>
(function(){
  if(document.getElementById('ext-link-switch') && location.pathname.indexOf('/top') !== 0){
    try{
      var u = new URL(location.href);
      if(!u.searchParams.has('_ac_fixtop')){
        u.searchParams.set('_ac_fixtop','1');
        u.searchParams.set('_', String(Date.now()));
        location.replace(u.toString());
      }
    }catch(e){}
  }
})();
</script>


<?php if ($is_mixlist): ?>
<div id="root-back-link-wrap"
     style="
      position:relative;
      z-index:99999;
      padding-top:120px;
      padding-left:18px;
      margin-bottom:18px;
     ">

  <a href="https://audiocafe.tokyo/?google_translate=0"
     id="root-back-link"
     target="_top"
     rel="noopener"
     onclick="return forceRootHome(event);"
     style="
      display:inline-block;
      font-size:2.4em;
      font-weight:bold;
      line-height:1.6;
      text-decoration:none;
      color:#00ccff;
      background:rgba(0,0,0,0.78);
      padding:16px 24px;
      border-radius:18px;
      box-shadow:0 4px 18px rgba(0,0,0,0.35);
      position:relative;
      z-index:99999;
      <?= ($lang !== 'ja') ? 'margin-top:120px;' : 'margin-top:90px;' ?>
     ">

     ⬅ 言語選択前の https://audiocafe.tokyo ルートに戻る

  </a>

</div>

<script>
function forceRootHome(e){

  e.preventDefault();

  // Google Translate iframe対策
  try{
    if(window.location.hostname.indexOf('translate.goog') !== -1){
      window.top.location.href = 'https://audiocafe.tokyo/';
      return false;
    }
  }catch(err){}

  // 翻訳Cookie削除
  document.cookie = 'googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
  document.cookie = 'googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=.audiocafe.tokyo;';

  // 強制的に翻訳無しルートへ
  window.top.location.replace('https://audiocafe.tokyo/');

  return false;
}
</script>

<style>
@media (max-width:768px){

  #root-back-link-wrap{
    padding-top:140px !important;
    padding-left:12px !important;
  }

  #root-back-link{
    font-size:1.65em !important;
    line-height:1.7 !important;
  }

}
</style>
<?php endif; ?>


    </h2>

    <!-- 1. 全言語カード 全文（L[]から自動生成） -->
    <div class="ac-mix-group">
      <h3 class="ac-mix-group-title">
        <?php if ($mlja): ?>
          🌐 全言語カード 全文（L[]から自動生成・<?=count($SUMMARY_LIST)?>件）
        <?php else: ?>
          🌐 All Language Cards — Full Text (auto from L[], <?=count($SUMMARY_LIST)?> cards)
        <?php endif; ?>
      </h3>
      <?php
        // 国旗コード: L[]のfcフィールドを直接使用（自動生成済み）
        // aフィールドからのフォールバックマップも保持
        $FC_MAP_A = [
          'EGYPT'=>'eg','USA'=>'us','JAPAN'=>'jp','IRAN (Persian)'=>'ir',
          'IRAQ (Arabic)'=>'iq','RUSSIA'=>'ru','CHINA'=>'cn','KOREA'=>'kr',
          'TAIWAN'=>'tw','INDIA'=>'in','GERMANY'=>'de','FRANCE'=>'fr',
          'SPAIN'=>'es','ITALY'=>'it','PORTUGAL'=>'pt','NETHERLANDS'=>'nl',
          'POLAND'=>'pl','UKRAINE'=>'ua','TURKEY'=>'tr','INDONESIA'=>'id',
          'VIETNAM'=>'vn','THAILAND'=>'th','PHILIPPINES'=>'ph','MALAYSIA'=>'my',
          'MYANMAR'=>'mm','CAMBODIA'=>'kh','BANGLADESH'=>'bd','PAKISTAN'=>'pk',
          'SAUDI ARABIA'=>'sa','UAE'=>'ae','ISRAEL'=>'il','IRAQ'=>'iq',
          'IRAN'=>'ir','AFGHANISTAN'=>'af','NIGERIA'=>'ng','ETHIOPIA'=>'et',
          'KENYA'=>'ke','SOUTH AFRICA'=>'za','BRAZIL'=>'br','MEXICO'=>'mx',
          'ARGENTINA'=>'ar','COLOMBIA'=>'co','CANADA'=>'ca','AUSTRALIA'=>'au',
          'NZ'=>'nz','UK'=>'gb','SWEDEN'=>'se','NORWAY'=>'no','DENMARK'=>'dk',
          'FINLAND'=>'fi','CZECH'=>'cz','SLOVAKIA'=>'sk','HUNGARY'=>'hu',
          'ROMANIA'=>'ro','BULGARIA'=>'bg','GREECE'=>'gr','CROATIA'=>'hr',
          'SERBIA'=>'rs','ESTONIA'=>'ee','LATVIA'=>'lv','LITHUANIA'=>'lt',
          'AZERBAIJAN'=>'az','GEORGIA'=>'ge','ARMENIA'=>'am','KAZAKHSTAN'=>'kz',
          'UZBEKISTAN'=>'uz','MONGOLIA'=>'mn','NEPAL'=>'np','SRI LANKA'=>'lk',
          'HAWAII'=>'us','SAMOA'=>'ws','FIJI'=>'fj',
        ];
        foreach ($SUMMARY_LIST as $sm):
          // fcフィールドが自動生成されている場合はそれを優先、なければマップから取得
          $fc = (!empty($sm['fc']) && $sm['fc'] !== 'un') ? $sm['fc']
              : (isset($FC_MAP_A[$sm['a']]) ? $FC_MAP_A[$sm['a']] : 'un');
      ?>
        <div class="ac-mix-item ac-mix-item--full">
          <div class="ac-mix-item-flag-row">
            <img src="https://flagcdn.com/w80/<?=htmlspecialchars($fc)?>.png" alt="<?=htmlspecialchars($sm['a'])?>" loading="lazy">
            <span class="ac-mix-item-flag-label"><?=htmlspecialchars($sm['a'])?> [<?=htmlspecialchars($sm['g'])?>]</span>
          </div>
          <?php if (!empty($sm['title_en'])): ?>
            <span class="ac-mix-item-label">EN</span>
            <div class="ac-mix-item-scroll">
              <div class="ac-mix-item-fulltext ac-mix-item-fulltext-en"><?=nl2br(htmlspecialchars($sm['title_en']))?></div>
            </div>
          <?php endif; ?>
          <?php if (!empty($sm['title_en']) && !empty($sm['title_ja'])): ?>
            <hr class="ac-mix-lang-sep">
          <?php endif; ?>
          <?php if (!empty($sm['title_ja'])): ?>
            <span class="ac-mix-item-label" style="color:#7c3aed">JA / 日本語</span>
            <div class="ac-mix-item-scroll">
              <div class="ac-mix-item-fulltext ac-mix-item-fulltext-ja"><?=nl2br(htmlspecialchars($sm['title_ja']))?></div>
            </div>
          <?php endif; ?>
          <?php if (!empty($sm['links'])): ?>
            <div class="ac-mix-item-links">
              <?php foreach ($sm['links'] as $lk): ?>
                <a href="<?=htmlspecialchars($lk['href'])?>" target="_blank" rel="noopener noreferrer" class="ac-mix-item-link-btn">
                  🎬 <?=htmlspecialchars($lk['label'])?>
                </a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- 2. /top の全文章の要約 -->
    <div class="ac-mix-group">
      <h3 class="ac-mix-group-title">
        <?php if ($mlja): ?>
          📄 audiocafe.tokyo/top 全文章の要約
        <?php else: ?>
          📄 audiocafe.tokyo/top Summary
        <?php endif; ?>
      </h3>
      <div class="ac-mix-item">
        <?php if ($mlja): ?>
          <p style="margin:0 0 .8rem;line-height:1.7;color:#475569">
            /top ページには、がん治療法（体温の法則、40℃前後の温かいお風呂の効能）、
            YouTube 音楽プレイヤー（歌手シリーズ、AI ジャンル検出）、会社概要、
            多言語対応（130カ国語Google翻訳）などの包括的なコンテンツが含まれています。
          </p>
        <?php else: ?>
          <p style="margin:0 0 .8rem;line-height:1.7;color:#475569">
            The /top page contains comprehensive content including cancer treatment methods (body temperature principles, benefits of 40°C warm baths),
            YouTube music player (artist series, AI genre detection), company overview (Aeon Japan Corporation),
            multilingual support (130 languages via Google Translate), and more.
          </p>
        <?php endif; ?>
        <a href="https://audiocafe.tokyo/top/?lang=<?=rawurlencode($lang)?><?php if(!$mlja): ?>#translateto=<?=rawurlencode($lang)?><?php endif; ?>"
           target="_blank" rel="noopener noreferrer"
           style="color:#0056b3;font-weight:700;font-size:1rem">
          <?php if ($mlja): ?>▶ audiocafe.tokyo/top を開く<?php else: ?>▶ Open audiocafe.tokyo/top in <?=htmlspecialchars($lang)?><?php endif; ?>
        </a>
      </div>
    </div>

    <!-- 3. ルート翻訳リンク -->
    <div class="ac-mix-group">
      <h3 class="ac-mix-group-title">
        <?php if ($mlja): ?>
          🌐 audiocafe.tokyo 翻訳リンク（<?=count($lists['text_links'])?>件）
        <?php else: ?>
          🌐 audiocafe.tokyo Translation Links (<?=count($lists['text_links'])?> entries)
        <?php endif; ?>
      </h3>
      <div style="max-height:400px;overflow-y:auto">
        <?php foreach ($lists['text_links'] as $tl): ?>
          <div class="ac-mix-item">
            <a href="<?=htmlspecialchars($tl['url'])?>" target="_blank" rel="noopener noreferrer" style="color:#0056b3;text-decoration:none"><?=htmlspecialchars($tl['title'])?></a>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- 3つのLIST -->
<section id="ac-lists-section">
  <div id="ac-lists-root">
    <h2 class="ac-list-header" lang="ja">📄 翻訳LIST <span class="ac-count" lang="ja">(<?=count($lists['text_links'])?>件)</span></h2>
    <?php if (empty($lists['text_links'])): ?>
      <div class="ac-empty" lang="ja">テキストリンクはありません</div>
    <?php else: ?>
      <div id="ac-list-translate" lang="ja">
        <?php foreach ($lists['text_links'] as $it): ?>
          <div class="ac-item" lang="ja"><a href="<?=htmlspecialchars($it['url'])?>" target="_blank" rel="noopener noreferrer" lang="ja"><?=htmlspecialchars($it['title'])?></a></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <h2 class="ac-list-header notranslate" translate="no">🎬 動画LIST <span class="ac-count">(<?=count($lists['video_links'])?>件・翻訳なし)</span></h2>
    <?php if (empty($lists['video_links'])): ?>
      <div class="ac-empty notranslate" translate="no">動画リンクはありません</div>
    <?php else: ?>
      <div id="ac-list-videos" class="notranslate" translate="no">
        <?php foreach ($lists['video_links'] as $it):
          $yt_id = extract_yt_id($it['url']);
          $thumb = $yt_id ? "https://i.ytimg.com/vi/{$yt_id}/hqdefault.jpg" : '';
          $src_n = source_name($it['url']);
        ?>
          <div class="ac-video-card" data-video-url="<?=htmlspecialchars($it['url'])?>" data-yt-id="<?=htmlspecialchars($yt_id ?: '')?>">
            <div class="ac-video-thumb"<?=$thumb?" style=\"background-image:url('".htmlspecialchars($thumb)."')\"":''?>>
              <span class="ac-source-badge"><?=htmlspecialchars($src_n)?></span>
              <div class="ac-play-icon"></div>
            </div>
            <div class="ac-video-meta">
              <div class="ac-video-title"><?=htmlspecialchars($it['title'])?></div>
              <div class="ac-video-url"><?=htmlspecialchars($it['url'])?></div>
              <div class="ac-video-actions">
                <button type="button" data-action="play">▶ 再生</button>
                <button type="button" data-action="copy">📋 URLコピー</button>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <h2 class="ac-list-header" lang="ja">🖼 写真LIST <span class="ac-count" lang="ja">(<?=count($lists['photos'])?>件)</span></h2>
    <?php if (empty($lists['photos'])): ?>
      <div class="ac-empty" lang="ja">写真はありません</div>
    <?php else: ?>
      <div id="ac-list-photos" class="notranslate" translate="no">
        <?php foreach ($lists['photos'] as $p): ?>
          <div class="ac-photo-card" data-photo-src="<?=htmlspecialchars($p['src'])?>">
            <img src="<?=htmlspecialchars($p['src'])?>" alt="<?=htmlspecialchars($p['alt'])?>" loading="lazy" referrerpolicy="no-referrer">
            <?php if ($p['alt']): ?>
              <div class="ac-photo-caption"><?=htmlspecialchars($p['alt'])?></div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<script>
// === Google翻訳 cookie 設定（他言語時のみ） =========================
(function(){
  "use strict";
  var lang = <?=json_encode($lang)?>;
  if(!lang || lang === 'ja') return;
  var expected = '/ja/' + lang;
  var m = document.cookie.match(/(?:^|;\s*)googtrans=([^;]+)/);
  var cur = m ? decodeURIComponent(m[1]) : '';
  var key = 'gtLoop_' + lang;
  if(cur !== expected){
    document.cookie = 'googtrans=' + expected + '; path=/';
    try{
      var host = location.hostname;
      var parts = host.split('.');
      if(parts.length > 1){
        document.cookie = 'googtrans=' + expected + '; path=/; domain=.' + parts.slice(-2).join('.');
      }
    }catch(e){}
    if(sessionStorage.getItem(key) !== '1'){
      sessionStorage.setItem(key, '1');
      location.reload();
      return;
    }
  } else {
    sessionStorage.removeItem(key);
  }
})();

// === Google翻訳ウィジェット初期化 ===================================
function googleTranslateElementInit(){
  new google.translate.TranslateElement({
    pageLanguage: 'ja',
    autoDisplay: false
  }, 'google_translate_element');
}

// === 動画LIST / 写真LIST / URLコピー ================================
(function(){
  var root = document.getElementById('ac-lists-root');
  if(!root) return;
  root.addEventListener('click', function(ev){
    var btn = ev.target.closest ? ev.target.closest('button[data-action]') : null;
    if(btn){
      ev.preventDefault(); ev.stopPropagation();
      var card = btn.closest('.ac-video-card'); if(!card) return;
      var url = card.getAttribute('data-video-url');
      if(btn.getAttribute('data-action') === 'play'){
        window.open(url, '_blank', 'noopener,noreferrer');
      } else { copyUrl(url, btn); }
      return;
    }
    var card2 = ev.target.closest ? ev.target.closest('.ac-video-card') : null;
    if(card2){
      var url2 = card2.getAttribute('data-video-url');
      if(url2) window.open(url2, '_blank', 'noopener,noreferrer');
      return;
    }
    var photo = ev.target.closest ? ev.target.closest('.ac-photo-card') : null;
    if(photo){
      var src = photo.getAttribute('data-photo-src');
      if(src) window.open(src, '_blank', 'noopener,noreferrer');
    }
  });
  function copyUrl(text, btn){
    var done = function(){
      var o = btn.textContent;
      btn.textContent = '✓ コピー済';
      btn.style.background='#10b981'; btn.style.color='#fff'; btn.style.borderColor='#10b981';
      setTimeout(function(){ btn.textContent=o; btn.style.background=''; btn.style.color=''; btn.style.borderColor=''; }, 1600);
    };
    if(navigator.clipboard && navigator.clipboard.writeText){
      navigator.clipboard.writeText(text).then(done, function(){ fb(text); done(); });
    } else { fb(text); done(); }
  }
  function fb(t){
    try{
      var ta=document.createElement('textarea');
      ta.value=t; ta.style.position='fixed'; ta.style.left='-9999px';
      document.body.appendChild(ta); ta.select();
      document.execCommand('copy');
      document.body.removeChild(ta);
    }catch(e){}
  }
  // YouTube oEmbedで正式タイトル
  var cards = document.querySelectorAll('.ac-video-card[data-yt-id]');
  cards.forEach(function(card){
    var ytId = card.getAttribute('data-yt-id');
    if(!ytId) return;
    var url = card.getAttribute('data-video-url');
    var titleEl = card.querySelector('.ac-video-title');
    if(!titleEl || !url) return;
    fetch('https://www.youtube.com/oembed?url=' + encodeURIComponent(url) + '&format=json')
      .then(function(r){ if(!r.ok) throw new Error(); return r.json(); })
      .then(function(data){ if(data && data.title) titleEl.textContent = data.title; })
      .catch(function(){});
  });
})();
</script>

<script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

<!-- ========================================================
     全リンクを Google翻訳プロキシ経由に書き換える (translate_mode)
     対象 : 通常のHTMLページ + PDF/テキスト/Word/Excel 等のドキュメント
     除外 : 動画 (YouTube/FB/Drive 等) ・画像 (.jpg/.png 等) ・
            Google自身のサイト ・ メールリンク (mailto:) など
     ======================================================== -->
<script>
(function(){
  "use strict";

  // ---- 対象言語の検出 ---------------------------------------------------
  function detectLang(){
    try {
      var sp = new URLSearchParams(location.search);
      var tl = sp.get('_x_tr_tl'); if(tl) return tl;
      var m = location.hash.match(/translateto=([a-zA-Z-]+)/);
      if(m) return decodeURIComponent(m[1]);
      var lang = sp.get('lang'); if(lang) return lang;
      var cookieMatch = document.cookie.match(/(?:^|;\s*)googtrans=\/[^/]+\/([^;]+)/);
      if(cookieMatch) return cookieMatch[1];
      var sLang = sessionStorage.getItem('selectedLang') || localStorage.getItem('selectedLang');
      if (sLang) return sLang;
    } catch(e) {}
    return '';
  }
  var TARGET_LANG = detectLang();
  if(!TARGET_LANG || TARGET_LANG === 'ja') return;

  // ---- 除外判定 (動画 / 画像 / Google自身) ------------------------------
  // ※ PDF・テキスト・docx・xlsx 等は ★翻訳プロキシ経由★ にする (ユーザー要望)
  var SKIP_HOSTS_RE   = /(?:^|\.)(?:youtube\.com|youtu\.be|facebook\.com|fb\.watch|vimeo\.com|tiktok\.com|drive\.google\.com|docs\.google\.com|nicovideo\.jp|instagram\.com|twitter\.com|x\.com)$/i;
  var GOOGLE_HOST_RE  = /^(?:www\.)?google\.[a-z.]+$/i;
  var IMG_EXT_RE      = /\.(?:jpe?g|png|gif|webp|svg|bmp|ico|avif|heic|heif)(?:[?#]|$)/i;
  var TRANSLATE_GOOG  = '.translate.goog';

  // ---- URL を翻訳プロキシ URL へ変換 -----------------------------------
  function toProxyUrl(href){
    if(!href || typeof href !== 'string') return href;
    if(/^(?:mailto:|tel:|javascript:|#)/i.test(href)) return href;
    if(!/^https?:\/\//i.test(href)) return href;

    var u;
    try{ u = new URL(href, location.href); }catch(e){ return href; }
    var host = u.hostname.toLowerCase();

    // (A) 既に *.translate.goog → _x_tr_tl だけ書き換える
    if(host.length > TRANSLATE_GOOG.length &&
       host.slice(-TRANSLATE_GOOG.length) === TRANSLATE_GOOG){
      if(!u.searchParams.get('_x_tr_sl'))  u.searchParams.set('_x_tr_sl','ja');
      u.searchParams.set('_x_tr_tl', TARGET_LANG);
      if(!u.searchParams.get('_x_tr_hl'))  u.searchParams.set('_x_tr_hl','ja');
      if(!u.searchParams.get('_x_tr_pto')) u.searchParams.set('_x_tr_pto','wapp');
      return u.toString();
    }

    // (B) translate.google.com 自体はそのまま (既に翻訳ラッパー)
    if(host === 'translate.google.com' || host === 'translate.googleusercontent.com') return href;

    // (Special) Daijob specific rules
    if(host === 'www.daijob.com' || host === 'daijob.com'){
      // 日本語を選択時に、 https://www.daijob.com/ はそのままGoogle翻訳しないで表示
      if(TARGET_LANG === 'ja') return href;
      // /en/ ページは常に Google翻訳を通さず、そのまま表示する
      if(u.pathname.indexOf('/en/') === 0) return href;
    }

    // (C) 動画 / SNS / Drive 等 → 再生可能に保つためそのまま
    if(SKIP_HOSTS_RE.test(host)) return href;

    // (D) Google 自身のサイト (検索 / マップ等) → そのまま
    if(GOOGLE_HOST_RE.test(host)) return href;

    // (E) 直接画像 URL → 表示のためそのまま
    if(IMG_EXT_RE.test(u.pathname)) return href;

    // (F) その他 (HTML / PDF / TXT / DOCX / XLSX 等) → 翻訳プロキシ経由
    //     例: aon.tokyo → aon-tokyo.translate.goog
    //     例: aon.tokyo/r.pdf → aon-tokyo.translate.goog/r.pdf?...
    //     ※ ホスト名に元から `-` がある場合は `--` にエスケープ
    var proxyHost = host.replace(/-/g,'--').replace(/\./g,'-') + TRANSLATE_GOOG;
    u.hostname = proxyHost;
    u.searchParams.set('_x_tr_sl','ja');
    u.searchParams.set('_x_tr_tl', TARGET_LANG);
    u.searchParams.set('_x_tr_hl','ja');
    u.searchParams.set('_x_tr_pto','wapp');
    
  // 内部ドメインの場合はハッシュも付与
  var SELF_DOMAIN_RE = /(^|\.)(ala\.tokyo|aon\.co\.jp|aon\.tokyo|audiocafe\.tokyo|icpo\.tokyo|ion\.tokyo|nanosoft\.jp|nasa\.tokyo|raysoft\.jp)$/i;
  if(SELF_DOMAIN_RE.test(host)){
    var hash = u.hash || '';
    if(hash.indexOf('translateto=') === -1){
      var parts = hash.replace(/^#/,'').split('&').filter(function(s){ return s; });
      parts.push('translateto=' + encodeURIComponent(TARGET_LANG));
      u.hash = '#' + parts.join('&');
    }
    if(!u.searchParams.has('lang')) u.searchParams.set('lang', TARGET_LANG);
  }
    
    return u.toString();
  }

  // ---- DOM 中のすべての <a href> を書き換え ----------------------------
  function rewriteAll(){
    var as = document.querySelectorAll('a[href]');
    for(var i=0; i<as.length; i++){
      var a = as[i];
      if(a.dataset && a.dataset.translateProxied === '1') continue;
      // notranslate コンテナ内のリンクは触らない (動画LIST 等)
      if(a.closest && a.closest('.notranslate, [translate="no"]')) continue;

      // data-daijob-en: 表示言語が英語の場合は英語版URLに差し替えてプロキシもスキップ
      // ※ data-no-translate より先に判定する（両属性が共存する場合のため）
      var enAlt = a.getAttribute('data-daijob-en');
      if(enAlt && (TARGET_LANG === 'en' || TARGET_LANG.indexOf('en-') === 0)){
        a.setAttribute('href', enAlt);
        a.setAttribute('data-no-translate', '1');
        if(a.dataset) a.dataset.translateProxied = '1';
        if(!a.hasAttribute('target')){
          a.setAttribute('target','_blank');
          a.setAttribute('rel','noopener noreferrer');
        }
        continue;
      }

      // data-no-translate="1": 翻訳プロキシを経由させない（常に素のURLのまま）
      if(a.getAttribute('data-no-translate') === '1'){
        if(a.dataset) a.dataset.translateProxied = '1';
        continue;
      }

      var orig = a.getAttribute('href');
      if(!orig) continue;
      var fixed = toProxyUrl(orig);
      if(fixed && fixed !== orig){
        a.setAttribute('href', fixed);
        if(a.dataset) a.dataset.translateProxied = '1';
        // 別タブで開く (元ページに戻れるように)
        if(!a.hasAttribute('target')){
          a.setAttribute('target','_blank');
          a.setAttribute('rel','noopener noreferrer');
        }
      }
    }
  }

  // 初回実行
  if(document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', rewriteAll);
  } else {
    rewriteAll();
  }

  // 動的に挿入されるリンクにも対応 (debounce)
  if(window.MutationObserver){
    var pending = false;
    var mo = new MutationObserver(function(){
      if(pending) return;
      pending = true;
      setTimeout(function(){ pending = false; rewriteAll(); }, 120);
    });
    var startObserve = function(){
      if(document.body) mo.observe(document.body, { childList:true, subtree:true });
    };
    if(document.body) startObserve();
    else document.addEventListener('DOMContentLoaded', startObserve);
  }

  // 手動再適用用 (デバッグ用)
  window.__rewriteLinksToProxy = rewriteAll;

  // ---- クリック時バイパス -----------------------------------------------
  // 別 IIFE の rewriteAll が href を *.translate.goog に書き換えていても、
  // ここで強制的に素のURLに戻して window.open する。
  // stopImmediatePropagation() で他のクリックハンドラも止める。
  function stripTranslateProxy(href){
    if(!href) return href;
    try{
      var u = new URL(href, location.href);
      var host = (u.hostname || '').toLowerCase();

      // translate.google.com のラッパーURL（.../website?u= または .../translate?u=）を素のURLへ戻す
      if(host === 'translate.google.com' && (u.pathname === '/website' || u.pathname === '/translate')){
        var wrapped = u.searchParams.get('u');
        if(!wrapped) return href;
        var prev = wrapped;
        for(var di=0; di<3; di++){
          try{
            var dec = decodeURIComponent(prev);
            if(dec === prev) break;
            prev = dec;
          }catch(e){ break; }
        }
        return prev;
      }

      // translate.goog proxy
      if(host.indexOf('.translate.goog') === -1) return href;
      var sub = u.hostname.replace(/\.translate\.goog$/, '');
      // "--" は元の "-"、それ以外の "-" は元の "." に戻す
      var orig = sub.replace(/--/g, '\u0001').replace(/-/g, '.').replace(/\u0001/g, '-');
      var sp = u.searchParams;
      ['_x_tr_sl','_x_tr_tl','_x_tr_hl','_x_tr_pto','_x_tr_hist'].forEach(function(k){ sp.delete(k); });
      var qs = sp.toString();
      return 'https://' + orig + u.pathname + (qs ? '?' + qs : '') + (u.hash || '');
    }catch(e){ return href; }
  }

  document.addEventListener('click', function(ev){
    var a = ev.target.closest && ev.target.closest('a[href]');
    if(!a) return;

    var hasNoTranslate = a.getAttribute('data-no-translate') === '1';
    var enAlt          = a.getAttribute('data-daijob-en');
    if(!hasNoTranslate && !enAlt) return;

    var isEn = TARGET_LANG === 'en' || TARGET_LANG.indexOf('en-') === 0;

    // 開くべきURLを決定:
    //  - data-daijob-en + 英語表示 → 強制的に /en/ URL
    //  - それ以外 → href を素のURL化（translate.goog 経由なら剥がす）
    var target;
    if(enAlt && isEn){
      target = enAlt;
    } else {
      target = stripTranslateProxy(a.getAttribute('href') || '');
    }
    if(!target) return;

    ev.preventDefault();
    ev.stopPropagation();
    if(ev.stopImmediatePropagation) ev.stopImmediatePropagation();
    window.open(target, '_blank', 'noopener,noreferrer');
  }, true);
})();
</script>

</body>
</html>
<?php
// ============================================================
// ★ 動画ランチャーモード (?mode=videolauncher)
// ============================================================
if ($is_videolauncher):
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>動画ランチャー — AUDIOCAFE</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{background:#0a0f1e;color:#e2e8f0;font-family:'Segoe UI',system-ui,sans-serif;min-height:100vh}
a{color:#22d3ee;text-decoration:none}
a:hover{text-decoration:underline}
#app{max-width:860px;margin:0 auto;padding:1.25rem 1rem 3rem}
.hd{display:flex;align-items:center;gap:.75rem;margin-bottom:1.5rem;flex-wrap:wrap}
.hd h1{font-size:1.25rem;font-weight:800;color:#f8fafc}
.back-link{display:inline-block;font-size:.78rem;color:#94a3b8;border:1px solid rgba(148,163,184,.25);padding:.35rem .85rem;border-radius:8px}
.back-link:hover{color:#22d3ee;border-color:rgba(34,211,238,.4);text-decoration:none}
.search-row{display:flex;gap:.5rem;margin-bottom:.75rem}
.search-row input{flex:1;padding:.65rem .9rem;border-radius:10px;border:1px solid rgba(34,211,238,.3);background:rgba(15,23,42,.85);color:#f1f5f9;font-size:.875rem;outline:none}
.search-row input:focus{border-color:rgba(34,211,238,.7);box-shadow:0 0 0 3px rgba(34,211,238,.12)}
.search-row button{padding:.65rem 1.1rem;border-radius:10px;border:1px solid rgba(34,211,238,.45);background:rgba(34,211,238,.18);color:#e0f2fe;font-size:.875rem;font-weight:700;cursor:pointer;white-space:nowrap}
.search-row button:hover{background:rgba(34,211,238,.32);border-color:rgba(34,211,238,.75)}
.hint{background:rgba(15,23,42,.7);border:1px solid rgba(148,163,184,.18);border-radius:8px;padding:.65rem 1rem;font-size:.78rem;color:#94a3b8;margin-bottom:1rem;line-height:1.6}
.hint strong{color:#cbd5e1}
.status{padding:.65rem 1rem;border-radius:8px;border:1px solid rgba(148,163,184,.22);background:rgba(15,23,42,.8);font-size:.82rem;color:#94a3b8;margin-bottom:1rem;display:none}
.ctrl-bar{background:rgba(15,23,42,.88);border:1px solid rgba(34,211,238,.22);border-radius:10px;padding:.65rem 1rem;margin-bottom:1rem;display:none;align-items:center;gap:.65rem;flex-wrap:wrap}
.ctrl-bar .now-title{font-size:.82rem;font-weight:700;color:#f1f5f9;flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;min-width:0}
.ctrl-bar .idx{font-size:.72rem;color:#64748b;white-space:nowrap}
.mini-btn{font-size:.72rem;font-weight:700;padding:.35rem .75rem;border-radius:8px;border:1px solid rgba(34,211,238,.38);background:rgba(15,23,42,.6);color:#e0f2fe;cursor:pointer}
.mini-btn:hover{border-color:rgba(34,211,238,.75);background:rgba(34,211,238,.18)}
.auto-chk{display:flex;align-items:center;gap:.35rem;font-size:.75rem;color:#94a3b8;cursor:pointer}
.auto-chk input{width:14px;height:14px;accent-color:#22d3ee;cursor:pointer}
.vcard{background:rgba(15,23,42,.72);border:1px solid rgba(255,255,255,.07);border-radius:12px;overflow:hidden;margin-bottom:.7rem;transition:border-color .15s}
.vcard:hover{border-color:rgba(34,211,238,.28)}
.vcard-row{display:flex;align-items:stretch}
.vthumb{width:108px;min-height:68px;background:#111;flex-shrink:0;position:relative;overflow:hidden;display:flex;align-items:center;justify-content:center}
.vthumb img{width:100%;height:100%;object-fit:cover;display:block}
.vthumb-ico{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:1.8rem;color:rgba(255,255,255,.35)}
.vbody{flex:1;padding:.75rem .9rem;min-width:0;display:flex;flex-direction:column;gap:.3rem}
.vbody-top{display:flex;align-items:center;gap:.45rem;flex-wrap:wrap}
.badge{font-size:.68rem;padding:2px 8px;border-radius:8px;font-weight:700;white-space:nowrap}
.b-yt{background:#7f1d1d;color:#fca5a5}
.b-tw{background:#1e3a5f;color:#93c5fd}
.b-fb{background:#312e81;color:#c4b5fd}
.b-ig{background:#831843;color:#f9a8d4}
.b-vm{background:#064e3b;color:#6ee7b7}
.b-nc{background:#78350f;color:#fcd34d}
.b-mp4{background:#1e293b;color:#cbd5e1}
.b-tk{background:#450a0a;color:#fca5a5}
.b-other{background:#1e293b;color:#94a3b8}
.can-tag{font-size:.68rem;color:#4ade80;white-space:nowrap}
.no-tag{font-size:.68rem;color:#fb923c;white-space:nowrap}
.vtitle{font-size:.82rem;font-weight:600;color:#f1f5f9;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.vurl{font-size:.65rem;color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.vbtns{display:flex;gap:.45rem;flex-wrap:wrap;margin-top:.3rem}
.btn-play{font-size:.72rem;padding:.3rem .85rem;border-radius:7px;border:1px solid rgba(34,211,238,.4);background:rgba(34,211,238,.15);color:#e0f2fe;cursor:pointer;display:inline-flex;align-items:center;gap:.3rem}
.btn-play:hover{background:rgba(34,211,238,.28);border-color:rgba(34,211,238,.7)}
.btn-open{font-size:.72rem;padding:.3rem .85rem;border-radius:7px;border:1px solid rgba(148,163,184,.3);background:transparent;color:#94a3b8;cursor:pointer}
.btn-open:hover{border-color:rgba(34,211,238,.45);color:#22d3ee}
.embed-wrap{border-top:1px solid rgba(255,255,255,.06);display:none}
.embed-wrap iframe,.embed-wrap video{width:100%;display:block;border:none}
.empty{text-align:center;padding:3rem 1rem;color:#475569;font-size:.9rem;display:none}
</style>
</head>
<body>
<div id="app">
  <div class="hd">
    <a href="/" class="back-link">← AUDIOCAFE トップへ</a>
    <h1>🎬 動画リンク自動抽出ランチャー</h1>
  </div>

  <div class="search-row">
    <input id="urlIn" type="url" placeholder="ブログ / アメブロ / X / Facebook / note / 一般サイトの URL を入力" />
    <button onclick="doAnalyze()">🔍 解析</button>
  </div>

  <div class="hint">
    <strong>▶ 自動再生:</strong> YouTube・Vimeo・ニコニコ動画・MP4直リンク &nbsp;|&nbsp;
    <strong>↗ 遷移して再生:</strong> Facebook・X・Instagram・TikTok（ブラウザのログイン状態で再生）<br>
    URLを入力してEnterキーまたは「解析」ボタンで動画リンクを自動検出します。
  </div>

  <div id="statusBox" class="status"></div>

  <div id="ctrlBar" class="ctrl-bar">
    <span class="now-title" id="nowTitle">—</span>
    <span class="idx" id="nowIdx"></span>
    <button class="mini-btn" onclick="goPrev()">◀ 前へ</button>
    <button class="mini-btn" onclick="goNext()">次へ ▶</button>
    <label class="auto-chk"><input type="checkbox" id="autoNext" checked> 自動で次へ</label>
  </div>

  <div id="listArea"></div>
  <div id="emptyBox" class="empty">動画リンクが見つかりませんでした。別のURLをお試しください。</div>
</div>

<script>
(function(){
var PT={
  youtube:{name:'YouTube',badge:'b-yt',canEmbed:true,detect:function(u){var m=u.match(/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/);return m?{id:m[1],embed:'https://www.youtube.com/embed/'+m[1]+'?autoplay=1&rel=0',thumb:'https://img.youtube.com/vi/'+m[1]+'/mqdefault.jpg',open:u}:null;}},
  vimeo:{name:'Vimeo',badge:'b-vm',canEmbed:true,detect:function(u){var m=u.match(/vimeo\.com\/(?:video\/)?(\d+)/);return m?{id:m[1],embed:'https://player.vimeo.com/video/'+m[1]+'?autoplay=1',thumb:'https://vumbnail.com/'+m[1]+'.jpg',open:u}:null;}},
  nicovideo:{name:'ニコニコ',badge:'b-nc',canEmbed:true,detect:function(u){var m=u.match(/nicovideo\.jp\/watch\/((?:sm|nm|so|lv)\d+)/);return m?{id:m[1],embed:'https://embed.nicovideo.jp/watch/'+m[1]+'?autoplay=1',thumb:null,open:u}:null;}},
  mp4:{name:'MP4',badge:'b-mp4',canEmbed:true,detect:function(u){return /\.(mp4|webm|ogg|mov)(\?.*)?$/i.test(u)?{id:u,embed:u,thumb:null,open:u,isVideo:true}:null;}},
  twitter:{name:'X / Twitter',badge:'b-tw',canEmbed:false,detect:function(u){var m=u.match(/(?:twitter\.com|x\.com)\/\w+\/status\/(\d+)/);return m?{id:m[1],embed:null,thumb:null,open:u}:null;}},
  facebook:{name:'Facebook',badge:'b-fb',canEmbed:false,detect:function(u){return /facebook\.com\/(watch|video|reel)|fb\.watch/i.test(u)?{id:u,embed:null,thumb:null,open:u}:null;}},
  instagram:{name:'Instagram',badge:'b-ig',canEmbed:false,detect:function(u){var m=u.match(/instagram\.com\/(?:p|reel|tv)\/([A-Za-z0-9_-]+)/);return m?{id:m[1],embed:null,thumb:null,open:u}:null;}},
  tiktok:{name:'TikTok',badge:'b-tk',canEmbed:false,detect:function(u){return /tiktok\.com\/@[^/]+\/video\/\d+|vm\.tiktok\.com/.test(u)?{id:u,embed:null,thumb:null,open:u}:null;}}
};
function detectPlatform(url){for(var k in PT){var r=PT[k].detect(url);if(r)return Object.assign({},r,{platform:k,platformName:PT[k].name,badge:PT[k].badge,canEmbed:PT[k].canEmbed});}return null;}
var videos=[],curIdx=-1;
function setStatus(msg,show){var b=document.getElementById('statusBox');b.style.display=show?'block':'none';b.textContent=msg;}
function doAnalyze(){
  var url=document.getElementById('urlIn').value.trim();
  if(!url)return;
  document.getElementById('listArea').innerHTML='';
  document.getElementById('emptyBox').style.display='none';
  document.getElementById('ctrlBar').style.display='none';
  setStatus('AI がページを解析して動画リンクを抽出中...',true);
  document.querySelectorAll('.search-row button').forEach(function(b){b.disabled=true;});
  videos=[];curIdx=-1;
  var fetchHtml=function(){
    return fetch('https://api.allorigins.win/get?url='+encodeURIComponent(url))
      .then(function(r){return r.json();}).catch(function(){return{contents:''};});
  };
  fetchHtml().then(function(pd){
    var html=pd.contents||'';
    var title=url;
    var tm=html.match(/<title[^>]*>([^<]+)<\/title>/i);
    if(tm)title=tm[1].trim();
    var prompt='あなたはウェブページ内の動画リンクを検出する専門AIです。\nページURL: '+url+'\nページタイトル: '+title+'\nHTML（先頭12000文字）:\n'+html.slice(0,12000)+'\n\n【検出対象】以下の動画URLを全て抽出:\n- YouTube: youtube.com/watch?v=, youtu.be/, /embed/, /shorts/\n- X(Twitter): twitter.com/*/status/*, x.com/*/status/*\n- Facebook: facebook.com/watch, /video/, /reel/, fb.watch\n- Instagram: instagram.com/p/, /reel/, /tv/\n- TikTok: tiktok.com/@*/video/*\n- Vimeo: vimeo.com/数字\n- ニコニコ動画: nicovideo.jp/watch/sm*\n- MP4直リンク: .mp4, .webm\nHTMLに動画URLがない場合、ページURLがSNS投稿なら動画候補として含めてください。\n\n回答はJSONのみ:\n{"videos":[{"url":"完全URL","title":"タイトル（不明なら空文字）","platform":"youtube|twitter|facebook|instagram|tiktok|vimeo|nicovideo|mp4|other"}],"pageTitle":"'+title+'"}';
    return fetch('https://api.anthropic.com/v1/messages',{
      method:'POST',headers:{'Content-Type':'application/json'},
      body:JSON.stringify({model:'claude-sonnet-4-20250514',max_tokens:1500,messages:[{role:'user',content:prompt}]})
    }).then(function(r){return r.json();}).then(function(ad){
      var raw=(ad.content||[]).map(function(b){return b.text||'';}).join('');
      try{return JSON.parse(raw.replace(/```json|```/g,'').trim());}catch(e){return{videos:[]};}
    });
  }).then(function(parsed){
    var rawList=parsed.videos||[];
    videos=[];
    rawList.forEach(function(v){
      if(!v.url)return;
      var det=detectPlatform(v.url);
      if(det){videos.push(Object.assign({},det,{title:v.title||'',origUrl:v.url}));}
      else{videos.push({platform:'other',platformName:'外部リンク',badge:'b-other',canEmbed:false,id:v.url,embed:null,thumb:null,open:v.url,title:v.title||v.url,origUrl:v.url});}
    });
    setStatus('',false);
    document.querySelectorAll('.search-row button').forEach(function(b){b.disabled=false;});
    if(videos.length===0){document.getElementById('emptyBox').style.display='block';return;}
    renderAll();
    var cb=document.getElementById('ctrlBar');cb.style.display='flex';updateCtrl(0);
  }).catch(function(err){
    setStatus('エラー: '+err.message,true);
    document.querySelectorAll('.search-row button').forEach(function(b){b.disabled=false;});
  });
}
function renderAll(){
  var area=document.getElementById('listArea');area.innerHTML='';
  videos.forEach(function(v,i){
    var wrap=document.createElement('div');wrap.className='vcard';wrap.id='vc-'+i;
    var thumbHtml=v.thumb?'<img src="'+v.thumb+'" alt="" loading="lazy">':'<div class="vthumb-ico">▶</div>';
    var canTag=v.canEmbed?'<span class="can-tag">▶ 自動再生可</span>':'<span class="no-tag">↗ 遷移して再生</span>';
    var playBtn=v.canEmbed
      ?'<button class="btn-play" onclick="toggleEmbed('+i+')">▶ 再生</button>'
      :'<button class="btn-play" style="background:rgba(74,222,128,.12);border-color:rgba(74,222,128,.4);color:#4ade80;" onclick="openPage(\''+encodeURIComponent(v.open||v.origUrl)+'\')">↗ ページを開いて再生</button>';
    wrap.innerHTML='<div class="vcard-row"><div class="vthumb">'+thumbHtml+'</div><div class="vbody"><div class="vbody-top"><span class="badge '+v.badge+'">'+v.platformName+'</span>'+canTag+'</div><div class="vtitle">'+(v.title||v.id||'—')+'</div><div class="vurl">'+(v.open||v.origUrl||'').slice(0,80)+'</div><div class="vbtns">'+playBtn+'<button class="btn-open" onclick="openPage(\''+encodeURIComponent(v.open||v.origUrl)+'\')">↗ 元ページ</button></div></div></div><div class="embed-wrap" id="ew-'+i+'"></div>';
    area.appendChild(wrap);
  });
}
function toggleEmbed(i){
  var v=videos[i];var ew=document.getElementById('ew-'+i);
  if(ew.style.display==='block'){ew.style.display='none';ew.innerHTML='';return;}
  closeAllEmbeds();ew.style.display='block';
  if(v.isVideo||v.platform==='mp4'){
    ew.innerHTML='<video src="'+v.embed+'" controls autoplay style="width:100%;max-height:380px;background:#000" onended="onEnded('+i+')"></video>';
  }else{
    var h=(v.badge==='b-yt'||v.badge==='b-nc')?320:280;
    ew.innerHTML='<iframe src="'+v.embed+'" height="'+h+'" frameborder="0" allowfullscreen allow="autoplay;encrypted-media;picture-in-picture"></iframe>';
  }
  updateCtrl(i);curIdx=i;
  ew.scrollIntoView({behavior:'smooth',block:'nearest'});
}
function onEnded(i){if(document.getElementById('autoNext').checked)setTimeout(function(){goNext();},700);}
function closeAllEmbeds(){videos.forEach(function(_,i){var e=document.getElementById('ew-'+i);if(e){e.style.display='none';e.innerHTML='';}});}
function updateCtrl(i){if(i<0||i>=videos.length)return;document.getElementById('nowTitle').textContent=videos[i].title||videos[i].id||'—';document.getElementById('nowIdx').textContent=(i+1)+' / '+videos.length;curIdx=i;}
function goNext(){var n=curIdx+1;if(n>=videos.length){setStatus('全ての動画の確認が完了しました。',true);return;}closeAllEmbeds();if(videos[n].canEmbed){toggleEmbed(n);}else{updateCtrl(n);openPage(encodeURIComponent(videos[n].open||videos[n].origUrl));}}
function goPrev(){var p=curIdx-1;if(p<0)return;closeAllEmbeds();if(videos[p].canEmbed){toggleEmbed(p);}else{updateCtrl(p);}}
function openPage(enc){window.open(decodeURIComponent(enc),'_blank','noopener,noreferrer');}
document.getElementById('urlIn').addEventListener('keydown',function(e){if(e.key==='Enter')doAnalyze();});
})();
</script>
</body>
</html>
<?php endif; ?>