<?php

ob_start();
error_reporting(0);
date_default_timezone_set('Asia/Tehran');
$CONFIG = json_decode(file_get_contents('config.json'), true);
include('functions.php');
include('buttons.php');
//-----------------------------------------

define('API_KEY', $CONFIG['TOKEN']);

//-----------------------------------------

function Bot($method,$datas=[]){
    $url = "https://api.telegram.org/bot".API_KEY."/".$method;
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_POSTFIELDS,$datas);
    $res = curl_exec($ch);
    if(curl_error($ch)){
        var_dump(curl_error($ch));
    }else{
       return $res;
    }
}

# --------------------------- #

$update = json_decode(file_get_contents('php://input'));
$channel = $CONFIG['CHANNEL']['OFFICIAL'];
if(isset($update->message)){
    $message = $update->message;
    $inline = $update->inline_query;
    $inline_text = $update->inline_query->query;
    $membercalls = $update->inline_query->id;
	$text = $message->text;
	$tc = $message->chat->type;
    $chat_id = $message->chat->id;
	$from_id = $message->from->id;
	$message_id = $message->message_id;
    $first_name = $message->from->first_name;
    $last_name = $message->from->last_name;
    $username = $message->from->username;
    $tch = json_decode(Bot('getChatMember', [
        'chat_id'=> '@'.$channel,
        'user_id'=>$from_id
    ]), true)['result']['status'];
}
if(isset($update->callback_query)){
    $callback_query = $update->callback_query;
	$databack = $callback_query->data;
	$tc = $callback_query->message->chat->type;
    $chatid = $callback_query->message->chat->id;
	$fromid = $callback_query->from->id;
	$messageid = $callback_query->message->message_id;
    $firstname = $callback_query->from->first_name;
    $lastname = $callback_query->from->last_name;
    $cusername = $callback_query->from->username;
    $membercall = $callback_query->id;
    $tch = json_decode(Bot('getChatMember', [
        'chat_id'=> '@'.$channel,
        'user_id'=>$fromid
    ]), true)['result']['status'];
}
if(isset($update->inline_query)){
    $inline = $update->inline_query;
    $inline_text = $inline->query;
    $membercalls = $inline->id;
    $id_from = $inline->from->id;
    $tch = json_decode(Bot('getChatMember', [
        'chat_id'=> '@'.$channel,
        'user_id'=>$id_from
    ]), true)['result']['status'];
}



# --------------------------- #




# --------------------------- #

foreach($CONFIG['DEFAULTS'] as $part => $val){
    foreach($val as $i){
        if($part == 'FOLDERS'){
            if(!is_dir($i)) mkdir($i);
        }else{
            if(!is_file($i)) file_put_contents($i, null);
        }
    }
}

if(!is_file('data/users/'.$from_id.'.json'))
    file_put_contents('data/users/'.$from_id.'.json', json_encode([
        'step'=> 'none'
    ]));

# --------------------------- #

if($text == '/start jointhechannel'){
    Bot('sendMessage',[
            'chat_id'=>$chat_id,
            'text'=>'برای عضویت در کانال رسمی ربات اوه پسر، روی کلمه زیر کلیک کنید :',
            'reply_markup'=>json_encode([
                'inline_keyboard'=>[
                [['text'=>"✅ ورود به کانال", 'url'=>'https://t.me/'.$channel]],
                ],
            ])
        ]);
        exit();
}

if(in_array($from_id, $CONFIG['ADMINS'])){
        $home[] = [['text'=>"📍 پنل مدیریت"]];
}

if($text){
    if(!in_array($tch,['member','creator','administrator'])){
        Bot('sendMessage',[
            'chat_id'=>$chat_id,
            'text'=>'اوه پسر! 🤯 دیدی چیشد؟ برای اینکه بتونی از ربات استفاده کنی باید داخل کانال رسمی «اوه پسر» عضو باشی! ولی متاسفانه تو عضو کانال اوه پسر نیستی و نمیتونی از ربات استفاده کنی! 🤛🏻
    پس همین الان جویین شو و دوباره وارد ربات شو و دوباره پیامتو ارسال کن 👇🏻',
            'reply_markup'=>json_encode([
                'inline_keyboard'=>[
                [['text'=>"✅ ورود به کانال", 'url'=>'https://t.me/'.$channel]],
                ],
            ])
        ]);
        exit();
    }
}

