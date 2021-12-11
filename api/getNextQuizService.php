<?php
    session_start();
    require_once("./common.php");
    /*
     * 新しいクイズを選出するサービス
     */
     
    // 共通初期化処理
    if(initialize('1') == false){
        return;
    }

    // 文字コード設定
    header('Content-Type: application/json; charset=UTF-8');

    $result = array();

    // 現在のクイズファイル名を取得
    $currentQuiz = getFileNameOne($filesQuizNewPath);

    // 未使用のクイズリスト取得
    $allList = getFilesInfo($filesQuizListPath, 1, 'basename');

    if(empty($currentQuiz) == false){
        // 現在のクイズを提供用の使用済みとして移動 quiz/current → quiz/used
        rename($filesQuizNewPath . $currentQuiz, $filesQuizUsedPath . $currentQuiz);
    }
    // 回答クリア
    removeDir($playerAnsPath);
    // 正答者のクリア
    removeDir($filesQuizAnswerPath);
    // 回答者のクリア
    removeDir($filesQuizChallengerPath);

    if(count($allList) == 0){
        $result['quiz'] = null;
    }else{
        // 乱数シード生成
        srand(time());
        // クイズ決定
        $nextQuiz = $allList[rand(0, count($allList) - 1)];
    
        // 次のクイズを提供フォルダへ移動 quiz/list → quiz/current
        rename($filesQuizListPath . $nextQuiz, $filesQuizNewPath . $nextQuiz);

        $result['quiz'] = $nextQuiz;
    }

    /* 結果の出力 */
    echo json_encode($result, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);

    session_write_close();
    exit;

?>