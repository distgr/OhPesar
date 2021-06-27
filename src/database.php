<?php
$CONFIG = json_decode(file_get_contents('config.json'), true);

$db = mysqli_connect('localhost', $CONFIG['DATABASE']['USERNAME'], $CONFIG['DATABASE']['PASSWORD'], $CONFIG['DATABASE']['DBNAME']);

if ($db->query("SELECT * FROM `user`") == false) mysqli_query($db, file_get_contents('database/user_initial.sql'));
if ($db->query("SELECT * FROM `voices`") == false) mysqli_query($db, file_get_contents('database/voices_initial.sql'));