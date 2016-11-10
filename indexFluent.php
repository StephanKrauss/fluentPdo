<?php

	include_once('sparrow.php');

	$options  = array
	(
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
	);

	$pdo = new PDO('mysql:host=localhost;dbname=test', 'test', 'test', $options);

	$cols = array('id','name');

	$bindParams = array(
		'name' => 'Schmidt'
	);

	/** @var  $objSparrow */
	$objSparrow = new Sparrow();

	/** @var $objPdoStm PDOStatement */
	$objPdoStm = $objSparrow
		->setDb($pdo)
		->from('author')
		->where('name = :name')
		->select($cols)
		->setBindParams($bindParams)
		->execute(null, 0, $bindParams);

	$result = $objPdoStm->fetchAll(PDO::FETCH_ASSOC);

	$query = $objSparrow->getRealSql();