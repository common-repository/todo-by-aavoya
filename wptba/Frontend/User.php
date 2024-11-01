<?php

namespace Wptba\Frontend;

if (!defined('ABSPATH')) exit;

use Wptba\Common\Auth;
use Wptba\Common\Officer;
use Wptba\Frontend\TemplateEmail;


class User
{
	private static $globalNamespace = 'Wptba\Frontend\User';

	public static function do()
	{
		add_action('wp_ajax_nopriv_wptbaLogin', array(self::$globalNamespace, 'login'));
		add_action('wp_ajax_wptbaLogin', array(self::$globalNamespace, 'login'));

		add_action('wp_ajax_nopriv_wptbaGetUserDetails', array(self::$globalNamespace, 'getUserDetails'));
		add_action('wp_ajax_wptbaGetUserDetails', array(self::$globalNamespace, 'getUserDetails'));

		add_action('wp_ajax_nopriv_wptbaUploadDarkMode', array(self::$globalNamespace, 'wptbaUploadDarkMode'));
		add_action('wp_ajax_wptbaUploadDarkMode', array(self::$globalNamespace, 'wptbaUploadDarkMode'));

		add_action('wp_ajax_nopriv_wptbaDownloadDarkMode', array(self::$globalNamespace, 'wptbaDownloadDarkMode'));
		add_action('wp_ajax_wptbaDownloadDarkMode', array(self::$globalNamespace, 'wptbaDownloadDarkMode'));

		add_action('wp_ajax_nopriv_wptbaCheckAvailableUsername', array(self::$globalNamespace, 'wptbaCheckAvailableUsername'));
		add_action('wp_ajax_wptbaCheckAvailableUsername', array(self::$globalNamespace, 'wptbaCheckAvailableUsername'));

		add_action('wp_ajax_nopriv_wptbaRegister', array(self::$globalNamespace, 'register'));
		add_action('wp_ajax_wptbaRegister', array(self::$globalNamespace, 'register'));

		add_action('admin_post_nopriv_wptba_verify_email', array(self::$globalNamespace, 'wptba_verify_email'));
		add_action('admin_post_wptba_verify_email', array(self::$globalNamespace, 'wptba_verify_email'));

		add_action('wp_ajax_nopriv_wptbaChangePassword', array(self::$globalNamespace, 'changePassword'));
		add_action('wp_ajax_wptbaChangePassword', array(self::$globalNamespace, 'changePassword'));

		add_action('wp_ajax_nopriv_wptbaResetPassword', array(self::$globalNamespace, 'resetPassword'));
		add_action('wp_ajax_wptbaResetPassword', array(self::$globalNamespace, 'resetPassword'));

		add_action('admin_post_nopriv_wptbaUpdatePassword', array(self::$globalNamespace, 'UpdatePassword'));
		add_action('admin_post_wptbaUpdatePassword', array(self::$globalNamespace, 'UpdatePassword'));

		add_action('wp_ajax_nopriv_wptbaGetAllUsers', array(self::$globalNamespace, 'getAllUsers'));
		add_action('wp_ajax_wptbaGetAllUsers', array(self::$globalNamespace, 'getAllUsers'));
	}

	/**
	 * login
	 * responsible user login
	 * @return json
	 */
	public static function login()
	{
		/**
		 * Verifying Nonce
		 */
		if (!wp_verify_nonce($_POST['wptba_nonce'], 'wptba_nonce')) wp_die();

		/**
		 * Sanitizing user entered email
		 */
		$todoer_email = sanitize_email($_POST['email']);
		if ($todoer_email == '') wp_die();

		/**
		 * Sanitizing the user entered password
		 */
		$todoer_password = sanitize_text_field($_POST['password']);
		if ($todoer_password == '') wp_die();

		/**
		 * If User Not Found Then send user404
		 */
		$user = get_user_by('email', $todoer_email);
		if (!$user) {
			/**
			 * do not send exact reason of failure
			 */
			echo json_encode(0);
			wp_die();
		}

		/**
		 * Do one thing and one thing properly.
		 * User/author can't have multiple roles.
		 */
		if (count($user->roles) > 1) wp_die();
		if (!in_array('todoer', $user->roles)) wp_die();

		/**
		 * If User Found Then Check Password
		 */
		if (wp_check_password($todoer_password, $user->data->user_pass, $user->ID) === false) {
			/**
			 * do not send exact reason of failure.
			 */
			echo json_encode(0);

			wp_die();
		}

		/**
		 * If Password is Correct Then Create JWT Token
		 */

		if (get_option('wptba_encryption_key', null) != null) {
			$key = sanitize_text_field(get_option('wptba_encryption_key'));
		} else {
			wp_die();
		}

		if (get_option('wptba_autoLogOutDuration', null) != null) {
			$alo = (int)intval(get_option('wptba_autoLogOutDuration'));
		} else {
			wp_die();
		}



		$authObject = new Auth($key, $alo);
		$authObject->setData(array('ID' => $user->ID));
		$token = $authObject->encode();

		echo json_encode($token);
		//echo json_encode($authObject->decode($token, $key));
		wp_die();



		echo json_encode($user);
		wp_die();
	}

