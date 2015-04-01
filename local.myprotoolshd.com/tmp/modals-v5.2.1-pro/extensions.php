<?php
/**
 * Extension Install File
 * Does the stuff for the specific extensions
 *
 * @package         Modals
 * @version         5.2.1
 *
 * @author          Peter van Westen <peter@nonumber.nl>
 * @link            http://www.nonumber.nl
 * @copyright       Copyright © 2015 NoNumber All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

class extensionsInstaller extends NoNumberInstallerInstaller
{
	var $name = 'Modals';
	var $alias = 'modals';

	function install(&$states, &$ext)
	{
		$ext = $this->name . ' (system plugin)';

		// SYSTEM PLUGIN
		$states[] = $this->installExtension($states, $this->alias, 'System - NoNumber ' . $this->name, 'plugin');
	}

	// Stuff to do before installation / update
	function beforeInstall()
	{
		// check if Modalizer is installed
		if (is_dir(JPATH_PLUGINS . '/system/modalizer'))
		{
			$query = $this->db->getQuery(true);
			$query->select('e.enabled')
				->from('#__extensions as e')
				->where('e.element = ' . $this->db->quote('modalizer'));
			$this->db->setQuery($query);
			if ($this->db->loadResult())
			{
				return 'You must uninstall or disable the old Modalizer plugin before you can install Modals!<br /><br />Modals has different options and functionality than the old Modalizer. So please investigate before switching to Modals.';
			}
		}
	}
}
