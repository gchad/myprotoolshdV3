<?php
/**
 * @version     1.0.7
 * @package     com_hellomaps
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      JoomlaForce Team <support@joomlaforce.com> - http://www.joomlaforce.com
 */
 
// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');
// load tooltip behavior
JHtml::_('behavior.tooltip');
?>
<div id="j-sidebar-container" class="span2">
	<?php echo $this->sidebar;?>
</div>
<div id="j-main-container" class="span10">
    <iframe	name="iframe" src="<?php echo JURI::base().'index.php?option=com_hellomaps&view=mapstyleriframe&tmpl=component'; ?>" width="100%" height="800" scrolling="no" frameborder="1">Unfortunately, your browser does not support inline frames.</iframe>
</div>