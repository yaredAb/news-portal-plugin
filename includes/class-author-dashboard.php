<?php

require_once NEWS_PORTAL_PLUGIN_PATH.'/includes/helper-functions.php';
class Author_Dashboard {
    
    public function __construct() {
        add_action('admin_menu', [$this, 'register_author_menu']);
        add_action('admin_init', [$this, 'handleArticleSubmission']);
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
        echo '<h1>Create a new article</h1>';
        $this->article_creation_form();
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
                echo '<td>'.get_the_title().'</td>';
                echo '<td>'.get_post_status().'</td>';
                echo '<td>'.get_the_date().'</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        }else{
            echo '<p>No articles</p>';
        }
        wp_reset_postdata();
    }

    public function article_creation_form() {
        echo '<form method="post" action="" enctype="multipart/form-data">';
        echo '<input type="hidden" name="action" value="submit_article">';

        //the title
        echo '<p><label for="article-title" >Title:</label>';
        echo '<input type="text" id="article-title" name="article-title" required>';
        echo '</p>';

        //the content
        echo '<p><label for="article-content" >Content:</label>';
        wp_editor('', 'article-content', ['textarea'=>'article-content']);
        echo '</p>';

        // Featured Image
        echo '<p><label for="article-featured-image">Featured Image:</label><br>';
        echo '<input type="file" id="article-featured-image" name="article-featured-image" accept="image/*"></p>';

        // Media Uploads
        echo '<p><label for="article-media">Media File(Images, Videos, Audio):</label><br>';
        echo '<input type="file" id="article-media" name="article-media[]" multiple accept="image/*,video/*,audio/*"></p>';

        // Categories
        echo '<p><label for="article-category">Category:</label><br>';
        wp_dropdown_categories(['name' => 'article-category', 'hide_empty' => false]);
        echo '</p>';
        
        // Tags
        echo '<p><label for="article-tags">Tags (comma-separated):</label><br>';
        echo '<input type="text" id="article-tags" name="article-tags" placeholder="e.g., news, politics"></p>';
        
        // Submit Button
        echo '<p><input type="submit" name="submit-article" value="Create Article" class="button button-primary"></p>';
        echo '</form>';
    }

    public function handleArticleSubmission() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit-article'])) {
            // Sanitize inputs
            $article_title = sanitize_text_field($_POST['article-title']);
            $article_content = wp_kses_post($_POST['article-content']);
            $article_category = intval($_POST['article-category']);
            $article_tags = sanitize_text_field($_POST['article-tags']);
            $user_id = get_current_user_id();
    
            // Create the post
            $post = [
                'post_title'   => $article_title,
                'post_content' => $article_content,
                'post_status'  => 'draft',
                'post_type'    => 'post',
                'post_author'  => $user_id,
                'post_category' => [$article_category],
            ];
    
            $post_id = wp_insert_post($post);
    
            if ($post_id) {
                // Add tags
                wp_set_post_tags($post_id, $article_tags);
    
                // Handle featured image
                if (!empty($_FILES['article-featured-image']['name'])) {
                    $featured_image_id = HelperFunction::upload_file($_FILES['article-featured-image']);
                    if ($featured_image_id) {
                        set_post_thumbnail($post_id, $featured_image_id);
                    }
                }
    
                // Handle media uploads
                if (!empty($_FILES['article-media']['name'][0])) {
                    foreach ($_FILES['article-media']['name'] as $key => $media_name) {
                        $media_file = [
                            'name'     => $_FILES['article-media']['name'][$key],
                            'type'     => $_FILES['article-media']['type'][$key],
                            'tmp_name' => $_FILES['article-media']['tmp_name'][$key],
                            'error'    => $_FILES['article-media']['error'][$key],
                            'size'     => $_FILES['article-media']['size'][$key],
                        ];
                        $attachment_id = HelperFunction::upload_file($media_file);
                        if ($attachment_id) {
                            add_post_meta($post_id, 'attached_media', $attachment_id);
                        }
                    }
                }
    
                // Redirect with a success message
                wp_safe_redirect(admin_url('admin.php?page=author-dashboard&success=1'));
                exit;
            }
        }
    }    
}
new Author_Dashboard();