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
$language = JFactory::getLanguage();
$language->load('plg_hellomaps_jomsocialevents', JPATH_ADMINISTRATOR);
?>
<div class="hellomap_search_result jomsocialevents_member_result" id="jomsocialevents_result_<?php echo $row['id']; ?>">
    <div class="jomsocialevents_result_item_wrapper">
         <h1><a href="<?php echo $row['eventLink']; ?>" target="_self"><?php echo $row['title']; ?></a></h1>
    </div>
    <div class="jomsocialevents_result_item_wrapper clearfix">
        <div class="jomsocialeventsInfoWindowWrapper1 clearfix">
        	<div class="jomsocialeventsImgPart">
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
			 	<img class="focus_marker" src="<?php echo JURI::root().'plugins/hellomaps/jomsocialevents/images/nouserimg.png'; ?>"/></a>
             <?php   
			 } 
			 ?>
            </div>
                <div class="jomsocialeventsTextPart"><?php echo substr($row['summary'], 0, 275);?></div>
        </div>
    </div>
    <?php
	
    $enable_show_more_less = false;
    
    ?>
    <div class="jomsocialevents_result_item_wrapper jomsocialevents_different_box clearfix">
        <div class="jomsocialeventsPartLeft">
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
                        <?php echo JText::_('COM_COMMUNITY_EVENTS_CATEGORY'); ?>: <?php echo $row['profileTypeName'];?>
                    </div>
                <?php    
                }
                ?>   
            
           
        </div>
        <div class="jomsocialeventsPartRight">
            <div class="jomsocialeventsAvatar">
              <a href="<?php echo $row['eventLink']; ?>" target="_self">
                    <?php echo JText::_('COM_COMMUNITY_EVENTS_DETAIL'); ?> </a>
                <a href="javascript:void(0);" class="collapsed_jomsocialevents_data_show_more" data-ad="<?php echo $row['id']; ?>" title="<?php echo JText::_('PLG_HELLOMAPS_JOMSOCIALEVENTS_SHOW_MORE_LABEL'); ?>">
                    <img src="<?php echo JURI::root().'plugins/hellomaps/jomsocialevents/images/rightArrow.png'; ?>" alt="arrow" />
                </a>
                <a href="javascript:void(0);" class="collapsed_jomsocialevents_data_show_less" data-ad="<?php echo $row['id']; ?>" style="display: none;" title="<?php echo JText::_('PLG_HELLOMAPS_JOMSOCIALEVENTS_SHOW_LESS_LABEL'); ?>">
                    <img src="<?php echo JURI::root().'plugins/hellomaps/jomsocialevents/images/leftArrow.png'; ?>" alt="arrow" />
                </a>
            </div>  
                  
        </div>
    </div> 
     <div class="collapsed_jomsocialevents_data">
        <?php
        if(!empty($row['extraFields']))
        {
            foreach($row['extraFields'] as $extraField)
            {
            ?>
                <div class="jomsocialeventsSearchResultWrapper clearfix">
                    <div class="extraFieldTitle">
					<?php 
					$templang= "PLG_HELLOMAPS_JOMSOCIALEVENTS_".strtoupper($extraField['label']);
					echo JText::_($templang); ?>
                    </div>
                    <div class="extraFieldContent"><?php echo $extraField['value']; ?></div>
                </div>
            <?php    
            }
        }
        ?>
        <div class="jomsocialeventsSearchResultWrapper diffrent_div clearfix">
            <div class="qrCodeImage"><img src="<?php echo $row['qr_code_img']; ?>" /></div>
            <div class="extraFieldContent diffrent_content_div">
                <img src="<?php echo JURI::base().'plugins/hellomaps/jomsocialevents/images/as-location.png'; ?>" />
                <a href="javascript:void(0);" onclick="HelloMapsSearch.openStreetView(<?php echo $row['latitude']; ?>,<?php echo $row['longitude']; ?>);"><?php echo JText::_('PLG_HELLOMAPS_MEMBERS_STREETVIEW_AVAILABLE_TEXT'); ?></a>
            </div>
        </div>
    </div> 
</div>
