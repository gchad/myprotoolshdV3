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
//JFormHelper::loadFieldClass('hidden');
 
/**
 * Form Field class for the component
 */
 if(!class_exists('XiptHelperProfiletypes') && is_file(JPATH_SITE.'/components/com_xipt/includes.php'))
    require_once JPATH_SITE.'/components/com_xipt/includes.php';
if(!class_exists('JFormFieldMembersmarkers'))
{	
	class JFormFieldMembersmarkers extends JFormField
	{
		/**
		 * The field type.
		 *
		 * @var		string
		 */
		protected $type = 'Membersmarkers';
	 
		/**
		 * Method to get the field input markup for a custom marker list.		 
		 *
		 * @return  string  The field input markup.
		 *
		 * @since   11.1
		 */
		protected function getInput()
		{		
			$jsonData = array();
            if($this->isJomsocialInstalled())
            {
                if($this->value!="")
    			{
    				$jsonData = array();
    				$jsonValue = json_decode($this->value,true);
    				foreach($jsonValue as $jsonVal)
    				{
    					$jsonData[$jsonVal['profileTypeID']] = $jsonVal['profileMarkerImage'];
    				}
    			}
    			JHtml::_('bootstrap.framework');//load bootstrap framework of joomla	
    			$db		= JFactory::getDBO();
    			$document = JFactory::getDocument();
    			$document->addScript(JURI::root().'plugins/hellomaps/members/fields/membersmarkers.js');
    			
    			$query	= 'SELECT name AS text, id AS value FROM ' . $db->quoteName( '#__community_profiles' ) . ' '
    					. 'WHERE ' . $db->quoteName( 'published' ) . '=' . $db->Quote( 1 ) . ' '
    					. 'ORDER BY ' . $db->quoteName( 'ordering' );
    			$db->setQuery( $query );
    			$jomsocialProfileTypes = $db->loadObjectList();
                
                if(class_exists('XiptLibProfiletypes'))
    			     $xiptProfileTypes = XiptLibProfiletypes::getProfiletypeArray();//xipt profile types
    			
    			$fileOptions = array();
    			$markersFolder = JPATH_ROOT.'/plugins/hellomaps/members/images/markers';
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
    
    			$profileTypesHTML = '<ul class="profileTypesMarkers" style="list-style:none;">';
                if (!empty($jomsocialProfileTypes))
    			{					
    				foreach($jomsocialProfileTypes as $profileType) 
    				{
    					$attr = 'data-profile_type=jomsocial_'.$profileType->value;
    					$selectedVal = isset($jsonData['jomsocial_'.$profileType->value])?$jsonData['jomsocial_'.$profileType->value]:"";
    					$profileTypesHTML .= '<li class="jomsocial_profile_type" style="margin-bottom:10px;margin-left:0px;">'.$profileType->text.': '.JHtml::_('select.genericlist', $fileOptions, 'profile_type_marker['.'jomsocial_'.$profileType->value.']', $attr, 'value', 'text', $selectedVal, 'jomsocial_'.$profileType->value.'_marker').'</li>';					
    				}
    			}
    			if (!empty($xiptProfileTypes))
    			{					
    				foreach($xiptProfileTypes as $profileType) 
    				{
    					$attr = 'data-profile_type=xipt_'.$profileType->id;
    					$selectedVal = isset($jsonData['xipt_'.$profileType->id])?$jsonData['xipt_'.$profileType->id]:"";
    					$profileTypesHTML .= '<li class="xipt_profile_type" style="margin-bottom:10px;margin-left:0px;">'.$profileType->name.': '.JHtml::_('select.genericlist', $fileOptions, 'profile_type_marker['.'xipt_'.$profileType->id.']', $attr, 'value', 'text', $selectedVal, 'xipt_'.$profileType->id.'_marker').'</li>';					
    				}
    			}
                
                
    			$profileTypesHTML .= '</ul>';
    
    			// Initialize some field attributes.
    			$class = !empty($this->class) ? ' class="' . $this->class . '"' : '';
    			$disabled = $this->disabled ? ' disabled' : '';
    
    			// Initialize JavaScript field attributes.
    			$onchange = $this->onchange ? ' onchange="' . $this->onchange . '"' : '';
    
    			return $profileTypesHTML.'<input type="hidden" name="' . $this->name . '" id="' . $this->id . '" value="'
    				. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . $class . $disabled . $onchange . ' />';    
            }
            else
            {
                JError::raiseWarning(500, "Jomsocial is not installed or Enabled");
                
            }
			

			
		}
        
        private function isJomsocialInstalled()
        {
            /*  $db		= JFactory::getDBO();
            $sql = 'SELECT COUNT(*) AS total FROM #__extensions WHERE element="com_community" AND enabled=1';
            $db->setQuery( $sql );
            $db->query();
            return ($db->loadResult() == 1);*/
			$db = JFactory::getDbo();
			$db->setQuery("SELECT enabled,name,element FROM #__extensions WHERE name = 'community' or element = 'com_community' AND enabled=1");
            return ($db->loadResult() == 1);
        }
	}	
}
