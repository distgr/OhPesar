<?php

if($callback_query){
    $data = $callback_query->data;
        
    if(strpos($data, 'accept-') !== false){
        bot('answercallbackquery', [
            'callback_query_id' => $update->callback_query->id,
            'text' => "ویس تایید شد. ✅",
            'show_alert' => false
        ]);
        $voiceid = str_replace('accept-', '', $data);
        $db->query("UPDATE `voices` SET `accepted` = '1' WHERE `unique_id` = '{$voiceid}' LIMIT 1");
        $getvoice = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `voices` WHERE `unique_id` = '{$voiceid}'"));
        $voicesender = $getvoice['sender'];
        Bot('deletemessage', [
            'chat_id' => $chatid,
            'message_id' => $messageid,
        ]);
        $voicesender = intval($getvoice['sender']);
        $db->query("UPDATE `user` SET `sendvoice` = '0' WHERE `user`.`id` = $voicesender;");
        $senderinfo = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `user` WHERE `id` = '{$voicesender}' LIMIT 1"));
        
        if($senderinfo['voicemode'] == 'waittomakepub'){
            SendMessage($voicesender, '✅ درخواست عمومی کردن ویس شما توسط مدیریت تایید شد.');
        }else{
            SendMessage($voicesender, 'ویس شما توسط مدیریت تایید شد. ✅');
        }
        $db->query("UPDATE `user` SET `sendvoice` = '0' WHERE `user`.`id` = $voicesender;");
    }elseif(strpos($data, 'reject-') !== false){
        $voiceid = str_replace('reject-', '', $data);
        bot('answercallbackquery', [
            'callback_query_id' => $update->callback_query->id,
            'text' => "ویس لغو شد. ❌",
            'show_alert' => false
        ]);
        Bot('deletemessage', [
            'chat_id' => $chatid,
            'message_id' => $messageid,
        ]);
        $getvoice = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `voices` WHERE `unique_id` = '{$voiceid}'"));
        
        $voicesender = intval($getvoice['sender']);
        
        $senderinfo = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `user` WHERE `id` = '{$voicesender}' LIMIT 1"));
        
        if($senderinfo['voicemode'] == 'waittomakepub'){
            $db->query("UPDATE `voices` SET `accepted` = '0', `mode` = 'private' WHERE `unique_id` = '$voiceid';");
            $db->query("UPDATE `user` SET `sendvoice` = '0' WHERE `user`.`id` = $voicesender;");
            SendMessage($getvoice['sender'], 'درخواست عمومی کردن ویس شما توسط مدیریت رد شد. ویس برروی حالت خصوصی مجددا در دسترس شما قرار گرفت. ❌');
            exit();
        }
        $db->query("DELETE FROM `voices` WHERE `unique_id` = '{$voiceid}' LIMIT 1");
        $db->query("UPDATE `user` SET `sendvoice` = '0' WHERE `user`.`id` = $voicesender;");
        SendMessage($getvoice['sender'], 'ویس شما توسط مدیریت رد شد. ❌');
        
    }
}