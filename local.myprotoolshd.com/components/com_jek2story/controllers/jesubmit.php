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
    
  
    function save(){
        
        set_time_limit(0);
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
        
        if(isset($_POST['facilityName']) && !empty($_POST['artistName'])){
             $_POST['title'] =  $_POST['facilityName'].' — '. $_POST['artistName'];
        } else {
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
        
        $cap        =   $_SESSION['comments-captcha-code'];
        $textval    = $post['cap'];
        
        //editing mode NOT USED NOW 
        if($post['k2itemid']){
          
        //adding mode    
        } else {
            
            //adds data in the session
            $_SESSION['story'] = array();
            foreach($_POST as $k => $v){
                $_SESSION['story'][$k] = $v;
            }
            
          
             //check all fields?
             if(empty($post['email']) || empty($post['name']) || empty($post['title'])){
                  $msg = JText::_( 'FILL_ALL_FIELDS');
                  $mainframe->redirect(JRoute::_('index.php?option='.$option.'&view=jesubmit'), $msg,'error');  
             }
             
             //hack to split emails
             $emailsArray = preg_split('/\s*[,|;]\s*/', trim($post['email']));
             
             if (filter_var($emailsArray[0], FILTER_VALIDATE_EMAIL) == false){
                     
                 $msg = JText::_( 'VALID_EMAIL');
                 $mainframe->redirect(JRoute::_('index.php?option='.$option.'&view=jesubmit'), $msg, 'error');
                 
                 if(isset($emailsArray[1]) && filter_var($emailsArray[1], FILTER_VALIDATE_EMAIL) == false){
                        
                    $msg = JText::_( 'VALID_EMAIL');
                    $mainframe->redirect(JRoute::_('index.php?option='.$option.'&view=jesubmit'), $msg, 'error');
                 }
             }
             
             
             //reassign emails
             $post['email'] = $emailsArray[0];
             $destEmail = isset($emailsArray[1]) ? $emailsArray[1] : $emailsArray[0];
             
                        
            if($cap == $textval) { 
                
                $mosConfig_live_site=  substr_replace(JURI::root(), '', -1, 1); 
               
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
                
                //language
                $language = "*";
                $country = isset($post['K2ExtraField_8']) ? $post['K2ExtraField_8'] : null;
               
                global $countryMatrix;
                
                require_once('libraries/joomla/language/helper.php');
                $languages  = JLanguageHelper::getLanguages();
                $activeLanguage = JFactory::getLanguage()->getTag();
                $languageId = array();
                
                foreach ($countryMatrix as $countryId => $cArray){ 
                    foreach ($cArray as $v){
                        if($v == $country){
                            $languageId[] = $countryId;
                           
                        }
                    }
                }
           
                foreach ($languages as $k => $v){
                    if($languageId[0] == $v->lang_id){
                        $language = $v->lang_code;
                        continue;
                    }
                }
               
                //test if duplicate emails
                $q = "SELECT * FROM jos_users WHERE email = '".$post['email']."'";
                $db->setQuery($q);
                $db->query();
                $potUser = $db->loadObject();
                if(!empty($potUser)){
                    $msg = JText::_ ( 'EMAIL_ALREADY_EXISTS' );
                    $mainframe->redirect(JRoute::_('index.php?option='.$option.'&view=jesubmit'), $msg,'error');
                }
                
 
                //K2 items
                $sql = "INSERT INTO #__k2_items ".
                       "(`title`,`alias`,`catid`,`published`,`introtext`,`fulltext`,`extra_fields`,`extra_fields_search`,`created`,`created_by`,`publish_up`,`access`,`language`, `plugins`) ".
                       "values ('".addslashes($post['title'])."','".addslashes($post['title'])."',".addslashes($post['catid']).",".addslashes($post['publish']).",'".addslashes($post['fulltext'])."',".
                       "'".addslashes($myfulltext)."','".addslashes($field_data)."','".addslashes($field_search)."','".$created."', 0,'".$created."','1','".addslashes($language)."','".addslashes($plugin)."')"; 
                       
                $db->setQuery($sql);
                $db->query();
                $item_id = $db->insertid();
                
                //test if there is a file and processes it
                if($file['name'] != '') {
                        
                    $row->p_name = JPath::clean(time().'_'.$file['name']); 
                    $filetype = strtolower(JFile::getExt($file['name']));
                   
                    if(!$this->processImage($item_id, $post['catid'], $file)){
                       
                        $msg = JText::_ ( 'PLEASE_UPLOAD_VALID_DOCUMENT_FILE' );
                        $mainframe->redirect(JRoute::_('index.php?option='.$option.'&view=jesubmit'), $msg,'error');
                    }    
                    
                } else {
                    
                     $msg = JText::_ ( 'PLEASE_UPLOAD_VALID_DOCUMENT_FILE' );
                     $mainframe->redirect(JRoute::_('index.php?option='.$option.'&view=jesubmit'), $msg,'error');
                }
                
                //Joomla user
                $user->name = $post['title'];
                $user->username = $post['name'];
                
                $newUser = new stdClass;
                $newUser->name = $post['title'];
                $newUser->username = $user->username;
                $newUser->email = $post['email'];
                $newUser->password = 'password';
                $newUser->registerDate = date('Y-m-d H:i:s');
                $newUser->lastvisitDate = date('Y-m-d H:i:s');
                
                $db->insertObject('jos_users',$newUser,'id');
                $newUserId = $db->insertid();
                $user->id = $newUserId;
                
                //if image processing was ok, we update the k2items with the userId
                $q = "UPDATE #__k2_items SET created_by = $user->id WHERE id = $item_id";
                $db->setQuery($q);
                $db->query();
 
                //K2 User
                $newK2User = new stdClass;
                $newK2User->userID = $user->id;
                $newK2User->userName = $user->username;
                $newK2User->gender = 'm';
                $newK2User->group = 1;
                $db->insertObject('jos_k2_users',$newK2User,'id');
                $newK2UserId = $db->insertid();
              
                //joomla usergroup map
                $sql = "INSERT INTO #__user_usergroup_map (`user_id`,`group_id`) values ($user->id, 2)";
                $db->setQuery($sql);
                $db->query();
                
                //insert in k2story db
                $published = $post['publish'] == 0 ? 0 : 1; 
                
                $sql = "INSERT INTO #__je_k2itemlist (`itemid`,`userid`,`name`,`email`,`published`)".
                       " values (".$item_id.",".$user->id.",'".addslashes($post['name'])."','".$post['email']."','".$published."')";  
                
                $db->setQuery($sql);
                $db->query();
                
                //emails
                if ($mesg->notify) {
                    
                    $previewLink =  'http://'.$_SERVER['HTTP_HOST'].'/'.ltrim(JRoute::_("index.php?option=com_k2&view=item&id=$item_id&lang=en&preview=1"),'/');
                    $userLink = 'http://'.$_SERVER['HTTP_HOST'].JRoute::_('index.php?option=com_users&view=login');
                    //to admin
                    $browse_tempt = $mesg->message;
                    $browse_tempt = str_replace("{created_by}", $post['name'], $browse_tempt);
                    $browse_tempt = str_replace("{email}", $post['email'], $browse_tempt);
                    $browse_tempt = str_replace("{introtext}", $post['title'], $browse_tempt);
                    $browse_tempt = str_replace("{fulltext}", $post['fulltext'], $browse_tempt);
                    $browse_tempt = str_replace("{REMOTE_ADDR}", $post['address'], $browse_tempt);
                    $browse_tempt = str_replace("{preview}", $previewLink, $browse_tempt);
                   
                    //to user
                    $browse_tempt1 = JText::_('NOTIFY_USER_EMAIL');
                   
                    $created_by_alias1 = isset($user->name) ? $user->name : $post['name'];
                    $browse_tempt1 =str_replace(array("{User}","{user}"), $user->username, $browse_tempt1);
                    $browse_tempt1 = str_replace(array("{Preview}","{preview}"), $previewLink, $browse_tempt1);
                    $browse_tempt1 =str_replace(array("{Login}","{login}"), $userLink, $browse_tempt1);
                      
                    $config     = &JFactory::getConfig();
                    $from       = $post['email']; 
                    $fromname   = $post['name'];        
                    $subject    = "New story - $title";
                    $created_by_alias = 'Admin'; 
                   
                    JFactory::getMailer()->sendMail($from               , $fromname         , $mesg->notify_email , $subject, $browse_tempt , $mode=1); //mail go to admin
                    JFactory::getMailer()->sendMail($mesg->notify_email , $created_by_alias , $destEmail          , $subject, $browse_tempt1, $mode=1); // User msg
                }
                
                
                if($mesg->pageurl == '0') {
                    
                    $redir_link = JRoute::_('index.php');
                    
                } else {
                    
                    $k2myitem   = $model->getk2item($mesg->pageurl);
                    $redir_link = JRoute::_('index.php?option=com_k2&view=item&id='.$k2myitem->id.':'.$k2myitem->alias);
                }
                
                $msg = JText::_( 'SUCCESS');
                $msg = JText::_( 'STORY_SAVED');
                $_SESSION['story'] = array();
                
                $this->setRedirect ($redir_link, $msg);
                
            } //endig Adding mode
            
            return;
        } // if cap // no text val
        
        $msg = JText::_( 'NO_CAP');
        $this->setRedirect ($redir_link, $msg);
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
			$extra_data .='<td><label id="label_K2ExtraField_'.JText::_($extraField->id).'" align="left">'.JText::_($extraField->name).'</label></td></tr><tr>';
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

    function test(){
        debug(JRoute::_('index.php?option=com_users&view=login'));
    }
	
}
?>