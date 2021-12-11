<?php
    session_start();
    require_once("./common.php");

    initialize();

    $_SESSION['ErrorMessage'] = "";
    $url = 'Login.php';

    if(isset($_POST['PassWord'])){
        $word = str_pad($_POST['PassWord'], 11);
        
        if($word == 'Admin2020PS'){
            /* 運営ログイン */
            $_SESSION['isLogin1'] = '1';        // ログインフラグ
            $url = 'ManagementSite.php';       // 画面遷移設定
            
        }else if(substr($word, 9, 2) == 'PS'){
            /* プレイヤーログイン */

            $prno = substr($word,0, 5);

            // マスタファイルよりプレイヤー情報を照合
            $infos = getPlayerInfo($prno);

            if(empty($infos)){
                $_SESSION['ErrorMessage'] = "パスワードが正しくありません。";
            }else if(substr($infos[$prno]['year'], 0, 4) == substr($word,5, 4)){
                $_SESSION['isLogin2'] = '2';    // ログインフラグ
                $url = 'ExciteGame.php';        // 画面遷移設定
                $_SESSION['PrNo'] = $prno;      // プレイヤー番号退避

                // ログインプレイヤー情報取得
                $_SESSION['MyInfo'] = $infos[$prno];

                if(file_exists($filesBackupPath . $prno)){
                    // バックアップが存在する場合
                }else{
                    // 
                    $filename = getFilesInfo($imagePathBase . $prno, 1)[0];
                    mkdir($imagePath . $filename['basename'], 0777);

                    if(getTimeDiff((string)$myConst->time_limit) >= 0){
                        // 制限時間内なら、有効プレイヤーとして画像をコピー
                        mkdir($imageUserPath . $filename['basename'], 0777);
                    }

                    // バックアップフォルダ作成
                    mkdir($filesBackupPath . $prno, 0777);

                    // 手札シャッフル（とバックアップ）
                    ShuffleCard((string)$myConst->max_card, $prno, true);

                    // スコアの保存とバックアップ
                    backupSave($filesBackupPath . $prno .'/' . $backupScorePath, '0');
                }
            }else{
                $_SESSION['ErrorMessage'] = "パスワードが正しくありません。";
            }
        }else{
            $_SESSION['ErrorMessage'] = "パスワードが正しくありません。";
        }
    }

    session_write_close();

    // リダイレクト先のURLへ転送する
    header('Location: ../forms/' . $url, true, 301);

    // すべての出力を終了
    exit;

?>