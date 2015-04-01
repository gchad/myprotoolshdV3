<?php
 /**
* @package   JE K2 Multiple Form STORY
* @copyright Copyright (C) 2009 - 2010 Open Source Matters. All rights reserved.
* @license   http://www.gnu.org/licenses/lgpl.html GNU/LGPL, see LICENSE.php
* Contact to : emailtohardik@gmail.com, joomextensions@gmail.com
* Visit : http://www.joomlaextensions.co.in/
**/ 

defined('_JEXEC') or die ('restricted access');
jimport('joomla.application.component.view');

class jesubmitViewjesubmit extends JViewLegacy
{ 
      
   	function display ($tpl=null)
   	{ 
		$post = JRequest::get ( 'post' );	
		JToolBarHelper::apply();
		JToolBarHelper::title(   JText::_( 'JE K2 Multiple Form STORY' ) ); 
		$lists = array();		
		$option	= JRequest::getVar('option', 'com_jek2storymultipleform');
		
		$model = $this->getModel ( 'jesubmit' );
		$res= $this->get('Check1');
		
		$this->assignRef('res',$res);
   		parent::display($tpl);
  	}
}