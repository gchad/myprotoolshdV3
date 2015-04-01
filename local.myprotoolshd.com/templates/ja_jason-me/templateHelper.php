<?php
/**
 * ------------------------------------------------------------------------
 * JA Jason template
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
define('JA_GRID_SIZE', '1x1');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.image.image');

class JasonHelper {

	public static function loadParamsPortfolioContents($item, $pdata = 'attribs') {
		$data = $item->$pdata;
		if(is_string($pdata)) {
			$data = new JRegistry;
			$data->loadString($item->$pdata);
		}

		if($data instanceof JRegistry) {
			return array(
				'jcontent_type'      =>$data->get('jcontent_type', ''),
        'jcontent_updateon'	=>$data->get('jcontent_updateon', ''),
        'jcontent_version'	=>$data->get('jcontent_version', ''),
        'jcontent_author'		=>$data->get('jcontent_author', ''),
        'jcontent_carousel'		=>$data->get('jcontent_carousel', '')
			);
		}
		
		return array (
			'jcontent_type'      => '',
      'jcontent_updateon' => '',
      'jcontent_version'  =>'',
      'jcontent_author'   =>'',
      'jcontent_carousel'   =>''
		);
	}
	
	public static function loadParamsGridContents($item, $pdata = 'attribs'){
        $data = $item->$pdata;
        if(is_string($pdata)){
            $data = new JRegistry;
            $data->loadString($item->$pdata);
        }

        if($data instanceof JRegistry){
            return array(
                'size' => $data->get('jcontent_size', JA_GRID_SIZE)
            );
        }

        return array(
            'size' => JA_GRID_SIZE
        );
    }

	public static function portfolioImage($text) {
      if(preg_match_all('#<img[^>]+>#iU', $text, $imgs)){
          //remove the text
          $text = preg_replace('#<img[^>]+>#iU', '', $text);
          //collect all images
          $img_data = array();
          // parse image attributes
          foreach( $imgs[0] as $img_tag){
              $img_data[$img_tag] = JUtility::parseAttributes($img_tag);
          }
          $total = count($img_data);

          if($total > 0) :
              $portfolioImage =  '';
              $j = 1;
              foreach ($img_data as $img => $attr) :
                $portfolioImage .=  '<div class="item item-image article-image">'.$img.'</div>';
                 $j++;
              endforeach;
          endif;

          return $portfolioImage;
      } else {
          return false;
      }
    }
	
	
	public static function loadAccordionScript($num){
       $doc = JFactory::getDocument();
       //$doc->addScript(JURI::base().'templates/ja_nuevo/js/accordion.js');
       $doc->addScriptDeclaration('
        ;(function($){
            $(document).ready(function(){
                if($(".category-arcodion").length > 0){
                    var ratio   = 5,
                        defaulth = 50,
                        modACArcodion = function(){
                            var wwindow = $(window).width();
                            if(wwindow >= 768){
                                var wdiv    = $(".category-arcodion").width(),
                                    iwidth  = $(".category-arcodion li").removeAttr("style").not(".active").width(),
                                    fwidth  = wdiv - iwidth * ('.$num.' - 1);
								
                                $(".category-arcodion > li").css({"width":iwidth,"height":"480px"});

                                $(".category-arcodion .heading").css({"width":iwidth,"height":"480px"});

                                $(".category-arcodion > li.active").css("width",fwidth);
                                $(".category-arcodion > li.active .heading").stop(true,true).fadeOut();

                                $(".category-arcodion > li .heading").unbind("click").bind("click",function (){												
										if($(this).hasClass("active")){
											return false;
										}
										var $this = $(this).parent();
										$(".category-arcodion > li").removeClass("active");
										$this.addClass("active");

										$(".category-arcodion > li").stop().animate({"width":iwidth},500);
										$(".category-arcodion .heading").stop(true,true).fadeIn();
										$(".category-arcodion .description").stop(true,true).fadeOut();

										$this.stop().animate({"width":fwidth},500);
										$(".heading",$this).stop(true,true).fadeOut();
										$(".description",$this).stop(true,true).fadeIn();
                                    }
                                );
                            }else{
								//Reset style
                                $(".category-arcodion > li").removeAttr("style");								
                                $(".category-arcodion .heading").removeAttr("style");
								$(".category-arcodion .description").removeAttr("style");
								$(".category-arcodion > li .heading").unbind("click").bind("click",function (){												
										if($(this).hasClass("active")){
											return false;
										}
										var $this = $(this).parent();
										$(".category-arcodion > li").removeClass("active");									
										$this.addClass("active");
										return false;
                                    }
                                );
                            }
                        };
                    modACArcodion();

                    $(window).resize(function(){
                       modACArcodion();
                    });
                }
            });
        })(jQuery);
       ');
       return;
   }
   
   public static function loadModule($name, $style = 'raw') {
		jimport('joomla.application.module.helper');
		$module = JModuleHelper::getModule($name);
		$params = array('style' => $style);
		echo JModuleHelper::renderModule($module, $params);
	}

	public static function loadModules($position, $style = 'raw') {
		jimport('joomla.application.module.helper');
		$modules = JModuleHelper::getModules($position);
		$params = array('style' => $style);
		foreach ($modules as $module) {
			echo JModuleHelper::renderModule($module, $params);
		}
	}
}