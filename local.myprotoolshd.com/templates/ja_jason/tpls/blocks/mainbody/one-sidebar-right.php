<?php
/**
 * @package   T3 Blank
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Mainbody 2 columns: content - sidebar
 */
?>
<div id="t3-mainbody" class="wrap t3-mainbody one-sidebar-right">
	<div class="container">
		<div class="row">
	
			<!-- MAIN CONTENT -->
			<div id="t3-content" class="t3-content equalheight col-xs-12 col-sm-8  col-md-9 ">
				<?php if($this->hasMessage()) : ?>
				<jdoc:include type="message" />
				<?php endif ?>
				<jdoc:include type="component" />

				<?php if($this->countModules('content-bottom')) : ?>
				<div class="content-bottom">
					<jdoc:include type="modules" name="content-bottom" style="xhtml" />
				</div>
				<?php endif; ?>
			</div>
			<!-- //MAIN CONTENT -->
	
			<!-- SIDEBAR RIGHT -->
			<div class="t3-sidebar t3-sidebar-right equalheight col-xs-12 col-sm-4  col-md-3 <?php $this->_c($vars['sidebar']) ?>">
				<jdoc:include type="modules" name="<?php $this->_p($vars['sidebar']) ?>" style="T3Xhtml" />
			</div>
			<!-- //SIDEBAR RIGHT -->
	
		</div>
	</div>
</div> 
