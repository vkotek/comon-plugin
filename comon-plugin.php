<?php
/**
 * Plugin Name: COM.ON plugin
 * Description: Includes customized latest posts widget and IMG zip download widget, as well as export function for Excel API.
 * Version: 2.1.0
 * Author: Vojtech Kotek
 * Author URI: http://kotek.co
 * Author Email: kotek.vojtech@gmail.com
 * License: GPL2
 * Text Domain: comon-plugin
 * Domain Path: /languages/
 */

 /*
  Misc. functions
    - comon_data_filter
    - get_val
    - get_text
    - comon_expiry
    - user_comment_count
    - userMeta
    - Custom API endpoints
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
// define plugin url

define( 'zipfile_url', plugins_url()."/".dirname( plugin_basename( __FILE__ ) ) );
define( 'COMON_PLUGIN_VERSION', '2.1.0');
define( 'COMON_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

function my_load_plugin_textdomain() {
  load_plugin_textdomain( 'comon-plugin', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'my_load_plugin_textdomain' );

include_once(ABSPATH .'wp-admin/includes/plugin.php');

// Email settings functions
require plugin_dir_path( __FILE__ ) . 'includes/emails.php';

// Shortcodes
require plugin_dir_path( __FILE__ ) . 'includes/shortcodes.php';

// Widgets
require plugin_dir_path( __FILE__ ) . 'includes/widgets.php';

// Custom REST API endpoints
require plugin_dir_path( __FILE__ ) . 'includes/endpoints.php';

/*
        _                ___                 _   _
  /\/\ (_)___  ___      / __\   _ _ __   ___| |_(_) ___  _ __  ___
 /    \| / __|/ __|    / _\| | | | '_ \ / __| __| |/ _ \| '_ \/ __|
/ /\/\ \ \__ \ (__ _  / /  | |_| | | | | (__| |_| | (_) | | | \__ \
\/    \/_|___/\___(_) \/    \__,_|_| |_|\___|\__|_|\___/|_| |_|___/

*/

// MAIN FILTERING FUNCTION FOR FRONT PAGE
function comon_data_filter() {

  // set defaults
	$debug = false;
  $user_id = get_current_user_id();

  // see nothing if not logged in.
  if ( !is_user_logged_in() ) {
    return false;
  }

  // is admin and ?debug=true in url params
  if ( current_user_can('edit_posts') ) {
    if ( $_GET['debug'] == 'true' ) {
      $debug = true;
    }
    if ( is_numeric($_GET['user']) ) {
      $user_id = $_GET['user'];
    } elseif ( $debug == false ) {
      return true;
    }
  }


	// Split users into groups of two (odd & even IDs)
	if( $user_id % 2 == 0 ) {
		$user_group = '1';
	} else {
		$user_group = '2';
	}

    // Get post info
	$post_group = get_field('group');
	$post_gender = get_field('gender');
  $post_age = get_field('age');
	$post_city = get_field('city');
	$post_edu = get_field('education');

	// Get user info
	// get_val function extracts the option number so that '13) Male' will return '13'
    $user_gender = xprofile_get_field_data( '139', $user_id);
	$user_gender = get_val($user_gender);

    $user_age = xprofile_get_field_data( '142', $user_id);
    $user_age = get_val($user_age);

    $user_city = xprofile_get_field_data( '256', $user_id);
    $user_city = get_val($user_city);

    $user_edu = xprofile_get_field_data( '186', $user_id);
	$user_edu = get_val($user_edu);

    // Show post unless any of the criteria below not satisfied
	$show = true;

	// Test post info against user info
	if ( !in_array( $user_group , $post_group ) )     { $show = false; $break = 'group'; }
    if ( !in_array($user_gender, $post_gender) )      { $show = false; $break = 'gender'; }
    if ( !in_array($user_age, $post_age) )            { $show = false; $break = 'age'; }
    if ( !in_array($user_city, $post_city) )          { $show = false; $break = 'city'; }
	if ( !in_array($user_edu, $post_edu) )            { $show = false; $break = 'education'; }


	// if "?debug=true"
	if ( $debug && !$show ) {
		printf("<b>[%d] <a href=\"%s\">%s</a></b><br>", get_the_ID(), get_the_permalink(), get_the_title());
		printf("Mismatch: <b>%s</b>", ucfirst($break));
        $post_break = (is_array(${"post_".$break})) ? array_values() : ${"post_".$break};
        print("<br>USER:<br>");
        print(${"user_".$break});
        print("<br>POST:<br>");
        print_r(${"post_".$break});
		print("<hr>");
		return false;
	}

    // Did any of the above fail?
	if ( $show ) { return true; } else { return false; }
}

