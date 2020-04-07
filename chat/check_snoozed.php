<?php
include dirname(__FILE__, 4). '/config.php';
include dirname(__FILE__, 4). '/db-write.php';
include dirname(__FILE__, 3). '/headerNew.php';

$thread_id=mysqli_real_escape_string($connWrite,$_REQUEST['thread_id']);
$mailbox_id =mysqli_real_escape_string($connWrite,$_REQUEST['mailbox_id']);
$snoozed = mysqli_real_escape_string($connWrite,$_REQUEST['snoozed_till']);
$snoozed_till = strtotime($snoozed);
$date = time();
if($snoozed_till<=$date){
$updatequery = mysqli_query($connWrite, "UPDATE chat_thread set is_snoozed='0',snoozed_at='0',snoozed_till ='0' where mailbox_id='$mailbox_id' and id='$thread_id'");
}
?>