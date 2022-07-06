<?php
/*
Plugin Name: Gravity Forms WPMktgEngine Extension
Description: This plugin requires the WPMKtgEngine or Genoo plugin installed before order to activate.
Version: 2.2.16
Requires PHP: 7.1
Author: Genoo LLC
*/
/*
    Copyright 2015  WPMKTENGINE, LLC  (web : http://www.genoo.com/)
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
register_activation_hook(__FILE__, function () {
    // Basic extension data
    global $wpdb;
    $fileFolder = basename(dirname(__FILE__));
    $file = basename(__FILE__);
    $filePlugin = $fileFolder . DIRECTORY_SEPARATOR . $file;
    // Activate?
    $activate = false;
    $isGenoo = false;
    // Get api / repo
    if (
        class_exists("\WPME\ApiFactory") &&
        class_exists("\WPME\RepositorySettingsFactory")
    ) {
        $activate = true;
        $repo = new \WPME\RepositorySettingsFactory();
        $api = new \WPME\ApiFactory($repo);
        if (class_exists("\Genoo\Api")) {
            $isGenoo = true;
        }
    } elseif (
        class_exists("\Genoo\Api") &&
        class_exists("\Genoo\RepositorySettings")
    ) {
        $activate = true;
        $repo = new \Genoo\RepositorySettings();
        $api = new \Genoo\Api($repo);
        $isGenoo = true;
    } elseif (
        class_exists("\WPMKTENGINE\Api") &&
        class_exists("\WPMKTENGINE\RepositorySettings")
    ) {
        $activate = true;
        $repo = new \WPMKTENGINE\RepositorySettings();
        $api = new \WPMKTENGINE\Api($repo);
    }
    // 1. First protectoin, no WPME or Genoo plugin
    if ($activate == false && $isGenoo == false) { ?>
  <div class="alert">
<p style="font-family:Segoe UI;font-size:14px;">This plugin requires the WPMKtgEngine or Genoo plugin installed  order to activate</p>
</div>
    <?php
    die();
    genoo_wpme_deactivate_plugin(
        $filePlugin,
        "This extension requires WPMktgEngine or Genoo plugin to work with."
    );
    } else {// Make ACTIVATE calls if any?}
        //creating tables setting save
        $sql = "CREATE TABLE {$wpdb->prefix}gf_settings (
            id mediumint(8) unsigned not null auto_increment,
            form_id mediumint(8) unsigned not null,
            is_active tinyint(1),
            select_lead_folder varchar(255),
            select_leadtype  varchar(255),
            select_folder  varchar(255),
            select_email varchar(255),
            select_webinar  varchar(250),
            source varchar(250),
            PRIMARY KEY  (id),
            UNIQUE KEY form_id (form_id)
                  ) $charset_collate;";
        gf_upgrade()->dbDelta($sql);

        $leadsql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}leadtype_form_save (
            id int(11) unsigned not null auto_increment,
            form_id int(11) unsigned not null,
            field_id int(11) unsigned not null,
            label_name varchar(255),
            label_value int(11), PRIMARY KEY(id)) $charset_collate;";
        gf_upgrade()->dbDelta($leadsql);}
});

/**
 * Plugin Updates
 */

include_once plugin_dir_path(__FILE__) . "deploy/updater.php";
wpme_gravity_forms_updater_init(__FILE__);

add_action(
    "wpmktengine_init",
    function ($repositarySettings, $api, $cache) {
        // Use the Settings, Api or Cache to do things on load of WPME if you need to
        // For example, add custom settings to WPME screen
        add_filter(
            "wpmktengine_tools_extensions_widget",
            function ($array) {
                $array["Gravity Forms WPMktgEngine Extension"] =
                    '<span style="color:green">Active</span>' . $r;
                return $array;
            },
            10,
            1
        );
        add_filter(
            "wpmktengine_settings_sections",
            function ($sections) {
                $sections[] = [
                    "id" => "Extension",
                    "title" => __("Extension", "wpmktengine"),
                ];
                return $sections;
            },
            10,
            1
        );
        add_filter(
            "wpmktengine_settings_fields",
            function ($fields) {
                $fields["Extension"] = [
                    [
                        "name" => "extension_cipher_key",
                        "id" => "extension_cipher_key",
                        "label" => __("Cipher", "wpmktengine"),
                        "type" => "text",
                        "default" => "",
                        "attr" => [
                            "style" => "display: block",
                        ], // Custom attributes, js etc.
                        "desc" => __("Description", "wpmktengine"),
                    ],
                    [
                        "label" => __("Dropdown", "wpmktengine"),
                        "name" => "extension_dropdown_key",
                        "id" => "extension_dropdown_key",
                        "type" => "select",
                        "options" => [
                            0 => "Select",
                        ],
                    ],
                ];
                return $fields;
            },
            10,
            1
        );
    },
    10,
    3
);
add_action("gform_after_submission", "access_entry_via_field", 10, 2);
add_filter("gform_pre_render", "gw_conditional_requirement");
add_filter("gform_pre_validation", "gw_conditional_requirement");
function gw_conditional_requirement($form)
{
    foreach ($form["fields"] as $field) {
        if ($field["type"] == "email") {
            $field->isRequired = true;
        }
    }
    return $form;
}

