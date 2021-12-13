<?php
if($text == '🔊 لیست ویس ها' or (strpos($data, 'voicelist_sort') !== false) or (strpos($data, 'voicelistpage_') !== false)){
    $list = $msgbtn = $unshift = [];
    $page_limit = 5;
    $pagenum = 1;
    $firstpage = true;
    $isinline = false;
    $sort_warning = false;

    $selectors_data = [
        'myvoices'=> '',
        'newest'=> '',
        'popularest'=> '',
    ];

    
    if(strpos($data, 'voicelist_sort') !== false){
        $isinline = true;
        $newquery = str_replace('voicelist_sort_', '', $data);
        $selectors_data[$newquery] = '🔘';
        $userid_meta = $fromid;
        $messageid_meta = $update->callback_query->message->message_id;
        $selector_meta = 'EditMessageText';
        $queryset = $newquery;
        $sort_warning = true;
    }elseif(strpos($data, 'voicelistpage_') !== false){
        $isinline = true;
        $splitvoicelistpage = explode('_', str_replace('voicelistpage_', '', $data));
        $pagenum = $splitvoicelistpage[0];
        $queryset = $splitvoicelistpage[1];
        $selector_meta = 'EditMessageText';
        $selectors_data[$queryset] = '🔘';
        $userid_meta = $fromid;

        if($pagenum == 1){
            $firstpage = true;
        }else{
            $firstpage = false;
        }

        $messageid_meta = $update->callback_query->message->message_id;
        
    }else{
        $selectors_data['myvoices'] = '🔘';
        $userid_meta = $from_id;
        $selector_meta = 'sendmessage';
        $messageid_meta = $message_id;
        $queryset = 'myvoices';
    }
    
    $db_queries = [
        'myvoices'=> "SELECT * FROM `voices` WHERE `sender` = '{$userid_meta}'",
        'newest'=> "SELECT * FROM `voices` ORDER BY `voices`.`id` DESC",
        'popularest'=> "SELECT * FROM `voices` ORDER BY `voices`.`usecount` DESC"
    ];

    $query = mysqli_query($db, $db_queries[$queryset]);
    $num = mysqli_num_rows($query);

    if($num <= 0){
        
        if(!$isinline){
            $selectors_data['newest'] = '🔘';
            $selectors_data['myvoices'] = '';
            $selectors_data['popularest'] = '';
            $queryset = 'newest';
            $query = mysqli_query($db, $db_queries[$queryset]);
            $num = mysqli_num_rows($query);
        }else{
            bot('answercallbackquery', [
                'callback_query_id' => $update->callback_query->id,
                'text' => "❗️ شما هیچ ویسی در ربات ندارید، در نتیجه بخش ویس های من برای شما باز نمیشود.",
                'show_alert' => true
            ]);
            mysqli_close($db);
            exit();
        }
    }

    $unshift[] = [
        ['text'=>$selectors_data['myvoices']." 🗂", 'callback_data'=>'voicelist_sort_myvoices'],
        ['text'=>$selectors_data['newest']." 🆕", 'callback_data'=>'voicelist_sort_newest'],
        ['text'=>$selectors_data['popularest']." ❣️", 'callback_data'=>'voicelist_sort_popularest']
    ];


    if(($page_limit*$pagenum) >= $num){
        $lastpage = true;
    }else{
        $lastpage = false;
    }

    $pagelimit = gettype($num/$page_limit) == 'integer' ? ($num/$page_limit) : intval($num/$page_limit)+1;

    if($firstpage){
        if($num > $page_limit){
            $unshift[] = [['text'=>'▶️ صفحه بعدی', 'callback_data'=>'voicelistpage_2_'.$queryset]];
        }
    }else{
        bot('answercallbackquery', [
            'callback_query_id' => $update->callback_query->id,
            'text' => "📄 به صفحه جدید منتقل شدید.",
            'show_alert' => false
        ]);
        if($pagenum == 0 or ($pagenum == 1 && $num <= $page_limit)){
        }elseif($lastpage){
            $unshift[] = [['text'=>'صفحه قبلی ◀️', 'callback_data'=>'voicelistpage_'.strval($pagenum-1).'_'.$queryset]];
        }elseif($pagenum == 1){
            $unshift[] = [['text'=>'▶️ صفحه بعدی', 'callback_data'=>'voicelistpage_'.strval($pagenum+1).'_'.$queryset]];
        }else{
            $unshift[] = [['text'=>'صفحه قبلی ◀️', 'callback_data'=>'voicelistpage_'.strval($pagenum-1).'_'.$queryset], ['text'=>'▶️ صفحه بعدی', 'callback_data'=>'voicelistpage_'.strval($pagenum+1).'_'.$queryset]];
        }
    }
    
    
    for ($i=0; $i < $num; $i++) {
        $voiceinfo = mysqli_fetch_assoc($query);
        if($queryset !== 'myvoices'){
            if($voiceinfo['mode'] == 'private' && $voiceinfo['sender'] != $inlineuserid){ continue; }
            if(!$voiceinfo['accepted']){ continue; }
            if($user['badvoices'] == 0){
                if( IsBadWord($voiceinfo['name']) ) continue;
            }
        }
        if(!$voiceinfo['accepted'] && strtolower($voiceinfo['mode']) == 'public'){
            $msgbtn[] = [['text'=>'🕐 '.$voiceinfo['name'], 'callback_data'=>'pendingmode']];
            continue;
        }
        if($voiceinfo['banned'] == '1') continue;
        $switchquery = ['byname'=>$voiceinfo['name'], 'byid'=>'-id '.$voiceinfo['id']][$user['sendvoiceaction']];
        if($voiceinfo['mode'] == 'private') $voice_emoji = '🔒'; else $voice_emoji = '🎤'
        $addtomsg = [['text'=>$voice_emoji." ".$voiceinfo['name'], 'switch_inline_query'=>$switchquery]];
        if($voiceinfo['sender'] == $userid_meta){
            $addtomsg[] = ['text'=>'⚙️ تنظیمات ویس', 'callback_data'=>'voicesettings___'.$voiceinfo['unique_id'].'___'.$pagenum];
        }
        $msgbtn[] = $addtomsg;
    }
    
    if($queryset == 'myvoices'){
        $msgbtn = array_reverse($msgbtn);
    }
    
    if($firstpage){
        $msgbtn = array_splice($msgbtn, 0, $page_limit, true);
    }else{
        $msgbtn = array_splice($msgbtn, ($page_limit*(($pagenum)-1)), $page_limit);
    }

    

    foreach(array_reverse($unshift) as $btn){
        array_unshift($msgbtn, $btn);
    }
    
    if($sort_warning){
        bot('answercallbackquery', [
            'callback_query_id' => $update->callback_query->id,
            'text' => "🔄 وضعیت نمایش بروز شد.",
            'show_alert' => false
        ]);
    }

    if($queryset == 'myvoices'){
        $showdesc = 'ویس هایی که خودتان ارسال کردید';
    }elseif($queryset == 'newest'){
        $showdesc = 'جدیدترین ویس های اضافه شده';
    }else{
        $showdesc = 'محبوب ترین ویس ها و پر استفاده ترین ویس ها';
    }

    Bot($selector_meta ,[
        'chat_id'=>$userid_meta,
        'message_id'=> $messageid_meta,
        'text'=>"👋🏻 به لیست ویس های ربات « اوه پسر » خوش آمدید.
❕ در این بخش میتوانید به تمامی ویس هایی که در ربات ثبت شده به دسترسی داشته باشید.
✅ درحال حاضر شما در لیست زیر میتوانید $showdesc را مشاهده کنید و به تنظیمات آن دسترسی داشته باشید. گزینه 🗂 نمایشگر ویس های شما، گزینه ❣️نمایشگر ویس های محبوب و گزینه 🆕 نمایشگر ویس های جدید میباشد.

شما درحال حاضر در صفحه $pagenum از $pagelimit قرار دارید",
        'reply_markup'=>json_encode([
        'inline_keyboard'=>$msgbtn,
        ])
    ]);
    
}