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

<?php if ($this->countModules('intro-2')) : ?>
<!-- Features Intro -->
<div class="ja-intro ja-intro-2<?php $this->_c('intro-2') ?>">
	<jdoc:include type="modules" name="<?php $this->_p('intro-2') ?>" style="FeatureRow" />
</div>
<!-- //Features Intro -->
<?php endif ?>

