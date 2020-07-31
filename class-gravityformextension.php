<?php

GFForms::include_addon_framework();

class Gravityformextension extends GFAddOn {

	protected $_version = GF_SIMPLE_ADDON_VERSION;
	protected $_min_gravityforms_version = '1.9';
	protected $_slug = 'WPMktgEngineExtension';
	protected $_path = 'wp-gravity-forms-extension-master/wp-starter.php';
	protected $_full_path = __FILE__;
	protected $_title = 'Genoo/WPMktgEngine';
	protected $_short_title = 'Genoo/WPMktgEngine';

	private static $_instance = null;

	/**
	 * Get an instance of this class.
	 *
	 * @return GFSimpleAddOn
	 */
	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new Gravityformextension();
		}

		return self::$_instance;
	}

	/**
	 * Handles hooks and loading of language files.
	 */
	public function init() {
		parent::init();
		add_filter( 'gform_submit_button', array( $this, 'form_submit_button' ), 10, 2 );
		add_action( 'gform_after_submission', array( $this, 'after_submission' ), 10, 2 );
	}


	// # SCRIPTS & STYLES -----------------------------------------------------------------------------------------------

	/**
	 * Return the scripts which should be enqueued.
	 *
	 * @return array
	 */


	// # FRONTEND FUNCTIONS --------------------------------------------------------------------------------------------

	/**
	 * Add the text in the plugin settings to the bottom of the form if enabled for this form.
	 *
	 * @param string $button The string containing the input tag to be filtered.
	 * @param array $form The form currently being displayed.
	 *
	 * @return string
	 */
	function form_submit_button( $button, $form ) {
		$settings = $this->get_form_settings( $form );
		if ( isset( $settings['enabled'] ) && true == $settings['enabled'] ) {
			$text   = $this->get_plugin_setting( 'mytextbox' );
			$button = "<div>{$text}</div>" . $button;
		}

		return $button;
	}


	// # ADMIN FUNCTIONS -----------------------------------------------------------------------------------------------

	/**
	 * Creates a custom page for this add-on.
	 */
	public function plugin_page() {
		echo 'This page appears in the Forms menu';
	}

	/**
	 * Configures the settings which should be rendered on the Form Settings > Simple Add-On tab.
	 *
	 * @return array
	 */
	public function form_settings_fields( $form ) {
	    
	    global $WPME_API;

        //getting api response for leadtypes,zoomwebinars,emailfolders
        $leadtypes_get = wp_remote_get('https://devawsapi.odportals.com/api/rest/leadtypes?api_key='.$WPME_API->key);
        $leadwebinar_get =  wp_remote_get('https://devawsapi.odportals.com/api/rest/zoomwebinars/all?api_key='.$WPME_API->key);
        $email_folders_get = wp_remote_get('https://devawsapi.odportals.com/api/rest/emailfolders?api_key='.$WPME_API->key);
        
        //if the api downs
        if( is_wp_error( $leadtypes_get ) || is_wp_error($leadwebinar) || is_wp_error($email_folders)) {
        	return false; 
        }
        
        //getting body of the response
        $leadtypes_json = wp_remote_retrieve_body( $leadtypes_get );
        $webinars_jsons = wp_remote_retrieve_body( $leadwebinar_get );
        $email_folders_json = wp_remote_retrieve_body($email_folders_get);
        
        //decode the json format response
        $leadTypes = json_decode( $leadtypes_json );
        $webinars = json_decode($webinars_jsons);
        $leademailfolders = json_decode($email_folders_json);
        
        //click the save setting button call the below process 
        if(isset($_POST['gform-settings-save'])):
            
        	 global $wpdb;

             //geting all form post values while click save sattings button
             $gf_addon_wpextenstion = $wpdb->prefix.gf_settings;
        	 $leadtypes = $emailfolder = $Webinar = $form_id  = $select_email = $check_webinnar = '';
        	 $leadtypes = isset($_POST['selectleadtypes']) ? $_POST['selectleadtypes'] : '';
        	 $emailfolder = isset($_POST['leadingemailfolders']) ? $_POST['leadingemailfolders'] : '';
        	 $Webinar = isset($_POST['leadwebinars']) ? $_POST['leadwebinars'] : '';
        	 $form_id = isset($_GET['id']) ? $_GET['id'] : '';
        	 $select_email = isset($_POST['leademail']) ? $_POST['leademail'] : '' ;
         	 $check_webinnar = isset($_POST['check_webinnar']) ? $_POST['check_webinnar'] : '';
         	 
        	 $count_extension = $wpdb->get_var("SELECT count(*) from $gf_addon_wpextenstion  WHERE `form_id` = '$form_id'");

             if($count_extension == 0) :
                 
                 //inserting setting data into table
            	 $gf_insert = $wpdb->insert( $gf_addon_wpextenstion,  
            	                             array(
                                                 'form_id' => $form_id,
                                                 'is_active' => $check_webinnar,
                                                 'select_leadtype' => $leadtypes,
                                                 'select_folder' => $emailfolder,
                                                 'select_email' => $select_email,
                                                 'select_webinar' => $Webinar
                                              )
                                            );
              else :
                 
                 //if the same data with same form id then update the values.
                 $gf_update = $wpdb->update($gf_addon_wpextenstion,
                                                array(
                                                    'form_id' => $form_id,
                                                    'is_active' => $check_webinnar,
                                                    'select_leadtype' => $leadtypes,
                                                    'select_folder' => $emailfolder,
                                                    'select_email' => $select_email,
                                                    'select_webinar' => $Webinar
                                                ),array('form_id' => $form_id)
                                             );
              endif;

              $leadtypes = $emailfolder = $Webinar = $form_id  = $select_email = $check_webinnar = ''; 
	
	    endif;
	    
	    //to view the WPMktgEngineExtension itself.
        if($_GET['subview']=='WPMktgEngineExtension'):
        
            global $wpdb;
            $gf_addon_wpextenstion = $wpdb->prefix.gf_settings;
            $form_id_title = $_GET['id'];
            
            //get title of the form
            $select_for_title = RGFormsModel::get_form($form_id_title);
            
            //get all the lead types,email folders,emails from table 
            $select_lead = $wpdb->get_row("SELECT * from $gf_addon_wpextenstion WHERE `form_id` = '$form_id_title'");
            
            //assign all the id in variable
            $select_folder_id = isset($select_lead->select_folder) ? $select_lead->select_folder : ''; 
            $select_lead_id   = isset($select_lead->select_leadtype) ? $select_lead->select_leadtype : '';
            $select_email_id  = isset($select_lead->select_email) ? $select_lead->select_email : '';
            $is_active        = isset($select_lead->is_active) ? $select_lead->is_active : '';
            $select_webinar   = isset($select_lead->select_webinar) ? $select_lead->select_webinar : '';
        
            //to pass the folder id to show emails based on folderid
            $getemails = getleadEmailshow($select_folder_id);
            
	        require_once('includes/formsettings.php');
	        
	     endif;
    }


	public function settings_save( $field, $echo = true ) {
	 

	
	}

}
