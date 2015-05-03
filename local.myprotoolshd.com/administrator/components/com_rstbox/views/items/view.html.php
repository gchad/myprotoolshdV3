<?php
/**
 * @package     Responsive Scroll Triggered Box
 * @subpackage  com_rstbox
 *
 * @copyright   Copyright (C) 2014 Tassos Marinos - http://www.tassos.gr
 * @license     GNU General Public License version 2 or later; see http://www.tassos.gr/license
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
 
/**
 * Items View
 */
class RstboxViewItems extends JViewLegacy
{
    /**
     * Items view display method
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  mixed  A string if successful, otherwise a JError object.
     */
    function display($tpl = null) 
    {
        // Get data from the model
        $items = $this->get('Items');
        $pagination = $this->get('Pagination');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) 
        {
            JError::raiseError(500, implode('<br />', $errors));
            return false;
        }
        // Assign data to the view
        $this->items = $items;
        $this->pagination = $pagination;

        // Set the toolbar
        $this->addToolBar();

        // Display the template
        parent::display($tpl);
    }

    /**
     * Setting the toolbar
     */
    protected function addToolBar() 
    {
        JToolBarHelper::title(JText::_('COM_RSTBOX_ITEMS_HEADING'));
        JToolBarHelper::deleteList('', 'items.delete');
        JToolBarHelper::editList('item.edit');
        JToolBarHelper::addNew('item.add');
        JToolbarHelper::publish('items.publish', 'JTOOLBAR_PUBLISH', true);
        JToolbarHelper::unpublish('items.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        JToolbarHelper::preferences('com_rstbox');
    }
}