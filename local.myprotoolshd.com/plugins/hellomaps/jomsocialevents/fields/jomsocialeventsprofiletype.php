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
if(!class_exists('JFormFieldJomsocialeventsprofiletype'))
{	
	class JFormFieldJomsocialeventsprofiletype extends JFormFieldList
	{
		/**
		 * The field type.
		 *
		 * @var		string
		 */
		protected $type = 'JFormFieldJomsocialeventsprofiletype';
	 
		/**
		 * Method to get a list of options for a list input.
		 *
		 * @return	array		An array of JHtml options.
		 */
		protected function getOptions() 
		{
			$db		= JFactory::getDBO();
			$options = array();
			if($this->isJomsocialInstalled())
            {
				$query	= 'SELECT name AS text, id AS value FROM ' . $db->quoteName( '#__community_events_category' ) . ' '
						. 'ORDER BY ' . $db->quoteName( 'id' );
				$db->setQuery( $query );
				$items = $db->loadObjectList();
				
				if ($items)
				{			
					foreach($items as $item) 
					{
						$options[] = JHtml::_('select.option', $item->value, $item->text);
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
