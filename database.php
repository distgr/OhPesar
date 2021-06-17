<?php
$CONFIG = json_decode(file_get_contents('config.json'), true);

$db = mysqli_connect('localhost', $CONFIG['DATABASE']['USERNAME'], $CONFIG['DATABASE']['PASSWORD'], $CONFIG['DATABASE']['DBNAME']);

if ($db->query("SELECT * FROM `user`") == false) {
    $x = mysqli_query($db, "CREATE TABLE `user` (
        `id` bigint(10) NOT NULL PRIMARY KEY,
        `step` varchar(50) NOT NULL
        )"
	);
}
if ($db->query("SELECT * FROM `voices`") == false) {
    mysqli_query($db, "CREATE TABLE `voices` (
        `id` char(250) NOT NULL PRIMARY KEY,
        `accepted` char(200) DEFAULT false,
        `name` char(200) NOT NULL,
        `sender` char(200) NOT NULL,
        `messageid` char(200) NOT NULL,
        `mode` char(200) NOT NULL
        )"
    );
}