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

function changeOrderingIcon(next){
    
    $$('.orderingIcon').each(function(el){
           
       if(el == next){
           
           el.setStyle('display','block');
           el.addClass('open');
          
           $('ordering').set('html','<option value="'+ next.get('data') +'"></option>');
           
       } else {
           
           el.setStyle('display','none');
           el.removeClass('open');
       }
    });
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
    	lid = this.getPrevious('.ja-magic-select').get('id');
    	jaMagicSelectMakeCount(lid);
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
    
    //orderingIcons
    if($$('.orderingIcon')){
    	changeOrderingIcon($$('.orderingIcon')[0]);
    }
    
    searchFromScratch();
    return;
}

function jaMagicInit(lid, fid) {
	
	
	if( window.isMagicInit == undefined	){ //makes sure it does not get reinitiated by the modal
		//apparently no need to block the modal anymore... dunno why...
		
		$$('#'+lid+' li').each(function(item){
			
			if(item.hasClass('selected')) {
				jaMagicAddElement(lid, fid, item.getChildren('.value')[0].innerHTML, item.getProperty('rel'));
			}
		});
	
		$$('#'+lid+' li.active').each(function(item){
			
			item.addEvent('click', function() {
				
				var id = this.getProperty('rel');
				if(!id) return;
				
			    if(this.hasClass('selected')) {
			    	this.removeClass('selected');
			    	$(lid+'-'+id).dispose();
			    	jaMagicSelectMakeCount(lid);
			    	
			    } else {
			    	this.addClass('selected');
			    	jaMagicAddElement(lid, fid, this.getChildren('.value')[0].innerHTML, id);
			    }
			    
			    var autofilter = $(lid).getProperty('data-autofilter');
			    if(autofilter == 1) {
			    	searchFromScratch();
			    	$(lid).getParent('form').fireEvent('submit');
			    }
			});
		    
		});
		
		//window.isMagicInit = true;
	}
	
}

function jaMagicAddElement(lid, fid, label, id) {
	
	var container = $(lid+'-container');

	var el = new Element('span', {
			id: lid+'-'+id,
		    html: label + '<input type="hidden" name="'+fid+'[]" value="'+id+'" />',
		    
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
		        	jaMagicSelectMakeCount(lid);
				    var autofilter = $(lid).getProperty('data-autofilter');
				    if(autofilter == 1) {
				    	$(lid).getParent('form').fireEvent('submit');
				    	searchFromScratch();
				    }
		        }
		    }
		});
	
	el.grab(elRemove);
	container.grab(el);
	jaMagicSelectMakeCount(lid);
	searchFromScratch();
}

function jaMagicSelectMakeCount(lid){
	
	var count = $(lid).getElements('li.active.selected').length;
	var prev = $(lid).getPrevious('.magicController').getElement('select').getElement('option');
	
	var replacer = count == 0 ? '' : ' (' + count + ')';
	
	var currentHtml = prev.get('html');
	 currentHtml = currentHtml.replace(/\s\(([^)]+)\)/,'');
	
	newHtml = currentHtml + replacer;
	prev.set('html',newHtml);
	return;
}

