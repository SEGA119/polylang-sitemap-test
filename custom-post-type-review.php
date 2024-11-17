<?php

add_action('init', function() {
    $labels = array(
        'name' => 'Review',
        'singular_name' => 'Review',
        'menu_name' => 'Locations',
        'name_admin_bar' => 'Review',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New review',
        'new_item' => 'New review',
        'edit_item' => 'Edit review',
        'view_item' => 'View review',
        'all_items' => 'All reviews',
        'search_items' => 'Search reviews',
        'parent_item_colon' => 'Parent reviews:',
        'not_found' => 'No reviews found.',
        'not_found_in_trash' => 'No reviews found in Trash.'
    );

    $args = array(
        'labels' => $labels,
        'description' => 'Review',
        'public' => TRUE,
        'publicly_queryable' => TRUE,
        'show_ui' => TRUE,
        'show_in_menu' => TRUE,
        'query_var' => TRUE,
        'rewrite' => [
            'slug' => 'review',
            'with_front' => TRUE,
        ],
        'capability_type' => 'post',
        'has_archive' => FALSE,
        'hierarchical' => FALSE,
        'menu_position' => NULL,
        'show_in_rest' => FALSE,
        'supports' => array('title')
    );

    register_post_type('review', $args);
});