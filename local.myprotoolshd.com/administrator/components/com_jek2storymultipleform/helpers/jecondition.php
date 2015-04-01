<?php
/**
* @package   JE K2 Multiple Form STORY
* @copyright Copyright (C) 2009 - 2010 Open Source Matters. All rights reserved.
* @license   http://www.gnu.org/licenses/lgpl.html GNU/LGPL, see LICENSE.php
* Contact to : emailtohardik@gmail.com, joomextensions@gmail.com
**/
 
defined('_JEXEC') or die( 'Restricted access' );

class JFormFieldjecondition extends JFormFieldList
{
	protected $type 	= 'jecondition';
	public $_cat_list 	= NULL ;
	function getInput()
	{
		$parents	= null;
		$db = JFactory::getDBO();
		$query = "SELECT id  as value, title as text FROM  #__k2_items WHERE published=1 AND trash=0"; 
		$db->setQuery( $query );
		$parents = $db->loadObjectList();
		
		
			
		/*if(count($parents)!=0) {	
			for($i=0;$i<count($parents);$i++){
				$this->_cat_list[]= $parents[$i];
				$this->get_child($parents[$i]->value,0);
			}
			$eventdata	= $this->_cat_list;
		} else {*/
			$eventdata	= $parents;
		//}
		
		/*$sel_pcat[0]->text	= JText::_('SELECT_K2_ITEM');	
		$sel_pcat[0]->value	= '0';*/
		
		$sel_pcat[]  = JHTML::_('select.option', '0 ', JText::_( 'SELECT_K2_ITEM'));
		$eventdata	= @array_merge($sel_pcat,$eventdata);
		return JHTML::_('select.genericlist',  $eventdata, $this->name, 'class="inputbox"', 'value', 'text', $this->value );
	}
	
	/*function get_child($id="",$count){
		$count++;
		$db= & JFactory :: getDBO();
		
		 $q = "SELECT id  as value, title as text FROM  #__k2_items WHERE published=1 AND trash=0 AND parent=".$id;
		$db->setQuery($q);
		$child=$db->loadObjectList();
		
	for($i=0;$i<count($child);$i++){						
			$des ='';
			for($k=0;$k<$count;$k++) {
				$des.=' - ';	
			}
				
			$child[$i]->text = $des.$child[$i]->text;
			$this->_cat_list[]= $child[$i];
			$this->get_child($child[$i]->value,$count);
			echo '<pre>';
			print_r($this->get_child);
			
			
			
        	}
	    }*/
   }
  