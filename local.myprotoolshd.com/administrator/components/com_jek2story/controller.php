<?php
/**
* @package   JE K2 STORY
* @copyright Copyright (C) 2009 - 2010 Open Source Matters. All rights reserved.
* @license   http://www.gnu.org/licenses/lgpl.html GNU/LGPL, see LICENSE.php
* Contact to : emailtohardik@gmail.com, joomextensions@gmail.com
* Visit : http://www.joomlaextensions.co.in/
**/ 

jimport('joomla.application.component.controller');

$l['st']	= 'K2Story Setting';
$l['ab']	= 'About';
// Submenu view
$view	= JRequest::getVar( 'view', '', '', 'string', JREQUEST_ALLOWRAW );
if ($view == '' || $view == 'jesubmit') {
	JSubMenuHelper::addEntry(JText::_($l['st']), 'index.php?option=com_jek2story&view=jesubmit', true);
	JSubMenuHelper::addEntry(JText::_($l['ab']), 'index.php?option=com_jek2story&view=about');
}
if ($view == 'about') {
	JSubMenuHelper::addEntry(JText::_($l['st']), 'index.php?option=com_jek2story&view=jesubmit');
	JSubMenuHelper::addEntry(JText::_($l['ab']), 'index.php?option=com_jek2story&view=about', true);
}

?>