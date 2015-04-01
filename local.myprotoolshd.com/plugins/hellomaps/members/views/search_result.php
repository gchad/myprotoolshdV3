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
<div class="hellomap_search_result jomsocial_member_result" id="jomsocial_member_result_<?php echo $row['id']; ?>">
    <h1><a href="<?php echo $row['profileLink']; ?>" target="_self"><?php echo $row['title']; ?></a></h1>
    <div class="jomsocialSearchResultWrapper clearfix">        
        <div class="jomsocialSearchResultWrapper1 clearfix">
            <div class="memberImgPart">
                <a href="javascript:void(0);" class="focus_marker"><img src="<?php echo $row['thumb']; ?>"/></a>
                <?php
                if($row['isOnline'])
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
                if(!$row['isFriend'])
                {
                ?>
                    <a href="javascript:void(0);" class="add_button" onclick="joms.friends.connect('<?php echo $row['id']; ?>')"><?php echo JText::_('COM_COMMUNITY_FRIENDS_ADD_BUTTON'); ?></a>
                <?php    
                }
                ?>            
                <a href="javascript:void(0);" class="send_pm_button" onclick="<?php echo CMessaging::getPopup($row['id']); ?>"><?php echo JText::_('COM_COMMUNITY_EVENTS_PM_ME'); ?></a>
            </div>
            <div class="memberTextPart"><?php echo $row['latestStatus']; ?></div>
        </div>
        <div class="jomsocialSearchResultWrapper2 clearfix">
            <div class="memberPartLeft">
                <div class="row">
                    <?php echo JText::_('COM_COMMUNITY_MEMBER_SINCE');?>:
					<?php //echo date("D, y M",strtotime($row['memberSince']->__toString()));
                    	  echo JHTML::_('date',strtotime($row['memberSince']->__toString()),JText::_('DATE_FORMAT_LC3'));?>
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
                        <?php echo JText::_('COM_COMMUNITY_PROFILE_TYPE');?>:<?php echo $row['profileTypeName'];?>
                    </div>
                <?php    
                }
                ?>   
                
                <div class="row">
                    <?php echo JText::_('COM_COMMUNITY_LAST_LOGIN');?>:<?php echo $row['lastLogin'];?>
                </div>   
            </div>
            <div class="memberPartRight">
                <div class="memberKarma">
                    <a href="<?php echo $row['profileLink']; ?>" target="_self"> <?php echo JText::_('COM_COMMUNITY_GO_TO_PROFILE'); ?> &nbsp;</a>
                  
                    <a href="javascript:void(0);" class="collapsed_member_data_show_more" data-member="<?php echo $row['id']; ?>" title="<?php echo JText::_('PLG_HELLOMAP_MEMBERS_SHOW_MORE_LABEL'); ?>">
                        <img src="<?php echo JURI::root().'plugins/hellomaps/members/images/rightArrow.png'; ?>" alt="arrow" />
                    </a>
                    <a href="javascript:void(0);" class="collapsed_member_data_show_less" data-member="<?php echo $row['id']; ?>" style="display: none;" title="<?php echo JText::_('PLG_HELLOMAP_MEMBERS_SHOW_LESS_LABEL'); ?>">
                        <img src="<?php echo JURI::root().'plugins/hellomaps/members/images/leftArrow.png'; ?>" alt="arrow" />
                    </a>
                </div>  
            </div>
        </div>
    </div>
    <div class="jomsocialSearchResultWrapper totalCounterItem clearfix">
        <?php echo $row['totalVideos']; echo ($row['totalVideos'] > 1)?JText::_('COM_COMMUNITY_VIDEOS'):JText::_('COM_COMMUNITY_VIDEOS');?> 
        <?php echo $row['totalPhotos']; echo ($row['totalPhotos'] > 1)?JText::_('COM_COMMUNITY_PHOTOS'):JText::_('COM_COMMUNITY_PHOTOS');?> 
        <?php echo $row['totalGroups']; echo ($row['totalGroups'] > 1)?JText::_('COM_COMMUNITY_GROUPS'):JText::_('COM_COMMUNITY_GROUPS');?> 
        <?php echo $row['totalFriends']; echo ($row['totalFriends'] > 1)?JText::_('COM_COMMUNITY_FRIENDS'):JText::_('COM_COMMUNITY_FRIENDS');?> 
        <?php echo $row['totalEvents']; echo ($row['totalEvents'] > 1)?JText::_('COM_COMMUNITY_EVENTS'):JText::_('COM_COMMUNITY_EVENTS');?>
    </div>
    <div class="collapsed_member_data">
        <?php
        if(!empty($row['extraFields']))
        {
            foreach($row['extraFields'] as $extraField)
            {
            ?>
                <div class="jomsocialSearchResultWrapper clearfix">
                    <div class="extraFieldTitle"><?php echo $extraField['label']; ?></div>
                    <div class="extraFieldContent"><?php echo $extraField['value']; ?></div>
                </div>
            <?php    
            }
        }
        ?>
        <div class="jomsocialSearchResultWrapper diffrent_div clearfix">
            <div class="qrCodeImage"><img src="<?php echo $row['qr_code_img']; ?>" /></div>
            <div class="extraFieldContent diffrent_content_div">
                <img src="<?php echo JURI::base().'plugins/hellomaps/members/images/as-location.png'; ?>" />
                <a href="javascript:void(0);" onclick="HelloMapsSearch.openStreetView(<?php echo $row['latitude']; ?>,<?php echo $row['longitude']; ?>);"><?php echo JText::_('PLG_HELLOMAP_MEMBERS_STREETVIEW_AVAILABLE_TEXT'); ?></a>
            </div>
        </div>
    </div>    
</div>