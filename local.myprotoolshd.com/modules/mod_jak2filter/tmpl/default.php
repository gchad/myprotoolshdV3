<?php
/**
 * ------------------------------------------------------------------------
 * JA K2 Filter Module for J25 & J3.3
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

// No direct access.
defined('_JEXEC') or die;

   /*** GCHAD FIX ***/
   /*** remove motools more ***/     
JHtml::_('behavior.tooltip', '.ja-k2filter-tip', array('hideDelay'=>1500, 'fixed'=>true, 'className' => 'jak2-tooltip'));

$formid = 'jak2filter-form-'.$module->id; 
$itemid = $params->get('set_itemid',0)?$params->get('set_itemid',0):JRequest::getInt('Itemid');
$ajax_filter = $params->get('ajax_filter', 0);
$share_url = $params->get('share_url_of_results_page', 0);

?>


<div id="jak2-loading"><?=JText::_('LOADING')?></div>
<a class="modal" href="#" style="display: none"></a>


<form id="<?php echo $formid; ?>" name="<?php echo $formid; ?>" method="POST" action="<?php echo JRoute::_('index.php?option=com_jak2filter&view=itemlist&Itemid='.$itemid); ?>">
    
    <input type="hidden" name="task" value="search" />
    <input type="hidden" name="swr" value="<?php echo $slider_whole_range; ?>" />
    
    <?php if(!empty($theme)): ?>
        <input type="hidden" name="theme" value="<?php echo $theme ?>" />
    <?php endif; ?>
    
    <?php if($catMode): ?>
        <!-- include sub category -->
        <input type="hidden" name="isc" value="1" />
    <?php endif; ?>
    
    <?php if(!$params->get('display_ordering_box', 1) && $params->get('catOrdering') != "inherit"): ?>
        <input type="hidden" id="ordering" name="ordering" value="<?php echo $params->get('catOrdering'); ?>" />
    <?php endif; 

/******* GCHAD FIX ******/

$totalK2Search = $_SESSION['totalK2Search'];
$limitK2Search = $_SESSION['limitK2Search'];

$limitStart = JRequest::getVar('limitstart', 0);
$nextLimitStart = ($limitK2Search + $limitStart) > $totalK2Search ? $totalK2Search : $limitK2Search + $limitStart;

?>

<input type="hidden" id="K2Start" name="start" value="<?=$nextLimitStart?>" />
<input type="hidden" id="K2Total" name="total" value="<?=$totalK2Search?>" />

<?php 
/******* GCHAD FIX ******/

if(!$filter_by_category): ?>
    <?php echo $categories; ?>
<?php endif; ?>

<ul id="jak2filter<?php echo $module->id; ?>" class="ja-k2filter <?php echo $ja_stylesheet;?>">

<?php 
$j = 0;
$clear = '';
$style = '';



?>
<?php 
/*BEGIN: filter by date*/
if($filter_by_daterange):
$style = '';
if($params->get('ja_column') >0 && (($j) % $params->get('ja_column')) == 0){
	$clear = " clear:both;";
}
if($ja_column || $clear){
	$style ='style="'.$ja_column.$clear.'"';
}
$j++;
?>
	<li <?php echo $style;?>>
		<label class="group-label"><?php echo JText::_('SEARCH_BY_DATE'); ?></label>
		<?php echo $filter_by_daterange; ?>
	</li>
<?php endif; ?>


<?php 
/*BEGIN: filter by Author*/
if($filter_by_author): 
$style = '';
if($params->get('ja_column') >0 && (($j) % $params->get('ja_column')) == 0){
	$clear = " clear:both;";
}
if($ja_column || $clear){
	$style ='style="'.$ja_column.$clear.'"';
}
$j++;

?>
	<li <?php echo $style;?>>
		<?php echo $authors_label; ?>
		<?php echo $authors; ?>
	</li>
<?php 
$clear = '';
endif; 
/*END: filter by Author*/?>

<?php 
/*BEGIN: filter by Rating*/
if($filter_by_rating_display): 
$style = '';
if($params->get('ja_column') >0 && (($j) % $params->get('ja_column')) == 0){
	$clear = " clear:both;";
}
if($ja_column || $clear){
	$style ='style="'.$ja_column.$clear.'"';
}
$j++;

