<?php
/**
 * @version     1.0.8
 * @package     com_hellomaps
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      JoomlaForce Team <support@joomlaforce.com> - http://www.joomlaforce.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.model');
if(!class_exists('HelloMapsHelper'))
    require_once(JPATH_ADMINISTRATOR.'/components/com_hellomaps/helpers/hellomaps.php');
/**
 * $allowedPluginsInModule
 * It is really important when you use this plugin inside module. tabs will be shown if the plugin is selected at backend, 
 * otherwise no action will be takn
 * It is not important for component and will be null
*/

if(!class_exists('plgHellomapsEasysocialevents'))
{
	class plgHellomapsEasysocialevents extends JPlugin
	{
		var $name		= 'Easysocialevents';
		private $filter_id  = 'easysocialevents';//Must be unqiue for each plugin, we will trigger javascript functions by this
        private $easysocialeventsInstalled = false;

	
	    function plgHellomapsEasysocialevents(& $subject, $config)
	    {		
			parent::__construct($subject, $config);
			//load language file
			$language = JFactory::getLanguage();
			$language->load('plg_hellomaps_easysocialevents', JPATH_ADMINISTRATOR, $language->getName(), true);
			$language->load( 'com_easysocial', JPATH_ADMINISTRATOR, $language->getName(), true);
			$file 	= JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/foundry.php';
			if( !JFile::exists( $file ) )
			{
				return;
			}
			require_once( $file );
			$this->easysocialeventsInstalled = true;
	    }

		public function onFilterListPrepare(&$filters) {  
		
		     if(!$this->easysocialeventsInstalled)
                return false;  
		    global $allowedPluginsInModule;
            if(isset($allowedPluginsInModule) && in_array($this->filter_id,$allowedPluginsInModule)== false)
            {
                return false;
            }
			$document = JFactory::getDocument();
			$tabTitle = $this->params->get('tab_title','Easysocialevents');
            $marker_width = (int)$this->params->get('marker_icon_width',45);
            $marker_height = (int)$this->params->get('marker_icon_height',45);
			$show_in_sidebar = (int)$this->params->get('show_in_sidebar',1);
			$enable_easysocialevents_detail_sidebar = (int)$this->params->get('enable_easysocialevents_detail_sidebar',1);
			
			$filterElementsHTML = $this->GetFilterElements();
			
			$filters[] = array('title'=>$tabTitle,'filter_id'=>$this->filter_id,'content'=>$filterElementsHTML,'show_in_sidebar'=>$show_in_sidebar);
			JHtml::_('bootstrap.framework');//load bootstrap framework of joomla	
            $document->addCustomTag('<link rel="stylesheet" href="'.JURI::root().'plugins/hellomaps/easysocialevents/css/style.css'.'" type="text/css" />');//to add css at last
            $document->addScriptDeclaration("var easysocialevents_marker_type='".$this->params->get('marker_icon','avatar')."';\n
                                            var easysocialevents_marker_width=".$marker_width.";\n
                                            var easysocialevents_marker_height=".$marker_height.";\n
											var easysocialevents_show_in_sidebar=".$show_in_sidebar.";\n    
                                            var enable_easysocialevents_detail_sidebar=".$enable_easysocialevents_detail_sidebar.";\n                                             
                                            ");            
			$document->addScript(JURI::root().'plugins/hellomaps/easysocialevents/js/script.js');				
		}
		
		/**
		 * [GetFilterElements description]
		 * Load the filter elements in a string
		 * return $html string
		 */
		private function GetFilterElements()
		{

			$show_search = $this->params->get('show_search',1);//show searchbox
			$show_filters = $this->params->get('show_filters',1);//show filters
            $search_enable = HelloMapsHelper::GetConfiguration('search_enable',1);//from component
            $search_enable_radius = HelloMapsHelper::GetConfiguration('search_enable_radius',1);//from component
            $contents_enable      = HelloMapsHelper::GetConfiguration('contents_enable',0);//to put the result html in the sidebar...
			
			$fitler_profile_types = $this->params->get('fitler_profile_types',array());//Allowed profile types
			$search_fields = $this->params->get('search_fields',array());
			
			if($show_filters && !empty($fitler_profile_types))
			{
				$profileTypes = $this->getProfileTypeEasysocial();		
			}
			
			ob_start();
			include(JPATH_ROOT.'/plugins/hellomaps/easysocialevents/views/filter.php');
			$filterElementsHTML = ob_get_contents();
			ob_end_clean();
			return $filterElementsHTML;
		}
		
		//ADD GIANLUCA
		public function getCustomFieldLabelEasysocial($fieldCodes) 
		{
			$db = JFactory::getDBO();
			$fieldsLabels = array();
			$totalFields = count($fieldCodes);
			for($i=0; $i < $totalFields; $i++)
			{
				if($i == 0)
					$query = $db->getQuery(true);
				else    
					$query->clear();		
				
				$query	= 'SELECT a.title, a.unique_key '
						.' FROM '.($db->quoteName('#__social_fields', 'a'))
					 	.' INNER JOIN '.($db->quoteName('#__social_fields_data', 'b').'ON a.id = b.field_id')
						.' WHERE ' . $db->quoteName( 'a.state' ) . '=' . $db->Quote( 1 ) . ' '
						.' AND '. $db->quoteName( 'b.type' ) . '= ' . $db->Quote('event')
						.' AND '. $db->quoteName( 'a.unique_key' ) . '= ' .$db->quote($fieldCodes[$i])
						.' GROUP BY ' . $db->quoteName( 'a.title' );				
								
				$db->setQuery($query);
				$db->query();
				$fieldLabel = $db->loadResult();
				if($fieldLabel != "")
					$fieldsLabels[$fieldCodes[$i]] = array('label'=>$fieldLabel);
			}
			
			
			
			return $fieldsLabels;	
						
		}
		
		public function getCustomFieldRAWEasysocial ($eventid, $fieldcode) 
		{		
			$db = JFactory::getDBO();
			$query	= 'SELECT b.raw, a.unique_key '
					.' FROM '.($db->quoteName('#__social_fields', 'a'))
					.' INNER JOIN '.($db->quoteName('#__social_fields_data', 'b').' ON a.id = b.field_id')
					.' WHERE ' . $db->quoteName( 'b.uid' ) . '=' . $db->Quote( $eventid )
					// passare invece dell' userid, passare l'id evento			
					.' AND '. $db->quoteName( 'a.unique_key' ) . '= ' .$db->quote($fieldcode);

			$db->setQuery($query);
			$result = $db->loadResult();	
			return $result;	
		}
		

		public function getProfileTypeEasysocial(){
			
			$db		= JFactory::getDBO();					
			$query	= 'SELECT title AS name, id AS id FROM ' . $db->quoteName( '#__social_clusters_categories' ) . ' '
					. 'WHERE ' . $db->quoteName( 'state' ) . '=' . $db->Quote( 1 ) . ' '
					. 'AND '. $db->quoteName( 'type' ) . '= ' . $db->Quote('event')
					. ' ORDER BY ' . $db->quoteName( 'ordering' );
			$db->setQuery( $query );
			$items = $db->loadObjectList();
			return $items;
		}

		/**
		 * [onHellomapSearch description]
		 * @param  [type] $litsenerName [only search when litsener name is same as the plugins filter_id]
		 * @param  [type] $searchData   [description]
		 * @return [type]               [description]
		 */
		public function onHellomapSearch($litsenerName,$searchParam,&$searchResult)
		{
			
		
			// 2. filtri per date
			// opzioni escludi eventi
			// ottimizzazione query con filtri backend di ricerca
			// escludi eventi scaduti
			
		    if(!$this->easysocialeventsInstalled)
                return false; 
			if($litsenerName == $this->filter_id)
			{
				$show_search = $this->params->get('show_search',1);//show searchbox
				$show_filters = $this->params->get('show_filters',1);//show filters
				$search_fields = $this->params->get('search_fields',array());//show filters
           
			    $fitler_profile_types = $this->params->get('fitler_profile_types',array());//show filters
                $details_extra_fields = $this->params->get('details_extra_fields',array());//extra fields    
				$details_extra_fields_labels = $this->getCustomFieldLabelEasysocial($details_extra_fields);   

                $marker_icon        = $this->params->get('marker_icon','avatar');
                $custom_marker_image= $this->params->get('custom_marker_image','');
                $markers_name         = $this->params->get('markers_name',''); //json data for each category                
                $display_marker_infowindow = (boolean)$this->params->get('display_marker_infowindow',1) && HelloMapsHelper::GetConfiguration('infowindow_enable',1); //json data for each category
                $default_marker_icon_url = JURI::root().'plugins/hellomaps/easysocialevents/images/markers/info.png';
                
                $profileTypeMarkers = array(); 
                if(!empty($markers_name))
                {
                    $markers_name = json_decode($markers_name,true);
                    if(is_array($markers_name))
                    {
                        foreach($markers_name as $marker_name)
                        {
                            $profileTypeMarkers[$marker_name['profileTypeID']] = $marker_name['profileMarkerImage'];
                        }
                    }
                }
				
                $search_text = isset($searchParam['search_text'])?$searchParam['search_text']:"";
                $results = array();  
                $rows = array();     
				//print_r ($search_fields);    
                //if($show_search && $show_filters && !empty($searchParam))
                if(!empty($searchParam))
                {
					$easysocialEventsFilters = array();//array of objects
					
					if(!empty($search_fields))
					{
						foreach ($search_fields as $searchFieldName) {
							$easysocialEventsFilter = new stdClass;
							$easysocialEventsFilter->field = $searchFieldName;
							$easysocialEventsFilter->condition = 'contain';
							$easysocialEventsFilter->fieldType = 'text';//get from db
							$easysocialEventsFilter->value = $search_text;
							$easysocialEventsFilters[] = $easysocialEventsFilter;
						}
					}
					$join='or';
					$avatarOnly='';
					$sorting = '';	
					
					$searchedProfileTypes = array();
					if(!empty($fitler_profile_types) && !empty($searchParam['profileType']) && is_array($searchParam['profileType']) )
                    {
                        foreach($searchParam['profileType'] as $searchedProfileType)
                        {
                            if(in_array($searchedProfileType,$fitler_profile_types)!==false)
                            {
                                $searchedProfileTypes[] = $searchedProfileType;
                            }
                        }
                    }	
					$results = $this->getAdvanceSearch($easysocialEventsFilters,$searchedProfileTypes);
                    if(!empty($results))
                    {
						foreach($results as $result ){
						//Creazione Array Valori Markers
						$row       = array(); 
						$row['id'] = $result->id;
						//print_r($result);
						
						//Call Event Data Object
						/* fare riferimento alle funzioni presenti nei seguenti file:
						administrator/component/com_easysocial/includes/cluster/cluster.php
						administrator/component/com_easysocial/model/
						administrator/component/com_easysocial/includes/event/event.php*/
						$event = Foundry::event($result->id);
									
						$row['title'] = $event->getName();
						$row['thumb'] = $event->getAvatar(SOCIAL_AVATAR_SMALL);
						$row['largeAvatar'] = $event->getAvatar(SOCIAL_AVATAR_LARGE);
						$row['profileLink']  = $event->getPermalink();
						$row['latestStatus']="";
						if (!empty($event->description))
							$row['latestStatus'] = nl2br((strip_tags($event->description)), 350);
						
						$row['latitude'] = (float)$result->latitude;  
						$row['longitude'] = (float)$result->longitude; 
						$row['location']= $result->address;	
						$row['eventdate'] = $event->getStartEndDisplay();
						$row['isOnline']="";
						$row['lastLogin']= "";
						
						//Type Of Event
						if ($event->isOpen()) { 
							$row['eventType'] = JText::_('COM_EASYSOCIAL_EVENTS_OPEN_EVENT');
						}
						if ($event->isPrivate()) {
							$row['eventType'] = JText::_('COM_EASYSOCIAL_EVENTS_PRIVATE_EVENT');
						}
						if ($event->isInviteOnly()) {
							$row['eventType'] = JText::_('COM_EASYSOCIAL_EVENTS_INVITE_EVENT');
						}
										
						$row['profileTypeName']= $event->getCategory()->title;
						$profileTypeId = $event->getCategory()->id;
				

						 if(!empty($details_extra_fields_labels))
                            {
                                $extraFieldsValues = array();
                                foreach($details_extra_fields_labels as $field_code=>$fieldData)
                                {
									$fieldData['value'] = $this->getCustomFieldRAWEasysocial($result->id, $field_code);//get custom field value
                                    if(!empty($fieldData['value']) && !is_array($fieldData['value']))
                                        $fieldData['value'] = JText::_($fieldData['value']);
										
                                    $details_extra_fields_labels[$field_code] = $fieldData;
                                }
                            }
                            $row['extraFields'] = $details_extra_fields_labels;
                            $row['qr_code_img'] = 'http://chart.apis.google.com/chart?cht=qr&chs=80x80&chl=geo:'.$row['latitude'].','.$row['longitude'];
							
							
							$marker_icon_url = $default_marker_icon_url;
                            if($marker_icon == 'avatar')
                            {
                                $marker_icon_url = $row['thumb'];
                            }						
                            else if($marker_icon == 'easysocialevent-type' && isset($profileTypeMarkers[$profileTypeId]))
                            {
								
                                $marker_icon_path = JPATH_SITE.'/plugins/hellomaps/easysocialevents/images/markers/'.$profileTypeMarkers[$profileTypeId];
                                if(is_file($marker_icon_path))
                                    $marker_icon_url = JURI::base().'plugins/hellomaps/easysocialevents/images/markers/'.$profileTypeMarkers[$profileTypeId];
                            }
                            else if($marker_icon == 'custom' && $custom_marker_image != "")
                            {
                                $marker_icon_url = $custom_marker_image;
                            }
                            $row['marker_icon_url'] = $marker_icon_url;
                            $row['display_marker_infowindow'] = $display_marker_infowindow;
							
                            $infowindow_content = '';
							if($display_marker_infowindow)
                            {
                                $infowindow_content = $this->GetMarkerInfoWindowContent($row);
                            }
                            $row['html'] = $this->GetSearchResultHTML($row);
                            $row['infowindow_content'] = $infowindow_content;
                            $rows[] = $row;
							
						}

                    	}
					}                  
				//}    
				$total = count($results);
                $display_marker_result_count = HelloMapsHelper::GetConfiguration('results_enable',0) && (boolean)$this->params->get('display_marker_result_count',0);
                $display_marker_result_count  = $display_marker_result_count && (HelloMapsHelper::GetConfiguration('results_type','byzoom') == 'byzoom');       
                $searchResult[$this->filter_id] = array('total'=>$total,'rows'=>$rows,'display_marker_result_count'=>$display_marker_result_count);

                if($display_marker_result_count)
                {
                    $eventCountWithoutFilter = $this->GetEasysocialEventsTotal();
                    $percentage = 0;
                    if($eventCountWithoutFilter > 0)
                    {
                        $percentage = ceil(($total / $eventCountWithoutFilter) * 100);    
                    }    
                    $percentageBlock = "<div class='percentageBlock easysocialeventsTotalBlock'>
                                           <span class='icon'></span>".JText::sprintf('PLG_HELLOMAPS_EASYSOCIALEVENTS_TOTAL_LABEL',$percentage)."
                                           <div class='color'>
                                            ".number_format($total,0,'',',')."
                                           </div>
                                        </div>";
                    $searchResult[$this->filter_id]['percentageBlock'] = $percentageBlock;
                }else{
				return false;
			}
		}
		}
        
        /**
         * Build marker info window for the ad
        */
        private function GetMarkerInfoWindowContent($markerData)
        {   
		   
            $markerInfoHTML = '';
            $markerInfoWindowWidth = HelloMapsHelper::GetConfiguration('infowindow_width',150);
            if(is_numeric($markerInfoWindowWidth))
                $markerInfoWindowWidth = $markerInfoWindowWidth.'px';
            $markerInfoWindowHeight = HelloMapsHelper::GetConfiguration('infowindow_height',150);
            if(is_numeric($markerInfoWindowHeight))
                $markerInfoWindowHeight = $markerInfoWindowHeight.'px';
            ob_start();
			include(JPATH_ROOT.'/plugins/hellomaps/easysocialevents/views/marker_info_window.php');
			$markerInfoHTML = ob_get_contents();
			ob_end_clean();
            return $markerInfoHTML;
        }
        
        /**
         * Generate output block for each search result
        */
        private function GetSearchResultHTML($row)
        {
			ob_start();
			include(JPATH_ROOT.'/plugins/hellomaps/easysocialevents/views/search_result.php');
			$searchResultHTML = ob_get_contents();
			ob_end_clean();
			return $searchResultHTML;
        }
		
		/**
         * Community advanced search
        */
        public function getAdvanceSearch($filter = array(), $searchedProfileTypes)
        {
    		$db	= JFactory::getDBO();
			$query = $this->getEasySocialData($filter, $searchedProfileTypes);
    		// execution of master query
    		$db->setQuery($query);
			
    		$result = $db->loadObjectList();
			
			
			return $result;			
        }
		
		private function getEasySocialData($filter = array(), $searchedProfileTypes)
		  {
			  
				$db	= JFactory::getDBO();
				$config		= JFactory::getConfig();
				$query		= '';
				$itemCnt	= 0;
				//for radius
				$post = JRequest::get('post');
				$enable_zoom_counter = $this->params->get('enable_zoom_counter',0);
				$search_enable       = HelloMapsHelper::GetConfiguration('search_enable',0);
            	$enable_radius       = HelloMapsHelper::GetConfiguration('search_enable_radius',0);
				$expired_events	 	 = $this->params->get('exclude_expired_events',0);//Exclude expired events

				$query = ' SELECT t1.*, u.email, u.username, sf.unique_key, b.raw';
				$query .=' FROM '.($db->quoteName('#__social_clusters', 't1'));
				$query .=' LEFT JOIN '.($db->quoteName('#__users', 'u').'ON t1.creator_uid = u.id');
				
				//UNIONE PER SISTEMA MULTIPROFILO DI EASYSOCIAL
				$query .=' LEFT JOIN '.($db->quoteName('#__social_clusters_categories', 'c').'ON t1.category_id = c.id');
				
				//join again to #__social_fields_data to prepare for search filter
				$query .=' LEFT JOIN '.($db->quoteName('#__social_fields_data', 'b').' ON t1.id = b.uid');
				$query .=' INNER JOIN '.($db->quoteName('#__social_fields', 'sf').' ON sf.id = b.field_id ');
				
				//join with meta data for event to #__social_fields_data to prepare for date filter
				$query .=' LEFT JOIN '.($db->quoteName('#__social_events_meta', 'ev').' ON t1.id = ev.cluster_id');
				
				

				//se i filtri sono stati inseriti nel backend come parametri
						if(!empty($filter))
						{
								$filterCnt	= count($filter);
								
								//vERIFICARE LA QUERY DEI FILTRI PASSA ANCORA I FILTRI DI EASYSOCIAL MEMBERS
								
								foreach($filter as $obj)
									{
										
										if($itemCnt <= 0){
												$query .= ' AND ( ';
												$query .= $db->quoteName('sf.unique_key')." = ".$db->quote($obj->field);
												$query .= ' AND ';
												$query .= $db->quoteName('b.raw');
												$query .=' LIKE '.$db->quote('%'.$obj->value.'%');
												$query .= ' ) ';   
										}else{
												$query .= ' OR ( ';
												$query .= $db->quoteName('sf.unique_key')." = ".$db->quote($obj->field);
												$query .= ' AND ';
												$query .= $db->quoteName('b.raw');
												$query .=' LIKE '.$db->quote('%'.$obj->value.'%');
												$query .= ' ) ';  
										}//end if
								 
										if($obj->field == 'username'){
												$query .= ' OR ('.$db->quoteName('u.username').' LIKE '.$db->quote('%'.$obj->value.'%').')';
										}elseif ($obj->field == 'useremail') {
												$query .= ' OR ('.$db->quoteName('u.email').' LIKE '.$db->quote('%'.$obj->value.'%').')';
										}//end if
										
								 
								   $itemCnt++; 
								}//end foreach
						}//end if filter
				
					
				
				$query .=' WHERE u.id IS NOT NULL';
			
				//check for category event, if there is any filter
				if(!empty($searchedProfileTypes))
				{
					$query .= ' AND c.'.$db->quoteName('id').' IN('.implode(',',$searchedProfileTypes).') ';
				}
				
					 if($enable_zoom_counter)
                        {
                            if(isset($post,$post['ne'],$post['ne']['lat'],$post['ne']['lng'],$post['sw'],$post['sw']['lat'],$post['sw']['lng']))
                            {
                             								 
							//CONDIZIONE CHE NON FUNZIONA con la condizione AND -- AND 
                              if ($post['ne']['lng'] > $post['sw']['lng']){
                                  $query .= ' AND ( (t1.longitude >= ' . $db->quote($post['sw']['lng']) . ' OR t1.longitude <= ' . $db->quote($post['ne']['lng']) . ')'.' AND (t1.latitude <= ' . $db->quote($post['ne']['lat']) . ' AND t1.latitude >= ' . $db->quote($post['sw']['lat']) . ') )';
                                }
								
                            }
                        }
                        //apply radius search
                        if($search_enable && $enable_radius && 
						isset($post['easysocialevents']['location'],$post['easysocialevents']['location_lat'],$post['easysocialevents']['location_lng'],$post['easysocialevents']['search_radius'])
                         && 
                         !empty($post['easysocialevents']['location']) && is_numeric($post['easysocialevents']['location_lat']) && is_numeric($post['easysocialevents']['location_lng'])
                         && is_numeric($post['easysocialevents']['search_radius']) && ($post['easysocialevents']['search_radius'] > 0)
                         )
                        {                            
                            $distance_col_expression = "(((acos(sin((".$post['easysocialevents']['location_lat']."*pi()/180)) *
            					sin((t1.latitude * pi()/180))+cos((".$post['easysocialevents']['location_lat']." * pi()/180)) *
            					cos((t1.latitude * pi()/180)) * cos(((".$post['easysocialevents']['location_lng']."- t1.longitude)
            					*pi()/180))))*180/pi())*60*1.1515) 
            					";
                            $query .= ' AND '.$distance_col_expression .' <= '.$post['easysocialevents']['search_radius'];
                        }
                        //added by sam end
			
			//filtro eventi scaduti
			if ($expired_events) 
				$query .=' AND '.$db->quoteName('ev.end').' >=DATE( NOW() ) ';
				//$query .=' AND '.$db->quoteName('ev.end')." >=  DATE('2018-01-30 14:15:55') ";
				
		/*	$query .= ' AND DATE('.$db->quote($post['easysocialevents']['startdate']).')';
			$query .= ' BETWEEN ';
			$query .= ' DATE( '.$db->quoteName('ev.start').' ) AND DATE( '.$db->quoteName('ev.end').' )';
				*/
						
			
			$query .=' GROUP BY ' . $db->quoteName( 't1.id' );		
			
			//echo $query;exit;

    		return $query;	
        }//end function
		
		
		
		private function getEasySocialData_condate($filter = array(), $searchedProfileTypes)
		  {
			  
				$db	= JFactory::getDBO();
				$config		= JFactory::getConfig();
				$query		= '';
				$itemCnt	= 0;
				//for radius
				$post = JRequest::get('post');
				$enable_zoom_counter = $this->params->get('enable_zoom_counter',0);
				$search_enable       = HelloMapsHelper::GetConfiguration('search_enable',0);
            	$enable_radius       = HelloMapsHelper::GetConfiguration('search_enable_radius',0);

				$query = ' SELECT t1.*, u.email, u.username, sf.unique_key, b.raw';
				$query .=' FROM '.($db->quoteName('#__social_clusters', 't1'));
				$query .=' LEFT JOIN '.($db->quoteName('#__users', 'u').'ON t1.creator_uid = u.id');
				
				//UNIONE PER SISTEMA MULTIPROFILO DI EASYSOCIAL
				$query .=' LEFT JOIN '.($db->quoteName('#__social_clusters_categories', 'c').'ON t1.category_id = c.id');
				
				//join again to #__social_fields_data to prepare for search filter
				$query .=' LEFT JOIN '.($db->quoteName('#__social_fields_data', 'b').' ON t1.id = b.uid');
				$query .=' INNER JOIN '.($db->quoteName('#__social_fields', 'sf').' ON sf.id = b.field_id ');
				
				//join with meta data for event to #__social_fields_data to prepare for date filter
				//$query .=' LEFT JOIN '.($db->quoteName('#__social_events_meta', 'ev').' ON t1.id = ev.cluster_id');

				
			$query .=' WHERE u.id IS NOT NULL';
				
			//dove filtrodatainizio <= datafinedb e filtrodatafine deve essere compresso tra dbinizio e dbfine	
			//(date_field BETWEEN '2010-01-30 14:15:55' AND '2010-09-29 10:15:55')
			
			
			// SE DATA FINE NON è COMPILATA
			//if ( $post['easysocialevents']['enddate'] == NULL ) {
				//$query .= ' AND '.$db->quoteName('ev.end');
				//$query .= ' >= ';
				//$query .= $db->quote($post['easysocialevents']['startdate']);
				//	solo se la data di finedb evento
		//	è maggiore uguale del filtro di inizio
		
			//} else {
			
			//dal 2015 al 2016
			
				// SE DATA INIZIO E DATA FINE SONO COMPILATE 
		/*	$query .= ' AND '.$db->quote($post['easysocialevents']['startdate']);
			$query .= " <= ".$db->quoteName('ev.end');
			$query .= ' AND ';
			$query .= ' ( '.$db->quote($post['easysocialevents']['enddate']);
			$query .= ' BETWEEN ';
			$query .= $db->quoteName('ev.start');
			$query .= " AND ".$db->quoteName('ev.end').' ) ';
			
			
			}*/
			
				//se i filtri sono stati inseriti nel backend come parametri
						if(!empty($filter))
						{
								$filterCnt	= count($filter);
								
								//vERIFICARE LA QUERY DEI FILTRI PASSA ANCORA I FILTRI DI EASYSOCIAL MEMBERS
								
								foreach($filter as $obj)
									{
										
										if($itemCnt <= 0){
												$query .= ' AND ( ';
												$query .= $db->quoteName('sf.unique_key')." = ".$db->quote($obj->field);
												$query .= ' AND ';
												$query .= $db->quoteName('b.raw');
												$query .=' LIKE '.$db->quote('%'.$obj->value.'%');
												$query .= ' ) ';   
										}else{
												$query .= ' OR ( ';
												$query .= $db->quoteName('sf.unique_key')." = ".$db->quote($obj->field);
												$query .= ' AND ';
												$query .= $db->quoteName('b.raw');
												$query .=' LIKE '.$db->quote('%'.$obj->value.'%');
												$query .= ' ) ';  
										}//end if
								 
										if($obj->field == 'username'){
												$query .= ' OR ('.$db->quoteName('u.username').' LIKE '.$db->quote('%'.$obj->value.'%').')';
										}elseif ($obj->field == 'useremail') {
												$query .= ' OR ('.$db->quoteName('u.email').' LIKE '.$db->quote('%'.$obj->value.'%').')';
										}//end if
										
								 
								   $itemCnt++; 
								}//end foreach
						}//end if filter
				
				//check for category event, if there is any filter
				if(!empty($searchedProfileTypes))
				{
					$query .= ' AND c.'.$db->quoteName('id').' IN('.implode(',',$searchedProfileTypes).') ';
				}
				
						 if($enable_zoom_counter)
                        {
                            if(isset($post,$post['ne'],$post['ne']['lat'],$post['ne']['lng'],$post['sw'],$post['sw']['lat'],$post['sw']['lng']))
                            {
                             								 
							//CONDIZIONE CHE NON FUNZIONA con la condizione AND -- AND 
                              if ($post['ne']['lng'] > $post['sw']['lng']){
                                  $query .= ' AND ( (t1.longitude >= ' . $db->quote($post['sw']['lng']) . ' OR t1.longitude <= ' . $db->quote($post['ne']['lng']) . ')'.' AND (t1.latitude <= ' . $db->quote($post['ne']['lat']) . ' AND t1.latitude >= ' . $db->quote($post['sw']['lat']) . ') )';
                                }
								
                            }
                        }
                        //apply radius search
                        if($search_enable && $enable_radius && 
						isset($post['easysocialevents']['location'],$post['easysocialevents']['location_lat'],$post['easysocialevents']['location_lng'],$post['easysocialevents']['search_radius'])
                         && 
                         !empty($post['easysocialevents']['location']) && is_numeric($post['easysocialevents']['location_lat']) && is_numeric($post['easysocialevents']['location_lng'])
                         && is_numeric($post['easysocialevents']['search_radius']) && ($post['easysocialevents']['search_radius'] > 0)
                         )
                        {                            
                            $distance_col_expression = "(((acos(sin((".$post['easysocialevents']['location_lat']."*pi()/180)) *
            					sin((t1.latitude * pi()/180))+cos((".$post['easysocialevents']['location_lat']." * pi()/180)) *
            					cos((t1.latitude * pi()/180)) * cos(((".$post['easysocialevents']['location_lng']."- t1.longitude)
            					*pi()/180))))*180/pi())*60*1.1515) 
            					";
                            $query .= ' AND '.$distance_col_expression .' <= '.$post['easysocialevents']['search_radius'];
                        }
                        //added by sam end
						
						
			
			$query .=' GROUP BY ' . $db->quoteName( 't1.id' );		
			
			//echo $query;exit;

    		return $query;	
        }//end function
		
		
        /**
         * Get community members count, without filter
        */
        private function GetEasysocialEventsTotal()
        {
            $db	= JFactory::getDBO();
            $query = $db->getQuery(true);
			
			$query ="SELECT COUNT(*) AS total"
			." FROM #__social_clusters a "
			." WHERE a.cluster_type = 'event' "
			.' AND ' . $db->quoteName( 'state' ) . '=' . $db->Quote( 1 )
			.' AND ' . $db->quoteName( 'latitude' ) . '!=255';
		
            $db->setQuery($query);
            $db->query();
            return (int) $db->loadResult();
        }
        
        /**
         * Get adsmanager ads count, without filter, who has lat long
        */
       public function OnGlobalResultCountPrepare(&$globalResultCount)
        {
			if(!$this->easysocialeventsInstalled)
                return false;
            
            $app = JFactory::getApplication();
            $db	= JFactory::getDBO();            
            $query = $db->getQuery(true);
          
		    $query ="SELECT COUNT(*) AS total"
			." FROM #__social_clusters a "
			." WHERE a.cluster_type = 'event' "
			.' AND ' . $db->quoteName( 'state' ) . '=' . $db->Quote( 1 )
			.' AND ' . $db->quoteName( 'latitude' ) . '!=255';
						
            $db->setQuery($query);
            $db->query();
            $total = (int) $db->loadResult();
            if($app->isAdmin())//show dashboard icons at backend
            {
                $globalResultCount = $globalResultCount + $total;
                $document = JFactory::getDocument();
                $document->addStyleSheet(JURI::root().'plugins/hellomaps/easysocialevents/css/backend_style.css');
                $percentageBlock = "<div class='dashboardPluginEasySocial easysocialeventsDashboardPlugin'> 
                                       <div clas='icon_and_count'>
                                           <div class='easysocialeventsIcon'><img src='".JURI::root().'plugins/hellomaps/easysocialevents/images/dashboard_icon.png'."'/></div>                                       
                                           <div class='markerCount'>
                                            ".number_format($total,0,'',',')."
                                           </div>
                                       </div>
                                       <div class='dashboard_titleEasySocial'>".$this->params->get('tab_title','Ads')."</div>
                                    </div>";
                echo $percentageBlock;    
            }
        }
        
        
        /**
         * set the notice status if exists
         * It will be needed to print the notice box holder or not
        */
        public function onNoticeCheck(&$pluginNoticeExist)
        {
			if(!$this->easysocialeventsInstalled) return false;
            global $allowedPluginsInModule;
            if(!isset($allowedPluginsInModule) || in_array($this->filter_id,$allowedPluginsInModule))
            {
                $show_notice_area = (boolean)$this->params->get('show_notice_area',0);
                $notice_area_text = $this->params->get('notice_area_text','');
                $notice_type = HelloMapsHelper::GetConfiguration('notice_type','global');
                if(($notice_type == "by_plugins" && $show_notice_area && $notice_area_text != ""))
                {
                    $pluginNoticeExist = $pluginNoticeExist || 1;    
                }   
            }       
        }
        
        /**
         * print the notice text if enabled
        */
        public function onNoticeAreaDisplay()
        {
            if(!$this->easysocialeventsInstalled)
                return false;
            global $allowedPluginsInModule;
            if(!isset($allowedPluginsInModule) || in_array($this->filter_id,$allowedPluginsInModule))
            {
                $show_notice_area = (boolean)$this->params->get('show_notice_area',0);
                $notice_area_text = $this->params->get('notice_area_text','');
                $notice_type = HelloMapsHelper::GetConfiguration('notice_type','global');
                if($notice_type == "by_plugins" && $show_notice_area && $notice_area_text != "")
                {
                    $sidebar_width = HelloMapsHelper::GetConfiguration('sidebar_width','20');
                    $notice_position = HelloMapsHelper::GetConfiguration('notice_position','left');
                    $infolink_enable = HelloMapsHelper::GetConfiguration('infolink_enable',0);
                    $infoLinkClass = " no_info_link";                
                    if($infolink_enable)
                    {
                        $infoLinkClass = " yes_info_link";
                    }
                ?>
                    <div id="notice_box_holder_easysocialevents_plugin" class="notice_box_holder notice_box_holder_plugin noticePositions<?php echo $notice_position.$infoLinkClass; ?>" style="display:none;width:<?php echo $sidebar_width; ?>px;">
                        <a class="notice_close plugin_notice_close_button" href="javascript:void(0);">X</a>                    
                        <div class="notice_box_container_plugin">
                            <div class="noticeBlock plugin">
                                <div class="plugnNotice easysocialeventsNotice">
                                    <div class="notice_plugin_header easysocialevents_notice_header"><?php echo JText::_('PLG_HELLOMAPS_EASYSOCIALEVENTS_NOTICE_HEADER_TEXT'); ?></div>
                                    <div class="plugin_notice_content easysocialevents_notice_content">
                                        <?php echo $notice_area_text; ?>
                                    </div>
                                </div>
                            </div>
                        </div>                         
                    </div>
                <?php    
                }    
            }                        
        }
        /**
         * Tell the gmap the zoom counter status          
        */
        public function onGmapZoomCounterStatusCheck(&$pluginsZoomCounterStatus)
        {
			if(!$this->easysocialeventsInstalled)
                return false;
            global $allowedPluginsInModule;
            if(!isset($allowedPluginsInModule) || in_array($this->filter_id,$allowedPluginsInModule))
            {
                $pluginsZoomCounterStatus[$this->filter_id] = $this->params->get('enable_zoom_counter',0);    
            }
        }
        /**
         * will be called while listing plugins at module backend
        */
        public function onPluginListingAtModuleBackend(&$hellomapPluginsEnabled)
        {
			if(!$this->easysocialeventsInstalled)
                return false;
           $hellomapPluginsEnabled[$this->filter_id] = $this->params->get('tab_title','EasySocial');
        }
	}
}