function access_entry_via_field($entry, $form)
{
    global $wpdb, $WPME_API;

    $id = isset($entry["form_id"]) ? $entry["form_id"] : 0;
    if ($id != 0):
        $gf_addon_wpextenstion = $wpdb->prefix . "gf_settings";
        $form_settings = $wpdb->get_row(
            "SELECT * from $gf_addon_wpextenstion WHERE form_id = $id"
        );
        $select_folder_id = isset($form_settings->select_folder)
            ? $form_settings->select_folder
            : "";
        $select_lead_id = isset($form_settings->select_leadtype)
            ? $form_settings->select_leadtype
            : "";
        $select_email_id = isset($form_settings->select_email)
            ? $form_settings->select_email
            : "";
        $select_webinar = isset($form_settings->select_webinar)
            ? $form_settings->select_webinar
            : "";
        $source = isset($form_settings->source) ? $form_settings->source : "";
        if ($select_lead_id != "" && $source != ""):
            $values = [];
            $leadvalues = [];
            $values["form_name"] = $form["title"];
            $values["client_ip_address"] = $entry["ip"];
            $values["lead_type_id"] = $select_lead_id;
            $values["source"] = $source;
            //$values['form_type'] = 'opt-in form';
            $values["page_url"] = $entry["source_url"];
            $values["form_type"] = "GF";
            if (!empty($select_email_id)):
                $values["confirmation_email_id"] = $select_email_id;
            endif;
            if (!empty($select_webinar)):
                $values["webinar_id"] = $select_webinar;
            endif;
            foreach ($form["fields"] as $field):
                if ($field["type"] == "email"):
                    $values["email"] = $entry[$field["id"]];
                endif;

                if ($field["type"] == "phone" && !empty($entry[$field["id"]])):
                    $values["phone"] = $entry[$field["id"]];
                endif;
                if (
                    $field["type"] == "website" &&
                    !empty($entry[$field["id"]])
                ):
                    $values["web_site_url"] = $entry[$field["id"]];
                endif;
                if ($field["type"] == "address"):
                    $field_id = $field["id"];
                    $values["address1"] = $entry[$field_id . ".1"];
                    $values["address2"] = $entry[$field_id . ".2"];
                    $values["city"] = $entry[$field_id . ".3"];
                    $values["state"] = $entry[$field_id . ".4"];
                    $values["province"] = $entry[$field_id . ".4"];
                    $values["zip"] = $entry[$field_id . ".5"];
                    $values["country"] = $entry[$field_id . ".6"];
                endif;
                if ($field["type"] == "consent"):
                    $values["c00gdprconsent"] =
                        $entry[$field["id"] . ".1"] != 1 ? "" : 1;
                    if (!empty($field->description)):
                        $values["c00gdprconsentmsg"] = $field->description;
                    endif;
                endif;
                if ($field["type"] == "name"):
                    $field_id = $field["id"];
                    $values["first_name"] = $entry[$field_id . ".3"];
                    $values["last_name"] = $entry[$field_id . ".6"];
                endif;
                $all_default_types = [
                    "textarea",
                    "text",
                    "multiselect",
                    "checkbox",
                    "number",
                    "captcha",
                    "fileupload",
                    "list",
                    "product",
                    "quantity",
                    "creditcard",
                    "post_title",
                    "html",
                    "select",
                    "page",
                    "section",
                    "radio",
                    "post_category",
                    "post_image",
                    "post_tags",
                    "post_excerpt",
                    "post_custom_field",
                    "option",
                    "total",
                    "shipping",
                    "post_content",
                    "date",
                    "time",
                    "hidden",
                ];
                //check all default types which is not a premapped types
                if (
                    in_array($field["type"], $all_default_types) &&
                    !empty($entry[$field["id"]]) &&
                    !empty($field->thirdPartyInput)
                ):
                    $firstindex = strstr($field->thirdPartyInput, "c00");
                    $lastindex = strstr($field->thirdPartyInput, "date");
                    if ($firstindex == true && $lastindex == true):
                        $date = date_create($entry[$field["id"]]);
                        $date = date_format($date, "Y-m-d");
                        $values[$field->thirdPartyInput] =
                            $date . "T" . "00:00:00+00:00";
                    elseif ($firstindex == false && $lastindex == true):
                        $date = date_create($entry[$field["id"]]);
                        $date = date_format($date, "m/d/Y");
                        $values[$field->thirdPartyInput] = $date;
                    elseif (
                        $field["type"] == "radio" &&
                        $field->thirdPartyInput == "c00eudatasubject" &&
                        !empty($entry[$field["id"]])
                    ):
                        $values["c00eudatasubject"] = "1";
                    elseif (!empty($entry[$field["id"]])):
                        $values[$field->thirdPartyInput] = $entry[$field["id"]];
                    endif;
                endif;

                if ($field["type"] == "select"):
                    if ($field->thirdPartyInput == "leadtypes"):
                        $leadvalues[] = $entry[$field["id"]];
                    endif;
                endif;
                if ($field["type"] == "multiselect"):
                    if ($entry[$field["id"]] != ""):
                        if ($entry[$field["id"]] != ""):
                            $leadvalues[] = $entry[$field["id"]];
                        endif;
                    endif;
                endif;

                if ($field["type"] == "checkbox"):
                    $inputs = $field->get_entry_inputs();
                    foreach ($inputs as $inputsfields):
                        if (!empty($entry[$inputsfields["id"]])):
                            $values[$field->thirdPartyInput] = "1";
                        endif;
                        if ($field->thirdPartyInput == "leadtypes"):
                            if ($entry[$inputsfields["id"]] != ""):
                                $leadvalues[] = $entry[$inputsfields["id"]];
                            endif;
                        endif;
                    endforeach;
                endif;
            endforeach;

            //changed callcustom api for leads submit
            if (method_exists($WPME_API, "callCustom")):
                try {
                    $response = $WPME_API->callCustom(
                        "/leadformsubmit",
                        "POST",
                        $values
                    );

                    $atts = apply_filters(
                        "genoo_wpme_lead_creation_attributes",

                        [],

                        "ecommerce-register-new-customer-lead"
                    );

                    foreach ($leadvalues as $leadvalues):
                        $WPME_API->setLeadUpdate(
                            $response->genoo_id,
                            $leadvalues,
                            $values["email"],
                            $entry[$field_id . ".3"],
                            $entry[$field_id . ".6"]
                        );
                    endforeach;

                    if ($WPME_API->http->getResponseCode() == 204):
                        // No values based on folderdid onchange! Ooops


                    elseif ($WPME_API->http->getResponseCode() == 200):
                    endif;
                } catch (Exception $e) {
                    if ($WPME_API->http->getResponseCode() == 404):


                        // Looks like leadfields not found
                    endif;
                }
            endif;
            $geno_ids = $response->genoo_id;
            setcookie("_gtld", $geno_ids, time() + 10 * 365 * 24 * 60 * 60);
        endif;
    endif;
}
add_action("wp_action_to_modify", function () {
    // Get WPME api object, same in both Genoo and WPME plugins
    global $WPME_API;
    // It's set on INIT, if it's not present, this hook runs too early and you
    if (!$WPME_API) {
        return;
    }
    // Do things
    // Get or save to settings repository
    $settings = $WPME_API->settingsRepo;
    // Value from custom setttings above
    $settingsCipher = $settings->getOption("extension_cipher_key", "Extension");
    // Do something with settings value from custom settings?
    // Make api calls, that are baked into the plugin
    // 1. Get lead by email address
    try {
        $lead = $WPME_API->getLeadByEmail("lead@email.com");
    } catch (\Exception $e) {
    }
    // 2. Call custom API, newly created, etc.
    if (method_exists($WPME_API, "callCustom")) {
        try {
            $product_id_external = 1;
            // Make a GET request, to Genoo / WPME api, for that rest endpoint
            $product = $WPME_API->callCustom(
                "/wpmeproductbyextid/" . $product_id_external,
                "GET",
                null
            );
            if ($WPME_API->http->getResponseCode() == 204) {
                // No product! Ooops
            } elseif ($WPME_API->http->getResponseCode() == 200) {
                // Good product in $product variable
            }
        } catch (Exception $e) {
            if ($WPME_API->http->getResponseCode() == 404) {
                // Looks like product not found
            }
        }
    }
    // 3. Api key?
    $apiKey = $WPME_API->key;
});

