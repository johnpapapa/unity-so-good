$(function(){
    'use strict';
    // $('.response-btn').on('click', function(){
    //     let button_current = $(this);
    //     button_current.prop('disabled', true);
    //     let button_siblings = $(this).siblings();
    //     button_siblings.each(function(index, element){
    //         $(this).prop('disabled', false);
    //     });

    //     let send_data = {
    //         "response_state": button_current.prop('value'),
    //         "user_id": current_user.id,
    //         "event_id": event_data.id
    //     };

    //     $.ajax({
    //         type: "post",
    //         url: response_ajax_send_url,
    //         data: send_data,
    //         headers: { 'X-CSRF-Token' : response_ajax_send_token },
    //     }).done(function(response){
    //         // $("#target").val($.parseJSON(response));
    //         console.log(response);
    //         // window.location.reload();

    //     }).fail(function(jqXHR){
    //         console.error('Error : ', jqXHR.status, jqXHR.statusText);
    //     });
    // });



    let interval;
    $.fn.sparkle_btn = function (opts) {
		var $target = this,
			num_sprites = 30,
			colors = ['#FFBF00'],
			allSprites = [],
			radius = 50,
			sprite_size = 5,
			lifespan = 1000,
			fadetoOpacity = 100,
			rotateDeg=200;

		$target.on("touchstart mousedown", function(){
            var centerX, centerY
            if (event.type == "touchstart"){
                var centerX = event.changedTouches[0].pageX; 
                var centerY = event.changedTouches[0].pageY; 
            }
            if (event.type == "mousedown"){
                var centerX = event.pageX;
                var centerY = event.pageY;
            }
            clickEvent(centerX, centerY);

            let button_current = $(this);
            button_current.prop('disabled', true);
            let button_siblings = $(this).siblings();
            button_siblings.each(function(index, element){
                $(this).prop('disabled', false);
            });
            
            clearInterval(interval);
            interval = setInterval(function(){createStar(button_current)}, 200);
            
            /* todo:関数として別にまとめる */
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
                console.log(response);
                // window.location.reload();
    
            }).fail(function(jqXHR){
                console.error('Error : ', jqXHR.status, jqXHR.statusText);
            });
		});


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

		function clickEvent(centerX, centerY){
			for (var i=0; i<num_sprites; i++){
				makeSprites(centerX, centerY)
			}
		}

		function makeSprites(centerX, centerY) {
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
		return $target;
	}

    $('.response-btn').sparkle_btn();
});