# --------------------------- #
$user = json_decode(file_get_contents("data/users/$from_id.json"), true);
# --------------------------- #

if(strtolower($text) == '/start' or $text == $backbtn){
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>'اوه پسر! باورم نمیشه! خیلی خوش اومدی😦
ربات اوه پسر یه ربات طنزه که بهت این امکان رو میده که ویس های طنز رو در مکان های طنز ارسال کنی 😎

الان هم میتونی از دکمه های زیر استفاده کنی 👇🏻',
        'reply_markup'=>json_encode(['keyboard'=>$home ,'resize_keyboard'=>true
        ])
    ]);
    $user['step'] = 'none';
    UpdateUser();
    exit();
}

elseif($text == '🆕 جدیدترین ویس ها'){
    $list = [];
    $voices = array_diff(sortandscan('data/voices'), ['.', '..', '.json']);
    $voices = array_slice($voices, 0, 10, true);
    foreach($voices as $thevoice){
        $voiceinfo = json_decode(file_get_contents("data/voices/$thevoice"), true);
        if(!$voiceinfo['accepted']){ continue; }
        if(!(strpos(strtolower($voiceinfo['name']), strtolower($inline_text)) !== false) && strlen($inline_text) > 1){ continue; }
        $list[] = [['text'=>"🎤 ".$voiceinfo['name'], 'switch_inline_query'=>$voiceinfo['name']]];
    }

    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>'لیست 10 ویس آخر ثبت شده در «اوه پسر» 👇🏻
✅ برای استفاده از ویس ها میتوانید روی آنها کنیک کنید.',
        'reply_markup'=>json_encode([
        'inline_keyboard'=>$list,
        ])
    ]);
    
}


elseif($text == '🎤 ارسال ویس' or $text == '/start sendvoice'){
    if($user['sendvoice']){
        SendMessage($from_id, 'شما یک ویس در حال انتظار دارید! لطفا صبر کنید تا ویس ارسالی شما توسط مدیریت بررسی شود، سپس میتوانید برای ارسال ویس جدید اقدام کنید.');
        exit();
    }
    $user['step'] = 'sendvoice1';
    UpdateUser();
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>'لطفا نام ویس را ارسال کنید.',
        'reply_markup'=>json_encode(['keyboard'=>$back, 'resize_keyboard'=>true])
    ]);
}

elseif($text && $user['step'] == 'sendvoice1' && $text !== $backbtn){
    if(strlen($text) < 3){
        SendMessage($from_id, 'نام ویس حداقل باید دارای 3 کاراکتر باشد');
        exit();
    }
    if(strlen($text) > 60){
        SendMessage($from_id, 'نام ویس حداکثر باید دارای 60 کاراکتر باشد');
        exit();
    }
    $user['voicename'] = $text;
    $user['step'] = 'sendvoice2';
    UpdateUser();
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>'حالا لطفا خود ویس را ارسال کنید.',
        'reply_markup'=>json_encode(['keyboard'=>$back, 'resize_keyboard'=>true])
    ]);
}

