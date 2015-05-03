
<div id="bz<?php echo ucfirst($layoutName);?>Wrap" class="posRel">
<button id="<?php if ($type == 'logout'){ echo 'popLogout';}else{echo 'popLogin';}?>"><span class="icoLogin"><?php if ($type == 'logout'){ echo JText::_('JLOGOUT');}else{echo JText::_('MOD_LOGIN_STYLES_LOGIN_SIGNUP');}?></span></button>
	<form style="display:none;" action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')); ?>" method="post" name="bzLogin" class="bzLogin <?php echo $layoutName;?> <?php echo $popDir;?>" id="bz<?php echo ucfirst($layoutName);?>Login" >
		<span class="pointer"></span>
		<a href="#" class="calloutCloseBtn"><?php echo JText::_('MOD_LOGIN_STYLES_CLOSE'); ?></a>
		<?php if ($params->get('pretext')){ ?>
			<div class="pretext">
			<p><?php echo $params->get('pretext'); ?></p>
			</div>
		<?php } ?>
		<div class="userData">
			<div class="textInput">
				<?php /*<label for="modlgn-username" id="bz_lblUsername" class="inputLabel"><?php echo JText::_('MOD_LOGIN_STYLES_VALUE_USERNAME') ?></label>*/ ?>
			<input id="modlgn-username" type="text" name="username" class="inputbox"  size="18" value="<?php echo JText::_('JGLOBAL_EMAIL') ?>" onfocus="if(value=='<?php echo JText::_('JGLOBAL_EMAIL') ?>') value = '';" onblur="if(value=='') value = '<?php echo JText::_('JGLOBAL_EMAIL') ?>'" />
				
			</div>
			
			<div class="textInput">
				<?php /*<label for="modlgn-passwd" id="bz_lblPassword" class="inputLabel"><?php echo JText::_('JGLOBAL_PASSWORD') ?></label>*/ ?>
				<input id="modlgn-passwd" type="password" name="password" class="inputbox" size="18" value="<?php echo JText::_('JGLOBAL_PASSWORD') ?>" onfocus="if(value=='<?php echo JText::_('JGLOBAL_PASSWORD') ?>') value = '';" onblur="if(value=='') value = '<?php echo JText::_('JGLOBAL_PASSWORD') ?>'"/>
				<a class="forgot pass" href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>" tabindex="-1" title="<?php echo JText::_('MOD_LOGIN_STYLES_FORGOT_PASSWORD'); ?>"><span><?php echo JText::_('MOD_LOGIN_STYLES_FORGOT'); ?></span></a>
			</div>
			<?php if(JPluginHelper::isEnabled('system', 'remember')) : ?>
			<div id="form-login-remember">
				<label ><input id="modlgn-remember" type="checkbox" name="remember" class="" title="<?php echo JText::_('Remember me') ?>" /><?php echo JText::_('MOD_LOGIN_STYLES_REMEMBER_ME') ?></label>
			</div>
			<?php endif; ?>
			<input type="submit" name="Submit" class="btnLogin" value="<?php echo JText::_('JLOGIN') ?>" />
			<?php
			$usersConfig = JComponentHelper::getParams('com_users');
			if ($usersConfig->get('allowUserRegistration')) { 
			?>
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
</div>

<script type="text/javascript">
	var JLOGIN = "<?php echo JText::_('MOD_LOGIN_STYLES_LOGIN_SIGNUP'); ?>";
	var JLOGOUT = "<?php echo JText::_('JLOGOUT'); ?>";
	var JHIDE = "<?php echo JText::_('JHIDE'); ?>";
</script>

