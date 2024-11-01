<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
	die;
}

/**
 * Delete any pending user approval and its meta 
 */

$argument = array(
	'post_type' => 'wp_todo_user',
	'post_per_page' => -1,

);

$unapprovedUsers = get_posts($argument);
unset($argument);
if ($unapprovedUsers) {
	foreach ($unapprovedUsers as $unapprovedUser) {
		delete_post_meta($unapprovedUser->ID, 'wptba_user_post_meta');
		wp_delete_post($unapprovedUser->ID, true);
	}
}

unregister_post_type('wp_todo_user');

/** end */


/**
 * Delete Todo posts and meta then related Terms
 */
$todoArgument = array(
	'post_type' => 'wp_todo_board',
	'post_per_page' => -1,

);

$todoPosts = get_posts($todoArgument);
unset($todoArgument);

if ($todoPosts) {
	foreach ($todoPosts as $todoPost) {
		delete_post_meta($todoPost->ID, 'wp_todo_board_meta');
		wp_delete_post($todoPost->ID, true);
	}
}

unregister_post_type('wp_todo_board');

$termArgument = array(
	'taxonomy' => 'wp_todo_board_tag',
	'hide_empty' => false
);

$allTerms = get_terms($termArgument);
unset($termArgument);

if ($allTerms) {
	foreach ($allTerms as $term) {
		wp_delete_term($term->ID, 'wp_todo_board_tag');
	}
}

unregister_taxonomy('wp_todo_board_tag');
/** ends */

/**
 * deleting Option data
 */
delete_option('wptba_aau'); //auto approve user setting
delete_option('wptba_autoLogOutDuration'); //auto logout user setting
delete_option('wptba_encryption_key'); //JWT encryption key 
delete_option('wptba_logo'); //logo: attachment ID
/** ends */

/**
 * removing users and role. 
 */
$allTodoUsers = get_users(array('role' => 'todoer', 'fields' => 'ID'));
if ($allTodoUsers) {
	foreach ($allTodoUsers as $todoUser) {
		wp_delete_user($todoUser);
	}
}

remove_role('todoer');

/**end */
