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
<div class="jomsocial_marker_info_window" id="jomsocial_marker_info_window_<?php echo $markerData['id']; ?>" style="height:<?php echo $markerInfoWindowHeight; ?>;">
    <h1><a href="<?php echo $markerData['profileLink']; ?>" target="_self"><?php echo $markerData['title']; ?></a></h1>
    <div class="jomsocialInfoWindowWrapper1 clearfix">
        <div class="memberImgPart">
            <a href="<?php echo $markerData['profileLink']; ?>"><img src="<?php echo $markerData['largeAvatar']; ?>"/></a>
            <?php
            if($markerData['isOnline'])
            {
            ?>
                <img class="online_status" src="<?php echo JURI::root().'plugins/hellomaps/members/images/online_icon.png'; ?>"/>
            <?php    
            }
            else
            {
            ?>
                <img class="offline_status" src="<?php echo JURI::root().'plugins/hellomaps/members/images/offline_icon.png'; ?>"/>
            <?php    
            }
            ?>
        </div>
        <div class="memberConnectionPMSLinks">
            <?php
            if(!$markerData['isFriend'])
            {
            ?>
                <a href="javascript:void(0);" class="add_button" onclick="joms.friends.connect('<?php echo $markerData['id']; ?>')"><?php echo JText::_('COM_COMMUNITY_FRIENDS_ADD_BUTTON'); ?></a>
            <?php    
            }
            ?>            
            <a href="javascript:void(0);" class="send_pm_button" onclick="<?php echo CMessaging::getPopup($markerData['id']); ?>"><?php echo JText::_('COM_COMMUNITY_EVENTS_PM_ME'); ?></a>
        </div>
        <div class="memberTextPart"><?php echo $markerData['latestStatus']; ?></div>
    </div>
    <div class="jomsocialInfoWindowWrapper2 clearfix">
        <div class="memberPartLeft">
            <div class="row">
                <?php echo JText::_('COM_COMMUNITY_MEMBER_SINCE');?>:
				<?php //echo date("D, y M",strtotime($markerData['memberSince']->__toString())); 
                      echo JHTML::_('date',strtotime($markerData['memberSince']->__toString()),JText::_('DATE_FORMAT_LC3'));?>
            </div>
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
                    <?php echo JText::_('COM_COMMUNITY_PROFILE_TYPE');?>:<?php echo $markerData['profileTypeName'];?>
                </div>
            <?php    
            }
            ?>   
            
            <div class="row">
                <?php echo JText::_('COM_COMMUNITY_LAST_LOGIN');?>:<?php echo $markerData['lastLogin'];?>
            </div>   
        </div>
        <div class="memberPartRight">
            <div class="memberKarma">
                <img src="<?php echo $markerData['karmaImgUrl']; ?>" /> <br />
                <a href="<?php echo $markerData['profileLink']; ?>" target="_self">                    
                    <?php echo JText::_('COM_COMMUNITY_GO_TO_PROFILE'); ?>   
                    <div class="adsmanagerArrow">
                        <img src="<?php echo JURI::root().'plugins/hellomaps/members/images/rightArrow.png'; ?>" alt="arrow" />
                    </div>                
                </a>
            </div>
            
        </div>
    </div>
</div>