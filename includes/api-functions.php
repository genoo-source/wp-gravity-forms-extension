<?php
     add_action( 'wp_ajax_getleadEmail', 'getleadEmail' );
 
     //for ajax call to get emails based on folderid when on change function works.
     function  getleadEmail($folderid){
         
        global $WPME_API;
        
        $emails_folder = wp_remote_get('https://devawsapi.odportals.com/api/rest/emails/'.$_GET['folderid'].'?api_key='.$WPME_API->key);
        if( is_wp_error( $emails_folder )):
    	    return false; 
        else:
            $emails_folder_json = wp_remote_retrieve_body( $emails_folder );
            $decoded_data = json_decode( $emails_folder_json );
            wp_send_json($decoded_data);
        endif;
     }
     
     //for show emails based on folder id after save 
     function  getleadEmailshow($folderid){
         
        global $WPME_API;
        $emails = wp_remote_get('https://devawsapi.odportals.com/api/rest/emails/'.$folderid.'?api_key='.$WPME_API->key);
           if( is_wp_error( $emails )):
    	      return false; 
           else:
              $emails_json = wp_remote_retrieve_body( $emails );
              return json_decode( $emails_json,true );
           endif;
           
     }
?>