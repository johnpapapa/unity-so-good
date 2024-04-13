$(function(){
    'use strict';
    $('.submit-comment-btn').on('click', function(){
        let button_current = $(this);
        let comment_body = $("#comment_body");
        if(comment_body.val() == ""){
            return;
        }
        button_current.prop('disabled', true);

        let send_data = {
            "body": comment_body.val(),
            "user_id": current_user.id,
            "event_id": event_data.id
        };

        $.ajax({
            type: "post",
            url: comment_submit_ajax_send_url,
            data: send_data,
            headers: { 'X-CSRF-Token' : ajax_send_token },
        }).done(function(response){
            // $("#target").val($.parseJSON(response));
            console.log(response);
            button_current.prop('disabled', false);
            window.location.reload();

        }).fail(function(jqXHR){
            console.error('Error : ', jqXHR.status, jqXHR.statusText);
            button_current.prop('disabled', false);
        });
    });

    $('.delete-comment-btn').on('click', function(){
        let button_current = $(this);
        let comment_id = $(this).children('input').val();

        let send_data = {
            "comment_id": comment_id
        };

        $.ajax({
            type: "post",
            url: comment_delete_ajax_send_url,
            data: send_data,
            headers: { 'X-CSRF-Token' : ajax_send_token },
        }).done(function(response){
            console.log(response);
            if(response['status'] == 'ok'){
                window.location.reload();
            }

        }).fail(function(jqXHR){
            console.error('Error : ', jqXHR.status, jqXHR.statusText);
            button_current.prop('disabled', false);
        });
    });
});