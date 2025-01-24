<?php

class HelperFunction {
    public static function upload_file($file) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
    
        $upload = wp_handle_upload($file, ['test_form' => false]);
        if (!isset($upload['error']) && isset($upload['file'])) {
            $filetype = wp_check_filetype($upload['file']);
            $attachment = [
                'post_mime_type' => $filetype['type'],
                'post_title'     => sanitize_file_name($upload['file']),
                'post_content'   => '',
                'post_status'    => 'inherit',
            ];
            $attachment_id = wp_insert_attachment($attachment, $upload['file']);
            require_once ABSPATH . 'wp-admin/includes/image.php';
            $attach_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
            wp_update_attachment_metadata($attachment_id, $attach_data);
            return $attachment_id;
        }
        return false;
    }

    public static function notify_editor($post_id) {
        $post = get_post($post_id);
        $author_name = get_the_author_meta('display_name', $post->post_author);

        //wordpress notification
        $message = "An article titled {$post->post_title} has been sent for review by {$author_name}";
        $edit_link = admin_url("post.php?post={$post_id}&action=edit");

        $editors = get_users(['role'=>'editor']);
        foreach($editors as $editor) {
            wp_mail($editor->user_email, 'New article submitted for review'.'\n\nyou can see it here', $message, $edit_link);
        }
    }

}