	/**
	 * getUserDetails
	 * Provides user Details
	 * @return void
	 */
	public static function getUserDetails()
	{
		/**
		 * Verifying Nonce
		 */
		if (!wp_verify_nonce($_POST['wptba_nonce'], 'wptba_nonce')) wp_die();


		$userID = Officer::validateRequest($_POST);
		if (gettype($userID) != 'integer') {
			echo json_encode(0);
			wp_die();
		}

		$user = get_user_by('ID', $userID);

		$user_meta = get_user_meta($userID, 'wptba_user_meta', true);
		if (!$user_meta) {
			/**
			 * incase user meta is not set
			 * Then manually sending nulled data
			 */
			$user_meta = array();
			$user_meta['bio'] = '';
			$user_meta['dark_mode'] = false;
		} else {
			/**
			 * incase user meta is set
			 * Then sanitizing the each key of user meta array
			 */
			$user_meta = unserialize($user_meta);
			array_key_exists('bio', $user_meta) ? $user_meta['bio'] =  sanitize_text_field($user_meta['bio']) : $user_meta['bio'] = '';
			array_key_exists('dark_mode', $user_meta) ? $user_meta['dark_mode'] = rest_sanitize_boolean($user_meta['dark_mode'])  : $user_meta['dark_mode'] = false;
		}



		$user_details = array();
		array_push(
			$user_details,
			array(
				'displayName' => $user->data->display_name,
				'niceName' => $user->data->user_nicename,
				'bio' => $user_meta['bio'],
				'darkMode' => $user_meta['dark_mode'],
			)
		);

		echo json_encode($user_details);
		wp_die();
	}

	/**
	 * wptbaUploadDarkMode
	 * Uploads Dark Mode data
	 * @return void
	 */
	public static function wptbaUploadDarkMode()
	{
		/**
		 * Verifying Nonce
		 */
		if (!wp_verify_nonce($_POST['wptba_nonce'], 'wptba_nonce')) wp_die();

		$userID = Officer::validateRequest($_POST);
		if (gettype($userID) != 'integer') {
			echo json_encode(0);
			wp_die();
		}

		$user_meta = get_user_meta($userID, 'wptba_user_meta', true);
		if (!$user_meta) {
			$user_meta = array();
			$user_meta['dark_mode'] = true;
			$update_status = update_user_meta($userID, 'wptba_user_meta', serialize($user_meta));

			echo json_encode('Created');
			wp_die();
		}

		$user_meta = unserialize($user_meta);
		if (array_key_exists('dark_mode', $user_meta)) {
			if (rest_sanitize_boolean($user_meta['dark_mode'])  === true) {
				$user_meta['dark_mode'] = rest_sanitize_boolean(false);
			} else {
				$user_meta['dark_mode'] = rest_sanitize_boolean(true);
			}
		} else {
			$user_meta['dark_mode'] = rest_sanitize_boolean(true);
		}

		$update_status = update_user_meta($userID, 'wptba_user_meta', serialize($user_meta));
		echo json_encode('Updated');
		wp_die();
	}

