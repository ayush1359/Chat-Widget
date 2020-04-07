<?php
include dirname(__FILE__, 2). '/config.php';
include dirname(__FILE__, 2). '/db-read.php';
include 'headerNew.php';

include dirname(dirname(__FILE__)) . '/vendor/autoload.php';

if ($user["is_admin"] == 0) {
    $managerId = $user["id"];
} else {
    $managerId = $user["manager_id"];
}
$userId = $user["id"];

use Aws\S3\S3Client;
use Aws\Exception\AwsException;


if (isset($_GET['threadID']) and isset($_GET['mailboxID'])) {
    $mailboxID = unhideID($_GET['mailboxID'], ID_HIDER);
    $threadID = unhideID($_GET['threadID'], ID_HIDER);
    if (isset($_GET['labelID'])) {
        $labelID = $_GET['labelID'];
    }
    $userID = $user['id'];
    if (!threadAuthStatus($connRead, $user, $threadID)) {
        echo(prepareAPIResponse('error', null, 'forbidden access to use this thread'));
     }
    else {
        // $labelStr = '';
        // if ($labelID == '5') {
        //     $labelStr = " AND is_deleted='1' AND label_id!='2'";
        // } elseif ($labelID == '2') {
        //     $labelStr = " AND label_id='2' AND user_id='$userID' AND is_deleted='0'";
        // } else {
        //     $labelStr = " AND is_deleted='0'";
        // }

        $managerid = $user['is_admin'] == 0 ? $user['id'] : $user['manager_id'];

        $all_users = $mem->get("account-$managerid-users");
        if (!gettype($all_users)!='array') {
            $all_users = array();
            $all_users_query = mysqli_query($connRead, "SELECT id,first_name,last_name,email FROM users WHERE (id='$managerid' or manager_id='$managerid')");
            while ($aud = mysqli_fetch_array($all_users_query)) {
                $userInfo = [
                'id' => hideID($aud['id'], ID_HIDER),
                'firstname' => htmlspecialchars($aud['first_name']),
                'lastname' => htmlspecialchars($aud['last_name']),
                'email' => htmlspecialchars(extract_emails_from($aud['email'])[0])
            ];
                $all_users[$aud['id']] = $userInfo;
            }
            $mem->set("account-$managerid-users", $all_users, 604800); // 1 week
        }

        // $threadtags =  $mem->get("thread-$threadID-tags");
        // if (!gettype($threadtags)!='array') {
        //     $qryTags = "SELECT ET.id,ET.name,ET.color,ETM.mail_id AS thread_id FROM emails_tags AS ET,emails_tags_mapping AS ETM WHERE ET.mailbox_id='$mailboxID' AND ET.id=ETM.tags_id AND ETM.mail_id IN ($threadID) AND ETM.delete_status='0'";
        //     $resTags = mysqli_query($connRead, $qryTags);
        //     $tagArray = [];
        //     if ($resTags and mysqli_num_rows($resTags) > 0) {
        //         while ($rowTags = $resTags->fetch_assoc()) {
        //             array_push($tagArray, [
        //             'id' => hideID($rowTags['id'], ID_HIDER),
        //             'name' => htmlspecialchars($rowTags['name']),
        //             'color' => $rowTags['color']
        //         ]);
        //         }
        //     }
        //     $threadtags = $tagArray;
        //     $mem->set("thread-$threadID-tags", $threadtags, 604800); // 1 week
        // }

        $messagesDataMap = [];
        $messageIDs = [];
        $query = "SELECT id,conversation_id,contact_id,body,date_time,type from chat_message where mailbox_id='$mailboxID' and conversation_id = '$threadID'";
        $result = mysqli_query($connRead, $query);
        $messages = [];
        while ($result and $row = $result->fetch_assoc()) {
            $row["body"] = $row["body"];
            $row["type"] = $row["type"];
            $row["date_time"] = $row["date_time"];
            $messageID = $row['id'];
            array_push($messageIDs, $row['id']);
            $messagesDataMap[$row['id']] = $row;
        }

        foreach($messageIDs as $messageID){
        //     $nonDraft = $emailsDataMap[$emailID]["label_id"] != 2;
        //     $sentBy = $all_users[$emailsDataMap[$emailID]['user_id']];

        //     if ($nonDraft) {
        //         $memData = $mem->get("email-$mailboxID-$threadID-$emailID");
        //     }

            // if (!isset($memData) || !$memData) {
            //     // if (true) {
            //     $html = getEmailHtmlFromAws($mailboxID, $emailsDataMap[$emailID]['id']) . "";
            //     $text = null;
            //     if (empty($html)) {
            //         $text = getEmailTextFromAws($mailboxID, $emailsDataMap[$emailID]['id']) . "";
            //         $html = null;
            //     }
            //     if ($nonDraft) {
            //         $mem->set("email-$mailboxID-$threadID-$emailID", json_encode([
            //             "html" => $html,
            //             "text" => $text
            //         ]), 604800);
            //     }
            // } else {
            //     $memData = json_decode($memData, true);
            //     $html = $memData["html"];
            //     $text = $memData["text"];
            // }

            // $memData = null;
            // if (isset($html) and !empty($html)) {
            //     libxml_use_internal_errors(true);

            //     $domHtml = new DOMDocument();
            //     $domHtml->preserveWhiteSpace = false;

            //     $domHtml->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));

            //     $script = $domHtml->getElementsByTagName('script');
            //     $style = $domHtml->getElementsByTagName('style');
            //     $base = $domHtml->getElementsByTagName('base');
            //     $ahref = $domHtml->getElementsByTagName('a');
            //     $meta  = $domHtml->getElementsByTagName("meta");
            //     $imgs = $domHtml->getElementsByTagName('img');

            //     $removeHtml = [];

            //     foreach ($meta as $item) {
            //         array_push($removeHtml, $item);
            //     }

            //     foreach ($script as $item) {
            //         array_push($removeHtml, $item);
            //     }

            //     foreach ($style as $item) {
            //         array_push($removeHtml, $item);
            //     }

            //     foreach ($base as $item) {
            //         array_push($removeHtml, $item);
            //         $basehref = $item->getAttribute('href');
            //     }
            //     if (isset($basehref)) {
            //         foreach ($ahref as $item) {
            //             $href = $item->getAttribute('href');
            //             $item->setAttribute('href', urljoin($basehref, $href));
            //         }
            //     }

            //     foreach ($imgs as $img) {
            //         $url = $img->getAttribute('src');
            //         if (strpos($url, "https://app.helpwise.io/api/get-attachment")===false && strpos($url, "https://app.helpwise.io/attachments")===false) {
            //             $img->setAttribute('src', "https://app.helpwise.io/proxyImage?url=$url");
            //         }
            //     }

            //     foreach ($removeHtml as $item) {
            //         $item->parentNode->removeChild($item);
            //     }

            //     $html = $domHtml->saveHTML();
            //     $html = preg_replace("#class=\".*mail-wrapper.*?\"#", "class=\"21787821\"", $html);
            //     // $html =  mb_convert_encoding($html, 'UTF-8', 'HTML-ENTITIES');
            //     libxml_clear_errors();
            // }

            array_push($messages, [
                'id' => hideID($messagesDataMap[$messageID]['id'], ID_HIDER),
                'threadID' =>hideID($messagesDataMap[$messageID]['conversation_id'], ID_HIDER),
                'contact_id' => hideID($messagesDataMap[$messageID]['contact_id'], ID_HIDER),
                'date' => date(DATE_ISO8601,($messagesDataMap[$messageID]['date_time'])),
                //'humanFriendlyDate' => time_elapsed_string("@" . $messagesDataMap[$messageID]['date_time']),
                "body"=>$messagesDataMap[$messageID]["body"],
                "type"=>$messagesDataMap[$messageID]["type"]
            ]);
        }

        $userManagerID = $user['manager_id'];
        
        $assignmentLogs = $mem->get("thread-$threadID-assignmentLogs");
        if (gettype($assignmentLogs)!='array') {
            $assignmentLogs = [];
            $query = "SELECT assigner_id, assigned_id, timestamp from assignment where  thread_id='$threadID' and mailbox_id='$mailboxID' AND is_deleted='0'";
            $result = mysqli_query($connRead, $query);
            while ($row = $result->fetch_assoc()) {
                array_push(
                    $assignmentLogs,
                    [
                    "assigner" => isset($row['assigner_id']) ? $all_users[$row['assigner_id']] : null,
                    "assigned" =>$row['assigned_id'] != 0 ? $all_users[$row['assigned_id']] : null,
                    "time" => date(DATE_ISO8601, strtotime($row['timestamp'])),
                    //'humanFriendlyDate' => time_elapsed_string("@" . strtotime($row['timestamp']))
                ]
                );
            }
            $mem->set("thread-$threadID-assignmentLogs", $assignmentLogs, 604800);
        }

        // $tagsLogs = $mem->get("thread-$threadID-tagLogs");
        // if (gettype($tagsLogs)!='array') {
        //     $tagsLogs = [];
        //     $query = "SELECT tl.user_id, tl.done_at as time, tl.id, tl.action_type_id as tag_operation, etm.id as etm_id, et.name as tagname  from thread_logs tl, emails_tags et, emails_tags_mapping etm where tl.thread_id='$threadID' and tl.mailbox_id='$mailboxID' and tl.action_type_id in (13,14) and etm.id=tl.action_id and etm.tags_id=et.id ORDER by time ASC";
        //     $result = mysqli_query($connRead, $query);
        //     while ($row = $result->fetch_assoc()) {
        //         array_push($tagsLogs, [
        //         "id" => hideID($row['id'], ID_HIDER),
        //         "user" => $all_users[$row['user_id']],
        //         "tag" => [
        //             "id" => hideID($row['etm_id'], ID_HIDER),
        //             "name" => htmlspecialchars($row['tagname']),
        //             "status" => gettagStatus($row['tag_operation']),
        //         ],
        //         'humanFriendlyDate' => time_elapsed_string("@" . strtotime($row['time'])),
        //         "time" => date(
        //             DATE_ISO8601,
        //             strtotime($row['time'])
        //         )
        //     ]);
        //         $mem->set("thread-$threadID-tagLogs", $tagsLogs, 604800); // 1 week
        //     }
        // }

        $rowLogs = $mem->get("thread-$threadID-rowLogs");
        if (gettype($rowLogs)!='array') {
            $qryLogs = "SELECT thread_logs.id as id,thread_logs.done_at as `time`, thread_logs.action_type_id ,thread_logs.snooze_till,chat_thread.snoozed_at,thread_logs.user_id FROM thread_logs LEFT JOIN chat_thread ON chat_thread.id=thread_logs.action_id WHERE thread_logs.mailbox_id='$mailboxID' AND thread_logs.action_id='$threadID' AND thread_logs.action_type_id IN ('18','19','20','21')";
            
            $resLogs = mysqli_query($connRead, $qryLogs);
            $rowLogs = [];
            while ($resLogs and $rowLog = $resLogs->fetch_assoc()) {
                array_push($rowLogs, $rowLog);
            }
            $mem->set("thread-$threadID-rowLogs", $rowLogs, 604800); // 1 week
        }
        $closeLogs = [];
        $snoozeLogs = [];
        foreach ($rowLogs as $rowLog) {
            $displayUser = $all_users[$rowLog['user_id']];
            if ($rowLog['action_type_id'] == 18 || $rowLog['action_type_id'] == 19) {
                array_push($closeLogs, [
                    'id' =>hideID($rowLog['id'], ID_HIDER),
                    'user' => $displayUser,
                    'time' => date(DATE_ISO8601,($rowLog['time'])),
                    //'humanFriendlyDate' => time_elapsed_string("@" . $rowLog['date_time']),
                    'status' => $rowLog['action_type_id'] == 18 ? 'closed' : 'reopened'
                ]);
            } elseif ($rowLog['action_type_id'] == 20 || $rowLog['action_type_id'] == 21) {
                array_push($snoozeLogs, [
                    'id' => hideID($rowLog['id'], ID_HIDER),
                    'user' => $displayUser,
                    'time' => date(DATE_ISO8601,($rowLog['time'])),
                    //'humanFriendlyDate' => time_elapsed_string("@" . $rowLog['date_time']),
                    //'humanFriendlySnoozedTill' => $rowLog["snooze_till"] ? time_elapsed_string("@" . $rowLog['snooze_till']) : null,
                    'status' => $rowLog['action_type_id'] == 20 ? 'snoozed' : 'snooze-ended',
                    'snoozedTill' => $rowLog['snooze_till']
                ]);
            }
        }

        // $activities = $mem->get("thread-$threadID-activities");
        // if (gettype($activities)!='array') {
        //     $query = "SELECT `id`,`user_id`,`json`,`type`,`timestamp` FROM activity where thread_id='$threadID' AND mailbox_id='$mailboxID' AND is_deleted=0";
        //     $result = mysqli_query($connRead, $query);
        //     $activities = [];
        //     if ($result and mysqli_num_rows($result) > 0) {
        //         while ($row = $result->fetch_assoc()) {
        //             if ($row["type"] == 1) {
        //                 $activityType = "call";
        //             } else {
        //                 $activityType = "custom";
        //             }
        //             array_push($activities, [
        //                 "id" => hideID($row['id'], ID_HIDER),
        //                 "body" => json_decode($row['json'], true),
        //                 "createdAt" => date(DATE_ISO8601, strtotime($row['timestamp'])),
        //                 "user" => $all_users[$row['user_id']],
        //                 "type" => $activityType,
        //                 'humanFriendlyDate' => time_elapsed_string("@" . strtotime($row["timestamp"]))
        //             ]);
        //         }
        //         $mem->set("thread-$threadID-activities", $activities, 604800); // 1 week
        //     }
        // }

        $logs = [
            'assignment' => $assignmentLogs,
            //'tag' => $tagsLogs,
            'snooze' => $snoozeLogs,
            'close' => $closeLogs,
        ];

        if ($user['manager_id'] == null or $user['is_admin'] == 0) {
            $accountID = $user['id'];
        } else {
            $accountID = $user['manager_id'];
        }

        $query = "SELECT contact_id FROM chat_message WHERE mailbox_id='$mailboxID' AND conversation_id='$threadID' LIMIT 1";
         $result = mysqli_query($connRead, $query);
         if (!$result) {
             echo(prepareAPIResponse("error", null, "Something went wrong"));
             exit;
         }
        $contact_id = $result->fetch_assoc()['contact_id'];
         
        $contact = $mem->get("contact-$contact_id");

        if ($contact_id) {
            $query = "SELECT id,firstname,lastname,email from contact_names where id = '$contact_id' AND user_id='$accountID' AND manager_id='$accountID' ";
            $contact = [
                "id"=>"",
                "email"=>"",
                "lastname"=>"",
                "firstname"=>""
            ];
            $result = mysqli_query($connRead, $query);
            if ($result and mysqli_num_rows($result) > 0) {
                $row = $result->fetch_assoc();
                $firstname = htmlspecialchars($row['firstname']);
                $lastname = htmlspecialchars($row['lastname']);
                $email = htmlspecialchars($row['email']);
                $contact_nameID = $row['id'];
                //$contactId = hideID($contact_nameID);

                $contact = [
                    "firstname" => htmlspecialchars($firstname),
                    "lastname" => htmlspecialchars($lastname),
                    "email" => $email,
                    //"phones" => [],
                    "id" => $contact_nameID,
                    //"job" => null
                ];
                // $query = "SELECT id,type,value_id from contact_mapping where user_id='$accountID' AND contact_id='$contact_nameID' AND is_deleted=0";
                // $result = mysqli_query($connRead, $query);
                // if ($result and mysqli_num_rows($result) > 0) {
                //     $emailIDS = [];
                //     $phoneIDS = [];
                //     $jobProfileIds = [];
                //     while ($row = $result->fetch_assoc()) {
                //         if ($row['type'] == 0) {
                //             array_push($emailIDS, $row['value_id']);
                //         } elseif ($row['type'] == 1) {
                //             array_push($phoneIDS, $row['value_id']);
                //         } else {
                //             $contact['job'] = getContactJobProfile($connRead, $row['value_id']);
                //         }
                //     }
                //     $contact['emails'] = getContactEmails($connRead, $emailIDS);
                //     $contact['phones'] = getContactPhones($connRead, $phoneIDS);
                // }
                //$mem->set("contact-$uniqueEmail", $contact, 604800); // 1 week
             }
            //else {
                // $qryCreateContact = "INSERT INTO contact_names (`firstname`,`lastname`,unique_email,manager_id,user_id) VALUES('','','$uniqueEmail','$accountID','$accountID')";
                // include dirname(__FILE__, 2). '/db-write.php';
                // $resCreateContact = mysqli_query($connReadWrite, $qryCreateContact);
                // $contactId = mysqli_insert_id($connReadWrite);
                // $contact = [
                //     "id"=>hideID($contactId),
                //     "emails"=>[],
                //     "phones"=>[],
                //     "lastname"=>"",
                //     "firstname"=>"",
                //     "primaryEmail"=>htmlspecialchars($uniqueEmail)
                // ];
            //}
        }

        $userReadMap = [];
        $resReadLogs = mysqli_query($connRead, "SELECT read_at, is_read, user_id from users_threads_read where mailbox_id='$mailboxID' and thread_id='$threadID'");
        if (mysqli_num_rows($resReadLogs)) {
            while ($readLog = mysqli_fetch_array($resReadLogs)) {
                $userReadMap[$readLog['user_id']] = array("hasRead" => boolval(intval($readLog['is_read'])), "readAt" => $readLog['read_at'] ? date(DATE_ISO8601, ($readLog['read_at'])) : null);
            }
        }
        
      
        ob_start("ob_gzhandler");
        echo(prepareAPIResponse("success", ['messages' => $messages,'logs' => $logs, 'contact' => $contact, 'usersReadMap' => $userReadMap], null));

        ob_end_flush();
    }
} else {
    echo(prepareAPIResponse('error', null, "Something went wrong"));
}

