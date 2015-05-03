<?php
/**
 * @version		$Id: EmailAsUsername.php version 3.2 $
 * @package		EmailAsUsername
  * @copyright	Copyright (C) 2005 - 2011 www.lunarhotel.co.uk, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

/**
 * EmailAsUsername Authentication plugin
 */
class plgAuthenticationEmailAsUsername extends JPlugin
{
		 
	function onUserAuthenticate(&$credentials, $options, &$response)
	{
		// we specify & which means it will update the variable in the calling function
		// which is needed so we can update the username array value.
		$response->type = 'Joomla';
		if (empty($credentials['password']))
		{
			$response->status = JAuthentication::STATUS_FAILURE;
			$response->error_message = JText::_('JGLOBAL_AUTH_EMPTY_PASS_NOT_ALLOWED');
			return false;
		}

		$db = JFactory::getDbo();
		$q	= $db->getQuery(true);
		$q->select('id, password, username');
		$q->from('#__users');
		$q->where('email=' . $db->quote($credentials['username']));
		$db->setQuery($q);
	
		if ($result = $db->loadObject())
		{
			//add this in otherwise authentication succeeds, but authorisation fails
			$credentials['username']=$result->username;
			$match = JUserHelper::verifyPassword($credentials['password'], $result->password, $result->id);

			if ($match === true)
			{
				$user = JUser::getInstance($result->id);
				$response->email = $user->email;
				$response->fullname = $user->name;

				if (JFactory::getApplication()->isAdmin())
				{
					$response->language = $user->getParam('admin_language');
				}
				else
				{
					$response->language = $user->getParam('language');
				}
				$response->status = JAuthentication::STATUS_SUCCESS;
				$response->error_message = '';
			}
			else
			{
				$response->status = JAuthentication::STATUS_FAILURE;
				$response->error_message = JText::_('JGLOBAL_AUTH_INVALID_PASS');
			}
		}
		else
		{
			$response->status = JAuthentication::STATUS_FAILURE;
			$response->error_message = JText::_('JGLOBAL_AUTH_NO_USER');
			
		}
	}
}
