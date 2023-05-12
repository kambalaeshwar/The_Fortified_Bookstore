<?php
include_once('library/db.inc.php');
include_once('library/auth.php');



function csce5560_handle_checkout(){

	if (!$_SESSION['commontoken']){
		throw new Exception('Please login first.');
	}

	$list=$_REQUEST['list'];
	
	$list=str_replace("{", "", $list);
	$list=str_replace("}", "", $list);
	$list=str_replace("\"","", $list);
	$list_combine=str_replace(":",",", $list);
	$list_pid_qty = explode(',', $list_combine);
	$pid=array();
	$qty=array();
	for ($i=0,$j=0;$i<count($list_pid_qty);$i+=2,$j++){
		$pid[$j]=$list_pid_qty[$i];                      
		$qty[$j]=$list_pid_qty[$i+1];                   
	}
	
	global $db;
	$db = csce5560_DB();
	$pid_list = implode(',', $pid);
	$a = sprintf('SELECT name, price, pid FROM products WHERE pid IN (%s);',implode(',',array_fill(1, count($pid), '?'))); 

	$q = $db->prepare($a);
	if ($q->execute($pid))
		$products=$q->fetchAll(); 
	$priceStr="";
	$totalPrice=0;
	$i=0;
	foreach($products as $pro){
		$priceStr=$priceStr.($pro["price"]*$qty[$i]).",";
		$totalPrice+=$pro["price"]*$qty[$i++];
	}
	$i=null;
	$priceStr=substr_replace($priceStr, "", strlen($priceStr)-1, 1); 
	$Currency="USD";
	$MerEmail="incredibleup-facilitator@gmail.com";
	$salt=mt_rand() . mt_rand();
	$digest=sha1($Currency. $MerEmail. $salt. $list_combine.'|'. $priceStr.'|'. $totalPrice); 
	

	$db = db_CARTORDER();
	$q = $db->prepare("INSERT INTO orders (digest, salt, tid) VALUES (?, ?, ?)");
	$q->execute(array($digest, $salt, "notyet")); 
	$invoice=$db->lastInsertId();

	
	$returnValue=array("digest"=>$digest, "invoice"=>$invoice);
	return $returnValue;
}

header('Content-Type: application/json');


if (empty($_REQUEST['action']) || !preg_match('/^\w+$/', $_REQUEST['action'])) {
	echo json_encode(array('failed'=>'undefined'));
	exit();
}


try {
	if (($returnVal = call_user_func('csce5560_' . $_REQUEST['action'])) === false) {
		if ($db && $db->errorCode()) 
			error_log(print_r($db->errorInfo(), true));
		echo json_encode(array('failed'=>'1'));
	}
	echo 'while(1);' . json_encode(array('success' => $returnVal));
} catch(PDOException $e) {
	error_log($e->getMessage());
	echo json_encode(array('failed'=>'error-db'));
} catch(Exception $e) {
	echo 'while(1);' . json_encode(array('failed' => $e->getMessage()));
}
?>