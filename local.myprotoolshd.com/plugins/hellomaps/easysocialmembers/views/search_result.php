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
<div class="hellomap_search_result easysocial_member_result" id="easysocialmembers_result_<?php echo $row['id']; ?>">
    <div class="easysocialmembers_result_item_wrapper">
         <h1><a href="<?php echo $row['profileLink']; ?>" target="_self"><?php echo $row['title']; ?></a></h1>
    </div>
    <div class="easysocialmembers_result_item_wrapper clearfix">
        <div class="easysocialmembersInfoWindowWrapper1 clearfix">
        	<div class="easysocialmembersImgPart">
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
			 	<img class="focus_marker" src="<?php echo JURI::root().'plugins/hellomaps/easysocialmembers/images/nouserimg.png'; ?>"/></a>
             <?php   
			 } 
			 ?>
              <?php
                if($row['isOnline'])
                {
                ?>
                    <img class="online_status" src="<?php echo JURI::root().'plugins/hellomaps/easysocialmembers/images/online_icon.png'; ?>"/>
                <?php    
                }
                else
                {
                ?>
                    <img class="offline_status" src="<?php echo JURI::root().'plugins/hellomaps/easysocialmembers/images/offline_icon.png'; ?>"/>
                <?php    
                }
                ?>
            </div>
              
      <div id="badges">
     <?php
			
			$my     = Foundry::user($row['id']);
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
    </div>
    <?php
	
    $enable_show_more_less = false;
    
    ?>

     
    <div class="easysocialmembers_result_item_wrapper easysocialmembers_different_box clearfix">
        <div class="easysocialmembersPartLeft">
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
                        <?php echo JText::_('PLG_HELLOMAPS_EASYSOCIALMEMBERS_PROFILE_TYPE_FIELD_TEXT'); ?>:<?php echo $row['profileTypeName'];?>
                    </div>
                <?php    
                }
                ?>   
            
           
        </div>
        <div class="easysocialmembersPartRight">
            <div class="easysocialmembersAvatar">
              <a href="<?php echo $row['profileLink']; ?>" target="_self">                    
                    <?php echo JText::_('PLG_HELLOMAPS_EASYSOCIALMEMBERS_VIEW_PROFILE_LABEL'); ?> </a>
                <a href="javascript:void(0);" class="collapsed_easysocialmembers_data_show_more" data-ad="<?php echo $row['id']; ?>" title="<?php echo JText::_('PLG_HELLOMAPS_EASYSOCIALMEMBERS_SHOW_MORE_LABEL'); ?>">
                    <img src="<?php echo JURI::root().'plugins/hellomaps/easysocialmembers/images/rightArrow.png'; ?>" alt="arrow" />
                </a>
                <a href="javascript:void(0);" class="collapsed_easysocialmembers_data_show_less" data-ad="<?php echo $row['id']; ?>" style="display: none;" title="<?php echo JText::_('PLG_HELLOMAPS_EASYSOCIALMEMBERS_SHOW_LESS_LABEL'); ?>">
                    <img src="<?php echo JURI::root().'plugins/hellomaps/easysocialmembers/images/leftArrow.png'; ?>" alt="arrow" />
                </a>
            </div>  
                   
        </div>
    </div> 
    <?php // EasySocial Totals ?>
      <div class="easysocialmembersSearchResultWrapper totalCounterItem clearfix">
        <?php echo $row['totalPoint']; echo ($row['totalPoint'] > 1)?JText::_('PLG_HELLOMAPS_EASYSOCIALMEMBERS_TOTALPOINTS'):JText::_('PLG_HELLOMAPS_EASYSOCIALMEMBERS_TOTALPOINTS');?> 
        <?php echo $row['totalPhotos']; echo ($row['totalPhotos'] > 1)?JText::_('PLG_HELLOMAPS_EASYSOCIALMEMBERS_TOTALALBUMS'):JText::_('PLG_HELLOMAPS_EASYSOCIALMEMBERS_TOTALALBUMS');?> 
        <?php echo $row['totalBadges']; echo ($row['totalBadges'] > 1)?JText::_('PLG_HELLOMAPS_EASYSOCIALMEMBERS_TOTALBADGES'):JText::_('PLG_HELLOMAPS_EASYSOCIALMEMBERS_TOTALBADGES');?> 
        <?php echo $row['totalFollowing']; echo ($row['totalFollowing'] > 1)?JText::_('PLG_HELLOMAPS_EASYSOCIALMEMBERS_TOTALFOLLOWERS'):JText::_('PLG_HELLOMAPS_EASYSOCIALMEMBERS_TOTALFOLLOWERS');?> 
        <?php echo $row['totalFollowers']; echo ($row['totalFollowers'] > 1)?JText::_('PLG_HELLOMAPS_EASYSOCIALMEMBERS_TOTALFOLLOWING'):JText::_('PLG_HELLOMAPS_EASYSOCIALMEMBERS_TOTALFOLLOWING');?>
    </div>
    
     <div class="collapsed_easysocialmembers_data">
        <?php
        if(!empty($row['extraFields']))
        {
            foreach($row['extraFields'] as $extraField)
            {
            ?>
                <div class="easysocialmembersSearchResultWrapper clearfix">
                    <div class="extraFieldTitle"><?php echo JText::_($extraField['label']); ?></div>
                    <div class="extraFieldContent"><?php echo $extraField['value']; ?></div>
                </div>
            <?php    
            }
        }
        ?>
        <div class="easysocialmembersSearchResultWrapper diffrent_div clearfix">
            <div class="qrCodeImage"><img src="<?php echo $row['qr_code_img']; ?>" /></div>
            <div class="extraFieldContent diffrent_content_div">
                <img src="<?php echo JURI::base().'plugins/hellomaps/members/images/as-location.png'; ?>" />
                <a href="javascript:void(0);" onclick="HelloMapsSearch.openStreetView(<?php echo $row['latitude']; ?>,<?php echo $row['longitude']; ?>);"><?php echo JText::_('PLG_HELLOMAPS_MEMBERS_STREETVIEW_AVAILABLE_TEXT'); ?></a>
            </div>
        </div>
    </div> 
</div>
