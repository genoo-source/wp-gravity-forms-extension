<?php
GFForms::include_addon_framework();

class Gravityformextension extends GFAddOn
{
    protected $_slug = "WPMktgEngineExtension";
    protected $_path = "wp-gravity-forms-extension-master/wp-starter.php";
    protected $_full_path = __FILE__;
    protected $_title = "Genoo/WPMktgEngine";
    protected $_short_title = "Genoo/WPMktgEngine";
    private static $_instance = null;
    /**
     * Get an instance of this class.
     *
     * @return GFGravityAddOn
     */
    public static function get_instance()
    {
        if (self::$_instance == null) {
            self::$_instance = new Gravityformextension();
        }
        return self::$_instance;
    }
    /**
     * 
     */

    public function init()
    {
        parent::init();
        add_filter("gform_submit_button", [$this, "form_submit_button"], 10, 2);
    }

    // 

    function form_submit_button($button, $form)
    {
        $settings = $this->get_form_settings($form);
        if (isset($settings["enabled"]) && true == $settings["enabled"]) {
            $text = $this->get_plugin_setting("mytextbox");
            $button = "<div>{$text}</div>" . $button;
        }
        return $button;
    }

    // ADMIN FUNCTIONS

    /**
     * Configures the settings which should be rendered on the Form Settings > Simple Add-On tab.
     *
     * @return array
     */

