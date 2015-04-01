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
if(!class_exists('JFormFieldEasysocialprofiletype'))
{	
	class JFormFieldEasysocialprofiletype extends JFormFieldList
	{
		/**
		 * The field type.
		 *
		 * @var		string
		 */
		protected $type = 'JFormFieldEasysocialprofiletype';
	 
		/**
		 * Method to get a list of options for a list input.
		 *
		 * @return	array		An array of JHtml options.
		 */
		protected function getOptions() 
		{
			$db		= JFactory::getDBO();
					
			$query	= 'SELECT title AS text, id AS value FROM ' . $db->quoteName( '#__social_profiles' ) . ' '
					. 'WHERE ' . $db->quoteName( 'state' ) . '=' . $db->Quote( 1 ) . ' '
					. 'ORDER BY ' . $db->quoteName( 'ordering' );
			$db->setQuery( $query );
			$items = $db->loadObjectList();
			$options = array();
			if ($items)
			{			
				//$options[] = JHtml::_('select.option', '', JText::_('JSelect'));
				foreach($items as $item) 
				{
					$options[] = JHtml::_('select.option', $item->value, $item->text);
				}
			}
			$options = array_merge(parent::getOptions(), $options);
			return $options;
		}
	}	
}
