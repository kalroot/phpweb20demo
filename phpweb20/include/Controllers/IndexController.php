<?php

class IndexController extends CustomControllerAction
{
	public function indexAction()
	{
		$options = array(
			'status' => DatabaseObject_BlogPost::STATUS_LIVE,
			'limit'	 => 10,
			'order'	 => 'p.ts_created desc',
			'public_only' => true
		);
		
		$posts = DatabaseObject_BlogPost::GetPosts($this->db, $options);
		
		$user_ids = array();
		foreach ($posts as $post)
			$user_ids[$post->user_id] = $post->user_id;
		
		if (count($user_ids) > 0)
		{
			$options = array('user_id' => $user_ids);
			$users = DatabaseObject_User::GetUsers($this->db, $options);
		}
		else
			$users = array();
		
		$this->view->posts = $posts;
		$this->view->users = $users;
	}
}

?>