?>
	<li <?php echo $style;?>>
		<label class="group-label">
		<?php echo JText::_('JAK2_RATING'); ?>
		<ul class="itemRatingList" id="rating_range_<?php echo $module->id; ?>">
			<li style="width:53.4%;" id="presenter_<?php echo $module->id; ?>_rating" class="itemCurrentRating"></li>
			<li><span class="srange one-star" title="" rel="1-stars" href="#">1</span></li>
			<li><span class="srange two-stars" title="" rel="2-stars" href="#">2</span></li>
			<li><span class="srange three-stars" title="" rel="3-stars" href="#">3</span></li>
			<li><span class="srange four-stars" title="" rel="4-stars" href="#">4</span></li>
			<li><span class="srange five-stars" title="" rel="5-stars" href="#">5</span></li>
		</ul>
		<span id="presenter_<?php echo $module->id; ?>_rating_note" class="itemCurrentRatingNote"></span>
		</label>
		<?php echo $filter_by_rating_display; ?>
	</li>
<?php 
$clear = '';
endif; 
/*END: filter by Rating*/
?>

<?php
/*BEGIN: filter by Category*/
if($filter_by_category){
	$style = '';
	if($params->get('ja_column') >0 && (($j) % $params->get('ja_column')) == 0){
		$clear = " clear:both;";
	}
	if($ja_column || $clear){
		$style ='style="'.$ja_column.$clear.'"';
	}
	$j++;
	?>
	<li <?php echo $style;?>>
		<label class="group-label"><?php echo JText::_('JAK2_CATEGORY'); ?></label>
		<?php echo $categories; ?>
	</li>
	<?php
	$clear = '';
}

/*END: filter by Category*/
?>

<?php if($list): ?>
    
	<?php if($ja_stylesheet == 'vertical-layout' && count($list) > 1): debug('la');?>
		
		<li id="ja-extra-field-accordion-<?php echo $module->id; ?>" class="accordion">
			
			<?php foreach($list as $glist): ?>
    			
    			<?php $groupid = $glist['groupid']; ?>
    			
    			<h4 class="heading-group heading-group-<?php echo $groupid ?>"><?php echo $glist['group'] ?></h4>
    			
    			<div>
    				<ul>
    					<?php require JModuleHelper::getLayoutPath('mod_jak2filter', 'default_extrafields'); ?>
    				</ul>
    			</div>
    			
			<?php endforeach; ?>
			
		</li>
	<?php else: ?>
	    
		<?php foreach($list as $glist): ?>
		    
		      <?php require JModuleHelper::getLayoutPath('mod_jak2filter', 'default_extrafields'); ?>
		      
		<?php endforeach; ?>
		
	<?php endif; ?>


	
<?php endif;

/*BEGIN: filter by Tags*/
if($filter_by_tags_display): 
    
    $style = '';
    
    if($params->get('ja_column') >0 && (($j) % $params->get('ja_column')) == 0){
        $clear = " clear:both;";
    }
    
    if($ja_column || $clear){
        $style ='style="'.$ja_column.$clear.'"';
    }
    
    $j++;?>
    
        <li <?php echo $style;?> class="magic-select">
            
            <?php echo $filter_by_tags_label; ?>
            <?php echo $filter_by_tags_display; ?>
            
        </li>
        
    <?php 
    $clear = '';
    
    $helper = new modJak2filterHelper($module);
    $paramsM = new JRegistry($module->params);
    
    $db = JFactory::getDbo();
       
    $cat_ids = $paramsM->get('k2catsid',null);
    if(count($helper->activeCats)) {
        $cat_ids = $this->activeCats;
    }
      
    if($paramsM->get('catMode', 0)) {
        $model = new JAK2FilterModelItemlist();
        $cat_ids = $model->getCategoryTree($cat_ids);
    }
    $cat_ids = is_array($cat_ids) ? implode(',',$cat_ids) : $cat_ids;
    
    $query ="SELECT t.id , t.name as name ".
            " FROM #__k2_tags AS t".
            " LEFT JOIN #__k2_tags_xref AS tx ON t.id = tx.tagID";
            
    if ($cat_ids) {
        
        $query .= " LEFT JOIN #__k2_items as ki ON tx.itemID = ki.id";
        $query .= " WHERE ki.catid IN ($cat_ids) AND t.published=1";
        
    } else {
        
        $query .= " WHERE t.published=1";
        
    }
    $query .=" GROUP BY t.id";
    $db->setQuery( $query );
    
    $availTags = $db->loadObjectList('id');
    
    global $tagsMatrix;
    
    foreach( $tagsMatrix as $catId => &$groups){
        
        foreach ( $groups as &$group){
            
            foreach($group as $k => &$v){
                
                if(key_exists($v, $availTags)){

                    $group[$k] = array('id' => $v, 'name' => JText::_('TAG_'.$availTags[$v]->name),);
            
                } else {
                    
                    unset( $group[$k]);
                }               
            }                    
        }
    }
    
    foreach( $tagsMatrix as $catId => &$groups){
        
       foreach ($groups as $k => &$group){
           
           if(empty($group)){
                unset($groups[$k]);
           }
       }    
    }
    
   
    ?>
    
    
    <script type="text/javascript">
     
        var tagsMatrix = <?=json_encode($tagsMatrix)?>;
        
        window.addEvent('load', function(){
            
            populateTags(null);
        });
       
             
    </script>
    
    
  <?php  
