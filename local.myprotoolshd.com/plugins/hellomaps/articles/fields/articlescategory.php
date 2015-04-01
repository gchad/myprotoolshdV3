<?php
/**
 * @version     1.0
 * @package     com_hellomaps
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      JoomlaForce Team <support@joomlaforce.com> - http://www.joomlaforce.com
 */
// no direct access
defined('_JEXEC') or die;
 
// import the list field type
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');
 
/** Form Field class for the component */
if(!class_exists('JFormFieldarticlescategory'))
{	
	class JFormFieldarticlescategory extends JFormFieldList
	{
		/** The field type.
		 * @var		string*/
		protected $type = 'JFormFieldarticlescategory';
	 
		/**Method to get a list of options for a list input.
		 * @return	array		An array of JHtml options.*/
		protected function getOptions() 
		{
			$db		= JFactory::getDBO();
		//	$query = "select * from #__categories where extension='com_content' order by lft";
			
			$query	= 'SELECT * FROM ' . $db->quoteName( '#__categories' ) . ' '
				. 'WHERE ' . $db->quoteName( 'extension' ) . '='.$db->quote( 'com_content')
				. 'AND ' . $db->quoteName( 'published' ) . '= 1';
					
			$db->setQuery( $query );
			$items = $db->loadObjectList();
			$options = array();
			if ($items)
			{			
				//$options[] = JHtml::_('select.option', '', JText::_('JSelect'));
				foreach($items as $item) 
				{
					$options[] = JHtml::_('select.option', $item->id, $item->title);
				}
			}
			$options = array_merge(parent::getOptions(), $options);
			return $options;
		}
	}	
}
