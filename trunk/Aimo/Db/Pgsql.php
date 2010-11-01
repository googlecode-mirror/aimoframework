<?php  
/**
 * Aimo Framework
 *
 * LICENSE
 *
 * This is not a free software,perhaps open source later.
 *
 * @category   Aimo
 * @package    Aimo_Db
 * @copyright  Copyright (c) 2010 Aimosoft Studio. (http://www.aimosoft.cn)
 * @version    $Id$
 */

/**
 * @category   Aimo
 * @package    Aimo_Db
 * @copyright  Copyright (c) 2010 Aimosoft Studio. (http://www.aimosoft.cn)
 * @license    Not free
 * @author     Jackie(jackie@aimosft.cn)
 */

class Aimo_Db_Pgsql
{
    /**
     * Config array
     *
     * @var string
     **/
    protected $_config = array();
    /**
     * Database connection
     *
     * @var object|resource|null
     */
    protected $_connection = null;
    /**
     * @var LastInsertId when possible
     */
    protected $lastid = null;    
    /**
     * Open the constraint_exclusion option for table partion
     *
     * @var string
     **/
    protected $_constraintExclusion = 'On';
    /**
     * Constructor.
     *
     * $config is an array of key/value pairs or an instance of Zend_Config
     * containing configuration options.  These options are common to most adapters:
     *
     * dbname         => (string) The name of the database to user
     * username       => (string) Connect to the database as this username.
     * password       => (string) Password associated with the username.
     * host           => (string) What host to connect to, defaults to localhost
     *
     * Some options are used on a case-by-case basis by adapters:
     *
     * port           => (string) The port of the database
     * schema         => (string) The schema of the database
     * @param  array

     * @throws Exception
     */
    public function __construct($config)
    {
        $this->_config = $config;
       
    }
    /**
     * Creates a connection to the database.
     *
     * @return void
     * @throws Exception
     */
    protected function _connect()
    {
        if ( $this->_connection ) {
            return;
        }
        if ( !extension_loaded( 'pgsql' ) ) {
            throw new Exception( 'The pgsql extension is required for this adapter but the extension is not loaded' );
        }
        $strconn = vsprintf( "host=%s port=%s dbname=%s user=%s password=%s", 
                    array( $this->_config['host'], $this->_config['port'], 
                    $this->_config['dbname'], $this->_config['username'], 
                    $this->_config['password'] )
                     );
        //echo $strconn;
        //exit;
        $this->_connection = pg_connect( $strconn );
        if ( !$this->_connection ){
            $this->closeConnection();
            throw new Exception(pg_last_error( $this->_connection ) );
        }
        if ( !empty( $this->_config['charset'] ) ) {
            $sql_query = "SET NAMES '" . $this->_config['charset'] . "'";
            @pg_query( $this->_connection, $sql_query );
        }
        if ( !empty( $this->_config['search_path'] ) ) {
            $sql_query = "SET search_path to '" . $this->_config['search_path'] . "'";
            @pg_query( $this->_connection, $sql_query );
        }
        if ( !empty( $this->_config['constraintExclusion'] ) ) {
            $sql_query = "SET constraintExclusion '" . $this->_config['constraintExclusion'] . "'";
            @pg_query( $this->_connection, $sql_query );
        }
    }
    /**
     * Returns the underlying database connection object or resource.
     * If not presently connected, this initiates the connection.
     *
     * @return object|resource|null
     */
    public function getConnection()
    {
        $this->_connect();
        return $this->_connection;
    }
    /**
     * Force the connection to close.
     *
     * @return void
     */
    public function closeConnection() {
        if ( $this->_connection ) {
            @pg_close( $this->_connection );
        }
        $this->_connection = null;
    }
    /**
     * Begin a transaction.
     *
     * @return void
     */
    protected function _beginTransaction() {
        $this->_connect();
        @pg_query( $this->_connection, 'BEGIN;' );
    }

    /**
     * Commit a transaction.
     *
     * @return void
     */
    protected function _commit() {
        $this->_connect();
        @pg_query( $this->_connection, 'COMMIT;' );
    }

    /**
     * Roll-back a transaction.
     *
     * @return void
     */
    protected function _rollBack() {
    	$this->_connect();
        @pg_query( $this->_connection, 'ROLLBACK;' );
    }
    /**
     * Quote a raw string.
     *
     * @param mixed $value Raw string
     *
     * @return string           Quoted string
     */
    protected function _quote( $value ) {
        if ( is_int( $value ) || is_float( $value ) ) {
            return $value;
        }
        //$this->_connect();
        return "'" . pg_escape_string( $this->_connection, $value ) . "'";
    }
    /**
     * Returns the symbol the adapter uses for delimiting identifiers.
     *
     * @param  Strng $data  Witch to quote.
     * @return string
     */
    public function quoteIdentifier($data) {
        return '"'.$data.'"';
    }        
    /**
     * Gets the last ID generated automatically by an IDENTITY/AUTOINCREMENT column.
     *
     * As a convention, on RDBMS brands that support sequences
     * (e.g. Oracle, PostgreSQL, DB2), this method forms the name of a sequence
     * from the arguments and returns the last id generated by that sequence.
     * On RDBMS brands that support IDENTITY/AUTOINCREMENT columns, this method
     * returns the last value generated for such a column, and the table name
     * argument is disregarded.
     *
     * MySQL does not support sequences, so $tableName and $primaryKey are ignored.
     *
     * @param string $tableName   OPTIONAL Name of table.
     * @param string $primaryKey  OPTIONAL Name of primary key column.
     * @return string
     * @todo Return value should be int?
     */
    public function lastInsertId( $tableName = null, $primaryKey = null ) {
        if ( $tableName !== null ) {
            $sequenceName = $tableName;
            if ($primaryKey) {
                $sequenceName .= "_$primaryKey";
            }
            $sequenceName .= '_seq';
            return $this->lastSequenceId( $sequenceName );
        }
        /* I hope this is correct (after insert returning OID and save it) */
        return $this->lastid;
    } 

