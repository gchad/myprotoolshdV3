<?php
/**
 * @version     1.0
 * @package 	mod_hellomaps
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      JoomlaForce Team <support@joomlaforce.com> - http://www.joomlaforce.com
 */
 
defined('_JEXEC') or die('Restricted access');
if(!defined('HELLOMAPS_FRONT_URL'))
    require_once JPATH_ADMINISTRATOR.'/components/com_hellomaps/helpers/constants.php';
if(!class_exists('HelloMapsHelper'))
    require_once JPATH_ADMINISTRATOR.'/components/com_hellomaps/helpers/hellomaps.php';
$document 	  = JFactory::getDocument();
global $allowedPluginsInModule;	
$allowedPluginsInModule = $params->get('allowed_plugins',array());

//Load Language
$language = JFactory::getLanguage();
$language->load( 'com_hellomaps', JPATH_SITE, $language->getName(), true); //load the component language

//Module Params
global $allowedPluginsInModule;	
$allowedPluginsInModule = $params->get('allowed_plugins',array());
$modulesettings = $params->get('modulesettings');
//end Module Params

$document->addScript(JURI::base().'components/com_hellomaps/assets/js/hquery-2.1.1.js');
JPluginHelper::importPlugin('hellomaps');

//Get Component Params
$hellomapParams = JComponentHelper::getParams('com_hellomaps');   
$gmap_style_properties =  $hellomapParams->get('jfstyler',"");
$gmap_api_key =  $hellomapParams->get('gmap_api_key','AIzaSyDXEbeQlKtqvBIHdtMXhBnaPa9KfteQ7IY');
$gmap_style_properties_js = "var gmap_styles='';";
$gmap_detault_zoom = 14;

if($gmap_style_properties!="")
{
    $gmap_style_properties_js = 'var gmap_styles='.$gmap_style_properties.';';
}
 
