<?php
session_start();
include_once('torrit_functions.php');
require 'vendor/autoload.php';
$bbcode = new ChrisKonnertz\BBCode\BBCode();
extend_bbcode($bbcode);

if(!isset($_GET['pid']))
{
    header("Location: /home");
}

if(!verify_pid($_GET['pid']))
{
    header("Location: /home");
}
$user_id = 0;
if(isset($_SESSION['user_id']))
{
    $user_id = $_SESSION['user_id'];
}

$post_id = $_GET['pid'];
$error_noauth = null;
$post = get_post($post_id);

if($post['user_id'] !== $user_id)
{
    header("Location: /home");
}
if($post['sub_id'] == 0)
{
    $sub = array();
    $sub['id'] = 0;
    $sub['name'] = "FrontPage";
    $sub['slug'] = "frontpage";
    $sub['color_code'] = "#e88f8f";
    $sub['mast_theme'] = "#e88f8f";
    $sub['sidebar'] = "[hr]Don't know which community to share to? [br][br] post where everyone can see [br][br] post anonymously or with your account [br][hr]";
    $sub['post_rules'] = "Post directly to frontpage - [b]logged in or not[/b]";
    $sub['owner'] = 1;
    $sub_id = 0;
}
else
{
    $sub = get_sub($post['sub_id']);
    $sub_id = $sub['id'];
}
$hover = null;
$hover = adjustBrightness($sub['color_code'], 20);

if(isset($_GET['a']))
{
    $action = $_GET['a'];
    if($action == 's')
    {
        if($user_id == null)
        {
            header("Location: /login");
        }
        $verify_user = verify_uid($user_id);
        if($verify_user)
        {
            $subscribe = subscribe_to_sub($sub_id, $user_id);
            header("Location: /post/{$post_id}/edit");
        }
    }
    else if($action == 'us')
    {
        if($user_id == null)
        {
            header("Location: /login");
        }
        $verify_user = verify_uid($user_id);
        if($verify_user)
        {
            $unsubscribe = unsubscribe($sub_id, $user_id);
            header("Location: /post/{$post_id}/edit");
        }
    }
}
if(isset($_POST['title']) && isset($_POST['content']))
{

    $post_title = $_POST['title'];
    $post_content = $_POST['content'];
    $sub_id = $sub['id'];

    $post_id = update_post($post_title, $post_content, $post['id'], $sub_id, $user_id);
    if ($post_id) {
        header("Location: /post/{$post_id}");
    }
    else
    {
        header("Location: {$base_uri}/post/{$post_id}/edit");
    }

}
?>
<html>
<head>
    <title>Torrit Edit Post</title>
    <link rel="stylesheet" href="/public/bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="/public/style.css">
    <style>
        a {
            color: <?php echo $sub['color_code']; ?>
        }
        .torrit-buttons {
            background-color: <?php echo $sub['color_code']; ?>
        }
        .torrit-buttons:hover {
            background-color: <?php echo $hover; ?>
        }
    </style>
</head>
<body>
<div class="subview">
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
                <li><a href="<?php echo $base_uri.'/frontpage'?>">frontpage</a></li>
                <li><a href="<?php echo $base_uri.'/t/torrit'?>">torrit</a></li>
                <li><a href="<?php echo $base_uri.'/frontpage'?>">contact</a></li>
                <li><a href="<?php echo $base_uri.'/search'; ?>"><text style="color:lightcoral">search</text> <img src="/public/search.png" class="search-glass"></a></li>
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
        <?php
        $mast_theme = $sub['mast_theme'];
        ?>
        <div class="sub-masthead" style="background-color: <?php echo $mast_theme; ?>">
            <a href="<?php echo $base_uri.'/t/'.$sub['slug']; ?>"><p class="sub-masthead-text"><?php echo '/t/'.$sub['name']; ?></p></a>
        </div>
        <div id="main-posts">
            <?php
            if(!is_null($error_noauth))
            {
                echo $error_noauth;
            }
            ?>
            <div class="clearfix-create-post"></div>
            <div class="create-post-container">
                <div class="post-rules-container">
                    <?php
                    if($sub['post_rules'])
                    {
                        $post_rules_html = $bbcode->render($sub['post_rules'], true, false);
                        echo $post_rules_html;
                    }
                    ?>
                </div>
                <form method="post" action="<?php echo $base_uri.'/post/'.$post['id'].'/edit'; ?>">
                    <label><text class="torrit-text">Title</text></label><br>
                    <input type="text" class="torrit-large-input" name="title" required="" value="<?php echo $post['title']; ?>">
                    <div class="clearfix"></div>
                    <label><text class="torrit-text">Content</text></label><br>
                    <textarea class="torrit-large-textarea" name="content" required=""><?php echo $post['body']; ?></textarea>
                    <div class="clearfix"></div>
                    <input type="submit" class="torrit-buttons" style="width: 19%; font-size: 12; text-align: center;" value="Submit Post" name="post"></input>
                </form>
            </div>
        </div>
    </main>
    <div class="sidebar-subview">

        <div class="sub-sidebar-info">
            <?php
            $sub_letter = strtoupper(substr($sub['name'],0,1));
            ?>
            <div class="sub-logo" style="background-color:<?php echo $sub['color_code']; ?>"><p class="subletter"><?php echo $sub_letter; ?></p></div>
            <div class="clearfix"></div>
            <?php if($sub_id > 0)
                  {
                        $subscriber_count = get_sub_subcriber_count($sub_id);
                        echo '
                             <a href="'.$base_uri.'/t/'.$sub['slug'].'" class="sub-sidebar-name">/t/'.$sub['name'].'</a>
                             <div class="clearfix"></div>
                             <div class="sub-member-count">'.$subscriber_count.'</div>
                             ';
                    }
            ?>
        </div>

        <div class="sidebar-button-container">
            <a href="<?php echo $base_uri.'/'.$sub['slug'].'/create'; ?>"><button class="torrit-buttons" role="button">Submit Post</button></a>
            <div class="clearfix">
                <?php
                if($sub_id > 0)
                {
                    $is_subbed = check_already_subbed($sub['id'], $user_id);
                    if ($is_subbed) {
                        echo '
                        <a href="/post/' . $post_id . '/edit/us"><button class="torrit-buttons" style="background-color: lightcoral" role="button">UnSubscribe</button></a>
                         ';
                    } else {
                        echo '
                         <a href="/post/' . $post_id . '/edit/s"><button class="torrit-buttons" role="button">Subscribe</button></a>
                         ';
                    }

                    if ($user_id == $sub['owner']) {
                        $manage_btn = '
                                  <div class="clearfix"></div>
                                  <a href="' . $base_uri . '/t/' . $sub['slug'] . '/manage"><button class="torrit-buttons" role="button">Manage Sub</button></a>
                                  ';
                        echo $manage_btn;
                    }
                }

                ?>
            </div>
        </div>
        <div class="sidebar-content-container">
            <?php
            if($sub['sidebar'])
            {
                $sidebar_html = $bbcode->render($sub['sidebar'], true, false);
                echo $sidebar_html;
            }
            ?>
        </div>
    </div>
    </div>
</body>
</html>