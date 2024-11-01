<?php

namespace Wptba\Backend;

use JsonException;
use JsonSerializable;

use function PHPSTORM_META\type;

if (!defined('ABSPATH')) exit;

class Posts
{
	private static $globalNamespace = 'Wptba\Backend\Posts';

	public static function activate()
	{
		add_action('wp_ajax_wptbaUploadImage', array(self::$globalNamespace, 'uploadImage'));
		add_action('wp_ajax_wptbaGetAttachment', array(self::$globalNamespace, 'getAttachment'));
		add_action('wp_ajax_wptbaGetAttachmentId', array(self::$globalNamespace, 'getAttachmentId'));
	}


	/**
	 * uploadImage
	 * This method upload logo Image
	 * @return void
	 */
	public static function uploadImage()
	{
		/**
		 * Checking if the nonce is valid
		 */
		if (!wp_verify_nonce($_POST['wptba_backend_nonce'], 'wptba_backend_nonce')) wp_die();


		/**
		 * Only allowing 'image' mime type
		 */
		if (self::checkIfImage(sanitize_text_field($_FILES['logo']['name'])) == false) {
			echo json_encode(0);
			wp_die();
		}

		/**
		 * Uploading the image
		 */
		$uploadStatus = wp_handle_upload($_FILES['logo'], array('test_form' => false), null);

		/**
		 * Checking if there is any error on upload
		 */
		if (array_key_exists('error', $uploadStatus)) {
			echo json_encode(1);
			wp_die();
		}

		/**
		 * attachment arguments
		 */
		$attachment = array(
			'post_mime_type' => $uploadStatus['type'],
			'post_title' => preg_replace('/\.[^.]+$/', '', basename($uploadStatus['file'])),
			'post_content' => '',
			'post_status' => 'inherit',
			'guid' => $uploadStatus['url']
		);

		/**
		 * Creating attachment for the image to show it in the media
		 */
		$attachmentId = wp_insert_attachment($attachment, $uploadStatus['url']);

		/**
		 * creating attachment meta
		 */
		wp_update_attachment_metadata($attachmentId, wp_generate_attachment_metadata($attachmentId, $uploadStatus['file']));

		/**
		 * Finally updating adding/updating option('wptba_logo') 
		 * with image attachment id
		 */
		update_option('wptba_logo', intval($attachmentId));

		/**
		 * returning attachment(thumb - 150x150 - ideal for logo area)
		 */
		echo json_encode(sanitize_url(wp_get_attachment_thumb_url($attachmentId)));

		/**
		 * terminating script
		 */
		wp_die();
	}

	/**
	 * checkIfImage
	 * @param  array $imgName
	 * @return boolean
	 */
	public static function checkIfImage($imgName)
	{
		$type = wp_check_filetype($imgName)['type'];
		if (str_contains($type, 'image')) return true;
		return false;
	}


	/**
	 * getAttchemnt
	 *
	 * @return void
	 */
	public static function getAttachment()
	{
		/**
		 * Checking if the nonce is valid
		 */
		if (!wp_verify_nonce($_POST['wptba_backend_nonce'], 'wptba_backend_nonce')) wp_die();

		if (!$_POST['attchmentId']) {
			echo json_encode(0);
			wp_die();
		}

		/**
		 * Sanitizing attachment ID
		 */
		$attachmentId =  intval($_POST['attchmentId']);

		/**
		 * Logo url
		 */
		$imageUrl = wp_get_attachment_thumb_url($attachmentId);

		/**
		 *  returning the url to client
		 */
		echo json_encode(sanitize_url($imageUrl));

		/**
		 * Terminating script
		 */
		wp_die();
	}


	/**
	 * getAttachmentId
	 * Sending logo attachment id stored in option table 
	 * option : 'wptba_logo'
	 * @return void
	 */
	public static function getAttachmentId()
	{
		/**
		 * Checking if the nonce is valid
		 */

		if (!wp_verify_nonce($_POST['wptba_backend_nonce'], 'wptba_backend_nonce')) wp_die();

		/**
		 * returning id to client
		 */
		echo json_encode(intval(get_option('wptba_logo')));

		/**
		 * terminating script
		 */
		wp_die();
	}
}
