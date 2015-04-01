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
<div class="adsmanager_marker_info_window" id="adsmanager_marker_info_window_<?php echo $markerData['id']; ?>" style="height:<?php echo $markerInfoWindowHeight; ?>;">
    <h1><a href="<?php echo $markerData['ad_link']; ?>" target="_blank"><?php echo $markerData['title']; ?></a></h1>
    <div class="adsmanagerInfoWindowWrapper1 clearfix">
        <div class="adImgPart"><a href="<?php echo $markerData['ad_link']; ?>"><img src="<?php echo $markerData['thumb']; ?>"/></a></div>
        <div class="adTextPart"><?php echo $af_text; ?></div>
    </div>
    <div class="adsmanagerInfoWindowWrapper2 clearfix">
        <div class="adPartLeft">
            <div class="row">
                <?php echo JText::_('PLG_HELLOMAP_ADSMANAGER_CATEGORY');?>:<?php echo $markerData['category_text'];?>
            </div>
            <?php
            if($markerData['location']!="")
            {
            ?>
                <div class="row">
                    <?php echo JText::_('PLG_HELLOMAP_ADSMANAGER_LOCATION_LABEL');?>:<?php echo $markerData['location'];?>
                </div>
            <?php    
            }
            ?>   
            
            <div class="row">
                <?php echo JText::_('PLG_HELLOMAP_ADSMANAGER_PRICE_LABEL');?>:<?php echo $markerData['ad_price_full'];?>
            </div>   
        </div>
        <div class="adPartRight">
            <div class="adsmanagerAvatar">
                <a href="<?php echo $markerData['profileLink']; ?>" target="_blank">
                    <img src="<?php echo $markerData['userAvatar']; ?>" /> <br />
                    <?php echo JText::_('PLG_HELLOMAP_ADSMANAGER_VIEW_USER_LABEL'); ?>                   
                </a>
            </div>      
            <div class="adsmanagerArrow">
                <a href="<?php echo $markerData['profileLink']; ?>" target="_blank">
                    <img src="<?php echo JURI::root().'plugins/hellomaps/adsmanager/images/rightArrow.png'; ?>" alt="arrow" />                    
                </a>
            </div>                  
        </div>
    </div>
</div>