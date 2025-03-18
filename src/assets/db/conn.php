<?php
//Create a connection
header('Access-Control-Allow-Origin: *');
// $conn = new mysqli("localhost", "assasate_gto", "Assasa@123", "assasate_greentop");
// For some machines the conn will be $conn = new mysqli("localhost", "root", "", "greentop", 3306);

$conn = new mysqli("127.0.0.1", "admin", "admin", "greentop", 3306);


function write_log($log, $flag, $errorval){
    if (!file_exists('../../logs')) {
        mkdir('../../logs', 0777, true);
    }
    date_default_timezone_set("Asia/Calcutta");
    $log = "User: ".$_SERVER['REMOTE_ADDR'].' - '.date("F j, Y, H:i:s").PHP_EOL.$log;
    if($flag == "success"){
        $log="Action: Success".PHP_EOL.$log;
    }
    else{
        if($errorval != NULL){
            $log="Action: Failed".PHP_EOL.$log;
        }
    }
    $log = $log. "\n*******************************************************************************".PHP_EOL;
    "******************************************************************\n".PHP_EOL;
    if($flag == "success"){
        file_put_contents('../../logs/log_'.date("j.n.Y").'.log', $log, FILE_APPEND);
    }
    else{
        if($errorval != NULL){
            file_put_contents('../../logs/error_log_'.date("j.n.Y").'.log', $log, FILE_APPEND);
        }
    }
}

function authenticate($headers){
    $secretkey = 'greentoporg';
	$authorization = NULL;
	foreach ($headers as $header => $value) {
		if($header == "Authorization"){
			$authorization = true;
			break;
		}
	}
	//print_r($ApacheHeaders);
	if($authorization == NULL){
		die(1);
	}
	try{
		$headauth= $headers['Authorization'];
		$token = JWT::decode($headauth, $secretkey);
	}
	catch (Exception $e) {
		$log  = "NON AUTHORISED REQUEST".PHP_EOL;
		write_log($log, "error", "Non Authorised");
		header(' ', true, 401);
		exit(0);
	}
}
?>