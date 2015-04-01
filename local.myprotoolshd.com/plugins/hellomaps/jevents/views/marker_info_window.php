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
<div class="jevents_marker_info_window" id="jevents_marker_info_window_<?php echo $markerData['id']; ?>" style="height:<?php echo $markerInfoWindowHeight; ?>;">
    <h1><a href="<?php echo $markerData['eventLink']; ?>" target="_blank"><?php echo $markerData['title']; ?></a></h1>
    <div class="jeventsInfoWindowWrapper1 clearfix">
        <!--<div class="jeventsImgPart">-->
        <div>
            <a href="<?php echo $markerData['eventLink']; ?>"><img width="100%" src="<?php echo $markerData['largeAvatar']; ?>"/></a>
        </div>
        <div class="jeventsTextPart"><?php echo substr($markerData['description'], 0, 275);?></div>
    </div>
    <div><img style="float:left; margin-right:5px; margin-top:3px;" src="<?php echo JURI::root().'plugins/hellomaps/jevents/images/calendar.png'; ?>"/><?php echo "<b>".JText::_('PLG_HELLOMAPS_JEVENTS_STARTDATE').":</b> ".$markerData['startrepeat'];?><br />
               <?php echo "<b>".JText::_('PLG_HELLOMAPS_JEVENTS_ENDDATE').":</b> ".$markerData['endrepeat'];?>
               </div>
    <div class="jeventsInfoWindowWrapper2 clearfix">
        <div class="jeventsPartLeft">
            
            <?php    
            if(!empty($markerData['profileTypeName']))
            {
            ?>
                <div class="row">
                    <?php echo JText::_('PLG_HELLOMAPS_JEVENTS_PROFILE_TYPE_FIELD_TEXT');?>: <?php echo $markerData['profileTypeName'];?>
                </div>
            <?php    
            }
            ?>   
             
        </div>
        <div class="jeventsPartRight">
            <div class="jeventsInfo">
                <a href="<?php echo $markerData['eventLink']; ?>" target="_blank">                    
                    <?php echo JText::_('PLG_HELLOMAPS_JEVENTS_VIEW_PROFILE_LABEL'); ?>   
                    <div class="jeventsArrow">
                        <img src="<?php echo JURI::root().'plugins/hellomaps/jevents/images/rightArrow.png'; ?>" alt="arrow" />
                    </div>                
                </a>
            </div>
            
        </div>
    </div>
</div>