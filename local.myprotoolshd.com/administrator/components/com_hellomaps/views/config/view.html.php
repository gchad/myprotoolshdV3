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
 * Configuration view for hellomaps
 */
class HelloMapsViewConfig extends JViewLegacy
{
	/**
	 * The default method that will display the output of this view which is called by
	 * Joomla
	 * 
	 * @param	string template	Template file name
	 **/	 	
	public function display( $tpl = null )
	{
		// Initialiase variables.
		$this->form		= $this->get('Form');
		
		// Load tooltips
		JHTML::_('behavior.tooltip', '.hasTip');
		
		//jimport('joomla.html.pane');
        HelloMapsHelper::addSubmenu('config');
		jimport( 'joomla.html.html.tabs' );
		JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
        $this->sidebar = JHtmlSidebar::render();    
		parent::display( $tpl );
		
		//CONFIGURATION COMPONENT DISABLE
		$this->addToolBar();
	}
	
	
	protected function addToolBar() 
	{
		JToolBarHelper::title( JText::_( 'COM_HELLOMAPS_CONFIGURATION' ));
		
		JToolBarHelper::back( JText::_('COM_HELLOMAPS_HOME'), 'index.php?option=com_hellomaps&view=dashboard');
		
		JToolbarHelper::apply('config.apply');
		JToolbarHelper::save('config.save');
		JToolbarHelper::cancel('config.cancel');
        
        $user = JFactory::getUser();
		if ($user->authorise('core.admin', 'com_hellomaps'))
		{
			JToolbarHelper::preferences('com_hellomaps');
		}
	}
	
	
	public function addIcon( $image , $url , $text , $newWindow = false )
	{
		$lang		=& JFactory::getLanguage();
		$newWindow	= ( $newWindow ) ? ' target="_blank"' : '';
?>
		<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
			<div class="icon">
				<a href="<?php echo $url; ?>"<?php echo $newWindow; ?>>
					<?php echo JHTML::_('image', 'administrator/components/com_hellomaps/assets/icons/' . $image , NULL, NULL ); ?>
					<span><?php echo $text; ?></span></a>
			</div>
		</div>
<?php
	}
}