<?php
/**
 * sh404SEF - SEO extension for Joomla!
 *
 * @author       Yannick Gaultier
 * @copyright    (c) Yannick Gaultier - Weeblr llc - 2015
 * @package      sh404SEF
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version      4.4.9.2487
 * @date        2015-04-20
 *
 */

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

// ------------------  standard plugin initialize function - don't change ---------------------------
$sefConfig = &Sh404sefFactory::getConfig();
$shLangName = '';
$shLangIso = '';
$title = array();
$shItemidString = '';
$dosef = shInitializePlugin($lang, $shLangName, $shLangIso, $option);
if ($dosef == false)
	return;
// ------------------  standard plugin initialize function - don't change ---------------------------

if (!function_exists('shGetVmShopName'))
{
	function shGetVmShopName($helper, $Itemid)
	{
		static $shopNames = array();

		// figure out shopname
		if (empty($shopNames[$Itemid]))
		{
			$Itemid = empty($helper->menu['virtuemart']) ? $Itemid : $helper->menu['virtuemart'];
			$menuItem = JFactory::getApplication()->getMenu()->getItem($Itemid);
			if (!empty($menuItem))
			{
				$shopNames[$Itemid] = $menuItem->route;
			}
			else
			{
				$shopNames[$Itemid] = 'vm';
			}
		}

		return $shopNames[$Itemid];
	}
}

// get shop name, as title of menu item to shop
ShlSystem_Log::debug('sh404sef', 'Loading component own router.php file from inside com_virtuemart.php');
$functionName = ucfirst(str_replace('com_', '', $option)) . 'BuildRoute';
if (!function_exists($functionName))
{
	include_once(JPATH_ROOT . '/components/' . $option . '/router.php');
}
$originalVars = empty($originalUri) ? $vars : $originalUri->getQuery($asArray = true);
$helper = vmrouterHelper::getInstance($originalVars);
$Itemid = empty($Itemid) ? 0 : $Itemid;
$shopName = shGetVmShopName($helper, $Itemid);

if (count($originalVars) == 2 && !empty($originalVars['Itemid']) && !empty($originalVars['option']))
{
	// use directly menu item
	$item = JFactory::getApplication()->getMenu()->getItem($originalVars['Itemid']);
	if (!empty($item))
	{
		$query = $item->query;
		// // when limitstart is not set, VM2 fetches start from the session, instead
		// of just assuming 0
		if (!empty($query['view']) && $query['view'] == 'category')
		{
			if (!isset($query['limitstart']))
			{
				$limitstart = 0;
				shAddToGETVarsList('limitstart', $limitstart);
				shRemoveFromGETVarsList('limitstart');
			}
		}
		ShlSystem_Log::debug('sh404sef', 'Inside com_virtuemart.php, building url from menu item route');
		$title = array($item->alias);

		// add shop menu item, if asked to, except on main shop page
		$isShopHome = !empty($query['view']) && $query['view'] == 'virtuemart';
		if (!$isShopHome && $sefConfig->shVmInsertShopName)
		{
			array_unshift($title, $shopName);
		}
	}
}

