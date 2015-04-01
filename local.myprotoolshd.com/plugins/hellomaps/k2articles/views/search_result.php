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
<div class="hellomap_search_result k2articles_member_result" id="k2articles_result_<?php echo $row['id']; ?>">
    <div class="k2articles_result_item_wrapper">
         <h1><a href="<?php echo $row['itemLink']; ?>" target="_blank"><?php echo $row['title']; ?></a></h1>
    </div>
    <div class="k2articles_result_item_wrapper clearfix">
        <div class="k2articlesInfoWindowWrapper1 clearfix">
        	<div class="k2articlesImgPart">
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
			 	<img class="focus_marker" src="<?php echo JURI::root().'plugins/hellomaps/k2articles/images/nouserimg.png'; ?>"/></a>
             <?php   
			 } 
			 ?>
            </div>
               <div class="k2articlesTextPart"><?php echo substr($row['summary'], 0, 275);?></div>
        </div>
    </div>
    <?php
	
    $enable_show_more_less = false;
    
    ?>
    <div class="k2articles_result_item_wrapper k2articles_different_box clearfix">
        <div class="k2articlesPartLeft">
              <div class="row">
                </div>
                <?php
                /*if($row['location']!="")
                {
                ?>
                    <div class="row">
                        <?php echo $row['location'];?>
                    </div>
					
                <?php    
                }*/
                if(!empty($row['profileTypeName']))
                {
                ?>
                    <div class="row">
                        <?php echo JText::_('PLG_HELLOMAPS_K2ARTICLES_PROFILE_TYPE_FIELD_TEXT'); ?>: <?php echo $row['profileTypeName'];?>
                    </div>
                <?php    
                }
                ?>   
            
           
        </div>
        <div class="k2articlesPartRight">
            <div class="k2articlesAvatar">
              <a href="<?php echo $row['eventLink']; ?>" target="_blank">                    
                    <?php echo JText::_('PLG_HELLOMAPS_K2ARTICLES_VIEW_PROFILE_LABEL'); ?> </a>
                <a href="javascript:void(0);" class="collapsed_k2articles_data_show_more" data-ad="<?php echo $row['id']; ?>" title="<?php echo JText::_('PLG_HELLOMAPS_K2ARTICLES_SHOW_MORE_LABEL'); ?>">
                    <img src="<?php echo JURI::root().'plugins/hellomaps/k2articles/images/rightArrow.png'; ?>" alt="arrow" />
                </a>
                <a href="javascript:void(0);" class="collapsed_k2articles_data_show_less" data-ad="<?php echo $row['id']; ?>" style="display: none;" title="<?php echo JText::_('PLG_HELLOMAPS_K2ARTICLES_SHOW_LESS_LABEL'); ?>">
                    <img src="<?php echo JURI::root().'plugins/hellomaps/k2articles/images/leftArrow.png'; ?>" alt="arrow" />
                </a>
            </div>  
                  
        </div>
    </div> 
     <div class="collapsed_k2articles_data">
        <?php
        if(!empty($row['extraFields']))
        {
            foreach($row['extraFields'] as $extraField)
            {
            ?>
                <div class="k2articlesSearchResultWrapper clearfix">
                    <div class="extraFieldTitle"><?php echo JText::_($extraField['label']); ?></div>
                    <div class="extraFieldContent"><?php echo $extraField['value']; ?></div>
                </div>
            <?php    
            }
        }
        ?>
        <div class="k2articlesSearchResultWrapper diffrent_div clearfix">
            <div class="qrCodeImage"><img src="<?php echo $row['qr_code_img']; ?>" /></div>
            <div class="extraFieldContent diffrent_content_div">
                <img src="<?php echo JURI::base().'plugins/hellomaps/k2articles/images/as-location.png'; ?>" />
                <a href="javascript:void(0);" onclick="HelloMapsSearch.openStreetView(<?php echo $row['latitude']; ?>,<?php echo $row['longitude']; ?>);"><?php echo JText::_('PLG_HELLOMAPS_MEMBERS_STREETVIEW_AVAILABLE_TEXT'); ?></a>
            </div>
        </div>
    </div> 
</div>
