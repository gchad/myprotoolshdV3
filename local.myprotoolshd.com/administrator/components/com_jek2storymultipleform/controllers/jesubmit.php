<?php
/**
* @package   JE K2 Multiple Form STORY
* @copyright Copyright (C) 2009 - 2010 Open Source Matters. All rights reserved.
* @license   http://www.gnu.org/licenses/lgpl.html GNU/LGPL, see LICENSE.php
* Contact to : emailtohardik@gmail.com, joomextensions@gmail.com
* Visit : http://www.joomlaextensions.co.in/
**/ 

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.controller' );
jimport('joomla.filesystem.file');

class jesubmitController extends JControllerForm  
{ 
	function __construct( $default = array())
	{
		parent::__construct( $default );
	}	
	 
	 
	function cancel($key = NULL)
	{
		$option = JRequest::getVar('option','','','string');
		$this->setRedirect ( 'index.php?option=' . $option  );
		return true;
	}
	 
	function save($key = NULL, $urlVar = NULL) 
	{
		$db1= & JFactory :: getDBO();
		$post = JRequest::get ( 'post' );
		
		$option = JRequest::getVar('option','','','string');
		$post["message"] = JRequest::getVar( 'message', '', 'post', 'string', JREQUEST_ALLOWRAW );
		$post["notify_message"] = JRequest::getVar( 'notify_message', '', 'post', 'string', JREQUEST_ALLOWRAW );
	
		$query1 = "UPDATE #__jemulti_jek2submit SET message='".$post['message']."',notify_message='".$post['notify_message']."' WHERE id=1";  
		
		$db1->setQuery($query1);
		$db1->query();
		
		if($db1)
		if($post['task'] == 'apply'){
			$this->setRedirect ( "index.php?option=".$option."&view=jesubmit" , JText::_( 'JE_SAVED') );
		}else{
			$this->setRedirect ( "index.php?option=".$option."&view=jesubmit" , JText::_( 'JE_SAVED') );
		}
		else
			$msg = JText::_ ( 'ERROR_SAVING_DETAIL' );
	}
	
	
}