<?php
/**
 * @version     1.0.7
 * @package     com_hellomaps
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      JoomlaForce Team <support@joomlaforce.com> - http://www.joomlaforce.com
 */

defined('_JEXEC') or die;

/**
 * @package     Joomla.Site
 * @subpackage  com_hellomaps
 */
class HellomapsControllerPlugin_manager extends JControllerForm
{
	protected $params; 
    
    /**
     * [search description]
     * will trigger hellomap plugins to get search result, same as joomla search works
     * @return [type] [description]
     */
    public function search()
    {
        $post = JRequest::get('post');
        $josnResponse = array();        
        $dispatcher = JEventDispatcher::getInstance();
        $searchResult = array();		
        if(!empty($post))
        {
        	JPluginHelper::importPlugin('hellomaps');
        	foreach ($post as $litsenerName => $searchParam) 
        	{
        		$dispatcher->trigger('onHellomapSearch', array ($litsenerName,$searchParam,&$searchResult));        		
        	}        	
        }
        echo json_encode($searchResult);
        exit;
    }
}
