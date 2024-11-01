<?php

namespace Wptba\Common;

if (!defined('ABSPATH')) exit;

use Wptba\Common\Auth;

class Officer
{
	/**
	 * validateRequest
	 *
	 * @param  array $post
	 * @return int $user_id
	 */
	public static function validateRequest($post = null)
	{
		/**
		 * just a fail safe mechanism
		 * to check if the method called without argument or not
		 */
		if ($post == null) return null;

		/**
		 * Received JWT from Frontend
		 * IF not found then return false
		 */
		if (!$post['jwt']) return false;

		/**
		 * Sanitizing the received JWT
		 */
		$token = sanitize_text_field($post['jwt']);
		if (!$token) return false;

		/**
		 * Getting the saved key for JWT from Option Table
		 */
		$key = get_option('wptba_encryption_key', null);
		if ($key == null) return false;

		/**
		 * Sanitizing key for JWT from Option Table 
		 */
		$key = sanitize_text_field($key);

		/**
		 * crating Auth Object for received JWT 
		 * Passing the received key for JWT to Auth Object's constructor
		 * Auth Object Class is in wptba\Common\Auth.php
		 */
		$authObj = new Auth($key);

		/**
		 * Decoding the received JWT
		 * decode method will return an array of previously encoded data
		 */
		$decodedToken = $authObj->decode($token);


		/**
		 * Checking if decoded token is an array or not
		 * in case exception is thrown from Auth class 
		 * then returned data($decodedToken) will not be an array
		 */
		if (gettype($decodedToken) != 'array')  return false;

		$userID = $decodedToken['data']->ID;

		return intval($userID);
	}
}
