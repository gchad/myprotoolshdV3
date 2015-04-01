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
 * Configuration view for Jom Social
 */
class HelloMapsViewDashboard extends JViewLegacy
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
		HelloMapsHelper::addSubmenu('dashboard');
		//jimport('joomla.html.pane');
		jimport( 'joomla.html.html.tabs' );
		JPluginHelper::importPlugin('hellomaps');
		
		//$pane	=& JPane::getInstance('sliders');
		
		$groups		= $this->get( 'Groupsinfo' );
		$community	= $this->get( 'Communityinfo' );

		$this->assignRef( 'groups'		, $groups );
		$this->assignRef( 'community'	, $community );
		//$this->assignRef( 'pane'		, $pane );
        // Include the component HTML helpers.
		JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
        $this->sidebar = JHtmlSidebar::render();
        
		parent::display( $tpl );
		$this->setDocument();
		
		//CONFIGURATION COMPONENT DISABLE
		$this->addToolBar();
	}
	
	
	protected function addToolBar() 
	{
		//JToolBarHelper::title(JText::_('COM_HELLOMAPS_MANAGER_HELLOMAPS'), 'hellomaps');
		//JToolBarHelper::deleteListX('', 'hellomaps.delete');
		//JToolBarHelper::editListX('hellomaps.edit');
		//JToolBarHelper::addNewX('hellomaps.add');
	
		//JToolBarHelper::custom(  'NOME FUNZIONE', 'TEST', 'TEST', JText::_( 'TEST' ), false );
        $user = JFactory::getUser();
		if ($user->authorise('core.admin', 'com_hellomaps'))
		{
			JToolbarHelper::preferences('com_hellomaps');
		}
	}
	
	protected function setDocument() 
	{
		//$document = JFactory::getDocument();
		//$document->setTitle(JText::_('HelloMaps Control Panel'));
		//JToolBarHelper::title( JText::_( 'HelloMaps Control Panel' ), 'generic.png' );
		
		// Set the titlebar text
		JToolBarHelper::title( JText::_('HelloMaps Control Panel'), 'generic' );
		JToolBarHelper::back( JText::_('COM_HELLOMAPS_HOME'), 'index.php?option=com_hellomaps&view=dashboard');
		JToolBarHelper::divider();
	}

	/**
	 * Private method to set the toolbar for this view
	 * 
	 * @access private
	 * 
	 * @return null
	 **/
	public function setToolBar()
	{

		// Set the titlebar text
		JToolBarHelper::title( JText::_( 'COM_COMMUNITY_JOMSOCIAL' ), 'community' );
	}
	
	public function addIcon( $image , $url , $text , $newWindow = false )
	{
		$lang		= JFactory::getLanguage();
		$newWindow	= ( $newWindow ) ? ' target="_blank"' : '';
?>
		<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;" class="hellomadp_dashboard_elements">
			<div class="icon">
				<a href="<?php echo $url; ?>"<?php echo $newWindow; ?>>
					<?php echo JHTML::_('image', 'administrator/components/com_hellomaps/assets/icons/' . $image , NULL, NULL ); ?>
					<span><?php echo $text; ?></span></a>
			</div>
		</div>
<?php
	}
}