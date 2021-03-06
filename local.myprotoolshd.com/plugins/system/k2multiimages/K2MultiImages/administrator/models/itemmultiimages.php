<?php
/**
 * @version		$Id: item.php 1516 2012-03-06 17:01:36Z lefteris.kavadas $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2012 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

 
/****
 * GCHAD
 * USED WHEN SAVING ITEMS
 * 
 * 
 */ 
 
 
// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

JTable::addIncludePath(JPATH_COMPONENT.DS.'tables');

//added
class K2ModelItemMultiImages extends K2Model {
//added

	function getData() {

		$cid = JRequest::getVar('cid');
		$row = &JTable::getInstance('K2Item', 'Table');
		$row->load($cid);
		return $row;
	}

	function save($front = false) {

		$mainframe = &JFactory::getApplication();
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.archive');
		jimport('joomla.database.table');
		$language =& JFactory::getLanguage();
		$language->load("com_k2", JPATH_ADMINISTRATOR);
		require_once (JPATH_COMPONENT_ADMINISTRATOR.DS.'lib'.DS.'class.upload.php');
		$db = &JFactory::getDBO();
		$user = &JFactory::getUser();
		$row = &JTable::getInstance('K2Item', 'Table');
		$params = &JComponentHelper::getParams('com_k2');
		$nullDate = $db->getNullDate();

		if (!$row->bind(JRequest::get('post'))) {
			$mainframe->redirect('index.php?option=com_k2&view=items', $row->getError(), 'error');
		}

		if ($front && $row->id == NULL) {
			//added
			JLoader::register('K2HelperPermissions', JPATH_COMPONENT.DS.'helpers'.DS.'permissions.php');
			JLoader::register('K2HelperUtilities', JPATH_COMPONENT.DS.'helpers'.DS.'utilities.php');

			K2HelperPermissions::setPermissions();
			K2HelperPermissions::checkPermissions();
			//added
			if (!K2HelperPermissions::canAddItem($row->catid)) {
				$mainframe->redirect('index.php?option=com_k2&view=item&task=add&tmpl=component', JText::_('K2_YOU_ARE_NOT_ALLOWED_TO_POST_TO_THIS_CATEGORY_SAVE_FAILED'), 'error');
			}
		}

		($row->id) ? $isNew = false : $isNew = true;


		if ($params->get('mergeEditors')) {
			$text = JRequest::getVar('text', '', 'post', 'string', 2);
			if($params->get('xssFiltering')){
				$filter = new JFilterInput(array(), array(), 1, 1, 0);
				$text = $filter->clean( $text );
			}
			$pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
			$tagPos = preg_match($pattern, $text);
			if ($tagPos == 0) {
				$row->introtext = $text;
				$row->fulltext = '';
			} else
			list($row->introtext, $row->fulltext) = preg_split($pattern, $text, 2);
		} else {
			$row->introtext = JRequest::getVar('introtext', '', 'post', 'string', 2);
			$row->fulltext = JRequest::getVar('fulltext', '', 'post', 'string', 2);
			if($params->get('xssFiltering')){
				$filter = new JFilterInput(array(), array(), 1, 1, 0);
				$row->introtext = $filter->clean( $row->introtext );
				$row->fulltext = $filter->clean( $row->fulltext );
			}
		}

		if ($row->id) {
			$datenow = &JFactory::getDate();
			$row->modified = $datenow->toSQL();
			$row->modified_by = $user->get('id');
		} else {
			$row->ordering = $row->getNextOrder("catid = {$row->catid} AND trash = 0");
			if ($row->featured)
			$row->featured_ordering = $row->getNextOrder("featured = 1 AND trash = 0", 'featured_ordering');
		}
		
		$row->created_by = $row->created_by ? $row->created_by : $user->get('id');

		if ($front) {	
			//added
			JLoader::register('K2HelperPermissions', JPATH_COMPONENT.DS.'helpers'.DS.'permissions.php');
			JLoader::register('K2HelperUtilities', JPATH_COMPONENT.DS.'helpers'.DS.'utilities.php');

			K2HelperPermissions::setPermissions();
			K2HelperPermissions::checkPermissions();
			//added
			$K2Permissions = &K2Permissions::getInstance();
	        if (!$K2Permissions->permissions->get('editAll')) {
	    		$row->created_by = $user->get('id');
	    	}
		} 
		
		if ($row->created && strlen(trim($row->created)) <= 10) {
			$row->created .= ' 00:00:00';
		}

		$config = JFactory::getConfig();
		$tzoffset = K2_JVERSION == '30' ? $config->get('offset') : $config->getValue('config.offset');
		$date = JFactory::getDate($row->created, $tzoffset);
		$row->created = K2_JVERSION == '15' ? $date->toMySQL() : $date->toSql();

		if (strlen(trim($row->publish_up)) <= 10)
		{
			$row->publish_up .= ' 00:00:00';
		}

		$date = JFactory::getDate($row->publish_up, $tzoffset);
		$row->publish_up = K2_JVERSION == '15' ? $date->toMySQL() : $date->toSql();

		if (trim($row->publish_down) == JText::_('K2_NEVER') || trim($row->publish_down) == '')
		{
			$row->publish_down = $nullDate;
		}
		else
		{
			if (strlen(trim($row->publish_down)) <= 10)
			{
				$row->publish_down .= ' 00:00:00';
			}
			$date = JFactory::getDate($row->publish_down, $tzoffset);
			$row->publish_down = K2_JVERSION == '15' ? $date->toMySQL() : $date->toSql();
		}

		$metadata = JRequest::getVar('meta', null, 'post', 'array');
		if (is_array($metadata)) {
			$txt = array();
			foreach ($metadata as $k=>$v) {
				if ($k == 'description') {
					$row->metadesc = $v;
				} elseif ($k == 'keywords') {
					$row->metakey = $v;
				} else {
					$txt[] = "$k=$v";
				}
			}
			$row->metadata = implode("\n", $txt);
		}

		if (!$row->check()) {
			//added
			if($row->id) {
				$mainframe->redirect('index.php?option=com_k2&view=item&cid='.$row->id, $row->getError(), 'error');
			}
			else {
				$mainframe->redirect('index.php?option=com_k2&view=item&task=add&template=component'.$row->id, $row->getError(), 'error');
			}
			//added
		}

		$dispatcher = &JDispatcher::getInstance();
		JPluginHelper::importPlugin('k2');
		$result = $dispatcher->trigger('onBeforeK2Save', array(&$row, $isNew));
		if (in_array(false, $result, true)) {
			JError::raiseError(500, $row->getError());
			return false;
		}
		
		// Try to save the video if there is no need to wait for item ID
		if (!JRequest::getBool('del_video')) {
			if (!isset($files['video']) || $files['video']['error'] == 0) {
				
				if (JRequest::getVar('remoteVideo')) {
					$fileurl = JRequest::getVar('remoteVideo');
					$filetype = JFile::getExt($fileurl);
					$row->video = '{'.$filetype.'remote}'.$fileurl.'{/'.$filetype.'remote}';
				}

				if (JRequest::getVar('videoID')) {
					$provider = JRequest::getWord('videoProvider');
					$videoID = JRequest::getVar('videoID');
					$row->video = '{'.$provider.'}'.$videoID.'{/'.$provider.'}';
				}

				if (JRequest::getVar('embedVideo', '', 'post', 'string', JREQUEST_ALLOWRAW)) {
					$row->video = JRequest::getVar('embedVideo', '', 'post', 'string', JREQUEST_ALLOWRAW);
				}
			}
		}

		// JoomFish! Front-end editing compatibility
		if($mainframe->isSite() && JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomfish'.DS.'joomfish.php')) {
			if (version_compare(phpversion(), '5.0') < 0) {
				$tmpRow = $row;
			}
			else {
				$tmpRow = clone($row);
			}
		}
		
		/* <--- added K2MultiImages ---> */
		$files = JRequest::get('files');
	
		//multiple files add////////////
		$files_multi_start = JRequest::getInt("image_multiple_start");
        
        /**
         * GCHAD FIX
         * force it to 0 otherwhise it will bug in the admin*/
        $files_multi_start = 0;
		
		foreach($files["image_multiple"]["name"] as $k=>$file) {
		   
			$files["image".$files_multi_start]["name"] = $file;
			$files["image".$files_multi_start]["type"] = $files["image_multiple"]["type"][$k];
			$files["image".$files_multi_start]["tmp_name"] = $files["image_multiple"]["tmp_name"][$k];
			$files["image".$files_multi_start]["error"] = $files["image_multiple"]["error"][$k];
			$files["image".$files_multi_start]["size"] = $files["image_multiple"]["size"][$k];
			
			$files_multi_start++;
		}
		////////////////////////////////
		
		$files[] = '';
		
		$caption = Array();
		$credits = Array();
		
		//caption and credits
		for($i=1; $i<count($files); $i++) {				
				$caption[$i] = JRequest::getVar("image".$i."_caption");
				$credits[$i] = JRequest::getVar("image".$i."_credits");					
		}
		
		$row->image_caption = implode("|", $caption);
		$row->image_credits = implode("|", $credits);

		/* <--- //added K2MultiImages ---> */

		if (!$row->store()) {
			$mainframe->redirect('index.php?option=com_k2&view=items', $row->getError(), 'error');
		}
		
		// JoomFish! Front-end editing compatibility
		if($mainframe->isSite() && JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomfish'.DS.'joomfish.php')) {
			$itemID = $row->id;
			$row = $tmpRow;
			$row->id = $itemID;
		}

		if(!$params->get('disableCompactOrdering')) {
			$row->reorder("catid = {$row->catid} AND trash = 0");
		}
		if ($row->featured && !$params->get('disableCompactOrdering')) {
			$row->reorder("featured = 1 AND trash = 0", 'featured_ordering');
		}

		//Image
		if((int)$params->get('imageMemoryLimit')) {
			ini_set('memory_limit', (int)$params->get('imageMemoryLimit').'M');
		}

		/* <--- added K2MultiImages ---> */
		
		//Upload Images
		
		ini_set("memory_limit", "400M");
		ini_set("upload_max_filesize", "300M");
		ini_set("post_max_size", "300M");
		ini_set("max_execution_time", "300"); 
		
		$multiimages_plugin = JPluginHelper::getPlugin('system', 'k2multiimages');
		$plgParams = class_exists('JParameter') ? new JParameter($multiimages_plugin->params) : new JRegistry($multiimages_plugin->params);
			
		$watermark = $plgParams->get("watermark", 0);
		$watermark_image = $plgParams->get("watermark_image", "");

		if($watermark == "1" && $watermark_image != '' && $watermark_image != '-1') {
			$watermark_image = JPATH_SITE.DS.'images'.DS.$watermark_image;
			$watermark_on = 1;
		}
		
		for($i=1; $i<count($files); $i++) {
		
			$existingImage = JRequest::getVar('existingImage'.$i);
			
			if ( ($files['image'.$i]['error'] === 0 || $existingImage) && !JRequest::getBool('del_image'.$i)) {

				if($files['image'.$i]['error'] === 0){
					$image = $files['image'.$i];
				}
				else{
					$image = JPATH_SITE.DS.JPath::clean($existingImage);
				}


				$handle = new Upload($image);
				$handle->allowed = array('image/*');
                
                /* GCHAD FIX
                 * this is where the images are treated 
                 */
                 
                 /* GCHAD IMAGE GENERATOR */
                  
				if ($handle->uploaded) {

					//Image params
					$category = &JTable::getInstance('K2Category', 'Table');
					$category->load($row->catid);
					$cparams = class_exists('JParameter') ? new JParameter($category->params) : new JRegistry($category->params);

					if ($cparams->get('inheritFrom')) {
						$masterCategoryID = $cparams->get('inheritFrom');
						$query = "SELECT * FROM #__k2_categories WHERE id=".(int)$masterCategoryID;
						$db->setQuery($query, 0, 1);
						$masterCategory = $db->loadObject();
						$cparams = class_exists('JParameter') ? new JParameter($masterCategory->params) : new JRegistry($masterCategory->params);
					}

					$params->merge($cparams);

					if($i > 1) {
						$filename = md5("Image".$row->id)."_".$i;
					}
					else {
						$filename = md5("Image".$row->id);
					}

					//Original image
					$savepath = JPATH_SITE.DS.'media'.DS.'k2'.DS.'items'.DS.'src';
					$handle->image_convert = 'jpg';
					$handle->jpeg_quality = 100;
					$handle->file_auto_rename = false;
					$handle->file_overwrite = true;
					$handle->file_new_name_body = $filename;
					$handle->Process($savepath);
				
					//Resized images
					$savepath = JPATH_SITE.DS.'media'.DS.'k2'.DS.'items'.DS.'cache';
                    

					//XLarge image
					$handle->image_resize = true;
					$handle->image_ratio_y = true;
                    $handle->image_ratio_crop = false;
                    $handle->image_x = 900;
                    
					if(@$watermark_on) {
						$handle->image_watermark = $watermark_image; 
						$handle->image_watermark_position = "BR";
					}
                    
					$handle->image_convert = 'jpg';
					$handle->jpeg_quality = $params->get('imagesQuality');
					$handle->file_auto_rename = false;
					$handle->file_overwrite = true;
					$handle->file_new_name_body = $filename.'_XL';
                    
					if (JRequest::getInt('itemImageXL')) {
						$imageWidth = JRequest::getInt('itemImageXL');
					} else {
						$imageWidth = $params->get('itemImageXL', '800');
					}
                    
					//$handle->image_x = $imageWidth;
					
					if ($imageWidth > 0) {
					    
                        
						$handle->Process($savepath);
					}
                    
              						
					//Large image
					$handle->image_resize = true;
					$handle->image_ratio_y = true;
                    $handle->image_ratio_crop = false;
                    
					if(@$watermark_on) {
						$handle->image_watermark = $watermark_image; 
						$handle->image_watermark_position = "BR";
					}
					$handle->image_convert = 'jpg';
					$handle->jpeg_quality = $params->get('imagesQuality');
					$handle->file_auto_rename = false;
					$handle->file_overwrite = true;
					$handle->file_new_name_body = $filename.'_L';
                    
					if (JRequest::getInt('itemImageL')) {
						$imageWidth = JRequest::getInt('itemImageL');
					} else {
						$imageWidth = $params->get('itemImageL', '600');
					}
					$handle->image_x = $imageWidth;
					
					if ($imageWidth > 0) {
						$handle->Process($savepath);
					}

					//Medium image
					$handle->image_resize = true;
					$handle->image_ratio_y = false;
                    $handle->image_ratio_crop = 1.5;
                    $handle->image_x = 400;
                    $handle->image_y = 267;
                    
					if(@$watermark_on) {
						$handle->image_watermark = $watermark_image; 
						$handle->image_watermark_position = "BR";
					}
					$handle->image_convert = 'jpg';
					$handle->jpeg_quality = $params->get('imagesQuality');
					$handle->file_auto_rename = false;
					$handle->file_overwrite = true;
					$handle->file_new_name_body = $filename.'_M';
					if (JRequest::getInt('itemImageM')) {
						$imageWidth = JRequest::getInt('itemImageM');
					} else {
						$imageWidth = $params->get('itemImageM', '400');
					}
					$handle->image_x = $imageWidth;
					
					if ($imageWidth > 0) {
						$handle->Process($savepath);
					}


					//Small image
					$handle->image_resize = true;
                    $handle->image_ratio_y = true;
                    $handle->image_ratio_crop = false;
                    
                    $handle->image_convert = 'jpg';
					$handle->jpeg_quality = $params->get('imagesQuality');
					$handle->file_auto_rename = false;
					$handle->file_overwrite = true;
					$handle->file_new_name_body = $filename.'_S';
                    
                    
					if (JRequest::getInt('itemImageS')) {
						$imageWidth = JRequest::getInt('itemImageS');
					} else {
						$imageWidth = $params->get('itemImageS', '200');
					}
					$handle->image_x = $imageWidth;
					
					if ($imageWidth > 0) {
						$handle->Process($savepath);
					}

					//XSmall image
					$handle->image_resize = true;
                    $handle->image_ratio_y = false;                
                    $handle->image_ratio_crop = 1;
                    $handle->image_x = 100;
                    $handle->image_y = 100;
                    
					$handle->image_convert = 'jpg';
					$handle->jpeg_quality = $params->get('imagesQuality');
					$handle->file_auto_rename = false;
					$handle->file_overwrite = true;
					$handle->file_new_name_body = $filename.'_XS';
                    
                    
					if (JRequest::getInt('itemImageXS')) {
						$imageWidth = JRequest::getInt('itemImageXS');
					} else {
						$imageWidth = $params->get('itemImageXS', '100');
					}
					$handle->image_x = $imageWidth;
					
					if($plgParams->get("fixSmall", 0)) {
						$handle->image_y = $imageWidth;
						$handle->image_ratio_crop = true;
                        $handle->image_ratio_y = false;
					}
					
					if ($imageWidth > 0) {
					    
						$handle->Process($savepath);
					}

					//Generic image
					$handle->image_resize = true;
					$handle->image_ratio_y = true;
                    $handle->image_ratio_crop = false;
                    
					$handle->image_convert = 'jpg';
					$handle->jpeg_quality = $params->get('imagesQuality');
					$handle->file_auto_rename = false;
					$handle->file_overwrite = true;
					$handle->file_new_name_body = $filename.'_Generic';
					$imageWidth = $params->get('itemImageGeneric', '300');
					$handle->image_x = $imageWidth;
					
					if ($imageWidth > 0) {
						$handle->Process($savepath);
					}

					if($files['image'.$i]['error'] === 0)
					$handle->Clean();

				} else {
					$mainframe->redirect('index.php?option=com_k2&view=items', $handle->error, 'error');
				}
			}
			
		} // image create
				
		//Delete Images
		
		for($i=1; $i<count($files); $i++) {
		
			$current = &JTable::getInstance('K2Item', 'Table');
			$current->load($row->id);
			if($i > 1) {
				$filename = md5("Image".$current->id)."_".$i;
			}
			else {
				$filename = md5("Image".$current->id);
			}
			
			if (JRequest::getBool('del_image'.$i)) {
				
				if (JFile::exists(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'src'.DS.$filename.'.jpg')) {
					JFile::delete(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'src'.DS.$filename.'.jpg');
				}

				if (JFile::exists(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$filename.'_XS.jpg')) {
					JFile::delete(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$filename.'_XS.jpg');
				}

				if (JFile::exists(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$filename.'_S.jpg')) {
					JFile::delete(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$filename.'_S.jpg');
				}

				if (JFile::exists(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$filename.'_M.jpg')) {
					JFile::delete(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$filename.'_M.jpg');
				}

				if (JFile::exists(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$filename.'_L.jpg')) {
					JFile::delete(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$filename.'_L.jpg');
				}

				if (JFile::exists(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$filename.'_XL.jpg')) {
					JFile::delete(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$filename.'_XL.jpg');
				}

				if (JFile::exists(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$filename.'_Generic.jpg')) {
					JFile::delete(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$filename.'_Generic.jpg');
				}

			}
			
		} // image delete
		
		//Replace captions and credits after delete
		for($i=count($files); $i>0; $i--) {
		
			if (JRequest::getBool('del_image'.$i)) {		

				$current = &JTable::getInstance('K2Item', 'Table');
				$current->load($row->id);
				
				$captions = explode("|", $current->image_caption);
				$credits = explode("|", $current->image_credits);
				
				$arrcount = $i - 1;
				$slice_capt =  count($captions);
				$slice_cred =  count($credits) - $arrcount;
				
				unset($captions[$arrcount]);
				$captions = array_slice($captions, 0);
				
				unset($credits[$arrcount]);
				$credits = array_slice($credits, 0);
				
				$current->image_caption = implode("|", $captions);
				$current->image_credits = implode("|", $credits);
				
				if (!$current->store()) {
					$mainframe->redirect('index.php?option=com_k2&view=items', $row->getError(), 'error');
				}
				
			}
			
		}
		
		//Replace Images after delete
		for($i=count($files); $i>0; $i--) {
		
			if (JRequest::getBool('del_image'.$i)) {
		
					if($i < count($files)) {
					
						$start = $i+1;
					
						for($j = $start; $j < count($files); $j++) {
							$filenameCurr = md5("Image".$current->id)."_".$j;
							if(($j-1) > 1) {
								$filenameNew = md5("Image".$current->id)."_".($j-1);
							}
							else {
								$filenameNew = md5("Image".$current->id);
							}
							
							if (JFile::exists(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'src'.DS.$filenameCurr.'.jpg')) {
								$src = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'src'.DS.$filenameCurr.'.jpg';
								$dst = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'src'.DS.$filenameNew.'.jpg';
								JFile::move($src, $dst);
							}
							
							if (JFile::exists(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$filenameCurr.'_XS.jpg')) {
								$src = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$filenameCurr.'_XS.jpg';
								$dst = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$filenameNew.'_XS.jpg';
								JFile::move($src, $dst);
							}

							if (JFile::exists(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$filenameCurr.'_S.jpg')) {
								$src = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$filenameCurr.'_S.jpg';
								$dst = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$filenameNew.'_S.jpg';
								JFile::move($src, $dst);
							}

							if (JFile::exists(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$filenameCurr.'_M.jpg')) {
								$src = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$filenameCurr.'_M.jpg';
								$dst = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$filenameNew.'_M.jpg';
								JFile::move($src, $dst);
							}

							if (JFile::exists(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$filenameCurr.'_L.jpg')) {
								$src = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$filenameCurr.'_L.jpg';
								$dst = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$filenameNew.'_L.jpg';
								JFile::move($src, $dst);
							}

							if (JFile::exists(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$filenameCurr.'_XL.jpg')) {
								$src = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$filenameCurr.'_XL.jpg';
								$dst = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$filenameNew.'_XL.jpg';
								JFile::move($src, $dst);
							}

							if (JFile::exists(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$filenameCurr.'_Generic.jpg')) {
								$src = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$filenameCurr.'_Generic.jpg';
								$dst = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$filenameNew.'_Generic.jpg';
								JFile::move($src, $dst);
							}

						}
					}
			}
					
		} // image replace
		
		// Ordering images
		$imagesOrdering = JRequest::getVar("imagesOrdering", "");
		
		if($imagesOrdering != "") {
			$imagesOrdering = explode("|", $imagesOrdering);
			$imagesOrdering = array_diff($imagesOrdering, array(''));
			
			$moved = Array();
			foreach($imagesOrdering as $image=>$moveTo) {
				if(in_array($moveTo, $moved)) continue;
				
				$image = $image + 1;
				if($image == $moveTo) continue;
				
				$moved[] = (int)$image;
				$moved[] = (int)$moveTo;
				$moved = array_unique($moved);

				if($image == "1") {
					$moveFrom = md5("Image".$row->id);
				}
				else {
					$moveFrom = md5("Image".$row->id)."_".$image;
				}

				if($moveTo == "1") {
					$moveTo = md5("Image".$row->id);
				}
				else {
					$moveTo = md5("Image".$row->id)."_".$moveTo;
				}
				
				$tmp = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.'temp.jpg';
				
				// XSmall
				$src = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$moveFrom.'_XS.jpg';
				$dst = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$moveTo.'_XS.jpg';
				if(!JFile::copy($src, $tmp)) {
					echo "Error copy xsmall image from src to tmp <br /><br />";
				}
				if(!JFile::copy($dst, $src)) {
					echo "Error copy xsmall image from dst to src <br /><br />";
				}
				if(!JFile::copy($tmp, $dst)) {
					echo "Error copy xsmall image from tmp to dst <br /><br />";
				}	

				// Small
				$src = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$moveFrom.'_S.jpg';
				$dst = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$moveTo.'_S.jpg';
				if(!JFile::copy($src, $tmp)) {
					echo "Error copy small image from src to tmp <br /><br />";
				}
				if(!JFile::copy($dst, $src)) {
					echo "Error copy small image from dst to src <br /><br />";
				}
				if(!JFile::copy($tmp, $dst)) {
					echo "Error copy small image from tmp to dst <br /><br />";
				}
				
				// Medium
				$src = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$moveFrom.'_M.jpg';
				$dst = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$moveTo.'_M.jpg';
				if(!JFile::copy($src, $tmp)) {
					echo "Error copy medium image from src to tmp <br /><br />";
				}
				if(!JFile::copy($dst, $src)) {
					echo "Error copy medium image from dst to src <br /><br />";
				}
				if(!JFile::copy($tmp, $dst)) {
					echo "Error copy medium image from tmp to dst <br /><br />";
				}
				
				// Large
				$src = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$moveFrom.'_L.jpg';
				$dst = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$moveTo.'_L.jpg';
				if(!JFile::copy($src, $tmp)) {
					echo "Error copy large image from src to tmp <br /><br />";
				}
				if(!JFile::copy($dst, $src)) {
					echo "Error copy large image from dst to src <br /><br />";
				}
				if(!JFile::copy($tmp, $dst)) {
					echo "Error copy large image from tmp to dst <br /><br />";
				}
				
				// XLarge
				$src = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$moveFrom.'_XL.jpg';
				$dst = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$moveTo.'_XL.jpg';
				if(!JFile::copy($src, $tmp)) {
					echo "Error copy xlarge image from src to tmp <br /><br />";
				}
				if(!JFile::copy($dst, $src)) {
					echo "Error copy xlarge image from dst to src <br /><br />";
				}
				if(!JFile::copy($tmp, $dst)) {
					echo "Error copy xlarge image from tmp to dst <br /><br />";
				}
				
				// Generic
				$src = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$moveFrom.'_Generic.jpg';
				$dst = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.$moveTo.'_Generic.jpg';
				if(!JFile::copy($src, $tmp)) {
					echo "Error copy generic image from src to tmp <br /><br />";
				}
				if(!JFile::copy($dst, $src)) {
					echo "Error copy generic image from dst to src <br /><br />";
				}
				if(!JFile::copy($tmp, $dst)) {
					echo "Error copy generic image from tmp to dst <br /><br />";
				}
			
				JFile::delete(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.'temp.jpg');
			}
		}
		
		/* <--- /added K2MultiImages ---> */

		//Attachments
		$attachments = JRequest::getVar('attachment_file', NULL, 'FILES', 'array');
		$attachments_names = JRequest::getVar('attachment_name', '', 'POST', 'array');
		$attachments_titles = JRequest::getVar('attachment_title', '', 'POST', 'array');
		$attachments_title_attributes = JRequest::getVar('attachment_title_attribute', '', 'POST', 'array');
		$attachments_existing_files = JRequest::getVar('attachment_existing_file', '', 'POST', 'array');

		$attachmentFiles = array();

		if (count($attachments)) {

			foreach ($attachments as $k=>$l) {
				foreach ($l as $i=>$v) {
					if (!array_key_exists($i, $attachmentFiles))
					$attachmentFiles[$i] = array();
					$attachmentFiles[$i][$k] = $v;
				}

			}

			$path = $params->get('attachmentsFolder', NULL);
			if (is_null($path)) {
				$savepath = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'attachments';
			} else {
				$savepath = $path;
			}

			$counter = 0;

			foreach ($attachmentFiles as $key=>$file) {
				 
				if($file["tmp_name"] || $attachments_existing_files[$key]){
					 
					if($attachments_existing_files[$key]){
						$file = JPATH_SITE.DS.JPath::clean($attachments_existing_files[$key]);
					}

					$handle = new Upload($file);

					if ($handle->uploaded) {
						$handle->file_auto_rename = true;
						$handle->allowed[] = 'application/x-zip';
						$handle->allowed[] = 'application/download';
						$handle->Process($savepath);
						$filename = $handle->file_dst_name;
						$handle->Clean();
						$attachment = &JTable::getInstance('K2Attachment', 'Table');
						$attachment->itemID = $row->id;
						$attachment->filename = $filename;
						$attachment->title = ( empty($attachments_titles[$counter])) ? $filename : $attachments_titles[$counter];
						$attachment->titleAttribute = ( empty($attachments_title_attributes[$counter])) ? $filename : $attachments_title_attributes[$counter];
						$attachment->store();
					} else {
						$mainframe->redirect('index.php?option=com_k2&view=items', $handle->error, 'error');
					}
				}


				$counter++;
			}

		}

		//Gallery
		$flickrGallery = JRequest::getVar('flickrGallery');
		if($flickrGallery) {
			$row->gallery = '{gallery}'.$flickrGallery.'{/gallery}';
		}

		if (isset($files['gallery']) && $files['gallery']['error'] == 0 && !JRequest::getBool('del_gallery')) {
			$handle = new Upload($files['gallery']);
			$handle->file_auto_rename = true;
			$savepath = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'galleries';
			$handle->allowed = array("application/download", "application/rar", "application/x-rar-compressed", "application/arj", "application/gnutar", "application/x-bzip", "application/x-bzip2", "application/x-compressed", "application/x-gzip", "application/x-zip-compressed", "application/zip", "multipart/x-zip", "multipart/x-gzip", "application/x-unknown", "application/x-zip");

			if ($handle->uploaded) {

				$handle->Process($savepath);
				$handle->Clean();

				if (JFolder::exists($savepath.DS.$row->id)) {
					JFolder::delete($savepath.DS.$row->id);
				}

				if (!JArchive::extract($savepath.DS.$handle->file_dst_name, $savepath.DS.$row->id)) {
					$mainframe->redirect('index.php?option=com_k2&view=items', JText::_('K2_GALLERY_UPLOAD_ERROR_CANNOT_EXTRACT_ARCHIVE'), 'error');
				} else {
					$row->gallery = '{gallery}'.$row->id.'{/gallery}';
				}
				JFile::delete($savepath.DS.$handle->file_dst_name);
				$handle->Clean();

			} else {
				$mainframe->redirect('index.php?option=com_k2&view=items', $handle->error, 'error');
			}
		}


		if (JRequest::getBool('del_gallery')) {

			$current = &JTable::getInstance('K2Item', 'Table');
			$current->load($row->id);

			if (JFolder::exists(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'galleries'.DS.$current->id)) {
				JFolder::delete(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'galleries'.DS.$current->id);
			}
			$row->gallery = '';
		}

		//Video
		if (!JRequest::getBool('del_video')) {
			if (isset($files['video']) && $files['video']['error'] == 0) {

				$videoExtensions = array("flv", "mp4", "ogv", "webm", "f4v", "m4v", "3gp", "3g2", "mov", "mpeg", "mpg", "avi", "wmv", "divx");
				$audioExtensions = array("mp3", "aac", "m4a", "ogg", "wma");
				$validExtensions = array_merge($videoExtensions, $audioExtensions);
				$filetype = JFile::getExt($files['video']['name']);

				if (!in_array($filetype, $validExtensions)) {
					$mainframe->redirect('index.php?option=com_k2&view=items', JText::_('K2_INVALID_VIDEO_FILE'), 'error');
				}

				if (in_array($filetype, $videoExtensions)) {
					$savepath = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'videos';
				}
				else {
					$savepath = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'audio';
				}
				
				$filename = JFile::stripExt($files['video']['name']);

				JFile::upload($files['video']['tmp_name'], $savepath.DS.$row->id.'.'.$filetype);
				$filetype = JFile::getExt($files['video']['name']);
				$row->video = '{'.$filetype.'}'.$row->id.'{/'.$filetype.'}';

			} 

		} else {

			$current = &JTable::getInstance('K2Item', 'Table');
			$current->load($row->id);

			preg_match_all("#^{(.*?)}(.*?){#", $current->video, $matches, PREG_PATTERN_ORDER);
			$videotype = $matches[1][0];
			$videofile = $matches[2][0];

			if (in_array($videotype, $videoExtensions)) {
				if (JFile::exists(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'videos'.DS.$videofile.'.'.$videotype))
				JFile::delete(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'videos'.DS.$videofile.'.'.$videotype);
			}
			
			if (in_array($videotype, $audioExtensions)) {
				if (JFile::exists(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'audio'.DS.$videofile.'.'.$videotype))
				JFile::delete(JPATH_ROOT.DS.'media'.DS.'k2'.DS.'audio'.DS.$videofile.'.'.$videotype);
			}

			$row->video = '';
			$row->video_caption = '';
			$row->video_credits = '';
		}


		//Extra fields
		$objects = array();
		$variables = JRequest::get('post', 4);
		foreach ($variables as $key=>$value) {
			if (( bool )JString::stristr($key, 'K2ExtraField_')) {
				$object = new JObject;
				$object->set('id', JString::substr($key, 13));
                
                /* GCHAD FIX if credits too long */
                if(JString::substr($key, 13) == 6){
                    $value = truncate($value);    
                }
                
				$object->set('value', $value);
				unset($object->_errors);
				$objects[] = $object;
			}
		}

		$csvFiles = JRequest::get('files');
		foreach ($csvFiles as $key=>$file) {
			if (( bool )JString::stristr($key, 'K2ExtraField_')) {
				$object = new JObject;
				$object->set('id', JString::substr($key, 13));
				$csvFile = $file['tmp_name'][0];
				if(!empty($csvFile) && JFile::getExt($file['name'][0])=='csv'){
					$handle = @fopen($csvFile, 'r');
					$csvData=array();
					while (($data = fgetcsv($handle, 1000)) !== FALSE) {
						$csvData[]=$data;
					}
					fclose($handle);
					$object->set('value', $csvData);
				}
				else {
					require_once (JPATH_COMPONENT_ADMINISTRATOR.DS.'lib'.DS.'JSON.php');
					$json = new Services_JSON;
					$object->set('value', $json->decode(JRequest::getVar('K2CSV_'.$object->id)));
					if(JRequest::getBool('K2ResetCSV_'.$object->id))
					$object->set('value', null);
				}
				unset($object->_errors);
				$objects[] = $object;
			}
		}


		require_once (JPATH_COMPONENT_ADMINISTRATOR.DS.'lib'.DS.'JSON.php');
		$json = new Services_JSON;
		$row->extra_fields = $json->encode($objects);

		require_once (JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'extrafield.php');
		$extraFieldModel = new K2ModelExtraField;
		$row->extra_fields_search = '';

		foreach ($objects as $object) {
			$row->extra_fields_search .= $extraFieldModel->getSearchValue($object->id, $object->value);
			$row->extra_fields_search .= ' ';
		}
		
		//Tags
		if($user->gid<24 && $params->get('lockTags'))
		$params->set('taggingSystem',0);
		$db = &JFactory::getDBO();
		$query = "DELETE FROM #__k2_tags_xref WHERE itemID={intval($row->id)}";
		$db->setQuery($query);
		$db->query();

		if($params->get('taggingSystem')){

			if($user->gid<24 && $params->get('lockTags'))
			JError::raiseError(403, JText::_('K2_ALERTNOTAUTH'));

			$tags = JRequest::getVar('tags', NULL, 'POST', 'array');
			if (count($tags)) {
				$tags = array_unique($tags);
				foreach ($tags as $tag) {
					$tag = str_replace('-','',$tag);
					$query = "SELECT id FROM #__k2_tags WHERE name=".$db->Quote($tag);
					$db->setQuery($query);
					$tagID = $db->loadResult();
					if($tagID){
						$query = "INSERT INTO #__k2_tags_xref (`id`, `tagID`, `itemID`) VALUES (NULL, {intval($tagID)}, {intval($row->id)})";
						$db->setQuery($query);
						$db->query();
					}
					else {
						$K2Tag = &JTable::getInstance('K2Tag', 'Table');
						$K2Tag->name = $tag;
						$K2Tag->published = 1;
						$K2Tag->check();
						$K2Tag->store();
						$query = "INSERT INTO #__k2_tags_xref (`id`, `tagID`, `itemID`) VALUES (NULL, {intval($K2Tag->id)}, {intval($row->id)})";
						$db->setQuery($query);
						$db->query();
					}
				}
			}

		}
		else {
			$tags = JRequest::getVar('selectedTags', NULL, 'POST', 'array');
			if (count($tags)) {
				foreach ($tags as $tagID) {
					$query = "INSERT INTO #__k2_tags_xref (`id`, `tagID`, `itemID`) VALUES (NULL, {intval($tagID)}, {intval($row->id)})";
					$db->setQuery($query);
					$db->query();
				}
			}

		}

		if ($front) {
			JLoader::register('K2HelperPermissions', JPATH_COMPONENT.DS.'helpers'.DS.'permissions.php');
			JLoader::register('K2HelperUtilities', JPATH_COMPONENT.DS.'helpers'.DS.'utilities.php');

			K2HelperPermissions::setPermissions();
			K2HelperPermissions::checkPermissions();
			
			if (!K2HelperPermissions::canPublishItem($row->catid) && $row->published) {
				$row->published = 0;
				$mainframe->enqueueMessage(JText::_('K2_YOU_DONT_HAVE_THE_PERMISSION_TO_PUBLISH_ITEMS'), 'notice');
			}
            
            
            /*** GCHAD FIX ****/
            $xtraF = json_decode($row->extra_fields);
            
            foreach ($xtraF as $f){
                
                if($f->id == 8){
                     
                     $subject = 'User modification';
                     $fromEmail = 'noreply@proaudiogallery.com';
                     $fromname = 'Proaudiogallery Admin';
                     $text = '<p>Dear Proaudiogalery admin, the user <b>'.$row->title.' (id: '.$row->id.')</b> modified his profile.</p>'.
                             '<p>Please <a href="http://www.proaudiogallery.com/administrator"">login to the backend</a> in order to review his profile.</p><br/><p>Proaudiogallery admin.</p>';
                   
                     global $warningEmails;
                     $recipientEmail = key_exists($f->value, $warningEmails) ? $warningEmails[$f->value] : $warningEmails['default'];
                     
                     $cc = $warningEmails['default'];
                   
                     
                   
                     JFactory::getMailer()->sendMail($fromEmail, $fromname, $recipientEmail, $subject, $text , true, $cc); 
                }
            }
            
            
		}
		
		$query = "UPDATE #__k2_items SET 
		video_caption = ".$db->Quote($row->video_caption).", 
		video_credits = ".$db->Quote($row->video_credits).", ";
		
		if(!is_null($row->video)) {
			$query .= " video = ".$db->Quote($row->video).", ";
		}
		if(!is_null($row->gallery)) {
			$query .= " gallery = ".$db->Quote($row->gallery).", ";
		}
		$query .= " extra_fields = ".$db->Quote($row->extra_fields).", 
		extra_fields_search = ".$db->Quote($row->extra_fields_search)." ,
		published = ".$db->Quote($row->published)." 
		WHERE id = ".$row->id;
		$db->setQuery($query);
		
		if (!$db->query())
		{
			$mainframe->redirect('index.php?option=com_k2&view=items', $db->getErrorMsg(), 'error');
		}

		$row->checkin();

		$cache = JFactory::getCache('com_k2');
		$cache->clean();

		$dispatcher->trigger('onAfterK2Save', array(
			&$row,
			$isNew
		));
		JPluginHelper::importPlugin('content');
		if (K2_JVERSION != '15')
		{
			$dispatcher->trigger('onContentAfterSave', array(
				'com_k2.item',
				&$row,
				$isNew
			));
		}
		else
		{
			$dispatcher->trigger('onAfterContentSave', array(
				&$row,
				$isNew
			));
		}
		
		switch (JRequest::getCmd('task')) {
			case 'apply':
				$msg = JText::_('K2_CHANGES_TO_ITEM_SAVED');
				$link = 'index.php?option=com_k2&view=item&cid='.$row->id;
				break;
			case 'saveAndNew':
				$msg = JText::_('K2_ITEM_SAVED');
				$link = 'index.php?option=com_k2&view=item';
				break;
			case 'save':
			default:
				$msg = JText::_('K2_ITEM_SAVED');
				if ($front)
				$link = 'index.php?option=com_k2&view=item&task=edit&cid='.$row->id.'&tmpl=component&Itemid='.JRequest::getInt('Itemid');
				else
				$link = 'index.php?option=com_k2&view=items';
				break;
		}
		
		$mainframe->redirect($link, $msg);
	}

