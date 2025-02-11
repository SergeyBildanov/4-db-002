<?php
require_once "autoloader.php";
spl_autoload_register('autoloader');

$pdo = new PDO("sqlite:./database.db");
/*
$sth = $pdo->query("SELECT * from shop");
$rows = $sth->fetchAll();
foreach ($rows as $row){
    echo $row['shopID'] . "|".$row['name']."|".$row['address'].PHP_EOL;
}

$sth = $pdo->query("SELECT * from product");
$rows = $sth->fetchAll();
foreach ($rows as $row){
    echo $row['productID'] . "|".$row['productName']."|".$row['productPrice']."|".$row['productCount'].PHP_EOL;
}

$sth = $pdo->query("SELECT * from client");
$rows = $sth->fetchAll();
foreach ($rows as $row){
    echo $row['clientID'] . "|".$row['clientName']."|".$row['phone']."|".$row['birthday'].PHP_EOL;
}

$sth = $pdo->query("SELECT * from 'order'");
$rows = $sth->fetchAll();
foreach ($rows as $row){
    echo $row['orderID'] . $row['created_at'] . "|".$row['shopID']."|".$row['clientID'].PHP_EOL;
}

$sth = $pdo->query("SELECT * from order_product");
$rows = $sth->fetchAll();
foreach ($rows as $row){
    echo $row['id'] . $row['orderID'] . "|".$row['productID']."|".$row['price'].PHP_EOL;
}
*/
$sth = $pdo->query("SELECT * from 'order_product'");
$rows = $sth->fetchAll();
foreach ($rows as $row){
    echo $row['id'] . "|".  $row['orderID'] . "|".$row['productID']."|".$row['price'].PHP_EOL;
}
$sth = $pdo->query("SELECT clientID, clientName, birthday from client");
$clients = $sth->fetchAll();
foreach ($clients as $client){
    echo $client['clientID'] . "|".  $client['clientName'] .  "|".  $client['birthday'] .PHP_EOL;
}
$sth = $pdo->query("SELECT * from 'order'");
$orders = $sth->fetchAll();
foreach ($orders as $order){
    echo $order['orderID'] . "|".  $order['shopID'] . "|".$order['clientID']."|". $order['created_at'].PHP_EOL;
}

foreach ($clients as $client){
    $query = "SELECT * from 'order' WHERE clientID=" . $client['clientID'];
    $rows = $pdo->query($query)->fetchAll();
    $result = 0;
    foreach ($rows as $row){
        $orderDate = new DateTime($row['created_at']);
        $birthDate = new DateTime($client['birthday']);
        //echo $orderDate->format('d.m.Y') . "|" . $birthDate->format('d.m.Y') . PHP_EOL;
        $interval = $birthDate->diff($birthDate);
        $sum = 0;
        if((int)$orderDate->format('Y') > (int)$birthDate->format('Y') and $interval->d <= 3){
            $newQuery = "SELECT price FROM order_product WHERE orderID=" . $row['orderID'];
            $sth = $pdo->query($newQuery);
            $prices = $sth->fetchAll();
            foreach ($prices as $price){
                $sum += $price['price'];
            }
        }
        $result += $sum;
    }
    echo $client['clientName'] . "|" .  $result. PHP_EOL;
}
echo "----------------------------------------------------------------------------------------------------------" . PHP_EOL;
$sth = $pdo->query("SELECT * from 'order_product'");
$order_products = $sth->fetchAll();
foreach ($order_products as $order_product){
    $query = "SELECT * from product WHERE productID= " . $order_product['productID'];
    $sth2 = $pdo->query($query);
    $prices = $sth2->fetchAll();
    foreach ($prices as $price){
        if((int)$order_product['price']-(int)$price['productPrice'] !== 0){
            echo "Заказ " . $order_product['id'] . "|".  $order_product['orderID'] . "|".$order_product['productID']."|".$order_product['price']. "|" ."Разница: " . abs((int)$order_product['price']-(int)$price['productPrice']) . PHP_EOL;
        }
    }
}
echo "----------------------------------------------------------------------------------------------------------" . PHP_EOL;
$incomes = [];
$sells = [];
$sth = $pdo->query("SELECT * from 'order'");
$orders = $sth->fetchAll();
foreach ($orders as $order){
    $query = "SELECT * from 'order_product' where orderID=" . $order['orderID'];
    //echo $query . PHP_EOL;
    $sth = $pdo->query($query);
    $order_products = $sth->fetchAll();
    //echo "Магазин " . $order["shopID"] . PHP_EOL;
    if(!array_key_exists( $order["shopID"] ,$incomes)){
        $incomes[$order["shopID"]] = 0;
    }
    if(!array_key_exists( $order["shopID"] ,$sells)){
        $sells[$order["shopID"]] = 0;
    }
    foreach ($order_products as $order_product){
        $incomes[$order["shopID"]] += $order_product["price"];
        $sells[$order["shopID"]]+=1;
    }
}
$query1 = "SELECT name from shop where shopID=" . array_search(max($incomes),$incomes);
$query2 = "SELECT name from shop where shopID=" . array_search(max($sells),$sells);

$stmt = $pdo->query($query1);
$smth = $stmt->fetchAll();
$mostIncomeShop = $smth[0]['name'];
$stmt = $pdo->query($query2);
$smth = $stmt->fetchAll();
$mostPopularShop = $smth[0]['name'];
echo "Самый прибыльный магазин: $mostIncomeShop" . PHP_EOL;
echo "Самый большое количество заказов у магазина $mostPopularShop". PHP_EOL;