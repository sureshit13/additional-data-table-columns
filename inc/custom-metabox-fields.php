<?php

// Add meta box for banner fields to pages
function custom_add_page_banner_meta_box() {
  add_meta_box(
      'page_banner_meta_box', // Meta box ID
      'Page Banner Settings', // Title of the meta box
      'custom_page_banner_meta_box_callback', // Callback function
      'page', // Post type: page
      'normal', // Context: normal
      'high' // Priority: high
  );
}
add_action('add_meta_boxes', 'custom_add_page_banner_meta_box');

// Callback function to display meta box contents
function custom_page_banner_meta_box_callback($post) {
  // Add nonce for security and authentication
  wp_nonce_field('custom_page_banner_meta_box', 'custom_page_banner_meta_box_nonce');

  // Get existing values for meta fields
  $banner_title = get_post_meta($post->ID, '_page_banner_title', true);
  $banner_subheading = get_post_meta($post->ID, '_page_banner_subheading', true);
  $banner_content = get_post_meta($post->ID, '_page_banner_content', true);

  // Output fields
  echo '<p><label for="banner_title">Page Banner Title:</label><br>';
  echo '<input type="text" id="banner_title" name="banner_title" value="' . esc_attr($banner_title) . '" size="80"></p>';

  echo '<p><label for="banner_subheading">Page Banner Subheading:</label><br>';
  echo '<input type="text" id="banner_subheading" name="banner_subheading" value="' . esc_attr($banner_subheading) . '" size="80"></p>';

  echo '<p><label for="banner_content">Page Banner Content:</label><br>';
  echo '<textarea id="banner_content" name="banner_content" rows="5" cols="80">' . esc_textarea($banner_content) . '</textarea></p>';
}

// Save meta box content
function custom_save_page_banner_meta_box($post_id) {
  // Check if nonce is set
  if (!isset($_POST['custom_page_banner_meta_box_nonce'])) {
      return;
  }

  // Verify nonce
  if (!wp_verify_nonce($_POST['custom_page_banner_meta_box_nonce'], 'custom_page_banner_meta_box')) {
      return;
  }

  // Check if this is an autosave
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
      return;
  }

  // Check the user's permissions
  if (!current_user_can('edit_page', $post_id)) {
      return;
  }

  // Update or add meta values
  if (isset($_POST['banner_title'])) {
      update_post_meta($post_id, '_page_banner_title', sanitize_text_field($_POST['banner_title']));
  }
  if (isset($_POST['banner_subheading'])) {
      update_post_meta($post_id, '_page_banner_subheading', sanitize_text_field($_POST['banner_subheading']));
  }
  if (isset($_POST['banner_content'])) {
      update_post_meta($post_id, '_page_banner_content', sanitize_textarea_field($_POST['banner_content']));
  }
}
add_action('save_post', 'custom_save_page_banner_meta_box');



// Add meta box for post banner fields
function custom_add_post_banner_meta_box() {
  add_meta_box(
      'post_banner_meta_box', // Meta box ID
      'Post Banner Settings', // Title of the meta box
      'custom_post_banner_meta_box_callback', // Callback function
      'post', // Post type: post
      'normal', // Context: normal
      'high' // Priority: high
  );
}
add_action('add_meta_boxes', 'custom_add_post_banner_meta_box');

// Callback function to display meta box contents
function custom_post_banner_meta_box_callback($post) {
  // Add nonce for security and authentication
  wp_nonce_field('custom_post_banner_meta_box', 'custom_post_banner_meta_box_nonce');

  // Get existing values for meta fields
  $post_banner_title = get_post_meta($post->ID, '_post_banner_title', true);
  $post_banner_subheading = get_post_meta($post->ID, '_post_banner_subheading', true);
  $post_banner_content = get_post_meta($post->ID, '_post_banner_content', true);

  // Output fields
  echo '<p><label for="post_banner_title">Post Banner Title:</label><br>';
  echo '<input type="text" id="post_banner_title" name="post_banner_title" value="' . esc_attr($post_banner_title) . '" size="80"></p>';

  echo '<p><label for="post_banner_subheading">Post Banner Subheading:</label><br>';
  echo '<input type="text" id="post_banner_subheading" name="post_banner_subheading" value="' . esc_attr($post_banner_subheading) . '" size="80"></p>';

  echo '<p><label for="post_banner_content">Post Banner Content:</label><br>';
  echo '<textarea id="post_banner_content" name="post_banner_content" rows="5" cols="80">' . esc_textarea($post_banner_content) . '</textarea></p>';
}

