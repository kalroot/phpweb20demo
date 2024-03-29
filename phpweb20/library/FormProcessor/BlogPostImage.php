<?php

class FormProcessor_BlogPostImage extends FormProcessor
{
	protected $post;
	public $image;
	
	public function  __construct(DatabaseObject_BlogPost $post)
	{
		parent::__construct();

		$this->post = $post;
		$this->image = new DatabaseObject_BlogPostImage($post->getDb());
		$this->image->post_id = $this->post->getId();
	}

	public function process(Zend_Controller_Request_Abstract $request)
	{
		if (!isset($_FILES['image']) || !is_array($_FILES['image']))
		{
			$this->addError('image', 'Invalid upload data');
			return false;
		}

		$file = $_FILES['image'];

		switch ($file['error'])
		{
			case UPLOAD_ERR_OK:
				break;

			case UPLOAD_ERR_FORM_SIZE:
			case UPLOAD_ERR_INI_SIZE:
				$this->addError('image', 'The uploaded file was too large');
				break;

			case UPLOAD_ERR_PARTIAL:
				$this->addError('image', 'File was only partially uploaded');
				break;

			case UPLOAD_ERR_NO_FILE:
				$this->addError('image', 'No file was uploaded');
				break;

			case UPLOAD_ERR_NO_TMP_DIR:
				$this->addError('image', 'Temporary folder not found');
				break;

			case UPLOAD_ERR_CANT_WRITE:
				$this->addError('image', 'Unable to write file');
				break;

			case UPLOAD_ERR_EXTENSION:
				$this->addError('image', 'Invalid file extension');
				break;

			default:
				$this->addError('image', 'Unknown error code');
		}

		if ($this->hasError())
			return false;

		$info = getImageSize($file['tmp_name']);
		if (!$info)
		{
			$this->addError('type', 'Uploaded file was not an image');
			return false;
		}

		switch ($info[2])
		{
			case IMAGETYPE_PNG:
				$type = "png";
				break;
			case IMAGETYPE_GIF:
				$type = "gif";
				break;
			case IMAGETYPE_JPEG:
				$type = "jpg";
				break;

			default:
				$this->addError('type', 'Invalid image type uploaded');
				return false;
		}

		if (!$this->hasError())
		{
			$this->image->uploadFile($file['tmp_name']);
			$this->image->filetype = $type;
			$this->image->filename = basename($file['name']);
			$this->image->save();
		}

		return !$this->hasError();
	}
}

?>
