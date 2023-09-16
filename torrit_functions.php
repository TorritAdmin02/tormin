<?php

echo '<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">';
    $base_uri = '';
    if(!isset($_SESSION['validated']))
    {
        header("Location: /");
        exit();
    }
    class DatabaseConnect
    {
        private static $init = FALSE;
        public static $conn;
        public static $env;

        public static function initialize()
        {
            if (self::$init===TRUE)return;
            self::$init = TRUE;
            $env = parse_ini_file('.env');
            self::$conn = new mysqli("localhost",$env['DB_USER'],$env['DB_PASS'], "tormin");
        }
    }
    DatabaseConnect::initialize();
    function get_sticky_home()
    {
        $mysqli = DatabaseConnect::$conn;
        $sticky_posts_query = $mysqli->prepare("select * from posts where active > 0 and locked <> 1 and approved > 0  and sticky_home = 1 ORDER BY sticky_home DESC");
        $sticky_posts_query->execute();
        $sticky_result = $sticky_posts_query->get_result();
        $sticky_posts = $sticky_result->fetch_all(MYSQLI_ASSOC);
        return $sticky_posts;
    }
    function get_home_page_posts($page_first_result, $results_per_page)
    {
        $mysqli = DatabaseConnect::$conn;
        $posts_query = $mysqli->prepare("select * from posts where active > 0 and locked <> 1 and approved > 0 ORDER BY created_at DESC LIMIT ?,?");
        $posts_query->bind_param('ii', $page_first_result, $results_per_page);
        $posts_query->execute();
        $post_result = $posts_query->get_result();
        $posts = $post_result->fetch_all(MYSQLI_ASSOC);
        return $posts;
        
    }

    function num_posts()
    {

        $mysqli = DatabaseConnect::$conn;

        if(is_null($mysqli))
            return false;

        $posts_query = $mysqli->prepare("select id from posts where active > 0 and locked <> 1 and approved > 0");
        $posts_query->execute();
        $post_result = $posts_query->get_result();
        $post_count = $post_result->num_rows;
        return $post_count;

    }
    function get_new_subs()
    {
        $mysqli = DatabaseConnect::$conn;

        if(is_null($mysqli))
            return false;

        $get_subs_query = $mysqli->prepare("select * from community order by(created_at) desc limit 15");
        $get_subs_query->execute();
        $subs_result = $get_subs_query->get_result();
        $subs = $subs_result->fetch_all(MYSQLI_ASSOC);

        return $subs;

    }

