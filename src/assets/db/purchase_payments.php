<?php
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
header('Access-Control-Allow-Headers: Authorization, X-Requested-With, Content-Type, Accept');
//account.php?action=signUp
include 'conn.php';
include 'jwt_helper.php';

$action = $_GET['action'];

if($action == "getAllPurchaseMastPayments"){
	$headers = apache_request_headers();
	authenticate($headers);
    $clientid= $_GET["clientid"];
    $fromdt= $_GET["fromdt"];
    $todt= $_GET["todt"];
	$sql = "SELECT * FROM `purchase_master` WHERE `clientid`=$clientid AND `billdt` BETWEEN '$fromdt' AND '$todt'";
	$result = $conn->query($sql);
	while($row = $result->fetch_array())
	{
		$rows[] = $row;
	}

	$tmp = array();
	$data = array();
	$i = 0;

	if($rows && count($rows)>0){
		foreach($rows as $row)
		{
			$tmp[$i]['purcmastid'] = $row['purcmastid'];
			$tmp[$i]['clientid'] = $row['clientid'];
			$tmp[$i]['vehicalno'] = $row['vehicalno'];
			$tmp[$i]['dcno'] = $row['dcno'];
			$tmp[$i]['billno'] = $row['billno'];
			$tmp[$i]['billdt'] = $row['billdt'];
			$tmp[$i]['arrivaldt'] = $row['arrivaldt'];
			$tmp[$i]['totaldiscount'] = $row['totaldiscount'];
			$tmp[$i]['totalamount'] = $row['totalamount'];
			$i++;
		}
		$data["status"] = 200;
		$data["data"] = $tmp;
		$log  = "File: purchase_payments.php - Method: ".$action.PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data["status"] = 204;
		$log  = "File: purchase_payments.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}
	echo json_encode($data);
}

if($action == "getAllPurchasePayments"){
	$headers = apache_request_headers();
	authenticate($headers);
    $clientid= $_GET["clientid"];
    $fromdt= $_GET["fromdt"];
    $todt= $_GET["todt"];
	$sql = "SELECT pp.`purchpayid`, pp.`paydate`, pp.`clientid`, pp.`amount`, pp.`paymodeid`, pp.`particulars`, pm.paymode FROM `purchase_payments` pp, `paymode_master` pm WHERE pp.`paymodeid`=pm.`paymodeid` AND `clientid`=$clientid AND `paydate` BETWEEN '$fromdt' AND '$todt'";
	$result = $conn->query($sql);
	while($row = $result->fetch_array())
	{
		$rows[] = $row;
	}

	$tmp = array();
	$data = array();
	$i = 0;

	if($rows && count($rows)>0){
		foreach($rows as $row)
		{
			$tmp[$i]['purchpayid'] = $row['purchpayid'];
			$tmp[$i]['paydate'] = $row['paydate'];
			$tmp[$i]['clientid'] = $row['clientid'];
			$tmp[$i]['amount'] = $row['amount'];
			$tmp[$i]['paymodeid'] = $row['paymodeid'];
			$tmp[$i]['particulars'] = $row['particulars'];
			$tmp[$i]['paymode'] = $row['paymode'];
			$i++;
		}
		$data["status"] = 200;
		$data["data"] = $tmp;
		$log  = "File: purchase_payments.php - Method: ".$action.PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data["status"] = 204;
		$log  = "File: purchase_payments.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}
	echo json_encode($data);
}

if($action == "getAllCashPaymentsToSuppliers"){
	$headers = apache_request_headers();
	authenticate($headers);
    $fromdt= $_GET["fromdt"];
    $todt= $_GET["todt"];
	$sql = "SELECT pp.`purchpayid`, pp.`paydate`, pp.`clientid`, pp.`amount`, pp.`particulars`, cm.`name`, pm.`paymode` FROM `purchase_payments` pp, `paymode_master` pm, `client_master` cm WHERE pp.`paymodeid`=pm.`paymodeid` AND pm.`paymode`='CASH' AND pp.`clientid`=cm.`clientid` AND `paydate` BETWEEN '$fromdt' AND '$todt'";
	$result = $conn->query($sql);
	while($row = $result->fetch_array())
	{
		$rows[] = $row;
	}

	$tmp = array();
	$data = array();
	$i = 0;

	if($rows && count($rows)>0){
		foreach($rows as $row)
		{
			$tmp[$i]['purchpayid'] = $row['purchpayid'];
			$tmp[$i]['paydate'] = $row['paydate'];
			$tmp[$i]['clientid'] = $row['clientid'];
			$tmp[$i]['amount'] = $row['amount'];
			$tmp[$i]['particulars'] = $row['particulars'];
			$tmp[$i]['name'] = $row['name'];
			$tmp[$i]['paymode'] = $row['paymode'];
			$i++;
		}
		$data["status"] = 200;
		$data["data"] = $tmp;
		$log  = "File: purchase_payments.php - Method: ".$action.PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data["status"] = 204;
		$log  = "File: purchase_payments.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}
	echo json_encode($data);
}

if($action == "addPurchasePayment"){
	$headers = apache_request_headers();
	authenticate($headers);
    $data = json_decode(file_get_contents("php://input"));
    $paydt = $data->paydt;
    $suppid = $data->suppid;
    $amtpaid = $data->amtpaid;
    $paymode = $data->paymode;
    $particulars = $data->particulars;
   	if($_SERVER['REQUEST_METHOD']=='POST'){
		$sql = "INSERT INTO `purchase_payments`(`paydate`, `clientid`, `amount`, `paymodeid`, `particulars`) VALUES ('$paydt','$suppid','$amtpaid','$paymode','$particulars')";
        $result = $conn->query($sql);
        $purchpayid = $conn->insert_id;
	}
    $data1= array();
    if($result){
		$data1["status"] = 200;
		$data1["data"] = $purchpayid;
		$log  = "File: purchase_payments.php - Method: ".$action.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data1["status"] = 204;
		$log  = "File: purchase_payments.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}

	echo json_encode($data1);
}

if($action == "updatePurchasePayment"){
	$headers = apache_request_headers();
	authenticate($headers);
    $data = json_decode(file_get_contents("php://input"));
    $purchpayid = $data->purchpayid;
    $purchdate = $data->purchdate;
    $amountpaid = $data->amountpaid;
    $particulars = $data->particulars;
   	if($_SERVER['REQUEST_METHOD']=='POST'){
		$sql = "UPDATE `purchase_payments` SET `paydate`='$purchdate',`amount`='$amountpaid',`particulars`='$particulars' WHERE `purchpayid`=$purchpayid";
        $result = $conn->query($sql);
	}
    $data1= array();
    if($result){
		$data1["status"] = 200;
		$data1["data"] = $purchpayid;
		$log  = "File: purchase_payments.php - Method: ".$action.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data1["status"] = 204;
		$log  = "File: purchase_payments.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}

	echo json_encode($data1);
}

if($action == "deletePurchasePayRecord"){
	$headers = apache_request_headers();
	authenticate($headers);
    $purchpayid = $_GET["purchpayid"];
	$sql = "DELETE FROM `purchase_payments` WHERE `purchpayid`=$purchpayid";
	$result = $conn->query($sql);
    $data1= array();
    if($result){
		$data1["status"] = 200;
		$data1["data"] = $purchpayid;
		$log  = "File: purchase_payments.php - Method: ".$action.PHP_EOL.
		"Data: ".json_encode($data1).PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data1["status"] = 204;
		$log  = "File: purchase_payments.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}

	echo json_encode($data1);
}

if($action == "receiveSupplierPayments"){
	$headers = apache_request_headers();
	authenticate($headers);
    $data = json_decode(file_get_contents("php://input"));
    $supppaydt = $data->supppaydt;
    $suppid = $data->suppid;
    $suppamtpaid = $data->suppamtpaid;
    $paymode = $data->paymode;
    $particulars = $data->particulars;
   	if($_SERVER['REQUEST_METHOD']=='POST'){
		$sql = "INSERT INTO `receive_supplier_payments`(`clientid`, `paydate`, `amountpaid`, `paymodeid`, `particulars`) VALUES ('$suppid','$supppaydt','$suppamtpaid','$paymode','$particulars')";
        $result = $conn->query($sql);
        $purchpayid = $conn->insert_id;
	}
    $data1= array();
    if($result){
		$data1["status"] = 200;
		$data1["data"] = $purchpayid;
		$log  = "File: purchase_payments.php - Method: ".$action.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data1["status"] = 204;
		$log  = "File: purchase_payments.php - Method: ".$action.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 204);
	}

	echo json_encode($data1);
}

if($action == "getAllClientSuppMadePayments"){
	$headers = apache_request_headers();
	authenticate($headers);
    $clientid= $_GET["clientid"];
    $fromdt= $_GET["fromdt"];
    $todt= $_GET["todt"];
	$sql = "SELECT rsp.*,pm.paymode FROM `receive_supplier_payments` rsp, `paymode_master` pm WHERE rsp.`paymodeid`=pm.`paymodeid` AND `clientid`='$clientid' AND `paydate` BETWEEN '$fromdt' AND '$todt'";
	$result = $conn->query($sql);
	$tmp = array();
	$data = array();
	if($result){
		$i=0;
        while($row = $result->fetch_array())
        {
			$tmp[$i]['receivesupppayid'] = $row['receivesupppayid'];
			$tmp[$i]['clientid'] = $row['clientid'];
			$tmp[$i]['paydate'] = $row['paydate'];
			$tmp[$i]['amountpaid'] = $row['amountpaid'];
			$tmp[$i]['paymodeid'] = $row['paymodeid'];
			$tmp[$i]['particulars'] = $row['particulars'];
			$tmp[$i]['paymode'] = $row['paymode'];
			$i++;
        }
		$data["status"] = 200;
		$data["data"] = $tmp;
		$log  = "File: purchase_payments.php - Method: ".$action.PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data["status"] = 204;
		$log  = "File: purchase_payments.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}
	echo json_encode($data);
}

if($action == "updateReceivePurchasePayment"){
	$headers = apache_request_headers();
	authenticate($headers);
    $data = json_decode(file_get_contents("php://input"));
    $id = $data->id;
    $paydate = $data->paydate;
    $amountpaid = $data->amountpaid;
    $particulars = $data->particulars;
   	if($_SERVER['REQUEST_METHOD']=='POST'){
		$sql = "UPDATE `receive_supplier_payments` SET `paydate`='$paydate',`amountpaid`='$amountpaid',`particulars`='$particulars' WHERE `receivesupppayid`=$id";
        $result = $conn->query($sql);
	}
    $data1= array();
    if($result){
		$data1["status"] = 200;
		$data1["data"] = $id;
		$log  = "File: purchase_payments.php - Method: ".$action.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data1["status"] = 204;
		$log  = "File: purchase_payments.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}

	echo json_encode($data1);
}

if($action == "deleteReceivePayRecord"){
	$headers = apache_request_headers();
	authenticate($headers);
    $recievepayid = $_GET["recievepayid"];
	$sql = "DELETE FROM `receive_supplier_payments` WHERE `receivesupppayid`=$recievepayid";
	$result = $conn->query($sql);
    $data1= array();
    if($result){
		$data1["status"] = 200;
		$data1["data"] = $recievepayid;
		$log  = "File: purchase_payments.php - Method: ".$action.PHP_EOL.
		"Data: ".json_encode($data1).PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data1["status"] = 204;
		$log  = "File: purchase_payments.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}

	echo json_encode($data1);
}
?>