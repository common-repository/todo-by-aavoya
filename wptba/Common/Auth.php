<?php

namespace Wptba\Common;

if (!defined('ABSPATH')) exit;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth
{

	/**
	 * Key to encode and decode the token
	 */
	private $key;

	/**
	 * Domain(url) that issuing the token
	 */
	private $issuer;

	/**
	 * Audience(url) that accepting the token
	 */
	private $audience;

	/**
	 * Time, when the token was issued
	 */
	private $issuedAt;


	/**
	 * Time, when the token will expire
	 * default: 1 day (60 sec X 60 min X 24 hours);
	 */
	private $expirationTime;


	/**
	 * User data to be encoded in the token
	 */
	private $data = array();

	/**
	 * Constructor
	 * @param string $key
	 * @param string $issuer
	 * @param string $audience
	 * @return void
	 */
	public function __construct(string $key = '', int $expirationTime = 1, string $issuer = '', string $audience = '')
	{
		$this->setKey($key);
		$this->setIssuer($issuer);
		$this->setAudience($audience);
		$this->setExpiration($expirationTime);
		$this->issuedAt = time();
	}

	/**
	 * setKey
	 * @param  string $key
	 * @return void
	 */
	public function setKey(string $key = '')
	{
		if ($key == '') {
			$this->key = 'wptba_secret_key';
		} else {
			$this->key = $key;
		}
	}

	/**
	 * setIssuer
	 *
	 * @param  string $issuer
	 * @return void
	 */
	public function setIssuer(string $issuer = '')
	{
		if ($issuer == '') {
			$this->issuer = get_bloginfo('url');
		} else {
			$this->issuer = $issuer;
		}
	}

	/**
	 * setAudience
	 *
	 * @param  string $audience
	 * @return void
	 */
	public function setAudience(string $audience = '')
	{
		if ($audience == '') {
			$this->audience = get_bloginfo('url');
		} else {
			$this->audience = $audience;
		}
	}

	/**
	 * setExpiration
	 *
	 * @param  int $day
	 * @return void
	 */
	public function setExpiration(int $day = 1)
	{
		if ($day > 365 || $day < 0) return;
		$this->expirationTime = time() + (60 * 60 * 24 * $day);
	}

	/**
	 * setData
	 *
	 * @param  array $data
	 * @return void
	 */
	public function setData(array $data = array())
	{
		$this->data = $data;
	}


	/**
	 * encode
	 * @param void
	 * @return string
	 */
	public function encode()
	{
		$payload = array(
			'iss' => $this->issuer,
			'aud' => $this->audience,
			'iat' => $this->issuedAt,
			'exp' => $this->expirationTime,
			'data' => $this->data
		);
		$jwt = JWT::encode($payload, $this->key, 'HS256');
		return $jwt;
	}

	/**
	 * decode
	 *
	 * @param  string $jwt
	 * @param string $key (optional if using same instance of the class with previously used setKey method or provided key during creation of the instance/object)
	 * @return array
	 */
	public function decode(string $jwt = '', $key = '')
	{
		if ($jwt == '') return;
		if ($key == '') $key = $this->key;


		try {
			$decoded = JWT::decode($jwt, new Key($key, 'HS256'));
		} catch (\Exception $e) {
			return false;
		}
		return (array)$decoded;
	}
}