if ($modulesettings==1){
	
	$maptype_default = HelloMapsHelper::GetConfiguration('maptype_default','street');
	$map_dimensions_width = HelloMapsHelper::GetConfiguration('dimensions_width','100%');
	$map_dimensions_height = HelloMapsHelper::GetConfiguration('dimensions_height','100%');
	
	//Initialize Map
	$inizialize_autocenter_markers=HelloMapsHelper::GetConfiguration('initialize_autocenter_markers',1);
	$default_latitude = (float)HelloMapsHelper::GetConfiguration('initialize_default_lat',-34.397);
	$default_longitude = (float)HelloMapsHelper::GetConfiguration('initialize_default_lng',150.644);
	
	//Ask User Position
	$center_onuser_position = HelloMapsHelper::GetConfiguration('initialize_center_onuser_position',0);
	//sidebar settings start
	$sidebar_enable       = HelloMapsHelper::GetConfiguration('sidebar_enable',0);
	$sidebar_load_open    = HelloMapsHelper::GetConfiguration('sidebar_load_open',0);
	$sidebar_position     = HelloMapsHelper::GetConfiguration('sidebar_position','left');
	$sidebar_width        = HelloMapsHelper::GetConfiguration('sidebar_width','320');
	//sidebar settings end
	//results section from backend(the result counter)
	$results_enable       = HelloMapsHelper::GetConfiguration('results_enable',0);
	//results tab from backend
	//contents section from layout tab
	$contents_enable      = HelloMapsHelper::GetConfiguration('contents_enable',0);//to put the result html in the sidebar...
	//contents section from layout tab
	//Buttons
	$buttonsenabled_zoom_inout = HelloMapsHelper::GetConfiguration('buttonsenabled_zoom_inout',0);
	$buttonsenabled_street_view = HelloMapsHelper::GetConfiguration('buttonsenabled_street_view',0);
	$buttonsenabled_userposition = HelloMapsHelper::GetConfiguration('buttonsenabled_userposition',0);
	$buttonsenabled_fullscreen = HelloMapsHelper::GetConfiguration('buttonsenabled_fullscreen',1);
	$buttonsenabled_settings = HelloMapsHelper::GetConfiguration('buttonsenabled_settings',0);
	$mobilebuttons_listview = HelloMapsHelper::GetConfiguration('mobilebuttons_listview',0);
	//search settings of Layout tab
	$search_enable       = HelloMapsHelper::GetConfiguration('search_enable',0);
	//Clustering
	$enable_clustering = HelloMapsHelper::GetConfiguration('clustering_enable',0);
	$cluster_icon = HelloMapsHelper::GetConfiguration('clustering_default_image','');
	//Events Enabled
	$eventsenabled_marker_mouseover = HelloMapsHelper::GetConfiguration('eventsenabled_marker_mouseover',1);
	$eventsenabled_sidebar_mouseover = HelloMapsHelper::GetConfiguration('eventsenabled_sidebar_mouseover',1);
	$eventsenabled_mousescrollzoom= HelloMapsHelper::GetConfiguration('eventsenabled_mousescroll_zoom',0);
	//infowindow
	$hmod_deny_module_infowindow = '0';
 
}else{

	$maptype_default 	   = $params->get('hmod_maptype_default','street');	
	$map_dimensions_width  =  $params->get('hmod_dimensions_width','100%');
	$map_dimensions_height = $params->get('hmod_dimensions_height','300');
	
	//Initialize Map
	$inizialize_autocenter_markers= $params->get('hmod_initialize_autocenter_markers',1);
    
    
    /** GCHAD fix ***/
    $lat = (float)JRequest::getVar('lat',false);
    $long = (float)JRequest::getVar('long',false);
    
    if($lat && $long){
        
        $default_latitude = (float)JRequest::getVar('lat',false);
        $default_longitude =  (float)JRequest::getVar('long',false);
        $inizialize_autocenter_markers = 0;
        
        //Ask User Position
        $center_onuser_position = 0;
        
    } else {
        
        $default_latitude = (float)$params->get('hmod_initialize_default_lat',-34.397);
        $default_longitude = (float)$params->get('hmod_initialize_default_lng',150.644);
        //Ask User Position
        $center_onuser_position = $params->get('hmod_initialize_center_onuser_position',0);
    }
	
	
    
    
	//sidebar settings start
	$sidebar_enable       = $params->get('hmod_sidebar_enable',0);
	$sidebar_load_open    = $params->get('hmod_sidebar_load_open',0);
	$sidebar_position     = $params->get('hmod_sidebar_position','left');
	$sidebar_width        = $params->get('hmod_sidebar_width','320');
	//sidebar settings end
	
	//results section from backend(the result counter)
	$results_enable       = $params->get('hmod_results_enable',0);
	//results tab from backend
	
	//contents section from layout tab
	$contents_enable      = $params->get('hmod_contents_enable',0);
	//to put the result html in the sidebar...
	//contents section from layout tab
	
	//Buttons
	$buttonsenabled_zoom_inout   = $params->get('hmod_buttonsenabled_zoom_inout',0);
	$buttonsenabled_street_view  = $params->get('hmod_buttonsenabled_street_view',0);
	$buttonsenabled_userposition = $params->get('hmod_buttonsenabled_userposition',0);
	$buttonsenabled_fullscreen   = $params->get('hmod_buttonsenabled_fullscreen',1);
	$buttonsenabled_settings     = $params->get('hmod_buttonsenabled_settings',0);
	$mobilebuttons_listview      = $params->get('hmod_mobilebuttons_listview',0);
	//search settings of Layout tab
	$search_enable       = $params->get('hmod_search_enable',0);
	//Clustering
	$enable_clustering = $params->get('hmod_clustering_enable',0);
	$cluster_icon = $params->get('hmod_clustering_default_image','');
	//Events Enabled
	$eventsenabled_marker_mouseover  = $params->get('hmod_eventsenabled_marker_mouseover',1);
	$eventsenabled_sidebar_mouseover = $params->get('hmod_eventsenabled_sidebar_mouseover',1);
	$eventsenabled_mousescrollzoom   = $params->get('hmod_eventsenabled_mousescroll_zoom',0);
	//infowindow
	$hmod_deny_module_infowindow = $params->get('hmod_deny_module_infowindow',0);

}//end if

//###Default Params from Component Backend Set by Default###

//results section from backend(the result counter)         
$notice_enabled   = HelloMapsHelper::GetConfiguration('notice_enable',0);
$infolink_enable  = HelloMapsHelper::GetConfiguration('infolink_enable',0);
$results_type     = HelloMapsHelper::GetConfiguration('results_type','byzoom');
$results_position = HelloMapsHelper::GetConfiguration('results_position','bottom');
$enable_radius    = HelloMapsHelper::GetConfiguration('search_enable_radius',0);

