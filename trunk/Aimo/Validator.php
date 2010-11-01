<?php
/**
 * Aimo Framework
 *
 * LICENSE
 *
 * This is not a free software,perhaps open source later.
 *
 * @category   Aimo
 * @package    Aimo_Validitor
 * @copyright  Copyright (c) 2010 Aimosoft Studio. (http://www.aimosoft.cn)
 * @version    $Id$
 */

/**
 * @category   Aimo
 * @package    Aimo_Validitor
 * @copyright  Copyright (c) 2010 Aimosoft Studio. (http://www.aimosoft.cn)
 * @license    Not free
 * @author     Jackie(jackie@aimosft.cn)
 */
class Aimo_Validator {
    
    /**
     * if the value is Not empty
     * @param String  $value
     * @return boolean
     */
    public static function NotEmpty($value)
    {
        return !empty($value);
    }
    
    /**
     * if the value is strictly not empty
     *
     * @param String  $value
     * @return boolean
     */
    public static function StrictNotEmpty($value)
    {
        if (trim($value) === '') {
            return false;
        }else {
            return true;
        }
    }
    /**
    * Equal another value
    *
    * @param  String $value
    * @param  String $to
    * @return boolean
    */
   public static function Equal($value, $to)
   {
       if ($value === $to) {
           return true;
       }else {
           return false;
       }
   }   
    /**
     * if the value is integer
     *
     * @param String  $value
     * @return boolean
     */
    public static function Numeric($value)
    {
        if (is_numeric($value)) {
            return true;
        }else {
            return false;
        }
    }
    
    /**
     * if the value great than $max
     *
     * @param String  $value
     * @param int     $max
     * @return boolean
     */
    public static function Max($value, $max = 0)
    {
        if ((int)$value >= $max) {
            return false;
        }else {
            return true;
        }
    }
    
    /**
     * if the value less than $min
     *
     * @param String  $value
     * @param int     $min
     * @return boolean
     */
    public static function Min($value, $min = 0)
    {
        if ((int)$value <= $min) {
            return false;
        }else {
            return true;
        }
    }    
    
    /**
     * if the $value between $min and $max
     *
     * @param String  $value
     * @param int     $min
     * @param int     $max
     * @return boolean
     */
    public static function Range($value, $min = 0, $max = 0)
    {
        $value = (int)$value;
        if ($value >= $min && $value <= $max) {
            return true;
        }else {
            return false;
        }
    }
    
    /**
     * if the string 's length greater than $max
     *
     * @param String  $value
     * @param int     $max
     * @param String  $encode
     * @return boolean
     */
    public static function LenMax($value, $max=0, $encode = 'UTF-8')
    {
        $length = iconv_strlen($value,$encode);
        if ($length > $max) {
            return false;
        }else {
            return true;
        }
    }
    
    /**
     * if the string 's length less than $min
     *
     * @param String  $value
     * @param int     $max
     * @param String  $encode
     * @return boolean
     */
    public static function LenMin($value, $max = 0, $encode = 'UTF-8')
    {
        $length = iconv_strlen($value,$encode);
        if ($length < $max) {
            return false;
        }else {
            return true;
        }
    }    
    
    /**
     * if the string's length between $min and $max
     *
     * @param String  $value
     * @param int     $min
     * @param int     $max
     * @param String  $encode
     * @return boolean
     */
    public static function LenRange($value, $min = 0, $max = 0, $encode = 'UTF-8')
    {
        //Aimo_Debug::dump($value);
        $length = iconv_strlen($value,$encode);
        
        if ($length >= $min && $length <= $max) {
            return true;
        }else {
            return false;
        }       
    }

    /**
     * if the value match the $pattern
     *
     * @param String  $value
     * @param string  $pattern
     * @return boolean
     */
    public static function Regex($value,$pattern)
    {
        if (preg_match($pattern,$value)) {
            return true;
        }else {
            return false;
        }
    }
    
    /**
     * Array validator  function for checkbox 
     * Validator array element the method name begin width Array
     *
     * @param  array   $value
     * @param  int     $min
     * @param  int     $max 
     * @return boolean
     */
    public static function ArrayRange($value,$min = 0,$max = 0)
    {
        if (!is_array($value)) {
            return false;
        }
        $count = count($value);
        if ($count >= $min && $count <= $max) {
            return true;
        }else {
            return false;
        }
    }
    /**
     * Must chose one for the group of checkbox
     *
     * @param  array   $value
     * @return boolean
     */
    public static function ArrayNotEmpty($value)
    {
        if (!is_array($value)) {
            return false;
        } 
        $count = count($value);
         if ($count > 0) {
             return true;  
         }else {
             return false;
         }
    }

    
}