<?php
/**
 * @version     1.0.7
 * @package     com_hellomaps
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      JoomlaForce Team <support@joomlaforce.com> - http://www.joomlaforce.com
 */
 
// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');
$dispatcher = JEventDispatcher::getInstance();
?>

<div id="j-sidebar-container" class="span2">
	<?php echo $this->sidebar;?>
</div>


<div id="j-main-container" class="span10">
<div class="hellomap_dashboard_logo" align="center"><br />
       <?php echo JText::_('COM_HELLOMAPS_DASHBOARD_LOGO'); ?>
       <hr /></div>

<div class="hellomap_dashboard_icons_wrapper">
        <div class="hellomap_dashboard_icon_list">
            <?php
            $globalResultCount = 0;
            $dispatcher->trigger('OnGlobalResultCountPrepare', array (&$globalResultCount));
            ?>
        </div>
        <div class="totalValue"><?php echo JText::_('COM_HELLOMAP_TOTAL_MARKERS_LABEL'); ?>: <?php echo number_format($globalResultCount,0,'',',');; ?></div>
    </div>

    <table width="100%" border="0" class="panel" id="dashboard_table">
    	<tr>
    		<td width="55%" valign="top">
    			<div id="cpanel" class="clearfix">
    				<?php echo $this->addIcon('configuration.png','index.php?option=com_hellomaps&view=config', JText::_('COM_HELLOMAPS_SUBMENU_CONFIGURATION'));?>
                    
                    <?php echo $this->addIcon('pluginslist.png','index.php?option=com_plugins&view=plugins&filter_search=hellomaps', JText::_('COM_HELLOMAPS_SUBMENU_PLUGINS'));?>
                    
    				<?php echo $this->addIcon('map-styler.png','index.php?option=com_hellomaps&view=mapstyler', JText::_('COM_HELLOMAPS_SUBMENU_SETTINGS'));?>
    				<?php echo $this->addIcon('support.png',JText::_('COM_HELLOMAPS_SUPPORT_WEBSITE'), JText::_('COM_HELLOMAPS_SUPPORT'), true ); ?>
    				<?php echo $this->addIcon('jforcelogo.png',JText::_('COM_HELLOMAPS_JFORCE_STORE_WEBSITE'), JText::_('COM_HELLOMAPS_JFORCE_STORE'), true ); ?>				
    			</div>
             
    		</td>
    		<td width="45%" valign="top">
    			<?php
    				//echo JHtml::_('sliders.panel', JText::_('COM_COMMUNITY_STATISTICS') , 'community'  );
    				//echo $this->pane->startPane( 'stat-pane' );
    				//echo $this->pane->startPanel( JText::_('COM_HELLOMAPS_WELCOME_TO_HELLOMAPS') , 'welcome' );
    				echo JHtml::_('sliders.start');
    				echo JHtml::_('sliders.panel', JText::_('COM_HELLOMAPS_ACCORDION1_TITLE') , 'accorion_1' );
    			?>            
                    <div class="pane-box accorion_1">
        				<?php
                        echo JText::_('COM_HELLOMAPS_ACCORDION1_DESCRIPTION');
                        ?>
        			</div>
    			<?php                
                    echo JHtml::_('sliders.panel', JText::_('COM_HELLOMAPS_ACCORDION2_TITLE') , 'accorion_2' );
                ?>
                    <div class="pane-box accorion_2">
        				<?php
                        echo JText::_('COM_HELLOMAPS_ACCORDION2_DESCRIPTION');
                        ?>
        			</div>
                <?php    
    				//echo $this->pane->endPanel();
    				//echo $this->pane->endPane();
    				echo JHtml::_('sliders.end');
    			?>
    		</td>
    	</tr>
    </table>
    
</div>

 

            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
