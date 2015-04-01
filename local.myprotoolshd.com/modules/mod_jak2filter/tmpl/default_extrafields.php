<?php
$i = 0;
foreach ($glist['items'] as $key => $exfield):
	$magicSelect = '';
	$fieldTypes = explode("_",$key);
	if($fieldTypes[0] == 'magicSelect'){
		$magicSelect = ' class="magic-select"';
	}
	$last = ($i == count($list)-1)?'class="li-last"':'';
	$i++;
	$style = '';
	if($params->get('ja_column') >0 && (($j) % $params->get('ja_column')) == 0){
		$clear = " clear:both;";
	}
	if($ja_column || $clear){
		$style ='style="'.$ja_column.$clear.'"';
	}
	$j++;

	?>
	<li <?php echo $style;?> <?php echo $magicSelect;?>>
		<?php echo $exfield; ?>
	</li>
	<?php
	$clear = '';
endforeach;
?>