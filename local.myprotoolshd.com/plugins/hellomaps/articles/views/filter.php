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
$filter_arts_fields_inline_css = '';
if(!$search_enable)
{
    $filter_arts_fields_inline_css = 'display:none;';
}
?>
<div id="filter_arts_fields" class="filterBlock" style="<?php echo $filter_arts_fields_inline_css; ?>">
    <?php
    if($show_search || ($show_filters && !empty($filters_categories) && !empty($articlesCategories)) || $search_enable_radius)
    {
    ?>
        <div class="articles_filter_control" data-filter_status="visible">
            <a href="javascript:void(0);" class="articles_filter_control_expand" style="display: none;">
                <img src="<?php echo JURI::base().'plugins/hellomaps/articles/images/16-plus.png'; ?>" />
            </a>
            <a href="javascript:void(0);" class="articles_filter_control_collapse">
                <img src="<?php echo JURI::base().'plugins/hellomaps/articles/images/16-minus.png'; ?>" />
            </a>
        </div>
    <?php    
    }
    ?>
    
    <div id="articles_filter_area">
        <?php
        $showSearchButton = false;
    	if($show_search)
    	{
    	   $showSearchButton = true;
    	?>
    		<div class="control-group">	        
    	        <div class="controls">
    	            <input type="text" placeholder="Search" class="stretch" id="articles_search_text" name="articles[search_text]" />
    	        </div>
    	    </div>
        <?php
    	}
        else//set the hidden fields
        {
        ?>
            <input type="hidden" id="articles_search_text" name="articles[search_text]" />
        <?php    
        }
        ?>
        <?php
        if($show_filters && !empty($filters_categories) && !empty($articlesCategories))
        {
            $showSearchButton = true;
        ?>
    	    <div class="control-group">
    	        <label class="control-label"><?php echo JText::_('PLG_HELLOMAPS_ARTICLES_FILTER_BY_CATEGORY_SUBCATEGORY');?></label>
    	        <div class="controls">
    	            <?php
    	            if(!empty($articlesCategories))
    	            {
    	            	foreach ($articlesCategories as $articlesCategory) {
    	            		if(in_array($articlesCategory->id, $filters_categories)!==false)
    	            		echo '<label>'.$articlesCategory->title.'<input type="checkbox" name="articles[category]['.$articlesCategory->id.']" value="'.$articlesCategory->id.'"/>
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
    	        <label class="control-label"><?php echo JText::_('PLG_HELLOMAPS_ARTICLES_LOCATION_LABEL'); ?></label>
    	        <div class="controls">
    	            <input type="text" placeholder="<?php echo JText::_('PLG_HELLOMAPS_ARTICLES_LOCATION_LABEL'); ?>" class="stretch" name="articles[location]" id="articles_location_text" />
    	        </div>
    	    </div>
            <div class="control-group">
    	        <label class="control-label"><?php echo JText::_('PLG_HELLOMAPS_ARTICLES_RADIUS_LABEL'); ?></label>
    	        <div class="controls">
    	            <input type="text" placeholder="<?php echo JText::_('PLG_HELLOMAPS_ARTICLES_RADIUS_LABEL'); ?>" class="stretch" name="articles[search_radius]" id="articles_search_radius" value="5" />
    	        </div>
    	    </div>
            <input type="hidden" name="articles[location_lat]" id="articles_location_lat" />
            <input type="hidden" name="articles[location_lng]" id="articles_location_lng" />
         </div>
        <?php    
        }*/
        if($showSearchButton)
        {
        ?>
            <div>
                <input type="button" class="btn btn-primary" value="<?php echo JText::_('PLG_HELLOMAPS_ARTICLES_SUBMIT_LABEL'); ?>" id="hellomapArticlesSearchButton" />
                <input type="button" class="btn btn-danger" value="<?php echo JText::_('PLG_HELLOMAPS_ARTICLES_CANCEL_LABEL'); ?>" id="hellomapArticlesResetSearchButton" />
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
    <div id="articles_plugin_results"></div>
<?php
}
?>
