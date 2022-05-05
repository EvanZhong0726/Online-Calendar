<?php
header("Content-Type: application/json"); 
require 'dataBaseAccess.php';
ini_set("session.cookie_httponly", 1);
session_start();
$username=$_SESSION['username'];
$json_str = file_get_contents('php://input');
$json_obj = json_decode($json_str, true);
//retrieve input variables
$eventID=$json_obj['eventID'];
$event=$json_obj['event'];
$startTime=$json_obj['eventStartTime'];
$endTime=$json_obj['eventEndTime'];
$eventDate=$json_obj['eventDate'];
$token=$json_obj['token'];
if(!hash_equals($_SESSION['token'], $token)){
	die("Request forgery detected");
}
//update the time to sql datetime format
$startTime=str_replace("am"," am",$startTime);
$startTime=str_replace("pm"," pm",$startTime);
$endTime=str_replace("am"," am",$endTime);
$endTime=str_replace("pm"," pm",$endTime);
$eventStartTime=$eventDate." ".$startTime;
$eventStartTime=strtotime($eventStartTime);
$eventEndTime=$eventDate." ".$endTime;
$eventEndTime=strtotime($eventEndTime);
//the event end time must be before the start time
if($eventEndTime<=$eventStartTime){
    echo json_encode(array(
          "success"=>false,
          "message"=>"The event end time must be before the event start time"
    ));
    exit;
}
$stmt = $mysqli->prepare("select eventStartTime, eventEndTime, eventID from events where username=? and eventID!=?");
$stmt->bind_param('si', $username,$eventID);
if(!$stmt){
    echo json_encode(array(
        "success" => false,
        "message" => "Query failed!"
    )); 
    exit;
}
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
//if there is another event in the time period, output an error message
while($row = $result->fetch_assoc()){
    if($eventEndTime>strtotime($row['eventStartTime']) && $eventStartTime<=strtotime($row['eventStartTime']) || ($eventStartTime<strtotime($row['eventEndTime'])&& $eventEndTime>=strtotime($row['eventEndTime']) )){
        echo json_encode(array(
            "success" => false,
            "message" => "There is another event in your time period!"
        )); 
        exit; 
    }
}
$eventEndTime=date('Y-m-d H:i:s', $eventEndTime);
$eventStartTime=date('Y-m-d H:i:s', $eventStartTime);
$eventDate=strtotime($eventDate);
$eventDate=date('Y-m-d', $eventDate);
//safe from sql injections and update the events
$stmt=$mysqli->prepare("update events set event=?, eventDate=?, eventStartTime=?, eventEndTime=? where eventID=?");
if(!$stmt){
    echo json_encode(array(
        "success" => false,
        "message" => "Query failed!"
    )); 
    exit;
}
$stmt->bind_param('ssssi', $event,$eventDate,$eventStartTime,$eventEndTime,$eventID);
$stmt->execute();
$stmt->close();
echo json_encode(array(
    "success"=>true,
));
exit;
?>