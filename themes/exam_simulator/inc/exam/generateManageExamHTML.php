
<?php 
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_nonce'])){
   if(wp_verify_nonce($_POST['user_nonce'], 'add_question')){
      
      $title = $_POST['question-title'];
      $answers = $_POST['question-answer'];
      $correct_answer = $_POST['correct-answer'];

      if(empty($title) || empty($answers) || empty($correct_answer)){
         $_SESSION['submit_status'] = ['type' => 'error', 'text' => 'All field should be filled'];
         wp_redirect($_SERVER['REQUEST_URI']);
         exit;
      }
   
      $question_post = wp_insert_post([
            'post_title' => $title,
            'post_type' => 'question',
            'post_status' => 'publish'
      ]);

      //add answer array to the question post's custom field
      if(!is_wp_error($question_post)){
         update_post_meta($question_post, 'answers', $answers);
         update_post_meta($question_post, 'correct_answer',  $correct_answer);
      }
      
      //add question to exam
      if($question_post){
         $updated_related_questions = get_field('related_questions');
         if(!is_array($updated_related_questions)){
            $updated_related_questions = [];
         }

         $updated_related_questions[] = $question_post;

         update_field('related_questions', $updated_related_questions, get_the_ID());
      
         $content = get_post_field('post_content', get_the_ID());
         $content .= "\n\n<!-- wp:custom/question-block {\"questionId\": $question_post} /-->";
         wp_update_post([
            'ID' => get_the_ID(),
            'post_content' => $content,
         ]);
      }

      wp_redirect($_SERVER['REQUEST_URI']);
      exit;
   }

   // delete question
   if(wp_verify_nonce($_POST['user_nonce'], 'delete_question')){
      //delete from ACF
      $related_questions = get_field('related_questions');
      $filtered_related_questions = array_filter($related_questions, callback: function($question){
         if($question->ID != $_POST['quiz-id']){
            return $question;
         }
      });
      
      update_field('related_questions', $filtered_related_questions, get_the_ID());

      //delete the block 
      $content = get_post_field('post_content', get_the_ID());
      $pattern = '/<!-- wp:custom\/question-block\s*\{\s*"questionId"\s*:\s*' . $_POST['quiz-id'] . '\s*\} \/-->/';
      
      $updated_content = preg_replace($pattern, '', $content);

      wp_update_post([
         'ID' => get_the_ID(),
         'post_content' => $updated_content
      ]);

      wp_redirect($_SERVER['REQUEST_URI']);
      exit;
   }

   // add question to the exam from the list
   if(wp_verify_nonce($_POST['user_nonce'], 'add_question_to_the_exam')){
      $related_questions = get_field('related_questions');
      if(!is_array($related_questions)){
         $related_questions = [];
      }  

      $question_ID = $_POST['quiz-id'];
      $existed_question = false;
      foreach($related_questions as $q){
         if($q->ID == $question_ID){
            $existed_question = true;
            break;
         }
      }
   
      if($existed_question){
         $_SESSION['submit_status'] = ['type' => 'error', 'text' => 'Already inserted'];
         wp_redirect($_SERVER['REQUEST_URI']);
         exit;
      }

      $related_questions[] = $question_ID;

      update_field('related_questions', $related_questions, get_the_ID());

      $content = get_post_field('post_content', get_the_ID());
      $content .= "\n\n<!-- wp:custom/question-block {\"questionId\": $question_ID} /-->";
      wp_update_post([
         'ID' => get_the_ID(),
         'post_content' => $content,
      ]);

      wp_redirect($_SERVER['REQUEST_URI']);
      exit;
   }

   // alter duration time
   if(wp_verify_nonce($_POST['user_nonce'], 'alter_exam_duration')){

      $hour = !empty($_POST['duration-hours']) ? $_POST['duration-hours'] : '00';
      $minute = !empty($_POST['duration-minutes']) ? $_POST['duration-minutes'] : '00';
      $durationTime = ['hour' => $hour, 'minute' => $minute];

      update_field('duration', $durationTime, get_the_ID());

      $_SESSION['submit_status'] = ['type' => 'success', 'text' => 'Updated exam duration'];
      wp_redirect($_SERVER['REQUEST_URI']);
      exit;
   }
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
         <div class="">
            <button class="add-question">add question</button>
            <div class="question-input-wrapper">
               <form action="" method="POST">
                  <div class="wrapper">
                     <label for="question-title">Question:</label>
                     <input type="text" name="question-title">
                  </div>
                  <div class="wrapper">
                     <label for="question-answer1">Answer1</label>
                     <input type="text" name="question-answer[]">
                     <input type="checkbox" name="correct-answer[]" value="1">
                  </div>
                  <div class="wrapper">
                     <label for="question-answer2">Answer2</label>
                     <input type="text" name="question-answer[]">
                     <input type="checkbox" name="correct-answer[]" value="2">
                  </div>
                  <div class="wrapper">
                     <label for="question-answer2">Answer3</label>
                     <input type="text" name="question-answer[]">
                     <input type="checkbox" name="correct-answer[]" value="3">
                  </div>
                  <?php wp_nonce_field('add_question', 'user_nonce')?>
                  <button type="submit">Save</button>
               </form>
            </div>
         </div>
         <form action="" method="POST">
            <label for="exam-duration"></label>
            <input type="number" id="duration-hours" name="duration-hours" min="0" placeholder="hour">
            <input type="number" id="duration-minutes" name="duration-minutes" min="0" max="59" placeholder="minute">
            <?php wp_nonce_field('alter_exam_duration', 'user_nonce')?>
            <button type="submit">Save</button>
         </form>
         <?php if(!empty($_SESSION['submit_status'])): ?>
            <?php
               $message = $_SESSION['submit_status'];
               unset($_SESSION['submit_status']);    
            ?>
            <p class="<?= $message['type'] === 'success' ? 'success-alert' : 'warning-alert';?>">
               <?= esc_html($message['text']); ?>
            </p>
         <?php endif; ?>
      
      </div>
      <div class="questions">
         <?php the_content(); ?>
      </div>
   </div>
</section>
<hr>
<section class="quiz-archive">
   <div class="container">
      <h1>Quiz Archive</h1>
      <?php
         $questions = new WP_Query([
            'post_type' => 'question',
            'posts_per_page' => -1
         ]);
         while($questions->have_posts()){
            $questions->the_post(); ?>
            
            
            <div class="quiz-block">
               <div class="quiz-heading">
                  <h3><?php the_title(); ?></h3>
                  <form action="" method="POST">
                     <input type="text" name="quiz-id" value="<?php the_ID(); ?>" class="hidden">
                     <?php wp_nonce_field('add_question_to_the_exam', 'user_nonce')?>
                     <button type="submit">Add to exam</button>
                  </form>
               </div>
               <div class="quiz-answers">
                  <?php 
                     $answers = get_field('answers');
                     $correct_answer = get_field('correct_answer');
                  ?>
                  <h4>Correct Answer: 
                     <?php foreach($correct_answer as $ca){
                        echo $ca . ", ";
                     }?>
                  </h4>
                  <ol>
                  <?php foreach($answers as $answer):?>
                     <li><?php echo esc_html($answer);?></li>
                  <?php endforeach;?>
                  </ol>
               </div>
            </div>
         <?php
         }
         wp_reset_postdata();
      ?>
      
   </div>
</section>
