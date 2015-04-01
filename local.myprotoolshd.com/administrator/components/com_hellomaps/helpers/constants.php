<?php
/**
 * @version     1.0.7
 * @package     com_hellomaps
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      JoomlaForce Team <support@joomlaforce.com> - http://www.joomlaforce.com
 */
 
//define all necessary constants here for the component both for backend and front end

// No direct access to this file
defined('_JEXEC') or die;
if(!defined('HELLOMAPS_FRONT_URL') )
{	
	define('HELLOMAPS_FRONT_URL',JURI::root().'components/com_hellomaps/');
}
if(!defined('HELLOMAPS_FRONT_PATH') )
{	
	define('HELLOMAPS_FRONT_PATH',JPATH_SITE.'/components/com_hellomaps/');
}

if(!defined('HELLOMAPS_BACKEND_URL') )
{	
	define('HELLOMAPS_BACKEND_URL',JURI::root().'administrator/components/com_hellomaps/');
}
if(!defined('HELLOMAPS_BACKEND_PATH') )
{	
	define('HELLOMAPS_BACKEND_PATH',JPATH_ADMINISTRATOR.'/components/com_hellomaps/');
}