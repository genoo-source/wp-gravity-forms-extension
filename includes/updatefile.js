jQuery(document).ready(function () {
  jQuery(".encrypt_setting_leadtypes").css("display","none");

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
     
       jQuery(".encrypt_setting_leadtypes").css("display","block");
    }
      else {
      jQuery(".encrypt_setting_leadtypes").html("");
      }
      });
  });


//jQuery('body').

});
