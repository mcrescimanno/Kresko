<?php 
	/* All functions that get data from the database exist here. */

	function getallorders(){
		$query = "SELECT * FROM orders";
		return $query;
	}
	
	/* to be used only if customer login is implemented*/
	function getordersfromuser($userid){
		$query = "SELECT * FROM orders WHERE userid = '$userid';";
		return $query;
	}
	/* query to see if user is a vendor - returns list of vendors associated with user */
	function isuservendor ($userid){
		$query = "SELECT * from vendors WHERE vendors.userid = '$userid';";
		return $query;
	}
	
	/* takes in logged in user, returns query of all orders with items sold by current logged in vendor
	assumes user is a vendor!
	*/
	function getordersbyvendor($loggedinuser){
		$query = "
			SELECT DISTINCT orders.orderid, orders.creation_date, orders.address, orders.status
			FROM (
			SELECT items.itemid, items.vendorid, itemsinorders.orderid FROM items INNER JOIN itemsinorders on items.itemid = itemsinorders.itemid
			) as ordernum
			INNER JOIN orders on ordernum.orderid = orders.orderid
			INNER JOIN
			(SELECT vendors.vendorid, vendors.userid FROM vendors INNER JOIN users on vendors.userid = users.userid WHERE vendors.userid = '$loggedinuser' ) as currentvendor
			ON
			currentvendor.vendorid = ordernum.vendorid";
		return $query;
		
	}
	/* return all items associated with an existing order.
	assumes orderid is valid*/
	function getorderitems ($orderid){
		$query = "SELECT items.itemid, items.itemname, items.description, itemsinorders.price, itemsinorders.quantity, items.filepath, categories.category, categories.subcategory
		FROM itemsinorders INNER JOIN items on itemsinorders.itemid = items.itemid INNER JOIN categories on categories.catid = items.itemid WHERE orderid = '$orderid';";
		return $query;
	}
	/* return all items associated with current vendor AND order */
	function getvendororderitems ($orderid, $userid){
		//to be implemented later
	}


	/* Used in category.php */

	function getVendorItems($vendorID) {
		return "SELECT * FROM `items` 
			INNER JOIN `vendors` ON 
			vendors.vendorid = items.vendorid
			WHERE items.vendorid = '$vendorID'";
	}

	function getCategoryItems($categoryID) { 
		if (is_numeric($categoryID)) {
			return "SELECT items.filepath AS 'itemfilepath', itemid, itemname, vendorname, price FROM `items` 
					INNER JOIN `vendors` ON vendors.vendorid = items.vendorid 
					WHERE items.catid = '$categoryID'";
		} else {
			return "SELECT items.filepath AS 'itemfilepath', itemid, itemname, vendorname, price  FROM `items` 
				INNER JOIN `categories` ON categories.catid = items.catid 
				INNER JOIN `vendors` ON items.vendorid = vendors.vendorid 
				WHERE categories.category = '$categoryID'";
		}
	}

	// Used in items.php

	function getSelectItem($itemID){
		if (is_numeric($itemID)){
			return "SELECT vendors.filepath AS `vendorfilepath`, vendorname, email, items.filepath AS 'itemfilepath', itemname, price, items.description AS 'itemdescription'
					 FROM `items` 
					INNER JOIN `vendors` ON vendors.vendorid = items.vendorid 
					WHERE items.itemid = $itemID";
		}
	}
//cart in session just maps item IDs to quantity. Bag ultimately pulls info from database
	function addtocart($itemid, $qty){
		if (isset($_SESSION['cart'])){
			$cart = $_SESSION['cart'];
			
			if (!$cart[$itemid]){
				$_SESSION['cart'][$itemid] = $qty;
			}
			else{
				$_SESSION['cart'][$itemid] = $_SESSION['cart'][$itemid] + $qty;
			}
		}
		else{
			$cart = array(
				$itemid => $qty
			);
			$_SESSION['cart'] = $cart;
		}
	}
	function removefromcart($itemid){
		if (isset($_SESSION['cart'])){
			//we don't know if itemid is in cart
			if (isset($_SESSION['cart'][$itemid])){
				unset($_SESSION['cart'][$itemid]);
			}
		}
	}
	//get item info from db
	function getiteminfo ($itemid){
		$query = "SELECT * from items WHERE items.itemid = $itemid;
		";
		return $query;
	}
	/********/
	/**functionality for order checkout*/
	//create new order 
	function createorderquery ($address){
		$date = date("Y-m-d");
		$query = "INSERT INTO orders (creation_date, userid, address) VALUES ('$date', '1', '$address'));";
		return $query;
	}
	
	function additemtoorderquery($itemid, $orderid, $price, $quantity){
		$query = "INSERT INTO itemsinorders (itemid, price, quantity, orderid) VALUES ('$itemid', '$orderid', '$price', '$quantity');";
		return query;
	}
	/**Creates order from cart **/
	function createorder($address){
		$tempmysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, true);
		//create new order
		$tempresult = $mysqli2->query(createorderquery($address));
		if( $mysqli->query($sql) ) {
			//get orderid of created order
			$orderid = $tempmysqli->insert_id;
			if (isset($_SESSION['cart'])){
				$cartsession = $_SESSION['cart'];
				foreach ($cartsession as $itemid => $itemqty){
					$tempresult = $mysqli2->query(getiteminfo($itemid));
					if ($tempresult && tempresult->num_rows == 1){
						$row = $tempresult->fetch_assoc();
						$itemprice = $row['price'];
						//add items to itemorders
						$tempresult = $mysqli2->query(additemtoorderquery($itemid, $orderid, $itemprice, $itemqty))
						if (!$tempresult){
							print ("<span class='error'>Problem adding item to order! Please contact sales team at kreskosales@gmail.com.</span>");
						}
						print_r($tempresult);
					}
					else {
						print ("<span class='error'>Problem adding item to order, itemID is invalid.</span>");
					}
					
					
				}
			}
			else {
				print ("<span class='error'>Problem creating order! Please contact sales team at kreskosales@gmail.com.</span>");
			}
			
		}	
		else {
			print ("<span class='error'>Problem creating order! Please contact sales team at kreskosales@gmail.com.</span>");
		}
		$row = $tempresult->fetch_assoc();
	}
?>