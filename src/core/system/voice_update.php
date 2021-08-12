<?php

if($update->message->voice){
    $vid = $update->message->voice->file_unique_id;
    $found = true;
    $voiceinfo = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `voices` WHERE `unique_id` = '{$vid}' LIMIT 1"));
    if(!$voiceinfo) $found = false;
    if($voiceinfo['mode'] == 'private' && intval($voiceinfo['sender']) !== intval($chat_id)){
        SendMessage($chat_id, '👀 اوه پسر متاسفم! این یه ویس شخصیه که توسط یکی از کاربرای ربات ثبت شده و تو نمیتونی ازش استفاده کنی');
        mysqli_close($db);
        exit();
    }
    if(!$voiceinfo['accepted'] && $voiceinfo['mode'] == 'public') $found = false;
    if(!$found && $user['step'] == 'none'){
        SendMessage($chat_id, '🧐 همچین ویسی داخل ربات ثبت نشده!');
        mysqli_close($db);
        exit();
    }

    $switchquery = ['byname'=>$voiceinfo['name'], 'byid'=>'-id '.$voiceinfo['id']][$user['sendvoiceaction']];

    $voiceload_btns = [
        [['text'=>"🎤 ارسال ویس برای دیگران", 'switch_inline_query'=>$switchquery]]
    ];
    if(intval($voiceinfo['sender']) == intval($chat_id)){
        $voiceload_btns[] = [['text'=>"⚙️ تنظیمات این ویس", 'callback_data'=>'voicesettings___'.$vid.'___00']];
    }
    $addtexts = '';

    $addtexts .= "🆔 آیدی ویس : ".$voiceinfo['id']."\n";

    if($user['badvoices'] == 0){
        if( IsBadWord($voiceinfo['name']) ) $addtexts .= '⚠️ توجه : ربات این ویس را جز دسته ویس های نامناسب تشخیص داده و حالت نمایش ویس های نامناسب شما خاموش است، در نتیجه این ویس برای شما در سرچ نمایش داده نمیشود!';
    }
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>'🎤 نام ویس ارسالی : '.$voiceinfo['name']."\n$addtexts",
        'reply_markup'=>json_encode([
        'inline_keyboard'=>$voiceload_btns,
        ])
    ]);
}

elseif($update->chosen_inline_result){
    $voiceid = explode('___', $update->chosen_inline_result->result_id)[0];
    $user = $update->chosen_inline_result->from->id;
    $query = $update->chosen_inline_result->query;
    $db->query("UPDATE `voices` SET `usecount` = `usecount` + 1 WHERE `unique_id` = '{$voiceid}' LIMIT 1");
    $db->query("UPDATE `user` SET `latestvoice` = '{$voiceid}' WHERE `user`.`id` = '{$user}' LIMIT 1");
    $dailylog['voice']++;
    file_put_contents('daily_log.json', json_encode($dailylog));
    if((strpos($query, '+favorite') !== false) or (strpos($query, '+fav') !== false)){
        $voiceinfo = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `voices` WHERE `unique_id` = '{$voiceid}' LIMIT 1"));
        $voicename = $voiceinfo['name'];

        $voiceinfav = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `favorites` WHERE `voiceid` = '{$voiceid}' and `userid` = '{$user}' LIMIT 1"));

        if (!$voiceinfav) {
            $db->query("INSERT INTO `favorites` (`voiceid`, `userid`) VALUES ('{$voiceid}', '{$user}')");
            SendMessage($user, "⭐️ ویس « $voicename » به علاقه مندی های شما اضافه شد.");
        }else{
            SendMessage($user, "⭐️ ویس « $voicename » در علاقه مندی های شما بود.");
        }
    }
}