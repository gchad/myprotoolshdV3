/**
 * @version     1.0.7
 * @package     com_hellomaps
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      JoomlaForce Team <support@joomlaforce.com> - http://www.joomlaforce.com
 */

/**
*Initiale map part start
*/
var map, bounds, infoWindow, markerCluster, panorama, sv;



function initializeGmap() {
  var mapTypeIdValue = '';
  switch(maptype_default)
  {
    case 'terrain':
        mapTypeIdValue = google.maps.MapTypeId.TERRAIN;
        break;
    case 'satellite':
        mapTypeIdValue = google.maps.MapTypeId.SATELLITE;
        break;
    case 'hybrid':
        mapTypeIdValue = google.maps.MapTypeId.HYBRID;
        break;
    case 'street':
        mapTypeIdValue = google.maps.MapTypeId.ROADMAP;
        break;
  }  
  var mapTypeIdsEnabled = [];
  
  
  if(maptype_enable_street)
  {
    mapTypeIdsEnabled.push(google.maps.MapTypeId.ROADMAP);
  }
  if(maptype_enable_terrain)
  {    
    mapTypeIdsEnabled.push(google.maps.MapTypeId.TERRAIN);
  }
  if(maptype_enable_satellite)
  {
    mapTypeIdsEnabled.push(google.maps.MapTypeId.SATELLITE);
  }  
  
  var mapOptions = {
    zoom: autocenter_markers ? 0 : (gmap_default_zoom ? gmap_default_zoom : 4),
    center: new google.maps.LatLng(default_latitude, default_longitude),
    zoomControl:false,
    scrollwheel: gmap_scrollwheel,
    disableDefaultUI: true, // a way to quickly hide all controls
    mapTypeControl:true,
    mapTypeControlOptions: {
      //mapTypeIds: [google.maps.MapTypeId.HYBRID,google.maps.MapTypeId.TERRAIN,google.maps.MapTypeId.SATELLITE,google.maps.MapTypeId.ROADMAP]
      mapTypeIds: mapTypeIdsEnabled
    } // here´s the array of controls
    
  

  }; console.log(mapOptions);
 
  var mapTypeOptions = [];
  
  //if(sidebar_position == 'right')
  {
    mapOptions.streetViewControl = false;
    mapOptions.panControl = false;
    //mapOptions.mapTypeControl = false;
    if(sidebar_position == 'right')
    {
        mapOptions.mapTypeControlOptions = {position: google.maps.ControlPosition.TOP_LEFT  ,mapTypeIds: mapTypeIdsEnabled};    
    }    
  }
  
  map = new google.maps.Map(document.getElementById('map-canvas-inner'),
      mapOptions);
  if(mapTypeIdValue != "")
    map.setMapTypeId(mapTypeIdValue);
  
  if(center_onuser_position)
  {
    if (navigator.geolocation) {
    	navigator.geolocation.getCurrentPosition(function(position){
    	var latitude = position.coords.latitude;
    	var longitude = position.coords.longitude;
    	var coords = new google.maps.LatLng(latitude, longitude);
    	/*var marker = new google.maps.Marker({
    				position: coords,
    				map: map,		
    				title: "Your current location!"
    		});*/
    		map.setCenter(coords);
    		map.setZoom(8);
            autocenter_markers = autocenter_markers_value;
            HelloMapsSearch.performSearch();
    	});
    }else {
    	alert("Geolocation API is not supported in your browser.");
    }  
  }    
  infoWindow = new google.maps.InfoWindow({zIndex: 5000,maxWidth: infowindow_width});
  
  google.maps.event.addListener(infoWindow,'closeclick',function(){
       //console.log('I am closing...');
       HelloMapsSearch.currentMarkerKey = "";//reset the currnt Marker
    });
  
    //Geolocation user's center position  
    if(autocenter_markers) 
        bounds = new google.maps.LatLngBounds();
    if(gmap_styles!="")
        map.setOptions({styles: gmap_styles});
    
    if(jQuery('.plugin_tab_li').length > 0)//at least one plugin is enabled
    {
        google.maps.event.addListener(map, 'idle', function(ev){
            // update the coordinates here
            handleGmapBoundChange();
            google.maps.event.clearListeners(map, 'idle');
        }); 
    }
       
    if(enable_gmap_zoom_callback)
    {        
        google.maps.event.addListener(map, 'dragend', function(ev){
            //console.log('Dragging is done...');
            if(!autocenter_markers)
                HelloMapsSearch.performSearch();
        });    
        google.maps.event.addListener(map, 'zoom_changed', function(ev){
            //console.log('Zoome Changed...');
            if(!autocenter_markers)
                HelloMapsSearch.performSearch();
        });    
    }
    if(show_full_screen_button)
    {
        if(sidebar_position == 'left')
            map.controls[google.maps.ControlPosition.TOP_RIGHT].push(new FullScreenControl(map));
        else
            map.controls[google.maps.ControlPosition.TOP_LEFT].push(new FullScreenControl(map));
            
    }
        
    
    // We get the map's default panorama and set up some defaults.
      // Note that we don't yet set it visible.
      
      
      
      
}
function handleGmapBoundChange()
{
    HelloMapsSearch.performSearch();
}    
function GetCurrentMapBounds()
{
    var mBounds = map.getBounds(); 
    var ne = mBounds.getNorthEast();     
    var sw = mBounds.getSouthWest(); 
    var boundObject = new Object();
    boundObject.ne = {lat:ne.lat(),lng:ne.lng()};
    boundObject.sw = {lat:sw.lat(),lng:sw.lng()};
    return boundObject;
}
function codeLatLng() {
  var input = document.getElementById('latlng').value;
  var latlngStr = input.split(',', 2);
  var lat = parseFloat(latlngStr[0]);
  var lng = parseFloat(latlngStr[1]);
  var latlng = new google.maps.LatLng(lat, lng);
  geocoder.geocode({'latLng': latlng}, function(results, status) {
    if (status == google.maps.GeocoderStatus.OK) {
      if (results[1]) {
        map.setZoom(11);
        marker = new google.maps.Marker({
            position: latlng,
            map: map
        });
        infowindow.setContent(results[1].formatted_address);
        infowindow.open(map, marker);
      } else {
        alert('No results found');
      }
    } else {
      alert('Geocoder failed due to: ' + status);
    }
  });
}

