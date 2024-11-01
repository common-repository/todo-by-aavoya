<?php

namespace Wptba\Backend;

class AloAjax
{

	private static $globalScopeName = 'Wptba\Backend\AloAjax';
	public static function init()
	{
		add_action('wp_ajax_setAutoLogOutWptba', array(self::$globalScopeName, 'setAutoLogOut'));
		add_action('wp_ajax_getAutoLogOutWptba', array(self::$globalScopeName, 'getAutoLogOut'));
	}

	public static function setAutoLogOut()
	{
		if (!wp_verify_nonce($_POST['wptba_backend_nonce'], 'wptba_backend_nonce')) wp_die();
		$autoLogOut = (int)intval($_POST['autoLogOut']);
		echo json_encode(update_option('wptba_autoLogOutDuration', $autoLogOut));
		wp_die();
	}

	public static function getAutoLogOut()
	{
		if (!wp_verify_nonce($_POST['wptba_backend_nonce'], 'wptba_backend_nonce')) wp_die();
		$autoLogOutDuration = get_option('wptba_autoLogOutDuration', null);
		if ($autoLogOutDuration != null) {
			$autoLogOutDuration  = intval($autoLogOutDuration);
		}
		echo json_encode($autoLogOutDuration);
		wp_die();
	}
}
