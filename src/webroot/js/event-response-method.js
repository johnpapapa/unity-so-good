$(function(){
    'use strict';
    let responseButtonElement = $(".response-btn");

    var tapEnable =false; // 有効なタップかを保持する
    responseButtonElement.on('mousedown', function (event) {
        tapEnable = true;
    }).on('touchmove', function () { // スクロール用のタップは発火させない。
        tapEnable = false;
    }).on('touchend mouseup', function () {
        if (tapEnable) {
            tapEnable = false;
            processButtonAction($(this));
        }
    });

    //ボタンを押した時の処理
    function processButtonAction(buttonElement){
        let confirmAnswer = window.confirm(buttonElement.html() + 'にしますか？');
        if(confirmAnswer){
            disableButton(buttonElement);
            let eventId = (typeof eventData !== 'undefined') ? eventData.id:buttonElement.siblings('input[type="hidden"]').val();
            let userId = currentUser.id
            let responseState = buttonElement.prop('value')
            sendAjaxProperty(
                responseAjaxSendUrl,
                eventId,
                userId,
                responseState
            ).then(function(response) {
                //ページ再読み込み時アンカーリンクをスクロール位置を調整
                window.location.hash = eventId; 
                window.location.reload();
            })
            .catch(function(error) {
            });            
        }
    }

    //押下したボタンを無効化し、他のボタンを有効化する関数
    function disableButton(buttonElement){
        buttonElement.prop('disabled', true); //押下ボタンを無効化
        let buttonSiblings = buttonElement.siblings();
        buttonSiblings.each(function(_, element){
            $(element).prop('disabled', false); //他のボタンを有効化
        });
    }
    
    //eventResponseに関わるAjax通信を制御する関数
    function sendAjaxProperty(ajaxUrl, eventId, userId, responseState){
        let sendData = {
            "response_state": responseState,
            "user_id": userId,
            "event_id": eventId
        };
        return getAjaxProperty(ajaxUrl, sendData)
            .then(function(response){
                if(response['status'] != 'ok'){
                    console.error('Error.')
                    throw new Error('Error.'); // エラーをスロー
                }
                else {
                    return response; // 正常なレスポンスを返す
                } 
            }).catch(function(jqXHR){
                console.error('Error : ', jqXHR.status, jqXHR.statusText);
                throw new Error('Error : ' + jqXHR.status + ', ' + jqXHR.statusText); // エラーをスロー
            });
    }

    //Ajax通信を行う関数
    function getAjaxProperty(url, data){
        return $.ajax({
            type: "post",
            url: url,
            data: data,
            headers: { 'X-CSRF-Token' : ajaxSendToken },
        })
    }
});