function FullScreenControl(mapObj) {
	var controlDiv = document.createElement('div');
	controlDiv.className = "fullScreen";
	controlDiv.index = 1;
	controlDiv.style.padding = '5px';
        

	// Set CSS for the control border.
	var controlUI = document.createElement('div');
	controlUI.style.backgroundColor = '#F5F5F5';
	controlUI.style.borderStyle = 'solid';
	controlUI.style.borderWidth = '1px';
	controlUI.style.borderColor = '#717B87';
	controlUI.style.cursor = 'pointer';
	controlUI.style.textAlign = 'center';
	controlUI.style.boxShadow = '0 1px 4px -1px rgba(0, 0, 0, 0.298)';
    controlUI.style.cursor = 'pointer';
	controlDiv.appendChild(controlUI);

	// Set CSS for the control interior.
	var controlText = document.createElement('div');
	controlText.style.fontFamily = 'Roboto,Arial,sans-serif';
	controlText.style.fontSize = '14px';
	controlText.style.fontWeight = 'normal';
	controlText.style.padding = '1px 8px'; 
	controlText.innerHTML = '<strong>'+fullScreenText+'</strong>';
	controlUI.appendChild(controlText);

	// set print CSS so the control is hidden
	var head = document.getElementsByTagName('head')[0];
	var newStyle = document.createElement('style');
	newStyle.setAttribute('type', 'text/css');
	newStyle.setAttribute('media', 'print');
	newStyle.appendChild(document.createTextNode('.fullScreen { display: none;}'));
	head.appendChild(newStyle);
	
	var fullScreen = false;
	var interval;
	var mapDiv = mapObj.getDiv();
	var divStyle = mapDiv.style;
	if (mapDiv.runtimeStyle)
		divStyle = mapDiv.runtimeStyle;
	var originalPos = divStyle.position;
	var originalWidth = divStyle.width;
	var originalHeight = divStyle.height;
	
	// IE8 hack
	if (originalWidth == "")
		originalWidth = mapDiv.style.width;
	if (originalHeight == "")
		originalHeight = mapDiv.style.height;
	
	var originalTop = divStyle.top;
	var originalLeft = divStyle.left;
	var originalZIndex = divStyle.zIndex;

	var bodyStyle = document.body.style;
	if (document.body.runtimeStyle)
		bodyStyle = document.body.runtimeStyle;
	var originalOverflow = bodyStyle.overflow;
    
    
	
	var goFullScreen = function() {
		var center = mapObj.getCenter();
		mapDiv.style.position = "fixed";
		mapDiv.style.width = "100%";
		mapDiv.style.height = "100%";
		mapDiv.style.top = "0";
		mapDiv.style.left = "0";
		mapDiv.style.zIndex = "100";
		document.body.style.overflow = "hidden";
		controlText.innerHTML = '<strong>'+exitFullScreenText+'</strong>';
		fullScreen = true;
		google.maps.event.trigger(mapObj, 'resize');
		mapObj.setCenter(center);
		// this works around street view causing the map to disappear, which is caused by Google Maps setting the 
		// CSS position back to relative. There is no event triggered when Street View is shown hence the use of setInterval
		interval = setInterval(function() { 
				if (mapDiv.style.position != "fixed") {
					mapDiv.style.position = "fixed";
					google.maps.event.trigger(mapObj, 'resize');
				}
			}, 100);
            
        if(sidebar_enable)
            HelloMapsSearch.OnFullScreenOpen();//added by sam 
	};
	
	var exitFullScreen = function() {
		var center = mapObj.getCenter();
		if (originalPos == "")
			mapDiv.style.position = "relative";
		else
			mapDiv.style.position = originalPos;
		mapDiv.style.width = originalWidth;
		mapDiv.style.height = originalHeight;
		mapDiv.style.top = originalTop;
		mapDiv.style.left = originalLeft;
		mapDiv.style.zIndex = originalZIndex;
		document.body.style.overflow = originalOverflow;
		controlText.innerHTML = '<strong>'+fullScreenText+'</strong>';
		fullScreen = false;
		google.maps.event.trigger(mapObj, 'resize');
		mapObj.setCenter(center);
		clearInterval(interval);
        
        if(sidebar_enable)
            HelloMapsSearch.OnFullScreenClose();//added by sam 
	}
	
	// Setup the click event listener
	google.maps.event.addDomListener(controlUI, 'click', function() {
		if (!fullScreen) {
			goFullScreen();
		}
		else {
			exitFullScreen();
		}
	});	
	return controlDiv;
}

