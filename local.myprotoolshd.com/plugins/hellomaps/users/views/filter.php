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
$filter_usrs_fields_inline_css = '';
if(!$search_enable)
{
    $filter_usrs_fields_inline_css = 'display:none;';
}
?>
<div id="filter_usrs_fields" class="filterBlock" style="<?php echo $filter_usrs_fields_inline_css; ?>">
    <?php
    if($show_search || ($show_filters && !empty($filters_categories) && !empty($usersCategories)) || $search_enable_radius)
    {
    ?>
        <div class="users_filter_control" data-filter_status="visible">
            <a href="javascript:void(0);" class="users_filter_control_expand" style="display: none;">
                <img src="<?php echo JURI::base().'plugins/hellomaps/users/images/16-plus.png'; ?>" />
            </a>
            <a href="javascript:void(0);" class="users_filter_control_collapse">
                <img src="<?php echo JURI::base().'plugins/hellomaps/users/images/16-minus.png'; ?>" />
            </a>
        </div>
    <?php    
    }
    ?>
    
    <div id="users_filter_area">
        <?php
        $showSearchButton = false;
    	if($show_search)
    	{
    	   $showSearchButton = true;
    	?>
    		<div class="control-group">	        
    	        <div class="controls">
    	            <input type="text" placeholder="Search" class="stretch" id="users_search_text" name="users[search_text]" />
    	        </div>
    	    </div>
        <?php
    	}
        else//set the hidden fields
        {
        ?>
            <input type="hidden" id="users_search_text" name="users[search_text]" />
        <?php    
        }
        ?>
        <?php
        if($show_filters && !empty($filters_categories) && !empty($usersCategories))
        {
            $showSearchButton = true;
        ?>
    	    <div class="control-group">
    	        <label class="control-label"><?php echo JText::_('PLG_HELLOMAPS_USERS_FILTER_BY_CATEGORY_SUBCATEGORY');?></label>
    	        <div class="controls">
    	            <?php
    	            if(!empty($usersCategories))
    	            {
    	            	foreach ($usersCategories as $usersCategory) {
    	            		if(in_array($usersCategory->id, $filters_categories)!==false)
    	            		echo '<label>'.$usersCategory->title.'<input type="checkbox" name="users[category]['.$usersCategory->id.']" value="'.$usersCategory->id.'"/>
    	            		</label>';
    	            	}
    	            }

    	            ?>
    	        </div>
    	    </div>
        <?php
    	}
      /*  if($search_enable_radius)
        {
            $showSearchButton = true;
        ?>
        <div class="locationWrap clearfix">
            <div class="control-group">
    	        <label class="control-label"><?php echo JText::_('PLG_HELLOMAPS_USERS_LOCATION_LABEL'); ?></label>
    	        <div class="controls">
    	            <input type="text" placeholder="<?php echo JText::_('PLG_HELLOMAPS_USERS_LOCATION_LABEL'); ?>" class="stretch" name="users[location]" id="users_location_text" />
    	        </div>
    	    </div>
            <div class="control-group">
    	        <label class="control-label"><?php echo JText::_('PLG_HELLOMAPS_USERS_RADIUS_LABEL'); ?></label>
    	        <div class="controls">
    	            <input type="text" placeholder="<?php echo JText::_('PLG_HELLOMAPS_USERS_RADIUS_LABEL'); ?>" class="stretch" name="users[search_radius]" id="users_search_radius" value="5" />
    	        </div>
    	    </div>
            <input type="hidden" name="users[location_lat]" id="users_location_lat" />
            <input type="hidden" name="users[location_lng]" id="users_location_lng" />
         </div>
        <?php    
        }*/
        if($showSearchButton)
        {
        ?>
            <div>
                <input type="button" class="btn btn-primary" value="<?php echo JText::_('PLG_HELLOMAPS_USERS_SUBMIT_LABEL'); ?>" id="hellomapUsersSearchButton" />
                <input type="button" class="btn btn-danger" value="<?php echo JText::_('PLG_HELLOMAPS_USERS_CANCEL_LABEL'); ?>" id="hellomapUsersResetSearchButton" />
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
    <div id="users_plugin_results"></div>
<?php
}
?>