// Save meta box content
function custom_save_post_banner_meta_box($post_id) {
  // Check if nonce is set
  if (!isset($_POST['custom_post_banner_meta_box_nonce'])) {
      return;
  }

  // Verify nonce
  if (!wp_verify_nonce($_POST['custom_post_banner_meta_box_nonce'], 'custom_post_banner_meta_box')) {
      return;
  }

  // Check if this is an autosave
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
      return;
  }

  // Check the user's permissions
  if (!current_user_can('edit_post', $post_id)) {
      return;
  }

  // Update or add meta values
  if (isset($_POST['post_banner_title'])) {
      update_post_meta($post_id, '_post_banner_title', sanitize_text_field($_POST['post_banner_title']));
  }
  if (isset($_POST['post_banner_subheading'])) {
      update_post_meta($post_id, '_post_banner_subheading', sanitize_text_field($_POST['post_banner_subheading']));
  }
  if (isset($_POST['post_banner_content'])) {
      update_post_meta($post_id, '_post_banner_content', sanitize_textarea_field($_POST['post_banner_content']));
  }
}
add_action('save_post', 'custom_save_post_banner_meta_box');




// Add meta box for book banner fields
function custom_add_book_banner_meta_box() {
  add_meta_box(
      'book_banner_meta_box', // Meta box ID
      'Book Banner Settings', // Title of the meta box
      'custom_book_banner_meta_box_callback', // Callback function
      'book', // Post type: book
      'normal', // Context: normal
      'high' // Priority: high
  );
}
add_action('add_meta_boxes', 'custom_add_book_banner_meta_box');

// Callback function to display meta box contents
function custom_book_banner_meta_box_callback($post) {
  // Add nonce for security and authentication
  wp_nonce_field('custom_book_banner_meta_box', 'custom_book_banner_meta_box_nonce');

  // Get existing values for meta fields
  $book_banner_title = get_post_meta($post->ID, '_book_banner_title', true);
  $book_banner_subheading = get_post_meta($post->ID, '_book_banner_subheading', true);
  $book_banner_content = get_post_meta($post->ID, '_book_banner_content', true);

  // Output fields
  echo '<p><label for="book_banner_title">Book Banner Title:</label><br>';
  echo '<input type="text" id="book_banner_title" name="book_banner_title" value="' . esc_attr($book_banner_title) . '" size="80"></p>';

  echo '<p><label for="book_banner_subheading">Book Banner Subheading:</label><br>';
  echo '<input type="text" id="book_banner_subheading" name="book_banner_subheading" value="' . esc_attr($book_banner_subheading) . '" size="80"></p>';

  echo '<p><label for="book_banner_content">Book Banner Content:</label><br>';
  echo '<textarea id="book_banner_content" name="book_banner_content" rows="5" cols="80">' . esc_textarea($book_banner_content) . '</textarea></p>';
}

// Save meta box content
function custom_save_book_banner_meta_box($post_id) {
  // Check if nonce is set
  if (!isset($_POST['custom_book_banner_meta_box_nonce'])) {
      return;
  }

  // Verify nonce
  if (!wp_verify_nonce($_POST['custom_book_banner_meta_box_nonce'], 'custom_book_banner_meta_box')) {
      return;
  }

  // Check if this is an autosave
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
      return;
  }

  // Check the user's permissions
  if (!current_user_can('edit_post', $post_id)) {
      return;
  }

  // Update or add meta values
  if (isset($_POST['book_banner_title'])) {
      update_post_meta($post_id, '_book_banner_title', sanitize_text_field($_POST['book_banner_title']));
  }
  if (isset($_POST['book_banner_subheading'])) {
      update_post_meta($post_id, '_book_banner_subheading', sanitize_text_field($_POST['book_banner_subheading']));
  }
  if (isset($_POST['book_banner_content'])) {
      update_post_meta($post_id, '_book_banner_content', sanitize_textarea_field($_POST['book_banner_content']));
  }
}
add_action('save_post', 'custom_save_book_banner_meta_box');
