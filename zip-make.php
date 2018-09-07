<html>
<head>
<meta charset="UTF-8">
<title>ZIP - JakToVidim</title>
</head>
<body>
<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);

include_once("../../../wp-load.php");

define('zipfile_path', WP_PLUGIN_DIR."/".dirname( plugin_basename( __FILE__ ) ) );
define('zipfile_url', plugins_url()."/".dirname( plugin_basename( __FILE__ ) ) );


$post_id = $_REQUEST['p'];

echo "<h3>" . get_the_title($post_id) . "</h3>";

		$post_args = array (
		'post_id'	=> $post_id,
	);
		
	$comment_query = new WP_Comment_Query;
	$comments = $comment_query->query( $post_args );

	// set empty array
	$imgs = array();
	
	// Comnent loop
	foreach ( $comments as $comment ) {
		$attachmentId =  get_comment_meta($comment->comment_ID, 'attachmentId', TRUE);
		if(is_numeric($attachmentId) && !empty($attachmentId)){

			// atachement info
			$real_path = get_attached_file( $attachmentId );
			$imgs[] = $real_path;
		} 
	}
		
//	$imgs = array('E:/FTP/LocalUser/jaktovidim/wp-content/uploads/2016/01/10590573_10205553413257250_377586726030243061_n.jpg', 'E:/FTP/LocalUser/jaktovidim/wp-content/uploads/2016/01/přání.jpg');

	// This cleans up czech characters in first posts where attachements weren't sanitized.
	$ext = array('ě','š','č','ř','ž','ý','á','í','é','Ě','Š','Č','Ř','Ž','Ý','Á','Í','É');
	$eng = array('e','s','c','r','z','y','a','i','e','E','S','C','R','Z','Y','A','I','E');

		
	$file = tempnam(zipfile_path, "zip");

	$file = str_replace("\\","/",$file);
	
	$zip = new ZipArchive();
	$zip->open($file, ZipArchive::OVERWRITE);

	// Loop through the attachments and add them to the file
	if ( $imgs ) {
		echo "Zipping..<ol>";
		foreach ( $imgs as $img ) {
			// Get the file name
			$name = explode('/', $img);
			$name = $name[sizeof($name) - 1];
			//echo "<li>" . $name . "</li>";
			if ( $post_id == 376 OR $post_id == 318 ) {
			$img = str_replace($ext,$eng,$img);
			}
			$img = str_replace("\\","/",$img);
			//echo preg_replace('/[^A-Za-z0-9\-]/', '', $img);
			echo "<li>" . $img . "</li>";
			$zip->addFile($img,$name);
		}
		echo "</ol>";
	}

//	$zip->addFile('E:/FTP/LocalUser/jaktovidim/wp-content/uploads/2016/01/10590573_10205553413257250_377586726030243061_n.jpg','file.jpg');

	if (is_writable($file)) {
		echo 'The file is writable';
	} else {
		echo 'The file is not writable';
	}
	echo "<br>";
	// Store the filename before closing the file

	$filename_array = explode('/', $zip->filename);
	$filename = $filename_array[sizeof($filename_array) - 1];

	echo "numfiles: " . $zip->numFiles . "<br>";
	
	//Close the file
	$zip->close();
	
	$bytesize = round(filesize($filename)/1024/1024,2);
	echo "<br>Size: " . $bytesize ."MB</small>";
	
	$dl_link = zipfile_url . "/download.php?f=" . basename($filename);
	
	echo "<hr>";
	printf ("<a href=\"%s\"><h2>Download</h2></a>", $dl_link );
	

?>
</body>
</html>