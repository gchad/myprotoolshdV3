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

class nnFile
{
	public static function getXMLVersion($file = '', $alias = '')
	{
		if (!$file && !$file = nnFile::getXMLFileByAlias($alias))
		{
			return false;
		}

		if (!self::exists($file))
		{
			return false;
		}

		$xml = JApplicationHelper::parseXMLInstallFile($file);

		if (!$xml || !isset($xml['version']))
		{
			return false;
		}

		return $xml['version'];
	}

	/**
	 * Find xml file
	 */
	private function getXMLFileByAlias($alias = '')
	{
		$files = array(
			JPATH_PLUGINS . '/system/' . $alias . '/' . $alias . '.xml',
			JPATH_PLUGINS . '/editors-xtd/' . $alias . '/' . $alias . '.xml',
			JPATH_ADMINISTRATOR . '/components/com_' . $alias . '/' . $alias . '.xml',
			JPATH_SITE . '/components/com_' . $alias . '/' . $alias . '.xml',
			JPATH_ADMINISTRATOR . '/modules/mod_' . $alias . '/mod_' . $alias . '.xml',
			JPATH_SITE . '/modules/mod_' . $alias . '/mod_' . $alias . '.xml',
		);

		foreach ($files as $file)
		{
			if (self::exists($file))
			{
				return $file;
			}
		}

		return false;
	}

