<?php
/**
 * @version		$Id: itemlist.php 1885 2013-02-08 11:45:29Z lefteris.kavadas $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.model');
include_once(JPATH_ADMINISTRATOR.'/components/com_k2/tables/table.php');
include_once(JPATH_ADMINISTRATOR.'/components/com_k2/tables/k2category.php');
class JAK2FilterModelItemlist extends JAK2FilterModel
{
    function getData($ordering = NULL)
    {

        $user 		= JFactory::getUser();
        $aid 		= $user->get('aid');
        $db 		= JFactory::getDBO();
        $params 	= K2HelperUtilities::getParams('com_k2');
		$jaK2FilterParams = K2HelperUtilities::getParams('com_jak2filter');
        $limitstart = JRequest::getInt('limitstart');
        $limit 		= JRequest::getInt('limit');
        $task 		= JRequest::getCmd('task');
        /*
        if ($task == 'search' && $params->get('googleSearch'))
            return array();
        */    
		$tags_id = JRequest::getVar('tags_id');
		
		$rating = JRequest::getVar('rating', '');
		$rating = explode('|', $rating);
		$ratingMin = (isset($rating[0]) && !empty($rating[0])) ? intval($rating[0]) : 1;
		$ratingMax = (isset($rating[1]) && !empty($rating[1])) ? intval($rating[1]) : 5;

        $jnow = JFactory::getDate();
        $now = K2_JVERSION == '15' ? $jnow->toMySQL() : $jnow->toSql();
        $nullDate = $db->getNullDate();

        if (JRequest::getWord('format') == 'feed')
            $limit = $params->get('feedLimit');

		preg_match('/(xf|rxf)([0-9]+)/', $ordering, $orderXField);

		//Remove duplicates
		//$groupby = " GROUP BY i.id ";

		//ORDER BY EXTRA FIELDS
		$groupby = '';
		$xf = '';
		$xfJoin = '';
		if($orderXField) {
			$xfSortDir = $orderXField[1] == 'rxf' ? 'DESC' : 'ASC';
			$xfDataType = $jaK2FilterParams->get('extra_fields_data_type');
			$datatype = 'string';
			if(is_array($xfDataType) && count($xfDataType)) {
				foreach($xfDataType as $dt) {
					if(strpos($dt, $orderXField[2].':') === 0) {
						$datatype = str_replace($orderXField[2].':', '', $dt);
					}
				}
			}

			//use left join instead of right join to ensure that it always return full items (included item with no extra field data)
			$xfJoin = " LEFT JOIN #__jak2filter_taxonomy_map jatm ON jatm.item_id = i.id";
			$xfJoin .= " LEFT JOIN #__jak2filter_taxonomy jat ON (jat.id = jatm.node_id AND jat.type = 'xfield' AND jat.asset_id = ".$db->quote($orderXField[2]).")";

			//order by asset_id descending first to ensure that record with extra field data will be always display on top
			//it help to display correct result and page navigator
			$xf = ' MAX(jat.asset_id) AS xfield_asset_id';
			$orderby = ' jat.asset_id DESC';
			if($datatype == 'number') {
				if($xfSortDir == 'DESC') {
					$xf .= ', MAX(jat.labels + 0) AS xfield_labels, ';
					$orderby .= ', MAX(jat.labels + 0) '.$xfSortDir;
				} else {
					$xf .= ', MIN(jat.labels + 0) AS xfield_labels, ';
					$orderby .= ', MIN(jat.labels + 0) '.$xfSortDir;
				}
			} else {
				if($xfSortDir == 'DESC') {
					$xf .= ', MAX(jat.labels) AS xfield_labels, ';
					$orderby .= ', MAX(jat.labels) '.$xfSortDir;
				} else {
					$xf .= ', MIN(jat.labels) AS xfield_labels, ';
					$orderby .= ', MIN(jat.labels) '.$xfSortDir;
				}
			}
			$groupby = ' GROUP BY i.id ';
		}

        $query = "SELECT DISTINCT {$xf} i.*, CASE WHEN i.modified = 0 THEN i.created ELSE i.modified END as lastChanged, c.name as categoryname,c.id as categoryid, c.alias as categoryalias, c.params as categoryparams";
        if ($ordering == 'best')
            $query .= ", (r.rating_sum/r.rating_count) AS rating";

		$scoreIndex = 0;
		if($ordering == 'relevance') {
			$minium_keyword = $jaK2FilterParams->get('minium_keyword',3);
			$maximum_keyword = $jaK2FilterParams->get('maximum_keyword',20);

			$badchars = array('#', '>', '<', '\\');
			$search = JString::trim(JString::str_ireplace($badchars, '', JRequest::getString('searchword', null)));
			$jaKS = new JAKeywordSearch($minium_keyword, $maximum_keyword);
			$jaKS->build($search);
			$relevance = $jaKS->getRelevances();
			if(count($relevance)) {
				$orderby = '';
				foreach($relevance as $rF => $rTs ) {
					$field = preg_replace('/^[a-z_]+\./', '', $rF);
					$score = (int) $jaK2FilterParams->get('score_'.$field, 3);
					foreach($rTs as $rT) {
						$query .= ", IF(".$rF." LIKE '%".$rT."%', ".$score.", 0) AS score_".$scoreIndex;
						$orderby .= empty($orderby) ? "score_".$scoreIndex : " + score_".$scoreIndex;
						$scoreIndex++;
					}
				}
				$orderby .= " DESC";
			}
		}

        $query .= " FROM #__k2_items as i RIGHT JOIN #__k2_categories AS c ON c.id = i.catid";

        if ($ordering == 'best' || !($ratingMin <=1 && $ratingMax == 5))
            $query .= " LEFT JOIN #__k2_rating r ON r.itemID = i.id";

        //Changed the query for the tag case for better performance
        //if ($task == 'tag')
        //		$query .= " LEFT JOIN #__k2_tags_xref AS tags_xref ON tags_xref.itemID = i.id LEFT JOIN #__k2_tags AS tags ON tags.id = tags_xref.tagID";
		if ($task == 'tag' || $tags_id)
			$query .= " LEFT JOIN #__k2_tags_xref AS tags_xref ON tags_xref.itemID = i.id LEFT JOIN #__k2_tags AS tags ON tags.id = tags_xref.tagID";

		$query .= $xfJoin;

        if ($task == 'user' && !$user->guest && $user->id == JRequest::getInt('id'))
        {
            $query .= " WHERE ";
        }
        else
        {
            $query .= " WHERE i.published = 1 AND ";
        }

        if (K2_JVERSION != '15')
        {

            $query .= "i.access IN(".implode(',', $user->getAuthorisedViewLevels()).")"." AND i.trash = 0"." AND c.published = 1"." AND c.access IN(".implode(',', $user->getAuthorisedViewLevels()).")"." AND c.trash = 0";

            $mainframe = JFactory::getApplication();
            $languageFilter = $mainframe->getLanguageFilter();
            if ($languageFilter)
            {
                $languageTag = JFactory::getLanguage()->getTag();
                $query .= " AND c.language IN (".$db->quote($languageTag).",".$db->quote('*').") 
						AND i.language IN (".$db->quote($languageTag).",".$db->quote('*').")";
            }
        }
        else
        {
            $query .= "i.access <= {$aid}"." AND i.trash = 0"." AND c.published = 1"." AND c.access <= {$aid}"." AND c.trash = 0";
        }

        if (!($task == 'user' && !$user->guest && $user->id == JRequest::getInt('id')))
        {
            $query .= " AND ( i.publish_up = ".$db->Quote($nullDate)." OR i.publish_up <= ".$db->Quote($now)." )";
            $query .= " AND ( i.publish_down = ".$db->Quote($nullDate)." OR i.publish_down >= ".$db->Quote($now)." )";
        }

        //Build query depending on task
        switch ($task)
        {

            case 'category' :
                $id = JRequest::getInt('id');

                $category = JTable::getInstance('K2Category', 'Table');
                $category->load($id);
                $cparams = class_exists('JParameter') ? new JParameter($category->params) : new JRegistry($category->params);

                if ($cparams->get('inheritFrom'))
                {

                    $parent = JTable::getInstance('K2Category', 'Table');
                    $parent->load($cparams->get('inheritFrom'));
                    $cparams = class_exists('JParameter') ? new JParameter($parent->params) : new JRegistry($parent->params);
                }

                if ($cparams->get('catCatalogMode'))
                {
                    $query .= " AND c.id={$id} ";
                }
                else
                {
                    $categories = $this->getCategoryTree($id);
                    $sql = @implode(',', $categories);
                    $query .= " AND c.id IN ({$sql})";
                }

                break;

            case 'user' :
                $id = JRequest::getInt('id');
                $query .= " AND i.created_by={$id} AND i.created_by_alias=''";
                $categories = $params->get('userCategoriesFilter', NULL);
                if (is_array($categories))
                {
                    $categories = array_filter($categories);
                    JArrayHelper::toInteger($categories);
                    $query .= " AND c.id IN(".implode(',', $categories).")";
                }
                if (is_string($categories) && $categories > 0)
                {
                    $query .= " AND c.id = {$categories}";
                }
                break;

            case 'search' :
                $badchars = array('#', '>', '<', '\\');
                $search = JString::trim(JString::str_ireplace($badchars, '', JRequest::getString('searchword', null)));
                $sql = $this->prepareSearch($search);
                if (!empty($sql))
                {
                    $query .= $sql;
                }
                
                break;

            case 'date' :
                if ((JRequest::getInt('month')) && (JRequest::getInt('year')))
                {
                    $month = JRequest::getInt('month');
                    $year = JRequest::getInt('year');
                    $query .= " AND MONTH(i.created) = {$month} AND YEAR(i.created)={$year} ";
                    if (JRequest::getInt('day'))
                    {
                        $day = JRequest::getInt('day');
                        $query .= " AND DAY(i.created) = {$day}";
                    }

                    if (JRequest::getInt('catid'))
                    {
                        $catid = JRequest::getInt('catid');
                        $query .= " AND c.id={$catid}";
                    }

                }
                break;

            case 'tag' :
                $tag = JRequest::getString('tag');
                jimport('joomla.filesystem.file');
                if (JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomfish'.DS.'joomfish.php') && $task == 'tag')
                {

                    $registry = JFactory::getConfig();
                    $lang = K2_JVERSION == '30' ? $registry->get('jflang') : $registry->getValue('config.jflang');

                    $sql = " SELECT reference_id FROM #__jf_content as jfc LEFT JOIN #__languages as jfl ON jfc.language_id = jfl.".K2_JF_ID;
                    $sql .= " WHERE jfc.value = ".$db->Quote($tag);
                    $sql .= " AND jfc.reference_table = 'k2_tags'";
                    $sql .= " AND jfc.reference_field = 'name' AND jfc.published=1";

                    $db->setQuery($sql, 0, 1);
                    $result = $db->loadResult();

                }

                if (JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_falang'.DS.'falang.php') && $task == 'tag')
                {

                    $registry = JFactory::getConfig();
                    $lang = K2_JVERSION == '30' ? $registry->get('jflang') : $registry->getValue('config.jflang');

                    $sql = " SELECT reference_id FROM #__falang_content as fc LEFT JOIN #__languages as fl ON fc.language_id = fl.lang_id";
                    $sql .= " WHERE fc.value = ".$db->Quote($tag);
                    $sql .= " AND fc.reference_table = 'k2_tags'";
                    $sql .= " AND fc.reference_field = 'name' AND fc.published=1";

                    $db->setQuery($sql, 0, 1);
                    $result = $db->loadResult();

                }

                if (!isset($result) || $result < 1)
                {
                    $sql = "SELECT id FROM #__k2_tags WHERE name=".$db->Quote($tag);
                    $db->setQuery($sql, 0, 1);
                    $result = $db->loadResult();
                }

                $query .= " AND i.id IN (SELECT itemID FROM #__k2_tags_xref WHERE tagID=".(int)$result.")";

                /*if (isset($result) && $result > 0) {
                 $query .= " AND (tags.id) = {$result}";
                 } else {
                 $query .= " AND (tags.name) = ".$db->Quote($tag);
                 }*/

                $categories = $params->get('categoriesFilter', NULL);
                if (is_array($categories))
                {
                    JArrayHelper::toInteger($categories);
                    $query .= " AND c.id IN(".implode(',', $categories).")";
                }
                if (is_string($categories))
                    $query .= " AND c.id = {$categories}";
                break;

            default :
                $searchIDs = $params->get('categories');

                if (is_array($searchIDs) && count($searchIDs))
                {

                    if ($params->get('catCatalogMode'))
                    {
                        $sql = @implode(',', $searchIDs);
                        $query .= " AND c.id IN ({$sql})";
                    }
                    else
                    {

                        $result = $this->getCategoryTree($searchIDs);
                        if (count($result))
                        {
                            $sql = @implode(',', $result);
                            $query .= " AND c.id IN ({$sql})";
                        }
                    }
                }

                break;
        }

        //Set featured flag
        if ($task == 'category' || empty($task))
        {
            if (JRequest::getInt('featured') == '0')
            {
                $query .= " AND i.featured != 1";
            }
            else if (JRequest::getInt('featured') == '2')
            {
                $query .= " AND i.featured = 1";
            }
        }

        //Set ordering
		if(!isset($orderby) || !@$orderby) {
			switch ($ordering)
			{

				case 'date' :
					$orderby = 'i.created ASC';
					break;

				case 'rdate' :
					$orderby = 'i.created DESC';
					break;

				case 'alpha' :
					$orderby = 'i.title';
					break;

				case 'ralpha' :
					$orderby = 'i.title DESC';
					break;

				case 'order' :
					if (JRequest::getInt('featured') == '2')
						$orderby = 'i.featured_ordering';
					else
						$orderby = 'c.ordering, i.ordering';
					break;

				case 'rorder' :
					if (JRequest::getInt('featured') == '2')
						$orderby = 'i.featured_ordering DESC';
					else
						$orderby = 'c.ordering DESC, i.ordering DESC';
					break;

				case 'featured' :
					$orderby = 'i.featured DESC, i.created DESC';
					break;

				case 'hits' :
					$orderby = 'i.hits DESC';
					break;

				case 'rand' :
					$orderby = 'RAND()';
					break;

				case 'best' :
					$orderby = 'rating DESC';
					break;

				case 'modified' :
					$orderby = 'lastChanged DESC';
					break;

				case 'publishUp' :
					$orderby = 'i.publish_up DESC';
					break;

				case 'id' :
				default :
					$orderby = 'i.id DESC';
					break;
			}
		}

        $query .= $groupby . " ORDER BY ".$orderby;
        
        //echo $query; die;

        $dispatcher = JDispatcher::getInstance();
        JPluginHelper::importPlugin('k2');
        $dispatcher->trigger('onK2BeforeSetQuery', array(&$query));
        $db->setQuery($query, $limitstart, $limit);
        $rows = $db->loadObjectList();
        
		/*@TODO: remove extra fields that is not allowed to display*/
        $selectedExtraFieldsType = $jaK2FilterParams->get('selectedExtraFieldsType');                
		if(count($rows) && $selectedExtraFieldsType) 
		{
			$selectedExtraFields = $jaK2FilterParams->get('selectedExtraFields');			
			foreach($rows as &$row) 
			{
				if(isset($row->extra_fields))
				{				
					$fields = json_decode($row->extra_fields);
					$extraFieldsArray = array();
					if(count($fields) && is_array($selectedExtraFields))
					{
						foreach($fields as $index => $field) 
						{
							if(in_array($field->id, $selectedExtraFields))
							{
								$extraFieldsArray[] = $field;
							}
						}
					}
					$row->extra_fields = json_encode($extraFieldsArray);
				}
			}
		}
		
        return $rows;
    }

    function getTotal()
    {

        $user = JFactory::getUser();
        $aid = $user->get('aid');
        $db = JFactory::getDBO();
        $params = K2HelperUtilities::getParams('com_k2');
        $task = JRequest::getCmd('task');
        $tags_id = JRequest::getVar('tags_id');
        
        
		$rating = JRequest::getVar('rating', '');
		$rating = explode('|', $rating);
		$ratingMin = (isset($rating[0]) && !empty($rating[0])) ? intval($rating[0]) : 1;
		$ratingMax = (isset($rating[1]) && !empty($rating[1])) ? intval($rating[1]) : 5;
		/*
        if($task=='search' && $params->get('googleSearch'))
        return 0;
		*/
        $jnow = JFactory::getDate();
        $now = K2_JVERSION == '15' ? $jnow->toMySQL() : $jnow->toSql();
        $nullDate = $db->getNullDate();

        $query = "SELECT COUNT(DISTINCT i.id) FROM #__k2_items as i RIGHT JOIN #__k2_categories c ON c.id = i.catid";

        if ($task == 'tag' || $tags_id)
        $query .= " LEFT JOIN #__k2_tags_xref tags_xref ON tags_xref.itemID = i.id LEFT JOIN #__k2_tags tags ON tags.id = tags_xref.tagID";
        
        if (!($ratingMin <=1 && $ratingMax == 5))
            $query .= " LEFT JOIN #__k2_rating r ON r.itemID = i.id";

        if ($task == 'user' && !$user->guest && $user->id == JRequest::getInt('id'))
        {
            $query .= " WHERE ";
        }
        else
        {
            $query .= " WHERE i.published = 1 AND ";
        }

        if (K2_JVERSION != '15')
        {
            $query .= "i.access IN(".implode(',', $user->getAuthorisedViewLevels()).")"." AND i.trash = 0"." AND c.published = 1"." AND c.access IN(".implode(',', $user->getAuthorisedViewLevels()).")"." AND c.trash = 0";

            $mainframe = JFactory::getApplication();
            $languageFilter = $mainframe->getLanguageFilter();
            if ($languageFilter)
            {
                $languageTag = JFactory::getLanguage()->getTag();
                $query .= " AND c.language IN (".$db->quote($languageTag).",".$db->quote('*').") 
						AND i.language IN (".$db->quote($languageTag).",".$db->quote('*').")";
            }
        }
        else
        {
            $query .= "i.access <= {$aid}"." AND i.trash = 0"." AND c.published = 1"." AND c.access <= {$aid}"." AND c.trash = 0";
        }

        $query .= " AND ( i.publish_up = ".$db->Quote($nullDate)." OR i.publish_up <= ".$db->Quote($now)." )";
        $query .= " AND ( i.publish_down = ".$db->Quote($nullDate)." OR i.publish_down >= ".$db->Quote($now)." )";

        //Build query depending on task
        switch ($task)
        {

            case 'category' :
                $id = JRequest::getInt('id');

                $category = JTable::getInstance('K2Category', 'Table');
                $category->load($id);
                $cparams = class_exists('JParameter') ? new JParameter($category->params) : new JRegistry($category->params);

                if ($cparams->get('inheritFrom'))
                {

                    $parent = JTable::getInstance('K2Category', 'Table');
                    $parent->load($cparams->get('inheritFrom'));
                    $cparams = class_exists('JParameter') ? new JParameter($parent->params) : new JRegistry($parent->params);
                }

                if ($cparams->get('catCatalogMode'))
                {
                    $query .= " AND c.id={$id} ";
                }
                else
                {
                    $categories = $this->getCategoryTree($id);
                    $sql = @implode(',', $categories);
                    $query .= " AND c.id IN ({$sql})";
                }

                break;

            case 'user' :
                $id = JRequest::getInt('id');
                $query .= " AND i.created_by={$id} AND i.created_by_alias=''";
                $categories = $params->get('userCategoriesFilter', NULL);
                if (is_array($categories))
                {
                    $categories = array_filter($categories);
                    JArrayHelper::toInteger($categories);
                    $query .= " AND c.id IN(".implode(',', $categories).")";
                }
                if (is_string($categories) && $categories > 0)
                {
                    $query .= " AND c.id = {$categories}";
                }
                break;

            case 'search' :
                $badchars = array('#', '>', '<', '\\');
                $search = trim(str_replace($badchars, '', JRequest::getString('searchword', null)));
                $sql = $this->prepareSearch($search);
                if (!empty($sql))
                {
                    $query .= $sql;
                }
                /*else
                {
                    $result = 0;
                    return $result;
                }*/
                break;

            case 'date' :
                if ((JRequest::getInt('month')) && (JRequest::getInt('year')))
                {
                    $month = JRequest::getInt('month');
                    $year = JRequest::getInt('year');
                    $query .= " AND MONTH(i.created) = {$month} AND YEAR(i.created)={$year} ";
                    if (JRequest::getInt('day'))
                    {
                        $day = JRequest::getInt('day');
                        $query .= " AND DAY(i.created) = {$day}";
                    }

                    if (JRequest::getInt('catid'))
                    {
                        $catid = JRequest::getInt('catid');
                        $query .= " AND c.id={$catid}";
                    }

                }
                break;

            case 'tag' :
                $tag = JRequest::getString('tag');
                jimport('joomla.filesystem.file');
                if (JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomfish'.DS.'joomfish.php') && $task == 'tag')
                {

                    $registry = JFactory::getConfig();
                    $lang = K2_JVERSION == '30' ? $registry->get('jflang') : $registry->getValue('config.jflang');

                    $sql = " SELECT reference_id FROM #__jf_content as jfc LEFT JOIN #__languages as jfl ON jfc.language_id = jfl.".K2_JF_ID;
                    $sql .= " WHERE jfc.value = ".$db->Quote($tag);
                    $sql .= " AND jfc.reference_table = 'k2_tags'";
                    $sql .= " AND jfc.reference_field = 'name' AND jfc.published=1";

                    $db->setQuery($sql, 0, 1);
                    $result = $db->loadResult();

                }

                if (JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_falang'.DS.'falang.php') && $task == 'tag')
                {

                    $registry = JFactory::getConfig();
                    $lang = K2_JVERSION == '30' ? $registry->get('jflang') : $registry->getValue('config.jflang');

                    $sql = " SELECT reference_id FROM #__falang_content as fc LEFT JOIN #__languages as fl ON fc.language_id = fl.lang_id";
                    $sql .= " WHERE fc.value = ".$db->Quote($tag);
                    $sql .= " AND fc.reference_table = 'k2_tags'";
                    $sql .= " AND fc.reference_field = 'name' AND fc.published=1";

                    $db->setQuery($sql, 0, 1);
                    $result = $db->loadResult();

                }

                if (isset($result) && $result > 0)
                {
                    $query .= " AND (tags.id) = {$result}";
                }
                else
                {
                    $query .= " AND (tags.name) = ".$db->Quote($tag);
                }
                $categories = $params->get('categoriesFilter', NULL);
                if (is_array($categories))
                    $query .= " AND c.id IN(".implode(',', $categories).")";
                if (is_string($categories))
                    $query .= " AND c.id = {$categories}";
                break;

            default :
                $searchIDs = $params->get('categories');

                if (is_array($searchIDs) && count($searchIDs))
                {

                    if ($params->get('catCatalogMode'))
                    {
                        $sql = @implode(',', $searchIDs);
                        $query .= " AND c.id IN ({$sql})";
                    }
                    else
                    {
                        $result = $this->getCategoryTree($searchIDs);
                        if (count($result))
                        {
                            $sql = @implode(',', $result);
                            $query .= " AND c.id IN ({$sql})";
                        }
                    }
                }

                break;
        }

        //Set featured flag
        if ($task == 'category' || empty($task))
        {
            if (JRequest::getVar('featured') == '0')
            {
                $query .= " AND i.featured != 1";
            }
            else if (JRequest::getVar('featured') == '2')
            {
                $query .= " AND i.featured = 1";
            }
        }
        $dispatcher = JDispatcher::getInstance();
        JPluginHelper::importPlugin('k2');
        $dispatcher->trigger('onK2BeforeSetQuery', array(&$query));
        $db->setQuery($query);
        $result = $db->loadResult();
        return $result;
    }

    function getCategoryTree($categories)
    {
        $mainframe = JFactory::getApplication();
        $db = JFactory::getDBO();
        $user = JFactory::getUser();
        $aid = (int)$user->get('aid');
        if (!is_array($categories))
        {
            $categories = (array)$categories;
        }
        JArrayHelper::toInteger($categories);
        $categories = array_unique($categories);
        sort($categories);
        $key = implode('|', $categories);
        $clientID = $mainframe->getClientId();
        static $K2CategoryTreeInstances = array();
        if (isset($K2CategoryTreeInstances[$clientID]) && array_key_exists($key, $K2CategoryTreeInstances[$clientID]))
        {
            return $K2CategoryTreeInstances[$clientID][$key];
        }
        $array = $categories;
        while (count($array))
        {
            $query = "SELECT id
						FROM #__k2_categories 
						WHERE parent IN (".implode(',', $array).") 
						AND id NOT IN (".implode(',', $array).") ";
            if ($mainframe->isSite())
            {
                $query .= "
								AND published=1 
								AND trash=0";
                if (K2_JVERSION != '15')
                {
                    $query .= " AND access IN(".implode(',', $user->getAuthorisedViewLevels()).")";
                    if ($mainframe->getLanguageFilter())
                    {
                        $query .= " AND language IN(".$db->Quote(JFactory::getLanguage()->getTag()).", ".$db->Quote('*').")";
                    }
                }
                else
                {
                    $query .= " AND access<={$aid}";
                }
            }
            $db->setQuery($query);
            $array = K2_JVERSION == '30' ? $db->loadColumn() : $db->loadResultArray();
            $categories = array_merge($categories, $array);
        }
        JArrayHelper::toInteger($categories);
        $categories = array_unique($categories);
        $K2CategoryTreeInstances[$clientID][$key] = $categories;
        return $categories;
    }

    // Deprecated function, left for compatibility reasons
    function getCategoryChildren($catid, $clear = false)
    {

        static $array = array();
        if ($clear)
            $array = array();
        $user = JFactory::getUser();
        $aid = (int)$user->get('aid');
        $catid = (int)$catid;
        $db = JFactory::getDBO();
        $query = "SELECT * FROM #__k2_categories WHERE parent={$catid} AND published=1 AND trash=0 AND access<={$aid} ORDER BY ordering ";
        $db->setQuery($query);
        $rows = $db->loadObjectList();

        foreach ($rows as $row)
        {
            array_push($array, $row->id);
            if ($this->hasChildren($row->id))
            {
                $this->getCategoryChildren($row->id);
            }
        }
        return $array;
    }

    // Deprecated function, left for compatibility reasons
    function hasChildren($id)
    {

        $user = JFactory::getUser();
        $aid = (int)$user->get('aid');
        $id = (int)$id;
        $db = JFactory::getDBO();
        $query = "SELECT * FROM #__k2_categories WHERE parent={$id} AND published=1 AND trash=0 AND access<={$aid} ";
        $db->setQuery($query);
        $rows = $db->loadObjectList();

        if (count($rows))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    function getCategoryFirstChildren($catid, $ordering = NULL)
    {

        $mainframe = JFactory::getApplication();
        $user = JFactory::getUser();
        $aid = $user->get('aid');
        $db = JFactory::getDBO();
        $query = "SELECT * FROM #__k2_categories WHERE parent={$catid} AND published=1 AND trash=0";

        if (K2_JVERSION != '15')
        {
            $query .= " AND access IN(".implode(',', $user->getAuthorisedViewLevels()).") ";
            if ($mainframe->getLanguageFilter())
            {
                $query .= " AND language IN(".$db->Quote(JFactory::getLanguage()->getTag()).", ".$db->Quote('*').")";
            }
        }
        else
        {
            $query .= " AND access<={$aid} ";
        }

        switch ($ordering)
        {

            case 'order' :
                $order = " ordering ASC";
                break;

            case 'alpha' :
                $order = " name ASC";
                break;

            case 'ralpha' :
                $order = " name DESC";
                break;

            case 'reversedefault' :
                $order = " id DESC";
                break;

            default :
                $order = " id ASC";
                break;
        }

        $query .= " ORDER BY {$order}";

        $db->setQuery($query);
        $rows = $db->loadObjectList();
        if ($db->getErrorNum())
        {
            echo $db->stderr();
            return false;
        }

        return $rows;
    }

    function countCategoryItems($id)
    {

        $mainframe = JFactory::getApplication();
        $user = JFactory::getUser();
        $aid = (int)$user->get('aid');
        $id = (int)$id;
        $db = JFactory::getDBO();

        $jnow = JFactory::getDate();
        $now = K2_JVERSION == '15' ? $jnow->toMySQL() : $jnow->toSql();
        $nullDate = $db->getNullDate();

        $categories = $this->getCategoryTree($id);
        $query = "SELECT COUNT(*) FROM #__k2_items WHERE catid IN (".implode(',', $categories).") AND published=1 AND trash=0";

        if (K2_JVERSION != '15')
        {
            $query .= " AND access IN(".implode(',', $user->getAuthorisedViewLevels()).") ";
            if ($mainframe->getLanguageFilter())
            {
                $query .= " AND language IN(".$db->Quote(JFactory::getLanguage()->getTag()).", ".$db->Quote('*').")";
            }
        }
        else
        {
            $query .= " AND access<=".$aid;
        }

        $query .= " AND ( publish_up = ".$db->Quote($nullDate)." OR publish_up <= ".$db->Quote($now)." )";
        $query .= " AND ( publish_down = ".$db->Quote($nullDate)." OR publish_down >= ".$db->Quote($now)." )";
        $db->setQuery($query);
        $total = $db->loadResult();
        return $total;
    }

    function getUserProfile($id = NULL)
    {

        $db = JFactory::getDBO();
        if (is_null($id))
            $id = JRequest::getInt('id');
        else
            $id = (int)$id;
        $query = "SELECT id, gender, description, image, url, `group`, plugins FROM #__k2_users WHERE userID={$id}";
        $db->setQuery($query);
        $row = $db->loadObject();
        return $row;
    }

    function getAuthorLatest($itemID, $limit, $userID)
    {

        $mainframe = JFactory::getApplication();
        $user = JFactory::getUser();
        $aid = (int)$user->get('aid');
        $itemID = (int)$itemID;
        $userID = (int)$userID;
        $limit = (int)$limit;
        $db = JFactory::getDBO();

        $jnow = JFactory::getDate();
        $now = K2_JVERSION == '15' ? $jnow->toMySQL() : $jnow->toSql();
        $nullDate = $db->getNullDate();

        $query = "SELECT i.*, c.alias as categoryalias FROM #__k2_items as i 
				LEFT JOIN #__k2_categories c ON c.id = i.catid 
				WHERE i.id != {$itemID} 
				AND i.published = 1 
				AND ( i.publish_up = ".$db->Quote($nullDate)." OR i.publish_up <= ".$db->Quote($now)." ) 
				AND ( i.publish_down = ".$db->Quote($nullDate)." OR i.publish_down >= ".$db->Quote($now)." ) ";

        if (K2_JVERSION != '15')
        {
            $query .= " AND i.access IN(".implode(',', $user->getAuthorisedViewLevels()).") ";
            if ($mainframe->getLanguageFilter())
            {
                $query .= " AND i.language IN(".$db->Quote(JFactory::getLanguage()->getTag()).", ".$db->Quote('*').")";
            }
        }
        else
        {
            $query .= " AND i.access <= {$aid} ";
        }

        $query .= " AND i.trash = 0 
				AND i.created_by = {$userID} 
				AND i.created_by_alias='' 
				AND c.published = 1 ";

        if (K2_JVERSION != '15')
        {
            $query .= " AND c.access IN(".implode(',', $user->getAuthorisedViewLevels()).") ";
            if ($mainframe->getLanguageFilter())
            {
                $query .= " AND c.language IN(".$db->Quote(JFactory::getLanguage()->getTag()).", ".$db->Quote('*').")";
            }
        }
        else
        {
            $query .= " AND c.access <= {$aid} ";
        }

        $query .= " AND c.trash = 0 
				ORDER BY i.created DESC";

        $db->setQuery($query, 0, $limit);
        $rows = $db->loadObjectList();

        foreach ($rows as $item)
        {
            //Image
            $item->imageXSmall = '';
            $item->imageSmall = '';
            $item->imageMedium = '';
            $item->imageLarge = '';
            $item->imageXLarge = '';

            if (JFile::exists(JPATH_SITE.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$item->id).'_XS.jpg'))
                $item->imageXSmall = JURI::root(true).'/media/k2/items/cache/'.md5("Image".$item->id).'_XS.jpg';

            if (JFile::exists(JPATH_SITE.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$item->id).'_S.jpg'))
                $item->imageSmall = JURI::root(true).'/media/k2/items/cache/'.md5("Image".$item->id).'_S.jpg';

            if (JFile::exists(JPATH_SITE.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$item->id).'_M.jpg'))
                $item->imageMedium = JURI::root(true).'/media/k2/items/cache/'.md5("Image".$item->id).'_M.jpg';

            if (JFile::exists(JPATH_SITE.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$item->id).'_L.jpg'))
                $item->imageLarge = JURI::root(true).'/media/k2/items/cache/'.md5("Image".$item->id).'_L.jpg';

            if (JFile::exists(JPATH_SITE.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$item->id).'_XL.jpg'))
                $item->imageXLarge = JURI::root(true).'/media/k2/items/cache/'.md5("Image".$item->id).'_XL.jpg';

            if (JFile::exists(JPATH_SITE.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$item->id).'_Generic.jpg'))
                $item->imageGeneric = JURI::root(true).'/media/k2/items/cache/'.md5("Image".$item->id).'_Generic.jpg';
        }

        return $rows;
    }
    /*
    function getRelatedItems($itemID, $tags, $params)
    {

        $mainframe = JFactory::getApplication();
        $limit = $params->get('itemRelatedLimit', 10);
        $itemID = (int)$itemID;
        foreach ($tags as $tag)
        {
            $tagIDs[] = $tag->id;
        }
        JArrayHelper::toInteger($tagIDs);
        $sql = implode(',', $tagIDs);

        $user = JFactory::getUser();
        $aid = (int)$user->get('aid');
        $db = JFactory::getDBO();

        $jnow = JFactory::getDate();
        $now = K2_JVERSION == '15' ? $jnow->toMySQL() : $jnow->toSql();
        $nullDate = $db->getNullDate();

        $query = "SELECT DISTINCT itemID FROM #__k2_tags_xref WHERE tagID IN ({$sql}) AND itemID!={$itemID}";
        $db->setQuery($query);
        $itemsIDs = K2_JVERSION == '30' ? $db->loadColumn() : $db->loadResultArray();

        if (!count($itemsIDs))
            return array();

        $sql = implode(',', $itemsIDs);

        $query = "SELECT i.*, c.alias as categoryalias FROM #__k2_items as i 
				LEFT JOIN #__k2_categories c ON c.id = i.catid 
				WHERE i.published = 1 
				AND ( i.publish_up = ".$db->Quote($nullDate)." OR i.publish_up <= ".$db->Quote($now)." ) 
				AND ( i.publish_down = ".$db->Quote($nullDate)." OR i.publish_down >= ".$db->Quote($now)." ) ";

        if (K2_JVERSION != '15')
        {
            $query .= " AND i.access IN(".implode(',', $user->getAuthorisedViewLevels()).") ";
            if ($mainframe->getLanguageFilter())
            {
                $query .= " AND i.language IN(".$db->Quote(JFactory::getLanguage()->getTag()).", ".$db->Quote('*').")";
            }

        }
        else
        {
            $query .= " AND i.access <= {$aid} ";
        }

        $query .= " AND i.trash = 0 
				AND c.published = 1 ";

        if (K2_JVERSION != '15')
        {
            $query .= " AND c.access IN(".implode(',', $user->getAuthorisedViewLevels()).") ";
            if ($mainframe->getLanguageFilter())
            {
                $query .= " AND c.language IN(".$db->Quote(JFactory::getLanguage()->getTag()).", ".$db->Quote('*').")";
            }
        }
        else
        {
            $query .= " AND c.access <= {$aid} ";
        }

        $query .= " AND c.trash = 0 
				AND (i.id) IN ({$sql}) 
				ORDER BY i.created DESC";

        $db->setQuery($query, 0, $limit);
        $rows = $db->loadObjectList();
        K2Model::addIncludePath(JPATH_COMPONENT.DS.'models');
        $model = K2Model::getInstance('Item', 'K2Model');
        for ($key = 0; $key < sizeof($rows); $key++)
        {
            $rows[$key] = $model->prepareItem($rows[$key], 'relatedByTag', '');
            $rows[$key] = $model->execPlugins($rows[$key], 'relatedByTag', '');
            K2HelperUtilities::setDefaultImage($rows[$key], 'relatedByTag', $params);
        }
        return $rows;
    }
    */

    function prepareSearch($search, $filter = '', $filter_categories = '')
    {

        jimport('joomla.filesystem.file');
        $db = JFactory::getDbo();
        $language = JFactory::getLanguage();
        $defaultLang = $language->getDefault();
        $currentLang = $language->getTag();
        $length = JString::strlen($search);
        //$keyword_option = modJak2filterHelper::getRadioKeywords();
        $config = JComponentHelper::getParams('com_jak2filter');
        $minium_keyword = $config->get('minium_keyword',3);
        $maximum_keyword = $config->get('maximum_keyword',20);
        $sql = '';
        $where = array();

        //Search by tags
		$tags_id = JRequest::getVar('tags_id');
        if($filter != 'tags_id'){
			if(is_array($tags_id) && count($tags_id)) {
				JArrayHelper::toInteger($tags_id);
				$sql .= " AND tags_xref.tagID IN (".implode(',', $tags_id).")";
			} elseif($tags_id) {
				$sql .= " AND tags_xref.tagID = ".$db->quote($tags_id);
			}
        }
        //Search by Author
        $created_by = JRequest::getVar('created_by');
        if($filter != 'created_by'){
			if(is_array($created_by) && count($created_by)) {
				JArrayHelper::toInteger($created_by);
				$where[] = "i.created_by IN (".implode(',', $created_by).")";
			} elseif($created_by) {
				$where[] = "i.created_by = ".$db->quote($created_by);
			}
        }
        //Search by Category
        $ja_cid = JRequest::getVar('category_id');
        if(!$ja_cid && ($filter != 'category_id') && ($filter_categories != '') && ($filter_categories != '0')) {
        	$ja_cid = $filter_categories;
        }
        
        if($ja_cid && $filter != 'category_id'){

			if(!is_array($ja_cid)) {
				$ja_cids = explode(",",$ja_cid);
			} else {
				$ja_cids = $ja_cid;
			}
            $ja_catMode = JRequest::getInt('isc',0);
            $category = JTable::getInstance('K2Category', 'Table');
            
            if($ja_catMode) {
            	$ja_cids = $this->getCategoryTree($ja_cids);
            }
            $sql .= " AND i.catid IN (".implode(', ',$ja_cids).")";
            
            //$sql.= " AND i.catid = ".$db->quote(JRequest::getVar('category_id'))." ";
        }
        //search by date
		$dateFormat = 'Y-m-d';
     	$dtrange = JRequest::getString('dtrange', '');

		$now = new JDate();

		switch($dtrange) {
			case 'today':
				$start_created = $end_created = new JDate();
				break;
			case '-1d':
				$start_created = new JDate('-1 day');
				$end_created = new JDate();
				break;
			case '-7d':
				$start_created = new JDate('-7 days');
				$end_created = new JDate();
				break;
			case '-1m':
				$start_created = new JDate('-1 month');
				$end_created = new JDate();
				break;
			case 'tw':
				$dayofweek = $now->dayofweek;
				$start_created = new JDate('-'.($dayofweek-1).' days');
				$end_created = new JDate($dayofweek == 7 ? 'now' : '+'.(7-$dayofweek).'days');
				break;
			case 'lw':
				$dayofweek = $now->dayofweek;
				$start_created = new JDate('-'.($dayofweek -1 + 7).' days');
				$end_created = new JDate('-'.($dayofweek).' days');
				break;
			case 'tm':
				$daysinmonth = $now->daysinmonth;
				$day = $now->format('d');
				$start_created = new JDate('-'.($day-1).' days');
				$end_created = new JDate($day == $daysinmonth ? 'now' : '+'.($daysinmonth-$day).'days');
				break;
			case 'lm':
				$lm = new JDate('-1 month');
				$daysinmonth = $lm->daysinmonth;
				$day = $now->format('d');
				$start_created = new JDate('-'.($day-1 + $daysinmonth).' days');
				$end_created = new JDate('+'.(-$day).'days');
				break;
			case 'range':
			default:
				$start_created = JRequest::getString('sdate', '');
				$end_created = JRequest::getString('edate', '');
				break;
		}
		if($start_created instanceof JDate) {
			$start_created = $start_created->format($dateFormat);
		}
		if($end_created instanceof JDate) {
			$end_created = $end_created->format($dateFormat);
		}

		if ($start_created){
   			$sql .= " AND (i.created >= ".$db->quote($start_created)." OR i.modified >= ".$db->quote($start_created).")";
   		}
   			
    	if ($end_created){
			if($start_created == $end_created) {
				$end_created .= ' 23:59:59';
			}
			$sql .= " AND (IF(i.modified <> ".$db->quote($db->getNullDate()).", i.modified, i.created) <= ".$db->quote($end_created).")";
   		}

		//Filter by Rating
		$rating = JRequest::getVar('rating', '');
		$rating = explode('|', $rating);
		$ratingMin = (isset($rating[0]) && !empty($rating[0])) ? intval($rating[0]) : 1;
		$ratingMax = (isset($rating[1]) && !empty($rating[1])) ? intval($rating[1]) : 5;
		if(!($ratingMin <= 1 && $ratingMax == 5)) {
			if($ratingMin == $ratingMax) {
				$where[] = "(r.rating_sum/r.rating_count) = ".$ratingMax;
			} else {
				$where[] = "(r.rating_sum/r.rating_count) >= ".$ratingMin;
				$where[] = "(r.rating_sum/r.rating_count) <= ".$ratingMax;
			}
		}
		
        //Search by extrafield
        $vars = JRequest::get();
        
		$querySearchableLabels = 'SELECT id FROM #__k2_extra_fields WHERE `type` = '.$db->quote('labels');
		$db->setQuery($querySearchableLabels);
		$aLabels = $db->loadColumn();

		$matchTypes = $config->get('extra_fields_search_mode', array());
        if(count($vars)) {
            $exPattern = '/xf_([0-9]+)(?:_([a-zA-Z0-9_]+))*/i';
            foreach ($vars as $field => $value) {
                if(empty($value)) continue;
                if(preg_match($exPattern, $field, $matches) && $filter != $field) {
                    $fid = $matches[1];
                    $cType = isset($matches[2]) ? $matches[2] : ''; //custom type

                    //Example K2 extra field value is storage in database
                    //{"id":"1","value":"180"}
                    $prefix = '{"id":"'.$fid.'","value":[^{]*';
                    if(empty($cType)) {
                        if(is_array($value)) {
                            if(count($value)) {
								$matchtype = 'any';
								if(count($matchTypes)) {
									foreach($matchTypes as $mt) {
										if(strpos($mt, $fid.':') === 0) {
											$matchtype = str_replace($fid.':', '', $mt);
											break;
										}
									}
								}

                            	if(in_array($fid, $aLabels)) {
                            		foreach($value as $index => $val) {
                            			$val = $this->rgEscapse($this->convert_string_non_latin($val));
                                		$val = str_replace('\u', '\\\\\\\\u', $val);
										$value[$index] = $val;
                            		}

									if($matchtype == 'all') {
										//http://www.joomlart.com/forums/showthread.php?95259-Changing-filter-function-from-logical-OR-to-logical-AND&p=410936#post410936
										$options = array();
										foreach($value as $val) {
											$options[] = $prefix.'([",][[:space:]]*'.$val.'[[:space:]]*[",])'; // ~ IN (string1)
										}

										$searchPattern = implode("' AND i.extra_fields REGEXP '", $options);
									} else {
										$searchPattern = $prefix.'([",][[:space:]]*'.implode('[[:space:]]*[",]|[",][[:space:]]*', $value).'[[:space:]]*[",])'; // ~ IN (string1, string2, ...)
									}
                            	} else {
									if($matchtype == 'all') {
										//http://www.joomlart.com/forums/showthread.php?95259-Changing-filter-function-from-logical-OR-to-logical-AND&p=410936#post410936
										$options = array();
										foreach($value as $val) {
											$options[] = $prefix.'("'.$val.'")'; // ~ IN (string1)
										}
										$searchPattern = implode("' AND i.extra_fields REGEXP '", $options);
									} else {
										$searchPattern = $prefix.'("'.implode('"|"', $value).'")'; // ~ IN (string1, string2, ...)
									}
                            	}
                            }
                        } else {
                            $value = $this->rgEscapse($value);
                            if(in_array($fid, $aLabels)) {
                            	$value = $this->rgEscapse($this->convert_string_non_latin($value));
                                $value = str_replace('\u', '\\\\\\\\u', $value);
                            	$searchPattern = $prefix.'[",][[:space:]]*'.$value.'[[:space:]]*[",]'; // ~ EQUAL 'string'
                            } else {
                            	$searchPattern = $prefix.'"'.$value.'"'; // ~ EQUAL 'string'
                            }
                        }
                        $where[] = "(i.extra_fields REGEXP '".$searchPattern."')";
                    } else {
                        switch ($cType) {
                            case 'txt':
								$value = trim($value);
								$value = $this->convert_text_search($value);
                                $value = $this->rgEscapse($this->convert_string_non_latin($value));
                                $searchPattern = $prefix.'"[^"]*'.$value.'[^"]*"';// ~ LIKE '%string%'
                                $searchPattern = str_replace('\u', '\\\\\\\\u', $searchPattern);
                                $where[] = "(i.extra_fields REGEXP '".$searchPattern."')";
                                break;
                            case 'range':
                                list($from, $to) = explode('|', $value);
								if($from != '') {
                                	$from = (int) $from;
                                    $searchPattern = $prefix.$this->rgRange($from, 1);
                                    $where[] = "(i.extra_fields REGEXP '".$searchPattern."')";
                                }
                                if($to != '') {
                                	$to = (int) $to;
                                    $searchPattern = $prefix.$this->rgRange($to, 0);
                                    $where[] = "(i.extra_fields REGEXP '".$searchPattern."')";
                                }
                                break;
                            case 'from':
                                if($value != '') {
                                    $searchPattern = $prefix.$this->rgRange($value, 1, 'date');
                                    $where[] = "(i.extra_fields REGEXP '".$searchPattern."')";
                                }
                                break;
                            case 'to':
                                if($value != '') {
                                    $searchPattern = $prefix.$this->rgRange($value, 0, 'date');
                                    $where[] = "(i.extra_fields REGEXP '".$searchPattern."')";
                                }
                                break;
                        }
                    }
                }
            }
        }
        

        $sql .= empty($where) ? '' : ' AND ' . implode(' AND ', $where);

        if(empty($search)) {
            return $sql;
        }

		$type = JRequest::getVar('st');

        if (JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomfish'.DS.'joomfish.php') && $currentLang != $defaultLang)
        {
            $conditions = array();
            $search_ignore = array();

            $ignoreFile = $language->getLanguagePath().DS.$currentLang.DS.$currentLang.'.ignore.php';

            if (JFile::exists($ignoreFile))
            {
                include $ignoreFile;
            }

			$langField = K2_JVERSION == '15' ? 'code' : 'lang_code';

			$jfQuery = " SELECT reference_id FROM #__jf_content as jfc LEFT JOIN #__languages as jfl ON jfc.language_id = jfl.".K2_JF_ID;
			$jfQuery .= " WHERE jfc.reference_table = 'k2_items'";
			$jfQuery .= " AND jfl.".$langField."=".$db->Quote($currentLang);
			$jfQuery .= " AND jfc.published=1";

			$jaKS = new JAKeywordSearch($minium_keyword, $maximum_keyword);
			$jaKS->enableJoomFish();
			$sqlKeyword = $jaKS->build($search);
			if(!empty($sqlKeyword)) {
				$jfWhere = explode('__AND__', $sqlKeyword);
				$jfIds = array();

				$first = 1;
				foreach($jfWhere as $jfw) {
					$jfQuery2 = $jfQuery . ' AND '.$jfw;

					$db->setQuery($jfQuery2);
					$result = K2_JVERSION == '30' ? $db->loadColumn() : $db->loadResultArray();
					$result = @array_unique($result);
					JArrayHelper::toInteger($result);

					if($first) {
						$jfIds = $result;
					} else {
						$jfIds = array_intersect($jfIds, $result);
					}

				}
				if (count($jfIds))
				{
					$conditions[] = "i.id IN(".implode(',', $jfIds).")";
				}
			}
			/**
			 * @TODO
			 * CURRENT WORK FLOW: If does not find any item in specific language, then return all item in default language
			 * is it should be updated for upcoming version?
			 */
			if (count($conditions))
            {
                $sql .= " AND (".implode(" OR ", $conditions).")";
            }

        }
        else
        {
			$search = trim($search);
			if(!empty($search)) {
				$jaKS = new JAKeywordSearch($minium_keyword, $maximum_keyword);
				$sqlKeyword = $jaKS->build($search);
				if(!empty($sqlKeyword)) {
					$sql .= ' AND '.$sqlKeyword;
				}
			}
        }
		//echo $sql;
        return $sql;
    }

    function getModuleItems($moduleID)
    {

        $db = JFactory::getDBO();
        $query = "SELECT * FROM #__modules WHERE id={$moduleID} AND published=1 AND client_id=0";
        $db->setQuery($query, 0, 1);
        $module = $db->loadObject();
        $format = JRequest::getWord('format');
        if (is_null($module))
            JError::raiseError(404, JText::_('K2_NOT_FOUND'));
        else
        {
            $params = class_exists('JParameter') ? new JParameter($module->params) : new JRegistry($module->params);
            switch ($module->module)
            {

                case 'mod_k2_content' :
                    require_once (JPATH_SITE.DS.'modules'.DS.'mod_k2_content'.DS.'helper.php');
                    $helper = new modK2ContentHelper;
                    $items = $helper->getItems($params, $format);
                    break;

                case 'mod_k2_comments' :
                    if ($params->get('module_usage') == 1)
                        JError::raiseError(404, JText::_('K2_NOT_FOUND'));

                    require_once (JPATH_SITE.DS.'modules'.DS.'mod_k2_comments'.DS.'helper.php');
                    $helper = new modK2CommentsHelper;
                    $items = $helper->getLatestComments($params);

                    foreach ($items as $item)
                    {
                        $item->title = $item->userName.' '.JText::_('K2_COMMENTED_ON').' '.$item->title;
                        $item->introtext = $item->commentText;
                        $item->created = $item->commentDate;
                        $item->id = $item->itemID;
                    }
                    break;

                default :
                    JError::raiseError(404, JText::_('K2_NOT_FOUND'));
            }

            $result = new JObject;
            $result->items = $items;
            $result->title = $module->title;
            $result->module = $module->module;
            $result->params = $module->params;
            return $result;

        }

    }
    /**
	 * escapse special charater in string that is used in regex pattern
	 */
    private function rgEscapse($str) {
        return preg_replace("/([\:\-\/\.\?\(\)\[\]\{\}])/", "\\\\$1", $str);
    }

    /**
		 * Find extra field that has data type is number or date and value is greater or lesser given value depend on $greater param.
		 */
    private function rgRange($value, $greater = 1, $type = 'number') {
        $aDecimalChars = array(',', '.');
        $value = trim($value);
        $orgValue = $value;
        $isNegative = 0;
        $pattern = array();
        $pattern[] = $value;

        if($orgValue == 0) {
            if($greater) {
                $pattern[] = '[0-9_dot_,]*';//all positive
            } else {
                $pattern[] = '-[0-9_dot_,]*';//all negative
            }
        } else {
            if($type == 'number') {
                if($greater && $orgValue < 0) {
                    $pattern[] = '[0-9_dot_,]*';//all positivie
                }
                if(!$greater && $orgValue > 0) {
                    $pattern[] = '-[0-9_dot_,]*';//all negative
                }

                if(substr($value, 0, 1) == '-') {
                    //is negative numbers
                    $isNegative = 1;
                    $greater = !$greater;
                    $value = abs($value);
                }
            }

            $arr = str_split($value);
            $len = count($arr);
            $aVal = preg_split('/[\.,]/', $value);
            $valueInt = $aVal[0];
            $lenInt = strlen($valueInt);
            $isDecimal = $len > $lenInt;
            $checkDecimal = 0;

            if($greater) {
                $nums = '';
                for ($i=0; $i<$len; $i++) {
                    $num = $arr[$i];
                    if($num == '-') {
                        if($type == 'date') {
                            $nums .= '-';
                        }
                        continue;
                    }
                    if(in_array($num, $aDecimalChars)) {
                        if($type == 'number') {
                            $checkDecimal = 1;
                        }
                    }
                    if($num < 9) {
                        if($type == 'date') {
                            $remain = $len-$i-1;
                            $remainChar = '0-9-';
                            $pattern[] = $nums
                            .(($num+1 == 9) ? '9': '['.($num+1).'-9]')
                            .($remain ? '['.$remainChar.']{'.($len-$i-1).',}' : '');
                        } else {
                            $remain = ($checkDecimal) ? $len-$i-1 : $lenInt-$i-1;
                            $remainChar = '0-9';

                            $pattern[] = ($isNegative ? '-' : '').$nums
                            .(($num+1 == 9) ? '9': '['.($num+1).'-9]')
                            .($remain ? '['.$remainChar.']{'.($remain).'}' : '')
                            .(!$checkDecimal ? '[0-9_dot_,]*' : '[0-9]*');

                        }

                    }
                    $nums .= $num;
                }
                if(!$checkDecimal) {
                    $pattern[] =  ($isNegative ? '-' : '').$value . '([_dot_,][0-9]+)*';
                }
                //number that has lenght of integer path is longer
                $pattern[] = ($isNegative ? '-' : '').'[0-9]{'.($lenInt+1).',}([_dot_,][0-9]+)*';
            } else {
                $nums = '';
                for ($i=0; $i<$len; $i++) {
                    $num = $arr[$i];
                    if($num == '-') {
                        if($type == 'date') {
                            $nums .= '-';
                        }
                        continue;
                    }
                    if(in_array($num, $aDecimalChars)) {
                        if($type == 'number') {
                            $checkDecimal = 1;
                        }
                    }
                    if($num > 0) {
                        if($type == 'date') {
                            $remain = $len-$i-1;
                            $remainChar = '0-9-';
                            $pattern[] = $nums
                            .(($num-1 == 0)? (($nums=='')?'':'0') : '[0-'.($num-1).']')
                            .($remain ? '['.$remainChar.']{0,'.($len-$i-1).'}' : '');
                        } else {
                            $remain = ($checkDecimal) ? $len-$i-1 : $lenInt-$i-1;
                            $remainChar = ($isDecimal && $checkDecimal) ? '0-9_dot_,' : '0-9';
                            $pattern[] = ($isNegative ? '-' : '').$nums
                            .(($num-1 == 0)? (($nums=='')?'':'0') : '[0-'.($num-1).']')
                            .($remain ? '['.$remainChar.']{0,'.($remain).'}' : '')
                            .(!$checkDecimal ? '([_dot_,][0-9]+)*' : '[0-9]*');

                        }
                    }
                    $nums .= $num;
                }
                //number that has lenght of integer path is shorter
                if($lenInt > 1) {
                	$pattern[] = ($isNegative ? '-' : '').'0*[0-9]{0,'.($lenInt-1).'}([_dot_,][0-9]+)*';
                }
            }
        }

		if($type == 'number') {

			// Search for currency with number price.
			$unitBefore = $unitAfter = '([^0-9_dot_,]|\\\\\\\\u[a-z0-9]{4})*';//unit might be currency symbol, characters
		} else {
			$unitBefore = $unitAfter = '';
		}

        $pattern = '"'.$unitBefore.'('.implode('|', $pattern).')'.$unitAfter.'"';
        $pattern = str_replace('.', '[.,]', $pattern);//[[.full-stop.]]
        $pattern = str_replace('_dot_', '.', $pattern);
        //echo($pattern."<br />");
        return $pattern;
    }
    
    /**
	* Convert input text to search character.
	*/
    function convert_text_search ($str) {
		$_a = array(
					// latin
					'A', 'O', 'U', 'Y', 'I', 'E', 'C', 'D', 'N', 'a', 'o', 'u', 'y', 'i', 'e', 'c', 'd', 'n',
					//special
					'Á', 'À', 'Ả', 'Ã', 'Ạ', 'Ă', 'Ắ', 'Ằ', 'Ẳ', 'Ẵ', 'Ặ', 'Â', 'Ấ', 'Ầ', 'Ẩ', 'Ẫ', 'Ậ',
					'Ó', 'Ò', 'Ỏ', 'Õ', 'Ọ', 'Ơ', 'Ớ', 'Ờ', 'Ở', 'Ỡ', 'Ợ', 'Ô', 'Ố', 'Ồ', 'Ổ', 'Ỗ', 'Ộ',
					'Ú', 'Ù', 'Ủ', 'Ũ', 'Ụ', 'Ư', 'Ứ', 'Ừ', 'Ử', 'Ữ', 'Ự',
					'É', 'È', 'Ẻ', 'Ẽ', 'Ẹ', 'Ê', 'Ế', 'Ề', 'Ể', 'Ễ', 'Ệ',
					'Ý', 'Ỳ', 'Ỷ', 'Ỹ', 'Ỵ',
					'Í', 'Ì', 'Ỉ', 'Ĩ', 'Ị',
					'á', 'à', 'ả', 'ã', 'ạ', 'ă', 'ắ', 'ằ', 'ẳ', 'ẵ', 'ặ', 'â', 'ấ', 'ầ', 'ẩ', 'ẫ', 'ậ',
					'ó', 'ò', 'ỏ', 'õ', 'ọ', 'ơ', 'ớ', 'ờ', 'ở', 'ỡ', 'ợ', 'ô', 'ố', 'ồ', 'ổ', 'ỗ', 'ộ',
					'ú', 'ù', 'ủ', 'ũ', 'ụ', 'ư', 'ứ', 'ừ', 'ử', 'ữ', 'ự',
					'é', 'è', 'ẻ', 'ẽ', 'ẹ', 'ê', 'ế', 'ề', 'ể', 'ễ', 'ệ',
					'ý', 'ỳ', 'ỷ', 'ỹ', 'ỵ',
					'í', 'ì', 'ỉ', 'ĩ', 'ị',
					);
		$_b = array(
			// latin
			'(A|Á|À|Ả|Ã|Ạ|Ă|Ắ|Ằ|Ẳ|Ặ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ|Ä|Å|Æ)',
			'(O|Ó|Ò|Ỏ|Õ|Ọ|Ơ|Ớ|Ờ|Ở|Ợ|Ỡ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ö|Ø)',
			'(U|Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ự|Ữ|Û|Ü)',
			'(Y|Ý|Ỳ|Ỷ|Ỹ|Ỵ|ÿ)',
			'(I|Í|Ì|Ỉ|Ĩ|Ị|Î|Ï)',
			'(E|É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ễ|Ệ|Ể|Ë)',
			'(C|Ç)',
			'(D|Ð)',
			'(N|Ñ)',
			'(a|á|à|ả|ã|ạ|ă|ắ|ằ|ẵ|ặ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ|Ä|Å|Æ)',
			'(o|ó|ò|ỏ|õ|ọ|ơ|ớ|ờ|ở|ợ|ỡ|ô|ố|ồ|ổ|ỗ|ộ|Ö|Ø)',
			'(u|ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ự|ữ|Û|Ü)',
			'(y|ý|ỳ|ỷ|ỹ|ỵ|ÿ)',
			'(i|í|ì|ỉ|ĩ|ị|Î|Ï)',
			'(e|é|è|ẻ|ẽ|ẹ|ê|ế|ề|ễ|ệ|ể|Ë)',
			'(c|Ç)',
			'(d|đ)',
			'(n|Ñ)',
			// special
			'(A|Á)', '(A|À)', '(A|Ả)', '(A|Ã)', '(A|Ạ)', '(A|Ă)', '(A|Ă|Ắ)', '(A|Ă|Ằ)', '(A|Ă|Ẳ)', '(A|Ă|Ẵ)', '(A|Ă|Ặ)', '(A|Â)', '(A|Â|Ấ)', '(A|Â|Ầ)', '(A|Â|Ẩ)', '(A|Â|Ẫ)', '(A|Â|Ậ)',
			'(O|Ó)', '(O|Ò)', '(O|Ỏ)', '(O|Õ)', '(O|Ọ)', '(O|Ơ)', '(O|Ơ|Ớ)', '(O|Ơ|Ờ)', '(O|Ơ|Ở)', '(O|Ơ|Ỡ)', '(O|Ơ|Ợ)', '(O|Ô)', '(O|Ô|Ố)', '(O|Ô|Ồ)', '(O|Ô|Ổ)', '(O|Ô|Ỗ)', '(O|Ô|Ộ)',
			'(U|Ú)', '(U|Ù)', '(U|Ủ)', '(U|Ũ)', '(U|Ụ)', '(U|Ư)', '(U|Ư|Ứ)', '(U|Ư|Ừ)', '(U|Ư|Ử)', '(U|Ư|Ữ)', '(U|Ư|Ự)',
			'(E|É)', '(E|È)', '(E|Ẻ)', '(E|Ẽ)', '(E|Ẹ)', '(E|Ê)', '(E|Ê|Ế)', '(E|Ê|Ề)', '(E|Ê|Ể)', '(E|Ê|Ễ)', '(E|Ê|Ệ)',
			'(Y|Ý)', '(Y|Ỳ)', '(Y|Ỷ)', '(Y|Ỹ)', '(Y|Ỵ)',
			'(I|Í)', '(I|Ì)', '(I|Ỉ)', '(I|Ĩ)', '(I|Ị)',
			'(a|á)', '(a|à)', '(a|ả)', '(a|ã)', '(a|ạ)', '(a|ă)', '(a|ă|ắ)', '(a|ă|ằ)', '(a|ă|ẳ)', '(a|ă|ẵ)', '(a|ă|ặ)', '(a|â)', '(a|â|ấ)', '(a|â|ầ)', '(a|â|ẩ)', '(a|â|ẫ)', '(a|â|ậ)',
			'(o|ó)', '(o|ò)', '(o|ỏ)', '(o|õ)', '(o|ọ)', '(o|ơ)', '(o|ơ|ớ)', '(o|ơ|ờ)', '(o|ơ|ở)', '(o|ơ|ỡ)', '(o|ơ|ợ)', '(o|ô)', '(o|ô|ố)', '(o|ô|ồ)', '(o|ô|ổ)', '(o|ô|ỗ)', '(o|ô|ộ)',
			'(u|ú)', '(u|ù)', '(u|ủ)', '(u|ũ)', '(u|ụ)', '(u|ư)', '(u|ư|ứ)', '(u|ư|ừ)', '(u|ư|ử)', '(u|ư|ữ)', '(u|ư|ự)',
			'(e|é)', '(e|è)', '(e|ẻ)', '(e|ẽ)', '(e|ẹ)', '(e|ê)', '(e|ê|ế)', '(e|ê|ề)', '(e|ê|ể)', '(e|ê|ễ)', '(e|ê|ệ)',
			'(y|ý)', '(y|ỳ)', '(y|ỷ)', '(y|ỹ)', '(y|ỵ)',
			'(i|í)', '(i|ì)', '(i|ỉ)', '(i|ĩ)', '(i|ị)',
			);
		return str_replace($_a, $_b, $str);
    }

    /**
	* Convertstring as json_endcode
	*/
    function convert_string_non_latin($var){
        require_once (JPATH_BASE.DS.'administrator'.DS.'components'.DS.'com_k2'.DS.'lib'.DS.'JSON.php');
        $json = new Services_JSON;
        $strlen_var = strlen($var);
        $ascii = '';
        for ($c = 0; $c < $strlen_var; ++$c) {

            $ord_var_c = ord($var{$c});

            switch (true) {
                case $ord_var_c == 0x08:
                    $ascii .= '\b';
                    break;
                case $ord_var_c == 0x09:
                    $ascii .= '\t';
                    break;
                case $ord_var_c == 0x0A:
                    $ascii .= '\n';
                    break;
                case $ord_var_c == 0x0C:
                    $ascii .= '\f';
                    break;
                case $ord_var_c == 0x0D:
                    $ascii .= '\r';
                    break;

                case $ord_var_c == 0x22:
                case $ord_var_c == 0x2F:
                case $ord_var_c == 0x5C:
                    // double quote, slash, slosh
                    $ascii .= '\\'.$var{$c};
                    break;

                case (($ord_var_c >= 0x20) && ($ord_var_c <= 0x7F)):
                    // characters U-00000000 - U-0000007F (same as ASCII)
                    $ascii .= $var{$c};
                    break;

                case (($ord_var_c & 0xE0) == 0xC0):
                    // characters U-00000080 - U-000007FF, mask 110XXXXX
                    // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                    $char = pack('C*', $ord_var_c, ord($var{$c + 1}));
                    $c += 1;
                    $utf16 = $json->utf82utf16($char);
                    $ascii .= sprintf('\u%04s', bin2hex($utf16));
                    break;

                case (($ord_var_c & 0xF0) == 0xE0):
                    // characters U-00000800 - U-0000FFFF, mask 1110XXXX
                    // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                    $char = pack('C*', $ord_var_c,
                    ord($var{$c + 1}),
                    ord($var{$c + 2}));
                    $c += 2;
                    $utf16 = $json->utf82utf16($char);
                    $ascii .= sprintf('\u%04s', bin2hex($utf16));
                    break;

                case (($ord_var_c & 0xF8) == 0xF0):
                    // characters U-00010000 - U-001FFFFF, mask 11110XXX
                    // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                    $char = pack('C*', $ord_var_c,
                    ord($var{$c + 1}),
                    ord($var{$c + 2}),
                    ord($var{$c + 3}));
                    $c += 3;
                    $utf16 = $json->utf82utf16($char);
                    $ascii .= sprintf('\u%04s', bin2hex($utf16));
                    break;

                case (($ord_var_c & 0xFC) == 0xF8):
                    // characters U-00200000 - U-03FFFFFF, mask 111110XX
                    // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                    $char = pack('C*', $ord_var_c,
                    ord($var{$c + 1}),
                    ord($var{$c + 2}),
                    ord($var{$c + 3}),
                    ord($var{$c + 4}));
                    $c += 4;
                    $utf16 = $json->utf82utf16($char);
                    $ascii .= sprintf('\u%04s', bin2hex($utf16));
                    break;

                case (($ord_var_c & 0xFE) == 0xFC):
                    // characters U-04000000 - U-7FFFFFFF, mask 1111110X
                    // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                    $char = pack('C*', $ord_var_c,
                    ord($var{$c + 1}),
                    ord($var{$c + 2}),
                    ord($var{$c + 3}),
                    ord($var{$c + 4}),
                    ord($var{$c + 5}));
                    $c += 5;
                    $utf16 = $json->utf82utf16($char);
                    $ascii .= sprintf('\u%04s', bin2hex($utf16));
                    break;
            }
        }
        return $ascii;
    }
}

