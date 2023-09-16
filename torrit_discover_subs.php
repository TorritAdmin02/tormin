<?php
session_start();
include_once('torrit_functions.php');

?>
<html>
<head>
    <title>Torrit Communities</title>
    <link rel="stylesheet" href="/public/bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="/public/style.css">

</head>
<body>
<div class="subview-post">
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
        <div class="topnav-right">
            <?php if(isset($_SESSION['logged_in']))
            {
                $notifications = get_notifications($_SESSION['user_id']);
                $nc = (count($notifications)) ? count($notifications) : '';
                $src = '/public/torrit-bell.png';
                $text_color = '';
                if($notifications)
                {
                    $text_color = 'color: lightcoral;';
                    $src = '/public/notification-bell.png';
                }
                echo '<a href="/mail"><img src="/public/torrit-mail.png" class="torrit-icon" /></a>
                                  &nbsp&nbsp';
                echo '<a href="/u/'.$_SESSION['username'].'"><img src="'.$src.'" class="notifications"/><text style="'.$text_color.'">'.$nc.'</text></a>
                                  &nbsp&nbsp';
                echo '<a href="/u/'.$_SESSION['username'].'">'.$_SESSION['username'].'</a>
                                  &#183
                                  <a href="/logout.php">logout</a>';
            }
            else
            {
                echo '
                                  <a href="/u/'.$_SESSION['username'].'"><img src="/public/torrit-bell.png" class="notifications"/></a>
                                  &nbsp&nbsp
                                <a href="/login">login</a>
                                &#8226
                                <a href="/register">register</a>
                                ';
            }
            ?>
        </div>
    </header>
    <main>
        <div id="main-posts">
            <div class="clearfix-post"></div>
           <p><text class="torrit-text">Page Under Construction</text></p>
        </div>
    </main>
    <div class="sidebar-post">

        <div class="sub-sidebar-info">

        </div>

        <div class="sidebar-button-container">

        </div>
        <div class="sidebar-content-container">

        </div>
    </div>
</body>
</html>