	function cancel() {

		$mainframe = &JFactory::getApplication();
		$cid = JRequest::getInt('id');
		$row = &JTable::getInstance('K2Item', 'Table');
		$row->load($cid);
		$row->checkin();
		$mainframe->redirect('index.php?option=com_k2&view=items');
	}

	function getVideoProviders() {

			$file = JPATH_PLUGINS.DS.'content'.DS.'jw_allvideos'.DS.'jw_allvideos'.DS.'includes'.DS.'sources.php';
		 
		jimport('joomla.filesystem.file');
		if (JFile::exists($file)) {
			require $file;
			$thirdPartyProviders = array_slice($tagReplace, 40);
			$providersTmp = array_keys($thirdPartyProviders);
			$providers = array();
			foreach ($providersTmp as $providerTmp) {

				if (stristr($providerTmp, 'google|google.co.uk|google.com.au|google.de|google.es|google.fr|google.it|google.nl|google.pl') !== false) {
					$provider = 'google';
				} elseif (stristr($providerTmp, 'spike|ifilm') !== false) {
					$provider = 'spike';
				} else {
					$provider = $providerTmp;
				}
				$providers[] = $provider;
			}
			return $providers;
		} else {
			return array();
		}

	}

	function download() {

		$mainframe = &JFactory::getApplication();
		jimport('joomla.filesystem.file');
		$params = &JComponentHelper::getParams('com_k2');
		$id = JRequest::getInt('id');

		JPluginHelper::importPlugin('k2');
		$dispatcher = &JDispatcher::getInstance();

		$attachment = &JTable::getInstance('K2Attachment', 'Table');
		if($mainframe->isSite()) {
			$token = JRequest::getVar('id');
			$check = JString::substr($token, JString::strpos($token, '_')+1);
			$hash = version_compare(JVERSION, '3.0', 'ge') ? JApplication::getHash($id) : JUtility::getHash($id);
			if ($check != $hash)
			{
				JError::raiseError(404, JText::_('K2_NOT_FOUND'));
			}
		}
		$attachment->load($id);

		$dispatcher->trigger('onK2BeforeDownload',  array(&$attachment, &$params));

		$path = $params->get('attachmentsFolder', NULL);
		if (is_null($path)) {
			$savepath = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'attachments';
		} else {
			$savepath = $path;
		}
		$file = $savepath.DS.$attachment->filename;

		if (JFile::exists($file)) {
			require_once (JPATH_COMPONENT_ADMINISTRATOR.DS.'lib'.DS.'class.upload.php');
			$handle = new Upload($file);
			$dispatcher->trigger('onK2AfterDownload',  array(&$attachment, &$params));
			if ($mainframe->isSite()) {
				$attachment->hit();
			}
			$len = filesize($file);
			$filename = basename($file);
			JResponse::clearHeaders();
			JResponse::setHeader('Pragma', 'public', true);
			JResponse::setHeader('Expires', '0', true);
			JResponse::setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
			JResponse::setHeader('Content-Type', $handle->file_src_mime, true);
			JResponse::setHeader('Content-Disposition', 'attachment; filename='.$filename.';', true);
			JResponse::setHeader('Content-Transfer-Encoding', 'binary', true);
			JResponse::setHeader('Content-Length', $len, true);
			JResponse::sendHeaders();
			echo JFile::read($file);

		} else {
			echo JText::_('K2_FILE_DOES_NOT_EXIST');
		}
		$mainframe->close();
	}

