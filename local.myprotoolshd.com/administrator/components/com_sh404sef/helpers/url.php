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
if (!defined('_JEXEC'))
	die();

class Sh404sefHelperUrl
{
	static $componentsRouters = array();

	public static function buildUrl($elements, $option = 'com_sh404sef')
	{
		$url = 'index.php?option=' . $option;

		if (is_array($elements) && !empty($elements))
		{
			foreach ($elements as $key => $value)
			{
				$url .= '&' . $key . '=' . $value;
			}
		}

		return $url;
	}

	public static function stripTrackingVarsFromNonSef($url)
	{
		$trackingVars = self::_getTrackingVars();
		return self::stripVarsFromNonSef($url, $trackingVars);
	}

	protected static function _getTrackingVars()
	{
		$trackingVars = Sh404sefFactory::getPConfig()->trackingVars;

		return $trackingVars;
	}

	public static function stripVarsFromNonSef($url, $vars = array())
	{
		if (!empty($vars))
		{
			foreach ($vars as $var)
			{
				$url = Sh404sefHelperUrl::clearUrlVar($url, $var);
			}
		}

		return $url;
	}

	public static function clearUrlVar($string, $var)
	{
		return ShlSystem_Strings::pr('/(&|\?)' . preg_quote($var, '/') . '=[^&]*/iu', '', $string);
	}

	public static function stripTrackingVarsFromSef($url)
	{
		// do we have query vars?
		$parts = explode('?', $url);
		if (empty($parts[1]))
		{
			// no variable parts, return identical
			return $url;
		}

		$trackingVars = self::_getTrackingVars();
		$cleaned = self::stripVarsFromNonSef('?' . $parts[1], $trackingVars);

		// rebuild and return
		$cleaned = JString::ltrim($cleaned, '?&');
		$cleaned = $parts[0] . (empty($cleaned) ? '' : '?' . $cleaned);

		return $cleaned;
	}

	public static function extractTrackingVarsFromNonSef($url, &$existingVars, $keepThem = false)
	{
		$trackingVars = self::_getTrackingVars();
		foreach ($trackingVars as $var)
		{
			// collect existing value, if any
			$value = self::getUrlVar($url, $var, null);
			if (!is_null($value))
			{
				// store extracted value into passed array
				$existingVars[$var] = $value;
			}
			// still remove var from url
			if (!$keepThem)
			{
				$url = Sh404sefHelperUrl::clearUrlVar($url, $var);
			}
		}
		return $url;
	}

	public static function getUrlVar($string, $var, $default = '')
	{
		if (strpos($string, 'index.php?') === 0)
		{
			$string = substr($string, 10);
		}
		$string = str_replace('&amp;', '&', $string); // normalize
		$string = str_replace('&amp;', '&', $string); // normalize #2
		$vars = array();
		parse_str($string, $vars);
		$value = isset($vars[$var]) ? $vars[$var] : $default;

		return $value;
	}

	public static function setUrlVar($string, $var, $value, $canBeEmpty = false)
	{
		if (empty($string) || empty($var))
			return $string;
		if (!$canBeEmpty && empty($value))
		{
			return $string;
		}
		$string = str_replace('&amp;', '&', $string); // normalize
		$exp = '/(&|\?)' . preg_quote($var, '/') . '=[^&]*/iu';
		$result = preg_match($exp, $string);
		if ($result) // var already in URL
			$result = preg_replace($exp, '$1' . $var . '=' . $value, $string);
		else
		{ // var does not exist in URL
			$result = $string . (strpos($string, '?') !== false ? '&' : '?') . $var . '=' . $value;
			$result = Sh404sefHelperUrl::sortUrl($result);
		}
		return $result;
	}

	/**
	 * Sort query key/value pairs in alphabetical
	 * increasing order
	 *
	 * @param string $string the non-sef url, starting with index.php?
	 * @return string
	 */
	public static function sortURL($nonSef)
	{
		// URL must be like : index.php?param2=xxx&option=com_ccccc&param1=zzz
		if ((substr($nonSef, 0, 10) !== 'index.php?'))
		{
			return $nonSef;
		}
		// URL returned will be ! index.php?option=com_ccccc&param1=zzz&param2=xxx
		$ret = '';
		$st = str_replace('&amp;', '&', $nonSef);
		$st = str_replace('index.php', '', $st);
		$st = str_replace('?', '', $st);
		parse_str($st, $shTmpVars);
		$shVars = self::deepEncode($shTmpVars);
		if (count($shVars) > 0)
		{
			ksort($shVars); // sort URL array
			$shNewString = '';
			$ret = 'index.php?';
			foreach ($shVars as $key => $value)
			{
				if (strtolower($key) != 'option')
				{
					// option is always first parameter
					if (is_array($value))
					{
						foreach ($value as $k => $v)
						{
							// fix for arrays, thanks doorknob
							$shNewString .= '&' . $key . '[' . $k . ']=' . $v;
						}
					}
					else
					{
						$shNewString .= '&' . $key . '=' . $value;
					}
				}
				else
				{
					$ret .= $key . '=' . $value;
				}
			}
			$ret .= $ret == 'index.php?' ? JString::ltrim($shNewString, '&') : $shNewString;
		}
		return $ret;
	}