add_action("gform_loaded", ["GF__gravityform_Bootstrap", "load"], 5);
class GF__gravityform_Bootstrap
{
    public static function load()
    {
        if (!method_exists("GFForms", "include_addon_framework")) {
            return;
        }
        //include the class file
        require_once "class-gravityformextension.php";
        GFAddOn::register("Gravityformextension");
    }
}

function gf_gravityform()
{
    return Gravityformextension::get_instance();
}
add_action(
    "gform_field_standard_settings",
    function ($position, $form_id) {
        // position -1 for adding third party(Genoo/WPMktgEngine Field:) as last

        if ($position == -1):

            global $WPME_API, $wpdb;
            //calling leadfields api for showing dropdown
            if (method_exists($WPME_API, "callCustom")):
                try {
                    $customfields = $WPME_API->callCustom(
                        "/leadfields",
                        "GET",
                        null
                    );
                    if ($WPME_API->http->getResponseCode() == 204):
                        // No leadfields based on folderdid onchange! Ooops


                    elseif ($WPME_API->http->getResponseCode() == 200):
                        $customfieldsjson = $customfields;
                    endif;
                } catch (Exception $e) {
                }
            endif;
            // right after Admin Field Label
            // $pre_mapped_fields for should not show the premapped fields
            $pre_mapped_fields = [
                "First Name",
                "Last Name",
                "Email",
                "Address 1",
                "Address 2",
                "City",
                "State",
                "Postal Code",
                "Country",
                "Phone #",
                "Zip",
                "Province",
                "GDPR Consent",
                "GDPR Consent Text",
                "Web Site URL",
            ];
            ?>
    <div>
        <li class="thirdparty_input_setting field_setting">
         <label class="section_label" for="field_admin_label"><?php _e(
             "Genoo/WPMktgEngine Field:"
         ); ?></label>
         <select id="field_thirdparty_input" onchange="SetFieldProperty('thirdPartyInput', this.value);" class="fieldwidth-3" >
            <option value="">Do not map fields</option>
            
             <?php foreach ($customfieldsjson as $customfields):
                 //comparing labels with premapped labels in trim_custom_array
                 if (
                     !in_array(trim($customfields->label), $pre_mapped_fields)
                 ): ?>
                     <option value="<?php echo $customfields->key; ?>"> <?php echo trim(
    $customfields->label
); ?></option> <?php endif;
             endforeach; ?>
       </select>
            </li>
              <div>
        <li class="premappedname field_setting">
           
         <label class="section_label" for="field_admin_label"><?php _e(
             "Genoo pre Mapped With"
         ); ?></label>
        <input type="text" value="First name" />
          <input type="text" value="Last name" />
            </li>
            </div>
                 <div>
        <li class="premappedemail field_setting">
         <label class="section_label" for="field_admin_label"><?php _e(
             "Genoo pre Mapped With"
         ); ?></label>
        <input type="text" value="Email" />
        
            </li>
            </div>
                    <div>
        <li class="premappedaddress field_setting">
         <label class="section_label" for="field_admin_label"><?php _e(
             "Genoo pre Mapped With"
         ); ?></label>
        <input type="text" value="Address 1" />
         <input type="text" value="Address 2" />
        <input type="text" value="City" />
          <input type="text" value="State" />
           <input type="text" value="Postal Code" />
        <input type="text" value="Province" />
         <input type="text" value="zip" />
            <input type="text" value="Country" />
            </li>
            </div>
             <div>
        <li class="premappedwebsite field_setting">
         <label class="section_label" for="field_admin_label"><?php _e(
             "Genoo pre Mapped With"
         ); ?></label>
        <input type="text" value="Web Site URL" />
        
            </li>
            </div>
            <div>
                 <li class="premappedconsent field_setting">
         <label class="section_label" for="field_admin_label"><?php _e(
             "Genoo pre Mapped With"
         ); ?></label>
        <input type="text" value="GDPR Consent" />
         <input type="text" value="GDPR Consent Text" />
            </li>
            </div>
                <div>
                 <li class="premappedphone field_setting">
         <label class="section_label" for="field_admin_label"><?php _e(
             "Genoo pre Mapped With"
         ); ?></label>
        <input type="text" value="Phone #" />
         
            </li>
            </div>
              
                    <?php if (method_exists($WPME_API, "callCustom")):
                        try {
                            $leadtypes_optional = $WPME_API->callCustom(
                                "/leadtypes",
                                "GET",
                                null
                            );
                            $leadTypefolders = $WPME_API->callCustom(
                                "/listLeadTypeFolders/Uncategorized",
                                "GET",
                                "NULL"
                            );
                            if ($WPME_API->http->getResponseCode() == 204):
                                // No leadfields based on folderdid onchange! Ooops


                            elseif ($WPME_API->http->getResponseCode() == 200):
                            endif;
                            $i = 0;
                            ?>
                        <div class="folderupdates"> 
                         <h2>Selected lead folders</h2>
                            <div class="folderleadupdates">
                               
                                </div>
                      
                        
                        <label class="section_label leadfolder" for="field_admin_label"><?php _e(
                            "Select Lead Folders:"
                        ); ?></label>
             
                   <i class="leadfolderarrow down"></i>         
                       <div class="leadtypefolder">
                          
                            <?php foreach (
                                $leadTypefolders
                                as $leadTypefolder
                            ) { ?>
                        <li class="encrypt_setting_folders field_setting" >

                   <input type="checkbox" id="leadfolder_encrypt_value<?php echo $i; ?>" name="leadfolder_encrypt_value<?php echo $i; ?>" dataidvalue=<?php echo $leadTypefolder->type_id; ?> leadfoldername="<?php echo $leadTypefolder->name; ?>" onchange="SetFieldProperty('leadfolder<?php echo $i; ?>', this.checked);" />
                <label for="leadfolder_encrypt_value<?php echo $i; ?>" class="leadtype_folder_label<?php echo $i; ?>" style="display:inline;">
                    <?php _e(
                        $leadTypefolder->name,
                        "Gravity Forms WPMktgEngine Extension"
                    ); ?>
                    <?php gform_tooltip("form_field_encrypt_value"); ?>
                </label>  
           

            </li>
              <?php $i++;} ?>
                  </div>
                <div><input type="button" class="leadfolderselected" value="Show lead types" /></div> 
                </div>   
                <div class="leadtypeselectoption">
                  <h2>Selected lead types</h2>

        	     <div class="leadtypeupdates"> 
        	     
        		 </div>
        		 <h1 class="labelupdateoption optionremove">Updated Labels</h1>

        		   <div class="updatedoptions">
		       
		   </div>
		   <h1 class="editheader">Edit Label Here:</h1>l
		   <div class="select-arrow-item">
              <label class="leadtype_label encrypt_section_label" for="field_admin_label"><?php _e(
                  "Select Lead Types:"
              ); ?></label>
		   <i class="leadtypesarrow down"></i>
		   </div>

                <div class="leadtypecheckbox">
                     <?php foreach (
                         $leadtypes_optional
                         as $leadtypes_optional_values
                     ) { ?>
                        <li class="encrypt_setting_leadtypes field_setting"  datafolder-id="<?php echo $leadtypes_optional_values->folder_id; ?>">
        
                <input type="checkbox" id="field_encrypt_value<?php echo $i; ?>" name="field_encrypt_value<?php echo $i; ?>" data-id =<?php echo $i; ?> data-value-id=<?php echo $leadtypes_optional_values->folder_id; ?> dataidvalue=<?php echo $leadtypes_optional_values->id; ?> leadfoldername="<?php echo $leadtypes_optional_values->name; ?>" onchange="SetFieldProperty('encryptField<?php echo $i; ?>', this.checked);" />
                <label for="field_encrypt_value<?php echo $i; ?>" class="leadtype_value_label<?php echo $i; ?>" style="display:inline;">
                    <?php _e(
                        $leadtypes_optional_values->name,
                        "Gravity Forms WPMktgEngine Extension"
                    ); ?>
                    <?php gform_tooltip("form_field_encrypt_value"); ?>
                </label>  
                <input type="text" id="field_id_input_label_text" class="field_id_input_label_text<?php echo $i; ?>" value="<?php echo $leadtypes_optional_values->name; ?>" style="display: none;"/>

        <input type="text" id="field_id_input_label_text" class="field_id_input_value_text<?php echo $i; ?>" value="<?php echo $leadtypes_optional_values->id; ?>" style="display: none;"/>
        </li>
        <?php $i++;} ?>
        
           <div> <input type="button" class="leadtypeupdate" value="Update" style="display: none;" /></div>
        </div>
      
           
           <div> <input type="button" class="leadtypeselected" value="Update label" /></div>
         
		   </div>
		  
                                   <?php
                        } catch (Exception $e) {
                        }
                    endif; ?>
           
           
           
         <?php
        endif; ?>
         

    <?php
    },
    10,
    2
);
// gform_editor_js function for restricting types to show Genoo/WPMktgEngine Field:
add_action("gform_editor_js", function () {
    ?>
  
    <?php
    global $WPME_API;
    if (method_exists($WPME_API, "callCustom")):
        try {
            $leadtypes_optional = $WPME_API->callCustom(
                "/leadtypes",
                "GET",
                null
            );
            $leadfoders_optional = $WPME_API->callCustom(
                "/listLeadTypeFolders/Uncategorized",
                "GET",
                null
            );

            if ($WPME_API->http->getResponseCode() == 204):
                // No leadfields based on folderdid onchange! Ooops


            elseif ($WPME_API->http->getResponseCode() == 200):
            endif;
        } catch (Exception $e) {
            //To DO
        }
    endif;

    $count = count($leadtypes_optional);
    $folder_count = count($leadfoders_optional);

    $all_default_types = [
        "text",
        "textarea",
        "multiselect",
        "checkbox",
        "number",
        "captcha",
        "fileupload",
        "list",
        "product",
        "quantity",
        "creditcard",
        "post_title",
        "html",
        "select",
        "page",
        "section",
        "radio",
        "post_category",
        "post_image",
        "post_tags",
        "post_excerpt",
        "post_custom_field",
        "option",
        "total",
        "shipping",
        "post_content",
        "date",
        "time",
        "hidden",
    ];
    ?>
    <script>
    fieldSettings.name += ', .premappedname';
    fieldSettings.email += ', .premappedemail';
    fieldSettings.address += ', .premappedaddress';
    fieldSettings.website += ', .premappedwebsite';
    fieldSettings.consent += ', .premappedconsent';
    fieldSettings.phone += ', .premappedphone';
    </script>
  <?php foreach ($all_default_types as $default_type): ?>
    <script type="text/javascript">
         jQuery('.leadtypeupdating').hide();

        var type = '<?php echo $default_type; ?>';
       
     
      fieldSettings[type] += ', .thirdparty_input_setting';
      fieldSettings[type] += ', .encrypt_setting_folders';
    //  fieldSettings[type] += ',.leadingbox';
      fieldSettings[type] += ', .encrypt_setting_leadtypes';
      fieldSettings[type] += ', .select_gravity_input_settings';
      fieldSettings[type] += ', .leadtypeupdating';
      
     
       

     // console.log(fieldSettings);
      
      
      // Make sure our field gets populated with its saved value
    jQuery(document).on("gform_load_field_settings", function(event, field, form) {
  
            
     var value = [];
       var leadtypescount = '<?php echo $count; ?>';

       var leadfolderscount = '<?php echo $folder_count; ?>';
    
        var third_party_value = field['thirdPartyInput'];
        
        
          jQuery('.leadtypeupdating').css('display','none');


        jQuery("#field_thirdparty_input").val(field["thirdPartyInput"]);


        for (i = 0; i < leadfolderscount; i++) {

          
    jQuery("#leadfolder_encrypt_value"+i).prop( 'checked', ( rgar( field, 'leadfolder'+i )) );

     
   
    }
    
      var alreadyAdded = [];
         var leadtypearray = [];
     
  

    jQuery('.encrypt_setting_folders > input[type="checkbox"]').each(function() {
     
       var objectvalue = jQuery(this).closest('.folderupdates');
        var foldername = jQuery(this).attr("leadfoldername");
       var folderid = jQuery(this).attr("dataidvalue");
        var parentDiv=jQuery(objectvalue);
     if(jQuery(this).is(':checked')){
     
   if(jQuery.inArray(folderid, alreadyAdded) == -1)
      {
       alreadyAdded.push(folderid); 
       
       if(jQuery("#" + folderid).length == 0) {
     parentDiv.find('.folderleadupdates').append('<span id='+folderid+' class='+folderid+'>' + foldername + '</span>');
     }
      }
     else
     {
        
     }
 
     }
     else
     {
       var folderremove = '.'+folderid;
      jQuery(folderremove).remove();
      
     }
   

 
       
   });


 jQuery('.encrypt_setting_leadtypes > input[type="checkbox"]').each(function() {
     
     
       
          var id = jQuery(this).attr("data-value-id");
          if(jQuery.inArray(id, alreadyAdded) != -1){
         
    var objectvalue = jQuery(this).closest('.encrypt_setting_leadtypes');
                  var parentDivision =jQuery(objectvalue);
             parentDivision.css('display','block');      
    
   }
   else{
       var objectvalue = jQuery(this).closest('.encrypt_setting_leadtypes');
                  var parentDivision =jQuery(objectvalue);
            parentDivision.css('display','none');      

   }
    });
    
     jQuery('.encrypt_setting_leadtypes > input[type="checkbox"]').each(function() {
          var objectvalue = jQuery(this).closest('.leadtypeselectoption');
         var parentDivision =jQuery(objectvalue);   
          var foldername = jQuery(this).attr("leadfoldername");
        var folderid = jQuery(this).attr("dataidvalue");
        
    if(jQuery(this).is(":checked")){
  
        var labelattribute = jQuery(this).attr("id");
     
       var myString = labelattribute.substring(19);
       
       var labelshow = '.field_id_input_label_text' + myString;
     
       jQuery(labelshow).css('display','none');
   
//   jQuery(".leadtypeselected").css("display","none");
      jQuery(".leadtypeupdate").css("display","none");
    
    
       if(jQuery.inArray(folderid, leadtypearray) == -1)
      {
       leadtypearray.push(folderid); 
              if(jQuery("#" + folderid).length == 0) {

     parentDivision.find('.leadtypeupdates').append('<span id='+folderid+' class='+folderid+'>' + foldername + '</span>');
     
     
       jQuery.ajax({
      url: ajaxurl,
      type: "POST",
      cache: false,
      data: {
        action: 'get_option_data',
        field_id: field['id'],
        form_id : folderid
      },
      success: function (data) {
      parentDivision.find(".optionremove").removeClass('labelupdateoption');

        jQuery.each(data, function (key, value) {
          
             parentDivision.find('.updatedoptions').append('<span id='+key+' class='+key+'>' + value + '</span>');
                  
             });
       },
      error: function (errorThrown) {
        console.log(errorThrown);
      },
    });
     }
      }
     else
     {
        
     }
    
    }
     else
     {
       var folderremove = '.'+folderid;
      jQuery(folderremove).remove();
      
     }

    });

    for (i = 0; i < leadtypescount; i++) {

          jQuery("#field_encrypt_value"+i).prop( 'checked', ( rgar( field, 'encryptField'+i )) );

            
        }

                
            if(third_party_value!='leadtypes')
            {
             jQuery('.folderupdates').css('display','none');
             jQuery(".leadtypeselectoption").css("display","none");

            // jQuery(".leadtypecheckbox").css("display","none");
            jQuery(".leadtypeselected").css("display","none"); 
            jQuery(".encrypt_section_label").css("display","none");
          jQuery(".leadtypesarrow").css("display","none");     
            }
            else{
               jQuery('.leadfolderarrow').trigger("click");
                 jQuery('.leadtypesarrow').trigger("click");

                jQuery('.folderupdates').css('display','block');  
                jQuery(".leadtypeselectoption").css("display","block");
                jQuery(".leadtypeselected").css("display","block");
              
           
            }

           
           
      });
     
        //binding to the load field settings event to initialize the checkbox

    </script>
   <?php endforeach;
});
//save while create the new form
add_action("gform_after_save_form", "after_save_form", 10, 2);
function after_save_form($form, $is_new)
{
    global $wpdb, $WPME_API;
    $gf_form_table = $wpdb->prefix . "gf_form";
    $gf_save_form_id = $wpdb->prefix . "postmeta";
    $get_form_name = $wpdb->get_row(
        "SELECT * from $gf_form_table WHERE `id` = " . $form["id"] . ""
    );
    $genoo_form_id = get_post_meta($form["id"], $form["id"], true);
    $values = [];
    if ($is_new) {
        $values["form_name"] = $get_form_name->title;
        $values["form_id"] = "0";
        $values["form_type"] = "GF";
    } else {
        $values["form_name"] = $get_form_name->title;
        $values["form_id"] = $genoo_form_id;
        $values["form_type"] = "GF";
    }

    //changed callcustom api for Save Form
    if (method_exists($WPME_API, "callCustom")):
        try {
            $response = $WPME_API->callCustom(
                "/saveExternalForm",
                "POST",
                $values
            );

            if ($WPME_API->http->getResponseCode() == 204):
                // No values based on form name,form id onchange! Ooops


            elseif ($WPME_API->http->getResponseCode() == 200):
                if ($genoo_form_id == $response->genoo_form_id):
                    update_post_meta(
                        $form["id"],
                        $form["id"],
                        $response->genoo_form_id
                    );
                    update_post_meta(
                        $form["id"],
                        "form_title",
                        $get_form_name->title
                    );
                else:
                    add_post_meta(
                        $form["id"],
                        $form["id"],
                        $response->genoo_form_id
                    );
                    add_post_meta(
                        $form["id"],
                        "form_title",
                        $get_form_name->title
                    );
                endif;
            endif;
        } catch (Exception $e) {
            if ($WPME_API->http->getResponseCode() == 404):

                // Looks like formname or form id not found
            endif;
        }
    endif;
}

