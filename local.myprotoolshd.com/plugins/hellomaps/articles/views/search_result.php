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
<div class="hellomap_search_result articles_result" id="articles_result_<?php echo $row['id']; ?>">
    <div class="articles_result_item_wrapper">
        <h1><a href="<?php echo $row['ad_link']; ?>" target="_blank"><?php echo $row['title']; ?></a></h1>
    </div>
    
    <div class="articles_result_item_wrapper">
        <div class="articleLargeThumb">
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
			 	<img class="focus_marker" src="<?php echo JURI::root().'plugins/hellomaps/articles/images/nouserimg.png'; ?>"/></a>
             <?php   
			 } 
			 ?>
            
            
        </div>
           <div class="memberTextPart"><?php echo strip_tags($row['latestStatus']); ?></div>
    </div>
    <?php
    $enable_show_more_less = false;
    
    ?>
  <?php ?>  <div class="articles_result_item_wrapper article_description">
        <?php echo $row['ad_text']; ?>
    </div>
    <?php
    if(!empty($row['extraFields']))
    {
        foreach($row['extraFields'] as $label => $value)
        {
			
            if($value!="")
            {
                $enable_show_more_less = true;
            ?>
                <div class="articles_result_item_wrapper collapsed_articles_data">
                    <div class="fieldTitle"><?php echo $label; ?></div>
                    <div class="fieldValue"><?php echo strip_tags($value); ?></div>
                </div>
            <?php    
            }            
        }        
    }?>
   
    <div class="articles_result_item_wrapper articles_different_box clearfix">
        <div class="articlePartLeft">
              <div class="row">
             	<?php echo JText::_('PLG_HELLOMAPS_ARTICLES_CREATIONDATE_LABEL');?>:<br /><?php echo $row['contentDate'];?>
                </div>
                <?php
                if($row['location']!="")
                {
                ?>
                    <div class="row">
                        <?php echo JText::_('PLG_HELLOMAPS_ARTICLES_LOCATION_LABEL');?>:<br /><?php echo $row['location'];?>
                    </div>
                <?php    
                }
                if(!empty($row['profileTypeName']))
                {
                ?>
                    <div class="row">
                        <?php echo JText::_('PLG_HELLOMAPS_ARTICLES_PROFILE_TYPE');?>:<?php echo $row['profileTypeName'];?>
                    </div>
                <?php    
                }
                ?>   
            
           
        </div>
        <div class="articlePartLeft">
            <div class="articlesAvatar">
              <a href="<?php echo $row['profileLink']; ?>" target="_blank">                    
                    <?php echo JText::_('PLG_HELLOMAPS_ARTICLES_VIEW_PROFILE_LABEL'); ?> </a><br /><br />
                <a href="javascript:void(0);" class="collapsed_articles_data_show_more" data-ad="<?php echo $row['id']; ?>" title="<?php echo JText::_('PLG_HELLOMAPS_ARTICLES_SHOW_MORE_LABEL'); ?>">
                    <img src="<?php echo JURI::root().'plugins/hellomaps/articles/images/rightArrow.png'; ?>" alt="arrow" />
                </a>
                <a href="javascript:void(0);" class="collapsed_articles_data_show_less" data-ad="<?php echo $row['id']; ?>" style="display: none;" title="<?php echo JText::_('PLG_HELLOMAPS_ARTICLES_SHOW_LESS_LABEL'); ?>">
                    <img src="<?php echo JURI::root().'plugins/hellomaps/articles/images/leftArrow.png'; ?>" alt="arrow" />
                </a>
            </div>            
        </div>
    </div> 
</div>