elseif($user['step'] == 'sendvoice2' && $text !== $backbtn){
    if(!$update->message->voice){
        SendMessage($chat_id, 'لطفا فقط یک ویس را ارسال کنید.');
        exit();
    }
    $vid = Forward('-1001169964092', $chat_id, $message_id);
    $vr = json_decode($vid, true);
    $dbase = [
        'accepted'=> false,
        'name'=> $user['voicename'],
        'url' => 'https://t.me/VoiceDatabaseOfOhPesar/'.strval($vr['result']['message_id']),
        'sender'=> $from_id,
        'messageid'=> $vr['result']['message_id']
    ];
    $id = strval(rand(11111,99999));
    // $systemid = strval($from_id)."000".$id;
    $systemid = $update->message->voice->file_unique_id;
    file_put_contents('data/voices/'.$systemid.'.json', json_encode($dbase));
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
    SendVoice('-1001425492536',
    'https://t.me/VoiceDatabaseOfOhPesar/'.strval($vr['result']['message_id']), 
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
    $user['voicename'] = 'none';
    $user['step'] = 'none';
    $user['sendvoice'] = true;
    UpdateUser();
}



elseif($callback_query){
    $data = $callback_query->data;
    if($data == 'pendingmode'){
        bot('answercallbackquery', [
            'callback_query_id' => $update->callback_query->id,
            'text' => "🕐 این ویس درحالت بررسی قرار دارد و هنوز توسط تایید نشده است. ویس شما تا زمانی که تایید نشود قابل استفاده نمیباشد.",
            'show_alert' => true
        ]);
    }
    if(strpos($data, 'myvoicespage_') !== false){
        $pagenum = intval(str_replace('myvoicespage_', '', $data));
        $__VOICES = [];
        foreach (sortandscan('data/voices') as $_VOICE) {
            $_VOICEINFO = json_decode(file_get_contents('data/voices/'.$_VOICE), true);
            if($_VOICEINFO['sender'] == $fromid){
                $__VOICES[] = $_VOICE;
            }
        }
        
        $AllCount = count($__VOICES);
        if((10*$pagenum) > $AllCount){
            $lastpage = true;
        }else{
            $lastpage = false;
        }
        $__VOICES = array_splice($__VOICES, (10*(($pagenum)-1)), 10);

        $MyVoicesKey = [];

        if($lastpage){
            $MyVoicesKey[] = [['text'=>'صفحه قبلی ◀️', 'callback_data'=>'myvoicespage_'.strval($pagenum-1)]];
        }elseif($pagenum == 1){
            $MyVoicesKey[] = [['text'=>'▶️ صفحه بعدی', 'callback_data'=>'myvoicespage_'.strval($pagenum+1)]];
        }else{
            $MyVoicesKey[] = [['text'=>'صفحه قبلی ◀️', 'callback_data'=>'myvoicespage_'.strval($pagenum-1)], ['text'=>'▶️ صفحه بعدی', 'callback_data'=>'myvoicespage_'.strval($pagenum+1)]];
        }

        foreach ($__VOICES as $uservoice) {
            $voice_unique_id = str_replace('.json', '', $uservoice);
            $user_voice_info = json_decode(file_get_contents('data/voices/'.$uservoice), true);
            if(!$user_voice_info['accepted']){
                $MyVoicesKey[] = [['text'=>'🕐 '.$user_voice_info['name'], 'callback_data'=>'pendingmode']];
                continue;
            }
            $MyVoicesKey[] = [
                ['text'=>'🎤 '.$user_voice_info['name'], 'switch_inline_query'=>$user_voice_info['name']],
                ['text'=>'❌ حذف ویس', 'callback_data'=>'removebyuser_'.$voice_unique_id],
            ];
        }

        Bot('EditMessageText',[
            'chat_id'=>$chatid,
            'message_id'=> $messageid,
            'text'=>"لیست تمامی ویس های ثبت شما در ربات توسط شما 👇🏻
🔄 تعداد تمامی ویس های ثبت شده توسط شما : $AllCount",
            'reply_markup'=>json_encode([
                'inline_keyboard'=>$MyVoicesKey,
            ])
        ]);

    }
    if(strpos($data, 'removebyuser_') !== false){
        $voice_unique_id = str_replace('removebyuser_', '', $data);
        $voiceinfo = json_decode(file_get_contents('data/voices/'.$voice_unique_id.'.json'), true);
        $voicename = $voiceinfo['name'];
        Bot('EditMessageText',[
            'chat_id'=>$chatid,
            'message_id'=> $messageid,
            'text'=>"❕ آیا مطمئن هستید که میخواهید ویس « $voicename » را حذف کنید ؟",
            'reply_markup'=>json_encode([
                'inline_keyboard'=>[
                    [['text'=>"✅ بله حذف کن", 'callback_data'=>'yesdeletebyuser_'.$voice_unique_id], ['text'=>"❌ نه حذف نکن", 'callback_data'=>'nodeletebyuser']]
                ],
            ])
        ]);
    }
    if(strpos($data, 'yesdeletebyuser_') !== false){
        $voice_unique_id = str_replace('yesdeletebyuser_', '', $data);
        unlink('data/voices/'.$voice_unique_id.'.json');
        EditMessage($chatid, $messageid, '✅ ویس مورد نظر حذف شد.');
    }
    if(strpos($data, 'nodeletebyuser') !== false){
        EditMessage($chatid, $messageid, '❌ عملیات حذف ویس لغو شد.');
    }
    
    if(strpos($data, 'accept-') !== false){
        bot('answercallbackquery', [
            'callback_query_id' => $update->callback_query->id,
            'text' => "ویس تایید شد. ✅",
            'show_alert' => false
        ]);
        $voiceid = str_replace('accept-', '', $data);
        $getvoice = json_decode(file_get_contents('data/voices/'.$voiceid.'.json'), true);
        $getvoice['accepted'] = true;
        Bot('deletemessage', [
            'chat_id' => $chatid,
            'message_id' => $messageid,
        ]);
        $usersender = json_decode(file_get_contents("data/users/".$getvoice['sender'].".json"), true);
        SendMessage($getvoice['sender'], 'ویس شما توسط مدیریت تایید شد. ✅');
        file_put_contents('data/voices/'.$voiceid.'.json', json_encode($getvoice));
        $usersender['sendvoice'] = false;
        file_put_contents("data/users/".$getvoice['sender'].".json", json_encode($usersender));
    }elseif(strpos($data, 'reject-') !== false){
        $voiceid = str_replace('reject-', '', $data);
        bot('answercallbackquery', [
            'callback_query_id' => $update->callback_query->id,
            'text' => "ویس لغو شد. ❌",
            'show_alert' => false
        ]);
        Bot('deletemessage', [
            'chat_id' => $chatid,
            'message_id' => $messageid,
        ]);
        $getvoice = json_decode(file_get_contents('data/voices/'.$voiceid.'.json'), true);
        SendMessage($getvoice['sender'], 'ویس شما توسط مدیریت رد شد. ❌');
        $usersender['sendvoice'] = false;
        file_put_contents("data/users/".$getvoice['sender'].".json", json_encode($usersender));
        unlink('data/voices/'.$voiceid.'.json');
        
    }
}

elseif($text == '📍 پنل مدیریت' && in_array($from_id, $CONFIG['ADMINS'])){
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>'⚙️ به پنل مدیریت ربات «اوه پسر» خوش آمدید.',
        'reply_markup'=>json_encode(['keyboard'=>$adminpanel ,'resize_keyboard'=>true])
    ]);
    // SendMessage($CONFIG['CHANNEL']['LOGID'], "پنل مدیریت توسط $from_id با نام $first_name باز شد.");
}

