<?php

namespace Wptba\Frontend;

if (!defined('ABSPATH')) exit;

use Wptba\Frontend\Enqueue;

class Shortcode
{
	private static $globallyScoopedClassName = 'Wptba\Frontend\Shortcode';

	public static function activate()
	{

		add_filter('template_include', array(self::$globallyScoopedClassName, 'init'));
	}

	public static function init($template)
	{
		global $post;

		if (!is_admin() && str_contains($post->post_content, '[wptba]')) {
			/**
			 * Do not remove it from this method, getting triggered from 'template_include'
			 * removing it and placing somewhere else will cause wordpres to break
			 */
			Enqueue::do();
			add_filter('show_admin_bar', '__return_false');
			$template = dirname(__FILE__) . '/Template.php';

			return $template;
		} else {
			return $template;
		}
	}
}
