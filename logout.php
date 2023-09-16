<?php

session_start();
/*
 * ENABLE THIS BLOCK WHEN LIVE

if(!isset($_SESSION['validated']))
{
    header("Location: /");
    exit();
}
*/

try 
{

    unset($_SESSION['logged_in']);
    unset($_SESSION['user_id']);
    unset($_SESSION['username']);
    unset($_SESSION['user_role']);
}
catch(Exception $e){
    //fail silently
}

header("Location: /home");
?>
