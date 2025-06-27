<?php
    if(isset($_POST['login-submit'])){
        $creds = array();
        $creds['user_login'] = $_POST['user-id'];
        $creds['user_password'] = $_POST['user-password'];
        $creds['remember'] = true;

        $user = wp_signon($creds, false);

        if(!is_wp_error($user)){
            
            if(isset($user->roles) && in_array('student', $user->roles)){
                wp_redirect(home_url('/student'));
                exit;
            }
            else if(isset($user->roles) && in_array('instructor', $user->roles)){
                wp_redirect(home_url('/instructor'));
                exit;
            }
        }

    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulator Login</title>
    <?php wp_head(); ?>
</head>
<body>
    <main>
        <div class="login-container">
            <div class="login-heading">
                <h2>Exam Simulator</h2>
            </div>

            <form method="post" action="">
                <div class="input-wrapper">
                    <svg fill="#000000" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 32 32" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path id="id--badge_1_" d="M26,31.36H6c-0.199,0-0.36-0.161-0.36-0.36V6c0-0.199,0.161-0.36,0.36-0.36h7.64V1 c0-0.199,0.161-0.36,0.36-0.36h4c0.199,0,0.36,0.161,0.36,0.36v4.64H26c0.199,0,0.36,0.161,0.36,0.36v25 C26.36,31.199,26.199,31.36,26,31.36z M6.36,30.64h19.28V6.36h-7.28V9c0,0.199-0.161,0.36-0.36,0.36h-4 c-0.199,0-0.36-0.161-0.36-0.36V6.36H6.36V30.64z M14.36,8.64h3.28V1.36h-3.28V8.64z M17,28.36h-7v-0.72h7V28.36z M22,26.36H10 v-0.72h12V26.36z M22.36,24h-0.72c0-2.474-1.841-4.706-4.377-5.307c-0.15-0.036-0.261-0.163-0.275-0.317 c-0.014-0.153,0.071-0.3,0.212-0.362c1.067-0.475,1.756-1.534,1.756-2.699c0-1.629-1.325-2.955-2.955-2.955 c-1.629,0-2.955,1.326-2.955,2.955c0,1.166,0.689,2.225,1.756,2.699c0.141,0.062,0.226,0.209,0.212,0.362 c-0.014,0.154-0.125,0.281-0.275,0.317C12.201,19.294,10.36,21.526,10.36,24H9.64c0-2.535,1.696-4.844,4.133-5.764 c-0.899-0.686-1.448-1.761-1.448-2.921c0-2.026,1.648-3.675,3.675-3.675s3.676,1.648,3.676,3.675c0,1.16-0.55,2.236-1.449,2.921 C20.664,19.156,22.36,21.465,22.36,24z"></path> <rect id="_Transparent_Rectangle" style="fill:none;" width="32" height="32"></rect> </g></svg>
                    <input type="text" name="user-id" id="user-id" placeholder="YOUR ID">
                </div>
                <div class="input-wrapper">
                    <svg viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g id="Layer_2" data-name="Layer 2"> <g id="invisible_box" data-name="invisible box"> <rect width="48" height="48" fill="none"></rect> </g> <g id="Layer_7" data-name="Layer 7"> <g> <path d="M39,18H35V13A11,11,0,0,0,24,2H22A11,11,0,0,0,11,13v5H7a2,2,0,0,0-2,2V44a2,2,0,0,0,2,2H39a2,2,0,0,0,2-2V20A2,2,0,0,0,39,18ZM15,13a7,7,0,0,1,7-7h2a7,7,0,0,1,7,7v5H15ZM37,42H9V22H37Z"></path> <circle cx="15" cy="32" r="3"></circle> <circle cx="23" cy="32" r="3"></circle> <circle cx="31" cy="32" r="3"></circle> </g> </g> </g> </g></svg>
                    <input type="text" name="user-password" id="user-password" placeholder="PASSWORD">
                </div>
                <div class="input-wrapper">
                    <input type="submit" name="login-submit" value="Login">
                </div>
            </form>
            <div class="login-footer">
                <p class="login-alert">
                    <?php if(isset($user) && is_wp_error($user)){
                        echo "Not matched ID or Password...";
                    }?>
                </p>
                <a href="<?php echo site_url('/signup')?>">Sign Up</a>
            </div>
        </div>
    </main>
    <?php wp_footer(); ?>
</body>
</html>

