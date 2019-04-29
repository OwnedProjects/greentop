<?php
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
//account.php?action=signUp
include 'conn.php';
$action = $_GET['action'];

if($action == "getAllPurchaseMastPayments"){
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

	if(count($rows)>0){
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
		header(' ', true, 200);
	}
	else{
		$data["status"] = 204;
		header(' ', true, 204);
	}
	echo json_encode($data);
}

if($action == "getAllPurchasePayments"){
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

	if(count($rows)>0){
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
		header(' ', true, 200);
	}
	else{
		$data["status"] = 204;
		header(' ', true, 204);
	}
	echo json_encode($data);
}

if($action == "addPurchasePayment"){
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
		header(' ', true, 200);
	}
	else{
		$data1["status"] = 204;
		header(' ', true, 204);
	}

	echo json_encode($data1);
}
?>