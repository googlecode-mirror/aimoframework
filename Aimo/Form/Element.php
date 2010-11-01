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
class Aimo_Form_Element{
    
    /**
     * I18n flag
     *
     * @var string
     **/
    protected $_enableI18n = false;    
    
    /**
     * translator
     *
     * @var Aimo_Translator
     **/
    protected $_translator = null;
    
    /**
     * Element tag html
     *
     * @var string
     **/
    public $tag;    
    
    /**
     * Element name
     *
     * @var string
     **/
    public $name;
    
    /**
     * Element id
     *
     * @var string
     **/
    public $id;
    
    /**
     * Element type
     *
     * @var string
     **/
    public $type;
    
    /**
     * Element value
     *
     * @var string
     **/
    public $value;
    /**
     * Element attribs not include id name value    
     *
     * array(
     *      'class' => 'style class',
     *      'style' => 'color:red;',
     *      'onclick' => 'javascript event ;',
     *      ... add so on  
     *    
     *    )
     *
     *
     * @var array
     **/
    public $attribs = array();
    
    /**
     * Element label eg. user's name
     *
     * @var string
     **/
    public $label;
    
    /**
     * Element message 
     *
     * @var string
     **/
    public $msg;
    
    /**
     * Element error msg if invalid
     *
     * @var string
     **/
    public $error;
    
    /**
     * Element description
     *
     * @var string
     **/
    public $description;
    
    /**
     * Element validators method of class Aimo_Validator
     *
     * @var array
     **/
    public $validators = array();
    
    /**
     * Element filters method of class Aimo_Filter
     *
     * @var array
     **/
    public $filters   = array();
    
    /**
     * select checkbox redio data source
     *
     * @var array
     **/
    public $data      = array();
    
    /**
      * construct function
      *
      * @return Aimo_Form_Element
      */
    public function __construct($options)
    {
        if (isset($options['i18n']) && $options['i18n']) {
            $this->enableI18n(true);
        }
        foreach ($options as $key => $value) {
            $method = 'set'.ucfirst($key);
            if (method_exists(__CLASS__, $method)) {
                $this->$method($value);
            }
        }
        return $this;
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
     * Get tralslation
     *
     * @return string
     */
    public function t($key)
    {
        if ($this->_enableI18n) {
            $args = func_get_args();
            
            $translator = null;
            
            if (null === $this->_translator) {
                $this->_translator = Aimo_Translator::getInstance();
            }
            $translator = $this->_translator;
            $result = call_user_func_array(array($translator,'t'),$args);
            
            return $result;
        }else {
            return $key;
        }
    }
	/**
	 * @param string $tag the $tag to set
	 * @param return Aimo_Form_Element
	 */
	public function setTag($tag) {
		$this->tag = $tag;
		return $this;
	}

	/**
	 * @param string $name the $name to set
	 * @param return Aimo_Form_Element
	 */
	public function setName($name) {
		$this->name = $name;
		return $this;
	}

	/**
	 * @param string $id the $id to set
	 * @param return Aimo_Form_Element
	 */
	public function setId($id) {
		$this->id = $id;
		return $this;
	}

	/**
	 * @param string $type the $type to set
	 * @param return Aimo_Form_Element
	 */
	public function setType($type) {
		$this->type = $type;
		return $this;
	}

	/**
	 * @param string $value the $value to set
	 * @param return Aimo_Form_Element
	 */
	public function setValue($value) {
		$this->value = $value;
		return $this;
	}

	/**
	 * @param array $attribs the $attribs to set
	 * @param return Aimo_Form_Element
	 */
	public function setAttribs($attribs) {
		$this->attribs = $attribs;
		return $this;
	}

	/**
	 * @param string $label the $label to set
	 * @param return Aimo_Form_Element
	 */
	public function setLabel($label) {
		$this->label = $this->t($label);
		
		return $this;
	}

	/**
	 * @param string $msg the $msg to set
	 * @param return Aimo_Form_Element
	 */
	public function setMsg($msg) {
		$this->msg = $this->t($msg);
		return $this;
	}

	/**
	 * @param string $error the $error to set
	 * @param return Aimo_Form_Element
	 */
	public function setError($error) {
		$this->error = $this->t($error);
		return $this;
	}

	/**
	 * @param string $description the $description to set
	 * @param return Aimo_Form_Element
	 */
	public function setDescription($description) {
		$this->description = $this->t($description);
		return $this;
	}

	/**
	 * @param array $validators the $validators to set
	 * @param return Aimo_Form_Element
	 */
	public function setValidators($validators) {
		$this->validators = $validators;
		return $this;
	}

	/**
	 * @param array $filters the $filters to set
	 * @param return Aimo_Form_Element
	 */
	public function setFilters($filters) {
		$this->filters = $filters;
		return $this;
	}

	/**
	 * @param  array $data the $data to set
	 * @param return Aimo_Form_Element
	 */
	public function setData($data) {
		$this->data = $data;
		return $this;
	}


}