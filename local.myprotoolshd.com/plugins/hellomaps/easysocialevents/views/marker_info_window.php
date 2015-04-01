<?php
/**
 * @version     1.0.8
 * @package     com_hellomaps
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      JoomlaForce Team <support@joomlaforce.com> - http://www.joomlaforce.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');
?>
<div class="easysocialevents_marker_info_window" id="easysocialevents_marker_info_window_<?php echo $markerData['id']; ?>" style="height:<?php echo $markerInfoWindowHeight; ?>;">
    <h1><a href="<?php echo $markerData['profileLink']; ?>" target="_self"><?php echo $markerData['title']; ?></a></h1>
    <div class="easysocialeventsInfoWindowWrapper1 clearfix">
        <div class="easysocialeventsImgPart">
            <a href="<?php echo $markerData['profileLink']; ?>"><img src="<?php echo $markerData['largeAvatar']; ?>"/></a>
         <?php /*?>   <?php
            if($markerData['isOnline'])
            {
            ?>
                <img class="online_status" src="<?php echo JURI::root().'plugins/hellomaps/events/images/online_icon.png'; ?>"/>
            <?php    
            }
            else
            {
            ?>
                <img class="offline_status" src="<?php echo JURI::root().'plugins/hellomaps/events/images/offline_icon.png'; ?>"/>
            <?php    
            }
            ?><?php */?>
        </div>
   		   <div class="easysocialeventsConnectionPMSLinks"></div>
    	   <div class="easysocialeventsTextPart"><?php echo strip_tags($markerData['latestStatus']); ?></div>
           <div id="badges">
             <?php
                    
                    $my     = Foundry::user($markerData['id']);
                    $badges = $my->getBadges();
                    // Loop through each of the badges
                    if( $badges )
                    {
                        foreach( $badges as $badge )
                        {
                            echo '<img style="width:16px; float:left; padding:2px;" src="' . $badge->getAvatar() . '" />';
                        }
        }
                    ?>
                    
            </div>
    </div>

   
    <div class="easysocialeventsInfoWindowWrapper2 clearfix">
        <div class="easysocialeventsPartFull">
            <div class="row">
                <?php echo JText::_('PLG_HELLOMAPS_EASYSOCIALEVENTS_EVENT_SINCE_LABEL');?>:<br />
<?php echo $markerData['eventdate'];?>
            </div>
            
            <div class="row">
              <?php  echo JText::_('PLG_HELLOMAPS_EASYSOCIALEVENTS_EVENT_TYPE');?>: <?php echo $markerData['eventType']; ?>
            </div>
            
            <?php
            if($markerData['location']!="")
            {
            ?>
                <div class="row">
              	    <?php echo JText::_('PLG_HELLOMAPS_EASYSOCIALEVENTS_LOCATION_LABEL');?>: 
                    <?php echo $markerData['location'];?>
                </div>
            <?php    
            }
            if(!empty($markerData['profileTypeName']))
            {
            ?>
                <div class="row">
                    <?php echo JText::_('PLG_HELLOMAPS_EASYSOCIALEVENTS_PROFILE_TYPE');?>:<?php echo $markerData['profileTypeName'];?>
                </div>
            <?php    
            }
            ?>   
            
           <?php /*?> <div class="row">
               <?php echo JText::_('PLG_HELLOMAPS_EASYSOCIALEVENTS_LAST_ONLINE_LABEL');?>:<?php echo $markerData['lastLogin'];?>
            </div>   <?php */?>
        </div>
        <div class="easysocialeventsPartRight">
            <div class="eventInfo">
                <a href="<?php echo $markerData['profileLink']; ?>" target="_self">                    
                    <?php echo JText::_('PLG_HELLOMAPS_EASYSOCIALEVENTS_VIEW_PROFILE_LABEL'); ?>   
                   <!-- <div class="eventArrow">-->
                        <img src="<?php echo JURI::root().'plugins/hellomaps/easysocialevents/images/rightArrow.png'; ?>" alt="arrow" />
                   <!-- </div>         -->       
                </a>
            </div>
            
        </div>
    </div>
</div>