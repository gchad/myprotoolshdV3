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

require_once INSTALLER_ROOT . '/file.php';

class NoNumberInstallerHelper
{
	var $has_installed = 0;
	var $has_updated = 0;

	public function install()
	{
		require_once INSTALLER_ROOT . '/installer.php';
		$installer = new NoNumberInstallerInstaller;

		$installer->installLanguages(INSTALLER_ROOT . '/language');

		// Load language for messaging
		$this->loadInstallerLanguage();

		$this->emptyMessageQueue();

		// Check if extension is compatible with current Joomla version
		if (!$this->hasVersionFolder())
		{
			return;
		}

		require_once INSTALLER_ROOT . '/extensions.php';

		$extension = new extensionsInstaller;

		// Check if Joomla version passes minimum requirement
		if (!$this->passMinimumJoomlaVersion($extension))
		{
			return;
		}

		// Check if PHP version passes minimum requirement
		if (!$this->passMinimumPHPVersion($extension))
		{
			return;
		}

		$this->db = JFactory::getDBO();

		$this->disableIncompatibleExtensions();

		$this->installFramework($installer);

		$this->installExtensions($extension);

		$this->redirect();
	}

	// Empty the Joomla message queue
	// Only works on J2.5 still
	private function emptyMessageQueue()
	{
		if (JV == 'j3')
		{
			return;
		}

		JFactory::getApplication()->set('_messageQueue', '');
	}

	// Check if extension folder exists for Joomla version
	private function hasVersionFolder()
	{
		if (!nnFile::existsFolder(INSTALLER_ROOT . '/extensions/' . JV))
		{
			$this->redirect(JText::sprintf('NNI_NOT_COMPATIBLE', implode('.', array_slice(explode('.', JVERSION), 0, 2))));

			return false;
		}

		return true;
	}

	// Check if Joomla version passes minimum requirement
	private function passMinimumJoomlaVersion($extension)
	{
		$min = JV == 'j2' ? $extension->min_j2 : $extension->min_j3;

		if (version_compare(JVERSION, $min, '<'))
		{
			$this->redirect(JText::sprintf('NNI_NOT_COMPATIBLE_UPDATE', JVERSION, $min));

			return false;
		}

		return true;
	}

	// Check if PHP version passes minimum requirement
	private function passMinimumPHPVersion($extension)
	{
		$min = $extension->min_php;

		if (version_compare(PHP_VERSION, $min, 'l'))
		{
			$this->redirect(JText::sprintf('NNI_NOT_COMPATIBLE_PHP', PHP_VERSION, $min));

			return false;
		}

		return true;
	}

	// Disable incompatible extensions like QLUE 404
	private function disableIncompatibleExtensions()
	{
		if (JV == 'j3')
		{
			return;
		}

		// check if QLUE 404 is installed and active (and break if it is)
		$query = $this->db->getQuery(true)
			->select('extension_id')
			->from('#__extensions')
			->where($this->db->quoteName('element') . ' = ' . $this->db->quote('qlue404'))
			->where($this->db->quoteName('enabled') . ' = 1');
		$this->db->setQuery($query);

		define('HASQLUE404', $this->db->loadResult());

		if (HASQLUE404)
		{
			$query->clear()
				->update('#__extensions')
				->set($this->db->quoteName('enabled') . ' = 0')
				->where($this->db->quoteName('element') . ' = ' . $this->db->quote('qlue404'));
			$this->db->setQuery($query);
			$this->db->execute();
		}
	}

	// Enable incompatible extensions again like QLUE 404
	private function enableIncompatibleExtensions()
	{
		if (JV == 'j3' || !HASQLUE404)
		{
			return;
		}

		$query = $this->db->getQuery(true)
			->update('#__extensions')
			->set($this->db->quoteName('enabled') . ' = 1')
			->where($this->db->quoteName('element') . ' = ' . $this->db->quote('qlue404'));
		$this->db->setQuery($query);
		$this->db->execute();
	}

	/*
	 * Set the global states
	 */
	private function setStates($states)
	{
		if (empty($states))
		{
			return;
		}

		if (!is_array($states))
		{
			$states = array($states);
		}

		foreach ($states as $state)
		{
			// Set the global states
			switch ($state)
			{
				case 'installed':
					$this->has_updated = 1;
					break;

				case 'updated':
					$this->has_installed = 1;
					break;

				default:
					$this->redirect(JText::_('NNI_SOMETHING_HAS_GONE_WRONG_DURING_INSTALLATION_OF_THE_DATABASE_RECORDS'));
					break;
			}
		}
	}

	private function loadInstallerLanguage()
	{
		if (JV != 'j3')
		{
			$this->loadInstallerLanguage25();

			return;
		}

		JFactory::getLanguage()->load('com_nonumberinstaller', JPATH_ADMINISTRATOR);
	}

	private function loadInstallerLanguage25()
	{
		$lang = JFactory::getLanguage();
		if ($lang->getTag() != 'en-GB')
		{
			// Loads English language file as fallback (for undefined stuff in other language file)
			$lang->load('com_nonumberinstaller', JPATH_ADMINISTRATOR, 'en-GB');
		}
		$lang->load('com_nonumberinstaller', JPATH_ADMINISTRATOR, null, 1);
	}

