<?php
/**
* @package   JE K2 Multiple Form STORY

* @copyright Copyright (C) 2009 - 2010 Open Source Matters. All rights reserved.

* @license   http://www.gnu.org/licenses/lgpl.html GNU/LGPL, see LICENSE.php

* Contact to : emailtohardik@gmail.com, joomextensions@gmail.com

* Visit : http://www.joomlaextensions.co.in/

**/ 


defined ('_JEXEC') or die ('Restricted access');

 
jimport('joomla.application.component.model');

class itemlistModelitemlist extends JModelLegacy
{
	var $_data = null;
	var $_total = null;
	var $_pagination = null;
	var $_table_prefix = null;
	
	function __construct()
	{
		parent::__construct();

		global $context; 
		$mainframe = JFactory::getApplication();
		$context='itemlist';
	  	$this->_table_prefix = '#__jemulti_';
		$this->_db = JFactory::getDbo();
		$this->cat_list      = null;
		
	}
	function getData()
	{		
		if (empty($this->_data))
		{
			$query = $this->_buildQuery();
			$this->_data = $this->_getList($query);
		}
	
		return $this->_data;
	}
	function _buildQuery()
	{
		$where = 'WHERE 1=1';
		$start = ( JRequest::getVar( 'limitstart', 0, '', 'int' ) );
		$user = clone(JFactory::getuser());
		$limit = intval(50);
		$mainframe = JFactory::getApplication();
		$context = '';
		
		$search_word  = JRequest::getVar('search_word','','request','string');
		
		
		if($search_word!=''){
			$where .= " AND (s.title LIKE '%".$search_word."%')";
		}    
		
		$where .= " AND s.trash != 1";
		
	  if(@$user->usertype=='Super Administrator')
			$where .= '';
		else
			$where .= " AND s.created_by= $user->id";
	   		
		$query = "SELECT i.*,s.created_by,s.title,s.introtext,s.published AS status,c.id AS categoryid,c.name AS categorynm FROM ".$this->_table_prefix."k2itemlist AS i INNER JOIN #__k2_items s ON i.itemid=s.id INNER JOIN #__k2_categories c ON c.id = s.catid ".$where." ORDER BY i.id ASC limit ".$start.",".$limit;
		
		return $query;
	}
	
	function getTotal()
	{
		
		$where = 'WHERE 1=1';
		$mainframe = JFactory::getApplication();
		$user = clone(JFactory::getuser());
		if (empty($this->_total))
		{
			
		$context = '';
		
		$search_word  = JRequest::getVar('search_word','','request','string');
		
		if($search_word!=''){
			$where .= " AND (s.title LIKE '%".$search_word."%')";
		}    
		
		$where .= " AND s.trash != 1";
		
	  if(@$user->usertype=='Super Administrator')
			$where .= '';
		else
			$where .= " AND s.created_by= $user->id";
			
			$query = "SELECT i.*,s.title,s.introtext,s.published AS status,c.id AS categoryid,c.name AS categorynm FROM ".$this->_table_prefix."k2itemlist AS i INNER JOIN #__k2_items s ON i.itemid=s.id INNER JOIN #__k2_categories c ON c.id = s.catid ".$where." ORDER BY i.id ASC ";
			$this->_total = $this->_getListCount($query);
			
		}
		
 		return $this->_total;

	}
	function getPagination()
	{
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}
		return $this->_pagination;
	}
	
	
	
	
	function getcategory()
	{
	
	$db = JFactory::getDbo();
	
	$query = "SELECT * FROM ".$this->_table_prefix."category ORDER BY id ASC";
	
	$db->setQuery($query);

	$row_data=$db->loadObjectList();
	return $row_data;
	
	}
	
	function getSetting() {
		$db=  JFactory :: getDBO();
		$query = 'SELECT * FROM #__jemulti_jek2submit where id = 1';
		$db->setQuery($query);
	   	return $db->loadObject();
	}
	
	function getk2category()
	{
	
	$db = JFactory::getDbo();
	
	$user = clone(JFactory::getuser());
	
	$query_q = 'SELECT distinct(i.catid) FROM #__k2_items AS i INNER JOIN  #__jemulti_k2itemlist AS s ON i.id = s.itemid WHERE s.userid = '.$user->id.' ORDER BY s.id ASC';
	$db->setQuery($query_q);
	$row_q=$db->loadObjectList();
	
	$res = '';
	for($i=0;$i<count($row_q);$i++){
		$res .= $row_q[$i]->catid.',';
	}
	$res1 = substr($res,0,-1);
	
	$query = 'SELECT id as value,name as text FROM  #__k2_categories WHERE id in ('.$res1.') AND published=1 ORDER BY id ASC';
	$db->setQuery($query);
	$row_data=$db->loadObjectList();
	return $row_data;
	
	}
	
	function getitem()
	{
		$db=  JFactory :: getDBO();
		$user = JFactory::getUser();
		$user_id = $user->id;
		$query = 'SELECT id as value,title as text FROM  #__k2_items WHERE published=1 AND created_by='.$user_id;
		$db->setQuery($query);
		$this->_data=$db->loadObjectList();
		
		return $this->_data;
	}
	
	function getitemajax($did="")
	{
		$db= JFactory :: getDBO();
		$user = JFactory::getUser();
		$user_id = $user->id;
		$query = 'SELECT title as text,id as value FROM #__k2_items WHERE catid='.$did.' AND created_by='.$user_id;
		$db->setQuery($query);
		$itemlist = $db->LoadObjectList();
		return $itemlist;
	}
	
	function getUsernm($user_id)
	{
		$db=  JFactory :: getDBO();
		$query = 'SELECT name FROM #__users WHERE id ='.$user_id;
		$db->setQuery($query);
		$this->_data=$db->loadObject();
		
		return $this->_data;
	}
	
	
	
}	



