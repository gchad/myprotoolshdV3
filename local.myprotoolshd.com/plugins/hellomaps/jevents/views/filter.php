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

$filter_jevents_fields_inline_css = '';
if(!$search_enable)
{
    $filter_jevents_fields_inline_css = 'display:none;';
}
?>
<div id="filter_jeventsmember_fields" class="filterBlock" style="<?php echo $filter_jeventsmember_fields_inline_css; ?>">
    <?php
    if($show_search || ($show_filters && !empty($fitler_profile_types) && !empty($profileTypes)) || $search_enable_radius)
    {
    ?>
        <div class="jevents_filter_control" data-filter_status="visible">
            <a href="javascript:void(0);" class="jevents_filter_control_expand" style="display: none;">
                <img src="<?php echo JURI::base().'plugins/hellomaps/jevents/images/16-plus.png'; ?>" />
            </a>
            <a href="javascript:void(0);" class="jevents_filter_control_collapse">
                <img src="<?php echo JURI::base().'plugins/hellomaps/jevents/images/16-minus.png'; ?>" />
            </a>
        </div>
    <?php    
    }
    ?>
    
    <div id="jevents_filter_area">
        <?php
        $showSearchButton = false;
    	if($show_search)
    	{
    	   $showSearchButton = true;
    	?>
    		<div class="control-group">	        
    	        <div class="controls">
    	            <input type="text" placeholder="Search" class="stretch" id="jevents_search_text" name="jevents[search_text]" />
    	        </div>
    	    </div>
        <?php
    	}
        else//set the hidden fields
        {
        ?>
            <input type="hidden" id="jevents_search_text" name="jevents[search_text]" />
        <?php    
        }
        ?>
        <?php
        if($show_filters && !empty($fitler_profile_types) && !empty($profileTypes))
        {
            $showSearchButton = true;
        ?>
    	    <div class="control-group">
    	        <label class="control-label"><?php echo JText::_('PLG_HELLOMAPS_JEVENTS_PROFILE_TYPE_FIELD_TEXT');?></label>
    	        <div class="controls">
    	            <?php
    	            if(!empty($profileTypes))
	            {
	            	foreach ($profileTypes as $profileType) {
	            		if(in_array($profileType->id, $fitler_profile_types)!==false)
	            		echo '<label>'.$profileType->name.'<input type="checkbox" name="jevents[profileType]['.$profileType->id.']" value="'.$profileType->id.'"/>
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
    	        <label class="control-label"><?php echo JText::_('PLG_HELLOMAPS_JEVENTS_LOCATION_LABEL'); ?></label>
    	        <div class="controls">
    	            <input type="text" placeholder="<?php echo JText::_('PLG_HELLOMAPS_JEVENTS_LOCATION_LABEL'); ?>" class="stretch" name="jevents[location]" id="jevents_location_text" />
    	        </div>
    	    </div>
            <div class="control-group">
    	        <label class="control-label"><?php echo JText::_('PLG_HELLOMAPS_JEVENTS_RADIUS_LABEL'); ?></label>
    	        <div class="controls">
    	            <input type="text" placeholder="<?php echo JText::_('PLG_HELLOMAPS_JEVENTS_RADIUS_LABEL'); ?>" class="stretch" name="jevents[search_radius]" id="jevents_search_radius" value="5" />
    	        </div>
    	    </div>
            <input type="hidden" name="jevents[location_lat]" id="jevents_location_lat" />
            <input type="hidden" name="jevents[location_lng]" id="jevents_location_lng" />
         </div>
        <?php    
        }
        if($showSearchButton)
        {
        ?>
            <div>
                <input type="button" class="btn btn-primary" value="<?php echo JText::_('PLG_HELLOMAPS_JEVENTS_SUBMIT_LABEL'); ?>" id="hellomapJeventsSearchButton" />
                <input type="button" class="btn btn-danger" value="<?php echo JText::_('PLG_HELLOMAPS_JEVENTS_CANCEL_LABEL'); ?>" id="hellomapJeventsResetSearchButton" />
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
    <div id="jevents_plugin_results"></div>
<?php
}
?>
