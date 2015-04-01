<?php
/**
 * @version     1.0.8
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

$filecommunity 	= JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/foundry.php';

	if( !JFile::exists( $filecommunity ) )
		{
			echo "<div class='alert alert-error' style='font-size: 18px;'>".JText::_('PLG_HELLOMAPS_EASYSOCIALMEMBERS_CHECK_FILE')."</div>";
			return;
	
		}
 
/**
 * Form Field class for the component
 */
if(!class_exists('JFormFieldEasysocialMembersmarkers'))
{	
	class JFormFieldEasysocialMembersmarkers extends JFormField
	{
		/**
		 * The field type.
		 *
		 * @var		string
		 */
		protected $type = 'easysocialmembersmarkers';
	 
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
			$document->addScript(JURI::root().'plugins/hellomaps/easysocialmembers/fields/easysocialmembersmarkers.js');
					
			$query	= 'SELECT title AS text, id AS value FROM ' . $db->quoteName( '#__social_profiles' ) . ' '
					. 'WHERE ' . $db->quoteName( 'state' ) . '=' . $db->Quote( 1 ) . ' '
					. 'ORDER BY ' . $db->quoteName( 'ordering' );
			$db->setQuery( $query );
			$profileTypes = $db->loadObjectList();
			
			$fileOptions = array();
			$markersFolder = JPATH_ROOT.'/plugins/hellomaps/easysocialmembers/images/markers';
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

			$profileTypesHTML = '<ul class="easysocialmembersmarkers" style="list-style:none;">';
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

			
		}
	}	
}
