<?php
/**
 * Plugin Name: WP Link Redirect Track
 * Description: Creates trackable redirect pages that fire GA4 events before redirecting.
 * Version: 0.0.2
 * Author: @my-code-library
 */
 
if (!defined('ABSPATH')) exit;

/**
 * 1. REGISTER CUSTOM POST TYPE: "pj_redirect"
 */
function pj_register_redirect_cpt() {
    register_post_type('pj_redirect', array(
        'labels' => array(
            'name' => 'Redirects',
            'singular_name' => 'Redirect',
            'add_new_item' => 'Add New Redirect',
            'edit_item' => 'Edit Redirect'
        ),
        'public' => false,
        'show_ui' => true,
        'menu_icon' => 'dashicons-randomize',
        'supports' => array('title'),
    ));
}
add_action('init', 'pj_register_redirect_cpt');


/**
 * 2. ADD META BOX FOR DESTINATION URL + LABEL
 */
function pj_redirect_meta_box() {
    add_meta_box(
        'pj_redirect_meta',
        'Redirect Settings',
        'pj_redirect_meta_callback',
        'pj_redirect',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'pj_redirect_meta_box');

function pj_redirect_meta_callback($post) {
    $url   = get_post_meta($post->ID, '_pj_url', true);
    $label = get_post_meta($post->ID, '_pj_label', true);

    echo '<label>Destination URL:</label><br>';
    echo '<input type="text" name="pj_url" value="' . esc_attr($url) . '" style="width:100%;"><br><br>';

    echo '<label>GA4 Event Label:</label><br>';
    echo '<input type="text" name="pj_label" value="' . esc_attr($label) . '" style="width:100%;">';
}

function pj_save_redirect_meta($post_id) {
    if (array_key_exists('pj_url', $_POST)) {
        update_post_meta($post_id, '_pj_url', sanitize_text_field($_POST['pj_url']));
    }
    if (array_key_exists('pj_label', $_POST)) {
        update_post_meta($post_id, '_pj_label', sanitize_text_field($_POST['pj_label']));
    }
}
add_action('save_post', 'pj_save_redirect_meta');


/**
 * 3. CREATE FRONT-END ROUTE: /go/{slug}
 */
function pj_add_rewrite_rule() {
    add_rewrite_rule('^go/([^/]+)/?$', 'index.php?pj_redirect_slug=$matches[1]', 'top');
}
add_action('init', 'pj_add_rewrite_rule');

function pj_add_query_vars($vars) {
    $vars[] = 'pj_redirect_slug';
    return $vars;
}
add_filter('query_vars', 'pj_add_query_vars');


/**
 * 4. TEMPLATE REDIRECT HANDLER
 * Fires GA4 event + logs click + redirects user
 */
function pj_redirect_template() {
    $slug = get_query_var('pj_redirect_slug');
    if (!$slug) return;

    $post = get_page_by_path($slug, OBJECT, 'pj_redirect');
    if (!$post) return;

    $url   = get_post_meta($post->ID, '_pj_url', true);
    $label = get_post_meta($post->ID, '_pj_label', true);

    // Log click
    pj_log_click($post->ID);

    // Output GA4 + redirect
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Redirecting…</title></head><body>';

    echo '<script>
        if (typeof gtag === "function") {
            gtag("event", "outbound_click", {
                event_category: "Redirect",
                event_label: "' . esc_js($label) . '",
                destination: "' . esc_js($url) . '"
            });
        }
        setTimeout(function() {
            window.location.href = "' . esc_url($url) . '";
        }, 150);
    </script>';

    echo '<p>Redirecting…</p>';
    echo '</body></html>';
    exit;
}
add_action('template_redirect', 'pj_redirect_template');


/**
 * 5. CLICK LOGGING (stores in wp_postmeta)
 */
function pj_log_click($post_id) {
    $count = get_post_meta($post_id, '_pj_clicks', true);
    $count = $count ? intval($count) + 1 : 1;
    update_post_meta($post_id, '_pj_clicks', $count);
}


/**
 * 6. SHOW CLICK COUNT IN ADMIN COLUMN
 */
function pj_redirect_columns($columns) {
    $columns['pj_clicks'] = 'Clicks';
    return $columns;
}
add_filter('manage_pj_redirect_posts_columns', 'pj_redirect_columns');

function pj_redirect_column_content($column, $post_id) {
    if ($column === 'pj_clicks') {
        echo intval(get_post_meta($post_id, '_pj_clicks', true));
    }
}
add_action('manage_pj_redirect_posts_custom_column', 'pj_redirect_column_content', 10, 2);
