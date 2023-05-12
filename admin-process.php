<?php
include_once('library/db.inc.php');
include_once('library/auth.php');

function validate()
{
	if (!auth()){
		
		header('Location: page_login.php');
		exit();
	}
}

function csce5560_prod_fetchlimit() {
	
	global $sql1;
	$sql1 = csce5560_DB();
	$q = $sql1->prepare("SELECT * FROM products LIMIT 6;");
	if ($q->execute())
		return $q->fetchAll();
}

function csce5560_prod_fetchall() {

	global $sql1;
	$sql1 = csce5560_DB();
	$q = $sql1->prepare("SELECT * FROM products LIMIT 100;");
	if ($q->execute())
		return $q->fetchAll();
}

function csce5560_cat_fetchall() {
	
	global $sql1;
	$sql1 = csce5560_DB();
	$q = $sql1->prepare("SELECT * FROM categories LIMIT 100;");
	if ($q->execute())
		return $q->fetchAll();
}

function csce5560_cat_fetch() {
	
	$_GET['catid'] = (int) $_GET['catid'];
	global $sql1;
	$sql1 = csce5560_DB();
	$q = $sql1->prepare("SELECT * FROM categories WHERE catid=?;");
	if ($q->execute(array($_GET['catid'])))
		return $q->fetchAll();
}

function csce5560_cat_select() {
	
	$_GET['catid'] = (int) $_GET['catid'];
	global $sql1;
	$sql1 = csce5560_DB();
	$q = $sql1->prepare("SELECT * FROM products WHERE catid=?;");
	if ($q->execute(array($_GET['catid'])))
		return $q->fetchAll();
}

function csce5560_prod_select() {
	
	$_GET['pid'] = (int) $_GET['pid'];
	global $sql1;
	$sql1 = csce5560_DB();
	$q = $sql1->prepare("SELECT * FROM products WHERE pid=?;");
	if ($q->execute(array($_GET['pid'])))
		return $q->fetchAll();
}

function csce5560_fetch_prod() {
	$array = json_decode($_POST['list_of_pid']);
	global $sql1;
	$sql1 = csce5560_DB();
	$a = sprintf('SELECT name, price, pid FROM products WHERE pid IN (%s);',implode(',',array_fill(1, count($array), '?'))); 
	
	$q = $sql1->prepare($a);
	if ($q->execute($array))
		return $q->fetchAll();
}

function csce5560_cat_insert() {
	validate();
	

    if (!preg_match('/^[\w\- ]+$/', $_POST['name']))
		throw new Exception("invalid-name");

	
	global $sql1;
	$sql1 = csce5560_DB();
	$q = $sql1->prepare("INSERT INTO categories (name) VALUES (?)");
	return $q->execute(array($_POST['name']));
}

function csce5560_cat_edit() {
	validate();
	if (!preg_match('/^[\w\- ]+$/', $_POST['name']))
		throw new Exception("invalid-name");

  $_POST['catid']=(int)$_POST['catid'];

	global $sql1;
	$sql1 = csce5560_DB();
	$q = $sql1->prepare("UPDATE categories SET name = ? WHERE catid = ?");
	return $q->execute(array($_POST['name'], $_POST['catid']));
}

function csce5560_cat_delete() {
	validate();
	
	$_POST['catid'] = (int) $_POST['catid'];

	
	global $sql1;
	$sql1 = csce5560_DB();
	$q = $sql1->prepare("DELETE FROM categories WHERE catid = ?");
	return $q->execute(array($_POST['catid']));
}

