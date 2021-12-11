<div class="modal js-modal_quiz">
    <div class="modal__bg js-modal-close"></div>
    <div class="modal__content">
        <img id="modalQuizBody" class="modal-main-img" src="<?php print($imageQuizPath); ?>" file=""></img>
        <div class="switch-disp-button">
            <button id="btnMove1" class="move" onClick="onClick_btnMove('1')">問題</button>
            <button id="btnMove2" class="move" onClick="onClick_btnMove('2')">選択肢</button>
            <button id="btnMove3" class="move" onClick="onClick_btnMove('3')">回答</button>
        </div>
        <div id="btnDialogAnswer" class="btn-answer">
            <button id="btnAnsA" class="answer" onClick="onClick_btnAnswer('A')">A</button>
            <button id="btnAnsB" class="answer" onClick="onClick_btnAnswer('B')">B</button>
            <button id="btnAnsC" class="answer" onClick="onClick_btnAnswer('C')">C</button>
        </div>
        <button class="js-modal-close">閉じる</button>
    </div>
</div>
