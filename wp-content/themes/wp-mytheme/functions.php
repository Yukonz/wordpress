<?php

add_theme_support('menus');
add_theme_support( 'post-thumbnails' );

add_action('after_setup_theme', function()
{
    register_nav_menus( array(
        'main_menu' => 'Main menu',
        'secondary_menu' => 'Secondary menu'
    ) );
});


function post_types_register()
{
    $postTypes[] = array('title' => 'post_thumbnails', 'args' => array(
        'labels' => array('name' => 'Post with Thumbnails',),
        'public' => true,
        'menu_position' => 15,
        'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
        'taxonomies' => array(''),
        'has_archive' => true
    ));

    $postTypes[] = array('title' => 'post_comments', 'args' => array(
        'labels' => array('name' => 'Post with Comments',),
        'public' => true,
        'menu_position' => 16,
        'supports' => array('title', 'editor', 'comments', 'custom-fields'),
        'taxonomies' => array(''),
        'has_archive' => true
    ));

    $postTypes[] = array('title' => 'post_hierarchical', 'args' => array(
        'labels' => array('name' => 'Hierarchical Post',),
        'public' => true,
        'menu_position' => 17,
        'supports' => array('title', 'editor', 'excerpts', 'custom-fields'),
        'taxonomies' => array(''),
        'has_archive' => true
    ));

    foreach ($postTypes as $postType) {
        register_post_type($postType['title'], $postType['args']);
    }
}
add_action( 'init', 'post_types_register' );


function new_excerpt_length($length)
{
    return 5;
}
add_filter('excerpt_length', 'new_excerpt_length');


add_action( 'widgets_init', 'register_my_widgets' );
function register_my_widgets()
{
    register_sidebar( array(
            'name' => 'Main Sidebar',
            'id'   => 'sidebar_1'
    ) );
}


function hi_shortcode()
{
    return 'Hi! I am from shortcode!';
}
add_shortcode('hi', 'hi_shortcode');


function theme_styles()
{
    wp_enqueue_style( 'bootstrap_min_css', get_template_directory_uri() . '/css/bootstrap.min.css' );
    wp_enqueue_style( 'bootstrap' );
    wp_enqueue_style( 'main_css', get_template_directory_uri() . '/style.css' );
}
add_action( 'wp_enqueue_scripts', 'theme_styles');

function theme_scripts()
{
    wp_enqueue_script('jquery');

    wp_enqueue_script( 'jquery_min_js', get_template_directory_uri() . '/js/jquery.min.js' );
    wp_enqueue_script( 'tether_min_js', get_template_directory_uri() . '/js/tether.min.js' );
    wp_enqueue_script( 'bootstrap_min_js', get_template_directory_uri() . '/js/bootstrap.min.js' );
    wp_enqueue_script( 'main_js', get_template_directory_uri() . '/js/main.js' );

    $message = do_shortcode('[hi]');
    wp_localize_script( 'main_js', 'alertMessage', $message );
}
add_action( 'wp_enqueue_scripts', 'theme_scripts');


