<?php
/**
 * @version     1.0.8
 * @package     com_hellomaps
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      JoomlaForce Team <support@joomlaforce.com> - http://www.joomlaforce.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.calendar');

$filter_easysocial_fields_inline_css = '';
if(!$search_enable)
{
    $filter_easysocial_fields_inline_css = 'display:none;';
}
?>
<div id="filter_easysocialevent_fields" class="filterBlock" style="<?php echo $filter_easysocialevent_fields_inline_css; ?>">
    <?php
    if($show_search || ($show_filters && !empty($fitler_profile_types) && !empty($profileTypes)) || $search_enable_radius)
    {
    ?>
        <div class="easysocialevents_filter_control" data-filter_status="visible">
            <a href="javascript:void(0);" class="easysocialevents_filter_control_expand" style="display: none;">
                <img src="<?php echo JURI::base().'plugins/hellomaps/easysocialevents/images/16-plus.png'; ?>" />
            </a>
            <a href="javascript:void(0);" class="easysocialevents_filter_control_collapse">
                <img src="<?php echo JURI::base().'plugins/hellomaps/easysocialevents/images/16-minus.png'; ?>" />
            </a>
        </div>
    <?php    
    }
    ?>
    
    <div id="easysocialevents_filter_area">
        <?php
        $showSearchButton = false;
    	if($show_search)
    	{
    	   $showSearchButton = true;
    	?>
    		<div class="control-group">	        
    	        <div class="controls">
    	            <input type="text" placeholder="Search" class="stretch" id="easysocialevents_search_text" name="easysocialevents[search_text]" />
    	        </div>
    	    </div>
        <?php
    	}
        else//set the hidden fields
        {
        ?>
            <input type="hidden" id="easysocialevents_search_text" name="easysocialevents[search_text]" />
        <?php    
        }
        ?>
        <?php
        if($show_filters && !empty($fitler_profile_types) && !empty($profileTypes))
        {
            $showSearchButton = true;
        ?>
    	    <div class="control-group">
    	        <label class="control-label"><?php echo JText::_('PLG_HELLOMAPS_EASYSOCIALEVENTS_PROFILE_TYPE_FIELD_TEXT');?></label>
    	        <div class="controls">
    	            <?php
    	            if(!empty($profileTypes))
	            {
	            	foreach ($profileTypes as $profileType) {
	            		if(in_array($profileType->id, $fitler_profile_types)!==false)
	            		echo '<label>'.$profileType->name.'<input type="checkbox" name="easysocialevents[profileType]['.$profileType->id.']" value="'.$profileType->id.'"/>
	            		</label>';
	            	}
	            }
				
				
				//Calendar Date Filter	
   		// echo JHTML::calendar(date("Y-m-d"),'easysocialevents[startdate]', 'easysocialevents_startdate', '%Y-%m-%d %H:%M:%S',array('size'=>'2','maxlength'=>'10',));
		 
    	/* echo JHTML::calendar(date("Y-m-d"),'easysocialevents[enddate]', 'easysocialevents_enddate', '%Y-%m-%d %H:%M:%S',array('size'=>'2','maxlength'=>'10','class'=>' validate[\'required\']',));*/

				

    	            ?>
    	        </div>
    	    </div>
        <?php
    	}
        if($search_enable_radius)
        {
            $showSearchButton = true;
        ?>
       <?php ?> 
       	<div class="locationWrap clearfix">
            <div class="control-group">
    	        <label class="control-label"><?php echo JText::_('PLG_HELLOMAPS_EASYSOCIALEVENTS_LOCATION_LABEL'); ?></label>
    	        <div class="controls">
    	            <input type="text" placeholder="<?php echo JText::_('PLG_HELLOMAPS_EASYSOCIALEVENTS_LOCATION_LABEL'); ?>" class="stretch" name="easysocialevents[location]" id="easysocialevents_location_text" />
    	        </div>
    	    </div>
            <div class="control-group">
    	        <label class="control-label"><?php echo JText::_('PLG_HELLOMAPS_EASYSOCIALEVENTS_RADIUS_LABEL'); ?></label>
    	        <div class="controls">
    	            <input type="text" placeholder="<?php echo JText::_('PLG_HELLOMAPS_EASYSOCIALEVENTS_RADIUS_LABEL'); ?>" class="stretch" name="easysocialevents[search_radius]" id="easysocialevents_search_radius" value="5" />
    	        </div>
    	    </div>
            <input type="hidden" name="easysocialevents[location_lat]" id="easysocialevents_location_lat" />
            <input type="hidden" name="easysocialevents[location_lng]" id="easysocialevents_location_lng" />
         </div><?php ?>
        <?php    
        }
        if($showSearchButton)
        {
        ?>
            <div>
                <input type="button" class="btn btn-primary" value="<?php echo JText::_('PLG_HELLOMAPS_EASYSOCIALEVENTS_SUBMIT_LABEL'); ?>" id="hellomapEasysocialeventsSearchButton" />
                <input type="button" class="btn btn-danger" value="<?php echo JText::_('PLG_HELLOMAPS_EASYSOCIALEVENTS_CANCEL_LABEL'); ?>" id="hellomapEasysocialeventsResetSearchButton" />
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
    <div id="easysocialevents_plugin_results"></div>
<?php
}
?>
