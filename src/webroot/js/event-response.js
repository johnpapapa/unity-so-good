// $("#kicker").on("change", function(){
//     var send_data = {selected:$('option:selected').val(), add_key:'add_value'};

//     $.ajax({
//         type: "post",
//         url: "<?= $this->Url->build(['controller' => 'Samples', 'action' => 'receiver']) ?>",
//         data: send_data,
//         headers: { 'X-CSRF-Token' : "<?= $this->request->getAttribute('csrfToken') ?>" },
//     }).done(function(response){
//         $("#target").val($.parseJSON(response));
//     }).fail(function(jqXHR){
//         console.error('Error : ', jqXHR.status, jqXHR.statusText);
//     });
// });


$(function(){
    $('button').on('click', function(){
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
            console.log(response)
        }).fail(function(jqXHR){
            console.error('Error : ', jqXHR.status, jqXHR.statusText);
        });
    });
});