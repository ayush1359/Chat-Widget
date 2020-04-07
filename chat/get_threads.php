<?php
include dirname(__FILE__, 2). '/config.php';
include dirname(__FILE__, 2). '/db-read.php';
include 'headerNew.php';

$page = 1;
$userId = $user['id'];

// if (isset($_GET['squery'])) {
//     // don't need to mysqli_real_escape it!!
//     $squery = strtolower($_GET['squery']);
// }

// if (isset($_GET['tagID'])) {
//     $tagId = unhideID(mysqli_real_escape_string($connRead, $_GET['tagID']), ID_HIDER);
// }

//$filter = 'none';

// if (isset($_GET['filter'])) {
//     $filter = $_GET['filter'];
// }

if (isset($_GET["order"])) {
    $order = $_GET["order"];
} else {
    $order = "newest";
}

$labelId =isset($_GET['labelID']) ? mysqli_real_escape_string($connRead, $_GET['labelID']) : 0;

if (!isset($_GET["mailboxID"])) {
    echo(prepareAPIResponse("error", null, 'mailboxID is required'));
    exit();
}


if (isset($_GET['page'])) {
    $pageNo = intval(mysqli_real_escape_string($connRead, $_GET['page']));
} else {
    $pageNo = 1;
}
$mailboxId = unhideID(mysqli_real_escape_string($connRead, $_GET['mailboxID']), ID_HIDER);

if (!mailboxAuthStatus($connRead, $user, $mailboxId)) {
    echo(prepareAPIResponse('error', null, 'Forbidden to access this mailbox'));
    exit();
}

$limit = 20;
$offset = ($pageNo - 1) * $limit;

if (isset($_GET['offset'])) {
    $offset = intval($_GET['offset']);
}
if (isset($_GET['limit'])) {
    $limit = intval($_GET['limit']);
    if ($limit > 20) {
        $limit = 20;
    }
}

$threadIds = [];
$isSearch = false;
//$isEmailQuery = false;

