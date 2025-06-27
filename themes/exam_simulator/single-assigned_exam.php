<?php 

$status = get_field('status', get_the_ID());
$exam = get_field('exam_id', get_the_ID())[0];
if($status === "submitted"){
   $studentResponse = get_field('student_response', get_the_ID());
   set_query_var('student_response', $studentResponse);
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
   
   $examId = $exam->ID;
   $questions = get_field('related_questions', $examId);

   // if(count($questions) !== count($_POST)){
   //    $_SESSION['submit_status'] = ['type' => 'error', 'text' => 'the number of answers does not match to questions count'];
   //    wp_redirect($_SERVER['REQUEST_URI']);
   //    exit;
   // }

   $result = calculateScore($questions);

   update_field('status', 'submitted', get_the_ID());
   update_field('score', $result['score'], get_the_ID());
   update_field('student_response', $result, get_the_ID());
   update_field('completed_at', current_time('Y-m-d H:i'), get_the_ID());
   
   $_SESSION['submit_status'] = ['type' => 'success', 'text' => 'Exam submitted'];
   wp_redirect($_SERVER['REQUEST_URI']);
   exit;
}

function calculateScore($questions){
   $checkResult = [];
   $studentSelectArray = [];
   $score = 0;
   foreach($questions AS $question){

      $questionId = $question->ID;
      $correct_answer = get_field('correct_answer', $questionId);
      $studentSelect = $_POST[(string)'question-' . $questionId];
      
      if(isset($studentSelect) && $studentSelect === $correct_answer){
         $score += 10;
         $checkResult[$questionId] = true;
      }
      else{
         $checkResult[$questionId] = false;
      }

      $studentSelectArray[$questionId] = $studentSelect;
   }
   return ['score' => $score, 'checkResult' => $checkResult, 'studentSelect' => $studentSelectArray];
}

get_template_part('layout/header');

while(have_posts()){
   the_post();
   $examDurationTime = get_field('duration', $exam->ID);
   $hour = '00';
   $minute = '00';
   if(!empty($examDurationTime)){
      $hour = $examDurationTime['hour'];
      $minute = $examDurationTime['minute'];
   }
?>

<section>
   <div 
      class="container assigned-exam-container" 
      data-status = "<?php echo esc_attr($status); ?>"
      data-duration = "<?php echo esc_attr($hour) . ':' . esc_attr($minute);?>"
   >
      <div class="exam-heading">
         <h1><?php echo $exam->post_title; ?></h1>
         <div class="">
            <h4 id="answered-count"></h4>
            <h4 id="timer"></h4>
            <h4><?php if($status === 'submitted'){
            echo 'Score: ' . get_field('score', get_the_ID());
         }?></h4>
         </div>
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
            <?php echo apply_filters('the_content', $exam->post_content); ?>
            <?php if($status !== 'submitted'): ?>
               <button type="submit" id="exam-submit">Save</button>
            <?php endif;?>
         </form>
      </div>
   </div>
</section>
<?php }
get_template_part('layout/footer');
?>