function convert_filesize($bytes, $decimals = 2)
{
    $size = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
}

// function getTagStatus($num)
// {
//     if ($num == 13) {
//         return 'new';
//     } elseif ($num == 14) {
//         return 'delete';
//     } else {
//         return 'update';
//     }
// }

// function extract_emails_from($string)
// {
//     preg_match_all("/[a-z0-9_\-\+\.]+@[a-z0-9\-]+\.([a-z]{2,4})(?:\.[a-z]{2})?/i", $string, $matches);
//     return $matches[0];
// }

function urljoin($base, $rel)
{
    $pbase = parse_url($base);
    $prel = parse_url($rel);

    $merged = array_merge($pbase, $prel);
    if ($prel['path'][0] != '/') {
        // Relative path
        $dir = preg_replace('@/[^/]*$@', '', $pbase['path']);
        $merged['path'] = $dir . '/' . $prel['path'];
    }

    // Get the path components, and remove the initial empty one
    $pathParts = explode('/', $merged['path']);
    array_shift($pathParts);

    $path = [];
    $prevPart = '';
    foreach ($pathParts as $part) {
        if ($part == '..' && count($path) > 0) {
            // Cancel out the parent directory (if there's a parent to cancel)
            $parent = array_pop($path);
            // But if it was also a parent directory, leave it in
            if ($parent == '..') {
                array_push($path, $parent);
                array_push($path, $part);
            }
        } elseif ($prevPart != '' || ($part != '.' && $part != '')) {
            // Don't include empty or current-directory components
            if ($part == '.') {
                $part = '';
            }
            array_push($path, $part);
        }
        $prevPart = $part;
    }
    $merged['path'] = '/' . implode('/', $path);

    $ret = '';
    if (isset($merged['scheme'])) {
        $ret .= $merged['scheme'] . ':';
    }

    if (isset($merged['scheme']) || isset($merged['host'])) {
        $ret .= '//';
    }

    if (isset($prel['host'])) {
        $hostSource = $prel;
    } else {
        $hostSource = $pbase;
    }

    // username, password, and port are associated with the hostname, not merged
    if (isset($hostSource['host'])) {
        if (isset($hostSource['user'])) {
            $ret .= $hostSource['user'];
            if (isset($hostSource['pass'])) {
                $ret .= ':' . $hostSource['pass'];
            }
            $ret .= '@';
        }
        $ret .= $hostSource['host'];
        if (isset($hostSource['port'])) {
            $ret .= ':' . $hostSource['port'];
        }
    }

    if (isset($merged['path'])) {
        $ret .= $merged['path'];
    }

    if (isset($prel['query'])) {
        $ret .= '?' . $prel['query'];
    }

    if (isset($prel['fragment'])) {
        $ret .= '#' . $prel['fragment'];
    }
    return $ret;
}


