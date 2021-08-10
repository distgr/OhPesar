<?php

if($text == '💬 شروع گفتوگو با کاربر' && in_array($from_id, $CONFIG['ADMINS'])){
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>'لطفا آیدی عددی کاربر را ارسال کنید :',
        'reply_markup'=>json_encode(['keyboard'=>$back ,'resize_keyboard'=>true])
    ]);
    $db->query("UPDATE `user` SET `step` = 'startchat1' WHERE `id` = '{$from_id}' LIMIT 1");
    mysqli_close($db);
    exit();
}

elseif($text && $user['step'] == 'startchat1'){
    $chatuser_info = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `user` WHERE `id` = '{$text}' LIMIT 1"));
    if(!$chatuser_info){
        SendMessage($chat_id, 'چنین کاربری در دیتابیس «اوه پسر» یافت نشد !');
        mysqli_close($db);
        exit();
    }
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>"گفتوگو با این کاربر در کانال OhPesar Contact آغاز شد.",
        'reply_markup'=>json_encode(['keyboard'=>$adminpanel ,'resize_keyboard'=>true])
    ]);
    
    $ContactMsgBtn = [];
    $ContactMsgBtn[] = [['text'=>'👤 کاربر ناشناس', 'callback_data'=>'nothing']];
    $ContactMsgBtn[] = [['text'=>'☑️ '.$text, 'callback_data'=>'nothing']];
    
    Bot('sendMessage',[
        'chat_id'=>$CONFIG['CHANNEL']['CONTACTID'],
        'text'=>'❗️ این گفتوگو در پنل مدیریت توسط '.$first_name.' ایجاد شده است. درصورت ریپلای روی این پیام میتوانید با کاربر مورد نظر چت کنید.',
        'reply_markup'=>json_encode([
            'inline_keyboard'=>$ContactMsgBtn
        ])
    ]);

    $db->query("UPDATE `user` SET `step` = 'none' WHERE `id` = '{$from_id}' LIMIT 1");
    mysqli_close($db);
    exit();
}