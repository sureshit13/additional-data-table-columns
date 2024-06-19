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
    <div class="wrap">
        <form id="sc-meta-fields-form" method="post" action="">
            <?php wp_nonce_field('sc-meta-fields-nonce', 'sc-meta-fields-nonce'); ?>
            <select id="post_type" name="post_type" class="post_type_list">
                <option value="">Select Post Type</option>
                <?php
                $post_types = get_post_types(array('public' => true), 'objects');
                unset($post_types['attachment']);
                foreach ($post_types as $post_type) {
                    echo '<option value="' . esc_attr($post_type->name) . '">' . esc_html($post_type->label) . '</option>';
                }
                ?>
            </select>
            <div id="loader"></div>
            <div id="meta-fields" class="sc-meta-fields new"></div>
            <input type="submit" name="submit" value="Set Meta Fields Column" class="sc-submit-button">
        </form>
    </div>
    
    <?php
}

// Handle the AJAX request to get meta fields
function sc_get_meta_fields() {
    if (isset($_POST['post_type'])) {
        $post_type = sanitize_text_field($_POST['post_type']);
        $output = '';

        if ($post_type != 'page') {
            $posts = get_posts(array('post_type' => $post_type, 'numberposts' => 1));
            if (!empty($posts)) {
                $post_id = $posts[0]->ID;
                $meta_keys = get_post_custom_keys($post_id);
                if ($meta_keys) {
                    // Define default WordPress meta keys
                    $default_meta_keys = array('_edit_lock', '_edit_last', '_thumbnail_id', '_wp_page_template', '_wp_old_slug', '_wp_trash_meta_status', '_wp_trash_meta_time');
                    $unique_meta_keys = array_unique($meta_keys);

                    foreach ($unique_meta_keys as $meta_key) {
                        if (!in_array($meta_key, $default_meta_keys)) {
                            $output .= '<div class="form-group">
                                            <input class="sc-meta-fields-checkbox" type="checkbox" id="' . esc_attr($meta_key) . '" name="meta_keys[]" value="' . esc_attr($meta_key) . '">
                                            <label for="' . esc_attr($meta_key) . '">' . esc_html(transform_meta_field_name($meta_key)) . '</label>
                                        </div>';
                        }
                    }
                } else {
                    $output = '<p>No meta fields found for this post type.</p>';
                }
            } else {
                $output = '<p>No posts found for this post type.</p>';
            }
        } else {
            $pages_custom_meta_fields = get_all_custom_meta_fields_for_pages();
            $unique_meta_keys = array();

            foreach ($pages_custom_meta_fields as $page_id => $meta_fields) {
                foreach ($meta_fields as $meta_key => $meta_value) {
                    if (!in_array($meta_key, $unique_meta_keys)) {
                        $unique_meta_keys[] = $meta_key;
                        $meta_value = is_array($meta_value) ? implode(', ', $meta_value) : $meta_value;
                        $output .= '<div class="form-group">
                                        <input class="sc-meta-fields-checkbox" type="checkbox" id="' . esc_attr($meta_key) . '" name="meta_keys[]" value="' . esc_attr($meta_key) . '">
                                        <label for="' . esc_attr($meta_key) . '">' . esc_html(transform_meta_field_name($meta_key)) . '</label>
                                    </div>';
                    }
                }
            }
        }
        echo $output;
    }
    wp_die();
}
add_action('wp_ajax_sc_get_meta_fields', 'sc_get_meta_fields');
add_action('wp_ajax_nopriv_sc_get_meta_fields', 'sc_get_meta_fields');

