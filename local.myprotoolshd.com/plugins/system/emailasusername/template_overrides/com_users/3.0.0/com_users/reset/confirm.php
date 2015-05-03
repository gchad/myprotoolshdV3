<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidation');
?>
<div class="reset-confirm<?php echo $this->pageclass_sfx?>">
	<?php if ($this->params->get('show_page_heading')) : ?>
	<h1>
		<?php echo $this->escape($this->params->get('page_heading')); ?>
	</h1>
	<?php endif; ?>

	<form action="<?php echo JRoute::_('index.php?option=com_users&task=reset.confirm'); ?>" method="post" class="form-validate">

		<?php foreach ($this->form->getFieldsets() as $fieldset) : ?>
		<p><?php echo JText::_($fieldset->label); ?></p>		<fieldset>
			<dl>
			
			<dt><label id="jform_username-lbl" for="jform_username" class="hasTip required" title="<?php echo JText::_('JGLOBAL_EMAIL') ?>"><?php echo JText::_('JGLOBAL_EMAIL') ?><span class="star">&#160;*</span></label></dt>
					<dd><input type="text" name="jform[username]" id="jform_username" value="" class="required" size="30" required="required" aria-required="true"/></dd>
				
								<dt><label id="jform_token-lbl" for="jform_token" class="hasTip required" title="Verification Code::Enter the password reset verification code you received by email.">Verification Code:<span class="star">&#160;*</span></label></dt>
					<dd><input type="text" name="jform[token]" id="jform_token" value="" class="required" size="32" required="required" aria-required="true"/></dd>
			</dl>
		</fieldset>
		<?php endforeach; ?>

		<div>
			<button type="submit" class="validate"><?php echo JText::_('JSUBMIT'); ?></button>
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>
