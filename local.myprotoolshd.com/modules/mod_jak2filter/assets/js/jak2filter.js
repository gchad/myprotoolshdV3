/**
 * ------------------------------------------------------------------------
 * JA K2 Filter Module for J25 & J3.3
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */
function jak2DisplayExtraFields (moduleid, obj, selected_group) {
	var sOption = obj.getSelected();
	var group = sOption.getProperty('rel');
	var value = sOption.get("value");
	
	var parent = obj.getParent('.ja-k2filter');
	var parentid = parent.id;
	
	$$('#'+parentid+' .exfield').each(function(item){
		magicid = $(item).get('id') .toString();
		if('m'+magicid){		
			if($(item).hasClass('opened')) {
				$(item).removeClass('opened');
				$(item).addClass('closed');
				$('m'+magicid).setStyle('display', 'none');
			} else if($(item).hasClass('closed')) {

			}
		}
	});

    if((value != 0 && group != '') || selected_group) {
        if(group == '') {
            group = selected_group;
        }
        jQuery('#'+parentid).find('.heading-group').each(function() {
            if(this.hasClass('heading-group-'+group)) {
                this.removeClass('ui-accordion-disabled ui-state-disabled');
                if(!this.hasClass('ui-state-active')) {
                    var accor = jQuery('#ja-extra-field-accordion-'+moduleid);
                    accor.accordion('activate', this);
                }
            } else {
                //clear value of extra fields in group that not associated with selected category
                this.addClass('ui-accordion-disabled ui-state-disabled');
                jaK2Reset(moduleid, jQuery(this).next('.ui-accordion-content'), false);
            }
        });
    } else {
        jQuery('#'+parentid).find('.heading-group').removeClass('ui-accordion-disabled ui-state-disabled');
    }
}

function jaK2Reset(moduleId, container, submitform)
{
    //var form = jQuery('#'+formId);
    if(typeof(container) == 'string') {
        container = jQuery('#'+container);
    }
	//reset input
    container.find('input[type=text], textarea').val('');

    //radio, checkbox
    container.find(':checked').each(function(item){
        jQuery(this).removeAttr('checked');
    });

    //select, multi-select
    container.find('select option:selected').each(function(item){
		jQuery(this).removeAttr('selected');
	});
	
	//reset magic select
    container.find('.ja-magic-select ul li').each(function(item)
	{
        jQuery(this).removeClass('selected');
	});
    container.find('.ja-magic-select-container').each(function(item)
	{
        jQuery(this).html('');
	});

    //reset range slider
    container.find('[name$="_jacheck"]').each(function(el) {
        var sliders = jQuery('#slider_'+this.id.replace('_jacheck', ''));
        if(sliders) {
            var val = this.value.split('|');
            sliders.slider('values', 0, val[0]);
            sliders.slider('values', 1, val[1]);
        }
    });

    //submit form?
    if(submitform) {
        if(container.prop('tagName').toLowerCase() != 'form') {
            var form = container.parents('form');
        } else {
            var form = container;
        }
        var autofilter = form.find('[name="btnSubmit"]').length;
        if(!autofilter) {
            if(typeof(form.submit) == 'function') {
                form.submit();
            }
        }
    }
}

function jaMagicInit(lid, fid) {

	$$('#'+lid+' li').each(function(item){
		if(item.hasClass('selected')) {
			jaMagicAddElement(lid, fid, item.innerHTML, item.getProperty('rel'));
		}
	});

	$$('#'+lid+' li.active').each(function(item){
		item.addEvent('click', function() {
			var id = this.getProperty('rel');
			if(!id) return;
			
		    if(this.hasClass('selected')) {
		    	this.removeClass('selected');
		    	$(lid+'-'+id).dispose();
		    } else {
		    	this.addClass('selected');
		    	jaMagicAddElement(lid, fid, this.innerHTML, id);
		    }
		    var autofilter = $(lid).getProperty('data-autofilter');
		    if(autofilter == 1) {
		    	$(lid).getParent('form').fireEvent('submit');
		    }
		});
	    
	});
}

function jaMagicAddElement(lid, fid, label, id) {
	var container = $(lid+'-container');
	var el = new Element('span', {
			id: lid+'-'+id,
		    html: label + '<input type="hidden" name="'+fid+'[]" value="'+id+'" />'
		});
	var elRemove = new Element('span', {
			title: 'Remove',
			'class': 'remove',
			rel: id,
		    html: '',
		    events: {
		        click: function(){
		        	var lid = (this.getParent().id).replace(/^((?:[a-z0-9_]+\-){2}[a-z0-9_]*).*/, '$1');//id format: mg-moduleid-fieldid-value
		        	$$('#'+lid+' li[rel="'+this.getProperty('rel')+'"]').removeClass('selected');
		        	this.getParent().dispose();
		        	//auto search
				    var autofilter = $(lid).getProperty('data-autofilter');
				    if(autofilter == 1) {
				    	$(lid).getParent('form').fireEvent('submit');
				    }
		        }
		    }
		});
	el.grab(elRemove);
	container.grab(el);
}