// search
// if (isset($squery)) {
//     $isSearch = true;
//     $searchResults = searchThreads($mailboxId, $squery, $offset, $limit);
//     $totalSearchResults = $searchResults["totalResults"];
//     $threadIds = $searchResults["threadIds"];
// }
// trash
if ($labelId == 5) {
    $qryThreadIdsWithoutOrderLimit = "SELECT id,GREATEST(latest_incoming_at,deleted_at,latest_outgoing_at) AS date_time FROM chat_thread WHERE mailbox_id='$mailboxId' AND is_deleted='1' ORDER BY GREATEST(latest_incoming_at,deleted_at,latest_outgoing_at) DESC";    
}
// closed
elseif ($labelId == 7) {
    $qryThreadIdsWithoutOrderLimit = "SELECT id,GREATEST(latest_incoming_at,archived_at,latest_outgoing_at) AS date_time FROM chat_thread WHERE mailbox_id='$mailboxId' AND is_archived='1' ORDER BY GREATEST(latest_incoming_at,archived_at,latest_outgoing_at) DESC";
}
// snoozed
elseif ($labelId == 9) {
    $qryThreadIdsWithoutOrderLimit = "SELECT id,GREATEST(latest_incoming_at,snoozed_at,latest_outgoing_at) AS date_time FROM chat_thread WHERE mailbox_id='$mailboxId' AND is_snoozed='1' ORDER BY GREATEST(latest_incoming_at,snoozed_at,latest_outgoing_at) DESC";
}
// drafts
// elseif ($labelId == 2) {
//     $qryThreadIdsWithoutOrderLimit = "SELECT T2.id,GREATEST(T1.date,T2.latest_email_at,latest_outgoing_email_at) AS date FROM (SELECT thread_id AS id,MAX(id) AS email_id,date AS date FROM emails WHERE mailbox_id='$mailboxId' AND user_id='$userId' AND label_id='2' AND is_deleted='0' GROUP BY thread_id) AS T1,threads AS T2 WHERE T1.id=T2.id AND T2.is_archived='0' AND T2.is_deleted='0' ORDER BY GREATEST(T1.date,T2.latest_email_at,latest_outgoing_email_at) DESC";
// }
// scheduled
// elseif ($labelId == 6) {
//     $isEmailQuery = true;
//     $qryThreadIdsWithoutOrderLimit = "SELECT thread_id AS id,MAX(id) AS email_id,date AS date FROM emails WHERE mailbox_id='$mailboxId' AND user_id='$userId' AND label_id='6' GROUP BY thread_id ORDER BY date DESC";
// }
// sent
// elseif ($labelId == 1) {
//     if (!empty($filter) and strpos($filter, "assignedTo") !== false) {
//         $assignedTo = explode(":", $filter)[1];
//         $assignedTo = unhideID($assignedTo);
//         $qryThreadIdsWithoutOrderLimit = "SELECT id,GREATEST(latest_outgoing_email_at,assigned_at) AS date,assigned_to FROM threads WHERE assigned_to='$assignedTo' AND mailbox_id='$mailboxId' AND latest_outgoing_email_at>'1973-01-01 00:00:00' AND is_deleted='0' AND is_spam='0' ORDER BY GREATEST(latest_outgoing_email_at,assigned_at) DESC";
//     } else {
//         $qryThreadIdsWithoutOrderLimit = "SELECT id,GREATEST(latest_outgoing_email_at,assigned_at) AS date,assigned_to FROM threads WHERE mailbox_id='$mailboxId' AND latest_outgoing_email_at>'1973-01-01 00:00:00' AND is_deleted='0' AND is_spam='0' ORDER BY GREATEST(latest_outgoing_email_at,assigned_at) DESC";
//     }
// }
// inbox,filter:unread
// elseif ($filter == "unread") {
//     $qryThreadIdsWithoutOrderLimit = "SELECT threads.id,GREATEST(threads.latest_email_at,threads.assigned_at,threads.moved_to_inbox_at) AS date,threads.assigned_to FROM threads LEFT JOIN users_threads_read ON threads.id=users_threads_read.thread_id WHERE COALESCE(users_threads_read.is_read,'0')='0' AND threads.mailbox_id='$mailboxId' AND users_threads_read.mailbox_id='$mailboxId' AND threads.is_deleted='0' AND threads.is_spam='0' AND threads.is_archived='0' AND threads.latest_email_at>'1973-01-01 00:00:00' AND threads.draft_only='0' AND user_id='$userId' ORDER BY GREATEST(threads.latest_email_at,threads.assigned_at,threads.moved_to_inbox_at) DESC";
// }
// // inbox,filter:unassigned
// elseif ($filter == "unassigned") {
//     $qryThreadIdsWithoutOrderLimit = "SELECT id,GREATEST(latest_email_at,assigned_at,moved_to_inbox_at) AS date,assigned_to FROM threads WHERE assigned_to='0' AND mailbox_id='$mailboxId' AND latest_email_at>'1973-01-01 00:00:00' AND is_deleted='0' AND is_archived='0' AND is_spam='0' AND draft_only='0' ORDER BY GREATEST(latest_email_at,assigned_at,moved_to_inbox_at)";
// }
// // inbox,filter:assignedTo
// elseif (!empty($filter) and strpos($filter, "assignedTo") !== false) {
//     $qryThreadIdsWithoutOrderLimit = "SELECT id,GREATEST(latest_email_at,assigned_at,moved_to_inbox_at) AS date,assigned_to FROM threads WHERE assigned_to='$assignedTo' AND mailbox_id='$mailboxId' AND latest_email_at>'1973-01-01 00:00:00' AND is_deleted='0' AND is_archived='0' AND is_spam='0' AND draft_only='0' ORDER BY GREATEST(latest_email_at,assigned_at,moved_to_inbox_at)";
// }
// // starred
// elseif ($labelId== 11) {
//     $qryThreadIdsWithoutOrderLimit = "SELECT T.id,GREATEST(T.latest_email_at,T.assigned_at,T.latest_outgoing_email_at,T.moved_to_inbox_at,USM.at) AS date,T.assigned_to FROM threads AS T LEFT JOIN user_star_map AS USM ON T.id=USM.thread_id WHERE COALESCE(USM.is_starred,'0')='1' AND T.mailbox_id='$mailboxId' AND USM.mailbox_id='$mailboxId' AND USM.user_id='$userId' ORDER BY GREATEST(T.latest_email_at,T.assigned_at,T.latest_outgoing_email_at,T.moved_to_inbox_at,USM.at) DESC";
//     error_log($qryThreadIdsWithoutOrderLimit);
// }
// unassigned
elseif ($labelId == 10) {
    $qryThreadIdsWithoutOrderLimit = "SELECT id,GREATEST(latest_incoming_at,assigned_at,latest_outgoing_at) AS date_time,snoozed_at,assigned_to FROM chat_thread WHERE mailbox_id='$mailboxId' AND assigned_to='0' AND latest_incoming_at>'0' AND is_deleted='0' AND is_archived='0' AND is_snoozed='0' ORDER BY GREATEST(latest_incoming_at,assigned_at,latest_outgoing_at) DESC";
    if ($order=="oldest") {
        $qryThreadIdsWithoutOrderLimit = "SELECT id,latest_incoming_at AS date_time,snoozed_at FROM chat_thread WHERE mailbox_id='$mailboxId' AND assigned_to='0' AND is_deleted='0' AND is_archived='0' AND is_snoozed='0' ORDER BY latest_incoming_at ASC";
    } elseif ($order=="waitingLongest") {
        $qryThreadIdsWithoutOrderLimit = "SELECT id,latest_incoming_at AS date_time,snoozed_at FROM chat_thread WHERE mailbox_id='$mailboxId' AND assigned_to='0' AND is_deleted='0' AND is_archived='0' AND is_snoozed='0' ORDER BY latest_email_at DESC";
    }
}
// mine
elseif ($labelId == 4) {
    $qryThreadIdsWithoutOrderLimit = "SELECT id,GREATEST(latest_incoming_at,assigned_at,latest_outgoing_at) AS date_time,snoozed_at FROM chat_thread WHERE mailbox_id='$mailboxId' AND assigned_to='$userId' AND is_deleted='0' AND is_archived='0' AND is_snoozed='0' ORDER BY GREATEST(latest_incoming_at,assigned_at,latest_outgoing_at) DESC";
    if ($order=="oldest") {
        $qryThreadIdsWithoutOrderLimit = "SELECT id,latest_incoming_at AS date_time,snoozed_at FROM chat_thread WHERE mailbox_id='$mailboxId' AND assigned_to='$userId' AND is_deleted='0'AND is_archived='0' AND is_snoozed='0' ORDER BY latest_incoming_at ASC";
    } elseif ($order=="waitingLongest") {
        $qryThreadIdsWithoutOrderLimit = "SELECT id,latest_incoming_at AS date_time,snoozed_at FROM chat_thread WHERE mailbox_id='$mailboxId' AND assigned_to='$userId' AND is_deleted='0' AND is_archived='0' AND is_snoozed='0' ORDER BY latest_email_at DESC";
    }
}
// assigned
elseif($labelId == 0) {
    $qryThreadIdsWithoutOrderLimit = "SELECT id,GREATEST(latest_incoming_at,assigned_at,latest_outgoing_at) AS date_time,snoozed_at,assigned_to FROM chat_thread WHERE mailbox_id='$mailboxId' AND assigned_to>'0' AND latest_incoming_at>'0' AND is_deleted='0' AND is_archived='0' AND is_snoozed='0' ORDER BY GREATEST(latest_incoming_at,assigned_at,latest_outgoing_at) DESC";
    if ($order=="oldest") {
        $qryThreadIdsWithoutOrderLimit = "SELECT id,latest_incoming_at AS date_time,snoozed_at,assigned_to FROM chat_thread WHERE mailbox_id='$mailboxId' AND assigned_to>'0' AND is_deleted='0' AND is_archived='0' AND is_snoozed='0' ORDER BY latest_incoming_at ASC";
    } elseif ($order=="waitingLongest") {
        $qryThreadIdsWithoutOrderLimit = "SELECT id,latest_incoming_at AS date_time,snoozed_at,assigned_to FROM chat_thread WHERE mailbox_id='$mailboxId' AND assigned_to>'0' AND is_deleted='0' AND is_archived='0' AND is_snoozed='0' ORDER BY latest_incoming_at DESC";
    }
}
// if (isset($tagId)) {
//     $qryThreadIdsWithoutOrderLimit = "SELECT id,GREATEST(latest_email_at,assigned_at,latest_outgoing_email_at,moved_to_inbox_at) FROM threads WHERE id IN (SELECT mail_id AS thread_id FROM emails_tags AS ET,emails_tags_mapping AS ETM WHERE ET.id='$tagId' AND ETM.tags_id='$tagId' AND ET.mailbox_id='$mailboxId' AND ET.is_deleted='0' AND ETM.delete_status='0') ORDER BY GREATEST(latest_email_at,assigned_at,latest_outgoing_email_at,moved_to_inbox_at)";
// }
$threadIdMap = [];
$assignedTos = [];
$order = 0;

