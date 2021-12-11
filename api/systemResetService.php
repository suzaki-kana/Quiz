<?php
    session_start();
    require_once("./common.php");
    /*
     * システムリセットサービス
     */
     
    // 共通初期化処理
    if(initialize('1') == false){
        return;
    }

    /* クイズリセット */
    foreach(getFilesInfo($filesQuizUsedPath, 1, 'basename') as $file) {
        rename($filesQuizUsedPath . $file, $filesQuizListPath . $file);
    }
    foreach(getFilesInfo($filesQuizNewPath, 1, 'basename') as $file) {
        rename($filesQuizNewPath . $file, $filesQuizListPath . $file);
    }
    removeDir($filesQuizChallengerPath);
    removeDir($playerAnsPath);
    removeDir($filesQuizAnswerPath);
    removeDir($filesRankingPath);

    /* バックアップリセット */
    removeDir($filesBackupPath);

    /* 有効プレイヤーリセット */
    removeDir($imageUserPath);
    removeDir($imageUsedPath);
    removeDir($imagePath);
    
    // foreach(glob($imagePathBase . '*') as $file){
    //     if(is_file($file)){
    //         mkdir($imageUserPath . pathinfo($file)['basename'], 0777);
    //         mkdir($imagePath . pathinfo($file)['basename'], 0777);
    //     }
    // }

?>