google.maps.event.addDomListener(window, 'load', initializeGmap);
/**
*Initialize map part end
*/
var HelloMapsPopulateFilterCallabck = new Object();
var HelloMapsSearch = new Object();
HelloMapsSearch.searchParams = "";
HelloMapsSearch.markers = [];
HelloMapsSearch.searchProgress = null;
HelloMapsSearch.currentMarkerKey = "";
HelloMapsSearch.oldOpenedMarkerExist = false;
HelloMapsSearch.plugin_result_containers = [];
HelloMapsSearch.totalSearchResult = 0;
HelloMapsSearch.sidebarOpened = 1;

hQuery(document).ready(function(){
	hQuery('#apply_all_filters').change(function(){
		ApplyAllPluginFilters();
	});
	hQuery('#hellomapSearchButton').click(function(){
		HelloMapsSearch.performSearch();
	});
    
    if(hQuery('.settings_checkbox').length > 0)
    {
        hQuery('.settings_checkbox').change(function(){
            HelloMapsSearch.SynchornizeSettingsCheckboxes('normal');//keep same plugin settings everywhere
            HelloMapsSearch.AdjustPluginTabs();
            HelloMapsSearch.performSearch();
        });    
    }
    
    if(hQuery('.settings_checkbox_collapsed').length > 0)
    {
        hQuery('.settings_checkbox_collapsed').change(function(){
            HelloMapsSearch.SynchornizeSettingsCheckboxes('collapsed');//keep same plugin settings everywhere
            HelloMapsSearch.AdjustPluginTabs();
            HelloMapsSearch.performSearch();
        });
    }
    if(contents_enable)
    {
        hQuery('a','#plugin_tabs_ul li:first').click(function(){
           //bootstrap takes a little time to show tabs, if there is no settimeout, jquery will not return the item height 
           setTimeout(function(){
                       HelloMapsSearch.AdjustGlobalResultTab(); 
                  },100);
        });    
    }
    
});
function ApplyAllPluginFilters()
{
	if(hQuery('#apply_all_filters').is(':checked'))
	{
		hQuery('.filter_checkbox').attr('checked','checked');
		hQuery('.filterBlock').show();
		hQuery('#hellomapSearchButton').show();
	}
	else
	{
		hQuery('.filter_checkbox').removeAttr('checked');
		hQuery('.filterBlock').hide();
		hQuery('#hellomapSearchButton').hide();
	}
    HelloMapsSearch.UpdateFiltersAreaScrollbar();
}
/**
 * [CheckSearchOptions description]
 * if no filter block is visible, hide the submit button
 */
function CheckSearchOptions()
{
	if(hQuery('.filter_checkbox:checked').length > 0)
		hQuery('#hellomapSearchButton').show();
	else
		hQuery('#hellomapSearchButton').hide();
}

function StringUCFirst(input_str)
{
	input_str = input_str.toLowerCase();
	return input_str.charAt(0).toUpperCase() + input_str.slice(1);
}

function formatNumber( number, decimals, dec_point, thousands_sep ){
	
	// Set the default values here, instead so we can use them in the replace below.
	thousands_sep	= (typeof thousands_sep === 'undefined') ? ',' : thousands_sep;
	dec_point		= (typeof dec_point === 'undefined') ? '.' : dec_point;
	decimals		= !isFinite(+decimals) ? 0 : Math.abs(decimals);
	
	// Work out the unicode representation for the decimal place.	
	var u_dec = ('\\u'+('0000'+(dec_point.charCodeAt(0).toString(16))).slice(-4));
	
	// Fix the number, so that it's an actual number.
	number = (number + '')
		.replace(new RegExp(u_dec,'g'),'.')
		.replace(new RegExp('[^0-9+\-Ee.]','g'),'');
	
	var n = !isFinite(+number) ? 0 : +number,
	    s = '',
	    toFixedFix = function (n, decimals) {
	        var k = Math.pow(10, decimals);
	        return '' + Math.round(n * k) / k;
	    };
	
	// Fix for IE parseFloat(0.55).toFixed(0) = 0;
	s = (decimals ? toFixedFix(n, decimals) : '' + Math.round(n)).split('.');
	if (s[0].length > 3) {
	    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, thousands_sep);
	}
	if ((s[1] || '').length < decimals) {
	    s[1] = s[1] || '';
	    s[1] += new Array(decimals - s[1].length + 1).join('0');
	}
	return s.join(dec_point);
}
/**
 * [BuildFlashMessage description]
 * Build bootstrap flash message for error, waiting, completed message
 * @param {[type]} messageType [error,success,info]
 * @param {[type]} message     [description]
 */
function GetFlashMessage(messageType, message)
{
	var messageHTML = '<div class="alert alert-'+messageType+'"><button data-dismiss="alert" class="close" type="button">×</button>'+message+'</div>';
	return messageHTML;
}

