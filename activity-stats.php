<?php 
header('Content-Type: text/html; charset=utf-8');
$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
require_once( $parse_uri[0] . 'wp-load.php' );
global $wpdb;

$query_admin_ids = "
    SELECT wp_usermeta.user_id
    FROM wp_usermeta
    WHERE wp_usermeta.meta_key = 'wp_user_level' AND wp_usermeta.meta_value = 10
    ";

// $admin_ids = $wpdb->get_results($query_admin_ids);
/*
// COUNT ALL MEMBERS
$query_mem_count = "
	SELECT COUNT(*) as Amount
	FROM wp_usermeta
	WHERE wp_usermeta.meta_key = 'wp_user_level' AND wp_usermeta.meta_value != 10";
	
$mem_count = $wpdb->get_results($query_mem_count);

// COUNT ACTIVE MEMBERS
$query_active_users = "
	SELECT COUNT(*) AS Amount
	FROM wp_bp_xprofile_data
	WHERE field_id = 2 AND user_id NOT IN (
				SELECT user_id
				FROM wp_usermeta
				WHERE wp_usermeta.meta_key = 'wp_user_level' AND wp_usermeta.meta_value = 10
			)";

$mem_count_active = $wpdb->get_results($query_active_users);
	*/
$query_users = $wpdb->prepare("
    SELECT id, user_login
    FROM wp_users
    WHERE id NOT IN (".$query_admin_ids.")
    ORDER BY id
    ");
$data_users = $wpdb->get_results($query_users);

$query_posts = "
    SELECT ID, post_date, post_title
    FROM wp_posts
    WHERE post_type = 'post' AND post_status = 'publish' 
    ORDER BY ID 
    ";
$data_posts = $wpdb->get_results($query_posts);

?>
<html>
<head>
<meta charset=“utf-8”>
<style>
table {
    border: none;
}
tr.header, tr.total {
    background-color: #888;
}
tr.even {
    background-color: #ccc;
}
</style>
</head> 
<body>
<table>
<tr class="header">
<td>ID</td><td>User</td>
<?php
        foreach( $data_posts as $post) {
            printf("<td>%d<br>%s</td>", $post->ID, $post->post_title);
        }
?>
<td>SUM</td>
</tr>
<?php
    $x = 0;
    foreach( $data_users as $user) {
    
        // Colorful even rows
        echo "<tr class=".( $x % 2 ? "even" : "odd" ).">";
        $x+=1;
        
        printf("<td>%d</td><td>%s</td>",$user->id, $user->user_login);
        $total = "0";
        foreach( $data_posts as $post) {
            $comments = $wpdb->get_results(
                $wpdb->prepare("
                SELECT COUNT(user_id) AS 'count'
                FROM wp_comments
                WHERE user_id = %d AND comment_post_id = %d
                ",$user->id,$post->ID));
            $total+=$comments[0]->count;
            printf("<td>%d</td>",$comments[0]->count);
        }
        printf("<td><b>%d</b></td>",$total);
        print("</tr>");
    }
  
  

  
?>
<tr class="total">
<td></td><td>SUM</td>
<?php
        foreach( $data_posts as $post) {
            $sum = $wpdb->get_results(
                $wpdb->prepare("
                    SELECT count(comment_post_id) AS 'count'
                    FROM `wp_comments`
                    WHERE comment_post_id = %d AND user_id NOT IN (".$query_admin_ids.")
                ",$post->ID));
        
            printf("<td><b>%d</b></td>",$sum[0]->count);
            
        }
?>
<td></td>
</tr>
</table>
</body>
</html>
