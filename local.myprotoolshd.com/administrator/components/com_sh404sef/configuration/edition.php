<?php
/**
 * sh404SEF - SEO extension for Joomla!
 *
 * @author      Yannick Gaultier
 * @copyright   (c) Yannick Gaultier - Weeblr llc - 2015
 * @package     sh404SEF
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version     4.4.9.2487
 * @date		2015-04-20
 */
class Sh404sefConfigurationEdition
{
	public static $id = 'full';
	public static $name = '';

	public static function is($edition)
	{
		return $edition == self::id;
	}
}
