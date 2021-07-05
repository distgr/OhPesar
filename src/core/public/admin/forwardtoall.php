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
    $to_edit = $message_id+2;
    $db->query("UPDATE `user` SET `step` = 'none' WHERE `id` = '{$from_id}' LIMIT 1");
    $query = mysqli_query($db, "SELECT * FROM `user`");
    $memberscount = mysqli_num_rows($query);
    
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>"درحال فوروارد برای تمامی $memberscount ممبر... لطفا برای بهبود سرعت تا تکمیل فرایند فوروارد کاری انجام ندهید!",
        'reply_markup'=>json_encode(['keyboard'=>$adminpanel ,'resize_keyboard'=>true])
    ]);
    SendMessage($chat_id, "درحال انجام : 0/$memberscount");
    for ($i=0; $i < $memberscount; $i++) { 
    	$u = mysqli_fetch_assoc($query);
    	Forward($u['id'], $from_id, $message_id);
        $ii = $i+1;
        EditMessage($chat_id, $to_edit, "درحال انجام : $ii/$memberscount");
    }
    SendMessage($chat_id, 'پیام مورد نظر برای همه اعضای ربات فوروارد شد. ✅');
}