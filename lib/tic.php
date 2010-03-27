<?php
/**
 * @package tic
 * TIC - Text Image Converter
 * Copyright (C) 2009  Andrea Belvedere
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 */
/**
 * TIC - Text Image Converter.
 * Use this class to instantiate concrete classes of TIC
 * or extend it to support other font types besides ttf.
 * <code><?php
 * // return and instance of TIC_ttf (based on font file extension)
 * $tic = TIC::factory('/path/to/font.ttf');
 * // methods are chainable
 * $tic->setText('Hello World !')
 * ->setBgColor(0x00, 0x00, 0xff)
 * ->setFontColor(array(0x00, 0x00, 0x00))
 * ->create(true); // create and output
 * ?></code>
 *
 * @package tic
 * @license http://www.gnu.org/licenses/lgpl.txt Lesser General Public License
 * @author Andrea Belvedere
 * @copyright  (c) 2010 Andrea Belvedere
 * @version 1.1
 */
abstract class TIC
{
    /**
     * @var string full path to a font
     */
    protected $font;

    /**
     * image background color
     * <code> array(0xff, 0x00, 0x00); // red </code>
     * @var array
     */
    protected $bgColor;

    /**
     * text color
     * <code> array(0x00, 0xff, 0x00); // green </code>
     * @var array
     */
    protected $fontColor;

    /**
     * text to write to image
     * @var string
     */
    protected $text;

    /**
     * stores width of text, it depends on the font used
     * @var int
     */
    protected $textWidth;

    /**
     * @var int stores the actual height of the font
     */
    protected $textHeight;

    /**
     * Width of the image, if set and greater than 0 will takes
     * priority over $textWidth
     * @var int
     */
    protected $imgWidth;

    /**
     * Height of the image, if set and greater than 0 will takes
     * priority over $textHeight;
     * @var int
     */
    protected $imgHeight;

    /**
     * @var int font size
     */
    protected $size;

    /**
     * Vertical Padding top and bottom padding, spacing between end of image and the text
     * @var int
     */
    protected $verPadding;

    /**
     * left and right padding, spacing between end of image and the text
     * @var int
     */
    protected $horPadding;

    /**
     * @var resource GD resource
     */
    protected $gd;

    /**
     * In degrees,  0 is 'left to write' reading.
     * @var int
     */
    protected $angle;

    /**
     * if true vertical align text
     * @var bool
     */
    protected $valign;

    /**
     * if true horizontal align text
     * @var bool
     */
    protected $align;


    /**
     * @param string $font Path to a font
     */
    public function __construct($font)
    {
        $this->font = $font;
        $this->bgColor = array(0xff, 0xff, 0x00);
        $this->fontColor = array(0xff, 0x00, 0x00);
        $this->text = 'TIC - Text Image Converter';
        $this->textWidth = 0;
        $this->textHeight = 0;
        $this->imgWidth = 0;
        $this->imgHeight = 0;
        $this->size = 12;
        $this->verPadding = 0;
        $this->horPadding = 0;
        $this->gd = null;
        $this->angle = 0;
        $this->valign = false;
        $this->align = false;
    }

    /**
     * Free $this->gd resource
     */
    public function __destruct()
    {
        if (is_resource($this->gd)) {
            imagedestroy($this->gd);
            $this->gd = null;
        }
    }

    /**
     * @param string $font Path to a font
     * <code><?php
     * $tic = TIC::factory('relative/path/font.ttf');
     * $tic->setText('Hello World !')
     * ->create(true);
     * ?></code>
     * @return TIC An instance of a Text Image Converter, based on the font extension
     */
    public static function factory($font)
    {
        if (!is_file($font)) {
            throw new Exception("Could not find font {$font}");
        }
        $ext = strtolower(pathinfo($font, PATHINFO_EXTENSION));
        switch ($ext) {
        case 'ttf':
        case 'otf':
            require_once('tic_ttf.php');
        return new TIC_ttf($font);
        default:
            throw new Exception("Unsupported font {$ext}");
        }
    }

    /**
     * @param int $width Set the width of the image
     * @return TIC method is chainable
     */
    public function setImgWidth($width)
    {
        $this->imgWidth = $width;
        return $this;
    }

    /**
     * @param int $height Set the height of the image
     * @return TIC method is chainable
     */
    public function setImgHeight($height)
    {
        $this->imgHeight = $height;
        return $this;
    }

