<?php
/**
 * @version     1.0.7
 * @package     com_hellomaps
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      JoomlaForce Team <support@joomlaforce.com> - http://www.joomlaforce.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');
?>
<div class="k2articles_marker_info_window" id="k2articles_marker_info_window_<?php echo $markerData['id']; ?>" style="height:<?php echo $markerInfoWindowHeight; ?>;">
    
    <h1><a href="<?php  echo JRoute::_($markerData['itemLink']); ?>" target="_blank"><?php echo $markerData['title']; ?></a></h1>
    
    <div class="k2articlesInfoWindowWrapper1 clearfix">
        <div class="k2articlesImgPart">
            <a href="<?php  echo JRoute::_($markerData['itemLink']); ?>"><img src="<?php echo $markerData['largeAvatar']; ?>"/></a>
        </div>
        <div class="k2articlesTextPart">
            
            
            <?php 
            
            echo truncate($markerData['summary'], 275);
           
            ?>
            
        </div>
    </div>
    
    <div class="k2articlesInfoWindowWrapper2 clearfix">
        <div class="k2articlesPartLeft">
            <?php
            /*if($markerData['location']!="")
            {
            ?>
                <div class="row">
                    <?php echo $markerData['location'];?>
                </div>
            <?php    
            }*/
            if(!empty($markerData['profileTypeName']))
            {
            ?>
                <div class="row">
                    <?php echo JText::_('PLG_HELLOMAPS_K2ARTICLES_PROFILE_TYPE_FIELD_TEXT');?>: <?php echo $markerData['profileTypeName'];?>
                </div>
            <?php    
            }
            ?>   
             
        </div>
        <div class="k2articlesPartRight">
            <div class="memberInfo">
                
                <a href="<?php  echo JRoute::_($markerData['itemLink']); ?>" target="_blank" class="button2" style="display: inline-block;">   
                                     
                    <?php echo JText::_('HELLO_MAP_VIEW_PROFILE'); ?>   
                      
                </a>
            </div>
            
        </div>
    </div>
</div>