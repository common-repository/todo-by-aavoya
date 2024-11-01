<?php

namespace Wptba\Frontend;

class TemplateEmail
{
	public static function linkExpired()
	{
		echo self::boilerplate('Link Expired', 'Your link has expired. Please try again.');
	}
	public static function approval()
	{
		$title = 'Account Activated';
		$body = 'Your account has been activated. You can now login to your account, with the provided password sent via Email.';

		echo self::boilerplate($title, $body);
	}
	public static function pendingApproval()
	{
		$title = 'Email Verified Successfully';
		$body = 'Your Email address has been verified. Please wait for Admin to approve your account.';
		echo self::boilerplate($title, $body);
	}

	public static function passReset($password)
	{
		$title = 'Password Reset';
		$body = 'Your password has been reset. Please login with your new password.</br>';
		$body .= 'New Password: <code class="border px-2 py-1 rounded">' . $password . '</code>';
		$body .= '';
		echo self::boilerplate($title, $body);
	}

	private static function boilerplate($title, $body)
	{
		$html = '<!DOCTYPE html>';
		$html .= '<html lang="en">';
		$html .= '<head>';
		$html .= '<meta charset="UTF-8">';
		$html .= '<meta http-equiv="X-UA-Compatible" content="IE=edge">';
		$html .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
		$html .= '<title>' . $title . '</title>';
		$html .= '<style>' . sanitize_text_field(self::tailwindcssinline()) . '</style>';
		$html .= '<link rel="preconnect" href="https://fonts.googleapis.com">';
		$html .= '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
		$html .= '<link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro&display=swap" rel="stylesheet">';
		$html .= '<style>';
		$html .= "body{font-family: 'Source Sans Pro', sans-serif;}";
		$html .= '</style>';
		$html .= '</head>';
		$html .= '<body class="bg-gray-200  w-full flex justify-center mt-20">';
		$html .= '<div class="  rounded-lg bg-gray-50 bg-gray-50 w-1/2 border rounded-lg shadow">';
		$html .= '<div class="px-4 py-2 border-b ">';
		$html .= '<span class="font-semibold ">' . $title . '</span>';
		$html .= '</div>';
		$html .= '<div class="px-4 py-4">';
		$html .= '<span>' . $body . '</span>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</body>';
		$html .= '</html>';

		return $html;
	}

	private static function tailwindcssinline()
	{
		$cssFile = fopen(__DIR__ . '\templateEmail.css', 'r') or die('File not Found');
		return fread($cssFile, filesize(__DIR__ . '\templateEmail.css'));
	}
}
