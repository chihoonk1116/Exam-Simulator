<?php

function simulator_post_type(){
    //exam post type
    register_post_type('exam',array(
        'show_in_rest' => true,
        'supports' => array('title', 'editor'),
        'rewrite' => array('slug' => 'exams'),
        'public' => true,
        'labels' => array(
            'name' => 'Exams',
            'add_new' => 'Add New Exam',
            'edit_item' => 'Edit exam',
            'all_items' => 'All exams',
            'singular_name' => 'exam'
        ),
        'menu_icon' => 'dashicons-welcome-write-blog'
    ));

    //question post type
    register_post_type('question',array(
        'show_in_rest' => true,
        'supports' => array('title', 'editor'),
        'rewrite' => array('slug' => 'questions'),
        'public' => true,
        'labels' => array(
            'name' => 'Questions',
            'add_new' => 'Add New question',
            'edit_item' => 'Edit question',
            'all_items' => 'All questions',
            'singular_name' => 'question'
        ),
        'menu_icon' => 'dashicons-edit'
    ));

    //exam-student joint post type : many to many
    register_post_type('assigned_exam',array(
        'show_in_rest' => true,
        'supports' => array('title'),
        'rewrite' => array('slug' => 'assigned-exams'),
        'public' => true,
        'labels' => array(
            'name' => 'Assigned Exams',
            'add_post' => 'Add New Assigned Exam',
            'edit_item' => 'Edit Assigned Exam',
            'all_items' => 'All Assigned Exams',
            'singular_name' => 'Assigned Exam'
        ),
        'menu_icon' => 'dashicons-yes'
    ));
}

add_action('init', 'simulator_post_type');