function get_top_subs($limit)
{
    $mysqli = DatabaseConnect::$conn;

    if(is_null($mysqli))
        return false;

    $get_subs_query = $mysqli->prepare(
                                        "
                                        select
                                          cm.id,
                                          cm.name,
                                          cm.slug,
                                          cm.color_code,
                                          s.cnt as sub_count
                                        from
                                          community cm
                                          inner join (select 
                                                        sub_id,
                                                        count(*) as cnt 
                                                      from subscriptions 
                                                      group by sub_id
                                                      order by count(*) desc
                                                      limit ?) as s on cm.id = s.sub_id
                                        order by s.cnt desc
                                        ");
    echo $mysqli->error;
    $get_subs_query->bind_param('i', $limit);
    $get_subs_query->execute();
    $subs_result = $get_subs_query->get_result();
    $subs = $subs_result->fetch_all(MYSQLI_ASSOC);

    return $subs;

}

    function get_posts_for_sub($sub_id)
    {
        $mysqli = DatabaseConnect::$conn;
        $posts_query = $mysqli->prepare("select * from posts where active > 0 and locked <> 1 and approved > 0 and sub_id = ? order by(created_at) desc");
        $posts_query->bind_param('i', $sub_id);
        $posts_query->execute();
        $post_result = $posts_query->get_result();
        $posts = $post_result->fetch_all(MYSQLI_ASSOC);

        return $posts;
    }

    function get_comment_count($post_id = null)
	{
        $mysqli = DatabaseConnect::$conn;
		if(is_null($post_id))
			return 0;
		
		$get_count = $mysqli->prepare("select count(id) as count from comments where post_id = ?");
		$get_count->bind_param('i', $post_id);
		$get_count->execute();
		$comment_count_result = $get_count->get_result();
		$comment_count = $comment_count_result->fetch_assoc()['count'];
		
		return $comment_count;
	}

    function get_user($user_id)
    {
        $mysqli = DatabaseConnect::$conn;
		if(is_null($user_id))
			return false;
		
		$get_user_query = $mysqli->prepare("select id, username, user_role, score, active from users where id = ?");
		$get_user_query->bind_param('i', $user_id);
		$get_user_query->execute();
		$user_result = $get_user_query->get_result();
		$user = $user_result->fetch_assoc();
		
		return $user;
    }

    function get_user_by_name($username = null)
    {
        $mysqli = DatabaseConnect::$conn;
		if(is_null($username))
			return false;
		
		$get_user_query = $mysqli->prepare("select * from users where username = ?");
		$get_user_query->bind_param('s', $username);
		$get_user_query->execute();
		$user_result = $get_user_query->get_result();
		$user = $user_result->fetch_assoc();
		
		return $user;
    }

    function get_sub_by_name($slug = null)
    {
        $mysqli = DatabaseConnect::$conn;

        if(is_null($mysqli))
            return false;

        if(is_null($slug))
            return false;

        $get_sub_query = $mysqli->prepare("select * from community where slug = ?");
        $get_sub_query->bind_param('s', $slug);
        $get_sub_query->execute();
        $sub_result = $get_sub_query->get_result();
        $sub = $sub_result->fetch_assoc();
        
        return $sub;

    }

    function get_sub($sub_id = null)
    {
        $mysqli = DatabaseConnect::$conn;
		if(is_null($sub_id))
			return false;

        $get_sub_query = $mysqli->prepare("select * from community where id = ?");
        $get_sub_query->bind_param('i', $sub_id);
        $get_sub_query->execute();
        $sub_result = $get_sub_query->get_result();
        $sub = $sub_result->fetch_assoc();
        
        return $sub;
    }

    function get_post($post_id = null)
    {
        $mysqli = DatabaseConnect::$conn;
		if(is_null($post_id))
			return false;

        $get_post_query = $mysqli->prepare("select * from posts where id = ?");
        $get_post_query->bind_param('i', $post_id);
        $get_post_query->execute();
        $post_result = $get_post_query->get_result();
        $post = $post_result->fetch_assoc();
        
        return $post;
    }

    function get_comments_for_post($post_id = null)
    {
        $mysqli = DatabaseConnect::$conn;
		if(is_null($post_id))
			return false;

        $get_comments = $mysqli->prepare("select * from comments where post_id = ? and parent = 0");
        $get_comments->bind_param('i', $post_id);
        $get_comments->execute();
        $get_comments_result = $get_comments->get_result();
        $comments = $get_comments_result->fetch_all(MYSQLI_ASSOC);

        $all_comments = array();
        foreach($comments as $key => $comment) {
            $all_comments[$key] = get_child_comments($comment);
        }

        return $all_comments;

    }

    function get_child_comments($comment)
    {
        $mysqli = DatabaseConnect::$conn;
		if(is_null($comment))
			return false;

        $comment_id = $comment['id'];
        $get_child_comments = $mysqli->prepare("select * from comments where parent = ?");
        $get_child_comments->bind_param('i', $comment_id);
        $get_child_comments->execute();
        $get_child_comments_result = $get_child_comments->get_result();
        $child_comments = $get_child_comments_result->fetch_all(MYSQLI_ASSOC);

        if(count($child_comments) > 0) {
            $this_children = array();
            foreach($child_comments as $child)
            {
               array_push($this_children, get_child_comments($child));
            }
            $comment['children'] = $this_children;
            return $comment;
        }
        else
        {
            return $comment;
        }
    }

    function torrit_user_login($auth_array = null)
    {
        session_start();
        $mysqli = DatabaseConnect::$conn;
		if(is_null($auth_array))
			return false;
        if(!array_key_exists('username', $auth_array) || !array_key_exists('password', $auth_array))
            return false;
        $secret = $auth_array['password'];
        $username = $auth_array['username'];

        $user = get_user_by_name($username);
        $hashed_secret = $user['secret_phrase'];
        if($user['active'] > 0)
        {
            if(password_verify($secret, $hashed_secret))
            {
                $_SESSION['logged_in'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['user_role'];
            }
            else
            {
                $_SESSION['error_message'] = "invalid user or pass";
            }
        }
        else
        {
            $_SESSION['error_message'] = "user error";
        }

    }

    function create_post($post_title, $post_content, $user_id, $sub_id)
    {
        $error_array = array();
        $mysqli = DatabaseConnect::$conn;

        if(empty(trim($post_title)))
        {
            array_push($error_array, 'title cannot be empty');
        }
        if(empty(trim($post_content)))
        {
            array_push($error_array, 'post cannot be empty');
        }
        if(is_null($sub_id))
        {
            array_push($error_array, 'invalid sub id');
        }

        if(count($error_array) > 0)
        {
            return $error_array;
        }

		if(is_null($mysqli))
			return false;

        if($sub_id == 0)
        {
            $stripped_post_title = strip_tags($post_title);

            $preserved_content = nl2br($post_content);
            $stripped_post_body  = strip_tags($preserved_content);

            $date = date('Y-m-d H:i:s');
            $create_post = $mysqli->prepare("insert into posts(title, body, user_id, active, locked, created_at, sub_id, score)
                                                     values(?, ?, ?, 1, 0, ?, 0, 0)");

            $create_post->bind_param('ssis', $stripped_post_title, $stripped_post_body, $user_id, $date);

            if ($create_post->execute()) {
                $post_id = $create_post->insert_id;
                return $post_id;
            }
            return;
        }
        $check_banned = check_already_banned($sub_id, $user_id);

        if(!$check_banned) {
            $sub_settings = get_sub_settings($sub_id);
            $approve_setting = $sub_settings['approve_posts'];

            //sanitize
            $stripped_post_title = strip_tags($post_title);
            $stripped_post_body  = strip_tags($post_content);

            $approved = ($approve_setting > 0) ? 0 : 1;

            $date = date('Y-m-d H:i:s');
            $create_post = $mysqli->prepare("insert into posts(title, body, user_id, active, locked, created_at, sub_id, score, approved)
                                                     values(?, ?, ?, 1, 0, ?, ?, 0, ?)");

            $create_post->bind_param('ssisii', $stripped_post_title, $stripped_post_body, $user_id, $date, $sub_id, $approved);

            if ($create_post->execute()) {
                $post_id = $create_post->insert_id;
                return $post_id;
            }
        }

        return false;
                        
    }

function update_post($post_title, $post_content, $post_id, $sub_id, $user_id)
{
    $error_array = array();
    $mysqli = DatabaseConnect::$conn;

    if(empty(trim($post_title)))
    {
        array_push($error_array, 'title cannot be empty');
    }
    if(empty(trim($post_content)))
    {
        array_push($error_array, 'post cannot be empty');
    }
    if(!verify_pid($post_id))
    {
        array_push($error_array, 'invalid post id');
    }
    if($sub_id > 0 && !verify_sid($sub_id))
    {
        array_push($error_array, 'invalid sub id');
    }

    if(count($error_array) > 0)
    {
        return $error_array;
    }

    if(is_null($mysqli))
        return false;
    $check_banned = check_already_banned($sub_id, $user_id);

    if(!$check_banned) {

        $sub_settings = get_sub_settings($sub_id);
        $approve_setting = $sub_settings['approve_posts'];

        //sanitize
        $stripped_post_title = strip_tags($post_title);
        $stripped_post_body  = strip_tags($post_content);

        $approved = ($approve_setting > 0) ? 0 : 1;

        $date = date('Y-m-d H:i:s');
        $update_post = $mysqli->prepare("update posts set title = ?, body = ?, approved = ? where id = ?");

        $update_post->bind_param('ssii', $stripped_post_title, $stripped_post_body, $approved, $post_id);

        if ($update_post->execute()) {
            return $post_id;
        }
    }

    return false;

}

    function create_notification($type, $user_id, $from, $post_id)
    {
        $mysqli = DatabaseConnect::$conn;
        if(is_null($mysqli))
            return false;

        $date = date('Y-m-d H:i:s');

        $create_notfication = $mysqli->prepare("insert into notifications(type, user_id, originator, post_id, created_at)
                                                 values(?, ?, ?, ?, ?)");
        echo $mysqli->error;
        $create_notfication->bind_param('iiiis', $type, $user_id, $from, $post_id, $date);

        if ($create_notfication->execute())
        {
            return true;
        }

        return false;
    }

    function update_notification($nid)
    {
        $mysqli = DatabaseConnect::$conn;
        if(is_null($mysqli))
            return false;
        $update_notification = $mysqli->prepare("update notifications set `read` = 1 where id = ?");
        $update_notification->bind_param('i', $nid);
        $update_notification->execute();

        return;
    }

    function get_notifications($user_id)
    {
        $mysqli = DatabaseConnect::$conn;
        if(is_null($mysqli))
            return false;

        $get_notifications = $mysqli->prepare("select * from notifications where user_id = ? and `read` = 0");
        $get_notifications->bind_param('i', $user_id);
        $get_notifications->execute();
        $get_notifications_result = $get_notifications->get_result();
        $notifications = $get_notifications_result->fetch_all(MYSQLI_ASSOC);

       return $notifications;

    }
    function create_comment($comment, $post_id, $sub_id, $comment_pid, $user_id)
    {
        $error_array = array();
        $mysqli = DatabaseConnect::$conn;

        if(empty(trim($comment)))
        {
            array_push($error_array, 'comment cannot be empty');
        }
        if(empty(trim($post_id)) || is_null($sub_id))
        {
            array_push($error_array, 'an error occurred');
        }

        if(count($error_array) > 0)
        {
            return $error_array;
        }

		if(is_null($mysqli))
			return false;
        $check_banned = check_already_banned($sub_id, $user_id);

        if(!$check_banned) {
            $comment_clean = strip_tags($comment);
            $created_at = date('Y-m-d H:i:s');
            $create_comment = $mysqli->prepare("insert into comments(user_id, post_id, comment, parent, score, active, created_at)
                                                     values(?, ?, ?, ?, 0, 1, ?)");

            $create_comment->bind_param('iisis', $user_id, $post_id, $comment_clean, $comment_pid, $created_at);

            if ($create_comment->execute()) {
                $comment_id = $create_comment->insert_id;

                try
                {
                    $type = ($comment_pid > 0) ? 2 : 1;
                    //2 comment reponse
                    //1 post reponse
                    $post = get_post($post_id);
                    $user = $post['user_id'];
                    if($user !==  $user_id)
                    {
                        create_notification($type, $user, $user_id, $post_id);
                    }
                }
                catch(Exception $e)
                {}
                return $comment_id;
            }
        }

        return false;
    }

    function torrit_user_register($user_data)
    {
        $mysqli = DatabaseConnect::$conn;
        if(is_null($user_data))
			return false;

        $date = date('Y-m-d H:i:s');
        $password = password_hash($user_data['password'], PASSWORD_BCRYPT);

        $username = strip_tags(trim($user_data['username']));
        $clean_username = str_replace( '/', '', $username);
        $clean_username = preg_replace('/\s+/', '', $clean_username);
        $clean_username = stripslashes($clean_username);

        $user_exists = get_user_by_name($clean_username);

        if(!$user_exists)
        {
            $create_user = $mysqli->prepare("insert into users(username, secret_phrase, user_role, score, active, created_at)
                                                     values(?, ?, 1, 0, 1, ?)");
            $create_user->bind_param('sss', $clean_username, $password, $date);

            if ($create_user->execute()) {
                $user_id = $create_user->insert_id;

                $_SESSION['logged_in'] = true;
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $clean_username;
                $_SESSION['user_role'] = 1;

                return true;
            }
        }

        return false;
    }
function create_sub($sub_name, $sub_url, $sub_color_code, $sub_masthead_color, $sub_sidebar, $sub_postrules, $user_id)
{
    $mysqli = DatabaseConnect::$conn;

    if(is_null($mysqli))
        return false;

    $date = date('Y-m-d H:i:s');
    $s_subname = strip_tags($sub_name);
    $s_suburl  = strip_tags($sub_url);
    $s_subcc   = strip_tags($sub_color_code);
    $s_mast    = strip_tags($sub_masthead_color);
    $s_sidebar = strip_tags($sub_sidebar);
    $s_rules   = strip_tags($sub_postrules);

    $clean_suburl = stripslashes($s_suburl);
    $clean_suburl = str_replace( '/', '', $clean_suburl );
    $clean_subname = stripslashes($s_subname);
    $clean_subname = str_replace( '/', '', $clean_subname);

    $create_sub = $mysqli->prepare("insert into community(name, slug, color_code, mast_theme, sidebar, post_rules, owner, created_at, subs, active)
                                                     values(?, ?, ?, ?, ?, ?, ?, ?, 0, 1)");

    $create_sub->bind_param('ssssssis', $clean_subname, $clean_suburl, $s_subcc, $s_mast, $s_sidebar, $s_rules, $user_id, $date);

    if($create_sub->execute())
    {
        $sub_id = $create_sub->insert_id;
        return $sub_id;
    }

    return false;

}

function check_already_voted($post_id, $user_id, $action)
{
    $mysqli = DatabaseConnect::$conn;

    if (is_null($mysqli))
        return false;

    $get_votes = $mysqli->prepare("select `action` from post_votes where post_id = ? and user_id = ?");
    $get_votes->bind_param('ii', $post_id, $user_id);
    $get_votes->execute();
    $get_votes_result = $get_votes->get_result();
    $vote_row = $get_votes_result->fetch_assoc();

    if(!is_null($vote_row))
    {
       if(($vote_row['action'] > 0) && ($action < 1))
       {
           $mod_by = 2;
           $update_vote = $mysqli->prepare("update post_votes set action = ? where post_id = ? and user_id = ?");
           $update_vote->bind_param('iii', $action, $post_id, $user_id);
           $update_vote->execute();
           update_post_score($post_id, false, $mod_by);
           return true;
       }
       else if($action == $vote_row['action']){
            return true;
        }
        else
        {
            $mod_by = 2;
            $update_vote = $mysqli->prepare("update post_votes set action = ? where post_id = ? and user_id = ?");
            $update_vote->bind_param('iii', $action, $post_id, $user_id);
            $update_vote->execute();
            update_post_score($post_id, $action, $mod_by);
            return true;
        }
    }

    return false;

}

function check_comment_voted($post_id, $comment_id, $user_id, $action)
{
    $mysqli = DatabaseConnect::$conn;

    if (is_null($mysqli))
        return false;

    $get_comment_votes = $mysqli->prepare("select `action` from comment_votes where post_id = ? and comment_id = ? and user_id = ?");
    $get_comment_votes->bind_param('iii', $post_id, $comment_id, $user_id);
    $get_comment_votes->execute();
    $get_comment_votes_result = $get_comment_votes->get_result();
    $comment_vote_row = $get_comment_votes_result->fetch_assoc();

    if(!is_null($comment_vote_row))
    {
        if(($comment_vote_row['action'] > 0) && ($action < 1))
        {
            $mod_by = 2;
            $update_comment = $mysqli->prepare("update comment_votes set action = ? where post_id = ? and comment_id = ? and user_id = ?");
            $update_comment->bind_param('iiii', $action, $post_id, $comment_id, $user_id);
            $update_comment->execute();
            update_comment_score($comment_id, false, $mod_by);
            return true;
        }
        else if($action == $comment_vote_row['action']){
            return true;
        }
        else
        {
            $mod_by = 2;
            $update_comment = $mysqli->prepare("update comment_votes set action = ? where post_id = ? and comment_id = ? and user_id = ?");
            $update_comment->bind_param('iiii', $action, $post_id, $comment_id, $user_id);
            $update_comment->execute();
            update_comment_score($comment_id, $action, $mod_by);
            return true;
        }
    }

    return false;

}
function submit_post_vote($post_id, $user_id, $action)
{
    $mysqli = DatabaseConnect::$conn;

    if (is_null($mysqli))
        return false;

    $voted = check_already_voted($post_id, $user_id, $action);

    if (!$voted)
    {

        $create_post_vote = $mysqli->prepare("insert into post_votes(post_id, user_id, action)
                                              values(?, ?, ?)");
        $create_post_vote->bind_param('iii', $post_id, $user_id, $action);

        if ($create_post_vote->execute()) {
            update_post_score($post_id, $action, 1);
        }
    }

    return false;
}

function update_post_score($post_id, $incr, $mod_by)
{
    $mysqli = DatabaseConnect::$conn;

    if (is_null($mysqli))
        return false;

    if($incr)
    {
        $update_post = $mysqli->prepare("update posts set score = score + ? where id = ?");
        $update_post->bind_param('ii', $mod_by, $post_id);
        $update_post->execute();
    }
    else
    {
        $update_post = $mysqli->prepare("update posts set score = score - ? where id = ?");
        $update_post->bind_param('ii', $mod_by, $post_id);
        $update_post->execute();
    }

    return true;
}

function update_comment_score($comment_id, $incr, $mod_by)
{
    $mysqli = DatabaseConnect::$conn;

    if (is_null($mysqli))
        return false;

    if($incr)
    {
        $update_comment = $mysqli->prepare("update comments set score = score + ? where id = ?");
        $update_comment->bind_param('ii', $mod_by, $comment_id);
        $update_comment->execute();
    }
    else
    {
        $update_comment = $mysqli->prepare("update comments set score = score - ? where id = ?");
        $update_comment->bind_param('ii', $mod_by, $comment_id);
        $update_comment->execute();
    }

    return true;
}

function unset_vote($post_id, $user_id)
{
    $mysqli = DatabaseConnect::$conn;

    if (is_null($mysqli))
        return false;

    $get_votes = $mysqli->prepare("select `action` from post_votes where post_id = ? and user_id = ?");
    $get_votes->bind_param('ii', $post_id, $user_id);
    $get_votes->execute();
    $get_votes_result = $get_votes->get_result();
    $vote_row = $get_votes_result->fetch_assoc();

    if(!is_null($vote_row))
    {
        $action = $vote_row['action'];
        $unset_vote = $mysqli->prepare("delete from post_votes where post_id = ? and user_id = ?");
        $unset_vote->bind_param('ii', $post_id, $user_id);
        if($unset_vote->execute())
        {
            update_post_score($post_id, !$action, 1);
        }
        return true;
    }
}

function verify_pid($post_id)
{
    $mysqli = DatabaseConnect::$conn;

    if (is_null($mysqli))
        return false;

    $verify_post = $mysqli->prepare("select id from posts where id = ?");
    $verify_post->bind_param('i', $post_id);
    $verify_post->execute();
    $verify = $verify_post->get_result()->num_rows;
    if($verify)
        return true;

    return false;
}

function verify_cid($comment_id)
{
    $mysqli = DatabaseConnect::$conn;

    if (is_null($mysqli))
        return false;

    $verify_comment = $mysqli->prepare("select id from comments where id = ?");
    $verify_comment->bind_param('i', $comment_id);
    $verify_comment->execute();
    $verify = $verify_comment->get_result()->num_rows;
    if($verify)
        return true;

    return false;
}

function verify_sid($sub_id)
{
    $mysqli = DatabaseConnect::$conn;

    if (is_null($mysqli))
        return false;

    $verify_sub = $mysqli->prepare("select id from community where id = ?");
    $verify_sub->bind_param('i', $sub_id);
    $verify_sub->execute();
    $verify = $verify_sub->get_result()->num_rows;
    if($verify)
        return true;

    return false;
}

function verify_uid($user_id)
{
    $mysqli = DatabaseConnect::$conn;

    if (is_null($mysqli))
        return false;

    $verify_user = $mysqli->prepare("select id from users where id = ?");
    $verify_user->bind_param('i', $user_id);
    $verify_user->execute();
    $verify = $verify_user->get_result()->num_rows;
    if($verify)
        return true;

    return false;
}

function submit_comment_vote($post_id, $comment_id, $user_id, $action)
{
    $mysqli = DatabaseConnect::$conn;

    if (is_null($mysqli))
        return false;

    $voted = check_comment_voted($post_id, $comment_id, $user_id, $action);

    if (!$voted)
    {

        $create_comment_vote = $mysqli->prepare("insert into comment_votes(post_id, comment_id, user_id, action)
                                              values(?, ?, ?, ?)");
        $create_comment_vote->bind_param('iiii', $post_id, $comment_id, $user_id, $action);

        if ($create_comment_vote->execute()) {
            update_comment_score($comment_id, $action, 1);
        }
    }

    return false;
}

function adjustBrightness($hex, $steps) {
    // Steps should be between -255 and 255. Negative = darker, positive = lighter
    $steps = max(-255, min(255, $steps));

    // Normalize into a six character long hex string
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) {
        $hex = str_repeat(substr($hex,0,1), 2).str_repeat(substr($hex,1,1), 2).str_repeat(substr($hex,2,1), 2);
    }

    // Split into three parts: R, G and B
    $color_parts = str_split($hex, 2);
    $return = '#';

    foreach ($color_parts as $color) {
        $color   = hexdec($color); // Convert to decimal
        $color   = max(0,min(255,$color + $steps)); // Adjust color
        $return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
    }

    return $return;
}

function get_post_queue($sid)
{
    $mysqli = DatabaseConnect::$conn;

    if (is_null($mysqli))
        return false;

    $sub_post_queue = $mysqli->prepare("select * from posts where sub_id = ? and approved = 0");
    $sub_post_queue->bind_param('i', $sid);
    $sub_post_queue->execute();

    $sub_post_queue_result = $sub_post_queue->get_result();
    $queued_posts = $sub_post_queue_result->fetch_all(MYSQLI_ASSOC);

    return $queued_posts;

}

function get_sub_settings($sub_id)
{
    $mysqli = DatabaseConnect::$conn;

    if (is_null($mysqli))
        return false;

    $sub_settings = $mysqli->prepare("select approve_posts from community where id = ?");
    $sub_settings->bind_param('i', $sub_id);
    $sub_settings->execute();

    $sub_settings_result = $sub_settings->get_result();
    $settings = $sub_settings_result->fetch_assoc();

    return $settings;
}

function update_sub($sub_id, $sub_name, $sub_url, $sub_color_code, $sub_masthead_color, $sub_sidebar, $sub_postrules, $approve_posts, $user_id)
{

    $mysqli = DatabaseConnect::$conn;

    if (is_null($mysqli))
        return false;

    $s_subname = strip_tags($sub_name);
    $s_suburl  = strip_tags($sub_url);
    $s_subcc   = strip_tags($sub_color_code);
    $s_mast    = strip_tags($sub_masthead_color);
    $s_sidebar = strip_tags($sub_sidebar);
    $s_rules   = strip_tags($sub_postrules);

    $update_sub = $mysqli->prepare("update community set name = ?, slug = ?, color_code = ?, mast_theme = ?, sidebar = ?, post_rules = ?, approve_posts = ?
                                                     where id = ?");

    $update_sub->bind_param('ssssssii', $s_subname, $s_suburl, $s_subcc, $s_mast, $s_sidebar, $s_rules, $approve_posts, $sub_id);

    if ($update_sub->execute()) {
        return $sub_id;
    }

    return false;
}

function manage_post($post_id, $action)
{
    $mysqli = DatabaseConnect::$conn;

    if (is_null($mysqli))
        return false;

    $manage_action = ($action < 1) ? -1 : 1;
    $update_post = $mysqli->prepare("update posts set approved = ? where id = ?");

    $update_post->bind_param('ii', $manage_action, $post_id);

    if ($update_post->execute()) {
        return true;
    }

    return false;
}

function frontpage()
{
    $mysqli = DatabaseConnect::$conn;

    if (is_null($mysqli))
        return false;
    
    if (session_status() === PHP_SESSION_NONE)
    {
        session_start();
    }

    //used later for mixing logged in users subscriptions
    if(isset($_SESSION['user_id']))
    {
        $user_id = $_SESSION['user_id'];
    }
    $frontpage_query = $mysqli->prepare("select * from posts p
                                            order by score desc, (select count(id) from comments where post_id = p.id) desc");
    $frontpage_query->execute();
    $frontpage_posts_result = $frontpage_query->get_result();
    $posts = $frontpage_posts_result->fetch_all(MYSQLI_ASSOC);

    return $posts;
}

function search_users($username_str)
{
    $mysqli = DatabaseConnect::$conn;

    if (is_null($mysqli))
        return false;

    $user_search = $mysqli->prepare("select u.*, sb.banned_status, sb.sub_id from users u left join sub_bans sb on u.id = sb.user_id where username like ?");
    $user_search->bind_param('s', $username_str);
    $user_search->execute();
    $search_results = $user_search->get_result();

    $users = $search_results->fetch_all(MYSQLI_ASSOC);

    return $users;
}

function toggle_user_sub_ban_status($sub_id, $user_to_ban, $action, $user_id)
{
    $mysqli = DatabaseConnect::$conn;

    if (is_null($mysqli))
        return false;

    $ban_record = check_already_banned($sub_id, $user_to_ban);

    if (!$ban_record)
    {
        $date = date('Y-m-d H:i:s');
        $create_ban_record = $mysqli->prepare("insert into sub_bans(sub_id, user_id, banned_by, banned_status, created_at)
                                              values(?, ?, ?, ?, ?)");
        $create_ban_record->bind_param('iiiis', $sub_id, $user_to_ban, $user_id, $action, $date);

        if ($create_ban_record->execute()) {
            return $ban_id;
        }
    }
    else
    {
        $update_ban = $mysqli->prepare("update sub_bans set banned_status = ?, banned_by = ? where sub_id = ? and user_id = ?");

        $update_ban->bind_param('iiii', $action, $user_id, $sub_id, $user_to_ban);

        if ($update_ban->execute()) {
            return true;
        }
    }

    return false;
}

function check_already_banned($sub_id, $user_id)
{
    $mysqli = DatabaseConnect::$conn;

    if (is_null($mysqli))
        return false;

    $ban_record = $mysqli->prepare("select id, banned_status from sub_bans where sub_id = ? and user_id = ?");
    $ban_record->bind_param('ii', $sub_id, $user_id);
    $ban_record->execute();
    $ban_record_query_results = $ban_record->get_result();

    $ban = $ban_record_query_results->fetch_all(MYSQLI_ASSOC);

    return $ban;
}

function subscribe_to_sub($sub_id, $user_id)
{
    $mysqli = DatabaseConnect::$conn;

    if (is_null($mysqli))
        return false;

    $already_subbed = check_already_subbed($sub_id, $user_id);
    if(!$already_subbed)
    {
        $date = date('Y-m-d H:i:s');
        $create_subscription = $mysqli->prepare("insert into subscriptions(sub_id, user_id, created_at)
                                              values(?, ?, ?)");
        echo $mysqli->error;
        $create_subscription->bind_param('iis', $sub_id, $user_id, $date);
        $sub = $create_subscription->execute();
        if ($sub) {
            return true;
        }
    }

    return;
}

function check_already_subbed($sub_id, $user_id)
{
    $mysqli = DatabaseConnect::$conn;

    if (is_null($mysqli))
        return false;

    $sub_record = $mysqli->prepare("select id from subscriptions where sub_id = ? and user_id = ?");
    $sub_record->bind_param('ii', $sub_id, $user_id);
    $sub_record->execute();
    $sub_record_query_results = $sub_record->get_result()->num_rows;

    if($sub_record_query_results)
        return true;

    return false;
}

function get_all_subs_for_user($user_id)
{
    $mysqli = DatabaseConnect::$conn;

    if (is_null($mysqli))
        return false;

    $all_user_subscriptions = $mysqli->prepare("select s.name, s.slug, s.id, s.color_code, s.mast_theme from community s join subscriptions scrp on s.id = scrp.sub_id where scrp.user_id = ?");
    $all_user_subscriptions->bind_param('i', $user_id);
    $all_user_subscriptions->execute();
    $all_user_subscriptions_result = $all_user_subscriptions->get_result();
    $subs = $all_user_subscriptions_result->fetch_all(MYSQLI_ASSOC);

    return $subs;
}

function unsubscribe($sub_id, $user_id)
{
    $mysqli = DatabaseConnect::$conn;

    if (is_null($mysqli))
        return false;

    $unsubscribe = $mysqli->prepare("delete from subscriptions where sub_id = ? and user_id = ?");
    $unsubscribe->bind_param('ii', $sub_id, $user_id);

    if($unsubscribe->execute())
        return true;

    return false;
}

function get_sub_subcriber_count($sub_id)
{
    $mysqli = DatabaseConnect::$conn;

    if (is_null($mysqli))
        return false;

    $all_subscribers = $mysqli->prepare("select id from subscriptions where sub_id = ?");
    $all_subscribers->bind_param('i', $sub_id);
    $all_subscribers->execute();
    $all_subs_count = $all_subscribers->get_result()->num_rows;

    return $all_subs_count;
}

function get_user_posts_and_comments($user_id)
{
    $mysqli = DatabaseConnect::$conn;

    if (is_null($mysqli))
        return false;

    $all_user_posts = $mysqli->prepare("select * from posts where user_id = ?");
    $all_user_posts->bind_param('i', $user_id);
    $all_user_posts->execute();
    $all_user_posts_result = $all_user_posts->get_result();
    $user_posts = $all_user_posts_result->fetch_all(MYSQLI_ASSOC);

    $all_user_comments = $mysqli->prepare("select * from comments where user_id = ?");
    $all_user_comments->bind_param('i', $user_id);
    $all_user_comments->execute();
    $all_user_comments_result = $all_user_comments->get_result();
    $user_comments = $all_user_comments_result->fetch_all(MYSQLI_ASSOC);

    $user_data = array_merge($user_posts, $user_comments);
    usort($user_data, 'cmp');

    return $user_data;
}

function torrit_search($search_term)
{
    $mysqli = DatabaseConnect::$conn;

    if (is_null($mysqli))
        return false;

    $search = '%'.$search_term.'%';
    $search_posts = $mysqli->prepare("select * from posts where body like ? or title like ?");
    $search_posts->bind_param('ss', $search, $search);
    $search_posts->execute();
    $search_posts_result = $search_posts->get_result();
    $returned_posts = $search_posts_result->fetch_all(MYSQLI_ASSOC);
    foreach($returned_posts as $key => $post)
    {
        $returned_posts[$key]['type'] = 'post';
    }

    $search_comments = $mysqli->prepare("select * from comments where comment like ?");
    $search_comments->bind_param('s', $search);
    $search_comments->execute();
    $search_comments_result = $search_comments->get_result();
    $returned_comments = $search_comments_result->fetch_all(MYSQLI_ASSOC);

    foreach($returned_comments as $key => $comment)
    {
        $returned_comments[$key]['type'] = 'comment';
    }

    $returned_search_data = array_merge($returned_posts, $returned_comments);

    return $returned_search_data;
}
function contact(){}

function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}
function cmp($a, $b){
    $ad = strtotime($a['created_at']);
    $bd = strtotime($b['created_at']);
    return ($bd-$ad);
}

function extend_bbcode($class)
{
    $bbcode = $class;
    $bbcode->addTag('h1', function($tag, &$html, $openingTag) {
        if ($tag->opening) {
            return '<h1>';
        } else {
            return '</h1>';
        }
    });
    $bbcode->addTag('h2', function($tag, &$html, $openingTag) {
        if ($tag->opening) {
            return '<h2>';
        } else {
            return '</h2>';
        }
    });
    $bbcode->addTag('h3', function($tag, &$html, $openingTag) {
        if ($tag->opening) {
            return '<h3>';
        } else {
            return '</h3>';
        }
    });
    $bbcode->addTag('p', function($tag, &$html, $openingTag) {
        if ($tag->opening) {
            return '<p>';
        } else {
            return '</p>';
        }
    });
    $bbcode->addTag('hr', function($tag, &$html, $openingTag) {
        if ($tag->opening) {
            return '<hr>';
        }
    });
    $bbcode->addTag('br', function($tag, &$html, $openingTag) {
        if ($tag->opening) {
            return '<br>';
        }
    });
}
