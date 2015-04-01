<?php
/**
 * @version     1.0
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
if(!class_exists('Articlescustomfields'))
{	
	class JFormFieldArticlescustomfields extends JFormFieldList
	{
		/**
		 * The field type.
		 *
		 * @var		string
		 */
		protected $type = 'articlescustomfields';
	 
		/**
		 * Method to get a list of options for a list input.
		 *
		 * @return	array		An array of JHtml options.
		 */
		protected function getOptions() 
		{
			$db		= JFactory::getDBO();
			$query = $db->getQuery(true);
			$query
			->select($db->quoteName(array('a.title','a.introtext','a.fulltext','b.username', 'b.name')))
			->from($db->quoteName('#__content', 'a'))
			->join('INNER', $db->quoteName('#__users', 'b') . ' ON (' . $db->quoteName('a.created_by') . ' = ' . $db->quoteName('b.id') . ')')
			->where($db->quoteName('b.username') . ' LIKE \'a%\'')
			->order($db->quoteName('a.created') . ' DESC');

			$db->setQuery( $query );
			$items = $db->loadObjectList();
			//print_r($items);
			$options = array();
			if ($items)
			{			
				$options[] = JHtml::_('select.option', 'username', JText::_('PLG_HELLOMAPS_ARTICLES_USERNAME'));
                $options[] = JHtml::_('select.option', 'name', JText::_('PLG_HELLOMAPS_ARTICLES_NAME'));
				$options[] = JHtml::_('select.option', 'title', JText::_('PLG_HELLOMAPS_ARTICLES_TITLE'));
				$options[] = JHtml::_('select.option', 'introtext', JText::_('PLG_HELLOMAPS_ARTICLES_INTROTEXT'));
				$options[] = JHtml::_('select.option', 'fulltext', JText::_('PLG_HELLOMAPS_ARTICLES_FULLTEXT'));
	
			}
			$options = array_merge(parent::getOptions(), $options);
			return $options;
		}
	}	
}
