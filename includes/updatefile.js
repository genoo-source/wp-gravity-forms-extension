jQuery(document).ready(function () {
  jQuery(".leadtypecheckbox h1").addClass("editheader");


  jQuery(document).on(
    "click",
    ".add-buttons > .edit-form-footer > input",
    function (event) {
      event.preventDefault();

      var edit = jQuery("#edit-title-input").val();
      const params = new URLSearchParams(window.location.search);
      var origin = window.location.origin;
      var postid = params.get("id");
      jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        cache: false,
        data: {
          action: "saveformdata",
          formname: edit,
          post_id: postid,
          formid: postid
        },
        success: function (data) {},
        error: function (errorThrown) {
          console.log(errorThrown);
        }
      });
    }
  );

    jQuery(function ($) {
      jQuery("#field_thirdparty_input").on("change", function () {
  
    if (jQuery(this).val() === "leadtypes")
    {
      jQuery(".leadtypecheckbox").css("height", "200px");
     jQuery('.leadtypecheckbox').css("overflow","auto");
     jQuery(".leadtypecheckbox").css("display","block");
     jQuery(".leadtypeselected").css("display","block");

     jQuery(".encrypt_setting_leadtypes").css("display","block");
    

    }
      else {
      jQuery(".leadtypecheckbox").css("display","none");
      jQuery(".leadtypeselected").css("display","none");

      
      }
      });
  });
  jQuery('.encrypt_setting_leadtypes > input[type="checkbox"]').change(function() {

    var labelattribute = jQuery(this).attr("id");
    var myString = labelattribute.substring(19);
    var labelvalueoption = '.field_id_input_label_text' + myString;
    if (jQuery(this).is(':checked')) {
      jQuery(this).css('display','block');
    }
    else{
     
      jQuery(this).css('display','none');
      jQuery(this).val('');
    }

});



jQuery(".leadtypeselected").on("click",function(){
 // jQuery(".leadtypecheckbox").removeAttr("style");
  jQuery(".leadtypecheckbox").scrollTop(0);
  jQuery(".leadtypecheckbox h1").removeClass("editheader");


  
jQuery('.encrypt_setting_leadtypes > input[type="checkbox"]').each(function() {
  if (jQuery(this).is(':checked')) {
    var labelattribute = jQuery(this).attr("id");
  
    var myString = labelattribute.substring(19);
    
    var labelshow = '.field_id_input_label_text' + myString;
  
jQuery(labelshow).css('display','block');

jQuery(".leadtypeselected").css("display","none");
jQuery(".leadtypeupdate").css("display","block");

  }
  else{

jQuery(this).parent().css("display","none");
  }
}); 

});

jQuery(".leadtypeupdate").on("click",function(){

  var allvalues = [];

 jQuery('.encrypt_setting_leadtypes > input[type="checkbox"]:checked').each(function() {
  var data = {};
  var labelattribute = jQuery(this).attr("id");
  var myString = labelattribute.substring(19);
  var labelshow = '.field_id_input_label_text' + myString;

  var field_id_input_value_text = '.field_id_input_value_text' + myString;



  data.label = jQuery(labelshow).val();
  data.labelvalue =  jQuery(field_id_input_value_text).val(); 
  
  allvalues.push(data);

 });

 var fieldid = jQuery("#sidebar_field_label").attr("data-fieldid");
  
 var formid = form['id'];

  jQuery.ajax({
      url: ajaxurl,
      type: "POST",
      cache: false,
      data: {
        action: "lead_type_option_submit",
        inservalues:allvalues,
        field_id :fieldid,
        form_id:formid,
      },
      success: function (data) {

        jQuery('.encrypt_setting_leadtypes').css('display','none');
        jQuery('.leadtypeupdate').css('display','none');
        jQuery(".leadtypecheckbox h1").addClass("editheader");
        jQuery(".leadtypecheckbox").removeAttr("style");

      },
  
      error: function (errorThrown) {
        console.log(errorThrown);
      },
    });

});
});
