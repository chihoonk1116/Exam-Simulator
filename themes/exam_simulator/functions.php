<?php

require_once get_template_directory() . '/inc/Manage_Exam_API.php';
require_once get_template_directory() . '/inc/Assign_Exam_API.php';

new Assign_Exam_API();
new Manage_Exam_API();
//after delete a question post, have to disconnect the relation between exam and question
add_action('wp_trash_post', 'delete_question_in_the_exam');
function delete_question_in_the_exam($post_id){
    if(get_post_type($post_id) !== 'question') return;
    
    $related_exams = new WP_Query(array(
        'posts_per_page' => -1,
        'post_type' => 'exam',
        'meta_query' => [
            [
                'key' => 'related_questions',
                'compare' => 'LIKE',
                'value' => '"' . $post_id . '"'
            ]
        ]
    ));

    //update the field and content of all exams that have the deleted question
    while($related_exams->have_posts()){
        $related_exams->the_post();
        $examId = get_the_ID();
        $questions = get_field('related_questions', $examId);
        if(!empty($questions) && is_array($questions)){
            //update custom field : related_question 
            $questions = array_map(function($q) use ($post_id){
                if($q->ID != $post_id){
                    return $q->ID;
                }
            }, $questions);
            
            update_field('related_questions', $questions, $examId);

            //modify exam's content
            $get_question_blocks = get_filtered_blocks($examId, 'custom/question-block');
            $filtered_blocks_by_id = array_filter($get_question_blocks, function($block) use ($post_id){
                return intval($block['attrs']['questionId']) !== $post_id;
            });

            $new_content = '';
            foreach($filtered_blocks_by_id as $block){
                $new_content .= serialize_block($block);
            }

            wp_update_post([
                'ID' => $examId,
                'post_content' => $new_content
            ]);
            
        }
    }
    wp_reset_postdata();
}



add_action('acf/save_post', 'sync_exam_questions_to_acf_field', 20);
// add_action('save_post_exam', 'sync_exam_questions_to_acf_field', 100);
function sync_exam_questions_to_acf_field($post_id): void{
    if(get_post_type($post_id) !== 'exam' || !is_admin()) return;
    if(defined('DOING_AJAX') && DOING_AJAX) return;
    if(defined('REST_REQUEST') && REST_REQUEST) return;

    $blocks = get_filtered_blocks($post_id, 'custom/question-block');

    $questions_ids = [];

    foreach($blocks as $block){
        if(isset($block['attrs']['questionId'])){
            $questions_ids[] = intval($block['attrs']['questionId']);
        }
    }
    
    update_field('related_questions', $questions_ids, $post_id);
}


// add custom field to question post
add_action('rest_api_init', 'add_custom_field_to_rest');
function add_custom_field_to_rest(){
    register_rest_field('question', 'answers', array(
        'get_callback' => function(){return get_field('answers', get_the_ID());}
    ));
    register_rest_field('question', 'correct_answer', array(
        'get_callback' => function(){return get_field('correct_answer', get_the_ID());}
    ));
}

//front page redirect
add_action('template_redirect', 'redirect_logged_in_users_from_frontpage');
function redirect_logged_in_users_from_frontpage(){
    if(is_front_page() && is_user_logged_in()){
        $current_user = wp_get_current_user();

        if(in_array('student', $current_user->roles)){
            wp_redirect(home_url('/student'));
        }
        else if(in_array('instructor', $current_user->roles)){
            wp_redirect(home_url('instructor'));
        }
        else{
            wp_redirect(home_url('/login'));
        }
        exit;
    }
   
}

//custom role
add_action('init', 'add_custom_user_roles');
function add_custom_user_roles(){
    add_role('student', 'Student', [
        'read' => true,
    ]);

    add_role('instructor', 'Instructor', [
        'read' => true,
        'edit_posts' => true,
    ]);
}

// login page redirection
add_action('init', 'redirect_login');
add_action('wp_logout', 'redirect_logout');

function redirect_login(){
    $request_uri = $_SERVER['REQUEST_URI'];
    if(strpos($request_uri, 'wp-login.php') !== false && $_SERVER['REQUEST_METHOD'] === 'GET'){
        wp_redirect(home_url('/login'));
        exit;
    }
    else if(strpos($request_uri, 'wp-signup.php') !== false && $_SERVER['REQUEST_METHOD'] === 'GET'){
        wp_redirect(home_url('/sign-up'));
        exit;
    }
}
function redirect_logout(){
    wp_redirect(home_url('/login?logged_out=true'));
    exit;
}

//load assetss
add_action('wp_enqueue_scripts', 'load_assets');
function load_assets(){
    wp_enqueue_style('simulator-main-style', get_theme_file_uri('/css/index.css'));
    wp_enqueue_script('main-simulator-js', get_theme_file_uri('/build/index.js'), array(), null, true);
    wp_localize_script('main-simulator-js', 'simulatorData', array(
        'root_url' => get_site_url(),
        'nonce' => wp_create_nonce('user_nonce'),
        'api_nonce' => wp_create_nonce('wp_rest'),
    ));

    //inst nonce, student nonce
    if(is_page('login') || is_page('signup')){
        wp_enqueue_style('login-page-style', get_template_directory_uri() . '/css/login.css');
    }

    if(is_page('instructor') || is_page('student')){
        wp_enqueue_style('dashboard-page-style', get_template_directory_uri() . '/css/dashboard.css');
    }

    if(is_singular('exam') || is_singular('assigned_exam')){
        wp_enqueue_style('exam-style', get_template_directory_uri() . '/css/single-exam.css');
    }
}

add_action('init', 'session_on');
function session_on(){
    if(!session_id()){
        session_start();
    }
}

function get_filtered_blocks($post_id, $block_name){
    $content = get_post_field('post_content', $post_id);
    $blocks = parse_blocks($content);

    $filtered_blocks = array_filter($blocks, function($block) use ($block_name){
        return $block['blockName'] == $block_name;
    });

    return $filtered_blocks;
}