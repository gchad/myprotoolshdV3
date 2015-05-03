<?php
/**
 * @version		1.6.7
 * @package		Joomla
 * @subpackage	Membership Pro
 * @author  Tuan Pham Ngoc
* @copyright	Copyright (C) 2012 - 2014 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die ;
JHtml::_('behavior.modal', 'a.osm-modal');
$selectedState = '';
?>
<script type="text/javascript">
	var siteUrl = '<?php echo OSMembershipHelper::getSiteUrl();  ?>';
</script>
<?php
OSMembershipHelperJquery::validateForm();
switch($this->action) 
{
	case 'upgrade' :
		$headerText = JText::_('OSM_SUBSCRIION_UPGRADE_FORM_HEADING');
		break ;			 
	case 'renew' :
		$headerText = JText::_('OSM_SUBSCRIION_RENEW_FORM_HEADING');
		break ;
	default :
		$headerText = JText::_('OSM_SUBSCRIPTION_FORM_HEADING') ;
		break ;
}	
$headerText = str_replace('[PLAN_TITLE]', $this->plan->title, $headerText) ;
?>
<div id="osm-singup-page" class="osm-container row-fluid">
<h1 class="osm-page-title"><?php echo $headerText; ?></h1>
<?php												
if (strlen($this->message)) 
{
?>
	<div class="osm-message clearfix"><?php echo $this->message; ?></div>
<?php	    
}
if (!$this->userId && $this->config->registration_integration && $this->config->show_login_box_on_subscribe_page) 
{
	$actionUrl = JRoute::_('index.php?option=com_users&task=user.login');	
	$returnUrl = JUri::getInstance()->toString();
?>
<form method="post" action="<?php echo $actionUrl ; ?>" name="osm_login_form" id="osm_login_form" autocomplete="off" class="form form-horizontal">
	<h2 class="osm-heading"><?php echo JText::_('OSM_EXISTING_USER_LOGIN'); ?></h2>
	<div class="control-group">
		<label class="control-label" for="username">
			<?php echo  JText::_('JGLOBAL_EMAIL') ?><span class="required">*</span>
		</label>
		<div class="controls">
			<input type="text" name="username" id="username" required class="input-large validate[required]" value=""/>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="password">
			<?php echo  JText::_('OSM_PASSWORD') ?><span class="required">*</span>
		</label>
		<div class="controls">
			<input type="password" id="password" name="password" required class="input-large validate[required" value="" />
		</div>
	</div>
	<div class="control-group">
		<div class="controls">
			<input type="submit" value="<?php echo JText::_('OSM_LOGIN'); ?>" class="button btn btn-primary" />
		</div>
	</div>
	<h2 class="eb-heading"><?php echo JText::_('OSM_NEW_USER_REGISTER'); ?></h2>
	<input type="hidden" name="remember" value="1" />
	<input type="hidden" name="return" value="<?php echo base64_encode($returnUrl) ; ?>" />
	<input type="hidden" name="registration_integration" value="<?php echo $this->config->registration_integration; ?>">
	<?php echo JHtml::_( 'form.token' ); ?>		                	
</form>	
<?php	
}
?>			
<form method="post" name="os_form" id="os_form" action="<?php echo JRoute::_('index.php?option=com_osmembership&task=process_subscription&Itemid='.$this->Itemid, false, $this->config->use_https ? 1 : 0); ?>" enctype="multipart/form-data" autocomplete="off" class="form form-horizontal">
	<h3 class="osm-heading"><?php echo JText::_('OSM_ACCOUNT_INFORMATION');?></h3>
	<?php
	if (!$this->userId && $this->config->registration_integration)
	{
		$params = JComponentHelper::getParams('com_users');
		$minimumLength = $params->get('minimum_length', 4);
	    ($minimumLength) ? $minSize = ",minSize[$minimumLength]" : $minSize = "";
	    if(version_compare(JVERSION, '3.1.2', 'ge'))
	    {
			$passwordValidation = ',ajax[ajaxValidatePassword]';
		}
		else 
		{
			$passwordValidation = '';
		}
	?>
	<div class="control-group">
		<label class="control-label" for="password1">
			<?php echo  JText::_('OSM_PASSWORD') ?>
			<span class="required">*</span>
		</label>
		<div class="controls">
			<input value="" class="validate[required<?php echo $minSize.$passwordValidation;?>] text-input osm_inputbox inputbox" type="password" name="password1" id="password1" autocomplete="off"/>
		</div>		
	</div>		
	<div class="control-group">
		<label class="control-label" for="password2">
			<?php echo  JText::_('OSM_RETYPE_PASSWORD') ?>
			<span class="required">*</span>
		</label>
		<div class="controls">
			<input value="" class="validate[required,equals[password1]] text-input osm_inputbox inputbox" type="password" name="password2" id="password2" />
		</div>		
	</div>						
    <?php	
    }
    $fields = $this->form->getFields();
    if (isset($fields['state']))
    {
    	$selectedState = $fields['state']->value;
    }
    foreach ($fields as $field)
    {    	
    	echo $field->getControlGroup();    						    										
    }				    		    		    	
	if ($this->fees['amount'] > 0 || $this->plan->recurring_subscription) 
	{
	?>
		<h3 class="osm-heading"><?php echo JText::_('OSM_PAYMENT_INFORMATION');?></h3>		
		<?php 
			if ($this->plan->recurring_subscription) 
			{
				if ($this->plan->trial_duration > 0) 
				{
				?>
					<div class="control-group">
						<label class="control-label">
							<?php echo JText::_('OSM_TRIAL_DURATION_PRICE'); ?>		
						</label>
						<div class="controls">
							<?php 
								if ($this->config->currency_position == 0) 
								{
								?>
									<div class="input-prepend inline-display">
										<span class="add-on"><?php echo $this->config->currency_symbol;?></span>
										<input id="trial_amount" type="text" readonly="readonly" class="input-small" value="<?php echo OSMembershipHelper::formatAmount($this->fees['trial_amount'], $this->config); ?>" />
									</div>
								<?php		
								} 
								else 
								{
								?>
									<div class="input-append inline-display">										
										<input id="trial_amount" type="text" readonly="readonly" class="input-small" value="<?php echo OSMembershipHelper::formatAmount($this->fees['trial_amount'], $this->config); ?>" />
										<span class="add-on"><?php echo $this->config->currency_symbol;?></span>
									</div>
								<?php										
								}
							?>
							<div class="explain-text inline-display"><?php echo $this->trialPeriorText; ?></div>
						</div>
					</div>															
				<?php
					if ($this->config->enable_coupon)
					{
					?>
					<div class="control-group">
						<label class="control-label">
							<?php echo JText::_('OSM_TRIAL_DURATION_DISCOUNT'); ?>		
						</label>
						<div class="controls">
							<?php 
								if ($this->config->currency_position == 0) 
								{
								?>
									<div class="input-prepend">
										<span class="add-on"><?php echo $this->config->currency_symbol;?></span>
										<input id="trial_discount_amount" type="text" readonly="readonly" class="input-small" value="<?php echo OSMembershipHelper::formatAmount($this->fees['trial_discount_amount'], $this->config); ?>" />
									</div>
								<?php		
								} 
								else 
								{
								?>
									<div class="input-append">										
										<input id="trial_discount_amount" type="text" readonly="readonly" class="input-small" value="<?php echo OSMembershipHelper::formatAmount($this->fees['trial_discount_amount'], $this->config); ?>" />
										<span class="add-on"><?php echo $this->config->currency_symbol;?></span>
									</div>
								<?php										
								}
							?>							
						</div>
					</div>								
					<?php	
					}
					if ($this->plan->tax_rate > 0)
					{
						$trialTaxAmount = $this->fees['trial_tax_amount'];	
					?>
					<div class="control-group">
						<label class="control-label">
							<?php echo JText::_('OSM_TRIAL_TAX_AMOUNT'); ?>		
						</label>
						<div class="controls">
							<?php 
								if ($this->config->currency_position == 0) 
								{
								?>
									<div class="input-prepend">
										<span class="add-on"><?php echo $this->config->currency_symbol;?></span>
										<input id="trial_tax_amount" type="text" readonly="readonly" class="input-small" value="<?php echo OSMembershipHelper::formatAmount($trialTaxAmount, $this->config); ?>" />
									</div>
								<?php		
								} 
								else 
								{
								?>
									<div class="input-append">										
										<input id="trial_tax_amount" type="text" readonly="readonly" class="input-small" value="<?php echo OSMembershipHelper::formatAmount($trialTaxAmount, $this->config); ?>" />
										<span class="add-on"><?php echo $this->config->currency_symbol;?></span>
									</div>
								<?php										
								}
							?>
						</div>
					</div>									
					<?php	
					} 
					else 
					{
						$trialTaxAmount = 0;
					}
					if ($this->config->enable_coupon || $trialTaxAmount > 0) 
					{
					?>
					<div class="control-group">
						<label class="control-label">
							<?php echo JText::_('OSM_GROSS_TRIAL_AMOUNT'); ?>		
						</label>
						<div class="controls">
							<?php 
								if ($this->config->currency_position == 0) 
								{
								?>
									<div class="input-prepend inline-display">
										<span class="add-on"><?php echo $this->config->currency_symbol;?></span>
										<input id="trial_gross_amount" type="text" readonly="readonly" class="input-small" value="<?php echo OSMembershipHelper::formatAmount($this->fees['trial_gross_amount'], $this->config); ?>" />
									</div>
								<?php		
								} 
								else 
								{
								?>
									<div class="input-append inline-display">										
										<input id="trial_gross_amount" type="text" readonly="readonly" class="input-small" value="<?php echo OSMembershipHelper::formatAmount($this->fees['trial_gross_amount'], $this->config); ?>" />
										<span class="add-on"><?php echo $this->config->currency_symbol;?></span>
									</div>
								<?php										
								}
							?>
							<div class="explain-text inline-display"><?php echo $this->trialPeriorText; ?></div>
						</div>
					</div>								
					<?php		
					}
				}
				?>				
				<div class="control-group">
					<label class="control-label">
						<?php echo JText::_('OSM_REGULAR_PRICE'); ?>		
					</label>
					<div class="controls">
						<?php 
							if ($this->config->currency_position == 0) {
							?>
								<div class="input-prepend inline-display">
									<span class="add-on"><?php echo $this->config->currency_symbol;?></span>
									<input id="regular_amount" type="text" readonly="readonly" class="input-small" value="<?php echo OSMembershipHelper::formatAmount($this->fees['regular_amount'], $this->config); ?>" />
								</div>
							<?php		
							} else {
							?>
								<div class="input-append inline-display">										
									<input id="regular_amount" type="text" readonly="readonly" class="input-small" value="<?php echo OSMembershipHelper::formatAmount($this->fees['regular_amount'], $this->config); ?>" />
									<span class="add-on"><?php echo $this->config->currency_symbol;?></span>
								</div>
							<?php										
							}
						?>
						<div class="explain-text inline-display"><?php echo $this->regularPeriorText; ?></div>
					</div>
				</div>										
			<?php
				if ($this->config->enable_coupon) 
				{
				?>
					<div class="control-group">
						<label class="control-label">
							<?php echo JText::_('OSM_REGULAR_DISCOUNT'); ?>		
						</label>
						<div class="controls">
							<?php 
								if ($this->config->currency_position == 0) 
								{
								?>
									<div class="input-prepend">
										<span class="add-on"><?php echo $this->config->currency_symbol;?></span>
										<input id="regular_discount_amount" type="text" readonly="readonly" class="input-small" value="<?php echo OSMembershipHelper::formatAmount($this->fees['regular_discount_amount'], $this->config); ?>" />
									</div>
								<?php		
								} 
								else 
								{
								?>
									<div class="input-append">										
										<input id="regular_discount_amount" type="text" readonly="readonly" class="input-small" value="<?php echo OSMembershipHelper::formatAmount($this->fees['regular_discount_amount'], $this->config); ?>" />
										<span class="add-on"><?php echo $this->config->currency_symbol;?></span>
									</div>
								<?php										
								}
							?>
						</div>
					</div>									
				<?php	
				}
				if ($this->fees['regular_tax_amount'] > 0)
				{
				?>
					<div class="control-group">
						<label class="control-label">
							<?php echo JText::_('OSM_REGULAR_TAX'); ?>		
						</label>
						<div class="controls">
							<?php 
								if ($this->config->currency_position == 0) 
								{
								?>
									<div class="input-prepend">
										<span class="add-on"><?php echo $this->config->currency_symbol;?></span>
										<input id="regular_tax_amount" type="text" readonly="readonly" class="input-small" value="<?php echo OSMembershipHelper::formatAmount($this->fees['regular_tax_amount'], $this->config); ?>" />
									</div>
								<?php		
								} 
								else 
								{
								?>
									<div class="input-append">										
										<input id="regular_tax_amount" type="text" readonly="readonly" class="input-small" value="<?php echo OSMembershipHelper::formatAmount($this->fees['regular_tax_amount'], $this->config); ?>" />
										<span class="add-on"><?php echo $this->config->currency_symbol;?></span>
									</div>
								<?php										
								}
							?>
						</div>
					</div>							
				<?php	
				}
				if ($this->config->enable_coupon || $this->fees['regular_tax_amount'] > 0) 
				{
				?>
					<div class="control-group">
						<label class="control-label">
							<?php echo JText::_('OSM_REGULAR_GROSS_AMOUNT'); ?>		
						</label>
						<div class="controls">
							<?php 
								if ($this->config->currency_position == 0) 
								{
								?>
									<div class="input-prepend inline-display">
										<span class="add-on"><?php echo $this->config->currency_symbol;?></span>
										<input id="regular_gross_amount" type="text" readonly="readonly" class="input-small" value="<?php echo OSMembershipHelper::formatAmount($this->fees['regular_gross_amount'], $this->config); ?>" />
									</div>
								<?php		
								} 
								else 
								{
								?>
									<div class="input-append inline-display">										
										<input id="regular_gross_amount" type="text" readonly="readonly" class="input-small" value="<?php echo OSMembershipHelper::formatAmount($this->fees['regular_gross_amount'], $this->config); ?>" />
										<span class="add-on"><?php echo $this->config->currency_symbol;?></span>
									</div>
								<?php										
								}
							?>
							<div class="explain-text inline-display"><?php echo $this->regularPeriorText; ?></div>
						</div>
					</div>								
				<?php	
				}	
													
			} 
			else 
			{
			?>
				<div class="control-group">
					<label class="control-label">
						<?php echo JText::_('OSM_PRICE'); ?>		
					</label>
					<div class="controls">
						<?php 
							if ($this->config->currency_position == 0) 
							{
							?>
								<div class="input-prepend">
									<span class="add-on"><?php echo $this->config->currency_symbol;?></span>
									<input id="amount" type="text" readonly="readonly" class="input-small" value="<?php echo OSMembershipHelper::formatAmount($this->fees['amount'], $this->config); ?>" />
								</div>
							<?php		
							} 
							else 
							{
							?>
								<div class="input-append">										
									<input id="amount" type="text" readonly="readonly" class="input-small" value="<?php echo OSMembershipHelper::formatAmount($this->fees['amount'], $this->config); ?>" />
									<span class="add-on"><?php echo $this->config->currency_symbol;?></span>
								</div>
							<?php										
							}
						?>
					</div>
				</div>								
			<?php
				if ($this->config->enable_coupon) 
				{
				?>
				<div class="control-group">
					<label class="control-label">
						<?php echo JText::_('OSM_DISCOUNT'); ?>		
					</label>
					<div class="controls">
						<?php 
							if ($this->config->currency_position == 0) 
							{
							?>
								<div class="input-prepend">
									<span class="add-on"><?php echo $this->config->currency_symbol;?></span>
									<input id="discount_amount" type="text" readonly="readonly" class="input-small" value="<?php echo OSMembershipHelper::formatAmount($this->fees['discount_amount'], $this->config); ?>" />
								</div>
							<?php		
							} 
							else 
							{
							?>
								<div class="input-append">										
									<input id="discount_amount" type="text" readonly="readonly" class="input-small" value="<?php echo OSMembershipHelper::formatAmount($this->fees['discount_amount'], $this->config); ?>" />
									<span class="add-on"><?php echo $this->config->currency_symbol;?></span>
								</div>
							<?php										
							}
						?>
					</div>
				</div>								
				<?php	
				}
				if ($this->fees['tax_amount'] > 0)
				{
				?>
				<div class="control-group">
					<label class="control-label">
						<?php echo JText::_('OSM_TAX'); ?>		
					</label>
					<div class="controls">
						<?php 
							if ($this->config->currency_position == 0) 
							{
							?>
								<div class="input-prepend">
									<span class="add-on"><?php echo $this->config->currency_symbol;?></span>
									<input id="tax_amount" type="text" readonly="readonly" class="input-small" value="<?php echo OSMembershipHelper::formatAmount($this->fees['tax_amount'], $this->config); ?>" />
								</div>
							<?php		
							} 
							else 
							{
							?>
								<div class="input-append">										
									<input id="tax_amount" type="text" readonly="readonly" class="input-small" value="<?php echo OSMembershipHelper::formatAmount($this->fees['tax_amount'], $this->config); ?>" />
									<span class="add-on"><?php echo $this->config->currency_symbol;?></span>
								</div>
							<?php										
							}
						?>
					</div>
				</div>							
				<?php	
				}
				if ($this->config->enable_coupon || $this->fees['tax_amount'] > 0) 
				{
				?>
				<div class="control-group">
					<label class="control-label">
						<?php echo JText::_('OSM_GROSS_AMOUNT'); ?>		
					</label>
					<div class="controls">
						<?php 
							if ($this->config->currency_position == 0) 
							{
							?>
								<div class="input-prepend">
									<span class="add-on"><?php echo $this->config->currency_symbol;?></span>
									<input id="gross_amount" type="text" readonly="readonly" class="input-small" value="<?php echo OSMembershipHelper::formatAmount($this->fees['gross_amount'], $this->config); ?>" />
								</div>
							<?php		
							} 
							else 
							{
							?>
								<div class="input-append">										
									<input id="gross_amount" type="text" readonly="readonly" class="input-small" value="<?php echo OSMembershipHelper::formatAmount($this->fees['gross_amount'], $this->config); ?>" />
									<span class="add-on"><?php echo $this->config->currency_symbol;?></span>
								</div>
							<?php										
							}
						?>
					</div>
				</div>						
				<?php	
				}	
			}							
			if ($this->config->enable_coupon)
			{
			?>
			<div class="control-group">
				<label class="control-label">
					<?php echo JText::_('OSM_COUPON'); ?>		
				</label>
				<div class="controls">
					<input type="text" class="input-medium" name="coupon_code" id="coupon_code" value="<?php echo JRequest::getVar('coupon_code');?>" onchange="validateCoupon();" />
					<span class="invalid" id="coupon_validate_msg" style="display: none;"><?php echo JText::_('OSM_INVALID_COUPON'); ?></span>
				</div>
			</div>			    				
    		<?php	
    		}    								
			if (count($this->methods) > 1) 
			{
			?>
			<div class="control-group payment_information" id="payment_method_container">				
				<label class="control-label" for="payment_method">
					<?php echo JText::_('OSM_PAYMENT_OPTION'); ?>
					<span class="required">*</span>
				</label>																	
				<div class="controls">
					<ul id="osm-payment-method-list">					
					<?php
						$method = null ;
						for ($i = 0 , $n = count($this->methods); $i < $n; $i++) 
						{
							$paymentMethod = $this->methods[$i];
							if ($paymentMethod->getName() == $this->paymentMethod) 
							{
								$checked = ' checked="checked" ';
								$method = $paymentMethod ;
							}										
							else
							{								 
								$checked = '';
							}		
						?>
						<li class="osm-payment-method-item">
							<input onclick="changePaymentMethod();" id="osm-payment-method-item-<?php echo $i; ?>" type="radio" name="payment_method" value="<?php echo $paymentMethod->getName(); ?>" <?php echo $checked; ?> />
							<label for="osm-payment-method-item-<?php echo $i; ?>"><?php echo JText::_($paymentMethod->title) ; ?></label>							
						</li>						
						<?php		
						}	
					?>
					</ul>
				</div>						
			</div>				
		<?php					
		} 
		else 
		{
			$method = $this->methods[0] ;
		?>
			<div class="control-group payment_information" id="payment_method_container">
				<label class="control-label" for="payment_method">
					<?php echo JText::_('OSM_PAYMENT_OPTION'); ?>					
				</label>					
				<div class="controls">
					<?php echo JText::_($method->title); ?>
				</div>
			</div>
		<?php					
		}																			
		if ($method->getCreditCard()) 
		{
			$style = '' ;	
		} 
		else 
		{
			$style = 'style = "display:none"';
		}			
		?>			
		<div class="control-group payment_information" id="tr_card_number" <?php echo $style; ?>>
			<label class="control-label"><?php echo  JText::_('AUTH_CARD_NUMBER'); ?><span class="required">*</span></label>
			<div class="controls">
				<input type="text" name="x_card_num" class="validate[required,creditCard] osm_inputbox inputbox" value="<?php echo JRequest::getVar('x_card_num', null,'post'); ?>" size="20" />
			</div>					
		</div>
		<div class="control-group payment_information" id="tr_exp_date" <?php echo $style; ?>>
			<label class="control-label">
				<?php echo JText::_('AUTH_CARD_EXPIRY_DATE'); ?><span class="required">*</span>
			</label>
			<div class="controls">					
				<?php echo $this->lists['exp_month'] .'  /  '.$this->lists['exp_year'] ; ?>
			</div>					
		</div>
		<div class="control-group payment_information" id="tr_cvv_code" <?php echo $style; ?>>
			<label class="control-label">
				<?php echo JText::_('AUTH_CVV_CODE'); ?><span class="required">*</span>
			</label>
			<div class="controls">
				<input type="text" name="x_card_code" class="validate[required,custom[number]] osm_inputbox input-small" value="<?php echo JRequest::getVar('x_card_code', null,'post'); ?>" size="20" />
			</div>					
		</div>
		<?php
			if ($method->getCardType()) 
			{
				$style = '' ;
			} 
			else 
			{
				$style = ' style = "display:none;" ' ;										
			}
		?>
			<div class="control-group payment_information" id="tr_card_type" <?php echo $style; ?>>
				<label class="control-label">
					<?php echo JText::_('OSM_CARD_TYPE'); ?><span class="required">*</span>
				</label>
				<div class="controls">
					<?php echo $this->lists['card_type'] ; ?>
				</div>						
			</div>					
		<?php
			if ($method->getCardHolderName()) 
			{
				$style = '' ;
			} 
			else 
			{
				$style = ' style = "display:none;" ' ;										
			}
		?>
			<div class="control-group payment_information" id="tr_card_holder_name" <?php echo $style; ?>>
				<label class="control-label">
					<?php echo JText::_('OSM_CARD_HOLDER_NAME'); ?><span class="required">*</span>
				</label>
				<div class="controls">
					<input type="text" name="card_holder_name" class="validate[required] osm_inputbox inputbox"  value="<?php echo JRequest::getVar('card_holder_name', null,'post'); ?>" size="40" />
				</div>						
			</div>
		<?php									
			if ($method->getName() == 'os_echeck') 
			{
				$style = '';												
			} 
			else 
			{
				$style = ' style = "display:none;" ' ;
			}
		?>
		    <div class="control-group payment_information" id="tr_bank_rounting_number" <?php echo $style; ?>>
		      <label class="control-label"><?php echo JText::_('OSM_BANK_ROUTING_NUMBER'); ?><span class="required">*</span></label>
		      <div class="controls"><input type="text" name="x_bank_aba_code" class="osm_inputbox inputbox"  value="<?php echo JRequest::getVar('x_bank_aba_code', null,'post'); ?>" size="40" onKeyUp="" /></div>
		    </div>
		    <div class="control-group payment_information" id="tr_bank_account_number" <?php echo $style; ?>>
		      <label class="control-label"><?php echo JText::_('OSM_BANK_ACCOUNT_NUMBER'); ?><span class="required">*</span></label>
		      <div class="controls"><input type="text" name="x_bank_acct_num" class="osm_inputbox inputbox"  value="<?php echo JRequest::getVar('x_bank_acct_num', null,'post'); ?>" size="40" onKeyUp="" /></div>
		    </div>
		    <div class="control-group payment_information" id="tr_bank_account_type" <?php echo $style; ?>>
		      <label class="control-label"><?php echo JText::_('OSM_BANK_ACCOUNT_TYPE'); ?><span class="required">*</span></label>
		      <div class="controls"><?php echo $this->lists['x_bank_acct_type']; ?></div>
		    </div>
		    <div class="control-group payment_information" id="tr_bank_name" <?php echo $style; ?>>
		      <label class="control-label"><?php echo JText::_('OSM_BANK_NAME'); ?><span class="required">*</span></label>
		      <div class="controls"><input type="text" name="x_bank_name" class="osm_inputbox inputbox"  value="<?php echo JRequest::getVar('x_bank_name', null,'post'); ?>" size="40" /></div>
		    </div>
		    <div class="control-group payment_information" id="tr_bank_account_holder" <?php echo $style; ?>>
		      <label class="control-label"><?php echo JText::_('OSM_ACCOUNT_HOLDER_NAME'); ?><span class="required">*</span></label>
		      <div class="controls"><input type="text" name="x_bank_acct_name" class="osm_inputbox inputbox"  value="<?php echo JRequest::getVar('x_bank_acct_name', null,'post'); ?>" size="40" /></div>
		    </div>	
		<?php			
		if ($this->idealEnabled) 
		{
	        if ($method->getName() == 'os_ideal') 
			{
				$style = '' ;
			} 
			else 
			{
				$style = ' style = "display:none;" ' ;
			}	
		?>
			<div class="control-group payment_information" id="tr_bank_list" <?php echo $style; ?>>
				<label class="control-label">
					<?php echo JText::_('OSM_BANK_LIST'); ?><span class="required">*</span>
				</label>
				<div class="controls">
					<?php echo $this->lists['bank_id'] ; ?>
				</div>
			</div>
		<?php	
	    }		
	}											        	
	if ($this->config->accept_term ==1) 
	{
		$articleId = $this->config->article_id ;
		$db = JFactory::getDbo() ;
		$sql = 'SELECT * FROM #__content WHERE id='.$articleId ;
		$db->setQuery($sql) ;
		$rowArticle = $db->loadObject() ;			
		$catId = $rowArticle->catid ;						
		require_once JPATH_ROOT.'/components/com_content/helpers/route.php' ;			
		if ($this->config->fix_terms_and_conditions_popup) 
		{
			$termLink = ContentHelperRoute::getArticleRoute($articleId, $catId).'&format=html' ;
			$extra = ' target="_blank" ';
		} 
		else 
		{
			$termLink = ContentHelperRoute::getArticleRoute($articleId, $catId).'&tmpl=component&format=html' ;
			$extra = ' class="osm-modal" ' ;
		}																    				 
	?>
	<div class="control-group">		
		<input type="checkbox" name="accept_term" value="1" class="validate[required] osm_inputbox inputbox" />
		<strong><?php echo JText::_('OSM_ACCEPT'); ?>&nbsp;<a href="<?php echo JRoute::_($termLink); ?>" <?php echo $extra ; ?> rel="{handler: 'iframe', size: {x: 700, y: 500}}"><?php echo JText::_('OSM_TERM_AND_CONDITION'); ?></a></strong>			
	</div>
	<?php	
	}
	if ($this->config->enable_captcha) {
	?>
		<div class="control-group">
			<label class="control-label">
				<?php echo JText::_('OSM_CAPTCHA'); ?><span class="required">*</span>
			</label>
			<div class="controls">
				<?php echo $this->captcha;?>							
			</div>
		</div>	
	<?php	
	}		
	?>										
	<div class="form-actions">	
		<input type="submit" class="btn btn-primary" name="btnSubmit" id="btn-submit" value="<?php echo  JText::_('OSM_PROCESS_SUBSCRIPTION') ;?>">
		<img id="ajax-loading-animation" src="<?php echo JUri::base();?>media/com_osmembership/ajax-loadding-animation.gif" style="display: none;"/>								
	</div>								
<?php
	if (count($this->methods) == 1) 
	{
	?>
		<input type="hidden" name="payment_method" value="<?php echo $this->methods[0]->getName(); ?>" />
	<?php	
	}		
?>
	<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
	<input type="hidden" name="plan_id" value="<?php echo $this->plan->id ; ?>" />
	<input type="hidden" name="option" value="com_osmembership" />
	<input type="hidden" name="act" value="<?php echo $this->action ; ?>" />		
	<input type="hidden" name="renew_option_id" value="<?php echo $this->renewOptionId ; ?>" />
	<input type="hidden" name="upgrade_option_id" value="<?php echo $this->upgradeOptionId ; ?>" />									
	<script type="text/javascript">
		OSM.jQuery(function($){
			$(document).ready(function(){
				if($('input[name^=registration_integration]').length)
				{
					OSMVALIDATEFORM("#osm_login_form");
				}
				OSMVALIDATEFORM("#os_form");
				buildStateField('state', 'country', '<?php echo $selectedState; ?>');
			})
			var planId = <?php echo $this->plan->id; ?>;
			var siteUrl = '<?php echo OSMembershipHelper::getSiteUrl();  ?>';
			var invaliEmailMsg = "<?php echo JText::_('OSM_INVALID_EMAIL_ENTERED'); ?>";
			<?php
				os_payments::writeJavascriptObjects();
			?>
		});
		var planId = <?php echo $this->plan->id; ?>;
		var siteUrl = '<?php echo OSMembershipHelper::getSiteUrl();  ?>';
		var invaliEmailMsg = "<?php echo JText::_('OSM_INVALID_EMAIL_ENTERED'); ?>";		
		<?php
			os_payments::writeJavascriptObjects();
		?>		
	</script>		
	<?php echo JHtml::_( 'form.token' ); ?>
</form>
</div>