<?php
/**
 * @package Sj Responsive Listing for K2
 * @version 2.5.0
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2013 YouTech Company. All Rights Reserved.
 * @author YouTech Company http://www.smartaddons.com
 */


defined('_JEXEC') or die;

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

if(!class_exists('plgSystemK2_ResListing_Ajax')){
		echo '<b>'.JText::_('WARNING_NOT_INSTALL_PLUGIN').'</b>';
		return;
		
}

require_once dirname(__FILE__).DS.'core'.DS.'helper.php';

$layout = $params->get('layout', 'default');
$cacheid = md5(serialize(array ($layout, $module->id)));

$cacheparams = new stdClass;
$cacheparams->cachemode = 'id';
$cacheparams->class = 'K2ResponsiveListing';
$cacheparams->method = 'getList';
$cacheparams->methodparams = array($params, $module);
$cacheparams->modeparams = $cacheid;
$list = JModuleHelper::moduleCache ($module, $params, $cacheparams);

if (!empty($list) && isset($list['items']) && isset($list['categories'])){
	$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
	if($is_ajax){
		$k2reslistingajax_moduleid	= JRequest::getVar('k2reslistingajax_moduleid', null);
		if($k2reslistingajax_moduleid == $module->id){
			$result = new stdClass();
			ob_start();
			require  JModuleHelper::getLayoutPath($module->module, $layout.'_items');
			$buffer = ob_get_contents();
			$result->items_markup = preg_replace(
					array(
							'/ {2,}/',
							'/<!--.*?-->|\t|(?:\r?\n[ \t]*)+/s'
					),
					array(
							' ',
							''
					),
					$buffer
			);
			ob_end_clean();
			echo json_encode($result);
		}
	}else{
		require JModuleHelper::getLayoutPath($module->module, $layout);
		require JModuleHelper::getLayoutPath($module->module, $layout.'_js');
	}
} else {
	echo JText::_('WARNING_MASSAGE');
}