<?php
/**

* @package   JE K2 STORY

* @copyright Copyright (C) 2009 - 2010 Open Source Matters. All rights reserved.

* @license   http://www.gnu.org/licenses/lgpl.html GNU/LGPL, see LICENSE.php

* Contact to : emailtohardik@gmail.com, joomextensions@gmail.com

* Visit : http://www.joomlaextensions.co.in/

**/     
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );

class thumbnail {

//---------Parameter Description -------------------

// $filetype = original File type
// $tsrc = where thumbnail will generate
// $dest = from where image taken
// $n_width = thumbnail output width
// $n_height = thumbnail output height

	function config_data()
	{
	 $db = jFactory::getDBO();
	$query = 'SELECT * FROM #__hm_photoconfigration WHERE id = 1 ';
	$db->setQuery($query);
	return $data = $db->loadObject();
	}
		
	function CreatThumb($filetype,$tsrc,$dest,$n_width,$n_height){
//	 $dest='D:\apache2triad\htdocs\Projects\testjoomla\media\k2\items\cache';
//	 echo $dest=$tsrc;
	
	if ( $filetype == "jpg" || $filetype == "jpeg")
	$im=imagecreatefromjpeg($dest); 	
	else if( $filetype == "gif")	
	$im=imagecreatefromgif($dest);
	else if($filetype=="png")
	$im=imagecreatefrompng($dest); 
			
	 /* $old_x=imageSX($im);
		
 		$old_y=imageSY($im);
 	
		if ($old_x > $old_y) {
			$n_width=$n_width;
			$n_height=$old_y*($n_height/$old_x);
		}
		if ($old_x < $old_y) {
			$n_width=$old_x*($n_width/$old_y);
			$n_height=$n_height;
		}
		if ($old_x == $old_y) {
			$n_width=$n_width;
			$n_height=$n_height;
		}*/

		
		if ( $filetype == "gif"){
		
			$im=ImageCreateFromGIF($dest);
			$width=ImageSx($im);              // Original picture width is stored
			$height=ImageSy($im);                  // Original picture height is stored
			$newimage=imagecreatetruecolor($n_width,$n_height);
			imageCopyResized($newimage,$im,0,0,0,0,$n_width,$n_height,$width,$height);
		
			ImageGIF($newimage,$tsrc);
			chmod("$tsrc",0777);
					
		}
		if($filetype=="jpg" || $filetype == "jpeg"){
			$im=imagecreatefromjpeg($dest); 
			$width=ImageSx($im);              // Original picture width is stored
			$height=ImageSy($im);             // Original picture height is stored
			$newimage=imagecreatetruecolor($n_width,$n_height);
			              
			imageCopyResized($newimage,$im,0,0,0,0,$n_width,$n_height,$width,$height);
			ImageJpeg($newimage,$tsrc);
			chmod("$tsrc",0777);
						
		}
		if($filetype=="png"){
			$im=ImageCreateFromPNG($dest); 
			$width=ImageSx($im);              // Original picture width is stored
			$height=ImageSy($im);             // Original picture height is stored
			$newimage=imagecreatetruecolor($n_width,$n_height);                 
			imageCopyResized($newimage,$im,0,0,0,0,$n_width,$n_height,$width,$height);
			imagepng($newimage,$tsrc);
			chmod("$tsrc",0777);
										
		}
		
	}
	

		
}
///////////////// New thumbnail generate

class thumbnail_images {

// get
var $PathImgOld;
var $PathImgNew;
var $NewWidth;
var $NewHeight;

// tmp
var $mime;

function imagejpeg_new ($NewImg,$path_img) {
if ($this->mime == 'image/jpeg' or $this->mime == 'image/pjpeg') @imagejpeg($NewImg,$path_img);
elseif ($this->mime == 'image/gif') imagegif($NewImg,$path_img);
elseif ($this->mime == 'image/png') imagepng($NewImg,$path_img);
else return(false);
return(true);
}

function imagecreatefromjpeg_new($path_img) {
if ($this->mime == 'image/jpeg' or $this->mime == 'image/pjpeg') $OldImg=imagecreatefromjpeg($path_img);
elseif ($this->mime == 'image/gif') $OldImg=imagecreatefromgif($path_img);
elseif ($this->mime == 'image/png') $OldImg=imagecreatefrompng($path_img);
else return(false);
return($OldImg);
}

function create_thumbnail_images() {
$PathImgOld = $this->PathImgOld;
$PathImgNew = $this->PathImgNew;
$NewWidth = $this->NewWidth;
$NewHeight = $this->NewHeight;

$Oldsize = @getimagesize($PathImgOld);
$this->mime = $Oldsize['mime'];
$OldWidth = $Oldsize[0];
$OldHeight = $Oldsize[1];

if ($NewHeight=='' and $NewWidth!='') {
$NewHeight = ceil(($OldHeight*$NewWidth)/$OldWidth);
}
elseif ($NewWidth=='' and $NewHeight!='') {
$NewWidth = ceil(($OldWidth*$NewHeight)/$OldHeight);
}
elseif ($NewHeight=='' and $NewWidth=='') {
return(false);
}

$OldHeight_castr = ceil(($OldWidth*$NewHeight)/$NewWidth);
$castr_bottom = ($OldHeight-$OldHeight_castr)/2;

$OldWidth_castr = ceil(($OldHeight*$NewWidth)/$NewHeight);
$castr_right = ($OldWidth-$OldWidth_castr)/2;

if ($castr_bottom>0) {
$OldWidth_castr = $OldWidth;
$castr_right = 0;
}
elseif ($castr_right>0) {
$OldHeight_castr = $OldHeight;
$castr_bottom = 0;
}
else {
$OldWidth_castr = $OldWidth;
$OldHeight_castr = $OldHeight;
$castr_right = 0;
$castr_bottom = 0;
}

$OldImg=$this->imagecreatefromjpeg_new($PathImgOld);
if ($OldImg) {
$NewImg_castr=imagecreatetruecolor($OldWidth_castr,$OldHeight_castr);
if ($NewImg_castr) {
imagecopyresampled($NewImg_castr,$OldImg,0,0,$castr_right,$castr_bottom,$OldWidth_castr,$OldHeight_castr,$OldWidth_castr,$OldHeight_castr);
$NewImg=imagecreatetruecolor($NewWidth,$NewHeight);
if ($NewImg) {
imagecopyresampled($NewImg,$NewImg_castr,0,0,0,0,$NewWidth,$NewHeight,$OldWidth_castr,$OldHeight_castr);
imagedestroy($NewImg_castr);
imagedestroy($OldImg);
if (!$this->imagejpeg_new($NewImg,$PathImgNew)) return (false);
imagedestroy($NewImg);
}
}
}
else {
return(false);
}

return(true);
}

}
?>