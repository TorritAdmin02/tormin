<?php
session_start();
include_once('torrit_functions.php');
require 'vendor/autoload.php';
$bbcode = new ChrisKonnertz\BBCode\BBCode();
extend_bbcode($bbcode);

$page = isset($_GET['p']) ? $_GET['p'] : 1;

$results_per_page = 10;
$page_first_result = ($page-1) * $results_per_page;

$num_posts = num_posts();
$number_of_pages = ceil ($num_posts / $results_per_page);

$home_posts = get_home_page_posts($page_first_result, $results_per_page);
if($page == 1)
{
    $stick_posts = get_sticky_home();
    foreach ($home_posts as $key => $post) {
        if ($post['sticky_home'] > 0) {
            unset($home_posts[$key]);
        }
    }
    $all_posts = array_merge($stick_posts, $home_posts);
}
else
{
    $all_posts = $home_posts;
}

if(isset($_GET['t']))
{
    $page = $_GET['t'];
    if($page == 'frontpage')
    {
        $all_posts = frontpage();
    }
}

$user_id = null;
$user_subscriptions = null;
if(isset($_SESSION['user_id']))
{
    $user_id = $_SESSION['user_id'];
    $user_subscriptions = get_all_subs_for_user($user_id);
}
?>
<html>
<noscript><style>body { visibility: hidden; }</style></noscript>
<noscript><style>head { visibility: hidden; }</style></noscript>
    <head>
        <title>Torrit Home</title>
        <link rel="stylesheet" href="/public/bootstrap/css/bootstrap.css">
        <link rel="stylesheet" href="/public/style.css">
    </head>
    <style>
        .sublist-links {
            color: #34495e;
            font-weight: bold;
            font-size: 13;
            font-family: roboto,helvetica,sans-serif,arial,verdana,tahoma;
        }
    </style>
    <body>
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
                                <a href="login">login</a>
                                &#8226
                                <a href="register">register</a>
                                ';
                        }
                    ?>
                </div>
        </header>
       <main>
           <div class="torrit-announcements"><p style="color:white; text-align: center;"><b>all features re-enabled</b></p></div>
           <div class="clearfix"></div>

           <div id="main-posts-home">
                <?php foreach($all_posts as $post){
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

                    $post_body = $bbcode->renderPlain($post['body']);
                    $post_title = wordwrap($post['title'], 65, "<br />\n");
                    $post_snippet = substr($post_body, 0, 55).'..';
                    $score_card = '<div class="score_card"><p class="score_text">score</p>'.$post['score'].'</div>';
                    $sticky_flair = null;
                    if($post['sticky_home'] > 0)
                    {
                        $sticky_text = (empty($post['sticky_content'])) ? 'sticky post' : $post['sticky_content'];
                        $sticky_color = (empty($post['sticky_theme'])) ? 'lightcoral' : $post['sticky_theme'];
                        $sticky_flair = '<sticky class="sticky" style="background-color: '.$sticky_color.'"><text class="sticky-text">'.$sticky_text.'</text></sticky>';
                    }
                    echo '
                    <div class="item-home">
                    '.$score_card.'
                    <div class="post">
                    <p class="title">'.$post_title.' '.$sticky_flair.'<br> <text class="home-post-links"><font class="username_post"> by <a href="u/'.$username.'" class="username_link">/u/'.$username.'</a>  &#183 <text class="sub_p"><small> '.time_elapsed_string($post['created_at']).' in<subname class="subname"> '.$sub_link.' </subname></text></small></font></text></p>
                    <div class="clearfix-home"></div>
                    <small>'.$post_snippet.'</small><br>
                    <div class="clearfix-comment"></div>
                    <div><small><b><a class="post-links" href="'.$base_uri.'/post/'.$post['id'].'">'.$comment_count .' comments</a><a class="post-links" href="'.$base_uri.'/post/'.$post['id'].'/1"> upvote</a><a class="post-links" href="'.$base_uri.'/post/'.$post['id'].'/0"> downvote</a></b></small></div>
                    </div>
                    </div>';
                }
                echo '<div class="pages">';
                for($page = 1; $page<= $number_of_pages; $page++)
                {
                    echo '<a class="page-link" href = "/home/p=' . $page . '">' . $page . ' </a>';
                }
                ?>
                </div>
            </div>
        </main>
        <div class="sidebar">
            <div class="sidebar-button-container">
                <a class="torrit-buttons" style="background-color: lightcoral;" href="/create/frontpage"> Create Frontpage Post</a>
                <div class="clearfix"></div>
                <a class="torrit-buttons" href="create_sub"> Create Torrit Sub</a>
                <div class="clearfix"></div>
                <a class="torrit-buttons" href="/discover"> Find Torrit Sub</a>
                <div class="clearfix"></div>
                <?php

                if(isset($_SESSION['user_id']))
                {
                    echo '<div class="sub-list">';
                    echo '<text class="torrit-text">Your Subscriptions</text>';
                    echo '<div class="clearfix"></div>';
                    if(!is_null($user_subscriptions))
                    {
                        foreach($user_subscriptions as $subscription)
                        {
                            $subscriber_count = get_sub_subcriber_count($subscription['id']);
                            $sub_letter = strtoupper(substr($subscription['name'],0,1));
                            echo '
                                <div style="display: inline-block;">
                                    <div class="sub-logo-small" style="background-color:'.$subscription['color_code'].'"><p class="subletter-small">'.$sub_letter.'</p></div>
                                </div>
                                <div style="display: inline-block;">   
                                    <a class="sublist-links" href="'.$base_uri.'/t/'.$subscription['slug'].'">/t/'.$subscription['slug'].'</a><br>
                                    <div class="clearfix"></div>
                                    <div class="sub-member-count">'.$subscriber_count.' subscribers</div>
                               </div>
                                <div class="clearfix"></div>
                                 ';
                        }
                    }
                    echo '</div>';
                }
                ?>
                <div class="sub-list">
                <text class="torrit-text">Top Subtorrits</text>
                <div class="clearfix"></div>
                <?php

                //$new_subs = get_new_subs();
                $home_top_subs = get_top_subs(7);
                foreach($home_top_subs as $subscription)
                {
                    $sub_letter = strtoupper(substr($subscription['name'],0,1));
                    $color = empty(trim($subscription['color_code'])) ? "#64c5a5" : $subscription['color_code'];
                    echo '
                        <div style="display: inline-block;">
                            <div class="sub-logo-small" style="background-color:'.$color.'"><p class="subletter-small">'.$sub_letter.'</p></div>
                        </div>
                        <div style="display: inline-block;">   
                            <a class="sublist-links" href="'.$base_uri.'/t/'.$subscription['slug'].'">/t/'.$subscription['slug'].'</a><br>
                            <div class="clearfix"></div>
                            <div class="sub-member-count">'.$subscription['sub_count'].' subscribers</div>
                       </div>
                        <div class="clearfix"></div>
                         ';
                }

                ?>
                </div>
            </div>
        </div>
        <noscript><style>head { visibility: visible; }</style></noscript>
        <noscript><style>body { visibility: visible; }</style></noscript>
    </body>
</html>