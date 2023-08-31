$(function(){
    $('button').on('click', function(){
        console.log("sdf");
        let button_current = $(this);
        button_current.prop('disabled', true);
        let button_siblings = $(this).siblings();
        button_siblings.each(function(index, element){
            $(this).prop('disabled', false);
        });

        let send_data = {
            "response_state": button_current.prop('value'),
            "user_id": current_user.id,
            "event_id": event_data.id
        };

        $.ajax({
            type: "post",
            url: response_ajax_send_url,
            data: send_data,
            headers: { 'X-CSRF-Token' : response_ajax_send_token },
        }).done(function(response){
            // $("#target").val($.parseJSON(response));
            window.location.reload();
        }).fail(function(jqXHR){
            console.error('Error : ', jqXHR.status, jqXHR.statusText);
        });
    });
});