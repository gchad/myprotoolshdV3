
function jaMagicInit(lid, fid) {
		
	
		$$('#'+lid+' li').each(function(item){
			
			if(item.hasClass('selected')) {
				jaMagicAddElement(lid, fid, item.getChildren('.value')[0].innerHTML, item.getProperty('rel'));
			}
		});
	
		
		$$('#'+lid+' li.active').each(function(item){
			
			item.addEvent('click', function() {
				
				console.log('click');
				var id = this.getProperty('rel');
				if(!id) return;
				
			    if(this.hasClass('selected')) {
			    	this.removeClass('selected');
			    	$(lid+'-'+id).dispose();
			    } else {
			    	this.addClass('selected');
			    	jaMagicAddElement(lid, fid, this.getChildren('.value')[0].innerHTML, id);
			    }   
			});
		    
		});	
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
		        	var lid = (this.getParent().id).replace(/^((?:[a-z0-9_]+\-){2}[a-z0-9_]*).*/, '$1');
		        	
		        	//id format: mg-moduleid-fieldid-value
		        	
		        	$$('#'+lid+' li[rel="'+this.getProperty('rel')+'"]').removeClass('selected');
		        	$$('li[rel="'+this.getProperty('rel')+'"]').removeClass('selected');
		        	this.getParent().dispose();
		        			    
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

function populateTags(catId){
	
	if($('mg-101-selectedTags')){
		
		var c = $('mg-101-selectedTags').getElement('ul');
		c.empty();
		
		$('mg-101-selectedTags-container').empty();
		
		if($('tagMessage')){
			$('tagMessage').setStyle('display','none');
		}		
		
		if(!catId){
			
			if($('tagMessage')){
				$('tagMessage').setStyle('display','block');
			}
			
			return;
		}
		
		console.log(tagsMatrix[catId]);
		
		if(tagsMatrix[catId]){
			
			
			tagsMatrix[catId].each(function(el){
				
				var html = '';
				
				for (var key in el) {
				  if (el.hasOwnProperty(key)) {
					  
					  html += '<li class="active '+ el[key].classe + '" rel="' +  el[key].id + '"><span class="icon"></span><span class="value">' + el[key].name + '</span></li>';
				  }
				}				
				
				html += '<span class="delimiter"></span>';
				html = Elements.from(html);
				html.inject(c);						
				
			});
		}
		
		jaMagicInit('mg-101-selectedTags','selectedTags');		
	}
}