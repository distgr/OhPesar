<?php

if($text == '🗂 ویس های من' or $text == '/myvoices'){
    $page_limit = 10;
    $query = mysqli_query($db, "SELECT * FROM `voices` WHERE `sender` = '{$from_id}'");
    $num = mysqli_num_rows($query);
    
    if(!$num){
        SendMessage($chat_id, '⚠️ شما هیچ ویسی در ربات ثبت نکردید !');
        mysqli_close($db);
        exit();
    }
    $MyVoicesKey = [];

    $voices = [];
    for ($i=0; $i < $num; $i++) { 
    	$voices[] = mysqli_fetch_assoc($query);
    }
    $voices = array_reverse($voices);
    $pagelimit = gettype($num/$page_limit) == 'integer' ? ($num/$page_limit) : intval($num/$page_limit)+1;
    if($num > $page_limit){
        $voices = array_splice($voices, 0, $page_limit, true);;
        $MyVoicesKey[] = [['text'=>'▶️ صفحه بعدی', 'callback_data'=>'myvoicespage_2']];
    }
    
    foreach ($voices as $user_voice_info) { 
        if($user_voice_info['mode'] == 'public'){
        if(!$user_voice_info['accepted']){
                $MyVoicesKey[] = [['text'=>'🕐 '.$user_voice_info['name'], 'callback_data'=>'pendingmode']];
                continue;
            }
        }
        if($user_voice_info['mode'] == 'public'){ $voiceemoji = '🎤'; }else{ $voiceemoji = '🔐'; }
        $switchquery = ['byname'=>$user_voice_info['name'], 'byid'=>'-id '.$user_voice_info['id']][$user['sendvoiceaction']];
        $MyVoicesKey[] = [
            ['text'=>$voiceemoji.' '.$user_voice_info['name'], 'switch_inline_query'=>$switchquery],
            ['text'=>'⚙️ تنظیمات ویس', 'callback_data'=>'voicesettings__'.$user_voice_info['unique_id'].'__1'],
        ];
    }
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>"لیست تمامی ویس های ثبت شما در ربات توسط شما 👇🏻
🔄 تعداد تمامی ویس های ثبت شده توسط شما : $num

📖 صفحه 1 از $pagelimit",
        'reply_markup'=>json_encode([
            'inline_keyboard'=>$MyVoicesKey,
        ])
    ]);
}