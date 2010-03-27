<?php
/**
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
 * ->setBackground(0x00, 0x00, 0xff)
 * ->setColor(array(0x00, 0x00, 0x00))
 * ->create(true); // create and output
 * ?></code>
 *
 * @package Tic
 * @license http://www.gnu.org/licenses/lgpl.txt Lesser General Public License
 * @author Andrea Belvedere
 * @copyright  (c) 2009 Andrea Belvedere
 * @version 1.0
 */
abstract class TIC
{
	/**
	 * full path to a font
	 * @var string
	 */
	protected $font;
	/**
	 * image background color
	 * <code> array(0xff, 0x00, 0x00); // red </code>
	 * @var array
	 */
	protected $background;
	/**
	 * text color
	 * <code> array(0x00, 0xff, 0x00); // green </code>
	 * @var array
	 */
	protected $color;
	/**
	 * text to write to image
	 * @var string
	 */
	protected $text;
	/**
	 * width of image
	 * @var int
	 */
	protected $width;
	/**
	 * stores the actual height of the font
	 * @var int
	 */
	protected $height;
	/**
	 * font size
	 * @var int
	 */
	protected $size;
	/**
	 * padding, spacing between end of image and beginning of text
	 * @var int
	 */
	protected $padding;
	/**
	 * GD resource
	 * @var resource
	 */
	protected $gd;
	/**
	 * In degrees,  0 is 'left to write' reading.
	 * @var int
	 */
	protected $angle;

	/**
	 * @param string $font Path to a font
	 */
	public function __construct($font)
	{
		$this->font = $font;
		$this->background = array(0xff, 0xff, 0x00);
		$this->color = array(0xff, 0x00, 0x00);
		$this->text = 'TIC - Text Image Converter';
		$this->width = 0;
		$this->size = 12;
		$this->padding = 0;
		$this->gd = null;
		$this->angle = 0;
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
	public function setWidth($width)
	{
		$this->width = $width;
		return $this;
	}

	/**
	 * @param int $size Set the font size
	 * @return TIC method is chainable
	 */
	public function setSize($size)
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
	public function setColor($rgb, $g = 0x00, $b = 0x00)
	{
		if (is_array($rgb)) {
			$this->color = $rgb;
		}
		else {
			$this->color = array($rgb, $g, $b);
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
	 * @param int|array $rgb If int (red) should be between 0 and 255, if array should have 3 ints, each between 0 and 255
	 * @param int $g Ignored if $rgb is an array, is the green part of RGB
	 * @param int $b Ignored if $rgb is an array, is the blue part of RGB
	 * @return TIC method is chainable
	 */	
	public function setBackground($rgb, $g = 0x00, $b = 0x00)
	{
		if (is_array($rgb)) {
			$this->background = $rgb;
		}
		else {
			$this->background = array($rgb, $g, $b);
		}
		return $this;
	}

	/**
	 * @param int $padding The padding between the text and the border of the image
	 * @return TIC method is chainable
	 */
	public function setPadding($padding)
	{
		$this->padding = $padding;
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
		list($width, $this->height) = $this->dimension();
		if ($this->width <= 0) {
			$this->width = $width + ($this->padding * 2);
		}
		$real_width = $this->width - ($this->padding * 2);
		$lines = '';
		$wline = 0;
		$segs = preg_split('/\s+/', $this->text, -1, PREG_SPLIT_NO_EMPTY);
		$words = count($segs);
		for ($i = 0; $i < $words; $i++) {
			if (($i + 1) < $words) {
				$segs[$i] .= ' ';
			}
			list($width, $height) = $this->dimension($segs[$i]);
			$width--; // remove 1 pixel for antialiasing

			if (($wline + $width) <= $real_width) {
				$lines .= $segs[$i];
				$wline += $width;
			}
			else if ($width > $real_width) {
				$this->wrap_line($lines, $wline, $segs[$i], $real_width);
			}
			else {
				$wline = $width;
				$lines .= "\n{$segs[$i]}";
			}
		}
		return $lines;
	}

	/**
	 * Wraps a word into multiple lines if the word is wider than $this->width - ($this->padding * 2)
	 *
	 * @access private
	 */
	private function wrap_line(&$lines, &$wline, $line, $max_width)
	{
		list($width, $height) = $this->dimension($line);
		if ($width > $max_width) {
			$text_array = str_split($line);
			$left_over = array();
			while((null !== ($ch = array_pop($text_array))) && ($width > $max_width)) {
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
					$this->wrap_line($lines, $wline, implode('', array_reverse($left_over)), $max_width);
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