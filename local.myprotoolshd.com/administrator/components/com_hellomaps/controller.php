<?php
/**
 * @version     1.0.7
 * @package     com_hellomaps
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      JoomlaForce Team <support@joomlaforce.com> - http://www.joomlaforce.com
 */
 
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controller library
jimport('joomla.application.component.controller');

 
/**
 * General Controller of HelloWorld component
 */
class HelloMapsController extends JControllerLegacy
{
	/**
	 * display task
	 *
	 * @return void
	 */
	function display($cachable = false, $urlparams = false) 
	{
	    $document = JFactory::getDocument();
        $document->addStyleSheet(JURI::base().'components/com_hellomaps/assets/css/style.css');
		// set default view if not set
		JRequest::setVar('view', JRequest::getCmd('view', 'dashboard'));
 
		// call parent behavior
		parent::display($cachable);
		
		// Set the submenu
		HelloMapsHelper::addSubmenu('mapstyler');
	}
}