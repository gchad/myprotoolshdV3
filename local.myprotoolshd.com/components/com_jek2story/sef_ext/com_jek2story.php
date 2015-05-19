<?php
/**
 * @version		2.6.x
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

global $sh_LANG;
$sefConfig = shRouter::shGetConfig();
$shLangName = '';
$shLangIso = '';
$title = array();
$shItemidString = '';
$dosef = shInitializePlugin($lang, $shLangName, $shLangIso, $option);
if ($dosef == false)
    return;


if(isset($view) && $view = 'jesubmit'){
    $title[] = 'join';
}

if ($dosef)
{
    $string = shFinalizePlugin($string, $title, $shAppendString, $shItemidString, (isset($limit) ? @$limit : null), (isset($limitstart) ? @$limitstart : null), (isset($shLangName) ? @$shLangName : null));
}

