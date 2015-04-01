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
$filter_ads_fields_inline_css = '';
if(!$search_enable)
{
    $filter_ads_fields_inline_css = 'display:none;';
}
?>
<div id="filter_ads_fields" class="filterBlock" style="<?php echo $filter_ads_fields_inline_css; ?>">
    <?php
    if($show_search || ($show_filters && !empty($filters_categories) && !empty($adsmanagerCategories)) || $search_enable_radius)
    {
    ?>
        <div class="adsmanager_filter_control" data-filter_status="visible">
            <a href="javascript:void(0);" class="adsmanager_filter_control_expand" style="display: none;">
                <img src="<?php echo JURI::base().'plugins/hellomaps/adsmanager/images/16-plus.png'; ?>" />
            </a>
            <a href="javascript:void(0);" class="adsmanager_filter_control_collapse">
                <img src="<?php echo JURI::base().'plugins/hellomaps/adsmanager/images/16-minus.png'; ?>" />
            </a>
        </div>
    <?php    
    }
    ?>
    
    <div id="adsmanager_filter_area">
        <?php
        $showSearchButton = false;
    	if($show_search)
    	{
    	   $showSearchButton = true;
    	?>
    		<div class="control-group">	        
    	        <div class="controls">
    	            <input type="text" placeholder="Search" class="stretch" id="adsmanager_search_text" name="adsmanager[search_text]" />
    	        </div>
    	    </div>
        <?php
    	}
        else//set the hidden fields
        {
        ?>
            <input type="hidden" id="adsmanager_search_text" name="adsmanager[search_text]" />
        <?php    
        }
        ?>
        <?php
        if($show_filters && !empty($filters_categories) && !empty($adsmanagerCategories))
        {
            $showSearchButton = true;
        ?>
    	    <div class="control-group">
    	        <label class="control-label"><?php echo JText::_('PLG_HELLOMAP_ADSMANAGER_FILTER_BY_CATEGORY_SUBCATEGORY');?></label>
    	        <div class="controls">
    	            <?php
    	            if(!empty($adsmanagerCategories))
    	            {
    	            	foreach ($adsmanagerCategories as $adsmanagerCategory) {
    	            		if(in_array($adsmanagerCategory->id, $filters_categories)!==false)
    	            		echo '<label>'.$adsmanagerCategory->name.'<input type="checkbox" name="adsmanager[category]['.$adsmanagerCategory->id.']" value="'.$adsmanagerCategory->id.'"/>
    	            		</label>';
    	            	}
    	            }
    	            ?>
    	        </div>
    	    </div>
        <?php
    	}
        if($search_enable_radius)
        {
            $showSearchButton = true;
        ?>
        <div class="locationWrap clearfix">
            <div class="control-group">
    	        <label class="control-label"><?php echo JText::_('PLG_HELLOMAP_ADSMANAGER_LOCATION_LABEL'); ?></label>
    	        <div class="controls">
    	            <input type="text" placeholder="<?php echo JText::_('PLG_HELLOMAP_ADSMANAGER_LOCATION_LABEL'); ?>" class="stretch" name="adsmanager[location]" id="adsmanager_location_text" />
    	        </div>
    	    </div>
            <div class="control-group">
    	        <label class="control-label"><?php echo JText::_('PLG_HELLOMAP_ADSMANAGER_RADIUS_LABEL'); ?></label>
    	        <div class="controls">
    	            <input type="text" placeholder="<?php echo JText::_('PLG_HELLOMAP_ADSMANAGER_RADIUS_LABEL'); ?>" class="stretch" name="adsmanager[search_radius]" id="adsmanager_search_radius" value="5" />
    	        </div>
    	    </div>
            <input type="hidden" name="adsmanager[location_lat]" id="adsmanager_location_lat" />
            <input type="hidden" name="adsmanager[location_lng]" id="adsmanager_location_lng" />
         </div>
        <?php    
        }
        if($showSearchButton)
        {
        ?>
            <div>
                <input type="button" class="btn btn-primary" value="<?php echo JText::_('PLG_HELLOMAP_ADSMANAGER_SUBMIT_LABEL'); ?>" id="hellomapAdsmanagerSearchButton" />
                <input type="button" class="btn btn-danger" value="<?php echo JText::_('PLG_HELLOMAP_ADSMANAGER_CANCEL_LABEL'); ?>" id="hellomapAdsmanagerResetSearchButton" />
            </div>
        <?php        
        }
        ?>
    </div>
	
    
</div>
<?php
if($contents_enable)
{
?>
    <div id="adsmanager_plugin_results"></div>
<?php
}
?>
