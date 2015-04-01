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

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
jimport( 'joomla.application.component.helper' );

class Pkg_HellomapsInstallerScript
{
	public function preflight ($type, $parent) {
		// Joomla! broke the update call, so we have to create a workaround check.
		$db = JFactory::getDbo();
		$db->setQuery("SELECT enabled FROM #__extensions WHERE element = 'com_hellomaps'");
                                 $is_enabled = $db->loadResult();   
		if (!$is_enabled){
			$this->hasHellomapsInst = 0;
			return;
		} else {
			$this->hasHellomapsInst = 1;
			return;
		}
	}

	public function update($parent)
	{
		return true;
	}

	public function install($parent)
			
	{	
		return true;
	}

	public function uninstall($parent)
	{
		return true;
	}

	/*
	 * enable the plugins
	 */
	
	public function postflight($type, $parent)
	{
		// CSS Styling:
		?> 
		<style type="text/css">
			.adminform tr th:first-child {display:none;}
			table.adminform tr td {padding:15px;}
			div.hellomaps_install {background-color:#f4f4f4;border:1px solid #ccc; border-radius:5px; padding:10px;}
			.installed {clear:both;display:inline-block;}
			.installed ul { width:350px;padding-left:0px;border: 1px solid #ccc;border-radius: 5px;}
			.installed ul li:first-child {border-top-left-radius: 5px;border-top-right-radius: 5px;}
			.installed ul li:last-child {border-bottom-left-radius: 5px;border-bottom-right-radius: 5px;}
			.installed ul li {padding:8px;list-style-type:none;}
			.installed ul li:nth-child(odd) {background-color: #fff;}
			.installed ul li:nth-child(even) {background-color: #D6D6D6;}
			.proceed {display:inline-block; vertical-align:top;}
			div.proceed ul {text-align:center;list-style-type:none;}
			div.proceed ul li {padding:5px;background-color:#fff;border:1px solid #ccc;margin-bottom:10px;border-radius:5px;}
		</style>
		<?php
		// End of CSS Styling
		if ($this->hasHellomapsInst == 1) 
		{ 
			$inst_text = JText::_('HELLOMAPS_INST_VERSION_UPRG'); 
		} 
		else 
		{  
			$inst_text = JText::_('HELLOMAPS_INST_VERSION');
		}

		echo "<div class='hellomaps_install'>
				<div class='hellomaps_logo'><img src='".JURI::base().'components/com_hellomaps/assets/images/'."logo.png' /></div>
				<div class='version'><h2>". $inst_text .": ".$parent->get('manifest')->version."</h2></div>
				<div class='installed'> 
					<ul>
						<li>Hellomaps Core Component</li>
						<li>Hellomaps Module</li>
						<li>Hellomaps Plugin for Joomla Users</li>
						<li>Hellomaps Plugin for Joomla Articles</li>
						<li>Hellomaps Plugin for AdsManager</li>
						<li>Hellomaps Plugin for JomSocial Members</li>
						<li>Hellomaps Plugin for JomSocial Events</li>
						<li>Hellomaps Plugin for EasySocial Members</li>
						<li>Hellomaps Plugin for EasySocial Events</li>
						<li>Hellomaps Plugin for Community Builder Members</li>
						<li>Hellomaps Plugin for K2 Articles</li>
						<li>Hellomaps Plugin for JEvents</li>
						<li>Content Plugin - HelloMaps for Articles</li>
						<li>User Plugin - HelloMaps for Users</li>
					</ul>
				</div>		
				<div class='proceed'> 
					<ul>
						<li><a href='index.php?option=com_hellomaps&view=config' alt='Hellomaps Configuration'><img src='components/com_hellomaps/assets/images/sidebar_icon.jpg' alt='Configuration Page' /><br/> Configuration</a><br/></li>	
						<li><a href='index.php?option=com_plugins&view=plugins&filter_folder=hellomaps' alt='HelloMaps Plugins'><img src='components/com_hellomaps/assets/images/sidebar_icon.jpg' alt='Plugins Configuration Page' /><br/>Plugins</a><br/></li>						
					</ul>
				</div>";

		
		if ($this->hasHellomapsInst == 0)
		{
			// enable plugin
			$db = JFactory::getDbo();
			$query = "SELECT * FROM #__extensions WHERE name='PLG_HELLOMAP_MEMBERS' and type='plugin' and element='members'";
			$db->setQuery($query);
			$finder_q = $db->loadObject();
			$finder = $finder_q->enabled;

			$query = 'SHOW TABLES LIKE "' . $db->getPrefix() . 'finder_types"';
			$db->setQuery($query);
			$finder_types = $db->loadObjectList();

			if (!count($finder_types))
			{
				echo "<div class='alert alert-warning'> Warning! your Joomla! installation is missing the finder_types database table.<br/><br/> You should run a database check and then fix if an error is reporting by <a href='index.php?option=com_installer&view=database' class='btn-warning btn button'>Clicking Here</a></div>";
			}
            
            //insert into hellomap config tables for the first entry if it does not exist
            $query = 'SELECT COUNT(*) AS total FROM #__hellomaps_config';
            $db->setQuery($query);
            $db->query();
            $configTotal = (int)$db->loadResult();
            if($configTotal == 0)
            {
                //insert the initial configuration
                $configObject = new stdClass;
                $configObject->id = 1;
                $configObject->name = 'config';
               $configObject->params = '{"sidebar_enable":"1","sidebar_load_open":"1","sidebar_position":"left","sidebar_width":"320","notice_enable":"1","notice_type":"by_plugins","notice_position":"left","notice_html":"<p><strong>Lorem Ipsum<\\/strong> is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the  standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.<\\/p>","search_enable":"1","search_enable_radius":"0","contents_enable":"1","contents_show_pagination":"0","contents_records_per_page":"5","results_enable":"1","results_type":"byzoom","results_position":"bottom","infolink_enable":"1","infolink_url":"www.site.com","maptype_enable_satellite":"1","maptype_enable_terrain":"1","maptype_enable_street":"1","maptype_default":"street","dimensions_width":"100%","dimensions_height":"500","initialize_autocenter_markers":"1","initialize_center_onuser_position":"1","initialize_default_lat":"-10.430530","initialize_default_lng":"12.3020","clustering_enable":"1","clustering_default_image":"","infowindow_enable":"1","infowindow_width":"500","infowindow_height":"350","eventsenabled_mousescroll_zoom":"1","eventsenabled_marker_mouseover":"1","eventsenabled_sidebar_mouseover":"1","buttonsenabled_zoom_inout":"1","buttonsenabled_street_view":"1","buttonsenabled_fullscreen":"1","buttonsenabled_userposition":"1","buttonsenabled_settings":"1","mobilebuttons_listview":"1"}';
                $configObject->modified_by = JFactory::getUser()->id;
                $configObject->modified_date = date('Y-m-d H:i:s');
                $db->insertObject('#__hellomaps_config',$configObject);
            }
            

			 
			//Auto enable plugins, but it will not do any good
			
            /*if ($finder == 1 && count($finder_types))
			{
				$query = "UPDATE #__extensions SET enabled=1 WHERE folder='hellomaps' and type='plugin' and element='members'";
				$db->setQuery($query);
				$db->query();
			}

			$query = "UPDATE #__extensions SET enabled=1 WHERE folder='hellomaps' and type='plugin' and element='adsmanager'";
			$db->setQuery($query);
			$db->query();
			
			$query = "UPDATE #__extensions SET enabled=1 WHERE folder='hellomaps' and type='plugin' and element='members'";
			$db->setQuery($query);
			$db->query();
            */
		}
		echo "</div>";

	}

}
