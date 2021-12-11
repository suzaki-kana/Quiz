<?php
    session_start();
    require_once("../api/common.php");

    // 共通初期化処理
    if(initialize('2') == false){
        return;
    }

    // 回答者数
    $challengerMax = (int)$myConst->max_challenger;    
    // ゲーム開始時間を取得
    $timeLimit = (string)$myConst->time_limit;
    //timeLimit = "00:00";

    $cycleInterval = 1500;
    $cycleIntervalCard = 300;

    // ログインユーザ情報取得
    $prno = $_SESSION['PrNo'];
    $myInfo = $_SESSION['MyInfo'];

    // スコア取得
    $score = file_get_contents($filesBackupPath . $prno . '/' . $backupScorePath);
    // 基準時間取得
    $localBaseTime = filemtime($filesBackupPath . $prno . '/' . $backupScorePath);

    // 手札の取得
    $cards = json_decode(file_get_contents($filesBackupPath . $prno .'/' . $backupCardPath), true);
    if(is_array($cards)){
        $maxCard = count($cards);
    }else{
        $maxCard = 0;
    }

    // 過去回答者情報取得
    $history = getFilesInfo($imageUsedPath, 0, 'filename');
?>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>クイズ大会</title>
    <link rel="stylesheet" type="text/css" href="../css/base.css">
    <link rel="stylesheet" type="text/css" href="../css/card.css">
    <link rel="stylesheet" type="text/css" href="../css/game.css">
    <link rel="stylesheet" type="text/css" href="../css/dialog.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="../script/common.js"></script>
    <script src="../script/dialog.js"></script>
    <script>
        /**
         * 画面Load時処理
         */
        function document_load(){

            /* [ログアウト]ボタンClick */
            $('#btnLogout').on('click', function(){
                window.location.href = '../forms/Login.php';
            });

            /* [リセマラ]ボタンClick */
            $('#btnShuffle').on('click', function(){
                window.location.href = '../api/dealCardsService.php';
            });

            dialogInitialize("<?php print($filesQuizNewPath); ?>","<?php print($filesQuizOptPath); ?>");

            <?php if(empty($history) == false){
                // 過去回答者のスポット
                foreach($history as $keyNo){
                    print('$(".card-w' . "[prno='${keyNo}']" . '").addClass("spot_histry");' . "\n");
                }
            } ?>

            /* カードオープンイベントトリガー */
            setTimeout(function(){ani_open(1)}, 500);

            /* シャッフル可能制御イベント 開始 */
            validShuffle(1);
        }

        /**
         * カードオープンアニメーション
         */
        function ani_open(i){
            if(i <= <?php print($maxCard) ?>){
                $('.card-b[bno="' + i + '"]').addClass('ani_open_1');
                $('.card-f[fno="' + i + '"]').addClass('ani_open_2');
                // Timer設定（0.6秒毎に処理）
                setTimeout(function(){ani_open(i + 1)}, <?php echo $cycleIntervalCard ?>);
            }
        }

        /**
         * シャッフル可能制御イベント
         */
        function validShuffle(mode){
            var secs = getTimeDeff("<?php print($timeLimit) ?>");
            if(secs > 0){
                // 制限時間の更新
                $('#validTime').text(secs);
                setTimeout(function(){validShuffle(0), 1000})
            }else{
                // シャッフルボタンの利用不可設定
                $('#validTime').text(0);
                $('#btnShuffle').prop('disabled', true)

                /* 状態チェックAPI サイクル実行設定 */
                if(mode == 1){
                    statusCheck();
                }else{
                    setTimeout(function(){statusCheck()}, <?php echo $cycleInterval ?>);
                }
            };
        }

        /**
         * 状態チェックAPI サイクル実行設定
         */
        function statusCheck(){
            $.ajax({
                /* サーバに送信するリクエストの設定 */
                type:'POST', // リクエストのタイプ
                url: '../api/statusCheckService.php', // URL
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify({
                    current: $('#quizNo').val(),
                    score: $('#scoreVal').text(),
                    checkTimeAnswer: $('#checkTimeAnswer').val(),
                    checkTimeChallenger: $('#checkTimeChallenger').val(),
                    checkTimeRanking: $('#checkTimeRanking').val()
                }) // 送信パラメータ値
            })
            .done(function(data){
                updateDisplayStatus(data);
            })
            .fail(function(jqXHR, textStatus, errorThrown){
                /* 通信に失敗した場合の処理 */
            })
            .always(function(){
                /* 結果にかかわらず常に実行する処理 */
                setTimeout(function(){statusCheck()}, <?php echo $cycleInterval ?>);
            });
        }

        /**
         * 状態チェックAPI 通信に成功した場合の処理
         * @param object data : 
         */
        function updateDisplayStatus(data){
            let initFlg = $('#checkTimeChallenger').val();

            if(data['checkTimeChallenger'] != null){
                // 回答者のチェック時間更新
                $('#checkTimeChallenger').val(data['checkTimeChallenger']);
            }
            if(data['challenger'] != null){
                // 回答者のスポット解除
                $('.spot').removeClass('spot');
                // 回答者をスポット
                reflash(data['challenger']);
            }
            if(data['checkTimeAnswer'] != null){
                // 正答者のチェック時間更新
                $('#checkTimeAnswer').val(data['checkTimeAnswer']);
                // 正答者のスポット解除
                $('.show').removeClass('show');
            }
            if(data['answers'] != null){
                // 正答者をスポット
                $.each(data['answers'], function(i,x){
                    $('.medal[prno="' + x + '"]').addClass('show')
                });
                // 回答者のスポット→過去回答者のスポットへ変更
                $('.spot').toggleClass('spot_histry');
                $('.spot').removeClass('spot');
                // 回答確認可能に設定
                $('.quiz-body').attr('mode', '1');
                changeMode('1');
            }
            if(data['checkTimeRanking'] != null){
                // 回答者のチェック時間更新
                $('#checkTimeRanking').val(data['checkTimeRanking']);
            }
            if(data['current'] != null){
                $('#quizNo').val(data['current']);
            }
            if(data['quiz'] != null){
                // 問題を更新
                $('.quiz-body').attr("src", "<?php print($filesQuizNewPath); ?>" + data['quiz']);
                setAttrFile(data['quiz']);
                if(initFlg != 0){
                    // 回答者リセット
                    $('.r1').attr('src', '<?php print($imageCardPath); ?>');
                    $('.r2').text(' ');
                    // 回答確認不可能にリセット
                    $('.quiz-body').attr('mode', '0');
                    // 回答不可にリセット
                    $('.quiz-body').attr('flg', '0');
                    // 正答者のスポット解除
                    $('.show').removeClass('show');
                }
            }
            if(data['score'] != null){
                // スコアの更新
                $('#scoreVal').text(data['score']);
            }
            if(data['rankingList'] != null){
                // ランキング表示
                createRankingList(data['rankingList']);
            }
        }

        /**
         * リフレッシュ
         */
        function reflash(data){
            $('.quiz-body').attr('flg', '');
            $.each(data, function(key, value) {
                var keyNo = '[no="' + value['no'] +'"]';
                $('.card-f' + keyNo).attr('src', "<?php print($imagePathBase) ?>" + value['img']);
                $('.card-w' + keyNo).attr('src', "<?php print($imagePathBase) ?>" + value['img']);
                $('.info-name' + keyNo).text(value['name']);
                $('.info-dept' + keyNo).text(value['dept']);
                $('.info-post' + keyNo).text(value['post']);
                $('.medal' + keyNo).attr('prno', key);
                $('.card-w[prno="' + key + '"]').addClass('spot');
                if(key == $('#PrNo').text()){
                    $('.quiz-body').attr('flg', '1');
                    modalOpenQuiz('.quiz-body');
                }
            });
        }
    </script>
