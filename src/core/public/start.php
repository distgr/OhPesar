<?php

if(strtolower($text) == '/start' or $text == $backbtn or $text == '/start startforuse'){
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>'اوه دوست عزیز! باورم نمیشه! خیلی خوش اومدی😦
ربات اوه پسر یه ربات طنزه که بهت این امکان رو میده که ویس های طنز رو در مکان های طنز ارسال کنی 😎

الان هم میتونی از دکمه های زیر استفاده کنی 👇🏻',
        'reply_markup'=>json_encode(['keyboard'=>$home ,'resize_keyboard'=>true
        ])
    ]);
    $db->query("UPDATE `user` SET `step` = 'none' WHERE `id` = '{$from_id}' LIMIT 1");
    mysqli_close($db);
    exit();
}