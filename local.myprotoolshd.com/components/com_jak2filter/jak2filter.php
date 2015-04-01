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

$controller = JRequest::getWord('view', 'itemlist');
$task = JRequest::getWord('task');


jimport('joomla.filesystem.file');
jimport('joomla.html.parameter');

if (JFile::exists(JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php')) {
	if( JFile::exists(JPATH_BASE.DS.'components'.DS.'com_k2'.DS.'k2.php')) {
		JLoader::register('K2FilterController', JPATH_COMPONENT.DS.'controllers'.DS.'controller.php');
		JLoader::register('JAK2FilterModel', JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'model.php');
		JLoader::register('JAK2FilterView', JPATH_COMPONENT_ADMINISTRATOR.DS.'views'.DS.'view.php');
		JLoader::register('JAK2FilterHelper', JPATH_COMPONENT.DS.'helpers'.DS.'helper.php');
		JLoader::register('JAK2FilterHelperUpgrade', JPATH_COMPONENT.DS.'helpers'.DS.'upgrade.php');
		//load language from component k2
		$lang =JFactory::getLanguage();
		$lang->load('com_k2');

		//check upgrade
		$helper = new JAK2FilterHelperUpgrade();
		$helper->checkUpdate();
		
		require_once (JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php');
		$classname = 'JAK2FilterController'.$controller;
		$controller = new $classname();
		$controller->execute($task);
		$controller->redirect();
		
	}else{
		$app = JFactory::getApplication();
		$app->redirect('index.php',JText::_('COMPONENT_K2_NOT_FOUND'),'error');
		
	}
}
else {
	JError::raiseError(404, JText::_('COMPONENT_NOT_FOUND'));
}

