<?php

// Function to change email address
add_filter( 'wp_mail_from', 'wpb_sender_email' );
function wpb_sender_email( $original_email_address ) {
    return 'comon@mbcomon.cz';
}

// Function to change sender name
add_filter( 'wp_mail_from_name', 'wpb_sender_name' );
function wpb_sender_name( $original_email_from ) {
	return 'COM.ON';
}



?>