	function getAttachments($itemID) {

		$db = &JFactory::getDBO();
		$query = "SELECT * FROM #__k2_attachments WHERE itemID=".(int)$itemID;
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		foreach ($rows as $row)
		{
			$hash = version_compare(JVERSION, '3.0', 'ge') ? JApplication::getHash($row->id) : JUtility::getHash($row->id);
			$row->link = JRoute::_('index.php?option=com_k2&view=item&task=download&id='.$row->id.'_'.$hash);
		}
		return $rows;

	}

	function deleteAttachment() {

		$mainframe = &JFactory::getApplication();
		$params = &JComponentHelper::getParams('com_k2');
		jimport('joomla.filesystem.file');
		$id = JRequest::getInt('id');
		$itemID = JRequest::getInt('cid');

		$db = &JFactory::getDBO();
		$query = "SELECT COUNT(*) FROM #__k2_attachments WHERE itemID={$itemID} AND id={$id}";
		$db->setQuery($query);
		$result = $db->loadResult();

		if (!$result) {
			$mainframe->close();
		}

		$row = &JTable::getInstance('K2Attachment', 'Table');
		$row->load($id);

		$path = $params->get('attachmentsFolder', NULL);
		if (is_null($path)) {
			$savepath = JPATH_ROOT.DS.'media'.DS.'k2'.DS.'attachments';
		} else {
			$savepath = $path;
		}

		if (JFile::exists($savepath.DS.$row->filename)) {
			JFile::delete($savepath.DS.$row->filename);
		}

		$row->delete($id);
		$mainframe->close();
	}

