<?php
/**
* @package   JE K2 STORY
* @copyright Copyright (C) 2009 - 2010 Open Source Matters. All rights reserved.
* @license   http://www.gnu.org/licenses/lgpl.html GNU/LGPL, see LICENSE.php
* Contact to : emailtohardik@gmail.com, joomextensions@gmail.com
* Visit : http://www.joomlaextensions.co.in/
**/ 

defined('_JEXEC') or die('Restricted access');

$model = $this->getModel ( 'jesubmit' );
$setting = $model->getarticle();
//print_r ($setting);
//echo '<pre>';

?>
	<div style="font-size:16px; font-weight:bold;" align="center">
		<?php echo JText::_('TERMS_AND_CONDITION'); ?>
	</div>
<?php if(count($setting)!=0) { 

$user = $model->getuser($setting[0]->created_by);

?>	
<table class="contentpaneopen">
<tr>
<td class="contentheading">
	<?php echo $setting[0]->title;?>
</td>
</tr>
<tr>
	<td valign="top">
		<span class="small">
			<?php JText::printf( 'Written by '.$user[0]->name) ; ?>
		</span>
		&nbsp;&nbsp;
	</td>
</tr>
<tr>
	<td valign="top" class="createdate">
		<?php JText::printf('date '.$setting[0]->created); ?>
	</td>
</tr>

<tr>
	<td valign="top">
		<?php  echo $setting[0]->introtext; ?>
		&nbsp;&nbsp;
	</td>
</tr>
</table>
<?php } ?>