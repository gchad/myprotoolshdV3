/**
 * Main JavaScript file
 *
 * @package         Tabs
 * @version         4.1.3
 *
 * @author          Peter van Westen <peter@nonumber.nl>
 * @link            http://www.nonumber.nl
 * @copyright       Copyright © 2015 NoNumber All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

(function($) {
	$(document).ready(function() {
		if (typeof( window['nn_tabs_use_hash'] ) != "undefined") {
			nnTabs.init();
		}
	});

	nnTabs = {
		init: function() {
			var $this = this;

			// Remove the transition durations off to make initial setting of active tabs as fast as possible
			$('.nn_tabs').removeClass('has_effects');

			if (nn_tabs_use_cookies) {
				$this.showByCookies();
			}

			this.showByURL();

			this.showByHash();

			setTimeout((function() {
				$this.initActiveClasses();

				$this.initContentHeights();

				$this.initResponsiveScrolling();

				$this.initClickMode();

				if (nn_tabs_mode == 'hover') {
					$this.initHoverMode();
				}

				if (nn_tabs_use_cookies || nn_tabs_set_cookies) {
					$this.initCookieHandling();
				}

				if (nn_tabs_use_hash) {
					$this.initHashHandling();
				}

				$this.initHashLinkList();

				if (nn_tabs_reload_iframes) {
					$this.initIframeReloading();
				}

				// Add the transition durations
				$('.nn_tabs').addClass('has_effects');
			}), 1000);

		},

		show  : function(id, scroll, openparents) {
			if (openparents) {
				this.openParents(id, scroll);
				return;
			}

			var $this = this;
			var $el = this.getElement(id);

			if (!$el.length) {
				return;
			}

			if (scroll) {
				if (!$el.hasClass('in')) {
					$el.one('shown shown.bs.tab', function(e) {
						$this.scroll(id);
					});
				} else {
					$this.scroll(id);
				}
			}

			$el.tab('show');

			$el.focus();
		},

		scroll: function(id) {
			var $el = this.getElement(id);

			if (!$el.length) {
				return;
			}

			// Scroll to tab
			$scrollto = $el.closest('ul:not(.dropdown-menu)').parent().find('.nn_tabs-scroll').first();

			if (!$scrollto.length) {
				return;
			}

			$('html,body').animate({scrollTop: $scrollto.offset().top});
		},

		setTabContentHeights: function() {
			$('.nn_tabs.bottom').each(function() {
				$(this).find('.tab-content').after($(this).find('.nav-tabs'));
			});

			$('.nn_tabs.left').each(function() {
				if ($(window).width() <= 767 && $(this).hasClass('nn_tabs-responsive')) {
					$(this).find('.tab-content')
						.css('margin-left', 0)
						.css('min-height', 0);

					return;
				}

				$(this).find('.tab-content')
					.css('margin-left', $(this).find('.nav-tabs').width())
					.css('min-height', $(this).find('.nav-tabs').height());
			});

			$('.nn_tabs.right').each(function() {
				if ($(window).width() <= 767 && $(this).hasClass('nn_tabs-responsive')) {
					$(this).find('.tab-content')
						.css('margin-right', 0)
						.css('min-height', 0);

					return;
				}

				$(this).find('.tab-content')
					.css('margin-right', $(this).find('.nav-tabs').width())
					.css('min-height', $(this).find('.nav-tabs').height());
			});
		},

		getElement: function(id) {
			return this.getTabElement(id);
		},

		getTabElement: function(id) {
			return $('a.nn_tabs-toggle[data-id="' + id + '"]');
		},

		getSliderElement: function(id) {
			return $('#' + id + '.nn_sliders-body');
		},

		showByCookies   : function() {
			var cookies = $.cookie(nn_tabs_cookie_name);
			if (!cookies) {
				return;
			}

			cookies = cookies.split('___');
			for (var i = 0; i < cookies.length; i++) {
				var keyval = cookies[i].split('=');
				if (keyval.length < 2) {
					continue;
				}

				var key = keyval.shift();
				if (key.substr(0, 11) != 'set-nn_tabs') {
					continue;
				}

				this.openParents(decodeURIComponent(keyval.join('=')), 0);
			}
		},

		showByURL: function() {
			var id = this.getUrlVar();

			if (id == '') {
				return;
			}

			this.showByID(id);
		},

		showByHash: function() {
			var id = decodeURIComponent(window.location.hash.replace('#', ''));

			if (id == '' || id.indexOf("&") != -1 || id.indexOf("=") != -1) {
				return;
			}

			// hash is a text anchor
			if ($('a.nn_tabs-toggle[data-id="' + id + '"]').length == 0) {
				this.showByHashAnchor(id);

				return;
			}

			// hash is a tab
			if (!nn_tabs_use_hash) {
				return;
			}

			if (!nn_tabs_urlscroll) {
				// Prevent scrolling to anchor
				$('html,body').animate({scrollTop: 0});
			}

			this.showByID(id);
		},

		showByHashAnchor: function(id) {
			if (id == '') {
				return;
			}

			var $anchor = $('a#' + id);

			if ($anchor.length == 0) {
				return;
			}

			// Check if anchor has a parent tab
			if ($anchor.closest('.nn_tabs').length == 0) {
				return;
			}

			var $tab = $anchor.closest('.tab-pane').first();

			// Check if tab has sliders. If so, let Sliders handle it.
			if ($tab.find('.nn_sliders').length > 0) {
				return;
			}

			this.openParents($tab.attr('id'), 0);

			setTimeout(function() {
				$('html,body').animate({scrollTop: $anchor.offset().top});
			}, 250);
		},

		showByID: function(id) {
			var $el = $('a.nn_tabs-toggle[data-id="' + id + '"]');

			if ($el.length == 0) {
				return;
			}

			this.openParents(id, nn_tabs_urlscroll);
		},

		openParents: function(id, scroll) {
			var $el = this.getElement(id);

			if (!$el.length) {
				return;
			}

			var parents = new Array;

			var parent = this.getElementArray($el);
			while (parent) {
				parents[parents.length] = parent;
				parent = this.getParent(parent.el);
			}

			if (!parents.length) {
				return false;
			}

			this.stepThroughParents(parents, null, scroll);
		},

		stepThroughParents: function(parents, parent, scroll) {
			$this = this;

			if (!parents.length && parent) {
				if (scroll) {
					if (typeof(scroll) == 'object') {
						$('html,body').animate({scrollTop: $(scroll).offset().top});
					} else {
						$this.scroll(parent.id);
					}
				}

				parent.el.focus();
				return;
			}

			parent = parents.pop();

			if (parent.el.hasClass('in') || parent.el.parent().hasClass('active')) {
				$this.stepThroughParents(parents, parent, scroll);
				return;
			}

			switch (parent.type) {
				case 'tab':
					if (typeof( window['nnTabs'] ) == "undefined") {
						$this.stepThroughParents(parents, parent, scroll);
						break;
					}

					parent.el.one('shown shown.bs.tab', function(e) {
						$this.stepThroughParents(parents, parent, scroll);
					});

					nnTabs.show(parent.id);
					break;

				case 'slider':
					if (typeof( window['nnSliders'] ) == "undefined") {
						$this.stepThroughParents(parents, parent, scroll);
						break;
					}

					parent.el.one('shown shown.bs.collapse', function(e) {
						$this.stepThroughParents(parents, parent, scroll);
					});

					nnSliders.show(parent.id);
					break;
			}
		},

		getParent: function($el) {
			if (!$el) {
				return false;
			}

			var $parent = $el.parent().closest('.nn_tabs-pane, .nn_sliders-body');

			if (!$parent.length) {
				return false;
			}

			var parent = this.getElementArray($parent);

			return parent;
		},

		getElementArray: function($el) {
			var id = $el.attr('data-toggle') ? $el.attr('data-id') : $el.attr('id');
			var type = ($el.hasClass('nn_tabs-pane') || $el.hasClass('nn_tabs-toggle')) ? 'tab' : 'slider'

			return {
				'type': type,
				'id'  : id,
				'el'  : type == 'tab' ? this.getTabElement(id) : this.getSliderElement(id)
			};
		},

		initActiveClasses: function() {
			$('li.nn_tabs-tab-sm').removeClass('active');
		},

		initHashLinkList: function() {
			var $this = this;

			$('a[href^="#"]').each(function($i, el) {
				$this.initHashLink(el);
			});
		},

		initHashLink: function(el) {
			var $this = this;
			var $link = $(el);

			// link is a tab or slider, so ignore
			if ($link.attr('data-toggle')) {
				return;
			}

			var id = $link.attr('href').replace('#', '');

			// No accompanying link found
			if (id == '') {
				return;
			}

			var $anchor = $('a#' + id);

			// No accompanying link found
			if ($anchor.length == 0) {
				return;
			}

			// Check if anchor has a parent tab
			if ($anchor.closest('.nn_tabs').length == 0) {
				return;
			}

			var $tab = $anchor.closest('.tab-pane').first();

			// Check if tab has sliders. If so, let Sliders handle it.
			if ($tab.find('.nn_sliders').length > 0) {
				return;
			}

			var tab_id = $tab.attr('id');

			// Check if link is inside the same tab
			if ($link.closest('.nn_tabs').length > 0) {
				if ($link.closest('.tab-pane').first().attr('id') == tab_id) {
					return;
				}
			}

			$link.click(function(e) {
				// Open parent tab and parents
				$this.openParents(tab_id, $anchor);
				e.stopPropagation();
			});
		},

		initHashHandling: function(el) {
			if (window.history.replaceState) {
				$('a.nn_tabs-toggle').on('shown shown.bs.tab', function(e) {
					var id = $(this).attr('data-id');
					history.replaceState({}, '', '#' + id);
					e.stopPropagation();
				});
			}
		},

		initClickMode: function(el) {
			$('body').on('click.tab.data-api', 'a.nn_tabs-toggle', function(e) {
				e.preventDefault();
				nnTabs.show($(this).attr('data-id'), $(this).hasClass('nn_tabs-doscroll'));
				e.stopPropagation();
			});
		},

		initHoverMode: function(el) {
			$('body').on('hover.tab.data-api', 'a.nn_tabs-toggle', function(e) {
				e.preventDefault();
				nnTabs.show($(this).attr('data-id'));
			});
		},

		initCookieHandling: function(el) {
			var $this = this;

			$('a.nn_tabs-toggle').on('show show.bs.tab', function(e) {
				var id = $(this).attr('data-id');
				var $el = $this.getElement(id);

				var set = 0;
				$el.closest('ul:not(.dropdown-menu)').each(function($i, el) {
					set = el.id;
				});

				var obj = {};
				var cookies = $.cookie(nn_tabs_cookie_name);
				if (cookies) {
					cookies = cookies.split('___');
					for (var i = 0; i < cookies.length; i++) {
						var keyval = cookies[i].split('=');
						if (keyval.length > 1 && keyval[0] != set) {
							var key = keyval.shift();
							if (key.substr(0, 11) == 'set-nn_tabs') {
								obj[key] = keyval.join('=');
							}
						}
					}
				}
				obj['set-nn_tabs-' + set] = id;

				var arr = [];
				for (var i in obj) {
					if (i && obj[i]) {
						arr[arr.length] = i + '=' + obj[i];
					}
				}

				$.cookie(nn_tabs_cookie_name, arr.join('___'));
			});
		},

		/* >>> [PRO] >>> */
		initContentHeights: function() {
			var $this = this;

			$this.setTabContentHeights();

			$('a.nn_tabs-toggle').on('shown shown.bs.tab', function(e) {
				$this.setTabContentHeights();
			});

			$(window).resize(function() {
				$this.setTabContentHeights();
			});
		},

		initResponsiveScrolling: function() {
			var $this = this;

			$('.nav-tabs-sm a.nn_tabs-link').click(function() {
				var $el = $this.getElement($(this).attr('data-id'));
				$('html,body').animate({scrollTop: $el.offset().top});
			});
		},

		initIframeReloading: function() {
			var $this = this;

			$('.tab-pane.active iframe').each(function() {
				$(this).attr('reloaded', true);
			});

			$('a.nn_tabs-toggle').on('show show.bs.tab', function(e) {
				// Re-inintialize Google Maps on tabs show
				if (typeof initialize == 'function') {
					initialize();
				}

				var $el = $('#' + $(this).attr('data-id'));

				$el.find('iframe').each(function() {
					if (this.src && !$(this).attr('reloaded')) {
						this.src += '';
						$(this).attr('reloaded', true);
					}
				});
			});

			$(window).resize(function() {
				if (typeof initialize == 'function') {
					initialize();
				}

				$('.tab-pane iframe').each(function() {
					$(this).attr('reloaded', false);
				});

				$('.tab-pane.active iframe').each(function() {
					if (this.src) {
						this.src += '';
						$(this).attr('reloaded', true);
					}
				});
			});
		},

		getUrlVar: function() {
			var search = 'tab';
			var query = window.location.search.substring(1);

			if (query.indexOf(search + '=') == -1) {
				return '';
			}

			var vars = query.split('&');
			for (var i = 0; i < vars.length; i++) {
				var keyval = vars[i].split('=');

				if (keyval[0] != search) {
					continue;
				}

				return keyval[1];
			}

			return '';
		}
	};
})
(jQuery);
