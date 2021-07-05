<?php

if(!is_null($inline_text)){
    $start_time = microtime(true);
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
    while ($voiceinfo = mysqli_fetch_assoc($query)) {
        if($userinline['badvoices'] == 0){
            if( IsBadWord($voiceinfo['name']) ) continue;
        }
        if((strtolower($voiceinfo['mode']) == 'private') && (intval($voiceinfo['sender']) !== intval($inlineuserid))){ continue; }
        elseif(!$voiceinfo['accepted'] && strtolower($voiceinfo['mode']) == 'public'){ continue; }
        if(!(strpos(strtolower($voiceinfo['name']), strtolower($inline_text)) !== false) && strlen($inline_text) > 1){ continue; }
        $results[] = [
            'type' => 'voice',
            'id' => $voiceinfo['unique_id'],
            'voice_url' =>  $voiceinfo['url'],
            'title' => $voiceinfo['mode'] == 'private' ? '🔐 '.$voiceinfo['name'] : $voiceinfo['name'],
        ];
    }
    $result_count = count($results);
    $results = array_splice($results, 0, 20, true);
    $dataval = [
        'inline_query_id' => $membercalls,
        'results' => json_encode($results)
    ];
    if($results == []){
        $dataval['switch_pm_text'] = 'نتیجه خاصی پیدا نشد';
        $dataval['switch_pm_parameter'] = 'noresult';
    }
    elseif(strlen($inline_text) < 1){
        $dataval['switch_pm_text'] = 'ارسال ویس جدید';
        $dataval['switch_pm_parameter'] = 'sendvoice';
    }
    elseif(!in_array('switch_pm_text', $dataval)){
        $time_end = microtime(true);
        $wait = round($time_end - $start_time, 4);
        if($result_count > 10){
            $dataval['switch_pm_text'] = "نتیجه جستوجو $result_count ویس در $wait ثانیه";
            $dataval['switch_pm_parameter'] = 'start';
        }
        
    }
    Bot('answerInlineQuery', $dataval);
}