<?php

$config = parse_ini_file("config.ini");

$server = $config['servername'];
$user = $config['username'];
$password = $config['password'];
$dbname = $config['dbname'];

$currentDate = Date("Y-m-d");

function insertRecord(){ 
$conn = new mysqli($GLOBALS['server'], $GLOBALS['user'], $GLOBALS['password'], $GLOBALS['dbname']);
$stmtInsertRecord = $conn->prepare("INSERT INTO birthdayBot (name, dob) VALUES (?,?)");

$name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
$dob = filter_var($_POST['dob'], FILTER_SANITIZE_STRING);

$stmtInsertRecord->bind_param("ss", $name, $dob);
$stmtInsertRecord->execute();
$conn->close();

}

function removeRecord(){
    $conn = new mysqli($GLOBALS['server'], $GLOBALS['user'], $GLOBALS['password'], $GLOBALS['dbname']);
    $stmtRemoveRecord = $conn->prepare("DELETE FROM birthdayBot WHERE name = ? AND dob = ?");
    
    $name = filter_var($_POST['name'],FILTER_SANITIZE_STRING);
    $dob =  filter_var($_POST['dob'],FILTER_SANITIZE_STRING);
    
    $stmtRemoveRecord->bind_param("ss", $name, $dob);
    
    $stmtRemoveRecord->execute();
    $conn->close();
    
    
}

function getCurrentRecords(){
     global $currentDate;
     $conn = new mysqli($GLOBALS['server'], $GLOBALS['user'], $GLOBALS['password'], $GLOBALS['dbname']);
     $stmtGetCurrentRecords = $conn->prepare("SELECT id,name,dob FROM birthdayBot");
     $stmtGetCurrentRecords->execute();
     $result = $stmtGetCurrentRecords->get_result();

     if($result->num_rows > 0){
        
         echo "<a style='font-weight:bold'>&nbsp;&nbsp;&nbsp;Name" . " &nbsp;&nbsp;&nbsp; " . "Date" . "<a><br>";
         
         while($row = $result->fetch_assoc()){
             echo "<table style='padding'>";
             echo "<tr>";
             echo "<td id='entryName'>".$row['name'].":</td>".""."<td id='entryDate'>" .$row['dob']."</td>";
             echo "</tr>";
             echo "</table>";
         }
         
     }else{
         echo "<br><a style='font-weight:bold'> No birthdays are recorded, please add some.</a>";
     }
   
     $conn->close();
}

function updateWebhook(){
    $conn = new mysqli($GLOBALS['server'], $GLOBALS['user'], $GLOBALS['password'], $GLOBALS['dbname']);
    $webhookURL = $_POST['webhook'];
    $stmt = $conn->prepare("UPDATE webhooktb SET webhook = ?");
    $stmt->bind_param("s", $webhookURL);
    $stmt->execute();
    $stmt->close();
    echo "Webhook successfully updated!";
    
}

function updateBirthdayMessage(){
    $conn = new mysqli($GLOBALS['server'], $GLOBALS['user'], $GLOBALS['password'], $GLOBALS['dbname']);
    $stmtUpdateBirthdayMessage = $conn->prepare("UPDATE birthdayBot SET birthdayMessage=? WHERE name=?");
    $message = filter_var($_POST['birthdayMessage'], FILTER_SANITIZE_STRING);
    $name = filter_var($_POST['nameForBirthdayMessage'], FILTER_SANITIZE_STRING);
    $stmtUpdateBirthdayMessage->bind_param("ss",$message,$name);
    $stmtUpdateBirthdayMessage->execute();
    $stmtUpdateBirthdayMessage->close();
}

function notification($getName,$message,$url){
    $noti = urlencode($message);
    header("Location: ".$url."?".$getName."=".$noti);
    die;
}


if(isset($_POST['addWebhook'])){
    updateWebhook();
    notification("addWebhookMsg","Webhook updated!","BirthdayBotGUI.php");
}

if(isset($_POST['add'])){
    insertRecord();
    notification("addBirthdayMsg","Birthday added!","BirthdayBotGUI.php");
}
if(isset($_POST['remove'])){
    removeRecord();
    notification("removeRecordMsg","Birthday has been removed!","BirthdayBotGUI.php");
}
if(isset($_POST['updateBirthdayMessage'])){
    updateBirthdayMessage();
    notification("updateBirthdayMsg","Birthday message has been updated!","BirthdayBotGUI.php");
    
}

echo "<div id='banner'><a id='limb'>Tools</a><a id='hyperbuttons' href='http://dev-insili.co/cvdpcr.php'>CVD-PCR</a><a id='hyperbuttons' href='http://dev-insili.co/tailandbc.php'>Tail & BC</a><a id='hyperbuttons' href='http://dev-insili.co/BirthdayBotGUI.php'>BirthdayBot</a><a id='hyperbuttons' href='http://dev-insili.co/ajaxMessageBoard.php'>MessageBoard</a><a id='hyperbuttons' href='https://sequencescape.psd.sanger.ac.uk/login'>Sequencescape</a><a id='hyperbuttons' href='https://limber.psd.sanger.ac.uk/'>Limber</a></div><br>";
echo "<h1>Slack Birthday Bot</h1>";
getCurrentRecords();
echo "</div>";

?>

<html>
    <head>
        <meta name="robots" content="noindex">
        <link rel="stylesheet" type="text/css" href="bbot.css">
        </head>
    <body style="font-family:Arial">
 
        <br>
        <div id="contain">
        <form method="post">
            <label type="text" id="lab">add or remove birthdays</label><?php if(isset($_GET['addBirthdayMsg'])){echo "<a id='messageUpdateNotification'>".$_GET['addBirthdayMsg']."</a>";} ?><?php if(isset($_GET['removeRecordMsg'])){echo "<a id='messageUpdateNotification'>".$_GET['removeRecordMsg']."</a>";} ?><br><br>
            <input type="text" name="name" placeholder="First name ONLY" required>
            <input type="text" name="dob" placeholder="yyyy-mm-dd" required>
            <input type="submit" name="add" class="button" value="add">
            <input type="submit" name="remove" class="button" value="remove">
            
            <p>IMPORTANT: If the birthday you are adding has passed for the current year, for yyyy use next year. If its yet to come, use the current year yyyy.
            </p>
            </form>
            </div>
            <br>
            <div id="contain">
            <form method="post">
                <label type="text" id="lab" >update birthday message</label><a id="up"></a><br><br>
                <input type="text" id="bdname" name="nameForBirthdayMessage" placeholder="Name.."><?php if(isset($_GET['updateBirthdayMsg'])){echo "<a id='messageUpdateNotification'>".$_GET['updateBirthdayMsg']."</a>";} ?><br>
                <textarea type="text" name="birthdayMessage" placeholder="Message.." cols="40" rows="20"></textarea>
                <input type="submit" id="birthdayButton" name="updateBirthdayMessage" class="button" value="update message">
            </form>
            </div>
            <br>
            <div id="contain">
            <form method="post">
                <label type="text" id="lab">update webhook</label><br><br>
                <input type="text" name="webhook" placeholder="SlackWebhook" required>
                 <input type="submit" name=addWebhook class="button" value="update webhook url"><?php if(isset($_GET['updateWebhookMsg'])){echo "<a id='messageUpdateNotification'>".$_GET['updateWebhookMsg']."</a>";} ?>
                 <p>IMPORTANT: Do not use unless changing slack channel configuration</p>
            </form>
            </div>
            </div>
        </body>
    </html>