// function getContactEmails($connRead, $emailIDS)
// {
//     $emailIDS = implode(",", $emailIDS);
//     $query = "SELECT email from contact_emails where id IN($emailIDS)";
//     $result = mysqli_query($connRead, $query);
//     if ($result and mysqli_num_rows($result) > 0) {
//         $emailJSON = [];
//         while ($row = $result->fetch_assoc()) {
//             array_push($emailJSON, htmlspecialchars($row['email']));
//         }
//         return $emailJSON;
//     } else {
//         return [];
//     }
// }

// function getContactPhones($connRead, $phoneIDS)
// {
//     $phoneIDS = implode(",", $phoneIDS);
//     $query = "SELECT phone_no,type from contact_phone where id IN($phoneIDS)";
//     $result = mysqli_query($connRead, $query);
//     if ($result and mysqli_num_rows($result) > 0) {
//         $phoneJSON = [];
//         while ($row = $result->fetch_assoc()) {
//             array_push($phoneJSON, [
//                 "phone_no" => htmlspecialchars($row['phone_no']),
//                 "type" => $row['type']
//             ]);
//         }
//         return $phoneJSON;
//     } else {
//         return [];
//     }
// }


// function getContactJobProfile($connRead, $profileID)
// {
//     $query = "SELECT name,profile from contact_company where id='$profileID'";
//     $result = mysqli_query($connRead, $query);
//     if ($result and mysqli_num_rows($result) > 0) {
//         $row = $result->fetch_assoc();
//         return [
//             "company" => htmlspecialchars($row['name']),
//             "job_title" => htmlspecialchars($row['profile'])
//         ];
//     } else {
//         return '';
//     }
// }
?>