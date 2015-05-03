<?php
/**
 * @package     Responsive Scroll Triggered Box
 * @subpackage  com_rstbox
 *
 * @copyright   Copyright (C) 2014 Tassos Marinos - http://www.tassos.gr
 * @license     GNU General Public License version 2 or later; see http://www.tassos.gr/license
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.formvalidation');

if (RstboxHelper::jVer() == "3") {
    JHtml::_('formbehavior.chosen', 'select');
}

if (RstboxHelper::jVer() == "2.5") {
    $options = false;
    JHtml::_('behavior.tooltip');
    JHtml::_('behavior.keepalive');
    jimport( 'joomla.html.html.tabs' );
}

?>

<script type="text/javascript">
    Joomla.submitbutton = function(task)
    {
        if (task == 'item.cancel' || document.formvalidator.isValid(document.id('adminForm')))
        {
            Joomla.submitform(task, document.getElementById('adminForm'));
        }
    }
</script>

<div class="form-horizontal">
    <form action="<?php echo JRoute::_('index.php?option=com_rstbox&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm">
        
        <?php if (RstboxHelper::jVer() == "3") { ?>
            <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>
            <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('General')); ?>
        <?php } else { ?>
            <?php echo JHtml::_('tabs.start', 'tab_group_id', $options); ?>
            <?php echo JHtml::_('tabs.panel', JText::_('General'), 'panel_1_id'); ?>
        <?php } ?>

        <div class="row-fluid">
            <div class="span9">
                <?php echo RstboxHelper::renderFormFields($this->form->getFieldset("type")) ?>
                <div class="boxtypes">
                    <div class="hide" id="emailform"><?php echo RstboxHelper::renderFormFields($this->form->getFieldset("emailform")) ?></div>
                    <div class="hide" id="custom"><?php echo RstboxHelper::renderFormFields($this->form->getFieldset("custom")) ?></div>
                    <div class="hide" id="module"><?php echo RstboxHelper::renderFormFields($this->form->getFieldset("module")) ?></div>
                </div>
            </div>

            <div class="span3 form-vertical paddingLeft">
                <h4>Details</h4>
                <hr>
                <?php echo RstboxHelper::renderFormFields($this->form->getFieldset("general")) ?>
            </div>
        </div>
        <?php if (RstboxHelper::jVer() == "3") { ?><?php echo JHtml::_('bootstrap.endTab'); ?><?php } ?>
        <div class="clr"></div>
        
        <!-- Trigger Tab -->
        <?php if (RstboxHelper::jVer() == "3") { ?>
            <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'trigger', JText::_('Trigger')); ?>
        <?php } else { ?>
            <?php echo JHtml::_('tabs.panel', JText::_('Trigger'), 'trigger'); ?>
        <?php } ?>

        <div class="row-fluid">
            <div class="span6"> <?php echo RstboxHelper::renderFormFields($this->form->getFieldset("item")) ?></div>
        </div>
        <?php if (RstboxHelper::jVer() == "3") { ?><?php echo JHtml::_('bootstrap.endTab'); ?><?php } ?>
        <div class="clr"></div>

        <!-- Appearance Tab -->
        <?php if (RstboxHelper::jVer() == "3") { ?>
            <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'appearance', JText::_('Appearance')); ?>
        <?php } else { ?>
            <?php echo JHtml::_('tabs.panel', JText::_('Appearance'), 'appearance'); ?>
        <?php } ?>

        <div class="row-fluid">
            <div class="span6"><?php echo RstboxHelper::renderFormFields($this->form->getFieldset("appearance1")) ?></div>
            <div class="span6"><?php echo RstboxHelper::renderFormFields($this->form->getFieldset("appearance2")) ?></div>
        </div>
        <?php if (RstboxHelper::jVer() == "3") { ?><?php echo JHtml::_('bootstrap.endTab'); ?><?php } ?>
        <div class="clr"></div>

        <!-- Publishing Assignments Tab -->
        <?php if (RstboxHelper::jVer() == "3") { ?>
            <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishingAssignments', JText::_('Publishing Assignments')); ?>
        <?php } else { ?>
            <?php echo JHtml::_('tabs.panel', JText::_('Publishing Assignments'), 'publishingAssignments'); ?>
        <?php } ?>

        <div class="row-fluid">
            <div class="span6">
                <div class="well">
                    <?php echo RstboxHelper::renderField($this->form->getField('prm_allmenus')) ?>
                    <?php echo RstboxHelper::renderField($this->form->getField('menuitems')) ?>
                </div>
                <div class="well">
                    <?php echo RstboxHelper::renderField($this->form->getField('accesslevel')) ?>
                </div>
                <div class="well">
                    <?php echo RstboxHelper::renderField($this->form->getField('prm_assign_devices')) ?>
                    <?php echo RstboxHelper::renderField($this->form->getField('prm_assign_devices_list')) ?>
                </div>
                <div class="well">
                    <?php echo RstboxHelper::renderField($this->form->getField('prm_assign_lang')) ?>
                    <?php echo RstboxHelper::renderField($this->form->getField('prm_assign_lang_list')) ?>
                </div>
            </div>
        </div>
        <?php if (RstboxHelper::jVer() == "3") { ?><?php echo JHtml::_('bootstrap.endTab'); ?><?php } ?>
        <div class="clr"></div>

        <!-- Advanced Tab -->
        <?php if (RstboxHelper::jVer() == "3") { ?>
            <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'advanced', JText::_('Advanced')); ?>
        <?php } else { ?>
            <?php echo JHtml::_('tabs.panel', JText::_('Advanced'), 'advanced'); ?>
        <?php } ?>

        <div class="row-fluid">
            <div class="span6"><?php echo RstboxHelper::renderFormFields($this->form->getFieldset("advanced")) ?></div>
        </div>
        <?php if (RstboxHelper::jVer() == "3") { ?><?php echo JHtml::_('bootstrap.endTab'); ?><?php } ?>
        <div class="clr"></div>


        <input type="hidden" name="task" value="item.edit" />
        <?php echo JHtml::_('form.token'); ?>

        <?php if (RstboxHelper::jVer() == "3") { ?>
            <?php echo JHtml::_('bootstrap.endTabSet'); ?>
        <?php } else { ?>
            <?php echo JHtml::_('tabs.end'); ?>
        <?php } ?>
    </form>
</div>


