<?php
    session_start();
    require_once("./common.php");
    /*
     * 回答者の回答を確認するサービス
     */
     
    // 共通初期化処理
    if(initialize('1') == false){
        return;
    }

    // 文字コード設定
    header('Content-Type: application/json; charset=UTF-8');

    $result = array();

    // 回答内容取得
    foreach(getFilesInfo($playerAnsPath, 2, 'basename') as $dir){
        $wk = explode('_', $dir);
        $result[$wk[0]] = $wk[1];
    }
    
    echo json_encode($result, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);

    session_write_close();
    exit;

?>