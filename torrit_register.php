<?php
session_start();
include_once('torrit_functions.php');
if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true)
{
    header('Location: /home');
}
$can_register = false;
$error_display = null;
if($_POST){
    if(isset($_POST['register_captcha_input']))
    {
        $captcha_input = $_POST['register_captcha_input'];
        if($_SESSION['secure'] == $captcha_input)
        {
            $can_register = true;
        }
    }
    if($can_register)
    {
        if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['confirm_password'])) {
            if (trim($_POST['password']) == trim($_POST['confirm_password'])) {
                $user_array = array();
                $user_array['username'] = $_POST['username'];
                $user_array['password'] = $_POST['password'];
                $user_array['confirm_password'] = $_POST['confirm_password'];

                $register_user = torrit_user_register($user_array);

                if ($register_user) {
                    $_SESSION['user_created'] = true;
                    header('Location: /home');
                } else {
                    echo '<div style="background-color: lightcoral;">Error Creating User Account</div>';
                }
            } else {
                echo '<div style="background-color: lightcoral;">Passwords Must Match</div>';
            }
        } else {
            echo '<div style="background-color: lightcoral;">All Fields Required</div>';
        }
    }
    else
    {
        $error_display =  '<div style="background-color: lightcoral; padding: 1%;"><p style="color:white; text-align: center;"><b>You failed the captcha</b></p></div>';
    }
}
?>
<html>
    <head>
        <title>Torrit Register</title>
        <link rel="stylesheet" href="/public/bootstrap/css/bootstrap.css">
        <link rel="stylesheet" href="/public/torrit_login.css">
    </head>
    <body>
    <header>
           <div class="logo-container">
                <div class="logo-holder logo-2">
                    <a href="/home">
                        <h3>Torrit</h3>
                        <p>since 2023</p>
                    </a>
                </div>
            </div>
            <nav>
                <ul>
                    <li><a href="frontpage">frontpage</a></li>
                    <li><a href="t/torrit">torrit</a></li>
                    <li><a href="contact">contact</a></li>
                    <li><a href="/search"><text style="color:lightcoral">search</text> <img src="/public/search.png" class="search-glass"></a></li>
                </ul>
            </nav>
        </header>
        <main>
            <div id="main-posts">
                <?php if(!is_null($error_display))
                      {
                        echo $error_display;
                      }
                ?>
                <div id="login-form-wrap">
                    <h2>Register</h2>
                    <form action="register" method="post" id="login-form">
                        <p>
                        <input type="text" id="username" name="username" placeholder="Username" value="<?php if (isset($_POST['username'])) echo $_POST['username']; ?>" required><i class="validation"><span></span><span></span></i>
                        </p>
                        <p>
                        <input type="password" id="password" name="password" placeholder="Password" required><i class="validation"><span></span><span></span></i>
                        </p>
                        <p>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required><i class="validation"><span></span><span></span></i>
                        </p>
                        <div id="comment-captcha">
                            <?php $captcha_id = substr(md5(time()), 0, 5); ?>
                            <div>
                                <img src='/captcha/<?php echo $captcha_id; ?>'>
                            </div>
                            <input type="text" placeholder="Enter Captcha" name="register_captcha_input"/>
                            <div class="clearfix-comment"></div>
                            <text class="torrit-text"><small><i>not* case sensitive</i></small></text>

                        </div>
                        <div class="clearfix"></div><br>
                        <p>
                        <input type="submit" id="register" value="Register">
                        </p>
                    </form>
                    <div id="create-account-wrap">
                        <p>Already a member? <a href="login">Sign In</a><p>
                    </div>
                </div>
            </div>
        </main>