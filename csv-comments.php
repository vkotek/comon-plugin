<?php
header('Content-Type: text/html;charset=UTF-8');

$time_start = microtime(true);

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once("../../../wp-load.php");

define('comon-plugin', plugins_url()."/".dirname( plugin_basename( __FILE__ ) ) );


// Get post id from URL, else shows all posts
$post_id = $_GET['post'];

$args = array(
   'post_id' => $post_id,
);

// The Query
$comments_query = new WP_Comment_Query;
$comments = $comments_query->query( $args );
$stamp = date("Y-m-d", time());

// Filename
$fname = sprintf("comments_%s_%s", ( $post_id ? $post_id : "ALL"), $stamp);

// Open new file
$file = fopen($fname.".csv","w");

// Define column headers
$cols = array(
	'post_title',
	'post_date',
	'comment_id',
	'comment_parent',
	'comment_author',
  'comment_author_meta',
	'comment_date',
	'comment_content'
);

// Write column headers
fputs($file, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
fputcsv($file, $cols, ",", "'");

// Write comments
foreach( $comments as $c) {
	$data = array(
		$c->post_title,
		$c->post_date,
		$c->comment_ID,
		$c->comment_parent,
		$c->comment_author,
    userMeta($c->user_id),
		$c->comment_date,
		$c->comment_content
	);
	fputcsv($file, $data, ",");
}
fclose($file);

$url = "./".$fname.".csv";
echo "If your download does not start automatically, click <a href=".$url.">HERE</a>";

header("Location: ".$url);
exit();

?>
