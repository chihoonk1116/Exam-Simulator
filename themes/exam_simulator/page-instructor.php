<?php 
$instId = (string) get_current_user_id();
function getAssignedExams($studentId){
    $assignedExams = new WP_Query([
        'posts_per_page' => -1,
        'post_type' => 'assigned_exam',
        'meta_query' => [
            [
                'key' => 'student_id',
                'value' => $studentId,
                'compare' => '='
            ],
            [
                'key' => 'status',
                'value' => 'assigned',
                'compare' => '='
            ]
        ]
    ]);

    return $assignedExams;
}
// write query to get students and exams
$exams = new WP_Query(array(
    'posts_per_page' => -1,
    'post_type' => 'exam',
    'order' => 'ASC'
));

$students = new WP_User_Query([
    'role' => 'student',
    'orderby' => 'registered',
    'order' => 'DESC',
    'number' => -1
]);

// output code should be the last 
get_template_part('layout/header');
?>

<section style="poistion:relative" class="dashboard-inst" data-userid="<?php echo $instId; ?>">
  <div class="container container-common-flex">
    <div class="dashboard-inst_students dashboard-left">
      <h2>Student List</h2>
      <div class="dashboard-inst_students_cards cards">
        <?php foreach($students->get_results() as $student):?>
        <div class="dashboard-inst_students_cards-card card">
          <div class="dashboard-inst_students_cards-card-top card-top card-top-details">
            <h5 style="margin: var(--spacing-sm) 0;"><?php echo $student->display_name; ?></h5>
            <div class="card-details">
              <button data-student-name="<?php echo $student->display_name?>" data-student-id="<?php echo $student->ID;?>" class="primary-btn select-student-btn">Select</button>
            </div>
          </div>
          <div class="dashboard-inst_students_cards-card-bottom card-bottom">
            <p>Assigned Exams: </p>
            <?php $assigned_exam_ids = getAssignedExams($student->ID);
            while($assigned_exam_ids->have_posts()):
                $assigned_exam_ids->the_post();
                $assigned_exam_post = get_field('exam_id', get_the_ID())[0];
            ?>
            <span class="assigned-exam">
                <?php 
                    echo $assigned_exam_post->post_title;
                ?>
            </span>
            <?php endwhile; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="dashboard-inst_exams dashboard-right">
      <div class="flex-between">
        <h2>Exam List</h2>
        <div class="">
            <button id="manage-exam-btn" class="info-btn">Manage Exam(s)</button>
            <button id="create-new-exam_btn" class="info-btn">Create New Exam</button>
        </div>
      </div>
      <div class="dashboard-inst_exams_cards cards">
        <?php while($exams->have_posts()):
          $exams->the_post();
        ?>
        <div class="dashboard-inst_exams_cards-card card">
          <div class="dashboard-inst_exams_cards-card-top card-top-details ">
            <h5 style="margin: var(--spacing-sm) 0;"><?php echo the_title(); ?></h5>
            <div class="card-details">
              <p>Total Question 15</p>
              <p>Duration : 1h</p>
            </div>
          </div>
          <div class="dashboard-inst_exams_cards-card-bottom card-bottom">
            <button class="primary-btn select-exam-btn" data-exam-title="<?php echo the_title();?>" data-exam-id="<?php echo the_ID();?>">Select</button>
            <a href="<?php the_permalink();?>" class="primary-btn">Edit Exam</a>
            <button class="danger-btn">To Archive</button>
          </div>
        </div>
        <?php endwhile; ?>
      </div>
    </div>
  </div>

  <div class="manage-exam-overlay flex-center hidden">
    <div class="container flex-center">
        <h1>Manage Exam(s)</h1>
        <div class="flex-between selected-items_wrapper">
            <div>
                <h5>Selected Exam(s)</h5>
                <div class="manage-exam-overlay_selected-exams">
                    <span class="selected-items">Exam1</span>
                    <span class="selected-items">Exam2</span>
                </div>
            </div>
            <div class="">
                <h5>Selected Student(s)</h5>
                <div class="manage-exam-overlay_selected-students">

                </div>
            </div>
        </div>
        <div class="manage-exam_date-input_wrapper">
            <p>Due Date</p>
            <div class="flex-center">
                <div class="">
                    <input type="date" name="due-date" id="due-date">
                </div>
                <div class="br_vertical"></div>
                <div class="flex-center">
                    <input type="checkbox" name="no-due-date" id="no-due-date" class="hidden">
                    <label for="no-due-date">No Due Date</label>
                </div>
            </div>
        </div>
        <div class="flex-center btns-row">
            <button class="primary-btn" id="assign-exam_btn">Assign Exam</button>
            <button class="primary-btn" id="edit-due_btn">Edit Due Date</button>
            <button class="danger-btn" id="unassign-exam_btn">Unassign Exam</button>
            <button class="info-btn" id="close-overlay-btn">Close</button>
        </div>
    </div>
  </div>

  <div class="create-exam-window flex-center hidden">
    <div class="container flex-center">
        <h1>Create Exam</h1>
        <div class="create-exam-window_input-wrapper flex-center">
            <label for="exam-title">Title:</label>
            <input type="text" id="exam-title" name="exam-title" placeholder="Enter the exam title...">
        </div>
        <div class="flex-center btns-row">
            <button class="primary-btn" id="save-new-exam_btn">Save Exam</button>
            <button class="info-btn" id="close-exam-window_btn">Close</button>
        </div>
    </div>
  </div>


</section>

<?php
 get_template_part('layout/footer');

?>


