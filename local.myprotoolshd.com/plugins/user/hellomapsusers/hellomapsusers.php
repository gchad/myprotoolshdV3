<?php
/**
 * @version     1.0.7
 * @package     com_hellomaps
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      JoomlaForce Team <support@joomlaforce.com> - http://www.joomlaforce.com
 */

defined('JPATH_BASE') or die;
class PlgUserHelloMapsUsers extends JPlugin
{
	
	//private $_date = '';

	/**
	 * Load the language file on instantiation.
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Constructor
	 *
	 * @param   object  $subject  The object to observe
	 * @param   array   $config   An array that holds the plugin configuration
	 *
	 * @since   1.5
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		JFormHelper::addFieldPath(__DIR__ . '/fields');
	}

	/**
	 * @param   string     $context  The context for the data
	 * @param   integer    $data     The user id
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	public function onContentPrepareData($context, $data)
	{
		// Check we are manipulating a valid form.
		if (!in_array($context, array('com_users.profile', 'com_users.user', 'com_users.registration', 'com_admin.profile')))
		{
			return true;
		}

		if (is_object($data))
		{
			$userId = isset($data->id) ? $data->id : 0;

			if (!isset($data->profile) and $userId > 0)
			{
				// Load the profile data from the database.
				$db = JFactory::getDbo();
				$db->setQuery(
					'SELECT profile_key, profile_value FROM #__user_profiles' .
						' WHERE user_id = ' . (int) $userId . " AND profile_key LIKE 'hellomapsusers.%'" .
						' ORDER BY ordering'
				);

				try
				{
					$results = $db->loadRowList();
				}
				catch (RuntimeException $e)
				{
					$this->_subject->setError($e->getMessage());
					return false;
				}

				// Merge the profile data.
				$data->hellomapsusers = array();

				foreach ($results as $v)
				{
					$k = str_replace('hellomapsusers.', '', $v[0]);
					$data->hellomapsusers[$k] = json_decode($v[1], true);
					if ($data->hellomapsusers[$k] === null)
					{
						$data->hellomapsusers[$k] = $v[1];
					}
				}
			}

			if (!JHtml::isRegistered('users.url'))
			{
				JHtml::register('users.url', array(__CLASS__, 'url'));
			}
	
		}

		return true;
	}

	public static function url($value)
	{
		if (empty($value))
		{
			return JHtml::_('users.value', $value);
		}
		else
		{
			// Convert website url to utf8 for display
			$value = JStringPunycode::urlToUTF8(htmlspecialchars($value));

			if (substr($value, 0, 4) == "http")
			{
				return '<a href="' . $value . '">' . $value . '</a>';
			}
			else
			{
				return '<a href="http://' . $value . '">' . $value . '</a>';
			}
		}
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

		// Check we are manipulating a valid form.
		$name = $form->getName();
		if (!in_array($name, array('com_admin.profile', 'com_users.user', 'com_users.profile', 'com_users.registration')))
		{
			return true;
		}

		// Add the registration fields to the form.
		JForm::addFormPath(__DIR__ . '/profiles');
		$form->loadFile('profile', false);

		$fields = array(
			'mappajf',
			'latitude',
			'longitude',
			'state',
			'region',
			'city',
			'postal_code',
			'usrimage',
			'phone',
			'aboutme',
			'website'
		);

		//Change fields description when displayed in front-end
		$app = JFactory::getApplication();
		if ($app->isSite())
		{
			$form->setFieldAttribute('mappajf', 'description', 'PLG_USER_HELLOMAPSUSERS_FILL_FIELD_DESC_SITE', 'hellomapsusers');
			$form->setFieldAttribute('address1', 'description', 'PLG_USER_HELLOMAPSUSERS_FILL_FIELD_DESC_SITE', 'hellomapsusers');
		/*	$form->setFieldAttribute('address2', 'description', 'PLG_USER_HELLOMAPSUSERS_FILL_FIELD_DESC_SITE', 'hellomapsusers');
			$form->setFieldAttribute('city', 'description', 'PLG_USER_HELLOMAPSUSERS_FILL_FIELD_DESC_SITE', 'hellomapsusers');
			$form->setFieldAttribute('region', 'description', 'PLG_USER_HELLOMAPSUSERS_FILL_FIELD_DESC_SITE', 'hellomapsusers');
			$form->setFieldAttribute('country', 'description', 'PLG_USER_HELLOMAPSUSERS_FILL_FIELD_DESC_SITE', 'hellomapsusers');
			$form->setFieldAttribute('postal_code', 'description', 'PLG_USER_HELLOMAPSUSERS_FILL_FIELD_DESC_SITE', 'hellomapsusers');*/
			$form->setFieldAttribute('phone', 'description', 'PLG_USER_HELLOMAPSUSERS_FILL_FIELD_DESC_SITE', 'hellomapsusers');
			$form->setFieldAttribute('website', 'description', 'PLG_USER_HELLOMAPSUSERS_FILL_FIELD_DESC_SITE', 'hellomapsusers');
			//$form->setFieldAttribute('favoritebook', 'description', 'PLG_USER_HELLOMAPSUSERS_FILL_FIELD_DESC_SITE', 'hellomapsusers');
			$form->setFieldAttribute('aboutme', 'description', 'PLG_USER_HELLOMAPSUSERS_FILL_FIELD_DESC_SITE', 'hellomapsusers');
		/*	$form->setFieldAttribute('dob', 'description', 'PLG_USER_HELLOMAPSUSERS_FILL_FIELD_DESC_SITE', 'hellomapsusers');
			$form->setFieldAttribute('tos', 'description', 'PLG_USER_HELLOMAPSUSERS_FIELD_TOS_DESC_SITE', 'hellomapsusers');*/
		}



		foreach ($fields as $field)
		{
			// Case using the users manager in admin
			if ($name == 'com_users.user')
			{
				// Remove the field if it is disabled in registration and profile
				if ($this->params->get('register-require_' . $field, 1) == 0
					&& $this->params->get('profile-require_' . $field, 1) == 0
				)
				{
					$form->removeField($field, 'hellomapsusers');
				}
			}
			// Case registration
			elseif ($name == 'com_users.registration')
			{
				// Toggle whether the field is required.
				if ($this->params->get('register-require_' . $field, 1) > 0)
				{
					$form->setFieldAttribute($field, 'required', ($this->params->get('register-require_' . $field) == 2) ? 'required' : '', 'hellomapsusers');
				}
				else
				{
					$form->removeField($field, 'hellomapsusers');
				}

			/*	if ($this->params->get('register-require_dob', 1) > 0)
				{
					$form->setFieldAttribute('spacer', 'type', 'spacer', 'hellomapsusers');
				}*/
			}
			// Case profile in site or admin
			elseif ($name == 'com_users.profile' || $name == 'com_admin.profile')
			{
				// Toggle whether the field is required.
				if ($this->params->get('profile-require_' . $field, 1) > 0)
				{
					$form->setFieldAttribute($field, 'required', ($this->params->get('profile-require_' . $field) == 2) ? 'required' : '', 'hellomapsusers');
				}
				else
				{
					$form->removeField($field, 'hellomapsusers');
				}

				/*if ($this->params->get('profile-require_dob', 1) > 0)
				{
					$form->setFieldAttribute('spacer', 'type', 'spacer', 'hellomapsusers');
				}*/
			}
		}

		return true;
	}

	
	public function onUserAfterSave($data, $isNew, $result, $error)
	{
		$userId = JArrayHelper::getValue($data, 'id', 0, 'int');

		if ($userId && $result && isset($data['hellomapsusers']) && (count($data['hellomapsusers'])))
		{
			try
			{
				// Sanitize the date
				//$data['hellomapsusers']['dob'] = $this->_date;

				$db = JFactory::getDbo();
				$query = $db->getQuery(true)
					->delete($db->quoteName('#__user_profiles'))
					->where($db->quoteName('user_id') . ' = ' . (int) $userId)
					->where($db->quoteName('profile_key') . ' LIKE ' . $db->quote('hellomapsusers.%'));
				$db->setQuery($query);
				$db->execute();

				$tuples = array();
				$order = 1;

				foreach ($data['hellomapsusers'] as $k => $v)
				{
					$tuples[] = '(' . $userId . ', ' . $db->quote('hellomapsusers.' . $k) . ', ' . $db->quote(json_encode($v)) . ', ' . $order++ . ')';
				}

				$db->setQuery('INSERT INTO #__user_profiles VALUES ' . implode(', ', $tuples));
				$db->execute();
			}
			catch (RuntimeException $e)
			{
				$this->_subject->setError($e->getMessage());
				return false;
			}
		}

		return true;
	}

	/**
	 * Remove all user profile information for the given user ID
	 *
	 * Method is called after user data is deleted from the database
	 *
	 * @param   array    $user     Holds the user data
	 * @param   boolean  $success  True if user was succesfully stored in the database
	 * @param   string   $msg      Message
	 *
	 * @return  boolean
	 */
	public function onUserAfterDelete($user, $success, $msg)
	{
		if (!$success)
		{
			return false;
		}

		$userId = JArrayHelper::getValue($user, 'id', 0, 'int');

		if ($userId)
		{
			try
			{
				$db = JFactory::getDbo();
				$db->setQuery(
					'DELETE FROM #__user_profiles WHERE user_id = ' . $userId .
						" AND profile_key LIKE 'hellomapsusers.%'"
				);

				$db->execute();
			}
			catch (Exception $e)
			{
				$this->_subject->setError($e->getMessage());
				return false;
			}
		}

		return true;
	}
}
