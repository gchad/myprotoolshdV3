<?php
/**
 * ------------------------------------------------------------------------
 * JA K2 Filter Package for J25 & J3.3
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_k2'.DS.'tables');
require_once JPATH_BASE.DS.'components'.DS.'com_k2'.DS.'helpers'.DS.'route.php';
require_once JPATH_BASE.DS.'components'.DS.'com_k2'.DS.'helpers'.DS.'permissions.php';
require_once JPATH_BASE.DS.'components'.DS.'com_k2'.DS.'helpers'.DS.'utilities.php';
require_once JPATH_BASE.DS.'components'.DS.'com_k2'.DS.'models'.DS.'item.php';
require_once JPATH_BASE.DS.'components'.DS.'com_k2'.DS.'models'.DS.'itemlist.php';
class JAK2FilterControllerItemlist extends K2FilterController{

    function display($cachable = false, $urlparams = false) {
    	error_reporting(E_ALL ^ E_NOTICE);

        JRequest::setVar('task', 'search');
        JRequest::setVar('view', 'itemlist');
		
        $model=$this->getModel('Itemlist','JAK2FilterModel');
        
        $modelitems = new K2ModelItem();
       
        $modelitems->getData();
        
        $document = JFactory::getDocument();
        
        $viewType = $document->getType();
      
        $view = $this->getView('itemlist', $viewType,'JAK2FilterView');
      
        $view->setModel($model);
        
        $view->setModel($modelitems);
        
        $user = JFactory::getUser();
        
        $cache = false;
        
        parent::display($cache);
    }
	function search(){
		$url = $this->buildUrlOfResultsPage();
		$this->setRedirect(JRoute::_($url, false));
	}
	
	function shareurl(){
		$url = $this->buildUrlOfResultsPage();
		echo JURI::base().substr(JRoute::_($url, false), strlen(JURI::base(true)) + 1);
		exit();
	}
	
	function buildUrlOfResultsPage(){
		$post   = JRequest::get('POST');
		$app	= JFactory::getApplication();
		$menu	= $app->getMenu();
		$items	= $menu->getItems('link', 'index.php?option=com_jak2fiter&view=search');

		if(isset($items[0])) {
			$post['Itemid'] = $items[0]->id;
		} elseif (JRequest::getInt('Itemid') > 0) { //use Itemid from requesting page only if there is no existing menu
			$post['Itemid'] = JRequest::getInt('Itemid');
		}
		unset($post['task']);
		unset($post['btnSubmit']);
		$uri = JURI::getInstance();
		//$uri->setQuery($post);
		$uri->setVar('option', 'com_jak2filter');
		$uri->setVar('view', 'itemlist');

		$swr = isset($post['swr']) ? (int) $post['swr'] : 0;//Search in whole slider range
		unset($post['swr']);

		foreach($post AS $key=>$value) {
			if(strpos($key, '_jacheck') !== false) {
				continue;
			}
			if(!$swr) {
				if(isset($post[$key.'_jacheck']) && $post[$key.'_jacheck'] == $value) {
					//for checking slider range field is selected or not
					continue;
				}
			}
			if($key == 'catMode' && (!isset($post['category_id']) || empty($post['category_id']))) {
				continue;
			}
			if(!empty($value)) {
				$uri->setVar($key, $value);
			}
		}
		$url = 'index.php'.$uri->toString(array('query', 'fragment'));
		$url = str_replace(array('<', '>'), array('',''), $url);
		
		return $url;
	}
}
