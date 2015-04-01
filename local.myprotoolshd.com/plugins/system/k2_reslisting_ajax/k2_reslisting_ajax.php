<?php
/**
 * @package K2 Responsive Listing Ajax
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2009-2013 YouTech Company. All Rights Reserved.
 * @author YouTech Company http://www.smartaddons.com
 *
 */

defined('_JEXEC') or die;
jimport('joomla.plugin.plugin');

class plgSystemK2_ResListing_Ajax extends JPlugin {
	
	function onBeforeRender(){
		$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

		if ($is_ajax){
			$db = JFactory::getDbo();
			$db->setQuery( 'SELECT * FROM #__modules WHERE id='.JRequest::getInt('k2reslistingajax_moduleid') );
			$result = $db->loadObject();
			if (isset($result->module)){
				echo JModuleHelper::renderModule($result);
				exit(0);
			}
		}
	}
}
