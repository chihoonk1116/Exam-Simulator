<?php
require_once plugin_dir_path(__FILE__) . 'generateHTML.php';

add_action('rest_api_init', 'customAPI');

function customAPI(){
    register_rest_route('simulator/v1','/question', array(
        'method' => 'GET',
        'callback' => 'getQuestion',
        'permission_callback' => '__return_true'
    ));
    register_rest_route('questionBlock/v1','/getHTML', array(
        'method' => 'GET',
        'callback' => 'getBlockHTML',
        'permission_callback' => '__return_true'
    ));
}

function getBlockHTML($req){
    return generateHTML($req['questionId'], true);
}

function getQuestion($req){
    $res = array();
    if(isset($req['examId'])){
        $questions = get_field('related_questions', $req['examId']);
        foreach($questions as $q){
            $res[] = $q->ID;
        }
        
    }
    return ['questions' => $res];
}