	/**
	 * wptbaDownloadDarkMode
	 * Sent Dark Mode data
	 * @return string
	 */
	public static function wptbaDownloadDarkMode()
	{
		/**
		 * Verifying Nonce
		 */
		if (!wp_verify_nonce($_POST['wptba_nonce'], 'wptba_nonce')) wp_die();

		$userID = Officer::validateRequest($_POST);
		if (gettype($userID) != 'integer') {
			echo json_encode(0);
			wp_die();
		}

		$user_meta = get_user_meta($userID, 'wptba_user_meta', true);

		if (!$user_meta) {

			echo json_encode(false);
			wp_die();
		} else {

			$user_meta = unserialize($user_meta);
			if (array_key_exists('dark_mode', $user_meta)) {
				$user_meta['dark_mode'] = rest_sanitize_boolean($user_meta['dark_mode']);
			} else {
				$user_meta['dark_mode'] = false;
			}


			echo json_encode($user_meta['dark_mode']);
			wp_die();
		}
	}

	public static function CheckAvailableUsername($userName)
	{
		$userLogins = get_users(array('fields' => 'user_login'));
		foreach ($userLogins as $userLogin) {
			if ($userLogin == $userName) {
				return false;
			}
		}
		return true;
	}

	public static function wptbaCheckAvailableUsername()
	{

		if (!wp_verify_nonce($_POST['wptba_nonce'], 'wptba_nonce')) wp_die();

		if (!sanitize_text_field($_POST['username'])) wp_die();

		if (self::CheckAvailableUsername(sanitize_text_field($_POST['username']))) {
			echo json_encode(true);
		} else {
			echo json_encode(false);
		}
		wp_die();
	}

	public static function register()
	{
		/**
		 * Verifying Nonce
		 */
		if (!wp_verify_nonce($_POST['wptba_nonce'], 'wptba_nonce')) wp_die();

		$name 			= sanitize_text_field($_POST['name']);
		$userName 	= sanitize_text_field($_POST['username']);
		$userEmail 	= sanitize_email($_POST['email']);

		if (!$name || !$userName || !$userEmail) wp_die();

		/** 
		 * return type
		 * 0 : Username is not available
		 * 1 : email is not available
		 * 2 : everything is ok and email verification initiated
		 */
		if (self::CheckAvailableUsername($userName) === false) {
			echo json_encode(0);
			wp_die();
		}

		if (get_user_by('email', $userEmail) != false) {
			echo json_encode(1);
			wp_die();
		}

		if (get_option('wptba_encryption_key', null) != null) {
			$key = sanitize_text_field(get_option('wptba_encryption_key'));
		} else {
			wp_die();
		}

		$authObject = new Auth($key, 1);
		$authObject->setData(array(
			'name' => $name,
			'userName' => $userName,
			'userEmail' => $userEmail
		));
		$tokenisedLinkData  = $authObject->encode();

		$link = admin_url('admin-post.php') . '?' . 'action=wptba_verify_email&token=' . $tokenisedLinkData;
		wp_mail(
			$userEmail,
			'Verify your email',
			'<div><p style="display:block">Please click on the link/button to verify your email address. </p><br>
			<a href="' . $link . '" style="background-color:#3b82f6; padding:10px 20px; color:white; text-transform:capitalize; border-radius: 4px; text-decoration:none display:inline-block;">Verify your email</a>
			<p>Please Ignore this mail if you have not registered with us.</p>
			</div>',
			array('Content-Type: text/html; charset=UTF-8')
		);
		echo json_encode(2);
		wp_die();
	}

	public static function wptba_verify_email()
	{

		if (get_option('wptba_encryption_key', null) != null) {
			$key = sanitize_text_field(get_option('wptba_encryption_key'));
		} else {
			wp_die();
		}
		$authObject = new Auth();
		$registrationData = $authObject->decode($_REQUEST['token'], $key);
		if ($registrationData == false) {
			TemplateEmail::linkExpired();
			die;
		}
		$registrationData = $registrationData['data'];

		$name 			= sanitize_text_field($registrationData->name);
		$userName 	= sanitize_text_field($registrationData->userName);
		$userEmail 	= sanitize_email($registrationData->userEmail);

		if (get_option('wptba_aau', null) == null) return;

		$aau = unserialize(get_option('wptba_aau'));
		$aau = rest_sanitize_boolean($aau['aau']);

		/**
		 * Save user Detail as Post
		 */
		if ($aau == true) {
			self::createUserAsPost($name, $userName, $userEmail);
			TemplateEmail::pendingApproval();
			die;
		}
		/**
		 * Save user Detail as User
		 */
		if ($aau == false) {
			$status = self::createUser($name, $userName, $userEmail);
			if ($status === false) {
				TemplateEmail::linkExpired();
				die;
			}

			TemplateEmail::approval();
			wp_mail(
				$userEmail,
				'Login Credentials',
				'Your login credentials are:</br>
				<p>Email: ' . $userEmail . '</p>
				<p>Password: ' . $status . '</p>',
				array('Content-Type: text/html; charset=UTF-8')
			);
			die;
		}
	}

