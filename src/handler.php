<?php

$update = json_decode(file_get_contents('php://input'));
$channel = $CONFIG['CHANNEL']['OFFICIAL'];
if(isset($update->message)){
    $message = $update->message;
    $inline = $update->inline_query;
    $inline_text = $update->inline_query->query;
    $membercalls = $update->inline_query->id;
	$text = $message->text;
	$tc = $message->chat->type;
    $chat_id = $message->chat->id;
	$from_id = $message->from->id;
	$message_id = $message->message_id;
    $first_name = $message->from->first_name;
    $last_name = $message->from->last_name;
    $username = $message->from->username;
}
if(isset($update->callback_query)){
    $callback_query = $update->callback_query;
	$databack = $callback_query->data;
	$tc = $callback_query->message->chat->type;
    $chatid = $callback_query->message->chat->id;
	$fromid = $callback_query->from->id;
	$messageid = $callback_query->message->message_id;
    $firstname = $callback_query->from->first_name;
    $lastname = $callback_query->from->last_name;
    $cusername = $callback_query->from->username;
    $membercall = $callback_query->id;
}
if(isset($update->inline_query)){
    $inline = $update->inline_query;
    $inline_text = $inline->query;
    $membercalls = $inline->id;
    $id_from = $inline->from->id;
}