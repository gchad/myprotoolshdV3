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
<div class="cbusers_marker_info_window" id="cbusers_marker_info_window_<?php echo $markerData['id']; ?>" style="height:<?php echo $markerInfoWindowHeight; ?>;">
    <h1><a href="<?php echo $markerData['eventLink']; ?>" target="_blank"><?php echo $markerData['title']; ?></a></h1>
    <div class="cbusersInfoWindowWrapper1 clearfix">
        <div class="cbusersImgPart">
            <a href="<?php echo $markerData['eventLink']; ?>"><img src="<?php echo $markerData['largeAvatar']; ?>"/></a>
        </div>
         <?php /*?><div class="cbusersTextPart"><?php echo substr($markerData['summary'], 0, 275);?></div><?php */?>
    </div>
    <div class="cbusersInfoWindowWrapper2 clearfix">
        <div class="cbusersPartLeft">
            <?php
            if($markerData['location']!="")
            {
            ?>
                <div class="row">
                    <?php echo $markerData['location'];?>
                </div>
            <?php    
            }
            if(!empty($markerData['profileTypeName']))
            {
            ?>
                <div class="row">
                    <?php echo JText::_('PLG_HELLOMAPS_CBUSERS_PROFILE_TYPE_FIELD_TEXT');?>: <?php echo $markerData['profileTypeName'];?>
                </div>
            <?php    
            }
            ?>   
             
        </div>
        <div class="cbusersPartRight">
            <div class="memberInfo">
                <a href="<?php echo $markerData['eventLink']; ?>" target="_blank">                    
                    <?php echo JText::_('PLG_HELLOMAPS_CBUSERS_VIEW_PROFILE_LABEL'); ?>   
                    <div class="memberArrow">
                        <img src="<?php echo JURI::root().'plugins/hellomaps/cbusers/images/rightArrow.png'; ?>" alt="arrow" />
                    </div>                
                </a>
            </div>
            
        </div>
    </div>
</div>