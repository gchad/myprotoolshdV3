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
		
		$option	= JRequest::getVar('option', 'com_jek2story','','string');
		$document = & JFactory::getDocument();
		$document->addScript ('components/'.$option.'/assets/ajax.js');
		JHTML::_('behavior.keepalive');
		$res	=& $this->get('check1');
		
		// ============================js for extrafield editor		==========================================
	
		$document->addScript(JURI::root().'administrator/components/com_jek2story/lib/nicEdit.js');
		$js =
		"function initExtraFieldsEditor(){
			$$('.k2ExtraFieldEditor').each(function(element){
				new nicEditor({fullPanel: true, maxHeight: 180, iconsPath: '".JURI::root()."administrator/components/com_k2/images/system/nicEditorIcons.gif'}).panelInstance(element.getProperty('id'));
    		});
		}
		function syncExtraFieldsEditor(){
			$$('.k2ExtraFieldEditor').each(function(element){
				editor = nicEditors.findEditor(element.getProperty('id'));
				editor.saveContent();
    		});
		}
		";
			
		$document->addScriptDeclaration($js);
		//======================================= end of js ===========================================================
		// ================================ extra field code start here  ==========================================
		
		//define (JPATH_COMPONENT_ADMINISTRATOR,$k2admin_path);
		
		$id = JRequest::getVar('id','','','int');
		
		/*if($id){
		$k2admin_path = str_replace('com_jek2story','com_k2',JPATH_COMPONENT_ADMINISTRATOR);
		require_once($k2admin_path.DS.'models'.DS.'extrafield.php');
		$extraFieldModel= new K2ModelExtraField;
		$extraFields = $extraFieldModel->getExtraFieldsByGroup(1);
		
		for($i=0; $i<sizeof($extraFields); $i++){
			$extraFields[$i]->element=$extraFieldModel->renderExtraField($extraFields[$i]);
		}
		}*/
		
		$cat_id=0;
		if($id)
		{
			$detail=& $this->get('data');
			
			if(count($detail)>0)
				$cat_id = $detail->catid;
		
			$this->assignRef('detail',	$detail);
		}
		
		if(isset($_SESSION['catid'])){
			$cat_id = $_SESSION['catid'];
		}else{
			$cat_id=0;			
		}
		

		$category=& $this->get('cat');
        
        /** GCHAD FIX **/
        foreach ($category as $k => &$v){
            $v->text = JText::_('CATEGORY_'.$v->value);
        }
        
        
		$sel_section = array();
		$sel_section[]  = JHTML::_('select.option', '0 ', JText::_( 'SELECT_CATEGORY'));
		
		$category=@array_merge($sel_section,$category);

		$lists['catid'] 	= JHTML::_('select.genericlist',$category,  'catid', 'class="inputtext" onchange="select_cate(this.value)"  ', 'value', 'text',$cat_id );
		
		// ==========================================end of code ======================================================
	
		$call = JRequest::getVar('call','','','int');
		$setting = & $this->get('Setting');
		
		if($call==2)
		{
			$tpl="detail"; 
		}
		
		$this->assignRef('lists',	$lists);
		$this->assignRef('setting',	$setting);
		$this->assignRef('extraFields', $extraFields);
		$this->assignRef('res',	$res);
   		parent::display($tpl);
	}
}
?>