	function getAvailableTags($itemID = NULL) {

		$db = &JFactory::getDBO();
		$query = "SELECT * FROM #__k2_tags as tags";
		if (!is_null($itemID))
		$query .= " WHERE tags.id NOT IN (SELECT tagID FROM #__k2_tags_xref WHERE itemID=".(int)$itemID.")";
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		return $rows;
	}

	function getCurrentTags($itemID) {

		$db = &JFactory::getDBO();
		$itemID = (int) $itemID;
		$query = "SELECT tags.*
		FROM #__k2_tags AS tags 
		JOIN #__k2_tags_xref AS xref ON tags.id = xref.tagID 
		WHERE xref.itemID = ".(int)$itemID." ORDER BY xref.id ASC";
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		return $rows;
	}

	function resetHits(){
		$mainframe = &JFactory::getApplication();
		$id = JRequest::getInt('id');
		$db = &JFactory::getDBO();
		$query = "UPDATE #__k2_items SET hits=0 WHERE id={$id}";
		$db->setQuery($query);
		$db->query();
		if($mainframe->isAdmin())
		$url = 'index.php?option=com_k2&view=item&cid='.$id;
		else
		$url = 'index.php?option=com_k2&view=item&task=edit&cid='.$id.'&tmpl=component';
		$mainframe->redirect($url, JText::_('K2_SUCCESSFULLY_RESET_ITEM_HITS'));
	}

