<?php

class IndexController extends CustomControllerAction
{
	public function indexAction()
	{
		$smarty = $this->view->getEngine();
		$template = $this->_helper->viewRenderer->getViewScript();
		$smarty->caching = true;
		$smarty->cache_lifetime = 1800;
		
		if (!$smarty->is_cached($template))
		{
			$options = array(
				'status' => Model_DatabaseObject_BlogPost::STATUS_LIVE,
				'limit'	 => 10,
				'order'	 => 'p.ts_created desc',
				'public_only' => true
			);

			$posts = Model_DatabaseObject_BlogPost::GetPosts($this->db, $options);

			$user_ids = array();
			foreach ($posts as $post)
				$user_ids[$post->user_id] = $post->user_id;

			if (count($user_ids) > 0)
			{
				$options = array('user_id' => $user_ids);
				$users = Model_DatabaseObject_User::GetUsers($this->db, $options);
			}
			else
				$users = array();

			$this->view->posts = $posts;
			$this->view->users = $users;
		}
	}
}

?>