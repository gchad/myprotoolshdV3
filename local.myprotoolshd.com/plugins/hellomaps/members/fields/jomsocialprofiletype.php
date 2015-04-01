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
if(!class_exists('JFormFieldJomsocialprofiletype'))
{	
	class JFormFieldJomsocialprofiletype extends JFormFieldList
	{
		/**
		 * The field type.
		 *
		 * @var		string
		 */
		protected $type = 'Jomsocialprofiletype';
	 
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
                $query	= 'SELECT name AS text, id AS value FROM ' . $db->quoteName( '#__community_profiles' ) . ' '
					. 'WHERE ' . $db->quoteName( 'published' ) . '=' . $db->Quote( 1 ) . ' '
					. 'ORDER BY ' . $db->quoteName( 'ordering' );
    			$db->setQuery( $query );
    			$items = $db->loadObjectList();
    			
    			if ($items)
    			{			
    				//$options[] = JHtml::_('select.option', '', JText::_('JSelect'));
    				foreach($items as $item) 
    				{
    					$options[] = JHtml::_('select.option', $item->value, $item->text);
    				}
    			}    
            }
            else
            {
                JError::raiseWarning(500, "Jomsocial is not Installed or Enabled");
            }
			
			$options = array_merge(parent::getOptions(), $options);
			return $options;
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