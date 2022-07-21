jQuery(document).ready(function () {
  jQuery(".leadtypecheckbox h1").addClass("editheader");

  jQuery(".folderdelete").on("click", function () {
    var idvalue = jQuery(this).attr("id");

    var objectvalue = jQuery(this).closest(".folderupdates");
    var parentDiv = jQuery(objectvalue);
    parentDiv.find(".leadfolder_encrypt_value" + idvalue).trigger("click");
    parentDiv.find(".leadfolder_value" + idvalue).css("display", "none");
    var fieldid = jQuery("#sidebar_field_label").attr("data-fieldid");

    var formid = form["id"];

    var formid = form["id"];
    jQuery.ajax({
      url: ajaxurl,
      type: "POST",
      cache: false,
      data: {
        action: "folderoptiondelete",
        folder_id: idvalue,
        fieldid: fieldid,
        formid: formid,
      },
      success: function (data) {},
      error: function (errorThrown) {
        console.log(errorThrown);
      },
    });
  });
  jQuery(".leadtypedelete").on("click", function () {
    var idvalue = jQuery(this).attr("id");

    var objectvalue = jQuery(this).closest(".leadtypeselectoption");
    var parentDiv = jQuery(objectvalue);
    //  parentDiv.find('.leadtype_value_label'+idvalue).trigger("click");
    var fieldid = jQuery("#sidebar_field_label").attr("data-fieldid");

    parentDiv.find(".updateoptions").css("display", "none");
    parentDiv.find(".leadtypeupdatebtn").css("display", "none");

    var formid = form["id"];

 
    jQuery.ajax({
      url: ajaxurl,
      type: "POST",
      cache: false,
      data: {
        action: "lead_type_delete",
        inservalues: idvalue,
        field_id: fieldid,
        form_id: formid,
      },
      success: function (data) {
             parentDiv
      .find(".leadtype_value_label" + idvalue)
      .attr("checked", false)
      .trigger("click");

    parentDiv
      .find(
        ".leadtypeupdates > .lead_value" +
          idvalue +
          "> .encrypt_setting_option_leads > label"
      )
      .html("");
          
      },
      error: function (errorThrown) {
        console.log(errorThrown);
      },
    });
  });

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
          formid: postid,
        },
        success: function (data) {},
        error: function (errorThrown) {
          console.log(errorThrown);
        },
      });
    }
  );

  jQuery(function ($) {
    jQuery("#field_thirdparty_input").on("change", function () {
      if (jQuery(this).val() === "leadtypes") {
        jQuery(".folderupdates").css("display", "block");

        jQuery(".encrypt_section_label").css("display", "none");
        jQuery(".leadtypesarrow").css("display", "none");

        //    jQuery(".leadtypesarrow").css("display","none");
        jQuery(".leadtypeselected").css("display", "none");

        //jQuery(".leadtypecheckbox").css("height", "200px");
        jQuery(".leadtypecheckbox").css("overflow", "auto");
        jQuery(".leadfolderarrow").trigger("click");
      } else {
        jQuery(".folderupdates").css("display", "none");

        //   jQuery(".leadtypecheckbox").css("display","none");
        jQuery(".leadtypeselected").css("display", "none");
        jQuery(".encrypt_section_label").css("display", "none");
        jQuery(".leadtypesarrow").css("display", "none");
      }
    });
  });

  jQuery(".leadtypeselected").on("click", function () {
    var objectvalue = jQuery(this).closest(".leadtypeselectoption");
    var parentDiv = jQuery(objectvalue);
    parentDiv.find(".updateoptions").css("display", "none");
    parentDiv.find(".leadtypeupdatebtn").css("display", "none");

    parentDiv.find(".leadtypeselected").css("display", "none");

    var allvalues = [];

    parentDiv
      .find('.encrypt_setting_leadtypes > input[type="checkbox"]:checked')
      .each(function () {
        var data = {};
        var labelattribute = jQuery(this).attr("dataidvalue");
        var dataleadfoldername = jQuery(this).attr("leadfoldername");

        data.label = dataleadfoldername;
        data.labelvalue = labelattribute;

        allvalues.push(data);
      });
    
      if(allvalues.length!=0){
    parentDiv.find(".loading > p").css("display", "block");
 
    var fieldid = jQuery("#sidebar_field_label").attr("data-fieldid");

    var formid = form["id"];

    jQuery.ajax({
      url: ajaxurl,
      type: "POST",
      cache: false,
      data: {
        action: "get_lead_options",
        field_id: fieldid,
        form_id: formid,
      },
      success: function (data) {

        parentDiv.find(".updateoptions").html("");
            
         parentDiv
            .find(".updateoptions")
            .append('<h4>Edit Label Here:</h4>');
        jQuery.each(data, function (key, value) {
            
         parentDiv.find(".loading > p").css("display", "none");

          parentDiv
            .find(".updateoptions")
            .append(
              '<input type="text" id="' +
                 key +
                '" datavalueselectoption="' +
                value +
                '" class="appendedleadtypes"  value="' +
                value +
                '" />'
            );
        });

  if (data.length !== 0) {
          parentDiv.find(".updateoptions").css("display", "block");

          parentDiv.find(".leadtypeupdatebtn").css("display", "block");
        } else {
          parentDiv.find(".updateoptions").css("display", "none");

          parentDiv.find(".leadtypeupdatebtn").css("display", "none");
        }
     
      },

      error: function (errorThrown) {
        console.log(errorThrown);
      },
    });
      }
  });

  jQuery(".leadtypeupdatecancel").on("click", function () {
    var objectvalue = jQuery(this).closest(".leadtypeselectoption");
    var parentDiv = jQuery(objectvalue);

    parentDiv.find(".updateoptions").html("");
  });

  jQuery("#leadtypeupdate").on("click", function (e) {
    var allvalues = [];

    var objectvalue = jQuery(this).closest(".leadtypeselectoption");
    var parentDiv = jQuery(objectvalue);
    
    parentDiv.find(".leadtypeselected").css("display", "block");

    jQuery(".leadtypecheckbox").removeAttr("style");

    var fieldid = jQuery("#sidebar_field_label").attr("data-fieldid");

    var formid = form["id"];
    parentDiv.find(".appendedleadtypes").each(function () {
      var data = {};
      var leadvalue = jQuery(this).attr("id");
      var leadoption = jQuery(this).val();

      if (leadoption == "") {
        jQuery(".appendedleadtypes").css(
          "border",

          "2px solid red"
        );

        e.stopImmediatePropagation();
        return false;
      } else {
        var datavalueselectoption = jQuery(this).attr("datavalueselectoption");
        var labelclassname = ".lead_value" + leadvalue;

        data.label = leadoption;
        data.labelvalue = leadvalue;

        allvalues.push(data);

        jQuery(".appendedleadtypes").removeAttr("style");
      }
    });

    if (jQuery.isEmptyObject(allvalues)) {
      jQuery(".appendedleadtypes").css(
        "border",

        "2px solid red"
      );
    } else {
      parentDiv.find(".updateoptions").css("display", "none");
      parentDiv.find(".leadtypeupdatebtn").css("display", "none");
      jQuery(".leadtypesarrow").trigger("click");

      jQuery.each(allvalues, function (key, value) {
        var folderid = value.labelvalue.split("-")[1];

        var leadidvalue = value.labelvalue.split("-")[0];
        parentDiv
        .find(
          ".leadtypeupdates > .lead_value" +
            leadidvalue +
            "> .encrypt_setting_option_leads > label"
        )
        .html("<span class=editlabelheader>Edited Label : </span><span>" + value.label + "</span>");
        parentDiv
          .find(
            ".leadtypeupdates > .lead_value" +
              leadidvalue +
              "> .encrypt_setting_option_leads > label"
          )
          .css("display", "block");
      });
      jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        cache: false,
        data: {
          action: "lead_type_option_submit",
          inservalues: allvalues,
          field_id: fieldid,
          form_id: formid,
        },
        success: function (data) {},

        error: function (errorThrown) {
          console.log(errorThrown);
        },
      });
      jQuery(".appendedleadtypes").removeAttr("style");
    }
  });
  jQuery(document).on(
    "change",
    ".encrypt_setting_folders > input[type='checkbox']",
    function (event) {
      var objectvalue = jQuery(this).closest(".folderupdates");
      var foldername = jQuery(this).attr("leadfoldername");
      var folderid = jQuery(this).attr("dataidvalue");
      var fieldid = jQuery("#sidebar_field_label").attr("data-fieldid");

      jQuery(".updateoptions").css("display", "none");
      jQuery(".leadtypeupdatebtn").css("display", "none");

      var formid = form["id"];
      var parentDiv = jQuery(objectvalue);
      if (!jQuery(this).is(":checked")) {
        jQuery.ajax({
          url: ajaxurl,
          type: "POST",
          cache: false,
          data: {
            action: "folderoptiondelete",
            field_id: fieldid,
            form_id: formid,
            folder_id: folderid,
          },
          success: function (data) {},

          error: function (errorThrown) {
            console.log(errorThrown);
          },
        });
      }
      if (jQuery(this).is(":checked")) {
        parentDiv.find(".leadfolder_value" + folderid).css("display", "block");
      } else {
        parentDiv.find(".leadfolder_value" + folderid).css("display", "none");
        jQuery(".leadtypeselectoption").css("display", "none");
      }
    }
  );

  jQuery(".leadfolderarrow").on("click", function (event) {
    jQuery(".leadtypefolder").toggle();
  });
  jQuery(".leadfolderselected").on("click", function (event) {
    var dataleadid = [];

    var objectvalue = jQuery(this).closest(".folderupdates");
    var parentDiv = jQuery(objectvalue);
    parentDiv
      .find(".leadtypefolder > .encrypt_setting_folders  > input:checked")
      .each(function () {
        dataleadid.push(jQuery(this).attr("dataidvalue"));
      });

    jQuery(".encrypt_setting_leadtypes > input[type='checkbox']").each(
      function () {
        var objectvalue = jQuery(this).closest(".encrypt_setting_leadtypes");

        var parentDiv = jQuery(objectvalue);

        var leadfolderid = jQuery(this).attr("datafolder-id");

        if (dataleadid.length != 0) {
          if (jQuery.inArray(leadfolderid, dataleadid) != -1) {
            parentDiv.css("display", "block");
          } else {
            if (jQuery(this).is(":checked")) {
              jQuery(this).attr("checked", false).trigger("click");
            }

            parentDiv.css("display", "none");

            jQuery(".encrypt_section_label").css("display", "none");
          }
        }
      }
    );
    if (dataleadid.length != 0) {
      jQuery(".leadtypeselected").css("display", "block");
      jQuery(".leadtypesarrow").css("display", "block");
      jQuery(".encrypt_section_label").css("display", "block");
      jQuery(".leadtypesarrow").trigger("click");

      jQuery(".leadtypeselectoption").css("display", "block");

      jQuery(".leadtypeupdatebtn").css("display", "none");
    }
    jQuery(".leadtypeupdatebtn").css("display", "none");

    jQuery(".updateoptions").css("display", "none");
  });

  jQuery(document).on(
    "change",
    ".encrypt_setting_leadtypes > input[type='checkbox']",
    function (event) {
      var objectvalue = jQuery(this).closest(".leadtypeselectoption");
      var foldername = jQuery(this).attr("leadfoldername");
      var leadid = jQuery(this).attr("dataidvalue");
      var folderid = jQuery(this).attr("datafolder-id");

      var fieldid = jQuery("#sidebar_field_label").attr("data-fieldid");

      var formid = form["id"];
      var parentDiv = jQuery(objectvalue);
      
      parentDiv
        .find(
          ".leadtypeupdates > .lead_value" +
            leadid +
            "> .encrypt_setting_option_leads > label"
        )
        .html("");
            parentDiv.find(".leadtypeselected").css("display", "block");


      parentDiv.find(".updateoptions").html("");
      parentDiv.find(".leadtypeupdatebtn").css("display", "none");

      if (!jQuery(this).is(":checked")) {
        jQuery.ajax({
          url: ajaxurl,
          type: "POST",
          cache: false,
          data: {
            action: "lead_type_option_change_delete",
            inservalues: leadid,
            labelvalue: foldername,
            field_id: fieldid,
            form_id: formid,
            folder_id: folderid,
          },
          success: function (data) {},

          error: function (errorThrown) {
            console.log(errorThrown);
          },
        });
         parentDiv.find(".lead_value" + leadid).css("display", "none");
             parentDiv
      .find(
        ".leadtypeupdates > .lead_value" +
          leadid +
          "> .encrypt_setting_option_leads > label"
      )
      .html("");
      }
      if (jQuery(this).is(":checked")) {
        jQuery.ajax({
          url: ajaxurl,
          type: "POST",
          cache: false,
          data: {
            action: "lead_type_option_change_submit",
            inservalues: leadid,
            labelvalue: foldername,
            field_id: fieldid,
            form_id: formid,
            folder_id: folderid,
          },
          success: function (data) {},

          error: function (errorThrown) {
            console.log(errorThrown);
          },
        });

        parentDiv.find(".lead_value" + leadid).css("display", "block");
  
      } else {
        parentDiv.find(".lead_value" + leadid).css("display", "none");
            parentDiv
      .find(
        ".leadtypeupdates > .lead_value" +
          leadid +
          "> .encrypt_setting_option_leads > label"
      )
      .html("");
      }
    }
  );

  jQuery(".leadtypesarrow").on("click", function (event) {
    jQuery(".leadtypecheckbox").toggle();
  });

  jQuery("#leadtypeupdatecancel").on("click", function () {
    var objectvalue = jQuery(this).closest(".leadtypeselectoption");
    var parentDiv = jQuery(objectvalue);
    parentDiv.find(".updateoptions").html("");
    parentDiv.find(".leadtypeupdatebtn").css("display", "none");
    parentDiv.find(".leadtypeselected").css("display", "block");
  });

  jQuery(".leadtype_label").on("click", function (event) {
    jQuery(".leadtypecheckbox").toggle();
  });
  jQuery(".leadfolder").on("click", function (event) {
    jQuery(".leadtypefolder").toggle();
  });
});
