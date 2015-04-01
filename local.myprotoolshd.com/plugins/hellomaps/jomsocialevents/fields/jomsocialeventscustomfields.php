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
JFormHelper::loadFieldClass('list');
 
/**
 * Form Field class for the component
 */
if(!class_exists('Jomsocialeventscustomfields'))
{	
	class JFormFieldJomsocialeventscustomfields extends JFormFieldList
	{
		/**
		 * The field type.
		 *
		 * @var		string
		 */
		protected $type = 'Jomsocialeventscustomfields';
	 
		/**
		 * Method to get a list of options for a list input.
		 *
		 * @return	array		An array of JHtml options.
		 */
		protected function getOptions() 
		{
			
			$language = JFactory::getLanguage();		
			$language->load( 'com_jomsocialevents', JPATH_ADMINISTRATOR, $language->getName(), true);
			$db		= JFactory::getDBO();
			
			if($this->isJomsocialInstalled())
            {
			$query = ' SHOW COLUMNS  ';
			$query .=' FROM '.($db->quoteName('#__community_events'));
					
					
			$db->setQuery( $query );
			$items = $db->loadObjectList();
			$options = array();
			if ($items)
			{			
				//$options[] = JHtml::_('select.option', '', JText::_('JSelect'));
				foreach($items as $item) 
				{
					if (($item->Field== "title")
					  ||($item->Field== "location")
					  ||($item->Field== "summary")
					  ||($item->Field== "startdate")
					  ||($item->Field== "enddate")
					  ||($item->Field== "description")){
					$options[] = JHtml::_('select.option', $item->Field, $item->Field);
					  }
					}
				}
			}else{
                JError::raiseWarning(500, "Jomsocial is not installed");
            }
			$options = array_merge(parent::getOptions(), $options);
			return $options;
		}
		 private function isJomsocialInstalled()
        {
            $db		= JFactory::getDBO();
            $sql = 'SELECT COUNT(*) AS total FROM #__extensions WHERE element="com_community" AND enabled=1';
            $db->setQuery( $sql );
            $db->query();
            return ($db->loadResult() == 1);
        }
	}	
}
