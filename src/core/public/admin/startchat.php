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
    $user_data = 'nonex';
    $getted_start = false;
    if(!is_numeric($text)){
        SendMessage($chat_id, 'درحال دریافت اطلاعات کاربر از طریق یوزرنیم... این عمل ممکن است کمی طول بکشد');
        $user_data = file_get_contents($CONFIG['SERVERURL'].'userapi.php?id='.$text);
        $getted_start = true;
        if(strpos(strtolower($user_data), strtolower($text)) !== false){
            $user_data_decode = json_decode($user_data, true);
            $text = $user_data_decode['result']['userid'];
        }
    }
    $chatuser_info = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `user` WHERE `id` = '{$text}' LIMIT 1"));
    if(!$chatuser_info or !is_numeric($text)){
        SendMessage($chat_id, 'چنین کاربری در دیتابیس «اوه پسر» یافت نشد !');
        mysqli_close($db);
        exit();
    }
    if($user_data == 'nonex'){
        Bot('sendMessage',[
            'chat_id'=>$chat_id,
            'text'=>"درحال دریافت اطلاعات این شخص، لطفا کمی صبر کنید...",
            'reply_markup'=>json_encode(['keyboard'=>$adminpanel ,'resize_keyboard'=>true])
        ]);
    }
    
    $ContactMsgBtn = [];
    if($user_data == 'nonex') $user_data = file_get_contents($CONFIG['SERVERURL'].'userapi.php?id='.$text);
    if(strpos($user_data, $text) !== false){
        if(!$getted_start){
            $user_data_decode = json_decode($user_data, true);
        }
        $ContactMsgBtn[] = [['text'=>'👤 '.$user_data_decode['result']['name'], 'callback_data'=>'nothing']];
        if($user_data_decode['result']['username'] !== '@'){
            $ContactMsgBtn[] = [['text'=>'🆔 '.$user_data_decode['result']['username'], 'url'=>'https://t.me/'.str_replace('@', '', $user_data_decode['result']['username'])]];
        }
    }else{
        $ContactMsgBtn[] = [['text'=>'👤 کاربر ناشناس', 'callback_data'=>'nothing']];
    }
    $ContactMsgBtn[] = [['text'=>'☑️ '.$text, 'callback_data'=>'nothing']];
    
    Bot('sendMessage',[
        'chat_id'=>$CONFIG['CHANNEL']['CONTACTID'],
        'text'=>'❗️ این گفتوگو در پنل مدیریت توسط '.$first_name.' ایجاد شده است. درصورت ریپلای روی این پیام میتوانید با کاربر مورد نظر چت کنید.',
        'reply_markup'=>json_encode([
            'inline_keyboard'=>$ContactMsgBtn
        ])
    ]);
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>"گفتوگو با این کاربر در کانال OhPesar Contact آغاز شد.",
        'reply_markup'=>json_encode(['keyboard'=>$adminpanel ,'resize_keyboard'=>true])
    ]);

    $db->query("UPDATE `user` SET `step` = 'none' WHERE `id` = '{$from_id}' LIMIT 1");
    mysqli_close($db);
    exit();
}