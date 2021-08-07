<?php

if($text == '✏️ ویرایش ویس' && in_array($from_id, $CONFIG['ADMINS'])){
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>'لطفا ویس مورد نظر را ارسال کنید :',
        'reply_markup'=>json_encode(['keyboard'=>$back ,'resize_keyboard'=>true])
    ]);
    $db->query("UPDATE `user` SET `step` = 'editvoice1' WHERE `id` = '{$from_id}' LIMIT 1");
    mysqli_close($db);
    exit();
}

elseif($update->message->voice && $user['step'] == 'editvoice1'){
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
        'text'=>"شما ویس « $voicename » را انتخاب کردید. لطفا از گزینه های زیر یک مورد را برای ویرایش انتخاب کنید 👇🏻",
        'reply_markup'=>json_encode(['keyboard'=>$editvoicepanel ,'resize_keyboard'=>true])
    ]);
    $db->query("UPDATE `user` SET `step` = 'editvoice2', `voicename` = '{$voiceid}' WHERE `id` = '{$from_id}' LIMIT 1");
    mysqli_close($db);
    exit();
}

elseif($text && $text !== $backbtn && $user['step'] == 'editvoice2'){
    $voiceid = $user['voicename'];
    $choices = [
        '✏️ ویرایش نام ویس',
        '✏️ ویرایش صدای ویس'  
    ];
    if(!in_array($text, $choices)){
        SendMessage($chat_id, 'لطفا فقط از دکمه های پایین یک گزینه را انتخاب کنید.');
        mysqli_close($db);
        exit();
    }
    $voiceinfo = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `voices` WHERE `unique_id` = '{$voiceid}'"));
    $voicename = $voiceinfo['name'];
    if($text == $choices[0]){
        Bot('sendMessage',[
            'chat_id'=>$chat_id,
            'text'=>"لطفا نام جدید را برای ویس « $voicename » ارسال کنید :",
            'reply_markup'=>json_encode(['keyboard'=>$back ,'resize_keyboard'=>true])
        ]);
        $db->query("UPDATE `user` SET `step` = 'editvoice3', `voiceedit` = 'name' WHERE `id` = '{$from_id}' LIMIT 1");
    }elseif($text == $choices[1]){
        Bot('sendMessage',[
            'chat_id'=>$chat_id,
            'text'=>"لطفا ویس جدید جایگزین را برای ویس « $voicename » ارسال کنید :",
            'reply_markup'=>json_encode(['keyboard'=>$back ,'resize_keyboard'=>true])
        ]);
        $db->query("UPDATE `user` SET `step` = 'editvoice3', `voiceedit` = 'replace' WHERE `id` = '{$from_id}' LIMIT 1");
    }
    mysqli_close($db);
    exit();
}

elseif($user['step'] == 'editvoice3'){
    $voiceid = $user['voicename'];
    $voiceinfo = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `voices` WHERE `unique_id` = '{$voiceid}'"));
    if($update->message->voice && $user['voiceedit'] == 'replace'){
        $vid = Forward($CONFIG['CHANNEL']['DATABASEID'], $chat_id, $message_id);
        $vr = json_decode($vid, true);
        $voicename = $voiceinfo['name'];
        $newurl = 'https://t.me/'.$CONFIG['CHANNEL']['DATABASE'].'/'.strval($vr['result']['message_id']);
        $newmessageid = $vr['result']['message_id'];
        $voiceprimarykey = $voiceinfo['id'];
        $newvoiceuniqueid = $update->message->voice->file_unique_id;
        $db->query("UPDATE `voices` SET `url` = '{$newurl}', `messageid` = '{$newmessageid}', `unique_id` = '{$newvoiceuniqueid}' WHERE `id` = '{$voiceprimarykey}' LIMIT 1");
        Bot('sendMessage',[
            'chat_id'=>$chat_id,
            'text'=>"✅ ویس ارسالی شما، جایگزین ویس « $voicename » شد.",
            'reply_markup'=>json_encode(['keyboard'=>$adminpanel ,'resize_keyboard'=>true])
        ]);
        SendMessage($CONFIG['CHANNEL']['LOGID'], "ویس « $voicename » توسط ادمین $from_id با نام $first_name جایگزین ویس دیگری شد.");
    }elseif($text && $user['voiceedit'] == 'name'){
        $old_name = $voiceinfo['name'];
        $db->query("UPDATE `voices` SET `name` = '{$text}' WHERE `unique_id` = '{$voiceid}' LIMIT 1");
        Bot('sendMessage',[
            'chat_id'=>$chat_id,
            'text'=>"✅ نام ویس « $old_name » به نام « $text » تغییر پیدا کرد.",
            'reply_markup'=>json_encode(['keyboard'=>$adminpanel ,'resize_keyboard'=>true])
        ]);
        SendMessage($CONFIG['CHANNEL']['LOGID'], "نام ویس « $old_name » به نام « $text » توسط ادمین $from_id با نام $first_name تغییر پیدا کرد.");
    }
    $db->query("UPDATE `user` SET `step` = 'none', `voiceedit` = NULL WHERE `id` = '{$from_id}' LIMIT 1");
    mysqli_close($db);
    exit();
}