<?php
   $uuid = isset($_COOKIE['hw_widget_uid']) ? $_COOKIE['hw_widget_uid'] : genUUID();
   setcookie('hw_widget_uid', $uuid, time() + (10 * 365 * 24 * 60 * 60));
   function genUUID()
   {
       return sprintf(
           '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
   
           // 32 bits for "time_low"
           mt_rand(0, 0xffff),
           mt_rand(0, 0xffff),
   
           // 16 bits for "time_mid"
           mt_rand(0, 0xffff),
   
           // 16 bits for "time_hi_and_version",
           // four most significant bits holds version number 4
           mt_rand(0, 0x0fff) | 0x4000,
   
           // 16 bits, 8 bits for "clk_seq_hi_res",
           // 8 bits for "clk_seq_low",
           // two most significant bits holds zero and one for variant DCE1.1
           mt_rand(0, 0x3fff) | 0x8000,
   
           // 48 bits for "node"
           mt_rand(0, 0xffff),
           mt_rand(0, 0xffff),
           mt_rand(0, 0xffff)
       );
   }
   ?>
<?php
   $server="localhost";
   $user = "root";
   $password = "";
   $db = "chat";
   $flag = 0;
   $conn = mysqli_connect($server,$user,$password,$db);
   $user_id = mysqli_real_escape_string($conn,$_REQUEST['user_id']);
   if($user_id=='NULL'||$user_id =='undefined' || $user_id ==''){
     $user_id = $uuid;
     $flag = 1;
   }
   $mailbox_id = mysqli_real_escape_string($conn,$_REQUEST['mailbox_id']);
   $firstname = mysqli_real_escape_string($conn,$_REQUEST['firstname']);
   $lastname = mysqli_real_escape_string($conn,$_REQUEST['lastname']);
   $email = mysqli_real_escape_string($conn,$_REQUEST['email']);
   
   $sql=mysqli_query($conn,"SELECT contact_id FROM contact_map where user_id ='$user_id' and mailbox_id = '$mailbox_id'");
   
   $no = mysqli_num_rows($sql);
   
   if($no>0){
     while($row=mysqli_fetch_assoc($sql)){
       $contact_id=$row['contact_id'];
     }
     if($flag==0){
     $updatequery = mysqli_query($conn, "UPDATE contact_names set firstname = '$firstname',lastname = '$lastname', email = '$email' where id ='$contact_id' and user_id='$user_id' and mailbox_id='$mailbox_id'");
   }
   }else{
     if($flag==1){
     $insertquery = mysqli_query($conn, "INSERT into contact_names (user_id,mailbox_id,firstname,lastname, email) values('$user_id','$mailbox_id','$firstname','$lastname','$email')");
   
     $contact_id = mysqli_insert_id($conn);
   }else{
     $contact_id=0;
   }
   $insertquery1 = mysqli_query($conn, "INSERT into contact_map (user_id,mailbox_id,contact_id) values('$user_id','$mailbox_id','$contact_id')");
   }
   ?>
