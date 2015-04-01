<?php	
 /**

* @package   JE K2 Multiple Form STORY

* @copyright Copyright (C) 2009 - 2010 Open Source Matters. All rights reserved.

* @license   http://www.gnu.org/licenses/lgpl.html GNU/LGPL, see LICENSE.php

* Contact to : emailtohardik@gmail.com, joomextensions@gmail.com

* Visit : http://www.joomlaextensions.co.in/

**/ 

defined ('_JEXEC') or die ('restricted access');
JHTML::_('behavior.tooltip');
JHTMLBehavior::modal();
$uri = JURI::getInstance();
$url= $uri->root();
$option = JRequest::getVar('option','','','string');
$editor = JFactory::getEditor();
$document = JFactory::getDocument();

$document->addScript($url.'administrator/components/'.$option.'/assets/ajax.js' );
$my = JFactory::getUser();

?>
<script language="javascript" type="text/javascript">
  	var live_url="<?php echo $url.'administrator/';?>";
	Joomla.submitform = function submitbutton(pressbutton) {
		submitform( pressbutton );
    }
	
</script>
	
<form action="index.php" method="post" name="adminForm">
		<table align="center" width="70%" border="0" cellpadding="4" cellspacing="2" class="adminForm">
		<tr>
			<td  align="right">
				<?php echo JText::_( 'USER_MESSAGE_BODY');?>
			</td>
			<td>
				<?php echo $editor->display("notify_message",$this->res->notify_message,'300','200','100','20','0');	?>
			</td>
		</tr>
   		<tr>
			<td  align="right">
				<?php echo JText::_( 'ADMINISTRATOR_MESSAGE_BODY'); ?>
			</td>
			<td>
				<?php echo $editor->display("message",$this->res->message,'500','300','100','20','0');	?>
			</td>
		</tr>
        
		
	</table>
  	<input type="hidden" name="option" value="<?php echo $option; ?>">
  	<input type="hidden" name="task" value="savesettings">
  	<input type="hidden" name="view" value="jesubmit">
</form>

   