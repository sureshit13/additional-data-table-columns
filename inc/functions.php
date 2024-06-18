<?php

// Add a custom menu to the admin
function sc_add_admin_menu() {
    add_menu_page(
        'Show Column Settings', 
        'Show Column Settings', 
        'manage_options', 
        'sc_cpt_meta', 
        'sc_display_admin_page',
        'dashicons-list-view', 
        30
    );
}
add_action('admin_menu', 'sc_add_admin_menu');

// Display the admin page
function sc_display_admin_page() {
    ?>
    <style type="text/css"></style>
    <div class="wrap">
        
        <form id="sc-meta-fields-form" method="post" action="">
            <?php wp_nonce_field('sc-meta-fields-nonce', 'sc-meta-fields-nonce'); ?>
            <select id="post_type" name="post_type" class="post_type_list">
                <option value="">Select Post Type</option>
                <?php
                $post_types = get_post_types(array('public' => true), 'objects');
                unset( $post_types['attachment'] );
                foreach ($post_types as $post_type) {
                    echo '<option value="' . esc_attr($post_type->name) . '">' . esc_html($post_type->label) . '</option>';
                }
                ?>
            </select>
            <div id="loader"></div>
            <div id="meta-fields" class="sc-meta-fields"></div>
            <input type="submit" name="submit" value="Set Meta Fields Column" class="sc-submit-button">
        </form>
        
    </div>
    <?php
}

// Handle the AJAX request
function sc_get_meta_fields() {
    if (isset($_POST['post_type'])) {
        $post_type = sanitize_text_field($_POST['post_type']);

        if($post_type != 'page'){

            $posts = get_posts(array('post_type' => $post_type, 'numberposts' => 1));
            if (!empty($posts)) {
                $post_id = $posts[0]->ID;
                $meta_keys = get_post_custom_keys($post_id);
                if ($meta_keys) {
                    // Define default WordPress meta keys
                    $default_meta_keys = array('_edit_lock', '_edit_last', '_thumbnail_id', '_wp_page_template', '_wp_old_slug', '_wp_trash_meta_status', '_wp_trash_meta_time');

                    //$output = '<h2>List Of Meta Fields Of ' . ucfirst(esc_html($post_type)) . ' Post Type.</h2>';
                    $output = '</br>';
                    foreach ($meta_keys as $meta_key) {
                        // Check if the meta key is not a default WordPress meta key
                        if (!in_array($meta_key, $default_meta_keys)) {
                            $output .= '<input class="sc-meta-fields-checkbox" type="checkbox" name="meta_keys[]" value="' . esc_attr($meta_key) . '">' . esc_html(transform_meta_field_name($meta_key)) . '</br></br>';
                        }
                    }
            
                } else {
                    $output = '<p>No meta fields found for this post type.</p>';
                }
            } else {
                $output = '<p>No posts found for this post type.</p>';
            }
        }else{
            // Usage example
            $pages_custom_meta_fields = get_all_custom_meta_fields_for_pages();
            //$output = '<h2>List Of Meta Fields Of ' . ucfirst(esc_html($post_type)) . ' Post Type.</h2>';
            // Output the custom meta fields as input elements
            $output = '</br>';
            foreach ($pages_custom_meta_fields as $page_id => $meta_fields) {
                //echo '<h3>Page ID: ' . $page_id . '</h3>';
                foreach ($meta_fields as $meta_key => $meta_value) {
                    $meta_value = is_array($meta_value) ? implode(', ', $meta_value) : $meta_value;
                    $output .= '<input class="sc-meta-fields-checkbox" type="checkbox" name="meta_keys[]" value="' . esc_attr($meta_key) . '">' . esc_html(transform_meta_field_name($meta_key)) . '</br></br>';
                }
            }
        
        }
        echo $output;
    }
    wp_die();
}

add_action('wp_ajax_sc_get_meta_fields', 'sc_get_meta_fields');
add_action('wp_ajax_nopriv_sc_get_meta_fields', 'sc_get_meta_fields');


