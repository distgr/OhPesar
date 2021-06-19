<?php

$allowed_ipranges = [
    ['lower' => '149.154.160.0', 'upper' => '149.154.175.255'],
    ['lower' => '91.108.4.0',    'upper' => '91.108.7.255'],
];
$ip_dec = (float) sprintf("%u", ip2long($_SERVER['REMOTE_ADDR']));
$ok = false;
foreach ($allowed_ipranges as $iprange) if (!$ok) {
    $lower_dec = (float) sprintf("%u", ip2long($iprange['lower']));
    $upper_dec = (float) sprintf("%u", ip2long($iprange['upper']));
    if ($ip_dec >= $lower_dec and $ip_dec <= $upper_dec) $ok = true;
}
if (!$ok) die();

ob_start();
error_reporting(0);
date_default_timezone_set('Asia/Tehran');
$CONFIG = json_decode(file_get_contents('config.json'), true);
include('functions.php');
include('buttons.php');
include('database.php');
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
    // $tch = json_decode(Bot('getChatMember', [
    //     'chat_id'=> '@'.$channel,
    //     'user_id'=>$from_id
    // ]), true)['result']['status'];
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
    // $tch = json_decode(Bot('getChatMember', [
    //     'chat_id'=> '@'.$channel,
    //     'user_id'=>$fromid
    // ]), true)['result']['status'];
}
if(isset($update->inline_query)){
    $inline = $update->inline_query;
    $inline_text = $inline->query;
    $membercalls = $inline->id;
    $id_from = $inline->from->id;
    // $tch = json_decode(Bot('getChatMember', [
    //     'chat_id'=> '@'.$channel,
    //     'user_id'=>$id_from
    // ]), true)['result']['status'];
}




# --------------------------- #

if(isset($from_id))
    $user = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `user` WHERE `id` = '{$from_id}' LIMIT 1"));
    
if (!$user) {
    $db->query("INSERT INTO `user` (`id`, `step`) VALUES ('{$from_id}', 'none')");
}
# --------------------------- #
$home[] = [['text'=>"⚙️ تنظیمات"]];

if(in_array($from_id, $CONFIG['ADMINS'])){
        $home[] = [['text'=>"📍 پنل مدیریت"]];
}

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
    $db->query("UPDATE `user` SET `step` = 'none' WHERE `id` = '{$from_id}' LIMIT 1");
    exit();
}

elseif($text == '⚙️ تنظیمات'){
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

    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>"⚙️ به تنظیمات ربات اوه پسر خوش آمدید! در این بخش میتوانید تعیین کنید که هنگامی که آیدی ربات را در چت مورد نظر وارد کردید، بر چه اساسی و چه ویس هایی برای شما به نمایش گذاشته شود 👇🏻",
        'reply_markup'=>json_encode([
            'inline_keyboard'=>[
                [['text'=>$sortby['newest'].' جدیدترین ویس ها', 'callback_data'=>'setsortby_newest'], ['text'=>$sortby['oldest'].' قدیمیترین ویس ها', 'callback_data'=>'setsortby_oldest']],
                [['text'=>$sortby['popularest'].' محبوبترین ویس ها', 'callback_data'=>'setsortby_popularest']],
            ],
        ])
    ]);
}

