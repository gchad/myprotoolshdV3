<?php
/**
 * @version     1.0.7
 * @package     com_hellomaps
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      JoomlaForce Team <support@joomlaforce.com> - http://www.joomlaforce.com
 */

defined('_JEXEC') or die;
$dispatcher = JEventDispatcher::getInstance();
$filters = array();
$dispatcher->trigger('onFilterListPrepare', array (&$filters));

$allTabsAreClosed = true;
if(!empty($filters))
{
    foreach($filters as $filter)
    {
        if($filter['show_in_sidebar'] == 1)
        {
            $allTabsAreClosed = false;
        }                                
    }    
}


$loadSidebarCSS = '';
if(!$this->sidebar_load_open)//close the sider with proper margin left/right
{
    if($this->sidebar_position == 'left')
        $loadSidebarCSS .= 'margin-left:-'.($this->sidebar_width+11).'px;';
    else if($this->sidebar_position == 'right')
        $loadSidebarCSS .= 'margin-right:-'.($this->sidebar_width+11).'px;';
}
if(!$this->sidebar_enable)
{
    $loadSidebarCSS = 'display:none;';
}
?>

<?php
if($this->results_enable && $this->results_position == 'top')
{
?>
    <div class="markerStatisticsParent">
        <div class="markerStatistics clearfix" id="markerStatistics" style="<?php echo ($this->results_type == "byzoom")?"display: none;":""; ?>">
        <?php 
        if($this->results_type == "global")
        {
            $globalResultCount = 0;
            $dispatcher->trigger('OnGlobalResultCountPrepare', array (&$globalResultCount));
        ?>
            <div class="totalValue"><?php echo JText::_('COM_HELLOMAP_TOTAL_LABEL'); ?>: <?php echo $globalResultCount; ?></div>
        <?php
        } 
        ?>    
        </div>
    </div>
<?php    
}
?>
<div id="map-animate-box">
    <?php
    $flashMessageStyle = "";
    if(!$this->sidebar_enable || $this->sidebar_position == "" || $this->sidebar_position == 'left')
    {
        $flashMessageStyle .= 'right:0px;';
        if($this->buttonsenabled_fullscreen)
        {
            $flashMessageStyle .= 'top:30px;';
        }
    }
    else//right
    {
        $flashMessageStyle .= 'left:0px;';
        if($this->buttonsenabled_fullscreen)
        {
            $flashMessageStyle .= 'top:30px;';
        }
    }
    ?>
    <div id="hellomap-search-flash-message" style="position:absolute;z-index:500;display: none;<?php echo $flashMessageStyle; ?>">
    </div>
    <div style="position: relative;overflow: hidden;">
        <?php
        $sidebarHeight = $this->sideBarHeight;     
        if($this->contents_enable && $this->show_global_notice && ($this->sidebar_position == $this->notice_position))
        {
            $sidebarHeight = $sidebarHeight - $this->notice_offset;
        }
        else if($this->contents_enable)
        {
            $sidebarHeight = $sidebarHeight - 40;
        }
        $sidebarHeightStyle = !$allTabsAreClosed?'height:'.$sidebarHeight.'px;':'';
        ?>
        <div id="map-canvas-sidebar" class="sidebarAligned<?php echo $this->sidebar_position; ?>" style="position:absolute;z-index:100;width:<?php echo $this->sidebar_width; ?>px;<?php echo $sidebarHeightStyle; ?><?php echo $loadSidebarCSS; ?>" data-original_height="<?php echo $this->sideBarHeight.'px'; ?>">
            <div class="map-canvas-sidebar-inner"<?php echo $allTabsAreClosed?" style='background:none;'":"";?>>            
    			<div class="toolsbar-area clearfix">
    				<ul>
                        <?php
                        if($this->sidebar_position == 'right')
                        {
                        ?>
                            <li><a href="javascript:void(0);" title="Close Sidebar" class="close_sidebar"><img src="<?php echo HELLOMAPS_FRONT_URL; ?>/assets/images/toolsbar_icon_hide_sidebar_img.jpg"/></a></li>
                        <?php    
                        }
                        ?>
                        <?php
                        if($this->buttonsenabled_zoom_inout)
                        {
                        ?>
            			     <li><a href="javascript:void(0);" class="do_zoom_in" title="<?php echo JText::_('COM_HELLOMAPS_ZOOM_IN_LABEL'); ?>">+</a></li>
            				 <li><a href="javascript:void(0);" class="do_zoom_out" title="<?php echo JText::_('COM_HELLOMAPS_ZOOM_OUT_LABEL'); ?>">-</a></li>
                        <?php
                        }
                        if($this->buttonsenabled_userposition)
                        {
                        ?>
                            <li><a href="javascript:void(0);" class="point_user_position" title="<?php echo JText::_('COM_HELLOMAPS_CURRENT_POSITION_LABEL'); ?>"><img src="<?php echo HELLOMAPS_FRONT_URL; ?>assets/images/toolsbar_icon_current_position_img.jpg"></a></li>
                        <?php    
                        }
                        if($this->buttonsenabled_street_view)
                        {
                        ?>
                            <li><a href="javascript:void(0);" class="open_street_view_button" title="<?php echo JText::_('COM_HELLOMAPS_ACTIVATE_STREETVIEW_LABEL'); ?>"><img src="<?php echo HELLOMAPS_FRONT_URL; ?>assets/images/toolsbar_icon_street_view_img.jpg"></a></li>
                        <?php    
                        }
                        if($this->mobilebuttons_listview && !$allTabsAreClosed)
                        {
                        ?>
                            <li>
                                <a href="javascript:void(0);" title="<?php echo JText::_('COM_HELLOMAPS_TOGGLE_LIST_MAP_VIEW_LABEL'); ?>" class="toggle_list_view_map_view" data-view="list_view"><img src="<?php echo HELLOMAPS_FRONT_URL; ?>assets/images/icon_grid_view_img.jpg"/></a>
                            </li>
                        <?php    
                        }
                        else
                        {
                        ?>
                                <input type="hidden" class="toggle_list_view_map_view" data-view="list_view"/>
                        <?php        
                        }
                        if($this->buttonsenabled_settings)
                        {
                        ?>
                            <li class="dropdown last_toolbar_icon">
                                <a href="javascript:void(0);" title="<?php echo JText::_('COM_HELLOMAPS_SETTINGS_LABEL'); ?>"><img src="<?php echo HELLOMAPS_FRONT_URL; ?>assets/images/toolsbar_icon_settings_img.jpg"/></a>        						
                                    <?php
                                    if(!empty($filters))
                                    {
                                    ?>
                                        <div class="dropdown-box">
                                    <?php    
                                            foreach($filters as $filter)
                                            {                                            
                                            ?>
                                                <div class="show_hide_plugin_tab_selection">
                                                    <input type="checkbox" checked="checked" id="filter_<?php echo $filter['filter_id']; ?>" data-filter_id="<?php echo $filter['filter_id']; ?>" class="settings_checkbox" /><?php echo $filter['title']; ?>
                                                </div>
                                            <?php    
                                            }
                                    ?>
                                        </div>
                                    <?php
                                    }
                                    ?>
                                
        					</li>
                        <?php    
                        }
                        ?>
    					
    					
                        <?php
                        if($this->sidebar_position == 'left')
                        {
                        ?>
                            <li><a href="javascript:void(0);" title="<?php echo JText::_('COM_HELLOMAPS_CLOSE_SIDEBAR_LABEL'); ?>" class="close_sidebar"><img src="<?php echo HELLOMAPS_FRONT_URL; ?>/assets/images/toolsbar_icon_hide_sidebar_img.jpg"/></a></li>
                        <?php    
                        }
                        ?>
    					
    				</ul>
    			</div><!-- toolsbar-area -->
                <div class="toolsbar-area-vertical" style="position:absolute;top:10px;<?php echo $this->sidebar_position; ?>:<?php echo $this->sidebar_width+10; ?>px;<?php echo ($this->sidebar_load_open)?'display:none;':''; ?>" id="closed_sidebar_toolbar">
    				<ul>
                        
    					<li><a href="javascript:void(0);" class="open_sidebar" title="<?php echo JText::_('COM_HELLOMAPS_OPEN_SIDEBAR_LABEL'); ?>"><img src="<?php echo HELLOMAPS_FRONT_URL; ?>assets/images/toolsbar_icon_show_sidebar_img.jpg"></a></li>
                        <?php
                        if($this->buttonsenabled_zoom_inout)
                        {
                        ?>
            			     <li><a href="javascript:void(0);" class="do_zoom_in" title="<?php echo JText::_('COM_HELLOMAPS_ZOOM_IN_LABEL'); ?>">+</a></li>
            				 <li><a href="javascript:void(0);" class="do_zoom_out" title="<?php echo JText::_('COM_HELLOMAPS_ZOOM_OUT_LABEL'); ?>">-</a></li>
                        <?php
                        }
                        if($this->buttonsenabled_settings)
                        {
                        ?>
                            <li class="dropdown collapsed_last_toolbar_icon">
                                <a href="javascript:void(0);"><img src="<?php echo HELLOMAPS_FRONT_URL; ?>assets/images/toolsbar_icon_settings_img.jpg"/></a>
                                
                                <?php
                                if(!empty($filters))
                                {
                                ?>
                                    <div class="dropdown-box">
                                <?php
                                    foreach($filters as $filter)
                                    {
                                    ?>
                                        <div class="show_hide_plugin_tab_selection">
                                            <input type="checkbox" checked="checked" id="filter_collapsed_<?php echo $filter['filter_id']; ?>" data-filter_id="<?php echo $filter['filter_id']; ?>" class="settings_checkbox_collapsed" /><?php echo $filter['title']; ?>
                                        </div>
                                    <?php    
                                    }
                                ?>
                                    </div>
                                <?php
                                }
                                ?>
                            
                            </li>
                        <?php    
                        }
                        ?>
                        <?php
                        if($this->buttonsenabled_userposition)
                        {
                        ?>
                            <li><a href="javascript:void(0);" class="point_user_position" title="<?php echo JText::_('COM_HELLOMAPS_CURRENT_POSITION_LABEL'); ?>"><img src="<?php echo HELLOMAPS_FRONT_URL; ?>assets/images/toolsbar_icon_current_position_img.jpg"></a></li>
                        <?php    
                        }
                        if($this->buttonsenabled_street_view)
                        {
                        ?>
                            <li><a href="javascript:void(0);" class="open_street_view_button" title="<?php echo JText::_('COM_HELLOMAPS_ACTIVATE_STREETVIEW_LABEL'); ?>"><img src="<?php echo HELLOMAPS_FRONT_URL; ?>assets/images/toolsbar_icon_street_view_img.jpg"></a></li>
                        <?php    
                        }
                        ?>
    				</ul>
    			</div><!-- toolsbar-area-vertical -->   
    			<div class="sidebarItems"<?php echo $allTabsAreClosed?" style='display:none;'":""; ?>>
                     
                    <div class="titles-tab-area">
        				<?php
                            //print the tabs
                        if(!empty($filters))
                        {
                            $tabLIs = '';
                            $tabContents = '';                            
                            foreach($filters as $filter)
                            {
                                $filterBlock = $filter['content'];
                                $style = ($filter['show_in_sidebar'] == 0)?" style='display:none;'":"";                                
                                
                                if($tabLIs == '')
                                {
                                    $activeTabClass = !$this->contents_enable?' active':'';
                                    $tabLIs .= '<li '.$style.' id="plugin_tab_li_'.$filter['filter_id'].'" data-filter_id="'.$filter['filter_id'].'" data-status="enabled" data-show_in_sidebar="'.$filter['show_in_sidebar'].'" class="plugin_tab_li'.$activeTabClass.'"><a href="#title_tab_'.$filter['filter_id'].'" data-toggle="tab" class="hellomaps_plugin_tab">'.$filter['title'].'</a></li>';
                                    $tabContents .= '<div '.$style.' class="tab-pane'.$activeTabClass.'" id="title_tab_'.$filter['filter_id'].'">'.$filterBlock.'</div>';
                                }
                                else
                                {
                                    $tabLIs .= '<li '.$style.' id="plugin_tab_li_'.$filter['filter_id'].'" data-filter_id="'.$filter['filter_id'].'"  data-status="enabled" data-show_in_sidebar="'.$filter['show_in_sidebar'].'" class="plugin_tab_li"><a href="#title_tab_'.$filter['filter_id'].'" data-toggle="tab" class="hellomaps_plugin_tab">'.$filter['title'].'</a></li>';
                                    $tabContents .= '<div '.$style.' class="tab-pane" id="title_tab_'.$filter['filter_id'].'">'.$filterBlock.'</div>';
                                }                                
                            }
                        ?>
                            <ul class="nav nav-tabs" id="plugin_tabs_ul"<?php echo ($allTabsAreClosed)?" style='display:none;'":""; ?>>
                              <?php
                              if($this->contents_enable)
                              {
                              ?>
                                <li class="all_tab active" id="global_result_tab"><a href="#title_tab_all"data-toggle="tab"><?php echo JText::_('COM_HELLOMAPS_ALL_LABEL'); ?></a></li>
                              <?php  
                              }
                              ?>                                  
                              <?php echo $tabLIs; ?>
                            </ul>
                            
                            <!-- Tab panes -->
                            <div class="tab-content" id="plugin_tabs_contents"<?php echo ($allTabsAreClosed)?" style='display:none;'":""; ?>>
                              <?php
                              if($this->contents_enable)
                              {
                              ?>
                                <div class="tab-pane active" id="title_tab_all">
                                    <?php
                                    if($this->show_global_notice && ($this->sidebar_position == $this->notice_position))
                                    {
                                        $this->global_result_height = $this->global_result_height - $this->notice_offset - 20;
                                    }
                                    else
                                    {
                                        $this->global_result_height = $this->global_result_height - 70;
                                    }
                                    ?>
                                    <div id="global_result_list" class="" style="height: <?php echo $this->global_result_height; ?>px;"></div>                                                                  
                                </div>
                              <?php
                              }
                              ?>  
                                
                              <?php echo $tabContents; ?>
                            </div>    
                        <?php    
                        }
                        ?>
        			</div><!-- titles-tab-area -->
                </div>                
            </div>
        </div>
        <?php
        //print the notice box
        $infoLinkClass = " no_info_link";
        if($this->infolink_enable)
        {
            $infoLinkClass = " yes_info_link";
        }
        if($this->show_global_notice)
        {
        ?>
            <div id="notice_box_holder_global" class="notice_box_holder noticePositions<?php echo $this->notice_position.$infoLinkClass; ?>" style="width:<?php echo $this->sidebar_width; ?>px;">
                <a class="notice_close global_notice_close_button" href="javascript:void(0);">X</a>
                <a class="notice_open global_notice_open_button" href="javascript:void(0);" style="display: none;">+</a>
                <div id="notice_box_container_global">
                    <div class="noticeBlock global">
                        <div class="notice_global_header"><?php echo JText::_('COM_HELLOMAP_NOTICE_HEADER_TEXT'); ?></div>
                        <div class="global_notice_content notices_area_scroll">
                            <?php echo $this->globalNoticeText; ?>
                        </div>
                    </div>                                        
                    <?php
                    //$dispatcher->trigger('onNoticeAreaDisplay');
                    ?>
                </div>                
            </div>
        <?php    
        }
        else if($this->pluginNoticeExist)
        {
            $dispatcher->trigger('onNoticeAreaDisplay');
        }
        ?>
        <div id="map-canvas" style="width:<?php echo $this->map_dimensions_width; ?>;height:<?php echo $this->map_dimensions_height; ?>px;">
            <div id="map-canvas-inner" style="height: 100%;width: 100%;"></div>
        </div>
    </div>
    
    
    <?php
    if($this->infolink_enable)
    {
        $infolink_url = ($this->infolink_url != "")?$this->infolink_url:"javascript:void(0);";
    ?>
        <div class="infoLinkWrapper infolinkAligned<?php echo $this->sidebar_position; ?>">
            <a class="custom-btn" href="<?php echo $infolink_url; ?>" target="_blank"><?php echo JText::_('COM_HELLOMAP_INFOLINK_LABEL'); ?></a>
        </div>
    <?php    
    }
    ?>    
</div>

<?php
if($this->results_enable && $this->results_position == 'bottom')
{
?>
    <div class="markerStatisticsParent">
        <div class="markerStatistics clearfix" id="markerStatistics" style="<?php echo ($this->results_type == "byzoom")?"display: none;":""; ?>">
            <?php 
            if($this->results_type == "global")
            {
                $globalResultCount = 0;
                $dispatcher->trigger('OnGlobalResultCountPrepare', array (&$globalResultCount));
            ?>
                <div class="totalValue"><?php echo JText::_('COM_HELLOMAP_TOTAL_LABEL'); ?>: <?php echo $globalResultCount; ?></div>
            <?php
            } 
            ?>            
        </div>
    </div>
<?php    
}
?>

