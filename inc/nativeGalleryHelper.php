<?php

add_filter('wp_get_attachment_link',
    /**
     * Filter a retrieved attachment page link.
     *
     * @since 2.7.0
     *
     * @param string      $link_html The page link HTML output.
     * @param int         $id        Post ID.
     * @param string      $size      Image size. Default 'thumbnail'.
     * @param bool        $permalink Whether to add permalink to image. Default false.
     * @param bool        $icon      Whether to include an icon. Default false.
     * @param string|bool $text      If string, will be link text. Default false.
     */
    function ($link_html, $post_id, $size, $permalink, $icon, $text) {
        $src        = wp_get_attachment_image_src($post_id, 'full');
        $dimensions = sprintf('%dx%d', $src[1], $src[2]);
        return str_replace('<a ', sprintf('<a data-size="%s" ', $dimensions), $link_html);
    }, 10, 6);