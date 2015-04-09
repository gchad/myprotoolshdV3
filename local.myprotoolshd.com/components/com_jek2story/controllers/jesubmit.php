<?php
/**
* @package   JE K2 STORY
* @copyright Copyright (C) 2009 - 2010 Open Source Matters. All rights reserved.
* @license   http://www.gnu.org/licenses/lgpl.html GNU/LGPL, see LICENSE.php
* Contact to : emailtohardik@gmail.com, joomextensions@gmail.com
* Visit : http://www.joomlaextensions.co.in/
**/ 

defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.controller' );
jimport( 'joomla.html.parameter' );
jimport('joomla.filesystem.file');

$newimgpath = str_replace('com_jek2story','com_k2',JPATH_COMPONENT);

JTable::addIncludePath($newimgpath.'/'.'tables');

class jesubmitController extends JControllerLegacy  {
     
	function __construct( $default = array())
	{
		
		parent::__construct( $default );
		$k2admin_path = str_replace('com_jek2story','com_k2',JPATH_COMPONENT_ADMINISTRATOR);
		define (JPATH_COMPONENT_ADMINISTRATOR,$k2admin_path);
	}
	
	function display() {
		
		$mainframe = &JFactory::getApplication();
		$user =  clone(JFactory::getUser());
		
		$tblsetting	= $this->getcheck1();
		
		
		if($tblsetting->allow_reguser==1) {
			if($user->id!=0) {
				parent::display();
			} else {
				$msg = JText::_ ( 'ONLY_REGISTERED_USER_CAN_POST_STORY' );
				$this->setRedirect ( 'index.php', $msg );
			}
		} else {
			parent::display();
		}
	}
	
	function getcheck1()
	{
		$db= & JFactory :: getDBO();
		$query = 'SELECT * FROM #__je_jek2submit';
		$db->setQuery( $query );
		$res=$db->loadObject();
		return $res;
	}
	
	function cancel()
	{
		$option = JRequest::getVar('option');
		$this->setRedirect ( 'index.php?option='.$option);
		return true;
	}
    
