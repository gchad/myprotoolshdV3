<?php	
 /**

* @package   JE K2 STORY

* @copyright Copyright (C) 2009 - 2010 Open Source Matters. All rights reserved.

* @license   http://www.gnu.org/licenses/lgpl.html GNU/LGPL, see LICENSE.php

* Contact to : emailtohardik@gmail.com, joomextensions@gmail.com

* Visit : http://www.joomlaextensions.co.in/

**/ 

defined ('_JEXEC') or die ('restricted access');
JHTML::_('behavior.tooltip');
JHTMLBehavior::modal();
$uri =& JURI::getInstance();
$url= $uri->root();
$option = JRequest::getVar('option','','','string');
$editor =& JFactory::getEditor();
$document = &JFactory::getDocument();

$document->addScript($url.'administrator/components/'.$option.'/assets/ajax.js' );
$my =& JFactory::getUser();

//echo '<pre>';
//print_r($this->res);

?>
<script language="javascript" type="text/javascript">
  	var live_url="<?php echo $url.'administrator/';?>";
	Joomla.submitform = function submitbutton(pressbutton) {
		submitform( pressbutton );
    }
	
	function displaypayment(payment_type) {
		if(payment_type==0) {
			document.getElementById("paymentdetail").style.display="block";
		} else {
			document.getElementById("paymentdetail").style.display="none";
		}
	}	
</script>
	
<form action="index.php" method="post" name="adminForm">
	<table align="center" width="70%" border="0" cellpadding="4" cellspacing="2" class="adminForm">
 		<tr align="center" valign="middle">
      		<td width="40%" align="right" valign="top">
    			<?php echo JText::_( 'STORY_TITLE');?>
      		</td>
      		<td align="left" valign="top">
      			<input type="text" name="title" value="<?php echo $this->res->title; ?>"/>
      		</td>
    	</tr>
		<tr align="center" valign="middle">
      		<td width="40%" align="right" valign="top">
    			<?php echo JText::_( 'ALLOW_TO_SHOW_CATEOGRY_IN_FRONT_END');?>
      		</td>
      		<td align="left" valign="top">
      			<?php  echo $this->lists['category'];?>
      		</td>
    	</tr>
		<tr align="center" valign="middle">
      		<td width="40%" align="right" valign="top">
    			<?php echo JText::_( 'CATETORY_LIST');?>
      		</td>
      		<td align="left" valign="top">
      			<?php  echo $this->lists['cat_id'];?>
      		</td>
    	</tr>
		<tr align="center" valign="middle">
      		<td align="right" valign="top">
      			<?php echo JText::_( 'ITEM');?>
      		</td>
      		<td align="left" valign="top">
     			<?php  echo $this->lists['articleid'];?>
     		</td>
    	</tr>
		
		<tr align="center" valign="middle">
      		<td align="right" valign="top">
      			<?php echo JText::_( 'REDIRECT_PAGE');?>
      		</td>
      		<td align="left" valign="top">
     			<?php  echo $this->lists['pageurl']; //echo '<br>'.JText::_( 'NOT_REDIRECT_PAGE'); ?>
     		</td>
    	</tr>
		
		<tr align="center" valign="middle">
      		<td width="40%" align="right" valign="top">
    			<?php echo JText::_( 'EMAILNOTIFY');?>
      		</td>
      		<td align="left" valign="top">
      			<?php echo $this->lists['notify']; ?>
      		</td>
    	</tr>
		<tr align="center" valign="middle">
      		<td align="right" valign="top">
     			<?php echo JText::_( 'NOTIFY_EMAIL');?>
      		</td>
      		<td align="left" valign="top">
      			<input type="text" name="notify_email" size="40" value="<?php echo $this->res->notify_email; ?>"/>
      		</td>
    	</tr>
		<tr>
			<td  align="right" valign="top">
				<?php echo JText::_( 'SHOW_CAPTCHA' ); ?>:
			</td>
			<td>
				<?php echo $this->lists['captcha']; ?> 
			</td>
		</tr>
		<tr align="center" valign="middle">
      		<td width="40%" align="right" valign="top">
    			<?php echo JText::_( 'ALLOW_TERMS');?>
      		</td>
      		<td align="left" valign="top">
      			<?php echo $this->lists['terms']; ?>
      		</td>
    	</tr>
		
		<tr align="center" valign="middle">
      		<td width="40%" align="right" valign="top">
    			<?php echo JText::_( 'ONLY_REGISTERED_USER_POST_STORY');?>
      		</td>
      		<td align="left" valign="top">
      			<?php echo $this->lists['allow_reguser']; ?>
      		</td>
    	</tr>
		
		<tr align="center" valign="middle">
      		<td width="40%" align="right" valign="top">
    			<?php echo JText::_( 'SHOW_STATUS_PUBLISH');?> 
      		</td>
      		<td align="left" valign="top">
      			<?php echo $this->lists['auto_publish']; ?>
      		</td>
    	</tr>
		<tr align="center" valign="middle">
      		<td width="40%" align="right" valign="top">
    			<?php echo JText::_( 'Auto published');?>
      		</td>
      		<td align="left" valign="top">
      			<?php echo $this->lists['publish']; ?>
      		</td>
    	</tr>
		</table>
		
	<?php 	if($this->res->allow_reguser==0) 
				$payment_style	= 'display:block;';
			  else
			  	$payment_style	= 'display:none;';
			  		
		 ?>
		
		<div id="paymentdetail" class="paymentdetail" style="<?php echo $payment_style; ?>">
		<table align="center" width="70%" border="0" cellpadding="4" cellspacing="2" class="adminForm">
		<tr align="center" valign="middle">
      		<td width="40%" align="right" valign="top">
    			<?php echo JText::_( 'USERNAME');?>
      		</td>
      		<td align="left" valign="top">
      			<?php echo $this->lists['name']; ?>
      		</td>
    	</tr>
        <tr align="center" valign="middle">
      		<td width="40%" align="right" valign="top">
    			<?php echo JText::_( 'EMAIL');?>
      		</td>
      		<td align="left" valign="top">
      			<?php echo $this->lists['email']; ?>
      		</td>
    	</tr>
		</table>
        </div>
		
		<table align="center" width="70%" border="0" cellpadding="4" cellspacing="2" class="adminForm">
		<tr>
			<td  align="right">
				<?php echo JText::_( 'USER_MESSAGE_BODY');?>
			</td>
			<td>
				<?php echo $editor->display("notify_message",$this->res->notify_message,'500','200','100','20','0');	?>
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

   <script language="javascript" type="text/javascript">
   var catvar;
   <?php 
   
   $sectionid			= $this->res->sectionid;
   $catid			= $this->res->catid;
   
   ?>
   select_cate("<?php echo  $sectionid; ?>  ");
   catvar=<?php echo $catid; ?>
  
   </script>  