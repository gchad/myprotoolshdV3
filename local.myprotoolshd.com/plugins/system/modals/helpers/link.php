<?php
/**
 * Plugin Helper File: Link
 *
 * @package         Modals
 * @version         5.4.0
 *
 * @author          Peter van Westen <peter@nonumber.nl>
 * @link            http://www.nonumber.nl
 * @copyright       Copyright © 2015 NoNumber All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

class plgSystemModalsHelperLink
{
	var $helpers = array();

	public function __construct()
	{
		require_once __DIR__ . '/helpers.php';
		$this->helpers = plgSystemModalsHelpers::getInstance();
		$this->params = $this->helpers->getParams();
	}

	public function buildLink($attributes, $data, $content = '')
	{
		if (isset($data['gallery']) && strpos($data['gallery'], '/') !== false)
		{
			return $this->helpers->get('gallery')->buildGallery($attributes, $data, $content);
		}

		$this->fixUrl($attributes->href);

		$isexternal = $this->helpers->get('file')->isExternal($attributes->href);
		$ismedia = $this->helpers->get('file')->isMedia($attributes->href);
		$isiframe = $this->helpers->get('file')->isIframe($attributes->href, $data);

		if ($ismedia)
		{
			$auto_titles = isset($data['title']) ? 0 : (isset($data['auto_titles']) ? $data['auto_titles'] : $this->params->auto_titles);
			$title_case = isset($data['title_case']) ? $data['title_case'] : $this->params->title_case;
			if ($auto_titles)
			{
				$data['title'] = $this->helpers->get('file')->getTitle($attributes->href, $title_case);
			}
		}
		unset($data['auto_titles']);

		// Force/overrule certain data values
		if ($isiframe || ($isexternal && !$ismedia))
		{
			// use iframe mode for external urls
			$data['iframe'] = 'true';
			$this->helpers->get('data')->setDataWidthHeight($data, $isexternal);
		}

		if ($attributes->href && $attributes->href['0'] != '#' && !$isexternal && !$ismedia)
		{
			$this->helpers->get('scripts')->addTmpl($attributes->href, $isiframe);
		}

		// Set open value based on sessions with openMin / openMax
		$this->helpers->get('data')->setDataOpen($data, $attributes);

		if (empty($data['group']) && $this->params->auto_group && preg_match('#' . $this->params->auto_group_filter . '#', $attributes->href))
		{
			$data['group'] = $this->params->auto_group_id;
		}

		if (!empty($data['description']))
		{
			$data['title'] = empty($data['title']) ? '' : $data['title'];
			$data['title'] .= '<div class="modals_description">' . $data['description'] . '</div>';
			unset($data['description']);
		}

		if (empty($data['title']) && empty($attributes->{'data-modal-title'}))
		{
			$data['classname'] = (isset($data['classname']) ? $data['classname'] . ' ' : '') . 'no_title';
			$data['title'] = '';
		}

		if (!empty($data['autoclose']))
		{
			$data['title'] .= '<div class="countdown"></div>';
		}

		return
			'<a'
			. $this->helpers->get('data')->flattenAttributeList($attributes)
			. $this->helpers->get('data')->flattenDataAttributeList($data)
			. '>'
			. $content;
	}

	public function getLink($string, $link = '', $content = '')
	{
		$attributes = $this->prepareLinkAttributeList($link);

		// map href to url
		$string = preg_replace('#^href=#', 'url=', $string);

		$tag = nnTags::getTagValues(
			$string,
			($attributes->href ? array() : array('url'))
		);

		if (!empty($tag->url))
		{
			$attributes->href = $tag->url;
		}
		unset($tag->url);

		$extra = '';

		// Place attribute values found in the href, like html=... into the tag object
		if (preg_match('#^(article|html|content|gallery)=#si', $attributes->href, $match))
		{
			$tag->{$match['1']} = substr($attributes->href, strlen($match['0']));
			$attributes->href = '';
		}

		// Handle the different tag attributes
		switch (true)
		{
			case (!empty($tag->article)):
				$id = $tag->article;

				$db = JFactory::getDbo();
				$query = $db->getQuery(true)
					->select('a.id, a.catid')
					->from('#__content as a');
				$where = 'a.title = ' . $db->quote(nnText::html_entity_decoder($id));
				$where .= ' OR a.alias = ' . $db->quote(nnText::html_entity_decoder($id));
				if (is_numeric($id))
				{
					$where .= ' OR a.id = ' . (int) $id;
				}
				$query->where('(' . $where . ')');
				$db->setQuery($query);
				$article = $db->loadObject();

				require_once(JPATH_SITE . '/components/com_content/helpers/route.php');
				$attributes->href = ContentHelperRoute::getArticleRoute($article->id, $article->catid);

				// Replace current active menu id with the default menu id
				$language = JFactory::getLanguage()->getTag();
				$default_menu = JFactory::getApplication()->getMenu('site')->getDefault($language);
				$active_menu = JFactory::getApplication()->getMenu('site')->getActive();
				$attributes->href = preg_replace('#&Itemid=' . $active_menu->id . '$#', '&Itemid=' . $default_menu->id, $attributes->href);

				unset($tag->article);
				break;

			case (!empty($tag->html)):
				$id = uniqid('modal_') . rand(1000, 9999);
				$extra = '<div style="display:none;"><div id="' . $id . '">'
					. $tag->html
					. '</div></div>';
				$attributes->href = '#' . $id;
				unset($tag->html);
				break;

			case (!empty($tag->content)):
				$attributes->href = '#' . str_replace('#', '', $tag->content);
				unset($tag->content);
				break;

			case (!empty($tag->gallery)):
				$attributes->href = '#';
				break;
		}

		$attributes->id = !empty($tag->id) ? $tag->id : '';
		unset($tag->id);

		$attributes->class .= !empty($tag->class) ? ' ' . $tag->class : '';
		unset($tag->class);

		// move onSomething params to attributes, except the modal callbacks
		$callbacks = array('onopen', 'onload', 'oncomplete', 'oncleanup', 'onclosed');
		foreach ($tag as $key => $val)
		{
			if (
				substr($key, 0, 2) == 'on'
				&& !in_array(strtolower($key), $callbacks)
				&& is_string($val)
			)
			{
				$attributes->$key = $val;
				unset($tag->$key);
			}
		}

		$data = array();

		// set data by keys set in tag without values (and see them as true)
		foreach ($tag->params as $key)
		{
			$data[strtolower($key)] = 'true';
		}
		unset($tag->params);

		// set data defaults
		if ($attributes->href)
		{
			if ($attributes->href['0'] == '#')
			{
				$data['inline'] = 'true';
				$data['rel'] = isset($data['rel']) ? $data['rel'] : substr($attributes->href, 1);
			}
			elseif ($attributes->href == '-html-')
			{
				$attributes->href = '#';
			}
		}

		// set data by values set in tag
		foreach ($tag as $key => $val)
		{
			$data[strtolower($key)] = $val;
		}

		return array($this->buildLink($attributes, $data, $content), $extra);
	}

	private function fixUrl(&$url)
	{
		switch (true)
		{
			case(strpos($url, 'youtube=') !== false || strpos($url, 'youtu.be') !== false || strpos($url, 'youtube.com') !== false) :
				$this->fixUrlYoutube($url);

				return;
			case(strpos($url, 'vimeo=') !== false || strpos($url, 'vimeo.com') !== false) :
				$this->fixUrlVimeo($url);

				return;
		}
	}

	private function fixUrlYoutube(&$url)
	{
		if (!preg_match(
			'#(?:^youtube=|youtu\.be/?|youtube\.com/embed/?|youtube\.com\/watch\?v=)([^/&\?]+)(?:\?|&amp;|&)?(.*)$#i',
			trim($url),
			$parts
		)
		)
		{
			return;
		}

		$url = 'https://www.youtube.com/embed/' . $parts['1'] . '?' . $parts['2'];

		if (strpos($parts['2'], 'wmode=transparent') !== false)
		{
			return;
		}

		$url .= '&wmode=transparent';
	}

	private function fixUrlVimeo(&$url)
	{
		if (!preg_match(
			'#(?:^vimeo=|vimeo\.com/(?:video/)?)([0-9]+)(.*)$#i',
			trim($url),
			$parts
		)
		)
		{
			return;
		}

		$url = 'https://player.vimeo.com/video/'
			. $parts['1']
			. $parts['2'];
	}

	private function prepareLinkAttributeList($link)
	{
		$attributes = new stdClass;
		$attributes->href = '';
		$attributes->class = $this->params->class;
		$attributes->id = '';

		if (!$link)
		{
			return $attributes;
		}

		$link_attributes = $this->getLinkAttributeList(trim($link));

		foreach ($link_attributes as $key => $val)
		{
			$key = trim($key);
			$val = trim($val);

			if ($key == '' || $val == '')
			{
				continue;
			}

			if ($key == 'class')
			{
				$attributes->{$key} = trim($attributes->{$key} . ' ' . $val);
				continue;
			}

			$attributes->{$key} = $val;
		}

		return $attributes;
	}

	public function getLinkAttributeList($string)
	{
		$attributes = new stdClass;

		if (!$string)
		{
			return $attributes;
		}

		if (preg_match_all('#([a-z0-9_-]+)="([^\"]*)"#si', $string, $params, PREG_SET_ORDER) < 1)
		{
			return $attributes;
		}

		foreach ($params as $param)
		{
			$attributes->$param['1'] = $param['2'];
		}

		return $attributes;
	}

}
