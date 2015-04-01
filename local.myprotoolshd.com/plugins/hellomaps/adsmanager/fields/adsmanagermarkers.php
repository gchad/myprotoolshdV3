<?php
/**
 * @version     1.0.7
 * @package     com_hellomaps
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      JoomlaForce Team <support@joomlaforce.com> - http://www.joomlaforce.com
 */
// no direct access
defined('_JEXEC') or die;
 
// import the list field type
jimport('joomla.form.helper');
jimport('joomla.filesystem.folder');
//JFormHelper::loadFieldClass('list');
 
/**
 * Form Field class for the component
 */
if(!class_exists('JFormFieldAdsmanagermarkers'))
{	
	class JFormFieldAdsmanagermarkers extends JFormField
	{
		/**
		 * The field type.
		 *
		 * @var		string
		 */
		protected $type = 'Adsmanagermarkers';
	    /**
		 * Method to get the field input markup for a custom marker list.		 
		 *
		 * @return  string  The field input markup.
		 *
		 * @since   11.1
		 */
		protected function getInput()
		{		
		    if($this->isAdsmanagerInstalled())
            {
                $jsonData = array();
    			if($this->value!="")
    			{
    				$jsonData = array();
    				$jsonValue = json_decode($this->value,true);
    				foreach($jsonValue as $jsonVal)
    				{
    					$jsonData[$jsonVal['categoryID']] = $jsonVal['categoryMarkerImage'];
    				}
    			}
    			JHtml::_('bootstrap.framework');//load bootstrap framework of joomla	
    			$db		= JFactory::getDBO();
    			$document = JFactory::getDocument();			
    			$document->addScript(JURI::root().'plugins/hellomaps/adsmanager/fields/adsmanagermarkers.js');
    			
    			$query = "SELECT c.name AS text, c.id AS value FROM #__adsmanager_categories as c WHERE c.published = 1 ORDER BY c.ordering ASC";
    			$db->setQuery( $query );
    			$adsManagerCategories = $db->loadObjectList();
    			
    			$fileOptions = array();
    			$markersFolder = JPATH_ROOT.'/plugins/hellomaps/adsmanager/images/markers';
    			$files = JFolder::files($markersFolder,'.png');//only png image is allowed
    			
    			if (!empty($files))
    			{			
    				$fileOptions[] = JHtml::_('select.option', '', JText::_('JSelect'));
    				foreach($files as $file) 
    				{
    					$fileNameWithoutExtension = JFile::stripExt($file);
    					$fileOptions[] = JHtml::_('select.option', $file,$fileNameWithoutExtension);
    				}
    			}
    
    			$adsManagerCategoriesHTML = '<ul class="adsManagerCategoryMarkers" style="list-style:none;">';
    			if (!empty($adsManagerCategories))
    			{					
    				foreach($adsManagerCategories as $adsManagerCategory) 
    				{
    					$attr = 'data-category_id='.$adsManagerCategory->value;
    					$selectedVal = isset($jsonData[$adsManagerCategory->value])?$jsonData[$adsManagerCategory->value]:"";
    					$adsManagerCategoriesHTML .= '<li style="margin-bottom:10px;margin-left:0px;">'.$adsManagerCategory->text.': '.JHtml::_('select.genericlist', $fileOptions, 'adsmanager_category_marker['.$adsManagerCategory->value.']', $attr, 'value', 'text', $selectedVal, $adsManagerCategory->value.'_marker').'</li>';					
    				}
    			}
    			$adsManagerCategoriesHTML .= '</ul>';
    
    			// Initialize some field attributes.
    			$class = !empty($this->class) ? ' class="' . $this->class . '"' : '';
    			$disabled = $this->disabled ? ' disabled' : '';
    
    			// Initialize JavaScript field attributes.
    			$onchange = $this->onchange ? ' onchange="' . $this->onchange . '"' : '';
    
    			return $adsManagerCategoriesHTML.'<input type="hidden" name="' . $this->name . '" id="' . $this->id . '" value="'
    				. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . $class . $disabled . $onchange . ' />';    
            }  
            else
            {
                JError::raiseError(500, "Adsmanager is not installed or enabled");
               
            }
			

			
		}
        private function isAdsmanagerInstalled()
        {
            /*$db		= JFactory::getDBO();
            $sql = 'SELECT COUNT(*) AS total FROM #__extensions WHERE element="com_adsmanager" AND enabled=1';
            $db->setQuery( $sql );
            $db->query();
			return ($db->loadResult() == 1);*/
			
			$db = JFactory::getDbo();
			$db->setQuery("SELECT enabled,name,element FROM #__extensions WHERE name = 'com_adsmanager' or element = 'com_adsmanager' AND enabled=1");
            return ($db->loadResult() == 1);
        }
	}	
}