    /**
     * Return the most recent value from the specified sequence in the database.
     * This is supported only on RDBMS brands that support sequences
     * (e.g. Oracle, PostgreSQL, DB2).  Other RDBMS brands return null.
     *
     * @param string $sequenceName
     * @return string
     */
    public function lastSequenceId($sequenceName)
    {
        $this->_connect();
        $value = $this->fetchOne("SELECT CURRVAL(".$this->quote($sequenceName).")");
        return $value;
    }

    /**
     * Generate a new value from the specified sequence in the database, and return it.
     * This is supported only on RDBMS brands that support sequences
     * (e.g. Oracle, PostgreSQL, DB2).  Other RDBMS brands return null.
     *
     * @param string $sequenceName
     * @return string
     */
    public function nextSequenceId($sequenceName)
    {
        $this->_connect();
        $value = $this->fetchOne("SELECT NEXTVAL(".$this->quote($sequenceName).")");
        return $value;
    } 

    /**
     * Adds an adapter-specific LIMIT clause to the SELECT statement.
     *
     * @param string $sql
     * @param int $count
     * @param int $offset OPTIONAL
     * @return string
     */
    public function limit( $sql, $count, $offset = 0 ) {
        $count = intval($count);
        if ($count <= 0) {
            throw new Exception("LIMIT argument count=$count is not valid");
        }

        $offset = intval($offset);
        if ($offset < 0) {
            throw new Exception("LIMIT argument offset=$offset is not valid");
        }
        $sql .= " LIMIT $count";
        if ($offset > 0) {
            $sql .= " OFFSET $offset";
        }

        return $sql;
    }
    /**
     * Prepares and executes an SQL statement with bound data.
     *
     * @param  mixed  $sql  The SQL statement with placeholders.
     *                      May be a string or Zend_Db_Select.
     * @param  String $fetchMode Set the fetch mode..
     * @return resource
     * @throw  Exception
     */
    public function query($sql, $bind = array())
    {
        // connect to the database if needed
        $this->_connect();
        if (count($bind)) {
            
            foreach ($bind as $key => $value) {
                $bind[$key] = $this->_quote($value);
            }
            //unset($bind);
            $sql = vsprintf($sql,$bind);
        }        
        $result = @pg_query($sql);
        if (!$result) {
            throw new Exception(pg_last_error( $this->_connection));
        }
        return $result;
    }
    /**
     * Shortcut for pg_fetch_array
     *
     * @param  resource  $stmt   the query result
     * @param  int       $fetchMode Aimo_Db::FETCH_ASSOC 1, FETCH_NUM 2 FETCH_BOTH 3.    
     * @return array
     */
    public function fetch($stmt,$fetchMode = Aimo_Db::FETCH_ASSOC )
    {
        $row = @pg_fetch_array($stmt,null,$fetchMode);
        $row = $row;
        return $row;
    }
    /**
     * Inserts a table row with specified data.
     *
     * @param string $table The table to insert data into.
     * @param array  $bind Column-value pairs.
     * @param String $pkey the Primary key of the table
     * @return int The number of affected rows.
     */
    public function insert($table, array $bind,$pkey = null)
    {
        // extract and quote col names from the array keys
        $cols = array();
        $vals = array();
        foreach ($bind as $col => $val) {
            $cols[] = $this->quoteIdentifier($col);
            $vals[] = '%s';
        }
        // build the statement
        $sql = 'INSERT INTO '
             . $this->quoteIdentifier($table)
             . ' (' . implode(', ', $cols) . ') '
                 . 'VALUES (' . implode(', ', $vals) . ') ';
        if (null !== $pkey) {
            $sql .= 'RETURNING ' . $pkey;
        }


        // execute the statement and return the number of affected rows
        $stmt = $this->query($sql,array_values($bind));
        
        $result = pg_affected_rows($stmt);
        if (null !== $pkey) {
            $pkey   = pg_fetch_result($stmt,1,0);
            $this->lastid = $pkey[0];  
        }
        return $result;
    }
    /**
     * Updates table rows with specified data based on a WHERE clause.
     *
     * @param  mixed        $table The table to update.
     * @param  array        $bind  Column-value pairs.
     * @param  mixed        $where UPDATE WHERE clause(s).
     * @return int          The number of affected rows.
     */
    public function update($table, array $bind, $where = '')
    {
 
    	$setsql = $comma = '';
    	foreach ($bind as $set_key => $set_value) {
    		$setsql .= $comma.$this->quoteIdentifier($key).'=\''.$this->_quote($set_value).'\'';
    		$comma = ', ';
    	}
    	$where = $comma = '';
    	if(empty($wheresqlarr)) {
    		$where = '1';
    	} elseif(is_array($where)) {
    		foreach ($where as $key => $value) {
    			$where .= $comma.$this->quoteIdentifier($key).'=\''.$this->_quote($value).'\'';
    			$comma = ' AND ';
    		}
    	} else {
    		$where = $where;
    	}
        /**
         * Build the UPDATE statement
         */
        $sql = "UPDATE "
             . $this->quoteIdentifier($table, true)
             . ' SET ' . $setsql
             . (($where) ? " WHERE $where" : '');

        /**
         * Execute the statement and return the number of affected rows
         */
        $stmt = $this->query($sql);
     
        $result = pg_affected_rows($stmt);
        return $result;
    } 
    /**
     * Deletes table rows based on a WHERE clause.
     *
     * @param  mixed        $table The table to update.
     * @param  mixed        $where DELETE WHERE clause(s).
     * @return int          The number of affected rows.
     */
    public function delete($table, $where = '')
    {
        /**
         * Build the DELETE statement
         */
        $sql = "DELETE FROM "
             . $this->quoteIdentifier($table)
             . (($where) ? " WHERE $where" : '');

        /**
         * Execute the statement and return the number of affected rows
         */
        $stmt   = $this->query($sql);
        $result = pg_affected_rows($stmt);
        return $result;
    }
    /**
     * Fetches all SQL result rows as a sequential array.
     * Uses the current fetchMode for the adapter.
     *
     * @param string $sql  An SQL SELECT statement.
     * @param mixed  $bind Data to bind into SELECT placeholders.
     * @param mixed  $fetchMode  PGSQL_ASSOC 1, PGSQL_NUM 2 PGSQL_BOTH 3..
     * @return array
     */
    public function fetchAll($sql, $bind = array(), $fetchMode = 1)
    {

        $stmt = $this->query($sql, $bind);
        $data = array();

        while ($row = @pg_fetch_array($stmt,NULL,$fetchMode)) {
            $data[] = $row;
        }
        return $data;
    }

