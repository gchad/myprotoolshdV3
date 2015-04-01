<?php
/**
 * ------------------------------------------------------------------------
 * JA Jason template
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
 */

defined('_JEXEC') or die;

// get params
$sitename  = $this->params->get('sitename');
$slogan    = $this->params->get('slogan', '');
$logotype  = $this->params->get('logotype', 'text');
$logoimage = $logotype == 'image' ? $this->params->get('logoimage', T3Path::getUrl('images/logo.png', '', true)) : '';
$logoimgsm = ($logotype == 'image' && $this->params->get('enable_logoimage_sm', 0)) ? $this->params->get('logoimage_sm', T3Path::getUrl('images/logo-sm.png', '', true)) : false;

if (!$sitename) {
	$sitename = JFactory::getConfig()->get('sitename');
}

$logosize = 'col-lg-3 col-sm-2 col-xs-5';

$mainnavsize = 'col-lg-9 col-sm-9 col-xs-7';
if ($headright = $this->countModules('head-search or languageswitcherload or off-canvas-1 or off-canvas-2')) {
	$mainnavsize = 'col-lg-7 col-sm-8';	
}

?>

<!-- HEADER -->
<header id="t3-header" class="wrap t3-header t3-header-6">
	<div class="container">
		<div class="row">
			<!-- LOGO -->
			<div class="<?php echo $logosize ?> logo">
				<div class="logo-<?php echo $logotype, ($logoimgsm ? ' logo-control' : '') ?>">
					<a href="<?php echo JURI::base(true) ?>" title="<?php echo strip_tags($sitename) ?>">
						<?php if($logotype == 'image'): ?>
							<img class="logo-img" src="<?php echo JURI::base(true) . '/' . $logoimage ?>" alt="<?php echo strip_tags($sitename) ?>" />
						<?php endif ?>
						<?php if($logoimgsm) : ?>
							<img class="logo-img-sm" src="<?php echo JURI::base(true) . '/' . $logoimgsm ?>" alt="<?php echo strip_tags($sitename) ?>" />
						<?php endif ?>
						<span><?php echo $sitename ?></span>
					</a>
					<small class="site-slogan"><?php echo $slogan ?></small>
				</div>
			</div>
			<!-- //LOGO -->
			
			<!-- BUTTON RIGHT -->
			<?php if ($headright): ?>
					<div class="t3-nav-btn pull-right">
						<div class="btn-fix pull-right">
							<!-- OFFCANVAS -->
							<?php if ($this->countModules('off-canvas-1')) : ?>
								<?php if ($this->getParam('addon_offcanvas_enable')) : ?>
									<?php $this->loadBlock ('off-canvas-1') ?>
								<?php endif ?>
							<?php endif ?>
							
							<?php if ($this->countModules('off-canvas-2')) : ?>
								<?php if ($this->getParam('addon_offcanvas_enable')) : ?>
									<?php $this->loadBlock ('off-canvas-2') ?>
								<?php endif ?>
							<?php endif ?>
							<!-- //OFFCANVAS -->
						</div>
				
						<!-- Brand and toggle get grouped for better mobile display -->
						<div class="navbar-header pull-right">
						
							<?php if ($this->getParam('navigation_collapse_enable', 1) && $this->getParam('responsive', 1)) : ?>
								<?php $this->addScript(T3_URL.'/js/nav-collapse.js'); ?>
								<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".t3-navbar-collapse">
									<i class="fa fa-bars"></i>
								</button>
							<?php endif ?>
						</div>
	
						<?php if ($this->countModules('languageswitcherload')) : ?>
							<!-- LANGUAGE SWITCHER -->
							<div class="languageswitcherload<?php $this->_c('languageswitcherload') ?>">
								<jdoc:include type="modules" name="<?php $this->_p('languageswitcherload') ?>" style="raw" />
							</div>
							<!-- //LANGUAGE SWITCHER -->
						<?php endif ?>
						
						<!-- HEAD SEARCH -->
						<?php if ($this->countModules('head-search')) : ?>
							<div class="dropdown nav-search pull-right<?php $this->_c('head-search') ?>">
								<a data-toggle="dropdown" href="#" class="dropdown-toggle">
									<i class="fa fa-search"></i>									
								</a>
								<div class="nav-child dropdown-menu container">
									<div class="dropdown-menu-inner">
										<jdoc:include type="modules" name="<?php $this->_p('head-search') ?>" style="T3Xhtml" />
									</div>
								</div>
							</div>
						<?php endif ?>
						<!-- //HEAD SEARCH -->
					</div>
			<?php endif ?>
			<!-- //BUTTON RIGHT -->
			
			<!-- MAIN NAVIGATION -->
			<nav id="t3-mainnav" class="<?php echo $mainnavsize; ?>">
				<div class="navbar navbar-default t3-mainnav pull-left">
			
					<?php if ($this->getParam('navigation_collapse_enable')) : ?>
						<div class="t3-navbar-collapse navbar-collapse collapse"></div>
					<?php endif ?>
			
					<div class="t3-navbar navbar-collapse collapse">
						<jdoc:include type="<?php echo $this->getParam('navigation_type', 'megamenu') ?>" name="<?php echo $this->getParam('mm_type', 'mainmenu') ?>" />
					</div>
			
				</div>
				
			</nav>
			<!-- //MAIN NAVIGATION -->

		</div>
		
		<?php if ($this->countModules('intro-1')) : ?>
		<!-- Features Intro -->
		<div class="clearfix ja-intro ja-intro-1<?php $this->_c('intro-1') ?>">
			<jdoc:include type="modules" name="<?php $this->_p('intro-1') ?>" style="raw" />
		</div>
		<!-- //Features Intro -->
		<?php endif ?>
	</div>
</header>
<!-- //HEADER -->
