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


if(!defined('COMMUNITY_COM_PATH') && is_file(JPATH_ROOT . '/components/com_community/defines.community.php'))
{
	require_once JPATH_ROOT . '/components/com_community/defines.community.php';
	require_once COMMUNITY_COM_PATH . '/libraries/error.php';	
	require_once COMMUNITY_COM_PATH . '/libraries/apps.php';
	require_once COMMUNITY_COM_PATH . '/libraries/core.php';
}

if(!class_exists('HelloMapsHelper'))
    require_once(JPATH_ADMINISTRATOR.'/components/com_hellomaps/helpers/hellomaps.php');
	

if(!class_exists('XiptHelperProfiletypes') && is_file(JPATH_SITE.'/components/com_xipt/includes.php'))
    require_once JPATH_SITE.'/components/com_xipt/includes.php';
if(!class_exists('XiptAPI') && is_file(JPATH_SITE.'/components/com_xipt/api.xipt.php'))    
    require_once ( JPATH_SITE.'/components/com_xipt/api.xipt.php');
/**
 * $allowedPluginsInModule
 * It is really important when you use this plugin inside module. tabs will be shown if the plugin is selected at backend, otherwise no action will be takn
 * It is not important for component and will be null
*/
if(!class_exists('plgHellomapsMembers'))
{
	class plgHellomapsMembers extends JPlugin
	{
		private $name		= 'Members';
		private $filter_id  = 'members';//Must be unqiue for each plugin, we will trigger javascript functions by this
        private $jomsocialInstalled = false;
	
	    public function plgHellomapsMembers(&$subject, $config)
	    {		
			parent::__construct($subject, $config);
			//load language file
			$language = JFactory::getLanguage();
			//echo 'Current language is: ' . $language->getName();
			$language->load( 'plg_hellomaps_members', JPATH_ADMINISTRATOR, $language->getName(), false);               
	   	    $language->load( 'com_community.country', JPATH_SITE, $language->getName(), false);
           // $language->load( 'com_community', JPATH_SITE, $language->getName(), false);
			
            $this->jomsocialInstalled = $this->isJomsocialInstalled();
	    }
		
		public function onFilterListPrepare(&$filters) {
		    if(!$this->jomsocialInstalled)
			
			
                return false;  
		    global $allowedPluginsInModule;
           
            if(isset($allowedPluginsInModule) && in_array($this->filter_id,$allowedPluginsInModule)== false)
            {
                return false;
            }
			$document = JFactory::getDocument();
			$tabTitle = $this->params->get('tab_title','Members');
            $marker_width = (int)$this->params->get('marker_icon_width',45);
            $marker_height = (int)$this->params->get('marker_icon_height',45);
            $show_in_sidebar = (int)$this->params->get('show_in_sidebar',1);
            $show_in_sidebar = (int)$this->params->get('show_in_sidebar',1);
            $enable_member_detail_sidebar = (int)$this->params->get('enable_member_detail_sidebar',1);
			$filterElementsHTML = $this->GetFilterElements();
			$filters[] = array('title'=>$tabTitle,'filter_id'=>$this->filter_id,'content'=>$filterElementsHTML,'show_in_sidebar'=>$show_in_sidebar);
			JHtml::_('bootstrap.framework');//load bootstrap framework of joomla
            $document->addCustomTag('<link rel="stylesheet" href="'.JURI::root().'plugins/hellomaps/members/css/style.css'.'" type="text/css" />');//to add css at last
            $document->addScriptDeclaration("var members_marker_type='".$this->params->get('marker_icon','ad_thumb')."';\n
                                            var members_marker_width=".$marker_width.";\n
                                            var members_marker_height=".$marker_height.";\n 
                                            var members_show_in_sidebar=".$show_in_sidebar.";\n      
                                            var enable_member_detail_sidebar =".$enable_member_detail_sidebar.";\n                                              
                                            ");
			$document->addScript(JURI::root().'plugins/hellomaps/members/js/script.js');	
            CMessaging::load();//load jomsocial library js files
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
            $profileTypeSource = $this->params->get('profile_type_source','jomsocial');//xipt or jomsocial
            if($profileTypeSource == 'jomsocial')
			     $fitler_profile_types = $this->params->get('fitler_profile_types',array());//Allowed profile types
            else if($profileTypeSource == 'xipt')
                $fitler_profile_types = $this->params->get('fitler_xipt_profile_types',array());//Allowed profile types of xipt
			$search_fields = $this->params->get('search_fields',array());
			
            
            $search_enable = HelloMapsHelper::GetConfiguration('search_enable',1);//from component
            $search_enable_radius = HelloMapsHelper::GetConfiguration('search_enable_radius',1);//from component
            $contents_enable      = HelloMapsHelper::GetConfiguration('contents_enable',0);//to put the result html in the sidebar...

			if($show_filters && !empty($fitler_profile_types))
			{
			    if($profileTypeSource == 'jomsocial')
                {
                    $profileModel	= CFactory::getModel('profile');
				    $profileTypes   = $profileModel->getProfileTypes();	
                } 
			    else if($profileTypeSource == 'xipt') 
                {
                    $profileTypes = XiptLibProfiletypes::getProfiletypeArray(); //xipt profile types
                }			         
			}
            
			
			ob_start();
			include(JPATH_ROOT.'/plugins/hellomaps/members/views/filter.php');
			$filterElementsHTML = ob_get_contents();
			ob_end_clean();
			return $filterElementsHTML;
		}
        private function GetJomsocialCustomFieldsLabels($fieldCodes)
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
                $query->select($db->quoteName('name'));
                $query->from($db->quoteName('#__community_fields'));
                $query->where($db->quoteName('published').'=1 AND '.$db->quoteName('fieldcode').'='.$db->quote($fieldCodes[$i]));   
                $db->setQuery($query);
                $db->query();
                $fieldLabel = $db->loadResult();
                if($fieldLabel != "")
                    $fieldsLabels[$fieldCodes[$i]] = array('label'=>$fieldLabel);
            }
            return $fieldsLabels;
        }
		/**
		 * [onHellomapSearch description]
		 * @param  [type] $litsenerName [only search when litsener name is same as the plugins filter_id]
		 * @param  [type] $searchData   [description]
		 * @return [type]               [description]
		 */
		public function onHellomapSearch($litsenerName,$searchParam,&$searchResult)
		{
		    if(!$this->jomsocialInstalled)
                return false;    
		    $db = JFactory::getDBO();  
            $my 		= CFactory::getUser();
		    $config		= CFactory::getConfig();
			if($litsenerName == $this->filter_id)
			{
				$show_search = $this->params->get('show_search',1);//show searchbox
				$show_filters = $this->params->get('show_filters',1);//show filters
				$search_fields = $this->params->get('search_fields',array());//show filters
                
                $profileTypeSource = $this->params->get('profile_type_source','jomsocial');//xipt or jomsocial
                if($profileTypeSource == 'jomsocial')
    			     $fitler_profile_types = $this->params->get('fitler_profile_types',array());//Allowed profile types
                else if($profileTypeSource == 'xipt')
                    $fitler_profile_types = $this->params->get('fitler_xipt_profile_types',array());//Allowed profile types of xipt
                
                
                $details_extra_fields = $this->params->get('details_extra_fields',array());//extra fields
                $details_extra_fields_labels = $this->GetJomsocialCustomFieldsLabels($details_extra_fields);//will have field_code=>label
                $respect_privacy    = $this->params->get('respect_privacy',0);
                $marker_icon        = $this->params->get('marker_icon','avatar');
                $custom_marker_image= $this->params->get('custom_marker_image','');
                $markers_name         = $this->params->get('markers_name',''); //json data for each profile type
                 
                $display_marker_infowindow = (boolean)$this->params->get('display_marker_infowindow',1) && HelloMapsHelper::GetConfiguration('infowindow_enable',1); //json data for each category
                $default_marker_icon_url = JURI::root().'plugins/hellomaps/members/images/markers/black.png';
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
				//if($show_search && $show_filters && !empty($searchParam))
                if(!empty($searchParam))
				{
					$searchModel	    = CFactory::getModel('search');
                    $videosModel	    = CFactory::getModel('videos');
                    $photosModel	    = CFactory::getModel('photos');
                    $groupsModel	    = CFactory::getModel('groups');
                    $eventsModel	    = CFactory::getModel('events');
					$jomsocialMembersFilters = array();//array of objects
					if(!empty($search_fields))
					{
						foreach ($search_fields as $searchFieldName) {
							$jomsocialMembersFilter = new stdClass;
							$jomsocialMembersFilter->field = $searchFieldName;
							$jomsocialMembersFilter->condition = 'contain';
							$jomsocialMembersFilter->fieldType = 'text';//get from db
							$jomsocialMembersFilter->value = $search_text;
							$jomsocialMembersFilters[] = $jomsocialMembersFilter;
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
					$results = $this->getAdvanceSearch($jomsocialMembersFilters, $join , $avatarOnly  , $sorting ,$searchedProfileTypes  );
                    
                    
                    if(!empty($results))
                    {   
                        foreach($results as $key=>$result)
                        {       
                            if($key == 0)
                                $query = $db->getQuery(true);
                            else
                                $query->clear();
                            $query->select('*');
                            $query->from($db->quoteName('#__community_users'));
                            $query->where($db->quoteName('userid').'='.$result->id);
                            $query->where($db->quoteName('latitude').'!=255 AND '.$db->quoteName('longitude').'!=255');
                            $db->setQuery($query);
                            $db->query();
                            $jomsocialUserInfo = $db->loadAssoc();
                            if(empty($jomsocialUserInfo))
                            {
                                unset($results[$key]);
                                continue;
                            }
                                
                            
                            $row       = array(); 
                            $row['id'] = $result->id;
                            $row['title'] = $result->getDisplayName();
                            $row['thumb'] = $result->getThumbAvatar();
                            $row['largeAvatar'] = $result->getAvatar();
                            $row['address'] = $result->getAddress();
                            if($row['address']!="")
                            {
                                $address = explode(',',$row['address']);
                                foreach($address as $addressIndex=>$value)
                                {
                                   $address[$addressIndex] = JText::_($value);                                     
                                }
                                $row['address'] = implode(',',$address);
                            }
                            $row['latitude'] = (float)$jomsocialUserInfo['latitude']; 
                            $row['longitude'] = (float)$jomsocialUserInfo['longitude'];
                            $row['latestStatus'] = $result->getStatus();
                            $row['memberSince']	= CTimeHelper::getDate($result->registerDate);
                            $row['isOnline']    = $result->isOnline();
                            $row['country']     = JText::_($result->getInfo('FIELD_COUNTRY'));//get custom field value
                            $row['state']       = $result->getInfo('FIELD_STATE');
                            
                            if($profileTypeSource == 'jomsocial')
                                $profileTypeId = $result->getProfileType();
                            else if($profileTypeSource == 'xipt')
                                $profileTypeId = XiptAPI::getUserProfiletype($result->id,'id');
                            $row['profileTypeId'] = $profileTypeId;                            
                            if($profileTypeId > 0)
                            {
                                if($profileTypeSource == 'jomsocial')
                                {
                                    $profileType = JTable::getInstance('MultiProfile', 'CTable');
                                    $profileType->load($profileTypeId);
                                    $row['profileTypeName'] = $profileType->name;    
                                }
                                else if($profileTypeSource == 'xipt')
                                {
                                    $row['profileTypeName'] = XiptAPI::getUserProfiletype($result->id,'name');
                                }                                    
                            }
                            if(!$row['isOnline'])
                            {
                                $lastLogin = JText::_('COM_COMMUNITY_PROFILE_NEVER_LOGGED_IN');
                                if ($result->lastvisitDate != '0000-00-00 00:00:00') {
                                    $userLastLogin = new JDate($result->lastvisitDate);
                                    $lastLogin = CActivityStream::_createdLapse($userLastLogin);
                                }    
                            }
                            else
                            {
                                $lastLogin = JText::_('COM_COMMUNITY_ONLINE');
                            }
                            $row['lastLogin']    = $lastLogin;
                            $row['profileLink']  = CUrlHelper::userLink($result->id);
                            
                            $row['totalFriends']  = $result->getFriendCount();
                            
                            $isFriend =  CFriendsHelper::isConnected( $result->id, $my->id );
                            
                
                			$row['addFriend'] 	= ((! $isFriend) && ($my->id != 0) && $my->id != $result->id) ? true : false;                            
                            $row['totalVideos'] = count($videosModel->getUserTotalVideos($result->id));
                            $row['totalPhotos'] = $photosModel->getPhotosCount($result->id);
                            $row['totalGroups'] = $groupsModel->getGroupsCount( $result->id );
                            $row['totalEvents'] = $eventsModel->getEventsCount($result->id);
                            if(!empty($details_extra_fields_labels))
                            {
                                $extraFieldsValues = array();
                                foreach($details_extra_fields_labels as $field_code=>$fieldData)
                                {							
                                    $fieldData['value'] = $result->getInfo($field_code);//get custom field value
                                    if(!empty($fieldData['value']) && !is_array($fieldData['value']))
                                      $fieldData['value'] = JText::_($fieldData['value']);
                                    $details_extra_fields_labels[$field_code] = $fieldData;
                                }
                            }
							
                            $row['extraFields'] = $details_extra_fields_labels;
                            
                            $row['karmaImgUrl'] = CUserPoints::getPointsImage($result);
                            //JText::_('COM_COMMUNITY_KARMA')
                            $row['qr_code_img'] = 'http://chart.apis.google.com/chart?cht=qr&chs=80x80&chl=geo:'.$jomsocialUserInfo['latitude'].','.$jomsocialUserInfo['longitude'];
                            
                            $marker_icon_url = $default_marker_icon_url;
                            if($marker_icon == 'avatar')
                            {
                                $marker_icon_url = $row['thumb'];
                            }
                            else if($marker_icon == 'profile-type' && isset($profileTypeMarkers[$profileTypeSource.'_'.$profileTypeId]))
                            {
                                $marker_icon_path = JPATH_SITE.'/plugins/hellomaps/members/images/markers/'.$profileTypeMarkers[$profileTypeSource.'_'.$profileTypeId];
                                if(is_file($marker_icon_path))
                                    $marker_icon_url = JURI::base().'plugins/hellomaps/members/images/markers/'.$profileTypeMarkers[$profileTypeSource.'_'.$profileTypeId];
                            }
                            else if($marker_icon == 'custom' && $custom_marker_image != "")
                            {
                                $marker_icon_url = $custom_marker_image;
                            }
                            
                            $row['isFriend'] =  $isFriend || (($my->id != 0) && $my->id == $result->id);
                            
            
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
                $total = count($results);
                $display_marker_result_count        = HelloMapsHelper::GetConfiguration('results_enable',0) && (boolean)$this->params->get('display_marker_result_count',0);     
                $display_marker_result_count        = $display_marker_result_count && (HelloMapsHelper::GetConfiguration('results_type','byzoom') == 'byzoom');           
                $searchResult[$this->filter_id] = array('total'=>$total,'rows'=>$rows,'display_marker_result_count'=>$display_marker_result_count);
                
                if($display_marker_result_count)
                {
                    $memberCountWithoutFilter = $this->GetJomsocialMembersTotal();
                    $percentage = 0;
                    if($memberCountWithoutFilter > 0)
                    {
                        $percentage = ceil(($total / $memberCountWithoutFilter) * 100);    
                    }    
                    $percentageBlock = "<div class='percentageBlock membersTotalBlock'>
                                           <span class='icon'></span>".JText::sprintf('PLG_HELLOMAP_MEMBERS_TOTAL_LABEL',$percentage)."
                                           <div class='color'>
                                            ".number_format($total,0,'',',')."
                                           </div>
                                        </div>";
                    $searchResult[$this->filter_id]['percentageBlock'] = $percentageBlock;
                }			
			}
		}
        /**
         * Build marker info window for the member         
         * @markerData is the information generatted for the user object 
        */
        private function GetMarkerInfoWindowContent($markerData)
        {
            $my 		= CFactory::getUser();
            $markerInfoHTML = '';
            
            $markerInfoWindowWidth = HelloMapsHelper::GetConfiguration('infowindow_width',150);
            if(is_numeric($markerInfoWindowWidth))
                $markerInfoWindowWidth = $markerInfoWindowWidth.'px';
            $markerInfoWindowHeight = HelloMapsHelper::GetConfiguration('infowindow_height',150);
            if(is_numeric($markerInfoWindowHeight))
                $markerInfoWindowHeight = $markerInfoWindowHeight.'px';
            ob_start();
			include(JPATH_ROOT.'/plugins/hellomaps/members/views/marker_info_window.php');
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
			include(JPATH_ROOT.'/plugins/hellomaps/members/views/search_result.php');
			$searchResultHTML = ob_get_contents();
			ob_end_clean();
			return $searchResultHTML;
        }
        
        
        /**
         * Community advanced search
         * copied from search model of com_community front end
        */
        public function getAdvanceSearch($filter = array(), $join='and' , $avatarOnly = '' , $sorting = '', $searchedProfileTypes = array())
        {
    		$db	= JFactory::getDBO();
            $respect_privacy    = $this->params->get('respect_privacy',0);
            $respect_privacy_field_id = 0;
            //if respect privacy is enabled, jomsocial must have the FIELD_HELLOMAP_PRIVACY field
            if($respect_privacy)
            {
                $sql = 'SELECT '.$db->quoteName('id').' FROM '.$db->quoteName('#__community_fields').' WHERE '.$db->quoteName('fieldcode').'='.$db->quote('FIELD_HELLOMAP_PRIVACY').' AND '.$db->quoteName('published').'=1';                
                $db->setQuery($sql);
                $db->query();
                $respect_privacy_field_id = (int)$db->loadResult();
                if($respect_privacy_field_id == 0)
                    return array();
            }
    		$query	= $this->_buildCustomQuery($filter, $join , $avatarOnly, $searchedProfileTypes );
            $query = 'SELECT DISTINCT(combined_result.user_id) FROM ('.$query.') AS combined_result';
            //sam search here
            //echo '<pre>';
            //print_r($filter);
            //echo $join.'<br>';
            //echo $avatarOnly.'<br>';
            //echo str_replace('#__','f9leh_',$query);
            //exit;
            //lets try temporary table here
    		$tmptablename = 'tmpmembersearch';
    		$drop = 'DROP TEMPORARY TABLE IF EXISTS '.$tmptablename;
    		$db->setQuery($drop);
    		$db->query();
    
    		$query = 'CREATE TEMPORARY TABLE '.$tmptablename.' '.$query;
    		$db->setQuery($query);
    		$db->query();
    		$total = $db->getAffectedRows();
    
    		//setting pagination object.
    		
    
    		$query = 'SELECT * FROM '.$tmptablename;
            
            if($respect_privacy)
            {
                $query = 'SELECT DISTINCT('.$tmptablename.'.user_id'.') AS user_id,'.$tmptablename.'.* FROM '.$tmptablename.' INNER JOIN #__community_fields_values AS fv ON (fv.user_id='.$tmptablename.'.user_id AND field_id='.$respect_privacy_field_id.' AND LOWER(fv.value)!="yes")';                                
            }
    
    		// @rule: Sorting if required.
    		if( !empty( $sorting ) )
    		{
    			$query  .= $this->_getSort($sorting);
    		}
    
    
    		// execution of master query
    		
    		$db->setQuery($query);
    
    		$result = $db->loadColumn();
            
    
    		if($db->getErrorNum()) {
    			JError::raiseError( 500, $db->stderr());
    		}
    
    		// Preload CUser objects
    		if(! empty($result))
    		{
    			CFactory::loadUsers($result);
    		}
    		$cusers = array();
    		for($i = 0; $i < count($result); $i++)
    		{
    			$usr = CFactory::getUser( $result[$i] );
    			$cusers[] = $usr;
    		}
    
    		return 	$cusers;
        }
        /**
         * Community advanced search
         * copied from search model of com_community front end
        */
        public function _buildCustomQuery($filter = array(), $join='and' , $avatarOnly = '', $searchedProfileTypes=array())
    	{
    		$db	= JFactory::getDBO();
    		$query		= '';
    		$itemCnt	= 0;
    		$config		= CFactory::getConfig();
            $post = JRequest::get('post');
            $enable_zoom_counter = $this->params->get('enable_zoom_counter',0);
            $search_enable       = HelloMapsHelper::GetConfiguration('search_enable',0);
            $enable_radius       = HelloMapsHelper::GetConfiguration('search_enable_radius',0);
            $profileTypeSource = $this->params->get('profile_type_source','jomsocial');//xipt or jomsocial
    
    
    		/**
    		 * For the 'ALL' case, we use 'IN' whereas for 'ANY' case, we use UNION.
    		 *
    		 */
    		if(! empty($filter))
    		{
    			$filterCnt	= count($filter);
    
    			foreach($filter as $obj)
    			{
    				if($obj->field == 'username' || $obj->field == 'useremail')
    				{
    					$useArray	= array('username' => $config->get('displayname') , 'useremail' => 'email');
    
    					if($itemCnt > 0 && $join == 'or')
    					{
    						$query	.= ' UNION ';
    					}
    
    					$query	.= ($join == 'or') ? ' (' : '';
    					$query	.= ' SELECT DISTINCT( b.'.$db->quoteName('userid').' ) as '.$db->quoteName('user_id');
    
    					if( $itemCnt == 0 || $join == 'or')
    					{
    					    $query  .= ', a.'.$db->quoteName('username').' AS '.$db->quoteName('username');
    					    $query  .= ', a.'.$db->quoteName('name').' AS '.$db->quoteName('name');
    						$query  .= ', a.'.$db->quoteName('registerDate').' AS '.$db->quoteName('registerDate');
    						$query	.= ', CASE WHEN s.'.$db->quoteName('userid').' IS NULL THEN 0 ELSE 1 END AS online';
    					}
    
    					$query  .= ' FROM '.$db->quoteName('#__users').' AS a';
    
    					if( $itemCnt == 0 || $join == 'or')
    					{
    						$query  .= ' LEFT JOIN '.$db->quoteName('#__session').' AS s';
    						$query  .= ' ON a.'.$db->quoteName('id').'=s.'.$db->quoteName('userid');
    					}
    
    					$query	.= ' INNER JOIN '.$db->quoteName('#__community_users').' AS b';
    					$query	.= ' ON a.'.$db->quoteName('id').' = b.'.$db->quoteName('userid');
    					$query	.= ' AND a.'.$db->quoteName('block').' = '.$db->Quote('0');
                        
                        //check for profile type, if there is any filter
                        //added by sam
                        if(!empty($searchedProfileTypes))
                        {
                            //$query .= ' AND b.'.$db->quoteName('profile_id').' IN('.implode(',',$searchedProfileTypes).')';
                            if($profileTypeSource == 'jomsocial')
                            {
                                $query .= ' AND b.'.$db->quoteName('profile_id').' IN('.implode(',',$searchedProfileTypes).')';
                            }   
                            else if($profileTypeSource == 'xipt')
                            {
                                //inner join with xipt
                                $query .= ' INNER JOIN '.$db->quoteName('#__xipt_users').' AS xipt_user';
                                $query	.= ' ON a.'.$db->quoteName('id').' = xipt_user.'.$db->quoteName('userid');
                                $query	.= ' AND xipt_user.'.$db->quoteName('profiletype').' IN('.implode(',',$searchedProfileTypes).')';    
                            }                           
                            
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
                        
                        //apply radius search
                        if($search_enable && $enable_radius && isset($post['members']['location'],$post['members']['location_lat'],$post['members']['location_lng'],$post['members']['search_radius'])
                         && 
                         !empty($post['members']['location']) && is_numeric($post['members']['location_lat']) && is_numeric($post['members']['location_lng'])
                         && is_numeric($post['members']['search_radius']) && ($post['members']['search_radius'] > 0)
                         )
                        {                            
                            // in Miles
                            //to convert into KM please use 60*1.1515*1.609344
                            //to convert into Mile please use 60*1.1515
                            //1 Mile = 1.609344 KM
                            $distance_col_expression = "(((acos(sin((".$post['members']['location_lat']."*pi()/180)) *
            					sin((b.latitude * pi()/180))+cos((".$post['members']['location_lat']." * pi()/180)) *
            					cos((b.latitude * pi()/180)) * cos(((".$post['members']['location_lng']."- b.longitude)
            					*pi()/180))))*180/pi())*60*1.1515) 
            					";
                            $query .= ' AND '.$distance_col_expression .' <= '.$post['members']['search_radius'];
                            //$distance_col_sql = ", $distance_col_expression as distance ";
                        }
                        //added by sam end
    
    					// @rule: Only fetch users that is configured to be searched via email.
    					if( $obj->field == 'useremail' && $config->get( 'privacy_search_email') == 1 )
    					{
    						$query  .= ' AND b.'.$db->quoteName('search_email').'=' . $db->Quote( 1 );
    					}
    
    					// @rule: Fetch records with proper avatar only.
    					if( !empty($avatarOnly) )
    					{
    						$query .= ' AND b.' . $db->quoteName( 'thumb' ) . ' != ' . $db->Quote( 'components/com_community/assets/default_thumb.jpg' );
    						$query .= ' AND b.' . $db->quoteName( 'thumb' ) . ' != ' . $db->Quote( '' );
    					}
    
    					$query	.= ' WHERE ' . $this->_mapConditionKey($obj->condition, $obj->fieldType, $obj->value, $useArray[$obj->field]);
    
    					$query	.= ($join == 'or') ? ' )' : '';
    
    					if($itemCnt < ($filterCnt - 1) && $join == 'and')
    					{
    						$query	.= ' AND b.'.$db->quoteName('userid').' IN (';
    					}
    
    				}
    				else
    				{
    					if($itemCnt > 0 && $join == 'or')
    					{
    						$query	.= ' UNION ';
    					}
    
    					$query	.= ($join == 'or') ? ' (' : '';
    					$query	.= ' SELECT DISTINCT( a.'.$db->quoteName('user_id').' ) AS '.$db->quoteName('user_id');
    
    					// We cannot select additional columns for the subquery otherwise it will result in operand errors,
    					if( $itemCnt == 0 || $join == 'or' )
    					{
    					    $query  .= ', u.'.$db->quoteName('username').' AS '.$db->quoteName('username');
    					    $query  .= ', u.'.$db->quoteName('name').' AS '.$db->quoteName('name');
    						$query  .= ', u.'.$db->quoteName('registerDate').' AS '.$db->quoteName('registerDate');
    						$query	.= ', CASE WHEN s.'.$db->quoteName('userid').' IS NULL THEN 0 ELSE 1 END AS online';
    					}
    					$query  .= ' FROM '.$db->quoteName('#__community_fields_values').' AS a';
    
    					// We cannot select additional columns for the subquery otherwise it will result in operand errors,
    					if( $itemCnt == 0 || $join == 'or')
    					{
    						$query  .= ' LEFT JOIN '.$db->quoteName('#__session').' AS s';
    						$query  .= ' ON a.'.$db->quoteName('id').'=s.'.$db->quoteName('userid');
    					}
    
    
         				$query	.= ' INNER JOIN '.$db->quoteName('#__community_fields').' AS b';
    					$query	.= ' ON a.'.$db->quoteName('field_id').' = b.'.$db->quoteName('id');
    					$query	.= ' INNER JOIN '.$db->quoteName('#__users').' AS u ON a.'.$db->quoteName('user_id').' = u.'.$db->quoteName('id');
    					$query	.= ' AND u.'.$db->quoteName('block').' ='.$db->Quote('0');
                        
                        //check for profile type, if there is any filter
                        
    
    					// @rule: Fetch records with proper avatar only.
                        $query	.= ' INNER JOIN '.$db->quoteName('#__community_users').' AS c ON a.'.$db->quoteName('user_id').'=c.'.$db->quoteName('userid');
    					if( !empty($avatarOnly) )
    					{    						
    						$query	.= ' AND c.'.$db->quoteName('thumb').' != ' . $db->Quote( '' );
    						$query  .= ' AND c.'.$db->quoteName('thumb').' != ' . $db->Quote( 'components/com_community/assets/default_thumb.jpg' );
    					}
                        //added by sam 
                        if(!empty($searchedProfileTypes))
                        {
                            //$query .= ' AND c.'.$db->quoteName('profile_id').' IN('.implode(',',$searchedProfileTypes).')';
                            if($profileTypeSource == 'jomsocial')
                            {
                                $query .= ' AND c.'.$db->quoteName('profile_id').' IN('.implode(',',$searchedProfileTypes).')';
                            }   
                            else if($profileTypeSource == 'xipt')
                            {
                                //inner join with xipt
                                $query .= ' INNER JOIN '.$db->quoteName('#__xipt_users').' AS xipt_user';
                                $query	.= ' ON c.'.$db->quoteName('userid').' = xipt_user.'.$db->quoteName('userid');
                                $query	.= ' AND xipt_user.'.$db->quoteName('profiletype').' IN('.implode(',',$searchedProfileTypes).')';    
                            }   
                                                  
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
                                    $query .= ' AND ( (c.longitude > ' . $db->quote($post['sw']['lng']) . ' AND c.longitude < ' . $db->quote($post['ne']['lng']) . ')'.
                                                 ' AND (c.latitude <= ' . $db->quote($post['ne']['lat']) . ' AND c.latitude >= ' . $db->quote($post['sw']['lat']) . ') )'
                                            ;                        
                                }
                                else
                                {
                                    $query .= ' AND ( (c.longitude >= ' . $db->quote($post['sw']['lng']) . ' OR c.longitude <= ' . $db->quote($post['ne']['lng']) . ')'.
                                               ' AND (c.latitude <= ' . $db->quote($post['ne']['lat']) . ' AND c.latitude >= ' . $db->quote($post['sw']['lat']) . ') )'
                                            ;
                                }
                            }
                        }
                        
                        //apply radius search
                        if($search_enable && $enable_radius && isset($post['members']['location'],$post['members']['location_lat'],$post['members']['location_lng'],$post['members']['search_radius'])
                         && 
                         !empty($post['members']['location']) && is_numeric($post['members']['location_lat']) && is_numeric($post['members']['location_lng'])
                         && is_numeric($post['members']['search_radius']) && ($post['members']['search_radius'] > 0)
                         )
                        {                            
                            // in KM
                            $distance_col_expression = "(((acos(sin((".$post['members']['location_lat']."*pi()/180)) *
            					sin((c.latitude * pi()/180))+cos((".$post['members']['location_lat']." * pi()/180)) *
            					cos((c.latitude * pi()/180)) * cos(((".$post['members']['location_lng']."- c.longitude)
            					*pi()/180))))*180/pi())*60*1.1515*1.609344) 
            					";
                            $query .= ' AND '.$distance_col_expression .' <= '.$post['members']['search_radius'];
                            //$distance_col_sql = ", $distance_col_expression as distance ";
                        }
    
    					if($obj->fieldType == 'birthdate')
    					{
    						$this->_birthdateFieldHelper($obj);
    					}
    
    					$query	.= ' WHERE b.'.$db->quoteName('fieldcode').' = ' . $db->Quote($obj->field);
    					$query	.= ' AND ' . $this->_mapConditionKey($obj->condition, $obj->fieldType, $obj->value);
    
    					// Privacy
    					$my		= CFactory::getUser();
    					$query	.= ' AND( ';
    
    					// If privacy for this field is 0, then we just display it.
    					$query	.= ' (a.'.$db->quoteName('access').' = '.$db->Quote('0').')';
    					$query	.= ' OR';
    
    					// If privacy for this field is set to site members only, ensure that the user id is not empty.
    					$query	.= ' (a.'.$db->quoteName('access').' = '.$db->Quote('20').' AND ' . $db->Quote( $my->id ) . '!=0 )';
    					$query	.= ' OR';
    
    					// If privacy for this field is set to friends only, ensure that the current user is a friend of the target.
    					$query	.= ' (a.'.$db->quoteName('access').' = '.$db->Quote('30').' AND a.'.$db->quoteName('user_id').' IN (
    									SELECT c.'.$db->quoteName('connect_to').' FROM '.$db->quoteName('#__community_connection') .' AS c'
    									.' WHERE c.'.$db->quoteName('connect_from').'=' . $db->Quote( $my->id ) . ' AND c.'.$db->quoteName('status').'='.$db->Quote('1').')	)';
    					$query	.= ' OR';
    
    					// If privacy for this field is set to the owner only, ensure that the id matches.
    					$query	.= ' (a.'.$db->quoteName('access').' = '.$db->Quote('40').' AND a.'.$db->quoteName('user_id').'=' . $db->Quote( $my->id ) . ')';
    
    					$query	.= ')';
    
    					$query	.= ($join == 'or') ? ' )' : '';
    
    					if($itemCnt < ($filterCnt - 1) && $join == 'and')
    					{
    						$query	.= ' AND '.$db->quoteName('user_id').' IN (';
    					}
    
    				}
    				$itemCnt++;
    			}
    
    			$closeTag	= '';
    			if($itemCnt > 1)
    			{
    				for($i = 0; $i < ($itemCnt - 1); $i++)
    				{
    					$closeTag .= ' )';
    				}
    			}
    
    			$query	= ($join == 'and') ? $query . $closeTag : $query;    
    		}
            else//search all users in the range
            {
                $query = "( SELECT DISTINCT( b.`userid` ) as `user_id`, a.`username` AS `username`, a.`name` AS `name`, a.`registerDate` AS `registerDate`, CASE WHEN s.`userid` IS NULL THEN 0 ELSE 1 END AS online 
                        FROM `#__users` AS a LEFT JOIN `#__session` AS s ON a.`id`=s.`userid` INNER JOIN `#__community_users` AS b ON a.`id` = b.`userid` AND a.`block` = '0' 
                     ";
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
                $query .= " WHERE  b.latitude != 255 AND  b.longitude != 255";
                //apply radius search
                if($search_enable && $enable_radius && isset($post['members']['location'],$post['members']['location_lat'],$post['members']['location_lng'],$post['members']['search_radius'])
                 && 
                 !empty($post['members']['location']) && is_numeric($post['members']['location_lat']) && is_numeric($post['members']['location_lng'])
                 && is_numeric($post['members']['search_radius']) && ($post['members']['search_radius'] > 0)
                 )
                {                            
                    // in Miles
                    //to convert into KM please use 60*1.1515*1.609344
                    //to convert into Mile please use 60*1.1515
                    //1 Mile = 1.609344 KM
                    $distance_col_expression = "(((acos(sin((".$post['members']['location_lat']."*pi()/180)) *
    					sin((b.latitude * pi()/180))+cos((".$post['members']['location_lat']." * pi()/180)) *
    					cos((b.latitude * pi()/180)) * cos(((".$post['members']['location_lng']."- b.longitude)
    					*pi()/180))))*180/pi())*60*1.1515) 
    					";
                    $query .= ' AND '.$distance_col_expression .' <= '.$post['members']['search_radius'];
                    //$distance_col_sql = ", $distance_col_expression as distance ";
                }
                //added by sam end
                $query .= ")";
            }
    		return $query;
    	}
        /**
         * Community advanced search
         * copied from search model of com_community front end
        */
        public function _mapConditionKey($condition, $fieldType='text', $value, $fieldname = '')
    	{
    		$db	= JFactory::getDBO();
    		//the date time format for birthdate field is stored incorrectly, force to format
    		if($fieldType=='birthdate' || $fieldType=='date'){
    			$condString	= (empty($fieldname)) ? ' DATE_FORMAT(a.'.$db->quoteName('value') .",'%Y-%m-%d %H:%i:%s')" : ' a.'.$db->quoteName($fieldname) ;
    		} else {
    			$condString	= (empty($fieldname)) ? ' a.'.$db->quoteName('value') : ' a.'.$db->quoteName($fieldname) ;
    		}
    
    		switch($condition)
    		{
    			case 'between':
    				//for now assume the value is date.
    				$startVal	= '';
    				$endVal		= '';
    				if(is_array($value))
    				{
    					$startVal	= $value[0];
    					$endVal		= $value[1];
    				}
    				else
    				{
    					$startVal	= $value;
    					$endVal		= $value;
    				}
    				$condString	.= ' BETWEEN ' . $db->Quote($startVal) . ' AND ' . $db->Quote($endVal);
    				break;
    
    			case 'equal':
    				if($fieldType != 'text' && $fieldType != 'select' && $fieldType != 'singleselect' && $fieldType != 'email' && $fieldType != 'radio') //this might be the list, select and etc. so we use like.
    				{
    					$chkOptionValue	= explode(',', $value);
    
    					if($fieldType == 'checkbox' && count($chkOptionValue) > 1)
    					{
    						$chkValue	= array_shift($chkOptionValue);
    						$condString = '(' . $condString;
    						$condString	.= ' LIKE ' . $db->Quote('%'.$chkValue.'%');
    						foreach($chkOptionValue as $chkValue)
    						{
    							$condString	.= (empty($fieldname)) ? ' OR a.'.$db->quoteName('value') : ' OR a.'.$db->quoteName($fieldname);
    							$condString	.= ' LIKE ' . $db->Quote('%'.$chkValue.'%');
    						}
    						$condString	.= ')';
    					}
    					else
    					{
    						$condString	.= (empty($value))? ' = ' . $db->Quote($value) : ' LIKE ' . $db->Quote('%'.$value.'%');
    					}
    				}
    				else
    				{
    					$condString	.= ' = ' . $db->Quote($value);
    				}
    				break;
    
    			case 'notequal':
    				if($fieldType != 'text' && $fieldType != 'select' && $fieldType != 'singleselect' && $fieldType != 'radio') //this might be the list, select and etc. so we use like.
    				{
    					$chkOptionValue	= explode(',', $value);
    
    					if($fieldType == 'checkbox' && count($chkOptionValue) > 1)
    					{
    						$chkValue	= array_shift($chkOptionValue);
    						$condString = '(' . $condString;
    						$condString	.= ' NOT LIKE ' . $db->Quote('%'.$chkValue.'%');
    						foreach($chkOptionValue as $chkValue)
    						{
    							$condString	.= (empty($fieldname)) ? ' AND a.'.$db->quoteName('value') : ' AND a.'.$db->quoteName($fieldname);
    							$condString	.= ' NOT LIKE ' . $db->Quote('%'.$chkValue.'%');
    						}
    						$condString	.= ')';
    					}
    					else
    					{
    						$condString	.= ' NOT LIKE ' . $db->Quote('%'.$value.'%');
    						//$condString	.= (empty($value))? ' != ' . $db->Quote($value) : ' NOT LIKE ' . $db->Quote('%'.$value.'%');
    					}
    				}
    				else
    				{
    					$condString	.= ' != ' . $db->Quote($value);
    				}
    				break;
    
    			case 'lessthanorequal':
    				$condString	.= ' <= ' . $db->Quote($value);
    				break;
    
    			case 'greaterthanorequal':
    				$condString	.= ' >= ' . $db->Quote($value);
    				break;
    
    			case 'contain':
    			default :
    				$condString	.= ' LIKE ' . $db->Quote('%'.$value.'%');
    				break;
    		}
    		$condString	.= (empty($join)) ? '' : ')';
    
    		return $condString;
    	}
        // since the user input value is age which is interger,
    	// we need to convert it into datetime
    	private function _birthdateFieldHelper(&$obj)
    	{
    		$is_age = true;
    		$obj->fieldType = 'birthdate';
    
    		//If value is not array, pass it back as array
    		//if(!is_array($obj->value)){
            //            $obj->value = explode(',',$obj->value);
            //        }
    
    
                    //detecting search by age or date
    		if((is_array($obj->value) && strtotime($obj->value[0]) !== false && strtotime($obj->value[1]) !== false)
    			|| (!is_array($obj->value) && strtotime($obj->value))) {
    			$is_age = false;
    		} else {
    			//the input value must be unsign number, else return
    			if(is_array($obj->value)){
    				if (!is_numeric($obj->value[0]) || !is_numeric($obj->value[1]) || intval($obj->value[0]) < 0 || intval($obj->value[1]) < 0){
    					//invalid range, reset to 0
    					$obj->value[0] = 0;
    					$obj->value[1] = 0;
    					return ;
    				}
    				$obj->value[0]	= intval($obj->value[0]);
    				$obj->value[1]	= intval($obj->value[1]);
    			} else {
    				if(!is_numeric($obj->value) || intval($obj->value) < 0){
    					//invalid range, reset to 0
    					$obj->value = 0;
    					return;
    				}
    				$obj->value = intval($obj->value);
    			}
    		}
    
    		// correct the age order
    		if (is_array($obj->value) && ($obj->value[1] > $obj->value[0]))
    		{
    			$obj->value = array_reverse($obj->value);
    		}
    
    		// TODO: something is wrong with comparing the datetime value
    		// in text type instead of datetime type,
    		// e.g. BETWEEN '1955-09-07 00:00:00' AND '1992-09-07 23:59:59'
    		// we can't find '1992-02-26 23:59:59' in the result.
    
    		if ($obj->condition == 'between')
    		{
    			if($is_age){
    				$year0 = $obj->value[0]+1;
    				$year1 = $obj->value[1];
    
    				$datetime0 = new Datetime();
    				$datetime0->modify('-'.$year0 . ' year');
    				$obj->value[0] = $datetime0->format('Y-m-d 00:00:00');
    
    				$datetime1 = new Datetime();
    				$datetime1->modify('-'.$year1 . ' year');
    				$obj->value[1] = $datetime1->format('Y-m-d 23:59:59');
    
    			} else {
    				$value0 = new JDate($obj->value[0]);
    				$obj->value[0] = $value0->format('Y-m-d 00:00:00');
    				$value1 = new JDate($obj->value[1]);
    				$obj->value[1] = $value1->format('Y-m-d 23:59:59');
    			}
    		}
    
    		if ($obj->condition == 'equal')
    		{
    			// equal to an age means the birthyear range is 1 year
    			// so we make it become a range
    			$obj->condition = 'between';
    
    			if($is_age){
    				$age	= $obj->value;
    				unset($obj->value);
    				$year0 = $age + 1;
    				$year1 = $age;
    
    				$datetime0 = new Datetime();
    				$datetime0->modify('-'.$year0 . ' year');
    				$obj->value[0] = $datetime0->format('Y-m-d 00:00:00');
    
    				$datetime1 = new Datetime();
    				$datetime1->modify('-'.$year1 . ' year');
    				$obj->value[1] = $datetime1->format('Y-m-d 23:59:59');
    
    
    			} else {
    				$value0 = new JDate($obj->value);
    				$value1 = new JDate($obj->value);
    				unset($obj->value);
    				$obj->value[0] = $value0->format('Y-m-d 00:00:00');
    				$obj->value[1] = $value1->format('Y-m-d 23:59:59');
    			}
    
    		}
    
    		if ($obj->condition == 'lessthanorequal')
    		{
    			if($is_age){
    				$obj->condition = 'between';
    
    				$year0 = $obj->value+1;
    				unset($obj->value);
    				$datetime0 = new Datetime();
    				$datetime0->modify('-'.$year0 . ' year');
    				$obj->value[0] = $datetime0->format('Y-m-d 00:00:00');
    
    				$datetime1 = new Datetime();
    				$obj->value[1] = $datetime1->format('Y-m-d 23:59:59');
    
    			} else {
    				$obj->condition = 'lessthanorequal';
    				$value0 = new JDate($obj->value);
    				$obj->value = $value0->format('Y-m-d 23:59:59');;
    			}
    		}
    
    		if ($obj->condition == 'greaterthanorequal')
    		{
    			if($is_age){
    				$obj->condition = 'lessthanorequal'; //the datetime logic is inversed
    				$age	= $obj->value;
    				unset($obj->value);
    
    				$year0 = $age;
    
    				$datetime0 = new Datetime();
    				$datetime0->modify('-'.$year0 . ' year');
    				$obj->value = $datetime0->format('Y-m-d 00:00:00');
    
    			} else {
    				$obj->condition = 'between';
    				$value0 = new JDate($obj->value);
    				unset($obj->value);
    
    				$obj->value[0] = $value0->format('Y-m-d 00:00:00');
    				$value1 = new JDate();
    				$obj->value[1] = $value1->format('Y-m-d 23:59:59');
    			}
    		}
    
    		// correct the date order
    		if (is_array($obj->value) && ($obj->value[1] < $obj->value[0]))
    		{
    			$obj->value = array_reverse($obj->value);
    		}
    
    	}
        /**
         * Community advanced search
         * copied from search model of com_community front end
        */
        private function _getSort( $sorting )
        {
            $db	= $this->getDBO();
            $query = '';
            switch( $sorting )
            {
                    case 'online':
                            $query	= 'ORDER BY '.$db->quoteName('online').' DESC';
                            break;
                    case 'alphabetical':
                            $config	= CFactory::getConfig();
                            $query	= ' ORDER BY ' . $db->quoteName($config->get('displayname')) . ' ASC';
                            break;
                    default:
                            $query	= ' ORDER BY '.$db->quoteName('registerDate').' DESC';
                            break;
            }

            return $query;
        }
        
        
        
        /**
         * Get community members count, without filter
        */
        private function GetJomsocialMembersTotal()
        {
            $db	= JFactory::getDBO();
            $query = $db->getQuery(true);
            $query->select('COUNT(*) AS total');
            $query->from($db->quoteName('#__community_users').' a');
            $query->join('inner','#__users AS b ON a.userid=b.id');
			$query->where('b.'.$db->quoteName('block').' = '.$db->Quote('0'));
            $db->setQuery($query);
            $db->query();
            return (int) $db->loadResult();
        }
        
        /**
         * Get community members count, without filter and who has lat long
        */
        public function OnGlobalResultCountPrepare(&$globalResultCount)
        {
            if(!$this->jomsocialInstalled)
                return false;  
            $app = JFactory::getApplication();
            $db	= JFactory::getDBO();
            $query = $db->getQuery(true);
            $query->select('COUNT(*) AS total');
            $query->from($db->quoteName('#__community_users').' a');
            $query->join('inner','#__users AS b ON a.userid=b.id');
			$query->where('b.'.$db->quoteName('block').' = '.$db->Quote('0'));            
            $query->where($db->quoteName('latitude').'!=255 AND '.$db->quoteName('longitude').'!=255');
            $db->setQuery($query);
            $db->query();
            $total = (int) $db->loadResult();
            if($app->isAdmin())//show dashboard icons at backend
            {
                $globalResultCount = $globalResultCount + $total;
                $document = JFactory::getDocument();
                $document->addStyleSheet(JURI::root().'plugins/hellomaps/members/css/backend_style.css');
                $percentageBlock = "<div class='dashboardPlugin membersDashboardPlugin'> 
                                       <div clas='icon_and_count'>
                                           <div class='membersIcon'><img src='".JURI::root().'plugins/hellomaps/members/images/dashboard_icon.png'."'/></div>                                       
                                           <div class='markerCount'>
                                            ".number_format($total,0,'',',')."
                                           </div>
                                       </div>
                                       <div class='dashboard_title'>".$this->params->get('tab_title','Members')."</div>
                                    </div>";
                echo $percentageBlock;    
            }
            else
            {
                $memberCountWithoutFilter = $this->GetJomsocialMembersTotal();
                $percentage = 0;
                if($memberCountWithoutFilter > 0)
                {
                    $percentage = ceil(($total / $memberCountWithoutFilter) * 100);    
                }    
                $globalResultCount = $globalResultCount + $total;
                $percentageBlock = "<div class='percentageBlock membersTotalBlock'>
                                       <span class='icon'></span>".JText::sprintf('PLG_HELLOMAP_MEMBERS_TOTAL_LABEL',$percentage)."
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
            if(!$this->jomsocialInstalled)
                return false;  
            global $allowedPluginsInModule;
            if(!isset($allowedPluginsInModule) || in_array($this->filter_id,$allowedPluginsInModule))
            {
                $show_notice_area = (boolean)$this->params->get('show_notice_area',0);
                $notice_area_text = $this->params->get('notice_area_text','');
                $notice_type = HelloMapsHelper::GetConfiguration('notice_type','global');
                if($notice_type == "by_plugins" && $show_notice_area && $notice_area_text != "")
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
            if(!$this->jomsocialInstalled)
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
                    <div id="notice_box_holder_members_plugin" class="notice_box_holder notice_box_holder_plugin noticePositions<?php echo $notice_position.$infoLinkClass; ?>" style="display:none;width:<?php echo $sidebar_width; ?>px;">
                        <a class="notice_close plugin_notice_close_button" href="javascript:void(0);">X</a>                    
                        <div class="notice_box_container_plugin">
                            <div class="noticeBlock plugin">
                                <div class="plugnNotice membersNotice">
                                    <div class="notice_plugin_header members_notice_header"><?php echo JText::_('PLG_HELLOMAP_MEMBERS_NOTICE_HEADER_TEXT'); ?></div>
                                    <div class="plugin_notice_content members_notice_content">
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
            if(!$this->jomsocialInstalled)
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
            if(!$this->jomsocialInstalled)
                return false;  
            $hellomapPluginsEnabled[$this->filter_id] = $this->params->get('tab_title','Members');
        }
        
        private function isJomsocialInstalled()
        {
          /*  $db		= JFactory::getDBO();
            $sql = 'SELECT COUNT(*) AS total FROM #__extensions WHERE element="com_community" AND enabled=1';
            $db->setQuery( $sql );
            $db->query();
            return ($db->loadResult() == 1);*/
			$db = JFactory::getDbo();
			$db->setQuery("SELECT enabled,name,element FROM #__extensions WHERE name = 'community' or element = 'com_community' AND enabled=1");
            return ($db->loadResult() == 1);
        }
	}	
    
    
}

