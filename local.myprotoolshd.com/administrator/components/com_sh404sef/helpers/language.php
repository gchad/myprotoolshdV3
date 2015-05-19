<?php
/**
 * sh404SEF - SEO extension for Joomla!
 *
 * @author      Yannick Gaultier
 * @copyright   (c) Yannick Gaultier - Weeblr llc - 2015
 * @package     sh404SEF
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version     4.4.9.2487
 * @date        2015-04-20
 */

// Security check to ensure this file is being included by a parent file.
defined('_JEXEC') or die;

class Sh404sefHelperLanguage
{
	/**
	 * Figures out if a language code should be inserted
	 * into urls for default language
	 *
	 */
	public static function getInsertLangCodeInDefaultLanguage()
	{
		static $shouldInsert = null;

		if (is_null($shouldInsert))
		{
			// default Joomla value is true
			// sh404SEF default always been false
			$shouldInsert = false;

			// try load languagefilter plugin params
			$plugin = JPluginHelper::getPlugin('system', 'languagefilter');
			if (!empty($plugin))
			{
				$params = new JRegistry();
				$params->loadString($plugin->params);
				$shouldInsert = $params->get('remove_default_prefix', false);
				$shouldInsert = empty($shouldInsert);
			}
		}

		return $shouldInsert;
	}

	public static function getLanguageFilterWarning()
	{
		static $displayed = false;

		if (!$displayed)
		{
			$displayed = true;
			$app = JFactory::getApplication();

			// figure out whether we should display the warning
			// only on html page, and on display or info tasks
			$format = $app->input->getCmd('format', 'html');
			if ($format != 'html')
			{
				return '';
			}
			$task = $app->input->getCmd('task', 'display');
			if ($task != 'display' && $task != 'info')
			{
				return '';
			}

			// only if this is supposed to be a ML site
			// that would be if the not-used anymore enableMultiLingualSupport
			// was false
			$sefConfig = Sh404sefFactory::getConfig();
			if (!$sefConfig->enableMultiLingualSupport)
			{
				return '';
			}

			// or if there's only one language on the site
			$languages = JLanguageHelper::getLanguages('sef');
			if (count($languages) <= 1)
			{
				return '';
			}

			// and only if the plugin is not enabled ofc
			$plugin = JPluginHelper::getPlugin('system', 'languagefilter');
			if (!empty($plugin))
			{
				return '';
			}

			// insert message
			$message = JText::_('COM_SH404SEF_LANGUAGEFILTER_PLUGIN_WARNING');
			if (version_compare(JVERSION, '3.0', 'ge'))
			{
				return ShlMvcLayout_Helper::render('com_sh404sef.general.warning', array('warning' => $message));
			}
			else
			{
				return $message;
			}
		}

	}

	/**
	 * Find a language family
	 *
	 * @param object $language a Joomla! language object
	 * @return string a 2 or 3 characters language family code
	 */
	public static function getFamily($language = null)
	{
		if (!is_object($language))
		{
			// get application db instance
			$language = JFactory::getLanguage();
		}

		$code = $language->get('lang');
		$bits = explode('-', $code);
		return empty($bits[0]) ? 'en' : $bits[0];
	}

	/**
	 * Get language tag from a url language code
	 *
	 * @param string $langCode
	 * @return string
	 */
	public static function getLangTagFromUrlCode($langCode)
	{

		$languages = JLanguageHelper::getLanguages('sef');
		if (!empty($languages[$langCode]))
		{
			$urlLangTag = $languages[$langCode]->lang_code;
		}
		else
		{
			$urlLangTag = self::getDefaultLanguageTag();
		}

		return $urlLangTag;
	}

	/**
	 * Get url short language code from a full language tag
	 *
	 * @param string $langTag
	 * @param bool $default if true, return code for default language if not found
	 * @return string
	 */
	public static function getUrlCodeFromTag($langTag, $default = true)
	{
		$languages = JLanguageHelper::getLanguages('lang_code');
		if (!empty($languages[$langTag]))
		{
			$urlLangCode = $languages[$langTag]->sef;
		}
		else if ($default)
		{
			$urlLangTag = self::getDefaultLanguageTag();
			$urlLangCode = $languages[$urlLangTag]->sef;
		}
		else
		{
			$urlLangCode = null;
		}

		return $urlLangCode;
	}

