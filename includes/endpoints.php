<?php
/*
Nicename here.
*/

/* Custom REST API endpoints */

// require( "../wp-admin/includes/plugin.php" );

// Custom API endpoint to get Version of current plugins, wordpress, theme.
function api_get_versions( $data ) {

  // Get all active plugins from wp_options 'active_plugins'
  // Use built-in function get_plugin_data()
  // result => 'Version' to get the version

  global $wpdb;
  $query = $wpdb->prepare(
    "SELECT option_value FROM wp_options WHERE option_name = 'active_plugins';"
  );

  $data = $wpdb->get_var($query);
  $data = explode(";", str_replace(":",";", $data));

  $plugins = array();

  foreach( $data as $key => $item ) {
    if ( substr($item, -1) == "\"" ) {
      $plugins[] = array($item, get_plugin_data($item, $markup = false));
    }
  }

  // $data = $plugins;
  // $data = get_plugin_data('comon-plugin/comon-plugin.php', $markup = false, $translate = false);

  // $response = new WP_REST_Response($data);

  return $plugins;
}

add_action('rest_api_init', function () {
  register_rest_route( 'comonplugin/v1', '/versions', array(
    'methods' => 'GET',
    'callback' => 'api_get_versions',
  ));
} );

?>
