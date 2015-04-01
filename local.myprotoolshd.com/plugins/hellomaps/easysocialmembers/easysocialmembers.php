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
 * It is really important when you use this plugin inside module. tabs will be shown if the plugin is selected at backend, otherwise no action will be takn
 * It is not important for component and will be null
*/



if(!class_exists('plgHellomapsEasysocialmembers'))
{
	class plgHellomapsEasysocialmembers extends JPlugin
	{
		var $name		= 'Easysocialmembers';
		private $filter_id  = 'easysocialmembers';//Must be unqiue for each plugin, we will trigger javascript functions by this
        private $easysocialmembersInstalled = false;
        
	
	    function plgHellomapsEasysocialmembers(& $subject, $config)
	    {		
			parent::__construct($subject, $config);
			//load language file
			$language = JFactory::getLanguage();
			$language->load('plg_hellomaps_easysocialmembers', JPATH_ADMINISTRATOR, $language->getName(), true);
			$language->load( 'com_easysocial', JPATH_ADMINISTRATOR, $language->getName(), true);
			
			$file 	= JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/foundry.php';
			if( !JFile::exists( $file ) )
			{
				return;
			}
			
			require_once( $file );
			$this->easysocialmembersInstalled = true;
        
	    }
		
		private function multiexplodegt ($delimiters,$string) {
   
								$ready = str_replace($delimiters, $delimiters[0], $string);
								$launch = explode($delimiters[0], $ready);
								return  $launch;
							}
		
		public function onFilterListPrepare(&$filters) {  
		     if(!$this->easysocialmembersInstalled)
                return false;  
		    global $allowedPluginsInModule;
            if(isset($allowedPluginsInModule) && in_array($this->filter_id,$allowedPluginsInModule)== false)
            {
                return false;
            }
			$document = JFactory::getDocument();
			$tabTitle = $this->params->get('tab_title','Easysocialmembers');
            $marker_width = (int)$this->params->get('marker_icon_width',45);
            $marker_height = (int)$this->params->get('marker_icon_height',45);
			$show_in_sidebar = (int)$this->params->get('show_in_sidebar',1);
			$enable_easysocialmembers_detail_sidebar = (int)$this->params->get('enable_easysocialmembers_detail_sidebar',1);
			$filterElementsHTML = $this->GetFilterElements();
			$filters[] = array('title'=>$tabTitle,'filter_id'=>$this->filter_id,'content'=>$filterElementsHTML,'show_in_sidebar'=>$show_in_sidebar);
			JHtml::_('bootstrap.framework');//load bootstrap framework of joomla	
            $document->addCustomTag('<link rel="stylesheet" href="'.JURI::root().'plugins/hellomaps/easysocialmembers/css/style.css'.'" type="text/css" />');//to add css at last
            $document->addScriptDeclaration("var easysocialmembers_marker_type='".$this->params->get('marker_icon','avatar')."';\n
                                            var easysocialmembers_marker_width=".$marker_width.";\n
                                            var easysocialmembers_marker_height=".$marker_height.";\n
											var easysocialmembers_show_in_sidebar=".$show_in_sidebar.";\n    
                                            var enable_easysocialmembers_detail_sidebar=".$enable_easysocialmembers_detail_sidebar.";\n                                             
                                            ");            
			$document->addScript(JURI::root().'plugins/hellomaps/easysocialmembers/js/script.js');				
		}
		
		private function geocodeUser($userid){
			$db = JFactory::getDBO();
			//ADD EASYSOCIAL 1.3
			$q ="SELECT sfd.datakey, sfd.data, sfd.field_id ";
			$q .="FROM #__social_fields_data sfd ";
			$q .=' LEFT JOIN '.($db->quoteName('#__social_fields', 'a').'ON a.id = sfd.field_id');
			$q .=" WHERE sfd.type = 'user' ";
			//DEFINIRE KEY MAPPA UNICA IN ADDRESS OR ADDRESSHM
			$q .='AND a.unique_key LIKE ' . $db->Quote( '%ADDRESS%' );
			$q .=" AND sfd.uid ='".$userid."' ";
			
			//echo $q; exit();
			$db->setQuery($q);
			$address = $db->loadObjectList('datakey');
			//print_r ($address);

		return $address;
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
			//$filters_categories = $this->params->get('filters_categories',array());//Allowed categories
			//$easysocialmembersCategories = $this->GetEasysocialmembersCategories();			
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
			include(JPATH_ROOT.'/plugins/hellomaps/easysocialmembers/views/filter.php');
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
							$query->select('`a`.`title`,`a`.`unique_key`');
							$query->from('`#__social_fields` AS `a`');
							$query->join('inner', '`#__social_fields_data` AS `b` ON `a`.`id` = `b`.`field_id`');
							$query->where('`b`.`type` = "user" AND `a`.`unique_key` = '.$db->quote($fieldCodes[$i]).' '); 
							$db->setQuery($query);
							$db->query();
							$fieldLabel = $db->loadResult();
							if($fieldLabel != "")
								$fieldsLabels[$fieldCodes[$i]] = array('label'=>$fieldLabel);
						}
						return $fieldsLabels;	
						
		}
		//ADD GIANLUCA
		
		private function GetEasysocialmembersCategories()
		{
			$db     = JFactory::getDBO();
			$query	= 'SELECT * FROM ' . $db->quoteName( '#__categories' ) . ' '
			. 'WHERE ' . $db->quoteName( 'extension' ) . '='.$db->quote( 'com_content')
			. 'AND ' . $db->quoteName( 'published' ) . '= 1';
			$db->setQuery($query);
			return $db->loadObjectList();
		}
		
		//ADD GIANLUCA
		public function getCustomFieldRAWEasysocial ($userid, $fieldcode) 
		{
			
						$db = Foundry::db();
						$query  = array();
						
						$query[] = "select b.raw, a.unique_key from #__social_fields as a";
						$query[] = "inner join #__social_fields_data as b";
						$query[] = "on a.id = b.field_id";
						$query[] = "where b.uid=" . $db->Quote($userid);
						$query[] = "and a.unique_key=" . $db->Quote($fieldcode);
						
						$db->setQuery($query);
						$result = $db->loadResult();	
						
						return $result;
						
		}
		//ADD GIANLUCA
		
		//ADD GIANLUCA
		public function getCustomFieldDATAEasysocial($userid) 
		{
			
						$db = Foundry::db();
						$query  = array();
						
						$query[] = "select b.data, a.unique_key from #__social_fields as a";
						$query[] = "inner join #__social_fields_data as b";
						$query[] = "on a.id = b.field_id";
						$query[] = "and b.type=" . $db->Quote('user');
						$query[] = "where b.uid=" . $db->Quote($userid);
						
						$db->setQuery($query);
						$result = $db->loadAssocList('unique_key');	
						
						return $result;
						
		}
		
		public function getProfileTypeEasysocial(){
			
			$db		= JFactory::getDBO();					
			$query	= 'SELECT title AS name, id AS id FROM ' . $db->quoteName( '#__social_profiles' ) . ' '
					. 'WHERE ' . $db->quoteName( 'state' ) . '=' . $db->Quote( 1 ) . ' '
					. 'ORDER BY ' . $db->quoteName( 'ordering' );
			$db->setQuery( $query );
			$items = $db->loadObjectList();
			
			return $items;
		}
		
		
		
		//ADD GIANLUCA
		public function getProfileTypeIDEasysocial($userid) 
		{
			
						$db = Foundry::db();
						$query  = array();
						
						$query[] = "select b.profile_id from #__social_profiles as a";
						$query[] = "inner join #__social_profiles_maps as b";
						$query[] = "on a.id = b.profile_id";
						$query[] = "where b.user_id = " . $db->Quote($userid);
						
					
						$db->setQuery($query);
						$result = $db->loadResult();	
						
						return $result;
						
		}
		//ADD GIANLUCA
		
		//ADD GIANLUCA
		public function getProfileTypeEasysocialbyuser($userid) 
		{
			
						$db = Foundry::db();
						$query  = array();
						
						$query[] = "select  a.title, b.profile_id, b.user_id from #__social_profiles as a";
						$query[] = "inner join #__social_profiles_maps as b";
						$query[] = "on a.id = b.profile_id";
						$query[] = "where b.user_id = " . $db->Quote($userid);
						
					
						$db->setQuery($query);
						$result = $db->loadResult();	
						
						return $result;
						
		}
		//ADD GIANLUCA
		/**
		 * [onHellomapSearch description]
		 * @param  [type] $litsenerName [only search when litsener name is same as the plugins filter_id]
		 * @param  [type] $searchData   [description]
		 * @return [type]               [description]
		 */
		public function onHellomapSearch($litsenerName,$searchParam,&$searchResult)
		{
		    if(!$this->easysocialmembersInstalled)
                return false; 
			if($litsenerName == $this->filter_id)
			{
				$show_search = $this->params->get('show_search',1);//show searchbox
				$show_filters = $this->params->get('show_filters',1);//show filters
				$search_fields = $this->params->get('search_fields',array());//show filters
               // $fitler_categories = $this->params->get('filters_categories',array());//show filters
			    $fitler_profile_types = $this->params->get('fitler_profile_types',array());//show filters
                $details_extra_fields = $this->params->get('details_extra_fields',array());//extra fields    
				$details_extra_fields_labels = $this->getCustomFieldLabelEasysocial($details_extra_fields);            
                $respect_privacy    = $this->params->get('respect_privacy',0);
                $marker_icon        = $this->params->get('marker_icon','avatar');
                $custom_marker_image= $this->params->get('custom_marker_image','');
                $markers_name         = $this->params->get('markers_name',''); //json data for each category                
                $display_marker_infowindow = (boolean)$this->params->get('display_marker_infowindow',1) && HelloMapsHelper::GetConfiguration('infowindow_enable',1); //json data for each category
                $default_marker_icon_url = JURI::root().'plugins/hellomaps/easysocialmembers/images/markers/info.png';
                
                
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
				if ($respect_privacy){
                $search_fields[] = "PRIVACY_MAP";
				}
                $search_text = isset($searchParam['search_text'])?$searchParam['search_text']:"";
                $results = array();  
                $rows = array();     
				//print_r ($search_fields);    
                //if($show_search && $show_filters && !empty($searchParam))
                if(!empty($searchParam))
                {
					$easysocialMembersFilters = array();//array of objects
					if(!empty($search_fields))
					{
						foreach ($search_fields as $searchFieldName) {
							$easysocialMembersFilter = new stdClass;
							$easysocialMembersFilter->field = $searchFieldName;
							$easysocialMembersFilter->condition = 'contain';
							$easysocialMembersFilter->fieldType = 'text';//get from db
							$easysocialMembersFilter->value = $search_text;
							$easysocialMembersFilters[] = $easysocialMembersFilter;
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
					$results = $this->getAdvanceSearch($easysocialMembersFilters,$searchedProfileTypes);
					//print_r($results);

                    if(!empty($results))
                    {
						foreach($results as $result ){
                        // foreach($results as $key=>$result ){

						 //ESTRAPOLA COORDINATE E INDIRIZZO DA EASYSOCIAL		
						 $easysocialUserInfo = $this->geocodeUser($result->uid);
						 if(empty($easysocialUserInfo))
                            {
                                unset($results[$key]);
                                continue;
                            }
						 
						//Creazione Array Valori Markers	
						$row       = array(); 
						//$row['id'] = $result->id;
						$row['id']= $result->uid;
						
						$row['title'] = $result->username;
						$row['thumb'] = Foundry::user( $result->uid )->getAvatar(SOCIAL_AVATAR_SMALL);
						$row['largeAvatar'] = Foundry::user( $result->uid )->getAvatar(SOCIAL_AVATAR_LARGE);
						$userinfo = $this->getCustomFieldDATAEasysocial($result->uid);
						
						//ESEMPIO STAMPA CAMPO EXTRA
						//$row['campoextra']= Foundry::user( $result->id )->getFieldValue( 'GENDER' );
						//print_r($row['campoextra']);
						$row['latitude'] = (float)$result->latitude;  //latitude
						$row['longitude'] = (float)$result->longitude; //longitude	
						$row['isOnline']    = Foundry::user( $result->uid )->isOnline();

						$row['country']     =  str_replace('"', '',$easysocialUserInfo['country']->data);//COUNTRY
						$row['state']       = str_replace('"', '', $easysocialUserInfo['state']->data);//STATE
						$row['profileTypeName'] = $this->getProfileTypeEasysocialbyuser($result->uid);
						$profileTypeId = $this->getProfileTypeIDEasysocial($result->uid);
						
						
						if(!$row['isOnline'])
						{
							$lastLogin = JText::_('COM_EASYSOCIAL_USER_NEVER_LOGGED_IN');
							if ($result->lastvisitDate != '0000-00-00 00:00:00') {
								$userLastLogin = new JDate($result->lastvisitDate);
								$lastLogin = JHtml::_('date', $userLastLogin, 'D F n, Y g:i a');
							}    
						}
						else
						{
							$lastLogin = JText::_('COM_EASYSOCIAL_USER_CURRENTLY_ONLINE');
						}
						$row['lastLogin']    = $lastLogin;
						$row['profileLink']  = Foundry::user( $result->uid )->getPermalink();
						$row['totalFriends']  = Foundry::user( $result->uid )->getTotalFriends();
						$row['totalPhotos'] = Foundry::user( $result->uid )->getTotalAlbums();
						$row['totalPoint'] = Foundry::user( $result->uid )->getPoints();
						$row['totalBadges'] = Foundry::user( $result->uid )->getTotalBadges();
						$row['totalFollowing'] = Foundry::user( $result->uid )->getTotalFollowing();
						$row['totalFollowers'] = Foundry::user( $result->uid )->getTotalFollowers();
						//$row['memberSince'] = JHtml::_('date', $result->registerDate, 'D F n, Y g:i a');
						$row['memberSince'] = JHtml::_('date', $result->registerDate, 'F n, Y ');
							
						    
						 if(!empty($details_extra_fields_labels))
                            {
                                $extraFieldsValues = array();
                                foreach($details_extra_fields_labels as $field_code=>$fieldData)
                                {
									$fieldData['value'] = $this->getCustomFieldRAWEasysocial($result->uid, $field_code);//get custom field value
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
													
                            else if($marker_icon == 'easysocialprofile-type' && isset($profileTypeMarkers[$profileTypeId]))
                            {
								
                                $marker_icon_path = JPATH_SITE.'/plugins/hellomaps/easysocialmembers/images/markers/'.$profileTypeMarkers[$profileTypeId];
                                if(is_file($marker_icon_path))
                                    $marker_icon_url = JURI::base().'plugins/hellomaps/easysocialmembers/images/markers/'.$profileTypeMarkers[$profileTypeId];
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
                            $location = implode('/ ',$location);
                            $row['location'] = $location;
           
                            $row['marker_icon_url'] = $marker_icon_url;
                            $row['display_marker_infowindow'] = $display_marker_infowindow;
							$row['privacy_map']= Foundry::user( $result->uid )->getFieldValue( 'PRIVACY_MAP' );
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
               	

                //$total = count($results);
				$total = count($results);
                $display_marker_result_count        = HelloMapsHelper::GetConfiguration('results_enable',0) && (boolean)$this->params->get('display_marker_result_count',0);
                $display_marker_result_count        = $display_marker_result_count && (HelloMapsHelper::GetConfiguration('results_type','byzoom') == 'byzoom');       
                $searchResult[$this->filter_id] = array('total'=>$total,'rows'=>$rows,'display_marker_result_count'=>$display_marker_result_count);

                if($display_marker_result_count)
                {
                     $memberCountWithoutFilter = $this->GetEasysocialMembersTotal();
                    $percentage = 0;
                    if($memberCountWithoutFilter > 0)
                    {
                        $percentage = ceil(($total / $memberCountWithoutFilter) * 100);    
                    }    
                    $percentageBlock = "<div class='percentageBlock easysocialmembersTotalBlock'>
                                           <span class='icon'></span>".JText::sprintf('PLG_HELLOMAPS_EASYSOCIALMEMBERS_TOTAL_LABEL',$percentage)."
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
			include(JPATH_ROOT.'/plugins/hellomaps/easysocialmembers/views/marker_info_window.php');
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
			include(JPATH_ROOT.'/plugins/hellomaps/easysocialmembers/views/search_result.php');
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
	/*	private function getEasySocialData_old($filter = array(), $searchedProfileTypes)
		  {
			  
			  //Todo: +lat & lng per drag map area
            		
				$db	= JFactory::getDBO();
				$config		= JFactory::getConfig();
				$query		= '';
				$itemCnt	= 0;
				//for radius
				$post = JRequest::get('post');
				$enable_zoom_counter = $this->params->get('enable_zoom_counter',0);
				$search_enable       = HelloMapsHelper::GetConfiguration('search_enable',0);
            	$enable_radius       = HelloMapsHelper::GetConfiguration('search_enable_radius',0);
				//
				$respect_privacy    = $this->params->get('respect_privacy',0);
				
				if (empty($filter)){
					$query = ' SELECT a.user_id, u.id, u.email, u.username, b.datakey, b.data';
				}else {
					$query = ' SELECT a.*, u.*, b.raw, c.unique_key,b.datakey, b.data ';
				}
				
				$query .=' FROM '.($db->quoteName('#__social_users', 'a'));
				$query .=' LEFT JOIN '.($db->quoteName('#__users', 'u').'ON a.user_id = u.id');
				$query .=' LEFT JOIN '.($db->quoteName('#__social_fields_data', 'b').'ON a.user_id = b.uid');
				
				//UNIONE PER SISTEMA MULTIPROFILO DI EASYSOCIAL
				$query .=' LEFT JOIN '.($db->quoteName('#__social_profiles_maps', 'd').'ON a.user_id = d.user_id');
				$query .=' INNER JOIN '.($db->quoteName('#__social_fields', 'c'));
				$query .=' ON c.id = b.field_id ';
				
				//check for profile type, if there is any filter
				if(!empty($searchedProfileTypes))
				{
					$query .= ' AND d.'.$db->quoteName('profile_id').' IN('.implode(',',$searchedProfileTypes).') ';
				}

				if ($respect_privacy){
					$query .= ' AND c.unique_key LIKE ' . $db->Quote( '%PRIVACY_MAP%' );
					$query .= " AND c.raw ='yes' ";
					}	

				$query .=' WHERE u.id IS NOT NULL';
				
			//se i filtri sono stati inseriti nel backend come parametri
    		if(!empty($filter))
    		{
    				$filterCnt	= count($filter);
      				foreach($filter as $obj)
						{
							
					 		if($itemCnt <= 0){
									$query .= ' AND ( ';
									$query .= $db->quoteName('c.unique_key')." = ".$db->quote($obj->field);
									$query .= ' AND ';
									$query .= $db->quoteName('b.raw');
									$query .=' LIKE '.$db->quote('%'.$obj->value.'%');
									$query .= ' ) ';   
					 		}else{
									$query .= ' OR ( ';
						 			$query .= $db->quoteName('c.unique_key')." = ".$db->quote($obj->field);
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
			
			$query .=' GROUP BY ' . $db->quoteName( 'a.user_id' );	
			
			//echo $query;exit;

    		return $query;	
        }//end function*/
		
		
		/** ADD ESUS - Transforma sql key/value in colonne */
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
				//end radius
				$respect_privacy    = $this->params->get('respect_privacy',0);

				//Use of Query and SubQuery
				//nota: mantenere l'ordine di esecuzione dei componenti della query!
					$query = ' SELECT DISTINCT t2.uid, t2.type, latitude, longitude, c.unique_key, b.raw, a.user_id '; 
					// prende le colonne 
				

					$query .= ' FROM '
							//sub query for key/value row extracting
							.' ( '
							.' SELECT DISTINCT t1.uid, t1.datakey, t1.data, t1.type, t1.field_id, '
							." MAX(CASE WHEN t1.datakey = 'latitude' THEN t1.data END) AS latitude,"
							." MAX(CASE WHEN t1.datakey = 'longitude' THEN t1.data END) AS longitude"	
							.' FROM '.($db->quoteName('#__social_fields_data', 't1'))
							//.' INNER JOIN '.($db->quoteName('#__social_fields_data', 't2').'ON t1.uid = t2.uid')	
							.' GROUP BY t1.uid'
							.' ) '
							.' as t2 ';
					// end subquery	
						
					
						//join with user table
						$query .=' LEFT JOIN '.($db->quoteName('#__social_users', 'a').'ON t2.uid = a.user_id');
						$query .=' LEFT JOIN '.($db->quoteName('#__users', 'u').'ON t2.uid = u.id');
						
						//join again to #__social_fields_data to prepare for search filter
						$query .=' LEFT JOIN '.($db->quoteName('#__social_fields_data', 'b').'ON a.user_id = b.uid');
						
						//UNIONE PER SISTEMA MULTIPROFILO DI EASYSOCIAL
						$query .=' LEFT JOIN '.($db->quoteName('#__social_profiles_maps', 'd').'ON t2.uid = d.user_id');
						$query .=' INNER JOIN '.($db->quoteName('#__social_fields', 'c').'ON c.id = t2.field_id ');

						//se i filtri sono stati inseriti nel backend come parametri
						if(!empty($filter))
						{
								$filterCnt	= count($filter);
								foreach($filter as $obj)
									{
										
										if($itemCnt <= 0){
												$query .= ' AND ( ';
												$query .= $db->quoteName('c.unique_key')." = ".$db->quote($obj->field);
												$query .= ' AND ';
												$query .= $db->quoteName('b.raw');
												$query .=' LIKE '.$db->quote('%'.$obj->value.'%');
												$query .= ' ) ';   
										}else{
												$query .= ' OR ( ';
												$query .= $db->quoteName('c.unique_key')." = ".$db->quote($obj->field);
												$query .= ' AND ';
												$query .= $db->quoteName('b.raw');
												$query .=' LIKE '.$db->quote('%'.$obj->value.'%');
												$query .= ' ) ';  
										}//end if
								 
										/*if($obj->field == 'username'){
												$query .= ' OR ('.$db->quoteName('u.username').' LIKE '.$db->quote('%'.$obj->value.'%').')';
										}elseif ($obj->field == 'useremail') {
												$query .= ' OR ('.$db->quoteName('u.email').' LIKE '.$db->quote('%'.$obj->value.'%').')';
										}//end if*/
										
								 
								   $itemCnt++; 
								}//end foreach
						}//end if filter*/
						
						
						$query .=' WHERE u.id IS NOT NULL';
						
						//check for profile type, if there is any filter
						if(!empty($searchedProfileTypes))
						{
							$query .=' AND d.'.$db->quoteName('profile_id').' IN('.implode(',',$searchedProfileTypes).') ';
						}
						

							 if($enable_zoom_counter)
                        {
                            if(isset($post,$post['ne'],$post['ne']['lat'],$post['ne']['lng'],$post['sw'],$post['sw']['lat'],$post['sw']['lng']))
                            {
                             								 
								//CONDIZIONE CHE NON FUNZIONA con la condizione AND -- AND 
                              if ($post['ne']['lng'] > $post['sw']['lng']){
                                  $query .= ' AND ( (longitude >= ' . $db->quote($post['sw']['lng']) . ' OR longitude <= ' . $db->quote($post['ne']['lng']) . ')'.' AND (latitude <= ' . $db->quote($post['ne']['lat']) . ' AND latitude >= ' . $db->quote($post['sw']['lat']) . ') )';
                                }
                            }
                        }
                        //apply radius search
                        if($search_enable && $enable_radius && 
						isset($post['easysocialmembers']['location'],$post['easysocialmembers']['location_lat'],$post['easysocialmembers']['location_lng'],$post['easysocialmembers']['search_radius'])
                         && 
                         !empty($post['easysocialmembers']['location']) && is_numeric($post['easysocialmembers']['location_lat']) && is_numeric($post['easysocialmembers']['location_lng'])
                         && is_numeric($post['easysocialmembers']['search_radius']) && ($post['easysocialmembers']['search_radius'] > 0)
                         )
                        {                            
                            $distance_col_expression = "(((acos(sin((".$post['easysocialmembers']['location_lat']."*pi()/180)) *
            					sin((latitude * pi()/180))+cos((".$post['easysocialmembers']['location_lat']." * pi()/180)) *
            					cos((latitude * pi()/180)) * cos(((".$post['easysocialmembers']['location_lng']."- longitude)
            					*pi()/180))))*180/pi())*60*1.1515) 
            					";
                            $query .= ' AND '.$distance_col_expression .' <= '.$post['easysocialmembers']['search_radius'];
                        }
                        //added by sam end
						
						$query .=' GROUP BY ' . $db->quoteName( 'a.user_id' );				

			//echo $query;exit;

    		return $query;	
        }//end function
        
        /**
         * Get community members count, without filter
        */
        private function GetEasysocialMembersTotal()
        {
            $db	= JFactory::getDBO();
            $query = $db->getQuery(true);
            $query->select('COUNT(*) AS total');
            $query->from($db->quoteName('#__social_users').' a');
            $query->join('inner','#__users AS b ON a.user_id=b.id');
			$query->where('b.'.$db->quoteName('block').' = '.$db->Quote('0'));
            $db->setQuery($query);
            $db->query();
            return (int) $db->loadResult();
        }
        
        /**
         * Get adsmanager ads count, without filter, who has lat long
        */
       public function OnGlobalResultCountPrepare(&$globalResultCount)
        {
			if(!$this->easysocialmembersInstalled)
                return false;
            
            $app = JFactory::getApplication();
            $db	= JFactory::getDBO();            
            $query = $db->getQuery(true);
            $query->select('COUNT(*) AS total');
           	$query->from($db->quoteName('#__social_users').' a');
            $query->join('inner','#__users AS b ON a.user_id=b.id');
			$query->where('b.'.$db->quoteName('block').' = '.$db->Quote('0'));
            $db->setQuery($query);
            $db->query();
            $total = (int) $db->loadResult();
            if($app->isAdmin())//show dashboard icons at backend
            {
                $globalResultCount = $globalResultCount + $total;
                $document = JFactory::getDocument();
                $document->addStyleSheet(JURI::root().'plugins/hellomaps/easysocialmembers/css/backend_style.css');
                $percentageBlock = "<div class='dashboardPluginEasySocial easysocialmembersDashboardPlugin'> 
                                       <div clas='icon_and_count'>
                                           <div class='easysocialmembersIcon'><img src='".JURI::root().'plugins/hellomaps/easysocialmembers/images/dashboard_icon.png'."'/></div>                                       
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
			if(!$this->easysocialmembersInstalled)
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
            if(!$this->easysocialmembersInstalled)
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
                    <div id="notice_box_holder_easysocialmembers_plugin" class="notice_box_holder notice_box_holder_plugin noticePositions<?php echo $notice_position.$infoLinkClass; ?>" style="display:none;width:<?php echo $sidebar_width; ?>px;">
                        <a class="notice_close plugin_notice_close_button" href="javascript:void(0);">X</a>                    
                        <div class="notice_box_container_plugin">
                            <div class="noticeBlock plugin">
                                <div class="plugnNotice easysocialmembersNotice">
                                    <div class="notice_plugin_header easysocialmembers_notice_header"><?php echo JText::_('PLG_HELLOMAPS_EASYSOCIALMEMBERS_NOTICE_HEADER_TEXT'); ?></div>
                                    <div class="plugin_notice_content easysocialmembers_notice_content">
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
			if(!$this->easysocialmembersInstalled)
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
			if(!$this->easysocialmembersInstalled)
                return false;
           $hellomapPluginsEnabled[$this->filter_id] = $this->params->get('tab_title','EasySocial');
        }
	}
}