	public static function validateSefLanguageCode($sefLangCode)
	{
		static $codes = null;

		if (!isset($codes[$sefLangCode]))
		{
			$availableLanguages = self::getInstalledLanguagesList();
			foreach ($availableLanguages as $language)
			{
				if ($sefLangCode == $language->sef)
				{
					$codes[$sefLangCode] = true;
					return true;
				}
			}
			$codes[$sefLangCode] = false;
		}

		return $codes[$sefLangCode];
	}

	public static function getDefaultLanguageSef()
	{
		return self::getUrlCodeFromTag(self::getDefaultLanguageTag());
	}

	/**
	 * Get installed front end language list
	 *
	 * @access  private
	 * @return  array
	 */
	public static function getInstalledLanguagesList($site = true)
	{
		static $languages = null;

		if (is_null($languages))
		{
			$db = ShlDbHelper::getDb();

			// is there a languages table ?
			$languages = array();
			$languagesTableName = $db->getPrefix() . 'languages';
			$tablesList = $db->getTableList();
			if (is_array($tablesList) && in_array($languagesTableName, $tablesList))
			{
				try
				{
					$query = 'SELECT * FROM #__languages';
					$db->setQuery($query);
					$languages = $db->loadObjectList();
				}
				catch (Exception $e)
				{
					JError::raiseWarning('SOME_ERROR_CODE', "Error loading languages lists: " . $e->getMessage());
					ShlSystem_Log::error('sh404sef', '%s::%s::%d: %s', __CLASS__, __METHOD__, __LINE__, $e->getMessage());
					return false;
				}
				// match fields name to what we need, those were changed in version 2.2 of JF
				foreach ($languages as $key => $language)
				{
					if (empty($language->id))
					{
						$languages[$key]->id = $language->lang_id;
					}
					if (empty($language->name))
					{
						$languages[$key]->name = $language->title;
					}
					if (empty($language->code))
					{
						$languages[$key]->code = $language->lang_code;
					}
					if (empty($language->shortcode))
					{
						$languages[$key]->shortcode = $language->sef;
					}
					if (empty($language->active) && empty($language->published))
					{
						// drop this language, it is not published
						unset($languages[$key]);
					}
				}
			}
		}

		return $languages;
	}

	/**
	 * Returns the full language tag for the site default language
	 *
	 * @return string
	 */
	public static function getDefaultLanguageTag()
	{
		if (JFactory::getApplication()->isAdmin())
		{
			return JComponentHelper::getParams('com_languages')->get('administrator', 'en-GB');
		}
		else
		{
			return JComponentHelper::getParams('com_languages')->get('site', 'en-GB');
		}
	}

	/**
	 * Returns the full language tag for the site default language
	 *
	 * @return string
	 */
	public static function getLanguageFilterPluginParam($paramName, $default = null)
	{
		static $params = null;

		if (is_null($params))
		{
			$plugin = JPluginHelper::getPlugin('system', 'languagefilter');
			$params = new JRegistry();
			$params->loadString(empty($plugin) ? '' : $plugin->params);
		}

		return is_null($params) ? $default : $params->get($paramName, $default);
	}

	/**
	 * Sets a Jooml! language cookie, deleting existing one if any
	 *
	 * @param string $languageCode the new target language code (ie: en-GB)
	 * @param int $cookieTime cookie duration time, set in params or default to 0
	 */
	public static function setLanguageCookie($languageCode = null, $cookieTime = 0)
	{
		$languageCode = empty($languageCode) ? Sh404sefHelperLanguage::getDefaultLanguageTag() : $languageCode;
		// Create a cookie
		$conf = JFactory::getConfig();
		$cookieName = JApplication::getHash('language');
		$cookieDomain = $conf->get('config.cookie_domain', '');
		$cookiePath = $conf->get('config.cookie_path', '/');
		// set new cookie, both in browser and in internal application vars
		$app = JFactory::getApplication();
		$app->input->cookie->set($cookieName, $languageCode, $cookieTime, $cookiePath, $cookieDomain, false, true);
		JRequest::setVar($cookieName, $languageCode, 'cookie');
		// as Joomla languagefilter also sets this cookie disregarding the path,domain and duration parameters, we must set it twice
		if (version_compare(JVERSION, '3.3', '<'))
		{
			$app->input->cookie->set($cookieName, $languageCode);
		}
	}

}
