<?php

namespace Wptba\Backend;

if (!defined('ABSPATH')) exit;

class AauAjax
{

	private static $globalScopeName = 'Wptba\Backend\AauAjax';

	public static function init()
	{
		add_action('wp_ajax_setAauWptba', array(self::$globalScopeName, 'setAauWptba'));
		add_action('wp_ajax_getAauWptba', array(self::$globalScopeName, 'getAauWptba'));
	}



	public static function getAauWptba()
	{
		if (!wp_verify_nonce($_POST['wptba_backend_nonce'], 'wptba_backend_nonce')) wp_die();
		$aau = get_option('wptba_aau', null);
		if ($aau != null) {
			$aau = unserialize($aau);
			$aau = rest_sanitize_boolean($aau['aau']);
		}
		echo json_encode($aau);
		wp_die();
	}

	public static function setAauWptba()
	{
		if (!wp_verify_nonce($_POST['wptba_backend_nonce'], 'wptba_backend_nonce')) wp_die();
		if (!isset($_POST['autoApproveUser'])) wp_die();

		$aau = rest_sanitize_boolean($_POST['autoApproveUser']);
		$aau = serialize(array('aau' => $aau));

		echo json_encode(update_option('wptba_aau', $aau));
		wp_die();
	}
}
