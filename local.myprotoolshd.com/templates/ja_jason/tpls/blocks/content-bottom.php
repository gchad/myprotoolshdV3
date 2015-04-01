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

<?php if ($this->countModules('content-bottom')) : ?>
<!-- Content bottom -->
<div class="ja-content-bottom container<?php $this->_c('content-bottom') ?>">
	<jdoc:include type="modules" name="<?php $this->_p('content-bottom') ?>" style="raw" />
</div>
<!-- //Content bottom -->
<?php endif ?>