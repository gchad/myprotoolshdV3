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
	
	function getdata()
	{
		
		$db=  JFactory :: getDBO();
		
		 $id	= JRequest::getVar('id', null, '', 'int'); 
		 $query = "SELECT i.*,s.title,s.introtext,s.catid,s.fulltext FROM #__jemulti_k2itemlist AS i INNER JOIN #__k2_items s ON i.itemid=s.id WHERE i.itemid = ".$id." ORDER BY s.id ASC "; 	
	  	$db->setQuery( $query );
		$itemlist = $db->loadObject();
		//Image
			
		
		$itemlist->imageXSmall='';
		$itemlist->imageSmall='';
		$itemlist->imageMedium='';
		$itemlist->imageLarge='';
		$itemlist->imageXLarge='';

		if (JFile::exists(JPATH_SITE.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$itemlist->itemid).'_XS.jpg'))
		$itemlist->imageXSmall = JURI::root().'media/k2/items/cache/'.md5("Image".$itemlist->itemid).'_XS.jpg';

		if (JFile::exists(JPATH_SITE.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$itemlist->itemid).'_S.jpg'))
		$itemlist->imageSmall = JURI::root().'media/k2/items/cache/'.md5("Image".$itemlist->itemid).'_S.jpg';

		if (JFile::exists(JPATH_SITE.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$itemlist->itemid).'_M.jpg'))
		$itemlist->imageMedium = JURI::root().'media/k2/items/cache/'.md5("Image".$itemlist->itemid).'_M.jpg';

		if (JFile::exists(JPATH_SITE.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$itemlist->itemid).'_L.jpg'))
		$itemlist->imageLarge = JURI::root().'media/k2/items/cache/'.md5("Image".$itemlist->itemid).'_L.jpg';

		if (JFile::exists(JPATH_SITE.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$itemlist->itemid).'_XL.jpg'))
		$itemlist->imageXLarge = JURI::root().'media/k2/items/cache/'.md5("Image".$itemlist->itemid).'_XL.jpg';

		if (JFile::exists(JPATH_SITE.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$itemlist->itemid).'_Generic.jpg'))
		$itemlist->imageGeneric = JURI::root().'media/k2/items/cache/'.md5("Image".$itemlist->itemid).'_Generic.jpg';

		
		return $itemlist;
		
	}
	
	
	function getmsg()
	{
		$db=  JFactory :: getDBO();
		$query = 'SELECT message,notify_message FROM #__jemulti_jek2submit';
		$db->setQuery( $query );
		$res=$db->loadObject();
		return $res;
	}
	
	function getuser($id)
	{
		$query = "SELECT name FROM #__users Where id=".$id;
		return	$this->_data = $this->_getList( $query );
	}
	
	function getarticle()
	{
		$jesubmit	= $this->getmsg();
		
		//$articleid	= $jesubmit->itemid;
		$articleid = $_SESSION['term'];
		$query 		= "SELECT * FROM #__k2_items WHERE id=".$articleid;
		return	$this->_data = $this->_getList( $query );
	}
	
	function getCat()
	{
		$db=  JFactory :: getDBO();
		$user	= clone(JFactory::getUser());
		$mainframe = JFactory::getApplication();
		$redconfig 	= $mainframe->getParams();
	    $allow_reguser=@$_SESSION['allow_reguser'];
		
		$catid = @$_SESSION['catid'];
		 
		if($allow_reguser==1) {
			
			if($catid==0) {
				 $query = 'SELECT id as value,name as text FROM  #__k2_categories WHERE published=1 AND trash=0';
			} else {
				//echo $query = 'SELECT id as value,name as text FROM  #__k2_categories WHERE published=1 AND trash=0 AND id IN('.$catid.')';
				$where	= ' ';
		        $where .= ' AND id='.$catid;
				 $query = 'SELECT id as value,name as text FROM  #__k2_categories WHERE published=1 AND trash=0'.$where;
			}
			
		} 
		else 
		{
			if($user->id!=0) 
				$access = '';
			else
				$access = ' AND access=1';
				
			if($catid==0) {
				 $query = 'SELECT id as value,name as text FROM  #__k2_categories WHERE published=1 AND trash=0'.$access;
			} else {
				
				$where	= ' ';
		        $where .= ' AND id='.$catid;
				 $query = 'SELECT id as value,name as text FROM  #__k2_categories WHERE published=1 AND trash=0'.$where;
				
			}
		}
		$db->setQuery($query);
		$this->_data=$db->loadObjectList();
		
		return $this->_data;
	}

	function getextra()
	{
		$db=  JFactory :: getDBO();
		$query = 'SELECT * FROM  #__k2_extra_fields WHERE `group`=1 AND published=1 ORDER BY ordering';
		$db->setQuery($query);
	    $this->_data=$db->loadObjectList();
		return $this->_data;
	}
	
	function categorylimit($catid)
	{
		$query = "SELECT id  as value, title as text FROM  #__categories WHERE id=".$catid;
		return	$this->_data = $this->_getList( $query );
	}
	
	function getk2item($k2itemid) {
		$db=  JFactory :: getDBO();
		$query = 'SELECT id,title,alias,catid FROM  #__k2_items WHERE published=1 AND trash=0 AND id='.$k2itemid;
		$db->setQuery($query);
	   	return $db->loadObject();
	}
	
	function getSetting() {
		$db=  JFactory :: getDBO();
		$query = 'SELECT * FROM #__jemulti_jek2submit where id = 1';
		$db->setQuery($query);
	   	return $db->loadObject();
	}
	function getExtrafields($catid)
	{
	    
	    $itemid = JRequest::getVar('itemid', '', '', 'int' );
		$db=  JFactory :: getDBO();
		$query = 'SELECT extraFieldsGroup FROM #__k2_categories WHERE id='.$catid; 
		$db->setQuery( $query );
		$res = $db->loadObject();
		
		
		$k2admin_path = str_replace('com_jek2storymultipleform','com_k2',JPATH_COMPONENT_ADMINISTRATOR); 
		
		require_once($k2admin_path.DS.'models'.DS.'extrafield.php');
		$extraFieldModel= new K2ModelExtraField;
	   	$res->extraFieldsGroup; 
		$extraFields = $extraFieldModel->getExtraFieldsByGroup($res->extraFieldsGroup);
	
		for($i=0; $i<sizeof($extraFields); $i++){
			if($itemid)
			$extraFields[$i]->element=$extraFieldModel->renderExtraField($extraFields[$i],$itemid);
			else
			$extraFields[$i]->element=$extraFieldModel->renderExtraField($extraFields[$i]);
			}
			
		$extra_data ='<table>';
		
 		foreach ($extraFields as $extraField){
			$extra_data .='<tr>';
			$extra_data .='<th align="left">'.$extraField->name.'</th></tr><tr>';
			$extra_data .='<td>'.$extraField->element.'</td>';
			$extra_data .='</tr>';
		}
		$extra_data .='</table>';
		
		return $extra_data;
	}
	
	
}
?>