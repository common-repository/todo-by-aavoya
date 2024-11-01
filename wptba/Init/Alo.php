<?php

/**
 * Auto log out users after a specified time
 * 
 */

namespace Wptba\Init;

if (!defined('ABSPATH')) exit;

class Alo
{
	public static function set($autoLogOut = 1)
	{
		if (get_option('wptba_autoLogOutDuration', null) != null) return;
		update_option('wptba_autoLogOutDuration', (int)intval($autoLogOut));
	}
}
