<?php
/**
 * @version		$Id: k2tag.php 478 2010-06-16 16:11:42Z joomlaworks $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.gr
 * @copyright	Copyright (c) 2006 - 2010 JoomlaWorks, a business unit of Nuevvo Webware Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class TableK2Tag extends JTable {

	var $id = null;
	var $name = null;
	var $published = null;

	function __construct( & $db) {

		parent::__construct('#__k2_tags', 'id', $db);
	}

	function check() {

		if (trim($this->name) == '') {
			$this->setError(JText::_('Tag cannot be empty'));
			return false;
		}
		$this->name = str_replace('-','',$this->name);
		return true;
	}

}
