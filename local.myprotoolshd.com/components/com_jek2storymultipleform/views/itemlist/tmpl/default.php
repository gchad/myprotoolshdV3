<?php 
/**
* @package   JE K2 Multiple Form STORY
* @copyright Copyright (C) 2009 - 2010 Open Source Matters. All rights reserved.
* @license   http://www.gnu.org/licenses/lgpl.html GNU/LGPL, see LICENSE.php
* Contact to : emailtohardik@gmail.com, joomextensions@gmail.com
* Visit : http://www.joomlaextensions.co.in/
**/ 

defined('_JEXEC') or die('Restricted access');
$uri = JURI::getInstance();
$url= $uri->root();
$folder = $url.'/components/com_jeshop/assets/icon/category.gif';
$option = JRequest::getVar('option','','','string');
$Itemid = JRequest::getVar('Itemid','','','int');

$limitstart = JRequest::getVar('limitstart','','','int');

$document =  JFactory::getDocument();

$document->addScript($url.'components/com_jek2storymultipleform/assets/ajax.js');
$mainframe = JFactory::getApplication();
$context = '';

$search_word  = JRequest::getVar('search_word','','request','string');

$model = $this->getModel ( 'itemlist' );
$link_img = $url.'/components/com_jek2storymultipleform/assets/images/';
$setting =  $this->get('Setting');
//$publish = $setting->publish;
//$auto_publish = $setting->auto_publish;
// ================================= Enter Contest id From backend ======================== //
$mainframe = JFactory::getApplication();
$redconfig 	= $mainframe->getParams();
 $titel=$redconfig->get('titel_id');
 $category=$redconfig->get('category_id');
 $catid=$redconfig->get('id');
 $condition_id=$redconfig->get('con_id');
 $pageurl=$redconfig->get('red_id');
 $notify=$redconfig->get('not_id');
 $captcha=$redconfig->get('cap_id');
 $term=$redconfig->get('term_id');
 $allow_reguser=$redconfig->get('reg_id');

 $publish_id = JRequest::getVar ( 'publish_id', '0', 'request', 'int' ); 
 $automatic_id = JRequest::getVar ( 'automatic_id', '0', 'request', 'int' ); 
 
 if($publish_id == '0'){
	 $auto_publish=$redconfig->get('pub_id');	
	 $publish=$redconfig->get('auto_id'); 
 }else{
 	$auto_publish=$publish_id;
	$publish=$automatic_id; 
	
 }
 
 $name=$redconfig->get('user_id');
 $email=$redconfig->get('email_id');
 $notify_email =$redconfig->get('notemail_id');
    
  

// ============================= EOF Enter Contest id From backend ======================== //

?>
<style type="text/css">
.pagination_area ul{list-style:none; margin:0; padding:0; }
.pagination_area ul li{display:inline; padding:0 5px;}

</style>
<script language="javascript" type="text/javascript">
var ajax_item_id = 0;
function nextpage(proid)
{
	document.getElementById("option").value= '<?php echo $option;?>';
	document.getElementById("view").value= 'category_detail';
	document.getElementById("id").value = proid;
	document.adminForm.submit();
}

function add()
{

	window.location = "<?php echo JRoute::_('index.php?option=com_jek2storymultipleform&view=jesubmit') ?>";
}


	 checked = false;
      function checkedAll () 
	  {
	  	if (checked == false){checked = true}else{checked = false}
		
		for (var i = 0; i < document.getElementById('frm1').elements.length; i++) 
		{
	  	document.getElementById('frm1').elements[i].checked = checked;
		}
      }
	  
	  
	  function checkvalidation()
	  {
	  
	  	var counter=0;
		
		var chk = document.getElementsByName('checkbox[]');
		for(var i = 0; i < chk.length; i++)
		{
			if(chk[i].checked == true)	
			{
				counter++;	
			}
			
		}
		
		
		if(counter < 1)
			{
				alert("please select atleaast one checkbox");
				return false;
			}
			else
			{
				window.confirm("Are you sure you want to delete?");
				var form = document.adminForm;	
				
				document.getElementById('option').value = 'com_jek2storymultipleform';
				document.getElementById('task').value = 'delete';
				form.submit();
	  
			}
			
	  }
	  
	   function clearinput(){
	   document.getElementById('search_word').value = '';
	   document.frm1.submit();
	   }
