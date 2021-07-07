<?php

if($text == '🆕 جدیدترین ویس ها'){
    $query = mysqli_query($db, "SELECT * FROM `voices`");
    $num = mysqli_num_rows($query);
    
    $list = $voices = [];
    
    for ($i=0; $i < $num; $i++) {
        
        $voices[] = mysqli_fetch_assoc($query);
    }
    $voices = array_reverse($voices);
    
    
    foreach($voices as $voiceinfo){
        if($voiceinfo['mode'] == 'private' && $voiceinfo['sender'] != $inlineuserid){ continue; }
        if(!$voiceinfo['accepted']){ continue; }
        if($user['badvoices'] == 0){
            if( IsBadWord($voiceinfo['name']) ) continue;
        }
        $switchquery = ['byname'=>$voiceinfo['name'], 'byid'=>'-id '.$voiceinfo['id']][$user['sendvoiceaction']];
        $list[] = [['text'=>"🎤 ".$voiceinfo['name'], 'switch_inline_query'=>$switchquery]];
    }

    $list = array_splice($list, 0, 10, true);

    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>'لیست 10 ویس آخر ثبت شده در «اوه پسر» 👇🏻
✅ برای استفاده از ویس ها میتوانید روی آنها کنیک کنید.',
        'reply_markup'=>json_encode([
        'inline_keyboard'=>$list,
        ])
    ]);
    
}