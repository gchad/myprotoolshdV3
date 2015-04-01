<?php
/**
 * ------------------------------------------------------------------------
 * JA K2 Filter Plg for J25 & J3.2
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
 */

// no direct access
defined('_JEXEC') or die ('Restricted access');

/**
 * Example K2 Plugin to render YouTube URLs entered in backend K2 forms to video players in the frontend.
 */

// Load the K2 Plugin API
JLoader::register('K2Plugin', JPATH_ADMINISTRATOR.DS.'components'.DS.'com_k2'.DS.'lib'.DS.'k2plugin.php');

// Initiate class to hold plugin events
class plgK2Jak2filter extends K2Plugin {

	// Some params
	var $pluginName = 'jak2filter';
	var $pluginNameHumanReadable = 'K2 Plugin - JA K2 Filter';

	function plgK2Jak2filter( & $subject, $params) {
		parent::__construct($subject, $params);
	}

	public function onAfterK2Save(&$row, $isNew) {
		$params = JComponentHelper::getParams('com_jak2filter');
		
		$indexing = (int) $params->get('indexing_onsave', 1);
		if($indexing) {
			$this->indexingData($row, $isNew);
		}
	}
	
	/**
	 * Update indexing data
	 * - if context is cron then update indexing data for all items
	 * - otherwise, update indexing records that are related with article
	 *
	 * @param string $context
	 * @param TableK2Item $row
	 * @param boolean $isNew
	 */
	private function indexingData($row, $isNew) {
		/*if($isNew && $context == 'onBeforeK2Save') {
			return;
		}*/
		//
		$db = JFactory::getDbo();
        $jnow = JFactory::getDate();
        $now = K2_JVERSION == '15' ? $jnow->toMySQL() : $jnow->toSql();
        $nullDate = $db->getNullDate();

		$updateid = $row->id;
		$updateCatid = $row->catid;
		$updateAuthor = $row->created_by;
		$updateFields = json_decode($row->extra_fields);
		$updateTags = $this->getItemTags($row->id);
		$language = $row->language;

		//
		$aXFSupported = array('select', 'multipleSelect', 'radio', 'labels', 'textfield', 'date');
		$query = "SELECT `id`, `name`, `value` FROM #__k2_extra_fields WHERE `type` IN ('".implode("','", $aXFSupported). "')";
		$db->setQuery($query);
		$aXFSupported = $db->loadObjectList('id');

		//Labels
		$query = 'SELECT id FROM #__k2_extra_fields WHERE `type` = '.$db->quote('labels');
		$db->setQuery($query);
		$aLabels = $db->loadColumn();

		//Text field + Date
		$query = "SELECT `id` FROM #__k2_extra_fields WHERE `type` IN (".$db->quote('textfield').",".$db->quote('date').")";
		$db->setQuery($query);
		$aTextfields = $db->loadColumn();

		//remove current data in map table to re-index
		$query = "DELETE FROM #__jak2filter_taxonomy_map WHERE item_id = ".$db->quote($updateid);
		$db->setQuery($query);
		$db->execute();
		//category
		$query = "SELECT `id`, `name` FROM #__k2_categories WHERE `id`=".$db->quote($updateCatid);
		$db->setQuery($query);
		$cat = $db->loadObject();
		if($cat) {
			$this->addNodeMap('category', $updateCatid, 0, $updateid, $cat->name, $language);
		}
		//author
		$query = "SELECT `id`, `name` FROM #__users WHERE `id` = ".$db->quote($updateAuthor);
		$db->setQuery($query);
		$author = $db->loadObject();
		if($author) {
			$this->addNodeMap('author', $updateAuthor, 0, $updateid, $author->name, $language);
		}
		//extra fields
		if(is_array($updateFields)) {
			foreach ($updateFields as $f) {
				if(!isset($aXFSupported[$f->id])) continue;

				if(is_array($f->value)) {
					foreach ($f->value as $sf) {
						$label = $this->getOptionName($aXFSupported[$f->id], $sf);
						$this->addNodeMap('xfield', $f->id, $sf, $updateid, $label, $language);
					}
				} else {
					if(in_array($f->id, $aLabels) || in_array($f->id, $aTextfields)) {
						if(in_array($f->id, $aLabels)) {
							$labels = explode(',', $f->value);
						} else {
							$labels = array($f->value);
						}

						if(count($labels)) {
							foreach($labels as $label) {
								$label = trim($label);
								if(empty($label)) continue;
								$this->addNodeMap('xfield', $f->id, 0, $updateid, $label, $language);
							}
						}
					} else {
						$label = $this->getOptionName($aXFSupported[$f->id], $f->value);
						$this->addNodeMap('xfield', $f->id, $f->value, $updateid, $label, $language);
					}
				}
			}
		}
		//tags
		if(is_array($updateTags) && count($updateTags)) {
			foreach ($updateTags as $t) {
				$this->addNodeMap('tag', $t->id, 0, $updateid, $t->name, $language);
			}
		}

		//UPDATE COUNTER
		$query = "
				UPDATE #__jak2filter_taxonomy
				SET num_items = (
					SELECT COUNT(item_id) FROM #__jak2filter_taxonomy_map
					WHERE #__jak2filter_taxonomy_map.node_id = #__jak2filter_taxonomy.id
				)";
		$query;
		$db->setQuery($query);
		$db->execute();
	}