elseif($text == '🗑 حذف ویس' && in_array($from_id, $CONFIG['ADMINS'])){
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>'لطفا ویس مورد نظر از «اوه پسر» را ارسال یا فوروارد کنید تا حذف شود :',
        'reply_markup'=>json_encode(['keyboard'=>$back ,'resize_keyboard'=>true])
    ]);
    $user['step'] = 'deletevoice1';
    UpdateUser();
}

elseif($update->message->voice && $user['step'] == 'deletevoice1'){
    $voiceid = $update->message->voice->file_unique_id;
    if(!is_file("data/voices/$voiceid.json")){
        SendMessage($chat_id, 'چنین ویسی در دیتابیس «اوه پسر» یافت نشد !');
        exit();
    }
    $voicedata = json_decode(file_get_contents("data/voices/$voiceid.json"), true);
    $voicename = $voicedata['name'];
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>"آیا مطمئن هستید که میخواهید ویس « $voicename » را حذف کنید؟",
        'reply_markup'=>json_encode(['keyboard'=>$yesnopanel ,'resize_keyboard'=>true])
    ]);
    $user['step'] = 'deletevoice2';
    $user['voiceid'] = $voiceid;
    UpdateUser();
}

elseif($text && $text !== $backbtn && $user['step'] == 'deletevoice2'){
    $choices = ["✅ بله", "❌ خیر"];
    if(!in_array($text, $choices)){
        SendMessage($chat_id, 'لطفا فقط از دکمه های پایین یک گزینه را انتخاب کنید.');
        exit();
    }
    if($text == $choices[1]){
        Bot('sendMessage',[
            'chat_id'=>$chat_id,
            'text'=>"عملیات حذف ویس لغو شد.",
            'reply_markup'=>json_encode(['keyboard'=>$adminpanel ,'resize_keyboard'=>true])
        ]);
        $user['step'] = 'none';
        UpdateUser();
        exit();
    }
    $voiceid = $user['voiceid'];
    $voicedata = json_decode(file_get_contents("data/voices/$voiceid.json"), true);
    $voicename = $voicedata['name'];
    unlink("data/voices/$voiceid.json");
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>"ویس « $voicename » با موفقیت حذف شد.",
        'reply_markup'=>json_encode(['keyboard'=>$adminpanel ,'resize_keyboard'=>true])
    ]);
    SendMessage($CONFIG['CHANNEL']['LOGID'], "ویس « $voicename » توسط ادمین $from_id با نام $first_name حذف شد.");
    $user['step'] = 'none';
    UpdateUser();
}

