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

class NoNumberInstallerInstaller
{
	var $db = null;

	var $min_j2 = '2.5.10';
	var $min_j3 = '3.3.0';
	var $min_php = '5.3.13';

	public function __construct()
	{
		$this->db = JFactory::getDBO();
	}

	public function install(&$states, &$ext)
	{
	}

	/*
	 * @return string false, installed, updated
	 */
	public function installExtension($states, $alias, $name, $type = 'component', $extra = array(), $framework = 0)
	{
		foreach ($states as $state)
		{
			if (empty($state))
			{
				return false;
			}
		}

		// Create database object
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		// set main vars
		$element = $alias;
		$folder = ($type == 'plugin') ? (isset($extra['folder']) ? $extra['folder'] : 'system') : '';
		unset($extra['folder']);

		// set main database where clauses
		$where = array();
		$where[] = $db->quoteName('type') . ' = ' . $db->quote($type);
		switch ($type)
		{
			case 'component':
				$element = 'com_' . $element;
				break;
			case 'plugin':
				$where[] = $db->quoteName('folder') . ' = ' . $db->quote($folder);
				break;
			case 'module':
				$element = 'mod_' . $element;
				break;
		}
		$where[] = $db->quoteName('element') . ' = ' . $db->quote($element);
		$where = implode(' AND ', $where);

		// get ordering
		$ordering = '';
		switch ($type)
		{
			case 'plugin':
				$query->clear()
					->select('ordering')
					->from('#__extensions')
					->where($where);
				$db->setQuery($query);
				$ordering = $db->loadResult();
				break;
			case 'module':
				$query->clear()
					->select('m.ordering')
					->from('#__modules AS m')
					->where('m.module = ' . $db->quote($element) . ' OR m.module = ' . $db->quote('mod_' . $element));
				$db->setQuery($query);
				$ordering = $db->loadResult();
				break;
		}

		// get installed state
		$installed = 0;
		if ($framework)
		{
			// remove extension(s) from database
			$query->clear()
				->delete('#__extensions')
				->where($where);
			$db->setQuery($query);
			$db->execute();
		}
		else
		{
			// get installed state
			$query->clear()
				->select('extension_id')
				->from('#__extensions')
				->where($where);
			$db->setQuery($query);
			$installed = (int) $db->loadResult();
		}

		$id = $installed;

		// if not installed yet, create database entries
		if (!$installed)
		{
			if ($type == 'module')
			{
				// create module database object
				$row = JTable::getInstance('module');
				$row->title = $name;
				$row->module = $element;
				$row->client_id = 1;
				$row->published = 1;
				$row->position = 'status';
				$row->showtitle = 1;
				$row->language = '*';
				foreach ($extra as $key => $val)
				{
					if (property_exists($row, $key))
					{
						$row->$key = $val;
					}
				}
				if ($ordering)
				{
					$row->ordering = $ordering;
				}
				else
				{
					$row->ordering = $row->getNextOrder("position='" . $row->position . "' AND client_id = " . $row->client_id);
				}
				// save module to database
				if (!$row->store())
				{
					JFactory::getApplication()
						->enqueueMessage($row->getError(), 'error');

					return false;
				}

				// clean up possible garbage first
				$query->clear()
					->delete('#__modules_menu')
					->where('moduleid = ' . (int) $row->id);
				$db->setQuery($query);
				$db->execute();

				// create a menu entry for the module
				$query->clear()
					->insert('#__modules_menu')
					->values((int) $row->id . ', 0');
				$db->setQuery($query);
				$db->execute();
			}

			// create extension database object
			$row = JTable::getInstance('extension');
			$row->name = strtolower($alias);
			$row->element = $alias;
			$row->type = $type;
			$row->enabled = 1;
			$row->client_id = 0;
			$row->access = 1;
			switch ($type)
			{
				case 'component':
					$row->name = strtolower('com_' . $row->name);
					$row->element = 'com_' . $row->element;
					$row->access = 0;
					$row->client_id = 1;
					break;
				case 'plugin':
					$row->name = strtolower('plg_' . $folder . '_' . $row->name);
					$row->folder = $folder;
					if ($ordering)
					{
						$row->ordering = $ordering;
					}
					break;
				case 'module':
					$row->name = strtolower('mod_' . $row->name);
					$row->element = 'mod_' . $row->element;
					$row->client_id = 1;
					break;
			}
			foreach ($extra as $key => $val)
			{
				if (property_exists($row, $key))
				{
					$row->$key = $val;
				}
			}

			// save extension to database
			if (!$row->store())
			{
				JFactory::getApplication()
					->enqueueMessage($row->getError(), 'error');

				return false;
			}
			$id = (int) $row->extension_id;
		}

		// if no extension id is found, return false (=not installed)
		if (!$id)
		{
			return false;
		}

		// remove manifest cache
		$query->clear()
			->update('#__extensions AS e')
			->set('e.manifest_cache = ' . $db->quote(''))
			->where('e.extension_id = ' . (int) $id);
		$db->setQuery($query);
		$db->execute();

		// add menus for components
		if ($type == 'component')
		{
			// delete old menu entries
			$query->clear()
				->delete('#__menu')
				->where('link = ' . $db->quote('index.php?option=com_' . $alias))
				->where('client_id = 1');
			$db->setQuery($query);
			$db->execute();

			// find menu details in xml file
			$xml = 0;
			$file = INSTALLER_ROOT . '/extensions/' . JV . '/administrator/components/com_' . $alias . '/' . $alias . '.xml';

			if (nnFile::exists($file))
			{
				$xml = JFactory::getXML($file);
			}

			if ($xml && isset($xml->administration) && isset($xml->administration->menu))
			{
				$menuElement = $xml->administration->menu;

				if ($menuElement)
				{
					// create menu database object
					$data = array();
					$data['menutype'] = 'menu';
					$data['client_id'] = 1;
					$data['title'] = (string) $menuElement;
					$data['alias'] = $alias;
					$data['link'] = 'index.php?option=com_' . $alias;
					$data['type'] = 'component';
					$data['published'] = 1;
					$data['parent_id'] = 1;
					$data['component_id'] = $id;
					$attribs = $menuElement->attributes();
					$data['img'] = ((string) $attribs->img) ? (string) $attribs->img : 'class:component';
					$data['home'] = 0;
					$data['language'] = '*';
					$table = JTable::getInstance('menu');

					// save menu to database
					try
					{
						$table->setLocation(1, 'last-child');
					}
					catch (InvalidArgumentException $e)
					{
						return false;
					}
					if (!$table->bind($data) || !$table->check() || !$table->store())
					{
						JFactory::getApplication()
							->enqueueMessage($table->getError(), 'error');

						return false;
					}
				}
			}
		}

		if (!$framework)
		{
			$this->addUpdateSite($id, $name, $alias, 'extension');
		}

		JInstaller::getInstance()
			->refreshManifestCache((int) $id);

		return $installed ? 'installed' : 'updated';
	}

