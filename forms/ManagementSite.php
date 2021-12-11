<?php
    session_start();
    require_once("../api/common.php");

    // 共通初期化処理
    if(initialize('1') == false){
        return;
    }

    // 回答者数
    $challengerMax = (int)$myConst->max_challenger;    
    // ゲーム開始時間を取得
    $timeLimit = (string)$myConst->time_limit;

    // 現在のクイズを取得
    $localQuizPath = getFileNameOne($filesQuizNewPath);
    if(empty($localQuizPath)){
        $localQuizPath = $imageQuizPath;
    }else{
        $localQuizExist = 1;
        $localQuizPath = $filesQuizNewPath . $localQuizPath;
    }

    // 回答者の現在情報取得
    $savePath = getFileNameOne($filesQuizChallengerPath);
    if(empty($savePath) == false){
        $localCardData = file_get_contents($filesQuizChallengerPath . $savePath);
    }

    // 正答者の現在情報取得
    $localAnswers = explode('_', getFileNameOne($filesQuizAnswerPath));
    if(empty($localAnswers[0])){
        $localAnswers = null;
    }

    // 参加人数
    $localValidUserCnt = count(glob($filesBackupPath . '*'));
?>

<html lang="ja">
<head>
    <title>クイズ大会 運営サイト</title>
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="../css/base.css">
    <link rel="stylesheet" type="text/css" href="../css/manage.css">
    <link rel="stylesheet" type="text/css" href="../css/dialog.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="../script/common.js"></script>
    <script src="../script/dialog.js"></script>
    <script type="text/javascript">
        /**
         * 画面Load時処理
         */
        function document_load(){

            /* [ログアウト]ボタンClick */
            $('#btnLogout').on('click', function(){
                window.location.href = '../forms/Login.php';
            });

            /* [システムリセット]ボタンClick */
            $('#btnReset').on('click', function(){
                ProcReset();
            });
            /* [変更]ボタンClick */
            $('#btnEditConfig').on('click', function(){
                procEditSettings();
            });
            /* [新しいクイズ]ボタンClick */
            $('#btnQuiz').on('click', function(){
                ProcNewQuiz();
            });
            /* [回答者選出]ボタンClick */
            $('#btnChallenger').on('click', function(){
                ProcNewChallebger();
            });
            /* [回答確認]ボタンClick */
            $('#btnCheck').on('click', function(){
                ProcCheckAnswer();
            });
            /* [回答者送信]ボタンClick */
            $('#btnAnswer').on('click', function(){
                ProcSendAnswer();
            });
            /* [結果集計]ボタンClick */
            $('#btnResult').on('click', function(){
                ProcCalcResult();
            });

            dialogInitialize("<?php print($filesQuizNewPath); ?>","<?php print($filesQuizOptPath); ?>");

            <?php if($localQuizExist == 1){
                // クイズボタン非活性
                print("$('#btnQuiz').prop('disabled', true);" . "\n");
            }else{
                // 回答者選出ボタン非活性
                print("$('#btnChallenger').prop('disabled', true);" . "\n");
                // 回答確認ボタン非活性
                print("$('#btnCheck').prop('disabled', true);" . "\n");
                // 正答者送信ボタン非活性
                print("$('#btnAnswer').prop('disabled', true);" . "\n");
            }?>
            <?php if(empty($localCardData)){
                // 回答確認ボタン非活性
                print("$('#btnCheck').prop('disabled', true);" . "\n");
                // 正答者送信ボタン非活性
                print("$('#btnAnswer').prop('disabled', true);" . "\n");
            }else{
                // 復元
                print("reflash(${localCardData});" . "\n");
                // クイズボタン非活性
                print("$('#btnQuiz').prop('disabled', true);" . "\n");
                // 正答者送信ボタン非活性
                print("$('#btnAnswer').prop('disabled', true);" . "\n");
            }?>

            <?php if(empty($localAnswers) == false){ 
                // クイズボタン活性
                print("$('#btnQuiz').prop('disabled', false);" . "\n");
                // 回答者選出ボタン非活性
                print("$('#btnChallenger').prop('disabled', true);" . "\n");
                // 回答確認ボタン非活性
                print("$('#btnCheck').prop('disabled', true);" . "\n");
                // 正答者送信ボタン非活性
                print("$('#btnAnswer').prop('disabled', true);" . "\n");

                foreach($localAnswers as $value){
                    print("$('.medal[prno=" . '"' . $value .'"' . "]').addClass('show');" . "\n");
                }
            } ?>

            /* ゲーム開始時間カウントダウン 表示 */
            countdown();
        }

        /**
         * ゲーム開始時間カウントダウン
         */
        function countdown(){
            var secs = getTimeDeff("<?php print($timeLimit); ?>");
            if(secs > 0){
                // 制限時間の更新
                $('#validTime').text(secs);
                setTimeout(function(){countdown(), 1000});
            }else{
                $('#validTime').text(0);
            }
        }

        /**
         * 正答者クリック
         */
        function answerCardClick(key){
            let id = '.medal[no="' + key + '"]';
            if($(id).hasClass('show')){
                $(id).removeClass('show');
            }else{
                $(id).addClass('show');
            }
        }

        /**
         * リフレッシュ
         */
        function reflash(data){
            $('.show').removeClass('show');
            $.each(data, function(key, value) {
                var keyNo = '[no="' + value['no'] +'"]';
                $('.card-f' + keyNo).attr('src', "<?php print($imagePathBase) ?>" + value['img']);
                $('.info-name' + keyNo).text(value['name']);
                $('.info-dept' + keyNo).text(value['dept']);
                $('.info-post' + keyNo).text(value['post']);
                $('.info-answer' + keyNo).attr('prno', key);
                $('.medal' + keyNo).attr('prno', key);
            });
            $('.quiz-body').attr('src', "<?php print($localQuizPath) ?>");
        }

        /**
         * システムリセット
         */
        function ProcReset(){
            if($('#txtResetText').val() != $('#resetText').text()){
                // 誤操作対策
                return;
            }

            $.ajax({
                /* サーバに送信するリクエストの設定 */
                url: '../api/systemResetService.php' // URL
            })
            .done(function(){
                /* 通信に成功した場合の処理 */
                window.location.href = '../forms/Login.php';
            })
            .fail(function(jqXHR, textStatus, errorThrown){
                /* 通信に失敗した場合の処理 */
                alert('<?php print(getConfigMessage("E004")) ?>');
            })
            .always(function(){
                /* 結果にかかわらず常に実行する処理 */
            });
        }
        /** 
         * 設定更新
        */
        function procEditSettings(){
            let val = $('#newTimelimit').val();
            if(!val){
                // 誤操作対策
                return;
            }
            $.ajax({
                /* サーバに送信するリクエストの設定 */
                type:'POST', // リクエストのタイプ
                url: '../api/updateAppConfigService.php', // URL
                contentType: 'application/json',
                dataType: 'text',
                data: JSON.stringify({
                    timeLimit: val
                }) // 送信パラメータ値
            })
            .done(function(){
                /* 通信に成功した場合の処理 */
                window.location.href = '../forms/ManagementSite.php';
            })
            .fail(function(jqXHR, textStatus, errorThrown){
                /* 通信に失敗した場合の処理 */
                alert('<?php print(getConfigMessage("E004")) ?>');
            })
            .always(function(){
                /* 結果にかかわらず常に実行する処理 */
            });
        }
        /**
         * 新しいクイズの取得・表示
         */
        function ProcNewQuiz(){
            $.ajax({
                /* サーバに送信するリクエストの設定 */
                url: '../api/getNextQuizService.php' // URL
            })
            .done(function(data){
                /* 通信に成功した場合の処理 */
                // 正答者マーククリア
                $('.show').removeClass('show');
                // 回答者カードクリア
                $('.card-f').attr('src', "<?php print($imageCardPath) ?>");
                $('.info-name').text('');
                $('.info-dept').text('');
                $('.info-post').text('');
                $('.info-answer').text('');
                // クイズ表示
                if(data['quiz'] == null){
                    $('.quiz-body').attr("src", "<?php print($$imageQuizPath); ?>");
                    setAttrFile('');
                }else{
                    $('.quiz-body').attr("src", "<?php print($filesQuizNewPath); ?>" + data['quiz']);
                    setAttrFile(data['quiz']);
                }
                // クイズボタン非活性
                $('#btnQuiz').prop('disabled', true);
                // 回答者選出ボタン活性
                $('#btnChallenger').prop('disabled', false);
            })
            .fail(function(jqXHR, textStatus, errorThrown){
                /* 通信に失敗した場合の処理 */
                alert('<?php print(getConfigMessage("E004")) ?>');
            })
            .always(function(){
                /* 結果にかかわらず常に実行する処理 */
            });
        }
        /**
         * 回答者選出
         */
        function ProcNewChallebger(){
            $.ajax({
                /* サーバに送信するリクエストの設定 */
                url: '../api/challengerSelectService.php', // URL
                contentType: 'application/json'
            })
            .done(function(data){
                /* 通信に成功した場合の処理 */
                // 正答者送信ボタン活性
                $('#btnCheck').prop('disabled', false);
                // 表示内容更新
                reflash(data['challenger']);
            })
            .fail(function(jqXHR, textStatus, errorThrown){
                /* 通信に失敗した場合の処理 */
                alert('<?php print(getConfigMessage("E004")) ?>');
            })
            .always(function(){
                /* 結果にかかわらず常に実行する処理 */
            });
        }
        /**
         * 回答確認
         */
        function ProcCheckAnswer(){
            $.ajax({
                /* サーバに送信するリクエストの設定 */
                url: '../api/checkAnswerService.php', // URL
                contentType: 'application/json'
            })
            .done(function(data){
                /* 通信に成功した場合の処理 */
                // 正答者送信ボタン活性
                $('#btnAnswer').prop('disabled', false);
                // 回答内容表示
                $.each(data, function(key, value){
                    $('.info-answer[prno="' + key + '"]').text(value);
                });
            })
            .fail(function(jqXHR, textStatus, errorThrown){
                /* 通信に失敗した場合の処理 */
                alert('<?php print(getConfigMessage("E004")) ?>');
            })
            .always(function(){
                /* 結果にかかわらず常に実行する処理 */
            });
        }
        /**
         * 正答者送信
         */
        function ProcSendAnswer(){
            let prno = [];
            $('.show').each(function(i,elem){
                prno.push($(elem).attr('prno'));
            });
            $.ajax({
                /* サーバに送信するリクエストの設定 */
                type:'POST', // リクエストのタイプ
                url: '../api/sendAnswersService.php', // URL
                contentType: 'application/json',
                dataType: 'text',
                data: JSON.stringify({
                    answers: prno
                }) // 送信パラメータ値
            })
            .done(function(data){
                /* 通信に成功した場合の処理 */
                // クイズボタン活性
                $('#btnQuiz').prop('disabled', false);
                // 回答者選出ボタン非活性
                $('#btnChallenger').prop('disabled', true);
                // 正答者送信ボタン非活性
                $('#btnAnswer').prop('disabled', true);

                alert('<?php print(getConfigMessage("E005",["正答者送信"])) ?>');
            })
            .fail(function(jqXHR, textStatus, errorThrown){
                /* 通信に失敗した場合の処理 */
                alert('<?php print(getConfigMessage("E004")) ?>');
            })
            .always(function(){
                /* 結果にかかわらず常に実行する処理 */
            });
        }
        /**
         * 結果集計
         */
        function ProcCalcResult(){
            $.ajax({
                /* サーバに送信するリクエストの設定 */
                url: '../api/calcRankingService.php', // URL
                contentType: 'application/json'
            })
            .done(function(data){
                /* 通信に成功した場合の処理 */
                createRankingList(data);
            })
            .fail(function(jqXHR, textStatus, errorThrown){
                /* 通信に失敗した場合の処理 */
                alert('<?php print(getConfigMessage("E004")) ?>');
            })
            .always(function(){
                /* 結果にかかわらず常に実行する処理 */
            });
        }
    </script>
