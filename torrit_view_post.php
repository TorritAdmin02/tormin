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
$user_id = null;
if(isset($_SESSION['user_id']))
{
    $user_id = $_SESSION['user_id'];
}
if(isset($_GET['r'])&& !is_null($user_id))
{
    update_notification($_GET['r']);
}
$post_id = $_GET['pid'];
$error_noauth = null;
$post = get_post($post_id);
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
if(isset($_SESSION['error_noauth']))
{
    $error_message = $_SESSION['error_noauth'];
    unset($_SESSION['error_noauth']);
    $error_noauth = '<div style="background-color: lightcoral; padding: 2%;"><p style="color:white; text-align: center;"><b>'.$error_message.'</b></p></div>';
}

if (isset($_SESSION['success_comment'])) {
    $success_message = $_SESSION['success_comment'];
    unset($_SESSION['success_comment']);
    $success_notice = '<div style="background-color: #36bd36; padding-top: 2%;"><p style="color:white; text-align: center;"><b>'.$success_message.'</b></p></div>';
}

if(isset($_GET['ac']))
{
    $base_uri ='';
    $action = $_GET['ac'];
    if($user_id == null)
    {
        header("Location: {$base_uri}/login");
        exit();
    }
    if($action == 3)
    {
        unset_vote($post_id, $user_id);
    }

    else
    {
        if($action == 's')
        {
            if($user_id == null)
            {
                $_SESSION['error'] = "Must login to subscribe";
                header("Location: /post/".$post_id);
            }
            $verify_user = verify_uid($user_id);
            if($verify_user)
            {
                $subscribe = subscribe_to_sub($sub['id'], $user_id);
                header("Location: /post/".$post_id);
            }
        }
        else if($action == 'us')
        {
            if($user_id == null)
            {
                $_SESSION['error'] = "Must login";
                header("Location: /post/".$post_id);
            }
            $verify_user = verify_uid($user_id);
            if($verify_user)
            {
                $unsubscribe = unsubscribe($sub['id'], $user_id);
                header("Location: /post/".$post_id);
            }
        }
        else if (intval($action) === 0 || intval($action) === 1) {
            $vote = submit_post_vote($post_id, $user_id, $action);
        }
    }

}

if(isset($_GET['comvt']))
{
    if($user_id == null)
    {
        header("Location: {$base_uri}/login");
        exit();
    }
    $comment_id = $_GET['cid'];
    $comment_action = $_GET['comvt'];
    $user_id = $_SESSION['user_id'];

    if ($comment_action >= 0 && $comment_action <= 1)
    {
        submit_comment_vote($post_id, $comment_id, $user_id, $comment_action);
    }
}

$post_comments = get_comments_for_post($post_id);
$post = get_post($post_id);
$hover = null;
$hover = adjustBrightness($sub['color_code'], 20);

$can_comment = false;
if(isset($_POST['create_comment']))
{
    $comment_pid = $_POST['comment_pid'];
    if(isset($_POST['comment_captcha_input']))
    {
        $captcha_input = $_POST['comment_captcha_input'];
        if($_SESSION['secure'] == $captcha_input)
        {
            $can_comment = true;
        }
    }
    if($can_comment)
    {
        $base_uri = '';
        $user_id = null;
        if ($sub_id == 0 && $sub['slug'] == 'frontpage') {
            if (isset($_SESSION['user_id'])) {
                $user_id = $_SESSION['user_id'];
            } else {
                $user_id = 0;
            }
            $comment = $_POST['create_comment'];
            $create_comment = create_comment($comment, $post_id, $sub['id'], $comment_pid, $user_id);
            if ($create_comment) {
                header("Location: {$base_uri}/post/{$post_id}");
            }
        } else {
            if (isset($_SESSION['user_id'])) {
                $user_id = $_SESSION['user_id'];
            }
            if (!is_null($user_id)) {
                $comment = $_POST['create_comment'];
                $create_comment = create_comment($comment, $post_id, $sub['id'], $comment_pid, $user_id);
                if ($create_comment) {
                    $_SESSION['success_comment'] = 'comment posted';
                    header("Location: {$base_uri}/post/{$post_id}");
                }
            } else {
                $_SESSION['error_noauth'] = "You must be logged in to comment";
                header("Location: {$base_uri}/post/{$post_id}");
            }
        }
    }
}