function jaMagicSelect(controller, lid) {
		
	controller = $(controller); 
	
	if(controller.hasClass('opened')) { //close it
		
		controller.removeClass('opened');
		controller.addClass('closed');
		$(lid).setStyle('display', 'none');
		
		
		
	} else { //open it
		
		document.addEvent('keydown', function(e){
			if(e.key == 'esc'){
			jaMagicSelectClose(controller, lid);}
		});
		
		document.body.addEvent('click',function(e){
		
			var d = e.target;

		    // if this node is not the one we want, move up the dom tree
		    while (d != null && d != $(lid) && d != controller) {
		      d = d.parentNode;
		    }

		    // at this point we have found our containing div or we are out of parent nodes
		    var insideMyDiv = ( d == $(lid) || d == controller) ? true : false;
		    
		    if(!insideMyDiv){
		    	jaMagicSelectClose(controller, lid);
		    }
		});
		
		controller.removeClass('closed');
		controller.addClass('opened');
		$(lid).setStyle('display', 'block');
		
		//close all other magic fields
		$$('div.magicController').each(function(el){
			if(el != controller){	
				el.removeClass('opened');
				el.addClass('closed');
				
			}
		});
		
		$$('ja-magic-select').each(function(el){
			if(el != $(lid)){	
				el.setStyle('display', 'none');	
			}
		});
		
		
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
    if(jQuery('#k2Container').length && window.jak2AjaxSubmitting == false) {
    	
    	window.jak2AjaxSubmitting = true;
        jak2AjaxStart();  
        
        var data = jQuery(form).serialize() + '&isAjax=1';
        
        jQuery.ajax({
            type: "POST",
            url: jQuery(form).attr('action'),
            data: data,
            success: function(text){
                jak2AjaxHandle(text, K2SitePath);
                jak2GetUrlSharing(form);
                window.jak2AjaxSubmitting = false;
            }
        });
        
    } 
    /*else {
        jQuery(form).find('input[name="tmpl"]').val('');
        $(form).submit();
    }*/
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
	params = params + '&isAjax=1';
	
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

/*function jak2AjaxPagination(container, K2SitePath) {
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
}*/

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

    if(content.length && content.find('#itemListPrimary').length) {
    	
    	//update the form search params
        var nextAjaxFormParams = jQuery('<div>' + text + '</div>').find('#K2FormAjaxParams');

        $('K2Start').set('value', nextAjaxFormParams.find('#K2StartParams')[0].get('value'));
        $('K2Total').set('value', nextAjaxFormParams.find('#K2TotalParams')[0].get('value'));
    	
       // console.log(nextAjaxFormParams.find('#K2StartParams')[0].get('value'));
        //console.log(nextAjaxFormParams.find('#K2TotalParams')[0].get('value'));
        
    	container.append(content.html());
    	
    	
    	initModals();
    	setScrollButton();
        /*
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
        jak2Highlight(container, jQuery('.ja-k2filter input[name="searchword"]').val());*/
    	
    } else {
    	
    	window.jak2BlockSearch = true;
        container.html('<div id="noItemFound">No Item found!</div>');
        setScrollButton();
    }
    jQuery('#jak2-loading').css({'display': 'none'});
	//jQuery('html, body').animate({scrollTop: container.offset().top}, 1000);
}

function jaK2ShowDaterange(obj, range) {
    if(jQuery(obj).val() == 'range') {
        jQuery(range).show();
    } else {
        jQuery(range).hide();
    }
}

function setScrollButton() {
	
	if($('K2ScrollButton')){
		
		var limitStart = parseInt($('K2Start').get('value'));
		var total = parseInt($('K2Total').get('value'));
		
		if(limitStart >= total){
			$('K2ScrollButton').hide();
		}else {
			$('K2ScrollButton').show();
		}
	}
    
}

function populateTags(catId){
	
	if($('mg-101-tags_id')){
		
		var c = $('mg-101-tags_id').getElement('ul');
		c.empty();
		$('mg-101-tags_id-container').empty();
		
		if($('tagMessage')){
			$('tagMessage').setStyle('display','none');
		}		
		
		if(!catId){
			
			if($('tagMessage')){
				$('tagMessage').setStyle('display','block');
			}
			
			return;
		}
		
		if(tagsMatrix[catId]){
			
			
			tagsMatrix[catId].each(function(el){
				
				var html = '';
				
				for (var key in el) {
				  if (el.hasOwnProperty(key)) {
					  
					  html += '<li class="active" rel="' +  el[key].id + '"><span class="icon"></span><span class="value">' + el[key].name + '</span></li>';
				  }
				}				
				
				html += '<span class="delimiter"></span>';
				html = Elements.from(html);
				html.inject(c);						
				
			});
		}
		
		jaMagicInit('mg-101-tags_id','tags_id');		
	}
}
