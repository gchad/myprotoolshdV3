<?php
/**
 * ------------------------------------------------------------------------
 * JA Promo Bar module
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */
defined('_JEXEC') or die('Restricted access');

$app = JFactory::getApplication();
$doc = JFactory::getDocument();
$basepath = JURI::root(true).'/modules/' . $module->module . '/assets/';

require_once(dirname(__FILE__).'/behavior.php');

$doc->addStyleSheet($basepath.'style.css');
//load override css
$templatepath = 'templates/'.$app->getTemplate().'/css/'.$module->module.'.css';
if(file_exists(JPATH_SITE . '/' . $templatepath)) {
	$doc->addStyleSheet(JURI::root(true).'/'.$templatepath);
}

//script
JHtml::_('JABehavior.jquery');
JHtml::_('JABehavior.jqueryeasing');
if($countdown) {
	$doc->addScript($basepath.'jquery.countdown.js');
}