elseif($text == '✏️ ویرایش ویس' && in_array($from_id, $CONFIG['ADMINS'])){
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>'لطفا ویس مورد نظر را ارسال کنید :',
        'reply_markup'=>json_encode(['keyboard'=>$back ,'resize_keyboard'=>true])
    ]);
    $user['step'] = 'editvoice1';
    UpdateUser();
}

elseif($update->message->voice && $user['step'] == 'editvoice1'){
    $voiceid = $update->message->voice->file_unique_id;
    if(!is_file("data/voices/$voiceid.json")){
        SendMessage($chat_id, 'چنین ویسی در دیتابیس «اوه پسر» یافت نشد !');
        exit();
    }
    $voicedata = json_decode(file_get_contents("data/voices/$voiceid.json"), true);
    $voicename = $voicedata['name'];
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>"شما ویس « $voicename » را انتخاب کردید. لطفا از گزینه های زیر یک مورد را برای ویرایش انتخاب کنید 👇🏻",
        'reply_markup'=>json_encode(['keyboard'=>$editvoicepanel ,'resize_keyboard'=>true])
    ]);
    $user['step'] = 'editvoice2';
    $user['voiceid'] = $voiceid;
    UpdateUser();
}

elseif($text && $text !== $backbtn && $user['step'] == 'editvoice2'){
    $voiceid = $user['voiceid'];
    $choices = [
        '✏️ ویرایش نام ویس',
        '✏️ ویرایش صدای ویس'  
    ];
    if(!in_array($text, $choices)){
        SendMessage($chat_id, 'لطفا فقط از دکمه های پایین یک گزینه را انتخاب کنید.');
        exit();
    }
    $voicedata = json_decode(file_get_contents("data/voices/$voiceid.json"), true);
    $voicename = $voicedata['name'];
    if($text == $choices[0]){
        Bot('sendMessage',[
            'chat_id'=>$chat_id,
            'text'=>"لطفا نام جدید را برای ویس « $voicename » ارسال کنید :",
            'reply_markup'=>json_encode(['keyboard'=>$back ,'resize_keyboard'=>true])
        ]);
        $user['voiceedit'] = 'name';
    }elseif($text == $choices[1]){
        Bot('sendMessage',[
            'chat_id'=>$chat_id,
            'text'=>"لطفا ویس جدید جایگزین را برای ویس « $voicename » ارسال کنید :",
            'reply_markup'=>json_encode(['keyboard'=>$back ,'resize_keyboard'=>true])
        ]);
        $user['voiceedit'] = 'replace';
    }
    $user['step'] = 'editvoice3';
    UpdateUser();
}

