<?php
/*
 __ _                _                _
/ _\ |__   ___  _ __| |_ ___ ___   __| | ___  ___
\ \| '_ \ / _ \| '__| __/ __/ _ \ / _` |/ _ \/ __|
_\ \ | | | (_) | |  | || (_| (_) | (_| |  __/\__ \
\__/_| |_|\___/|_|   \__\___\___/ \__,_|\___||___/

*/

// Shortcode for statistics
add_shortcode('stats','comon_stats');
function comon_stats() {
    global $wpdb;

	// Gets IDs of all members that are in wp_users (not deleted) and that aren't admins
	$active_users_id = "
		SELECT user_id
		FROM wp_usermeta
		WHERE wp_usermeta.meta_key = 'wp_user_level'
		AND wp_usermeta.meta_value != 10
		AND user_id IN (
			SELECT id
			FROM wp_users
		)
	";

    // COUNT ALL MEMBERS
    $query_mem_count = "
	    SELECT COUNT(*) AS Amount
	    FROM wp_users
	    WHERE id IN (
	        ".$active_users_id."
	    )";

    $mem_count = $wpdb->get_results($query_mem_count);

	// COUNT ACTIVE MEMBERS v2
    $query_active_users = "
      SELECT COUNT(DISTINCT(user_id)) AS Amount
      FROM `wp_bp_xprofile_data`
      WHERE field_id IN (
    		SELECT id
    		FROM wp_bp_xprofile_fields
    		WHERE group_id =1
    	) AND user_id IN (
        ".$active_users_id."
      )
		";
    $mem_count_active = $wpdb->get_results($query_active_users);

    printf('<iframe src="%s" class="iframe-stats" style="height: 550px;">Error loading iframe..</iframe>', zipfile_url."/includes/statistics.php");

    printf('<b>Members:</b> %d / %d <small>[ filled in profile / all members ]</small><br>', $mem_count_active[0]->Amount, $mem_count[0]->Amount);
    printf( '<a href="%s">User activity table</a>', zipfile_url."/activity-stats.php" );
		$csv = plugins_url('comon-plugin/csv-comments.php');
    printf('<br><a href="%s" title="Download CSV">%s</a>', $csv, _x("Download all comments in CSV", "Alt text for icon above comments","sparkling-child"));

}

// Shortcode to display active posts list
add_shortcode( 'active_posts', 'comon_active_posts' );
function comon_active_posts() {
    $the_query = new WP_Query( array( 'posts_per_page' => -1 ) );
    while ($the_query -> have_posts()) : $the_query -> the_post();
        if ( comon_data_filter() ) :
            if ( comon_expiry() ) : ?>
                <li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a> | <?php printf(_n('<b>%s</b> day left','<b>%s</b> days left', comon_expiry('days'), 'comon-plugin'), number_format_i18n(comon_expiry('days'))); ?></li>
            <?php
            endif;
        endif;
    endwhile;
}

// Shortcode to display expired posts list
add_shortcode( 'expired_posts', 'comon_expired_posts' );
function comon_expired_posts() {

    echo "<ul>";
    $the_query = new WP_Query( array( 'posts_per_page' => -1 ) );
    while ($the_query -> have_posts()) : $the_query -> the_post();
        if ( comon_data_filter() ) :
            if ( !comon_expiry() ) : ?>
                <li> <?php
                if( current_user_can('edit_posts') && get_field('report') ){
			    printf('<a href="%s"><i class="fa fa-file-word-o" title="Download report"></i></a> | ',get_field('report'));
			    } ?>
			    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a> | <?php printf(_n('<b>%s</b> day left','<b>%s</b> days left', comon_expiry('days'), 'comon-plugin'), number_format_i18n(comon_expiry('days'))); ?></li>
            <?php
            endif;
        endif;
    endwhile;
    echo "</ul>";
}
