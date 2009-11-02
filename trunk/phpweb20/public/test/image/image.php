<?php

class Image
{
	private $_imagefile = '';
	private $_imageinfo = '';
	private $_imagecreatefromfunc = '';
	private $_imagefunc = '';
	private $_animatedgif = 0;

	public function  __construct($imagefile)
	{
		$this->_imagefile = $imagefile;
		$this->_imageinfo = @getimagesize($imagefile);
		switch ($this->_imageinfo[2])
		{
			case 1:
				$this->_imagecreatefromfunc = 'imagecreatefromgif';
				$this->_imagefunc = 'imagegif';
				break;
			case 2:
				$this->_imagecreatefromfunc = 'imagecreatefromjpeg';
				$this->_imagefunc = 'imagejpeg';
				break;
			case 3:
				$this->_imagecreatefromfunc = 'imagecreatefrompng';
				$this->_imagefunc = 'imagepng';
				break;
			default:
				$this->_imagecreatefromfunc = $this->_imagefunc = '';
				break;
		}
		if ($this->_imageinfo[2] == 1)
		{
			$fp = fopen($imagefile, 'rb');
			$imagefilecontent = fread($fp, @filesize($imagefile));
			fclose($fp);
			$this->_animatedgif = strpos($imagefilecontent, 'NETSCAPE2.0') === FALSE ? 0 : 1;
		}
	}

	public function watermark()
	{
		$imagecreatefromfunc = $this->_imagecreatefromfunc;
		$imagefunc = $this->_imagefunc;
		list($img_w, $img_h) = $this->_imageinfo;
		$watermark_file = 'D:/www/phpweb20/public/watermark.png';
		$watermarkinfo	= @getimagesize($watermark_file);
		$watermark_logo = @imagecreatefrompng($watermark_file);
		if (!$watermark_logo)
			return;
		list($logo_w, $logo_h) = $watermarkinfo;

		$x = $img_w - $logo_w - 5;
		$y = $img_h - $logo_h - 5;

		$dst_photo = imagecreatetruecolor($img_w, $img_h);
		$image_photo = @$imagecreatefromfunc($this->_imagefile);
		imagecopy($dst_photo, $image_photo, 0, 0, 0, 0, $img_w, $img_h);
		imageCopy($dst_photo, $watermark_logo, $x, $y, 0, 0, $logo_w, $logo_h);
		clearstatcache();

		if ($this->_imageinfo[2] == 2)
			$imagefunc($dst_photo, $this->_imagefile, 80);
		else
			$imagefunc($dst_photo, $this->_imagefile);
	}

	public function thumb($thumbwidth, $thumbheight)
	{
		list($img_w, $img_h) = $this->_imageinfo;
		if(!$this->_animatedgif && ($img_w >= $thumbwidth || $img_h >= $thumbheight))
		{
			$imagecreatefromfunc = $this->_imagecreatefromfunc;
			$imagefunc = $this->_imagefunc;
			list($img_w, $img_h) = $this->_imageinfo;

			$attach_photo = $imagecreatefromfunc($this->_imagefile);
			$x_ratio = $thumbwidth / $img_w;
			$y_ratio = $thumbheight / $img_h;

			if(($x_ratio * $img_h) < $thumbheight)
			{
				$thumb['height'] = ceil($x_ratio * $img_h);
				$thumb['width'] = $thumbwidth;
			}
			else
			{
				$thumb['width'] = ceil($y_ratio * $img_w);
				$thumb['height'] = $thumbheight;
			}

			$targetfile = $this->_imagefile . '.thumb.jpg';
			$cx = $img_w;
			$cy = $img_h;

			$thumb_photo = imagecreatetruecolor($thumb['width'], $thumb['height']);
			imageCopyreSampled($thumb_photo, $attach_photo ,0, 0, 0, 0, $thumb['width'], $thumb['height'], $cx, $cy);
			clearstatcache();
			if ($this->_imageinfo[2] == 2)
				$imagefunc($thumb_photo, $targetfile, 80);
			else
				$imagefunc($thumb_photo, $targetfile);
		}
	}
}

?>