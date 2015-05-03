<?php 
$document->addScript("modules/mod_login_styles/js/forgot.js");
?>
<form action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')); ?>" method="post" name="bzLogin" class="bzLogin <?php echo $layoutName;?> <?php echo $popDir;?>" >
	<?php if ($params->get('pretext')){ ?>
		<div class="pretext">
		<p><?php echo $params->get('pretext'); ?></p>
		</div>
	<?php } ?>
	<div class="userData">
		<div class="textInput">
			<?php /*<label for="modlgn-username" id="bz_lblUsername" class="inputLabel"><?php echo JText::_('MOD_LOGIN_STYLES_VALUE_USERNAME') ?></label>*/ ?>
			<input id="modlgn-username" type="text" name="username" class="inputbox"  size="18" value="<?php echo JText::_('JGLOBAL_EMAIL') ?>" onfocus="if(value=='<?php echo JText::_('JGLOBAL_EMAIL') ?>') value = '';" onblur="if(value=='') value = '<?php echo JText::_('JGLOBAL_EMAIL') ?>'" />
						
			<div class="recoveryPanel bzUser <?php echo $popDir;?>">
				<div class="pointer"></div>
				<div class="panelWrap">
					<a href="#" class="calloutCloseBtn"><?php echo JText::_('MOD_LOGIN_STYLES_CLOSE'); ?></a>
					<p class="title"><?php echo JText::_('MOD_LOGIN_STYLES_FORGOT_USERNAME_DESC_TITLE'); ?></p>
					<p class="text"><?php echo JText::_('MOD_LOGIN_STYLES_FORGOT_USERNAME_DESC_TEXT'); ?></p>
					<span class="buttonStart"><a href="<?php echo JRoute::_('index.php?option=com_users&view=remind'); ?>"><?php echo JText::_('MOD_LOGIN_STYLES_GETSTARTED'); ?></a></span>
				</div>
			</div>
		</div>
		
		<div class="textInput">
			<?php /*<label for="modlgn-passwd" id="bz_lblPassword" class="inputLabel"><?php echo JText::_('JGLOBAL_PASSWORD') ?></label>*/ ?>
			<input id="modlgn-passwd" type="password" name="password" class="inputbox" size="18" value="<?php echo JText::_('JGLOBAL_PASSWORD') ?>" onfocus="if(value=='<?php echo JText::_('JGLOBAL_PASSWORD') ?>') value = '';" onblur="if(value=='') value = '<?php echo JText::_('JGLOBAL_PASSWORD') ?>'"/>
			<a class="forgot pass" href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>" tabindex="-1" title="<?php echo JText::_('MOD_LOGIN_STYLES_FORGOT_PASSWORD'); ?>"><span><?php echo JText::_('MOD_LOGIN_STYLES_FORGOT'); ?></span></a>
			
			<div class="recoveryPanel bzPass <?php echo $popDir;?>">
				<div class="pointer"></div>
				<div class="panelWrap">
					<a href="#" class="calloutCloseBtn" title="<?php echo JText::_('MOD_LOGIN_STYLES_CLOSE'); ?>"><?php echo JText::_('MOD_LOGIN_STYLES_CLOSE'); ?></a>
					<p class="title"><?php echo JText::_('MOD_LOGIN_STYLES_FORGOT_PASSWORD_DESC_TITLE'); ?></p>
					<p class="text"><?php echo JText::_('MOD_LOGIN_STYLES_FORGOT_PASSWORD_DESC_TEXT'); ?></p>
					<span class="buttonStart"><a href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>"><?php echo JText::_('MOD_LOGIN_STYLES_GETSTARTED'); ?></a></span>
				</div>
			</div>
		</div>
		<?php if(JPluginHelper::isEnabled('system', 'remember')) : ?>
		<div id="form-login-remember">
			<input id="modlgn-remember" type="checkbox" name="remember" class="" title="<?php echo JText::_('Remember me') ?>" />
		</div>
		<?php endif; ?>
		<input type="submit" name="Submit" class="btnLogin" value="<?php echo JText::_('JLOGIN') ?>" />
		<?php
		$usersConfig = JComponentHelper::getParams('com_users');
		if ($usersConfig->get('allowUserRegistration')) { ?>
			<a class="icoRegister" href="<?php echo JRoute::_($customSignupLink); ?>"><span><?php echo JText::_('MOD_LOGIN_STYLES_SIGNUP'); ?></span></a>
		<?php } ?>
		<input type="hidden" name="option" value="com_users" />
		<input type="hidden" name="task" value="user.login" />
		<input type="hidden" name="return" value="<?php echo $return; ?>" />
		<?php echo JHtml::_('form.token'); ?>
		
		<?php if ($params->get('posttext')){ ?>
		<div class="posttext"><p><?php echo $params->get('posttext'); ?></p></div>
		<?php } ?>
	</div>
</form>
