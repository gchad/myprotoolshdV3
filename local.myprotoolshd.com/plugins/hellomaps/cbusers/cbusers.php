<?php
/**
 * @version     1.0.7
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
 * It is really important when you use this plugin inside module. tabs will be shown if the plugin is selected at backend, otherwise no action will be takn
 * It is not important for component and will be null
*/

if(!class_exists('plgHellomapsCbusers'))
{
	class plgHellomapsCbusers extends JPlugin
	{
		var $name		= 'Cbusers';
		private $filter_id  = 'cbusers';//Must be unqiue for each plugin, we will trigger javascript functions by this
        private $cbusersInstalled = false;
        
	
	    function plgHellomapsCbusers(& $subject, $config)
	    {		
			parent::__construct($subject, $config);
			//load language file
			$language = JFactory::getLanguage();
			$language->load('plg_hellomaps_cbusers', JPATH_ADMINISTRATOR, $language->getName(), true);			
			$this->cbusersInstalled = $this->isCBusersInstalled();
        	
	    }
		
		public function onFilterListPrepare(&$filters) {  
		     if(!$this->cbusersInstalled)
                return false;  
		    global $allowedPluginsInModule;
            if(isset($allowedPluginsInModule) && in_array($this->filter_id,$allowedPluginsInModule)== false)
            {
                return false;
            }
			$document = JFactory::getDocument();
			$tabTitle = $this->params->get('tab_title','Cbusers');
            $marker_width = (int)$this->params->get('marker_icon_width',45);
            $marker_height = (int)$this->params->get('marker_icon_height',45);
			$show_in_sidebar = (int)$this->params->get('show_in_sidebar',1);
			$enable_cbusers_detail_sidebar = (int)$this->params->get('enable_cbusers_detail_sidebar',1);
			$filterElementsHTML = $this->GetFilterElements();
			$filters[] = array('title'=>$tabTitle,'filter_id'=>$this->filter_id,'content'=>$filterElementsHTML,'show_in_sidebar'=>$show_in_sidebar);
			JHtml::_('bootstrap.framework');//load bootstrap framework of joomla	
            $document->addCustomTag('<link rel="stylesheet" href="'.JURI::root().'plugins/hellomaps/cbusers/css/style.css'.'" type="text/css" />');//to add css at last
            $document->addScriptDeclaration("var cbusers_marker_type='".$this->params->get('marker_icon','avatar')."';\n
                                            var cbusers_marker_width=".$marker_width.";\n
                                            var cbusers_marker_height=".$marker_height.";\n
											var cbusers_show_in_sidebar=".$show_in_sidebar.";\n    
                                            var enable_cbusers_detail_sidebar=".$enable_cbusers_detail_sidebar.";\n                                             
                                            ");            
			$document->addScript(JURI::root().'plugins/hellomaps/cbusers/js/script.js');				
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
				$profileTypes = $this->getProfileTypeCbusers();		
			}
			
			ob_start();
			include(JPATH_ROOT.'/plugins/hellomaps/cbusers/views/filter.php');
			$filterElementsHTML = ob_get_contents();
			ob_end_clean();
			return $filterElementsHTML;
		}
		
		public function getCustomFieldLabelCbusers($fieldCodes) 
		{
			
						$db = JFactory::getDBO();
						$fieldsLabels = array();
						$totalFields = count($fieldCodes);
						$query = ' SHOW COLUMNS ';
						$query .=' FROM '.($db->quoteName('#__comprofiler'));
						
					
						for($i=0; $i < $totalFields; $i++)
						{
							
							$db->setQuery($query);
							$db->query();
							$fieldLabel = $db->loadObjectList();

							if($fieldLabel != "")
								$fieldsLabels[$i] = array('label'=>$fieldCodes[$i]);
						}
						return $fieldsLabels;	
						
		}
		
		private function GetCbusersCategories()
		{
			$db     = JFactory::getDBO();
			$query	= 'SELECT * FROM ' . $db->quoteName( '#__usergroups' ) . ' ';
			$query	.= 'WHERE ' . $db->quoteName( 'extension' ) . '='.$db->quote( 'com_comprofiler');
			$query	.= 'AND ' . $db->quoteName( 'published' ) . '= 1';
			$db->setQuery($query);
			return $db->loadObjectList();
		}
		
		public function getCustomFieldRAWCbusers ($userid, $fieldcode) 
		{
						$db	= JFactory::getDBO();
						$filter = "a.".$fieldcode['label'];
						$query = " select ".$filter." from #__comprofiler as a ";
						$query .= " where a.user_id = " . $db->Quote($userid);
						$db->setQuery($query);
						$result = $db->loadResult();	
						
						return $result;
						
		}
		
		public function getProfileTypeCbusers(){
			
			$db		= JFactory::getDBO();					
								
			$query	= 'SELECT title AS name, id AS id FROM ' . $db->quoteName( '#__usergroups' ) . ' '
					. 'ORDER BY ' . $db->quoteName( 'id' );
					
			$db->setQuery( $query );
			$items = $db->loadObjectList();
			
			return $items;
		}
		
		public function getProfileTypeIDCbusers($userid) 
		{
			
						$db		= JFactory::getDBO();
						
						$query = " select b.group_id from #__comprofiler as a";
						$query .= " inner join #__user_usergroup_map as b";
						$query .= " on a.user_id = b.user_id";
						$query .= " inner join #__usergroups as c";
						$query .= " on c.id = b.group_id";
						$query .= " where a.user_id = " . $db->Quote($userid);
					
						$db->setQuery($query);
						$result = $db->loadResult();	
						return $result;
						
		}
		
		
		public function getProfileTypeCbusersbyuser($userid) 
		{
			
						$db		= JFactory::getDBO();
						
						$query = " select c.title, b.group_id from #__comprofiler as a";
						$query .= " inner join #__user_usergroup_map as b";
						$query .= " on a.user_id = b.user_id";
						$query .= " inner join #__usergroups as c";
						$query .= " on c.id = b.group_id";
						$query .= " where a.user_id = " . $db->Quote($userid);
						
					
						$db->setQuery($query);
						$result = $db->loadResult();
						return $result;
						
		}

		/**
		 * [onHellomapSearch description]
		 * @param  [type] $litsenerName [only search when litsener name is same as the plugins filter_id]
		 * @param  [type] $searchData   [description]
		 * @return [type]               [description]
		 */
		public function onHellomapSearch($litsenerName,$searchParam,&$searchResult)
		{
			$db = JFactory::getDBO();  
		    if(!$this->cbusersInstalled)
                return false; 
			if($litsenerName == $this->filter_id)
			{
				$show_search = $this->params->get('show_search',1);//show searchbox
				$show_filters = $this->params->get('show_filters',1);//show filters
				$search_fields = $this->params->get('search_fields',array());//show filters
			    $fitler_profile_types = $this->params->get('fitler_profile_types',array());//show filters
                $details_extra_fields = $this->params->get('details_extra_fields',array());//extra fields    
				$details_extra_fields_labels = $this->getCustomFieldLabelCbusers($details_extra_fields);            
                $marker_icon        = $this->params->get('marker_icon','avatar');
                $custom_marker_image= $this->params->get('custom_marker_image','');
                $markers_name         = $this->params->get('markers_name',''); //json data for each category                
                $display_marker_infowindow = (boolean)$this->params->get('display_marker_infowindow',1) && HelloMapsHelper::GetConfiguration('infowindow_enable',1); //json data for each category
                $default_marker_icon_url = JURI::root().'plugins/hellomaps/cbusers/images/markers/info.png';
                
                
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
                if(!empty($searchParam))
                {
					$cbUsersFilters = array();//array of objects
					if(!empty($search_fields))
					{
						foreach ($search_fields as $searchFieldName) {
							$cbUsersFilter = new stdClass;
							$cbUsersFilter->field = $searchFieldName;
							$cbUsersFilter->condition = 'contain';
							$cbUsersFilter->fieldType = 'text';//get from db
							$cbUsersFilter->value = $search_text;
							$cbUsersFilters[] = $cbUsersFilter;
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
					$results = $this->getAdvanceSearch($cbUsersFilters,$searchedProfileTypes);
					
					$i=0;
                    if(!empty($results))
                    {
                        foreach($results as $key=>$result )
                        {
							 if($key == 0){
                                $query = $db->getQuery(true);
							 }
                            else
								
                                $query->clear();
								$query->select('*');
								$query->from($db->quoteName('#__comprofiler'));
								$query->where($db->quoteName('user_id').'='.$result->id);
								$query->where($db->quoteName('geoLat').'!=255.000000 AND '.$db->quoteName('geoLng').'!=255.000000');
								$query->where($db->quoteName('approved').' = '.$db->Quote('1'));
								
								$db->setQuery($query);
								$db->query();
								
								
								$cbusersUserInfo = $db->loadAssoc();
								//
								if(empty($cbusersUserInfo))
								{
									unset($results[$key]);
									continue;
								}	
							//print_r ($result);
							$row       = array(); 
							$row['id'] = $result->user_id;
                           	$row['title'] = $result->username;
							//SEF
							
                           if ($result->avatar != null){
							$row['largeAvatar'] = JURI::root().'/images/comprofiler/'.$result->avatar;
							$row['thumb'] = JURI::root().'/images/comprofiler/'.$result->avatar;
							}else{
							$row['largeAvatar'] = JURI::root().'plugins/hellomaps/cbusers/images/no-image.png';
							$row['thumb'] = $result->thumb;
							}
							
							$row['latitude'] = (float)$result->geoLat; 
                            $row['longitude'] = (float)$result->geoLng;		
							$row['country'] = $result->cb_country;
							$row['state'] = $result->cb_state;
							$row['city'] = $result->cb_city;
							

							$row['profileTypeName'] = $this->getProfileTypeCbusersbyuser($result->user_id);
							$profileTypeId = $this->getProfileTypeIDCbusers($result->user_id);
							$row['eventLink'] = JRoute::_('index.php?option=com_comprofiler&task=userprofile&user=' . $result->user_id);
							
							

						 if(!empty($details_extra_fields_labels))
                            {
                                $extraFieldsValues = array();
                                foreach($details_extra_fields_labels as $field_code=>$fieldData)
                                {

									$fieldData['value'] = $this->getCustomFieldRAWCbusers($result->user_id, $fieldData);//get custom field value
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
								 if ($row['thumb'] != null){
										$marker_icon_url = $row['thumb'];
										}else{
										$marker_icon_url = $default_marker_icon_url;
										}
                            }
			
                            else if($marker_icon == 'cbusersprofile-type' && isset($profileTypeMarkers[$profileTypeId]))
                            {
								
                                $marker_icon_path = JPATH_SITE.'/plugins/hellomaps/cbusers/images/markers/'.$profileTypeMarkers[$profileTypeId];
								
                                if(is_file($marker_icon_path))
                                    $marker_icon_url = JURI::base().'/plugins/hellomaps/cbusers/images/markers/'.$profileTypeMarkers[$profileTypeId];
                            }
                            else if($marker_icon == 'custom' && $custom_marker_image != "")
                            {
                                $marker_icon_url = $custom_marker_image;
                            }
							
							$location = array();
                            if(!empty($row['country']))
                            {
                                $location[] = $row['country'];
                            }
                            if(!empty($row['state']))
                            {
                                $location[] = $row['state'];
                            }
							 if(!empty($row['city']))
                            {
                                $location[] = $row['city'];
                            }
                            $location = implode(' / ',$location);
                            $row['location'] = $location;
           
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
                $display_marker_result_count        = HelloMapsHelper::GetConfiguration('results_enable',0) && (boolean)$this->params->get('display_marker_result_count',0);
                $display_marker_result_count        = $display_marker_result_count && (HelloMapsHelper::GetConfiguration('results_type','byzoom') == 'byzoom');       
                $searchResult[$this->filter_id] = array('total'=>$total,'rows'=>$rows,'display_marker_result_count'=>$display_marker_result_count);

                if($display_marker_result_count)
                {
                     $memberCountWithoutFilter = $this->GetCbUsersTotal();
                    $percentage = 0;
                    if($memberCountWithoutFilter > 0)
                    {
                        $percentage = ceil(($total / $memberCountWithoutFilter) * 100);    
                    }    
                    $percentageBlock = "<div class='percentageBlock cbusersTotalBlock'>
                                           <span class='icon'></span>".JText::sprintf('PLG_HELLOMAPS_CBUSERS_TOTAL_LABEL',$percentage)."
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
			include(JPATH_ROOT.'/plugins/hellomaps/cbusers/views/marker_info_window.php');
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
			include(JPATH_ROOT.'/plugins/hellomaps/cbusers/views/search_result.php');
			$searchResultHTML = ob_get_contents();
			ob_end_clean();
			return $searchResultHTML;
        }
		
		/**
         * Community advanced search
        */
        public function getAdvanceSearch($filter = array(), $searchedProfileTypes = array())
        {
    		$db	= JFactory::getDBO();
			$query = $this->getCbUsersData($filter, $searchedProfileTypes);
    		// execution of master query
    		$db->setQuery($query);
			
    		$result = $db->loadObjectList();
			
		
			return $result;			
        }
		 private function getCbUsersData($filter = array(), $searchedProfileTypes)
		  {
            		
				$db	= JFactory::getDBO();
				$config		= JFactory::getConfig();
				$query		= '';
				$itemCnt	= 0;
				$post = JRequest::get('post');
				$enable_zoom_counter = $this->params->get('enable_zoom_counter',0);
				$search_enable       = HelloMapsHelper::GetConfiguration('search_enable',0);
            	$enable_radius       = HelloMapsHelper::GetConfiguration('search_enable_radius',0);
				
		
    		if(!empty($filter))
    		{
				
    				$filterCnt	= count($filter);
    			 					
					$query = ' SELECT a.*, u.*, b.group_id ';
					$query .=' FROM '.($db->quoteName('#__comprofiler', 'a'));
					$query .=' INNER JOIN '.($db->quoteName('#__users', 'u').'ON a.user_id = u.id');
					$query .=' INNER JOIN '.($db->quoteName('#__user_usergroup_map', 'b').'ON a.user_id = b.user_id');
					$query .=' INNER JOIN '.($db->quoteName('#__usergroups', 'c').'ON b.group_id = c.id');
					$query .=' AND a.geoLat !=255.000000 AND a.geoLng !=255.000000';
											
					
					 //check for category event type, if there is any filter
					 if(!empty($searchedProfileTypes))
                        {
                            $query .= ' AND b.'.$db->quoteName('group_id').' IN('.implode(',',$searchedProfileTypes).') ';
                        }
					 
					 if($enable_zoom_counter)
                        {
                            if(isset($post,$post['ne'],$post['ne']['lat'],$post['ne']['lng'],$post['sw'],$post['sw']['lat'],$post['sw']['lng']))
                            {
                                /**
                    			 * We need to take in account the meridian in the Mercator
                    			 * projection of the map. In the Mercator projection the meridian of the earth
                    			 * is at the left and right edges. When you slide to the left the
                    			 * or right, the map will wrap as you move past the meridian
                    			 * at +/- 180 degrees. In that case, the bounds are partially split
                    			 * across the left and right edges of the map and the northeast
                    			 * corner is actually positioned at a poin that is greater than 180 degree.
                    			 * The gmaps API automatiacally adjusts the longitude values to fit
                    			 * between -180 and +180 degrees so we ned to request 2 portions of the map
                    			 * from our database convering the left and right sides.
                    			 */
                                if ($post['ne']['lng'] > $post['sw']['lng'])
                                {
                                    $query .= ' AND ( (a.geoLng > ' . $db->quote($post['sw']['lng']) . ' AND a.geoLng < ' . $db->quote($post['ne']['lng']) . ')'.
                                                 ' AND (a.geoLat <= ' . $db->quote($post['ne']['lat']) . ' AND a.geoLat >= ' . $db->quote($post['sw']['lat']) . ') )'
                                            ;                        
                                }
                                else
                                {
                                    $query .= ' AND ( (a.geoLng >= ' . $db->quote($post['sw']['lng']) . ' OR a.geoLng <= ' . $db->quote($post['ne']['lng']) . ')'.
                                               ' AND (a.geoLat <= ' . $db->quote($post['ne']['lat']) . ' AND a.geoLat >= ' . $db->quote($post['sw']['lat']) . ') )'
                                            ;
                                }
                            }
                        }
						
						//apply radius search
                        if($search_enable && $enable_radius && isset($post['cbusers']['location'],$post['cbusers']['location_lat'],$post['cbusers']['location_lng'],$post['cbusers']['search_radius'])
                         && 
                         !empty($post['cbusers']['location']) && is_numeric($post['cbusers']['location_lat']) && is_numeric($post['cbusers']['location_lng'])
                         && is_numeric($post['cbusers']['search_radius']) && ($post['cbusers']['search_radius'] > 0)
                         )
                        {                            
                            // in Miles
                            //to convert into KM please use 60*1.1515*1.609344
                            //to convert into Mile please use 60*1.1515
                            //1 Mile = 1.609344 KM
                            $distance_col_expression = "(((acos(sin((".$post['cbusers']['location_lat']."*pi()/180)) *
            					sin((a.geoLat * pi()/180))+cos((".$post['cbusers']['location_lat']." * pi()/180)) *
            					cos((a.geoLat * pi()/180)) * cos(((".$post['cbusers']['location_lng']."- a.geoLng)
            					*pi()/180))))*180/pi())*60*1.1515) 
            					";
                            $query .= ' AND '.$distance_col_expression .' <= '.$post['cbusers']['search_radius'];
                            //$distance_col_sql = ", $distance_col_expression as distance ";
                        }
                        //added by sam end
					
						$query .=' WHERE a.approved = 1';
						
					foreach($filter as $obj)
						{
					 		if($itemCnt <= 0){
								
									$query .= ' AND ( ';
									$query .= $db->quoteName($obj->field);
									$query .=' LIKE '.$db->quote('%'.$obj->value.'%');
									$query .= ' ) ';   
					 		}else {
									$query .= ' OR ( ';
									$query .= $db->quoteName($obj->field);
									$query .=' LIKE '.$db->quote('%'.$obj->value.'%');
									$query .= ' ) ';
					   		}//end if
					 
					 		if($obj->field == 'username'){
					   				$query .= ' OR ('.$db->quoteName('u.username').' LIKE '.$db->quote('%'.$obj->value.'%').')';
					  		}elseif ($obj->field == 'email') {
					   				$query .= ' OR ('.$db->quoteName('u.email').' LIKE '.$db->quote('%'.$obj->value.'%').')';
					  		}//end if
					 		
					   $itemCnt++; 
					}//end foreach
					
					
			}//end if
			//echo $query;exit;
			
    		return $query;	
        }//end function
        
        /**
         * Get community members count, without filter
        */
         private function GetCbUsersTotal()
        {
            $db	= JFactory::getDBO();
            $query = $db->getQuery(true);
            $query->select('COUNT(*) AS total');
            $query->from($db->quoteName('#__comprofiler').' a');
			$query->where('a.'.$db->quoteName('approved').' = '.$db->Quote('1'));
            $db->setQuery($query);
            $db->query();
            return (int) $db->loadResult();
        }
        
        /**
         * Get adsmanager ads count, without filter, who has lat long
        */
       public function OnGlobalResultCountPrepare(&$globalResultCount)
        {
			if(!$this->cbusersInstalled)
                return false;
            
            $app = JFactory::getApplication();
            $db	= JFactory::getDBO();            
            $query = $db->getQuery(true);
            $query->select('COUNT(*) AS total');
           	$query->from($db->quoteName('#__comprofiler').' a');
			$query->where($db->quoteName('geoLat').'!=255.000000 AND '.$db->quoteName('geoLng').'!=255.000000');
            $query->where('a.'.$db->quoteName('approved').' = '.$db->Quote('1'));
            $db->setQuery($query);
            $db->query();
            $total = (int) $db->loadResult();
            if($app->isAdmin())//show dashboard icons at backend
            {
                $globalResultCount = $globalResultCount + $total;
                $document = JFactory::getDocument();
                $document->addStyleSheet(JURI::root().'plugins/hellomaps/cbusers/css/backend_style.css');
                $percentageBlock = "<div class='dashboardPluginEasySocial cbusersDashboardPlugin'> 
                                       <div clas='icon_and_count'>
                                           <div class='cbusersIcon'><img src='".JURI::root().'plugins/hellomaps/cbusers/images/dashboard_icon.png'."'/></div>                                       
                                           <div class='markerCount'>
                                            ".number_format($total,0,'',',')."
                                           </div>
                                       </div>
                                       <div class='dashboard_titleEasySocial'>".$this->params->get('tab_title','JomSocial events')."</div>
                                    </div>";
                echo $percentageBlock;    
            }
            else
            {
                $adsCountWithoutFilter = $this->GetCbusersAdsTotal();
                $percentage = 0;
                if($adsCountWithoutFilter > 0)
                {
                    $percentage = ceil(($total / $adsCountWithoutFilter) * 100);    
                }    
                $globalResultCount = $globalResultCount + $total;
                $percentageBlock = "<div class='percentageBlock adsTotalBlock'>
                                       <span class='icon'></span>".JText::sprintf('PLG_HELLOMAPS_CBUSERS_TOTAL_LABEL',$percentage)."
                                       <div class='color'>
                                        ".number_format($total,0,'',',')."
                                       </div>
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
			if(!$this->cbusersInstalled)
                return false;
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
            if(!$this->cbusersInstalled)
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
                    <div id="notice_box_holder_cbusers_plugin" class="notice_box_holder notice_box_holder_plugin noticePositions<?php echo $notice_position.$infoLinkClass; ?>" style="display:none;width:<?php echo $sidebar_width; ?>px;">
                        <a class="notice_close plugin_notice_close_button" href="javascript:void(0);">X</a>                    
                        <div class="notice_box_container_plugin">
                            <div class="noticeBlock plugin">
                                <div class="plugnNotice cbusersNotice">
                                    <div class="notice_plugin_header cbusers_notice_header"><?php echo JText::_('PLG_HELLOMAPS_CBUSERS_NOTICE_HEADER_TEXT'); ?></div>
                                    <div class="plugin_notice_content cbusers_notice_content">
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
			if(!$this->cbusersInstalled)
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
			if(!$this->cbusersInstalled)
                return false;
           $hellomapPluginsEnabled[$this->filter_id] = $this->params->get('tab_title','CB Users');
        }
		
		 private function isCBusersInstalled()
        {
            $db		= JFactory::getDBO();
            $sql = 'SELECT COUNT(*) AS total FROM #__extensions WHERE element="com_comprofiler" AND enabled=1';
            $db->setQuery( $sql );
            $db->query();
            return ($db->loadResult() == 1);
        }
	}
}