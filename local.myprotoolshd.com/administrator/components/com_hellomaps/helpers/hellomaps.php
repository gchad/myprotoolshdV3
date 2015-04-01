<?php
/**
 * @version     1.0.7
 * @package     com_hellomaps
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      JoomlaForce Team <support@joomlaforce.com> - http://www.joomlaforce.com
 */
 
// No direct access to this file
defined('_JEXEC') or die;
 
abstract class HelloMapsHelper
{
    private static $configuration = null;
	/**
	 * Configure the Linkbar.
	 */
	public static function addSubmenu($submenu) 
	{
        JHtmlSidebar::addEntry(JText::_('COM_HELLOMAPS_DASHBOARD'),
		                         'index.php?option=com_hellomaps&view=dashboard',$submenu == 'dashboard');
                               	
		JHtmlSidebar::addEntry(JText::_('COM_HELLOMAPS_SUBMENU_CONFIGURATION'),
		                         'index.php?option=com_hellomaps&view=config',$submenu == 'config');		
		
		JHtmlSidebar::addEntry(JText::_('COM_HELLOMAPS_SUBMENU_PLUGINS'),
		                         'index.php?option=com_plugins&view=plugins&filter_search=hellomaps',$submenu == 'plugins');								
								 				 								 
		JHtmlSidebar::addEntry(JText::_('COM_HELLOMAPS_SUBMENU_SETTINGS'),
		                         'index.php?option=com_hellomaps&view=mapstyler',$submenu == 'mapstyler');	
								 
		JHtmlSidebar::addEntry(JText::_('COM_HELLOMAPS_SUPPORT'),
		                         JText::_('COM_HELLOMAPS_SUPPORT_WEBSITE'), false);
                                 
                                 
		
		
		// set some global property
		$document = JFactory::getDocument();
		//$document->addStyleDeclaration('.icon-48-jforcempas ' .
		                               //'{background-image: url(../media/com_hellomaps/images/tux-48x48.png);}');
	}
    /**
     * Load the configuration only once, if it is already not initialized
    */
    private static function LoadConfiguration()
    {
        if(HelloMapsHelper::$configuration == null)
        {
            $db = JFactory::getDBO();
            $query = $db->getQuery(true);
            $query->select($db->quoteName('params'));
            $query->from($db->quoteName('#__hellomaps_config'));
            $query->where($db->quoteName('name').'="config"');
            $query->order($db->quoteName('id').' DESC');
            $db->setQuery($query,0,1);
            $db->query();
            $config_json = $db->loadResult();
            if($config_json != "")
            {
                HelloMapsHelper::$configuration = json_decode($config_json,true);
            }
            else
            {
                HelloMapsHelper::$configuration = array();
            }
        }
    }
    /**
     * Get configuration value set by admin
    */
    public static function GetConfiguration($config_name,$default_value="")
    {
        HelloMapsHelper::LoadConfiguration();
        $config_value = $default_value;
        if(isset(HelloMapsHelper::$configuration[$config_name]))
            $config_value = HelloMapsHelper::$configuration[$config_name];
        return $config_value;    
    }    
}