elseif($user['step'] == 'editvoice3'){
    $voiceid = $user['voiceid'];
    $getvoice = json_decode(file_get_contents("data/voices/$voiceid.json"), true);
    if($update->message->voice && $user['voiceedit'] == 'replace'){
        $vid = Forward('-1001169964092', $chat_id, $message_id);
        $vr = json_decode($vid, true);
        $voicename = $getvoice['name'];
        $getvoice['url'] = 'https://t.me/VoiceDatabaseOfOhPesar/'.strval($vr['result']['message_id']);
        file_put_contents('data/voices/'.$voiceid.'.json', json_encode($getvoice));
        Bot('sendMessage',[
            'chat_id'=>$chat_id,
            'text'=>"✅ ویس ارسالی شما، جایگزین ویس « $voicename » شد.",
            'reply_markup'=>json_encode(['keyboard'=>$adminpanel ,'resize_keyboard'=>true])
        ]);
        rename('data/voices/'.$voiceid.'.json', 'data/voices/'.$update->message->voice->file_unique_id.'.json');
        SendMessage($CONFIG['CHANNEL']['LOGID'], "ویس « $voicename » توسط ادمین $from_id با نام $first_name جایگزین ویس دیگری شد.");
    }elseif($text && $user['voiceedit'] == 'name'){
        $old_name = $getvoice['name'];
        $getvoice['name'] = $text;
        file_put_contents('data/voices/'.$voiceid.'.json', json_encode($getvoice));
        Bot('sendMessage',[
            'chat_id'=>$chat_id,
            'text'=>"✅ نام ویس « $old_name » به نام « $text » تغییر پیدا کرد.",
            'reply_markup'=>json_encode(['keyboard'=>$adminpanel ,'resize_keyboard'=>true])
        ]);
        SendMessage($CONFIG['CHANNEL']['LOGID'], "نام ویس « $old_name » به نام « $text » توسط ادمین $from_id با نام $first_name تغییر پیدا کرد.");
    }
    $user['step'] = 'none';
    UpdateUser();
}

