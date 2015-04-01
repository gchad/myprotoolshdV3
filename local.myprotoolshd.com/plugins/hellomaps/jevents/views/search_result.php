<?php
/**
 * @version     1.0.7
 * @package     com_hellomaps
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      JoomlaForce Team <support@joomlaforce.com> - http://www.joomlaforce.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');
?>
<div class="hellomap_search_result jevents_member_result" id="jevents_result_<?php echo $row['id']; ?>">
    <div class="jevents_result_item_wrapper">
         <h1><a href="<?php echo $row['eventLink']; ?>" target="_blank"><?php echo $row['title']; ?></a></h1>
    </div>
    <div class="jevents_result_item_wrapper clearfix">
        <div class="jeventsInfoWindowWrapper1 clearfix">
        	<div class="jeventsImgPart">
             <?php
             if($row['largeAvatar'])
			 {
			 ?>
            	<img class="focus_marker" src="<?php echo $row['largeAvatar']; ?>"/></a>
             <?php
			 }
			 else
			 {
			 ?>
			 	<img class="focus_marker" src="<?php echo JURI::root().'plugins/hellomaps/jevents/images/nouserimg.png'; ?>"/></a>
             <?php   
			 } 
			 ?>
            </div>
            
               <div class="jeventsTextPart"><?php echo substr($row['description'], 0, 275);?></div>
        </div>
        <div><img style="float:left; margin-right:5px; margin-top:3px;" src="<?php echo JURI::root().'plugins/hellomaps/jevents/images/calendar.png'; ?>"/><?php echo "<b>".JText::_('PLG_HELLOMAPS_JEVENTS_STARTDATE').":</b> ".$row['startrepeat'];?><br />
               <?php echo "<b>".JText::_('PLG_HELLOMAPS_JEVENTS_ENDDATE').":</b> ".$row['endrepeat'];?>
               </div>
    </div>
    <?php
	
    $enable_show_more_less = false;
    
    ?>
    <div class="jevents_result_item_wrapper jevents_different_box clearfix">
        <div class="jeventsPartLeft">
              <div class="row">
                </div>
               
                <?php    
                if(!empty($row['profileTypeName']))
                {
                ?>
                    <div class="row">
                        <?php echo JText::_('PLG_HELLOMAPS_JEVENTS_PROFILE_TYPE_FIELD_TEXT'); ?>: <?php echo $row['profileTypeName'];?>
                    </div>
                <?php    
                }
                ?>   
            
           
        </div>
        <div class="jeventsPartRight">
            <div class="jeventsAvatar">
              <a href="<?php echo $row['eventLink']; ?>" target="_blank">                    
                    <?php echo JText::_('PLG_HELLOMAPS_JEVENTS_VIEW_PROFILE_LABEL'); ?> </a>
                <a href="javascript:void(0);" class="collapsed_jevents_data_show_more" data-ad="<?php echo $row['id']; ?>" title="<?php echo JText::_('PLG_HELLOMAPS_JEVENTS_SHOW_MORE_LABEL'); ?>">
                    <img src="<?php echo JURI::root().'plugins/hellomaps/jevents/images/rightArrow.png'; ?>" alt="arrow" />
                </a>
                <a href="javascript:void(0);" class="collapsed_jevents_data_show_less" data-ad="<?php echo $row['id']; ?>" style="display: none;" title="<?php echo JText::_('PLG_HELLOMAPS_JEVENTS_SHOW_LESS_LABEL'); ?>">
                    <img src="<?php echo JURI::root().'plugins/hellomaps/jevents/images/leftArrow.png'; ?>" alt="arrow" />
                </a>
            </div>  
                  
        </div>
    </div> 
     <div class="collapsed_jevents_data">
        <?php
        if(!empty($row['extraFields']))
        {
            foreach($row['extraFields'] as $extraField)
            {
            ?>
                <div class="jeventsSearchResultWrapper clearfix">
                    <div class="extraFieldTitle"><?php echo JText::_($extraField['label']); ?></div>
                    <div class="extraFieldContent"><?php echo $extraField['value']; ?></div>
                </div>
            <?php    
            }
        }
        ?>
        <div class="jeventsSearchResultWrapper diffrent_div clearfix">
            <div class="qrCodeImage"><img src="<?php echo $row['qr_code_img']; ?>" /></div>
            <div class="extraFieldContent diffrent_content_div">
                <img src="<?php echo JURI::base().'plugins/hellomaps/jevents/images/as-location.png'; ?>" />
                <a href="javascript:void(0);" onclick="HelloMapsSearch.openStreetView(<?php echo $row['latitude']; ?>,<?php echo $row['longitude']; ?>);"><?php echo JText::_('PLG_HELLOMAPS_MEMBERS_STREETVIEW_AVAILABLE_TEXT'); ?></a>
            </div>
        </div>
    </div> 
</div>
