<?php
// 設定ファイルのパス
$fileConfigPath = '../app.config.xml';
// Files.Masterフォルダパス
$filesInfoPath = '../files/master/player_info.csv';
// Files.Backup
$filesBackupPath = '../files/backup/';
// 手札バックアップファイル名
$backupCardPath = 'card.json';
// スコアバックアップファイル名
$backupScorePath = 'score.txt';
// クイズファイル名
$quizBodyFile = 'QuizNoimage.jpg';
// 画像配置パス
$imagePathBase = '../image/player/';
$imageUserPath = $imagePathBase . 'valid/';
$imagePath = $imagePathBase . 'list/';
$imageUsedPath = $imagePathBase . 'used/';
$imageSystemPath = '../image/system/';
$imageQuizPath = $imageSystemPath . $quizBodyFile;
$imageCardPath = $imageSystemPath . 'CardCover.jpg';
$imageMedalPath = $imageSystemPath . 'MedalGold.png';
// 画像ファイルの拡張子
$extentionImg = '.png';

// プレイヤーの回答
$playerAnsPath = '../files/quiz/playerAnswer/';

// Files.Ranking
$filesRankingPath = '../files/ranking/';
// ランキング結果ファイル名
$rankingFile = 'result.txt';

// Files.Quiz
$filesQuizPath = '../files/quiz/';
// Files.Quiz.List
$filesQuizOptPath = $filesQuizPath . 'option/';
// Files.Quiz.List
$filesQuizListPath = $filesQuizPath . 'list/';
// Files.Quiz.current
$filesQuizNewPath = $filesQuizPath . 'current/';
// Files.Quiz.used
$filesQuizUsedPath = $filesQuizPath . 'used/';
// Files.Quiz.Challenger
$filesQuizChallengerPath = $filesQuizPath . 'challenger/';
// Files.Quiz.Answer
$filesQuizAnswerPath = $filesQuizPath . 'answer/';

$myConst;


/*
 * 共通初期化処理
 */
function initialize(string $isLogin = null) :bool{
    if(empty($isLogin)){

    }else{
        if(checkLogin($isLogin) == false){
            return false;
        }
    }
    global $myConst;
    $myConst = getConfigInfo();
    return true;
}

/*
 * ログインチェック
 */
function checkLogin(string $isLogin) :bool{
    // 運営とプレイヤーのそれぞれでログイン状態をチェック
    if($_SESSION['isLogin' . $isLogin] != $isLogin){
        header('Location: ../forms/Login.php' . $url, true, 301);
        return false;
    }
    return true;
}

/** 
 * システム設定ファイル内容取得
 */
function getConfigInfo() :object{
    global $fileConfigPath;
    $constdata = file_get_contents($fileConfigPath);
    return simplexml_load_string($constdata, NULL, LIBXML_NOCDATA);
}

/**
 * メッセージ取得
 * @param string $msgCode:メッセージコード
 * @param string $texts  :置換文字列配列
 */
function getConfigMessage(string $msgCode, array $texts = []) :string{
    global $myConst;
    $message = $myConst->msg->{$msgCode};
    foreach($texts as $i => $text){
        $message = str_replace('{'. $i . '}', $text, $message);
    }
    return $message;
}

/**
 * 手札をランダムに決定する
 * @param string $maxCnt:手札最大枚数
 * @param string $prno  :プレイヤー番号
 * @param bool   $backup:true=バックアップ
 */