	public static function createUserAsPost($name, $userName, $userEmail)
	{
		$userAsPost = get_posts(array(
			'post_type' => 'wp_todo_user',
			'post_status' => 'publish'
		));
		if (!empty($userAsPost)) {
			foreach ($userAsPost as $user) {
				if ($user->post_title == $userEmail) {
					return;
				}
			}
		}
		$post = array(
			'post_title' => $userEmail,
			'post_type' => 'wp_todo_user',
			'post_status' => 'publish',
			'post_author' => 1
		);
		$postID = wp_insert_post($post);
		if ($postID) {
			update_post_meta($postID, 'wptba_user_post_meta', array(
				'name' => $name,
				'userName' => $userName,
				'userEmail' => $userEmail,

			));
		}
	}

	public static function createUser($name, $userName, $userEmail)
	{

		if (self::CheckAvailableUsername($userName) == false) return false;

		if (get_user_by('email', $userEmail) != false) return false;

		$userPassword = wp_generate_password();

		/**
		 * Creating User and storing the user ID retured from wp_insert_user,
		 * in a variable. To use it to store user meta
		 */
		$userID = wp_insert_user(array(
			'user_login' 	=> $userName,
			'user_pass' 	=> $userPassword,
			'user_email' 	=> $userEmail,
			'first_name' 	=> $name,
			'role' 				=> 'todoer'
		));

		/**
		 * Checking if the user creation was successful or not
		 */
		if (gettype($userID) != 'integer') return false;

		/**
		 * Creating tag with the user ID
		 */
		if (is_wp_error(wp_insert_term($userID, 'wp_todo_board_tag'))) return;

		/**
		 * Adding User Meta
		 */
		add_user_meta($userID, 'wptba_user_meta', serialize(array(
			'bio' => '',
			'dark_mode' => true,
		)));

		return $userPassword;
	}

	public static function changePassword()
	{
		/**
		 * Verfiy nonce
		 */
		if (!wp_verify_nonce($_POST['wptba_nonce'], 'wptba_nonce')) {
			echo json_encode(0);
			wp_die();
		}

		/**
		 * Verfiy JWT and getting user ID from JWT
		 */
		$userID = Officer::validateRequest($_POST);
		if (gettype($userID) != 'integer') {
			/**
			 * Security Situation - Imidiatly log out the user
			 * return/echo 0 will do that in client side
			 */
			echo json_encode(0);
			wp_die();
		}

		/**
		 * Checking if old password is provided or not 
		 */
		if (!$_POST['old_password']) wp_die();
		$oldPass = sanitize_text_field($_POST['old_password']);

		/**
		 * Checking if new password is provided or not 
		 */
		if (!$_POST['new_password']) wp_die();
		$newPass = sanitize_text_field($_POST['new_password']);

		/**
		 * getting user password hash from database
		 */
		$user = get_user_by('id', $userID);
		if ($user == false) {
			echo json_encode(0);
			wp_die();
		}

		$passWordHash = $user->data->user_pass;

		/**
		 * Checking if old password is correct or not
		 */
		if (wp_check_password($oldPass, $passWordHash, $userID) == false) {
			echo json_encode('failed');
			wp_die();
		}

		/**
		 * Setting New password
		 */
		wp_set_password($newPass, $userID);

		echo json_encode('success');

		wp_die();
	}