endif; 




/*END: filter by Tags*/

if ($params->get('display_ordering_box', 1)): ?>
	<?php
	$style = '';
	if($params->get('ja_column') >0 && (($j) % $params->get('ja_column')) == 0){
		$clear = " clear:both;";
	}
	if($ja_column || $clear){
		$style ='style="'.$ja_column.$clear.'"';
	}
	$j++;
	?>
	<li <?php echo $style; ?>>
		<label for="catOrderingSelectBox" class="group-label"><?php echo JText::_('JAK2_ITEM_ORDERING_SELECT_BOX'); ?></label>
		<?php echo $display_ordering; ?>
	</li>
<?php endif; ?>


<?php
$style='';
if($params->get('ja_column') >0 && (($j) % $params->get('ja_column')) == 0){
	$clear = " clear:both;";
}
if($ja_column || $clear){
	$style ='style="'.$ja_column.$clear.'"';
}
$j++;






 /*** GCHAD FIX add the search box */
    

if($filter_by_keyword): ?>
    
    
    <li >
        <input type="text" name="searchword" id="searchword<?php echo $module->id; ?>" class="inputbox" value="" placeholder="<?=JText::_('SEARCH_BY_KEYWORD'); ?>"/>
    </li>
    
    <li>
        <button id="searchKeyWord" class="button2"><?=JText::_('SEARCH')?></button>
        
        <?php if($params->get('enable_reset_button',1) == 1): ?>
        <input class="button2 buttonGrey" type="button" name="btnReset" value="<?php echo JText::_('RESET'); ?>" onclick="jaK2Reset('<?php echo $module->id;?>', '<?php echo $formid; ?>', true);" />
        <?php endif; ?>
        
    </li>
    
    
    
    
    <?php 
   
    
endif; 
   


if($params->get('auto_filter',1) == 0): ?>
	<input class="btn button2" type="submit" name="btnSubmit" value="<?php echo JText::_('JAK2SEARCH'); ?>" />
<?php endif; ?>

<?php if($ajax_filter && $share_url): ?>
	<div class="jak2shareurl"><a href="<?php echo JURI::current() ?>" target="_blank" title="<?php echo JText::_('JAK2_SHARE_URL_OF_RESULTS_PAGE_DESC', true)?>"><?php echo JText::_('JAK2_SHARE_URL_OF_RESULTS_PAGE')?></a></div>
<?php endif; ?>
	

<?php 
$clear = '';

 ?>
    
</ul>
<?php if($params->get('ajax_filter', 0) == 1): ?>
	<input type="hidden" name="tmpl" value="component"/>
<?php endif;



 ?>
 
</form>

<script type="text/javascript">

/*<![CDATA[*/

//validate date function
function isDate(txtDate){
    var reg = /^(\d{4})([\/-])(\d{1,2})\2(\d{1,2})$/;
    return reg.test(txtDate);
};

