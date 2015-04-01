<?php
/**
 * @package K2BaseHelper
 * @subpackage SmartAddons.Helper
 * @version 2.5
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2012 YouTech Company. All Rights Reserved.
 * @author YouTech Company http://www.smartaddons.com
 *
 */
defined('_JEXEC') or die;

JLoader::register('ImageHelper', dirname(__FILE__).'/helper_image.php');
require_once (JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'helpers'.DS.'route.php');
if (!class_exists('K2BaseHelperResponsiveContent')){
	/**
	 * BaseHelper for com_k2 only.
	 */
	abstract class K2BaseHelperResponsiveContent{
		/**
		 * Cache all image path or url
		 * @var array
		 */
		protected static $image_cache = array();



		/**
		 * strips all tag, except a, em, strong
		 * @param string $text
		 * @return string
		*/
		public function _cleanText($text){
			$text = str_replace('<p>', ' ', $text);
			$text = str_replace('</p>', ' ', $text);
			$text = strip_tags($text, '<a><em><strong>');
			$text = trim($text);
			return $text;
		}

		/**
		 * Parse and build target attribute for links.
		 * @param string $value (_self, _blank, _windowopen, _modal)
		 * _blank 	Opens the linked document in a new window or tab
		 * _self 	Opens the linked document in the same frame as it was clicked (this is default)
		 * _parent 	Opens the linked document in the parent frame
		 * _top 	Opens the linked document in the full body of the window
		 * _windowopen  Opens the linked document in a Window
		 * _modal		Opens the linked document in a Modal Window
		 */
		public static function parseTarget($type='_self'){
			$target = '';
			switch($type){
				default:
				case '_self':
					break;
				case '_blank':
				case '_parent':
				case '_top':
					$target = 'target="'.$type.'"';
					break;
				case '_windowopen':
					$target = "onclick=\"window.open(this.href,'targetWindow','toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,false');return false;\"";
					break;
				case '_modal':
					// user process
					break;
			}
			return $target;
		}

		/**
		 * Truncate string by $length
		 * @param string $string
		 * @param int $length
		 * @param string $etc
		 * @return string
		 */
		public static function truncate($string, $length, $etc='...'){
			return defined('MB_OVERLOAD_STRING')
			? self::_mb_truncate($string, $length, $etc)
			: self::_truncate($string, $length, $etc);
		}

		/**
		 * Truncate string if it's size over $length
		 * @param string $string
		 * @param int $length
		 * @param string $etc
		 * @return string
		 */
		private static function _truncate($string, $length, $etc='...'){
			if ($length>0 && $length<strlen($string)){
				$buffer = '';
				$buffer_length = 0;
				$parts = preg_split('/(<[^>]*>)/', $string, -1, PREG_SPLIT_DELIM_CAPTURE);
				$self_closing_tag = split(',', 'area,base,basefont,br,col,frame,hr,img,input,isindex,link,meta,param,embed');
				$open = array();

				foreach($parts as $i => $s){
					if( false===strpos($s, '<') ){
						$s_length = strlen($s);
						if ($buffer_length + $s_length < $length){
							$buffer .= $s;
							$buffer_length += $s_length;
						} else if ($buffer_length + $s_length == $length) {
							if ( !empty($etc) ){
								$buffer .= ($s[$s_length - 1]==' ') ? $etc : " $etc";
							}
							break;
						} else {
							$words = preg_split('/([^\s]*)/', $s, - 1, PREG_SPLIT_DELIM_CAPTURE);
							$space_end = false;
							foreach ($words as $w){
								if ($w_length = strlen($w)){
									if ($buffer_length + $w_length < $length){
										$buffer .= $w;
										$buffer_length += $w_length;
										$space_end = (trim($w) == '');
									} else {
										if ( !empty($etc) ){
											$more = $space_end ? $etc : " $etc";
											$buffer .= $more;
											$buffer_length += strlen($more);
										}
										break;
									}
								}
							}
							break;
						}
					} else {
						preg_match('/^<([\/]?\s?)([a-zA-Z0-9]+)\s?[^>]*>$/', $s, $m);
						//$tagclose = isset($m[1]) && trim($m[1])=='/';
						if (empty($m[1]) && isset($m[2]) && !in_array($m[2], $self_closing_tag)){
							array_push($open, $m[2]);
						} else if (trim($m[1])=='/') {
							$tag = array_pop($open);
							if ($tag != $m[2]){
								// uncomment to to check invalid html string.
								// die('invalid close tag: '. $s);
							}
						}
						$buffer .= $s;
					}
				}
				// close tag openned.
				while(count($open)>0){
					$tag = array_pop($open);
					$buffer .= "</$tag>";
				}
				return $buffer;
			}
			return $string;
		}

		/**
		 * Truncate mutibyte string if it's size over $length
		 * @param string $string
		 * @param int $length
		 * @param string $etc
		 * @return string
		 */
		private static function _mb_truncate($string, $length, $etc='...'){
			$encoding = mb_detect_encoding($string);
			if ($length>0 && $length<mb_strlen($string, $encoding)){
				$buffer = '';
				$buffer_length = 0;
				$parts = preg_split('/(<[^>]*>)/', $string, -1, PREG_SPLIT_DELIM_CAPTURE);
				$self_closing_tag = explode(',', 'area,base,basefont,br,col,frame,hr,img,input,isindex,link,meta,param,embed');
				$open = array();

				foreach($parts as $i => $s){
					if (false === mb_strpos($s, '<')){
						$s_length = mb_strlen($s, $encoding);
						if ($buffer_length + $s_length < $length){
							$buffer .= $s;
							$buffer_length += $s_length;
						} else if ($buffer_length + $s_length == $length) {
							if ( !empty($etc) ){
								$buffer .= ($s[$s_length - 1]==' ') ? $etc : " $etc";
							}
							break;
						} else {
							$words = preg_split('/([^\s]*)/', $s, -1, PREG_SPLIT_DELIM_CAPTURE);
							$space_end = false;
							foreach ($words as $w){
								if ($w_length = mb_strlen($w, $encoding)){
									if ($buffer_length + $w_length < $length){
										$buffer .= $w;
										$buffer_length += $w_length;
										$space_end = (trim($w) == '');
									} else {
										if ( !empty($etc) ){
											$more = $space_end ? $etc : " $etc";
											$buffer .= $more;
											$buffer_length += mb_strlen($more);
										}
										break;
									}
								}
							}
							break;
						}
					} else {
						preg_match('/^<([\/]?\s?)([a-zA-Z0-9]+)\s?[^>]*>$/', $s, $m);
						//$tagclose = isset($m[1]) && trim($m[1])=='/';
						if (empty($m[1]) && isset($m[2]) && !in_array($m[2], $self_closing_tag)){
							array_push($open, $m[2]);
						} else if (trim($m[1])=='/') {
							$tag = array_pop($open);
							if ($tag != $m[2]){
								// uncomment to to check invalid html string.
								// die('invalid close tag: '. $s);
							}
						}
						$buffer .= $s;
					}
				}
				// close tag openned.
				while(count($open)>0){
					$tag = array_pop($open);
					$buffer .= "</$tag>";
				}
				return $buffer;
			}
			return $string;
		}

		/**
		 * First image in list of images for K2 Item
		 * @param object $item is an Item of K2
		 * @param JRegistry $params
		 * @return string
		 */
		public static function getK2Image($item, $params, $prefix='imgcfg'){
			$images = &self::getK2Images($item, $params, $prefix);
			return is_array($images) && count($images) ? $images[0] : null;
		}

		/**
		 *
		 * @param unknown_type $item
		 * @param unknown_type $params
		 */
		public static function getK2Images($item, $params, $prefix='imgcfg'){
			$hash = md5( serialize(array($params, 'article')) );
			if ( !isset(self::$image_cache[$hash][$item->id]) ){
				$defaults = array(
						'external'			=> 1,
						'k2_image'			=> 1,
						'inline_description'=> 1,
						'inline_introtext'	=> 1,
						'inline_fulltext'	=> 1
				);

				$images_path = array();
				$priority = preg_split('/[\s|,|;]/', $params->get($prefix.'_order', 'k2_image, inline_description, external, image_intro,inline_introtext,image_fulltext,inline_fulltext'), -1, PREG_SPLIT_NO_EMPTY);
				if ( count($priority) > 0 ){
					$priority = array_map('strtolower', $priority);
					$mark = array();

					for($i=0; $i<count($priority); $i++){
						$type = $priority[$i];
						if ( array_key_exists($type, $defaults) )
							unset($defaults[ $type ]);
						if ( $params->get($prefix.'_from_'.$type, 1) )
							$mark[ $type ] = 1;
					}
				}

				foreach($defaults as $type => $val){
					if ( $params->get($prefix.'_from_'.$type, 0) )
						$mark[ $type ] = 1;
				}

				if ( count($mark) > 0 ){
					// prepare data.
					$iparams = null;
					if (array_key_exists('k2_image', $mark)){
						$suffix_map = array(
								'XSmall' => '_XS.jpg',
								'Small' => '_S.jpg',
								'Medium' => '_M.jpg',
								'Large' => '_L.jpg',
								'XLarge' => '_XL.jpg',
								'Generic' => '_Generic.jpg',
						);
					}

					foreach($mark as $type => $true){
						switch ($type){
							case 'k2_image':
								$imgcfg_size = $params->get($prefix.'_k2_image_size', '');
								if (!empty($imgcfg_size) && isset($suffix_map[$imgcfg_size])){
									$attrib = 'image'.$imgcfg_size;
									$img_path = JPATH_SITE.'/media/k2/items/cache/';
									$img_suffix = $suffix_map[$imgcfg_size];
									$img_hash = md5('Image'.$item->id);
									if (file_exists($img_path.$img_hash.$img_suffix)){
										$image = array(
												'src' => $img_path.$img_hash.$img_suffix,
										);
										array_push($images_path, $image);
									}
								} else {
									$img_path = JPATH_SITE.'/media/k2/items/src/';
									$img_hash = md5('Image'.$item->id);
									$img_suffix = '.jpg';
									if (file_exists($img_path.$img_hash.$img_suffix)){
										$image = array(
												'src' => $img_path.$img_hash.$img_suffix,
										);
										array_push($images_path, $image);
									}
								}
								break;
							case 'inline_description':
								//$text = $item->description;
							case 'inline_introtext':
								$text = $item->introtext;
							case 'inline_fulltext':
								if ($type == 'inline_fulltext'){
									$text = $item->fulltext;
								}
								$inline_images = self::getInlineImages($text);
								for ($i=0; $i<count($inline_images); $i++){
									array_push($images_path, $inline_images[$i]);
								}
								break;
									
							case 'external':
								$exf = $params->get($prefix.'_external_url', 'images/article/{id}/');
								preg_match_all('/{([a-zA-Z0-9_]+)}/', $exf, $m);
								if ( count($m)==2 && count($m[0])>0 ){
									$compat = 1;
									foreach ($m[1] as $property){
										!property_exists($item, $property) && ($compat=0);
									}
									if ($compat){
										$replace = array();
										foreach ($m[1] as $property){
											$replace[] = is_null($item->$property) ? '' : $item->$property;
										}
										$exf = str_replace($m[0], $replace, $exf);
									}
								}
								$files = self::getExternalImages($exf);
								for ($i=0; $i<count($files); $i++){
									array_push($images_path, array('src'=>$files[$i]));
								}
								break;
							default:
								break;
						}
					}
				}
					
				if ( count($images_path) == 0 && $params->get($prefix.'_placeholder', 1)==1){
					$images_path[] = array('src'=> $params->get($prefix.'_placeholder_path', 'modules/mod_sj_k2_responsive_content/assets/images/nophoto.png'), 'class'=>'placeholder');
				}
					
				self::$image_cache[$hash][$item->id] = $images_path;
			}
			return self::$image_cache[$hash][$item->id];
		}

		public static function getK2CImage($item, $params, $prefix='imgcfg'){
			$images = &self::getK2CImages($item, $params, $prefix);
			return is_array($images) && count($images) ? $images[0] : null;
		}

		public static function getK2CImages($item, $params, $prefix='imgcfg'){
			$hash = md5( serialize(array($params, 'category')) );
			if ( !isset(self::$image_cache[$hash][$item->id]) ){
				$defaults = array(
						'external'		=> 1,
						'k2_image'	=> 1,
						'description'	=> 1
				);
				$images_path = array();
				$priority = preg_split('/[\s|,|;]/', $params->get($prefix.'_order', 'external, params, description'), -1, PREG_SPLIT_NO_EMPTY);
				if ( count($priority) > 0 ){
					$priority = array_map('strtolower', $priority);
					$mark = array();

					for($i=0; $i<count($priority); $i++){
						$type = $priority[$i];
						if ( array_key_exists($type, $defaults) )
							unset($defaults[ $type ]);
						if ( $params->get($prefix.'_from_'.$type, 1) )
							$mark[ $type ] = 1;
					}
				}
				foreach($defaults as $type => $val){
					if ( $params->get($prefix.'_from_'.$type, 1) )
						$mark[ $type ] = 1;
				}
				if ( count($mark) > 0 ){
					$cparams = null;
					if (array_key_exists('params', $mark)){
						$cparams = new JRegistry;
						$cparams->loadString($item->params);
					}

					foreach($mark as $type => $true){
						switch ($type){
							case 'k2_image':
								if ( $cparams instanceof JRegistry && $cparams->get('image') ){
									$image = array(
											'src' => $cparams->get('image')
									);
									array_push($images_path, $image);
								}
								break;
							case 'description':
								$inline_images = self::getInlineImages($item->description);
								for ($i=0; $i<count($inline_images); $i++){
									array_push($images_path, $inline_images[$i]);
								}
								break;
									
							case 'external':
								$exf = $params->get($prefix.'_external_url', 'images/category/{id}/');
								preg_match_all('/{([a-zA-Z0-9_]+)}/', $exf, $m);
								if ( count($m)==2 && count($m[0])>0 ){
									$compat = 1;
									foreach ($m[1] as $property){
										!property_exists($item, $property) && ($compat=0);
									}
									if ($compat){
										$replace = array();
										foreach ($m[1] as $property){
											$replace[] = is_null($item->$property) ? '' : $item->$property;
										}
										$exf = str_replace($m[0], $replace, $exf);
									}
								}
								$files = self::getExternalImages($exf);
								for ($i=0; $i<count($files); $i++){
									array_push($images_path, array('src'=>$files[$i]));
								}
								break;
							default:
								break;
						}
					}
				}
					
				if ( count($images_path) == 0 && $params->get($prefix.'_placeholder', 1)==1){
					$images_path[] = array('src'=> $params->get($prefix.'_placeholder_path', null), 'class'=>'placeholder');
				}
					
				self::$image_cache[$hash][$item->id] = $images_path;
			}
			return self::$image_cache[$hash][$item->id];
		}


		/**
		 * Get all image url|path in $text.
		 * @param string $text
		 * @return string
		 */
		public static function getInlineImages($text){
			$images = array();
			$searchTags = array(
					'img'	=> '/<img[^>]+>/i',
					'input'	=> '/<input[^>]+type\s?=\s?"image"[^>]+>/i'
			);
			foreach ($searchTags as $tag => $regex){
				preg_match_all($regex, $text, $m);
				if ( is_array($m) && isset($m[0]) && count($m[0])){
					foreach ($m[0] as $htmltag){
						$tmp = JUtility::parseAttributes($htmltag);
						if ( isset($tmp['src']) ){
							if ($tag == 'input'){
								array_push( $images, array('src' => $tmp['src']) );
							} else {
								array_push( $images, $tmp );
							}
						}
					}
				}
			}
			return $images;
		}

		/**
		 *
		 * @param string $path
		 * @return multitype:multitype:unknown  |Ambigous <multitype:, boolean, multitype:unknown multitype:unknown  >
		 */
		public static function getExternalImages($path){
			jimport('joomla.filesystem.folder');
			$files = array();

			// check if $path is url
			$path = trim($path);
			$isHttp = stripos($path, 'http') === 0;
			if ($isHttp){
				if ( !JUri::isInternal($path) ){
					// is external, test if is valid
					if ( version_compare(JVERSION, '3.0.0', '>=') ){
						// is Joomla 3
						$http = JHttpFactory::getHttp();
						$head = $http->head($path);
						if ($head->code == 200 || $head->code == 302 || $head->code == 304){
							// is valid url
							if (preg_match('/image/', $head->headers['Content-Type'])){
								// is true image
								$files[] = $path;
							}
						}
					} else {
						// for Joomla 3 older
						$files[] = $path;
					}
					if (!count($files)){
						//var_dump('Url is not valid');
					}
					return $files;
				} else {
					$uri = JUri::getInstance($path);
					$uri_path = (string)$uri->getPath();
					$uri_base = (string)JURI::base(true);
					if (stripos($uri_path, $uri_base)===0 && ($baselen = strlen($uri_base))){
						$uri_path = substr($uri_path, $baselen);
					}
					$path = JPATH_BASE.$uri_path;
				}
			}

			if ( ($realpath = realpath($path))===false ){
				//var_dump('File or Folder is not exists!');
				return $files;
			}

			if ( is_file($realpath) ){
				$files[] = $realpath;
			} else if ( is_dir($realpath) ){
				$files = JFolder::files($path, '.jpg|.png|.gif', false, true);
			}
			return $files;
		}

		/**
		 * Get an image helper object
		 * @param string $image - path or url of image
		 * @param array $options
		 * @return ImageHelper
		 */
		public static function getImageHelper($image, $options=array()){
			return ImageHelper::init($image, $options);
		}

		/**
		 * Resize and return image tag (<img .../>)
		 * @param string $image - path or url of image
		 * @param array $options
		 * @return string
		 */
		public static function imageTag($image, $options=array()){
			return ImageHelper::init($image, $options)->tag();
		}

		/**
		 * Resize and return image src
		 * @param string $image - path or url of image
		 * @param array $options
		 * @return string
		 */
		public static function imageSrc($image, $options=array()){
			return ImageHelper::init($image, $options)->src();
		}


		public static function getItems($cid, &$params)
		{

			jimport('joomla.filesystem.file');
			$mainframe = JFactory::getApplication();
			$limit = $params->get('itemCount', 5);
			$start = $limit*(JRequest::getInt('page',1)-1);
			$ordering = $params->get('itemsOrdering', '');
			$componentParams = JComponentHelper::getParams('com_k2');
			$limitstart = JRequest::getInt('limitstart');

			$user = JFactory::getUser();
			$db = JFactory::getDBO();

			$jnow = JFactory::getDate();
			$now =  $jnow->toSql();
			$nullDate = $db->getNullDate();

				if ($params->get('source') == 'specific')
		{

			$value = $params->get('items');
			$current = array();
			if (is_string($value) && !empty($value))
				$current[] = $value;
			if (is_array($value))
				$current = $value;

			$items = array();

			foreach ($current as $id)
			{

				$query = "SELECT i.*, c.name AS categoryname,c.id AS categoryid, c.alias AS categoryalias, c.params AS categoryparams 
				FROM #__k2_items as i 
				LEFT JOIN #__k2_categories c ON c.id = i.catid 
				WHERE i.published = 1 ";
				if (K2_JVERSION != '15')
				{
					$query .= " AND i.access IN(".implode(',', $user->getAuthorisedViewLevels()).") ";
				}
				else
				{
					$query .= " AND i.access<={$aid} ";
				}
				$query .= " AND i.trash = 0 AND c.published = 1 ";
				if (K2_JVERSION != '15')
				{
					$query .= " AND c.access IN(".implode(',', $user->getAuthorisedViewLevels()).") ";
				}
				else
				{
					$query .= " AND c.access<={$aid} ";
				}
				$query .= " AND c.trash = 0 
				AND ( i.publish_up = ".$db->Quote($nullDate)." OR i.publish_up <= ".$db->Quote($now)." ) 
				AND ( i.publish_down = ".$db->Quote($nullDate)." OR i.publish_down >= ".$db->Quote($now)." ) 
				AND i.id={$id}";
				if (K2_JVERSION != '15')
				{
					if ($mainframe->getLanguageFilter())
					{
						$languageTag = JFactory::getLanguage()->getTag();
						$query .= " AND c.language IN (".$db->Quote($languageTag).", ".$db->Quote('*').") AND i.language IN (".$db->Quote($languageTag).", ".$db->Quote('*').")";
					}
				}
				$db->setQuery($query, $start, $limit);
				 $item = $db->loadObject();
				if ($item)
					$items[] = $item; 

			}
		}
			
			else
			{
				$query = "SELECT i.*, CASE WHEN i.modified = 0 THEN i.created ELSE i.modified END as lastChanged, c.name AS categoryname,c.id AS categoryid, c.alias AS categoryalias, c.params AS categoryparams";

				if ($ordering == 'best')
					$query .= ", (r.rating_sum/r.rating_count) AS rating";

				if ($ordering == 'comments')
					$query .= ", COUNT(comments.id) AS numOfComments";

				$query .= " FROM #__k2_items as i LEFT JOIN #__k2_categories c ON c.id = i.catid";

				if ($ordering == 'best')
					$query .= " LEFT JOIN #__k2_rating r ON r.itemID = i.id";

				if ($ordering == 'comments')
					$query .= " LEFT JOIN #__k2_comments comments ON comments.itemID = i.id";

				$query .= " WHERE i.published = 1 AND i.access IN(".implode(',', $user->getAuthorisedViewLevels()).") AND i.trash = 0 AND c.published = 1 AND c.access IN(".implode(',', $user->getAuthorisedViewLevels()).")  AND c.trash = 0";

				$query .= " AND ( i.publish_up = ".$db->Quote($nullDate)." OR i.publish_up <= ".$db->Quote($now)." )";
				$query .= " AND ( i.publish_down = ".$db->Quote($nullDate)." OR i.publish_down >= ".$db->Quote($now)." )";


				if (!is_null($cid))
				{
					if (is_array($cid))
					{
						if ($params->get('getChildren'))
						{
							$itemListModel = K2Model::getInstance('Itemlist', 'K2Model');
							$categories = $itemListModel->getCategoryTree($cid);
							$sql = @implode(',', $categories);
							$query .= " AND i.catid IN ({$sql})";

						}
						else
						{
							JArrayHelper::toInteger($cid);
							$query .= " AND i.catid IN(".implode(',', $cid).")";
						}

					}
					else
					{
						if ($params->get('getChildren'))
						{
							$itemListModel = K2Model::getInstance('Itemlist', 'K2Model');
							$categories = $itemListModel->getCategoryTree($cid);
							$sql = @implode(',', $categories);
							$query .= " AND i.catid IN ({$sql})";
						}
						else
						{
							$query .= " AND i.catid=".(int)$cid;
						}

					}
				}

				if ($params->get('FeaturedItems') == '0')
					$query .= " AND i.featured != 1";

				if ($params->get('FeaturedItems') == '2')
					$query .= " AND i.featured = 1";

				if ($params->get('videosOnly'))
					$query .= " AND (i.video IS NOT NULL AND i.video!='')";

				if ($ordering == 'comments')
					$query .= " AND comments.published = 1";

				if ($mainframe->getLanguageFilter())
				{
					$languageTag = JFactory::getLanguage()->getTag();
					$query .= " AND c.language IN (".$db->Quote($languageTag).", ".$db->Quote('*').") AND i.language IN (".$db->Quote($languageTag).", ".$db->Quote('*').")";
				}

				switch ($ordering)
				{

					case 'date' :
						$orderby = 'i.created ASC';
						break;

					case 'rdate' :
						$orderby = 'i.created DESC';
						break;

					case 'alpha' :
						$orderby = 'i.title';
						break;

					case 'ralpha' :
						$orderby = 'i.title DESC';
						break;

					case 'order' :
						if ($params->get('FeaturedItems') == '2')
							$orderby = 'i.featured_ordering';
						else
							$orderby = 'i.ordering';
						break;

					case 'rorder' :
						if ($params->get('FeaturedItems') == '2')
							$orderby = 'i.featured_ordering DESC';
						else
							$orderby = 'i.ordering DESC';
						break;

					case 'hits' :
						if ($params->get('popularityRange'))
						{
							$datenow = JFactory::getDate();
							$date =  $datenow->toSql();
							$query .= " AND i.created > DATE_SUB('{$date}',INTERVAL ".$params->get('popularityRange')." DAY) ";
						}
						$orderby = 'i.hits DESC';
						break;

					case 'rand' :
						$orderby = 'RAND()';
						break;

					case 'best' :
						$orderby = 'rating DESC';
						break;

					case 'comments' :
						if ($params->get('popularityRange'))
						{
							$datenow = JFactory::getDate();
							$date =  $datenow->toSql();
							$query .= " AND i.created > DATE_SUB('{$date}',INTERVAL ".$params->get('popularityRange')." DAY) ";
						}
						$query .= " GROUP BY i.id ";
						$orderby = 'numOfComments DESC';
						break;

					case 'modified' :
						$orderby = 'lastChanged DESC';
						break;

					case 'publishUp' :
						$orderby = 'i.publish_up DESC';
						break;

					default :
						$orderby = 'i.id DESC';
						break;
				}

				$query .= " ORDER BY ".$orderby;
				$db->setQuery($query, $start, $limit);
				$items = $db->loadObjectList();
			}
			
			$model = K2Model::getInstance('Item', 'K2Model');

			if (count($items))
			{

				foreach ($items as $item)
				{
					$item->event = new stdClass;

					//Clean title
					$item->title = JFilterOutput::ampReplace($item->title);

					//Images

					//Read more link
					$item->link = urldecode(JRoute::_(K2HelperRoute::getItemRoute($item->id.':'.urlencode($item->alias), $item->catid.':'.urlencode($item->categoryalias))));

					//Tags
					if ($params->get('itemTags'))
					{
						$tags = $model->getItemTags($item->id);
						for ($i = 0; $i < sizeof($tags); $i++)
						{
							$tags[$i]->link = JRoute::_(K2HelperRoute::getTagRoute($tags[$i]->name));
						}
						$item->tags = $tags;
					}

					//Category link
					if ($params->get('itemCategory'))
						$item->categoryLink = urldecode(JRoute::_(K2HelperRoute::getCategoryRoute($item->catid.':'.urlencode($item->categoryalias))));

					//Extra fields
					if ($params->get('itemExtraFields'))
					{
						$item->extra_fields = $model->getItemExtraFields($item->extra_fields, $item);
					}

					//Comments counter
					if ($params->get('itemCommentsCounter'))
						$item->numOfComments = $model->countItemComments($item->id);

					//Attachments
					if ($params->get('itemAttachments'))
						$item->attachments = $model->getItemAttachments($item->id);

					if ($params->get('JPlugins', 0)){
						//Import plugins
						$dispatcher = JDispatcher::getInstance();
						JPluginHelper::importPlugin('content');
					}

					//Video
					if ($params->get('itemVideo') && $format != 'feed')
					{
						$params->set('vfolder', 'media/k2/videos');
						$params->set('afolder', 'media/k2/audio');
						$item->text = $item->video;

						$dispatcher->trigger('onContentPrepare', array('mod_k2_content.', &$item, &$params, $limitstart));
						$item->video = $item->text;
					}

					// Introtext
					$item->text = '';
					if ($params->get('itemIntroText'))
					{
						
						$item->text .= self::_cleanText($item->introtext);
					}
						
					$params->set('parsedInModule', 1);
					// for plugins to know when they are parsed inside this module

					if ($params->get('JPlugins', 0))
					{
						//Plugins
							
						$results = $dispatcher->trigger('onBeforeDisplay', array(&$item, &$params, $limitstart));
						$item->event->BeforeDisplay = trim(implode("\n", $results));

						$results = $dispatcher->trigger('onAfterDisplay', array(&$item, &$params, $limitstart));
						$item->event->AfterDisplay = trim(implode("\n", $results));

						$results = $dispatcher->trigger('onAfterDisplayTitle', array(&$item, &$params, $limitstart));
						$item->event->AfterDisplayTitle = trim(implode("\n", $results));

						$results = $dispatcher->trigger('onBeforeDisplayContent', array(&$item, &$params, $limitstart));
						$item->event->BeforeDisplayContent = trim(implode("\n", $results));

						$results = $dispatcher->trigger('onAfterDisplayContent', array(&$item, &$params, $limitstart));
						$item->event->AfterDisplayContent = trim(implode("\n", $results));

						$dispatcher->trigger('onPrepareContent', array(&$item, &$params, $limitstart));

					}
					//Init K2 plugin events
					$item->event->K2BeforeDisplay = '';
					$item->event->K2AfterDisplay = '';
					$item->event->K2AfterDisplayTitle = '';
					$item->event->K2BeforeDisplayContent = '';
					$item->event->K2AfterDisplayContent = '';
					$item->event->K2CommentsCounter = '';

					if ($params->get('K2Plugins', 0))
					{
						//K2 plugins
						JPluginHelper::importPlugin('k2');
						$results = $dispatcher->trigger('onK2BeforeDisplay', array(&$item, &$params, $limitstart));
						$item->event->K2BeforeDisplay = trim(implode("\n", $results));

						$results = $dispatcher->trigger('onK2AfterDisplay', array(&$item, &$params, $limitstart));
						$item->event->K2AfterDisplay = trim(implode("\n", $results));

						$results = $dispatcher->trigger('onK2AfterDisplayTitle', array(&$item, &$params, $limitstart));
						$item->event->K2AfterDisplayTitle = trim(implode("\n", $results));

						$results = $dispatcher->trigger('onK2BeforeDisplayContent', array(&$item, &$params, $limitstart));
						$item->event->K2BeforeDisplayContent = trim(implode("\n", $results));

						$results = $dispatcher->trigger('onK2AfterDisplayContent', array(&$item, &$params, $limitstart));
						$item->event->K2AfterDisplayContent = trim(implode("\n", $results));

						$dispatcher->trigger('onK2PrepareContent', array(&$item, &$params, $limitstart));

						if ($params->get('itemCommentsCounter'))
						{
							$results = $dispatcher->trigger('onK2CommentsCounter', array(&$item, &$params, $limitstart));
							$item->event->K2CommentsCounter = trim(implode("\n", $results));
						}

					}

					// Restore the intotext variable after plugins execution
					$item->displayIntrotext = $item->text;

					//Clean the plugin tags
					$item->displayIntrotext = preg_replace("#{(.*?)}(.*?){/(.*?)}#s", '', $item->displayIntrotext);

					//Author
					if ($params->get('itemAuthor'))
					{
						if (!empty($item->created_by_alias))
						{
							$item->author = $item->created_by_alias;
							$item->authorGender = NULL;
							$item->authorDescription = NULL;
							if ($params->get('itemAuthorAvatar'))
								$item->authorAvatar = K2HelperUtilities::getAvatar('alias');
							$item->authorLink = Juri::root(true);
						}
						else
						{
							$author = JFactory::getUser($item->created_by);
							$item->author = $author->name;

							$query = "SELECT `description`, `gender` FROM #__k2_users WHERE userID=".(int)$author->id;
							$db->setQuery($query, 0, 1);
							$result = $db->loadObject();
							if ($result)
							{
								$item->authorGender = $result->gender;
								$item->authorDescription = $result->description;
							}
							else
							{
								$item->authorGender = NULL;
								$item->authorDescription = NULL;
							}

							if ($params->get('itemAuthorAvatar'))
							{
								$item->authorAvatar = K2HelperUtilities::getAvatar($author->id, $author->email, $componentParams->get('userImageWidth'));
							}
							//Author Link
							$item->authorLink = JRoute::_(K2HelperRoute::getUserRoute($item->created_by));
						}
					}

					// Extra fields plugins
					if (is_array($item->extra_fields))
					{
						foreach ($item->extra_fields as $key => $extraField)
						{
							if ($extraField->type == 'textarea' || $extraField->type == 'textfield')
							{
								$tmp = new JObject();
								$tmp->text = $extraField->value;
								if ($params->get('JPlugins', 0))
								{
									$dispatcher->trigger('onPrepareContent', array(&$tmp, &$params, $limitstart));
								}
								if ($params->get('K2Plugins', 0))
								{
									$dispatcher->trigger('onK2PrepareContent', array(&$tmp, &$params, $limitstart));
								}
								$extraField->value = $tmp->text;
							}
						}
					}
					$rows[] = $item;
				}
				return $rows;
			}
		}

		public static function getCategories(&$params){
			$mainframe = JFactory::getApplication();
			$db = JFactory::getDBO();
			$user = JFactory::getUser();
			//$aid = (int)$user->get('aid');
			$list = array();
			if ($params->get('catfilter')){
				$categories = $params->get('category_id');
				//$maxlevel = $params->get('maxlevel',0);
				if ($categories){
					$list = self::getCategoriesFull($categories);
				}
				if ( count($list)>0){
					foreach ($list as $i => $category){
						if ($params->get('getCategoriesChild')){
							$categoriesChild = self::getCategoriesChild($category->id, 0);
						} else $categoriesChild = self::getCategoriesChild($category->id, 1);
						$list[$i]->categoriesChild = $categoriesChild;
					}
				}
			} else {
				$list = self::getCategoriesChild();
				if ( count($list)>0){
					foreach ($list as $i => $category){
						if ($params->get('getCategoriesChild')){
							$categoriesChild = self::getCategoriesChild($category->id, 0);
						} else {
							$categoriesChild = self::getCategoriesChild($category->id, 1);
						}
						$list[$i]->categoriesChild = $categoriesChild;
					}
				}
			}
			return $list; // raw from db
		}
		
		public  static function getCategoriesFull($categories){
			$mainframe = JFactory::getApplication();
			$db = JFactory::getDBO();
			$user = JFactory::getUser();
			//$aid = (int)$user->get('aid');
			$rows = array();
			if (!is_array($categories))
			{
				$categories = (array)$categories;
			}
			JArrayHelper::toInteger($categories);
			$categories = array_unique($categories);
			sort($categories);
			$query = "SELECT *
					FROM #__k2_categories
					WHERE id IN (".implode(',', $categories).") ";
			if ($mainframe->isSite())
			{
				$query .= "
						AND published=1
						AND trash=0";
				$query .= " AND access IN(".implode(',', $user->getAuthorisedViewLevels()).")";
				if ($mainframe->getLanguageFilter())
				{
					$query .= " AND language IN(".$db->Quote(JFactory::getLanguage()->getTag()).", ".$db->Quote('*').")";
				}
			}
			$db->setQuery($query);
			$list = $db->loadObjectList();
			if (count($list) > 0){
				foreach ($list as $category){
					$category->title = JFilterOutput::ampReplace($category->name);
					$category->link = urldecode(JRoute::_(K2HelperRoute::getCategoryRoute($category->id.':'.urlencode($category->alias))));
					$category->categoriesChild = array();
					$rows[] = $category;
				}
			}
			return $rows;
		}
		
		public static function getCategoriesChild($category=0,$maxlevel=0)
		{
			$mainframe = JFactory::getApplication();
			$list = self::getCategoryTree($category, $maxlevel);
			$list = array_slice($list, 1);
			if (count($list)>0){
				$list = self::getCategoriesFull($list);
			}
			return $list;
		}
	    function getCategoryTree($categories, $maxlevel=0)
	    {
	        $mainframe = JFactory::getApplication();
	        $db = JFactory::getDBO();
	        $user = JFactory::getUser();
	        //$aid = (int)$user->get('aid');
	        if (!is_array($categories))
	        {
	            $categories = (array)$categories;
	        }
	        JArrayHelper::toInteger($categories);
	        $categories = array_unique($categories);
	        sort($categories);
	        $key = implode('|', $categories);
	        $clientID = $mainframe->getClientId();
	        static $K2CategoryTreeInstances = array();
	        if (isset($K2CategoryTreeInstances[$clientID]) && array_key_exists($key, $K2CategoryTreeInstances[$clientID]))
	        {
	            return $K2CategoryTreeInstances[$clientID][$key];
	        }
	        $array = $categories;
	        $i = 0;
	        while (count($array))
	        {
	            $query = "SELECT id
							FROM #__k2_categories 
							WHERE parent IN (".implode(',', $array).") 
							AND id NOT IN (".implode(',', $array).") ";
	            if ($mainframe->isSite())
	            {
	                $query .= "
									AND published=1 
									AND trash=0";
                    $query .= " AND access IN(".implode(',', $user->getAuthorisedViewLevels()).")";
                    if ($mainframe->getLanguageFilter())
                    {
                        $query .= " AND language IN(".$db->Quote(JFactory::getLanguage()->getTag()).", ".$db->Quote('*').")";
                    }
	            }
	            $db->setQuery($query);
	            $array =  $db->loadColumn();
	            $categories = array_merge($categories, $array);
	            $i++;
	            if ($maxlevel>0 && $i >= $maxlevel){
	            	break;
	            }
	        }
	        JArrayHelper::toInteger($categories);
	        $categories = array_unique($categories);
	        $K2CategoryTreeInstances[$clientID][$key] = $categories;
	        return $categories;
	    }
	}
}
