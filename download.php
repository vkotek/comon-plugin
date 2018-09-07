<?php 

include_once("../../../wp-load.php");

define('zipfile_path', WP_PLUGIN_DIR."/".dirname( plugin_basename( __FILE__ ) ) );

$filename = $_REQUEST['f'];

    $upload_dir = wp_upload_dir();
	$file = $upload_dir['url'] . "/" . $filename;
	$test_path = zipfile_path . "/" . $filename;
	
	$size = filesize($test_path);
	
		header('Content-Description: File Transfer');
		header('Content-Type: application/zip');
		header('Content-Disposition: attachment; filename="ideablog.zip"');
		header('Content-Length: ' . $size);            
		ob_clean();
        flush();

		readfile($filename);
		unlink($test_path);
	
	exit;
	
?>