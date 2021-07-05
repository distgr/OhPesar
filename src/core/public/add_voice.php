<?php


if($text == '🎤 افزودن ویس' or $text == '/start sendvoice'){
    $db->query("UPDATE `user` SET `step` = 'sendvoice1' WHERE `id` = '{$from_id}' LIMIT 1");
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>'لطفا نام ویس را ارسال کنید.',
        'reply_markup'=>json_encode(['keyboard'=>$back, 'resize_keyboard'=>true])
    ]);
}
elseif($user['step'] == 'sendvoice1' && $text !== $backbtn){
    if(strlen($text) < 3){
        SendMessage($from_id, 'نام ویس حداقل باید دارای 3 کاراکتر باشد');
        exit();
    }
    if(strlen($text) > 45){
        SendMessage($from_id, 'نام ویس حداکثر باید دارای 45 کاراکتر باشد');
        exit();
    }
    $db->query("UPDATE `user` SET `step` = 'sendvoice2', `voicename` = '{$text}' WHERE `id` = '{$from_id}' LIMIT 1");
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>'لطفا تعیین کنید که تصمیم دارید ویس خود را بر روی چه حالتی قرار دهید 👇🏻
🔓 درصورتی که ویس خود را روی حالت عمومی قرار دهید، ویس شما نیاز به تایید توسط مدیریت را دارد و پس از تایید در دسترس عموم قرار میگیرد.
🔐 اما درصورتی که میخواهید ویس خود را روی حالت خصوصی قرار دهید، ویس شما نیاز به مرحله تایید ندارد و ویس شما در ربات ثبت میشود، اما فقط خودتان قادر به مشاهده و استفاده آن ویس خواهید بود.',
        'reply_markup'=>json_encode(['keyboard'=>$privateorpublic, 'resize_keyboard'=>true])
    ]);
    exit();
}

if($text && $user['step'] == 'sendvoice2' && $text !== $backbtn){
    $buttons = [
        "🔓 عمومی",
        "🔐 خصوصی",
    ];
    if(!in_array($text, $buttons)){
        SendMessage($chat_id, 'لطفا فقط یک گزینه را از دکمه های زیر انتخاب کنید.');
        exit();
    }
    if($user['sendvoice'] == 1 && $text == $buttons[0]){
        SendMessage($from_id, 'شما یک ویس در حال انتظار دارید! لطفا صبر کنید تا ویس ارسالی شما توسط مدیریت بررسی شود، سپس میتوانید برای ارسال ویس جدید اقدام کنید. شما درحال حاضر میتوانید یک ویس خصوصی اضافه کنید.');
        exit();
    }
    if($text == $buttons[0]){ $voicemodevar = 'public'; }
    elseif($text == $buttons[1]) { $voicemodevar = 'private'; }
    $db->query("UPDATE `user` SET `step` = 'sendvoice3', `voicemode` = '{$voicemodevar}' WHERE `id` = '{$from_id}' LIMIT 1");
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>'حالا لطفا خود ویس را ارسال کنید.',
        'reply_markup'=>json_encode(['keyboard'=>$back, 'resize_keyboard'=>true])
    ]);
    exit();
}

if($user['step'] == 'sendvoice3' && $text !== $backbtn){
    $systemid = $update->message->voice->file_unique_id;
    if(!$update->message->voice){
        SendMessage($chat_id, 'لطفا فقط یک ویس را ارسال کنید.');
        exit();
    }
    $getsubmittedvoice = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `voices` WHERE `unique_id` = '{$systemid}' LIMIT 1"));
    if($getsubmittedvoice){
        if($getsubmittedvoice['mode'] == 'private'){
            SendMessage($chat_id, '❗️اوه پسر! این ویسی که فرستادی قبلا داخل ربات توسط یه شخص دیگه ثبت شده، ولی مثل اینکه ویسی که ثبت کرده خصوصی بوده و فقط خودش میتونه از این ویس استفاده کنه... حالا لطفا یه ویس دیگه بفرست :');
        }else{
            $subvoicename = $getsubmittedvoice['name'];
            SendMessage($chat_id, "❗️اوه پسر! این ویسی که فرستادی قبلا داخل ربات با نام « $subvoicename » توسط یه شخص دیگه ثبت شده... حالا لطفا یه ویس دیگه بفرست :");
        }
        exit();
    }
    $vid = Forward($CONFIG['CHANNEL']['DATABASEID'], $chat_id, $message_id);
    $vr = json_decode($vid, true);
    $id = strval(rand(11111, 99999));
    $definedvoicename = $user['voicename'];
    $voicedburl = 'https://t.me/'.$CONFIG['CHANNEL']['DATABASE'].'/'.strval($vr['result']['message_id']);
    $voicemsgid = $vr['result']['message_id'];
    $thevoicemode = $user['voicemode'];
    $db->query("INSERT INTO `voices` (`unique_id`, `accepted`, `name`, `url`, `sender`, `messageid`, `mode`, `usecount`) VALUES ('{$systemid}', '0', '{$definedvoicename}', '$voicedburl', '$from_id', '$voicemsgid', '$thevoicemode', 0)");
    if($user['voicemode'] == 'public'){
        Bot('sendMessage',[
            'chat_id'=>$chat_id,
            'text'=>'ویس شما برای تایید برای مدیریت ارسال شد. لطفا منتظر بمانید تا ویس ارسالی توسط شما تایید شود',
            'reply_markup'=>json_encode(['keyboard'=>$home, 'resize_keyboard'=>true])
        ]);
        $first_name = $message->from->first_name;
        $last_name = $message->from->last_name;
        $username = $update->callback_query->from->username;
        $voicename = $user['voicename'];
        $senderusername = '';
        if(isset($username)){
            $senderusername = '🆔 آیدی ارسال کننده : @'.$username;
        }
        SendVoice($CONFIG['CHANNEL']['VOICEACCEPT'],
        'https://t.me/'.$CONFIG['CHANNEL']['DATABASE'].'/'.strval($vr['result']['message_id']), 
        json_encode([
            'inline_keyboard'=>[
            [['text'=>"✅",'callback_data'=>'accept-'.$systemid], ['text'=>"❌",'callback_data'=>'reject-'.$systemid]],
            ],
        ]),
        "🎤 $voicename
    
👤 ارسال کننده : $first_name
💬 آیدی عددی ارسال کننده : $from_id
$senderusername"
        );
        $usersendvoice = '1';
    }else{
        Bot('sendMessage',[
            'chat_id'=>$chat_id,
            'text'=>'🔐 ویس شما در حالت خصوصی اضافه شد و فقط برای خودتان قابل دسترس میباشد.',
            'reply_markup'=>json_encode(['keyboard'=>$home, 'resize_keyboard'=>true])
        ]);
        $usersendvoice = '0';
    }
    $db->query("UPDATE `user` SET `step` = 'none', `voicename` = NULL, `voicemode` = NULL, `sendvoice` = '{$usersendvoice}' WHERE `user`.`id` = '{$from_id}' LIMIT 1");
    exit();
}
