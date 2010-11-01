<?php
/**
 * Aimo Framework
 *
 * LICENSE
 *
 * This is not a free software,perhaps open source later.
 *
 * @category   Aimo
 * @package    Aimo_Captcha
 * @copyright  Copyright (c) 2010 Aimosoft Studio. (http://www.aimosoft.cn)
 * @version    $Id$
 */

/**
 * @category   Aimo
 * @package    Aimo_Captcha
 * @copyright  Copyright (c) 2010 Aimosoft Studio. (http://www.aimosoft.cn)
 * @license    Not free
 * @author     Jackie(jackie@aimosft.cn)
 */
class Aimo_Captcha
{
    /**
     * The width of the image
     *
     * @var int
     **/
    protected $_width = 100;
    
    /**
     * The height of the image
     *
     * @var int
     **/
    protected $_height = 30;
    
    
    /**
     * integer the font size. For example,
     *
     * @var int
     **/
    protected $_fontSize = 12;    
    /**
     * length for randomly generated word. Defaults to 6
     *
     * @var int
     **/
    protected $_wordLength = 6;
    
    /**
     * generated word. 
     *
     * @var String
     **/
    protected $_word = '';    
    /**
     * undocumented class variable
     *
     * @var string
     **/
    protected $_font = null;
    
    /**
     * Number of noise dots on image
     * Used twice - before and after transform
     *
     * @var int
     */
    protected $_dotNoiseLevel = 100;
    
    /**
     * Number of noise lines on image
     * Used twice - before and after transform
     *
     * @var int
     */
    protected $_lineNoiseLevel = 5;
    
    /**
     * set the image width
     *
     * @param int $width
     * @return Aimo_Captcha
     */
    public function setWidth($width)
    {
        $this->_width = $width;
        return $this;
    }
      
    /**
     * set the image height
     *
     * @param int $height
     * @return Aimo_Captcha
     */
    public function setHeight($height)
    {
        $this->_height = $height;
        return $this;
    }  
    
    /**
     * set the word length
     *
     * @param  int $wordlength
     * @return Aimo_Captcha
     */
    public function setWordLength($wordlength)
    {
        $this->_wordLength = $wordlength;
        return $this;
    }
    
    /**
     * set the image text font
     *
     * @param String $font
     * @return Aimo_Captcha
     */
    public function setFont($font)
    {
        $this->_font = $font;
        return $this;
    }

    /**
     * set the image text font size
     *
     * @param String $fontsize
     * @return Aimo_Captcha
     */
    public function setFontSize($fontsize)
    {
        $this->_fontSize = $fontsize;
        return $this;
    }
    
    /**
     * @param int $dotNoiseLevel
     * @return Aimo_Captcha
     */
    public function setDotNoiseLevel ($dotNoiseLevel)
    {
        $this->_dotNoiseLevel = $dotNoiseLevel;
        return $this;
    }
   /**
     * @param int $lineNoiseLevel
     * @return Aimo_Captcha
     */
    public function setLineNoiseLevel ($lineNoiseLevel)
    {
        $this->_lineNoiseLevel = $lineNoiseLevel;
        return $this;
    }    
    /**
     * construct function
     *
     * @param  array $options
     * @return void
     **/
    public function __construct($options = array())
    {
        foreach ($options as $key => $value) {
            $method = 'set'.ucfirst($key);
            if (method_exists(__CLASS__,$method)) {
                $this->$method($value);
            }else {
                trigger_error($method.' does not exists !',E_USER_NOTICE);
            }
        }
    }
    
    /**
     * generate the word 
     *
     * @return void
     */
    public function genWord()
    {

		$length= $this->_wordLength;

		$letters ='bcdfghjklmnpqrstvwxyz';
		$vowels  ='aeiou';
		$code='';
		for($i=0;$i<$length;++$i)
		{
			if($i%2 && rand(0,10)>2 || !($i%2) && rand(0,10)>9)
				$code.=$vowels[rand(0,4)];
			else
				$code.=$letters[rand(0,20)];
		}
		$this->_word = $code;
		return $code;
    }
    /**
     * Generate random frequency
     *
     * @return float
     */
    protected function _randomFreq()
    {
        return mt_rand(700000, 1000000) / 15000000;
    }

    /**
     * Generate random phase
     *
     * @return float
     */
    protected function _randomPhase()
    {
        // random phase from 0 to pi
        return mt_rand(0, 3141592) / 1000000;
    }

