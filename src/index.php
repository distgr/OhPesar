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
// if (!$ok) die();

ob_start();
// error_reporting(0);
date_default_timezone_set('Asia/Tehran');
$CONFIG = json_decode(file_get_contents('config.json'), true);

$important_inc = ['handler.php', 'functions.php', 'buttons.php', 'database.php'];
foreach($important_inc as $inc){ include($inc); }


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

if(!is_file('badwords.json')){
    file_put_contents('badwords.json', file_get_contents('https://raw.githubusercontent.com/amirshnll/Persian-Swear-Words/master/data.json'));
}

if(isset($from_id))
    $user = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `user` WHERE `id` = '{$from_id}' LIMIT 1"));
elseif(isset($fromid))
    $user = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `user` WHERE `id` = '{$fromid}' LIMIT 1"));
    
if (!$user) {
    $db->query("INSERT INTO `user` (`id`, `step`) VALUES ('{$from_id}', 'none')");
}

if(in_array($from_id, $CONFIG['ADMINS'])){
        $home[] = [['text'=>"📍 پنل مدیریت"]];
}

foreach(Recursive('core') as $to_inc){ include ($to_inc); }

?>