<?php

class IndexController extends CustomControllerAction
{
	public function indexAction()
	{
		$config = Zend_Registry::get('config');
		
		$frontendOptions = array('caching' => true, 'lifeTime' => 1800, 'cached_entity' => 'DatabaseObject_BlogPost');
		$backendOptions = array('cache_dir' => sprintf('%s/tmp/cache_dir', $config->paths->data));
		$cache = Zend_Cache::factory('Class', 'File', $frontendOptions, $backendOptions);	
		
		$options = array(
			'status' => DatabaseObject_BlogPost::STATUS_LIVE,
			'limit'	 => 10,
			'order'	 => 'p.ts_created desc',
			'public_only' => true
		);

		$posts = $cache->GetPosts($this->db, $options);

		$user_ids = array();
		foreach ($posts as $post)
			$user_ids[$post->user_id] = $post->user_id;

		if (count($user_ids) > 0)
		{
			$frontendOptions['cached_entity'] = 'DatabaseObject_User';
			$cache = Zend_Cache::factory('Class', 'File', $frontendOptions, $backendOptions);
			$options = array('user_id' => $user_ids);
			$users = $cache->GetUsers($this->db, $options);
		}
		else
			$users = array();

		$this->view->posts = $posts;
		$this->view->users = $users;
	}
}

?>