    /** NEEDS TESTING ON ALL UPLOAD FIELS, Check the extra fields that where hidden); */
    function save(){
        
        JRequest::checkToken() or jexit( 'Invalid Token' );
        
        $mainframe = &JFactory::getApplication();
        $uri =& JURI::getInstance();
        $url= $uri->root();
        $user =& JFactory::getUser();
        
        $k2admin_path = str_replace('com_jek2story','com_k2',JPATH_COMPONENT_ADMINISTRATOR);
        $db = JFactory::getDBO();
        $model = $this->getModel ('jesubmit');
        $mesg   = $model->getcheck1();
        $now =& JFactory::getDate();
        $created=$now->toSql();
        
        if(isset($_POST['facilityName']) && isset($_POST['artistName'])){
             $_POST['title'] =  $_POST['facilityName'].' -- '. $_POST['artistName'];
        }else {
             $_POST['title'] =  $_POST['facilityName'];
        }
       
        $post = $_POST;
        
        
        require_once ($k2admin_path.'/'.'lib'.'/'.'class.upload.php');
        $params = &JComponentHelper::getParams('com_k2');
        
        $fulltext =     JRequest::getVar( 'fulltext', '', 'post', 'string', JREQUEST_ALLOWRAW );
        $Itemid =       JRequest::getVar('Itemid','','','int'); 
        $file =&        JRequest::getVar('itemimage', '', 'files', 'array' );
        $option =       JRequest::getVar('option','','','string');
        $setting_name = JRequest::getVar('setting_name','','','int');   
        $setting_email= JRequest::getVar('setting_email','','','int'); 
        
        /*if($setting_name == 0){
          $post['name'] = $user->name;
        }
        
        if($setting_email == 0){
          $post['email'] = $user->email;
        }*/
        
      
        $cap        =   $_SESSION['comments-captcha-code'];
        $textval    = $post['cap'];
        
        //editing mode NOT USED NOW
        
        if($post['k2itemid']){
          
        //adding mode    
        } else {
           
          
             if (filter_var($post['email'], FILTER_VALIDATE_EMAIL) == false){
                 $msg = JText::_( 'VALID_EMAIL');
                 $mainframe->redirect(JRoute::_('index.php?option='.$option.'&view=jesubmit&ses=1'), $msg, 'error');
             }
             
             //check all fields?
             if(empty($post['email']) || empty($post['name']) || empty($post['title'])){
                  $msg = JText::_( 'FILL_ALL_FIELDS');
                  $mainframe->redirect(JRoute::_('index.php?option='.$option.'&view=jesubmit&ses=1'), $msg,'error');
                  
             }
                        
            if($cap == $textval) { 
                
                $mosConfig_live_site=  substr_replace(JURI::root(), '', -1, 1); 
                //$my =& JFactory::getUser(); 
                $title = strip_tags($post['title']);
                
                $objects = array();
                $variables = JRequest::get('post', 4);
                
                //extra fields
                foreach ($variables as $key => $value) {
                    
                    if ( (bool)JString::stristr($key, 'K2ExtraField_')) {
                            
                        $object = new JObject;
                        $object->set('id', JString::substr($key, 13));
                        $object->set('value', $value);
                        unset($object->_errors);
                        $objects[] = $object;
                    }
                }
                
                
                //extra fields image
                $csvFiles = JRequest::get('files');
                
                foreach ($csvFiles as $key => $file) {
                    
                    if ( (bool)JString::stristr($key, 'K2ExtraField_')) {
                        
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
                            
                        } else {
                            
                            require_once ($k2admin_path.'/'.'lib'.'/'.'JSON.php');
                            $json = new Services_JSON;
                            $object->set('value', $json->decode(JRequest::getVar('K2CSV_'.$object->id)));
                            if(JRequest::getBool('K2ResetCSV_'.$object->id))
                                $object->set('value', null);
                        }
                        
                        unset($object->_errors);
                        $objects[] = $object;
                    }
                }
                
                //extra fields to json
                require_once ($k2admin_path.'/'.'lib'.'/'.'JSON.php');
                $json = new Services_JSON;
                $field_data = $json->encode($objects);
       
                //extra fields search
                require_once ($k2admin_path.'/'.'models'.'/'.'extrafield.php');
                $extraFieldModel = new K2ModelExtraField;
                $row = new stdClass;
                $row->extra_fields_search = '';
            
                foreach ($objects as $object) {
                    $row->extra_fields_search .= $extraFieldModel->getSearchValue($object->id, $object->value);
                    $row->extra_fields_search .= ' ';
                }
               
                //tweek I don't understand
                $field_search = $row->extra_fields_search; 
                $post['fulltext'] = htmlentities($_POST['fulltext']);
                $pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
                $text = $_POST['fulltext'];
                $tagPos = preg_match($pattern, $fulltext); 

                if ($tagPos == 0) {
                    
                    $post['fulltext'] = $text;
                    $myfulltext = '';
                    
                } else{
                     
                     list($post['fulltext'], $myfulltext) = preg_split($pattern, $text, 2);
                }   
                
                //google map
                $map = array(
                    'address' =>  $post['address'],
                    'latitude' =>  $post['address_lat'],
                    'longitude' => $post['address_long'],
                    'privacy' => '1'
                );
              
                $plugin = json_encode( $map, JSON_UNESCAPED_UNICODE );
                $user->id = 0;
                 
                //insert in database
                $sql = "INSERT INTO #__k2_items ".
                       "(`title`,`alias`,`catid`,`published`,`introtext`,`fulltext`,`extra_fields`,`extra_fields_search`,`created`,`created_by`,`publish_up`,`access`,`language`, `plugins`) ".
                       "values ('".$post['title']."','".$post['title']."',".$post['catid'].",".$post['publish'].",'".addslashes($post['fulltext'])."','".addslashes($myfulltext)."','".addslashes($field_data)."','".addslashes($field_search)."','".$created."',".$user->id.",'".$created."','1','*','".addslashes($plugin)."')"; 
                       
                
                $db->setQuery($sql);
                $db->query();
                $item_id = $db->insertid();
                
                
                //register a new joomla
                $user->name = $post['title'];
                $user->username = $post['name'];
                /* TO DO */
                
                
               
                
                //insert in k2story db
                $published = $post['publish'] == 0 ? 0 : 1; 
                
                $sql = "INSERT INTO #__je_k2itemlist (`itemid`,`userid`,`name`,`email`,`published`)".
                       " values (".$item_id.",".$user->id.",'".$post['name']."','".$post['email']."','".$published."')";  
                
                $db->setQuery($sql);
                $db->query();
                
               
                
                //test if there is a file
                if($file['name'] != '') {
                        
                    $row->p_name = JPath::clean(time().'_'.$file['name']); 
                    $filetype = strtolower(JFile::getExt($file['name']));
                    $mylink = JRoute::_('index.php?option='.$option.'&view=product_detail&edit&cid[]='.$post['id']);
                   
                    if(!$this->processImage($item_id, $post['catid'], $file)){
                       
                        $msg = JText::_ ( 'PLEASE_UPLOAD_VALID_DOCUMENT_FILE' );
                        $mainframe->redirect( $mylink,$msg );   
                    }    
                }
                
                //emails
                if ($mesg->notify) {
                    
                    $browse_tempt = $mesg->message;
                    $browse_tempt = str_replace("{created_by}", $post['name'], $browse_tempt);
                    $browse_tempt = str_replace("{email}", $post['email'], $browse_tempt);
                    $browse_tempt = str_replace("{introtext}", $post['title'], $browse_tempt);
                    $browse_tempt = str_replace("{fulltext}", $post['fulltext'], $browse_tempt);
                    $browse_tempt = str_replace("{REMOTE_ADDR}", $post['address'], $browse_tempt);
                         
                    $browse_tempt1 = $mesg->notify_message;
                    $created_by_alias1 = isset($user->name) ? $user->name : $post['name'];
                    $browse_tempt1 =str_replace("{User}", $post['name'], $browse_tempt1);
                                
                    $config     = &JFactory::getConfig();
                    $from       = $post['email']; 
                    $fromname   = $post['name'];        
                    $subject    = "New story - $title";
                    $created_by_alias = 'Admin'; 
                  
                    JFactory::getMailer()->sendMail($from               , $fromname         , $mesg->notify_email , $subject, $browse_tempt , $mode=1); //mail go to admin
                    JFactory::getMailer()->sendMail($mesg->notify_email , $created_by_alias , $from               , $subject, $browse_tempt1, $mode=1); // User msg
                }
                
                
                if($mesg->pageurl == '0') {
                    
                    $redir_link = JRoute::_('index.php');
                    
                } else {
                    
                    $k2myitem   = $model->getk2item($mesg->pageurl);
                    $redir_link = JRoute::_('index.php?option=com_k2&view=item&id='.$k2myitem->id.':'.$k2myitem->alias);
                }
                
                $msg = JText::_( 'SUCCESS');
                $this->setRedirect ($redir_link, $msg);
                
            } //endig Adding mode
            
            return;
        } // if cap // no text val
        