// if (!$isSearch) {
    $qryThreadIds = "$qryThreadIdsWithoutOrderLimit LIMIT $offset,$limit";
    $resThreadIds = mysqli_query($connRead, $qryThreadIds);
    if (!$resThreadIds) {
        echo(prepareAPIResponse("error", null, "Something went wrong."));
        exit();
    }
    while ($rowThreadId = mysqli_fetch_assoc($resThreadIds)) {
        $threadIdMap[$rowThreadId["id"]] = $rowThreadId;
        array_push($threadIds, $rowThreadId["id"]);
        // if ($isEmailQuery) {
        //     array_push($emailIds, $rowThreadId["email_id"]);
        // }
        if (isset($rowThreadId["assigned_to"])) {
            array_push($assignedTos, $rowThreadId["assigned_to"]);
        }
         $threadIdMap[$rowThreadId["id"]]["order"] = $order;
         $order++;
    }

// } else {
//     $order = 0;
//     foreach ($threadIds as $threadId) {
//         $threadIdMap[$threadId] = [
//             "order"=>$order++
//         ];
//     }
//}

if (count($threadIds)<=0) {
    echo(prepareAPIResponse("success", [
        "threads"=>[],
        "nextPage"=>false
    ]));
    exit();
}


$threadIdIn = join($threadIds,"','");


