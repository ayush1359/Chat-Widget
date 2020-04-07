<?php
include dirname(__FILE__, 4). '/config.php';
include dirname(__FILE__, 4). '/db-write.php';
include dirname(__FILE__, 3). '/headerNew.php';
 
$mailbox_id = mysqli_real_escape_string($connWrite,$_REQUEST['mailbox_id']);
$conversation_id =mysqli_real_escape_string($connWrite,$_REQUEST['conversation_id']);
$client_user_id = mysqli_real_escape_string($connWrite,$_REQUEST['user_id']);
if(!$client_user_id){
	$client_user_id = mysqli_real_escape_string($connWrite,$_COOKIE['hw_widget_uid']);
}
$contact_id ='';
$body = mysqli_real_escape_string($connWrite,$_REQUEST['value']);
$datetime=time();
$assigned_to = '29038';//mysqli_real_escape_string($connWrite,$_REQUEST['assigned_id']);

$sql1=mysqli_query($connWrite,"SELECT id FROM contact_map WHERE user_id ='$client_user_id' and mailbox_id = '$mailbox_id'");
$no1=mysqli_num_rows($sql1);
if($no1>0){
	while($row1=mysqli_fetch_assoc($sql1)){
		$contact_id =$row1['id'];
	}
}
//latest_incoming_at = from Helpwise's view
if(!$conversation_id){
	$sql=mysqli_query($connWrite,"INSERT into chat_thread(mailbox_id,contact_id,date_time,assigned_to,assigned_at,latest_incoming_at) values('$mailbox_id','$contact_id','$datetime','$assigned_to','$datetime','$datetime')");
	$conversation_id = mysqli_insert_id($connWrite);
}else{
	$updatequery = mysqli_query($connWrite, "UPDATE chat_thread set date_time = '$datetime',latest_incoming_at ='$datetime' where conversation_id='$conversation_id' and contact_id='$contact_id' and mailbox_id='$mailbox_id'");
}

mysqli_query($connWrite, "INSERT into chat_message (mailbox_id,contact_id,body,date_time,conversation_id) values ('$mailbox_id','$contact_id','$body','$datetime','$conversation_id')");
$response = $conversation_id;
echo prepareAPIResponse("success",$response);

?>










