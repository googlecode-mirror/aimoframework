<?php
/**
 * Aimo Framework
 *
 * LICENSE
 *
 * This is not a free software,perhaps open source later.
 *
 * @category   Aimo
 * @package    Aimo_Filter
 * @copyright  Copyright (c) 2010 Aimosoft Studio. (http://www.aimosoft.cn)
 * @version    $Id$
 */

/**
 * @category   Aimo
 * @package    Aimo_Filter
 * @copyright  Copyright (c) 2010 Aimosoft Studio. (http://www.aimosoft.cn)
 * @license    Not free
 * @author     Jackie(jackie@aimosft.cn)
 */
class Aimo_Filter{
    /**
     * trim the space 
     *
     * @param  string $value
     * @return string
     */
    public static function Trim($value)
    {
        return trim($value);
    }
    
    /**
     * cut string
     *
     * @param  string $value
     * @param  int    $len
     * @param  string $encode
     * @return string
     */
    public static function Cut($value, $len , $encode="UTF-8")
    {
        if (empty($value)) {
            return $value;
        }
        return  iconv_substr($value,0,$len,$encode);
    }
    
    /**
     * convert the $value to int
     *
     * @param  string $value
     * @return int
     */
    public static function Int($value)
    {
        return (int)$value;
    }
}