add_filter("gform_pre_render", "populate_dropdown");
add_filter("gform_pre_validation", "populate_dropdown");
add_filter("gform_admin_pre_render", "populate_dropdown");
add_filter("gform_pre_submission_filter", "populate_dropdown");
function populate_dropdown($form)
{
    global $WPME_API, $wpdb;

    // Make a GET request, to Genoo / WPME api, for that rest endpoint

    $leadtype_form_save = $wpdb->prefix . "leadtype_form_save";

    // $inputs = array();

    $leaddetailsoptions = false;

    foreach ($form["fields"] as $field) {
        $i = 0;

        $leadTypes = $wpdb->get_results(
            "select `label_name`,`label_value`,`field_id` from $leadtype_form_save where field_id=$field->id and form_id=$field->formId"
        );
        $choices = [];
        foreach ($leadTypes as $leadType) {
            $choices[] = [
                "text" => $leadType->label_name,
                "value" => $leadType->label_value,
            ];

            $i++;
        }
        if ($field->thirdPartyInput == "leadtypes") {
            $leaddetailsoptions = true;
        } else {
            $leaddetailsoptions = false;
        }
        if ($field->type == "select") {
            $field->placeholder = " ";
        }

        if ($leaddetailsoptions) {
            $field["choices"] = $choices;
        }
    }

    return $form;
}
//delete while click the delete permanantly
add_action("gform_before_delete_form", "log_form_deleted");
function log_form_deleted($form_id)
{
    global $wpdb, $WPME_API;
    $values = [];

    $form_genoo_title = get_post_meta($form_id, "form_title", true);
    $form_genoo_id = get_post_meta($form_id, $form_id, true);
    $values["form_name"] = $form_genoo_title;
    $values["form_id"] = $form_genoo_id;
    if (method_exists($WPME_API, "callCustom")):
        try {
            $response = $WPME_API->callCustom(
                "/deleteGravityForm",
                "DELETE",
                $values
            );
            if ($WPME_API->http->getResponseCode() == 204):
                // No values based on form name,form id onchange! Ooops


            elseif ($WPME_API->http->getResponseCode() == 200):
                $delete = delete_post_meta($form_id, "form_title", true);
                $deleteid = delete_post_meta($form_id, $form_id, true);
            endif;
        } catch (Exception $e) {
        }
    endif;
}

