<?php
add_action( 'wp_ajax_getleadEmail', 'getleadEmail' );
add_action( 'wp_ajax_createleadtype', 'createleadtype' );
add_action( 'wp_ajax_leadtypefilter', 'leadtypefilter' );
add_action( 'wp_ajax_saveformdata', 'saveformdata' );
add_action('wp_ajax_create_gravity_lead_folder','create_gravity_lead_folder');
add_action('wp_ajax_display_selected_gravity_leadtypes','display_selected_gravity_leadtypes');
add_action('wp_ajax_display_updating_leadtype_box','display_updating_leadtype_box');

//for ajax call to get emails based on folderid when on change function works.

function getleadEmail( $folderid ) {
    global $WPME_API;
    if ( method_exists( $WPME_API, 'callCustom' ) ):
    try {
        // Make a GET request, to Genoo / WPME api, for that rest endpoint
        $email_data = $WPME_API->callCustom( '/emails/' . $_GET['folderid'], 'GET', NULL );
        if ( $WPME_API->http->getResponseCode() == 204 ): // No emails based on folderdid onchange! Ooops
        elseif ( $WPME_API->http->getResponseCode() == 200 ):
        wp_send_json( $email_data );
        // emails in $decoded_data variable

        endif;
    } catch( Exception $e ) {
    }
    endif;
}
function display_updating_leadtype_box()
{
    global $WPME_API;
     $lead_gravity_id = $_REQUEST['ledtype_ids'];
    
     if (method_exists($WPME_API, 'callCustom')) :
        try {
            $leadTypes = $WPME_API->callCustom('/leadtypes', 'GET', null);


            foreach ($leadTypes as $leadType) {
              if (in_array($leadType->id, $lead_gravity_id)) :
                        $leadtype_values[$leadType->id] = $leadType->name;

                    endif;
                    
            }
               wp_send_json($leadtype_values);    
       
            }
         catch (Exception $e) {
            //To Do
        }
        endif;
    
}

function create_gravity_lead_folder()
{
   global $WPME_API;
    $lead_folder = [];
    $lead_folder['name'] = stripslashes($_REQUEST['folder_id']);
    $lead_folder['description'] = stripslashes($_REQUEST['description']);

    if (method_exists($WPME_API, 'callCustom')):
        try {
            $createfolders = $WPME_API->callCustom(
                '/listLeadTypeFoldersByName/' . stripslashes($lead_folder['name']),
                'GET','NULL'
            );

           
              if (empty($createfolders)):

                   try {
                        $createfolder = $WPME_API->callCustom(
                            '/saveLeadTypeFolder',
                            'POST',
                            $lead_folder
                        );

                      

                        wp_send_json($createfolder->id);
                    } catch (Exception $e) {
                         //To DO
                    }
                endif;
           
        } catch (Exception $e) {
             //To DO
        }
    endif;

}
function display_selected_gravity_leadtypes()
{
    global $WPME_API;
    
    $lead_gravity_id = $_REQUEST['folderid'];
    
  
    
     if (method_exists($WPME_API, 'callCustom')) :
        try {
            $leadTypes = $WPME_API->callCustom('/leadtypes', 'GET', null);


            foreach ($leadTypes as $leadType) {
              if (in_array($leadType->folder_id, $lead_gravity_id)) :
                        $leadtype_values[$leadType->id] = $leadType->name;

                    endif;
                    
            }
               wp_send_json($leadtype_values);    
       
            }
         catch (Exception $e) {
            //To Do
        }
        endif;
        
     
}

function createleadtype() {
    global $WPME_API;
    $createlead = array();
    $createlead['name'] = stripslashes($_REQUEST['leadtypevalue']);
    $createlead['description'] = stripslashes($_REQUEST['description']);
    $createlead['mngdlistind'] = $_REQUEST['mngdlistind'];
    $createlead['costforall'] = $_REQUEST['costforall'];
    $createlead['costperlead'] = $_REQUEST['costperlead'];
    $createlead['sales_ind'] = $_REQUEST['sales_ind'];
    $createlead['system_ind'] = $_REQUEST['system_ind'];
    $createlead['blog_commenters'] = $_REQUEST['blog_commenters'];
    $createlead['blog_subscribers'] = $_REQUEST['blog_subscribers'];
    $createlead['folder_id'] = $_REQUEST['folder_id'];
    if ( method_exists( $WPME_API, 'callCustom' ) ):
    try {
        $leadresponse = $WPME_API->callCustom( '/createLeadType', 'POST', $createlead );

        wp_send_json( $leadresponse->ltid );

    } catch( Exception $e ) {
    }
    endif;
}

function leadtypefilter() {
    global $WPME_API;
    $lead_folder = array();
    $lead_folder['folder_id'] = $_REQUEST['folder_id'];

    if ( method_exists( $WPME_API, 'callCustom' ) ):
    try {
        $leadtypes = $WPME_API->callCustom( '/leadtypes', 'GET', NULL );
        $leadnames = array();
        foreach ( $leadtypes as $leadtype ):
        if ( $lead_folder['folder_id'] == $leadtype->folder_id ):
        $leadnames[$leadtype
        ->id] = $leadtype->name;
        endif;
        endforeach;
        wp_send_json( $leadnames );
    } catch( Exception $e ) {

    }
    endif;

}



function saveformdata() {

    global $WPME_API, $wp;

    $form_name = $_REQUEST['formname'];
    $post_id =  $_REQUEST['post_id'];
    $formid =  $_REQUEST['formid'];

    $values = array();

    $values['form_name'] = $form_name;

    $values['form_type'] = 'GF';

    $post_meta_value  = get_post_meta( $post_id, $formid, true );

    if ( $post_meta_value == '' ) {

        $values['form_id'] = '0';
    } else {

        $values['form_id'] = $post_meta_value;

    }

    if ( method_exists( $WPME_API, 'callCustom' ) ):

    try {
        $response = $WPME_API->callCustom( '/saveExternalForm', 'POST', $values );

        if ( $WPME_API->http->getResponseCode() == 204 ): // No values based on form name, form id onchange! Ooops
        elseif ( $WPME_API->http->getResponseCode() == 200 ):

        if ( $post_meta_value == $response->genoo_form_id ):
        update_post_meta( $post_id, $formid, $response->genoo_form_id );
        else:
        add_post_meta( $post_id, $formid, $response->genoo_form_id );

        endif;

        endif;
    } catch( Exception $e ) {
        if ( $WPME_API->http->getResponseCode() == 404 ):
        // Looks like formname or form id not found

        endif;
    }
    endif;

}

?>