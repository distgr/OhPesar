<?php

if($update->message->voice){
    $vid = $update->message->voice->file_unique_id;
    $found = true;
    $voiceinfo = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `voices` WHERE `unique_id` = '{$vid}' LIMIT 1"));
    if(!$voiceinfo) $found = false;
    if($voiceinfo['mode'] == 'private' && intval($voiceinfo['sender']) !== intval($chat_id)){
        SendMessage($chat_id, '👀 اوه پسر متاسفم! این یه ویس شخصیه که توسط یکی از کاربرای ربات ثبت شده و تو نمیتونی ازش استفاده کنی');
        exit();
    }
    if(!$voiceinfo['accepted'] && $voiceinfo['mode'] == 'public') $found = false;
    if(!$found && $user['step'] == 'none'){
        SendMessage($chat_id, '🧐 همچین ویسی داخل ربات ثبت نشده!');
        exit();
    }
    $voiceload_btns = [
        [['text'=>"🎤 ارسال ویس برای دیگران", 'switch_inline_query'=>$voiceinfo['name']]]
    ];
    if(intval($voiceinfo['sender']) == intval($chat_id)){
        $voiceload_btns[] = [['text'=>"⚙️ تنظیمات این ویس", 'callback_data'=>'voicesettings__'.$vid.'__00']];
    }
    $addtexts = '';
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
    $voiceid = explode('__', $update->chosen_inline_result->result_id)[0];
    $db->query("UPDATE `voices` SET `usecount` = `usecount` + 1 WHERE `unique_id` = '{$voiceid}' LIMIT 1");
}