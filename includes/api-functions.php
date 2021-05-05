<?php
add_action('wp_ajax_getleadEmail', 'getleadEmail');
add_action('wp_ajax_createleadtype', 'createleadtype');
add_action('wp_ajax_leadtypefilter', 'leadtypefilter');
//for ajax call to get emails based on folderid when on change function works.
function getleadEmail($folderid)
{
    global $WPME_API;
    if (method_exists($WPME_API, 'callCustom')):
        try
        { // Make a GET request, to Genoo / WPME api, for that rest endpoint
            $email_data = $WPME_API->callCustom('/emails/' . $_GET['folderid'], 'GET', NULL);
            if ($WPME_API->http->getResponseCode() == 204): // No emails based on folderdid onchange! Ooops
                elseif ($WPME_API->http->getResponseCode() == 200):
                    wp_send_json($email_data);
                    // emails in $decoded_data variable
                    
                endif;
            }
            catch(Exception $e)
            {  }
        endif;
    }
    function createleadtype()
    {
        global $WPME_API;
        $createlead = array();
        $createlead['name'] = $_REQUEST['leadtypevalue'];
        $createlead['description'] = $_REQUEST['description'];
        $createlead['mngdlistind'] = $_REQUEST['mngdlistind'];
        $createlead['costforall'] = $_REQUEST['costforall'];
        $createlead['costperlead'] = $_REQUEST['costperlead'];
        $createlead['sales_ind'] = $_REQUEST['sales_ind'];
        $createlead['system_ind'] = $_REQUEST['system_ind'];
        $createlead['blog_commenters'] = $_REQUEST['blog_commenters'];
        $createlead['blog_subscribers'] = $_REQUEST['blog_subscribers'];
        $createlead['folder_id'] = $_REQUEST['folder_id'];
        if (method_exists($WPME_API, 'callCustom')):
            try
            {
                $leadresponse = $WPME_API->callCustom('/createLeadType', 'POST', $createlead);

                wp_send_json($leadresponse->ltid);

            }
            catch(Exception $e)
            {  }
        endif;
    }

    function leadtypefilter()
    {
        global $WPME_API;
        $lead_folder = array();
        $lead_folder['folder_id'] = $_REQUEST['folder_id'];

        if (method_exists($WPME_API, 'callCustom')):
            try
            {
                $leadtypes = $WPME_API->callCustom('/leadtypes', 'GET', NULL);
                $leadnames = array();
                foreach ($leadtypes as $leadtype):
                    if ($lead_folder['folder_id'] == $leadtype->folder_id):
                        $leadnames[$leadtype
                            ->id] = $leadtype->name;
                    endif;
                endforeach;
                wp_send_json($leadnames);
            }
            catch(Exception $e)
            {

            }
        endif;

    }

?>