elseif($text == '🖥 آمار' && in_array($from_id, $CONFIG['ADMINS'])){
    SendMessage($from_id, 'درحال بررسی...');
    $all_voices = $unaccepted_voice = $accepted_voice = $all_users = 0;
    foreach(sortandscan('data/voices') as $voice){
        $get_voice = json_decode(file_get_contents("data/voices/$voice"), true);
        if($get_voice['accepted']){
            $accepted_voice++;
        }else{
            $unaccepted_voice++;
        }
        $all_voices++;
    }
    foreach(sortandscan('data/users') as $auser){
        $all_users++;
    }
    $admins = count($CONFIG['ADMINS']);
    SendMessage($from_id, "📌 آمار ربات اوه پسر درحالت حاضر به شرح زیر میباشد 👇🏻

👤 تعداد تمامی کاربران ربات : $all_users
🚨 تعداد ادمین های ربات : $admins

🎤 تعداد تمامی ویس ها : $all_voices
✅ ویس های تایید شده : $accepted_voice
❌ ویس های تایید نشده : $unaccepted_voice
");
    SendMessage($CONFIG['CHANNEL']['LOGID'], "آمار ربات توسط $from_id با نام $first_name گرفته شد.");
}

elseif($text == '🧐 راهنما'){
    $cap = '👈🏻 برای استفاده از ربات اوه پسر و ارسال ویس ها داخل چت مورد نظر، کافیه که عبارت زیر رو مانند عکس همراه با یک فاصله تایپ کنید :
@OhPesar
حتما حواست باشه بعد از اینکه این آیدی رو نوشتی یه فاصله هم بعدش بزاری تا لیست آخرین ویس های ثبت شده در ربات برات باز بشه 😛
😎بعد میتونی با نوشتن یک عبارت، ویس مورد نظرتو هم جستوجو کنی

اگر هم دیدی یه نفر یه ویسی رو فرستاد و خواستی ببینی اسم اون ویس چیه، میتونی اون ویس رو همینجا داخل ربات فوروارد کنی تا اسمشو بهت بگم 😆


راستی! جدای اون روش بالایی که بهت گفتم، میتونی با کلیک بر روی دکمه پایین هم چت مورد نظرتو انتخاب کنی تا منوی ربات برات باز بشه 👇🏻';
    SendPhoto($chat_id, 'https://t.me/VoiceDatabaseOfOhPesar/76', json_encode(['inline_keyboard'=>[[['text'=>"🎤 ارسال یک ویس", 'switch_inline_query'=>'']]]]), $cap, null);
}




elseif($text == '❣️ ویس های من' or $text == '/myvoices'){
    $__VOICES = [];
    foreach (sortandscan('data/voices') as $_VOICE) {
        $_VOICEINFO = json_decode(file_get_contents('data/voices/'.$_VOICE), true);
        if($_VOICEINFO['sender'] == $from_id){
            $__VOICES[] = $_VOICE;
        }
    }

    if($__VOICES == []){
        SendMessage($chat_id, '⚠️ شما هیچ ویسی در ربات ثبت نکردید !');
        exit();
    }
    $allvoicescount = count($__VOICES);
    $MyVoicesKey = []; // To store 

    if(count($__VOICES) > 10){
        $__VOICES = array_splice($__VOICES, 0, 10, true);
        $MyVoicesKey[] = [['text'=>'▶️ صفحه بعدی', 'callback_data'=>'myvoicespage_2']];
    }

    foreach ($__VOICES as $uservoice) {
        $voice_unique_id = str_replace('.json', '', $uservoice);
        $user_voice_info = json_decode(file_get_contents('data/voices/'.$uservoice), true);
        if(!$user_voice_info['accepted']){
            $MyVoicesKey[] = [['text'=>'🕐 '.$user_voice_info['name'], 'callback_data'=>'pendingmode']];
            continue;
        }
        $MyVoicesKey[] = [
            ['text'=>'🎤 '.$user_voice_info['name'], 'switch_inline_query'=>$user_voice_info['name']],
            ['text'=>'❌ حذف ویس', 'callback_data'=>'removebyuser_'.$voice_unique_id],
        ];
    }

    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>"لیست تمامی ویس های ثبت شما در ربات توسط شما 👇🏻
🔄 تعداد تمامی ویس های ثبت شده توسط شما : $allvoicescount",
        'reply_markup'=>json_encode([
            'inline_keyboard'=>$MyVoicesKey,
        ])
    ]);
}


elseif($text == '💬 پیام همگانی' && in_array($chat_id, $CONFIG['ADMINS'])){
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>"لطفا پیام مورد نظر خود را ارسال کنید تا برای همه اعضا ارسال شود : (لطفا در ارسال پیام دقت کنید، این بخش فاقد تاییدیه میباشد و به محض ارسال پیام برای همه ارسال میشود)",
        'reply_markup'=>json_encode(['keyboard'=>$back ,'resize_keyboard'=>true])
    ]);
    $user['step'] = 'msg2all';
    UpdateUser();
}

elseif($user['step'] == 'msg2all' && ($text !== $backbtn or strtolower($text) !== '/start')){
    $user['step'] = 'none';
    UpdateUser();
    $memberscount = count(sortandscan('data/users'));
    
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>"درحال ارسال برای تمامی $memberscount ممبر... لطفا برای بهبود سرعت تا تکمیل فرایند ارسال کاری انجام ندهید!",
        'reply_markup'=>json_encode(['keyboard'=>$adminpanel ,'resize_keyboard'=>true])
    ]);
    foreach(sortandscan('data/users') as $selecteduser){
        SendMessage(str_replace('.json', '', $selecteduser), $text);
    }
    SendMessage($chat_id, 'پیام مورد نظر برای همه اعضای ربات ارسال شد. ✅');
    
}


elseif($text == '💬 فوروارد همگانی' && in_array($chat_id, $CONFIG['ADMINS'])){
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>"لطفا پیام مورد نظر خود را فوروارد کنید تا برای همه اعضا فوروارد شود : (لطفا در ارسال پیام دقت کنید، این بخش فاقد تاییدیه میباشد و به محض ارسال پیام برای همه ارسال میشود)",
        'reply_markup'=>json_encode(['keyboard'=>$back ,'resize_keyboard'=>true])
    ]);
    $user['step'] = 'forward2all';
    UpdateUser();
}

