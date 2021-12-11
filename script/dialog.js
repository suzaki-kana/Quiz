/**
 * ダイアログ表示の初期処理
 */
function dialogInitialize(basepath,optpath){

    $('#modalQuizBody').attr('base', basepath);
    $('#modalQuizBody').attr('opt', optpath);

    // $('.js-modal-open').on('click',function(){
    //     modalOpen();
    //     return false;
    // });
    $('.js-modal-close').on('click',function(){
        $('.modal').fadeOut();
        return false;
    });
    
    $('.quiz-body').on('click',function(){
        if($('#modalQuizBody').attr('file') == ''){
        }else{
            modalOpenQuiz('.quiz-body');
        }
        return false;
    });
    $('.card-w').on('click',function(){
        zoomImage($(this).attr('src'));
        return false;
    });
}

/**
 * 
 * @param {*} attrFile 
 */
function setAttrFile(attrFile){
    $('#modalQuizBody').attr('file', attrFile);
}

/**
 * クイズダイアログのモーダル表示
 * @param {*} query 
 */
function modalOpenQuiz(query){
    $('.switch-disp-button').removeClass('hide');
    onClick_btnMove("1")
    changeMode($(query).attr('mode'));
    if($(query).attr('flg') == '1'){
        $('#btnDialogAnswer').removeClass('hide');
    }else if($('#btnDialogAnswer').hasClass('hide') == false){
        $('#btnDialogAnswer').addClass('hide');
    }
    $('.js-modal_quiz').fadeIn();
}

/**
 * IMGタグの画像を拡大表示
 * @param {*} query 
 */
function zoomImage(path){
    $('#modalQuizBody').attr('src', path);
    if($('#btnDialogAnswer').hasClass('hide') == false){
        $('#btnDialogAnswer').addClass('hide');
    }
    if($('.switch-disp-button').hasClass('hide') == false){
        $('.switch-disp-button').addClass('hide');
    }
    $('.js-modal_quiz').fadeIn();
}

/**
 * ボタン表示切替
 * @param {*} mode 
 */
function changeMode(mode){
    if(mode == '1'){
        $('#btnMove3').removeClass('hide');
    }else if($('#btnMove3').hasClass('hide') == false){
        $('#btnMove3').addClass('hide');
    }
}

/**
 * 表示切替ボタン
 * @param {*} mode 
 */
function onClick_btnMove(mode){
    $('.move').removeClass('select');
    $('#btnMove' + mode).addClass('select');
    if(mode == '1'){
        // 問題表示
        $('#modalQuizBody').attr('src', $('#modalQuizBody').attr('base') + $('#modalQuizBody').attr('file'));
    }else if(mode == '2'){
        // 回答表示
        $('#modalQuizBody').attr('src', $('#modalQuizBody').attr('opt') + 'select_' + $('#modalQuizBody').attr('file'));
    }else if(mode == '3'){
        // 正答表示
        $('#modalQuizBody').attr('src', $('#modalQuizBody').attr('opt') + 'ans_' + $('#modalQuizBody').attr('file'));
    }
}

/**
 * 回答ボタン押下
 * @param {*} val 
 */
function onClick_btnAnswer(val){
    // 回答ボタン非活性
    $('.answer').removeClass('select');
    $('#btnAns' + val).addClass('select');
    // 回答送信
    $.ajax({
        /* サーバに送信するリクエストの設定 */
        type:'POST', // リクエストのタイプ
        url: '../api/sendPlayerAnswerService.php', // URL
        contentType: 'application/json',
        dataType: 'text',
        data: JSON.stringify({
            answer: val,
            prno : $('#PrNo').text()
        }) // 送信パラメータ値
    })
    .done(function(data){
    })
    .fail(function(jqXHR, textStatus, errorThrown){
        /* 通信に失敗した場合の処理 */
        alert('<?php print(getConfigMessage("E004")) ?>');
    })
    .always(function(){
        /* 結果にかかわらず常に実行する処理 */
    });
}

/**
 * 順位リスト作成・表示
 * @param {*} data 
 */
function createRankingList(data){
    let row = 0
    $('.ranking-list').empty();
    $.each(data, function(key, value) {
        row = Number(key) + 1;
        $('.ranking-list').append('<div id="rankRow' + row + '" class="ranking-row ani_spin_1 hide"></div>');
        $('#rankRow' + row).append('<div class="ranking-rank">第' + value['rank'] + '位</div>');
        $('#rankRow' + row).append('<div class="ranking-score">' + value['score'] + 'pt</div>');
        $('#rankRow' + row).append('<div class="ranking-image"><img src="' + value['img'] + '" /></div>');
        $('#rankRow' + row).append('<div class="ranking-name hide">' + value['name'] + '</div>');
    });

    // リストのスクロールを最下部へ
    var obj = document.getElementById('listRanking');
    obj.scrollTop = obj.scrollHeight;

    $('.js-modal_ranking').fadeIn();

    // ランキング下部からアニメーションを再生する
    $('#rankRow' + row).removeClass('hide');
    setTimeout(function(){ani_rankingDisplay(row)}, 1500);
}

/**
 * ランキング表示のアニメーション
 * @param {*} i 
 */
function ani_rankingDisplay(i){
    $('#rankRow' + i).addClass('ani_spin_2');
    $('#rankRow' + i).removeClass('ani_spin_1');
    $('#rankRow' + i + ' > .ranking-name').removeClass('hide');
    if(i > 1){
        $('#rankRow' + (i - 1)).removeClass('hide');
        // Timer設定
        setTimeout(function(){ani_rankingDisplay(i - 1)}, 2000);
    }
}


