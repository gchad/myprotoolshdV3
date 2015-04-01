<?php
/**
 * @package Sj Responsive Listing for K2
 * @version 2.5.0
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2013 YouTech Company. All Rights Reserved.
 * @author YouTech Company http://www.smartaddons.com
 */

// no direct access
defined('_JEXEC') or die;

require_once (JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'helpers'.DS.'route.php');
require_once (JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'helpers'.DS.'utilities.php');
require_once dirname(__FILE__).DS.'helper_base.php';

class K2ResponsiveListing extends K2BaseHelperResponsiveListing
{
	protected static $helper = null;
	public static $total = null;	
	

	public static function getList($params , $module){
		$helper =  self::getInstance();
		return $helper->_getList($params, $module);
	}
	
	protected function _getList($params, $module)
	{
		$list = array();
		$retur = array();	
		if ($params->get('catfilter')){
			$cid = $params->get('category_id', NULL);
		} else{
			$itemListModel = K2Model::getInstance('Itemlist', 'K2Model');
			$cid = $itemListModel->getCategoryTree($category=0);
		}
		if(!empty($cid)){	
			
			$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
			if(!$is_ajax){
				self::$total = self::getTotal($cid,$params);
			}
			$categories = self::getCategories($params);
			foreach($categories as $cat){
					$category = $cat;
					$category->count = 0;
					$list[$category->id] = $category;
			}
				
			$retur['categories'] = $list;
			$items = self::getItems($cid, $params);
			if(!empty($items)){
				foreach($items as $key => $item){
						$category = $list[$item->catid];
						if(isset($category->count)){
							$category->count ++;
						}else{
							$category->count = 1;
						}
				}
				$retur['items'] = $items;				
			}
			
				if ($params->get('tab_all_display', 1)){
					$all = new stdClass();
					$all->id = '*';
					$all->count = count($items);
					$all->title = JText::_('All');
				
					array_unshift($retur['categories'], $all);
				}
				// default select
				$catidpreload = $params->get('category_preload');
				$selected = false;
				foreach ($retur['categories'] as $cat){
					if ( $cat->id == $catidpreload && $cat->count > 0 ){
						$cat->sel = 'sel';
						$selected = true;
					}
				}
				// first tab is active
				if (!$selected){
					foreach ($retur['categories'] as $cat){
						if ($cat->count > 0){
							$cat->sel = 'sel';
							break;
						}
					}
				}
				
			return $retur;
		}else{
			return;
		}
	}
	
	public static function getInstance(){
			if(is_null(self::$helper)){
				$classname = __CLASS__;
				self::$helper = new $classname;
			}
			return self::$helper;
	}
}
