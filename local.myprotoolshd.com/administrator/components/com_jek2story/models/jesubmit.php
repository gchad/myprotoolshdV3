<?php
/**
* @package   JE K2 STORY
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
	
	function getSection()
	{
		$query = "SELECT id  as value, title as text FROM #__sections ";
		return	$this->_data = $this->_getList( $query );
	}
	
	function getCategory()
	{
		$query = "SELECT id  as value, title as text FROM #__categories ";
		return	$this->_data = $this->_getList( $query );
	}
	
	function getarticle()
	{
		$query = "SELECT id  as value, title as text FROM  #__k2_items WHERE published=1 AND trash=0";
		return	$this->_data = $this->_getList( $query );
	}
	
	function store($data)
	{
		$db1= & JFactory :: getDBO();
		$query1 = "UPDATE #__je_jek2submit set sectionid=".$data['sectionid'].",catid=".$data['catid'].",enabled=".$data['enabled'].",notify_email='".$data['notify_email']."',captcha=".$data['captcha'].",category='".$data['category']."',cat_id=".$data['cat_id']." where id=1";
		$db1->setQuery($query1);
		return true;
	}
	
	function &getCheck1()
	{
		$db= & JFactory :: getDBO();
		$query = 'SELECT * FROM #__je_jek2submit';
		$db->setQuery( $query );
		$res=$db->loadObject();
		return $res;
	}
	
	function getCat()
	{
		$db= & JFactory :: getDBO();
		$q = "SELECT id as value,name as text FROM  #__k2_categories WHERE published=1 AND trash=0 AND parent=0";
		$db->setQuery($q); 
		$parents=$db->loadObjectList();
		if(count($parents)!=0) {	
			for($i=0;$i<count($parents);$i++){
				$this->_cat_list[]= $parents[$i];
				$this->get_child($parents[$i]->value,0);
			}
			return $this->_cat_list;
		} else {
			return $parents;
		}
	}
	
	function get_child($id="",$count){
		$count++;
		$db= & JFactory :: getDBO();
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
	
	function &getCheck11()
	{
		$db1= & JFactory :: getDBO();
		$db11= & JFactory :: getDBO();
		$query1 = 'SELECT catid FROM #__je_jek2submit';
		$db1->setQuery( $query1 );
		$res1=$db1->loadResult();
			
		$query11 = 'SELECT title FROM #__categories WHERE published=1 AND trash=0 AND id='.$res1;
		$db11->setQuery( $query11 );
		$res11=$db11->loadResult();
		
		return $res11;
	}
}