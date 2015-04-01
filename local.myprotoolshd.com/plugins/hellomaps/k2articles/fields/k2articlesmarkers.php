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
if(!class_exists('JFormFieldK2articlesmarkers'))
{	
	class JFormFieldK2articlesmarkers extends JFormField
	{
		/**
		 * The field type.
		 *
		 * @var		string
		 */
		protected $type = 'k2articlesmarkers';
	 
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
			if($this->isK2Installed())
            {
				if($this->value!="")
				{
					$jsonData = array();
					$jsonValue = json_decode($this->value,true);
					foreach($jsonValue as $jsonVal)
					{
						$jsonData[$jsonVal['categoryTypeID']] = $jsonVal['profileMarkerImage'];
					}
				}
			JHtml::_('bootstrap.framework');//load bootstrap framework of joomla	
			$db		= JFactory::getDBO();
			$document = JFactory::getDocument();
			$document->addScript(JURI::root().'plugins/hellomaps/k2articles/fields/k2articlesmarkers.js');
					
			$query	= 'SELECT name AS text, id AS value FROM ' . $db->quoteName( '#__k2_categories' ) . ' '
					. 'ORDER BY ' . $db->quoteName( 'id' );
			$db->setQuery( $query );
			$profileTypes = $db->loadObjectList();
			
			$fileOptions = array();
			$markersFolder = JPATH_ROOT.'/plugins/hellomaps/k2articles/images/markers';
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

			$profileTypesHTML = '<ul class="k2articlesmarkers" style="list-style:none;">';
			if (!empty($profileTypes))
			{					
				foreach($profileTypes as $profileType) 
				{
					$attr = 'data-profile_type='.$profileType->value;
					$selectedVal = isset($jsonData[$profileType->value])?$jsonData[$profileType->value]:"";
					$profileTypesHTML .= '<li style="margin-bottom:10px;margin-left:0px;">'.$profileType->text.': '.JHtml::_('select.genericlist', $fileOptions, 'profile_type_marker['.$profileType->value.']', $attr, 'value', 'text', $selectedVal, $profileType->value.'_marker').'</li>';					
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
		
			}else{
                JError::raiseWarning(500, "K2 is not installed");
                return 'K2 is not installed';
            }
			
		}
		
		private function isK2Installed()
        {
            $db		= JFactory::getDBO();
            $sql = 'SELECT COUNT(*) AS total FROM #__extensions WHERE element="com_k2" AND enabled=1';
            $db->setQuery( $sql );
            $db->query();
            return ($db->loadResult() == 1);
        }
	}	
}
