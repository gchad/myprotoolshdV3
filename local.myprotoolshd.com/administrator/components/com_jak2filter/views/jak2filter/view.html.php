<?php
/**
 * ------------------------------------------------------------------------
 * JA K2 Filter Package for J25 & J3.3
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View class for a list of search terms.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_jak2filter
 * @since		1.5
 */
class Jak2filterViewJak2filter extends JAK2FilterView
{
	
	public function display($tpl = null)
	{

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	2.5
	 */
	protected function addToolbar()
	{
		JToolBarHelper::title(JText::_('COM_JA_K2_FILTER_ADMINISTRATOR'), 'search.png');
		JToolBarHelper::preferences('com_jak2filter');
		JToolBarHelper::custom('reindexing', 'refresh', 'refresh', JText::_('UPDATE_INDEXING'), false);
	}
}
