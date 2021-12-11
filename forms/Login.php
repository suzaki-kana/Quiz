<?php
    // session のタイムアウトを設定（s）
    ini_set('session.gc_maxlifetime', 60 * 60 * 24);
    // session 開始
    session_start();
    require_once("../api/common.php");

    // 共通初期化処理
    if(initialize() == false){
        return;
    }

    $localMassage = $_SESSION['ErrorMessage'];
    $_SESSION['ErrorMessage'] = null;

?>

<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>☆クイズ大会入り口☆</title>
  <link rel="stylesheet" type="text/css" href="../css/base.css">
  <link rel="stylesheet" type="text/css" href="../css/login.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
  <script>
/**
 * 画面Load時処理
 */
function document_load(){
    /* ログインボタンClick */
    $('#btnLogin').click(function(){
        procLogin();
    });
    /* Form Submit */
    // $('#frmMain').on('submit', function(){ 
    //     return procLogin();
    // });
    /* keypress */
    $('#PassWord').on('keypress', function(event){
        if(event.keyCode == 13){
            return procLogin();
        }
    });

    if('<?php print($localMassage) ?>' != ''){
        $('#message').addClass('show');
    }
}
function procLogin(){
    // 入力チェックエラー時の設定をクリア
    $('#PassWord').removeClass('text-error');
    $('#message').removeClass('show');
    let password = $('#PassWord').val();
    if(!password){
        $('#PassWord').addClass('text-error');
        $('#message').addClass('message');
        $('#message').text('<?php print(getConfigMessage("E001",["パスワード"])) ?>');
        $('#message').addClass('show');
        $('#PassWord').focus();
        return false;
    }

    $('#frmMain').submit();
    return true;
}
    </script>
</head>

<body onload="document_load()">
    <form id="frmMain" method="post" action="../api/loginService.php">
        <div class="frame-login">
            <div style="top:240px" class="label-title-header">クイズ大会入り口</div>
            <div style="top:250px"><input type="password" id="PassWord" name="PassWord" placeholder="パスワード" class="text-input" /></div>
            <div style="top:255px"><button type="button" id="btnLogin" class="normal">参加！</button></div>

            <div style="top:350px; visibility:hidden" class="error-msg" id="message"><?php print($localMassage); ?></div>
        </div>
    </form>
</body>
</html>
