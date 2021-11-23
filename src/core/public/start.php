<?php

if($text == '/start changevisib'){
    $theuser = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `user` WHERE `id` = '{$from_id}' LIMIT 1"));
    if($theuser['sortby'] == 'newest'){
        $to_change = 'popularest';
    }elseif($theuser['sortby'] == 'popularest'){
        $to_change = 'oldest';
    }else{
        $to_change = 'newest';
    }
    $datafrommsg = Bot('SendMessage',[
        'chat_id'=>$chat_id,
        'text'=>'انجام شد!',
        'reply_markup'=>json_encode([
            'inline_keyboard'=>[
                [['text'=>'وضعیت نمایش ویس ها برای شما تغییر کرد.', 'switch_inline_query'=>'']],
            ],
        ])
    ]);
    $datafrommsg = json_decode($datafrommsg, true)['result'];
    $db->query("UPDATE `user` SET `sortby` = '{$to_change}' WHERE `user`.`id` = $chat_id;");
    Bot('deletemessage', [
        'chat_id' => $datafrommsg['chat']['id'],
        'message_id' => $datafrommsg['message_id'],
    ]);
    Bot('deletemessage', [
        'chat_id' => $datafrommsg['chat']['id'],
        'message_id' => $datafrommsg['message_id']-1,
    ]);
    mysqli_close($db);
    exit();
}

if(strtolower($text) == '/start' or $text == $backbtn or $text == '/start startforuse'){
    Bot('sendvideo',[
        'chat_id'=>$chat_id,
        'video'=>'https://t.me/OhPesar/42',
        'caption'=>'اوه دوست عزیز! باورم نمیشه! خیلی خوش اومدی😦
ربات اوه پسر یه ربات طنزه که بهت این امکان رو میده که ویس های طنز رو در مکان های طنز ارسال کنی 😎

الان هم میتونی از دکمه های زیر استفاده کنی 👇🏻',
        'reply_markup'=>json_encode(['keyboard'=>$home ,'resize_keyboard'=>true
        ])
    ]);
    $db->query("UPDATE `user` SET `step` = 'none' WHERE `id` = '{$from_id}' LIMIT 1");
    mysqli_close($db);
    exit();
}