<?php

/**
 * @version     1.0.7
 * @package     com_hellomaps
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      JoomlaForce Team <support@joomlaforce.com> - http://www.joomlaforce.com
 */

 
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Config controller class.
 */
class HelloMapsControllerConfig extends JControllerForm
{
	/**
	 * Method to save a record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 *
	 * @since   12.2
	 */
	public function save($key = null, $urlVar = null)
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		$app   = JFactory::getApplication();
		$lang  = JFactory::getLanguage();
		$model = $this->getModel();
		$table = $model->getTable();
		$task = $this->getTask();
		
		$table->id = 1;
		$table->name = 'config';

		$params		= new JRegistry( $table->params );
		$data  = $this->input->post->get('jform', array(), 'array');

		foreach( $data as $key => $value )
		{
			if( $key != 'task' && $key != 'option' && $key != 'view' )
			{
				$params->set( $key , $value );
			}
		}
		$table->params	= $params->toString();

		// Save it
		if(!$table->store() )
		{
			return false;
		}
		
		$this->setMessage(JText::_('COM_HELLOMAPS_CONFIG_SAVE_SUCCESS'));
		
		// Redirect the user and adjust session state based on the chosen task.
		switch ($task)
		{
			case 'apply':
				$this->setRedirect(
					JRoute::_(
						'index.php?option=' . $this->option . '&view=config', false
					)
				);
				break;

			case 'save':
				$this->setRedirect(
					JRoute::_(
						'index.php?option=' . $this->option . '&view=dashboard', false
					)
				);
				break;

			default:
				
				$this->setRedirect(
					JRoute::_(
						'index.php?option=' . $this->option . '&view=config', false
					)
				);
				break;
		}
		
		return true;
	}
	
	/**
	 * Method to cancel an edit.
	 *
	 * @param   string  $key  The name of the primary key of the URL variable.
	 *
	 * @return  boolean  True if access level checks pass, false otherwise.
	 *
	 * @since   12.2
	 */
	public function cancel($key = null)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();

		$this->setRedirect(
			JRoute::_(
				'index.php?option=' . $this->option . '&view=dashboard'. $this->getRedirectToListAppend(), false
			)
		);

		return true;
	}
}
