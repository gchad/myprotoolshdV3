<?php
/**
 * @version     1.0
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
if(!class_exists('plgHellomapsArticles'))
{
	class plgHellomapsArticles extends JPlugin
	{
		var $name		= 'Articles';
		private $filter_id  = 'articles';//Must be unqiue for each plugin, we will trigger javascript functions by this
        //private $articlesInstalled = false;
        
	
	    function plgHellomapsArticles(& $subject, $config)
	    {		
			parent::__construct($subject, $config);
			//load language file
			$language = JFactory::getLanguage();
			$language->load('plg_hellomaps_articles', JPATH_ADMINISTRATOR, $language->getName(), true);
        
	    }
		
		public function onFilterListPrepare(&$filters) {  
		    global $allowedPluginsInModule;
            if(isset($allowedPluginsInModule) && in_array($this->filter_id,$allowedPluginsInModule)== false)
            {
                return false;
            }
			$document = JFactory::getDocument();
			$tabTitle = $this->params->get('tab_title','Articles');
            $marker_width = (int)$this->params->get('marker_icon_width',45);
            $marker_height = (int)$this->params->get('marker_icon_height',45);
			$show_in_sidebar = (int)$this->params->get('show_in_sidebar',1);
			$enable_article_detail_sidebar = (int)$this->params->get('enable_article_detail_sidebar',1);
			$filterElementsHTML = $this->GetFilterElements();
			$filters[] = array('title'=>$tabTitle,'filter_id'=>$this->filter_id,'content'=>$filterElementsHTML,'show_in_sidebar'=>$show_in_sidebar);
			JHtml::_('bootstrap.framework');//load bootstrap framework of joomla	
            $document->addCustomTag('<link rel="stylesheet" href="'.JURI::root().'plugins/hellomaps/articles/css/style.css'.'" type="text/css" />');//to add css at last
            $document->addScriptDeclaration("var articles_marker_type='".$this->params->get('marker_icon','art')."';\n
                                            var articles_marker_width=".$marker_width.";\n
                                            var articles_marker_height=".$marker_height.";\n   
											var articles_show_in_sidebar=".$show_in_sidebar.";\n    
                                            var enable_article_detail_sidebar=".$enable_article_detail_sidebar.";\n                                                
                                            ");            
			$document->addScript(JURI::root().'plugins/hellomaps/articles/js/script.js');				
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
			$articlesCategories = $this->GetArticlesCategories();			
            $search_enable = HelloMapsHelper::GetConfiguration('search_enable',1);//from component
            $search_enable_radius = HelloMapsHelper::GetConfiguration('search_enable_radius',1);//from component
            $contents_enable      = HelloMapsHelper::GetConfiguration('contents_enable',0);//to put the result html in the sidebar...
			
			ob_start();
			include(JPATH_ROOT.'/plugins/hellomaps/articles/views/filter.php');
			$filterElementsHTML = ob_get_contents();
			ob_end_clean();
			return $filterElementsHTML;
		}
		
		
		private function GetArticlesCategories()
		{
			$db     = JFactory::getDBO();
			$query	= 'SELECT * FROM ' . $db->quoteName( '#__categories' ) . ' '
			. 'WHERE ' . $db->quoteName( 'extension' ) . '='.$db->quote( 'com_content')
			. 'AND ' . $db->quoteName( 'published' ) . '= 1';
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
		    
			if($litsenerName == $this->filter_id)
			{
				
				$show_search = $this->params->get('show_search',1);//show searchbox
				$show_filters = $this->params->get('show_filters',1);//show filters
				$search_fields = $this->params->get('search_fields',array());//show filters
                $fitler_categories = $this->params->get('filters_categories',array());//show filters
                $details_extra_fields = $this->params->get('details_extra_fields',array());//extra fields 
                              
                $respect_privacy    = $this->params->get('respect_privacy',0);
                $marker_icon        = $this->params->get('marker_icon','artcat');
                $custom_marker_image= $this->params->get('custom_marker_image','');
                $markers_name         = $this->params->get('markers_name',''); //json data for each category                
                $display_marker_infowindow = (boolean)$this->params->get('display_marker_infowindow',1) && HelloMapsHelper::GetConfiguration('infowindow_enable',1); //json data for each category
                $default_marker_icon_url = JURI::root().'plugins/hellomaps/articles/images/markers/default.png';
                
                
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
                    $articlesFilter = array();
                    $articlesFilter['publish'] = 1;
                    $articlesFilter['search'] = $search_text;
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
                            $articlesFilter['categories'] =  $searchedCategoryIds ;
							
                        }
                    }
					//new in 1.07fg
					$searchedCategoryIds = isset($searchedCategoryIds);
					//end
					
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
					
					$results = $this->getAdvanceSearch($JUsersFilters, $join , $avatarOnly  , $sorting , $searchedCategoryIds  );
					//print_r($results);
					$db = JFactory::getDBO();  
					
                    if(!empty($results))
                    {
						
                        foreach($results as $key=>$result )
                        {
												
						if($key == 0)
						
							$query = $db->getQuery(true);
						else
							$query->clear();
						
						$attribs = json_decode($result->attribs, true);
						$images = json_decode($result->images, true);
						
						//new in 1.07g risolve notice: undefined index
						 if(empty($attribs['latitude'])) {
									unset($attribs['latitude']);
									continue;
							 }
							 
						 if(empty($attribs['longitude'])) {
									unset($attribs['longitude']);
									continue;
							 }	 
							 
						$lat = $attribs['latitude'];
						$lng = $attribs['longitude'];
						

						if (($lat != 0)||($lng != 0)){
							
							$catid= $result->catid;

							//Creazione Array Valori Markers	
							$row  = array();
							$row['id'] ='';
							$row['id'] = $result->id;
							
							//print_r($row['id']);
							$row['title'] = $result->title;
							$row['latitude'] 	= (float)$lat; 
							$row['longitude']	= (float)$lng; 
							$row['thumb'] = $images['image_intro'];
							$row['largeAvatar'] = $images['image_intro'];
							$row['latestStatus']= strip_tags($result->introtext,'<p><br>'); 
							$row['location'] = $attribs['fullcountry'];
							$row['profileLink']  =  JURI::root().'index.php?option=com_content&view=article&id='.$result->id;
							$row['qr_code_img'] = 'http://chart.apis.google.com/chart?cht=qr&chs=80x80&chl=geo:'.(float)$lat.','.(float)$lng;
							$row['contentDate']	= JHtml::_('date', $result->created_by, 'D F n, Y g:i a');

						   //Get Extra Fields Values 
							if(!empty($details_extra_fields)) {
								
								$details_extra_fields_vk= array();
								foreach ($result as $key => $fieldData){
									if (in_array($key, $details_extra_fields)) {
									 $details_extra_fields_vk[$key] = $fieldData;
									}
								 }
							}
							$row['extraFields'] = $details_extra_fields_vk;
						
							//prende il nomee id  del gruppo:::da passare dentro funzione
							$user = JFactory::getUser();
							$db     = JFactory::getDBO();
							$query  = $db->getQuery(true);
							$query->select('a.title AS group_name, a.id AS catid');
							$query->from('#__categories AS a');
							$query->join('LEFT', '#__content AS b ON b.catid = a.id' );
							$query->where('b.catid = '.$result->catid);
							$query->group($db->quoteName('catid'));
							
							//echo $query; exit;	
							$db->setQuery($query);
							$res = $db->loadObjectList();
							//print_r($res);
							$profileTypeId = $res[0]->catid; 
							$row['profileTypeId'] = $profileTypeId;
							$row['profileTypeName'] = $res[0]->group_name;     
							
							
								$marker_icon_url = $default_marker_icon_url;
								if($marker_icon == 'articleimage')
								{
									$marker_icon_url = $row['thumb'];
									if ($marker_icon_url=="") $marker_icon_url= JURI::root().'plugins/hellomaps/articles/images/markers/default.png';
									
								}
								else if($marker_icon == 'artcat' && isset($categoryMarkers[$profileTypeId]))
								{
									$marker_icon_path = JPATH_SITE.'/plugins/hellomaps/articles/images/markers/'.$categoryMarkers[$profileTypeId];
									if(is_file($marker_icon_path))
										$marker_icon_url = JURI::base().'plugins/hellomaps/articles/images/markers/'.$categoryMarkers[$profileTypeId];
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
							
						}
						
						
                    	}
					}                    
				}      
                          
                //$total = count($results);
				$total=count($rows);
                $display_marker_result_count        = HelloMapsHelper::GetConfiguration('results_enable',0) && (boolean)$this->params->get('display_marker_result_count',0);
                $display_marker_result_count        = $display_marker_result_count && (HelloMapsHelper::GetConfiguration('results_type','byzoom') == 'byzoom');       
                $searchResult[$this->filter_id] = array('total'=>$total,'rows'=>$rows,'display_marker_result_count'=>$display_marker_result_count);

                if($display_marker_result_count)
                {
                    $artsCountWithoutFilter = $this->GetArticlesTotal();
                    $percentage = 0;
                    if($artsCountWithoutFilter > 0)
                    {
                        $percentage = ceil(($total / $artsCountWithoutFilter) * 100);    
                    }    
                    $percentageBlock = "<div class='percentageBlock artsTotalBlock'>
                                           <span class='icon'></span>".JText::sprintf('PLG_HELLOMAPS_ARTICLES_TOTAL_LABEL',$percentage)."
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
			
			//new in 1.07g risolve notice: undefined index
					
            $markerData['ad_text'] = str_replace ('<br />'," ",isset($markerData['ad_text']));
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
			include(JPATH_ROOT.'/plugins/hellomaps/articles/views/marker_info_window.php');
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
			include(JPATH_ROOT.'/plugins/hellomaps/articles/views/search_result.php');
			$searchResultHTML = ob_get_contents();
			ob_end_clean();
			return $searchResultHTML;
        }
       
	    private function _getResults($filter = array(), $join='and' , $avatarOnly = '',  $searchedProfileTypes=array())
        {
          
			$db = JFactory::getDBO();
			$post = JRequest::get('post');
            $enable_zoom_counter = $this->params->get('enable_zoom_counter',0);
			$search_fields = $this->params->get('search_fields',array());//show filters
			$query  = '';
			$itemCnt = 0;
			
			//print_r($filter);
			
			if(!empty($filter))
            {
                	$filterCnt    = count($filter);
					
					$query .=' SELECT u.*,c.*';
					$query .=' FROM '.($db->quoteName('#__users', 'u'));	
    				$query .=' INNER JOIN '.$db->quoteName('#__content', 'c').' ON u.'.$db->quoteName('id').' = c.'.$db->quoteName('created_by');
					$query .=' AND c.'.$db->quoteName('state').'=1';
					
					
					//check for checkbox filters if there are any filters
                    if(!empty($searchedProfileTypes))
                    {
                     // If group is supplied, we only want to fetch users from a particular group type
					$query .=' INNER JOIN '.$db->quoteName('#__categories', 'd').' ON c.'.$db->quoteName('catid').' = d.'.$db->quoteName('id');
					$query .= ' AND c.'.$db->quoteName('catid').' IN ('.implode(',',$searchedProfileTypes).')';
                    }
					
			
					//trick to add join where clause on foreach
					$query .=' WHERE u.'.$db->quoteName('id').' IS NOT NULL';
					
						foreach($filter as $obj)
					  {
					if ($obj->value!=""){
						if($itemCnt <= 0){
							
							 $query .= ' AND ( ';
							 $query .= 'c.'.$db->quoteName($obj->field);
							 $query .=' LIKE '.$db->quote('%'.$obj->value.'%');
							 $query .= ' ) ';   
							}else {
							 $query .= ' OR ( ';
							 $query .= 'c.'.$db->quoteName($obj->field);
							 $query .=' LIKE '.$db->quote('%'.$obj->value.'%');
							 $query .= ' ) ';
							  }//end if
					}
					  
						if($obj->field == 'username'){
							$query .= ' OR ('.$db->quoteName('u.username').' LIKE '.$db->quote('%'.$obj->value.'%').')';
						 }elseif ($obj->field == 'name') {
							$query .= ' OR ('.$db->quoteName('u.name').' LIKE '.$db->quote('%'.$obj->value.'%').')';
						 }//end if
						
						$itemCnt++; 
					 }//end foreach

					//$query .=' GROUP BY ' . $db->quoteName( 'u.id' );
			}//main if
			else{
				
					$query .=' SELECT u.*,c.*';
					$query .=' FROM '.($db->quoteName('#__users', 'u'));	
    				$query .=' INNER JOIN '.$db->quoteName('#__content', 'c').' ON u.'.$db->quoteName('id').' = c.'.$db->quoteName('created_by');
					$query .=' AND c.'.$db->quoteName('state').'=1';
					
					
					
			}
			


           //echo $query;exit;
           return $query;

        } 
		
		
		public function getAdvanceSearch($filter = array() , $join='and' , $avatarOnly = '' , $sorting = '',  $searchedCategoryIds = array())
        {

		$db = JFactory::getDBO();
		$query = $this->_getResults($filter, $join , $avatarOnly, $searchedCategoryIds);
		// execution of master query
		$db->setQuery($query);
		$result = $db->loadObjectList();

		if($db->getErrorNum()) {
    			JError::raiseError( 500, $db->stderr());
    		}
		return $result;   
        }

        
        /**
         * Get Articles count, without filter
        */
        private function GetArticlesTotal()
        {
            $db	= JFactory::getDBO();
            $query = $db->getQuery(true);
            $query->select('COUNT(*) AS total');
            $query->from($db->quoteName('#__content').' a');
            $query->join('inner','#__users AS b ON a.created_by=b.id');
			$query->where('b.'.$db->quoteName('block').' = '.$db->Quote('0'));
            $db->setQuery($query);
            $db->query();
            return (int) $db->loadResult();
        }
        
        /**
         * Get Articles count, without filter, who has lat long
        */
        public function OnGlobalResultCountPrepare(&$globalResultCount)
        {
            
            $app = JFactory::getApplication();
            $db	= JFactory::getDBO();            
            $query = $db->getQuery(true);
            $query->select('attribs,state');
            $query->from($db->quoteName('#__content'));
    		$query->where($db->quoteName('state').' = 1');
            //$query->where($db->quoteName('ad_googlemaps_lat').' != "" AND '.$db->quoteName('ad_googlemaps_lng').' != ""');
            $db->setQuery($query);
            //$db->query();
			
			
			 $articlestotal = $db->loadObjectList();
			 if(!empty($articlestotal))
                    {
						$total = 0;
                        foreach($articlestotal as $key=>$article )
                        {
							$attribs = json_decode($article->attribs, true);
							$lat = $attribs['latitude'];
							$lng = $attribs['longitude'];
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
                $document->addStyleSheet(JURI::root().'plugins/hellomaps/articles/css/backend_style.css');
                $percentageBlock = "<div class='dashboardPlugin articlesDashboardPlugin'> 
                                       <div clas='icon_and_count'>
                                           <div class='articlesIcon'><img src='".JURI::root().'plugins/hellomaps/articles/images/dashboard_icon.png'."'/></div>                                       
                                           <div class='markerCount'>
                                            ".number_format($total,0,'',',')."
                                           </div>
                                       </div>
                                       <div class='dashboard_title'>".$this->params->get('tab_title','Articles')."</div>
                                    </div>";
                echo $percentageBlock;    
            }
            else
            {
                $artsCountWithoutFilter = $this->GetArticlesTotal();
                $percentage = 0;
                if($artsCountWithoutFilter > 0)
                {
                    $percentage = ceil(($total / $artsCountWithoutFilter) * 100);    
                }    
                $globalResultCount = $globalResultCount + $total;
                $percentageBlock = "<div class='percentageBlock artsTotalBlock'>
                                       <span class='icon'></span>".JText::sprintf('PLG_HELLOMAPS_ARTICLES_TOTAL_LABEL',$percentage)."
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
                    <div id="notice_box_holder_articles_plugin" class="notice_box_holder notice_box_holder_plugin noticePositions<?php echo $notice_position.$infoLinkClass; ?>" style="display:none;width:<?php echo $sidebar_width; ?>px;">
                        <a class="notice_close plugin_notice_close_button" href="javascript:void(0);">X</a>                    
                        <div class="notice_box_container_plugin">
                            <div class="noticeBlock plugin">
                                <div class="plugnNotice articlesNotice">
                                    <div class="notice_plugin_header articles_notice_header"><?php echo JText::_('PLG_HELLOMAPS_ARTICLES_NOTICE_HEADER_TEXT'); ?></div>
                                    <div class="plugin_notice_content articles_notice_content">
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
           $hellomapPluginsEnabled[$this->filter_id] = $this->params->get('tab_title','Articles');
        }
	}
}

