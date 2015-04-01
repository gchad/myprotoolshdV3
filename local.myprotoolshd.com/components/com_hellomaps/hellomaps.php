<?php
/**
 * @version     1.0.7
 * @package     com_hellomaps
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      JoomlaForce Team <support@joomlaforce.com> - http://www.joomlaforce.com
 */
 
// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access'); 
// Include dependancies
jimport('joomla.application.component.controller');
if(!defined('HELLOMAPS_FRONT_URL'))
    require_once JPATH_ADMINISTRATOR.'/components/com_hellomaps/helpers/constants.php';
if(!class_exists('HelloMapsHelper'))
    require_once JPATH_ADMINISTRATOR.'/components/com_hellomaps/helpers/hellomaps.php';


// Launch the controller.
$controller = JControllerLegacy::getInstance('HelloMaps');

$controller->execute(JRequest::getCmd('task', 'display'));
$controller->redirect();    
