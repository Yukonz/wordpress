<?php

/*
Plugin Name: WP My Plugin
Description: My first plugin
Version: 1.0
Author: Viktor.G
*/

register_activation_hook(__FILE__, 'wp_myplugin_activation');
register_deactivation_hook(__FILE__, 'wp_myplugin_deactivation');
register_uninstall_hook(__FILE__, 'wp_myplugin_uninstall');


function wp_myplugin_activation()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'myplugin';
    $sql = "CREATE TABLE {$table_name} (PluginID int)";
    $wpdb->query($sql);
}

function wp_myplugin_uninstall()
{
    wp_myplugin_deactivation();
}

function wp_myplugin_deactivation()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'myplugin';
    $sql = "DROP TABLE IF EXISTS {$table_name}";
    $wpdb->query($sql);
}

add_filter( 'the_title', 'title_to_div');
function title_to_div($title)
{
    $title = '<div class="filtered" style="color: red">' . $title . '</div>';
    return $title;
}

function plugin_admin_assets()
{
    wp_enqueue_style( 'admin_css', plugin_dir_url( __FILE__ ) . '/css/admin.css' );
    wp_enqueue_script( 'admin_js', plugin_dir_url( __FILE__ ) . '/js/admin-functions.js' );
}
add_action( 'admin_enqueue_scripts', 'plugin_admin_assets');

function plugin_assets()
{
    wp_enqueue_style( 'style_css', plugin_dir_url( __FILE__ ) . '/css/style.css' );
    wp_enqueue_script( 'main_js', plugin_dir_url( __FILE__ ) . '/js/functions.js' );
}
add_action( 'wp_enqueue_scripts', 'plugin_assets');
