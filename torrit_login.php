<?php
session_start();
include_once('torrit_functions.php');
if(isset($_POST['username']) && isset($_POST['password']))
{
    $auth_array = array();
    $auth_array['username'] = $_POST['username'];
    $auth_array['password'] = $_POST['password'];

    $authenticate_user = torrit_user_login($auth_array);

    if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true)
    {
        header('Location: /home');
    }

    if(isset($_SESSION['error_message']))
    {
        echo '<div style="background-color: lightcoral;">'.$_SESSION['error_message'].'</div>';
    }
}
?>
<html>
    <head>
        <title>Torrit Login</title>
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
                <div id="login-form-wrap">
                    <h2>Login</h2>
                    <form id="login-form" method="post" action="login">
                        <p>
                        <input type="text" id="username" name="username" placeholder="Username" required><i class="validation"><span></span><span></span></i>
                        </p>
                        <p>
                        <input type="password" id="password" name="password" placeholder="Password" required><i class="validation"><span></span><span></span></i>
                        </p>
                        <p>
                        <input type="submit" id="login" value="Login">
                        </p>
                    </form>
                    <div id="create-account-wrap">
                        <p>Not a member? <a href="register">Create Account</a><p>
                    </div>
                </div>
            </div>
        </main>