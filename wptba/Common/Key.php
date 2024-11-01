<?php

namespace Wptba\Common;

if (!defined('ABSPATH')) exit;
class Key
{

	public static function generate($length = 10)
	{
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}

	public static function set($KeyString = '')
	{
		if ($KeyString = '') return;
		return update_option('wptba_encryption_key', $KeyString);
	}

	public static function get()
	{
		return get_option('wptba_encryption_key', null);
	}
}
