<?php
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
header('Access-Control-Allow-Headers: Authorization, X-Requested-With, Content-Type, Accept');
//account.php?action=signUp
include 'conn.php';
include 'jwt_helper.php';

$action = $_GET['action'];

if($action == "newProductInStock"){
	$headers = apache_request_headers();
	authenticate($headers);
    $data = json_decode(file_get_contents("php://input"));
	$prodid = $data->prodid;
	$moddt = $data->moddt;
	$openbal = $data->openbal;
	$openbaldt = $data->openbaldt;
    
    if($_SERVER['REQUEST_METHOD']=='POST'){
		$sql = "INSERT INTO `stock_master`(`prodid`, `quantity`, `lastmodifieddate`) VALUES ($prodid,'$openbal','$moddt')";
        $result = $conn->query($sql);
		$stkid = $conn->insert_id;
		
		//Add opening Stock in Stock Register
		$sqlreg = "INSERT INTO `stock_register`(`stockid`, `INorOUT`, `quantity`, `date`, `remarks`) VALUES ('$stkid','IN','$openbal','$openbaldt','Opening Balance')";
        $resultreg = $conn->query($sqlreg);
        $stkregid = $conn->insert_id;
	}
    $data1= array();
    if($result){
		$data1["status"] = 200;
		$data1["data"] = $stkid;
		$log  = "File: stock.php - Method: ".$action.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data1["status"] = 204;
		$log  = "File: stock.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}

	echo json_encode($data1);
}

if($action == "newRawMaterialInStock"){
	$headers = apache_request_headers();
	authenticate($headers);
    $data = json_decode(file_get_contents("php://input"));
	$rawmatid = $data->rawmatid;
	$moddt = $data->moddt;
	$openbal = $data->openbal;
	$openbaldt = $data->openbaldt;
    
    if($_SERVER['REQUEST_METHOD']=='POST'){
		$sql = "INSERT INTO `stock_master`(`rawmatid`, `quantity`, `lastmodifieddate`) VALUES ($rawmatid,'$openbal','$moddt')";
        $result = $conn->query($sql);
        $stkid = $conn->insert_id;
		//Add opening Stock in Stock Register
		$sqlreg = "INSERT INTO `stock_register`(`stockid`, `INorOUT`, `quantity`, `date`, `remarks`) VALUES ('$stkid','IN','$openbal','$openbaldt','Opening Balance')";
        $resultreg = $conn->query($sqlreg);
        $stkregid = $conn->insert_id;
	}
    $data1= array();
    if($result){
		$data1["status"] = 200;
		$data1["data"] = $stkid;
		$log  = "File: stock.php - Method: ".$action.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data1["status"] = 204;
		$log  = "File: stock.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}

	echo json_encode($data1);
}

if($action == "updateStockRawMaterial"){
	$headers = apache_request_headers();
	authenticate($headers);
    $data = json_decode(file_get_contents("php://input"));
    $billdt = $data->billdt;
    $addedmaterials = $data->addedmaterials;
    $remark = $data->remark;
    $purchid = $data->purchid;

    for($i=0; $i<count($addedmaterials); $i++) {
        $rawmatid=$addedmaterials[$i]->rawmatid;
        $qty=$addedmaterials[$i]->qty;
        $sql = "SELECT `stockid`,`rawmatid`,`quantity` FROM `stock_master` WHERE `rawmatid`=$rawmatid";
        $result = $conn->query($sql);
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $totalqty = floatval($row["quantity"]) + floatval($qty);

        $sqlupdt = "UPDATE `stock_master` SET `quantity`='$totalqty',`lastmodifieddate`='$billdt' WHERE `rawmatid`=$rawmatid";
        $resultqty = $conn->query($sqlupdt);

        $stkid = $row["stockid"];
        $sqlregins = "INSERT INTO `stock_register`(`stockid`, `INorOUT`, `quantity`, `date`, `remarks`) VALUES ($stkid,'IN','$qty','$billdt', '$remark')";
        $resultins = $conn->query($sqlregins);
    }

    $data1= array();
    if($result){
        $data1["status"] = 200;
        $data1["data"] = $stkid;
		$log  = "File: stock.php - Method: ".$action.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "success", NULL);
        header(' ', true, 200);
    }
    else{
        $data1["status"] = 204;
		$log  = "File: stock.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
    }
    echo json_encode($data1);
}

if($action == "updateOpeningStock"){
	$headers = apache_request_headers();
	authenticate($headers);
    $data = json_decode(file_get_contents("php://input"));
    $stockid = $data->stockid;
    $quantity = $data->quantity;
    $stkdt = $data->stkdt;
    $openbaldt = $data->openbaldt;

	// $sqlupdt = "UPDATE `stock_master` SET `quantity`='$quantity',`lastmodifieddate`='$stkdt' WHERE `stockid`=$stockid";
	// $resultqty = $conn->query($sqlupdt);
	
    if($_SERVER['REQUEST_METHOD']=='POST'){
		$sqlregupdt = "UPDATE `stock_register` SET `quantity`='$quantity',`date`='$openbaldt' WHERE `stockid`=$stockid AND `remarks`='Opening Balance'";
		$resultregqty = $conn->query($sqlregupdt);
	}

    $data1= array();
    if($result){
        $data1["status"] = 200;
        $data1["data"] = $stockid;
		$log  = "File: stock.php - Method: ".$action.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "success", NULL);
        header(' ', true, 200);
    }
    else{
        $data1["status"] = 204;
		$log  = "File: stock.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
    }
    echo json_encode($data1);
}