        $msg = JText::_( 'NO_CAP');
        $this->setRedirect ($redir_link, $msg);
    }

	function save2()
	{
		$mainframe = &JFactory::getApplication();
		$uri =& JURI::getInstance();
		$url= $uri->root();
		JRequest::checkToken() or jexit( 'Invalid Token' );
		$k2admin_path = str_replace('com_jek2story','com_k2',JPATH_COMPONENT_ADMINISTRATOR);
		$db = JFactory::getDbo();
		$post = JRequest::get ( 'post' );
		
		$fulltext = JRequest::getVar( 'fulltext', '', 'post', 'string', JREQUEST_ALLOWRAW );
		//echo $auto_publish = JRequest::getVar('auto_publish','0','','int');	
		
		//===================== k2 component ========================//

	    /*$path = $url.'administrator/components/com_k2\lib\class.upload.php';
		require_once($path);*/
		//require_once (JPATH_COMPONENT.DS.'lib'.DS.'class.upload.php');
		require_once ($k2admin_path.'/'.'lib'.'/'.'class.upload.php');
		$params = &JComponentHelper::getParams('com_k2');
		
	
		
		//===================== k2 component ========================//
		$Itemid = JRequest::getVar('Itemid','','','int');	
					
		$file =& JRequest::getVar('itemimage', '', 'files', 'array' );
		
		$uri =& JURI::getInstance();
		$url= $uri->root();
		$option = JRequest::getVar('option','','','string');
		$user =& JFactory::getUser();
		$setting_name = JRequest::getVar('setting_name','','','int');	
		$setting_email = JRequest::getVar('setting_email','','','int');	
		
		if($setting_name == 0){
		  $post['name'] = $user->name;
		}
		
		if($setting_email == 0){
		  $post['email'] = $user->email;
		}
				
		$model = $this->getModel ('jesubmit');
		$mesg	= $model->getcheck1();
	
        //EDITING MODE
        
		if($post['k2itemid']){
		    
			$editlink = JRoute::_('index.php?option='.$option.'&view=itemlist&Itemid='.$Itemid) ;
			// ===================for extra fields ===============================================================
				$objects = array();
				$variables = JRequest::get('post', 4);
				foreach ($variables as $key=>$value) {
					if (( bool )JString::stristr($key, 'K2ExtraField_')) {
						$object = new JObject;
						$object->set('id', JString::substr($key, 13));
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
						} else {
							require_once ($k2admin_path.'/'.'lib'.'/'.'JSON.php');
							$json = new Services_JSON;
							$object->set('value', $json->decode(JRequest::getVar('K2CSV_'.$object->id)));
							if(JRequest::getBool('K2ResetCSV_'.$object->id))
								$object->set('value', null);
						}
						unset($object->_errors);
						$objects[] = $object;
					}
				}
                
				require_once ($k2admin_path.'/'.'lib'.'/'.'JSON.php');
				$json = new Services_JSON;
				$field_data = $json->encode($objects);
		
				require_once ($k2admin_path.'/'.'models'.'/'.'extrafield.php');
				$extraFieldModel = new K2ModelExtraField;
				$row->extra_fields_search = '';
			
				foreach ($objects as $object) {
					$row->extra_fields_search .= $extraFieldModel->getSearchValue($object->id, $object->value);
					$row->extra_fields_search .= ' ';
				}
				
		
				$field_search =$row->extra_fields_search; 
				
				$post['fulltext'] = htmlentities($_POST['fulltext']);
				//--------------code by sanju---------------------------//
				 $pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
				
				 $text = $_POST['fulltext'];
				 $tagPos = preg_match($pattern, $fulltext); 

				if ($tagPos == 0) {
				$post['fulltext'] = $text;
				$myfulltext = '';
					} else{
						list($post['fulltext'], $myfulltext) = preg_split($pattern, $text, 2);
					}			
					
				
    	       //============================================================End of extra field code ====================================================================	
    	        $date_time = date('Y-m-d h:i:s'); 
    			
    			$sql="UPDATE #__k2_items SET `title`='".$post['title']."',`catid`=".$post['catid'].",`introtext`='".addslashes($fulltext)."',`fulltext`='".addslashes($myfulltext)."',`extra_fields`='".addslashes($field_data)."', `extra_fields_search` ='".addslashes($field_search)."', modified = '".$date_time."',modified_by='".$user->id."' WHERE id=".$post['k2itemid'];    
    			
    			$db->setQuery($sql);
    			$temp = $db->query();
    			
    			$sql="UPDATE #__je_k2itemlist SET name='".$post['name']."',email='".$post['email']."' WHERE itemid=".$post['k2itemid']; 
    			$db->setQuery($sql);
    			$temp = $db->query();
    			
			
    			if($file['name']!='') {
    			    	
					$row->p_name= JPath::clean(time().'_'.$file['name']);
					$filetype = strtolower(JFile::getExt($file['name']));//Get extension of the file
					
					$item_id = $post['k2itemid'];
					
					if($filetype =='jpg' || $filetype=='jpeg' || $filetype =='png' || $filetype =='gif'){
    						
            			if (JFile::exists(JPATH_SITE.'/'.'media'.'/'.'k2'.'/'.'items'.'/'.'cache'.'/'.md5("Image".$post['id']).'_XS.jpg'))
            			unlink(JPATH_SITE.'/'.'media'.'/'.'k2'.'/'.'items'.'/'.'cache'.'/'.md5("Image".$post['id']).'_XS.jpg');
            			
            			if (JFile::exists(JPATH_SITE.'/'.'media'.'/'.'k2'.'/'.'items'.'/'.'cache'.'/'.md5("Image".$post['id']).'_S.jpg'))
            			unlink(JPATH_SITE.'/'.'media'.'/'.'k2'.'/'.'items'.'/'.'cache'.'/'.md5("Image".$post['id']).'_S.jpg');
            
            			if (JFile::exists(JPATH_SITE.'/'.'media'.'/'.'k2'.'/'.'items'.'/'.'cache'.'/'.md5("Image".$post['id']).'_M.jpg'))
            			unlink(JPATH_SITE.'/'.'media'.'/'.'k2'.'/'.'items'.'/'.'cache'.'/'.md5("Image".$post['id']).'_M.jpg');
            	
            			if (JFile::exists(JPATH_SITE.'/'.'media'.'/'.'k2'.'/'.'items'.'/'.'cache'.'/'.md5("Image".$post['id']).'_L.jpg'))
            			unlink(JPATH_SITE.'/'.'media'.'/'.'k2'.'/'.'items'.'/'.'cache'.'/'.md5("Image".$post['id']).'_L.jpg');
            	
            			if (JFile::exists(JPATH_SITE.'/'.'media'.'/'.'k2'.'/'.'items'.'/'.'cache'.'/'.md5("Image".$post['id']).'_XL.jpg'))
            			unlink(JPATH_SITE.'/'.'media'.'/'.'k2'.'/'.'items'.'/'.'cache'.'/'.md5("Image".$post['id']).'_XL.jpg');
            	
            			if (JFile::exists(JPATH_SITE.'/'.'media'.'/'.'k2'.'/'.'items'.'/'.'cache'.'/'.md5("Image".$post['id']).'_Generic.jpg'))
            			unlink(JPATH_SITE.'/'.'media'.'/'.'k2'.'/'.'items'.'/'.'cache'.'/'.md5("Image".$post['id']).'_Generic.jpg');
						
						$handle = new Upload($file);
						$handle->allowed = array('image/*');
    						
    			        if ($handle->uploaded) {
    
            				//Image params
            				$category = &JTable::getInstance('K2Category', 'Table');
            				$category->load($post['catid']);
            				//$category->load($row->catid);
            				$cparams = new JParameter($category->params);
            
            				if ($cparams->get('inheritFrom')) {
            					$masterCategoryID = $cparams->get('inheritFrom');
            					$query = "SELECT * FROM #__k2_categories WHERE id=".(int)$masterCategoryID;
            					$db->setQuery($query, 0, 1);
            					$masterCategory = $db->loadObject();
            					$cparams = new JParameter($masterCategory->params);
            				}
            
            				$params->merge($cparams);
    
        				//Original image
        				$savepath = JPATH_SITE.'/'.'media'.'/'.'k2'.'/'.'items'.'/'.'src';
        				$handle->image_convert = 'jpg';
        				$handle->jpeg_quality = 100;
        				$handle->file_auto_rename = false;
        				$handle->file_overwrite = true;
        				$handle->file_new_name_body = md5("Image".$post['id']);
        				$handle->Process($savepath);
        
        				$filename = $handle->file_dst_name_body;
                        
        				
                        $savepath = JPATH_SITE.'/'.'media'.'/'.'k2'.'/'.'items'.'/'.'cache';
        				//XLarge image
                        
        				$handle->image_resize = true;
        				$handle->image_ratio_y = true;
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
        				$handle->image_x = $imageWidth;
        				$handle->Process($savepath);
        
        				//Large image
        				$handle->image_resize = true;
        				$handle->image_ratio_y = true;
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
        				$handle->Process($savepath);
        
        				//Medium image
        				$handle->image_resize = true;
        				$handle->image_ratio_y = true;
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
        				$handle->Process($savepath);
        
        				//Small image
        				$handle->image_resize = true;
        				$handle->image_ratio_y = true;
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
        				$handle->Process($savepath);
        
        				//XSmall image
        				$handle->image_resize = true;
        				$handle->image_ratio_y = true;
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
        				$handle->Process($savepath);
        
        				//Generic image
        				$handle->image_resize = true;
        				$handle->image_ratio_y = true;
        				$handle->image_convert = 'jpg';
        				$handle->jpeg_quality = $params->get('imagesQuality');
        				$handle->file_auto_rename = false;
        				$handle->file_overwrite = true;
        				$handle->file_new_name_body = $filename.'_Generic';
        				$imageWidth = $params->get('itemImageGeneric', '300');
        				$handle->image_x = $imageWidth;
        				$handle->Process($savepath);
        
        				if($files['image']['error'] === 0)
        				    $handle->Clean();
        				
        				} 
        				
        			} else {
        				$msg = JText::_ ( 'PLEASE_UPLOAD_VALID_DOCUMENT_FILE' );
        				$mainframe->redirect( $mylink,$msg );	
        			}
    		   }

        	$msg = JText::_ ( 'ITEM_EDIT_SUCCESSFULLY' );
        	$mainframe->redirect( $editlink,$msg );	
        
        
        //ADDING MODE	
        
		} else {
		
    		$cap		=	$_SESSION['comments-captcha-code'];
    		$textval	= $post['cap'];
    		//$textval = base64_decode($v11);
    		//$created = date('Y-m-d H:m:s');
    		$now =& JFactory::getDate();
    		$created=$now->toSql();//$jdate->toSql()  
		
				
		    if($cap == $textval) { 
			    
				$mosConfig_live_site=  substr_replace(JURI::root(), '', -1, 1);
				$my =& JFactory::getUser();
				$title = strip_tags($_POST['title']);
				
				
				// ===================for extra fields ===============================================================
				$objects = array();
				$variables = JRequest::get('post', 4);
                
				foreach ($variables as $key=>$value) {
					if (( bool )JString::stristr($key, 'K2ExtraField_')) {
						$object = new JObject;
						$object->set('id', JString::substr($key, 13));
						$object->set('value', $value);
						unset($object->_errors);
						$objects[] = $object;
					}
				}
                
				$csvFiles = JRequest::get('files');
                
				foreach ($csvFiles as $key => $file) {
				    
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
						} else {
							require_once ($k2admin_path.'/'.'lib'.'/'.'JSON.php');
							$json = new Services_JSON;
							$object->set('value', $json->decode(JRequest::getVar('K2CSV_'.$object->id)));
							if(JRequest::getBool('K2ResetCSV_'.$object->id))
								$object->set('value', null);
						}
                        
						unset($object->_errors);
						$objects[] = $object;
					}
				}
                
				require_once ($k2admin_path.'/'.'lib'.'/'.'JSON.php');
				$json = new Services_JSON;
				$field_data = $json->encode($objects);
		
				require_once ($k2admin_path.'/'.'models'.'/'.'extrafield.php');
				$extraFieldModel = new K2ModelExtraField;
				$row->extra_fields_search = '';
			
				foreach ($objects as $object) {
					$row->extra_fields_search .= $extraFieldModel->getSearchValue($object->id, $object->value);
					$row->extra_fields_search .= ' ';
				}
                
				$field_search =$row->extra_fields_search; 
				
				$post['fulltext'] = htmlentities($_POST['fulltext']);
                
				
				$pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
				
				$text = $_POST['fulltext'];
				$tagPos = preg_match($pattern, $fulltext); 

				if ($tagPos == 0) {
				    
				    $post['fulltext'] = $text;
				    $myfulltext = '';
                    
				} else{
				     
    			     list($post['fulltext'], $myfulltext) = preg_split($pattern, $text, 2);
				}			
					
								
				 $sql = "INSERT INTO #__k2_items (`title`,`alias`,`catid`,`published`,`introtext`,`fulltext`,`extra_fields`,`extra_fields_search`,`created`,`created_by`,`publish_up`,`access`,`language`) values ('".$post['title']."','".$post['title']."',".$post['catid'].",".$post['publish'].",'".addslashes($post['fulltext'])."','".addslashes($myfulltext)."','".addslashes($field_data)."','".addslashes($field_search)."','".$created."',".$user->id.",'".$created."','1','*')"; 
				
    			 $db->setQuery($sql);
    			 $db->query();
    			 $item_id = $db->insertid();
	
	             //===========================================Enter data in our component table =========================================================
	
				 if($post['publish'] == 0){
				    $published = 0;
				 }else{
				    $published = 1;
				 }	
				
			     $sql = "INSERT INTO #__je_k2itemlist (`itemid`,`userid`,`name`,`email`,`published`) values (".$item_id.",".$user->id.",'".$post['name']."','".$post['email']."','".$published."')";  
				
				 $db->setQuery($sql);
				 $db->query();
	
	             //======================================================================================================================================	
				
				 $browse_tempt=$mesg->message;
				 $browse_tempt=str_replace("{created_by}",$post['name'],$browse_tempt);
				 $browse_tempt=str_replace("{email}",$post['email'],$browse_tempt);
				 $browse_tempt=str_replace("{introtext}",$post['title'],$browse_tempt);
				 $browse_tempt=str_replace("{fulltext}",$post['fulltext'],$browse_tempt);
				 $browse_tempt=str_replace("{REMOTE_ADDR}",$_SERVER["REMOTE_ADDR"],$browse_tempt);
					 
				 $browse_tempt1=$mesg->notify_message;
				 $created_by_alias1 = $my->name;
				 $browse_tempt1=str_replace("{User}",$post['name'],$browse_tempt1);
				
				 if($file['name']!='') {
				     	
				 	$row->p_name= JPath::clean(time().'_'.$file['name']); 
					$filetype = strtolower(JFile::getExt($file['name']));//Get extension of the file
					$mylink = JRoute::_('index.php?option='.$option.'&view=product_detail&edit&cid[]='.$post['id']);
                    
					if($filetype =='jpg' || $filetype=='jpeg' || $filetype =='png' || $filetype =='gif') {
						    
						$handle = new Upload($file);
						$handle->allowed = array('image/*');
						
		                if ($handle->uploaded) {

            				//Image params
            				$category = &JTable::getInstance('K2Category', 'Table');
            				$category->load($post['catid']);
            				//$category->load($row->catid);
            				$cparams = json_decode($category->params);

            				if ($cparams->inheritFrom) {
            					$masterCategoryID = $cparams->inheritFrom;
            					$query = "SELECT * FROM #__k2_categories WHERE id=".(int)$masterCategoryID;
            					$db->setQuery($query, 0, 1);
            					$masterCategory = $db->loadObject();
            					$cparams = json_decode($masterCategory->params);
            				}

				            $params->merge($cparams);

            				//Original image
            				$savepath = JPATH_SITE.'/'.'media'.'/'.'k2'.'/'.'items'.'/'.'src';
            				$handle->image_convert = 'jpg';
            				$handle->jpeg_quality = 100;
            				$handle->file_auto_rename = false;
            				$handle->file_overwrite = true;
            				$handle->file_new_name_body = md5("Image".$item_id);
            				$handle->Process($savepath);
            
            				$filename = $handle->file_dst_name_body;
            				$savepath = JPATH_SITE.'/'.'media'.'/'.'k2'.'/'.'items'.'/'.'cache';
            
            				//XLarge image
            				$handle->image_resize = true;
            				$handle->image_ratio_y = true;
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
            				$handle->image_x = $imageWidth;
            				$handle->Process($savepath);
            
            				//Large image
            				$handle->image_resize = true;
            				$handle->image_ratio_y = true;
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
            				$handle->Process($savepath);
            
            				//Medium image
            				$handle->image_resize = true;
            				$handle->image_ratio_y = true;
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
            				$handle->Process($savepath);
            
            				//Small image
            				$handle->image_resize = true;
            				$handle->image_ratio_y = true;
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
            				$handle->Process($savepath);
            
            				//XSmall image
            				$handle->image_resize = true;
            				$handle->image_ratio_y = true;
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
            				$handle->Process($savepath);
            
            				//Generic image
            				$handle->image_resize = true;
            				$handle->image_ratio_y = true;
            				$handle->image_convert = 'jpg';
            				$handle->jpeg_quality = $params->get('imagesQuality');
            				$handle->file_auto_rename = false;
            				$handle->file_overwrite = true;
            				$handle->file_new_name_body = $filename.'_Generic';
            				$imageWidth = $params->get('itemImageGeneric', '300');
            				$handle->image_x = $imageWidth;
            				$handle->Process($savepath);
            
            				if($files['image']['error'] === 0)
            				$handle->Clean();
            				
            				} 
					
						
					
    					} else {
    					    
    						$msg = JText::_ ( 'PLEASE_UPLOAD_VALID_DOCUMENT_FILE' );
    						$mainframe->redirect( $mylink,$msg );	
    					}
				    }

				    if ($mesg->notify) {
				        	
        				$config		= &JFactory::getConfig();
        				$from		= $post['email']; 
        				$fromname	= $post['name'];		
        				$subject 	= "New story - $title";
        				$created_by_alias = 'Admin'; 
        				//$return = JFactory::getMailer()->sendMail;
        				//echo $browse_tempt;exit;
        				JFactory::getMailer()->sendMail($from, $fromname,$mesg->notify_email, $subject, $browse_tempt, $mode=1);//mail go to admin
        				JFactory::getMailer()->sendMail($mesg->notify_email, $created_by_alias,$from, $subject, $browse_tempt1, $mode=1);// User msg
        			}
				
		
    				if($mesg->pageurl=='0') {
    				    
    					$redir_link	= JRoute::_('index.php');
                        
    				} else {
    				    
    					$k2myitem	= $model->getk2item($mesg->pageurl);
    					$redir_link	= JRoute::_('index.php?option=com_k2&view=item&id='.$k2myitem->id.':'.$k2myitem->alias);
    				}
                    
    				$msg    = JText::_( 'SUCCESS');
    				$this->setRedirect ($redir_link,$msg);
				
		        } else {
				    
				    if($post['cpt'] == "0") {
					
    					$db = JFactory::getDbo();
    					$mosConfig_live_site=  substr_replace(JURI::root(), '', -1, 1);
    					$my =& JFactory::getUser();
    					$model = $this->getModel ('jesubmit');
    					$mesg=$model->getcheck1();
    					$title = strip_tags($_POST['title']);
    					# input validation
    					$fulltext = JRequest::getVar( 'fulltext', '', 'post', 'string', JREQUEST_ALLOWRAW );
    					// ===================for extra fields ===============================================================
    					$objects = array();
    					$variables = JRequest::get('post', 4);
    					
    					foreach ($variables as $key=>$value) {
    						if (( bool )JString::stristr($key, 'K2ExtraField_')) {
    							$object = new JObject;
    							$object->set('id', JString::substr($key, 13));
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
    							} else {
    								require_once (JPATH_COMPONENT_ADMINISTRATOR.'/'.'lib'.'/'.'JSON.php');
    								$json = new Services_JSON;
    								$object->set('value', $json->decode(JRequest::getVar('K2CSV_'.$object->id)));
    								if(JRequest::getBool('K2ResetCSV_'.$object->id))
    									$object->set('value', null);
    							}
    							unset($object->_errors);
    							$objects[] = $object;
    						}
    					}

    					require_once (JPATH_COMPONENT_ADMINISTRATOR.'/'.'lib'.'/'.'JSON.php');
    					$json = new Services_JSON;
    					$field_data = $json->encode($objects);
    					require_once ($k2admin_path.'/'.'models'.'/'.'extrafield.php');
    					$extraFieldModel = new K2ModelExtraField;
    					$row->extra_fields_search = '';
					
    					foreach ($objects as $object) {
    						$row->extra_fields_search .= $extraFieldModel->getSearchValue($object->id, $object->value);
    						$row->extra_fields_search .= ' ';
    					}
    					$field_search =$row->extra_fields_search; 
    					
                        
    					$post['fulltext'] = htmlentities($_POST['fulltext']);
    					//--------------code by sanju---------------------------//
    					$pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
    					
    					$text = $_POST['fulltext'];
    					$tagPos = preg_match($pattern, $fulltext);
    					if ($tagPos == 0) {
    					$post['fulltext'] = $text;
    					$myfulltext = '';
    						} else{
    							list($post['fulltext'], $myfulltext) = preg_split($pattern, $text, 2);
    						}			
    			
    					//--------------code by sanju---------------------------//
			
					
    					//$post['fulltext'] = addslashes($_POST['fulltext']);
    					//$myfulltext			= "";
			            //================================end of code =========================================================
    					$sql = "INSERT INTO #__k2_items (`title`,`alias`,`catid`,`published`,`introtext`,`fulltext`,`extra_fields`,`extra_fields_search`,`created`,`created_by`,`publish_up`,`access`,`language`) values ('".$post['title']."','".$post['title']."',".$post['catid'].",".$post['publish'].",'".addslashes($post['fulltext'])."','".addslashes($myfulltext)."','".addslashes($field_data)."','".addslashes($field_search)."','".$created."',".$user->id.",'".$created."','1','*')";  
    					$db->setQuery($sql);
    					$db->query();
    					$item_id = $db->insertid();
					
        				if($post['publish'] == 0){
        				    $published = 0;
        				}else{
        				    $published = 1;
        				}	
			            //===========================================Enter data in our component table =========================================================
	
				        $sql = "INSERT INTO #__je_k2itemlist (`itemid`,`userid`,`name`,`email`,`published`) values (".$item_id.",".$user->id.",'".$post['name']."','".$post['email']."','".$published."')"; 
				 
        				$db->setQuery($sql);
        				$db->query();
	
		                //======================================================================================================================================			
					
    					$browse_tempt=$mesg->message;
    					$browse_tempt=str_replace("{created_by}",$post['name'],$browse_tempt);
    					$browse_tempt=str_replace("{email}",$post['email'],$browse_tempt);
    					$browse_tempt=str_replace("{introtext}",$post['title'],$browse_tempt);
    					$browse_tempt=str_replace("{fulltext}",$post['fulltext'],$browse_tempt);
    					$browse_tempt=str_replace("{REMOTE_ADDR}",$_SERVER["REMOTE_ADDR"],$browse_tempt);
    		 
    					$browse_tempt1=$mesg->notify_message;
    					$created_by_alias1 = $my->name;
    					$browse_tempt1=str_replace("{User}",$post['name'],$browse_tempt1);
			
					   if($file['name']!='') {
					       
						$row->p_name= JPath::clean(time().'_'.$file['name']);
						$filetype = strtolower(JFile::getExt($file['name']));//Get extension of the file
						$mylink = JRoute::_('index.php?option='.$option.'&view=product_detail&edit&cid[]='.$post['id']);
						
						if($filetype =='jpg' || $filetype=='jpeg' || $filetype =='png' || $filetype =='gif'){
						        
					    $handle = new Upload($file);
						$handle->allowed = array('image/*');
						
			            if ($handle->uploaded) {

            				//Image params
            				$category = &JTable::getInstance('K2Category', 'Table');
            				$category->load($post['catid']);
            				//$category->load($row->catid);
            				$cparams = new JParameter($category->params);

            				if ($cparams->get('inheritFrom')) {
            					$masterCategoryID = $cparams->get('inheritFrom');
            					$query = "SELECT * FROM #__k2_categories WHERE id=".(int)$masterCategoryID;
            					$db->setQuery($query, 0, 1);
            					$masterCategory = $db->loadObject();
            					$cparams = new JParameter($masterCategory->params);
            				}

				            $params->merge($cparams);

            				//Original image
            				$savepath = JPATH_SITE.'/'.'media'.'/'.'k2'.'/'.'items'.'/'.'src';
            				$handle->image_convert = 'jpg';
            				$handle->jpeg_quality = 100;
            				$handle->file_auto_rename = false;
            				$handle->file_overwrite = true;
            				$handle->file_new_name_body = md5("Image".$item_id);
            				$handle->Process($savepath);
            
            				$filename = $handle->file_dst_name_body;
            				$savepath = JPATH_SITE.'/'.'media'.'/'.'k2'.'/'.'items'.'/'.'cache';
            
            				//XLarge image
            				$handle->image_resize = true;
            				$handle->image_ratio_y = true;
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
            				$handle->image_x = $imageWidth;
            				$handle->Process($savepath);
            
            				//Large image
            				$handle->image_resize = true;
            				$handle->image_ratio_y = true;
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
            				$handle->Process($savepath);
            
            				//Medium image
            				$handle->image_resize = true;
            				$handle->image_ratio_y = true;
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
            				$handle->Process($savepath);
            
            				//Small image
            				$handle->image_resize = true;
            				$handle->image_ratio_y = true;
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
            				$handle->Process($savepath);
            
            				//XSmall image
            				$handle->image_resize = true;
            				$handle->image_ratio_y = true;
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
            				$handle->Process($savepath);
            
            				//Generic image
            				$handle->image_resize = true;
            				$handle->image_ratio_y = true;
            				$handle->image_convert = 'jpg';
            				$handle->jpeg_quality = $params->get('imagesQuality');
            				$handle->file_auto_rename = false;
            				$handle->file_overwrite = true;
            				$handle->file_new_name_body = $filename.'_Generic';
            				$imageWidth = $params->get('itemImageGeneric', '300');
            				$handle->image_x = $imageWidth;
            				$handle->Process($savepath);
            
            				if($files['image']['error'] === 0)
            				    $handle->Clean();
            				} 
							
							
						} else {
							$msg = JText::_ ( 'PLEASE_UPLOAD_VALID_DOCUMENT_FILE' );
							$mainframe->redirect( $mylink,$msg );	
						}
					}
					
					if ($mesg->notify) {
					    	
						$config		= &JFactory::getConfig();
						$from		= $post['email']; 
						$fromname	= $post['name'];		
						$subject 	= "New story - $title";
						$created_by_alias = 'Admin'; 
						JUtility::sendMail($from, $fromname,$mesg->notify_email, $subject, $browse_tempt, $mode=1);//mail go to admin
						JUtility::sendMail($mesg->notify_email, $created_by_alias,$from, $subject, $browse_tempt1, $mode=1);//msg user
					}
				
					if($mesg->pageurl=='0') {
					    
						$redir_link	= JRoute::_('index.php');
                        
					} else {
						$k2myitem	= $model->getk2item($mesg->pageurl);
						$redir_link	= JRoute::_('index.php?option=com_k2&view=item&id='.$k2myitem->id.':'.$k2myitem->alias);
					}
                    
					$msg    = JText::_( 'SUCCESS');
					$this->setRedirect ($redir_link, $msg);
					
				} else {
				    
					$msg=JText::_( 'PLEASE_ENTER_CORRECT_CODE_GIVEN_IN_IMAGE' );
					$_SESSION['name']	=	$post['name'];
					$_SESSION['email']	=	$post['email'];
					$_SESSION['title']	=	$post['title'];
					$_SESSION['fulltext']	=	$post['fulltext'];
					$link	= JRoute::_('index.php?option='.$option.'&view=jesubmit&ses=1');
					$this->setRedirect ($link,$msg);
				}
			}
		}
	}
	
	function getExtrafield()
	{
		$catid =& JRequest::getVar('did', '', '', 'int' );
		$itemid =& JRequest::getVar('itemid', '', '', 'int' );
		$db= & JFactory :: getDBO();
		$query = 'SELECT extraFieldsGroup FROM #__k2_categories WHERE id='.$catid;
		$db->setQuery( $query );
		$res=$db->loadObject();
		
		$k2admin_path = str_replace('com_jek2story','com_k2',JPATH_COMPONENT_ADMINISTRATOR);
		//define (JPATH_COMPONENT_ADMINISTRATOR,$k2admin_path);

		require_once($k2admin_path.'/'.'models'.'/'.'extrafield.php');
		$extraFieldModel= new K2ModelExtraField;
		//if($item->id)
			$extraFields = $extraFieldModel->getExtraFieldsByGroup($res->extraFieldsGroup);
		//else $extraFields = NULL;

//debug($extraFields);
		for($i=0; $i< sizeof($extraFields); $i++){
		    
			if($itemid){
			     $extraFields[$i]->element= $extraFieldModel->renderExtraField($extraFields[$i],$itemid);
			} else {
			     $extraFields[$i]->element= $extraFieldModel->renderExtraField($extraFields[$i]);
            }
			
		}
       
		//$extra_data ='<table>';
		
 		foreach ($extraFields as $extraField){
 		   
			$extra_data .='<tr>';
			$extra_data .='<td><label align="left">'.JText::_($extraField->name).'</label></td></tr><tr>';
			$extra_data .='<td>'.$extraField->element.'</td>';
			$extra_data .='</tr>';
		}
		//$extra_data .='</table>';
		echo $extra_data;
		exit;
	}
	
	//====================== New Captcha Code ==============================//		
	function captchacr() {
		//require_once(JPATH_COMPONENT.DS."helpers/kcaptcha/kcaptcha.php");
		@session_start();
		$captcha = new KCAPTCHA();
		$_SESSION['comments-captcha-code'] = $captcha->getKeyString();
		exit;
	}
	//====================== EOF New Captcha Code ==========================//
	//+++++++++++++++++++++++++++++++ Ajax Captcha Code +++++++++++++++++++++++++++++++++++++++++++++++ //
	function refresh_captchacr() {
		$option = JRequest::getVar('option','','request','string');
		$uri =& JURI::getInstance();
		$url= $uri->root();
			
		$dest = $url.'index.php?option=com_jek2story&view=jesubmit&task=captchacr&tmpl=component&ac='.rand();
		echo '<img src="'.$dest.'" />';
		exit;
	}
	//++++++++++++++++++++++++++++++ EOF Ajax Captcha Code +++++++++++++++++++++++++++++++++++++++++++ //
	
	function processImage($itemId, $catId, $file) { 
	    
        $filetype = strtolower(JFile::getExt($file['name']));
        $db = JFactory::getDBO();
        $params = &JComponentHelper::getParams('com_k2');
        
        if( $filetype == 'jpg' || $filetype == 'jpeg' || $filetype == 'png' || $filetype == 'gif') {
                            
            $handle = new Upload($file);
            $handle->allowed = array('image/*');
       
            if ($handle->uploaded) {
                    
                //Image params
                $category = &JTable::getInstance('K2Category', 'Table');
                $category->load($catId);
                
                $cparams = json_decode($category->params);
    
                if ($cparams->inheritFrom) {
                    
                    $masterCategoryID = $cparams->inheritFrom;
                    $query = "SELECT * FROM #__k2_categories WHERE id=".(int)$masterCategoryID;
                    $db->setQuery($query, 0, 1);
                    $masterCategory = $db->loadObject();
                    $cparams = json_decode($masterCategory->params);
                }
    
                $params->merge($cparams);

                //Original image
                $savepath = JPATH_SITE.'/'.'media'.'/'.'k2'.'/'.'items'.'/'.'src';
                $handle->image_convert = 'jpg';
                $handle->jpeg_quality = 100;
                $handle->file_auto_rename = false;
                $handle->file_overwrite = true;
                $handle->file_new_name_body = md5("Image".$itemId);
                $handle->Process($savepath);
    
                $filename = $handle->file_dst_name_body;
                
                $savepath = JPATH_SITE.'/'.'media'.'/'.'k2'.'/'.'items'.'/'.'cache';
    
                //XLarge image
                $handle->image_resize = true;
                $handle->image_ratio_y = true;
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
                $handle->image_x = $imageWidth;
                $handle->Process($savepath);
    
                //Large image
                $handle->image_resize = true;
                $handle->image_ratio_y = true;
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
                $handle->Process($savepath);
    
                //Medium image
                $handle->image_resize = true;
                $handle->image_ratio_y = true;
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
                $handle->Process($savepath);
    
                //Small image
                $handle->image_resize = true;
                $handle->image_ratio_y = true;
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
                $handle->Process($savepath);
    
                //XSmall image
                $handle->image_resize = true;
                $handle->image_ratio_y = true;
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
                $handle->Process($savepath);
    
                //Generic image
                $handle->image_resize = true;
                $handle->image_ratio_y = true;
                $handle->image_convert = 'jpg';
                $handle->jpeg_quality = $params->get('imagesQuality');
                $handle->file_auto_rename = false;
                $handle->file_overwrite = true;
                $handle->file_new_name_body = $filename.'_Generic';
                $imageWidth = $params->get('itemImageGeneric', '300');
                $handle->image_x = $imageWidth;
                $handle->Process($savepath);
    
                if($files['image']['error'] === 0){
                    $handle->Clean();
                } 
                 
                return $savepath;

            }   
    
        } 
            
        return false;   
	}
	
}
?>