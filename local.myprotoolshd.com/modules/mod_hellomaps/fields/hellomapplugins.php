<?php
/**
 * @version     1.0
 * @package 	mod_hellomaps
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      JoomlaForce Team <support@joomlaforce.com> - http://www.joomlaforce.com
 */
 
// no direct access
defined('_JEXEC') or die;
 
// import the list field type
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');
 
/**
 * Form Field class for the component
 */
if(!class_exists('JFormFieldHellomapPlugins'))
{
    class JFormFieldHellomapPlugins extends JFormFieldList
    {
    	/**
    	 * The field type.
    	 *
    	 * @var		string
    	 */
    	protected $type = 'HellomapPlugins';
     
    	/**
    	 * Method to get a list of options for a list input.
    	 *
    	 * @return	array		An array of JHtml options.
    	 */
    	protected function getOptions() 
    	{
            $options = array();
            JPluginHelper::importPlugin('hellomaps');
            $dispatcher = JEventDispatcher::getInstance();
            
            $hellomapPluginsEnabled = array();
            $dispatcher->trigger('onPluginListingAtModuleBackend', array (&$hellomapPluginsEnabled));
            if (!empty($hellomapPluginsEnabled))
    		{
    			foreach($hellomapPluginsEnabled as $plugin_id=>$pluginTitle) 
    			{
    				$options[] = JHtml::_('select.option', $plugin_id, $pluginTitle);
    			}
    		}
    		$options = array_merge(parent::getOptions(), $options);
    		return $options;
    	}
    }    
} 

