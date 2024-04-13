$(function(){
    'use strict';

    let interval;
    let $response_btn_obj = $(".response-btn");
    $response_btn_obj.on("touchstart mousedown", function(){
        var centerX, centerY
        if (event.type == "touchstart"){
            var centerX = event.changedTouches[0].pageX; 
            var centerY = event.changedTouches[0].pageY; 
        }
        if (event.type == "mousedown"){
            var centerX = event.pageX;
            var centerY = event.pageY;
        }
        clickSpriteStars(centerX, centerY);

        //送信する前に本当に送信していいのかを確認するダイアログを表示する処理
        let button_current = $(this);
        var confirmAnswer = window.confirm(button_current.html() + 'にしますか？');
        if(confirmAnswer){
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
            
            getAjaxProperty(response_ajax_send_url, send_data)
            .done(function(response){
                if(response['status'] != 'ok'){
                    console.error('Error.');
                    return;
                } 
                updateResponseList(
                    current_user.display_name,
                    response['response_state'],
                    response['bef_response_state'],
                    response['updated_at'],
                    event_data.participants_limit
                )
            }).fail(function(jqXHR){
                console.error('Error : ', jqXHR.status, jqXHR.statusText);
            });

            clearInterval(interval);
            if(button_current.prop('value') == 1){
                interval = setInterval(function(){createStar(button_current)}, 200);    
            }
        }
    });

    // 参加人数と参加者一覧に変更された内容を反映
    function updateResponseList(display_name,response_state, bef_response_state, update_at, participants_limit){
        //参加人数
        
        if(bef_response_state != "null"){
            let bef_response_count_obj = $("#state-count-"+bef_response_state);
            let bef_response_count = Number(bef_response_count_obj.html());
            bef_response_count_obj.html(bef_response_count - 1);
        }
        let response_count_obj = $("#state-count-"+response_state);
        let response_count = Number(response_count_obj.html());
        response_count_obj.html(response_count + 1);

        


        //参加者一覧
        let element_state_content = "";
        let user_state_content = $("#user-state");
        if(user_state_content.length){
            user_state_content.remove();
        }
        if(response_state == '2'){
            element_state_content = element_state_content + "<div id='user-state' class='state-content over-ellipsis disp-iblock pure-u-1'>";
            element_state_content = element_state_content + "<div class='fs-medium  fs-m-midium'>";
            element_state_content = element_state_content + display_name;
            element_state_content = element_state_content + "</div>";    
        } else {
            element_state_content = element_state_content + "<div id='user-state' class='state-content over-ellipsis disp-iblock pure-u-1 mt10'>";
            element_state_content = element_state_content + "<div class='name disp-m-block disp-iblock over-ellipsis fs-large fs-m-large'>";
            element_state_content = element_state_content + display_name;
            element_state_content = element_state_content + "</div>";
        }
        if(participants_limit > 0 && response_state != '2'){
            element_state_content = element_state_content + "<div class='time disp-iblock fr fs-small fs-m-small'>";
            element_state_content = element_state_content + update_at;
            element_state_content = element_state_content + "</div>";    
        }
        element_state_content = element_state_content + "</div>";

        $("#state-contents-"+response_state).append(element_state_content);
    }

    function getAjaxProperty(url, data){
        return $.ajax({
            type: "post",
            url: url,
            data: data,
            headers: { 'X-CSRF-Token' : ajax_send_token },
        })
    }

    // クリックしたbtnの周囲がキラキラするエフェクト
    const createStar = (el) => {
        const starEl = document.createElement("span");
        starEl.className = "star";
        starEl.style.left = (Math.random() * el.width()) + el.offset().left + "px";
        starEl.style.top = (Math.random() * el.height()) + el.offset().top + "px";
        document.body.appendChild(starEl);
        
        setTimeout(() => {
            starEl.remove();
        }, 1000);
    };

    // 放射状に星が拡散するエフェクト
    function clickSpriteStars(centerX, centerY){
        let num_sprites = 30;
        for (var i=0; i<num_sprites; i++){
            makeSprites(centerX, centerY)
        }
    }

    // 星単体の動き
    function makeSprites(centerX, centerY) {
        let colors = ['#FFBF00'];
        let radius = 50;
        let sprite_size = 5;
        let lifespan = 1000;
        let fadetoOpacity = 100;

        var newsprite = document.createElement('div');
        var rotateDeg = Math.random() * 360;
        var radSpread = Math.random() * (radius + radius) - radius;

        

        $(newsprite).css({
            backgroundColor: colors[0],
            borderRadius: "0px",
        });
        $(newsprite).css({
            left: centerX,
            top: centerY,
            width: sprite_size,
            height: sprite_size,
            borderRadius: "0px",
            position: "absolute",
                "-webkit-transform": "rotate("+rotateDeg+"deg)",
                "-moz-transform": "rotate("+rotateDeg+"deg)",
                "-o-transform": "rotate("+rotateDeg+"deg)",
                "-ms-transform": "rotate("+rotateDeg+"deg)",
                "transform": "rotate("+rotateDeg+"deg)"
        });
        $(newsprite).animate(
            {
                opacity:fadetoOpacity,
                left: centerX + Math.random() * (radius + radius) - radius,
                top: centerY + radSpread,
            },
            {
                duration: lifespan,
                easing: 'linear',
                queue: false,
            }
        )
        .fadeOut(lifespan, function() {
            $(this).remove();
        });
        document.body.appendChild(newsprite);
    }
});