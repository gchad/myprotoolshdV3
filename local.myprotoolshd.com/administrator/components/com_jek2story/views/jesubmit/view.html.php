<?php
 /**
* @package   JE K2 STORY
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
		$mainframe = &JFactory::getApplication();
		// Request variables
		$document = & JFactory::getDocument();
		$document->setTitle( JText::_('JE K2 story') );
		
		JToolBarHelper::title(   JText::_( 'K2 story' ), 'generic.png' );
		$lists = array();		
		$option	= JRequest::getVar('option', 'com_jek2story');
		
		$model = $this->getModel ( 'jesubmit' );
		
		$article	=& $this->get('article');
		$res=& $this->get('Check1');
		
		$lists['terms'] 	= JHTML::_('select.booleanlist',  'terms', 'class="inputtext" ', $res->term );
		$lists['category'] 	= JHTML::_('select.booleanlist',  'category', 'class="inputtext" ', $res->category );
		$lists['notify'] 	= JHTML::_('select.booleanlist',  'notify', 'class="inputtext" ', $res->notify );
		$lists['captcha'] 	= JHTML::_('select.booleanlist',  'captcha', 'class="inputtext" ', $res->captcha );
		$lists['allow_reguser'] 	= JHTML::_('select.booleanlist',  'allow_reguser', 'class="inputtext" onclick="displaypayment(this.value)" ', $res->allow_reguser);
		
		$sel_article = array();
		@$sel_article[]  = JHTML::_('select.option', '0 ', JText::_( 'SELECT_ITEM'));
		
		$article=@array_merge($sel_article,$article);
		$lists['articleid'] 	= JHTML::_('select.genericlist',$article,  'itemid', 'class="inputtext"   ', 'value', 'text',$res->itemid );
		
		$cat	=& $this->get('Cat');
		$sel_category = array();
		$sel_category[]  = JHTML::_('select.option', '0 ', JText::_( 'SELECT_CATEGORY'));
		
		$cat	= @array_merge($sel_category,$cat);
		$mysel_cat	= @explode(',',$res->cat_id);
		$lists['cat_id']	= JHTML::_('select.genericlist',$cat,  'cat_id[]', 'class="inputtext" multiple="multiple" size="10"   ', 'value', 'text',$mysel_cat );
		
		
		$pageurl	= & $this->get('article');
		
		$sel_pageurl = array();
		@$sel_pageurl[]  = JHTML::_('select.option', '0 ', JText::_( 'SELECT_REDIRECTPAGE'));
		
	
		$pageurl=@array_merge($sel_pageurl,$pageurl);
		$lists['pageurl'] 	= JHTML::_('select.genericlist',$pageurl, 'pageurl', 'class="inputtext"   ', 'value', 'text',$res->pageurl );
		
		
		$lists['auto_publish'] 	= JHTML::_('select.booleanlist',  'auto_publish', 'class="inputtext" ', $res->auto_publish );
		$lists['name'] 	= JHTML::_('select.booleanlist',  'name', 'class="inputtext" ', $res->name );
		$lists['email'] 	= JHTML::_('select.booleanlist',  'email', 'class="inputtext" ', $res->email );
		$lists['publish'] 	= JHTML::_('select.booleanlist',  'publish', 'class="inputtext" ', $res->publish );
		
		JToolBarHelper::apply();
		$this->assignRef('lists',$lists);
		$this->assignRef('res',$res);
   		parent::display($tpl);
  	}
}