elseif($text == '🆕 جدیدترین ویس ها'){
    $query = mysqli_query($db, "SELECT * FROM `voices`");
    $num = mysqli_num_rows($query);
    
    $list = $voices = [];
    
    for ($i=0; $i < $num; $i++) { $voices[] = mysqli_fetch_assoc($query); }
    $voices = array_reverse($voices);
    $voices = array_splice($voices, 0, 10, true);
    
    foreach($voices as $voiceinfo){
        if($voiceinfo['mode'] == 'private' && $voiceinfo['sender'] != $inlineuserid){ continue; }
        if(!$voiceinfo['accepted']){ continue; }
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

elseif($text == '❣️ محبوبترین ویس ها'){
    $list = $msgbtn = [];
    
    $query = mysqli_query($db, "SELECT * FROM `voices` ORDER BY `voices`.`usecount` DESC");
    $num = mysqli_num_rows($query);
    
    for ($i=0; $i < $num; $i++) {
        $voiceinfo = mysqli_fetch_assoc($query);
        if($voiceinfo['mode'] == 'private' && $voiceinfo['sender'] != $inlineuserid){ continue; }
        if(!$voiceinfo['accepted']){ continue; }
        $msgbtn[] = [['text'=>"❣️🎤 ".$voiceinfo['name'], 'switch_inline_query'=>$voiceinfo['name']]];
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


elseif($text == '🎤 افزودن ویس' or $text == '/start sendvoice'){
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
}

elseif($text && $user['step'] == 'sendvoice2' && $text !== $backbtn){
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
}

elseif($user['step'] == 'sendvoice3' && $text !== $backbtn){
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
    $vid = Forward('-1001169964092', $chat_id, $message_id);
    $vr = json_decode($vid, true);
    if($user['voicemode'] == 'public'){ $accepted_var = false; }else{ $accepted_var = true; }
    $id = strval(rand(11111,99999));
    $definedvoicename = $user['voicename'];
    $voicedburl = 'https://t.me/VoiceDatabaseOfOhPesar/'.strval($vr['result']['message_id']);
    $voicemsgid = $vr['result']['message_id'];
    $thevoicemode = $user['voicemode'];
    $db->query("INSERT INTO `voices` (`unique_id`, `accepted`, `name`, `url`, `sender`, `messageid`, `mode`, `usecount`) VALUES ('{$systemid}', '{$accepted_var}', '{$definedvoicename}', '$voicedburl', '$from_id', '$voicemsgid', '$thevoicemode', 0)");
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
    if(strpos($data, 'setsortby_') !== false){
        $mode = str_replace('setsortby_', '', $data);
        $userinline = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `user` WHERE `id` = '{$chatid}' LIMIT 1"));
        
        if($userinline['sortby'] == $mode){
            bot('answercallbackquery', [
                'callback_query_id' => $update->callback_query->id,
                'text' => "⚠️ تنظیمات نمایش از قبل بر روی این گزینه تنظیم بود",
                'show_alert' => false
            ]);
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
            'text'=>"⚙️ به تنظیمات ربات اوه پسر خوش آمدید! در این بخش میتوانید تعیین کنید که هنگامی که آیدی ربات را در چت مورد نظر وارد کردید، بر چه اساسی و چه ویس هایی برای شما به نمایش گذاشته شود 👇🏻",
            'reply_markup'=>json_encode([
                'inline_keyboard'=>[
                    [['text'=>$sortby['newest'].' جدیدترین ویس ها', 'callback_data'=>'setsortby_newest'], ['text'=>$sortby['oldest'].' قدیمیترین ویس ها', 'callback_data'=>'setsortby_oldest']],
                    [['text'=>$sortby['popularest'].' محبوبترین ویس ها', 'callback_data'=>'setsortby_popularest']],
                ],
            ])
        ]);
    }
    if(strpos($data, 'myvoicespage_') !== false){
        $pagenum = intval(str_replace('myvoicespage_', '', $data));
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
                ['text'=>'❌ حذف ویس', 'callback_data'=>'removebyuser_'.$user_voice_info['unique_id']],
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
    if(strpos($data, 'removebyuser_') !== false){
        $voice_unique_id = str_replace('removebyuser_', '', $data);
        $voiceinfo = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `voices` WHERE `unique_id` = '{$voice_unique_id}'"));
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
        $voiceinfo = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `voices` WHERE `unique_id` = '{$voice_unique_id}'"));
        $db->query("DELETE FROM `voices` WHERE `unique_id` = '{$voice_unique_id}' LIMIT 1");
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
        $db->query("UPDATE `voices` SET `accepted` = '1' WHERE `unique_id` = '{$voiceid}' LIMIT 1");
        $getvoice = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `voices` WHERE `unique_id` = '{$voiceid}'"));
        $voicesender = $getvoice['sender'];
        Bot('deletemessage', [
            'chat_id' => $chatid,
            'message_id' => $messageid,
        ]);
        $voicesender = intval($getvoice['sender']);
        $db->query("UPDATE `user` SET `sendvoice` = '0' WHERE `user`.`id` = $voicesender;");
        SendMessage($voicesender, 'ویس شما توسط مدیریت تایید شد. ✅');
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
        $getvoice = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `voices` WHERE `unique_id` = '{$voiceid}'"));
        SendMessage($getvoice['sender'], 'ویس شما توسط مدیریت رد شد. ❌');
        $voicesender = intval($getvoice['sender']);
        $db->query("UPDATE `user` SET `sendvoice` = '0' WHERE `user`.`id` = $voicesender;");
        $db->query("DELETE FROM `voices` WHERE `unique_id` = '{$voiceid}' LIMIT 1");
        
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
    $db->query("UPDATE `user` SET `step` = 'deletevoice1' WHERE `id` = '{$from_id}' LIMIT 1");
}

elseif($update->message->voice && $user['step'] == 'deletevoice1'){
    $voiceid = $update->message->voice->file_unique_id;
    $voiceinfo = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `voices` WHERE `unique_id` = '{$voiceid}'"));
    if(!$voiceinfo){
        SendMessage($chat_id, 'چنین ویسی در دیتابیس «اوه پسر» یافت نشد !');
        exit();
    }
    $voicename = $voiceinfo['name'];
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>"آیا مطمئن هستید که میخواهید ویس « $voicename » را حذف کنید؟",
        'reply_markup'=>json_encode(['keyboard'=>$yesnopanel ,'resize_keyboard'=>true])
    ]);
    $db->query("UPDATE `user` SET `step` = 'deletevoice2', `voicename` = '{$voiceid}' WHERE `id` = '{$from_id}' LIMIT 1");
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
        $db->query("UPDATE `user` SET `step` = 'none' WHERE `id` = '{$from_id}' LIMIT 1");
        exit();
    }
    $voiceid = $user['voicename'];
    $voiceinfo = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `voices` WHERE `unique_id` = '{$voiceid}'"));
    $voicename = $voiceinfo['name'];
    $db->query("DELETE FROM `voices` WHERE `unique_id` = '{$voiceid}' LIMIT 1");
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>"ویس « $voicename » با موفقیت حذف شد.",
        'reply_markup'=>json_encode(['keyboard'=>$adminpanel ,'resize_keyboard'=>true])
    ]);
    SendMessage($CONFIG['CHANNEL']['LOGID'], "ویس « $voicename » توسط ادمین $from_id با نام $first_name حذف شد.");
    $db->query("UPDATE `user` SET `step` = 'none' , `voicename` = 'none' WHERE `id` = '{$from_id}' LIMIT 1");
}

