<?php
/**
* @package   JE K2 Multiple Form STORY
* @copyright Copyright (C) 2009 - 2010 Open Source Matters. All rights reserved.
* @license   http://www.gnu.org/licenses/lgpl.html GNU/LGPL, see LICENSE.php
* Contact to : emailtohardik@gmail.com, joomextensions@gmail.com
* Visit : http://www.joomlaextensions.co.in/
**/ 

defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.model' );

class jesubmitModeljesubmit extends JModelLegacy
{
	var $_id = null;
	var $_data = null;
	var $_product = null; // product data
	var $_table_prefix = null;
	var $_template = null;
	
	function __construct()
	{
		parent::__construct();
	}
	
	
	function store($data)
	{
		$db1=  JFactory :: getDBO();
		$query1 = "UPDATE #__jemulti_jek2submit set message =".$data['message'].",notify_message =".$data['notify_message ']." where id=1";
		$db1->setQuery($query1);
		return true;
	}
	
	function getCheck1()
	{
		$db=  JFactory :: getDBO();
		$query = 'SELECT * FROM #__jemulti_jek2submit'; 
		$db->setQuery( $query );
		$res=$db->loadObject();
		return $res;
	}
	
	
	
	function get_child($id="",$count){
		$count++;
		$db=  JFactory :: getDBO();
		$addquery	= "";
		$q = "SELECT id as value,name as text FROM  #__k2_categories WHERE published=1 AND trash=0 AND parent=".$id;
		$db->setQuery($q);
		$child=$db->loadObjectList();
			
		for($i=0;$i<count($child);$i++){
			$des ='';
			for($k=0;$k<$count;$k++) {
				$des.='  - ';
			}
			$child[$i]->text = $des.$child[$i]->text;
			$this->_cat_list[]= $child[$i];
			$this->get_child($child[$i]->value,$count);
		}
		
	}
	

}