<?php
error_reporting(0);
date_default_timezone_set('Asia/Tehran');
if (!file_exists('madeline.php')) {copy('https://phar.madelineproto.xyz/madeline.php', 'madeline.php');}
define('MADELINE_BRANCH', 'deprecated');
include 'madeline.php';

$settings = ['logger'=>['logger'=>0],'app_info'=> ['api_id'=>0,'api_hash'=> '']];
$MadelineProto = new \danog\MadelineProto\API('session.madeline',$settings);
$MadelineProto->start();
if(isset($_GET['id'])){
    $info = $MadelineProto->get_info(str_replace('@',null,$_GET['id']));
    if($info['type'] == 'user'){
        $name = $info['User']['first_name'];
        $user = $info['User']['username'];
        $id = $info['User']['id'];
        $kind = str_replace('userStatus',null,$info['User']['status']["_"]);
    if($kind == "Offline"){
        $time = date('Y/m/d H:i:s',$info['User']['status']['expires']);
        echo json_encode(["result"=>["name"=>"$name","username"=>"@$user","userid"=>"$id","status"=>"$kind","last_seen"=>"$time"]],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
    }else{
        echo json_encode(["result"=>["name"=>"$name","username"=>"@$user","userid"=>"$id","status"=>"$kind"]],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
    }
    }else{
        echo 0;
    }
}