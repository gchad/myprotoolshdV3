<?php
/**
 * ------------------------------------------------------------------------
 * JA K2 Filter Package for J25 & J3.3
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

function JAK2FilterBuildRoute(&$query){
	$segments = array();
	
	if (isset($query['view'])) {
		if($query['view'] != 'itemlist') {
			$segments[] = $query['view'];
		}
		unset($query['view']);
	}
	return $segments;
}

function JAK2FilterParseRoute($segments) {
	$vars = array();
	if(count($segments)) {
		switch($segments[0]) {
			case 'itemlist':
				$vars['view'] = 'itemlist';
				break;
			case 'suggestions':
				$vars['view'] = 'suggestions';
				break;
			case 'cron':
				$vars['view'] = 'cron';
				break;

		}
	} else {
		$vars['view'] = 'itemlist';
	}
	return $vars;
}