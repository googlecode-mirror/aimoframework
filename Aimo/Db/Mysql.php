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

class Aimo_Db_Mysql
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
     * Array That contains sqls 
     *
     * @var array
     **/
    public $_debug = array();
    
    /**
     * query times
     *
     * @var integer
     **/
    public $_count = 0;
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
        if ( !extension_loaded( 'mysql' ) ) {
            throw new Exception( 'The mysql extension is required for this adapter but the extension is not loaded' );
        }

        $port = empty($this->_config['port'])?':3306':':'.$this->_config['port'];
        $this->_connection = mysql_connect($this->_config['host'].$port,
                            $this->_config['username'],$this->_config['password']);
        if ( !$this->_connection ){
            $this->closeConnection();
            throw new Exception(mysql_error( $this->_connection ) );
        }
        mysql_select_db($this->_config['dbname'],$this->_connection);
        if ( !empty( $this->_config['charset'] ) ) {
            $sql_query = "SET NAMES '" . $this->_config['charset'] . "'";
            @mysql_query( $sql_query,$this->_connection );
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
            @mysql_close( $this->_connection );
        }
        $this->_connection = null;
    }
    /**
     * Begin a transaction.
     *
     * @return void
     */
    public function beginTransaction() {
        $this->_connect();
        @mysql_query( $this->_connection, 'BEGIN;' );
    }

    /**
     * Commit a transaction.
     *
     * @return void
     */
    public function commit() {
        $this->_connect();
        @mysql_query( $this->_connection, 'COMMIT;' );
    }

    /**
     * Roll-back a transaction.
     *
     * @return void
     */
    public function rollBack() {
    	$this->_connect();
        @mysql_query( $this->_connection, 'ROLLBACK;' );
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
        return "'" . mysql_escape_string($value ) . "'";
    }
    /**
     * strip the $value's quote 
     *
     * @param  mixed $value
     * @return String 
     */
    protected static function _stripQuote($value)
    {
        $value = is_array($value) ?array_map('stripslashes',$value) :
                 stripslashes($value);
        return $value;
    }
    /**
     * Returns the symbol the adapter uses for delimiting identifiers.
     *
     * @param  Strng $data  Witch to quote.
     * @return string
     */
    public function quoteIdentifier($data) {
        return '`'.$data.'`';
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
     * @return string
     * @todo Return value should be int?
     */
    public function lastInsertId() {
        /* I hope this is correct (after insert returning OID and save it) */
        return $this->lastid;
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
        $sql .= " LIMIT $offset";
        if ($offset > 0) {
            $sql .= " , $count";
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
        $result = @mysql_query($sql);
        array_push($this->_debug,$sql);
        if (!$result) {
            $e = new Exception(mysql_errno().":".
                    mysql_error($this->_connection).PHP_EOL."SQL:".$sql);
					
			Aimo_Debug::dump($e->getTrace());
			throw $e;
        }
        $this->_count++;
        return $result;
    }
    /**
     * Shortcut for mysql_fetch_array
     *
     * @param  resource  $stmt   the query result
     * @param  int       $fetchMode     
     * @return array
     */
    public function fetch($stmt,$fetchMode = Aimo_Db::FETCH_ASSOC)
    {
        $row = @mysql_fetch_array($stmt,$fetchMode);
        $row = self::_stripQuote($row);
        return $row;
    }
    /**
     * Inserts a table row with specified data.
     *
     * @param string $table The table to insert data into.
     * @param array  $bind Column-value pairs.
     * @param boolean $replace use replace instead of insert 
     * @return int The number of affected rows.
     */
    public function insert($table, $bind = array(), $replace = false)
    {
        // extract and quote col names from the array keys
        $cols = array();
        $vals = array();
        foreach ($bind as $col => $val) {
            $cols[] = $this->quoteIdentifier($col);
            
            $vals[] = '%s';

        }
        // build the statement
        $method = $replace?'REPLACE':'INSERT';
        $sql = $method.' INTO '
             . $this->quoteIdentifier($table)
             . ' (' . implode(', ', $cols) . ') '
                 . 'VALUES (' . implode(', ', $vals) . ') ';
        //Aimo_Debug::dump(array_values($bind));
        // execute the statement and return the number of affected rows
        $stmt = $this->query( $sql, array_values( $bind ) );
        
        $result = mysql_affected_rows($this->_connection);
        //$pkey   = mysql_fetch_result($result,1,0);
        if (!$replace) {
            $this->lastid = mysql_insert_id();
        }else {
            $this->lastid = null;
        }
        
        return $result;
    }
    /**
     * Updates table rows with specified data based on a WHERE clause.
     *
     * @param  mixed        $table The table to update.
     * @param  array        $bind  Column-value pairs.
     * @param  mixed        $wheresqlarr UPDATE WHERE clause(s).
     * @return int          The number of affected rows.
     */
    public function update($table, $bind = array(), $wheresqlarr = '')
    {
 
    	$setsql = $comma = '';
    	foreach ($bind as $set_key => $set_value) {
    		$setsql .= $comma.$this->quoteIdentifier($set_key).'='.$this->_quote($set_value).'';
    		$comma = ', ';
    	}
    	$where = $comma = '';
    	if(empty($wheresqlarr)) {
    		$where = '1';
    	} elseif(is_array($wheresqlarr)) {
    		foreach ($wheresqlarr as $key => $value) {
    			$where .= $comma.$this->quoteIdentifier($key).'='.$this->_quote($value).'';
    			$comma = ' AND ';
    		}
    	} else {
    		$where = $wheresqlarr;
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
     
        $result = mysql_affected_rows($this->_connection);
        return $result;
    } 
    /**
     * Deletes table rows based on a WHERE clause.
     *
     * @param  mixed        $table The table to update.
     * @param  mixed        $wheresqlarr DELETE WHERE clause(s).
     * @return int          The number of affected rows.
     */
    public function delete($table, $wheresqlarr = '')
    {
     	$where = $comma = '';
    	if(empty($wheresqlarr)) {
    		$where = '1';
    	} elseif(is_array($wheresqlarr)) {
    		foreach ($wheresqlarr as $key => $value) {
    			$where .= $comma.$this->quoteIdentifier($key).'='.$this->_quote($value).'';
    			$comma = ' AND ';
    		}
    	} else {
    		$where = $wheresqlarr;
    	}       
        
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
        $result = mysql_affected_rows($this->_connection);
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

        while ($row = @mysql_fetch_array($stmt,$fetchMode)) {
            $data[] = self::_stripQuote($row);
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
    public function fetchRow($sql, $bind = array(), $fetchMode = Aimo_Db::FETCH_ASSOC)
    {

        $stmt = $this->query($sql, $bind);
        $result = @mysql_fetch_array($stmt,$fetchMode);
        return self::_stripQuote($result);
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
        while ($row = @mysql_fetch_assoc($stmt)) {
            $tmp = array_values(array_slice($row, 0, 1));
            $data[$tmp[0]] = self::_stripQuote($row);
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
        $data = @mysql_fetch_assoc($stmt);
        return self::_stripQuote($data);
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
        while ($row = @mysql_fetch_row($result)) {
            $row = self::_stripQuote($row);
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
        $field_value = @mysql_result($result,0,0);
        
        return self::_stripQuote($field_value);
    }        
} // END class Pgsql