elseif($text == '✏️ ویرایش ویس' && in_array($from_id, $CONFIG['ADMINS'])){
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>'لطفا ویس مورد نظر را ارسال کنید :',
        'reply_markup'=>json_encode(['keyboard'=>$back ,'resize_keyboard'=>true])
    ]);
    $db->query("UPDATE `user` SET `step` = 'editvoice1' WHERE `id` = '{$from_id}' LIMIT 1");
}

elseif($update->message->voice && $user['step'] == 'editvoice1'){
    $voiceid = $update->message->voice->file_unique_id;
    $voiceinfo = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `voices` WHERE `unique_id` = '{$voiceid}'"));
    if(!$voiceinfo){
        SendMessage($chat_id, 'چنین ویسی در دیتابیس «اوه پسر» یافت نشد !');
        exit();
    }
    $voicename = $voiceinfo['name'];
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>"شما ویس « $voicename » را انتخاب کردید. لطفا از گزینه های زیر یک مورد را برای ویرایش انتخاب کنید 👇🏻",
        'reply_markup'=>json_encode(['keyboard'=>$editvoicepanel ,'resize_keyboard'=>true])
    ]);
    $db->query("UPDATE `user` SET `step` = 'editvoice2', `voicename` = '{$voiceid}' WHERE `id` = '{$from_id}' LIMIT 1");
}

