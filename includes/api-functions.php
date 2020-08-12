<?php
add_action('wp_ajax_getleadEmail', 'getleadEmail');
//for ajax call to get emails based on folderid when on change function works.
function getleadEmail($folderid) {
    global $WPME_API;
    if (method_exists($WPME_API, 'callCustom')):
        try { // Make a GET request, to Genoo / WPME api, for that rest endpoint
            $email_data = $WPME_API->callCustom('/emails/' . $_GET['folderid'], 'GET', NULL);
            if ($WPME_API->http->getResponseCode() == 204): // No emails based on folderdid onchange! Ooops
                elseif ($WPME_API->http->getResponseCode() == 200):
                    wp_send_json($email_data);
                    // emails in $decoded_data variable
                    
                endif;
            }
            catch(Exception $e) {
                if ($WPME_API->http->getResponseCode() == 404):
                    // Looks like emails not found
                    
                endif;
            }
        endif;
    }
?>