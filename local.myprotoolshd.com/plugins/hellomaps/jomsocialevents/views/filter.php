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

$filter_jomsocialevents_fields_inline_css = '';
if(!$search_enable)
{
    $filter_jomsocialevents_fields_inline_css = 'display:none;';
}
?>
<div id="filter_jomsocialeventsmember_fields" class="filterBlock" style="<?php echo $filter_jomsocialeventsmember_fields_inline_css; ?>">
    <?php
    if($show_search || ($show_filters && !empty($fitler_profile_types) && !empty($profileTypes)) || $search_enable_radius)
    {
    ?>
        <div class="jomsocialevents_filter_control" data-filter_status="visible">
            <a href="javascript:void(0);" class="jomsocialevents_filter_control_expand" style="display: none;">
                <img src="<?php echo JURI::base().'plugins/hellomaps/jomsocialevents/images/16-plus.png'; ?>" />
            </a>
            <a href="javascript:void(0);" class="jomsocialevents_filter_control_collapse">
                <img src="<?php echo JURI::base().'plugins/hellomaps/jomsocialevents/images/16-minus.png'; ?>" />
            </a>
        </div>
    <?php    
    }
    ?>
    
    <div id="jomsocialevents_filter_area">
        <?php
        $showSearchButton = false;
    	if($show_search)
    	{
    	   $showSearchButton = true;
    	?>
    		<div class="control-group">	        
    	        <div class="controls">
    	            <input type="text" placeholder="Search" class="stretch" id="jomsocialevents_search_text" name="jomsocialevents[search_text]" />
    	        </div>
    	    </div>
        <?php
    	}
        else//set the hidden fields
        {
        ?>
            <input type="hidden" id="jomsocialevents_search_text" name="jomsocialevents[search_text]" />
        <?php    
        }
        ?>
        <?php
        if($show_filters && !empty($fitler_profile_types) && !empty($profileTypes))
        {
            $showSearchButton = true;
        ?>
    	    <div class="control-group">
    	        <label class="control-label"><?php echo JText::_('PLG_HELLOMAPS_JOMSOCIALEVENTS_PROFILE_TYPE_FIELD_TEXT');?></label>
    	        <div class="controls">
    	            <?php
    	            if(!empty($profileTypes))
	            {
	            	foreach ($profileTypes as $profileType) {
	            		if(in_array($profileType->id, $fitler_profile_types)!==false)
	            		echo '<label>'.$profileType->name.'<input type="checkbox" name="jomsocialevents[profileType]['.$profileType->id.']" value="'.$profileType->id.'"/>
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
    	        <label class="control-label"><?php echo JText::_('PLG_HELLOMAPS_JOMSOCIALEVENTS_LOCATION_LABEL'); ?></label>
    	        <div class="controls">
    	            <input type="text" placeholder="<?php echo JText::_('PLG_HELLOMAPS_JOMSOCIALEVENTS_LOCATION_LABEL'); ?>" class="stretch" name="jomsocialevents[location]" id="jomsocialevents_location_text" />
    	        </div>
    	    </div>
            <div class="control-group">
    	        <label class="control-label"><?php echo JText::_('PLG_HELLOMAPS_JOMSOCIALEVENTS_RADIUS_LABEL'); ?></label>
    	        <div class="controls">
    	            <input type="text" placeholder="<?php echo JText::_('PLG_HELLOMAPS_JOMSOCIALEVENTS_RADIUS_LABEL'); ?>" class="stretch" name="jomsocialevents[search_radius]" id="jomsocialevents_search_radius" value="5" />
    	        </div>
    	    </div>
            <input type="hidden" name="jomsocialevents[location_lat]" id="jomsocialevents_location_lat" />
            <input type="hidden" name="jomsocialevents[location_lng]" id="jomsocialevents_location_lng" />
         </div>
        <?php    
        }
        if($showSearchButton)
        {
        ?>
            <div>
                <input type="button" class="btn btn-primary" value="<?php echo JText::_('PLG_HELLOMAPS_JOMSOCIALEVENTS_SUBMIT_LABEL'); ?>" id="hellomapJomsocialeventsSearchButton" />
                <input type="button" class="btn btn-danger" value="<?php echo JText::_('PLG_HELLOMAPS_JOMSOCIALEVENTS_CANCEL_LABEL'); ?>" id="hellomapJomsocialeventsResetSearchButton" />
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
    <div id="jomsocialevents_plugin_results"></div>
<?php
}
?>
