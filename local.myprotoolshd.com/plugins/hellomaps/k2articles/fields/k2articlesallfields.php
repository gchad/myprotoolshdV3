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
if(!class_exists('K2articlesallfields'))
{	
	class JFormFieldK2articlesallfields extends JFormFieldList
	{
		/**
		 * The field type.
		 *
		 * @var		string
		 */
		protected $type = 'K2articlesallfields';
	 
		/**
		 * Method to get a list of options for a list input.
		 *
		 * @return	array		An array of JHtml options.
		 */
		protected function getOptions() 
		{
			
			$language = JFactory::getLanguage();		
			$language->load( 'com_k2', JPATH_ADMINISTRATOR, $language->getName(), true);
			$db		= JFactory::getDBO();            
			if($this->isK2Installed())
            {	
					$query = ' SHOW COLUMNS  ';
					$query .=' FROM '.($db->quoteName('#__k2_items'));

			$db->setQuery( $query );
			$items = $db->loadObjectList();
			$options = array();
			if ($items)
			{			
				$options[] = JHtml::_('select.option', 'username', JText::_('PLG_HELLOMAPS_K2ARTICLES_USERNAME'));//put the username field
                $options[] = JHtml::_('select.option', 'email', JText::_('PLG_HELLOMAPS_K2ARTICLES_EMAIL'));//put the username field
				foreach($items as $item) 
				{
					//Per escludere valori da backend
					/* if (($item->Field== "title")
					  ||($item->Field== "location")
					  ||($item->Field== "summary")
					  ||($item->Field== "description"))
					 {*/
					$options[] = JHtml::_('select.option', $item->Field,  $item->Field);
					//}
					
				}
			}
			}else{
                JError::raiseWarning(500, "K2 is not installed");
            } 
			$options = array_merge(parent::getOptions(), $options);
			return $options;
		}
		 private function isK2Installed()
        {
            $db	= JFactory::getDBO();
            $sql = 'SELECT COUNT(*) AS total FROM #__extensions WHERE element="com_k2" AND enabled=1';
            $db->setQuery( $sql );
            $db->query();
            return ($db->loadResult() == 1);
        }
	}	
}
