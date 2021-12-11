<?php
    session_start();    
    require_once("./common.php");
    /*
     * 手札をランダムに決定するサービス
     */

     // 共通初期化処理
    if(initialize('2') == false){
        return;
    }

    // 文字コード設定
    header('Content-Type: application/json; charset=UTF-8');

    $backUrl = '../forms/ExciteGame.php';

    // シャッフル制限時間を確認
    if(getTimeDiff((string)$myConst->time_limit) >= 0){
        // 手札シャッフル
        ShuffleCard((string)$myConst->max_card, $_SESSION['PrNo'], true);
    }else{
        header('Location: ' . $backUrl, true, 301);
    }

    session_write_close();

    header('Location: ' . $backUrl, true, 301);
    exit;
?>