class JAKeywordSearch
{
	protected $joomFish = 0;
	public $minLen = 3;
	public $maxLen = 20;
	public $db;
	public $config;
	protected $relevance = array();
	public function __construct($minLen = 3, $maxLen = 20) {
		$this->db = JFactory::getDbo();
		$this->config = JComponentHelper::getParams('com_jak2filter');
		if(!$minLen) $minLen = 1;
		$this->minLen = $minLen;
		$this->maxLen = $maxLen;
	}

	public function enableJoomFish() {
		$this->joomFish = 1;
	}

	public function build($keyword) {
		/*var_dump($keyword);
		var_dump('Minium Word Length Allowed: '.$this->minLen);
		var_dump('Maximum Word Length Allowed: '.$this->maxLen);*/
		$this->relevance = array();
		$terms = $this->_parseKeyword($keyword);
		$where = array();

		foreach($terms as $term) {
			if(is_array($term)) {
				$subwhere = array();
				for($i=0; $i<count($term); $i++) {
					$cond = $this->_parseTerm($term[$i]);
					if($cond) {
						$subwhere[] = is_array($cond) ? implode(' AND ', $cond) : $cond;
					}
				}
				$where[] = '('.implode(' OR ', $subwhere).')';
			} else {
				$cond = $this->_parseTerm($term);
				if($cond){
					$where[] = is_array($cond) ? implode(' AND ', $cond) : $cond;
				}
			}
		}
		$operator = $this->joomFish ? ' __AND__ ' : ' AND ';
		return count($where) ? implode($operator, $where) : '';
	}

