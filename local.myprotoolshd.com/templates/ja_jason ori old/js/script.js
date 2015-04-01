/**
 * ------------------------------------------------------------------------
 * JA Jason template
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
 */
 
(function($){
	//Add grayscale image for partners 
	$(window).load(function() {
		$('.frameworks img').each(function() {
			$(this).wrap('<div style="display:inline-block;width:' + this.width + 'px;height:' + this.height + 'px;">').clone().addClass('gotcolors').css({'position': 'absolute', 'opacity' : 0 }).insertBefore(this);
			this.src = grayscale(this.src);
		}).animate({opacity: 0.5}, 500);
	});
	
	function grayscale(src) {
		var supportsCanvas = !!document.createElement('canvas').getContext;
		if (supportsCanvas) {
			var canvas = document.createElement('canvas'), 
			context = canvas.getContext('2d'), 
			imageData, px, length, i = 0, gray, 
			img = new Image();
			
			img.src = src;
			canvas.width = img.width;
			canvas.height = img.height;
			context.drawImage(img, 0, 0);
				
			imageData = context.getImageData(0, 0, canvas.width, canvas.height);
			px = imageData.data;
			length = px.length;
			
			for (; i < length; i += 4) {
				//gray = px[i] * .3 + px[i + 1] * .59 + px[i + 2] * .11;
				//px[i] = px[i + 1] = px[i + 2] = gray;
				px[i] = px[i + 1] = px[i + 2] = (px[i] + px[i + 1] + px[i + 2]) / 3;
			}
					
			context.putImageData(imageData, 0, 0);
			return canvas.toDataURL();
		} else {
			return src;
		}
	}
	//Fix bug tab typography
	$(document).ready(function(){
		if($('.docs-section .nav.nav-tabs').length > 0){
			$('.docs-section .nav.nav-tabs a').click(function (e) {
				e.preventDefault();
				$(this).tab('show');
            });
		}
		
		// Button middle hikashop
		if($('#hikashop_add_wishlist').length > 0) {
			$('.hikashop_product_stock').each(function(i) {
				$(this).css('margin-top', -($(this).height()/2));
			});
		}
		
		// set body height
		var windowsHeight = $(window).height();
		if($('.t3-header-video .ja-intro-1').length > 0) {
			$('.t3-header-video .ja-intro-1').css('padding-top', ( ($('#t3-header').outerHeight() - $('.ja-intro-1').height())/2 ) + 'px');
			$('.t3-header-video .ja-intro-1').css('padding-bottom', (($('#t3-header').outerHeight() - $('.ja-intro-1').height())/2 ) + 'px');
			
			$(window).resize(function() {
				$('.t3-header-video .ja-intro-1').css('padding-top', ( ($('#t3-header').outerHeight() - $('.ja-intro-1').height())/2 ) + 'px');
				$('.t3-header-video .ja-intro-1').css('padding-bottom', ( ($('#t3-header').outerHeight() - $('.ja-intro-1').height())/2 ) + 'px');
			});
		}
		
		
		if($('#portfolio-carousel').length > 0) {
			$('#portfolio-carousel').css('height', (windowsHeight-$('#t3-footer').height()-$('#t3-header').height()) + 'px');
			
			$(window).resize(function() {	
				var windowsHeight = $(window).height();
				$('#portfolio-carousel').css('height', (windowsHeight-$('#t3-footer').height()-$('#t3-header').height()) + 'px');
				
			});
		}
		
		$('input.form-control').placeholder();
		
		$('.modal').each(function(i) {
        var modal = $(this);
        if (modal.hasClass('hide')) {
            modal.attr('role', 'dialog').removeClass('hide').removeAttr('style');
            modal.html('<div class="modal-dialog modal-lg">' +
                '<div class="modal-content">' + modal.html() + '</div>' +
                '</div>');

            var oldModal = modal;
            modal.on('shown.bs.modal', function(e) {
                oldModal.trigger('show');
            });
        }
    });
    
    $(".frameworks a").hover(
			function() {
				$(this).find('.gotcolors').stop().animate({opacity: 1}, 200);
			}, 
			function() {
				$(this).find('.gotcolors').stop().animate({opacity: 0}, 500);
			}
		);
		
	});
	
 })(jQuery);
 
 
 //Portfolio page - Used JQuery Isotope & infinitescroll
(function($){
  $(document).ready(function(){
    var $container = $('.ja-masonry-wrap #grid');

    if (!$container.length) return ;

    $container.isotope({
      itemSelector: '.isotope-item',
      masonry: {
        columnWidth: '.grid-sizer',
        gutter: 0
      }
    });
    
    // re-order when images loaded
    $container.imagesLoaded(function(){
      $container.isotope();
    
      /* fix for IE-8 */
      setTimeout (function() {
        $('.ja-masonry-wrap #grid').isotope();
      }, 2000);  
    });
 
  });

})(jQuery);
