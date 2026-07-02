<?php
/**
 * Plugin Name: WP Link Redirect Track
 * Description: Creates trackable redirect pages that fire GA4 events before redirecting.
 * Version: 0.0.4
 * Author: @my-code-library
 */

/**
 * MIT License
 * 
 * Copyright (c) 2026 @my-code-library
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the “Software”), to deal
 * in the Software without restriction, including without limitation the rights  
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell  
 * copies of the Software, and to permit persons to whom the Software is  
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in  
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR  
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,  
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE  
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER  
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING  
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER  
 * DEALINGS IN THE SOFTWARE.
 */

if (!defined('ABSPATH')) exit;

/**
 * Build UTM-tagged URL from base URL and slug.
 *
 * Example:
 *   https://example.com/page
 * becomes:
 *   https://example.com/page?utm_source=threads&utm_medium=social&utm_campaign=hard-to-walk-away
 */
function wplr_build_utm_url($url, $slug) {
    if (empty($url) || empty($slug)) {
        return $url;
    }

    // Default UTM values (can later be moved to settings).
    $utm = array(
        'utm_source'   => 'threads',
        'utm_medium'   => 'social',
        'utm_campaign' => sanitize_title($slug),
    );

    // Preserve existing query args.
    $parsed = wp_parse_url($url);
    $base   = $url;

    if (!empty($parsed['query'])) {
        parse_str($parsed['query'], $existing);
        $utm = array_merge($existing, $utm);
        $base = remove_query_arg(array_keys($existing), $url);
    }

    return add_query_arg($utm, $base);
}

/**
 * TEMPLATE REDIRECT HANDLER
 * Fires GA4 event + logs click + redirects user
 */
function wplr_redirect_template() {
    $slug = get_query_var('wplr_redirect_slug');
    if (!$slug) return;

    $post = get_page_by_path($slug, OBJECT, 'wplr_redirect');
    if (!$post) return;

    $url   = get_post_meta($post->ID, '_wplr_url', true);
    $label = get_post_meta($post->ID, '_wplr_label', true);

    // Apply UTM auto-tagging
    $utm_url = wplr_build_utm_url($url, $slug);

    // Log click
    wplr_log_click($post->ID);

    // Server-side GA4 event
    wplr_send_ga4_server_event($label, $utm_url, $slug);

    // Output GA4 + redirect
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Redirecting…</title></head><body>';

    echo '<script>
        if (typeof gtag === "function") {
            gtag("event", "outbound_click", {
                event_category: "Redirect",
                event_label: "' . esc_js($label) . '",
                destination: "' . esc_js($utm_url) . '"
            });
        }
        setTimeout(function() {
            window.location.href = "' . esc_url($utm_url) . '";
        }, 150);
    </script>';

    echo '<p>Redirecting…</p>';
    echo '</body></html>';
    exit;
}
add_action('template_redirect', 'wplr_redirect_template');

/**
 * Send GA4 Measurement Protocol server-side event.
 *
 * @param string $label
 * @param string $destination
 * @param string $slug
 */
function wplr_send_ga4_server_event($label, $destination, $slug) {

    // TODO: Move these to plugin settings later.
    $measurement_id = 'G-XXXXXXXXXX';
    $api_secret     = 'YOUR_API_SECRET_HERE';

    if (!$measurement_id || !$api_secret) {
        return; // Fail silently if not configured.
    }

    $endpoint = "https://www.google-analytics.com/mp/collect?measurement_id={$measurement_id}&api_secret={$api_secret}";

    $payload = array(
        'client_id' => uniqid(), // Anonymous unique ID
        'events' => array(
            array(
                'name' => 'outbound_click',
                'params' => array(
                    'event_label'   => $label,
                    'destination'   => $destination,
                    'redirect_slug' => $slug,
                    'timestamp'     => time(),
                )
            )
        )
    );

    wp_remote_post($endpoint, array(
        'method'      => 'POST',
        'body'        => wp_json_encode($payload),
        'headers'     => array('Content-Type' => 'application/json'),
        'timeout'     => 3,
    ));
}

/**
 * Register plugin settings
 */
function wplr_register_settings() {

    // GA4
    register_setting('wplr_settings_group', 'wplr_ga4_measurement_id', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('wplr_settings_group', 'wplr_ga4_api_secret', ['sanitize_callback' => 'sanitize_text_field']);

    // UTM defaults
    register_setting('wplr_settings_group', 'wplr_utm_source', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('wplr_settings_group', 'wplr_utm_medium', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('wplr_settings_group', 'wplr_utm_campaign_default', ['sanitize_callback' => 'sanitize_text_field']);

    // Redirect delay (ms)
    register_setting('wplr_settings_group', 'wplr_redirect_delay', ['sanitize_callback' => 'intval']);

    // Toggle server-side tracking
    register_setting('wplr_settings_group', 'wplr_enable_server_tracking', ['sanitize_callback' => 'intval']);
}
add_action('admin_init', 'wplr_register_settings');


/**
 * Add settings page to WP admin
 */
function wplr_add_settings_page() {
    add_options_page(
        'WP Link Redirect Track Settings',
        'WP Link Redirect Track',
        'manage_options',
        'wplr-settings',
        'wplr_render_settings_page'
    );
}
add_action('admin_menu', 'wplr_add_settings_page');


/**
 * Render settings page
 */
function wplr_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>WP Link Redirect Track — Settings</h1>

        <form method="post" action="options.php">
            <?php settings_fields('wplr_settings_group'); ?>

            <h2>GA4 Measurement Protocol</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">GA4 Measurement ID</th>
                    <td><input type="text" name="wplr_ga4_measurement_id" value="<?php echo esc_attr(get_option('wplr_ga4_measurement_id')); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row">GA4 API Secret</th>
                    <td><input type="text" name="wplr_ga4_api_secret" value="<?php echo esc_attr(get_option('wplr_ga4_api_secret')); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row">Enable Server-Side Tracking</th>
                    <td>
                        <label>
                            <input type="checkbox" name="wplr_enable_server_tracking" value="1" <?php checked(get_option('wplr_enable_server_tracking'), 1); ?>>
                            Yes, send server-side GA4 events
                        </label>
                    </td>
                </tr>
            </table>

            <h2>UTM Defaults</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">UTM Source</th>
                    <td><input type="text" name="wplr_utm_source" value="<?php echo esc_attr(get_option('wplr_utm_source', 'threads')); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row">UTM Medium</th>
                    <td><input type="text" name="wplr_utm_medium" value="<?php echo esc_attr(get_option('wplr_utm_medium', 'social')); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row">Default UTM Campaign</th>
                    <td><input type="text" name="wplr_utm_campaign_default" value="<?php echo esc_attr(get_option('wplr_utm_campaign_default')); ?>" class="regular-text">
                        <p class="description">If empty, slug will be used automatically.</p>
                    </td>
                </tr>
            </table>

            <h2>Redirect Behavior</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">Redirect Delay (ms)</th>
                    <td><input type="number" name="wplr_redirect_delay" value="<?php echo esc_attr(get_option('wplr_redirect_delay', 150)); ?>" class="small-text"></td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
