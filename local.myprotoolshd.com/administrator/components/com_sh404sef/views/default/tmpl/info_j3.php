<?php
/**
 * sh404SEF - SEO extension for Joomla!
 *
 * @author      Yannick Gaultier
 * @copyright   (c) Yannick Gaultier - Weeblr llc - 2015
 * @package     sh404SEF
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version     4.4.9.2487
 * @date		2015-04-20
 */

// Security check to ensure this file is being included by a parent file.
if (!defined('_JEXEC'))
	die('Direct Access to this location is not allowed.');

?>

<div class="row-fluid">

<div id="j-sidebar-container" class="span2">
	<?php echo $this->sidebar; ?>
</div>
<div class="span10">
	<?php 
	echo Sh404sefHelperLanguage::getLanguageFilterWarning();
	include($this->readmeFilename);
	?>
</div>
</div>

<div class="sh404sef-footer-container">
	<?php echo $this->footerText; ?>
</div>
  