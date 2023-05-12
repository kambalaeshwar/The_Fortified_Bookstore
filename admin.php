<?php
include_once('library/csrf.php');
include_once('library/auth.php');
include_once('library/db.inc.php');


if (!auth()){
	
	header('Location: page_login.php');
	exit();
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>IERG4210 Shop - Admin Panel</title>
	<link href="incl/admin.css" rel="stylesheet" type="text/css"/>
</head>

<body>
<h1>Book Store - Admin Panel</h1>
<article id="main">
<section id="categoryPanel">
	<fieldset>
		<legend>New Category</legend>
		<form id="cat_insert" method="POST" action="admin-process.php?action=cat_insert" onsubmit="return false;">
			<label for="cat_insert_name">Name</label>
			<div><input id="cat_insert_name" type="text" name="name" required="true" pattern="^[\w\- ]+$" /></div>
			<input type="hidden" name="nonce" value="<?php echo get_csrf($action); ?>"/>
			
			<input type="submit" value="Submit" />
		</form>

		<form id="logout" method="POST" action="authenticator.php?action=logout">
			<input type="hidden" name="nonce" value="<?php echo get_csrf($action); ?>"/>

    		<input type="submit" value="Logout" />
		</form>
	</fieldset>

	<!-- Generate the existing categories here -->
	<ul id="categoryList"></ul>
</section>

<section id="categoryEditPanel" class="hide">
	<fieldset>
		<legend>Editing Category</legend>
		<form id="cat_edit" method="POST" action="admin-process.php?action=cat_edit" onsubmit="return false;">
			<label for="cat_edit_name">Name</label>

			<input type="hidden" name="nonce" value="<?php echo get_csrf($action); ?>"/>

			<div><input id="cat_edit_name" type="text" name="name" required="true" pattern="^[\w\- ]+$" /></div>
			<input type="hidden" id="cat_edit_catid" name="catid" />
			<input type="submit" value="Submit" /> <input type="button" id="cat_edit_cancel" value="Cancel" />
		</form>
	</fieldset>
</section>

<section id="productPanel">
	<fieldset>
		<legend>New Product</legend>
		<form id="prod_insert" method="POST" action="admin-process.php?action=prod_insert" enctype="multipart/form-data">
			<input type="hidden" name="nonce" value="<?php echo get_csrf($action); ?>"/>

			<label for="prod_insert_catid">Category *</label>
			<div><select id="prod_insert_catid" name="catid"></select></div>

			<label for="prod_insert_name">Name *</label>
			<div><input id="prod_insert_name" type="text" name="name" required="true" pattern="^[\w\- ]+$" /></div>

			<label for="prod_insert_price">Price *</label>
			<div><input id="prod_insert_price" type="number" name="price" required="true" pattern="^[\d\.]+$" /></div>

			<label for="prod_insert_description">Description</label>
			<div><textarea id="prod_insert_description" name="description" pattern="^[\w\-, ]$"></textarea></div>

			<label for="prod_insert_name">Image *</label>
			<div><input type="file" name="file" required="true" accept="image/jpeg" /></div>

			<input type="submit" value="Submit" id="prod_insert_submit"/>
		</form>
	</fieldset>



	<!-- Generate the corresponding products here -->
	<ul id="productList"></ul>

</section>

<section id="productEditPanel" class="hide">
	<!--
		Design your form for editing a product's catid, name, price, description and image
		- the original values/image should be prefilled in the relevant elements (i.e. <input>, <select>, <textarea>, <img>)
		- prompt for input errors if any, then submit the form to admin-process.php (AJAX is not required)
	-->
	<legend>Product Editing</legend>
	<form id="prod_edit" method="POST" action="admin-process.php?action=prod_edit" enctype="multipart/form-data">
		<label for="prod_edit_catid">Category *</label>
		<div><select id="prod_edit_catid" name="catid"></select></div>

		<label for="prod_edit_name">Name *</label>
		<div><input id="prod_edit_name" type="text" name="name" required="true" pattern="^[\w\- ]+$" /></div>

		<label for="prod_edit_price">Price *</label>
		<div><input id="prod_edit_price" type="number" name="price" required="true" pattern="^[\d\.]+$" /></div>

		<label for="prod_edit_description">Description</label>
		<div><textarea id="prod_edit_description" name="description" pattern="^[\w\-, ]$"></textarea></div>

		<label for="prod_edit_name">Image *</label>
		<div><input type="file" name="file" required="true" accept="image/jpeg" /></div>

		<label for="prod_edit_pid">Pid *</label>
		<div><input id="prod_edit_pid" type="number" name="pid" required="true" pattern="^[\d\.]+$" /></div>

		<input type="submit" value="Submit" id="prod_edit_submit"/> <input type="button" id="prod_edit_cancel" value="Cancel" />
	</form>
</section>

<section id="txnTable">
	<fieldset style="width:900px">
	<legend>Lastest 50 Transaction Records</legend>
		<table id = "transTable"></table>
	</fieldset>
</section>

<div class="clear"></div>
</article>

<script type="text/javascript" src="incl/myLib.js"></script>
<script type="text/javascript">

(function(){

	function updateTrans(){
		myLib.get({action:'trans_fetch'}, function(json){
				
				
				for (var orderItems = [], i = 0, order; order = json[i]; i++) {
					orderItems.push('<tr><th width="70">',parseInt(order.oid),'</th><th width="400">',order.digest.escapeHTML(),'</th><th width="200">',order.salt.escapeHTML(),'</th><th width="200">',order.tid.escapeHTML(),'</th></tr>');
				}
				el('transTable').innerHTML = orderItems.join('');
				
			});
	}
	updateTrans();

	function updateUI() {
		myLib.get({action:'cat_fetchall'}, function(json){
			for (var options = [], listItems = [],
					i = 0, cat; cat = json[i]; i++) {
				options.push('<option value="' , parseInt(cat.catid) , '">' , cat.name.escapeHTML() , '</option>');
				listItems.push('<li id="cat' , parseInt(cat.catid) , '"><span class="name">' , cat.name.escapeHTML() , '</span> <span class="delete">[Delete]</span> <span class="edit">[Edit]</span></li>');
			}
			el('prod_insert_catid').innerHTML = '<option></option>' + options.join('');
			el('prod_edit_catid').innerHTML = '<option></option>' + options.join('');
			el('categoryList').innerHTML = listItems.join('');
		});
		el('productList').innerHTML = '';
	}
	updateUI();

	el('categoryList').onclick = function(e) {
		if (e.target.tagName != 'SPAN')
			return false;


		var target = e.target,
			parent = target.parentNode,
			id = target.parentNode.id.replace(/^cat/, ''),
			name = target.parentNode.querySelector('.name').innerHTML;

		if ('delete' === target.className) {
			confirm('Sure?') && myLib.post({action: 'cat_delete', catid: id}, function(json){
				alert('"' + name + '" is deleted successfully!');
				updateUI();
			});

		} else if ('edit' === target.className) {
			el('categoryEditPanel').show();
			el('categoryPanel').hide();

			el('cat_edit_name').value = name;
			el('cat_edit_catid').value = id;

		} 
	}


	el('cat_insert').onsubmit = function() {
		return myLib.submit(this, updateUI);
	}
	el('cat_edit').onsubmit = function() {
		return myLib.submit(this, function() {
			el('categoryEditPanel').hide();
			el('categoryPanel').show();
			updateUI();
		});
	}
	el('cat_edit_cancel').onclick = function() {
		el('categoryEditPanel').hide();
		el('categoryPanel').show();
	}

})();

(function(){

	function updateUI_prod() {


		myLib.get({action:'fetch_all_product'}, function(json){
			for (var options = [], listItems = [],
					i = 0, prod; prod = json[i]; i++) {
				var c =prod.catid + "";
				options.push('<option value="' , parseInt(prod.catid) , '">' , c.escapeHTML() , '</option>');
				listItems.push('<li id="prod' , parseInt(prod.pid) , '"><span class="name">' , prod.name.escapeHTML() , '</span> <span class="proddelete">[Delete]</span> <span class="prodedit">[Edit]</span></li>');
			}

			el('productList').innerHTML = listItems.join('');
		});
	}
	updateUI_prod();

	el('productList').onclick = function(e) {
		if (e.target.tagName != 'SPAN')
			return false;


		var target = e.target,
			parent = target.parentNode,
			id = target.parentNode.id.replace(/^prod/, ''),
			name = target.parentNode.querySelector('.name').innerHTML;

		if ('proddelete' === target.className) {
			confirm('Sure?') && myLib.post({action: 'prod_delete', pid: id}, function(json){
				alert('"' + name + '" is deleted successfully!');
				updateUI_prod();
			});

		
	} else if ('prodedit' === target.className) {
			el('productEditPanel').show();
			el('productPanel').hide();

			el('prod_edit_name').value = name;
			el('prod_edit_pid').value = id;

		} 
	}


	el('prod_insert_submit').onsubmit = function() {
		return myLib.submit(this, updateUI_prod);
	}
	el('prod_edit_submit').onsubmit = function() {
		return myLib.submit(this, function() {
			el('productEditPanel').hide();
			el('productPanel').show();
			updateUI_prod();
		});
	}
	el('prod_edit_cancel').onclick = function() {
			el('productEditPanel').hide();
		el('productPanel').show();
	}

})();

</script>
</body>
</html>
<style>
	fieldset{width:310px}
form label{clear:left;float:left;width:90px;line-height:29px}
form div{padding:10px 0}
input[type=text],input[type=number],select,textarea{width:200px}

#categoryPanel,#categoryEditPanel{float:left;width:350px;border-right:1px solid #CCC}
#productPanel,#productEditPanel{float:left;padding-left:10px}

#categoryList span{cursor:pointer;text-decoration:underline;color:#00F}
#categoryList span:hover{text-decoration:none}
#categoryList span.edit, #categoryList span.delete{float:right;padding-right:10px}

.hide{display:none}
.clear{clear:both}
body{background-color: black;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 15px;
        color: azure;}
        form {background-color:black;
            color: white;}
    input[type=text] , input[type = password]{
  width: 50%;
  padding: 12px 20px;
  font-size: 30px;
  margin: 8px 0;
  box-sizing: border-box;
  border-radius: 12px;
}
input[type=button], input[type=submit], input[type=reset] {
  background-color: #04AA6D;
  border: none;
  color: white;
  padding: 16px 32px;
  font-size: 30px;
  text-decoration: none;
  margin: 4px 2px;
  cursor: pointer;
  border-radius: 12px;
}
</style>