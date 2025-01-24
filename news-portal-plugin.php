<?php
/* 
Plugin Name: News Portal
Version: 1.0.0
Author: Yared Sebsbe
*/

if(!defined('ABSPATH')){
    exit;
}

function news_portal_plugin_init() {
    add_action('admin_footer', function() {
        echo '<p>News Portal Plugin is Active</p>';
    });
}

add_action('plugins_loaded', 'news_portal_plugin_init');