if($action == "getRawMatOpeningStock"){
	$headers = apache_request_headers();
	authenticate($headers);
	$rawmatid = ($_GET["rawmatid"]);
	$fromdt = ($_GET["fromdt"]);
	$todt = ($_GET["todt"]);
	$sql = "SELECT * FROM `stock_register` WHERE `remarks`='Opening Balance' AND `stockid`= (SELECT `stockid` FROM `stock_master` WHERE `rawmatid`= $rawmatid) AND `date` BETWEEN '$fromdt' AND '$todt'";
	$result = $conn->query($sql);
	$row = $result->fetch_array(MYSQLI_ASSOC);

	$tmp = array();
	$data = array();

	if($result && $row){
		$tmp['stockid'] = $row['stockid'];
		$tmp['quantity'] = $row['quantity'];
		$tmp['date'] = $row['date'];
		
		$data["status"] = 200;
		$data["data"] = $tmp;
		$log  = "File: stock.php - Method: ".$action.PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data["status"] = 204;
		$log  = "File: stock.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}

	echo json_encode($data);
}

if($action == "getProductStock"){
	$headers = apache_request_headers();
	authenticate($headers);
	$prodid = ($_GET["prodid"]);
	$sql = "SELECT * FROM `stock_master` WHERE `prodid`=$prodid";
	$result = $conn->query($sql);
	$row = $result->fetch_array(MYSQLI_ASSOC);

	$tmp = array();
	$data = array();

	if($result){
		$tmp['stockid'] = $row['stockid'];
		$tmp['prodid'] = $row['prodid'];
		$tmp['quantity'] = $row['quantity'];
		
		$data["status"] = 200;
		$data["data"] = $tmp;
		$log  = "File: stock.php - Method: ".$action.PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data["status"] = 204;
		$log  = "File: stock.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}

	echo json_encode($data);
}

if($action == "getRawMatStock"){
	$headers = apache_request_headers();
	authenticate($headers);
	$rawmatid = ($_GET["rawmatid"]);
	$sql = "SELECT * FROM `stock_master` WHERE `rawmatid`=$rawmatid";
	$result = $conn->query($sql);
	$row = $result->fetch_array(MYSQLI_ASSOC);

	$tmp = array();
	$data = array();

	if($result){
		$tmp['stockid'] = $row['stockid'];
		$tmp['rawmatid'] = $row['rawmatid'];
		$tmp['quantity'] = $row['quantity'];
		
		$data["status"] = 200;
		$data["data"] = $tmp;
		$log  = "File: stock.php - Method: ".$action.PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data["status"] = 204;
		$log  = "File: stock.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}

	echo json_encode($data);
}

if($action == "getProdOpeningStock"){
	$headers = apache_request_headers();
	authenticate($headers);
	$prodid = ($_GET["prodid"]);
	$fromdt = ($_GET["fromdt"]);
	$todt = ($_GET["todt"]);
	$sql = "SELECT * FROM `stock_register` WHERE `remarks`='Opening Balance' AND `stockid`= (SELECT `stockid` FROM `stock_master` WHERE `prodid`= $prodid) AND `date` BETWEEN '$fromdt' AND '$todt'";
	$result = $conn->query($sql);
	$row = $result->fetch_array(MYSQLI_ASSOC);

	$tmp = array();
	$data = array();

	if($result && $row){
		$tmp['stockid'] = $row['stockid'];
		$tmp['quantity'] = $row['quantity'];
		$tmp['date'] = $row['date'];
		
		$data["status"] = 200;
		$data["data"] = $tmp;
		$log  = "File: stock.php - Method: ".$action.PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data["status"] = 204;
		$log  = "File: stock.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}

	echo json_encode($data);
}

if($action == "getStockHistory"){
	$headers = apache_request_headers();
	authenticate($headers);
	$stockid = $_GET["stockid"];
	$fromdt = ($_GET["fromdt"]);
	$todt = ($_GET["todt"]);
	
	$sql = "SELECT * FROM `stock_register` WHERE `stockid`=$stockid AND `date` BETWEEN '$fromdt' AND '$todt' ORDER BY `date`,`INorOUT`";
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
			$tmp[$i]['stockregid'] = $row['stockregid'];
			$tmp[$i]['stockid'] = $row['stockid'];
			$tmp[$i]['INorOUT'] = $row['INorOUT'];
			$tmp[$i]['quantity'] = $row['quantity'];
			$tmp[$i]['date'] = $row['date'];
			$tmp[$i]['remarks'] = $row['remarks'];
			$i++;
		}
		$data["status"] = 200;
		$data["data"] = $tmp;
		$log  = "File: stock.php - Method: ".$action.PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data["status"] = 204;
		$log  = "File: stock.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}

	echo json_encode($data);

}

if($action == "getFromToStockHistory"){
	$headers = apache_request_headers();
	authenticate($headers);
	$stockid = $_GET["stockid"];
	$fromdt = ($_GET["fromdt"]);
	$todt = ($_GET["todt"]);
	$sql = "SELECT * FROM `stock_register` WHERE `stockid`=$stockid AND `date` BETWEEN '$fromdt' AND '$todt' ORDER BY `date`,`INorOUT`";
	$result = $conn->query($sql);
	$i = 0;
	$tmp = array();
	$data = array();
	while($row = $result->fetch_array())
	{
		$tmp[$i]['stockregid'] = $row['stockregid'];
		$tmp[$i]['stockid'] = $row['stockid'];
		$tmp[$i]['INorOUT'] = $row['INorOUT'];
		$tmp[$i]['quantity'] = $row['quantity'];
		$tmp[$i]['date'] = $row['date'];
		$tmp[$i]['remarks'] = $row['remarks'];
		$i++;
	}

	if($result){
		if(count($tmp)>0){
			$firststkid=floatval($tmp[0]['stockregid'])-1;
			$sql = "SELECT * FROM `stock_register` WHERE `stockregid`=$firststkid AND `stockid`=$stockid";
			$resultsel = $conn->query($sql);
			$rowsel = $resultsel->fetch_array();
			$tmpsel=array();
			// echo $firststkid."-".$rowsel['stockregid']."-".$sql;
			// exit(0);
			if($rowsel && count($rowsel)>0){
				$tmpsel['stockregid'] = $rowsel['stockregid'];
				$tmpsel['stockid'] = $rowsel['stockid'];
				$tmpsel['INorOUT'] = $rowsel['INorOUT'];
				$tmpsel['quantity'] = $rowsel['quantity'];
				$tmpsel['closingstock'] = $rowsel['closingstock'];
				$tmpsel['date'] = $rowsel['date'];
				$tmpsel['remarks'] = $rowsel['remarks'];
			}
		}
		$data["status"] = 200;
		$data["data"] = $tmp;
		$data["openstk"] = $tmpsel;
		$log  = "File: stock.php - Method: ".$action.PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data["status"] = 204;
		$log  = "File: stock.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}

	echo json_encode($data);

}

if($action == "updateDataToStockHistory"){
	$headers = apache_request_headers();
	authenticate($headers);
    $data = json_decode(file_get_contents("php://input"));
    $stockid = $data->stockid;
    $fromdt = $data->fromdt;
    $todt = $data->todt;
    $stockhist = $data->stockhist;
	
    if($_SERVER['REQUEST_METHOD']=='POST'){
		$sql = "DELETE FROM `stock_register` WHERE `stockid`=$stockid AND `date` BETWEEN '$fromdt' AND '$todt'";
		$result = $conn->query($sql);
		for($i=0; $i<count($stockhist); $i++) {
            $inout = $stockhist[$i]->inout;
            $quantity = $stockhist[$i]->quantity;
            $closingstock = $stockhist[$i]->closingstock;
            $date = $stockhist[$i]->date;
            $remarks = $stockhist[$i]->remarks;

            $sqlins="INSERT INTO `stock_register`(`stockid`, `INorOUT`, `quantity`, `closingstock`, `date`, `remarks`) VALUES ($stockid,'$inout','$quantity','$closingstock','$date','$remarks')";
            $resultqty = $conn->query($sqlins);
        }
	}
    $data1= array();
    if($result){
        $data1["status"] = 200;
        $data1["data"] = $stockid;
		$log  = "File: stock.php - Method: ".$action.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "success", NULL);
        header(' ', true, 200);
    }
    else{
        $data1["status"] = 204;
		$log  = "File: stock.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
    }
    echo json_encode($data1);
}

//Check if there is any opening balance created for current financial year
if($action == "checkRawMatOpenStockForCrntFinanYear"){
	$headers = apache_request_headers();
	authenticate($headers);
	$rawmatid = $_GET["rawmatid"];
	$fromdt = $_GET["fromdt"];
	$todt = $_GET["todt"];
	$sql = "SELECT * FROM `stock_register` WHERE `remarks`='Opening Balance' AND `stockid`= (SELECT `stockid` FROM `stock_master` WHERE `rawmatid`= $rawmatid) AND `date` BETWEEN '$fromdt' AND '$todt'";
	$result = $conn->query($sql);
	$row = $result->fetch_array(MYSQLI_ASSOC);

	$tmp = array();
	$data = array();

	if($result and $row){
		$tmp['stockid'] = $row['stockid'];
		$tmp['quantity'] = $row['quantity'];
		$tmp['date'] = $row['date'];
		
		$data["status"] = 200;
		$data["data"] = $tmp;
		$log  = "File: stock.php - Method: ".$action.PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data["status"] = 204;
		$log  = "File: stock.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}

	echo json_encode($data);
}

//Check if there is any opening balance created for current financial year
if($action == "checkProductOpenStockForCrntFinanYear"){
	$headers = apache_request_headers();
	authenticate($headers);
	$prodid = $_GET["prodid"];
	$fromdt = $_GET["fromdt"];
	$todt = $_GET["todt"];
	$sql = "SELECT * FROM `stock_register` WHERE `remarks`='Opening Balance' AND `stockid`= (SELECT `stockid` FROM `stock_master` WHERE `prodid`= $prodid) AND `date` BETWEEN '$fromdt' AND '$todt'";
	$result = $conn->query($sql);
	$row = $result->fetch_array(MYSQLI_ASSOC);

	$tmp = array();
	$data = array();

	if($result and $row){
		$tmp['stockid'] = $row['stockid'];
		$tmp['quantity'] = $row['quantity'];
		$tmp['date'] = $row['date'];
		
		$data["status"] = 200;
		$data["data"] = $tmp;
		$log  = "File: stock.php - Method: ".$action.PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data["status"] = 204;
		$log  = "File: stock.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}

	echo json_encode($data);
}

if($action == "insertOpeningStock"){
	$headers = apache_request_headers();
	authenticate($headers);
    $data = json_decode(file_get_contents("php://input"));
    $stockid = $data->stockid;
    $quantity = $data->quantity;
    $stkdt = $data->stkdt;
    $openbaldt = $data->openbaldt;

	// $sqlupdt = "UPDATE `stock_master` SET `quantity`='$quantity',`lastmodifieddate`='$stkdt' WHERE `stockid`=$stockid";
	// $resultqty = $conn->query($sqlupdt);
	
    if($_SERVER['REQUEST_METHOD']=='POST'){
		$sqlregupdt = "INSERT INTO `stock_register`(`stockid`, `INorOUT`, `quantity`, `date`, `remarks`) VALUES ($stockid,'IN','$quantity','$openbaldt','Opening Balance')";
		$resultregqty = $conn->query($sqlregupdt);
	}
	
    $data1= array();
    if($result){
        $data1["status"] = 200;
        $data1["data"] = $stockid;
		$log  = "File: stock.php - Method: ".$action.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "success", NULL);
        header(' ', true, 200);
    }
    else{
        $data1["status"] = 204;
		$log  = "File: stock.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
    }
    echo json_encode($data1);
}

if($action == "updateCurrentStock"){
	$headers = apache_request_headers();
	authenticate($headers);
    $data = json_decode(file_get_contents("php://input"));
    $stockid = $data->stockid;
    $quantity = $data->quantity;
    $openbaldt = $data->openbaldt;

    if($_SERVER['REQUEST_METHOD']=='POST'){
		$sql = "SELECT * FROM `stock_master` WHERE `stockid`=$stockid";
		$result = $conn->query($sql);
		$row = $result->fetch_array(MYSQLI_ASSOC);
		$totalqty = floatval($row["quantity"]) + floatval($quantity);
		$sqlregupdt = "UPDATE `stock_master` SET `quantity`='$totalqty',`lastmodifieddate`='$openbaldt' WHERE `stockid`=$stockid";
		$resultregqty = $conn->query($sqlregupdt);
	}
    $data1= array();
    if($result){
        $data1["status"] = 200;
        $data1["data"] = $stockid;
		$log  = "File: stock.php - Method: ".$action.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "success", NULL);
        header(' ', true, 200);
    }
    else{
        $data1["status"] = 204;
		$log  = "File: stock.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
    }
    echo json_encode($data1);
}

if($action == "updateStockUsingProdid"){
	$headers = apache_request_headers();
	authenticate($headers);
    $data = json_decode(file_get_contents("php://input"));
    $prodid = $data->prodid;
    $quantity = $data->quantity;
    $qtydt = $data->qtydt;

    if($_SERVER['REQUEST_METHOD']=='POST'){
		$sql = "SELECT * FROM `stock_master` WHERE `prodid`=$prodid";
		$result = $conn->query($sql);
		$row = $result->fetch_array(MYSQLI_ASSOC);
		$totalqty = floatval($row["quantity"]) + floatval($quantity);
		$sqlregupdt = "UPDATE `stock_master` SET `quantity`='$totalqty',`lastmodifieddate`='$qtydt' WHERE `prodid`=$prodid";
		$resultregqty = $conn->query($sqlregupdt);
	}

    $data1= array();
    if($result){
        $data1["status"] = 200;
        $data1["data"] = $row["stockid"];
		$log  = "File: stock.php - Method: ".$action.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "success", NULL);
        header(' ', true, 200);
    }
    else{
        $data1["status"] = 204;
		$log  = "File: stock.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
    }
    echo json_encode($data1);
}

if($action == "updateStockRegQuantity"){
	$headers = apache_request_headers();
	authenticate($headers);
    $data = json_decode(file_get_contents("php://input"));
    $stockid = $data->stockid;
    $quantity = $data->quantity;
    $manufacdate = $data->manufacdate;
    $newdate = $data->newdate;

    if($_SERVER['REQUEST_METHOD']=='POST'){
		$sqlregupdt = "UPDATE `stock_register` SET `quantity`='$quantity', `date`='$newdate' WHERE `stockid`=$stockid AND `date`=$manufacdate";
		$resultregqty = $conn->query($sqlregupdt);
	}

    $data1= array();
    if($resultregqty){
        $data1["status"] = 200;
        $data1["data"] = $stockid;
		$log  = "File: stock.php - Method: ".$action.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "success", NULL);
        header(' ', true, 200);
    }
    else{
        $data1["status"] = 204;
		$log  = "File: stock.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
    }
    echo json_encode($data1);
}

if($action == "getStockidFromRawMatId"){
	$headers = apache_request_headers();
	authenticate($headers);
	$rawmatid = $_GET["rawmatid"];
	$sql = "SELECT `stockid`, `quantity` FROM `stock_master` WHERE `rawmatid`=$rawmatid";
	$result = $conn->query($sql);
	$row = $result->fetch_array(MYSQLI_ASSOC);

	$tmp = array();
	$data = array();

	if($result && $row){
		$tmp['stockid'] = $row['stockid'];
		$tmp['quantity'] = $row['quantity'];
		
		$data["status"] = 200;
		$data["data"] = $tmp;
		$log  = "File: stock.php - Method: ".$action.PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data["status"] = 204;
		$log  = "File: stock.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}

	echo json_encode($data);
}

if($action == "insertStockRegister"){
	$headers = apache_request_headers();
	authenticate($headers);
    $data = json_decode(file_get_contents("php://input"));
    $stockid = $data->stockid;
    $quantity = $data->quantity;
    $openbaldt = $data->openbaldt;
    $INnOut = $data->INnOut;
    $remarks = $data->remarks;

    if($_SERVER['REQUEST_METHOD']=='POST'){
		$sqlins = "INSERT INTO `stock_register`(`stockid`, `INorOUT`, `quantity`, `date`, `remarks`) VALUES ($stockid,'$INnOut','$quantity','$openbaldt','$remarks')";
		$resultins = $conn->query($sqlins);
	}
	
    $data1= array();
    if($resultins){
        $data1["status"] = 200;
        $data1["data"] = $stockid;
		$log  = "File: stock.php - Method: ".$action.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "success", NULL);
        header(' ', true, 200);
    }
    else{
        $data1["status"] = 204;
		$log  = "File: stock.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
    }
    echo json_encode($data1);
}

if($action == "insertReprocessing"){
	$headers = apache_request_headers();
	authenticate($headers);
    $data = json_decode(file_get_contents("php://input"));
    $reprocessdt = $data->reprocessdt;
    $fromprodid = $data->fromprodid;
    $toprodid = $data->toprodid;
    $quantity = $data->quantity;

    if($_SERVER['REQUEST_METHOD']=='POST'){
		$sqlins = "INSERT INTO `reprocessed_product`(`reprocessdt`, `fromprodid`, `toprodid`, `quantity`) VALUES ('$reprocessdt', '$fromprodid', '$toprodid', '$quantity')";
		$resultins = $conn->query($sqlins);
		$procesid=$conn->insert_id;
	}
	
    $data1= array();
    if($resultins){
        $data1["status"] = 200;
        $data1["data"] = $procesid;
		$log  = "File: stock.php - Method: ".$action.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "success", NULL);
        header(' ', true, 200);
    }
    else{
        $data1["status"] = 204;
		$log  = "File: stock.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
    }
    echo json_encode($data1);
}

