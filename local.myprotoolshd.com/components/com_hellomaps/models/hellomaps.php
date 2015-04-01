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
 * @subpackage  com_contact
 * @since       1.5
 */
class HelloMapsModelHelloMaps extends JModelList
{

	/**
	 * Constructor.
	 *
	 * @param   array  An optional associative array of configuration settings.
	 * @see     JController
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since   1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app	= JFactory::getApplication();
		$params	= JComponentHelper::getParams('com_hellomaps');
		$db		= $this->getDbo();
		// Load the parameters.
		$this->setState('params', $params);
	}
}
