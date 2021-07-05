<?php
if($callback_query){
    $data = $callback_query->data;

    if((strpos($data, 'voicesettings__') !== false)){
            
        $explode = explode('__', str_replace('voicesettings__', '', $data));
        $voice_unique_id = $explode[0];
        $page_num = $explode[1];
        $voiceinfo = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `voices` WHERE `unique_id` = '{$voice_unique_id}'"));
        $voicename = $voiceinfo['name'];

        if($voiceinfo['mode'] == 'public'){ $changemode_text = "🔐 شخصی کردن ویس"; }else{ $changemode_text = "🔓 عمومی کردن ویس"; }

        $voicesettings_btn = [
            [['text'=>"💬 اطلاعات ویس", 'callback_data'=>'aboutvoice__'.$voice_unique_id]],
            [['text'=>"🗑 حذف ویس", 'callback_data'=>'removebyuser__'.$voice_unique_id]],
            [['text'=>$changemode_text, 'callback_data'=>'changemode__'.$voice_unique_id.'__'.$page_num]],
        ];

        if($page_num == '0'){
            $voicesettings_btn[] = [['text'=>"🎤 ارسال ویس برای دیگران", 'switch_inline_query'=>$voicename]];
        }else{
            $voicesettings_btn[] = [['text'=>"🔙 بازگشت", 'callback_data'=>'myvoicespage_'.$page_num]];
        }

        Bot('EditMessageText',[
            'chat_id'=>$chatid,
            'message_id'=> $messageid,
            'text'=>"به صفحه تنظیمات ویس « $voicename » خوش آمدید. لطفا از دکمه های زیر، یک مورد را انتخاب کنید 👇🏻",
            'reply_markup'=>json_encode([
                'inline_keyboard'=>$voicesettings_btn,
            ])
        ]);
    }

    // -------------

    if(strpos($data, 'aboutvoice__') !== false){
        $voice_unique_id = str_replace('aboutvoice__', '', $data);
        $voiceinfo = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `voices` WHERE `unique_id` = '{$voice_unique_id}'"));
        $voicename = $voiceinfo['name'];
        $voiceusecount = $voiceinfo['usecount'];
        bot('answercallbackquery', [
            'callback_query_id' => $update->callback_query->id,
            'text' => "🔸 نام ویس : $voicename
    🔹 تعداد استفاده ویس : $voiceusecount بار",
            'show_alert' => true
        ]);
    }

    // -------------

    if(strpos($data, 'changemode__') !== false){
        $explode = explode('__', str_replace('changemode__', '', $data));
        $voice_unique_id = $explode[0];
        $pagenum = $explode[1];
        $voiceinfo = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `voices` WHERE `unique_id` = '{$voice_unique_id}'"));
        $voicename = $voiceinfo['name'];
        if($voiceinfo['mode'] == 'public'){
            // Make private
            $db->query("UPDATE `voices` SET `mode` = 'private' WHERE `unique_id` = '$voice_unique_id';");
            Bot('EditMessageText',[
                'chat_id'=>$chatid,
                'message_id'=> $messageid,
                'text'=>"ویس « $voicename » به حالت خصوصی تغییر پیدا کرد. درصورتی که میخواهید مجدد این ویس را به حالت عمومی تغییر دهید، بدون نیاز به تایید مجدد میتوانید این کار را انجام دهید.",
                'reply_markup'=>json_encode([
                    'inline_keyboard'=>[
                        [['text'=>"🔙 بازگشت", 'callback_data'=>'myvoicespage_'.$pagenum]],
                    ],
                ])
            ]);
        }else{
            if($voiceinfo['accepted'] == 1){
                $db->query("UPDATE `voices` SET `mode` = 'public' WHERE `unique_id` = '$voice_unique_id';");
                Bot('EditMessageText',[
                    'chat_id'=>$chatid,
                    'message_id'=> $messageid,
                    'text'=>"ویس « $voicename » به حالت عمومی تغییر پیدا کرد. (توجه: این ویس یکبار توسط مدیریت تایید شده و اکنون دیگر نیازی به تایید مجدد نیست)",
                    'reply_markup'=>json_encode([
                        'inline_keyboard'=>[
                            [['text'=>"🔙 بازگشت", 'callback_data'=>'myvoicespage_'.$pagenum]],
                        ],
                    ])
                ]);
            }else{
                $first_name = $message->from->first_name;
                $last_name = $message->from->last_name;
                $username = $update->callback_query->from->username;
                $senderusername = '';
                if(isset($cusername)){
                    $senderusername = '🆔 آیدی ارسال کننده : @'.$cusername;
                }
                SendVoice($CONFIG['CHANNEL']['VOICEACCEPT'],
                'https://t.me/'.$CONFIG['CHANNEL']['DATABASE'].'/'.strval($voiceinfo['messageid']), 
                json_encode([
                    'inline_keyboard'=>[
                    [['text'=>"✅",'callback_data'=>'accept-'.$voice_unique_id], ['text'=>"❌",'callback_data'=>'reject-'.$voice_unique_id]],
                    ],
                ]),
                "🎤 $voicename
            
    👤 ارسال کننده : $firstname
    💬 آیدی عددی ارسال کننده : $fromid
    $senderusername"
                );
                $usersendvoice = '1';
                $db->query("UPDATE `voices` SET `accepted` = '0', `mode` = 'public' WHERE `unique_id` = '$voice_unique_id';");
                $db->query("UPDATE `user` SET `step` = 'none', `voicename` = NULL, `voicemode` = 'waittomakepub', `sendvoice` = '1' WHERE `user`.`id` = '{$chatid}' LIMIT 1");
                Bot('EditMessageText',[
                    'chat_id'=>$chatid,
                    'message_id'=> $messageid,
                    'text'=>"🕔 این ویس تا به حال توسط مدیریت تایید نشده است! ویس شما برای مدیریت ارسال شد، لطفا کمی صبر کنید تا ویس شما تایید شود.",
                    'reply_markup'=>json_encode([
                        'inline_keyboard'=>[
                            [['text'=>"🔙 بازگشت", 'callback_data'=>'myvoicespage_'.$pagenum]],
                        ],
                    ])
                ]);
            }
        }
    }

    // ----

    if(strpos($data, 'removebyuser__') !== false){
        $voice_unique_id = str_replace('removebyuser__', '', $data);
        $voiceinfo = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `voices` WHERE `unique_id` = '{$voice_unique_id}'"));
        $voicename = $voiceinfo['name'];
        Bot('EditMessageText',[
            'chat_id'=>$chatid,
            'message_id'=> $messageid,
            'text'=>"❕ آیا مطمئن هستید که میخواهید ویس « $voicename » را حذف کنید ؟",
            'reply_markup'=>json_encode([
                'inline_keyboard'=>[
                    [['text'=>"✅ بله حذف کن", 'callback_data'=>'yesdeletebyuser__'.$voice_unique_id], ['text'=>"❌ نه حذف نکن", 'callback_data'=>'nodeletebyuser']]
                ],
            ])
        ]);
    }

    // --------

    if(strpos($data, 'yesdeletebyuser__') !== false){
        $voice_unique_id = str_replace('yesdeletebyuser__', '', $data);
        $voiceinfo = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `voices` WHERE `unique_id` = '{$voice_unique_id}'"));
        $db->query("DELETE FROM `voices` WHERE `unique_id` = '{$voice_unique_id}' LIMIT 1");
        EditMessage($chatid, $messageid, '✅ ویس مورد نظر حذف شد.');
    }

    if(strpos($data, 'nodeletebyuser') !== false){
        EditMessage($chatid, $messageid, '❌ عملیات حذف ویس لغو شد.');
    }
}