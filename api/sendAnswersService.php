<?php
    session_start();
    require_once("./common.php");
    /*
     * 正答者送信サービス
     */
     
    // 共通初期化処理
    if(initialize('1') == false){
        return;
    }

    // 文字コード設定
    header('Content-Type: application/json; charset=UTF-8');

    // POSTされたJSON文字列を取り出し
    $json = file_get_contents("php://input");
    // JSON文字列をobjectに変換
    $contents = json_decode($json, true);

    $prnoList = $contents['answers'];

    // 前回分正答者の削除
    removeDir($filesQuizAnswerPath);

    // プレイヤー側への開示・提供
    if(empty($prnoList)){
        mkdir($filesQuizAnswerPath . 'dummy', 0777);
    }else{
        mkdir($filesQuizAnswerPath . join('_', $prnoList), 0777);
    }

    // 回答者の削除(player/valid → player/used)
    foreach(explode('_', getFileNameOne($filesQuizChallengerPath)) as $prno){
        $imgFile = getFileNameOne($imagePath . $prno);
        rename($imageUserPath . $imgFile, $imageUsedPath . $imgFile);
    }

    session_write_close();
    exit;

?>