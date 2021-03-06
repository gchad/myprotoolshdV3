<?php
/**
 * ------------------------------------------------------------------------
 * JA Sugite Template
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
 */

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
?>

<div class="login-wrap row">

  <div class="login <?php echo $this->pageclass_sfx?>">

    <div class="col-xs-12 col-sm-6 login-left">
  	<form action="<?php echo JRoute::_('index.php?option=com_users&task=user.login'); ?>" method="post" class="form-horizontal">
  		
      <fieldset>
  			<?php foreach ($this->form->getFieldset('credentials') as $field): ?>
  				<?php if (!$field->hidden): ?>
  					<div class="form-group">
  						<div class="control-label">
  							<?php echo $field->label; ?>
  						</div>
  						<div class="control-input">
  							<?php echo $field->input; ?>
  						</div>
  					</div>
  				<?php endif; ?>
  			<?php endforeach; ?>

			<?php $tfa = JPluginHelper::getPlugin('twofactorauth'); ?>

			<?php if (!is_null($tfa) && $tfa != array()): ?>
				<div class="form-group">
					<div class="control-label">
						<?php echo $this->form->getField('secretkey')->label; ?>
					</div>
					<div class="control-input">
						<?php echo $this->form->getField('secretkey')->input; ?>
					</div>
				</div>
			<?php endif; ?>
			
			<?php if (JPluginHelper::isEnabled('system', 'remember')) : ?>
       <div class="form-group">
        <div>
          <div class="checkbox">
            <label>
              <input id="remember" type="checkbox" name="remember" value="yes"/> 
              <?php echo JText::_(version_compare(JVERSION, '3.0', 'ge') ? 'COM_USERS_LOGIN_REMEMBER_ME' : 'JGLOBAL_REMEMBER_ME') ?>
            </label>
          </div>
        </div>
      </div>
      <?php endif; ?>
			
  			<div class="form-group">
  				<div>
  					<button type="submit" class="btn btn-primary"><?php echo JText::_('JLOGIN'); ?></button>
  				</div>
  			</div>
  			<input type="hidden" name="return" value="<?php echo base64_encode($this->params->get('login_redirect_url', $this->form->getValue('return'))); ?>" />
  			<?php echo JHtml::_('form.token'); ?>
  		</fieldset>

      <div class="other-links form-group">
        <div>
        <ul>
          <li><a href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>">
            <?php echo JText::_('COM_USERS_LOGIN_RESET'); ?></a></li>
          <li><a href="<?php echo JRoute::_('index.php?option=com_users&view=remind'); ?>">
            <?php echo JText::_('COM_USERS_LOGIN_REMIND'); ?></a></li>
        </ul>
        </div>
      </div>

  	</form>
    </div>


  <div class="col-xs-12 col-sm-6 login-right">
    <div class="inner">
      <?php if ($this->params->get('show_page_heading')) : ?>
      <div class="page-header">
        <h1>
          <?php echo $this->escape($this->params->get('page_heading')); ?>
        </h1>
      </div>
      <?php endif; ?>

      <?php if (($this->params->get('logindescription_show') == 1 && str_replace(' ', '', $this->params->get('login_description')) != '') || $this->params->get('login_image') != '') : ?>
      <div class="login-description">
      <?php endif; ?>

        <?php if (($this->params->get('login_image')!='')) :?>
          <div class="login-image"><img src="<?php echo $this->escape($this->params->get('login_image')); ?>" class="login-image" alt="<?php echo JTEXT::_('COM_USER_LOGIN_IMAGE_ALT')?>"/></div>
        <?php endif; ?>

        <?php if($this->params->get('logindescription_show') == 1) : ?>
          <p><?php echo $this->params->get('login_description'); ?></p>
        <?php endif; ?>

        <?php
        $usersConfig = JComponentHelper::getParams('com_users');
        if ($usersConfig->get('allowUserRegistration')) : ?>
        <a href="<?php echo JRoute::_('index.php?option=com_users&view=registration'); ?>">
          <?php echo JText::_('COM_USERS_LOGIN_REGISTER'); ?>
        </a>
        <?php endif; ?>


        <?php if (($this->params->get('logindescription_show') == 1 && str_replace(' ', '', $this->params->get('login_description')) != '') || $this->params->get('login_image') != '') : ?>
        </div>
        <?php endif; ?>
      </div>
    </div>

  </div>

</div>