// Gets the number of the answer, used in the filtering function above
function get_val(&$value) {
	if( !is_array($value) ) {
		$pos = strpos($value, ")");
		if(!$pos) {
			$pos = strpos($value, " ");
		}
		if($pos) {
			$value =  substr($value,0,$pos);
		}
	} else {
		array_walk($value, 'get_val');
	}
    return $value;
}

// Extract text from from "n) text"
function get_text(&$value) {
	$pos = strpos($value, ")");
	return substr($value, $pos + 2, strlen($value));
}

// Universal function to return days left or if expired bool if no args.
function comon_expiry($request) {
    $post_expire = get_field('topic_end');
    $expire_time = (strtotime($post_expire)-time())/86400;
    $days = ceil($expire_time);
    if ( $request == 'days') {
        return $days;
    } else {
        $active = ($expire_time > 0) ? True : False;
        return $active;
    }
}

// Unique user comments count, used above comments' section
function user_comment_count() {
    global $wpdb;
    $count = $wpdb->get_var('SELECT COUNT(comment_ID) FROM ' . $wpdb->comments. ' WHERE comment_author_email = "' . get_comment_author_email() . '"');
    return $count;
}

// Get user meta data for comments
function userMeta($user_id) {

	$meta = array();

	/* DEFAULT META */

	// Gender
  $user_gender = bp_get_profile_field_data('field=139&user_id='.$user_id);
  $user_gender = get_text($user_gender);

	// Age
	$user_age = bp_get_profile_field_data('field=142&user_id='.$user_id);

	// City size
	$user_city = bp_get_profile_field_data('field=256&user_id='.$user_id);
	switch ( get_value($user_city) ) {
		case "1":
			$user_city = "<20k";
			break;
		case "2":
			$user_city = "20<100k";
			break;
		case "3":
			$user_city = ">100k";
			break;
    default:
      $user_city = "N/A";
	}

	// Education level
	$user_edu = bp_get_profile_field_data('field=186&user_id='.$user_id);
  $user_edu = get_val($user_edu);
  switch ( get_value($user_edu) ) {
		case "1":
			$user_edu = "SS";
			break;
		case "2":
			$user_edu = "SS+M";
			break;
		case "3":
			$user_edu = "VS";
			break;
	}

	/* CUSTOM META */

	// Add your custom meta here
	// Field ID can be found in the URL when editing the profile filed in Admin. (&field_id=XXX)
	// FORMAT:
	// $user_q{x} = bp_get_profile_field_data('field={y}&user_id='.$user_id);
	// $meta[] = $user_q{x};

  # Visible to Admins only
  if ( current_user_can('edit_posts') && !current_user_can('edit_pages') ) {
    $meta[] = $user_gender;
  	$meta[] = $user_age;
  	$meta[] = $user_city;
    $meta[] = $user_edu;
  }
  # Visible to Client only
  elseif ( current_user_can('edit_pages') ) {
    $meta[] = $user_gender;
  	$meta[] = $user_age;
  	$meta[] = $user_city;
    $meta[] = $user_edu;
  }

    return(join(', ',$meta));
}

/* Stop Adding Functions Below this Line */
?>
