<?php
/*
Plugin Name: Gravity Forms WPMktgEngine Extension
Plugin URI: http://www.gravityforms.com
Description: Gravity Forms should be installed and active to use this plugin.
Version: 2.1
Requires PHP: 7.1
Author: Rocketgenius
Author URI: http://www.rocketgenius.com

------------------------------------------------------------------------
Copyright 2012-2016 Rocketgenius Inc.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/


register_activation_hook(__FILE__, function(){
    // Basic extension data
     global $wpdb;
    $fileFolder = basename(dirname(__FILE__));
    $file = basename(__FILE__);
    $filePlugin = $fileFolder . DIRECTORY_SEPARATOR . $file;
    // Activate?
    $activate = FALSE;
    $isGenoo = FALSE;
    // Get api / repo
    if(class_exists('\WPME\ApiFactory') && class_exists('\WPME\RepositorySettingsFactory')){
        $activate = TRUE;
        $repo = new \WPME\RepositorySettingsFactory();
        $api = new \WPME\ApiFactory($repo);
        if(class_exists('\Genoo\Api')){
            $isGenoo = TRUE;
        }
    } elseif(class_exists('\Genoo\Api') && class_exists('\Genoo\RepositorySettings')){
        $activate = TRUE;
        $repo = new \Genoo\RepositorySettings();
        $api = new \Genoo\Api($repo);
        $isGenoo = TRUE;
    } elseif(class_exists('\WPMKTENGINE\Api') && class_exists('\WPMKTENGINE\RepositorySettings')){
        $activate = TRUE;
        $repo = new \WPMKTENGINE\RepositorySettings();
        $api = new \WPMKTENGINE\Api($repo);
    }
    // 1. First protectoin, no WPME or Genoo plugin
    if($activate == FALSE){
        genoo_wpme_deactivate_plugin(
            $filePlugin,
            'This extension requires WPMktgEngine or Genoo plugin to work with.'
        );
    } else {
        // Make ACTIVATE calls if any?
    }
    //creating tables setting save
   $sql = "CREATE TABLE {$wpdb->prefix}gf_settings (
            id mediumint(8) unsigned not null auto_increment,
            form_id mediumint(8) unsigned not null,
            is_active tinyint(1),
            select_leadtype  varchar(255),
            select_folder  varchar(255),
            select_email varchar(255),
            select_webinar  varchar(250),
            PRIMARY KEY  (id),
            UNIQUE KEY form_id (form_id)
                  ) $charset_collate;";

		gf_upgrade()->dbDelta( $sql );
	
		
});
add_action('wpmktengine_init', function($repositarySettings, $api, $cache){
  
  // Use the Settings, Api or Cache to do things on load of WPME if you need to
  // For example, add custom settings to WPME screen

  add_filter('wpmktengine_tools_extensions_widget', function($array){
        $array['Extension'] = '<span style="color:green">Active</span>' . $r;
        return $array;
  }, 10, 1);

  add_filter('wpmktengine_settings_sections', function($sections){
      $sections[] = array(
          'id' => 'Extension',
          'title' => __('Extension', 'wpmktengine')
      );
      return $sections;
  }, 10, 1);

  add_filter('wpmktengine_settings_fields', function($fields){
      $fields['Extension'] = array(
          array(
            'name' => 'extension_cipher_key',
            'id' => 'extension_cipher_key',
            'label' => __('Cipher', 'wpmktengine'),
            'type' => 'text',
            'default' => '',
            'attr' => array('style' => 'display: block'), // Custom attributes, js etc.
            'desc' => __('Description', 'wpmktengine')
          ),
          array(
            'label' => __('Dropdown', 'wpmktengine'),
            'name' => 'extension_dropdown_key',
            'id' => 'extension_dropdown_key',
            'type' => 'select',
            'options' => array(0 => 'Select')
				  )
      );
      return $fields;
  }, 10, 1);
}, 10, 3);
add_action('gform_after_submission', 'access_entry_via_field', 10, 2 );
add_action('gform_after_submission', 'access_entry_via_field', 10, 2 );
function access_entry_via_field($entry,$form) {
    
  // echo "<pre>";
   //print_r($entry);
  // print_r($form);
   
   global $wpdb,$WPME_API;
   $id = isset($entry['form_id']) ? $entry['form_id'] : 0;
   
   if($id != 0):
       
        $gf_addon_wpextenstion = $wpdb->prefix.gf_settings;
        $form_settings = $wpdb->get_row("SELECT * from $gf_addon_wpextenstion WHERE form_id = $id");
        
        $select_folder_id = isset($form_settings->select_folder) ? $form_settings->select_folder : ''; 
        $select_lead_id   = isset($form_settings->select_leadtype) ? $form_settings->select_leadtype : '';
        $select_email_id  = isset($form_settings->select_email) ? $form_settings->select_email : '';
        $select_webinar   = isset($form_settings->select_webinar) ? $form_settings->select_webinar : '';
        
        if($select_lead_id != ''):

             $values = array();
             $values['form_name'] = $form['title'];
             $values['client_ip_address'] = $entry['ip'];
             $values['lead_type_id'] = $select_lead_id;
             $values['form_type']='opt-in form';
             $values['web_site_url']=$entry['source_url'];
             
             if(!empty($select_email_id)):
                 $values['confirmation_email_id'] = $select_email_id;
             endif;
             
             if(!empty($select_webinar)):
                 $values['webinar_id'] = $select_webinar;
             endif;
             
             foreach ($form['fields'] as $field):
                 
                 // echo $field['type'];
                 if ($field['type'] == 'email') :
                    $values['email'] = $entry[$field['id']];
                 endif;
                 
                 if ($field['type'] == 'phone' && !empty($entry[$field['id']])):
                    $values['phone']= $entry[$field['id']];
                 endif;
                 
                 if ($field['type'] == 'address'):
                     $field_id = $field['id'];
                     $values['address1'] = $entry[$field_id . '.1'];
                     $values['address2'] = $entry[$field_id . '.2'];
                     $values['city'] = $entry[$field_id . '.3'];
                     $values['state']= $entry[$field_id . '.4'];
                     $values['province']= $entry[$field_id . '.4'];
                     $values['zip'] = $entry[$field_id . '.5'];
                     $values['country'] = $entry[$field_id . '.6'];
                  endif;
                  
                  if ($field['type'] == 'consent'):
                     $values['c00gdprconsent'] = ($entry[$field['id'].'.1']!=1)?'':1;
                      if(!empty($field->description)):
                          $values['c00gdprconsentmsg'] = $field->description;
                      endif;
                  endif;
                  
                  if ($field['type'] == 'name'):
                     $field_id = $field['id'];
                     $values['first_name']=$entry[$field_id . '.3'];
                     $values['last_name']=$entry[$field_id . '.6'];
                  endif;
                  
                  $all_default_types = array('textarea','text','multiselect','checkbox','number','captcha','fileupload','list',
                                              'product','quantity','creditcard','post_title','html','select','page','section','radio','post_category','post_image','post_tags','post_excerpt','post_custom_field','option','total','shipping','post_content','date','time','hidden');
                                             
                  //check all default types which is not a premapped types
                 
                   
                  if (in_array($field['type'],$all_default_types) && !empty($entry[$field['id']]) && !empty($field->thirdPartyInput)):
                        $firstindex =  strstr($field->thirdPartyInput, 'c00');
                        $lastindex = strstr($field->thirdPartyInput, 'date');
                      
                       //echo $field['type'];
                        
                        if($firstindex == true && $lastindex == true):
                             $time = strtotime($entry[$field['id']]);
                             $date = date('Y-m-d',$time);
                             $values[$field->thirdPartyInput] = $date."T".'00:00:00+00:00';
                       elseif($firstindex == false && $lastindex == true):
                             $time = strtotime($entry[$field['id']]);
                             $date = date('m/d/Y',$time);
                             $values[$field->thirdPartyInput] = $date;
                        elseif($field['type']=='radio' && $field->thirdPartyInput=='c00eudatasubject' && !empty($entry[$field['id']])):
                             $values['c00eudatasubject'] = '1';
                        elseif(!empty($entry[$field['id']])):
                             $values[$field->thirdPartyInput] = $entry[$field['id']];
                        endif;
                        
                   endif;

                   if($field['type']=='checkbox'): 
                        $inputs = $field->get_entry_inputs();
                        foreach($inputs as $inputsfields) :
                            if($entry[$inputsfields['id']]):
                               $values[$field->thirdPartyInput] = '1';    
                            endif;
                        endforeach;
                   endif;
                   
                 endforeach;
             
             $form_values = json_encode($values);
             
             $args = array('body' => $form_values, 'headers' => array('Content-Type' => 'application/json','x-api-key' => $WPME_API->key));
                    
             // post url
             $response = wp_remote_post('https://devawsapi.odportals.com/api/rest/leadformsubmit', $args );
                   
             if( is_wp_error( $response )) :
                  return false; 
             else :
                $json_data=json_decode($response['body']);
                //set cookies
                $geno_ids = $json_data->genoo_id; 
                setcookie('_gtld', $geno_ids, time() + (10 * 365 * 24 * 60 * 60));
             endif;
             
         endif;
        endif;
}

add_action('wp_action_to_modify', function(){
  // Get WPME api object, same in both Genoo and WPME plugins
  global $WPME_API;
  // It's set on INIT, if it's not present, this hook runs too early and you
  if(!$WPME_API){
    return;
  }
  // Do things
  // Get or save to settings repository
  $settings = $WPME_API->settingsRepo;
  // Value from custom setttings above
  $settingsCipher = $settings->getOption('extension_cipher_key', 'Extension');
  // Do something with settings value from custom settings?
  
  // Make api calls, that are baked into the plugin
  // 1. Get lead by email address
  try {
    $lead = $WPME_API->getLeadByEmail('lead@email.com');
  } catch (\Exception $e){

  }

  // 2. Call custom API, newly created, etc.
  if(method_exists($WPME_API, 'callCustom')){
    try {
      $product_id_external = 1;
      // Make a GET request, to Genoo / WPME api, for that rest endpoint
      $product = $WPME_API->callCustom('/wpmeproductbyextid/' . $product_id_external, 'GET', NULL);
      if($WPME_API->http->getResponseCode() == 204){
          // No product! Ooops
      } elseif($WPME_API->http->getResponseCode() == 200){
         // Good product in $product variable
      }
    } catch(Exception $e){
        if($WPME_API->http->getResponseCode() == 404){
            // Looks like product not found
        }
    }
  }

  // 3. Api key?
  $apiKey = $WPME_API->key;
});

define( 'GF_SIMPLE_ADDON_VERSION', '2.1' );

add_action( 'gform_loaded', array( 'GF__gravityform_Bootstrap', 'load' ), 5 );

class GF__gravityform_Bootstrap {

    public static function load() {

        if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
            return;
        }
//include the class file
        require_once( 'class-gravityformextension.php' );

        GFAddOn::register( 'Gravityformextension' );
    }

}
register_activation_hook( __FILE__, 'child_plugin_activate' );
function child_plugin_activate(){

    // Require parent plugin
    if ( ! is_plugin_active( 'gravityforms/gravityforms.php' ) and current_user_can( 'activate_plugins' ) ) {
        // Stop activation redirect and show error
        wp_die('Sorry, but this plugin requires the Parent Plugin to be installed and active. <br><a href="' . admin_url( 'plugins.php' ) . '">&laquo; Return to Plugins</a>');
    }
}


function gf_gravityform() {
    return Gravityformextension::get_instance();
} 

add_action('gform_field_standard_settings', function($position, $form_id) {
    
    // position -1 for adding third party(Genoo/WPMktgEngine Field:) as last
	if($position == -1):
	    
	    global $WPME_API;
	    
	    //calling leadfields api for showing dropdown
        $leadfields = wp_remote_get('https://devawsapi.odportals.com/api/rest/leadfields?api_key='.$WPME_API->key);
        $leadfield_json = wp_remote_retrieve_body( $leadfields );
        $customfieldsjson = json_decode( $leadfield_json );
        
        // right after Admin Field Label
    	// $pre_mapped_fields for should not show the premapped fields
        $pre_mapped_fields = array('First Name','Last Name','Email','Address 1','Address 2','City','State',
                                     'Postal Code','Country','Phone #','Zip','Province','GDPR Consent',
                                     'GDPR Consent Text','Web Site URL');
    ?>
    
        <li class="thirdparty_input_setting field_setting">
		  <label class="section_label" for="field_admin_label"><?php _e('Genoo/WPMktgEngine Field:'); ?></label>
    
          <select id="field_thirdparty_input" onchange="SetFieldProperty('thirdPartyInput', this.value);" class="fieldwidth-3" >
	          <option value="">Do not map fields</option>
	          
	           <?php
	            	//showing labels of leadfields	 
	                foreach($customfieldsjson as $customfields): 
	                   //comparing labels with premapped labels in trim_custom_array
	                   if (!in_array(trim($customfields->label),$pre_mapped_fields)):
	           ?>
	                   <option value="<?php echo $customfields->key; ?>"> <?php echo trim($customfields->label);  ?></option>
	           <?php 	
	                   endif;  
	                 endforeach;
	           ?>
	     </select>
	 <?php    
	 endif;
}, 10, 2);


// gform_editor_js function for restricting types to show Genoo/WPMktgEngine Field:
add_action('gform_editor_js', function() {
	
	//standard, advanced,post,price field types without premapped fields
    $all_default_types = array('text','textarea','multiselect','checkbox','number','captcha','fileupload','list',
                                'product','quantity','creditcard','post_title','html','select','page','section','radio','post_category',
                                'post_image','post_tags','post_excerpt','post_custom_field','option','total','shipping','post_content','date',
                                'time','hidden');
                                
    foreach($all_default_types as $default_type):
    ?>
	  <script type="text/javascript">
        var type = '<?php echo $default_type; ?>';
	  	fieldSettings[type] += ', .thirdparty_input_setting';
	  	
	    // Make sure our field gets populated with its saved value
		jQuery(document).on("gform_load_field_settings", function(event, field, form) {
	        	jQuery("#field_thirdparty_input").val(field["thirdPartyInput"]);
	    });
	  </script>
	 <?php 
	 endforeach;
}); 


require_once('includes/api-functions.php');