<?php 

// review functionality to see correct and wrong questions -> using student_response field
// populate student overview data
// timer (simple - no bar)
// cvs reader
// pagination

//consider to use exam post(inst side ) and assign exam post(student side) view separately

if($_SERVER['REQUEST_METHOD'] === 'POST'){
   $examId = get_the_ID();
   $questions = get_field('related_questions', $examId);

   if(count($questions) !== count($_POST)){
      $_SESSION['submit_status'] = ['type' => 'error', 'text' => 'the number of answers does not match to questions count'];
      wp_redirect($_SERVER['REQUEST_URI']);
      exit;
   }

   $result = calculateScore($questions);
   $assignedExam = getAssignedExam($examId);
   
   if($assignedExam->have_posts()){
      $assignedExam->the_post();
      
      update_field('status', 'submitted', get_the_ID());
      update_field('score', $result['score'], get_the_ID());
      update_field('student_response', $result['checkResult'], get_the_ID());
      update_field('completed_at', date('m-s-Y H:i'), get_the_ID());
   }

   wp_reset_postdata();

   $_SESSION['submit_status'] = ['type' => 'success', 'text' => 'Score : ' . $result['score']];
   wp_redirect($_SERVER['REQUEST_URI']);
   exit;
}

function calculateScore($questions){
   $checkResult = [];
   $score = 0;
   foreach($questions AS $question){

      $questionId = $question->ID;
      $correct_answer = json_decode(get_field('correct_answer', $questionId));
       
      if($_POST[(string)'question-' . $questionId] === $correct_answer){
         $score += 10;
         $checkResult[$questionId] = true;
      }
      else{
         $checkResult[$questionId] = false;
      }
   }
   return ['score' => $score, 'checkResult' => $checkResult];
}

function getAssignedExam($examId): WP_Query{
   $stuId = get_current_user_id();

   $assignedExam = new WP_Query([
      'post_type' => 'assigned_exam',
      'meta_query' => [
         [
            'key' => 'exam_id',
            'compare' => '=',
            'value' => $examId,
            'type' => 'NUMERIC'
         ],
         [
            'key' => 'student_id',
            'compare' => '=',
            'value' => $stuId,
            'type' => 'NUMERIC'
         ]
      ]
   ]);

   return $assignedExam;
}

get_template_part('layout/header');
while(have_posts()){
   the_post();
}
?>

<section>
   <div class="container">
      <div class="exam-heading">
         <h1><?php the_title(); ?></h1>
         <?php if(!empty($_SESSION['submit_status'])):
               $message = $_SESSION['submit_status'];
               unset($_SESSION['submit_status']);
            ?>
               <span class="<?php echo $message['type'] === 'error' ? 'warning-alert': 'success-alert'; ?>">
                  <?php echo $message['text'] ?>
               </span>
         <?php endif;?>
      </div>
      <div class="questions">
         <form action="" method="POST">
            <?php the_content(); ?>
            <input type="submit" value="Save">
         </form>
         
      </div>
   </div>
</section>

