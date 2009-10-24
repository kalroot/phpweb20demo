<?php

class DatabaseObject_BlogPostImage extends DatabaseObject
{
	protected $_uploadedFile;

	public function  __construct($db)
	{
		parent::__construct($db, 'blog_posts_images', 'image_id');

		$this->add('filename');
		$this->add('filetype');
		$this->add('post_id');
		$this->add('ranking');
	}

	public static function GetUploadPath()
	{
		$config = Zend_Registry::get('config');

		return $config->paths->upload;
	}

	public function getFullPath()
	{
		return sprintf('%s/%d.%s', self::GetUploadPath(), $this->getId(), $this->filetype);
	}

	public function uploadFile($path)
	{
		if (!file_exists($path) || !is_file($path))
			throw new Exception('Unable to find uploaded file');

		if (!is_readable($path))
			throw new Exception('Unable to read uploaded file');

		$this->_uploadedFile = $path;
	}

	public function preInsert()
	{
		$path = self::GetUploadPath();
		if (!file_exists($path) || !is_dir($path))
			throw new Exception('Upload path ' . $path . ' not found');

		if (!is_writable($path))
			throw new Excpetion('Unable to write to upload paht ' . $path);

		$query = sprintf('select coalesce(max(ranking), 0) + 1 from %s where post_id = %d', $this->_table, $this->post_id);

		$this->ranking = $this->_db->fetchOne($query);
		return true;
	}

	public function postInsert()
	{
		if (strlen($this->_uploadedFile) > 0)
		{
			if (!move_uploaded_file($this->_uploadedFile, $this->getFullPath()))
				return false;

			try
			{
				$this->createThumbnail(200, 65, 's');
				$this->createThumbnail(100, 0, 'm');
				$this->createThumbnail(150, 0, 'b');
				$this->createThumbnail(600, 0, 'h');

				return true;
			}
			catch (Exception $ex)
			{
				$logger = Zend_Registry::get('logger');
				$logger->crit($ex->getMessage());
				return false;
			}
		}

		return false;
	}

	public function preDelete()
	{
		unlink($this->getFullPath());

		$pattern = sprintf('%s/%d*', self::GetThumbnailPath(), $this->getId());
		foreach (glob($pattern) as $thumbnail)
		{
			unlink($thumbnail);
		}

		return true;
	}

	public static function GetThumbnailPath()
	{
		$config = Zend_Registry::get('config');

		return $config->paths->attachments;
	}

	public function createThumbnail($maxW, $maxH, $name)
	{
		$fullpath = $this->getFullPath();
		//$ts = (int)filemtime($fullpath);
		$info = getImageSize($fullpath);

		$w = $info[0];
		$h = $info[1];
		$ratio = $w / $h;

		$maxW = min($w, $maxW);
		if ($maxW == 0)
			$maxW = $w;
		$maxH = min($h, $maxH);
		if ($maxH == 0)
			$maxH = $h;

		$newW = $maxW;
		$newH = $newW / $ratio;
		
		if ($newH > $maxH)
		{
			$newH = $maxH;
			$newW = $newH * $ratio;
		}
		
		//if ($w == $newW && $h == $newH)
			//return $fullpath;
		
		switch ($info[2])
		{
			case IMAGETYPE_GIF:
				$infunc = 'ImageCreateFromGif';
				$outfunc = 'ImageGif';
				break;
			case IMAGETYPE_JPEG:
				$infunc = 'ImageCreateFromJpeg';
				$outfunc = 'ImageJpeg';
				break;
			case IMAGETYPE_PNG:
				$infunc = 'ImageCreateFromPng';
				$outfunc = 'ImagePng';
				break;
			default:
				throw new Exception('Image Id ' . $this->getId() . ': Invalid image type');
		}
		
		$filename = sprintf('%d%s.%s', $this->getId(), $name, $this->filetype);
		$path = self::GetThumbnailPath();
		if (!file_exists($path))
			mkdir($path, 0777);
		
		if (!is_writable($path))
			throw new Exception('Image Id ' . $this->getId() . ': Unable to write thumbnail dir');
		
		$thumbPath = sprintf('%s/%s', $path, $filename);
		if (!file_exists($thumbPath))
		{
			$im = @$infunc($fullpath);
			if (!$im)
				throw new Exception('Image Id ' . $this->getId() . ': Unable to read image file');
			
			$thumb = imagecreatetruecolor($newW, $newH);
			imagecopyresampled($thumb, $im, 0, 0, 0, 0, $newW, $newH, $w, $h);
			$outfunc($thumb, $thumbPath);
		}
		
		if (!file_exists($thumbPath))
			throw new Exception('Image Id ' . $this->getId() . ': Unknown error occurred creating thumbnail');
		if (!is_readable($thumbPath))
			throw new Exception('Image Id ' . $this->getId() . ': Unable to read thumbnail');
			
		return $thumbPath;
	}

	public static function GetImageHash($id, $w, $h)
	{
		$id = (int)$id;
		$w  = (int)$w;
		$h  = (int)$h;

		return md5(sprintf('%s,%s,%s', $id, $w, $h));
	}

	public static function GetImages($db, $options = array())
	{
		$defaults = array('post_id' => array());

		foreach ($defaults as $k => $v)
		{
			$options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;
		}

		$select = $db->select();
		$select->from(array('i' => 'blog_posts_images'), array('i.*'));
		if (count($options['post_id']) > 0)
			$select->where('i.post_id in (?)', $options['post_id']);

		$select->order('i.ranking');
		$data = $db->fetchAll($select);
		$images = parent::BuildMultiple($db, __CLASS__, $data);

		return $images;
	}

	public function loadForPost($post_id, $image_id)
	{
		$post_id = (int)$post_id;
		$image_id = (int)$image_id;

		if ($post_id <= 0 || $image_id <= 0)
			return false;

		$query = sprintf('select %s from %s where post_id = %d and image_id = %d',
				join(', ', $this->getSelectFields()),
				$this->_table,
				$post_id,
				$image_id
		);

		return $this->_load($query);
	}
}

?>
