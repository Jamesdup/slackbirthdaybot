<?php
    $config = parse_ini_file("config.ini");

    $server = $config['servername'];
    $user = $config['username'];
    $password = $config['password'];
    $dbname = $config['dbname'];
    $currentDate = Date("Y-m-d");
    
    $conn = new mysqli($GLOBALS['server'], $GLOBALS['user'], $GLOBALS['password'], $GLOBALS['dbname']);
    $stmtGetBirthday = $conn->prepare("SELECT name, birthdayMessage FROM birthdayBot WHERE dob=?");
    $date = $currentDate;
    $stmtGetBirthday->bind_param("s", $date);
    $stmtGetBirthday->execute();
    $resultGetBirthday = $stmtGetBirthday->get_result();
    
    $stmtUpdateYr = $conn->prepare("UPDATE birthdayBot SET dob = DATE_ADD(dob, INTERVAL 1 YEAR) WHERE dob=?");
    $stmtUpdateYr->bind_param("s",$date);
    
    
    
    $stmt = $conn->prepare("SELECT webhook FROM webhooktb");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $liveWebhookURL = $row['webhook'];
    $stmt->close();
    
    if($resultGetBirthday->num_rows > 0){
        while($rowGetBirthday = $resultGetBirthday->fetch_assoc()){
        echo " :partyingface: Happy birthday ". $rowGetBirthday['name'] ." :partyingface: ".$rowGetBirthday['birthdayMessage']." ";
       $message = array('payload' => json_encode(array('text' => ' :partying_face: Happy birthday '. $rowGetBirthday['name'].'  :partying_face: '. $rowGetBirthday['birthdayMessage'].' ')));
       $c = curl_init($liveWebhookURL);
       curl_setopt($c, CURLOPT_SSL_VERIFYPEER, true);
       curl_setopt($c, CURLOPT_POST, true);
       curl_setopt($c, CURLOPT_POSTFIELDS, $message);
       curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
       curl_exec($c); 
       curl_close($c); 
       $stmtUpdateYr->execute();
        }
        
    }
    
    $stmtGetBirthday->close();
    $stmtUpdateYr->close();
?>