if (empty($title))
{
	// check for shop root url, else normal routing
	if (!empty($originalVars['view']) && $originalVars['view'] == 'virtuemart')
	{
		// if VM is homepage, then that's fine
		if (!shIsAnyHomepage($string))
		{
			// else use menu item alias as slug
			$title[] = $shopName;
			unset($originalVars['view']);
		}
	}
	else
	{
		// various checks as VM2 seem to produce funky non-sef urls
		if (!empty($originalVars['view']) && $originalVars['view'] == 'productdetails')
		{
			if (empty($originalVars['virtuemart_product_id']))
			{
				// request for product details, but product id is 0
				return;
			}
		}

		// when limitstart is not set, VM2 fetches start from the session, instead
		// of just assuming 0
		if (!empty($originalVars['view']) && $originalVars['view'] == 'category')
		{
			if (!isset($originalVars['limitstart']))
			{
				$limitstart = 0;
				shAddToGETVarsList('limitstart', $limitstart);
				shRemoveFromGETVarsList('limitstart');
				// router.php expects this to be start, not limitstart
				$originalVars['start'] = $limitstart;
			}
			else
			{
				$originalVars['start'] = $originalVars['limitstart'];
				unset($originalVars['limitstart']);
			}
		}

		$hasCategoryId = !empty($originalVars['view']) && ($originalVars['view'] == 'category' || $originalVars['view'] == 'productdetails')
			&& !empty($originalVars['virtuemart_category_id']);
		$isProductView = !empty($originalVars['view']) && $originalVars['view'] == 'productdetails'
			&& !empty($originalVars['virtuemart_product_id']);
		$isCategoryView = !empty($originalVars['view']) && $originalVars['view'] == 'category'
			&& !empty($originalVars['virtuemart_category_id']);
		$isCartView = !empty($originalVars['view']) && $originalVars['view'] == 'cart';
		$isUserView = !empty($originalVars['view']) && $originalVars['view'] == 'user';
		$nonSefItemid = empty($originalVars['Itemid']) ? 0 : $originalVars['Itemid'];

		// have router.php build url
		$title = $functionName($originalVars);

		if ($isProductView)
		{
			//if only option and Itemid left, VM wants Joomla router to prepend menu item. Let's do that
			if (empty($title) && count($originalVars) == 2 && !empty($originalVars['Itemid']) && !empty($originalVars['option']))
			{
				$item = JFactory::getApplication()->getMenu()->getItem($originalVars['Itemid']);
				if (!empty($item))
				{
					$validItemid = $originalVars['Itemid'];
				}
			}
			if (!empty($validItemid))
			{
				// we now use the calculated Itemid, either the original one
				// or the one that was swapped in by Virtuemart router.php
				$Itemid = $validItemid;
				$vars['Itemid'] = $validItemid;
				$originalUri->setVar('Itemid', $validItemid);
				shAddToGETVarsList('Itemid', $validItemid);

				// stick the product route
				$prodRoute = $helper->getProductName($vars['virtuemart_product_id']);
				!empty($prodRoute) ? array_unshift($title, $prodRoute) : null;
				$hasCategoryId = false;
			}
		}

		// VM router set the Itemid for category links!!!!
		// instead of doing the routing
		if ($hasCategoryId)
		{
			//if only option and Itemid left, VM wants Joomla router to prepend menu item. Let's do that
			if (count($originalVars) == 2 && !empty($originalVars['Itemid']) && !empty($originalVars['option'])
			)
			{
				$item = JFactory::getApplication()->getMenu()->getItem($originalVars['Itemid']);
				if (!empty($item))
				{
					$validItemid = $originalVars['Itemid'];
				}
			}
			if ($isCategoryView)
			{
				unset($originalVars['categorylayout']);
				$item = JFactory::getApplication()->getMenu()->getItem($originalVars['Itemid']);
				if (!empty($item))
				{
					$validItemid = $originalVars['Itemid'];
				}
			}
			if (!empty($validItemid))
			{
				// we now use the calculated Itemid, either the original one
				// or the one that was swapped in by Virtuemart router.php
				$Itemid = $validItemid;
				$vars['Itemid'] = $validItemid;
				$originalUri->setVar('Itemid', $validItemid);
				shAddToGETVarsList('Itemid', $validItemid);

				// then stick the category route
				$categoryRoute = $helper->getCategoryRoute($vars['virtuemart_category_id']);
				if (!empty($categoryRoute->itemId))
				{
					$menuItem = JFactory::getApplication()->getMenu()->getItem($categoryRoute->itemId);
					$catRoute = empty($menuItem) ? '' : $menuItem->alias;
				}
				!empty($catRoute) ? array_unshift($title, $catRoute) : null;
			}
		}

		if ($isCartView)
		{
			//if only option and Itemid left, VM wants Joomla router to prepend menu item. Let's do that
			if (empty($title) && count($originalVars) == 2 && !empty($originalVars['Itemid']) && !empty($originalVars['option']))
			{
				$item = JFactory::getApplication()->getMenu()->getItem($originalVars['Itemid']);
				if (!empty($item))
				{
					$cartRoute = $item->alias;
					!empty($cartRoute) ? array_unshift($title, $cartRoute) : null;
				}
			}
		}

		if ($isUserView)
		{
			//if only option and Itemid left, VM wants Joomla router to prepend menu item. Let's do that
			if (empty($title) && count($originalVars) == 2 && !empty($originalVars['Itemid']) && !empty($originalVars['option']))
			{
				$item = JFactory::getApplication()->getMenu()->getItem($originalVars['Itemid']);
				if (!empty($item))
				{
					$userRoute = $item->title;
					!empty($userRoute) ? array_unshift($title, $userRoute) : null;
				}
			}
		}

		// add shop menu item, if asked to
		if ($sefConfig->shVmInsertShopName)
		{
			array_unshift($title, $shopName);
		}
	}
}

if (!empty($title))
{
	// add user defined prefix
	$prefix = shGetComponentPrefix($option);
	if (!empty($prefix))
	{
		array_unshift($title, $prefix);
	}
	$title = empty($title) ? $title : sh404sefHelperUrl::encodeSegments($title);
}

// new in VM3:
unset($originalVars['productsublayout']);
unset($originalVars['showcategory']);
unset($originalVars['showproducts']);

// manage GET var lists ourselves, as Joomla router.php does not do it
if (!empty($vars))
{
	// there are some unused GET vars, we must transfer them to our mechanism, so
	// that they are eventually appended to the sef url
	foreach ($vars as $k => $v)
	{
		switch ($k)
		{
			case 'option':
			case 'Itemid':
			case 'lang':
				shRemoveFromGETVarsList($k);
				break;
			default:
				// if variable has not been used in sef url, add it to list of variables to be
				// appended to the url as query string elements
				if (array_key_exists($k, $originalVars))
				{
					shAddToGETVarsList($k, $v);
				}
				else
				{
					shRemoveFromGETVarsList($k);
				}
				break;
		}
	}
}
// ------------------  standard plugin finalize function - don't change ---------------------------
if ($dosef)
{
	$string = shFinalizePlugin($string, $title, $shAppendString, $shItemidString, (isset($limit) ? $limit : null),
		(isset($limitstart) ? $limitstart : null), (isset($shLangName) ? $shLangName : null), (isset($showall) ? $showall : null),
		$suppressPagination = true);
}
// ------------------  standard plugin finalize function - don't change ---------------------------
