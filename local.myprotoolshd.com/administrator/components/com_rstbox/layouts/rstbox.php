<?php
/**
 * @package     Responsive Scroll Triggered Box
 * @subpackage  com_rstbox
 * @copyright   Copyright (C) 2014 Tassos Marinos - http://www.tassos.gr
 * @license     GNU General Public License version 2 or later; see http://www.tassos.gr/license
 */

defined('JPATH_BASE') or die;
$boxes = $displayData;
$p = JComponentHelper::getParams('com_rstbox');
$forceLoadMedia = $p->get("forceloadmedia");
$debug = $p->get("debug", 0);

?>

 <!-- Responsive Scroll Triggered Box for Joomla -->
 <!-- by Tassos Marinos - www.tassos.gr/joomla-extensions -->
<div class="rstboxes " data-site="<?php echo md5(JPATH_SITE) ?>" data-debug="<?php echo $debug ?>">
	<?php if ($forceLoadMedia) { ?>
		<link rel="stylesheet" href="<?php echo JURI::root(true) ?>/components/com_rstbox/assets/css/rstbox.css?v=<?php echo RstboxHelper::getVer() ?>" type="text/css" />

        <?php if ($p->get('jquery', 1)) { ?>
        	<script src="//code.jquery.com/jquery-latest.min.js" type="text/javascript"></script>
        <?php } ?>

		<script src="<?php echo JURI::root(true) ?>/components/com_rstbox/assets/js/rstbox.js?v=<?php echo RstboxHelper::getVer() ?>" type="text/javascript"></script>
	<?php } ?>

	<?php foreach ($boxes as $box) { ?>
	<div id="rstbox_<?php echo $box->id ?>" class="rstbox <?php echo implode(" ",$box->classes) ?>" data-overlay="<?php echo $box->params->overlay ?>" data-delay="<?php echo $box->params->triggerdelay ?>" data-autohide="<?php echo $box->params->autohide ?>" data-trigger="<?php echo $box->trigger_prepared ?>" data-anim="<?php echo $box->animation ?>" data-cookie="<?php echo $box->cookie ?>" data-testmode="<?php echo $box->testmode .":". $box->isroot ?>" style="<?php echo $box->style ?>">
		<?php 
			$customCSS = (isset($box->params->customcss)) ? $box->params->customcss : "";
			if ($customCSS) { echo "<style>$customCSS</style>"; }	
		?>

		<a class="rstbox-close" href="#">x</a>
		<div class="rstbox-container">
			<?php if ($box->params->showtitle) { ?>
			<div class="rstbox-header">
				<div class="rstbox-heading"><?php echo $box->name ?></div>
			</div>
			<?php } ?>
			<div class="rstbox-content"><?php echo RstboxHelper::boxRender($box) ?></div>
		</div>

		<?php 
			if (isset($box->params->customcode)) {
				echo $box->params->customcode;
			}
		?>

	</div>	
	<?php } ?>
</div>
 <!-- End of Responsive Scroll Triggered Box for Joomla -->
