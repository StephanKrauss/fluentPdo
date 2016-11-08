<?php

/**
 * Database Query
 *
 * Class fluentPdo
 */
class fluentPdo
{

    /**
     * PDO Connection
     * @var \PDO
     */
    private $Pdo = null;
    
    /**
     * SQL Logger
     * @var SqlLoggerInterface
     */
    private $SqlLogger;

    /**
     * Type
     * @var string
     */
    private $Type = null;

    /**
     * Fields
     * @var string[]
     */
    private $Fields = array();

    /**
     * Where
     * @var string[]
     * */
    private $Where = array();

    /**
     * Field <-> Value
     * @var string[]
     */
    private $Values = array();

    /**
     * Parameters to bind
     * @var mixed[]
     */
    private $Parameters = array();

    /**
     * Limit
     * @var int[]
     */
    private $Limit = array();

    /**
     * From/To Table
     * @var string
     * */
    private $Table;

    /**
     * Joins
     * @var string[][]
     */
    private $Joins = array();

    /**
     * Custom SQL
     * @var string
     */
    private $CustomSQL = '';

    /**
     * Order By
     * @var string[]
     */
    private $OrderBy = array();

    /**
     * Group By
     * @var string[]
     */
    private $GroupBy = array();

    /**
     * Prepared statement
     * @var \PDOStatement
     */
    private $Prepared = null;

    /**
     * Select For Update
     * @var boolean
     */
    private $ForUpdate = false;

    /**
     * Ausführungszeit
     *
     * @var int
     */
    private $time = 0;

    /**
     * reale SQL mit geparsten Platzhaltern
     *
     * @var null
     */
    private $realSql= null;

    /**
     * Constructor
     *
     * @internal Do not create directly, only \ElzeKool\FluentDbal\FluentDbal should
     * create a new Query
     *
     * realSql constructor.
     *
     * @param \PDO $pdo
     * @param null $sql_logger
     */
    public function __construct(\PDO $pdo, $sql_logger = null)
    {
        $this->Pdo = $pdo;
        $this->SqlLogger = $sql_logger;
    }

    /**
     * Cast Query to string
     *
     * @return string
     * */
    public function __toString() 
    {
        return $this->getRawQuery();
    }

    /**
     * Get SQL for current Query
     * 
     * @return string SQL Query
     */
    public function getRawQuery()
    {
        switch ($this->Type) {

            // Custom SQL
            case 'custom':
                return $this->CustomSQL;

            // Select
            case 'select':
                return
                    'SELECT ' .
                    join(', ', $this->Fields) .
                    ' FROM ' . $this->Table .
                    $this->sqlJoin() .
                    $this->sqlWhere() .
                    $this->sqlGroupBy() .
                    $this->sqlOrderBy() .
                    $this->sqlLimit() .
                    ($this->ForUpdate ? ' FOR UPDATE' : '');

            // Delete
            case 'delete':
                return
                    'DELETE ' .
                    join(', ', $this->Fields) .
                    ' FROM ' .
                    $this->Table .
                    $this->sqlJoin() .
                    $this->sqlWhere() .
                    $this->sqlLimit();

            // Insert/Replace share syntax
            case 'insert':
            case 'replace':
                return
                    strtoupper($this->Type) . ' INTO ' .
                    $this->Table .
                    ' SET ' .
                    join(',', $this->Values) .
                    $this->sqlJoin();

            // Update
            case 'update':
                return
                    'UPDATE ' .
                    $this->Table .
                    ' SET ' .
                    join(', ', $this->Values) .
                    $this->sqlWhere() .
                    $this->sqlOrderBy() .
                    $this->sqlLimit();

            default:
                return "ERROR: Query type not set/unimplemented";
        }
    }

    /**
     * Set Type, check if another type is already set
     *
     * @return void
     */
    private function setType($type) 
    {
        if ($this->Type === null) {
            $this->Type = $type;
        } else if ($this->Type != $type) {
            throw new fluentException('Query already started with another type');
        }
    }

    /**
     * Return WHERE part of SQL query
     *
     * @return string Where part
     * */
    private function sqlWhere()
    {
        return (count($this->Where) == 0) ? '' : (' WHERE ' . join(' AND ', $this->Where));
    }

    /**
     * Return LIMIT part of SQL query
     *
     * @return string Limit part
     * */
    private function sqlLimit()
    {
        return (count($this->Limit) == 0) ? '' : (' LIMIT ' . join(',', $this->Limit));
    }

    /**
     * Return ORDER BY part of SQL query
     *
     * @return string Order By part
     * */
    private function sqlOrderBy() 
    {
        return (count($this->OrderBy) == 0) ? '' : (' ORDER BY ' . join(', ', $this->OrderBy));
    }

