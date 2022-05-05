<?php
header("Content-Type: application/json"); 
require 'dataBaseAccess.php';
ini_set("session.cookie_httponly", 1);
session_start();
$json_str = file_get_contents('php://input');
$json_obj = json_decode($json_str, true);
//get user session variable
$username = $_SESSION['username'];
//convert date to sql datetimes
$date=$json_obj['date'];
$date=strtotime($date);
$date=date('Y-m-d', $date);
//safe from sql injections
$stmt = $mysqli->prepare("select event, eventID, eventStartTime, eventEndTime from events where username=? and eventDate=? order by eventStartTime asc");
$stmt->bind_param('ss', $username, $date);
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
//create variables arrays
$events=array();
$startTimes=array();
$eventIDs=array();
$endTimes=array();
//fetch the results
//escape html output to prevent xss attacks
while($row = $result->fetch_assoc()){
    array_push($events,htmlentities($row['event']));
    array_push($startTimes,htmlentities($row['eventStartTime']));
    array_push($eventIDs,htmlentities($row['eventID']));
    array_push($endTimes,htmlentities($row['eventEndTime']));
}
//encode the results into json
echo json_encode(array(
    "success" =>true,
    "eventIDs"=>$eventIDs,
    "events"=>$events,
    "startTimes"=>$startTimes,
    "endTimes"=>$endTimes
)); 
exit;
?>