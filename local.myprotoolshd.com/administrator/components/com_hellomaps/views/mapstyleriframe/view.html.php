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

jimport( 'joomla.application.component.view' );

/**
 * Map styler iframe view
 */
class HelloMapsViewMapstyleriframe extends JViewLegacy
{
	/**
	 * The default method that will display the output of this view which is called by
	 * Joomla
	 * 
	 * @param	string template	Template file name
	 **/	 	
	public function display( $tpl = null )
	{
		// Load tooltips
		JHTML::_('behavior.tooltip', '.hasTip');
		jimport('joomla.html.pane');
		//$pane	=& JPane::getInstance('sliders');
		
		$params = JComponentHelper::getParams('com_hellomaps');
        $this->gmap_api_key = $params->get('gmap_api_key','AIzaSyDXEbeQlKtqvBIHdtMXhBnaPa9KfteQ7IY');
		//$this->assignRef( 'pane'		, $pane );
		parent::display( $tpl );		
	}
}