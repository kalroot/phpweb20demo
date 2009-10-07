<?php

class UtilityController extends CustomControllerAction
{
	public function captchaAction()
	{
		$session = new Zend_Session_Namespace('captcha');
		
		$phrase = null;
		if (isset($session->phrase) && strlen($session->phrase) > 0)
			$phrase = $session->phrase;
		
		$captcha = Text_CAPTCHA::factory('Image');
		
		$opts = array('font_size' => 20,
					  'font_path' => Zend_Registry::get('config')->paths->data,
					  'font_file' => 'VeraBd.ttf');
		
		$captcha->init(120, 60, $phrase, $opts);
		
		$session->phrase = $captcha->getPhrase();
		
		$this->_helper->viewRenderer->setNoRender();
		
		header('Content-type: image/png');
		echo $captcha->getCAPTCHAAsPng();
	}

	public function imageAction()
	{
		$request = $this->getRequest();
		$response = $this->getResponse();

		$id = (int)$request->getQuery('id');
		$w	= (int)$request->getQuery('w');
		$h	= (int)$request->getQuery('h');
		$hash = $request->getQuery('hash');

		$realHash = DatabaseObject_BlogPostImage::GetImageHash($id, $w, $h);

		$this->_helper->viewRenderer->setNoRender();

		$image = new DatabaseObject_BlogPostImage($this->db);
		if ($hash != $realHash || !$image->load($id))
		{
			$response->setHttpResponseCode(404);
			return;
		}

		try
		{
			$fullpath = $image->createThumbnail($w, $h);
		}
		catch (Exception $ex)
		{
			$fullpath = $image->getFullPath();
		}
		
		$info = getImageSize($fullpath);

		$response->setHeader('content-type', $info['mime']);
		$response->setHeader('content-length', filesize($fullpath));
		echo file_get_contents($fullpath);
	}
}

?>