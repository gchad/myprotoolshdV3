<?php
/**
 * ------------------------------------------------------------------------
 * JA Promo Bar module
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
if(!defined('JA_PROMO_BAR')){
    define('JA_PROMO_BAR', 1);
}else{
    return;
}
$enable_countdown = $params->get('enable_countdown', 0);
$countdown_end_date = $params->get('countdown_end_date', '');
$countdown = ($enable_countdown && !empty($countdown_end_date));

include_once(dirname(__FILE__).'/assets/asset.php');

require JModuleHelper::getLayoutPath('mod_japromobar', $params->get('layout', 'default'));