$subscriber_count = get_sub_subcriber_count($sub['id']);

if(isset($_SESSION['error']))
{
    echo '<div style="background-color: lightcoral;">'.$_SESSION['error_message'].'</div>';
    unset($_SESSION['error']);
}

?>
<html>
<noscript><style>body { visibility: hidden; }</style></noscript>
<noscript><style>head { visibility: hidden; }</style></noscript>
    <head>
        <title>Torrit Post - <?php echo $post['title']; ?></title>
        <link rel="stylesheet" href="/public/bootstrap/css/bootstrap.css">
        <link rel="stylesheet" href="/public/style.css">
        <style>
            a {
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
            if(!is_null($error_noauth))
            {
                echo $error_noauth;
            }
            if($can_comment == false && isset($_POST['create_comment']))
            {
                echo '<div style="background-color: lightcoral; padding-top: 2%;"><p style="color:white; text-align: center;"><b>You failed the captcha</b></p></div>';
            }
            else if(isset($success_message))
            {
                echo $success_notice;
            }
            ?>
            <?php
                $mast_theme = $sub['mast_theme'];
            ?>
            <div class="sub-masthead-post" style="background-color: <?php echo $mast_theme; ?>">
                <p class="sub-masthead-text"><a class="sub-mast-link" href="<?php echo $base_uri.'/t/'.$sub['slug']; ?>"><?php echo '/t/'.$sub['name']; ?></a></p>
            </div>
            <div id="main-posts">
               <?php
              $comment_count = get_comment_count($post['id']);
              $user = get_user($post['user_id']);
              $username = $user['username'];
              $post_user_id = $user['id'];
               if($post['sub_id'] == 0)
               {
                   $sub_link = '<text class="torrit-text"><small>frontpage</small></text>';
               }
               else
               {
                   $sub = get_sub($post['sub_id']);
                   $sub_href = $base_uri . '/t/' . $sub['slug'];
                   $sub_link = '<a href=' . $sub_href . '>/t/' . $sub['slug'] . '</a>';
               }

              $post_body = nl2br($bbcode->render($post['body'], true, false));
              $score_card = '<div class="score_card_post"><p class="post_score_text">score</p>'.$post['score'].'</div>';
              $edit_link = null;
              if($post['user_id'] == $user_id && $user_id > 0)
              {
                  $edit_link = ' &nbsp<a href="/post/'.$post['id'].'/edit">edit</a>';
              }
               echo '
               <div class="item-post">
               '.$score_card.'
               <div class="post">
               <p class="post_title">'.$post['title'].' <div class="clearfix-post"><font class="post_username_post"> <font class="sub_p">by </font><a href="/u/'.$username.'" class="username_link">/u/'.$username.'</a><font class="sub_p"> in</font> <subname class="subname">'.$sub_link.'</subname> &#183 <text class="sub_p"> '.time_elapsed_string($post['created_at']).' </text></font></p></div>
               <div class="clearfix"></div>
               <font class="post-font"><text class="torrit-text">'.$post_body.'</text></font><br>
               <div class="clearfix"></div>
               <div><small><b><a href="'.$base_uri.'/post/'.$post['id'].'">'.$comment_count.' comments</a>  &nbsp&nbsp<a href="'.$base_uri.'/post/'.$post['id'].'/1">upvote</a> &nbsp<a href="'.$base_uri.'/post/'.$post['id'].'/0">downvote</a> &nbsp<a href="'.$base_uri.'/post/'.$post['id'].'/3">unset-vote</a>'.$edit_link.'</b></small></div>
               </div>
               </div>';
                ?>
            </div>

            <div class="clearfix-comment"></div>

            <h3 class="torrit-text">Comments</h3>

            <form action="<?php echo $base_uri.'/post/'.$post_id; ?>" method="post" class="torrit-comment">
                <textarea class="comment-textarea" name="create_comment" placeholder="Respond to post or comment.."><?php if(isset($_POST['create_comment']) && $comment_pid == 0) echo $_POST['create_comment']; ?></textarea>
                <div class="clearfix"></div>
                <div id="comment-captcha">
                    <?php $captcha_id = substr(md5(time()), 0, 5); ?>
                    <div>
                        <img src='/captcha/<?php echo $captcha_id; ?>'>
                    </div>
                    <input type="text" placeholder="Enter Captcha" name="comment_captcha_input"/>
                    <div class="clearfix-comment"></div>
                    <text class="torrit-text"><small><i>not* case sensitive</i></small></text>

                </div>
                <div class="clearfix"></div><br>
                <input type="submit" class="torrit-buttons" style="width: 19%; font-size: 12; text-align: center;" value="Submit Comment"></input>
                <input type="hidden" name="comment_pid" value="0" />
            </form>
            <div class="clearfix-comment"></div>
            <div class="comm">
               <?php
                
                function print_child_comments($comments, $level, $bbcode, $comment_pid)
                {
                    foreach($comments as $comment)
                    {        
                        $user = get_user($comment['user_id']);
                        $username = $user['username'];
                        //$comment_content = $bbcode->render($comment['comment'], true, false);
                        $comment_content = nl2br($bbcode->render($comment['comment'], true, false));
                        $base_uri = '';
                        $captcha_id = substr(md5(time()), 0, 5);
                        $fill = '';
                        if ((isset($_POST['create_comment']) && $comment_pid == $comment['id']))
                        {
                            $fill = $_POST['create_comment'];
                        }
                        echo '
                        <div class="item" style="border-left: 1px dashed;">
                            <div class="post">
                                <small><a href="/u/'.$username.'" class="username_link">/u/'.$username.'</a> &#183 <text class="sub_p"><small> '.time_elapsed_string($comment['created_at']).' </small></text></small>
                                <div class="clearfix-comment"></div>
                                <small class="post-font">'.$comment_content.'</small>
                                <div class="clearfix"></div>
                                <div class="comment-ctrl-bar"><small><a href><text class="score">score '.$comment['score'].'</text></a> &#183 <a href="#tc-'.$comment['id'].'">reply</a> &#183 <a href="'.$base_uri.'/post/'.$comment['post_id'].'/'.$comment['id'].'/1"> up vote </a> &#183 <a href="'.$base_uri.'/post/'.$comment['post_id'].'/'.$comment['id'].'/0"> down vote </a></small></div>
                            </div>
                            <div class="clearfix-comment"></div>
                            <form action="'.$base_uri.'/post/'.$comment['post_id'].'" method="post" id="tc-'.$comment['id'].'" style="margin-left: '.($level + 10).'">
                                <textarea class="comment-textarea-small" name="create_comment" placeholder="Respond to '.$username.'..">'.$fill.'</textarea>
                                <div class="clearfix"></div>
                                        <div id="comment-captcha">
                                            <div>
                                                <img src="/captcha/'.$captcha_id.'">
                                            </div>
                                            <input type="text" placeholder="Enter Captcha" name="comment_captcha_input"/>
                                            <div class="clearfix-comment"></div>
                                            <text class="torrit-text"><small><i>not* case sensitive</i></small></text>
                                        </div>
                                    <div class="clearfix"></div><br>
                                <input type="submit" class="torrit-buttons" style="width: 15%; padding-left: 5; font-size: 10; text-align: center;" value="Submit Comment" name="comment"></input>
                                <input type="hidden" name="comment_pid" value="'.$comment['id'].'" />
                            </form>
                        <div class="clearfix"></div>
                        ';

                        if(array_key_exists('children', $comment))
                        {
                            print_child_comments($comment['children'], $level, $bbcode, $comment_pid);
                        }
                        echo '</div>';
                    }
                }
                
                
                foreach($post_comments as $comment)
                {
                    $user = get_user($comment['user_id']);
                    $username = $user['username'];
                    // $comment_content = $bbcode->render($comment['comment'], true, false);
                    $comment_content = nl2br($bbcode->render($comment['comment'], true, false));
                    $level = 0;
                    $captcha_id = substr(md5(time()), 0, 5);
                    $fill = '';
                    if ((isset($_POST['create_comment']) && $comment_pid == $comment['id']))
                    {
                        $fill = $_POST['create_comment'];
                    }
                        echo '
                        <div class="clearfix-comment"></div>
                        <div id="main-comments">
                        <div class="item">
                            <div class="post">
                                <small><a href="/u/'.$username.'" class="username_link">/u/'.$username.'</a> &#183 <text class="sub_p"><small> '.time_elapsed_string($comment['created_at']).' </small></text></small>
                                <div class="clearfix-comment"></div>
                                <small class="post-font">'.$comment_content.'</small>
                                <div class="clearfix"></div>
                                <div class="comment-ctrl-bar"><small><a href><text class="score">score '.$comment['score'].'</text></a> &#183 <a href="#tc-'.$comment['id'].'">reply</a> &#183 <a href="'.$base_uri.'/post/'.$comment['post_id'].'/'.$comment['id'].'/1"> up vote </a> &#183 <a href="'.$base_uri.'/post/'.$comment['post_id'].'/'.$comment['id'].'/0"> down vote </a></small></div>
                            </div>
                            <div class="clearfix-comment"></div>
                            <form action="'.$base_uri.'/post/'.$post_id.'" method="post" id="tc-'.$comment['id'].'" style="margin-left: '.($level + 10).'">
                                <textarea class="comment-textarea-small" name="create_comment" placeholder="Respond to '.$username.'..">'.$fill.'</textarea>
                                <div class="clearfix"></div>
                                        <div id="comment-captcha">
                                            <div>
                                                <img src="/captcha/'.$captcha_id.'">
                                            </div>
                                            <input type="text" placeholder="Enter Captcha" name="comment_captcha_input"/>
                                            <div class="clearfix-comment"></div>
                                            <text class="torrit-text"><small><i>not* case sensitive</i></small></text>
                                        </div>
                                 <div class="clearfix"></div>
                                <input type="submit" class="torrit-buttons" style="width: 14%; padding-left: 5; font-size: 10; text-align: center;" value="Submit Comment" name="comment"></input>
                                <input type="hidden" name="comment_pid" value="'.$comment['id'].'" />
                             </form>
                            <hr class="comments-hr">
                            ';

                        if(array_key_exists('children', $comment))
                        {
                           print_child_comments($comment['children'], $level, $bbcode, $comment_pid);

                        } 
                        echo 
                        '
                            </div>
                        </div>
                        ';

                }
                
                ?>
                </div>
            </div>
        </main>
        <div class="sidebar-post">

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
            <div class="clearfix-post"></div>
            <?php echo $sub_elem; ?>
        </div>

            <div class="sidebar-button-container">
                <a href="<?php echo $base_uri.'/'.$sub['slug'].'/create'; ?>"><button class="torrit-buttons" role="button">Submit Post</button></a>
                <div class="clearfix">
                    <?php
                    if($sub_id > 0)
                    {
                        $is_subbed = check_already_subbed($sub['id'], $user_id);
                        if($is_subbed)
                        {
                            echo '
                            <a href="/post/'.$post_id.'/us"><button class="torrit-buttons" style="background-color: lightcoral" role="button">UnSubscribe</button></a>
                             ';
                        }
                        else
                        {
                            if($sub['id'] > 0)
                            {
                                echo '
                             <a href="/post/' . $post_id . '/s"><button class="torrit-buttons" role="button">Subscribe</button></a>
                             ';
                            }
                        }
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
        <noscript><style>head { visibility: visible; }</style></noscript>
        <noscript><style>body { visibility: visible; }</style></noscript>
    </body>
</html>