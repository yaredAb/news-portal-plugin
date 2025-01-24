<?php

class Author_Dashboard {
    
    public function __construct() {
        add_action('admin_menu', [$this, 'register_author_menu']);
    }

    public function register_author_menu () {
        add_menu_page(
            'Author Dashboard',//page title
            'Author_Dashboard',//Menu title
            'edit_posts', //capablity
            'author-dashboard',//menu-slug
            [$this, 'display_author_dashboard'],//callback function
            'dashicons-welcome-write-blog'//icon
        );
  
    }

    public function display_author_dashboard() {
            echo '<h1>Author Dashboard</h1>';
    }
}
new Author_Dashboard();