// Get all custom meta fields for pages
function get_all_custom_meta_fields_for_pages() {
    $pages = get_posts(array('post_type' => 'page', 'numberposts' => -1));
    $all_custom_meta_fields = array();
    $default_meta_keys = array('_edit_lock', '_edit_last', '_wp_page_template', '_wp_old_slug');

    foreach ($pages as $page) {
        $meta_fields = get_post_meta($page->ID);
        $custom_meta_fields = array_diff_key($meta_fields, array_flip($default_meta_keys));
        if (!empty($custom_meta_fields)) {
            $all_custom_meta_fields[$page->ID] = $custom_meta_fields;
        }
    }
    return $all_custom_meta_fields;
}

// Handle form data via AJAX
function sc_get_form_data() {
    check_ajax_referer('sc-meta-fields-nonce', 'nonce');
    
    if (isset($_POST['post_type'])) {
        $post_type = sanitize_text_field($_POST['post_type']);
        $meta_keys = isset($_POST['meta_keys']) ? array_map('sanitize_text_field', $_POST['meta_keys']) : array();

        $all_columns = get_option('sc_all_selected_meta_keys', []);
        $all_columns[$post_type] = array_unique($meta_keys);

        update_option('sc_all_selected_meta_keys', $all_columns);

        echo json_encode(['status' => 'success', 'message' => 'Settings saved successfully.', 'posttype' =>$post_type]);  
    }
    wp_die();
}
add_action('wp_ajax_sc_get_form_data', 'sc_get_form_data');
add_action('wp_ajax_nopriv_sc_get_form_data', 'sc_get_form_data');

// Hook to manage custom columns for the selected post type
function sc_manage_custom_columns($columns) {
    global $post_type;
    $all_columns = get_option('sc_all_selected_meta_keys', []);
    
    if (isset($all_columns[$post_type])) {
        $meta_keys = $all_columns[$post_type];
        
        // Remove all existing custom columns added by this plugin
        foreach ($columns as $key => $value) {
            if (strpos($key, 'sc_custom_column_') === 0) {
                unset($columns[$key]);
            }
        }

        // Add new custom columns based on the selected meta keys
        $unique_meta_keys = array_unique($meta_keys);
        foreach ($unique_meta_keys as $meta_key) {
            $columns['sc_custom_column_' . $meta_key] = ucfirst(str_replace('_', ' ', $meta_key));
        }
    }

    return $columns;
}

// Hook to display content for custom columns
function sc_custom_column_content($column_name, $post_id) {
    if (strpos($column_name, 'sc_custom_column_') === 0) {
        $meta_key = str_replace('sc_custom_column_', '', $column_name);
        $meta_value = get_post_meta($post_id, $meta_key, true);
        echo esc_html($meta_value);
    }
}


function sc_manage_sortable_columns($sortable_columns) {
    $all_columns = get_option('sc_all_selected_meta_keys', []);
    $post_types = get_post_types(array('public' => true), 'names');

    foreach ($post_types as $post_type) {
        if (isset($all_columns[$post_type])) {
            $meta_keys = $all_columns[$post_type];
            foreach ($meta_keys as $meta_key) {
                $sortable_columns['sc_custom_column_' . $meta_key] = $meta_key;
            }
        }
    }

    return $sortable_columns;
}

function sc_custom_column_sort($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }

    $orderby = $query->get('orderby');

    if (strpos($orderby, 'sc_custom_column_') === 0) {
        $meta_key = substr($orderby, strlen('sc_custom_column_'));
        $query->set('meta_key', $meta_key);
        $query->set('orderby', 'meta_value');
    }
}


// Apply custom columns only for post types with selected columns
function sc_apply_custom_columns() {
    $all_columns = get_option('sc_all_selected_meta_keys', []);
    
    foreach ($all_columns as $post_type => $meta_keys) {
        add_filter('manage_'. $post_type .'_posts_columns', 'sc_manage_custom_columns');
        add_action('manage_'. $post_type .'_posts_custom_column', 'sc_custom_column_content', 10, 2);
        add_filter('manage_edit-'. $post_type .'_sortable_columns', 'sc_manage_sortable_columns');
    }
}
add_action('admin_init', 'sc_apply_custom_columns');
