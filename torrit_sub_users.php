<?php
session_start();
include_once('torrit_functions.php');
if(isset($_GET['sid']))
{
    $user_id = $_SESSION['user_id'];
    $sub_id = $_GET['sid'];

    $verify_sub = verify_sid($sub_id);
    if(!$verify_sub){
        header("Location: home");
    }
    $sub = get_sub($sub_id);
    if($user_id !== $sub['owner'])
    {
        header("Location: home");
    }

    $posts_in_queue = get_post_queue($sub_id);
    $hover = null;
    $hover = adjustBrightness($sub['color_code'], 20);

    if(isset($_GET['uid']) && isset($_GET['a']))
    {
        $user_to_ban = $_GET['uid'];
        $action = $_GET['a'];
        $verify_user = verify_uid($user_to_ban);
        if(($action >=0 && $action <=1) && $verify_user)
        {
            $mod_action = toggle_user_sub_ban_status($sub_id, $user_to_ban, $action, $user_id);
            header("Location: torrit_sub_users.php?sid=".$sub_id);
        }

    }
    $search_results = null;
    if(isset($_POST['search_users']))
    {
        $username_str = $_POST['search_users'];
        $user_results = search_users($username_str);
        $search_results = null;
        if($user_results)
            $search_results = $user_results;
        else
            $search_results = "No users found";
    }
}

?>
<html>
<head>
    <title>Torrit Manage Sub Users</title>
    <link rel="stylesheet" href="public/style.css">
    <style>
        a {
            color: <?php echo $sub['color_code']; ?>
        }
        .torrit-buttons {
            background-color: <?php echo $sub['color_code']; ?>
        }
        .logo-container {
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
                    <form method="post" action="torrit_sub_users.php?sid=<?php echo $sub_id ?>">
                        <label><text class="torrit-text">Search Users</text></label><br>
                        <input type="text" class="torrit-large-input" name="search_users" value="">
                        <div class="clearfix"></div>
                        <input type="submit" class="torrit-buttons" style="width: 19%; font-size: 12; text-align: center;" value="search" name="search"></input>
                    </form>
                </div>
            <?php if(is_array($search_results))
                {
                  echo '
                  
                  <div class="user-grid">
                  <span>Username</span>
                  <span>Score</span>
                  <span>Is Active</span>
                  <span>Joined</span>
                  <span>Is Banned</span>
                  <span>Action</span>
                  
                  ';
                  foreach($search_results as $result)
                  {
                      $user_active = ($result['active'] > 0) ? 'Yes' : 'No';
                      $joined = date('M d Y',strtotime($result['created_at']));
                      $uid = $result['id'];
                      $banned_status = $result['banned_status'];
                      $banned_status_text = '<text class="torrit-text">Not Banned</text>';
                      $button_text = 'ban user';
                      $action = 1;
                      if($result['sub_id'] !== null)
                      {
                          if(($sub_id == $result['sub_id']) && $banned_status > 0)
                          {
                              $banned_status_text = '<text style="color:red">Banned</text>';
                              $button_text = 'unban user';
                              $action = 0;
                          }
                      }
                      echo '
                      <span>'.$result['username'].'</span>
                      <span>'.$result['score'].'</span>
                      <span>'.$user_active.'</span>
                      <span>'.$joined.'</span>
                      <span>'.$banned_status_text.'</span>
                      <span><a href="torrit_sub_users.php?sid='.$sub_id.'&uid='.$uid.'&a='.$action.'">'.$button_text.'</a></span>
                           ';
                  }
                }
                ?>
        </div>
        <div class="clearfix-post"></div>
    </main>
    <div class="sidebar-subview">
        <div class="sub-sidebar-info">
            <?php
            $sub_letter = strtoupper(substr($sub['name'],0,1));
            ?>
            <div class="sub-logo" style="background-color:<?php echo $sub['color_code']; ?>"><p class="subletter"><?php echo $sub_letter; ?></p></div>
            <div class="clearfix"></div>
            <a href="torrit_view_sub.php?sid=<?php echo $sub['id']; ?>" class="sub-sidebar-name">/t/<?php echo $sub['name']; ?></a>
            <div class="clearfix"></div>
            <div class="sub-member-count">280,020 subscribers</div>
        </div>
        <div class="sidebar-button-container">
            <a href="torrit_sub_post_queue.php?sid=<?php echo $sub_id; ?>"><button class="torrit-buttons" role="button">Post Queue [ <?php echo count($posts_in_queue); ?> ]</button></a>
            <div class="clearfix"></div>
            <a href="torrit_edit_sub.php?sid=<?php echo $sub_id; ?>"><button class="torrit-buttons" role="button">Manage Sub</button></a>
            <div class="clearfix"></div>
            <a href="torrit_sub_mods.php?sid=<?php echo $sub_id; ?>"><button class="torrit-buttons" role="button">Mod Management</button></a>
        </div>
</body>
</html>
