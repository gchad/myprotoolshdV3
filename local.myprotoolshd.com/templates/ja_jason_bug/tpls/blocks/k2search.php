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
?>

<?php if ($this->countModules('slideshow')) : ?>
<!-- Slideshow -->
<div class="ja-slideshow hidden-xs container<?php $this->_c('k2search') ?>">
	<jdoc:include type="modules" name="<?php $this->_p('k2search') ?>" style="raw" />
</div>
<!-- //Slideshow -->
<?php endif ?>

