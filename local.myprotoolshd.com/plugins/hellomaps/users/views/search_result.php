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
<div class="hellomap_search_result users_result" id="users_result_<?php echo $row['id']; ?>">
    <div class="users_result_item_wrapper">
        <h1><a href="<?php echo $row['profileLink']; ?>" target="_blank"><?php echo $row['title']; ?></a></h1>
    </div>
    
    <div class="users_result_item_wrapper">
        <div class="userLargeThumb">
           <?php
             if($row['largeAvatar'])
			 {
			 ?>
            	<a href="javascript:void(0);" class="focus_marker"><img style="float:left; margin:5px;" src="<?php echo $row['largeAvatar']; ?>"/></a>
             <?php
			 }
			 else
			 {
			 ?>
			 	<a href="javascript:void(0);" class="focus_marker"><img style="float:left; margin:5px;" src="<?php echo JURI::root().'plugins/hellomaps/users/images/no-image.png'; ?>"/></a>
             <?php   
			 } 
			 ?>
             <?php echo strip_tags($row['latestStatus']); ?>
        </div>
        <div style="clear:both"></div>
       
    </div>
    <?php
    $enable_show_more_less = false;
    
    ?>

   <?php /*?> <?php
    if(!empty($row['extraFields']))
    {
        foreach($row['extraFields'] as $label => $value)
        {
			
            if($value!="")
            {
                $enable_show_more_less = true;
            ?>
                <div class="users_result_item_wrapper collapsed_users_data">
                    <div class="fieldTitle"><?php echo $label; ?></div>
                    <div class="fieldValue"><?php echo strip_tags($value); ?></div>
                </div>
            <?php    
            }            
        }        
    }?><?php */?>
   
    <div class="users_result_item_wrapper users_different_box clearfix">
        <div class="adPartLeft">
                <div class="row">
                      <?php echo JText::_('PLG_HELLOMAPS_USERS_MEMBER_SINCE_LABEL');?>:<?php echo $row['memberSince'];?>
                </div>
                <?php
                if($row['location']!="")
                {
                ?>
                    <div class="row">
                        <?php echo JText::_('PLG_HELLOMAPS_USERS_LOCATION_LABEL');?>:<?php echo $row['location'];?>
                    </div>
                <?php    
                }
                if(!empty($row['profileTypeName']))
                {
                ?>
                    <div class="row">
                        <?php echo JText::_('PLG_HELLOMAPS_USERS_PROFILE_TYPE');?>:<?php echo $row['profileTypeName'];?>
                    </div>
                <?php    
                }
                ?>   
                
                <div class="row">
                    <?php echo JText::_('PLG_HELLOMAPS_USERS_LAST_ONLINE_LABEL');?>:<?php echo $row['lastLogin'];?>
                </div>   
        </div>
        <?php /* <div class="adPartLeft">
         ?>  <div class="usersAvatar">
        	
                <a href="javascript:void(0);" class="collapsed_users_data_show_more" data-ad="<?php echo $row['id']; ?>" title="<?php echo JText::_('PLG_HELLOMAPS_USERS_SHOW_MORE_LABEL'); ?>">
                    <img src="<?php echo JURI::root().'plugins/hellomaps/users/images/rightArrow.png'; ?>" alt="arrow" />
                </a>
                <a href="javascript:void(0);" class="collapsed_users_data_show_less" data-ad="<?php echo $row['id']; ?>" style="display: none;" title="<?php echo JText::_('PLG_HELLOMAPS_USERS_SHOW_LESS_LABEL'); ?>">
                    <img src="<?php echo JURI::root().'plugins/hellomaps/users/images/leftArrow.png'; ?>" alt="arrow" />
                </a>
            </div>         
        </div>   <?php */?>
    </div> 
</div>