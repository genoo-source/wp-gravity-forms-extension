<?php
/*
    Plugin Name: Extension Name - WPMktgEngine Extension
    Description: Genoo, LLC
    Author:  Genoo, LLC
    Author URI: http://www.genoo.com/
    Author Email: info@genoo.com
    Version: 0.0.1
    License: GPLv2
*/
/*
    Copyright 2020  WPMKTENGINE, LLC  (web : http://www.genoo.com/)

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

/**
 * Use this, if you need to call Genoo / WPME Api to activate
 * an API call, or get some data from an API
 */
register_activation_hook(__FILE__, function(){
    // Basic extension data
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
});

/**
 * WPMKTENGINE Extension Init
 */
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

/**
 * Genoo / WPME deactivation function
 */
if(!function_exists('genoo_wpme_deactivate_plugin')){

    /**
     * @param $file
     * @param $message
     * @param string $recover
     */

    function genoo_wpme_deactivate_plugin($file, $message, $recover = '')
    {
        // Require files
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        // Deactivate plugin
        deactivate_plugins($file);
        unset($_GET['activate']);
        // Recover link
        if(empty($recover)){
            $recover = '</p><p><a href="'. admin_url('plugins.php') .'">&laquo; ' . __('Back to plugins.', 'wpmktengine') . '</a>';
        }
        // Die with a message
        wp_die($message . $recover);
        exit();
    }
}
