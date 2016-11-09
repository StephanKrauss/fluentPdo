<?php

class fluentException extends Exception
{
    
    /**
     * PDO statement
     * @var \PDOStatement
     */
    protected $PdoStatement;
    
    
    /**
     * Get PDO Statement
     *
     * @return \PDOStatement PDO Statement
     **/
    public function getPdoStatement()
    {
        return $this->PdoStatement;
    }

    /**
     * Set PDO Statement
     *
     * @param \PDOStatement $PdoStatement PDO Statement
     *
     * @return void
     **/
    public function setPdoStatement($PdoStatement)
    {
        $this->PdoStatement = $PdoStatement;
    }
    
}