//$emailIdIn = join("','", $emailIds);
$userMap = [];
if ($labelId==0 && count($assignedTos)>0) {
    $assignedToIn = join("','", $assignedTos);
    $qryAssigned = "SELECT id,first_name,last_name,email FROM users WHERE id IN ('$assignedToIn')";
    $resAssigned = mysqli_query($connRead, $qryAssigned);
    if ($resAssigned) {
        while ($rowAssigned = mysqli_fetch_assoc($resAssigned)) {
            $userMap[$rowAssigned["id"]] = [
            "id" => hideID($rowAssigned["id"]),
            "firstname" => $rowAssigned["first_name"],
            "lastname" => $rowAssigned["last_name"],
            "email" => $rowAssigned["email"]
        ];
        }
    }
}

//$topEmailIds = [];

//$qryTopEmailIds = "SELECT MAX(id) AS id FROM chat_message WHERE conversation_id IN ('$threadIdIn') AND mailbox_id='$mailboxId' GROUP BY conversation_id";
// if ($isEmailQuery) {
//     $qryTopEmailIds = "SELECT MAX(id) AS id FROM chat_message WHERE id IN ('$emailIdIn') GROUP BY thread_id";
// }
// $resTopEmailIds = mysqli_query($connRead, $qryTopEmailIds);
// if (!$resTopEmailIds) {
//     echo(prepareAPIResponse("error", null, "Something Went wrong."));
//     exit();
// }
// while ($rowTopEmailId = mysqli_fetch_assoc($resTopEmailIds)) {
//     array_push($topEmailIds, $rowTopEmailId["id"]);
// }

// $topEmailIdIn = join("','", $topEmailIds);
$chatIds = array();
$query ="SELECT MAX(id) as id,conversation_id from chat_message WHERE mailbox_id='$mailboxId' and conversation_id IN ('$threadIdIn') GROUP BY conversation_id";
$sql = mysqli_query($connRead, $query);
if (!$sql) {
        echo(prepareAPIResponse("error", null, "Something went wrong."));
        exit();
    }
while ($rowid = mysqli_fetch_assoc($sql)) {
        $chatIds[] = $rowid['id'];
        }
$chatIds = join($chatIds,"','");

//$qrymessages = "SELECT MAX(id),conversation_id,SUBSTRING(body,1,200) AS body,date_time FROM chat_message WHERE mailbox_id='$mailboxId' and conversation_id IN ('$threadIdIn') GROUP BY conversation_id ";

$qrymessages = "SELECT id ,conversation_id,SUBSTRING(body,1,200) AS body,date_time FROM chat_message WHERE mailbox_id='$mailboxId' and id IN ('$chatIds')";

