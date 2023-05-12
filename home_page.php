<?php
include_once('library/csrf.php');
include_once('library/auth.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Home Page</title>
</head>
<body>
	<h1>
		<p class="center">
			<i>Book Store</i>
		</p>
	</h1>

	<div class="nav_list">
		<p id = "navigation"></p>
	</div>

	<form method="POST" id="logout" action="authenticator.php?action=logout" >
			 <input type="hidden" name="nonce" value="<?php echo get_csrf($action); ?>"/>
    		 <input type="submit" class="logout" value="Logout" />
	</form>

	<form id="btn_lgn" method="POST">	
		<input type="hidden" name="nonce" value=" <?php echo get_csrf($action); ?>"/>
    	<input class="btn_lgn" value="Login" type="button" onclick="javascript:location.href='page_login.php'"/>
    </form>

<section id="categoryListPanel">
	<div class="category_list">
		<p class="ct"><i>Categories:</i></p>
		<ul id="categoryList"></ul>
	</div>
</section>
<section id="productPanel">
	<div class="picture" id="productImage">
	</div>
	<div class="iformation" id="productInfo">
	</div>
</section>

<section id="categoryPanel">
	<div class="prodcts_list"><i>
		<ul class="table" id="productList"></ul>
	</div>
</section>

<div id="shoppingCart" class="slist">
				<nav><p id="price"></p>
					<ul id="cartPanel"></ul>
					<form method="POST" action="https://www.sandbox.paypal.com/cgi-bin/webscr" onsubmit="return cartSubmit(this);">
						<ul id="submitPanel"></ul>
						<input type="hidden" name="cmd" value="_cart" />
						<input type="hidden" name="upload" value="1" />
						<input type="hidden" name="business" value="incredibleup-facilitator@gmail.com" />
						<input type="hidden" name="currency_code" value="USD" />
						<input type="hidden" name="charset" value="utf-8" />
						<input type="hidden" name="custom" value="0" />
						<input type="hidden" name="invoice" value="0" />
						<input type="submit" value="Checkout" />
					</form>
				</nav>
	</div>

<script type="text/javascript" src="incl/myLib.js"></script>
<script type="text/javascript">

function Click(id,option)
{
	if(localStorage.getItem(id) == null)
	{
		localStorage.setItem(id, JSON.stringify(0));
	} 

	if(option == 1)
	{
		var n = JSON.parse(localStorage.getItem(id)) + 1;
		localStorage.setItem(id, JSON.stringify(n));
	}
	else if(option == 2)
	{
		var n = JSON.parse(localStorage.getItem(id)) - 1;
		if(n == 0)
		{
			localStorage.removeItem(id);
		}
		else
		{
			localStorage.setItem(id, JSON.stringify(n));
		}
	}
	else if(option == 3)
	{
		localStorage.removeItem(id);
	}

	totalPrice = 0;

	myLib.get({action:'prod_fetchall'}, function(json){
		for (var items_list = [],
				i = 0, prod; prod = json[i]; i++) {
			if(localStorage.getItem(parseInt(prod.pid)) === null){}
			else{
				items_list.push('<li id="prod' , parseInt(prod.pid) ,'">' ,prod.name.escapeHTML() ,'  Price:  $', prod.price.escapeHTML(),'  Quantity:  ', localStorage.getItem(parseInt(prod.pid)),'   ',
						'<button id="IncreaseButton" onclick="Click(',parseInt(prod.pid),',',parseInt(1),')">+</button><button id="decreaseButton" onclick="Click(',parseInt(prod.pid),',',parseInt(2),')">-</button><button id="decreaseButton" onclick="Click(',parseInt(prod.pid),',',parseInt(3),')">x</button></li>');
			}
			totalPrice += localStorage.getItem(parseInt(prod.pid)) * prod.price;
		}
		el('cartPanel').innerHTML = items_list.join('');

		ListItems3 = [];
		ListItems3.push('Shopping List: $',parseInt(totalPrice));
		el('price').innerHTML = ListItems3.join('');
	});
}

function cartSubmit(form){

	var buyList = {};

	for (var key in localStorage){
		buyList[key] = parseInt(localStorage.getItem(key));
	}

	myLib.get({action:'prod_fetchall'}, function(json){
			count = 0;
			for (var prodItems = [],
					i = 0, prod; prod = json[i]; i++) {
				if(localStorage.getItem(parseInt(prod.pid)) === null){}
				else{
					count += 1;
					var c = prod.pid+"";
					prodItems.push('<input type="hidden" name="item_name_',parseInt(count),'" value="'+prod.name.escapeHTML()+'"/>');
					prodItems.push('<input type="hidden" name="item_n_',parseInt(count),'" value="'+c.escapeHTML()+'"/>');
					prodItems.push('<input type="hidden" name="quantity_',parseInt(count),'" value="'+localStorage.getItem(parseInt(prod.pid))+'"/>');
					prodItems.push('<input type="hidden" name="amount_',parseInt(count),'" value="'+parseFloat(prod.price)+'"/>');
				}
			}
			el('submitPanel').innerHTML = prodItems.join('');
		});

	myLib.processJSON(
		    "process_cart.php",                                     
		    {action: "handle_checkout", list:JSON.stringify(buyList)},   
		    function(returnValue){                                
				form.custom.value=returnValue.digest;
				form.invoice.value=returnValue.invoice;
				
				form.submit();
				for (var key in localStorage)                    
					localStorage.removeItem(key);
			},
		    {method:"POST"});                                            
	return false;
}

function View_Product(id)
{
	el('productList').innerHTML = '';
	
	myLib.get({action:'prod_select',pid: id}, function(json){
		
				for (var items_list1 = [], naviItems2 = [], upperCat = 1, items_list2 = [],
						i = 0, prod; prod = json[i]; i++) {
					upperCat = prod.catid;
					items_list1.push('<img src="incl/img/', parseInt(prod.pid), '.jpg"/>');
					items_list2.push('<li id="prod', parseInt(prod.pid),'">',prod.name.escapeHTML(),'</li>');
					items_list2.push('<li id="prod', parseInt(prod.pid),'">','$',prod.price.escapeHTML(),'</li>');
					items_list2.push('<li id="prod', parseInt(prod.pid),'">',prod.description.escapeHTML(),'</li>');
					items_list2.push('<button type="button" class = "iformation" onclick="Click(',parseInt(prod.pid),',',parseInt(1),')">Add</button>');
					naviItems2.push('  >>>  ','<a href="home_page.php?pid=',id,'">',prod.name.escapeHTML(),'</a>');
					}

				el('productImage').innerHTML = items_list1.join('');
				el('productInfo').innerHTML = items_list2.join('');

				var product_temp = <?php if (isset($_GET["pid"])) {echo $_GET["pid"];} else {echo -1;} ?>;
				if(product_temp == -1){
					url = "?catid=";
					url = url.concat(upperCat);
					url = url.concat("&pid=");
					url = url.concat(id);
					window.history.pushState(null, null, url);
				}

				myLib.get({action:'cat_fetch',catid: upperCat}, function(json){
					
					for (var naviItems = [],
							i = 0, cat; cat = json[i]; i++) {
						naviItems.push('<a href="home_page.php">Home</a>','  >>>  ','<a href="home_page.php?catid=',parseInt(upperCat),'">',cat.name.escapeHTML(),'</a>');	
					}
					naviItems = naviItems.concat(naviItems2);
					el('navigation').innerHTML = naviItems.join('');
				});

	});
			
	el('productPanel').show();
	el('categoryPanel').show();
			
}

function View_Category(id)
{
	el('productImage').innerHTML = '';
	el('productInfo').innerHTML = '';
	myLib.get({action:'cat_select',catid: id}, function(json){
		
		for (var items_list = [],
				i = 0, prod; prod = json[i]; i++) {
				items_list.push('<li id="prod' , parseInt(prod.pid) , '">' , '<img src="incl/img/', parseInt(prod.pid), '.jpg"/ onclick="View_Product(',parseInt(prod.pid),')">', '<p class="center" >',prod.name.escapeHTML() , ' - $', prod.price.escapeHTML(),'</p><p class="center"><button type="button" onclick="Click(',parseInt(prod.pid),',',parseInt(1),')">Add To Cart!</button></p></li>');
		}
				el('productList').innerHTML = items_list.join('');
	});

	myLib.get({action:'cat_fetch',catid: id}, function(json){
		
		for (var naviItems = [],
				i = 0, cat; cat = json[i]; i++) {
			naviItems.push('<a href="home_page.php">Home</a>','  >>>  ','<a href="home_page.php?catid=',id,'">',cat.name.escapeHTML(),'</a>');	
		}
		el('navigation').innerHTML = naviItems.join('');
	});
				
	
	var category_temp = <?php if (isset($_GET["catid"])) {echo $_GET["catid"];} else {echo -1;} ?>;
	if(category_temp == -1){
		url = "?catid=";
		url = url.concat(id);
		window.history.pushState(null, null, url);
	}

}

function category_update()
{
	

	myLib.get({action:'cat_fetchall'}, function(json){
			
			for (var items_list = [],
					i = 0, cat; cat = json[i]; i++) {
				items_list.push('<li id="cat' , parseInt(cat.catid) , '" onclick="View_Category(',parseInt(cat.catid),')"><i>' , cat.name.escapeHTML() , '</i></li>');
			}
			el('categoryList').innerHTML = items_list.join('');
		});
}

function cart_Update() {
		totalPrice = 0;

		myLib.get({action:'prod_fetchall'}, function(json){
			for (var items_list = [],
					i = 0, prod; prod = json[i]; i++) {
				if(localStorage.getItem(parseInt(prod.pid)) === null){}
				else{
					items_list.push('<li id="prod' , parseInt(prod.pid) ,'">' ,prod.name.escapeHTML() ,'  Price:  $', prod.price.escapeHTML(),'  Quantity:  ', localStorage.getItem(parseInt(prod.pid)),'   ',
							'<button id="IncreaseButton" onclick="Click(',parseInt(prod.pid),',',parseInt(1),')">+</button><button id="decreaseButton" onclick="Click(',parseInt(prod.pid),',',parseInt(2),')">-</button><button id="decreaseButton" onclick="Click(',parseInt(prod.pid),',',parseInt(3),')">x</button></li>');
				}
				totalPrice += localStorage.getItem(parseInt(prod.pid)) * prod.price;
			}
			el('cartPanel').innerHTML = items_list.join('');
			

			ListItems3 = [];
			ListItems3.push('Shopping List: $',parseInt(totalPrice));
			el('price').innerHTML = ListItems3.join('');
		});
}

(function(){

	function interface_update() {

		var product_temp = <?php if (isset($_GET["pid"])) {echo $_GET["pid"];} else {echo -1;} ?>;
		if(product_temp != -1)
		{
			View_Product(product_temp);
		}
		else
		{
			var category_temp = <?php if (isset($_GET["catid"])) {echo $_GET["catid"];} else {echo -1;} ?>;
			
			if(category_temp != -1)
			{
				View_Category(category_temp);
			}
			else{
				myLib.get({action:'prod_fetchlimit'}, function(json){
				
					var items_list;
				for (var items_list = [],
						i = 0, prod; prod = json[i]; i++) {
					
					items_list.push('<li id="prod' , parseInt(prod.pid) , '">' , '<img src="incl/img/', parseInt(prod.pid), '.jpg" onclick="View_Product(',parseInt(prod.pid),')">', '<p class="center" onclick="View_Product(',parseInt(prod.pid),')">',prod.name.escapeHTML() , ' - $', prod.price.escapeHTML(),'</p><p class="center"><button type="button" onclick="Click(',parseInt(prod.pid),',',parseInt(1),')">Add To Cart!</button></p></li>');
				}
				el('productList').innerHTML = items_list.join('');
				});
			}

		}
		cart_Update();
		category_update();
	}
	interface_update();

})();
</script>
</body>
</html>

<style>
	nav ul{display: none;color:azure}
	nav:hover ul{display:list-item;color: azure}
    div.nav_list{position: absolute; height: 9%; top: 19%;left: 14%; width:84%;
        font-family: Arial, Helvetica, sans-serif;
        color: azure; }
	p.right{text-align: right;}
	p.ct{font-size: 100%;
        top:50%;
        color: azure;
        }

	div.prodcts_list{position: absolute; height: 100%; left:14%;width:79%;top: 29%;color:azure}
	div.slist{position: absolute; top: 6%; height: 16%; left:76%; width:26%;font-size:83%;line-height: 76%;color:azure;}
	div.category_list{position: absolute; height: 31%; top: 25%; left:2%; width:13%;color:azure;}
    p.center{text-align: center;font-size:75%;line-height: 75%;color:azure;}
	div.iformation{position: absolute;top: 51%; height: 61%; left: 60%; width: 39%;color:azure;}

	div.product{display:none}
	div.area_dis{display: contents;}
	ul.table{width:100%;height:100%;margin:0;padding:0;list-style:none;overflow:auto}
	ul.table li{width:33%;height:45%;float:left;border:1px solid #CCC;
        background-color: #04AA6D;
    color: white;
    padding: 16px 32px;
    font-size: 30px;
    text-decoration: none;
    margin: 50px 5px;
    cursor: pointer;
    border-radius: 12px;}
    button{
        background-color: #04AA6D;
  border: none;
  color: white;
  padding: 16px 32px;
  font-size: 30px;
  margin: 2px 2px;
  cursor: pointer;
  border-radius: 12px;
    }
	.clear{clear: both}
	
	button.iformation{position: absolute; top: 0%; height: 5%; left: 85%; width: 7%;}
    input.btn_lgn{position: absolute; top: 5%;  left: 1%; }
	input.logout{position: absolute; top: 5%; left: 10%;}
    div.picture{position: absolute;top: 34%; left: 14%;}
    body{background-color: black;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 40px;
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
	/*
	ul.table{position: absolute; top:30%; left: 15%;width: 85%;height: 70%;margin: 0;padding: 0;list-style: none;overflow: auto}
	ul.table li{width:250px;height:300px;float:left;border:0.1px solid #CCC;overflow: auto}
	.clear{clear: both}
	*/

	img{max-width: 90%; max-height: 90%; padding-left:5%; padding-right:5%}
	body {
		color:#333;
		font-family: Centaur;
	}
</style>