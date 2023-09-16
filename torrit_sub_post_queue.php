<?php
session_start();
include_once('torrit_functions.php');
if(isset($_GET['sid']))
{
    $user_id = $_SESSION['user_id'];
    $sub_id = $_GET['sid'];
    $sub = get_sub_by_name($sub_id);
    $sub_id = $sub['id'];

    $verify_sub = verify_sid($sub_id);
    if(!$verify_sub){
        header("Location: home");
    }
    $sub = get_sub($sub_id);
    if($user_id !== $sub['owner'])
    {
        header("Location: home");
    }
    $sub_settings = get_sub_settings($sub_id);
    $queue_posts = get_post_queue($sub_id);
    $hover = null;
    $hover = adjustBrightness($sub['color_code'], 20);

    if(isset($_GET['pid']) && isset($_GET['a']))
    {
        $post_id = $_GET['pid'];
        $action  = $_GET['a'];

        $verify = verify_pid($post_id);
        if(!$verify)
        {
            header("Location: home");
        }
        if($a >= 0 && $a < 2)
        {
            $processed = manage_post($post_id, $action);
            if($processed)
            {
                header("Location: {$base_uri}/t/{$sub['slug']}/manage/post_queue");
            }
        }
    }
}

?>
<html>
<head>
    <title>Torrit Edit Sub</title>
    <link rel="stylesheet" href="/public/sub_edit.css">
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
                <a href="<?php echo $base_uri.'/home'; ?>">
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
            <div id="main-posts-sub">
                <?php foreach($queue_posts as $post){
                    $user = get_user($post['user_id']);
                    $username = $user['username'];
                    $sub_href = '/torrit_view_sub.php?sid='.$sub['id'];

                    echo '
                    <div class="item">
                    <div class="post">
                    <p class="title">'.$post['title'].' &#183 <font class="username_post"> by <a href="/u/'.$username.'" class="username_link">/u/'.$username.'</a></font></p>
                    <p class="sub_p">in<subname class="subname"> <a href='.$sub_href.'>/t/'.$sub['slug'].'</a></subname></p>
                    <small>'.substr($post['body'], 0, 55).'..</small><br>
                   <div><b><a href="'.$base_uri.'/post/'.$post['id'].'">view</a></b> | <b><a href="'.$base_uri.'/t/'.$sub['slug'].'/manage/post_queue/'.$post['id'].'/1">approve</a></b> | <b><a href="'.$base_uri.'/t/'.$sub['slug'].'/manage/post_queue/'.$post['id'].'/0">reject</a></b></div>
                    </div>
                    <hr>
                    </div>';
                }
                if(count($queue_posts) < 1){
                    echo '<div class="item">
                             <div class="post">
                                 <p class="torrit-text"> No posts in queue </p>
                            </div>
                         </div>';
                }
                ?>
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
            <a href="<?php echo $base_uri.'/t/'.$sub['slug']; ?>" class="sub-sidebar-name">/t/<?php echo $sub['name']; ?></a>
            <div class="clearfix"></div>
            <div class="sub-member-count">280,020 subscribers</div>
        </div>
        <div class="sidebar-button-container">
            <a href="torrit_edit_sub.php?sid=<?php echo $sub_id; ?>"><button class="torrit-buttons" role="button">Manage Sub</button></a>
            <div class="clearfix"></div>
            <a href="torrit_sub_mods.php?sid=<?php echo $sub_id; ?>"><button class="torrit-buttons" role="button">Mod Management</button></a>
            <div class="clearfix"></div>
            <a href="torrit_sub_users.php?sid=<?php echo $sub_id; ?>"><button class="torrit-buttons" role="button">User Management</button></a>
        </div>
</body>
</html>