if(is_numeric($map_dimensions_width)) $map_dimensions_width = $map_dimensions_width.'px';
if(is_numeric($map_dimensions_height))$map_dimensions_height = $map_dimensions_height; 
if($infolink_enable)
{
    $infolink_url = HelloMapsHelper::GetConfiguration('infolink_url','');
    if($infolink_url != "" && ( (strpos($infolink_url,'http://') === false) || (strpos($infolink_url,'https://') === false)))
    {
        $infolink_url = 'http://'.$infolink_url;
    }            
} 
if($notice_enabled)
{
    $notice_type    = HelloMapsHelper::GetConfiguration('notice_type','global');
}

$center_onuser_position_js = 'var center_onuser_position = false;';
if($center_onuser_position)
    $center_onuser_position_js = 'var center_onuser_position = true;';


$document->addStyleSheet(HELLOMAPS_FRONT_URL.'assets/mCustomScrollbar/jquery.mCustomScrollbar.css');
$document->addStyleSheet(HELLOMAPS_FRONT_URL.'assets/css/map_view.css');
JHtml::_('bootstrap.framework');//load bootstrap framework of joomla 
 
$document->addScript('https://maps.googleapis.com/maps/api/js?key='.$gmap_api_key.'&amp;sensor=true&amp;libraries=places,geocoder');
$inlineJS = $gmap_style_properties_js
                                ."\n".'var COM_HELLOMAP_SEARCH_NO_PARAMETER_SELECTED_MESSAGE="'.addslashes(JText::_('COM_HELLOMAP_SEARCH_NO_PARAMETER_SELECTED_MESSAGE')).'";'.'
                                var COM_HELLOMAP_SEARCH_IN_PROGRESS="'.addslashes(JText::_('COM_HELLOMAP_SEARCH_IN_PROGRESS')).'";
                                var COM_HELLOMAP_SEARCH_COMPLETED_LABEL="'.addslashes(JText::_('COM_HELLOMAP_SEARCH_COMPLETED_LABEL')).'";                                        
                                var default_latitude = '.$default_latitude.';
                                var default_longitude = '.$default_longitude.';
                                '.$center_onuser_position_js.'
                                var maptype_default = "'.$maptype_default.'";   
                                var totalText = "'.JText::_('COM_HELLOMAP_TOTAL_LABEL').'"; 
                                var HELLOMAPS_FRONT_URL = "'.HELLOMAPS_FRONT_URL.'"; 
                                var fullScreenText = "'.JText::_('COM_HELLOMAPS_FULL_SCREEN_LABEL').'";   
                                var exitFullScreenText = "'.JText::_('COM_HELLOMAPS_EXIT_FULL_SCREEN_LABEL').'";
								var deny_module_infowindow = '.$hmod_deny_module_infowindow.';
									                                                            
                                ';
if($inizialize_autocenter_markers)
{
    $inlineJS .= "var autocenter_markers = autocenter_markers_value = true;\n";
}
else
{
    $inlineJS .= "var autocenter_markers = autocenter_markers_value = false;\n";
}
if($eventsenabled_mousescrollzoom)
{
    $inlineJS .= "var gmap_scrollwheel = true;\n";
}
else
{
    $inlineJS .= "var gmap_scrollwheel = false;var cluster_style_options=null;\n";
}

if($enable_clustering)
{
    $inlineJS .= "var gmap_cluster_enabled = true;\n";
    $document->addScript(HELLOMAPS_FRONT_URL.'assets/js/markerclusterer.js');
    //$cluster_icon = HelloMapsHelper::GetConfiguration('clustering_default_image','');
    if($cluster_icon != "")
    {
        $inlineJS .= "var cluster_style_options = [{
        url: '".JURI::base().$cluster_icon."',
        height: 38,
        width: 48,                
        textColor: '#ff00ff',
        textSize: 10
      }];\n
    ";
    }
    else
    {
        $inlineJS .= "var cluster_style_options = null;\n";
    }
}
else
{
    $inlineJS .= "var gmap_cluster_enabled = false;\n
    var cluster_style_options=[{}];\n";
}

//available map type options
$maptype_enable_satellite = HelloMapsHelper::GetConfiguration('maptype_enable_satellite',0);
$inlineJS .= 'var maptype_enable_satellite = '.$maptype_enable_satellite.';'."\n";
$maptype_enable_terrain = HelloMapsHelper::GetConfiguration('maptype_enable_terrain',0);
$inlineJS .= 'var maptype_enable_terrain = '.$maptype_enable_terrain.';'."\n";
$maptype_enable_street = HelloMapsHelper::GetConfiguration('maptype_enable_street',0);
$inlineJS .= 'var maptype_enable_street = '.$maptype_enable_street.';'."\n";

