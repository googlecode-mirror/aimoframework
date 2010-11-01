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
 class Aimo_Db
 {
    const FETCH_ASSOC = 1;
    const FETCH_NUM   = 2;
    const FETCH_BOTH  = 3;
    

    /**
     * Factory for Db classes.
     *
     * @param  mixed $adapter String name of base adapter class.
     * @param  array $config 
     * @return Aimo_DB_Pgsql | Aimo_DB_Mysql
     * @throws Exception
     */
    public static function factory($adapter, $config = array()){
        
        $dbDir   = dirname(__FILE__).DIRECTORY_SEPARATOR.'Db'.DIRECTORY_SEPARATOR;
        $adapter = ucfirst(strtolower($adapter));
        $adapter_file  = $dbDir.$adapter.'.php';
		require_once $adapter_file;
        $adapter_class = 'Aimo_Db_'.$adapter;
        $adapterObj    = null;
        
        if (is_file($adapter_file) && class_exists($adapter_class)) {
            $adapterObj = new $adapter_class($config);
        }else {
            throw new Exception("$adapter_class does not exits!");
            
        }
        return $adapterObj;
    }
    
 }
 