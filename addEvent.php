<?php
header("Content-Type: application/json"); 
require 'dataBaseAccess.php';
ini_set("session.cookie_httponly", 1);
session_start();
$json_str = file_get_contents('php://input');
$json_obj = json_decode($json_str, true);
$username = $_SESSION['username'];
$event = $json_obj['event'];
$token=$json_obj['token'];
if(!hash_equals($_SESSION['token'], $token)){
	die("Request forgery detected");
}
//if event is null, the name of the event is set to untitled
if($event==""){
    $event="untitled";
}
//retrieve and convert start and end times to sql dateTime formats
$startTime = $json_obj['startTime'];
$endTime = $json_obj['endTime'];
$eventDate = $json_obj['eventDate'];
$startTime=str_replace("am"," am",$startTime);
$startTime=str_replace("pm"," pm",$startTime);
$endTime=str_replace("am"," am",$endTime);
$endTime=str_replace("pm"," pm",$endTime);
$eventStartTime=$eventDate." ".$startTime;
$eventStartTime=strtotime($eventStartTime);
$eventEndTime=$eventDate." ".$endTime;
$eventEndTime=strtotime($eventEndTime);
//if the event end time is less than the event start time, output an error message
if($eventEndTime<=$eventStartTime){
      echo json_encode(array(
            "success"=>false,
            "message"=>"The event end time must be before the event start time"
      ));
      exit;
}
//safe from sql injections
$stmt = $mysqli->prepare("select eventStartTime, eventEndTime from events where username=?");
$stmt->bind_param('s', $username);
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
//if there is another event in the same time period, output an error message
while($row = $result->fetch_assoc()){
    if(($eventEndTime>strtotime($row['eventStartTime']) && $eventStartTime<=strtotime($row['eventStartTime']))|| ($eventStartTime<strtotime($row['eventEndTime'])&& $eventEndTime>=strtotime($row['eventEndTime']) )){
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
//safe from sql injections, add the event to the events database
$stmt = $mysqli->prepare("insert into events (username, event, eventDate, eventStartTime, eventEndTime) values (?,?,?,?,?)"); 
if(!$stmt){
    echo json_encode(array(
        "success" => false,
        "message" => "Query failed!"
    )); 
    exit;
}
$stmt->bind_param('sssss', $username, $event,$eventDate,$eventStartTime,$eventEndTime);
$stmt->execute();
$stmt->close();
echo json_encode(array(
    "success" => true,
)); 
exit;
?>