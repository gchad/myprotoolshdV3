<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$params = $displayData['params'];
$pagination = $displayData['pagination'];
$mode = $params->def('pagination_type', 2) == 2 ? 'manual' : 'auto';
?>

<?php if($pagination->get('pages.total') > 1) :?> 

  <?php JFactory::getDocument()->addScript (T3_TEMPLATE_URL . '/js/infinitive-paging.js'); ?>
  
  <div id="infinity-next" class="btn btn-primary hide" data-mode="<?php echo $mode ?>" data-pages="<?php echo $pagination->get('pages.total') ?>" data-finishedmsg="<?php echo JText::_('TPL_JSLANG_FINISHEDMSG');?>"><?php echo JText::_('TPL_INFINITY_NEXT')?></div>
<?php else:?>
  <div id="infinity-next" class="btn btn-primary disabled" data-pages="1"><?php echo JText::_('TPL_JSLANG_FINISHEDMSG');?></div>
<?php endif;?>	
