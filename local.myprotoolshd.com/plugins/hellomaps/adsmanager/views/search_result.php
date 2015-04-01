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
<div class="hellomap_search_result adsmanager_result" id="adsmanager_result_<?php echo $row['id']; ?>">
    <div class="adsmanager_result_item_wrapper">
        <h1><a href="<?php echo $row['ad_link']; ?>" target="_blank"><?php echo $row['title']; ?></a></h1>
    </div>
    
    <div class="adsmanager_result_item_wrapper">
        <div class="adsLargeThumb">
            <img class="focus_marker" src="<?php echo $row['largeAvatar'];?>"/>
        </div>
    </div>
    <?php
    $enable_show_more_less = false;
    if(!empty($row['images']) && (count($row['images']) > 1) )
    {
        $enable_show_more_less = true;
    ?>
        <div class="adsmanager_result_item_wrapper collapsed_adsmanager_data">
            <div class="adsThumbnailImages clearfix">
                <ul>
                    <?php
                    foreach($row['images'] as $ad_image)
                    {
                        $thumb = JURI::base()."images/com_adsmanager/ads/".$ad_image->thumbnail;
                        $large = JURI::base()."images/com_adsmanager/ads/".$ad_image->image;
                    ?>
                        <li>
                            <a href="<?php echo $large; ?>" onclick="HelloMapsSearch.ChangeBigImage(<?php echo $row['id']; ?>,'<?php echo $large; ?>');return false;">
                                <img src="<?php echo $thumb; ?>"/>
                            </a>
                        </li>
                    <?php    
                    }
                    ?>
                </ul>
            </div>
        </div>
    <?php
    }
    ?>
    <div class="adsmanager_result_item_wrapper ad_description">
        <?php echo $row['ad_text']; ?>
    </div>
    <?php
    if(!empty($row['extraFields']))
    {
        foreach($row['extraFields'] as $extraField)
        {
            if($extraField['value']!="")
            {
                $enable_show_more_less = true;
            ?>
                <div class="adsmanager_result_item_wrapper collapsed_adsmanager_data">
                    <div class="fieldTitle"><?php echo $extraField['label']; ?></div>
                    <div class="fieldValue"><?php echo $extraField['value']; ?></div>
                </div>
            <?php    
            }            
        }        
    }
    ?>
    <div class="adsmanager_result_item_wrapper adsmanager_different_box clearfix">
        <div class="adPartLeft">
            <div class="row">
                <?php echo JText::_('PLG_HELLOMAP_ADSMANAGER_CATEGORY');?>:<?php echo $row['category_text'];?>
            </div>
            <?php
            if($row['location']!="")
            {
            ?>
                <div class="row">
                    <?php echo JText::_('PLG_HELLOMAP_ADSMANAGER_LOCATION_LABEL');?>:<?php echo $row['location'];?>
                </div>
            <?php    
            }
            ?>   
            
            <div class="row">
                <?php echo JText::_('PLG_HELLOMAP_ADSMANAGER_PRICE_LABEL');?>:<?php echo $row['ad_price_full'];?>
            </div>   
        </div>
        <div class="adPartRight">
            <div class="adsmanagerAvatar">
                <a href="<?php echo $row['profileLink']; ?>" target="_blank">
                    <img src="<?php echo $row['userAvatar']; ?>" /> <br />
                    <?php echo JText::_('PLG_HELLOMAP_ADSMANAGER_VIEW_USER_LABEL'); ?>
                </a>
                <a href="javascript:void(0);" class="collapsed_adsmanager_data_show_more" data-ad="<?php echo $row['id']; ?>" title="<?php echo JText::_('PLG_HELLOMAP_ADSMANAGER_SHOW_MORE_LABEL'); ?>">
                    <img src="<?php echo JURI::root().'plugins/hellomaps/adsmanager/images/rightArrow.png'; ?>" alt="arrow" />
                </a>
                <a href="javascript:void(0);" class="collapsed_adsmanager_data_show_less" data-ad="<?php echo $row['id']; ?>" style="display: none;" title="<?php echo JText::_('PLG_HELLOMAP_ADSMANAGER_SHOW_LESS_LABEL'); ?>">
                    <img src="<?php echo JURI::root().'plugins/hellomaps/adsmanager/images/leftArrow.png'; ?>" alt="arrow" />
                </a>
            </div>            
        </div>
    </div> 
</div>