function ShuffleCard(string $maxCnt, string $prno = null, bool $backup = false) : array{
    global $imageUserPath, $filesBackupPath, $backupCardPath;

    // 有効（参加）プレイヤーのリスト作成
    $players = getFilesInfo($imageUserPath, 0);
    $x = count($players) - 1;

    // プレイヤー情報の読み込み（マスタファイルより）
    $infos = getPlayerInfo();

    // セーフティコード（参加数＜選出最大数）
    $max = (int)$maxCnt;
    if($x < $max){
        $max = $x;
    }

    // ランダムに規定人選出
    $cards = array();
    srand(time());
    for ($i = 1 ; $i <= $max ; $i++){
        // 選出が重複しないように制御
        while(true){
            $player = $players[rand(0, $x)];
            if($player['filename'] == $prno){
                continue; // 自ログインユーザ除く
            }
            if(array_key_exists($player['filename'], $cards)){
                continue; // 重複除く
            }
            $info = $infos[$player['filename']];
            $cards[$player['filename']] = 
                array('img' => $player['basename'],
                      'name' => $info['name'],
                      'dept' => $info['dept'],
                      'post' => $info['post'],
                      'no' => $i);
            break;
        }
    }

    if($backup){
        // 手札バックアップ
        $bkPath = $filesBackupPath . $prno . '/' . $backupCardPath;
        backupSave($bkPath , json_encode($cards));
    }

    // 結果を戻す
    return $cards;
}

/**
 * プレイヤー情報の読み込み（マスタファイルより）
 * @param string $prno:プレイヤー番号（省略時、全部取得）
 */
function getPlayerInfo(string $prno = null) :array {
    global $filesInfoPath;
    $infos = array();
    $handle = fopen($filesInfoPath, 'r');

    // $example = function($data) use (&$infos) {
    //     $infos[$data[0]] = array('year' => $data[2], 'name' => $data[1], 'dept' => $data[3], 'post' => $data[4]);
    // };
    if(empty($prno)){
        while ($readRec = fgetcsv($handle)) {
            $infos[$readRec[0]] = array('name' => $readRec[1], 'dept' => $readRec[3], 'post' => $readRec[4]);
            // $example($readRec);
        }
    }else{
        while ($readRec = fgetcsv($handle)) {
            if($prno == $readRec[0]){
                $infos[$readRec[0]] = array('year' => $readRec[2], 'name' => $readRec[1]);
                // $example($readRec);
                break;
            }
        }
    }
    fclose($handle);
    return $infos;
}

/**
 * ファイル情報リストの取得
 * @param string $path    :取得する対象ディレクトリ
 * @param int    $target  :0=ファイルとフォルダ,1=ファイル,2=フォルダ,
 * @param string $attrName:取得する属性（省略時、pathinfo全て）
 */
function getFilesInfo(string $path, int $target, string $attrName = null) :array{
    $result = array();
    foreach(glob($path . '*') as $file){
        if($target == 0 || ($target == 1 && is_file($file)) || ($target == 2 && is_dir($file))){
            if(empty($attrName)){
                $result[] = pathinfo($file);
            }else{
                $result[] = pathinfo($file)[$attrName];
            }
        }
    }
    return $result;
}

/**
 * バックアップセーブ
 * @param string $backupPath:出力先パス
 * @param        $data      :出力直
 */
function backupSave(string $backupPath, $data){
    $fh = fopen($backupPath, "w");  // 上書き
    @fwrite($fh, $data);
    fclose($fh);
}

/**
 * 指定パスのファイル名を一つ返す
 * @param string $path:対象パス
 */
function getFileNameOne(string $path) :string{
    foreach(glob($path . '*') as $file){
        return pathinfo($file)['basename'];
    }
    return '';
}

/**
 * ディレクトリ内部全て削除
 * @param string $path:対象ディレクトリパス
 */
function removeDir(string $path){
    foreach(glob($path . '*') as $dir){
        if(is_file($dir)){
            unlink($dir);
        }else{
            system("rm -rf ${dir}");
        }
    }
}

/**
 * 
 */
function getTimeDiff(string $baseTime) :int{
    $pLen = strlen($baseTime);
    if($pLen == 0){
        $baseTime = '00:00:00';
    }else if($pLen <= 2){
        $baseTime = $baseTime . '00:00';
    }else if($pLen <= 5){
        $baseTime = $baseTime . ':00';
    }
    $nowTime = strtotime('now');
    $calcTime = strtotime(date('Y-m-d', $nowTime) . ' ' . $baseTime);
    return ($calcTime - $nowTime);
}

?>