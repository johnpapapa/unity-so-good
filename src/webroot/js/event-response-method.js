$(function(){
    'use strict';
    let $responseButtonElement = $(".response-btn");
    $responseButtonElement.on("touchstart mousedown", function(){
        let currentButtonElement = $(this);

        //送信する前に本当に送信していいのかを確認するダイアログを表示する処理
        let confirmAnswer = window.confirm(currentButtonElement.html() + 'にしますか？');
        if(confirmAnswer){
            disableButton(currentButtonElement); //押下したボタンを無効化し、他のボタンを有効化

            let eventId = (eventData !== 'undefined') ? eventData.id:currentButtonElement.siblings('input[type="hidden"]').val();
            let userId = currentUser.id
            let responseState = currentButtonElement.prop('value')
            
            sendAjaxProperty(
                responseAjaxSendUrl,
                eventId,
                userId,
                responseState
            ).then(function(response) {
            })
            .catch(function(error) {
            });            
        }
    });

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