    /**
     * @param int $size Set the font size
     * @return TIC method is chainable
     */
    public function setFontSize($size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * @param int|array $rgb If int (red) should be between 0 and 255, if array should have 3 ints, each between 0 and 255
     * @param int $g Ignored if $rgb is an array, is the green part of RGB
     * @param int $b Ignored if $rgb is an array, is the blue part of RGB
     * @return TIC method is chainable
     */
    public function setFontColor($rgb, $g = 0x00, $b = 0x00)
    {
        if (is_array($rgb)) {
            $this->fontColor = $rgb;
        }
        else {
            $this->fontColor = array($rgb, $g, $b);
        }
        return $this;
    }

    /**
     * @param int|array $rgb If int (red) should be between 0 and 255, if array should have 3 ints, each between 0 and 255
     * @param int $g Ignored if $rgb is an array, is the green part of RGB
     * @param int $b Ignored if $rgb is an array, is the blue part of RGB
     * @return TIC method is chainable
     */
    public function setBgColor($rgb, $g = 0x00, $b = 0x00)
    {
        if (is_array($rgb)) {
            $this->bgColor= $rgb;
        }
        else {
            $this->bgColor = array($rgb, $g, $b);
        }
        return $this;
    }

    /**
     * @param string $text Text to write in the image
     * @return TIC method is chainable
     */
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    /**
     * @param int $padding The vertical padding (top and bottom)
     * between the text and the border of the image
     * @return TIC method is chainable
     */
    public function setVerPadding($padding)
    {
        $this->verPadding = $padding;
        return $this;
    }

    /**
     * @param int $padding The horizonal padding (left and right)
     * between the text and the border of the image
     * @return TIC method is chainable
     */
    public function setHorPadding($padding)
    {
        $this->horPadding = $padding;
        return $this;
    }

    public function setPadding($padding)
    {
        $this->verPadding = $padding;
        $this->horPadding = $padding;
        return $this;
    }

    /**
     * @param int $angle Angle to write the text in
     * @return TIC method is chainable
     */
    public function setAngle($angle)
    {
        $this->angle = $angle;
        return $this;
    }

    /**
     * @param bool $valign If true valign text
     * @return TIC method is chainable
     */
    public function setVerAlign($valign = true)
    {
        $this->valign = (bool)$valign;
        return $this;
    }

    /**
     * @param bool $align If true align text
     * @return TIC method is chainable
     */
    public function setHorAlign($align = true)
    {
        $this->align = (bool)$align;
        return $this;
    }

    /**
     * @param string $type Supported types are 'png', 'jpeg|jpg' and gif
     * @return void
     */
    public function render($type = 'png')
    {
        switch ($type) {
        case 'png':
            header('Content-type: image/png');
            imagepng($this->gd);
            break;
        case "jpeg":
        case "jpg":
            header('Content-type: image/jpeg');
        imagejpeg($this->gd);
        break;
        case "gif":
            header('Content-type: image/gif');
            imagegif($this->gd);
            break;
        default:
            throw new Exception ("Unknown content-type {$type}");
        }
    }

    /**
     * Wraps $this->text into multiple lines if wider then $this->width - ($this->padding * 2)
     * @access protected
     */
    protected function wrap_text()
    {
        // retrieve the current text width and height
        list($this->textWidth, $this->textHeight) = $this->dimension();

        // if the imgWidth is not set just return the text
        if (! isset($this->imgWidth) || ($this->imgWidth <= 0))
        {
            return $this->text;
        }
        // if the image width is set > then the text width plus
        // the padding just return the text
        if ($this->imgWidth >= ($this->textWidth + ($this->horPadding * 2)))
        {
            return $this->text;
        }
        // else wrap the text, the calculated width of the text is wider
        // then the required image width so we wrap the lines
        $lines = '';
        $wline = 0;
        $segs = preg_split('/\s+/', $this->text, -1, PREG_SPLIT_NO_EMPTY);
        $words = count($segs);
        for ($i = 0; $i < $words; $i++)
        {
            $segs[$i] = trim($segs[$i]);

            // add a space at the end of the
            // word, unless is the last word
            if (($i + 1) < $words) {
                $segs[$i] .= ' ';
            }
            //
            list($width, $height) = $this->dimension($segs[$i]);
            $width--; // remove 1 pixel for antialiasing

            if (($wline + $width) <= $this->imgWidth) {
                $lines .= $segs[$i];
                $wline += $width;
            }
            else if ($width > $this->imgWidth) {
                $this->wrap_word($lines, $wline, $segs[$i], $this->imgWidth);
            }
            else {
                $wline = $width;
                $lines .= "\n{$segs[$i]}";
            }
        }
        return $lines;
    }

    /**
     * Wraps a word into multiple lines if the word is wider than $this->imgWidth
     *
     * @access private
     */
    protected function wrap_word(&$lines, &$wline, $line, $max_width)
    {
        list($width, $height) = $this->dimension($line);
        if ($width > $max_width)
        {
            $text_array = str_split($line);
            $left_over = array();
            while((null !== ($ch = array_pop($text_array))) && ($width > $max_width))
            {
                $text = implode('', $text_array);
                $left_over[] = $ch;

                list ($width, $height) = $this->dimension($text);
                if ($width <= $max_width) {

                    if (($wline + $width) <= $max_width) {
                        $lines .= $text;
                        $wline += $width;
                    }
                    else {
                        $wline = $width;
                        $lines .= "\n{$text}";
                    }
                    $this->wrap_word($lines, $wline, implode('', array_reverse($left_over)), $max_width);
                }
            }
        }
        else {
            if (($wline + $width) <= $max_width) {
                $lines .= $line;
                $wline += $width;
            }
            else {
                $wline = $width;
                $lines .= "\n{$line}";
            }
        }
    }

    /**
     * Calculates text width, height and x, y coordinates where drawing starts.
     * If a parameter is not passed or passed with negative value
     * then the class property is going to be used
     *
     * @param string $text If -1 then $this->text is going to be used
     * @param int $size font size, depending on GD version is going to be interpreded as points or pixels
     * @param int $angle Angle in degree text should be written into image.
     * @param string $font Full or Relative path to a True type font
     * @return array array($width, $height)
     * <code>
     * <?php list($width, $height, $x, $y) = $this->dimension(); ?>
     * </code>
     */
    abstract protected function dimension($text = -1, $size = -1, $angle = -1, $font = -1);

    /**
     * Creates the image with the text, optionally output the image
     *
     * @param boolean $render Set to true to render the image
     * @param string $type png|jpg|jpeg|gif
     * @return TIC
     */
    abstract public function create($render = false, $type = 'png');
}