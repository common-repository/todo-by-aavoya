<?php

/**
 * 
 * @author Pijush Gupta
 * @version 1
 * @package wp-todo-by-aavoya
 * This class is used to set wptba_encryption_key during the plugin initialization.
 * One time use only
 */

namespace Wptba\Init;

if (!defined('ABSPATH')) exit;

use Wptba\Common\Key as Random;

class Key
{

	public static function add($length = 30)
	{
		if (get_option('wptba_encryption_key', null) != null) return;
		$key = Random::generate($length);
		update_option('wptba_encryption_key', $key);
	}
}
