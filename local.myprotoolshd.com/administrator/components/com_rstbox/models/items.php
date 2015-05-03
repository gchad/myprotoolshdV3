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
// import the Joomla modellist library
jimport('joomla.application.component.modellist');

class RstboxModelItems extends JModelList
{
    /**
     * Method to build an SQL query to load the list data.
     *
     * @return      string  An SQL query
     */
    protected function getListQuery()
    {
        // Create a new query object.           
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        // Select some fields from the item table
        $query
            ->select('b.*, vl.title as accessleveltitle')
            ->from('#__rstbox b')
            ->join('LEFT', $db->quoteName('#__viewlevels', 'vl') . ' ON (' . $db->quoteName('b.accesslevel') . ' = ' . $db->quoteName('vl.id') . ')');
        return $query;
    }
}