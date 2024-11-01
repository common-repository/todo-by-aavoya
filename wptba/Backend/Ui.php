<?php

namespace Wptba\Backend;

if (!defined('ABSPATH')) exit;

class Ui
{

	private static $globalScopeName = 'Wptba\Backend\Ui';

	/**
	 * activate
	 * Init method
	 * @return void
	 */
	public static function activate()
	{
		if (current_user_can('manage_options')) {
			add_action('admin_menu', array(self::$globalScopeName, 'addMenu'));
			add_action('admin_enqueue_scripts', array(self::$globalScopeName, 'add_css_js'));
		}
	}

	/**
	 * addMenu
	 * This to add menu to the admin panel
	 * @return void
	 */
	public static function addMenu()
	{
		add_menu_page(
			__('WP Todo by Aavoya', 'wp-todo-by-aavoya'),
			__('WP Todo', 'wp-todo-by-aavoya'),
			'manage_options',
			'wptba-admin',
			array(self::$globalScopeName, 'render'),
			'dashicons-schedule',
		);
	}

	/**
	 * render
	 * This to render the html for the admin panel
	 * @return void
	 */
	public static function render()
	{
		$url 									= admin_url('admin-ajax.php');
		$wptba_backend_nonce	= wp_create_nonce('wptba_backend_nonce');
		$wptba_dist_path			= WPTBA_REL . '/wptba/Backend/client/dist/';
		$html_root 						= 'wptba-admin-container';

		printf(
			'<script> 
			var wptba_backend_nonce="%1s";
			var wptba_backend_url="%2s";
			var wptba_dist_path="%4s";
			</script>
			<div class="%4s"></div>',
			$wptba_backend_nonce,
			$url,
			$wptba_dist_path,
			$html_root
		);
	}

	public static function add_css_js($hook)
	{
		if ($hook != 'toplevel_page_wptba-admin') return;

		if (file_exists(WPTBA_ABS . 'wptba/Backend/client/dist/main.js')) {
			wp_enqueue_script('wptba-vue-script', WPTBA_REL . '/wptba/Backend/client/dist/main.js', array(), '1.0.0', true);
		}

		if (file_exists(WPTBA_ABS . 'wptba/Backend/client/dist/main.css')) {
			wp_enqueue_style('wptba-tailwind-style', WPTBA_REL . '/wptba/Backend/client/dist/main.css', array(), '1.0.0');
		}

		if (file_exists(WPTBA_ABS . 'wptba/Backend/client/dist/fonts.css')) {
			wp_enqueue_style('wptba-fonts-style', WPTBA_REL . '/wptba/Backend/client/dist/fonts.css', array(), '1.0.0');
		}
	}
}
