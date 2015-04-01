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
<div class="hellomap_search_result cbusers_member_result" id="cbusers_result_<?php echo $row['id']; ?>">
    <div class="cbusers_result_item_wrapper">
         <h1><a href="<?php echo $row['eventLink']; ?>" target="_blank"><?php echo $row['title']; ?></a></h1>
    </div>
    <div class="cbusers_result_item_wrapper clearfix">
        <div class="cbusersInfoWindowWrapper1 clearfix">
        	<div class="cbusersImgPart">
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
			 	<img class="focus_marker" src="<?php echo JURI::root().'plugins/hellomaps/cbusers/images/nouserimg.png'; ?>"/></a>
             <?php   
			 } 
			 ?>
            </div>
               <?php /*?><div class="cbusersTextPart"><?php echo substr($row['summary'], 0, 275);?></div><?php */?>
        </div>
    </div>
    <?php
	
    $enable_show_more_less = false;
    
    ?>
    <div class="cbusers_result_item_wrapper cbusers_different_box clearfix">
        <div class="cbusersPartLeft">
              <div class="row">
                </div>
                <?php
                if($row['location']!="")
                {
                ?>
                    <div class="row">
                        <?php echo $row['location'];?>
                    </div>
                <?php    
                }
                if(!empty($row['profileTypeName']))
                {
                ?>
                    <div class="row">
                        <?php echo JText::_('PLG_HELLOMAPS_CBUSERS_PROFILE_TYPE_FIELD_TEXT'); ?>: <?php echo $row['profileTypeName'];?>
                    </div>
                <?php    
                }
                ?>   
            
           
        </div>
        <div class="cbusersPartRight">
            <div class="cbusersAvatar">
              <a href="<?php echo $row['eventLink']; ?>" target="_blank">                    
                    <?php echo JText::_('PLG_HELLOMAPS_CBUSERS_VIEW_PROFILE_LABEL'); ?> </a>
                <a href="javascript:void(0);" class="collapsed_cbusers_data_show_more" data-ad="<?php echo $row['id']; ?>" title="<?php echo JText::_('PLG_HELLOMAPS_CBUSERS_SHOW_MORE_LABEL'); ?>">
                    <img src="<?php echo JURI::root().'plugins/hellomaps/cbusers/images/rightArrow.png'; ?>" alt="arrow" />
                </a>
                <a href="javascript:void(0);" class="collapsed_cbusers_data_show_less" data-ad="<?php echo $row['id']; ?>" style="display: none;" title="<?php echo JText::_('PLG_HELLOMAPS_CBUSERS_SHOW_LESS_LABEL'); ?>">
                    <img src="<?php echo JURI::root().'plugins/hellomaps/cbusers/images/leftArrow.png'; ?>" alt="arrow" />
                </a>
            </div>  
                  
        </div>
    </div> 
     <div class="collapsed_cbusers_data">
        <?php
        if(!empty($row['extraFields']))
        {
            foreach($row['extraFields'] as $extraField)
            {
            ?>
                <div class="cbusersSearchResultWrapper clearfix">
                    <div class="extraFieldTitle"><?php echo JText::_($extraField['label']); ?></div>
                    <div class="extraFieldContent"><?php echo $extraField['value']; ?></div>
                </div>
            <?php    
            }
        }
        ?>
        <div class="cbusersSearchResultWrapper diffrent_div clearfix">
            <div class="qrCodeImage"><img src="<?php echo $row['qr_code_img']; ?>" /></div>
            <div class="extraFieldContent diffrent_content_div">
                <img src="<?php echo JURI::base().'plugins/hellomaps/cbusers/images/as-location.png'; ?>" />
                <a href="javascript:void(0);" onclick="HelloMapsSearch.openStreetView(<?php echo $row['latitude']; ?>,<?php echo $row['longitude']; ?>);"><?php echo JText::_('PLG_HELLOMAPS_MEMBERS_STREETVIEW_AVAILABLE_TEXT'); ?></a>
            </div>
        </div>
    </div> 
</div>
