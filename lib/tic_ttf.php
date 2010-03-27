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
 * TIC concrete class for TrueType Fonts
 *
 * @package Tic
 * @license http://www.gnu.org/licenses/lgpl.txt Lesser General Public License
 * @author Andrea Belvedere
 * @copyright  (c) 2009 Andrea Belvedere
 * @version 1.0
 */
class TIC_ttf extends TIC
{
	/**
	 * @param string $font Full or relative path to a font
	 * <code>
	 * <?php
	 * $tic = new TIC_ttf('/full/path/font.ttf');
	 * $tic->setText('Hello World !')
	 * ->setBackground(array(0x00, 0x00, 0xff))
	 * ->setColor(0x00, 0x00, 0x00)
	 * ->create(true);
	 * ?>
	 * </code>
	 */
	public function __construct($font)
	{
		parent::__construct($font);
	}

	/**
	 * Creates the image with the text, optionally output the image
	 *
	 * @param boolean $render Set to true to render the image
	 * @param string $type png|jpg|jpeg|gif
	 * @return TIC_ttf method is chainable
	 */
	public function create($render = false, $type = 'png')
	{
		$lines = $this->wrap_text();
		list($width, $height, $x, $y) = $this->dimension($lines);
		$height += ($this->padding * 2);
		$this->width += ($this->padding * 2);

		$this->gd = imagecreatetruecolor($this->width, $height);

		list ($r, $g, $b) = $this->background;
		imagefill($this->gd, 0, 0, imagecolorallocate($this->gd, $r, $g, $b));
		list ($r, $g, $b) = $this->color;
		$color = imagecolorallocate($this->gd, $r, $g, $b);

		$x += $this->padding;
		$y += $this->padding;
		imagettftext($this->gd, $this->size, $this->angle, $x, $y, $color, $this->font, $lines);

		if ($render) {
			$this->render($type);
		}
		return $this;
	}


	/**
	 * Calculates text width, height and x, y coordinates where drawing starts.
	 * If a parameter is not passed or passed with negative value
	 * then the class property is going to be used
	 *
	 * @param string $text If -1 then $this->text is going to be used
	 * @param int $size font size, depending on GD version is going to be interpreded as points or pixels
	 * @param int $angle Angle in degree text should be written into image.
	 * @param string $font Full or relative path to a True type font
	 * @return array array($width, $height)
	 * <code>
	 * <?php list($width, $height, $x, $y) = $this->dimension(); ?>
	 * </code>
	 */
	protected function dimension($text = -1, $size = -1, $angle = -1, $font = -1) 
	{
		if ($text < 0) $text = $this->text;
		if ($size < 0) $size = $this->size;
		if ($angle < 0) $angle = $this->angle;
		if ($font < 0) $font = $this->font;

		$bbox = imagettfbbox($size, $angle, $font, $text);
		$dim = array(0, 0, 0, 0);
		// $width
		$dim[0] = abs($bbox[2] - $bbox[0]);
		if($bbox[0] < -1) {
			$dim[0] = abs($bbox[2]) + abs($bbox[0]) - 1;
		}
		// $height
		$dim[1] = abs($bbox[7]) - abs($bbox[1]);
		if($bbox[3] > 0) {
			$dim[1] = abs($bbox[7] - $bbox[1]) - 1;
		}
		// $x
		if($bbox[0] >= -1) {
			$dim[2] = abs($bbox[0] + 1) * -1;
		} 
		else {
			$dim[2] = abs($bbox[0] + 2);
		}
		// $y
		$dim[3] = abs($bbox[5] + 1);

		return $dim;
	}
}