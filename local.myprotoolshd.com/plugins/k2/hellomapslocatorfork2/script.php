<?php
/**
 * @version     1.0.7g
 * @package     Plugin HelloMaps Locator for K2
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      JoomlaForce Team <support@joomlaforce.com> - http://www.joomlaforce.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

class plgK2Hellomapslocatorfork2InstallerScript{

	public function update($type){
		
		$db = JFactory::getDBO();
		$db->setQuery("CREATE TABLE IF NOT EXISTS `#__k2_k2locator` (
						`itemid` int(11) NOT NULL AUTO_INCREMENT,
						`lat` float(10,6) NOT NULL,
						`lng` float(10,6) NOT NULL,
						`privacy` int(11) NOT NULL,
						PRIMARY KEY (`itemid`),
						KEY `lat` (`lat`),
						KEY `lng` (`lng`),
						KEY `privacy` (`privacy`)
						) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;");
		$db->execute();
	   
	}
}      