<?php
/**
 * sh404SEF - SEO extension for Joomla!
 *
 * @author      Yannick Gaultier
 * @copyright   (c) Yannick Gaultier - Weeblr llc - 2015
 * @package     sh404SEF
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version     4.4.9.2487
 * @date		2015-04-20
 */

// Security check to ensure this file is being included by a parent file.
if (!defined('_JEXEC')) die('Direct Access to this location is not allowed.');

?>

  <dl id="system-message">
  <dt class="error"></dt>
  <dd class="error message fade">
    <div id="sh-error-box">
  <?php if (!empty( $this->errors)) : ?>
      <div id="error-box-content">
        <ul>
        <?php
          foreach ($this->errors as $error) :
            echo '<li>' . $error . '</li>';
          endforeach;
        ?>
        </ul>
      </div>
    <?php endif; ?>
    </div>
  </dd>
  </dl>

  <dl id="system-message">
  <dt class="message"></dt>
  <dd class="message message fade">
  <div id="sh-message-box">
  <?php if (!empty( $this->helpMessage)) echo $this->helpMessage; ?>
  <?php if (!empty( $this->message)) : ?>
    <ul>
      <li><div id="message-box-content"><?php if (!empty( $this->message)) echo $this->message; ?></div></li>
    </ul>
    <?php endif; ?>
    </div>
  </dd>
  </dl>

<form method="post" name="adminForm" id="adminForm">

<?php
if(version_compare(JVERSION, '3.0', 'ge')):
	if(!empty( $this->sidebar)): ?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>
		<div id="j-main-container" class="span10">
	<?php else : ?>
		<div id="j-main-container">
	<?php
  endif;
endif;

echo $this->loadTemplate( $this->joomlaVersionPrefix . '_filters')?>

<div id="editcell">
    <table class="adminlist">
      <thead>
        <tr>
          <th class="title" width="3%">
            <?php echo JText::_( 'NUM' ); ?>
          </th>

          <th width="2%">
            <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
          </th>

          <th class="title" width="40%" style="text-align: left;" >
            <?php echo JHTML::_('grid.sort', JText::_( 'COM_SH404SEF_ALIAS'), 'oldurl', $this->options->filter_order_Dir, $this->options->filter_order); ?>
          </th>

          <th class="title" width="50%" style="text-align: left;" >
            <?php echo JText::_( 'COM_SH404SEF_URL'); ?>
          </th>

        </tr>
      </thead>
      <tfoot>
        <tr>
          <td colspan="4">
            <?php echo $this->pagination->getListFooter(); ?>
          </td>
        </tr>
      </tfoot>
      <tbody>
        <?php
          $k = 0;
          if( $this->itemCount > 0 ) {
            for ($i=0; $i < $this->itemCount; $i++) {

              $alias = &$this->items[$i];
              $checked = JHtml::_( 'grid.id', $i, $alias->id);
        ?>

        <tr class="<?php echo "row$k"; ?>">

          <td align="center" width="3%">
            <?php echo $this->pagination->getRowOffset( $i ); ?>
          </td>

          <td align="center" width="2%">
            <?php echo $checked; ?>
          </td>

          <td width="30%">
            <?php
            $linkData = array( 'c' => 'editalias', 'task' => 'edit', 'view' => 'editurl', 'startOffset' => '2','cid[]' => $alias->id, 'tmpl' => 'component');
            $aliasData = array( 'title' => JText::_('COM_SH404SEF_MODIFY_ALIAS_TITLE') . ' ' .$alias->oldurl, 'class' => 'modalediturl', 'anchor' => $alias->alias);
            $modalOptions = array( 'size' => array('x' =>800, 'y' => 600));
            echo Sh404sefHelperHtml::makeLink( $this, $linkData, $aliasData, $modal = true, $modalOptions, $hasTip = false, $extra = '');
            ?>
          </td>

          <td width="42%">
            <?php
            echo $this->escape( $alias->newurl);
            $sefConfig = & Sh404sefFactory::getConfig();
            if (!empty( $alias->oldurl)) {
              echo '<br/><i>(' . $this->escape( $alias->oldurl) . ')</i>';
              $link = JURI::root() . ltrim( $sefConfig->shRewriteStrings[$sefConfig->shRewriteMode], '/') . $alias->oldurl;
            } else {
              echo '<br /><i>(-)</i>';
              $link = JURI::root() . $alias->newurl;
            }
            // small preview icon
            echo '&nbsp;<a href="' . $link . '" target="_blank" title="' . JText::_('COM_SH404SEF_PREVIEW') . ' ' . $this->escape($alias->oldurl) . '">';
            echo '<img src=\'components/com_sh404sef/assets/images/external-black.png\' border=\'0\' alt=\''.JText::_('COM_SH404SEF_PREVIEW').'\' />';
            echo '</a>';
            ?>

          </td>

        </tr>
        <?php
        $k = 1 - $k;
      }
    } else {
      ?>
        <tr>
          <td align="center" colspan="4">
            <?php echo JText::_( 'COM_SH404SEF_NO_ALIASES' ); ?>
          </td>
        </tr>
        <?php
      }
      ?>
      </tbody>
    </table>
    <input type="hidden" name="c" value="aliases" />
    <input type="hidden" name="view" value="aliases" />
    <input type="hidden" name="option" value="com_sh404sef" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="hidemainmenu" value="0" />
    <input type="hidden" name="filter_order" value="<?php echo $this->options->filter_order; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->options->filter_order_Dir; ?>" />
    <?php echo JHTML::_( 'form.token' ); ?>
  </div>
</form>

<div class="sh404sef-footer-container">
	<?php echo $this->footerText; ?>
</div>

