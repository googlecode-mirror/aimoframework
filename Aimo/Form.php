<?php
/**
 * Aimo Framework
 *
 * LICENSE
 *
 * This is not a free software,perhaps open source later.
 *
 * @category   Aimo
 * @package    Aimo_Form
 * @copyright  Copyright (c) 2010 Aimosoft Studio. (http://www.aimosoft.cn)
 * @version    $Id$
 */

/**
 * @category   Aimo
 * @package    Aimo_Form
 * @copyright  Copyright (c) 2010 Aimosoft Studio. (http://www.aimosoft.cn)
 * @license    Not free
 * @author     Jackie(jackie@aimosft.cn)
 */
class Aimo_Form
{
    /**
     * The form errors collector
     *
     * @var array
     **/
    public $_errors = array(); 
    
    
    /**
     * The form's elements
     *
     * @var array
     **/
    protected $_elements = array();
    
    /**
     * Strict variables flag; when on, undefined variables accessed in the view
     * scripts will trigger notices
     * @var boolean
     */
    protected   $_strictVars = false;    
    
    /**
     * client validate status unfinished 
     *
     * @var boolean
     **/
    protected $_clientValidate = false;
    
    /**
     * I18n flag
     *
     * @var string
     **/
    protected $_enableI18n = false;
    /**
     * Construct function
     *
     * options key 
     * boolean fileConfig   if enable file config 
     * string  basePath     default app/forms 
     * boolean i18n         if enable translator
     * @return void
     */
    public function __construct($options = array())
    {
        if (isset($options['i18n']) && $options['i18n']) {
            $this->enableI18n(true);
        }
        
        if (isset($options['fileConfig']) && $options['fileConfig'] ) {
            //Manaul set form config file.
			if (isset($options['configFile'])){
				$config_file = $options['configFile'];
			}else {
				$form_path = isset($options['basePath'])?$options['basePath']
							:realpath(dirname(__FILE__).'/../../app/forms');
				$params = Aimo_Controller::getInstance()->_params;
				
				$config_file = $form_path.DIRECTORY_SEPARATOR.$params['_m']
							.DIRECTORY_SEPARATOR.$params['_c'].'_'.$params['_a'].'.php';
            }
            $form_options = array();
                
            if (is_file($config_file)) {
                $form_options = @include $config_file;
            }else {
                throw new Exception($config_file." Does not exists !");
                
            }
            foreach ($form_options as $option) {
                $type = isset($option['type'])?$option['type']:'';
                $this->addElement($type,$option);
            }
        }
    }

    /**
     * enable I18n
     *
     * @param  boolean $flag
     * @return Aimo_Form
     */
    public function enableI18n($flag = false)
    {
        $this->_enableI18n = $flag;
    }
    /**
     * Add an element for the form
     * $options format example
     * $options = array(
     *    'attribs' => array(
     *        'onclick' => 'javascript function'
     *        )
     *   'name' => 'elementName',
     *   'id'   => 'elementId',
     *   'validators' => array(
     *       'NotEmpty',
     *       'Range' => array($min,$max),
     *       'int',
     *       ...  
     *    )
     *    'filters' => array(
     *           formats like validators
     *        )
     *    )
     *    'error'  => 'error msg',
     *    'label'  => 'Element label',
     *    'description' => ' element descripton'
     *   )
     * @param  String $type the type of the element
     * @param  array  $options
     * @return Aimo_Form
     */
    public function addElement($type,$options)
    {
        require_once 'Aimo/Form/Element.php';
        
        if ($this->_enableI18n) {
 
            $options['i18n'] = true;
        }        
        $element = new Aimo_Form_Element($options);
        

       
        $element->type = $type;
        $this->{$element->name} = $element;
        $type    = strtolower($type);
        $typefun = $type.'Element';
        if (!method_exists(__CLASS__,$typefun)) {
            throw new Exception("There no an element type $type");   
        }
        $this->$typefun($element->name);
        return $this;   
    }
    /**
     * create an input text element
     *
     * @param  array  $options
     * @return Aimo_Form
     */
    protected function textElement($name)
    {
        $attrib  = $this->renderAttribs($name);
        $element = $this->$name;
        
        $tag     = '<input type="text" value="'.$element->value.'" ' 
                             .$attrib.' />'; 
        $this->{$name}->tag = $tag;
        
        return $this;
    }
    /**
     * create an input password element
     *
     * @param  array  $options
     * @return Aimo_Form
     */
    protected function passwordElement($name)
    {
        $attrib  = $this->renderAttribs($name);
        $element = $this->$name;
        
        $tag     = '<input type="password" value="'.$element->value.'" ' 
                             .$attrib.' />'; 
        $this->{$name}->tag = $tag;
        
        return $this;
    }    
    /**
     * create an input hidden element
     *
     * @param  array  $options
     * @return Aimo_Form
     */
    protected function hiddenElement($name)
    {
        $attrib  = $this->renderAttribs($name);
        $element = $this->$name;
        
        $tag     = '<input type="hidden" value="'.$element->value.'" ' 
                             .$attrib.' />'; 
        $this->{$name}->tag = $tag;
        
        return $this;      
    }
    
