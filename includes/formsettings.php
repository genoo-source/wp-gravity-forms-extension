<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"> </script>
<?php 
if($is_active=='1'): ?>
<script>
jQuery(document).ready(function() {
    jQuery(".leadwebinars").css('display', 'block');
    jQuery("#gform_setting_leadwebinars > div > label").css('display', 'block');
});
</script>
<?php 
else : 
  $wpdb->update($gf_addon_wpextenstion, array('select_webinar'=>''),array('form_id' => $form_id_title));  ?>
<script>
$('.leadwebinars option:selected').removeAttr('selected');
</script>

<?php  endif; ?>

<script>
// script for call function of emails based on email folder id on change
jQuery(document).ready(function() {
    //Script for select leadtypes on change 
    //create lead type api call
    jQuery('.newleadtype').css('display', 'none');
    jQuery('.email-show').css('display', 'none');
    jQuery('#gform_setting_leademail > div > label').css('display', 'none');
    var typeid = jQuery('#selectleadtypes').val();
    if (typeid == '') {
        jQuery("[name='gform-settings-save']").prop('disabled', true);
        jQuery("#selectleadtypes").after("<div class='validationlead' style='color:red;margin-bottom: 20px;'>required</div>");
    } else {
        jQuery("[name='gform-settings-save']").removeAttr("disabled");
         jQuery(".validationlead").remove();
    }

    jQuery('#selectleadtypes').on('change', function() {
        var value = jQuery(this).val();
        if (value == '') {
            jQuery("[name='gform-settings-save']").prop('disabled', true);
             jQuery("#selectleadtypes").after("<div class='validationleadvalue' style='color:red;margin-bottom: 20px;'>required</div>");
            jQuery('.newleadtype').css('display', 'none');
            jQuery('#newleadtypecrt').prop('required', false);
            jQuery('#selectleadtypes').prop('required', false);
        } else if (value == 'createleadtype') {
            jQuery('.newleadtype').css('display', 'block');
            jQuery('#gform_setting_leadtypesaving').css('display', 'block');
            jQuery('#newleadtypecrt').prop('required', true);
             jQuery("#newleadtypecrt").after("<div class='validationtype' style='color:red;margin-bottom: 20px;'>required</div>");
            jQuery("[name='gform-settings-save']").prop('disabled', true);

        } else {
            jQuery("[name='gform-settings-save']").removeAttr("disabled");
            jQuery('.newleadtype').css('display', 'none');
            jQuery('#gform_setting_leadtypesaving').css('display', 'none');
            jQuery('#newleadtypecrt').prop('required', false);
            jQuery('#selectleadtypes').prop('required', false);
            jQuery(".validationleadvalue").remove();
            jQuery(".validationlead").remove();
            jQuery(".validationleadvaluefolder").remove();
            jQuery(".validationtype").remove();
            
        }

    });
    //Select lead folder on change
    jQuery('#selectleadtypefolders').on('change', function() {
        jQuery('.newleadtype').css('display', 'none');
        jQuery("#selectleadtypes").prop("required", true);
        var folder_id = jQuery(this).val();
        if (folder_id != '') {
            jQuery.ajax({
                url: '<?php echo admin_url("admin-ajax.php") ?>',
                type: 'POST',
                data: {
                    'action': 'leadtypefilter',
                    'folder_id': folder_id,
                },
                success: function(data) {
                    console.log(data);
                    jQuery("#selectleadtypes").empty();
                    jQuery("#selectleadtypes").append(
                        '<option value="">Select Lead Types</option><option value="">----------------------------------</option><option value="createleadtype">Create leadtypes</option>'
                    );
                    $.each(data, function(key, value) {
                        jQuery("#selectleadtypes").append('<option value="' + key +
                            '">' + value + ' </option>');
                    });
                      jQuery("#selectleadtypes").after("<div class='validationleadvaluefolder' style='color:red;margin-bottom: 20px;'>required</div>");
                       jQuery("[name='gform-settings-save']").prop('disabled', true);

                },

                error: function(errorThrown) {
                    console.log(errorThrown);
                }
            });
        } else {
            jQuery("[name='gform-settings-save']").prop('disabled', true);
        }

    });
    //  jQuery('')
    //create new lead type    
    jQuery('#leadtypesaving').on('click', function(event) {
        event.preventDefault();
        jQuery('#newleadtypecrt').prop('required', true);

        var getval = jQuery('#newleadtypecrt').val();
        var folderid = jQuery('#selectleadtypefolders').val();
        if (getval != '') {
            jQuery.ajax({
                url: '<?php echo admin_url("admin-ajax.php") ?>',
                type: 'POST',
                data: {
                    'action': 'createleadtype',
                    'leadtypevalue': getval,
                    'description': getval,
                    'mngdlistind': false,
                    'costforall': '',
                    'costperlead': '',
                    'sales_ind': 'no',
                    'system_ind': 'no',
                    'blog_commenters': 'no',
                    'blog_subscribers': 'no',
                    'folder_id': folderid,
                },

                success: function(data) {
                    console.log(data);
                    jQuery('#newleadtypecrt').prop('required', false);
                    jQuery("[name='gform-settings-save']").removeAttr("disabled");
                     jQuery(".validationleadvalue").remove();
                     jQuery(".validationtype").remove();
                     jQuery(".validationlead").remove();
                    jQuery('.newleadtype').css('display', 'none');
                    
                    jQuery('#selectleadtypes').append('<option value="' + data +
                        '" selected="selected">' + getval + '</option>');
                    jQuery("#selectleadtypes").val(data);
                    jQuery("#leadtypesaving").css("display","none");
               

                },

                error: function(errorThrown) {
                    console.log(errorThrown);
                }
            });
        }

    });

    //lead email folder on change
    jQuery('.leademailfolders').on('change', function() {
        var folderid = jQuery(this).val();
        if (folderid != '') {
            jQuery.ajax({
                url: '<?php echo admin_url( "admin-ajax.php" ) ?>',
                data: {
                    'action': 'getleadEmail',
                    'folderid': folderid
                },
                success: function(data) {
                    //success of function call to show in options

                    if (folderid) {
                        jQuery(".send-email").show();
                         jQuery('#gform_setting_leademail > div > label').css('display','block');
                        jQuery(".send-email").empty();
                        jQuery(".email-show").addClass("send-email");
                        jQuery(".send-email").append(
                            '<option value="">Select Email</option>');
                        $.each(data, function(key, value) {
                            jQuery(".send-email").append('<option value="' + value
                                .id + '">' + value.name + ' </option>');
                        });
                          jQuery(".email-show").after("<div class='validationemail' style='color:red;margin-bottom: 20px;'>required</div>");
                         jQuery("[name='gform-settings-save']").prop('disabled', true);  
                    } else {
                        jQuery(".email-show").prop("required", false);
                        jQuery(".validationemail").remove();
                        jQuery("[name='gform-settings-save']").removeAttr("disabled"); 
                        jQuery(".email-show").empty();
                        jQuery(".send-email").hide();
                         jQuery('#gform_setting_leademail > div > label').css('display','none');
                            }
                    if (data.length == 0) {
                        jQuery(".email-show").prop("required", false);
                        jQuery(".email-show").empty();
                          jQuery(".validationemail").remove();
                           jQuery("[name='gform-settings-save']").removeAttr("disabled"); 
                        jQuery(".send-email").append(
                            '<option value="">No Email Here</option>');
                    }
                },
                error: function(errorThrown) {
                    console.log(errorThrown);
                }
            });
        } else {
            jQuery(".email-show").css("display", "none");
             jQuery('#gform_setting_leademail > div > label').css('display','none');

        }
    });

    jQuery('.check_webinnar').on('click', function() {
        if ($(this).is(":checked")) {
            jQuery('.leadwebinars').css('display', 'block');
              jQuery("#gform_setting_leadwebinars > div > label").css('display', 'block');
                var webiner = jQuery(".leadwebinars").val();
              if(webiner==''){
            jQuery("[name='gform-settings-save']").prop('disabled', true);   
            jQuery(".leadwebinars").after("<div class='validation' style='color:red;margin-bottom: 20px;'>required</div>");
              }
              else
              {
              jQuery("[name='gform-settings-save']").removeAttr("disabled");    
              }
  
        } else if ($(this).is(":not(:checked)")) {
            jQuery('.leadwebinars').css('display', 'none');
              jQuery("#gform_setting_leadwebinars > div > label").css('display', 'none');
        jQuery("[name='gform-settings-save']").removeAttr("disabled"); 
        jQuery(".validation").remove();
        }

    });
    
    jQuery('.leadwebinars').on('change',function()
    {
        var webiner = jQuery(this).val();
        
        if(webiner=='')
        {
         jQuery("[name='gform-settings-save']").prop('disabled', true);   
           jQuery(".leadwebinars").after("<div class='validation' style='color:red;margin-bottom: 20px;'>required</div>");
        }
        else
        {
        jQuery(".validation").remove();
        jQuery("[name='gform-settings-save']").removeAttr("disabled");    
        }
        
    }
    );
    
        jQuery('#leademail').on('change',function()
         {
        var leademail = jQuery(this).val();
        
        if(leademail=='')
        {
         jQuery("[name='gform-settings-save']").prop('disabled', true);   
           jQuery("#leademail").after("<div class='validationemail' style='color:red;margin-bottom: 20px;'>required</div>");
        }
        else
        {
        jQuery(".validationemail").remove();
        jQuery("[name='gform-settings-save']").removeAttr("disabled");    
        }
        
    }
    );


});
</script>

<?php if(!empty($select_folder_id)) : ?>
<script>
jQuery(document).ready(function() {
    $(".send-email").show();
    jQuery('#gform_setting_leademail > div > label').css('display','block');
});
</script>
<?php   
     endif;
if(empty($select_email_id)) : ?>
<script>
jQuery(document).ready(function() {
    jQuery(".email-show").prop("required", false);
});
</script>
<?php   
       endif;
   ?>
<style>
.email-show {
    display: none;
}

.newleadtype {
    display: none;
}

#gform_setting_leadtypesaving {
    display: none;
}

.leadwebinars{
    display: none;
}
#gform_setting_leadwebinars > div > label{
    display: none;
}
#gform_setting_leademail > div >label
{
 display: none;    
}

</style>