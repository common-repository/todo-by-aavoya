<?php

namespace Wptba\Frontend;

if (!defined('ABSPATH')) exit;

class Enqueue{
	
	public static function do(){
	
		add_action('wp_enqueue_scripts', array(self::class, 'enqueue'));
		add_action('wp_enqueue_scripts', array(self::class, 'removeAll'));
	
	}
	
	/**
	 * removeAll
	 * This to remove all scripts and styles
	 * from wordpress
	 * @return void
	 */
	public static function removeAll(){
			global $wp_scripts;
			$registeredScripts = $wp_scripts->registered;
	
			foreach($registeredScripts as $registeredScript){
				if($registeredScript->handle != 'wptba-frontend-script'){
					wp_dequeue_script($registeredScript->handle);
				}
			}
	
			global $wp_styles;
			$registeredStyles = $wp_styles->registered;
			
			foreach($registeredStyles as $registeredStyle){
				if($registeredStyle->handle != 'wptba-frontend-style' && $registeredStyle->handle != 'wptba-frontend-fonts'){
					wp_dequeue_style($registeredStyle->handle);
				}
			}
	}
	
	/**
	 * enqueue
	 * This to enqueue all scripts and styles for this plugin
	 * @return void
	 */
	public static function enqueue(){
		wp_enqueue_style('wptba-frontend-style', plugin_dir_url(__FILE__) . 'client/dist/main.css', array(), '1.0.0','all');
		wp_enqueue_style('wptba-frontend-fonts', plugin_dir_url(__FILE__) . 'client/dist/fonts.css', array(), '1.0.0','all');
		wp_enqueue_script('wptba-frontend-script', plugin_dir_url(__FILE__) . 'client/dist/main.js', array(), '1.0.0',true);
	}
}