    /**
     * Return GROUP BY part of SQL query
     *
     * @return string Order By part
     * */
    private function sqlGroupBy() 
    {
        return (count($this->GroupBy) == 0) ? '' : (' GROUP BY ' . join(', ', $this->GroupBy));
    }

    /**
     * Return Joins part of SQL query
     *
     * @return string Join part
     * */
    private function sqlJoin() 
    {
        if (count($this->Joins) == 0) {
            return '';
        }
        $sql = '';
        foreach ($this->Joins as $join) {
            $sql .= ' ' . $join[0] . ' JOIN ' . $join[1] . ' ON ' . $join[2];
        }
        return $sql;
    }

    /**
     * Handle Parameters
     *
     * @param mixed[] $args   Arguments (fetched with func_get_args)
     * @param int     $offset Number of arguments to skip
     *
     * @return void
     */
    private function handleParams($args, $offset = 1)
    {
        $n_args = count($args);

        if ($n_args <= $offset) {
            return;
        }

        if ($n_args == ($offset+1) AND is_array($args[$offset])) {
            $this->Parameters = array_merge($this->Parameters, $args[$offset]);
            return;
        }

        for($i = $offset; $i < $n_args; $i++) {
            $this->Parameters[] = $args[$i];
        }
    }

    /**
     * Custom SQL
     *
     * Accepts more parameters for position based parameters
     *
     * @param $sql
     *
     * @return $this
     */
    public function custom($sql) 
    {
        $this->Prepared = null;
        $this->CustomSQL = $sql;
        $this->setType('custom');
        $this->handleParams(func_get_args(), 1);
        return $this;
    }

    /**
     * Select
     *
     * @param $fields
     * @param bool $for_update
     *
     * @return $this
     */
    public function select($fields, $for_update = false) {
        $this->Prepared = null;

        $this->setType('select');
        $this->Fields = array_merge($this->Fields, (array) $fields);
        $this->ForUpdate = $for_update;

        return $this;
    }

    /**
     * Insert
     *
     * @return $this
     */
    public function insert() 
    {
        $this->Prepared = null;
        $this->setType('insert');

        return $this;
    }

    /**
     * Replace
     *
     * @return $this
     */
    public function replace() 
    {
        $this->Prepared = null;
        $this->setType('replace');

        return $this;
    }

    /**
     * Update
     *
     * @return $this
     */
    public function update() 
    {
        $this->Prepared = null;
        $this->setType('update');

        return $this;
    }

    /**
     * Delete
     *
     * @param string $fields Tables to delete (in case of JOIN)
     *
     * @param string $fields
     *
     * @return $this
     */
    public function delete($fields = '')
    {
        $this->Prepared = null;
        $this->setType('delete');
        $this->Fields = array_merge($this->Fields, (array) $fields);

        return $this;
    }

    /**
     * Set table to select/delete from
     *
     * @param $table
     *
     * @return $this
     * @throws fluentException
     */
    public function from($table)
    {
        $this->Prepared = null;

        if (($this->Type != 'select') AND ($this->Type != 'delete')) {
            throw new fluentException('From only allowed for select/delete queries');
        }

        $this->Table = $table;

        return $this;
    }

    /**
     * Set table to update/insert/replace into
     *
     * @param $table
     *
     * @return $this
     * @throws fluentException
     */
    public function into($table) 
    {
        $this->Prepared = null;

        if (($this->Type != 'update') AND ($this->Type != 'insert') AND ($this->Type != 'replace')) {
            throw new fluentException('Into only allowed for update/insert/replace queries');
        }
        $this->Table = $table;

        return $this;
    }

    /**
     * Set/Add where conditions
     *
     * Accepts more parameters for position based parameters
     *
     * @param $conditions
     *
     * @return $this
     */
    public function where($conditions)
    {
        $this->Prepared = null;
        $this->Where = array_merge($this->Where, (array) $conditions);
        $this->handleParams(func_get_args(), 1);
        
        return $this;
    }

    /**
     * Set Limit
     *
     * @param $count
     * @param int $offset
     *
     * @return $this
     */
    public function limit($count, $offset = 0) 
    {
        $this->Prepared = null;
        $this->Limit = array(
            $offset,
            $count
        );

        return $this;
    }

    /**
     * Add Left Join
     *
     * @param string $table Table
     * @param string $on    Join on
     *
     * @return $this
     */
    public function leftJoin($table, $on) 
    {
        return $this->join('LEFT', $table, $on);
    }

    /**
     * Add Right Join
     *
     * @param string $table Table
     * @param string $on    Join on
     *
     * @return $this
     */
    public function rightJoin($table, $on)
    {
        return $this->join('RIGHT', $table, $on);
    }