    public function form_settings_fields($form)
    {
        global $WPME_API;
        //getting api response for leadtypes, zoomwebinars, emailfolders
        if (method_exists($WPME_API, "callCustom")):
            try {
                // Make a GET request, to Genoo / WPME api, for that rest endpoint
                $lead_types = $WPME_API->callCustom("/leadtypes", "GET", null);
                $zoom_webinars = $WPME_API->callCustom(
                    "/zoomwebinars/all",
                    "GET",
                    null
                );
                $lead_email_folders = $WPME_API->callCustom(
                    "/emailfolders",
                    "GET",
                    null
                );
                $lead_type_folders = $WPME_API->callCustom(
                    "/listLeadTypeFolders/Uncategorized",
                    "GET",
                    "NULL"
                );
            }
            catch (Exception $e) {
                if ($WPME_API->http->getResponseCode() == 404):
                    // Looks like folders not found
                endif;
            }
        endif;
        //click the save setting button call the below process
        if (isset($_POST["gform-settings-save"])):
            global $wpdb;
            //geting all form post values while click save sattings button
            $gf_addon_wpextenstion = $wpdb->prefix . "gf_settings";
            $lead_types = $lead_email_folder = $zoom_Webinar = $form_id = $lead_email = $webinar_check_box_value = $lead_folder =
                "";
            $lead_folder = isset($_POST["_gform_setting_selectleadtypefolders"])
                ? $_POST["_gform_setting_selectleadtypefolders"]
                : "";
            $lead_types = isset($_POST["_gform_setting_selectleadtypes"])
                ? $_POST["_gform_setting_selectleadtypes"]
                : "";
            $lead_email_folder = isset(
                $_POST["_gform_setting_leadingemailfolders"]
                )
                ? $_POST["_gform_setting_leadingemailfolders"]
                : "";
            $zoom_Webinar = isset($_POST["_gform_setting_leadwebinars"])
                ? $_POST["_gform_setting_leadwebinars"]
                : "";
            $form_id = isset($_GET["id"]) ? $_GET["id"] : "";
            $lead_email = isset($_POST["_gform_setting_leademail"])
                ? $_POST["_gform_setting_leademail"]
                : "";
            $webinar_check_box_value = isset(
                $_POST["_gform_setting_check_webinnar"]
                )
                ? $_POST["_gform_setting_check_webinnar"]
                : "";
            $source = isset($_POST["_gform_setting_source_gravity"])
                ? $_POST["_gform_setting_source_gravity"]
                : "";

            $count_extension = $wpdb->get_var(
                "SELECT count(*) from $gf_addon_wpextenstion  WHERE `form_id` = '$form_id'"
            );

            if ($count_extension == 0):
                //inserting setting data into table
                $wpdb->insert($gf_addon_wpextenstion, [
                    "form_id" => $form_id,
                    "is_active" => $webinar_check_box_value,
                    "select_lead_folder" => $lead_folder,
                    "select_leadtype" => $lead_types,
                    "source" => $source,
                    "select_folder" => $lead_email_folder,
                    "select_email" => $lead_email,
                    "select_webinar" => $zoom_Webinar,
                ]);
            //if the same data with same form id then update the values.
            else:
                $wpdb->update($gf_addon_wpextenstion,[
                                "form_id" => $form_id,
                                "is_active" => $webinar_check_box_value,
                                "select_lead_folder" => $lead_folder,
                                "select_leadtype" => $lead_types,
                                "source" => $source,
                                "select_folder" => $lead_email_folder,
                                "select_email" => $lead_email,
                                "select_webinar" => $zoom_Webinar,
                             ],
                             [
                                "form_id" => $form_id,
                             ]
                );
            endif;
            $lead_types = $lead_email_folder = $zoom_Webinar = $form_id = $lead_email = $webinar_check_box_value = $lead_folder =
                "";
        endif;

        //to view the WPMktgEngineExtension itself.
        if ($_GET["subview"] == "WPMktgEngineExtension"):
            global $wpdb;
            $gf_addon_wpextenstion = $wpdb->prefix . "gf_settings";
            $form_id_title = $_GET["id"];
            //get title of the form
            $select_for_title = RGFormsModel::get_form($form_id_title);
            //get all the lead types, email folders, emails from table
            $get_form_leads = $wpdb->get_row(
                "SELECT * from $gf_addon_wpextenstion WHERE `form_id` = '$form_id_title'"
            );

            //assign all the id in variable
            $get_email_folder_id = isset($get_form_leads->select_folder)? $get_form_leads->select_folder : "";
            $get_email_id = isset($get_form_leads->select_email)? $get_form_leads->select_email : "";
            $lead_folder = isset($get_form_leads->select_lead_folder)? $get_form_leads->select_lead_folder : "";
            $source = isset($get_form_leads->source) ? $get_form_leads->source : "";

            //to pass the folder id to show emails based on folderid
            if (method_exists($WPME_API, "callCustom")):
                try {
                    // Make a GET request, to Genoo / WPME api, for that rest endpoint
                    $get_emails = $WPME_API->callCustom(
                        "/emails/" . $get_email_folder_id,
                        "GET",
                        null
                    );
                }
                catch (Exception $e) {
                    if ($WPME_API->http->getResponseCode() == 404):

                    // Looks like product not found
                    endif;
                }
            endif;
        endif;
        $lead_folder_array = $lead_type_array =  $lead_email_folder_array = $get_emails_array = $webinar_array = [];
        $lead_folder_array[] = [
            "label" => esc_html__(
            "Select Lead Type Folders",
            "Gravity Forms WPMktgEngine Extension"
        ),
            "value" => "selectleadtypefolder",
        ];
        $lead_folder_array[] = [
            "label" => esc_html__(
            "Create Lead Type Folder",
            "Gravity Forms WPMktgEngine Extension"
        ),
            "value" => "createleadtypefolder",
        ];
        foreach ($lead_type_folders as $lead_type_folder) {
            $lead_folder_array[] = [
                "label" => esc_html__(
                $lead_type_folder->name,
                "Gravity Forms WPMktgEngine Extension"
            ),
                "value" => $lead_type_folder->type_id,
            ];
        }
        $lead_type_array[] = [
            "label" => esc_html__(
            "Select Lead Types",
            "Gravity Forms WPMktgEngine Extension"
        ),
            "value" => "",
        ];
        $lead_type_array[] = [
            "label" => esc_html__(
            "Create Lead Types",
            "Gravity Forms WPMktgEngine Extension"
        ),
            "value" => "createleadtype",
        ];

        foreach ($lead_types as $lead_type) {
            if ($lead_folder == $lead_type->folder_id):
                $lead_type_array[] = [
                    "label" => esc_html__(
                    $lead_type->name,
                    "Gravity Forms WPMktgEngine Extension"
                ),
                    "value" => $lead_type->id,
                ];
            endif;
        }
        $lead_email_folder_array[] = [
            "label" => esc_html__(
            "Select Email Folders",
            "Gravity Forms WPMktgEngine Extension"
        ),
            "value" => "",
        ];

        foreach ($lead_email_folders as $lead_email_folder) {
            $lead_email_folder_array[] = [
                "label" => esc_html__(
                $lead_email_folder->name,
                "Gravity Forms WPMktgEngine Extension"
            ),
                "value" => $lead_email_folder->id,
            ];
        }

        if (!empty($get_email_id) && !empty($get_emails)):
            foreach ($get_emails as $get_email) {
                $get_emails_array[] = [
                    "label" => esc_html__(
                    $get_email->name,
                    "Gravity Forms WPMktgEngine Extension"
                ),
                    "value" => $get_email->id,
                ];
            }
        else:
            $get_emails_array[] = [
                "label" => esc_html__(
                "no email here",
                "Gravity Forms WPMktgEngine Extension"
            ),
                "value" => "",
            ];
        endif;

        foreach ($zoom_webinars as $zoom_webinar) {
            $webinar_array[] = [
                "label" => esc_html__(
                $zoom_webinar->name,
                "Gravity Forms WPMktgEngine Extension"
            ),
                "value" => $zoom_webinar->id,
            ];
        }

        require_once "includes/formsettings.php";

        return [
            [
                "title" => esc_html__(
                "Simple Form Settings",
                "Gravity Forms WPMktgEngine Extension"
            ),
                "fields" => [
                    [
                        "label" => esc_html__(
                        "LeadType Folder:",
                        "Gravity Forms WPMktgEngine Extension"
                    ),
                        "type" => "select",
                        "name" => "selectleadtypefolders",
                        "tooltip" => esc_html__(
                        "Select the folder where lead type exists.",
                        ""
                    ),
                        "choices" => $lead_folder_array,
                    ],
                    [
                        "type" => "text",
                        "name" => "",
                        "tooltip" => esc_html__(
                        "Create new lead type",
                        "Gravity Forms WPMktgEngine Extension"
                    ),
                        "class" => "newleadtypefolder",
                        "id" => "newleadtypefolder",
                        "feedback_callback" => [$this, "is_valid_setting"],
                    ],
                    [
                        "type" => "button",
                        "name" => "leadtypefoldersaving",
                        "class" => "leadtypefoldersaving",
                        "id" => "leadtypefoldersaving",
                        "feedback_callback" => [$this, "is_valid_setting"],
                    ],
                    [
                        "label" => esc_html__(
                        "Lead Type dropdowns (Lead Type where submissions should be put):",
                        "Gravity Forms WPMktgEngine Extension"
                    ),
                        "type" => "select",
                        "name" => "selectleadtypes",
                        "tooltip" => esc_html__(
                        "Each leads submit in this form will be added to this lead type",
                        ""
                    ),
                        "choices" => $lead_type_array,
                    ],
                    [
                        "type" => "text",
                        "name" => "",
                        "tooltip" => esc_html__(
                        "This is the tooltip",
                        "Gravity Forms WPMktgEngine Extension"
                    ),
                        "class" => "newleadtype",
                        "id" => "newleadtypecrt",
                        "feedback_callback" => [$this, "is_valid_setting"],
                    ],
                    [
                        "type" => "button",
                        "name" => "leadtypesaving",
                        "id" => "leadtypesaving",
                        "feedback_callback" => [$this, "is_valid_setting"],
                    ],
                    [
                        "label" => esc_html__(
                        "My Text Box",
                        "Gravity Forms WPMktgEngine Extension"
                    ),
                        "type" => "hidden",
                        "name" => "form_title",
                        "value" => $select_for_title->title,
                        "class" => "form_title",
                        "feedback_callback" => [$this, "is_valid_setting"],
                    ],
                    [
                        "label" => esc_html__(
                        "My Text Box",
                        "Gravity Forms WPMktgEngine Extension"
                    ),
                        "type" => "hidden",
                        "name" => "folder_id",
                        "value" => $select_folder_id,
                        "tooltip" => esc_html__(
                        "This is the tooltip",
                        "Gravity Forms WPMktgEngine Extension"
                    ),
                        "class" => "folder_id",
                        "feedback_callback" => [$this, "is_valid_setting"],
                    ],
                    [
                        "label" => esc_html__(
                        "Source:",
                        "Gravity Forms WPMktgEngine Extension"
                    ),
                        "type" => "text",
                        "name" => "source_gravity",
                        "value" => $source,
                        "class" => "label_source_gravity",
                        "tooltip" => esc_html__(
                        "This will be set as the origination source for any new leads to complete this form.",
                        "Gravity Forms WPMktgEngine Extension"
                    ),
                        "feedback_callback" => [$this, "is_valid_setting"],
                    ],
                    [
                        "label" => esc_html__(
                        "Select Your Confirmation Email:",
                        "Gravity Forms WPMktgEngine Extension"
                    ),
                        "type" => "text",
                        "name" => "label_class_email_gravity",
                        "value" => "",
                        "class" => "label_class_email_gravity",
                        "tooltip" => esc_html__(
                        " This email will be send upon form submission.",
                        "Gravity Forms WPMktgEngine Extension"
                    ),
                        "feedback_callback" => [$this, "is_valid_setting"],
                    ],
                    [
                        "label" => esc_html__(
                        "Select Email Folders:",
                        "Gravity Forms WPMktgEngine Extension"
                    ),
                        "type" => "select",
                        "name" => "leadingemailfolders",
                        "class" => "leademailfolders",
                        "tooltip" => esc_html__(
                        "The location where the confirmation email is located.",
                        ""
                    ),
                        "choices" => $lead_email_folder_array,
                    ],
                    [
                        "label" => esc_html__(
                        "Select Email to Send:",
                        "Gravity Forms WPMktgEngine Extension"
                    ),
                        "type" => "select",
                        "class" => "send-email email-show",
                        "name" => "leademail",
                        "tooltip" => esc_html__(
                        "The confirmation email you?d like sent to person who completes the form",
                        ""
                    ),
                        "choices" => $get_emails_array,
                    ],
                    [
                        "name" => "check_webinnar",
                        "type" => "checkbox",
                        "class" => "check_webinnar",
                        "tooltip" => esc_html__(
                        "Check the box if you?d like lead to be registered into a webinar",
                        ""
                    ),
                        "choices" => [
                            [
                                "label" => esc_html__(
                                "Register User into Webinar",
                                "Gravity Forms WPMktgEngine Extension"
                            ),
                                "name" => "check_webinnar",
                            ],
                        ],
                    ],
                    [
                        "label" => esc_html__(
                        "Select Lead Webinars:",
                        "Gravity Forms WPMktgEngine Extension"
                    ),
                        "type" => "select",
                        "class" => "leadwebinars",
                        "name" => "leadwebinars",
                        "tooltip" => esc_html__(
                        "The webinar you?d like the lead to be registered into",
                        ""
                    ),
                        "choices" => $webinar_array,
                    ],
                ],
            ],
        ];
    }

    public function settings_save($field, $echo = true)
    {
        //TO DO
    }

    public function render_settings($sections)
    {  
        //TO DO
    }
}