$resmessages = mysqli_query($connRead, $qrymessages);
if (!$resmessages) {
    echo(prepareAPIResponse("error", null, "Something Went wrong."));
    exit();
}
$responseThreads = [];
$topEmailIdThreadIdMap = [];
while ($rowmessages = mysqli_fetch_assoc($resmessages)) {
    //in utc
    // $timestamp = $isSearch ? strtotime($rowTopEmail["date"]) : strtotime($threadIdMap[$rowTopEmail["thread_id"]]["date"]);
    // $tzOffset = (new DateTimeZone($tz))->getOffset(new DateTime);
    // //in user's tz
    // $timestamp+=$tzOffset;
    // $today = date("Y-m-d");
    // $timestampDay = date("Y-m-d", $timestamp);
    // $thisYear = date("Y");
    // $timestampYear = date("Y", $timestamp);
    // $humanFriendlyDate = date("d/m/Y", $timestamp);
    // if ($today == $timestampDay) {
    //     $humanFriendlyDate = date("H:i A", $timestamp);
    // } elseif ($thisYear == $timestampYear) {
    //     $humanFriendlyDate = date("j M", $timestamp);
    // }
    //$topEmailIdThreadIdMap[$rowmessages["id"]] = $rowmessages["conversation_id"];
    $responseThreads[$rowmessages["conversation_id"]] = [
        "id" =>hideID($rowmessages["conversation_id"]),
        //"tags"=>[],
        "isRead" => false,
        "message"=>[
            "body"=> $rowmessages["body"],
            "date"=>$rowmessages["date_time"]
        ],
        "order"=>$threadIdMap[$rowmessages["conversation_id"]]["order"]
    ];
}
// check if next page exists
$nextPageOffset = $offset + $limit;
$qryNextPage = "$qryThreadIdsWithoutOrderLimit LIMIT $nextPageOffset,1";
$nextPage = false;


if  (($labelId == 0 || $labelId == 4) || $labelId == 10) {
    $qryReadStatus = "SELECT TR.is_read,T.id AS thread_id FROM chat_thread AS T, users_threads_read AS TR WHERE T.id IN ('$threadIdIn') AND T.mailbox_id='$mailboxId' AND TR.mailbox_id='$mailboxId' AND T.id=TR.thread_id AND TR.user_id='$userId'";
    $resReadStatus = mysqli_query($connRead, $qryReadStatus);
    while ($resReadStatus and $rowReadStatus = $resReadStatus->fetch_assoc()) {
        $responseThreads[$rowReadStatus['thread_id']]['isRead'] = boolval(intval($rowReadStatus['is_read']));
    }
}

$response=json_encode($responseThreads,true);



// if ($labelId!=11) {
//     $qryStarStatus = "SELECT is_starred,thread_id FROM user_star_map WHERE thread_id IN ('$threadIdIn') AND mailbox_id='$mailboxId' AND user_id='$userId'";
//     $resStarStatus = mysqli_query($connRead, $qryStarStatus);
//     if ($resStarStatus) {
//         while ($rowStarStatus = mysqli_fetch_assoc($resStarStatus)) {
//             $responseThreads[$rowStarStatus['thread_id']]['isStarred'] = boolval(intval($rowStarStatus['is_starred']));
//         }
//     }
// }

// $qryTags = "SELECT ET.id,ET.name,ET.color,ETM.mail_id AS thread_id FROM emails_tags AS ET,emails_tags_mapping AS ETM WHERE ET.mailbox_id='$mailboxId' AND ET.id=ETM.tags_id AND ETM.mail_id IN ('$threadIdIn') AND ETM.delete_status='0'";
// $resTags = mysqli_query($connRead, $qryTags);
// while ($resTags and $rowTags = $resTags->fetch_assoc()) {
//     array_push($responseThreads[$rowTags['thread_id']]['tags'], [
//         'id' => hideID($rowTags['id'], ID_HIDER),
//         'name' => htmlspecialchars($rowTags['name']),
//         'color' => $rowTags['color']
//     ]);
// }

$responseThreadsCount = count($responseThreads);
if ($responseThreadsCount>10) {
    ob_start("ob_gzhandler");
}

$threads = array_values($responseThreads);
usort($threads, "compareFunc");

echo(prepareAPIResponse('success', ['threads' => $threads , 'nextPage' => $nextPage]));

if ($responseThreadsCount>10) {
    ob_end_flush();
}


function compareFunc($threadA, $threadB)
{
    $orderA =  $threadA["order"];
    $orderB =  $threadB["order"];
    return $orderA - $orderB;
}
?>
