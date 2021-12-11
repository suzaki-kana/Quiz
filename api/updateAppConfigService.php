<?php
    session_start();
    require_once("./common.php");
    /*
     * AppConfigを更新するサービス
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

    $myConst->time_limit = $contents['timeLimit'];

    //上書き保存
    $myConst->asXml($fileConfigPath);

    session_write_close();
    exit;
?>