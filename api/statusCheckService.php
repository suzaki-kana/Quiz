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

    $result = array();

    $current = count(glob($filesQuizUsedPath . '*')) + 1;
    if($current != (int)$contents['current']){
        /* 問題の確認 */
        $newQuiz = getFileNameOne($filesQuizNewPath);
        if(empty($newQuiz) == false){
            // クイズパスを設定
            $result['quiz'] = $newQuiz;
            // 現在の問題番号を更新
            $result['current'] = $current;
        }
    }

    $localUpdTime = (int)$contents['checkTimeChallenger'];
    $serverUpdTime = filemtime($filesQuizChallengerPath);
    if($localUpdTime < $serverUpdTime){
        /* 回答者の更新確認 */
        $result['challenger'] = json_decode(file_get_contents($filesQuizChallengerPath . getFileNameOne($filesQuizChallengerPath)), true);
        $result['checkTimeChallenger'] = $serverUpdTime;
    }

    /* 正答者の更新確認 */
    $localUpdTime = (int)$contents['checkTimeAnswer'];
    $serverUpdTime = filemtime($filesQuizAnswerPath);
    if($localUpdTime < $serverUpdTime){
        $score = (int)$contents['score'];
        $cards = json_decode(file_get_contents($filesBackupPath . $_SESSION['PrNo'] .'/' . $backupCardPath), true);

        $result['answers'] = explode('_', getFileNameOne($filesQuizAnswerPath));

        /* スコア更新 */
        foreach($result['answers'] as $value){
            if(array_key_exists($value, $cards)){
                $score += 2;
            }else if($_SESSION['PrNo'] == $value){
                $score += 3;
            }
        }
        foreach(explode('_', getFileNameOne($filesQuizChallengerPath)) as $value){
            if(array_key_exists($value, $cards)){
                $score += 1;
            }
        }
        if($score != (int)$contents['score']){
            $result['score'] = $score;
            // スコアバックアップ
            backupSave($filesBackupPath . $_SESSION['PrNo'] . '/' . $backupScorePath, $score);
        }

        $result['checkTimeAnswer'] = $serverUpdTime;
    }

    $localUpdTime = (int)$contents['checkTimeRanking'];
    $serverUpdTime = filemtime($filesRankingPath);
    if($localUpdTime < $serverUpdTime){
        if(file_exists($filesRankingPath . $rankingFile)){
            $result['rankingList'] = json_decode(file_get_contents($filesRankingPath . $rankingFile), true);
        }
        $result['checkTimeRanking'] = $serverUpdTime;
    }

    /* 結果の出力 */
    echo json_encode($result, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);

    exit;
?>