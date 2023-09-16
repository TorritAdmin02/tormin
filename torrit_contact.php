<?php
session_start();
include_once('torrit_functions.php');


if(isset($_POST['title']) && isset($_POST['content']))
{
    $post_title = $_POST['title'];
    $post_content = $_POST['content'];
    $user_id = $_SESSION['user_id'];
    $message_id = contact($post_title, $post_content, $user_id);

    if($message_id)
    {
        header("Location: home?mid={$message_id}");
    }
}
?>
<html>
<head>
    <title>Torrit Contact</title>
    <link rel="stylesheet" href="/public/bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="/public/style.css">
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
                <li><a href="frontpage">frontpage</a></li>
                <li><a href="t/torrit">torrit</a></li>
                <li><a href="contact">contact</a></li>
                <li><a href="<?php echo '/search'; ?>"><text style="color:lightcoral">search <span class="glyphicon glyphicon-search" aria-hidden="true"></span></text></a></li>
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
            <div class="clearfix-create-post"></div>
            <div class="create-post-container">
                <p><text class="torrit-text">This will be sent directly to Torrit Admins</text></p>
                <form method="post" action="torrit_contact.php">
                    <label><text class="torrit-text">Message Header</text></label><br>
                    <input type="text" class="torrit-large-input" name="title" required="" value="">
                    <div class="clearfix"></div>
                    <label><text class="torrit-text">Message</text></label><br>
                    <textarea class="torrit-large-textarea" name="content" required=""></textarea>
                    <div class="clearfix"></div>
                    <input type="submit" class="torrit-buttons" style="width: 19%; font-size: 12; text-align: center;" value="send message" name="post"></input>
                </form>
            </div>
        </div>
    </main>
    <div class="sidebar-subview">
        <div class="sidebar-button-container">

        </div>
    </div>
</body>
</html>