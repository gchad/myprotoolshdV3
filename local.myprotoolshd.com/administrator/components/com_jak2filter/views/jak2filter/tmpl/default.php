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

// No direct access.
defined('_JEXEC') or die;

JHTML::_('behavior.modal');


$params = JComponentHelper::getParams('com_jak2filter');
$key = $params->get('indexing_cron_key', 'indexing');
$cronUrl = JURI::root().'index.php?option=com_jak2filter&view=cron&jakey='.$key;
?>
<h1>JA K2 Filter Component for Joomla 2.5.x and Joomla 3.x</h1>
<div style='font-weight: normal'>
<p><span style='color: #ff6600;'><strong>Features:</strong></span>
JA K2 Filter Component</p>

<strong><span style='color: #ff0000;'>Usage Instruction:</span></strong><br />
<ul>
<li>Enable JA K2 Filter Module in Module Manager</li>
</ul>
<strong><span style='color: #ff0000;'>Upgrade Method:</span><br /></strong>
<ul>
  <li>You can install new version directly over this version. Uninstallation is not required. </li>
  </ul>

<span style='color: #008000;'><strong>Links:</strong></span><br />
<ul>
<li><a target="_blank" href="http://www.joomlart.com/joomla/extensions/ja-k2-search">Wiki Userguide</a></li>
 <li><a target='_blank' href='http://www.joomlart.com/forums/downloads.php?do=cat&id=20372'>Updates &amp; Versions</a></li>
</ul>
<p>Copyright 2004 - 2012 <a href='http://www.joomlart.com/' title='Visit Joomlart.com!'>JoomlArt.com</a>.</p>
</div>

<a id="reindexing-link" class="modal" rel="{handler: 'iframe', size: {x: 500, y: 350}}" href="<?php echo $cronUrl ?>" title="">&nbsp;</a>
<script type="text/javascript">

Joomla.submitbutton = function(pressbutton) {
	if (pressbutton == "reindexing") {
		$('reindexing-link').fireEvent('click');
	}
} 
</script>