	public static function resetPassword()
	{
		/**
		 * Verfiy nonce
		 */
		if (!wp_verify_nonce($_POST['wptba_nonce'], 'wptba_nonce')) {
			echo json_encode(0);
			wp_die();
		}


		$email = sanitize_email($_POST['email']);
		if (!$email) {
			echo json_encode(0);
			wp_die();
		}

		/**
		 * checking if the user with provided email id exists or not
		 */
		$user = get_user_by('email', $email);
		if ($user == false) {
			echo json_encode(0);
			wp_die();
		}

		/**
		 * Checking if the user is todoer or not
		 */
		if (!in_array('todoer', $user->roles)) {
			echo json_encode(0);
			wp_die();
		}


		/**
		 * checking if the user is associted with any other role(s) apart from 'todoer'
		 * This required to avoid any security issue in case 'todoer' user added to any other admistrative group accidently.
		 */
		if (count($user->roles) > 1) wp_die();


		/**
		 * Getting Key for JWT
		 */
		if (get_option('wptba_encryption_key', null) != null) {
			$key = sanitize_text_field(get_option('wptba_encryption_key'));
		} else {
			echo json_encode(0);
			wp_die();
		}

		/**
		 * Creating JSON Web Token with user data
		 */
		$authObject = new Auth($key, 1);
		$authObject->setData(array(
			'password' => wp_generate_password(),
			'user_id' => intval($user->ID)
		));
		$token = $authObject->encode();

		$link = admin_url('admin-post.php') . '?' . 'action=wptbaUpdatePassword&token=' . $token;
		wp_mail(
			$email,
			'Password Reset Request',
			'<div><p style="display:block">Please click on the link/button to reset the password. </p>
			<a href="' . $link . '" style="background-color:#3b82f6; padding:10px 20px; color:white; text-transform:capitalize; border-radius: 4px; text-decoration:none display:inline-block;">Verify your email</a>
			<p>Please Ignore this mail if its not you, who initiated this process.</p>
			</div>',
			array('Content-Type: text/html; charset=UTF-8')
		);

		echo json_encode(1);
		wp_die();
	}

	/**
	 * wptbaUpdatePassword
	 * This to handle the password reset request from email
	 * @return void
	 */
	public static function UpdatePassword()
	{

		if (get_option('wptba_encryption_key', null) != null) {
			$key = sanitize_text_field(get_option('wptba_encryption_key'));
		} else {
			wp_die();
		}

		$authObject = new Auth();
		$userInfo = $authObject->decode($_REQUEST['token'], $key);
		if ($userInfo == false) {
			TemplateEmail::linkExpired();
			die;
		}
		$password = sanitize_text_field($userInfo['data']->password);
		$userId 	= intval($userInfo['data']->user_id);

		wp_set_password($password, $userId);

		TemplateEmail::passReset($password);
	}

	/**
	 * getAllUsers
	 * Returns all users, except the logged in user doing this ajax call
	 * @return void
	 */
	public static function getAllUsers()
	{
		/**
		 * Verifying Nonce
		 */
		if (!wp_verify_nonce($_POST['wptba_nonce'], 'wptba_nonce')) {
			wp_die();
		}

		/**
		 * Validating the user 
		 */
		$userID = Officer::validateRequest($_POST);
		if (gettype($userID) != 'integer') {
			echo json_encode(0);
			wp_die();
		}
		$userID = intval($userID);

		/**
		 * Getting all users except the logged(client side) in user
		 * and if logged in user not the original author of the post then also excluding the original author
		 */
		$original_author = get_post_field('post_author', intval($_POST['post_Id']));
		if ($userID != $original_author) {
			$exclude = array($userID, $original_author);
		} else {
			$exclude = array($userID);
		}

		$users = get_users(array(
			'role__in' => array('todoer'),
			'exclude' => $exclude
		));

		/**
		 * Verifying if user exists  
		 */
		if (empty($users)) {
			echo json_encode(1);
			wp_die();
		}

		/**
		 * rebuilding the array to make it more simple
		 * to handle on client side 
		 */
		$users = array_map(function ($user) {

			return array(
				'id' => intval($user->ID),
				'name' => sanitize_text_field($user->display_name),
			);
		}, $users);


		/**
		 * Returning the users
		 */
		echo json_encode($users);

		/**
		 * terminating the script
		 */
		wp_die();
	}
}