</script>

<form action="<?php echo JRoute::_('index.php?option='.$option); ?>" method="post" name="frm1"  id="frm1">
<div id="editcell">
<table class="adminlist" cellspacing="0" cellpadding="0" border="1" width="100%">
    <tr><td colspan="10">
	<table>
           <tr>
		    <td><input type="submit" name="toggle" value="delete" onclick="return checkvalidation();"/></td>
		<td colspan="6"> <b><?php echo JText::_( 'Filter' ); ?>:</b> <input type="text" name="search_word" id="search_word" value="<?php echo $search_word;?>" /> <input type="submit" name="search" value="GO" /><input type="button" name="clear" id="clear" value="Reset" onclick="clearinput();" /></td>
		</tr> 	
            </table>
		</td></tr>
		<tr>
			<th>
				<?php echo JText::_( 'ID' ); ?>
			</th>
			<th>
				<input type="checkbox" name="chk" onclick="checkedAll();" />
			</th>
            
			<th>
				<?php echo JText::_( 'TITLE_ITEM' ); ?>
			</th>
			<th>
				<?php echo JText::_( 'CATEGORY' ); ?>
			</th>
			
			<th>
				<?php echo JText::_( 'EDIT' ); ?>
			</th>
           
            <?php //if($auto_publish == 1){?>
            <th>
				<?php echo JText::_( 'PUBLISHED' ); ?>
			</th>
            <?php //}?>
		</tr>
	</thead>
<?php
	for ($i=0; $i<count($this->subscribe );$i++)
	{
		$row = $this->subscribe[$i];
		
		
		$usernm = $model->getUsernm($row->created_by);

		$edit 	= JRoute::_( 'index.php?option='.$option.'&view=jesubmit&id='. $row->itemid.'&Itemid='.$Itemid );
		if($row->status == 1){	$news_status = 'tick.png';	}else{	$news_status = 'publish_x.png';		}
?>	
		<tr>
		
			<td align="center"><?php echo $i + 1 + $limitstart;?></td>
            <td align="center">
			<input type="checkbox" name="checkbox[]" id="checkbox[]" value="<?php echo $row->itemid;?>"/>		
            </td>
			<td><a href="<?php echo $edit;?>"><?php  echo $row->title; ?></a></td>
			<td  align="center"><?php echo $row->categorynm;?></td>
			<td align="center"><a href="<?php echo $edit;?>"><?php echo JText::_( 'EDIT' ); ?></a>
            </td>
            
            <?php //if($auto_publish==1){?>
			 <td align="center"><img src="<?php echo $link_img.$news_status;?>"  /></td><?php //} ?>
            
		</tr>
<?php }
?>	
	</table>
</div>

<table class = "pagination_area" cellpadding="0" cellspacing="0"  border="0"  align="center" width="100%">
<tr>
	<td valign="top" align="center">
	<?php echo $this->pagination->getPagesLinks(); ?>

	</td>
</tr>
<tr>
	<td valign="top" align="center">
		<?php //echo $this->pagination->getPagesCounter(); ?>
	</td>
</tr>
</table>

  <div style="clear:both;"></div>

<input type="hidden" id="view" name="view" value="itemlist" />

<input type="hidden" id="option" name="option" value="com_jek2storymultipleform" />
<input type="hidden" id="id" name="id" value="" />

<input type="hidden" name="task" id="task" value="" />
<input type="hidden" name="uid" id="uid" value="" />
<input type="hidden" name="nid" id="nid" value="" />
<input type="hidden" name="itemno" id="itemno" value="" />

<input type="hidden" name="categoryid" id="categoryid" value="<?php echo @$this->subscribe[0]->categoryid?>" />


<input type="hidden" id="Itemid" name="Itemid" value="<?php echo $Itemid;?>" />
<input type="hidden" name="jelive_url" id="jelive_url" value="<?php echo $url; ?>" />
</form>

