<?php
GFForms::include_addon_framework();

class Gravityformextension extends GFAddOn {
    protected $_slug = 'WPMktgEngineExtension';
    protected $_path = 'wp-gravity-forms-extension-master/wp-starter.php';
    protected $_full_path = __FILE__;
    protected $_title = 'Genoo/WPMktgEngine';
    protected $_short_title = 'Genoo/WPMktgEngine';
    private static $_instance = null;
    /**
    * Get an instance of this class.
    *
    * @return GFGravityAddOn
    */
    public static function get_instance() {
        if ( self::$_instance == null ) {
            self::$_instance = new Gravityformextension();
        }
        return self::$_instance;
    }
   
    public function init() {
        parent::init();
        add_filter( 'gform_submit_button', array(
            $this,
            'form_submit_button'
        ), 10, 2 );

    }
 function form_submit_button( $button, $form ) {
        $settings = $this->get_form_settings( $form );
        if ( isset( $settings['enabled'] ) && true == $settings['enabled'] ) {
            $text = $this->get_plugin_setting( 'mytextbox' );
            $button = "<div>{$text}</div>" . $button;
        }
        return $button;
    }
    // ADMIN FUNCTIONS

    /**
    * Configures the settings which should be rendered on the Form Settings > Gravity Add-On tab.
    *
    * @return array
    */

