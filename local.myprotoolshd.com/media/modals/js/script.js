/**
 * Main JavaScript file
 *
 * @package         Modals
 * @version         6.0.0d
 *
 * @author          Peter van Westen <peter@nonumber.nl>
 * @link            http://www.nonumber.nl
 * @copyright       Copyright © 2015 NoNumber All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

(function($) {
	$(document).ready(function() {
		if (typeof( window['modals_defaults'] ) != "undefined") {
			initModals();
		}
	});

	initModals = function() {
		$.each($('.' + modals_class), function(i, el) {
			var $el = $(el);
			var defaults = $.extend({}, modals_defaults);

			// Get data from tag
			$.each(el.attributes, function(index, attr) {
				if (attr.name.indexOf("data-modal-") === 0) {
					var key = $.camelCase(attr.name.substring(11));
					defaults[key] = attr.value;
				}
			});

			// remove width/height if inner is already set
			if (defaults['innerWidth'] != undefined) {
				delete defaults['width'];
			}
			if (defaults['innerHeight'] != undefined) {
				delete defaults['height'];
			}

			if (defaults['delay'] != undefined) {
				if (defaults['open'] != undefined) {
					delete defaults['open'];
				} else {
					delete defaults['delay'];
				}
			}

			if (modals_open_by_url) {
				delete defaults['open'];
			}

			// set true/false values to booleans
			for (key in defaults) {
				if (defaults[key] == 'true') {
					defaults[key] = true;
				} else if (defaults[key] == 'false') {
					defaults[key] = false;
				} else if (!isNaN(defaults[key])) {
					defaults[key] = parseFloat(defaults[key]);
				}
			}

			var onOpen = defaults['onOpen'];
			defaults['onOpen'] = function() {
				typeof onOpen !== 'undefined' && setTimeout(onOpen, 1);
			};

			var onLoad = defaults['onLoad'];
			defaults['onLoad'] = function() {
				typeof onLoad !== 'undefined' && setTimeout(onLoad, 1);
			};

			var onCleanup = defaults['onCleanup'];
			defaults['onCleanup'] = function() {
				typeof onCleanup !== 'undefined' && setTimeout(onCleanup, 1);
			};

			
			var autocloseTimeout = false;
			var onComplete = defaults['onComplete'];
			defaults['onComplete'] = function() {
				typeof onComplete !== 'undefined' && setTimeout(onComplete, 1);
				modalsResize();

				if (defaults['autoclose'] != undefined && defaults['autoclose']) {
					var time = parseInt(defaults['autoclose']);
					time = time == 1 ? 5000 : time;
					$('#cboxTitle .countdown').animate({
						width: 0
					}, time, 'linear');
					autocloseTimeout = setTimeout(function() {
						$el.colorbox.close()
					}, time);
				}

				$('#colorbox').addClass('complete');
			};

			var onClosed = defaults['onClosed'];
			defaults['onClosed'] = function() {
				typeof onClosed !== 'undefined' && setTimeout(onClosed, 1);
				clearTimeout(autocloseTimeout);
				$('#colorbox').removeClass('complete');
			};

			// Bind the modal script to the element
			$el.colorbox(defaults);

			if (defaults['delay'] != undefined) {
				setTimeout(function() {
					$el.click();
				}, defaults['delay']);
			}

			/* Disable Colorbox on mobile devices */
			if (modals_disable_on_mobile) {
				if ($(window).width() <= 767) {
					$el.colorbox.remove();
					if (el.href.match(/([\?&](ml|iframe))=1/g)) {
						el.href = el.href.replace(/([\?&](ml|iframe))=1/g, '$1=0');
					}
				}
				$(window).resize(function() {
					if ($(window).width() <= 767) {
						$el.colorbox.remove();
						if (el.href.match(/([\?&](ml|iframe))=1/g)) {
							el.href = el.href.replace(/([\?&](ml|iframe))=1/g, '$1=0');
						}
					} else {
						if (el.href.match(/([\?&](ml|iframe))=0/g)) {
							el.href = el.href.replace(/([\?&](ml|iframe))=0/g, '$1=1');
						}
						$el.colorbox(defaults);
					}
				});
			}
		});

		if (modals_open_by_url) {
			$.each($('.' + modals_class), function(i, el) {
				var $el = $(el);

				if (modals_open_by_url == $el.attr('id')
					|| modals_open_by_url == $el.attr('data-modal-rel')
					|| modals_open_by_url == $el.attr('href').replace(/.*?\#/g, '')) {
					$el.click();
				}
			});
		}
	};

	modalsResize = function() {
		$.each($('#colorbox'), function(i, el) {
			var $el = $(el);
			var $title = $('#cboxTitle');
			var $content = $('#cboxLoadedContent');

			var $title_height = $title.outerHeight() + 1;
			var $margin_top = parseInt($content.css('marginTop'));

			if ($title_height > $margin_top) {
				var $div_height = $title_height - $margin_top;
				$content.css('marginTop', $title_height);

				if (parseInt($el.css('top')) < 23) {
					// resize the inner content
					$content.css('height', parseInt($content.css('height')) - $div_height);
				} else {
					// resize the window
					$el.css('height', parseInt($el.css('height')) + $div_height);
					$el.css('top', parseInt($el.css('top')) - ($div_height / 2));
					$('#cboxWrapper').css('height', parseInt($('#cboxWrapper').css('height')) + $div_height);
					$('#cboxContent').css('height', parseInt($('#cboxContent').css('height')) + $div_height);
					$('#cboxMiddleLeft').css('height', parseInt($('#cboxMiddleLeft').css('height')) + $div_height);
					$('#cboxMiddleRight').css('height', parseInt($('#cboxMiddleRight').css('height')) + $div_height);
				}
			}
		});
	};
})(jQuery);
