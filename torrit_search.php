<?php
session_start();
include_once('torrit_functions.php');
require 'vendor/autoload.php';
$bbcode = new ChrisKonnertz\BBCode\BBCode();
extend_bbcode($bbcode);

$get_results = null;
if(isset($_POST['search_term']))
{
    $search_term = $_POST['search_term'];
    $get_results = torrit_search($search_term);
}

?>
<html>
<head>
    <title>Torrit Search</title>
    <link rel="stylesheet" href="/public/bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="public/style.css">
</head>
<body>
<div class="subview">
    <header>
        <div class="logo-container">
            <div class="logo-holder logo-2">
                <a href="home">
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
            <div class="clearfix-create-post"></div>
            <div class="create-post-container">
                <form method="post" action="/search">
                    <label><text class="torrit-text">Search Torrit</text></label><br>
                    <input type="text" class="torrit-large-input" name="search_term" value="">
                    <div class="clearfix"></div>
                    <input type="submit" class="torrit-buttons" style="width: 19%; font-size: 12; text-align: center;" value="search" name="search"></input>
                </form>
            </div>
        </div>
        <div class="clearfix-post"></div>
        <div id="user-items">

            <?php
            if(isset($get_results))
            {
                foreach ($get_results as $item) {
                    $user = get_user($item['user_id']);
                    $username = $user['username'];
                    if ($item['type'] == 'comment') {
                        $post = get_post($item['post_id']);
                        if ($post['sub_id'] == 0) {
                            $sub_link = ' <text class="torrit-text"><small>  frontpage</small></text>';
                        } else {
                            $sub = get_sub($post['sub_id']);
                            $sub_href = '/t/' . $sub['slug'];
                            $sub_link = '<a href=' . $sub_href . '>/t/' . $sub['slug'] . '</a>';
                        }
                        $comment_body = $bbcode->renderPlain($item['comment']);

                        echo '
                    <div class="user-item">
                    <div class="post">
                    <text class="torrit-text"><small>Comment : </small></text>
                    <p class="sub_p">on post <a href="/post/' . $item['post_id'] . '">' . $post['title'] . '</a> in <subname class="subname">' . $sub_link . '</subname></p>
                    <small>' . $comment_body . '..</small><br>
                    <div><small><b><a href="/post/' . $item['post_id'] . '">view</a></b></small></div>
                    </div>
                    <hr>
                    </div>';
                    } else {
                        if ($item['sub_id'] == 0) {
                            $sub_link = ' <text class="torrit-text"><small>  frontpage</small></text>';
                        } else {
                            $sub = get_sub($item['sub_id']);
                            $sub_href = '/t/' . $sub['slug'];
                            $sub_link = '<a href=' . $sub_href . '>/t/' . $sub['slug'] . '</a>';
                        }
                        $post_body = $bbcode->renderPlain($item['body']);

                        echo '
                    <div class="user-item">
                    <div class="post">
                    <p class="title">' . $item['title'] . '</p>
                    <p class="sub_p">in<subname class="subname">' . $sub_link . '</subname></p>
                    <small>' . substr($post_body, 0, 55) . '..</small><br>
                    <div><small><b><a href="/post/' . $item['id'] . '">view</a></b></small></div>
                    </div>
                    <hr>
                    </div>';
                    }
                }
            }
            ?>
        </div>
    </main>
    <div class="sidebar">
        <div class="sidebar-button-container">
            <a class="torrit-buttons" style="background-color: lightcoral;" href="/create/frontpage"> Create Frontpage Post</a>
            <div class="clearfix"></div>
            <a class="torrit-buttons" href="create_sub"> Create Torrit Sub</a>
            <div class="clearfix"></div>
            <a class="torrit-buttons" href="/discover"> Find Torrit Sub</a>
            <div class="clearfix"></div>

        </div>
</body>
</html>
