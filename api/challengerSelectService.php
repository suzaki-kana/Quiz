<?php
    session_start();
    require_once("./common.php");
    /*
     * 回答者を選出するサービス
     */
     
    // 共通初期化処理
    if(initialize('1') == false){
        return;
    }

    // 文字コード設定
    header('Content-Type: application/json; charset=UTF-8');

    $result = array();

    // 前回分の削除
    removeDir($filesQuizChallengerPath);

    // ランダム選出
    $challenger = ShuffleCard((string)$myConst->max_challenger);
    
    // プレイヤー側への開示・提供
    $filename = $filesQuizChallengerPath . join('_', array_keys($challenger));
    backupSave($filename , json_encode($challenger));

    /* 結果の出力 */
    $result['challenger'] =  $challenger;
    echo json_encode($result, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);

    session_write_close();
    exit;

?>