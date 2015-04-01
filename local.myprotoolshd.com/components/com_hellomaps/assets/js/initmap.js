/**
 * @version     1.0.7
 * @package     com_hellomaps
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      JoomlaForce Team <support@joomlaforce.com> - http://www.joomlaforce.com
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
  }  
  var mapOptions = {
    zoom: 4,
    center: new google.maps.LatLng(default_latitude, default_longitude),
    scrollwheel: gmap_scrollwheel
  };
  map = new google.maps.Map(document.getElementById('map-canvas'),
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
    	});
    }else {
    	alert("Geolocation API is not supported in your browser.");
    }  
  }    
  infoWindow = new google.maps.InfoWindow;
  
    //Geolocation user's center position  
    if(autocenter_markers) 
        bounds = new google.maps.LatLngBounds();
    if(gmap_styles!="")
        map.setOptions({styles: gmap_styles});
    
    google.maps.event.addListener(map, 'idle', function(ev){
        // update the coordinates here
        handleGmapBoundChange();
        google.maps.event.clearListeners(map, 'idle');
    });    
    if(enable_gmap_zoom_callback)
    {        
        google.maps.event.addListener(map, 'dragend', function(ev){
            console.log('Dragging is done...');
            if(!autocenter_markers)
                HelloMapsSearch.performSearch();
        });    
        google.maps.event.addListener(map, 'zoom_changed', function(ev){
            console.log('Zoome Changed...');
            if(!autocenter_markers)
                HelloMapsSearch.performSearch();
        });    
    }
    if(show_full_screen_button)
        map.controls[google.maps.ControlPosition.TOP_RIGHT].push(new FullScreenControl(map));
    
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
	controlUI.style.backgroundColor = 'white';
	controlUI.style.borderStyle = 'solid';
	controlUI.style.borderWidth = '1px';
	controlUI.style.borderColor = '#717b87';
	controlUI.style.cursor = 'pointer';
	controlUI.style.textAlign = 'center';
	controlUI.style.boxShadow = 'rgba(0, 0, 0, 0.298039) 0px 1px 4px -1px';
	controlDiv.appendChild(controlUI);

	// Set CSS for the control interior.
	var controlText = document.createElement('div');
	controlText.style.fontFamily = 'Roboto,Arial,sans-serif';
	controlText.style.fontSize = '11px';
	controlText.style.fontWeight = '400';
	controlText.style.paddingTop = '1px';
	controlText.style.paddingBottom = '1px';
	controlText.style.paddingLeft = '6px';
	controlText.style.paddingRight = '6px';
	controlText.innerHTML = '<strong>Full Screen</strong>';
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
		controlText.innerHTML = '<strong>Exit full screen</strong>';
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
		controlText.innerHTML = '<strong>Full Screen</strong>';
		fullScreen = false;
		google.maps.event.trigger(mapObj, 'resize');
		mapObj.setCenter(center);
		clearInterval(interval);
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
