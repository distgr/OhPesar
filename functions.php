<?php
function Getme(){
    return json_decode(file_get_contents('https://api.telegram.org/bot'.API_KEY.'/getMe'),true)['result'];
}
function SendMessage($chat_id, $text, $mode='MarkDown', $reply=null, $keyboard=null){
	Bot('sendMessage',[
'chat_id'=>$chat_id,
'text'=>$text,
  'reply_markup'=>$keyboard
	 ]);
}
function SendVoice($chat_id, $voice, $markup=null, $caption=null){
    return Bot('sendVoice',[
        'chat_id'=> $chat_id,
        'voice'=> $voice,
        'reply_markup'=>$markup,
        'caption'=> $caption
    ]);
}
function DeleteMessage($chat_id, $message_id)
{
    Bot('deletemessage', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
    ]);
}
function EditMessage($chatid, $msgid, $text, $mod='MarkDown', $keyboard = null){
    Bot('EditMessageText', ['chat_id'=>$chatid,'message_id'=>$msgid,'text'=>$text,'parse_mode'=>$mod,'reply_markup'=>$keyboard]);
}
function SendPhoto($chat_id, $photo, $keyboard, $caption , $rep){
	Bot('SendPhoto', ['chat_id' => $chat_id, 'photo' => $photo, 'caption' => $caption, 'reply_to_message_id'=>$rep, 'reply_markup' => $keyboard]);
}
function SendDocument($chat_id,$document,$caption,$mode,$keyboard){
    Bot('SendDocument',['chat_id'=>$chat_id,'document'=>$document,'caption'=>$caption,'parse_mode'=>$mode,'reply_markup'=>$keyboard]);
}
function Forward($chat_id,$from_id,$massege_id){
    return Bot('ForwardMessage',['chat_id'=>$chat_id,'from_chat_id'=>$from_id,'message_id'=>$massege_id]);
}
function sendaction($chat_id, $action){
	Bot('sendchataction',['chat_id'=>$chat_id,'action'=>$action]);
}
function sendvideo($chat_id, $video, $cap, $mods, $key , $msg){
	Bot('sendvideo',['chat_id'=>$chat_id,'video'=>$video,'caption'=>$cap,'parse_mode'=>$mods,'reply_to_message_id'=>$msg,'reply_markup'=>$key]);
}
function getChatMember($channel, $id = ""){
    $forchannel = json_decode(file_get_contents("https://api.telegram.org/bot".API_KEY."/getChatMember?chat_id=@$channel&user_id=".$id));
    $tch = $forchannel->result->status;

     if($tch == 'member' or $tch == 'creator' or $tch == 'administrator'){
         return true;
     }else{
         return false;
     }
}

function UpdateUser(){
    global $user, $from_id;
    file_put_contents("data/users/$from_id.json", json_encode($user));
}
function sortandscan($dir) {
    $ignored = array('.', '..', '.svn', '.htaccess');

    $files = array();    
    foreach (scandir($dir) as $file) {
        if (in_array($file, $ignored)) continue;
        $files[$file] = filemtime($dir . '/' . $file);
    }

    arsort($files);
    $files = array_keys($files);

    return ($files) ? $files : false;
}