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
<div class="users_marker_info_window" id="users_marker_info_window_<?php echo $markerData['id']; ?>" style="width:<?php echo $markerInfoWindowWidth; ?>;height:<?php echo $markerInfoWindowHeight; ?>;">
    <h3><a href="<?php echo $markerData['profileLink']; ?>" target="_blank"><?php echo $markerData['title']; ?></a></h3>
    <div class="usersInfoWindowWrapper1 clearfix">
        <div class="userImgPart"><?php 
			 if($markerData['largeAvatar'])
			 {
			 ?>
            	<a href="<?php echo $markerData['profileLink']; ?>"><img style="float:left; margin:5px;" src="<?php echo $markerData['largeAvatar']; ?>"/></a>
            <?php
			 }
			 else
			 {
			 ?>
			 	<img style="float:left; margin:5px;" src="<?php echo JURI::root().'plugins/hellomaps/users/images/no-image.png'; ?>"/>
            <?php    
            }

            ?>
        </div>
        <div class="userConnectionPMSLinks">
        </div>
        <div class="userTextPart"><?php echo strip_tags($markerData['latestStatus']); ?></div>
    </div>
    <div class="usersInfoWindowWrapper2 clearfix">
        <div class="userPartLeft">
         <div>
                <?php echo JText::_('PLG_HELLOMAPS_USERS_MEMBER_SINCE_LABEL');?>:<?php echo $markerData['memberSince'];?>
            </div>
            <?php
            if($markerData['location']!="")
            {
            ?>
                <div>
                    <?php echo JText::_('PLG_HELLOMAPS_USERS_LOCATION_LABEL');?>:<?php echo $markerData['location'];?>
                </div>
            <?php    
            }
            if(!empty($markerData['profileTypeName']))
            {
            ?>
                <div>
                    <?php echo JText::_('PLG_HELLOMAPS_USERS_PROFILE_TYPE');?>:<?php echo $markerData['profileTypeName'];?>
                </div>
            <?php    
            }
            ?>   
            
            <div>
                <?php echo JText::_('PLG_HELLOMAPS_USERS_LAST_ONLINE_LABEL');?>:<?php echo $markerData['lastLogin'];?>
            </div>   
        </div>
        <div class="userPartRight">
                  <?php /*?> <a href="<?php echo $markerData['profileLink']; ?>" target="_blank">                    
                    <?php echo JText::_('PLG_HELLOMAPS_USERS_VIEW_PROFILE_LABEL'); ?>   
                    <div class="usersArrow">
                        <img src="<?php echo JURI::root().'plugins/hellomaps/users/images/rightArrow.png'; ?>" alt="arrow" />
                    </div>                
                </a><?php */?>
        </div>
    </div>
</div>