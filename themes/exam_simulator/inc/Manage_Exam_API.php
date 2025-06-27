<?php

class Manage_Exam_API{
  public function __construct(Type $var = null) {
    add_action('rest_api_init', [$this, 'register_routes']);
  }

  public function register_routes(){
    register_rest_route('custom/v1', 'add_question', [
      'methods' => 'POST',
      'callback' => [$this, 'addQuestion']
    ]);
  }

  public function addQuestion(WP_REST_Request $req){
    
    error_log(var_dump("hello"));
   
    return new WP_REST_Response([
      'status' => 'success'
    ], 200);
  }

}