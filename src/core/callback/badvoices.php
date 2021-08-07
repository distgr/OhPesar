<?php

if($callback_query){
    $data = $callback_query->data;

    if($data == 'showbadvoices'){
        if($user['badvoices'] == 1){
            $modetype = 'روشن';
            $btnchange = '✅ '.$modetype;
        }else{
            $modetype = 'خاموش';
            $btnchange = '❌ '.$modetype;
        }
        Bot('EditMessageText',[
            'chat_id'=>$chatid,
            'message_id'=> $messageid,
            'text'=>"⚙️ به تنظیمات نمایش ویس های نامناسب خوش آمدید.

    ❔ ویس های نامناسب چیست؟ برخی از ویس های ثبت شده توسط کاربران دارای محتوا و نام های نامناسب، زشت و یا حتی +18 هستند که برخی از کاربران قادر به نمایش این نوع ویس ها نیستند. درصورتی که میخواهید این ویس ها برای شما به طور کلی در ربات نمایش داده نشوند، میتوانید این حالت را خاموش کنید.

    این حالت درحال حاضر برای شما $modetype است.",
            'reply_markup'=>json_encode([
                'inline_keyboard'=>[
                    [['text'=>$btnchange, 'callback_data'=>'changemode_badvoice']],
                ],
            ])
        ]);
        mysqli_close($db);
        exit();
    }

    if($data == 'changemode_badvoice'){
        
        if($user['badvoices'] == 0){
            $mode = 1;
            $modetype = 'روشن';
            $btnchange = '✅ '.$modetype;
        }else{
            $mode = 0;
            $modetype = 'خاموش';
            $btnchange = '❌ '.$modetype;
        }
        $db->query("UPDATE `user` SET `badvoices` = '{$mode}' WHERE `user`.`id` = $chatid;");
        Bot('EditMessageText',[
            'chat_id'=>$chatid,
            'message_id'=> $messageid,
            'text'=>"⚙️ به تنظیمات نمایش ویس های نامناسب خوش آمدید.

    ❔ ویس های نامناسب چیست؟ برخی از ویس های ثبت شده توسط کاربران دارای محتوا و نام های نامناسب، زشت و یا حتی +18 هستند که برخی از کاربران قادر به نمایش این نوع ویس ها نیستند. درصورتی که میخواهید این ویس ها برای شما به طور کلی در ربات نمایش داده نشوند، میتوانید این حالت را خاموش کنید.

    این حالت درحال حاضر برای شما $modetype است.",
            'reply_markup'=>json_encode([
                'inline_keyboard'=>[
                    [['text'=>$btnchange, 'callback_data'=>'changemode_badvoice']],
                ],
            ])
        ]);
        mysqli_close($db);
        exit();
    }
}