/**
*When the All tab will be open again
* we will need to adjust the all result scroller based on notice
*/
HelloMapsSearch.AdjustGlobalResultTab = function(){
    var currentViewStyle = hQuery('.toggle_list_view_map_view').attr('data-view');
    if(currentViewStyle == 'map_view')
        return;
    if(contents_enable && show_global_notice && (notice_position == sidebar_position) && hQuery('#notice_box_holder_global').length > 0)
    {        
        if(hQuery('.global_notice_open_button').is(':visible'))//notice is closed
        {
            var sidebarNewHeight = sidebar_height-120;          
            hQuery('#map-canvas-sidebar').height(sidebarNewHeight);
            hQuery('#global_result_list').height(sidebarNewHeight - 70);                      
        }
        else//notice is opened
        {
            var sidebarNewHeight = sidebar_height-notice_offset;          
            hQuery('#global_result_list').height(sidebarNewHeight-70);  
            hQuery('#map-canvas-sidebar').height(sidebarNewHeight);  
        }                  
        hQuery("#global_result_list").mCustomScrollbar("destroy"); 
        hQuery("#global_result_list").mCustomScrollbar({
			scrollButtons:{
				enable:true
			}
		});   
    }
    else //give full height
    {
        if(contents_enable && pluginNoticeExist)
        {
            if(hQuery('.notice_box_holder_plugin').length > 0) //other plugins noticebox should be hidden
            {
                hQuery('.notice_box_holder_plugin').hide();
            }
        }
        hQuery('#map-canvas-sidebar').height(sidebar_height-40);
        if(contents_enable)
        {
            hQuery('#global_result_list').height(hQuery('#map-canvas-sidebar').height()-70);  
            hQuery("#global_result_list").mCustomScrollbar("destroy"); 
            hQuery("#global_result_list").mCustomScrollbar({
    			scrollButtons:{
    				enable:true
    			}
    		});      
        }
    }
}
/**
 * [initSearchParams description]
 * will call all plugins search function in javascript
 * if no parameters found, then invalid search
 * @return {[type]} [description]
 */
HelloMapsSearch.initSearchParams = function(){
	HelloMapsSearch.searchParams = "";
    var pluginSelector = ".plugin_tab_li[data-status='enabled']";
    if(!sidebar_enable)
    {
        pluginSelector = '.plugin_tab_li';
    }
	hQuery(pluginSelector).each(function(){
		var filter_id = hQuery(this).attr('data-filter_id');		
		filterName = StringUCFirst(filter_id);
		eval('HelloMapsSearch.init'+filterName+'SearchParams()');//call the plugin function
	});
}
HelloMapsSearch.performSearch = function(){
	
	this.initSearchParams();	
    
	if (HelloMapsSearch.searchParams.length == 0) //validation failed, no plugin was able to validate
	{
		//show warning message or do nothing
		var flashMessage = GetFlashMessage('error', COM_HELLOMAP_SEARCH_NO_PARAMETER_SELECTED_MESSAGE);
		hQuery('#hellomap-search-flash-message').html(flashMessage).show();
        
        HelloMapsSearch.oldOpenedMarkerExist = false;
        HelloMapsSearch.clearAllMarkers();  
        HelloMapsSearch.clearPreviousSearchResults();  
        HelloMapsSearch.totalSearchResult = 0;
        if((jQuery('#markerStatistics').length > 0) && (counter_result_type == "byzoom"))//byzoom=byplugin
        {
            jQuery('#markerStatistics').html('');
        }
	}
	else
	{		

        var boundParams = GetCurrentMapBounds();
        
        if(HelloMapsSearch.searchProgress != null)
            HelloMapsSearch.searchProgress.abort();
		HelloMapsSearch.searchProgress = hQuery.ajax({
			url:'index.php?option=com_hellomaps&task=marker_search',
			type:'POST',
			data:HelloMapsSearch.searchParams+'&ne[lat]='+boundParams.ne.lat+'&ne[lng]='+boundParams.ne.lng+'&sw[lat]='+boundParams.sw.lat+'&sw[lng]='+boundParams.sw.lng,
			dataType:'JSON',
			beforeSend:function(){
				var flashMessage = GetFlashMessage('warning', COM_HELLOMAP_SEARCH_IN_PROGRESS);
				hQuery('#hellomap-search-flash-message').html(flashMessage).show();                
			},
			success:function(jsonResponse){
				var flashMessage = GetFlashMessage('success', COM_HELLOMAP_SEARCH_COMPLETED_LABEL);
				hQuery('#hellomap-search-flash-message').html(flashMessage).show().fadeOut(1000);
                if(results_enable && (counter_result_type == "byzoom"))//byzoom=byplugins
                    hQuery('#markerStatistics').html('').hide();
                HelloMapsSearch.processSearchResult(jsonResponse);
			}
		});
	}
}

HelloMapsSearch.processSearchResult = function(searchResult){ 
    HelloMapsSearch.oldOpenedMarkerExist = false;
    HelloMapsSearch.lastActiveMarker = null;
    HelloMapsSearch.clearAllMarkers();  
    HelloMapsSearch.clearPreviousSearchResults();  
    HelloMapsSearch.totalSearchResult = 0;
    hQuery.each(searchResult, function(filterName,result) {
        filterName = StringUCFirst(filterName);		
		eval('HelloMapsSearch.process'+filterName+'SearchResult(result);');//call the plugin function
    });
    //console.log(HelloMapsSearch.currentMarkerKey);
    if(!HelloMapsSearch.oldOpenedMarkerExist)
    {
        HelloMapsSearch.currentMarkerKey = "";
    }
    else//open the info window
    {        
        google.maps.event.trigger(HelloMapsSearch.lastActiveMarker, 'click');
    }
    if(results_enable)
    {
        if(counter_result_type == 'byzoom')
        {            
            hQuery('#markerStatistics').append('<div class="totalValue">'+totalText+': '+formatNumber(HelloMapsSearch.markers.length)+'</div>');            
        }
            
        hQuery('#markerStatistics').show();
    }
        
    if(autocenter_markers)
    {
        if(HelloMapsSearch.totalSearchResult > 0)
            map.fitBounds(bounds);//adjust zoom level to show maximum markers
        autocenter_markers = false;//only auto center at first time        
    }
        
    if(gmap_cluster_enabled)
    {
        //console.log('total marker = '+HelloMapsSearch.markers.length);
        if(cluster_style_options != null)
            markerCluster = new MarkerClusterer(map, HelloMapsSearch.markers,{styles:cluster_style_options});
        else
            markerCluster = new MarkerClusterer(map, HelloMapsSearch.markers);
    }     
    if(sidebar_enable && contents_enable && hQuery("#global_result_list").length > 0)
    {
        hQuery("#global_result_list").mCustomScrollbar("destroy");
        hQuery("#global_result_list").mCustomScrollbar({
			scrollButtons:{
				enable:true
			}
		});   
    }
    if(sidebar_enable && (HelloMapsSearch.totalSearchResult == 0))//no result found
    {
        hQuery("#global_result_list").mCustomScrollbar("destroy");
        hQuery("#global_result_list").html('<div class="alert alert-warning alert-dismissable">'+
                                                '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>'+
                                                '<strong>No Result Found.</strong>'+
                                            '</div>');
    }
}