function csce5560_prod_insert() {
	validate();
	
	global $sql1;
	$sql1 = csce5560_DB();


	
	if (!preg_match('/^\d*$/', $_POST['catid']))
		throw new Exception("invalid-catid");
	$_POST['catid'] = (int) $_POST['catid'];
	if (!preg_match('/^[\w\- ]+$/', $_POST['name']))
		throw new Exception("invalid-name");
	if (!preg_match('/^[\d\.]+$/', $_POST['price']))
		throw new Exception("invalid-price");
	if (!preg_match('/^[\w\- ]+$/', $_POST['description']))
		throw new Exception("invalid-textt");

	$sql="INSERT INTO products (catid, name, price, description) VALUES (?, ?, ?, ?)";
	$q = $sql1->prepare($sql);
	if ($_FILES["file"]["error"] == 0
		&& $_FILES["file"]["type"] == "image/jpeg"
		&& mime_content_type($_FILES["file"]["tmp_name"]) == "image/jpeg"
		&& $_FILES["file"]["size"] < 5000000) {

		$q->execute(array($_POST['catid'],$_POST['name'],$_POST['price'],$_POST['description']));
		$lastId = $sql1->lastInsertId();
		if (move_uploaded_file($_FILES["file"]["tmp_name"], "/incl/img/" . $lastId . ".jpg")) {
		
			header('Location: admin.php');
			exit();
		}
	}
	}



function csce5560_prod_edit() {
	validate();
	global $sql1;
	$sql1 = csce5560_DB();

	if (!preg_match('/^\d*$/', $_POST['catid']))
		throw new Exception("invalid-catid");
	$_POST['catid'] = (int) $_POST['catid'];
	$_POST['pid']=(int)$_POST['pid'];
	if (!preg_match('/^[\w\- ]+$/', $_POST['name']))
		throw new Exception("invalid-name");
	if (!preg_match('/^[\d\.]+$/', $_POST['price']))
		throw new Exception("invalid-price");
	$_POST['price']=(int)$_POST['price'];
	if (!preg_match('/^[\w\- ]+$/', $_POST['description']))
		throw new Exception("invalid-textt");

	$sql="UPDATE products SET catid=?, name=?, price=?, description=? WHERE pid=?";
	$q = $sql1->prepare($sql);
	if ($_FILES["file"]["error"] == 0
		&& $_FILES["file"]["type"] == "image/jpeg"
		&& mime_content_type($_FILES["file"]["tmp_name"]) == "image/jpeg"
		&& $_FILES["file"]["size"] < 5000000) {

		$q->execute(array($_POST['catid'],$_POST['name'],$_POST['price'],$_POST['description'],$_POST['pid']));
		if (move_uploaded_file($_FILES["file"]["tmp_name"], "/incl/img/" . $_POST['pid'] . ".jpg")) {
		
			header('Location: admin.php');
			exit();
		}
	}
	}

function csce5560_prod_delete() {

	validate();
	$_POST['pid'] = (int) $_POST['pid'];

	global $sql1;
	$sql1 = csce5560_DB();
	$q = $sql1->prepare("DELETE FROM products WHERE pid = ?");
	return $q->execute(array($_POST['pid']));
}

function csce5560_trans_fetch(){
	validate();
	
	global $sql1;
	$sql1 = db_CARTORDER();
	$q = $sql1->prepare("SELECT * FROM orders LIMIT 50;");
	if ($q->execute())
		return $q->fetchAll();
}

header('Content-Type: application/json');

if (empty($_REQUEST['action']) || !preg_match('/^\w+$/', $_REQUEST['action'])) {
	echo json_encode(array('failed'=>'undefined'));
	exit();
}

try {
	if (($returnVal = call_user_func('csce5560_' . $_REQUEST['action'])) === false) {
		if ($sql1 && $sql1->errorCode())
			error_log(print_r($sql1->errorInfo(), true));
		echo json_encode(array('failed'=>'1'));
	}
	echo 'while(1);' . json_encode(array('success' => $returnVal));
} catch(PDOException $e) {
	error_log($e->getMessage(),0);
	echo json_encode(array('failed'=>'error-db'));
} catch(Exception $e) {
	echo 'while(1);' . json_encode(array('failed' => $e->getMessage()));
}
?>