$dispatcher = JEventDispatcher::getInstance();
$pluginsZoomCounterStatus = array();
$dispatcher->trigger('onGmapZoomCounterStatusCheck', array (&$pluginsZoomCounterStatus));

//if at least one plugin's enable zoom counter is enabled, prepare the callback function for gmap->onZoom
if(array_search(1,$pluginsZoomCounterStatus)!==false)
{
    $inlineJS .= "var enable_gmap_zoom_callback = true;\n";
}
else
{
    $inlineJS .= "var enable_gmap_zoom_callback = false;\n";
}
if($buttonsenabled_fullscreen)
{
    $inlineJS .= 'var show_full_screen_button = true;'."\n";
}
else
{
    $inlineJS .= 'var show_full_screen_button = false;'."\n";
}
if($eventsenabled_marker_mouseover)
{
    $inlineJS .= 'var marker_mouse_over_enabled = true;'."\n";
}
else
{
    $inlineJS .= 'var marker_mouse_over_enabled = false;'."\n";
}
if($eventsenabled_sidebar_mouseover)
{
    $inlineJS .= 'var sidebar_mouse_over_enabled = true;'."\n";
}
else
{
    $inlineJS .= 'var sidebar_mouse_over_enabled = false;'."\n";
}

//sidebar js settings
$inlineJS .= 'var sidebar_enable = '.$sidebar_enable.';'."\n";
$sideBarHeight = ($map_dimensions_height - 20);
$inlineJS .= 'var sidebar_height = '.$sideBarHeight.';'."\n";
$inlineJS .= 'var sidebar_position = "'.$sidebar_position.'";'."\n";
$inlineJS .= 'var sidebar_load_open = '.$sidebar_load_open.';'."\n";
//sidebar js settings

//results(counter) js settings
$inlineJS .= 'var results_enable = '.$results_enable.';'."\n";
//results(counter) js settings

//contents(sidebar) js settings
$inlineJS .= 'var contents_enable = '.$contents_enable.';'."\n";
//contents(sidebar) js settings

//search(sidebar) js settings
$inlineJS .= 'var search_enable = '.$search_enable.';'."\n";
$inlineJS .= 'var enable_radius_search = '.$enable_radius.';'."\n";

$notice_position = HelloMapsHelper::GetConfiguration('notice_position','left');
$notice_offset = 240;
$inlineJS .= 'var notice_position = "'.$notice_position.'";'."\n";
$inlineJS .= 'var notice_offset = '.$notice_offset.';'."\n";



$pluginNoticeExist = 0;
$dispatcher->trigger('onNoticeCheck', array (&$pluginNoticeExist));
$pluginNoticeExist = $pluginNoticeExist;

$globalNoticeText = HelloMapsHelper::GetConfiguration('notice_html','');
$show_global_notice = ($notice_enabled && ($notice_type == 'global') && $globalNoticeText!="" )?1:0;
$global_result_height = ($sideBarHeight - 40);

$inlineJS .= 'var show_global_notice = '.$show_global_notice.';'."\n";
$inlineJS .= 'var pluginNoticeExist = '.$pluginNoticeExist.';'."\n";


$infowindow_width = HelloMapsHelper::GetConfiguration('infowindow_width','300');

$inlineJS .= 'var infowindow_width = '.$infowindow_width.';'."\n";
$inlineJS .= 'var counter_result_type = "'.$results_type.'";'."\n";

/** GCHAD FIX***/
$inlineJS .= 'var gmap_default_zoom = '.$gmap_detault_zoom.';'."\n";
//debug($inlineJS);
$document->addScriptDeclaration($inlineJS);
//        $document->addScriptDeclaration('var COM_HELLOMAP_SEARCH_IN_PROGRESS="'.addslashes(JText::_('COM_HELLOMAP_SEARCH_IN_PROGRESS')).'"');
$document->addScript(HELLOMAPS_FRONT_URL.'assets/mCustomScrollbar/hquery.mCustomScrollbar.concat.min.js');
$document->addScript(HELLOMAPS_FRONT_URL.'assets/js/hquery.actual.min.js');
$document->addScript(HELLOMAPS_FRONT_URL.'assets/js/hquery.hellomaps.js');       

require(JModuleHelper::getLayoutPath('mod_hellomaps'));    