if($action == "allreprocessdata"){
	$headers = apache_request_headers();
	authenticate($headers);
	$sql = "select _t1.`reprocessid`, _t1.`reprocessdt`, _t1.`quantity`, _t1.`fromprodid`, _t1.`toprodid`, _t2.prodname as fname, _t3.prodname as tname
	from `reprocessed_product` _t1
	inner join `product_master` _t2  on _t1.`fromprodid` = _t2.prodid
	inner join `product_master` _t3  on _t1.`toprodid` = _t3.prodid
	ORDER BY _t1.`reprocessdt`";
	$result = $conn->query($sql);
	while($row = $result->fetch_array())
	{
		$rows[] = $row;
	}

	$tmp = array();
	$data = array();
	$i = 0;

	$tmp = array();
	$data1 = array();

	if(count($rows)>0){
		foreach($rows as $row)
		{
			$tmp[$i]['reprocessid'] = $row['reprocessid'];
			$tmp[$i]['reprocessdt'] = $row['reprocessdt'];
			$tmp[$i]['quantity'] = $row['quantity'];
			$tmp[$i]['fromprodid'] = $row['fromprodid'];
			$tmp[$i]['toprodid'] = $row['toprodid'];
			$tmp[$i]['fname'] = $row['fname'];
			$tmp[$i]['tname'] = $row['tname'];
			$i++;
		}
		$data1["status"] = 200;
		$data1["data"] = $tmp;
		$log  = "File: stock.php - Method: ".$action.PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data1["status"] = 204;
		$log  = "File: stock.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data1).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}

	echo json_encode($data1);
}

if($action == "updateStockDetails"){
	$headers = apache_request_headers();
	authenticate($headers);
    $data = json_decode(file_get_contents("php://input"));
	$stockregid = $data->stockregid;
	$quantity = $data->quantity;
	$remarks = mysqli_real_escape_string($conn,$data->remarks);

    if($_SERVER['REQUEST_METHOD']=='POST'){
		$sql = "UPDATE `stock_register` SET `quantity`='$quantity',`remarks`='$remarks' WHERE `stockregid`=$stockregid";
        $result = $conn->query($sql);
	}
    $data1= array();
    if($result){
		$data1["status"] = 200;
		$data1["data"] = $stockregid;
		$log  = "File: stock.php - Method: $action".PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "success", NULL);
		header(' ', true, 200);
	}
	else{
		$data1["status"] = 204;
		$log  = "File: stock.php - Method: $action".PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
	}

	echo json_encode($data1);
}

if($action == "updateStockQuantity"){
	$headers = apache_request_headers();
	authenticate($headers);
    $data = json_decode(file_get_contents("php://input"));
    $stockid = $data->stockid;
    $quantity = $data->quantity;

    if($_SERVER['REQUEST_METHOD']=='POST'){
		$sqlregupdt = "UPDATE `stock_master` SET `quantity`='$quantity' WHERE `stockid`=$stockid";
		$resultregqty = $conn->query($sqlregupdt);
	}
    $data1= array();
    if($resultregqty){
        $data1["status"] = 200;
        $data1["data"] = $stockid;
		$log  = "File: stock.php - Method: ".$action.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "success", NULL);
        header(' ', true, 200);
    }
    else{
        $data1["status"] = 204;
		$log  = "File: stock.php - Method: ".$action.PHP_EOL.
		"Error message: ".$conn->error.PHP_EOL.
		"Data: ".json_encode($data).PHP_EOL;
		write_log($log, "error", $conn->error);
		header(' ', true, 204);
    }
    echo json_encode($data1);
}
?>