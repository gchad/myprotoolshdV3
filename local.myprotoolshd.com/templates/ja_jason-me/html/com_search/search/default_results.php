<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_search
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>

<dl class="search-results<?php echo $this->pageclass_sfx; ?>">
	<?php foreach ($this->results as $result) : ?>
		<dt class="result-title">
			<?php echo $this->pagination->limitstart + $result->count . '. '; ?>
			<?php if ($result->href) : ?>
				<a href="<?php echo JRoute::_($result->href); ?>"
					<?php if ($result->browsernav == 1) : ?>
						target="_blank"
					<?php endif; ?>>
					<?php echo $this->escape($result->title); ?>
				</a>
			<?php else: ?>
				<?php echo $this->escape($result->title); ?>
			<?php endif; ?>
		</dt>

		<?php if (($result->section) || $this->params->get('show_date') ) : ?>
			<dd class="result-meta">
			
			<span class="<?php echo $this->pageclass_sfx; ?>">
				<?php echo $this->escape($result->section); ?>
			</span>

			<span class="sep">/</span>

			<span><?php echo JText::sprintf('JGLOBAL_CREATED_DATE_ON', '<strong>' . $result->created . '</strong>'); ?></span>

			</dd>
		<?php endif; ?>

		<dd class="result-text">
			<?php echo $result->text; ?>
		</dd>
		
	<?php endforeach; ?>
</dl>

<div class="pagination-wrap">
	<?php echo $this->pagination->getPagesLinks(); ?>
</div>
