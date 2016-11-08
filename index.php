<?php

	include_once('fluentPdo.php');
	include_once( 'fluentException.php' );

	$server   = 'mysql:dbname=test;host=localhost; port=3306';
	$user     = 'test';
	$password = 'test';

	$options  = array
	(
	    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
	);

	$pdo = new PDO($server, $user, $password, $options);
	$fluentPdo = new fluentPdo($pdo);

	$cols = array('id','name');

	$where = array(
		"name = :name"
	);

	// Variablen aus einem Formular
	$formVars = array(
		'name' => 'mustermann'
	);

    /** @var $stmtPdo fluentPdo  */
	$stmtPdo = $fluentPdo->select($cols)->from('users')->where($where)->execute($formVars);

    //$rawQuery = $fluentPdo->getRawQuery();
    //
    //$cleanQuery = $fluentPdo->getRealSql();
    //
    //$timeQuery = $fluentPdo->getTime();
    //
    //echo $cleanQuery;
    //
    //$result = $stmtPdo->fetch(PDO::FETCH_ASSOC);
    //
    //var_dump($result);