<?php

class Model_DatabaseObject_BlogPostLocation extends Model_DatabaseObject
{
	public function  __construct($db)
	{
		parent::__construct($db, 'blog_posts_locations', 'location_id');

		$this->add('post_id');
		$this->add('longitude');
		$this->add('latitude');
		$this->add('description');
	}

	public function loadForPost($post_id, $location_id)
	{
		$post_id		= (int)$post_id;
		$location_id	= (int)$location_id;

		if ($post_id <= 0 || $location_id <= 0)
			return false;

		$query = sprintf(
			'select %s from %s where post_id = %d and location_id = %d',
			join(', ', $this->getSelectFields()),
			$this->_table,
			$post_id,
			$location_id
		);

		return $this->_load($query);
	}

	public static function GetLocations($db, $options = array())
	{
		$defaults = array('post_id' => array());

		foreach ($defaults as $k => $v)
			$options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;

		$select = $db->select();
		$select->from(array('l' => 'blog_posts_locations'), 'l.*');

		if (count($options['post_id']) > 0)
			$select->where('l.post_id in (?)', $options['post_id']);

		$data = $db->fetchAll($select);

		$locations = parent::BuildMultiple($db, __CLASS__, $data);
		return $locations;
	}

	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'latitude':
			case 'longitude':
				$value = sprintf('%01.6lf', $value);
				break;
		}

		return parent::__set($name, $value);
	}
}

?>
