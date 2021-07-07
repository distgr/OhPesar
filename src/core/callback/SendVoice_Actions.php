<?php

if($callback_query){
    $data = $callback_query->data;
    
    $public_text = '⚙️ به تنظیمات دکمه "ارسال ویس برای دیگران" خوش آمدید.

❔ این تنظیم برای چیست؟ وقتی شما ویسی را از ربات برای خود ربات ارسال میکنید، ربات نام ویس را به شما نمایش میدهد و دکمه ای تحت عنوان ارسال ویس برای دیگران را برای شما قرار میدهد که با استفاده از آن دکمه میتوانید آن ویس را برای دوستانتان ارسال کنید. همچنین در بخش "ویس های من" اگر روی نام ویس کلیک کنید، میتوانید آن ویس را برای دوستانتان ارسال کنید. در این بخش میتوانید این مورد را تنظیم کنید که طرز عملکرد آن دکمه به چه شکل باشد. اگر عملکرد دکمه به شکل "ارسال با نام ویس" باشد، ربات نام ویس را جلوی آیدی ربات قرار میدهد که ممکن هست ویس های دیگری با همچین نامی وجود داشته باشند و باعث دریافت چند جواب شود، اما درصورتی که آن را بر اساس آیدی تنظیم کنید، ربات آیدی ویس را فیلتر میکند و جلوی آیدی ربات قرار میدهد که به طور دقیق فقط میتوانید بدون وجود ویس های مشابه به آن ویس دسترسی پیدا کنید.';

    if($data == 'sendvoiceaction'){
        $userinline = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `user` WHERE `id` = '{$chatid}' LIMIT 1"));

        $changebtn = ['byname'=>'✅ بر اساس نام', 'byid'=>'✅ بر اساس آیدی'][$userinline['sendvoiceaction']];

        Bot('EditMessageText',[
            'chat_id'=>$chatid,
            'message_id'=> $messageid,
            'text'=>$public_text,
            'reply_markup'=>json_encode([
                'inline_keyboard'=>[
                    [['text'=>$changebtn, 'callback_data'=>'changesendvoiceresponde']],
                ],
            ])
        ]);
        exit();
    }

    if($data == 'changesendvoiceresponde'){
        $userinline = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `user` WHERE `id` = '{$chatid}' LIMIT 1"));
        if($userinline['sendvoiceaction'] == 'byname'){ $vaction = 'byid'; }else{ $vaction = 'byname'; }

        $db->query("UPDATE `user` SET `sendvoiceaction` = '{$vaction}' WHERE `user`.`id` = $chatid;");
        $userinline = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `user` WHERE `id` = '{$chatid}' LIMIT 1"));
    
        $changebtn = ['byname'=>'✅ بر اساس نام', 'byid'=>'✅ بر اساس آیدی'][$userinline['sendvoiceaction']];

        Bot('EditMessageText',[
            'chat_id'=>$chatid,
            'message_id'=> $messageid,
            'text'=>$public_text,
            'reply_markup'=>json_encode([
                'inline_keyboard'=>[
                    [['text'=>$changebtn, 'callback_data'=>'changesendvoiceresponde']],
                ],
            ])
        ]);

        exit();
    }
}