    public function form_settings_fields( $form ) {
        global $WPME_API;
        //getting api response for leadtypes, zoomwebinars, emailfolders
        if ( method_exists( $WPME_API, 'callCustom' ) ):
        try {
            // Make a GET request, to Genoo / WPME api, for that rest endpoint
            $leadtypeoptions = $WPME_API->callCustom( '/leadtypes', 'GET', NULL );
            $webinars = $WPME_API->callCustom( '/zoomwebinars/all', 'GET', NULL );
            $leademailfolders = $WPME_API->callCustom( '/emailfolders', 'GET', NULL );
            $leadTypefolder = $WPME_API->callCustom( '/listLeadTypeFolders/Uncategorized', 'GET', 'NULL' );
            if ( $WPME_API->http->getResponseCode() == 204 ): // No leadtypes, zoomwebinars, emailfolders ! Ooops
            elseif ( $WPME_API->http->getResponseCode() == 200 ):
            // Good product in $leadtypes, $zoomwebinars, $emailfolders variable

            endif;
        } catch( Exception $e ) {
            if ( $WPME_API->http->getResponseCode() == 404 ):
            // Looks like folders not found

            endif;
        }
        endif;
        //click the save setting button call the below process
        if ( isset( $_POST['gform-settings-save'] ) ):
        global $wpdb;

        //geting all form post values while click save sattings button
        $gf_addon_wpextenstion = $wpdb->prefix.'gf_settings';
        $leadtypes = $emailfolder = $Webinar = $form_id = $select_email = $check_webinnar =   $leadfolder = '';

        $leadfolder = isset( $_POST['_gform_setting_selectleadtypefolders'] ) ? $_POST['_gform_setting_selectleadtypefolders'] : '';
        $leadtypes = isset( $_POST['_gform_setting_selectleadtypes'] ) ? $_POST['_gform_setting_selectleadtypes'] : '';
        $emailfolder = isset( $_POST['_gform_setting_leadingemailfolders'] ) ? $_POST['_gform_setting_leadingemailfolders'] : '';
        $Webinar = isset( $_POST['_gform_setting_leadwebinars'] ) ? $_POST['_gform_setting_leadwebinars'] : '';
        $form_id = isset( $_GET['id'] ) ? $_GET['id'] : '';
        $select_email = isset( $_POST['_gform_setting_leademail'] ) ? $_POST['_gform_setting_leademail'] : '';
        $check_webinnar = isset( $_POST['_gform_setting_check_webinnar'] ) ? $_POST['_gform_setting_check_webinnar'] : '';
        $source = isset( $_POST['_gform_setting_source_gravity'] ) ? $_POST['_gform_setting_source_gravity'] : '';

        $count_extension = $wpdb->get_var( "SELECT count(*) from $gf_addon_wpextenstion  WHERE `form_id` = '$form_id'" );
        if ( $count_extension == 0 ):
        //inserting setting data into table
       $wpdb->insert( $gf_addon_wpextenstion, array(
            'form_id' => $form_id,
            'is_active' => $check_webinnar,
            'select_lead_folder' => $leadfolder,
            'select_leadtype' => $leadtypes,
            'source' => $source,
            'select_folder' => $emailfolder,
            'select_email' => $select_email,
            'select_webinar' => $Webinar
        ) );
        else:
        //if the same data with same form id then update the values.
        $wpdb->update( $gf_addon_wpextenstion, array(
            'form_id' => $form_id,
            'is_active' => $check_webinnar,
            'select_lead_folder' => $leadfolder,
            'select_leadtype' => $leadtypes,
            'source' => $source,
            'select_folder' => $emailfolder,
            'select_email' => $select_email,
            'select_webinar' => $Webinar
        ), array(
            'form_id' => $form_id
        ) );
        endif;
        $leadtypes = $emailfolder = $Webinar = $form_id = $select_email = $check_webinnar = $leadfolder = '';
        endif;
        //to view the WPMktgEngineExtension itself.
        if ( $_GET['subview'] == 'WPMktgEngineExtension' ):
        global $wpdb;
        $gf_addon_wpextenstion = $wpdb->prefix.'gf_settings';
        $form_id_title = $_GET['id'];
        //get title of the form
        $select_for_title = RGFormsModel::get_form( $form_id_title );
        //get all the lead types, email folders, emails from table
        $select_lead = $wpdb->get_row( "SELECT * from $gf_addon_wpextenstion WHERE `form_id` = '$form_id_title'" );
        //assign all the id in variable
        $select_folder_id = isset( $select_lead->select_folder ) ? $select_lead->select_folder : '';
        $select_lead_id = isset( $select_lead->select_leadtype ) ? $select_lead->select_leadtype : '';
        $select_email_id = isset( $select_lead->select_email ) ? $select_lead->select_email : '';
        $is_active = isset( $select_lead->is_active ) ? $select_lead->is_active : '';
        $select_webinar = isset( $select_lead->select_webinar ) ? $select_lead->select_webinar : '';
        $leadfolder = isset( $select_lead->select_lead_folder ) ? $select_lead->select_lead_folder : '';
        $source = isset( $select_lead->source ) ? $select_lead->source : '';
        //to pass the folder id to show emails based on folderid
        if ( method_exists( $WPME_API, 'callCustom' ) ):
        try {
            // Make a GET request, to Genoo / WPME api, for that rest endpoint

            $get_emails = $WPME_API->callCustom( '/emails/' . $select_folder_id, 'GET', NULL );
         
        } catch( Exception $e ) {
            if ( $WPME_API->http->getResponseCode() == 404 ):
            // Looks like product not found

            endif;
        }
        endif;
        endif;
        $leadfolder_array = $leadtype_array = $leademail_folderarray = $get_emails_array = $webinararray =  array();
     
        $leadfolder_array[] =  array( 'label' => esc_html__( 'Select Lead Type Folders', 'Gravity Forms WPMktgEngine Extension' ), 'value' => 'selectleadtypefolder' );
        $leadfolder_array[] = array( 'label' => esc_html__( 'Create Lead Type Folder', 'Gravity Forms WPMktgEngine Extension' ),
        'value' => 'createleadtypefolder' );
        foreach ( $leadTypefolder as $leadTypefolders ) {
            $leadfolder_array[] =
            array( 'label' => esc_html__( $leadTypefolders->name, 'Gravity Forms WPMktgEngine Extension' ),
            'value' => $leadTypefolders->type_id );

        }
        $leadtype_array[] = array( 'label' => esc_html__( 'Select Lead Types', 'Gravity Forms WPMktgEngine Extension' ), 'value'=>'' );
        $leadtype_array[] = array( 'label' => esc_html__( 'Create Lead Types', 'Gravity Forms WPMktgEngine Extension' ),
        'value' => 'createleadtype' );

        foreach ( $leadtypeoptions as $leadtypeoption ) {
            if ( $leadfolder == $leadtypeoption->folder_id ):
            $leadtype_array[] = array( 'label' => esc_html__( $leadtypeoption->name, 'Gravity Forms WPMktgEngine Extension' ),
            'value' => $leadtypeoption->id );
            endif;
        }
        $leademail_folderarray[] = array( 'label' => esc_html__( 'Select Email Folders', 'Gravity Forms WPMktgEngine Extension' ), 'value' => '' );

        foreach ( $leademailfolders as $leademailfolder ) {
            $leademail_folderarray[] = array( 'label' => esc_html__( $leademailfolder->name, 'Gravity Forms WPMktgEngine Extension' ), 'value' => $leademailfolder->id );
        }
        if ( !empty( $select_email_id ) && !empty( $get_emails ) ) :
        foreach ( $get_emails as $get_email ) {
            $get_emails_array[] = array( 'label' => esc_html__( $get_email->name, 'Gravity Forms WPMktgEngine Extension' ), 'value' => $get_email->id );

        } else:
        $get_emails_array[] = array( 'label' => esc_html__( 'no email here', 'Gravity Forms WPMktgEngine Extension' ), 'value' => '' );

        endif;

        foreach ( $webinars as $webinar ) {

            $webinararray[] = array( 'label' => esc_html__( $webinar->name, 'Gravity Forms WPMktgEngine Extension' ), 'value' => $webinar->id );

        }

        require_once ( 'includes/formsettings.php' );
        return array(
            array(
                'title'  => esc_html__( 'Simple Form Settings', 'Gravity Forms WPMktgEngine Extension' ),
                'fields' => array(
                    array(
                        'label'   => esc_html__( 'LeadType Folder:', 'Gravity Forms WPMktgEngine Extension' ),
                        'type'    => 'select',
                        'name'    => 'selectleadtypefolders',
                        'tooltip' => esc_html__( 'Select the folder where lead type exists.', '' ),
                        'choices' =>
                        $leadfolder_array

                    ),
                    array(
                        'type'              => 'text',
                        'name'              => '',
                        'tooltip'           => esc_html__( 'Create new lead type', 'Gravity Forms WPMktgEngine Extension' ),
                        'class'             => 'newleadtypefolder',
                        'id' => 'newleadtypefolder',
                        'feedback_callback' => array( $this, 'is_valid_setting' ),
                    ),
                    array(

                        'type'              => 'button',
                        'name'              => 'leadtypefoldersaving',
                       'class'             => 'leadtypefoldersaving',
                        'id' => 'leadtypefoldersaving',
                        'feedback_callback' => array( $this, 'is_valid_setting' ),
                    ),
                    array(
                        'label'   => esc_html__( 'Lead Type dropdowns (Lead Type where submissions should be put):', 'Gravity Forms WPMktgEngine Extension' ),
                        'type'    => 'select',
                        'name'    => 'selectleadtypes',
                        'tooltip' => esc_html__( 'Each leads submit in this form will be added to this lead type', '' ),
                        'choices' =>
                        $leadtype_array

                    ),
                    array(
                        'type'              => 'text',
                        'name'              => '',
                        'tooltip'           => esc_html__( 'This is the tooltip', 'Gravity Forms WPMktgEngine Extension' ),
                        'class'             => 'newleadtype',
                        'id' => 'newleadtypecrt',
                        'feedback_callback' => array( $this, 'is_valid_setting' ),
                    ),
                    array(

                        'type'              => 'button',
                        'name'              => 'leadtypesaving',
                        'id' => 'leadtypesaving',
                        'feedback_callback' => array( $this, 'is_valid_setting' ),
                    ),
                    array(
                        'label'             => esc_html__( 'My Text Box', 'Gravity Forms WPMktgEngine Extension' ),
                        'type'              => 'hidden',
                        'name'              => 'form_title',
                        'value' => $select_for_title->title,
                        'class'             => 'form_title',
                        'feedback_callback' => array( $this, 'is_valid_setting' ),
                    ),
                    array(
                        'label'             => esc_html__( 'My Text Box', 'Gravity Forms WPMktgEngine Extension' ),
                        'type'              => 'hidden',
                        'name'              => 'folder_id',
                        'value' => $select_folder_id,
                        'tooltip'           => esc_html__( 'This is the tooltip', 'Gravity Forms WPMktgEngine Extension' ),
                        'class'             => 'folder_id',
                        'feedback_callback' => array( $this, 'is_valid_setting' ),
                    ),
                    array(
                        'label'             => esc_html__( 'Source:', 'Gravity Forms WPMktgEngine Extension' ),
                        'type'              => 'text',
                        'name'              => 'source_gravity',
                        'value' => $source,
                        'class' => 'label_source_gravity',
                        'tooltip'           => esc_html__( 'This will be set as the origination source for any new leads to complete this form.', 'Gravity Forms WPMktgEngine Extension' ),
                        'feedback_callback' => array( $this, 'is_valid_setting' ),
                    ),
                    array(
                        'label'             => esc_html__( 'Select Your Confirmation Email:', 'Gravity Forms WPMktgEngine Extension' ),
                        'type'              => 'text',
                        'name'              => 'label_class_email_gravity',
                        'value' => '',
                        'class' => 'label_class_email_gravity',
                        'tooltip'           => esc_html__( ' This email will be send upon form submission.', 'Gravity Forms WPMktgEngine Extension' ),
                        'feedback_callback' => array( $this, 'is_valid_setting' ),
                    ),
                    array(
                       
                        'label'   => esc_html__( 'Select Email Folders:', 'Gravity Forms WPMktgEngine Extension' ),
                        'type'    => 'select',
                        'name'    => 'leadingemailfolders',
                        'class' => 'leademailfolders',
                        'tooltip' => esc_html__( 'The location where the confirmation email is located.', '' ),
                        'choices' =>
                        $leademail_folderarray

                    ),
                    array(
                        'label'   => esc_html__( 'Select Email to Send:', 'Gravity Forms WPMktgEngine Extension' ),
                        'type'    => 'select',
                        'class' => 'send-email email-show',
                        'name'    => 'leademail',
                        'tooltip' => esc_html__( 'The confirmation email you�d like sent to person who completes the form', '' ),
                        'choices' =>
                        $get_emails_array

                    ),
                    array(
                        'name'    => 'check_webinnar',
                        'type'    => 'checkbox',
                        'class' => 'check_webinnar',
                        'tooltip' => esc_html__( 'Check the box if you�d like lead to be registered into a webinar', '' ),
                        'choices' => array(
                            array(
                                'label' => esc_html__( 'Register User into Webinar', 'Gravity Forms WPMktgEngine Extension' ),
                                'name'  => 'check_webinnar',
                            ),
                        ),
                    ),
                    array(
                        'label'   => esc_html__( 'Select Lead Webinars:', 'Gravity Forms WPMktgEngine Extension' ),
                        'type'    => 'select',
                        'class' => 'leadwebinars',
                        'name'    => 'leadwebinars',
                        'tooltip' => esc_html__( 'The webinar you�d like the lead to be registered into', '' ),
                        'choices' =>
                        $webinararray

                    ),

                ),
            ),
        );

    }

    public function settings_save( $field, $echo = true ) {
        //To Do

    }

    public function render_settings( $sections ) {
       //To Do
    }
}
