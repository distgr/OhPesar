<?php

if($text == '📍 پنل مدیریت' && in_array($from_id, $CONFIG['ADMINS'])){
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>'⚙️ به پنل مدیریت ربات «اوه پسر» خوش آمدید.',
        'reply_markup'=>json_encode(['keyboard'=>$adminpanel ,'resize_keyboard'=>true])
    ]);
}