/**
 * @version     1.0.7
 * @package     com_hellomaps
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      JoomlaForce Team <support@joomlaforce.com> - http://www.joomlaforce.com
 */
 
 /*var postCode = extractFromAdress(results[0].address_components, "postal_code");
	var street = extractFromAdress(results[0].address_components, "route");
	var town = extractFromAdress(results[0].address_components, "locality");
	var country = extractFromAdress(results[0].address_components, "country");*/
 
 
 	  var geocoder;
      var map;
	  var marker;
	      
      function initialize() {
        geocoder = new google.maps.Geocoder();
		var latitude  =  document.getElementById('jform_hellomapsusers_lat').value;
		var longitude =  document.getElementById('jform_hellomapsusers_lng').value;
		
		
		var myaddress =  document.getElementById('jform_hellomapsusers_address').value
		var latlng = new google.maps.LatLng(latitude,longitude);
		//var infowindow = new google.maps.InfoWindow();
		var marker;
		if (myaddress == null ){
			var mapOptions = {zoom: 4, center: new google.maps.LatLng(255, 255), mapTypeId: 'roadmap' }
		}else {
			var mapOptions = {zoom: 6, center: latlng, mapTypeId: 'roadmap' }
		}
		
        if(marker)
            marker.setMap(null);
            marker = new google.maps.Marker({
                map: map,
                position: latlng,
                draggable: true,
				
            });	
        map = new google.maps.Map(document.getElementById('hellomap_canvas'), mapOptions);
		//codeAddress();
		codeLatLng();
      }
	  
	  function extractFromAdress(components, type){
		for (var i=0; i<components.length; i++)
			for (var j=0; j<components[i].types.length; j++)
				if (components[i].types[j]==type) return components[i].long_name;
		return "";
	}
	
	  
	  function codeLatLng() {
		  var latitude = document.getElementById('jform_hellomapsusers_lat').value;
		  var longitude =  document.getElementById('jform_hellomapsusers_lng').value;
		  var latlng = new google.maps.LatLng(latitude, longitude);
		
			if(marker)
				marker.setMap(null);
				marker = new google.maps.Marker({
					map: map,
					position: latlng,
					draggable: true
				});
		  geocoder.geocode({'latLng': latlng}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
			  if (results[1]) {
				document.getElementById('jform_hellomapsusers_address').value = results[1].formatted_address;
				
				//FILL COUNTRY/STATE/ZIP
				var stato = extractFromAdress(results[0].address_components, "country");
				var regione = extractFromAdress(results[0].address_components, "administrative_area_level_1");
				var citta  = extractFromAdress(results[0].address_components, "locality");
				var myzip  = extractFromAdress(results[0].address_components, "postal_code");
				document.getElementById('jform_hellomapsusers_state').value = stato;
				document.getElementById('jform_hellomapsusers_region').value = regione;
				document.getElementById('jform_hellomapsusers_city').value = citta;
				//to store lat & lng in column
				document.getElementById('jform_hellomapsusers_latitude').value= latitude;
				document.getElementById('jform_hellomapsusers_longitude').value= longitude;
				
				
				
			  } else {
				alert('No results found');
			  }
			} /*else {
			  alert('Geocoder failed due to: ' + status);
			}*/	
			codeAddress();		
		  });
	
	}

      function codeAddress() {
        var address = document.getElementById('jform_hellomapsusers_address').value;
        geocoder.geocode( { 'address': address}, function(results, status) {
          if (status == google.maps.GeocoderStatus.OK) {
            map.setCenter(results[0].geometry.location);
            if(marker)
              marker.setMap(null);
            marker = new google.maps.Marker({
                map: map,
                position: results[0].geometry.location,
                draggable: true
            });
            google.maps.event.addListener(marker, "dragend", function() {
              document.getElementById('jform_hellomapsusers_lat').value = marker.getPosition().lat();
              document.getElementById('jform_hellomapsusers_lng').value = marker.getPosition().lng();
			  codeLatLng();
            });
         	  document.getElementById('jform_hellomapsusers_lat').value = marker.getPosition().lat();
         	  document.getElementById('jform_hellomapsusers_lng').value = marker.getPosition().lng();

			
			var newposlat = marker.getPosition().lat();
			var newposlng = marker.getPosition().lng();
			var newlatlng = new google.maps.LatLng(newposlat, newposlng);
		    geocoder.geocode({'latLng': newlatlng}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
			  if (results[1]) {
				document.getElementById('jform_hellomapsusers_address').value = results[1].formatted_address;
				
				//FILL COUNTRY/STATE/ZIP
				var stato = extractFromAdress(results[0].address_components, "country");
				var regione = extractFromAdress(results[0].address_components, "administrative_area_level_1");
				var citta  = extractFromAdress(results[0].address_components, "locality");
				var myzip  = extractFromAdress(results[0].address_components, "postal_code");
				document.getElementById('jform_hellomapsusers_state').value = stato;
				document.getElementById('jform_hellomapsusers_region').value = regione;
				document.getElementById('jform_hellomapsusers_city').value = citta;
				//to store lat & lng in column
				document.getElementById('jform_hellomapsusers_latitude').value= newposlat;
				document.getElementById('jform_hellomapsusers_longitude').value= newposlng;
				
			  } else {
				alert('No results found');
			  }
			} 
		  });

          }/* else {
            alert('Geocode was not successful for the following reason: ' + status);
          }*/

        });
      }