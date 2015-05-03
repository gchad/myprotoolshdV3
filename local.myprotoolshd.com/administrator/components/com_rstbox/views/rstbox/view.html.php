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
 
class RstboxViewRstbox extends JViewLegacy
{
        /**
         * Items view display method
         * @return void
         */
        function display($tpl = null) 
        {
        // Display the template
        parent::display($tpl);
        }
}