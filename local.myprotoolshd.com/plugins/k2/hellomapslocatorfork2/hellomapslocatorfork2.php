<?php
/**
 * @version     1.0.7g
 * @package     Plugin HelloMaps Locator for K2
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      JoomlaForce Team <support@joomlaforce.com> - http://www.joomlaforce.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.form.form');
JLoader::register('K2Plugin', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_k2' . DS . 'lib' . DS . 'k2plugin.php');
class plgK2hellomapslocatorfork2 extends K2Plugin {
    
    var $pluginName = 'hellomapslocatorfork2';
    var $pluginNameHumanReadable = 'Google Map';
    var $namespace = 'hellomapslocatorfork2';
	
	
    function plgK2hellomapslocatorfork2(&$subject, $params) {
        if ((defined('K2_JVERSION') && K2_JVERSION == '15') || (defined('JVERSION') && preg_match('/^1\.5\.[0-9]{2}$/', JVERSION))) {
            $this->dir = 'hellomapslocatorfork2';
            if (!defined('K2_JVERSION'))
                $this->namespace = '';
        }else{
				$this->dir = 'hellomapslocatorfork2/hellomapslocatorfork2';
			}
        parent::__construct($subject, $params);
        $this->loadLanguage();
    }

	
    function onRenderAdminForm(&$item, $type, $tab = '') {
        static $init = false;
        if (!$init && $type == 'item') {
            $plugins = new JRegistry($item->plugins);
            $zoom = '18';
            $lat = $plugins->get('latitude', null);
            $lon = $plugins->get('longitude', null);
           	
            $addressInputField = "plugins[address]";
			$latInputField = "plugins[latitude]";
            $logInputField = "plugins[longitude]";
            
            $null = null;
            
			
		$defaultaddress = "";
           if (!$lat && !$lon) {
              	$lat = $this->params->get('defaultlat', null);
               	$lon = $this->params->get('defaultlon', null);
                if (!$lat && !$lon && $defaultaddress) {
                        $geocode = file_get_contents('http://maps.google.com/maps/api/geocode/json?address=' . $this->mb_rawurlencode($defaultaddress) . '&sensor=false');
                    $output = json_decode($geocode);
                    if ($output->status == 'OK') {
                        $lat = $output->results[0]->geometry->location->lat;
                        $lon = $output->results[0]->geometry->location->lng;
                        $this->params->set('defaultlat', $lat);
                        $this->params->set('defaultlon', $lon);
                        $db = JFactory::getDBO();
                        $str = $this->params->toString();
						
						 $str = $db->escape($str);
                        $db->setQuery("UPDATE #__extensions SET params = '" . $str . "' WHERE folder = 'k2' AND element = 'k2locator'  LIMIT 1");
                        $db->query();
                    }
                }
            }
			if ( $lat == NULL){
			$lat = "0.000000";
			$lon = "0.000000";
			}
			$mainframe = & JFactory::getApplication();
            $doc = JFactory::getDocument();
            $js = "
		jQuery(function() {
			jQuery('.itemPlugins fieldset label').css('width','110px').css('display','block').css('margin','0 0 5px');
			
				" . (isset($mapContainer) ? $mapContainer : '') . "
				
				
				var myZoom =" . $zoom . ";
				var myMarkerIsDraggable = true;
				var myCoordsLenght = 6;
					
				var defaultLat =" . $lat . " ;
				var defaultLng = " . $lon . ";
				var map = new google.maps.Map(document.getElementById('gmap'), {
					zoom: myZoom,
					center: new google.maps.LatLng(defaultLat, defaultLng), 
						navigationControlOptions: {style: 
				google.maps.NavigationControlStyle.ZOOM_PAN,position: 
				google.maps.ControlPosition.TOP_LEFT}, 
						mapTypeId: google.maps.MapTypeId.ROADMAP 
				}); ";
			
			$js .= " var myMarker = new google.maps.Marker({
					position: new google.maps.LatLng(defaultLat, defaultLng),
					draggable: myMarkerIsDraggable
				}); ";
				 
			
			$js .= "	
				google.maps.event.addListener(myMarker, 'dragend', function(evt){
					
					document.getElementsByName('" . $latInputField . "')[0].value = evt.latLng.lat().toFixed(myCoordsLenght);
					document.getElementsByName('" . $logInputField . "')[0].value = evt.latLng.lng().toFixed(myCoordsLenght);
				});
				map.setCenter(myMarker.position);
				myMarker.setMap(map);
				
				" . (isset($mapresize) ? $mapresize : '') . " ";
			$js .= " var input = document.getElementById('plugins_address_');
			var autocomplete = new google.maps.places.Autocomplete(input, {types: ['geocode']});  
			//map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
			 var markers = [];

			var searchBox = new google.maps.places.SearchBox(
				/** @type {HTMLInputElement} */(input));
			
			  // Listen for the event fired when the user selects an item from the
			  // pick list. Retrieve the matching places for that item.
			  google.maps.event.addListener(searchBox, 'places_changed', function() {
				var places = searchBox.getPlaces();
			
				if (places.length == 0) {
				  return;
				}
				for (var i = 0, myMarker; myMarker = markers[i]; i++) {
				  myMarker.setMap(null);
				}
			
				// For each place, get the icon, place name, and location.
				markers = [];
				var bounds = new google.maps.LatLngBounds();
				for (var i = 0, place; place = places[i]; i++) {
			
				  // Create a marker for each place.
				  var myMarker = new google.maps.Marker({
					map: map,
					title: place.name,
					position: place.geometry.location,
					draggable: myMarkerIsDraggable
				  });
			
				  markers.push(myMarker);
			
				  bounds.extend(place.geometry.location);
				  
				  document.getElementsByName('" . $latInputField . "')[0].value = place.geometry.location.lat().toFixed(myCoordsLenght);
				  document.getElementsByName('" . $logInputField . "')[0].value = place.geometry.location.lng().toFixed(myCoordsLenght);
				
				  
				  google.maps.event.addListener(myMarker, 'dragend', function(evt){
					
					document.getElementsByName('" . $latInputField . "')[0].value = evt.latLng.lat().toFixed(myCoordsLenght);
					document.getElementsByName('" . $logInputField . "')[0].value = evt.latLng.lng().toFixed(myCoordsLenght);
				});
				map.setCenter(myMarker.position);
				myMarker.setMap(map);
				  
				}
			
				map.fitBounds(bounds);
			  });


			";
						
		 	$js .= " });
		
		";
		
            $doc->addCustomTag('<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?libraries=places"></script><style type="text/css">#gmap img{ max-width:none; } #gmap label{ width: auto; display:inline; }</style> <script type="text/javascript">' . $js . '</script>');
        }
        $init = true;
        $manifest = JPATH_SITE . DS . 'plugins' . DS . 'k2' . DS . $this->pluginName . DS . $this->pluginName . '.xml';
        if (!empty($tab)) {
            $path = $type . '-' . $tab;
        } else {
            $path = $type;
        }
        $form = JForm::getInstance($path, $manifest, array(), true, "fields[@name='params']/fieldset[@name='" . $path . "']");
		
        $data = json_decode($item->plugins, true);
		
        $data1 = array();
        if (!empty($data)) {
            foreach ($data as $k => $v) {
                $data1['plugins[' . $k . ']'] = $v;
            }
            $form->bind($data1);
        }
        $html = array();
		foreach ($form->getFieldset($path) as $field) {
			if ($field->type == 'header') {
                $html[] = '<div class="paramValueHeader">' . $field->input . '</div>';
            } elseif ($field->type == 'Spacer') {
                $html[] = '<div class="paramValueSpacer">&nbsp;</div>
										<div class="clr"></div>';
            } else {
                $html[] = '<div class="paramLabel">' . $field->label . '</div>' .
                        '<div class="paramValue">' . $field->input . '</div>' .
                        '<div class="clr"></div>';
            }
        }
        if (count($html) > 0) {
            $html = implode("\n", $html);
            if ($path == 'item-content') {
                    $html = "<div style='float:left; width:29%;'>" . $html . "</div><div id='gmap' style='float:right; width:70%; height:400px;'></div>";

            }
            $plugin = new JObject;
            $plugin->set('name', $this->pluginNameHumanReadable);
            $plugin->set('fields', $html);
            return $plugin;
        }
    }
