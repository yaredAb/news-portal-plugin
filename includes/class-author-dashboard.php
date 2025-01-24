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
        if(isset($_GET['edit'])){
            $post_id = intval($_GET['edit']);
            $this->edit_article_form($post_id);
        }else{
        echo '<h1>Your Articles</h1>';
        $this->list_author_articles();
        echo '<h1>Create a new article</h1>';
        $this->article_creation_form();
        }
        echo '</div>';

    }

    public function list_author_articles() {
        
        $user_id = get_current_user_id();
        
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $date_filter = isset($_GET['date']) ? sanitize_text_field($_GET['date']) : '';


        $args = [
            'author'=>$user_id,
            'post_type' => 'post',
            'post_status' => $status_filter ? [$status_filter] : ['draft', 'publish', 'pending', 'future'],
            'date_query' => $date_filter ? [['after'=>$date_filter]] : []
        ];
        $query = new WP_Query($args);

        //status filter
        echo '<form method="get" action="">';
        echo '<input type="hidden" name="page" value="author-dashboard">';
        echo '<select name="status">';
        echo '<option value="">All Statuses</option>';
        $statuses = ['draft', 'publish', 'pending', 'future'];
        foreach($statuses as $status){
            echo '<option value="'.esc_attr($status).'"'.selected($status_filter, $status, false).'>'.ucfirst($status).'</option>';
        }
        echo '</select>';

        echo '<input type="date" name="date" value="'.esc_attr($date_filter).'">';
        echo '<button type="submit" class="button">Filter</button>';
        echo '</form>';

        //displaying articles
        if($query->have_posts()) {
            echo '<table class="widefat fixed">';
            echo '<thead><tr><th>Title</th><th>Status</th><th>Date</th><th></th></tr></thead><tbody>';
            while($query->have_posts()) {
                $query->the_post();
                echo '<tr>';
                echo '<td>'.get_the_title().'</td>';
                echo '<td>'.get_post_status().'</td>';
                echo '<td>'.get_the_date().'</td>';
                echo '<td> <a href='.admin_url('admin.php?page=author-dashboard&edit='.get_the_ID()).'">'.get_the_title().'</a></td>';
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
        wp_nonce_field('article_submission', '_wpnonce');
        echo '<input type="hidden" name="action" value="submit_article">';

        //the title
        echo '<p><label for="article-title" >Title:</label>';
        echo '<input type="text" id="article-title" name="article-title" required>';
        echo '</p>';

        //the content
        echo '<p><label for="article-content" >Content:</label>';
        wp_editor('', 'article-content', ['textarea_name'=>'article-content']);
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

    public function edit_article_form($post_id) {
        if (!$post_id) {
            echo "<p>Invalid post ID.</p>";
            return;
        }
        $post = get_post($post_id);

        if($post->post_author != get_current_user_id()){
            echo "<p>You are not allowed to edit this article</p>";
            echo "post author: {$post->post_author}";
            echo "current user".get_current_user_id();
            return;
        }
        echo '<h1>Edit Article Form</h1>';
        echo '<form method="post" action="" enctype="multipart/form-data">';
        wp_nonce_field('article_submission', '_wpnonce');
        echo '<input type="hidden" name="action" value="update_article">';
        echo '<input type="hidden" name="post_id" value="' . esc_attr($post_id) . '">';

        // Title
        echo '<p><label for="article-title">Title:</label>';
        echo '<input type="text" id="article-title" name="article-title" value="' . esc_attr($post->post_title) . '" required>';
        echo '</p>';

        // Content
        echo '<p><label for="article-content">Content:</label>';
        wp_editor($post->post_content, 'article-content', ['textarea_name' => 'article-content']);
        echo '</p>';

        // Featured Image (optional, not pre-filled)
        echo '<p><label for="article-featured-image">Featured Image:</label><br>';
        echo '<input type="file" id="article-featured-image" name="article-featured-image" accept="image/*"></p>';

        // Categories
        echo '<p><label for="article-category">Category:</label><br>';
        wp_dropdown_categories(['name' => 'article-category', 'selected' => $post->post_category[0], 'hide_empty' => false]);
        echo '</p>';

        // Tags
        $tags = implode(', ', wp_get_post_tags($post_id, ['fields' => 'names']));
        echo '<p><label for="article-tags">Tags (comma-separated):</label><br>';
        echo '<input type="text" id="article-tags" name="article-tags" value="' . esc_attr($tags) . '">';
        echo '</p>';

        // Submit Buttons
        echo '<p>';
        echo '<input type="submit" name="update-article" value="Update Article" class="button button-primary">';
        echo '<input type="submit" name="submit-review" value="Submit for Review" class="button">';
        echo '</p>';
        echo '</form>';
    }

    public function handleArticleSubmission() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Verify the nonce for security
            if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'article_submission')) {
                wp_die('Invalid nonce verification.');
            }
    
            // Check for action
            if (isset($_POST['action'])) {
                $action = sanitize_text_field($_POST['action']);
    
                if ($action === 'submit_article') {
                    $this->createArticle();
                } elseif ($action === 'update_article') {
                    $this->updateArticle();
                }
            }
        }
    }
    
    private function createArticle() {
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
    
    private function updateArticle() {
        // Sanitize inputs
        $post_id = intval($_POST['post_id']);
        $article_title = sanitize_text_field($_POST['article-title']);
        $article_content = wp_kses_post($_POST['article-content']);
        $article_category = intval($_POST['article-category']);
        $article_tags = sanitize_text_field($_POST['article-tags']);
        $status = isset($_POST['submit-review']) ? 'pending' : 'draft';
    
        // Ensure the current user is the author of the post
        $post = get_post($post_id);
        if ($post->post_author != get_current_user_id()) {
            wp_die('You are not authorized to edit this article.');
        }
    
        // Update the post
        $post_data = [
            'ID'           => $post_id,
            'post_title'   => $article_title,
            'post_content' => $article_content,
            'post_status'  => $status,
            'post_category' => [$article_category],
        ];
        wp_update_post($post_data);
    
        // Update tags
        wp_set_post_tags($post_id, $article_tags);
    
        // Handle featured image
        if (!empty($_FILES['article-featured-image']['name'])) {
            $featured_image_id = HelperFunction::upload_file($_FILES['article-featured-image']);
            if ($featured_image_id) {
                set_post_thumbnail($post_id, $featured_image_id);
            }
        }
    
        // Redirect
        wp_safe_redirect(admin_url('admin.php?page=author-dashboard&success=1'));
        exit;
    }
    
}
new Author_Dashboard();