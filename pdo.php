<?php

$server   = 'mysql:dbname=test;host=localhost; port=3306';
$user     = 'test';
$password = 'test';

$options  = array
    (
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
    );

$pdo = new PDO($server, $user, $password, $options);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // Fetch Assoc
$pdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL); // Spaltennamen Kleinschreibung


// quoten von Parametern
$bla = 'bla\' OR \'1=1';
$query = "SELECT *  FROM  user WHERE pw = '".$pdo->quote($bla)."'";
// echo $query;

$query = 'SELECT * FROM author WHERE id = 3';
$resultStatement = $pdo -> query($query);
$result = $resultStatement->fetchAll();

// Transaktion
//$pdo -> beginTransaction();
//
//$sql = "INSERT INTO author SET NAME = 'xxx';";
//$pdo->exec($sql);

// Transaktion
// $pdo->rollBack();
// $pdo->commit();

$params = array(
    'bla' => 'y1z2'
);

$sql = 'INSERT INTO author SET NAME = :bla;';
$stmt = $pdo->prepare($sql);

$wert1 = 'xyz';
// $kontrolle = $stmt->bindParam(':bla',$wert1);

$kontrolle = $stmt->execute($params);

?>