    /**
     * Generate random character size
     *
     * @return int
     */
    protected function _randomSize()
    {
        return mt_rand(300, 700) / 100;
    }
    /**
     * Generate image captcha
     *
     * Override this function if you want different image generator
     * Wave transform from http://www.captcha.ru/captchas/multiwave/
     *
     * @param string $id Captcha ID
     * @param string $word Captcha word
     */
    public function generateImage($word = null)
    {
        if (!extension_loaded("gd")) {
			trigger_error('Image CAPTCHA requires GD extension',E_USER);  
        }

        if (!function_exists("imagepng")) {
			trigger_error('Image CAPTCHA requires PNG suppor',E_USER);      
        }

        if (!function_exists("imageftbbox")) {
			trigger_error('Image CAPTCHA requires FT fonts support',E_USER);
        }
        if ($word == null) {
            $word = $this->_word?$this->_word:$this->genWord();
        }
        $font = $this->_font?$this->_font:dirname(__FILE__).DIRECTORY_SEPARATOR.'Duality.ttf';

        if (empty($font)) {
           trigger_error('Image CAPTCHA requires font',E_USER);
        }

        $w     = $this->_width;
        $h     = $this->_height;
        $fsize = $this->_fontSize;       
        $img        = imagecreatetruecolor($w, $h);
        
        $text_color = imagecolorallocate($img, 0,0,0);
                        
        $bg_color   = imagecolorallocate($img, 255,255,255);
        
        imagefilledrectangle($img, 0, 0, $w-1, $h-1, $bg_color);
        
        $textbox = imageftbbox($fsize, 0, $font, $word);
        $x = ($w - ($textbox[2] - $textbox[0])) / 2;
        $y = ($h - ($textbox[7] - $textbox[1])) / 2;
        imagettftext($img, $fsize, 0, $x, $y, $text_color, $font, $word);

       // generate noise
        for ($i=0; $i<$this->_dotNoiseLevel; $i++) {
           imagefilledellipse($img, mt_rand(0,$w), mt_rand(0,$h), 2, 2, $text_color);
        }
        for($i=0; $i<$this->_lineNoiseLevel; $i++) {
           imageline($img, mt_rand(0,$w), mt_rand(0,$h), mt_rand(0,$w), mt_rand(0,$h), $text_color);
        }
		
        // transformed image
        $img2     = imagecreatetruecolor($w, $h);
        //imagecolordeallocate($img2,$text_color);
        $bg_color = imagecolorallocate($img2, 255, 255, 255);
        imagefilledrectangle($img2, 0, 0, $w-1, $h-1, $bg_color);
        // apply wave transforms
        $freq1 = $this->_randomFreq();
        $freq2 = $this->_randomFreq();
        $freq3 = $this->_randomFreq();
        $freq4 = $this->_randomFreq();

        $ph1 = $this->_randomPhase();
        $ph2 = $this->_randomPhase();
        $ph3 = $this->_randomPhase();
        $ph4 = $this->_randomPhase();

        $szx = $this->_randomSize();
        $szy = $this->_randomSize();
        
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $sx = $x + (sin($x*$freq1 + $ph1) + sin($y*$freq3 + $ph3)) * $szx;
                $sy = $y + (sin($x*$freq2 + $ph2) + sin($y*$freq4 + $ph4)) * $szy;

                if ($sx < 0 || $sy < 0 || $sx >= $w - 1 || $sy >= $h - 1) {
                    continue;
                } else {
                    $color    = (imagecolorat($img, $sx, $sy) >> 16)         & 0xFF;
                    $color_x  = (imagecolorat($img, $sx + 1, $sy) >> 16)     & 0xFF;
                    $color_y  = (imagecolorat($img, $sx, $sy + 1) >> 16)     & 0xFF;
                    $color_xy = (imagecolorat($img, $sx + 1, $sy + 1) >> 16) & 0xFF;
                }
                if ($color == 255 && $color_x == 255 && $color_y == 255 && $color_xy == 255) {
                    // ignore background
                    continue;
                } elseif ($color == 0 && $color_x == 0 && $color_y == 0 && $color_xy == 0) {
                    // transfer inside of the image as-is
                    $newcolor = 0;
                } else {
                    // do antialiasing for border items
                    $frac_x  = $sx-floor($sx);
                    $frac_y  = $sy-floor($sy);
                    $frac_x1 = 1-$frac_x;
                    $frac_y1 = 1-$frac_y;

                    $newcolor = $color    * $frac_x1 * $frac_y1
                              + $color_x  * $frac_x  * $frac_y1
                              + $color_y  * $frac_x1 * $frac_y
                              + $color_xy * $frac_x  * $frac_y;

                }
                imagesetpixel($img2, $x, $y, imagecolorallocate($img2, $newcolor, $newcolor, $newcolor));
            }
        }

        // generate noise
        for ($i=0; $i<$this->_dotNoiseLevel; $i++) {
            imagefilledellipse($img2, mt_rand(0,$w), mt_rand(0,$h), 2, 2, $text_color);
        }
        for ($i=0; $i<$this->_lineNoiseLevel; $i++) {
           imageline($img2, mt_rand(0,$w), mt_rand(0,$h), mt_rand(0,$w), mt_rand(0,$h), $text_color);
        }

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Transfer-Encoding: binary');
		header("Content-type: image/png");
        imagepng($img2);
        imagedestroy($img);
        imagedestroy($img2);
    }
}
