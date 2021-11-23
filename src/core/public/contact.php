<?php

if($text == '💬 ارتباط با مدیریت'){
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>'💭 شما هم اکنون در حالت گفتوگو با تیم مدیریت ربات اوه پسر هستید؛ لطفا پیام خود را ارسال کنید.',
        'reply_markup'=>json_encode(['keyboard'=>$back, 'resize_keyboard'=>true])
    ]);
    $db->query("UPDATE `user` SET `step` = 'contact' WHERE `id` = '{$from_id}' LIMIT 1");
}

if($user['step'] == 'contact' && $text !== $backbtn){
    $x = Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>'✅ پیام شما ارسال شد.',
        'reply_markup'=>json_encode(['keyboard'=>$back, 'resize_keyboard'=>true])
    ]);
    $x = json_decode($x, true);
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
    // $db->query("UPDATE `user` SET `step` = 'none' WHERE `id` = '{$from_id}' LIMIT 1");
}


if($update->channel_post->reply_to_message && $update->channel_post->sender_chat->title == 'OhPesarContact'){
    $senderid = str_replace('☑️ ', '', end($update->channel_post->reply_to_message->reply_markup->inline_keyboard)[0]->text);
    if($update->channel_post->voice){
        $finfo = Forward($CONFIG['CHANNEL']['DATABASEID'], $update->channel_post->chat->id, $update->channel_post->message_id);
        $x = SendVoice($senderid, 'https://t.me/'.$CONFIG['CHANNEL']['DATABASE'].'/'.json_decode($finfo)->result->message_id);
        $undo_query = json_decode($x, true)['result']['message_id'].'__'.json_decode($x, true)['result']['chat']['id'];
    }elseif($update->channel_post->text){
        $x = Bot('sendMessage',[ 'chat_id'=>$senderid, 'text'=>$update->channel_post->text]);
        $undo_query = json_decode($x, true)['result']['message_id'].'__'.json_decode($x, true)['result']['chat']['id'];
    }elseif($update->channel_post->document){
        $finfo = Forward($CONFIG['CHANNEL']['DATABASEID'], $update->channel_post->chat->id, $update->channel_post->message_id);
        $x = Bot('SendDocument',['chat_id'=>$senderid,'document'=>'https://t.me/'.$CONFIG['CHANNEL']['DATABASE'].'/'.json_decode($finfo)->result->message_id]);
        $undo_query = json_decode($x, true)['result']['message_id'].'__'.json_decode($x, true)['result']['chat']['id'];
    }elseif($update->channel_post->sticker){
        $finfo = Forward($CONFIG['CHANNEL']['DATABASEID'], $update->channel_post->chat->id, $update->channel_post->message_id);
        $x = Bot('sendSticker',['chat_id'=>$senderid,'sticker'=>'https://t.me/'.$CONFIG['CHANNEL']['DATABASE'].'/'.json_decode($finfo)->result->message_id]);
        $undo_query = json_decode($x, true)['result']['message_id'].'__'.json_decode($x, true)['result']['chat']['id'];
    }elseif($update->channel_post->video){
        $finfo = Forward($CONFIG['CHANNEL']['DATABASEID'], $update->channel_post->chat->id, $update->channel_post->message_id);
        $x = Bot('sendvideo',['chat_id'=>$senderid,'video'=>'https://t.me/'.$CONFIG['CHANNEL']['DATABASE'].'/'.json_decode($finfo)->result->message_id, 'caption'=>json_decode($finfo, true)['result']['caption']]);
        $undo_query = json_decode($x, true)['result']['message_id'].'__'.json_decode($x, true)['result']['chat']['id'];
    }elseif($update->channel_post->photo){
        $finfo = Forward($CONFIG['CHANNEL']['DATABASEID'], $update->channel_post->chat->id, $update->channel_post->message_id);
        $x = Bot('SendPhoto',['chat_id'=>$senderid,'photo'=>'https://t.me/'.$CONFIG['CHANNEL']['DATABASE'].'/'.json_decode($finfo)->result->message_id, 'caption'=>json_decode($finfo, true)['result']['caption']]);
        $undo_query = json_decode($x, true)['result']['message_id'].'__'.json_decode($x, true)['result']['chat']['id'];
    }else{
        mysqli_close($db);
        exit();
    }
    Bot('editMessageReplyMarkup',[
        'chat_id'=>$update->channel_post->chat->id,
        'message_id'=> $update->channel_post->message_id,
        'reply_markup'=>json_encode([
        'inline_keyboard'=>[
        [['text'=>'❌ پاک کردن پیام','callback_data'=>'unsend_'.$undo_query]],
        // [['text'=>'✔️ پیام ارسال شد.','callback_data'=>'nothing']],
        ],
        ])
    ]);
}