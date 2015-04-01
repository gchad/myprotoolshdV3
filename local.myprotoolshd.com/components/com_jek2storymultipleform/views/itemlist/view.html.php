<?php
/**
* @package   JE K2 Multiple Form STORY

* @copyright Copyright (C) 2009 - 2010 Open Source Matters. All rights reserved.

* @license   http://www.gnu.org/licenses/lgpl.html GNU/LGPL, see LICENSE.php

* Contact to : emailtohardik@gmail.com, joomextensions@gmail.com

* Visit : http://www.joomlaextensions.co.in/

**/ 



defined( '_JEXEC' ) or die( 'Restricted access' );


jimport( 'joomla.application.component.view' );
 
class itemlistViewitemlist extends JViewLegacy
{
	function __construct( $config = array())
	{
		 parent::__construct( $config );
	}
    
	function display($tpl = null)
	{	
		global $context;
		$mainframe = JFactory::getApplication();
		
		$document =  JFactory::getDocument();
		$document->setTitle( JText::_('ITEM_LISTING') );
   		 
   		
		$uri	= JFactory::getURI();
		
		$filter_order     = $mainframe->getUserStateFromRequest( $context.'filter_order',      'filter_order', 	  'id' );
		$filter_order_Dir = $mainframe->getUserStateFromRequest( $context.'filter_order_Dir',  'filter_order_Dir', '' );
		
				  
		$lists['order'] 		= $filter_order;  
		$lists['order_Dir'] = $filter_order_Dir;
		
		$subscribe			=  $this->get( 'Data');
		
		$total			=  $this->get( 'Total');
		
		$uri	= JFactory::getURI();
		$limit = JRequest::getVar('limit', 50, '', 'int');
		$limitstart = JRequest::getVar('limitstart', 0, '', 'int');
		
		jimport('joomla.html.pagination'); 
		
		
		$this->pagination = new JPagination($total, $limitstart, $limit);
		$this->assignRef('lists',		$lists);    
  		$this->assignRef('subscribe',		$subscribe); 		
	  	//$this->assignRef('request_url',	$uri->toString());    	
    	parent::display($tpl);
  }
}
?>
