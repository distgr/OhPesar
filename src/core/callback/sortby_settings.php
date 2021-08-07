<?php

if($callback_query){
    $data = $callback_query->data;
        
    $settings_text = "⚙️ به تنظیمات مرتب سازی ربات اوه پسر خوش آمدید! در این بخش میتوانید تعیین کنید که هنگامی که آیدی ربات را در چت مورد نظر وارد کردید، بر چه اساسی و چه ویس هایی برای شما به نمایش گذاشته شود 👇🏻";

    if($data == 'usersettings'){
        $sortby = [
            'oldest'=>'',
            'newest'=>'',
            'popularest'=>'',
            'private'=>''
        ];
        if($user['sortby'] == 'newest'){ $sortby['newest'] = '✅'; }
        elseif($user['sortby'] == 'popularest'){ $sortby['popularest'] = '✅'; }
        elseif($user['sortby'] == 'private'){ $sortby['private'] = '✅'; }
        else{ $sortby['oldest'] = '✅'; }

        Bot('EditMessageText',[
            'chat_id'=>$chatid,
            'message_id'=> $messageid,
            'text'=>$settings_text,
            'reply_markup'=>json_encode([
                'inline_keyboard'=>[
                    [['text'=>$sortby['newest'].' جدیدترین ویس ها', 'callback_data'=>'setsortby_newest'], ['text'=>$sortby['oldest'].' قدیمیترین ویس ها', 'callback_data'=>'setsortby_oldest']],
                    [['text'=>$sortby['popularest'].' محبوبترین ویس ها', 'callback_data'=>'setsortby_popularest']],
                ],
            ])
        ]);
    }

    if(strpos($data, 'setsortby_') !== false){
        $mode = str_replace('setsortby_', '', $data);
        $userinline = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `user` WHERE `id` = '{$chatid}' LIMIT 1"));
        
        if($userinline['sortby'] == $mode){
            bot('answercallbackquery', [
                'callback_query_id' => $update->callback_query->id,
                'text' => "⚠️ تنظیمات نمایش از قبل بر روی این گزینه تنظیم بود",
                'show_alert' => false
            ]);
            mysqli_close($db);
            exit();
        }
        
        $db->query("UPDATE `user` SET `sortby` = '{$mode}' WHERE `user`.`id` = $chatid;");

        $sortby = [
            'oldest'=>'',
            'newest'=>'',
            'popularest'=>'',
            'private'=>''
        ];
        if($mode == 'newest'){ $sortby['newest'] = '✅'; }
        elseif($mode == 'popularest'){ $sortby['popularest'] = '✅'; }
        elseif($mode == 'private'){ $sortby['private'] = '✅'; }
        else{ $sortby['oldest'] = '✅'; }
        
        bot('answercallbackquery', [
                'callback_query_id' => $update->callback_query->id,
                'text' => "✅ تنظیم نمایش ویس ها بروز شد. ",
                'show_alert' => false
            ]);
        
        Bot('EditMessageText',[
            'chat_id'=>$chatid,
            'message_id'=>$messageid,
            'text'=>$settings_text,
            'reply_markup'=>json_encode([
                'inline_keyboard'=>[
                    [['text'=>$sortby['newest'].' جدیدترین ویس ها', 'callback_data'=>'setsortby_newest'], ['text'=>$sortby['oldest'].' قدیمیترین ویس ها', 'callback_data'=>'setsortby_oldest']],
                    [['text'=>$sortby['popularest'].' محبوبترین ویس ها', 'callback_data'=>'setsortby_popularest']],
                ],
            ])
        ]);
    }
}