HelloMapsSearch.clearAllMarkers = function(){
    
    if(gmap_cluster_enabled && markerCluster)
    {
        markerCluster.clearMarkers();
        HelloMapsSearch.markers = [];//reset the marker array
    }
    else
    {
        if(HelloMapsSearch.markers.length > 0)
        {
            for (var i = 0; i < HelloMapsSearch.markers.length; i++) 
            {
                    HelloMapsSearch.markers[i].setMap(null);
            }
            HelloMapsSearch.markers = [];//reset the marker array
        }
    }
    
}
HelloMapsSearch.clearPreviousSearchResults = function(){
    hQuery('#global_result_list').html('');
    for(var i=0;i<HelloMapsSearch.plugin_result_containers.length;i++)
    {
        hQuery(HelloMapsSearch.plugin_result_containers[i]).html('');
    }
}
HelloMapsSearch.openStreetView = function(marker_lat,marker_lng){
	var streetViewLocation = new google.maps.LatLng(marker_lat, marker_lng);
    var client = new google.maps.StreetViewService();
    client.getPanoramaByLocation(streetViewLocation, 50, function(result,status) {
    if (status == google.maps.StreetViewStatus.OK) {
	 
	 var panorama = map.getStreetView();
     var toggle = panorama.getVisible();
		
        panorama.setOptions({
		position: streetViewLocation,
		pov: {
                heading: 34,
                pitch: 10,
                zoom: 1
            },
		imageDateControl: true,
		linksControl: false
		});
	
    panorama.setVisible(true);
    } else {
	
	var panorama = map.getStreetView();
    var toggle = panorama.getVisible();
	
	panorama.setVisible(false);
	alert ("street view not available")
	}
});        
}
HelloMapsSearch.toggleStreetView = function(){
    var toggle = panorama.getVisible();
    if (toggle == false) 
    {
        panorama.setVisible(true);
    } 
    else 
    {
        panorama.setVisible(false);
    }
}
HelloMapsSearch.gmap_zoom_in = function(){
    var currentZoomLevel = map.getZoom();
    if(currentZoomLevel != 21){
        map.setZoom(currentZoomLevel + 1);}
}
HelloMapsSearch.gmap_zoom_out = function(){
    var currentZoomLevel = map.getZoom();
    if(currentZoomLevel > 0){
        map.setZoom(currentZoomLevel - 1);}
}

HelloMapsSearch.OpenCurrentStreetView = function(){
    var mapCenter = map.getCenter();
    HelloMapsSearch.openStreetView(mapCenter.lat(),mapCenter.lng());
}
HelloMapsSearch.ToggleMapViewListView = function(){
    var currentViewStyle = hQuery('.toggle_list_view_map_view').attr('data-view');
    
    if(currentViewStyle == 'list_view')//hide the list view and show the map behind it
    {
        hQuery('img','.toggle_list_view_map_view').attr('src',HELLOMAPS_FRONT_URL+'assets/images/toolsbar_icon_5_img.jpg');        
        hQuery('.toggle_list_view_map_view').attr('data-view','map_view');
        hQuery('.sidebarItems').slideUp('slow',function(){
            hQuery(this).height(0);
            hQuery('#map-canvas-sidebar').height(hQuery('.toolsbar-area').height());
        });
    }
    else
    {
        hQuery('img','.toggle_list_view_map_view').attr('src',HELLOMAPS_FRONT_URL+'assets/images/icon_grid_view_img.jpg');
        hQuery('.toggle_list_view_map_view').attr('data-view','list_view');
        //sam work here
        
            
        hQuery('.sidebarItems').show();        
        hQuery( ".sidebarItems" ).animate({
            height: "100%"
        }, 1000, function() {
        // Animation complete.
            //check which tab is open and it have notice, then adjust it
            
            if(!hQuery('li.active','ul#plugin_tabs_ul').hasClass('plugin_tab_li'))
            {
                if(contents_enable && show_global_notice && (notice_position == sidebar_position) && hQuery('#notice_box_holder_global').length > 0)
                {
                    if(hQuery('.global_notice_open_button').is(':visible'))//notice is closed
                    {
                        var sidebarNewHeight = sidebar_height-120;          
                        hQuery('#map-canvas-sidebar').height(sidebarNewHeight);
                        hQuery('#global_result_list').height(sidebarNewHeight - 70);                      
                    }
                    else//notice is opened
                    {
                        var sidebarNewHeight = sidebar_height-notice_offset;          
                        hQuery('#global_result_list').height(sidebarNewHeight-70);  
                        hQuery('#map-canvas-sidebar').height(sidebarNewHeight);  
                    }                  
                    hQuery("#global_result_list").mCustomScrollbar("destroy"); 
                    hQuery("#global_result_list").mCustomScrollbar({
            			scrollButtons:{
            				enable:true
            			}
            		});   
                }    
                else //give full height
                {
                    hQuery('#map-canvas-sidebar').height(sidebar_height);
                }
            }        
            else //trigger plugin
            {
                var filter_id = hQuery('li.active','#plugin_tabs_ul').attr('data-filter_id');		
            		filterName = StringUCFirst(filter_id);
            		eval('HelloMapsSearch.Adjust'+filterName+'TabLayout()');//call the plugin function
                //hQuery('#map-canvas-sidebar').height(sidebar_height);
            }
        });
    }
}
HelloMapsSearch.PointUserPosition = function(){
    if (navigator.geolocation) {
    	navigator.geolocation.getCurrentPosition(function(position){
    	var latitude = position.coords.latitude;
    	var longitude = position.coords.longitude;
        
    	var coords = new google.maps.LatLng(latitude, longitude);
    	
        var panorama = map.getStreetView();
        if(panorama.getVisible())
            panorama.setVisible(false);
        
    		map.setCenter(coords);
    		map.setZoom(8);
            autocenter_markers = false;
            HelloMapsSearch.performSearch();
    	});
    }else {
    	alert("Geolocation API is not supported in your browser.");
    }  
}