	private function addUpdateSite($id, $name, $alias, $type, $enabled = true)
	{
		$name = preg_replace('#^.*? - #', '', $name);

		$db = JFactory::getDBO();

		$query = $db->getQuery(true)
			->select('update_site_id')
			->from('#__update_sites as u')
			->where('u.location LIKE ' . $db->quote('%download.nonumber.nl/updates.php?e=' . $alias . '%'));
		$db->setQuery($query);
		$db->execute();
		$update_site_ids = $db->loadColumn();

		$update_site_id = null;
		if (!empty($update_site_ids))
		{
			$update_site_id = $update_site_ids['0'];

			$query->clear()
				->delete('#__update_sites')
				->where($db->quoteName('update_site_id') . ' IN (' . implode(',', $update_site_ids) . ')')
				->where($db->quoteName('update_site_id') . ' != ' . (int) $update_site_id);
			$db->setQuery($query);
			$db->execute();

			$query->clear()
				->delete('#__updates')
				->where($db->quoteName('update_site_id') . ' IN (' . implode(',', $update_site_ids) . ')');
			$db->setQuery($query);
			$db->execute();
		}

		$query->clear()
			->delete('#__update_sites_extensions')
			->where('extension_id = ' . $id);
		$db->setQuery($query);
		$db->execute();

		$location = 'http://download.nonumber.nl/updates.php?e=' . $alias;
		$location .= '&pro=1';
		$location .= '&type=.zip';

		if ($update_site_id)
		{
			$query = $query->clear()
				->update('#__update_sites as u')
				->set('u.name = ' . $db->q($name))
				->set('u.type = ' . $db->q($type))
				->set('u.location = ' . $db->q($location))
				->where('u.update_site_id = ' . (int) $update_site_id);
			$db->setQuery($query);
			$db->execute();
		}
		else
		{
			$query->clear()
				->insert('#__update_sites')
				->columns(array($db->quoteName('name'), $db->quoteName('type'), $db->quoteName('location'), $db->quoteName('enabled')))
				->values($db->quote($name) . ', ' . $db->quote($type) . ', ' . $db->quote($location) . ', ' . (int) $enabled);
			$db->setQuery($query);
			$db->execute();
		}

		if (JV == 'j3')
		{
			$this->updateDownloadKey();
		}
	}