	/**
	 * Copies all files from install folder
	 */
	public static function copy_from_folder($folder, $force = 0)
	{
		if (!is_dir($folder))
		{
			JFactory::getApplication()->enqueueMessage('!!! ' . $folder . ' is not a dir', 'error');

			return false;
		}

		// Copy files
		$folders = JFolder::folders($folder);

		foreach ($folders as $subfolder)
		{
			$dest = JPATH_SITE . '/' . $subfolder;
			$dest = str_replace(JPATH_SITE . '/plugins', JPATH_PLUGINS, $dest);
			$dest = str_replace(JPATH_SITE . '/administrator', JPATH_ADMINISTRATOR, $dest);

			if (!self::folder_copy($folder . '/' . $subfolder, $dest, $force))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Copy a folder
	 */
	public static function folder_copy($src, $dest, $force = 0)
	{
		// Initialize variables
		jimport('joomla.client.helper');
		$ftpOptions = JClientHelper::getCredentials('ftp');

		// Eliminate trailing directory separators, if any
		$src = rtrim(str_replace('\\', '/', $src), '/');
		$dest = rtrim(str_replace('\\', '/', $dest), '/');

		if (!self::existsFolder($src))
		{
			JFactory::getApplication()->enqueueMessage('!!! ' . $src . ' does not exist', 'error');

			return false;
		}

		// Make sure the destination exists
		if (!self::existsFolder($dest) && !self::folder_create($dest))
		{
			$folder = str_replace(JPATH_ROOT, '', $dest);
			JFactory::getApplication()->enqueueMessage(JText::sprintf(JText::_('NNI_FAILED_TO_CREATE_DIRECTORY'), $folder), 'error');

			return false;
		}

		if (!($dh = @opendir($src)))
		{
			JFactory::getApplication()->enqueueMessage('!!! ' . $src . ' cannot opendir', 'error');

			return false;
		}

		$folders = array();
		$files = array();
		while (($file = readdir($dh)) !== false)
		{
			if ($file != '.' && $file != '..')
			{
				$file_src = $src . '/' . $file;
				switch (filetype($file_src))
				{
					case 'dir':
						$folders[] = $file;
						break;
					case 'file':
						$files[] = $file;
						break;
				}
			}
		}
		sort($folders);
		sort($files);

		$curr_folder = explode('/', $src);
		$curr_folder = array_pop($curr_folder);

		// Walk through the directory recursing into folders
		foreach ($folders as $folder)
		{
			$folder_src = $src . '/' . $folder;
			$folder_dest = $dest . '/' . $folder;
			if (!($curr_folder == 'language' && !self::existsFolder($folder_dest)))
			{
				if (!self::folder_copy($folder_src, $folder_dest, $force))
				{
					return false;
				}
			}
		}

		if ($ftpOptions['enabled'] == 1)
		{
			// Connect the FTP client
			jimport('joomla.client.ftp');
			$ftp = JFTP::getInstance(
				$ftpOptions['host'], $ftpOptions['port'], array(),
				$ftpOptions['user'], $ftpOptions['pass']
			);

			// Walk through the directory copying files
			foreach ($files as $file)
			{
				$file_src = $src . '/' . $file;
				$file_dest = $dest . '/' . $file;
				// Translate path for the FTP account
				$file_dest = JPath::clean(str_replace(str_replace('\\', '/', JPATH_ROOT), $ftpOptions['root'], $file_dest), '/');
				if ($force || !self::exists($file_dest))
				{
					if (!@$ftp->store($file_src, $file_dest))
					{
						$file_path = str_replace($ftpOptions['root'], '', $file_dest);
						JFactory::getApplication()->enqueueMessage(JText::sprintf(JText::_('NNI_ERROR_SAVING_FILE'), $file_path), 'error');

						return false;
					}
				}
			}
		}
		else
		{
			foreach ($files as $file)
			{
				$file_src = $src . '/' . $file;
				$file_dest = $dest . '/' . $file;
				if ($force || !self::exists($file_dest))
				{
					if (!@copy($file_src, $file_dest))
					{
						$file_path = str_replace(JPATH_ROOT, '', $file_dest);
						JFactory::getApplication()->enqueueMessage(JText::sprintf(JText::_('NNI_ERROR_SAVING_FILE'), $file_path), 'error');

						return false;
					}
				}
			}
		}

		return true;
	}

	/**
	 * Create a folder
	 */
	public static function folder_create($path = '', $mode = 0755)
	{
		// Initialize variables
		jimport('joomla.client.helper');
		$ftpOptions = JClientHelper::getCredentials('ftp');

		// Check to make sure the path valid and clean
		$path = JPath::clean($path);

		// Check if dir already exists
		if (self::existsFolder($path))
		{
			return true;
		}

		// Check for safe mode
		if ($ftpOptions['enabled'] == 1)
		{
			// Connect the FTP client
			jimport('joomla.client.ftp');
			$ftp = JFTP::getInstance(
				$ftpOptions['host'], $ftpOptions['port'], array(),
				$ftpOptions['user'], $ftpOptions['pass']
			);

			// Translate path to FTP path
			$path = JPath::clean(str_replace(JPATH_ROOT, $ftpOptions['root'], $path), '/');
			$ret = $ftp->mkdir($path);
			$ftp->chmod($path, $mode);
		}
		else
		{
			// We need to get and explode the open_basedir paths
			$obd = ini_get('open_basedir');

			// If open_basedir is set we need to get the open_basedir that the path is in
			if ($obd != null)
			{
				if (JPATH_ISWIN)
				{
					$obdSeparator = ";";
				}
				else
				{
					$obdSeparator = ":";
				}
				// Create the array of open_basedir paths
				$obdArray = explode($obdSeparator, $obd);
				$inBaseDir = false;
				// Iterate through open_basedir paths looking for a match
				foreach ($obdArray as $test)
				{
					$test = JPath::clean($test);
					if (strpos($path, $test) === 0)
					{
						$inBaseDir = true;
						break;
					}
				}
				if ($inBaseDir == false)
				{
					// Return false for JFolder::create because the path to be created is not in open_basedir
					JFactory::getApplication()->enqueueMessage(JText::_('NNI_PATH_NOT_IN_OPEN_BASEDIR_PATHS'), 'error');

					return false;
				}
			}

			// First set umask
			$origmask = @umask(0);

			// Create the path
			if (!$ret = @mkdir($path, $mode))
			{
				@umask($origmask);
				JFactory::getApplication()->enqueueMessage('!!!! Cannot create path: ' . $path, 'error');

				return false;
			}

			// Reset umask
			@umask($origmask);
		}

		return $ret;
	}

	/**
	 * delete a file
	 */
	public static function delete($src)
	{
		if (!self::exists($src))
		{
			return true;
		}

		return JFile::delete($src);
	}

	/**
	 * delete a folder
	 */
	public static function deleteFolder($src)
	{
		if (!self::existsFolder($src))
		{
			return true;
		}

		return JFolder::delete($src);
	}

	/**
	 * copy a file
	 */
	public static function copy($src, $dest)
	{
		if (!self::exists($src))
		{
			return true;
		}

		return JFile::copy($src, $dest);
	}

	/**
	 *  check if a file exists
	 */
	public static function exists($src)
	{
		return (JFile::exists($src) && is_readable($src));
	}

	/**
	 * check if a folder exists
	 */
	public static function existsFolder($src)
	{
		return (JFolder::exists($src) && is_readable($src));
	}
}
