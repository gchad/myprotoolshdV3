<?php
/**
* @package   JE K2 STORY

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
	  	$this->_table_prefix = '#__je_';
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
		$where = '';
		$squery = '';
		$where = 'WHERE 1=1';
		$start = ( JRequest::getVar( 'limitstart', 0, '', 'int' ) );
		$user =  clone(JFactory::getUser());
		$db= & JFactory :: getDBO();
			
			$sup_sql_gr="SELECT id FROM  #__usergroups WHERE `title` = 'Super Users'";
			$db->setQuery($sup_sql_gr);
			$sup_data=$db->loadObject();
			$access	= $sup_data->id;
			
			$sql_gr="SELECT group_id FROM  #__user_usergroup_map WHERE user_id = ".$user->id;
			$db->setQuery($sql_gr);
			$gr_data=$db->loadObjectList();
			
			$row = array();
			
			for($i=0; $i<count($gr_data); $i++){
			$row[] = $gr_data[$i]->group_id;
			}
			
			$admin_user = '';
			if(in_array($access, $row)){
				$admin_user = '1'; 
			}else{
				$admin_user = '0';
			}
			
			

		$limit = intval(15);
		$mainframe = JFactory::getApplication();
		/*$context = '';
		$search_word  = JRequest::getVar('search_word','','request','string');
		
		
		if($search_word!=''){
			$squery .= " AND (s.title LIKE '%".$search_word."%')";
		}  
		$item_id1		= $mainframe->getUserStateFromRequest( $context.'item_id1', 'item_id1',  '0');
		$k2category		= $mainframe->getUserStateFromRequest( $context.'k2category', 'k2category',  '0');
		if($k2category!=0){
			$squery .= ' AND s.catid='.$k2category;
			}
		
		if($item_id1!=0){
		$squery .= ' AND i.itemid='.$item_id1;
		}
		
	  if($admin_user == 1)
	  {
			$squery .= '';
			
			}
		else
		{
			$squery .= " AND i.userid= $user->id";
	   		
			} 	
			 $query = "SELECT i.*,s.title,s.introtext,s.published AS status,c.id AS categoryid FROM ".$this->_table_prefix."k2itemlist AS i INNER JOIN #__k2_items s ON i.itemid=s.id INNER JOIN #__k2_categories c ON c.id = s.catid ".$squery." AND s.trash!=1 ORDER BY i.id ASC limit ".$start.",".$limit; */
			
			$context = '';
		
		$item_id1		= $mainframe->getUserStateFromRequest( $context.'item_id1', 'item_id1',  '0');
		$k2category		= $mainframe->getUserStateFromRequest( $context.'k2category', 'k2category',  '0');
		
		
		if($k2category!=0){
			$squery .= ' AND s.catid='.$k2category;
			}
		
		if($item_id1!=0){
		$squery .= ' AND i.itemid='.$item_id1;
		}
	 /* if(in_array('8',$user->groups))
		{	
			$condition = '';
		}else{
			$condition = " WHERE i.userid= $user->id";
		}	*/
	   	 if($admin_user == 1)
	  {
			$condition = '';
			
			}
		else
		{
			$condition = " WHERE i.userid= $user->id";
	   		
			} 		
			
			$squery .= ' AND s.trash != 1';
			
			$query = "SELECT i.*,s.title,s.introtext,s.published AS status,c.id AS categoryid FROM ".$this->_table_prefix."k2itemlist AS i INNER JOIN #__k2_items s ON i.itemid=s.id INNER JOIN #__k2_categories c ON c.id = s.catid ".$condition.$squery." ORDER BY i.id ASC limit ".$start.",".$limit;
			
		//$query = "SELECT s.*, u.username, u.email FROM #__k2_items s INNER JOIN #__users u where s.created_by = u.id AND s.created_by=".$user->id." ORDER BY s.id ASC ";
		return $query;
	}
	
	function getTotal()
	{
	
		
		$where = '';
		$squery = '';
		$where = 'WHERE 1=1';
		
		$user = clone(JFactory::getuser());
		$mainframe =& JFactory::getApplication();
		$context = '';
		$user =  clone(JFactory::getUser());
		$db= & JFactory :: getDBO();
			
			$sup_sql_gr="SELECT id FROM  #__usergroups WHERE `title` = 'Super Users'";
			$db->setQuery($sup_sql_gr);
			$sup_data=$db->loadObject();
			$access	= $sup_data->id;
			
			$sql_gr="SELECT group_id FROM  #__user_usergroup_map WHERE user_id = ".$user->id;
			$db->setQuery($sql_gr);
			$gr_data=$db->loadObjectList();
			
			$row = array();
			
			for($i=0; $i<count($gr_data); $i++){
			$row[] = $gr_data[$i]->group_id;
			}
			
			$admin_user = '';
			if(in_array($access, $row)){
				$admin_user = '1'; 
			}else{
				$admin_user = '0';
			}			 
			 
		if (empty($this->_total))
		{
		
		$item_id1		= $mainframe->getUserStateFromRequest( $context.'item_id1', 'item_id1',  '0');
		$k2category		= $mainframe->getUserStateFromRequest( $context.'k2category', 'k2category',  '0');
		if($k2category!=0){
			$squery .= ' AND s.catid='.$k2category;
			}
		
		if($item_id1!=0){
		$squery .= ' AND i.itemid='.$item_id1;
		}
	   if($admin_user == 1)
	  {
			$condition = '';
			
			}
		else
		{
			$condition = " WHERE i.userid= $user->id";
	   		
			} 		
	   		
			$squery .= ' AND s.trash != 1';
			
			 $query = "SELECT i.*,s.title,s.introtext,s.published AS status,c.id AS categoryid FROM ".$this->_table_prefix."k2itemlist AS i INNER JOIN #__k2_items s ON i.itemid=s.id INNER JOIN #__k2_categories c ON c.id = s.catid ".$condition.$squery." ORDER BY i.id ASC ";

		$this->_total = $this->_getListCount($query);
			
		}
		
 		return $this->_total;

	}
	function getPagination()
	{
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), JRequest::getVar( 'limitstart', 0 ,'','int'), 15 );
		}

		return $this->_pagination;
	}
  	
	
	
	
	/*function getcategory()
	{
	
	$db = JFactory::getDbo();
	
	$query = "SELECT * FROM ".$this->_table_prefix."category ORDER BY id ASC";
	
	$db->setQuery($query);

	$row_data=$db->loadObjectList();
	return $row_data;
	
	}*/
	
	function getSetting() {
		$db= & JFactory :: getDBO();
		$query = 'SELECT * FROM #__je_jek2submit where id = 1';
		$db->setQuery($query);
	   	return $db->loadObject();
	}
	
	function getk2category()
	{
	
	$db = JFactory::getDbo();
	$query = 'SELECT id as value,name as text FROM  #__k2_categories WHERE published=1 AND trash!=1 ORDER BY id ASC';
	$db->setQuery($query);
	$row_data=$db->loadObjectList();
	return $row_data;
	
	}
	
	function getitem()
	{
		$db= & JFactory :: getDBO();
		$user =& JFactory::getUser();
		$user_id = $user->id;
		$query = 'SELECT id as value,title as text FROM  #__k2_items WHERE published=1 AND created_by='.$user_id;
		$db->setQuery($query);
		$this->_data=$db->loadObjectList();
		
		return $this->_data;
	}
	
	function getitemajax($did="")
	{
		$db= & JFactory :: getDBO();
		$user =  clone(JFactory::getUser());
				
			$sup_sql_gr="SELECT id FROM  #__usergroups WHERE `title` = 'Super Users'";
			$db->setQuery($sup_sql_gr);
			$sup_data=$db->loadObject();
			$access	= $sup_data->id;
			
			$sql_gr="SELECT group_id FROM  #__user_usergroup_map WHERE user_id = ".$user->id;
			$db->setQuery($sql_gr);
			$gr_data=$db->loadObjectList();
			
			$row = array();
			
			for($i=0; $i<count($gr_data); $i++){
			$row[] = $gr_data[$i]->group_id;
			}
			
			$admin_user = '';
			if(in_array($access, $row)){
				$admin_user = '1'; 
			}else{
				$admin_user = '0';
			}
			 if($admin_user == 1)
	  {
			$condition = '';
			
			}
		else
		{
			$condition = ' AND created_by='.$user->id;
	   		
			} 		
    	$query = 'SELECT title as text,id as value FROM #__k2_items WHERE catid='.$did.' AND trash!=1 '.$condition;
		$db->setQuery($query);
		$itemlist = $db->LoadObjectList();
		return $itemlist;
	}
	
	
	
}	



