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

/********* GCHAD FIX *****/

JHtml::script('https://maps.googleapis.com/maps/api/js?v=3.exp&signed_in=true&libraries=places');




/********* GCHAD FIX *****/
 //echo $editor->display('fulltext', $text, '100%','100%','80','50'); 

?>


<script language="javascript" type="text/javascript">

    window.addEvent('domready',function(){

        var button = $('storySubmit');
        
        button.addEvent('click',function(e){
             
            var form = $('avidStory');
            e.preventDefault();
   
            var validate = true;
            var focused = false;
            var errorMsg = false;
            var labels = form.getElements('label');
            
            labels.each(function(el){
                el.removeClass('error');
            });
            
            var inputs = form.getElements('input');
           // var inputs = [];
            inputs.each(function(el){
                
                var name = el.name.replace('[]','');
                 
                 //hidden fields, artistname and credits are not compulsory
                if( el.type != 'hidden' && el.name != 'artistName' && el.name != 'K2ExtraField_6'){
                    
                    if( el.value == ""){
                        
                        var validate = false;
                        errorMsg =  ("<?php echo JText::_( 'FILL_ALL FIELDS'); ?>");
                        
                        
                        if( $('label_' + name)){
                            $('label_' + name).addClass('error');
                        } 
                        
                        if(focused == false){
                            el.focus(); 
                            focused = true;
                        }
                    }
                }
                
                if( el.type == 'email'){
                    
                    var emails = el.value.split(/[|,;]/);
                    
                    var reg = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
                    
                    emails.each(function(email){ 
                        
                        if(!reg.test(email.trim())){
                            
                             var validate = false;
                             errorMsg = ("<?php echo JText::_( 'VALID_EMAIL'); ?>");
                             
                             if($('label_' + name)){
                                $('label_' + name).addClass('error');
                             }; 
                             
                             if(focused == false){
                                el.focus(); 
                                focused = true;
                             };
                        }
                    });
                }
            });
            
            var selects = form.getElements('select');
           
            selects.each(function(el){
                
                var name = el.name.replace('[]','');
              
                if( el.value == 0 || el.value == ""){
                        
                        var validate = false;
                        errorMsg =  ("<?php echo JText::_( 'FILL_ALL FIELDS'); ?>");
                       
                        if( $('label_' +  name)){
                            $('label_' + name).addClass('error');
                        };
                        
                        if(focused == false){
                            el.focus(); 
                            focused = true;
                        };
                    }
                
            });
            
            //editor
            var story = tinyMCE.editors[0].getContent();

            if( story.trim() == ""){
                    
                var validate = false;
                errorMsg =  ("<?php echo JText::_( 'FILL_ALL FIELDS'); ?>");
               
                if( $('label_fulltext')){
                    $('label_fulltext').addClass('error');
                } 
                
                if(focused == false){
                    tinyMCE.activeEditor.focus(); 
                    focused = true;
                }
            };
      
   
            //terms
            if (form.accept.checked == false) {
                
                errorMsg =  ( "<?php echo  JText::_( 'ERR_TERMS_COND'); ?>" );
                form.accept.focus();
            }  
            
            //file size 
            if (window.File && window.FileReader && window.FileList && window.Blob){
               
                var file = $$('input[type="file"]')[0].files[0];
                if(file){
                    var size = file.size;
                    if(size > 2100000){
                        errorMsg =  ( "<?php echo  JText::_( 'FILE_TOO_BIG'); ?>" );
                    }
                }
             }


            
           if(errorMsg){
               alert(errorMsg);
               return false;
           }

    
            if(validate == true){
                
                var place = googleMap.getPlace();
                var adLat = place.geometry.location.A;
                var adLong = place.geometry.location.F;
                $('city').value = null;
                var city = place.vicinity;
                
                if(adLat){
                     $('address_lat').value =  adLat;
                }
                
                if(adLong){
                     $('address_long').value = adLong;
                }
                
                if(city){
                    $('city').value = city;
                }
                
                jQuery('#jak2-loadingWrap').css({'display': 'block'});
                
                form.submit();
            }           
          
        });
    });
    

	window.addEvent('domready',function(){
        window.googleMap = new google.maps.places.Autocomplete( (document.getElementById('address')), { types: ['geocode'] });
    });
    
    function insertPlaceHolders(){
        
    }
    
</script>

<?php
	/*$res_file=$url.'index.php?template=component&option='.$option.'&view=request_get';
	$img=$url.'images/stories/';
	$imgbin=$url.'components/'.$option.'/images/bin.png';*/
