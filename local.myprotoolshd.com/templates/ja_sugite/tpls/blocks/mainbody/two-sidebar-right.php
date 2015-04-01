<?php
/**
 * ------------------------------------------------------------------------
 * JA Sugite Template
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
 */

defined('_JEXEC') or die;

/**
 * Mainbody 3 columns, content in center: content - sidebar1 - sidebar2
 */
?>

<div id="t3-mainbody" class="container t3-mainbody">
	<div class="row">

		<!-- MAIN CONTENT -->
		<div id="t3-content" class="t3-content col-xs-12 col-sm-8 col-md-6">
			<?php if($this->hasMessage()) : ?>
			<jdoc:include type="message" />
			<?php endif ?>
			<jdoc:include type="component" />
		</div>
		<!-- //MAIN CONTENT -->

		<div class="t3-sidebar col-xs-12 col-sm-4 col-md-6">
			<div class="row">

				<?php if ($vars['mastcol']) : ?>
					<!-- MASSCOL 1 -->
					<div class="t3-mastcol t3-mastcol-1 <?php $this->_c($vars['mastcol']) ?>">
						<jdoc:include type="modules" name="<?php $this->_p($vars['mastcol']) ?>" style="T3Xhtml" />
					</div>
					<!-- //MASSCOL 1 -->
				<?php endif ?>

				<!-- SIDEBAR 1 -->
				<div class="t3-sidebar t3-sidebar-1 col-xs-6 col-sm-12 col-md-6 <?php $this->_c($vars['sidebar1']) ?>">
					<jdoc:include type="modules" name="<?php $this->_p($vars['sidebar1']) ?>" style="T3Xhtml" />
				</div>
				<!-- //SIDEBAR 1 -->
			
				<!-- SIDEBAR 2 -->
				<div class="t3-sidebar t3-sidebar-2 col-xs-6 col-sm-12 col-md-6 <?php $this->_c($vars['sidebar2']) ?>">
					<jdoc:include type="modules" name="<?php $this->_p($vars['sidebar2']) ?>" style="T3Xhtml" />
				</div>
				<!-- //SIDEBAR 2 -->
	
			</div>
		</div>

	</div>
</div> 