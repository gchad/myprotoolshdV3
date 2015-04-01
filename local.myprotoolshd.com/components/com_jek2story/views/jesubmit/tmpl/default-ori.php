<?php	
/**
* @package   JE K2 STORY
* @copyright Copyright (C) 2009 - 2010 Open Source Matters. All rights reserved.
* @license   http://www.gnu.org/licenses/lgpl.html GNU/LGPL, see LICENSE.php
* Contact to : emailtohardik@gmail.com, joomextensions@gmail.com
* Visit : http://www.joomlaextensions.co.in/
**/ 

defined ('_JEXEC') or die ('restricted access');
$arr = array();
$_SESSION['img_path']=array();
$_SESSION['new_imgpath'] = array();
$uri =& JURI::getInstance();
$url= $uri->root();
JHTML::_('behavior.calendar');
$editor =& JFactory::getEditor();
$option = JRequest::getVar('option','','','string');
$document = &JFactory::getDocument();
$document->addScript($url.'components/'.$option.'/assets/ajax.js' );
$document->addStyleSheet($url.'components/'.$option.'/assets/style.css');

$my =& JFactory::getUser();
$msg    = JText::_( 'SUCCESS');
$link	= JRoute::_($url.'index.php?option='.$option);
echo '<b>'.$rr = JRequest::getVar('msg');echo '</b>';
$ses 	= JRequest::getVar('ses');
$id 	= JRequest::getVar('id','','','int');
$user	= clone(JFactory::getUser());
$setting = $this->setting;
/*echo '<pre>';
print_r($setting);
*/


?>
<script language="javascript" type="text/javascript">
	function submitbutton() 
	{
		var form = document.myadminform;
		
		/*if(form.name.value=="") {
			alert ("<?php //echo JText::_( 'ERR_FNAME'); ?>");
			form.name.focus();
			return false;
		}
		
		if(form.email.value=="") {
			alert ("<?php //echo JText::_( 'ERR_EMAIL'); ?>");
			form.email.focus();
			return false;
		}*/
		
		if(form.title.value=="") {
			alert ("<?php echo JText::_( 'ERR_TITLE'); ?>");
			form.title.focus();
			return false;
		}
<?php	if($this->res->category == "1") { ?>		
		if (form.catid.value=="0") {
			alert ( "<?php echo  JText::_( 'ERR_CATEGORY'); ?>" );
			form.catid.focus();
			return false;
		}
<?php	}	 ?>

<?php	if($this->res->captcha == "1") { ?>
		if (form.cap.value=="") {
			alert ( "<?php echo  JText::_( 'ERR_CAPTCHA'); ?>" );
			form.cap.focus();
			return false;
		}	
<?php	}	 ?>

<?php	if($this->res->term == "1") { ?>
		if (form.accept.checked==false) {
			alert ( "<?php echo  JText::_( 'ERR_TERMS_COND'); ?>" );
			form.accept.focus();
			return false;
		}	
<?php	}	 ?>

	}
</script>

<?php
	/*$res_file=$url.'index.php?template=component&option='.$option.'&view=request_get';
	$img=$url.'images/stories/';
	$imgbin=$url.'components/'.$option.'/images/bin.png';*/
?>

<style type="text/css">
.inputbox {
	background:none;
}

</style>

  <form action="<?php echo $link;?>" method="post" name="myadminform" enctype="multipart/form-data" onSubmit="return submitbutton()" >

	<table class="contenettable" >
		<tr>
			<th ><h3><?php echo  $this->res->title; ?></h3></th>
		</tr>  

	
	<?php 
		
	
		if($setting->name == 1){
	
	?>
	<tr>
			<th align="left"> <?php echo  JText::_( 'NAME'); ?> </th>
	 	</tr>
	 	<tr> 
			<td><input class="inputbox"  type="text" name="name" size="50" maxlength="100" value="<?php if($ses==1) { echo $_SESSION['name']; } if($id){ echo $this->detail->name;} ?>" /></td>
		</tr>
	<?php }?>	
	<?php if($setting->email == 1){?>		
	 	<tr>
			<th align="left"> <?php echo  JText::_( 'EMAIL'); ?> </th>
	 	</tr>
	 	<tr> 
			<td><input class="inputbox" type="text" name="email" size="50" maxlength="100" value="<?php if($ses==1) { echo $_SESSION['email']; } if($id){ echo $this->detail->email;} ?>" /></td>
		</tr>	
	<?php }
	
	?>
	
	 	<tr>
			<th align="left"> <?php echo  JText::_( 'TITLE'); ?> </th>
	 	</tr>
	 	<tr> 
			<td><input class="inputbox" type="text" name="title" size="50" maxlength="100" value="<?php if($ses==1) { echo $_SESSION['title']; } if($id){echo $this->detail->title;} ?>" /></td>
		</tr>
