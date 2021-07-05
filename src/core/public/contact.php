<?php

if($text == '💬 ارتباط با مدیریت'){
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>'💭 به بخش ارسال پیام به مدیریت ربات اوه پسر خوش آمدید. لطفا پیام خود را ارسال کنید تا پیامتان به دست مدیریت و ادمین های ربات ارسال شود.',
        'reply_markup'=>json_encode(['keyboard'=>$back, 'resize_keyboard'=>true])
    ]);
    $db->query("UPDATE `user` SET `step` = 'contact' WHERE `id` = '{$from_id}' LIMIT 1");
}

if($user['step'] == 'contact' && $text !== $backbtn){
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>'✅ پیام شما با موفقیت برای تیم مدیریت ربات اوه پسر ارسال شد.',
        'reply_markup'=>json_encode(['keyboard'=>$home, 'resize_keyboard'=>true])
    ]);
    $ContactMsgBtn = [];
    $ContactMsgBtn[] = [['text'=>'👤 '.$first_name, 'callback_data'=>'nothing']];
    if($username){
        $ContactMsgBtn[] = [['text'=>'🆔 @'.$username, 'url'=>'https://t.me/'.$username]];
    }
    $ContactMsgBtn[] = [['text'=>'☑️ '.$from_id, 'callback_data'=>'nothing']];
    
    Bot('sendMessage',[
        'chat_id'=>$CONFIG['CHANNEL']['CONTACTID'],
        'text'=>$text,
        'reply_markup'=>json_encode([
            'inline_keyboard'=>$ContactMsgBtn
        ])
    ]);
    $db->query("UPDATE `user` SET `step` = 'none' WHERE `id` = '{$from_id}' LIMIT 1");
}


if($update->channel_post->reply_to_message && $update->channel_post->sender_chat->title == 'OhPesarContact'){
    $senderid = str_replace('☑️ ', '', end($update->channel_post->reply_to_message->reply_markup->inline_keyboard)[0]->text);
    if($update->channel_post->voice){
        $finfo = Forward($CONFIG['CHANNEL']['DATABASEID'], $update->channel_post->chat->id, $update->channel_post->message_id);
        SendVoice($senderid, 'https://t.me/'.$CONFIG['CHANNEL']['DATABASE'].'/'.json_decode($finfo)->result->message_id);
    }elseif($update->channel_post->text){
        SendMessage($senderid, $update->channel_post->text);
    }else{
        exit();
    }
    Bot('editMessageReplyMarkup',[
        'chat_id'=>$update->channel_post->chat->id,
        'message_id'=> $update->channel_post->message_id,
        'reply_markup'=>json_encode([
        'inline_keyboard'=>[
        [['text'=>'✔️ پیام ارسال شد.','callback_data'=>'nothing']],
        ],
        ])
    ]);
    
}