elseif($text && $text !== $backbtn && $user['step'] == 'editvoice2'){
    $voiceid = $user['voicename'];
    $choices = [
        '✏️ ویرایش نام ویس',
        '✏️ ویرایش صدای ویس'  
    ];
    if(!in_array($text, $choices)){
        SendMessage($chat_id, 'لطفا فقط از دکمه های پایین یک گزینه را انتخاب کنید.');
        exit();
    }
    $voiceinfo = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `voices` WHERE `unique_id` = '{$voiceid}'"));
    $voicename = $voiceinfo['name'];
    if($text == $choices[0]){
        Bot('sendMessage',[
            'chat_id'=>$chat_id,
            'text'=>"لطفا نام جدید را برای ویس « $voicename » ارسال کنید :",
            'reply_markup'=>json_encode(['keyboard'=>$back ,'resize_keyboard'=>true])
        ]);
        $db->query("UPDATE `user` SET `step` = 'editvoice3', `voiceedit` = 'name' WHERE `id` = '{$from_id}' LIMIT 1");
    }elseif($text == $choices[1]){
        Bot('sendMessage',[
            'chat_id'=>$chat_id,
            'text'=>"لطفا ویس جدید جایگزین را برای ویس « $voicename » ارسال کنید :",
            'reply_markup'=>json_encode(['keyboard'=>$back ,'resize_keyboard'=>true])
        ]);
        $db->query("UPDATE `user` SET `step` = 'editvoice3', `voiceedit` = 'replace' WHERE `id` = '{$from_id}' LIMIT 1");
    }
}

elseif($user['step'] == 'editvoice3'){
    $voiceid = $user['voicename'];
    $voiceinfo = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `voices` WHERE `unique_id` = '{$voiceid}'"));
    if($update->message->voice && $user['voiceedit'] == 'replace'){
        $vid = Forward('-1001169964092', $chat_id, $message_id);
        $vr = json_decode($vid, true);
        $voicename = $voiceinfo['name'];
        $newurl = 'https://t.me/VoiceDatabaseOfOhPesar/'.strval($vr['result']['message_id']);
        $newmessageid = $vr['result']['message_id'];
        $voiceprimarykey = $voiceinfo['id'];
        $newvoiceuniqueid = $update->message->voice->file_unique_id;
        $db->query("UPDATE `voices` SET `url` = '{$newurl}', `messageid` = '{$newmessageid}', `unique_id` = '{$newvoiceuniqueid}' WHERE `id` = '{$voiceprimarykey}' LIMIT 1");
        Bot('sendMessage',[
            'chat_id'=>$chat_id,
            'text'=>"✅ ویس ارسالی شما، جایگزین ویس « $voicename » شد.",
            'reply_markup'=>json_encode(['keyboard'=>$adminpanel ,'resize_keyboard'=>true])
        ]);
        SendMessage($CONFIG['CHANNEL']['LOGID'], "ویس « $voicename » توسط ادمین $from_id با نام $first_name جایگزین ویس دیگری شد.");
    }elseif($text && $user['voiceedit'] == 'name'){
        $old_name = $voiceinfo['name'];
        $db->query("UPDATE `voices` SET `name` = '{$text}' WHERE `unique_id` = '{$voiceid}' LIMIT 1");
        Bot('sendMessage',[
            'chat_id'=>$chat_id,
            'text'=>"✅ نام ویس « $old_name » به نام « $text » تغییر پیدا کرد.",
            'reply_markup'=>json_encode(['keyboard'=>$adminpanel ,'resize_keyboard'=>true])
        ]);
        SendMessage($CONFIG['CHANNEL']['LOGID'], "نام ویس « $old_name » به نام « $text » توسط ادمین $from_id با نام $first_name تغییر پیدا کرد.");
    }
    $db->query("UPDATE `user` SET `step` = 'none', `voiceedit` = NULL WHERE `id` = '{$from_id}' LIMIT 1");
}

