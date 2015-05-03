/**
 * @package			Responsive Scroll Triggered Box
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2015 TM Extensions All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

var jqRSTBox = jQuery.noConflict();

(function($) {

	/* jQuery Pageleave */
	!function(a){function c(){a(b.container).on("mousemove.pageleave",function(a){var c=(new Date).getTime()-start;a.clientY<=b.limitY&&a.clientX<=b.limitX&&c>=b.timeTillActive&&(times>0&&times--,"function"==typeof b.callback?b.callback.call(this):d())})}function d(){a(b.container).trigger("pageleave"),0==times&&a(b.container).off("mousemove.pageleave")}var b=times=start=null;a.fn.pageleave=function(d){b=a.extend({},a.fn.pageleave.defaultOptions,d),times=b.times,start=(new Date).getTime(),c()}}(jQuery),$.fn.pageleave.defaultOptions={container:document,limitX:screen.width,limitY:5,timeTillActive:100,times:1,callback:null};

	$(window).load(function() {
		var logprefix = "RSTB";
		var rstboxes = $(".rstboxes").last();

		if (!rstboxes.children().length) {
			console.log(logprefix+': Can\'t find any boxes on this page. Exiting..');
			return;
		}

		var cookiePrefix = rstboxes.data("site")+"_";
		var windowHeight = $(window).height();
		var documentHeight = $(document).height();
		var log = rstboxes.data("debug");

		function xlog(str) {
			if (log) {
				console = window.console || { log: function() { } };
				console.log(logprefix+": "+str)
			}
		}

		function createCookie(name,value,days) {
			if (days) {
				var date = new Date();
				date.setTime(date.getTime()+(days*24*60*60*1000));
				var expires = "; expires="+date.toGMTString();
			}
			else var expires = "";
			document.cookie = cookiePrefix+name+"="+value+expires+"; path=/";
		}

		function readCookie(name) {
			var nameEQ = cookiePrefix+name + "=";
			var ca = document.cookie.split(';');
			for(var i=0;i < ca.length;i++) {
				var c = ca[i];
				while (c.charAt(0)==' ') c = c.substring(1,c.length);
				if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
			}
			return null;
		}

		function toggleBox(box, show) {
			var boxID = box.attr("id");
			var boxID = boxID.replace("rstbox_","")+"#";

			if (box === undefined ) {
				xlog("Box "+boxID+" is not present in the current page.");
				return;
			}

			// don't do anything if box is undergoing an animation
			if(box.is(":animated")) {
				return false;
				xlog("Box "+boxID+" is animating. Exiting..");
			}

			// don't do anything if box is already at desired visibility
			if ((show === true && box.is(":visible")) || (show === false && box.is(":hidden"))) {
				return false;
				xlog("Box "+boxID+" is already at desired visibility. Exiting..")
			}

			// Show or hide box
			if (show) {
				box.trigger("beforeOpen");
				xlog("Box "+boxID+" is going to open!")
			} else {
				box.trigger("beforeClose");
				xlog("Box "+boxID+" is going to close!");
			}

			var animation = box.data('anim');

			// If box is window centered then we use only fade animation 
			// because of bad behavior of slide effect in absolute center elements
			if (box.hasClass("rstbox_center")) { animation = "fade"; }

			// Setup animations
			delay = box.data("delay") || 0;
			if (!show) { delay = 0; }

			if (animation === 'fade') {
				setTimeout(function() { 
					box.fadeToggle("fast", function() {
						box.trigger((show) ? "afterOpen" : "afterClose");
					})
				}, 
				delay);
			} else {
				setTimeout(function() { 
					box.slideToggle("fast", function() {
						box.trigger((show) ? "afterOpen" : "afterClose");
					})
				}, 
				delay);
			}

			return show;
		}

		$('a[data-rstbox]').on("click", function() {
			boxID = parseInt($(this).data("rstbox"));
			box = $(".rstboxes > #rstbox_"+boxID);

			if (!box) {
				return;
			}

			boxCommand = $(this).data("rstbox-command");

			switch (boxCommand) {
				case "open":
					box.trigger("open");
					break;
				case "close":
					box.trigger("close");
					break;
				case "closeKeep":
					box.trigger("closeKeep");
					break;
				default:
					if (box.is(":visible")) {
						box.trigger("close");
					} else {
						box.trigger("open");
					}			
				break;
			}

			return false;
		});

		rstboxes.find("> .rstbox").each(function() {
			var box = $(this);
			var boxID = box.attr("id").replace("rstbox_","")+"#";
			var timer = 0;
			var triggerMethod = box.data('trigger').split(":")[0];
			var triggerMethodSettings = (triggerMethod != "pageload") ? box.data('trigger').split(":")[1] : false;
			var triggerHeight = false;
			var cookieExist = readCookie(box.attr("id"));
			var autoHide = (box.data("autohide") == "1") ? true : false;
			var testMode = (box.data("testmode").split(":")[0] == "1") ? true : false;
			var isroot = (box.data("testmode").split(":")[1] == "1") ? true : false;

			switch (triggerMethod) {
				case "pageheight":
					var triggerPercentage = parseInt(triggerMethodSettings, 10) / 100
					triggerHeight = (triggerPercentage * documentHeight);
					xlog("Box " +boxID+ " will be trigger at " + triggerHeight + "/" + documentHeight);
					break;
				case "element":
					var triggerElement = $(triggerMethodSettings).filter(":first"); 

					if(triggerElement.length == 0) { 
						/* Trigger element does not exist */
						xlog('Box '+boxID+' Can\'t find element "'+ triggerMethodSettings +'". Not showing box.');
					} else {
						/* Trigger element exist */
						xlog("Box " +boxID+ " will be trigger at " + triggerMethodSettings);
						triggerHeight = triggerElement.offset().top;
					}

					break;
			}

			/* Assign Manual Trigger Events */
			box.bind('open', function() { 
				toggleBox(box, true); 
			});

			box.bind('close', function() { 
				toggleBox(box, false); 
			});

			box.bind('closeKeep', function() { 
				box.find(".rstbox-close").trigger("click");
			});

			// Overlay Check
			box.bind("afterOpen", function() {
				ovrl = box.data("overlay");

				if (ovrl) {
					overlay = ovrl.split(":");
					overlayColor = overlay[1];
					overlayOpacity = overlay[0];

					overlayObject = jQuery('<div/>', {
					    id: 'overlay_'+box.attr("id"),
					    class: 'rstbox_overlay',
					    css: {
					    	"background-color": overlay[1],
							"opacity": overlay[0]	
					    }
					}).appendTo('body');

					overlayClick = parseInt(overlay[2]);
					if (overlayClick) {
						overlayObject.on("click", function() {
							box.trigger("closeKeep");
						})
					}

					box.bind("beforeClose", function() {
						overlayObject.remove();
					});
				}
			})
			
			// End of Overlay Check

			box.bind("afterOpen", function() {
				box.addClass("visible");
				xlog("Box " +boxID+ " opened");
				$("body").addClass(box.attr("id"));
			})

			box.bind("afterClose", function() {
				box.removeClass("visible");
				xlog("Box " +boxID+ " closed");
				$("body").removeClass(box.attr("id"));
			})

			/* Test Mode Check */
			if (testMode) {
				xlog("Box " +boxID+ " is on test mode");
			}

			box.find(".rstbox-close").click(function() {
				// hide box
				toggleBox(box, false);

				// unbind box scroll event
				$(window).unbind('scroll.box'+parseInt(boxID), scrollCheck);

				// If box is not on testMode set a cookie expiration on the browser
				if (!testMode) {
					cook = parseInt(box.data("cookie"))
					if (cook > 0) {
						createCookie(box.attr("id"), true, cook);
						xlog("Box " +boxID+ " Cookie set");
					}
				}
				return false;
			});

			/* Cookie Check */
			if ((cookieExist) && (!testMode)) {
				xlog("Box "+boxID+" will be hidden. There is a cookie expiration on this browser");
				return;
			}

			/* If we have triggerHeight let's setup some events */
			if (triggerHeight) {
				var scrollCheck = function() {
					if (timer) {
						clearTimeout(timer);
					}

					timer = window.setTimeout(function () {
						var scrollY = $(window).scrollTop();
						var triggered = ((scrollY + windowHeight) >= triggerHeight);

						xlog("Scroll current position: " +(scrollY + windowHeight))

						if (triggered) {
							if (!autoHide) {
								$(window).unbind('scroll.box'+parseInt(boxID), scrollCheck);
							}

							toggleBox(box, true);
						} else {
							toggleBox(box, false);
						}
					}, 100);
				};

				$(window).bind('scroll.box'+parseInt(boxID), scrollCheck);			
			}

			if (triggerMethod == "userleave") {
				xlog("Box "+boxID+ " is going to be triggered on user leave");

				$(document).on('pageleave', function() {
			        toggleBox(box, true);
			    });
			    $.fn.pageleave();
			}

			if (triggerMethod == "pageload") {
				xlog("Box "+boxID+ " is going to be triggered at page load");
				toggleBox(box, true);
			}
		})
	});
}(jqRSTBox));