<?php
/**
 * Installer File
 * Performs an install / update of NoNumber extensions
 *
 * @package         NoNumber Installer
 * @version         15.1.6
 *
 * @author          Peter van Westen <peter@nonumber.nl>
 * @link            http://www.nonumber.nl
 * @copyright       Copyright © 2015 NoNumber All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

define('JV', (version_compare(JVERSION, '3', 'l')) ? 'j2' : 'j3');
define('INSTALLER_ROOT', dirname(__FILE__));

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');


class com_NoNumberInstallerInstallerScript
{
	public function preflight($adapter)
	{
		require_once INSTALLER_ROOT . '/helper.php';
		$helper = new NoNumberInstallerHelper;

		$helper->install();
	}
}
