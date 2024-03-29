<?php
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
header('Access-Control-Allow-Headers: Authorization, X-Requested-With, Content-Type, Accept');
//account.php?action=signUp
include 'conn.php';
include 'jwt_helper.php';

$action = $_GET['action'];

if($action == "addRawMaterial"){
	$headers = apache_request_headers();
	authenticate($headers);
    $data = json_decode(file_get_contents("php://input"));
    $name = $data->name;
    $hsncode = $data->hsncode;
    
	if($_SERVER['REQUEST_METHOD']=='POST'){
		$sql = "INSERT INTO `raw_material_master`(`name`, `hsncode`) VALUES ('$name', '$hsncode')";
        $result = $conn->query($sql);
        $rawmatid = $conn->insert_id;
	}
    $data1= array();
    if($result){
		$data1["status"] = 200;
		$data1["data"] = $rawmatid;
		$log  = "File: rawmaterial.php - Method: ".$action.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data1["status"] = 204;
		$log  = "File: rawmaterial.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}

	echo json_encode($data1);
}

if($action == "getRawMaterials"){
	$headers = apache_request_headers();
	authenticate($headers);
	$sql = "SELECT * FROM `raw_material_master` ORDER BY `name`";
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
			$tmp[$i]['rawmatid'] = $row['rawmatid'];
			$tmp[$i]['name'] = $row['name'];
			$tmp[$i]['hsncode'] = $row['hsncode'];
			$i++;
		}
		$data["status"] = 200;
		$data["data"] = $tmp;
		$log  = "File: rawmaterial.php - Method: ".$action.PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data["status"] = 204;
		$log  = "File: rawmaterial.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}

	echo json_encode($data);
}

