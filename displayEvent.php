<?php
header("Content-Type: application/json"); 
require 'dataBaseAccess.php';
ini_set("session.cookie_httponly", 1);
session_start();
$json_str = file_get_contents('php://input');
$json_obj = json_decode($json_str, true);
$eventID=$json_obj['eventID'];
//safe from sql injections
$stmt = $mysqli->prepare("select event, eventStartTime, eventEndTime from events where eventID=?");
$stmt->bind_param('i', $eventID);
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
//store event information into arrays
$events=array();
$startTimes=array();
$endTimes=array();
//update the array
//escape html output to prevent xss attacks
while($row = $result->fetch_assoc()){
    array_push($events,htmlentities($row['event']));
    array_push($startTimes,htmlentities($row['eventStartTime']));
    array_push($endTimes,htmlentities($row['eventEndTime']));
}
//output the results
echo json_encode(array(
    "success" =>true,
    "endTime"=>$endTimes[0],
    "event"=>$events[0],
    "startTime"=>$startTimes[0],
)); 
exit;
?>