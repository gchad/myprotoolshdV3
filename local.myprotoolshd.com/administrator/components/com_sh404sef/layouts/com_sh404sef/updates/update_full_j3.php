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

defined('_JEXEC') or die;

/**
 * This layout displays a button to allow one-click update
 */

$button = ShlHtmlBs_Helper::button(JText::_('COM_SH404SEF_PERFORM_UPDATE'));
?>
<form method="post" action="index.php?option=com_installer&task=update.find">
<?php
  echo $button; 
  echo JHTML::_( 'form.token' ); ?>
</form>