    /**
     * Add Inner Join
     *
     * @param string $table Table
     * @param string $on    Join on
     *
     * @return $this
     */
    public function innerJoin($table, $on)
    {
        return $this->join('INNER', $table, $on);
    }

    /**
     * Add Outer Join
     *
     * @param string $table Table
     * @param string $on    Join on
     *
     * @return $this
     */
    public function outerJoin($table, $on)
    {
        return $this->join('OUTER', $table, $on);
    }

    /**
     * Add Join
     *
     * @param string $type  Type
     * @param string $table Table
     * @param string $on    Join on
     *
     * @return $this
     */
    public function join($type, $table, $on)
    {
        $this->Prepared = null;
        if (!in_array($type, array('LEFT', 'RIGHT', 'INNER', 'OUTER'))) {
            throw new fluentException('Invalid JOIN type');
        }
        if (!in_array($this->Type, array('select', 'delete', 'update'))) {
            throw new fluentException('Join only allowed for select/update/delete queries');
        }
        $this->Joins[] = array(
            $type,
            $table,
            $on
        );

        return $this;
    }

    /**
     * Add Field <-> Value
     *
     * Accepts more parameters for position based parameters
     *
     * @param string|string[] $values Field <-> Value combination(s)
     *
     * @return $this
     * */
    public function set($values) 
    {
        $this->Prepared = null;
        if (!in_array($this->Type, array('insert', 'replace', 'update'))) {
            throw new fluentException('Set only allowed for insert/replace/update queries');
        }
        $this->Values = array_merge($this->Values, (array) $values);
        $this->handleParams(func_get_args(), 1);

        return $this;
    }

    /**
     * Order By
     *
     * @param string $field     Field
     * @param string $direction Direction (ASC|DESC)
     *
     * @return $this
     */
    public function orderby($field, $direction = 'ASC') 
    {
        $direction = strtoupper($direction);
        if (!in_array($direction, array('ASC', 'DESC'))) {
            throw new fluentException('Invalid direction for Order By');
        }
        $this->OrderBy[] = $field . ' ' . $direction;

        return $this;
    }

    /**
     * Group By
     *
     * @param string $field Field
     *
     * @return $this
     */
    public function groupby($field) 
    {
        $this->GroupBy[] = $field;

        return $this;
    }

    /**
     * Execute Query and return result
     *
     * @param mixed[] $params Override parameters
     *
     * @return \PDOStatement Executed statement
     */
    public function execute($params = null) 
    {
        // Check if there is a prepared statement
        if ($this->Prepared === null) {
            $rawSql = $this->getRawQuery();
            $this->Prepared = $this->Pdo->prepare($rawSql);
            $this->Prepared->setFetchMode(\PDO::FETCH_ASSOC);
        } else {
            // Make sure cursor is closed
            $this->Prepared->closeCursor();
        }

        // Check which parameters to use
        if ($params === null) {
            $params = $this->Parameters;
        }
        
        $start = microtime(true);

        if (!$this->Prepared->execute($params)) {
            $error = $this->Prepared->errorInfo();
            $exception = new DbalException('[' . $error[0] . '] ' . $error[2]);
            $exception->setPdoStatement($this->Prepared);

            throw $exception;
        }

        $end = microtime(true);

        $this->time = floor(($end-$start)*1000);

        if ($this->SqlLogger !== null) {
            $this->SqlLogger->log(
                $this->getRawQuery(),
                $params, 
                floor(($end-$start)*1000), 
                $this->Prepared
            );
        }

        if(count($params) >     0)
            $this->realSql = $this->generateCleanQuery($params);


        return $this->Prepared;
    }

    /**
     * ermittelt die reale SQL
     *
     * @param bool $params
     *
     * @return mixed
     */
    protected function generateCleanQuery($params)
    {
        $keys = array();
        $values = array();

        $rawSql = $this->getRawQuery();

        /*
         * Get longest keys first, sot the regex replacement doesn't
         * cut markers (ex : replace ":username" with "'joe'name"
         * if we have a param name :user )
         */
        $isNamedMarkers = false;
        if (count($params) && is_string(key($params))) {

            uksort($params, function($k1, $k2) {
                return strlen($k2) - strlen($k1);
            });

            $isNamedMarkers = true;
        }
        foreach ($params as $key => $value) {
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
            return preg_replace($keys, $values, $rawSql);
        } else {
            return preg_replace($keys, $values, $rawSql, 1, $count);
        }
    }

    /**
     * @return int
     */
    public function getTime(){
        return $this->time;
    }

    /**
     * @return null
     */
    public function getRealSql(){
        return $this->realSql;
    }
}