//validate startdate and enddate before submit form
function validateDateRange(obj){
    
    if(obj.id == 'sdate_<?php echo $module->id; ?>' || obj.id == 'edate_<?php echo $module->id; ?>'){
        var sDate = $('jak2filter<?php echo $module->id;?>').getElement('#sdate_<?php echo $module->id; ?>').get('value');
        var eDate = $('jak2filter<?php echo $module->id;?>').getElement('#edate_<?php echo $module->id; ?>').get('value');
        if(sDate != '' && eDate != ''){
            if(isDate(sDate) && isDate(eDate)){
                obj.removeClass('date-error');
                $('<?php echo $formid; ?>').fireEvent('submit');
            }
            else{
                obj.addClass('date-error');
            }
        }
    }
    else{
        $('<?php echo $formid; ?>').fireEvent('submit');
    }
};

function searchFromScratch(){
        
        var container = jQuery('#k2Container');
        container.empty();
        
        $('K2Start').set('value', 0);
        $('K2Total').set('value', 0);
        
        window.jak2BlockSearch = false; //in case there was no item found and we blocked the search
        
        jQuery('#<?php echo $formid; ?>').submit();
};

window.addEvent('load', function(){
    
    
    
    /****** REMOVE MAGIC SELECT REGULAR BUTTON POP***/
   if($$('.magicController')){
       $$('.magicController').each(function(el){
           
           el.addEvent('focus', function(f){
               f.preventDefault();
           });

           var listid = el.get('listid');

           
           el.addEvent('click',function(e){
          
                jaMagicSelect(el, listid);
            });
      
           
       });
   }
   
   var isWebkit = 'WebkitAppearance' in document.documentElement.style;
   var isMoz = 'MozAppearance' in document.documentElement.style;
   
    if(isWebkit || isMoz){
        
        $$('#jak2filter101 li').each(function(el){
           
            if(el.getChildren('select').length > 0 || el.hasClass('magic-select')){
                el.addClass('kitEl');
            }
        });
        $('jak2filter101').addClass('kit');
    }
   
    
	if($('jak2filter<?= $module->id;?>').getElement('#category_id')){
		jak2DisplayExtraFields(<?= $module->id;?>, $('jak2filter<?= $module->id;?>').getElement('#category_id'), <?= $selected_group; ?>);
	}

	<?php 
	
	/** add searchbutton **/
	?>
	
	
	if($('searchKeyWord')){
	    
	    $('searchKeyWord').addEvent('click',function(e){
	        e.preventDefault();
	        
	       /* $('category_id').value='';
	        $('ordering').value = '';
	        $$('.ja-magic-select-container').each(function(el){
	            el.empty();
	        })
	        
	        if($('searchword101').value.length == 0){
	            
	            alert('asasdg');
	            
	        } else {
	             searchFromScratch();
	        }*/
	        
	        searchFromScratch();
	    });
	    
	    
	}
	
	<?php
	
	/**** AUTO SEARCH ******/
	
	if($auto_filter): ?>
	
    	var f = $('<?php echo $formid; ?>');
    	
    	f.getElements('input').each(function(el) {
    	    
    	    if(el.get('name') != 'searchword'){
    	    
        		el.addEvent('change', function(){
        		    
                    if(this.id == 'sdate_<?php echo $module->id; ?>' || this.id == 'edate_<?php echo $module->id; ?>'){
                        
                        var sDate = $('jak2filter<?php echo $module->id;?>').getElement('#sdate_<?php echo $module->id; ?>').get('value');
                        var eDate = $('jak2filter<?php echo $module->id;?>').getElement('#edate_<?php echo $module->id; ?>').get('value');
                        
                        if(sDate != '' && eDate != ''){
                            if(isDate(sDate) && isDate(eDate)){
                                this.removeClass('date-error');
                                searchFromScratch();
                            }
                            else{
                                this.addClass('date-error');
                            }
                        }
                        
                    } else{
                        searchFromScratch();
                    }
        		});
    		}
    	});
    	
    	f.getElements('select').each(function(el) {
    	       	    
    		el.addEvent('change', function(){
    		    
                if(this.id == 'dtrange' && this.value == 'range'){
                    
                    var sDate = $('jak2filter<?php echo $module->id;?>').getElement('#sdate_<?php echo $module->id; ?>');
                    var eDate = $('jak2filter<?php echo $module->id;?>').getElement('#edate_<?php echo $module->id; ?>');
                   
                    if(sDate.get('value') != '' && eDate.get('value') != ''){
                        var isStartDate = isDate(sDate.get('value'));
                        var isEndDate = isDate(eDate.get('value'));
                        if(isStartDate && isEndDate){
                             searchFromScratch();                           
                        }
                        else{
                            if(!isStartDate)
                                sDate.addClass('date-error');
                            if(!isEndDate)
                                eDate.addClass('date-error');
                        }
                    }
                    
                } else if (this.id == 'category_id' ){
                    
                    populateTags(this.value);
                    searchFromScratch();  
                    
                } else {
                    
                     searchFromScratch();                    
                }
    		});
    	});
    	
    	f.getElements('textarea').each(function(el) {
    	    
    		el.addEvent('change', function(){
    		    searchFromScratch();    			
    		});
    	});
    	
	<?php endif;
    
    /***** END AUTO SEARCH *****/?>

	<?php if($ajax_filter): ?>
	
	$('<?php echo $formid; ?>').addEvent('submit', function() { 
//		jak2AjaxSubmit(this, '<?php echo JURI::root(true).'/'; ?>');
		<?php if($share_url): ?>
//		jak2GetUrlSharing(this);
		<?php endif; ?>
		return false;
	});
	
	jQuery('#<?php echo $formid; ?>').on('submit', function(event) {
		
		event.preventDefault();
		
		var limitStart = parseInt($('K2Start').get('value'));
		var total = parseInt($('K2Total').get('value'));
		
        //makes sure it will start only if there is something to search
		if(limitStart < total || total == 0){
		    
		    jak2AjaxSubmit(this, '<?php echo JURI::root(true).'/'; ?>');
		    
		} else {
		    
		    if($('K2ScrollButton')){
		        //$('K2ScrollButton').css({'display': 'none'});
		    }
		}
		
		
		<?php if($share_url): ?>
	//	jak2GetUrlSharing(this);
		<?php endif; ?>
	});
	
	//if(jQuery('#k2Container')) {
	//	jak2AjaxPagination(jQuery('#k2Container'), '<?php echo JURI::root(true).'/'; ?>');
		<?php if($share_url): ?>
	//	jak2GetUrlSharing(this);
		<?php endif; ?>
//	}

	<?php else: ?>
	$('<?php echo $formid; ?>').addEvent('submit', function() {
		$('<?php echo $formid; ?>').submit();
	});
	<?php endif; ?>
	
	<?php /****** GCHAD FIX **** Add the scrolling to trigger the event */ ?>
    
    //initialize ajax submission
    window.jak2AjaxSubmitting = false;
    window.jak2BlockSearch = false;
   
    
    //add scrolling to trigger event
    var container = $('k2Container'); 
    
    if(container){
        
       $(window).addEvent('scroll',function(){
        
        //not used
           function getDocHeight() {
               
                var D = document;
                return Math.max(
                    D.body.scrollHeight, D.documentElement.scrollHeight,
                    D.body.offsetHeight, D.documentElement.offsetHeight,
                    D.body.clientHeight, D.documentElement.clientHeight
                );
           } 
           
          // var isAjax = $('K2IsAjax').get('value');
      
           var containerH = container.getSize().y;
           var containerPos = container.getPosition().y;
           var containerPosBottom = containerH + containerPos;
           var triggerHeight =  containerPosBottom - $(window).getSize().y + 100;
           
           var curScrollY =  $(window).getScroll().y;  
    
            
           if(curScrollY > triggerHeight && jak2BlockSearch == false ){
               
                jQuery('#<?php echo $formid; ?>').submit();
           } 
        });
    
    }
    
    
    
    <?php /****** Make sure we reset the limits when click on search or auto search is enabled*/ ?>
    
    
    //if the search button is here
    var button = jQuery('#<?php echo $formid; ?>').find('input[type=submit]')[0];
    
    if(button){
        
        button.addEvent('click',function(e){
        
            e.preventDefault();
            searchFromScratch();
       
         });
    }
   
    
    
    <?php /***** displays the please scroll buton or not *//// ?>
   
    
    jQuery('<div id="K2ScrollButtonWrap"><button id="K2ScrollButton" class="button2">' + '<?php echo JText::_('PLEASE_SCROLL');?>' + '</button></div>').appendTo('#t3-content');
    $('K2ScrollButton').addEvent('click',function(e){
        jQuery('#<?php echo $formid; ?>').submit();
    });
    setScrollButton();
    
    searchFromScratch();
   
});
/*]]>*/
</script>