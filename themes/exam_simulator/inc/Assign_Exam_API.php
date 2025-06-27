<?php

class Assign_Exam_API {

    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes() {
        register_rest_route('custom/v1', 'create-assign-post', [
            'methods' => 'POST',
            'callback' => [$this, 'create_assign_post'],
            'permission_callback' => '__return_true'
        ]);

        register_rest_route('custom/v1', 'delete-assign-post', [
            'methods' => 'POST',
            'callback' => [$this, 'delete_assign_post'],
            'permission_callback' => '__return_true'
        ]);

        register_rest_route('custom/v1', 'edit-due-date', [
            'methods' => 'POST',
            'callback' => [$this, 'edit_due_date'],
            'permission_callback' => '__return_true'
        ]);
    }

    public function create_assign_post(WP_REST_Request $req) {
        $params = $req->get_json_params();

        $examIds   = $params['examIds'];
        $studentIds = $params['stuIds'];
        $instId    = (string) $params['instId'];
        $dueDate   = $params['completeByDate'] ?: '9999-12-31';
        $dueTime   = empty($params['completeByTime']) ? '00:00' : $params['completeByTime'];

        foreach ($studentIds as $studentId) {
            $studentName = get_userdata($studentId)->display_name;

            foreach ($examIds as $examId) {
                $examTitle = get_the_title($examId);

                // 중복 검사 필요 시 이곳에서 쿼리 또는 조건 추가

                $assignExamPost = wp_insert_post([
                    'post_title'  => (string) $studentName . '-assigned-' . $examTitle,
                    'post_type'   => 'assigned_exam',
                    'post_status' => 'publish'
                ]);

                if (is_wp_error($assignExamPost)) {
                    return new WP_Error('create_failed', 'Post creation failed', ['status' => 500]);
                }

                update_field('exam_id', $examId, $assignExamPost);
                update_post_meta($assignExamPost, 'student_id', $studentId);
                update_post_meta($assignExamPost, 'status', 'assigned');
                update_post_meta($assignExamPost, 'complete_by', $dueDate . ' ' . $dueTime);
                update_post_meta($assignExamPost, 'assigned_by', $instId);
            }
        }

        return new WP_REST_Response(['status' => 'Success'], 200);
    }

    public function delete_assign_post(WP_REST_Request $req) {
        $params     = $req->get_json_params();
        $studentIds = $params['studentIds'];
        $examIds    = $params['examIds'];

        foreach ($studentIds as $studentId) {
            $assignedPost = $this->get_assigned_exams($studentId);

            while ($assignedPost->have_posts()) {
                $assignedPost->the_post();
                $postExamId = get_field('exam_id', get_the_ID())[0]->ID;

                if (in_array($postExamId, $examIds)) {
                    wp_delete_post(get_the_ID(), true);
                }
            }
            wp_reset_postdata();
        }

        return new WP_REST_Response(['message' => 'Complete!'], 200);
    }

    public function edit_due_date(WP_REST_Request $req) {
        $params      = $req->get_json_params();
        $studentIds  = $params['studentIds'];
        $examIds     = $params['examIds'];
        $completeBy  = $params['completeByDate'];

        foreach ($studentIds as $studentId) {
            $assignedExams = $this->get_assigned_exams($studentId);

            while ($assignedExams->have_posts()) {
                $assignedExams->the_post();
                $assignedExamId = get_field('exam_id', get_the_ID())[0]->ID;

                if (in_array($assignedExamId, $examIds)) {
                    update_field('complete_by', $completeBy, get_the_ID());
                }
            }

            wp_reset_postdata();
        }

        return new WP_REST_Response(['message' => 'Complete'], 200);
    }

    private function get_assigned_exams($studentId) {
        return new WP_Query([
            'posts_per_page' => -1,
            'post_type'      => 'assigned_exam',
            'meta_query'     => [
                [
                    'key'     => 'student_id',
                    'value'   => $studentId,
                    'compare' => '='
                ],
                [
                    'key'     => 'status',
                    'value'   => 'assigned',
                    'compare' => '='
                ]
            ]
        ]);
    }
}