elseif($user['step'] == 'forward2all' && ($text !== $backbtn or strtolower($text) !== '/start')){
    $user['step'] = 'none';
    UpdateUser();
    $memberscount = count(sortandscan('data/users'));
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>"درحال فوروارد برای تمامی $memberscount ممبر... لطفا برای بهبود سرعت تا تکمیل فرایند فوروارد کاری انجام ندهید!",
        'reply_markup'=>json_encode(['keyboard'=>$adminpanel ,'resize_keyboard'=>true])
    ]);
    foreach(sortandscan('data/users') as $selecteduser){
        Forward(str_replace('.json', '', $selecteduser), $from_id, $message_id);
    }
    SendMessage($chat_id, 'پیام مورد نظر برای همه اعضای ربات فوروارد شد. ✅');
}


elseif(!is_null($inline_text)){
    $results = [];
    $inlineuserid = $update->inline_query->from->id;
    if(!is_file("data/users/$inlineuserid.json")){
        Bot('answerInlineQuery', [
            'inline_query_id' => $membercalls,
            'results' => json_encode($results),
            'switch_pm_text'=> 'برای استفاده از ربات باید ربات را استارت بزنید',
            'switch_pm_parameter'=> 'startforuse'
        ]);
        exit();
    }
    if(!in_array($tch,['member','creator','administrator'])){
        Bot('answerInlineQuery', [
            'inline_query_id' => $membercalls,
            'results' => json_encode($results),
            'switch_pm_text'=> 'لطفا وارد کانال اوه پسر شوید',
            'switch_pm_parameter'=> 'jointhechannel'
        ]);
        exit();
    }
    $voices = array_diff(sortandscan('data/voices'), ['.', '..', '.json']);
    if(strlen($inline_text) < 1){
    $voices = array_slice($voices, 0, 20, true);
    }
    foreach($voices as $thevoice){
        $voiceinfo = json_decode(file_get_contents("data/voices/$thevoice"), true);
        if(!$voiceinfo['accepted']){ continue; }
        if(!(strpos(strtolower($voiceinfo['name']), strtolower($inline_text)) !== false) && strlen($inline_text) > 1){ continue; }
        $results[] = [
            'type' => 'voice',
            'id' => base64_encode(rand()),
            'voice_url' =>  $voiceinfo['url'],
            'title' => $voiceinfo['name'],
        ];
    }
    $dataval = [
        'inline_query_id' => $membercalls,
        'results' => json_encode($results)
    ];
    if($results == []){
        $dataval['switch_pm_text'] = 'نتیجه خاصی پیدا شد';
        $dataval['switch_pm_parameter'] = 'noresult';
    }
    if(strlen($inline_text) < 1){
        $dataval['switch_pm_text'] = 'ارسال ویس جدید';
        $dataval['switch_pm_parameter'] = 'sendvoice';
    }
    
    Bot('answerInlineQuery', $dataval);
}

elseif($update->message->voice){
    $vid = $update->message->voice->file_unique_id;
    $found = true;
    if(!is_file("data/voices/$vid.json")) $found = false;
    $voiceinfo = json_decode(file_get_contents("data/voices/$vid.json"), true);
    if(!$voiceinfo['accepted']) $found = false;
    if($message->via_bot->username !== 'OhPesarBot') $found = false;
    if(!$found && $user['step'] == 'none'){
        SendMessage($chat_id, '🧐 همچین ویسی داخل ربات ثبت نشده!');
        exit();
    }
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>'🎤 نام ویس ارسالی : '.$voiceinfo['name'],
        'reply_markup'=>json_encode([
        'inline_keyboard'=>[
            [['text'=>"🎤 ارسال ویس برای دیگران", 'switch_inline_query'=>$voiceinfo['name']]]
        ],
        ])
    ]);
}



?>