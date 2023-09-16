<?php
session_start();
include_once('torrit_functions.php');
require 'vendor/autoload.php';
$bbcode = new ChrisKonnertz\BBCode\BBCode();
extend_bbcode($bbcode);

if(!isset($_GET['sid']))
{
    header("Location: /home");
}
$user_id = null;
if(isset($_SESSION['user_id']))
{
    $user_id = $_SESSION['user_id'];
}

$error_noauth = null;
if(isset($_SESSION['error_noauth_post']))
{
    $error_message = $_SESSION['error_noauth_post'];
    unset($_SESSION['error_noauth_post']);
    $error_noauth = '<div style="background-color: lightcoral; padding: 2%;"><p style="color:white; text-align: center;"><b>'.$error_message.'</b></p></div>';
}

$sub_id = $_GET['sid'];
if($sub_id == 'frontpage')
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
    $sub = get_sub_by_name($sub_id);
    $sub_id = $sub['id'];
    $sub_posts = get_posts_for_sub($sub_id);
}
$hover = null;
$hover = adjustBrightness($sub['color_code'], 20);
if(!$sub)
{
    header("Location: /home");
}
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
            header("Location: /{$sub['slug']}/create");
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
            header("Location: /{$sub['slug']}/create");
        }
    }
}
$can_post = false;
if(isset($_POST['title']) && isset($_POST['content']))
{
    if(isset($_POST['post_captcha_input']))
    {
        $captcha_input = $_POST['post_captcha_input'];
        if($_SESSION['secure'] !== $captcha_input)
        {
            $can_post = false;
        }
        else
        {
            $can_post = true;
        }
    }
    if($can_post)
    {
        if ($sub_id == 0 && $sub['slug'] == 'frontpage') {
            if (isset($_SESSION['user_id'])) {
                $user_id = $_SESSION['user_id'];
            } else {
                $user_id = 0;
            }
            $post_title = $_POST['title'];
            $post_content = $_POST['content'];
            $sub_id = 0;
            $post_id = create_post($post_title, $post_content, $user_id, $sub_id);

            if ($post_id) {
                header("Location: {$base_uri}/post/{$post_id}");
            }
        } else if (!is_null($user_id)) {
            $post_title = $_POST['title'];
            $post_content = $_POST['content'];
            $sub_id = $sub['id'];

            $post_id = create_post($post_title, $post_content, $user_id, $sub_id);

            if ($post_id) {
                header("Location: {$base_uri}/post/{$post_id}");
            }
        } else {
            $_SESSION['error_noauth_post'] = "You must be logged in to post";
            header("Location: {$base_uri}/{$sub['slug']}/create");
        }
    }
}
?>
<html>
    <head>
        <title>Torrit Create Post</title>
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
                <?php if($sub_id > 0){ ?>
                <a href="<?php echo $base_uri.'/t/'.$sub['slug']; ?>"><p class="sub-masthead-text"><?php echo '/t/'.$sub['name']; ?></p></a>
                <?php } else { ?>
                <p class="sub-masthead-text">FrontPage</p>
                <?php } ?>
            </div>
            <div id="main-posts">
                <?php
                if(!is_null($error_noauth))
                {
                    echo $error_noauth;
                }
                if($can_post == false && isset($_POST['title']) && isset($_POST['content']))
                {
                    echo '<div style="background-color: lightcoral; padding: 2%;"><p style="color:white; text-align: center;"><b>You failed the captcha</b></p></div>';
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
                    <?php

                    if($sub_id > 0)
                    {
                        $action = $base_uri.'/'.$sub['slug'].'/create';
                    }
                    else
                    {
                        $action = '/create/frontpage';
                    }

                    ?>
                    <form method="post" action="<?php echo $action; ?>">
                            <label><text class="torrit-text">Title</text></label><br>
                            <input type="text" class="torrit-large-input" name="title" required="" value="<?php if(isset($_POST['title'])) echo $_POST['title']; ?>">
                            <div class="clearfix"></div>
                            <label><text class="torrit-text">Content</text></label><br>
                            <textarea class="torrit-large-textarea" name="content" required=""><?php if(isset($_POST['content'])) echo $_POST['content']; ?></textarea>
                            <div class="clearfix"></div>
                            <div id="post-captcha">
                                <?php $captcha_id = substr(md5(time()), 0, 5); ?>
                                <div>
                                    <img src='/captcha/<?php echo $captcha_id; ?>'>
                                </div>
                                <input type="text" placeholder="Enter Captcha" name="post_captcha_input"/>
                                <div class="clearfix-comment"></div>
                                <text class="torrit-text"><small><i>not* case sensitive</i></small></text>

                            </div>
                        <div class="clearfix"></div><br>
                            <input type="submit" class="torrit-buttons" style="width: 19%; font-size: 12; text-align: center;" value="Submit Post" name="post"></input>

                    </form>
                </div>
            </div>
        </main>
            <div class="sidebar-subview">

                <div class="sub-sidebar-info">
                    <?php
                    $sub_letter = strtoupper(substr($sub['name'],0,1));
                    $subscriber_count = get_sub_subcriber_count($sub['id']);
                    $sub_elem = ($sub_id > 0) ? '
                            
                                                            <a href="/t/'.$sub['slug'].'" class="sub-sidebar-name">/t/'.$sub['name'].'</a>
                                                            <div class="clearfix"></div>
                                                            <div class="sub-member-count">'.$subscriber_count.' subscribers</div>
                                                            
                                                        ' : '';
                    ?>
                    <div class="sub-logo" style="background-color:<?php echo $sub['color_code']; ?>"><p class="subletter"><?php echo $sub_letter; ?></p></div>
                    <div class="clearfix"></div>
                    <?php echo $sub_elem; ?>
                </div>

                <div class="sidebar-button-container">
                    <?php if($sub_id > 0)
                          {
                            ?>
                    <a href="<?php echo $base_uri.'/'.$sub['slug'].'/create'; ?>"><button class="torrit-buttons" role="button">Submit Post</button></a>
                    <?php }  ?>
                    <div class="clearfix">
                        <?php
                        if($sub_id > 0)
                        {
                        $is_subbed = check_already_subbed($sub['id'], $user_id);
                        if($is_subbed)
                        {
                            echo '
                        <a href="/'.$sub['slug'].'/create/us"><button class="torrit-buttons" style="background-color: lightcoral" role="button">UnSubscribe</button></a>
                         ';
                        }
                        else
                        {
                            echo '
                         <a href="/'.$sub['slug'].'/create/s"><button class="torrit-buttons" role="button">Subscribe</button></a>
                         ';
                        }
                        ?>
                        <?php
                        $sub_id = $sub['id'];
                        if($user_id == $sub['owner'])
                        {
                            $manage_btn =  '
                                            <div class="clearfix"></div>
                                            <a href="'.$base_uri.'/t/'.$sub['slug'].'/manage"><button class="torrit-buttons" role="button">Manage Sub</button></a>
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