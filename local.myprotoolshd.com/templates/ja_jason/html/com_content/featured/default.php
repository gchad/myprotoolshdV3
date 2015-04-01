<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers');
JHtml::addIncludePath(T3_PATH.'/html/com_content');
JHtml::addIncludePath(dirname(dirname(__FILE__)));
JHtml::_('behavior.caption');
//register the helper class
JLoader::register('JasonHelper', T3_TEMPLATE_PATH . '/templateHelper.php');

$menu = JFactory::getApplication()->getMenu();
$active = $menu->getActive() ? $menu->getActive() : $menu->getDefault();
$params = $active->params;
$grid_lg = $params->get('itemlg',4);
$grid_md = $params->get('itemmd',3);
$grid_sm = $params->get('itemsm',2);
$grid_smx = $params->get('itemsmx',2);
$grid_xs = $params->get('itemxs',1);
$gutter  = $params->get('gutter',30);
$mgutter = $gutter / 2;

$grid_cls = "grid-xs-{$grid_xs} grid-smx-{$grid_smx} grid-sm-{$grid_sm} grid-md-{$grid_md} grid-lg-{$grid_lg}";
$grid_css = ".ja-masonry-wrap {margin-left:-{$mgutter}px;margin-right:-{$mgutter}px;}\n";
$grid_css .= ".ja-masonry-wrap .item{padding-left:{$mgutter}px;padding-right:{$mgutter}px;padding-bottom:{$gutter}px}\n";

$doc = JFactory::getDocument();
$doc->addStyleSheet (T3_TEMPLATE_URL . '/css/masonry.css');
$doc->addStyleDeclaration ($grid_css);
$doc->addScript (T3_TEMPLATE_URL . '/js/jquery.infinitescroll.min.js');
$doc->addScript (T3_TEMPLATE_URL . '/js/imagesloaded.pkgd.min.js');
$doc->addScript (T3_TEMPLATE_URL . '/js/isotope.pkgd.min.js');
?>


<div class="ja-masonry-wrap">
<?php if ( $this->params->get('show_page_heading')!=0) : ?>
	<h1>
	<?php echo $this->escape($this->params->get('page_heading')); ?>
	</h1>
<?php endif; ?>

<div id="grid" class="grid isotope blog clearfix <?php echo $this->pageclass_sfx;?> <?php echo $grid_cls ?>">
  <div class="item grid-sizer"></div>
<?php $leadingcount=0 ; ?>
<?php if (!empty($this->lead_items)) : ?>

	<?php foreach ($this->lead_items as &$item) : ?>
			<?php
				$this->item = &$item;
				echo $this->loadTemplate('item');
			?>
		<?php
			$leadingcount++;
		?>
	<?php endforeach; ?>

<?php endif; ?>
<?php
	$introcount=(count($this->intro_items));
	$counter=0;
?>
<?php if (!empty($this->intro_items)) : ?>
	<?php foreach ($this->intro_items as $key => &$item) : ?>

	<?php
		$key= ($key-$leadingcount)+1;
	?>

			<?php
					$this->item = &$item;
					echo $this->loadTemplate('item');
			?>

		<?php $counter++; ?>

	<?php endforeach; ?>
<?php endif; ?>

</div>

<?php echo JLayoutHelper::render('joomla.content.pagination', array('params'=>$this->params, 'pagination'=>$this->pagination)); ?>

</div>
