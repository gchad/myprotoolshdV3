<?php
/**
 * @version     1.0.7
 * @package     com_hellomaps
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      JoomlaForce Team <support@joomlaforce.com> - http://www.joomlaforce.com
 */

defined('JPATH_BASE') or die;
jimport('joomla.utilities.date');

class PlgContentHelloMaps extends JPlugin
{
	
	protected $document;

	
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		JFormHelper::addFieldPath(__DIR__ . '/fields');
		$this->loadLanguage();
	}

	/**
	 * @param   JForm    $form    The form to be altered.
	 * @param   array    $data    The associated data for the form.
	 *
	 * @return  boolean
	 * @since   1.6
	 */
	public function onContentPrepareForm($form, $data)
	{
		if (!($form instanceof JForm))
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');
			return false;
		}

		// which belong to the following components
		$components_list = array("com_content.article");
		if (!in_array($form->getName(), $components_list)) return true;

		// Add the registration fields to the form.
		JForm::addFormPath(dirname(__FILE__) . '/models');
		$form->loadFile('hellomaps', false);

		return true;
	}
	
	/*function onContentPrepareData($context, $data)
	{
		
		if (is_object($data))
		{
			//print_r($data);
			$articleId = isset($data->id) ? $data->id : 0;
			//$latitude = $data->attribs->get("latitude");
			//print_r($data->attribs['latitude']);
		}

		return true;
	}*/

}
