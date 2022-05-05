<?php
header('Content-Type: application/json'); 
require 'dataBaseAccess.php';
$json_str = file_get_contents('php://input');
$json_obj = json_decode($json_str, true);
$username = $json_obj['username'];
$password=$json_obj['password1'];
$password2=$json_obj['password2'];
//check if username is valid
if(preg_match('/^[\w_\-]+$/', $username)){
    $uppercase = preg_match('@[A-Z]@', $password);
    $lowercase = preg_match('@[a-z]@', $password);
    $number    = preg_match('@[0-9]@', $password);
    $specialChars = preg_match('@[^\w]@', $password);
    //check if password is valid
    if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 8) {
        echo json_encode(array(
            "success" => false,
            "message" => "Password should be at least 8 characters in length and should include at least one upper case letter, one number, and one special character!"
        ));
        exit;
    }
    else{
        //check if passwords match
        if ($password2!=$password){
            echo json_encode(array(
                "success" => false,
                "message" => "Passwords do not match, try again!"
            )); 
            exit;	
        }
        else{
            //hash the password
            $hashpassword=password_hash($password, PASSWORD_BCRYPT); 
            $stmt = $mysqli->prepare("select username from users"); 
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
            //check if username already exists
            while($row = $result->fetch_assoc()){ 
                if($row["username"]===$username){ 
                    echo json_encode(array(
                        "success" => false,
                        "message" => "Username already exists, please choose another one!"
                    ));  
                    exit; 
                }
            }
            //safe from sql injection, insert into the user table
            $stmt = $mysqli->prepare("insert into users (username, password) values (?,?)"); 
            if(!$stmt){
                echo json_encode(array(
                    "success" => false,
                    "message" => "Query failed!"
                ));  
                exit; 
            }
            $stmt->bind_param('ss', $username, $hashpassword);
            $stmt->execute();
            $stmt->close();
            ini_set("session.cookie_httponly", 1);
            session_start();
            //start the session
            $_SESSION['username']=$username;
            echo json_encode(array(
                "success" => true
            ));
            exit;
        }
    }
}
echo json_encode(array(
    "success" => false,
    "message" => "Please choose an appropriate username!"
));
exit;
?>
