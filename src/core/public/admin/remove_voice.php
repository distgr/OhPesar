<?php

if($text == '🗑 حذف ویس' && in_array($from_id, $CONFIG['ADMINS'])){
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>'لطفا ویس مورد نظر از «اوه پسر» را ارسال یا فوروارد کنید تا حذف شود :',
        'reply_markup'=>json_encode(['keyboard'=>$back ,'resize_keyboard'=>true])
    ]);
    $db->query("UPDATE `user` SET `step` = 'deletevoice1' WHERE `id` = '{$from_id}' LIMIT 1");
}

elseif($update->message->voice && $user['step'] == 'deletevoice1'){
    $voiceid = $update->message->voice->file_unique_id;
    $voiceinfo = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `voices` WHERE `unique_id` = '{$voiceid}'"));
    if(!$voiceinfo){
        SendMessage($chat_id, 'چنین ویسی در دیتابیس «اوه پسر» یافت نشد !');
        mysqli_close($db);
        exit();
    }
    $voicename = $voiceinfo['name'];
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>"آیا مطمئن هستید که میخواهید ویس « $voicename » را حذف کنید؟",
        'reply_markup'=>json_encode(['keyboard'=>$yesnopanel ,'resize_keyboard'=>true])
    ]);
    $db->query("UPDATE `user` SET `step` = 'deletevoice2', `voicename` = '{$voiceid}' WHERE `id` = '{$from_id}' LIMIT 1");
}

elseif($text && $text !== $backbtn && $user['step'] == 'deletevoice2'){
    $choices = ["✅ بله", "❌ خیر"];
    if(!in_array($text, $choices)){
        SendMessage($chat_id, 'لطفا فقط از دکمه های پایین یک گزینه را انتخاب کنید.');
        mysqli_close($db);
        exit();
    }
    if($text == $choices[1]){
        Bot('sendMessage',[
            'chat_id'=>$chat_id,
            'text'=>"عملیات حذف ویس لغو شد.",
            'reply_markup'=>json_encode(['keyboard'=>$adminpanel ,'resize_keyboard'=>true])
        ]);
        $db->query("UPDATE `user` SET `step` = 'none' WHERE `id` = '{$from_id}' LIMIT 1");
        mysqli_close($db);
        exit();
    }
    $voiceid = $user['voicename'];
    $voiceinfo = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `voices` WHERE `unique_id` = '{$voiceid}'"));
    $voicename = $voiceinfo['name'];
    $db->query("DELETE FROM `voices` WHERE `unique_id` = '{$voiceid}' LIMIT 1");
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>"ویس « $voicename » با موفقیت حذف شد.",
        'reply_markup'=>json_encode(['keyboard'=>$adminpanel ,'resize_keyboard'=>true])
    ]);
    SendMessage($CONFIG['CHANNEL']['LOGID'], "ویس « $voicename » توسط ادمین $from_id با نام $first_name حذف شد.");
    $db->query("UPDATE `user` SET `step` = 'none' , `voicename` = 'none' WHERE `id` = '{$from_id}' LIMIT 1");
}