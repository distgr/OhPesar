<?php

if($text == '❣️ محبوبترین ویس ها'){
    $list = $msgbtn = [];
    
    $query = mysqli_query($db, "SELECT * FROM `voices` ORDER BY `voices`.`usecount` DESC");
    $num = mysqli_num_rows($query);
    
    for ($i=0; $i < $num; $i++) {
        $voiceinfo = mysqli_fetch_assoc($query);
        if($voiceinfo['mode'] == 'private' && $voiceinfo['sender'] != $inlineuserid){ continue; }
        if(!$voiceinfo['accepted']){ continue; }
        if($user['badvoices'] == 0){
            if( IsBadWord($voiceinfo['name']) ) continue;
        }
        $switchquery = ['byname'=>$voiceinfo['name'], 'byid'=>'-id '.$voiceinfo['id']][$user['sendvoiceaction']];
        $msgbtn[] = [['text'=>"❣️🎤 ".$voiceinfo['name'], 'switch_inline_query'=>$switchquery]];
    }
    $msgbtn = array_splice($msgbtn, 0, 10, true);
    
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>'لیست 10 ویس محبوب و پر استفاده در «اوه پسر» 👇🏻
✅ برای استفاده از ویس ها میتوانید روی آنها کنیک کنید.',
        'reply_markup'=>json_encode([
        'inline_keyboard'=>$msgbtn,
        ])
    ]);
    
}