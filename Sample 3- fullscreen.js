/**
 * Please take note that this is just a part of my script, so it will not work. For reviewing purpose only. 
 * The script below creates all the interactivity your eyes can in this page https://www.talktotucker.com/tours/7365-edgewater-drive/21887707
 */

var empty           = /^\s*$/;
var email_format    = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;  
var number_format   = /^\d+$/;
var url_format      = /(http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
var dur             = 6000;
var actfs           = false;
var mobile          = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
var timer 					= 0;
var timeout, imglen, cloned_mc, vwtimer;

$(function() {

	// For Mobile
	$('.mob-btn').hide();

	if (mobile) {
		// Detect Orientation
		var mql = window.matchMedia("(orientation: portrait)");

		if (mql.matches) {
			$('body').removeClass('landscape');
			$('body').addClass('portrait');
			$('.images-parent').removeClass('fullscreen');
		} else {  
			$('body').removeClass('portrait');
			$('body').addClass('landscape');
			$('.images-parent').addClass('fullscreen');
		}

		mql.addListener(function(m) {
			if (m.matches) {
				$('body').removeClass('landscape');
				$('body').addClass('portrait');
				$('.images-parent').removeClass('fullscreen');
			} else {
				$('body').removeClass('portrait');
				$('body').addClass('landscape');
				$('.images-parent').addClass('fullscreen');
			}
		});

		$('.mob-btn').show();
		$('.fsbtn').hide();

		$('.mob-cont').on('click', function() {
			targ = $(this).data('targ');

			switch(targ) {
				case 'mc-cont':
					cloned_mc = $('#loan').clone();
					$('#loan').remove();
					$('#mc-clone-holder').html(cloned_mc);
					break;

				case 'music-onoff':
					if ($(this).hasClass('off')) {
						$(this).addClass('on');
						$(this).removeClass('off');
						document.getElementById('bg-music').play();
						$(this).html('Turn Off Music');
					} else {
						$(this).addClass('off');
						$(this).removeClass('on');
						document.getElementById('bg-music').pause();
						$(this).html('Turn On Music');
					}
					break;

				case 'pa-hide':
					if ($('.info-holder').hasClass('ih-hide')) {
						$('.info-holder').removeClass('ih-hide');
						$('.fs-info').removeClass('fs-hide');

						$(this).addClass('show');
						$(this).removeClass('hid');
						$(this).html('Hide Price and Address');
					} else {
						$('.info-holder').addClass('ih-hide');
						$('.fs-info').addClass('fs-hide');

						$(this).addClass('hid');
						$(this).removeClass('show');
						$(this).html('Show Price and Address');
					}
					break;
			}

			$('#' + targ).addClass('mob-act');
		});

		$('.mc-close').on('click', function() {
			$('.mob-content').removeClass('mob-act')

			if (targ == 'mc-cont') {
				cloned_mc = $('#loan').clone();
				$('#mc-clone-holder').html('');
				$('.mort-cont').append(cloned_mc);
			}
		});
	}

	$('.shicons').on('click', function() {
		type = $(this).data('type');

		switch(type) {
			case 'info-list' :
				if ($('.info-holder').hasClass('ih-hide')) {
					$('.info-holder').removeClass('ih-hide');
					$(this).children('i').text('info');
					$('.fs-info').removeClass('fs-hide');
				} else {
					$('.info-holder').addClass('ih-hide');
					$('.fs-info').addClass('fs-hide');

					$(this).children('i').text('highlight_off');
				}
				break;

			case 'sh-volume':
				if ($(this).children('i').text() == 'volume_off') {
					$(this).children('i').text('volume_up');
					document.getElementById('bg-music').play();
				} else {
					$(this).children('i').text('volume_off');
					document.getElementById('bg-music').pause();
				}
				break;

			case 'sh-fullscreen':
				toggle_fullscreen();
				break;

			case 'sh-mortgage':
				if ($('#customtab').hasClass('mort-act')) {
					$('#customtab').removeClass('mort-act');
				}

				if ($('#mortgage').hasClass('mort-act')) {
					$('#mortgage').removeClass('mort-act');
				} else {
					$('#mortgage').addClass('mort-act');
				}
				break;

			case 'sh-customtab':
				if ($('#mortgage').hasClass('mort-act')) {
					$('#mortgage').removeClass('mort-act');
				}
				
				if ($('#customtab').hasClass('mort-act')) {
					$('#customtab').removeClass('mort-act');
				} else {
					$('#customtab').addClass('mort-act');
				}
				break;
		}

	});

	$('.prop-dtls, #viewmore').on('click', function() {
		if ($('.info').hasClass('actprop')) {
			$('.info').removeClass('actprop');
			$('.prop-dtls, #viewmore').text('View More Details');
			setTimeout(() => {
				$('.info-op').show();
			}, 550);
		} else {
			$('.info-op').hide();
			$('.info').addClass('actprop');
			$('.prop-dtls, #viewmore').text('Close');
		}
	});

	$('.shares').on('click', function() {
		pro = $(this).data('type');

		switch(pro) {
			case 'fb' :
				FB.ui({
			    	method: 'share',
			    	display: 'popup',
			    	href: $('#og-url').val(), // baseurl + 'preview/fbshare/' + $('#listno').val(),
			  	}, function(response){});
				break;

			case 'tw'	:
				url = 'https://twitter.com/intent/tweet?url=' + $('#og-url').val() + '&text=Looking to buy? This home might a good fit for you!&hashtags=virtualtours,realestate';
				window.open(url, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=500,width=600');
				break;

			case 'pt'	:
				url = 'https://pinterest.com/pin/create/button/?url=' + $('#og-url').val() + '&media=' + $('#og-image').val() + '&description=' + $('#og-description').val();
				window.open(url, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=500,width=600');
				break;

			case 'gp'	: 
				url = 'https://plus.google.com/share?url=' + $('#og-url').val();
				window.open(url, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=500,width=600');
				break;

			case 'in'	: 
				url = 'https://www.linkedin.com/shareArticle?mini=true&url=' + $('#og-url').val() + '&title=' + $('#og-title').val() + '&summary=' + $('#og-description').val() + '&source=Tucker Tours';
				window.open(url, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=500,width=600');
				break;

			case 'em' :
				$('#vmail').removeClass('vhide');
				break;

			default :
				break;
		}
	});

	$('#vmail-cancel').on('click', function() {
		$('#vmail').addClass('vhide');
	});

	$('#vmail-frm').on('submit', function() {
		mails 	= ($('#vemails').val()).split(',');
		ctr  	= 1;
		proceed = true;

		for (var i = 0; i < mails.length; i++) {
			mail = (mails[i]).trim();

			if (email_format.test(mail) == false) {
				alert('Error: Invalid email format.');
				proceed = false;
				return false;
			}

			ctr++;
		}

		if (proceed == true) {
			$('#vmail .btn').attr('disabled', true);
			$('#vmail-cancel').after('<small class="sending"> Sending, please wait...</small>');

			ser = {
				mails 	: $('#vemails').val(),
				tid 	: $('#tourid').val(),
				aphoto 	: $('#og-image').val(),
			}

			$.get(baseurl + 'fullscreen/send_email', ser, function(data) {
				$('#vmail').addClass('vhide');
				$('#vemails').val('');

				$('#vmail .btn').attr('disabled', false);
				$('.sending').remove();
			});
		}

		return false;
	});

	// below is for the image transition and prev, next and thumb clicks	

	setTimeout(() => {
		$.get(baseurl + 'fullscreen/load_images', { listingid : $('#listno').val() }, function(resp) {
			let r = $.parseJSON(resp);

			$('.images-parent').html(r.images);
			$('.scroller').html(r.thumbs);

			$(document).view_timer();
			$('.info-holder').removeClass('ih-hide');
			$('.thumbs-holder').removeClass('th-hide');
			$('.mob-info').addClass('mob-act');
			$('.sh-icons').show();
			$('.sk-folding-cube').hide();

			if (mobile) {
				$('.sh-icons a:nth-child(4)').hide();
			}

			// auto adjust info width
			mainw = $('.info-holder').find('.agent-info').outerWidth();
			coagw = $('.info-holder').find('.coagent').outerWidth();
			mainw = parseInt(mainw);
			coagw = parseInt(coagw);
			
			infow = mainw + 115;
			
			if(coagw > mainw) {
				infow = coagw + 115;				
			}

			if ($('#unbranded')[0]) {
				$('.info-holder').find('.info').css('max-width', 180);				
			} else {
				$('.info-holder').find('.info').css('max-width', infow);
			}

			$('.navis').show();

			imglen = $('.img-child').length;

			timeout = setTimeout((next_image(0)));
		});
	}, 5000); // added 5 second delay before loading the main images

	// https://www.talktotucker.com/tours/6205-rucker-road/21804356
	// https://www.talktotucker.com/tours/6154-north-oxford-street/21804629

	// $('img').preload(function(perc, done) {
	// 	if (done == true) {
	// 		imglen = $('.img-child').length;

	// 		$('img').load(function() {
	// 			$('.img-child:first').addClass('active');
	// 			$('.img-thumbs a:first').addClass('active');
	// 		});

	// 		$(document).view_timer();
	// 		$('.info-holder').removeClass('ih-hide');
	// 		$('.thumbs-holder').removeClass('th-hide');
	// 		$('.mob-info').addClass('mob-act');
	// 		$('.sh-icons').show();
	// 		$('.sk-folding-cube').hide();

	// 		if (mobile) {
	// 			$('.sh-icons a:nth-child(4)').hide();
	// 		}

	// 		// auto adjust info width
	// 		mainw = $('.info-holder').find('.agent-info').outerWidth();
	// 		coagw = $('.info-holder').find('.coagent').outerWidth();
	// 		mainw = parseInt(mainw);
	// 		coagw = parseInt(coagw);
			
	// 		infow = mainw + 115;
			
	// 		if(coagw > mainw) {
	// 			infow = coagw + 115;				
	// 		}

	// 		if ($('#unbranded')[0]) {
	// 			$('.info-holder').find('.info').css('max-width', 166);				
	// 		} else {
	// 			$('.info-holder').find('.info').css('max-width', infow);
	// 		}

	// 		timeout = setTimeout((next_image(0)));
	// 	}
	// });

	$(document).on('click', '.aprev', function() {
		next_image('prev');
	});

	$(document).on('click', '.anext', function() {
		next_image('next');
	});

	$(document).on('click', '.img-thumbs a', function() {
		next = $(this).index();
		next_image(next);
	});

	function next_image(pro) {
		if (pro == undefined) {
			pro = 'next';
		}

		clearTimeout(timeout);

		$('.tc-cont').removeClass('tc-act');

		hwid = $('.img-thumbs').outerWidth();
		swid = $('.scroller').outerWidth();
		twid = $('.scroller a').outerWidth();
		chld = $('.scroller').children().length;

		center = parseInt((hwid / twid) / 2);

		if (center == 0) {
			center = 1;
		}

		if (center / 2 == 0) {
			center = center - 1;
		}

		$(".scroller").stop();

		if (pro == 'next') {
			next  = $('.active').index('.img-child') + 1;
			
			limit = swid - hwid;
			left  = twid * (next - center);

			if (left > limit) {
				left = limit;
			}

			if (next == chld) {
				$(".scroller").animate({'left': '0px'}, 750);				
			} else {
				if (next > center) {
					$(".scroller").animate({'left': -left + 'px'}, 750);
				}
			}
		} else if (pro == 'prev') {
			next = $('.active').index('.img-child') - 1;

			if (next == '-1') {
				next = chld - 1;
			}

			limit = swid - hwid;
			left  = twid * (next - center);

			if (left > limit) {
				left = limit;
			}

			if (left < 0) {
				$(".scroller").animate({'left': '0px'}, 750);
			} else {
				if (next > center) {
					$(".scroller").animate({'left': -left + 'px'}, 750);
				}
			}
		} else {
			next  = pro;			
			limit = swid - hwid;
			left  = twid * (next - center);

			if (left > limit) {
				left = limit;
			}

			if (left < 0) {
				$(".scroller").animate({'left': '0px'}, 750);
			} else {				
				if (next > center) {
					$(".scroller").animate({'left': -left + 'px'}, 750);
				}
			}
		}

		if (next >= imglen) {
			next = 0;
		}

		if (next == -1) {
			next = imglen - 1;
		}

		$('.img-child').removeClass('active');
		$('.img-child').eq(next).addClass('active');

		$('.img-thumbs a').removeClass('active');
		$('.img-thumbs a').eq(next).addClass('active');

		tc = $('.img-thumbs a.active img');

		if (tc.data('imgtitle') != '' || tc.data('imgcaption')) {
			setTimeout(() => {
				$('#tct').html(tc.data('imgtitle'));
				$('#tcc').html(tc.data('imgcaption'));
				$('.tc-cont').addClass('tc-act')
			}, 800);
		} else {
			$('.tc-cont').removeClass('tc-act');
		}

		timeout = setTimeout((next_image), dur);
	}

	function toggle_fullscreen() {
		if ((document.fullScreenElement && document.fullScreenElement !== null) || (!document.mozFullScreen && !document.webkitIsFullScreen)) {
			if (document.documentElement.requestFullScreen) {
				document.documentElement.requestFullScreen();
			} else if (document.documentElement.mozRequestFullScreen) {
				document.documentElement.mozRequestFullScreen();
			} else if (document.documentElement.webkitRequestFullScreen) {
				document.documentElement.webkitRequestFullScreen(Element.ALLOW_KEYBOARD_INPUT);
			}

			$('.fsbtn').children('i').text('fullscreen_exit');
			$('.mob-holder').hide();
			$('.tc-holder').css('bottom', '300px');

			$('.images-parent').addClass('fullscreen');
			$('.thumbs-holder').addClass('fs-thumbs');
			$('.fs-info').addClass('fs-info-act');
		} else {
			if (document.cancelFullScreen) {
				document.cancelFullScreen();
			} else if (document.mozCancelFullScreen) {
				document.mozCancelFullScreen();
			} else if (document.webkitCancelFullScreen) {
				document.webkitCancelFullScreen();  
			}

			$('.fsbtn').children('i').text('fullscreen');
			$('.mob-holder').show();
			$('.tc-holder').css('bottom', '30px');

			$('.images-parent').removeClass('fullscreen');
			$('.thumbs-holder').removeClass('fs-thumbs');
			$('.fs-info').removeClass('fs-info-act');
		}
	}

	// mortgage calcu
	$(document).on('keyup', '#lprice', function() {
		var txt = this.value;
		var lp  = txt.match(/\d/g);
		lp      = lp.join("");

		$('#price').val(lp);
		// console.log(lp)
	});

	$(document).on('keypress keyup keydown change', '#down, #dptype', function() {
		val 	= $('#down').val();
		dpt 	= $('#dptype').val();
		price 	= $('#price').val();

		if (dpt === 'percentage' && parseInt(val) > 100) {
			$('#down').val(100);
		}

		if (dpt === 'dollar' && parseInt(val) > parseInt(price)) {
			$('#down').val(price)
		}
	});

	$(document).on('keypress keyup keydown', '#interest', function() {
		val = $(this).val();

		if (val > 100) {
			$(this).val(100);
		}
	});

	$(document).on('keypress keydown keyup change', 'input[type="number"], select, #lprice', compute_mortgage);

	compute_mortgage();

	function compute_mortgage() {
		price  = $('#price').val();
		down   = $('#down').val();
		dptype = $('#dptype').val();
		i      = ($('#interest').val() / 100) / 12;
		n      = $('#years').val() * 12;

		if (dptype === 'percentage') {
			down = price * (down / 100)
		}

		// M = P [ i(1 + i)^n ] / [ (1 + i)^n – 1]
		p 		= price - down;    
		left 	= p * (i * Math.pow((1 + i), n));
		right	= Math.pow((1 + i), n) - 1;
		m 		= left / right;
		m 		= m.toFixed(0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');

		$('#total').val('$' + m);
	}
	// mortgage calcu

});

$.fn.preload = function (callback) {
  var length = this.length;
  var iterator = 0;

  return this.each(function () {
    var self = this;
    var tmp = new Image();

    if (callback) tmp.onload = function () {
      callback.call(self, 100 * ++iterator / length, iterator === length);
    };

    tmp.src = this.src;
  });
};

// for launch : uncomment the view timer
$.fn.view_timer = () => {
	let vid = $('#viewid').val();

	vwtimer = setTimeout(() => {
		timer++;

		$.get('https://www.talktotucker.com/tours/fullscreen/time_record', { vid : vid, rec : timer	});

		$(document).view_timer();

	}, 1000);
}