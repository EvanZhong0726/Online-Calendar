<?php
header('Content-Type: application/json');
ini_set("session.cookie_httponly", 1);
session_start();
//destroy and unset the session
session_destroy();
session_unset();
if(isset($_SESSION['username'])){
    echo json_encode(array(
        "success"=>false,
        "message"=>$_SESSION['username']
    ));
    exit;
}
else{
    echo json_encode(array(
        "success"=>true,
    ));
    exit;
}
?>