	// Save the download key from the NoNumber Extension Manager config to the update sites
	private function updateDownloadKey()
	{
		$db = JFactory::getDBO();

		$query = $db->getQuery(true)
			->select('e.params')
			->from('#__extensions as e')
			->where('e.element = ' . $db->quote('com_nonumbermanager'));
		$db->setQuery($query);
		$params = $db->loadResult();

		if (!$params)
		{
			return;
		}

		$params = json_decode($params);

		if (!isset($params->key))
		{
			return;
		}

		$query = $query->clear()
			->update('#__update_sites as u')
			->set('u.extra_query = ' . $db->q(''))
			->where('u.location LIKE ' . $db->q('http://download.nonumber.nl%'));
		$db->setQuery($query);
		$db->execute();

		$query = $query->clear()
			->update('#__update_sites as u')
			->set('u.extra_query = ' . $db->q('k=' . $params->key))
			->where('u.location LIKE ' . $db->q('http://download.nonumber.nl%'))
			->where('u.location LIKE ' . $db->q('%&pro=1%'));
		$db->setQuery($query);
		$db->execute();
	}

	// Stuff to do before installation / update
	public function beforeInstall()
	{
	}

	// Stuff to do after installation / update
	public function afterInstall()
	{
	}

	public function fixFileVersionsByFile($file)
	{
		if (!nnFile::exists($file))
		{
			return;
		}

		$contents = file_get_contents($file);

		if (
			strpos($contents, 'FREEFREE') === false
			&& strpos($contents, 'FREEPRO') === false
			&& strpos($contents, 'PROFREE') === false
			&& strpos($contents, 'PROPRO') === false
		)
		{
			return;
		}

		$contents = str_replace(
			array('FREEFREE', 'FREEPRO', 'PROFREE', 'PROPRO'),
			array('FREE', 'PRO', 'FREE', 'PRO'),
			$contents
		);

		JFile::write($file, $contents);
	}

	/**
	 * Copies all files from install folder
	 */
	public function installFiles($folder)
	{
		if (
			nnFile::existsFolder($folder . '/all')
			&& !nnFile::copy_from_folder($folder . '/all', 1)
		)
		{
			return false;
		}

		if (
			nnFile::existsFolder($folder . '/' . JV)
			&& !nnFile::copy_from_folder($folder . '/' . JV, 1)
		)
		{
			return false;
		}

		if (
			nnFile::existsFolder($folder . '/' . JV . '_optional')
			&& !nnFile::copy_from_folder($folder . '/' . JV . '_optional', 0)
		)
		{
			return false;
		}

		if (nnFile::existsFolder($folder . '/language'))
		{
			$this->installLanguages($folder . '/language');
		}

		return true;
	}

	/**
	 * Copies language files to the language folders
	 */
	public function installLanguages($folder)
	{
		if (!$this->installLanguagesByPath($folder . '/admin', JPATH_ADMINISTRATOR . '/language'))
		{
			return false;
		}

		if (!$this->installLanguagesByPath($folder . '/site', JPATH_SITE . '/language'))
		{
			return false;
		}

		return true;
	}

	/**
	 * Removes language files from the language admin folders by filter
	 */
	public function uninstallLanguages($filter)
	{
		$dir = JPATH_ADMINISTRATOR . '/language';
		$languages = JFolder::folders($dir);

		foreach ($languages as $lang)
		{
			$folder = $dir . '/' . $lang;
			$files = JFolder::files($folder, $filter);

			foreach ($files as $file)
			{
				nnFile::delete($folder . '/' . $file);
			}
		}
	}

	/**
	 * Copies language files to the specified path
	 */
	private function installLanguagesByPath($folder, $path)
	{
		if (!nnFile::existsFolder($folder))
		{
			return true;
		}

		$languages = array_unique(array('en-GB', JFactory::getLanguage()->getTag()));

		if (nnFile::existsFolder($path . '/en-GB'))
		{
			nnFile::folder_create($path . '/en-GB');
		}

		foreach ($languages as $lang)
		{
			if (!nnFile::existsFolder($folder . '/' . $lang))
			{
				continue;
			}

			$files = JFolder::files($folder . '/' . $lang);

			foreach ($files as $file)
			{
				$src = $folder . '/' . $lang . '/' . $file;
				$dest = $path . '/' . $lang . '/' . $file;

				if (!(strpos($file, '.menu.ini') === false))
				{
					nnFile::delete($dest);

					continue;
				}

				if (!nnFile::copy($src, $dest))
				{
					return false;
				}
			}
		}

		return true;
	}
}
