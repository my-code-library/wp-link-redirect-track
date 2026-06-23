<?php
/**
 * Plugin Name: WP Link Redirect Track
 * Description: Creates trackable redirect pages that fire GA4 events before redirecting.
 * Version: 0.0.3
 * License: MIT
 * Author: @my-code-library
 */

/*
MIT License

Copyright (c) 2026 @my-code-library

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the “Software”), to deal
in the Software without restriction, including without limitation the rights  
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell  
copies of the Software, and to permit persons to whom the Software is  
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in  
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR  
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,  
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE  
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER  
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING  
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER  
DEALINGS IN THE SOFTWARE.
*/

if (!defined('ABSPATH')) exit;

/**
 * 1. REGISTER CUSTOM POST TYPE: "wplr_redirect"
 */
function wplr_register_redirect_cpt() {
    register_post_type('wplr_redirect', array(
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
add_action('init', 'wplr_register_redirect_cpt');


/**
 * 2. META BOX FOR DESTINATION URL + LABEL
 */
function wplr_redirect_meta_box() {
    add_meta_box(
        'wplr_redirect_meta',
        'Redirect Settings',
        'wplr_redirect_meta_callback',
        'wplr_redirect',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'wplr_redirect_meta_box');

function wplr_redirect_meta_callback($post) {
    $url   = get_post_meta($post->ID, '_wplr_url', true);
    $label = get_post_meta($post->ID, '_wplr_label', true);

    echo '<label>Destination URL:</label><br>';
    echo '<input type="text" name="wplr_url" value="' . esc_attr($url) . '" style="width:100%;"><br><br>';

    echo '<label>GA4 Event Label:</label><br>';
    echo '<input type="text" name="wplr_label" value="' . esc_attr($label) . '" style="width:100%;">';
}

function wplr_save_redirect_meta($post_id) {
    if (array_key_exists('wplr_url', $_POST)) {
        update_post_meta($post_id, '_wplr_url', sanitize_text_field($_POST['wplr_url']));
    }
    if (array_key_exists('wplr_label', $_POST)) {
        update_post_meta($post_id, '_wplr_label', sanitize_text_field($_POST['wplr_label']));
    }
}
add_action('save_post', 'wplr_save_redirect_meta');


/**
 * 3. FRONT-END ROUTE: /go/{slug}
 */
function wplr_add_rewrite_rule() {
    add_rewrite_rule('^go/([^/]+)/?$', 'index.php?wplr_redirect_slug=$matches[1]', 'top');
}
add_action('init', 'wplr_add_rewrite_rule');

function wplr_add_query_vars($vars) {
    $vars[] = 'wplr_redirect_slug';
    return $vars;
}
add_filter('query_vars', 'wplr_add_query_vars');


/**
 * 4. TEMPLATE REDIRECT HANDLER
 * Fires GA4 event + logs click + redirects user
 */
function wplr_redirect_template() {
    $slug = get_query_var('wplr_redirect_slug');
    if (!$slug) return;

    $post = get_page_by_path($slug, OBJECT, 'wplr_redirect');
    if (!$post) return;

    $url   = get_post_meta($post->ID, '_wplr_url', true);
    $label = get_post_meta($post->ID, '_wplr_label', true);

    // Log click
    wplr_log_click($post->ID);

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
add_action('template_redirect', 'wplr_redirect_template');


/**
 * 5. CLICK LOGGING
 */
function wplr_log_click($post_id) {
    $count = get_post_meta($post_id, '_wplr_clicks', true);
    $count = $count ? intval($count) + 1 : 1;
    update_post_meta($post_id, '_wplr_clicks', $count);
}


/**
 * 6. ADMIN COLUMN: CLICK COUNT
 */
function wplr_redirect_columns($columns) {
    $columns['wplr_clicks'] = 'Clicks';
    return $columns;
}
add_filter('manage_wplr_redirect_posts_columns', 'wplr_redirect_columns');

function wplr_redirect_column_content($column, $post_id) {
    if ($column === 'wplr_clicks') {
        echo intval(get_post_meta($post_id, '_wplr_clicks', true));
    }
}
add_action('manage_wplr_redirect_posts_custom_column', 'wplr_redirect_column_content', 10, 2);
