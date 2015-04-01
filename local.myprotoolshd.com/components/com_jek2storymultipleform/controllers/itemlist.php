<?php
/**
* @package   JE K2 Multiple Form STORY
* @copyright Copyright (C) 2009 - 2010 Open Source Matters. All rights reserved.
* @license   http://www.gnu.org/licenses/lgpl.html GNU/LGPL, see LICENSE.php
* Contact to : emailtohardik@gmail.com, joomextensions@gmail.com
* Visit : http://www.joomlaextensions.co.in/
**/  



defined ('_JEXEC') or die ('Restricted access');

 jimport( 'joomla.application.component.controller' );
 
class itemlistController extends JControllerForm
{
	function __construct( $default = array())
	{
		parent::__construct( $default );
	}	
	function cancel($key = NULL)
	{
		$this->setRedirect( 'index.php' );
	}
	function display($cachable = false, $urlparams = '') {
		$user = clone(JFactory::getuser());
		
		if($user->id!=0)
			
			parent::display();
			
		else
		{
			$msg ='Please login before view this page';
			$this->setRedirect( 'index.php',$msg );
			
		}
	}
	
	function published_story(){
		$mainframe = JFactory::getApplication();
		$option = JRequest::getVar('option','','request','string');
		$nid = JRequest::getVar ( 'nid', '', 'post', 'int' ); 
		$uid = JRequest::getVar ( 'uid', '', 'post', 'int' ); 
		$itemno = JRequest::getVar ( 'itemno', '', 'post', 'int' ); 
		
		if($nid == 1){
			$id = 0;
		}else if($nid == 0){
			$id = 1;
		}
		$db = JFactory::getDbo();
		$query1 = 'UPDATE #__jemulti_k2itemlist'. ' SET published = ' .$id. ' WHERE id ='.$uid; 
		$db->setQuery( $query1 );
		$db->query();

		$query = 'UPDATE #__k2_items'. ' SET published = ' .$id. ' WHERE id ='.$itemno; 
		$db->setQuery( $query );
		
		if(!$db->query()){
			$msg = JText::_ ( 'VIDEO_APPROVAL_ERROR' );
		}else{
			$msg = JText::_ ( 'VIDEO_APPROVAL_SUCCESSFULLY' );
		}
		$mainframe->redirect ( 'index.php?option='.$option.'&view=itemlist&publish_id=1&automatic_id=1' );		
	}
	function delete()
	{

		$option = JRequest::getVar('option','','request','string');
		$mainframe = &JFactory::getApplication();
		$checkarray = array();
		$post = JRequest::get ( 'post' );
		

		if($post['toggle'] == 'delete')
		{

		$checkarray = $post['checkbox'];
		$cids = implode( ',', $checkarray );
		
		$db = JFactory::getDbo();
		
		$query = 'DELETE FROM #__jemulti_k2itemlist WHERE itemid IN ( '.$cids.' )';
		$db->setQuery( $query );
		$db->query();
		
		$query_1 = 'DELETE FROM #__k2_items WHERE id IN ( '.$cids.' )';
		$db->setQuery( $query_1 );
		
		if(!$db->query())
		{
			$msg = JText::_ ( 'DETAIL_NOT_DELETED_SUCCESSFULLY' );
		}else{
			$msg = JText::_ ( 'DETAIL_DELETED_SUCCESSFULLY' );	
		}
		
		
		}
		
	$mainframe->redirect ( 'index.php?option='.$option.'&view=itemlist',$msg );	
	}
	
	function getitem()
	{ 
	
		$option = JRequest::getWord('option','','','string');
		//$item_id1 = JRequest::getVar('item_id1','','','int');
		$mainframe =& JFactory::getApplication();
		$context = '';
		$item_id1		= $mainframe->getUserStateFromRequest( $context.'item_id1', 'item_id1',  '0');
		$k2category		= $mainframe->getUserStateFromRequest( $context.'k2category', 'k2category',  '0');

		$k2category = JRequest::getVar('k2category','','','int'); 
		$model = $this->getModel ( 'itemlist' );
		$rdata=$model->getitemajax($k2category);
		$sel_data = array();
		$sel_data[0]->value="0";
		$sel_data[0]->text=JText::_('SELECT_ITEM');
		
		$rdata=@array_merge($sel_data,$rdata);
		
		echo $lists['item_id1'] = JHTML::_('select.genericlist',$rdata,  'item_id1', 'class="inputtext" style="width:190px; "', 'value', 'text',$item_id1);
		
		exit;
	} 

	
}	

?>