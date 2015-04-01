<?php
/**
 * @package   T3 Blank
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<?php
	if (!$this->getParam('addon_offcanvas_enable')) return ;
?>

<button class="btn off-canvas-toggle <?php $this->_c('off-canvas-2') ?>" type="button" data-pos="right" data-nav="#t3-off-canvas-2" data-effect="<?php echo $this->getParam('addon_offcanvas_effect', 'off-canvas-effect-4') ?>">
  <i class="fa fa-bars"></i>
</button>

<!-- OFF-CANVAS SIDEBAR -->
<div id="t3-off-canvas-2" class="t3-off-canvas <?php $this->_c('off-canvas-2') ?>" style="top: 180px;">

  <div class="t3-off-canvas-header">
    <h2 class="t3-off-canvas-header-title">Sidebar</h2>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
  </div>

  <div class="t3-off-canvas-body">
    <jdoc:include type="modules" name="<?php $this->_p('off-canvas-2') ?>" style="T3Xhtml" />
  </div>

</div>
<!-- //OFF-CANVAS SIDEBAR -->