function jaMagicSelect(controller, lid) {
	controller = $(controller);
	if(controller.hasClass('opened')) {
		controller.removeClass('opened');
		controller.addClass('closed');
		$(lid).setStyle('display', 'none');
	} else {
		controller.removeClass('closed');
		controller.addClass('opened');
		$(lid).setStyle('display', 'block');
	}
}
function jaMagicSelectClose(controller, lid) {
	controller = $(controller);
	controllerparent = $(lid).getParent().getElement('.select');
	if(controllerparent.hasClass('opened')) {
		controllerparent.removeClass('opened');
		controllerparent.addClass('closed');
	} else {
		controllerparent.removeClass('closed');
		controllerparent.addClass('opened');
	}
	$(lid).setStyle('display', 'none');	
}

function jak2AjaxSubmit(form, K2SitePath) {
	//if Container K2 does not exist, submit form to redirect to K2 Filter result page
    if(jQuery('#k2Container').length) {
        jak2AjaxStart();
        jQuery.ajax({
            type: "POST",
            url: jQuery(form).attr('action'),
            data: jQuery(form).serialize(),
            success: function(text){
                jak2AjaxHandle(text, K2SitePath);
            }
        });
    } else {
        jQuery(form).find('input[name="tmpl"]').val('');
        $(form).submit();
    }
}

function jak2AjaxStart() {
    if(!jQuery('#jak2-loading').length) {
        jQuery('body').append('<div id="jak2-loading">Loading</div>');
    }
    jQuery('#jak2-loading').css({'display': 'block'});
}

function jak2GetUrlSharing(form){
	var params = jQuery(form).serialize();
	params = params.replace('task=search&', 'task=shareurl&');
	params = params.replace('&tmpl=component', '');
	jQuery.ajax({
		type: "POST",
		url: jQuery(form).attr('action'),
		data: params,
		success: function(shareurl){
			if(jQuery(form).find('.jak2shareurl a').length){
				jQuery(form).find('.jak2shareurl a').attr('href', shareurl);
			}
		}
	});
}

function jak2AjaxPagination(container, K2SitePath) {
    var pages = container.find('ul.pagination-list li a');
    if(!pages.length) {
        pages = container.find('.k2Pagination ul li a');
    }
    pages.each(function(){
        jQuery(this).click(function(event) {
            event.preventDefault();
            jak2AjaxStart();
            jQuery.ajax({
                type: "GET",
                url: jQuery(this).attr('href'),
                success: function(text){
                    jak2AjaxHandle(text, K2SitePath);
                }
            });
            return false;
        });
    });
}

function jak2Highlight(container, searchword) {
    if(typeof(jQuery.fn.highlight) == 'function') {
        searchword = searchword.replace(/[<>#\\]/, '');
        //remove excluded words
        searchword = searchword.replace(/\-\s*(intitle\:|intext\:|inmetadata\:|inmedia\:)?\s*("[^"]"|[^\s]+)/g,'');
        //remove special keywords
        searchword = searchword.replace(/(intitle\:|intext\:|inmetadata\:|inmedia\:)/g,'');

        var pattern = /(?:"[^"]+"|[^\s]+)/gi;
        var matches = searchword.match(pattern);
        if(matches) {
            for(i=0; i<matches.length; i++) {
                var word = matches[i].replace(/"/g, '');
                if(word != '' && word != 'OR') {
                    container.highlight(word);
                }
            }
        }
    }
}
function jak2AjaxHandle(text, K2SitePath) {
    var container = jQuery('#k2Container');
    var content = jQuery('<div>' + text + '</div>').find('#k2Container');
    if(content.length) {
        container.html(content.html());
        //paging
        jak2AjaxPagination(container, K2SitePath);

        //rating
        container.find('.itemRatingForm a').click(function(event){
            event.preventDefault();
            var itemID = jQuery(this).attr('rel');
            var log = jQuery('#itemRatingLog' + itemID).empty().addClass('formLogLoading');
            var rating = jQuery(this).html();
            jQuery.ajax({
                url: K2SitePath+"index.php?option=com_k2&view=item&task=vote&format=raw&user_rating=" + rating + "&itemID=" + itemID,
                type: 'get',
                success: function(response){
                    log.removeClass('formLogLoading');
                    log.html(response);
                    jQuery.ajax({
                        url: K2SitePath+"index.php?option=com_k2&view=item&task=getVotesPercentage&format=raw&itemID=" + itemID,
                        type: 'get',
                        success: function(percentage){
                            jQuery('#itemCurrentRating' + itemID).css('width', percentage + "%");
                            setTimeout(function(){
                                jQuery.ajax({
                                    url: K2SitePath+"index.php?option=com_k2&view=item&task=getVotesNum&format=raw&itemID=" + itemID,
                                    type: 'get',
                                    success: function(response){
                                        log.html(response);
                                    }
                                });
                            }, 2000);
                        }
                    });
                }
            });
        });

        //highlight search team in result
        jak2Highlight(container, jQuery('.ja-k2filter input[name="searchword"]').val());
    } else {
        container.html('No Item found!');
    }
    jQuery('#jak2-loading').css({'display': 'none'});
	jQuery('html, body').animate({scrollTop: container.offset().top}, 1000);
}

function jaK2ShowDaterange(obj, range) {
    if(jQuery(obj).val() == 'range') {
        jQuery(range).show();
    } else {
        jQuery(range).hide();
    }
}
