<?php 
//use due date field with assigned exam post
//do not allow students to access to not permitted exam
//do not confuse user and post -> get_the_ID() returns current page'id not user ID

// start here ----- 6.3
// cvs reader
// pagination - for question list in exam post
// end

$userObj = wp_get_current_user();

$assigned_exams = new WP_Query([
    'posts_per_page' => -1,
    'post_type' => 'assigned_exam',
    'meta_query' => [
        [
            'key' => 'student_id',
            'value' => $userObj->ID,
            'compare' => '='
        ],
        [
            'key' => 'status',
            'value' => 'assigned',
            'compare' => '='
        ],
        [
            'key' => 'complete_by',
            'compare' => '>',
            'value' => date('Y-m-d H:i'),
            'type' => 'datetime'
        ]
    ]
]);

$completedExams = new WP_Query([
    'posts_per_page' => -1,
    'post_type' => 'assigned_exam',
    'meta_query' => [
        [
            'key' => 'student_id',
            'compare' => '=',
            'value' => $userObj->ID
        ],
        [
            'key' => 'status',
            'compare' => '=',
            'value' => 'submitted'
        ]
    ]
]);

$missedExams = new WP_Query([
    'posts_per_page' => -1,
    'post_type' => 'assigned_exam',
    'meta_query' => [
        [
            'key' => 'complete_by',
            'compare' => '<',
            'value' => date('Y-m-d H:i'),
            'type' => 'datetime'
        ]
    ]
]);

get_template_part('layout/header');
?>

<section class="dashboard-section">
    <div class="container">
        <div class="left-block">
            <div class="list-wrapper">
                <h1>Student Overview</h1>
                <div class="info-box">
                    <div class="info-wrapper">
                        <h2><?php echo esc_html($userObj->display_name);?></h2>
                    </div>
                </div>
            </div>
            <div class="list_wrapper">
                <div class="wrapper-heading">
                    <h1>Scores</h1>
                    <h4>More</h4>
                </div>
                <?php while($completedExams->have_posts()):
                        $completedExams->the_post();
                        $examTitle = get_field('exam_id', get_the_ID())[0]->post_title;
                        $score = get_field('score', get_the_ID());
                        $studentCheck = get_field('student_response', get_the_ID())['checkResult'];
                        $numOfCorrect = 0;
                        foreach($studentCheck as $key => $value){
                            if($value){
                                $numOfCorrect++;
                            }
                        }
                        $completedDate = get_field('completed_at', get_the_ID());
                        
                        $instId = get_field('assigned_by', get_the_ID());
                        $userData = get_userdata($instId);
                ?>  
                <div class="list-block score-card">
                    <h2><a href="<?php the_permalink(); ?>"><?php echo esc_html($examTitle);?></a></h2>
                    <span>Score: <?php echo esc_html($score); ?></span>
                    <span>Questions : <?php echo $numOfCorrect; ?> / <?php echo count($studentCheck)?> (Correct / Total)</span>
                    <span>Completed Date: <?php echo $completedDate; ?></span>
                    <span>Assigned By: <?php echo $userData->display_name; ?></span>
                </div>
                <?php endwhile; wp_reset_postdata();?>
            </div>
        </div>
        <div class="exams">
            <div class="list-wrapper">
                <div class="wrapper-heading">
                    <h1>Active Exams List</h1>
                </div>
                <?php while($assigned_exams->have_posts()):
                    $assigned_exams->the_post();
                    $dueDate = '';
                    $dueTime = '';
                    $examPost = get_field('exam_id', get_the_ID())[0];
                    $examTitle = $examPost->post_title;
                    $examDueDateTime = explode(' ', get_field('complete_by', get_the_ID()));
                     if(isset($examDueDateTime[0])){
                        $dueDate = $examDueDateTime[0];
                    }
                    if(isset($examDueDateTime[1])){
                        $dueTime = $examDueDateTime[1] . $examDueDateTime[2];
                    }
                    if($dueDate === '9999-12-31'){
                        $dueDate = 'No due date';
                        unset($dueTime);
                    }
                    $instId = get_field('assigned_by', get_the_ID());
                    $userData = get_userdata($instId);


                    $examDurationTime = get_field('duration', $examPost->ID);
                    $hour = '00';
                    $minute = '00';
                    if(!empty($examDurationTime)){
                        $hour = $examDurationTime['hour'];
                        $minute = $examDurationTime['minute'];
                    }

                ?>
                    <div class="list-block">
                        <h3><a href="<?php the_permalink();?>"><?php echo $examTitle; ?></a></h3>
                        <span><?php echo esc_html($dueDate)?> <?php echo isset($dueTime) ? esc_html($dueTime) : ''; ?></span>
                        <span>Assigned By: <?php echo $userData->display_name; ?></span>
                        <span>
                        <?php if($hour === '00' && $minute === '00'):?>
                            No Limited Time
                        <?php else: ?>
                            Duration: 
                            <?php echo esc_attr($hour) . ' : ' . esc_attr($minute)?>
                        <?php endif; ?>
                        </span>
                    </div>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
            <div class="list-wrapper">
                <div class="wrapper-heading">
                    <h1>Missed Exams List</h1>
                </div>
                <?php
                while($missedExams->have_posts()):
                    $missedExams->the_post();
                    $dueDate = '';
                    $dueTime = '';
                    $examPost = get_field('exam_id', get_the_ID());
                    $examDueDateTime = explode(' ', get_field('complete_by', get_the_ID()));
                    
                    if(isset($examDueDateTime[0])){
                        $dueDate = $examDueDateTime[0];
                    }
                    if(isset($examDueDateTime[1])){
                        $dueTime = $examDueDateTime[1] . $examDueDateTime[2];
                    }
                    $instId = get_field('assigned_by', get_the_ID());
                    $userData = get_userdata($instId);
                ?>
                <div class="list-block">
                    <h3><a href="<?php the_permalink();?>"><?php echo $examPost[0]->post_title ?></a></h3>
                    <span><?php echo esc_html($dueDate . ' ' . $dueTime)?></span>
                    <span>Assigned By: <?php echo $userData->display_name; ?></span>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</section>
<?php
 get_template_part('layout/footer');
?>