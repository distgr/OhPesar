<?php

if($text == '💬 فوروارد همگانی' && in_array($chat_id, $CONFIG['ADMINS'])){
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>"لطفا پیام مورد نظر خود را فوروارد کنید تا برای همه اعضا فوروارد شود : (لطفا در ارسال پیام دقت کنید، این بخش فاقد تاییدیه میباشد و به محض ارسال پیام برای همه ارسال میشود)",
        'reply_markup'=>json_encode(['keyboard'=>$back ,'resize_keyboard'=>true])
    ]);
    $db->query("UPDATE `user` SET `step` = 'forward2all' WHERE `id` = '{$from_id}' LIMIT 1");
}

elseif($user['step'] == 'forward2all' && ($text !== $backbtn or strtolower($text) !== '/start')){
    $db->query("UPDATE `user` SET `step` = 'none' WHERE `id` = '{$from_id}' LIMIT 1");
    $query = mysqli_query($db, "SELECT * FROM `user`");
    $memberscount = mysqli_num_rows($query);
    $toleft = round($memberscount/50);
    
    $datas = json_encode(['chat_id'=>'[*USER*]','from_chat_id'=>$from_id,'message_id'=>$message_id]);

    file_get_contents($CONFIG['SERVERURL'].'sender.php?q=add&type=ForwardMessage&data='.$datas);

    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>"✅ فوروارد همگانی در صف ارسال قرار گرفت. ارسال حدودا پس از 1 دقیقه شروع میشود و تقریبا $toleft دقیقه فوروارد پیام به تمامی کاربران طول خواهد کشید. اطلاعات بیشتر را میتوانید از دکمه (وضعیت ارسال) بررسی کنید.",
        'reply_markup'=>json_encode(['keyboard'=>$adminpanel ,'resize_keyboard'=>true])
    ]);
}