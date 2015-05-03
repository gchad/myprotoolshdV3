/**
 * @package         Responsive Scroll Triggered Box
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2015 TM Extensions All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

var jqRSTBox = jQuery.noConflict();

(function($) {
    $(document).ready(function() {
        /* Joomla <= 3.3.6 - Added change trigger when check the radio input */
        $('.btn-group label:not(.active)').click(function() {
            var input = $('#' + $(this).attr('for'));
            if (!input.prop('checked')) {
                input.trigger('change');
            }
        });
        
        $('#jform_boxtype').change(function() {
            $(".boxtypes > div").addClass("hide");
            $(".boxtypes > div#"+$(this).val()+"").removeClass("hide");
        }).trigger('change');

        /* Trigger Method */
        $("#jform_triggermethod").change(function() {
            val = $(this).val();
            switch (val) {
                case "pageheight":
                    disableObjects = ["prmtriggerelement"];
                    break;
                case "pageload":
                    disableObjects = ["prmtriggerelement", "prmtriggerpercentage","prmautohide"];
                    break;
                case "userleave":
                    disableObjects = ["prmtriggerelement", "prmtriggerpercentage","prmtriggerdelay","prmautohide"];
                    break;
                case "element":
                    disableObjects = ["prmtriggerpercentage"];
                    break;
            }

            form = $(this).closest("form#adminForm");

            form.find(".control-group").removeClass("hide");
            disableObjects.each(function(x) {
                form.find(".control-group."+x).addClass("hide");
            })
        }).trigger('change');

        /* Show/Hide Fieldsets */
        showOnFields = [
            {
                "trigger": $('input[name="jform[prm_assign_devices]"]'),
                "triggerValues": ["1","2"],
                "showField" : $(".prmassigndeviceslist")
            },
            {
                "trigger": $('input[name="jform[prm_overlay]"]'),
                "triggerValues" : ["1"],
                "showField": $(".prmoverlaycolor, .prmoverlaypercent, .prmoverlayclick")
            },
            {
                "trigger": $('input[name="jform[prm_allmenus]"]'),
                "triggerValues" : ["0"],
                "showField": $(".menuitems")
            },
            {
                "trigger": $('input[name="jform[prm_assign_lang]"]'),
                "triggerValues": ["1","2"],
                "showField": $(".prmassignlanglist")
            }
        ]

        $.each(showOnFields, function(key, value) {
            trigger = $(value.trigger);
            trigger.on("change", function() {

                valid = false;

                if (value.triggerValues == ":checked") {
                    if ($(this).is(":checked")) {
                        valid = true;
                    }
                }

                if ($.isArray(value.triggerValues)) {
                    if ($.inArray($(this).val(), value.triggerValues) > -1) {
                        valid = true;
                    }   
                }  

                if (valid) {
                    value.showField.slideDown("fast");
                } else {
                    value.showField.slideUp("fast");
                }

            });

            if (trigger.attr("type") == "radio") {
                trigger.filter(":checked").trigger("change"); 
            }
        });
    });
}(jqRSTBox));
