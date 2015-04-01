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
JFormHelper::loadFieldClass('list');

$filecommunity 	= JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/foundry.php';

	if( !JFile::exists( $filecommunity ) )
		{
			echo "<div class='alert alert-error' style='font-size: 18px;'>".JText::_('PLG_HELLOMAPS_EASYSOCIALMEMBERS_CHECK_FILE')."</div>";
			return;
	
		}

 
/**
 * Form Field class for the component
 */
if(!class_exists('Easysocialallfields'))
{	
	class JFormFieldEasysocialallfields extends JFormFieldList
	{
		/**
		 * The field type.
		 *
		 * @var		string
		 */
		protected $type = 'Easysocialallfields';
	 
		/**
		 * Method to get a list of options for a list input.
		 *
		 * @return	array		An array of JHtml options.
		 */
		protected function getOptions() 
		{
			
			$language = JFactory::getLanguage();		
			$language->load( 'com_easysocial', JPATH_ADMINISTRATOR, $language->getName(), true);
			$db		= JFactory::getDBO();
			/*$query	= 'SELECT title AS text, unique_key AS value FROM ' . $db->quoteName( '#__social_fields' ) . ' '
					. 'WHERE ' . $db->quoteName( 'state' ) . '=' . $db->Quote( 1 ) 
					. ' AND '.$db->quoteName( 'searchable' ) . '=' . $db->Quote( 1 ) 
					. ' AND '.$db->quoteName( 'visible_display' ) . '=' . $db->Quote( 1 )
					. ' GROUP BY ' . $db->quoteName( 'unique_key' ) ;
				//	. 'ORDER BY ' . $db->quoteName( 'ordering' );*/
				
			//Esus Add
			//new query for 1.3			
			$query ="SELECT title AS text, unique_key AS value, sfd.datakey, sfd.data, sfd.field_id"
				." FROM #__social_fields_data sfd "
				.' LEFT JOIN '.($db->quoteName('#__social_fields', 'a').'ON a.id = sfd.field_id')
				." WHERE sfd.type = 'user' "
				.' AND ' . $db->quoteName( 'state' ) . '=' . $db->Quote( 1 ) 
				.' AND '.$db->quoteName( 'searchable' ) . '=' . $db->Quote( 1 )
				.' AND '.$db->quoteName( 'visible_display' ) . '=' . $db->Quote( 1 )
				.' GROUP BY ' . $db->quoteName( 'unique_key' )
				. 'ORDER BY ' . $db->quoteName( 'ordering' );

			
			$db->setQuery( $query );
			$items = $db->loadObjectList();
			$options = array();
			if ($items)
			{			
				$options[] = JHtml::_('select.option', 'username', JText::_('PLG_HELLOMAPS_EASYSOCIALMEMBERS_USERNAME'));//put the username field
                $options[] = JHtml::_('select.option', 'useremail', JText::_('PLG_HELLOMAPS_EASYSOCIALMEMBERS_EMAIL'));//put the username field
				foreach($items as $item) 
				{
					
					if ($item->text!= "COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_ADDRESS")
					{
						$options[] = JHtml::_('select.option', $item->value, JText::_($item->text));
					}

				}
			}
			$options = array_merge(parent::getOptions(), $options);
			return $options;
		}
	}	
}
