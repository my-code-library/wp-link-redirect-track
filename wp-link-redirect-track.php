<?php
/**
 * Plugin Name: WP Link Redirect Track
 * Description: Creates trackable redirect pages that fire GA4 events before redirecting.
 * Version: 0.0.3
 * Author: @my-code-library
 *
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