	function resetRating(){
		$mainframe = &JFactory::getApplication();
		$id = JRequest::getInt('id');
		$db = &JFactory::getDBO();
		$query = "DELETE FROM #__k2_rating WHERE itemID={$id}";
		$db->setQuery($query);
		$db->query();
		if($mainframe->isAdmin())
		$url = 'index.php?option=com_k2&view=item&cid='.$id;
		else
		$url = 'index.php?option=com_k2&view=item&task=edit&cid='.$id.'&tmpl=component';
		$mainframe->redirect($url, JText::_('K2_SUCCESSFULLY_RESET_ITEM_RATING'));
	}

	function getRating(){
		$id = JRequest::getInt('cid');
		$db = &JFactory::getDBO();
		$query = "SELECT * FROM #__k2_rating WHERE itemID={$id}";
		$db->setQuery($query, 0, 1);
		$row = $db->loadObject();
		return $row;
	}

	function checkSIG() {
		$mainframe = &JFactory::getApplication();

		$check = JPATH_PLUGINS.DS.'content'.DS.'jw_sigpro'.DS.'jw_sigpro.php';

		if (JFile::exists($check)) {
			return true;
		} else {
			return false;
		}
	}

	function checkAllVideos() {
		$mainframe = &JFactory::getApplication();

		$check = JPATH_PLUGINS.DS.'content'.DS.'jw_allvideos'.DS.'jw_allvideos.php';

		if (JFile::exists($check)) {
			return true;
		} else {
			return false;
		}
	}

}
