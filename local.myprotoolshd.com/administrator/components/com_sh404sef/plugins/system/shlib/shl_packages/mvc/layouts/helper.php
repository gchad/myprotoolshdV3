<?php
/**
 * Shlib - programming library
 *
 * @author      Yannick Gaultier
 * @copyright   (c) Yannick Gaultier 2014
 * @package     shlib
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version     0.2.11.388
 * @date		2014-11-07
 */

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die;

Class ShlMvcLayout_Helper
{
	public static $defaultBasePath = '';

	public static function render($layoutFile, $displayData = null, $basePath = '')
	{
		$basePath = empty($basePath) ? self::$defaultBasePath : $basePath;
		$layout = new ShlMvcLayout_File($layoutFile, $basePath);
		$renderedLayout = $layout->render($displayData);

		return $renderedLayout;
	}
}