</head>

<body onload="document_load()">
    <div class="flex-box">
        <div class="info-header">
            <!-- 情報表示部 -->
            <div class="flex-box">
                <button id="btnLogout" class="normal">logout</button>
                <div class="label-title">Player</div>
                <div class="label" id="PrNo"><?php print($prno); ?></div>
                <div class="label" id="PrNm"><?php print($myInfo['name']); ?></div>
            </div>
            <div class="flex-box">
                <button id="btnShuffle" class="normal">リセマラ</button>
                <div class="label">使用終了</div>
                <div class="label">まであと</div>
                <div class="flex-box">
                    <div class="label-time" id="validTime">0</div>
                    <div class="label">秒</div>
                </div>
            </div>
            <div class="flex-box">
            <div class="label-title">score</div>
                <div class="label-score" id="scoreVal"><?php print($score); ?></div>
                <input type="hidden" id="checkTimeAnswer" value="<?php print($localBaseTime) ?>" />
                <input type="hidden" id="checkTimeChallenger" value="0" />
                <input type="hidden" id="checkTimeRanking" value="<?php print(filemtime($filesRankingPath)); ?>" />
            </div>
        </div>

        <div class="flex-box info-header">
            <div class="label-title">回答者</div>
            <?php for($key = 1; $key <= $challengerMax; $key++){ ?>
            <div class="frame-card">
                <div class="card">
                    <img class="card-f r1"    no="<?php print($key); ?>" src="<?php print($imageCardPath); ?>" />
                    <img class="medal"        no="<?php print($key); ?>" src="<?php print($imageMedalPath); ?>" />
                    <div class="info-name r2" no="<?php print($key); ?>"></div>
                    <div class="info-dept r2" no="<?php print($key); ?>"></div>
                    <div class="info-post r2" no="<?php print($key); ?>"></div>
                    <div class="card-w r1"    no="<?php print($key); ?>" src="<?php print($imageCardPath); ?>"></div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>

    <!-- 回答者表示部 -->
    <div class="info-quize">
        <!-- 問題表示部 -->
        <div class="flex-box">
            <div class="label-title">問題</div>
            <img class="quiz-body js-modal-open" src="<?php print($imageQuizPath); ?>" flg="" mode=""></img>
            <input type="hidden" id="quizNo" value="0" />
        </div>
    </div>

    <!-- 手札表示部 -->
    <div class="flex-box">
<?php if($maxCard == 0){ ?>
        <div>準備中。。。</div>
<?php }else{ ?>
<?php foreach($cards as $key => $value) { ?>
        <div class="frame-card">
            <div class="card">
                <img class="card-f" fno="<?php print($value['no']); ?>" src="<?php print($imagePathBase . $value['img']); ?>" />
                <img class="card-b" bno="<?php print($value['no']); ?>" src="<?php print($imageCardPath); ?>" />
                <img class="medal" prno="<?php print($key); ?>" src="<?php print($imageMedalPath); ?>" />
                <div class="info-name"><?php print($value['name']); ?></div>
                <div class="info-dept"><?php print($value['dept']); ?></div>
                <div class="info-post"><?php print($value['post']); ?></div>
                <div class="card-w" prno="<?php print($key); ?>" src="<?php print($imagePathBase . $value['img']); ?>"></div>
            </div>
        </div>
<?php }?>
<?php }?>
    </div>

    <?php include ('../forms/DialogQuiz.php'); ?>
    <?php include ('../forms/DialogRanking.php'); ?>

</body>
</html>