function onBeforeK2Save(&$row, $isnew) {

        $plugins = new JRegistry($row->plugins);
        $address = $plugins->get('address', '');
        $latitude = $plugins->get('latitude', '');
        $longitude = $plugins->get('longitude', '');

        if (!$plugins || !$address)
            return;
        else {

            if ($latitude && $longitude)
                return;

                $geocode = file_get_contents('http://maps.google.com/maps/api/geocode/json?address=' . $this->mb_rawurlencode($address) . '&sensor=false');
         
            $output = json_decode($geocode);
                if ($output->status == 'OK') {
                    $plugins->set('latitude', $output->results[0]->geometry->location->lat);
                    $plugins->set('longitude', $output->results[0]->geometry->location->lng);
				}
                $row->plugins = $plugins->toString();
         
        }
        return;
    }

	function onAfterK2Save(&$row, $isnew) {
		
		$itemId = $row->id;
		$lat = $lng = null;
		
			$plugins = new JRegistry($row->plugins);
			$lat = $plugins->get('latitude', null);
            $lng = $plugins->get('longitude', null);
			$prv = $plugins->get('privacy', null);
			
			if(!$lat || !$lng) return;
		
		if(!$lat || !$lng || !$itemId) return;
		
		$db = JFactory::getDBO();
		
		$db->setQuery("INSERT INTO #__k2_k2locator (itemid,lat,lng,privacy) VALUES(".(int)$itemId.",".(double)$lat.",".(double)$lng.",".(int)$prv.") ON DUPLICATE KEY UPDATE lat = VALUES(lat),lng = VALUES(lng),privacy = VALUES(privacy) ");
		$db->execute();
		
		
	
	}
	function onK2AfterDisplay( &$item, &$params, $limitstart) {
		return $this->displayItemMap(__METHOD__,$item,$params,$limitstart);
	}
	function onK2BeforeDisplay( &$item, &$params, $limitstart) {
		return $this->displayItemMap(__METHOD__,$item,$params,$limitstart);
	}
	function onK2AfterDisplayTitle( &$item, &$params, $limitstart) {
		return $this->displayItemMap(__METHOD__,$item,$params,$limitstart);
	}		
	function onK2BeforeViewDisplay(){
				
	}	
	function onK2BeforeDisplayContent( &$item, &$params, $limitstart) {
		return $this->displayItemMap(__METHOD__,$item,$params,$limitstart);
	}
	function onK2AfterDisplayContent( &$item, &$params, $limitstart) {
		return $this->displayItemMap(__METHOD__,$item,$params,$limitstart);
	}
	
    protected function displayItemMap($method,&$item, &$params, $limitstart) {

		$event = $this->params->get('itemEvent','onK2AfterDisplay');
		
		if(__CLASS__."::".$event != $method) return '';
       
        $view = JRequest::getCmd('view');
        if ($view != 'item') { // load only in item view
            return;
        }
        if (!empty($this->params)) {
            $pluginParams = $this->params;
        } else {
            $pluginParams = $params;
        }
        $plugins = new JRegistry($item->plugins);
        
		
        $width = '100%';
        $height = '406';
        if(is_numeric($width)){
			$width .="px";
		}
		if(is_numeric($height)){
			$height .="px";
		}
		
       
        $zoom = "18";
        $type = "ROADMAP";
       
		$introtext = JString::substr($item->introtext, 0, 297);
		$introtext = $introtext."...";
		
        if ($item->imageXSmall) {
            $itemimage = '<img src="' . $item->imageXSmall . '" alt="' . $item->title . '" style="margin-right:6px;float:left;" />';
        } else {
            $itemimage = '';
        }
        
        
       	$itemExtraFields = '';
        

        $address = $plugins->get('address');
        $latitude = $plugins->get('latitude', '');
        $longitude = $plugins->get('longitude', '');

            $mapTypeId = 'mapTypeId: google.maps.MapTypeId.' . $type . '';
            $mapTypeControlOptions = 'style: google.maps.MapTypeControlStyle.DROPDOWN_MENU';

        if (($view == 'item' || $params->get('parsedInModule') == 1) && ($address != '' || ($latitude && $longitude))) {
		
		//prepare output
            $output = '
			<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false&amp;v=3.exp"></script>
			<script type="text/javascript">
					var myLocation = null;
					var geocoder;
					var map;
					var address = "' . $address . '";
					var lat 	= "' . $latitude . '";
					var lng 	= "' . $longitude . '";
				
			
					function initialize() {
											
						geocoder = new google.maps.Geocoder();
						var latlng = new google.maps.LatLng(lat, lng);
						var myOptions = {
							zoom: ' . $zoom . ',
							center: latlng,
							mapTypeControl: true,
							mapTypeControlOptions: {
								' . $mapTypeControlOptions . '
							},
							navigationControl: true,
							' . $mapTypeId . '
						};
						map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
	
						function getCoord() {
							if(lat.length > 0 && lng.length > 0) {
								return {\'latLng\': new google.maps.LatLng(lat, lng)};
							}
							return { \'address\': address};
						}
								
								if (geocoder) {
									geocoder.geocode(getCoord(), function(results, status) {
										if (status == google.maps.GeocoderStatus.OK) {
											if (status != google.maps.GeocoderStatus.ZERO_RESULTS) {
											map.setCenter(latlng);
											var infowindow = new google.maps.InfoWindow(
												{ content: 
													\'<div id="content">\'+
														\'<h1 id="firstHeading" class="firstHeading">' . htmlspecialchars($item->title, ENT_QUOTES) . '</h1>\'+
														\'<div id="bodyContent">\'+
															\'<p><b><i>' . str_replace("'", "\'", $address) . '</i></b></p>\' +
															\'<p>' . $itemimage . '<span class="hellomapslocatorfork2-intro">' . strip_tags(str_replace("\r", "", str_replace("\n", "", str_replace("'", "\"", str_replace("&nbsp;", " ", $introtext))))) . '\'+
															\'</span></p>\'+
														\'</div>\'+
														' . $itemExtraFields . '
													\'</div>\',
													size: new google.maps.Size(150,50),
														maxWidth:200
												});
												var marker = new google.maps.Marker({
													position: latlng,
													map: map, 
													title:address});
												
												
												google.maps.event.addListener(marker,\'click\', function() {
													infowindow.open(map,marker);
												});
												
												
												var rendererOptions = {
														map: map,
														suppressMarkers: 1
													}
												
				
											} else {
												alert("No results found");
											}
										} else {
											alert("Geocode was not successful for the following reason: " + status);
									}
								});
	
							}
						}
						window.addEvent("load",function(){initialize();});
				</script>
				<style type="text/css">
					#map_canvas {
						width:' . $width . ';
						height:' . $height . ';
						overflow: hidden;
						clear: both;
						background: none !important;
					}
					#map_canvas img {
						background: none !important;
						max-width:none !important;
					}
				</style>
		  <div id="map_canvas"></div>';
            return $output;
        }
		
    }
	 function mb_rawurlencode($url) {
        $encoded = '';
        $length = mb_strlen($url);
        for ($i = 0; $i < $length; $i++) {
            $encoded .= '%' . wordwrap(bin2hex(mb_substr($url, $i, 1)), 2, '%', true);
        }
        return $encoded;
    }
}