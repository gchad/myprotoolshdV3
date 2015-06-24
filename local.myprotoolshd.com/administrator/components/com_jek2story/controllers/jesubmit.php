<?php
/**
* @package   JE K2 STORY
* @copyright Copyright (C) 2009 - 2010 Open Source Matters. All rights reserved.
* @license   http://www.gnu.org/licenses/lgpl.html GNU/LGPL, see LICENSE.php
* Contact to : emailtohardik@gmail.com, joomextensions@gmail.com
* Visit : http://www.joomlaextensions.co.in/
**/ 

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.controller' );
jimport('joomla.filesystem.file');

class jesubmitController extends JControllerLegacy  
{ 
	function __construct( $default = array())
	{
		parent::__construct( $default );
	}	
	 
	 
	function cancel()
	{
		$option = JRequest::getVar('option','','','string');
		$this->setRedirect ( 'index.php?option=' . $option  );
		return true;
	}
	 
	function apply()
	{
		$db1= & JFactory :: getDBO();
		$post = JRequest::get ( 'post' );
		$option = JRequest::getVar('option','','','string');
	/*	echo '<pre>';
		print_r($post);
		exit;*/
		$cnt	= @implode(',',$post['cat_id']);
  		$post['cat_id']				= $cnt;
		//$post['allow_reguser']		= $allow_user;
		
		$post["message"] = JRequest::getVar( 'message', '', 'post', 'string', JREQUEST_ALLOWRAW );
		$post["notify_message"] = JRequest::getVar( 'notify_message', '', 'post', 'string', JREQUEST_ALLOWRAW );
		
		
				
		$query1 = "UPDATE #__je_jek2submit SET title='".$post['title']."',notify_email='".$post['notify_email']."',message='".$post['message']."',notify=".$post['notify'].",notify_message='".$post['notify_message']."',captcha=".$post['captcha'].",itemid=".$post['itemid'].",term=".$post['terms'].",category='".$post['category']."',cat_id='".$post['cat_id']."',allow_reguser=".$post['allow_reguser'].", auto_publish=".$post['auto_publish'].", pageurl=".$post['pageurl'].",name=".$post['name'].", email=".$post['email'].", publish=".$post['publish']." WHERE id=1";  
		$db1->setQuery($query1);
		$db1->query();
		
		if($post['allow_reguser']==0){
		$sql_query1 = "UPDATE #__je_jek2submit SET name=1, email=1 WHERE id=1";  
		}else{
		$sql_query1 = "UPDATE #__je_jek2submit SET name=0, email=0 WHERE id=1";  
		}
		$db1->setQuery($sql_query1);
		$db1->query();
		
		if($db1)
			$this->setRedirect ( "index.php?option=$option&view=jesubmit" , JText::_( 'JE_SAVED') );
		else
			$msg = JText::_ ( 'ERROR_SAVING_DETAIL' );
	}
	
	function getCat()
	{  
		$option = JRequest::getVar('option','','','string');
		$did = JRequest::getVar('did',0);		
		$model = $this->getModel ('jesubmit');
		$rdata=$model->getCat($did);
		$res=$model->get('Check1');
	
		if($rdata!="") {
			$sel_cat = array();
			$sel_cat[]  = JHTML::_('select.option', '0 ', JText::_( 'All Category'));
			
			$rdata=@array_merge($sel_cat,$rdata);
		 	echo  JHTML::_('select.genericlist',$rdata,  'catid', 'class="inputtext"  ', 'value', 'text',$this->rdata->catid );
		} else {
			
			$sel_cat[]  = JHTML::_('select.option', '0 ', JText::_( 'All Category'));
			echo $lists['catid'] 	= JHTML::_('select.genericlist',$sel_cat,  'catid', 'class="inputtext"  ', 'value', 'text',$this->rdata->catid );
		}
		exit;
	}
   
}