<?php 		
if($this->res->category == "1")
{?>
		<tr>
 			<th align="left"> <?php echo JText::_( 'CATEGORY'); ?> : </th>
		</tr>
		<tr>
			<td><?php echo $this->lists['catid']; ?></td>
		</tr>
 <?php }else {?>
 
 <input  type="hidden" name="catid" id="catid" value="<?php echo $this->res->cat_id;?>" />
 
 <?php } ?>
		<tr><td> <div id="extrafield_data" style="display:none;">
		
			</div>
		 </td></tr>
 		
 
 
 
     	
	 	<tr>
	 		<th align="left"><?php echo  JText::_( 'MAINTEXT'); ?></th>
		</tr>
  		<tr>
			<td><?php
			if($id)
			{
				$text = '';
				$introtext =  $this->detail->introtext;
				$fulltext = $this->detail->fulltext;
				if($fulltext != ''){
					$tag = '<hr id="system-readmore">';
					 $text = $introtext.' '.$tag.' '.$fulltext;
				}else{
					$text = $introtext;
				}	
				
				echo $editor->display('fulltext',$text,'$widthPx','$heightPx','80','20','0');
			}
			else{
		
			 if($ses==1) { $text = $_SESSION['fulltext']; }else{ $text = '';}  
			 echo $editor->display('fulltext',$text,'$widthPx','$heightPx','80','20','0'); //$editor->display("fulltext",$longtext,'$widthPx','$heightPx','80','20','0'); 
			 }
			 ?></td>
 		</tr>
		<tr>
		<th align="left"><?php echo  JText::_( 'UPLOAD_IMAGE'); ?></th>
		</tr>
		<tr>
		<td>
		<input type="file" name="itemimage" id="itemimage"  />
		<?php if($id) { ?>
		<img src="<?php echo $this->detail->imageXSmall;?>" border="0"/>
		
		<?php } ?>
		</td>
		</tr>
		<?php 
		if($this->res->captcha == "1" && $id==0)
		{
			$user =& JFactory::getUser();
			$dest = $url.'index.php?option='.$option.'&view=jesubmit&task=captchacr&tmpl=component&ac='.rand();
		?>

	
		<tr>
			<td>
				<table cellpadding="0" cellspacing="0" border="0">
				<tr><td colspan="2"><b><?php echo JText::_( 'WORD_VARIFICATION' ); ?></b></td></tr>
				<tr><td><div id="default_cap_div"><img src="<?php echo $dest;?>" /></div>
							<div id="refresh_cap_div"></div>
					</td>
					<td><a href="javascript:void(0);" onclick="cap_refresh()"><img src="<?php echo $url.'components/'.$option.'/assets/images/refresh.png'; ?>" border="0" /></a></td>
				</tr>
				<tr><td colspan="2"><?php echo JText::_( 'PLEASE_ENTER_CODE_IN_GIVEN_IMAGE' ); ?></td></tr>
				</table>
			</td>
		</tr>
		<tr>
			<td><input class="inputbox" type="text" name="cap" value="" id="cap" /></td>
			<input type="hidden" name="cpt" id="cpt" value="1" />
		</tr>
		<?php }else{ ?> 
			<input type="hidden" name="cpt" id="cpt" value="0" />
		<?php }	
			
			$model = $this->getModel ( 'jesubmit' );
			$setting=$model->getcheck1(); 
			
			if($setting->term == 1 && $id==0) {
	?>
		<tr>
		<td>
			<input type="checkbox" id="acc" name="accept" value="accept" />
			<a href="javascript: void(0)" onclick="window.open('index.php?tmpl=component&option=com_jek2story&view=jesubmit&call=2', 'windowname', 'scrollbars=1,width=500px, height=500px')"><?php echo  JText::_( 'ACCEPT_TERMS_CONDITION'); ?></a>
   <input type="hidden" id="termaccept" name="termaccept" value="1" />
		</td>
		</tr> 
	<?php }else{ ?>
	<input type="hidden" id="termaccept" name="termaccept" value="0" />
	<?php }?>
		<tr>
			<td>	
			   
				<input type="submit" name="save" onSubmit="return submitbutton()" class="button"  value="<?php echo  JText::_( 'SUBMIT_BUTTON'); ?>"  />			<input type="button" name="cancel" onClick="history.go(-1)" class="button"  value="<?php echo  JText::_( 'CANCEL_BUTTON'); ?>"  />
			</td>
		</tr>
	</table>

	<div style="clear:both;"></div>
	<?php 
		//echo $this->res->auto_publish; 
		if($this->res->publish==1)
			$publish	= 1;
		else
			$publish	= 0;
			
		if($id)
		$k2itemid = $this->detail->itemid;
		else
		$k2itemid =0;
	?>
	<input type="hidden" name="publish" value="<?php echo $publish; ?>" />
	
  	<input type="hidden" name="option" value="com_jek2story" />
	<!--<input type="hidden" name="v11" value="<?php //echo base64_encode($ResultStr);?>" />-->
	<input type="hidden" name="view" value="jesubmit" />
	<input type="hidden" name="task" value="save" />
	<input type="hidden" id="k2itemid" name="k2itemid" value="<?php echo $k2itemid;?>" />
	<input type="hidden" name="submit_name" value="" />
	<input type="hidden" name="id" value="<?php echo $id;?>" />
	<input type="hidden" name="setting_name" value="<?php echo $setting->name;?>" />
	<input type="hidden" name="setting_email" value="<?php echo $setting->email;?>" />
	<input type="hidden" name="live_url" id="live_url" value="<?php echo $url;?>" />
	
	<?php echo JHTML::_( 'form.token' ); 

	?>
    
</form>

<?php 


if($id){ ?>
<script language="javascript" type="text/javascript">
select_cate(<?php echo $this->detail->catid;?>);
</script>
<?php } 


?>