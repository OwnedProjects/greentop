<?php
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
header('Access-Control-Allow-Headers: Authorization, X-Requested-With, Content-Type, Accept');
//https://coderwall.com/p/8wrxfw/goodbye-php-sessions-hello-json-web-tokens
//account.php?action=signUp
include 'conn.php';
include 'jwt_helper.php';
$action = $_GET['action'];
$transferAccs=["client_open_bal", "cashbank_acc_ledger_bal", "stock_bal_transfer"];
if($action == "checkLogin"){
    date_default_timezone_set("Asia/Calcutta");
    $data = json_decode(file_get_contents("php://input"));
	$email = $data->email;
	$passwd = md5($data->passwd);
	$fromdt = $data->fromdt;
	$sql = "select * from `user_register` where email='$email' AND password='$passwd'";
	$result = $conn->query($sql);
	$row = $result->fetch_array();
	$tmp = array();
	$datares = array();
	$token = array();
	$d1 = new Datetime();
	$token['email'] = $email.$d1->format('U')*1000;
	if(count($row)>0){
        $tmp[0]['userid'] = $row['userid'];
        $tmp[0]['fullname'] = $row['fullname'];
        $tmp[0]['email'] = $row['email'];
		$tmp[0]['email'] = $row['email'];
		$tmp[0]['address'] = $row['address'];
		$d1 = new Datetime();
		$tmp[0]['sessiontime'] = $d1->format('U')*1000;
		$tmp[0]['token'] = JWT::encode($token, 'greentoporg');
			$sqlyr = "SELECT * FROM `current_financialyr` WHERE `finanyr`=$fromdt";
			$resultyr = $conn->query($sqlyr);
			$rowyr = $resultyr->fetch_array();
			$yrdet = array();
			if($rowyr && count($rowyr)>0){
				for($i=0; $i<count($transferAccs); $i++) {
					$sqldet = "SELECT * FROM `current_financialyr` WHERE `finanyr`=$fromdt AND `transferaccs`='$transferAccs[$i]'";
					$resultdet = $conn->query($sqldet);
					$rowdet = $resultdet->fetch_array();
					if($rowdet && count($rowdet)>0){
						$yrdet[$i]["finanyr_id"]=$rowdet["finanyr_id"];
						$yrdet[$i]["finanyr"]=$fromdt;
						$yrdet[$i]["transferaccs"]=$rowdet["transferaccs"];
						$yrdet[$i]["status"]=$rowdet["status"];
					}
				}

			}else{
				for($i=0; $i<count($transferAccs); $i++) {
					$sqlin = "INSERT INTO `current_financialyr`(`finanyr`, `transferaccs`, `status`) VALUES ('$fromdt','$transferAccs[$i]','inactive')";
					$resultin = $conn->query($sqlin);
					$yrdet[$i]["finanyr"]=$fromdt;
					$yrdet[$i]["transferaccs"]=$transferAccs[$i];
					$yrdet[$i]["status"]='inactive';
				}
			}
			$tmp[0]["transferAcc"] = $yrdet;

        $datares["status"] = 200;
		$datares["data"] = $tmp;
		$log  = "File: users.php - Method: ".$action.PHP_EOL.
		"Logged In User: ".$data->email.PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
    }
    else{
        $datares["status"] = 204;
		$log  = "File: users.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
    }
    echo json_encode($datares);
}

if($action == "checkOldPass"){
	$headers = apache_request_headers();
	authenticate($headers);
	$data = json_decode(file_get_contents("php://input"));
	$uid = $data->uid;
	$oldpass = md5($data->oldpass);
	if($_SERVER['REQUEST_METHOD']=='POST'){
		$sql = "SELECT * FROM `user_register` WHERE `userid`=1 AND `password`='$oldpass'";
		$result = $conn->query($sql);
		$row = $result->fetch_array(MYSQLI_ASSOC);
	}
	$data1 = array();
    if($result){
        $data1["status"] = 200;
		$data1["data"] = $row['userid'];
		$log  = "File: users.php - Method: ".$action.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data1["status"] = 204;
		$log  = "File: users.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
    }
	echo json_encode($data1);
}

if($action == "changePassword"){
	$headers = apache_request_headers();
	authenticate($headers);
	$data = json_decode(file_get_contents("php://input"));
	$uid = $data->uid;
	$newpass = md5($data->newpass);
	if($_SERVER['REQUEST_METHOD']=='POST'){
		$sql = "UPDATE `user_register` SET `password`='$newpass' WHERE `userid`=$uid";
		$result = $conn->query($sql);
	}
	$data1 = array();
    if($result){
        $data1["status"] = 200;
		$data1["data"] = $uid;
		$log  = "File: users.php - Method: ".$action.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data1["status"] = 204;
		$log  = "File: users.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
    }
	echo json_encode($data1);
}

if($action == "getDBSettings"){
	$headers = apache_request_headers();
	authenticate($headers);
	$sql = "SELECT * FROM `dbsetting_master`";
	$result = $conn->query($sql);
	while($row = $result->fetch_array())
	{
		$rows[] = $row;
	}

	$tmp = array();
	$data = array();
	$i = 0;

	if(count($rows)>0){
		foreach($rows as $row)
		{
			$tmp[$i]['dbsettingid'] = $row['dbsettingid'];
			$tmp[$i]['dbsettingtitle'] = $row['dbsettingtitle'];
			$tmp[$i]['state'] = $row['state'];
			$i++;
		}
		$data["status"] = 200;
		$data["data"] = $tmp;
		$log  = "File: users.php - Method: ".$action.PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data["status"] = 204;
		$log  = "File: users.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}

	echo json_encode($data);
}

if($action == "updateDBSettings"){
	$headers = apache_request_headers();
	authenticate($headers);
	$data = json_decode(file_get_contents("php://input"));
	$dbsettingid = $data->dbsettingid;
	$state = $data->state;
	if($_SERVER['REQUEST_METHOD']=='POST'){
		$sql = "UPDATE `dbsetting_master` SET `state`=$state WHERE `dbsettingid`=$dbsettingid";
		$result = $conn->query($sql);
	}
	$data1 = array();
    if($result){
        $data1["status"] = 200;
		$data1["data"] = $dbsettingid;
		$log  = "File: users.php - Method: ".$action.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data1["status"] = 204;
		$log  = "File: users.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
    }
	echo json_encode($data1);
}

if($action == "updateCompanyAddress"){
	$headers = apache_request_headers();
	authenticate($headers);
	$data = json_decode(file_get_contents("php://input"));
	$userid = $data->userid;
	$address = $data->address;
	if($_SERVER['REQUEST_METHOD']=='POST'){
		$sql = "UPDATE `user_register` SET `address`='$address' WHERE `userid`=$userid";
		$result = $conn->query($sql);
	}
	$data1 = array();
    if($result){
        $data1["status"] = 200;
		$data1["data"] = $userid;
		$log  = "File: users.php - Method: ".$action.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data1["status"] = 204;
		$log  = "File: users.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
    }
	echo json_encode($data1);
}
?>