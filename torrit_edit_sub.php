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
    if($user_id !== $sub['owner'])
    {
        header("Location: home");
    }
    $sub_settings = get_sub_settings($sub_id);
    $posts_in_queue = get_post_queue($sub_id);

    $hover = null;
    $hover = adjustBrightness($sub['color_code'], 20);
}
if(isset($_POST['sub_name']) && isset($_POST['sub_url']))
{
    $sub_id = $_GET['sid'];
    $sub = get_sub_by_name($sub_id);
    $sub_id = $sub['id'];
    $sub_updated = update_sub($sub_id, $_POST['sub_name'], $_POST['sub_url'], $_POST['sub_color_code'], $_POST['sub_masthead_color'], $_POST['sub_sidebar'], $_POST['sub_postrules'], $_POST['approve_posts'], $user_id);

    if($sub_updated){
        header("Location: {$base_uri}/t/{$_POST['sub_url']}/manage");
    }
    else
    {
        echo '<div style="background-color: lightcoral;">Error Updating Sub</div>';
    }
}
?>
<html>
<head>
    <title>Torrit Edit Sub</title>
    <link rel="stylesheet" href="/public/bootstrap/css/bootstrap.css">
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
        <div id="main-posts">
            <div class="clearfix-post"></div>
            <div class="create-post-container">
                <form method="post" action="<?php echo $base_uri.'/t/'.$sub['slug'].'/manage'; ?>">
                    <label><text class="torrit-text">Sub Name</text></label><br>
                    <input type="text" class="torrit-large-input" name="sub_name" required="" value="<?php echo $sub['name']; ?>">
                    <div class="clearfix"></div>
                    <label><text class="torrit-text">Sub URL</text></label><br>
                    <input type="text" class="torrit-large-input" name="sub_url" required="" value="<?php echo $sub['slug']; ?>">
                    <div class="clearfix"></div>
                    <label><text class="torrit-text">Color Code (links and buttons)</text></label><br>
                    <input type="text" class="torrit-large-input" name="sub_color_code" value="<?php echo $sub['color_code']; ?>">
                    <div class="clearfix"></div>
                    <label><text class="torrit-text">Sub Masthead Color</text></label><br>
                    <input type="text" class="torrit-large-input" name="sub_masthead_color" value="<?php echo $sub['mast_theme']; ?>">
                    <div class="clearfix"></div>
                    <label><text class="torrit-text">Sidebar Content (Markdown - <a href="public/formatting.html">view formatting</a>)</text></label><br>
                    <textarea class="torrit-large-textarea" name="sub_sidebar"><?php echo $sub['sidebar']; ?></textarea>
                    <div class="clearfix"></div>
                    <label><text class="torrit-text">Post Rules (Markdown - <a href="public/formatting.html">view formatting</a>)</text></label><br>
                    <textarea class="torrit-large-textarea" name="sub_postrules"><?php echo $sub['post_rules']; ?></textarea>
                    <div class="clearfix-post"></div>
                    <label for="standard-select"><text class="torrit-text">Approve Posts</text></label><br>
                    <div class="select">
                        <select id="standard-select" name="approve_posts">
                            <?php

                             if($sub_settings['approve_posts']){
                                 echo '<option value="1" selected>true</option>';
                                 echo '<option value="0" >false</option>';
                             }
                             else
                             {
                                 echo '<option value="0" selected>false</option>';
                                 echo '<option value="1" >true</option>';
                             }
                            ?>
                        </select>
                    </div>
                    <div class="clearfix"></div>
                    <input type="submit" class="torrit-buttons" style="width: 19%; font-size: 12; text-align: center;" value="Update Sub" name="post"></input>
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
            <a href="<?php echo $base_uri.'/t/'.$sub['slug']; ?>" class="sub-sidebar-name">/t/<?php echo $sub['name']; ?></a>
            <div class="clearfix"></div>
            <div class="sub-member-count">280,020 subscribers</div>
        </div>
        <div class="sidebar-button-container">
            <a href="<?php echo $base_uri.'/t/'.$sub['slug'].'/manage/post_queue'; ?>"><button class="torrit-buttons" role="button">Post Queue [ <?php echo count($posts_in_queue); ?> ]</button></a>
            <div class="clearfix"></div>
            <a href="torrit_sub_mods.php?sid=<?php echo $sub_id; ?>"><button class="torrit-buttons" role="button">Mod Management</button></a>
            <div class="clearfix"></div>
            <a href="torrit_sub_users.php?sid=<?php echo $sub_id; ?>"><button class="torrit-buttons" role="button">User Management</button></a>
    </div>
</body>
</html>
