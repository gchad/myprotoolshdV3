<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_config
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * This layout displays message or error, insde a bootstrap alert box
 */

if (!empty($displayData->message))
{
	if (is_array($displayData->message))
	{
		foreach ($displayData->message as $message)
		{
			echo ShlHtmlBs_Helper::alert($message, $type = 'success', $dismiss = true);
		}
	}
	else
	{
		echo ShlHtmlBs_Helper::alert($displayData->message, $type = 'success', $dismiss = true);
	}
}
if (method_exists($displayData, 'getError'))
{
	$error = $displayData->getError();
	if (!empty($error))
	{
		echo ShlHtmlBs_Helper::alert($error, $type = 'error', $dismiss = true);
	}
}
