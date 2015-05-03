<?php
/**
 * @package         Responsive Scroll Triggered Box
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2015 TM Extensions All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die('Restricted access');
if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

// import joomla controller library
jimport('joomla.application.component.controller');

// require helper file
JLoader::register('RstboxHelper', dirname(__FILE__) . DS . 'helpers' . DS . 'rstbox.php');
 
// Get an instance of the controller prefixed by RSTBox
$controller = JControllerLegacy::getInstance('Rstbox');

// Check for Plugin 
$plugin = JPluginHelper::isEnabled("system", "rstbox");
if ((!$plugin) && (!RstboxHelper::enableRenderPlugin())) {
	JError::raiseError(403, "Please enable \"Responsive Scroll Triggered Box Render\" Plugin");
}
 
// Perform the Request task
$input = JFactory::getApplication()->input;
$controller->execute($input->getCmd('task'));

RstboxHelper::loadassets();
 
// Redirect if set by the controller
$controller->redirect();

