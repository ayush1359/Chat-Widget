<?php

include dirname(__FILE__, 4). '/config.php';
include dirname(__FILE__, 4). '/db-read.php';
include dirname(__FILE__, 3). '/headerNew.php';

 
$mailbox_id = mysqli_real_escape_string($connRead,$_REQUEST['mailbox_id']);
$conversation_id =mysqli_real_escape_string($connRead,$_REQUEST['conversation_id']);
$client_user_id = mysqli_real_escape_string($connRead,$_REQUEST['user_id']);
if(!$client_user_id){
  $client_user_id = mysqli_real_escape_string($connRead,$_COOKIE['hw_widget_uid']);
}
$contact_id ='';

$sql1=mysqli_query($connRead,"SELECT id FROM contact_map WHERE user_id ='$client_user_id' and mailbox_id = '$mailbox_id'");
$no1=mysqli_num_rows($sql1);
if($no1>0){
  while($row1=mysqli_fetch_array($sql1)){
    $contact_id =$row1['id'];
  }
}

$sql=mysqli_query($connRead,"SELECT id,body,date_time,type from chat_message where mailbox_id='$mailbox_id' and contact_id ='$contact_id' and conversation_id = '$conversation_id'");
$no = mysqli_num_rows($sql);
$data = array();
if($no>0){
	while($row=mysqli_fetch_assoc($sql)){
	$data[] = $row;
	}
}

$response = $data;
echo prepareAPIResponse("success",$response);
?>