//update the hook for create new field in database addon table.

add_action("upgrader_process_complete", "lead_folder_field_creation", 10, 2);

function lead_folder_field_creation($upgrader_object, $options)
{
    global $wpdb;

    //get plugin file.

    $our_plugin = plugin_basename(__FILE__);

    $is_plugin_updated = false;

    //check plugin is active

    if (isset($options["plugins"]) && is_array($options["plugins"])) {
        foreach ($options["plugins"] as $index => $plugin) {
            if ($our_plugin === $plugin) {
                $is_plugin_updated = true;
                break;
            }
        }
    }

    if (!$is_plugin_updated) {
        return;
    }
    $gf_addon_wpextenstion = $wpdb->prefix . "gf_settings";

    $existing_columns = $wpdb->get_col("DESC {$gf_addon_wpextenstion}", 0);

    // Implode to a string suitable for inserting into the SQL query

    $sql[] = implode(", ", $existing_columns);

    if (!in_array("select_lead_folder", $sql)):
        //updated field in addon table
        $wpdb->query(
            "ALTER TABLE $gf_addon_wpextenstion ADD select_lead_folder VARCHAR(255)"
        );
    endif;
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}leadtype_form_save (
        id int(11) unsigned not null auto_increment,
        form_id int(11) unsigned not null,
        field_id int(11) unsigned not null,
        label_name varchar(255),
        label_value int(11), PRIMARY KEY(id)) $charset_collate;";
    gf_upgrade()->dbDelta($sql);
}
add_action("admin_init", "adminEnqueueScripts");

