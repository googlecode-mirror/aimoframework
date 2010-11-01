<?php
/**
 * Aimo Framework
 *
 * LICENSE
 *
 * This is not a free software,perhaps open source later.
 *
 * @category   Aimo
 * @package    Aimo_Translator
 * @copyright  Copyright (c) 2010 Aimosoft Studio. (http://www.aimosoft.cn)
 * @version    $Id$
 */

/**
 * @category   Aimo
 * @package    Aimo_Translator
 * @copyright  Copyright (c) 2010 Aimosoft Studio. (http://www.aimosoft.cn)
 * @license    Not free
 * @author     Jackie(jackie@aimosft.cn)
 */
class Aimo_Translator{
    
    /**
     * default language
     *
     * @var string
     **/
    protected $_defaultLang = 'zh_CN';  
    
    /**
     * Using language 
     *
     * @var string
     **/
    protected $_lang;
    
    /**
     * default language path default app/languages
     *
     * @var string
     **/
    protected $_basePath;
    
    /**
     * The default total language file default is /app/languages/zh_CN/common.php
     *
     * @var string
     **/
    protected $_firstLangFile = 'common.php';
    
    /**
     * undocumented class variable
     *
     * @var string
     **/
    protected $_lastLangFile = null;
    
    /**
     * Array that contains languages data
     *
     * @var array
     **/
    protected $_langData ;
    
    /**
     * Current module
     *
     * @var string
     */
    protected $_curModule = null;
    
    /**
     * Self
     *
     * @var Aimo_Translator
     **/
    public static $_instance;
    
    /**
     * construct funciton     
     * @return void
     */
    public function __construct(){}
    
    /**
     * Singleton instance
     *
     * @return Aimo_Translator
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    } 
    
    /**
     * set Default language
     *
     * @param  String $lang
     * @return Aimo_Translator
     */
    public  function setDefaultLang($lang = null)
    {
        if (null !== $lang) {
            $this->_defaultLang = $lang;
        }
        return $this;
    }
    
    /**
     * set using language
     *
     * @param  String $lang
     * @return Aimo_Translator
     */
    public function setLang($lang = null)
    {
        if (null !== $lang) {
            $this->_lang = $lang;
        }else {
            $this->_lang = $this->_defaultLang;
        }
        return $this;
    }
    /**
     * set languages dir default is app/languages
     *
     * @param  String  $path
     * @return Aimo_Translator
     */
    public function setBasePath($path = null)
    {
        if (null !== $path) {
            $this->_basePath = $path;
        }else {
            $this->_basePath = realpath(dirname(__FILE__).'/../../app/languages')
                                .DIRECTORY_SEPARATOR;
        }
        return $this;
    }
    
    /**
     * set FirstLangFile  default common.php
     *
     * @return Aimo_Translator
     **/
    public function setFirstLangFile($file = null)
    {
        if (null !== $file) {
            $this->_firstLangFile = $file;
        }
        
        return $this;
    }
    
    /**
     * get current module
     *
     * @return string
     */
    public function getCurModule()
    {
        if (null === $this->_curModule || empty($this->_curModule)) {
            
            $params = Aimo_Controller::getInstance()->_params;
            $this->_curModule = $params['_m'];
        }
        return $this->_curModule;
    }
    /**
     * Get translation content.
     * 
     * Support sprintf method
     * @param  String $key
     * @return String
     */
    public function t($key)
    {

        $lang_file =  $this->_basePath;
        $result    = null;
        $lang_to   = $key;
        // Use last language file
        $args      = array();
        if (func_num_args() >1) {
            $args = func_get_args();
            //Aimo_Debug::dump($args);
            array_shift($args);
            
        }
        
        if (isset($this->_langData)) {
            if (isset($this->_langData[$key])) {
                $lang_to = vsprintf($this->_langData[$key],$args);
                return $lang_to;
            }
        }

        if (!isset($this->_langData)) {
            $lang_file .= $this->_lang.DIRECTORY_SEPARATOR
                          .$this->_firstLangFile;
            if (is_file($lang_file)) {
                $this->_langData = include $lang_file;
            }
         
            if (isset($this->_langData[$key])) {
                $lang_to = vsprintf($this->_langData[$key],$args);
                
            }else {
                $lang_file .= $this->_lang.DIRECTORY_SEPARATOR
                          .$this->getCurModule().'.php';
                if (is_file($lang_file)) {
                    $result = include_once $lang_file;
                    if (is_array($result)) {
                      $this->_langData = array_merge($this->_langData,$result);  
                    }
                }
                if (isset($this->_langData[$key])) {
                    $lang_to = vsprintf($this->_langData[$key],$args);
                }
            }
        }

        return $lang_to;
    }
}