elseif($text == '🖥 آمار' && in_array($from_id, $CONFIG['ADMINS'])){
    // SendMessage($from_id, 'درحال بررسی...');
    $all_voices = $unaccepted_voice = $accepted_voice = $all_users = $private_voices = 0;
    
    $all_users = mysqli_num_rows(mysqli_query($db, "SELECT * FROM `user`"));
    
    $query = mysqli_query($db, "SELECT * FROM `voices`");
    $all_voices = mysqli_num_rows($query);

    for ($i=0; $i < $all_voices; $i++) {
        $thevoice = mysqli_fetch_assoc($query);
        if($thevoice['mode'] == 'private'){
            $private_voices++;
        }else{
            if($thevoice['accepted']){
                $accepted_voice++;
            }else{
                $unaccepted_voice++;
            }
        }
    }
    
    $sizeq = mysqli_query($db, "SHOW TABLE STATUS");  
    $dbsize = 0;  
    while($row = mysqli_fetch_assoc($sizeq)) {  
        $dbsize += $row["Data_length"] + $row["Index_length"];  
    }
    $decimals = 2;  
    $mbytes = number_format($dbsize/(1024*1024), $decimals);

    
    $admins = count($CONFIG['ADMINS']);
    SendMessage($from_id, "📌 آمار ربات اوه پسر درحالت حاضر به شرح زیر میباشد 👇🏻

👤 تعداد تمامی کاربران ربات : $all_users
🚨 تعداد ادمین های ربات : $admins

🎤 تعداد تمامی ویس ها : $all_voices
🔐 تعداد ویس های شخصی : $private_voices
✅ ویس های تایید شده : $accepted_voice
❌ ویس های تایید نشده : $unaccepted_voice

🗂 حجم کل دیتابیس : $mbytes مگابایت
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




elseif($text == '🗂 ویس های من' or $text == '/myvoices'){
    $page_limit = 10;
    $query = mysqli_query($db, "SELECT * FROM `voices` WHERE `sender` = '{$from_id}'");
    $num = mysqli_num_rows($query);
    
    
    
    
    if(!$num){
        SendMessage($chat_id, '⚠️ شما هیچ ویسی در ربات ثبت نکردید !');
        exit();
    }
    $MyVoicesKey = []; // To store 

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
        $MyVoicesKey[] = [
            ['text'=>$voiceemoji.' '.$user_voice_info['name'], 'switch_inline_query'=>$user_voice_info['name']],
            ['text'=>'❌ حذف ویس', 'callback_data'=>'removebyuser_'.$user_voice_info['unique_id']],
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


elseif($text == '💬 پیام همگانی' && in_array($chat_id, $CONFIG['ADMINS'])){
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>"لطفا پیام مورد نظر خود را ارسال کنید تا برای همه اعضا ارسال شود : (لطفا در ارسال پیام دقت کنید، این بخش فاقد تاییدیه میباشد و به محض ارسال پیام برای همه ارسال میشود)",
        'reply_markup'=>json_encode(['keyboard'=>$back ,'resize_keyboard'=>true])
    ]);
    $db->query("UPDATE `user` SET `step` = 'msg2all' WHERE `id` = '{$from_id}' LIMIT 1");
}

elseif($user['step'] == 'msg2all' && ($text !== $backbtn or strtolower($text) !== '/start')){
    $db->query("UPDATE `user` SET `step` = 'none' WHERE `id` = '{$from_id}' LIMIT 1");
    $query = mysqli_query($db, "SELECT * FROM `user`");
    $memberscount = mysqli_num_rows($query);
    
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>"درحال ارسال برای تمامی $memberscount ممبر... لطفا برای بهبود سرعت تا تکمیل فرایند ارسال کاری انجام ندهید!",
        'reply_markup'=>json_encode(['keyboard'=>$adminpanel ,'resize_keyboard'=>true])
    ]);
    for ($i=0; $i < $memberscount; $i++) { 
    	$u = mysqli_fetch_assoc($query);
    	SendMessage($u['id'], $text);
    }
    SendMessage($chat_id, 'پیام مورد نظر برای همه اعضای ربات ارسال شد. ✅');
    
}