	/**
	 * Url encoding with 2-levels arrays
	 *
	 * @param mixed $data
	 * @return mixed
	 */
	public static function deepEncode($data)
	{
		if (is_array($data))
		{
			foreach ($data as $key => $element)
			{
				$data[$key] = self::deepEncode($element);
			}
			return $data;
		}
		else
		{
			return urlencode($data);
		}
	}

	/**
	 * Get Language tag from url code found in a url
	 *
	 */
	public static function getUrlLang($string)
	{
		$matches = array();
		$string = str_replace('&amp;', '&', $string); // normalize
		$result = preg_match('/(&|\?)lang=[^&]*/i', $string, $matches);
		if (!empty($matches))
		{
			$result = JString::trim($matches[0], '&?');
			$result = str_replace('lang=', '', $result);
			return Sh404sefHelperLanguage::getLangTagFromUrlCode($result);
		}
		return '';
	}

	public static function buildUrlWithRouterphp(&$vars, $option)
	{
		$componentName = substr($option, 4);

		// search for proxy functions first
		// so as to be pre-3.3 compatible
		$functionName = ucfirst($componentName) . 'BuildRoute';
		$fileName = JPATH_ROOT . '/components/' . $option . '/router.php';
		if (!function_exists($functionName) && file_exists($fileName))
		{
			include $fileName;
		}

		if (function_exists($functionName))
		{
			$segments = $functionName($vars);
			return $segments;
		}

		// new API, J!3.3+
		if (version_compare(JVERSION, '3.3', 'ge'))
		{
			// no function, try class
			if (empty(self::$componentsRouters[$componentName]))
			{
				$className = $componentName . 'Router';
				if (!class_exists($className))
				{
					// Use the custom routing handler if it exists
					if (file_exists($fileName))
					{
						require_once $fileName;
					}
				}
				if (class_exists($className))
				{
					$reflection = new ReflectionClass($className);
					if (in_array('JComponentRouter', $reflection->getInterfaceNames()))
					{
						self::$componentsRouters[$componentName] = new $className();
					}
				}
			}
			if (!empty(self::$componentsRouters[$componentName]))
			{
				$segments = self::$componentsRouters[$componentName]->build($vars);
				return $segments;
			}
		}

		return array();
	}

	/**
	 * Encode route segments
	 *
	 * @param   array $segments An array of route segments
	 *
	 * @return  array  Array of encoded route segments
	 *
	 */
	public static function encodeSegments($segments)
	{
		$total = count($segments);
		for ($i = 0; $i < $total; $i++)
		{
			$segments[$i] = str_replace(':', '-', $segments[$i]);
		}

		return $segments;
	}

	/**
	 * Extract original url used to build a JUri object
	 * as it's often modified by Joomla, though needed later
	 *
	 * @param JUri $uri
	 * @return string original url stored in protected property
	 */
	public static function getOriginalUrlFromUri($uri)
	{
		if (version_compare(JVERSION, '3.4', 'ge'))
		{
			// things changed in 3.4, the uri property doesnt't have the full url
			$originalUrl = Sh404sefHelperGeneral::getProtectedProperty('Juri', 'uri', $uri);
			if ($originalUrl == 'index.php')
			{
				$query = $uri->getQuery();
				$originalUrl = empty($query) ? $originalUrl : $originalUrl . '?' . $query;
			}
		}
		else
		{
			$propertyName = version_compare(JVERSION, '3.0', 'ge') ? 'uri' : '_uri';
			$originalUrl = Sh404sefHelperGeneral::getProtectedProperty('Juri', $propertyName, $uri);
		}

		return $originalUrl;
	}

	/**
	 * Extract original path used to build a JUri object
	 * as it's often modified by Joomla, though needed later
	 *
	 * @param JUri $uri
	 * @parma string $base the base url for the uri, usually JURI:: base
	 * @return string original path built from stored in protected property
	 */
	public static function getOriginalPathFromUri($uri, $base)
	{
		$propertyName = version_compare(JVERSION, '3.0', 'ge') ? 'uri' : '_uri';
		$originalUrl = Sh404sefHelperGeneral::getProtectedProperty('Juri', $propertyName, $uri);
		$originalPath = substr_replace(urldecode($originalUrl), '', 0, strlen(JUri::base()));
		$pathBits = explode('?', $originalPath);
		$originalPath = $pathBits[0];

		return $originalPath;
	}

}
