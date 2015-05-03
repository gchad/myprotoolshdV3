<?php
/**
 * @package 	mod_bt_sociallogin - BT Sociallogin Module
 * @version		1.0.0
 * @created		April 2012
 * @author		BowThemes
 * @email		support@bowthems.com
 * @website		http://bowthemes.com
 * @support		Forum - http://bowthemes.com/forum/
 * @copyright	Copyright (C) 2011 Bowthemes. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 *
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
?>
<div id="btl">
	<!-- Panel top -->	
	<div class="btl-panel">
		<?php if($type == 'logout') : ?>
		<!-- Profile button -->
		<span id="btl-panel-profile" class="btl-dropdown">
			
			<?php
			echo JText::_("BTL_WELCOME").", ";
			if($params->get('name') == 1) : {
				echo $user->get('name');
			} else : {
				echo $user->get('username');
			} endif;
			?>
		</span> 
		<?php else : ?>
			<!-- Login button -->
			 <span id="btl-panel-login" class="<?php echo $effect;?>"><?php echo JText::_('JLOGIN');?></span> 
			
			<!-- Registration button -->
			<?php
			if($enabledRegistration){
				$option = JRequest::getCmd('option');
				$task = JRequest::getCmd('task');
				if($option!='com_user' && $task != 'register' ){
			?>
			<span id="btl-panel-registration" class="<?php echo $effect;?>"><?php echo JText::_('JREGISTER');?></span>
			<?php }
			} ?>
			
			
		<?php endif; ?>
	</div>
	<!-- content dropdown/modal box -->
	<div id="btl-content">
		<?php if($type == 'logout') { ?>
		<!-- Profile module -->
		<div id="btl-content-profile" class="btl-content-block">
			<div class="bt-scroll">
			<div class="bt-scroll-inner">
				<div id="module-in-profile">
					<?php echo $loggedInHtml; ?>
				</div>
				<?php if($showLogout == 1):?>
				<div class="btl-buttonsubmit">
					<form action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')); ?>" method="post" name="logoutForm">
						<button name="Submit" class="btl-buttonsubmit" onclick="document.logoutForm.submit();"><?php echo JText::_('JLOGOUT'); ?></button>
						<input type="hidden" name="option" value="com_users" />
						<input type="hidden" name="task" value="user.logout" />
						<input type="hidden" name="return" value="<?php echo $return; ?>" />
						<?php echo JHtml::_('form.token'); ?>
					</form>
				</div>
				<?php endif;?>
			</div>
			</div>
		</div>
		<?php }else{ ?>	
		<!-- Form login -->	
		<div id="btl-content-login" class="btl-content-block">
			<?php if(JPluginHelper::isEnabled('authentication', 'openid')) : ?>
				<?php JHTML::_('script', 'openid.js'); ?>
			<?php endif; ?>
			
			<!-- if not integrated any component -->
			<?php if($integrated_com==''|| $moduleRender == ''){?>
			<form name="btl-formlogin" class="btl-formlogin" action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')); ?>" method="post">
				<div id="btl-login-in-process"></div>	
				<h3><?php echo JText::_('LOGIN_TO_YOUR_ACCOUNT') ?></h3>
				<?php if ($enabledRegistration) : ?>
					<div id="register-link">
						<?php echo sprintf(JText::_('DONT_HAVE_AN_ACCOUNT_YET'),'<a href="'.JRoute::_('index.php?option=com_users&view=registration').'">','</a>');?>
					</div>
				<?php else: ?>
					<div class="spacer"></div>
				<?php endif; ?>
				<div class="btl-error" id="btl-login-error"></div>
				<div class="btl-field">
					<div class="btl-label"><?php echo JText::_('JGLOBAL_EMAIL') ?></div>
					<div class="btl-input">
						<input id="btl-input-username" type="text" name="username"	/>
					</div>
				</div>
				<div class="btl-field">
					<div class="btl-label"><?php echo JText::_('MOD_BT_LOGIN_PASSWORD') ?></div>
					<div class="btl-input">
						<input id="btl-input-password" type="password" name="password" />
					</div>
				</div>
				<div class="clear"></div>
				<?php if (JPluginHelper::isEnabled('system', 'remember')) : ?>
				<div class="btl-field">				
					<div class="btl-input" id="btl-input-remember">
						<input id="btl-checkbox-remember"  type="checkbox" name="remember"
							value="yes" />
							<?php echo JText::_('BT_REMEMBER_ME'); ?>
					</div>	
				</div>
				<div class="clear"></div>
				<?php endif; ?>
				<div class="btl-buttonsubmit">
					<input type="submit" name="Submit" class="btl-buttonsubmit button" onclick="return loginAjax()" value="<?php echo JText::_('JLOGIN') ?>" /> 
					<input type="hidden" name="option" value="com_users" />
					<input type="hidden" name="task" value="user.login" /> 
					<input type="hidden" name="return" id="btl-return"	value="<?php echo $return; ?>" />
					<?php echo JHtml::_('form.token');?>
				</div>
			</form>	
			<ul id ="bt_ul">
				<li>
					<a href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>">
					<?php echo JText::_('BT_FORGOT_YOUR_PASSWORD'); ?></a>
				</li>
			
			</ul>
			<?php if($enable_fb || $enable_gg || $enable_twitter):?>
			<div id="social-connect">
				<div><?php echo JText::_('SIGNIN_SOCIAL');?></div>
				<ul>
					<?php if($enable_fb):?><li id="bt-facebook"><a href="JavaScript:newPopup('<?php echo $dialog_urlFB?>','<?php echo JText::sprintf ( 'MOD_BT_LOGIN_CONNECT_TITLE', 'Facebook'); ?>',600,400);"><img alt="facebook" src="<?php echo JURI::root(); ?>/modules/mod_bt_sociallogin/tmpl/images/fb.png"/></a></li><?php endif;?>
					<?php if($enable_gg):?><li id="bt-google"><a href="JavaScript:newPopup('<?php echo $dialog_urlGG?>','<?php echo JText::sprintf ( 'MOD_BT_LOGIN_CONNECT_TITLE', 'Google'); ?>',600,400);"><img alt="google" src="<?php echo JURI::root(); ?>/modules/mod_bt_sociallogin/tmpl/images/gg.png" /></a></li><?php endif;?>
					<?php if($enable_twitter):?><li id="bt-twitter"><a href="JavaScript:newPopup('<?php echo $dialog_urlTT?>','<?php echo JText::sprintf ( 'MOD_BT_LOGIN_CONNECT_TITLE', 'Twitter'); ?>',600,400);"><img alt="twitter" src="<?php echo JURI::root(); ?>/modules/mod_bt_sociallogin/tmpl/images/tt.png"/></a></li><?php endif;?>
				</ul>
			</div>
			<?php endif;?>
		<!-- if integrated with one component -->
			<?php }else{ ?>
				<h3><?php echo JText::_('JLOGIN') ?></h3>
				<div id="btl-wrap-module"><?php  echo $moduleRender; ?></div>
				<?php }?>			
		</div>
		
		<?php if($enabledRegistration ){ ?>			
		<div id="btl-content-registration"  class="btl-content-block">			
			<!-- if not integrated any component -->
			<?php if($integrated_com==''){
				$captchaField ='';
			?>
			<form id="btl-form-register" name="btl-formregistration" class="btl-formregistration form-validate" onsubmit="return registerAjax();"  autocomplete="off">
					<div id="btl-register-in-process"></div>	
					<h3><?php echo JText::_('CREATE_AN_ACCOUNT') ?></h3>
					<div id="btl-success"></div>
					<div class="bt-scroll">
						<div class="bt-scroll-inner">
						<div class="btl-note"><span><?php echo JText::_("BTL_REQUIRED_FIELD"); ?></span></div>
						<div id="btl-registration-error" class="btl-error"></div>
						<?php foreach ($form->getFieldsets() as $fieldset): // Iterate through the form fieldsets and display each one.?>
							<?php 
								if($fieldset->name == 'profile' && !$params->get('enable_profile')){
									continue;
								}
								$fields = $form->getFieldset($fieldset->name);
								
							?>
							<?php if (count($fields)):?>
								<?php //die("<pre>" . print_r($fields,true) . "</pre>"); ?>
								<?php foreach($fields as $field):// Iterate through the fields in the set and display them.?>
									<?php if(strtolower($field->name)=='jform[username]'){ continue; } ?>
									<?php if(strtolower($field->type)=='captcha'){ $captchaField = $field; continue; } ?>
									<?php if(strtolower($field->type)=='spacer'){ continue; } ?>
									<?php if ($field->hidden):// If the field is hidden, just display the input.?>
										<?php echo $field->input;?>
									<?php else:?>
										  <div class="btl-field">
											  <div class="btl-label">
												  <?php echo $field->label ;?>
											  </div>
											  <div class="btl-input"><?php echo $field->input;?></div>
										  </div>
									<?php endif;?>
								<?php endforeach;?>
							<?php endif;?>
						<?php endforeach;?>
						
						<!-- show captcha -->
						<?php if($enabledCaptcha == 'recaptcha'):?>
							<?php if ($captchaField): ?>
								<div class="btl-field">
									<div class="btl-label">
										<?php echo $captchaField->label ;?>
									</div>
									<div class="btl-input" style='height:150px;'><div id="btrecaptcha"><?php echo $captchaField->input;?></div></div>
								</div>
							<?php endif;?>
						<?php elseif($enabledCaptcha == 2):?>
							<div class="btl-field">
								<div class="btl-label"><?php echo JText::_( 'MOD_BT_CAPTCHA' ); ?></div>
								<div  id="recaptcha"><?php echo $reCaptcha;?></div>
							</div>
						<?php endif;?>
						
						<div class="btl-buttonsubmit">						
							<button type="submit" class="btl-buttonsubmit validate">
								<?php echo JText::_('JREGISTER');?>							
							</button>
							<!-- <input type="hidden" name="option" value="com_users" /> --> 
							<input type="hidden" name="bttask" value="register" /> 
							<?php echo JHtml::_('form.token');?>
						</div>
					</div>
					</div>
			</form>
			
			<!-- if  integrated any component -->
			<?php }else{ ?>
				<input type="hidden" name="integrated" value="<?php echo $linkOption?>" value="no" id="btl-integrated"/>
			<?php }?>
		</div>
						
		<?php } ?>
	<?php } ?>
	
	</div>
	<div class="clear"></div>
</div>

<script type="text/javascript">
/*<![CDATA[*/
var btlOpt = 
{
	BT_AJAX					:'<?php echo addslashes($ajaxLink); ?>',
	BT_RETURN				:'<?php echo addslashes($return_decode); ?>',
	RECAPTCHA				:'<?php echo $enabledCaptcha ;?>',
	LOGIN_TAGS				:'<?php echo $loginTag?>',
	REGISTER_TAGS			:'<?php echo $registerTag?>',
	EFFECT					:'<?php echo $effect?>',
	ALIGN					:'<?php echo $align?>',
	BG_COLOR				:'<?php echo $bgColor ;?>',
	MOUSE_EVENT				:'<?php echo $params->get('mouse_event','click') ;?>',
	TEXT_COLOR				:'<?php echo $textColor;?>',
	LB_SIZE					:'<?php echo $params->get('loginbox_size');?>',
	RB_SIZE					:'<?php echo $params->get('registrationbox_size');?>'
}
if(btlOpt.ALIGN == "center"){
	BTLJ(".btl-panel").css('textAlign','center');
}else{
	BTLJ(".btl-panel").css('float',btlOpt.ALIGN);
}
BTLJ("input.btl-buttonsubmit,button.btl-buttonsubmit").css({"color":btlOpt.TEXT_COLOR,"background":btlOpt.BG_COLOR});
BTLJ("#btl .btl-panel > span").css({"color":btlOpt.TEXT_COLOR,"background-color":btlOpt.BG_COLOR,"border":btlOpt.TEXT_COLOR});
/*]]>*/
</script>
