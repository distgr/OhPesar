<?php

function MakeVoiceResponde($voiceinfo, $show_id){
    $voicename = $voiceinfo['mode'] == 'private' ? '🔐 '.$voiceinfo['name'] : $voiceinfo['name'];
    return [
        'type' => 'voice',
        'id' => $voiceinfo['unique_id'].'__'.base64_encode(rand()),
        'voice_url' =>  $voiceinfo['url'],
        'title' => $show_id ? '('.$voiceinfo['id'].') '.$voicename : $voicename,
    ];
}

function startsWith ($string, $startString)
{
    $len = strlen($startString);
    return (substr($string, 0, $len) === $startString);
}

function arraypos($text, $array){
    foreach ($array as $i) {
        if(strpos($text, $i) !== false){
            return true;
        }
    }
    return false;
}

function SearchFilter($voiceinfo, $userinline, $inlineuserid, $inline_text){
    $kwargs = ['-id'];
    if($userinline['badvoices'] == 0){
        if( IsBadWord($voiceinfo['name']) ) return false;
    }
    if((strtolower($voiceinfo['mode']) == 'private') && (intval($voiceinfo['sender']) !== intval($inlineuserid))){ return false; }
    elseif(!$voiceinfo['accepted'] && strtolower($voiceinfo['mode']) == 'public'){ return false; }
    if((!(strpos(strtolower($voiceinfo['name']), strtolower($inline_text)) !== false) && strlen($inline_text) > 1) && !arraypos($inline_text, $kwargs)){ return false; }
    return true;
}

if(!is_null($inline_text)){
    $show_id = false;
    $order = true;
    $start_time = microtime(true);
    $inline_text = trim($inline_text);
    $results = [];
    $inlineuserid = $update->inline_query->from->id;
    $inlineusername = $update->inline_query->from->username;
    $userinline = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `user` WHERE `id` = '{$inlineuserid}' LIMIT 1"));
    if(!$userinline){
        Bot('answerInlineQuery', [
            'inline_query_id' => $membercalls,
            'results' => json_encode($results),
            'switch_pm_text'=> 'برای استفاده از ربات باید ربات را استارت بزنید',
            'switch_pm_parameter'=> 'startforuse',
            'is_personal'=> true,
            'cache_time'=> 1
        ]);
        mysqli_close($db);
        exit();
    }

    if(!$userinline['username'] or $user['username'] !== $fixusername){
        if($inlineusername){
            $db->query("UPDATE `user` SET `username` = '{$inlineusername}' WHERE `id` = '{$inlineuserid}' LIMIT 1");
        }
    }    

    $querystring = "SELECT * FROM `voices`";
    
    if(strpos($inline_text, '-showid') !== false){
        $inline_text = trim(str_replace('-showid', '', $inline_text));
        $show_id = true;
    }
    
    if(strpos($inline_text, '-id ') !== false){
        $inline_vid = str_replace('-id ', '', $inline_text);
        $querystring = "SELECT * FROM `voices` WHERE `id` = '{$inline_vid}' LIMIT 1";
        $order = false;
    }

    elseif(strpos($inline_text, '-private') !== false){
        $inline_text = trim(str_replace('-private', '', $inline_text));
        $querystring = "SELECT * FROM `voices` WHERE `mode` = 'private'";
    }

    elseif(strpos($inline_text, '-public') !== false){
        $inline_text = trim(str_replace('-public', '', $inline_text));
        $querystring = "SELECT * FROM `voices` WHERE `mode` = 'public'";
    }

    elseif(strpos($inline_text, '-me') !== false){
        $inline_text = trim(str_replace('-me', '', $inline_text));
        $querystring = "SELECT * FROM `voices` WHERE `sender` = '{$inlineuserid}'";
    }

    elseif(strpos($inline_text, '-latest') !== false){
        $inline_text = trim(str_replace(('-latest'), '', $inline_text));
        $latestid = $userinline['latestvoice'];
        $querystring = "SELECT * FROM `voices` WHERE `unique_id` = '{$latestid}' LIMIT 1";
        $order = false;
    }

    if($order){
        if($userinline['sortby'] == 'newest'){
            $querystring .= " ORDER BY `voices`.`id` DESC";
        }elseif($userinline['sortby'] == 'popularest'){
            $querystring .= " ORDER BY `voices`.`usecount` DESC";
        }else{
            $querystring .= " ORDER BY `voices`.`id` ASC";
        }
    }
    
    $query = mysqli_query($db, $querystring);
    if(mysqli_num_rows($query) == 1){
        $voiceinfo = mysqli_fetch_assoc($query);
        if(SearchFilter($voiceinfo, $userinline, $inlineuserid, $inline_text))
            $results[] = MakeVoiceResponde($voiceinfo, $show_id);
    }else{
        while ($voiceinfo = mysqli_fetch_assoc($query)) {
            if(SearchFilter($voiceinfo, $userinline, $inlineuserid, $inline_text))
                $results[] = MakeVoiceResponde($voiceinfo, $show_id);
        }
    }
    $result_count = count($results);
    
    $show_limit = 20;
    
    $offset = $update->inline_query->offset;
    
    if($offset == ""){
        $from_offest = 0;
        $next_offset = $show_limit;
    }else{
        $offset_explode = explode(':', $offset);
        $from_offest = intval($offset_explode[1]);
        $next_offset = $from_offest+$show_limit;
    }
    
    $results = array_splice($results, $from_offest, $show_limit, true);

    $dataval = [
        'inline_query_id' => $membercalls,
        'results' => json_encode($results),
        'is_personal'=> true,
        'cache_time'=> 1,
    ];
    if(count($results) >= 10)
        $dataval['next_offset'] = "$from_offest:$next_offset";
    
    if($results == []){
        $dataval['switch_pm_text'] = 'نتیجه خاصی پیدا نشد';
        $dataval['switch_pm_parameter'] = 'noresult';
    }
    elseif(strlen($inline_text) < 1 && $order){
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
    if($addtofav && $results !== []){
        $dataval['switch_pm_text'] = "ویس انتخابی به علاقه مندی های شما اضافه خواهد شد";
        $dataval['switch_pm_parameter'] = 'start';
    }
    Bot('answerInlineQuery', $dataval);
}