    /**
     * create a textarea element
     *
     * @param  array  $options
     * @return Aimo_Form
     */
    protected function textareaElement($name)
    {
        $attrib  = $this->renderAttribs($name);
        $element = $this->$name;
        
        $value   = $element->value;
        
        $tag = '<textarea'
                             .$attrib.'>'.$value.'</textarea>'; 
                             
        $this->$name->tag = $tag;
        return $this;         
    }
    
    /**
     * create a select element
     * datasource from $options['data']
     * @param  array  $options
     * @return Aimo_Form
     */
    protected function selectElement($name)
    {
        $attrib  = $this->renderAttribs($name);
        $element = $this->$name;
        $value   = $element->value;
        $option  = $element->data;
        $tag = '<select '.$attrib.' >'.PHP_EOL;
        foreach ($option as $val => $text) {
            $selected = $val == $value?' selected="selected"':'';
            $tag .= "\t\t".'<option value="'.$val.'"'.$selected.'>'
                                .$text.'</option>'.PHP_EOL;
        }
        $tag .= "\t".'</select>';
        $this->$name->tag = $tag;
        return $this;  
    }
    
    /**
     * create a or group radio Element
     * $options['data]
     * @param  array  $options
     * @return Aimo_Form
     */
    protected function radioElement($name)
    {
        $attrib  = $this->renderAttribs($name);
        $element = $this->$name;
        $value   = $element->value;
        $option  = $element->data;        
        $attrib  = preg_replace('/\s?class="([^\"]+)"\s?/i','',$attrib);
        $attrib  = preg_replace('/\s?name="([^\"]+)"\s?/i','',$attrib);
        $class   = isset($element->attribs['class'])?
                   $element->attribs['class']:'';        
        $tag = '<ul class="Aimo_Form_ul" '.$attrib." >".PHP_EOL;
        foreach ($option as $val => $lable) {
            $tag .= "\t\t".'<li><span>'.$lable.'</span><input type="radio" name="';
            $tag .= $name.'" value="'.$val.'"';
            $checked = (string)$val === $value?'checked="checked"':'';
            $tag .= ' '.$checked.' /></li>'.PHP_EOL;
        }
        $tag .='</ul>';
        
        $this->$name->tag = $tag;
        return $this;           
    }
    
    /**
     * create a or group checkbox Element
     * 
     * @param  array  $options
     * @return void
     */
    protected function checkboxElement($name)
    {   
        $attrib  = $this->renderAttribs($name);
        $element = $this->$name;
        $value   = $element->value;
        $option  = $element->data;  
        $attrib  = preg_replace('/\s?class="([^\"]+)"\s?/i','',$attrib);
        $attrib  = preg_replace('/\s?name="([^\"]+)"\s?/i','',$attrib);
        $class   = isset($element->attribs['class'])?
                   $element->attribs['class']:'';
        $tag = '<ul class="Aimo_Form_ul '.$class.'" '.$attrib." >".PHP_EOL;

        $values = explode(',',$value);
        //Aimo_Debug::dump($values);
        foreach ($option as $val => $label) {
            $tag .= "\t\t".'<li><input type="checkbox" name="';
            $tag .= $name.'[]" value="'.$val.'"';
            $checked = in_array($val,$values) ?'checked="checked"':'';
            $tag .= ' '.$checked.' /><span>'.$label.'</span></li>'.PHP_EOL;
        }
        $tag .='</ul>';
        $this->$name->tag = $tag;
        return $this;        
    }

    
    /**
     * If the post values is valid
     *
     * @return boolean
     */
    public function isValid()
    {
        $submit = true;
        require_once 'Aimo/Validator.php';
        $result = null;
        $values = $this->getValues();
        //Aimo_Debug::dump($values);
        $elements = $this->getElements();
        
        foreach ($elements as $name) {
           
             $element = $this->$name;
            $values[$name] = isset($values[$name])?$values[$name]:'';
            if (isset($values[$name]) && is_array($values[$name])) {
                $this->$name->setValue(implode(',',$values[$name]));
             }else {
                $this->$name->setValue($values[$name]);
            }
            
            $typefun = $element->type.'Element';
            $this->$typefun($element->name);
            //Aimo_Debug::dump($this->$name);
            if (!count($element->validators)) {
                continue;
            }
            foreach ($element->validators as $method => $args) {
                
                if (is_int($method)) {
                    $method = $args;
					$args   = array();
                }
                $method     = ucfirst($method);
                //add checkbox validator
                
                if (!method_exists('Aimo_Validator',$method)) {
                     trigger_error("Aimo_Validator::$method does not exist", E_USER_NOTICE);
                    continue;    
                }
                if ('Array' == substr($method,0,5) && is_array($values[$name])) {
                    
                    $args   = array_merge(array($values[$name]),$args);
                    //Aimo_Debug::dump($args);
                    $result = call_user_func_array("Aimo_Validator::$method",$args);
                    
                    if ($result == false) {
                        $submit =  false;
                        $this->$name->setError($this->$name->msg);
                        $this->_errors[$name] = $this->$name->msg;
                    }
                    continue;
                }
                $tmpValue = is_array($values[$name])?
                            $values[$name]:array($values[$name]);
                
                foreach ($tmpValue as $key => $value) {
                                    
                    $args   = array_merge(array($value),$args);
                    $result = call_user_func_array("Aimo_Validator::$method",$args);
                    
                    if ($result == false) {
                        $submit =  false;
                        $this->$name->setError($this->$name->msg);
                        $this->_errors[$name] = $this->$name->msg;
                    }
                }
                
            }                
            
        }
        return $submit;
    }
    