?>

<div id="jak2-loadingWrap" style="display: none;">
<div id="jak2-loading" ><?=JText::_('UPLOADING')?></div>
</div>

<div class="login-wrap2">

  <div class="login ">
    <!--<h3><?php echo  $this->res->title; ?></h3>-->
    <h3><?=JText::_('STORY_TITLE')?></h3>
    <p><?=JText::_('STORY_SUBHEADER')?></p>
    <form id="avidStory" action="<?php echo $link;?>" method="post" enctype="multipart/form-data" onSubmit="return submitbutton()" >

        <table class="contenettable" style="width:100%;" ><?php 
            
        		if($setting->name == 1){?>
        		    
                	<tr>
            			<td><label id="label_name"><?php echo  JText::_( 'NAME'); ?></label></td>
            	 	</tr>
            	 	
            	 	<tr> 
            			<td>
            			    <input placeholder="<?php echo  JText::_( 'NAME_PLACEHOLDER');?>" class="inputbox"  type="text" name="name" size="50" maxlength="100" value="<?php echo isset($_SESSION['story']['name']) ? $_SESSION['story']['name'] : ''; ?>" />
            		    </td>
            		</tr><?php 
                }	
                
                if($setting->email == 1){?>		
            	 	<tr>
            			<td><label id="label_email"><?php echo  JText::_( 'EMAIL'); ?></label></td>
            	 	</tr>
            	 	
            	 	<tr> 
            			<td>
            			    <input placeholder="<?php echo  JText::_( 'ME@EXAMPLE'); ?>" class="inputbox" type="email" name="email" size="50" maxlength="100" value="<?php echo isset($_SESSION['story']['email']) ? $_SESSION['story']['email'] : ''; ?>" />
            		    </td>
            		</tr><?php 
                }?>
        	
        	 	
        		
        		<tr>
                    <td><label id="label_facilityName"><?php echo  JText::_( 'FACILITY'); ?></label></td>
                </tr>
                
                <tr> 
                    <td>
                        <input placeholder="<?=JText::_( 'FACILITY_PLACEHOLDER')?>" class="inputbox" type="text" name="facilityName" size="50" maxlength="100" value="<?php echo isset($_SESSION['story']['facilityName']) ? $_SESSION['story']['facilityName'] : ''; ?>" />
                    </td>
                </tr>
                
                <tr>
                    <td><label  id="label_artistName"><?php echo JText::_( 'ARTIST'); ?></label></td>
                </tr>
                
                <tr> 
                    <td>
                        <input placeholder="<?=JText::_( 'ARTIST_PLACEHOLDER')?>" class="inputbox" type="text" name="artistName" size="50" maxlength="100" value="<?php echo isset($_SESSION['story']['artistName']) ? $_SESSION['story']['artistName'] : ''; ?>" />
                    </td>
                </tr>
                <?php
        		
        		/********* GCHAD FIX *****/?>
        			
        		<tr>
                    <td><label id="label_address"><?php echo JText::_( 'LOCATION'); ?> *</label>{tip <?=JText::_('MAP_TOOLTIP');?>}?{/tip}</td>
                </tr>
                <tr> 
                    <td>
                        <input  onpaste="event.preventDefault(); alert('<?= JText::_( 'TYPE_ADDRESS_MANUALLY')?>')" id="address" class="inputbox" type="text" name="address" size="50"  value="" />
                        <input  id="address_long" class="inputbox" type="hidden" name="address_long" value="" />
                        <input  id="address_lat" class="inputbox" type="hidden" name="address_lat" value="" />
                        
                        <!-- city -->
                        <input type="hidden" name="K2ExtraField_21" id="city" value="">
                    </td>
                </tr><?php
                
                
                /******* GHAD FIX ******/
                
                if($this->res->category == "1") { ?>
            		
            		<tr>
             			<td><label id="label_catid" align="left"> <?php echo JText::_( 'CATEGORY'); ?></label></td>
            		</tr>
            		<tr>
            			<td><?= $this->lists['catid']; ?></td>
            		</tr><?php 
            		
                } else { ?>
         
                    <input  type="hidden" name="catid" id="catid" value="<?php echo $this->res->cat_id;?> *" /><?php 
                    
                } ?>
                
        		<tr>
        		    <td style="padding-left: 0px;">
        		        <table id="extrafield_data" style="display:none;"></table>
                    </td>
        		</tr>
         		
         	
        	 	<tr>
        	 		<td><label id="label_fulltext" align="left"><?php echo  JText::_( 'MAINTEXT'); ?> *</label>{tip <?=JText::_('STORY_TOOLTIP');?>}?{/tip}</td>
        		</tr>
        		
          		<tr>
        			<td style="max-height: 300px;"><?php
        			
            			if($id){
            			    
            				$text = '';
            				$introtext =  $this->detail->introtext;
            				$fulltext = $this->detail->fulltext;
                            
            				if($fulltext != ''){
            					$tag = '<hr id="system-readmore">';
            					$text = $introtext.' '.$tag.' '.$fulltext;
            				} else {
            					$text = $introtext;
            				}	
            				
            				echo $editor->display('fulltext',$text,'$widthPx','$heightPx','80','50','0');
                            
            			} else {
            		               
        		           $text = isset($_SESSION['story']['fulltext']) ? $_SESSION['story']['fulltext'] : '';
                			 
                          //  $name, $html, $width, $height, $col, $row, $buttons = true, $id = null, $asset = null, $author = null, $params = array()
                			 echo $editor->display('fulltext', $text, '100%','auto','80','11'); 
                			 //$editor->display("fulltext",$longtext,'$widthPx','$heightPx','80','50','0'); 
            			 }?>
            			 
        		   </td>
         		</tr>
         		
        		<tr>
        		  <td><label id="label_itemimage" ><?php echo  JText::_( 'UPLOAD_IMAGE'); ?> *</label>{tip <?=JText::_('PHOTO_TOOLTIP');?>}?{/tip}</td>
        		</tr>
        		
        		<tr>
                    <td>
                        <input required  type="file" name="itemimage" id="itemimage"  /><?php 
        		          
        		          if($id) { ?>
        		              <img src="<?php echo $this->detail->imageXSmall;?>" border="0"/><?php 
                          }?>
        	       </td>
        		</tr><?php 
        		
        		
        		//Captcha
        		if($this->res->captcha == "1" && $id==0) {
        		    
        			$user =& JFactory::getUser();
        			$dest = $url.'index.php?option='.$option.'&view=jesubmit&task=captchacr&tmpl=component&ac='.rand();?>
        
            		<tr>
            			<td>
            				<table cellpadding="0" cellspacing="0" border="0">
            				    <tr>
            				        <td colspan="2"><b><?php echo JText::_( 'WORD_VARIFICATION' ); ?></b></td></tr>
            				    <tr>
            				        <td><div id="default_cap_div"><img src="<?php echo $dest;?>" /></div>
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
            		</tr><?php 
            		
                } else { ?> 
        		    
        			<input type="hidden" name="cpt" id="cpt" value="0" /><?php 
                }	
        			
        		$model = $this->getModel ( 'jesubmit' );
        		$setting=$model->getcheck1(); 
        		
                //Terms & conditions
        		if($setting->term == 1 && $id==0) {?>
        		    
            		<tr>
            	        <td class="terms">
                			<input required type="checkbox" id="acc" name="accept" value="accept" />
                			
                			<?php $linkTerms = JRoute::_('index.php?option=com_content&view=article&id=26&Itemid=293&tmpl=component');?>
                			
                			<a href="<?=$linkTerms?>" target="_blank">
                			    <?=JText::_( 'ACCEPT_TERMS_CONDITION'); ?>
            			    </a>
                            <input type="hidden" id="termaccept" name="termaccept" value="1" />
                        </td>
            		</tr><?php
        		 
                } else { ?>
                    
            	   <input type="hidden" id="termaccept" name="termaccept" value="0" /><?php 
                }?>
                
            	<tr>
            		<td>	
            			<input type="submit" name="save" id="storySubmit" class="button2"  value="<?php echo  JText::_( 'SUBMIT_BUTTON'); ?>"  />
            			<!--<input type="button" name="cancel" onClick="history.go(-1)" class="button2"  value="<?php echo  JText::_( 'CANCEL_BUTTON'); ?>"  />-->
            		</td>
            	</tr>
            	
            </table>
        
           
        
        	<?php 
        		//echo $this->res->auto_publish; 
        		if($this->res->publish==1) {
        			$publish	= 1;
        		} else {
        			$publish	= 0;
                }	
                
        		if($id) {
                    $k2itemid = $this->detail->itemid;
        		} else {
                    $k2itemid = 0;
                }?>
                
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
        	
        	<?php echo JHTML::_( 'form.token' );?>
            
        </form>
        
    </div>

</div>


<?php 


if($id) { ?>
    <script language="javascript" type="text/javascript">
    select_cate(<?php echo $this->detail->catid;?>);
    </script><?php 
} ?>
