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
if(!class_exists('Userscustomfields'))
{	
	class JFormFieldUserscustomfields extends JFormFieldList
	{
		/**
		 * The field type.
		 *
		 * @var		string
		 */
		protected $type = 'userscustomfields';
	 
		/**
		 * Method to get a list of options for a list input.
		 *
		 * @return	array		An array of JHtml options.
		 */
		protected function getOptions() 
		{
			$db		= JFactory::getDBO();            
							
			$query	= 'SELECT profile_key AS text, profile_key AS value FROM '.$db->quoteName('#__user_profiles')
			. ' WHERE '.$db->quoteName('profile_key') . 'LIKE "hellomapsusers.%"'
			//. ' ORDER BY ' . $db->quoteName( 'profile_key' )
			. ' GROUP BY ' . $db->quoteName( 'profile_key' );
			
			$db->setQuery( $query );
			$items = $db->loadObjectList();
			$options = array();
			if ($items)
			{			
				$options[] = JHtml::_('select.option', 'username', JText::_('PLG_HELLOMAPS_USERS_USERNAME'));
                $options[] = JHtml::_('select.option', 'useremail', JText::_('PLG_HELLOMAPS_USERS_EMAIL'));
			    $item = array();
				foreach($items as $item) 
				{
							
					//$options[] = JHtml::_('select.option', $item->value, $item->text);
							
					if (($item->text!= "hellomapsusers.mappajf")
						&&($item->text!= "hellomapsusers.spacer")
						&&($item->text!= "hellomapsusers.usrimage"))
					{
					$mynewtext = ucfirst(str_replace('hellomapsusers.', '', $item->text));
					$options[] = JHtml::_('select.option', $item->value, str_replace('hellomapsusers.', '', $mynewtext));
					}
				}
			}
			$options = array_merge(parent::getOptions(), $options);
			return $options;
		}
	}	
}
