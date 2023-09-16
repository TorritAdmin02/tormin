<?php
session_start();
include_once('torrit_functions.php');
require 'vendor/autoload.php';
$bbcode = new ChrisKonnertz\BBCode\BBCode();

if(!isset($_GET['uid']))
{
    header("Location: /home");
}

$user = get_user_by_name($_GET['uid']);
if(!verify_uid($user['id']))
{
    header("Location: /home");
}

$viewed_user = $user['id'];

$user_id = null;
if(isset($_SESSION['user_id']))
{
    $user_id = $_SESSION['user_id'];
}
if($viewed_user == $user_id)
{
  $notifications = get_notifications($user_id);
}

$viewed_user_data = get_user_posts_and_comments($viewed_user);


?>
<html>
<head>
    <title>Torrit User <?php echo $user['username']; ?></title>
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
        <div id="user-items">

            <?php
            foreach($notifications as $notification)
            {
                $action = null;
                $type = $notification['type'];
                if($type == 1)
                {
                    $action = ' commented on your post';
                }
                else
                {
                    $action = ' responded to you comment';
                }
                $user = get_user($notification['originator']);
                $message = $user['username'] . $action;

                echo '<a class="notification-link-text" href="/post/'.$notification['post_id'].'/r='.$notification['id'].'"><div class="user-item">'.$message.'</div>
                       <div class="clearfix-comment"></div></a>';

            }
           echo '<div class="clearfix"></div>';
            foreach($viewed_user_data as $item)
            {
                if(array_key_exists('comment', $item))
                {

                    $user = get_user($item['user_id']);
                    $username = $user['username'];
                    $post = get_post($item['post_id']);
                    if($post['sub_id'] == 0)
                    {
                        $sub_link = ' <text class="torrit-text"><small>  frontpage</small></text>';
                    }
                    else
                    {
                        $sub = get_sub($post['sub_id']);
                        $sub_href = $base_uri . '/t/' . $sub['slug'];
                        $sub_link = '<a href=' . $sub_href . '>/t/' . $sub['slug'] . '</a>';
                    }
                    $comment_body = $bbcode->renderPlain($item['comment']);

                    echo '
                    <div class="user-item">
                    <div class="post">
                    <text class="torrit-text"><small>Comment : </small></text>
                    <p class="sub_p">on post <a href="/post/'.$item['post_id'].'">'.$post['title'].'</a> in <subname class="subname">'.$sub_link.'</subname></p>
                    <small>'.$comment_body.'..</small><br>
                    <div><small><b><a href="/post/'.$item['post_id'].'">view</a></b></small></div>
                    </div>
                    <hr>
                    </div>';
                }
                else
                {
                    $user = get_user($item['user_id']);
                    $username = $user['username'];
                    if($item['sub_id'] == 0)
                    {
                        $sub_link = ' <text class="torrit-text"><small>  frontpage</small></text>';
                    }
                    else
                    {
                        $sub = get_sub($item['sub_id']);
                        $sub_href = $base_uri . '/t/' . $sub['slug'];
                        $sub_link = '<a href=' . $sub_href . '>/t/' . $sub['slug'] . '</a>';
                    }
                    $post_body = $bbcode->renderPlain($item['body']);

                    echo '
                    <div class="user-item">
                    <div class="post">
                    <p class="title">'.$item['title'].'</p>
                    <p class="sub_p">in<subname class="subname">'.$sub_link.'</subname></p>
                    <small>'.substr($post_body, 0, 55).'..</small><br>
                    <div><small><b><a href="/post/'.$item['id'].'">view</a></b></small></div>
                    </div>
                    <hr>
                    </div>';
                }
            }
            ?>
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