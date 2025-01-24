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
        echo '<div class="wrap">';
        echo '<h1>Your Articles</h1>';
        $this->list_author_articles();
        echo '</div>';
    }

    public function list_author_articles() {
        $user_id = get_current_user_id();
        $args = [
            'author'=>$user_id,
            'post_type' => 'post',
            'post_status' => ['draft', 'publish', 'pending', 'future'],
        ];
        $query = new WP_Query($args);
        if($query->have_posts()) {
            echo '<table class="widefat fixed">';
            echo '<thead><tr><th>Title</th><th>Status</th><th>Date</th></tr></thead><tbody>';
            while($query->have_posts()) {
                $query->the_post();
                echo '<tr>';
                echo '<td>'.get_the_title().'</tr>';
                echo '<td>'.get_post_status().'</tr>';
                echo '<td>'.get_the_date().'</tr>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        }else{
            echo '<p>No articles</p>';
        }
        wp_reset_postdata();
    }
}
new Author_Dashboard();