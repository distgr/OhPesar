<?php

if($text == '🖥 آمار' && in_array($from_id, $CONFIG['ADMINS'])){
    $all_voices = $unaccepted_voice = $accepted_voice = $all_users = $private_voices = 0;
    
    $all_users = number_format(mysqli_num_rows(mysqli_query($db, "SELECT * FROM `user`")));
    
    $query = mysqli_query($db, "SELECT * FROM `voices`");
    $all_voices = mysqli_num_rows($query);

    for ($i=0; $i < $all_voices; $i++) {
        $thevoice = mysqli_fetch_assoc($query);
        if($thevoice['mode'] == 'private'){
            $private_voices++;
        }else{
            if($thevoice['accepted']) $accepted_voice++; else $unaccepted_voice++;
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

🗂 حجم کل دیتابیس : $mbytes مگابایت");
    SendMessage($CONFIG['CHANNEL']['LOGID'], "آمار ربات توسط $from_id با نام $first_name گرفته شد.");
}