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
$mailbox_id =mysqli_real_escape_string($connWrite,$_REQUEST['mailbox_id']);
$read_at=time();

$sql = mysqli_query($connWrite,"SELECT is_read from users_threads_read where user_id = '$userid' and thread_id='$thread_id' and mailbox_id='$mailbox_id'");
$no=mysqli_num_rows($sql);
if($no>0){
	while($row=mysqli_fetch_array($sql)){
		$is_read =$row['is_read'];
	}
}else{
	mysqli_query($connWrite,"INSERT into users_threads_read(user_id, thread_id, is_read , read_at , mailbox_id) values('$userid','$thread_id','1','$read_at','$mailbox_id')");
}

$updatequery = mysqli_query($connWrite, "UPDATE users_threads_read set is_read = '1' , read_at = '$read_at' where user_id = '$userid' and thread_id='$thread_id' and mailbox_id='$mailbox_id'");
?>

