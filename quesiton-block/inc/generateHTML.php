<?php 

function generateHTML($id, $fromJS = null){
    $questionPost = new WP_Query([
        'post_type' => 'question',
        'p' => $id
    ]);

    $studentResponse = get_query_var('student_response');
    $studentSelect = [];
    $studentResult = [];
    if(!empty($studentResponse)){
        $studentSelect = $studentResponse['studentSelect'];
        $studentResult = $studentResponse['checkResult'];
    }

    //hide correct answer
    $isInstructor = false;
    $currentUser = wp_get_current_user();
    if(in_array('instructor', $currentUser->roles)){
        $isInstructor = true;
    }
    
    while($questionPost->have_posts()){
        $questionPost->the_post();
        ob_start(); 
        ?>
        <?php 
            $correct_answers = get_field('correct_answer');
            $questionStringId = (string) get_the_ID();
            
            if(isset($studentResult[$questionStringId])){
                if($studentResult[$questionStringId]){
                    echo "Correct";
                }
                else{
                    echo "Wrong Answer";
                }
            }
        ?>
        <div class="quiz-block" data-id = <?php the_ID();?>>
            <div class="quiz-heading">
                <h3><?php the_title(); ?></h3>
                <?php 
                    if($isInstructor && ($fromJS == null || $fromJS == false)){
                        //not api call 
                ?>
                    <form action="" method="POST">
                        <input type="text" name="quiz-id" value="<?php the_ID(); ?>" class="hidden">
                        <?php wp_nonce_field('delete_question', 'user_nonce')?>
                        <button type="submit">Delete</button>
                    </form>
                <?php 
                    }
                ?>
            </div>
            <div class="quiz-answers">
            <?php
                

                $answers = get_field('answers');
            ?>

            <h4>Correct Answer: 
                <?php foreach($correct_answers as $ca){
                    echo $ca . ", ";
                }?>
            </h4>
            
            <?php foreach($answers as $index => $answer):?>
                <input 
                    type=<?php echo count($correct_answers) > 1 ? "checkbox" : "radio"?>
                    name="<?php echo 'question-' . get_the_ID()?>[]"  
                    id="<?php echo 'question-' . get_the_ID() . '-' . $index; ?>" 
                    value="<?php echo $index+1; ?>" 
                    <?php
                    if(!empty($studentResponse)){
                        echo 'disabled ';
                        if(isset($studentSelect[$questionStringId])){
                            echo in_array((string) ($index+1), $studentSelect[$questionStringId]) ? 'checked' : "";
                        }
                    }
                   
                    ?>
                    
                >
                <label for="<?php echo 'question-' . get_the_ID() . '-' . $index; ?>"><?php echo esc_html($answer); ?></label>
            <?php endforeach;?>
                
            </div>
        </div>
        <?php 
        wp_reset_postdata();
        return ob_get_clean();
    }
}
