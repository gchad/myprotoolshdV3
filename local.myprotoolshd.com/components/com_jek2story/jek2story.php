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
    $controller = JRequest::getVar('view','jesubmit' ); 
	$userviews = array('jesubmit','itemlist','itemlist_detail');
	$flag = 0;
	if (in_array( $controller, $userviews)) 
	{
		$flag = 1;
	}	

	if($flag)
	{ 
		//set the controller page
		require_once(JPATH_COMPONENT.'/'."helpers/k2helper.php");
		$storyitem =  k2helper::getdata();

		require_once (JPATH_COMPONENT.'/'.'controllers'.'/'.$controller.'.php');
		require_once(JPATH_COMPONENT.'/'."helpers/thumbnail.php");
		require_once(JPATH_COMPONENT.'/'."helpers/kcaptcha/kcaptcha.php");
		//set the controller page 
		$classname  = $controller.'controller';
		//create a new class of classname and set the default task:display
		$controller = new $classname( array('default_task' => 'display') );
		// Perform the Request task
		$controller->execute( JRequest::getVar('task','','','string' ));
		// Redirect if set by the controller
		$controller->redirect();
	}
	else
	{
		$mainframe = JFactory::getApplication();
		require_once(JPATH_COMPONENT.'/'."helpers/k2helper.php");
		$storyitem =  k2helper::getdata();
		
		$Itemid = JRequest::getVar('Itemid','','request','int');
		$option = JRequest::getVar('option','','request','string');
		$mainframe->redirect ( 'index.php?option=' . $option . '&view=jesubmit&Itemid='.$Itemid);
	}
?>
