<?php
/**
 * Aimo Framework
 *
 * LICENSE
 *
 * This is not a free software,perhaps open source later.
 *
 * @category   Aimo
 * @package    Aimo_Page
 * @copyright  Copyright (c) 2010 Aimosoft Studio. (http://www.aimosoft.cn)
 * @version    $Id$
 */

/**
 * @category   Aimo
 * @package    Aimo_Page
 * @copyright  Copyright (c) 2010 Aimosoft Studio. (http://www.aimosoft.cn)
 * @license    Not free
 * @author     Jackie(jackie@aimosft.cn)
 */

class Aimo_Page
{
    /**
     * page label such as first next prev last 
     *
     * @var array
     **/
    protected $_labels = array(
        'P_FIRST' => '首页',
        'P_NEXT'  => '下一页',
        'P_PREV'  => '上一页',
        'P_LAST'  => '尾页',
        'P_RECORDS' => '共 %s 项',
        'P_PAGES' => ' %s 页',
        );
    /**
     * The page var default page 
     *
     * @var string
     **/
    protected $_pagevar = 'page';
    
    /**
     * List pages range 
     *
     * @var int 
     **/
    protected $_range = 5;
    
    /**
     * The lang object
     *
     * @var Aimo_Translator
     **/
    protected $_lang;
    
    /**
     * The db object
     *
     * @var Aimo_Db
     **/
    protected $_db;
    
    /**
     * The sql sentence
     *
     * @var string
     **/
    protected $_sql;
    
    /**
     * construct function
     *
     * @return void
     */
    public function __construct()
    {
        $this->_lang = Aimo_Translator::getInstance();
    }
    
}
