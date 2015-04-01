<?php	
/**
* @package  JE K2 Multiple Form STORY
* @copyright Copyright (C) 2009 - 2010 Open Source Matters. All rights reserved.
* @license   http://www.gnu.org/licenses/lgpl.html GNU/LGPL, see LICENSE.php
* Contact to : emailtohardik@gmail.com, joomextensions@gmail.com
* Visit : http://www.joomlaextensions.co.in/
**/ 

defined ('_JEXEC') or die ('restricted access');
$arr = array();
$_SESSION['img_path']=array();
$_SESSION['new_imgpath'] = array();
$_SESSION['term'] = '';

$uri = JURI::getInstance();
$url= $uri->root();

$editor = JFactory::getEditor();
$option = JRequest::getVar('option','','','string');
$document = JFactory::getDocument();
$document->addScript($url.'components/'.$option.'/assets/ajax.js' );
$document->addStyleSheet($url.'components/'.$option.'/assets/style.css');

$my = JFactory::getUser();
$msg    = JText::_( 'SUCCESS');
$link	= JRoute::_($url.'index.php?option='.$option);
$rr = JRequest::getVar('msg');
$ses 	= JRequest::getVar('ses');
$id 	= JRequest::getVar('id','','','int');
$user	= clone(JFactory::getUser());
//$setting = $this->setting;
// ================================= Enter Contest id From backend ======================== //
$mainframe = JFactory::getApplication();
$redconfig 	= 	$mainframe->getParams();
$titel=$redconfig->get('titel_id');

$catid=$redconfig->get('id');
if($catid != ''){
$_SESSION['catid'] = $catid;
}


$condition_id=$redconfig->get('con_id'); //selected terms and condition

if($condition_id != ''){
$_SESSION['condition_id'] = $condition_id;
}



$pageurl=$redconfig->get('red_id'); // Selected redirect page id

if($pageurl != ''){
$_SESSION['pageurl'] =$pageurl;
}


$notify=$redconfig->get('not_id'); // Select yes to notify by mail

if($notify != ''){
$_SESSION['notify'] = $notify;
}



$captcha=$redconfig->get('cap_id'); // Allow for capcha code to show

if($captcha != ''){
$_SESSION['captcha'] = $captcha;
}


$allow_reguser=$redconfig->get('reg_id'); // Allow register or unregister user to post article in k2
if($allow_reguser != ''){
$_SESSION['allow_reguser'] = $allow_reguser;
}


$auto_publish=$redconfig->get('pub_id'); // Allow to see publish status of article in itemlist page
if($auto_publish != ''){
$_SESSION['auto_publish'] = $auto_publish;
}

$publish_auto=$redconfig->get('auto_id'); // Allow Auto published the article

if($publish_auto != ''){
$_SESSION['publish_auto'] = $publish_auto;
}

$notify_email =$redconfig->get('notemail_id'); //Notify Email comes from menu

if($notify_email != ''){
$_SESSION['notify_email'] = $notify_email;
}


$category =$redconfig->get('id'); // This is for display category on page
if($category != ''){
$_SESSION['allow_dis_cat'] = $category;
}

 
$term=$redconfig->get('term_id'); //Allow to display terms and condition yes/no
if($term != ''){
$_SESSION['term'] = $term;
}


$name=$redconfig->get('user_id'); // When user is unregistered then you can use Username.
if($name != ''){
$_SESSION['setting_name'] = $name;
}

$email=$redconfig->get('email_id'); // When user is unregistered then you can use email.

if($email != ''){
$_SESSION['setting_email'] = $email;
}

//echo '<pre>';print_r($_SESSION);
//echo '<pre>';
//print_r($_SESSION);


// ============================= EOF Enter Contest id From backend ======================== //

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
<?php	if($_SESSION['allow_dis_cat'] == "1") { ?>		

		var cat = document.getElementById('catid').value;
		if (cat == "0") {
			alert ( "<?php echo  JText::_( 'ERR_CATEGORY'); ?>" );
			return false;
		}
<?php	}	 ?>

<?php	if($_SESSION['captcha'] == "1") { ?>
		if (form.cap.value=="") {
			alert ( "<?php echo  JText::_( 'ERR_CAPTCHA'); ?>" );
			form.cap.focus();
			return false;
		}	
<?php	}	 ?>

<?php	if($_SESSION['term'] == "1") { ?>
		if (form.accept.checked==false) {
			alert ( "<?php echo  JText::_( 'ERR_TERMS_COND'); ?>" );
			form.accept.focus();
			return false;
		}	
<?php	}	 ?>

	}
</script>


<style type="text/css">
.inputbox {
	background:none;
}

