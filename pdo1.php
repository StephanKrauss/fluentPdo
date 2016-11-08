<?php

$server   = 'mysql:dbname=test;host=localhost; port=3306';
$user     = 'test';
$password = 'test';

$options  = array
(
    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
);

class myPdo extends PDO
{
    protected $rawSql = null;
    protected $prepareParams = [];

    public function showQuery($params, $rawSql)
    {
        $this->rawSql = $rawSql;
        $this->prepareParams = $params;
        $realQuery = $this->generateQuery();

        return $realQuery;
    }

    protected function generateQuery()
    {
        $keys = array();
        $values = array();
        /*
         * Get longest keys first, sot the regex replacement doesn't
         * cut markers (ex : replace ":username" with "'joe'name"
         * if we have a param name :user )
         */
        $isNamedMarkers = false;
        if (count($this->prepareParams) && is_string(key($this->prepareParams))) {
            uksort($this->prepareParams, function($k1, $k2) {
                return strlen($k2) - strlen($k1);
            });
            $isNamedMarkers = true;
        }
        foreach ($this->prepareParams as $key => $value) {
            // check if named parameters (':param') or anonymous parameters ('?') are used
            if (is_string($key)) {
                $keys[] = '/:'.ltrim($key, ':').'/';
            } else {
                $keys[] = '/[?]/';
            }
            // bring parameter into human-readable format
            if (is_string($value)) {
                $values[] = "'" . addslashes($value) . "'";
            } elseif(is_int($value)) {
                $values[] = strval($value);
            } elseif (is_float($value)) {
                $values[] = strval($value);
            } elseif (is_array($value)) {
                $values[] = implode(',', $value);
            } elseif (is_null($value)) {
                $values[] = 'NULL';
            }
        }
        if ($isNamedMarkers) {
            return preg_replace($keys, $values, $this->rawSql);
        } else {
            return preg_replace($keys, $values, $this->rawSql, 1, $count);
        }
    }
}

// simple Test
$pdo = new myPdo($server, $user, $password, $options);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // Fetch Assoc
$pdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL); // Spaltennamen Kleinschreibung

$params = array(
    'bla' => 'y1z2'
);

$sql = 'INSERT INTO author SET NAME = :bla;';
$stmt = $pdo->prepare($sql);

$kontrolle = $stmt->execute($params);

$sql = $pdo->showQuery($params, $sql);


