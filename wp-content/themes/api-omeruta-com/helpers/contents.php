<?php

/**
 * A Contents class containing functions that can be used for generating content particles response
 **/

class HCMS_Contents
{
   

    // Function to format post data
    public static function format_post_data($post) {
        $featured_image = get_the_post_thumbnail_url($post->ID, 'full');
        return array(
            'id' => $post->ID,
            'title' => $post->post_title,
            'postContent' => $post->post_content,
            'excerpt' => get_the_excerpt($post),
            'date' => get_the_date('c', $post),
            'modified' => get_the_modified_date('c', $post),
            'slug' => $post->post_name,
            'image' => $featured_image ?: null,
            'categories' => wp_get_post_categories($post->ID, array('fields' => 'names')),
            'tags' => wp_get_post_tags($post->ID, array('fields' => 'names')),
            'author' => get_the_author_meta('display_name', $post->post_author),
            'type' => $post->post_type, //distinguish between posts and pages
        );
    }
}
