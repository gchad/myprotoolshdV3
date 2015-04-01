<?php
/**
* @package   JE K2 STORY
* @copyright Copyright (C) 2009 - 2010 Open Source Matters. All rights reserved.
* @license   http://www.gnu.org/licenses/lgpl.html GNU/LGPL, see LICENSE.php
* Contact to : emailtohardik@gmail.com, joomextensions@gmail.com
* Visit : http://www.joomlaextensions.co.in/
**/ 

defined ('_JEXEC') or die ('Restricted access');
$option = JRequest::getVar('option','','','string');
$controller = JRequest::getVar('view','jesubmit','','string' );
$task = JRequest::getVar('task','' );

if($controller=="about")
{
	require_once (JPATH_COMPONENT.'/'.'readme.html');
	require_once (JPATH_COMPONENT.'/'.'controller.php');
} else {
	require_once (JPATH_COMPONENT.'/'.'controller.php');
	//set the controller page
	require_once (JPATH_COMPONENT.'/'.'controllers'.'/'.$controller.'.php');
	//set the controller page 
	$classname  = $controller.'controller';
	//create a new class of classname and set the default task:display
	$controller = new $classname( array('default_task' => 'display') );
	// Perform the Request task
	$controller->execute( JRequest::getVar('task' ));
	// Redirect if set by the controller
	$controller->redirect();
}