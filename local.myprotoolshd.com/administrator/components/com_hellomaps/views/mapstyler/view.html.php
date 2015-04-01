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
class HelloMapsViewMapstyler extends JViewLegacy
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
        HelloMapsHelper::addSubmenu('mapstyler');
		jimport('joomla.html.pane');
		//$pane	=& JPane::getInstance('sliders');
		//$this->assignRef( 'pane'		, $pane );
        JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
        $this->sidebar = JHtmlSidebar::render();        
		parent::display( $tpl );
		$this->setDocument();
	}
	
	protected function addToolBar() 
	{
		//JToolBarHelper::title(JText::_('COM_HELLOMAPS_MANAGER_HELLOMAPS'), 'hellomaps');
		//JToolBarHelper::deleteListX('', 'hellomaps.delete');
		//JToolBarHelper::editListX('hellomaps.edit');
		//JToolBarHelper::addNewX('hellomaps.add');
		JToolBarHelper::preferences('com_hellomaps');
		
	}
	
	protected function setDocument() 
	{
		//$document = JFactory::getDocument();
		//$document->setTitle(JText::_('HelloMaps Control Panel'));
		JToolBarHelper::title( JText::_( 'HelloMaps Control Panel' ), 'generic.png' );
		//JToolBarHelper::preferences('com_hellomaps');
		JToolBarHelper::preferences('com_hellomaps', 480, 600, JText::_('COM_HELLOMAPS_APPLY_STYLE_LABEL'), '', 'window.location.reload()');
		JToolBarHelper::divider();
		JToolBarHelper::back( JText::_('COM_HELLOMAPS_HOME'), 'index.php?option=com_hellomaps&view=dashboard');
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
		$lang		=& JFactory::getLanguage();
		$newWindow	= ( $newWindow ) ? ' target="_blank"' : '';
?>
		<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
			<div class="icon">
				<a href="<?php echo $url; ?>"<?php echo $newWindow; ?>>
					<?php echo JHTML::_('image', 'administrator/components/com_community/assets/icons/' . $image , NULL, NULL ); ?>
					<span><?php echo $text; ?></span></a>
			</div>
		</div>
<?php
	}
}