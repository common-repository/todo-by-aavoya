<?php

namespace Wptba\Backend;

if (!defined('ABSPATH')) exit;

use Wptba\Common\Key;

class KeyAjax
{


	private static $globalScopeName = 'Wptba\Backend\KeyAjax';

	public static function init()
	{
		add_action('wp_ajax_setKeyWptba', array(self::$globalScopeName, 'setKeyWptba'));
		add_action('wp_ajax_getKetKeyWptba', array(self::$globalScopeName, 'getKetKeyWptba'));
	}

	/**
	 * setKeyWptba
	 * setting JWT encryption key
	 * @return void
	 */
	public static function setKeyWptba()
	{
		if (!wp_verify_nonce($_POST['wptba_backend_nonce'], 'wptba_backend_nonce')) wp_die();

		$key  = Key::generate();
		$key = sanitize_text_field($key);
		echo json_encode(Key::set($key));
		wp_die();
	}

	/**
	 * getKetKeyWptba
	 * sending back the key after update 
	 * @return void
	 */
	public static function getKetKeyWptba()
	{
		if (!wp_verify_nonce($_POST['wptba_backend_nonce'], 'wptba_backend_nonce')) wp_die();

		echo json_encode(Key::get());
		wp_die();
	}
}
