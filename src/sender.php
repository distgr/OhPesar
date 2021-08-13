<?php
// 1 Minutes cron job

header('Content-Type: application/json');

$CONFIG = json_decode(file_get_contents('config.json'), true);

define('API_KEY', $CONFIG['TOKEN']);

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

if(!is_file('sendermeta.json')){
    file_put_contents('sendermeta.json', json_encode(['list'=>[]]));
}

$meta = json_decode(file_get_contents('sendermeta.json'), true);

function is_send($id){
    global $meta;
    foreach($meta['list'] as $m=>$v){
        if($m == $id)
            return true;
    }
    return false;
}

if(isset($_GET['q'])){

    $q = $_GET['q'];
    $data = $_GET['data'];
    $type = $_GET['type'];

    if($q == 'add' && isset($data) && isset($type)){
        $id = rand(111111, 999999);
        $meta['list'][$id] = [
            'send'=> 0,
            'type'=>$type,
            'data'=> $data
        ];
        file_put_contents('sendermeta.json', json_encode($meta));
        echo json_encode(['ok'=>true]);
        exit();
    }

    elseif($q == 'list'){
        $pending_list = [];
        foreach($meta['list'] as $p=>$val){
            $pending_list[] = $p;
        }
        echo json_encode(['ok'=>true, 'list'=>$pending_list]);
        exit();
    }

    elseif($q == 'remove' && isset($data)){

        if(is_send($data)){
            unset($meta['list'][$data]);
            file_put_contents('sendermeta.json', json_encode($meta));
            echo json_encode(['ok'=>true]);
        }else{
            echo json_encode(['ok'=>false, 'text'=>'sendid is not exist']);
        }
        exit();
    }

    elseif($q == 'get' && isset($data)){
        if(is_send($data)){
            echo json_encode([
                'ok'=>true,
                'send'=>$meta['list'][$data]['send'],
                'type'=>$meta['list'][$data]['type'],
                'data'=>$meta['list'][$data]['data']
            ]);
        }else{
            echo json_encode(['ok'=>false, 'text'=>'sendid is not exist']);
        }
        exit();
    }
}

// send
if($meta['list'] == []){
    exit();
}

foreach($meta['list'] as $id=>$val){
    $send_part = 50;
    include('database.php');
    include('buttons.php');

    $query = mysqli_query($db, "SELECT * FROM `user`");
    $memberscount = mysqli_num_rows($query);

    $members = [];

    for ($i=0; $i < $memberscount; $i++) { 
    	$u = mysqli_fetch_assoc($query);
        $members[] = $u['id'];
    }
    
    $to_send = array_splice($members, $val['send'], $send_part);
    $val['send'] = $val['send']+$send_part;

    foreach($to_send as $u){
        $data_val = str_replace('[*USER*]', $u, $val['data']);
        Bot($val['type'], json_decode($data_val, true));
    }

    if($val['send'] >= $memberscount ){

        unset($meta['list'][$id]);
        file_put_contents('sendermeta.json', json_encode($meta));

        $to_echo = ['ok'=>true, 'complete'=>true];
    }else{
        $meta['list'][$id] = $val;

        file_put_contents('sendermeta.json', json_encode($meta));
        $to_echo = ['ok'=>true];
    }
}

echo json_encode($to_echo);
mysqli_close($db);