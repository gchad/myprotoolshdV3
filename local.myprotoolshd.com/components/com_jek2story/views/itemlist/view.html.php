<?php
/**
* @package   JE K2 STORY

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
		$mainframe = &JFactory::getApplication();
		
		$document = & JFactory::getDocument();
		$document->setTitle( JText::_('ITEM_LISTING') );
   		 
   		
		$uri	=& JFactory::getURI();
		
		$filter_order     = $mainframe->getUserStateFromRequest( $context.'filter_order',      'filter_order', 	  'id' );
		$filter_order_Dir = $mainframe->getUserStateFromRequest( $context.'filter_order_Dir',  'filter_order_Dir', '' );
		
				  
		$lists['order'] 		= $filter_order;  
		$lists['order_Dir'] = $filter_order_Dir;
		
		$subscribe			= & $this->get( 'Data');
		
		//$category			= & $this->get( 'category');
		//$subscribelist			= & $this->get( 'subscribe');
			$k2category			= & $this->get( 'k2category');
				
		$total			= & $this->get( 'Total');
		$sel_category 				=	 array();
		$sel_category[]  = JHTML::_('select.option', '0 ', JText::_( 'SELECT_CATEGORY'));
		
		$k2category=@array_merge($sel_category,$k2category);
		$catid = JRequest::getVar('k2category','0','request','int');
		$lists['k2category'] 		= 	JHTML::_('select.genericlist',$k2category,'k2category','class="inputtext" onchange="get_item(this.value)" ', 'value', 'text',$catid);

		$uri	=& JFactory::getURI();
		$limit = JRequest::getVar('limit', 15, '', 'int');
		$limitstart = JRequest::getVar('limitstart', 0, '', 'int');
		
		jimport('joomla.html.pagination'); 
		
		$this->pagination = new JPagination($total, $limitstart, $limit);
		
		
	
     	$this->assignRef('lists',		$lists);    
  		$this->assignRef('subscribe',		$subscribe); 		
    	//$this->assignRef('pagination',	$pagination);
		//$this->assignRef('category',		$category); 		
    	//$this->assignRef('subscribelist',	$subscribelist);
    	$this->assignRef('request_url',	$uri->toString());    	
    	parent::display($tpl);
  }
}
?>
