<?php
/**
 * @package Sj Responsive Content for K2
 * @version 2.5.0
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2012 YouTech Company. All Rights Reserved.
 * @author YouTech Company http://www.smartaddons.com
 */


defined ( '_JEXEC' ) or die ();
// import css
JHtml::stylesheet('modules/'.$module->module.'/assets/css/jquery.fancybox.css');
JHtml::stylesheet('modules/'.$module->module.'/assets/css/rescontent.css');

// import jQuery
if (!defined('SMART_JQUERY') && ( int ) $params->get ( 'include_jquery', '1' )) {
	JHtml::script('modules/'.$module->module.'/assets/js/jquery-1.8.2.min.js');
	define('SMART_JQUERY', 1);
}
if (!defined('SMART_NOCONFLICT')){
	JHtml::script('modules/'.$module->module.'/assets/js/jsmart.noconflict.js');
	define('SMART_NOCONFLICT', 1);
}

JHtml::script('modules/'.$module->module.'/assets/js/jquery.isotope.min.js');
JHtml::script('modules/'.$module->module.'/assets/js/jquery.infinitescroll.min.js');
JHtml::script('modules/'.$module->module.'/assets/js/jquery.fancybox.js');
$app = JFactory::getApplication();
$option = $app->input->get('option');
$view = $app->input->get('view');
$mode = $params->get('mode', 'normal');


include_once dirname( __FILE__ ) . '/core/helper.php';

$list = modSjK2ResContentHelper::getList($params);

if(($option === 'com_k2') && ($view !='itemlist' ) && empty($list)  ){
	//nood;
}else{
	$layout  = $params->get('layout', 'default');
	if ( !empty($list) ) {
		$is_ajax = ! empty ( $_SERVER ['HTTP_X_REQUESTED_WITH'] ) && strtolower ( $_SERVER ['HTTP_X_REQUESTED_WITH'] ) == 'xmlhttprequest';
		if ($is_ajax) {
			$sj_module_id = JRequest::getVar ( 'module_id', null );
	
			if ($sj_module_id == $module->id) {
					
				ob_start ();
				require JModuleHelper::getLayoutPath ( $module->module, $layout . '_items' );
					
				$buffer = ob_get_contents ();
				$result = preg_replace ( array ('/ {2,}/', '/<!--.*?-->|\t|(?:\r?\n[ \t]*)+/s' ), array (' ', '' ), $buffer );
				ob_end_clean ();
				echo ($result);
	
			}
		} else {
			require JModuleHelper::getLayoutPath ( $module->module, $layout );
		}
	} else {
		echo JText::_('Has no content to show!');
	}
}