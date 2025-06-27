<?php

get_template_part('layout/header');

if(have_posts()){
   the_post();
}

$questions = get_field('related_questions', get_the_ID());
?>

<section style="position:relative">
   <div class="container">
      <div class="flex-between exam-heading">
         <h1><?php the_title();?></h1>
         <div class="exam-details">
            <p>Total Questions : 10</p>
            <p>Duration : 1 Hour </p>
         </div>
      </div>
      <div class="exam-quizs">
         <?php foreach($questions as $question):
            $answers = get_field('answers', $question->ID);
            $correctAnswers = get_field('correct_answer', $question->ID)   
         ?>
           <div class="quiz-card card">
            <div class="card-top quiz-card_top flex-between">
               <h4 class="quiz-card_top-title"><?php echo $question->post_title;?></h4>
               <div class="flex-center quiz-card_top-info">
                  <p>
                     Correct Answer: 
                     <?php foreach($correctAnswers as $ca):
                        echo $ca;
                     ?>
                     <?php endforeach;?>
                  </p>
                  <button class="danger-btn">X</button>
               </div>
            </div>
            <div class="card-details quiz-options">
               <ol>
                  <?php foreach($answers as $answer):?>
                     <li><?php echo $answer;?></li>
                  <?php endforeach;?>
               </ol>
            </div>
           </div>
         <?php endforeach; ?>
         
         
      </div>
   </div>
</section>