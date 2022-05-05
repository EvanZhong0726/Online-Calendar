<?php

header("Content-Type: application/json"); 
require 'dataBaseAccess.php';
$json_str = file_get_contents('php://input');
$json_obj = json_decode($json_str, true);
//retrieve input variables
$username = $json_obj['username'];
$password = $json_obj['password'];
// check if the username is in correct format
if(preg_match('/^[\w_\-]+$/', $username)){
	//safe from sql injections
	$stmt = $mysqli->prepare("select username, password from users");
	if(!$stmt){
		echo json_encode(array(
			"success" => false,
			"message" => "Query failed!"
		)); 
		exit;
	}
	$stmt->execute();
	$result = $stmt->get_result();
    //check if password matches
	while($row = $result->fetch_assoc()){
		if($username==htmlspecialchars($row["username"] ) && password_verify($password, $row["password"])){ 	
			$stmt->close();
			ini_set("session.cookie_httponly", 1);
			session_start();
			//start the session
			$_SESSION['username']=$username;
			$_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes(32));
			echo json_encode(array(
				"success" => true,
				"token"=> $_SESSION['token']
			));
			exit;
		}
		//if not, output incorrect password
		elseif ($username==htmlspecialchars($row["username"])){
			$stmt->close();
			echo json_encode(array(
				"success" => false,
				"message" => "Incorrect Password!"
			)); 
			exit;
		}
	}
	$stmt->close();
	//if username is not found, output another error message
	echo json_encode(array(
		"success" => false,
		"message" => "Username does not exist, try again!"
	)); 
	exit;
}
else{
	echo json_encode(array(
		"success" => false,
		"message" => "Invalid Username"
	));
	exit;
}
?>