function get_all_custom_meta_fields_for_pages() {
    // Get all pages
    $pages = get_posts(array(
        'post_type' => 'page',
        'numberposts' => -1 // Retrieve all pages
    ));
  
    // Initialize an array to store custom meta fields
    $all_custom_meta_fields = array();
  
    // Default meta keys to exclude
    $default_meta_keys = array('_edit_lock', '_edit_last', '_wp_page_template', '_wp_old_slug');
  
    // Loop through each page
    foreach ($pages as $page) {
        // Get the meta fields for the current page
        $meta_fields = get_post_meta($page->ID);
  
        // Filter out default meta fields
        $custom_meta_fields = array_diff_key($meta_fields, array_flip($default_meta_keys));
  
        // Add the custom meta fields to the array
        if (!empty($custom_meta_fields)) {
            $all_custom_meta_fields[$page->ID] = $custom_meta_fields;
        }
    }
  
    return $all_custom_meta_fields;
}
  
function sc_get_form_data() {
    check_ajax_referer('sc-meta-fields-nonce', 'nonce');
    
    if (isset($_POST['post_type'])) {

        $post_type = sanitize_text_field($_POST['post_type']);
        if(isset($_POST['meta_keys'])){
            $meta_keys = array_map('sanitize_text_field', $_POST['meta_keys']);
        }
        if(isset($_POST['uncheck_meta_keys'])){
            $uncheck_meta_keys = array_map('sanitize_text_field', $_POST['uncheck_meta_keys']);
        }
        // Store the selected post type and meta keys in the options table
        if (isset($meta_keys)){
            update_option('sc_selected_post_type', $post_type);
            update_option('sc_selected_meta_keys', $meta_keys);
        }

        if(isset($uncheck_meta_keys)){
            update_option('sc_selected_post_type', $post_type);
            update_option('sc_unselected_meta_keys', $uncheck_meta_keys);
        }
        
        echo json_encode(['status' => 'success', 'message' => 'Settings saved successfully.']);  
    }
    wp_die();
}

add_action('wp_ajax_sc_get_form_data', 'sc_get_form_data');
add_action('wp_ajax_nopriv_sc_get_form_data', 'sc_get_form_data');

function add_custom_columns($columns) {
    $meta_keys = get_option('sc_selected_meta_keys');
    if ($meta_keys) {
        foreach ($meta_keys as $key) {
            $columns[$key] = ucfirst(str_replace('_', ' ', $key));
        }
    }
    return $columns;
}

function populate_custom_columns($column, $post_id) {
    $meta_keys = get_option('sc_selected_meta_keys');
    if ($meta_keys && in_array($column, $meta_keys)) {
        $meta_value = get_post_meta($post_id, $column, true);
        echo esc_html($meta_value);
    }
}

function make_custom_columns_sortable($columns) {
    $meta_keys = get_option('sc_selected_meta_keys');
    if ($meta_keys) {
        foreach ($meta_keys as $key) {
            $columns[$key] = $key;
        }
    }
    return $columns;
}

function custom_column_orderby($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }

    $meta_keys = get_option('sc_selected_meta_keys');
    $orderby = $query->get('orderby');

    if ($meta_keys && in_array($orderby, $meta_keys)) {
        $query->set('meta_key', $orderby);
        $query->set('orderby', 'meta_value');
    }
}

$post_type = get_option('sc_selected_post_type'); // Array of selected post types (post, page, book)
$meta_keys = get_option('sc_selected_meta_keys');
if ($post_type && $meta_keys) {
    add_filter("manage_{$post_type}_posts_columns", 'add_custom_columns');
    add_action("manage_{$post_type}_posts_custom_column", 'populate_custom_columns', 10, 2);
    add_filter("manage_edit-{$post_type}_sortable_columns", 'make_custom_columns_sortable');
    add_action('pre_get_posts', 'custom_column_orderby');
}


$uncheck_keys = get_option('sc_unselected_meta_keys'); // Array of meta keys to be removed

// Hook to remove custom columns based on unselected meta keys
add_filter("manage_{$post_type}_posts_columns", function($columns) use ($uncheck_keys) {
    foreach ($uncheck_keys as $key) {
        if (isset($columns[$key])) {
            unset($columns[$key]);
        }
    }
    return $columns;
});

// Optionally, you may need to refresh the admin page to reflect the changes immediately
add_action('admin_init', function() {
    $post_type = get_option('sc_selected_post_type');
    $screen = get_current_screen();
    
    if ($screen && $screen->id === "edit-{$post_type}") {
        $location = admin_url("edit.php?post_type={$post_type}");
        wp_redirect($location);
        exit;
    }
});


?>