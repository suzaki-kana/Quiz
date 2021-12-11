<?php
    session_start();
    require_once("./common.php");
    /*
     * スコアを集計するサービス
     */
     
    // 共通初期化処理
    if(initialize('1') == false){
        return;
    }

    // 文字コード設定
    header('Content-Type: application/json; charset=UTF-8');

    $result = array();

    // プレイヤー情報の読み込み（マスタファイルより）
    $infos = getPlayerInfo();

    // backupのscoreより参会者分のscore集計
    foreach(glob($filesBackupPath . '*') as $file){
        $prno = pathinfo($file)['basename'];
        if($prno == 'admin'){
            continue;
        }
        $info = $infos[$prno];
        $result[] = 
            array('rank' => 0,
                  'img' => $imagePathBase . getFileNameOne($imagePath . $prno),
                  'name' => $info['name'],
                  'score' => (int)file_get_contents($filesBackupPath . $prno . '/' . $backupScorePath));
    }

    // 値で降順ソート
    array_multisort(array_column($result, 'score'), SORT_DESC, $result);
    $rank = 0;
    $rankCnt = 1;
    $score = -1;
    foreach($result as $key => $value){
        if($score == $value['score']){
            $rankCnt += 1;
        }else{
            $rank += $rankCnt;
            $rankCnt = 1;
            $score = $value['score'];
        }
        $result[$key]['rank'] = $rank;
    }

    /* 結果の出力 */
    $output = json_encode($result, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
    backupSave($filesRankingPath . $rankingFile, $output);
    echo $output;

    session_write_close();
    exit;
?>