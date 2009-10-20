<?php

class FormProcessor_BlogPostLocation extends FormProcessor
{
	protected $post;
	public $location;

	public function  __construct(Model_DatabaseObject_BlogPost $post)
	{
		parent::__construct();

		$this->post = $post;
		$this->location = new Model_DatabaseObject_BlogPostLocation($post->getDb());
		$this->location->post_id = $this->post->getId();
	}

	public function process(Zend_Controller_Request_Abstract $request)
	{
		$this->description = $this->sanitize($request->getPost('description'));
		$this->longitude = $request->getPost('longitude');
		$this->latitude = $request->getPost('latitude');

		if (!$this->hasError())
		{
			$this->location->description = $this->description;
			$this->location->longitude	 = $this->longitude;
			$this->location->latitude	 = $this->latitude;
			$this->location->save();
		}

		return !$this->hasError();
	}
}

?>
