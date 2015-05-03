<?php
/**
 * @package     Responsive Scroll Triggered Box
 * @subpackage  com_rstbox
 *
 * @copyright   Copyright (C) 2014 Tassos Marinos - http://www.tassos.gr
 * @license     GNU General Public License version 2 or later; see http://www.tassos.gr/license
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
// load tooltip behavior
JHtml::_('behavior.tooltip');
?>
<form action="<?php echo JRoute::_('index.php?option=com_rstbox'); ?>" method="post" name="adminForm" id="adminForm">
    <table class="adminlist table table-striped">
        <thead>
            <tr>
                <th class="center" width="2%">
                    <?php if (RstboxHelper::jVer() == "3") { ?>
                        <?php echo JHtml::_('grid.checkall'); ?>
                    <?php } else { ?>
                        <input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
                    <?php } ?>
                </th>
                <th class="center" width="7%"><a href="#"><?php echo JText::_('COM_RSTBOX_ITEM_PUBLISHED'); ?></a></th>
                <th class="title"><a href="#"><?php echo JText::_('COM_RSTBOX_ITEM_TITLE'); ?></a></th>
                <th class="center" width="15%"><?php echo JText::_('COM_RSTBOX_ACCESSLEVEL'); ?></th>
                <th class="center" width="15%"><?php echo JText::_('COM_RSTBOX_ITEM_BOX_POSITION'); ?></th>
                <th class="center" width="15%"><?php echo JText::_('COM_RSTBOX_ITEM_TRIGGER'); ?></th>
                <th class="center" width="15%"><?php echo JText::_('COM_RSTBOX_ITEM_ANIMATION'); ?></th>
                <th class="center" width="15%"><?php echo JText::_('COM_RSTBOX_ITEM_COOKIE'); ?></th>
                <th class="center" width="4%"><a href="#"><?php echo JText::_('COM_RSTBOX_ID'); ?></a></th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($this->items)) { ?>
                <?php foreach($this->items as $i => $item): ?>
                    <tr class="row<?php echo $i % 2; ?>">
                        <td class="center"><?php echo JHtml::_('grid.id', $i, $item->id); ?></td>
                        <td class="center"><?php echo JHtml::_('jgrid.published', $item->published, $i, 'items.', true); ?></td>
                        <td>
                            <a href="<?php echo JRoute::_('index.php?option=com_rstbox&task=item.edit&id='.$item->id); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?>">
                                <?php echo RstboxHelper::pretty($this->escape($item->name)); ?>
                                <?php if ($item->testmode) { ?><strong>[Test Mode]</strong> <?php } ?>
                            </a>
                            <div class="small">
                                <?php echo $item->boxtype ?>
                            </div>
                        </td>
                        <td class="center"><?php echo $item->accessleveltitle ?></td>
                        <td class="center"><?php echo RstboxHelper::pretty($item->position) ?></td>
                        <td class="center"><?php echo RstboxHelper::pretty($item->triggermethod) ?></td>
                        <td class="center"><?php echo RstboxHelper::pretty($item->animation) ?></td>
                        <td class="center"><?php echo $item->cookie ?></td>
                        <td class="center"><?php echo $item->id ?></td>
                    </tr>
                <?php endforeach; ?>  
            <?php } else { ?>
                <tr>
                    <td align="center" colspan="9">
                        <div align="center"><?php echo JText::_('COM_RSTBOX_ERROR_NO_BOXES') ?></div>
                    </td>
                </tr>
            <?php } ?>        
        </tbody>
        <tfoot>
			<tr><td colspan="9"><?php echo $this->pagination->getListFooter(); ?></td></tr>
        </tfoot>
    </table>
    <div>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>

<div id="rstboxfooter" class="center">
    <?php echo JText::_('COM_RSTBOX_DESC')?><br>
    &copy; 2014 Tassos Marinos All Rights Reserved<br>
    Need support? Drop me an e-mail <a href="http://www.tassos.gr/contact?s=BackEndSupport" target="_blank">here</a><br>
    Please post a rating and a review at the <a href="http://extensions.joomla.org/extensions/extension/style-a-design/popups-a-iframes/responsive-scroll-triggered-box-for-joomla">Joomla! Extensions Directory <i class="icon-thumbs-up"></i></a>
</div>