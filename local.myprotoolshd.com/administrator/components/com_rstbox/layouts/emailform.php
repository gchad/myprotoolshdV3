<?php
/**
 * @package     Responsive Scroll Triggered Box
 * @subpackage  com_rstbox
 *
 * @copyright   Copyright (C) 2014 Tassos Marinos - http://www.tassos.gr
 * @license     GNU General Public License version 2 or later; see http://www.tassos.gr/license
 */

	defined('JPATH_BASE') or die;
	$box = $displayData;

	$form_url = $box->settings->mc_url;
	$form_labels = $box->settings->mc_showlabels;
	$form_labels_show = (($form_labels=="0") || ($form_labels=="2")) ? true : false;
	$form_placeholders = (($form_labels=="1") || ($form_labels=="2")) ? true : false;
	$form_header = $box->settings->mc_header;

	/* Prepare Button Styles */
    $btn_bg = ($box->settings->mc_submit_bg) ? "background-color:".$box->settings->mc_submit_bg.";" : "";
    $btn_color = ($box->settings->mc_submit_color) ? "color:".$box->settings->mc_submit_color." ;" : "";
    $btn_style = $btn_bg.$btn_color;

    /* Prepare Fields Array */
	$field_mail = new stdclass;
	$field_mail->name = $box->settings->mc_email_namefield;
	$field_mail->type = "email";
	$field_mail->label = $box->settings->mc_email_name;
	$field_mail->value = null;
	$field_mail->required = true;
	$field_mail->active = true;				

	$field1 = new stdclass;
	$field1->name = $box->settings->mc_merge1_name;
	$field1->type = $box->settings->mc_merge1_type;
	$field1->label = $box->settings->mc_merge1_label;
	$field1->value = $box->settings->mc_merge1_value;
	$field1->required = $box->settings->mc_merge1_required;
	$field1->active = $box->settings->mc_merge1_active;			

	$field2 = new stdclass;
	$field2->name = $box->settings->mc_merge2_name;
	$field2->type = $box->settings->mc_merge2_type;
	$field2->label = $box->settings->mc_merge2_label;
	$field2->value = $box->settings->mc_merge2_value;
	$field2->required = $box->settings->mc_merge2_required;			
	$field2->active = $box->settings->mc_merge2_active;			

	$fields = array($field_mail, $field1, $field2);

?>

<form action="<?php echo $form_url; ?>" method="post" id="mcform" name="mcform" target="_blank">
	<?php if ($form_header) { ?>
		<div class="rstbox_header"><?php echo $form_header ?></div>
	<?php } ?>

	<?php foreach ($fields as $field) { ?>
		<?php if ($field->active) { ?>
		<div class="rstbox_field_row">
			<?php if ($form_labels_show) { ?><label for="<?php echo $field->name ?>"><?php echo $field->label ?></label><?php } ?>
			<input class="rstbox_input" type="<?php echo $field->type ?>" name="<?php echo $field->name ?>" <?php if ($form_placeholders) { ?> placeholder="<?php echo $field->label ?>" <?php } ?> id="<?php echo $field->name ?>" value="<?php echo $field->value ?>" <?php echo ($field->required) ? "required" : "" ?>>
		</div>
		<?php } ?>
	<?php } ?>
	
	<div class="rstbox_footer">
    	<button class="rstbox_btn" type="submit" name="subscribe" style="<?php echo $btn_style ?>">
    		<?php echo $box->settings->mc_submit ?>
    	</button>
    </div>
</form>