HelloMapsSearch.CloseSidebar = function(){
    var shift_margin_left = (hQuery('#map-canvas-sidebar').width()+11);   
    
    if(sidebar_position == 'right')
    {
        hQuery( "#map-canvas-sidebar" ).animate({
            'margin-right': "-"+shift_margin_left+'px'
        }, 1000, function() {
            // Animation complete.
            hQuery('#closed_sidebar_toolbar').show();
            HelloMapsSearch.sidebarOpened = 0;
        });
    }   
    else
    {
        hQuery( "#map-canvas-sidebar" ).animate({
            'margin-left': "-"+shift_margin_left+'px'
        }, 1000, function() {
            // Animation complete.
            hQuery('#closed_sidebar_toolbar').show();
            HelloMapsSearch.sidebarOpened = 0;
        });
    } 
}
HelloMapsSearch.CloseSidebarWithoutAnimation = function(){
    var shift_margin_left = (hQuery('#map-canvas-sidebar').width()+11);   
    
    if(sidebar_position == 'right')
    {        
        hQuery( "#map-canvas-sidebar" ).css({
            'margin-right': "-"+shift_margin_left+'px'
        });
        hQuery('#closed_sidebar_toolbar').show();
        HelloMapsSearch.sidebarOpened = 0;
    }   
    else
    {
        hQuery( "#map-canvas-sidebar" ).css({
            'margin-left': "-"+shift_margin_left+'px'
        });
        hQuery('#closed_sidebar_toolbar').show();
        HelloMapsSearch.sidebarOpened = 0;
    }
}
HelloMapsSearch.OpenSidebar = function(){
    var shift_margin_left = 0;
    if(sidebar_position == 'right')
    {
        hQuery( "#map-canvas-sidebar" ).animate({
            'margin-right': "-"+shift_margin_left+'px'
        }, 1000, function() {
            // Animation complete.
            hQuery('#closed_sidebar_toolbar').hide();
            HelloMapsSearch.sidebarOpened = 1;
        });   
    }
    else
    {
        hQuery( "#map-canvas-sidebar" ).animate({
            'margin-left': "-"+shift_margin_left+'px'
        }, 1000, function() {
            // Animation complete.
            hQuery('#closed_sidebar_toolbar').hide();
            HelloMapsSearch.sidebarOpened = 1;
        });    
    }    
}

HelloMapsSearch.UpdateFiltersAreaScrollbar = function(){
    if(hQuery(".search_filters_area_scroll").length > 0)
        hQuery(".search_filters_area_scroll").mCustomScrollbar("update");   
}

HelloMapsSearch.AdjustPluginTabs = function (){
    var currentTabLIId = hQuery('li.active','#plugin_tabs_ul').attr('id');
    var closeCurrentTab = false;
    hQuery('.settings_checkbox').each(function(){
       var filter_id = hQuery(this).attr('data-filter_id');		
       if(!hQuery(this).is(':checked'))
       {
           hQuery('#plugin_tab_li_'+filter_id).hide();
           hQuery('#title_tab_'+filter_id).removeClass('active'); 
           if( ('plugin_tab_li_'+filter_id) == currentTabLIId)
           {
                closeCurrentTab = true;
           }
           hQuery('#plugin_tab_li_'+filter_id).attr('data-status',"disabled");
       } 
       else
       {
           var tab_show_in_sidebar =  hQuery('#plugin_tab_li_'+filter_id).attr('data-show_in_sidebar');
           
           if(tab_show_in_sidebar == '1')
               hQuery('#plugin_tab_li_'+filter_id).show();
           if( !(('plugin_tab_li_'+filter_id) == currentTabLIId) )
           {
                hQuery('#plugin_tab_li_'+filter_id).removeClass('active');
           }
           hQuery('#plugin_tab_li_'+filter_id).attr('data-status',"enabled");
           //hQuery('#title_tab_'+filter_id).show();  
       }
    });
    if(closeCurrentTab)
    {
        var filter_id = hQuery('li.active','#plugin_tabs_ul').attr('data-filter_id');		
        hQuery('li.active','#plugin_tabs_ul').hide();
        hQuery('#title_tab_'+filter_id).removeClass('active'); 
        hQuery('#plugin_tabs_ul li#global_result_tab').addClass('active');  
        hQuery('#title_tab_all').addClass('active');      
    }
    
}

