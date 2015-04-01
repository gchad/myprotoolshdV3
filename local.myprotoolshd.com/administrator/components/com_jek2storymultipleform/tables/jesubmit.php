<?php

/**

* @package   JE K2 Multiple Form STORY

* @copyright Copyright (C) 2009 - 2010 Open Source Matters. All rights reserved.

* @license   http://www.gnu.org/licenses/lgpl.html GNU/LGPL, see LICENSE.php

* Contact to : emailtohardik@gmail.com, joomextensions@gmail.com

* Visit : http://www.joomlaextensions.co.in/

**/ 

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.model');

class Tablejesubmit extends JTable
{
	var $id = null;
	var $catid = null;
	var $sectionid= null;
	var $enabled = null;

	var $notify_email =null;
	
		
	function Tablejesubmit(& $db) 
	{
	  $this->_table_prefix = '#__jemulti_';
			
		parent::__construct($this->_table_prefix.'jesubmit', 'id', $db);
	}

	function bind($array, $ignore = '')
	{
		if (key_exists( 'params', $array ) && is_array( $array['params'] )) {
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = $registry->toString();
		}

		return parent::bind($array, $ignore);
	}
	
}
?>