	public function getOptionName($field, $option_id) {
		$xfields = json_decode($field->value);
		if(count($xfields)) {
			foreach ($xfields as $xfield) {
				if($xfield->value == $option_id) {
					return $xfield->name;
				}
			}
		}
		return '';
	}
	
	private function addNodeMap($type, $asset_id, $option_id, $item_id, $label='', $language = '*') {
		$db = JFactory::getDbo();
		$byLabel = false;
		if($type == 'xfield' && !$option_id) {
			$byLabel = true;
		}
		$query = "SELECT id FROM #__jak2filter_taxonomy
			WHERE `type` = ".$db->quote($type)
			." AND asset_id = ".$db->quote($asset_id);
		if($byLabel) {
			$query .= " AND labels = ".$db->quote($label);
		} else {
			$query .= " AND option_id = ".$db->quote($option_id);
		}
		$db->setQuery($query);
		
		$nodeid = $db->loadResult();
		if(!$nodeid) {
			$title = $type;
			
			$query = $db->getQuery(true);
			$query->insert('#__jak2filter_taxonomy');
			$query->columns(array($db->quoteName('type'), $db->quoteName('title'), $db->quoteName('asset_id'), $db->quoteName('option_id'), $db->quoteName('labels'), $db->quoteName('num_items')));
			$query->values($db->quote($type) . ', ' . $db->quote($title). ', ' . $db->quote($asset_id). ', ' . $db->quote($option_id). ', ' . $db->quote($label). ', ' . $db->quote(0));			
			$db->setQuery($query);
			$db->execute();
			$nodeid = $db->insertid();
		} else {
			if(!$byLabel && !empty($label)) {
				//Update label to get correct result if order by extra field
				$query = $db->getQuery(true);
				$query->update('#__jak2filter_taxonomy')
					->set($db->quoteName('labels') .'='.$db->quote($label))
					->where($db->quoteName('id').'='.$db->quote($nodeid));
				$db->setQuery($query);
				$db->execute();
			}
		}
		
		if($nodeid) {
			$db = JFactory::getDbo();
			$query = "INSERT INTO #__jak2filter_taxonomy_map (`node_id`, `item_id`, `language`) VALUES( ".$db->quote($nodeid).", ".$db->quote($item_id).", ".$db->quote($language).")";
			$db->setQuery($query);
			$db->execute();
		}	
	}
	
	private function getItemTags($itemID)
	{
		$db = JFactory::getDBO();

		$query = "SELECT tag.id, tag.`name`
		FROM #__k2_tags AS tag
		JOIN #__k2_tags_xref AS xref ON tag.id = xref.tagID
		WHERE tag.published=1
		AND xref.itemID = ".(int)$itemID." ORDER BY xref.id ASC";

		$db->setQuery($query);
		$rows = $db->loadObjectList();
		
		return $rows;
	}
	
	public function onAfterRender()
	{
		$app = JFactory::getApplication();
		if ($app->isAdmin()) {
			return;
		}
		
		$params = JComponentHelper::getParams('com_jak2filter');
		$indexing = (int) $params->get('indexing_cron', 1);
		$interval = (int) $params->get('indexing_interval', 900);
		$interval = $interval * 60;
		$cronkey = $params->get('indexing_cron_key', 'indexing');
		
		if(!$indexing) return;
		
		$db = JFactory::getDbo();
		$query = "SELECT updatetime FROM #__jak2filter WHERE `name` = 'cron'";
		$db->setQuery($query);
		$updatetime = $db->loadResult();
		$updatetime = !$updatetime ? 0 : strtotime($updatetime);
		
		$key = JRequest::getVar('jakey');
		$run = (($updatetime + $interval < time()) || ($key == $cronkey));
		
		if($run) {
			$body = JResponse::getBody();
			$url = JURI::root().'index.php?option=com_jak2filter&view=cron';
			$script = '
<script type="text/javascript">
window.addEvent("domready", function() {
new Request({url: "'.$url.'", method: "get"}).send();
});
</script>
';
			$body = str_replace('</head>', $script.'</head>', $body);
			JResponse::setBody($body);
		}
	}
}