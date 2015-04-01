<?php
/**
 * @version     1.0.7
 * @package     com_hellomaps
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      JoomlaForce Team <support@joomlaforce.com> - http://www.joomlaforce.com
 */

defined('_JEXEC') or die;

/**
 * HTML View class for the Contacts component
 *
 * @package     Joomla.Site
 * @subpackage  com_hellomaps
 * @since       1.5
 */
class HelloMapsViewHelloMaps extends JViewLegacy
{
	protected $state;

	public function display($tpl = null)
	{
		$app		  = JFactory::getApplication();
		$params		  = $app->getParams();
		$this->params = $params;
        $layout       = JRequest::getCmd('layout', 'default');
        
        
        
		$document 	  = JFactory::getDocument();	

		// Get some data from the models
		$state		= $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
        
        JPluginHelper::importPlugin('hellomaps');
        
        $gmap_style_properties =  $this->params->get('jfstyler',"");
        $gmap_api_key =  $this->params->get('gmap_api_key','AIzaSyDXEbeQlKtqvBIHdtMXhBnaPa9KfteQ7IY');
        $gmap_style_properties_js = "var gmap_styles='';";
        if($gmap_style_properties!="")
        {
            $gmap_style_properties_js = 'var gmap_styles='.$gmap_style_properties.';';
        }
        
        $this->default_latitude = (float)HelloMapsHelper::GetConfiguration('initialize_default_lat',-34.397);
        $this->default_longitude = (float)HelloMapsHelper::GetConfiguration('initialize_default_lng',150.644);
        $this->center_onuser_position = HelloMapsHelper::GetConfiguration('initialize_center_onuser_position',0);
        $this->buttonsenabled_zoom_inout = HelloMapsHelper::GetConfiguration('buttonsenabled_zoom_inout',0);
        $this->buttonsenabled_street_view = HelloMapsHelper::GetConfiguration('buttonsenabled_street_view',0);
        $this->buttonsenabled_userposition = HelloMapsHelper::GetConfiguration('buttonsenabled_userposition',0);
        $this->buttonsenabled_fullscreen = HelloMapsHelper::GetConfiguration('buttonsenabled_fullscreen',0);
        $this->eventsenabled_marker_mouseover = HelloMapsHelper::GetConfiguration('eventsenabled_marker_mouseover',0);
        $this->eventsenabled_sidebar_mouseover = HelloMapsHelper::GetConfiguration('eventsenabled_sidebar_mouseover',0);
        $this->notice_enabled    = HelloMapsHelper::GetConfiguration('notice_enable',0);
        $this->infolink_enable = HelloMapsHelper::GetConfiguration('infolink_enable',0);
        $this->mobilebuttons_listview = HelloMapsHelper::GetConfiguration('mobilebuttons_listview',0);
        $this->buttonsenabled_settings = HelloMapsHelper::GetConfiguration('buttonsenabled_settings',0);
        
        //sidebar settings start
        $this->sidebar_enable       = HelloMapsHelper::GetConfiguration('sidebar_enable',0);
        $this->sidebar_load_open    = HelloMapsHelper::GetConfiguration('sidebar_load_open',0);
        $this->sidebar_position     = HelloMapsHelper::GetConfiguration('sidebar_position','left');
        $this->sidebar_width        = HelloMapsHelper::GetConfiguration('sidebar_width','20');
        //sidebar settings end
        
        //results section from backend(the result counter)
        $this->results_enable       = HelloMapsHelper::GetConfiguration('results_enable',0);
        $this->results_type         = HelloMapsHelper::GetConfiguration('results_type','byzoom');
        $this->results_position     = HelloMapsHelper::GetConfiguration('results_position','bottom');
        //results tab from backend
        
        //contents section from layout tab
        $this->contents_enable      = HelloMapsHelper::GetConfiguration('contents_enable',0);//to put the result html in the sidebar...
        //contents section from layout tab
        
        //search settings of Layout tab
        $this->search_enable       = HelloMapsHelper::GetConfiguration('search_enable',0);
        $this->enable_radius       = HelloMapsHelper::GetConfiguration('search_enable_radius',0);
        
        if($this->infolink_enable)
        {
            $this->infolink_url = HelloMapsHelper::GetConfiguration('infolink_url','');
            if($this->infolink_url != "" && ( (strpos($this->infolink_url,'http://') === false) || (strpos($this->infolink_url,'https://') === false)))
            {
                $this->infolink_url = 'http://'.$this->infolink_url;
            }            
        } 
        if($this->notice_enabled)
        {
            $this->notice_type    = HelloMapsHelper::GetConfiguration('notice_type','global');
        }
        
        $this->map_dimensions_width = HelloMapsHelper::GetConfiguration('dimensions_width','100%');
        if(is_numeric($this->map_dimensions_width))
            $this->map_dimensions_width = $this->map_dimensions_width.'px';
        $this->map_dimensions_height = HelloMapsHelper::GetConfiguration('dimensions_height','100%');
        if(is_numeric($this->map_dimensions_height))
            //$this->map_dimensions_height = $this->map_dimensions_height.'px';
			$this->map_dimensions_height = $this->map_dimensions_height;                
            
        
        $center_onuser_position_js = 'var center_onuser_position = false;';
        if($this->center_onuser_position)
            $center_onuser_position_js = 'var center_onuser_position = true;';
        
        
        $document->addStyleSheet(HELLOMAPS_FRONT_URL.'assets/mCustomScrollbar/jquery.mCustomScrollbar.css');
        
        $document->addStyleSheet(HELLOMAPS_FRONT_URL.'assets/css/map_view.css');  
		$document->addScript('https://maps.googleapis.com/maps/api/js?key='.$gmap_api_key.'&amp;sensor=true&amp;libraries=places,geocoder');
		JHtml::_('bootstrap.framework');//load bootstrap framework of joomla
        $inlineJS = $gmap_style_properties_js
                                        ."\n".'var COM_HELLOMAP_SEARCH_NO_PARAMETER_SELECTED_MESSAGE="'.addslashes(JText::_('COM_HELLOMAP_SEARCH_NO_PARAMETER_SELECTED_MESSAGE')).'";'.'
                                        var COM_HELLOMAP_SEARCH_IN_PROGRESS="'.addslashes(JText::_('COM_HELLOMAP_SEARCH_IN_PROGRESS')).'";
                                        var COM_HELLOMAP_SEARCH_COMPLETED_LABEL="'.addslashes(JText::_('COM_HELLOMAP_SEARCH_COMPLETED_LABEL')).'";                                        
                                        var default_latitude = '.$this->default_latitude.';
                                        var default_longitude = '.$this->default_longitude.';
                                        '.$center_onuser_position_js.'
                                        var maptype_default = "'.HelloMapsHelper::GetConfiguration('maptype_default','street').'";   
                                        var totalText = "'.JText::_('COM_HELLOMAP_TOTAL_LABEL').'"; 
                                        var HELLOMAPS_FRONT_URL = "'.HELLOMAPS_FRONT_URL.'"; 
                                        var fullScreenText = "'.JText::_('COM_HELLOMAPS_FULL_SCREEN_LABEL').'";   
                                        var exitFullScreenText = "'.JText::_('COM_HELLOMAPS_EXIT_FULL_SCREEN_LABEL').'";                                                                         
                                        ';
        if(HelloMapsHelper::GetConfiguration('initialize_autocenter_markers',1))
        {
            $inlineJS .= "var autocenter_markers = autocenter_markers_value = true;\n";
        }
        else
        {
            $inlineJS .= "var autocenter_markers = autocenter_markers_value = false;\n";
        }
        if(HelloMapsHelper::GetConfiguration('eventsenabled_mousescroll_zoom',1))
        {
            $inlineJS .= "var gmap_scrollwheel = true;\n";
        }
        else
        {
            $inlineJS .= "var gmap_scrollwheel = false;var cluster_style_options=null;\n";
        }
        if(HelloMapsHelper::GetConfiguration('clustering_enable',0))
        {
            $inlineJS .= "var gmap_cluster_enabled = true;\n";
            $document->addScript(HELLOMAPS_FRONT_URL.'assets/js/markerclusterer.js');
            $cluster_icon = HelloMapsHelper::GetConfiguration('clustering_default_image','');
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
        $this->maptype_enable_satellite = HelloMapsHelper::GetConfiguration('maptype_enable_satellite',0);
        $inlineJS .= 'var maptype_enable_satellite = '.$this->maptype_enable_satellite.';'."\n";
        $this->maptype_enable_terrain = HelloMapsHelper::GetConfiguration('maptype_enable_terrain',0);
        $inlineJS .= 'var maptype_enable_terrain = '.$this->maptype_enable_terrain.';'."\n";
        $this->maptype_enable_street = HelloMapsHelper::GetConfiguration('maptype_enable_street',0);
        $inlineJS .= 'var maptype_enable_street = '.$this->maptype_enable_street.';'."\n";
        
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
        if($this->buttonsenabled_fullscreen)
        {
            $inlineJS .= 'var show_full_screen_button = true;'."\n";
        }
        else
        {
            $inlineJS .= 'var show_full_screen_button = false;'."\n";
        }
        if($this->eventsenabled_marker_mouseover)
        {
            $inlineJS .= 'var marker_mouse_over_enabled = true;'."\n";
        }
        else
        {
            $inlineJS .= 'var marker_mouse_over_enabled = false;'."\n";
        }
        if($this->eventsenabled_sidebar_mouseover)
        {
            $inlineJS .= 'var sidebar_mouse_over_enabled = true;'."\n";
        }
        else
        {
            $inlineJS .= 'var sidebar_mouse_over_enabled = false;'."\n";
        }
        
        //sidebar js settings
        $inlineJS .= 'var sidebar_enable = '.$this->sidebar_enable.';'."\n";
        $this->sideBarHeight = ($this->map_dimensions_height - 20);
        $inlineJS .= 'var sidebar_height = '.$this->sideBarHeight.';'."\n";
        $inlineJS .= 'var sidebar_position = "'.$this->sidebar_position.'";'."\n";
        $inlineJS .= 'var sidebar_load_open = '.$this->sidebar_load_open.';'."\n";
        //sidebar js settings
        
        //results(counter) js settings
        $inlineJS .= 'var results_enable = '.$this->results_enable.';'."\n";
        //results(counter) js settings
        
        //contents(sidebar) js settings
        $inlineJS .= 'var contents_enable = '.$this->contents_enable.';'."\n";
        //contents(sidebar) js settings
        
        //search(sidebar) js settings
        $inlineJS .= 'var search_enable = '.$this->search_enable.';'."\n";
        $inlineJS .= 'var enable_radius_search = '.$this->enable_radius.';'."\n";
        
        $this->notice_position = HelloMapsHelper::GetConfiguration('notice_position','left');
        $this->notice_offset = 240;
        $inlineJS .= 'var notice_position = "'.$this->notice_position.'";'."\n";
        $inlineJS .= 'var notice_offset = '.$this->notice_offset.';'."\n";
        
        
        
        $pluginNoticeExist = 0;
        $dispatcher->trigger('onNoticeCheck', array (&$pluginNoticeExist));
        $this->pluginNoticeExist = $pluginNoticeExist;
        
        $this->globalNoticeText = HelloMapsHelper::GetConfiguration('notice_html','');
        $this->show_global_notice = ($this->notice_enabled && ($this->notice_type == 'global') && $this->globalNoticeText!="" )?1:0;
        $this->global_result_height = ($this->sideBarHeight - 40);
        
        $inlineJS .= 'var show_global_notice = '.$this->show_global_notice.';'."\n";
        $inlineJS .= 'var pluginNoticeExist = '.$this->pluginNoticeExist.';'."\n";
        
        $this->infowindow_width = HelloMapsHelper::GetConfiguration('infowindow_width','300');
        $inlineJS .= 'var infowindow_width = '.$this->infowindow_width.';'."\n";
        $inlineJS .= 'var counter_result_type = "'.$this->results_type.'";'."\n";
        
        
        $document->addScriptDeclaration($inlineJS);
//        $document->addScriptDeclaration('var COM_HELLOMAP_SEARCH_IN_PROGRESS="'.addslashes(JText::_('COM_HELLOMAP_SEARCH_IN_PROGRESS')).'"');
        
        $document->addScript(HELLOMAPS_FRONT_URL.'assets/mCustomScrollbar/hquery.mCustomScrollbar.concat.min.js');
        $document->addScript(HELLOMAPS_FRONT_URL.'assets/js/hquery.actual.min.js');
        
        $document->addScript(HELLOMAPS_FRONT_URL.'assets/js/hquery.hellomaps.js');    
        
        
                      
        /*if($this->show_global_notice)
        {
            $this->global_result_height = floor($this->global_result_height * 0.70)-20;
        }
        else
        {
            $this->global_result_height = $this->global_result_height - 100;
        }*/
        
        

		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 */
	protected function _prepareDocument()
	{
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu();
		$pathway	= $app->getPathway();
		$title 		= null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_('COM_CONTACT_DEFAULT_PAGE_TITLE'));
		}
	}
}
