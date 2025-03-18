<?php
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
header('Access-Control-Allow-Headers: Authorization, X-Requested-With, Content-Type, Accept');
//account.php?action=signUp
include 'conn.php';
include 'jwt_helper.php';

$action = $_GET['action'];

if($action == "getAllOrderInvoicePayments"){
	$headers = apache_request_headers();
	authenticate($headers);
    $clientid= $_GET["clientid"];
    $fromdt= $_GET["fromdt"];
    $prevfromdt= $_GET["prevfromdt"];
    $prevtodt= $_GET["prevtodt"];
    $todt= $_GET["todt"];
	$sql = "SELECT * FROM `order_taxinvoice` WHERE `clientid`=$clientid AND `billdt` BETWEEN '$fromdt' AND '$todt'";
	$result = $conn->query($sql);
    $tmp = array();
	if($result){
        $i=0;
	while($row = $result->fetch_array())
	{
		$tmp[$i]['orderid'] = $row['orderid'];
		$tmp[$i]['clientid'] = $row['clientid'];
		$tmp[$i]['billno'] = $row['billno'];
		$tmp[$i]['billdt'] = $row['billdt'];
		$tmp[$i]['totalamount'] = $row['totalamount'];
		$i++;
	}

		$data["status"] = 200;
		$data["data"] = $tmp;
		$log  = "File: sales_payments.php - Method: ".$action.PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data["status"] = 204;
		$log  = "File: sales_payments.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}
	echo json_encode($data);
}

if($action == "getAllSaleOrderPayments"){
	$headers = apache_request_headers();
	authenticate($headers);
    $clientid= $_GET["clientid"];
    $fromdt= $_GET["fromdt"];
    $todt= $_GET["todt"];
	$sql = "SELECT op.`orderpayid`, op.`paydate`, op.`clientid`, op.`amount`, op.`paymodeid`, op.`particulars`, pm.paymode FROM `order_payments` op, `paymode_master` pm WHERE op.`paymodeid`=pm.`paymodeid` AND `clientid`=$clientid AND `paydate` BETWEEN '$fromdt' AND '$todt'";
	$result = $conn->query($sql);
	$tmp = array();
	if($result){
        $i=0;
		while($row = $result->fetch_array())
		{
			$tmp[$i]['orderpayid'] = $row['orderpayid'];
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
		$log  = "File: sales_payments.php - Method: ".$action.PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data["status"] = 204;
		$log  = "File: sales_payments.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}
	echo json_encode($data);
}

if($action == "addSalesPayment"){
	$headers = apache_request_headers();
	authenticate($headers);
    $data = json_decode(file_get_contents("php://input"));
    $paydt = $data->paydt;
    $custid = $data->custid;
    $amtpaid = $data->amtpaid;
    $paymode = $data->paymode;
    $particulars = $data->particulars;
   	if($_SERVER['REQUEST_METHOD']=='POST'){
		//$sql = "INSERT INTO `purchase_payments`(`paydate`, `clientid`, `amount`, `paymodeid`, `particulars`) VALUES ('$paydt','$custid','$amtpaid','$paymode','$particulars')";
		$sql = "INSERT INTO `order_payments`(`paydate`, `clientid`, `amount`, `paymodeid`, `particulars`) VALUES ('$paydt','$custid','$amtpaid','$paymode','$particulars')";
        $result = $conn->query($sql);
        $salepayid = $conn->insert_id;
	}
    $data1= array();
    if($result){
		$data1["status"] = 200;
		$data1["data"] = $salepayid;
		$log  = "File: sales_payments.php - Method: ".$action.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data1["status"] = 204;
		$log  = "File: sales_payments.php - Method: ".$action.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 204);
	}

	echo json_encode($data1);
}

if($action == "updateSalePayment"){
	$headers = apache_request_headers();
	authenticate($headers);
    $data = json_decode(file_get_contents("php://input"));
    $orderpayid = $data->orderpayid;
    $paydate = $data->paydate;
    $amountpaid = $data->amountpaid;
    $particulars = $data->particulars;
   	if($_SERVER['REQUEST_METHOD']=='POST'){
		$sql = "UPDATE `order_payments` SET `paydate`='$paydate',`amount`='$amountpaid',`particulars`='$particulars' WHERE `orderpayid`=$orderpayid";
        $result = $conn->query($sql);
	}
    $data1= array();
    if($result){
		$data1["status"] = 200;
		$data1["data"] = $orderpayid;
		$log  = "File: sales_payments.php - Method: ".$action.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data1["status"] = 204;
		$log  = "File: sales_payments.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}

	echo json_encode($data1);
}

if($action == "updateMakeCustSalePayment"){
	$headers = apache_request_headers();
	authenticate($headers);
    $data = json_decode(file_get_contents("php://input"));
    $id = $data->id;
    $paydate = $data->paydate;
    $amountpaid = $data->amountpaid;
    $particulars = $data->particulars;
   	if($_SERVER['REQUEST_METHOD']=='POST'){
		$sql = "UPDATE `make_cust_payments` SET `paydate`='$paydate',`amountpaid`='$amountpaid',`particulars`='$particulars' WHERE `makecustpayid`=$id";
        $result = $conn->query($sql);
	}
    $data1= array();
    if($result){
		$data1["status"] = 200;
		$data1["data"] = $id;
		$log  = "File: sales_payments.php - Method: ".$action.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data1["status"] = 204;
		$log  = "File: sales_payments.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}

	echo json_encode($data1);
}

if($action == "deleteSalePayRecord"){
	$headers = apache_request_headers();
	authenticate($headers);
    $orderpayid = $_GET["orderpayid"];
	$sql = "DELETE FROM `order_payments` WHERE `orderpayid`=$orderpayid";
	$result = $conn->query($sql);
    $data1= array();
    if($result){
		$data1["status"] = 200;
		$data1["data"] = $orderpayid;
		$log  = "File: sales_payments.php - Method: ".$action.PHP_EOL.
		"Data: ".json_encode($data1).PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data1["status"] = 204;
		$log  = "File: sales_payments.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}

	echo json_encode($data1);
}

if($action == "deleteMakeCustPayRecord"){
	$headers = apache_request_headers();
	authenticate($headers);
    $makecustpayid = $_GET["makecustpayid"];
	$sql = "DELETE FROM `make_cust_payments` WHERE `makecustpayid`=$makecustpayid";
	$result = $conn->query($sql);
    $data1= array();
    if($result){
		$data1["status"] = 200;
		$data1["data"] = $makecustpayid;
		$log  = "File: sales_payments.php - Method: ".$action.PHP_EOL.
		"Data: ".json_encode($data1).PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data1["status"] = 204;
		$log  = "File: sales_payments.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}

	echo json_encode($data1);
}

if($action == "getAllOrderPaymentsFromToDt"){
	$headers = apache_request_headers();
	authenticate($headers);
    $fromdt= $_GET["fromdt"];
    $todt= $_GET["todt"];
	$sql = "SELECT op.*,cm.*,pm.* FROM `order_payments` op,`client_master` cm, `paymode_master` pm WHERE op.`clientid`=cm.`clientid` AND `paydate` BETWEEN '$fromdt' AND '$todt' AND op.paymodeid=pm.paymodeid";
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
			$tmp[$i]['orderpayid'] = $row['orderpayid'];
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
		$log  = "File: sales_payments.php - Method: ".$action.PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data["status"] = 204;
		$log  = "File: sales_payments.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}
	echo json_encode($data);
}

if($action == "makeCustomerPayments"){
	$headers = apache_request_headers();
	authenticate($headers);
    $data = json_decode(file_get_contents("php://input"));
    $custpaydate = $data->custpaydate;
    $custid = $data->custid;
    $customeramtpaid = $data->customeramtpaid;
    $paymode = $data->paymode;
    $particulars = $data->particulars;
   	if($_SERVER['REQUEST_METHOD']=='POST'){
		$sql = "INSERT INTO `make_cust_payments`(`clientid`, `paydate`, `amountpaid`, `paymodeid`, `particulars`) VALUES ('$custid','$custpaydate','$customeramtpaid','$paymode','$particulars')";
        $result = $conn->query($sql);
        $salepayid = $conn->insert_id;
	}
    $data1= array();
    if($result){
		$data1["status"] = 200;
		$data1["data"] = $salepayid;
		$log  = "File: sales_payments.php - Method: ".$action.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data1["status"] = 204;
		$log  = "File: sales_payments.php - Method: ".$action.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 204);
	}

	echo json_encode($data1);
}

if($action == "getAllClientCustMadePayments"){
	$headers = apache_request_headers();
	authenticate($headers);
    $clientid= $_GET["clientid"];
    $fromdt= $_GET["fromdt"];
    $todt= $_GET["todt"];
	$sql = "SELECT mcp.*,pm.paymode FROM `make_cust_payments` mcp, `paymode_master` pm WHERE mcp.`paymodeid`=pm.`paymodeid` AND `clientid`='$clientid' AND `paydate` BETWEEN '$fromdt' AND '$todt'";
	$result = $conn->query($sql);
	$tmp = array();
	$data = array();
	if($result){
		$i=0;
        while($row = $result->fetch_array())
        {
			$tmp[$i]['makecustpayid'] = $row['makecustpayid'];
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
		$log  = "File: sales_payments.php - Method: ".$action.PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data["status"] = 204;
		$log  = "File: sales_payments.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}
	echo json_encode($data);
}

if($action == "getAllFinancialYears"){
	$headers = apache_request_headers();
	authenticate($headers);
	$sql = "SELECT DISTINCT(`finanyr`) FROM `current_financialyr`";
	$result = $conn->query($sql);
	$tmp = array();
	if($result){
        $i=0;
		while($row = $result->fetch_array())
		{
			$tmp[$i]['finanyr'] = $row['finanyr'];
			$i++;
		}
		$data["status"] = 200;
		$data["data"] = $tmp;
		$log  = "File: sales_payments.php - Method: ".$action.PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data["status"] = 204;
		$log  = "File: sales_payments.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}
	echo json_encode($data);
}

if($action == "getAllCashOrderReceipts"){
	$headers = apache_request_headers();
	authenticate($headers);
    $fromdt= $_GET["fromdt"];
    $todt= $_GET["todt"];
	$sql = "SELECT op.`orderpayid`, op.`paydate`, op.`clientid`, op.`amount`, op.`paymodeid`, op.`particulars`, pm.`paymode`, cm.`name` FROM `order_payments` op, `paymode_master` pm, `client_master` cm WHERE op.`paymodeid`=pm.`paymodeid` AND op.`clientid`=cm.`clientid` AND pm.`paymode`='CASH' AND op.`paydate` BETWEEN '$fromdt' AND '$todt'";
	$result = $conn->query($sql);
	$tmp = array();
	if($result){
        $i=0;
		while($row = $result->fetch_array())
		{
			$tmp[$i]['orderpayid'] = $row['orderpayid'];
			$tmp[$i]['paydate'] = $row['paydate'];
			$tmp[$i]['clientid'] = $row['clientid'];
			$tmp[$i]['amount'] = $row['amount'];
			$tmp[$i]['paymodeid'] = $row['paymodeid'];
			$tmp[$i]['particulars'] = $row['particulars'];
			$tmp[$i]['paymode'] = $row['paymode'];
			$tmp[$i]['name'] = $row['name'];
			$i++;
		}
		$data["status"] = 200;
		$data["data"] = $tmp;
		$log  = "File: sales_payments.php - Method: ".$action.PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data["status"] = 204;
		$log  = "File: sales_payments.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}
	echo json_encode($data);
}
?>