	private function _parseKeyword($keyword) {
		//SPLIT UP SEARCH TERMS
		$matches = array();
		preg_match_all('/(?:-\s*)?(?:(?:intitle\:|intext\:|inmetadata\:|inmedia\:|inall\:)\s*)?(?:"[^"]+"|[^\s]+)/i', $keyword, $matches);
		$matches = $matches[0];
		$terms = array();
		for($i=0; $i < count($matches); $i++) {
			$term = $matches[$i];
			if($term == 'OR') {
				// OR keyword should not be placed on beginning position or ending position
				if(!count($terms)) continue;
				if(!isset($matches[$i+1])) continue;

				$last = count($terms) - 1;
				$nextTerm = $matches[++$i];
				$terms[$last] = array_merge((array)$terms[$last], array($nextTerm));
			} else {
				$terms[] = $term;
			}
		}
		//var_dump($terms);
		return $terms;
	}

	private function _parseTerm($term) {
		$prefix = '/^(intitle\:|intext\:|inmetadata\:|inmedia\:|inall\:)/';
		$fields = $this->config->get('fields_keyword', array());
		if(!is_array($fields) || !count($fields)) {
			$fields = array('i.title', 'i.introtext', 'i.`fulltext`', 'i.image_caption', 'i.image_credits', 'i.video_caption', 'i.video_credits', 'i.metadesc', 'i.metakey', 'i.extra_fields_search' );
		}
		$exact = false;
		$exclude = false;
		if(substr($term, 0, 1) == '-') {
			$exclude = true;
			$term = trim(substr($term, 1));
		}
		if(preg_match($prefix, $term, $matches)) {
			switch($matches[1]) {
				case 'intitle:':
					$fields = array('i.title');
					break;
				case 'intext:':
					$fields = array('i.introtext', 'i.`fulltext`');
					break;
				case 'inmetadata:':
					$fields = array('i.metadesc', 'i.metakey');
					break;
				case 'inmedia:':
					$fields = array('i.image_caption', 'i.image_credits', 'i.video_caption', 'i.video_credits');
					break;
				case 'inall:':
					$fields = array('i.title', 'i.introtext', 'i.`fulltext`', 'i.image_caption', 'i.image_credits', 'i.video_caption', 'i.video_credits', 'i.metadesc', 'i.metakey', 'i.extra_fields_search' );
					break;
			}
			$term = preg_replace($prefix, '', $term);
			$term = trim($term);
		}
		if(substr($term, 0, 1) == '"' && substr($term, strlen($term)-1, 1) == '"') {
			$exact = true;
			$term = substr($term, 1, strlen($term)-2);
		}

		if($exact) {
			$term = $this->_replaceWildcard($term);
			return $this->_buildSearch($term, $exclude, $fields);
		} else {
			$words = preg_split('/\s+/', $term);
			$where = array();
			foreach($words as $word) {
				$w = $this->_buildSearch($word, $exclude, $fields);
				if($w) $where[] = $w;
			}
			return $where;
		}
	}

