<?php
/**
 * @version		1.0
 * @copyright	Copyright (C) Cecil Gupta. All rights reserved.
 */

// no direct access
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );


if(version_compare(JVERSION,'3.0.0','l')) {
	JHtml::_('behavior.mootools');
}

$document = JFactory::getDocument();
$document->addStyleSheet("modules/mod_login_styles/css/style.css");
$document->addStyleSheet("modules/mod_login_styles/css/bookmark.css");
$document->addScript("modules/mod_login_styles/js/bookmark.js");

	
if(version_compare(JVERSION,'1.6.0','ge')) {
	JHtml::_('behavior.keepalive');
	if ($type == 'logout') {
		require_once('j16/popbook_logout.php');
	} else {
		require_once('j16/popbook_login.php');
	}
} else {
	//JOOMLA 1.5 CODE
	?>
	<?php if($type == 'logout') {
	JHtml::_('behavior.keepalive');
		require_once('j15/popbook_logout.php');
	} else { 
		require_once('j15/popbook_login.php');
	};
}