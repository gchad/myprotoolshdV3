<?php
/**
 * @version		$Id: view.html.php 1956 2013-04-04 13:40:22Z lefteris.kavadas $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

jimport('joomla.application.component.view');
require_once JPATH_ROOT . '/components/com_k2/models/item.php';

class JAK2FilterViewItemlist extends JAK2FilterView
{

	function display($tpl = null)
	{
		$mainframe = JFactory::getApplication();
		//$params = K2HelperUtilities::getParams('com_k2');
		if ($mainframe->isSite()) {
			$params = $mainframe->getParams('com_jak2filter');
		} else {
			$params = JComponentHelper::getParams('com_jak2filter');
		}
		//JA K2 FILTER - custom params to display well search result with category view
		$params->set('show_page_heading', 1);
		$params->set('catItemImage', 1);
		$params->set('catCatalogMode', 0);
		$params->set('theme', JRequest::getString('theme', $params->get('theme', 'default')));
		$params->def('num_leading_items', 0);
		$params->def('num_leading_columns', 1);
		$params->def('num_primary_items', 9);
		$params->def('num_primary_columns', 3);
		$params->def('num_secondary_items', 0);
		$params->def('num_secondary_columns', 1);
		$params->def('num_links', 0);
		$params->def('num_links_columns', 1);
		
		$model = $this->getModel('itemlist');
		$limitstart = JRequest::getInt('limitstart');
		$view = JRequest::getWord('view');
		$task = JRequest::getWord('task');
		$db = JFactory::getDBO();

		// Add link
		if (K2HelperPermissions::canAddItem())
			$addLink = JRoute::_('index.php?option=com_k2&view=item&task=add&tmpl=component');
		$this->assignRef('addLink', $addLink);

		// Get data depending on task
		switch ($task)
		{
			case 'category' :
				// Get category
				$id = JRequest::getInt('id');
				JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'tables');
				$category = JTable::getInstance('K2Category', 'Table');
				$category->load($id);
				$category->event = new stdClass;

				// State check
				if (!$category->published || $category->trash)
				{
					JError::raiseError(404, JText::_('K2_CATEGORY_NOT_FOUND'));
				}

				// Access check
				$user = JFactory::getUser();
				if (K2_JVERSION != '15')
				{
					if (!in_array($category->access, $user->getAuthorisedViewLevels()))
					{

						if ($user->guest)
						{
							$uri = JFactory::getURI();
							$url = 'index.php?option=com_users&view=login&return='.base64_encode($uri->toString());
							$mainframe->enqueueMessage(JText::_('K2_YOU_NEED_TO_LOGIN_FIRST'), 'notice');
							$mainframe->redirect(JRoute::_($url, false));
						}
						else
						{
							JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
							return;
						}

					}
					$languageFilter = $mainframe->getLanguageFilter();
					$languageTag = JFactory::getLanguage()->getTag();
					if ($languageFilter && $category->language != $languageTag && $category->language != '*')
					{
						return;
					}
				}
				else
				{
					if ($category->access > $user->get('aid', 0))
					{
						if ($user->guest)
						{
							$uri = JFactory::getURI();
							$url = 'index.php?option=com_user&view=login&return='.base64_encode($uri->toString());
							$mainframe->enqueueMessage(JText::_('K2_YOU_NEED_TO_LOGIN_FIRST'), 'notice');
							$mainframe->redirect(JRoute::_($url, false));
						}
						else
						{
							JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
							return;
						}
					}
				}

				// Hide the add new item link if user cannot post in the specific category
				if (!K2HelperPermissions::canAddItem($id))
				{
					unset($this->addLink);
				}

				// Merge params
				$cparams = class_exists('JParameter') ? new JParameter($category->params) : new JRegistry($category->params);

				// Get the meta information before merging params since we do not want them to be inherited
				$category->metaDescription = $cparams->get('catMetaDesc');
				$category->metaKeywords = $cparams->get('catMetaKey');
				$category->metaRobots = $cparams->get('catMetaRobots');
				$category->metaAuthor = $cparams->get('catMetaAuthor');

				if ($cparams->get('inheritFrom'))
				{
					$masterCategory = JTable::getInstance('K2Category', 'Table');
					$masterCategory->load($cparams->get('inheritFrom'));
					$cparams = class_exists('JParameter') ? new JParameter($masterCategory->params) : new JRegistry($masterCategory->params);
				}
				$params->merge($cparams);

				// Category link
				$category->link = urldecode(JRoute::_(K2HelperRoute::getCategoryRoute($category->id.':'.urlencode($category->alias))));

				// Category image
				$category->image = K2HelperUtilities::getCategoryImage($category->image, $params);

				// Category plugins
				$dispatcher = JDispatcher::getInstance();
				JPluginHelper::importPlugin('content');
				$category->text = $category->description;
				if (K2_JVERSION != '15')
				{
					$dispatcher->trigger('onContentPrepare', array('com_k2.category', &$category, &$params, $limitstart));
				}
				else
				{
					$dispatcher->trigger('onPrepareContent', array(&$category, &$params, $limitstart));
				}

				$category->description = $category->text;

				// Category K2 plugins
				$category->event->K2CategoryDisplay = '';
				JPluginHelper::importPlugin('k2');
				$results = $dispatcher->trigger('onK2CategoryDisplay', array(&$category, &$params, $limitstart));
				$category->event->K2CategoryDisplay = trim(implode("\n", $results));
				$category->text = $category->description;
				$dispatcher->trigger('onK2PrepareContent', array(&$category, &$params, $limitstart));
				$category->description = $category->text;

				$this->assignRef('category', $category);
				$this->assignRef('user', $user);

				// Category children
				$ordering = $params->get('subCatOrdering');
				$children = $model->getCategoryFirstChildren($id, $ordering);
				if (count($children))
				{
					foreach ($children as $child)
					{
						if ($params->get('subCatTitleItemCounter'))
						{
							$child->numOfItems = $model->countCategoryItems($child->id);
						}
						$child->image = K2HelperUtilities::getCategoryImage($child->image, $params);
						$child->name = htmlspecialchars($child->name, ENT_QUOTES);
						$child->link = urldecode(JRoute::_(K2HelperRoute::getCategoryRoute($child->id.':'.urlencode($child->alias))));
						$subCategories[] = $child;
					}
					$this->assignRef('subCategories', $subCategories);
				}

				// Set limit
				$limit = $params->get('num_leading_items') + $params->get('num_primary_items') + $params->get('num_secondary_items') + $params->get('num_links');

				// Set featured flag
				JRequest::setVar('featured', $params->get('catFeaturedItems'));

				// Set layout
				$this->setLayout('category');

				// Set title
				$title = $category->name;
				$category->name = htmlspecialchars($category->name, ENT_QUOTES);

				// Set ordering
				if ($params->get('singleCatOrdering'))
				{
					$ordering = $params->get('singleCatOrdering');
				}
				else
				{
					$ordering = $params->get('catOrdering');
				}

				$addHeadFeedLink = $params->get('catFeedLink');

				break;

			case 'user' :
				// Get user
				$id = JRequest::getInt('id');
				$userObject = JFactory::getUser($id);
				$userObject->event = new stdClass;

				// Check user status
				if ($userObject->block)
				{
					JError::raiseError(404, JText::_('K2_USER_NOT_FOUND'));
				}

				// Get K2 user profile
				$userObject->profile = $model->getUserProfile();

				// User image
				$userObject->avatar = K2HelperUtilities::getAvatar($userObject->id, $userObject->email, $params->get('userImageWidth'));

				// User K2 plugins
				$userObject->event->K2UserDisplay = '';
				if (is_object($userObject->profile) && $userObject->profile->id > 0)
				{
					$dispatcher = JDispatcher::getInstance();
					JPluginHelper::importPlugin('k2');
					$results = $dispatcher->trigger('onK2UserDisplay', array(&$userObject->profile, &$params, $limitstart));
					$userObject->event->K2UserDisplay = trim(implode("\n", $results));
					$userObject->profile->url = htmlspecialchars($userObject->profile->url, ENT_QUOTES, 'UTF-8');
				}
				$this->assignRef('user', $userObject);

				$date = JFactory::getDate();
				$now = K2_JVERSION == '15' ? $date->toMySQL() : $date->toSql();
				$this->assignRef('now', $now);

				// Set layout
				$this->setLayout('user');

				// Set limit
				$limit = $params->get('userItemCount');

				// Set title
				$title = $userObject->name;
				$userObject->name = htmlspecialchars($userObject->name, ENT_QUOTES);

				// Set ordering
				$ordering = $params->get('userOrdering');

				$addHeadFeedLink = $params->get('userFeedLink', 1);

				break;

			case 'tag' :
				// Set layout
				$this->setLayout('tag');

				// Set limit
				$limit = $params->get('tagItemCount');

				// Set title
				$title = JText::_('K2_DISPLAYING_ITEMS_BY_TAG').' '.JRequest::getVar('tag');

				// Set ordering
				$ordering = $params->get('tagOrdering');

				$addHeadFeedLink = $params->get('tagFeedLink', 1);

				break;

			case 'search' : 
				// Set layout
				$this->setLayout('category');

				// Set limit
				//$limit = $params->get('genericItemCount');
				//JA K2 FILTER - like category view
				$limit = $params->get('num_leading_items') + $params->get('num_primary_items') + $params->get('num_secondary_items') + $params->get('num_links');

				// Set title
				$title = JText::_('JAK2FILTER_SEARCH_RESULTS').' '.JRequest::getVar('searchword');

				$addHeadFeedLink = $params->get('genericFeedLink', 1);
				//JA K2 FILTER - Set ordering
				$ordering = JRequest::getVar('ordering', $params->get('catOrdering'));

				break;

			case 'date' :
				// Set layout
				$this->setLayout('generic');

				// Set limit
				$limit = $params->get('genericItemCount');

				// Fix wrong timezone
				if (function_exists('date_default_timezone_get'))
				{
					$originalTimezone = date_default_timezone_get();
				}
				if (function_exists('date_default_timezone_set'))
				{
					date_default_timezone_set('UTC');
				}

				// Set title
				if (JRequest::getInt('day'))
				{
					$date = strtotime(JRequest::getInt('year').'-'.JRequest::getInt('month').'-'.JRequest::getInt('day'));
					$dateFormat = (K2_JVERSION == '15') ? '%A, %d %B %Y' : 'l, d F Y';
					$title = JText::_('K2_ITEMS_FILTERED_BY_DATE').' '.JHTML::_('date', $date, $dateFormat);
				}
				else
				{
					$date = strtotime(JRequest::getInt('year').'-'.JRequest::getInt('month'));
					$dateFormat = (K2_JVERSION == '15') ? '%B %Y' : 'F Y';
					$title = JText::_('K2_ITEMS_FILTERED_BY_DATE').' '.JHTML::_('date', $date, $dateFormat);
				}

				// Restore the original timezone
				if (function_exists('date_default_timezone_set') && isset($originalTimezone))
				{
					date_default_timezone_set($originalTimezone);
				}

				// Set ordering
				$ordering = 'rdate';

				$addHeadFeedLink = $params->get('genericFeedLink', 1);

				break;

			default :
				// Set layout
				$this->setLayout('category');
				$user = JFactory::getUser();
				$this->assignRef('user', $user);

				// Set limit
				$limit = $params->get('num_leading_items') + $params->get('num_primary_items') + $params->get('num_secondary_items') + $params->get('num_links');
				// Set featured flag
				JRequest::setVar('featured', $params->get('catFeaturedItems'));

				// Set title
				$title = $params->get('page_title');

				// Set ordering
				$ordering = $params->get('catOrdering');

				$addHeadFeedLink = $params->get('catFeedLink', 1);

				break;
		}



        /******* GCHAD FIX *******/
        
        $_SESSION['limitK2Search'] = 6;
        //$_SESSION['limitK2Search'] = $params->num_primary_items;

        $limitK2Search = $_SESSION['limitK2Search'];
        $limit = $limitK2Search;
        
        JRequest::setVar('limit', $limitK2Search);
        /******* GCHAD FIX *******/
        
        
		// Set limit for model
		if (!$limit){
		    $limit = 10;
            JRequest::setVar('limit', $limit);
		}
          
		// Get items
		if (!isset($ordering))
		{
			$items = $model->getData();
		}
		else
		{
			$items = $model->getData($ordering);
		}
        
		if(count($items)==0){
		   
			//return JError::raiseNotice(500, JText::_('SEARCH_RESULT_NULL'));
		}
   
		// Pagination
		jimport('joomla.html.pagination');
		$total = count($items) ? $model->getTotal() : 0;
        //debug(count($items));
	    //$pagination = new JPagination($total, $limitstart, $limit);
        
        /**** GHAD FIX *****/
        $_SESSION['totalK2Search'] = $total;
        /**** GHAD FIX *****/
		
		//Fix bug: page navigation does not work properly if SEF is enabled
		/*$vars = JRequest::get('get');
		if(count($vars)) {
			foreach ($vars as $k => $v) {
				if($k == 'task') continue;
				if(is_array($v)) {
					foreach ($v as $sk => $sv) {
						$pagination->setAdditionalUrlParam($k.'['.$sk.']', $sv);
					}
				} else {
					$pagination->setAdditionalUrlParam($k, $v);
				}
			}
		}*/

		//Prepare items
		$user = JFactory::getUser();
		$cache = JFactory::getCache('com_k2_extended');
		$model = JModelLegacy::getInstance('item', 'K2Model');
        
        $q = "SELECT value FROM jos_k2_extra_fields WHERE id = 8";
        $db->setQuery($q);
        $db->query();
        $r = json_decode($db->loadResult('id'));
       
        $regionMatrix = array();
        foreach($r as $k => $v){
            $regionMatrix[$v->value] = $v->name;
        }
        

		for ($i = 0; $i < sizeof($items); $i++)
		{

			//Item group
			// JA K2 FILTER - using category view for displaying search result
			if ($task == "category" || $task == "search" || $task == "")
			{
				if ($i < ($params->get('num_links') + $params->get('num_leading_items') + $params->get('num_primary_items') + $params->get('num_secondary_items')))
					$items[$i]->itemGroup = 'links';
				if ($i < ($params->get('num_secondary_items') + $params->get('num_leading_items') + $params->get('num_primary_items')))
					$items[$i]->itemGroup = 'secondary';
				if ($i < ($params->get('num_primary_items') + $params->get('num_leading_items')))
					$items[$i]->itemGroup = 'primary';
				if ($i < $params->get('num_leading_items'))
					$items[$i]->itemGroup = 'leading';
			}

			// Check if the model should use the cache for preparing the item even if the user is logged in
			/*
			// JA K2 Filter: Remove cache method
			if ($user->guest || $task == 'tag' || $task == 'search' || $task == 'date')
			{
				$cacheFlag = true;
			}
			else
			{
				$cacheFlag = true;
				if (K2HelperPermissions::canEditItem($items[$i]->created_by, $items[$i]->catid))
				{
					$cacheFlag = false;
				}
			}

			// Prepare item
			if ($cacheFlag)
			{
				$hits = $items[$i]->hits;
				$items[$i]->hits = 0;
				JTable::getInstance('K2Category', 'Table');
				$items[$i] = $cache->call(array($model, 'prepareItem'), $items[$i], $view, $task);
				$items[$i]->hits = $hits;
			}
			else
			{
				$items[$i] = $model->prepareItem($items[$i], $view, $task);
			}
			*/
			// JA K2 Filter: PrepareItem
			$items[$i] = $model->prepareItem($items[$i], $view, '');

			// Plugins
			$items[$i]->params->set('genericItemIntroText', $params->get('catItemIntroText'));
			$items[$i]->params->set('catItemK2Plugins', $params->get('catItemK2Plugins'));
			$items[$i] = $model->execPlugins($items[$i], 'itemlist', '');

			// Trigger comments counter event
			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin('k2');
			$results = $dispatcher->trigger('onK2CommentsCounter', array(&$items[$i], &$params, $limitstart));
			$items[$i]->event->K2CommentsCounter = trim(implode("\n", $results));

			//JA K2 FILTER - CUSTOM VIEW OPTIONS
			$items[$i]->params->merge($params);
			if(!is_array($items[$i]->extra_fields)) { 
				$items[$i]->extra_fields = $model->getItemExtraFields($items[$i]->extra_fields, $items[$i]);
			}
            
			if ($params->get('catItemIntroTextWordLimit'))
			{
				$items[$i]->introtext = K2HelperUtilities::wordLimit($items[$i]->introtext, $params->get('catItemIntroTextWordLimit'));
			}
			
			//JA K2 FILTER - CUSTOM VIEW OPTIONS - AUTHOR
			if (!empty($items[$i]->created_by_alias))
			{
				$items[$i]->author = new stdClass;
				$items[$i]->author->name = $items[$i]->created_by_alias;
				$items[$i]->author->avatar = K2HelperUtilities::getAvatar('alias');
				$items[$i]->author->link = JURI::root();
			}
			else
			{
				$author = JFactory::getUser($items[$i]->created_by);
				$items[$i]->author = $author;
				$items[$i]->author->link = JRoute::_(K2HelperRoute::getUserRoute($items[$i]->created_by));
				$items[$i]->author->profile = $model->getUserProfile($items[$i]->created_by);
				$items[$i]->author->avatar = K2HelperUtilities::getAvatar($author->id, $author->email, $params->get('userImageWidth'));
			}

			if (!isset($items[$i]->author->profile) || is_null($items[$i]->author->profile))
			{

				$items[$i]->author->profile = new JObject;
				$items[$i]->author->profile->gender = NULL;

			}
			//JA K2 FILTER - CUSTOM VIEW OPTIONS - RATING
			$items[$i]->votingPercentage = $model->getVotesPercentage($items[$i]->id);
			$items[$i]->numOfvotes = $model->getVotesNum($items[$i]->id);
            
            /* GCHAD FIX */
            /* we add back the region because we remove it before */
          
            $region = new stdClass;
            $region->id = 8;
            $region->name = 'Region';
            $region->value = $regionMatrix[$items[$i]->regionXtraField->value];
            $region->type = 'select';
            $region->published = 1;
            $region->ordering = 1;
            $region->alias = 'region';
            $items[$i]->extra_fields[] = $region;
            
            $items[$i]->extraFields->region = $region;
         
            
          
		}

		// Set title
		$document = JFactory::getDocument();
		$application = JFactory::getApplication();
		$menus = $application->getMenu();
		$menu = $menus->getActive();
		if (is_object($menu))
		{
			if (is_string($menu->params))
			{
				$menu_params = K2_JVERSION == '15' ? new JParameter($menu->params) : new JRegistry($menu->params);
			}
			else
			{
				$menu_params = $menu->params;
			}
			$params->set('page_title', $menu_params->get('page_title', $title));
			$params->set('page_heading', $menu_params->get('page_heading', $title));

			// override theming params
			$params_query = new JRegistry($menu->query);
			$params->set('theme', $params_query->get('theme', $params->get('theme')));
		}
		else
		{
			$params->set('page_title', $title);
		}

		// We're adding a new variable here which won't get the appended/prepended site title,
		// when enabled via Joomla!'s SEO/SEF settings
		$params->set('page_title_clean', $title);

		if (K2_JVERSION != '15')
		{
			if ($mainframe->getCfg('sitename_pagetitles', 0) == 1)
			{
				$tmpTitle = JText::sprintf('JPAGETITLE', $mainframe->getCfg('sitename'), $params->get('page_title'));
				$params->set('page_title', $tmpTitle);
			}
			elseif ($mainframe->getCfg('sitename_pagetitles', 0) == 2)
			{
				$tmpTitle = JText::sprintf('JPAGETITLE', $params->get('page_title'), $mainframe->getCfg('sitename'));
				$params->set('page_title', $tmpTitle);
			}
		}
		$document->setTitle($params->get('page_title'));

		// Search - Update the Google Search results container (K2 v2.6.6+)
		/*
		if ($task == 'search')
		{
			$googleSearchContainerID = trim($params->get('googleSearchContainer', 'k2GoogleSearchContainer'));
			if ($googleSearchContainerID == 'k2Container')
			{
				$googleSearchContainerID = 'k2GoogleSearchContainer';
			}
			$params->set('googleSearchContainer', $googleSearchContainerID);
		}
		*/
		// Set metadata for category
		if ($task == 'category')
		{
			if ($category->metaDescription)
			{
				$document->setDescription($category->metaDescription);
			}
			else
			{
				$metaDescItem = preg_replace("#{(.*?)}(.*?){/(.*?)}#s", '', $this->category->description);
				$metaDescItem = strip_tags($metaDescItem);
				$metaDescItem = K2HelperUtilities::characterLimit($metaDescItem, $params->get('metaDescLimit', 150));
				$metaDescItem = htmlspecialchars($metaDescItem, ENT_QUOTES, 'UTF-8');
				$document->setDescription($metaDescItem);
			}
			if ($category->metaKeywords)
			{
				$document->setMetadata('keywords', $category->metaKeywords);
			}
			if ($category->metaRobots)
			{
				$document->setMetadata('robots', $category->metaRobots);
			}
			if ($category->metaAuthor)
			{
				$document->setMetadata('author', $category->metaAuthor);
			}
		}

		if (K2_JVERSION != '15')
		{

			// Menu metadata options
			if ($params->get('menu-meta_description'))
			{
				$document->setDescription($params->get('menu-meta_description'));
			}

			if ($params->get('menu-meta_keywords'))
			{
				$document->setMetadata('keywords', $params->get('menu-meta_keywords'));
			}

			if ($params->get('robots'))
			{
				$document->setMetadata('robots', $params->get('robots'));
			}

			// Menu page display options
			if ($params->get('page_heading'))
			{
				$params->set('page_title', $params->get('page_heading'));
			}
			$params->set('show_page_title', $params->get('show_page_heading'));

		}

		// Pathway
		$pathway = $mainframe->getPathWay();
		if (!isset($menu->query['task']))
			$menu->query['task'] = '';
		if ($menu)
		{
			switch ($task)
			{
				case 'category' :
					if ($menu->query['task'] != 'category' || $menu->query['id'] != JRequest::getInt('id'))
						$pathway->addItem($title, '');
					break;
				case 'user' :
					if ($menu->query['task'] != 'user' || $menu->query['id'] != JRequest::getInt('id'))
						$pathway->addItem($title, '');
					break;

				case 'tag' :
					if ($menu->query['task'] != 'tag' || $menu->query['tag'] != JRequest::getVar('tag'))
						$pathway->addItem($title, '');
					break;

				case 'search' :
				case 'date' :
					$pathway->addItem($title, '');
					break;
			}
		}

		// Feed link
		$config = JFactory::getConfig();
		$menu = $application->getMenu();
		$default = $menu->getDefault();
		$active = $menu->getActive();
		if ($task == 'tag')
		{
			$link = K2HelperRoute::getTagRoute(JRequest::getVar('tag'));
		}
		else
		{
			$link = '';
		}
		$sef = K2_JVERSION == '30' ? $config->get('sef') : $config->getValue('config.sef');
		if (!is_null($active) && $active->id == $default->id && $sef)
		{
			$link .= '&Itemid='.$active->id.'&format=feed&limitstart=';
		}
		else
		{
			$link .= '&format=feed&limitstart=';
		}

		$feed = JRoute::_($link);
		$this->assignRef('feed', $feed);

		// Add head feed link
		if ($addHeadFeedLink)
		{
			$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
			$document->addHeadLink(JRoute::_($link.'&type=rss'), 'alternate', 'rel', $attribs);
			$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
			$document->addHeadLink(JRoute::_($link.'&type=atom'), 'alternate', 'rel', $attribs);
		}

		//JA K2 FILTER - custom item view option
		// Assign data
		if ($task == "category" || $task == "search" || $task == "")
		{
			$leading = @array_slice($items, 0, $params->get('num_leading_items'));
			$primary = @array_slice($items, $params->get('num_leading_items'), $params->get('num_primary_items'));
			$secondary = @array_slice($items, $params->get('num_leading_items') + $params->get('num_primary_items'), $params->get('num_secondary_items'));
			$links = @array_slice($items, $params->get('num_leading_items') + $params->get('num_primary_items') + $params->get('num_secondary_items'), $params->get('num_links'));
			$this->assignRef('leading', $leading);
			$this->assignRef('primary', $primary);
			$this->assignRef('secondary', $secondary);
			$this->assignRef('links', $links);
		}
		else
		{
			$this->assignRef('items', $items);
		}

		// Set default values to avoid division by zero
		if ($params->get('num_leading_columns') == 0)
			$params->set('num_leading_columns', 1);
		if ($params->get('num_primary_columns') == 0)
			$params->set('num_primary_columns', 1);
		if ($params->get('num_secondary_columns') == 0)
			$params->set('num_secondary_columns', 1);
		if ($params->get('num_links_columns') == 0)
			$params->set('num_links_columns', 1);

		$this->assignRef('params', $params);
		$this->assignRef('pagination', $pagination);

		// Set Facebook meta data
		$document = JFactory::getDocument();
		$uri = JURI::getInstance();
		$document->setMetaData('og:url', $uri->toString());
		$document->setMetaData('og:title', htmlspecialchars($document->getTitle(), ENT_QUOTES, 'UTF-8'));
		$document->setMetaData('og:type', 'website');
		if ($task == 'category' && $this->category->image && strpos($this->category->image, 'placeholder/category.png') === false)
		{
			$image = substr(JURI::root(), 0, -1).str_replace(JURI::root(true), '', $this->category->image);
			$document->setMetaData('og:image', $image);
			$document->setMetaData('image', $image);
		}
		$document->setMetaData('og:description', htmlspecialchars(strip_tags($document->getDescription()), ENT_QUOTES, 'UTF-8'));

		// Look for template files in component folders
		$this->_addPath('template', JPATH_BASE.DS.'components'.DS.'com_k2'.DS.'templates');
		$this->_addPath('template', JPATH_BASE.DS.'components'.DS.'com_k2'.DS.'templates'.DS.'default');

		// Look for overrides in template folder (K2 template structure)
		$this->_addPath('template', JPATH_SITE.DS.'templates'.DS.$mainframe->getTemplate().DS.'html'.DS.'com_k2'.DS.'templates');
		$this->_addPath('template', JPATH_SITE.DS.'templates'.DS.$mainframe->getTemplate().DS.'html'.DS.'com_k2'.DS.'templates'.DS.'default');

		// Look for overrides in template folder (Joomla! template structure)
		$this->_addPath('template', JPATH_SITE.DS.'templates'.DS.$mainframe->getTemplate().DS.'html'.DS.'com_k2'.DS.'default');
		$this->_addPath('template', JPATH_SITE.DS.'templates'.DS.$mainframe->getTemplate().DS.'html'.DS.'com_k2');

		// Look for specific K2 theme files
		if ($params->get('theme'))
		{
			$this->_addPath('template', JPATH_BASE.DS.'components'.DS.'com_k2'.DS.'templates'.DS.$params->get('theme'));
			$this->_addPath('template', JPATH_SITE.DS.'templates'.DS.$mainframe->getTemplate().DS.'html'.DS.'com_k2'.DS.'templates'.DS.$params->get('theme'));
			$this->_addPath('template', JPATH_SITE.DS.'templates'.DS.$mainframe->getTemplate().DS.'html'.DS.'com_k2'.DS.$params->get('theme'));
		}

		$nullDate = $db->getNullDate();
		$this->assignRef('nullDate', $nullDate);
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('k2');
		$dispatcher->trigger('onK2BeforeViewDisplay');
		// Prevent spammers from using the tag view
		if ($task == 'tag' && !count($this->items))
		{
			$tag = JRequest::getString('tag');
			$db = JFactory::getDBO();
			$db->setQuery('SELECT id FROM #__k2_tags WHERE name = '.$db->quote($tag));
			$tagID = $db->loadResult();
			if (!$tagID)
			{
				JError::raiseError(404, JText::_('K2_NOT_FOUND'));
				return false;
			}
		}

		$badchars = array('#', '>', '<', '\\');
		$searchword = JString::trim(JString::str_ireplace($badchars, '', JRequest::getString('searchword', null)));

		if($params->get('enableHighlightSearchTerm', 0)) {
			$document->addScript(JURI::root(true).'/modules/mod_jak2filter/assets/jquery/jquery.highlight-4.js');
			$document->addStyleDeclaration('.highlight { background-color: #FFFFCC }');
			if(!empty($searchword) && strpos($searchword, '-') !== 0) {
				$document->addScriptDeclaration("
				(function($) {
					$(document).ready(function(){
						if($('#k2Container').length) {
        					jak2Highlight($('#k2Container'), \"".addslashes($searchword)."\");
						}
					});
				})(jQuery);
				");
			}
		}

		parent::display($tpl);
	}

}