</head>

<body onload="document_load()">

    <!-- システムリセット -->
    <div class="flex-box">
        <button id="btnLogout" class="normal">ログアウト</button>
        <div class="label">リセット：</div><div class="label" id="resetText">1234</div>
        <input type="text" id="txtResetText" placeholder="リセットテキストを入力" />
        <button id="btnReset" class="normal">リセット</button>
    </div>

    <!-- ゲーム開始までのカウントダウン -->
    <div class="flex-box">
        <div class="label">あと</div>
        <div class="label-time" id="validTime">0</div>
        <div class="label">秒</div>
        <input type="text" id="newTimelimit" placeholder="<?php echo $timeLimit ?>" />
        <button id="btnEditConfig" class="normal">変更</button>
    </div>

    <!-- ゲーム開始までのカウントダウン -->
    <div class="flex-box">
        <div class="label-title">参加人数</div>
        <div class="label"><?php echo $localValidUserCnt ?>人</div>
    </div>
    
    <!-- クイズ表示 -->
    <button id="btnQuiz" class="normal">クイズ</button>
    <div class="label-title">問題</div>
    <img class="quiz-body js-modal-open" src="<?php print($imageQuizPath); ?>" flg="" mode="1"></img>
    <input type="hidden" id="quizNo" />
    
    <!-- 回答者選出 -->
    <div><button id="btnChallenger" class="normal">回答者選出</button></div>

    <div id ="answerCards" class="flex-box">
        <?php for($key = 1; $key <= $challengerMax; $key++){ ?>
        <div class="frame-card">
            <div class="card" onclick="answerCardClick(<?php print($key); ?>)">
                <img class="card-f"      no="<?php print($key); ?>" src="<?php print($imageCardPath); ?>" />
                <img class="medal"       no="<?php print($key); ?>" src="<?php print($imageMedalPath); ?>" />
                <div class="info-name"   no="<?php print($key); ?>"></div>
                <div class="info-dept"   no="<?php print($key); ?>"></div>
                <div class="info-post"   no="<?php print($key); ?>"></div>
                <div class="info-answer" no="<?php print($key); ?>"></div>
                <div class="card-fw"></div>
            </div>
        </div>
        <?php } ?>
    </div>

    <!-- 正答者送信 -->
    <div class="flex-box">
        <button id="btnCheck" class="normal">回答確認</button>
        <button id="btnAnswer" class="normal">正答者送信</button>
    </div>
    
    <!-- 結果集計 -->
    <div><button id="btnResult" class="normal">結果集計</button></div>

    <?php include ('../forms/DialogQuiz.php'); ?>
    <?php include ('../forms/DialogRanking.php'); ?>

</body>
</html>