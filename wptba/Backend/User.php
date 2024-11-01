<?php

namespace Wptba\Backend;

class User
{

	private static $globalNamespace = 'Wptba\Backend\User';

	public static function activate()
	{
		add_action('wp_ajax_wptbaGetPendingUsers', array(self::$globalNamespace, 'getPendingUsers'));
		add_action('wp_ajax_wptbaPostToUser', array(self::$globalNamespace, 'postToUser'));
		add_action('wp_ajax_wptbaUserPostDelete', array(self::$globalNamespace, 'userPostDelete'));
	}

	/**
	 * getPendingUsers
	 * This to get registered user(actually posts of post type of 'wp_todo_user') waiting
	 * for approval
	 * @return void
	 */
	public static function getPendingUsers()
	{
		/**
		 * Verifying Nonce for Backend
		 */
		if (!wp_verify_nonce($_POST['wptba_backend_nonce'], 'wptba_backend_nonce')) wp_die();

		/**
		 * Getting user all account requests
		 */
		$pendingUsers = 	get_posts(array(
			'post_type' => 'wp_todo_user',
			'posts_per_page' => -1
		));

		/**
		 * Checking if there are account request(type:post) 
		 */
		if (empty($pendingUsers)) {
			echo json_encode('null');
			wp_die();
		}

		/**
		 * Simplifying account requests and preparing for client side rendering 
		 */
		$pendingUsers = array_map(function ($pendingUser) {
			$pendingUserMeta = get_post_meta($pendingUser->ID, 'wptba_user_post_meta', true);

			return array(
				'id' => $pendingUser->ID,
				'name' => sanitize_text_field($pendingUserMeta['name']),
				'email' => sanitize_email($pendingUserMeta['userEmail'])
			);
		}, $pendingUsers);

		/**
		 * send the data back 
		 */
		echo json_encode($pendingUsers);

		/**
		 * Terminating script
		 */
		wp_die();
	}


	/**
	 * postToUser
	 * This to convert a pending user(post:'') to a regular user(real user)
	 * @param string $postID
	 * @return string
	 */
	public static function postToUser()
	{
		/**
		 * Checking if the nonce is valid
		 */
		if (!wp_verify_nonce($_POST['wptba_backend_nonce'], 'wptba_backend_nonce')) wp_die();

		/**
		 * Checking if the post id is provided or not 
		 * We are converting post to user so we need to know the post id
		 */
		$userId = intval($_POST['postId']);
		if (!$userId) wp_die();

		/**
		 * Checking if the post actually exists
		 */
		$userAsPost = get_post($userId);
		if ($userAsPost == null) wp_die();

		/**
		 * getting post meta since we saved user registration details in post meta
		 */
		$postMeta = get_post_meta($userAsPost->ID, 'wptba_user_post_meta', true);
		if (empty($postMeta)) wp_die();

		/**
		 * Extracting post meta and assigning it to vatiable to use in creating user
		 */
		$userName 	= sanitize_text_field($postMeta['userName']);
		$userEmail 	= sanitize_email($postMeta['userEmail']);
		$name 			= sanitize_text_field($postMeta['name']);

		/**
		 * Checking if we have all the required data
		 */
		if (empty($userName) || empty($userEmail) || empty($name)) wp_die();

		/**
		 * Checking if the user already exists or not by its email
		 */
		$user = get_user_by('email', $userEmail);
		if ($user != false) wp_die();
		unset($user);

		/**
		 * Checking the user already exists or not by its login name(username)
		 */
		$user = get_user_by('login', $userName);
		if ($user != false) wp_die();
		unset($user);

		/**
		 * Generating a random password for the user
		 * and storing it in separate variable to use it in the confirmation 
		 * Email
		 */
		$password = wp_generate_password();

		/**
		 * Creating User and storing the user ID returned from wp_insert_user,
		 * in a variable. To use it to store user meta
		 */
		$userID = wp_insert_user(array(
			'user_login'	=> $userName,
			'user_email'	=> $userEmail,
			'user_pass'		=> $password,
			'first_name'	=> $name,
			'role'				=> 'todoer'
		));

		/**
		 * Checking if the user creation was successful or not
		 */
		if (gettype($userID) != 'integer') wp_die();

		/**
		 * Creating tag/term with the user ID
		 * Its needed for post sharing, since post sharing basically 
		 * tagging a post with tag(name=userid,slug=userid) having same slug and name as user id
		 */
		if (is_wp_error(wp_insert_term($userID, 'wp_todo_board_tag'))) return;

		/**
		 * Adding User Meta
		 * bio: for future use 
		 * darkmode: to store darkmode data
		 */
		add_user_meta($userID, 'wptba_user_meta', serialize(array(
			'bio' => '',
			'dark_mode' => true,
		)));

		/**
		 * Sending email to the user with the password
		 */
		self::sentActivationEmail($userEmail, $userName, $password);

		/**
		 * Deleting the pending user POST and its Meta
		 */
		wp_delete_post($userAsPost->ID, true);
		delete_user_meta($userAsPost->ID, 'wptba_user_post_meta');

		/**
		 * Sending AJAX response as success 
		 */
		echo json_encode('success');

		/**
		 * Stopping the execution of the script
		 */
		wp_die();
	}

	/**
	 * sentActivationEmail
	 * Sending Credentials to User as an Email
	 * @param  string $userEmail
	 * @param  string $userName
	 * @param  string $password
	 * @return void
	 */
	public static function sentActivationEmail($userEmail, $userName, $password)
	{
		wp_mail(
			$userEmail,
			'Login Credentials',
			'Your login credentials are:</br>
			<p>Email: ' . $userEmail . '</p></br>
			<p>Password: ' . $password . '</p>'
		);
	}

	/**
	 * userPostDelete
	 * This to delete user account request
	 * @return void
	 */
	public static function userPostDelete()
	{
		/**
		 * Checking if the nonce is valid
		 */
		if (!wp_verify_nonce($_POST['wptba_backend_nonce'], 'wptba_backend_nonce')) wp_die();

		/**
		 * Checking if the post id is provied or not 
		 * We are converting post to user so we need to know the post id
		 */
		$postId = intval($_POST['postId']);
		if (!$postId) {
			echo json_encode(0);
			wp_die();
		}


		/**
		 * If unable to delete, terminate
		 */
		if (wp_delete_post($postId, true) == false) {
			echo json_encode(0);
			wp_die();
		}

		/**
		 * delete post meta
		 */
		delete_post_meta($postId, 'wptba_user_post_meta');

		/**
		 * sending success code 
		 */
		echo json_encode(1);

		/**
		 * terminating script 
		 */
		wp_die();
	}
}