	private function _buildSearch($term, $exclude, $fields) {
		if(empty($term)) return false;
		if(strlen($term) < $this->minLen || strlen($term) > $this->maxLen) return false;
		$search = array();
		$operator = $exclude ? ' NOT LIKE ' : ' LIKE ';
		foreach($fields as $field) {
			if(!$exclude) {
				$this->addRelevance($field, $term);
			}
			if($this->joomFish) {
				$field = preg_replace('/^[a-z_]+\./i', '', $field);//remove table prefix
				$search[] = '(jfc.value '.$operator . $this->quote('%'.$term.'%').' AND jfc.reference_field = '.$this->quote($field).')';
			} else {
				$search[] = $field . $operator . $this->quote('%'.$term.'%');
			}
		}

		$operator = $exclude ? ' AND ' : ' OR ';
		return '('.implode($operator, $search).')';
	}

	private function addRelevance($field, $term) {
		if(isset($this->relevance[$field])) {
			$this->relevance[$field][] = $term;
		} else {
			$this->relevance[$field] = array($term);
		}
	}

	public function getRelevances() {
		return $this->relevance;
	}

	private function quote($term) {
		return $this->db->quote($term);
	}

	private function _replaceWildcard($term) {
		return str_replace('*', '%', $term);
	}
}
