<?php

if($text == '📣 لیست ارسال' && in_array($from_id, $CONFIG['ADMINS'])){
    $listtosend = json_decode(file_get_contents($CONFIG['SERVERURL'].'sender.php?q=list'), true)['list'];
    if($listtosend == []){
        SendMessage($from_id, "❗️ هیچ پیامی در لیست ارسال ربات وجود ندارد.");
        mysqli_close($db);
        exit();
    }
    $listofsend = '';

    $query = mysqli_query($db, "SELECT * FROM `user`");
    $memberscount = mysqli_num_rows($query);
    
    foreach($listtosend as $l){
        $sendinfo = json_decode(file_get_contents($CONFIG['SERVERURL'].'sender.php?q=get&data='.$l), true);
        
        if($sendinfo['type'] == 'ForwardMessage') $sendtype = 'فوروارد همگانی'; else $sendtype = 'پیام همگانی';
        if(count($listtosend) > 1) $listofsend .= "\n〰️〰️〰️〰️〰️〰️〰️\n";
        
        $sendscount = $sendinfo['send'];
        $sendparts = $sendscount/50;
        if($sendparts >= 1)
            $mintoend = round($memberscount/(50*$sendparts));
        else
            $mintoend = round($memberscount/50);

        $listofsend .= "📎 $sendtype / شناسه : $l
👤 تعداد ارسال شده تا کنون : $sendscount
🕔 زمان تقریبی اتمام : کمتر از $mintoend دقیقه دیگر
❌ لغو ارسال : /cancelsend_$l";

    }
    SendMessage($from_id, "🔖 لیست ارسال ربات اوه پسر به شرح زیر میباشد :\n$listofsend");
    SendMessage($CONFIG['CHANNEL']['LOGID'], "آمار ارسال ربات توسط $from_id با نام $first_name گرفته شد.");
}

if((strpos($text, '/cancelsend_') !== false) && in_array($from_id, $CONFIG['ADMINS'])){
    $id = str_replace('/cancelsend_', '', $text);
    $responde = json_decode(file_get_contents($CONFIG['SERVERURL'].'sender.php?q=remove&data='.$id), true);
    if(!$responde['ok']){
        SendMessage($from_id, "❗️ چنین ارسالی با این شناسه در لیست ارسال وجود ندارد.");
        mysqli_close($db);
        exit();
    }
    SendMessage($from_id, "✅ ارسال لغو شد.");
}