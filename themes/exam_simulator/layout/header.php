<?php

 $current_user = wp_get_current_user();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head();?>
    <title>Document</title>
</head>
<body>
      
    <header>
        <div class="heading-container">
            <h1>Hello <?php echo esc_html($current_user->display_name); ?></h1>
            <div class="">
                <a href="<?php echo home_url();?>">Home</a>
                <a href="<?php echo wp_logout_url(home_url('/login'))?>" class="logout-link">Logout</a>
            </div>

        </div>
    </header>
    <main>