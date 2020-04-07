<?php
include dirname(__FILE__, 4). '/config.php';
include dirname(__FILE__, 4). '/db-write.php';
include dirname(__FILE__, 3). '/headerNew.php';

$userid = $user['id'];
if($user['is_admin']==0){
  	$managerid = $userid;
	}
else{
  	$managerid = $user['manager_id'];
}

$mailbox_id = mysqli_real_escape_string($connWrite,$_REQUEST['mailbox_id']);
$conversation_id =mysqli_real_escape_string($connWrite,$_REQUEST['conversation_id']);
$contact_id = mysqli_real_escape_string($connWrite,$_REQUEST['contact_id']);;
$body = mysqli_real_escape_string($connWrite,$_REQUEST['value']);
$datetime=time();


mysqli_query($connWrite, "INSERT into chat_message (mailbox_id,contact_id,body,date_time,type,conversation_id,hw_user_id) values ('$mailbox_id','$contact_id','$body','$datetime','1','$conversation_id','$userid')");

$updatequery = mysqli_query($connWrite, "UPDATE chat_thread set latest_outgoing_at ='$datetime' where mailbox_id='$mailbox_id' and id='$conversation_id'");


$response = $conversation_id;
echo prepareAPIResponse("success",$response);

?>












