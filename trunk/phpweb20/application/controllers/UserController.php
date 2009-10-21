<?php

class UserController extends CustomControllerAction
{
	protected $user = null;
	
	public function preDispatch()
	{
		parent::preDispatch();
		
		$request = $this->getRequest();
		
		if (strtolower($request->getActionName()) == 'usernotfound')
			return;
		
		$username = trim($request->getUserParam('username'));
		if (strlen($username) == 0)
			$this->_redirect($this->getUrl('index', 'index'));
		
		$this->user = new DatabaseObject_User($this->db);
		
		if (!$this->user->loadByUsername($username))
		{
			$this->_forward('usernotfound');
			return;
		}
		
		$this->breadcrumbs->addStep(
			$this->user->username . "'s Blog",
			$this->getCustomUrl(
				array('username' => $this->user->username,
					  'action'   => 'index'),
				'user'
			)
		);
		
		$this->view->user = $this->user;
	}
	
	public function usernotfoundAction()
	{
		$username = trim($this->getRequest()->getUserParam('username'));
		
		$this->breadcrumbs->addStep('User Not Found');
		$this->view->requestedUsername = $username;
	}
	
	public function indexAction()
	{
		if (isset($this->user->profile->num_posts))
			$limit = max(1, (int)$this->user->profile->num_posts);
		else
			$limit = 10;
		
		$options = array(
			'user_id' => $this->user->getId(),
			'status'  => DatabaseObject_BlogPost::STATUS_LIVE,
			'limit'   => $limit,
			'order'   => 'p.ts_created desc'
		);
		
		$posts = DatabaseObject_BlogPost::GetPosts($this->db, $options);
		
		$this->view->posts = $posts;
	}
	
	public function viewAction()
	{
		$request = $this->getRequest();
		$url = trim($request->getUserParam('url'));
		
		if (strlen($url) == 0)
		{
			$this->_redirect($this->getCustomUrl(
				array('username' => $this->user->username,
					  'action'	 => 'index'),
				'user'
			));
		}
		
		$post = new DatabaseObject_BlogPost($this->db);
		$post->loadLivePost($this->user->getId(), $url);
		
		if (!$post->isSaved())
		{
			$this->_forward('postnotfound');
			return;
		}
		
		$archiveOptions = array(
			'username' => $this->user->username,
			'year'	   => date('Y', $post->ts_created),
			'month'	   => date('m', $post->ts_created)
		);
		
		$this->breadcrumbs->addStep(date('F Y', $post->ts_created),
									$this->getCustomUrl($archiveOptions, 'archive'));
		$this->breadcrumbs->addStep($post->profile->title);
		
		$this->view->post = $post;
	}
	
	public function postNotFoundAction()
	{
		$this->breadcrumbs->addStep('Post Not Found');
	}
	
	public function archiveAction()
	{
		$request = $this->getRequest();
		
		$m = (int)trim($request->getUserParam('month'));
		$y = (int)trim($request->getUserParam('year'));
		
		$m = max(1, min(12, $m));
		
		$from = mktime(0, 0, 0, $m, 1, $y);
		$to	  = mktime(0, 0, 0, $m + 1, 1, $y) - 1;
		
		$options = array(
			'user_id' => $this->user->getId(),
			'from'	  => date('Y-m-d H:i:s', $from),
			'to'	  => date('Y-m-d H:i:s', $to),
			'status'  => DatabaseObject_BlogPost::STATUS_LIVE,
			'order'	  => 'p.ts_created desc'
		);
		
		$posts = DatabaseObject_BlogPost::GetPosts($this->db, $options);
		$this->breadcrumbs->addStep(date('F Y', $from));
		
		$this->view->month = $from;
		$this->view->posts = $posts;
	}
	
	public function tagAction()
	{
		$request = $this->getRequest();
		
		$tag = trim($request->getUserParam('tag'));
		if (strlen($tag) == 0)
		{
			$this->_redirect($this->getCustomUrl(
				array('username' => $this->user->username,
					  'action'	 => 'index'),
				'user'));
		}
		
		$options = array(
			'user_id' => $this->user->getId(),
			'tag'	  => $tag,
			'status'  => DatabaseObject_BlogPost::STATUS_LIVE,
			'order'	  => 'p.ts_created desc'
		);
		$posts = DatabaseObject_BlogPost::GetPosts($this->db, $options);
		
		$this->breadcrumbs->addStep('Tag: ' . $tag);
		$this->view->tag = $tag;
		$this->view->posts = $posts;
	}
	
	public function feedAction()
	{
		$options = array(
			'user_id' => $this->user->getId(),
			'status'  => DatabaseObject_BlogPost::STATUS_LIVE,
			'limit'   => 10,
			'order'	  => 'p.ts_created desc'
		);
		
		$recentPosts = DatabaseObject_BlogPost::GetPosts($this->db, $options);
		
		$domain = 'http://' . $this->getRequest()->getServer('HTTP_HOST');
		$url = $this->getCustomUrl(
			array('username' => $this->user->username,
				  'action'   => 'index'),
			'user'
		);
		$feedData = array(
			'title' 	=> sprintf("%s's Blog", $this->user->username),
			'link'  	=> $domain . $url,
			'charset'	=> 'UTF-8',
			'entries'	=> array()
		);
		
		foreach ($recentPosts as $post)
		{
			$url = $this->getCustomUrl(
				array('username' => $this->user->username,
					  'url'		 => $post->url),
				'post'
			);
			
			$entry = array(
				'title'			=> $post->profile->title,
				'link'			=> $domain . $url,
				'description'	=> $post->getTeaser(200),
				'lastUpdate'	=> $post->ts_created,
				'category'		=> array()
			);
			
			foreach ($post->getTags() as $tag)
			{
				$entry['category'][] = array('term' => $tag);
			}
			
			$feedData['entries'][] = $entry;
		}
		
		$feed = Zend_Feed::importArray($feedData, 'atom');
		$this->_helper->viewRenderer->setNoRender();
		$feed->send();
	}
}

?>