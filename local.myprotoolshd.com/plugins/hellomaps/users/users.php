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
if(!class_exists('plgHellomapsUsers'))
{
	class plgHellomapsUsers extends JPlugin
	{
		var $name		= 'Users';
		private $filter_id  = 'users';//Must be unqiue for each plugin, we will trigger javascript functions by this
        //private $usersInstalled = false;
        
	
	    function plgHellomapsUsers(& $subject, $config)
	    {		
			parent::__construct($subject, $config);
			//load language file
			$language = JFactory::getLanguage();
			$language->load('plg_hellomaps_users', JPATH_ADMINISTRATOR, $language->getName(), true);
			//Load Custom File language
            //$language->load( 'com_mycom', JPATH_SITE, $language->getName(), true);
        
	    }
		
		public function onFilterListPrepare(&$filters) {  
		    global $allowedPluginsInModule;
            if(isset($allowedPluginsInModule) && in_array($this->filter_id,$allowedPluginsInModule)== false)
            {
                return false;
            }
			$document = JFactory::getDocument();
			$tabTitle = $this->params->get('tab_title','Users');
            $marker_width = (int)$this->params->get('marker_icon_width',45);
            $marker_height = (int)$this->params->get('marker_icon_height',45);
			$show_in_sidebar = (int)$this->params->get('show_in_sidebar',1);
			$enable_user_detail_sidebar = (int)$this->params->get('enable_user_detail_sidebar',1);
			$filterElementsHTML = $this->GetFilterElements();
			$filters[] = array('title'=>$tabTitle,'filter_id'=>$this->filter_id,'content'=>$filterElementsHTML,'show_in_sidebar'=>$show_in_sidebar);
			JHtml::_('bootstrap.framework');//load bootstrap framework of joomla	
            $document->addCustomTag('<link rel="stylesheet" href="'.JURI::root().'plugins/hellomaps/users/css/style.css'.'" type="text/css" />');//to add css at last
            $document->addScriptDeclaration("var users_marker_type='".$this->params->get('marker_icon','art')."';\n
                                            var users_marker_width=".$marker_width.";\n
                                            var users_marker_height=".$marker_height.";\n   
											var users_show_in_sidebar=".$show_in_sidebar.";\n    
                                            var enable_user_detail_sidebar=".$enable_user_detail_sidebar.";\n
                                            ");            
			$document->addScript(JURI::root().'plugins/hellomaps/users/js/script.js');				
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
			$filters_categories = $this->params->get('filters_categories',array());//Allowed categories
			$usersCategories = $this->GetJoomlaUserGroups();			
            $search_enable = HelloMapsHelper::GetConfiguration('search_enable',1);//from component
            $search_enable_radius = HelloMapsHelper::GetConfiguration('search_enable_radius',1);//from component
            $contents_enable      = HelloMapsHelper::GetConfiguration('contents_enable',0);//to put the result html in the sidebar...
			
			ob_start();
			include(JPATH_ROOT.'/plugins/hellomaps/users/views/filter.php');
			$filterElementsHTML = ob_get_contents();
			ob_end_clean();
			return $filterElementsHTML;
		}
		
		private function GetJoomlaUserGroups()
		{
			$db     = JFactory::getDBO();
			$query  = $db->getQuery(true);
			$query->select('id, title');
			$query->from('#__usergroups');
			$query->order('id');			
			$db->setQuery($query);
			return $db->loadObjectList();
		}
		
		private function GetUsersProfileValue($user)
		{
			
			$db     = JFactory::getDBO();
			$query  = $db->getQuery(true);
			$query->select('profile_key, profile_value');
			$query->from($db->quoteName('#__user_profiles'));
			$query->where($db->quoteName('user_id').'='.(int)$user ." AND profile_key LIKE 'hellomapsusers.%'");
			$query->order('ordering');						
			$db->setQuery($query);
			$db->query();
			return $db->loadAssocList();
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
			if($litsenerName == $this->filter_id)
			{
				$show_search = $this->params->get('show_search',1);//show searchbox
				$show_filters = $this->params->get('show_filters',1);//show filters
				$search_fields = $this->params->get('search_fields',array());//show filters
                $fitler_categories = $this->params->get('filters_categories',array());//show filters
                $details_extra_fields = $this->params->get('details_extra_fields',array());//extra fields 
                              
                $respect_privacy    = $this->params->get('respect_privacy',0);
                $marker_icon        = $this->params->get('marker_icon','avatar');
                $custom_marker_image= $this->params->get('custom_marker_image','');
                $markers_name         = $this->params->get('markers_name',''); //json data for each category                
                $display_marker_infowindow = (boolean)$this->params->get('display_marker_infowindow',1) && HelloMapsHelper::GetConfiguration('infowindow_enable',1); //json data for each category
                $default_marker_icon_url = JURI::root().'plugins/hellomaps/users/images/no-image.png';
                
                
                $categoryMarkers = array(); 
                if(!empty($markers_name))
                {
                    $markers_name = json_decode($markers_name,true);
                    if(is_array($markers_name))
                    {
                        foreach($markers_name as $marker_name)
                        {
                            $categoryMarkers[$marker_name['categoryID']] = $marker_name['categoryMarkerImage'];
                        }
                    }
                }
                
                $search_text = isset($searchParam['search_text'])?$searchParam['search_text']:"";
                $results = array();  
                $rows = array();         
                //if($show_search && $show_filters && !empty($searchParam))
                if(!empty($searchParam))
                {
                    $usersFilter = array();
                    $usersFilter['publish'] = 1;
                    $usersFilter['search'] = $search_text;
					
					//Check Group Type
					$searchedCategoryIds ="";
                    if(!empty($fitler_categories) && !empty($searchParam['category']) && is_array($searchParam['category']) )
                    {
                        $searchedCategoryIds = array();
                        foreach($searchParam['category'] as $searchedCategoryId)
                        {
                            if(in_array($searchedCategoryId,$fitler_categories)!==false)
                            {
                                $searchedCategoryIds[] = $searchedCategoryId;
                            }
                        }
                        if(!empty($searchedCategoryIds))
                        {
                            $usersFilter['categories'] = $searchedCategoryIds;
                        }
                    }// end group types filter
					
					$JUsersFilters = array();//array of objects
					
					//print_r($search_fields);
					if(!empty($search_fields))
					{
						foreach ($search_fields as $searchFieldName) {
							$JUsersFilter = new stdClass;
							$JUsersFilter->field = $searchFieldName;
							$JUsersFilter->condition = 'contain';
							$JUsersFilter->fieldType = 'text';//get from db
							$JUsersFilter->value = $search_text;
							$JUsersFilters[] = $JUsersFilter;
						}
					}
					$join='or';
					$avatarOnly='';
					$sorting = '';	
					
					$results = $this->getAdvanceSearch($JUsersFilters, $join , $avatarOnly  , $sorting ,$searchedCategoryIds);
					//print_r($results);
                    if(!empty($results))
                    {
						$JoomlaUsersUserInfo[2]['profile_value']='';
                        foreach($results as $key=>$result )
                        {

						if($key == 0)
							$query = $db->getQuery(true);
						else
							$query->clear();
						
						$JoomlaUsersUserInfo = $this->GetUsersProfileValue($result->id);
						
						//risolve offset
						if(empty($JoomlaUsersUserInfo))
						{
							unset($results[$key]);
							continue;
						}
						//Recupero Coordinate Iniziali
						$find[] = '"'; 
						$find[] = '[';
						$find[] = ']';
						$replace[] = '';
					
						if($JoomlaUsersUserInfo[0]['profile_value']!="")
						{
							$coords = $JoomlaUsersUserInfo[0]['profile_value'];
							$coords = str_replace($find, $replace, $coords);
							$coords = explode(",", $coords);
							
							$lat = $JoomlaUsersUserInfo[1]['profile_value'];
							$lat = str_replace($find, $replace, $lat);
							$lng = $JoomlaUsersUserInfo[2]['profile_value'];
							$lng = str_replace($find, $replace, $lng);
							//print_r($coords);
						} 
						$row       = array();
						$row['id'] ='';
						$row['id'] = $result->id;
                        $row['title'] = $result->name;
					
						$row['profileLink']  =  JURI::root().'index.php?option=com_users&view=profile&layout=default&id='.$result->id;
						$row['isOnline']    = '';
						$row['lastLogin']   = JHtml::_('date', $result->lastvisitDate, 'D F n, Y g:i a');
						$row['memberSince']	= JHtml::_('date', $result->registerDate, 'D F n, Y g:i a');
						
						$row['thumb'] 		= str_replace($find, $replace, $JoomlaUsersUserInfo[7]['profile_value']);
						$row['thumb'] 		= str_replace("\\", '', $row['thumb']);
					
						$row['largeAvatar'] = str_replace($find, $replace, $JoomlaUsersUserInfo[7]['profile_value']);
						$row['largeAvatar'] = str_replace("\\", '', $row['largeAvatar']);
						$row['latitude'] 	= (float)$lat; 
						$row['longitude']	= (float)$lng;      
						$row['state']       = str_replace($find, $replace, $JoomlaUsersUserInfo[3]['profile_value']); 
						$row['region']      = str_replace($find, $replace, $JoomlaUsersUserInfo[4]['profile_value']);
						$row['city']        = str_replace($find, $replace, $JoomlaUsersUserInfo[5]['profile_value']);
						$location = array();
						if(!empty($row['state']))   $location[] = $row['state'];
						if(!empty($row['region'])) $location[]  = $row['region'];
						if(!empty($row['city'])) $location[]    = $row['city'];
						$location = implode('/ ',$location);
						$row['location'] = $location;
						$row['postal_code'] = str_replace($find, $replace, $JoomlaUsersUserInfo[7]['profile_value']);
						$row['phone'] 		= str_replace($find, $replace, $JoomlaUsersUserInfo[8]['profile_value']);
						$row['latestStatus']= str_replace($find, $replace, $JoomlaUsersUserInfo[9]['profile_value']);
						$row['qr_code_img'] = 'http://chart.apis.google.com/chart?cht=qr&chs=80x80&chl=geo:'.(float)$coords[0].','.(float)$coords[1];
						
						 //Get Extra Fields Values 
						/*if(!empty($details_extra_fields)) {
							
							$details_extra_fields_vk= array();
							foreach ($result as $key => $fieldData){
								if (in_array($key, $details_extra_fields)) {
								 $details_extra_fields_vk[$key] = $fieldData;
								// print_r($result);
								}
							 }
						}*/
						//$row['extraFields'] = $details_extra_fields_vk;
						$row['extraFields']='';
						//print_r($details_extra_fields_vk);
						
						//prende il nomee id  del gruppo:::da passare dentro funzione
						$user = JFactory::getUser();
						$db     = JFactory::getDBO();
						$query  = $db->getQuery(true);
						$query->select('s.title AS group_name, s.id AS idtype');
						$query->from('#__user_usergroup_map AS c');
						$query->join('LEFT', '#__usergroups AS s ON s.id = c.group_id' );
						$query->where('c.user_id = '.$result->id);
						$db->setQuery($query);
						$res = $db->loadObjectList();	
						//print_r($res);
						
						
						$profileTypeId = $res[0]->idtype; 
						$row['profileTypeId'] = $profileTypeId;
						$row['profileTypeName'] = $res[0]->group_name;     
						$marker_icon_url = $default_marker_icon_url;
						if($marker_icon == 'userimage')
						{
							$marker_icon_url = $row['thumb'];
							
						}
						else if($marker_icon == 'usersprofile-type' && isset($categoryMarkers[$profileTypeId]))
						{
							$marker_icon_path = JPATH_SITE.'/plugins/hellomaps/users/images/markers/'.$categoryMarkers[$profileTypeId];
							if(is_file($marker_icon_path))
								$marker_icon_url = JURI::base().'plugins/hellomaps/users/images/markers/'.$categoryMarkers[$profileTypeId];
						}
						else if($marker_icon == 'custom' && $custom_marker_image != "")
						{
							$marker_icon_url = $custom_marker_image;
						}
						$row['marker_icon_url'] = $marker_icon_url;
						$row['display_marker_infowindow'] = $display_marker_infowindow;

						if($display_marker_infowindow)
						{
							$row['infowindow_content'] = $this->GetMarkerInfoWindowContent($row);
						}               
						$row['html'] = $this->GetSearchResultHTML($row);             
						$rows[] = $row;
							
						} // end foreach						
                    }//end if results
				} // end listener                 
				//}      
                          
                //$total = count($results);
				$total=count($results);
                $display_marker_result_count        = HelloMapsHelper::GetConfiguration('results_enable',0) && (boolean)$this->params->get('display_marker_result_count',0);
                $display_marker_result_count        = $display_marker_result_count && (HelloMapsHelper::GetConfiguration('results_type','byzoom') == 'byzoom');       
                $searchResult[$this->filter_id] = array('total'=>$total,'rows'=>$rows,'display_marker_result_count'=>$display_marker_result_count);

                if($display_marker_result_count)
                {
                    $usrsCountWithoutFilter = $this->GetUsersTotal();
                    $percentage = 0;
                    if($usrsCountWithoutFilter > 0)
                    {
                        $percentage = ceil(($total / $usrsCountWithoutFilter) * 100);    
                    }    
                    $percentageBlock = "<div class='percentageBlock usrsTotalBlock'>
                                           <span class='icon'></span>".JText::sprintf('PLG_HELLOMAPS_USERS_TOTAL_LABEL',$percentage)."
                                           <div class='color'>
                                            ".number_format($total,0,'',',')."
                                           </div>
                                        </div>";
                    $searchResult[$this->filter_id]['percentageBlock'] = $percentageBlock;
                }
			}
			else
			{
				return false;
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
			include(JPATH_ROOT.'/plugins/hellomaps/users/views/marker_info_window.php');
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
			include(JPATH_ROOT.'/plugins/hellomaps/users/views/search_result.php');
			$searchResultHTML = ob_get_contents();
			ob_end_clean();
			return $searchResultHTML;
        }
       
	   	 private function _getResults($filter = array(), $join='and' , $avatarOnly = '',  $searchedProfileTypes=array())
        {
          
			$db = JFactory::getDBO();
			$post = JRequest::get('post');
			//print_r($post); exit;
            $enable_zoom_counter = $this->params->get('enable_zoom_counter',0);
			$search_fields = $this->params->get('search_fields',array());//show filters
			$query  = '';
			$itemCnt = 0;
			
			if(!empty($filter))
            {
                	$filterCnt    = count($filter);
					
					$query .=' SELECT u.*,p.* ';
					$query .=' FROM '.($db->quoteName('#__users', 'u'));	
    				$query .=' INNER JOIN '.$db->quoteName('#__user_profiles', 'p').' ON u.'.$db->quoteName('id').' = p.'.$db->quoteName('user_id');
					
					//check for profile type, if there is any filter
                    if(!empty($searchedProfileTypes))
                    {
                     // If group is supplied, we only want to fetch users from a particular group type
					$query .=' INNER JOIN '.$db->quoteName('#__user_usergroup_map', 'c').' ON u.'.$db->quoteName('id').' = c.'.$db->quoteName('user_id');
					$query .= ' AND c.'.$db->quoteName('group_id').' IN ('.implode(',',$searchedProfileTypes).')';
                    }
					
					
					$query .=' INNER JOIN '.$db->quoteName('#__user_profiles', 'l').' ON u.'.$db->quoteName('id').' = l.'.$db->quoteName('user_id');
					$query .= ' OR '.$db->quoteName('p.profile_key').' = '.$db->quote('hellomapsusers.mappajf');
					
					/*//Zoom controls
					if($enable_zoom_counter)
                        {
                            if(isset($post,$post['ne'],$post['ne']['lat'],$post['ne']['lng'],$post['sw'],$post['sw']['lat'],$post['sw']['lng']))
                            {
                               
                                if ($post['ne']['lng'] > $post['sw']['lng'])
                                {
                                    $query .= ' AND ( (b.longitude > ' . $db->quote($post['sw']['lng']) . ' AND b.longitude < ' . $db->quote($post['ne']['lng']) . ')'.
                                                 ' AND (b.latitude <= ' . $db->quote($post['ne']['lat']) . ' AND b.latitude >= ' . $db->quote($post['sw']['lat']) . ') )'
                                            ;                        
                                }
                                else
                                {
                                    $query .= ' AND ( (b.longitude >= ' . $db->quote($post['sw']['lng']) . ' OR b.longitude <= ' . $db->quote($post['ne']['lng']) . ')'.
                                               ' AND (b.latitude <= ' . $db->quote($post['ne']['lat']) . ' AND b.latitude >= ' . $db->quote($post['sw']['lat']) . ') )'
                                            ;
                                }
                            }
                        }
                        //end zoom controls*/

					//trick to add join where clause on foreach
					$query .=' WHERE u.'.$db->quoteName('id').' IS NOT NULL';
					foreach($filter as $obj)
                    {
							if($itemCnt <= 0){
									$query .= ' AND ( ';
									$query .= $db->quoteName('p.profile_key')." = ".$db->quote($obj->field);
									$query .= ' AND ';
									$query .= $db->quoteName('p.profile_value');
									$query .=' LIKE '.$db->quote('%'.$obj->value.'%');
									$query .= ' ) '; 						
							}else{
									$query .= ' OR ( ';
									$query .= $db->quoteName('p.profile_key')." = ".$db->quote($obj->field);
									$query .= ' AND ';
									$query .= $db->quoteName('p.profile_value');
									$query .=' LIKE '.$db->quote('%'.$obj->value.'%');
									$query .= ' ) '; 	
							} 
						if($obj->field == 'username'){
							$query .= ' OR ('.$db->quoteName('u.username').' LIKE '.$db->quote('%'.$obj->value.'%').')';
						} elseif ($obj->field == 'useremail') {
							$query .= ' OR ('.$db->quoteName('u.email').' LIKE '.$db->quote('%'.$obj->value.'%').')';
						} 
						
						 $itemCnt++; 
					}

					$query .=' GROUP BY ' . $db->quoteName( 'u.id' );
			}//main if
			
			else {
			
					$query .=' SELECT u.*,p.* ';
					$query .=' FROM '.($db->quoteName('#__users', 'u'));	
    				$query .=' INNER JOIN '.$db->quoteName('#__user_profiles', 'p').' ON u.'.$db->quoteName('id').' = p.'.$db->quoteName('user_id');				
					$query .= ' WHERE '.$db->quoteName('p.profile_key').' = '.$db->quote('hellomapsusers.mappajf');
			
			}

            //echo $query;exit;
            return $query;

        } 
		
		
		public function getAdvanceSearch($filter = array() , $join='and' , $avatarOnly = '' , $sorting = '',  $searchedProfileTypes = array())
        {

		$db = JFactory::getDBO();
		$query = $this->_getResults($filter, $join , $avatarOnly, $searchedProfileTypes);
		// execution of master query
		$db->setQuery($query);
		$result = $db->loadObjectList();

		if($db->getErrorNum()) {
    			JError::raiseError( 500, $db->stderr());
    		}
		return $result;   
        }

        
        /**
         * Get Users count, without filter
        */
        private function GetUsersTotal()
        {
            $db	= JFactory::getDBO();
            $query = $db->getQuery(true);		
          	$query->select('COUNT(*) AS total');
            $query->from($db->quoteName('#__users').' AS u');
            $query->join('inner','#__user_profiles AS p ON u.id=p.user_id');
			$query->where('p.'.$db->quoteName('profile_key').' = '.$db->Quote('hellomapsusers.mappajf'));
			$query->where('p.'.$db->quoteName('profile_value').' != ""');
            $db->setQuery($query);
            $db->query();
            return (int) $db->loadResult();
        }
        
        /**
         * Get Users count, without filter, who has lat long
        */
        public function OnGlobalResultCountPrepare(&$globalResultCount)
        {
            
            $app = JFactory::getApplication();
            $db	= JFactory::getDBO();            
            $query = $db->getQuery(true);
      	   	$query->select('p.profile_key,p.profile_value');
            $query->from($db->quoteName('#__users').' AS u');
            $query->join('inner','#__user_profiles AS p ON u.id=p.user_id');
			$query->where('p.'.$db->quoteName('profile_key').' = '.$db->Quote('hellomapsusers.mappajf'));
			$query->where('p.'.$db->quoteName('profile_value').' != ""');
			//$query->where($db->quoteName('ad_googlemaps_lat').' != "" AND '.$db->quoteName('ad_googlemaps_lng').' != ""');
		    $db->setQuery($query);
            //$db->query();
			 $userstotal = $db->loadObjectList();
			
			 if(!empty($userstotal))
					{
						$total = 0;
						foreach($userstotal as $key=>$user )
						{
							//print_r($user->profile_value);
							//Recupero Coordinate Iniziali
							$find[] = '"'; 
							$find[] = '[';
							$find[] = ']';
							$replace[] = '';
							$coords = $user->profile_value;
							$coords = str_replace($find, $replace, $coords);
							$coords = explode(",", $coords);
							$lat = $coords[0];
							$lng = $coords[1];
							if (($lat != 0)||($lng != 0)){
								(int)$total++;
							}
						}
					}
			//$total = (int) $db->loadResult();
		
            if($app->isAdmin())//show dashboard icons at backend
            {
                $globalResultCount = $globalResultCount + $total;
                $document = JFactory::getDocument();
                $document->addStyleSheet(JURI::root().'plugins/hellomaps/users/css/backend_style.css');
                $percentageBlock = "<div class='dashboardPlugin usersDashboardPlugin'> 
                                       <div clas='icon_and_count'>
                                           <div class='usersIcon'><img src='".JURI::root().'plugins/hellomaps/users/images/dashboard_icon.png'."'/></div>                                       
                                           <div class='markerCount'>
                                            ".number_format($total,0,'',',')."
                                           </div>
                                       </div>
                                       <div class='dashboard_title'>".$this->params->get('tab_title','Users')."</div>
                                    </div>";
                echo $percentageBlock;    
            }
            else
            {
                $usrsCountWithoutFilter = $this->GetUsersTotal();
                $percentage = 0;
                if($usrsCountWithoutFilter > 0)
                {
                    $percentage = ceil(($total / $usrsCountWithoutFilter) * 100);    
                }    
                $globalResultCount = $globalResultCount + $total;
                $percentageBlock = "<div class='percentageBlock usrsTotalBlock'>
                                       <span class='icon'></span>".JText::sprintf('PLG_HELLOMAPS_USERS_TOTAL_LABEL',$percentage)."
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
                    <div id="notice_box_holder_users_plugin" class="notice_box_holder notice_box_holder_plugin noticePositions<?php echo $notice_position.$infoLinkClass; ?>" style="display:none;width:<?php echo $sidebar_width; ?>px;">
                        <a class="notice_close plugin_notice_close_button" href="javascript:void(0);">X</a>                    
                        <div class="notice_box_container_plugin">
                            <div class="noticeBlock plugin">
                                <div class="plugnNotice usersNotice">
                                    <div class="notice_plugin_header users_notice_header"><?php echo JText::_('PLG_HELLOMAPS_USERS_NOTICE_HEADER_TEXT'); ?></div>
                                    <div class="plugin_notice_content users_notice_content">
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
           $hellomapPluginsEnabled[$this->filter_id] = $this->params->get('tab_title','Users');
        }
	}
}