elseif($text == '💬 فوروارد همگانی' && in_array($chat_id, $CONFIG['ADMINS'])){
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>"لطفا پیام مورد نظر خود را فوروارد کنید تا برای همه اعضا فوروارد شود : (لطفا در ارسال پیام دقت کنید، این بخش فاقد تاییدیه میباشد و به محض ارسال پیام برای همه ارسال میشود)",
        'reply_markup'=>json_encode(['keyboard'=>$back ,'resize_keyboard'=>true])
    ]);
    $db->query("UPDATE `user` SET `step` = 'forward2all' WHERE `id` = '{$from_id}' LIMIT 1");
}

elseif($user['step'] == 'forward2all' && ($text !== $backbtn or strtolower($text) !== '/start')){
    $db->query("UPDATE `user` SET `step` = 'none' WHERE `id` = '{$from_id}' LIMIT 1");
    $query = mysqli_query($db, "SELECT * FROM `user`");
    $memberscount = mysqli_num_rows($query);
    
    Bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>"درحال فوروارد برای تمامی $memberscount ممبر... لطفا برای بهبود سرعت تا تکمیل فرایند فوروارد کاری انجام ندهید!",
        'reply_markup'=>json_encode(['keyboard'=>$adminpanel ,'resize_keyboard'=>true])
    ]);
    for ($i=0; $i < $memberscount; $i++) { 
    	$u = mysqli_fetch_assoc($query);
    	Forward($u['id'], $from_id, $message_id);
    }
    SendMessage($chat_id, 'پیام مورد نظر برای همه اعضای ربات فوروارد شد. ✅');
}


elseif(!is_null($inline_text)){
    $inline_text = trim($inline_text);
    $results = [];
    $inlineuserid = $update->inline_query->from->id;
    $userinline = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `user` WHERE `id` = '{$inlineuserid}' LIMIT 1"));
    if(!$userinline){
        Bot('answerInlineQuery', [
            'inline_query_id' => $membercalls,
            'results' => json_encode($results),
            'switch_pm_text'=> 'برای استفاده از ربات باید ربات را استارت بزنید',
            'switch_pm_parameter'=> 'startforuse'
        ]);
        exit();
    }
    
    if($userinline['sortby'] == 'newest'){
        $querystring = "SELECT * FROM `voices` ORDER BY `voices`.`id` DESC";
    }elseif($userinline['sortby'] == 'popularest'){
        $querystring = "SELECT * FROM `voices` ORDER BY `voices`.`usecount` DESC";
    }else{
        $querystring = "SELECT * FROM `voices` ORDER BY `voices`.`id` ASC";
    }
    $query = mysqli_query($db, $querystring);
    $num = mysqli_num_rows($query);
    for ($i=0; $i < $num; $i++) {
    	$voiceinfo = mysqli_fetch_assoc($query);
        if((strtolower($voiceinfo['mode']) == 'private') && (intval($voiceinfo['sender']) !== intval($inlineuserid))){ continue; }
        if(!$voiceinfo['accepted']){ continue; }
        if(!(strpos(strtolower($voiceinfo['name']), strtolower($inline_text)) !== false) && strlen($inline_text) > 1){ continue; }
        
        $results[] = [
            'type' => 'voice',
            'id' => $voiceinfo['unique_id'],
            'voice_url' =>  $voiceinfo['url'],
            'title' => $voiceinfo['mode'] == 'private' ? '🔐 '.$voiceinfo['name'] : $voiceinfo['name'],
        ];
    }
    $results = array_splice($results, 0, 20, true);
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
    $voiceinfo = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `voices` WHERE `unique_id` = '{$vid}' LIMIT 1"));
    if(!$voiceinfo) $found = false;
    if($voiceinfo['mode'] == 'private' && intval($voiceinfo['sender']) !== intval($chat_id)){
        SendMessage($chat_id, '👀 اوه پسر متاسفم! این یه ویس شخصیه که توسط یکی از کاربرای ربات ثبت شده و تو نمیتونی ازش استفاده کنی');
        exit();
    }
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


elseif($update->chosen_inline_result){
    $voiceid = $update->chosen_inline_result->result_id;
    $db->query("UPDATE `voices` SET `usecount` = `usecount` + 1 WHERE `unique_id` = '{$voiceid}' LIMIT 1");
}

?>