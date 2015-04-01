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
if(!class_exists('JFormFieldUsersmarkers'))
{	
	class JFormFieldUsersmarkers extends JFormField
	{
		/**
		 * The field type.
		 *
		 * @var		string
		 */
		protected $type = 'Usersmarkers';
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
					$jsonData[$jsonVal['categoryID']] = $jsonVal['categoryMarkerImage'];
				}
			}
			JHtml::_('bootstrap.framework');//load bootstrap framework of joomla	
			$db		= JFactory::getDBO();
			$document = JFactory::getDocument();			
			$document->addScript(JURI::root().'plugins/hellomaps/users/fields/usersmarkers.js');
			
			
			$query	= 'SELECT title AS text, id AS value FROM ' . $db->quoteName( '#__usergroups' ) . ' '
				//. 'WHERE ' . $db->quoteName( 'state' ) . '=' . $db->Quote( 1 ) . ' '
				. 'ORDER BY ' . $db->quoteName( 'id' );		
					
			$db->setQuery($query);
			$UsersCategories = $db->loadObjectList();
			
			$fileOptions = array();
			$markersFolder = JPATH_ROOT.'/plugins/hellomaps/users/images/markers';
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

			$UsersCategoriesHTML = '<ul class="UsersCategoryMarkers" style="list-style:none;">';
			if (!empty($UsersCategories))
			{					
				foreach($UsersCategories as $UsersCategory) 
				{
					$attr = 'data-category_id='.$UsersCategory->value;
					$selectedVal = isset($jsonData[$UsersCategory->value])?$jsonData[$UsersCategory->value]:"";
					$UsersCategoriesHTML .= '<li style="margin-bottom:10px;margin-left:0px;">'.$UsersCategory->text.': '.JHtml::_('select.genericlist', $fileOptions, 'users_category_marker['.$UsersCategory->value.']', $attr, 'value', 'text', $selectedVal, $UsersCategory->value.'_marker').'</li>';					
				}
			}
			$UsersCategoriesHTML .= '</ul>';

			// Initialize some field attributes.
			$class = !empty($this->class) ? ' class="' . $this->class . '"' : '';
			$disabled = $this->disabled ? ' disabled' : '';

			// Initialize JavaScript field attributes.
			$onchange = $this->onchange ? ' onchange="' . $this->onchange . '"' : '';

			return $UsersCategoriesHTML.'<input type="hidden" name="' . $this->name . '" id="' . $this->id . '" value="'
				. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . $class . $disabled . $onchange . ' />';

			
		}
	}	
}