    /**
     * Was the request made by POST
     *
     * @return boolean
     */
    public function isPost()
    {
        if ('POST' == $_SERVER['REQUEST_METHOD']) {
            return true;
        }
        return false;
    }
    
    
    /**
     * Get the values and Filtered
     *
     * @return array
     */
    public function getValues()
    {
        if ($this->isPost()) {
            $post = &$_POST;
        }else {
            $post = &$_GET;
        }
        
        require_once 'Aimo/Filter.php';
        
        $elements = $this->getElements();
        
        foreach ($elements as $name) {
            $element = $this->$name;
            
            if (!count($element->filters)) {
                continue;
            }
            foreach ($element->filters as $method => $args) {
                if (is_int($method)) {
                    $method = $args;
                    unset($args);
                }
                $method     = ucfirst($method);
                
                if (!method_exists('Aimo_Filter',$method)) {
                    trigger_error("Aimo_Filter::$method does not exist", E_USER_NOTICE);
                }
                $args   = empty($args)?array():$args;
                $post[$name] = isset($post[$name])?$post[$name]:'';
                if (is_array($post[$name])) {
                   foreach ($post[$name] as $key => $value) {
                        $args   = array_merge(array($value),$args);
                        $post[$name][$key] = call_user_func_array("Aimo_Filter::$method",$args); 
                   }
                }else {
                    $args   = array_merge(array($post[$name]),$args);
                    
                    $post[$name] = call_user_func_array("Aimo_Filter::$method",$args);  
                }

            }
            
        }        
        return $post;
    }
    /**
     * Get all Elements
     *
     * @return array
     */
    protected function getElements(){
        
        
        if (count($this->_elements)) {
            
            return $this->_elements;
        }
        $vars = get_object_vars($this);
        //Aimo_Debug::dump(get_object_vars($this));
        $elements = array();
        foreach ($vars as $var => $val) {
            if (substr($var,0,1) == '_') {
                continue;
            }
            $elements[] = $var;
        }
        $this->_elements = $elements;
        return $elements;
    }
    
    /**
     * attribs to string 
     *
     * @return String
     */
    private function renderAttribs($name)
    {
        $element = $this->$name;
        $attribsString = '';
        $attribs = $element->attribs;
        
        $attribsString .= ' name="'.$element->name.'" '; 
        
        if (!isset($element->id)) {
            $element->id = $name;
        }
        $attribsString  .= ' id="'.$element->id.'"';
        foreach ($attribs as $key => $value) {
            $attribsString .= ' '.$key.'="'.$value.'" ';
        }
 
        if ($this->_clientValidate && isset($element->msg)) {
            $attribsString .=' msg="'.$element->msg.'" ';
        }
        if ($this->_clientValidate && count($element->validators)) {
            $attribsString .=' validators="';
            $comar = '';

            foreach ($element->validators as $key => $value) {
                
                if (is_int($key)) {
                    $key = '';
                }
                $value = is_array($value)?'('.implode(',',$value).')':$value;
                if (isset($value) && !empty($value)) {
                    $attribsString .=$comar.$key.$value;
                }else {
                    $attribsString .=$comar.$key;
                }
                $comar = ',';
            }
            $attribsString.='"'; 
        }
      
        return $attribsString;
    }
    /**
     * Enable or disable strict vars
     *
     * If strict variables are enabled, {@link __get()} will raise a notice
     * when a variable is not defined.
     *
     * to enforce strict variable handling in your view scripts.
     *
     * @param  boolean $flag
     * @return Zend_View_Abstract
     */
    public function strictVars($flag = true)
    {
        $this->_strictVars = ($flag) ? true : false;

        return $this;
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
        if ($this->_strictVars) {
            trigger_error('Key "' . $key . '" does not exist', E_USER_NOTICE);
        }
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
     * set the client validate flag default false.
     *
     * @return Aimo_Form
     */
    public function setClientValidate($flag = false)
    {
        $this->_clientValidate = $flag;
        return $this;
    }
}
