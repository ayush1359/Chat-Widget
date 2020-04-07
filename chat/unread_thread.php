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

$user_id='62304';//hw_user_id
$thread_id=mysqli_real_escape_string($connWrite,$_REQUEST['thread_id']);
$mailbox_id =mysqli_real_escape_string($connWrite,$_REQUEST['mailbox_id']);

$updatequery = mysqli_query($connWrite, "UPDATE users_threads_read set is_read = '0' where user_id = '$user_id' and thread_id='$thread_id' and mailbox_id='$mailbox_id'");
?>

