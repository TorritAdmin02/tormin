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
$sub_id = $_GET['sid'];
$sub = get_sub_by_name($sub_id);
$sub_id = $sub['id'];
if($sub && isset($sub['id']))
{
    $verify = verify_sid($sub_id);
    if (!$verify) {
        header("Location: /home");
    }
}
else
{
        header("Location: /home");
}

$sub_posts = get_posts_for_sub($sub_id);
$subscriber_count = get_sub_subcriber_count($sub_id);
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
            header("Location: /t/".$sub['slug']);
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
            header("Location: /t/".$sub['slug']);
        }
    }
}
if(isset($_SESSION['error']))
{
    echo '<div style="background-color: lightcoral;">'.$_SESSION['error_message'].'</div>';
    unset($_SESSION['error']);
}
$hover = null;
$hover = adjustBrightness($sub['color_code'], 20);
?>
<html>
<noscript><style>head { visibility: hidden; }</style></noscript>
<noscript><style>body { visibility: hidden; }</style></noscript>
    <head>
        <title>Torrit Sub - <?php echo $sub['name']; ?></title>
        <link rel="stylesheet" href="/public/bootstrap/css/bootstrap.css">
        <link rel="stylesheet" href="/public/style.css">
        <style>
            .post-links {
                color: <?php echo $sub['color_code']; ?>
            }
            .post a {
                color: <?php echo $sub['color_code']; ?>
            }
            .sub-mast-link {
                color: white;
                text-decoration: none;
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
                    <li><a href="<?php echo $base_uri.'/frontpage'; ?>">frontpage</a></li>
                    <li><a href="<?php echo $base_uri.'/t/torrit'; ?>">torrit</a></li>
                    <li><a href="<?php echo $base_uri.'/contact'; ?>">contact</a></li>
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
                <p class="sub-masthead-text"><a class="sub-mast-link" href="<?php echo $base_uri.'/t/'.$sub['slug']; ?>"><?php echo '/t/'.$sub['name']; ?></a></p>
            </div>
            <div id="main-posts-sub">
                <?php foreach($sub_posts as $post){
                    $comment_count = get_comment_count($post['id']);
                    $user = get_user($post['user_id']);
                    $username = $user['username'];
                    $sub_href = $base_uri.'/t/'.$sub['slug'];
                    $post_body = $bbcode->renderPlain($post['body']);
                    $post_snippet = substr($post_body, 0, 55).'..';
                    $score_card = '<div class="score_card"><p class="score_text">score</p>'.$post['score'].'</div>';
                    echo '
                    <div class="item-sub">
                    '.$score_card.'
                    <div class="post">
                    <p class="title">'.$post['title'].' &#183 <font class="username_post"> by <a href="/u/'.$username.'" class="username_link">/u/'.$username.'</a></font></p>
                    <p class="sub_p">in<subname class="subname"> <a href='.$sub_href.'>/t/'.$sub['slug'].'</a></subname></p>
                    <small>'.$post_snippet.'</small><br>
                    <div class="clearfix-comment"></div>
                    <div><small><b><a class="post-links" href="'.$base_uri.'/post/'.$post['id'].'">'.$comment_count.' comments</a><a class="post-links" href="'.$base_uri.'/post/'.$post['id'].'/1"> upvote</a> <a class="post-links" href="'.$base_uri.'/post/'.$post['id'].'/0">downvote</a></b></small></div>
                    </div>
                    </div>';
                }
                ?>
            </div>
        </main>
        <div class="sidebar-subview">

        <div class="sub-sidebar-info">
            <?php
                $sub_letter = strtoupper(substr($sub['name'],0,1));
            ?>
            <div class="sub-logo" style="background-color:<?php echo $sub['color_code']; ?>"><p class="subletter"><?php echo $sub_letter; ?></p></div>
            <div class="clearfix-post"></div>
            <a href="<?php echo $base_uri.'/t/'.$sub['slug']; ?>" class="sub-sidebar-name">/t/<?php echo $sub['name']; ?></a>
            <div class="clearfix"></div>
            <div class="sub-member-count"><?php echo $subscriber_count;?> subscribers</div>
        </div>
            <div class="sidebar-button-container">
                <a href="<?php echo $base_uri.'/'.$sub['slug'].'/create'; ?>"><button class="torrit-buttons" role="button">Submit Post</button></a>
                <div class="clearfix"></div>
                <?php
                $is_subbed = check_already_subbed($sub_id, $user_id);
                if($is_subbed)
                {
                    echo '
                        <a href="/t/'.$sub['slug'].'/us"><button class="torrit-buttons" style="background-color: lightcoral" role="button">UnSubscribe</button></a>
                         ';
                }
                else
                {
                    echo '
                         <a href="/t/'.$sub['slug'].'/s"><button class="torrit-buttons" role="button">Subscribe</button></a>
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
                    ?>
        </div>
            <div class="sidebar-content-container">
            <?php
            if($sub['sidebar'])
            {
                $sidebar_html = $bbcode->render($sub['sidebar'], true, false);
                echo $sidebar_html;
            }
            ?>
                <br>
                <div class="created_by">
                    <?php
                    $created_by = get_user($sub['owner']);
                    ?>
                    <text class="torrit-text"><small>created by <a href="/u/<?php echo $created_by['username']; ?>"><?php echo '/u/'.$created_by['username']; ?></a></small></text>
                </div>
            </div>
      </div>
            <noscript><style>head { visibility: visible; }</style></noscript>
            <noscript><style>body { visibility: visible; }</style></noscript>
    </body>
</html>