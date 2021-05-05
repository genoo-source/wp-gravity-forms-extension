<h3> <i class="fa fa-cogs"></i>Simple Form Settings</h3> 
 <form method="post" action="">
  <div>
  <div>
      <input type="hidden" name="form_title" value="<?php echo $select_for_title->title; ?>" />
    <input type="hidden" name="folder_id" class="folder_id" value="<?php echo $select_folder_id; ?>" />
     </div>
       <div>
          <h4>Select LeadType Folders:</h4>
          <select name="selectleadtypefolders" id="selectleadtypefolders" style="width: 65%">
           <option value="">Select Lead Type Folders</option>
           <?php 
                //showing leadtypes to select
                foreach($leadTypefolder as $leadTypefolder):
           ?>
                 <option value="<?php echo $leadTypefolder->type_id; ?>" <?php if($leadTypefolder->type_id==$leadfolder) :  ?> selected="selected" <?php endif; ?> >
                     <?php echo $leadTypefolder->name; ?>
                 </option>
             <?php
                  endforeach;
             ?>
           </select>
        </div>
      <div>
          <h4>Select LeadType (where leads will be put who submit this form):</h4>
          <select name="selectleadtypes" id="selectleadtypes" style="width: 65%">
           <option value="">Select Lead Types</option>
           <option disabled="disabled">--------------------------------</option>
            <option value="createleadtype">Create Lead Types</option>
             <?php 
                //showing leadtypes to select
                if($leadfolder):
                foreach($leadTypes as $leadType):
                if($leadfolder==$leadType->folder_id):
           ?>
                 <option value="<?php echo $leadType->id; ?>" <?php if($leadType->id==$select_lead_id) :  ?> selected="selected" <?php endif; ?> >
                     <?php echo $leadType->name; ?>
                 </option>
                
             <?php
                  endif;
                  endforeach;
                  else:
                   foreach($leadTypes as $leadType):
           ?>
                 <option value="<?php echo $leadType->id; ?>" <?php if($leadType->id==$select_lead_id) :  ?> selected="selected" <?php endif; ?> >
                     <?php echo $leadType->name; ?>
                 </option>
             <?php
                  endforeach;    
                 endif;
             ?>
           </select>
        </div>
        <div class="newleadtype" style="display:none;">
        <div>
            <h4>Create new leadtype</h4>
            <input type="text" class="newleadtypecrt form-control"  id="newleadtypecrt"  value="" style="width:65%"; />
        </div>
        <div>
            <button type="submit" class="btn btn-primary" name="leadtypesaving" id="leadtypesaving">Save new leadtype</button>
        </div>
            
        </div>
       
        <div>
          <h4>Select Confirmation Email that should be sent when the form is submitted:</h4>
            <h4>Select Folder</h4>
            <select name="leadingemailfolders" id="leadingemailfolders" class="leademailfolders" style="width: 65%">
               <option value="">Select Email Folders</option>
               <?php 
                  //showing email folders to select
                  foreach($leademailfolders as $leademailfolder):
               ?>
                  <option value="<?php echo $leademailfolder->id; ?>" <?php if($leademailfolder->id==$select_folder_id) : ?> selected="selected" <?php endif; ?>>
                        <?php echo $leademailfolder->name; ?> 
                  </option>
               <?php
                   endforeach;
               ?>
            </select>
        </div>
      
         <div>
            <h4 class="title-send-email">Select Email to Send</h4>
            <select name="leademail" class="send-email email-show" id="leademail" style="width: 65%;">
            <?php 
               //showing emails to select
               if(!empty($select_email_id) && !empty($getemails)) :
                 foreach($getemails as $getemail) :  ?>
                    <option value="<?php echo $getemail->id; ?>"  <?php if($getemail->id==$select_email_id) : ?> selected="selected" <?php endif; ?> >
                       <?php echo $getemail->name; ?> 
                    </option>
                 <?php
                  endforeach;
                else: ?>
                  <option value="">No Email here</option>
                <?php   
                  endif;  
             ?>
            </select>
        </div>
      
        <div>
           <h4><input type="checkbox" name="check_webinnar" class="check_webinnar" value="1" <?php echo ($is_active==1 ?'checked' : ''); ?> />Register User into Webinar</h4>
           <h4>Select Webinar</h4>
        
           <select name="leadwebinars" class="leadwebinars" style="width: 65%">
             <option value="">Select Lead Webinars</option>
             <?php
                //showing webinars to select
                foreach($webinars as $webinar) : 
              ?>   
                 <option value="<?php echo $webinar->id; ?>" <?php if($webinar->id==$select_webinar) : ?> selected="selected" 
                     <?php endif; ?> ><?php echo $webinar->startdate . "::" . $webinar->name; ?>
                  </option>
              <?php  
                 endforeach;  
              ?>
            </select>
        </div>
    
      <div style="padding-top:12px;">
            <button type="Submit" class="btn btn-primary" name="gform-settings-save">Save Settings</button>
      </div>
  </div>
  
</form>
<style>
 #leadtypesaving{
    margin-top: 8px;
    width: 140px;
    height: 44px;
    background: #2271b1;
    border: 1px solid #2271b1;
    color: #fff;
 }
</style>
 
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"> </script>
<?php if($select_folder_id != ''): ?>
  <script>
    jQuery("select#leademail.send-email").prop("required", true);
  </script>