if($action == "updateRawMaterial"){
	$headers = apache_request_headers();
	authenticate($headers);
    $data = json_decode(file_get_contents("php://input"));
    $rawmatid = $data->rawmatid;
    $name = $data->name;
    $hsncode = $data->hsncode;
    
	if($_SERVER['REQUEST_METHOD']=='POST'){
		$sql = "UPDATE `raw_material_master` SET `name`='$name', `hsncode`='$hsncode' WHERE `rawmatid`=$rawmatid";
        $result = $conn->query($sql);
	}
    $data1= array();
    if($result){
		$data1["status"] = 200;
		$data1["data"] = $rawmatid;
		$log  = "File: rawmaterial.php - Method: ".$action.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data1["status"] = 204;
		$log  = "File: rawmaterial.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}

	echo json_encode($data1);
}

if($action == "purchaseRawMaterial"){
	$headers = apache_request_headers();
	authenticate($headers);
    $data = json_decode(file_get_contents("php://input"));
    $clientid = $data->clientid;
    $vehicalno = $data->vehicalno;
    $dcno = $data->dcno;
    $billno = $data->billno;
    $billdt = $data->billdt;
    $arrivaldt = $data->arrivaldt;
    $totaldiscount = $data->totaldiscount;
    $totalamt = $data->totalamt;
    $addedmaterials = $data->addedmaterials;
    
	if($_SERVER['REQUEST_METHOD']=='POST'){
		$sql = "INSERT INTO `purchase_master`(`clientid`, `vehicalno`, `dcno`, `billno`, `billdt`, `arrivaldt`, `totaldiscount`, `totalamount`) VALUES ($clientid,'$vehicalno','$dcno','$billno','$billdt','$arrivaldt','$totaldiscount','$totalamt')";
        $result = $conn->query($sql);
		$purcmastid = $conn->insert_id;
		
		if($result){
			for($i=0; $i<count($addedmaterials); $i++) {
				$rawmatid=$addedmaterials[$i]->rawmatid;
				$qty=$addedmaterials[$i]->qty;
				$rate=$addedmaterials[$i]->rate;
				$cgst=$addedmaterials[$i]->cgst;
				$sgst=$addedmaterials[$i]->sgst;
				$igst=$addedmaterials[$i]->igst;
				$amount=$addedmaterials[$i]->amount;
				$discount=$addedmaterials[$i]->discount;
				$roundoff=$addedmaterials[$i]->roundoff;

				$sqlins="INSERT INTO `purchase_register`(`purcmastid`, `rawmatid`, `quantity`, `rate`, `cgst`, `sgst`, `igst`, `discount`, `roundoff`,`amount`) VALUES ($purcmastid,$rawmatid,'$qty','$rate','$cgst','$sgst','$igst','$discount','$roundoff','$amount')";
				$resultins = $conn->query($sqlins);
			}
		}
	}
    $data1= array();
    if($result){
		$data1["status"] = 200;
		$data1["data"] = $purcmastid;
		$log  = "File: rawmaterial.php - Method: ".$action.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data1["status"] = 204;
		$log  = "File: rawmaterial.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}

	echo json_encode($data1);
}

if($action == "getPurchaseDetails"){
	$headers = apache_request_headers();
	authenticate($headers);
	$purcmastid = $_GET["purcmastid"];
	$sql = "SELECT pm.`purcmastid`, pm.`clientid`, pm.`vehicalno`, pm.`dcno`, pm.`billno`, pm.`billdt`, pm.`arrivaldt`, pm.`totaldiscount`, pm.`totalamount`, cm.`name`, pr.`rawmatid`, pr.`quantity`, pr.`rate`, pr.`cgst`, pr.`sgst`, pr.`igst`, pr.`amount`, pr.`discount`, pr.`roundoff`, rmm.`name` as `rawmatname` FROM `purchase_master` pm, `purchase_register` pr, `client_master` cm, `raw_material_master` rmm WHERE pm.`purcmastid` = pr.`purcmastid` AND pm.`clientid` = cm.`clientid` AND pr.`rawmatid`=rmm.`rawmatid` AND pm.`purcmastid`=$purcmastid";
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
			$tmp[$i]['name'] = $row['name'];
			$tmp[$i]['rawmatname'] = $row['rawmatname'];
			$tmp[$i]['rawmatid'] = $row['rawmatid'];
			$tmp[$i]['quantity'] = $row['quantity'];
			$tmp[$i]['rate'] = $row['rate'];
			$tmp[$i]['cgst'] = $row['cgst'];
			$tmp[$i]['sgst'] = $row['sgst'];
			$tmp[$i]['igst'] = $row['igst'];
			$tmp[$i]['amount'] = $row['amount'];
			$tmp[$i]['discount'] = $row['discount'];
			$tmp[$i]['roundoff'] = $row['roundoff'];
			$i++;
		}
		$data["status"] = 200;
		$data["data"] = $tmp;
		$log  = "File: rawmaterial.php - Method: ".$action.PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data["status"] = 204;
		$log  = "File: rawmaterial.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}

	echo json_encode($data);
}

if($action == "updatePurchasesRawMaterial"){
	$headers = apache_request_headers();
	authenticate($headers);
    $data = json_decode(file_get_contents("php://input"));
    $purcmastid = $data->purcmastid;
    $clientid = $data->clientid;
    $vehicalno = $data->vehicalno;
    $dcno = $data->dcno;
    $billno = $data->billno;
    $billdt = $data->billdt;
    $arrivaldt = $data->arrivaldt;
    $totaldiscount = $data->totaldiscount;
    $totalamt = $data->totalamt;
    $addedmaterials = $data->addedmaterials;
    
	if($_SERVER['REQUEST_METHOD']=='POST'){
		$sql = "UPDATE `purchase_master` SET `clientid`=$clientid,`vehicalno`='$vehicalno',`dcno`='$dcno',`billno`='$billno',`billdt`='$billdt',`arrivaldt`='$arrivaldt',`totaldiscount`='$totaldiscount',`totalamount`='$totalamt' WHERE `purcmastid`=$purcmastid";
        $result = $conn->query($sql);
		
		if($result){
			//for($i=0; $i<count($addedmaterials); $i++) {
				$rawmatid=$addedmaterials[0]->rawmatid;
				$qty=$addedmaterials[0]->qty;
				$rate=$addedmaterials[0]->rate;
				$cgst=$addedmaterials[0]->cgst;
				$sgst=$addedmaterials[0]->sgst;
				$igst=$addedmaterials[0]->igst;
				$amount=$addedmaterials[0]->amount;
				$discount=$addedmaterials[0]->discount;
				$roundoff=$addedmaterials[0]->roundoff;

				$sqlins="UPDATE `purchase_register` SET `rawmatid`=$rawmatid,`quantity`='$qty',`rate`='$rate',`cgst`='$cgst',`sgst`='$sgst',`igst`='$igst',`discount`='$discount',`amount`='$amount', `roundoff`='$roundoff' WHERE `purcmastid`=$purcmastid";
				$resultins = $conn->query($sqlins);
			//}
		}
	}
    $data1= array();
    if($result){
		$data1["status"] = 200;
		$data1["data"] = $purcmastid;
		$log  = "File: rawmaterial.php - Method: ".$action.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data1["status"] = 204;
		$log  = "File: rawmaterial.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}

	echo json_encode($data1);
}

if($action == "updateRawMaterialsAndStocks"){
	$headers = apache_request_headers();
	authenticate($headers);
    $data = json_decode(file_get_contents("php://input"));
	$prodregid=$data->prodregid;
	$rawmatid=$data->rawmatid;
	$rawmatqty=$data->rawmatqty;
	$stockid=$data->stockid;
	$changeqty=$data->changeqty;
	$stkdate=$data->stkdate;
	$newdate=$data->newdate;
    
	if($_SERVER['REQUEST_METHOD']=='POST'){
		// Update Production Batch Register
		$sqlprodbatupd = "UPDATE `production_batch_register` SET `rawmatqty`='$rawmatqty' WHERE `prodregid`=$prodregid";
		$resultprodbatupt = $conn->query($sqlprodbatupd);
		
		// Get latest quantity from Stock Master 
		$sql = "SELECT `quantity` FROM `stock_master` WHERE `stockid`=$stockid";
		$result = $conn->query($sql);
		$row = $result->fetch_array(MYSQLI_ASSOC);
		$totalqty = floatval($row["quantity"]) - floatval($changeqty);

		// Update Stock Master
		$sqlstkmstupd = "UPDATE `stock_master` SET `quantity`='$totalqty',`lastmodifieddate`='$newdate' WHERE `stockid`=$stockid";
		$resultstkmstupt = $conn->query($sqlstkmstupd);

		// Update Stock Register
		$sqlstkregupd = "UPDATE `stock_register` SET `quantity`='$rawmatqty',`date`='$newdate' WHERE `INorOUT`='OUT' AND `stockid`=$stockid AND `date`=$stkdate";
		$resultstkregupt = $conn->query($sqlstkregupd);
	}

    $data1= array();
    if($result){
		$data1["status"] = 200;
		$data1["data"] = $stockid;
		$log  = "File: rawmaterial.php - Method: ".$action.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data1["status"] = 204;
		$log  = "File: rawmaterial.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}

	echo json_encode($data1);
}

if($action == "checkPurchaseBillNoIfPresent"){
	$headers = apache_request_headers();
	authenticate($headers);
	$billno = ($_GET["billno"]);
	$sql = "SELECT * FROM `purchase_master` WHERE `billno`='$billno'";
	$result = $conn->query($sql);
	$row = $result->fetch_array(MYSQLI_ASSOC);

	$tmp = array();
	$data = array();

	if($result && $row){
		$tmp['billno'] = $row['billno'];
		$tmp['billdt'] = $row['billdt'];
		
		$data["status"] = 200;
		$data["data"] = $tmp;
		$log  = "File: rawmaterial.php - Method: ".$action.PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data["status"] = 204;
		$log  = "File: rawmaterial.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}

	echo json_encode($data);
}

if($action == "checkPurchaseDCNoIfPresent"){
	$headers = apache_request_headers();
	authenticate($headers);
	$dcno = ($_GET["dcno"]);
	$sql = "SELECT * FROM `purchase_master` WHERE `dcno`=$dcno";
	$result = $conn->query($sql);
	$row = $result->fetch_array(MYSQLI_ASSOC);

	$tmp = array();
	$data = array();

	if($result && $row){
		$tmp['dcno'] = $row['dcno'];
		$tmp['billdt'] = $row['billdt'];
		
		$data["status"] = 200;
		$data["data"] = $tmp;
		$log  = "File: rawmaterial.php - Method: ".$action.PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data["status"] = 204;
		$log  = "File: rawmaterial.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}

	echo json_encode($data);
}

if($action == "deletePurchase"){
	$headers = apache_request_headers();
	authenticate($headers);
    $data = json_decode(file_get_contents("php://input"));
    $purcmastid = $data->purcmastid;
    
	if($_SERVER['REQUEST_METHOD']=='POST'){
		$sql = "DELETE FROM `purchase_master` WHERE `purcmastid`=$purcmastid";
        $result = $conn->query($sql);
		$sqldel = "DELETE FROM `purchase_register` WHERE `purcmastid`=$purcmastid";
        $resultdel = $conn->query($sqldel);
	}
    $data1= array();
    if($result){
		$data1["status"] = 200;
		$data1["data"] = $purcmastid;
		$log  = "File: rawmaterial.php - Method: ".$action.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data1["status"] = 204;
		$log  = "File: rawmaterial.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}

	echo json_encode($data1);
}

if($action == "addPurchaseReturns"){
	$headers = apache_request_headers();
	authenticate($headers);
    $data = json_decode(file_get_contents("php://input"));
    $purcmastid = $data->purcmastid;
    $debitnoteno = $data->debitnoteno;
    $returnsdate = $data->returnsdate;
    $quantity = $data->quantity;
    $amount = $data->amount;
    $particulars = mysqli_real_escape_string($conn,$data->particulars);
    
	if($_SERVER['REQUEST_METHOD']=='POST'){
		$sql = "INSERT INTO `purchase_returns`(`purcmastid`, `debitnoteno`, `returnsdate`, `quantity`, `amount`, `particulars`) VALUES ($purcmastid,'$debitnoteno','$returnsdate','$quantity','$amount','$particulars')";
        $result = $conn->query($sql);
        $returnsid = $conn->insert_id;
	}
    $data1= array();
    if($result){
		$data1["status"] = 200;
		$data1["data"] = $returnsid;
		$log  = "File: rawmaterial.php - Method: ".$action.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data1["status"] = 204;
		$log  = "File: rawmaterial.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}

	echo json_encode($data1);
}
?>