add_action("wp_ajax_gravity_form_get_lead_id", "gravity_form_get_lead_id");
function gravity_form_get_lead_id()
{
    global $WPME_API;
    $lead_id = $_REQUEST["lead_id"];

    if (method_exists($WPME_API, "callCustom")):
        try {
            $leadtypes_optional_value = $WPME_API->callCustom(
                "/leadtypes",
                "GET",
                null
            );

            $leadtypes_optional = [];

            foreach ($leadtypes_optional_value as $leadtypes_optional_values):
                if ($leadtypes_optional_values->folder_id == $lead_id):
                    $leadtypes_optional[$leadtypes_optional_values->id] =
                        $leadtypes_optional_values->name;
                endif;
            endforeach;
        } catch (Exception $e) {
            //To DO
        }
    endif;

    wp_send_json($leadtypes_optional);
}

add_action("wp_ajax_lead_type_option_submit", "lead_type_option_submit");

function lead_type_option_submit()
{
    global $wpdb;

    $leadtype_form_save = $wpdb->prefix . "leadtype_form_save";

    $leadtype_save_values = $_REQUEST["inservalues"];

    $field_id = $_REQUEST["field_id"];

    $form_id = $_REQUEST["form_id"];

    $wpdb->delete($leadtype_form_save, [
        "form_id" => $form_id,
        "field_id" => $field_id,
    ]);

    foreach ($leadtype_save_values as $leadtype_save_value) {
        $wpdb->insert($leadtype_form_save, [
            "form_id" => $form_id,
            "field_id" => $field_id,
            "label_name" => $leadtype_save_value["label"],
            "label_value" => $leadtype_save_value["labelvalue"],
        ]);
    }
}

add_action("wp_ajax_get_option_data", "get_option_data");

function get_option_data()
{
    global $wpdb;

    $leadtype_form_save = $wpdb->prefix . "leadtype_form_save";
    $field_id = $_REQUEST["field_id"];
    $form_id = $_REQUEST["form_id"];

    $leadTypes = $wpdb->get_results(
        "select `label_name`,`label_value`,`field_id` from $leadtype_form_save where field_id=$field_id and label_value=$form_id"
    );

    foreach ($leadTypes as $leadType) {
        $lead_results[$leadType->label_value] = $leadType->label_name;
    }

    wp_send_json($lead_results);
}

function adminEnqueueScripts()
{
    // scripts
    wp_enqueue_script(
        "gravityform-script",
        plugin_dir_url(__FILE__) . "includes/updatefile.js",
        [],
        "1.0"
    );
    wp_enqueue_style(
        "gravityform-style",
        plugin_dir_url(__FILE__) . "includes/leadtype.css"
    );
}

add_action("wp_head", "myplugin_ajaxurl");
function myplugin_ajaxurl()
{
    echo '<script type="text/javascript">
                       var ajaxurl = "' .
        admin_url("admin-ajax.php") .
        '";
                     </script>';
}

require_once "includes/api-functions.php";
?>
