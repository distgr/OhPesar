<?php

if($callback_query){
    $data = $callback_query->data;
    if(strpos($data, 'unsend_') !== false){
        $unsend_data = str_replace('unsend_', '', $data);
        $unsend_data = explode('__', $unsend_data);
        $msg_id_unsend = $unsend_data[0];
        $chat_id_unsend = $unsend_data[1];
        Bot('deletemessage', [
            'chat_id' => $chat_id_unsend,
            'message_id' => $msg_id_unsend,
        ]);
        bot('answercallbackquery', [
            'callback_query_id' => $update->callback_query->id,
            'text' => "پیام برای کاربر پاک شد!",
            'show_alert' => false
        ]);
        Bot('deletemessage', [
            'chat_id' => $chatid,
            'message_id' => $messageid,
        ]);
    }
}