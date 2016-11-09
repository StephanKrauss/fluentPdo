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
	    "|name = :name1",
		"name = :name2"
	);

	// Variablen aus einem Formular
	$formVars = array(
		'name1' => 'mustermann',
        'name2' => 'sonnenschein'
	);

    /** @var $stmtPdoObj fluentPdo  */
	$stmtPdoObj = $fluentPdo
        ->select($cols)
        ->from('users')
        ->where($where)
        ->execute($formVars)
        ->getStmtObjPdo();

    $rawQuery = $fluentPdo->getRawQuery();

    $cleanQuery = $fluentPdo->getRealSql();

    $timeQuery = $fluentPdo->getTime();

    // Ausgabe
    $result = $fluentPdo->getManyRows();

    // Quoten SQL
    $string = 'Naughty \' string';
    $string = $fluentPdo->quote($string);