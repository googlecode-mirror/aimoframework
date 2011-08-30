<?php  
/**
 * Aimo Framework
 *
 * LICENSE
 *
 * This is not a free software,perhaps open source later.
 *
 * @category   Aimo
 * @package    Aimo_Controller
 * @copyright  Copyright (c) 2010 Aimosoft Studio. (http://www.aimosoft.cn)
 * @version    $Id$
 */

/**
 * @category   Aimo
 * @package    Aimo_Controller
 * @copyright  Copyright (c) 2010 Aimosoft Studio. (http://www.aimosoft.cn)
 * @license    Not free
 * @author     Jackie(jackie@aimosft.cn)
 */
 
class Aimo_Controller{
    /**
     * Aimo_Controller
     *
     * @var Aimo_Controller
     **/
    public static $_instance = null;
    /**
     * All URL query string includes 
     *
     * @var array
     */
    public $_params = array();
    /**
     * undocumented class variable
     *
     * @var string
     **/
    protected $_headers = array();

    /**
     * The site base url
     *
     * @var string
     **/
    protected $_baseUrl;
    /**
     * The app root dir includes modules and controllers
     * app dir construct like this:
     * app/modules/front(default module)/controller.php
     *
     * @var string
     **/
    protected $_appRoot;
    /**
     * urlSuffix
     *
     * @var string
     **/
    protected $_urlSuffix = '.html';


    /**
     * The modules Directories 
     * Example:array('admin','front') 
     *
     * @var array
     **/
    protected $_modules = array();
    /**
     * Default module  is front 
     *
     * @var string
     **/
    protected $_defaultModule = 'front';    

    /**
     * default controller name  
     *
     * @var string
     **/
    protected $_defaultController = 'index';
    
    /**
     * default action name
     *
     * @var string
     **/
    protected $_defaultAction = 'index';
    
    /**
     * The template name. Can be abslute
     *
     * @var string
     **/
    protected $_tpl = null;
    /**
     * The templete base dir default is app/templets/default/
     *
     * @var string
     **/
    protected $_tplDir = 'default';
    /**
     * Set no render templete 
     *
     * @var boolean
     **/
    protected $_noRender = false;
    /**
     * Url style  Good look url(0) or Original 2 or path_info 1
     *
     * @var int
     **/
    protected $_urlMode = 0;
    /**
     * construct funciton     
     * @return void
     */
    public function __construct(){}
    /**
     * Singleton instance
     *
     * @return Aimo_Controller
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
     /**
     * Set a header
     *
     * If $replace is true, replaces any headers already defined with that
     * $name.
     *
     * @param string $name
     * @param string $value
     * @param boolean $replace
     * @return Aimo_Controller
     */
    public function setHeader($name, $value, $replace = false)
    {
        $name  = $this->_normalizeHeader($name);
        $value = (string) $value;

        if ($replace) {
            foreach ($this->_headers as $key => $header) {
                if ($name == $header['name']) {
                    unset($this->_headers[$key]);
                }
            }
        }

        $this->_headers[] = array(
            'name'    => $name,
            'value'   => $value,
            'replace' => $replace
        );

        return $this;
    }   
    /**
     * Normalize a header name
     *
     * Normalizes a header name to X-Capitalized-Names
     * 
     * @param  string $name 
     * @return string
     */
    protected function _normalizeHeader($name)
    {
        $filtered = str_replace(array('-', '_'), ' ', (string) $name);
        $filtered = ucwords(strtolower($filtered));
        $filtered = str_replace(' ', '-', $filtered);
        return $filtered;
    }    
    /**
     * Enforce singleton; disallow cloning
     *
     * @return void
     */
    private function __clone()
    {
    }
    /**
     * Prevent E_NOTICE for nonexistent values
     *
     * If {@link strictVars()} is on, raises a notice.
     *
     * @param  string $key
     * @return null
     */
    public function __get($key)
    {
        return null;
    }

    /**
     * Allows testing with empty() and isset() to work inside
     * templates.
     *
     * @param  string $key
     * @return boolean
     */
    public function __isset($key)
    {
        if ('_' != substr($key, 0, 1)) {
            return isset($this->$key);
        }

        return false;
    }

    /**
     * Directly assigns a variable to the view script.
     *
     * Checks first to ensure that the caller is not attempting to set a
     * protected or private member (by checking for a prefixed underscore); if
     * not, the public member is set; otherwise, an exception is raised.
     *
     * @param string $key The variable name.
     * @param mixed $val The variable value.
     * @return void
     * @throws Exception if an attempt to set a private or protected
     * member is detected
     */
    public function __set($key, $val)
    {
        if ('_' != substr($key, 0, 1)) {
            $this->$key = $val;
            return;
        }
        $e = new Exception('Setting private or protected class members is not allowed');
        throw $e;
    }

