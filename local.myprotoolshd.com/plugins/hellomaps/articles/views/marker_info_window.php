<?php
/**
 * @version     1.0
 * @package     com_hellomaps
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      JoomlaForce Team <support@joomlaforce.com> - http://www.joomlaforce.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

?>
<div class="articles_marker_info_window" id="articles_marker_info_window_<?php echo $markerData['id']; ?>" style="width:<?php echo $markerInfoWindowWidth; ?>;height:<?php echo $markerInfoWindowHeight; ?>;">
    <h3><a href="<?php echo $markerData['profileLink']; ?>" target="_blank"><?php echo $markerData['title']; ?></a></h3>
    <div class="JarticlesInfoWindowWrapper1 clearfix">
        <div class="articleImgPart"><?php 
			 if($markerData['largeAvatar'])
			 {
			 ?>
            	<a href="<?php echo $markerData['profileLink']; ?>"><img src="<?php echo $markerData['largeAvatar']; ?>"/></a>
            <?php
			 }
			 else
			 {
			 ?>
			 	<img src="<?php echo JURI::root().'plugins/hellomaps/articles/images/nouserimg.png'; ?>"/>
            <?php    
            }

            ?>
        </div>
        <div class="articleConnectionPMSLinks">
        </div>
        <div class="articleTextPart"><?php echo strip_tags($markerData['latestStatus']); ?></div>
    </div>
    <div class="articlesInfoWindowWrapper2 clearfix">
        <div class="articlePartLeft">
            <div class="row">
                <?php echo JText::_('PLG_HELLOMAPS_ARTICLES_CREATIONDATE_LABEL');?>:<?php echo $markerData['contentDate'];?>
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
                    <?php echo JText::_('PLG_HELLOMAPS_ARTICLES_PROFILE_TYPE');?>:<?php echo $markerData['profileTypeName'];?>
                </div>
            <?php    
            }
            ?>   
            
          
        </div>
        <div class="articlePartRight">
            <div class="articleKarma">
                <a href="<?php echo $markerData['profileLink']; ?>" target="_blank">                    
                    <?php echo JText::_('PLG_HELLOMAPS_ARTICLES_VIEW_PROFILE_LABEL'); ?>   
                    <div class="articlesArrow">
                        <img src="<?php echo JURI::root().'plugins/hellomaps/articles/images/rightArrow.png'; ?>" alt="arrow" />
                    </div>                
                </a>
            </div>
            
        </div>
    </div>
</div>