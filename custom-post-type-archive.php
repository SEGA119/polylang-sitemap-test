<?php

add_action('init', function() {
    $labels = array(
        'name' => 'Archive',
        'singular_name' => 'Archive',
        'menu_name' => 'Archives',
        'name_admin_bar' => 'Archive',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New archive',
        'new_item' => 'New archive',
        'edit_item' => 'Edit archive',
        'view_item' => 'View archive',
        'all_items' => 'All archives',
        'search_items' => 'Search archives',
        'parent_item_colon' => 'Parent archives:',
        'not_found' => 'No archives found.',
        'not_found_in_trash' => 'No archives found in Trash.'
    );

    $args = array(
        'labels' => $labels,
        'description' => 'Archive',
        'public' => TRUE,
        'publicly_queryable' => TRUE,
        'show_ui' => TRUE,
        'show_in_menu' => TRUE,
        'query_var' => TRUE,
        'rewrite' => [
            'slug' => 'archive',
            'with_front' => TRUE,
        ],
        'capability_type' => 'post',
        'has_archive' => FALSE,
        'hierarchical' => FALSE,
        'menu_position' => NULL,
        'show_in_rest' => FALSE,
        'supports' => array('title')
    );

    register_post_type('archive', $args);
});