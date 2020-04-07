<?php
include dirname(__FILE__, 4). '/config.php';
include dirname(__FILE__, 4). '/db-write.php';
include dirname(__FILE__, 3). '/headerNew.php';

$thread_id=mysqli_real_escape_string($connWrite,$_REQUEST['thread_id']);
$assigner_id='29038';
$assigned_id=mysqli_real_escape_string($connWrite,$_REQUEST['assigned_id']);
$mailbox_id =mysqli_real_escape_string($connWrite,$_REQUEST['mailbox_id']);
$date = time();

$updatequery = mysqli_query($connWrite, "UPDATE assignment set latest = '0' where thread_id='$thread_id' and mailbox_id='$mailbox_id'");

mysqli_query($connWrite, "INSERT into assignment (thread_id ,assigner_id, assigned_id, mailbox_id) values ('$thread_id','$assigner_id','$assigned_id','$mailbox_id')");

$updatequery = mysqli_query($connWrite, "UPDATE chat_thread set assigned_to = '$assigned_id', assigned_at='$date' where mailbox_id='$mailbox_id' and id='$thread_id'");
?>