/* JCE Editor - 2.5.1 | 26 May 2015 | http://www.joomlacontenteditor.net | Copyright (C) 2006 - 2015 Ryan Demmer. All rights reserved | GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html */
var ColorPicker={settings:{},init:function(){var self=this,ed=tinyMCEPopup.editor,color=tinyMCEPopup.getWindowArg('input_color')||'#FFFFFF',doc=ed.getDoc();var stylesheets=[];if(doc.styleSheets.length){$.each(doc.styleSheets,function(i,s){if(s.href&&s.href.indexOf('tiny_mce')==-1){stylesheets.push(s);}});}
$('#tmp_color').val(color).colorpicker($.extend(this.settings,{dialog:true,insert:function(){return ColorPicker.insert();},close:function(){return tinyMCEPopup.close();},stylesheets:stylesheets,custom_colors:ed.getParam('colorpicker_custom_colors')}));$('button#insert').button({icons:{primary:'ui-icon-check'}});$('#jce').css('display','block');},insert:function(){var color=$("#colorpicker_color").val(),f=tinyMCEPopup.getWindowArg('func');tinyMCEPopup.restoreSelection();if(f)
f(color);tinyMCEPopup.close();}};tinyMCEPopup.onInit.add(ColorPicker.init,ColorPicker);