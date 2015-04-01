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
<div class="jomsocialevents_marker_info_window" id="jomsocialevents_marker_info_window_<?php echo $markerData['id']; ?>" style="height:<?php echo $markerInfoWindowHeight; ?>;">
    <h1><a href="<?php echo $markerData['eventLink']; ?>" target="_self"><?php echo $markerData['title']; ?></a></h1>
    <div class="jomsocialeventsInfoWindowWrapper1 clearfix">
        <div class="jomsocialeventsImgPart">
            <a href="<?php echo $markerData['eventLink']; ?>"><img src="<?php echo $markerData['largeAvatar']; ?>"/></a>
        </div>
         <div class="jomsocialeventsTextPart"><?php echo substr($markerData['summary'], 0, 275);?></div>
    </div>
    <div class="jomsocialeventsInfoWindowWrapper2 clearfix">
        <div class="jomsocialeventsPartLeft">
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
                    <?php echo JText::_('COM_COMMUNITY_EVENTS_CATEGORY');?>: <?php echo $markerData['profileTypeName'];?>
                </div>
            <?php    
            }
            ?>   
             
        </div>
        <div class="jomsocialeventsPartRight">
            <div class="memberInfo">
                <a href="<?php echo $markerData['eventLink']; ?>" target="_self">                    
                    <?php echo JText::_('COM_COMMUNITY_EVENTS_DETAIL'); ?>   
                    <div class="memberArrow">
                        <img src="<?php echo JURI::root().'plugins/hellomaps/jomsocialevents/images/rightArrow.png'; ?>" alt="arrow" />
                    </div>                
                </a>
            </div>
            
        </div>
    </div>
</div>