HelloMapsSearch.OnFullScreenOpen = function(){
    //hide the notice box first
    if(hQuery('.notice_box_holder').length > 0)
    {
        hQuery('.notice_box_holder').css('z-index',-1);
    }
    
    //save the sidebar height, which will be used when user exits  the full screen
    //save show_global_notice, pluginNoticeExist too
    hQuery('#map-canvas-sidebar').data('sidebar_height',sidebar_height);
    sidebar_height = hQuery(window).height()-25;
    hQuery('#map-canvas-sidebar').data('show_global_notice',show_global_notice);
    hQuery('#map-canvas-sidebar').data('pluginNoticeExist',pluginNoticeExist);
    show_global_notice = pluginNoticeExist = false;
    
    if(hQuery('.notice_box_holder').length > 0)
        hQuery('.notice_box_holder').hide();
    
    hQuery('a','ul#plugin_tabs_ul li.active').click();
    
    if(hQuery('#map-canvas-sidebar').length > 0)
    {
        sidebarStyle = hQuery('#map-canvas-sidebar').attr('style');
        if(sidebar_position == 'left')
        {
            var sidebarTop = hQuery('#map-canvas-sidebar').offset().top-20;
            var sidebarLeft = hQuery('#map-canvas-sidebar').offset().left;  
            if(HelloMapsSearch.sidebarOpened == 0)
            {
                sidebarLeft = (sidebarLeft + hQuery('#map-canvas-sidebar').width() + 10);
            }
            hQuery('#map-canvas-sidebar').parent().css('overflow',''); 
            hQuery('#map-canvas-sidebar').css('position','fixed');            
            hQuery('#map-canvas-sidebar').css('left','0px');
            hQuery('#map-canvas-sidebar').css('top','0px');
            hQuery('#map-canvas-sidebar').css('z-index','1000');
        }
        else
        {
            var sidebarTop = hQuery('#map-canvas-sidebar').offset().top;
            var sidebarRight = hQuery('#map-canvas-sidebar').width()-45;  
            
            hQuery('#map-canvas-sidebar').parent().css('overflow','');
            hQuery('#map-canvas-sidebar').css('position','fixed'); 
            hQuery('#map-canvas-sidebar').css('right','0px');
            hQuery('#map-canvas-sidebar').css('top','0px');
            hQuery('#map-canvas-sidebar').css('z-index','1000');
        }
        
        if(HelloMapsSearch.sidebarOpened == 0)
        {
            setTimeout(function(){
                HelloMapsSearch.CloseSidebarWithoutAnimation();
            },100);    
        }
    }
    if(hQuery('.notice_box_holder').length > 0)
        hQuery('.notice_box_holder').hide();
}

HelloMapsSearch.OnFullScreenClose = function(){    
    if(hQuery('.notice_box_holder').length > 0)
    {
        hQuery('.notice_box_holder').css('z-index',105);
    }
    sidebar_height = hQuery('#map-canvas-sidebar').data('sidebar_height');
    show_global_notice = hQuery('#map-canvas-sidebar').data('show_global_notice');
    pluginNoticeExist = hQuery('#map-canvas-sidebar').data('pluginNoticeExist');
    
    if(hQuery('.notice_box_holder').length > 0)
        hQuery('.notice_box_holder').show();
    if(hQuery('#map-canvas-sidebar').length > 0)
    {
        hQuery('#map-canvas-sidebar').parent().css('overflow','hidden'); 
        hQuery('#map-canvas-sidebar').attr('style',sidebarStyle);
        if(HelloMapsSearch.sidebarOpened == 0)
        {
            //close it again for adjusting margin
            //HelloMapsSearch.CloseSidebar();
            if(sidebar_position == 'left')
            {
                var shift_margin_left = (hQuery('#map-canvas-sidebar').width()+11);    
                hQuery( "#map-canvas-sidebar" ).css({
                    'margin-left': "-"+shift_margin_left+'px'
                });
                // Animation complete.
               hQuery('#closed_sidebar_toolbar').show();    
            }
            else
            {
                var shift_margin_left = (hQuery('#map-canvas-sidebar').width()+11);    
                hQuery( "#map-canvas-sidebar" ).css({
                    'margin-right': "-"+shift_margin_left+'px'
                });
                // Animation complete.
               hQuery('#closed_sidebar_toolbar').show();    
            }            
        }
        else
        {
            //HelloMapsSearch.OpenSidebar();
            if(sidebar_position == 'left')
            {
                var shift_margin_left = 0;    
                hQuery( "#map-canvas-sidebar" ).css({
                    'margin-left': "-"+shift_margin_left+'px'
                });
                hQuery('#closed_sidebar_toolbar').hide();    
            }
            else
            {
                var shift_margin_left = 0;    
                hQuery( "#map-canvas-sidebar" ).css({
                    'margin-right': "-"+shift_margin_left+'px'
                });
                hQuery('#closed_sidebar_toolbar').hide();    
            }
            var currentViewStyle = hQuery('.toggle_list_view_map_view').attr('data-view');            
            if(contents_enable && (notice_position == sidebar_position) && currentViewStyle == 'list_view')
            {
                hQuery('li.active','#plugin_tabs_ul').find('a').trigger('click');
            }
        }
    }
    
}

