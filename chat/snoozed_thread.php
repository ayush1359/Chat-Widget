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

$thread_id=mysqli_real_escape_string($connWrite,$_REQUEST['thread_id']);
$user_id='62304';
$mailbox_id = mysqli_real_escape_string($connWrite,$_REQUEST['mailbox_id']);
$snoozed = mysqli_real_escape_string($connWrite,$_REQUEST['snoozed_till']);
$snoozed_till = strtotime($snoozed);
$date = time();

$updatequery = mysqli_query($connWrite, "UPDATE chat_thread set is_snoozed='1',snoozed_at='$date',snoozed_till ='$snoozed_till' where mailbox_id='$mailbox_id' and id='$thread_id'");

mysqli_query($connWrite, "INSERT into thread_logs (action_type_id,mailbox_id,action_id,user_id,done_at) values ('20','$mailbox_id','$thread_id','$user_id','$date')");


?>