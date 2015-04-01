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
if(!class_exists('AdsmanagerHelperSelect') && is_file(JPATH_SITE.'/components/com_adsmanager/lib/core.php'))
	require_once(JPATH_SITE.'/components/com_adsmanager/lib/core.php');
if(!class_exists('HelloMapsHelper'))
    require_once(JPATH_ADMINISTRATOR.'/components/com_hellomaps/helpers/hellomaps.php');
/**
 * $allowedPluginsInModule
 * It is really important when you use this plugin inside module. tabs will be shown if the plugin is selected at backend, otherwise no action will be takn
 * It is not important for component and will be null
*/
if(!class_exists('plgHellomapsAdsmanager'))
{
	class plgHellomapsAdsmanager extends JPlugin
	{
		var $name		= 'Adsmanager';
		private $filter_id  = 'adsmanager';//Must be unqiue for each plugin, we will trigger javascript functions by this
        private $adsmanagerInstalled = false;
        
	
	    function plgHellomapsAdsmanager(& $subject, $config)
	    {		
			parent::__construct($subject, $config);
			//load language file
			$language = JFactory::getLanguage();
			$language->load('plg_hellomaps_adsmanager', JPATH_ADMINISTRATOR, $language->getName(), true);            
            $this->adsmanagerInstalled = $this->isAdsmanagerInstalled();
	    }
		
		public function onFilterListPrepare(&$filters) {  
		    if(!$this->adsmanagerInstalled)
                return false;  
		    global $allowedPluginsInModule;
            if(isset($allowedPluginsInModule) && in_array($this->filter_id,$allowedPluginsInModule)== false)
            {
                return false;
            }
			$document = JFactory::getDocument();
			$tabTitle = $this->params->get('tab_title','Ads');
            $marker_width = (int)$this->params->get('marker_icon_width',45);
            $marker_height = (int)$this->params->get('marker_icon_height',45);
            $show_in_sidebar = (int)$this->params->get('show_in_sidebar',1);
            $show_in_sidebar = (int)$this->params->get('show_in_sidebar',1);
            $enable_adsmanager_detail_sidebar = (int)$this->params->get('enable_adsmanager_detail_sidebar',1);
			$filterElementsHTML = $this->GetFilterElements();
			$filters[] = array('title'=>$tabTitle,'filter_id'=>$this->filter_id,'content'=>$filterElementsHTML,'show_in_sidebar'=>$show_in_sidebar);
			JHtml::_('bootstrap.framework');//load bootstrap framework of joomla	
            $document->addCustomTag('<link rel="stylesheet" href="'.JURI::root().'plugins/hellomaps/adsmanager/css/style.css'.'" type="text/css" />');//to add css at last
            $document->addScriptDeclaration("var adsmanager_marker_type='".$this->params->get('marker_icon','ad_thumb')."';\n
                                            var adsmanager_marker_width=".$marker_width.";\n
                                            var adsmanager_marker_height=".$marker_height.";\n     
                                            var adsmanager_show_in_sidebar=".$show_in_sidebar.";\n    
                                            var enable_adsmanager_detail_sidebar=".$enable_adsmanager_detail_sidebar.";\n                                          
                                            ");            
			$document->addScript(JURI::root().'plugins/hellomaps/adsmanager/js/script.js');				
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
			$adsmanagerCategories = $this->GetAdsManagerCategories();			
            $search_enable = HelloMapsHelper::GetConfiguration('search_enable',1);//from component
            $search_enable_radius = HelloMapsHelper::GetConfiguration('search_enable_radius',1);//from component
            $contents_enable      = HelloMapsHelper::GetConfiguration('contents_enable',0);//to put the result html in the sidebar...
			
			ob_start();
			include(JPATH_ROOT.'/plugins/hellomaps/adsmanager/views/filter.php');
			$filterElementsHTML = ob_get_contents();
			ob_end_clean();
			return $filterElementsHTML;
		}
		private function GetAdsManagerCategories()
		{
		  $db =JFactory::getDBO();
	      $query = "SELECT * FROM #__adsmanager_categories as c WHERE c.published = 1 ORDER BY c.ordering ASC";
	      $db->setQuery($query);
	      return $db->loadObjectList();
		}
		/**
		 * [onHellomapSearch description]
		 * @param  [type] $litsenerName [only search when litsener name is same as the plugins filter_id]
		 * @param  [type] $searchData   [description]
		 * @return [type]               [description]
		 */
		public function onHellomapSearch($litsenerName,$searchParam,&$searchResult)
		{
		    if(!$this->adsmanagerInstalled)
                return false;  
		    if(!class_exists('JHTMLAdsmanagerField'))
                require_once(JPATH_ROOT."/components/com_adsmanager/helpers/field.php");
			if($litsenerName == $this->filter_id)
			{
				$show_search = $this->params->get('show_search',1);//show searchbox
				$show_filters = $this->params->get('show_filters',1);//show filters
				$search_fields = $this->params->get('search_fields',array());//show filters
                $fitler_categories = $this->params->get('filters_categories',array());//show filters
                $details_extra_fields = $this->params->get('details_extra_fields',array());//extra fields                
                $respect_privacy    = $this->params->get('respect_privacy',0);
                $marker_icon        = $this->params->get('marker_icon','ad_thumb');
                $custom_marker_image= $this->params->get('custom_marker_image','');
                $markers_name         = $this->params->get('markers_name',''); //json data for each category                
                $display_marker_infowindow = (boolean)$this->params->get('display_marker_infowindow',1) && HelloMapsHelper::GetConfiguration('infowindow_enable',1); //json data for each category
                $default_marker_icon_url = JURI::root().'plugins/hellomaps/adsmanager/images/markers/black.png';
                
                JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_adsmanager/models');
                $this->adsmanagerFieldModel = JModelLegacy::getInstance( 'Field', 'AdsmanagerModel' );
                $this->adsmanagerConfigurationModel = JModelLegacy::getInstance( 'Configuration', 'AdsmanagerModel' );
                
                $fieldmodel = $this->adsmanagerFieldModel;
                $configurationmodel = $this->adsmanagerConfigurationModel;
                $conf = $configurationmodel->getConfiguration();
                $field_values = $fieldmodel->getFieldValues();
                $extaFieldsOBjects = array();
                
                $mode = 0;
                //adsmanager is generating warning from
                //Only variables should be assigned by reference in images\com_adsmanager\plugins\AdsManagerGTMapLocator\plug.php on line 356
                @$plugins = $fieldmodel->getPlugins();
                
                
                $this->adsManagerField = new JHTMLAdsmanagerField($conf,$field_values,$mode,$plugins);
                
                $ad_priceField = $fieldmodel->getFieldsByName('"ad_price"');
                if(!empty($ad_priceField))
                {
                    $this->ad_priceField = array_pop($ad_priceField);
                }
                if(!empty($details_extra_fields))
                {
                    
                    foreach($details_extra_fields as $details_extra_field)
                    {
                        $fieldObj = $fieldmodel->getFieldsByName('"'.$details_extra_field.'"');
                        if(!empty($fieldObj))
                        {
                            $extaFieldOBjects[$details_extra_field] = array_pop($fieldObj);
                        }
                    }
                }
                
                
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
                    $adsmanagerFilter = array();
                    $adsmanagerFilter['publish'] = 1;
                    $adsmanagerFilter['search'] = $search_text;
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
                            $adsmanagerFilter['categories'] = $searchedCategoryIds;
                        }
                    }
                    $results = $this->getAdsmanagerProducts($adsmanagerFilter);
                    if(!empty($results))
                    {
                        foreach($results as $result)
                        {
                            $linkTarget = TRoute::_( "index.php?option=com_adsmanager&view=details&id=".$result->id."&catid=".$result->catid);
                            $row       = array(); 
                            $row['id'] = $result->id;
                            $row['title'] = $result->ad_headline;
                            $thumb = ADSMANAGER_NOPIC_IMG;//default pic
                            $largeAvatar = ADSMANAGER_NOPIC_IMG;
                            if(!empty($result->images))
                            {
                                $thumb = JURI::base()."images/com_adsmanager/ads/".$result->images[0]->thumbnail;
                                $largeAvatar = JURI::base()."images/com_adsmanager/ads/".$result->images[0]->image;
                            }
                            $row['thumb'] = $thumb;
                            $row['largeAvatar'] = $largeAvatar;
                            $row['latitude'] = (float)$result->ad_googlemaps_lat; 
                            $row['longitude'] = (float)$result->ad_googlemaps_lng;                            
                            $row['category'] = $result->parent." / ".$result->cat;
                            
                            $row['price']    = $result->ad_price;
                            
                            $marker_icon_url = $default_marker_icon_url;
                            if($marker_icon == 'ad_thumb')
                            {
                                $marker_icon_url = $row['thumb'];
                            }
                            else if($marker_icon == 'ad_category' && isset($categoryMarkers[$result->catid]))
                            {
                                $marker_icon_path = JPATH_SITE.'/plugins/hellomaps/adsmanager/images/markers/'.$categoryMarkers[$result->catid];
                                if(is_file($marker_icon_path))
                                    $marker_icon_url = JURI::base().'plugins/hellomaps/adsmanager/images/markers/'.$categoryMarkers[$result->catid];
                            }
                            else if($marker_icon == 'custom' && $custom_marker_image != "")
                            {
                                $marker_icon_url = $custom_marker_image;
                            }
                            $row['marker_icon_url'] = $marker_icon_url;
                            $row['display_marker_infowindow'] = $display_marker_infowindow;
                            $row['profileLink'] = TLink::getUserAdsLink($result->userid);
                            $row['userAvatar'] = $this->GetAdsmanagerUserAvatar($result->userid);
                            $ad_price = 'N/A';
                            if(!empty($this->ad_priceField))
                            {
                                $ad_price = $this->adsManagerField->showFieldValue($result,$this->ad_priceField);    
                            }
                            $row['ad_price_full'] = $ad_price;
                            $row['ad_text'] = $result->ad_text;
                            $row['ad_link'] = $linkTarget;
                            $location = array();
                            if(!empty($result->ad_country))
                            {
                                $location[] = $result->ad_country;
                            }
                            if(!empty($result->ad_city))
                            {
                                $location[] = $result->ad_city;
                            }
                            $row['location'] = implode('/ ',$location);
                            $row['category_text'] = $result->parent." / ".$result->cat;
                            $row['images'] = $result->images;
                            if(!empty($details_extra_fields) && !empty($extaFieldOBjects))
                            {
                                $extraFieldsData = array();
                                foreach($details_extra_fields as $details_extra_field)
                                {
                                    $fieldsData['label'] = JText::_($extaFieldOBjects[$details_extra_field]->title);
                                    $fieldsData['value'] = $this->adsManagerField->showFieldValue($result,$extaFieldOBjects[$details_extra_field]);
                                    $extraFieldsData[$details_extra_field] =  $fieldsData;
                                }
                                $row['extraFields'] = $extraFieldsData;
                            }
                            if($display_marker_infowindow)
                            {
                                $row['infowindow_content'] = $this->GetMarkerInfoWindowContent($row);
                            }               
                            $row['html'] = $this->GetSearchResultHTML($row);             
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
                    $adsCountWithoutFilter = $this->GetAdsmanagerAdsTotal();
                    $percentage = 0;
                    if($adsCountWithoutFilter > 0)
                    {
                        $percentage = ceil(($total / $adsCountWithoutFilter) * 100);    
                    }    
                    $percentageBlock = "<div class='percentageBlock adsTotalBlock'>
                                           <span class='icon'></span>".JText::sprintf('PLG_HELLOMAP_ADSMANAGER_TOTAL_LABEL',$percentage)."
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
            
            
                        
            
            
            $markerData['ad_text'] = str_replace ('<br />'," ",$markerData['ad_text']);
            $af_text = JString::substr($markerData['ad_text'], 0, 100);
            if (strlen($markerData['ad_text'])>100) {
            	$af_text .= "[...]";
            }	
            
            $markerInfoWindowWidth = HelloMapsHelper::GetConfiguration('infowindow_width',150);
            if(is_numeric($markerInfoWindowWidth))
                $markerInfoWindowWidth = $markerInfoWindowWidth.'px';
            $markerInfoWindowHeight = HelloMapsHelper::GetConfiguration('infowindow_height',150);
            if(is_numeric($markerInfoWindowHeight))
                $markerInfoWindowHeight = $markerInfoWindowHeight.'px';
            ob_start();
			include(JPATH_ROOT.'/plugins/hellomaps/adsmanager/views/marker_info_window.php');
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
			include(JPATH_ROOT.'/plugins/hellomaps/adsmanager/views/search_result.php');
			$searchResultHTML = ob_get_contents();
			ob_end_clean();
			return $searchResultHTML;
        }
        
        private function GetAdsmanagerUserAvatar($userid)
        {
            $db = JFactory::getDBO();
            $avatar = JURI::base().'plugins/hellomaps/adsmanager/images/no_avatar.png';
            if (COMMUNITY_BUILDER == 1) {
                $query = $db->getQuery(true);
                $query->select($db->quoteName('avatar'));
                $query->from($db->quoteName('#__comprofiler'));
                $query->where($db->quoteName('user_id').'='.$userid);     
                $query->where($db->quoteName('avatar').'!=""');
                $query->where($db->quoteName('avatarapproved').'=1');
                $db->setQuery($query);
                $db->query();
                $avatarSrc = $db->loadResult();
                if($avatarSrc != "")
                {
                    //check , is it from gallery
                    if(strpos($avatarSrc,'gallery/') === false)//return the thumb version
                    {
                        if(is_file(JPATH_SITE.'/images/comprofiler/tn'.$avatarSrc))
                            $avatar = JURI::root().'images/comprofiler/tn'.$avatarSrc;
                    }
                    else
                    {
                        $avatar = JURI::root().'images/comprofiler/'.$avatarSrc;
                    }
                }
    		} 
            else if (JOMSOCIAL == 1) 
            {
    		    if(!defined('COMMUNITY_COM_PATH'))
                {
                	require_once JPATH_ROOT . '/components/com_community/defines.community.php';
                	require_once COMMUNITY_COM_PATH . '/libraries/error.php';	
                	require_once COMMUNITY_COM_PATH . '/libraries/apps.php';
                	require_once COMMUNITY_COM_PATH . '/libraries/core.php';
                }
                $usr = CFactory::getUser( $userid );
                $avatar = $usr->getThumbAvatar();
    		} 
            return $avatar;
        }
        
        
        /**
         * copied from adsmanager content model at backend
         * function getContents
         * for searching product
        */
        private function getAdsmanagerProducts($filters = null,$limitstart=null,$limit=null,$filter_order=null,$filter_order_Dir=null,$admin=0,$favorite=0)
        {
            $search_enable       = HelloMapsHelper::GetConfiguration('search_enable',0);
            $enable_radius       = HelloMapsHelper::GetConfiguration('search_enable_radius',0);
        
            $db = JFactory::getDBO();
            $post = JRequest::get('post');
            $enable_zoom_counter = $this->params->get('enable_zoom_counter',0);
            $distance_col_sql = '';
            $distance_col_expression = '';
            $apply_radius_condition = false;
            $respect_privacy    = $this->params->get('respect_privacy',0);
            
            //check the field exists or not
            if($respect_privacy)
            {
                $privacy_field_check_sql = 'DESCRIBE '.$db->quoteName('#__adsmanager_ads');
                $db->setQuery($privacy_field_check_sql);
                $db->query();
                $adTableComuns = $db->loadAssocList();
                $privacy_field_exists = false;
                foreach($adTableComuns as $adTableComun)
                {
                    if($adTableComun['Field'] == 'ad_respectprivacy')
                    {
                        $privacy_field_exists = true;
                        break;
                    }
                }
                if(!$privacy_field_exists)
                {
                    return null;
                }
            }
            
            
            if($search_enable && $enable_radius && isset($post['adsmanager']['location'],$post['adsmanager']['location_lat'],$post['adsmanager']['location_lng'],$post['adsmanager']['search_radius'])
             && 
             !empty($post['adsmanager']['location']) && is_numeric($post['adsmanager']['location_lat']) && is_numeric($post['adsmanager']['location_lng'])
             && is_numeric($post['adsmanager']['search_radius']) && ($post['adsmanager']['search_radius'] > 0)
             )
            {
                $apply_radius_condition = true;
                // in KM
                //to convert into KM please use 60*1.1515*1.609344
                //to convert into Mile please use 60*1.1515
                //1 Mile = 1.609344 KM
                $distance_col_expression = "(((acos(sin((".$post['adsmanager']['location_lat']."*pi()/180)) *
					sin((a.ad_googlemaps_lat * pi()/180))+cos((".$post['adsmanager']['location_lat']." * pi()/180)) *
					cos((a.ad_googlemaps_lat * pi()/180)) * cos(((".$post['adsmanager']['location_lng']."- a.ad_googlemaps_lng)
					*pi()/180))))*180/pi())*60*1.1515) 
					";
                //$distance_col_sql = ", $distance_col_expression as distance ";
            }
            
        	$sql = "SELECT a.*, p.name as parent, p.id as parentid, c.name as cat, c.id as catid,u.username as user,u.name as fullname ".$distance_col_sql.
    			" FROM #__adsmanager_ads as a ".
    			" INNER JOIN #__adsmanager_adcat as adcat ON adcat.adid = a.id ";
    		
    		$sql .=	" LEFT JOIN #__users as u ON a.userid = u.id ".
    			" INNER JOIN #__adsmanager_categories as c ON adcat.catid = c.id ".
    			" LEFT JOIN #__adsmanager_categories as p ON c.parent = p.id ";
            
            if($favorite != 0) {
                $sql .= " INNER JOIN #__adsmanager_favorite as adfav ON a.id = adfav.adid";
            }
            
      		$filter = $this->_getSQLFilter($filters);
            $sql .= $filter;
            
            //add the bound restriction if exist
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
                    $and_prefix = ""; 
                    if($filter != null)
                        $and_prefix = " AND "; 
                    if ($post['ne']['lng'] > $post['sw']['lng'])
                    {
                        $sql .= $and_prefix.'  ( (a.ad_googlemaps_lng > ' . $db->quote($post['sw']['lng']) . ' AND a.ad_googlemaps_lng < ' . $db->quote($post['ne']['lng']) . ')'.
                                     ' AND (a.ad_googlemaps_lat <= ' . $db->quote($post['ne']['lat']) . ' AND a.ad_googlemaps_lat >= ' . $db->quote($post['sw']['lat']) . ') )'
                                ;                        
                    }
                    else
                    {
                        $sql .= $and_prefix.'  ( (a.ad_googlemaps_lng >= ' . $db->quote($post['sw']['lng']) . ' OR a.ad_googlemaps_lng <= ' . $db->quote($post['ne']['lng']) . ')'.
                                   ' AND (a.ad_googlemaps_lat <= ' . $db->quote($post['ne']['lat']) . ' AND a.ad_googlemaps_lat >= ' . $db->quote($post['sw']['lat']) . ') )'
                                ;
                    }
                }
            }
            
            
            
            
               
            if($favorite != 0) {
                if($filter != null)
                    $prefix = " AND ";
                else
                    $prefix = " WHERE ";
                $sql .= $prefix."adfav.userid = ".(int)$favorite." ";
            }
            
            if($admin != 1) {
    		
                if(version_compare(JVERSION, '1.6', 'ge')) {
                    
                    $listCategories = TPermissions::getAuthorisedCategories('read');
    
                    //If the variable is an array and if it's not empty, we add a filter to the request
                    //If not we're not return any category
                    if(is_array($listCategories) && !empty($listCategories)){
                        $categories = implode(',',$listCategories);
                        $listCategories = " AND c.id IN (".$categories.") "; 
                    }else{
                        $listCategories = " AND 0 ";
                    }
                    
                } else {
                    $listCategories = "";
                }
    				
    			$sql .= $listCategories;
    		
    		}
            
            //added by sam for radius search
            if($apply_radius_condition)
            {
                $sql .= ' AND '.$distance_col_expression.' <= '.$post['adsmanager']['search_radius'];
            }
            
            //apply privacy condition
            if($respect_privacy)
            {
                $sql .= ' AND a.'.$db->quoteName('ad_respectprivacy').'!=1';
            }
            
        	if ($filter_order === null) {
        		$sql .= " GROUP BY a.id";
        	} else {
        		$sql .= " GROUP BY a.id ORDER BY $filter_order $filter_order_Dir ";
        	}
        	if (($admin == 0)&&(function_exists("updateQueryWithReorder")))
        		updateQueryWithReorder($sql);
        	else if (($admin == 1)&&(function_exists("updateQuery")))
        		updateQuery($sql);
            
            
            
        	if ($limitstart === null) {
        		$db->setQuery($sql);
        	} else {
        		$db->setQuery($sql,$limitstart,$limit);
        	}
        	//echo str_replace('#__','f9leh_',$sql);exit;
        	$products = $db->loadObjectList();
        	
        	foreach($products as &$product) {
        		$product->cat = JText::_($product->cat);
        		if ($product->parent != "")
        			$product->parent = JText::_($product->parent);
        		$product->images = @json_decode($product->images);
        		if (!is_array($product->images))
        			$product->images = array();
        	}
        	
    		return $products;	
        }
        /**
         * copied from adsmanager content model at backend
         * function _getSQLFilter
         * for building searching product sql from filter
        */
        private function _getSQLFilter($filters){
            $db = JFactory::getDBO();
            $search_fields = $this->params->get('search_fields',array());//in these columns like search will be performed for the search text
       		 /* Filters */
            $search = "";
            
        	if (isset($filters))
        	{
    	    	foreach($filters as $key => $filter)
    	    	{
    	    		if ($search == "")
    	    			$temp = " WHERE ";
    	    		else
    	    			$temp = " AND ";
    	    		switch($key)
    	    		{
    	    			case 'category':
    	    				$catid = $filter;
    	    				$db->setQuery( "SELECT c.id, c.name,c.parent ".
    						" FROM #__adsmanager_categories as c ".
    						 "WHERE c.published = 1 ORDER BY c.parent,c.ordering");
    						 
    						$listcats = $db->loadObjectList();
    						$list[] = $catid;
    						$this->_recurseSearch($listcats,$list,$catid);
    						$listids = implode(',', $list);
    	    				$search .= $temp."c.id IN ($listids) ";break;
                        case 'categories':
    	    				$catids = $filter;
                            JArrayHelper::toInteger($catids);
    						$listids = implode(',', $catids);
    	    				$search .= $temp."c.id IN ($listids) ";break;
    	    			case 'user':
    	    				$search .= $temp."u.id = ".(int)$filter;break;
    	    			case 'username':
                            if (version_compare(JVERSION,'1.7.0','<')) {
                                $search .= $temp."u.username LIKE '%".$db->getEscaped($filter,true)."%'";
                            }else{
                                $search .= $temp."u.username LIKE '%".$db->escape($filter,true)."%'";
                            }
                            break;
    	    			case 'content_id':
    	    				$search .= $temp."a.id = ".(int)$filter;break;
    	    			case "phone":
                            if (version_compare(JVERSION,'1.7.0','<')) {
                            	$search .= $temp." a.ad_phone LIKE '%".$db->getEscaped($filter,true)."%'";
                            }else{
                            	$search .= $temp." a.ad_phone LIKE '%".$db->escape($filter,true)."%'";
                            }
                            break;
    	    			case "ip":
                            if (version_compare(JVERSION,'1.7.0','<')) {
                                $search .= $temp." a.ad_ip LIKE '%".$db->getEscaped($filter,true)."%'";
                            }else{
                                $search .= $temp." a.ad_ip LIKE '%".$db->escape($filter,true)."%'";
                            }
                            break;
    	    			case 'mag':
    	    				$search .= $temp."a.ad_magazine = ".$db->Quote($filter);break;
    	    			case "online":
    	    				if ($filter == 1) {
    	    					$search .= $temp." (a.ad_publishtype = 'online' OR a.ad_publishtype = 'both')";
    	    				} else
    	    					$search .= $temp." (a.ad_publishtype = 'offline' OR a.ad_publishtype = 'both')";
    	    				break;
    	    			
    	    			
    	    			case 'publish':
    	    				$search .= $temp." a.published = ".(int)$filter." AND c.published = TRUE ";break;
    	    			case 'fields':
    	    				$search .= $temp.$filter;break;
    	    			case 'search':
                            $filter = JString::strtolower($filter);
                            
                            $orWhereStr = '';
                            if(!empty($search_fields))
                            {
                                $orWhere = array();
                                foreach($search_fields as $search_field)
                                {
                                    $orWhere[] = ' LOWER(a.'.$search_field.') LIKE '.$db->quote('%'.$filter.'%');
                                }    
                                $orWhereStr = ' ('.implode(' OR ',$orWhere).')';       
                                
                                if (intval($filter) != 0) {
        	    					$filter = JString::strtolower($filter);
                                    if(!empty($orWhereStr))
                                        $orWhereStr = ' OR '.$orWhereStr;
        	    					$id = intval($filter);
                                    $search .= $temp."(a.id = $id".$orWhereStr.")";
        	    				} else {
        	    					$filter = JString::strtolower($filter);
                                    $search .= $temp.$orWhereStr;
        	    				}                        
                            }
                            else
                            {
                                $search .= $temp.' 1 ';
                            }
    	    				
    	    				break;
                        case 'publication_date':
                            $search .= $temp." a.publication_date <= NOW()";break;
                        
    	    		}
    	    	}
        	}
        	
        	$currentSession = JSession::getInstance('none',array());
        	
        	$filter = $currentSession->get("sqlglobalfilter","");
        	if ($filter != ""){
        		if (isset($filters['user'])) {
    	    		$conf = TConf::getConfig();
    	    		if (@$conf->globalfilter_user == 0) {
    	    			return $search;
    	    		}
        		}
        		// si on a que le super filter car pas de recherche classique fieldsFilter = WHERE + super filter
        		if($search == " "){
        			$search  = " WHERE $filter ";
        		} else { // si on a une recherche il faut cumuler les deux fieldsFIlters = fieldFilter + AND + super filter
        			$search  .= " AND $filter";
        		}
        	}
        	return $search;
        }
        
        /**
         * Get adsmanager ads count, without filter
        */
        private function GetAdsmanagerAdsTotal()
        {
            $db	= JFactory::getDBO();
            $query = $db->getQuery(true);
            $query->select('COUNT(*) AS total');
            $query->from($db->quoteName('#__adsmanager_ads'));
    		$query->where($db->quoteName('published').' = '.$db->Quote('1'));
            $db->setQuery($query);
            $db->query();
            return (int) $db->loadResult();
        }
        
        /**
         * Get adsmanager ads count, without filter, who has lat long
        */
        public function OnGlobalResultCountPrepare(&$globalResultCount)
        {
            if(!$this->adsmanagerInstalled)
                return false;
            $app = JFactory::getApplication();
            $db	= JFactory::getDBO();            
            $query = $db->getQuery(true);
            $query->select('COUNT(*) AS total');
            $query->from($db->quoteName('#__adsmanager_ads'));
    		$query->where($db->quoteName('published').' = '.$db->Quote('1'));
            $query->where($db->quoteName('ad_googlemaps_lat').' != "" AND '.$db->quoteName('ad_googlemaps_lng').' != ""');
            $db->setQuery($query);
            $db->query();
            $total = (int) $db->loadResult();
            if($app->isAdmin())//show dashboard icons at backend
            {
                $globalResultCount = $globalResultCount + $total;
                $document = JFactory::getDocument();
                $document->addStyleSheet(JURI::root().'plugins/hellomaps/adsmanager/css/backend_style.css');
                $percentageBlock = "<div class='dashboardPlugin adsmanagerDashboardPlugin'> 
                                       <div clas='icon_and_count'>
                                           <div class='adsmanagerIcon'><img src='".JURI::root().'plugins/hellomaps/adsmanager/images/dashboard_icon.png'."'/></div>                                       
                                           <div class='markerCount'>
                                            ".number_format($total,0,'',',')."
                                           </div>
                                       </div>
                                       <div class='dashboard_title'>".$this->params->get('tab_title','Ads')."</div>
                                    </div>";
                echo $percentageBlock;    
            }
            else
            {
                $adsCountWithoutFilter = $this->GetAdsmanagerAdsTotal();
                $percentage = 0;
                if($adsCountWithoutFilter > 0)
                {
                    $percentage = ceil(($total / $adsCountWithoutFilter) * 100);    
                }    
                $globalResultCount = $globalResultCount + $total;
                $percentageBlock = "<div class='percentageBlock adsTotalBlock'>
                                       <span class='icon'></span>".JText::sprintf('PLG_HELLOMAP_ADSMANAGER_TOTAL_LABEL',$percentage)."
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
            if(!$this->adsmanagerInstalled)
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
            if(!$this->adsmanagerInstalled)
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
                    <div id="notice_box_holder_adsmanager_plugin" class="notice_box_holder notice_box_holder_plugin noticePositions<?php echo $notice_position.$infoLinkClass; ?>" style="display:none;width:<?php echo $sidebar_width; ?>px;">
                        <a class="notice_close plugin_notice_close_button" href="javascript:void(0);">X</a>                    
                        <div class="notice_box_container_plugin">
                            <div class="noticeBlock plugin">
                                <div class="plugnNotice adsmanagerNotice">
                                    <div class="notice_plugin_header adsmanager_notice_header"><?php echo JText::_('PLG_HELLOMAP_ADSMANAGER_NOTICE_HEADER_TEXT'); ?></div>
                                    <div class="plugin_notice_content adsmanager_notice_content">
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
            if(!$this->adsmanagerInstalled)
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
            if(!$this->adsmanagerInstalled)
                return false;
            $hellomapPluginsEnabled[$this->filter_id] = $this->params->get('tab_title','Ads');
        }
        
        private function isAdsmanagerInstalled()
        {
          /*$db		= JFactory::getDBO();
            $sql = 'SELECT COUNT(*) AS total FROM #__extensions WHERE element="com_adsmanager" AND enabled=1';
            $db->setQuery( $sql );
            $db->query();
			return ($db->loadResult() == 1);*/
			
			$db = JFactory::getDbo();
			$db->setQuery("SELECT enabled,name,element FROM #__extensions WHERE name = 'com_adsmanager' or element = 'com_adsmanager' AND enabled=1");
            return ($db->loadResult() == 1);
        }
		
	}
}

