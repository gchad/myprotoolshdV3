<?php
/**
 * @package Sj Responsive Content for K2
 * @version 2.5.0
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2012 YouTech Company. All Rights Reserved.
 * @author YouTech Company http://www.smartaddons.com
 */

// no direct access
defined('_JEXEC') or die;

require_once (JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'helpers'.DS.'route.php');
require_once (JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'helpers'.DS.'utilities.php');
require_once dirname(__FILE__).'/helper_base.php';

class modSjK2ResContentHelper extends K2BaseHelperResponsiveContent
{
	public static function getList(&$params)
	{
		$app = JFactory::getApplication();
		$mode = $params->get('mode', 'normal');
		switch ($mode)
		{
			case 'dynamic':
				$option = $app->input->get('option');
				$view = $app->input->get('view');
				if ($option === 'com_k2') {
					switch($view)
					{
						case 'itemlist':
							$cid = array($app->input->getInt('id'));

							break;
						case 'item':	
						case 'latest':
						case 'comments':	
						default:
						/* 	if ($params->get('catfilter')){
								$cid = $params->get('category_id', NULL);
							} else{
								$itemListModel = K2Model::getInstance('Itemlist', 'K2Model');
								$cid = $itemListModel->getCategoryTree($category=0);
							} */
							return ;
						break;
					}
				}
				else {
					 if ($params->get('catfilter')){
						$cid = $params->get('category_id', NULL);
					} else{
						$itemListModel = K2Model::getInstance('Itemlist', 'K2Model');
						$cid = $itemListModel->getCategoryTree($category=0);
					} 
				}
				break;
			case 'normal':
			default:
				if ($params->get('catfilter')){
					$cid = $params->get('category_id', NULL);
				} else{
					$itemListModel = K2Model::getInstance('Itemlist', 'K2Model');
					$cid = $itemListModel->getCategoryTree($category=0);
				}
				break;
		}
		$items = self::getItems($cid, $params);
		return $items;
	}
}
