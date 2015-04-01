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

if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
// require helper file
JLoader::register('HelloMapsHelper', dirname(__FILE__) . DS . 'helpers' . DS . 'hellomaps.php');

// Execute the task.
$controller = JControllerLegacy::getInstance('HelloMaps');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();