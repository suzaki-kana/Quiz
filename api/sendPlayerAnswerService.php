<?php
    session_start();
    require_once("./common.php");
    /*
     * プレイヤー側の状態をチェックするサービス
     */

    // 共通初期化処理
    if(initialize('2') == false){
        return;
    }

    // 文字コード設定
    header('Content-Type: application/json; charset=UTF-8');

    // POSTされたJSON文字列を取り出し
    $json = file_get_contents("php://input");
    // JSON文字列をobjectに変換
    $contents = json_decode($json, true);

    // 既存削除
    $path = getFileNameOne($playerAnsPath . $contents['prno']);
    if(empty($path) == false){
        system("rm -rf ${playerAnsPath}${path}");
    }

    // 今回回答保存
    mkdir($playerAnsPath . $contents['prno'] . '_' . $contents['answer']);

    exit;
?>