<?php
/*
Plugin Name: Polylang Sitemap Test
Description: Plugin to fix Polylang and Yoast SEO compatibility
Version: 1.0
Requires at least: 5.8
Requires PHP: 5.6.20
Author: Serhii Shumakov
License: GPLv2 or later
Text Domain: polylang-sitemap-test
*/

/**
 * Register custom post types
 */
include plugin_dir_path(__FILE__) . "./custom-post-type-archive.php";
include plugin_dir_path(__FILE__) . "./custom-post-type-review.php";

class PolylangTest {

    public function init() {
        add_action(
            'plugins_loaded',
            function() {
                // Run when Polylang and YoastSEO activated
                if ( class_exists( 'Polylang' ) && class_exists('WPSEO_Options') ) {
                    // Update wpseo_posts_where to show only current language posts
                    add_action('after_setup_theme', array( $this, 'update_polylang_seo_query' ), 5, 2);
                    /* Exclude post types from Yoast SEO Sitemap */
                    add_filter('wpseo_sitemap_exclude_post_type', array( $this, 'exclude_post_types' ), 10, 2 );
                    // Add new rewrite rules for sitemaps
                    add_filter('pll_rewrite_rules', array( $this, 'pll_rewrite_rules' ) );
                    add_filter('generate_rewrite_rules', array( $this, 'rewrite_rules') );
                    // Add current language code to Yoast SEO sitemap links if needed
                    add_filter('wpseo_sitemaps_base_url', array( $this, 'sitemaps_base_url') );
                    // Redirect to sitemap index if wrong language in url
                    add_action('parse_query', array( $this, 'sitemap_index_redirect' ), 5);
                }
            },
            10
        );
    }

    public function remove_object_callback_hook( $hook = '', $obj_name = '' ) {
        global $wp_filter;

        if( empty($obj_name) || empty($hook) || !isset($wp_filter[$hook]) )
            return;

        foreach( $wp_filter[$hook]->callbacks as $key => $item ) {
            $wp_filter[$hook]->callbacks[$key] = array_filter(
                $wp_filter[$hook]->callbacks[$key],
                function( $callback ) use ($obj_name) {

                    $function = $callback['function'];
                    if( is_array( $function )
                        && is_object( $function[0] )
                        && get_class( $function[0] ) === $obj_name
                    ) return FALSE;

                    return TRUE;
                }
            );
        }
    }

    public function polylang_seo_posts_where_hotfix($sql, $post_type) {
        if ( ! pll_is_translated_post_type( $post_type ) ) {
            return $sql;
        }

        if ( PLL()->options['force_lang'] > 1 && PLL()->curlang instanceof PLL_Language ) {
            return $sql . PLL()->model->post->where_clause( PLL()->curlang );
        }

        $languages = PLL()->model->get_languages_list();
        $languages = wp_list_pluck(
            wp_list_filter(
                $languages,
                array(
                    'active' => TRUE,
                    'slug'  => pll_current_language('slug')
                ),
                'AND'
            ),
            'slug'
        );

        return $sql . PLL()->model->post->where_clause( $languages );
    }

    public function pll_rewrite_rules( $rules ) {
        return array_merge( $rules, array( "sitemap" ) );
    }

    public function sitemaps_base_url( $base_url ) {
        return str_replace( home_url(), '/', pll_home_url() );
    }

    public function sitemap_index_redirect() {
        if(
            empty( get_query_var('sitemap') )
            && empty( get_query_var('sitemap_n') )
            && empty( get_query_var('yoast-sitemap-xsl') )
        ) return;

        $query_lang = get_query_var('lang');

        if( empty( $query_lang ) ) return;

        if( $query_lang !== pll_current_language() || $query_lang === pll_default_language() ) {
            wp_safe_redirect( home_url( '/sitemap_index.xml' ), 301 );
            exit;
        }
    }

    public function exclude_post_types( $value, $post_type ) {
        return $post_type === 'archive';
    }

    public function update_polylang_seo_query() {
        $this->remove_object_callback_hook('wpseo_posts_where', 'PLL_WPSEO');
        add_filter('wpseo_posts_where', array( $this, 'polylang_seo_posts_where_hotfix' ), 10, 2);
    }

    public function rewrite_rules( $wp_rewrite ) {
        $rules = array(
            '([a-z]+)/sitemap_index\.xml$' => 'index.php?lang=$matches[1]&sitemap=1',
            '([a-z]+)/([^/]+?)-sitemap([0-9]+)?\.xml$' => 'index.php?lang=$matches[1]&sitemap=$matches[2]&sitemap_n=$matches[3]',
            '([a-z]+)/([a-z]+)?-?sitemap\.xsl$' => 'index.php?lang=$matches[1]&yoast-sitemap-xsl=$matches[2]'
        );
        $wp_rewrite->rules = array_merge( $rules, $wp_rewrite->rules );
    }

}

$PolylangTest = new PolylangTest();
$PolylangTest->init();
