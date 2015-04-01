<?php
/**
 * @version     1.0.7
 * @package     com_hellomaps
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      JoomlaForce Team <support@joomlaforce.com> - http://www.joomlaforce.com
 */
 
// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');

$app = JFactory::getApplication();

?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'config.cancel' || document.formvalidator.isValid(document.id('config-form')))
		{
			Joomla.submitform(task, document.getElementById('config-form'));
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_hellomaps'); ?>" method="post" name="adminForm" id="config-form" class="form-validate">
	<div id="j-sidebar-container" class="span2">
    	<?php echo $this->sidebar;?>
    </div>
    <div id="j-main-container" class="span10">
            <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'layout')); ?>
    	
    		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'layout', JText::_('COM_HELLOMAPS_CONFIG_TAB_LAYOUT', true)); ?>
    		<div class="row-fluid">
    			<div class="span6">
    				<div class="form-horizontal well">
    					<legend><?php echo JText::_('COM_HELLOMAPS_CONFIG_BLOCK_SIDEBAR', true);?></legend>
    					<?php echo $this->form->getControlGroups('sidebar'); ?>
    				</div>
    				
    				<div class="form-horizontal well">
    					<legend><?php echo JText::_('COM_HELLOMAPS_CONFIG_BLOCK_NOTICE', true);?></legend>
    					<?php echo $this->form->getControlGroups('notice'); ?>
    				</div>
    			</div>
    			
    			<div class="span6">
    				<div class="form-horizontal well">
    					<legend><?php echo JText::_('COM_HELLOMAPS_CONFIG_BLOCK_SEARCH', true);?></legend>
    					<?php echo $this->form->getControlGroups('search'); ?>
    				</div>
    				<div class="form-horizontal well">
    					<legend><?php echo JText::_('COM_HELLOMAPS_CONFIG_BLOCK_CONTENTS', true);?></legend>
    					<?php echo $this->form->getControlGroups('contents'); ?>
    				</div>
    				<div class="form-horizontal well">
    					<legend><?php echo JText::_('COM_HELLOMAPS_CONFIG_BLOCK_RESULTS', true);?></legend>
    					<?php echo $this->form->getControlGroups('results'); ?>
    				</div>
    				<div class="form-horizontal well">
    					<legend><?php echo JText::_('COM_HELLOMAPS_CONFIG_BLOCK_INFOLINK', true);?></legend>
    					<?php echo $this->form->getControlGroups('infolink'); ?>
    				</div>
    			</div>
    		</div>
    		<?php echo JHtml::_('bootstrap.endTab'); ?>
    		
    		
    		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'map', JText::_('COM_HELLOMAPS_CONFIG_TAB_MAP', true)); ?>
    		<div class="row-fluid">
    			<div class="span6">
    				<div class="form-horizontal well">
    					<legend><?php echo JText::_('COM_HELLOMAPS_CONFIG_BLOCK_MAPTYPE', true);?></legend>
    					<?php echo $this->form->getControlGroups('maptype'); ?>
    				</div>
    				<div class="form-horizontal well">
    					<legend><?php echo JText::_('COM_HELLOMAPS_CONFIG_BLOCK_DIMENSIONS', true);?></legend>
    					<?php echo $this->form->getControlGroups('dimensions'); ?>
    				</div>
    				<div class="form-horizontal well">
    					<legend><?php echo JText::_('COM_HELLOMAPS_CONFIG_BLOCK_INITIALIZE', true);?></legend>
    					<?php echo $this->form->getControlGroups('initialize'); ?>
    				</div>
    			</div>
    			
    			<div class="span6">
    				<div class="form-horizontal well">
    					<legend><?php echo JText::_('COM_HELLOMAPS_CONFIG_BLOCK_CLUSTERING', true);?></legend>
    					<?php echo $this->form->getControlGroups('clustering'); ?>
    				</div>
    				<div class="form-horizontal well">
    					<legend><?php echo JText::_('COM_HELLOMAPS_CONFIG_BLOCK_INFOWINDOW', true);?></legend>
    					<?php echo $this->form->getControlGroups('infowindow'); ?>
    				</div>
    				<div class="form-horizontal well">
    					<legend><?php echo JText::_('COM_HELLOMAPS_CONFIG_BLOCK_EVENTSENABLED', true);?></legend>
    					<?php echo $this->form->getControlGroups('eventsenabled'); ?>
    				</div>
    			</div>
    		</div>
    		<?php echo JHtml::_('bootstrap.endTab'); ?>
    		
    		
    		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'toolbar_buttons', JText::_('COM_HELLOMAPS_CONFIG_TAB_TOOLBAR_BUTTONS', true)); ?>
    		<div class="row-fluid">
    			<div class="span6">
    				<div class="form-horizontal well">
    					<legend><?php echo JText::_('COM_HELLOMAPS_CONFIG_BLOCK_BUTTONSENABLED', true);?></legend>
    					<?php echo $this->form->getControlGroups('buttonsenabled'); ?>
    				</div>
    			</div>
    			
    			<div class="span6">
    				<div class="form-horizontal well">
    					<legend><?php echo JText::_('COM_HELLOMAPS_CONFIG_BLOCK_MOBILEBUTTONS', true);?></legend>
    					<?php echo $this->form->getControlGroups('mobilebuttons'); ?>
    				</div>
    			</div>
    		</div>
    		<?php echo JHtml::_('bootstrap.endTab'); ?>
    		
            <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'jquery', JText::_('COM_HELLOMAPS_CONFIG_TAB_JQUERY', true)); ?>
    		<div class="row-fluid">
    			<div class="span12">
    				<div class="form-horizontal well">
    					<legend><?php echo JText::_('COM_HELLOMAPS_CONFIG_BLOCK_JQUERY', true);?></legend>
    					<?php echo $this->form->getControlGroups('jquerysettings'); ?>
    				</div>
    			</div>
    		</div>
    		<?php echo JHtml::_('bootstrap.endTab'); ?>
    		
    		
    	
    	<?php echo JHtml::_('bootstrap.endTabSet'); ?>
    	
    	<input type="hidden" name="task" value="" />
    	<?php echo JHtml::_('form.token'); ?>
    	<input type="hidden" name="option" value="com_hellomaps" />
        </div>
	
</form>


