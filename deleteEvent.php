<?php

header("Content-Type: application/json"); 
require 'dataBaseAccess.php';
ini_set("session.cookie_httponly", 1);
session_start();
$json_str = file_get_contents('php://input');
$json_obj = json_decode($json_str, true);
//retrieve event ID
$eventID=$json_obj['eventID'];
$token=$json_obj['token'];
if(!hash_equals($_SESSION['token'], $token)){
	die("Request forgery detected");
}
//safe from sql injections
$stmt = $mysqli->prepare("delete from events where eventID=?");
$stmt->bind_param('i', $eventID);
if(!$stmt){
    echo json_encode(array(
        "success" => false,
        "message" => "Query failed!"
    )); 
    exit;
}
$stmt->execute();
$stmt->close();
echo json_encode(array(
    "success" => true,
)); 
exit;

?>