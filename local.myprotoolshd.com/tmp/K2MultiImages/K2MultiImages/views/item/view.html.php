<?php
/**
 * @version		$Id: view.html.php 1511 2012-03-01 21:41:16Z joomlaworks $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2012 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

class K2ViewItem extends K2View {

	function display($tpl = null) {
		$mainframe = &JFactory::getApplication();
		$user = &JFactory::getUser();
		$document = &JFactory::getDocument();
		$params = &K2HelperUtilities::getParams('com_k2');
		$limitstart = JRequest::getInt('limitstart', 0);
		$view = JRequest::getWord('view');
		$task = JRequest::getWord('task');

		$db = &JFactory::getDBO();
		$jnow = &JFactory::getDate();
		$now = $jnow->toSQL();
		$nullDate = $db->getNullDate();
		
		//added
		$plugin = JPluginHelper::getPlugin('system', 'k2multiimages');
		$plgParams = class_exists('JParameter') ? new JParameter($plugin->params) : new JRegistry($plugin->params);
		
		$template = JRequest::getVar("template_name");
		
		if($template == '') {
			$template = $plgParams->get("template", "standart");
		}

		$pluginPathSite = JURI::root().'plugins/system/k2multiimages/K2MultiImages';
		$document->addScript($pluginPathSite.'/assets/jquery-migrate-1.2.1.min.js');
		
		switch($template) {
		
			case "slider" :
				$this->setLayout('item-multiimages-slider');
			break;			
			
			case "slider-hs" :
				$this->setLayout('item-multiimages-slider-hs');
			break;
			
			case "default" :
				$this->setLayout('item-multiimages-default');
			break;
			
			default :
				$this->setLayout('item-multiimages');
			break;
		
		}
		//added
		
		// Add link
		if (K2HelperPermissions::canAddItem())
		$addLink = JRoute::_('index.php?option=com_k2&view=item&task=add&tmpl=component');
		$this->assignRef('addLink', $addLink);

		// Get item
		//added
		$version = new JVersion;
		$joomlaVersion = $version->RELEASE;	

		if($joomlaVersion < 1.6) {
			$pluginPath = JPATH_BASE.DS.'plugins'.DS.'system'.DS.'K2MultiImages';
		}
		else {
			$pluginPath = JPATH_BASE.DS.'plugins'.DS.'system'.DS.'k2multiimages'.DS.'K2MultiImages';
		}
		
		require_once (JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'controllers'.DS.'item.php');
		$controller = new K2ControllerItem;							
	
		$controller->addModelPath($pluginPath.DS."models");
		
		$model = &$controller->getModel('itemmultiimages');
		//added
		$item = $model->getData();
		
		// Does the item exists?
		if (!is_object($item) || !$item->id) {
			JError::raiseError(404, JText::_('K2_ITEM_NOT_FOUND'));
		}

		// Prepare item
		$item = $model->prepareItem($item, $view, $task);

		// Plugins
		$item = $model->execPlugins($item, $view, $task);

		// User K2 plugins
		$item->event->K2UserDisplay = '';
		if (isset($item->author) && is_object($item->author->profile) && isset($item->author->profile->id)) {
			$dispatcher = &JDispatcher::getInstance();
			JPluginHelper::importPlugin('k2');
			$results = $dispatcher->trigger('onK2UserDisplay', array(&$item->author->profile, &$params, $limitstart));
			$item->event->K2UserDisplay = trim(implode("\n", $results));
		}

		// Access check
		if ($this->getLayout() == 'form') {
			JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
		}

		if (!in_array($item->access, $user->getAuthorisedViewLevels()) || !in_array($item->category->access, $user->getAuthorisedViewLevels())) {
			JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));
		}

		// Published check
		if (!$item->published || $item->trash) {
			JError::raiseError(404, JText::_('K2_ITEM_NOT_FOUND'));
		}

		if ($item->publish_up != $nullDate && $item->publish_up > $now) {
			JError::raiseError(404, JText::_('K2_ITEM_NOT_FOUND'));
		}

		if ($item->publish_down != $nullDate && $item->publish_down < $now) {
			JError::raiseError(404, JText::_('K2_ITEM_NOT_FOUND'));
		}

		if (!$item->category->published || $item->category->trash) {
			JError::raiseError(404, JText::_('K2_ITEM_NOT_FOUND'));
		}

		// Increase hits counter
		$model->hit($item->id);

		// Set default image
		//added
		K2ModelItemMultiImages::setDefaultImage($item, $view);
		//added
		
		// Pass the old parameter to the view in order to avoid layout changes
		if ($params->get('antispam') == 'recaptcha' || $params->get('antispam') == 'both')
		{
			$params->set('recaptcha', true);
			$item->params->set('recaptcha', true);
		}
		else
		{
			$params->set('recaptcha', false);
			$item->params->set('recaptcha', false);
		}

		// Comments
		$item->event->K2CommentsCounter = '';
		$item->event->K2CommentsBlock = '';
		if ($item->params->get('itemComments'))
		{
			// Trigger comments events
			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin('k2');
			$results = $dispatcher->trigger('onK2CommentsCounter', array(
				&$item,
				&$params,
				$limitstart
			));
			$item->event->K2CommentsCounter = trim(implode("\n", $results));
			$results = $dispatcher->trigger('onK2CommentsBlock', array(
				&$item,
				&$params,
				$limitstart
			));
			$item->event->K2CommentsBlock = trim(implode("\n", $results));

			// Load K2 native comments system only if there are no plugins overriding it
			if (empty($item->event->K2CommentsCounter) && empty($item->event->K2CommentsBlock))
			{

				// Load reCAPTCHA script
				if (!JRequest::getInt('print') && ($item->params->get('comments') == '1' || ($item->params->get('comments') == '2' && K2HelperPermissions::canAddComment($item->catid))))
				{

					if ($params->get('recaptcha') && ($user->guest || $params->get('recaptchaForRegistered', 1)))
					{
						$document->addScript('https://www.google.com/recaptcha/api/js/recaptcha_ajax.js');
						$js = '
						function showRecaptcha(){
							Recaptcha.create("'.$item->params->get('recaptcha_public_key').'", "recaptcha", {
								theme: "'.$item->params->get('recaptcha_theme', 'clean').'"
							});
						}
						$K2(window).load(function() {
							showRecaptcha();
						});
						';
						$document->addScriptDeclaration($js);
					}
				}

				// Check for inline comment moderation
				if (!$user->guest && $user->id == $item->created_by && $params->get('inlineCommentsModeration'))
				{
					$inlineCommentsModeration = true;
					$commentsPublished = false;
				}
				else
				{
					$inlineCommentsModeration = false;
					$commentsPublished = true;
				}
				$this->assignRef('inlineCommentsModeration', $inlineCommentsModeration);

				// Flag spammer link
				$reportSpammerFlag = false;
				if (K2_JVERSION != '15')
				{
					if ($user->authorise('core.admin', 'com_k2'))
					{
						$reportSpammerFlag = true;
						$document = JFactory::getDocument();
						$document->addScriptDeclaration('var K2Language = ["'.JText::_('K2_REPORT_USER_WARNING', true).'"];');
					}
				}
				else
				{
					if ($user->gid > 24)
					{
						$reportSpammerFlag = true;
					}
				}

				$limit = $params->get('commentsLimit');
				$comments = $model->getItemComments($item->id, $limitstart, $limit, $commentsPublished);

				for ($i = 0; $i < sizeof($comments); $i++)
				{
					$comments[$i]->commentText = nl2br($comments[$i]->commentText);

					// Convert URLs to links properly
					$comments[$i]->commentText = preg_replace("/([^\w\/])(www\.[a-z0-9\-]+\.[a-z0-9\-]+)/i", "$1http://$2", $comments[$i]->commentText);
					$comments[$i]->commentText = preg_replace("/([\w]+:\/\/[\w-?&;#~=\.\/\@]+[\w\/])/i", "<a target=\"_blank\" rel=\"nofollow\" href=\"$1\">$1</A>", $comments[$i]->commentText);
					$comments[$i]->commentText = preg_replace("/([\w-?&;#~=\.\/]+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,3}|[0-9]{1,3})(\]?))/i", "<a href=\"mailto:$1\">$1</A>", $comments[$i]->commentText);

					$comments[$i]->userImage = K2HelperUtilities::getAvatar($comments[$i]->userID, $comments[$i]->commentEmail, $params->get('commenterImgWidth'));
					if ($comments[$i]->userID > 0)
					{
						$comments[$i]->userLink = K2HelperRoute::getUserRoute($comments[$i]->userID);
					}
					else
					{
						$comments[$i]->userLink = $comments[$i]->commentURL;
					}
					if ($reportSpammerFlag && $comments[$i]->userID > 0)
					{
						$comments[$i]->reportUserLink = JRoute::_('index.php?option=com_k2&view=comments&task=reportSpammer&id='.$comments[$i]->userID.'&format=raw');
					}
					else
					{
						$comments[$i]->reportUserLink = false;
					}
				}

				$item->comments = $comments;

				jimport('joomla.html.pagination');
				$total = $item->numOfComments;
				$pagination = new JPagination($total, $limitstart, $limit);

			}

		}

		// Author's latest items
		if ($item->params->get('itemAuthorLatest') && $item->created_by_alias == '') {
			$model = &$this->getModel('itemlist');
			$authorLatestItems = $model->getAuthorLatest($item->id, $item->params->get('itemAuthorLatestLimit'), $item->created_by);
			if (count($authorLatestItems)) {
				for ($i = 0; $i < sizeof($authorLatestItems); $i++) {
					$authorLatestItems[$i]->link = urldecode(JRoute::_(K2HelperRoute::getItemRoute($authorLatestItems[$i]->id.':'.urlencode($authorLatestItems[$i]->alias), $authorLatestItems[$i]->catid.':'.urlencode($authorLatestItems[$i]->categoryalias))));
				}
				$this->assignRef('authorLatestItems', $authorLatestItems);
			}
		}

		// Related items
		if ($item->params->get('itemRelated') && isset($item->tags) && count($item->tags)) {
			$model = &$this->getModel('itemlist');
			$relatedItems = $model->getRelatedItems($item->id, $item->tags, $item->params);
			if (count($relatedItems)) {
				for ($i = 0; $i < sizeof($relatedItems); $i++) {
					$relatedItems[$i]->link = urldecode(JRoute::_(K2HelperRoute::getItemRoute($relatedItems[$i]->id.':'.urlencode($relatedItems[$i]->alias), $relatedItems[$i]->catid.':'.urlencode($relatedItems[$i]->categoryalias))));
				}
				$this->assignRef('relatedItems', $relatedItems);
			}

		}

		// Navigation (previous and next item)
		if ($item->params->get('itemNavigation')) {
			$model = &$this->getModel('item');

			$nextItem = $model->getNextItem($item->id, $item->catid, $item->ordering);
			if (!is_null($nextItem)) {
				$item->nextLink = urldecode(JRoute::_(K2HelperRoute::getItemRoute($nextItem->id.':'.urlencode($nextItem->alias), $nextItem->catid.':'.urlencode($item->category->alias))));
				$item->nextTitle = $nextItem->title;
			}

			$previousItem = $model->getPreviousItem($item->id, $item->catid, $item->ordering);
			if (!is_null($previousItem)) {
				$item->previousLink = urldecode(JRoute::_(K2HelperRoute::getItemRoute($previousItem->id.':'.urlencode($previousItem->alias), $previousItem->catid.':'.urlencode($item->category->alias))));
				$item->previousTitle = $previousItem->title;
			}

		}

		// Absolute URL
		$uri = &JURI::getInstance();
		$item->absoluteURL = $uri->toString();

		// Email link
		require_once(JPATH_SITE . '/components/com_mailto/helpers/mailto.php');
		$template = $mainframe->getTemplate();
		$item->emailLink = JRoute::_('index.php?option=com_mailto&tmpl=component&template='.$template.'&link='.MailToHelper::addLink($item->absoluteURL));
		
		// Twitter link (legacy code)
		if($params->get('twitterUsername')) {
			$item->twitterURL = 'http://twitter.com/intent/tweet?text='.urlencode($item->title).'&amp;url='.urlencode($item->absoluteURL).'&amp;via='.$params->get('twitterUsername');
		} else {
			$item->twitterURL = 'http://twitter.com/intent/tweet?text='.urlencode($item->title).'&amp;url='.urlencode($item->absoluteURL);
		}

		// Social link
		$item->socialLink = urlencode($item->absoluteURL);

		// Set page title
		$menus = $mainframe->getMenu();
		$menu = $menus->getActive();
		if (is_object($menu) && isset($menu->query['view']) && $menu->query['view'] == 'item' && isset($menu->query['id']) && $menu->query['id'] == $item->id)
		{
			if (is_string($menu->params))
			{
				$menu_params = K2_JVERSION == '15' ? new JParameter($menu->params) : new JRegistry($menu->params);
			}
			else
			{
				$menu_params = $menu->params;
			}
			if (!$menu_params->get('page_title'))
			{
				$params->set('page_title', $item->cleanTitle);
			}
		}
		else
		{
			$params->set('page_title', $item->cleanTitle);
		}

		if (K2_JVERSION != '15')
		{
			if ($mainframe->getCfg('sitename_pagetitles', 0) == 1)
			{
				$title = JText::sprintf('JPAGETITLE', $mainframe->getCfg('sitename'), $params->get('page_title'));
				$params->set('page_title', $title);
			}
			elseif ($mainframe->getCfg('sitename_pagetitles', 0) == 2)
			{
				$title = JText::sprintf('JPAGETITLE', $params->get('page_title'), $mainframe->getCfg('sitename'));
				$params->set('page_title', $title);
			}
		}
		$document->setTitle($params->get('page_title'));

		// Set pathway
		$menus = &JSite::getMenu();
		$menu = $menus->getActive();
		$pathway = &$mainframe->getPathWay();
		if($menu) {
			if($menu->query['view']!='item' || $menu->query['id']!= $item->id){
				if(!isset($menu->query['task']) || $menu->query['task']!='category' || $menu->query['id']!= $item->catid)
				$pathway->addItem($item->category->name, $item->category->link);
				$pathway->addItem($item->cleanTitle, '');
			}
		}

		// Set metadata
		if ($item->metadesc) {
			$document->setDescription($item->metadesc);
		}
		else {
			$metaDescItem = preg_replace("#{(.*?)}(.*?){/(.*?)}#s", '', $item->introtext.' '.$item->fulltext);
			$metaDescItem = K2HelperUtilities::characterLimit($metaDescItem, $params->get('metaDescLimit', 150));
			$metaDescItem = htmlentities($metaDescItem, ENT_QUOTES, 'utf-8');
			$document->setDescription($metaDescItem);
		}
		if ($item->metakey) {
			$document->setMetadata('keywords', $item->metakey);
		}
		else {
			if(isset($item->tags) && count($item->tags)){
				$tmp = array();
				foreach($item->tags as $tag){
					$tmp[]=$tag->name;
				}
				$document->setMetadata('keywords', implode(',', $tmp));
			}
		}

		// Menu metadata for Joomla! 1.6/1.7 (Overrides the current metadata if set)

			if ($params->get('menu-meta_description')) {
				$document->setDescription($params->get('menu-meta_description'));
			}

			if ($params->get('menu-meta_keywords')) {
				$document->setMetadata('keywords', $params->get('menu-meta_keywords'));
			}

			if ($params->get('robots')) {
				$document->setMetadata('robots', $params->get('robots'));
			}

			// Menu page display options
			if($params->get('page_heading')) {
				$params->set('page_title', $params->get('page_heading'));
			}
			$params->set('show_page_title', $params->get('show_page_heading'));

		if ($mainframe->getCfg('MetaTitle') == '1') {
			$document->setMetadata('title', $item->title);
		}
		if ($mainframe->getCfg('MetaAuthor') == '1' && isset($item->author->name)) {
			$document->setMetadata('author', $item->author->name);
		}
		$mdata = class_exists('JParameter') ? new JParameter($item->metadata) : new JRegistry($item->metadata);
		$mdata = $mdata->toArray();
		foreach ($mdata as $k=>$v) {
			if ($k == 'robots' || $k == 'author') {
				if ($v)
				$document->setMetadata($k, $v);
			}
		}
		
		// Set Facebook meta data
		$document = JFactory::getDocument();
		$uri = JURI::getInstance();
		$document->setMetaData('og:url', $uri->toString());
		$document->setMetaData('og:title', (K2_JVERSION == '15') ? htmlspecialchars($document->getTitle(), ENT_QUOTES, 'UTF-8') : $document->getTitle());
		$document->setMetaData('og:type', 'article');
		$facebookImage = 'image'.$params->get('facebookImage', 'Small');
		if ($item->$facebookImage)
		{
			$parts = parse_url($item->$facebookImage);
			if (JFile::exists(JPATH_SITE.$parts['path']))
			{
				$image = substr(JURI::root(), 0, -1).str_replace(JURI::root(true), '', $item->$facebookImage);
				$document->setMetaData('og:image', $image);
				$document->setMetaData('image', $image);
			}
		}
		$document->setMetaData('og:description', strip_tags($document->getDescription()));

		// Look for template files in component folders
		$this->_addPath('template', JPATH_COMPONENT.DS.'templates');
		$this->_addPath('template', JPATH_COMPONENT.DS.'templates'.DS.'default');

		// Look for overrides in template folder (K2 template structure)
		$this->_addPath('template', JPATH_SITE.DS.'templates'.DS.$mainframe->getTemplate().DS.'html'.DS.'com_k2'.DS.'templates');
		$this->_addPath('template', JPATH_SITE.DS.'templates'.DS.$mainframe->getTemplate().DS.'html'.DS.'com_k2'.DS.'templates'.DS.'default');

		// Look for overrides in template folder (Joomla! template structure)
		$this->_addPath('template', JPATH_SITE.DS.'templates'.DS.$mainframe->getTemplate().DS.'html'.DS.'com_k2'.DS.'default');
		$this->_addPath('template', JPATH_SITE.DS.'templates'.DS.$mainframe->getTemplate().DS.'html'.DS.'com_k2');

		// Look for specific K2 theme files
		if ($item->params->get('theme')) {
			$this->_addPath('template', JPATH_COMPONENT.DS.'templates'.DS.$item->params->get('theme'));
			$this->_addPath('template', JPATH_SITE.DS.'templates'.DS.$mainframe->getTemplate().DS.'html'.DS.'com_k2'.DS.'templates'.DS.$item->params->get('theme'));
			$this->_addPath('template', JPATH_SITE.DS.'templates'.DS.$mainframe->getTemplate().DS.'html'.DS.'com_k2'.DS.$item->params->get('theme'));
		}

		// Assign data
		$this->assignRef('item', $item);
		$this->assignRef('user', $user);
		$this->assignRef('params', $item->params);
		$this->assignRef('pagination', $pagination);

		parent::display($tpl);
	}

}
