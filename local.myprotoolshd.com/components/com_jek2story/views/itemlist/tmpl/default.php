<?php 
/**
* @package   JE K2 STORY
* @copyright Copyright (C) 2009 - 2010 Open Source Matters. All rights reserved.
* @license   http://www.gnu.org/licenses/lgpl.html GNU/LGPL, see LICENSE.php
* Contact to : emailtohardik@gmail.com, joomextensions@gmail.com
* Visit : http://www.joomlaextensions.co.in/
**/ 

defined('_JEXEC') or die('Restricted access');
$uri =& JURI::getInstance();
$url= $uri->root();
$folder = $url.'/components/com_jeshop/assets/icon/category.gif';
$option = JRequest::getVar('option','','','string');
$Itemid = JRequest::getVar('Itemid','','','int');
$document = & JFactory::getDocument();

$document->addScript($url.'components/com_jek2story/assets/ajax.js');
$mainframe =& JFactory::getApplication();
$context = '';

$item_id1		= $mainframe->getUserStateFromRequest( $context.'item_id1', 'item_id1',  '0');
$k2category		= $mainframe->getUserStateFromRequest( $context.'k2category', 'k2category',  '0');
$model = $this->getModel ( 'itemlist' );
$link_img = $url.'/components/com_jek2story/assets/images/';
$search_word  = JRequest::getVar('search_word','','request','string');
$setting = & $this->get('Setting');
$publish = $setting->publish;
$auto_publish = $setting->auto_publish;

/*echo '<pre>';
print_r($setting);
*/
?>
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

	window.location = "<?php echo JRoute::_('index.php?option=com_jek2story&view=jesubmit') ?>";
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
				
				document.getElementById('option').value = 'com_jek2story';
				document.getElementById('task').value = 'delete';
				form.submit();
	  
			}
			
	  }
	   function clearinput(){
	   document.getElementById('search_word').value = '';
	   if(document.frm1.submit){
	  
	   }else{
	  
	   
	   }
	  // document.frm1.submit();
	   }
</script>

<form action="<?php echo JRoute::_('index.php?option='.$option); ?>" method="post" name="adminForm"  id="frm1">
<div id="editcell">
<table class="adminlist" cellspacing="0" cellpadding="0" border="1" width="100%">
    <tr><td colspan="10">
	<table>
            	<tr>
                	<td><input type="button" name="new" value="NEW" onclick="add();"/></td>
                    <td><input type="submit" name="toggle" value="delete" onClick="return checkvalidation();"/></td>
                    <td><?php echo $this->lists['k2category'];?></td>
                    <td>
                    	<div id="md_item_mydiv" >
                        <select name="item_id1" id="item_id1" disabled="disabled">
                          <option value="" ><?php echo JText::_( 'SELECT_ITEM' ); ?></option>
						  
                        </select>
      					</div>
                    </td>
                     <td><input type="submit" name="search" value="search" /></td>
        		</tr>
            </table>
			<!--<table>
           <tr>
		    <td><input type="submit" name="toggle" value="delete" onclick="return checkvalidation();"/></td>
		<td colspan="6"> <b><?php echo JText::_( 'Filter' ); ?>:</b> <input type="text" name="search_word" id="search_word" value="<?php echo $search_word;?>" /> <input type="submit" name="search" value="GO" /><input type="submit" name="clear" id="clear" value="Reset" onclick="clearinput();" /></td>
		</tr> 	
            </table>-->
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
				<?php echo JText::_( 'USER_NAME' ); ?>
			</th>
			<th>
				<?php echo JText::_( 'EMAIL' ); ?>
			</th>
			<th>
				<?php echo JText::_( 'EDIT' ); ?>
			</th>
           
            <?php if($auto_publish == 1){?>
            <th>
				<?php echo JText::_( 'PUBLISHED' ); ?>
			</th>
            <?php }?>
		</tr>
	</thead>
<?php
	for ($i=0; $i<count($this->subscribe );$i++)
	{
		$row = &$this->subscribe[$i];
		/*echo '<pre>';
		print_r($row);
		exit;*/
		
		$edit 	= JRoute::_( 'index.php?option='.$option.'&view=jesubmit&id='. $row->id.'&Itemid='.$Itemid );
		if($row->status == 1){	$news_status = 'tick.png';	}else{	$news_status = 'publish_x.png';		}
?>	
		<tr>
			<td><?php echo $row->itemid;?></td>
            <td>
			<input type="checkbox" name="checkbox[]" id="checkbox[]" value="<?php echo $row->itemid;?>"/>		
            </td>
            
			<td><a href="<?php echo $edit;?>"><?php  echo $row->title; ?></a></td>
			<td><?php  echo $row->name; ?></td>
			<td><?php  echo $row->email; ?>	</td>
			<td><a href="<?php echo $edit;?>"><?php echo JText::_( 'EDIT' ); ?></a>
            </td>
            
            <?php if($auto_publish==1){?>
			 <td align="center"><img src="<?php echo $link_img.$news_status;?>" <?php if($publish == 1){?>onclick="return update_status(<?php echo $row->published; ?>,<?php echo $row->id;?>,<?php echo $row->itemid?>)" <?php } ?> /></td><?php } ?>
            
		</tr>
<?php }
?>	
	</table>
</div>
<table cellpadding="0" cellspacing="0"  border="0"  align="center" width="100%">
<tr>
	<td valign="top" align="center">
	<?php echo $this->pagination->getPagesLinks(); ?>

	</td>
</tr>
<tr>
	<td valign="top" align="center">
		<?php echo $this->pagination->getPagesCounter(); ?>
	</td>
</tr>
</table>
<div style="clear:both; margin-bottom:20px;">
</div>

<input type="hidden" id="view" name="view" value="itemlist" />

<input type="hidden" id="option" name="option" value="com_jek2story" />
<input type="hidden" id="id" name="id" value="" />

<input type="hidden" name="task" id="task" value="" />
<input type="hidden" name="uid" id="uid" value="" />
<input type="hidden" name="nid" id="nid" value="" />
<input type="hidden" name="itemno" id="itemno" value="" />
<input type="hidden" name="categoryid" id="categoryid" value="<?php echo $this->subscribe[0]->categoryid?>" />


<input type="hidden" id="Itemid" name="Itemid" value="<?php echo $Itemid;?>" />
<input type="hidden" name="jelive_url" id="jelive_url" value="<?php echo $url; ?>" />
</form>
<?php if($k2category > 0){?>
<script language="javascript" type="text/javascript">
	var ajax_item_id = <?php echo $item_id1;?>;

	get_item(<?php echo $k2category;?>);
</script>
<?php } ?>
