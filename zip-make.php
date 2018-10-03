<html>
<head>
<meta charset="UTF-8">
<title>COM.ON - Export Images</title>
</head>
<body>
<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);

include_once("../../../wp-load.php");

// logged in users only
define('WP_USE_THEMES', false);
require('../../../wp-blog-header.php');
if(!current_user_can("edit_posts")){
    exit('You do not have access');
}

define('zipfile_path', WP_PLUGIN_DIR."/".dirname( plugin_basename( __FILE__ ) ) );
define('zipfile_url', plugins_url()."/".dirname( plugin_basename( __FILE__ ) ) );

$post_id = $_REQUEST['p'];

echo "<h3>" . get_the_title($post_id) . " - Discussion Images Export</h3>";

// Query to get all comments from given post
$post_args = array (    'post_id'	=> $post_id,    );
$comment_query = new WP_Comment_Query;
$comments = $comment_query->query( $post_args );

// Load image paths into empty array
$imgs = array();
foreach ( $comments as $comment ) {
    $attachmentId =  get_comment_meta($comment->comment_ID, 'attachmentId', TRUE);
    if(is_numeric($attachmentId) && !empty($attachmentId)){
        $imgs[] = get_attached_file( $attachmentId );
    }
}

    $files = implode(" ", $imgs);
    $filename = sprintf("export_%d.zip", $post_id);

    // Linux
    /// $command = sprintf('zip -jf %s %s', $filename, $files);
    // Windows Server
    $command = sprintf('"C:\Program Files\7-Zip\7z" a %s %s', $filename, $files);
    // echo "<hr><pre>$command</pre>";
    $output = shell_exec($command);
    echo "<hr><pre>$output</pre>";

    printf("<hr><a href=\"%s\">Download</a>", $filename);
?>
</body>
</html>
