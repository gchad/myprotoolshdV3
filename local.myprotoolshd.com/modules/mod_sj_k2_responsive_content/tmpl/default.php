<?php
/**
 * @package Sj Responsive Content for K2
 * @version 2.5.0
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2012 YouTech Company. All Rights Reserved.
 * @author YouTech Company http://www.smartaddons.com
 */

defined('_JEXEC') or die;
ImageHelper::setDefault($params);
$options = $params->toObject();
$uri=JURI::getInstance();
$uri->setVar('page', '2');
$uri->setVar('module_id',$module->id);

?>
<script type="text/javascript">
//<!--
jQuery(document).ready(function($){
	;(function(element){
		var time = 0, inter = null;
		var $container = $(element), test = function(){ if (time>2000 && inter){clearInterval(inter); time=0;} time+=10; console.log('At '+time+': '+$container.height()); },
			is = element + ' .responsive-content-box',
			scroll = function(){
				<?php if($params->get('loadmore_type') == 1) { ?>
					$container.infinitescroll('scroll');
				<?php } ?>
			},
			start = function(){
				$container.isotope({
					itemSelector: is
				});

				if ( $.browser.msie  && parseInt($.browser.version, 10) <= 8){
					//nood
				}else{
					$(window).resize(function() {
						$container.isotope('reLayout');
					});
			    }
				<?php if($params->get('loadmore_type') == 1) { ?>
					$(window).bind('scroll');
					$container.infinitescroll(
						{
							
							navSelector : '#page_nav_<?php echo $module->id;?>', // selector for the paged navigation
							nextSelector : '#page_nav_<?php echo $module->id;?> a', // selector for the NEXT link (to page 2)
							itemSelector : is, // selector for all items you'll retrieve
							debug		 : false,
							loading: {
								finishedMsg: 'No more pages to load.',
								img: 'http://i.imgur.com/qkKy8.gif'
							},
							 infid:'<?php echo $module->id; ?>'
						},
						// call Isotope as a callback
						function( newElements ) {
							var $newElements = $( newElements ).css({ opacity: 0 });
							$newElements.imagesLoaded( function(){
								$newElements.animate({ opacity: 1 });
								$container.isotope( 'appended', $newElements );
								setTimeout(scroll, 1000);
							});
							
						}
					);
				<?php } else {?>
					var  loading_state = function(){
       						 $('.loader-image','#resp_content_button_<?php echo $module->id; ?>').css('display','inline-block');
       						 $('.loader-label','#resp_content_button_<?php echo $module->id; ?>').html('Loading...');
       					} 
					var  loadmore_state = function(){
       						 $('.loader-image','#resp_content_button_<?php echo $module->id; ?>').css('display','none');
       						 $('.loader-label','#resp_content_button_<?php echo $module->id; ?>').html('Load More');
       					} 
					
						$container.infinitescroll(
							{
								navSelector : "a#resp_content_button_<?php echo $module->id; ?>:last", // selector for the paged navigation
								nextSelector : "a#resp_content_button_<?php echo $module->id; ?>:last", // selector for the NEXT link (to page 2)
								itemSelector : is, // selector for all items you'll retrieve
								debug		 : false,
								loading: {
									finishedMsg: 'No more pages to load.',
									img: 'http://i.imgur.com/qkKy8.gif'
								},
								errorCallback: function(){
									$('#responsive_loadmore_<?php echo $module->id ?>').remove();
									
								},
								 animate      : true,  
								 localMode    : true,
								infid:'<?php echo $module->id; ?>'
							},
						// call Isotope as a callback
						function( newElements ) {
							var $newElements = $( newElements ).css({ opacity: 0 });
							$newElements.imagesLoaded( function(){
								$newElements.animate({ opacity: 1 });
								$container.isotope( 'appended', $newElements );
								loadmore_state();
							});
							
						}
					);
					
				$(window).unbind('.infscr');
				$('#resp_content_button_<?php echo $module->id; ?>','#responsive_loadmore_<?php echo $module->id ?>').click(function(e){
						loading_state();	
						e.preventDefault();
       					$container.infinitescroll('retrieve');
					 return false;;
					});
						
				<?php }?>
			};
			
			
		$container.imagesLoaded(function(){
			start();
			scroll();
		});
		
		// fancybox
		$('.fancybox').fancybox({
			prevEffect : 'none',
			nextEffect : 'none',
			width     :600,
			heidth    :200,
			maxWight  :800,
			maxHeight :400,
			autoSize  : false
		});
	})('#<?php echo 'sj-k2-responsive-content-'.$module->id;?>');
});
//-->
</script>
<?php $class_respl= 'sj-respl01-'.$params->get('nb-column1',6).' sj-respl02-'.$params->get('nb-column2',4).' sj-respl03-'.$params->get('nb-column3',2).' sj-respl04-'.$params->get('nb-column4',1) ?>
<?php if (!empty($options->pretext)) { ?>
	<div class="sj-k2-responsive-content-introtext ">
		<?php echo $options->pretext; ?>
	</div>
<?php } ?>
<!-- Begin sj-resonsive-content -->
<div id="<?php echo 'sj-k2-responsive-content-'.$module->id;?>" class="sj-k2-responsive-content <?php echo $class_respl?> <?php echo ($params->get('loadmore_type') == 0)?'loadmore-click':'';?>">
<?php foreach($list  as $item){
	$img = modSjK2ResContentHelper::getK2Image($item, $params);
	?>
	<div class="responsive-content-box">
		<div class="responsive-content-box-inner">
			<div class="responsive-content-box-bg">
				<div class="item <?php echo "id".$item->id?>">
					<?php
			        	$img = modSjK2ResContentHelper::getK2Image($item, $params);
			        	if($img){
			        	?>
		       		<div class="item-img">
			        		<img class="responsive-loadimage" title="<?php echo $item->title; ?>" alt="<?php echo $item->title; ?>"  src="<?php echo ImageHelper::init($img)->src(); ?>"  style="display:none;" />
			        		<?php 	echo modSjK2ResContentHelper::imageTag($img); ?>
			        	<?php if($params->get('itemDateCreated', 1)== 1 || $params->get('itemHits',1) == 1 || $params->get('itemCommentsCounter',1) == 1) {?>
			      		<div class="item-caption">
			      			<?php if($params->get('itemDateCreated',1) == 1) {?>
			           		<span class="item-date">
			                	<?php echo  JHTML::_('date', $item->created,JText::_('DATE_FORMAT_LC3')) ?>
			             	</span>
			             	<?php }?>
			             	<?php if($params->get('itemHits',1) == 1 || $params->get('itemCommentsCounter',1) == 1) {?>
			             	<span class="item-hit-comment">
				             	<?php if($params->get('itemHits',1) == 1) {?>
				              	<span class="item-hit">
				                	<?php if ((int)$item->hits>1){ ?>
				                		<?php echo $item->hits ?> hits
				                	<?php } else {?>
				                		<?php echo $item->hits ?> hit
				                	<?php }?>
				           		</span>
				           		<?php }?>
				           		<?php if($params->get('itemCommentsCounter',1) == 1) {?>
				             	<span class="item-comment">
				             		<?php echo $item->numOfComments; ?>
				             		<span class="item-comment-bottom"></span>
				             	</span>
				             	<?php }?>
			             	</span>
			             	<?php }?>
						</div>
						<?php } ?>
						<div class="item-img-mask"></div>
						<div class="item-spacer"></div>
			  		</div>
			  		<?php }?>
			  		<?php if($params->get('itemTitle',1) == 1){?>
		    		<h4 class="item-title">
		            	<?php echo modSjK2ResContentHelper::truncate($item->title, $params->get('itemTitleWordLimit',25)); ?>
		         	</h4>
		         	<?php } ?>
		         	<?php if($params->get('itemIntroText', 1) == 1 && $item->displayIntrotext !='') {?>
		       		<div class="item-desc">
		       			<?php echo modSjK2ResContentHelper::truncate($item->displayIntrotext, $params->get('itemIntroTextWordLimit',200)); ?>
		            </div>
		           <?php }?>
		           <?php if($params->get('item_readmore_display', 0) == 1){?>
						<div class="item-readmore">
							<a href="<?php echo $item->link ?>" <?php echo modSjK2ResContentHelper::parseTarget($options->link_target);?> title="<?php echo $item->title?>" >
								 <?php echo $params->get('item_readmore_text','read more..') ?>
							</a>
						</div>
					<?php } ?>
				</div>
				<div class="responsive-content-box-mask">
				</div>
				<?php if ($options->link_target=='_windowopen'){
					$link = $item->link;
					$link .= (strpos($item->link,'?'))?'&tmpl=component':'?tmpl=component';
					?>
					<a class="mask-img fancybox fancybox.iframe <?php echo ($img)?'':'item-img-mask'?>" data-fancybox-group="gallery" href="<?php  echo $link; ?>" title="<?php echo $item->title;?> "></a>
				<?php } else {?>
					<a class="mask-img <?php echo ($img)?'':'item-img-mask'?>" href="<?php echo $item->link ?>" <?php echo modSjK2ResContentHelper::parseTarget($options->link_target);?> title="<?php echo $item->title?>" ></a>
				<?php }?>
			</div>
    	</div>
   	</div>
<?php } ?>
</div>

<!-- End sj-resonsive-content -->

<?php if ( !empty($options->posttext)){ ?>
	<div class="sj-k2-responsive-content-footertext ">
		<?php echo $options->posttext; ?>
	</div>
<?php } ?>

<?php if($params->get('loadmore_type') == 1){?>
<nav id="page_nav_<?php echo $module->id;?>" style="clear: both;">
	<a class="respl-button" href="<?php echo (string)$uri; ?>"></a>
</nav>
<?php  } else {?>
<nav id="responsive_loadmore_<?php echo $module->id ?>" style="margin-top:30px;" class="responsive-content-loadmore">
	<a class="resp-content-button" id="resp_content_button_<?php echo $module->id; ?>" href="<?php echo (string)$uri; ?>">
		<span class="loader-image"></span>
		<span class="loader-label" >Load More</span>
	</a>
</nav>
<?php }?>