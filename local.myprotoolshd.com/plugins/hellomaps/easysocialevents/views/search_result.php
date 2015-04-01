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
<div class="hellomap_search_result easysocial_event_result" id="easysocialevents_result_<?php echo $row['id']; ?>">
    <div class="easysocialevents_result_item_wrapper">
         <h1><a href="<?php echo $row['profileLink']; ?>" target="_self"><?php echo $row['title']; ?></a></h1>
    </div>
    <div class="easysocialevents_result_item_wrapper clearfix">
        <div class="easysocialeventsInfoWindowWrapper1 clearfix">
        	<div class="easysocialeventsImgPart">
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
			 	<img class="focus_marker" src="<?php echo JURI::root().'plugins/hellomaps/easysocialevents/images/nouserimg.png'; ?>"/></a>
             <?php   
			 } 
			 ?>
             <?php /*?> <?php
                if($row['isOnline'])
                {
                ?>
                    <img class="online_status" src="<?php echo JURI::root().'plugins/hellomaps/easysocialevents/images/online_icon.png'; ?>"/>
                <?php    
                }
                else
                {
                ?>
                    <img class="offline_status" src="<?php echo JURI::root().'plugins/hellomaps/easysocialevents/images/offline_icon.png'; ?>"/>
                <?php    
                }
                ?><?php */?>
            </div>
            <div class="memberTextPart"><?php echo strip_tags($row['latestStatus']); ?></div>
              
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

     
    <div class="easysocialevents_result_item_wrapper easysocialevents_different_box clearfix">
        <div class="easysocialeventsPartFull">
        
        <div class="row">
                <?php echo JText::_('PLG_HELLOMAPS_EASYSOCIALEVENTS_EVENT_SINCE_LABEL');?>:<br />
<?php echo $row['eventdate'];?>
            </div>
            
              <?php 
			//Type of Event;
			?>	
			 <div class="row">
              <?php  echo JText::_('PLG_HELLOMAPS_EASYSOCIALEVENTS_EVENT_TYPE');?>: <?php echo $row['eventType']; ?>
            </div>
			
			<?php
                if($row['location']!="")
                {
                ?>
                    <div class="row">
                   
                      	<?php echo JText::_('PLG_HELLOMAPS_EASYSOCIALEVENTS_LOCATION_LABEL');?>:
                        <?php echo $row['location'];?>
                    </div>
                <?php    
                }
                ?>   
                
                  <?php
				    if(!empty($row['profileTypeName']))
                {
                ?>
                    <div class="row">
                        <?php echo JText::_('PLG_HELLOMAPS_EASYSOCIALEVENTS_PROFILE_TYPE_FIELD_TEXT'); ?>: <?php echo $row['profileTypeName'];?>
                    </div>
                <?php    
                }
				?>
            
           
        </div>
    <div class="easysocialeventsPartRight">
            <div class="easysocialeventsAvatar">
              <a href="<?php echo $row['profileLink']; ?>" target="_self">                    
                    <?php echo JText::_('PLG_HELLOMAPS_EASYSOCIALEVENTS_VIEW_PROFILE_LABEL'); ?> </a>
                <a href="javascript:void(0);" class="collapsed_easysocialevents_data_show_more" data-ad="<?php echo $row['id']; ?>" title="<?php echo JText::_('PLG_HELLOMAPS_EASYSOCIALEVENTS_SHOW_MORE_LABEL'); ?>">
                    <img src="<?php echo JURI::root().'plugins/hellomaps/easysocialevents/images/rightArrow.png'; ?>" alt="arrow" />
                </a>
                <a href="javascript:void(0);" class="collapsed_easysocialevents_data_show_less" data-ad="<?php echo $row['id']; ?>" style="display: none;" title="<?php echo JText::_('PLG_HELLOMAPS_EASYSOCIALEVENTS_SHOW_LESS_LABEL'); ?>">
                    <img src="<?php echo JURI::root().'plugins/hellomaps/easysocialevents/images/leftArrow.png'; ?>" alt="arrow" />
                </a>
            </div>  
                   
        </div>
    </div> 
    <?php // EasySocial Events Totals ?>
      <div class="easysocialeventsSearchResultWrapper totalCounterItem clearfix"> </div>
    
     <div class="collapsed_easysocialevents_data">
        <?php
        if(!empty($row['extraFields']))
        {
            foreach($row['extraFields'] as $extraField)
            {
            ?>
                <div class="easysocialeventsSearchResultWrapper clearfix">
                    <div class="extraFieldTitle"><?php echo JText::_($extraField['label']); ?></div>
                    <div class="extraFieldContent"><?php echo $extraField['value']; ?></div>
                </div>
            <?php    
            }
        }
        ?>
        <div class="easysocialeventsSearchResultWrapper diffrent_div clearfix">
            <div class="qrCodeImage"><img src="<?php echo $row['qr_code_img']; ?>" /></div>
            <div class="extraFieldContent diffrent_content_div">
                <img src="<?php echo JURI::base().'plugins/hellomaps/easysocialevents/images/as-location.png'; ?>" />
                <a href="javascript:void(0);" onclick="HelloMapsSearch.openStreetView(<?php echo $row['latitude']; ?>,<?php echo $row['longitude']; ?>);"><?php echo JText::_('PLG_HELLOMAPS_EVENTS_STREETVIEW_AVAILABLE_TEXT'); ?></a>
            </div>
        </div>
    </div> 
</div>
