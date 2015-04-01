<?php
/**
 * ------------------------------------------------------------------------
 * JA K2 Filter Module for J25 & J3.3
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */
// no direct access
defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once dirname(__FILE__).'/helper.php';
require_once JPATH_ROOT.'/components/com_jak2filter/helpers/helper.php';
require_once JPATH_ROOT.'/components/com_jak2filter/helpers/upgrade.php';

/**
 * check if k2 component is installed
 */
if( JFile::exists(JPATH_BASE.DS.'components'.DS.'com_k2'.DS.'k2.php')){
	//check upgrade
	$helperUpgrade = new JAK2FilterHelperUpgrade();
	$helperUpgrade->checkUpdate();

	//INCLUDING ASSET
	require_once(dirname(__FILE__) . '/assets/behavior.php');
	include_once(dirname(__FILE__) . '/assets/asset.php');
	
	$app	= JFactory::getApplication();
	$db = JFactory::getDbo();
	$componentParams = JComponentHelper::getParams('com_jak2filter');

	$menu	= $app->getMenu();
	$active	= $menu->getActive();
	$active_id = isset($active) ? $active->id : $menu->getDefault()->id;
	$path	= isset($active) ? $active->tree : array();

	$maximum_keyword 		= (int) $componentParams->get('maximum_keyword', 20);
	$showAll				= $params->get('showAllChildren');
	$class_sfx				= htmlspecialchars($params->get('moduleclass_sfx'));
	$use_standard_fields   	= (int) $params->get('use_standard_fields', 1);
	$filter_by_extrafield	= $params->get('filter_by_extrafield', array());
	$filter_by_fieldtype 	= $params->get('filter_by_fieldtype');
	$keyword_default_mode 	= $params->get('keyword_default_mode', '');
	$slider_whole_range 	= (int) $componentParams->get('range_option', 0);

	$exgroups = '';
	if($use_standard_fields) {
		$filter_by_keyword   = (int) $params->get('filter_by_keyword', 1);
		$display_keyword_tip = (int) $params->get('display_keyword_tip', 1);
		$filter_by_category  = (int) $params->get('filter_by_category', 1);
		$filter_by_author    = (int) $params->get('filter_by_author', 1);
		$filter_by_tags		 = (int) $params->get('filter_by_tags', 0); 
		$filter_by_rating	 = (int) $params->get('filter_by_rating', 0);
		$filter_keyword_option	 = 0;//(int) $params->get('keyword_options', 1);
		$search_by_date	   	= (int) $params->get('search_by_date', 0);
		$catMode	 = (int) $params->get('catMode', 0);
	} else {
		$filter_by_keyword = 0;
		$display_keyword_tip = 0;
		$filter_by_category = 0;
		$filter_by_author = 0;
		$filter_by_tags = 0;
		$filter_by_rating = 0;
		$catMode	 = 0;
		$filter_keyword_option = 0;
		$search_by_date = 0;
	}
	
	$ja_stylesheet			= $params->get('ja_stylesheet');
	$auto_filter		= (int) $params->get('auto_filter');
	
	$ja_column = '';
	if($ja_stylesheet == 'horizontal-layout' && $params->get('ja_column') && $params->get('ja_column') > 0){
		$ja_column	= 'width:'.round(100/$params->get('ja_column'),2).'%;';
	}
	
	$helper = new modJak2filterHelper($module);
	
	/**
	 * Get list
	 */
	$list	= $helper->getList($filter_by_extrafield, $filter_by_fieldtype);
	if($slider_whole_range && count($list) > 1) {
		/**
		 * if form contains extra field from many Extra field group,
		 * then Search in whole range setting must be disabled to ensure that search return correct result
		 */
		if(count($helper->activeCats)) {
			$activeGroups = $helper->getextraFieldsGroupsByCat($helper->activeCats);
			if(count($activeGroups) > 1) {
				$slider_whole_range = 0;
			}
		} else {
			$slider_whole_range = 0;
		}
	}
	//search by date
	$filter_by_daterange = '';
	if($use_standard_fields && $search_by_date)
	{
		$filter_by_daterange = $helper->getSearchDateCreate();
	}
	
	//keyword
	$keyword_option =  '';
	if($use_standard_fields && $filter_keyword_option) {
		$keyword_option = $helper->getRadioKeywords();
	}
		
	//category
	$categories = '';
	$groupcategories = $params->get('k2catsid',null);
	
	if($use_standard_fields) {
		if($filter_by_category){		
			$categories = $helper->getCategories($groupcategories);
		} elseif ($groupcategories) {
			$categories  = $helper->getCategories($groupcategories,true);
		}
	} else {
		if(is_array($filter_by_extrafield) && count($filter_by_extrafield)) {
			$query = "
				SELECT DISTINCT ".$db->quoteName('c.id')." 
				FROM #__k2_categories c
				INNER JOIN #__k2_extra_fields xf ON xf.group = c.extraFieldsGroup
				WHERE xf.id IN (".implode(',', $filter_by_extrafield).")";
			$db->setQuery($query);
			
			if (version_compare(JVERSION, '3.0', 'ge'))
			{
			   $cids = $db->loadColumn(0);
			}
			else
			{
			   $cids = $db->loadResultArray(0);
			}
			if(count($cids)) {
				$categories  = '<input type="hidden" name="category_id" value="'.implode(',', $cids).'" />';
			}
		}
	}

	$selected_group = 0;
	if(count($_GET)) {
		$xf = array();
		foreach($_GET as $key => $val) {
			if(preg_match('/^xf_[0-9]+$/', $key)) {
				$xf[] = (int) (str_replace('xf_', '', $key));
			}
		}
		if(count($xf)) {
			$query = "SELECT `group` FROM #__k2_extra_fields WHERE `id` IN (".implode(',', $xf).") ORDER BY `group` DESC";
			$db->setQuery($query);
			$selected_group = $db->loadResult();
		}
	}

	//author
	$authors = '';
	if($use_standard_fields && $filter_by_author){
		$filter_author_display = $params->get('filter_author_display','author_display_name');
		$authors = $helper->getAuthors($filter_author_display);
		$authors_label = $helper->getLabel($params->get('filter_by_author_fieldtype', 'select'), 'created_by', JText::_('JAK2_AUTHOR'));
	}
	
	//tags
	$filter_by_tags_display = '';
	if($use_standard_fields && $filter_by_tags){
		$filter_by_tags_display = $helper->getTags();
		$filter_by_tags_label = $helper->getLabel($params->get('filter_by_tags_fieldtype', 'select'), 'tags_id', JText::_('JAK2_TAGS'));
	}
	
	//rating
	$filter_by_rating_display = '';
	if($use_standard_fields && $filter_by_rating){
		$filter_by_rating_display = $helper->getRatings();
	}

	//ordering
	$display_ordering = $helper->getOrderingList();

	//theme
	$theme = $params->get('theme', '');

	require JModuleHelper::getLayoutPath('mod_jak2filter', $params->get('layout', 'default'));
}else{
	echo JText::_('COMPONENT_K2_NOT_FOUND');
}