    /**
     * Fetches the first row of the SQL result.
     * Uses the current fetchMode for the adapter.
     *
     * @param string $sql An SQL SELECT statement.
     * @param mixed $bind Data to bind into SELECT placeholders.
     * @param mixed                 $fetchMode Override current fetch mode.
     * @return array
     */
    public function fetchRow($sql, $bind = array(), $fetchMode = PGSQL_ASSOC)
    {

        $stmt = $this->query($sql, $bind);
        $result = @pg_fetch_array($stmt,null,$fetchMode);
        return $result;
    }

    /**
     * Fetches all SQL result rows as an associative array.
     *
     * The first column is the key, the entire row array is the
     * value.  You should construct the query to be sure that
     * the first column contains unique values, or else
     * rows with duplicate values in the first column will
     * overwrite previous data.
     *
     * @param string $sql An SQL SELECT statement.
     * @param mixed $bind Data to bind into SELECT placeholders.
     * @return array
     */
    public function fetchAssoc($sql, $bind = array())
    {
        $stmt = $this->query($sql, $bind);
        $data = array();
        while ($row = @pg_fetch_assoc($stmt)) {
            $tmp = array_values(array_slice($row, 0, 1));
            $data[$tmp[0]] = $row;
        }
        return $data;
    }

    /**
     * Fetches the first column of all SQL result rows as an array.
     *
     * The first column in each row is used as the array key.
     *
     * @param string $sql An SQL SELECT statement.
     * @param mixed $bind Data to bind into SELECT placeholders.
     * @return array
     */
    public function fetchCol($sql, $bind = array())
    {
        $stmt = $this->query($sql, $bind);
        $data = array();
        $data = @pg_fetch_assoc($stmt);
        return $data;
    }

    /**
     * Fetches all SQL result rows as an array of key-value pairs.
     *
     * The first column is the key, the second column is the
     * value.
     *
     * @param string $sql An SQL SELECT statement.
     * @param mixed $bind Data to bind into SELECT placeholders.
     * @return array
     */
    public function fetchPairs($sql, $bind = array())
    {
        $result = $this->query($sql, $bind);
        $data = array();
        while ($row = @pg_fetch_row($result)) {
          
            $data[$row[0]] = $row[1];
        }
        return $data;
    }

    /**
     * Fetches the first column of the first row of the SQL result.
     *
     * @param string| SQL SELECT statement.
     * @param mixed $bind Data to bind into SELECT placeholders.
     * @return string
     */
    public function fetchOne($sql, $bind = array())
    {

        $result = null;
        
        $result = $this->query($sql, $bind);
        $field_value = @pg_fetch_result($result,0,0);
        
        return $field_value;
    }        
} // END class Pgsql