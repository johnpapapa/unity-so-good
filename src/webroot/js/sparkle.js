/**
 * https://www.jqueryscript.net/animation/jQuery-Plugin-For-Hover-triggered-Sparkle-Effect-sparkleHover.html
 * こっから改修
 */

(function ($) {
	'use strict';

	// $.fn.sparkle_btn = function (opts) {

	// 	var defaultOptions = {
	// 		// num_sprites: 10,
	// 		// lifespan: 500,
	// 		// radius: 200,
	// 		// sprite_size: 10,
	// 		// image:'star-on.png',
	// 		// colors:['#00000000'], 
	// 		// sprite_size:10,
	// 	}

	// 	var opts = $.extend({}, defaultOptions, opts);

	// 	var $target = this,
	// 		num_sprites = 30,
	// 		colors = ['#FFBF00'],
	// 		allSprites = [],
	// 		radius = 50,
	// 		sprite_size = 5,
	// 		// shape = opts.shape.toLowerCase(),
	// 		image = opts.image,
	// 		lifespan = 1000,
	// 		fadetoOpacity = 100,
	// 		rotateDeg=200;

	// 	// $target.on("click", function(){
	// 	// 	var centerX = $(this).width() / 2 + $(this).offset().left;
	// 	// 	var centerY = $(this).offset().top + $(this).height() / 2;
	// 	// 	clickEvent(centerX, centerY);
	// 	// });

	// 	$target.on("touchstart", function(){
	// 		var centerX = event.changedTouches[0].pageX; // X 座標の位置
	// 		var centerY = event.changedTouches[0].pageY; //
	// 		clickEvent(centerX, centerY);
	// 	});

	// 	$target.on("mousedown", function(){
	// 		var centerX = event.pageX;   // X 座標の位置
	// 		var centerY = event.pageY;   // Y 座標の位置
	// 		clickEvent(centerX, centerY);
	// 	});

	// 	function clickEvent(centerX, centerY){
	// 		for (var i=0; i<num_sprites; i++){
	// 			makeSprites(centerX, centerY)
	// 		}
	// 	}

	// 	function makeSprites(centerX, centerY) {
	// 		var newsprite = document.createElement('div');
	// 		var rotateDeg = Math.random() * 360;
	// 		var radSpread = Math.random() * (radius + radius) - radius;

	// 		$(newsprite).css({
	// 			backgroundColor: colors[0],
	// 			// "background-size": "contain",
	// 			// backgroundImage: "url(" + image + ")",
	// 			borderRadius: "0px",
	// 		});

	// 		$(newsprite).css({
	// 			left: centerX,
	// 			top: centerY,
	// 			width: sprite_size,
	// 			height: sprite_size,
	// 			borderRadius: "0px",
	// 			position: "absolute",
	// 				"-webkit-transform": "rotate("+rotateDeg+"deg)",
	// 				"-moz-transform": "rotate("+rotateDeg+"deg)",
	// 				"-o-transform": "rotate("+rotateDeg+"deg)",
	// 				"-ms-transform": "rotate("+rotateDeg+"deg)",
	// 				"transform": "rotate("+rotateDeg+"deg)"
	// 		});
			
	// 		$(newsprite).animate(
    //             {
	// 				opacity:fadetoOpacity,
	// 				left: centerX + Math.random() * (radius + radius) - radius,
	// 				top: centerY + radSpread,
    //             },
    //             {
    //                 duration: lifespan,
    //                 easing: 'linear',
    //                 queue: false,
					
    //             }
    //         )
	// 		.fadeOut(lifespan, function() {
	// 			$(this).remove();
	// 		});

	// 		document.body.appendChild(newsprite);
	// 	}
	// 	return $target;
	// }
})(jQuery);