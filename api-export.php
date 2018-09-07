<?php

$time_start = microtime(true);

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once("../../../wp-load.php");

global $wpdb;

$args = "
	SELECT DATE_FORMAT(wp_posts.post_date,'%Y') AS Year ,DATE_FORMAT(wp_posts.post_date,'%m') AS Month, DATE_FORMAT(wp_posts.post_date,'%d') AS Day, wp_posts.post_title AS Thread, wp_users.user_login AS User, COUNT(wp_comments.comment_post_id) AS Comments
	FROM wp_comments
	LEFT JOIN wp_users ON wp_comments.user_id = wp_users.ID
	LEFT JOIN wp_posts ON wp_comments.comment_post_ID = wp_posts.ID
	LEFT JOIN wp_usermeta ON wp_comments.user_id = wp_usermeta.user_id AND wp_usermeta.meta_key = 'wp_user_level'
	WHERE wp_comments.comment_approved = 1 AND wp_usermeta.meta_value != 10
	GROUP BY wp_users.ID, wp_comments.comment_post_id
	ORDER BY wp_posts.post_date
	";

$results = $wpdb->get_results($args);


$file = fopen("export.csv","w");

	foreach( $results as $line) {
		$y = ($line->Year);
		$m = ($line->Month);
		$d = ($line->Day);
		$t = "'".($line->Thread)."'";
		$u = ($line->User);
		$c = ($line->Comments);
		$data = array($y,$m,$d,$t,$u,$c);
		fwrite( $file,  join(',',$data) );
		fwrite( $file, "\n" );
	}


fclose($file);

$time_end = microtime(true);
$date = date('Y-m-d @ H:i:s');
print $date ." za ". round( ($time_end - $time_start)*1000, 1 ) ." ms";


?>