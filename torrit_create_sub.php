<?php
session_start();
include_once('torrit_functions.php');
if(!isset($_SESSION['user_id']))
{
    header("Location: /login");
}
$can_create = false;
if(isset($_POST['sub_name']) && isset($_POST['sub_url']))
{
    if(isset($_POST['sub_captcha_input']))
    {
        $captcha_input = $_POST['sub_captcha_input'];
        if($_SESSION['secure'] == $captcha_input)
        {
            $can_create = true;
        }
    }
    if($can_create)
    {
        $user_id = $_SESSION['user_id'];
        $sub_id = create_sub($_POST['sub_name'], $_POST['sub_url'], $_POST['sub_color_code'], $_POST['sub_masthead_color'], $_POST['sub_sidebar'], $_POST['sub_postrules'], $user_id);
        $sub_uri = $_POST['sub_url'];

        if ($sub_id) {
            header("Location: {$base_uri}/t/{$sub_uri}");
        } else {
            echo '<div style="background-color: lightcoral;">Error Creating Sub</div>';
        }
    }
}
?>
<html>
<head>
    <title>Torrit Create Sub</title>
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
            <?php
            if($can_create == false && isset($_POST['sub_name']) && isset($_POST['sub_url']))
            {
            echo '<div style="background-color: lightcoral; padding: 2%;"><p style="color:white; text-align: center;"><b>You failed the captcha</b></p></div>';
            }
            ?>
            <div class="clearfix-create-post"></div>
            <div class="create-post-container">
                <h3><text class="torrit-text">Create a Torrit Community</text></h3>
                <form method="post" action="torrit_create_sub.php">
                    <label><text class="torrit-text">Sub Name</text></label><br>
                    <input type="text" class="torrit-large-input" name="sub_name" required="" value="<?php if(isset($_POST['sub_name'])) echo $_POST['sub_name']; ?>">
                    <div class="clearfix"></div>
                    <label><text class="torrit-text">Sub Slug<br><i>sub path ie.</i><b> /t/[slug]</b></text></label><br>

                    <input type="text" class="torrit-large-input" name="sub_url" required="" value="<?php if(isset($_POST['sub_url'])) echo $_POST['sub_url']; ?>" placeholder="hiddenservices">
                    <div class="clearfix"></div>
                    <label><text class="torrit-text">Color Code (links and buttons)</text></label><br>
                    <input type="text" class="torrit-large-input" name="sub_color_code" value="<?php if(isset($_POST['sub_color_code'])) echo $_POST['sub_color_code']; ?>">
                    <div class="clearfix"></div>
                    <label><text class="torrit-text">Sub Masthead Color</text></label><br>
                    <input type="text" class="torrit-large-input" name="sub_masthead_color" value="<?php if(isset($_POST['sub_masthead_color'])) echo $_POST['sub_masthead_color']; ?>">
                    <div class="clearfix"></div>
                    <label><text class="torrit-text">Sidebar Content (Markdown - <a href="public/formatting.html">view formatting</a>)</text></label><br>
                    <textarea class="torrit-large-textarea" name="sub_sidebar"><?php if(isset($_POST['sub_sidebar'])) echo $_POST['sub_sidebar']; ?></textarea>
                    <div class="clearfix"></div>
                    <label><text class="torrit-text">Post Rules (Markdown - <a href="public/formatting.html">view formatting</a>)</text></label><br>
                    <textarea class="torrit-large-textarea" name="sub_postrules"><?php if(isset($_POST['sub_postrules'])) echo $_POST['sub_postrules']; ?></textarea>
                    <div class="clearfix"></div>
                    <div id="post-captcha">
                        <?php $captcha_id = substr(md5(time()), 0, 5); ?>
                        <div>
                            <img src='/captcha/<?php echo $captcha_id; ?>'>
                        </div>
                        <input type="text" placeholder="Enter Captcha" name="sub_captcha_input"/>
                        <div class="clearfix-comment"></div>
                        <text class="torrit-text"><small><i>not* case sensitive</i></small></text>

                    </div>
                    <div class="clearfix"></div>
                    <input type="submit" class="torrit-buttons" style="width: 19%; font-size: 12; text-align: center;" value="Create Sub" name="post"></input>
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