    /**
     * Allows unset() on object properties to work
     *
     * @param string $key
     * @return void
     */
    public function __unset($key)
    {
        if ('_' != substr($key, 0, 1) && isset($this->$key)) {
            unset($this->$key);
        }
    }
    /**
     * Setup application.
     *
     * @return void
     */
    public function run()
    {

        
        // Init MVC params
        $this->intParams();
        // Init Controller init Method
        $mvc_path = $this->_params['_m'].DIRECTORY_SEPARATOR.$this->_params['_c'];
        $controllerPath = $this->_appRoot.'modules'.DIRECTORY_SEPARATOR.
                          $mvc_path.'.php';
        if (!is_file($controllerPath)) {
            
            throw new Exception($mvc_path." Controller NOT FOUND!");
        }

        require $controllerPath;

        $controller    = strtolower($this->_params['_c']);
        
        if(!class_exists($controller)){
            throw new Exception($mvc_path." Controller NOT FOUND!");
        }
        $controllerObj = new $controller();
        //Aimo_Debug::dump($this);
        // pass the Aimo_Controller 's vars to new object

        $vars = get_class_vars(__CLASS__);
        foreach ($vars as $key => $value) {
            $method = 'set'.ucfirst(substr($key,1));
            if (method_exists(__CLASS__,$method)) {
                //Aimo_Debug::dump($this->$key);
                $controllerObj->{$method}($this->$key);
            }
        }
        if (method_exists($this->_params['_c'],'setup')) {
            $controllerObj->setup();
        }
        if (method_exists($this->_params['_c'],'init')) {
            $controllerObj->init();
        }
        // Call action
        $action = $this->_params['_a'].'Action';
        if (!method_exists($this->_params['_c'],$action)) {
            throw new Exception($this->_params['_a']." NOT FOUND IN ".$mvc_path);
        }else {
            $controllerObj->{$action}();
        }

        // render view
        if (!$controllerObj->getNoRender()) {
            try {
                print($controllerObj->render()); 
             } catch (Exception $e) {
                print($e->getMessage());
             } 
         } 
    }
    /**
     * Set appRoot,Need manaul set if has
     *
     * @param String $appRoot
     * @return Aimo_Controller
     */
    public function setAppRoot($appRoot = null)
    {
        if (null !== $appRoot) {
            $this->_appRoot = $appRoot;
        }else {
            $this->_appRoot = realpath(dirname(__FILE__).DIRECTORY_SEPARATOR.
                              '../../app').DIRECTORY_SEPARATOR;
        }
        return $this;
    }
    /**
     * Set the base url such as subdir/ end with slash
     *
     * @param String $base
     * @return Aimo_Controller
     */
    public function setBaseUrl($base = null)
    {
        if(null !== $base)
            $this->_baseUrl = $base;
        else 
            $this->_baseUrl = '';
    
        return $this;
    }
    /**
     * set UrlSuffix
     *
     * @return Aimo_Controller
     */
    public function setUrlSuffix($urlSuffix = null)
    {
        $this->_urlSuffix = $urlSuffix;
        return $this;
    }
	/**
	 * set default urlMode
	 * @return Aimo_Controller
	 */
	public function setUrlMode($urlModel = -1)
	{
		$this->_urlMode = $urlModel;
	}
    /**
     * Set the default module
     *
     * @param  String $module 
     * @return Aimo_Controller
     */
    public function setDefaultModule($module = 'front')
    {
        $this->_defaultModule = $module;
        return $this;
    }
    /**
     * Set the default controller
     *
     * @param  String $controller
     * @return Aimo_Controller
     */
    public function setDefaultController($controller = 'index')
    {
        $this->_defaultController = $controller;
        return $this;
    }
    /**
     * Set the default action 
     * @param   String $action 
     * @return  Aimo_Controller
     */
    public function setDefaultAction($action = 'index')
    {
        $this->_defaultAction = $action;
        return $this;
    }
    /**
     * Add default modules,not includes default module
     *
     * @param  array $modules 
     * @return Aimo_Controller
     */
    public function addModules($modules = array())
    {
        $this->_modules = array_merge($this->_modules,$modules);
        return $this;
    }
    /**
     * Set the modules
     *
     * @return Aimo_Controller
     */
    public function setModules($modules)
    {
        $this->_modules = $modules;
    }
    /**
     * set the action 's templete
     *
     * @param  String $tpl  
     * @return Aimo_Controller
     */
    public function setTpl($tpl = null)
    {
        if (null !== $tpl) {
            $this->_tpl = $tpl;
        }
        return $this;
    }
    /**
     * Set the base templets dir 
     *
     * @param  String $tpldir
     * @return Aimo_Controller
     */
    public function setTplDir($tpldir = null)
    {
        if (null !== $tpldir) {
            $this->_tplDir = $tpldir;
        }
        return $this;
    }
    /**
     * Set no render templete
     *
     * @return Aimo_Controller
     */
    public function setNoRender($noRender = true)
    {
        $this->_noRender = $noRender;
        return $this;
    }
    /**
     * Get no render templete
     *
     * @return boolean
     */
    public function getNoRender()
    {
        return $this->_noRender;
    }
    /**
     * Analyze the url ,manaul get the $_GET array,last merge the Original array.
     *
     * @return Aimo_Controller
     */
    public function intParams()
    {
        $request_uri = $_SERVER['REQUEST_URI'];
        
       // support path info such as index.php/company/list
        if (isset($_SERVER['PATH_INFO']) && null !== $_SERVER['PATH_INFO'] 
                && is_string($_SERVER['PATH_INFO']) && !empty($_SERVER['PATH_INFO'])) {
            
            $request_uri = $_SERVER['PATH_INFO'];
        }
        
        $request_uri = ltrim($request_uri,'\/');

        //precess index  / /index.php 
        if ($request_uri == '' || $request_uri == 'index.php') {
            
            $this->_params = array('_m' => $this->_defaultModule,
                                   '_c' => $this->_defaultController,
                                   '_a' => $this->_defaultAction,
                                );            
            return $this;
        }
        // strip ?
        if (false !== ($pos = strpos($request_uri, '?'))) {
             $request_uri = substr($request_uri, 0, $pos);
        } 
         
        //first strip baseurl ,last operation if path info 
        if ($this->_baseUrl !='' 
            && strpos($request_uri,$this->_baseUrl) !==false) {
             $request_uri = substr($request_uri,strlen($this->_baseUrl));
        }
        $query_string = null;
        //second strip Suffix 
        if (strpos($request_uri,$this->_urlSuffix) !== false) {
            $request_uri  = substr($request_uri,0,-strlen($this->_urlSuffix));
            $query_string = '';
        }
        // trim last slash
        $request_uri = rtrim($request_uri,'\/');
        $path        = explode('/',$request_uri);
        
        if ($query_string === '') {
            $query_string = array_pop($path);

        }
        if (isset($path[0]) && is_string($path[0])
                 && in_array($path[0], $this->_modules)) {
            $this->_params['_m'] = array_shift($path);
        }
        
        if (!isset($this->_params['_m'])) {
            $this->_params['_m'] = $this->_defaultModule;
        }

        if (count($path) && !empty($path[0])) {
            $tmp_c    = $path[0];
            $mvc_path = $this->_params['_m'].DIRECTORY_SEPARATOR.$tmp_c;
            $controllerPath = $this->_appRoot.'modules'.DIRECTORY_SEPARATOR
                                .$mvc_path.'.php';
            //Aimo_Debug::dump($controllerPath);
            if (is_file($controllerPath)) {
                $this->_params['_c'] = array_shift($path);
            }
        }
        if (!isset($this->_params['_c'])) {         
            $this->_params['_c'] = $this->_defaultController;
        }
        if (count($path) && !empty($path[0])
             && preg_match('/[a-z0-9]+/i',$path[0])) {
            $this->_params['_a'] = array_shift($path);
        }
        if (!isset($this->_params['_a'])) {
            $this->_params['_a'] = $this->_defaultAction;
        }        
        $count = 0;
        $query_string = str_replace(array('_','--'), array('&','='),
                                    $query_string, $count);
        
        if ($count%2 == 0) {
            $query_string = '_first_='.$query_string;
        }
        parse_str($query_string,$args);
		
		if(isset($args) && count($args)){
			$_GET = array_merge($_GET,$args); 
		}

        $this->_params = array_merge($this->_params,$_GET,$_POST,
                                    $_SERVER,$_COOKIE);
        
        return $this;
    }
    /**
     * Set params function
     *
     * @param array $params
     * @return Aimo_Controller
     */
    public function setParams($params)
    {
        $this->_params = $params;
        return $this;
    }
    /**
     * Get the param by $key from $_GET,$_params,$_POST
     *
     * @param  $key the array's key
     * @return String
     */
    public function &getParam($key)
    {
        $value = null;
        if (isset($this->_params[$key])) {
            $value = $this->_params[$key];
        }
        return $value;
    }
    /**
     * Build URL
     * @params array $params   format like $this->_params
     * @params String $urlMode -1 default urlMode 
     * @return String   
     */
    public function url($params=array(),$urlMode = -1)
    {
        
		$mvc_params = array();
        $url  = '/'.$this->_baseUrl;
       
        $mvc_params['_m'] = isset($params['_m'])?$params['_m']:$this->_params['_m'];
        if ($mvc_params['_m'] == $this->_defaultModule) {
            unset($mvc_params['_m']);
        }
        $mvc_params['_c'] = isset($params['_c'])?$params['_c']:$this->_params['_c'];
        if ($mvc_params['_c'] == $this->_defaultController) {
            unset($mvc_params['_c']);
        }
        $mvc_params['_a'] = isset($params['_a'])?$params['_a']:$this->_params['_a'];
        
        if ($mvc_params['_a'] == $this->_defaultController) {
            unset($mvc_params['_a']);
        }
        $urlMode = $urlMode == -1?$this->_urlMode:$urlMode;
        
        unset($params['_m'],$params['_c'],$params['_a']);

        if ($urlMode == 0 || $urlMode == 1) {
            if ($urlMode == 1) {
                $url .= 'index.php/';
            }
            $url    .= implode('/',$mvc_params)."/";
            $tmp_url = '';
            foreach($params as $k => $v){
                if(!isset($v) || (string)$v ===''){
                    unset($params[$k]);
                }
            }
            if (count($params)) {
                $tmp_url = http_build_query($params,'_'); 
                if (strpos($tmp_url,'_first_') !== false) {
                    $tmp_url = str_replace('_first_=','',$tmp_url);
                }
                $tmp_url  = str_replace(array('=','&'),array('--','_'),$tmp_url);
                $tmp_url .= $this->_urlSuffix;
            }
            $url .= $tmp_url;
        }else if ($urlMode == 2) {
            $mvc_params = array_merge($mvc_params,$params);
            $url .= 'index.php?'.http_build_query($mvc_params,'&amp;');
        }
        if ($url == '//') {
            $url = '/';
        }
        return $url;
    }
    /**
     * Redirect to an address,if the $params is an array invoke url method convert to url
     *
     * @param mixed $params
     * @param String $code   http status
     * @return void
     */
    public function redirect($params,$code = '302')
    {
        $url = '';
        if (is_array($params)) {
            $url = $this->url($params);
        }else {
            $url = $params;
        }
        if ($code == '301') {
            header('HTTP/1.1 301 Moved Permanently');  
        }
        header("Location: $url");
    }
    /**
     * Rend template 
     *
     * Render View
     * @return String
     */
    public function render()
    {
        $tpl = array(
            $this->_appRoot.'templets'.DIRECTORY_SEPARATOR,
            $this->_tplDir.DIRECTORY_SEPARATOR,
            $this->_params['_m'].DIRECTORY_SEPARATOR,
            $this->_params['_c'].DIRECTORY_SEPARATOR,
            $this->_params['_a'],
            '.phtml'
            );
        if (null === $this->_tpl) {
            
            $this->_tpl = implode('',$tpl);
        }else {
            $tpl[4] = $this->_tpl;
            $tmptpl = array_slice($tpl,0,5);
            $this->_tpl = implode('',$tmptpl).$tpl[5];
        }
        if (!is_file($this->_tpl)) {
            //if tpl not exists in tplDir find it in default Dir
            if ($this->_tplDir != 'default') {
                $tpl[1]     = 'default'.DIRECTORY_SEPARATOR;
                $this->_tpl = implode('',$tpl);  
                if (!is_file($this->_tpl)) {
                    throw new Exception('Templete '.$this->_tpl.' NOT EXISTS!');    
                }
            }else {
                throw new Exception('Templete '.$this->_tpl.' NOT EXISTS!');    
            }
        }
        ob_start();
        include $this->_tpl;
        $html = ob_get_clean();   
  
        return $html;
    }
    /**
     * include a header or a footer file in module path
     *
     * @param  Strng $filename
     * @return void
     */
    public function layout($filename)
    {
        $path = $this->_appRoot.'templets'.DIRECTORY_SEPARATOR
             .$this->_tplDir.DIRECTORY_SEPARATOR
             .$this->_params['_m'].DIRECTORY_SEPARATOR;
             //.$this->_params['_c'].DIRECTORY_SEPARATOR;
        include $path.$filename.'.phtml';       
    }
}