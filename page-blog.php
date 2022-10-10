<?php

global $paged;
if (!isset($paged) || !$paged) {
    $paged = 1;
}

$context = Timber::context();

$args = array(
    'post_type' => 'post',
    'orderby' => 'ID',
    'order' => 'DESC',
    'posts_per_page' => '9',
    'post_status' => 'publish',
    'perm' => 'readable',
    'paged' => $paged
);

$context['posts'] = new Timber\PostQuery($args);
$timber_post = new Timber\Post();
$context['post'] = $timber_post;

Timber::render(array('page-' . $timber_post->post_name . '.twig', 'page.twig'), $context);