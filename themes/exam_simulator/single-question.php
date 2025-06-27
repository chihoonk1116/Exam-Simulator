<?php 
 get_template_part('layout/header');


 while(have_posts()){
    the_post();
    echo get_the_ID();
 }

?>
<h1>questions post.php</h1>

<h1><?php the_title(); ?></h1>

   
<?php
 get_template_part('layout/footer');
?>