<?php

if($callback_query){
    $data = $callback_query->data;
        
    if(strpos($data, 'acceptreportseen-') !== false){
        $meta = explode('-', str_replace('acceptreportseen-', '', $data));
        bot('answercallbackquery', [
            'callback_query_id' => $update->callback_query->id,
            'text' => "گزارش دیده شد. ✅",
            'show_alert' => false
        ]);
        Bot('sendMessage',[
            'chat_id'=>$meta[1],
            'text'=>'✔️ گزارش شما توسط تیم مدیریت اوه پسر دیده شد. لطفا تا بررسی و اعلام نتیجه منتظر بمانید.',
        ]);
        Bot('editMessageReplyMarkup',[
            'chat_id'=>$chatid,
            'message_id'=> $messageid,
            'reply_markup'=>json_encode([
                'inline_keyboard'=>[
                    [['text'=>"✔️ گزارش دیده شد",'callback_data'=>'reportseenby-'.$callback_query->from->first_name]],
                    [['text'=>"✅",'callback_data'=>'acceptreport-'.$meta[0].'-'.$meta[1]], ['text'=>"❌",'callback_data'=>'rejectreport-'.$meta[0].'-'.$meta[1]]],
                ],
            ])
        ]);
    }elseif(strpos($data, 'reportseenby-') !== false){
        $name = str_replace('reportseenby-', '', $data);
        bot('answercallbackquery', [
            'callback_query_id' => $update->callback_query->id,
            'text' => "این گزارش توسط ($name) دیده شده. ✅",
            'show_alert' => true
        ]);
    }elseif(strpos($data, 'acceptreport-') !== false){

        $meta = explode('-', str_replace('acceptreport-', '', $data));
        $systemid = $meta[0]; $meta_chat_id = $meta[1];
        $voiceinfo = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `voices` WHERE `unique_id` = '{$systemid}'"));
        $db->query("UPDATE `voices` SET `banned` = '1' WHERE `unique_id` = '{$systemid}' LIMIT 1");
        $vname = $voiceinfo['name'];
        Bot('deletemessage', [
            'chat_id' => $chatid,
            'message_id' => $messageid,
        ]);
        Bot('sendMessage',[
            'chat_id'=>$meta_chat_id,
            'text'=>"☑️ گزارش شما با موفقیت تایید شد و ویس ($vname) با موفقیت از دسترس خارج و برای همیشه از ربات ban شد.",
        ]);
        Bot('sendMessage',[
            'chat_id'=>$voiceinfo['sender'],
            'text'=>"⚠️ کاربر گرامی، متاسفانه یکی از ویس های شما با عنوان ($vname) به دلیل گزارش های ارسال شده توسط کاربران از دسترس برای همیشه خارج شد. لطفا سعی کنید قوانین ربات را به درستی رعایت کنید.",
        ]);
    }elseif(strpos($data, 'rejectreport-') !== false){
        $meta = explode('-', str_replace('rejectreport-', '', $data));
        $systemid = $meta[0]; $meta_chat_id = $meta[1];
        $voiceinfo = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `voices` WHERE `unique_id` = '{$systemid}'"));
        $vname = $voiceinfo['name'];
        Bot('deletemessage', [
            'chat_id' => $chatid,
            'message_id' => $messageid,
        ]);
        Bot('sendMessage',[
            'chat_id'=>$meta_chat_id,
            'text'=>"❌ متاسفانه گزارش شما برای ویس ($vname) رد شد.",
        ]);
    }
}