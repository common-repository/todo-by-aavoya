<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Plugin Name: Todo by Aavoya
 * Plugin URI: https://www.aavoya.co/wp-todo
 * Description: Yet another todo plugin with project management features
 * Version: 22.7
 * Requires PHP: 7.4.1
 * Author: Pijush Gupta (aavoya.co)
 * Author URI: https://www.linkedin.com/in/pijush-gupta-php/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: todo-by-aavoya
 */

if (file_exists(__DIR__) . '/vendor/autoload.php') {
	require_once __DIR__ . '/vendor/autoload.php';
}

define('WPTBA_ABS', plugin_dir_path(__FILE__));
define('WPTBA_REL', plugins_url('', __FILE__));

use Wptba\Init\Cpt as Board;
use Wptba\Init\Userrole;
use Wptba\Init\Key;
use Wptba\Init\Alo;
use Wptba\Init\Aau;

use Wptba\Backend\KeyAjax;
use Wptba\Backend\AloAjax;
use Wptba\Backend\AauAjax;

use Wptba\Backend\Ui;
use Wptba\Frontend\User;
use Wptba\Frontend\Posts;
use Wptba\Frontend\Shortcode;


add_action('plugins_loaded', function () {
	//Init Processes
	Board::create();
	Userrole::add();
	Key::add();
	Alo::set();
	Aau::init();

	//Backend 
	KeyAjax::init();
	AloAjax::init();
	AauAjax::init();
	Wptba\Backend\User::activate();
	Wptba\Backend\Posts::activate();
	Ui::activate();

	//Frontend 
	User::do();
	Posts::do();
	Shortcode::activate();
});
