<?php
require_once "autoloader.php";
spl_autoload_register('autoloader');

$pdo = new PDO("sqlite:./database.db");

$pdo->query("CREATE TABLE IF NOT EXISTS shop (
    shopID INTEGER,
    'name' VARCHAR,
    address VARCHAR,
    PRIMARY KEY (shopID AUTOINCREMENT)
)");

$pdo->query("CREATE TABLE IF NOT EXISTS product (
    productID INTEGER,
    productName VARCHAR,
    productPrice INT,
    productCount INT,
    PRIMARY KEY (productID AUTOINCREMENT)
)");

$pdo->query("CREATE TABLE IF NOT EXISTS client (
    clientID INTEGER,
    clientName VARCHAR,
    phone VARCHAR,
    birthday DATE,
    PRIMARY KEY (clientID AUTOINCREMENT)
)");

$pdo->query("CREATE TABLE IF NOT EXISTS 'order' (
    orderID INTEGER,
    shopID INT,
    clientID INT,
    created_at DATE,
    FOREIGN KEY (shopID) REFERENCES shop (shopID),
    FOREIGN KEY (clientID) REFERENCES client (clientID),
    PRIMARY KEY (orderID AUTOINCREMENT)
)");

$pdo->query("CREATE TABLE IF NOT EXISTS 'order_product' (
    id INTEGER,
    orderID INT,
    productID INT,
    price INT,
    FOREIGN KEY (orderID) REFERENCES 'order' (orderID),
    FOREIGN KEY (productID) REFERENCES product (productID),
    PRIMARY KEY (id AUTOINCREMENT)
)");

$sth = $pdo->query("SELECT SUM(price) as order_price, client.clientName FROM 'order_product' 
         JOIN 'order' ON order_product.orderID='order'.orderID
         JOIN client ON client.clientID='order'.clientID
		 WHERE ABS(strftime('%d', created_at)-strftime('%d', birthday)) < 3
         GROUP BY (client.clientName) ");
$rows = $sth->fetchAll();
foreach ($rows as $row){
    echo $row["clientName"] . "|".  $row['order_price'] . PHP_EOL;
}
echo "----------------------------------------------------------------------------------------------------------" . PHP_EOL;
$sth = $pdo->query("SELECT order_product.price - product.productPrice as difference, order_product.orderID FROM 'order_product' 
         JOIN product ON order_product.productID=product.productID
		 WHERE order_product.price <> product.productPrice
         GROUP BY (order_product.orderID) ");
$rows = $sth->fetchAll();
foreach ($rows as $row){
    echo  "Заказ " . $row["orderID"] . "|".  $row['difference'] . PHP_EOL;
}
echo "----------------------------------------------------------------------------------------------------------" . PHP_EOL;
$sth = $pdo->query("SELECT SUM(price) as order_price, 'order'.shopID, shop.name FROM 'order_product' 
         JOIN 'order' ON order_product.orderID='order'.orderID
		 JOIN shop ON shop.shopID='order'.shopID
         GROUP BY 'order'.shopID
		 ORDER BY order_price DESC
		 limit 1; ");
$rows = $sth->fetchAll();
echo "Самый прибыльный магазин:";
foreach ($rows as $row){
    echo $row["name"] . "|".  $row['order_price'] . PHP_EOL;
}
$sth = $pdo->query("SELECT COUNT('order'.orderID) as orders, 'order'.shopID, shop.name FROM 'order_product' 
         JOIN 'order' ON order_product.orderID='order'.orderID
		 JOIN shop ON shop.shopID='order'.shopID
         GROUP BY ('order'.shopID)
		 ORDER BY orders DESC
		 limit 1; ");
$rows = $sth->fetchAll();
echo "Наибольшее число покупок в магазине:";
foreach ($rows as $row){
    echo $row["name"] . "|".  $row['orders'] . PHP_EOL;
}