<?php endif; ?>
  <script>
     //hide the email block and webinar when page onload
     $(".send-email").hide();
     $(".title-send-email").hide();
     $(".leadwebinars").hide();
     //script for after register checkbox check leadwebinar show(toggle)
     $(".check_webinnar").click(function() {
     $(".leadwebinars").toggle();
         });  
    </script>

    <?php if($is_active): ?>
    <script>
        $(".leadwebinars").show(); 
    </script>
    <?php 
          else : 
           $wpdb->update($gf_addon_wpextenstion, array('select_webinar'=>''),array('form_id' => $form_id_title));  ?>
           <script>
                $('.leadwebinars option:selected').removeAttr('selected');
           </script>
           
    <?php  endif; ?>
 
    <script>
        // script for call function of emails based on email folder id on change
      jQuery(document).ready(function() {
          
       //Script for select leadtypes on change 
       //create lead type api call
      jQuery('.newleadtype').css('display','none');
      jQuery('#selectleadtypes').on('change',function(){
        var value = jQuery(this).val();
        if(value=='createleadtype'){
          jQuery('.newleadtype').css('display','block');
          jQuery('#newleadtypecrt').prop('required',true);
          jQuery("[name='gform-settings-save']").prop('disabled', true);
   
        }
        else
        {
         jQuery("[name='gform-settings-save']").removeAttr("disabled");
         jQuery('.newleadtype').css('display','none'); 
         jQuery('#newleadtypecrt').prop('required',false);
         jQuery('#selectleadtypes').prop('required',false);
        }
        
      }); 
      //Select lead folder on change
      jQuery('#selectleadtypefolders').on('change',function(){
      jQuery('.newleadtype').css('display','none');
      jQuery("#selectleadtypes").prop("required", true);
       var folder_id= jQuery(this).val();
      jQuery.ajax({
           url: '<?php echo admin_url("admin-ajax.php") ?>',
           type: 'POST',
           data:{
            'action': 'leadtypefilter',
            'folder_id':folder_id,
           },
            success: function(data){
            console.log(data);
            jQuery("#selectleadtypes").empty(); 
            jQuery("#selectleadtypes").append('<option value="">Select Lead Types</option><option value="">----------------------------------</option><option value="createleadtype">Create leadtypes</option>');
            $.each(data, function( key, value ){
            jQuery("#selectleadtypes").append('<option value="'+key+'">'+value+' </option>');
                        });
                  
            },
            
           error: function(errorThrown){
            console.log(errorThrown);
             }
        });
          
      });
       //create new lead type    
     jQuery('#leadtypesaving').on('click',function(event){
         event.preventDefault();
     jQuery('#newleadtypecrt').prop('required',true);
   
        var getval = jQuery('#newleadtypecrt').val();
        var folderid = jQuery('#selectleadtypefolders').val();
        if(getval!=''){
         jQuery.ajax({
           url: '<?php echo admin_url("admin-ajax.php") ?>',
           type: 'POST',
           data:{
            'action': 'createleadtype',
            'leadtypevalue':getval,
            'description':getval,
            'mngdlistind':false,
            'costforall':'',
            'costperlead':'',
            'sales_ind':'no',
            'system_ind':'no',
            'blog_commenters':'no',
            'blog_subscribers':'no',
            'folder_id':folderid,
           },
           
            success: function(data){
            console.log(data);
            jQuery('#newleadtypecrt').prop('required',false);
             jQuery("[name='gform-settings-save']").removeAttr("disabled");
            jQuery('.newleadtype').css('display','none');
            jQuery('#selectleadtypes').append('<option value="'+data+'" selected="selected">'+getval+'</option>'); 
            jQuery("#selectleadtypes").val(data);
         
            },
            
           error: function(errorThrown){
            console.log(errorThrown);
             }
        });
        }
         
     }); 
          
          //lead email folder on change
      jQuery('.leademailfolders').on('change',function(){
        var folderid = jQuery(this).val();
        jQuery.ajax({
         url: '<?php echo admin_url( "admin-ajax.php" ) ?>',
         data: {
                    'action':  'getleadEmail',
                    'folderid' : folderid
                },
         success:function(data) {
        //success of function call to show in options
     
        if(folderid){
               jQuery(".send-email").show(); 
               jQuery(".title-send-email").show();
               jQuery(".send-email").empty();
               jQuery(".email-show").addClass("send-email");
               jQuery(".send-email").append('<option value="">Select Email</option>');
               $.each( data, function( key, value ){
               jQuery(".send-email").append('<option value="'+value.id+'">'+value.name+' </option>');
                        });
               jQuery(".email-show").prop("required", true);
        }
        else{
            jQuery(".email-show").prop("required", false);
            jQuery(".email-show").empty();
            jQuery(".send-email").hide();
            jQuery(".title-send-email").hide();
        }
        if(data.length == 0) {
            jQuery(".email-show").prop("required", false);
            jQuery(".email-show").empty();
            jQuery(".send-email").append('<option value="">No Email Here</option>');
        }
             },
             error: function(errorThrown){
               console.log(errorThrown);
             }
          });
        });
     });
   </script>

   <?php if(!empty($select_folder_id)) : ?>
        <script>
            jQuery(document).ready(function() {
                $(".send-email").show(); 
                $(".title-send-email").show();
            });
         </script> 
   <?php   
        endif;
        if(empty($select_email_id)) : ?>
           <script>
                jQuery(document).ready(function() {
                  jQuery(".email-show").prop("required", false);
                });
           </script>
   <?php   
       endif;
   ?>