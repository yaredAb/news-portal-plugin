<?php
/* 
Plugin Name: News Portal
Version: 1.0.0
Author: Yared Sebsbe
*/

if(!defined('ABSPATH')){
    exit;
}

define ('NEWS_PORTAL_PLUGIN_PATH', plugin_dir_path( __FILE__ ));
define ('NEWS_PORTAL_PLUGIN_URL', plugin_dir_url( __FILE__ ));


require_once NEWS_PORTAL_PLUGIN_PATH.'/includes/class-author-dashboard.php';
require_once NEWS_PORTAL_PLUGIN_PATH.'/includes/class-editor-dashboard.php';
require_once NEWS_PORTAL_PLUGIN_PATH.'/includes/class-notifications.php';
require_once NEWS_PORTAL_PLUGIN_PATH.'/includes/class-analytics.php';
require_once NEWS_PORTAL_PLUGIN_PATH.'/includes/helper-functions.php';

function news_portal_assets() {
    wp_enqueue_style(
        'news-portal-styles',
        NEWS_PORTAL_PLUGIN_URL.'/asstes/css/style.css',
        [],
        '1.0.0'
    );

    wp_enqueue_script(
        'news-portal-scripts',
        NEWS_PORTAL_PLUGIN_URL.'/assets/javascript/script.js',
        ['jquery'],
        '1.0.0'
    );
}
add_action('admin_enqueue_scripts', 'news_portal_assets');