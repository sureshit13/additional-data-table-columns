<?php
/*
Plugin Name: Show Column
Description: Adds custom meta boxes to posts, pages, and a custom post type and show in data table list.
Version: 1.0
Author: Suresh Dutt
*/

define('SC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SC_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('SC_PLUGIN_VERSION', '1.0');

// Enqueue scripts and styles
function sc_enqueue_assets($hook) {
  // Check if we're on the custom plugin menu page
  if ($hook !== 'toplevel_page_sc_cpt_meta') {
    return;
  }
  wp_enqueue_style('sc-main-css', SC_PLUGIN_URL . 'assets/css/style.css', array(), SC_PLUGIN_VERSION);
  wp_enqueue_style('sc-font-awesome-all-min-css', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', array(), '6.4.0');
  wp_enqueue_style('sc-animate-min-css', 'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css', array(), '4.1.1');
  wp_enqueue_style('sc-bootstrap-min-css', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.0.2/css/bootstrap.min.css', array(), '5.0.2');
  
  wp_enqueue_script('sc-script-js', SC_PLUGIN_URL . 'assets/js/script.js', array('jquery'), SC_PLUGIN_VERSION, true);
  wp_localize_script('sc-script-js', 'sc_ajax_obj', array(
    'ajax_url' => admin_url('admin-ajax.php')
  ));
  wp_enqueue_script('sc-sweet-alert2-js', 'https://cdn.jsdelivr.net/npm/sweetalert2@11', array('jquery'), '2.1.1', true);
  wp_enqueue_script('sc-bootstrap-min-js', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.3/js/bootstrap.min.js', array('jquery'), '5.2.3', true);
  
}
add_action('admin_enqueue_scripts', 'sc_enqueue_assets');

require_once SC_PLUGIN_PATH . 'inc/custom-metabox-fields.php';
require_once SC_PLUGIN_PATH . 'inc/functions.php';

function sc_register_custom_post_type() {
  $args = array(
      'public' => true,
      'label'  => 'Books',
      'supports' => array('title', 'editor', 'thumbnail'),
  );
  register_post_type('book', $args);
}
add_action('init', 'sc_register_custom_post_type');

// Activation hook
register_activation_hook(__FILE__, 'sc_plugin_activation');

// Deactivation hook
register_deactivation_hook(__FILE__, 'sc_plugin_deactivation');

// Uninstall hook
register_uninstall_hook(__FILE__, 'sc_plugin_uninstall');

function sc_plugin_activation() {
  // Activation tasks here
}

function sc_plugin_deactivation() {
  // Deactivation tasks here
}

function sc_plugin_uninstall() {
  // Uninstall tasks here
}

// custom function remove "_" and " " from text.
function transform_meta_field_name($meta_field) {
  // Replace underscore with space and remove leading underscore or space
  $transformed_name = ltrim(str_replace('_', ' ', $meta_field), ' _');

  // Capitalize the first letter of each word
  $transformed_name = ucwords($transformed_name);

  return $transformed_name;
}

?>