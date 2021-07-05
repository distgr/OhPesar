<?php


if($callback_query){
    $data = $callback_query->data;
    if(strpos($data, 'myvoicespage_') !== false){
        $pagenum = intval(str_replace('myvoicespage_', '', $data));
        $page_num = strval($pagenum);
        $page_limit = 10;
        $query = mysqli_query($db, "SELECT * FROM `voices` WHERE `sender` = '{$fromid}'");
        $num = mysqli_num_rows($query);
        
        if(($page_limit*$pagenum) >= $num){
            $lastpage = true;
        }else{
            $lastpage = false;
        }
        
        $voices = [];
        for ($i=0; $i < $num; $i++) { 
            $voices[] = mysqli_fetch_assoc($query);
        }
        $voices = array_reverse($voices);
        $voices = array_splice($voices, ($page_limit*(($pagenum)-1)), $page_limit);

        $MyVoicesKey = [];

        if($lastpage){
            $MyVoicesKey[] = [['text'=>'صفحه قبلی ◀️', 'callback_data'=>'myvoicespage_'.strval($pagenum-1)]];
        }elseif($pagenum == 1){
            $MyVoicesKey[] = [['text'=>'▶️ صفحه بعدی', 'callback_data'=>'myvoicespage_'.strval($pagenum+1)]];
        }else{
            $MyVoicesKey[] = [['text'=>'صفحه قبلی ◀️', 'callback_data'=>'myvoicespage_'.strval($pagenum-1)], ['text'=>'▶️ صفحه بعدی', 'callback_data'=>'myvoicespage_'.strval($pagenum+1)]];
        }

        foreach ($voices as $user_voice_info) { 
            if($user_voice_info['mode'] == 'public'){
            if(!$user_voice_info['accepted']){
                    $MyVoicesKey[] = [['text'=>'🕐 '.$user_voice_info['name'], 'callback_data'=>'pendingmode']];
                    continue;
                }
            }
            if($user_voice_info['mode'] == 'public'){ $voiceemoji = '🎤'; }else{ $voiceemoji = '🔐'; }
            $MyVoicesKey[] = [
                ['text'=>$voiceemoji.' '.$user_voice_info['name'], 'switch_inline_query'=>$user_voice_info['name']],
                ['text'=>'⚙️ تنظیمات ویس', 'callback_data'=>'voicesettings__'.$user_voice_info['unique_id'].'__'.$pagenum],
            ];
        }
        
        $pagelimit = gettype($num/$page_limit) == 'integer' ? ($num/$page_limit) : intval($num/$page_limit)+1;

        Bot('EditMessageText',[
            'chat_id'=>$chatid,
            'message_id'=> $messageid,
            'text'=>"لیست تمامی ویس های ثبت شما در ربات توسط شما 👇🏻
    🔄 تعداد تمامی ویس های ثبت شده توسط شما : $num

    📖 صفحه $pagenum از $pagelimit",
            'reply_markup'=>json_encode([
                'inline_keyboard'=>$MyVoicesKey,
            ])
        ]);

    }
}