</style>

  <form action="<?php echo $link;?>" method="post" name="myadminform" enctype="multipart/form-data" onSubmit="return submitbutton()" >

	
	<table class="contenettable" >
	
	<tr>
			<th align="left"> <?php echo $titel; ?> </th>
	 </tr>
		
	<?php 
	if(@$_SESSION['setting_name'] == 1){
	
	?>
	<tr>
			<th align="left"> <?php echo  JText::_( 'NAME'); ?> </th>
	 	</tr>
	 	<tr> 
			<td><input class="inputbox"  type="text" name="name" size="50" maxlength="100" value="<?php if($ses==1) {  @$_SESSION['name']; } if($id){ echo @$this->detail->name;} ?>" /></td>
		</tr>
	<?php }?>	
	<?php if(@$_SESSION['setting_email'] == 1){?>		
	 	<tr>
			<th align="left"> <?php echo  JText::_( 'EMAIL'); ?> </th>
	 	</tr>
	 	<tr> 
			<td><input class="inputbox" type="text" name="email" size="50" maxlength="100" value="<?php if($ses==1) { echo @$_SESSION['email']; } if($id){ echo @$this->detail->email;} ?>" /></td>
		</tr>	
	<?php }
	
	?>
	
	 	<tr>
			<th align="left"> <?php echo  JText::_( 'TITLE'); ?> </th>
	 	</tr>
	 	<tr> 
			<td>
			<?php 
			if($id != '')
			{
				echo $this->detail->title;
			
			 }else{
			?>
			
			<input class="inputbox" type="text" name="title" size="50" maxlength="100" value="<?php if($ses==1) { echo $_SESSION['title']; } if($id){echo $this->detail->title;} ?>" />
			<?php
			
			}?>
			</td>
			
			
		</tr>
		

 
 
 
 <?php if($id){?>
				<tr>
				   <td>
				<div id="extrafield_data" style="display:none;">
		 
			</div>
		 </td></tr>
       <?php }else{
			
		    $model = $this->getModel ( 'jesubmit' );
			$setting=$model->getExtrafields($catid);
		?>
		    <tr>
			<td>
				<?php  echo $setting; }?>
				
				
		</td><tr>
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
		<?php if($id) {
		
		 ?>
		<img src="<?php echo $this->detail->imageXSmall;?>" border="0"/>
		
		<?php } ?>
		</td>
		</tr>
		<?php 
		if(@$_SESSION['captcha'] == "1" && $id==0)
		{
			$user = JFactory::getUser();
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
			$setting=$model->getmsg(); 
			
			if(@$_SESSION['term'] == 1 && $id==0) {
	?>
		<tr>
		<td>
			<input type="checkbox" id="acc" name="accept" value="accept" />
			<a href="javascript: void(0)" onclick="window.open('index.php?tmpl=component&option=com_jek2storymultipleform&view=jesubmit&call=2', 'windowname', 'scrollbars=1,width=500px, height=500px')"><?php echo JText::_('ACCEPT_TERMS_CONDITION'); ?></a>
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
		
		if(@$_SESSION['publish_auto']==1)
			$publish	= 1;
		else
			$publish	= 0;
			
			
		if($id)
	     $k2itemid = $this->detail->itemid; 
		else
		$k2itemid =0;
	?>
	<input type="hidden" name="publish" value="<?php echo $publish; ?>" />
	
  	<input type="hidden" name="option" value="com_jek2storymultipleform" />
	<input type="hidden" name="catid" value="<?php echo $_SESSION['catid']; ?>" />
	<input type="hidden" name="condition_id" value="<?php echo $_SESSION['condition_id']; ?>" />
	<input type="hidden" name="pageurl" value="<?php echo $_SESSION['pageurl']; ?>" />
	<input type="hidden" name="notify" value="<?php echo $_SESSION['notify']; ?>" />
	<input type="hidden" name="notify_email" value="<?php echo $_SESSION['notify_email']; ?>" />
	
	<input type="hidden" name="captcha" value="<?php echo $_SESSION['captcha']; ?>" />
	<input type="hidden" name="term" value="<?php echo $_SESSION['term']; ?>" />
	
	<input type="hidden" name="allow_reguser" value="<?php echo $_SESSION['allow_reguser']; ?>" />
	<input type="hidden" name="auto_publish" value="<?php echo $_SESSION['auto_publish']; ?>" />
	<input type="hidden" name="publish" value="<?php echo $publish;?>" />
	<?php /*?><input type="hidden" name="username" value="<?php echo $name ?>" />
	<input type="hidden" name="uemail" value="<?php echo $mail ?>" /><?php */
	?>
	<input type="hidden" name="view" value="jesubmit" />
	
	<input type="hidden" name="task" value="save" />
	<input type="hidden" id="k2itemid" name="k2itemid" value="<?php echo $k2itemid;?>" />
	<input type="hidden" name="submit_name" value="" />
	<input type="hidden" name="id" value="<?php echo $id;?>" />
	<input type="hidden" name="setting_name" value="<?php echo $_SESSION['setting_name'];?>" />
	<input type="hidden" name="setting_email" value="<?php echo $_SESSION['setting_email'];?>" />

	<input type="hidden" name="live_url" id="live_url" value="<?php echo $url;?>" />
	<input type="hidden" name="allow_dis_cat" id="allow_dis_cat" value="<?php echo $_SESSION['allow_dis_cat'];?>" />	
	<?php echo JHTML::_( 'form.token' ); 

	?>
    
</form>

<?php 
if($id){ ?>
<script language="javascript" type="text/javascript">
select_cate(<?php echo $this->detail->catid;?>);
</script>
<?php } ?>