	private function installExtensions(&$extension)
	{

		// execute custom beforeInstall function
		$extension->beforeInstall();

		if (!$extension->installFiles(INSTALLER_ROOT . '/extensions'))
		{
			$this->redirect(JText::_('NNI_COULD_NOT_COPY_ALL_FILES'));
		}

		$states = array();
		$ext = 'NNI_THE_EXTENSION'; // default value. Will be overruled in extensions.php

		$extension->install($states, $ext);

		$this->setStates($states);

		// execute custom afterInstall function
		$extension->afterInstall();

		$txt_installed = ($this->has_installed) ? JText::_('NNI_INSTALLED') : '';
		$txt_installed .= ($this->has_installed && $this->has_updated) ? ' / ' : '';
		$txt_installed .= ($this->has_updated) ? JText::_('NNI_UPDATED') : '';

		$this->emptyMessageQueue();

		JFactory::getApplication()->enqueueMessage(sprintf(JText::_('NNI_THE_EXTENSION_HAS_BEEN_INSTALLED_SUCCESSFULLY'), JText::_($ext), $txt_installed), 'message');
		JFactory::getApplication()->enqueueMessage(JText::_('NNI_PLEASE_CLEAR_YOUR_BROWSERS_CACHE'), 'notice');
	}

	private function installFramework($installer)
	{
		$framework_folder = INSTALLER_ROOT . '/framework/framework';
		$xml_name = 'plugins/system/nnframework/nnframework.xml';
		$xml_file = $framework_folder . '/' . JV . '/' . $xml_name;

		if (!nnFile::exists($xml_file))
		{
			return;
		}

		$new_version = nnFile::getXMLVersion($xml_file);
		if (!$new_version)
		{
			return;
		}

		$installed_version = nnFile::getXMLVersion('', 'nnframework');
		if ($installed_version && version_compare($installed_version, $new_version, '>'))
		{
			return;
		}

		$file = JPATH_PLUGINS . '/system/nnframework/nnframework.php';
		if (JV == 'j3' && nnFile::exists($file) && $contents = file_get_contents($file))
		{
			if (strpos($contents, 'checkUpdates') !== false)
			{
				return;
			}
		}

		if (!$installer->installFiles($framework_folder))
		{
			JFactory::getApplication()->enqueueMessage(JText::_('NNI_COULD_NOT_INSTALL_THE_NONUMBER_FRAMEWORK_EXTENSION'), 'error');
			JFactory::getApplication()->enqueueMessage(JText::_('NNI_COULD_NOT_COPY_ALL_FILES'), 'error');

			return;
		}

		if (nnFile::existsFolder(JPATH_PLUGINS . '/system/nonumberelements'))
		{
			$installer->uninstallLanguages('nonumberelements');
			$installer->installFiles(INSTALLER_ROOT . '/framework/elements');
			$installer->installExtension(array(), 'nonumberelements', 'System - NoNumber Elements', 'plugin', array('published' => '0'), 1);
		}

		$installer->installExtension(array(), 'nnframework', 'System - NoNumber Framework', 'plugin', array(), 1);
	}

	private function redirect($error = '')
	{
		if ($error)
		{
			$this->emptyMessageQueue();

			JFactory::getApplication()->enqueueMessage(JText::_($error), 'error');
		}

		$this->cleanupInstall();

		if (!$error)
		{
			$this->enableIncompatibleExtensions();

			$this->uninstallInstaller();
		}

		// Redirect with message
		$view = JFactory::getApplication()->input->get('option') == 'com_installer' ? JFactory::getApplication()->input->get('view') : '';
		JFactory::getApplication()->redirect('index.php?option=com_installer' . ($view ? '&view=' . $view : ''));
	}

	/**
	 * Cleanup install files/folders
	 */
	private function cleanupInstall()
	{
		$installer = JInstaller::getInstance();
		$source = str_replace('\\', '/', $installer->getPath('source'));
		$tmp = dirname(str_replace('\\', '/', JFactory::getConfig()->get('tmp_path') . '/x'));

		if (strpos($source, $tmp) === false || $source == $tmp)
		{
			return;
		}

		$package_folder = dirname($source);

		if ($package_folder == $tmp)
		{
			$package_folder = $source;
		}

		$package_file = '';
		switch (JFactory::getApplication()->input->getString('installtype', ''))
		{
			case 'url':
				$package_file = JFactory::getApplication()->input->getString('install_url', '');
				$package_file = str_replace(dirname($package_file), '', $package_file);
				break;
			case 'upload':
			default:
				if (isset($_FILES) && isset($_FILES['install_package']) && isset($_FILES['install_package']['name']))
				{
					$package_file = $_FILES['install_package']['name'];
				}
				break;
		}

		if (!$package_file && $package_folder != $source)
		{
			$package_file = str_replace($package_folder . '/', '', $source) . '.zip';
		}

		$package_file = $tmp . '/' . $package_file;

		JInstallerHelper::cleanupInstall($package_file, $package_folder);
	}

	private function uninstallInstaller()
	{
		$this->db = JFactory::getDBO();

		$query = $this->db->getQuery(true)
			->delete('#__menu')
			->where('title = ' . $this->db->quote('com_nonumberinstaller'));
		$this->db->setQuery($query);
		$this->db->execute();

		// Reset the auto-increment
		if (in_array($this->db->name, array('mysql', 'mysqli')))
		{
			$this->db->setQuery('ALTER TABLE `#__menu` AUTO_INCREMENT = 1');
			$this->db->execute();
		}

		// Delete language files
		$lang_folder = JPATH_ADMINISTRATOR . '/language';
		$languages = JFolder::folders($lang_folder);
		foreach ($languages as $lang)
		{
			nnFile::delete($lang_folder . '/' . $lang . '/' . $lang . '.com_nonumberinstaller.ini');
		}

		// Delete old language files
		$files = JFolder::files(JPATH_SITE . '/language', 'com_nonumberinstaller.ini');
		foreach ($files as $file)
		{
			nnFile::delete(JPATH_SITE . '/language/' . $file);
		}

		// Delete component folder
		nnFile::deleteFolder(JPATH_ADMINISTRATOR . '/components/com_nonumberinstaller');
	}
}
