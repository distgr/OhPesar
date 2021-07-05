<?php

if($callback_query){
    $data = $callback_query->data;
    if($data == 'pendingmode'){
        bot('answercallbackquery', [
            'callback_query_id' => $update->callback_query->id,
            'text' => "🕐 این ویس درحالت بررسی قرار دارد و هنوز توسط تایید نشده است. ویس شما تا زمانی که تایید نشود قابل استفاده نمیباشد.",
            'show_alert' => true
        ]);
    }
}