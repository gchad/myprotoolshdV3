<?php
/**
 * ------------------------------------------------------------------------
 * JA Promo Bar module
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

$css 		= $params->get('always_top', 1) ? 'fixed' : '';
$text 		= $params->get('message');
$speed 		= $params->get('animation_speed', 'slow');
$easing 	= $params->get('animation_easing', 'linear');

//Countdown document: http://hilios.github.io/jQuery.countdown/documentation.html
//Convert to UTC time
$serverTZ = new DateTimeZone(date('e'));
$utcTZ = new DateTimeZone('UTC');
$date = new DateTime($params->get('countdown_end_date'), $serverTZ);
$date->setTimezone($utcTZ);

//$countdown_end_date = date('Y/m/d H:i:s', $date->format('Y-m-d H:i:s'));
$countdown_expired_text = addslashes($params->get('countdown_expired_text', ''));
$countdown_format = $params->get('countdown_format', '%w weeks %D days %H:%M:%S');
$countdown_format = preg_replace('/\%[a-zA-Z]/i', '<span class="digit">$0</span>', $countdown_format);

$countdown_html = $countdown ? '<div class="ja-promo-bar-countdown" id="ja-promo-bar-countdown-'.$module->id.'"></div>' : '';

if(strpos($easing, '_') === 0) {
	$easing = ucfirst(substr($easing, 1));
	$easingOpen = 'easeIn'.$easing;
	$easingClose = 'easeOut'.$easing;
} else {
	$easingOpen = $easingClose = $easing;
}

$link_text = $params->get('link_text', '');
$button = '';
if(!empty($link_text)) {
	$link_url = $params->get('link_url', '#');
	$link_target = $params->get('link_target', '_self');
	$link_style = $params->get('link_style', 'button');

	$button_class = ($link_style == 'button') ? 'button' : '';
	if($link_target == 'popup') {
		$link_url = "javascript:Joomla.popupWindow('{$link_url}', '', 800, 450, 'yes')";
		$button = sprintf('<a class="%s" href="%s" title="%s">%s</a>', $button_class, $link_url, htmlspecialchars($link_text), $link_text);
	} else {
		$button = sprintf('<a class="%s" href="%s" target="%s" title="%s">%s</a>', $button_class, $link_url, $link_target, htmlspecialchars($link_text), $link_text);
	}
}

//replace variables
if(strpos($text, '{button}') !== false) {
	$text = str_replace('{button}', $button, $text);
}
if(strpos($text, '{countdown}') !== false) {
	$text = str_replace('{countdown}', $countdown_html, $text);
}

?>
<style type="text/css">
#ja-promo-bar-<?php echo $module->id?> .inner {
	background: <?php echo $params->get('bgcolor', '#DD3333'); ?>;
	color: <?php echo $params->get('textcolor', '#FFFFFF'); ?>;
	border-bottom:1px solid <?php echo $params->get('bordercolor', '#DB5555'); ?>;
}
#ja-promo-bar-<?php echo $module->id?> .inner .button {
	background: <?php echo $params->get('button_color', '#333333'); ?>;
	color: <?php echo $params->get('button_text_color', '#FFFFFF'); ?>;
}
#ja-promo-bar-<?php echo $module->id?> .buttons span {
	background-color: <?php echo $params->get('control_color', '#B82828'); ?>;
}
#ja-promo-bar-countdown-<?php echo $module->id?> {
	background-color: <?php echo $params->get('countdown_bgcolor', '#EA7777'); ?>;
	color: <?php echo $params->get('countdown_text_color', '#000000'); ?>;
}
#ja-promo-bar-countdown-<?php echo $module->id?> .digit {
    background-color: <?php echo $params->get('countdown_number_bgcolor', '#000000'); ?>;
    color: <?php echo $params->get('countdown_number_color', '#ffffff'); ?>;
}

<?php echo $params->get('custom_css', ''); ?>

</style>

<div id="ja-promo-bar-<?php echo $module->id?>" class="ja-promo-bar <?php echo $css; ?>">
	<div class="inner normal-msg">
		<?php echo $text; ?>
	</div>
	<div class="buttons">
		<span class="icon-toogle opened"><?php echo JText::_('TOGGLE_BAR'); ?></span>
	</div>
</div>

<script type="text/javascript">
	(function($){
		$(document).ready(function() {
			//trigger for toggle button
			$('#ja-promo-bar-<?php echo $module->id?> .icon-toogle').click(function() {
				if(getCookie('ja_promo_bar_<?php echo $module->id?>')=='opened') {
					jaClosePromoBar();
				} else {
					jaOpenPromoBar();
				}
			});
			
			//Display countdown
			<?php if($countdown): ?>
			var endate_utc = new Date(<?php echo $date->getTimestamp() * 1000; ?>);
			var endate_local = new Date(endate_utc.getTime() - (endate_utc.getTimezoneOffset() * 60000));
			var selectedDate = endate_local.valueOf();

			$('#ja-promo-bar-countdown-<?php echo $module->id?>').countdown(selectedDate.toString(), function(event) {
					$(this).html(event.strftime('<?php echo $countdown_format; ?>'));
				}) .on('finish.countdown', function() {
					$(this).html("<?php echo $countdown_expired_text; ?>");
				});
			<?php endif; ?>

			//push page down
            <?php if($params->get('push_page_down', 1)):?>
			if(getCookie('ja_promo_bar_<?php echo $module->id?>') == 'opened' || getCookie('ja_promo_bar_<?php echo $module->id?>') == ''){
				jaPromoBarBody('opened');
				//fix conflict with t3 menu
				jaPromoBarT3Nav('opened');
			}
            <?php endif;?>
			
            //check previous status bar
            if(getCookie('ja_promo_bar_<?php echo $module->id?>') == 'closed'){
                jaClosePromoBar();
            }else{
				jaOpenPromoBar();
            }
            
            //hide bar when scroll window
            <?php if((int)$params->get('always_top', 1)==1): ?>
            $(window).scroll(function(){
                if(getCookie('ja_promo_bar_<?php echo $module->id?>') == 'opened' || getCookie('ja_promo_bar_<?php echo $module->id?>') == ''){
                    if($(window).scrollTop() > parseInt($('#ja-promo-bar-<?php echo $module->id?>').outerHeight())){
                        $('#ja-promo-bar-<?php echo $module->id?>').css({opacity:<?php echo $params->get('sticky_opacity', 1)?>});
                    }else{
                        $('#ja-promo-bar-<?php echo $module->id?>').css({opacity:1});
                    }
                }
            });
			<?php endif; ?>
			
            //window resize
            $(window).resize(function() {});
			
		});
		
		function jaClosePromoBar(){
			var h = parseInt($('#ja-promo-bar-<?php echo $module->id?>').outerHeight());
			$('#ja-promo-bar-<?php echo $module->id?> .inner').animate({'top':'-'+h+'px'}, '<?php echo $speed; ?>', '<?php echo $easingClose; ?>');
			$('#ja-promo-bar-<?php echo $module->id?> .inner').hide('<?php echo $speed; ?>');
			$('#ja-promo-bar-<?php echo $module->id?> .icon-toogle').removeClass('opened').addClass('closed');
			jaPromoBarT3Nav('closed');
			jaPromoBarBody('closed');
			setCookie('ja_promo_bar_<?php echo $module->id?>','closed',1);
		}
		
		function jaOpenPromoBar(){
			$('#ja-promo-bar-<?php echo $module->id?> .inner').css('display', 'block');
			$('#ja-promo-bar-<?php echo $module->id?> .inner').show('<?php echo $speed; ?>', function() {
				$('#ja-promo-bar-<?php echo $module->id?> .inner').animate({'top':0}, '<?php echo $speed; ?>', '<?php echo $easingClose; ?>');
				$('#ja-promo-bar-<?php echo $module->id?> .icon-toogle').removeClass('closed').addClass('opened');
				jaPromoBarT3Nav('opened');
				jaPromoBarBody('opened');
				setCookie('ja_promo_bar_<?php echo $module->id?>','opened',1);
			});
		}
		
		function jaPromoBarBody(type){
			var ja_promo_bar_height = $('#ja-promo-bar-<?php echo $module->id?>').outerHeight();
			var ja_t3_main_nav = 0;
			if($('.navbar-collapse-fixed-top').length && $(window).width()<641){
				ja_t3_main_nav = $('.navbar-collapse-fixed-top').outerHeight() + 10;
			}
			
			if(type=='opened' || type==''){
				$('body').animate({'padding-top': (ja_promo_bar_height + ja_t3_main_nav) + 'px'}, '<?php echo $speed; ?>', '<?php echo $easingOpen; ?>');
			}else{
				$('body').animate({'padding-top': ja_t3_main_nav+'px'}, '<?php echo $speed; ?>', '<?php echo $easingOpen; ?>');
			}
		}
		
		function jaPromoBarT3Nav(type){
			if(!type){
				type = getCookie('ja_promo_bar_<?php echo $module->id?>');
			}
			var ja_promo_bar_height = $('#ja-promo-bar-<?php echo $module->id?>').outerHeight();
			
			if($('.navbar-collapse-fixed-top').length){
				if(type=='opened' || type==''){
					$('.navbar-collapse-fixed-top').animate({'top': ja_promo_bar_height+'px'}, '<?php echo $speed; ?>', '<?php echo $easingOpen; ?>');
				}else{
					$('.navbar-collapse-fixed-top').animate({'top': 0}, '<?php echo $speed; ?>', '<?php echo $easingOpen; ?>');
				}
			}
		}
		
		//set user cookie
		function setCookie(cname,cvalue,exdays){
			var d = new Date();
			d.setTime(d.getTime()+(exdays*24*60*60*1000));
			var expires = "expires="+d.toGMTString();
			document.cookie = cname + "=" + cvalue + "; " + expires;
		}

		function getCookie(cname){
			var name = cname + "=";
			var ca = document.cookie.split(';');
			for(var i=0; i<ca.length; i++)
			{
				var c = ca[i].trim();
				if (c.indexOf(name)==0) return c.substring(name.length,c.length);
			}
			return "";
		}
	}(jQuery));
</script>