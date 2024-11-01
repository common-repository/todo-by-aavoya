<?php

namespace Wptba\Init;

if (!defined('ABSPATH')) exit;

class Userrole
{

	public static function add()
	{
		add_role('todoer', 'Todoer', get_role('author')->capabilities);
	}
}
