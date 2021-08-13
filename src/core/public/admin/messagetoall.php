<?php

if($text == '💬 پیام همگانی' && in_array($chat_id, $CONFIG['ADMINS'])){
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>"لطفا پیام مورد نظر خود را ارسال کنید تا برای همه اعضا ارسال شود : (لطفا در ارسال پیام دقت کنید، این بخش فاقد تاییدیه میباشد و به محض ارسال پیام برای همه ارسال میشود)",
        'reply_markup'=>json_encode(['keyboard'=>$back ,'resize_keyboard'=>true])
    ]);
    $db->query("UPDATE `user` SET `step` = 'msg2all' WHERE `id` = '{$from_id}' LIMIT 1");
}

elseif($user['step'] == 'msg2all' && ($text !== $backbtn or strtolower($text) !== '/start')){
    $db->query("UPDATE `user` SET `step` = 'none' WHERE `id` = '{$from_id}' LIMIT 1");
    $query = mysqli_query($db, "SELECT * FROM `user`");
    $memberscount = mysqli_num_rows($query);
    $toleft = round($memberscount/50);
    
    $datas = json_encode([
        'chat_id'=>'[*USER*]',
        'text'=>$text,
        'reply_markup'=>json_encode(['resize_keyboard'=>true])
    ]);

    file_get_contents($CONFIG['SERVERURL'].'sender.php?q=add&type=sendMessage&data='.$datas);
    
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>"✅ پیام همگانی در صف ارسال قرار گرفت. ارسال حدودا پس از 1 دقیقه شروع میشود و تقریبا $toleft دقیقه ارسال پیام به تمامی کاربران طول خواهد کشید. اطلاعات بیشتر را میتوانید از دکمه (وضعیت ارسال) بررسی کنید.",
        'reply_markup'=>json_encode(['keyboard'=>$adminpanel ,'resize_keyboard'=>true])
    ]);

}