<?php
/**
 * Plugin Name: Question Block
 * Description: A simple custom block for questions.
 * Version: 1.0
 * Author: You
 */


if( ! defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'inc/custom-route.php';
require_once plugin_dir_path(__FILE__) . 'inc/generateHTML.php';

class QuestionBlock{
    function __construct(){
        add_action('init', array($this, 'adminAssets'));

    }
    function adminAssets(){
        register_block_type(__DIR__, array(
            'render_callback' => array($this, 'theHTML')
        ));
        wp_register_script('questionBlockFrontScript', plugin_dir_url(__FILE__) . 'build/front.js');
    }
    function theHTML($attributes){
        wp_enqueue_script('questionBlockFrontScript');
        return generateHTML($attributes['questionId'], false);
    }
}

$questionBlock = new QuestionBlock();