HelloMapsSearch.CloseGlobalNotice = function(){
    hQuery('.global_notice_close_button').hide();
    hQuery('.global_notice_open_button').show();
    hQuery("#notice_box_holder_plugin .notices_area_scroll").mCustomScrollbar('destroy');    
    var noticeContentHeight = (hQuery('.global_notice_content').height()+30);
    var currentViewStyle = hQuery('.toggle_list_view_map_view').attr('data-view');
    
    hQuery('li.plugin_tab_li').removeClass('visited');
    
    hQuery( "#notice_box_holder_global" ).animate({
        'height': '0px'
    }, 500, function() {
        hQuery(this).remove();
        show_global_notice = 0;
        //sidebar_height = sidebar_height - 42;
        if(contents_enable && (notice_position == sidebar_position) && HelloMapsSearch.sidebarOpened && currentViewStyle == 'list_view')
        {
            if(hQuery('li.active','#plugin_tabs_ul').hasClass('all_tab'))
            {
                //adjust the sidebar                  
                var sidebarNewHeight = sidebar_height;          
                hQuery('#map-canvas-sidebar').height(sidebarNewHeight);          
                HelloMapsSearch.AdjustGlobalResultTab(); 
            }
            else
            {
                var sidebarNewHeight = sidebar_height;          
                hQuery('#map-canvas-sidebar').height(sidebarNewHeight);
                var filter_id = hQuery('li.active','#plugin_tabs_ul').attr('data-filter_id');		
        		filterName = StringUCFirst(filter_id);                
        		eval('HelloMapsSearch.Adjust'+filterName+'TabLayout()');//call the plugin function  
            }
        }
        else if((notice_position == sidebar_position) && HelloMapsSearch.sidebarOpened && currentViewStyle == 'list_view')
        {
            hQuery('#map-canvas-sidebar').height(sidebar_height);
        }       
    });
}

HelloMapsSearch.SynchornizeSettingsCheckboxes = function(source){
    if(source == 'normal')//sidebar is not collapes
    {
        jQuery('.settings_checkbox').each(function(i){
            if(jQuery(this).is(':checked'))
            {
                if(!jQuery('.settings_checkbox_collapsed').eq(i).is(':checked'))
                    jQuery('.settings_checkbox_collapsed').eq(i).attr('checked','checked');
            }
            else
            {
                if(jQuery('.settings_checkbox_collapsed').eq(i).is(':checked'))
                    jQuery('.settings_checkbox_collapsed').eq(i).removeAttr('checked');
            }
        });
    }
    else
    {
        jQuery('.settings_checkbox_collapsed').each(function(i){
            if(jQuery(this).is(':checked'))
            {
                if(!jQuery('.settings_checkbox').eq(i).is(':checked'))
                    jQuery('.settings_checkbox').eq(i).attr('checked','checked');
            }
            else
            {
                if(jQuery('.settings_checkbox').eq(i).is(':checked'))
                    jQuery('.settings_checkbox').eq(i).removeAttr('checked');
            }
        });
    }
}



//on dom ready
hQuery(document).ready(function(){
   if(hQuery('.do_zoom_in').length > 0)
   {
       hQuery('.do_zoom_in').click(function(){
         HelloMapsSearch.gmap_zoom_in();
       });   
   } 
   if(hQuery('.do_zoom_out').length > 0)
   {
       hQuery('.do_zoom_out').click(function(){
         HelloMapsSearch.gmap_zoom_out();
       });   
   }
   if(hQuery('.open_street_view_button').length > 0)
   {
       hQuery('.open_street_view_button').click(function(){
         HelloMapsSearch.OpenCurrentStreetView();
       }); 
   }
   if(hQuery('.point_user_position').length > 0)
   {
       hQuery('.point_user_position').click(function(){
         HelloMapsSearch.PointUserPosition();
       });  
   }
   if(hQuery('.toggle_list_view_map_view').length > 0)
   {
       hQuery('.toggle_list_view_map_view').click(function(){
         HelloMapsSearch.ToggleMapViewListView();
       }); 
   }
   if(hQuery('.close_sidebar').length > 0)
   {
        hQuery('.close_sidebar').click(function(){
         HelloMapsSearch.CloseSidebar();
       });
   }
   if(hQuery('.open_sidebar').length > 0)
   {
       hQuery('.open_sidebar').click(function(){
         HelloMapsSearch.OpenSidebar();
       }); 
   }
   if(sidebar_enable && contents_enable && (hQuery('#global_result_list').length > 0))
   {
        hQuery("#global_result_list").mCustomScrollbar({
			scrollButtons:{
				enable:true
			}
		});        
   }
   if(hQuery('#notice_box_holder_global .notices_area_scroll').length > 0)
   {
        hQuery("#notice_box_holder_global .notices_area_scroll").mCustomScrollbar({
			scrollButtons:{
				enable:true
			}
		});
   }
   if(!sidebar_load_open)
   {
        HelloMapsSearch.sidebarOpened = 0;
   }
   //global notice show hide
   if(hQuery('.global_notice_close_button').length > 0)
   {
        hQuery('.global_notice_close_button').click(function(){
            HelloMapsSearch.CloseGlobalNotice();
        });
   }
   //global notice show hide
   
});