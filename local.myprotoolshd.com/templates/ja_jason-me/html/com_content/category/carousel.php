<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers');
JHtml::addIncludePath(T3_PATH.'/html/com_content');
JHtml::addIncludePath(dirname(dirname(__FILE__)));
JHtml::_('behavior.caption');

$tplparams = JFactory::getApplication()->getTemplate(true)->params;

$headertype   = $this->params->get('headertype', 'image');
$headerimage  = $tplparams->get('headerimage','');
?>
<div id="portfolio-carousel" class="carousel slide" data-ride="carousel" itemscope itemtype="http://schema.org/Blog" data-interval="false">
	<?php if (empty($this->intro_items)) : ?>
		<?php if ($this->params->get('show_no_articles', 1)) : ?>
			<p><?php echo JText::_('COM_CONTENT_NO_ARTICLES'); ?></p>
		<?php endif; ?>
	<?php endif; ?>
	
  <!-- Indicators -->
  <ol class="carousel-indicators">  
  <?php if (!empty($this->intro_items)) : 
    $counter=0; 
    $cls = ' class="active"';
  ?>
	<?php foreach ($this->intro_items as $key => &$item) : ?>
    <li data-target="#portfolio-carousel" data-slide-to="<?php echo $counter++; ?>"<?php echo $cls ?>></li>
  <?php 
    $cls = '';
  endforeach; 
  ?>
	<?php endif; ?>
	
  </ol>
	
  <!-- Wrapper for slides -->
  <div class="carousel-inner">
  
  <?php if (!empty($this->intro_items)) : 
    $active = ' active';
  ?>
	<?php foreach ($this->intro_items as $key => &$item) : ?>

		<div class="item<?php echo $active ?>" itemprop="blogPost" itemscope itemtype="http://schema.org/BlogPosting">
			<?php
			$this->item = &$item;
			echo $this->loadTemplate('item');
		?>
		</div><!-- end item -->

	<?php 
    $active = '';
  endforeach; 
  ?>
	<?php endif; ?>
  </div>

  <!-- Controls -->
  <a class="left carousel-control" href="#portfolio-carousel" role="button" data-slide="prev">
    <span class="fa fa-angle-left"></span>
  </a>
  <a class="right carousel-control" href="#portfolio-carousel" role="button" data-slide="next">
    <span class="fa fa-angle-right"></span>
  </a>
</div>