<!DOCTYPE html>
<html>
   <head>
      <title>Chat Widget</title>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <meta http-equiv="X-UA-Compatible" content="ie=edge">
      <title>Chat</title>
      <link href="https://cdn.helpwise.io/assets/theme/template/lib/@fortawesome/fontawesome-free/css/all.min.css"
         rel="stylesheet">
      <link href="https://cdn.helpwise.io/assets/theme/template/lib/ionicons/css/ionicons.min.css" rel="stylesheet">
      <link rel="stylesheet"
         href="https://cdn.helpwise.io/assets/theme/template/lib/bootstrap-tagsinput/bootstrap-tagsinput.css">
      <link rel="stylesheet"
         href="https://cdn.helpwise.io/assets/theme/template/lib/bootstrap-tagsinput/bootstrap-tagsinput-typeahead.css">
      <link rel="stylesheet" href="https://cdn.helpwise.io/assets/libs/tribute.css">
      <link rel="stylesheet" href="https://cdn.helpwise.io/assets/css/lib/file-icon-vivid.min.css">
      <link rel="stylesheet" href="https://cdn.helpwise.io/assets/libs/emojionearea.min.css">
      <link href="https://cdn.helpwise.io/assets/summernote/summernote-bs4.css" rel="stylesheet">
      <!-- DashForge CSS -->
      <link rel="stylesheet" href="https://cdn.helpwise.io/assets/theme/template/assets/css/dashforge.css">
      <link rel="stylesheet" href="https://cdn.helpwise.io/assets/theme/template/assets/css/dashforge.mail.css">
      <link rel="stylesheet" href="https://cdn.helpwise.io/assets/theme/template/assets/css/dashforge.profile.css">
      <script src="//cdn.helpwise.io/assets/js/libs.js?v=3"></script>
   </head>
   <body>
      <style type="text/css">
         /*#conversation-list{
         padding: 10px;
         overflow: scroll;
         margin: 20px;
         }*/
         .card-footer{
         border-top-style: none;
         }
         .create_new_conversation{
         width: 100%;
         height: 100%;
         padding: 20px;
         overflow: scroll;
         }
         .chat_widget_show_conversation{
         width: 100%;
         height: 100%;
         }
         .helpwise-home-screen-start-conversation-card{
         /*display: flex;
         flex-wrap: wrap;
         -webkit-box-align: center;
         align-items: center;*/
         margin-top: 50%;
         margin-left: 20px;
         width: 90%;
         height: 80%;
         padding: 10px;
         position: fixed;
         line-height: 1.5;
         }
         .helpwise-messenger-card-text-header{
         line-height: 1.5;
         word-break: break-word;
         font-size: 16px;
         text-align: left;
         margin: 20px 20px 10px;
         }
         .helpwise-messenger-card-text{
         line-height: 1.5;
         margin-left: 20px;
         margin-bottom: 10px;
         word-break: break-word;
         font-size: 14px;
         text-align: left;
         }
         .new-conversation-button{
         height: 40px;
         font-size: 13px;
         line-height: 40px;
         pointer-events: auto;
         cursor: pointer;
         text-align: center;
         background-color: rgb(0,104,250);
         color: rgb(255,255,255);
         border-radius: 40px;
         padding: 0px 24px;
         margin: 20px 10px 10px 10px;
         }
         .helpwise-anchor{
         color: rgb(0,104,250);
         cursor: pointer;
         }
         #new-conversation{
         height: 40px;
         font-size: 13px;
         line-height: 40px;
         pointer-events: auto;
         cursor: pointer;
         text-align: center;
         background-color: rgb(0,104,250);
         color: rgb(255,255,255);
         border-radius: 40px;
         padding: 0px 24px;
         margin: 10px 10px 10px 10px;
         }
         /*.helpwise-compose-message{
         width: 100%;
         position: fixed;
         bottom: 0px;
         left: 0px;
         border-width: initial;
         overflow-wrap: break-word;
         }*/
         textarea{
         box-sizing: border-box;
         position: fixed;
         bottom: 0px;
         left: 0px;
         width: 100%;
         font-family: intercom-font, "Helvetica Neue", "Apple Color Emoji", Helvetica, Arial, sans-serif;
         font-size: 14px;
         font-weight: normal;
         line-height: 1;
         background-color: rgb(255, 255, 255);
         white-space: pre-wrap;
         overflow-wrap: break-word;
         padding: 10px 100px 18px 29px;
         }
      </style>
      <div class = "chat-widget" style="width: 100vw; height: 100vh;">
         <div class="helpwise-home-screen-start-conversation-card d-none">
            <div class="card">
               <h2 class="helpwise-messenger-card-text helpwise-messenger-card-text-header">Start a conversation</h2>
               <div class="helpwise-home-screen-start-conversation-card-message">
                  <div class="helpwise-messenger-card-text ">The team typically replies in a few minutes.</div>
               </div>
               <div class="helpwise-user">
                  <!-- <div class="helpwise-hqjkmt e1e91qmz0">
                     <div class="helpwise-1cdhepc e1e91qmz1">
                        <div class="helpwise-79elbk ecseou40">
                           <div size="52" class="helpwise-gk51dj e1chjwx0"><img src="https://static.helpwiseassets.com/avatars/3635690/square_128/myimage-1575539192.jpeg?1575539192" alt="Prabhat profile"></div>
                        </div>
                     </div>
                     <div class="helpwise-1rbeeec e1e91qmz1">
                        <div class="helpwise-79elbk ecseou40">
                           <div size="52" class="helpwise-gk51dj e1chjwx0"><img src="https://static.helpwiseassets.com/avatars/3654524/square_128/photo-1575547491.jpg?1575547491" alt="vibhor profile"></div>
                        </div>
                     </div>
                     <div class="helpwise-1rbeeec e1e91qmz1">
                        <div class="helpwise-79elbk ecseou40">
                           <div size="52" class="helpwise-gk51dj e1chjwx0"><img src="https://static.helpwiseassets.com/avatars/3654540/square_128/30739494_1333374470126868_2987002290960859136_n-1575371048.jpg?1575371048" alt="Rishabh profile"></div>
                        </div>
                     </div>
                     </div> -->
                  <button class="new-conversation-button" tabindex="0">
                     <div class="helpwise-start-new-conversation">
                        <svg focusable="false" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-send">
                           <line x1="22" y1="2" x2="11" y2="13"></line>
                           <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                        </svg>
                        <span>New conversation</span>
                     </div>
                  </button>
                  <span class="helpwise-see-conversation-list"><span class="helpwise-anchor helpwise-see-all-link" role="button" tabindex="0">See previous</span></span>
               </div>
            </div>
         </div>
         <div class = "chat_widget_show_conversation d-none">
            <div class="card">
               <div class="card-header" style="height: 10vh; background-color: #0388fc;">
                  <span class="back_show_home_page">
                     <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                     </svg>
                     Your Conversations
                  </span>
               </div>
               <div class="card-body chat_widget_conversation_list" style= "overflow: auto; width: 100vh;
                  height: 80vh;" id="conversation-list">
               </div>
               <button class="new-conversation-button" style="position: fixed; bottom: 0px; margin-left:28%;" tabindex="0">
                  <div class="helpwise-start-new-conversation">
                     <svg focusable="false" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-send">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                     </svg>
                     <span>New conversation</span>
                  </div>
               </button>
            </div>
         </div>
         <div class = "chat_widget_new_conversation d-none">
            <div class="card">
               <div class="card-header" style="height: 10vh; background-color: #0388fc;">
                  <span class="back_show_conversation_list">
                     <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                     </svg>
                     Helpwise Chat
                  </span>
               </div>
               <div class="card-body create_new_conversation" style= "overflow: auto; width: 100vw;
                  height: 80vh;">
               </div>
               <div class="card-footer" style="position: fixed; bottom: 5px; height: 10vh; width: 100vw;">
                  <form class="chat_widget_form_field">
                     <fieldset class="form-fieldset pd-x-10" style="border: none; padding-top: 0px; padding-bottom:0px;">
                        <div class="input-group">
                           <input name = "inputmessage" id="send-new-conversation-message-input" type="text" class="form-control input-sm chat_input" id="input-send-new-conversation-message" placeholder="Write your message here..." />
                           <span class="input-group-btn">
                           <button class="btn btn-primary btn-sm" type="button" id="send-new-conversation-message">Send</button>
                           </span>
                        </div>
                     </fieldset>
                  </form>
               </div>
            </div>
         </div>
         <div class = "chat_widget_show_messages d-none">
            <div class="card">
               <div class="card-header" style="height: 10vh; background-color: #0388fc;">
                  <span class="back_show_conversation_list">
                     <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                     </svg>
                     Helpwise Chat
                  </span>
               </div>
               <div class="card-body conversation_messages_box" style= "overflow: auto; width: 100vw;
                  height: 80vh;">
               </div>
               <div class="card-footer" style="position: fixed; bottom: 5px;height: 10vh; width: 100vw;">
                  <form class="chat_widget_form_field">
                     <fieldset class="form-fieldset pd-x-10" style="border: none; padding-top: 0px; padding-bottom:0px;">
                        <div class="input-group">
                           <input name = "inputmessage" id="send-existing-conversation-message-input" type="text" class="form-control input-sm chat_input" id="input-send-existing-conversation-message" placeholder="Write your message here..." />
                           <span class="input-group-btn">
                           <button class="btn btn-primary btn-sm" type="button" id="send-existing-conversation-message">Send</button>
                           </span>
                        </div>
                     </fieldset>
                  </form>
               </div>
            </div>
         </div>
      </div>
      <script>
         const user_id = "<?php echo($user_id)?>"
         const mailbox_id = "<?php echo($mailbox_id)?>"
      </script>
      <script src="widget.js"></script>
   </body>
</html>