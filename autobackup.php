<?php

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

function backup_tables($host, $user, $pass, $dbname, $tables = '*') {
    $link = mysqli_connect($host,$user,$pass, $dbname);

    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
        exit;
    }

    mysqli_query($link, "SET NAMES 'utf8'");


    $tables = array();
    $result = mysqli_query($link, 'SHOW TABLES');
    while($row = mysqli_fetch_row($result))
    {
        $tables[] = $row[0];
    }


    $return = '';
    foreach($tables as $table)
    {
        $result = mysqli_query($link, 'SELECT * FROM '.$table);
        $num_fields = mysqli_num_fields($result);
        $num_rows = mysqli_num_rows($result);

        $return.= 'DROP TABLE IF EXISTS '.$table.';';
        $row2 = mysqli_fetch_row(mysqli_query($link, 'SHOW CREATE TABLE '.$table));
        $return.= "\n\n".$row2[1].";\n\n";
        $counter = 1;

        for ($i = 0; $i < $num_fields; $i++) 
        {
            while($row = mysqli_fetch_row($result))
            {   
                if($counter == 1){
                    $return.= 'INSERT INTO '.$table.' VALUES(';
                } else{
                    $return.= '(';
                }

                for($j=0; $j<$num_fields; $j++) 
                {
                    $row[$j] = addslashes($row[$j]);
                    $row[$j] = str_replace("\n","\\n",$row[$j]);
                    if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
                    if ($j<($num_fields-1)) { $return.= ','; }
                }

                if($num_rows == $counter){
                    $return.= ");\n";
                } else{
                    $return.= "),\n";
                }
                ++$counter;
            }
        }
        $return.="\n\n\n";
    }

    $fileName = 'ohpesar-dbbackup-'.time().'-'.(md5(implode(',',$tables))).'.zip';
    $handle = fopen($fileName,'w+');
    fwrite($handle,$return);
    if(fclose($handle)){
        $document = 'https://server-me.ir/bots/theohpesar/'.$fileName;
        $db = $link;
        $all_voices = $unaccepted_voice = $accepted_voice = $all_users = $private_voices = 0;
    
        $all_users = mysqli_num_rows(mysqli_query($db, "SELECT * FROM `user`"));
        
        $query = mysqli_query($db, "SELECT * FROM `voices`");
        $all_voices = mysqli_num_rows($query);
    
        for ($i=0; $i < $all_voices; $i++) {
            $thevoice = mysqli_fetch_assoc($query);
            if($thevoice['mode'] == 'private'){
                $private_voices++;
            }else{
                if($thevoice['accepted']){
                    $accepted_voice++;
                }else{
                    $unaccepted_voice++;
                }
            }
        }
        
        
        Bot('SendDocument',['chat_id'=>'-1001491735326', 'document'=>$document,'caption'=>"Backup from OhPesar Database
        
Bot users : $all_users

Bot Voices : $all_voices
Private Voices : $private_voices
Accepted Voices : $accepted_voice
Unaccepted Voices : $unaccepted_voice"]);
        sleep(1);
        unlink($fileName);
        exit;
    }
}

backup_tables('localhost', $CONFIG['DATABASE']['USERNAME'], $CONFIG['DATABASE']['PASSWORD'], $CONFIG['DATABASE']['DBNAME'], '*');